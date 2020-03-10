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


function bit_get_gallery( $playid, $type ) {
	$output = '';
	$fotos = bit_get_mediatype($playid, $type );

	if($fotos):
		$output .= '<div class="bit-gallery">';
		foreach($fotos as $foto):
			$output .= '<div class="image-wrap">';
			$output .= bit_get_image($foto);
			$output .= '</div>';
		endforeach;
		$output .= '</div>';
	endif;

	return $output;
}

add_action( 'wp_ajax_nopriv_bit_get_media', 'bit_get_media');
add_action( 'wp_ajax_bit_get_media', 'bit_get_media');


function bit_get_media( ) {
	$playid = $_POST['playid'];
	$type = $_POST['getType'];

	$output = '';

	switch($type) {
		case('gallery'):
			$output .= bit_get_gallery( $playid, 'fotografia' );
		break;
		case('bocetos'):
			$output .= bit_get_gallery( $playid, 'bocetos' );
		break;
		case('papeleria'):
			$output .= bit_get_gallery( $playid, 'papeleria' );
		break;
		default:
			$output .= $type . ': Content not ready yet';
	}

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
		case('bocetos'):
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
	$image = bit_get_media_from_wp( $resource, '.jpg' );
	$imgurl = wp_get_attachment_image_url( $image[0]->ID, 'medium' );
	$imgelement = '<img class="text-related-image" src="' . $imgurl . '" alt="' . $image[0]->post_title . '" title="' . $image[0]->post_title . '">';

	//return 'postid:' . $image;
	return $imgelement;
	
}

function bit_get_video( $resource ) {
	// Returns post object from a video, in case is in the database but not in wordpress posts it creates a new post with the stuff.

	$video = bit_get_media_from_wp( $resource, '.m4v' );
	$vidurl = wp_get_attachment_url( $video[0]->ID );
	return $video[0];
}

function bit_get_video_player( $videoid ) {

	return $videoelement;
}

function bit_get_audio( $resource ) {
	$audio = bit_get_media_from_wp( $resource, '.mp3');
	$audiourl = wp_get_attachment_url( $audio[0]->ID );
	$audioid = 'audio_' . $audio[0]->ID;
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
		$documento = bit_get_media_from_wp( $resource, '.pdf' );
		$docurl = wp_get_attachment_url( $documento[0]->ID );
		
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

	function bit_get_media_from_wp( $resource, $extension = '.jpg' ) {
		
		$args = array(
			'post_type' => 'attachment',
			'numberposts' => 1,
			'meta_key'  => '_bit_mediaid',
			'meta_value' => $resource->mediaid
		);


		$media = get_posts($args);
		
		if(empty($media)) {
			$id = bit_assign_resource_to_wp( $resource, $extension );
			if($id) {
				$args = array(
					'posts__in' => $id
				);
				$wpresource = get_posts($args);
			}
		} else {
			$wpresource = $media;
		}

		//return $media[0]->ID;
		return $wpresource;
	}

	function bit_assign_resource_to_wp( $resource, $extension = '.jpg' ) {
		$image_url = bit_get_mediafolder($resource->play_asoc) . $resource->mediaid . $extension;
		$upload_dir = wp_upload_dir();

		$image_data = file_get_contents( $image_url );
		$filename = basename( $image_url );

		if ( wp_mkdir_p( $upload_dir['path'] ) ) {
			$file = $upload_dir['path'] . '/' . $filename;
		}
		else {
			$file = $upload_dir['basedir'] . '/' . $filename;
		}

		if($image_data != false) {
			file_put_contents($file, $image_data);

			$wp_filetype = wp_check_filetype( $filename, null );

			$attachment = array(
				'post_mime_type' => $wp_filetype['type'],
				'post_title'	 => sanitize_file_name( $filename ),
				'post_content'   => '',
				'post_status'	 => 'inherit',
				'meta_input'	 => array(
					'_bit_mediaid'		=> $resource->mediaid,
					'_bit_categoria'	=> $resource->categoria,
					'_bit_id'			=> $resource->id,
					'_bit_tipomaterial' => $resource->tipo_material,
					'_bit_fechatext'	=> $resource->fecha_text,
					'_bit_descripcion_sintetica'	=> $resource->descripcion_sintetica,
					'_bit_descripcion_detallada'	=> $resource->descripcion_detallada,
					'_bit_ingreso'		=> $resource->ingreso,
					'_bit_procesamiento'=> $resource->procesamiento,
					'_bit_fuente'		=> $resource->fuente,
					'_bit_play_asoc'	=> $resource->play_asoc
				)
			);

			$attach_id = wp_insert_attachment( $attachment, $file );
			
			require_once( ABSPATH . 'wp-admin/includes/image.php');
			require_once( ABSPATH . 'wp-admin/includes/media.php');
			
			$attach_data = wp_generate_attachment_metadata( $attach_id, $file );
			wp_update_attachment_metadata( $attach_id, $attach_data );

			return $attach_id;
		}
	}

// Funciones para revisar todos los materiales por tipo

// Listado de fotografias

// Listado de videos

// Listado de audios

// Listado de documentos

// Listado de bocetos 3d

// Funciones para revisar todos los materiales por categoría

