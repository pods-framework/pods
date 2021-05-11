<?php


class Tribe__Support__Obfuscator {

	/**
	 * @var array
	 */
	protected $prefixes = [];

	/**
	 * Tribe__Support__Obfuscator constructor.
	 *
	 * @param array $prefixes
	 */
	public function __construct( array $prefixes = [] ) {
		$this->prefixes = $prefixes;
	}

	/**
	 * Whether a value should be obfuscated or not.
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public function should_obfuscate( $key ) {
		foreach ( $this->prefixes as $prefix ) {
			if ( strpos( $key, $prefix ) === 0 ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Conditionally obfuscates a string value.
	 *
	 * @param string $key
	 * @param mixed $string_value
	 *
	 * @return mixed Either the obfuscated string or the original value if not a string.
	 */
	public function obfuscate( $key, $string_value ) {
		if ( ! is_string( $string_value ) ) {
			return $string_value;
		}
		if ( ! $this->should_obfuscate( $key ) ) {
			return $string_value;
		}

		$length = strlen( $string_value );
		if ( $length <= 3 ) {
			return preg_replace( "/./", "#", $string_value );
		} elseif ( $length > 3 && $length <= 5 ) {
			return preg_replace( '/^(.{1}).*$/', '$1' . str_repeat( '#', $length - 1 ) . '$2', $string_value );
		} elseif ( $length > 5 && $length <= 9 ) {
			return preg_replace( '/^(.{1}).*(.{1})$/', '$1' . str_repeat( '#', $length - 2 ) . '$2', $string_value );
		} elseif ( $length > 9 && $length <= 19 ) {
			return preg_replace( '/^(.{2}).*(.{2})$/', '$1' . str_repeat( '#', $length - 4 ) . '$2', $string_value );
		} elseif ( $length > 19 && $length <= 31 ) {
			return preg_replace( '/^(.{3}).*(.{3})$/', '$1' . str_repeat( '#', $length - 6 ) . '$2', $string_value );
		}

		return preg_replace( '/^(.{4}).*(.{4})$/', '$1' . str_repeat( '#', $length - 8 ) . '$2', $string_value );
	}
}
