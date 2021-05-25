<?php
/**
 * Provides methods useful to deal with meta updates.
 *
 * @since   4.12.6
 *
 * @package Tribe\Traits
 */

namespace Tribe\Traits;

/**
 * Trait With_Meta_Updates_Handling
 *
 * @since   4.12.6
 *
 * @package Tribe\Traits
 */
trait With_Meta_Updates_Handling {
	/**
	 * Returns a closure that should be hooked to the `udapte_post_metadata` filter to "unpack" arrays of meta
	 * for a specific key.
	 *
	 * Providing an array of values in the context of `meta_input` will store them as a single array of values, not
	 * as multiple values. This closure will unpack the meta on update to have multiple values in place of one.
	 * This is the case, as an example, with Event Organizers, where we want a meta entry for each Organizer, not an
	 * array of Organizer IDs in a single meta.
	 *
	 * @since 4.12.6
	 *
	 * @param string   $target_meta_key The meta key that should be "unpacked" for updates.
	 * @param int|null $target_post_id  The specific post ID to target, or null to target the next update.
	 *
	 * @return \Closure The closure that will deal with the unpacked meta update.
	 */
	protected function unpack_meta_on_update( $target_meta_key, $target_post_id = null ) {
		$closure = static function ( $update = null, $post_id = null, $meta_key = null, $meta_value = null ) use (
			$target_post_id,
			$target_meta_key,
			&$closure
		) {
			if ( $target_meta_key !== $meta_key ) {
				return $update;
			}

			if ( null !== $target_post_id && $target_post_id !== $post_id ) {
				return $update;
			}

			remove_filter( 'update_post_metadata', $closure );

			$values = (array) $meta_value;
			delete_post_meta( $post_id, $target_meta_key );
			foreach ( $values as $organizer_id ) {
				add_post_meta( $post_id, $target_meta_key, $organizer_id );
			}

			// As in "we've dealt with it, do not update this meta."
			return true;
		};

		add_filter( 'update_post_metadata', $closure, 10, 4 );

		return $closure;
	}
}
