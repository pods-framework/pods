<?php

/**
 * @package Pods
 */
class PodsArray implements ArrayAccess {

	/**
	 * @var array|mixed
	 */
	private $__container = array();

	/**
	 * Alternative to get_object_vars to access an object as an array with simple functionality and accepts arrays to
	 * add additional functionality. Additional functionality includes validation and setting default data.
	 *
	 * @param mixed $container Object (or existing Array).
	 *
	 * @license http://www.gnu.org/licenses/gpl-2.0.html
	 * @since 2.0.0
	 */
	public function __construct( $container ) {

		if ( is_array( $container ) || is_object( $container ) ) {
			$this->__container = &$container;
		}
	}

	/**
	 * Set value from array usage $object['offset'] = 'value';
	 *
	 * @param mixed $offset Used to set index of Array or Variable name on Object.
	 * @param mixed $value  Value to be set.
	 *
	 * @return mixed
	 * @since 2.0.0
	 */
	#[\ReturnTypeWillChange]
	public function offsetSet( $offset, $value ) {

		if ( is_array( $this->__container ) ) {
			$this->__container[ $offset ] = $value;
		} else {
			$this->__container->{$offset} = $value;
		}

		return $value;
	}

	/**
	 * Get value from array usage $object['offset'];
	 *
	 * @param mixed $offset Used to get value of Array or Variable on Object.
	 *
	 * @return mixed|null
	 * @since 2.0.0
	 */
	#[\ReturnTypeWillChange]
	public function offsetGet( $offset ) {

		if ( is_array( $this->__container ) ) {
			if ( isset( $this->__container[ $offset ] ) ) {
				return $this->__container[ $offset ];
			}
		} elseif ( isset( $this->__container->$offset ) ) {
			return $this->__container->$offset;
		}

		return null;
	}

	/**
	 * Get value from array usage $object['offset'];
	 *
	 * @param mixed $offset Used to get value of Array or Variable on Object.
	 *
	 * @return bool
	 * @since 2.0.0
	 */
	#[\ReturnTypeWillChange]
	public function offsetExists( $offset ) {

		if ( is_array( $this->__container ) ) {
			return isset( $this->__container[ $offset ] );
		}

		return isset( $this->__container->$offset );
	}

	/**
	 * Get value from array usage $object['offset'];
	 *
	 * @param mixed $offset Used to unset index of Array or Variable on Object.
	 *
	 * @since 2.0.0
	 */
	#[\ReturnTypeWillChange]
	public function offsetUnset( $offset ) {

		if ( is_array( $this->__container ) ) {
			unset( $this->__container[ $offset ] );
		} else {
			unset( $this->__container->$offset );
		}
	}

	/**
	 * Validate value on a specific type and set default (if empty)
	 *
	 * @param mixed       $offset  Used to get value of Array or Variable on Object.
	 * @param mixed|null  $default Used to set default value if it doesn't exist.
	 * @param string|null $type    Used to force a specific type of variable (allowed: array, object, integer, absint,
	 *                             boolean).
	 * @param mixed|null  $extra   Used in advanced types of variables.
	 *
	 * @return array|bool|int|mixed|null|number|object
	 * @since 2.0.0
	 */
	public function validate( $offset, $default = null, $type = null, $extra = null ) {

		if ( ! $this->offsetExists( $offset ) ) {
			$this->offsetSet( $offset, $default );
		}

		$value = $this->offsetGet( $offset );

		if ( empty( $value ) && null !== $default && false !== $value ) {
			$value = $default;
		}

		if ( 'array' === $type || 'array_merge' === $type ) {
			if ( ! is_array( $value ) ) {
				$value = explode( ',', $value );
			}

			if ( 'array_merge' === $type && $value !== $default ) {
				$value = array_merge( $default, $value );
			}
		} elseif ( 'object' === $type || 'object_merge' === $type ) {
			if ( ! is_object( $value ) ) {
				if ( ! is_array( $value ) ) {
					$value = explode( ',', $value );
				}
				$value = (object) $value;
			}

			if ( 'object_merge' === $type && $value !== $default ) {
				$value = (object) array_merge( (array) $default, (array) $value );
			}
		} elseif ( 'integer' === $type || 'int' === $type || 'absint' === $type ) {
			if ( ! is_numeric( trim( $value ) ) ) {
				$value = 0;
			} else {
				$value = intval( $value );
			}

			if ( 'absint' === $type ) {
				$value = abs( $value );
			}
		} elseif ( 'boolean' === $type || 'bool' === $type ) {
			$value = (boolean) $value;
		} elseif ( 'in_array' === $type && is_array( $default ) ) {
			if ( is_array( $value ) ) {
				foreach ( $value as $k => $v ) {
					if ( ! in_array( $v, $extra, true ) ) {
						unset( $value[ $k ] );
					}
				}
			} elseif ( ! in_array( $value, $extra, true ) ) {
				$value = $default;
			}
		} elseif ( 'isset' === $type && is_array( $default ) ) {
			if ( is_array( $value ) ) {
				foreach ( $value as $k => $v ) {
					if ( ! isset( $extra[ $v ] ) ) {
						unset( $value[ $k ] );
					}
				}
			} elseif ( ! isset( $extra[ $value ] ) ) {
				$value = $default;
			}
		}//end if

		$this->offsetSet( $offset, $value );

		return $value;
	}

	/**
	 * Dump the PodsArray object to array
	 *
	 * @return array Array version of the object
	 *
	 * @since 2.0.0
	 */
	public function dump() {

		if ( is_array( $this->__container ) ) {
			return $this->__container;
		}

		return get_object_vars( $this->__container );
	}

	/**
	 * Mapping >> offsetSet
	 *
	 * @param mixed $offset Property name.
	 * @param mixed $value  Property value.
	 *
	 * @return mixed
	 * @since 2.0.0
	 */
	public function __set( $offset, $value ) {

		return $this->offsetSet( $offset, $value );
	}

	/**
	 * Mapping >> offsetGet
	 *
	 * @param mixed $offset Property name.
	 *
	 * @return mixed|null
	 * @since 2.0.0
	 */
	public function __get( $offset ) {

		return $this->offsetGet( $offset );
	}

	/**
	 * Mapping >> offsetExists
	 *
	 * @param mixed $offset Property name.
	 *
	 * @return bool
	 * @since 2.0.0
	 */
	public function __isset( $offset ) {

		return $this->offsetExists( $offset );
	}

	/**
	 * Mapping >> offsetUnset
	 *
	 * @param mixed $offset Property name.
	 *
	 * @since 2.0.0
	 */
	public function __unset( $offset ) {

		$this->offsetUnset( $offset );
	}
}
