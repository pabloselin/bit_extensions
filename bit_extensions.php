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

add_action( 'wp_ajax_nopriv_bit_render_mediazone', 'bit_render_mediazone');
add_action( 'wp_ajax_bit_render_mediazone', 'bit_render_mediazone');

function bit_render_mediazone( ) {
	$media = $_POST['params'];
	$id = $_POST['id'];
	$medias = explode(',', $media);
	$output = '';
	if(count($medias) > 1):
		$carouselID = 'mediacarousel-' . $id;

		$output .= '<div class="carousel" id="' . $carouselID . '">';

			foreach($medias as $media):
				$media = str_replace(' ', '', $media);
				$resource = bit_get_single_media_item($media);
	
				$output .= '<div class="carousel-item">' . $resource . '</div>';
				
			endforeach;
		
		$output .= '</div>';
	else:
		foreach($medias as $media) {
			$media = str_replace(' ', '', $media);
			$resource = bit_get_single_media_item($media);

			$output .= $resource;

		}
	endif;

echo $output;
die();
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

function bit_get_mediatype( $playid, $type ) {
	//get all media for a type associated to play
	global $wpdb;
	$media_tablename = BIT_MEDIATABLENAME;

	$materialtype = bit_convert_typename($type);

	$results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}{$media_tablename} WHERE tipo_material = '{$materialtype}'");

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
		case('boceto-3d'):
			return 'Boceto 3D';
		break;
	}
}

function bit_get_mediafolder( $playid ) {
	$uploads_folder = wp_get_upload_dir();
	$playslug = get_term_by( 'id', $playid, 'obra' );

	return $uploads_folder['baseurl'] . '/media_obras/' . $playslug->slug . '/';
}

function bit_get_single_media_item( $mediaid ) {
	$item = bit_get_resource( $mediaid );
	$mediaitem = '<div class="media-item-wrapper">';
	$mediaitem .= bit_separate_resource( $item );
	$mediaitem .= '<div class="media-caption">
						<p class="item-descsint">' . $item->descripcion_sintetica . '</p>
						<p class="item-descext">' . $item->descripcion_detallada . '</p>
						<p class="item-cat">' . $item->categoria . '</p>
						<p class="item-fecha">' . $item->fecha_text . '</p>
						<p class="item-fuente"><span class="label">Fuente: </span>' . $item->fuente . '</p>
					</div>';
	$mediaitem .= '</div>';
	return $mediaitem;
}

function bit_get_image( $resource ) {
	$imgurl = bit_get_mediafolder($resource->play_asoc) . $resource->mediaid . '.jpg';
	$imgelement = '<img class="text-related-image" src="' . $imgurl . '" alt="' . $resource->descripcion_detallada . '" title="' . $resource->descripcion_sintetica . '">';
	
	return $imgelement;
}

function bit_get_video( $resource ) {
	$vidurl = bit_get_mediafolder($resource->play_asoc) . $resource->mediaid . '.m4v';
	$videlement = '<video controls="true" src="' . $vidurl .'"></video>';
	
	return $videlement;
}

function bit_get_audio( $resource ) {
	$audiourl = bit_get_mediafolder($resource->play_asoc) . $resource->mediaid . '.mp3';
	$audioid = 'audio_' . $resource->mediaid;
	$audioelement = '<div class="audiocontainer">';
	$audioelement .= '<div class="wavecontainer" id="' . $audioid . '" data-audio="' . $audiourl . '"></div>';
	$audioelement .= '<div class="controls"><button class="btn btn-primary" data-action="play-' . $audioid . '"><i class="fas fa-play"></i> | <i class="fas fa-pause"></i></button></div>';
	$audioelement .= '<script>var wavesurfer_' . $audioid . ' = WaveSurfer.create({
		container: "#' . $audioid . '",
		waveColor: "#555",
		progressColor: "#333",
		barRadius: 1,
		barWidth: 3
	});
	wavesurfer_' . $audioid . '.load("' . $audiourl . '");
	// Play button
    var button_' . $audioid . ' = document.querySelector(\'[data-action="play-'. $audioid .'"]\');
    button_' .$audioid .'.addEventListener(\'click\', wavesurfer_' . $audioid  .'.playPause.bind(wavesurfer_' . $audioid . '));
	</script>';
	$audioelement .= '</div>';


	return $audioelement;
}

function bit_get_documento( $resource ) {
	$docurl = bit_get_mediafolder($resource->play_asoc) . $resource->mediaid . '.pdf';
	$docelement = '<a href="' . $docurl . '" target="_blank" class="btn btn-primary document-download-button"><i class="fas fa-download"></i> Descargar documento (pdf)</a>';

	return $docelement;
}

function bit_get_papeleria( $resource ) {
	$docelement = bit_get_documento($resource);

	return $docelement;
}

function bit_get_boceto3d( $resource ) {
	$imgelement = bit_get_image( $resource );

	return $imgelement; 
}

function bit_get_resource( $mediaid ) {
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
		$composed_resource = bit_get_image($resource);
		break;
		case('video'):
		$composed_resource = bit_get_video($resource);
		break;
		case('documentos'):
		$composed_resource = bit_get_documento($resource);
		break;
		case('audio'):
		$composed_resource = bit_get_audio($resource);
		break;
		case('papeleria'):
		$composed_resource = bit_get_papeleria($resource);
		break;
		case('boceto-3d'):
		$composed_resource = bit_get_boceto3d($resource);
		break;
		default:
		$composed_resource = bit_get_image($resource);
	}

	return $composed_resource;
}

// Funciones para revisar todos los materiales por tipo

// Listado de fotografias

// Listado de videos

// Listado de audios

// Listado de documentos

// Listado de bocetos 3d

// Funciones para revisar todos los materiales por categoría

