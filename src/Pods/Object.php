<?php

/**
 * Pods_Object abstract class.
 *
 * @method string      get_object_type()
 * @method string|null get_storage_type()
 * @method string|null get_name()
 * @method string|null get_id()
 * @method string|null get_parent()
 * @method string|null get_group()
 * @method string|null get_label()
 * @method string|null get_description()
 * @method string|null get_type()
 * @method string|null get_parent_identifier()
 * @method string|null get_parent_object_type()
 * @method string|null get_parent_storage_type()
 * @method string|null get_parent_name()
 * @method string|null get_parent_id()
 * @method string|null get_parent_label()
 * @method string|null get_parent_description()
 * @method string|null get_parent_type()
 * @method string|null get_group_identifier()
 * @method string|null get_group_object_type()
 * @method string|null get_group_storage_type()
 * @method string|null get_group_name()
 * @method string|null get_group_id()
 * @method string|null get_group_label()
 * @method string|null get_group_description()
 * @method string|null get_group_type()
 *
 * @since 2.8
 */
abstract class Pods_Object implements ArrayAccess, JsonSerializable {

	/**
	 * @var string
	 */
	protected static $type = 'object';

	/**
	 * @var array
	 */
	protected $args = array(
		'object_type'  => '',
		'storage_type' => '',
		'name'         => '',
		'id'           => '',
		'parent'       => '',
		'group'        => '',
		'label'        => '',
		'description'  => '',
	);

	/**
	 * @var array|null
	 */
	protected $_fields;

	/**
	 * @var array|null
	 */
	protected $_object_fields;

	/**
	 * @var array|null
	 */
	protected $_table_info;

	/**
	 * @var string
	 */
	protected $storage_type = '';

	/**
	 * Pods_Object constructor.
	 *
	 * @todo Define storage per Pods_Object.
	 *
	 * @param array     $args        {
	 *                               Object arguments.
	 *
	 * @type string     $name        Object name.
	 * @type string|int $id          Object ID.
	 * @type string     $label       Object label.
	 * @type string     $description Object description.
	 * @type string|int $parent      Object parent name or ID.
	 * @type string|int $group       Object group name or ID.
	 * }
	 */
	public function __construct( array $args = array() ) {
		$this->args['object_type'] = static::$type;

		// Setup the object.
		$this->setup( $args );
	}

	/**
	 * Setup object from a serialized string.
	 *
	 * @param string  $serialized Serialized representation of the object.
	 * @param boolean $to_args    Return as arguments array.
	 *
	 * @return Pods_Object|array|null
	 */
	public static function from_serialized( $serialized, $to_args = false ) {
		$object = maybe_unserialize( $serialized );

		if ( $object instanceof self ) {
			if ( $to_args ) {
				return $object->get_args();
			}

			return $object;
		}

		if ( is_array( $object ) ) {
			$called_class = get_called_class();

			/** @var Pods_Object $object */
			$object = new $called_class( $object );

			if ( $to_args ) {
				return $object->get_args();
			}

			return $object;
		}

		return null;
	}

	/**
	 * Setup object from a JSON string.
	 *
	 * @param string  $json    JSON representation of the object.
	 * @param boolean $to_args Return as arguments array.
	 *
	 * @return Pods_Object|array|null
	 */
	public static function from_json( $json, $to_args = false ) {
		$args = @json_decode( $json, true );

		if ( is_array( $args ) ) {
			if ( ! empty( $args['id'] ) ) {
				// Check if we already have an object registered and available.
				$object = Pods_Object_Collection::get_instance()->get_object( $args['id'] );

				if ( $object ) {
					if ( $to_args ) {
						return $object->get_args();
					}

					return $object;
				}
			}

			$called_class = get_called_class();

			/** @var Pods_Object $object */
			$object = new $called_class( $args );

			if ( $to_args ) {
				return $object->get_args();
			}

			return $object;
		}

		return null;
	}

	/**
	 * Setup object from an array configuration.
	 *
	 * @param array   $array   Array configuration.
	 * @param boolean $to_args Return as arguments array.
	 *
	 * @return Pods_Object|array|null
	 */
	public static function from_array( array $array, $to_args = false ) {
		if ( ! empty( $array['id'] ) ) {
			// Check if we already have an object registered and available.
			$object = Pods_Object_Collection::get_instance()->get_object( $array['id'] );

			if ( $object ) {
				if ( $to_args ) {
					return $object->get_args();
				}

				return $object;
			}
		}

		$called_class = get_called_class();

		/** @var Pods_Object $object */
		$object = new $called_class( $array );

		if ( $to_args ) {
			return $object->get_args();
		}

		return $object;
	}

	/**
	 * On serialization of this object, only include _args.
	 *
	 * @return array List of properties to serialize.
	 */
	public function __sleep() {
		// @todo If DB based config, return only name, id, parent, group
		/*$this->args = array(
			'object_type' => $this->args['object_type'],
			'name'        => $this->args['name'],
			'id'          => $this->args['id'],
			'parent'      => $this->args['parent'],
			'group'       => $this->args['group'],
		);*/

		return array(
			'args',
		);
	}

	/**
	 * On unserialization of this object, setup the object.
	 */
	public function __wakeup() {
		// Setup the object.
		$this->setup();
	}

	/**
	 * Handle JSON encoding for object.
	 *
	 * @return array Object arguments.
	 */
	public function jsonSerialize() {
		return $this->get_args();
	}

	/**
	 * On cast to string, return object identifier.
	 *
	 * @return string Object identifier.
	 */
	public function __toString() {
		return $this->get_identifier();
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
	public function offsetGet( $offset ) {
		return $this->__get( $offset );
	}

	/**
	 * Set offset value.
	 *
	 * @param mixed $offset Offset name.
	 * @param mixed $value  Offset value.
	 */
	public function offsetSet( $offset, $value ) {
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
	 */
	public function __isset( $offset ) {
		// @todo Handle offsetExists for fields and other options.

		if ( isset( $this->args[ $offset ] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get offset value.
	 *
	 * @param mixed $offset Offset name.
	 *
	 * @return mixed|null Offset value, or null if not set.
	 */
	public function __get( $offset ) {
		// @todo Handle offsetGet for fields and other options.

		return $this->get_arg( $offset );
	}

	/**
	 * Set offset value.
	 *
	 * @param mixed $offset Offset name.
	 * @param mixed $value  Offset value.
	 */
	public function __set( $offset, $value ) {
		// @todo Handle offsetGet for fields and other options.

		$this->set_arg( $offset, $value );
	}

	/**
	 * Unset offset value.
	 *
	 * @param mixed $offset Offset name.
	 */
	public function __unset( $offset ) {
		// @todo Handle offsetUnset for fields and other options.

		$this->set_arg( $offset, null );
	}

	/**
	 * Setup object.
	 *
	 * @param array     $args        {
	 *                               Object arguments.
	 *
	 * @type string     $name        Object name.
	 * @type string|int $id          Object ID.
	 * @type string     $label       Object label.
	 * @type string     $description Object description.
	 * @type string|int $parent      Object parent name or ID.
	 * @type string|int $group       Object group name or ID.
	 * }
	 */
	public function setup( array $args = array() ) {
		if ( empty( $args ) ) {
			$args = $this->get_args();
		}

		$defaults = array(
			'object_type'  => $this->get_arg( 'object_type' ),
			'storage_type' => $this->get_arg( 'storage_type' ),
			'name'         => '',
			'id'           => '',
			'parent'       => '',
			'group'        => '',
			'label'        => '',
			'description'  => '',
		);

		$args = array_merge( $defaults, $args );

		// Reset arguments.
		$this->args = $defaults;

		foreach ( $args as $arg => $value ) {
			$this->set_arg( $arg, $value );
		}
	}

	/**
	 * Get object argument value.
	 *
	 * @param string $arg Argument name.
	 *
	 * @return null|mixed Argument value, or null if not set.
	 */
	public function get_arg( $arg ) {
		$arg = (string) $arg;

		$special_args = array(
			'fields'        => 'get_fields',
			'object_fields' => 'get_object_fields',
			'table_info'    => 'get_table_info',
		);

		if ( isset( $special_args[ $arg ] ) ) {
			return call_user_func( array( $this, $special_args[ $arg ] ) );
		}

		if ( ! isset( $this->args[ $arg ] ) ) {
			return null;
		}

		return $this->args[ $arg ];
	}

	/**
	 * Set object argument.
	 *
	 * @param string $arg   Argument name.
	 * @param mixed  $value Argument value.
	 */
	public function set_arg( $arg, $value ) {
		$arg = (string) $arg;

		$reserved = array(
			'object_type',
			'storage_type',
			'fields',
			'options',
			'name',
			'id',
			'parent',
			'group',
			'label',
			'description',
		);

		$read_only = array(
			'object_type',
			'fields',
			'options',
		);

		if ( in_array( $arg, $reserved, true ) ) {
			if ( in_array( $arg, $read_only, true ) ) {
				return;
			}

			if ( is_string( $value ) ) {
				$value = trim( $value );
			}

			$empty_values = array(
				null,
				0,
				'0',
			);

			if ( in_array( $value, $empty_values, true ) ) {
				$value = '';
			}
		}

		$this->args[ $arg ] = $value;
	}

	/**
	 * Check whether the object is valid.
	 *
	 * @return bool Whether the object is valid.
	 */
	public function is_valid() {
		if ( $this->get_name() ) {
			return true;
		}

		return false;
	}

	/**
	 * Get object identifier from arguments.
	 *
	 * @param array $args Object arguments.
	 *
	 * @return string|null Object identifier or if invalid object.
	 */
	public static function get_identifier_from_args( array $args ) {
		if ( empty( $args['object_type'] ) ) {
			return null;
		}

		$parts = array(
			$args['object_type'],
		);

		if ( isset( $args['parent'] ) && 0 < strlen( $args['parent'] ) ) {
			$parts[] = $args['parent'];
		}

		if ( isset( $args['name'] ) && 0 < strlen( $args['name'] ) ) {
			$parts[] = $args['name'];
		}

		return implode( '/', $parts );
	}

	/**
	 * Get object identifier.
	 *
	 * @return string Object identifier.
	 */
	public function get_identifier() {
		return self::get_identifier_from_args( $this->get_args() );
	}

	/**
	 * Get object arguments.
	 *
	 * @return array Object arguments.
	 */
	public function get_args() {
		return $this->args;
	}

	/**
	 * Get object parent.
	 *
	 * @return Pods_Object|null Object parent, or null if not set.
	 */
	public function get_parent_object() {
		$parent = $this->get_parent();

		if ( $parent ) {
			$parent = Pods_Object_Collection::get_instance()->get_object( $parent );
		}

		return $parent;
	}

	/**
	 * Get object group.
	 *
	 * @return Pods_Object|null Object group, or null if not set.
	 */
	public function get_group_object() {
		$group = $this->get_group();

		if ( $group ) {
			$group = Pods_Object_Collection::get_instance()->get_object( $group );

			if ( $group ) {
				$this->set_arg( 'group', $group->get_identifier() );
			}
		}

		return $group;
	}

	/**
	 * Get fields for object.
	 *
	 * @return Pods_Object[] List of field objects.
	 */
	public function get_fields() {
		if ( array() === $this->_fields ) {
			return array();
		}

		$object_collection = Pods_Object_Collection::get_instance();

		$storage_object = $object_collection->get_storage_object( $this->get_arg( 'storage_type' ) );

		if ( ! $storage_object ) {
			return array();
		}

		if ( null === $this->_fields ) {
			$args = array(
				'object_type'       => 'field',
				'parent'            => $this->get_id(),
				'parent_id'         => $this->get_id(),
				'parent_name'       => $this->get_name(),
				'parent_identifier' => $this->get_identifier(),
			);

			$fields = $storage_object->find( $args );

			$this->_fields = wp_list_pluck( $fields, 'id' );

			return $fields;
		}

		$fields = array_map( array( $object_collection, 'get_object' ), $this->_fields );
		$fields = array_filter( $fields );

		$names = wp_list_pluck( $fields, 'name' );

		$fields = array_combine( $names, $fields );

		return $fields;
	}

	/**
	 * Get object fields for object.
	 *
	 * @return Pods_Object[] List of object field objects.
	 */
	public function get_object_fields() {
		return array();
	}

	/**
	 * Get table information for object.
	 *
	 * @return array Table information for object.
	 */
	public function get_table_info() {
		return array();
	}

	/**
	 * Call magic methods.
	 *
	 * @param string $name      Method name.
	 * @param array  $arguments Method arguments.
	 *
	 * @return mixed|null
	 */
	public function __call( $name, $arguments ) {
		$object = null;
		$method = null;

		// Handle parent method calls.
		if ( 0 === strpos( $name, 'get_parent_' ) ) {
			$object = $this->get_parent_object();

			$method = explode( 'get_parent_', $name );
			$method = 'get_' . $method[1];
		}

		// Handle group method calls.
		if ( 0 === strpos( $name, 'get_group_' ) ) {
			$object = $this->get_group_object();

			$method = explode( 'get_group_', $name );
			$method = 'get_' . $method[1];
		}

		if ( $object && $method ) {
			return call_user_func_array( array( $object, $method ), $arguments );
		}

		// Handle arg method calls.
		if ( 0 === strpos( $name, 'get_' ) ) {
			$arg = explode( 'get_', $name );
			$arg = $arg[1];

			$supported_args = array(
				'object_type',
				'storage_type',
				'name',
				'id',
				'parent',
				'group',
				'label',
				'description',
				'type',
			);

			$value = $this->get_arg( $arg );

			if ( ! empty( $value ) && in_array( $arg, $supported_args, true ) ) {
				return $value;
			}

			return null;
		}

		return null;
	}

}
