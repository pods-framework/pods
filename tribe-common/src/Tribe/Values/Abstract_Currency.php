<?php

namespace Tribe\Values;

abstract class Abstract_Currency extends Abstract_Value implements Currency_Interface {

	use Value_Formatting;
	use Value_Update;

	/**
	 * The currency formatter representation, including the currency symbol.
	 *
	 * @since 4.14.9
	 *
	 * @var string
	 */
	protected $currency;

	/**
	 * The currency decimal representation, rounded to the precision.
	 *
	 * @since 4.14.9
	 *
	 * @var float
	 */
	protected $decimal;

	/**
	 * The currency formatted string representation, without the currency symbol.
	 *
	 * @since 4.14.9
	 *
	 * @var string
	 */
	protected $string;

	/**
	 * The default currency code
	 *
	 * @since 4.14.9
	 *
	 * @var string
	 */
	protected $currency_code = 'USD';

	/**
	 * The default currency decimal separator
	 *
	 * @since 4.14.9
	 *
	 * @var string
	 */
	protected $currency_separator_decimal = '.';

	/**
	 * The default currency thousands separator
	 *
	 * @since 4.14.9
	 *
	 * @var string
	 */
	protected $currency_separator_thousands = ',';

	/**
	 * The default complete currency symbol, such as $, € or R$
	 *
	 * @since 4.14.9
	 *
	 * @var string
	 */
	protected $currency_symbol = '$';

	/**
	 * The default currency symbol position: prefix if $1 or postfix for 1$
	 *
	 * @since 4.14.9
	 *
	 * @var string
	 */
	protected $currency_symbol_position = 'prefix';

	/**
	 * Initialize object
	 *
	 * @since 4.14.9
	 *
	 * @param mixed $amount the value to set initially
	 */
	public function __construct( $amount = 0 ) {
		$this->set_up_currency_details();

		parent::__construct( $amount );
	}

	/**
	 * @inheritDoc
	 */
	public function get_currency() {

		/**
		 * Filter the value returned for get_currency() when implemented in a specific class type
		 *
		 * @since 4.14.9
		 *
		 * @param string $currency the string representation of the value
		 * @param Abstract_Currency the object instance
		 *
		 * @return string
		 */
		$currency = apply_filters( "tec_common_value_{$this->get_value_type()}_get_currency", $this->currency, $this );

		/**
		 * Filter the value returned for get_currency() when implemented in any class
		 *
		 * @since 4.14.9
		 *
		 * @param string $currency the string representation of the value
		 * @param Abstract_Currency the object instance
		 *
		 * @return string
		 */
		return apply_filters( 'tec_common_value_get_currency', $currency, $this );
	}

	/**
	 * @inheritDoc
	 */
	public function get_decimal() {

		/**
		 * Filter the value returned for get_decimal() when implemented in a specific class type
		 *
		 * @since 4.14.9
		 *
		 * @param float $decimal the float representation of the value, rounded to precision
		 * @param Abstract_Currency the object instance
		 *
		 * @return float
		 */
		$decimal = apply_filters( "tec_common_value_{$this->get_value_type()}_get_decimal", $this->decimal, $this );

		/**
		 * Filter the value returned for get_decimal() when implemented in any class
		 *
		 * @since 4.14.9
		 *
		 * @param float $decimal the string representation of the value
		 * @param Abstract_Currency the object instance
		 *
		 * @return float
		 */
		return apply_filters( 'tec_common_value_get_decimal', $decimal, $this );
	}

	/**
	 * @inheritDoc
	 */
	public function get_string() {

		/**
		 * Filter the value returned for get_string() when implemented in a specific class type
		 *
		 * @since 4.14.9
		 *
		 * @param string $string the string representation of the value
		 * @param Abstract_Currency the object instance
		 *
		 * @return string
		 */
		$string = apply_filters( "tec_common_value_{$this->get_value_type()}_get_string", $this->string, $this );

		/**
		 * Filter the value returned for get_string() when implemented in any class
		 *
		 * @since 4.14.9
		 *
		 * @param string $string the string representation of the value
		 * @param Abstract_Currency the object instance
		 *
		 * @return string
		 */
		return apply_filters( 'tec_common_value_get_string', $string, $this );
	}

	/**
	 * @inheritDoc
	 */
	public function get_currency_code() {

		/**
		 * Filter the value returned for get_currency_code() when implemented in a specific class type
		 *
		 * @since 4.14.9
		 *
		 * @param string $currency_code the string representation of the value
		 * @param Abstract_Currency the object instance
		 *
		 * @return string
		 */
		$currency_code = apply_filters( "tec_common_value_{$this->get_value_type()}_get_currency_code", $this->currency_code, $this );

		/**
		 * Filter the value returned for get_currency_code() when implemented in any class
		 *
		 * @since 4.14.9
		 *
		 * @param string $currency_code the string representation of the value
		 * @param Abstract_Currency the object instance
		 *
		 * @return string
		 */
		return apply_filters( 'tec_common_value_get_currency_code', $currency_code, $this );
	}

	/**
	 * @inheritDoc
	 */
	public function get_currency_symbol() {

		/**
		 * Filter the value returned for get_currency_symbol() when implemented in a specific class type
		 *
		 * @since 4.14.9
		 *
		 * @param string $currency_symbol the string representation of the value
		 * @param Abstract_Currency the object instance
		 *
		 * @return string
		 */
		$currency_symbol = apply_filters( "tec_common_value_{$this->get_value_type()}_get_currency_symbol", $this->currency_symbol, $this );

		/**
		 * Filter the value returned for get_currency_symbol() when implemented in any class
		 *
		 * @since 4.14.9
		 *
		 * @param string $currency_symbol the string representation of the value
		 * @param Abstract_Currency the object instance
		 *
		 * @return string
		 */
		return apply_filters( 'tec_common_value_get_currency_symbol', $currency_symbol, $this );
	}

	/**
	 * @inheritDoc
	 */
	public function get_currency_symbol_position() {

		/**
		 * Filter the value returned for get_currency_symbol_position() when implemented in a specific class type
		 *
		 * @since 4.14.9
		 *
		 * @param string $currency_symbol_position the string representation of the value
		 * @param Abstract_Currency the object instance
		 *
		 * @return string
		 */
		$currency_symbol_position = apply_filters( "tec_common_value_{$this->get_value_type()}_get_currency_symbol_position", $this->currency_symbol_position, $this );

		/**
		 * Filter the value returned for get_currency_symbol_position() when implemented in any class
		 *
		 * @since 4.14.9
		 *
		 * @param string $currency_symbol_position the string representation of the value
		 * @param Abstract_Currency the object instance
		 *
		 * @return string
		 */
		return apply_filters( 'tec_common_value_get_currency_symbol_position', $currency_symbol_position, $this );
	}

	/**
	 * @inheritDoc
	 */
	public function get_currency_separator_decimal() {

		/**
		 * Filter the value returned for get_currency_separator_decimal() when implemented in a specific class type
		 *
		 * @since 4.14.9
		 *
		 * @param string $currency_separator_decimal the string representation of the value
		 * @param Abstract_Currency the object instance
		 *
		 * @return string
		 */
		$currency_separator_decimal = apply_filters( "tec_common_value_{$this->get_value_type()}_get_currency_separator_decimal", $this->currency_separator_decimal, $this );

		/**
		 * Filter the value returned for get_currency_separator_decimal() when implemented in any class
		 *
		 * @since 4.14.9
		 *
		 * @param string $currency_separator_decimal the string representation of the value
		 * @param Abstract_Currency the object instance
		 *
		 * @return string
		 */
		return apply_filters( 'tec_common_value_get_currency_separator_decimal', $currency_separator_decimal, $this );
	}

	/**
	 * @inheritDoc
	 */
	public function get_currency_separator_thousands() {

		/**
		 * Filter the value returned for get_currency_separator_thousands() when implemented in a specific class type
		 *
		 * @since 4.14.9
		 *
		 * @param string $currency_separator_thousands the string representation of the value
		 * @param Abstract_Currency the object instance
		 *
		 * @return string
		 */
		$currency_separator_thousands = apply_filters( "tec_common_value_{$this->get_value_type()}_get_currency_separator_thousands", $this->currency_separator_thousands, $this );

		/**
		 * Filter the value returned for get_currency_separator_thousands() when implemented in any class
		 *
		 * @since 4.14.9
		 *
		 * @param string $currency_separator_thousands the string representation of the value
		 * @param Abstract_Currency the object instance
		 *
		 * @return string
		 */
		return apply_filters( 'tec_common_value_get_currency_separator_thousands', $currency_separator_thousands, $this );
	}

	/**
	 * Protected setter for the string representation of the object amount. This is a formatted string, including the
	 * currency symbol.
	 *
	 * @since 4.14.9
	 *
	 * To set a new value use the public setter `$obj->set_value( $amount )`
	 */
	protected function set_currency_value() {
		$this->currency = $this->to_currency( $this->get_normalized_value() );
	}

	/**
	 * Protected setter for the decimal representation of the object amount. This is a float, rounded to the precision.
	 *
	 * @since 4.14.9
	 *
	 * To set a new value use the public setter `$obj->set_value( $amount )`
	 */
	protected function set_decimal_value() {
		$this->decimal = $this->to_decimal( $this->get_normalized_value() );
	}

	/**
	 * Protected setter for the string representation of the object amount. This is a formatted string, without the
	 * currency symbol.
	 *
	 * @since 4.14.9
	 *
	 * To set a new value use the public setter `$obj->set_value( $amount )`
	 */
	protected function set_string_value() {
		$this->string = $this->to_string( $this->get_normalized_value() );
	}
}