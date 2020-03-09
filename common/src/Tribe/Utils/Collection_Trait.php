<?php
/**
 * Implements all the methods required by the `Tribe\Utils\Collection` interface minus the `all` one.
 *
 * The trait will also implement the `ArrayAccess`, `Iterator` and `Countable` interface methods.
 *
 * @since   4.9.14
 * @package Tribe\Utils
 */

namespace Tribe\Utils;

/**
 * Trait Collection_Trait
 * @since   4.9.14
 * @package Tribe\Utils
 */
trait Collection_Trait {
	/**
	 * The current items index.
	 *
	 * @var int
	 */
	protected $items_index = 0;

	/**
	 * Returns the first item in the collection.
	 *
	 * @since 4.9.14
	 *
	 * @return mixed The first item in the collection.
	 */
	public function first() {
		$items = $this->all();

		return reset( $items );
	}

	/**
	 * Returns the last item in the collection.
	 *
	 * @since 4.9.14
	 *
	 * @return mixed The last item in the collection.
	 */
	public function last() {
		$items = $this->all();

		return end( $items );
	}

	/**
	 * Returns the nth item in the collection.
	 *
	 * @since 4.9.14
	 *
	 * @param int $n The 1-based index of the item to return. It's not 0-based, `1` will return the first item.
	 *
	 * @return mixed|null The nth item in the collection or `null` if not set.
	 */
	public function nth( $n ) {
		$items = array_values( $this->all() );

		return isset( $items[ $n - 1 ] ) ? $items[ $n - 1 ] : null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function offsetExists( $offset ) {
		$items = $this->all();

		return isset( $items[ $offset ] );
	}

	/**
	 * {@inheritDoc}
	 */
	public function offsetGet( $offset ) {
		$items = $this->all();

		return isset( $items[ $offset ] )
			? $items[ $offset ]
			: null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function offsetSet( $offset, $value ) {
		$this->items = $this->all();

		$this->items[ $offset ] = $value;
	}

	/**
	 * {@inheritDoc}
	 */
	public function offsetUnset( $offset ) {
		$this->items = $this->all();

		unset( $this->items[ $offset ] );
	}

	/**
	 * {@inheritDoc}
	 */
	public function next() {
		$this->items_index ++;
	}

	/**
	 * {@inheritDoc}
	 */
	public function valid() {
		$items = $this->all();

		return ( isset( $items[ $this->items_index ] ) );
	}

	/**
	 * {@inheritDoc}
	 */
	public function key() {
		return $this->items_index;
	}

	/**
	 * {@inheritDoc}
	 */
	public function current() {
		$items = array_values( $this->all() );

		return isset( $items[ $this->items_index ] ) ? $items[ $this->items_index ] : null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function rewind() {
		$this->items_index = 0;
	}

	/**
	 * {@inheritDoc}
	 */
	public function count() {
		return count( $this->all() );
	}

	/**
	 * {@inheritDoc}
	 */
	public function serialize() {
		return serialize( $this->all() );
	}

	/**
	 * {@inheritDoc}
	 */
	public function unserialize( $serialized ) {
		$this->items = unserialize( $serialized );
	}

	/**
	 * {@inheritDoc}
	 */
	public function seek( $position ) {
		$this->items_index = $position;
	}
}
