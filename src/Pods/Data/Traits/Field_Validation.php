<?php

namespace Pods\Data\Traits;

// Don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * Field_Validation trait.
 *
 * @since 3.4.0
 */
trait Field_Validation {

	/**
	 * Get the minimum length validation error for a field value, if any.
	 *
	 * @since 3.4.0
	 *
	 * @param mixed       $check   The cleaned value (after pre_save()).
	 * @param string|null $name    Field name.
	 * @param array|null  $options Field options.
	 *
	 * @return string|null The error message, or null if the value passes.
	 */
	protected function get_min_length_error( $check, $name, $options ) {
		$min_length = (int) pods_v( static::$type . '_min_length', $options, 0 );

		if ( 0 >= $min_length ) {
			return null;
		}

		$len = pods_mb_strlen( (string) $check );

		if ( 0 >= $len || $len >= $min_length ) {
			return null;
		}

		$label = pods_v( 'label', $options, ucwords( str_replace( '_', ' ', $name ) ) );

		// translators: %1$s is the field label, %2$d is the minimum number of characters.
		return sprintf( __( '%1$s must be at least %2$d characters long.', 'pods' ), $label, $min_length );
	}
}
