<?php

/**
 * A Class to handle Transients for posts, useful for caching complex structures
 * It uses the same logic as WordPress Transient, but instead of options it will
 * use the Post Meta as the table
 *
 * @since   4.1
 */
class Tribe__Post_Transient {

	/**
	 * Get (and instantiate, if necessary) the instance of the class
	 *
	 * @since  4.1
	 * @static
	 * @return self
	 *
	 */
	public static function instance() {
		return tribe( 'post-transient' );
	}

	/**
	 * Fetches the Transient Data
	 *
	 * @since  4.1
	 *
	 * @param int    $post_id   The Post ID, can also be a WP_Post
	 * @param string $transient Post Meta to Fetch
	 *
	 */
	public function get( $post_id, $transient ) {
		global $_wp_using_ext_object_cache;

		if ( is_numeric( $post_id ) ) {
			$post_id = (int) $post_id;
		} else {
			$post    = get_post( $post_id );
			$post_id = $post->ID;
		}

		if ( has_filter( 'tribe_pre_post_meta_transient_' . $transient ) ) {
			/**
			 * Attach an action before getting the new Transient
			 *
			 * @since  4.1
			 *
			 * @param int    $post_id   Post ID
			 * @param string $transient The Post Meta Key
			 */
			$pre = apply_filters( 'tribe_pre_post_meta_transient_' . $transient, $post_id, $transient );
			if ( false !== $pre ) {
				return $pre;
			}
		}

		if ( $_wp_using_ext_object_cache ) {
			$value = wp_cache_get( "tribe_{$transient}-{$post_id}", "tribe_post_meta_transient-{$post_id}" );
		} else {
			$meta_timeout = '_transient_timeout_' . $transient;
			$meta         = '_transient_' . $transient;
			$value        = get_post_meta( $post_id, $meta, false );

			// if there aren't any values, communicate that it did not fetch data from post transient
			if ( ! is_array( $value ) || 0 === count( $value ) ) {
				return false;
			}

			// grab the first value, because that's all we care about
			$value = current( $value );

			if ( $value && ! defined( 'WP_INSTALLING' ) ) {
				if ( get_post_meta( $post_id, $meta_timeout, true ) < time() ) {
					$this->delete( $post_id, $transient );

					return false;
				}
			}
		}

		/**
		 * Attach an action after getting the new Transient
		 *
		 * @since  4.1
		 *
		 * @param int    $post_id   Post ID
		 * @param string $transient The Post Meta Key
		 */
		return has_filter( 'tribe_post_meta_transient_' . $transient )
				? apply_filters( 'tribe_post_meta_transient_' . $transient, $value, $post_id )
				: $value;
	}

	/**
	 * Delete a post meta transient.
	 *
	 * @since  4.1
	 *
	 * @param int    $post_id   The Post ID, can also be a WP_Post
	 * @param string $transient Post Meta to Delete
	 * @param string $value     Only delete if the value Matches
	 *
	 */
	public function delete( $post_id, $transient, $value = null ) {
		global $_wp_using_ext_object_cache;

		if ( is_numeric( $post_id ) ) {
			$post_id = (int) $post_id;
		} else {
			$post    = get_post( $post_id );
			$post_id = $post->ID;
		}

		/**
		 * Use this to pre attach an action to deleting a Post Transient
		 *
		 * @since  4.1
		 *
		 * @param int    $post_id   Post ID
		 * @param string $transient The Post Meta Key
		 */
		do_action( 'tribe_delete_post_meta_transient_' . $transient, $post_id, $transient );

		if ( $_wp_using_ext_object_cache ) {
			$result = wp_cache_delete( "tribe_{$transient}-{$post_id}", "tribe_post_meta_transient-{$post_id}" );
		} else {
			$meta_timeout = '_transient_timeout_' . $transient;
			$meta         = '_transient_' . $transient;
			$result       = delete_post_meta( $post_id, $meta, $value );
			if ( $result ) {
				delete_post_meta( $post_id, $meta_timeout, $value );
			}
		}

		if ( $result ) {
			/**
			 * Use this to attach an Action to when the Transient is deleted
			 *
			 * @since  4.1
			 *
			 * @param int    $post_id   Post ID
			 * @param string $transient The Post Meta Key
			 */
			do_action( 'tribe_deleted_post_meta_transient', $transient, $post_id, $transient );
		}

		return $result;
	}

	/**
	 * Sets a new value for the Transient
	 *
	 * @since  4.1
	 *
	 * @param int    $post_id    The Post ID, can also be a WP_Post
	 * @param string $transient  Post Meta to set
	 * @param string $value      Only delete if the value Matches
	 * @param int    $expiration How long this transient will be valid, in seconds
	 *
	 */
	public function set( $post_id, $transient, $value, $expiration = 0 ) {
		global $_wp_using_ext_object_cache;

		if ( is_numeric( $post_id ) ) {
			$post_id = (int) $post_id;
		} else {
			$post    = get_post( $post_id );
			$post_id = $post->ID;
		}

		$this->delete( $post_id, $transient );

		/**
		 * Attach an action before setting the new Transient
		 *
		 * @since  4.1
		 *
		 * @param int    $post_id   Post ID
		 * @param string $transient The Post Meta Key
		 */
		if ( has_filter( 'tribe_pre_set_post_meta_transient_' . $transient ) ) {
			$value = apply_filters( 'tribe_pre_set_post_meta_transient_' . $transient, $value, $post_id, $transient );
		}

		if ( $_wp_using_ext_object_cache ) {
			$result = wp_cache_set( "tribe_{$transient}-{$post_id}", $value, "tribe_post_meta_transient-{$post_id}", $expiration );
		} else {
			$meta_timeout = '_transient_timeout_' . $transient;
			$meta         = '_transient_' . $transient;
			if ( $expiration ) {
				add_post_meta( $post_id, $meta_timeout, time() + $expiration, true );
			}
			$result = add_post_meta( $post_id, $meta, $value, true );
		}

		if ( $result ) {
			/**
			 * Attach an action after setting the new Transient
			 *
			 * @since  4.1
			 *
			 * @param int    $post_id   Post ID
			 * @param string $transient The Post Meta Key
			 */
			do_action( 'tribe_set_post_meta_transient_' . $transient, $post_id, $transient );
		}

		return $result;
	}


}
