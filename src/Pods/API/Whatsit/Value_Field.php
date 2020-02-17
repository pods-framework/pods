<?php

namespace Pods\API\Whatsit;

use Pods\Whatsit;
use Pods\Whatsit\Field;
use Pods\Whatsit\Object_Field;

/**
 * Value_Field class.
 *
 * @property string $value Value of field.
 *
 * @since 2.8
 */
class Value_Field implements \ArrayAccess {

	/**
	 * Value of field.
	 *
	 * @var mixed
	 */
	protected $_value = null;

	/**
	 * The field object.
	 *
	 * @var Field|Object_Field
	 */
	protected $_field;

	/**
	 * Value_Field constructor.
	 *
	 * @param Whatsit $field The field object.
	 */
	public function __construct( Whatsit $field ) {
		$this->_field = $field;
	}

	/**
	 * Easy to reference init static function for array_map() to use.
	 *
	 * @param Whatsit $field The field object.
	 *
	 * @return Value_Field
	 */
	public static function init( Whatsit $field ) {
		return new static( $field );
	}

	/**
	 * On cast to string, return object identifier.
	 *
	 * @return string Object identifier.
	 *
	 * @uses Whatsit::__toString()
	 */
	public function __toString() {
		return $this->_field->get_identifier();
	}

	/**
	 * Check if offset exists.
	 *
	 * @param mixed $offset Offset name.
	 *
	 * @return bool Whether the offset exists.
	 */
	public function offsetExists( $offset ) {
		return $this->__isset( $offset );
	}

	/**
	 * Get offset value.
	 *
	 * @param mixed $offset Offset name.
	 *
	 * @return mixed|null Offset value, or null if not set.
	 */
	public function &offsetGet( $offset ) {
		// We fake the pass by reference to avoid PHP errors for backcompat.
		$value = $this->__get( $offset );

		return $value;
	}

	/**
	 * Set offset value.
	 *
	 * @param mixed $offset Offset name.
	 * @param mixed $value  Offset value.
	 */
	public function offsetSet( $offset, $value ) {
		if ( null === $offset ) {
			// Do not allow $object[] additions.
			return;
		}

		$this->__set( $offset, $value );
	}

	/**
	 * Unset offset value.
	 *
	 * @param mixed $offset Offset name.
	 */
	public function offsetUnset( $offset ) {
		$this->__unset( $offset );
	}

	/**
	 * Check if offset exists.
	 *
	 * @param mixed $offset Offset name.
	 *
	 * @return bool Whether the offset exists.
	 *
	 * @uses Whatsit::__isset()
	 */
	public function __isset( $offset ) {
		if ( 'value' === $offset ) {
			return isset( $this->_value );
		}

		return $this->_field->__isset( $offset );
	}

	/**
	 * Get offset value.
	 *
	 * @param mixed $offset Offset name.
	 *
	 * @return mixed|null Offset value, or null if not set.
	 *
	 * @uses Whatsit::__get()
	 */
	public function __get( $offset ) {
		if ( 'value' === $offset ) {
			return $this->_value;
		}

		return $this->_field->__get( $offset );
	}

	/**
	 * Set offset value.
	 *
	 * @param mixed $offset Offset name.
	 * @param mixed $value  Offset value.
	 *
	 * @uses Whatsit::__set()
	 */
	public function __set( $offset, $value ) {
		if ( 'value' === $offset ) {
			$this->_value = $value;

			return;
		}

		$this->_field->__set( $offset, $value );
	}

	/**
	 * Unset offset value.
	 *
	 * @param mixed $offset Offset name.
	 *
	 * @uses Whatsit::__unset()
	 */
	public function __unset( $offset ) {
		if ( 'value' === $offset ) {
			$this->_value = null;

			return;
		}

		$this->_field->__unset( $offset );
	}

}
