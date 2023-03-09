<?php

namespace Pods;

use Closure;
use Exception;
use Pods\Whatsit\Field;
use Pods\Whatsit\Group;
use Pods\Whatsit\Object_Field;
use Pods\Whatsit\Store;

/**
 * Whatsit abstract class.
 *
 * Why did we name this "Whatsit"? Because we wanted "Object" but that's a reserved PHP keyword,
 * and we couldn't think of a better name. We said "let's just use Whatsit for now" and then we
 * never changed it. Enjoy!
 *
 * @method string      get_object_type()
 * @method string|null get_object_storage_type()
 * @method string|null get_name()
 * @method string|null get_id()
 * @method string|null get_parent()
 * @method string|null get_group()
 * @method string|null get_type()
 * @method string|null get_parent_identifier()
 * @method string|null get_parent_object_type()
 * @method string|null get_parent_object_storage_type()
 * @method string|null get_parent_name()
 * @method string|null get_parent_id()
 * @method string|null get_parent_label()
 * @method string|null get_parent_description()
 * @method string|null get_parent_type()
 * @method string|null get_group_identifier()
 * @method string|null get_group_object_type()
 * @method string|null get_group_object_storage_type()
 * @method string|null get_group_name()
 * @method string|null get_group_id()
 * @method string|null get_group_label()
 * @method string|null get_group_description()
 * @method string|null get_group_type()
 *
 * @since 2.8.0
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
		'object_type'         => '',
		'object_storage_type' => 'collection',
		'name'                => '',
		'id'                  => '',
		'parent'              => '',
		'group'               => '',
		'label'               => '',
		'description'         => '',
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
				$store = Store::get_instance();

				// Attempt to get from storage directly.
				$object = $store->get_object_from_storage( isset( $args['object_storage_type'] ) ? $args['object_storage_type'] : null, $args['id'] );

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
			$store = Store::get_instance();

			// Attempt to get from storage directly.
			$object = $store->get_object_from_storage( isset( $args['object_storage_type'] ) ? $args['object_storage_type'] : null, $array['id'] );

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

		// Handle closures that cannot be serialized.
		if ( isset( $this->args['data'] ) && $this->args['data'] instanceof Closure ) {
			$this->args['data'] = $this->args['data']();
		}

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
	#[\ReturnTypeWillChange]
	public function jsonSerialize() {
		$args = $this->get_args();

		// Handle closures that cannot be serialized.
		if ( isset( $args['data'] ) && $args['data'] instanceof Closure ) {
			$args['data'] = $args['data']();
		}

		return $args;
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
	#[\ReturnTypeWillChange]
	public function rewind() {
		$this->position = 0;
	}

	/**
	 * {@inheritdoc}
	 */
	#[\ReturnTypeWillChange]
	public function current() {
		$args = $this->getArrayCopy();

		return $args[ $this->position ];
	}

	/**
	 * {@inheritdoc}
	 */
	#[\ReturnTypeWillChange]
	public function key() {
		return $this->position;
	}

	/**
	 * {@inheritdoc}
	 */
	#[\ReturnTypeWillChange]
	public function next() {
		$this->position ++;
	}

	/**
	 * {@inheritdoc}
	 */
	#[\ReturnTypeWillChange]
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
	#[\ReturnTypeWillChange]
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
	#[\ReturnTypeWillChange]
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
	#[\ReturnTypeWillChange]
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
	#[\ReturnTypeWillChange]
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
			'object_storage_type' => $this->get_arg( 'object_storage_type', 'collection' ),
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

		/**
		 * Allow further adjustments after a Whatsit object is set up.
		 *
		 * @since 2.9.8
		 *
		 * @param Whatsit $object      Whatsit object.
		 * @param string  $object_type The Whatsit object type.
		 */
		do_action( 'pods_whatsit_setup', $this, static::$type );

		// Make a hook friendly name.
		$class_hook = static::$type;

		/**
		 * Allow further adjustments after a Whatsit object is set up for a specific object class.
		 *
		 * Example hook names:
		 * - pods_whatsit_setup_pod
		 * - pods_whatsit_setup_group
		 * - pods_whatsit_setup_object-field
		 * - pods_whatsit_setup_field
		 * - pods_whatsit_setup_template
		 * - pods_whatsit_setup_page
		 *
		 * @since 2.9.8
		 *
		 * @param Whatsit $object     Whatsit object.
		 * @param string  $object_type The Whatsit object type.
		 */
		do_action( "pods_whatsit_setup_{$class_hook}", $this, static::$type );

		// If the type is a Pod or Group and types-only mode is enabled, force the groups/fields to be empty.
		if (
			(
				'pod' === static::$type
				|| 'group' === static::$type
			)
			&& pods_is_types_only()
		) {
			$this->_groups = [];
			$this->_fields = [];
		}
	}

	/**
	 * Set the arguments for the object.
	 *
	 * @param array|Whatsit $args    List of object arguments to set.
	 * @param bool          $replace Whether to replace the argument if it was already set.
	 *
	 * @return self The object.
	 */
	public function set_args( $args, $replace = true ) {
		if ( $args instanceof self ) {
			$args = $args->get_args();
		}

		// Invalid arguments received.
		if ( ! is_array( $args ) ) {
			return $this;
		}

		// Set up the options if they were provided.
		if ( isset( $args['options'] ) ) {
			$args = array_merge( $args['options'], $args );

			unset( $args['options'] );
		}

		foreach ( $args as $arg => $value ) {
			// Skip arguments if we are not replacing them but they are already set.
			if (
				! $replace
				&& ! empty( $this->args[ $arg ] )
				&& '' !== $this->args[ $arg ]
			) {
				continue;
			}

			$this->set_arg( $arg, $value );
		}

		return $this;
	}

	/**
	 * Get object argument value.
	 *
	 * @param string     $arg     Argument name.
	 * @param mixed|null $default Default to use if not set.
	 * @param bool       $strict  Whether to check only normal arguments and not special arguments.
	 *
	 * @return null|mixed Argument value, or null if not set.
	 */
	public function get_arg( $arg, $default = null, $strict = false ) {
		$arg = (string) $arg;

		$special_args = [
			'identifier'    => 'get_identifier',
			'label'         => 'get_label',
			'description'   => 'get_description',
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

		// Enforce lowercase 'id' argument usage.
		if ( 'ID' === $arg ) {
			$arg = 'id';
		}

		$is_set = isset( $this->args[ $arg ] );

		if ( ! $is_set && ! $strict ) {
			if ( 'internal' === $arg ) {
				return $default;
			}

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

		}//end if

		$value = $is_set ? $this->args[ $arg ] : $default;

		/**
		 * Allow filtering the object arguments / options.
		 *
		 * @since 2.8.4
		 *
		 * @param mixed   $value  The object argument value.
		 * @param string  $name   The argument name.
		 * @param Whatsit $object The object.
		 */
		return apply_filters( 'pods_whatsit_get_arg', $value, $arg, $this );
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
			'object_storage_type',
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

		if ( 'options' === $arg && is_array( $value ) ) {
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
	 * Override table info when it needs to be set, for fields for example.
	 *
	 * @since 2.8.0
	 *
	 * @param array $table_info The table information to be referenced by this object.
	 */
	public function set_table_info( $table_info ) {
		$this->_table_info = $table_info;
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
	 * Get object label.
	 *
	 * @return string Object label.
	 */
	public function get_label() {
		$label = '';

		if ( isset( $this->args['label'] ) ) {
			$label = $this->args['label'];
		}

		/**
		 * Allow filtering the object label.
		 *
		 * @since 2.8.0
		 *
		 * @param string  $label  The object label.
		 * @param Whatsit $object The object.
		 */
		return apply_filters( 'pods_whatsit_get_label', $label, $this );
	}

	/**
	 * Get object description.
	 *
	 * @return string Object description.
	 */
	public function get_description() {
		$description = '';

		if ( isset( $this->args['description'] ) ) {
			$description = $this->args['description'];
		}

		/**
		 * Allow filtering the object description.
		 *
		 * @since 2.8.0
		 *
		 * @param string  $description The object description.
		 * @param Whatsit $object      The object.
		 */
		return apply_filters( 'pods_whatsit_get_description', $description, $this );
	}

	/**
	 * Get list of object arguments.
	 *
	 * @return array List of object arguments.
	 */
	public function get_args() {
		/**
		 * Allow filtering the object arguments.
		 *
		 * @since 2.8.4
		 *
		 * @param array   $args   The object arguments.
		 * @param Whatsit $object The object.
		 */
		return apply_filters( 'pods_whatsit_get_args', $this->args, $this );
	}

	/**
	 * Get list of clean object arguments.
	 *
	 * @return array List of clean object arguments.
	 */
	public function get_clean_args() {
		$args = $this->args;

		$excluded_args = [
			'object_type',
			'object_storage_type',
			'parent',
			'group',
		];

		foreach ( $excluded_args as $excluded_arg ) {
			if ( isset( $args[ $excluded_arg ] ) ) {
				unset( $args[ $excluded_arg ] );
			}
		}

		return $args;
	}

	/**
	 * Get object parent.
	 *
	 * @return Whatsit|null Object parent, or null if not set.
	 */
	public function get_parent_object() {
		$parent = $this->get_parent();

		if ( $parent ) {
			$store = Store::get_instance();

			// Attempt to get from storage directly.
			$parent = $store->get_object_from_storage( $this->get_object_storage_type(), $parent );
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
			$store = Store::get_instance();

			// Attempt to get from storage directly.
			$group = $store->get_object_from_storage( $this->get_object_storage_type(), $group );

			if ( $group ) {
				$this->set_arg( 'group', $group->get_identifier() );
			}
		}

		return $group;
	}

	/**
	 * Maybe get the list of objects or determine if they need to be loaded.
	 *
	 * @since 2.9.10
	 *
	 * @param array $objects The list of objects or their identifiers.
	 * @param array $args    The list of arguments to filter by.
	 *
	 * @return Whatsit[]|null The list of objects or null if they need to be loaded separately.
	 */
	protected function maybe_get_objects_by_identifier( array $objects, $args ) {
		$api = pods_api();

		$object_collection = Store::get_instance();

		$storage_type = ! empty( $args['object_storage_type'] ) ? $args['object_storage_type'] : $api->get_default_object_storage_type();

		/** @var \Pods\Whatsit\Storage\Post_Type $storage_object */
		$storage_object = $object_collection->get_storage_object( $storage_type );

		$parent = $this;

		// Check if we have at least the object field.
		if ( ! empty( $objects ) ) {
			$first_object = reset( $objects );

			// Check if this is an identifier.
			if ( is_string( $first_object ) ) {
				// We likely don't have any of these objects so just fetch them together normally as that's quicker.
				return null;
			}
		}

		$found_identifier = false;

		// Build any objects from identifiers that are needed.
		$objects = array_map(
			static function( $identifier ) use ( $storage_object, $parent, &$found_identifier ) {
				if ( $identifier instanceof Whatsit ) {
					return $identifier;
				}

				$found_identifier = true;

				return $storage_object->get_by_identifier( $identifier, $parent );
			},
			$objects
		);

		if ( ! $found_identifier ) {
			return $objects;
		}

		$objects = array_filter( $objects );
		$names   = wp_list_pluck( $objects, 'name' );

		return array_combine( $names, $objects );
	}

	/**
	 * Fetch field from object with no traversal support.
	 *
	 * @param string $field_name    Field name.
	 * @param bool   $load_all      Whether to load all fields when getting this field.
	 * @param bool   $check_aliases Whether to check aliases if field not found.
	 *
	 * @return Field|null Field object, or null if object not found.
	 */
	public function fetch_field( $field_name, $load_all = true, $check_aliases = true ) {
		$get_fields_args = [];

		if ( ! $load_all ) {
			$get_fields_args = [
				'name' => $field_name,
			];
		}

		$fields = $this->get_fields( $get_fields_args );

		$field = null;

		if ( isset( $fields[ $field_name ] ) ) {
			$field = $fields[ $field_name ];
		} else {
			$object_fields = $this->get_object_fields();

			if ( isset( $object_fields[ $field_name ] ) ) {
				$field = $object_fields[ $field_name ];
			} elseif ( $check_aliases ) {
				foreach ( $fields as $the_field ) {
					if ( ! empty( $the_field['alias'] ) && in_array( $field_name, $the_field['alias'], true ) ) {
						$field = $the_field;

						break;
					}
				}

				if ( ! $field && ! empty( $object_fields ) ) {
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

		return $field;
	}

	/**
	 * Get field from object with traversal support.
	 *
	 * @param string      $field_name    Field name.
	 * @param null|string $arg           Argument name.
	 * @param bool        $load_all      Whether to load all fields when getting this field.
	 * @param bool        $check_aliases Whether to check aliases if field not found.
	 *
	 * @return Field|mixed|null Field object, argument value, or null if object not found.
	 */
	public function get_field( $field_name, $arg = null, $load_all = true, $check_aliases = true ) {
		$fields_to_traverse = explode( '.', $field_name );
		$fields_to_traverse = array_filter( $fields_to_traverse );

		$total_fields_to_traverse = count( $fields_to_traverse );

		$field = null;

		/** @var Whatsit $whatsit */
		$whatsit = $this;

		for ( $f = 0; $f < $total_fields_to_traverse; $f ++ ) {
			$field_to_traverse = $fields_to_traverse[ $f ];

			$field = $whatsit->fetch_field( $field_to_traverse, $load_all, $check_aliases );

			// Check if there are more fields to traverse.
			if ( ( $f + 1 ) === $total_fields_to_traverse ) {
				break;
			}

			// Check if the field is traversable.
			if ( ! $field instanceof Field ) {
				$field = null;

				break;
			}

			// Fill in the next object data.
			$whatsit = $field->get_related_object();

			// Check if the related object exists.
			if ( ! $whatsit instanceof Whatsit ) {
				$field = null;

				break;
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
	 * @param array $args List of arguments to filter by.
	 *
	 * @return Field[] List of field objects.
	 */
	public function get_fields( array $args = [] ) {
		if ( [] === $this->_fields ) {
			return [];
		}

		$api = pods_api();

		$has_custom_args = ! empty( $args );

		if ( null !== $this->_fields && ! $has_custom_args ) {
			$objects = $this->maybe_get_objects_by_identifier( $this->_fields, $args );

			if ( is_array( $objects ) ) {
				$this->_fields = pods_clone_objects( $objects );

				/** @var Field[] $objects */
				return $objects;
			}
		}

		$filtered_args = [
			'parent'            => $this->get_id(),
			'parent_id'         => $this->get_id(),
			'parent_name'       => $this->get_name(),
			'parent_identifier' => $this->get_identifier(),
		];

		if ( empty( $filtered_args['parent_id'] ) ) {
			$filtered_args['bypass_post_type_find'] = true;
		}

		$filtered_args = array_filter( $filtered_args );

		$args = array_merge( [
			'orderby'           => 'menu_order title',
			'order'             => 'ASC',
		], $filtered_args, $args );

		try {
			if ( ! empty( $args['object_type'] ) ) {
				$objects = $api->_load_objects( $args );
			} else {
				$objects = $api->load_fields( $args );
			}
		} catch ( Exception $exception ) {
			pods_debug_log( $exception );

			$objects = [];
		}

		if ( ! $has_custom_args ) {
			$this->_fields = pods_clone_objects( $objects );
		}

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
	 * Determine whether the object has fields.
	 *
	 * @param array $args List of arguments to filter by.
	 *
	 * @return bool Whether the object has fields.
	 */
	public function has_fields( array $args = [] ) {
		$count = $this->count_fields( $args );

		return 0 < $count;
	}

	/**
	 * Count the number of fields the object has.
	 *
	 * @param array $args List of arguments to filter by.
	 *
	 * @return int The number of fields the object has.
	 */
	public function count_fields( array $args = [] ) {
		if ( [] === $this->_fields ) {
			return 0;
		}

		$has_custom_args = ! empty( $args );

		if ( null !== $this->_fields && ! $has_custom_args ) {
			return count( $this->_fields );
		}

		$filtered_args = [
			'parent'            => $this->get_id(),
			'parent_id'         => $this->get_id(),
			'parent_name'       => $this->get_name(),
			'parent_identifier' => $this->get_identifier(),
		];

		if ( empty( $filtered_args['parent_id'] ) ) {
			$filtered_args['bypass_post_type_find'] = true;
		}

		$filtered_args = array_filter( $filtered_args );

		$args = array_merge( $filtered_args, $args );

		// Enforce argument.
		$args['count'] = true;

		try {
			$api = pods_api();

			if ( ! empty( $args['object_type'] ) ) {
				$total_objects = $api->_load_objects( $args );
			} else {
				$total_objects = $api->load_fields( $args );
			}
		} catch ( Exception $exception ) {
			pods_debug_log( $exception );

			$total_objects = 0;
		}

		return $total_objects;
	}

	/**
	 * Determine whether the object has object fields.
	 *
	 * @return bool Whether the object has object fields.
	 */
	public function has_object_fields() {
		$count = $this->count_object_fields();

		return 0 < $count;
	}

	/**
	 * Count the number of object fields the object has.
	 *
	 * @return int The number of object fields the object has.
	 */
	public function count_object_fields() {
		return 0;
	}

	/**
	 * Get groups for object.
	 *
	 * @param array $args List of arguments to filter by.
	 *
	 * @return Group[] List of group objects.
	 */
	public function get_groups( array $args = [] ) {
		if ( [] === $this->_groups ) {
			return [];
		}

		$api = pods_api();

		$has_custom_args = ! empty( $args );

		if ( null !== $this->_groups && ! $has_custom_args ) {
			$objects = $this->maybe_get_objects_by_identifier( $this->_groups, $args );

			if ( is_array( $objects ) ) {
				$this->_groups = $objects;

				/** @var Group[] $objects */
				return $objects;
			}
		}

		$filtered_args = [
			'parent'            => $this->get_id(),
			'parent_id'         => $this->get_id(),
			'parent_name'       => $this->get_name(),
			'parent_identifier' => $this->get_identifier(),
		];

		if ( empty( $filtered_args['parent_id'] ) ) {
			$filtered_args['bypass_post_type_find'] = true;
		}

		$filtered_args = array_filter( $filtered_args );

		$args = array_merge( [
			'orderby'           => 'menu_order title',
			'order'             => 'ASC',
		], $filtered_args, $args );

		try {
			if ( ! empty( $args['object_type'] ) ) {
				$objects = $api->_load_objects( $args );
			} else {
				$objects = $api->load_groups( $args );
			}
		} catch ( Exception $exception ) {
			pods_debug_log( $exception );

			$objects = [];
		}

		if ( ! $has_custom_args ) {
			$this->_groups = wp_list_pluck( $objects, 'identifier' );
		}

		return $objects;
	}

	/**
	 * Determine whether the object has groups.
	 *
	 * @param array $args List of arguments to filter by.
	 *
	 * @return bool Whether the object has groups.
	 */
	public function has_groups( array $args = [] ) {
		$count = $this->count_groups( $args );

		return 0 < $count;
	}

	/**
	 * Count the number of groups the object has.
	 *
	 * @param array $args List of arguments to filter by.
	 *
	 * @return int The number of groups the object has.
	 */
	public function count_groups( array $args = [] ) {
		if ( [] === $this->_groups ) {
			return 0;
		}

		$has_custom_args = ! empty( $args );

		if ( null !== $this->_groups && ! $has_custom_args ) {
			return $this->_groups;
		}

		$filtered_args = [
			'parent'            => $this->get_id(),
			'parent_id'         => $this->get_id(),
			'parent_name'       => $this->get_name(),
			'parent_identifier' => $this->get_identifier(),
		];

		if ( empty( $filtered_args['parent_id'] ) ) {
			$filtered_args['bypass_post_type_find'] = true;
		}

		$filtered_args = array_filter( $filtered_args );

		$args = array_merge( $filtered_args, $args );

		// Enforce argument.
		$args['count'] = true;

		try {
			$api = pods_api();

			if ( ! empty( $args['object_type'] ) ) {
				$total_objects = $api->_load_objects( $args );
			} else {
				$total_objects = $api->load_groups( $args );
			}
		} catch ( Exception $exception ) {
			pods_debug_log( $exception );

			$total_objects = 0;
		}

		return $total_objects;
	}

	/**
	 * Get table information for object.
	 *
	 * @return array Table information for object.
	 */
	public function get_table_info() {
		if ( null !== $this->_table_info ) {
			return $this->_table_info;
		}

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
	 * Get the pod table name for object.
	 *
	 * @since 2.8.9
	 *
	 * @return string|null The Pod table name for object or null if not set.
	 */
	public function get_pod_table_name() {
		$table_info = $this->get_table_info();

		if ( ! empty( $table_info['pod_table'] ) ) {
			return $table_info['pod_table'];
		}

		return null;
	}

	/**
	 * Get the object storage type label.
	 *
	 * @since 2.8.24
	 *
	 * @return string|null
	 */
	public function get_object_storage_type_label() {
		$object_storage_type = $this->get_arg( 'object_storage_type', 'collection' );

		if ( ! $object_storage_type ) {
			return null;
		}

		$object_collection = Store::get_instance();

		$storage_type_obj = $object_collection->get_storage_object( $object_storage_type );

		if ( ! $storage_type_obj ) {
			return null;
		}

		return $storage_type_obj->get_label();
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
			'include_field_data'    => false,
			'include_object_fields' => false,
			'include_table_info'    => false,
			'build_default_group'   => false,
			'assoc_keys'            => false,
		];

		$args = array_merge( $defaults, $args );

		$data = $this->get_args();

		if ( $args['include_groups'] ) {
			$data['groups'] = $this->get_export_for_items( $this->get_groups(), [
				'include_groups'     => false,
				'include_fields'     => $args['include_group_fields'],
				'include_field_data' => $args['include_field_data'],
				'assoc_keys'         => $args['assoc_keys'],
			] );

			// If there are no groups, see if we need to build the default one.
			if ( $args['build_default_group'] && empty( $data['groups'] ) ) {
				$fields = [];

				if ( $args['include_group_fields'] ) {
					$fields = $this->get_args_for_items( $this->get_fields(), [
						'include_field_data' => $args['include_field_data'],
					] );

					if ( ! $args['assoc_keys'] ) {
						$fields = array_values( $fields );
					}
				}

				/**
				 * Filter the title of the Pods Metabox used in the post editor.
				 *
				 * @since unknown
				 *
				 * @param string  $title  The title to use, default is 'More Fields'.
				 * @param Whatsit $pod    Current Pods Object.
				 * @param array   $fields Array of fields that will go in the metabox.
				 * @param string  $type   The type of Pod.
				 * @param string  $name   Name of the Pod.
				 */
				$group_title = apply_filters( 'pods_meta_default_box_title', __( 'More Fields', 'pods' ), $this, $fields, $this->get_type(), $this->get_name() );

				$group_name  = sanitize_key( pods_js_name( sanitize_title( $group_title ) ) );

				$data['groups'][ $group_name ] = [
					'name'   => $group_name,
					'label'  => $group_title,
					'fields' => $fields,
				];
			}

			if ( ! $args['assoc_keys'] ) {
				$data['groups'] = array_values( $data['groups'] );
			}
		}

		if ( $args['include_fields'] ) {
			$data['fields'] = $this->get_args_for_items( $this->get_fields(), [
				'include_field_data' => $args['include_field_data'],
			] );

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
	 * @since 2.8.0
	 *
	 * @param Whatsit[] $items List of items.
	 * @param array     $args  List of arguments to customize what is returned.
	 *
	 * @return array List of item args.
	 */
	protected function get_args_for_items( array $items, array $args = [] ) {
		return array_map( static function ( $object ) use ( $args ) {
			/** @var Whatsit $object */
			$item_args = $object->get_args();

			// Include related field data if needed.
			if ( ! empty( $args['include_field_data'] ) ) {
				/** @var Whatsit\Field $object */
				$related_data = $object->get_related_object_data();

				if ( is_array( $related_data ) ) {
					$item_args['data'] = $related_data;
				}
			}

			return $item_args;
		}, $items );
	}

	/**
	 * Get export for items in an array.
	 *
	 * @since 2.8.0
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

		if ( 0 === strpos( $name, 'get_parent_' ) ) {
			// Handle parent method calls.
			$object = $this->get_parent_object();

			$method = explode( 'get_parent_', $name );
			$method = 'get_' . $method[1];
		} elseif ( 0 === strpos( $name, 'get_group_' ) ) {
			// Handle group method calls.
			$object = $this->get_group_object();

			$method = explode( 'get_group_', $name );
			$method = 'get_' . $method[1];
		}

		if ( $method ) {
			if ( ! $object ) {
				return null;
			}

			return call_user_func_array( [ $object, $method ], $arguments );
		}

		// Handle arg method calls.
		if ( 0 === strpos( $name, 'get_' ) ) {
			$arg = explode( 'get_', $name );
			$arg = $arg[1];

			$supported_args = [
				'object_type',
				'object_storage_type',
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

	/**
	 * Flush object of cached child data.
	 */
	public function flush() {
		$this->_fields        = null;
		$this->_groups        = null;
		$this->_object_fields = null;
		$this->_table_info    = null;
	}

}
