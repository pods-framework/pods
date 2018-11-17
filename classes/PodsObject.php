<?php

/**
 * PodsObject abstract class.
 *
 * @method string|null get_parent_type()
 * @method string|null get_parent_id()
 * @method string|null get_parent_name()
 * @method string|null get_group_type()
 * @method string|null get_group_id()
 * @method string|null get_group_name()
 *
 * @since 2.8
 */
abstract class PodsObject implements ArrayAccess {

	/**
	 * @var array
	 */
	private $_args = array(
		'name'   => '',
		'id'     => '',
		'parent' => '',
		'group'  => '',
	);

	/**
	 * @var string
	 */
	private $_type = 'object';

	/**
	 * @var string
	 */
	private $_name = '';

	/**
	 * @var string
	 */
	private $_id = '';

	/**
	 * @var string
	 */
	private $_parent = '';

	/**
	 * @var string
	 */
	private $_group = '';

	/**
	 * PodsObject constructor.
	 *
	 * @todo Define storage per PodsObject.
	 *
	 * @param array $args {
	 *      Object arguments.
	 *
	 *      @type string     $name        Object name.
	 *      @type string|int $id          Object ID.
	 *      @type string     $label       Object label.
	 *      @type string     $description Object description.
	 *      @type string|int $parent      Object parent name or ID.
	 *      @type string|int $group       Object group name or ID.
	 * }
	 */
	public function __construct( array $args = array() ) {

		// Setup the object.
		$this->setup( $args );

	}

	/**
	 * Setup object from a serialized string.
	 *
	 * @param string  $serialized Serialized representation of the object.
	 * @param boolean $to_args    Return as arguments array if serialized string is an array.
	 *
	 * @return PodsObject|array|null
	 */
	public static function from_serialized( $serialized, $to_args = false ) {

		$object = maybe_unserialize( $serialized );

		if ( $object instanceof self ) {
			return $object;
		}

		if ( is_array( $object ) ) {
			if ( $to_args ) {
				return $object;
			}

			$called_class = get_called_class();

			return new $called_class( $object );
		}

		return null;

	}

	/**
	 * Setup object from a JSON string.
	 *
	 * @param string  $json    JSON representation of the object.
	 * @param boolean $to_args Return as arguments array.
	 *
	 * @return PodsObject|array|null
	 */
	public static function from_json( $json, $to_args = false ) {

		$args = @json_decode( $json, true );

		if ( is_array( $args ) ) {
			if ( $to_args ) {
				return $args;
			}

			$called_class = get_called_class();

			return new $called_class( $args );
		}

		return null;

	}

	/**
	 * Setup object from a Post ID or Post object.
	 *
	 * @param WP_Post|int $post    Post object or ID of the object.
	 * @param boolean     $to_args Return as arguments array.
	 *
	 * @return PodsObject|array|null
	 */
	public static function from_wp_post( $post, $to_args = false ) {

		$called_class = get_called_class();

		if ( ! $post instanceof WP_Post ) {
			$post = get_post( $post );
		}

		if ( empty( $post ) ) {
			return null;
		}

		$args = array(
			'name'        => $post->post_name,
			'id'          => $post->ID,
			'label'       => $post->post_title,
			'description' => $post->post_content,
		);

		if ( 0 < $post->post_parent ) {
			$args['parent'] = $post->post_parent;
		}

		$group = get_post_meta( $post->ID, 'group', true );

		if ( 0 < strlen( $group ) ) {
			$args['group'] = $group;
		}

		if ( $to_args ) {
			return $args;
		}

		return new $called_class( $args );

	}

	/**
	 * On serialization of this object, only include _args.
	 *
	 * @return array List of properties to serialize.
	 */
	public function __sleep() {

		// @todo If DB based config, return only name, id, parent, group
		/*$this->_args = array(
			'name'   => $this->_args['name'],
			'id'     => $this->_args['id'],
			'parent' => $this->_args['parent'],
			'group'  => $this->_args['group'],
		);*/

		return array(
			'_args',
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

		// @todo Handle offsetExists.
		return false;

	}

	/**
	 * Get offset value.
	 *
	 * @param mixed $offset Offset name.
	 *
	 * @return mixed|null Offset value, or null if not set.
	 */
	public function offsetGet( $offset ) {

		// @todo Handle offsetGet.
		return null;

	}

	/**
	 * Set offset value.
	 *
	 * @param mixed $offset Offset name.
	 * @param mixed $value  Offset value.
	 */
	public function offsetSet( $offset, $value ) {

		// @todo Handle offsetSet.

	}

	/**
	 * Unset offset value.
	 *
	 * @param mixed $offset Offset name.
	 */
	public function offsetUnset( $offset ) {

		// @todo Handle offsetUnset.

	}

	/**
	 * Setup object.
	 *
	 * @param array $args {
	 *      Object arguments.
	 *
	 *      @type string     $name        Object name.
	 *      @type string|int $id          Object ID.
	 *      @type string     $label       Object label.
	 *      @type string     $description Object description.
	 *      @type string|int $parent      Object parent name or ID.
	 *      @type string|int $group       Object group name or ID.
	 * }
	 */
	public function setup( array $args = array() ) {

		if ( ! empty( $args ) ) {
			$this->_args = $args;
		}

		$defaults = array(
			'name'   => '',
			'id'     => '',
			'parent' => '',
			'group'  => '',
		);

		$this->_args = array_merge( $defaults, $this->_args );

		// @todo Handle setup.

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
	 * Get object identifier.
	 *
	 * @return string Object identifier.
	 */
	public function get_identifier() {

		$parts = array(
			$this->_type,
		);

		if ( 0 < strlen( $this->_parent ) ) {
			$parts[] = $this->_parent;
		}

		if ( 0 < strlen( $this->_name ) ) {
			$parts[] = $this->_name;
		}

		return implode( '/', $parts );

	}

	/**
	 * Get object type.
	 *
	 * @return string Object type.
	 */
	public function get_type() {

		return $this->_type;

	}

	/**
	 * Get object ID.
	 *
	 * @return null|string Object ID or null if not set.
	 */
	public function get_id() {

		if ( $this->_id ) {
			return $this->_id;
		}

		return null;

	}

	/**
	 * Get object name.
	 *
	 * @return null|string Object name or null if not set.
	 */
	public function get_name() {

		if ( 0 < strlen( $this->_name ) ) {
			return $this->_name;
		}

		return null;

	}

	/**
	 * Get object parent ID or name.
	 *
	 * @return null|string Object parent ID, parent name, or null if not set.
	 */
	public function get_parent() {

		if ( 0 < strlen( $this->_parent ) ) {
			return $this->_parent;
		}

		return null;

	}

	/**
	 * Get object parent.
	 *
	 * @return PodsObject|null Object parent, or null if not set.
	 */
	public function get_parent_object() {

		$parent = PodsObject_Collection::get_object( $this->_parent );

		if ( $parent ) {
			/** @var PodsObject $parent */
			$this->_parent = $parent->get_name();
		}

		return $parent;

	}

	/**
	 * Get object group ID or name.
	 *
	 * @return null|string Object group ID, group name, or null if not set.
	 */
	public function get_group() {

		if ( 0 < strlen( $this->_group ) ) {
			return $this->_group;
		}

		return null;

	}

	/**
	 * Get object group.
	 *
	 * @return PodsObject|null Object group, or null if not set.
	 */
	public function get_group_object() {

		$group = PodsObject_Collection::get_object( $this->_group );

		if ( $group ) {
			/** @var PodsObject $group */
			$this->_group = $group->get_name();
		}

		return $group;

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

		if ( $object && $method && method_exists( $object, $method ) ) {
			return call_user_func_array( array( $object, $method ), $arguments );
		}

		return null;

	}

}