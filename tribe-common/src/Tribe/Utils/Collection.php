<?php
/**
 * Models a generic collection of elements as a linked list.
 *
 * @since 4.9.5
 */

/**
 * Class Tribe__Utils__Collection
 *
 * @since 4.9.5
 */
class Tribe__Utils__Collection extends SplDoublyLinkedList {

	/**
	 * The list of items the collection was initialized with.
	 *
	 * @var array
	 */
	protected $items = [];

	/**
	 * The doubly-linked list that will hold the items handled by the collection.
	 *
	 * @var \SplDoublyLinkedList
	 */
	protected $list;

	/**
	 * Tribe__Utils__Collection constructor.
	 *
	 * @param array $items The array of items to initialize the linked list with.
	 */
	public function __construct( array $items ) {
		$this->items = $items;
		foreach ( $items as $item ) {
			$this->push( $item );
		}
	}

	/**
	 * Runs a callback function on all the collection items and returns the results.
	 *
	 * This is just a wrapper around the `array_map` method.
	 *
	 * @since 4.9.5
	 *
	 * @param callable $callback The callback to run on each collection item.
	 *
	 * @return array An array of results returned by running the callback on all
	 *               collection items.
	 */
	public function map( $callback ) {
		return array_map( $callback, $this->items );
	}
}