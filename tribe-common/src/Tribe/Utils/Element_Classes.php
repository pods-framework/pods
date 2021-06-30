<?php
namespace Tribe\Utils;

/**
 * Class Element_Classes to handle HTML class attribute for elements.
 *
 * @since  4.9.13
 *
 * @package Tribe\Utils
 */
class Element_Classes {
	/**
	 * Store the results of parsing the classes.
	 *
	 * @since  4.9.13
	 *
	 * @var array
	 */
	protected $results = [];

	/**
	 * Stores the arguments passed.
	 *
	 * @since  4.9.13
	 *
	 * @var array
	 */
	protected $arguments = [];

	/**
	 * Setups an instance of Element Classes.
	 *
	 * @since  4.9.13
	 *
	 * @return void
	 */
	public function __construct() {
		$this->arguments = func_get_args();
	}

	/**
	 * When invoked this class will return the full HTML class attribute.
	 *
	 * @since  4.9.13
	 *
	 * @return string In the format ` class="class1 class2" `
	 */
	public function __invoke() {
		$this->arguments = func_get_args();
		return $this->get_attribute();
	}


	/**
	 * When cast to string an instance will return the full HTML class attribute.
	 *
	 * @since  4.9.13
	 *
	 * @return string In the format ` class="class1 class2" `
	 */
	public function __toString() {
		return $this->get_attribute();
	}

	/**
	 * Gets the full HTML class attribute for this instance of Element Classes.
	 * It will contain a space on each end of the attribute.
	 *
	 * @since  4.9.13
	 *
	 * @return string In the format ` class="class1 class2" `
	 */
	public function get_attribute() {
		$classes = $this->get_classes_as_string();

		// Bail with empty string when no classes are present
		if ( ! $classes ) {
			return '';
		}

		return " class=\"{$classes}\" ";
	}

	/**
	 * Gets a space separated string of all classes to be printed.
	 *
	 * @since  4.9.13
	 *
	 * @return string
	 */
	public function get_classes_as_string() {
		return implode( ' ', $this->get_classes() );
	}

	/**
	 * Get the array of classes to be printed.
	 *
	 * @since  4.9.13
	 *
	 * @return array
	 */
	public function get_classes() {
		$this->results = [];
		$classes       = [];

		$this->parse_array( $this->arguments );

		foreach ( $this->results as $key => $val ) {
			if ( ! $val ) {
				continue;
			}

			$classes[] = $key;
		}

		$classes = array_map( 'sanitize_html_class', $classes );
		$classes = array_filter( array_unique( $classes ) );

		return $classes;
	}


	/**
	 * Get the array of the classes, using [ class_name => bool ] as the format.
	 *
	 * @since  4.9.13
	 *
	 * @return array [ class_name => bool ]
	 */
	public function get_conditions() {
		$this->results = [];
		$this->parse_array( $this->arguments );

		return $this->results;
	}

	/**
	 * Parse arguments or argument for this instance, and store values on results.
	 *
	 * @since  4.9.13
	 *
	 * @param  mixed  $arguments      Any possible set of arguments that this class supports.
	 * @param  boolean $default_value What is the default value for a given class.
	 *
	 * @return void
	 */
	protected function parse( $arguments, $default_value = true ) {
		if ( ! $arguments ) {
			return;
		}

		if ( is_numeric( $arguments ) ) { // phpcs:ignore
			// Bail on any numeric values
		} elseif ( is_string( $arguments ) ) {
			// 'foo bar'
			$this->parse_string( $arguments );
		} elseif ( $arguments instanceof \Closure || is_callable( $arguments ) ) {
			// function() {}
			$this->parse_callable( $arguments );
		} elseif ( is_array( $arguments ) ) {
			// ['foo', 'bar', ...] || ['foo' => TRUE, 'bar' => FALSE, ...]
			$this->parse_array( $arguments );
		} elseif ( is_object( $arguments ) ) {
			// stdClass
			$this->parse_object( $arguments );
		}
	}

	/**
	 * Parse a string into an array of acceptable values for the instance.
	 *
	 * @since  4.9.13
	 *
	 * @param  string  $arguments     Space separated string of classes to be parsed.
	 * @param  boolean $default_value What is the default value for a given class.
	 *
	 * @return void
	 */
	protected function parse_string( $arguments, $default_value = true ) {
		$values = preg_split( '/\s+/', $arguments, -1, PREG_SPLIT_NO_EMPTY );

		// When it doesn't match, bail early.
		if ( ! $values ) {
			return;
		}

		foreach ( $values as $class_name ) {
			$this->results[ $class_name ] = $default_value;
		}
	}

	/**
	 * Parse an array into an array of acceptable values for the instance.
	 *
	 * @since  4.9.13
	 *
	 * @param  array  $values  Array of values to be parsed.
	 *
	 * @return void
	 */
	protected function parse_array( array $values ) {
		foreach ( $values as $key => $value ) {
			if ( is_int( $key ) ) {
				if ( is_bool( $value ) ) {
					$this->parse( $key, $value );
				} else {
					$this->parse( $value );
				}
			} elseif ( is_string( $key ) ) {
				if ( ! is_bool( $value ) ) {
					throw new \UnexpectedValueException( 'Value for key ' . $key . ' must be of type boolean' );
				}

				$this->parse_string( $key, $value );
			}
		}
	}

	/**
	 * Parses an object, only if it contains __toString it will be considered.
	 *
	 * @since  4.9.13
	 *
	 * @param  mixed  $object  Object to be checked for the __toString method
	 *
	 * @return void
	 */
	protected function parse_object( $object ) {
		if ( method_exists( $object, '__toString' ) ) {
			$this->parse( (string) $object );
		}
	}

	/**
	 * Parses a callable method or function into the array of considered classes.s
	 *
	 * @since  4.9.13
	 *
	 * @param  callable  $method_or_function  Method or Function to be called.
	 *
	 * @return void
	 */
	protected function parse_callable( callable $method_or_function ) {
		$this->parse( $method_or_function( $this->results ) );
	}
}
