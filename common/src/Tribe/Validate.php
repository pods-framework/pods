<?php

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( ! class_exists( 'Tribe__Validate' ) ) {
	/**
	 * helper class that validates fields for use in Settings, MetaBoxes, Users, anywhere.
	 * Instantiate whenever you want to validate a field
	 *
	 */
	class Tribe__Validate {

		/**
		 * the field object to validate
		 * @var array
		 */
		public $field;

		/**
		 * the field's value
		 * @var mixed
		 */
		public $value;

		/**
		 * additional arguments for validation
		 * used by some methods only
		 * @var array
		 */
		public $additional_args;

		/**
		 * the field's label, used in error messages
		 * @var string
		 */
		public $label;

		/**
		 * the type of validation to perform
		 * @var string
		 */
		public $type;

		/**
		 * the result object of the validation
		 * @var stdClass
		 */
		public $result;

		/**
		 * Class constructor
		 *
		 * @param string $field_id The field ID to validate
		 * @param array  $field    The field object to validate
		 * @param mixed  $value    The value to validate
		 */
		public function __construct( $field_id, $field, $value, $additional_args = array() ) {

			// prepare object properties
			$this->result          = new stdClass;
			$this->field           = $field;
			$this->field['id']     = $field_id;
			$this->value           = $value;
			$this->additional_args = $additional_args;

			// if the field is invalid or incomplete, fail validation
			if ( ! is_array( $this->field ) || ! ( isset( $this->field['validation_type'] ) || isset( $this->field['validation_callback'] ) ) ) {
				$this->result->valid = false;
				$this->result->error = esc_html__( 'Invalid or incomplete field passed', 'tribe-common' );
				$this->result->error .= ( isset( $this->field['id'] ) ) ? ' (' . esc_html__( 'Field ID:', 'tribe-common' ) . ' ' . $this->field['id'] . ' )' : '';
			}

			// call validation callback if a validation callback function is set
			if ( isset( $this->field['validation_callback'] ) ) {
				if ( is_callable( $this->field['validation_callback'] ) || function_exists( $this->field['validation_callback'] ) ) {
					if ( ( ! isset( $_POST[ $field_id ] ) || ! $_POST[ $field_id ] || $_POST[ $field_id ] == '' ) && isset( $this->field['can_be_empty'] ) && $this->field['can_be_empty'] ) {
						$this->result->valid = true;
					} else {
						$this->result->valid = call_user_func( $this->field['validation_callback'], $value );
						if ( ! $this->result->valid ) {
							$this->result->error = esc_html__( 'Invalid or incomplete field passed', 'tribe-common' );
							$this->result->error .= ( isset( $this->field['id'] ) ) ? ' (' . esc_html__( 'Field ID:', 'tribe-common' ) . ' ' . $this->field['id'] . ' )' : '';
						}
					}
				}
			}

			if ( isset( $this->field['validation_type'] ) ) {
				if ( method_exists( $this, $this->field['validation_type'] ) ) {
					// make sure there's a field validation type set for this validation and that such method exists
					$this->type  = $this->field['validation_type'];
					$this->label = isset( $this->field['label'] ) ? $this->field['label'] : $this->field['id'];
					if ( ( ! isset( $_POST[ $field_id ] ) || ! $_POST[ $field_id ] || $_POST[ $field_id ] == '' ) && isset( $this->field['can_be_empty'] ) && $this->field['can_be_empty'] ) {
						$this->result->valid = true;
					} else {
						call_user_func( array( $this, $this->type ) ); // run the validation
					}
				} else {
					// invalid validation type set, validation fails
					$this->result->valid = false;
					$this->result->error = esc_html__( 'Non-existant field validation function passed', 'tribe-common' );
					$this->result->error .= ( isset( $this->field['id'] ) ) ? ' (' . esc_html__( 'Field ID:', 'tribe-common' ) . ' ' . $this->field['id'] . ' ' . _x( 'with function name:', 'non-existant function name passed for field validation', 'tribe-common' ) . ' ' . $this->field['validation_type'] . ' )' : '';
				}
			}
		}

		/**
		 * validates a field as a string containing only letters and numbers
		 */
		public function alpha_numeric() {
			if ( preg_match( '/^[a-zA-Z0-9]+$/', $this->value ) ) {
				$this->result->valid = true;
			} else {
				$this->result->valid = false;
				$this->result->error = sprintf( esc_html__( '%s must contain numbers and letters only', 'tribe-common' ), $this->label );
			}
		}

		/**
		 * validates a field as a string containing only letters,
		 * numbers and carriage returns
		 */
		public function alpha_numeric_multi_line() {
			if ( preg_match( '/^[a-zA-Z0-9\s]+$/', $this->value ) ) {
				$this->result->valid = true;
				$this->value         = tribe_multi_line_remove_empty_lines( $this->value );
			} else {
				$this->result->valid = false;
				$this->result->error = sprintf( esc_html__( '%s must contain numbers and letters only', 'tribe-common' ), $this->label );
			}
		}

		/**
		 * Validates a field as a string containing only letters,
		 * numbers, dots and carriage returns
		 */
		public function alpha_numeric_multi_line_with_dots_and_dashes() {
			if ( preg_match( '/^[a-zA-Z0-9\s.-]+$/', $this->value ) ) {
				$this->result->valid = true;
				$this->value         = tribe_multi_line_remove_empty_lines( $this->value );
			} else {
				$this->result->valid = false;
				$this->result->error = sprintf( esc_html__( '%s must contain numbers, letters and dots only', 'tribe-common' ), $this->label );
			}
		}

		/**
		 * Validates a field as a string containing only letters,
		 * numbers, dashes and underscores
		 */
		public function alpha_numeric_with_dashes_and_underscores() {
			$this->value = trim( $this->value );
			if ( preg_match( '/^[a-zA-Z0-9_-]+$/', $this->value ) ) {
				$this->result->valid = true;
			} else {
				$this->result->valid = false;
				$this->result->error = sprintf( esc_html__( '%s must contain numbers, letters, dashes and undescores only', 'tribe-common' ), $this->label );
			}
		}

		/**
		 * Validates a field as just "not empty".
		 *
		 * @since 4.7.6
		 */
		public function not_empty() {
			$this->value = trim( $this->value );

			if ( empty( $this->value ) ) {
				$this->result->valid = false;
				$this->result->error = sprintf( esc_html__( '%s must not be empty', 'tribe-common' ), $this->label );
			} else {
				$this->result->valid = true;
			}
		}

		/**
		 * validates a field as being positive decimal
		 */
		public function positive_decimal() {
			if ( preg_match( '/^[0-9]+(\.[0-9]+)?$/', $this->value ) && $this->value > 0 ) {
				$this->result->valid = true;
			} else {
				$this->result->valid = false;
				$this->result->error = sprintf( esc_html__( '%s must be a positive number.', 'tribe-common' ), $this->label );
			}
		}

		/**
		 * validates a field as being positive decimal or percent
		 */
		public function positive_decimal_or_percent() {
			if ( preg_match( '/^[0-9]+(\.[0-9]+)?%?$/', $this->value ) && $this->value > 0 ) {
				$this->result->valid = true;
			} else {
				$this->result->valid = false;
				$this->result->error = sprintf( esc_html__( '%s must be a positive number or percent.', 'tribe-common' ), $this->label );
			}
		}

		/**
		 * validates a field as being positive integers
		 */
		public function positive_int() {
			if ( preg_match( '/^[0-9]+$/', $this->value ) && $this->value > 0 ) {
				$this->result->valid = true;
			} else {
				$this->result->valid = false;
				$this->result->error = sprintf( esc_html__( '%s must be a positive number.', 'tribe-common' ), $this->label );
			}
		}

		/**
		 * validates a field as being an integer
		 *
		 * The expected value is a whole number (positive or negative). This method is named "int" to
		 * match the mathematical definition of the word AND to closely match the pre-exiting method
		 * with a similar name: positive_int(). This method WILL validate whole numbers that go beyond
		 * values that PHP's int type supports, however, if someone enters something like that, that's
		 * on them. Smart people do smart things.
		 */
		public function int() {
			if ( preg_match( '/^-?[0-9]+$/', $this->value ) ) {
				$this->result->valid = true;
			} else {
				$this->result->valid = false;
				$this->result->error = sprintf( esc_html__( '%s must be a whole number.', 'tribe-common' ), $this->label );
			}
		}

		/**
		 * validates & sanitizes fields as URL slugs
		 */
		public function slug() {
			$maybe_valid_value = esc_url_raw( $this->value );

			// esc_url_raw does the work of validating chars, but returns the checked string with a
			// prepended URL protocol; so let's use strpos to match the values.
			if ( false !== strpos( $maybe_valid_value, $this->value ) ) {
				$this->result->valid = true;
				$this->value         = sanitize_title( $this->value );
			} else {
				$this->result->valid = false;
				$this->result->error = sprintf( esc_html__( '%s must be a valid slug (numbers, letters, dashes, and underscores).', 'tribe-common' ), $this->label );
			}
		}

		/**
		 * validates & sanitizes fields as URLs
		 */
		public function url() {

			if ( esc_url_raw( $this->value ) == $this->value ) {
				$this->result->valid = true;
			} else {
				$this->result->valid = false;
				$this->result->error = sprintf( esc_html__( '%s must be a valid URL.', 'tribe-common' ), $this->label );
			}
		}

		/**
		 * validates fields that have options (radios, dropdowns, etc.)
		 * by making sure the value is part of the options array
		 */
		public function options() {
			if ( array_key_exists( $this->value, $this->field['options'] ) ) {
				$this->value         = ( $this->value === 0 ) ? false : $this->value;
				$this->result->valid = true;
			} else {
				$this->result->valid = false;
				$this->result->error = sprintf( esc_html__( "%s must have a value that's part of its options.", 'tribe-common' ), $this->label );
			}
		}

		/**
		 * Validates fields that have multiple options (checkbox list, etc.)
		 * by making sure the value is part of the options array.
		 */
		public function options_multi() {
			// if we are here it cannot be empty
			if ( empty( $this->value ) ) {
				$this->result->valid = false;
				$this->result->error = sprintf( esc_html__( "%s must have a value that's part of its options.", 'tribe-common' ), $this->label );

				return;
			}

			$this->value = is_array( $this->value ) ? $this->value : array( $this->value );

			foreach ( $this->value as $val ) {
				if ( array_key_exists( $val, $this->field['options'] ) ) {
					$this->value         = ( $this->value === 0 ) ? false : $this->value;
					$this->result->valid = true;
				} else {
					$this->result->valid = false;
					$this->result->error = sprintf( esc_html__( "%s must have a value that's part of its options.", 'tribe-common' ), $this->label );
				}
			}
		}

		/**
		 * validates fields that have options (radios, dropdowns, etc.)
		 * by making sure the value is part of the options array
		 * then combines the value into an array containing the value
		 * and name from the option
		 */
		public function options_with_label() {
			if ( array_key_exists( $this->value, $this->field['options'] ) ) {
				$this->value         = ( $this->value === 0 ) ? false : array(
					$this->value,
					$this->field['options'][ $this->value ],
				);
				$this->result->valid = true;
			} else {
				$this->result->valid = false;
				$this->result->error = sprintf( esc_html__( "%s must have a value that's part of its options.", 'tribe-common' ), $this->label );
			}
		}

		/**
		 * validates a field as not being able to be the same
		 * as the specified value as specified in
		 * $this->additional_args['compare_name']
		 */
		public function cannot_be_the_same_as() {
			if ( ! isset( $this->additional_args['compare'] ) ) {
				$this->result->valid = false;
				$this->result->error = sprintf( esc_html__( 'Comparison validation failed because no comparison value was provided, for field %s', 'tribe-common' ), $this->field['id'] );
			} else {
				if ( $this->value != $this->additional_args['compare'] ) {
					$this->result = true;
				} else {
					$this->result->valid = false;
					if ( isset( $this->additional_args['compare_name'] ) ) {
						$this->result->error = sprintf( esc_html__( '%s cannot be the same as %s.', 'tribe-common' ), $this->label, $this->additional_args['compare_name'] );
					} else {
						$this->result->error = sprintf( esc_html__( '%s cannot be a duplicate', 'tribe-common' ), $this->label );
					}
				}
			}
		}

		/**
		 * validates a field as being a number or a percentage
		 */
		public function number_or_percent() {
			if ( preg_match( '/^[0-9]+%{0,1}$/', $this->value ) ) {
				$this->result->valid = true;
			} else {
				$this->result->valid = false;
				$this->result->error = sprintf( esc_html__( '%s must be a number or percentage.', 'tribe-common' ), $this->label );
			}
		}

		/**
		 * sanitizes an html field
		 */
		public function html() {
			$this->value         = balanceTags( $this->value );
			$this->result->valid = true;
		}

		/**
		 * sanitizes a license key
		 */
		public function license_key() {
			$this->value         = trim( $this->value );
			$this->result->valid = true;
		}

		/**
		 * sanitizes a textarea field
		 */
		public function textarea() {
			$this->value         = wp_kses( $this->value, [] );
			$this->result->valid = true;
		}

		/**
		 * sanitizes a field as being a boolean
		 */
		public function boolean() {
			$this->value         = (bool) $this->value;
			$this->result->valid = true;
		}

		/**
		 * validates a Google Maps Zoom field
		 */
		public function google_maps_zoom() {
			if ( preg_match( '/^([0-9]|[0-1][0-9]|2[0-1])$/', $this->value ) ) {
				$this->result->valid = true;
			} else {
				$this->result->valid = false;
				$this->result->error = sprintf( esc_html__( '%s must be a number between 0 and 21.', 'tribe-common' ), $this->label );
			}
		}

		/**
		 * validates a field as being part of an address
		 * allows for letters, numbers, dashes and spaces only
		 */
		public function address() {
			$this->value = stripslashes( $this->value );
			if ( preg_match( "/^[0-9\S '-]+$/", $this->value ) ) {
				$this->result->valid = true;
			} else {
				$this->result->valid = false;
				$this->result->error = sprintf( esc_html__( '%s must consist of letters, numbers, dashes, apostrophes, and spaces only.', 'tribe-common' ), $this->label );
			}
		}

		/**
		 * validates a field as being a city or province
		 * allows for letters, dashes and spaces only
		 */
		public function city_or_province() {
			$this->value = stripslashes( $this->value );
			if ( preg_match( "/^[\D '\-]+$/", $this->value ) ) {
				$this->result->valid = true;
			} else {
				$this->result->valid = false;
				$this->result->error = sprintf( esc_html__( '%s must consist of letters, spaces, apostrophes, and dashes.', 'tribe-common' ), $this->label );
			}
		}

		/**
		 * validates a field as being a zip code
		 */
		public function zip() {
			if ( preg_match( '/^[0-9]{5}$/', $this->value ) ) {
				$this->result->valid = true;
			} else {
				$this->result->valid = false;
				$this->result->error = sprintf( esc_html__( '%s must consist of 5 numbers.', 'tribe-common' ), $this->label );
			}
		}

		/**
		 * validates a field as being a phone number
		 */
		public function phone() {
			if ( preg_match( '/^[0-9\(\)\+ -]+$/', $this->value ) ) {
				$this->result->valid = true;
			} else {
				$this->result->valid = false;
				$this->result->error = sprintf( esc_html__( '%s must be a phone number.', 'tribe-common' ), $this->label );
			}
		}

		/**
		 * validates & sanitizes a field as being a country list
		 */
		public function country_list() {
			$country_rows = explode( "\n", $this->value );
			if ( is_array( $country_rows ) ) {
				foreach ( $country_rows as $crow ) {
					$country = explode( ',', $crow );
					if ( ! isset( $country[0] ) || ! isset( $country[1] ) ) {
						$this->result->valid = false;
						$this->result->error = sprintf( esc_html__( 'Country List must be formatted as one country per line in the following format: <br>US, United States <br> UK, United Kingdom.', 'tribe-common' ), $this->label );
						$this->value         = wp_kses( $this->value, array() );

						return;
					}
				}
			}
			$this->result->valid = true;
		}

		/**
		 * automatically validate a field regardless of the value
		 * Don't use this unless you know what you are doing
		 */
		public function none() {
			$this->result->valid = true;
		}

		/**
		 * Validates and sanitizes an email address.
		 *
		 * @since 4.7.4
		 */
		public function email(  ) {
			$candidate = trim( $this->value );

			$this->result->valid = filter_var( $candidate, FILTER_VALIDATE_EMAIL );

			if ( ! $this->result->valid ) {
				$this->result->error = sprintf( esc_html__( '%s must be an email address.', 'tribe-common' ), $this->label );
			} else {
				$this->value = filter_var( trim( $candidate ), FILTER_SANITIZE_EMAIL );
			}
		}

	} // end class
} // endif class_exists
