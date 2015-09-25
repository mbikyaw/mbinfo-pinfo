<?php
/**
 * A Helper function.
 * User: mbikyaw
 * Date: 2/9/15
 * Time: 2:22 PM
 */



global $mbinfo_pinfo_db_version;
$mbinfo_pinfo_db_version = '1.1';


class MBInfoPInfo {

    public $table_name;
    public static $BUCKET = 'mbi-data';

    /**
     * Mbinfo constructor.
     */
    public function __construct()
    {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'mbinfopinfo';
    }


    function update_to_v11() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $this->table_name (
          uniprot varchar(14) NOT NULL,
          protein tinytext NOT NULL,
          family tinytext DEFAULT '',
          summary text DEFAULT '',
          pdb varchar(14) DEFAULT '',
          gene mediumint(14) DEFAULT 0,
          UNIQUE KEY uniprot (uniprot)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }

    function clear_data() {
        global $wpdb;
        $wpdb->query('Truncate table ' . $this->table_name);
    }

    function get_info() {
        global $wpdb;
        $count = $wpdb->get_var( "SELECT COUNT(*) FROM $this->table_name" );
        return ['count' => $count];
    }


    /**
     * Insert GCS meta data into database, if not already exist.
     * @param array $items
     * @return number of data
     */
    protected function insert_data($items) {
        global $wpdb;
        $cnt = 0;
        $family = '';
        $summary = '';
        foreach($items as $line) {
            $record = str_getcsv($line);
            if (count($record) != 6) {
                throw new Exception('invalid record at row ' . ($cnt + 2) . ': ' . $line);
            }
            if (empty($item['uniprot'])) {
                throw new Exception('no uniprot at row ' . ($cnt + 2));
            }
            $family = empty($item['protein']) ? $family : $item['protein'];
            $summary = empty($item['summary']) ? $summary : $item['summary'];
            $id = $wpdb->insert(
                $this->table_name,
                [
                    'uniprot' => $item['uniprot'],
                    'protein' => $family,
                    'summary' => $summary,
                    'pdb' => $item['pdb'],
                    'gene' => $item['gene']
                ]
            );
            if ($id != false) {
                $cnt++;
            }
        }
        return $cnt;
    }

    static function get_remote_data( $url) {
        $c = curl_init();
        curl_setopt( $c, CURLOPT_URL, $url );
        curl_setopt( $c, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $c, CURLOPT_SSL_VERIFYHOST, false );
        curl_setopt( $c, CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $c, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; rv:33.0) Gecko/20100101 Firefox/33.0" );
        curl_setopt( $c, CURLOPT_MAXREDIRS, 10 );
        curl_setopt( $c, CURLOPT_FOLLOWLOCATION, 1 );
        curl_setopt( $c, CURLOPT_CONNECTTIMEOUT, 9 );
        curl_setopt( $c, CURLOPT_REFERER, $url );
        curl_setopt( $c, CURLOPT_TIMEOUT, 60 );
        curl_setopt( $c, CURLOPT_AUTOREFERER, true );
        curl_setopt( $c, CURLOPT_ENCODING, 'gzip,deflate' );
        $data   = curl_exec( $c );
        curl_close( $c );
        return $data;
    }



    /**
     * Insert figure page from GCS object.
     * @param $fn string key of the csv file.
     * @return number return number of record inserted.
     */
    public function insert_from_gcs($fn) {
        $url = 'https://' . self::$BUCKET . '.storage.googleapis.com/' . $fn;
        $csv = self::get_remote_data($url);
        if ($csv === false) {
            throw new Exception('Fail to read ' . $url);
        }
        $lines = str_getcsv($csv, "\n");
        array_shift($lines); // remove header row
        $cnt = $this->insert_data($lines);
        return $cnt;

    }

}
