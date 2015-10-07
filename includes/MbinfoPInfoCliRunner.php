<?php

/**
 * Plugin Name: MbInfo PInfo CLI Runner
 * Version: 1.0
 * Description: Load image file from Google Cloud Storage as WordPress figure page.
 * Author: Kyaw Tun
 * Author URI: http://mbinfo.mbi.nus.edu.sg
 */


require_once 'pinfo.php';


class MbinfoPInfoCliRunner extends WP_CLI_Command {
	/**
	 * Prints figure statistic.
	 *
	 *
	 * ## EXAMPLES
	 *
	 *     wp mbinfo-pinfo statistic
	 *
	 * @synopsis
	 */
	function statistic( $args, $assoc_args ) {
		$pinfo = new MBInfoPInfo();
		$ans   = $pinfo->get_info();
		$cnt   = $ans['count'];
		WP_CLI::line( "$cnt protein records" );
		WP_CLI::success( "Done!" );
	}

	/**
	 * Prints figure statistic.
	 *
	 * ## OPTIONS
	 *
	 * [<limit>]
	 * : max number of protien to show, default to 5.
	 *
	 * [<offset>]
	 * : offset, default to 0.
	 *
	 * ## EXAMPLES
	 *
	 *     wp mbinfo-pinfo info P25054
	 *     wp mbinfo-pinfo info --offset=10
	 *
	 * @synopsis
	 */
	function info( $args, $assoc_args ) {
		$pinfo = new MBInfoPInfo();
		$uniprot = $args[0];
		if (empty($uniprot)) {
			$limit = isset($assoc_args['limit']) ? intval($assoc_args['limit']) : 5;
			$offset = isset($assoc_args['offset']) ? intval($assoc_args['offset']) : 0;
			$arr = $pinfo->list_record($limit, $offset);
			var_dump($arr);
		} else {
			$r = $pinfo->get_record($uniprot);
			var_dump($r);
		}
	}

	/**
	 * Load images files meta data to wordpress.
	 *
	 * ## OPTIONS
	 *
	 * <clean>
	 * : Clear all previous record.
	 *
	 * ## EXAMPLES
	 *
	 *     wp mbinfo-pinfo load
	 *
	 * @synopsis [--clean]
	 */
	function load( $args, $assoc_args ) {
		$pinfo = new MBInfoPInfo();
		if (isset($assoc_args['clean'])) {
			$pinfo->clear_data();
		}
		$fn1 = 'pinfo/p2.csv';
		WP_CLI::line("Loading $fn1");
		$cnt = $pinfo->insert_from_gcs($fn1);
		WP_CLI::line("$cnt loaded from $fn1");
		WP_CLI::success( "Done!" );
	}


	/**
	 * Clean figure pages.
	 *
	 * ## OPTIONS
	 *
	 * <dry-run>
	 * : Dry run instead of actual deleting figure pages.
	 *
	 * <purge-all>
	 * : Purge all figure pages.
	 *
	 * ## EXAMPLES
	 *
	 *     wp mbinfo-pinfo clean
	 *
	 * @synopsis [--dry-run] [--purge-all]
	 */
	function clean( $args, $assoc_args ) {
		$pinfo = new MBInfoPInfo();
		$pinfo->clear_data();
		WP_CLI::success( "Done!" );
	}

}


WP_CLI::add_command( 'mbinfo-pinfo', 'MbinfoPInfoCliRunner' );
