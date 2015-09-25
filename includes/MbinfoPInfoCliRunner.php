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
	}

	/**
	 * Load images files meta data to wordpress.
	 *
	 * ## OPTIONS
	 *
	 * <create>
	 * : Create a new figure page if not exists.
	 *
	 * ## EXAMPLES
	 *
	 *     wp mbinfo-pinfo load
	 *
	 * @synopsis [--create]
	 */
	function load( $args, $assoc_args ) {


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

	}

}


WP_CLI::add_command( 'mbinfo-pinfo', 'MbinfoPInfoCliRunner' );
