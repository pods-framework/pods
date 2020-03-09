<?php

/**
 * Interface Tribe__Repository__Setter_Interface
 *
 * @since 4.8.2
 */
interface Tribe__Repository__Setter_Interface {

	/**
	 * Sets a key on the posts to update using a value or a callback.
	 *
	 * The callback method will be passed the post ID, the `$key` and
	 * the Update repository instance.
	 * The update will check, in order, if the key is a post table field,
	 * a taxonomy and will, finally, set on a custom field.
	 * Updates to the same key will not stack.
	 *
	 * @since 4.7.19
	 *
	 * @since 4.7.19
	 *
	 * @param string         $key
	 * @param mixed|callable $value
	 *
	 * @return Tribe__Repository__Update_Interface
	 * @throws Tribe__Repository__Usage_Error If $key is not a string
	 */
	public function set( $key, $value );

	/**
	 * Sets updates in bulk using a map.
	 *
	 * Updates to the same key will not stack.
	 *
	 * @since 4.7.19
	 *
	 * @param array $update_map A map relating update keys to values.
	 *
	 * @return Tribe__Repository__Update_Interface
	 * @throws Tribe__Repository__Usage_Error If not all keys are strings.
	 *
	 * @see   the `set` method
	 */
	public function set_args( array $update_map );
}