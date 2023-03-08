<?php

namespace Pods\Data;

use Pods;
use Pods\Whatsit\Field;
use Pods\Whatsit\Object_Field;

/**
 * Map_Field_Values class.
 *
 * @since 2.8.0
 */
class Map_Field_Values {

	/**
	 * Map the matching image field value.
	 *
	 * @since 2.8.0
	 *
	 * @param string                  $field      The first field name in the path.
	 * @param string[]                $traverse   The list of all fields in the path.
	 * @param null|Field|Object_Field $field_data The field data or null if not a field.
	 * @param Pods|null               $obj        The Pods object or null if not set.
	 * @param object|null             $params     The full Pods::field() parameters.
	 *
	 * @return null|mixed The matching image field value or null if there was no match.
	 */
	public function map_value( $field, $traverse, $field_data, $obj = null, $params = null ) {
		// Remove the first field from $traverse.
		if ( $field === reset( $traverse ) ) {
			array_shift( $traverse );
		}

		/**
		 * Allow filtering the field mapping ahead of running all other method checks.
		 *
		 * @since 2.8.0
		 *
		 * @param null|mixed              $value      The matching field value or null if there was no match.
		 * @param string                  $field      The first field name in the path.
		 * @param string[]                $traverse   The list of fields in the path excluding the first field name.
		 * @param null|Field|Object_Field $field_data The field data or null if not a field.
		 * @param Pods|null               $obj        The Pods object or null if not set.
		 * @param object|null             $params     The full Pods::field() parameters.
		 */
		$value = apply_filters( 'pods_data_map_field_values_map_value_pre_check', null, $field, $traverse, $field_data, $obj, $params );

		if ( null !== $value ) {
			return $value;
		}

		$methods = [
			'custom',
			'pod_info',
			'field_info',
			'display_fields',
			'context_info',
			'calculation',
			'image_fields',
			'avatar',
		];

		$method = false;

		// Go through all of the support mappings and check for a field value.
		while ( null === $value && $method = array_shift( $methods ) ) {
			$value = $this->$method( $field, $traverse, $field_data, $obj, $params );
		}

		// If no value was found, set $method to false.
		if ( null === $value ) {
			$method = false;
		}

		/**
		 * Allow filtering the field mapping.
		 *
		 * @since 2.8.0
		 *
		 * @param null|mixed              $value      The matching field value or null if there was no match.
		 * @param string                  $field      The first field name in the path.
		 * @param string[]                $traverse   The list of fields in the path excluding the first field name.
		 * @param null|Field|Object_Field $field_data The field data or null if not a field.
		 * @param Pods|null               $obj        The Pods object or null if not set.
		 * @param string|false            $method     The matching mapping method or false if there was no match.
		 * @param object|null             $params     The full Pods::field() parameters.
		 */
		return apply_filters( 'pods_data_map_field_values_map_value', $value, $field, $traverse, $field_data, $obj, $method, $params );
	}

	/**
	 * Handle custom field mapping.
	 *
	 * @since 2.8.0
	 *
	 * @param string                  $field      The first field name in the path.
	 * @param string[]                $traverse   The list of fields in the path excluding the first field name.
	 * @param null|Field|Object_Field $field_data The field data or null if not a field.
	 * @param Pods|null               $obj        The Pods object or null if not set.
	 * @param object|null             $params     The full Pods::field() parameters.
	 *
	 * @return null|mixed The matching field value or null if there was no match.
	 */
	public function custom( $field, $traverse, $field_data, $obj = null, $params = null ) {
		/**
		 * Allow filtering for a custom field mapping.
		 *
		 * @since 2.8.0
		 *
		 * @param null|mixed              $value      The matching field value or null if there was no match.
		 * @param string                  $field      The first field name in the path.
		 * @param string[]                $traverse   The list of fields in the path excluding the first field name.
		 * @param null|Field|Object_Field $field_data The field data or null if not a field.
		 * @param Pods|null               $obj        The Pods object or null if not set.
		 * @param object|null             $params     The full Pods::field() parameters.
		 */
		return apply_filters( 'pods_data_map_field_values_custom', null, $field, $traverse, $field_data, $obj, $params );
	}

	/**
	 * Map the matching pod info value.
	 *
	 * @since 2.8.0
	 *
	 * @param string                  $field      The first field name in the path.
	 * @param string[]                $traverse   The list of fields in the path excluding the first field name.
	 * @param null|Field|Object_Field $field_data The field data or null if not a field.
	 * @param Pods|null               $obj        The Pods object or null if not set.
	 *
	 * @return null|mixed The matching pod info value or null if there was no match.
	 */
	public function pod_info( $field, $traverse, $field_data, $obj = null ) {
		// Skip if the field exists.
		if ( $field_data ) {
			return null;
		}

		// Skip if not the field we are looking for.
		if ( '_pod' !== $field ) {
			return null;
		}

		// Skip if there is no Pods object.
		if ( ! $obj ) {
			return null;
		}

		$pod_option = ! empty( $traverse[0] ) ? $traverse[0] : 'name';

		return $obj->pod_data->get_arg( $pod_option );
	}

	/**
	 * Map the matching field info value.
	 *
	 * @since 2.8.0
	 *
	 * @param string                  $field      The first field name in the path.
	 * @param string[]                $traverse   The list of fields in the path excluding the first field name.
	 * @param null|Field|Object_Field $field_data The field data or null if not a field.
	 * @param Pods|null               $obj        The Pods object or null if not set.
	 *
	 * @return null|mixed The matching field info value or null if there was no match.
	 */
	public function field_info( $field, $traverse, $field_data, $obj = null ) {
		// Skip if the field exists.
		if ( $field_data ) {
			return null;
		}

		// Skip if not the field we are looking for.
		if ( '_field' !== $field ) {
			return null;
		}

		// Skip if there is no Pods object.
		if ( ! $obj ) {
			return null;
		}

		// Skip if no field was set.
		if ( empty( $traverse[0] ) ) {
			return null;
		}

		$field_match  = $traverse[0];
		$field_option = ! empty( $traverse[1] ) ? $traverse[1] : 'label';

		return $obj->fields( $field_match, $field_option );
	}

	/**
	 * Map the matching _display_fields value.
	 *
	 * @since 2.9.4
	 *
	 * @param string                  $field      The first field name in the path.
	 * @param string[]                $traverse   The list of fields in the path excluding the first field name.
	 * @param null|Field|Object_Field $field_data The field data or null if not a field.
	 * @param Pods|null               $obj        The Pods object or null if not set.
	 * @param object|null             $params     The full Pods::field() parameters.
	 *
	 * @return null|mixed The matching _display_fields value or null if there was no match.
	 */
	public function display_fields( $field, $traverse, $field_data, $obj = null, $params = null ) {
		// Skip if the field exists.
		if ( $field_data ) {
			return null;
		}

		$is_all_fields = '_all_fields' === $field;

		// Skip if not the field we are looking for.
		if ( '_display_fields' !== $field && ! $is_all_fields ) {
			return null;
		}

		// Skip if there is no Pods object.
		if ( ! $obj ) {
			return null;
		}

		$item_id = $obj->id();

		// Skip if there is no item data.
		if ( empty( $item_id ) ) {
			return null;
		}

		$output_type       = ! empty( $traverse[0] ) ? $traverse[0] : 'ul';
		$fields_to_display = '_all';

		if ( ! $is_all_fields ) {
			$fields_to_display = ( ! empty( $traverse[1] ) ? $traverse[1] : '_all' );
		}

		$pod = $obj->pod_data;

		$are_fields_excluded = 0 === strpos( $fields_to_display, 'exclude=' );

		if ( '_all' === $fields_to_display || $are_fields_excluded ) {
			$display_fields = $pod->get_fields();

			// @todo For post types -- handle checking if the post type supported title / editor, include them only if they are enabled.
			if ( ! empty( $obj->data->field_index ) ) {
				$display_field = $pod->get_field( $obj->data->field_index );

				if ( $display_field ) {
					$display_fields = array_merge( [
						$display_field->get_name() => $display_field,
					], $display_fields );
				}
			}

			if ( $are_fields_excluded ) {
				// Handle excluded fields.
				$fields_to_exclude = substr( $fields_to_display, strlen( 'exclude=' ) );
				$fields_to_exclude = explode( '|', $fields_to_exclude );
				$fields_to_exclude = array_filter( array_unique( $fields_to_exclude ) );

				foreach ( $fields_to_exclude as $field_to_exclude ) {
					$field_to_exclude = str_replace( ':', '.', $field_to_exclude );

					$field_name = explode( '.', $field_to_exclude );
					$field_name = $field_name[0];

					if ( isset( $display_fields[ $field_name ] ) ) {
						unset( $display_fields[ $field_name ] );
					}
				}
			}
		} else {
			$display_fields = [];

			// Handle included fields.
			$fields_to_display = explode( '|', $fields_to_display );
			$fields_to_display = array_filter( array_unique( $fields_to_display ) );

			foreach ( $fields_to_display as $field_to_display ) {
				$field_to_display = str_replace( ':', '.', $field_to_display );

				$field_name = explode( '.', $field_to_display );
				$field_name = $field_name[0];

				$display_field = $pod->get_field( $field_name );

				if ( $display_field ) {
					$display_fields[ $field_to_display ] = $display_field;
				}
			}
		}

		if ( 'div' === $output_type ) {
			$display_file = 'list.php';
			$list_type    = 'div';
			$tag_name     = 'div';
			$sub_tag_name = 'div';
		} elseif ( 'p' === $output_type ) {
			$display_file = 'list.php';
			$list_type    = 'p';
			$tag_name     = 'div';
			$sub_tag_name = 'p';
		} elseif ( 'table' === $output_type ) {
			$display_file = 'table.php';
			$list_type    = 'table';
			$tag_name     = 'table';
			$sub_tag_name = 'td';
		} elseif ( 'ol' === $output_type ) {
			$display_file = 'list.php';
			$list_type    = 'ol';
			$tag_name     = 'ol';
			$sub_tag_name = 'li';
		} elseif ( 'dl' === $output_type ) {
			$display_file = 'dl.php';
			$list_type    = 'dl';
			$tag_name     = 'dl';
			$sub_tag_name = 'dd';
		} else {
			// Default to ul / li list.
			$display_file = 'list.php';
			$list_type    = 'ul';
			$tag_name     = 'ul';
			$sub_tag_name = 'li';
		}

		$bypass_map_field_values = true;

		return pods_view( PODS_DIR . 'ui/front/display/' . $display_file, compact( array_keys( get_defined_vars() ) ), false, 'cache', true );
	}

	/**
	 * Map the matching context info value.
	 *
	 * @since 2.8.0
	 *
	 * @param string                  $field      The first field name in the path.
	 * @param string[]                $traverse   The list of fields in the path excluding the first field name.
	 * @param null|Field|Object_Field $field_data The field data or null if not a field.
	 * @param Pods|null               $obj        The Pods object or null if not set.
	 *
	 * @return null|mixed The matching context info value or null if there was no match.
	 */
	public function context_info( $field, $traverse, $field_data, $obj ) {
		// Skip if the field exists.
		if ( $field_data ) {
			return null;
		}

		// Skip if not the field we are looking for.
		if ( '_context' !== $field ) {
			return null;
		}

		// Skip if no field was set.
		if ( empty( $traverse[0] ) ) {
			return null;
		}

		$context_type = $traverse[0];

		$supported_contexts = [
			'get',
			'post',
			'request',
			'query',
			'url',
			'uri',
			'url-relative',
			'template-url',
			'stylesheet-url',
			'site-url',
			'home-url',
			'admin-url',
			'includes-url',
			'content-url',
			'plugins-url',
			'network-site-url',
			'network-home-url',
			'network-admin-url',
			'user-admin-url',
			'prefix',
			'user',
			'option',
			'site-option',
			'date',
			'pods',
			'pods_display',
			'post_id',
		];

		if ( ! in_array( $context_type, $supported_contexts, true ) ) {
			return null;
		}

		$context_var = isset( $traverse[1] ) ? $traverse[1] : null;

		$raw = isset( $traverse[2] ) && 'raw' === $traverse[2];

		if ( 'user' === $context_type && 'user_pass' === $context_var ) {
			return '';
		}

		$value = pods_v( $context_var, $context_type );

		// Maybe return the raw value.
		if ( $raw ) {
			return $value;
		}

		// Sanitize the field with some basic protections.
		return sanitize_text_field( $value );
	}

	/**
	 * Map the matching calculation value.
	 *
	 * @since 2.8.0
	 *
	 * @param string                  $field      The first field name in the path.
	 * @param string[]                $traverse   The list of fields in the path excluding the first field name.
	 * @param null|Field|Object_Field $field_data The field data or null if not a field.
	 * @param Pods|null               $obj        The Pods object or null if not set.
	 *
	 * @return null|mixed The matching calculation value or null if there was no match.
	 */
	public function calculation( $field, $traverse, $field_data, $obj ) {
		// Skip if the field exists.
		if ( $field_data ) {
			return null;
		}

		// Skip if there is no Pods object.
		if ( ! $obj ) {
			return null;
		}

		$supported_calculations = [
			'_zebra',
			'_position',
			'_total',
			'_total_found',
			'_total_all_rows',
			'_total_pages',
			'_current_page',
		];

		// Skip if not the field we are looking for.
		if ( ! in_array( $field, $supported_calculations, true ) ) {
			return null;
		}

		$value = null;

		switch ( $field ) {
			case '_zebra':
				$value = (int) $obj->zebra();

				break;
			case '_position':
				$value = $obj->position();

				break;
			case '_total':
				$value = $obj->total();

				break;
			case '_total_found':
				$value = $obj->total_found();

				break;
			case '_total_all_rows':
				$value = $obj->total_all_rows();

				break;
			case '_total_pages':
				$value = $obj->total_pages();

				break;
			case '_current_page':
				$value = (int) $obj->page;

				break;
		}

		return $value;
	}

	/**
	 * Map the matching image field value.
	 *
	 * @since 2.8.0
	 *
	 * @param string                  $field      The first field name in the path.
	 * @param string[]                $traverse   The list of fields in the path excluding the first field name.
	 * @param null|Field|Object_Field $field_data The field data or null if not a field.
	 * @param Pods|null               $obj        The Pods object or null if not set.
	 *
	 * @return null|mixed The matching image field value or null if there was no match.
	 */
	public function image_fields( $field, $traverse, $field_data, $obj ) {
		// Skip if the field exists.
		if ( $field_data ) {
			return null;
		}

		// Default image field handlers.
		$image_fields = [
			'image_attachment',
			'image_attachment_url',
			'image_attachment_src',
		];

		$object_type = $obj ? $obj->pod_data->get_type() : null;

		if ( 'post_type' === $object_type ) {
			$image_fields[] = 'post_thumbnail';
			$image_fields[] = 'post_thumbnail_url';
			$image_fields[] = 'post_thumbnail_src';
		} elseif ( 'media' === $object_type ) {
			$image_fields[] = '_img';
			$image_fields[] = '_url';
			$image_fields[] = '_src';
		}

		// Handle special field tags.
		if ( ! in_array( $field, $image_fields, true ) ) {
			return null;
		}

		$item_id = $obj ? $obj->id() : 0;

		// Handle the odd ._src and ._url variations with dot-notation too.
		if ( 'post_thumbnail' === $field ) {
			$first_traverse = ! empty( $traverse[0] ) ? $traverse[0] : null;

			if ( '_src' === $first_traverse || '_url' === $first_traverse ) {
				// Append the first traverse to the field name used.
				$field .= $first_traverse;

				// Remove the first traverse from the list.
				array_shift( $traverse );
			}
		}

		// Copy for further modification.
		$image_field     = $field;
		$traverse_params = $traverse;

		// Is it a URL request?
		if ( '_url' === $image_field || '_src' === $image_field ) {
			// This is a _url or _src field itself.
			$url = true;
		} else {
			$url = '_url' === substr( $image_field, - 4 ) || '_src' === substr( $image_field, - 4 );

			if ( $url ) {
				// This is a image_field._url or a image_field._src field.
				$image_field = substr( $image_field, 0, - 4 );
			}
		}

		$attachment_id = 0;

		switch ( $image_field ) {
			// Media pods.
			case '_img':
			case '_url':
			case '_src':
				$attachment_id = $item_id;

				break;

			// All other pods.
			case 'post_thumbnail':
				if ( $item_id ) {
					$attachment_id = get_post_thumbnail_id( $item_id );
				}

				break;
			case 'image_attachment':
				if ( isset( $traverse_params[0] ) ) {
					$attachment_id = $traverse_params[0];

					array_shift( $traverse_params );
				}
				break;
		}

		if ( ! $attachment_id ) {
			return null;
		}

		$is_image = wp_attachment_is_image( $attachment_id );

		$size = 'thumbnail';

		if ( isset( $traverse_params[0] ) ) {
			$size = $traverse_params[0];

			if ( pods_is_image_size( $size ) ) {
				// Force image request since a valid size parameter is passed.
				$is_image = true;
			} else {
				// No valid image size found.
				$size = false;
			}
		}

		if ( $url ) {
			if ( $is_image ) {
				return pods_image_url( $attachment_id, $size, 0, true );
			}

			return wp_get_attachment_url( $attachment_id );
		}

		if ( $size ) {
			// Pods will auto-get the thumbnail ID if this isn't an attachment.
			return pods_image( $attachment_id, $size, 0, null, true );
		}

		$media_field_name = implode( '.', $traverse_params );

		$shorthand = [
			'title'       => 'post_title',
			'description' => 'post_content',
			'mime_type'   => 'post_mime_type',
			'alt_text'    => '_wp_attachment_image_alt',
		];

		$metadata_shorthand = [
			'width'      => true,
			'height'     => true,
			'filesize'   => true,
		];

		$is_filename        = 'filename' === $media_field_name;
		$is_file_extension  = 'extension' === $media_field_name;
		$is_file_dimensions = 'dimensions' === $media_field_name;

		if ( 'caption' === $media_field_name ) {
			return wp_get_attachment_caption( $attachment_id );
		} elseif ( isset( $shorthand[ $media_field_name ] ) ) {
			$media_field_name = $shorthand[ $media_field_name ];
		} elseif (
			isset( $metadata_shorthand[ $media_field_name ] )
			|| 'image_meta' === $traverse_params[0]
			|| $is_filename
			|| $is_file_extension
			|| $is_file_dimensions
		) {
			$metadata = wp_get_attachment_metadata( $attachment_id );

			if ( $is_filename ) {
				if ( ! isset( $metadata['file'] ) ) {
					return '';
				}

				return basename( $metadata['file'] );
			} elseif ( $is_file_extension ) {
				return pathinfo( $metadata['file'], PATHINFO_EXTENSION );
			} elseif ( $is_file_dimensions && isset( $metadata['width'], $metadata['height'] ) ) {
				return $metadata['width'] . 'x' . $metadata['height'];
			}

			return pods_traverse( $traverse_params, $metadata );
		}

		if ( 'media' !== $object_type ) {
			// Fallback to attachment Post object to look for other image properties.
			$media = pods( 'media', $attachment_id, false );

			if ( $media && $media->valid() && $media->exists() ) {
				return $media->field( [
					'name'                    => $media_field_name,
					'bypass_map_field_values' => true,
				] );
			}

			// Fallback to default attachment object.
			$attachment = get_post( $attachment_id );
		} else {
			$attachment = $obj->row();
		}

		$value = pods_v( $media_field_name, $attachment );

		if ( null !== $value ) {
			return $value;
		}

		// Start traversal though object property or metadata.
		$name_key = array_shift( $traverse_params );
		$value    = pods_v( $name_key, $attachment );

		if ( null !== $value ) {
			return $value;
		}

		$value = get_post_meta( $attachment_id, $name_key, true );

		return pods_traverse( $traverse_params, $value );
	}

	/**
	 * Map the matching avatar field value.
	 *
	 * @since 2.8.0
	 *
	 * @param string                  $field      The first field name in the path.
	 * @param string[]                $traverse   The list of fields in the path excluding the first field name.
	 * @param null|Field|Object_Field $field_data The field data or null if not a field.
	 * @param Pods|null               $obj        The Pods object or null if not set.
	 * @param object|null             $params     The full Pods::field() parameters.
	 *
	 * @return null|mixed The matching avatar field value or null if there was no match.
	 */
	public function avatar( $field, $traverse, $field_data, $obj = null, $params = null ) {
		// Skip if not the field we are looking for.
		if ( 'avatar' !== $field ) {
			return null;
		}

		// Skip if there is no Pods object.
		if ( ! $obj ) {
			return null;
		}

		global $wpdb;

		// Skip if not on the supported pod type.
		if ( 'user' !== $obj->pod_data->get_type() && $wpdb->users !== $obj->pod_data->get_table_name() ) {
			return null;
		}

		$size    = 0;
		$item_id = $obj->id();

		// Skip if the item ID is not set.
		if ( empty( $item_id ) ) {
			return null;
		}

		// Copy for further modification.
		$image_field     = $field;
		$traverse_params = $traverse;

		$url = '_url' === substr( $image_field, - 4 ) || '_src' === substr( $image_field, - 4 );

		if ( isset( $traverse_params[0] ) ) {
			// Check if there is a numeric size, bail if not because it needs normal relationship traversal.
			if ( $field_data && ! is_numeric( $traverse_params[0] ) ) {
				return null;
			}

			$size = absint( $traverse_params[0] );
		}

		// If we have an actual field named "avatar" here, we have to assume it was meant to be the URL unless display was set.
		if (
			$field_data
			&& (
				$params
				&& empty( $params->display )
			)
		) {
			$url = true;
		}

		if ( $url ) {
			if ( 0 < $size ) {
				$avatar_url = get_avatar_url( $item_id, $size );
			} else {
				$avatar_url = get_avatar_url( $item_id );
			}

			if ( ! $avatar_url ) {
				return '';
			}
		}

		if ( 0 < $size ) {
			$avatar = get_avatar( $item_id, $size );
		} else {
			$avatar = get_avatar( $item_id );
		}

		if ( ! $avatar ) {
			return '';
		}

		return $avatar;
	}

}
