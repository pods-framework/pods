<?php

namespace Pods\Data;

use Pods;
use Pods\Whatsit\Field;
use Pods\Whatsit\Object_Field;

/**
 * Map_Field_Values class.
 *
 * @since 2.8
 */
class Map_Field_Values {

	/**
	 * Map the matching image field value.
	 *
	 * @since 2.8
	 *
	 * @param string                  $field      The first field name in the path.
	 * @param string[]                $traverse   The list of all fields in the path.
	 * @param null|Field|Object_Field $field_data The field data or null if not a field.
	 * @param Pods                    $obj        The Pods object.
	 *
	 * @return null|mixed The matching image field value or null if there was no match.
	 */
	public function map_value( $field, $traverse, $field_data, $obj ) {
		// Remove the first field from $traverse.
		if ( $field === reset( $traverse ) ) {
			array_shift( $traverse );
		}

		// @todo Add filter.
		$value = null;

		if ( null !== $value ) {
			return $value;
		}

		$methods = [
			'custom',
			'pod_info',
			'image_fields',
			'avatar',
		];

		$method = false;

		// Go through all of the support mappings and check for a field value.
		while ( null === $value && $method = array_shift( $methods ) ) {
			$value = $this->$method( $field, $traverse, $field_data, $obj );
		}

		// If no value was found, set $method to false.
		if ( null === $value ) {
			$method = false;
		}

		/**
		 * Allow filtering the field mapping.
		 *
		 * @since 2.8
		 *
		 * @param null|mixed              $value      The matching field value or null if there was no match.
		 * @param string                  $field      The first field name in the path.
		 * @param string[]                $traverse   The list of fields in the path excluding the first field name.
		 * @param null|Field|Object_Field $field_data The field data or null if not a field.
		 * @param Pods                    $obj        The Pods object.
		 * @param string|false            $method     The matching mapping method or false if there was no match.
		 */
		return apply_filters( 'pods_data_map_field_values_map_value', $value, $field, $traverse, $field_data, $obj, $method );
	}

	/**
	 * Handle custom field mapping.
	 *
	 * @since 2.8
	 *
	 * @param string                  $field      The first field name in the path.
	 * @param string[]                $traverse   The list of fields in the path excluding the first field name.
	 * @param null|Field|Object_Field $field_data The field data or null if not a field.
	 * @param Pods                    $obj        The Pods object.
	 *
	 * @return null|mixed The matching field value or null if there was no match.
	 */
	public function custom( $field, $traverse, $field_data, $obj ) {
		/**
		 * Allow filtering for a custom field mapping.
		 *
		 * @since 2.8
		 *
		 * @param null|mixed              $value      The matching field value or null if there was no match.
		 * @param string                  $field      The first field name in the path.
		 * @param string[]                $traverse   The list of fields in the path excluding the first field name.
		 * @param null|Field|Object_Field $field_data The field data or null if not a field.
		 * @param Pods                    $obj        The Pods object.
		 */
		return apply_filters( 'pods_data_map_field_values_custom', null, $field, $traverse, $field_data, $obj );
	}

	/**
	 * Map the matching pod info value.
	 *
	 * @since 2.8
	 *
	 * @param string                  $field      The first field name in the path.
	 * @param string[]                $traverse   The list of fields in the path excluding the first field name.
	 * @param null|Field|Object_Field $field_data The field data or null if not a field.
	 * @param Pods                    $obj        The Pods object.
	 *
	 * @return null|mixed The matching pod info value or null if there was no match.
	 */
	public function pod_info( $field, $traverse, $field_data, $obj ) {
		if ( '_pod' !== $field ) {
			return null;
		}

		if ( empty( $traverse[0] ) ) {
			return null;
		}

		$pod_option = $traverse[0];

		return $obj->pod_data->get_arg( $pod_option );
	}

	/**
	 * Map the matching field info value.
	 *
	 * @since 2.8
	 *
	 * @param string                  $field      The first field name in the path.
	 * @param string[]                $traverse   The list of fields in the path excluding the first field name.
	 * @param null|Field|Object_Field $field_data The field data or null if not a field.
	 * @param Pods                    $obj        The Pods object.
	 *
	 * @return null|mixed The matching field info value or null if there was no match.
	 */
	public function field_info( $field, $traverse, $field_data, $obj ) {
		if ( '_field' !== $field ) {
			return null;
		}

		if ( empty( $traverse[0] ) ) {
			return null;
		}

		$field_match = $traverse[0];
		$field_option = ! empty( $traverse[1] ) ? $traverse[1] : 'label';

		return $obj->fields( $field_match, $field_option );
	}

	/**
	 * Map the matching image field value.
	 *
	 * @since 2.8
	 *
	 * @param string                  $field      The first field name in the path.
	 * @param string[]                $traverse   The list of fields in the path excluding the first field name.
	 * @param null|Field|Object_Field $field_data The field data or null if not a field.
	 * @param Pods                    $obj        The Pods object.
	 *
	 * @return null|mixed The matching image field value or null if there was no match.
	 */
	public function image_fields( $field, $traverse, $field_data, $obj ) {
		// Default image field handlers.
		$image_fields = [
			'image_attachment',
			'image_attachment_url',
		];

		if ( 'post_type' === $obj->pod_data->get_type() ) {
			$image_fields[] = 'post_thumbnail';
			$image_fields[] = 'post_thumbnail_url';
		}

		// Handle special field tags.
		if ( ! in_array( $field, $image_fields, true ) ) {
			return null;
		}

		$item_id = $obj->id();

		// Copy for further modification.
		$image_field     = $field;
		$traverse_params = $traverse;

		// Is it a URL request?
		$url = '_url' === substr( $image_field, - 4 );

		if ( $url ) {
			$image_field = substr( $image_field, 0, - 4 );
		}

		// Results in an empty array if no traversal params are passed.
		array_shift( $traverse_params );

		$attachment_id = 0;

		switch ( $image_field ) {
			case 'post_thumbnail':
				$attachment_id = get_post_thumbnail_id( $item_id );

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

		// Fallback to attachment Post object to look for other image properties.
		$media = pods( 'media', $attachment_id, false );

		if ( $media && $media->valid() && $media->exists() ) {
			return $media->field( implode( '.', $traverse_params ) );
		}

		// Fallback to default attachment object.
		$attachment = get_post( $attachment_id );
		$value      = pods_v( implode( '.', $traverse_params ), $attachment );

		if ( null === $value ) {
			// Start traversal though object property or metadata.
			$name_key = array_shift( $traverse_params );
			$value    = pods_v( $name_key, $attachment );

			if ( null === $value ) {
				$value = get_post_meta( $attachment_id, $name_key, true );
			}

			return pods_traverse( $traverse_params, $value );
		}
	}

	/**
	 * Map the matching avatar field value.
	 *
	 * @since 2.8
	 *
	 * @param string                  $field      The first field name in the path.
	 * @param string[]                $traverse   The list of fields in the path excluding the first field name.
	 * @param null|Field|Object_Field $field_data The field data or null if not a field.
	 * @param Pods                    $obj        The Pods object.
	 *
	 * @return null|mixed The matching avatar field value or null if there was no match.
	 */
	public function avatar( $field, $traverse, $field_data, $obj ) {
		if ( 'avatar' !== $field || 'user' !== $obj->pod_data->get_type() ) {
			return null;
		}

		$size    = 0;
		$item_id = $obj->id();

		if ( isset( $traverse_fields[0] ) ) {
			$size = (int) $traverse_fields[0];

			// Check if there is a numeric size, bail if not because it needs normal relationship traversal.
			if ( $field_data && ! is_numeric( $traverse_fields[0] ) ) {
				return null;
			}
		}

		if ( 0 < $size ) {
			return get_avatar( $item_id, $size );
		}

		return get_avatar( $item_id );
	}

}
