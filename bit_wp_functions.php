<?php


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