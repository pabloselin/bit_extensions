<?php
/**
 * Plugin Name:       Extensiones Bitacora Teatro UC
 * Plugin URI:        https://example.com/plugins/the-basics/
 * Description:       Sistemas para textos y otras funcionalidades
 * Version:           0.0.7
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Pablo Selín Carrasco Armijo 
 * Author URI:        https://apie.cl
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       bit
 * Domain Path:       /lang
 */


define( 'PLUGIN_VERSION', '0.0.8' );
define( 'BIT_DB_VERSION', '0.0.16' );
define( 'BIT_TABLENAME', 'texto_dramatico');
define( 'BIT_MEDIATABLENAME', 'archivo_medios');

include_once( plugin_dir_path( __FILE__ ) . 'admin-interface.php');


function bit_create_tables() {
		global $wpdb;
		$dbver = BIT_DB_VERSION;
		$actver = get_option('bit_dbver');
		
		if(!get_site_option('bit_dbver') || $dbver != get_site_option('bit_dbver')) {
			$table_name = $wpdb->prefix . BIT_TABLENAME;
			$mediatable_name = $wpdb->prefix . BIT_MEDIATABLENAME;
			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE $table_name(
						id mediumint(9) NOT NULL AUTO_INCREMENT,
						unidad mediumint(9) NOT NULL,
						ids_asoc tinytext NOT NULL,
						play_asoc int NOT NULL,
						texto text NOT NULL,
						tipo tinytext NOT NULL,
						personajes text NOT NULL,
						UNIQUE KEY id (id)
					) $charset_collate;
					CREATE TABLE $mediatable_name(
						id mediumint(9) NOT NULL AUTO_INCREMENT,
						mediaid tinytext NOT NULL,
						categoria text NOT NULL,
						tipo_material text NOT NULL,
						fecha_text tinytext NOT NULL,
						descripcion_sintetica text NOT NULL,
						descripcion_detallada text NOT NULL,
						ingreso text NOT NULL,
						procesamiento text NOT NULL,
						fuente text NOT NULL,
						play_asoc int NOT NULL,
						UNIQUE KEY id (id)
					) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta($sql);
		update_option('bit_dbver', $dbver);
		}
	}

function bit_create_mediatables() {
		global $wpdb;
		$dbver = BIT_DB_VERSION;
		$actver = get_option('bit_dbver');
		
		if(!get_site_option('bit_dbver') || $dbver != get_site_option('bit_dbver')) {
			$table_name = $wpdb->prefix . BIT_MEDIATABLENAME;
			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE $table_name(
						id mediumint(9) NOT NULL AUTO_INCREMENT,
						mediaid mediumint(9) NOT NULL,
						categoria text NOT NULL,
						tipo_material text NOT NULL,
						fecha_text tinytext NOT NULL,
						descripcion_sintetica text NOT NULL,
						descripcion_detallada text NOT NULL,
						ingreso text NOT NULL,
						procesamiento text NOT NULL,
						fuente text NOT NULL,
						UNIQUE KEY id (id)
					) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta($sql);
		update_option('bit_dbver', $dbver);
		}
	}

function bit_update_db_check() {
    if ( get_site_option( 'bit_dbver' ) != BIT_DB_VERSION ) {
        bit_create_tables();
        //bit_create_mediatables();
    }
}

add_action( 'plugins_loaded', 'bit_update_db_check' );

register_activation_hook( __FILE__, 'bit_update_db_check' );

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
	global $wpdb;
	$tablename = BIT_TABLENAME;

	$results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}{$tablename} WHERE play_asoc = {$playid}", OBJECT);

	return $results;
}

function bit_get_media( $playid ) {
	//get all media from playid
	global $wpdb;
	$media_tablename = BIT_MEDIATABLENAME;

	$results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}{$media_tablename} WHERE play_asoc = {$playid}", OBJECT);

	return $results;
}

function bit_get_image( $mediaid, $playslug ) {
	$uploads_folder = wp_get_upload_dir();
	$uploadsplay = $uploads_folder['baseurl'] . '/media_obras/' . $playslug . '/' . $mediaid . '.jpg';
	return $uploadsplay;
}

function bit_get_video( $mediaid ) {

}

function bit_get_audio( $mediaid ) {

}

function bit_get_documento( $mediaid ) {

}

function bit_get_papeleria( $mediaid ) {

}

function bit_get_boceto3d( $mediaid ) {

}

function bit_get_resource( $mediaid, $playslug ) {
	//gets a media info by mediaid
	global $wpdb;
	$media_tablename = BIT_MEDIATABLENAME;

	$results = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}{$media_tablename} WHERE mediaid LIKE '{$mediaid}'", OBJECT);
	
	return $results;
}

function bit_separate_resource( $resource ) {
	$type = sanitize_title( $resource->tipo_material );

	switch($type) {
		case('fotografia'):
		break;
		case('video'):
		break;
		case('documentos'):
		break;
		case('audio'):
		break;
		case('papeleria'):
		break;
		case('boceto-3d'):
		break;
	}

	return $composed_resource;
}