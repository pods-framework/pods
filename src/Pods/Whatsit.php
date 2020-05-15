<?php

namespace Pods;

use Pods\Whatsit\Field;
use Pods\Whatsit\Group;
use Pods\Whatsit\Object_Field;
use Pods\Whatsit\Store;

/**
 * Whatsit abstract class.
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
abstract class Whatsit implements \ArrayAccess, \JsonSerializable, \Iterator {

	/**
	 * @var int
	 */
	private $position = 0;

	/**
	 * @var string
	 */
	protected static $type = 'object';

	/**
	 * @var array
	 * @noinspection PropertyCanBeStaticInspection
	 */
	protected $args = [
		'object_type'  => '',
		'storage_type' => 'collection',
		'name'         => '',
		'id'           => '',
		'parent'       => '',
		'group'        => '',
		'label'        => '',
		'description'  => '',
	];

	/**
	 * @var Field[]|null
	 */
	protected $_fields;

	/**
	 * @var Object_Field[]|null
	 */
	protected $_object_fields;

	/**
	 * @var Group[]|null
	 */
	protected $_groups;

	/**
	 * @var array|null
	 */
	protected $_table_info;

	/**
	 * Whatsit constructor.
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
	 * @todo Define storage per Whatsit.
	 *
	 */
	public function __construct( array $args = [] ) {
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
	 * @return Whatsit|array|null
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

			/** @var Whatsit $object */
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
	 * @return Whatsit|array|null
	 */
	public static function from_json( $json, $to_args = false ) {
		/** @noinspection PhpUsageOfSilenceOperatorInspection */
		$args = @json_decode( $json, true );

		if ( is_array( $args ) ) {
			if ( ! empty( $args['id'] ) ) {
				// Check if we already have an object registered and available.
				$object = Store::get_instance()->get_object( $args['id'] );

				if ( $object ) {
					if ( $to_args ) {
						return $object->get_args();
					}

					return $object;
				}
			}

			$called_class = get_called_class();

			/** @var Whatsit $object */
			$object = new $called_class( $args );

			if ( $to_args ) {
				return $object->get_args();
			}

			return $object;
		}//end if

		return null;
	}

	/**
	 * Setup object from an array configuration.
	 *
	 * @param array   $array   Array configuration.
	 * @param boolean $to_args Return as arguments array.
	 *
	 * @return Whatsit|array|null
	 */
	public static function from_array( array $array, $to_args = false ) {
		if ( ! empty( $array['id'] ) ) {
			// Check if we already have an object registered and available.
			$object = Store::get_instance()->get_object( $array['id'] );

			if ( $object ) {
				if ( $to_args ) {
					return $object->get_args();
				}

				return $object;
			}
		}

		$called_class = get_called_class();

		/** @var Whatsit $object */
		$object = new $called_class( $array );

		if ( $to_args ) {
			return $object->get_args();
		}

		return $object;
	}

	/**
	 * Override var_dump data that is used for debugging with.
	 *
	 * @return array Data for debugging with.
	 */
	public function __debugInfo() {
		return [
			'args' => $this->args,
		];
	}

	/**
	 * Override __set_state handling to limit it to passing in only $args.
	 *
	 * @return self Object with state set.
	 */
	public static function __set_state( $data ) {
		$args = [];

		if ( ! empty( $data['args'] ) ) {
			$args = $data['args'];
		}

		return new static( $args );
	}

	/**
	 * On serialization of this object, only include _args.
	 *
	 * @return array List of properties to serialize.
	 */
	public function __sleep() {
		// @todo If DB based config, return only name, id, parent, group
		// @todo Maybe set up a variable with the custom array and implement Serializable::serialize/unserialize
		/*
		$this->args = array(
			'object_type' => $this->args['object_type'],
			'name'        => $this->args['name'],
			'id'          => $this->args['id'],
			'parent'      => $this->args['parent'],
			'group'       => $this->args['group'],
		);
		*/
		return [
			'args',
		];
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
	 * {@inheritdoc}
	 */
	public function getArrayCopy() {
		return array_values( $this->args );
	}

	/**
	 * {@inheritdoc}
	 */
	public function rewind() {
		$this->position = 0;
	}

	/**
	 * {@inheritdoc}
	 */
	public function current() {
		$args = $this->getArrayCopy();

		return $args[ $this->position ];
	}

	/**
	 * {@inheritdoc}
	 */
	public function key() {
		return $this->position;
	}

	/**
	 * {@inheritdoc}
	 */
	public function next() {
		$this->position ++;
	}

	/**
	 * {@inheritdoc}
	 */
	public function valid() {
		$args = $this->getArrayCopy();

		return isset( $args[ $this->position ] );
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
	 */
	public function __isset( $offset ) {
		if ( is_int( $offset ) ) {
			$args = $this->getArrayCopy();

			return isset( $args[ $offset ] );
		}

		$special_args = [
			'fields'        => 'get_fields',
			'object_fields' => 'get_object_fields',
			'groups'        => 'get_groups',
			'table_info'    => 'get_table_info',
			'options'       => 'get_args',
		];

		if ( isset( $special_args[ $offset ] ) ) {
			return true;
		}

		$value = $this->get_arg( $offset, null );

		return ( null !== $value );
	}

	/**
	 * Get offset value.
	 *
	 * @param mixed $offset Offset name.
	 *
	 * @return mixed|null Offset value, or null if not set.
	 */
	public function __get( $offset ) {
		if ( is_int( $offset ) ) {
			$args = $this->getArrayCopy();

			return isset( $args[ $offset ] );
		}

		return $this->get_arg( $offset );
	}

	/**
	 * Set offset value.
	 *
	 * @param mixed $offset Offset name.
	 * @param mixed $value  Offset value.
	 */
	public function __set( $offset, $value ) {
		$this->set_arg( $offset, $value );
	}

	/**
	 * Unset offset value.
	 *
	 * @param mixed $offset Offset name.
	 */
	public function __unset( $offset ) {
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
	public function setup( array $args = [] ) {
		if ( empty( $args ) ) {
			$args = $this->get_args();
		}

		$this->_fields        = null;
		$this->_object_fields = null;
		$this->_groups        = null;
		$this->_table_info    = null;

		$defaults = [
			'object_type'  => $this->get_arg( 'object_type' ),
			'storage_type' => $this->get_arg( 'storage_type', 'collection' ),
			'name'         => '',
			'id'           => '',
			'parent'       => '',
			'group'        => '',
			'label'        => '',
			'description'  => '',
		];

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
	 * @param string     $arg     Argument name.
	 * @param mixed|null $default Default to use if not set.
	 *
	 * @return null|mixed Argument value, or null if not set.
	 */
	public function get_arg( $arg, $default = null ) {
		$arg = (string) $arg;

		$special_args = [
			'fields'        => 'get_fields',
			'object_fields' => 'get_object_fields',
			'all_fields'    => 'get_all_fields',
			'groups'        => 'get_groups',
			'table_info'    => 'get_table_info',
			'options'       => 'get_args',
		];

		if ( isset( $special_args[ $arg ] ) ) {
			return $this->{$special_args[ $arg ]}();
		}

		if ( ! isset( $this->args[ $arg ] ) ) {
			$table_info_fields = [
				'object_name',
				'object_hierarchical',
				'table',
				'meta_table',
				'pod_table',
				'field_id',
				'field_index',
				'field_slug',
				'field_type',
				'field_parent',
				'field_parent_select',
				'meta_field_id',
				'meta_field_index',
				'meta_field_value',
				'pod_field_id',
				'pod_field_index',
				'pod_field_slug',
				'pod_field_parent',
				'join',
				'where',
				'where_default',
				'orderby',
				'recurse',
			];

			if ( in_array( $arg, $table_info_fields, true ) ) {
				$table_info = $this->get_table_info();

				if ( isset( $table_info[ $arg ] ) ) {
					return $table_info[ $arg ];
				}
			}

			return $default;
		}//end if

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

		$reserved = [
			'object_type',
			'storage_type',
			'fields',
			'object_fields',
			'groups',
			'table_info',
			'name',
			'id',
			'parent',
			'group',
			'label',
			'description',
		];

		$read_only = [
			'object_type',
			'fields',
			'object_fields',
			'groups',
			'table_info',
		];

		if ( 'options' === $arg ) {
			$value = [];

			foreach ( $value as $real_arg => $real_value ) {
				$this->set_arg( $real_arg, $real_value );
			}

			return;
		}

		if ( in_array( $arg, $reserved, true ) ) {
			if ( in_array( $arg, $read_only, true ) ) {
				return;
			}

			if ( is_string( $value ) ) {
				$value = trim( $value );
			}

			$empty_values = [
				null,
				0,
				'0',
			];

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

		$parts = [
			$args['object_type'],
		];

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
	 * @return Whatsit|null Object parent, or null if not set.
	 */
	public function get_parent_object() {
		$parent = $this->get_parent();

		if ( $parent ) {
			$parent = Store::get_instance()->get_object( $parent );
		}

		return $parent;
	}

	/**
	 * Get object group.
	 *
	 * @return Whatsit|null Object group, or null if not set.
	 */
	public function get_group_object() {
		$group = $this->get_group();

		if ( $group ) {
			$group = Store::get_instance()->get_object( $group );

			if ( $group ) {
				$this->set_arg( 'group', $group->get_identifier() );
			}
		}

		return $group;
	}

	/**
	 * Get field from object.
	 *
	 * @param string      $field_name Field name.
	 * @param null|string $arg        Argument name.
	 *
	 * @return Field|mixed|null Field object, argument value, or null if object not found.
	 */
	public function get_field( $field_name, $arg = null ) {
		$fields = $this->get_fields();

		$field = null;

		if ( isset( $fields[ $field_name ] ) ) {
			$field = $fields[ $field_name ];
		} else {
			$object_fields = $this->get_object_fields();

			if ( isset( $object_fields[ $field_name ] ) ) {
				$field = $object_fields[ $field_name ];
			} else {
				foreach ( $fields as $the_field ) {
					if ( ! empty( $the_field['alias'] ) && in_array( $field_name, $the_field['alias'], true ) ) {
						$field = $the_field;

						break;
					}
				}

				if ( ! $field && isset( $object_fields ) ) {
					foreach ( $object_fields as $the_field ) {
						if ( ! empty( $the_field['alias'] ) && in_array( $field_name, $the_field['alias'], true ) ) {
							$field = $the_field;

							break;
						}
					}
				}
			}
		}

		if ( ! $field instanceof Field ) {
			return null;
		}

		if ( null !== $arg ) {
			return $field->get_arg( $arg );
		}

		return $field;
	}

	/**
	 * Get fields for object.
	 *
	 * @return Field[] List of field objects.
	 */
	public function get_fields() {
		if ( [] === $this->_fields ) {
			return [];
		}

		$object_collection = Store::get_instance();
		$storage_object    = $object_collection->get_storage_object( $this->get_arg( 'storage_type' ) );

		if ( ! $storage_object ) {
			return [];
		}

		if ( null === $this->_fields ) {
			$args = [
				'object_type'       => 'field',
				'orderby'           => 'menu_order title',
				'order'             => 'ASC',
				'parent'            => $this->get_id(),
				'parent_id'         => $this->get_id(),
				'parent_name'       => $this->get_name(),
				'parent_identifier' => $this->get_identifier(),
			];

			/** @var Field[] $objects */
			$objects = $storage_object->find( $args );

			$this->_fields = wp_list_pluck( $objects, 'id' );

			return $objects;
		}

		$objects = array_map( [ $object_collection, 'get_object' ], $this->_fields );
		$objects = array_filter( $objects );

		$names = wp_list_pluck( $objects, 'name' );

		$objects = array_combine( $names, $objects );

		return $objects;
	}

	/**
	 * Get object fields for object.
	 *
	 * @return Object_Field[] List of object field objects.
	 */
	public function get_object_fields() {
		return [];
	}

	/**
	 * Get all fields for object.
	 *
	 * @return Field[] List of field objects.
	 */
	public function get_all_fields() {
		return array_merge( $this->get_fields(), $this->get_object_fields() );
	}

	/**
	 * Get groups for object.
	 *
	 * @return Group[] List of group objects.
	 */
	public function get_groups() {
		if ( [] === $this->_groups ) {
			return [];
		}

		$object_collection = Store::get_instance();
		$storage_object    = $object_collection->get_storage_object( $this->get_arg( 'storage_type' ) );

		if ( ! $storage_object ) {
			return [];
		}

		if ( null === $this->_groups ) {
			$args = [
				'object_type'       => 'group',
				'orderby'           => 'menu_order title',
				'order'             => 'ASC',
				'parent'            => $this->get_id(),
				'parent_id'         => $this->get_id(),
				'parent_name'       => $this->get_name(),
				'parent_identifier' => $this->get_identifier(),
			];

			/** @var Group[] $objects */
			$objects = $storage_object->find( $args );

			$this->_groups = wp_list_pluck( $objects, 'id' );

			return $objects;
		}

		$objects = array_map( [ $object_collection, 'get_object' ], $this->_groups );
		$objects = array_filter( $objects );

		$names = wp_list_pluck( $objects, 'name' );

		$objects = array_combine( $names, $objects );

		return $objects;
	}

	/**
	 * Get table information for object.
	 *
	 * @return array Table information for object.
	 */
	public function get_table_info() {
		return [];
	}

	/**
	 * Get table name for object.
	 *
	 * @return string|null Table name for object or null if not set.
	 */
	public function get_table_name() {
		$table_info = $this->get_table_info();

		if ( ! empty( $table_info['table'] ) ) {
			return $table_info['table'];
		}

		return null;
	}

	/**
	 * Get the full data from the object.
	 *
	 * @param array $args List of arguments.
	 *
	 * @return array Full data from the object.
	 */
	public function export( array $args = [] ) {
		$defaults = [
			'include_groups'        => true,
			'include_group_fields'  => true,
			'include_fields'        => true,
			'include_object_fields' => false,
			'include_table_info'    => false,
			'assoc_keys'            => false,
		];

		$args = array_merge( $defaults, $args );

		$data = $this->get_args();

		if ( $args['include_groups'] ) {
			$data['groups'] = $this->get_export_for_items( $this->get_groups(), [
				'include_groups' => false,
				'include_fields' => $args['include_group_fields'],
				'assoc_keys'     => $args['assoc_keys'],
			] );

			if ( ! $args['assoc_keys'] ) {
				$data['groups'] = array_values( $data['groups'] );
			}
		}

		if ( $args['include_fields'] ) {
			$data['fields'] = $this->get_args_for_items( $this->get_fields() );

			if ( ! $args['assoc_keys'] ) {
				$data['fields'] = array_values( $data['fields'] );
			}
		}

		if ( $args['include_object_fields'] ) {
			$data['object_fields'] = $this->get_args_for_items( $this->get_object_fields() );

			if ( ! $args['assoc_keys'] ) {
				$data['object_fields'] = array_values( $data['object_fields'] );
			}
		}

		if ( $args['include_table_info'] ) {
			$data['table_info'] = $this->get_table_info();
		}

		return $data;
	}

	/**
	 * Get args for items in an array.
	 *
	 * @since 2.8
	 *
	 * @param Whatsit[] $items List of items.
	 *
	 * @return array List of item args.
	 */
	protected function get_args_for_items( array $items ) {
		return array_map( static function ( $object ) {
			/** @var Whatsit $object */
			return $object->get_args();
		}, $items );
	}

	/**
	 * Get export for items in an array.
	 *
	 * @since 2.8
	 *
	 * @param Whatsit[] $items List of items.
	 * @param array     $args  List of export arguments.
	 *
	 * @return array List of item exports.
	 */
	protected function get_export_for_items( array $items, array $args = [] ) {
		return array_map( static function ( $object ) use ( $args ) {
			/** @var Whatsit $object */
			return $object->export( $args );
		}, $items );
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
			return call_user_func_array( [ $object, $method ], $arguments );
		}

		// Handle arg method calls.
		if ( 0 === strpos( $name, 'get_' ) ) {
			$arg = explode( 'get_', $name );
			$arg = $arg[1];

			$supported_args = [
				'object_type',
				'storage_type',
				'name',
				'id',
				'parent',
				'group',
				'label',
				'description',
				'type',
			];

			$value = $this->get_arg( $arg );

			if ( ! empty( $value ) && in_array( $arg, $supported_args, true ) ) {
				return $value;
			}

			return null;
		}//end if

		return null;
	}

}
