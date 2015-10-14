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


    /**
     * Parse short code.
     * @param $attr
     * @param string $content
     * @return string
     */
    public function parse_short_code($attr, $content) {
        $s = '';
        if (isset($attr['uniprot'])) {
            $s = 'uniprot="' . $attr['uniprot'] . '"';
        }
        if (isset($attr['protein'])) {
            if ($s) {
                $s .= ' ';
            }
            $s = 'protein="' . $attr['protein'] . '"';
            if (empty($content)) {
                $content = $attr['protein'];
            }
        }
        if (isset($attr['family'])) {
            if ($s) {
                $s .= ' ';
            }
            $s = 'family="' . $attr['family'] . '"';
        }
        return '<a ' . $s . '>' . $content . '</a>';
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

    function get_record($uniprot, $field = 'uniport') {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $this->table_name WHERE $s = '%s'", $field, $uniprot));
    }

    function list_record($limit, $offset) {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare("SELECT * FROM $this->table_name LIMIT %d OFFSET %d", $limit, $offset), ARRAY_A);
    }

    /**
     * @return array
     */
    function list_protein() {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM $this->table_name", ARRAY_A);
    }

    private function search_proteins_by_name($content) {
        $proteins = $this->list_protein();
        $list = [];
        foreach ($proteins as $protein) {
            $p = $protein['protein'];
            if (!empty($p)) {
                if (mb_stripos($content, ' ' . $p)) {
                    array_push($list, $protein);
                    continue;
                }
            }
            $family = $protein['family'];
            if (false && !empty($family)) {
                if (mb_stripos($content, $family)) {
                    array_push($list, $protein);
                    continue;
                }
            }
        }
        return $list;
    }

    function search_proteins($content) {
        $proteins = $this->list_protein();
        $list = [];
        foreach ($proteins as $protein) {
            $uniprot = $protein['uniprot'];
            if (mb_stripos($content, 'uniprot="' . $uniprot . '"')) {
                array_push($list, $protein);
                continue;
            }
            $p = $protein['protein'];
            if (!empty($p)) {
                if (mb_stripos($content, 'protein="' . $p . '"')) {
                    array_push($list, $protein);
                    continue;
                }
            }
            $f = $protein['family'];
            if (!empty($f)) {
                if (mb_stripos($content, 'family="' . $f . '"')) {
                    array_push($list, $protein);
                    continue;
                }
            }
        }
        return $list;
    }


    /**
     * Insert GCS meta data into database, if not already exist.
     * @param array $items
     * @return number of data
     * @throws Exception
     */
    function insert_data($items) {
        global $wpdb;
        $cnt = 0;
        $family = '';
        $summary = '';
        foreach($items as $line) {
            $record = str_getcsv($line, ",", '"');
            if (count($record) != 6) {
                throw new Exception('invalid record at row ' . ($cnt + 2) . ': ' . $line);
            }
            if (empty($record[3])) {
                throw new Exception('no uniprot at row ' . ($cnt + 2) . ' ' . $record[3]);
            }
            $family = empty($record[0]) ? $family : $record[0];
            $summary = empty($record[1]) ? $summary : $record[1];
            $id = $wpdb->insert(
                $this->table_name,
                [
                    'family' => $family,
                    'summary' => $summary,
                    'protein' => $record[2],
                    'uniprot' => $record[3],
                    'pdb' => $record[4],
                    'gene' => $record[5]
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
        $lines = str_getcsv($csv, "\r");
        array_shift($lines); // remove header row
        $cnt = $this->insert_data($lines);
        return $cnt;

    }

}
