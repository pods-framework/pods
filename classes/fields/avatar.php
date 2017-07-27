<?php
require_once( PODS_DIR . 'classes/fields/file.php' );

/**
 * @package Pods\Fields
 */
class PodsField_Avatar extends PodsField_File {

	/**
	 * Field Type Group
	 *
	 * @var string
	 * @since 2.0
	 */
	public static $group = 'Relationships / Media';

	/**
	 * Field Type Identifier
	 *
	 * @var string
	 * @since 2.0
	 */
	public static $type = 'avatar';

	/**
	 * Field Type Label
	 *
	 * @var string
	 * @since 2.0
	 */
	public static $label = 'Avatar';

	/**
	 * Pod Types supported on (true for all, false for none, or give array of specific types supported)
	 *
	 * @var array|bool
	 * @since 2.1
	 */
	public static $pod_types = array( 'user' );

	/**
	 * {@inheritdoc}
	 */
	public function __construct() {

		self::$label = __( 'Avatar', 'pods' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function options() {

		$options = parent::options();

		unset( $options[ self::$type . '_type' ] );
		unset( $options[ self::$type . '_allowed_extensions' ] );
		unset( $options[ self::$type . '_field_template' ] );
		unset( $options[ self::$type . '_wp_gallery_output' ] );
		unset( $options[ self::$type . '_wp_gallery_link' ] );
		unset( $options[ self::$type . '_wp_gallery_columns' ] );
		unset( $options[ self::$type . '_wp_gallery_random_sort' ] );
		unset( $options[ self::$type . '_wp_gallery_size' ] );

		return $options;

	}

	/**
	 * {@inheritdoc}
	 */
	public function input( $name, $value = null, $options = null, $pod = null, $id = null ) {

		$options = (array) $options;

		$options[ self::$type . '_type' ]              = 'images';
		$options[ self::$type . '_field_template' ]    = 'rows';
		$options[ self::$type . '_wp_gallery_output' ] = 0;

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

		// Don't replace for the Avatars section of the Discussion settings page.
		if ( is_admin() ) {
			$current_screen = get_current_screen();

			if ( ! is_null( $current_screen ) && 'options-discussion' === $current_screen->id && 32 === $size ) {
				return $avatar;
			}
		}

		$user_id = 0;

		if ( is_numeric( $id_or_email ) && 0 < $id_or_email ) {
			$user_id = (int) $id_or_email;
		} elseif ( is_object( $id_or_email ) && isset( $id_or_email->user_id ) && 0 < $id_or_email->user_id ) {
			$user_id = (int) $id_or_email->user_id;
		} elseif ( is_object( $id_or_email ) && isset( $id_or_email->ID ) && isset( $id_or_email->user_login ) && 0 < $id_or_email->ID ) {
			$user_id = (int) $id_or_email->ID;
		} elseif ( ! is_object( $id_or_email ) && false !== strpos( $id_or_email, '@' ) ) {
			$_user = get_user_by( 'email', $id_or_email );

			if ( ! empty( $_user ) ) {
				$user_id = (int) $_user->ID;
			}
		}

		// Include PodsMeta if not already included.
		pods_meta();

		if ( 0 < $user_id && ! empty( PodsMeta::$user ) ) {
			$avatar_cached = pods_cache_get( $user_id . '-' . $size, 'pods_avatars' );

			if ( ! empty( $avatar_cached ) ) {
				$avatar = $avatar_cached;
			} else {
				$avatar_field = pods_transient_get( 'pods_avatar_field' );

				$user = current( PodsMeta::$user );

				if ( empty( $avatar_field ) ) {
					foreach ( $user['fields'] as $field ) {
						if ( 'avatar' === $field['type'] ) {
							$avatar_field = $field['name'];

							pods_transient_set( 'pods_avatar_field', $avatar_field );

							break;
						}
					}
				} elseif ( ! isset( $user['fields'][ $avatar_field ] ) ) {
					$avatar_field = false;
				}

				if ( ! empty( $avatar_field ) ) {
					$user_avatar = get_user_meta( $user_id, $avatar_field . '.ID', true );

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

							pods_cache_set( $user_id . '-' . $size, $avatar, 'pods_avatars' );
						}
					}
				}
			}
		}

		return $avatar;

	}

}
