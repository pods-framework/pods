<?php
/**
 * Wraps the logic to lazy load a post thumbnail information.
 *
 * Example usage:
 * ```php
 * $post_thumbnail = new Tribe\Utils\Post_Thumbnail( $post_id );
 *
 * // ...some code later...
 *
 * // The post thumbnail data is fetched only now.
 * $large_url = $post_thumbnail->large->url;
 * ```
 *
 * @since   4.9.14
 * @package Tribe\Utils
 */


namespace Tribe\Utils;

use Tribe__Utils__Array as Arr;

/**
 * Class Post_Thumbnail
 *
 * @since   4.9.14
 * @package Tribe\Utils
 */
class Post_Thumbnail implements \ArrayAccess, \Serializable {
	use Lazy_Events;

	/**
	 * An array of the site image sizes, including the `full` one.
	 *
	 * @since 4.9.14
	 *
	 * @var array
	 */
	protected $image_sizes;

	/**
	 * The post ID this images collection is for.
	 *
	 * @since 4.9.14
	 *
	 * @var int
	 */
	protected $post_id;

	/**
	 * The post thumbnail data.
	 *
	 * @since 4.9.14
	 *
	 * @var array
	 */
	protected $data;

	/**
	 * A flag property indicating whether the post thumbnail for the post exists or not.
	 *
	 * @since 4.9.16
	 *
	 * @var bool
	 */
	protected $exists;

	/**
	 * The post ID, if any, of the post thumbnail.
	 *
	 * @since 4.9.16
	 *
	 * @var int
	 */
	protected $thumbnail_id;

	/**
	 * Post_Images constructor.
	 *
	 * @param int $post_id The post ID.
	 */
	public function __construct( $post_id ) {
		$this->post_id = $post_id;
	}

	/**
	 * {@inheritDoc}
	 */
	public function __get( $property ) {
		if ( 'exists' === $property ) {
			return $this->exists();
		}

		return $this->offsetGet( $property );
	}

	/**
	 * {@inheritDoc}
	 */
	public function __set( $property, $value ) {
		if ( 'exists' === $property ) {
			throw new \InvalidArgumentException( 'The `Post_Thumbnail::exists` property cannot be set.' );
		}

		$this->offsetSet( $property, $value );
	}

	/**
	 * {@inheritDoc}
	 */
	public function __isset( $property ) {
		return $this->offsetExists( $property );
	}

	/**
	 * Fetches and returns the image sizes registered on the site, if any.
	 *
	 * @since 4.9.14
	 *
	 * @return array An array of the registered image sizes.
	 */
	public function get_image_sizes() {
		if ( null !== $this->image_sizes ) {
			return $this->image_sizes;
		}

		$image_sizes = array_merge( [ 'full' ], get_intermediate_image_sizes() );

		/**
		 * Filters the image sizes the `Tribe\Utils\Post_Thumbnail` class will manage and fetch data for.
		 *
		 * @since 4.9.14
		 *
		 * @param array $image_sizes All the available image sizes; this includes the default and the intermediate ones.
		 */
		$this->image_sizes = apply_filters( 'tribe_post_thumbnail_image_sizes', $image_sizes );

		return $this->image_sizes;
	}

	/**
	 * Returns the data about the post thumbnail, if any.
	 *
	 * @since 4.9.14
	 *
	 * @return array An array of objects containing the post thumbnail data.
	 */
	public function fetch_data() {
		static $cache_thumbnail = [];

		if ( ! $this->exists() ) {
			return [];
		}

		if ( null !== $this->data ) {
			return $this->data;
		}

		$image_sizes  = $this->get_image_sizes();
		$thumbnail_id = $this->thumbnail_id;

		$cache_key = empty( $thumbnail_id ) ? -1 : $thumbnail_id;

		if ( empty( $cache_thumbnail[ $cache_key ] ) ) {
			$thumbnail_data = array_combine(
				$image_sizes,
				array_map(
					static function( $size ) use ( $thumbnail_id ) {
						static $cache_size_data = [];

						$size_data_cache_key = empty( $thumbnail_id ) ? -1 : $thumbnail_id;
						$size_data_cache_key = "{$size_data_cache_key}:{$size}";

						if ( ! isset( $cache_size_data[ $size_data_cache_key ] ) ) {
							$cache_size_data[ $size_data_cache_key ] = wp_get_attachment_image_src( $thumbnail_id, $size );
						}

						$size_data = $cache_size_data[ $size_data_cache_key ];

						if ( false === $size_data ) {
							return (object) [
								'url'             => '',
								'width'           => '',
								'height'          => '',
								'is_intermediate' => false,
							];
						}

						return (object) [
							'url'             => Arr::get( $size_data, 0, '' ),
							'width'           => Arr::get( $size_data, 1, '' ),
							'height'          => Arr::get( $size_data, 2, '' ),
							'is_intermediate' => (bool) Arr::get( $size_data, 3, false ),
						];
					},
					$image_sizes
				)
			);

			$srcset                   = wp_get_attachment_image_srcset( $thumbnail_id );
			$thumbnail_data['srcset'] = ! empty( $srcset ) ? $srcset : false;

			$title                   = get_the_title( $thumbnail_id );
			$thumbnail_data['title'] = ! empty( $title ) ? $title : false;

			$alt                   = trim( strip_tags( get_post_meta( $thumbnail_id, '_wp_attachment_image_alt', true ) ) );
			$thumbnail_data['alt'] = ! empty( $alt ) ? $alt : false;

			$cache_thumbnail[ $cache_key ] = $thumbnail_data;
		}

		/**
		 * Filters the post thumbnail data and information that will be returned for a specific post.
		 *
		 * Note that the thumbnail data will be cast to an object after this filtering.
		 *
		 * @since 4.9.14
		 *
		 * @param array $thumbnail_data The thumbnail data for the post.
		 * @param int   $post_id        The ID of the post the data is for.
		 */
		$thumbnail_data = apply_filters( 'tribe_post_thumbnail_data', $cache_thumbnail[ $cache_key ], $this->post_id );

		$this->resolved();

		return $thumbnail_data;
	}

	/**
	 * {@inheritDoc}
	 */
	#[\ReturnTypeWillChange]
	public function offsetExists( $offset ) {
		$this->data = $this->fetch_data();

		return isset( $this->data[ $offset ] );
	}

	/**
	 * {@inheritDoc}
	 */
	#[\ReturnTypeWillChange]
	public function offsetGet( $offset ) {
		$this->data = $this->fetch_data();

		return isset( $this->data[ $offset ] )
			? $this->data[ $offset ]
			: null;
	}

	/**
	 * {@inheritDoc}
	 */
	#[\ReturnTypeWillChange]
	public function offsetSet( $offset, $value ) {
		$this->data = $this->fetch_data();

		$this->data[ $offset ] = $value;
	}

	/**
	 * {@inheritDoc}
	 */
	#[\ReturnTypeWillChange]
	public function offsetUnset( $offset ) {
		$this->data = $this->fetch_data();

		unset( $this->data[ $offset ] );
	}

	/**
	 * Returns an array representation of the post thumbnail data.
	 *
	 * @since 4.9.14
	 *
	 *
	 * @return array An array representation of the post thumbnail data.
	 */
	public function to_array() {
		$this->data = $this->fetch_data();

		return json_decode( json_encode( $this->data ), true );
	}

	/**
	 * {@inheritDoc}
	 */
	public function serialize() {
		$data            = $this->fetch_data();
		$data['post_id'] = $this->post_id;

		return wp_json_encode( $data );
	}

	/**
	 * {@inheritDoc}
	 */
	public function unserialize( $serialized ) {
		$data = json_decode( $serialized, true );
		array_walk( $data, static function ( &$data_entry ) {
			if ( is_array( $data_entry ) ) {
				$data_entry = (object) $data_entry;
			}
		} );
		$this->post_id = $data['post_id'];
		unset( $data['post_id'] );
		$this->data = ! empty( $data ) ? $data : null;
	}

	/**
	 * Returns whether a post thumbnail is set for the post or not.
	 *
	 * @since 4.9.16
	 *
	 * @return bool Whether a post thumbnail is set for the post or not.
	 */
	public function exists() {
		if ( null !== $this->exists ) {
			return $this->exists;
		}

		$thumbnail_id = get_post_thumbnail_id( $this->post_id );

		if ( empty( $thumbnail_id ) ) {
			$this->exists = false;
		} else {
			$this->thumbnail_id = $thumbnail_id;
			$this->exists       = true;
		}

		return $this->exists;
	}
}
