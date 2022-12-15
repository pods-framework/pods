<?php

namespace Tribe\Values;

abstract class Abstract_Value implements Value_Interface {

	use Value_Calculation;
	use Value_Update;

	/**
	 * Holds the initial value passed to the constructor. This variable does not change.
	 *
	 * @since 4.14.9
	 *
	 * @var mixed
	 */
	private $initial_value;

	/**
	 * Holds the value normalized value calculated when instantiating an object or setting new values.
	 *
	 * @since 4.14.9
	 *
	 * @var float
	 */
	private $normalized_amount;

	/**
	 * The integer representation of the amount. By default, this is the float value, rounded to the object precision
	 * places and multiplied by (10^precision).
	 *
	 * @since 4.14.9
	 *
	 * @var int
	 */
	private $integer = 0;

	/**
	 * The float representation of the amount. By default, this is the same as $normalized_amount
	 *
	 * @since 4.14.9
	 *
	 * @var float
	 */
	private $float = 0.0;

	/**
	 * The decimal precision to use in calculations.
	 *
	 * @since 4.14.9
	 *
	 * @var int
	 */
	private $precision = 2;

	/**
	 * The class type representation to use when firing scoped filters
	 *
	 * @since 4.14.9
	 *
	 * @var string
	 */
	public $value_type;

	/**
	 * Initialize object
	 *
	 * @since 4.14.9
	 *
	 * @param mixed $amount the value to set initially
	 */
	public function __construct( $amount = 0 ) {
		$this->set_initial_representation( $amount );
		$this->set_normalized_amount( $amount );
		$this->update();
	}

	/**
	 * @inheritDoc
	 */
	public static function create( $value = 0 ) {
		$class = get_called_class();
		return new $class( $value );
	}

	/**
	 * @inheritDoc
	 */
	public function set_value( $amount ) {
		$this->set_normalized_amount( $amount );
		$this->update();
	}

	/**
	 * @inheritDoc
	 */
	public function set_precision( $amount ) {
		$this->precision = $amount;
	}


	/**
	 * @inheritDoc
	 */
	public function get_integer() {
		/**
		 * Filter the value returned for get_integer() when implemented in a specific class name
		 *
		 * @since 4.14.9
		 *
		 * @param int $integer the integer representation of the value
		 * @param Abstract_Value the object instance
		 *
		 * @return int
		 */
		$integer = apply_filters( "tec_common_value_{$this->get_value_type()}_get_integer", $this->integer, $this );

		/**
		 * Filter the value returned for get_integer() when implemented in any class
		 *
		 * @since 4.14.9
		 *
		 * @param int $integer the integer representation of the value
		 * @param Abstract_Value the object instance
		 *
		 * @return int
		 */
		return apply_filters( 'tec_common_value_get_integer', $integer, $this );

	}

	/**
	 * @inheritDoc
	 */
	public function get_float() {
		/**
		 * Filter the value returned for get_float() when implemented in a specific class name
		 *
		 * @since 4.14.9
		 *
		 * @param float $float the float representation of the value
		 * @param Abstract_Value the object instance
		 *
		 * @return float
		 */
		$float = apply_filters( "tec_common_value_{$this->get_value_type()}_get_float", $this->float, $this );

		/**
		 * Filter the value returned for get_float() when implemented in any class
		 *
		 * @since 4.14.9
		 *
		 * @param float $float the float representation of the value
		 * @param Abstract_Value the object instance
		 *
		 * @return float
		 */
		return apply_filters( 'tec_common_value_get_float', $float, $this );
	}

	/**
	 * @inheritDoc
	 */
	public function get_precision() {
		/**
		 * Filter the value returned for get_precision() when implemented in a specific class name
		 *
		 * @since 4.14.9
		 *
		 * @param int $precision the precision to which values will be calculated
		 * @param Abstract_Value the object instance
		 *
		 * @return int
		 */
		$precision = apply_filters( "tec_common_value_{$this->get_value_type()}_get_precision", $this->precision, $this );

		/**
		 * Filter the value returned for get_precision() when implemented in any class
		 *
		 * @since 4.14.9
		 *
		 * @param int $precision the precision to which values will be calculated
		 * @param Abstract_Value the object instance
		 *
		 * @return int
		 */
		return (int) apply_filters( 'tec_common_value_get_precision', $precision, $this );
	}

	/**
	 * @inheritDoc
	 */
	public function get_normalized_value() {
		return $this->normalized_amount;
	}

	/**
	 * @inheritDoc
	 */
	public function get_initial_representation() {
		return $this->initial_value;
	}

	/**
	 * @inheritDoc
	 */
	public function get_value_type() {
		return $this->value_type;
	}

	/**
	 * @inheritDoc
	 */
	public function normalize( $value ) {

		if ( is_numeric( $value ) ) {
			return (float) $value;
		}

		if ( $this->is_character_block( $value ) ) {
			return (float) 0;
		}

		$value = $this->remove_character_blocks( $value );
		$value = $this->remove_html( $value );

		// Get all non-digits from the amount
		preg_match_all( '/[^\d]/', $value, $non_digits );

		// if the string is all digits, it is numeric
		if ( empty( $non_digits[0] ) ) {
			return (float) $value;
		}

		$pieces = $this->remove_non_digits( $value, $non_digits );

		return (float) $this->assemble_normalized_value( $pieces );
	}

	/**
	 * Removes any blocks composed of all non-digit characters from the numeric string. These will usually represent
	 * the currency code and any other pieces of text that may have been sent with the value.
	 *
	 * This is specially important in case the currency unit contains the same characters as the decimal/thousands
	 * separators such as in Moroccan Dirham (1,234.56 .د.م.) or Danish Krone (kr. 1.234,56)
	 *
	 * @since 4.14.9
	 *
	 * @param string $value the numeric string being normalized
	 *
	 * @return string
	 */
	private function remove_character_blocks( $value ) {
		foreach ( explode( ' ', $value ) as $block ) {
			if ( ! $this->is_character_block( $block ) ) {
				continue;
			}

			$value = str_replace( $block, '', $value );
		}

		return $value;
	}

	/**
	 * Removes all html tags and html entities from the value string
	 *
	 * @since 4.14.9
	 *
	 * @param string $value the value being normalized
	 *
	 * @return string
	 */
	private function remove_html( $value ) {
		return wp_strip_all_tags( preg_replace( '/&[^;]+;/', '', trim( $value ) ) );
	}

	/**
	 * Takes the value string and a list of non-digit characters and removes any of those characters. If the character
	 * is found to be a decimal separator, normalize it to a dot, so the number translates to a float.
	 *
	 * @since 4.14.9
	 *
	 * @param string   $value      the value being normalized
	 * @param string[] $non_digits a list of non-digit characters present in $value
	 * @param string   $separator  a default separator to use when splitting the string
	 *
	 * @return string[]
	 */
	private function remove_non_digits( $value, $non_digits, $separator = '>>>' ) {

		$tokens = array_unique( $non_digits[0] );

		foreach ( $tokens as $token ) {
			if ( $this->is_decimal_separator( $token, $value ) ) {
				$separator = $token;
				continue;
			}

			$value = str_replace( $token, '', $value );
		}

		return explode( $separator, $value );
	}

	/**
	 * Re-assemble the normalized value to store.
	 *
	 * @since 4.14.9
	 *
	 * @param int[] $pieces the normalized value split in an array.
	 *
	 * @return float
	 */
	private function assemble_normalized_value( $pieces ) {

		// If the initial amount did not have decimals specified, $pieces will be an array of a single
		// numeric value, so we just return it as a float.
		if ( 1 === count( $pieces ) && is_numeric( reset( $pieces ) ) ) {
			return (float) reset( $pieces );
		}

		$decimal = array_pop( $pieces );

		return (float) implode( '', array_merge( $pieces, [ '.', $decimal ] ) );
	}

	/**
	 * Private setter for the initial value the object was created with. This value cannot be changed during the object
	 * lifecycle.
	 *
	 * @since 4.14.9
	 *
	 * To set a new value discard the original object and create a new one.
	 */
	private function set_initial_representation( $amount ) {
		if ( empty( $this->initial_value ) ) {
			$this->initial_value = $amount;
		}
	}

	/**
	 * Private setter for the normalized amount extracted from the initial value.
	 *
	 * @since 4.14.9
	 *
	 * To set a new value use the public setter `$obj->set_value( $amount )`
	 */
	private function set_normalized_amount( $amount ) {

		$normalized_value = $this->normalize( $amount );

		/**
		 * Filter the value to be set as $normalized_amount for a specific implementation.
		 *
		 * @since 4.14.9
		 *
		 * @param float $normalized_value the normalized value
		 * @param Abstract_Value the object instance
		 *
		 * @return float
		 */
		$normalized_value = (float) apply_filters( "tec_common_{$this->get_value_type()}_value_normalized", $normalized_value, $this );

		/**
		 * Filter the value to be set as $normalized_amount for all implementations.
		 *
		 * @since 4.14.9
		 *
		 * @param float $normalized_value the normalized value
		 * @param Abstract_Value the object instance
		 *
		 * @return float
		 */
		$normalized_value = (float) apply_filters( "tec_common_value_normalized", $normalized_value, $this );

		/**
		 * Fire action right before setting the normalized value
		 *
		 * @since 4.14.9
		 *
		 * @param float $normalized_value the normalized value
		 * @param Abstract_Value the object instance
		 */
		do_action( 'tec_common_value_normalized', $normalized_value, $this );

		$this->normalized_amount = $normalized_value;
	}

	/**
	 * Private setter for the integer representation of the object amount.
	 *
	 * @since 4.14.9
	 *
	 * To set a new value use the public setter `$obj->set_value( $amount )`
	 */
	protected function set_integer_value() {
		$this->integer = $this->to_integer( $this->normalized_amount );
	}

	/**
	 * Private setter for the floating point representation of the object amount.
	 *
	 * @since 4.14.9
	 *
	 * To set a new value use the public setter `$obj->set_value( $amount )`
	 */
	protected function set_float_value() {
		$this->float = $this->normalized_amount;
	}

	/**
	 * Tries to determine if a token is serving as a decimal separator or something else
	 * in a string;
	 *
	 * The rule to determine a decimal is straightforward. It needs to exist only once
	 * in the string and the piece of the string after the separator cannot be longer
	 * than 2 digits. Anything else is serving another purpose.
	 *
	 * @since 4.14.9
	 *
	 * @param $separator string a separator token, like . or ,
	 * @param $value     string a number formatted as a string
	 *
	 * @return bool
	 */
	private function is_decimal_separator( $separator, $value ) {
		$pieces = array_filter( explode( $separator, $value ) );

		foreach ( $pieces as $i => $block ) {
			if ( $this->is_character_block( $block ) ) {
				unset( $pieces[ $i ] );
			}
		}

		if ( 2 === count( $pieces ) ) {
			return strlen( array_pop( $pieces ) ) < 3;
		}

		return false;
	}

	/**
	 * Tests if a string is composed entirely of non-digit characters
	 *
	 * @since 4.14.9
	 *
	 * @param string $block the string to check
	 *
	 * @return bool
	 */
	private function is_character_block( $block ) {
		return empty( preg_replace( '/\D/', '', $block ) );
	}
}