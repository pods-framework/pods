<?php

require_once __DIR__ . '/file.php';

/**
 * PodsField_Avatar class.
 *
 * @package Pods\Fields
 */
class PodsField_Avatar extends PodsField_File {

	/**
	 * {@inheritdoc}
	 */
	public static $group = 'Relationships / Media';

	/**
	 * {@inheritdoc}
	 */
	public static $type = 'avatar';

	/**
	 * {@inheritdoc}
	 */
	public static $label = 'Avatar';

	/**
	 * {@inheritdoc}
	 */
	public static $pod_types = array(
		'user',
	);

	/**
	 * {@inheritdoc}
	 */
	public function setup() {

		static::$group = __( 'Relationships / Media', 'pods' );
		static::$label = __( 'Avatar', 'pods' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function options() {

		$options = parent::options();

		unset( $options[ static::$type . '_type' ], $options[ static::$type . '_allowed_extensions' ], $options[ static::$type . '_field_template' ], $options[ static::$type . '_wp_gallery_output' ], $options[ static::$type . '_wp_gallery_link' ], $options[ static::$type . '_wp_gallery_columns' ], $options[ static::$type . '_wp_gallery_random_sort' ], $options[ static::$type . '_wp_gallery_size' ] );

		return $options;

	}

	/**
	 * {@inheritdoc}
	 */
	public function input( $name, $value = null, $options = null, $pod = null, $id = null ) {

		$options = ( is_array( $options ) || is_object( $options ) ) ? $options : (array) $options;

		$options[ static::$type . '_type' ]              = 'images';
		$options[ static::$type . '_field_template' ]    = 'rows';
		$options[ static::$type . '_wp_gallery_output' ] = 0;

		parent::input( $name, $value, $options, $pod, $id );

	}

	/**
	 * Take over the avatar served from WordPress
	 *
	 * @param string            $avatar      Default Avatar Image output from WordPress.
	 * @param int|string|object $id_or_email A user ID, email address, or comment object.
	 * @param int               $size        Size of the avatar image.
	 * @param string            $default     URL to a default image to use if no avatar is available.
	 * @param string            $alt         Alternate text to use in image tag. Defaults to blank.
	 *
	 * @return string <img> tag for the user's avatar
	 */
	public function get_avatar( $avatar, $id_or_email, $size, $default = '', $alt = '' ) {

		if ( ! $this->allow_avatar_overwrite() ) {
			return $avatar;
		}

		$user_id = $this->get_avatar_user_id( $id_or_email );

		if ( 0 < $user_id && ! empty( PodsMeta::$user ) ) {
			$avatar_cached = pods_cache_get( $user_id . '-' . $size, 'pods_avatars' );

			if ( ! empty( $avatar_cached ) ) {
				$avatar = $avatar_cached;
			} else {

				$user_avatar = $this->get_avatar_id( $user_id );

				if ( ! empty( $user_avatar ) ) {
					$attributes = array(
						'alt'   => '',
						'class' => 'avatar avatar-' . $size . ' photo',
					);

					if ( ! empty( $alt ) ) {
						$attributes['alt'] = $alt;
					}

					$user_avatar = pods_image( $user_avatar, array( $size, $size ), 0, $attributes );

					if ( ! empty( $user_avatar ) ) {
						$avatar = $user_avatar;

						pods_cache_set( $user_id . '-' . $size, $avatar, 'pods_avatars', WEEK_IN_SECONDS );
					}
				}
			}
		}

		return $avatar;
	}

	/**
	 * Take over the avatar data served from WordPress
	 *
	 * @since 2.7.22
	 *
	 * @param array $args {
	 *     Optional. Arguments to return instead of the default arguments.
	 *
	 *     @type int    $size           Height and width of the avatar image file in pixels. Default 96.
	 *     @type int    $height         Display height of the avatar in pixels. Defaults to $size.
	 *     @type int    $width          Display width of the avatar in pixels. Defaults to $size.
	 *     @type string $default        URL for the default image or a default type. Accepts '404' (return
	 *                                  a 404 instead of a default image), 'retro' (8bit), 'monsterid' (monster),
	 *                                  'wavatar' (cartoon face), 'indenticon' (the "quilt"), 'mystery', 'mm',
	 *                                  or 'mysteryman' (The Oyster Man), 'blank' (transparent GIF), or
	 *                                  'gravatar_default' (the Gravatar logo). Default is the value of the
	 *                                  'avatar_default' option, with a fallback of 'mystery'.
	 *     @type bool   $force_default  Whether to always show the default image, never the Gravatar. Default false.
	 *     @type string $rating         What rating to display avatars up to. Accepts 'G', 'PG', 'R', 'X', and are
	 *                                  judged in that order. Default is the value of the 'avatar_rating' option.
	 *     @type string $scheme         URL scheme to use. See set_url_scheme() for accepted values.
	 *                                  Default null.
	 *     @type array  $processed_args When the function returns, the value will be the processed/sanitized $args
	 *                                  plus a "found_avatar" guess. Pass as a reference. Default null.
	 *     @type string $extra_attr     HTML attributes to insert in the IMG element. Is not sanitized. Default empty.
	 * }
	 * @param mixed $id_or_email The Gravatar to retrieve. Accepts a user ID, Gravatar MD5 hash,
	 *                           user email, WP_User object, WP_Post object, or WP_Comment object.
	 * @return array {
	 *     Along with the arguments passed in `$args`, this will contain a couple of extra arguments.
	 *
	 *     @type bool   $found_avatar True if we were able to find an avatar for this user,
	 *                                false or not set if we couldn't.
	 *     @type string $url          The URL of the avatar we found.
	 * }
	 */
	public function get_avatar_data( $args, $id_or_email ) {
		if ( ! $this->allow_avatar_overwrite() ) {
			return $args;
		}

		$return_args = $args;

		$args = wp_parse_args(
			$args,
			array(
				'size'           => 96,
				'height'         => null,
				'width'          => null,
				'default'        => get_option( 'avatar_default', 'mystery' ),
				'force_default'  => false,
				'rating'         => get_option( 'avatar_rating' ),
				'scheme'         => null,
				'processed_args' => null, // If used, should be a reference.
				'extra_attr'     => '',
			)
		);

		$size = $args['size'];
		if ( $args['width'] !== $args['height'] ) {
			$size = $args['width'] . 'x' . $args['height'];
		}

		$user_id = $this->get_avatar_user_id( $id_or_email );

		if ( 0 < $user_id && ! empty( PodsMeta::$user ) ) {
			$user_avatar_url = null;
			$avatar_cached   = pods_cache_get( $user_id . '-' . $size, 'pods_avatar_urls' );

			if ( ! empty( $avatar_cached ) ) {
				$user_avatar_url = $avatar_cached;
			} else {

				$user_avatar_id = $this->get_avatar_id( $user_id );

				if ( ! empty( $user_avatar_id ) ) {

					$user_avatar_url = pods_image_url( $user_avatar_id, array( $args['width'], $args['height'] ), 0 );

					if ( ! empty( $user_avatar_url ) ) {
						pods_cache_set( $user_id . '-' . $size, $user_avatar_url, 'pods_avatar_urls', WEEK_IN_SECONDS );
					}
				}
			}

			if ( $user_avatar_url ) {
				$return_args['url']          = $user_avatar_url;
				$return_args['found_avatar'] = true;
			}
		}

		return $return_args;
	}

	/**
	 * Get the custom user avatar ID.
	 *
	 * @since 2.7.22
	 *
	 * @param mixed $id_or_email The Gravatar to retrieve. Accepts a user ID, Gravatar MD5 hash,
	 *                           user email, WP_User object, WP_Post object, or WP_Comment object.
	 *
	 * @return int
	 */
	public function get_avatar_id( $id_or_email ) {
		$user_id = $this->get_avatar_user_id( $id_or_email );

		// Include PodsMeta if not already included.
		pods_meta();
		$avatar_id = 0;

		if ( 0 < $user_id && ! empty( PodsMeta::$user ) ) {
			$avatar_cached = pods_cache_get( $user_id, 'pods_avatar_ids' );

			if ( ! empty( $avatar_cached ) ) {
				$avatar_id = $avatar_cached;
			} else {
				$avatar_field = pods_transient_get( 'pods_avatar_field' );

				$user = current( PodsMeta::$user );

				if ( empty( $avatar_field ) ) {
					foreach ( $user['fields'] as $field ) {
						if ( 'avatar' === $field['type'] ) {
							$avatar_field = $field['name'];

							pods_transient_set( 'pods_avatar_field', $avatar_field, WEEK_IN_SECONDS );

							break;
						}
					}
				} elseif ( ! isset( $user['fields'][ $avatar_field ] ) ) {
					$avatar_field = false;
				}

				if ( ! empty( $avatar_field ) ) {
					$avatar_id = get_user_meta( $user_id, $avatar_field . '.ID', true );

					pods_cache_set( $user_id, $avatar_id, 'pods_avatar_ids', WEEK_IN_SECONDS );
				}//end if
			}//end if
		}//end if

		return (int) $avatar_id;
	}

	/**
	 * Get the avatar user ID based on parameter provided.
	 *
	 * @since 2.7.22
	 *
	 * @param mixed $id_or_email The Gravatar to retrieve. Accepts a user ID, Gravatar MD5 hash,
	 *                           user email, WP_User object, WP_Post object, or WP_Comment object.
	 *
	 * @return int
	 */
	public function get_avatar_user_id( $id_or_email ) {
		$user_id = 0;

		if ( is_numeric( $id_or_email ) && 0 < $id_or_email ) {
			$user_id = $id_or_email;
		} elseif ( is_object( $id_or_email ) && isset( $id_or_email->user_id ) && 0 < $id_or_email->user_id ) {
			$user_id = $id_or_email->user_id;
		} elseif ( is_object( $id_or_email ) && isset( $id_or_email->ID ) && isset( $id_or_email->user_login ) && 0 < $id_or_email->ID ) {
			$user_id = $id_or_email->ID;
		} elseif ( ! is_object( $id_or_email ) && false !== strpos( $id_or_email, '@' ) ) {
			$_user = get_user_by( 'email', $id_or_email );

			if ( ! empty( $_user ) ) {
				$user_id = $_user->ID;
			}
		}

		return (int) $user_id;
	}

	/**
	 * Checks if we're not on WordPress admin pages where we shouldn't overwrite.
	 *
	 * @since 2.7.22
	 *
	 * @return bool
	 */
	public function allow_avatar_overwrite() {

		// Don't replace for the Avatars section of the Discussion settings page.
		if ( is_admin() && ! doing_action( 'admin_bar_menu' ) && function_exists( 'get_current_screen' ) ) {
			$current_screen = get_current_screen();

			$screens = array(
				'profile',
				'user-edit',
				'options-discussion',
			);

			if ( $current_screen && in_array( $current_screen->id, $screens, true ) ) {
				return false;
			}
		}

		return true;
	}

}
