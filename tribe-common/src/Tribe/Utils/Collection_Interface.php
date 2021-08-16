<?php
/**
 * A class providing collection methods.
 *
 * For convenience classes implementing this interface should `use Collection_Trait` and implement just the `all`
 * method.
 *
 * @since   4.9.14
 * @package Tribe\Utils
 */

namespace Tribe\Utils;

/**
 * Interface Collection_Interface
 * @since   4.9.14
 * @package Tribe\Utils
 */
interface Collection_Interface extends \ArrayAccess, \SeekableIterator, \Countable, \Serializable, \JsonSerializable {
	/**
	 * Returns all the items in the collection.
	 *
	 * @since 4.9.14
	 *
	 * @return array All the items in the collection.
	 */
	public function all();

	/**
	 * Returns the first item in the collection.
	 *
	 * @since 4.9.14
	 *
	 * @return mixed The first item in the collection.
	 */
	public function first();

	/**
	 * Returns the last item in the collection.
	 *
	 * @since 4.9.14
	 *
	 * @return mixed The last item in the collection.
	 */
	public function last();

	/**
	 * Returns the nth item in the collection.
	 *
	 * @since 4.9.14
	 *
	 * @param int $n The 1-based index of the item to return. It's not 0-based, `1` will return the first item.
	 *
	 * @return mixed|null The nth item in the collection or `null` if not set.
	 */
	public function nth( $n );
}