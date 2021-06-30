<?php

/**
 * Class Tribe__Data
 *
 * A value object implementing the ArrayAccess interface.
 *
 * Example usage:
 *      $my_data = array( 'lorem' => 'dolor' );
 *
 *      // by default return 'nope' when a value is not set in the data
 *      $data = new Tribe__Data( $my_data, 'nope' );
 *
 *      // set some values in the data
 *      $data['foo'] = 'bar';
 *      $data['bar'] = 23;
 *
 *      // fetch some values
 *      $var_1 = $data['foo']; // "bar"
 *      $var_2 = $data['bar']; // 23
 *      $var_3 = $data['lorem']; // "dolor"
 *      $var_4 = $data['woo']; // "nope"
 *
 *      $data->set_default( 'not found' );
 *
 *      $var_4 = $data['woo']; // "not found"
 *
 */
class Tribe__Data implements ArrayAccess, Iterator {
	/**
	 * @var int
	 */
	protected $index = 0;

	/**
	 * @var array The data managed by this object.
	 */
	protected $data;

	/**
	 * @var mixed The default value that will be returned when trying to get the value
	 *            of a non set key.
	 */
	protected $default;

	/**
	 * Tribe__Data constructor.
	 *
	 * @param array|object $data    An array or object of data.
	 * @param mixed        $default The default value that should be returned if a key is not set
	 */
	public function __construct( $data = [], $default = false ) {
		$this->data = (array) $data;
		$this->default = $default;
	}

	/**
	 * Whether a offset exists
	 *
	 * @link  http://php.net/manual/en/arrayaccess.offsetexists.php
	 * @param mixed $offset <p>
	 *                      An offset to check for.
	 *                      </p>
	 * @return boolean true on success or false on failure.
	 *                      </p>
	 *                      <p>
	 *                      The return value will be casted to boolean if non-boolean was returned.
	 * @since 4.11.0
	 */
	public function offsetExists( $offset ) {
		return isset( $this->data[ $offset ] );
	}

	/**
	 * Offset to retrieve
	 *
	 * @link  http://php.net/manual/en/arrayaccess.offsetget.php
	 * @param mixed $offset <p>
	 *                      The offset to retrieve.
	 *                      </p>
	 * @return mixed Can return all value types.
	 * @since 4.11.0
	 */
	public function offsetGet( $offset ) {
		return isset( $this->data[ $offset ] )
			? $this->data[ $offset ]
			: $this->default;
	}

	/**
	 * Offset to set
	 *
	 * @link  http://php.net/manual/en/arrayaccess.offsetset.php
	 * @param mixed $offset <p>
	 *                      The offset to assign the value to.
	 *                      </p>
	 * @param mixed $value  <p>
	 *                      The value to set.
	 *                      </p>
	 * @return void
	 * @since 4.11.0
	 */
	public function offsetSet( $offset, $value ) {
		$this->data[ $offset ] = $value;
	}

	/**
	 * Offset to unset
	 *
	 * @link  http://php.net/manual/en/arrayaccess.offsetunset.php
	 * @param mixed $offset <p>
	 *                      The offset to unset.
	 *                      </p>
	 * @return void
	 * @since 4.11.0
	 */
	public function offsetUnset( $offset ) {
		unset( $this->data[ $offset ] );
	}

	/**
	 * Gets the data this object manages.
	 *
	 * @return array
	 */
	public function get_data() {
		return $this->data;
	}

	/**
	 * Sets the data this object will manage.
	 *
	 * @param array $data
	 */
	public function set_data( array $data ) {
		$this->data = $data;
	}

	/**
	 * Gets the default value that will be returned when a key is not set.
	 *
	 * @return mixed
	 */
	public function get_default() {
		return $this->default;
	}

	/**
	 * Sets the default value that should be returned when a key is not set.
	 *
	 * @param mixed $default
	 */
	public function set_default( $default ) {
		$this->default = $default;
	}

	/**
	 * Return the current element
	 *
	 * @link  http://php.net/manual/en/iterator.current.php
	 * @return mixed Can return any type.
	 * @since 4.11.0
	 */
	public function current() {
		$keys = array_keys( $this->data );

		return $this->data[ $keys[ $this->index ] ];
	}

	/**
	 * Move forward to next element
	 *
	 * @link  http://php.net/manual/en/iterator.next.php
	 * @return void Any returned value is ignored.
	 * @since 4.11.0
	 */
	public function next() {
		$keys = array_keys( $this->data );

		if ( isset( $keys[ ++ $this->index ] ) ) {
			return $this->data[ $keys[ $this->index ] ];
		}

		return false;
	}

	/**
	 * Return the key of the current element
	 *
	 * @link  http://php.net/manual/en/iterator.key.php
	 * @return mixed scalar on success, or null on failure.
	 * @since 4.11.0
	 */
	public function key() {
		$keys = array_keys( $this->data );

		return $keys[ $this->index ];
	}

	/**
	 * Checks if current position is valid
	 *
	 * @link  http://php.net/manual/en/iterator.valid.php
	 * @return boolean The return value will be casted to boolean and then evaluated.
	 * Returns true on success or false on failure.
	 * @since 4.11.0
	 */
	public function valid() {
		$keys = array_keys( $this->data );

		return isset( $keys[ $this->index ] );
	}

	/**
	 * Rewind the Iterator to the first element
	 *
	 * @link  http://php.net/manual/en/iterator.rewind.php
	 * @return void Any returned value is ignored.
	 * @since 4.11.0
	 */
	public function rewind() {
		$this->index = 0;
	}

	/**
	 * Converts the data object in an array.
	 *
	 * @return array
	 *
	 * @since 4.6
	 */
	public function to_array() {
		return $this->get_data();
	}
}
