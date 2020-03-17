<?php


function bit_item_data_terms( $postid ) {
	/** Genera un output de todos los terms asociados a un attachment, en atributos data-taxonomy **/

	$output = '';

	$taxonomies = get_taxonomies( );

	foreach($taxonomies as $taxonomy) {
		$terms = get_the_terms( $postid, $taxonomy );
		if($terms) {
			$termsarray = [];
				
			foreach($terms as $term) {
				$termsarray[] = $term->slug;
			}

			$output .= ' data-' . $taxonomy . '="' . implode(', ', $termsarray) . '"';
		}
	}

	return $output;
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

function bit_get_mediafolder( $playid ) {
	$uploads_folder = wp_get_upload_dir();
	$playslug = get_term_by( 'id', $playid, 'obra' );

	return $uploads_folder['baseurl'] . '/media_obras/' . $playslug->slug . '/';
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