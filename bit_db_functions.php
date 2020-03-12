<?php


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


function bit_get_play( $playid ) {
	//get intro for play
	global $wpdb;
	$tablename = BIT_TABLENAME;

	$results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}{$tablename} WHERE play_asoc = {$playid}", OBJECT);

	return $results;
}

function bit_get_all_media_from_db( $playid ) {
	//get all media from playid
	global $wpdb;
	$media_tablename = BIT_MEDIATABLENAME;

	$results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}{$media_tablename} WHERE play_asoc = {$playid}", OBJECT);

	return $results;
}

function bit_get_mediatype( $playid, $type ) {
	//get all media for a type associated to play
	global $wpdb;
	$media_tablename = BIT_MEDIATABLENAME;

	$materialtype = bit_convert_typename($type);

	$results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}{$media_tablename} WHERE tipo_material = '{$materialtype}' AND play_asoc = '{$playid}'");

	return $results;
}

function bit_convert_typename( $type ) {
	switch($type) {
		case('fotografia'):
		return 'Fotografía';
		break;
		case('video'):
		return 'Video';
		case('documentos'):
		return 'Documentos';
		break;
		case('audio'):
		return 'Audio';
		break;
		case('papeleria'):
		return 'Papelería';
		break;
		case('bocetos'):
		return 'Boceto 3D';
		break;
	}
}

	function bit_get_resource( $mediaid ) {
	//gets a media info by mediaid
		global $wpdb;
		$media_tablename = BIT_MEDIATABLENAME;

		$results = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}{$media_tablename} WHERE mediaid LIKE '{$mediaid}'", OBJECT);

		return $results;
	}
