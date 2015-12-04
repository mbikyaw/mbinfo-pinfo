<?php
/**
 * A Helper function.
 * User: mbikyaw
 * Date: 2/9/15
 * Time: 2:22 PM
 */



global $mbinfo_pinfo_db_version;
$mbinfo_pinfo_db_version = '1.2';


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

    function update_to_v12() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $this->table_name (
          uniprot varchar(14) NOT NULL,
          protein tinytext NOT NULL,
          family tinytext DEFAULT '',
          subfamily tinytext DEFAULT '',
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

    function get_record($uniprot) {
        global $wpdb;
        $sql = $wpdb->prepare("SELECT * FROM $this->table_name WHERE uniprot = '%s'", $uniprot);
        return $wpdb->get_row($sql, ARRAY_A);
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
        return $wpdb->get_results("SELECT * FROM $this->table_name ORDER BY protein", ARRAY_A);
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

    function search_referred_pages($protein) {

        $out = [];
        if (empty($protein)) {
            return $out;
        }

        $args = array(
            'sort_order' => 'desc',
            'sort_column' => 'post_modified',
            'post_type' => 'page',
            'post_status' => 'publish'
        );
        $pages = get_pages($args);
        foreach ($pages as $page) {
            if (mb_stripos($page->post_content, 'uniprot="' . $protein['uniprot'] . '"')) {
                array_push($out, $page);
                continue;
            }
            if (mb_stripos($page->post_content, 'protein="' . $protein['protein'] . '"')) {
                array_push($out, $page);
                continue;
            }
            if (mb_stripos($page->post_content, 'family="' . $protein['family'] . '"')) {
                array_push($out, $page);
                continue;
            }
        }
        return $out;
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
        $subfamily = '';
        $summary = '';
        foreach($items as $line) {
            $record = str_getcsv($line, ",", '"');
            if (count($record) != 7) {
                throw new Exception('invalid record at row ' . ($cnt + 2) . ': ' . $line);
            }
            if (empty($record[3])) {
                throw new Exception('no uniprot at row ' . ($cnt + 2) . ' ' . $record[3]);
            }
            $family = empty($record[0]) ? $family : $record[0];
            $subfamily = empty($record[1]) ? $subfamily : $record[1];
            $summary = empty($record[2]) ? $summary : $record[2];
            $id = $wpdb->insert(
                $this->table_name,
                [
                    'family' => $family,
                    'summary' => $summary,
                    'subfamily' => $subfamily,
                    'protein' => $record[3],
                    'uniprot' => $record[4],
                    'pdb' => $record[5],
                    'gene' => $record[6]
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

    function render_referred_pages($p) {
        $pages = $this->search_referred_pages($p);
        $out = '';
        foreach ($pages as $page) {
            if (!empty($out)) {
                $out .= '<br/>';
            }
            $out .= '<a href="/?page_id=' . $page->ID . '">' . $page->post_title . '</a>';
        }
        return $out;
    }

    static function render_link($p) {
        $pdb = '';
        if ($p['pdb']) {
            $pdb = '<a href="http://www.ebi.ac.uk/pdbe/entry/pdb/' . $p['pdb'] . '" class="protein-link pdb" target="pdb"></a>';
        }
        $gene = '';
        $hgene = '';
        if ($p['gene']) {
            $gene = '<a href="http://www.ncbi.nlm.nih.gov/gene/?term=' . $p['gene'] . '" class="protein-link ncbi-gene" target="ncbi"></a>';
            $hgene = '<a href="http://www.ncbi.nlm.nih.gov/homologene?LinkName=gene_homologene&from_uid=' . $p['gene'] . '" class="protein-link homologene" target="ncbi"></a>';
        }

        return '<a href="http://www.uniprot.org/uniprot/' . $p['uniprot'] . '" class="protein-link uniprot" target="uniprot"></a>' .
            '<a href="http://www.ebi.ac.uk/QuickGO/GProtein?ac=' . $p['uniprot'] . '" class="protein-link quickgo" target="quickgo"></a>' .
            '<a href="http://pfam.xfam.org/protein/' . $p['uniprot'] . '" class="protein-link pfam" target="pfam"></a>' .
            '<a href="http://www.ncbi.nlm.nih.gov/protein/' . $p['uniprot'] . '" class="protein-link ncbi-protein" target="ncbi"></a>' .
            $pdb .
            $gene .
            $hgene;
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
