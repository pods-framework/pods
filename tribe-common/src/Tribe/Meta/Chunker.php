<?php

/**
 * Class Tribe__Meta__Chunker
 *
 * Chunks large meta to avoid killing the database in queries.
 *
 * Databases can have a `max_allowed_packet` value set as low as 1M; we often need to store large blobs of data way
 * over that and doing so would kill the database ("MySQL server has gone away"...); registering a meta to be chunked
 * if needed avoids that.
 *
 * Example usage:
 *
 *      $chunker = tribe( 'chunker' );
 *      $chunker->register_for_chunking( $post_id, 'my_meta_key' );
 *
 *      // ... some code later...
 *
 *      // data will be transparently chunked if needed...
 *      update_meta( $post_id, 'my_meta_key', $some_looooooooooong_string );
 *
 *      // ...and glued back together when reading
 *      get_post_meta( $post_id, 'my_meta_key', true );
 *
 * By default the Chunker supports the `post` post type only, filter the `tribe_meta_chunker_post_types` to add yours:
 *
 *      add_filter( 'tribe_meta_chunker_post_types`, 'my_chunkable_post_types' );
 *      function my_chunkable_post_types( $post_types ) {
 *          $post_types[] = 'book';
 *
 *          return $post_types;
 *      }
 *
 * or filter the `tribe_meta_chunker_post_types` filter.
 */
class Tribe__Meta__Chunker {
	/**
	 * @var string The key used to cache the class results in the WordPress object cache.
	 */
	protected $cache_group = 'post_meta';

	/**
	 * @var string
	 */
	protected $chunked_keys_option_name = '_tribe_chunker_chunked_keys';

	/**
	 * @var array The cache that will store chunks to avoid middleware operations from fetching the database.
	 */
	protected $chunks_cache = null;

	/**
	 * @var array The cache that will store the IDs of the posts that have at least one meta key registered
	 *            for chunking.
	 */
	protected $post_ids_cache = null;

	/**
	 * @var string The separator that's used to mark the start of each chunk.
	 */
	protected $chunk_separator = '{{{TCSEP}}}';

	/**
	 * @var array The post types supported by the Chunker.
	 */
	protected $post_types = [];

	/**
	 * @var int The filter priority at which Chunker will operate on meta CRUD operations.
	 */
	protected $filter_priority = - 1;

	/**
	 * @var string The meta key prefix applied ot any Chunker related post meta.
	 */
	protected $meta_key_prefix = '_tribe_chunker_';

	/**
	 * @var int The largest size allowed by the Chunker.
	 */
	protected $max_chunk_size;

	/**
	 * Hooks the chunker on metadata operations for each supported post types.
	 *
	 * When changing post types unhook and rehook it like:
	 *
	 *      $chunker = tribe( 'chunker' );
	 *      $chunker->set_post_types( array_merge( $my_post_types, $chunker->get_post_types() );
	 *      $chunker->unhook();
	 *      $chunker->hook();
	 */
	public function hook() {
		if ( empty( $this->post_types ) ) {
			return;
		}

		add_filter( 'update_post_metadata', [ $this, 'filter_update_metadata' ], $this->filter_priority, 4 );
		add_filter( 'delete_post_metadata', [ $this, 'filter_delete_metadata' ], $this->filter_priority, 3 );
		add_filter( 'add_post_metadata', [ $this, 'filter_add_metadata' ], $this->filter_priority, 4 );
		add_filter( 'get_post_metadata', [ $this, 'filter_get_metadata' ], $this->filter_priority, 4 );
		add_action( 'deleted_post', [ $this, 'remove_post_entry' ] );
	}

	/**
	 * Primes the chunked cache.
	 *
	 * This will just fetch the keys for the supported post types, not the values.
	 *
	 * @param bool $force Whether the cache should be reprimed even if already primed.
	 */
	public function prime_chunks_cache( $force = false ) {
		if ( false === $force && null !== $this->chunks_cache ) {
			return;
		}

		$this->chunks_cache   = [];
		$this->post_ids_cache = [];

		$chunked_keys = get_option( $this->chunked_keys_option_name );

		if ( empty( $chunked_keys ) ) {
			return;
		}

		foreach ( $chunked_keys as $post_id => $keys ) {
			if ( ! is_array( $keys ) || empty( $keys ) ) {
				continue;
			}
			$this->post_ids_cache[] = $post_id;
			foreach ( $keys as $key ) {
				$this->chunks_cache[ $this->get_key( $post_id, $key ) ] = null;
			}
		}
	}

	/**
	 * Gets the key used to identify a post ID and meta key in the chunks cache.
	 *
	 * @param int    $post_id
	 * @param string $meta_key
	 * @return string
	 */
	public function get_key( $post_id, $meta_key ) {
		return "{$post_id}::{$meta_key}";
	}

	/**
	 * Register a post ID and meta key to be chunked if needed.
	 *
	 * @param int    $post_id
	 * @param string $meta_key
	 *
	 * @return bool `false` if the post type is not supported, `true` otherwise
	 */
	public function register_chunking_for( $post_id, $meta_key ) {
		if ( ! $this->is_supported_post_type( $post_id ) ) {
			return false;
		}

		$this->tag_as_chunkable( $post_id, $meta_key );

		return true;
	}

	/**
	 * Whether a post type is supported or not.
	 *
	 * @param int $object_id
	 *
	 * @return bool
	 */
	protected function is_supported_post_type( $object_id ) {
		$post = get_post( $object_id );
		if ( empty( $post ) || ! in_array( $post->post_type, $this->post_types ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Tags a post ID and meta key couple as "chunkable" if needed.
	 *
	 * @param $post_id
	 * @param $meta_key
	 */
	protected function tag_as_chunkable( $post_id, $meta_key ) {
		$key = $this->get_key( $post_id, $meta_key );

		$this->prime_chunks_cache();

		if ( ! array_key_exists( $key, $this->chunks_cache ) ) {
			$this->chunks_cache[ $key ] = null;
		}

		$this->post_ids_cache[] = $post_id;

		$option = (array) get_option( $this->chunked_keys_option_name );

		if ( ! isset( $option[ $post_id ] ) ) {
			$option[ $post_id ] = [ $meta_key ];
		} else {
			$option[ $post_id ][] = $meta_key;
		}

		update_option( $this->chunked_keys_option_name, array_filter( $option ), true );
	}

	/**
	 * Returns the meta key used to indicate if a meta key for a post is marked as chunkable.
	 *
	 * @param string $meta_key
	 *
	 * @return string
	 */
	public function get_chunkable_meta_key( $meta_key ) {
		return $this->meta_key_prefix . $meta_key;
	}

	/**
	 * Filters the add operations.
	 *
	 * Due to how the system works no more than one chunked entry can be stored.
	 *
	 * @param mixed  $check
	 * @param int    $object_id
	 * @param string $meta_key
	 * @param string $meta_value
	 *
	 * @see add_metadata()
	 *
	 * @return bool
	 */
	public function filter_add_metadata( $check, $object_id, $meta_key, $meta_value ) {
		return $this->filter_update_metadata( $check, $object_id, $meta_key, $meta_value );
	}

	/**
	 * Filters the updated operations.
	 *
	 * @param mixed  $check
	 * @param int    $object_id
	 * @param string $meta_key
	 * @param string $meta_value
	 *
	 * @see update_metadata()
	 *
	 * @return bool
	 */
	public function filter_update_metadata( $check, $object_id, $meta_key, $meta_value ) {
		if ( ! $this->applies( $object_id, $meta_key ) ) {
			return $check;
		}

		/**
		 * Filters the chunked meta update operation.
		 *
		 * Returning a non null value here will make the function return that value immediately.
		 *
		 * @param mixed $updated
		 * @param int $object_id The post ID
		 * @param string $meta_key
		 * @param mixed $meta_value
		 *
		 * @since 4.5.6
		 */
		$updated = apply_filters( 'tribe_meta_chunker_update_meta', null, $object_id, $meta_key, $meta_value );
		if ( null !== $updated ) {
			return $updated;
		}

		$this->delete_chunks( $object_id, $meta_key );
		$this->remove_checksum_for( $object_id, $meta_key );
		wp_cache_delete( $object_id, $this->cache_group );

		if ( $this->should_be_chunked( $object_id, $meta_key, $meta_value ) ) {
			$this->insert_chunks( $object_id, $meta_key );

			return true;
		} else {
			$this->cache_delete( $object_id, $meta_key );
			$this->insert_meta( $object_id, $meta_key, $meta_value );

			return true;
		}

		return $check;
	}

	/**
	 * Whether the chunker should operate on this post ID and meta key couple or not.
	 *
	 * @param int    $object_id
	 * @param string $meta_key
	 *
	 * @return bool
	 */
	protected function applies( $object_id, $meta_key ) {
		$applies = ! $this->is_chunker_logic_meta_key( $meta_key )
		           && $this->is_supported_post_type( $object_id )
		           && $this->is_chunkable( $object_id, $meta_key );

		/**
		 * Filters whether the meta chunker will apply to a post ID and meta key or not.
		 *
		 * The `$meta_key` parameter might be empty.
		 *
		 * @param bool $applies
		 * @param int $object_id
		 * @param string $meta_key
		 *
		 * @since 4.5.6
		 */
		return apply_filters( 'tribe_meta_chunker_applies', $applies, $object_id, $meta_key );
	}

	/**
	 * Whether the meta key is one used by the chunker to keep track of its operations or not.
	 *
	 * @param string $meta_key
	 *
	 * @return bool
	 */
	protected function is_chunker_logic_meta_key( $meta_key ) {
		if ( ! is_string( $meta_key ) ) {
			return false;
		}

		return 0 === strpos( $meta_key, $this->meta_key_prefix );
	}

	/**
	 * Whether a post ID and meta key couple is registered as chunkable or not.
	 *
	 * If no meta key is passed then the function will check if there is at least
	 * one meta key registered for chunking for the specified post ID.
	 *
	 * @param int    $post_id
	 * @param string $meta_key
	 *
	 * @return bool
	 */
	public function is_chunkable( $post_id, $meta_key = null ) {
		$this->prime_chunks_cache();

		return ! empty( $meta_key )
			? array_key_exists( $this->get_key( $post_id, $meta_key ), $this->chunks_cache )
			: in_array( $post_id, $this->post_ids_cache );
	}

	/**
	 * Deletes all the chunks for a post ID and meta key couple.
	 *
	 * @param int    $object_id
	 * @param string $meta_key
	 */
	protected function delete_chunks( $object_id, $meta_key ) {
		/** @var wpdb $wpdb */
		global $wpdb;
		$chunk_meta_key = $this->get_chunk_meta_key( $meta_key );
		$delete = "DELETE FROM {$wpdb->postmeta} WHERE (meta_key = %s OR meta_key = %s) AND post_id = %d";
		$wpdb->query( $wpdb->prepare( $delete, $chunk_meta_key, $meta_key, $object_id ) );
	}

	/**
	 * Returns the meta key used to indicate a chunk for a meta key.
	 *
	 * @param string $meta_key
	 *
	 * @return string
	 */
	public function get_chunk_meta_key( $meta_key ) {
		return $this->get_chunkable_meta_key( $meta_key ) . '_chunk';
	}

	/**
	 * Removes the checksum used to verify the integrity of the chunked values.
	 *
	 * @param int    $object_id
	 * @param string $meta_key
	 */
	protected function remove_checksum_for( $object_id, $meta_key ) {
		/** @var wpdb $wpdb */
		global $wpdb;
		$data = [
			'post_id'  => $object_id,
			'meta_key' => $this->get_checksum_key( $meta_key ),
		];
		$wpdb->delete( $wpdb->postmeta, $data );
	}

	/**
	 * Returns the meta_key used to store the chunked meta checksum for a specified meta key.
	 *
	 * @param string $meta_key
	 *
	 * @return string
	 */
	public function get_checksum_key( $meta_key ) {
		return $this->meta_key_prefix . $meta_key . '_checksum';
	}

	/**
	 * Whether a value should be chunked or not.
	 *
	 * @param int    $post_id
	 * @param string $meta_key
	 * @param mixed  $meta_value
	 *
	 * @return bool
	 */
	public function should_be_chunked( $post_id, $meta_key, $meta_value ) {
		$should_be_chunked = false;

		$max_allowed_packet = $this->get_max_chunk_size();
		$serialized = maybe_serialize( $meta_value );
		$byte_size = $this->get_byte_size( $serialized );

		$this->prime_chunks_cache();

		// we use .8 and not 1 to allow for MySQL instructions to use 20% of the string size
		if ( $byte_size > .8 * $max_allowed_packet ) {
			$chunk_size = ceil( $max_allowed_packet * 0.75 );
			$key = $this->get_key( $post_id, $meta_key );
			$this->chunks_cache[ $key ] = $this->prefix_chunks( $this->chunk( $serialized, $chunk_size ) );
			$should_be_chunked = true;
		}

		return $should_be_chunked;
	}

	/**
	 * Returns the max chunk size in bytes.
	 *
	 * @return array|int|null|object
	 */
	public function get_max_chunk_size() {
		if ( ! empty( $this->max_chunk_size ) ) {
			return $this->max_chunk_size;
		}

		$max_size = tribe( 'db' )->get_max_allowed_packet_size();

		/**
		 * Filters the max size of the of the chunks in bytes.
		 *
		 * @param int $max_size By default the `max_allowed_packet` from the database.
		 */
		$this->max_chunk_size = apply_filters( 'tribe_meta_chunker_max_size', $max_size );

		return $max_size;
	}

	/**
	 * Sets the max chunk size.
	 *
	 * @param int $max_chunk_size The max chunk size in bytes.
	 */
	public function set_max_chunk_size( $max_chunk_size ) {
		$this->max_chunk_size = $max_chunk_size;
	}

	/**
	 * Gets the size in bytes of something.
	 *
	 * @param mixed $data
	 *
	 * @return int
	 */
	public function get_byte_size( $data ) {
		return strlen( utf8_decode( maybe_serialize( $data ) ) );
	}

	/**
	 * Prefixes each chunk with a sequence number.
	 *
	 * @param array $chunks
	 *
	 * @return array An array of chunks each prefixed with sequence number.
	 */
	protected function prefix_chunks( array $chunks ) {
		$count = count( $chunks );
		$prefixed = [];
		for ( $i = 0; $i < $count; $i ++ ) {
			$prefixed[] = "{$i}{$this->chunk_separator}{$chunks[$i]}";
		}

		return $prefixed;
	}

	/**
	 * Chunks a string.
	 *
	 * The chunks are not prefixed!
	 *
	 * @param string $serialized
	 * @param int    $chunk_size
	 *
	 * @return array An array of unprefixed chunks.
	 */
	protected function chunk( $serialized, $chunk_size ) {
		$sep = $this->chunk_separator;
		$chunks = array_filter( explode( $sep, chunk_split( $serialized, $chunk_size, $sep ) ) );

		return $chunks;
	}

	/**
	 * Inserts the chunks for a post ID and meta key couple in the database.
	 *
	 * The chunks are read from the array cache.
	 *
	 * @param int    $object_id
	 * @param string $meta_key
	 */
	protected function insert_chunks( $object_id, $meta_key ) {
		/** @var wpdb $wpdb */
		global $wpdb;

		$this->prime_chunks_cache();

		$key = $this->get_key( $object_id, $meta_key );
		$chunks = $this->chunks_cache[ $key ];
		$chunk_meta_key = $this->get_chunk_meta_key( $meta_key );
		$this->insert_meta( $object_id, $meta_key, $chunks[0] );
		foreach ( $chunks as $chunk ) {
			$wpdb->insert( $wpdb->postmeta, [
				'post_id'    => $object_id,
				'meta_key'   => $chunk_meta_key,
				'meta_value' => $chunk,
			] );
		}

		$glued = $this->glue_chunks( $this->get_chunks_for( $object_id, $meta_key ) );
		$checksum_key = $this->get_checksum_key( $meta_key );
		$wpdb->delete( $wpdb->postmeta, [ 'post_id' => $object_id, 'meta_key' => $checksum_key ] );
		$wpdb->insert( $wpdb->postmeta, [
			'post_id'    => $object_id,
			'meta_key'   => $checksum_key,
			'meta_value' => md5( $glued ),
		] );
	}

	/**
	 * Inserts a meta value in the database.
	 *
	 * Convenience method to avoid infinite loop in hooks.
	 *
	 * @param int    $object_id
	 * @param string $meta_key
	 * @param mixed  $meta_value
	 */
	protected function insert_meta( $object_id, $meta_key, $meta_value ) {
		/** @var wpdb $wpdb */
		global $wpdb;
		$data = [
			'post_id'    => $object_id,
			'meta_key'   => $meta_key,
			'meta_value' => maybe_serialize( $meta_value ),
		];
		$wpdb->insert( $wpdb->postmeta, $data );
	}

	/**
	 * Glues the provided chunks.
	 *
	 * This method is sequence aware and should be used with what the `get_chunks_for` method returns.
	 *
	 * @param array $chunks
	 *
	 * @return string
	 *
	 * @see Tribe__Meta__Chunker::get_chunks_for()
	 */
	public function glue_chunks( array $chunks ) {
		$ordered_chunks = [];
		foreach ( $chunks as $chunk ) {
			preg_match( '/(\\d+)' . preg_quote( $this->chunk_separator ) . '(.*)/', $chunk, $matches );
			$ordered_chunks[ $matches[1] ] = $matches[2];
		}
		ksort( $ordered_chunks );

		return implode( '', array_values( $ordered_chunks ) );
	}

	/**
	 * Returns the chunks stored in the database for a post ID and meta key couple.
	 *
	 * The chunks are returned as they are with prefix.
	 *
	 * @param int    $object_id
	 * @param string $meta_key
	 *
	 * @return array|mixed
	 */
	public function get_chunks_for( $object_id, $meta_key ) {
		$key = $this->get_key( $object_id, $meta_key );

		$this->prime_chunks_cache();

		if ( ! empty( $this->chunks_cache[ $key ] ) ) {
			return $this->chunks_cache[ $key ];
		}

		/** @var wpdb $wpdb */
		global $wpdb;

		$chunk_meta_key = $this->get_chunk_meta_key( $meta_key );

		$meta_ids = $wpdb->get_col( $wpdb->prepare( "SELECT meta_id FROM {$wpdb->postmeta}
			WHERE post_id = %d
			AND meta_key = %s",
			$object_id, $chunk_meta_key
		) );

		$meta_values = [];
		foreach ( $meta_ids as $meta_id ) {
			$query = $wpdb->prepare( "SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_id = %d", $meta_id );
			$meta_values[] = $wpdb->get_var( $query );
		}

		if ( ! empty( $meta_values ) ) {
			$this->chunks_cache[ $this->get_key( $object_id, $meta_key ) ] = $meta_values;
		} else {
			$this->chunks_cache[ $this->get_key( $object_id, $meta_key ) ] = null;
		}

		return $meta_values;
	}

	/**
	 * Resets a post ID and meta key couple cache.
	 *
	 * @param int    $object_id
	 * @param string $meta_key
	 */
	protected function cache_delete( $object_id, $meta_key ) {
		$key = $this->get_key( $object_id, $meta_key );

		$this->prime_chunks_cache();

		if ( isset( $this->chunks_cache[ $key ] ) ) {
			$this->chunks_cache[ $key ] = null;
		}
	}

	/**
	 * Filters the delete operations.
	 *
	 * @param mixed  $check
	 * @param int    $object_id
	 * @param string $meta_key
	 *
	 * @return bool
	 *
	 * @see delete_metadata()
	 */
	public function filter_delete_metadata( $check, $object_id, $meta_key ) {
		if ( ! $this->applies( $object_id, $meta_key ) ) {
			return $check;
		}

		/**
		 * Filters the value returned when deleting a specific meta for a post.
		 *
		 * Returning a non null value here will make the function return that value immediately.
		 *
		 * @param mixed $deleted
		 * @param int $object_id The post ID
		 * @param string $meta_key The requested meta key
		 *
		 * @since 4.5.6
		 */
		$deleted = apply_filters( 'tribe_meta_chunker_delete_meta', null, $object_id, $meta_key );
		if ( null !== $deleted ) {
			return $deleted;
		}

		$has_chunked_meta = $this->is_chunked( $object_id, $meta_key );
		if ( ! $has_chunked_meta ) {
			return $check;
		}
		$this->cache_delete( $object_id, $meta_key );
		$this->delete_chunks( $object_id, $meta_key );
		wp_cache_delete( $object_id, $this->cache_group );

		return true;
	}

	/**
	 * Whether a post ID and meta key couple has chunked meta or not.
	 *
	 * @param int    $object_id
	 * @param string $meta_key
	 * @param bool   $check_db Do verify the chunking state on the database.
	 *
	 * @return mixed
	 */
	public function is_chunked( $object_id, $meta_key, $check_db = false ) {
		$key = $this->get_key( $object_id, $meta_key );

		$this->prime_chunks_cache();

		$chunked_in_cache = array_key_exists( $key, $this->chunks_cache ) && is_array( $this->chunks_cache[ $key ] );

		return $chunked_in_cache;
	}

	/**
	 * Returns the checksum for the stored meta key to spot meta value corruption malforming.
	 *
	 * @param int    $object_id
	 * @param string $meta_key
	 *
	 * @return string
	 */
	public function get_checksum_for( $object_id, $meta_key ) {
		/** @var wpdb $wpdb */
		global $wpdb;

		$query = "SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key = %s";
		$checksum = $wpdb->get_var( $wpdb->prepare( $query, $object_id, $this->get_checksum_key( $meta_key ) ) );

		return ! empty( $checksum ) ? $checksum : '';
	}

	/**
	 * Handles the object destruction cycle to leave no traces behind.
	 */
	public function __destruct() {
		$this->unhook();
	}

	/**
	 * Unhooks the Chunker from the metadata operations.
	 */
	public function unhook() {
		remove_filter( 'update_post_metadata', [ $this, 'filter_update_metadata' ], $this->filter_priority );
		remove_filter( 'delete_post_metadata', [ $this, 'filter_delete_metadata' ], $this->filter_priority );
		remove_filter( 'add_post_metadata', [ $this, 'filter_add_metadata' ], $this->filter_priority );
		remove_filter( 'get_post_metadata', [ $this, 'filter_get_metadata' ], $this->filter_priority );
		remove_action( 'deleted_post', [ $this, 'remove_post_entry' ] );
	}

	/**
	 * Filters the get operations.
	 *
	 * @param mixed  $check
	 * @param int    $object_id
	 * @param string $meta_key
	 * @param bool   $single
	 *
	 * @return array|mixed
	 *
	 * @see get_metadata()
	 */
	public function filter_get_metadata( $check, $object_id, $meta_key, $single ) {
		if ( ! $this->applies( $object_id, $meta_key ) ) {
			return $check;
		}

		$all_meta = wp_cache_get( $object_id, $this->cache_group );

		if ( ! $all_meta ) {
			$all_meta = $this->get_all_meta_for( $object_id );
			wp_cache_set( $object_id, $all_meta, $this->cache_group );
		}

		// getting all the meta
		if ( empty( $meta_key ) ) {
			return $all_meta;
		}

		// why not take $single into account? See condition check on the filter to understand.
		$meta_value = isset( $all_meta[ $meta_key ] )
			? array_map( 'maybe_unserialize', $all_meta[ $meta_key ] )
			: '';

		/**
		 * Filters the value returned when getting a specific meta for a post.
		 *
		 * Returning a non null value here will make the function return that value immediately.
		 *
		 * @param mixed $meta_value
		 * @param int $object_id The post ID
		 * @param string $meta_key The requested meta key
		 *
		 * @since 4.5.6
		 */
		$meta_value = apply_filters( 'tribe_meta_chunker_get_meta', $meta_value, $object_id, $meta_key );

		if ( $single ) {
			return (array) $meta_value;
		} else {
			return ! empty( $meta_value ) ? $meta_value : '';
		}
	}

	/**
	 * Returns all the meta for a post ID.
	 *
	 * The meta includes the chunked one but not the chunker logic meta keys.
	 * The return format is the same used by the `get_post_meta( $post_id )` function.
	 *
	 * @param int $object_id
	 *
	 * @return array An array containing all meta including the chunked one.
	 *
	 * @see get_post_meta() with empty `$meta_key` argument.
	 */
	public function get_all_meta_for( $object_id ) {
		$all_meta = $this->get_all_meta( $object_id );

		if ( empty( $all_meta ) ) {
			return [];
		}

		$grouped = [];
		foreach ( $all_meta as $entry ) {
			if ( ! isset( $grouped[ $entry['meta_key'] ] ) ) {
				$grouped[ $entry['meta_key'] ] = [ $entry['meta_value'] ];
			} else {
				$grouped[ $entry['meta_key'] ][] = $entry['meta_value'];
			}
		}

		$chunker_meta_keys = array_filter( array_keys( $grouped ), [ $this, 'is_chunker_logic_meta_key' ] );

		if ( empty( $chunker_meta_keys ) ) {
			return $grouped;
		}

		$checksum_keys = array_filter( $chunker_meta_keys, [ $this, 'is_chunker_checksum_key' ] );

		if ( empty( $checksum_keys ) ) {
			return $grouped;
		}

		$chunker_meta = array_intersect_key( $grouped, array_combine( $chunker_meta_keys, $chunker_meta_keys ) );
		$normal_meta = array_diff_key( $grouped, array_combine( $chunker_meta_keys, $chunker_meta_keys ) );
		foreach ( $checksum_keys as $checksum_key ) {
			$normal_meta_key = str_replace( [ $this->meta_key_prefix, '_checksum' ], '', $checksum_key );
			$chunk_meta_key = $this->get_chunk_meta_key( $normal_meta_key );

			if ( empty( $chunker_meta[ $chunk_meta_key ] ) ) {
				continue;
			}

			$normal_meta[ $normal_meta_key ] = [ $this->glue_chunks( $chunker_meta[ $chunk_meta_key ] ) ];
		}

		return $normal_meta;
	}

	/**
	 * Fetches all the meta for a post.
	 *
	 * @param int $object_id
	 *
	 * @return array|null|object
	 */
	protected function get_all_meta( $object_id ) {
		/**
		 * Filters the value returned when getting all the meta for a post.
		 *
		 * Returning a non null value here will make the function return that value immediately.
		 *
		 * @param mixed $all_meta
		 * @param int $object_id The post ID
		 *
		 * @since 4.5.6
		 */
		$all_meta = apply_filters( 'tribe_meta_chunker_get_all_meta', null, $object_id );
		if ( null !== $all_meta ) {
			return $all_meta;
		}

		/** @var wpdb $wpdb */
		global $wpdb;
		$query = $wpdb->prepare( "SELECT meta_key, meta_value FROM {$wpdb->postmeta} WHERE post_id = %d", $object_id );
		$results = $wpdb->get_results( $query, ARRAY_A );

		return ! empty( $results ) && is_array( $results ) ? $results : [];
	}

	/**
	 * Returns the post types supported by the chunker.
	 *
	 * @return array
	 */
	public function get_post_types() {
		return $this->post_types;
	}

	/**
	 * Sets the post types the Chunker should support.
	 *
	 * @param array $post_types
	 */
	public function set_post_types( array $post_types = null ) {
		if ( null === $post_types ) {
			/**
			 * Filters the chunk-able post types.
			 *
			 * @param array $post_types
			 */
			$this->post_types = apply_filters( 'tribe_meta_chunker_post_types', $this->post_types );

			return;
		}

		$this->post_types = $post_types;
	}

	/**
	 * Returns the name of the option that stores the keys registered for chunking for each post.
	 *
	 * @return string
	 */
	public function get_key_option_name() {
		return $this->chunked_keys_option_name;
	}

	/**
	 * Returns the cache group used by the meta chunker.
	 *
	 * @return string
	 */
	public function get_cache_group() {
		return $this->cache_group;
	}

	/**
	 * Asserts that a meta key is not a chunk meta key.
	 *
	 * @param string $meta_key
	 *
	 * @return bool
	 */
	protected function is_chunker_checksum_key( $meta_key ) {
		return preg_match( "/^{$this->meta_key_prefix}.*_checksum$/", $meta_key );
	}

	/**
	 * Removes the entries associated with a deleted post from the cache and the database option.
	 *
	 * @param int $post_id A post ID
	 */
	public function remove_post_entry( $post_id ) {
		$this->prime_chunks_cache();

		foreach ( $this->chunks_cache as $key => $value ) {
			if ( 0 === strpos( $key, (string) $post_id ) ) {
				unset( $this->chunks_cache[ $key ] );
			}
		}

		if ( ! empty( $this->chunks_cache ) ) {
			update_option( $this->chunked_keys_option_name, $this->chunks_cache );
		} else {
			delete_option( $this->chunked_keys_option_name );
		}
	}
}
