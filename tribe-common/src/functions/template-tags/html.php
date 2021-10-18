<?php
/**
 * HTML functions (template-tags) for use in WordPress templates.
 */
use Tribe\Utils\Element_Attributes;
use Tribe\Utils\Element_Classes;

/**
 * Parse input values into a valid array of classes to be used in the templates.
 *
 * @since  4.9.13
 *
 * @param  mixed $classes,... unlimited Any amount of params to be rendered as classes.
 *
 * @return array
 */
function tribe_get_classes() {
	$element_classes = new Element_Classes( func_get_args() );
	return $element_classes->get_classes();
}

/**
 * Parses input values into a valid class html attribute to be used in the templates.
 *
 * @since  4.9.13
 *
 * @param  mixed $classes,... unlimited Any amount of params to be rendered as classes.
 *
 * @return void
 */
function tribe_classes() {
	$element_classes = new Element_Classes( func_get_args() );
	echo $element_classes->get_attribute();
}

/**
 * Parse input values into a valid array of attributes to be used in the templates.
 *
 * @since  4.12.3
 *
 * @param  mixed $attributes,... unlimited Any amount of params to be rendered as attributes.
 *
 * @return array<string> An array of the parsed string attributes.
 */
function tribe_get_attributes() {
	$element_attributes = new Element_Attributes( func_get_args() );
	return $element_attributes->get_attributes_array();
}

/**
 * Parse input values into a valid html attributes to be used in the templates.
 *
 * @since  4.12.3
 *
 * @param  mixed $attributes,... unlimited Any amount of params to be rendered as attributes.
 *
 * @return void
 */
function tribe_attributes() {
	$element_attributes = new Element_Attributes( func_get_args() );
	echo $element_attributes->get_attributes();
}

/**
 * Get attributes for required fields.
 *
 * @deprecated 4.12.6
 *
 * @since 4.10.0
 *
 * @param boolean $required If the field is required.
 * @param boolean $echo     Whether to echo the string or return it.
 *
 * @return string|void If echo is false, returns $required_string.
 */
function tribe_required( $required, $echo = true ) {
	if ( $required ) {
		$required_string = 'required aria-required="true"';

		if ( ! $echo ) {
			return $required_string;
		} else {
			echo $required_string;
		}
	}
}

/**
 * Get string for required field labels.
 *
 * @since 4.10.0
 *
 * @param boolean $required If the field is required.
 * @param boolean $echo     Whether to echo the string or return it.
 *
 * @return string|void If echo is false, returns $required_string.
 */
function tribe_required_label( $required, $echo = true ) {
	if ( $required ) {
		$required_string = '<span class="screen-reader-text">'
			. esc_html_x( '(required)', 'The associated field is required.', 'tribe-common' )
			. '</span><span class="tribe-required" aria-hidden="true" role="presentation">*</span>';

		if ( ! $echo ) {
			return $required_string;
		} else {
			echo $required_string;
		}
	}
}

/**
 * Get attributes for disabled fields.
 *
 * @deprecated 4.12.6
 *
 * @since 4.10.0
 *
 * @param boolean $disabled If the field is disabled.
 * @param boolean $echo     Whether to echo the string or return it.
 *
 * @return string|void If echo is false, returns $disabled_string.
 */
function tribe_disabled( $disabled, $echo = true ) {
	if ( $disabled ) {
		$disabled_string = 'disabled aria-disabled="true"';

		if ( ! $echo ) {
			return $disabled_string;
		} else {
			echo $disabled_string;
		}
	}
}

/**
 * Generates a string for the tribe-dependency attributes.
 *
 * @since 4.12.14
 *
 * @param array<string,mixed> $deps       The passed array of dependencies.
 *
 * @return string             $dependency The string of dependencies attributes to add to the input.
 */
function tribe_format_field_dependency( $deps ) {
	// Sanity check.
	if ( empty( $deps ) ) {
		return '';
	}

	// Let's be case-insensitive!
	$deps = array_combine( array_map( 'strtolower', array_keys( $deps ) ), $deps );

	// No ID to hook to? Bail.
	if ( empty( $deps['id'] ) ) {
		return;
	}

	$dependency = '';

	$accepted = [
		'id',
		'parent',
		'is',
		'is-not',
		'is-empty',
		'is-not-empty',
		'is-numeric',
		'is-not-numeric',
		'is-checked',
		'is-not-checked',
	];

	$valid_deps = array_intersect_key( $deps, array_flip( $accepted ) );

	foreach ( $valid_deps as $attr => $value ) {
		// Attributes are always lower case.
		$attr = strtolower( $attr );

		// Handle the ID component.
		if ( 'id' === $attr ) {
			// Prepend a hash "#" if it's missing.
			if ( '#' !== substr( $value, 0, 1 ) ) {
				$value = '#' . $value;
			}

			$dependency .= " data-depends=\"{$value}\"";
			continue;
		}

		// Handle the dependent parent component.
		if ( 'parent' === $attr ) {
			$dependency .= " data-dependent-parent=\"{$value}\"";
			continue;
		}

		// Handle boolean values.
		if ( is_bool( $value ) ) {
			if ( $value ) {
				$dependency .= " data-condition-{$attr}";
			} else {
				if ( 0 === stripos( $attr, 'is-not-' ) ) {
					$attr = str_replace( 'is-not-', 'is-', $attr );
				} else {
					$attr = str_replace( 'is-', 'is-not-', $attr );
				}

				$dependency .= " data-{$attr}";
			}

			continue;
		}

		// Handle string and "empty" values
		if( 0 === strlen( $value ) ) {
			$dependency .= " data-condition-{$attr}";
		} else if ( 'is' === $attr ) {
			$dependency .= " data-condition=\"{$value}\"";
		} else if ( 'is-not' === $attr ) {
			$dependency .= " data-condition-not=\"{$value}\"";
		}
	}

	return $dependency;
}
