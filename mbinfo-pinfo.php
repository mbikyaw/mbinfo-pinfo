<?php
/**
 * Plugin Name: Protein Info
 * Plugin URI: https://github.com/mbikyaw/mbinfo-pinfo
 * Description: Display protein widget.
 * Version: 1.2
 * Author: Kyaw Tun
 * Author URI: https://github.com/mbikyaw/
 * License: MIT
 */


require_once 'includes/pinfo.php';
require_once 'includes/PInfoWidget.php';


register_activation_hook( __FILE__, 'mbinfo_pinfo_install' );
add_action( 'wp_enqueue_scripts', 'mbinfo_pinfo_enqueue_scripts' );


function mbinfo_pinfo_install() {
	global $mbinfo_pinfo_db_version;

	$existing = get_site_option( 'mbinfo_pinfo_db_version' );
	if ( $existing != $mbinfo_pinfo_db_version ) {
		error_log( 'MBInfoFigure: running mbinfo_pinfo_install ' . $existing . ' to ' . $mbinfo_pinfo_db_version );
		$pinfo = new MBInfoPInfo();
		$pinfo->update_to_v11();
	}
}


function mbinfo_pinfo_enqueue_scripts() {
	$css_url = plugins_url( 'css/mbinfo-pinfo.css', __FILE__ );
	$js_url = plugins_url( 'js/mbinfo-pinfo.js', __FILE__ );
	wp_enqueue_style( 'mbinfo-pinfo-css', $css_url, false, '0.2.0', 'screen' );
	wp_enqueue_script( 'mbinfo-pinfo-js', $js_url, false, '0.1.0', 'screen' );
}


if ( defined( 'WP_CLI' ) && WP_CLI ) {
	include __DIR__ . '/includes/MbinfoPInfoCliRunner.php';
}