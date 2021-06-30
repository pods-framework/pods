<?php
namespace Tribe\Utils;

/**
 * Class Element_Attributes to handle HTML attributes for elements.
 *
 * @since  4.12.3
 *
 * @package Tribe\Utils
 */
class Element_Attributes {
	/**
	 * Store the results of parsing the attributes.
	 *
	 * @since  4.12.3
	 *
	 * @var array<string,string>
	 */
	protected $results = [];

	/**
	 * Stores the arguments passed.
	 *
	 * @since  4.12.3
	 *
	 * @var array
	 */
	protected $arguments = [];

	/**
	 * Setups an instance of Element Attributes.
	 *
	 * @since  4.12.3
	 *
	 * @return void
	 */
	public function __construct() {
		$this->arguments = func_get_args();
	}

	/**
	 * When invoked this class will return the full HTML attributes.
	 *
	 * @since  4.12.3
	 *
	 * @return string In the format ` attribute1="value1" attribute2="value2" `
	 */
	public function __invoke() {
		$this->arguments = func_get_args();
		return $this->get_attributes();
	}


	/**
	 * When cast to string an instance will return the full HTML attributes.
	 *
	 * @since  4.12.3
	 *
	 * @return string In the format ` attribute1="value1" attribute2="value2" `
	 */
	public function __toString() {
		return $this->get_attributes();
	}

	/**
	 * Gets the full HTML attributes for this instance of Element Attributes.
	 * It will contain a space on each end of the attribute.
	 *
	 * @since  4.12.3
	 *
	 * @return string In the format ` attribute1="value1" attribute2="value2" `
	 */
	public function get_attributes() {
		$attributes = $this->get_attributes_as_string();

		// Bail with empty string when no attributes are present
		if ( ! $attributes ) {
			return '';
		}

		return " {$attributes} ";
	}

	/**
	 * Gets a space separated string of all attributes to be printed.
	 *
	 * @since  4.12.3
	 *
	 * @return string
	 */
	public function get_attributes_as_string() {
		return implode( ' ', $this->get_attributes_array() );
	}

	/**
	 * Get the array of attributes to be printed.
	 *
	 * @since  4.12.3
	 *
	 * @return array
	 */
	public function get_attributes_array() {
		$this->results = [];
		$attributes    = [];

		$this->parse_array( $this->arguments );

		foreach ( $this->results as $key => $val ) {
			if ( ! $val && '0' !== $val ) {
				continue;
			}

			if ( is_bool( $val ) ) {
				$attributes[] = esc_attr( $key );
			} else {
				// Remove double quotes that might be surrounding the value.
				trim( $val, '"' );
				$attributes[] = esc_attr( $key ) . '="' . esc_attr( $val ) . '"';
			}
		}

		return $attributes;
	}

	/**
	 * Parse arguments or argument for this instance, and store values on results.
	 *
	 * @since  4.12.3
	 *
	 * @param  mixed  $arguments  Any possible set of arguments that this class supports.
	 *
	 * @return void
	 */
	protected function parse( $arguments ) {
		if ( ! $arguments ) {
			return;
		}

		if ( is_numeric( $arguments ) ) { // phpcs:ignore
			// Bail on any numeric values.
		} elseif ( is_array( $arguments ) ) {
			// ['foo', 'bar', ...] || ['foo' => TRUE, 'bar' => FALSE, 'baz' => 'foo', ...]
			$this->parse_array( $arguments );
		} elseif ( is_string( $arguments ) ) {
			// 'foo bar'
			$this->parse_string( $arguments );
		} elseif ( $arguments instanceof \Closure || is_callable( $arguments ) ) {
			// function() {}
			$this->parse_callable( $arguments );
		} elseif ( is_object( $arguments ) ) {
			// stdClass
			$this->parse_object( $arguments );
		}
	}

	/**
	 * Parse an array into an array of acceptable values for the instance.
	 *
	 * @since  4.12.3
	 *
	 * @param  array  $values  Array of values to be parsed.
	 *
	 * @return void
	 */
	protected function parse_array( array $values ) {
		foreach ( $values as $key => $value ) {
			if ( is_int( $key ) ) {
				$this->parse( $value );
			} elseif ( is_string( $key ) ) {
				if ( ! is_bool( $value ) && ! is_string( $value ) ) {
					throw new \UnexpectedValueException( 'Value for key ' . $key . ' must be of type boolean or string' );
				}

				$this->results[ $key ] = $value;
			}
		}
	}

	/**
	 * Parse a string into an array of acceptable values for the instance.
	 *
	 * @since  4.12.3
	 *
	 * @param  string  $arguments  Space separated string of attributes to be parsed.
	 *
	 * @return void
	 */
	protected function parse_string( $arguments ) {
		$values = preg_split( '/\s+/', $arguments, -1, PREG_SPLIT_NO_EMPTY );

		// When it doesn't match, bail early.
		if ( ! $values ) {
			return;
		}

		$attrs = [];

		foreach ( $values as $key => $value ) {
			if ( preg_match( '/^(?<key>[^=]+)="*(?<value>.*?)"*$/', $value, $m ) ) {
				// Something like `f="boo"` or `foo=bar`.
				$attrs[ $m['key'] ] = $m['value'];

				continue;
			}

			$attrs[ $value ] = true;
		}

		$this->parse_array( $attrs );
	}

	/**
	 * Parses an object into the array of considered attributes.
	 *
	 * @since  4.12.3
	 *
	 * @param  mixed  $object  Object to be converted into array and parsed.
	 *
	 * @return void
	 */
	protected function parse_object( $object ) {
		$this->parse_array( (array) $object );
	}

	/**
	 * Parses a callable method or function into the array of considered attributes.
	 *
	 * The result of the callable will REPLACE the current attributes, callables will work like filters.
	 *
	 * @since  4.12.3
	 *
	 * @param  callable  $method_or_function  Method or Function to be called.
	 *
	 * @return void
	 */
	protected function parse_callable( callable $method_or_function ) {
		$filtered = $method_or_function( $this->results );
		$this->results = [];
		$this->parse( $filtered );
	}
}
