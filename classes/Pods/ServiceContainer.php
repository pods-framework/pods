<?php

/**
 * @package Pods
 * Class Pods_Service_Container
 */
class Pods_Service_Container implements
	ArrayAccess {

	private $values = array();

	private $services = array();

	private $aliases = array();

	private $locked = array();

	/**
	 * @param array $values
	 */
	function __construct( array $values = array() ) {
		$this->values = $values;
	}

	/**
	 * @param mixed $id
	 * @param mixed $value
	 */
	public function offsetSet( $id, $value ) {
		if ( is_callable( $value ) || $value instanceof Pods_Service ) {
			$this->services[ $id ] = $value;
		} elseif ( 0 === strpos( $value, '@' ) ) {
			$this->aliases[ $id ] = substr( $value, 1 );
		} else {
			$this->values[ $id ] = $value;
		}

	}

	/**
	 * @param mixed $id
	 *
	 * @return mixed
	 * @throws InvalidArgumentException
	 */
	public function offsetGet( $id ) {
		if ( isset( $this->locked[ $id ] ) ) {
			throw new InvalidArgumentException( 'Circular dependency found.' );
		}
		$this->locked[ $id ] = true;
		$value               = $this->getId( $id );
		unset( $this->locked[ $id ] );

		return $value;
	}

	/**
	 * @param mixed $id
	 *
	 * @return bool
	 */
	public function offsetExists( $id ) {
		if ( isset( $this->values[ $id ] ) || isset( $this->services[ $id ] ) || isset( $this->aliases[ $id ] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * @param mixed $id
	 */
	public function offsetUnset( $id ) {
		unset( $this->values[ $id ] );
		unset( $this->services[ $id ] );
		unset( $this->aliases[ $id ] );
	}

	/**
	 * @param $id
	 *
	 * @return mixed|null|object
	 */
	private function getId( $id ) {
		if ( isset( $this->aliases[ $id ] ) ) {
			return $this->offsetGet( $this->aliases[ $id ] );
		}

		if ( isset( $this->values[ $id ] ) ) {
			return $this->values[ $id ];
		}

		if ( isset( $this->services[ $id ] ) ) {
			return $this->values[ $id ] = $this->build( $this->services[ $id ] );
		}

		return null;

	}

	/**
	 * @param $service
	 *
	 * @return mixed|object
	 */
	public function build( $service ) {
		if ( is_callable( $service ) ) {
			return call_user_func( $service, $this );
		}

		$reflection = new ReflectionClass( $this->resolve( $service->class ) );
		$instance   = $reflection->newinstance( $this->resolve( $service->arguments ) );

		// @TODO add ability to call methods in functions for the current instance(ex: for more detailed setups)

		return $instance;
	}

	/**
	 * @param $value
	 *
	 * @return array|mixed|null|void
	 */
	protected function resolve( $value ) {
		$return = null;
		if ( is_array( $value ) ) {
			foreach ( $value as $key => $val ) {
				$value[ $key ] = $this->resolve( $val );
			}
			$return = $value;
		} elseif ( is_string( $value ) && '@' == $value[0] ) {
			$return = $this->offsetGet( substr( $value, 1 ) );
			if ( is_null( $return ) ) {
				$option = get_option( $value );
				if ( false !== $option ) {
					$return = $option;
				}
			}
		}

		return $return;
	}

}
