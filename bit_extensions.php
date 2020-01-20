<?php
/**
 * Plugin Name:       Extensiones Bitacora Teatro UC
 * Plugin URI:        https://example.com/plugins/the-basics/
 * Description:       Sistemas para textos y otras funcionalidades
 * Version:           0.0.1
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Pablo Selín Carrasco Armijo 
 * Author URI:        https://apie.cl
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       bit
 * Domain Path:       /lang
 */


define( 'PLUGIN_VERSION', '0.0.1' );
define( 'DB_VERSION', '0.0.1' );
define( 'BIT_TABLENAME', 'texto_dramatico');

include_once( plugin_dir_path( __FILE__ ) . 'admin-interface.php');


function bit_create_tables() {
		global $wpdb;
		$dbver = BIT_TABLENAME;
		$actver = get_option('bit_dbver');
		
		if(!get_site_option('bit_dbver') || $dbver != get_site_option('bit_dbver')) {
			$table_name = $wpdb->prefix . BIT_TABLENAME;
			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE $table_name(
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			unidad mediumint(9) NOT NULL,
			ids_asoc tinytext NOT NULL,
			texto text NOT NULL,
			tipo tinytext NOT NULL,
			personajes text NOT NULL,
			time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			data text NOT NULL,
			confirmed boolean NOT NULL,
			UNIQUE KEY id (id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta($sql);
		add_option('bit_dbver', $dbver);
		}
	}

function bit_plugin_activation() {
	//Inicializar tablas
	bit_create_tables();
}

register_activation_hook( __FILE__, 'bit_plugin_activation' );

function bit_populate_text_tables( $data ) {
	//upload data to text tables
}

function bit_populate_media_tables( $data ) {
	//upload data to media tables
}

function bit_correlate_media( $mediaid ) {
	//correlate media with actual resource
}

function bit_get_line( $lineid ) {
	//get text line and metadata
}

function bit_get_play( $playid ) {
	//get intro for play
}

function bit_get_media( $mediaid ) {
	//get media from mediaid
}

