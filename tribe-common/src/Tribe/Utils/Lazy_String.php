<?php
/**
 * A string lazily built, suited to any string value that might be costly to be built.
 *
 * @since   4.9.16
 *
 * @package Tribe\Utils
 */


namespace Tribe\Utils;


class Lazy_String implements \Serializable, \JsonSerializable {
	use Lazy_Events;

	/**
	 * The string value produced by the callback, cached.
	 *
	 * @since 4.9.16
	 *
	 * @var string
	 */
	protected $string;

	/**
	 * The callback that will be used to set the string value when called the first time.
	 *
	 * @since 4.9.16
	 *
	 * @var callable
	 */
	protected $value_callback;

	/**
	 * The callback that will be used to escape the string in the `escaped()` method..
	 *
	 * @since 4.9.16
	 *
	 * @var callable
	 */
	protected $escape_callback;

	/**
	 * The escaped string value.
	 *
	 * @since 4.9.16
	 *
	 * @var string
	 */
	protected $escaped;

	/**
	 * Lazy_String constructor.
	 *
	 * @param callable     $callback        The callback that will be used to populate the string on the first fetch.
	 * @param string|false $escape_callback The callback that will be used to escape the string in the `escaped`
	 *                                      method.
	 */
	public function __construct( callable $callback, $escape_callback = 'esc_html' ) {
		$this->value_callback  = $callback;
		$this->escape_callback = $escape_callback;
	}

	/**
	 * Inits, and returns, the string value of the string.
	 *
	 * @since 4.9.16
	 *
	 * @return string The unescaped string value.
	 */
	public function __toString() {
		if ( null === $this->string ) {
			$this->string = call_user_func( $this->value_callback );
			$this->resolved();
		}

		return $this->string;
	}

	/**
	 * Returns the HTML ready, escaped version of the string.
	 *
	 * @since 4.9.16
	 *
	 * @return string The escaped version of the string.
	 */
	public function escaped() {
		if ( null !== $this->escaped ) {
			return $this->escaped;
		}

		$this->escaped = empty( $this->escape_callback )
			? $this->__toString()
			: call_user_func( $this->escape_callback, $this->__toString() );

		return $this->escaped;
	}

	/**
	 * Returns the string value, just a proxy of the `__toString` method.
	 *
	 * @since 4.9.16
	 *
	 * @return string The string value.
	 */
	public function value() {
		return $this->__toString();
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since 4.9.16
	 */
	public function serialize() {
		$serialized = serialize( [ $this->__toString(), $this->escaped() ] );

		unset( $this->value_callback, $this->escape_callback );

		return $serialized;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since 4.9.16
	 */
	public function unserialize( $serialized ) {
		list( $string, $escaped ) = unserialize( $serialized );
		$this->string  = $string;
		$this->escaped = $escaped;
	}

	/**
	 * {@inheritDoc}
	 */
	public function jsonSerialize() {
		return $this->value();
	}
}