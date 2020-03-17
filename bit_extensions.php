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
include_once( plugin_dir_path( __FILE__ ) . 'bit_db_functions.php');
include_once( plugin_dir_path( __FILE__ ) . 'bit_wp_functions.php');




function bit_scripts() {
	wp_enqueue_script('bit_admin', plugin_dir_url( __FILE__) . '/bit_admin.js', array('jquery'), PLUGIN_VERSION, false);
	wp_localize_script( 'bit_admin', 'bit', array(
												'ajaxurl' => admin_url('admin-ajax.php')
												)
	);	
}

add_action('admin_enqueue_scripts', 'bit_scripts', 0, 0);

add_action( 'wp_ajax_nopriv_bit_get_mediazone', 'bit_get_mediazone');
add_action( 'wp_ajax_bit_get_mediazone', 'bit_get_mediazone');



function bit_get_mediazone( ) {
	$media = $_POST['params'];
	$id = $_POST['id'];
	$medias = explode(',', $media);
	$output = '';
	
	$carouselID = 'mediaitems-' . $id;

	$output .= '<div class="col-md-12">';
	$output .= '<h3 class="mediazone-title">Materiales asociados</h3>';

	$output .= '<div class="mediaitems-gallery" id="' . $carouselID . '">';

	foreach($medias as $media):
		$media = str_replace(' ', '', $media);
		$resource = bit_get_resource($media);
		$tipomaterial = sanitize_title( $resource->tipo_material );

		$output .= '<div class="media-item type-' . $tipomaterial . '" data-toggle="modal" data-target="#modal-media-text" data-type="' . $tipomaterial . '" data-mediaid="'. $resource->mediaid .'">';
		$output .= '<span class="mediaicon">' . bit_return_mediaicon( $tipomaterial ) . '</span>';
		$output .= '<div class="media-item-text">';
		$output .= $resource->mediaid;
		$output .= '</div>';
		$output .= '</div>';

	endforeach;

	$output .= '</div></div>';

	echo $output;
	die();
}

add_action( 'wp_ajax_nopriv_bit_get_all_mediazone', 'bit_get_all_mediazone');
add_action( 'wp_ajax_bit_get_all_mediazone', 'bit_get_all_mediazone');

function bit_get_all_mediazone() {
	$playid = $_POST['playid'];
	$all = $_POST['all'];
	$type = $_POST['type'];
	$tax = $_POST['tax'];
	$output = '';

	$output .= '<div class="mediaitems-gallery">';
	if($all == true) {
		$args = array(
			'post_type' 	=> 'attachment',
			'meta_key'		=> '_bit_play_asoc',
			'meta_value' 	=> $playid,
			'numberposts'	=> -1
		);
		$medias = get_posts($args);

		foreach($medias as $media) {
			$mediaid = get_post_meta($media->ID, '_bit_mediaid', true);
			$tipomaterial = sanitize_title(get_post_meta($media->ID, '_bit_tipomaterial', true));

			$output .= '<div class="media-item type-' . $tipomaterial . '" data-toggle="modal" data-target="#modal-media-text-materiales" data-type="' . $tipomaterial . '" data-mediaid="'. $mediaid .'">';
			$output .= '<span class="mediaicon">' . bit_return_mediaicon( $tipomaterial ) . '</span>';
			$output .= '<div class="media-item-text">';
			$output .= $mediaid;
			$output .= '</div>';
			$output .= '</div>';
		}
	}

	$output .= '</div>';

	echo $output;
	die();

}


add_action( 'wp_ajax_nopriv_bit_get_mediapage', 'bit_get_mediapage');
add_action( 'wp_ajax_bit_get_mediapage', 'bit_get_mediapage');

function bit_get_mediapage() {
	$pageid = $_POST['pageid'];
	$output = '';

	$output .= '<div class="mediaitems-gallery">';
		$args = array(
			'post_type' 	=> 'attachment',
			'post_parent'	=> $pageid,
			'numberposts'	=> -1
		);
		$medias = get_posts($args);

		foreach($medias as $media) {
			$mediaid = $media->ID;
			$tipomaterial = get_post_mime_type( $media->ID );

			$output .= '<div class="media-item type-' . $tipomaterial . '" data-toggle="modal" data-target="#modal-media-text-materiales" data-type="' . $tipomaterial . '" data-mediaid="'. $mediaid .'">';
			$output .= '<span class="mediaicon">' . bit_return_mediaicon( $tipomaterial ) . '</span>';
			$output .= '<div class="media-item-text">';
			$output .= $mediaid;
			$output .= '</div>';
			$output .= '</div>';
		}

	$output .= '</div>';

	echo $output;
	die();

}

function bit_return_mediaicon( $type ) {

	switch($type) {
		case('fotografia'):
		case('image/jpeg'):
		$icon = '<i class="fas fa-image"></i>';
		break;
		case('video'):
		case('video/m4v'):
		$icon = '<i class="fas fa-film"></i>';
		break;
		case('audio'):
		case('audio/mp3'):
		$icon = '<i class="fas fa-music"></i>';
		break;
		case('papeleria'):
		case('documentos'):
		case('application/pdf'):
		$icon = '<i class="fas fa-file-invoice"></i>';
		break;
		case('boceto-3d'):
		$icon = '<i class="fas fa-cube"></i>';
		break;
		default:
		$icon = '<i class="fas fa-file"></i>';
		break;
	}

	return $icon;
}

function bit_mime_equivs( $type ) {

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



function bit_get_mediafolder( $playid ) {
	$uploads_folder = wp_get_upload_dir();
	$playslug = get_term_by( 'id', $playid, 'obra' );

	return $uploads_folder['baseurl'] . '/media_obras/' . $playslug->slug . '/';
}

function bit_get_single_media_item( $mediaid ) {
	$item = bit_get_resource( $mediaid );
	if($item):
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
	else:
		$mediaitem = 'error';
	endif;
	return $mediaitem;
}

function bit_get_image( $resource ) {
	$image = bit_get_media_from_wp( $resource, '.jpg' );
	$imgurl = wp_get_attachment_image_url( $image[0]->ID, 'medium' );
	$imgelement = '<img class="text-related-image" src="' . $imgurl . '" alt="' . $image[0]->post_title . '" title="' . $image[0]->post_title . '">';

	//return 'postid:' . $image;
	return $imgelement;
	
}


add_action( 'wp_ajax_nopriv_bit_ajax_get_media', 'bit_ajax_get_media');
add_action( 'wp_ajax_bit_ajax_get_media', 'bit_ajax_get_media');

function bit_ajax_get_media() {

	$resourceid = $_POST['mediaid'];
	$type = $_POST['type'];
	$ispage = $_POST['ispage'];
	
	if($ispage == true):
		$args = array(
			'p' => $resourceid,
			'post_type' => 'attachment'
		);
	else:
		$args = array(
			'post_type' => 'attachment',
			'numberposts' => 1,
			'meta_key'  => '_bit_mediaid',
			'meta_value' => $resourceid
		);
	endif;

	$mediaitem = get_posts($args);
	if($mediaitem) {
		$mediaurl = wp_get_attachment_url( $mediaitem[0]->ID );
		switch($type) {
			case('fotografia'):
			case('image/jpeg'):
			case('image/png'):
			$output = '<img src="' . $mediaurl . '" alt="">';
			break;
			case('video'):
			case('video/m4v'):
			$output = do_shortcode('[video src="' . $mediaurl . '"]');
			break;
			case('audio'):
			case('audio/mp3'):
			$output = do_shortcode('[audio src="' . $mediaurl . '"]');
			break;
			case('documentos'):
			case('application/pdf'):
			$output = '<a href="' . $mediaurl . '"><i class="fas fa-download"></i> Descargar documento</a>';
			break;
			case('boceto-3d'):
			$output = '<img src="' . $mediaurl . '" alt="">';
			break;
			default:
			$output = '<a href="' . $mediaurl . '"><i class="fas fa-download"></i> Descargar documento</a>';
			break;

		}
			$jsoninfo = array(
				'post_title' => $mediaitem[0]->post_title
			);
			$jsonmediaitem = json_encode($jsoninfo, JSON_FORCE_OBJECT);
			$output .= "<script>
		 	 			//<![CDATA[
						var ispage = '{$ispage}';
						var mediaitem='{$jsonmediaitem}';
						//]]>
					</script>";
	}

	echo $output;
	die();
}

function bit_get_video( $resource ) {
	// Returns post object from a video, in case is in the database but not in wordpress posts it creates a new post with the stuff.

	$video = bit_get_media_from_wp( $resource, '.m4v' );
	if(isset($video[0]->ID)):
		$vidurl = wp_get_attachment_url( $video[0]->ID );
		$vidplayer = do_shortcode('[video src="'.$vidurl.'"]');
		return $vidplayer;
	endif;
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
			$composed_resource = '';
		}

		return $composed_resource;
	}

	function bit_separate_extension( $type ) {
		switch($type) {
			case('fotografia'):
			$extension = '.jpg';
			break;
			case('video'):
			$extension = '.m4v';
			break;
			case('documentos'):
			$extension = '.pdf';
			break;
			case('audio'):
			$extension = '.mp3';
			break;
			case('papeleria'):
			$extension = '.pdf';
			break;
			case('boceto-3d'):
			$extension = '.jpg';
			break;	
		}
		return $extension;
	}

	function bit_mediaitemsgallery() {
	//Devuelve un lote de cuadraditos con interfaces apropiadas para cada cuadradito (foto, audio, video, etc)
	}


// Funciones para revisar todos los materiales por tipo

// Listado de fotografias

// Listado de videos

// Listado de audios

// Listado de documentos

// Listado de bocetos 3d

// Funciones para revisar todos los materiales por categoría

