<?php


class Tribe__Image__Uploader {
	/**
	 * @var bool|array
	 */
	protected static $original_urls_cache = false;
	/**
	 * @var bool|array
	 */
	protected static $attachment_guids_cache = false;
	/**
	 * @var string|int Either an absolute URL to an image file or a media attachment post ID.
	 */
	protected $featured_image;

	/**
	 * Tribe__Events__Importer__Featured_Image_Uploader constructor.
	 *
	 * @var string A single importing file row.
	 */
	public function __construct( $featured_image = null ) {
		$this->featured_image = $featured_image;
	}

	/**
	 * Resets the static "cache" of the class.
	 */
	public static function reset_cache() {
		self::$attachment_guids_cache = false;
		self::$original_urls_cache    = false;
	}

	/**
	 * Uploads a file and creates the media attachment or simply returns the attachment ID if existing.
	 *
	 * @return int|bool The attachment post ID if the uploading and attachment is successful or the ID refers to an
	 *                  attachment;
	 *                  `false` otherwise.
	 */
	public function upload_and_get_attachment_id() {
		if ( empty( $this->featured_image ) ) {
			return false;
		}

		$existing = false;

		if ( is_string( $this->featured_image ) && ! is_numeric( $this->featured_image ) ) {
			// Assume image exists in the local file system.
			$id = $this->get_attachment_ID_from_url( $this->featured_image );
			if ( ! $id ) {
				$id = $this->upload_file( $this->featured_image );
				$id = $this->maybe_retry_upload( $id );
			}
			$existing = (bool) $id;
		} elseif ( $post = get_post( $this->featured_image ) ) {
			$id = $post && 'attachment' === $post->post_type ? $this->featured_image : false;
		} else {
			$id = false;
		}

		do_action(
			'tribe_log',
			'debug',
			__CLASS__,
			[
				'featured_image' => $this->featured_image,
				'exists'         => $existing,
				'id'             => $id,
			]
		);

		return $id;
	}

	/**
	 * Retry to upload an image after it failed as was provided, try to decode the URL as in some cases the
	 * original URL might be encoded HTML components such as: "&" and some CDNs does not handle well different URLs
	 * as they were provided so we try to recreate the original URL where it might be required.
	 *
	 * @since 4.11.5
	 *
	 * @param int|bool $id The id of the attachment if was uploaded correctly, false otherwise.
	 *
	 * @return int The ID of the attachment after the upload retry.
	 */
	protected function maybe_retry_upload( $id ) {
		if ( $id ) {
			do_action( 'tribe_log', 'debug', __CLASS__, [ 'message' => "ID: {$id} is already a valid one." ] );

			return $id;
		}

		$decoded = esc_url_raw( html_entity_decode( $this->featured_image ) );

		do_action( 'tribe_log', 'debug', __CLASS__, [
			'message' => 'Retry upload decoding the URL of the image',
			'url'     => $this->featured_image,
			'decoded' => $decoded,
		] );

		// Maybe the URL was encoded and we need to convert it to a valid URL.
		return $this->upload_file( $decoded );
	}

	/**
	 * @param string $file_url
	 *
	 * @return int
	 */
	protected function upload_file( $file_url ) {
		/**
		 * Allow plugins to enable local URL uploads, mainly used for testing.
		 *
		 * @since 4.9.5
		 *
		 * @param bool   $allow_local_urls Whether to allow local URLs.
		 * @param string $file_url         File URL.
		 */
		$allow_local_urls = apply_filters( 'tribe_image_uploader_local_urls', false, $file_url );

		if ( ! $allow_local_urls && ! filter_var( $file_url, FILTER_VALIDATE_URL ) ) {
			return false;
		}

		// These files need to be included as dependencies
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		require_once( ABSPATH . 'wp-admin/includes/media.php' );

		$is_local = false;
		// This is a local file no need to fetch it from the wire.
		if ( $allow_local_urls && file_exists( $file_url ) ) {
			$file = $file_url;
			$is_local = true;
		} else {
			/**
			 * Some CDN services will append query arguments to the image URL; removing
			 * them now has the potential of blocking the image fetching completely so we
			 * let them be here.
			 */
			$file = download_url( $file_url );
			if ( is_wp_error( $file ) ) {
				do_action( 'tribe_log', 'error', __CLASS__, [
					'message' => $file->get_error_message(),
					'url'     => $file_url,
					'error'   => $file,
				] );

				return false;
			}
		}

		// Upload file into WP and leave WP handle the resize and such.
		$attachment_id = media_handle_sideload(
			[
				'name'           => $this->create_file_name( $file ),
				'tmp_name'       => $file,
				'post_mime_type' => 'image',
			],
			0
		);

		// Remove the temporary file as is no longer required at this point.
		if ( ! $is_local && file_exists( $file ) ) {
			@unlink( $file );
		}

		if ( is_wp_error( $attachment_id ) ) {
			do_action( 'tribe_log', 'error', __CLASS__, [
				'message' => $attachment_id->get_error_message(),
				'url'     => $file_url,
				'error'   => $attachment_id,
			] );

			return false;
		}

		update_post_meta( $attachment_id, '_tribe_importer_original_url', $file_url );

		$this->maybe_init_attachment_guids_cache();
		$this->maybe_init_attachment_original_urls_cache();

		$attachment_post = get_post( $attachment_id );
		// Only update the cache if is a valid attachment.
		if ( $attachment_post instanceof WP_Post ) {
			self::$attachment_guids_cache[ $attachment_post->guid ] = $attachment_id;
			self::$original_urls_cache[ $file_url ]                 = $attachment_id;
		}

		return $attachment_id;
	}

	/**
	 * WordPress requires to have an extension in all all files as uses `wp_check_filetype` which uses the extension
	 * of the file to define if a file is valid or not, in this case the extension might not be present in some URLs of
	 * attachments or media files, in those cases we try to guess the right extension using the mime of the file as
	 * an alternative, if the $filename is a path we can verify the mime type using native WP functions.
	 *
	 * @since 4.11.5
	 *
	 * @param string $filename The name of the file or URL.
	 *
	 * @return string Returned a file name with an extension if is not already part of the file name.
	 */
	protected function create_file_name( $filename ) {
		/**
		 * We use the path basename only here to provided WordPress with a good filename
		 * that will allow it to correctly detect and validate the extension.
		 */
		$path = wp_parse_url( $filename, PHP_URL_PATH );

		$name       = basename( $path );
		$properties = wp_check_filetype( $name );

		// Type can be defined from the name use that one instead.
		if ( ! empty( $properties['type'] ) ) {
			return $name;
		}

		// This is not a file that exists on the system, use the name instead.
		if ( ! file_exists( $filename ) ) {
			return $name;
		}

		$mime = wp_get_image_mime( $filename );

		// There's no mime defined for the file use the plain name instead.
		if ( $mime === '' ) {
			return $name;
		}

		// create an array with the mimes as the keys and extensions as values.
		$mime_to_extensions = array_flip( wp_get_mime_types() );

		// No mime was found for the file on the array of allowed mime types, fallback to the name.
		if ( ! isset( $mime_to_extensions[ $mime ] ) ) {
			return $name;
		}

		// If there are more than one extension just ose one.
		$parts = explode( '|', $mime_to_extensions[ $mime ] );

		// Create a new name with extension.
		return implode( '.', [ $name, reset( $parts ) ] );
	}

	protected function get_attachment_ID_from_url( $featured_image ) {
		$this->maybe_init_attachment_guids_cache();
		$this->maybe_init_attachment_original_urls_cache();

		$guids_cache         = self::$attachment_guids_cache;
		$original_urls_cache = self::$original_urls_cache;
		if ( isset( $guids_cache[ $featured_image ] ) ) {
			return $guids_cache[ $featured_image ];
		}

		if ( isset( $original_urls_cache[ $featured_image ] ) ) {
			return $original_urls_cache[ $featured_image ];
		}

		return false;
	}

	protected function maybe_init_attachment_guids_cache() {
		if ( false === self::$attachment_guids_cache ) {
			/** @var \wpdb $wpdb */
			global $wpdb;
			$guids = $wpdb->get_results( "SELECT ID, guid FROM $wpdb->posts where post_type = 'attachment'" );

			if ( $guids ) {
				$keys                         = wp_list_pluck( $guids, 'guid' );
				$values                       = wp_list_pluck( $guids, 'ID' );
				self::$attachment_guids_cache = array_combine( $keys, $values );
			} else {
				self::$attachment_guids_cache = [];
			}
		}
	}

	protected function maybe_init_attachment_original_urls_cache() {
		if ( false === self::$original_urls_cache ) {
			/** @var \wpdb $wpdb */
			global $wpdb;
			$original_urls = $wpdb->get_results( "
				SELECT p.ID, pm.meta_value FROM $wpdb->posts p
				JOIN $wpdb->postmeta pm
				ON p.ID = pm.post_id
				WHERE p.post_type = 'attachment'
				AND pm.meta_key = '_tribe_importer_original_url'
			" );

			if ( $original_urls ) {
				$keys                      = wp_list_pluck( $original_urls, 'meta_value' );
				$values                    = wp_list_pluck( $original_urls, 'ID' );
				self::$original_urls_cache = array_combine( $keys, $values );
			} else {
				self::$original_urls_cache = [];
			}
		}
	}

	/**
	 * Handles errors generated during the use of `file_get_contents` to
	 * make them run-time exceptions.
	 *
	 * @since 4.7.22
	 *
	 * @param string $unused_error_code The error numeric code.
	 * @param string $message           The error message.
	 *
	 * @throws RuntimeException To pass the error as an exception to
	 *                          the handler.
	 */
	public function handle_error( $unused_error_code, $message ) {
		throw new RuntimeException( $message );
	}
}
