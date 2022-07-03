<?php

use Pods\API\Whatsit\Value_Field;
use Pods\Static_Cache;
use Pods\Whatsit\Field;
use Pods\Whatsit\Group;
use Pods\Whatsit\Object_Field;
use Pods\Whatsit\Pod;

/**
 * @package Pods
 */
class PodsAPI {

	/**
	 * @var PodsAPI
	 */
	public static $instance = null;

	/**
	 * @var array PodsAPI
	 */
	public static $instances = array();

	/**
	 * @var bool
	 */
	public $display_errors = false;

	/**
	 * The pod object.
	 *
	 * @var array|Pod
	 */
	public $pod_data;

	/**
	 * The pod name (deprecated).
	 *
	 * @var string
	 * @deprecated 2.8.0
	 */
	public $pod;

	/**
	 * The format type (deprecated).
	 *
	 * @var string
	 * @deprecated 2.0.0
	 */
	public $format = null;

	/**
	 * Deprecated property, not really used.
	 *
	 * @deprecated 2.8.9
	 */
	private $deprecated;

	/**
	 * Singleton-ish handling for a basic pods_api() request
	 *
	 * @param string $pod    (optional) The pod name
	 * @param string $format (deprecated) Format for import/export, "php" or "csv"
	 *
	 * @return \PodsAPI
	 *
	 * @since 2.3.5
	 */
	public static function init( $pod = null, $format = null ) {
		if ( null !== $pod || null !== $format ) {
			if ( ! isset( self::$instances[ $pod ] ) ) {
				// Cache API singleton per Pod
				self::$instances[ $pod ] = new PodsAPI( $pod, $format );
			}

			return self::$instances[ $pod ];
		}

		if ( ! is_object( self::$instance ) ) {
			self::$instance = new PodsAPI();
		}

		return self::$instance;
	}

	/**
	 * Store and retrieve data programatically
	 *
	 * @param string $pod    (optional) The pod name
	 * @param string $format (deprecated) Format for import/export, "php" or "csv"
	 *
	 * @return \PodsAPI
	 *
	 * @license http://www.gnu.org/licenses/gpl-2.0.html
	 * @since   1.7.1
	 */
	public function __construct( $pod = null, $format = null ) {
		if ( null === $pod || '' === (string) $pod  ) {
			return;
		}

		$pod = pods_clean_name( $pod );
		$pod = $this->load_pod( [ 'name' => $pod ] );

		if ( ! empty( $pod ) ) {
			$this->pod_data = $pod;
		}
	}

	/**
	 * Save a WP object and its meta
	 *
	 * @param string $object_type Object type: post|taxonomy|user|comment|setting
	 * @param array  $data        All post data to be saved
	 * @param array  $meta        (optional) Associative array of meta keys and values
	 * @param bool   $strict      (optional) Decides whether the previous saved meta should be deleted or not
	 * @param bool   $sanitized   (optional) Will unsanitize the data, should be passed if the data is sanitized before
	 *                            sending.
	 * @param array  $fields      (optional) The array of fields and their options, for further processing with.
	 *
	 * @return int|string|false The object ID after saving, false if not saved.
	 *
	 * @since 2.0.0
	 */
	public function save_wp_object( $object_type, $data, $meta = [], $strict = false, $sanitized = false, $fields = [] ) {
		if ( in_array( $object_type, [ 'post_type', 'media' ], true ) ) {
			$object_type = 'post';
		} elseif ( 'taxonomy' === $object_type ) {
			$object_type = 'term';
		}

		if ( $sanitized ) {
			$data = pods_unsanitize( $data );
			$meta = pods_unsanitize( $meta );
		}

		if ( in_array( $object_type, [ 'post', 'term', 'user', 'comment' ], true ) ) {
			return call_user_func( [ $this, 'save_' . $object_type ], $data, $meta, $strict, false, $fields );
		} elseif ( 'settings' === $object_type ) {
			// Nothing to save
			if ( empty( $meta ) ) {
				return true;
			}

			return $this->save_setting( pods_v( 'option_id', $data ), $meta, false );
		}

		/**
		 * Allow hooking in to support saving for custom object types.
		 *
		 * @since 2.8.0
		 *
		 * @param int|string|false $object_id   The object ID after saving, false if not saved.
		 * @param string           $object_type The custom object type.
		 * @param array            $data        All object data to be saved
		 * @param array            $meta        Associative array of meta keys and values.
		 * @param bool             $strict      Decides whether the previous saved meta should be deleted or not.
		 * @param bool             $sanitized   Will unsanitize the data, should be passed if the data is sanitized before sending.
		 * @param array            $fields      The array of fields and their options, for further processing with.
		 */
		$object_id = apply_filters( 'pods_api_save_wp_object_for_custom_object_type', false, $object_type, $data, $meta, $strict, $sanitized, $fields );

		if ( false === $object_id ) {
			return $object_id;
		}

		/**
		 * Allow hooking in to support saving meta using the meta fallback.
		 *
		 * @since 2.8.0
		 *
		 * @param bool   $use_meta_fallback Whether to support saving meta using the meta fallback.
		 * @param string $object_type       The custom object type.
		 * @param array  $data              All object data to be saved
		 * @param array  $meta              Associative array of meta keys and values.
		 * @param bool   $strict            Decides whether the previous saved meta should be deleted or not.
		 * @param bool   $sanitized         Will unsanitize the data, should be passed if the data is sanitized before sending.
		 * @param array  $fields            The array of fields and their options, for further processing with.
		 */
		$use_meta_fallback = apply_filters( 'pods_api_save_wp_object_use_meta_fallback', false, $object_type, $data, $meta, $strict, $sanitized, $fields );

		// Maybe use meta fallback for saving.
		if ( $use_meta_fallback ) {
			$this->save_meta( $object_type, $object_id, $meta, false, $fields );
		}

		return $object_id;
	}

	/**
	 * Delete a WP object
	 *
	 * @param string $object_type  Object type: post|user|comment
	 * @param int    $id           Object ID
	 * @param bool   $force_delete (optional) Force deletion instead of trashing (post types only)
	 *
	 * @return bool|mixed
	 *
	 * @since 2.0.0
	 */
	public function delete_wp_object( $object_type, $id, $force_delete = true ) {

		if ( in_array( $object_type, array( 'post_type', 'media' ), true ) ) {
			$object_type = 'post';
		}

		if ( 'taxonomy' === $object_type ) {
			$object_type = 'term';
		}

		if ( empty( $id ) ) {
			return false;
		}

		if ( in_array( $object_type, array( 'post' ), true ) ) {
			return wp_delete_post( $id, $force_delete );
		}

		if ( function_exists( 'wp_delete_' . $object_type ) ) {
			return call_user_func( 'wp_delete_' . $object_type, $id );
		}

		return false;
	}

	/**
	 * Save the meta for a meta type.
	 *
	 * @since 2.8.11
	 *
	 * @param string $meta_type The meta type.
	 * @param int    $id        The object ID.
	 * @param array  $meta      All meta to be saved (set value to null to delete).
	 * @param bool   $strict    Whether to delete previously saved meta not in $meta.
	 * @param array  $fields    (optional) The array of fields and their options, for further processing with.
	 *
	 * @return int The object ID.
	 */
	public function save_meta( $meta_type, $id, $meta = null, $strict = false, $fields = [] ) {
		$simple_tableless_objects = PodsForm::simple_tableless_objects();

		$conflicted = pods_no_conflict_check( $meta_type );

		if ( ! $conflicted ) {
			pods_no_conflict_on( $meta_type );
		}

		if ( ! is_array( $meta ) ) {
			$meta = [];
		}

		$id = (int) $id;

		$existing_meta = get_metadata( $meta_type, $id );

		foreach ( $existing_meta as $k => $value ) {
			if ( is_array( $value ) && 1 === count( $value ) ) {
				$existing_meta[ $k ] = current( $value );
			}
		}

		foreach ( $meta as $meta_key => $meta_value ) {
			$original_meta_value = $meta_value;

			// Prevent WP unslash removing already sanitized input.
			$meta_value = pods_slash( $meta_value );

			// Enforce boolean integer values.
			$meta_value = pods_bool_to_int( $meta_value );

			$simple    = false;
			$is_single = false;

			if ( isset( $fields[ $meta_key ] ) ) {
				$field_data = $fields[ $meta_key ];

				$simple = ( 'pick' === pods_v( 'type', $field_data ) && in_array( pods_v( 'pick_object', $field_data ), $simple_tableless_objects, true ) );

				if ( $simple ) {
					$is_single = 'single' === pods_v( 'pick_format_type', $field_data, 'single' );
				}
			}

			if ( null === $original_meta_value || ( $strict && '' === $original_meta_value ) ) {
				if ( $simple ) {
					delete_metadata( $meta_type, $id, $meta_key );
					delete_metadata( $meta_type, $id, '_pods_' . $meta_key );
				} else {
					$old_meta_value = '';

					if ( isset( $existing_meta[ $meta_key ] ) ) {
						$old_meta_value = $existing_meta[ $meta_key ];
					}

					delete_metadata( $meta_type, $id, $meta_key, $old_meta_value );
				}
			} elseif ( $simple ) {
				delete_metadata( $meta_type, $id, $meta_key );

				if ( ! is_array( $meta_value ) ) {
					$meta_value = [ $meta_value ];
				}

				if ( $is_single ) {
					// Delete it because it is not needed for single values.
					delete_metadata( $meta_type, $id, '_pods_' . $meta_key );
				} else {
					update_metadata( $meta_type, $id, '_pods_' . $meta_key, $meta_value );
				}

				foreach ( $meta_value as $value ) {
					add_metadata( $meta_type, $id, $meta_key, $value );
				}
			} else {
				update_metadata( $meta_type, $id, $meta_key, $meta_value );
			}
		}

		if ( $strict ) {
			foreach ( $existing_meta as $meta_key => $meta_value ) {
				if ( ! isset( $meta[ $meta_key ] ) ) {
					delete_metadata( $meta_type, $id, $meta_key, $meta_value );
				}
			}
		}

		if ( ! $conflicted ) {
			pods_no_conflict_off( $meta_type );
		}

		return $id;
	}

	/**
	 * Save a post and it's meta
	 *
	 * @param array $post_data All post data to be saved (using wp_insert_post / wp_update_post)
	 * @param array $post_meta (optional) All meta to be saved (set value to null to delete)
	 * @param bool  $strict    (optional) Whether to delete previously saved meta not in $post_meta
	 * @param bool  $sanitized (optional) Will unsanitize the data, should be passed if the data is sanitized before
	 *                         sending.
	 * @param array $fields    (optional) The array of fields and their options, for further processing with
	 *
	 * @return mixed
	 *
	 * @since 2.0.0
	 */
	public function save_post( $post_data, $post_meta = null, $strict = false, $sanitized = false, $fields = array() ) {

		$conflicted = pods_no_conflict_check( 'post' );

		if ( ! $conflicted ) {
			pods_no_conflict_on( 'post' );
		}

		if ( ! is_array( $post_data ) || empty( $post_data ) ) {
			$post_data = [
				'post_title' => '',
			];
		}

		if ( ! is_array( $post_meta ) ) {
			$post_meta = array();
		}

		if ( $sanitized ) {
			$post_data = pods_unsanitize( $post_data );
			$post_meta = pods_unsanitize( $post_meta );
		}

		if ( ! isset( $post_data['ID'] ) || empty( $post_data['ID'] ) ) {
			$post_data['ID'] = wp_insert_post( $post_data, true );
		} elseif ( 2 < count( $post_data ) || ! isset( $post_data['post_type'] ) ) {
			$post_data['ID'] = wp_update_post( $post_data, true );
		}

		if ( is_wp_error( $post_data['ID'] ) ) {
			if ( ! $conflicted ) {
				pods_no_conflict_off( 'post' );
			}

			/**
			 * @var $post_error WP_Error
			 */
			$post_error = $post_data['ID'];

			return pods_error( $post_error->get_error_message(), $this );
		}

		$this->save_post_meta( $post_data['ID'], $post_meta, $strict, $fields );

		if ( ! $conflicted ) {
			pods_no_conflict_off( 'post' );
		}

		return $post_data['ID'];
	}

	/**
	 * Save a post's meta
	 *
	 * @param int   $id        Post ID
	 * @param array $post_meta All meta to be saved (set value to null to delete)
	 * @param bool  $strict    Whether to delete previously saved meta not in $post_meta
	 * @param array $fields    (optional) The array of fields and their options, for further processing with
	 *
	 * @return int Id of the post with the meta
	 *
	 * @since 2.0.0
	 */
	public function save_post_meta( $id, $post_meta = null, $strict = false, $fields = [] ) {
		return $this->save_meta( 'post', $id, $post_meta, $strict, $fields );
	}

	/**
	 * Save a user and it's meta
	 *
	 * @param array $user_data All user data to be saved (using wp_insert_user / wp_update_user)
	 * @param array $user_meta (optional) All meta to be saved (set value to null to delete)
	 * @param bool  $strict    (optional) Whether to delete previously saved meta not in $user_meta
	 * @param bool  $sanitized (optional) Will unsanitize the data, should be passed if the data is sanitized before
	 *                         sending.
	 * @param array $fields    (optional) The array of fields and their options, for further processing with
	 *
	 * @return int Returns user id on success
	 *
	 * @since 2.0.0
	 */
	public function save_user( $user_data, $user_meta = null, $strict = false, $sanitized = false, $fields = array() ) {

		if ( ! is_array( $user_data ) || empty( $user_data ) ) {
			return pods_error( __( 'User data is required but is either invalid or empty', 'pods' ), $this );
		}

		$conflicted = pods_no_conflict_check( 'user' );

		if ( ! $conflicted ) {
			pods_no_conflict_on( 'user' );
		}

		if ( ! is_array( $user_meta ) ) {
			$user_meta = array();
		}

		if ( $sanitized ) {
			$user_data = pods_unsanitize( $user_data );
			$user_meta = pods_unsanitize( $user_meta );
		}

		// Set role
		if ( isset( $user_meta['role'] ) ) {
			$user_data['role'] = $user_meta['role'];

			unset( $user_meta['role'] );
		}

		if ( ! isset( $user_data['ID'] ) || empty( $user_data['ID'] ) ) {
			$user_data['ID'] = wp_insert_user( $user_data );
		} elseif ( 1 < count( $user_data ) ) {
			wp_update_user( $user_data );
		}

		if ( is_wp_error( $user_data['ID'] ) ) {
			if ( ! $conflicted ) {
				pods_no_conflict_off( 'user' );
			}

			/**
			 * @var $user_error WP_Error
			 */
			$user_error = $user_data['ID'];

			return pods_error( $user_error->get_error_message(), $this );
		}

		$this->save_user_meta( $user_data['ID'], $user_meta, $strict, $fields );

		if ( ! $conflicted ) {
			pods_no_conflict_off( 'user' );
		}

		return $user_data['ID'];
	}

	/**
	 * Save a user meta
	 *
	 * @param int   $id        User ID
	 * @param array $user_meta (optional) All meta to be saved (set value to null to delete)
	 * @param bool  $strict    (optional) Whether to delete previously saved meta not in $user_meta
	 * @param array $fields    (optional) The array of fields and their options, for further processing with
	 *
	 * @return int User ID
	 *
	 * @since 2.0.0
	 *
	 */
	public function save_user_meta( $id, $user_meta = null, $strict = false, $fields = [] ) {
		return $this->save_meta( 'user', $id, $user_meta, $strict, $fields );
	}

	/**
	 * Save a comment and it's meta
	 *
	 * @param array $comment_data All comment data to be saved (using wp_insert_comment / wp_update_comment)
	 * @param array $comment_meta (optional) All meta to be saved (set value to null to delete)
	 * @param bool  $strict       (optional) Whether to delete previously saved meta not in $comment_meta
	 * @param bool  $sanitized    (optional) Will unsanitize the data, should be passed if the data is sanitized before
	 *                            sending.
	 * @param array $fields       (optional) The array of fields and their options, for further processing with
	 *
	 * @return int Comment ID
	 *
	 * @since 2.0.0
	 */
	public function save_comment( $comment_data, $comment_meta = null, $strict = false, $sanitized = false, $fields = array() ) {

		if ( ! is_array( $comment_data ) || empty( $comment_data ) ) {
			return pods_error( __( 'Comment data is required but is either invalid or empty', 'pods' ), $this );
		}

		$conflicted = pods_no_conflict_check( 'comment' );

		if ( ! $conflicted ) {
			pods_no_conflict_on( 'comment' );
		}

		if ( ! is_array( $comment_meta ) ) {
			$comment_meta = array();
		}

		if ( $sanitized ) {
			$comment_data = pods_unsanitize( $comment_data );
			$comment_meta = pods_unsanitize( $comment_meta );
		}

		if ( ! isset( $comment_data['comment_ID'] ) || empty( $comment_data['comment_ID'] ) ) {
			$comment_data['comment_ID'] = wp_insert_comment( pods_slash( $comment_data ) );
		} elseif ( 1 < count( $comment_data ) ) {
			// Expects slashed
			wp_update_comment( $comment_data );
		}

		if ( is_wp_error( $comment_data['comment_ID'] ) ) {
			if ( ! $conflicted ) {
				pods_no_conflict_off( 'comment' );
			}

			/**
			 * @var $comment_error WP_Error
			 */
			$comment_error = $comment_data['comment_ID'];

			return pods_error( $comment_error->get_error_message(), $this );
		}

		$this->save_comment_meta( $comment_data['comment_ID'], $comment_meta, $strict, $fields );

		if ( ! $conflicted ) {
			pods_no_conflict_off( 'comment' );
		}

		return $comment_data['comment_ID'];
	}

	/**
	 * Save a comment meta
	 *
	 * @param int   $id           Comment ID
	 * @param array $comment_meta (optional) All meta to be saved (set value to null to delete)
	 * @param bool  $strict       (optional) Whether to delete previously saved meta not in $comment_meta
	 * @param array $fields       (optional) The array of fields and their options, for further processing with
	 *
	 * @return int Comment ID
	 *
	 * @since 2.0.0
	 */
	public function save_comment_meta( $id, $comment_meta = null, $strict = false, $fields = [] ) {
		return $this->save_meta( 'comment', $id, $comment_meta, $strict, $fields );
	}

	/**
	 * Save a taxonomy's term
	 *
	 * @param array $term_data All term data to be saved (using wp_insert_term / wp_update_term)
	 * @param array $term_meta All meta to be saved (set value to null to delete)
	 * @param bool  $strict    (optional) Whether to delete previously saved meta not in $post_meta
	 * @param bool  $sanitized (optional) Will unsanitize the data, should be passed if the data is sanitized before
	 *                         sending.
	 * @param array $fields    (optional) The array of fields and their options, for further processing with
	 *
	 * @return int Term ID
	 *
	 * @since 2.0.0
	 */
	public function save_term( $term_data, $term_meta, $strict = false, $sanitized = false, $fields = array() ) {

		if ( empty( $term_data['taxonomy'] ) ) {
			return 0;
		}

		$conflicted = pods_no_conflict_check( 'taxonomy' );

		if ( ! is_array( $term_data ) || empty( $term_data ) ) {
			$term_data = [
				'name' => '',
			];
		}

		if ( ! $conflicted ) {
			pods_no_conflict_on( 'taxonomy' );
		}

		if ( ! is_array( $term_meta ) ) {
			$term_meta = array();
		}

		if ( $sanitized ) {
			$term_data = pods_unsanitize( $term_data );
			$term_meta = pods_unsanitize( $term_meta );
		}

		$taxonomy = $term_data['taxonomy'];

		unset( $term_data['taxonomy'] );

		if ( empty( $term_data['term_id'] ) ) {
			$term_name = '';

			if ( ! empty( $term_data['name'] ) ) {
				$term_name = $term_data['name'];

				unset( $term_data['name'] );
			}

			$term_data['term_id'] = wp_insert_term( $term_name, $taxonomy, $term_data );
		} elseif ( 1 < count( $term_data ) ) {
			$term_data['term_id'] = wp_update_term( $term_data['term_id'], $taxonomy, $term_data );
		}

		if ( is_wp_error( $term_data['term_id'] ) ) {
			if ( ! $conflicted ) {
				pods_no_conflict_off( 'taxonomy' );
			}

			/**
			 * @var $term_error WP_Error
			 */
			$term_error = $term_data['term_id'];

			return pods_error( $term_error->get_error_message(), $this );
		} elseif ( is_array( $term_data['term_id'] ) ) {
			$term_data['term_id'] = $term_data['term_id']['term_id'];
		}

		$this->save_term_meta( $term_data['term_id'], $term_meta, $strict, $fields );

		if ( ! $conflicted ) {
			pods_no_conflict_off( 'taxonomy' );
		}

		return $term_data['term_id'];
	}

	/**
	 * Save a term's meta
	 *
	 * @param int   $id        Term ID
	 * @param array $term_meta All meta to be saved (set value to null to delete)
	 * @param bool  $strict    Whether to delete previously saved meta not in $term_meta
	 * @param array $fields    (optional) The array of fields and their options, for further processing with
	 *
	 * @return int Id of the term with the meta
	 *
	 * @since 2.0.0
	 */
	public function save_term_meta( $id, $term_meta = null, $strict = false, $fields = [] ) {
		return $this->save_meta( 'term', $id, $term_meta, $strict, $fields );
	}

	/**
	 * Save a set of options
	 *
	 * @param string $setting     Setting group name
	 * @param array  $option_data All option data to be saved
	 * @param bool   $sanitized   (optional) Will unsanitize the data, should be passed if the data is sanitized before
	 *                            sending.
	 *
	 * @return bool
	 *
	 * @since 2.3.0
	 */
	public function save_setting( $setting, $option_data, $sanitized = false ) {

		if ( ! is_array( $option_data ) || empty( $option_data ) ) {
			return pods_error( __( 'Setting data is required but is either invalid or empty', 'pods' ), $this );
		}

		$conflicted = pods_no_conflict_check( 'settings' );

		if ( ! $conflicted ) {
			pods_no_conflict_on( 'settings' );
		}

		if ( $sanitized ) {
			$option_data = pods_unsanitize( $option_data );
		}

		foreach ( $option_data as $option => $value ) {
			if ( ! empty( $setting ) ) {
				$option = $setting . '_' . $option;
			}

			// Enforce boolean integer values.
			$value = pods_bool_to_int( $value );

			update_option( $option, $value );
		}

		if ( ! $conflicted ) {
			pods_no_conflict_off( 'settings' );
		}

		return true;
	}

	/**
	 * Rename a WP object's type
	 *
	 * @param string $object_type Object type: post|taxonomy|comment|setting
	 * @param string $old_name    The old name
	 * @param string $new_name    The new name
	 *
	 * @return bool
	 *
	 * @since 2.0.0
	 */
	public function rename_wp_object_type( $object_type, $old_name, $new_name ) {

		/**
		 * @var $wpdb wpdb
		 */
		global $wpdb;

		if ( 'post_type' === $object_type ) {
			$object_type = 'post';
		}

		if ( 'post' === $object_type ) {
			pods_query( "UPDATE `{$wpdb->posts}` SET `post_type` = %s WHERE `post_type` = %s", array(
				$new_name,
				$old_name
			) );
		} elseif ( 'taxonomy' === $object_type ) {
			pods_query( "UPDATE `{$wpdb->term_taxonomy}` SET `taxonomy` = %s WHERE `taxonomy` = %s", array(
				$new_name,
				$old_name
			) );
		} elseif ( 'comment' === $object_type ) {
			pods_query( "UPDATE `{$wpdb->comments}` SET `comment_type` = %s WHERE `comment_type` = %s", array(
				$new_name,
				$old_name
			) );
		} elseif ( 'settings' === $object_type ) {
			pods_query( "UPDATE `{$wpdb->options}` SET `option_name` = REPLACE( `option_name`, %s, %s ) WHERE `option_name` LIKE '" . pods_sanitize_like( $old_name ) . "_%'", array(
				$new_name . '_',
				$old_name . '_'
			) );
		}

		return true;
	}

	/**
	 * Get a list of core WP object fields for a specific object
	 *
	 * @param string  $object  The pod type to look for, possible values: post_type, user, comment, taxonomy
	 * @param array   $pod     Array of Pod data
	 * @param boolean $refresh Whether to force refresh the information
	 *
	 * @return array Array of fields
	 *
	 * @since 2.0.0
	 */
	public function get_wp_object_fields( $object = 'post_type', $pod = null, $refresh = false ) {

		$pod_name = pods_v( 'name', $pod, $object, true );

		if ( 'media' === $pod_name ) {
			$object   = 'post_type';
			$pod_name = 'attachment';
		}

		$fields = false;

		$cache_key = null;

		if ( did_action( 'init' ) && pods_api_cache() ) {
			$cache_key = 'pods_api_object_fields_' . $object . '/' . $pod_name;
		}

		if ( $cache_key ) {
			$fields = pods_transient_get( $cache_key );
		}

		if ( false !== $fields && ! $refresh ) {
			// Add currently associated taxonomies to object fields that may not yet be set up.
			if ( 'post_type' === $object ) {
				$taxonomies = get_object_taxonomies( $pod_name, 'objects' );

				foreach ( $taxonomies as $taxonomy ) {
					$fields[ $taxonomy->name ] = [
						'name'            => $taxonomy->name,
						'label'           => $taxonomy->labels->name,
						'type'            => 'taxonomy',
						'pick_object'     => 'taxonomy',
						'pick_val'        => $taxonomy->name,
						'taxonomy_object' => 'taxonomy',
						'taxonomy_val'    => $taxonomy->name,
						'alias'           => [],
						'hidden'          => true,
						'options'         => [
							'taxonomy_format_type' => 'multi',
						],
					];
				}
			}

			return $this->do_hook( 'get_wp_object_fields', $fields, $object, $pod );
		}

		$fields = [];

		if ( 'post_type' === $object ) {
			$fields = [
				'ID'                    => [
					'name'                 => 'ID',
					'label'                => 'ID',
					'type'                 => 'number',
					'alias'                => [ 'id' ],
					'options'              => [
						'number_format' => '9999.99',
					],
					'hide_in_default_form' => true,
				],
				'post_title'            => [
					'name'    => 'post_title',
					'label'   => 'Title',
					'type'    => 'text',
					'alias'   => [ 'title', 'name' ],
					'options' => [
						'display_filter'      => 'the_title',
						'display_filter_args' => [ 'post_ID' ],
					],
				],
				'post_content'          => [
					'name'    => 'post_content',
					'label'   => 'Content',
					'type'    => 'wysiwyg',
					'alias'   => [ 'content' ],
					'options' => [
						'wysiwyg_allowed_html_tags' => '',
						'display_filter'            => 'the_content',
						'pre_save'                  => 0,
					],
				],
				'post_excerpt'          => [
					'name'    => 'post_excerpt',
					'label'   => 'Excerpt',
					'type'    => 'paragraph',
					'alias'   => [ 'excerpt' ],
					'options' => [
						'paragraph_allow_html'        => 1,
						'paragraph_allowed_html_tags' => '',
						'display_filter'              => 'the_excerpt',
						'pre_save'                    => 0,
					],
				],
				'post_author'           => [
					'name'        => 'post_author',
					'label'       => 'Author',
					'type'        => 'pick',
					'alias'       => [ 'author' ],
					'pick_object' => 'user',
					'options'     => [
						'pick_format_type'   => 'single',
						'pick_format_single' => 'autocomplete',
						'default_value'      => '{@user.ID}',
					],
				],
				'post_date'             => [
					'name'  => 'post_date',
					'label' => 'Publish Date',
					'type'  => 'datetime',
					'alias' => [ 'created', 'date' ],
				],
				'post_date_gmt'         => [
					'name'                 => 'post_date_gmt',
					'label'                => 'Publish Date (GMT)',
					'type'                 => 'datetime',
					'alias'                => [],
					'hidden'               => true,
					'hide_in_default_form' => true,
				],
				'post_status'           => [
					'name'        => 'post_status',
					'label'       => 'Status',
					'type'        => 'pick',
					'pick_object' => 'post-status',
					'default'     => $this->do_hook( 'default_status_' . $pod_name, pods_v( 'default_status', pods_v( 'options', $pod ), 'draft', true ), $pod ),
					'alias'       => [ 'status' ],
				],
				'comment_status'        => [
					'name'    => 'comment_status',
					'label'   => 'Comment Status',
					'type'    => 'text',
					'default' => get_option( 'default_comment_status', 'open' ),
					'alias'   => [],
					'data'    => [
						'open'   => __( 'Open', 'pods' ),
						'closed' => __( 'Closed', 'pods' ),
					],
				],
				'ping_status'           => [
					'name'                 => 'ping_status',
					'label'                => 'Ping Status',
					'default'              => get_option( 'default_ping_status', 'open' ),
					'type'                 => 'text',
					'alias'                => [],
					'data'                 => [
						'open'   => __( 'Open', 'pods' ),
						'closed' => __( 'Closed', 'pods' ),
					],
					'hide_in_default_form' => true,
				],
				'post_password'         => [
					'name'  => 'post_password',
					'label' => 'Password',
					'type'  => 'password',
					'alias' => [],
				],
				'post_name'             => [
					'name'  => 'post_name',
					'label' => 'Permalink',
					'type'  => 'slug',
					'alias' => [ 'slug', 'permalink' ],
				],
				'to_ping'               => [
					'name'                 => 'to_ping',
					'label'                => 'To Ping',
					'type'                 => 'text',
					'alias'                => [],
					'hidden'               => true,
					'hide_in_default_form' => true,
				],
				'pinged'                => [
					'name'                 => 'pinged',
					'label'                => 'Pinged',
					'type'                 => 'text',
					'alias'                => [],
					'hidden'               => true,
					'hide_in_default_form' => true,
				],
				'post_modified'         => [
					'name'                 => 'post_modified',
					'label'                => 'Last Modified Date',
					'type'                 => 'datetime',
					'alias'                => [ 'modified' ],
					'hidden'               => true,
					'hide_in_default_form' => true,
				],
				'post_modified_gmt'     => [
					'name'                 => 'post_modified_gmt',
					'label'                => 'Last Modified Date (GMT)',
					'type'                 => 'datetime',
					'alias'                => [],
					'hidden'               => true,
					'hide_in_default_form' => true,
				],
				'post_content_filtered' => [
					'name'                 => 'post_content_filtered',
					'label'                => 'Content (filtered)',
					'type'                 => 'paragraph',
					'alias'                => [],
					'hidden'               => true,
					'options'              => [
						'paragraph_allow_html'        => 1,
						'paragraph_oembed'            => 1,
						'paragraph_wptexturize'       => 1,
						'paragraph_convert_chars'     => 1,
						'paragraph_wpautop'           => 1,
						'paragraph_allow_shortcode'   => 1,
						'paragraph_allowed_html_tags' => '',
					],
					'hide_in_default_form' => true,
				],
				'post_parent'           => [
					'name'        => 'post_parent',
					'label'       => 'Parent',
					'type'        => 'pick',
					'pick_object' => 'post_type',
					'pick_val'    => '__current__',
					'alias'       => [ 'parent' ],
					'data'        => [],
					'hidden'      => true,
				],
				'guid'                  => [
					'name'                 => 'guid',
					'label'                => 'GUID',
					'type'                 => 'text',
					'alias'                => [],
					'hidden'               => true,
					'hide_in_default_form' => true,
				],
				'menu_order'            => [
					'name'    => 'menu_order',
					'label'   => 'Menu Order',
					'type'    => 'number',
					'alias'   => [],
					'options' => [
						'number_format' => '9999.99',
					],
				],
				'post_type'             => [
					'name'                 => 'post_type',
					'label'                => 'Type',
					'type'                 => 'text',
					'alias'                => [ 'type' ],
					'hidden'               => true,
					'hide_in_default_form' => true,
				],
				'post_mime_type'        => [
					'name'                 => 'post_mime_type',
					'label'                => 'Mime Type',
					'type'                 => 'text',
					'alias'                => [],
					'hidden'               => true,
					'hide_in_default_form' => true,
				],
				'comment_count'         => [
					'name'                 => 'comment_count',
					'label'                => 'Comment Count',
					'type'                 => 'number',
					'alias'                => [],
					'hidden'               => true,
					'hide_in_default_form' => true,
				],
				'comments'              => [
					'name'                 => 'comments',
					'label'                => 'Comments',
					'type'                 => 'comment',
					'pick_object'          => 'comment',
					'pick_val'             => 'comment',
					'alias'                => [],
					'hidden'               => true,
					'options'              => [
						'comment_format_type' => 'multi',
					],
					'hide_in_default_form' => true,
				],
			];

			if ( ! empty( $pod ) ) {
				$taxonomies = get_object_taxonomies( $pod_name, 'objects' );

				foreach ( $taxonomies as $taxonomy ) {
					$fields[ $taxonomy->name ] = [
						'name'            => $taxonomy->name,
						'label'           => $taxonomy->labels->name,
						'type'            => 'taxonomy',
						'pick_object'     => 'taxonomy',
						'pick_val'        => $taxonomy->name,
						'taxonomy_object' => 'taxonomy',
						'taxonomy_val'    => $taxonomy->name,
						'alias'           => [],
						'hidden'          => true,
						'options'         => [
							'taxonomy_format_type' => 'multi',
						],
					];
				}
			}
		} elseif ( 'user' === $object ) {
			$fields = [
				'ID'              => [
					'name'                 => 'ID',
					'label'                => 'ID',
					'type'                 => 'number',
					'alias'                => [ 'id' ],
					'options'              => [
						'number_format' => '9999.99',
					],
					'hide_in_default_form' => true,
				],
				'user_login'      => [
					'name'    => 'user_login',
					'label'   => 'Title',
					'type'    => 'text',
					'alias'   => [ 'login' ],
					'options' => [
						'required' => 1,
					],
				],
				'user_nicename'   => [
					'name'  => 'user_nicename',
					'label' => 'Permalink',
					'type'  => 'slug',
					'alias' => [ 'nicename', 'slug', 'permalink' ],
				],
				'display_name'    => [
					'name'  => 'display_name',
					'label' => 'Display Name',
					'type'  => 'text',
					'alias' => [ 'title', 'name' ],
				],
				'user_pass'       => [
					'name'    => 'user_pass',
					'label'   => 'Password',
					'type'    => 'text',
					'alias'   => [ 'password', 'pass' ],
					'options' => [
						'required'         => 1,
						'text_format_type' => 'password',
					],
				],
				'user_email'      => [
					'name'    => 'user_email',
					'label'   => 'E-mail',
					'type'    => 'text',
					'alias'   => [ 'email' ],
					'options' => [
						'required'         => 1,
						'text_format_type' => 'email',
					],
				],
				'user_url'        => [
					'name'    => 'user_url',
					'label'   => 'URL',
					'type'    => 'text',
					'alias'   => [ 'url', 'website' ],
					'options' => [
						'required'            => 0,
						'text_format_type'    => 'website',
						'text_format_website' => 'normal',
					],
				],
				'user_registered' => [
					'name'                 => 'user_registered',
					'label'                => 'Registration Date',
					'type'                 => 'date',
					'alias'                => [ 'created', 'date', 'registered' ],
					'options'              => [
						'date_format_type' => 'datetime',
					],
					'hidden'               => true,
					'hide_in_default_form' => true,
				],
			];
		} elseif ( 'comment' === $object ) {
			$fields = [
				'comment_ID'           => [
					'name'                 => 'comment_ID',
					'label'                => 'ID',
					'type'                 => 'number',
					'alias'                => [ 'id', 'ID', 'comment_id' ],
					'options'              => [
						'number_format' => '9999.99',
					],
					'hide_in_default_form' => true,
				],
				'comment_content'      => [
					'name'  => 'comment_content',
					'label' => 'Content',
					'type'  => 'wysiwyg',
					'alias' => [ 'content' ],
				],
				'comment_approved'     => [
					'name'    => 'comment_approved',
					'label'   => 'Approved',
					'type'    => 'number',
					'alias'   => [ 'approved' ],
					'options' => [
						'number_format' => '9999.99',
					],
				],
				'comment_post_ID'      => [
					'name'  => 'comment_post_ID',
					'label' => 'Post',
					'type'  => 'pick',
					'alias' => [ 'post', 'post_id' ],
					'data'  => [],
				],
				'user_id'              => [
					'name'        => 'user_id',
					'label'       => 'Author',
					'type'        => 'pick',
					'alias'       => [ 'author' ],
					'pick_object' => 'user',
					'data'        => [],
				],
				'comment_date'         => [
					'name'    => 'comment_date',
					'label'   => 'Date',
					'type'    => 'date',
					'alias'   => [ 'created', 'date' ],
					'options' => [
						'date_format_type' => 'datetime',
					],
				],
				'comment_author'       => [
					'name'  => 'comment_author',
					'label' => 'Author',
					'type'  => 'text',
					'alias' => [ 'author' ],
				],
				'comment_author_email' => [
					'name'  => 'comment_author_email',
					'label' => 'Author E-mail',
					'type'  => 'email',
					'alias' => [ 'author_email' ],
				],
				'comment_author_url'   => [
					'name'  => 'comment_author_url',
					'label' => 'Author URL',
					'type'  => 'text',
					'alias' => [ 'author_url' ],
				],
				'comment_author_IP'    => [
					'name'                 => 'comment_author_IP',
					'label'                => 'Author IP',
					'type'                 => 'text',
					'alias'                => [ 'author_IP' ],
					'hide_in_default_form' => true,
				],
				'comment_type'         => [
					'name'                 => 'comment_type',
					'label'                => 'Type',
					'type'                 => 'text',
					'alias'                => [ 'type' ],
					'hidden'               => true,
					'hide_in_default_form' => true,
				],
				'comment_parent'       => [
					'name'                 => 'comment_parent',
					'label'                => 'Parent',
					'type'                 => 'pick',
					'pick_object'          => 'comment',
					'pick_val'             => '__current__',
					'alias'                => [ 'parent' ],
					'data'                 => [],
					'hidden'               => true,
					'hide_in_default_form' => true,
				],
			];
		} elseif ( 'taxonomy' === $object ) {
			$fields = [
				'term_id'          => [
					'name'                 => 'term_id',
					'label'                => 'ID',
					'type'                 => 'number',
					'alias'                => [ 'id', 'ID' ],
					'options'              => [
						'number_format' => '9999.99',
					],
					'hide_in_default_form' => true,
				],
				'name'             => [
					'name'  => 'name',
					'label' => 'Title',
					'type'  => 'text',
					'alias' => [ 'title' ],
				],
				'slug'             => [
					'name'  => 'slug',
					'label' => 'Permalink',
					'type'  => 'slug',
					'alias' => [ 'permalink' ],
				],
				'description'      => [
					'name'  => 'description',
					'label' => 'Description',
					'type'  => 'wysiwyg',
					'alias' => [ 'content' ],
				],
				'taxonomy'         => [
					'name'  => 'taxonomy',
					'label' => 'Taxonomy',
					'type'  => 'text',
					'alias' => [],
				],
				'parent'           => [
					'name'        => 'parent',
					'label'       => 'Parent',
					'type'        => 'pick',
					'pick_object' => 'taxonomy',
					'pick_val'    => '__current__',
					'alias'       => [ 'parent' ],
					'data'        => [],
					'hidden'      => true,
				],
				'term_taxonomy_id' => [
					'name'                 => 'term_taxonomy_id',
					'label'                => 'Term Taxonomy ID',
					'type'                 => 'number',
					'alias'                => [],
					'hidden'               => true,
					'options'              => [
						'number_format' => '9999.99',
					],
					'hide_in_default_form' => true,
				],
				'term_group'       => [
					'name'                 => 'term_group',
					'label'                => 'Term Group',
					'type'                 => 'number',
					'alias'                => [ 'group' ],
					'hidden'               => true,
					'options'              => [
						'number_format' => '9999.99',
					],
					'hide_in_default_form' => true,
				],
				'count'            => [
					'name'                 => 'count',
					'label'                => 'Count',
					'type'                 => 'number',
					'alias'                => [],
					'hidden'               => true,
					'options'              => [
						'number_format' => '9999.99',
					],
					'hide_in_default_form' => true,
				],
			];
		} elseif ( 'pod' === $object ) {
			$fields = [
				'id' => [
					'name'                 => 'id',
					'label'                => 'ID',
					'type'                 => 'number',
					'alias'                => [ 'ID' ],
					'options'              => [
						'number_format' => '9999.99',
					],
					'hide_in_default_form' => true,
				],
			];
		}

		$fields = $this->do_hook( 'get_wp_object_fields', $fields, $object, $pod );

		foreach ( $fields as $field => $options ) {
			if ( ! isset( $options['alias'] ) ) {
				$options['alias'] = array();
			} else {
				$options['alias'] = (array) $options['alias'];
			}

			if ( ! isset( $options['name'] ) ) {
				$options['name'] = $field;
			}

			$fields[ $field ] = $options;
		}

		$fields = PodsForm::fields_setup( $fields );

		if ( $cache_key ) {
			pods_transient_set( $cache_key, $fields, WEEK_IN_SECONDS );
		}

		return $fields;
	}

	/**
	 *
	 * @see   PodsAPI::save_pod
	 *
	 * Add a Pod via the Wizard
	 *
	 * $params['create_extend'] string Create or Extend a Content Type
	 * $params['create_pod_type'] string Pod Type (for Creating)
	 * $params['create_name'] string Pod Name (for Creating)
	 * $params['create_label_plural'] string Plural Label (for Creating)
	 * $params['create_label_singular'] string Singular Label (for Creating)
	 * $params['create_storage'] string Storage Type (for Creating)
	 * $params['create_rest_api'] int Whether REST API will be enabled (for Creating Post Types and Taxonomies)
	 * $params['extend_pod_type'] string Pod Type (for Extending)
	 * $params['extend_post_type'] string Post Type (for Extending Post Types)
	 * $params['extend_taxonomy'] string Taxonomy (for Extending Taxonomies)
	 * $params['extend_storage'] string Storage Type (for Extending Post Types / Users / Comments)
	 *
	 * @param array $params An associative array of parameters
	 *
	 * @return bool|int Pod ID
	 * @since 2.0.0
	 */
	public function add_pod( $params ) {
		$defaults = [
			'create_extend'   => 'create',
			'create_pod_type' => 'post_type',

			'create_name'           => '',
			'create_label_singular' => '',
			'create_label_plural'   => '',
			'create_storage'        => 'meta',
			'create_rest_api'       => 1,

			'create_setting_name'  => '',
			'create_label_title'   => '',
			'create_label_menu'    => '',
			'create_menu_location' => 'settings',

			'extend_pod_type'         => 'post_type',
			'extend_post_type'        => 'post',
			'extend_taxonomy'         => 'category',
			'extend_table'            => '',
			'extend_storage'          => 'meta',
			'extend_storage_taxonomy' => '',
		];

		$params = (object) array_merge( $defaults, (array) $params );

		if ( empty( $params->create_extend ) || ! in_array( $params->create_extend, array( 'create', 'extend' ) ) ) {
			return pods_error( __( 'Please choose whether to Create or Extend a Content Type', 'pods' ), $this );
		}

		$pod_params = array(
			'name'          => '',
			'label'         => '',
			'type'          => '',
			'storage'       => 'table',
			'object'        => '',
			'options'       => array(),
			'create_extend' => $params->create_extend,
		);

		if ( 'create' === $params->create_extend ) {
			$label = ucwords( str_replace( '_', ' ', $params->create_name ) );

			if ( ! empty( $params->create_label_singular ) ) {
				$label = $params->create_label_singular;
			}

			$pod_params['name']           = $params->create_name;
			$pod_params['label']          = ( ! empty( $params->create_label_plural ) ? $params->create_label_plural : $label );
			$pod_params['type']           = $params->create_pod_type;
			$pod_params['label_singular'] = ( ! empty( $params->create_label_singular ) ? $params->create_label_singular : $pod_params['label'] );
			$pod_params['public']         = 1;
			$pod_params['show_ui']        = 1;

			// Auto-generate name if not provided
			if ( empty( $pod_params['name'] ) && ! empty( $pod_params['label_singular'] ) ) {
				$pod_params['name'] = pods_clean_name( $pod_params['label_singular'] );
			}

			if ( 'post_type' === $pod_params['type'] ) {
				if ( empty( $pod_params['name'] ) ) {
					return pods_error( __( 'Please enter a Name for this Pod', 'pods' ), $this );
				}

				$pod_params['storage'] = pods_tableless() ? 'meta' : $params->create_storage;

				$pod_params['rest_enable'] = 1 === (int) $params->create_rest_api ? 1 : 0;
			} elseif ( 'taxonomy' === $pod_params['type'] ) {
				if ( empty( $pod_params['name'] ) ) {
					return pods_error( __( 'Please enter a Name for this Pod', 'pods' ), $this );
				}

				// Backwards compatibility with old parameter name.
				if ( ! empty( $params->create_storage_taxonomy ) ) {
					$params->create_storage = $params->create_storage_taxonomy;
				}

				$pod_params['storage'] = pods_tableless() ? 'meta' : $params->create_storage;

				$pod_params['hierarchical'] = 1;

				$pod_params['rest_enable'] = 1 === (int) $params->create_rest_api ? 1 : 0;
			} elseif ( 'pod' === $pod_params['type'] ) {
				if ( empty( $pod_params['name'] ) ) {
					return pods_error( __( 'Please enter a Name for this Pod', 'pod' ), $this );
				}

				if ( pods_tableless() ) {
					$pod_params['type']    = 'post_type';
					$pod_params['storage'] = 'meta';
				}
			} elseif ( 'settings' === $pod_params['type'] ) {
				$pod_params['name']          = $params->create_setting_name;
				$pod_params['label']         = ( ! empty( $params->create_label_title ) ? $params->create_label_title : ucwords( str_replace( '_', ' ', $params->create_setting_name ) ) );
				$pod_params['menu_name']     = ( ! empty( $params->create_label_menu ) ? $params->create_label_menu : $pod_params['label'] );
				$pod_params['menu_location'] = $params->create_menu_location;
				$pod_params['storage']       = 'none';

				// Auto-generate name if not provided
				if ( empty( $pod_params['name'] ) && ! empty( $pod_params['label'] ) ) {
					$pod_params['name'] = pods_clean_name( $pod_params['label'] );
				}

				if ( empty( $pod_params['name'] ) ) {
					return pods_error( __( 'Please enter a Name for this Pod', 'pods' ), $this );
				}
			}
		} elseif ( 'extend' === $params->create_extend ) {
			$pod_params['type'] = $params->extend_pod_type;

			if ( 'post_type' === $pod_params['type'] ) {
				$pod_params['storage'] = $params->extend_storage;

				if ( pods_tableless() ) {
					$pod_params['storage'] = 'meta';
				}

				$pod_params['name'] = $params->extend_post_type;
			} elseif ( 'taxonomy' === $pod_params['type'] ) {
				$pod_params['storage'] = $params->extend_storage;

				if ( ! function_exists( 'get_term_meta' ) || ! empty( $params->extend_storage_taxonomy ) ) {
					$pod_params['storage'] = $params->extend_storage_taxonomy;
				}

				if ( pods_tableless() ) {
					$pod_params['storage'] = ( function_exists( 'get_term_meta' ) ? 'meta' : 'none' );
				}

				$pod_params['name'] = $params->extend_taxonomy;
			} elseif ( 'table' === $pod_params['type'] ) {
				$pod_params['storage'] = 'table';
				$pod_params['name']    = $params->extend_table;
			} else {
				$pod_params['storage'] = $params->extend_storage;

				if ( pods_tableless() ) {
					$pod_params['storage'] = 'meta';
				}

				$pod_params['name'] = $params->extend_pod_type;
			}

			$pod_params['label']  = ucwords( str_replace( '_', ' ', $pod_params['name'] ) );
			$pod_params['object'] = $pod_params['name'];
		}

		if ( empty( $pod_params['object'] ) ) {
			if ( 'post_type' === $pod_params['type'] ) {
				$check = get_post_type_object( $pod_params['name'] );

				if ( ! empty( $check ) ) {
					return pods_error( sprintf( __( 'Post Type %s already exists, try extending it instead', 'pods' ), $pod_params['name'] ), $this );
				}

				$pod_params['supports_title']  = 1;
				$pod_params['supports_editor'] = 1;
			} elseif ( 'taxonomy' === $pod_params['type'] ) {
				$check = get_taxonomy( $pod_params['name'] );

				if ( ! empty( $check ) ) {
					return pods_error( sprintf( __( 'Taxonomy %s already exists, try extending it instead', 'pods' ), $pod_params['name'] ), $this );
				}
			}
		}

		if ( ! empty( $pod_params ) ) {
			return $this->save_pod( $pod_params );
		}

		return false;
	}

	/**
	 * Add or edit a Pod
	 *
	 * $params['id'] int The Pod ID
	 * $params['name'] string The Pod name
	 * $params['label'] string The Pod label
	 * $params['type'] string The Pod type
	 * $params['object'] string The object being extended (if any)
	 * $params['storage'] string The Pod storage
	 * $params['create_extend'] string Create or Extend a Content Type
	 * $params['order'] array List of group and field IDs to reorder
	 *
	 * @param array|Pod $params    An associative array of parameters
	 * @param bool      $sanitized (optional) Decides whether the params have been sanitized before being passed, will
	 *                             sanitize them if false.
	 * @param bool|int  $db        (optional) Whether to save into the DB or just return Pod array.
	 *
	 * @throws Exception
	 *
	 * @return int Pod ID
	 * @since 1.7.9
	 */
	public function save_pod( $params, $sanitized = false, $db = true ) {
		if ( $params instanceof Pod ) {
			$params = [
				'id'  => $params->get_id(),
				'pod' => $params,
			];
		}

		$params = (object) $params;

		$extend = false;

		if ( isset( $params->create_extend ) ) {
			$extend = 'extend' === $params->create_extend;

			unset( $params->create_extend );
		}

		$pod = null;

		$lookup_type = null;

		if ( isset( $params->pod ) && $params->pod instanceof Pod ) {
			$pod = $params->pod;

			unset( $params->pod );

			$lookup_type = 'Pod';
		} else {
			$load_params = [];

			$fail_on_load = false;

			if ( ! empty( $params->id ) ) {
				$load_params['id'] = $params->id;

				$fail_on_load = true;

				$lookup_type = 'id';
			} elseif ( ! empty( $params->old_name ) ) {
				$load_params['name'] = $params->old_name;

				$fail_on_load = true;

				$lookup_type = 'old_name';
			} elseif ( ! empty( $params->name ) ) {
				$load_params['name'] = $params->name;

				$lookup_type = 'name';
			} elseif ( ! empty( $params->label ) ) {
				$load_params = false;

				$lookup_type = 'new';
			} else{
				return pods_error( __( 'Pod name or label is required', 'pods' ), $this );
			}

			if ( $load_params ) {
				$pod = $this->load_pod( $load_params );

				if ( $fail_on_load ) {
					if ( is_wp_error( $pod ) ) {
						return $pod;
					} elseif ( empty( $pod ) ) {
						return pods_error( __( 'Pod not found', 'pods' ), $this );
					}
				}
			}
		}

		if ( empty( $params->name ) ) {
			if ( $pod ) {
				$params->name = $pod['name'];
			} elseif ( ! empty( $params->label ) ) {
				$params->name = pods_clean_name( $params->label );
			} else {
				return pods_error( __( 'Pod name or label is required', 'pods' ), $this );
			}
		}

		if ( $pod instanceof Pod ) {
			$groups = $pod->get_groups();

			$pod = $pod->get_args();

			$pod['groups'] = [];

			foreach ( $groups as $group ) {
				$fields = $group->get_fields();

				$pod['groups'][ $group->name ] = $group->get_args();

				$pod['groups'][ $group->name ]['fields'] = [];

				foreach ( $fields as $field ) {
					$pod['groups'][ $group->name ]['fields'][ $field->name ] = $field->get_args();
				}
			}
		}

		if ( false === $sanitized ) {
			$params = pods_sanitize( $params );

			$sanitized = true;
		}

		$old_id      = null;
		$old_name    = null;
		$old_storage = null;
		$old_groups  = array();
		$old_fields  = array();

		if ( isset( $params->name ) && ! isset( $params->object ) ) {
			$params->name = pods_clean_name( $params->name );
		}

		$params->overwrite = ! empty( $params->overwrite ) ? (boolean) $params->overwrite : false;

		$order_group_fields = null;

		if ( isset( $params->order ) ) {
			$order_group_fields = $params->order;

			unset( $params->order );
		}

		if ( ! empty( $pod ) ) {
			// Existing pod (update).
			$old_id      = $pod['id'];
			$old_name    = $pod['name'];
			$old_storage = isset( $pod['storage'] ) ? $pod['storage'] : 'meta';
			$old_groups  = isset( $pod['groups'] ) ? $pod['groups'] : [];
			$old_fields  = isset( $pod['fields'] ) ? $pod['fields'] : [];

			// When renaming a Pod, use the old ID for reference if empty.
			if ( ( 'old_name' === $lookup_type || $params->overwrite ) && empty( $params->id ) ) {
				$params->id = $old_id;
			}

			// Get group fields if we have groups.
			if ( ! empty( $old_groups ) ) {
				$old_fields = wp_list_pluck( array_values( $old_groups ), 'fields' );

				if ( ! empty( $old_fields ) ) {
					$old_fields = array_merge( ...$old_fields );
				}
			}

			if ( ! isset( $params->name ) ) {
				// Check if name is intentionally not set, set it as current name.
				$params->name = $pod['name'];
			}

			if ( $old_name !== $params->name ) {
				if ( false !== $this->pod_exists( array( 'name' => $params->name ) ) ) {
					return pods_error( sprintf( __( 'Pod %1$s already exists, you cannot rename %2$s to that', 'pods' ), $params->name, $old_name ), $this );
				}

				if (
					in_array( $pod['type'], array( 'user', 'comment', 'media' ), true )
					&& in_array( $pod['object'], array( 'user', 'comment', 'media' ), true )
				) {
					return pods_error( sprintf( __( 'Pod %s cannot be renamed, it extends an existing WP Object', 'pods' ), $old_name ), $this );
				}

				if (
					! empty( $pod['object'] )
					&& $pod['object'] === $old_name
					&& in_array( $pod['type'], array( 'post_type', 'taxonomy' ), true )
				) {
					return pods_error( sprintf( __( 'Pod %s cannot be renamed, it extends an existing WP Object', 'pods' ), $old_name ), $this );
				}
			}

			if ( ! isset( $params->id ) || (int) $old_id !== (int) $params->id ) {
				if ( isset( $params->type, $params->object ) && $params->type === $pod['type'] && $params->object === $pod['object'] ) {
					return pods_error( sprintf( __( 'Pod using %s already exists, you can not reuse an object across multiple pods', 'pods' ), $params->object ), $this );
				} else {
					return pods_error( sprintf( __( 'Pod %s already exists', 'pods' ), $params->name ), $this );
				}
			}
		} else {
			// New pod (create).

			if ( empty( $params->name ) ) {
				return pods_error( __( 'Pod name is required', 'pods' ), $this );
			}

			if (
				in_array( $params->name, pods_reserved_keywords( 'pods' ), true )
				|| (
					in_array( $params->name, pods_reserved_keywords( 'wp' ), true )
					&& in_array( pods_v( 'type', $params ), [ 'post_type', 'taxonomy' ], true ) )
			) {
				$valid_name = false;

				// Only if it's extending an existing content type then these
				// names are still allowed, even if they are reserved.
				if ( $extend ) {
					if ( 'post_type' === pods_v( 'type', $params ) ) {
						$valid_name = in_array( $params->name, get_post_types(), true );
					} elseif ( 'taxonomy' === pods_v( 'type', $params ) ) {
						$valid_name = in_array( $params->name, get_taxonomies(), true );
					}
				}

				if ( ! $valid_name ) {
					return pods_error( sprintf( __( '%s is reserved for internal WordPress or Pods usage, please try a different name', 'pods' ), $params->name ), $this );
				}
			}

			$pod = array(
				'id'          => 0,
				'name'        => $params->name,
				'label'       => $params->name,
				'description' => '',
				'type'        => 'pod',
				'storage'     => 'table',
				'object'      => '',
				'alias'       => '',
				'groups'      => array(),
			);
		}

		// Blank out fields and options for AJAX calls (everything should be sent to it for a full overwrite)
		if ( ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || $params->overwrite ) {
			$pod['groups'] = array();
		}

		// Setup options
		$options = get_object_vars( $params );

		if ( isset( $options['method'] ) ) {
			unset( $options['method'] );
		}

		if ( isset( $options['overwrite'] ) ) {
			unset( $options['overwrite'] );
		}

		$pod = pods_config_merge_data( $pod, $options );

		if ( is_array( $pod ) && isset( $pod['options'] ) ) {
			$pod = array_merge( $pod, $pod['options'] );

			unset( $pod['options'] );
		}

		// Get raw args because $pod may be Pod object and could return magic __get() options.
		$raw_args = $pod;

		if ( $pod instanceof Pod ) {
			$raw_args = $pod->get_args();
		}

		$options_ignore = array(
			'_locale',
			'adhoc',
			'attributes',
			'dependency',
			'depends-on',
			'developer_mode',
			'excludes-on',
			'field_id',
			'field_index',
			'field_parent',
			'field_parent_select',
			'field_slug',
			'field_type',
			'group',
			'group_id',
			'grouped',
			'is_new',
			'join',
			'meta_field_id',
			'meta_field_index',
			'meta_field_value',
			'meta_table',
			'object_name',
			'object_type',
			'old_name',
			'orderby',
			'parent',
			'pod',
			'pod_field_id',
			'pod_field_index',
			'pod_table',
			'post_status',
			'recurse',
			'table',
			'where',
			'where_default',
			'podType',
			'storageType',
		);

		// Remove options we do not want to set on the Pod.
		foreach ( $options_ignore as $ignore ) {
			if ( isset( $raw_args[ $ignore ] ) ) {
				unset( $pod[ $ignore ] );
			}
		}

		// Enforce pod types and storage types.
		if ( pods_tableless() && ! in_array( $pod['type'], array( 'settings', 'table' ), true ) ) {
			if ( 'pod' === $pod['type'] ) {
				$pod['type'] = 'post_type';
			}

			if ( 'table' === $pod['storage'] ) {
				if ( 'taxonomy' === $pod['type'] && ! function_exists( 'get_term_meta' ) ) {
					$pod['storage'] = 'none';
				} else {
					$pod['storage'] = 'meta';
				}
			}
		}

		/**
		 * @var WP_Query
		 */
		global $wp_query;

		$reserved_query_vars = array(
			'post_type',
			'taxonomy',
			'output'
		);

		if ( is_object( $wp_query ) ) {
			$reserved_query_vars = array_merge( $reserved_query_vars, array_keys( $wp_query->fill_query_vars( array() ) ) );
		}

		if ( isset( $pod['query_var_string'] ) && in_array( $pod['query_var_string'], $reserved_query_vars, true ) ) {
			$pod['query_var_string'] = $pod['type'] . '_' . $pod['query_var_string'];
		}

		if ( isset( $pod['query_var'] ) && in_array( $pod['query_var'], $reserved_query_vars, true ) ) {
			$pod['query_var'] = $pod['type'] . '_' . $pod['query_var'];
		}

		// Solve custom rewrite slug common misconfiguration issues.
		if ( ! empty( $pod['rewrite_custom_slug'] ) ) {
			$pod['rewrite_custom_slug'] = trim( $pod['rewrite_custom_slug'] );

			if ( '/' === $pod['rewrite_custom_slug'] ) {
				$pod['rewrite_custom_slug'] = '';
			}
		}

		if ( '' === $pod['label'] ) {
			$pod['label'] = $pod['name'];
		}

		if ( 'post_type' === $pod['type'] ) {
			// Max length for post types are 20 characters
			$pod['name'] = substr( $pod['name'], 0, 20 );
		} elseif ( 'taxonomy' === $pod['type'] ) {
			// Max length for taxonomies are 32 characters
			$pod['name'] = substr( $pod['name'], 0, 32 );
		}

		$params->id   = $pod['id'];
		$params->name = $pod['name'];

		if ( null !== $old_name && $old_name !== $params->name && empty( $pod['object'] ) ) {
			if ( 'post_type' === $pod['type'] ) {
				$check = get_post_type_object( $params->name );

				if ( ! empty( $check ) ) {
					return pods_error( sprintf( __( 'Post Type %1$s already exists, you cannot rename %2$s to that', 'pods' ), $params->name, $old_name ), $this );
				}
			} elseif ( 'taxonomy' === $pod['type'] ) {
				$check = get_taxonomy( $params->name );

				if ( ! empty( $check ) ) {
					return pods_error( sprintf( __( 'Taxonomy %1$s already exists, you cannot rename %2$s to that', 'pods' ), $params->name, $old_name ), $this );
				}
			}
		}

		$field_table_operation = true;

		// Add new pod
		if ( empty( $params->id ) ) {
			if ( '' === $params->name ) {
				return pods_error( __( 'Pod name cannot be empty', 'pods' ), $this );
			}

			$post_data = array(
				'post_name'    => $pod['name'],
				'post_title'   => $pod['label'],
				'post_content' => $pod['description'],
				'post_type'    => '_pods_pod',
				'post_status'  => 'publish',
			);

			$save_groups_for_pod = false;

			if ( empty( $pod['groups'] ) || ! is_array( $pod['groups'] ) ) {
				$default_group_label  = __( 'More Fields', 'pods' );
				$default_group_fields = [];

				// Advanced Content Types have default fields.
				if ( 'pod' === $pod['type'] ) {
					$default_group_label  = __( 'Details', 'pods' );
					$default_group_fields = [
						'name'      => [
							'name'     => 'name',
							'label'    => 'Name',
							'type'     => 'text',
							'required' => '1',
						],
						'created'   => [
							'name'                 => 'created',
							'label'                => 'Date Created',
							'type'                 => 'datetime',
							'datetime_format'      => 'ymd_slash',
							'datetime_time_type'   => '12',
							'datetime_time_format' => 'h_mm_ss_A',
						],
						'modified'  => [
							'name'                 => 'modified',
							'label'                => 'Date Modified',
							'type'                 => 'datetime',
							'datetime_format'      => 'ymd_slash',
							'datetime_time_type'   => '12',
							'datetime_time_format' => 'h_mm_ss_A',
						],
						'author'    => [
							'name'               => 'author',
							'label'              => 'Author',
							'type'               => 'pick',
							'pick_object'        => 'user',
							'pick_format_type'   => 'single',
							'pick_format_single' => 'autocomplete',
							'default_value'      => '{@user.ID}',
						],
						'permalink' => [
							'name'        => 'permalink',
							'label'       => 'Permalink',
							'type'        => 'slug',
							'description' => 'Leave blank to auto-generate from Name',
						],
					];

					if ( ! isset( $pod['pod_index'] ) ) {
						$pod['pod_index'] = 'name';
					}
				}

				/**
				 * Filter the title of the Pods Metabox used in the post editor.
				 *
				 * @since unknown
				 *
				 * @param string  $title  The title to use, default is 'More Fields'.
				 * @param array   $pod    The Pods config data.
				 * @param array   $fields Array of fields that will go in the metabox.
				 * @param string  $type   The type of Pod.
				 * @param string  $name   Name of the Pod.
				 */
				$default_group_label = apply_filters( 'pods_meta_default_box_title', $default_group_label, $pod, $default_group_fields, $pod['type'], $pod['name'] );
				$default_group_name  = sanitize_key( pods_js_name( sanitize_title( $default_group_label ) ) );

				if ( ! empty( $default_group_fields ) ) {
					$save_groups_for_pod = true;

					$pod['groups'] = [
						$default_group_name => [
							'name'   => $default_group_name,
							'label'  => $default_group_label,
							'fields' => $default_group_fields,
						],
					];
				}
			}

			$pod = $this->do_hook( 'save_pod_default_pod', $pod, $params, $sanitized, $db );

			// Maybe save default groups and fields.
			if ( $save_groups_for_pod && ! empty( $pod['groups'] ) ) {
				$params->groups = $pod['groups'];
			}

			$field_table_operation = false;
		} else {
			$post_data = array(
				'ID'           => $pod['id'],
				'post_name'    => $pod['name'],
				'post_title'   => $pod['label'],
				'post_content' => $pod['description'],
				'post_status'  => 'publish'
			);
		}

		/**
		 * Allow filtering the Pod config data before saving the options.
		 *
		 * @since 2.8.0
		 *
		 * @param array  $pod       The Pod config data to be used for saving groups/fields.
		 * @param object $params    The list of parameters used to save this pod.
		 * @param bool   $sanitized Whether the data was sanitized already.
		 * @param bool   $db        Whether to save the data to the database.
		 */
		$pod = apply_filters( 'pods_api_save_pod_config_data', $pod, $params, $sanitized, $db );

		$meta = $pod;

		if ( $pod instanceof Pod ) {
			$meta = $pod->get_args();
		}

		$excluded_meta = array(
			'id',
			'name',
			'label',
			'description',
			'weight',
			'options',
			'fields',
			'group',
			'groups',
			'object_fields',
			'object_type',
			'object_storage_type',
			'old_name',
		);

		foreach ( $excluded_meta as $meta_key ) {
			if ( isset( $meta[ $meta_key ] ) ) {
				unset( $meta[ $meta_key ] );
			}
		}

		/**
		 * Allow filtering the Pod object data before saving.
		 *
		 * @since 2.8.0
		 *
		 * @param array  $post_data The Pod object data to be saved.
		 * @param array  $pod       The Pod config data.
		 * @param object $params    The list of parameters used to save this pod.
		 * @param bool   $sanitized Whether the data was sanitized already.
		 * @param bool   $db        Whether to save the data to the database.
		 */
		$post_data = apply_filters( 'pods_api_save_pod_post_data', $post_data, $pod, $params, $sanitized, $db );

		/**
		 * Allow filtering the Pod config data before saving the options.
		 *
		 * @since 2.8.0
		 *
		 * @param array  $meta      The Pod meta data to be saved.
		 * @param array  $pod       The Pod config data.
		 * @param object $params    The list of parameters used to save this pod.
		 * @param bool   $sanitized Whether the data was sanitized already.
		 * @param bool   $db        Whether to save the data to the database.
		 */
		$meta = apply_filters( 'pods_api_save_pod_meta_data', $meta, $pod, $params, $sanitized, $db );

		if ( true === $db ) {
			if ( ! has_filter( 'wp_unique_post_slug', array( $this, 'save_slug_fix' ) ) ) {
				add_filter( 'wp_unique_post_slug', array( $this, 'save_slug_fix' ), 100, 6 );
			}

			$conflicted = false;

			// Headway compatibility fix
			if ( has_filter( 'wp_insert_post_data', 'headway_clean_slug', 0 ) ) {
				remove_filter( 'wp_insert_post_data', 'headway_clean_slug', 0 );

				$conflicted = true;
			}

			$params->id = $this->save_wp_object( 'post', $post_data, $meta, true, true );

			if ( $conflicted ) {
				add_filter( 'wp_insert_post_data', 'headway_clean_slug', 0 );
			}

			if ( false === $params->id ) {
				return pods_error( __( 'Cannot save Pod', 'pods' ), $this );
			}
		} elseif ( empty( $params->id ) ) {
			$params->id = (int) $db;
		}

		$pod['id'] = $params->id;

		$all_fields = [];

		if ( ! empty( $pod['fields'] ) ) {
			$all_fields = (array) $pod['fields'];
		} elseif ( ! empty( $pod['groups'] ) ) {
			$all_fields = wp_list_pluck( array_values( $pod['groups'] ), 'fields' );

			if ( ! empty( $all_fields ) ) {
				$all_fields = array_merge( ...$all_fields );
			} else {
				$all_fields = [];
			}
		}

		// Maybe save the pod table schema.
		if ( $db ) {
			$old_info = compact(
				'old_storage',
				'old_name'
			);

			$this->save_pod_table_schema( $pod, $all_fields, $old_info );
		}

		// Maybe handle renaming.
		if ( $db && $pod['name'] !== $old_name ) {
			$this->save_pod_handle_rename( $pod, $old_name );
		}

		// Maybe sync built-in options for post type and taxonomies.
		if ( $db && empty( $pod['object'] ) ) {
			$this->save_pod_handle_sync_built_in( $pod );
		}

		$saved  = array();
		$errors = array();

		$id_required = false;

		// Save the object to the collection.
        $object_collection = Pods\Whatsit\Store::get_instance();

        /** @var Pods\Whatsit\Storage\Post_Type $post_type_storage */
        $post_type_storage = $object_collection->get_storage_object( $this->get_default_object_storage_type() );

        $object = $post_type_storage->to_object( $pod['id'], true );

        if ( ! $object ) {
        	$errors[] = __( 'Cannot save pod to collection', 'pods' );
        }

		if ( ! empty( $errors ) ) {
			return pods_error( $errors, $this );
		}

		$field_index        = pods_v( 'pod_index', $pod, 'id', true );
		$field_index_id     = 0;
		$field_index_change = false;

		if ( 'pod' === $pod['type'] && isset( $all_fields[ $field_index ] ) ) {
			$field_index_id = $all_fields[ $field_index ];

			if ( is_array( $field_index_id ) && ! empty( $field_index_id['id'] ) ) {
				$field_index_id = $field_index_id['id'];
			}
		}

		$fields_to_save = [];

		if ( ! empty( $params->fields ) ) {
			$params->fields = (array) $params->fields;

			$weight = 0;

			// Handle weight of fields.
			foreach ( $params->fields as $field ) {
				$is_field_object = $field instanceof Field;

				if ( ! $is_field_object && ! is_array( $field ) && empty( $field['name'] ) ) {
					continue;
				}

				if ( ! isset( $field['weight'] ) ) {
					$field['weight'] = $weight;

					$weight ++;
				}

				$fields_to_save[ $field['name'] ] = $field;
			}
		} elseif ( ! empty( $params->groups ) ) {
			$params->groups = (array) $params->groups;

			$group_weight = 0;

			// Handle saving of groups.
			foreach ( $params->groups as $group ) {
				if ( ! ( is_array( $group ) || $group instanceof Pods\Whatsit ) || ! isset( $group['name'] ) ) {
					continue;
				}

				$group_to_save = $group;

				// Normalize as an array if an object.
				if ( $group instanceof Pods\Whatsit ) {
					$group_to_save = $group->get_args();
				}

				if ( ! isset( $group_to_save['weight'] ) ) {
					$group_to_save['weight'] = $group_weight;

					$group_weight ++;
				}

				$group_to_save['pod']       = $object;
				$group_to_save['overwrite'] = $params->overwrite;

				$group_fields = [];

				if ( isset( $group_to_save['fields'] ) ) {
					$group_fields = $group_to_save['fields'];

					unset( $group_to_save['fields'] );
				}

				$group['id'] = $this->save_group( $group_to_save, $sanitized, $db );

				if ( ! empty( $group_fields ) ) {
					$weight = 0;

					// Handle weight of fields.
					foreach ( $group_fields as $field ) {
						$is_field_object = $field instanceof Field;

						if ( ! $is_field_object && ! is_array( $field ) && empty( $field['name'] ) ) {
							continue;
						}

						// Set the parent.
						$field['pod']   = $object;

						if ( $group instanceof Pods\Whatsit ) {
							$field['group'] = $group;
						} else {
							$field['group_id'] = $group['id'];
						}

						if ( ! isset( $field['weight'] ) ) {
							$field['weight'] = $weight;

							$weight ++;
						}

						$fields_to_save[ $field['name'] ] = $field;
					}
				}
			}
		}

		if ( $fields_to_save || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || ! empty( $params->overwrite ) ) {
			$saved_field_ids = array();

			$fields_to_save = $fields_to_save;

			$weight = 0;

			foreach ( $fields_to_save as $k => $field ) {
				$is_field_object = $field instanceof Field;

				$is_field_ok = (
					is_array( $field )
					|| $is_field_object
				);

				if (
					! empty( $old_id )
					&& (
						! $is_field_ok
						|| ! isset( $field['name'], $fields_to_save[ $field['name'] ] )
					)
				) {
					// Iterative change handling for setup-edit.php
					if ( ! $is_field_ok && isset( $old_fields[ $k ] ) ) {
						$saved[ $old_fields[ $k ]['name'] ] = true;
					}

					continue;
				}

				if ( ! empty( $old_id ) ) {
					$field_data = $fields_to_save[ $field['name'] ];

					/** @noinspection SlowArrayOperationsInLoopInspection */
					$field = pods_config_merge_data( $field, $field_data );
				}

				$field['pod_data'] = $object;

				if ( ! isset( $field['weight'] ) ) {
					$field['weight'] = $weight;

					$weight ++;
				}

				if ( 0 < $field_index_id && (int) pods_v( 'id', $field ) === $field_index_id ) {
					$field_index_change = $field['name'];
				}

				if ( 0 < pods_v( 'id', $field ) ) {
					$id_required = true;
				}

				if ( $id_required ) {
					$field['id_required'] = true;
				}

				$field_data = $field;

				$field = $this->save_field( $field_data, $field_table_operation, $sanitized, $db );

				if ( true !== $db ) {
					$fields_to_save[ $k ] = $field;
					$saved_field_ids[]   = $field['id'];
				} elseif ( ! empty( $field ) && 0 < $field ) {
					$saved[ $field_data['name'] ] = true;
					$saved_field_ids[]            = $field;
				} else {
					$errors[] = sprintf( __( 'Cannot save the %s field', 'pods' ), $field_data['name'] );
				}
			}

			if ( true === $db ) {
				foreach ( $old_fields as $field ) {
					if ( isset( $fields_to_save[ $field['name'] ] ) || isset( $saved[ $field['name'] ] ) || in_array( $field['id'], $saved_field_ids ) ) {
						continue;
					}

					if ( $field['id'] === (int) $field_index_id ) {
						$field_index_change = 'id';
					} elseif ( $field['name'] === $field_index ) {
						$field_index_change = 'id';
					}

					$this->delete_field( array(
						'id'   => (int) $field['id'],
						'name' => $field['name'],
						'pod'  => $pod
					), $field_table_operation );
				}
			}

			// Update field index if the name has changed or the field has been removed
			if ( false !== $field_index_change && true === $db ) {
				update_post_meta( $pod['id'], 'pod_index', $field_index_change );
			}
		}

		if ( is_array( $order_group_fields ) && ! empty( $order_group_fields['groups'] ) ) {
			$this->save_pod_group_field_order( $order_group_fields['groups'], $object, $db );
		}

		$this->cache_flush_pods( $pod );

		if ( ! empty( $errors ) ) {
			return pods_error( $errors, $this );
		}

		$refresh_pod = $this->load_pod( array( 'name' => $pod['name'] ), false );

		if ( $refresh_pod ) {
			$pod = $refresh_pod;
		}

		if ( 'post_type' === $pod['type'] ) {
			PodsMeta::$post_types[ $pod['id'] ] = $pod;
		} elseif ( 'taxonomy' === $pod['type'] ) {
			PodsMeta::$taxonomies[ $pod['id'] ] = $pod;
		} elseif ( 'media' === $pod['type'] ) {
			PodsMeta::$media[ $pod['id'] ] = $pod;
		} elseif ( 'user' === $pod['type'] ) {
			PodsMeta::$user[ $pod['id'] ] = $pod;
		} elseif ( 'comment' === $pod['type'] ) {
			PodsMeta::$comment[ $pod['id'] ] = $pod;
		}

		if ( ! class_exists( 'PodsInit' ) ) {
			pods_init();
		}

		// Register Post Types / Taxonomies post-registration from PodsInit
		if (
			! empty( PodsInit::$content_types_registered )
			&& in_array( $pod['type'], array(
		     	'post_type',
				'taxonomy'
			), true )
			&& (
				! $pod instanceof Pod
				|| ! $pod->is_extended()
			)
		) {
			pods_init()->setup_content_types( true );
		}

		if ( true === $db ) {
			return $pod['id'];
		} else {
			return $pod;
		}
	}

	/**
	 * Handle saving the pod table schema.
	 *
	 * @since 2.8.0
	 *
	 * @param array $pod      The pod configuration.
	 * @param array $fields   The list of fields on the pod.
	 * @param array $old_info The old information to reference.
	 *
	 * @return bool|WP_Error True if the schema changes were handled, false or an error if it failed to create/update.
	 *
	 * @throws Exception
	 */
	public function save_pod_table_schema( $pod, array $fields, array $old_info ) {
		global $wpdb;

		$tableless_field_types    = PodsForm::tableless_field_types();
		$simple_tableless_objects = PodsForm::simple_tableless_objects();

		$old_storage = $old_info['old_storage'];
		$old_name    = $old_info['old_name'];

		// Skip custom mapped table pods.
		if ( 'table' === $pod['type'] || ! empty( $pod['table'] ) ) {
			return;
		}

		// Skip if not using table storage.
		if ( isset( $pod['storage'] ) && 'table' !== $pod['storage'] ) {
			return;
		}

		$table_name     = "@wp_pods_{$pod['name']}";
		$old_table_name = "@wp_pods_{$old_name}";

		if ( $old_storage !== $pod['storage'] ) {
			// Create the table if it wasn't there before.
			$definitions = [
				'`id` BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY',
			];

			$defined_fields = [];

			foreach ( $fields as $field ) {
				$is_field_object = $field instanceof Field;

				// Skip if not a field, if an invalid field, or if already defined.
				if (
					! (
						is_array( $field )
					    || $is_field_object
					)
					|| ! isset( $field['name'] )
					|| in_array( $field['name'], $defined_fields, true )
				) {
					continue;
				}

				$defined_fields[] = $field['name'];

				// Skip if we are not defining tableless fields and it is a tableless field or not a simple tableless object.
				$definition = $this->get_field_definition( $field['type'], $field );

				if ( $definition && '' !== $definition ) {
					$definitions[] = "`{$field['name']}` " . $definition;
				}
			}

			// Drop the table if it already exists.
			pods_query( "DROP TABLE IF EXISTS `{$table_name}`" );

			/**
			 * @see  PodsUpgrade::install() L64-L76
			 * @todo Central function to fetch charset.
			 */
			$charset_collate = 'DEFAULT CHARSET utf8';

			if ( ! empty( $wpdb->charset ) ) {
				$charset_collate = "DEFAULT CHARSET {$wpdb->charset}";
			}

			if ( ! empty( $wpdb->collate ) ) {
				$charset_collate .= " COLLATE {$wpdb->collate}";
			}

			if ( empty( $definitions ) ) {
				return pods_error( __( 'Cannot add Database Table for Pod, no table column definitions provided', 'pods' ), $this );
			}

			$all_definitions = implode( ', ', $definitions );

			$result = pods_query( "CREATE TABLE `{$table_name}` ({$all_definitions}) {$charset_collate}", $this );

			if ( empty( $result ) ) {
				return pods_error( __( 'Cannot add Database Table for Pod', 'pods' ), $this );
			}
		} elseif ( null !== $old_name && $old_name !== $pod['name'] ) {
			// Rename the table.
			$result = pods_query( "ALTER TABLE `{$old_table_name}` RENAME `{$table_name}`", $this );

			if ( empty( $result ) ) {
				return pods_error( __( 'Cannot update Database Table for Pod', 'pods' ), $this );
			}
		}

		/**
		 * Allow hooking after the table schema has been created or the table has been renamed.
		 *
		 * @since 2.8.0
		 *
		 * @param array $pod      The pod configuration.
		 * @param array $fields   The list of fields on the pod.
		 * @param array $old_info The old information to reference.
		 */
		do_action( 'pods_api_save_pod_table_schema_after', $pod, $fields, $old_info );

		return true;
	}

	/**
	 * Handle saving the pod table schema.
	 *
	 * @since 2.8.0
	 *
	 * @param array  $pod      The pod configuration.
	 * @param string $old_name The old pod name.
	 *
	 * @return bool Whether the pod was successfully renamed.
	 *
	 * @throws Exception
	 */
	public function save_pod_handle_rename( $pod, $old_name ) {
		global $wpdb;

		$pod_name   = sanitize_key( $pod['name'] );
		$pod_id     = (int) $pod['id'];
		$pod_type   = pods_sanitize( $pod['type'] );
		$has_object = ! empty( $pod['object'] );
		$old_name   = sanitize_key( $old_name );

		// Skip if the name did not change.
		if ( $pod_name === $old_name ) {
			return false;
		}

		// Skip if either name is empty.
		if ( empty( $pod_name ) || empty( $old_name ) ) {
			return false;
		}

		// Rename items in the DB pointed at the old WP Object names.
		if ( 'post_type' === $pod_type && ! $has_object ) {
			$this->rename_wp_object_type( 'post', $old_name, $pod_name );
		} elseif ( 'taxonomy' === $pod_type && ! $has_object ) {
			$this->rename_wp_object_type( 'taxonomy', $old_name, $pod_name );
		} elseif ( 'comment' === $pod_type && ! $has_object ) {
			$this->rename_wp_object_type( 'comment', $old_name, $pod_name );
		} elseif ( 'settings' === $pod_type ) {
			$this->rename_wp_object_type( 'settings', $old_name, $pod_name );
		}

		$field_ids_to_sync = [];

		// Sync any related fields if the name has changed
		$field_ids_to_sync[] = pods_query(
			"
				SELECT `p`.`ID`
				FROM `{$wpdb->posts}` AS `p`
				LEFT JOIN `{$wpdb->postmeta}` AS `pm` ON `pm`.`post_id` = `p`.`ID`
				LEFT JOIN `{$wpdb->postmeta}` AS `pm2` ON `pm2`.`post_id` = `p`.`ID`
				WHERE
					`p`.`post_type` = '_pods_field'
					AND `p`.`post_parent` != {$pod_id}
					AND `pm`.`meta_key` = 'pick_object'
					AND (
						`pm`.`meta_value` = 'pod'
						OR `pm`.`meta_value` = '{$pod_type}'
					)
					AND `pm2`.`meta_key` = 'pick_val'
					AND `pm2`.`meta_value` = '{$old_name}'
			"
		);

		$field_ids_to_sync[] = pods_query(
			"
				SELECT `p`.`ID`
				FROM `{$wpdb->posts}` AS `p`
				LEFT JOIN `{$wpdb->postmeta}` AS `pm` ON `pm`.`post_id` = `p`.`ID`
				WHERE
					`p`.`post_type` = '_pods_field'
					AND `p`.`post_parent` != {$pod_id}
					AND `pm`.`meta_key` = 'pick_object'
					AND (
						`pm`.`meta_value` = 'pod-{$old_name}'
						OR `pm`.`meta_value` = '{$pod_type}-{$old_name}'
					)
			"
		);

		$field_ids_to_sync = array_merge( ...$field_ids_to_sync );
		$field_ids_to_sync = array_map( static function( $field_to_sync ) {
			return $field_to_sync->ID;
		}, $field_ids_to_sync );
		$field_ids_to_sync = array_unique( array_filter( $field_ids_to_sync ) );

		// Update the field configurations for any related fields that changed.
		if ( ! empty( $field_ids_to_sync ) ) {
			foreach ( $field_ids_to_sync as $field_id_to_sync ) {
				$found_field = $this->load_field( [
					'id' => $field_id_to_sync,
				] );

				// Field not found.
				if ( ! $found_field ) {
					continue;
				}

				// Save new location.
				$found_field['pick_object'] = $pod_type;
				$found_field['pick_val']    = $pod_name;

				$this->save_field( $found_field );
			}
		}

		/**
		 * Allow hooking after the pod has been renamed.
		 *
		 * @since 2.8.0
		 *
		 * @param array  $pod      The pod configuration.
		 * @param string $old_name The old pod name.
		 */
		do_action( 'pods_api_save_pod_handle_rename_after', $pod, $old_name );

		return true;
	}

	/**
	 * Handle syncing the built-in post type / taxonomy options.
	 *
	 * @since 2.8.0
	 *
	 * @param array $pod The pod configuration.
	 *
	 * @return bool Whether the sync was successful.
	 */
	public function save_pod_handle_sync_built_in( $pod ) {
		global $wpdb;

		if ( ! empty( $pod['object'] ) || ! in_array( $pod['type'], array( 'post_type', 'taxonomy' ), true ) ) {
			return false;
		}

		// Build list of 'built_in' for later.
		$built_in = array();

		$options = $pod;

		if ( is_object( $options ) ) {
			$options = $pod->get_args();
		}

		foreach ( $options as $key => $val ) {
			if ( false === strpos( $key, 'built_in_' ) ) {
				continue;
			}

			if ( false !== strpos( $key, 'built_in_post_types_' ) ) {
				$built_in_type = 'post_type';
			} elseif ( false !== strpos( $key, 'built_in_taxonomies_' ) ) {
				$built_in_type = 'taxonomy';
			} else {
				continue;
			}

			// The built in type is the same as this pod type.
			if ( $pod['type'] === $built_in_type ) {
				continue;
			}

			if ( ! isset( $built_in[ $built_in_type ] ) ) {
				$built_in[ $built_in_type ] = array();
			}

			$built_in_object = str_replace( array( 'built_in_post_types_', 'built_in_taxonomies_' ), '', $key );

			$built_in[ $built_in_type ][ $built_in_object ] = (int) $val;
		}

		$lookup_option   = false;
		$lookup_built_in = false;

		$lookup_name = $pod['name'];

		if ( 'post_type' === $pod['type'] ) {
			$lookup_option   = 'built_in_post_types_' . $lookup_name;
			$lookup_built_in = 'taxonomy';
		} elseif ( 'taxonomy' === $pod['type'] ) {
			$lookup_option   = 'built_in_taxonomies_' . $lookup_name;
			$lookup_built_in = 'post_type';
		}

		// The built in options were not found.
		if ( empty( $lookup_option ) || empty( $lookup_built_in ) || ! isset( $built_in[ $lookup_built_in ] ) ) {
			return false;
		}

		foreach ( $built_in[ $lookup_built_in ] as $built_in_object => $val ) {
			$search_val = 1;

			if ( 1 === (int) $val ) {
				$search_val = 0;
			}

			$built_in_object = pods_sanitize( $built_in_object );
			$lookup_option   = pods_sanitize( $lookup_option );
			$lookup_built_in = pods_sanitize( $lookup_built_in );

			$query = "SELECT p.ID FROM {$wpdb->posts} AS p
						LEFT JOIN {$wpdb->postmeta} AS pm ON pm.post_id = p.ID AND pm.meta_key = '{$lookup_option}'
						LEFT JOIN {$wpdb->postmeta} AS pm2 ON pm2.post_id = p.ID AND pm2.meta_key = 'type' AND pm2.meta_value = '{$lookup_built_in}'
						LEFT JOIN {$wpdb->postmeta} AS pm3 ON pm3.post_id = p.ID AND pm3.meta_key = 'object' AND pm3.meta_value = ''
						WHERE p.post_type = '_pods_pod' AND p.post_name = '{$built_in_object}'
							AND pm2.meta_id IS NOT NULL
							AND ( pm.meta_id IS NULL OR pm.meta_value = {$search_val} )";

			$results = pods_query( $query );

			if ( ! empty( $results ) ) {
				foreach ( $results as $the_pod ) {
					delete_post_meta( $the_pod->ID, $lookup_option );

					add_post_meta( $the_pod->ID, $lookup_option, $val );
				}
			}
		}

		return true;
	}

	/**
	 * Handle saving the groups/fields order for a pod.
	 *
	 * @since 2.8.0
	 *
	 * @param array    $groups List of group IDs and their fields to reorder.
	 * @param Pod      $object The pod object.
	 * @param bool|int $db     (optional) Whether to save into the DB or just return group array.
	 *
	 * @throws Exception
	 */
	public function save_pod_group_field_order( $groups, $object, $db = true ) {
		$group_order = 0;

		foreach ( $groups as $group ) {
			if ( ! is_array( $group ) || empty( $group['group_id'] ) ) {
				continue;
			}

			$group_id = (int) $group['group_id'];

			$this->save_group( [
				'pod_data' => $object,
				'id'       => $group_id,
				'weight'   => $group_order,
			], false, $db );

			$group_order ++;

			if ( empty( $group['fields'] ) ) {
				continue;
			}

			$group_field_order = 0;

			foreach ( $group['fields'] as $field_id ) {
				$this->save_field( [
					'pod_data'     => $object,
					'id'           => (int) $field_id,
					'new_group_id' => $group_id,
					'weight'       => $group_field_order,
				], false, false, $db );

				$group_field_order ++;
			}
		}
	}

	/**
	 * Add field within a Pod
	 *
	 * $params['id'] int Field ID (id OR pod_id+pod+name required)
	 * $params['pod_id'] int Pod ID (id OR pod_id+pod+name required)
	 * $params['pod'] string Pod name (id OR pod_id+pod+name required)
	 * $params['name'] string Field name (id OR pod_id+pod+name required)
	 * $params['label'] string (optional) Field label
	 * $params['type'] string (optional) Field type (avatar, boolean, code, color, currency, date, datetime, email,
	 * file, number, paragraph, password, phone, pick, slug, text, time, website, wysiwyg)
	 * $params['pick_object'] string (optional) Related Object (for relationships)
	 * $params['pick_val'] string (optional) Related Object name (for relationships)
	 * $params['sister_id'] int (optional) Related Field ID (for bidirectional relationships)
	 * $params['weight'] int (optional) Order in which the field appears
	 *
	 * @param array    $params          An associative array of parameters
	 * @param bool     $table_operation (optional) Whether or not to handle table operations
	 * @param bool     $sanitized       (optional) Decides whether the params have been sanitized before being passed,
	 *                                  will sanitize them if false.
	 * @param bool|int $db              (optional) Whether to save into the DB or just return field array.
	 *
	 * @return int|array The field ID or field array (if !$db)
	 *
	 * @since 2.8.0
	 */
	public function add_field( $params, $table_operation = true, $sanitized = false, $db = true ) {
		$params = (object) $params;

		$params->is_new    = true;
		$params->overwrite = false;

		return $this->save_field( $params, $table_operation, $sanitized, $db );
	}

	/**
	 * Add or edit a field within a Pod
	 *
	 * $params['id'] int Field ID (id OR pod_id+pod+name required)
	 * $params['pod_id'] int Pod ID (id OR pod_id+pod+name required)
	 * $params['pod'] string Pod name (id OR pod_id+pod+name required)
	 * $params['name'] string Field name (id OR pod_id+pod+name required)
	 * $params['label'] string (optional) Field label
	 * $params['type'] string (optional) Field type (avatar, boolean, code, color, currency, date, datetime, email,
	 * file, number, paragraph, password, phone, pick, slug, text, time, website, wysiwyg)
	 * $params['pick_object'] string (optional) Related Object (for relationships)
	 * $params['pick_val'] string (optional) Related Object name (for relationships)
	 * $params['sister_id'] int (optional) Related Field ID (for bidirectional relationships)
	 * $params['weight'] int (optional) Order in which the field appears
	 *
	 * @param array|Field $params          An associative array of parameters
	 * @param bool        $table_operation (optional) Whether or not to handle table operations
	 * @param bool        $sanitized       (optional) Decides whether the params have been sanitized before being passed,
	 *                                     will sanitize them if false.
	 * @param bool|int    $db              (optional) Whether to save into the DB or just return field array.
	 *
	 * @return int|array The field ID or field array (if !$db)
	 * @since 1.7.9
	 */
	public function save_field( $params, $table_operation = true, $sanitized = false, $db = true ) {
		if ( $params instanceof Field ) {
			$params = [
				'id'    => $params->get_id(),
				'field' => $params,
			];
		}

		$params = (object) $params;

		/** @var false|array|Field $field */
		$field = false;

		if ( isset( $params->field ) && $params->field instanceof Field ) {
			$field = $params->field;

			$params->id = $field->get_id();

			if ( ! isset( $params->pod_id ) ) {
				$params->pod_id = $field->get_parent_id();
			}

			if ( ! isset( $params->group_id ) ) {
				$params->group_id = $field->get_group_id();
			}

			unset( $params->field );
		}

		/**
		 * @var $wpdb wpdb
		 */
		global $wpdb;

		if ( true !== $db ) {
			$table_operation = false;
		}

		$tableless_field_types    = PodsForm::tableless_field_types();
		$simple_tableless_objects = PodsForm::simple_tableless_objects();

		$pod      = null;
		$save_pod = false;

		if ( isset( $params->pod ) && $params->pod instanceof Pod ) {
			$pod = $params->pod;

			$params->pod_id = $pod['id'];
			$params->pod    = $pod['name'];
		} elseif ( isset( $params->pod_data ) ) {
			$pod = $params->pod_data;

			unset( $params->pod_data );

			$params->pod_id = $pod['id'];
			$params->pod    = $pod['name'];

			$save_pod = true;
		} elseif ( isset( $params->pod_id ) ) {
			$params->pod_id = pods_absint( $params->pod_id );
		} elseif ( true !== $db ) {
			$params->pod_id = (int) $db;
		}

		$group                = null;
		$new_group            = null;
		$group_identifier     = null;
		$new_group_identifier = null;

		if ( ! empty( $params->group_id ) ) {
			$group_identifier = 'ID: ' . $params->group_id;

			$group = $this->load_group( [
				'id'  => $params->group_id,
				'pod' => $pod,
			] );
		} elseif ( ! empty( $params->group ) ) {
			if ( $params->group instanceof Group ) {
				$group = $params->group;
			} else {
				$group_identifier = 'Slug: ' . $params->group;

				$group = $this->load_group( [
					'name' => $params->group,
					'pod'  => $pod,
				] );
			}
		}

		// Handle assigning to new groups.
		if ( ! empty( $params->new_group_id ) ) {
			$new_group_identifier = 'ID: ' . $params->new_group_id;

			$new_group = $this->load_group( [
				'id'  => $params->new_group_id,
				'pod' => $pod,
			] );

			unset( $params->new_group_id );
		} elseif ( ! empty( $params->new_group ) ) {
			if ( $params->new_group instanceof Group ) {
				$new_group = $params->new_group;
			} else {
				$new_group_identifier = 'Slug: ' . $params->new_group;

				$new_group = $this->load_group( [
					'name' => $params->new_group,
					'pod'  => $pod,
				] );
			}

			unset( $params->new_group );
		}

		if ( $group instanceof Group ) {
			$params->group_id = $group['id'];
			$params->group    = $group['name'];
		} elseif ( false === $group ) {
			return pods_error( sprintf( __( 'Group (%s) not found.', 'pods' ), $group_identifier ), $this );
		}

		if ( false === $new_group ) {
			return pods_error( sprintf( __( 'New group (%s) not found.', 'pods' ), $new_group_identifier ), $this );
		}

		if ( false === $sanitized ) {
			$params = pods_sanitize( $params );

			$sanitized = true;
		}

		$id_required = false;

		if ( isset( $params->id_required ) ) {
			unset( $params->id_required );

			$id_required = true;
		}

		if ( ! $pod && ( ! isset( $params->pod ) || empty( $params->pod ) ) && ( ! isset( $params->pod_id ) || empty( $params->pod_id ) ) ) {
			return pods_error( __( 'Pod ID or name is required', 'pods' ), $this );
		}

		if ( ! $pod ) {
			if ( isset( $params->pod ) && ( is_array( $params->pod ) || $params->pod instanceof Pods\Whatsit ) ) {
				$pod = $params->pod;

				$save_pod = true;
			} elseif ( ( ! isset( $params->pod_id ) || empty( $params->pod_id ) ) && ( true === $db || 0 < $db ) ) {
				$pod = $this->load_pod( array( 'name' => $params->pod ), false );
			} elseif ( ! isset( $params->pod ) && ( true === $db || 0 < $db ) ) {
				$pod = $this->load_pod( array( 'id' => $params->pod_id ), false );
			} elseif ( true === $db || 0 < $db ) {
				$pod = $this->load_pod( array( 'id' => $params->pod_id, 'name' => $params->pod ), false );
			}
		}

		if ( empty( $pod ) && true === $db ) {
			return pods_error( __( 'Pod not found', 'pods' ), $this );
		}

		$params->pod_id = $pod['id'];
		$params->pod    = $pod['name'];

		$params->is_new    = isset( $params->is_new ) ? (boolean) $params->is_new : false;
		$params->overwrite = isset( $params->overwrite ) ? (boolean) $params->overwrite : false;

		$reserved_keywords = pods_reserved_keywords( 'wp-post' );

		if ( isset( $params->name ) ) {
			$params->name = pods_clean_name( $params->name, true, 'meta' !== $pod['storage'] );

			if ( $params->is_new && isset( $params->id ) ) {
				$params->id = null;
			}
		}

		$field_obj = $field;

		$lookup_type = null;

		if ( ! $field ) {
			$load_params = [
				'pod_id' => $params->pod_id,
			];

			$fail_on_load = false;

			if ( ! empty( $params->id ) ) {
				$load_params['id'] = $params->id;

				$fail_on_load = true;

				$lookup_type = 'id';
			} elseif ( ! empty( $params->old_name ) ) {
				$load_params['name'] = $params->old_name;

				$fail_on_load = true;

				$lookup_type = 'old_name';
			} elseif ( ! empty( $params->name ) ) {
				$load_params['name'] = $params->name;

				$lookup_type = 'name';
			} elseif ( ! empty( $params->label ) ) {
				$load_params = false;
			} else{
				return pods_error( __( 'Pod field name or label is required', 'pods' ), $this );
			}

			if ( $load_params ) {
				$field_obj = $this->load_field( $load_params );

				if ( $fail_on_load && ( empty( $field_obj ) || is_wp_error( $field_obj ) ) ) {
					return $field_obj;
				}
			}
		}

		if ( $field_obj ) {
			$field = $field_obj->get_args();
		}

		if ( empty( $params->name ) ) {
			if ( $field ) {
				$params->name = $field['name'];
			} elseif ( ! empty( $params->label ) ) {
				$params->name = pods_clean_name( $params->label, true, 'meta' !== $pod['storage'] );
			} else {
				return pods_error( __( 'Pod field name or label is required', 'pods' ), $this );
			}
		}

		$old_id                = null;
		$old_name              = null;
		$old_type              = null;
		$old_definition        = null;
		$old_simple            = null;
		$old_options           = null;
		$old_sister_id         = null;
		$old_type_is_tableless = false;

		$act_safe_keywords = [
			'name',
			'author',
			'permalink',
			'slug',
		];

		$is_act = 'pod' === $pod->get_type();

		if ( ! empty( $field ) ) {
			$old_id        = pods_v( 'id', $field );
			$old_name      = pods_clean_name( $field['name'], true, 'meta' !== $pod['storage'] );
			$old_type      = $field['type'];
			$old_options   = $field;
			$old_sister_id = pods_v( 'sister_id', $old_options, 0 );

			// Maybe clone the field object if we need to.
			if ( $old_options instanceof Field ) {
				$old_options = clone $old_options;
			}

			// When renaming a field, use the old ID for reference if empty.
			if ( ( 'old_name' === $lookup_type || $params->overwrite ) && empty( $params->id ) ) {
				$params->id = $old_id;
			}

			if ( is_numeric( $old_sister_id ) ) {
				$old_sister_id = (int) $old_sister_id;
			} else {
				$old_sister_id = 0;
			}

			$old_simple = ( 'pick' === $old_type && in_array( pods_v( 'pick_object', $field ), $simple_tableless_objects, true ) );

			if ( isset( $params->new_name ) && ! empty( $params->new_name ) ) {
				$field['name'] = $params->new_name;

				unset( $params->new_name );
			} elseif ( isset( $params->name ) && ! empty( $params->name ) ) {
				$field['name'] = $params->name;
			}

			if ( $new_group && ( ! $group || $group->get_id() !== $new_group->get_id() ) ) {
				$field['group'] = $new_group->get_id();
			}

			if ( $old_name !== $field['name'] || empty( $params->id ) || $old_id !== $params->id ) {
				/*
				 * Prevent adding / renaming fields that conflict with taxonomy names. This is to prevent cases
				 * where someone has a field named as the taxonomy and then chooses to associate the taxonomy
				 * to the post type. That will then cause conflicts for the field because it then matches an
				 * object field.
				 */
				if ( 'post_type' === $pod['type'] && taxonomy_exists( $field['name'] ) ) {
					return pods_error( sprintf( __( '%s conflicts with the name of an existing Taxonomy, please try a different name', 'pods' ), $field['name'] ), $this );
				}

				if (
					in_array( $field['name'], $reserved_keywords, true )
					&& (
						! $is_act
						|| ! in_array( $field['name'], $act_safe_keywords, true )
					)
				) {
					return pods_error( sprintf( __( '%s is reserved for internal WordPress or Pods usage, please try a different name', 'pods' ), $field['name'] ), $this );
				}

				if ( false !== $this->field_exists( $params, false ) ) {
					return pods_error( sprintf( __( 'Field %1$s already exists, you cannot rename %2$s to that on the %3$s pod', 'pods' ), $field['name'], $old_name, $pod['name'] ), $this );
				}
			}

			if ( ( $id_required || ! empty( $params->id ) ) && ( empty( $old_id ) || $old_id !== $params->id ) ) {
				return pods_error( sprintf( __( 'Field %s already exists', 'pods' ), $field['name'] ), $this );
			}

			if ( empty( $params->id ) ) {
				$params->id = $old_id;
			}

			$field_definition      = $this->get_field_definition( $old_type, $old_options );
			$old_type_is_tableless = in_array( $old_type, $tableless_field_types, true );

			/**
			 * Allow filtering of the old field definition when saving updated field.
			 *
			 * @since 2.8.0
			 *
			 * @param string|false       $field_definition The SQL definition to use for the field's table column.
			 * @param string             $type             The field type.
			 * @param array              $old_options      The field data.
			 * @param bool               $simple           Whether the field is a simple tableless field.
			 * @param Pods\Whatsit\Field $field_obj        The field object.
			 */
			$field_definition = apply_filters( 'pods_api_save_field_old_definition', $field_definition, $old_type, $old_options, $old_simple, $field_obj );

			if ( ! empty( $field_definition ) ) {
				$old_definition = "`{$old_name}` " . $field_definition;
			}
		} else {
			$field = [
				'id'          => 0,
				'pod_id'      => $params->pod_id,
				'name'        => $params->name,
				'label'       => $params->name,
				'description' => '',
				'type'        => 'text',
				'pick_object' => '',
				'pick_val'    => '',
				'sister_id'   => '',
				'weight'      => null,
				'options'     => [],
			];

			if ( $group ) {
				$field['group'] = $group->get_id();
			} elseif ( $new_group ) {
				$field['group'] = $new_group->get_id();
			}
		}

		// Setup options
		$options = get_object_vars( $params );

		$options_ignore = [
			'_locale',
			'adhoc',
			'attributes',
			'dependency',
			'depends-on',
			'developer_mode',
			'excludes-on',
			'group',
			'group_id',
			'grouped',
			'is_new',
			'method',
			'object_type',
			'old_name',
			'parent',
			'pod_data',
			'sanitized',
			'object_storage_type',
			'table_info',
		];

		foreach ( $options_ignore as $ignore ) {
			if ( isset( $options[ $ignore ] ) ) {
				unset( $options[ $ignore ] );
			}
		}

		if ( isset( $options['method'] ) ) {
			unset( $options['method'] );
		} elseif ( isset( $options['table_info'] ) ) {
			unset( $options['table_info'] );
		}

		$exclude = [
			'_locale',
			'description',
			'group_id',
			'id',
			'is_new',
			'overwrite',
			'label',
			'name',
			'old_name',
			'options',
			'parent',
			'pick_object',
			'pick_val',
			'pod',
			'pod_id',
			'post_status',
			'sister_id',
			'type',
			'weight',
		];

		foreach ( $exclude as $k => $exclude_field ) {
			$aliases = array( $exclude_field );

			if ( is_array( $exclude_field ) ) {
				$aliases       = array_merge( array( $k ), $exclude_field );
				$exclude_field = $k;
			}

			foreach ( $aliases as $alias ) {
				if ( isset( $options[ $alias ] ) ) {
					$field[ $exclude_field ] = pods_trim( $options[ $alias ] );

					unset( $options[ $alias ] );
				}
			}
		}

		if ( '' === $field['label'] ) {
			$field['label'] = $field['name'];
		}

		$type_is_tableless = in_array( $field['type'], $tableless_field_types, true );

		if ( $type_is_tableless && 'pick' === $field['type'] ) {
			// Clean up special drop-down in field editor and save out pick_val
			$field['pick_object'] = pods_v( 'pick_object', $field, '', true );

			if ( 0 === strpos( $field['pick_object'], 'pod-' ) ) {
				$field['pick_val']    = pods_str_replace( 'pod-', '', $field['pick_object'], 1 );
				$field['pick_object'] = 'pod';
			} elseif ( 0 === strpos( $field['pick_object'], 'post_type-' ) ) {
				$field['pick_val']    = pods_str_replace( 'post_type-', '', $field['pick_object'], 1 );
				$field['pick_object'] = 'post_type';
			} elseif ( 0 === strpos( $field['pick_object'], 'taxonomy-' ) ) {
				$field['pick_val']    = pods_str_replace( 'taxonomy-', '', $field['pick_object'], 1 );
				$field['pick_object'] = 'taxonomy';
			} elseif ( 'table' === $field['pick_object'] && 0 < strlen( pods_v( 'pick_table', $field ) ) ) {
				$field['pick_val']    = $field['pick_table'];
				$field['pick_object'] = 'table';
			} elseif ( false === strpos( $field['pick_object'], '-' ) && ! in_array( $field['pick_object'], array(
					'pod',
					'post_type',
					'taxonomy'
				) ) ) {
				$field['pick_val'] = '';
			} elseif ( 'custom-simple' === $field['pick_object'] ) {
				$field['pick_val'] = '';
			}
		}

		foreach ( $options as $o => $v ) {
			$field[ $o ] = $v;
		}

		// Check for strict mode (default: strict).
		$strict_mode = ! defined( 'PODS_FIELD_STRICT' ) || PODS_FIELD_STRICT;

		$object_fields = (array) pods_v( 'object_fields', $pod, [], true );

		if ( 0 < $old_id && ! $strict_mode ) {
			$params->id  = $old_id;
			$field['id'] = $old_id;
		}

		// Add new field
		if ( ! isset( $params->id ) || empty( $params->id ) || empty( $field ) ) {
			if ( $table_operation && $strict_mode && in_array( $field['name'], [
					'created',
					'modified',
				], true ) && ! in_array( $field['type'], [
					'date',
					'datetime',
				], true ) ) {
				return pods_error( sprintf( __( '%s is reserved for internal Pods usage, please try a different name', 'pods' ), $field['name'] ), $this );
			}

			if ( $table_operation && $strict_mode && 'author' === $field['name'] && 'pick' !== $field['type'] ) {
				return pods_error( sprintf( __( '%s is reserved for internal Pods usage, please try a different name', 'pods' ), $field['name'] ), $this );
			}

			if (
				in_array( $field['name'], $reserved_keywords, true )
				&& (
					! $is_act
					|| ! in_array( $field['name'], $act_safe_keywords, true )
				)
			) {
				return pods_error( sprintf( __( '%s is reserved for internal WordPress or Pods usage, please try a different name', 'pods' ), $field['name'] ), $this );
			}

			foreach ( $object_fields as $object_field => $object_field_opt ) {
				if ( $object_field === $field['name'] || in_array( $field['name'], $object_field_opt['alias'], true ) ) {
					return pods_error( sprintf( __( '%s is reserved for internal WordPress or Pods usage, please try a different name. You may also want to consider what built-in functionality WordPress and Pods provides you.', 'pods' ), $field['name'] ), $this );
				}
			}

			 // Reserved post_name values that can't be used as field names
			if ( 'rss' === $field['name'] ) {
				$field['name'] .= '2';
			}

			if ( 'slug' === $field['type'] && true === $db ) {
				if ( in_array( $pod['type'], array( 'post_type', 'taxonomy', 'user' ) ) ) {
					return pods_error( __( 'This pod already has an internal WordPress permalink field', 'pods' ), $this );
				}

				$slug_field = get_posts( array(
					'post_type'      => '_pods_field',
					'orderby'        => 'menu_order',
					'order'          => 'ASC',
					'posts_per_page' => 1,
					'post_parent'    => $field['pod_id'],
					'meta_query'     => array(
						array(
							'key'   => 'type',
							'value' => 'slug'
						)
					)
				) );

				if ( ! empty( $slug_field ) ) {
					return pods_error( __( 'This pod already has a permalink field', 'pods' ), $this );
				}
			}

			// Sink the new field to the bottom of the list
			if ( null === $field['weight'] ) {
				$field['weight'] = 0;

				$bottom_most_field = get_posts( array(
					'post_type'      => '_pods_field',
					'orderby'        => 'menu_order',
					'order'          => 'DESC',
					'posts_per_page' => 1,
					'post_parent'    => $field['pod_id']
				) );

				if ( ! empty( $bottom_most_field ) ) {
					$field['weight'] = pods_absint( $bottom_most_field[0]->menu_order ) + 1;
				}
			}

			$field['weight'] = pods_absint( $field['weight'] );

			$post_data = array(
				'post_name'    => $field['name'],
				'post_title'   => $field['label'],
				'post_content' => $field['description'],
				'post_type'    => '_pods_field',
				'post_parent'  => $field['pod_id'],
				'post_status'  => 'publish',
				'menu_order'   => $field['weight']
			);
		} else {
			if ( in_array( $field['name'], array( 'id', 'ID' ) ) ) {
				if ( null !== $old_name ) {
					return pods_error( sprintf( __( '%s is reserved for internal Pods usage, please try a different name', 'pods' ), $field['name'] ), $this );
				} else {
					return pods_error( sprintf( __( '%s is not editable', 'pods' ), $field['name'] ), $this );
				}
			}

			if ( $strict_mode && null !== $old_name && $field['name'] !== $old_name ) {
				if ( in_array( $field['name'], [
						'created',
						'modified',
					] ) && ! in_array( $field['type'], [
						'date',
						'datetime',
					] ) ) {
					return pods_error( sprintf( __( '%s is reserved for internal Pods usage, please try a different name', 'pods' ), $field['name'] ), $this );
				}

				if ( 'author' === $field['name'] && 'pick' !== $field['type'] ) {
					return pods_error( sprintf( __( '%s is reserved for internal Pods usage, please try a different name', 'pods' ), $field['name'] ), $this );
				}
			}

			foreach ( $object_fields as $object_field => $object_field_opt ) {
				if ( $object_field !== $field['name'] && ! in_array( $field['name'], $object_field_opt['alias'], true ) ) {
					continue;
				}

				if ( null !== $old_name ) {
					return pods_error( sprintf( __( '%s is reserved for internal WordPress or Pods usage, please try a different name', 'pods' ), $field['name'] ), $this );
				} else {
					return pods_error( sprintf( __( '%s is not editable', 'pods' ), $field['name'] ), $this );
				}
			}

			$post_data = array(
				'ID'           => $field['id'],
				'post_name'    => $field['name'],
				'post_title'   => $field['label'],
				'post_content' => $field['description']
			);

			if ( null !== $field['weight'] ) {
				$field['weight'] = pods_absint( $field['weight'] );

				$post_data['menu_order'] = $field['weight'];
			}
		}

		$field_types = PodsForm::field_types_list();

		if ( true === $db ) {
			if ( ! has_filter( 'wp_unique_post_slug', array( $this, 'save_slug_fix' ) ) ) {
				add_filter( 'wp_unique_post_slug', array( $this, 'save_slug_fix' ), 100, 6 );
			}

			$conflicted = false;

			// Headway compatibility fix
			if ( has_filter( 'wp_insert_post_data', 'headway_clean_slug', 0 ) ) {
				remove_filter( 'wp_insert_post_data', 'headway_clean_slug', 0 );

				$conflicted = true;
			}

			// Store the old field name
			if ( $old_name && $old_name !== $post_data['post_name'] ) {
				$field['old_name'] = $old_name;
			}

			$meta = $field;

			$excluded_meta = array(
				'id',
				'name',
				'label',
				'description',
				'pod_id',
				'pod',
				'weight',
				'options',
				'fields',
				'groups',
				'object_fields',
				'object_type',
				'object_storage_type',
				'parent',
			);

			foreach ( $excluded_meta as $meta_key ) {
				if ( isset( $meta[ $meta_key ] ) ) {
					unset( $meta[ $meta_key ] );
				}
			}

			// Get all field types except the current.
			$field_types = array_diff( $field_types, [ $field['type'] ] );

			$pattern = '/^(' . implode( '|', $field_types ) . ')_/';

			// Filter meta that is not for the current field type.
			$meta = array_filter( $meta, static function ( $value, $key ) use ( $pattern ) {
				return 1 !== preg_match( $pattern, $key );
			}, ARRAY_FILTER_USE_BOTH );

			$params->id = $this->save_wp_object( 'post', $post_data, $meta, true, true );

			if ( $conflicted ) {
				add_filter( 'wp_insert_post_data', 'headway_clean_slug', 0 );
			}

			if ( false === $params->id ) {
				return pods_error( __( 'Cannot save Field', 'pods' ), $this );
			}
		} else {
			$params->id = $field['name'];
		}

		$field['id'] = $params->id;

		if ( $field instanceof Field ) {
			$field_obj = $field;
		} elseif ( $field_obj instanceof Field && is_array( $field ) ) {
			$field_obj->set_args( $field );
		}

		$simple = ( 'pick' === $field['type'] && in_array( pods_v( 'pick_object', $field ), $simple_tableless_objects, true ) );

		$definition       = false;
		$field_definition = false;

		if ( $simple || ! $type_is_tableless ) {
			$field_definition = $this->get_field_definition( $field['type'], $field );
		}

		/**
		 * Allow filtering of the field definition when saving field.
		 *
		 * @since 2.8.0
		 *
		 * @param string|false       $field_definition The SQL definition to use for the field's table column.
		 * @param string             $type             The field type.
		 * @param array              $field            The field data.
		 * @param bool               $simple           Whether the field is a simple tableless field.
		 * @param Pods\Whatsit\Field $field_obj        The field object.
		 */
		$field_definition = apply_filters( 'pods_api_save_field_definition', $field_definition, $field['type'], $field, $simple, $field_obj );

		if ( ! empty( $field_definition ) ) {
			$definition = '`' . $field['name'] . '` ' . $field_definition;
		}

		$has_definition     = ! empty( $definition );
		$has_old_definition = ! empty( $old_definition );
		$simple_diff        = $old_simple !== $simple;
		$definition_diff    = $old_definition !== $definition;

		$sister_id = pods_v( 'sister_id', $field, 0 );

		if ( is_numeric( $sister_id ) ) {
			$sister_id = (int) $sister_id;
		} else {
			$sister_id = 0;
		}

		$definition_mode = 'bypass';

		if ( $table_operation && 'table' === $pod['storage'] && ! pods_tableless() ) {
			if ( ! empty( $old_id ) ) {
				if ( ( ( $field['type'] !== $old_type ) || $simple_diff ) && ! $has_definition ) {
					$definition_mode = 'drop';
				} elseif ( $has_definition ) {
					if ( $simple_diff || $old_name !== $field['name'] || $definition_diff ) {
						$definition_mode = 'add';

						if ( $has_old_definition ) {
							$definition_mode = 'change';
						}
					} elseif ( $has_old_definition && $definition_diff ) {
						$definition_mode = 'change';
					}
				}
			} elseif ( $has_definition ) {
				$definition_mode = 'add';

				if ( $has_old_definition ) {
					$definition_mode = 'change';
				}
			}

			/**
			 * Allow filtering the definition mode to use for the field definition handling.
			 *
			 * @since 2.8.14
			 *
			 * @param string             $definition_mode The definition mode used for the table field.
			 * @param Pods\Whatsit\Pod   $pod             The pod object.
			 * @param string             $type            The field type.
			 * @param array              $field           The field data.
			 * @param array              $extra_info      {
			 *      Extra information about the field.
			 *
			 *      @type bool               $simple Whether the field is a simple tableless field.
			 *      @type string             $definition The field definition.
			 *      @type null|string        $old_name The old field name (if preexisting).
			 *      @type null|string        $old_definition The old field definition (if preexisting).
			 *      @type null|array         $old_options The old field options (if preexisting).
			 *      @type Pods\Whatsit\Field $field_obj The field object.
			 * }
			 */
			$definition_mode = apply_filters( 'pods_api_save_field_table_definition_mode', $definition_mode, $pod, $field['type'], $field, [
				'simple'         => $simple,
				'definition'     => $definition,
				'old_name'       => $old_name,
				'old_definition' => $old_definition,
				'old_options'    => $old_options,
				'field_obj'      => $field_obj,
			] );

			if ( 'bypass' !== $definition_mode ) {
				/**
				 * Allow hooking into before the table has been altered for any custom functionality needed.
				 *
				 * @since 2.7.17
				 *
				 * @param string             $definition_mode The definition mode used for the table field.
				 * @param Pods\Whatsit\Pod   $pod             The pod object.
				 * @param string             $type            The field type.
				 * @param array              $field           The field data.
				 * @param array              $extra_info      {
				 *      Extra information about the field.
				 *
				 *      @type bool               $simple Whether the field is a simple tableless field.
				 *      @type string             $definition The field definition.
				 *      @type null|string        $old_name The old field name (if preexisting).
				 *      @type null|string        $old_definition The old field definition (if preexisting).
				 *      @type null|array         $old_options The old field options (if preexisting).
				 *      @type Pods\Whatsit\Field $field_obj The field object.
				 * }
				 */
				do_action( 'pods_api_save_field_table_pre_alter', $definition_mode, $pod, $field['type'], $field, [
					'simple'         => $simple,
					'definition'     => $definition,
					'old_name'       => $old_name,
					'old_definition' => $old_definition,
					'old_options'    => $old_options,
					'field_obj'      => $field_obj,
				] );

				if ( 'drop' === $definition_mode ) {
					// Drop field column.
					pods_query( "ALTER TABLE `@wp_pods_{$params->pod}` DROP COLUMN `{$old_name}`", false );
				} elseif ( 'change' === $definition_mode ) {
					// Change field column definition.
					if ( $old_name && $old_name !== $field['name'] ) {
						$test = pods_query( "ALTER TABLE `@wp_pods_{$params->pod}` CHANGE `{$old_name}` {$definition}", false );
					} else {
						$test = pods_query( "ALTER TABLE `@wp_pods_{$params->pod}` MODIFY {$definition}", false );
					}

					if ( false === $test ) {
						$definition_mode = 'add';
					}
				}

				// If the old field doesn't exist, continue to add a new field
				if ( 'add' === $definition_mode ) {
					$test = pods_query( "ALTER TABLE `@wp_pods_{$params->pod}` ADD COLUMN {$definition}", false );

					if ( false === $test ) {
						pods_query( "ALTER TABLE `@wp_pods_{$params->pod}` MODIFY {$definition}", __( 'Cannot create or update new field', 'pods' ) );
					}
				}

				/**
				 * Allow hooking into after the table has been altered for any custom functionality needed.
				 *
				 * @since 2.7.17
				 *
				 * @param string             $definition_mode The definition mode used for the table field.
				 * @param Pods\Whatsit\Pod   $pod             The pod object.
				 * @param string             $type            The field type.
				 * @param array              $field           The field object.
				 * @param array              $extra_info      {
				 *      Extra information about the field.
				 *
				 *      @type bool               $simple Whether the field is a simple tableless field.
				 *      @type string             $definition The field definition.
				 *      @type null|string        $old_name The old field name (if preexisting).
				 *      @type null|string        $old_definition The old field definition (if preexisting).
				 *      @type null|array         $old_options The old field options (if preexisting).
				 *      @type Pods\Whatsit\Field $field_obj The field object.
				 * }
				 */
				do_action( 'pods_api_save_field_table_altered', $definition_mode, $pod, $field['type'], $field, [
					'simple'         => $simple,
					'definition'     => $definition,
					'old_name'       => $old_name,
					'old_definition' => $old_definition,
					'old_options'    => $old_options,
					'field_obj'      => $field_obj,
				] );
			}
		}

		if ( ! empty( $old_id ) && 'meta' === $pod['storage'] && $old_name !== $field['name'] && $pod['meta_table'] !== $pod['table'] ) {
			$prepare = array(
				$field['name'],
				$old_name
			);

			// Users don't have a type
			if ( ! empty( $pod['field_type'] ) ) {
				$prepare[] = $pod['name'];
			}

			$join_table = $pod['table'];

			// Taxonomies are the odd type out, terrible I know
			if ( 'taxonomy' === $pod['type'] ) {
				// wp_term_taxonomy has the 'taxonomy' field we need to limit by
				$join_table = $wpdb->term_taxonomy;
			}

			pods_query( "
				UPDATE `{$pod[ 'meta_table' ]}` AS `m`
				LEFT JOIN `{$join_table}` AS `t`
					ON `t`.`{$pod[ 'field_id' ]}` = `m`.`{$pod[ 'meta_field_id' ]}`
				SET
					`m`.`{$pod[ 'meta_field_index' ]}` = %s
				WHERE
					`m`.`{$pod[ 'meta_field_index' ]}` = %s
			" . ( ! empty( $pod['field_type'] ) ? " AND `t`.`{$pod[ 'field_type' ]}` = %s" : "" ), $prepare );
		}

		if ( $old_type_is_tableless && $field['type'] !== $old_type ) {
			delete_post_meta( $old_sister_id, 'sister_id' );

			if ( true === $db ) {
				pods_query( "
						DELETE pm
						FROM {$wpdb->postmeta} AS pm
						LEFT JOIN {$wpdb->posts} AS p
							ON p.post_type = '_pods_field'
							AND p.ID = pm.post_id
						WHERE
							p.ID IS NOT NULL
							AND pm.meta_key = 'sister_id'
							AND pm.meta_value = %d
					", array(
						$params->id,
					) );

				if ( pods_podsrel_enabled() ) {
					pods_query( "DELETE FROM @wp_podsrel WHERE `field_id` = {$params->id}", false );

					pods_query( '
						UPDATE `@wp_podsrel`
						SET `related_field_id` = 0
						WHERE `field_id` = %d
					', array(
						$old_sister_id,
					) );
				}
			}
		} elseif ( 0 < $sister_id ) {
			update_post_meta( $sister_id, 'sister_id', $params->id );

			if ( true === $db && pods_podsrel_enabled() ) {
				pods_query( '
					UPDATE `@wp_podsrel`
					SET `related_field_id` = %d
					WHERE `field_id` = %d
				', array(
					$params->id,
					$sister_id,
				) );
			}
		} elseif ( 0 < $old_sister_id ) {
			delete_post_meta( $old_sister_id, 'sister_id' );

			if ( true === $db && pods_podsrel_enabled() ) {
				pods_query( '
					UPDATE `@wp_podsrel`
					SET `related_field_id` = 0
					WHERE `field_id` = %d
				', array(
					$old_sister_id,
				) );
			}
		}

		if ( ! empty( $old_id ) && $old_name !== $field['name'] && true === $db ) {
			pods_query( '
				UPDATE `@wp_postmeta`
				SET `meta_value` = %s
				WHERE
					`post_id` = %d
					AND `meta_key` = "pod_index"
					AND `meta_value` = %s
			', array(
				$field['name'],
				$pod['id'],
				$old_name,
			) );
		}

        $object_collection = Pods\Whatsit\Store::get_instance();

		$storage_type = 'collection';

		if ( true === $db ) {
			$storage_type = $this->get_default_object_storage_type();
		}

        /** @var Pods\Whatsit\Storage $storage */
        $storage = $object_collection->get_storage_object( $storage_type );

        $object = $storage->to_object( $field['id'], true );

        if ( ! $object ) {
        	return pods_error( __( 'Cannot save field to collection', 'pods' ), $this );
        }

		if ( ! $save_pod ) {
			$this->cache_flush_pods( $pod );
		}

		if ( true === $db ) {
			return $params->id;
		} else {
			return $object;
		}
	}

	/**
	 * Add a Group within a Pod.
	 *
	 * @since 2.8.0
	 *
	 * @param array    $params          {
	 *      An associative array of parameters
	 *
	 *      @type int|null    $id     The Group ID (id OR pod_id+name OR pod+name required).
	 *      @type string|null $name   The Group name (id OR pod_id+name OR pod+name required).
	 *      @type int|null    $pod_id The Pod ID (id OR pod_id+name OR pod+name required).
	 *      @type string|null $pod    The Pod name (id OR pod_id+name OR pod+name required).
	 *      @type string|null $label  The Group label.
	 *      @type string|null $type   The Group type.
	 *      @type int|null    $weight The order in which the Group appears.
	 * }
	 * @param bool     $sanitized       (optional) Decides whether the params have been sanitized before being passed,
	 *                                  will sanitize them if false.
	 * @param bool|int $db              (optional) Whether to save into the DB or just return group array.
	 *
	 * @return int|array The group ID or group array (if !$db)
	 *
	 * @throws \Exception
	 */
	public function add_group( $params, $sanitized = false, $db = true ) {
		$params = (object) $params;

		$params->is_new = true;

		return $this->save_group( $params, $sanitized, $db );
	}

	/**
	 * Add or edit a Group within a Pod.
	 *
	 * @since 2.8.0
	 *
	 * @param array|Group $params        {
	 *      An associative array of parameters
	 *
	 *      @type int|null    $id        The Group ID (id OR pod_id+name OR pod+name required).
	 *      @type string|null $name      The Group name (id OR pod_id+name OR pod+name required).
	 *      @type int|null    $pod_id    The Pod ID (id OR pod_id+name OR pod+name required).
	 *      @type string|null $pod       The Pod name (id OR pod_id+name OR pod+name required).
	 *      @type string|null $label     The Group label.
	 *      @type string|null $type      The Group type.
	 *      @type int|null    $weight    The order in which the Group appears.
	 *      @type bool        $is_new    Whether to try to add the group as a new group when passing name.
	 *      @type bool        $overwrite Whether to try to replace the existing group if name and no ID is passed.
	 * }
	 * @param bool        $sanitized     (optional) Decides whether the params have been sanitized before being passed,
	 *                                   will sanitize them if false.
	 * @param bool|int    $db            (optional) Whether to save into the DB or just return group array.
	 *
	 * @return int|array The group ID or group array (if !$db)
	 *
	 * @throws \Exception
	 */
	public function save_group( $params, $sanitized = false, $db = true ) {
		if ( $params instanceof Group ) {
			$params = [
				'id'    => $params->get_id(),
				'group' => $params,
			];
		}

		$params = (object) $params;

		$pod   = null;
		$group = null;

		// Setup Pod if passed.
		if ( isset( $params->pod_data ) && $params->pod_data instanceof Pod ) {
			$pod = $params->pod_data;

			unset( $params->pod_data );

			$params->pod    = $pod->get_name();
			$params->pod_id = $pod->get_id();
		} elseif ( isset( $params->pod ) && $params->pod instanceof Pod ) {
			$pod = $params->pod;

			$params->pod    = $pod->get_name();
			$params->pod_id = $pod->get_id();
		} elseif ( isset( $params->pod ) && is_array( $params->pod ) ) {
			$pod = $params->pod;

			$params->pod    = $pod['name'];
			$params->pod_id = $pod['id'];
		} elseif ( isset( $params->pod_id ) ) {
			$params->pod_id = pods_absint( $params->pod_id );
		}

		// Setup Group if passed.
		if ( isset( $params->group ) && $params->group instanceof Group ) {
			$group = $params->group;

			unset( $params->group );

			$params->id   = $group->get_id();
		} elseif ( isset( $params->group ) && is_array( $params->group ) ) {
			$group = $params->group;

			unset( $params->group );

			$params->id   = $group['id'];
		} elseif ( isset( $params->id ) ) {
			$params->id = pods_absint( $params->id );
		}

		if ( false === $sanitized ) {
			$params = pods_sanitize( $params );

			$sanitized = true;
		}

		$id_required = false;

		if ( isset( $params->id_required ) ) {
			$id_required = (boolean) $params->id_required;

			unset( $params->id_required );
		}

		$params->is_new    = isset( $params->is_new ) ? (boolean) $params->is_new : false;
		$params->overwrite = isset( $params->overwrite ) ? (boolean) $params->overwrite : false;

		if ( ! $pod && ( ! isset( $params->pod ) || empty( $params->pod ) ) && ( ! isset( $params->pod_id ) || empty( $params->pod_id ) ) ) {
			return pods_error( __( 'Pod ID or name is required', 'pods' ), $this );
		}

		if ( ! $pod ) {
			if ( ( ! isset( $params->pod_id ) || empty( $params->pod_id ) ) && ( true === $db || 0 < $db ) ) {
				$pod = $this->load_pod( array( 'name' => $params->pod ), false );
			} elseif ( ! isset( $params->pod ) && ( true === $db || 0 < $db ) ) {
				$pod = $this->load_pod( array( 'id' => $params->pod_id ), false );
			} elseif ( true === $db || 0 < $db ) {
				$pod = $this->load_pod( array( 'id' => $params->pod_id, 'name' => $params->pod ), false );
			}
		}

		if ( empty( $pod ) && true === $db ) {
			return pods_error( __( 'Pod not found', 'pods' ), $this );
		}

		$reserved_keywords = pods_reserved_keywords( 'wp-post' );

		/** @var Pod $pod */
		$params->pod_id = $pod->get_id();
		$params->pod    = $pod->get_name();

		if ( isset( $params->name ) ) {
			$params->name = pods_clean_name( $params->name, true, 'meta' !== $pod['storage'] );

			if ( $params->is_new && isset( $params->id ) ) {
				$params->id = null;
			}
		}

		if ( empty( $params->name ) && empty( $params->id ) ) {
			return pods_error( __( 'Pod group name is required', 'pods' ), $this );
		}

		$load_params = array(
			'parent' => $params->pod_id,
		);

		if ( ! empty( $params->id ) ) {
			$load_params['id'] = $params->id;
		} elseif ( ! empty( $params->old_name ) ) {
			$load_params['name'] = $params->old_name;
		} elseif ( ! empty( $params->name ) ) {
			$load_params['name'] = $params->name;
		}

		$group = $this->load_group( $load_params );

		if ( $group instanceof Group ) {
			$group = $group->get_args();
		}

		$old_id   = null;
		$old_name = null;

		if ( ! empty( $group ) ) {
			$old_id   = $group['id'];
			$old_name = $group['name'];

			// Maybe set up the group to save over the existing group.
			if ( $params->overwrite && empty( $params->id ) ) {
				$params->id = $old_id;
			}

			if ( isset( $params->new_name ) && ! empty( $params->new_name ) ) {
				$group['name'] = $params->new_name;

				unset( $params->new_name );
			} elseif ( isset( $params->name ) ) {
				$group['name'] = $params->name;
			}

			if ( $old_name !== $group['name'] || empty( $params->id ) || $old_id !== $params->id ) {
				if ( in_array( $params->name, $reserved_keywords, true ) ) {
					return pods_error( sprintf( __( '%s is reserved for internal WordPress or Pods usage, please try a different name', 'pods' ), $params->name ), $this );
				}

				if ( false !== $this->group_exists( $params, false ) ) {
					return pods_error( sprintf( __( 'Group %1$s already exists, you cannot rename %2$s to that', 'pods' ), $params->name, $old_name ), $this );
				}
			}

			if ( ( $id_required || ! empty( $params->id ) ) && ( empty( $old_id ) || $old_id !== $params->id ) ) {
				return pods_error( sprintf( __( 'Group %s already exists', 'pods' ), $params->name ), $this );
			}

			if ( empty( $params->id ) ) {
				$params->id = $old_id;
			}
		} else {
			$group = [
				'id'          => 0,
				'pod_id'      => $params->pod_id,
				'name'        => $params->name,
				'label'       => $params->name,
				'description' => '',
				'type'        => '',
				'weight'      => null,
				'options'     => [],
			];
		}

		// Setup options
		$options = get_object_vars( $params );

		$options_ignore = [
			'adhoc',
			'method',
			'table_info',
			'attributes',
			'group',
			'grouped',
			'developer_mode',
			'dependency',
			'depends-on',
			'excludes-on',
			'object_type',
			'object_storage_type',
			'is_new',
			'overwrite',
			'_locale',
			'old_name',
		];

		foreach ( $options_ignore as $ignore ) {
			if ( isset( $options[ $ignore ] ) ) {
				unset( $options[ $ignore ] );
			}
		}

		$exclude = [
			'id',
			'pod_id',
			'pod',
			'name',
			'label',
			'description',
			'type',
			'weight',
			'options',
			'is_new',
			'overwrite',
			'_locale',
			'post_status',
		];

		foreach ( $exclude as $k => $exclude_group ) {
			$aliases = array( $exclude_group );

			if ( is_array( $exclude_group ) ) {
				$aliases       = array_merge( array( $k ), $exclude_group );
				$exclude_group = $k;
			}

			foreach ( $aliases as $alias ) {
				if ( isset( $options[ $alias ] ) ) {
					$group[ $exclude_group ] = pods_trim( $options[ $alias ] );

					unset( $options[ $alias ] );
				}
			}
		}

		if ( '' === $group['label'] ) {
			$group['label'] = $group['name'];
		}

		foreach ( $options as $o => $v ) {
			$group[ $o ] = $v;
		}

		// Check for strict mode (default: strict).
		$strict_mode = ! defined( 'PODS_FIELD_STRICT' ) || PODS_FIELD_STRICT;

		if ( 0 < $old_id && ! $strict_mode ) {
			$params->id  = $old_id;
			$group['id'] = $old_id;
		}

		// Add new group.
		if ( ! isset( $params->id ) || empty( $params->id ) || empty( $group ) ) {
			if ( in_array( $group['name'], $reserved_keywords, true ) ) {
				return pods_error( sprintf( __( '%s is reserved for internal WordPress or Pods usage, please try a different name', 'pods' ), $group['name'] ), $this );
			}

			 // Reserved post_name values that can't be used as group names
			if ( 'rss' === $group['name'] ) {
				$group['name'] .= '2';
			}

			// Sink the new group to the bottom of the list
			if ( null === $group['weight'] ) {
				$group['weight'] = 0;

				$bottom_most_group = get_posts( array(
					'post_type'      => '_pods_group',
					'orderby'        => 'menu_order',
					'order'          => 'DESC',
					'posts_per_page' => 1,
					'post_parent'    => $group['pod_id']
				) );

				if ( ! empty( $bottom_most_group ) ) {
					$group['weight'] = pods_absint( $bottom_most_group[0]->menu_order ) + 1;
				}
			}

			$group['weight'] = pods_absint( $group['weight'] );

			$post_data = array(
				'post_name'    => $group['name'],
				'post_title'   => $group['label'],
				'post_content' => $group['description'],
				'post_type'    => '_pods_group',
				'post_parent'  => $group['pod_id'],
				'post_status'  => 'publish',
				'menu_order'   => $group['weight']
			);
		} else {
			if ( in_array( $group['name'], array( 'id', 'ID' ) ) ) {
				if ( null !== $old_name ) {
					return pods_error( sprintf( __( '%s is reserved for internal Pods usage, please try a different name', 'pods' ), $group['name'] ), $this );
				} else {
					return pods_error( sprintf( __( '%s is not editable', 'pods' ), $group['name'] ), $this );
				}
			}

			$post_data = array(
				'ID'           => $group['id'],
				'post_name'    => $group['name'],
				'post_title'   => $group['label'],
				'post_content' => $group['description']
			);

			if ( null !== $group['weight'] ) {
				$group['weight'] = pods_absint( $group['weight'] );

				$post_data['menu_order'] = $group['weight'];
			}
		}

		if ( true === $db ) {
			if ( ! has_filter( 'wp_unique_post_slug', array( $this, 'save_slug_fix' ) ) ) {
				add_filter( 'wp_unique_post_slug', array( $this, 'save_slug_fix' ), 100, 6 );
			}

			$conflicted = false;

			// Headway compatibility fix
			if ( has_filter( 'wp_insert_post_data', 'headway_clean_slug', 0 ) ) {
				remove_filter( 'wp_insert_post_data', 'headway_clean_slug', 0 );

				$conflicted = true;
			}

			// Store the old group name
			if ( $old_name && $old_name !== $post_data['post_name'] ) {
				$group['old_name'] = $old_name;
			}

			$meta = $group;

			$excluded_meta = array(
				'id',
				'name',
				'label',
				'description',
				'pod_id',
				'pod',
				'weight',
				'options',
				'groups',
				'group',
				'fields',
				'object_fields',
				'is_new',
				'overwrite',
				'_locale',
			);

			foreach ( $excluded_meta as $meta_key ) {
				if ( isset( $meta[ $meta_key ] ) ) {
					unset( $meta[ $meta_key ] );
				}
			}

			$params->id = $this->save_wp_object( 'post', $post_data, $meta, true, true );

			if ( $conflicted ) {
				add_filter( 'wp_insert_post_data', 'headway_clean_slug', 0 );
			}

			if ( false === $params->id ) {
				return pods_error( __( 'Cannot save Group', 'pods' ), $this );
			}
		} else {
			$params->id = $group['name'];
		}

		$group['id'] = $params->id;

        $object_collection = Pods\Whatsit\Store::get_instance();

        /** @var Pods\Whatsit\Storage\Post_Type $post_type_storage */
        $post_type_storage = $object_collection->get_storage_object( $this->get_default_object_storage_type() );

        $object = $post_type_storage->to_object( $group['id'], true );

        if ( ! $object ) {
        	return pods_error( __( 'Cannot save group to collection', 'pods' ), $this );
        }

		$this->cache_flush_pods( $object );

		if ( true === $db ) {
			return $params->id;
		} else {
			return $object;
		}
	}

	/**
	 * Fix Pod / Group / Field post_name to ensure they are exactly as saved (allow multiple posts w/ same post_name)
	 *
	 * @param string $slug          Unique slug value
	 * @param int    $post_ID       Post ID
	 * @param string $post_status   Post Status
	 * @param string $post_type     Post Type
	 * @param int    $post_parent   Post Parent ID
	 * @param string $original_slug Original slug value
	 *
	 * @return string Final slug value
	 *
	 * @since 2.3.3
	 */
	public function save_slug_fix( $slug, $post_ID, $post_status, $post_type, $post_parent = 0, $original_slug = null ) {
		if ( in_array( $post_type, array( '_pods_pod', '_pods_group', '_pods_field' ), true ) && false !== strpos( $slug, '-' ) ) {
			$slug = $original_slug;
		}

		return $slug;
	}

	/**
	 * Add or Edit a Pods Object
	 *
	 * $params['id'] int The Object ID
	 * $params['name'] string The Object name
	 * $params['type'] string The Object type
	 *
	 * @param array|object $params    An associative array of parameters
	 * @param bool         $sanitized (optional) Decides whether the params have been sanitized before being passed,
	 *                                will sanitize them if false.
	 *
	 * @return int The Object ID
	 * @since 2.0.0
	 */
	public function save_object( $params, $sanitized = false ) {

		$params = (object) $params;

		if ( false === $sanitized ) {
			$params = pods_sanitize( $params );

			$sanitized = true;
		}

		if ( ! isset( $params->name ) || empty( $params->name ) ) {
			return pods_error( __( 'Name must be given to save an Object', 'pods' ), $this );
		}

		if ( ! isset( $params->type ) || empty( $params->type ) ) {
			return pods_error( __( 'Type must be given to save an Object', 'pods' ), $this );
		}

		$object = [
			'id'   => isset( $params->id ) ? $params->id : 0,
			'name' => $params->name,
			'type' => $params->type,
			'code' => isset( $params->code ) ? $params->code : '',
		];

		// Setup options
		$options = get_object_vars( $params );

		if ( isset( $options['options'] ) ) {
			$options = array_merge( $options, $options['options'] );

			unset( $options['options'] );
		}

		if ( isset( $options['method'] ) ) {
			unset( $options['method'] );
		}

		$post_meta = $options;

		$exclude = array_keys( $object );

		foreach ( $exclude as $excluded_key ) {
			if ( isset( $post_meta[ $excluded_key ] ) ) {
				unset( $post_meta[ $excluded_key ] );
			}
		}

		$object = array_merge( $options, $object );

		$post_data = array(
			'post_name'    => pods_clean_name( $object['name'], true ),
			'post_title'   => $object['name'],
			'post_content' => $object['code'],
			'post_type'    => '_pods_' . $object['type'],
			'post_status'  => 'publish'
		);

		if ( ! empty( $object['id'] ) ) {
			$post_data['ID'] = $object['id'];
		}

		$post_status = pods_v( 'status', $object, null, true );

		if ( null !== $post_status ) {
			$post_data['post_status'] = $post_status;
		}

		remove_filter( 'content_save_pre', 'balanceTags', 50 );

		$post_data = pods_sanitize( $post_data );

		$params->id = $this->save_post( $post_data, $post_meta, true, true );

		pods_transient_clear( 'pods_objects_' . $params->type );
		pods_transient_clear( 'pods_objects_' . $params->type . '_get' );

		return $params->id;
	}

	/**
	 * @see   PodsAPI::save_object
	 *
	 * Add or edit a Pod Template
	 *
	 * $params['id'] int The template ID
	 * $params['name'] string The template name
	 * $params['code'] string The template code
	 *
	 * @param array|object $params    An associative array of parameters
	 * @param bool         $sanitized (optional) Decides whether the params have been sanitized before being passed,
	 *                                will sanitize them if false.
	 *
	 * @return int The Template ID
	 *
	 * @since 1.7.9
	 */
	public function save_template( $params, $sanitized = false ) {

		$params = (object) $params;

		$params->type = 'template';

		return $this->save_object( $params, $sanitized );
	}

	/**
	 * @see   PodsAPI::save_object
	 *
	 * Add or edit a Pod Page
	 *
	 * $params['id'] int The page ID
	 * $params['name'] string The page URI
	 * $params['code'] string The page code
	 *
	 * @param array|object $params    An associative array of parameters
	 * @param bool         $sanitized (optional) Decides whether the params have been sanitized before being passed,
	 *                                will sanitize them if false.
	 *
	 * @return int The page ID
	 * @since 1.7.9
	 */
	public function save_page( $params, $sanitized = false ) {

		$params = (object) $params;

		if ( ! isset( $params->name ) ) {
			$params->name = $params->uri;
			unset( $params->uri );
		}

		if ( isset( $params->phpcode ) ) {
			$params->code = $params->phpcode;
			unset( $params->phpcode );
		}

		$params->name = trim( $params->name, '/' );
		$params->type = 'page';

		return $this->save_object( $params, $sanitized );
	}

	/**
	 * @see   PodsAPI::save_object
	 *
	 * Add or edit a Pod Helper
	 *
	 * $params['id'] int The helper ID
	 * $params['name'] string The helper name
	 * $params['helper_type'] string The helper type ("pre_save", "display", etc)
	 * $params['code'] string The helper code
	 *
	 * @param array $params    An associative array of parameters
	 * @param bool  $sanitized (optional) Decides whether the params have been sanitized before being passed, will
	 *                         sanitize them if false.
	 *
	 * @return int The helper ID
	 * @since 1.7.9
	 *
	 * @deprecated since 2.8.0
	 */
	public function save_helper( $params, $sanitized = false ) {
		return 0;
	}

	/**
	 * Add or edit a single pod item
	 *
	 * $params['pod'] string The Pod name (pod or pod_id is required)
	 * $params['pod_id'] string The Pod ID (pod or pod_id is required)
	 * $params['id'] int|array The item ID, or an array of item IDs to save data for
	 * $params['data'] array (optional) Associative array of field names + values
	 * $params['bypass_helpers'] bool Set to true to bypass running pre-save and post-save helpers
	 * $params['track_changed_fields'] bool Set to true to enable tracking of saved fields via
	 * PodsAPI::get_changed_fields()
	 * $params['error_mode'] string Throw an 'exception', 'exit' with the message, return 'false', or return 'wp_error'
	 *
	 * @param array|object $params An associative array of parameters
	 *
	 * @return int|array The item ID, or an array of item IDs (if `id` is an array if IDs)
	 *
	 * @throws Exception
	 *
	 * @since 1.7.9
	 */
	public function save_pod_item( $params ) {

		global $wpdb;

		$params = (object) pods_str_replace( '@wp_', '{prefix}', $params );

		$tableless_field_types    = PodsForm::tableless_field_types();
		$repeatable_field_types   = PodsForm::repeatable_field_types();
		$layout_field_types       = PodsForm::layout_field_types();
		$simple_tableless_objects = PodsForm::simple_tableless_objects();

		$error_mode = $this->display_errors;

		if ( ! empty( $params->error_mode ) ) {
			$error_mode = $params->error_mode;
		}

		if ( ! isset( $params->pod ) ) {
			$params->pod = false;
		}
		if ( isset( $params->pod_id ) ) {
			$params->pod_id = pods_absint( $params->pod_id );
		} else {
			$params->pod_id = 0;
		}

		if ( isset( $params->id ) ) {
			$params->id = pods_absint( $params->id );
		} else {
			$params->id = 0;
		}

		if ( ! isset( $params->from ) ) {
			$params->from = 'save';
		}

		if ( ! isset( $params->location ) ) {
			$params->location = null;
		}

		if ( ! isset( $params->track_changed_fields ) ) {
			$params->track_changed_fields = false;
		}

		if ( ! isset( $params->podsmeta ) ) {
			$params->podsmeta = false;
		}

		if ( ! isset( $params->podsmeta_direct ) ) {
			$params->podsmeta_direct = false;
		}

		$pod_name = $params->pod;
		/**
		 * Override $params['track_changed_fields']
		 *
		 * Use for globally setting field change tracking.
		 *
		 * @param bool
		 *
		 * @since 2.3.19
		 */
		$track_changed_fields = apply_filters( "pods_api_save_pod_item_track_changed_fields_{$pod_name}", (boolean) $params->track_changed_fields, $params );

		$changed_fields = array();

		if ( ! isset( $params->clear_slug_cache ) ) {
			$params->clear_slug_cache = true;
		}

		// Support for bulk edit
		if ( isset( $params->id ) && ! empty( $params->id ) && is_array( $params->id ) ) {
			$ids        = array();
			$new_params = $params;

			foreach ( $params->id as $id ) {
				$new_params->id = $id;

				$ids[] = $this->save_pod_item( $new_params );
			}

			return $ids;
		}

		// Allow Helpers to know what's going on, are we adding or saving?
		$is_new_item = false;

		if ( empty( $params->id ) ) {
			$is_new_item = true;
		}

		if ( isset( $params->is_new_item ) ) {
			$is_new_item = (boolean) $params->is_new_item;
		}

		// Allow Helpers to bypass subsequent helpers in recursive save_pod_item calls
		$bypass_helpers = false;

		if ( isset( $params->bypass_helpers ) && false !== $params->bypass_helpers ) {
			$bypass_helpers = true;
		}

		// Allow Custom Fields not defined by Pods to be saved
		$allow_custom_fields = false;

		if ( isset( $params->allow_custom_fields ) && false !== $params->allow_custom_fields ) {
			$allow_custom_fields = true;
		}

		// Get array of Pods
		$pod = $this->load_pod( array( 'id' => $params->pod_id, 'name' => $params->pod ), false );

		if ( false === $pod ) {
			// Bypass if doing a normal object sync from PodsMeta.
			if ( $params->podsmeta ) {
				return;
			}

			return pods_error( __( 'Pod not found', 'pods' ), $error_mode );
		}

		$params->pod    = $pod['name'];
		$params->pod_id = $pod['id'];

		if ( 'settings' === $pod['type'] ) {
			$params->id = $pod['id'];
		}

		$fields        = $pod->get_fields();
		$object_fields = $pod->get_object_fields();

		// Map the fields to Value_Field to store values.
		$fields        = array_map( [ Value_Field::class, 'init' ], $fields );
		$object_fields = array_map( [ Value_Field::class, 'init' ], $object_fields );

		$fields_active = array();
		$custom_data   = array();
		$custom_fields = array();

		// Find the active fields (loop through $params->data to retain order)
		if ( ! empty( $params->data ) && is_array( $params->data ) ) {
			foreach ( $params->data as $field => $value ) {
				if ( isset( $object_fields[ $field ] ) ) {
					$object_fields[ $field ]['value'] = $value;

					$fields_active[] = $field;
				} elseif ( isset( $fields[ $field ] ) ) {
					if ( 'save' === $params->from || true === pods_permission( $fields[ $field ] ) ) {
						$fields[ $field ]['value'] = $value;

						$fields_active[] = $field;
					} elseif ( ! pods_has_permissions( $fields[ $field ] ) && pods_v( 'hidden', $fields[ $field ], false ) ) {
						$fields[ $field ]['value'] = $value;

						$fields_active[] = $field;
					}
				} else {
					$found = false;

					foreach ( $object_fields as $object_field => $object_field_opt ) {
						if ( in_array( $field, $object_field_opt['alias'] ) ) {
							$object_fields[ $object_field ]['value'] = $value;

							$fields_active[] = $object_field;

							$found = true;

							break;
						}
					}

					if ( $allow_custom_fields && ! $found ) {
						$custom_fields[] = $field;
					}
				}
			}

			if ( $allow_custom_fields && ! empty( $custom_fields ) ) {
				foreach ( $custom_fields as $field ) {
					$custom_data[ $field ] = $params->data[ $field ];
				}
			}

			unset( $params->data );
		}

		if ( empty( $params->id ) && isset( $fields['created'] ) && ! in_array( 'created', $fields_active, true ) && in_array( $fields['created']['type'], array(
				'date',
				'datetime'
			), true ) ) {
			$fields['created']['value'] = current_time( 'mysql' );

			$fields_active[] = 'created';
		}

		if ( isset( $fields['modified'] ) && ! in_array( 'modified', $fields_active, true ) && in_array( $fields['modified']['type'], array(
				'date',
				'datetime'
			), true ) ) {
			$fields['modified']['value'] = current_time( 'mysql' );

			$fields_active[] = 'modified';
		}

		if ( empty( $params->id ) && ! empty( $pod['pod_field_index'] ) && isset( $fields[ $pod['pod_field_slug'] ] ) && in_array( $pod['type'], array(
				'pod',
				'table'
			), true ) && in_array( $pod['pod_field_index'], $fields_active, true ) && ! in_array( $pod['pod_field_slug'], $fields_active, true ) ) {
			$fields[ $pod['pod_field_slug'] ]['value'] = ''; // this will get picked up by slug pre_save method

			$fields_active[] = $pod['pod_field_slug'];
		}

		// Handle hidden fields
		if ( empty( $params->id ) ) {
			foreach ( $fields as $field => $field_data ) {
				if ( in_array( $field, $fields_active, true ) ) {
					continue;
				}

				if ( in_array( $params->from, array(
						'save',
						'process_form'
					), true ) || true === pods_permission( $fields[ $field ] ) ) {
					$value = PodsForm::default_value( pods_v( $field, 'post' ), $field_data['type'], $field, pods_v( 'options', $field_data, $field_data, true ), $pod, $params->id );

					if ( null !== $value && '' !== $value && false !== $value ) {
						$fields[ $field ]['value'] = $value;

						$fields_active[] = $field;
					}
				}
			}

			// Set default field values for object fields
			if ( ! empty( $object_fields ) ) {
				foreach ( $object_fields as $field => $field_data ) {
					if ( in_array( $field, $fields_active, true ) ) {
						continue;
					}

					if ( ! isset( $field_data['default'] ) || '' === $field_data['default'] ) {
						continue;
					}

					$value = PodsForm::default_value( pods_v( $field, 'post' ), $field_data['type'], $field, pods_v( 'options', $field_data, $field_data, true ), $pod, $params->id );

					if ( null !== $value && '' !== $value && false !== $value ) {
						$object_fields[ $field ]['value'] = $value;

						$fields_active[] = $field;
					}
				}
			}

			// Set default field values for Pod fields
			foreach ( $fields as $field => $field_data ) {
				if ( in_array( $field, $fields_active, true ) ) {
					continue;
				}

				if ( ! isset( $field_data['default'] ) || '' === $field_data['default'] ) {
					continue;
				}

				$value = PodsForm::default_value( pods_v( $field, 'post' ), $field_data['type'], $field, pods_v( 'options', $field_data, $field_data, true ), $pod, $params->id );

				if ( null !== $value && '' !== $value && false !== $value ) {
					$fields[ $field ]['value'] = $value;

					$fields_active[] = $field;
				}
			}
		}

		$columns            =& $fields; // @deprecated 2.0.0
		$active_columns     =& $fields_active; // @deprecated 2.0.0
		$params->tbl_row_id =& $params->id; // @deprecated 2.0.0

		$pre_save_helpers  = array();
		$post_save_helpers = array();

		$pieces = array(
			'fields',
			'params',
			'pod',
			'fields_active',
			'object_fields',
			'custom_fields',
			'custom_data',
			'track_changed_fields',
			'changed_fields'
		);

		if ( $track_changed_fields ) {
			self::handle_changed_fields( $params->pod, $params->id, 'set' );
		}

		if ( false === $bypass_helpers ) {
			// Plugin hooks
			$hooked = $this->do_hook( 'pre_save_pod_item', compact( $pieces ), $is_new_item, $params->id );

			if ( is_array( $hooked ) && ! empty( $hooked ) ) {
				extract( $hooked );
			}

			$hooked = $this->do_hook( "pre_save_pod_item_{$params->pod}", compact( $pieces ), $is_new_item, $params->id );

			if ( is_array( $hooked ) && ! empty( $hooked ) ) {
				extract( $hooked );
			}

			if ( $is_new_item ) {
				$hooked = $this->do_hook( 'pre_create_pod_item', compact( $pieces ) );

				if ( is_array( $hooked ) && ! empty( $hooked ) ) {
					extract( $hooked );
				}

				$hooked = $this->do_hook( "pre_create_pod_item_{$params->pod}", compact( $pieces ) );

				if ( is_array( $hooked ) && ! empty( $hooked ) ) {
					extract( $hooked );
				}
			} else {
				$hooked = $this->do_hook( 'pre_edit_pod_item', compact( $pieces ), $params->id );

				if ( is_array( $hooked ) && ! empty( $hooked ) ) {
					extract( $hooked );
				}

				$hooked = $this->do_hook( "pre_edit_pod_item_{$params->pod}", compact( $pieces ), $params->id );

				if ( is_array( $hooked ) && ! empty( $hooked ) ) {
					extract( $hooked );
				}
			}

			// Call any pre-save helpers (if not bypassed)
			if ( ! defined( 'PODS_DISABLE_EVAL' ) || ! PODS_DISABLE_EVAL ) {
				if ( ! empty( $pod ) ) {
					$helpers = array( 'pre_save_helpers', 'post_save_helpers' );

					foreach ( $helpers as $helper ) {
						if ( isset( $pod[ $helper ] ) && ! empty( $pod[ $helper ] ) ) {
							${$helper} = explode( ',', $pod[ $helper ] );
						}
					}
				}
			}
		}

		$table_data         = [];
		$table_formats      = [];
		$update_values      = [];
		$rel_fields         = [];
		$rel_field_ids      = [];
		$fields_to_run_save = [];

		$object_type = $pod['type'];

		$object_ID = 'ID';

		if ( ! empty( $pod['field_id'] ) ) {
			$object_ID = $pod['field_id'];
		} elseif ( ! empty( $pod['pod_field_id'] ) ) {
			$object_ID = $pod['pod_field_id'];
		}

		$has_object_data_to_save = false;

		$object_data     = [];
		$object_meta     = [];
		$simple_rel_meta = [];
		$post_term_data  = [];

		if ( 'settings' === $object_type ) {
			$object_data['option_id'] = $pod['name'];

			$params->id = $pod['name'];
		} elseif ( ! empty( $params->id ) ) {
			$object_data[ $object_ID ] = $params->id;
		}

		$fields_active = array_unique( $fields_active );

		/**
		 * Allow filtering whether to save data to the associated table (if the Pod is using table storage).
		 *
		 * @since 2.8.9
		 *
		 * @param bool $save_to_table Whether to save data to the associated table (if the Pod is using table storage).
		 */
		$save_to_table = (bool) apply_filters( 'pods_api_save_pod_item_save_to_table', true );

		// Loop through each active field, validating and preparing the table data
		foreach ( $fields_active as $field ) {
			if ( isset( $object_fields[ $field ] ) ) {
				$field_data = $object_fields[ $field ];
			} elseif ( isset( $fields[ $field ] ) ) {
				$field_data = $fields[ $field ];
			} else {
				continue;
			}

			$value   = $field_data['value'];
			$type    = $field_data['type'];
			$options = pods_v( 'options', $field_data, [] );

			$field_object = $field_data;

			if ( $field_data instanceof Value_Field ) {
				$field_object = $field_data->get_field_object();
			}

			if ( in_array( $type, $layout_field_types, true ) ) {
				continue;
			}

			// WPML AJAX compatibility
			if (
				is_admin()
				&& (
					(
						isset( $_POST['action'] )
						&& 'wpml_save_job_ajax' === $_POST['action']
					)
					|| (
						isset( $_GET['page'], $_POST['icl_ajx_action'], $_POST['_icl_nonce'] )
						&& false !== strpos( $_GET['page'], '/menu/languages.php' )
						&& wp_verify_nonce( $_POST['_icl_nonce'], $_POST['icl_ajx_action'] . '_nonce' )
					)
				)
			) {
				$options['unique']            = 0;
				$fields[ $field ]['unique']   = 0;
				$options['required']          = 0;
				$fields[ $field ]['required'] = 0;
			} else {
				// Validate value
				$validate = $this->handle_field_validation( $value, $field, $object_fields, $fields, $pod, $params );

				if ( false === $validate ) {
					$validate = sprintf( __( 'There was an issue validating the field %s', 'pods' ), $field_data['label'] );
				} elseif ( true !== $validate ) {
					$validate = (array) $validate;
				}

				if ( ! is_bool( $validate ) && ! empty( $validate ) ) {
					return pods_error( $validate, $error_mode );
				}
			}

			$value = PodsForm::pre_save( $field_data['type'], $value, $params->id, $field, $field_data, pods_config_merge_fields( $fields, $object_fields ), $pod, $params );

			$field_data['value'] = $value;

			if ( isset( $object_fields[ $field ] ) ) {
				// @todo Eventually support 'comment' field type saving here too
				if ( 'taxonomy' === $object_fields[ $field ]['type'] ) {
					$post_term_data[ $field ] = $value;
				} else {
					$object_data[ $field ] = $value;

					$has_object_data_to_save = true;
				}
			} else {
				$simple = ( 'pick' === $type && in_array( pods_v( 'pick_object', $field_data ), $simple_tableless_objects, true ) );
				$simple = (boolean) $this->do_hook( 'tableless_custom', $simple, $field_data, $field, $fields, $pod, $params );

				// Handle Simple Relationships
				if ( $simple ) {
					if ( ! is_array( $value ) ) {
						if ( 0 < strlen( $value ) ) {
							$value = explode( ',', $value );
						} else {
							$value = array();
						}
					}

					$pick_limit = (int) pods_v( 'pick_limit', $options, 0 );

					if ( 'single' === pods_v( 'pick_format_type', $options ) ) {
						$pick_limit = 1;
					}

					if ( 'custom-simple' === pods_v( 'pick_object', $field_data ) ) {
						$custom = pods_v( 'pick_custom', $options, '' );

						$custom = apply_filters( 'pods_form_ui_field_pick_custom_values', $custom, $field_data['name'], $value, $field_data, $pod, $params->id );

						// Input values are unslashed. Unslash database values as well to ensure correct comparison.
						$custom = pods_unslash( $custom );

						if ( empty( $value ) || empty( $custom ) ) {
							$value = '';
						} elseif ( ! empty( $custom ) ) {
							if ( ! is_array( $custom ) ) {
								$custom = explode( "\n", $custom );

								$custom_values = array();

								foreach ( $custom as $c => $cv ) {
									if ( 0 < strlen( $cv ) ) {
										$custom_label = explode( '|', $cv );

										if ( ! isset( $custom_label[1] ) ) {
											$custom_label[1] = $custom_label[0];
										}

										$custom_label[0]                   = trim( (string) $custom_label[0] );
										$custom_label[1]                   = trim( (string) $custom_label[1] );
										$custom_values[ $custom_label[0] ] = $custom_label[1];
									}
								}
							} else {
								$custom_values = $custom;
							}

							$values = array();

							foreach ( $value as $k => $v ) {
								$v = pods_unsanitize( $v );

								if ( isset( $custom_values[ $v ] ) ) {
									$values[ $k ] = $v;
								}
							}

							$value = $values;
						}
					}

					if ( 0 < $pick_limit && ! empty( $value ) ) {
						$value = array_slice( $value, 0, $pick_limit );
					}

					if ( empty( $value ) ) {
						// Don't save an empty array, just make it an empty string
						$value = '';
					} elseif ( is_array( $value ) && ( 1 === $pick_limit || 1 === count( $value ) ) ) {
						// If there's just one item, don't save as an array, save the string
						$value = implode( '', $value );
					}
				}

				$is_tableless_field       = in_array( $type, $tableless_field_types, true );
				$is_settings_pod          = 'settings' === $pod['type'];
				$save_non_simple_to_table = $is_tableless_field && ! $simple && ! $is_settings_pod && pods_relationship_table_storage_enabled_for_object_relationships( $field_object, $pod );

				$value_data = null;

				// Pre-process the relationship values.
				if (
					$is_tableless_field
					&& (
						! $simple
						|| $save_non_simple_to_table
					)
				) {
					$value_data = $this->prepare_tableless_data_for_save( $pod, $field_data, $value, compact( $pieces ) );
				}

				// Prepare all table / meta data.
				if ( ! $is_tableless_field || $simple || $save_non_simple_to_table ) {
					if ( in_array( $type, $repeatable_field_types, true ) && 1 === (int) pods_v( $type . '_repeatable', $field_data, 0 ) ) {
						// Don't save an empty array, just make it an empty string
						if ( empty( $value ) ) {
							$value = '';
						} elseif ( is_array( $value ) && 1 === count( $value ) ) {
							// If there's just one item, don't save as an array, save the string
							$value = implode( '', $value );
						}
					}

					$table_save_value = null;

					if ( $value_data ) {
						$value            = $value_data['value_ids'];
						$table_save_value = $value_data['table_save_value'];
					}

					$save_simple_to_table = $simple && ! $is_settings_pod && pods_relationship_table_storage_enabled_for_simple_relationships( $field_object, $pod );
					$save_simple_to_meta  = $simple && ( $is_settings_pod || pods_relationship_meta_storage_enabled_for_simple_relationships( $field_object, $pod ) );

					// Check if we should save to the table, and then check if the field is not a simple relationship OR the simple relationship field is allowed to be saved to the table.
					if ( $save_to_table && ( ! $simple || $save_simple_to_table || $save_non_simple_to_table ) ) {
						$table_data[ $field ] = $value;

						// Use pre-processed table save value if found.
						if ( null !== $table_save_value ) {
							$table_data[ $field ] = $table_save_value;
						}

						// Enforce JSON values for objects/arrays.
						if ( is_object( $table_data[ $field ] ) || is_array( $table_data[ $field ] ) ) {
							$table_data[ $field ] = json_encode( $table_data[ $field ], JSON_UNESCAPED_UNICODE );
						}

						if ( is_string( $table_data[ $field ] ) ) {
							// Fix for pods_query replacements.
							$table_data[ $field ] = str_replace( [ '{prefix}', '@wp_' ], [
								'{/prefix/}',
								'{prefix}'
							], $table_data[ $field ] );
						}

						$table_formats[ $field ] = PodsForm::prepare( $type, $field_object );
					}

					// Check if the field is not a simple relationship OR the simple relationship field is allowed to be saved to meta.
					if ( ! $simple ) {
						// Save fields to meta.
						$object_meta[ $field ] = $value;
					} elseif ( $save_simple_to_meta ) {
						// Save simple to meta.
						$simple_rel_meta[ $field ] = $value;
					}

					$field_save_value = $value;

					if ( $value_data ) {
						$field_save_value = $value_data['field_save_values'];
					}

					$fields_to_run_save[ $field ] = $field_save_value;
				} else {
					// Store relational field data to be looped through later.
					$rel_fields[ $type ][ $field ] = $value_data;
					$rel_field_ids[]               = $field_data['id'];
				}
			}
		}

		if ( in_array( $pod['type'], array( 'post_type', 'taxonomy' ) ) ) {
			$object_name = $pod['name'];

			if ( ! empty( $pod['object'] ) ) {
				$object_name = $pod['object'];
			}

			$object_name_field = 'post_type';

			if ( 'taxonomy' === $pod['type'] ) {
				$object_name_field = 'taxonomy';
			}

			$object_data[ $object_name_field ] = $object_name;

			$has_object_data_to_save = true;
		}

		$is_not_external_pod = ! in_array( $pod['type'], [ 'pod', 'table', '' ], true );

		if ( $is_not_external_pod ) {
			$meta_fields = array();

			if ( 'meta' === $pod['storage'] || 'settings' === $pod['type'] ) {
				$meta_fields = $object_meta;
			}

			// Maybe add simple relationship data to the meta directly regardless if pod is table-based.
			if ( ! empty( $simple_rel_meta ) ) {
				$meta_fields = array_merge( $meta_fields, $simple_rel_meta );
			}

			if ( $allow_custom_fields && ! empty( $custom_data ) ) {
				$meta_fields = array_merge( $custom_data, $meta_fields );
			}

			$fields_to_send = array_flip( array_keys( $meta_fields ) );

			foreach ( $fields_to_send as $field => $field_data ) {
				if ( isset( $object_fields[ $field ] ) ) {
					$field_data = $object_fields[ $field ];
				} elseif ( isset( $fields[ $field ] ) ) {
					$field_data = $fields[ $field ];
				} else {
					unset( $fields_to_send[ $field ] );
				}

				$fields_to_send[ $field ] = $field_data;
			}

			$meta_fields = pods_sanitize( $meta_fields );

			/**
			 * Allow filtering whether to delete values if values are passed as an empty '' string.
			 *
			 * @since 2.8.9
			 *
			 * @param bool $strict_meta_save Whether to delete values if values are passed as an empty '' string.
			 */
			$strict_meta_save = (bool) apply_filters( 'pods_api_save_pod_item_strict_meta_save', false );
		}

		$data_to_filter = [
			'has_object_data_to_save',
			'object_data',
			'object_meta',
			'post_term_data',
			'fields_to_run_save',
			'rel_field_ids',
			'rel_fields',
			'simple_rel_meta',
			'table_data',
			'table_formats',
		];

		$data_to_save = compact( ...$data_to_filter );

		/**
		 * Allow filtering the list of processed data values that will be used to do the final save.
		 *
		 * @since 2.8.22
		 *
		 * @param array $data_to_save The list of processed data values that will be used to do the final save.
		 */
		$data_to_save = (array) apply_filters( 'pods_api_save_pod_item_processed_data_to_save', $data_to_save );

		foreach ( $data_to_filter as $data_var ) {
			// Skip variables not returned in filter.
			if ( ! isset( $data_to_save[ $data_var ] ) ) {
				continue;
			}

			$$data_var = $data_to_save[ $data_var ];
		}

		if ( $is_not_external_pod ) {
			if ( empty( $params->id ) || $has_object_data_to_save || ! empty( $meta_fields ) ) {
				$params->id = $this->save_wp_object( $object_type, $object_data, $meta_fields, $strict_meta_save, true, $fields_to_send );
			}

			if ( ! empty( $params->id ) && 'settings' === $pod['type'] ) {
				$params->id = $pod['id'];
			}
		}

		if ( 'table' === $pod['storage'] ) {
			// Every row should have an id set here, otherwise Pods with nothing
			// but relationship fields won't get their ID properly set.
			if ( empty( $params->id ) ) {
				$params->id = 0;
			}

			$table_formats = array_values( $table_formats );

			$table_data = array( 'id' => $params->id ) + $table_data;
			array_unshift( $table_formats, '%d' );

			if ( ! empty( $table_data ) ) {
				$sql = pods_data()->insert_on_duplicate( "@wp_pods_{$params->pod}", $table_data, $table_formats );

				$id = pods_query( $sql, 'Cannot add/save table row' );

				if ( empty( $params->id ) ) {
					$params->id = $id;
				}
			}
		}

		$params->id = (int) $params->id;

		// Save terms for taxonomies associated to a post type
		if ( 0 < $params->id && 'post_type' === $pod['type'] && ! empty( $post_term_data ) ) {
			foreach ( $post_term_data as $post_taxonomy => $post_terms ) {
				$post_terms = (array) $post_terms;

				foreach ( $post_terms as $k => $v ) {
					if ( ! preg_match( '/[^0-9]/', $v ) ) {
						$v = (int) $v;
					}

					$post_terms[ $k ] = $v;
				}

				$post_terms = array_filter( $post_terms );

				wp_set_object_terms( $params->id, $post_terms, $post_taxonomy );
			}
		}

		$no_conflict = pods_no_conflict_check( $pod['type'] );

		if ( ! $no_conflict ) {
			pods_no_conflict_on( $pod['type'] );
		}

		$all_fields = pods_config_merge_fields( $fields, $object_fields );

		// Handle other save processes based on field type.
		foreach ( $fields_to_run_save as $field_name => $field_save_value ) {
			// Run save function for field type (where needed).
			PodsForm::save( $fields[ $field_name ]['type'], $field_save_value, $params->id, $field_name, $fields[ $field_name ], $all_fields, $pod, $params );
		}

		// Save relationship / file data
		if ( ! empty( $rel_fields ) ) {
			foreach ( $rel_fields as $type => $data ) {
				foreach ( $data as $field_name => $value_data ) {
					$field_data = $fields[ $field_name ];
					$field_id   = $field_data['id'];

					$value_ids         = $value_data['value_ids'];
					$field_save_values = $value_data['field_save_values'];

					$related_data = pods_static_cache_get( $field_name . '/' . $field_id, 'PodsField_Pick/related_data' ) ?: [];

					// Get current values
					if ( 'pick' === $type && isset( $related_data[ 'current_ids_' . $params->id ] ) ) {
						$related_ids = $related_data[ 'current_ids_' . $params->id ];
					} else {
						$related_ids = $this->lookup_related_items( $field_id, $pod['id'], $params->id, $field_data, $pod );
					}

					// Get ids to remove
					$remove_ids = array_diff( $related_ids, $value_ids );

					if ( ! empty( $field_data ) ) {
						// Delete relationships
						if ( ! empty( $remove_ids ) ) {
							$this->delete_relationships( $params->id, $remove_ids, $pod, $field_data );
						}

						// Save relationships
						if ( ! empty( $value_ids ) ) {
							$this->save_relationships( $params->id, $value_ids, $pod, $field_data );
						}
					}

					// Run save function for field type (where needed).
					PodsForm::save( $type, $field_save_values, $params->id, $field_name, $field_data, $all_fields, $pod, $params );
				}

				// Unset data no longer needed
				if ( 'pick' === $type ) {
					foreach ( $data as $field_name => $value_data ) {
						$field_data = $fields[ $field_name ];
						$field_id   = $field_data['id'];

						$related_data = pods_static_cache_get( $field_name . '/' . $field_id, 'PodsField_Pick/related_data' ) ?: [];

						if ( ! empty( $related_data ) ) {
							if ( ! empty( $related_data['related_field'] ) ) {
								pods_static_cache_clear( $related_data['related_field']['name'] . '/' . $related_data['related_field']['id'], 'PodsField_Pick/related_data' );
							}

							pods_static_cache_clear( $field_name . '/' . $field_id, 'PodsField_Pick/related_data' );
						}
					}
				}
			}
		}

		if ( ! $no_conflict ) {
			pods_no_conflict_off( $pod['type'] );
		}

		// Clear cache
		pods_cache_clear( $params->id, 'pods_items_' . $pod['name'] );

		if ( $params->clear_slug_cache && ! empty( $pod['field_slug'] ) ) {
			$slug = pods( $pod['name'], $params->id )->field( $pod['field_slug'] );

			if ( 0 < strlen( $slug ) ) {
				pods_cache_clear( $slug, 'pods_items_' . $pod['name'] );
			}
		}

		// Clear WP meta cache
		if ( in_array( $pod['type'], [ 'post_type', 'media', 'taxonomy', 'user', 'comment' ], true ) ) {
			$meta_type = $pod['type'];

			if ( 'post_type' === $meta_type ) {
				$meta_type = 'post';
			}

			wp_cache_delete( $params->id, $meta_type . '_meta' );
			wp_cache_delete( $params->id, 'pods_' . $meta_type . '_meta' );
		}

		if ( false === $bypass_helpers ) {
			if ( $track_changed_fields ) {
				$changed_fields = self::handle_changed_fields( $params->pod, $params->id, 'get' );
			}

			$compact_pieces = compact( $pieces );

			// Plugin hooks
			$this->do_hook( 'post_save_pod_item', $compact_pieces, $is_new_item, $params->id );
			$this->do_hook( "post_save_pod_item_{$params->pod}", $compact_pieces, $is_new_item, $params->id );

			if ( $is_new_item ) {
				$this->do_hook( 'post_create_pod_item', $compact_pieces, $params->id );
				$this->do_hook( "post_create_pod_item_{$params->pod}", $compact_pieces, $params->id );
			} else {
				$this->do_hook( 'post_edit_pod_item', $compact_pieces, $params->id );
				$this->do_hook( "post_edit_pod_item_{$params->pod}", $compact_pieces, $params->id );
			}
		}

		// Success! Return the id
		return $params->id;

	}

	/**
	 * Prepare tableless (file/relationship) data to be saved.
	 *
	 * @since 2.8.22
	 *
	 * @param Pod|array    $pod    The Pod object.
	 * @param Field|array  $field  The field object.
	 * @param array|string $values The values to be prepared.
	 * @param array        $pieces The pieces used for filtering.
	 *
	 * @return array|null The value_ids, field_save_values, meta_save_values, and table_save_value information used to save relationships data with, or null if field type is not tableless or is a simple relationship.
	 */
	public function prepare_tableless_data_for_save( $pod, $field, $values, $pieces ) {
		global $wpdb;

		$field_type = $field['type'];

		$pods_api = pods_api();

		$tableless_field_types = PodsForm::tableless_field_types();

		if ( ! in_array( $field_type, $tableless_field_types, true ) ) {
			return null;
		}

		// Store relational field data to be looped through later
		// Convert values from a comma-separated string into an array
		if ( ! is_array( $values ) ) {
			if ( is_string( $values ) ) {
				$values = explode( ',', $values );
			} elseif ( is_numeric( $values ) ) {
				$values = [
					$values
				];
			} else {
				$values = [];
			}
		}

		$values = array_filter( $values );

		$simple_tableless_objects = PodsForm::simple_tableless_objects();
		$file_field_types         = PodsForm::file_field_types();

		$is_file_field = in_array( $field_type, $file_field_types, true );

		$search_data     = false;
		$find_rel_params = false;
		$data_mode       = false;
		$is_taggable     = false;

		$is_single = 'single' === pods_v( $field_type . '_format_type', $field );

		if ( ! $is_file_field ) {
			$pick_object = pods_v( 'pick_object', $field );
			$pick_val    = pods_v( 'pick_val', $field );

			if ( 'table' === $pick_object ) {
				$pick_val = pods_v( 'pick_table', $field, $pick_val, true );
			}

			if ( in_array( $pick_object, $simple_tableless_objects, true ) ) {
				return null;
			}

			if ( '__current__' === $pick_val ) {
				if ( is_array( $pod ) || $pod instanceof Pods\Whatsit ) {
					$pick_val = $pod['name'];
				} elseif ( is_object( $pod ) && isset( $pod->pod ) ) {
					$pick_val = $pod->pod;
				} elseif ( is_string( $pod ) && 0 < strlen( $pod ) ) {
					$pick_val = $pod;
				}
			}

			if ( ! $field instanceof Field ) {
				$field['table_info'] = $pods_api->get_table_info( $pick_object, $pick_val, null, null, $field );
			}

			$field_table_info = $field['table_info'];

			if ( ! empty( $field_table_info['pod'] ) && ! empty( $field_table_info['pod']['name'] ) ) {
				$search_data = pods( $field_table_info['pod']['name'] );

				$data_mode = 'pods';
			} else {
				try {
					$search_data = pods_data();
					$search_data->table( $field_table_info );

					$data_mode = 'data';
				} catch ( Exception $exception ) {
					$search_data = false;
				}
			}

			if ( $search_data ) {
				$find_rel_params = [
					'select'     => "`t`.`{$search_data->field_id}`",
					'where'      => "`t`.`{$search_data->field_slug}` = %s OR `t`.`{$search_data->field_index}` = %s",
					'limit'      => 1,
					'pagination' => false,
					'search'     => false
				];

				if ( empty( $search_data->field_slug ) && ! empty( $search_data->field_index ) ) {
					$find_rel_params['where'] = "`t`.`{$search_data->field_index}` = %s";
				} elseif ( empty( $search_data->field_slug ) && empty( $search_data->field_index ) ) {
					$find_rel_params = false;
				}
			}

			$is_taggable = 1 === (int) pods_v( $field_type . '_taggable', $field );
		}

		$related_limit = (int) pods_v( $field_type . '_limit', $field, 0 );

		if ( $is_single ) {
			$related_limit = 1;
		}

		// Enforce integers / unique values for IDs
		$value_ids = array();

		// @todo Handle simple relationships eventually
		foreach ( $values as $v ) {
			if ( ! is_array( $v ) ) {
				if ( ! preg_match( '/[^\D]/', $v ) ) {
					$v = (int) $v;
				} elseif ( $is_file_field ) {
					// File handling
					// Get ID from GUID
					$v = pods_image_id_from_field( $v );

					// If file not found, add it
					if ( empty( $v ) ) {
						try {
							$v = pods_attachment_import( $v );
						} catch ( Exception $exception ) {
							continue;
						}
					}
				} elseif ( $search_data ) {
					// Reference by slug
					$v_data = false;

					if ( false !== $find_rel_params ) {
						$rel_params          = $find_rel_params;
						$rel_params['where'] = $wpdb->prepare( $rel_params['where'], array( $v, $v ) );

						$search_data->select( $rel_params );

						$v_data = $search_data->fetch( $v );
					}

					if ( ! empty( $v_data ) && isset( $v_data[ $search_data->field_id ] ) ) {
						$v = (int) $v_data[ $search_data->field_id ];
					} elseif ( $is_taggable && 'pods' === $data_mode ) {
						// Allow tagging for Pods objects
						$tag_data = array(
							$search_data->field_index => $v
						);

						if ( 'post_type' === $search_data->pod_data['type'] ) {
							$tag_data['post_status'] = 'publish';
						}

						/**
						 * Filter for changing tag before adding new item.
						 *
						 * @param array  $tag_data    Fields for creating new item.
						 * @param int    $v           Field ID of tag.
						 * @param Pods   $search_data Search object for tag.
						 * @param string $field_name  Table info for field.
						 * @param array  $pieces      Field array.
						 *
						 * @since 2.3.19
						 */
						$tag_data = apply_filters( 'pods_api_save_pod_item_taggable_data', $tag_data, $v, $search_data, $field, $pieces );

						// Save $v to a new item on related object
						$v = $search_data->add( $tag_data );

						// @todo Support non-Pods for tagging
					}
				}
			} elseif ( $is_file_field && isset( $v['id'] ) ) {
				$v = (int) $v['id'];
			} else {
				continue;
			}

			if ( ! empty( $v ) && ! in_array( $v, $value_ids, true ) ) {
				$value_ids[] = $v;
			}
		}

		$value_ids = array_unique( array_filter( $value_ids ) );

		// Filter unique values not equal to false in case of a multidimensional array
		$filtered_values          = $this->array_filter_walker( $values );
		$serialized_values        = array_map( 'serialize', $filtered_values );
		$unique_serialized_values = array_unique( $serialized_values );

		$values = array_map( 'unserialize', $unique_serialized_values );

		// Limit values
		if ( 0 < $related_limit && ! empty( $value_ids ) ) {
			$value_ids = array_slice( $value_ids, 0, $related_limit );
			$values    = array_slice( $values, 0, $related_limit );
		}

		$field_save_values = $value_ids;

		if ( $is_file_field ) {
			$field_save_values = $values;
		}

		$meta_save_values = $value_ids;
		$table_save_value = $is_single ? 0 : '[]';

		if ( ! empty( $value_ids ) ) {
			$table_save_value = json_encode( $value_ids, JSON_UNESCAPED_UNICODE );

			if ( $is_single ) {
				$table_save_value = reset( $value_ids );
			}
		}

		return compact( 'value_ids', 'field_save_values', 'meta_save_values', 'table_save_value' );
	}

	/**
	 * @see   PodsAPI::save_pod_item
	 * Add multiple pod items
	 *
	 * $params['pod'] string The Pod name (pod or pod_id is required)
	 * $params['pod_id'] string The Pod ID (pod or pod_id is required)
	 * $params['bypass_helpers'] bool Set to true to bypass running pre-save and post-save helpers
	 *
	 * $data['id'] int The item ID (optional)
	 * $data['data'] array An associative array of field names + values
	 *
	 * @param array|object $params An associative array of parameters, data excluded
	 * @param array        $data   An associative array of pod ids and field names + values (arrays of field data)
	 *
	 * @return int The item ID
	 * @since 2.0.0
	 */
	public function save_pod_items( $params, $data ) {

		$params = (object) $params;

		$ids = array();

		foreach ( $data as $fields ) {
			$params->data = $fields;

			if ( isset( $fields['id'] ) && isset( $fields['data'] ) ) {
				$params->id   = $fields['id'];
				$params->data = $fields['data'];
			}

			$ids[] = $this->save_pod_item( $params );
		}

		return $ids;
	}

	/**
	 * Handle tracking changed fields or get them.
	 *
	 * @since 2.7.0
	 *
	 * @param string $pod
	 * @param int    $id
	 * @param string $mode
	 *
	 * @return array List of changed fields (if $mode = 'get')
	 */
	public static function handle_changed_fields( $pod, $id, $mode = 'set' ) {
		$first_pods_version = get_option( 'pods_framework_version_first' );
		$first_pods_version = '' === $first_pods_version ? PODS_VERSION : $first_pods_version;

		$watch_changed_fields = (int) pods_get_setting( 'watch_changed_fields', version_compare( $first_pods_version, '2.8.21', '<=' ) ? 1 : 0 );

		// Only continue if changed fields are watched.
		if ( 0 === $watch_changed_fields ) {
			return [];
		}

		$changed_pods_cache   = pods_static_cache_get( 'changed_pods_cache', __CLASS__ ) ?: [];
		$old_fields_cache     = pods_static_cache_get( 'old_fields_cache', __CLASS__ ) ?: [];
		$changed_fields_cache = pods_static_cache_get( 'changed_fields_cache', __CLASS__ ) ?: [];

		$cache_key = $pod . '|' . $id;

		$export_params = array(
			'depth' => 1,
		);

		if ( in_array( $mode, array( 'set', 'reset' ), true ) ) {
			if ( isset( $changed_fields_cache[ $cache_key ] ) ) {
				unset( $changed_fields_cache[ $cache_key ] );
			}

			if ( empty( $old_fields_cache[ $cache_key ] ) || 'reset' === $mode ) {
				$old_fields_cache[ $cache_key ] = [];

				if ( ! empty( $id ) ) {
					if ( ! isset( $changed_pods_cache[ $pod ] ) ) {
						$pod_object = pods( $pod );

						if ( ! $pod_object || ! $pod_object->is_defined() ) {
							return [];
						}

						$changed_pods_cache[ $pod ] = $pod_object;
					}

					if ( $changed_pods_cache[ $pod ] ) {
						if ( $changed_pods_cache[ $pod ]->fetch( $id ) ) {
							$old_fields_cache[ $cache_key ] = $changed_pods_cache[ $pod ]->export( $export_params );
						}
					}
				}
			}
		}

		$changed_fields = array();

		if ( isset( $changed_fields_cache[ $cache_key ] ) ) {
			$changed_fields = $changed_fields_cache[ $cache_key ];
		} elseif ( isset( $old_fields_cache[ $cache_key ] ) ) {
			$old_fields = $old_fields_cache[ $cache_key ];

			if ( 'get' === $mode ) {
				$changed_fields_cache[ $cache_key ] = array();

				if ( ! empty( $changed_pods_cache[ $pod ] ) ) {
					if ( $id != $changed_pods_cache[ $pod ]->id() ) {
						$found = $changed_pods_cache[ $pod ]->fetch( $id );

						if ( ! $found ) {
							return [];
						}
					}

					$new_fields = $changed_pods_cache[ $pod ]->export( $export_params );

					foreach ( $new_fields as $field => $value ) {
						if ( ! isset( $old_fields[ $field ] ) || $value != $old_fields[ $field ] ) {
							$changed_fields[ $field ] = $value;
						}
					}

					$changed_fields_cache[ $cache_key ] = $changed_fields;
				}
			}
		}

		pods_static_cache_set( 'changed_pods_cache', $changed_pods_cache, __CLASS__ );
		pods_static_cache_set( 'old_fields_cache', $old_fields_cache, __CLASS__ );
		pods_static_cache_set( 'changed_fields_cache', $changed_fields_cache, __CLASS__ );

		return $changed_fields;

	}

	/**
	 * Get the fields that have changed during a save
	 *
	 * @param array $pieces Pieces array from save_pod_item
	 *
	 * @return array Array of fields and values that have changed
	 *
	 * @deprecated 2.7.0 Use PodsAPI::handle_changed_fields
	 */
	public function get_changed_fields( $pieces ) {
		_deprecated_function( __METHOD__, '2.7.0', 'PodsAPI::handle_changed_fields' );

		return self::handle_changed_fields( $pieces['params']->pod, $pieces['params']->id, 'get' );
	}

	/**
	 * Save relationships
	 *
	 * @param int                       $id          ID of item.
	 * @param int|array                 $related_ids ID(s) for items to save.
	 * @param array|Pod   $pod         The Pod object.
	 * @param array|Field $field       The Field object.
	 *
	 * @return array List of ID(s) that were setup for saving.
	 */
	public function save_relationships( $id, $related_ids, $pod, $field ) {
		$related_data = pods_static_cache_get( $field['name'] . '/' . $field['id'], 'PodsField_Pick/related_data' ) ?: [];

		// Get current values
		if ( 'pick' === $field['type'] && isset( $related_data[ 'current_ids_' . $id ] ) ) {
			$current_ids = $related_data[ 'current_ids_' . $id ];
		} else {
			$current_ids = $this->lookup_related_items( $field['id'], $pod['id'], $id, $field, $pod );
		}

		$cache_key = $pod['id'] . '|' . $field['id'];

		// Delete relationship from cache.
		pods_static_cache_clear( $cache_key, __CLASS__ . '/related_item_cache' );

		if ( ! is_array( $related_ids ) ) {
			$related_ids = implode( ',', $related_ids );
		}

		foreach ( $related_ids as $k => $related_id ) {
			$related_ids[ $k ] = (int) $related_id;
		}

		$related_ids = array_unique( array_filter( $related_ids ) );

		$related_limit = (int) pods_v( $field['type'] . '_limit', $field, 0 );

		if ( 'single' === pods_v( $field['type'] . '_format_type', $field ) ) {
			$related_limit = 1;
		}

		// Limit values
		if ( 0 < $related_limit && ! empty( $related_ids ) ) {
			$related_ids = array_slice( $related_ids, 0, $related_limit );
		}

		$no_conflict = pods_no_conflict_check( $pod['type'] );

		if ( ! $no_conflict ) {
			pods_no_conflict_on( $pod['type'] );
		}

		// Post Types, Media, Users, and Comments (meta-based)
		if ( pods_relationship_meta_storage_enabled( $field, $pod ) && in_array( $pod['type'], [
				'post_type',
				'media',
				'taxonomy',
				'user',
				'comment',
			], true ) ) {
			$object_type = $pod['type'];

			if ( in_array( $object_type, [ 'post_type', 'media' ], true ) ) {
				$object_type = 'post';
			} elseif ( 'taxonomy' === $object_type ) {
				$object_type = 'term';
			}

			delete_metadata( $object_type, $id, $field['name'] );

			if ( ! empty( $related_ids ) ) {
				update_metadata( $object_type, $id, '_pods_' . $field['name'], $related_ids );

				foreach ( $related_ids as $related_id ) {
					add_metadata( $object_type, $id, $field['name'], $related_id );
				}
			} else {
				delete_metadata( $object_type, $id, '_pods_' . $field['name'] );
			}
		} elseif ( 'settings' === $pod['type'] ) {
			// Custom Settings Pages (options-based)
			if ( ! empty( $related_ids ) ) {
				update_option( $pod['name'] . '_' . $field['name'], $related_ids );
			} else {
				delete_option( $pod['name'] . '_' . $field['name'] );
			}
		}

		$related_pod_id   = 0;
		$related_field_id = 0;

		if ( 'pick' === $field['type'] && ! empty( $related_data['related_field'] ) ) {
			$related_pod_id   = $related_data['related_pod']['id'];
			$related_field_id = $related_data['related_field']['id'];
		}

		// Relationships table
		if ( pods_podsrel_enabled() ) {
			$related_weight = 0;

			foreach ( $related_ids as $related_id ) {
				if ( in_array( $related_id, $current_ids ) ) {
					pods_query( '
						UPDATE `@wp_podsrel`
						SET
							`pod_id` = %d,
							`field_id` = %d,
							`item_id` = %d,
							`related_pod_id` = %d,
							`related_field_id` = %d,
							`related_item_id` = %d,
							`weight` = %d
						WHERE
							`pod_id` = %d
							AND `field_id` = %d
							AND `item_id` = %d
							AND `related_item_id` = %d
					', array(
						$pod['id'],
						$field['id'],
						$id,
						$related_pod_id,
						$related_field_id,
						$related_id,
						$related_weight,

						$pod['id'],
						$field['id'],
						$id,
						$related_id,
					) );
				} else {
					pods_query( '
						INSERT INTO `@wp_podsrel`
							(
								`pod_id`,
								`field_id`,
								`item_id`,
								`related_pod_id`,
								`related_field_id`,
								`related_item_id`,
								`weight`
							)
						VALUES ( %d, %d, %d, %d, %d, %d, %d )
					', array(
						$pod['id'],
						$field['id'],
						$id,
						$related_pod_id,
						$related_field_id,
						$related_id,
						$related_weight,
					) );
				}

				$related_weight ++;
			}
		}

		/**
		 * Allow custom saving actions for relationships.
		 *
		 * @since 2.8.0
		 *
		 * @param int         $id          ID of item.
		 * @param array       $related_ids ID(s) for items to save.
		 * @param array|Pod   $pod         The Pod object.
		 * @param array|Field $field       The Field object.
		 */
		do_action( 'pods_api_save_relationships', $id, $related_ids, $field, $pod );

		if ( ! $no_conflict ) {
			pods_no_conflict_off( $pod['type'] );
		}

		return $related_ids;
	}

	/**
	 * Duplicate a Pod.
	 *
	 * $params['id'] int The Pod ID.
	 * $params['name'] string The Pod name.
	 * $params['new_name'] string The new Pod name.
	 *
	 * @since 2.3.0
	 *
	 * @param array $params An associative array of parameters.
	 * @param bool  $strict (optional) Makes sure a pod exists, if it doesn't throws an error.
	 *
	 * @return int|false New Group ID or false if not successful.
	 */
	public function duplicate_pod( $params, $strict = false ) {

		if ( ! is_object( $params ) && ! is_array( $params ) ) {
			if ( is_numeric( $params ) ) {
				$params = array( 'id' => $params );
			} else {
				$params = array( 'name' => $params );
			}

			$params = (object) pods_sanitize( $params );
		} else {
			$params = (object) pods_sanitize( $params );
		}

		$pod = $this->load_pod( $params, false );

		if ( empty( $pod ) ) {
			if ( false !== $strict ) {
				return pods_error( __( 'Pod not found', 'pods' ), $this );
			}

			return false;
		} elseif ( in_array( $pod['type'], array( 'media', 'user', 'comment' ) ) ) {
			if ( false !== $strict ) {
				return pods_error( __( 'Pod not allowed to be duplicated', 'pods' ), $this );
			}

			return false;
		} elseif ( in_array( $pod['type'], array( 'post_type', 'taxonomy' ) ) && 0 < strlen( $pod['object'] ) ) {
			$pod['object'] = '';
		}

		if ( $pod instanceof Pod ) {
			$pod = $pod->export(
				[
					'include_groups' => true,
					'include_fields' => false,
				]
			);
		}

		if ( isset( $params->new_name ) ) {
			$pod['name'] = $params->new_name;
		}

		$try = 1;

		$check_name = $pod['name'];
		$new_label  = $pod['label'];

		while ( $this->load_pod( array( 'name' => $check_name ), false ) ) {
			$try ++;

			$check_name = $pod['name'] . $try;
			$new_label  = $pod['label'] . $try;
		}

		$pod['name']  = $check_name;
		$pod['label'] = $new_label;

		$groups = $pod['groups'];

		unset( $pod['id'], $pod['parent'], $pod['object_type'], $pod['object_storage_type'], $pod['groups'] );

		try {
			$pod_id = $this->save_pod( $pod );
		} catch ( Exception $exception ) {
			// Pod not saved.
			return false;
		}

		if ( ! is_int( $pod_id ) ) {
			// Pod not saved.
			return false;
		}

		$pod = $this->load_pod( [ 'id' => $pod_id ] );

		foreach ( $groups as $group_data ) {
			$fields = $group_data['fields'];

			unset( $group_data['id'], $group_data['parent'], $group_data['object_type'], $group_data['object_storage_type'], $group_data['fields'] );

			$group_data['pod_data'] = $pod;

			try {
				$group_id = $this->save_group( $group_data );
			} catch ( Exception $exception ) {
				// Group not saved.
				continue;
			}

			if ( ! is_int( $group_id ) ) {
				// Group not saved.
				continue;
			}

			$group = $this->load_group( [ 'id' => $group_id ] );

			foreach ( $fields as $field_data ) {
				unset( $field_data['id'], $field_data['parent'], $field_data['object_type'], $field_data['object_storage_type'], $field_data['group'] );

				$field_data['pod_data'] = $pod;
				$field_data['group']    = $group;

				try {
					$this->save_field( $field_data );
				} catch ( Exception $exception ) {
					// Field not saved.
				}
			}
		}

		return $pod_id;
	}

	/**
	 * Duplicate a Group.
	 *
	 * $params['id'] int The Group ID.
	 * $params['name'] string The Group name.
	 * $params['new_name'] string The new Group name.
	 *
	 * @since 2.8.0
	 *
	 * @param array $params An associative array of parameters.
	 * @param bool  $strict (optional) Makes sure a group exists, if it doesn't throws an error.
	 *
	 * @return int|false New Group ID or false if not successful.
	 */
	public function duplicate_group( $params, $strict = false ) {

		if ( ! is_object( $params ) && ! is_array( $params ) ) {
			if ( is_numeric( $params ) ) {
				$params = array( 'id' => $params );
			} else {
				$params = array( 'name' => $params );
			}

			$params = (object) pods_sanitize( $params );
		} else {
			$params = (object) pods_sanitize( $params );
		}

		if ( ! empty( $params->pod_id ) ) {
			$load_params['parent'] = $params->pod_id;
		} elseif ( ! empty( $params->pod ) ) {
			$load_params['pod'] = $params->pod;
		}

		$group = $this->load_group( $params, false );

		if ( empty( $group ) ) {
			if ( false !== $strict ) {
				return pods_error( __( 'Group not found', 'pods' ), $this );
			}

			return false;
		}

		if ( $group instanceof Group ) {
			$group = $group->export(
				[
					'include_fields' => true,
				]
			);
		}

		unset( $group['id'] );

		if ( isset( $params->new_name ) ) {
			$group['name'] = $params->new_name;
		}

		$try = 1;

		$check_name = $group['name'];
		$new_label  = $group['label'];

		while ( $this->load_group( array( 'name' => $check_name ), false ) ) {
			$try ++;

			$check_name = $group['name'] . $try;
			$new_label  = $group['label'] . $try;
		}

		$group['name']  = $check_name;
		$group['label'] = $new_label;

		$fields = $group['fields'];

		unset( $group['id'], $group['parent'], $group['object_type'], $group['object_storage_type'], $group['fields'] );

		try {
			$group_id = $this->save_group( $group );
		} catch ( Exception $exception ) {
			return false;
		}

		if ( ! is_int( $group_id ) ) {
			return false;
		}

		foreach ( $fields as $field => $field_data ) {
			unset( $field_data['id'], $field_data['parent'], $field_data['object_type'], $field_data['object_storage_type'], $field_data['group'] );

			$field_data['group_id'] = $group_id;

			try {
				$this->save_field( $field_data );
			} catch ( Exception $exception ) {
				// Field not saved.
			}
		}

		return $group_id;
	}

	/**
	 * Duplicate a Field.
	 *
	 * $params['pod_id'] int The Pod ID.
	 * $params['pod'] string The Pod name.
	 * $params['id'] int The Field ID.
	 * $params['name'] string The Field name.
	 * $params['new_name'] string The new Field name.
	 *
	 * @since 2.3.10
	 *
	 * @param array $params An associative array of parameters.
	 * @param bool  $strict (optional) Makes sure a field exists, if it doesn't throws an error.
	 *
	 * @return int|false New Field ID or false if not successful.
	 */
	public function duplicate_field( $params, $strict = false ) {

		if ( ! is_object( $params ) && ! is_array( $params ) ) {
			if ( is_numeric( $params ) ) {
				$params = array( 'id' => $params );
			} else {
				$params = array( 'name' => $params );
			}
		}

		$params = (object) pods_sanitize( $params );

		$load_params = array();

		if ( ! empty( $params->pod_id ) ) {
			$load_params['parent'] = $params->pod_id;
		} elseif ( ! empty( $params->pod ) ) {
			$load_params['pod'] = $params->pod;
		}

		if ( ! empty( $params->id ) ) {
			$load_params['id'] = $params->id;
		} elseif ( ! empty( $params->name ) ) {
			$load_params['name'] = $params->name;
		}

		$field = $this->load_field( $load_params, $strict );

		if ( empty( $field ) ) {
			if ( false !== $strict ) {
				return pods_error( __( 'Field not found', 'pods' ), $this );
			}

			return false;
		}

		if ( $field instanceof Field ) {
			$field = $field->export();
		}

		if ( isset( $params->new_name ) ) {
			$field['name'] = $params->new_name;
		}

		$try = 1;

		$check_name = $field['name'];
		$new_label  = $field['label'];

		while ( $this->load_field( array(
			'parent' => $field['pod_id'],
			'name'   => $check_name,
		), false ) ) {
			$try ++;

			$check_name = $field['name'] . $try;
			$new_label  = $field['label'] . $try;
		}

		$field['name']  = $check_name;
		$field['label'] = $new_label;

		unset( $field['id'], $field['object_type'], $field['object_storage_type'] );

		return $this->save_field( $field, true, true );

	}

	/**
	 * @see   PodsAPI::save_pod_item
	 *
	 * Duplicate a pod item
	 *
	 * $params['pod'] string The Pod name
	 * $params['id'] int The item's ID from the wp_pods_* table
	 *
	 * @param array $params An associative array of parameters
	 *
	 * @return int The table row ID
	 *
	 * @since 1.12
	 */
	public function duplicate_pod_item( $params ) {

		$params = (object) pods_sanitize( $params );

		$load_pod_params = array(
			'name' => $params->pod,
		);

		$pod = $this->load_pod( $load_pod_params, false );

		if ( false === $pod ) {
			return pods_error( __( 'Pod not found', 'pods' ), $this );
		}

		$pod = pods( $params->pod, $params->id );

		$params->pod    = $pod->pod;
		$params->pod_id = $pod->pod_id;

		$fields        = (array) pods_v( 'fields', $pod->pod_data, [], true );
		$object_fields = (array) pods_v( 'object_fields', $pod->pod_data, [], true );

		if ( ! empty( $object_fields ) ) {
			$fields = array_merge( $object_fields, $fields );
		}

		$save_params = array(
			'pod'         => $params->pod,
			'data'        => array(),
			'is_new_item' => true,
		);

		$ignore_fields = array(
			$pod->pod_data['field_id'],
			$pod->pod_data['field_slug'],
		);

		if ( in_array( $pod->pod_data['type'], array( 'post_type', 'media' ), true ) ) {
			$ignore_fields = array(
				'ID',
				'post_name',
				'post_date',
				'post_date_gmt',
				'post_modified',
				'post_modified_gmt',
				'guid',
			);
		} elseif ( 'term' === $pod->pod_data['type'] ) {
			$ignore_fields = array(
				'term_id',
				'term_taxonomy_id',
				'slug',
			);
		} elseif ( 'user' === $pod->pod_data['type'] ) {
			$ignore_fields = array(
				'ID',
				'user_nicename',
			);
		} elseif ( 'comment' === $pod->pod_data['type'] ) {
			$ignore_fields = array(
				'comment_ID',
			);
		} elseif ( 'settings' === $pod->pod_data['type'] ) {
			return pods_error( __( 'Settings do not support duplication.', 'pods' ), $this );
		}

		/**
		 * Filter the fields to ignore during duplication
		 *
		 * @since 2.6.6
		 *
		 * @param array  $ignore_fields Fields to ignore and not save during duplication
		 * @param Pods   $pod           Pod object
		 * @param array  $fields        Fields on the pod to duplicate
		 * @param object $params        Params passed into duplicate_pod_item()
		 */
		$ignore_fields = apply_filters( 'pods_api_duplicate_pod_item_ignore_fields', $ignore_fields, $pod, $fields, $params );

		foreach ( $fields as $field ) {
			if ( in_array( $field['name'], $ignore_fields ) ) {
				continue;
			}

			$field = array(
				'name'   => $field['name'],
				'output' => 'ids'
			);

			$value = $pod->field( $field );

			// @todo Add post type compatibility to set unique post_title
			// @todo Add term compatibility to set unique name
			// @todo Add user compatibility to set unique user_login/user_email

			if ( ! empty( $value ) || ( ! is_array( $value ) && 0 < strlen( $value ) ) ) {
				$save_params['data'][ $field['name'] ] = $value;
			}
		}

		$save_params = $this->do_hook( 'duplicate_pod_item', $save_params, $pod->pod, $pod->id(), $params );

		$id = $this->save_pod_item( $save_params );

		return $id;

	}

	/**
	 * @see   pods()
	 *
	 * Export a pod item
	 *
	 * $params['pod'] string The Pod name
	 * $params['id'] int The item's ID from the wp_pods_* table
	 * $params['fields'] array The fields to export
	 * $params['depth'] int How many levels deep to export data
	 *
	 * @param array  $params An associative array of parameters
	 * @param object $pod    (optional) Pods object
	 *
	 * @return int The table row ID
	 * @since 1.12
	 */
	public function export_pod_item( $params, $pod = null ) {

		if ( ! is_object( $pod ) || 'Pods' !== get_class( $pod ) ) {
			if ( empty( $params ) ) {
				return false;
			}

			if ( is_object( $params ) ) {
				$params = get_object_vars( (object) $params );
			}

			$params = pods_sanitize( $params );

			$pod = pods( $params['pod'], $params['id'], false );

			if ( empty( $pod ) ) {
				return false;
			}
		}

		$params['fields']        = (array) pods_v( 'fields', $params, array(), true );
		$params['depth']         = (int) pods_v( 'depth', $params, 2, true );
		$params['object_fields'] = (array) pods_v( 'object_fields', $pod->pod_data, array(), true );
		$params['flatten']       = (boolean) pods_v( 'flatten', $params, false, true );
		$params['context']       = pods_v( 'context', $params, null, true );

		if ( empty( $params['fields'] ) ) {
			$params['fields'] = array_merge( $pod->fields, $params['object_fields'] );
		}

		$data = $this->export_pod_item_level( $pod, $params );

		$data = $this->do_hook( 'export_pod_item', $data, $pod->pod, $pod->id(), $pod, $params['fields'], $params['depth'], $params['flatten'], $params );

		return $data;
	}

	/**
	 * Export a pod item by depth level
	 *
	 * @param Pods  $pod    Pods object
	 * @param array $params Export params
	 *
	 * @return array Data array
	 *
	 * @since 2.3.0
	 */
	private function export_pod_item_level( $pod, $params ) {

		$fields        = $params['fields'];
		$depth         = $params['depth'];
		$flatten       = $params['flatten'];
		$current_depth = pods_v( 'current_depth', $params, 1, true );
		$context       = $params['context'];

		$tableless_field_types    = PodsForm::tableless_field_types();
		$simple_tableless_objects = PodsForm::simple_tableless_objects();

		$object_fields = (array) is_object( $pod->pod_data ) ? $pod->pod_data->get_object_fields() : pods_v( 'object_fields', $pod->pod_data, [], true );

		$export_fields = array();

		$pod_type = $pod->pod_data['type'];

		if ( 'post_type' === $pod_type ) {
			$pod_type = 'post';
		} elseif ( 'taxonomy' === $pod_type ) {
			$pod_type = 'term';
		}

		$registered_meta_keys = false;

		if ( function_exists( 'get_registered_meta_keys' ) ) {
			$registered_meta_keys = get_registered_meta_keys( $pod_type );
		}

		$show_in_rest = false;

		// If in rest, check if this pod can be exposed
		if ( 'rest' === $context ) {
			$read_all = (int) pods_v( 'read_all', $pod->pod_data, 0 );

			if ( 1 === $read_all ) {
				$show_in_rest = true;
			}
		}

		foreach ( $fields as $k => $field ) {
			$is_field_object = $field instanceof Field;

			if ( ! is_array( $field ) && ! $is_field_object ) {
				$field = array(
					'id'   => 0,
					'name' => $field
				);
			}

			if ( isset( $pod->fields[ $field['name'] ] ) ) {
				// If in rest, check if this field can be exposed
				if ( 'rest' === $context && false === $show_in_rest ) {
					$show_in_rest = PodsRESTFields::field_allowed_to_extend( $field['name'], $pod, 'read' );

					if ( false === $show_in_rest ) {
						// Fallback to checking $registered_meta_keys
						if ( false !== $registered_meta_keys ) {
							if ( ! isset( $registered_meta_keys[ $field['name'] ] ) ) {
								continue;
							} elseif ( empty( $registered_meta_keys[ $field['name'] ]['show_in_rest'] ) ) {
								continue;
							}
						}
					}
				}

				$field                = $pod->fields( $field['name'] );
				$field['lookup_name'] = $field['name'];

				$field_type = pods_v( 'type', $field, 'text' );

				if ( in_array( $field_type, $tableless_field_types, true ) && ! in_array( pods_v( 'pick_object', $field ), $simple_tableless_objects, true ) ) {
					if ( 'pick' === $field_type ) {
						if ( empty( $field['table_info'] ) ) {
							$field['table_info'] = $this->get_table_info( pods_v( 'pick_object', $field ), pods_v( 'pick_val', $field ), null, null, $field );
						}

						if ( ! empty( $field['table_info'] ) && 'table' !== $field['table_info']['object_type'] ) {
							$field['lookup_name'] .= '.' . $field['table_info']['field_id'];
						}
					} elseif ( in_array( $field_type, PodsForm::file_field_types(), true ) ) {
						$field['lookup_name'] .= '.guid';
					}
				}

				$export_fields[ $field['name'] ] = $field;
			} elseif ( isset( $object_fields[ $field['name'] ] ) ) {
				$field                = $object_fields[ $field['name'] ];
				$field['lookup_name'] = $field['name'];

				$export_fields[ $field['name'] ] = $field;
			} elseif ( $field['name'] === $pod->pod_data['field_id'] ) {
				$field['type']        = 'number';
				$field['lookup_name'] = $field['name'];

				$export_fields[ $field['name'] ] = $field;
			}
		}

		$data = array();

		$is_strict_mode = pods_strict( false );

		/**
		 * Allow filtering whether to export IDs at the final depth, set to false to return the normal object data.
		 *
		 * @since 2.8.6
		 *
		 * @param bool  $export_ids_at_final_depth Whether to export IDs at the final depth, set to false to return the normal object data.
		 * @param Pods  $pod                       Pods object.
		 * @param array $params                    Export params.
		 */
		$export_ids_at_final_depth = (bool) apply_filters( 'pods_api_export_pod_item_level_export_ids_at_final_depth', $is_strict_mode, $pod, $params );

		/**
		 * Allow filtering whether to export relationships as JSON compatible.
		 *
		 * @since 2.8.6
		 *
		 * @param bool  $export_as_json_compatible Whether to export relationships as JSON compatible.
		 * @param Pods  $pod                       Pods object.
		 * @param array $params                    Export params.
		 */
		$export_as_json_compatible = (bool) apply_filters( 'pods_api_export_pod_item_level_export_as_json', $is_strict_mode || pods_doing_json(), $pod, $params );

		foreach ( $export_fields as $field ) {
			// Return IDs (or guid for files) if only one level deep
			if ( 1 === $depth || ( $export_ids_at_final_depth && $current_depth === $depth ) ) {
				$data[ $field['name'] ] = $pod->field( array( 'name' => $field['lookup_name'], 'output' => 'arrays' ) );

				if ( $export_as_json_compatible && is_array( $data[ $field['name'] ] ) ) {
					$data[ $field['name'] ] = array_values( $data[ $field['name'] ] );
				}
			} elseif ( ( - 1 === $depth || $current_depth < $depth ) && 'pick' === $field['type'] && ! in_array( pods_v( 'pick_object', $field ), $simple_tableless_objects, true ) ) {
				// Recurse depth levels for pick fields if $depth allows
				$related_data = array();

				$related_ids = $pod->field( array( 'name' => $field['name'], 'output' => 'ids' ) );

				if ( ! empty( $related_ids ) ) {
					$related_ids = (array) $related_ids;

					$pick_object = pods_v( 'pick_object', $field );
					$pick_val    = pods_v( 'pick_val', $field );

					$related_pod = pods( $pick_val, null, false );

					// If this isn't a Pod, return data exactly as Pods does normally
					if ( empty( $related_pod ) || empty( $related_pod->pod_data ) || ( 'pod' !== $pick_object && $pick_object !== $related_pod->pod_data['type'] ) || $related_pod->pod === $pod->pod ) {
						$related_data = $pod->field( array( 'name' => $field['name'], 'output' => 'arrays' ) );
					} else {
						$related_object_fields = (array) pods_v( 'object_fields', $related_pod->pod_data, [], true );

						$related_fields = array_merge( $related_pod->fields, $related_object_fields );

						foreach ( $related_ids as $related_id ) {
							if ( $related_pod->fetch( $related_id ) ) {
								$related_params = array(
									'fields'        => $related_fields,
									'depth'         => $depth,
									'flatten'       => $flatten,
									'current_depth' => $current_depth + 1,
									'context'       => $context,
								);

								$related_item = $this->export_pod_item_level( $related_pod, $related_params );

								$related_item_data = $this->do_hook( 'export_pod_item_level', $related_item, $related_pod->pod, $related_pod->id(), $related_pod, $related_fields, $depth, $flatten, ( $current_depth + 1 ), $params );

								if ( $export_as_json_compatible ) {
									// Don't pass IDs as keys for REST API context to ensure arrays of data are returned.
									$related_data[] = $related_item_data;
								} else {
									$related_data[ $related_id ] = $related_item_data;
								}
							}
						}

						if ( $flatten && ! empty( $related_data ) ) {
							$related_data = pods_serial_comma( array_values( $related_data ), array(
								'and'         => '',
								'field_index' => $related_pod->pod_data['field_index']
							) );
						}
					}
				}

				$data[ $field['name'] ] = $related_data;
			} else {
				// Return data exactly as Pods does normally
				$data[ $field['name'] ] = $pod->field( array( 'name' => $field['name'], 'output' => 'arrays' ) );
			}

			if ( $flatten && is_array( $data[ $field['name'] ] ) ) {
				$data[ $field['name'] ] = pods_serial_comma( $data[ $field['name'] ], array(
					'field'  => $field['name'],
					'fields' => $export_fields,
					'and'    => ''
				) );
			}
		}

		$data['id'] = (int) $pod->id();

		return $data;
	}

	/**
	 * Reorder a Pod
	 *
	 * $params['pod'] string The Pod name
	 * $params['field'] string The field name of the field to reorder
	 * $params['order'] array The key => value array of items to reorder (key should be an integer)
	 *
	 * @param array $params An associative array of parameters
	 *
	 * @return bool
	 *
	 * @since 1.9.0
	 */
	public function reorder_pod_item( $params ) {

		$params = (object) pods_sanitize( $params );

		if ( null === pods_v( 'pod', $params, null, true ) ) {
			return pods_error( __( '$params->pod is required', 'pods' ), $this );
		}

		if ( ! is_array( $params->order ) ) {
			$params->order = explode( ',', $params->order );
		}

		$pod = $this->load_pod( array( 'name' => $params->pod ), false );

		if ( false === $pod ) {
			return pods_error( __( 'Pod is required', 'pods' ), $this );
		}

		$params->name = $pod['name'];

		foreach ( $params->order as $order => $id ) {
			if ( isset( $pod['fields'][ $params->field ] ) || isset( $pod['object_fields'][ $params->field ] ) ) {
				if ( 'table' === $pod['storage'] && ! pods_tableless() ) {
					if ( isset( $pod['fields'][ $params->field ] ) ) {
						pods_query( "UPDATE `@wp_pods_{$params->name}` SET `{$params->field}` = " . pods_absint( $order ) . " WHERE `id` = " . pods_absint( $id ) . " LIMIT 1" );
					} else {
						pods_query( "UPDATE `{$pod['table']}` SET `{$params->field}` = " . pods_absint( $order ) . " WHERE `{$pod['field_id']}` = " . pods_absint( $id ) . " LIMIT 1" );
					}
				} else {
					$this->save_pod_item( array(
						'pod'    => $params->pod,
						'pod_id' => $params->pod_id,
						'id'     => $id,
						'data'   => array( $params->field => pods_absint( $order ) )
					) );
				}
			}
		}

		return true;
	}

	/**
	 *
	 * Delete all content for a Pod
	 *
	 * $params['id'] int The Pod ID
	 * $params['name'] string The Pod name
	 *
	 * @param array $params An associative array of parameters
	 * @param array $pod    Pod data
	 *
	 * @return bool
	 *
	 * @uses  pods_query
	 * @uses  pods_cache_clear
	 *
	 * @since 1.9.0
	 */
	public function reset_pod( $params, $pod = false ) {
		if ( empty( $pod ) ) {
			$pod = $this->load_pod( $params, false );
		}

		if ( false === $pod ) {
			return pods_error( __( 'Pod not found', 'pods' ), $this );
		}

		if ( is_array( $params ) || is_object( $params ) ) {
			$params = (object) pods_sanitize( $params );
		} else {
			$params = new stdClass();
		}

		$params->id   = (int) $pod['id'];
		$params->name = $pod['name'];

		if ( ! pods_tableless() ) {
			if ( 'table' === $pod['storage'] ) {
				try {
					pods_query( "TRUNCATE `@wp_pods_{$params->name}`", false );
				} catch ( Exception $e ) {
					// Allow pod to be reset if the table doesn't exist
					if ( false === strpos( $e->getMessage(), 'Unknown table' ) ) {
						return pods_error( $e->getMessage(), $this );
					}
				}
			}

		}

		if ( pods_podsrel_enabled() ) {
			pods_query( "DELETE FROM `@wp_podsrel` WHERE `pod_id` = {$params->id} OR `related_pod_id` = {$params->id}", false );
		}

		// @todo Delete relationships from tableless relationships

		if ( in_array( $pod['type'], [ 'post_type', 'media' ], true ) ) {
			// Delete all posts/revisions from this post type.
			$type = $pod['name'];

			if ( ! empty( $pod['object'] ) ) {
				$type = $pod['object'];
			}

			$type = pods_sanitize( $type );

			$sql = "
				DELETE `t`, `r`, `m`
				FROM `{$pod['table']}` AS `t`
				LEFT JOIN `{$pod['meta_table']}` AS `m`
					ON `m`.`{$pod['meta_field_id']}` = `t`.`{$pod['field_id']}`
				LEFT JOIN `{$pod['table']}` AS `r`
					ON `r`.`post_parent` = `t`.`{$pod['field_id']}` AND `r`.`post_status` = 'inherit'
				WHERE `t`.`{$pod['field_type']}` = '{$type}'
			";

			pods_query( $sql, false );
		} elseif ( 'taxonomy' === $pod['type'] ) {
			// Delete all terms from this taxonomy.
			if ( function_exists( 'get_term_meta' ) ) {
				$sql = "
					DELETE `t`, `m`, `tt`, `tr`
					FROM `{$pod['table']}` AS `t`
					LEFT JOIN `{$pod['meta_table']}` AS `m`
						ON `m`.`{$pod['meta_field_id']}` = `t`.`{$pod['field_id']}`
					" . $pod['join']['tt'] . "
					" . $pod['join']['tr'] . "
					WHERE " . implode( ' AND ', $pod['where'] ) . "
				";
			} else {
				$sql = "
					DELETE `t`, `tt`, `tr`
					FROM `{$pod['table']}` AS `t`
					" . $pod['join']['tt'] . "
					" . $pod['join']['tr'] . "
					WHERE " . implode( ' AND ', $pod['where'] ) . "
				";
			}

			pods_query( $sql, false );
		} elseif ( 'user' === $pod['type'] ) {
			// Delete all users except the current one
			$sql = "
				DELETE `t`, `m`
				FROM `{$pod['table']}` AS `t`
				LEFT JOIN `{$pod['meta_table']}` AS `m`
					ON `m`.`{$pod['meta_field_id']}` = `t`.`{$pod['field_id']}`
				WHERE `t`.`{$pod['field_id']}` != " . (int) get_current_user_id() . "
			";

			pods_query( $sql, false );
		} elseif ( 'comment' === $pod['type'] ) {
			// Delete all comments
			$type = $pod['name'];

			if ( ! empty( $pod['object'] ) ) {
				$type = $pod['object'];
			}

			$type = pods_sanitize( $type );

			$sql = "
				DELETE `t`, `m`
				FROM `{$pod['table']}` AS `t`
				LEFT JOIN `{$pod['meta_table']}` AS `m`
					ON `m`.`{$pod['meta_field_id']}` = `t`.`{$pod['field_id']}`
				WHERE `t`.`{$pod['field_type']}` = '{$type}'
			";

			pods_query( $sql, false );
		}

		pods_cache_clear( true ); // only way to reliably clear out cached data across an entire group

		return true;
	}

	/**
	 * Delete a Pod and all its content
	 *
	 * $params['id'] int The Pod ID
	 * $params['name'] string The Pod name
	 *
	 * @param array|string|int $params     An associative array of parameters, the pod name, or pod ID.
	 * @param bool             $strict     (optional) Makes sure a pod exists, if it doesn't throws an error
	 * @param bool             $delete_all (optional) Whether to delete all content from a WP object
	 *
	 * @uses  PodsAPI::load_pod
	 * @uses  wp_delete_post
	 * @uses  pods_query
	 *
	 * @return bool
	 * @since 1.7.9
	 */
	public function delete_pod( $params, $strict = false, $delete_all = false ) {
		$pod = $this->load_pod( $params, false );

		if ( empty( $pod ) ) {
			if ( false !== $strict ) {
				return pods_error( __( 'Pod not found', 'pods' ), $this );
			}

			return false;
		}

		/**
		 * @var $wpdb wpdb
		 */
		global $wpdb;

		if ( is_array( $params ) || is_object( $params ) ) {
			$params = (object) pods_sanitize( $params );
		} else {
			$params = new stdClass();
		}

		$params->id   = (int) $pod['id'];
		$params->name = $pod['name'];

		$type = $pod['name'];

		if ( ! empty( $pod['object'] ) ) {
			$type = $pod['object'];
		}

		if ( ! isset( $params->delete_all ) ) {
			$params->delete_all = $delete_all;
		}

		$params->delete_all = (boolean) $params->delete_all;

		// Reset content
		if ( true === $params->delete_all ) {
			$this->reset_pod( $params, $pod );
		}

		foreach ( $pod['fields'] as $field ) {
			$delete_field = array(
				'id'     => $field->get_id(),
				'name'   => $field->get_name(),
				'pod'    => $pod,
			);

			$this->delete_field( $delete_field, false );
		}

        $object_collection = Pods\Whatsit\Store::get_instance();

        /** @var Pods\Whatsit\Storage\Post_Type $post_type_storage */
        $post_type_storage = $object_collection->get_storage_object( $this->get_default_object_storage_type() );

        $object = $post_type_storage->to_object( $params->id );

        $success = false;

        if ( $object ) {
	        $success = $post_type_storage->delete( $object );
        }

		if ( ! $success ) {
			return pods_error( __( 'Pod unable to be deleted', 'pods' ), $this );
		}

		// @todo Push this logic into pods_object_storage_delete_pod action.
		if ( ! pods_tableless() ) {
			if ( 'table' === $pod['storage'] ) {
				try {
					pods_query( "DROP TABLE IF EXISTS `@wp_pods_{$params->name}`", false );
				} catch ( Exception $e ) {
					// Allow pod to be deleted if the table doesn't exist
					if ( false === strpos( $e->getMessage(), 'Unknown table' ) ) {
						return pods_error( $e->getMessage(), $this );
					}
				}
			}

			if ( pods_podsrel_enabled() ) {
				pods_query( "DELETE FROM `@wp_podsrel` WHERE `pod_id` = {$params->id} OR `related_pod_id` = {$params->id}", false );
			}
		}

		// @todo Delete relationships from tableless relationships

		// Delete any relationship references
		$sql = "
			DELETE `pm`
			FROM `{$wpdb->postmeta}` AS `pm`
			LEFT JOIN `{$wpdb->posts}` AS `p`
				ON `p`.`post_type` = '_pods_field'
					AND `p`.`ID` = `pm`.`post_id`
			LEFT JOIN `{$wpdb->postmeta}` AS `pm2`
				ON `pm2`.`meta_key` = 'pick_object'
					AND `pm2`.`meta_value` = 'pod'
					AND `pm2`.`post_id` = `pm`.`post_id`
			WHERE
				`p`.`ID` IS NOT NULL
				AND `pm2`.`meta_id` IS NOT NULL
				AND `pm`.`meta_key` = 'pick_val'
				AND `pm`.`meta_value` = '{$type}'
		";

		pods_query( $sql );

		$this->cache_flush_pods( $pod );

		return true;
	}

	/**
	 * Drop a field within a Pod
	 *
	 * $params['id'] int The field ID
	 * $params['name'] int The field name
	 * $params['pod'] string The Pod name
	 * $params['pod_id'] string The Pod name
	 *
	 * @param array|object|Field $params An associative array or object of parameters, or the Field object itself.
	 * @param bool  $table_operation                   Whether or not to handle table operations.
	 *
	 * @uses  PodsAPI::load_field
	 * @uses  wp_delete_post
	 * @uses  pods_query
	 *
	 * @return bool
	 * @since 1.7.9
	 */
	public function delete_field( $params, $table_operation = true ) {

		/**
		 * @var $wpdb wpdb
		 */
		global $wpdb;

		$tableless_field_types    = PodsForm::tableless_field_types();
		$simple_tableless_objects = PodsForm::simple_tableless_objects();
		$field                    = null;
		$pod                      = null;

		// Check if the params is a field.
		if ( $params instanceof Field ) {
			$field = $params;
			$pod   = $field->get_parent_object();

			$params = [
				'name'   => $field->get_name(),
				'id'     => $field->get_id(),
				'pod'    => $field->get_parent(),
				'pod_id' => $field->get_parent_id(),
			];
		}

		$params = (object) $params;

		if ( ! isset( $params->pod ) ) {
			$params->pod = '';
		}

		if ( ! isset( $params->pod_id ) ) {
			$params->pod_id = 0;
		}

		if ( ! $pod ) {
			$pod = $params->pod;
		}

		$save_pod = false;

		if ( ! ( is_array( $pod ) || $pod instanceof Pods\Whatsit ) ) {
			$load_params = array();

			if ( ! empty( $params->pod_id ) ) {
				$load_params['id'] = $params->pod_id;
			} elseif ( is_int( $pod ) && ! empty( $pod ) ) {
				$load_params['id'] = $pod;
			} elseif ( ! empty( $params->pod ) ) {
				$load_params['name'] = $params->pod;
			} elseif ( is_string( $pod ) && 0 < strlen( $pod ) ) {
				$load_params['name'] = $pod;
			}

			$pod = false;

			if ( $load_params ) {
				$pod = $this->load_pod( $load_params, false );
			}
		} else {
			$save_pod = true;
		}

		if ( empty( $pod ) && empty( $params->id ) ) {
			return pods_error( __( 'Pod not found', 'pods' ), $this );
		}

		if ( $pod ) {
			$params->pod_id = $pod['id'];
			$params->pod    = $pod['name'];
		}

		$params = pods_sanitize( $params );

		if ( ! isset( $params->name ) ) {
			$params->name = '';
		}

		if ( ! isset( $params->id ) ) {
			$params->id = 0;
		}

		if ( ! $field ) {
			$load_params = [];

			if ( $params->pod_id ) {
				$load_params['parent'] = $params->pod_id;
			}

			if ( ! empty( $params->id ) ) {
				$load_params['id'] = $params->id;
			} elseif ( ! empty( $params->name ) ) {
				$load_params['name'] = $params->name;
			}

			$field = $this->load_field( $load_params );

			if ( false === $field ) {
				return pods_error( __( 'Field not found', 'pods' ), $this );
			}
		}
		$params->id     = $field['id'];
		$params->name   = $field['name'];

		// Get the pod from the field if pod information not provided.
		if ( false === $pod ) {
			$pod = $field->get_parent_object();

			if ( $pod ) {
				$params->pod_id = $pod['id'];
				$params->pod    = $pod['name'];
			}
		}

		$simple = ( 'pick' === $field['type'] && in_array( pods_v( 'pick_object', $field ), $simple_tableless_objects, true ) );
		$simple = (boolean) $this->do_hook( 'tableless_custom', $simple, $field, $pod, $params );

		// @todo Push this logic into pods_object_storage_delete_pod action.
		if ( $table_operation && $pod && 'table' === $pod['storage'] && ( ! in_array( $field['type'], $tableless_field_types, true ) || $simple ) ) {
			pods_query( "ALTER TABLE `@wp_pods_{$params->pod}` DROP COLUMN `{$params->name}`", false );
		}

        $object_collection = Pods\Whatsit\Store::get_instance();

        /** @var Pods\Whatsit\Storage\Post_Type $post_type_storage */
        $post_type_storage = $object_collection->get_storage_object( $this->get_default_object_storage_type() );

        $success = false;

        if ( $post_type_storage ) {
	        $object = $post_type_storage->to_object( $params->id );

	        if ( $object ) {
		        $success = $post_type_storage->delete( $object );
	        }
        }

		if ( ! $success ) {
			return pods_error( __( 'Field unable to be deleted', 'pods' ), $this );
		}

		// @todo Push this logic into pods_object_storage_delete_pod action.
		$wpdb->query( $wpdb->prepare( "DELETE pm FROM {$wpdb->postmeta} AS pm
			LEFT JOIN {$wpdb->posts} AS p
				ON p.post_type = '_pods_field' AND p.ID = pm.post_id
			WHERE p.ID IS NOT NULL AND pm.meta_key = 'sister_id' AND pm.meta_value = %d", $params->id ) );

		if ( $table_operation && pods_podsrel_enabled() ) {
			pods_query( "DELETE FROM `@wp_podsrel` WHERE (`pod_id` = {$params->pod_id} AND `field_id` = {$params->id}) OR (`related_pod_id` = {$params->pod_id} AND `related_field_id` = {$params->id})", false );
		}

		// @todo Delete tableless relationship meta

		if ( ! $save_pod ) {
			$this->cache_flush_pods( $pod );
		}

		return true;
	}

	/**
	 * Delete a Pod and all its content
	 *
	 * $params['id'] int The Group ID
	 * $params['name'] string The Group name
	 * $params['pod'] string|Pods\Whatsit\Pod The Pod name or object.
	 * $params['pod_id'] string The Pod ID.
	 *
	 * @since 2.8.0
	 *
	 * @param array|string|int $params     An associative array of parameters, the pod name, or pod ID.
	 * @param bool             $strict     (optional) Makes sure a group exists, if it doesn't throws an error.
	 * @param bool             $delete_all (optional) Whether to delete all fields from the group too.
	 *
	 * @return bool Whether the group was deleted successfully.
	 */
	public function delete_group( $params, $strict = false, $delete_all = false ) {
		/**
		 * @var $wpdb wpdb
		 */
		global $wpdb;

		if ( ! is_object( $params ) && ! is_array( $params ) ) {
			if ( is_numeric( $params ) ) {
				$params = [ 'id' => $params ];
			} else {
				$params = [ 'name' => $params ];
			}

			$params = (object) pods_sanitize( $params );
		} else {
			$params = (object) pods_sanitize( $params );
		}

		if ( ! isset( $params->delete_all ) ) {
			$params->delete_all = (boolean) $delete_all;
		}

		$group = $this->load_group( $params, false );

		if ( empty( $group ) ) {
			if ( false !== $strict ) {
				return pods_error( __( 'Group not found', 'pods' ), $this );
			}

			return false;
		}

		$pod = $group->get_parent();

		$params->id   = (int) $group['id'];
		$params->name = $group['name'];

		// Delete all fields.
		if ( true === $params->delete_all ) {
			foreach ( $group['fields'] as $field ) {
				$delete_field = [
					'id'   => $field->get_id(),
					'name' => $field->get_name(),
					'pod'  => $pod,
				];

				$this->delete_field( $delete_field, false );
			}
		}

		$object_collection = Pods\Whatsit\Store::get_instance();

		/** @var Pods\Whatsit\Storage\Post_Type $post_type_storage */
		$post_type_storage = $object_collection->get_storage_object( $this->get_default_object_storage_type() );

		$object = $post_type_storage->to_object( $params->id );

		$success = false;

		if ( $object ) {
			$success = $post_type_storage->delete( $object );
		}

		if ( ! $success ) {
			return pods_error( __( 'Group unable to be deleted', 'pods' ), $this );
		}

		return true;
	}

	/**
	 * Drop a Pod Object
	 *
	 * $params['id'] int The object ID
	 * $params['name'] string The object name
	 * $params['type'] string The object type
	 *
	 * @param array|object $params An associative array of parameters
	 *
	 * @uses  wp_delete_post
	 *
	 * @return bool
	 * @since 2.0.0
	 */
	public function delete_object( $params ) {
		$object = $this->load_object( $params );

		if ( empty( $object ) ) {
			return pods_error( sprintf( esc_html__( '%s Object not found', 'pods' ), ucwords( $params->type ) ), $this );
		}

        $object_collection = Pods\Whatsit\Store::get_instance();

        /** @var Pods\Whatsit\Storage\Post_Type $post_type_storage */
        $post_type_storage = $object_collection->get_storage_object( $this->get_default_object_storage_type() );

        $object = $post_type_storage->to_object( $params->id );

        $success = false;

        if ( $object ) {
	        $success = $post_type_storage->delete( $object );
        }

		if ( ! $success ) {
			return pods_error( sprintf( esc_html__( '%s Object not deleted', 'pods' ), ucwords( $params->type ) ), $this );
		}

		pods_transient_clear( 'pods_objects_' . $params->type );

		return true;
	}

	/**
	 * @see   PodsAPI::delete_object
	 *
	 * Drop a Pod Template
	 *
	 * $params['id'] int The template ID
	 * $params['name'] string The template name
	 *
	 * @param array $params An associative array of parameters
	 *
	 * @return bool
	 * @since 1.7.9
	 */
	public function delete_template( $params ) {
		$params       = (object) $params;
		$params->type = 'template';

		return $this->delete_object( $params );
	}

	/**
	 * @see   PodsAPI::delete_object
	 *
	 * Drop a Pod Page
	 *
	 * $params['id'] int The page ID
	 * $params['uri'] string The page URI
	 *
	 * @param array $params An associative array of parameters
	 *
	 * @return bool
	 * @since 1.7.9
	 */
	public function delete_page( $params ) {
		$params = (object) $params;

		if ( isset( $params->uri ) ) {
			$params->name = $params->uri;

			unset( $params->uri );
		}

		if ( isset( $params->name ) ) {
			$params->name = trim( $params->name, '/' );
		}

		$params->type = 'page';

		return $this->delete_object( $params );
	}

	/**
	 * @see   PodsAPI::delete_object
	 *
	 * Drop a Pod Helper
	 *
	 * $params['id'] int The helper ID
	 * $params['name'] string The helper name
	 *
	 * @param array $params An associative array of parameters
	 *
	 * @return bool
	 * @since 1.7.9
	 *
	 * @deprecated since 2.8.0
	 */
	public function delete_helper( $params ) {
		return false;
	}

	/**
	 * Drop a single pod item
	 *
	 * $params['id'] int (optional) The item's ID from the wp_pod_* table (used with datatype parameter)
	 * $params['pod'] string (optional) The Pod name (used with id parameter)
	 * $params['pod_id'] int (optional) The Pod ID (used with id parameter)
	 * $params['bypass_helpers'] bool Set to true to bypass running pre-save and post-save helpers
	 *
	 * @param array $params An associative array of parameters
	 * @param bool  $wp     Whether to run WP object delete action
	 *
	 * @return bool
	 * @since 1.7.9
	 */
	public function delete_pod_item( $params, $wp = true ) {

		$params = (object) $params;

		if ( ! isset( $params->id ) ) {
			return pods_error( __( 'Pod Item not found', 'pods' ), $this );
		}

		$params->id = pods_absint( $params->id );

		if ( ! isset( $params->pod ) ) {
			$params->pod = '';
		}

		if ( ! isset( $params->pod_id ) ) {
			$params->pod_id = 0;
		}

		if ( ! isset( $params->strict ) ) {
			$params->strict = true;
		}

		$pod = $this->load_pod( array( 'name' => $params->pod, 'id' => $params->pod_id ), false );

		if ( false === $pod ) {
			if ( $params->strict ) {
				return pods_error( __( 'Pod not found', 'pods' ), $this );
			}

			return false;
		}

		$params->pod_id = $pod['id'];
		$params->pod    = $pod['name'];

		$params = pods_sanitize( $params );

		// Allow Helpers to bypass subsequent helpers in recursive delete_pod_item calls
		$bypass_helpers = false;

		if ( isset( $params->bypass_helpers ) && false !== $params->bypass_helpers ) {
			$bypass_helpers = true;
		}

		$pre_delete_helpers  = array();
		$post_delete_helpers = array();

		if ( false === $bypass_helpers ) {
			// Plugin hook
			$this->do_hook( 'pre_delete_pod_item', $params, $pod );
			$this->do_hook( "pre_delete_pod_item_{$params->pod}", $params, $pod );

			// Call any pre-save helpers (if not bypassed)
			if ( ! defined( 'PODS_DISABLE_EVAL' ) || ! PODS_DISABLE_EVAL ) {
				if ( ! empty( $pod ) ) {
					$helpers = array( 'pre_delete_helpers', 'post_delete_helpers' );

					foreach ( $helpers as $helper ) {
						if ( isset( $pod[ $helper ] ) && ! empty( $pod[ $helper ] ) ) {
							${$helper} = explode( ',', $pod[ $helper ] );
						}
					}
				}
			}
		}

		// Delete object from relationship fields
		$this->delete_object_from_relationships( $params->id, $pod );

		if ( 'table' === $pod['storage'] ) {
			pods_query( "DELETE FROM `@wp_pods_{$params->pod}` WHERE `id` = {$params->id} LIMIT 1" );
		}

		if ( $wp ) {
			if ( 'taxonomy' === $pod['type'] ) {
				$taxonomy = $pod['name'];

				if ( ! empty( $pod['object'] ) ) {
					$taxonomy = $pod['object'];
				}

				wp_delete_term( $params->id, $taxonomy );
			} elseif ( ! in_array( $pod['type'], array( 'pod', 'table', '', 'taxonomy' ) ) ) {
				$this->delete_wp_object( $pod['type'], $params->id );
			}
		}

		if ( false === $bypass_helpers ) {
			// Plugin hook
			$this->do_hook( 'post_delete_pod_item', $params, $pod );
			$this->do_hook( "post_delete_pod_item_{$params->pod}", $params, $pod );
		}

		pods_cache_clear( $params->id, 'pods_items_' . $params->pod );

		return true;
	}

	/**
	 * Delete an object from tableless fields.
	 *
	 * @param int    $id
	 * @param string $type
	 * @param string $name
	 *
	 * @return bool
	 *
	 * @since 2.3.0
	 */
	public function delete_object_from_relationships( $id, $object, $name = null ) {

		/**
		 * @var $pods_init \PodsInit
		 * @todo Use pods_init() function?
		 */
		global $pods_init;

		$pod = false;

		// Run any bidirectional delete operations
		if ( is_array( $object ) || $object instanceof Pods\Whatsit ) {
			$pod = $object;
		} elseif ( is_object( $pods_init ) ) {
			$pod = PodsInit::$meta->get_object( $object, $name );
		}

		if ( ! empty( $pod ) ) {
			$object = $pod['type'];
			$name   = $pod['name'];

			foreach ( $pod['fields'] as $field ) {
				PodsForm::delete( $field['type'], $id, $field['name'], $field, $pod );
			}
		}

		// Lookup related fields (non-bidirectional)
		$params = array(
			'args' => array(
				'type' => 'pick',
				'pick_object' => $object,
			),
		);

		if ( ! empty( $name ) && $name !== $object ) {
			$params['args']['pick_val'] = $name;
		}

		try {
			$fields = $this->load_fields( $params );

			if ( ! empty( $pod ) && 'media' === $pod['type'] ) {
				$params['args']['type'] = 'file';

				$fields = pods_config_merge_fields( $fields, $this->load_fields( $params ) );
			}

			if ( is_array( $fields ) && ! empty( $fields ) ) {
				foreach ( $fields as $related_field ) {
					$related_pod = $this->load_pod( [ 'id' => $related_field['pod_id'] ], false );

					if ( empty( $related_pod ) ) {
						continue;
					}

					$related_from = $this->lookup_related_items_from( $related_field['id'], $related_pod['id'], $id, $related_field, $related_pod );

					$this->delete_relationships( $related_from, $id, $related_pod, $related_field );
				}
			}
		} catch ( Exception $exception ) {
			// Nothing left to do here.
		}

		if ( ! empty( $pod ) ) {
			if ( pods_podsrel_enabled() ) {
				pods_query( '
					DELETE FROM `@wp_podsrel`
					WHERE
					(
						`pod_id` = %d
						AND `item_id` = %d
					)
					OR (
						`related_pod_id` = %d
						AND `related_item_id` = %d
					)
				', [
					$pod['id'],
					$id,

					$pod['id'],
					$id,
				] );
			}

			/**
			 * Allow custom deletion actions for relationships.
			 *
			 * @since 2.8.0
			 *
			 * @param int                     $id  ID to remove.
			 * @param array|Pod $pod The Pod object.
			 */
			do_action( 'pods_api_delete_object_from_relationships', $id, $pod );
		}

		return true;
	}

	/**
	 * Handle deletion of relationship data.
	 *
	 * @since 2.3.0
	 *
	 * @param int|array   $related_id    ID(s) for items to save.
	 * @param int|array   $id            ID(s) to remove.
	 * @param array|Pod   $related_pod   The related Pod object.
	 * @param array|Field $related_field The related Field object.
	 * @param bool        $force         Whether to force the deletion, even if found related IDs not set or matching.
	 */
	public function delete_relationships( $related_id, $id, $related_pod, $related_field, $force = true ) {

		if ( is_array( $related_id ) ) {
			foreach ( $related_id as $rid ) {
				$this->delete_relationships( $rid, $id, $related_pod, $related_field );
			}

			return;
		}

		if ( is_array( $id ) ) {
			foreach ( $id as $rid ) {
				$this->delete_relationships( $related_id, $rid, $related_pod, $related_field );
			}

			return;
		}

		$id = (int) $id;

		if ( empty( $id ) ) {
			return;
		}

		$related_ids = $this->lookup_related_items( $related_field['id'], $related_pod['id'], $related_id, $related_field, $related_pod );

		if ( ! $force ) {
			if ( empty( $related_ids ) ) {
				return;
			} elseif ( ! in_array( $id, $related_ids ) ) {
				return;
			}
		}

		$cache_key = $related_pod['id'] . '|' . $related_field['id'];

		// Delete relationship from cache.
		pods_static_cache_clear( $cache_key, __CLASS__ . '/related_item_cache' );

		// @codingStandardsIgnoreLine
		$key = array_search( $id, $related_ids );

		if ( false !== $key ) {
			unset( $related_ids[ $key ] );
		}

		$no_conflict = pods_no_conflict_check( $related_pod['type'] );

		if ( ! $no_conflict ) {
			pods_no_conflict_on( $related_pod['type'] );
		}

		// Post Types, Media, Users, and Comments (meta-based)
		if ( pods_relationship_meta_storage_enabled( $related_field, $related_pod ) && in_array( $related_pod['type'], [
				'post_type',
				'media',
				'taxonomy',
				'user',
				'comment',
			], true ) ) {
			$object_type = $related_pod['type'];

			if ( in_array( $object_type, [ 'post_type', 'media' ], true ) ) {
				$object_type = 'post';
			} elseif ( 'taxonomy' === $object_type ) {
				$object_type = 'term';
			}

			delete_metadata( $object_type, $related_id, $related_field['name'] );

			if ( ! empty( $related_ids ) ) {
				update_metadata( $object_type, $related_id, '_pods_' . $related_field['name'], $related_ids );

				foreach ( $related_ids as $rel_id ) {
					add_metadata( $object_type, $related_id, $related_field['name'], $rel_id );
				}
			} else {
				delete_metadata( $object_type, $related_id, '_pods_' . $related_field['name'] );
			}
		} elseif ( 'settings' === $related_pod['type'] ) {
			// Custom Settings Pages (options-based)
			if ( ! empty( $related_ids ) ) {
				update_option( $related_pod['name'] . '_' . $related_field['name'], $related_ids );
			} else {
				delete_option( $related_pod['name'] . '_' . $related_field['name'] );
			}
		}

		// Relationships table
		if ( pods_podsrel_enabled() ) {
			pods_query( '
				DELETE FROM `@wp_podsrel`
				WHERE
				(
					`pod_id` = %d
					AND `field_id` = %d
					AND `item_id` = %d
					AND `related_item_id` = %d
				)
				OR (
					`related_pod_id` = %d
					AND `related_field_id` = %d
					AND `related_item_id` = %d
					AND `item_id` = %d
				)
			', array(
				$related_pod['id'],
				$related_field['id'],
				$related_id,
				$id,

				$related_pod['id'],
				$related_field['id'],
				$related_id,
				$id,
			) );
		}

		/**
		 * Allow custom deletion actions for relationships.
		 *
		 * @since 2.8.0
		 *
		 * @param int         $related_id    ID for item to save.
		 * @param int         $id            ID to remove.
		 * @param array|Pod   $related_pod   The related Pod object.
		 * @param array|Field $related_field The related Field object.
		 * @param array       $related_ids   The full list of related IDs for the item, with the related item already removed.
		 */
		do_action( 'pods_api_delete_relationships', $related_id, $id, $related_field, $related_pod, $related_ids );

		if ( ! $no_conflict ) {
			pods_no_conflict_off( $related_pod['type'] );
		}
	}

	/**
	 * Check if a Pod exists
	 *
	 * $params['id'] int Pod ID
	 * $params['name'] string Pod name
	 *
	 * @param array $params An associative array of parameters
	 *
	 * @return bool True if exists
	 *
	 * @since 1.12
	 */
	public function pod_exists( $params, $type = null ) {

		if ( is_string( $params ) ) {
			$params = array( 'name' => $params );
		}

		$params = (object) pods_sanitize( $params );

		if ( ! empty( $params->id ) || ! empty( $params->name ) ) {
			if ( ! isset( $params->name ) ) {
				$dummy = (int) $params->id;
				$pod   = get_post( $dummy );
			} else {
				$pod = get_posts( array(
					'name'           => $params->name,
					'post_type'      => '_pods_pod',
					'posts_per_page' => 1
				) );

				if ( is_array( $pod ) && ! empty( $pod[0] ) ) {
					$pod = $pod[0];
				}
			}

			if ( ! empty( $pod ) && ( empty( $type ) || $type == get_post_meta( $pod->ID, 'type', true ) ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get number of pods for a specific pod type
	 *
	 * @param string $type Type to get count
	 *
	 * @return int Total number of pods for a type
	 *
	 * @since 2.6.6
	 */
	public function get_pod_type_count( $type ) {

		$args = array(
			'post_type'      => '_pods_pod',
			'posts_per_page' => - 1,
			'nopaging'       => true,
			'fields'         => 'ids',
			'meta_query'     => array(
				array(
					'key'   => 'type',
					'value' => $type,
				),
			),
		);

		$posts = get_posts( $args );

		return count( $posts );

	}

	/**
	 * Load a Pod.
	 *
	 * @param array|int|WP_Post|string $params       {
	 *                                               An associative array of parameters.
	 *
	 * @type int                       $id           The Pod ID.
	 * @type string                    $name         The Pod name.
	 * @type boolean                   $bypass_cache Bypass the cache when getting data.
	 * }
	 *
	 * @param bool                     $strict       Makes sure the pod exists, throws an error if it doesn't work.
	 *
	 * @return Pods\Whatsit\Pod|false Pod object or false if not found.
	 *
	 * @throws Exception
	 * @since 1.7.9
	 */
	public function load_pod( $params, $strict = false ) {
		if ( $params instanceof Pod ) {
			return $params;
		}

		if ( $params instanceof WP_Post ) {
			return $this->get_pods_object_from_wp_post( $params );
		}

		if ( is_numeric( $params ) ) {
			$params = [
				'id' => $params,
			];
		} elseif ( is_string( $params ) ) {
			$params = [
				'name' => $params,
			];
		}

		// Backwards compatibility handling.
		if ( is_object( $params ) ) {
			$params = get_object_vars( (object) $params );
		}

		if ( empty( $params ) ) {
			return false;
		}

		// Check if we need to bypass cache automatically.
		if ( ! isset( $params['bypass_cache'] ) ) {
			$api_cache = pods_api_cache();

			if ( ! $api_cache ) {
				$params['bypass_cache'] = true;
			}
		}

		if ( isset( $params['fields'] ) ) {
			unset( $params['fields'] );
		}

		if ( isset( $params['table_info'] ) ) {
			unset( $params['table_info'] );
		}

		$params['object_type']      = 'pod';
		$params['include_internal'] = true;

		if ( isset( $params['name'] ) && '' === $params['name'] ) {
			unset( $params['name'] );
		}

		if ( isset( $params['id'] ) && in_array( $params['id'], array( '', 0 ), true ) ) {
			unset( $params['id'] );
		}

		try {
			$object = $this->_load_object( $params );

			if ( $object ) {
				return $object;
			}
		} catch ( Exception $exception ) {
			// Return error below.
		}

		if ( $strict ) {
			return pods_error( __( 'Pod not found', 'pods' ), $this );
		}

		return false;
	}

	/**
	 * Load a list of Pods based on filters specified.
	 *
	 * @param array       $params       {
	 *                                  An associative array of parameters
	 *
	 * @type string|array $type         Pod type(s) to filter by.
	 * @type string|array $id           ID(s) of Objects.
	 * @type array        $args         Args(s) key=>value array to filter by.
	 * @type boolean      $count        Return only a count of pods.
	 * @type boolean      $names        Return only an array of name => label.
	 * @type boolean      $ids          Return only an array of ID => label.
	 * @type boolean      $bypass_cache Bypass the cache when getting data.
	 * }
	 *
	 * @return Pods\Whatsit\Pod[]|int List of pod objects or count.
	 *
	 * @throws Exception
	 *
	 * @since 2.0.0
	 */
	public function load_pods( $params = [] ) {
		// Backwards compatibility handling.
		if ( is_object( $params ) ) {
			$params = get_object_vars( (object) $params );
		}

		$include_internal = false;

		if ( isset( $params['include_internal'] ) ) {
			$include_internal = (boolean) $params['include_internal'];

			unset( $params['include_internal'] );
		}

		if ( ! $include_internal ) {
			$params['internal'] = false;
		}

		if ( isset( $params['fields'] ) ) {
			unset( $params['fields'] );
		}

		if ( isset( $params['table_info'] ) ) {
			unset( $params['table_info'] );
		}

		if ( isset( $params['object_fields'] ) ) {
			unset( $params['object_fields'] );
		}

		// Backcompat handling.
		if ( ! empty( $params['ids'] ) ) {
			$params['id'] = $params['ids'];

			unset( $params['ids'] );
		}

		$params['object_type'] = 'pod';

		return $this->_load_objects( $params );
	}

	/**
	 * Check if a Pod's field exists
	 *
	 * $params['pod_id'] int The Pod ID
	 * $params['id'] int The field ID
	 * $params['name'] string The field name
	 *
	 * @param array   $params   An associative array of parameters
	 * @param boolean $allow_id Whether to allow the ID when checking if the group exists.
	 *
	 * @return bool
	 *
	 * @since 1.12
	 */
	public function field_exists( $params, $allow_id = true ) {
		$params = (object) $params;

		$allowed = [
			'name',
			'pod_id',
			'pod',
		];

		if ( $allow_id ) {
			$allowed[] = 'id';
		}

		$load_params = [];

		foreach ( $allowed as $param ) {
			if ( ! isset( $params->{$param} ) ) {
				continue;
			}

			$load_params[ $param ] = $params->{$param};
		}

		try {
			return (boolean) $this->load_field( $load_params );
		} catch ( Exception $exception ) {
			return false;
		}
	}

	/**
	 * Load a field.
	 *
	 * @param array|int $params       {
	 *                                An associative array of parameters.
	 *
	 * @type int        $pod_id       The Pod ID.
	 * @type string     $pod          The Pod name.
	 * @type int        $id           The field ID.
	 * @type string     $name         The field name.
	 * @type boolean    $bypass_cache Bypass the cache when getting data.
	 * }
	 *
	 * @param boolean   $strict       Whether to require a field exist or not when loading the info.
	 *
	 * @return Pods\Whatsit\Field|bool Field object or false if not found.
	 *
	 * @throws Exception
	 * @throws Exception
	 * @since 1.7.9
	 */
	public function load_field( $params, $strict = false ) {
		if ( $params instanceof Field ) {
			return $params;
		}

		if ( $params instanceof WP_Post ) {
			return $this->get_pods_object_from_wp_post( $params );
		}

		if ( is_numeric( $params ) ) {
			$params = [
				'id' => $params,
			];
		} elseif ( is_string( $params ) ) {
			$params = [
				'name' => $params,
			];
		}

		// Backwards compatibility handling.
		if ( is_object( $params ) ) {
			$params = get_object_vars( (object) $params );
		}

		// Check if we need to bypass cache automatically.
		if ( ! isset( $params['bypass_cache'] ) ) {
			$api_cache = pods_api_cache();

			if ( ! $api_cache ) {
				$params['bypass_cache'] = true;
			}
		}

		if ( isset( $params['table_info'] ) ) {
			unset( $params['table_info'] );
		}

		if ( isset( $params['pod_id'] ) ) {
			$params['parent'] = (int) $params['pod_id'];

			unset( $params['pod_id'] );
		}

		if ( isset( $params['pod'] ) ) {
			if ( empty( $params['parent'] ) ) {
				$pod = $this->load_pod( $params['pod'] );

				if ( ! $pod ) {
					return [];
				}

				$params['parent'] = $pod->get_id();
			}

			unset( $params['pod'] );
		}

		if ( isset( $params['group_id'] ) ) {
			$params['group'] = (int) $params['group_id'];

			unset( $params['group_id'] );
		}

		if ( isset( $params['group'] ) ) {
			$group = $this->load_group( $params['group'], false );

			if ( $group ) {
				$params['group'] = $group->get_id();
			}
		}

		$params['object_type']      = 'field';
		$params['include_internal'] = true;

		$object = $this->_load_object( $params );

		if ( $object ) {
			return $object;
		}

		if ( $strict ) {
			return pods_error( __( 'Pod field not found', 'pods' ), $this );
		}

		return false;
	}

	/**
	 * Traverse fields and load their information.
	 *
	 * @param array       $params       {
	 *                                  An associative array of parameters.
	 *
	 * @type string  $pod               The Pod name.
	 * @type array   $expand            The field name(s) to expand.
	 * @type array   $types             The field type(s).
	 * @type boolean $bypass_cache      Bypass the cache when getting data.
	 * }
	 *
	 * @return Pods\Whatsit\Field[] List of field objects.
	 *
	 * @since 2.8.0
	 */
	public function traverse_fields( array $params ) {
		$fields = array();

		try {
			// pod and expand are required parameters.
			if ( empty( $params['pod'] ) || empty( $params['expand'] ) ) {
				return array();
			}

			// Check if we need to bypass cache automatically.
			if ( ! isset( $params['bypass_cache'] ) ) {
				$api_cache = pods_api_cache();

				if ( ! $api_cache ) {
					$params['bypass_cache'] = true;
				}
			}

			$pod    = $params['pod'];
			$expand = $params['expand'];
			$types  = ! empty( $params['types'] ) ? (array) $params['types'] : PodsForm::tableless_field_types();

			// For each in expand, load field, fall back to load pod if an object field.
			foreach ( $expand as $field_name ) {
				$args = array(
					'pod'  => $pod,
					'name' => $field_name,
					'type' => $types,
				);

				$field = $this->load_field( $args );

				if ( ! $field instanceof Field ) {
					// Check if this is an object field.
					$pod_data = $this->load_pod( $pod );

					if ( ! $pod_data instanceof Pod ) {
						break;
					}

					$field = $pod_data->get_field( $field_name );

					if ( ! $field instanceof Object_Field || ! in_array( $field['type'], $types, true ) ) {
						break;
					}
				}

				$fields[] = $field;

				$pod = $field->get_related_object_name();

				if ( null === $pod ) {
					break;
				}
			}
		} catch ( \Exception $e ) {
			// Do nothing.
		}

		return $fields;
	}

	/**
	 * Load fields by Pod, ID, Name, and/or Type.
	 *
	 * @param array       $params       {
	 *                                  An associative array of parameters.
	 *
	 * @type int          $pod_id       The Pod ID.
	 * @type string       $pod          The Pod name.
	 * @type string|array $id           The field ID(s).
	 * @type string|array $name         The field name(s).
	 * @type string|array $type         The field type(s).
	 * @type array        $args         Arg(s) key=>value array to filter by.
	 * @type boolean      $count        Return only a count of fields.
	 * @type boolean      $names        Return only an array of name => label.
	 * @type boolean      $ids          Return only an array of ID => label.
	 * @type boolean      $bypass_cache Bypass the cache when getting data.
	 * }
	 *
	 * @return Pods\Whatsit\Field[]|int List of field objects or count.
	 *
	 * @throws Exception
	 *
	 * @since 1.7.9
	 */
	public function load_fields( $params = [] ) {
		// Backwards compatibility handling.
		if ( is_object( $params ) ) {
			$params = get_object_vars( $params );
		}

		$include_internal = false;

		if ( isset( $params['include_internal'] ) ) {
			$include_internal = (boolean) $params['include_internal'];

			unset( $params['include_internal'] );
		}

		if ( ! $include_internal ) {
			$params['internal'] = false;
		}

		if ( isset( $params['table_info'] ) ) {
			unset( $params['table_info'] );
		}

		// Backcompat handling.
		if ( ! empty( $params['ids'] ) ) {
			$params['id'] = $params['ids'];

			unset( $params['ids'] );
		}

		if ( isset( $params['pod_id'] ) ) {
			$params['parent'] = (int) $params['pod_id'];

			unset( $params['pod_id'] );
		}

		if ( isset( $params['pod'] ) ) {
			if ( empty( $params['parent'] ) ) {
				$pod = $this->load_pod( $params['pod'] );

				if ( ! $pod ) {
					return [];
				}

				$params['parent'] = $pod->get_id();
			}

			unset( $params['pod'] );
		}

		$params['object_type'] = 'field';

		return $this->_load_objects( $params );
	}

	/**
	 * Check if a Pod's group exists
	 *
	 * @param array|int|WP_Post $params              {
	 *                                               An associative array of parameters.
	 *
	 * @type int                $pod_id              The Pod ID.
	 * @type string             $pod                 The Pod name.
	 * @type int                $id                  The Group ID.
	 * @type string             $name                The Group name.
	 * @type boolean            $bypass_cache        Bypass the cache when getting data.
	 * }
	 * @param boolean $allow_id Whether to allow the ID when checking if the group exists.
	 *
	 * @return bool
	 *
	 * @since 2.8.0
	 */
	public function group_exists( $params, $allow_id = true ) {
		$params = (object) $params;

		$allowed = [
			'name',
			'pod_id',
			'pod',
		];

		if ( $allow_id ) {
			$allowed[] = 'id';
		}

		$load_params = [];

		foreach ( $allowed as $param ) {
			if ( ! isset( $params->{$param} ) ) {
				continue;
			}

			$load_params[ $param ] = $params->{$param};
		}

		try {
			return (boolean) $this->load_group( $load_params );
		} catch ( Exception $exception ) {
			return false;
		}
	}

	/**
	 * Load a Group.
	 *
	 * @param array|int|WP_Post $params              {
	 *                                               An associative array of parameters.
	 *
	 * @type int                $pod_id              The Pod ID.
	 * @type string             $pod                 The Pod name.
	 * @type int                $id                  The Group ID.
	 * @type string             $name                The Group name.
	 * @type boolean            $bypass_cache        Bypass the cache when getting data.
	 * }
	 *
	 * @param bool              $strict              Makes sure the pod exists, throws an error if it doesn't work.
	 *
	 * @return Pods\Whatsit\Group|false Group object or false if not found.
	 *
	 * @throws Exception
	 * @since 2.8.0
	 */
	public function load_group( $params, $strict = false ) {
		if ( $params instanceof Group ) {
			return $params;
		}

		if ( $params instanceof WP_Post ) {
			return $this->get_pods_object_from_wp_post( $params );
		}

		if ( is_numeric( $params ) ) {
			$params = [
				'id' => $params,
			];
		} elseif ( is_string( $params ) ) {
			$params = [
				'name' => $params,
			];
		}

		// Backwards compatibility handling.
		if ( is_object( $params ) ) {
			$params = get_object_vars( (object) $params );
		}

		// Check if we need to bypass cache automatically.
		if ( ! isset( $params['bypass_cache'] ) ) {
			$api_cache = pods_api_cache();

			if ( ! $api_cache ) {
				$params['bypass_cache'] = true;
			}
		}

		if ( isset( $params['table_info'] ) ) {
			unset( $params['table_info'] );
		}

		if ( isset( $params['pod_id'] ) ) {
			$params['parent'] = (int) $params['pod_id'];

			unset( $params['pod_id'] );
		}

		if ( isset( $params['pod'] ) ) {
			if ( empty( $params['parent'] ) ) {
				$pod = $this->load_pod( $params['pod'] );

				if ( ! $pod ) {
					return [];
				}

				$params['parent'] = $pod->get_id();
			}

			unset( $params['pod'] );
		}

		$params['object_type']      = 'group';
		$params['include_internal'] = true;

		$object = $this->_load_object( $params );

		if ( $object ) {
			return $object;
		}

		if ( $strict ) {
			return pods_error( __( 'Pod group not found', 'pods' ), $this );
		}

		return false;
	}

	/**
	 * Load a list of Groups based on filters specified.
	 *
	 * @param array       $params       {
	 *                                  An associative array of parameters.
	 *
	 * @type int          $pod_id       The Pod ID.
	 * @type string       $pod          The Pod name.
	 * @type string|array $id           The group ID(s).
	 * @type array        $name         The group names.
	 * @type array        $type         The group types.
	 * @type array        $args         Arg(s) key=>value to filter by.
	 * @type boolean      $count        Return only a count of objects.
	 * @type boolean      $names        Return only an array of name => label.
	 * @type boolean      $ids          Return only an array of ID => label.
	 * @type boolean      $bypass_cache Bypass the cache when getting data.
	 * }
	 *
	 * @return Pods\Whatsit\Group[]|int List of group objects or count.
	 *
	 * @throws Exception
	 *
	 * @since 2.8.0
	 */
	public function load_groups( $params = [] ) {
		// Backwards compatibility handling.
		if ( is_object( $params ) ) {
			$params = get_object_vars( $params );
		}

		$include_internal = false;

		if ( isset( $params['include_internal'] ) ) {
			$include_internal = (boolean) $params['include_internal'];

			unset( $params['include_internal'] );
		}

		if ( ! $include_internal ) {
			$params['internal'] = false;
		}

		if ( isset( $params['table_info'] ) ) {
			unset( $params['table_info'] );
		}

		if ( isset( $params['object_fields'] ) ) {
			unset( $params['object_fields'] );
		}

		if ( isset( $params['pod_id'] ) ) {
			$params['parent'] = (int) $params['pod_id'];

			unset( $params['pod_id'] );
		}

		if ( isset( $params['pod'] ) ) {
			if ( empty( $params['parent'] ) ) {
				$pod = $this->load_pod( $params['pod'] );

				if ( ! $pod ) {
					return [];
				}

				$params['parent'] = $pod->get_id();
			}

			unset( $params['pod'] );
		}

		$params['object_type'] = 'group';

		return $this->_load_objects( $params );
	}

	/**
	 * Load a Pods Object
	 *
	 * $params['id'] int The Object ID
	 * $params['name'] string The Object name
	 * $params['type'] string The Object type
	 *
	 * @param array|object $params An associative array of parameters
	 * @param bool         $strict
	 *
	 * @return array|bool
	 * @since 2.0.0
	 */
	public function load_object( $params, $strict = false ) {
		// Backwards compatibility handling.
		if ( is_object( $params ) ) {
			$params = get_object_vars( (object) $params );
		}

		if ( ! isset( $params['type'] ) ) {
			return false;
		}

		$params['object_type'] = $params['type'];

		unset( $params['type'] );

		// Check if we need to bypass cache automatically.
		if ( ! isset( $params['bypass_cache'] ) ) {
			$api_cache = pods_api_cache();

			if ( ! $api_cache ) {
				$params['bypass_cache'] = true;
			}
		}

		return $this->_load_object( $params, $strict );
	}

	/**
	 * Load Multiple Pods Objects
	 *
	 * $params['type'] string The Object type
	 * $params['options'] array Pod Option(s) key=>value array to filter by
	 * $params['orderby'] string ORDER BY clause of query
	 * $params['limit'] string Number of objects to return
	 * $params['where'] string WHERE clause of query
	 * $params['ids'] string|array IDs of Objects
	 *
	 * @param array|object $params An associative array of parameters
	 *
	 * @return array
	 * @since 2.0.0
	 */
	public function load_objects( $params ) {
		// Backwards compatibility handling.
		if ( is_object( $params ) ) {
			$params = get_object_vars( (object) $params );
		}

		if ( ! isset( $params['type'] ) ) {
			return array();
		}

		$params['object_type'] = $params['type'];

		unset( $params['type'] );

		if ( isset( $params['ids'] ) ) {
			$params['id'] = $params['ids'];

			unset( $params['ids'] );
		}

		// Check if we need to bypass cache automatically.
		if ( ! isset( $params['bypass_cache'] ) ) {
			$api_cache = pods_api_cache();

			if ( ! $api_cache ) {
				$params['bypass_cache'] = true;
			}
		}

		return $this->_load_objects( $params );
	}

	/**
	 * @see   PodsAPI::load_object
	 *
	 * Load a Pod Template
	 *
	 * $params['id'] int The template ID.
	 * $params['name'] string The template name (title).
	 * $params['slug'] string The template slug.
	 *
	 * @param array $params An associative array of parameters
	 *
	 * @return array|bool
	 * @since 1.7.9
	 */
	public function load_template( $params ) {
		if ( ! class_exists( 'Pods_Templates' ) ) {
			return false;
		}

		$params       = (object) $params;
		$params->type = 'template';

		// Backwards compatibility check.
		if ( isset( $params->name ) ) {
			$params->title = $params->name;

			unset( $params->name );
		}

		// Because we always used name for title, support slug for name.
		if ( isset( $params->slug ) ) {
			$params->name = $params->slug;

			unset( $params->slug );
		}

		return $this->load_object( $params );
	}

	/**
	 * @see   PodsAPI::load_objects
	 *
	 * Load Multiple Pod Templates
	 *
	 * $params['where'] string The WHERE clause of query
	 * $params['options'] array Pod Option(s) key=>value array to filter by
	 * $params['orderby'] string ORDER BY clause of query
	 * $params['limit'] string Number of templates to return
	 *
	 * @param array $params (optional) An associative array of parameters
	 *
	 * @return array
	 *
	 * @since 2.0.0
	 */
	public function load_templates( $params = null ) {
		if ( ! class_exists( 'Pods_Templates' ) ) {
			return array();
		}

		$params       = (object) $params;
		$params->type = 'template';

		return $this->load_objects( $params );
	}

	/**
	 * @see   PodsAPI::load_object
	 *
	 * Load a Pod Page
	 *
	 * $params['id'] int The page ID
	 * $params['name'] string The page URI
	 *
	 * @param array $params An associative array of parameters
	 *
	 * @return array|bool
	 *
	 * @since 1.7.9
	 */
	public function load_page( $params ) {
		if ( ! class_exists( 'Pods_Pages' ) ) {
			return false;
		}

		$params = (object) $params;

		if ( isset( $params->name ) ) {
			$params->title = $params->name;

			unset( $params->name );
		}

		if ( ! isset( $params->title ) && isset( $params->uri ) ) {
			$params->title = $params->uri;

			unset( $params->uri );
		}

		$params->type = 'page';

		return $this->load_object( $params );
	}

	/**
	 * @see   PodsAPI::load_objects
	 *
	 * Load Multiple Pod Pages
	 *
	 * $params['where'] string The WHERE clause of query
	 * $params['options'] array Pod Option(s) key=>value array to filter by
	 * $params['orderby'] string ORDER BY clause of query
	 * $params['limit'] string Number of pages to return
	 *
	 * @param array $params (optional) An associative array of parameters
	 *
	 * @return array
	 *
	 * @since 2.0.0
	 */
	public function load_pages( $params = null ) {
		if ( ! class_exists( 'Pods_Pages' ) ) {
			return array();
		}

		$params       = (object) $params;
		$params->type = 'page';

		return $this->load_objects( $params );
	}

	/**
	 * @see   PodsAPI::load_object
	 *
	 * Load a Pod Helper
	 *
	 * $params['id'] int The helper ID
	 * $params['name'] string The helper name
	 *
	 * @param array $params An associative array of parameters
	 *
	 * @return array|bool
	 *
	 * @since 1.7.9
	 *
	 * @deprecated since 2.8.0
	 */
	public function load_helper( $params ) {
		return false;
	}

	/**
	 * @see   PodsAPI::load_objects
	 *
	 * Load Multiple Pod Helpers
	 *
	 * $params['where'] string The WHERE clause of query
	 * $params['options'] array Pod Option(s) key=>value array to filter by
	 * $params['orderby'] string ORDER BY clause of query
	 * $params['limit'] string Number of pages to return
	 *
	 * @param array $params (optional) An associative array of parameters
	 *
	 * @return array
	 *
	 * @since 2.0.0
	 *
	 * @deprecated since 2.8.0
	 */
	public function load_helpers( $params = null ) {
		return [];
	}

	/**
	 * Load the pod item object
	 *
	 * $params['pod'] string The datatype name
	 * $params['id'] int (optional) The item's ID
	 *
	 * @param array $params An associative array of parameters
	 *
	 * @return bool|\Pods
	 *
	 * @uses  pods()
	 *
	 * @since 2.0.0
	 */
	public function load_pod_item( $params ) {

		$params = (object) pods_sanitize( $params );

		if ( ! isset( $params->pod ) || empty( $params->pod ) ) {
			return pods_error( __( 'Pod name required', 'pods' ), $this );
		}
		if ( ! isset( $params->id ) || empty( $params->id ) ) {
			return pods_error( __( 'Item ID required', 'pods' ), $this );
		}

		$pod = false;

		if ( pods_api_cache() ) {
			$pod = pods_cache_get( $params->id, 'pods_item_object_' . $params->pod );
		}

		if ( false !== $pod ) {
			return $pod;
		}

		$pod = pods( $params->pod, $params->id );

		if ( pods_api_cache() ) {
			pods_cache_set( $params->id, $pod, 'pods_item_object_' . $params->pod );
		}

		return $pod;
	}

	/**
	 * Load potential sister fields for a specific field
	 *
	 * $params['pod'] int The Pod name
	 * $params['related_pod'] string The related Pod name
	 *
	 * @param array $params An associative array of parameters
	 * @param array $pod    (optional) Array of Pod data to use (to avoid lookup)
	 *
	 * @return array|bool
	 *
	 * @since 1.7.9
	 *
	 * @uses  PodsAPI::load_pod
	 */
	public function load_sister_fields( $params, $pod = null ) {

		$params = (object) pods_sanitize( $params );

		if ( empty( $pod ) ) {
			$pod = $this->load_pod( array( 'name' => $params->pod ), false );

			if ( false === $pod ) {
				return pods_error( __( 'Pod not found', 'pods' ), $this );
			}
		}

		$params->pod_id = $pod['id'];
		$params->pod    = $pod['name'];

		$type = false;

		if ( 0 === strpos( $params->related_pod, 'pod-' ) ) {
			$params->related_pod = pods_str_replace( 'pod-', '', $params->related_pod, 1 );
			$type                = 'pod';
		} elseif ( 0 === strpos( $params->related_pod, 'post_type-' ) ) {
			$params->related_pod = pods_str_replace( 'post_type-', '', $params->related_pod, 1 );
			$type                = 'post_type';
		} elseif ( 0 === strpos( $params->related_pod, 'taxonomy-' ) ) {
			$params->related_pod = pods_str_replace( 'taxonomy-', '', $params->related_pod, 1 );
			$type                = 'taxonomy';
		} elseif ( 'comment' === $params->related_pod ) {
			$type = $params->related_pod;
		}

		$related_pod = $this->load_pod( array( 'name' => $params->related_pod ), false );

		if ( false === $related_pod || ( false !== $type && 'pod' !== $type && $type !== $related_pod['type'] ) ) {
			return pods_error( __( 'Related Pod not found', 'pods' ), $this );
		}

		$params->related_pod_id = $related_pod['id'];
		$params->related_pod    = $related_pod['name'];

		$sister_fields = array();

		foreach ( $related_pod['fields'] as $field ) {
			if ( 'pick' === $field['type'] && in_array( $field['pick_object'], array(
					$pod['type'],
					'pod',
				), true ) && ( $params->pod == $field['pick_object'] || $params->pod == $field['pick_val'] ) ) {
				$sister_fields[ $field['id'] ] = esc_html( $field['label'] . ' (' . $field['name'] . ')' );
			}
		}

		return $sister_fields;
	}

	/**
	 * Takes a sql field such as tinyint and returns the pods field type, such as num.
	 *
	 * @param string $sql_field The SQL field to look for
	 *
	 * @return string The field type
	 *
	 * @since 2.0.0
	 */
	public static function detect_pod_field_from_sql_data_type( $sql_field ) {

		$sql_field = strtolower( $sql_field );

		$field_to_field_map = array(
			'tinyint'    => 'number',
			'smallint'   => 'number',
			'mediumint'  => 'number',
			'int'        => 'number',
			'bigint'     => 'number',
			'float'      => 'number',
			'double'     => 'number',
			'decimal'    => 'number',
			'date'       => 'date',
			'datetime'   => 'datetime',
			'timestamp'  => 'datetime',
			'time'       => 'time',
			'year'       => 'date',
			'varchar'    => 'text',
			'text'       => 'paragraph',
			'mediumtext' => 'paragraph',
			'longtext'   => 'paragraph'
		);

		return ( array_key_exists( $sql_field, $field_to_field_map ) ) ? $field_to_field_map[ $sql_field ] : 'paragraph';
	}

	/**
	 * Gets all field types
	 *
	 * @return array Array of field types
	 *
	 * @uses  PodsForm::field_loader
	 *
	 * @since 2.0.0
	 * @deprecated 2.3.0
	 */
	public function get_field_types() {

		return PodsForm::field_types();
	}

	/**
	 * Gets the schema definition of a field.
	 *
	 * @param string $type    Field type to look for
	 * @param array  $options (optional) Options of the field to pass to the schema function.
	 *
	 * @return array|bool|mixed|null
	 *
	 * @since 2.0.0
	 */
	private function get_field_definition( $type, $options = null ) {
		$definition = PodsForm::field_method( $type, 'schema', $options );

		return $this->do_hook( 'field_definition', $definition, $type, $options );
	}

	/**
	 * @see   PodsForm:validate
	 *
	 * Validates the value of a field.
	 *
	 * @param mixed        $value         The value to validate
	 * @param string       $field         Field to use for validation
	 * @param array        $object_fields Fields of the object we're validating
	 * @param array        $fields        Array of all fields data
	 * @param array|Pods   $pod           Array of pod data (or Pods object)
	 * @param array|object $params        Extra parameters to pass to the validation function of the field.
	 *
	 * @return array|bool
	 *
	 * @uses  PodsForm::validate
	 *
	 * @since 2.0.0
	 */
	public function handle_field_validation( &$value, $field, $object_fields, $fields, $pod, $params = [] ) {

		$tableless_field_types = PodsForm::tableless_field_types();

		$fields = pods_config_merge_fields( $fields, $object_fields );

		if ( ! isset( $fields[ $field ] ) ) {
			return pods_error( sprintf( __( '"%s" is not a valid field', 'pods' ), $field ), $this );
		}

		$options = $fields[ $field ];

		if ( is_array( $params ) ) {
			$params = (object) $params;
		}

		$id = 0;

		if ( is_object( $params ) && isset( $params->id ) ) {
			$id = $params->id;
		} elseif ( $pod instanceof Pods ) {
			$id = $pod->id();
		}

		// Normalize to Pod config object.
		if ( $pod instanceof Pods ) {
			$pod = $pod->pod_data;
		} elseif ( ! is_array( $pod ) && ! $pod instanceof Pod ) {
			$pod = null;
		}

		$type  = $options['type'];
		$label = $options['label'];
		$label = empty( $label ) ? $field : $label;

		/**
		 * Allow filtering whether to check the required fields for values.
		 *
		 * @since 2.8.9
		 *
		 * @param bool $check_required Whether to check the required fields for values.
		 */
		$check_required = apply_filters( 'pods_api_handle_field_validation_check_required', true );

		// Verify required fields
		if ( $check_required && 'slug' !== $type && 1 === (int) pods_v( 'required', $options, 0 ) ) {
			if ( '' === $value || null === $value || array() === $value ) {
				return pods_error( sprintf( __( '%s is empty', 'pods' ), $label ), $this );
			}

			if ( 'multi' === pods_v( 'pick_format_type', $options ) && 'autocomplete' !== pods_v( 'pick_format_multi', $options ) ) {
				$has_value = false;

				$check_value = (array) $value;

				foreach ( $check_value as $val ) {
					if ( '' !== $val && null !== $val && 0 !== $val && '0' !== $val ) {
						$has_value = true;

						continue;
					}
				}

				if ( ! $has_value ) {
					return pods_error( sprintf( __( '%s is required', 'pods' ), $label ), $this );
				}
			}

		}

		// @todo move this to after pre-save preparations
		// Verify unique fields
		if ( 1 === (int) pods_v( 'unique', $options, 0 ) && '' !== $value && null !== $value && array() !== $value ) {
			if ( empty( $pod ) ) {
				return false;
			}

			if ( ! in_array( $type, $tableless_field_types, true ) ) {
				$exclude = '';

				if ( ! empty( $id ) ) {
					$exclude = "AND `id` != {$id}";
				}

				$check = false;

				$check_value = pods_sanitize( $value );

				// @todo handle meta-based fields
				// Trigger an error if not unique
				if ( 'table' === $pod['storage'] ) {
					$check = pods_query( "SELECT `id` FROM `@wp_pods_" . $pod['name'] . "` WHERE `{$field}` = '{$check_value}' {$exclude} LIMIT 1", $this );
				}

				if ( ! empty( $check ) ) {
					return pods_error( sprintf( __( '%s needs to be unique', 'pods' ), $label ), $this );
				}
			} else {
				// @todo handle tableless check
			}
		}

		$validate = PodsForm::validate( $options['type'], $value, $field, $options, $fields, $pod, $id, $params );

		$validate = $this->do_hook( 'field_validation', $validate, $value, $field, $object_fields, $fields, $pod, $params );

		return $validate;
	}

	/**
	 * Find items related to a parent field
	 *
	 * @param int|array $field_id The Field ID or the list of all parameters.
	 * @param int       $pod_id   The Pod ID
	 * @param mixed     $ids      A comma-separated string (or array) of item IDs
	 * @param array     $field    Field data array
	 * @param array     $pod      Pod data array
	 *
	 * @return int[]
	 *
	 * @since 2.0.0
	 *
	 * @uses  pods_query()
	 */
	public function lookup_related_items( $field_id, $pod_id, $ids, $field = null, $pod = null ) {
		$params = [
			'field_id'    => $field_id,
			'pod_id'      => $pod_id,
			'ids'         => $ids,
			'field'       => $field,
			'pod'         => $pod,
			'force_meta'  => false,
			'pods_object' => null,
		];

		if ( is_array( $field_id ) && isset( $field_id['field_id'] ) ) {
			$params['field_id'] = $field_id['field_id'];

			$params = array_merge( $params, $field_id );
		}

		$params = (object) $params;

		$related_ids = array();

		if ( ! is_array( $params->ids ) ) {
			$params->ids = explode( ',', $params->ids );
		}

		$params->ids = array_map( 'absint', $params->ids );
		$params->ids = array_unique( array_filter( $params->ids ) );

		$idstring = implode( ',', $params->ids );

		$cache_key = $params->pod_id . '|' . $params->field_id;

		// Check cache first, no point in running the same query multiple times
		if ( $params->pod_id && $params->field_id ) {
			$cache_value = pods_static_cache_get( $cache_key, __CLASS__ . '/related_item_cache' ) ?: [];

			if ( isset( $cache_value[ $idstring ] ) && is_array( $cache_value[ $idstring ] ) ) {
				return $cache_value[ $idstring ];
			}
		}

		$tableless_field_types = PodsForm::tableless_field_types();

		if ( empty( $params->field ) ) {
			$load_params = array(
				'parent' => $params->pod_id,
			);

			if ( ! empty( $params->field_id ) ) {
				$load_params['id'] = $params->field_id;
			}

			$params->field = $this->load_field( $load_params );
		}

		$field_type = pods_v( 'type', $params->field );

		if ( empty( $params->ids ) || ! in_array( $field_type, $tableless_field_types, true ) ) {
			return array();
		}

		$related_pick_limit = 0;

		if ( ! empty( $params->field ) ) {
			$params->field_id = $params->field['id'];

			if ( $params->field instanceof Field && empty( $params->pod ) ) {
				$params->pod = $params->field->get_parent_object();
			}

			if ( ! empty( $params->pod ) ) {
				$params->pod_id = $params->pod['id'];
			}

			if ( 'multi' === pods_v( $field_type . '_format_type', $params->field, 'single' ) ) {
				$related_pick_limit = (int) pods_v( $field_type . '_limit', $params->field, 0 );
			} else {
				$related_pick_limit = 1;
			}

			// Temporary hack until there's some better handling here.
			$related_pick_limit *= count( $params->ids );
		}

		$meta_type = null;

		if ( $params->pod ) {
			$meta_type = $params->pod['type'];

			if ( in_array( $meta_type, [ 'post_type', 'media' ], true ) ) {
				$meta_type = 'post';
			} elseif ( 'taxonomy' === $meta_type ) {
				$meta_type = 'term';
			}
		}

		if ( 'taxonomy' === $field_type ) {
			$related = wp_get_object_terms( $params->ids, pods_v( 'name', $params->field ), array( 'fields' => 'ids' ) );

			if ( ! is_wp_error( $related ) ) {
				$related_ids = $related;
			}
		} elseif ( 'comment' === $field_type ) {
			$comment_args = array(
				'post__in' => $params->ids,
				'fields'   => 'ids',
			);

			$related = get_comments( $comment_args );

			if ( ! is_wp_error( $related ) ) {
				$related_ids = $related;
			}
		} elseif ( ! $params->force_meta && ! pods_tableless() && pods_podsrel_enabled( $params->field, 'lookup' ) ) {
			$params->field_id  = (int) $params->field_id;

			$ids_in = implode( ', ', array_fill( 0, count( $params->ids ), '%d' ) );

			$sister_id = pods_v( 'sister_id', $params->field, 0 );

			if ( is_numeric( $sister_id ) ) {
				$sister_id = (int) $sister_id;
			} else {
				$sister_id = 0;
			}

			$gathered_ids = [];

			$sql = "
				SELECT item_id, related_item_id
				FROM `@wp_podsrel`
				WHERE
					`field_id` = %d
					AND `item_id` IN ( {$ids_in} )
				ORDER BY `weight`
			";

			$prepare = [
				$params->field_id,
			];

			$prepare = array_merge( $prepare, $params->ids );

			$relationships = pods_query_prepare( $sql, $prepare );

			if ( ! empty( $relationships ) ) {
				foreach ( $relationships as $relation ) {
					// Skip if this is not a valid 1+ item ID.
					if ( (int) $relation->related_item_id < 1 ) {
						continue;
					}

					if ( ! isset( $gathered_ids[ (int) $relation->item_id ] ) ) {
						$gathered_ids[ (int) $relation->item_id ] = [];
					}

					$gathered_ids[ (int) $relation->item_id ][] = (int) $relation->related_item_id;
				}
			}

			/**
			 * Allow filtering whether bidirectional fallback should be used for podsrel table lookups.
			 *
			 * @since 2.8.22
			 *
			 * @param bool   $bidirectional_fallback The list of related IDs found.
			 * @param int    $sister_id              The bidirectional sister field ID.
			 * @param object $params                 The parameters object for the method.
			 */
			$bidirectional_fallback = (bool) apply_filters( 'pods_api_lookup_related_items_bidirectional_fallback', false, $sister_id, $params );

			if ( $bidirectional_fallback && 0 < $sister_id ) {
				$sql = "
					SELECT item_id, related_item_id
					FROM `@wp_podsrel`
					WHERE
						`related_field_id` = %d
						AND `related_item_id` IN ( {$ids_in} )
					ORDER BY `weight`
				";

				$relationships = pods_query_prepare( $sql, $prepare );

				if ( ! empty( $relationships ) ) {
					foreach ( $relationships as $relation ) {
						// Skip if this is not a valid 1+ item ID.
						if ( (int) $relation->item_id < 1 ) {
							continue;
						}

						if ( ! isset( $gathered_ids[ (int) $relation->related_item_id ] ) ) {
							$gathered_ids[ (int) $relation->related_item_id ] = [];
						}

						$gathered_ids[ (int) $relation->related_item_id ][] = (int) $relation->item_id;
					}
				}
			}

			// Filter the gathered IDs.
			foreach ( $params->ids as $id ) {
				$related_item_ids = isset( $gathered_ids[ (int) $id ] ) ? $gathered_ids[ (int) $id ] : [];

				/**
				 * Allow filtering the related IDs for an ID.
				 *
				 * @since 2.8.9
				 *
				 * @param array       $related_item_ids The list of related IDs found.
				 * @param int         $id               The object ID.
				 * @param string|null $meta_type        The meta type (if any).
				 * @param object      $params           The parameters object for the method.
				 */
				$gathered_ids[ (int) $id ] = (array) apply_filters( 'pods_api_lookup_related_items_related_ids_for_id', $related_item_ids, $id, $meta_type, $params );
			}

			$related_ids = array_merge( ...$gathered_ids );
			$related_ids = array_map( 'absint', $related_ids );
			$related_ids = array_unique( $related_ids );
		} else {
			if ( ! ( is_array( $params->pod ) || $params->pod instanceof Pods\Whatsit ) ) {
				$params->pod = $this->load_pod( array( 'id' => $params->pod_id ), false );
			}

			if ( ! empty( $params->pod ) ) {
				$no_conflict = pods_no_conflict_check( $meta_type );

				if ( ! $no_conflict ) {
					pods_no_conflict_on( $meta_type );
				}

				$meta_storage_types = [
					'post_type',
					'media',
					'taxonomy',
					'user',
					'comment',
				];

				$meta_storage_enabled = in_array( $params->pod['type'], $meta_storage_types, true ) && pods_relationship_meta_storage_enabled( $params->field, $params->pod );

				foreach ( $params->ids as $id ) {
					if ( 'settings' === $meta_type ) {
						$related_id = get_option( '_pods_' . $params->pod['name'] . '_' . $params->field['name'] );

						if ( empty( $related_id ) ) {
							$related_id = get_option( $params->pod['name'] . '_' . $params->field['name'] );
						}

						if ( is_array( $related_id ) && ! empty( $related_id ) ) {
							foreach ( $related_id as $related ) {
								if ( is_array( $related ) && ! empty( $related ) ) {
									if ( isset( $related['id'] ) ) {
										$related_ids[] = (int) $related['id'];
									} else {
										foreach ( $related as $r ) {
											$related_ids[] = (int) $r;
										}
									}
								} else {
									$related_ids[] = (int) $related;
								}
							}
						}
					} elseif ( $meta_storage_enabled ) {
						$related_id = get_metadata( $meta_type, $id, '_pods_' . $params->field['name'], true );

						if ( empty( $related_id ) ) {
							$related_id = get_metadata( $meta_type, $id, $params->field['name'] );
						}

						if ( is_array( $related_id ) && ! empty( $related_id ) ) {
							foreach ( $related_id as $related ) {
								if ( is_array( $related ) && ! empty( $related ) ) {
									if ( isset( $related['id'] ) ) {
										$related_ids[] = (int) $related['id'];
									} else {
										foreach ( $related as $r ) {
											if ( isset( $related['id'] ) ) {
												$related_ids[] = (int) $r['id'];
											} else {
												$related_ids[] = (int) $r;
											}
										}
									}
								} else {
									$related_ids[] = (int) $related;
								}
							}
						}
					}

					/**
					 * Allow filtering the related IDs for an ID.
					 *
					 * @since 2.8.9
					 *
					 * @param array       $related_item_ids The list of related IDs found.
					 * @param int         $item_id          The object ID.
					 * @param string|null $meta_type        The meta type (if any).
					 * @param object      $params           The parameters object for the method.
					 */
					$related_ids = (array) apply_filters( 'pods_api_lookup_related_items_related_ids_for_id', $related_ids, $id, $meta_type, $params );
				}

				if ( ! $no_conflict ) {
					pods_no_conflict_off( $meta_type );
				}
			}
		}

		if ( is_array( $related_ids ) ) {
			$related_ids = array_unique( array_filter( $related_ids ) );

			if ( 0 < $related_pick_limit && ! empty( $related_ids ) ) {
				$related_ids = array_slice( $related_ids, 0, $related_pick_limit );
			}
		}

		if ( 0 != $params->pod_id && 0 != $params->field_id && ! empty( $related_ids ) ) {
			// Only cache if $params->pod_id and $params->field_id were passed
			$cache_value = pods_static_cache_get( $cache_key, __CLASS__ . '/related_item_cache' ) ?: [];

			$cache_value[ $idstring ] = $related_ids;

			pods_static_cache_set( $cache_key, $cache_value, __CLASS__ . '/related_item_cache' );
		}

		return $related_ids;
	}

	/**
	 * Find related items related to an item
	 *
	 * @param int   $field_id The Field ID
	 * @param int   $pod_id   The Pod ID
	 * @param int   $id       Item ID to get related IDs from
	 * @param array $field    Field data array
	 * @param array $pod      Pod data array
	 *
	 * @return array|bool
	 *
	 * @since 2.3.0
	 *
	 * @uses  pods_query()
	 */
	public function lookup_related_items_from( $field_id, $pod_id, $id, $field = null, $pod = null ) {

		$related_ids = false;

		$id = (int) $id;

		$tableless_field_types = PodsForm::tableless_field_types();

		if ( empty( $id ) || ! in_array( pods_v( 'type', $field ), $tableless_field_types ) ) {
			return false;
		}

		$related_pick_limit = 0;

		if ( ! empty( $field ) ) {
			$related_pick_limit = (int) pods_v( $field['type'] . '_limit', $field, 0 );

			if ( 'single' === pods_v( $field['type'] . '_format_type', $field ) ) {
				$related_pick_limit = 1;
			}
		}

		if ( ! pods_tableless() ) {
			$field_id  = (int) $field_id;
			$sister_id = pods_v( 'sister_id', $field, 0 );

			if ( is_numeric( $sister_id ) ) {
				$sister_id = (int) $sister_id;
			} else {
				$sister_id = 0;
			}

			$relationships = array();

			if ( pods_podsrel_enabled() ) {
				$sql = "
					SELECT *
					FROM `@wp_podsrel`
					WHERE
						`field_id` = {$field_id}
						AND `related_item_id` = {$id}
					ORDER BY `weight`
				";

				$relationships = pods_query( $sql );
			}

			if ( ! empty( $relationships ) ) {
				$related_ids = array();

				foreach ( $relationships as $relation ) {
					if ( $field_id == $relation->field_id && ! in_array( $relation->item_id, $related_ids ) ) {
						$related_ids[] = (int) $relation->item_id;
					} elseif ( 0 < $sister_id && $field_id == $relation->related_field_id && ! in_array( $relation->related_item_id, $related_ids ) ) {
						$related_ids[] = (int) $relation->related_item_id;
					}
				}
			}
		} else {
			// @todo handle meta-based lookups
			return false;

			if ( ! ( is_array( $pod ) || $pod instanceof Pods\Whatsit ) ) {
				$pod = $this->load_pod( array( 'id' => $pod_id ), false );
			}

			if ( ! empty( $pod ) && in_array( $pod['type'], array(
					'post_type',
					'media',
					'taxonomy',
					'user',
					'comment',
					'settings'
				) ) ) {
				$related_ids = array();

				$meta_type = $pod['type'];

				if ( in_array( $meta_type, array( 'post_type', 'media' ), true ) ) {
					$meta_type = 'post';
				} elseif ( 'taxonomy' === $meta_type ) {
					$meta_type = 'term';
				}

				$no_conflict = pods_no_conflict_check( ( 'term' === $meta_type ? 'taxonomy' : $meta_type ) );

				if ( ! $no_conflict ) {
					pods_no_conflict_on( ( 'term' === $meta_type ? 'taxonomy' : $meta_type ) );
				}

				if ( 'settings' === $meta_type ) {
					$related_id = get_option( '_pods_' . $pod['name'] . '_' . $field['name'] );

					if ( empty( $related_id ) ) {
						$related_id = get_option( $pod['name'] . '_' . $field['name'] );
					}

					if ( is_array( $related_id ) && ! empty( $related_id ) ) {
						foreach ( $related_id as $related ) {
							if ( is_array( $related ) && ! empty( $related ) ) {
								if ( isset( $related['id'] ) ) {
									$related_ids[] = (int) $related['id'];
								} else {
									foreach ( $related as $r ) {
										$related_ids[] = (int) $r;
									}
								}
							} else {
								$related_ids[] = (int) $related;
							}
						}
					}
				} else {
					$related_id = get_metadata( $meta_type, $id, '_pods_' . $field['name'], true );

					if ( empty( $related_id ) ) {
						$related_id = get_metadata( $meta_type, $id, $field['name'] );
					}

					if ( is_array( $related_id ) && ! empty( $related_id ) ) {
						foreach ( $related_id as $related ) {
							if ( is_array( $related ) && ! empty( $related ) ) {
								if ( isset( $related['id'] ) ) {
									$related_ids[] = (int) $related['id'];
								} else {
									foreach ( $related as $r ) {
										if ( isset( $related['id'] ) ) {
											$related_ids[] = (int) $r['id'];
										} else {
											$related_ids[] = (int) $r;
										}
									}
								}
							} else {
								$related_ids[] = (int) $related;
							}
						}
					}
				}

				if ( ! $no_conflict ) {
					pods_no_conflict_off( ( 'term' === $meta_type ? 'taxonomy' : $meta_type ) );
				}
			}
		}

		if ( is_array( $related_ids ) ) {
			$related_ids = array_unique( array_filter( $related_ids ) );
		}

		return $related_ids;
	}

	/**
	 *
	 * Load the information about an objects MySQL table
	 *
	 * @param        $object_type
	 * @param string $object The object to look for
	 * @param null   $name   (optional) Name of the pod to load
	 * @param array  $pod    (optional) Array with pod information
	 *
	 * @return array
	 *
	 * @since 2.5.0
	 */
	public function get_table_info_load( $object_type, $object, $name = null, $pod = null ) {
		$info = [
			'pod' => null,
		];

		if ( 'pod' === $object_type && null === $pod ) {
			if ( empty( $name ) ) {
				$prefix = 'pod-';

				// Make sure we actually have the prefix before trying anything with the name
				if ( 0 === strpos( $object_type, $prefix ) ) {
					$name = substr( $object_type, strlen( $prefix ), strlen( $object_type ) );
				}
			}

			if ( empty( $name ) && ! empty( $object ) ) {
				$name = $object;
			}

			$pod = $this->load_pod( [
				'name'       => $name,
				'auto_setup' => true,
			] );

			if ( ! empty( $pod ) ) {
				$object_type = $pod['type'];
				$name        = $pod['name'];
				$object      = $pod['object'];

				$info['pod'] = $pod;
			}
		} elseif ( null === $pod ) {
			if ( empty( $name ) ) {
				$prefix = $object_type . '-';

				// Make sure we actually have the prefix before trying anything with the name
				if ( 0 === strpos( $object_type, $prefix ) ) {
					$name = substr( $object_type, strlen( $prefix ), strlen( $object_type ) );
				}
			}

			if ( empty( $name ) && ! empty( $object ) ) {
				$name = $object;
			}

			if ( ! empty( $name ) ) {
				$pod = $this->load_pod( [
					'name'       => $name,
					'auto_setup' => true,
				] );

				if ( ! empty( $pod ) && ( null === $object_type || $object_type == $pod['type'] ) ) {
					$object_type = $pod['type'];
					$name        = $pod['name'];
					$object      = $pod['object'];

					$info['pod'] = $pod;
				}
			}
		} elseif ( ! empty( $pod ) ) {
			$info['pod'] = $pod;
		}

		if ( 0 === strpos( $object_type, 'pod' ) ) {
			if ( empty( $name ) ) {
				$prefix = 'pod-';

				// Make sure we actually have the prefix before trying anything with the name
				if ( 0 === strpos( $object_type, $prefix ) ) {
					$name = substr( $object_type, strlen( $prefix ), strlen( $object_type ) );
				}
			}

			$info['type'] = 'pod';
			global $wpdb;

			$info['meta_table'] = $wpdb->prefix . 'pods_' . ( empty( $object ) ? $name : $object );
			$info['table']      = $info['meta_table'];

			if ( ( is_array( $info['pod'] ) || $info['pod'] instanceof Pods\Whatsit ) && 'pod' === pods_v( 'type', $info['pod'] ) ) {
				$info['meta_field_value'] = pods_v( 'pod_index', $info['pod'], 'id', true );
				$info['pod_field_index']  = $info['meta_field_value'];
				$info['field_index']      = $info['meta_field_value'];
				$info['meta_field_index'] = $info['meta_field_value'];

				$slug_field = get_posts( array(
					'post_type'      => '_pods_field',
					'posts_per_page' => 1,
					'nopaging'       => true,
					'post_parent'    => $info['pod']['id'],
					'orderby'        => 'menu_order',
					'order'          => 'ASC',
					'meta_query'     => array(
						array(
							'key'   => 'type',
							'value' => 'slug',
						)
					)
				) );

				if ( ! empty( $slug_field[0] ) ) {
					$slug_field = $slug_field[0];

					$info['pod_field_slug'] = $slug_field->post_name;
					$info['field_slug']     = $slug_field->post_name;
				}

				if ( 1 == pods_v( 'hierarchical', $info['pod'], 0 ) ) {
					$parent_field = pods_v( 'pod_parent', $info['pod'], 'id', true );

					if ( ! empty( $parent_field ) && isset( $info['pod']['fields'][ $parent_field ] ) ) {
						$info['object_hierarchical'] = true;

						$info['field_parent']        = $parent_field . '_select';
						$info['pod_field_parent']    = $info['field_parent'];
						$info['field_parent_select'] = '`' . $parent_field . '`.`id` AS `' . $info['field_parent'] . '`';
					}
				}
			}
		}

		return $info;
	}

	/**
	 * Get information about an objects MySQL table
	 *
	 * @param string $object_type
	 * @param string $object The object to look for
	 * @param null   $name   (optional) Name of the pod to load
	 * @param array  $pod    (optional) Array with pod information
	 * @param array  $field  (optional) Array with field information
	 *
	 * @return array|bool
	 *
	 * @since 2.0.0
	 */
	public function get_table_info( $object_type, $object, $name = null, $pod = null, $field = null ) {

		/**
		 * @var $wpdb wpdb
		 */
		global $wpdb;

		// @todo Handle $object arrays for Post Types, Taxonomies, Comments (table pulled from first object in array)

		$info = array(
			//'select' => '`t`.*',
			'object_type'         => $object_type,
			'type'                => null,
			'object_name'         => $object,
			'object_hierarchical' => false,
			'storage'             => null,

			'table'      => $object,
			'meta_table' => $object,
			'pod_table'  => $wpdb->prefix . 'pods_' . ( empty( $object ) ? $name : $object ),

			'field_id'            => 'id',
			'field_index'         => 'name',
			'field_slug'          => null,
			'field_type'          => null,
			'field_parent'        => null,
			'field_parent_select' => null,

			'meta_field_id'    => 'id',
			'meta_field_index' => 'name',
			'meta_field_value' => 'name',

			'pod_field_id'     => 'id',
			'pod_field_index'  => 'name',
			'pod_field_slug'   => null,
			'pod_field_parent' => null,

			'join' => array(),

			'where'         => null,
			'where_default' => null,

			'orderby' => null,

			'pod'     => null,
			'recurse' => false
		);

		if ( empty( $object_type ) ) {
			$object_type = 'post_type';
			$object      = 'post';
		} elseif ( empty( $object ) && in_array( $object_type, array( 'user', 'media', 'comment' ), true ) ) {
			$object = $object_type;
		} elseif ( 'post_type' === $object_type && 'attachment' === $object ) {
			$object_type = 'media';
			$object      = $object_type;
		}

		$pod_name = $pod;

		if ( is_array( $pod_name ) || $pod_name instanceof Pods\Whatsit ) {
			$pod_name = pods_v( 'name', $pod_name, json_encode( $pod_name, JSON_UNESCAPED_UNICODE ), true );
		} else {
			$pod_name = $object;
		}

		$field_name = $field;

		if ( is_array( $field_name ) || $field_name instanceof Pods\Whatsit ) {
			$field_name = pods_v( 'name', $field_name, json_encode( $field_name, JSON_UNESCAPED_UNICODE ), true );
		}

		$cache_key = 'pods_' . $wpdb->prefix . '_get_table_info_' . md5( $object_type . '_object_' . $object . '_name_' . $name . '_pod_' . $pod_name . '_field_' . $field_name );

		$current_language = pods_i18n()->get_current_language();
		if ( ! empty( $current_language ) ) {
			$cache_key = 'pods_' . $wpdb->prefix . '_get_table_info_' . $current_language . '_' . md5( $object_type . '_object_' . $object . '_name_' . $name . '_pod_' . $pod_name . '_field_' . $field_name );
		}

		$_info = false;

		$table_info_cache = pods_static_cache_get( $cache_key, __CLASS__ . '/table_info_cache' ) ?: [];

		if ( $table_info_cache ) {
			// Prefer info from the object internal cache
			$_info = $table_info_cache;
		} elseif ( pods_api_cache() ) {
			if ( ! did_action( 'init' ) || doing_action( 'init' ) ) {
				$_info = pods_transient_get( $cache_key . '_pre_init' );
			} else {
				$_info = pods_transient_get( $cache_key );
			}
		}

		if ( false !== $_info && is_array( $_info ) ) {
			// Data was cached, use that
			$info = $_info;

			/**
			 * Allow filtering the table information for an object.
			 *
			 * @param array       $info        The table information.
			 * @param string      $object_type The object type.
			 * @param string      $object      The object name.
			 * @param string      $name        The pod name.
			 * @param array|Pod   $pod         The pod config (if found).
			 * @param array|Field $field       The field config (if found).
			 * @param self        $obj         The PodsAPI object.
			 */
			return apply_filters( 'pods_api_get_table_info', $info, $object_type, $object, $name, $pod, $field, $this );
		} else {
			// Data not cached, load it up
			$_info = $this->get_table_info_load( $object_type, $object, $name, $pod );
			if ( isset( $_info['type'] ) ) {
				// Allow function to override $object_type
				$object_type = $_info['type'];
			}
			$info = array_merge( $info, $_info );
		}

		if (
			0 === strpos( $object_type, 'post_type' )
			|| 'media' === $object_type
			|| in_array( pods_v( 'type', $info['pod'] ), [
				'post_type',
				'media',
			], true )
		) {
			// Post type.
			$info['table']      = $wpdb->posts;
			$info['meta_table'] = $wpdb->postmeta;
			$info['storage']    = 'meta';

			$info['field_id']            = 'ID';
			$info['field_index']         = 'post_title';
			$info['field_slug']          = 'post_name';
			$info['field_type']          = 'post_type';
			$info['field_parent']        = 'post_parent';
			$info['field_parent_select'] = "`t`.`{$info['field_parent']}`";

			$info['meta_field_id']    = 'post_id';
			$info['meta_field_index'] = 'meta_key';
			$info['meta_field_value'] = 'meta_value';

			if ( 'media' === $object_type ) {
				$object = 'attachment';
			}

			if ( empty( $name ) ) {
				$prefix = 'post_type-';

				// Make sure we actually have the prefix before trying anything with the name
				if ( 0 === strpos( $object_type, $prefix ) ) {
					$name = substr( $object_type, strlen( $prefix ), strlen( $object_type ) );
				}
			}

			if ( 'media' !== $object_type ) {
				$object_type = 'post_type';
			}

			$post_type = pods_sanitize( ( empty( $object ) ? $name : $object ) );

			if ( 'attachment' === $post_type || 'media' === $object_type ) {
				$info['pod_table'] = "{$wpdb->prefix}pods_media";
			} else {
				$info['pod_table'] = "{$wpdb->prefix}pods_" . pods_clean_name( $post_type, true, false );
			}

			$post_type_object = get_post_type_object( $post_type );

			if ( is_object( $post_type_object ) && $post_type_object->hierarchical ) {
				$info['object_hierarchical'] = true;
			}

			// Post Status default
			$post_status = [
				'publish',
			];

			if ( ! empty( $field['pick_post_status'] ) ) {
				// Support limiting by certain post status values.
				$post_status = $field['pick_post_status'];
			} elseif ( ! empty( $field['post_status'] ) ) {
				// Backwards-compatible with the old bugged version named as post_status.
				$post_status = $field['post_status'];
			}

			// Check for bad serialized array.
			if ( is_string( $post_status ) ) {
				$post_status = maybe_unserialize( $post_status );

				if ( is_string( $post_status ) ) {
					$post_status = explode( ',', $post_status );
				}
			}

			if ( ! is_array( $post_status ) ) {
				$post_status = [
					'publish',
				];
			}

			/**
			 * Default Post Status to query for.
			 *
			 * Use to change "default" post status from publish to any other status or statuses.
			 *
			 * @param  array  $post_status List of post statuses. Default is 'publish' or field setting (if available).
			 * @param  string $post_type   Post type of current object.
			 * @param  array  $info        Array of information about the object.
			 * @param  string $object      Type of object.
			 * @param  string $name        Name of pod to load.
			 * @param  array  $pod         Array with Pod information. Result of PodsAPI::load_pod().
			 * @param  array  $field       Array with field information.
			 *
			 * @since unknown
			 */
			$post_status = apply_filters( 'pods_api_get_table_info_default_post_status', $post_status, $post_type, $info, $object_type, $object, $name, $pod, $field );

			$info['where'] = [
				//'post_status' => "`t`.`post_status` IN ( 'inherit', 'publish' )", // @todo Figure out what statuses Attachments can be
				'post_type' => "`t`.`{$info['field_type']}` = '" . pods_sanitize( $post_type ) . "'",
			];

			if ( 'post_type' === $object_type && ! empty( $post_status ) ) {
				$info['where_default'] = "`t`.`post_status` IN ( '" . implode( "', '", pods_sanitize( $post_status ) ) . "' )";
			}

			$info['orderby'] = "`t`.`menu_order`, `t`.`{$info['field_index']}`, `t`.`post_date`";

			$info['object_fields'] = $this->get_wp_object_fields( $object_type, $info['pod'] );
		} elseif (
			0 === strpos( $object_type, 'taxonomy' ) || in_array( $object_type, [
				'nav_menu',
				'post_format',
			], true )
			|| 'taxonomy' === pods_v( 'type', $info['pod'] )
		) {
			// Taxonomy.
			$info['table']      = $wpdb->terms;
			$info['meta_table'] = $wpdb->terms;
			$info['storage']    = 'meta';

			$info['join']['tt']          = "LEFT JOIN `{$wpdb->term_taxonomy}` AS `tt` ON `tt`.`term_id` = `t`.`term_id`";
			$info['join']['tr']          = "LEFT JOIN `{$wpdb->term_relationships}` AS `tr` ON `tr`.`term_taxonomy_id` = `tt`.`term_taxonomy_id`";
			$info['meta_field_id']       = 'term_id';
			$info['field_id']            = 'term_id';
			$info['meta_field_value']    = 'name';
			$info['field_index']         = 'name';
			$info['meta_field_index']    = 'name';
			$info['field_slug']          = 'slug';
			$info['field_type']          = 'taxonomy';
			$info['field_parent']        = 'parent';
			$info['field_parent_select'] = "`tt`.`{$info['field_parent']}`";

			if ( ! empty( $wpdb->termmeta ) ) {
				$info['meta_table'] = $wpdb->termmeta;

				$info['meta_field_id']    = 'term_id';
				$info['meta_field_index'] = 'meta_key';
				$info['meta_field_value'] = 'meta_value';
			}

			if ( 'nav_menu' === $object_type ) {
				$object = 'nav_menu';
			} elseif ( 'post_format' === $object_type ) {
				$object = 'post_format';
			}

			if ( empty( $name ) ) {
				$prefix = 'taxonomy-';

				// Make sure we actually have the prefix before trying anything with the name
				if ( 0 === strpos( $object_type, $prefix ) ) {
					$name = substr( $object_type, strlen( $prefix ), strlen( $object_type ) );
				}
			}

			if ( ! in_array( $object_type, array( 'nav_menu', 'post_format' ), true ) ) {
				$object_type = 'taxonomy';
			}

			$taxonomy = pods_sanitize( ( empty( $object ) ? $name : $object ) );

			$info['pod_table'] = "{$wpdb->prefix}pods_" . pods_clean_name( $taxonomy, true, false );

			$taxonomy_object = get_taxonomy( $taxonomy );

			if ( is_object( $taxonomy_object ) && $taxonomy_object->hierarchical ) {
				$info['object_hierarchical'] = true;
			}

			$info['where'] = [
				'tt.taxonomy' => "`tt`.`{$info['field_type']}` = '" . pods_sanitize( $taxonomy ) . "'",
			];

			$info['object_fields'] = $this->get_wp_object_fields( $object_type, $info['pod'] );
		} elseif ( 'user' === $object_type || 'user' === pods_v( 'type', $info['pod'] ) ) {
			// User.
			$info['table']      = $wpdb->users;
			$info['meta_table'] = $wpdb->usermeta;
			$info['pod_table']  = $wpdb->prefix . 'pods_user';
			$info['storage']    = 'meta';

			$info['field_id']    = 'ID';
			$info['field_index'] = 'display_name';
			$info['field_slug']  = 'user_nicename';

			$info['meta_field_id']    = 'user_id';
			$info['meta_field_index'] = 'meta_key';
			$info['meta_field_value'] = 'meta_value';

			$info['where'] = [];

			$info['object_fields'] = $this->get_wp_object_fields( $object_type, $info['pod'] );
		} elseif ( 'comment' === $object_type || 'comment' === pods_v( 'type', $info['pod'] ) ) {
			// Comment type.
			$info['table']      = $wpdb->comments;
			$info['meta_table'] = $wpdb->commentmeta;
			$info['pod_table']  = $wpdb->prefix . 'pods_comment';
			$info['storage']    = 'meta';

			$info['field_id']            = 'comment_ID';
			$info['field_index']         = 'comment_date';
			$info['field_type']          = 'comment_type';
			$info['field_parent']        = 'comment_parent';
			$info['field_parent_select'] = '`t`.`' . $info['field_parent'] . '`';

			$info['meta_field_id']    = 'comment_id';
			$info['meta_field_index'] = 'meta_key';
			$info['meta_field_value'] = 'meta_value';

			$object = 'comment';

			$comment_type = empty( $object ) ? $name : $object;

			$comment_type_clause = "`t`.`{$info['field_type']}` = '" . pods_sanitize( $comment_type ) . "'";

			if ( 'comment' === $comment_type ) {
				$comment_type_clause = "( {$comment_type_clause} OR `t`.`{$info['field_type']}` = '' )";
			}

			$info['where'] = [
				'comment_approved' => '`t`.`comment_approved` = 1',
				'comment_type'     => $comment_type_clause,
			];

			$info['orderby'] = "`t`.`{$info['field_index']}` DESC, `t`.`{$info['field_id']}`";

			$info['object_fields'] = $this->get_wp_object_fields( $object_type, $info['pod'] );
		} elseif (
			in_array( $object_type, [
				'option',
				'settings',
			], true )
			|| 'settings' === pods_v( 'type', $info['pod'] )
		) {
			// Setting.
			$info['table']      = $wpdb->options;
			$info['meta_table'] = $wpdb->options;
			$info['storage']    = 'option';

			$info['field_id']    = 'option_id';
			$info['field_index'] = 'option_name';

			$info['meta_field_id']    = 'option_id';
			$info['meta_field_index'] = 'option_name';
			$info['meta_field_value'] = 'option_value';

			$info['orderby'] = "`t`.`{$info['field_index']}` ASC";
		} elseif (
			is_multisite()
			&& (
				in_array( $object_type, [
					'site_option',
					'site_settings',
				], true )
				|| 'site_settings' === pods_v( 'type', $info['pod'] )
			)
		) {
			// Site meta.
			$info['table']      = $wpdb->sitemeta;
			$info['meta_table'] = $wpdb->sitemeta;
			$info['storage']    = 'meta';

			$info['field_id']    = 'site_id';
			$info['field_index'] = 'meta_key';

			$info['meta_field_id']    = 'site_id';
			$info['meta_field_index'] = 'meta_key';
			$info['meta_field_value'] = 'meta_value';

			$info['orderby'] = "`t`.`{$info['field_index']}` ASC";
		} elseif ( 'network' === $object_type && is_multisite() ) {
			// Network = Site.
			$info['table']      = $wpdb->site;
			$info['meta_table'] = $wpdb->sitemeta;
			$info['storage']    = 'meta';

			$info['field_id']    = 'id';
			$info['field_index'] = 'domain';

			$info['meta_field_id']    = 'site_id';
			$info['meta_field_index'] = 'meta_key';
			$info['meta_field_value'] = 'meta_value';

			$info['orderby'] = "`t`.`{$info['field_index']}` ASC, `t`.`path` ASC, `t`.`{$info['field_id']}`";
		} elseif ( 'site' === $object_type && is_multisite() ) {
			// Site = Blog.
			$info['table']   = $wpdb->blogs;
			$info['storage'] = 'none';

			$info['field_id']    = 'blog_id';
			$info['field_index'] = 'domain';
			$info['field_type']  = 'site_id';

			$info['where'] = [
				'archived' => '`t`.`archived` = 0',
				'spam'     => '`t`.`spam` = 0',
				'deleted'  => '`t`.`deleted` = 0',
				'site_id'  => "`t`.`{$info['field_type']}` = " . (int) get_current_site()->id,
			];

			$info['orderby'] = "`t`.`{$info['field_index']}` ASC, `t`.`path` ASC, `t`.`{$info['field_id']}`";
		} elseif ( 'table' === $object_type || 'table' === pods_v( 'type', $info['pod'] ) || ! empty( $info['pod']['table_custom'] ) ) {
			// Custom tables.
			$info['table']      = pods_v( 'table_custom', $info['pod'], ( empty( $object ) ? $name : $object ), true );
			$info['meta_table'] = pods_v( 'meta_table_custom', $info['pod'], $info['meta_table'], true );
			$info['pod_table']  = pods_v( 'pod_table_custom', $info['pod'], "{$wpdb->prefix}pods_" . $info['table'], true );
			$info['storage']    = 'table';

			$info['field_id']            = pods_v( 'field_id_custom', $info['pod'], $info['field_id'], true );
			$info['field_index']         = pods_v( 'field_index_custom', $info['pod'], $info['field_index'], true );
			$info['field_slug']          = pods_v( 'field_slug_custom', $info['pod'], $info['field_slug'], true );
			$info['field_type']          = pods_v( 'field_type_custom', $info['pod'], $info['field_type'], true );
			$info['field_parent']        = pods_v( 'field_parent_custom', $info['pod'], $info['field_parent'], true );
			$info['field_parent_select'] = pods_v( 'field_parent_select_custom', $info['pod'], $info['field_parent_select'], true );

			$info['meta_field_id']    = pods_v( 'meta_field_id_custom', $info['pod'], $info['meta_field_id'], true );
			$info['meta_field_index'] = pods_v( 'meta_field_index_custom', $info['pod'], $info['meta_field_index'], true );
			$info['meta_field_value'] = pods_v( 'meta_field_value_custom', $info['pod'], $info['meta_field_value'], true );

			$info['join'] = (array) pods_v( 'join_custom', $info['pod'], $info['join'], true );

			$info['orderby'] = pods_v( 'orderby_custom', $info['pod'], $info['orderby'], true );

			$info['where']         = pods_v( 'where_custom', $info['pod'], $info['where'], true );
			$info['where_default'] = pods_v( 'where_default_custom', $info['pod'], $info['where_default'], true );

			if ( ! empty( $field ) ) {
				$is_field_object = $field instanceof Field;

				if ( ! is_array( $field ) && ! $is_field_object ) {
					if ( is_string( $pod ) ) {
						$pod = pods( $pod );
					}

					if ( is_object( $pod ) && ! empty( $pod->fields[ $field ] ) ) {
						$field = $pod->fields[ $field ];
					}
				}

				$is_field_object = $field instanceof Field;

				if ( is_array( $field ) || $is_field_object ) {
					$info['table']            = pods_v( 'pick_table', $field, $info['table'], true );
					$info['field_id']         = pods_v( 'pick_table_id', $field, $info['field_id'], true );
					$info['meta_field_value'] = pods_v( 'pick_table_index', $field, $info['meta_field_value'], true );
					$info['field_index']      = $info['meta_field_value'];
					$info['meta_field_index'] = $info['meta_field_value'];
				}
			}
		}

		$info['table']      = pods_clean_name( $info['table'], false, false );
		$info['meta_table'] = pods_clean_name( $info['meta_table'], false, false );
		$info['pod_table']  = pods_clean_name( $info['pod_table'], false, false );

		$info['field_id']    = pods_clean_name( $info['field_id'], false, false );
		$info['field_index'] = pods_clean_name( $info['field_index'], false, false );
		$info['field_slug']  = pods_clean_name( $info['field_slug'], false, false );

		$info['meta_field_id']    = pods_clean_name( $info['meta_field_id'], false, false );
		$info['meta_field_index'] = pods_clean_name( $info['meta_field_index'], false, false );
		$info['meta_field_value'] = pods_clean_name( $info['meta_field_value'], false, false );

		if ( empty( $info['orderby'] ) ) {
			$info['orderby'] = "`t`.`{$info['field_index']}`, `t`.`{$info['field_id']}`";
		}

		if (
			'table' === pods_v( 'storage', $info['pod'] )
			&& ! in_array( $object_type, [
				'pod',
				'table',
			], true )
		) {
			$info['join']['d'] = "LEFT JOIN `{$info['pod_table']}` AS `d` ON `d`.`id` = `t`.`{$info['field_id']}`";
		}

		if ( ! empty( $info['pod'] ) && ( is_array( $info['pod'] ) || $info['pod'] instanceof Pods\Whatsit ) ) {
			$info['recurse'] = true;
			$info['storage'] = $info['pod']['storage'];
		}

		$info['type']        = $object_type;
		$info['object_name'] = $object;

		pods_static_cache_set( $cache_key, $info, __CLASS__ . '/table_info_cache' );

		if ( pods_api_cache() ) {
			if ( ! did_action( 'init' ) || doing_action( 'init' ) ) {
				pods_transient_set( $cache_key . '_pre_init', $info, WEEK_IN_SECONDS );
			} else {
				pods_transient_set( $cache_key, $info, WEEK_IN_SECONDS );
			}
		}

		/**
		 * Allow filtering the table information for an object.
		 *
		 * @param array       $info        The table information.
		 * @param string      $object_type The object type.
		 * @param string      $object      The object name.
		 * @param string      $name        The pod name.
		 * @param array|Pod   $pod         The pod config (if found).
		 * @param array|Field $field       The field config (if found).
		 * @param self        $obj         The PodsAPI object.
		 */
		return apply_filters( 'pods_api_get_table_info', $info, $object_type, $object, $name, $pod, $field, $this );
	}

	/**
	 * Export a package
	 *
	 * $params['pods'] array Pod Type IDs to export
	 * $params['templates'] array Template IDs to export
	 * $params['pages'] array Pod Page IDs to export
	 * $params['helpers'] array Helper IDs to export
	 *
	 * @param array $params An associative array of parameters
	 *
	 * @return array|bool
	 *
	 * @since      1.9.0
	 * @deprecated 2.0.0
	 */
	public function export_package( $params ) {

		if ( class_exists( 'Pods_Migrate_Packages' ) ) {
			return Pods_Migrate_Packages::export( $params );
		}

		return false;
	}

	/**
	 * Replace an existing package
	 *
	 * @param mixed $data (optional) An associative array containing a package, or the json encoded package
	 *
	 * @return bool
	 *
	 * @since      1.9.8
	 * @deprecated 2.0.0
	 */
	public function replace_package( $data = false ) {

		return $this->import_package( $data, true );
	}

	/**
	 * Import a package
	 *
	 * @param mixed $data    (optional) An associative array containing a package, or the json encoded package
	 * @param bool  $replace (optional) Replace existing items when found
	 *
	 * @return bool
	 *
	 * @since      1.9.0
	 * @deprecated 2.0.0
	 */
	public function import_package( $data = false, $replace = false ) {

		if ( class_exists( 'Pods_Migrate_Packages' ) ) {
			return Pods_Migrate_Packages::import( $data, $replace );
		}

		return false;
	}

	/**
	 * Validate a package
	 *
	 * @param array|string $data   (optional) An associative array containing a package, or the json encoded package
	 * @param bool         $output (optional)
	 *
	 * @return array|bool
	 *
	 * @since      1.9.0
	 * @deprecated 2.0.0
	 */
	public function validate_package( $data = false, $output = false ) {

		return true;
	}

	/**
	 * Import data from an array or a CSV file.
	 *
	 * @param mixed  $import_data  PHP associative array or CSV input
	 * @param bool   $numeric_mode Use IDs instead of the name field when matching
	 * @param string $format       Format of import data, options are php or csv
	 *
	 * @return array IDs of imported items
	 *
	 * @since 1.7.1
	 * @todo  This needs some love and use of table_info etc for relationships
	 */
	public function import( $import_data, $numeric_mode = false, $format = null ) {

		/**
		 * @var $wpdb wpdb
		 */
		global $wpdb;

		if ( null === $format && null !== $this->format ) {
			$format = $this->format;
		}

		if ( 'csv' === $format && ! is_array( $import_data ) ) {
			$data = pods_migrate( 'sv', ',' )->parse( $import_data );

			$import_data = $data['items'];
		}

		pods_query( "SET NAMES utf8" );
		pods_query( "SET CHARACTER SET utf8" );

		// Loop through the array of items
		$ids = array();

		// Test to see if it's an array of arrays
		if ( ! is_array( @current( $import_data ) ) ) {
			$import_data = array( $import_data );
		}

		if ( ! empty( $this->pod_data ) ) {
			$pod = $this->pod_data;
		} elseif ( ! empty( $this->pod ) ) {
			$pod = $this->load_pod( [ 'name' => $this->pod ], false );
		}

		if ( false === $pod ) {
			return pods_error( __( 'Pod not found', 'pods' ), $this );
		}

		$pod_name = $pod['name'];

		$fields = pods_config_merge_fields( $pod['fields'], $pod['object_fields'] );

		$simple_tableless_objects = PodsForm::simple_tableless_objects();

		foreach ( $import_data as $key => $data_row ) {
			$data = array();

			// Loop through each field (use $fields so only valid fields get parsed)
			foreach ( $fields as $field_name => $field_data ) {
				if ( ! isset( $data_row[ $field_name ] ) && ! isset( $data_row[ $field_data['label'] ] ) ) {
					continue;
				}

				$field_id    = $field_data['id'];
				$type        = $field_data['type'];
				$pick_object = isset( $field_data['pick_object'] ) ? $field_data['pick_object'] : '';
				$pick_val    = isset( $field_data['pick_val'] ) ? $field_data['pick_val'] : '';

				if ( isset( $data_row[ $field_name ] ) ) {
					$field_value = $data_row[ $field_name ];
				} else {
					$field_value = $data_row[ $field_data['label'] ];
				}

				if ( null !== $field_value && false !== $field_value && '' !== $field_value ) {
					if ( 'pick' === $type || in_array( $type, PodsForm::file_field_types() ) ) {
						$field_values = is_array( $field_value ) ? $field_value : array( $field_value );
						$pick_values  = array();

						foreach ( $field_values as $pick_value ) {
							if ( in_array( $type, PodsForm::file_field_types(), true ) || in_array( $pick_object, [ 'media', 'attachment' ], true ) ) {
								$where = "`guid` = '" . pods_sanitize( $pick_value ) . "'";

								if ( 0 < pods_absint( $pick_value ) && false !== $numeric_mode ) {
									$where = "`ID` = " . pods_absint( $pick_value );
								}

								$result = pods_query( "SELECT `ID` AS `id` FROM `{$wpdb->posts}` WHERE `post_type` = 'attachment' AND {$where} ORDER BY `ID`", $this );

								if ( ! empty( $result ) ) {
									$pick_values[] = $result[0]->id;
								}
							} elseif ( 'pick' === $type ) {
								// @todo This could and should be abstracted better and simplified
								$related_pod = false;

								if ( 'pod' === $pick_object ) {
									$related_pod = $this->load_pod( array(
										'name' => $pick_val,
									), false );
								}

								if ( empty( $related_pod ) ) {
									$related_pod = array(
										'id'   => 0,
										'type' => $pick_object,
									);
								}

								if ( in_array( 'taxonomy', array( $pick_object, $related_pod['type'] ) ) ) {
									$where = "`t`.`name` = '" . pods_sanitize( $pick_value ) . "'";

									if ( 0 < pods_absint( $pick_value ) && false !== $numeric_mode ) {
										$where = "`tt`.`term_id` = " . pods_absint( $pick_value );
									}

									$result = pods_query( "SELECT `t`.`term_id` AS `id` FROM `{$wpdb->term_taxonomy}` AS `tt` LEFT JOIN `{$wpdb->terms}` AS `t` ON `t`.`term_id` = `tt`.`term_id` WHERE `taxonomy` = '{$pick_val}' AND {$where} ORDER BY `t`.`term_id` LIMIT 1", $this );

									if ( ! empty( $result ) ) {
										$pick_values[] = $result[0]->id;
									}
								} elseif ( in_array( 'post_type', array(
										$pick_object,
										$related_pod['type'],
									) ) || in_array( 'media', array( $pick_object, $related_pod['type'] ) ) ) {
									$where = "`post_title` = '" . pods_sanitize( $pick_value ) . "'";

									if ( 0 < pods_absint( $pick_value ) && false !== $numeric_mode ) {
										$where = "`ID` = " . pods_absint( $pick_value );
									}

									$result = pods_query( "SELECT `ID` AS `id` FROM `{$wpdb->posts}` WHERE `post_type` = '{$pick_val}' AND {$where} ORDER BY `ID` LIMIT 1", $this );

									if ( ! empty( $result ) ) {
										$pick_values[] = $result[0]->id;
									}
								} elseif ( in_array( 'user', array( $pick_object, $related_pod['type'] ) ) ) {
									$where = "`user_login` = '" . pods_sanitize( $pick_value ) . "'";

									if ( 0 < pods_absint( $pick_value ) && false !== $numeric_mode ) {
										$where = "`ID` = " . pods_absint( $pick_value );
									}

									$result = pods_query( "SELECT `ID` AS `id` FROM `{$wpdb->users}` WHERE {$where} ORDER BY `ID` LIMIT 1", $this );

									if ( ! empty( $result ) ) {
										$pick_values[] = $result[0]->id;
									}
								} elseif ( in_array( 'comment', array( $pick_object, $related_pod['type'] ) ) ) {
									$where = "`comment_ID` = " . pods_absint( $pick_value );

									$result = pods_query( "SELECT `comment_ID` AS `id` FROM `{$wpdb->comments}` WHERE {$where} ORDER BY `ID` LIMIT 1", $this );

									if ( ! empty( $result ) ) {
										$pick_values[] = $result[0]->id;
									}
								} elseif ( in_array( $pick_object, $simple_tableless_objects, true ) ) {
									$pick_values[] = $pick_value;
								} elseif ( ! empty( $related_pod['id'] ) ) {
									$where = "`" . $related_pod['field_index'] . "` = '" . pods_sanitize( $pick_value ) . "'";

									if ( 0 < pods_absint( $pick_value ) && false !== $numeric_mode ) {
										$where = "`" . $related_pod['field_id'] . "` = " . pods_absint( $pick_value );
									}

									$result = pods_query( "SELECT `" . $related_pod['field_id'] . "` AS `id` FROM `" . $related_pod['table'] . "` WHERE {$where} ORDER BY `" . $related_pod['field_id'] . "` LIMIT 1", $this );

									if ( ! empty( $result ) ) {
										$pick_values[] = $result[0]->id;
									}
								}
							}
						}

						$field_value = implode( ',', $pick_values );
					}

					$data[ $field_name ] = $field_value;
				}
			}

			if ( ! empty( $data ) ) {
				$params = array(
					'pod'  => $pod_name,
					'data' => $data
				);

				$ids[] = $this->save_pod_item( $params );
			}
		}

		return $ids;
	}

	/**
	 * Export data from a Pod
	 *
	 * @param string|object $pod    The pod name or Pods object
	 * @param array         $params An associative array of parameters
	 *
	 * @return array Data arrays of all exported pod items
	 * @since 1.7.1
	 */
	public function export( $pod = null, $params = null ) {
		if ( empty( $pod ) ) {
			if ( ! empty( $this->pod_data ) ) {
				$pod = $this->pod_data;
			} elseif ( ! empty( $this->pod ) ) {
				$pod = $this->load_pod( [ 'name' => $this->pod ], false );
			}
		}

		if ( empty( $pod ) ) {
			return [];
		}

		$find = array(
			'limit'      => - 1,
			'search'     => false,
			'pagination' => false
		);

		if ( ! empty( $params ) && isset( $params['params'] ) ) {
			$find = array_merge( $find, (array) $params['params'] );

			unset( $params['params'] );

			$pod = pods( $pod, $find );
		} elseif ( ! is_object( $pod ) ) {
			$pod = pods( $pod, $find );
		}

		$data = array();

		while ( $pod->fetch() ) {
			$data[ $pod->id() ] = $this->export_pod_item( $params, $pod );
		}

		$data = $this->do_hook( 'export', $data, $pod->pod, $pod );

		return $data;
	}

	/**
	 * Convert CSV to a PHP array
	 *
	 * @param string $data The CSV input
	 *
	 * @return array
	 * @since      1.7.1
	 *
	 * @deprecated 2.3.5
	 */
	public function csv_to_php( $data, $delimiter = ',' ) {

		pods_deprecated( "PodsAPI->csv_to_php", '2.3.5' );

		$data = pods_migrate( 'sv', $delimiter, $data )->parse();

		return $data['items'];
	}

	/**
	 * Clear Pod-related cache
	 *
	 * @param array|Pod|null $pod            The pod object or null of flushing general cache.
	 * @param bool           $flush_rewrites Whether to flush rewrites.
	 *
	 * @return void
	 *
	 * @since 2.0.0
	 */
	public function cache_flush_pods( $pod = null, $flush_rewrites = true ) {

		/**
		 * @var $wpdb wpdb
		 */
		global $wpdb;

		pods_transient_clear( 'pods' );
		pods_transient_clear( 'pods_components' );
		pods_transient_clear( 'pods_core_loader_objects' );

		pods_transient_clear( 'pods_pfat_the_pods' );
		pods_transient_clear( 'pods_pfat_auto_pods' );
		pods_transient_clear( 'pods_pfat_archive_test' );

		pods_transient_clear( 'pods_blocks' );
		pods_transient_clear( 'pods_blocks_js' );

		if ( is_array( $pod ) || $pod instanceof Pod ) {
			pods_transient_clear( 'pods_pod_' . $pod['name'] );
			pods_cache_clear( $pod['name'], 'pods-class' );
			pods_cache_clear( $pod['type'] . '/' . $pod['name'], PodsMeta::class . '/is_key_covered' );

			if ( in_array( $pod['type'], array( 'post_type', 'taxonomy' ) ) ) {
				pods_transient_clear( 'pods_wp_cpt_ct' );
			}
		} else {
			pods_transient_clear( 'pods_wp_cpt_ct' );
		}

		pods_static_cache_clear( true, __CLASS__ );
		pods_static_cache_clear( true, __CLASS__ . '/table_info_cache' );
		pods_static_cache_clear( true, __CLASS__ . '/related_item_cache' );
		pods_static_cache_clear( true, PodsInit::class . '/existing_content_types' );
		pods_static_cache_clear( true, PodsView::class );
		pods_static_cache_clear( true, PodsField_Pick::class . '/related_data' );
		pods_static_cache_clear( true, PodsField_Pick::class . '/field_data' );
		pods_static_cache_clear( true, 'pods_svg_icon/base64' );
		pods_static_cache_clear( true, 'pods_svg_icon/svg' );

		pods_init()->refresh_existing_content_types_cache( true );

		// Delete transients in the database
		$wpdb->query( "DELETE FROM `{$wpdb->options}` WHERE `option_name` LIKE '_transient_pods%'" );
		$wpdb->query( "DELETE FROM `{$wpdb->options}` WHERE `option_name` LIKE '_transient_timeout_pods%'" );

		// Delete Pods Options Cache in the database
		$wpdb->query( "DELETE FROM `{$wpdb->options}` WHERE `option_name` LIKE '_pods_option_%'" );

		pods_cache_clear( true );

		if ( $flush_rewrites ) {
			pods_transient_set( 'pods_flush_rewrites', 1, WEEK_IN_SECONDS );
		}

		do_action( 'pods_cache_flushed' );
	}

	/**
	 * Process a Pod-based form
	 *
	 * @param mixed  $params
	 * @param object $obj       Pod object
	 * @param array  $fields    Fields being submitted in form ( key => settings )
	 * @param string $thank_you URL to send to upon success
	 *
	 * @return mixed
	 *
	 * @since 2.0.0
	 */
	public function process_form( $params, $obj = null, $fields = null, $thank_you = null ) {
		$old_display_errors = $this->display_errors;

		$this->display_errors = false;

		$form = null;

		$nonce    = pods_v_sanitized( '_pods_nonce', $params );
		$pod      = pods_v_sanitized( '_pods_pod', $params );
		$id       = pods_v_sanitized( '_pods_id', $params );
		$uri      = pods_v_sanitized( '_pods_uri', $params );
		$form     = pods_v_sanitized( '_pods_form', $params );
		$location = pods_v_sanitized( '_pods_location', $params );

		if ( is_object( $obj ) ) {
			$pod = $obj->pod;
			$id  = $obj->id();
		}

		if ( ! empty( $fields ) ) {
			$fields = array_keys( $fields );
			$form   = implode( ',', $fields );
		} else {
			$fields = explode( ',', $form );
		}

		if ( empty( $nonce ) || empty( $pod ) || empty( $uri ) || empty( $fields ) ) {
			return pods_error( __( 'Invalid submission', 'pods' ), $this );
		}

		$uid = pods_session_id();

		if ( is_user_logged_in() ) {
			$uid = 'user_' . get_current_user_id();
		}

		$field_hash = wp_create_nonce( 'pods_fields_' . $form );

		$action = 'pods_form_' . $pod . '_' . $uid . '_' . $id . '_' . $uri . '_' . $field_hash;

		if ( empty( $uid ) ) {
			return pods_error( __( 'Access denied for your session, please refresh and try again.', 'pods' ), $this );
		}

		if ( false === wp_verify_nonce( $nonce, $action ) ) {
			return pods_error( __( 'Access denied, please refresh and try again.', 'pods' ), $this );
		}

		$data = array();

		foreach ( $fields as $field ) {
			$data[ $field ] = pods_v( 'pods_field_' . $field, $params, '' );
		}

		$params = array(
			'pod'      => $pod,
			'id'       => $id,
			'data'     => $data,
			'from'     => 'process_form',
			'location' => $location
		);

		$id = $this->save_pod_item( $params );

		/**
		 * Fires after the form has been processed and save_pod_item has run.
		 *
		 * @param int       $id     Item ID.
		 * @param array     $params save_pod_item parameters.
		 * @param null|Pods $obj    Pod object (if set).
		 */
		do_action( 'pods_api_processed_form', $id, $params, $obj );

		// Always return $id for AJAX requests.
		if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
			if ( 0 < $id && ! empty( $thank_you ) ) {
				$thank_you = str_replace( 'X_ID_X', $id, $thank_you );

				pods_redirect( $thank_you, 302, false );
			}
		}

		$this->display_errors = $old_display_errors;

		return $id;
	}

	/**
	 * Handle filters / actions for the class
	 *
	 * @since 2.0.0
	 */
	private function do_hook() {

		$args = func_get_args();
		if ( empty( $args ) ) {
			return false;
		}
		$name = array_shift( $args );

		return pods_do_hook( "api", $name, $args, $this );
	}

	/**
	 * Handle variables that have been deprecated and PodsData vars
	 *
	 * @param string $name Property name.
	 *
	 * @return mixed
	 *
	 * @since 2.0.0
	 */
	public function __get( $name ) {

		$name = (string) $name;

		// Handle alias Pods\Whatsit\Pod properties.
		$supported_pods_object = array(
			'pod'         => 'name',
			'pod_id'      => 'id',
			'fields'      => 'fields',
		);

		if ( isset( $supported_pods_object[ $name ] ) ) {
			if ( ! is_object( $this->pod_data ) ) {
				return null;
			}

			return $this->pod_data->get_arg( $supported_pods_object[ $name ] );
		}

		return null;
	}

	/**
	 * Filter an array of arrays without causing PHP notices/warnings.
	 *
	 * @param array $values
	 *
	 * @return array
	 *
	 * @since 2.6.10
	 */
	private function array_filter_walker( $values = array() ) {

		$values = (array) $values;

		foreach ( $values as $k => $v ) {
			if ( is_object( $v ) ) {
				// Skip objects
				continue;
			} elseif ( is_array( $v ) ) {
				if ( empty( $v ) ) {
					// Filter values with empty arrays
					unset( $values[ $k ] );
				}
			} else {
				if ( ! $v ) {
					// Filter empty values
					unset( $values[ $k ] );
				}
			}
		}

		return $values;

	}

	/**
	 * Get default object storage type to use in Pods Object requests.
	 *
	 * @return string
	 */
	public function get_default_object_storage_type() {
		/**
		 * Filter the storage type to use for Pods Object requests.
		 *
		 * @param string $storage_type Storage type.
		 *
		 * @since 2.8.0
		 */
		return apply_filters( 'pods_api_object_storage_type', 'post_type' );
	}

	/**
	 * Get Pods Object from WP_Post.
	 *
	 * @param WP_Post|array|int $post Post object, array, or ID.
	 *
	 * @return false|Pods\Whatsit Object or false if the post does not exist.
	 *
	 * @since 2.8.0
	 */
	public function get_pods_object_from_wp_post( $post ) {
		if ( ! $post instanceof WP_Post ) {
			$post = get_post( $post );
		}

		if ( ! $post || is_wp_error( $post ) ) {
			return false;
		}

		$object_collection = Pods\Whatsit\Store::get_instance();

		/** @var Pods\Whatsit\Storage\Post_Type $post_type_storage */
		$post_type_storage = $object_collection->get_storage_object( $this->get_default_object_storage_type() );

		return $post_type_storage->to_object( $post );
	}

	/**
	 * Load an object.
	 *
	 * @param array   $params       {
	 *                              An associative array of parameters.
	 *
	 * @type string   $object_type  The object type.
	 * @type string   $id           The ID.
	 * @type string   $name         The name.
	 * @type boolean  $bypass_cache Bypass the cache when getting data.
	 * }
	 *
	 * @param boolean $strict       Whether to require a field exist or not when loading the info.
	 *
	 * @return Pods\Whatsit|false Object or false if not found.
	 *
	 * @throws Exception
	 *
	 * @since 2.8.0
	 */
	public function _load_object( array $params, $strict = false ) {
		if ( empty( $params['object_type'] ) ) {
			return false;
		}

		if ( is_array( $params['object_type'] ) ) {
			$params['object_type'] = reset( $params['object_type'] );
		}

		$object = false;

		if ( isset( $params['title'] ) ) {
			$object = pods_by_title( $params['title'], ARRAY_A, '_pods_' . $params['object_type'], 'publish', 'id' );

			// Normalize the response as Whatsit.
			if ( 0 < $object ) {
				$params['id'] = $object;

				unset( $params['title'] );

				$object = $this->_load_object( $params, $strict );
			}
		} else {
			$params['limit'] = 1;

			$loaded = $this->_load_objects( $params );

			if ( $loaded ) {
				$object = reset( $loaded );
			}
		}

		if ( $object ) {
			return $object;
		}

		if ( $strict ) {
			return pods_error( __( 'Object not found', 'pods' ), $this );
		}

		return false;
	}

	/**
	 * Load objects.
	 *
	 * @param array $params {
	 *                      An associative array of parameters.
	 *
	 *                      @type string|array $object_type  The object type(s).
	 *                      @type string|array $id           The ID(s).
	 *                      @type string|array $name         The name(s).
	 *                      @type string|array $type         The type(s).
	 *                      @type array        $args         Arg(s) key=>value to filter by.
	 *                      @type boolean      $count        Return only a count of fields.
	 *                      @type boolean      $labels       Return only an array of name => label.
	 *                      @type boolean      $names        Return only an array of name.
	 *                      @type boolean      $names_ids    Return only an array of id => name.
	 *                      @type boolean      $ids          Return only an array of ID => label.
	 *                      @type boolean      $bypass_cache Bypass the cache when getting data.
	 * }
	 *
	 * @return Pods\Whatsit[]|int List of objects or count.
	 *
	 * @since 2.8.0
	 */
	public function _load_objects( array $params ) {
		if ( empty( $params['object_type'] ) ) {
			return array();
		}

		// Check if we need to bypass cache automatically.
		if ( ! isset( $params['bypass_cache'] ) ) {
			$api_cache = pods_api_cache();

			if ( ! $api_cache ) {
				$params['bypass_cache'] = true;
			}
		}

		if ( isset( $params['options'] ) ) {
			$params['args'] = $params['options'];

			unset( $params['options'] );
		}

		if ( isset( $params['where'] ) ) {
			$where = $params['where'];

			unset( $params['where'] );

			if ( ! isset( $params['args'] ) ) {
				$params['args'] = array();
			}

			foreach ( $where as $arg ) {
				if ( ! isset( $arg['key'], $arg['value'] ) ) {
					continue;
				}

				$params['args'][ $arg['key'] ] = $arg['value'];
			}
		}

		if ( ! empty( $params['return_type'] ) ) {
			$return_type = $params['return_type'];

			if ( 'labels' === $return_type ) {
				$params['labels'] = true;
			} elseif ( 'names' === $return_type ) {
				$params['names'] = true;
			} elseif ( 'names_ids' === $return_type ) {
				$params['names_ids'] = true;
			} elseif ( 'ids' === $return_type ) {
				$params['ids'] = true;
			} elseif ( 'count' === $return_type ) {
				$params['count'] = true;
			}
		}

		$storage_type = ! empty( $params['object_storage_type'] ) ? $params['object_storage_type'] : $this->get_default_object_storage_type();

		$object_collection = Pods\Whatsit\Store::get_instance();

		/** @var Pods\Whatsit\Storage\Post_Type $post_type_storage */
		$post_type_storage = $object_collection->get_storage_object( $storage_type );

		$objects = $post_type_storage->find( $params );

		if ( ! empty( $params['auto_setup'] ) && ! $objects ) {
			$type = pods_v( 'type', $params, null );

			$whatsit_args = null;

			if ( 'user' === $params['name'] ) {
				// Detect user.
				$type = 'user';

				// Setup the pod and return the request again.
				$whatsit_args = [
					'object_type' => 'pod',
					'type'        => $type,
					'name'        => $type,
					'label'       => __( 'User', 'pods' ),
					'storage'     => 'meta',
					'adhoc'       => true,
				];
			} elseif ( 'comment' === $params['name'] ) {
				// Detect comment.
				$type = 'comment';

				$whatsit_args = [
					'object_type' => 'pod',
					'type'        => $type,
					'name'        => $type,
					'label'       => __( 'Comment', 'pods' ),
					'storage'     => 'meta',
					'adhoc'       => true,
				];
			} elseif ( 'media' === $params['name'] || 'attachment' === $params['name'] ) {
				// Detect media.
				$type = 'media';

				$whatsit_args = [
					'object_type' => 'pod',
					'type'        => $type,
					'name'        => $type,
					'label'       => __( 'Media', 'pods' ),
					'storage'     => 'meta',
					'adhoc'       => true,
				];
			}

			// Detect a post type.
			if ( 'post_type' === $type || null === $type ) {
				$post_type = get_post_type_object( $params['name'] );

				if ( $post_type ) {
					$type = 'post_type';

					$whatsit_args = [
						'object_type' => 'pod',
						'type'        => $type,
						'name'        => $post_type->name,
						'label'       => $post_type->label,
						'description' => $post_type->description,
						'storage'     => 'meta',
						'adhoc'       => true,
					];
				}
			}

			// Detect a taxonomy.
			if ( 'taxonomy' === $type || null === $type ) {
				$taxonomy = get_taxonomy( $params['name'] );

				if ( $taxonomy ) {
					$type = 'taxonomy';

					$whatsit_args = [
						'object_type' => 'pod',
						'type'        => $type,
						'name'        => $taxonomy->name,
						'label'       => $taxonomy->label,
						'description' => $taxonomy->description,
						'storage'     => 'meta',
						'adhoc'       => true,
					];
				}
			}

			// Setup the pod and return the request again.
			if ( null !== $whatsit_args ) {
				// Set up the params for the next call.
				$params['auto_setup']   = false;
				$params['object_storage_type'] = 'collection';

				$pod = new Pod( $whatsit_args );

				$object_collection->register_object( $pod );

				return $this->_load_objects( $params );
			}
		}

		if ( ! empty( $params['count'] ) ) {
			return count( $objects );
		}

		if ( ! empty( $params['labels'] ) ) {
			return wp_list_pluck( $objects, 'label', 'name' );
		}

		if ( ! empty( $params['names'] ) ) {
			return wp_list_pluck( $objects, 'name' );
		}

		if ( ! empty( $params['names_ids'] ) ) {
			return wp_list_pluck( $objects, 'name', 'id' );
		}

		if ( ! empty( $params['ids'] ) ) {
			return wp_list_pluck( $objects, 'id' );
		}

		return $objects;
	}

	/**
	 * Get the list of Pod types.
	 *
	 * @since 2.8.0
	 *
	 * @return string[] The list of pod types and their labels.
	 */
	public function get_pod_types() {
		$pod_types = [
			'post_type' => _x( 'Post Type (extended)', 'pod type label', 'pods' ),
			'taxonomy'  => _x( 'Taxonomy (extended)', 'pod type label', 'pods' ),
			'cpt'       => _x( 'Custom Post Type', 'pod type label', 'pods' ),
			'ct'        => _x( 'Custom Taxonomy', 'pod type label', 'pods' ),
			'user'      => _x( 'User (extended)', 'pod type label', 'pods' ),
			'media'     => _x( 'Media (extended)', 'pod type label', 'pods' ),
			'comment'   => _x( 'Comments (extended)', 'pod type label', 'pods' ),
			'pod'       => _x( 'Advanced Content Type', 'pod type label', 'pods' ),
			'settings'  => _x( 'Custom Settings Page', 'pod type label', 'pods' ),
			'internal'  => _x( 'Pods Internal', 'pod type label', 'pods' ),
		];

		/**
		 * Allow filtering the list of pod types and their labels.
		 *
		 * @since 2.8.0
		 *
		 * @param string[] $pod_types The list of pod types and their labels.
		 */
		return apply_filters( 'pods_api_pod_types', $pod_types );
	}

	/**
	 * Get the list of Pod types.
	 *
	 * @since 2.8.0
	 *
	 * @return string[] The list of storage types and their labels.
	 */
	public function get_storage_types() {
		$storage_types = [
			'none'    => _x( 'None (No Fields)', 'storage type label', 'pods' ),
			'options' => _x( 'Options', 'storage type label', 'pods' ),
			'meta'    => _x( 'Meta', 'storage type label', 'pods' ),
			'table'   => _x( 'Table', 'storage type label', 'pods' ),
		];

		/**
		 * Allow filtering the list of pod types and their labels.
		 *
		 * @since 2.8.0
		 *
		 * @param string[] $storage_types The list of storage types and their labels.
		 */
		return apply_filters( 'pods_api_storage_types', $storage_types );
	}

}
