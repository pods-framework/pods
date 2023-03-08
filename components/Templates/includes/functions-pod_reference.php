<?php

add_action( 'wp_ajax_pq_loadpod', 'pq_loadpod' );

/**
 * @param bool $podname
 *
 * @return array
 */
function pq_loadpod( $podname = false ) {

	if ( ! pods_is_admin() ) {
		pods_error( __( 'Unauthorized request', 'pods' ) );
	}
	if ( ! empty( $_POST['pod_reference']['pod'] ) ) {
		$podname = $_POST['pod_reference']['pod'];
	}
	if ( ! empty( $_POST['pod'] ) ) {
		$podname = $_POST['pod'];
	}
	$fields = array( __( 'No reference Pod selected', 'pods' ) );

	if ( ! empty( $podname ) ) {
		$fields = pq_recurse_pod_fields( $podname );
	}
	if ( ! empty( $_POST['pod_reference']['pod'] ) || ! empty( $_POST['pod'] ) ) {
		header( 'Content-Type:application/json' );
		echo json_encode( $fields );
		die;
	}

	return $fields;
}

/**
 * @param        $pod_name
 * @param string   $prefix
 * @param array    $pods_visited
 *
 * @return array
 */
function pq_recurse_pod_fields( $pod_name, $prefix = '', &$pods_visited = array() ) {

	$fields = array();
	if ( empty( $pod_name ) ) {
		return $fields;
	}

	$pod = pods_get_instance( $pod_name );

	if ( empty( $pod ) || ! $pod->valid() ) {
		return $fields;
	}

	$recurse_queue = array();

	$image_sizes  = get_intermediate_image_sizes();
	$media_fields = [
		'title',
		'caption',
		'description',
		'alt_text',
		'width',
		'height',
		'filesize',
		'filename',
		'extension',
		'mime_type',
	];

	if ( post_type_supports( $pod_name, 'thumbnail' ) ) {
		$fields[] = "{$prefix}post_thumbnail";
		$fields[] = "{$prefix}post_thumbnail_url";

		foreach ( $media_fields as $media_field ) {
			$fields[] = "{$prefix}post_thumbnail.{$media_field}";
		}

		foreach ( $image_sizes as $image_size ) {
			$fields[] = "{$prefix}post_thumbnail.{$image_size}";
			$fields[] = "{$prefix}post_thumbnail_url.{$image_size}";
		}
	}

	$pod_fields = $pod->fields();

	foreach ( $pod_fields as $name => $field ) {
		// Add base field name
		$fields[] = $prefix . $name;

		// Field type specific handling
		if ( 'file' === $field['type'] && 'attachment' === $field['options']['file_uploader'] ) {
			$fields[] = $prefix . $name . '._src';
			$fields[] = $prefix . $name . '._img';

			foreach ( $media_fields as $media_field ) {
				$fields[] = "{$prefix}{$name}._img.{$media_field}";
			}

			foreach ( $image_sizes as $image_size ) {
				$fields[] = "{$prefix}{$name}._src.{$image_size}";

				if ( 'multi' !== $field['options']['file_format_type'] ) {
					$fields[] = "{$prefix}{$name}._src_relative.{$image_size}";
					$fields[] = "{$prefix}{$name}._src_schemeless.{$image_size}";
				}

				$fields[] = "{$prefix}{$name}._img.{$image_size}";
			}
		} elseif ( ! empty( $field['table_info'] ) && ! empty( $field['table_info']['pod'] ) ) {
			$linked_pod = $field['table_info']['pod']['name'];
			if ( ! isset( $pods_visited[ $linked_pod ] ) || ! in_array( $name, $pods_visited[ $linked_pod ], true ) ) {
				$pods_visited[ $linked_pod ][] = $name;
				$recurse_queue[ $linked_pod ]  = "{$prefix}{$name}.";
			}
		}//end if
	}//end foreach
	foreach ( $recurse_queue as $recurse_name => $recurse_prefix ) {
		$fields = array_merge( $fields, pq_recurse_pod_fields( $recurse_name, $recurse_prefix, $pods_visited ) );
	}

	return $fields;
}

