<?php
/**
 * @package Pods
 * @category Utilities
 *
 * Class Pods_Service_Container
 */
class Pods_Service_Container implements
	ArrayAccess {

	static $instance;

	private $values = array();

	private $services = array();

	private $aliases = array();

	private $locked = array();

	/**
	 * @return Pods_Service_Container
	 */
	static function init() {
		if ( ! is_object( self::$instance ) ) {
			self::$instance = new Pods_Service_Container();
			do_action( 'pods_register_services', self::$instance );
		}

		return self::$instance;
	}

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
		$this->set( $id, $value );
	}

	/**
	 * @param mixed $id
	 *
	 * @return mixed
	 * @throws InvalidArgumentException
	 */
	public function offsetGet( $id ) {
		return $this->get( $id );
	}

	/**
	 * @param mixed $id
	 *
	 * @return bool
	 */
	public function offsetExists( $id ) {
		return $this->exists( $id );
	}

	/**
	 * @param mixed $id
	 */
	public function offsetUnset( $id ) {
		$this->remove( $id );
	}

	/**
	 * @param $id
	 *
	 * @throws InvalidArgumentException
	 * @return mixed|null|object
	 */
	public function get( $id ) {
		if ( isset( $this->locked[$id] ) ) {
			throw new InvalidArgumentException( 'Circular dependency found.' );
		}
		$this->locked[$id] = true;

		if ( isset( $this->aliases[$id] ) ) {
			return $this->get( $this->aliases[$id] );
		}

		if ( isset( $this->values[$id] ) ) {
			return $this->values[$id];
		}

		if ( isset( $this->services[$id] ) ) {
			return $this->values[$id] = $this->build( $this->services[$id] );
		}

		unset( $this->locked[$id] );

		return null;
	}

	/**
	 * @param $id
	 * @param $value
	 */
	public function set( $id, $value ) {
		if ( is_callable( $value ) || $value instanceof Pods_Service_Definition ) {
			$this->services[$id] = $value;
		} elseif ( 0 === strpos( $value, '@' ) ) {
			$this->aliases[$id] = substr( $value, 1 );
		} else {
			$this->values[$id] = $value;
		}
	}

	/**
	 * @param $id
	 *
	 * @return bool
	 */
	public function exists( $id ) {
		if ( isset( $this->values[$id] ) || isset( $this->services[$id] ) || isset( $this->aliases[$id] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * @param $id
	 */
	public function remove( $id ) {
		unset( $this->values[$id] );
		unset( $this->services[$id] );
		unset( $this->aliases[$id] );
	}

	/**
	 * @param Pods_Service_Definition|mixed $service
	 *
	 * @return mixed|object
	 */
	public function build( $service ) {
		if ( is_callable( $service ) ) {
			return call_user_func( $service, $this );
		}

		$reflection = new ReflectionClass( $this->resolve( $service->class ) );
		$instance   = $reflection->newinstance( $this->resolve( $service->parameters ) );

		// TODO add ability to call methods in functions for the current instance(ex: for more detailed setups)

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
				$value[$key] = $this->resolve( $val );
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
