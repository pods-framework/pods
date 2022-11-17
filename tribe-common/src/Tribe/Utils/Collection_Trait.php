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
	#[\ReturnTypeWillChange]
	public function offsetExists( $offset ) {
		$items = $this->all();

		return isset( $items[ $offset ] );
	}

	/**
	 * {@inheritDoc}
	 */
	#[\ReturnTypeWillChange]
	public function offsetGet( $offset ) {
		$items = $this->all();

		return isset( $items[ $offset ] )
			? $items[ $offset ]
			: null;
	}

	/**
	 * {@inheritDoc}
	 */
	#[\ReturnTypeWillChange]
	public function offsetSet( $offset, $value ) {
		$this->items = $this->all();

		$this->items[ $offset ] = $value;
	}

	/**
	 * {@inheritDoc}
	 */
	#[\ReturnTypeWillChange]
	public function offsetUnset( $offset ) {
		$this->items = $this->all();

		unset( $this->items[ $offset ] );
	}

	/**
	 * {@inheritDoc}
	 */
	#[\ReturnTypeWillChange]
	public function next() {
		$this->items_index ++;
	}

	/**
	 * {@inheritDoc}
	 */
	#[\ReturnTypeWillChange]
	public function valid() {
		$items = $this->all();

		return ( isset( $items[ $this->items_index ] ) );
	}

	/**
	 * {@inheritDoc}
	 */
	#[\ReturnTypeWillChange]
	public function key() {
		return $this->items_index;
	}

	/**
	 * {@inheritDoc}
	 */
	#[\ReturnTypeWillChange]
	public function current() {
		$items = array_values( $this->all() );

		return isset( $items[ $this->items_index ] ) ? $items[ $this->items_index ] : null;
	}

	/**
	 * {@inheritDoc}
	 */
	#[\ReturnTypeWillChange]
	public function rewind() {
		$this->items_index = 0;
	}

	/**
	 * {@inheritDoc}
	 */
	#[\ReturnTypeWillChange]
	public function count() {
		return count( $this->all() );
	}

	/**
	 * {@inheritDoc}
	 */
	public function serialize() {
		$to_serialize = $this->all();

		if ( method_exists( $this, 'before_serialize' ) ) {
			$to_serialize = $this->before_serialize( $this->all() );
		}

		return serialize( $to_serialize );
	}

	/**
	 * {@inheritDoc}
	 */
	public function unserialize( $serialized ) {
		$to_unserialize = $serialized;

		if ( method_exists( $this, 'custom_unserialize' ) ) {
			$this->items = $this->custom_unserialize( $to_unserialize );
			return;
		}

		$this->items = unserialize( $to_unserialize );
	}

	/**
	 * {@inheritDoc}
	 */
	public function seek( $position ) {
		$this->items_index = $position;
	}

	/**
	 * Applies a filter callback to each element of this collection changing the collection elements to only those
	 * passing the filter.
	 *
	 * @since 4.10.2
	 *
	 * @param callable $filter_callback The filter callback that will be applied to each element of the collection; the
	 *                                  callback will receive the element as parameter.
	 *
	 * @return Collection_Trait A new collection instance, that contains only the elements that passed the filter.
	 */
	public function filter( $filter_callback ) {
		if ( $this->count() === 0 ) {
			// If there is nothing to filter to begin with, just return this.
			return $this;
		}

		$filtered        = new static();
		$filtered->items = array_filter( $this->all(), $filter_callback );

		return $filtered;
	}
}
