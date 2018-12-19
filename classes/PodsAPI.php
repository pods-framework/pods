<?php

/**
 * @package Pods
 */
class PodsAPI {

	/**
	 * @var PodsAPI
	 */
	static $instance = null;

	/**
	 * @var array PodsAPI
	 */
	static $instances = array();

	/**
	 * @var bool
	 */
	public $display_errors = false;

	/**
	 * @var array|bool|mixed|null|void
	 */
	public $pod_data;

	/**
	 * @var
	 */
	public $pod;

	/**
	 * @var
	 */
	public $pod_id;

	/**
	 * @var
	 */
	public $fields;

	/**
	 * @var
	 * @deprecated 2.0.0
	 */
	public $format = null;

	/**
	 * @var
	 */
	private $deprecated;

	/**
	 * @var array
	 * @since 2.5.0
	 */
	private $fields_cache = array();

	/**
	 * @var array
	 * @since 2.5.0
	 *
	 */
	private static $table_info_cache = array();

	/**
	 * @var array
	 * @since 2.5.0
	 *
	 */
	private static $related_item_cache = array();

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
		} elseif ( ! is_object( self::$instance ) ) {
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

		if ( null !== $pod && 0 < strlen( (string) $pod ) ) {
			if ( null !== $format ) {
				$this->format = $format;

				pods_deprecated( 'pods_api( $pod, $format )', '2.0', 'pods_api( $pod )' );
			}

			$pod = pods_clean_name( $pod );

			$pod = $this->load_pod( array( 'name' => $pod, 'table_info' => true ), false );

			if ( ! empty( $pod ) ) {
				$this->pod_data = $pod;
				$this->pod      = $pod['name'];
				$this->pod_id   = $pod['id'];
				$this->fields   = $pod['fields'];
			}
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
	 * @param array  $fields      (optional) The array of fields and their options, for further processing with
	 *
	 * @return bool|mixed
	 *
	 * @since 2.0.0
	 */
	public function save_wp_object( $object_type, $data, $meta = array(), $strict = false, $sanitized = false, $fields = array() ) {

		if ( in_array( $object_type, array( 'post_type', 'media' ) ) ) {
			$object_type = 'post';
		}

		if ( 'taxonomy' === $object_type ) {
			$object_type = 'term';
		}

		if ( $sanitized ) {
			$data = pods_unsanitize( $data );
			$meta = pods_unsanitize( $meta );
		}

		if ( in_array( $object_type, array( 'post', 'term', 'user', 'comment' ) ) ) {
			return call_user_func( array( $this, 'save_' . $object_type ), $data, $meta, $strict, false, $fields );
		} elseif ( 'settings' === $object_type ) {
			// Nothing to save
			if ( empty( $meta ) ) {
				return true;
			}

			return $this->save_setting( pods_var( 'option_id', $data ), $meta, false );
		}

		return false;
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

		if ( in_array( $object_type, array( 'post_type', 'media' ) ) ) {
			$object_type = 'post';
		}

		if ( 'taxonomy' === $object_type ) {
			$object_type = 'term';
		}

		if ( empty( $id ) ) {
			return false;
		}

		if ( in_array( $object_type, array( 'post' ) ) ) {
			return wp_delete_post( $id, $force_delete );
		}

		if ( function_exists( 'wp_delete_' . $object_type ) ) {
			return call_user_func( 'wp_delete_' . $object_type, $id );
		}

		return false;
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
	 * @return mixed|void
	 *
	 * @since 2.0.0
	 */
	public function save_post( $post_data, $post_meta = null, $strict = false, $sanitized = false, $fields = array() ) {

		$conflicted = pods_no_conflict_check( 'post' );

		if ( ! $conflicted ) {
			pods_no_conflict_on( 'post' );
		}

		if ( ! is_array( $post_data ) || empty( $post_data ) ) {
			$post_data = array( 'post_title' => '' );
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
	public function save_post_meta( $id, $post_meta = null, $strict = false, $fields = array() ) {

		$simple_tableless_objects = PodsForm::simple_tableless_objects();

		$conflicted = pods_no_conflict_check( 'post' );

		if ( ! $conflicted ) {
			pods_no_conflict_on( 'post' );
		}

		if ( ! is_array( $post_meta ) ) {
			$post_meta = array();
		}

		$id = (int) $id;

		$meta = get_post_meta( $id );

		foreach ( $meta as $k => $value ) {
			if ( is_array( $value ) && 1 == count( $value ) ) {
				$meta[ $k ] = current( $value );
			}
		}

		foreach ( $post_meta as $meta_key => $meta_value ) {
			if ( null === $meta_value || ( $strict && '' === $post_meta[ $meta_key ] ) ) {
				$old_meta_value = '';

				if ( isset( $meta[ $meta_key ] ) ) {
					$old_meta_value = $meta[ $meta_key ];
				}

				delete_post_meta( $id, $meta_key, $old_meta_value );
			} else {
				$simple = false;

				if ( isset( $fields[ $meta_key ] ) ) {
					$field_data = $fields[ $meta_key ];

					$simple = ( 'pick' === $field_data['type'] && in_array( pods_var( 'pick_object', $field_data ), $simple_tableless_objects ) );
				}

				if ( $simple ) {
					delete_post_meta( $id, $meta_key );

					update_post_meta( $id, '_pods_' . $meta_key, $meta_value );

					if ( ! is_array( $meta_value ) ) {
						$meta_value = array( $meta_value );
					}

					foreach ( $meta_value as $value ) {
						add_post_meta( $id, $meta_key, $value );
					}
				} else {
					update_post_meta( $id, $meta_key, $meta_value );
				}
			}
		}

		if ( $strict ) {
			foreach ( $meta as $meta_key => $meta_value ) {
				if ( ! isset( $post_meta[ $meta_key ] ) ) {
					delete_post_meta( $id, $meta_key, $meta_value );
				}
			}
		}

		if ( ! $conflicted ) {
			pods_no_conflict_off( 'post' );
		}

		return $id;
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
	public function save_user_meta( $id, $user_meta = null, $strict = false, $fields = array() ) {

		$simple_tableless_objects = PodsForm::simple_tableless_objects();

		$conflicted = pods_no_conflict_check( 'user' );

		if ( ! $conflicted ) {
			pods_no_conflict_on( 'user' );
		}

		if ( ! is_array( $user_meta ) ) {
			$user_meta = array();
		}

		$id = (int) $id;

		$meta = get_user_meta( $id );

		foreach ( $user_meta as $meta_key => $meta_value ) {
			if ( null === $meta_value ) {
				$old_meta_value = '';

				if ( isset( $meta[ $meta_key ] ) ) {
					$old_meta_value = $meta[ $meta_key ];
				}

				delete_user_meta( $id, $meta_key, $old_meta_value );
			} else {
				$simple = false;

				if ( isset( $fields[ $meta_key ] ) ) {
					$field_data = $fields[ $meta_key ];

					$simple = ( 'pick' === $field_data['type'] && in_array( pods_var( 'pick_object', $field_data ), $simple_tableless_objects ) );
				}

				if ( $simple ) {
					delete_user_meta( $id, $meta_key );

					if ( ! is_array( $meta_value ) ) {
						$meta_value = array( $meta_value );
					}

					foreach ( $meta_value as $value ) {
						add_user_meta( $id, $meta_key, $value );
					}
				} else {
					update_user_meta( $id, $meta_key, $meta_value );
				}
			}
		}

		if ( $strict ) {
			foreach ( $meta as $meta_key => $meta_value ) {
				if ( ! isset( $user_meta[ $meta_key ] ) ) {
					delete_user_meta( $id, $meta_key, $user_meta[ $meta_key ] );
				}
			}
		}

		if ( ! $conflicted ) {
			pods_no_conflict_off( 'user' );
		}

		return $id;
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
	public function save_comment_meta( $id, $comment_meta = null, $strict = false, $fields = array() ) {

		$simple_tableless_objects = PodsForm::simple_tableless_objects();

		$conflicted = pods_no_conflict_check( 'comment' );

		if ( ! $conflicted ) {
			pods_no_conflict_on( 'comment' );
		}

		if ( ! is_array( $comment_meta ) ) {
			$comment_meta = array();
		}

		$id = (int) $id;

		$meta = get_comment_meta( $id );

		foreach ( $comment_meta as $meta_key => $meta_value ) {
			if ( null === $meta_value ) {
				$old_meta_value = '';

				if ( isset( $meta[ $meta_key ] ) ) {
					$old_meta_value = $meta[ $meta_key ];
				}

				delete_comment_meta( $id, $meta_key, $old_meta_value );
			} else {
				$simple = false;

				if ( isset( $fields[ $meta_key ] ) ) {
					$field_data = $fields[ $meta_key ];

					$simple = ( 'pick' === $field_data['type'] && in_array( pods_var( 'pick_object', $field_data ), $simple_tableless_objects ) );
				}

				if ( $simple ) {
					delete_comment_meta( $id, $meta_key );

					if ( ! is_array( $meta_value ) ) {
						$meta_value = array( $meta_value );
					}

					foreach ( $meta_value as $value ) {
						add_comment_meta( $id, $meta_key, $value );
					}
				} else {
					update_comment_meta( $id, $meta_key, $meta_value );
				}
			}
		}

		if ( $strict ) {
			foreach ( $meta as $meta_key => $meta_value ) {
				if ( ! isset( $comment_meta[ $meta_key ] ) ) {
					delete_comment_meta( (int) $id, $meta_key, $comment_meta[ $meta_key ] );
				}
			}
		}

		if ( ! $conflicted ) {
			pods_no_conflict_off( 'comment' );
		}

		return $id;
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
			$term_data = array( 'name' => '' );
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
			$term_name = $term_data['name'];

			unset( $term_data['name'] );

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
	public function save_term_meta( $id, $term_meta = null, $strict = false, $fields = array() ) {

		if ( ! function_exists( 'get_term_meta' ) ) {
			return $id;
		}

		$simple_tableless_objects = PodsForm::simple_tableless_objects();

		$conflicted = pods_no_conflict_check( 'taxonomy' );

		if ( ! $conflicted ) {
			pods_no_conflict_on( 'taxonomy' );
		}

		if ( ! is_array( $term_meta ) ) {
			$term_meta = array();
		}

		$id = (int) $id;

		$meta = get_term_meta( $id );

		foreach ( $meta as $k => $value ) {
			if ( is_array( $value ) && 1 == count( $value ) ) {
				$meta[ $k ] = current( $value );
			}
		}

		foreach ( $term_meta as $meta_key => $meta_value ) {
			if ( null === $meta_value || ( $strict && '' === $term_meta[ $meta_key ] ) ) {
				$old_meta_value = '';

				if ( isset( $meta[ $meta_key ] ) ) {
					$old_meta_value = $meta[ $meta_key ];
				}

				delete_term_meta( $id, $meta_key, $old_meta_value );
			} else {
				$simple = false;

				if ( isset( $fields[ $meta_key ] ) ) {
					$field_data = $fields[ $meta_key ];

					$simple = ( 'pick' === $field_data['type'] && in_array( pods_var( 'pick_object', $field_data ), $simple_tableless_objects ) );
				}

				if ( $simple ) {
					delete_term_meta( $id, $meta_key );

					update_term_meta( $id, '_pods_' . $meta_key, $meta_value );

					if ( ! is_array( $meta_value ) ) {
						$meta_value = array( $meta_value );
					}

					foreach ( $meta_value as $value ) {
						add_term_meta( $id, $meta_key, $value );
					}
				} else {
					update_term_meta( $id, $meta_key, $meta_value );
				}
			}
		}

		if ( $strict ) {
			foreach ( $meta as $meta_key => $meta_value ) {
				if ( ! isset( $term_meta[ $meta_key ] ) ) {
					delete_term_meta( $id, $meta_key, $meta_value );
				}
			}
		}

		if ( ! $conflicted ) {
			pods_no_conflict_off( 'taxonomy' );
		}

		return $id;
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

		$pod_name = pods_var_raw( 'name', $pod, $object, null, true );

		if ( 'media' === $pod_name ) {
			$object   = 'post_type';
			$pod_name = 'attachment';
		}

		$fields = false;

		if ( pods_api_cache() ) {
			$fields = pods_transient_get( trim( 'pods_api_object_fields_' . $object . $pod_name . '_', '_' ) );
		}

		if ( false !== $fields && ! $refresh ) {
			return $this->do_hook( 'get_wp_object_fields', $fields, $object, $pod );
		}

		$fields = array();

		if ( 'post_type' === $object ) {
			$fields = array(
				'ID'                    => array(
					'name'    => 'ID',
					'label'   => 'ID',
					'type'    => 'number',
					'alias'   => array( 'id' ),
					'options' => array(
						'number_format' => '9999.99'
					)
				),
				'post_title'            => array(
					'name'    => 'post_title',
					'label'   => 'Title',
					'type'    => 'text',
					'alias'   => array( 'title', 'name' ),
					'options' => array(
						'display_filter'      => 'the_title',
						'display_filter_args' => array( 'post_ID' )
					)
				),
				'post_content'          => array(
					'name'    => 'post_content',
					'label'   => 'Content',
					'type'    => 'wysiwyg',
					'alias'   => array( 'content' ),
					'options' => array(
						'wysiwyg_allowed_html_tags' => '',
						'display_filter'            => 'the_content',
						'pre_save'                  => 0
					)
				),
				'post_excerpt'          => array(
					'name'    => 'post_excerpt',
					'label'   => 'Excerpt',
					'type'    => 'paragraph',
					'alias'   => array( 'excerpt' ),
					'options' => array(
						'paragraph_allow_html'        => 1,
						'paragraph_allowed_html_tags' => '',
						'display_filter'              => 'the_excerpt',
						'pre_save'                    => 0
					)
				),
				'post_author'           => array(
					'name'        => 'post_author',
					'label'       => 'Author',
					'type'        => 'pick',
					'alias'       => array( 'author' ),
					'pick_object' => 'user',
					'options'     => array(
						'pick_format_type'   => 'single',
						'pick_format_single' => 'autocomplete',
						'default_value'      => '{@user.ID}'
					)
				),
				'post_date'             => array(
					'name'  => 'post_date',
					'label' => 'Publish Date',
					'type'  => 'datetime',
					'alias' => array( 'created', 'date' )
				),
				'post_date_gmt'         => array(
					'name'   => 'post_date_gmt',
					'label'  => 'Publish Date (GMT)',
					'type'   => 'datetime',
					'alias'  => array(),
					'hidden' => true
				),
				'post_status'           => array(
					'name'        => 'post_status',
					'label'       => 'Status',
					'type'        => 'pick',
					'pick_object' => 'post-status',
					'default'     => $this->do_hook( 'default_status_' . $pod_name, pods_var( 'default_status', pods_var_raw( 'options', $pod ), 'draft', null, true ), $pod ),
					'alias'       => array( 'status' )
				),
				'comment_status'        => array(
					'name'    => 'comment_status',
					'label'   => 'Comment Status',
					'type'    => 'text',
					'default' => get_option( 'default_comment_status', 'open' ),
					'alias'   => array(),
					'data'    => array(
						'open'   => __( 'Open', 'pods' ),
						'closed' => __( 'Closed', 'pods' )
					)
				),
				'ping_status'           => array(
					'name'    => 'ping_status',
					'label'   => 'Ping Status',
					'default' => get_option( 'default_ping_status', 'open' ),
					'type'    => 'text',
					'alias'   => array(),
					'data'    => array(
						'open'   => __( 'Open', 'pods' ),
						'closed' => __( 'Closed', 'pods' )
					)
				),
				'post_password'         => array(
					'name'  => 'post_password',
					'label' => 'Password',
					'type'  => 'text',
					'alias' => array()
				),
				'post_name'             => array(
					'name'  => 'post_name',
					'label' => 'Permalink',
					'type'  => 'slug',
					'alias' => array( 'slug', 'permalink' )
				),
				'to_ping'               => array(
					'name'   => 'to_ping',
					'label'  => 'To Ping',
					'type'   => 'text',
					'alias'  => array(),
					'hidden' => true
				),
				'pinged'                => array(
					'name'   => 'pinged',
					'label'  => 'Pinged',
					'type'   => 'text',
					'alias'  => array(),
					'hidden' => true
				),
				'post_modified'         => array(
					'name'   => 'post_modified',
					'label'  => 'Last Modified Date',
					'type'   => 'datetime',
					'alias'  => array( 'modified' ),
					'hidden' => true
				),
				'post_modified_gmt'     => array(
					'name'   => 'post_modified_gmt',
					'label'  => 'Last Modified Date (GMT)',
					'type'   => 'datetime',
					'alias'  => array(),
					'hidden' => true
				),
				'post_content_filtered' => array(
					'name'    => 'post_content_filtered',
					'label'   => 'Content (filtered)',
					'type'    => 'paragraph',
					'alias'   => array(),
					'hidden'  => true,
					'options' => array(
						'paragraph_allow_html'        => 1,
						'paragraph_oembed'            => 1,
						'paragraph_wptexturize'       => 1,
						'paragraph_convert_chars'     => 1,
						'paragraph_wpautop'           => 1,
						'paragraph_allow_shortcode'   => 1,
						'paragraph_allowed_html_tags' => ''
					)
				),
				'post_parent'           => array(
					'name'        => 'post_parent',
					'label'       => 'Parent',
					'type'        => 'pick',
					'pick_object' => 'post_type',
					'pick_val'    => '__current__',
					'alias'       => array( 'parent' ),
					'data'        => array(),
					'hidden'      => true
				),
				'guid'                  => array(
					'name'   => 'guid',
					'label'  => 'GUID',
					'type'   => 'text',
					'alias'  => array(),
					'hidden' => true
				),
				'menu_order'            => array(
					'name'    => 'menu_order',
					'label'   => 'Menu Order',
					'type'    => 'number',
					'alias'   => array(),
					'options' => array(
						'number_format' => '9999.99'
					)
				),
				'post_type'             => array(
					'name'   => 'post_type',
					'label'  => 'Type',
					'type'   => 'text',
					'alias'  => array( 'type' ),
					'hidden' => true
				),
				'post_mime_type'        => array(
					'name'   => 'post_mime_type',
					'label'  => 'Mime Type',
					'type'   => 'text',
					'alias'  => array(),
					'hidden' => true
				),
				'comment_count'         => array(
					'name'   => 'comment_count',
					'label'  => 'Comment Count',
					'type'   => 'number',
					'alias'  => array(),
					'hidden' => true
				),
				'comments'              => array(
					'name'        => 'comments',
					'label'       => 'Comments',
					'type'        => 'comment',
					'pick_object' => 'comment',
					'pick_val'    => 'comment',
					'alias'       => array(),
					'hidden'      => true,
					'options'     => array(
						'comment_format_type' => 'multi'
					)
				)
			);

			if ( ! empty( $pod ) ) {
				$taxonomies = get_object_taxonomies( $pod_name, 'objects' );

				foreach ( $taxonomies as $taxonomy ) {
					$fields[ $taxonomy->name ] = array(
						'name'        => $taxonomy->name,
						'label'       => $taxonomy->labels->name,
						'type'        => 'taxonomy',
						'pick_object' => 'taxonomy',
						'pick_val'    => $taxonomy->name,
						'alias'       => array(),
						'hidden'      => true,
						'options'     => array(
							'taxonomy_format_type' => 'multi'
						)
					);
				}
			}
		} elseif ( 'user' === $object ) {
			$fields = array(
				'ID'              => array(
					'name'    => 'ID',
					'label'   => 'ID',
					'type'    => 'number',
					'alias'   => array( 'id' ),
					'options' => array(
						'number_format' => '9999.99'
					)
				),
				'user_login'      => array(
					'name'    => 'user_login',
					'label'   => 'Title',
					'type'    => 'text',
					'alias'   => array( 'login' ),
					'options' => array(
						'required' => 1
					)
				),
				'user_nicename'   => array(
					'name'  => 'user_nicename',
					'label' => 'Permalink',
					'type'  => 'slug',
					'alias' => array( 'nicename', 'slug', 'permalink' )
				),
				'display_name'    => array(
					'name'  => 'display_name',
					'label' => 'Display Name',
					'type'  => 'text',
					'alias' => array( 'title', 'name' )
				),
				'user_pass'       => array(
					'name'    => 'user_pass',
					'label'   => 'Password',
					'type'    => 'text',
					'alias'   => array( 'password', 'pass' ),
					'options' => array(
						'required'         => 1,
						'text_format_type' => 'password'
					)
				),
				'user_email'      => array(
					'name'    => 'user_email',
					'label'   => 'E-mail',
					'type'    => 'text',
					'alias'   => array( 'email' ),
					'options' => array(
						'required'         => 1,
						'text_format_type' => 'email'
					)
				),
				'user_url'        => array(
					'name'    => 'user_url',
					'label'   => 'URL',
					'type'    => 'text',
					'alias'   => array( 'url', 'website' ),
					'options' => array(
						'required'            => 0,
						'text_format_type'    => 'website',
						'text_format_website' => 'normal'
					)
				),
				'user_registered' => array(
					'name'    => 'user_registered',
					'label'   => 'Registration Date',
					'type'    => 'date',
					'alias'   => array( 'created', 'date', 'registered' ),
					'options' => array(
						'date_format_type' => 'datetime'
					)
				)
			);
		} elseif ( 'comment' === $object ) {
			$fields = array(
				'comment_ID'           => array(
					'name'    => 'comment_ID',
					'label'   => 'ID',
					'type'    => 'number',
					'alias'   => array( 'id', 'ID', 'comment_id' ),
					'options' => array(
						'number_format' => '9999.99'
					)
				),
				'comment_content'      => array(
					'name'  => 'comment_content',
					'label' => 'Content',
					'type'  => 'wysiwyg',
					'alias' => array( 'content' )
				),
				'comment_approved'     => array(
					'name'    => 'comment_approved',
					'label'   => 'Approved',
					'type'    => 'number',
					'alias'   => array( 'approved' ),
					'options' => array(
						'number_format' => '9999.99'
					)
				),
				'comment_post_ID'      => array(
					'name'  => 'comment_post_ID',
					'label' => 'Post',
					'type'  => 'pick',
					'alias' => array( 'post', 'post_id' ),
					'data'  => array()
				),
				'user_id'              => array(
					'name'        => 'user_id',
					'label'       => 'Author',
					'type'        => 'pick',
					'alias'       => array( 'author' ),
					'pick_object' => 'user',
					'data'        => array()
				),
				'comment_date'         => array(
					'name'    => 'comment_date',
					'label'   => 'Date',
					'type'    => 'date',
					'alias'   => array( 'created', 'date' ),
					'options' => array(
						'date_format_type' => 'datetime'
					)
				),
				'comment_author'       => array(
					'name'  => 'comment_author',
					'label' => 'Author',
					'type'  => 'text',
					'alias' => array( 'author' )
				),
				'comment_author_email' => array(
					'name'  => 'comment_author_email',
					'label' => 'Author E-mail',
					'type'  => 'email',
					'alias' => array( 'author_email' )
				),
				'comment_author_url'   => array(
					'name'  => 'comment_author_url',
					'label' => 'Author URL',
					'type'  => 'text',
					'alias' => array( 'author_url' )
				),
				'comment_author_IP'    => array(
					'name'  => 'comment_author_IP',
					'label' => 'Author IP',
					'type'  => 'text',
					'alias' => array( 'author_IP' )
				),
				'comment_type'         => array(
					'name'   => 'comment_type',
					'label'  => 'Type',
					'type'   => 'text',
					'alias'  => array( 'type' ),
					'hidden' => true
				),
				'comment_parent'       => array(
					'name'        => 'comment_parent',
					'label'       => 'Parent',
					'type'        => 'pick',
					'pick_object' => 'comment',
					'pick_val'    => '__current__',
					'alias'       => array( 'parent' ),
					'data'        => array(),
					'hidden'      => true
				)
			);
		} elseif ( 'taxonomy' === $object ) {
			$fields = array(
				'term_id'          => array(
					'name'    => 'term_id',
					'label'   => 'ID',
					'type'    => 'number',
					'alias'   => array( 'id', 'ID' ),
					'options' => array(
						'number_format' => '9999.99'
					)
				),
				'name'             => array(
					'name'  => 'name',
					'label' => 'Title',
					'type'  => 'text',
					'alias' => array( 'title' )
				),
				'slug'             => array(
					'name'  => 'slug',
					'label' => 'Permalink',
					'type'  => 'slug',
					'alias' => array( 'permalink' )
				),
				'description'      => array(
					'name'  => 'description',
					'label' => 'Description',
					'type'  => 'wysiwyg',
					'alias' => array( 'content' )
				),
				'taxonomy'         => array(
					'name'  => 'taxonomy',
					'label' => 'Taxonomy',
					'type'  => 'text',
					'alias' => array()
				),
				'parent'           => array(
					'name'        => 'parent',
					'label'       => 'Parent',
					'type'        => 'pick',
					'pick_object' => 'taxonomy',
					'pick_val'    => '__current__',
					'alias'       => array( 'parent' ),
					'data'        => array(),
					'hidden'      => true
				),
				'term_taxonomy_id' => array(
					'name'    => 'term_taxonomy_id',
					'label'   => 'Term Taxonomy ID',
					'type'    => 'number',
					'alias'   => array(),
					'hidden'  => true,
					'options' => array(
						'number_format' => '9999.99'
					)
				),
				'term_group'       => array(
					'name'    => 'term_group',
					'label'   => 'Term Group',
					'type'    => 'number',
					'alias'   => array( 'group' ),
					'hidden'  => true,
					'options' => array(
						'number_format' => '9999.99'
					)
				),
				'count'            => array(
					'name'    => 'count',
					'label'   => 'Count',
					'type'    => 'number',
					'alias'   => array(),
					'hidden'  => true,
					'options' => array(
						'number_format' => '9999.99'
					)
				)
			);
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

		if ( did_action( 'init' ) && pods_api_cache() ) {
			pods_transient_set( trim( 'pods_api_object_fields_' . $object . $pod_name . '_', '_' ), $fields );
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
	 * $params['create_storage'] string Storage Type (for Creating Post Types)
	 * $params['create_storage_taxonomy'] string Storage Type (for Creating Taxonomies)
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

		$defaults = array(
			'create_extend'   => 'create',
			'create_pod_type' => 'post_type',

			'create_name'             => '',
			'create_label_singular'   => '',
			'create_label_plural'     => '',
			'create_storage'          => 'meta',
			'create_storage_taxonomy' => '',

			'create_setting_name'  => '',
			'create_label_title'   => '',
			'create_label_menu'    => '',
			'create_menu_location' => 'settings',

			'extend_pod_type' => 'post_type',
			'extend_post_type' => 'post',
			'extend_taxonomy' => 'category',
			'extend_table' => '',
			'extend_storage' => 'meta',
			'extend_storage_taxonomy' => '',
		);

		if( !function_exists( 'get_term_meta' ) ) {
			$defaults['create_storage_taxonomy'] = 'none';
			$defaults['extend_storage_taxonomy' ] = 'table' ;
		}

		$params = (object) array_merge( $defaults, (array) $params );

		if ( empty( $params->create_extend ) || ! in_array( $params->create_extend, array( 'create', 'extend' ) ) ) {
			return pods_error( __( 'Please choose whether to Create or Extend a Content Type', 'pods' ), $this );
		}

		$pod_params = array(
			'name'    => '',
			'label'   => '',
			'type'    => '',
			'storage' => 'table',
			'object'  => '',
			'options' => array()
		);

		if ( 'create' === $params->create_extend ) {
			$label = ucwords( str_replace( '_', ' ', $params->create_name ) );

			if ( ! empty( $params->create_label_singular ) ) {
				$label = $params->create_label_singular;
			}

			$pod_params['name']    = $params->create_name;
			$pod_params['label']   = ( ! empty( $params->create_label_plural ) ? $params->create_label_plural : $label );
			$pod_params['type']    = $params->create_pod_type;
			$pod_params['options'] = array(
				'label_singular' => ( ! empty( $params->create_label_singular ) ? $params->create_label_singular : $pod_params['label'] ),
				'public'         => 1,
				'show_ui'        => 1
			);

			// Auto-generate name if not provided
			if ( empty( $pod_params['name'] ) && ! empty( $pod_params['options']['label_singular'] ) ) {
				$pod_params['name'] = pods_clean_name( $pod_params['options']['label_singular'] );
			}

			if ( 'post_type' === $pod_params['type'] ) {
				if ( empty( $pod_params['name'] ) ) {
					return pods_error( 'Please enter a Name for this Pod', $this );
				}

				$pod_params['storage'] = $params->create_storage;

				if ( pods_tableless() ) {
					$pod_params['storage'] = 'meta';
				}
			} elseif ( 'taxonomy' === $pod_params['type'] ) {
				if ( empty( $pod_params['name'] ) ) {
					return pods_error( 'Please enter a Name for this Pod', $this );
				}

				$pod_params['storage'] = $params->create_storage;

				if ( ! function_exists( 'get_term_meta' ) || ! empty( $params->create_storage_taxonomy ) ) {
					$pod_params['storage'] = $params->create_storage_taxonomy;
				}

				if ( pods_tableless() ) {
					$pod_params['storage'] = ( function_exists( 'get_term_meta' ) ? 'meta' : 'none' );
				}

				$pod_params['options']['hierarchical'] = 1;
			} elseif ( 'pod' === $pod_params['type'] ) {
				if ( empty( $pod_params['name'] ) ) {
					return pods_error( 'Please enter a Name for this Pod', $this );
				}

				if ( pods_tableless() ) {
					$pod_params['type']    = 'post_type';
					$pod_params['storage'] = 'meta';
				}
			} elseif ( 'settings' === $pod_params['type'] ) {
				$pod_params['name']    = $params->create_setting_name;
				$pod_params['label']   = ( ! empty( $params->create_label_title ) ? $params->create_label_title : ucwords( str_replace( '_', ' ', $params->create_setting_name ) ) );
				$pod_params['options'] = array(
					'menu_name'     => ( ! empty( $params->create_label_menu ) ? $params->create_label_menu : $pod_params['label'] ),
					'menu_location' => $params->create_menu_location
				);
				$pod_params['storage'] = 'none';

				// Auto-generate name if not provided
				if ( empty( $pod_params['name'] ) && ! empty( $pod_params['label'] ) ) {
					$pod_params['name'] = pods_clean_name( $pod_params['label'] );
				}

				if ( empty( $pod_params['name'] ) ) {
					return pods_error( 'Please enter a Name for this Pod', $this );
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

				$pod_params['options']['supports_title']  = 1;
				$pod_params['options']['supports_editor'] = 1;
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
	 * $params['options'] array Options
	 *
	 * @param array    $params    An associative array of parameters
	 * @param bool     $sanitized (optional) Decides whether the params have been sanitized before being passed, will
	 *                            sanitize them if false.
	 * @param bool|int $db        (optional) Whether to save into the DB or just return Pod array.
	 *
	 * @return int Pod ID
	 * @since 1.7.9
	 */
	public function save_pod( $params, $sanitized = false, $db = true ) {

		$tableless_field_types    = PodsForm::tableless_field_types();
		$simple_tableless_objects = PodsForm::simple_tableless_objects();

		$load_params = (object) $params;

		if ( isset( $load_params->id ) && isset( $load_params->name ) ) {
			unset( $load_params->name );
		}

		if ( isset( $load_params->old_name ) ) {
			$load_params->name = $load_params->old_name;
		}

		$load_params->table_info = true;

		$pod = $this->load_pod( $load_params, false );

		$params = (object) $params;

		if ( false === $sanitized ) {
			$params = pods_sanitize( $params );
		}

		$old_id      = null;
		$old_name    = null;
		$old_storage = null;

		$old_fields  = array();
		$old_options = array();

		if ( isset( $params->name ) && ! isset( $params->object ) ) {
			$params->name = pods_clean_name( $params->name );
		}

		if ( ! empty( $pod ) ) {
			if ( isset( $params->id ) && 0 < $params->id ) {
				$old_id = $params->id;
			}

			$params->id = $pod['id'];

			$old_name    = $pod['name'];
			$old_storage = $pod['storage'];
			$old_fields  = $pod['fields'];
			$old_options = $pod['options'];

			if ( ! isset( $params->name ) && empty( $params->name ) ) {
				$params->name = $pod['name'];
			}

			if ( $old_name !== $params->name && false !== $this->pod_exists( array( 'name' => $params->name ) ) ) {
				return pods_error( sprintf( __( 'Pod %1$s already exists, you cannot rename %2$s to that', 'pods' ), $params->name, $old_name ), $this );
			}

			if ( $old_name !== $params->name && in_array( $pod['type'], array(
					'user',
					'comment',
					'media'
				) ) && in_array( $pod['object'], array( 'user', 'comment', 'media' ) ) ) {
				return pods_error( sprintf( __( 'Pod %s cannot be renamed, it extends an existing WP Object', 'pods' ), $old_name ), $this );
			}

			if ( $old_name !== $params->name && in_array( $pod['type'], array(
					'post_type',
					'taxonomy'
				) ) && ! empty( $pod['object'] ) && $pod['object'] == $old_name ) {
				return pods_error( sprintf( __( 'Pod %s cannot be renamed, it extends an existing WP Object', 'pods' ), $old_name ), $this );
			}

			if ( $old_id != $params->id ) {
				if ( $params->type == $pod['type'] && isset( $params->object ) && $params->object == $pod['object'] ) {
					return pods_error( sprintf( __( 'Pod using %s already exists, you can not reuse an object across multiple pods', 'pods' ), $params->object ), $this );
				} else {
					return pods_error( sprintf( __( 'Pod %s already exists', 'pods' ), $params->name ), $this );
				}
			}
		} elseif ( in_array( $params->name, array(
				'order',
				'orderby',
				'post_type'
			) ) && 'post_type' === pods_var( 'type', $params ) ) {
			return pods_error( sprintf( 'There are certain names that a Custom Post Types cannot be named and unfortunately, %s is one of them.', $params->name ), $this );
		} else {
			$pod = array(
				'id'          => 0,
				'name'        => $params->name,
				'label'       => $params->name,
				'description' => '',
				'type'        => 'pod',
				'storage'     => 'table',
				'object'      => '',
				'alias'       => '',
				'options'     => array(),
				'fields'      => array()
			);
		}

		// Blank out fields and options for AJAX calls (everything should be sent to it for a full overwrite)
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			$pod['fields']  = array();
			$pod['options'] = array();
		}

		// Setup options
		$options = get_object_vars( $params );

		if ( isset( $options['method'] ) ) {
			unset( $options['method'] );
		}

		$options_ignore = array(
			'object_type',
			'object_name',
			'table',
			'meta_table',
			'pod_table',
			'field_id',
			'field_index',
			'field_slug',
			'field_type',
			'field_parent',
			'field_parent_select',
			'meta_field_id',
			'meta_field_index',
			'meta_field_value',
			'pod_field_id',
			'pod_field_index',
			'object_fields',
			'join',
			'where',
			'where_default',
			'orderby',
			'pod',
			'recurse',
			'table_info',
			'attributes',
			'group',
			'grouped',
			'developer_mode',
			'dependency',
			'depends-on',
			'excludes-on'
		);

		foreach ( $options_ignore as $ignore ) {
			if ( isset( $options[ $ignore ] ) ) {
				unset( $options[ $ignore ] );
			}
		}

		$exclude = array(
			'id',
			'name',
			'label',
			'description',
			'type',
			'storage',
			'object',
			'alias',
			'options',
			'fields'
		);

		foreach ( $exclude as $k => $exclude_field ) {
			$aliases = array( $exclude_field );

			if ( is_array( $exclude_field ) ) {
				$aliases       = array_merge( array( $k ), $exclude_field );
				$exclude_field = $k;
			}

			foreach ( $aliases as $alias ) {
				if ( isset( $options[ $alias ] ) ) {
					$pod[ $exclude_field ] = pods_trim( $options[ $alias ] );

					unset( $options[ $alias ] );
				}
			}
		}

		if ( pods_tableless() && ! in_array( $pod['type'], array( 'settings', 'table' ) ) ) {
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

		$pod['options']['type']    = $pod['type'];
		$pod['options']['storage'] = $pod['storage'];
		$pod['options']['object']  = $pod['object'];
		$pod['options']['alias']   = $pod['alias'];

		$pod['options'] = array_merge( $pod['options'], $options );

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

		if ( isset( $pod['options']['query_var_string'] ) ) {
			if ( in_array( $pod['options']['query_var_string'], $reserved_query_vars ) ) {
				$pod['options']['query_var_string'] = $pod['options']['type'] . '_' . $pod['options']['query_var_string'];
			}
		}

		if ( isset( $pod['options']['query_var'] ) ) {
			if ( in_array( $pod['options']['query_var'], $reserved_query_vars ) ) {
				$pod['options']['query_var'] = $pod['options']['type'] . '_' . $pod['options']['query_var'];
			}
		}

		if ( strlen( $pod['label'] ) < 1 ) {
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
			if ( strlen( $params->name ) < 1 ) {
				return pods_error( __( 'Pod name cannot be empty', 'pods' ), $this );
			}

			$post_data = array(
				'post_name'    => $pod['name'],
				'post_title'   => $pod['label'],
				'post_content' => $pod['description'],
				'post_type'    => '_pods_pod',
				'post_status'  => 'publish'
			);

			if ( 'pod' === $pod['type'] && ( ! is_array( $pod['fields'] ) || empty( $pod['fields'] ) ) ) {
				$pod['fields'] = array();

				$pod['fields']['name'] = array(
					'name'    => 'name',
					'label'   => 'Name',
					'type'    => 'text',
					'options' => array(
						'required' => '1'
					)
				);

				$pod['fields']['created'] = array(
					'name'    => 'created',
					'label'   => 'Date Created',
					'type'    => 'datetime',
					'options' => array(
						'datetime_format'      => 'ymd_slash',
						'datetime_time_type'   => '12',
						'datetime_time_format' => 'h_mm_ss_A'
					)
				);

				$pod['fields']['modified'] = array(
					'name'    => 'modified',
					'label'   => 'Date Modified',
					'type'    => 'datetime',
					'options' => array(
						'datetime_format'      => 'ymd_slash',
						'datetime_time_type'   => '12',
						'datetime_time_format' => 'h_mm_ss_A'
					)
				);

				$pod['fields']['author'] = array(
					'name'        => 'author',
					'label'       => 'Author',
					'type'        => 'pick',
					'pick_object' => 'user',
					'options'     => array(
						'pick_format_type'   => 'single',
						'pick_format_single' => 'autocomplete',
						'default_value'      => '{@user.ID}'
					)
				);

				$pod['fields']['permalink'] = array(
					'name'        => 'permalink',
					'label'       => 'Permalink',
					'type'        => 'slug',
					'description' => 'Leave blank to auto-generate from Name'
				);

				if ( ! isset( $pod['options']['pod_index'] ) ) {
					$pod['options']['pod_index'] = 'name';
				}
			}

			$pod = $this->do_hook( 'save_pod_default_pod', $pod, $params, $sanitized, $db );

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

			$params->id = $this->save_wp_object( 'post', $post_data, $pod['options'], true, true );

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

		// Setup / update tables
		if ( 'table' !== $pod['type'] && 'table' === $pod['storage'] && $old_storage !== $pod['storage'] && $db ) {
			$definitions = array( "`id` BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY" );

			$defined_fields = array();

			foreach ( $pod['fields'] as $field ) {
				if ( ! is_array( $field ) || ! isset( $field['name'] ) || in_array( $field['name'], $defined_fields ) ) {
					continue;
				}

				$defined_fields[] = $field['name'];

				if ( ! in_array( $field['type'], $tableless_field_types ) || ( 'pick' === $field['type'] && in_array( pods_var( 'pick_object', $field ), $simple_tableless_objects ) ) ) {
					$definition = $this->get_field_definition( $field['type'], array_merge( $field, pods_var_raw( 'options', $field, array() ) ) );

					if ( 0 < strlen( $definition ) ) {
						$definitions[] = "`{$field['name']}` " . $definition;
					}
				}
			}

			pods_query( "DROP TABLE IF EXISTS `@wp_pods_{$params->name}`" );

			$result = pods_query( "CREATE TABLE `@wp_pods_{$params->name}` (" . implode( ', ', $definitions ) . ") DEFAULT CHARSET utf8", $this );

			if ( empty( $result ) ) {
				return pods_error( __( 'Cannot add Database Table for Pod', 'pods' ), $this );
			}

		} elseif ( 'table' !== $pod['type'] && 'table' === $pod['storage'] && $pod['storage'] == $old_storage && null !== $old_name && $old_name !== $params->name && $db ) {
			$result = pods_query( "ALTER TABLE `@wp_pods_{$old_name}` RENAME `@wp_pods_{$params->name}`", $this );

			if ( empty( $result ) ) {
				return pods_error( __( 'Cannot update Database Table for Pod', 'pods' ), $this );
			}
		}

		/**
		 * @var $wpdb wpdb
		 */
		global $wpdb;

		if ( null !== $old_name && $old_name !== $params->name && $db ) {
			// Rename items in the DB pointed at the old WP Object names
			if ( 'post_type' === $pod['type'] && empty( $pod['object'] ) ) {
				$this->rename_wp_object_type( 'post', $old_name, $params->name );
			} elseif ( 'taxonomy' === $pod['type'] && empty( $pod['object'] ) ) {
				$this->rename_wp_object_type( 'taxonomy', $old_name, $params->name );
			} elseif ( 'comment' === $pod['type'] && empty( $pod['object'] ) ) {
				$this->rename_wp_object_type( 'comment', $old_name, $params->name );
			} elseif ( 'settings' === $pod['type'] ) {
				$this->rename_wp_object_type( 'settings', $old_name, $params->name );
			}

			// Sync any related fields if the name has changed
			$fields = pods_query( "
				SELECT `p`.`ID`
				FROM `{$wpdb->posts}` AS `p`
				LEFT JOIN `{$wpdb->postmeta}` AS `pm` ON `pm`.`post_id` = `p`.`ID`
				LEFT JOIN `{$wpdb->postmeta}` AS `pm2` ON `pm2`.`post_id` = `p`.`ID`
				WHERE
					`p`.`post_type` = '_pods_field'
					AND `pm`.`meta_key` = 'pick_object'
					AND (
						`pm`.`meta_value` = 'pod'
						OR `pm`.`meta_value` = '" . $pod['type'] . "'
					)
					AND `pm2`.`meta_key` = 'pick_val'
					AND `pm2`.`meta_value` = '{$old_name}'
			" );

			if ( ! empty( $fields ) ) {
				foreach ( $fields as $field ) {
					update_post_meta( $field->ID, 'pick_object', $pod['type'] );
					update_post_meta( $field->ID, 'pick_val', $params->name );
				}
			}

			$fields = pods_query( "
				SELECT `p`.`ID`
				FROM `{$wpdb->posts}` AS `p`
				LEFT JOIN `{$wpdb->postmeta}` AS `pm` ON `pm`.`post_id` = `p`.`ID`
				WHERE
					`p`.`post_type` = '_pods_field'
					AND `pm`.`meta_key` = 'pick_object'
					AND (
						`pm`.`meta_value` = 'pod-{$old_name}'
						OR `pm`.`meta_value` = '" . $pod['type'] . "-{$old_name}'
					)
			" );

			if ( ! empty( $fields ) ) {
				foreach ( $fields as $field ) {
					update_post_meta( $field->ID, 'pick_object', $pod['type'] );
					update_post_meta( $field->ID, 'pick_val', $params->name );
				}
			}
		}

		// Sync built-in options for post types and taxonomies
		if ( in_array( $pod['type'], array( 'post_type', 'taxonomy' ) ) && empty( $pod['object'] ) && $db ) {
			// Build list of 'built_in' for later
			$built_in = array();

			foreach ( $pod['options'] as $key => $val ) {
				if ( false === strpos( $key, 'built_in_' ) ) {
					continue;
				} elseif ( false !== strpos( $key, 'built_in_post_types_' ) ) {
					$built_in_type = 'post_type';
				} elseif ( false !== strpos( $key, 'built_in_taxonomies_' ) ) {
					$built_in_type = 'taxonomy';
				} else {
					continue;
				}

				if ( $built_in_type == $pod['type'] ) {
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

			if ( ! empty( $lookup_option ) && ! empty( $lookup_built_in ) && isset( $built_in[ $lookup_built_in ] ) ) {
				foreach ( $built_in[ $lookup_built_in ] as $built_in_object => $val ) {
					$search_val = 1;

					if ( 1 == $val ) {
						$search_val = 0;
					}

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
			}
		}

		$saved  = array();
		$errors = array();

		$field_index_change = false;
		$field_index_id     = 0;

		$id_required = false;

		$field_index = pods_var( 'pod_index', $pod['options'], 'id', null, true );

		if ( 'pod' === $pod['type'] && ! empty( $pod['fields'] ) && isset( $pod['fields'][ $field_index ] ) ) {
			$field_index_id = $pod['fields'][ $field_index ];
		}

		if ( isset( $params->fields ) || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			$fields = array();

			if ( isset( $params->fields ) ) {
				$params->fields = (array) $params->fields;

				$weight = 0;

				foreach ( $params->fields as $field ) {
					if ( ! is_array( $field ) || ! isset( $field['name'] ) ) {
						continue;
					}

					if ( ! isset( $field['weight'] ) ) {
						$field['weight'] = $weight;

						$weight ++;
					}

					$fields[ $field['name'] ] = $field;
				}
			}

			$weight = 0;

			$saved_field_ids = array();

			foreach ( $pod['fields'] as $k => $field ) {
				if ( ! empty( $old_id ) && ( ! is_array( $field ) || ! isset( $field['name'] ) || ! isset( $fields[ $field['name'] ] ) ) ) {
					// Iterative change handling for setup-edit.php
					if ( ! is_array( $field ) && isset( $old_fields[ $k ] ) ) {
						$saved[ $old_fields[ $k ]['name'] ] = true;
					}

					continue;
				}

				if ( ! empty( $old_id ) ) {
					$field = array_merge( $field, $fields[ $field['name'] ] );
				}

				$field['pod'] = $pod;

				if ( ! isset( $field['weight'] ) ) {
					$field['weight'] = $weight;

					$weight ++;
				}

				if ( 0 < $field_index_id && pods_var( 'id', $field ) == $field_index_id ) {
					$field_index_change = $field['name'];
				}

				if ( 0 < pods_var( 'id', $field ) ) {
					$id_required = true;
				}

				if ( $id_required ) {
					$field['id_required'] = true;
				}

				$field_data = $field;

				$field = $this->save_field( $field_data, $field_table_operation, true, $db );

				if ( true !== $db ) {
					$pod['fields'][ $k ] = $field;
					$saved_field_ids[]   = $field['id'];
				} else {
					if ( ! empty( $field ) && 0 < $field ) {
						$saved[ $field_data['name'] ] = true;
						$saved_field_ids[]            = $field;
					} else {
						$errors[] = sprintf( __( 'Cannot save the %s field', 'pods' ), $field_data['name'] );
					}
				}
			}

			if ( true === $db ) {
				foreach ( $old_fields as $field ) {
					if ( isset( $pod['fields'][ $field['name'] ] ) || isset( $saved[ $field['name'] ] ) || in_array( $field['id'], $saved_field_ids ) ) {
						continue;
					}

					if ( $field['id'] == $field_index_id ) {
						$field_index_change = 'id';
					} elseif ( $field['name'] == $field_index ) {
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

		if ( ! empty( $errors ) ) {
			return pods_error( $errors, $this );
		}

		$this->cache_flush_pods( $pod );

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

		// Register Post Types / Taxonomies post-registration from PodsInit
		if ( ! empty( PodsInit::$content_types_registered ) && in_array( $pod['type'], array(
				'post_type',
				'taxonomy'
			) ) && empty( $pod['object'] ) ) {
			global $pods_init;

			$pods_init->setup_content_types( true );
		}

		if ( true === $db ) {
			return $pod['id'];
		} else {
			return $pod;
		}
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
	 * $params['options'] array (optional) Options
	 *
	 * @param array    $params          An associative array of parameters
	 * @param bool     $table_operation (optional) Whether or not to handle table operations
	 * @param bool     $sanitized       (optional) Decides wether the params have been sanitized before being passed,
	 *                                  will sanitize them if false.
	 * @param bool|int $db              (optional) Whether to save into the DB or just return field array.
	 *
	 * @return int|array The field ID or field array (if !$db)
	 * @since 1.7.9
	 */
	public function save_field( $params, $table_operation = true, $sanitized = false, $db = true ) {

		/**
		 * @var $wpdb wpdb
		 */
		global $wpdb;

		if ( true !== $db ) {
			$table_operation = false;
		}

		$tableless_field_types    = PodsForm::tableless_field_types();
		$simple_tableless_objects = PodsForm::simple_tableless_objects();

		$params = (object) $params;

		if ( false === $sanitized ) {
			$params = pods_sanitize( $params );
		}

		if ( isset( $params->pod_id ) ) {
			$params->pod_id = pods_absint( $params->pod_id );
		}

		if ( true !== $db ) {
			$params->pod_id = (int) $db;
		}

		$pod         = null;
		$save_pod    = false;
		$id_required = false;

		if ( isset( $params->id_required ) ) {
			unset( $params->id_required );

			$id_required = true;
		}

		if ( ( ! isset( $params->pod ) || empty( $params->pod ) ) && ( ! isset( $params->pod_id ) || empty( $params->pod_id ) ) ) {
			return pods_error( __( 'Pod ID or name is required', 'pods' ), $this );
		}

		if ( isset( $params->pod ) && is_array( $params->pod ) ) {
			$pod = $params->pod;

			$save_pod = true;
		} elseif ( ( ! isset( $params->pod_id ) || empty( $params->pod_id ) ) && ( true === $db || 0 < $db ) ) {
			$pod = $this->load_pod( array( 'name' => $params->pod, 'table_info' => true ) );
		} elseif ( ! isset( $params->pod ) && ( true === $db || 0 < $db ) ) {
			$pod = $this->load_pod( array( 'id' => $params->pod_id, 'table_info' => true ) );
		} elseif ( true === $db || 0 < $db ) {
			$pod = $this->load_pod( array( 'id' => $params->pod_id, 'name' => $params->pod, 'table_info' => true ) );
		}

		if ( empty( $pod ) && true === $db ) {
			return pods_error( __( 'Pod not found', 'pods' ), $this );
		}

		$params->pod_id   = $pod['id'];
		$params->pod      = $pod['name'];
		$params->pod_data = $pod;

		$params->name = pods_clean_name( $params->name, true, ( 'meta' === $pod['storage'] ? false : true ) );

		if ( ! isset( $params->id ) ) {
			$params->id = 0;
		}

		if ( empty( $params->name ) ) {
			return pods_error( 'Pod field name is required', $this );
		}

		$field = $this->load_field( $params );

		unset( $params->pod_data );

		$old_id         = null;
		$old_name       = null;
		$old_type       = null;
		$old_definition = null;
		$old_simple     = null;
		$old_options    = null;
		$old_sister_id  = null;

		if ( ! empty( $field ) ) {
			$old_id        = pods_var( 'id', $field );
			$old_name      = pods_clean_name( $field['name'], true, ( 'meta' === $pod['storage'] ? false : true ) );
			$old_type      = $field['type'];
			$old_options   = $field['options'];
			$old_sister_id = (int) pods_var( 'sister_id', $old_options, 0 );

			$old_simple = ( 'pick' === $old_type && in_array( pods_var( 'pick_object', $field ), $simple_tableless_objects ) );

			if ( isset( $params->name ) && ! empty( $params->name ) ) {
				$field['name'] = $params->name;
			}

			if ( $old_name !== $field['name'] && false !== $this->field_exists( $params ) ) {
				return pods_error( sprintf( __( 'Field %1$s already exists, you cannot rename %2$s to that', 'pods' ), $field['name'], $old_name ), $this );
			}

			if ( ( $id_required || ! empty( $params->id ) ) && ( empty( $old_id ) || $old_id != $params->id ) ) {
				return pods_error( sprintf( __( 'Field %s already exists', 'pods' ), $field['name'] ), $this );
			}

			if ( empty( $params->id ) ) {
				$params->id = $old_id;
			}

			if ( ! in_array( $old_type, $tableless_field_types ) || $old_simple ) {
				$definition = $this->get_field_definition( $old_type, array_merge( $field, $old_options ) );

				if ( 0 < strlen( $definition ) ) {
					$old_definition = "`{$old_name}` " . $definition;
				}
			}
		} else {
			$field = array(
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
				'options'     => array()
			);
		}

		// Setup options
		$options = get_object_vars( $params );

		$options_ignore = array(
			'method',
			'table_info',
			'attributes',
			'group',
			'grouped',
			'developer_mode',
			'dependency',
			'depends-on',
			'excludes-on'
		);

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

		$exclude = array(
			'id',
			'pod_id',
			'pod',
			'name',
			'label',
			'description',
			'type',
			'pick_object',
			'pick_val',
			'sister_id',
			'weight',
			'options'
		);

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

		if ( strlen( $field['label'] ) < 1 ) {
			$field['label'] = $field['name'];
		}

		$field['options']['type'] = $field['type'];

		if ( in_array( $field['options']['type'], $tableless_field_types ) ) {
			// Clean up special drop-down in field editor and save out pick_val
			$field['pick_object'] = pods_var( 'pick_object', $field, '', null, true );

			if ( 0 === strpos( $field['pick_object'], 'pod-' ) ) {
				$field['pick_val']    = pods_str_replace( 'pod-', '', $field['pick_object'], 1 );
				$field['pick_object'] = 'pod';
			} elseif ( 0 === strpos( $field['pick_object'], 'post_type-' ) ) {
				$field['pick_val']    = pods_str_replace( 'post_type-', '', $field['pick_object'], 1 );
				$field['pick_object'] = 'post_type';
			} elseif ( 0 === strpos( $field['pick_object'], 'taxonomy-' ) ) {
				$field['pick_val']    = pods_str_replace( 'taxonomy-', '', $field['pick_object'], 1 );
				$field['pick_object'] = 'taxonomy';
			} elseif ( 'table' === $field['pick_object'] && 0 < strlen( pods_var_raw( 'pick_table', $field['options'] ) ) ) {
				$field['pick_val']    = $field['options']['pick_table'];
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

			$field['options']['pick_object'] = $field['pick_object'];
			$field['options']['pick_val']    = $field['pick_val'];
			$field['options']['sister_id']   = pods_var( 'sister_id', $field );

			unset( $field['pick_object'] );
			unset( $field['pick_val'] );

			if ( isset( $field['sister_id'] ) ) {
				unset( $field['sister_id'] );
			}
		}

		$field['options'] = array_merge( $field['options'], $options );

		$object_fields = (array) pods_var_raw( 'object_fields', $pod, array(), null, true );

		if ( 0 < $old_id && defined( 'PODS_FIELD_STRICT' ) && ! PODS_FIELD_STRICT ) {
			$params->id  = $old_id;
			$field['id'] = $old_id;
		}

		// Add new field
		if ( ! isset( $params->id ) || empty( $params->id ) || empty( $field ) ) {
			if ( $table_operation && in_array( $field['name'], array(
					'created',
					'modified'
				) ) && ! in_array( $field['type'], array(
					'date',
					'datetime'
				) ) && ( ! defined( 'PODS_FIELD_STRICT' ) || PODS_FIELD_STRICT ) ) {
				return pods_error( sprintf( __( '%s is reserved for internal Pods usage, please try a different name', 'pods' ), $field['name'] ), $this );
			}

			if ( $table_operation && 'author' === $field['name'] && 'pick' !== $field['type'] && ( ! defined( 'PODS_FIELD_STRICT' ) || PODS_FIELD_STRICT ) ) {
				return pods_error( sprintf( __( '%s is reserved for internal Pods usage, please try a different name', 'pods' ), $field['name'] ), $this );
			}

			if ( in_array( $field['name'], array( 'id', 'ID' ) ) ) {
				return pods_error( sprintf( __( '%s is reserved for internal Pods usage, please try a different name', 'pods' ), $field['name'] ), $this );
			}

			foreach ( $object_fields as $object_field => $object_field_opt ) {
				if ( $object_field == $field['name'] || in_array( $field['name'], $object_field_opt['alias'] ) ) {
					return pods_error( sprintf( __( '%s is reserved for internal WordPress or Pods usage, please try a different name. Also consider what WordPress and Pods provide you built-in.', 'pods' ), $field['name'] ), $this );
				}
			}

			if ( in_array( $field['name'], array( 'rss' ) ) ) // Reserved post_name values that can't be used as field names
			{
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

			if ( null !== $old_name && $field['name'] !== $old_name && in_array( $field['name'], array(
					'created',
					'modified'
				) ) && ! in_array( $field['type'], array(
					'date',
					'datetime'
				) ) && ( ! defined( 'PODS_FIELD_STRICT' ) || PODS_FIELD_STRICT ) ) {
				return pods_error( sprintf( __( '%s is reserved for internal Pods usage, please try a different name', 'pods' ), $field['name'] ), $this );
			}

			if ( null !== $old_name && $field['name'] !== $old_name && 'author' === $field['name'] && 'pick' !== $field['type'] && ( ! defined( 'PODS_FIELD_STRICT' ) || PODS_FIELD_STRICT ) ) {
				return pods_error( sprintf( __( '%s is reserved for internal Pods usage, please try a different name', 'pods' ), $field['name'] ), $this );
			}

			foreach ( $object_fields as $object_field => $object_field_opt ) {
				if ( $object_field !== $field['name'] && ! in_array( $field['name'], $object_field_opt['alias'] ) ) {
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
				$field['options']['old_name'] = $old_name;
			}

			$params->id = $this->save_wp_object( 'post', $post_data, $field['options'], true, true );

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

		$simple = ( 'pick' === $field['type'] && in_array( pods_var( 'pick_object', $field['options'] ), $simple_tableless_objects ) );

		$definition = false;

		if ( ! in_array( $field['type'], $tableless_field_types ) || $simple ) {
			$field_definition = $this->get_field_definition( $field['type'], array_merge( $field, $field['options'] ) );

			if ( 0 < strlen( $field_definition ) ) {
				$definition = '`' . $field['name'] . '` ' . $field_definition;
			}
		}

		$sister_id = (int) pods_var( 'sister_id', $field['options'], 0 );

		if ( $table_operation && 'table' === $pod['storage'] && ! pods_tableless() ) {
			if ( ! empty( $old_id ) ) {
				if ( ( $field['type'] !== $old_type || $old_simple != $simple ) && empty( $definition ) ) {
					pods_query( "ALTER TABLE `@wp_pods_{$params->pod}` DROP COLUMN `{$old_name}`", false );
				} elseif ( 0 < strlen( $definition ) ) {
					if ( $old_name !== $field['name'] || $old_simple != $simple ) {
						$test = false;

						if ( 0 < strlen( $old_definition ) ) {
							$test = pods_query( "ALTER TABLE `@wp_pods_{$params->pod}` CHANGE `{$old_name}` {$definition}", false );
						}

						// If the old field doesn't exist, continue to add a new field
						if ( false === $test ) {
							pods_query( "ALTER TABLE `@wp_pods_{$params->pod}` ADD COLUMN {$definition}", __( 'Cannot create new field', 'pods' ) );
						}
					} elseif ( null !== $old_definition && $definition !== $old_definition ) {
						$test = pods_query( "ALTER TABLE `@wp_pods_{$params->pod}` CHANGE `{$old_name}` {$definition}", false );

						// If the old field doesn't exist, continue to add a new field
						if ( false === $test ) {
							pods_query( "ALTER TABLE `@wp_pods_{$params->pod}` ADD COLUMN {$definition}", __( 'Cannot create new field', 'pods' ) );
						}
					}
				}
			} elseif ( 0 < strlen( $definition ) ) {
				$test = false;

				if ( 0 < strlen( $old_definition ) ) {
					$test = pods_query( "ALTER TABLE `@wp_pods_{$params->pod}` CHANGE `" . $field['name'] . "` {$definition}", false );
				}

				// If the old field doesn't exist, continue to add a new field
				if ( false === $test ) {
					pods_query( "ALTER TABLE `@wp_pods_{$params->pod}` ADD COLUMN {$definition}", __( 'Cannot create new field', 'pods' ) );
				}
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

		if ( $field['type'] !== $old_type && in_array( $old_type, $tableless_field_types ) ) {
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
						$params->id
					) );

				if ( ! pods_tableless() ) {
					pods_query( "DELETE FROM @wp_podsrel WHERE `field_id` = {$params->id}", false );

					pods_query( "
							UPDATE `@wp_podsrel`
							SET `related_field_id` = 0
							WHERE `field_id` = %d
						", array(
							$old_sister_id
						) );
				}
			}
		} elseif ( 0 < $sister_id ) {
			update_post_meta( $sister_id, 'sister_id', $params->id );

			if ( true === $db && ( ! pods_tableless() ) ) {
				pods_query( "
						UPDATE `@wp_podsrel`
						SET `related_field_id` = %d
						WHERE `field_id` = %d
					", array(
						$params->id,
						$sister_id
					) );
			}
		} elseif ( 0 < $old_sister_id ) {
			delete_post_meta( $old_sister_id, 'sister_id' );

			if ( true === $db && ( ! pods_tableless() ) ) {
				pods_query( "
						UPDATE `@wp_podsrel`
						SET `related_field_id` = 0
						WHERE `field_id` = %d
					", array(
						$old_sister_id
					) );
			}
		}

		if ( ! empty( $old_id ) && $old_name !== $field['name'] && true === $db ) {
			pods_query( "
					UPDATE `@wp_postmeta`
					SET `meta_value` = %s
					WHERE
						`post_id` = %d
						AND `meta_key` = 'pod_index'
						AND `meta_value` = %s
				", array(
					$field['name'],
					$pod['id'],
					$old_name
				) );
		}

		if ( ! $save_pod ) {
			$this->cache_flush_pods( $pod );
		} else {
			pods_transient_clear( 'pods_field_' . $pod['name'] . '_' . $field['name'] );

			if ( ! empty( $old_id ) && $old_name !== $field['name'] ) {
				pods_transient_clear( 'pods_field_' . $pod['name'] . '_' . $old_name );
			}
		}

		if ( true === $db ) {
			return $params->id;
		} else {
			return $field;
		}
	}

	/**
	 * Fix Pod / Field post_name to ensure they are exactly as saved (allow multiple posts w/ same post_name)
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

		if ( in_array( $post_type, array( '_pods_field', '_pods_pod' ) ) && false !== strpos( $slug, '-' ) ) {
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
	 * $params['options'] Associative array of Object options
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
		}

		if ( ! isset( $params->name ) || empty( $params->name ) ) {
			return pods_error( __( 'Name must be given to save an Object', 'pods' ), $this );
		}

		if ( ! isset( $params->type ) || empty( $params->type ) ) {
			return pods_error( __( 'Type must be given to save an Object', 'pods' ), $this );
		}

		$object = array(
			'id'      => 0,
			'name'    => $params->name,
			'type'    => $params->type,
			'code'    => '',
			'options' => array()
		);

		// Setup options
		$options = get_object_vars( $params );

		if ( isset( $options['method'] ) ) {
			unset( $options['method'] );
		}

		$exclude = array(
			'id',
			'name',
			'helper_type',
			'code',
			'options',
			'status'
		);

		foreach ( $exclude as $k => $exclude_field ) {
			$aliases = array( $exclude_field );

			if ( is_array( $exclude_field ) ) {
				$aliases       = array_merge( array( $k ), $exclude_field );
				$exclude_field = $k;
			}

			foreach ( $aliases as $alias ) {
				if ( isset( $options[ $alias ] ) ) {
					$object[ $exclude_field ] = pods_trim( $options[ $alias ] );

					unset( $options[ $alias ] );
				}
			}
		}

		if ( 'helper' === $object['type'] ) {
			$object['options']['helper_type'] = $object['helper_type'];
		}

		if ( isset( $object['options']['code'] ) ) {
			unset( $object['options']['code'] );
		}

		$object['options'] = array_merge( $object['options'], $options );

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

		if ( null !== pods_var( 'status', $object, null, null, true ) ) {
			$post_data['post_status'] = pods_var( 'status', $object, null, null, true );
		}

		remove_filter( 'content_save_pre', 'balanceTags', 50 );

		$post_data = pods_sanitize( $post_data );

		$params->id = $this->save_post( $post_data, $object['options'], true, true );

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
	 * @param bool         $sanitized (optional) Decides wether the params have been sanitized before being passed,
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
	 * @param bool         $sanitized (optional) Decides wether the params have been sanitized before being passed,
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
	 * @param bool  $sanitized (optional) Decides wether the params have been sanitized before being passed, will
	 *                         sanitize them if false.
	 *
	 * @return int The helper ID
	 * @since 1.7.9
	 */
	public function save_helper( $params, $sanitized = false ) {

		$params = (object) $params;

		if ( isset( $params->phpcode ) ) {
			$params->code = $params->phpcode;
			unset( $params->phpcode );
		}

		if ( isset( $params->type ) ) {
			$params->helper_type = $params->type;
			unset( $params->type );
		}

		$params->type = 'helper';

		return $this->save_object( $params, $sanitized );
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
	 * @since 1.7.9
	 */
	public function save_pod_item( $params ) {

		global $wpdb;

		$params = (object) pods_str_replace( '@wp_', '{prefix}', $params );

		$tableless_field_types    = PodsForm::tableless_field_types();
		$repeatable_field_types   = PodsForm::repeatable_field_types();
		$simple_tableless_objects = PodsForm::simple_tableless_objects();

		$error_mode = $this->display_errors;

		if ( ! empty( $params->error_mode ) ) {
			$error_mode = $params->error_mode;
		}

		// @deprecated 2.0.0
		if ( isset( $params->datatype ) ) {
			pods_deprecated( '$params->pod instead of $params->datatype', '2.0' );

			$params->pod = $params->datatype;

			unset( $params->datatype );

			if ( isset( $params->pod_id ) ) {
				pods_deprecated( '$params->id instead of $params->pod_id', '2.0' );

				$params->id = $params->pod_id;

				unset( $params->pod_id );
			}

			if ( isset( $params->data ) && ! empty( $params->data ) && is_array( $params->data ) ) {
				$check = current( $params->data );

				if ( is_array( $check ) ) {
					pods_deprecated( 'PodsAPI::save_pod_items', '2.0' );

					return $this->save_pod_items( $params, $params->data );
				}
			}
		}

		// @deprecated 2.0.0
		if ( isset( $params->tbl_row_id ) ) {
			pods_deprecated( '$params->id instead of $params->tbl_row_id', '2.0' );

			$params->id = $params->tbl_row_id;

			unset( $params->tbl_row_id );
		}

		// @deprecated 2.0.0
		if ( isset( $params->columns ) ) {
			pods_deprecated( '$params->data instead of $params->columns', '2.0' );

			$params->data = $params->columns;

			unset( $params->columns );
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
		$pod = $this->load_pod( array( 'id' => $params->pod_id, 'name' => $params->pod, 'table_info' => true ) );

		if ( false === $pod ) {
			return pods_error( __( 'Pod not found', 'pods' ), $error_mode );
		}

		$params->pod    = $pod['name'];
		$params->pod_id = $pod['id'];

		if ( 'settings' === $pod['type'] ) {
			$params->id = $pod['id'];
		}

		$fields = $pod['fields'];

		$object_fields = (array) pods_var_raw( 'object_fields', $pod, array(), null, true );

		$fields_active = array();
		$custom_data   = array();

		// Find the active fields (loop through $params->data to retain order)
		if ( ! empty( $params->data ) && is_array( $params->data ) ) {
			$custom_fields = array();

			foreach ( $params->data as $field => $value ) {
				if ( isset( $object_fields[ $field ] ) ) {
					$object_fields[ $field ]['value'] = $value;
					$fields_active[]                  = $field;
				} elseif ( isset( $fields[ $field ] ) ) {
					if ( 'save' === $params->from || true === PodsForm::permission( $fields[ $field ]['type'], $field, $fields[ $field ], $fields, $pod, $params->id, $params ) ) {
						$fields[ $field ]['value'] = $value;
						$fields_active[]           = $field;
					} elseif ( ! pods_has_permissions( $fields[ $field ]['options'] ) && pods_var( 'hidden', $fields[ $field ]['options'], false ) ) {
						$fields[ $field ]['value'] = $value;
						$fields_active[]           = $field;
					}
				} else {
					$found = false;

					foreach ( $object_fields as $object_field => $object_field_opt ) {
						if ( in_array( $field, $object_field_opt['alias'] ) ) {
							$object_fields[ $object_field ]['value'] = $value;
							$fields_active[]                         = $object_field;

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

		if ( empty( $params->id ) && ! in_array( 'created', $fields_active ) && isset( $fields['created'] ) && in_array( $fields['created']['type'], array(
				'date',
				'datetime'
			) ) ) {
			$fields['created']['value'] = current_time( 'mysql' );
			$fields_active[]            = 'created';
		}

		if ( ! in_array( 'modified', $fields_active ) && isset( $fields['modified'] ) && in_array( $fields['modified']['type'], array(
				'date',
				'datetime'
			) ) ) {
			$fields['modified']['value'] = current_time( 'mysql' );
			$fields_active[]             = 'modified';
		}

		if ( in_array( $pod['type'], array(
				'pod',
				'table'
			) ) && empty( $params->id ) && ! empty( $pod['pod_field_index'] ) && in_array( $pod['pod_field_index'], $fields_active ) && ! in_array( $pod['pod_field_slug'], $fields_active ) && isset( $fields[ $pod['pod_field_slug'] ] ) ) {
			$fields[ $pod['pod_field_slug'] ]['value'] = ''; // this will get picked up by slug pre_save method
			$fields_active[]                           = $pod['pod_field_slug'];
		}

		// Handle hidden fields
		if ( empty( $params->id ) ) {
			foreach ( $fields as $field => $field_data ) {
				if ( in_array( $field, $fields_active ) ) {
					continue;
				}

				if ( in_array( $params->from, array(
						'save',
						'process_form'
					) ) || true === PodsForm::permission( $fields[ $field ]['type'], $field, $fields[ $field ], $fields, $pod, $params->id, $params ) ) {
					$value = PodsForm::default_value( pods_var_raw( $field, 'post' ), $field_data['type'], $field, pods_var_raw( 'options', $field_data, $field_data, null, true ), $pod, $params->id );

					if ( null !== $value && '' !== $value && false !== $value ) {
						$fields[ $field ]['value'] = $value;
						$fields_active[]           = $field;
					}
				}
			}

			// Set default field values for object fields
			if ( ! empty( $object_fields ) ) {
				foreach ( $object_fields as $field => $field_data ) {
					if ( in_array( $field, $fields_active ) ) {
						continue;
					} elseif ( ! isset( $field_data['default'] ) || strlen( $field_data['default'] ) < 1 ) {
						continue;
					}

					$value = PodsForm::default_value( pods_var_raw( $field, 'post' ), $field_data['type'], $field, pods_var_raw( 'options', $field_data, $field_data, null, true ), $pod, $params->id );

					if ( null !== $value && '' !== $value && false !== $value ) {
						$object_fields[ $field ]['value'] = $value;
						$fields_active[]                  = $field;
					}
				}
			}

			// Set default field values for Pod fields
			foreach ( $fields as $field => $field_data ) {
				if ( in_array( $field, $fields_active ) ) {
					continue;
				} elseif ( ! isset( $field_data['default'] ) || strlen( $field_data['default'] ) < 1 ) {
					continue;
				}

				$value = PodsForm::default_value( pods_var_raw( $field, 'post' ), $field_data['type'], $field, pods_var_raw( 'options', $field_data, $field_data, null, true ), $pod, $params->id );

				if ( null !== $value && '' !== $value && false !== $value ) {
					$fields[ $field ]['value'] = $value;
					$fields_active[]           = $field;
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
				if ( ! empty( $pod['options'] ) && is_array( $pod['options'] ) ) {
					$helpers = array( 'pre_save_helpers', 'post_save_helpers' );

					foreach ( $helpers as $helper ) {
						if ( isset( $pod['options'][ $helper ] ) && ! empty( $pod['options'][ $helper ] ) ) {
							${$helper} = explode( ',', $pod['options'][ $helper ] );
						}
					}
				}

				if ( ! empty( $pre_save_helpers ) ) {
					pods_deprecated( sprintf( __( 'Pre-save helpers are deprecated, use the action pods_pre_save_pod_item_%s instead', 'pods' ), $params->pod ), '2.0' );

					foreach ( $pre_save_helpers as $helper ) {
						$helper = $this->load_helper( array( 'name' => $helper ) );

						if ( false !== $helper ) {
							eval( '?>' . $helper['code'] );
						}
					}
				}
			}
		}

		$table_data    = array();
		$table_formats = array();
		$update_values = array();
		$rel_fields    = array();
		$rel_field_ids = array();

		$object_type = $pod['type'];

		$object_ID = 'ID';

		if ( 'comment' === $object_type ) {
			$object_ID = 'comment_ID';
		} elseif ( 'taxonomy' === $object_type ) {
			$object_ID = 'term_id';
		}

		$object_data    = array();
		$object_meta    = array();
		$post_term_data = array();

		if ( 'settings' === $object_type ) {
			$object_data['option_id'] = $pod['name'];
		} elseif ( ! empty( $params->id ) ) {
			$object_data[ $object_ID ] = $params->id;
		}

		$fields_active = array_unique( $fields_active );

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
			$options = pods_var( 'options', $field_data, array() );

			// WPML AJAX compatibility
			if ( is_admin()
				&& ( isset( $_POST['action'] ) && 'wpml_save_job_ajax' === $_POST['action'] )
				|| ( isset( $_GET['page'] ) && false !== strpos( $_GET['page'], '/menu/languages.php' )
					&& isset( $_POST['icl_ajx_action'] ) && isset( $_POST['_icl_nonce'] )
					&& wp_verify_nonce( $_POST['_icl_nonce'], $_POST['icl_ajx_action'] . '_nonce' ) )
			) {
				$options['unique']                       = 0;
				$fields[ $field ]['options']['unique']   = 0;
				$options['required']                     = 0;
				$fields[ $field ]['options']['required'] = 0;
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

			$value = PodsForm::pre_save( $field_data['type'], $value, $params->id, $field, array_merge( $field_data, $options ), array_merge( $fields, $object_fields ), $pod, $params );

			$field_data['value'] = $value;

			if ( isset( $object_fields[ $field ] ) ) {
				// @todo Eventually support 'comment' field type saving here too
				if ( 'taxonomy' === $object_fields[ $field ]['type'] ) {
					$post_term_data[ $field ] = $value;
				} else {
					$object_data[ $field ] = $value;
				}
			} else {
				$simple = ( 'pick' === $type && in_array( pods_var( 'pick_object', $field_data ), $simple_tableless_objects ) );
				$simple = (boolean) $this->do_hook( 'tableless_custom', $simple, $field_data, $field, $fields, $pod, $params );

				// Handle Simple Relationships
				if ( $simple ) {
					if ( ! is_array( $value ) ) {
						if ( 0 < strlen( $value ) ) {
							$value = array( $value );
						} else {
							$value = array();
						}
					}

					$pick_limit = (int) pods_var_raw( 'pick_limit', $options, 0 );

					if ( 'single' === pods_var_raw( 'pick_format_type', $options ) ) {
						$pick_limit = 1;
					}

					if ( 'custom-simple' === pods_var( 'pick_object', $field_data ) ) {
						$custom = pods_var_raw( 'pick_custom', $options, '' );

						$custom = apply_filters( 'pods_form_ui_field_pick_custom_values', $custom, $field_data['name'], $value, array_merge( $field_data, $options ), $pod, $params->id );

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

					// Don't save an empty array, just make it an empty string
					if ( empty( $value ) ) {
						$value = '';
					} elseif ( is_array( $value ) ) {
						if ( 1 == $pick_limit || 1 == count( $value ) ) {
							// If there's just one item, don't save as an array, save the string
							$value = implode( '', $value );
						} elseif ( 'table' === pods_var( 'storage', $pod ) ) {
							// If storage is set to table, json encode, otherwise WP will serialize automatically
							$value = version_compare( PHP_VERSION, '5.4.0', '>=' ) ? json_encode( $value, JSON_UNESCAPED_UNICODE ) : json_encode( $value );
						}
					}
				}

				// Prepare all table / meta data
				if ( ! in_array( $type, $tableless_field_types ) || $simple ) {
					if ( in_array( $type, $repeatable_field_types ) && 1 == pods_var( $type . '_repeatable', $field_data, 0 ) ) {
						// Don't save an empty array, just make it an empty string
						if ( empty( $value ) ) {
							$value = '';
						} elseif ( is_array( $value ) ) {
							if ( 1 == count( $value ) ) {
								// If there's just one item, don't save as an array, save the string
								$value = implode( '', $value );
							} elseif ( 'table' === pods_var( 'storage', $pod ) ) {
								// If storage is set to table, json encode, otherwise WP will serialize automatically
								$value = version_compare( PHP_VERSION, '5.4.0', '>=' ) ? json_encode( $value, JSON_UNESCAPED_UNICODE ) : json_encode( $value );
							}
						}
					}

					$table_data[ $field ] = str_replace( array( '{prefix}', '@wp_' ), array(
						'{/prefix/}',
						'{prefix}'
					), $value ); // Fix for pods_query
					$table_formats[]      = PodsForm::prepare( $type, $options );

					$object_meta[ $field ] = $value;
				} else {
					// Store relational field data to be looped through later
					// Convert values from a comma-separated string into an array
					if ( ! is_array( $value ) ) {
						$value = explode( ',', $value );
					}

					$rel_fields[ $type ][ $field ] = $value;
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
		}

		if ( ! in_array( $pod['type'], array( 'pod', 'table', '' ) ) ) {
			$meta_fields = array();

			if ( 'meta' === $pod['storage'] || 'settings' === $pod['type'] || ( 'taxonomy' === $pod['type'] && 'none' === $pod['storage'] ) ) {
				$meta_fields = $object_meta;
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

			$params->id = $this->save_wp_object( $object_type, $object_data, $meta_fields, false, true, $fields_to_send );

			if ( ! empty( $params->id ) && 'settings' === $pod['type'] ) {
				$params->id = $pod['id'];
			}
		}

		if ( 'table' === $pod['storage'] ) {
			// Every row should have an id set here, otherwise Pods with nothing
			// but relationship fields won't get properly ID'd
			if ( empty( $params->id ) ) {
				$params->id = 0;
			}

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

				wp_set_object_terms( $params->id, $post_terms, $post_taxonomy );
			}
		}

		$no_conflict = pods_no_conflict_check( $pod['type'] );

		if ( ! $no_conflict ) {
			pods_no_conflict_on( $pod['type'] );
		}

		// Save relationship / file data
		if ( ! empty( $rel_fields ) ) {
			foreach ( $rel_fields as $type => $data ) {
				// Only handle tableless fields
				if ( ! in_array( $type, $tableless_field_types ) ) {
					continue;
				}

				foreach ( $data as $field => $values ) {
					$pick_val = pods_var( 'pick_val', $fields[ $field ] );

					if ( 'table' === pods_var( 'pick_object', $fields[ $field ] ) ) {
						$pick_val = pods_var( 'pick_table', $fields[ $field ]['options'], $pick_val, null, true );
					}

					if ( '__current__' === $pick_val ) {
						if ( is_object( $pod ) ) {
							$pick_val = $pod->pod;
						} elseif ( is_array( $pod ) ) {
							$pick_val = $pod['name'];
						} elseif ( 0 < strlen( $pod ) ) {
							$pick_val = $pod;
						}
					}

					$fields[ $field ]['options']['table_info'] = pods_api()->get_table_info( pods_var( 'pick_object', $fields[ $field ] ), $pick_val, null, null, $fields[ $field ]['options'] );

					if ( isset( $fields[ $field ]['options']['table_info']['pod'] ) && ! empty( $fields[ $field ]['options']['table_info']['pod'] ) && isset( $fields[ $field ]['options']['table_info']['pod']['name'] ) ) {
						$search_data = pods( $fields[ $field ]['options']['table_info']['pod']['name'] );

						$data_mode = 'pods';
					} else {
						$search_data = pods_data();
						$search_data->table( $fields[ $field ]['options']['table_info'] );

						$data_mode = 'data';
					}

					$find_rel_params = array(
						'select'     => "`t`.`{$search_data->field_id}`",
						'where'      => "`t`.`{$search_data->field_slug}` = %s OR `t`.`{$search_data->field_index}` = %s",
						'limit'      => 1,
						'pagination' => false,
						'search'     => false
					);

					if ( empty( $search_data->field_slug ) && ! empty( $search_data->field_index ) ) {
						$find_rel_params['where'] = "`t`.`{$search_data->field_index}` = %s";
					} elseif ( empty( $search_data->field_slug ) && empty( $search_data->field_index ) ) {
						$find_rel_params = false;
					}

					$related_limit = (int) pods_var_raw( $type . '_limit', $fields[ $field ]['options'], 0 );

					if ( 'single' === pods_var_raw( $type . '_format_type', $fields[ $field ]['options'] ) ) {
						$related_limit = 1;
					}

					// Enforce integers / unique values for IDs
					$value_ids = array();

					$is_file_field = in_array( $type, PodsForm::file_field_types() );
					$is_taggable   = ( in_array( $type, PodsForm::tableless_field_types() ) && 1 == pods_v( $type . '_taggable', $fields[ $field ]['options'] ) );

					// @todo Handle simple relationships eventually
					foreach ( $values as $v ) {
						if ( ! empty( $v ) ) {
							if ( ! is_array( $v ) ) {
								if ( ! preg_match( '/[^0-9]/', $v ) ) {
									$v = (int) $v;
								} elseif ( $is_file_field ) {
									// File handling
									// Get ID from GUID
									$v = pods_image_id_from_field( $v );

									// If file not found, add it
									if ( empty( $v ) ) {
										$v = pods_attachment_import( $v );
									}
								} else {
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
										 * @param string $field       Table info for field.
										 * @param array  $pieces      Field array.
										 *
										 * @since 2.3.19
										 */
										$tag_data = apply_filters( 'pods_api_save_pod_item_taggable_data', $tag_data, $v, $search_data, $field, compact( $pieces ) );

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

							if ( ! empty( $v ) && ! in_array( $v, $value_ids ) ) {
								$value_ids[] = $v;
							}
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

					// Get current values
					if ( 'pick' === $type && isset( PodsField_Pick::$related_data[ $fields[ $field ]['id'] ] ) && isset( PodsField_Pick::$related_data[ $fields[ $field ]['id'] ]['current_ids'] ) ) {
						$related_ids = PodsField_Pick::$related_data[ $fields[ $field ]['id'] ]['current_ids'];
					} else {
						$related_ids = $this->lookup_related_items( $fields[ $field ]['id'], $pod['id'], $params->id, $fields[ $field ], $pod );
					}

					// Get ids to remove
					$remove_ids = array_diff( $related_ids, $value_ids );

					// Delete relationships
					if ( ! empty( $remove_ids ) ) {
						$this->delete_relationships( $params->id, $remove_ids, $pod, $fields[ $field ] );
					}

					// Save relationships
					if ( ! empty( $value_ids ) ) {
						$this->save_relationships( $params->id, $value_ids, $pod, $fields[ $field ] );
					}

					$field_save_values = $value_ids;

					if ( 'file' === $type ) {
						$field_save_values = $values;
					}

					// Run save function for field type (where needed)
					PodsForm::save( $type, $field_save_values, $params->id, $field, array_merge( $fields[ $field ], $fields[ $field ]['options'] ), array_merge( $fields, $object_fields ), $pod, $params );
				}

				// Unset data no longer needed
				if ( 'pick' === $type ) {
					foreach ( $data as $field => $values ) {
						if ( isset( PodsField_Pick::$related_data[ $fields[ $field ]['id'] ] ) ) {
							unset( PodsField_Pick::$related_data[ PodsField_Pick::$related_data[ $fields[ $field ]['id'] ]['related_field']['id'] ] );
							unset( PodsField_Pick::$related_data[ $fields[ $field ]['id'] ] );
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
		if ( in_array( $pod['type'], array( 'post_type', 'media', 'taxonomy', 'user', 'comment' ) ) ) {
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

			// Call any post-save helpers (if not bypassed)
			if ( ! defined( 'PODS_DISABLE_EVAL' ) || ! PODS_DISABLE_EVAL ) {
				if ( ! empty( $post_save_helpers ) ) {
					pods_deprecated( sprintf( __( 'Post-save helpers are deprecated, use the action pods_post_save_pod_item_%s instead', 'pods' ), $params->pod ), '2.0' );

					foreach ( $post_save_helpers as $helper ) {
						$helper = $this->load_helper( array( 'name' => $helper ) );

						if ( false !== $helper && ( ! defined( 'PODS_DISABLE_EVAL' ) || ! PODS_DISABLE_EVAL ) ) {
							eval( '?>' . $helper['code'] );
						}
					}
				}
			}
		}

		// Success! Return the id
		return $params->id;

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

		static $changed_pods_cache = array();
		static $old_fields_cache = array();
		static $changed_fields_cache = array();

		$cache_key = $pod . '|' . $id;

		$export_params = array(
			'depth' => 1,
		);

		if ( in_array( $mode, array( 'set', 'reset' ), true ) ) {
			if ( isset( $changed_fields_cache[ $cache_key ] ) ) {
				unset( $changed_fields_cache[ $cache_key ] );
			}

			if ( empty( $old_fields_cache[ $cache_key ] ) || 'reset' === $mode ) {
				$old_fields_cache[ $cache_key ] = array();

				if ( ! empty( $id ) ) {
					if ( ! isset( $changed_pods_cache[ $pod ] ) ) {
						$changed_pods_cache[ $pod ] = pods( $pod );
					}

					if ( $changed_pods_cache[ $pod ] && $changed_pods_cache[ $pod ]->valid() ) {
						$changed_pods_cache[ $pod ]->fetch( $id );

						$old_fields_cache[ $cache_key ] = $changed_pods_cache[ $pod ]->export( $export_params );
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
						$changed_pods_cache[ $pod ]->fetch( $id );
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

		return self::handle_changed_fields( $pieces['params']->pod, $pieces['params']->id, 'get' );

	}

	/**
	 * Save relationships
	 *
	 * @param int       $id         ID of item
	 * @param int|array $related_id ID or IDs to save
	 * @param array     $pod        Pod data
	 * @param array     $field      Field data
	 */
	public function save_relationships( $id, $related_ids, $pod, $field ) {

		// Get current values
		if ( 'pick' === $field['type'] && isset( PodsField_Pick::$related_data[ $field['id'] ] ) && isset( PodsField_Pick::$related_data[ $field['id'] ]['current_ids'] ) ) {
			$current_ids = PodsField_Pick::$related_data[ $field['id'] ]['current_ids'];
		} else {
			$current_ids = $this->lookup_related_items( $field['id'], $pod['id'], $id, $field, $pod );
		}

		if ( isset( self::$related_item_cache[ $pod['id'] ][ $field['id'] ] ) ) {
			// Delete relationship from cache
			unset( self::$related_item_cache[ $pod['id'] ][ $field['id'] ] );
		}

		if ( ! is_array( $related_ids ) ) {
			$related_ids = implode( ',', $related_ids );
		}

		foreach ( $related_ids as $k => $related_id ) {
			$related_ids[ $k ] = (int) $related_id;
		}

		$related_ids = array_unique( array_filter( $related_ids ) );

		$related_limit = (int) pods_var_raw( $field['type'] . '_limit', $field['options'], 0 );

		if ( 'single' === pods_var_raw( $field['type'] . '_format_type', $field['options'] ) ) {
			$related_limit = 1;
		}

		// Limit values
		if ( 0 < $related_limit && ! empty( $related_ids ) ) {
			$related_ids = array_slice( $related_ids, 0, $related_limit );
		}

		// Post Types, Media, Users, and Comments (meta-based)
		if ( in_array( $pod['type'], array( 'post_type', 'media', 'taxonomy', 'user', 'comment' ) ) ) {
			$object_type = $pod['type'];

			if ( in_array( $object_type, array( 'post_type', 'media' ) ) ) {
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

		if ( 'pick' === $field['type'] && isset( PodsField_Pick::$related_data[ $field['id'] ] ) && ! empty( PodsField_Pick::$related_data[ $field['id'] ]['related_field'] ) ) {
			$related_pod_id   = PodsField_Pick::$related_data[ $field['id'] ]['related_pod']['id'];
			$related_field_id = PodsField_Pick::$related_data[ $field['id'] ]['related_field']['id'];
		}

		// Relationships table
		if ( ! pods_tableless() ) {
			$related_weight = 0;

			foreach ( $related_ids as $related_id ) {
				if ( in_array( $related_id, $current_ids ) ) {
					pods_query( "
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
					", array(
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
					pods_query( "
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
					", array(
						$pod['id'],
						$field['id'],
						$id,
						$related_pod_id,
						$related_field_id,
						$related_id,
						$related_weight
					) );
				}

				$related_weight ++;
			}
		}
	}

	/**
	 * Duplicate a Pod
	 *
	 * $params['id'] int The Pod ID
	 * $params['name'] string The Pod name
	 * $params['new_name'] string The new Pod name
	 *
	 * @param array $params An associative array of parameters
	 * @param bool  $strict (optional) Makes sure a pod exists, if it doesn't throws an error
	 *
	 * @return int New Pod ID
	 * @since 2.3.0
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

		$params->table_info = false;

		$pod = $this->load_pod( $params, $strict );

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

		unset( $pod['id'] );

		if ( isset( $params->new_name ) ) {
			$pod['name'] = $params->new_name;
		}

		$try = 1;

		$check_name = $pod['name'];
		$new_label  = $pod['label'];

		while ( $this->load_pod( array( 'name' => $check_name, 'table_info' => false ), false ) ) {
			$try ++;

			$check_name = $pod['name'] . $try;
			$new_label  = $pod['label'] . $try;
		}

		$pod['name']  = $check_name;
		$pod['label'] = $new_label;

		foreach ( $pod['fields'] as $field => $field_data ) {
			unset( $pod['fields'][ $field ]['id'] );
		}

		return $this->save_pod( $pod );
	}

	/**
	 * Duplicate a Field
	 *
	 * $params['pod_id'] int The Pod ID
	 * $params['pod'] string The Pod name
	 * $params['id'] int The Field ID
	 * $params['name'] string The Field name
	 * $params['new_name'] string The new Field name
	 *
	 * @param array $params An associative array of parameters
	 * @param bool  $strict (optional) Makes sure a field exists, if it doesn't throws an error
	 *
	 * @return int New Field ID
	 * @since 2.3.10
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

		$params->table_info = false;

		$field = $this->load_field( $params, $strict );

		if ( empty( $field ) ) {
			if ( false !== $strict ) {
				return pods_error( __( 'Field not found', 'pods' ), $this );
			}

			return false;
		}

		unset( $field['id'] );

		if ( isset( $params->new_name ) ) {
			$field['name'] = $params->new_name;
		}

		$try = 1;

		$check_name = $field['name'];
		$new_label  = $field['label'];

		while ( $this->load_field( array(
			'pod_id'     => $field['pod_id'],
			'name'       => $check_name,
			'table_info' => false
		), false ) ) {
			$try ++;

			$check_name = $field['name'] . $try;
			$new_label  = $field['label'] . $try;
		}

		$field['name']  = $check_name;
		$field['label'] = $new_label;

		return $this->save_field( $field );

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
			'name'       => $params->pod,
			'table_info' => false,
		);

		$pod = $this->load_pod( $load_pod_params );

		if ( false === $pod ) {
			return pods_error( __( 'Pod not found', 'pods' ), $this );
		}

		$pod = pods( $params->pod, $params->id );

		$params->pod    = $pod->pod;
		$params->pod_id = $pod->pod_id;

		$fields        = (array) pods_var_raw( 'fields', $pod->pod_data, array(), null, true );
		$object_fields = (array) pods_var_raw( 'object_fields', $pod->pod_data, array(), null, true );

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

		if ( in_array( $pod->pod_data['type'], array( 'post_type', 'media' ) ) ) {
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

		$object_fields = (array) pods_v( 'object_fields', $pod->pod_data, array(), true );

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
			$read_all = (int) pods_v( 'read_all', $pod->pod_data['options'], 0 );

			if ( 1 === $read_all ) {
				$show_in_rest = true;
			}
		}

		foreach ( $fields as $k => $field ) {
			if ( ! is_array( $field ) ) {
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

				$field                = $pod->fields[ $field['name'] ];
				$field['lookup_name'] = $field['name'];

				if ( in_array( $field['type'], $tableless_field_types ) && ! in_array( pods_var( 'pick_object', $field ), $simple_tableless_objects ) ) {
					if ( 'pick' === $field['type'] ) {
						if ( empty( $field['table_info'] ) ) {
							$field['table_info'] = $this->get_table_info( pods_var_raw( 'pick_object', $field ), pods_var_raw( 'pick_val', $field ), null, null, $field );
						}

						if ( ! empty( $field['table_info'] ) ) {
							$field['lookup_name'] .= '.' . $field['table_info']['field_id'];
						}
					} elseif ( in_array( $field['type'], PodsForm::file_field_types() ) ) {
						$field['lookup_name'] .= '.guid';
					}
				}

				$export_fields[ $field['name'] ] = $field;
			} elseif ( isset( $object_fields[ $field['name'] ] ) ) {
				$field                = $object_fields[ $field['name'] ];
				$field['lookup_name'] = $field['name'];

				$export_fields[ $field['name'] ] = $field;
			} elseif ( $field['name'] == $pod->pod_data['field_id'] ) {
				$field['type']        = 'number';
				$field['lookup_name'] = $field['name'];

				$export_fields[ $field['name'] ] = $field;
			}
		}

		$data = array();

		foreach ( $export_fields as $field ) {
			// Return IDs (or guid for files) if only one level deep
			if ( 1 == $depth ) {
				$data[ $field['name'] ] = $pod->field( array( 'name' => $field['lookup_name'], 'output' => 'arrays' ) );
			} elseif ( ( - 1 == $depth || $current_depth < $depth ) && 'pick' === $field['type'] && ! in_array( pods_var( 'pick_object', $field ), $simple_tableless_objects ) ) {
				// Recurse depth levels for pick fields if $depth allows
				$related_data = array();

				$related_ids = $pod->field( array( 'name' => $field['name'], 'output' => 'ids' ) );

				if ( ! empty( $related_ids ) ) {
					$related_ids = (array) $related_ids;

					$pick_object = pods_var_raw( 'pick_object', $field );

					$related_pod = pods( pods_var_raw( 'pick_val', $field ), null, false );

					// If this isn't a Pod, return data exactly as Pods does normally
					if ( empty( $related_pod ) || ( 'pod' !== $pick_object && $pick_object !== $related_pod->pod_data['type'] ) || $related_pod->pod == $pod->pod ) {
						$related_data = $pod->field( array( 'name' => $field['name'], 'output' => 'arrays' ) );
					} else {
						$related_object_fields = (array) pods_var_raw( 'object_fields', $related_pod->pod_data, array(), null, true );

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

								$related_data[ $related_id ] = $this->do_hook( 'export_pod_item_level', $related_item, $related_pod->pod, $related_pod->id(), $related_pod, $related_fields, $depth, $flatten, ( $current_depth + 1 ), $params );
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

		// @deprecated 2.0.0
		if ( isset( $params->datatype ) ) {
			pods_deprecated( __( '$params->pod instead of $params->datatype', 'pods' ), '2.0' );

			$params->pod = $params->datatype;

			unset( $params->datatype );
		}

		if ( null === pods_var_raw( 'pod', $params, null, null, true ) ) {
			return pods_error( __( '$params->pod is required', 'pods' ), $this );
		}

		if ( ! is_array( $params->order ) ) {
			$params->order = explode( ',', $params->order );
		}

		$pod = $this->load_pod( array( 'name' => $params->pod, 'table_info' => true ) );

		$params->name = $pod['name'];

		if ( false === $pod ) {
			return pods_error( __( 'Pod is required', 'pods' ), $this );
		}

		foreach ( $params->order as $order => $id ) {
			if ( isset( $pod['fields'][ $params->field ] ) || isset( $pod['object_fields'][ $params->field ] ) ) {
				if ( 'table' === $pod['storage'] && ( ! pods_tableless() ) ) {
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

		$params = (object) pods_sanitize( $params );

		$params->table_info = true;

		if ( empty( $pod ) ) {
			$pod = $this->load_pod( $params );
		}

		if ( false === $pod ) {
			return pods_error( __( 'Pod not found', 'pods' ), $this );
		}

		$params->id   = $pod['id'];
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

			pods_query( "DELETE FROM `@wp_podsrel` WHERE `pod_id` = {$params->id} OR `related_pod_id` = {$params->id}", false );
		}

		// @todo Delete relationships from tableless relationships

		// Delete all posts/revisions from this post type
		if ( in_array( $pod['type'], array( 'post_type', 'media' ) ) ) {
			$type = pods_var( 'object', $pod, $pod['name'], null, true );

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
			// Delete all terms from this taxonomy
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
			$type = pods_var( 'object', $pod, $pod['name'], null, true );

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
	 * @param array $params     An associative array of parameters
	 * @param bool  $strict     (optional) Makes sure a pod exists, if it doesn't throws an error
	 * @param bool  $delete_all (optional) Whether to delete all content from a WP object
	 *
	 * @uses  PodsAPI::load_pod
	 * @uses  wp_delete_post
	 * @uses  pods_query
	 *
	 * @return bool
	 * @since 1.7.9
	 */
	public function delete_pod( $params, $strict = false, $delete_all = false ) {

		/**
		 * @var $wpdb wpdb
		 */
		global $wpdb;

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

		if ( ! isset( $params->delete_all ) ) {
			$params->delete_all = $delete_all;
		}

		$params->table_info = false;

		$pod = $this->load_pod( $params, $strict );

		if ( empty( $pod ) ) {
			if ( false !== $strict ) {
				return pods_error( __( 'Pod not found', 'pods' ), $this );
			}

			return false;
		}

		$params->id   = (int) $pod['id'];
		$params->name = $pod['name'];

		// Reset content
		if ( true === $params->delete_all ) {
			$this->reset_pod( $params, $pod );
		}

		foreach ( $pod['fields'] as $field ) {
			$field['pod'] = $pod;

			$this->delete_field( $field, false );
		}

		// Only delete the post once the fields are taken care of, it's not required anymore
		$success = wp_delete_post( $params->id );

		if ( ! $success ) {
			return pods_error( __( 'Pod unable to be deleted', 'pods' ), $this );
		}

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

			pods_query( "DELETE FROM `@wp_podsrel` WHERE `pod_id` = {$params->id} OR `related_pod_id` = {$params->id}", false );
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
				AND `pm`.`meta_value` = '{$params->name}'
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
	 * @param array $params          An associative array of parameters
	 * @param bool  $table_operation Whether or not to handle table operations
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

		$params = (object) pods_sanitize( $params );

		if ( ! isset( $params->pod ) ) {
			$params->pod = '';
		}

		if ( ! isset( $params->pod_id ) ) {
			$params->pod_id = 0;
		}

		$pod = $params->pod;

		$save_pod = false;

		if ( ! is_array( $pod ) ) {
			$pod = $this->load_pod( array( 'name' => $pod, 'id' => $params->pod_id, 'table_info' => false ) );
		} else {
			$save_pod = true;
		}

		if ( empty( $pod ) ) {
			return pods_error( __( 'Pod not found', 'pods' ), $this );
		}

		$params->pod_id = $pod['id'];
		$params->pod    = $pod['name'];

		if ( ! isset( $params->name ) ) {
			$params->name = '';
		}

		if ( ! isset( $params->id ) ) {
			$params->id = 0;
		}

		$field = $this->load_field( array(
			'name'   => $params->name,
			'id'     => $params->id,
			'pod'    => $params->pod,
			'pod_id' => $params->pod_id
		) );

		if ( false === $field ) {
			return pods_error( __( 'Field not found', 'pods' ), $this );
		}

		$params->id   = $field['id'];
		$params->name = $field['name'];

		$simple = ( 'pick' === $field['type'] && in_array( pods_var( 'pick_object', $field ), $simple_tableless_objects ) );
		$simple = (boolean) $this->do_hook( 'tableless_custom', $simple, $field, $pod, $params );

		if ( $table_operation && 'table' === $pod['storage'] && ( ! in_array( $field['type'], $tableless_field_types ) || $simple ) ) {
			pods_query( "ALTER TABLE `@wp_pods_{$params->pod}` DROP COLUMN `{$params->name}`", false );
		}

		$success = wp_delete_post( $params->id );

		if ( ! $success ) {
			return pods_error( __( 'Field unable to be deleted', 'pods' ), $this );
		}

		$wpdb->query( $wpdb->prepare( "DELETE pm FROM {$wpdb->postmeta} AS pm
			LEFT JOIN {$wpdb->posts} AS p
				ON p.post_type = '_pods_field' AND p.ID = pm.post_id
			WHERE p.ID IS NOT NULL AND pm.meta_key = 'sister_id' AND pm.meta_value = %d", $params->id ) );

		if ( ( ! pods_tableless() ) && $table_operation ) {
			pods_query( "DELETE FROM `@wp_podsrel` WHERE (`pod_id` = {$params->pod_id} AND `field_id` = {$params->id}) OR (`related_pod_id` = {$params->pod_id} AND `related_field_id` = {$params->id})", false );
		}

		// @todo Delete tableless relationship meta

		if ( true === $save_pod ) {
			$this->cache_flush_pods( $pod );
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

		$params = (object) $params;
		$object = $this->load_object( $params );

		if ( empty( $object ) ) {
			return pods_error( sprintf( __( "%s Object not found", 'pods' ), ucwords( $params->type ) ), $this );
		}

		$success = wp_delete_post( $params->id );

		if ( ! $success ) {
			return pods_error( sprintf( __( "%s Object not deleted", 'pods' ), ucwords( $params->type ) ), $this );
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
	 */
	public function delete_helper( $params ) {

		$params       = (object) $params;
		$params->type = 'helper';

		return $this->delete_object( $params );
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

		$params = (object) pods_sanitize( $params );

		// @deprecated 2.0.0
		if ( isset( $params->datatype_id ) || isset( $params->datatype ) || isset( $params->tbl_row_id ) ) {
			if ( isset( $params->tbl_row_id ) ) {
				pods_deprecated( __( '$params->id instead of $params->tbl_row_id', 'pods' ), '2.0' );
				$params->id = $params->tbl_row_id;
				unset( $params->tbl_row_id );
			}

			if ( isset( $params->pod_id ) ) {
				pods_deprecated( __( '$params->id instead of $params->pod_id', 'pods' ), '2.0' );
				$params->id = $params->pod_id;
				unset( $params->pod_id );
			}

			if ( isset( $params->dataype_id ) ) {
				pods_deprecated( __( '$params->pod_id instead of $params->datatype_id', 'pods' ), '2.0' );
				$params->pod_id = $params->dataype_id;
				unset( $params->dataype_id );
			}

			if ( isset( $params->datatype ) ) {
				pods_deprecated( __( '$params->pod instead of $params->datatype', 'pods' ), '2.0' );
				$params->pod = $params->datatype;
				unset( $params->datatype );
			}
		}

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

		$pod = $this->load_pod( array( 'name' => $params->pod, 'id' => $params->pod_id, 'table_info' => false ) );

		if ( false === $pod ) {
			return pods_error( __( 'Pod not found', 'pods' ), $this );
		}

		$params->pod_id = $pod['id'];
		$params->pod    = $pod['name'];

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
				if ( ! empty( $pod['options'] ) && is_array( $pod['options'] ) ) {
					$helpers = array( 'pre_delete_helpers', 'post_delete_helpers' );

					foreach ( $helpers as $helper ) {
						if ( isset( $pod['options'][ $helper ] ) && ! empty( $pod['options'][ $helper ] ) ) {
							${$helper} = explode( ',', $pod['options'][ $helper ] );
						}
					}
				}

				if ( ! empty( $pre_delete_helpers ) ) {
					pods_deprecated( sprintf( __( 'Pre-delete helpers are deprecated, use the action pods_pre_delete_pod_item_%s instead', 'pods' ), $params->pod ), '2.0' );

					foreach ( $pre_delete_helpers as $helper ) {
						$helper = $this->load_helper( array( 'name' => $helper ) );

						if ( false !== $helper ) {
							eval( '?>' . $helper['code'] );
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

			// Call any post-save helpers (if not bypassed)
			if ( ! defined( 'PODS_DISABLE_EVAL' ) || ! PODS_DISABLE_EVAL ) {
				if ( ! empty( $post_delete_helpers ) ) {
					pods_deprecated( sprintf( __( 'Post-delete helpers are deprecated, use the action pods_post_delete_pod_item_%s instead', 'pods' ), $params->pod ), '2.0' );

					foreach ( $post_delete_helpers as $helper ) {
						$helper = $this->load_helper( array( 'name' => $helper ) );

						if ( false !== $helper ) {
							eval( '?>' . $helper['code'] );
						}
					}
				}
			}
		}

		pods_cache_clear( $params->id, 'pods_items_' . $params->pod );

		return true;
	}

	/**
	 * Delete an object from tableless fields
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
		 */
		global $pods_init;

		$pod = false;

		// Run any bidirectional delete operations
		if ( is_array( $object ) ) {
			$pod = $object;
		} elseif ( is_object( $pods_init ) ) {
			$pod = PodsInit::$meta->get_object( $object, $name );
		}

		if ( ! empty( $pod ) ) {
			$object = $pod['type'];
			$name   = $pod['name'];

			foreach ( $pod['fields'] as $field ) {
				PodsForm::delete( $field['type'], $id, $field['name'], array_merge( $field, $field['options'] ), $pod );
			}
		}

		// Lookup related fields (non-bidirectional)
		$params = array(
			'where' => array(
				array(
					'key'   => 'type',
					'value' => 'pick'
				),
				array(
					'key'   => 'pick_object',
					'value' => $object
				)
			)
		);

		if ( ! empty( $name ) && $name !== $object ) {
			$params['where'][] = array(
				'key'   => 'pick_val',
				'value' => $name
			);
		}

		$fields = $this->load_fields( $params, false );

		if ( ! empty( $pod ) && 'media' === $pod['type'] ) {
			$params['where'] = array(
				array(
					'key'   => 'type',
					'value' => 'file'
				)
			);

			$fields = array_merge( $fields, $this->load_fields( $params, false ) );
		}

		if ( is_array( $fields ) && ! empty( $fields ) ) {
			foreach ( $fields as $related_field ) {
				$related_pod = $this->load_pod( array( 'id' => $related_field['pod_id'], 'fields' => false ), false );

				if ( empty( $related_pod ) ) {
					continue;
				}

				$related_from = $this->lookup_related_items_from( $related_field['id'], $related_pod['id'], $id, $related_field, $related_pod );

				$this->delete_relationships( $related_from, $id, $related_pod, $related_field );
			}
		}

		if ( ! empty( $pod ) && ! pods_tableless() ) {
			pods_query( "
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
			", array(
				$pod['id'],
				$id,

				$pod['id'],
				$id
			) );
		}

		return true;
	}

	/**
	 * Delete relationships
	 *
	 * @param int|array $related_id    IDs for items to save
	 * @param int|array $id            ID or IDs to remove
	 * @param array     $related_pod   Pod data
	 * @param array     $related_field Field data
	 *
	 * @return void
	 *
	 * @since 2.3.0
	 */
	public function delete_relationships( $related_id, $id, $related_pod, $related_field ) {

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

		if ( empty( $related_ids ) ) {
			return;
		} elseif ( ! in_array( $id, $related_ids ) ) {
			return;
		}

		if ( isset( self::$related_item_cache[ $related_pod['id'] ][ $related_field['id'] ] ) ) {
			// Delete relationship from cache
			unset( self::$related_item_cache[ $related_pod['id'] ][ $related_field['id'] ] );
		}

		// @codingStandardsIgnoreLine
		unset( $related_ids[ array_search( $id, $related_ids ) ] );

		$no_conflict = pods_no_conflict_check( $related_pod['type'] );

		if ( ! $no_conflict ) {
			pods_no_conflict_on( $related_pod['type'] );
		}

		// Post Types, Media, Users, and Comments (meta-based)
		if ( in_array( $related_pod['type'], array( 'post_type', 'media', 'taxonomy', 'user', 'comment' ) ) ) {
			$object_type = $related_pod['type'];

			if ( in_array( $object_type, array( 'post_type', 'media' ) ) ) {
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
		if ( ! pods_tableless() ) {
			pods_query( "
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
			", array(
				$related_pod['id'],
				$related_field['id'],
				$related_id,
				$id,

				$related_pod['id'],
				$related_field['id'],
				$related_id,
				$id
			) );
		}

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

		$total = count( $posts );

		return $total;

	}

	/**
	 * Load a Pod and all of its fields
	 *
	 * $params['id'] int The Pod ID
	 * $params['name'] string The Pod name
	 * $params['fields'] bool Whether to load fields (default is true)
	 * $params['bypass_cache'] boolean Bypass the cache when getting data
	 *
	 * @param array|object $params An associative array of parameters or pod name as a string
	 * @param bool         $strict Makes sure the pod exists, throws an error if it doesn't work
	 *
	 * @return array|bool|mixed|void
	 * @since 1.7.9
	 */
	public function load_pod( $params, $strict = true ) {

		/**
		 * @var $sitepress SitePress
		 * @var $wpdb      wpdb
		 */
		global $wpdb;

		$current_language = false;
		$load_fields      = true;
		$bypass_cache     = false;

		// Get current language data
		$lang_data = PodsInit::$i18n->get_current_language_data();

		if ( $lang_data ) {
			if ( ! empty( $lang_data['language'] ) ) {
				$current_language = $lang_data['language'];
			}
		}

		if ( ! is_array( $params ) && ! is_object( $params ) ) {
			$params = array( 'name' => $params, 'table_info' => false, 'fields' => true );
		}

		if ( is_object( $params ) && ! is_a( $params, 'WP_Post' ) && isset( $params->fields ) && ! $params->fields ) {
			$load_fields = false;
		} elseif ( is_array( $params ) && isset( $params['fields'] ) && ! $params['fields'] ) {
			$load_fields = false;
		}

		$table_info = false;

		if ( is_object( $params ) && ! is_a( $params, 'WP_Post' ) && ! empty( $params->table_info ) ) {
			$table_info = true;
		} elseif ( is_array( $params ) && ! empty( $params['table_info'] ) ) {
			$table_info = true;
		}

		$transient = 'pods_' . $wpdb->prefix . '_pod';

		if ( ! empty( $current_language ) ) {
			$transient .= '_' . $current_language;
		}

		if ( ! $load_fields ) {
			$transient .= '_nofields';
		}

		if ( $table_info ) {
			$transient .= '_tableinfo';
		}

		$check_pod = $params;

		if ( is_object( $params ) && ! is_a( $params, 'WP_Post' ) && ! empty( $params->pod ) ) {
			$check_pod = $params->pod;
		} elseif ( is_array( $params ) && ! empty( $params['pod'] ) ) {
			$check_pod = $params['pod'];
		}

		if ( is_object( $check_pod ) && ( is_a( $check_pod, 'WP_Post' ) || isset( $check_pod->post_name ) ) ) {
			$pod = false;

			if ( pods_api_cache() ) {
				$pod = pods_transient_get( $transient . '_' . $check_pod->post_name );
			}

			if ( false !== $pod && ( ! $table_info || isset( $pod['table'] ) ) ) {
				// @todo Is this needed anymore for WPML?
				if ( in_array( $pod['type'], array(
						'post_type',
						'taxonomy'
					) ) && did_action( 'wpml_loaded' ) && apply_filters( 'wpml_setting', true, 'auto_adjust_ids' ) ) {
					$pod = array_merge( $pod, $this->get_table_info( $pod['type'], $pod['object'], $pod['name'], $pod ) );
				}

				return $pod;
			}

			$_pod = get_object_vars( $check_pod );
		} else {
			$params = (object) pods_sanitize( $params );

			if ( ( ! isset( $params->id ) || empty( $params->id ) ) && ( ! isset( $params->name ) || empty( $params->name ) ) ) {
				if ( $strict ) {
					return pods_error( 'Either Pod ID or Name are required', $this );
				}

				return false;
			}

			if ( ! empty( $params->bypass_cache ) ) {
				$bypass_cache = true;
			}

			if ( isset( $params->name ) ) {
				$pod = false;

				if ( '_pods_pod' === $params->name ) {
					$pod = array(
						'id'      => 0,
						'name'    => $params->name,
						'label'   => __( 'Pods', 'pods' ),
						'type'    => 'post_type',
						'storage' => 'meta',
						'options' => array(
							'label_singular' => __( 'Pod', 'pods' )
						),
						'fields'  => array()
					);
				} elseif ( '_pods_field' === $params->name ) {
					$pod = array(
						'id'      => 0,
						'name'    => $params->name,
						'label'   => __( 'Pod Fields', 'pods' ),
						'type'    => 'post_type',
						'storage' => 'meta',
						'options' => array(
							'label_singular' => __( 'Pod Field', 'pods' )
						),
						'fields'  => array()
					);
				} elseif ( ! $bypass_cache & pods_api_cache() ) {
					$pod = pods_transient_get( $transient . '_' . $params->name );
				}

				if ( false !== $pod && ( ! $table_info || isset( $pod['table'] ) ) ) {
					if ( in_array( $pod['type'], array(
							'post_type',
							'taxonomy'
						) ) && did_action( 'wpml_loaded' ) && apply_filters( 'wpml_setting', true, 'auto_adjust_ids' ) ) {
						$pod = array_merge( $pod, $this->get_table_info( $pod['type'], $pod['object'], $pod['name'], $pod ) );
					}

					return $pod;
				}
			}

			if ( ! isset( $params->name ) ) {
				$dummy = (int) $params->id;
				$pod   = get_post( $dummy );
			} else {
				$pod = get_posts( array(
					'name'           => $params->name,
					'post_type'      => '_pods_pod',
					'posts_per_page' => 1
				) );
			}

			if ( empty( $pod ) ) {
				if ( $strict ) {
					return pods_error( __( 'Pod not found', 'pods' ), $this );
				}

				return false;
			}

			if ( is_array( $pod ) && ! empty( $pod[0] ) ) {
				$pod = $pod[0];
			}

			$_pod = get_object_vars( $pod );
		}

		$pod = false;

		if ( ! $bypass_cache || pods_api_cache() ) {
			$pod = pods_transient_get( $transient . '_' . $_pod['post_name'] );
		}

		if ( false !== $pod && ( ! $table_info || isset( $pod['table'] ) ) ) {
			if ( in_array( $pod['type'], array(
					'post_type',
					'taxonomy'
				) ) && did_action( 'wpml_loaded' ) && apply_filters( 'wpml_setting', true, 'auto_adjust_ids' ) ) {
				$pod = array_merge( $pod, $this->get_table_info( $pod['type'], $pod['object'], $pod['name'], $pod ) );
			}

			return $pod;
		}

		$pod = array(
			'id'          => $_pod['ID'],
			'name'        => $_pod['post_name'],
			'label'       => $_pod['post_title'],
			'description' => $_pod['post_content']
		);

		if ( strlen( $pod['label'] ) < 1 ) {
			$pod['label'] = $pod['name'];
		}

		// @todo update with a method to put all options in
		$defaults = array(
			'show_in_menu' => 1,
			'type'         => 'post_type',
			'storage'      => 'meta',
			'object'       => '',
			'alias'        => ''
		);

		if ( $bypass_cache ) {
			wp_cache_delete( $pod['id'], 'post_meta' );

			update_postmeta_cache( array( $pod['id'] ) );
		}

		$pod['options'] = get_post_meta( $pod['id'] );

		foreach ( $pod['options'] as $option => $value ) {
			if ( is_array( $value ) ) {
				foreach ( $value as $k => $v ) {
					if ( ! is_array( $v ) ) {
						$value[ $k ] = maybe_unserialize( $v );
					}
				}

				if ( 1 == count( $value ) ) {
					$value = current( $value );
				}
			} else {
				$value = maybe_unserialize( $value );
			}

			$pod['options'][ $option ] = $value;
		}

		$pod['options'] = array_merge( $defaults, $pod['options'] );

		$pod['type']    = $pod['options']['type'];
		$pod['storage'] = $pod['options']['storage'];
		$pod['object']  = $pod['options']['object'];
		$pod['alias']   = $pod['options']['alias'];

		unset( $pod['options']['type'] );
		unset( $pod['options']['storage'] );
		unset( $pod['options']['object'] );
		unset( $pod['options']['alias'] );

		if ( $table_info ) {
			$pod = array_merge( $this->get_table_info( $pod['type'], $pod['object'], $pod['name'], $pod ), $pod );
		}

		// Override old 'none' storage type
		if ( 'taxonomy' === $pod['type'] && 'none' === $pod['storage'] && function_exists( 'get_term_meta' ) ) {
			$pod['storage'] = 'meta';
		}

		if ( isset( $pod['pod'] ) ) {
			unset( $pod['pod'] );
		}

		$pod['fields'] = array();

		$pod['object_fields'] = array();

		if ( 'pod' !== $pod['type'] ) {
			$pod['object_fields'] = $this->get_wp_object_fields( $pod['type'], $pod );
		}

		$fields = get_posts( array(
			'post_type'      => '_pods_field',
			'posts_per_page' => - 1,
			'nopaging'       => true,
			'post_parent'    => $pod['id'],
			'orderby'        => 'menu_order',
			'order'          => 'ASC'
		) );

		if ( ! empty( $fields ) ) {
			foreach ( $fields as $field ) {
				$field->pod          = $pod['name'];
				$field->table_info   = $table_info;
				$field->bypass_cache = $bypass_cache;

				if ( $load_fields ) {
					$field = $this->load_field( $field );

					$field = PodsForm::field_setup( $field, null, $field['type'] );
				} else {
					if ( $bypass_cache ) {
						wp_cache_delete( $field->ID, 'post_meta' );

						update_postmeta_cache( array( $field->ID ) );
					}

					$field = array(
						'id'    => $field->ID,
						'name'  => $field->post_name,
						'label' => $field->post_title,
						'type'  => get_post_meta( $field->ID, 'type', true )
					);
				}

				$pod['fields'][ $field['name'] ] = $field;
			}
		}

		if ( did_action( 'init' ) && pods_api_cache() ) {
			pods_transient_set( $transient . '_' . $pod['name'], $pod );
		}

		return $pod;
	}

	/**
	 * Load a list of Pods based on filters specified.
	 *
	 * $params['type'] string/array Pod Type(s) to filter by
	 * $params['object'] string/array Pod Object(s) to filter by
	 * $params['options'] array Pod Option(s) key=>value array to filter by
	 * $params['orderby'] string ORDER BY clause of query
	 * $params['limit'] string Number of Pods to return
	 * $params['where'] string WHERE clause of query
	 * $params['ids'] string|array IDs of Objects
	 * $params['count'] boolean Return only a count of Pods
	 * $params['names'] boolean Return only an array of name => label
	 * $params['ids'] boolean Return only an array of ID => label
	 * $params['fields'] boolean Return pod fields with Pods (default is true)
	 * $params['key_names'] boolean Return pods keyed by name
	 * $params['bypass_cache'] boolean Bypass the cache when getting data
	 *
	 * @param array $params An associative array of parameters
	 *
	 * @return array|mixed
	 *
	 * @uses  PodsAPI::load_pod
	 *
	 * @since 2.0.0
	 */
	public function load_pods( $params = null ) {

		$current_language = false;

		// Get current language data
		$lang_data = PodsInit::$i18n->get_current_language_data();

		if ( $lang_data ) {
			if ( ! empty( $lang_data['language'] ) ) {
				$current_language = $lang_data['language'];
			}
		}

		$params = (object) pods_sanitize( $params );

		$order   = 'ASC';
		$orderby = 'menu_order title';
		$limit   = - 1;
		$ids     = false;

		$meta_query = array();
		$cache_key  = '';

		$bypass_cache = false;

		if ( ! empty( $params->bypass_cache ) ) {
			$bypass_cache = true;
		}

		if ( isset( $params->type ) && ! empty( $params->type ) ) {
			if ( ! is_array( $params->type ) ) {
				$params->type = array( trim( $params->type ) );
			}

			sort( $params->type );

			$meta_query[] = array(
				'key'     => 'type',
				'value'   => $params->type,
				'compare' => 'IN'
			);

			if ( 0 < count( $params->type ) ) {
				$cache_key .= '_type_' . trim( implode( '_', $params->type ) );
			}
		}

		if ( isset( $params->object ) && ! empty( $params->object ) ) {
			if ( ! is_array( $params->object ) ) {
				$params->object = array( $params->object );
			}

			$params->object = pods_trim( $params->object );

			sort( $params->object );

			$meta_query[] = array(
				'key'     => 'object',
				'value'   => $params->object,
				'compare' => 'IN'
			);

			if ( 1 == count( $params->object ) ) {
				$cache_key .= '_object_' . trim( implode( '', $params->object ) );
			}
		}

		if ( isset( $params->options ) && ! empty( $params->options ) && is_array( $params->options ) ) {
			foreach ( $params->options as $option => $value ) {
				if ( ! is_array( $value ) ) {
					$value = array( $value );
				}

				$value = pods_trim( $value );

				sort( $value );

				$meta_query[] = array(
					'key'     => $option,
					'value'   => pods_sanitize( $value ),
					'compare' => 'IN'
				);
			}

			$cache_key = '';
		}

		if ( isset( $params->where ) && is_array( $params->where ) ) {
			$meta_query = array_merge( $meta_query, (array) $params->where );
		}

		if ( isset( $params->order ) && ! empty( $params->order ) && in_array( strtoupper( $params->order ), array(
				'ASC',
				'DESC'
			) ) ) {
			$order = strtoupper( $params->order );
		}

		if ( isset( $params->orderby ) && ! empty( $params->orderby ) ) {
			$orderby = strtoupper( $params->orderby );
		}

		if ( isset( $params->limit ) && ! empty( $params->limit ) ) {
			$limit = pods_absint( $params->limit );
		}

		if ( isset( $params->ids ) && ! empty( $params->ids ) ) {
			$ids = $params->ids;

			if ( ! is_array( $ids ) ) {
				$ids = explode( ',', $ids );
			}
		}

		if ( empty( $ids ) ) {
			$ids = false;
		}

		$pre_key = '';

		if ( ! empty( $current_language ) ) {
			$pre_key .= '_' . $current_language;
		}

		if ( isset( $params->count ) && $params->count ) {
			$pre_key .= '_count';
		}

		if ( isset( $params->ids ) && $params->ids && ! empty( $ids ) ) {
			$pre_key .= '_ids_' . implode( '_', $ids );
		}

		if ( isset( $params->names ) && $params->names ) {
			$pre_key .= '_names';
		} elseif ( isset( $params->names_ids ) && $params->names_ids ) {
			$pre_key .= '_names_ids';
		}

		if ( isset( $params->key_names ) && $params->key_names ) {
			$pre_key .= '_namekeys';
		}

		if ( isset( $params->fields ) && ! $params->fields ) {
			$pre_key .= '_nofields';
		}

		if ( isset( $params->table_info ) && $params->table_info ) {
			$pre_key .= '_tableinfo';
		}

		$pre_key .= '_get';

		if ( empty( $cache_key ) ) {
			$cache_key = 'pods' . $pre_key . '_all';
		} else {
			$cache_key = 'pods' . $pre_key . $cache_key;
		}

		if ( ! $bypass_cache && pods_api_cache() && ! empty( $cache_key ) && ( 'pods' . ( ! empty( $current_language ) ? '_' . $current_language : '' ) . '_get_all' !== $cache_key || empty( $meta_query ) ) && $limit < 1 && ( empty( $orderby ) || 'menu_order title' === $orderby ) && empty( $ids ) ) {
			$the_pods = pods_transient_get( $cache_key );

			if ( false === $the_pods ) {
				$the_pods = pods_cache_get( $cache_key, 'pods' );
			}

			if ( ! is_array( $the_pods ) && 'none' === $the_pods ) {
				return array();
			} elseif ( false !== $the_pods ) {
				return $the_pods;
			}
		}

		$the_pods = array();

		$args = array(
			'post_type'      => '_pods_pod',
			'nopaging'       => true,
			'posts_per_page' => $limit,
			'order'          => $order,
			'orderby'        => $orderby,
			'meta_query'     => $meta_query,
		);

		// Only set post__in if there are ids to filter (see https://core.trac.wordpress.org/ticket/28099)
		if ( false !== $ids ) {
			$args['post__in'] = $ids;
		}

		$_pods = get_posts( $args );

		$export_ignore = array(
			'object_type',
			'object_name',
			'table',
			'meta_table',
			'pod_table',
			'field_id',
			'field_index',
			'field_slug',
			'field_type',
			'field_parent',
			'field_parent_select',
			'meta_field_id',
			'meta_field_index',
			'meta_field_value',
			'pod_field_id',
			'pod_field_index',
			'object_fields',
			'join',
			'where',
			'where_default',
			'orderby',
			'pod',
			'recurse',
			'table_info',
			'attributes',
			'group',
			'grouped',
			'developer_mode',
			'dependency',
			'depends-on',
			'excludes-on'
		);

		$total_fields = 0;

		if ( isset( $params->count ) && $params->count ) {
			$the_pods = count( $_pods );
		} else {
			foreach ( $_pods as $pod ) {
				if ( isset( $params->names ) && $params->names ) {
					$the_pods[ $pod->post_name ] = $pod->post_title;
				} elseif ( isset( $params->names_ids ) && $params->names_ids ) {
					$the_pods[ $pod->ID ] = $pod->post_name;
				} else {
					if ( isset( $params->fields ) && ! $params->fields ) {
						$pod->fields = false;
					}

					$pod = $this->load_pod( array(
						'pod'          => $pod,
						'table_info'   => ! empty( $params->table_info ),
						'bypass_cache' => $bypass_cache
					) );

					// Remove extra data not needed
					if ( pods_var( 'export', $params, false ) && ( ! isset( $params->fields ) || $params->fields ) ) {
						foreach ( $export_ignore as $ignore ) {
							if ( isset( $pod[ $ignore ] ) ) {
								unset( $pod[ $ignore ] );
							}
						}

						foreach ( $pod['fields'] as $field => $field_data ) {
							if ( isset( $pod['fields'][ $field ]['table_info'] ) ) {
								unset( $pod['fields'][ $field ]['table_info'] );
							}
						}
					}

					$total_fields += count( $pod['fields'] );

					if ( isset( $params->key_names ) && $params->key_names ) {
						$the_pods[ $pod['name'] ] = $pod;
					} else {
						$the_pods[ $pod['id'] ] = $pod;
					}
				}
			}
		}

		if ( ( ! function_exists( 'pll_current_language' ) || ! empty( $params->refresh ) ) && ! empty( $cache_key ) && ( 'pods' !== $cache_key || empty( $meta_query ) ) && $limit < 1 && ( empty( $orderby ) || 'menu_order title' === $orderby ) && empty( $ids ) ) {
			$total_pods = (int) ( is_array( $the_pods ) ) ? count( $the_pods ) : $the_pods;
			// Too many Pods can cause issues with the DB when caching is not enabled
			if ( 15 < $total_pods || 75 < (int) $total_fields ) {
				pods_transient_clear( $cache_key );

				if ( pods_api_cache() ) {
					if ( empty( $the_pods ) && ( ! isset( $params->count ) || ! $params->count ) ) {
						pods_cache_set( $cache_key, 'none', 'pods' );
					} else {
						pods_cache_set( $cache_key, $the_pods, 'pods' );
					}
				}
			} else {
				pods_cache_clear( $cache_key, 'pods' );

				if ( pods_api_cache() ) {
					if ( empty( $the_pods ) && ( ! isset( $params->count ) || ! $params->count ) ) {
						pods_transient_set( $cache_key, 'none' );
					} else {
						pods_transient_set( $cache_key, $the_pods );
					}
				}
			}
		}

		return $the_pods;
	}

	/**
	 * Check if a Pod's field exists
	 *
	 * $params['pod_id'] int The Pod ID
	 * $params['id'] int The field ID
	 * $params['name'] string The field name
	 *
	 * @param array $params An associative array of parameters
	 *
	 * @return bool
	 *
	 * @since 1.12
	 */
	public function field_exists( $params ) {

		$params = (object) pods_sanitize( $params );

		if ( ( ! empty( $params->id ) || ! empty( $params->name ) ) && isset( $params->pod_id ) && ! empty( $params->pod_id ) ) {
			if ( ! isset( $params->name ) ) {
				$dummy = (int) $params->id;
				$field = get_post( $dummy );
			} else {
				$field = get_posts( array(
					'name'           => $params->name,
					'post_type'      => '_pods_field',
					'posts_per_page' => 1,
					'post_parent'    => $params->pod_id
				) );
			}

			if ( ! empty( $field ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Load a field
	 *
	 * $params['pod_id'] int The Pod ID
	 * $params['pod'] string The Pod name
	 * $params['id'] int The field ID
	 * $params['name'] string The field name
	 * $params['table_info'] boolean Whether to lookup a pick field's table info
	 * $params['bypass_cache'] boolean Bypass the cache when getting data
	 *
	 * @param array   $params An associative array of parameters
	 * @param boolean $strict Whether to require a field exist or not when loading the info
	 *
	 * @return array|bool Array with field data, false if field not found
	 * @since 1.7.9
	 */
	public function load_field( $params, $strict = false ) {

		$params = (object) $params;

		if ( ! isset( $params->table_info ) ) {
			$params->table_info = false;
		}

		$bypass_cache = false;

		if ( ! empty( $params->bypass_cache ) ) {
			$bypass_cache = true;
		}

		$pod   = array();
		$field = array();

		if ( isset( $params->post_title ) ) {
			$_field = $params;
		} elseif ( isset( $params->id ) && ! empty( $params->id ) ) {
			$dummy = (int) $params->id;
			$_field = get_post( $dummy );
		} else {
			if ( ! isset( $params->pod ) ) {
				$params->pod = '';
			}

			if ( ! isset( $params->pod_id ) ) {
				$params->pod_id = 0;
			}

			if ( isset( $params->pod_data ) ) {
				$pod = $params->pod_data;
			} else {
				$pod = $this->load_pod( array(
					'name'         => $params->pod,
					'id'           => $params->pod_id,
					'table_info'   => false,
					'bypass_cache' => $bypass_cache
				), false );

				if ( false === $pod ) {
					if ( $strict ) {
						return pods_error( __( 'Pod not found', 'pods' ), $this );
					}

					return false;
				}
			}

			$params->pod_id = $pod['id'];
			$params->pod    = $pod['name'];

			if ( empty( $params->name ) && empty( $params->pod ) && empty( $params->pod_id ) ) {
				return pods_error( __( 'Either Field Name or Field ID / Pod ID are required', 'pods' ), $this );
			}

			$params->name = pods_clean_name( $params->name, true, ( 'meta' === $pod['storage'] ? false : true ) );

			if ( isset( $pod['fields'][ $params->name ] ) && isset( $pod['fields'][ $params->name ]['id'] ) ) {
				return $pod['fields'][ $params->name ];
			}

			$field = false;

			if ( ! $bypass_cache && pods_api_cache() ) {
				$field = pods_transient_get( 'pods_field_' . $params->pod . '_' . $params->name );
			}

			if ( empty( $field ) ) {
				$field = get_posts( array(
					'name'           => $params->name,
					'post_type'      => '_pods_field',
					'posts_per_page' => 1,
					'post_parent'    => $params->pod_id
				) );

				if ( empty( $field ) || empty( $field[0] ) ) {
					if ( $strict ) {
						return pods_error( __( 'Field not found', 'pods' ), $this );
					}

					return false;
				}

				$_field = $field[0];

				$field = array();
			}
		}

		if ( empty( $_field ) ) {
			if ( $strict ) {
				return pods_error( __( 'Field not found', 'pods' ), $this );
			}

			return false;
		}

		$_field = get_object_vars( $_field );

		if ( ! isset( $pod['name'] ) && ! isset( $_field['pod'] ) ) {
			if ( 0 < $_field['post_parent'] ) {
				$pod = $this->load_pod( array( 'id' => $_field['post_parent'], 'table_info' => false ), false );
			}

			if ( empty( $pod ) ) {
				if ( $strict ) {
					return pods_error( __( 'Pod for field not found', 'pods' ), $this );
				}

				return false;
			}
		}

		if ( empty( $field ) ) {
			if ( ! $bypass_cache && pods_api_cache() && ( isset( $pod['name'] ) || isset( $_field['pod'] ) ) ) {
				$field = pods_transient_get( 'pods_field_' . pods_var( 'name', $pod, pods_var( 'pod', $_field ), null, true ) . '_' . $_field['post_name'] );
			}

			if ( empty( $field ) ) {
				$defaults = array(
					'type' => 'text'
				);

				$field = array(
					'id'          => $_field['ID'],
					'name'        => $_field['post_name'],
					'label'       => $_field['post_title'],
					'description' => $_field['post_content'],
					'weight'      => $_field['menu_order'],
					'pod_id'      => $_field['post_parent'],
					'pick_object' => '',
					'pick_val'    => '',
					'sister_id'   => '',
					'table_info'  => array()
				);

				if ( isset( $pod['name'] ) ) {
					$field['pod'] = $pod['name'];
				} elseif ( isset( $_field['pod'] ) ) {
					$field['pod'] = $_field['pod'];
				}

				if ( $bypass_cache ) {
					wp_cache_delete( $field['id'], 'post_meta' );

					update_postmeta_cache( array( $field['id'] ) );
				}

				$field['options'] = get_post_meta( $field['id'] );

				$options_ignore = array(
					'method',
					'table_info',
					'attributes',
					'group',
					'grouped',
					'developer_mode',
					'dependency',
					'depends-on',
					'excludes-on'
				);

				foreach ( $options_ignore as $ignore ) {
					if ( isset( $field['options'][ $ignore ] ) ) {
						unset( $field['options'][ $ignore ] );
					}
				}

				foreach ( $field['options'] as $option => $value ) {
					if ( is_array( $value ) ) {
						foreach ( $value as $k => $v ) {
							if ( ! is_array( $v ) ) {
								$value[ $k ] = maybe_unserialize( $v );
							}
						}

						if ( 1 == count( $value ) ) {
							$value = current( $value );
						}
					} else {
						$value = maybe_unserialize( $value );
					}

					$field['options'][ $option ] = $value;
				}

				$field['options'] = array_merge( $defaults, $field['options'] );

				$field['type'] = $field['options']['type'];

				unset( $field['options']['type'] );

				if ( isset( $field['options']['pick_object'] ) ) {
					$field['pick_object'] = $field['options']['pick_object'];

					unset( $field['options']['pick_object'] );
				}

				if ( isset( $field['options']['pick_val'] ) ) {
					$field['pick_val'] = $field['options']['pick_val'];

					unset( $field['options']['pick_val'] );
				}

				if ( isset( $field['options']['sister_id'] ) ) {
					$field['sister_id'] = $field['options']['sister_id'];

					unset( $field['options']['sister_id'] );
				}

				if ( isset( $field['options']['sister_field_id'] ) ) {
					unset( $field['options']['sister_field_id'] );
				}

				if ( pods_api_cache() && ( isset( $pod['name'] ) || isset( $_field['pod'] ) ) ) {
					pods_transient_set( 'pods_field_' . pods_var( 'name', $pod, pods_var( 'pod', $_field ), null, true ) . '_' . $field['name'], $field );
				}
			}
		}

		$field['table_info'] = array();

		if ( 'pick' === $field['type'] && $params->table_info ) {
			$field['table_info'] = $this->get_table_info( $field['pick_object'], $field['pick_val'], null, null, $field );
		}

		return $field;
	}

	/**
	 * Load fields by Pod, ID, Name, and/or Type
	 *
	 * $params['pod_id'] int The Pod ID
	 * $params['pod'] string The Pod name
	 * $params['id'] array The field IDs
	 * $params['name'] array The field names
	 * $params['type'] array The field types
	 * $params['options'] array Field Option(s) key=>value array to filter by
	 * $params['where'] string WHERE clause of query
	 * $params['object_fields'] bool Whether to include the object fields for WP objects, default true
	 *
	 * @param array $params An associative array of parameters
	 * @param bool  $strict Whether to require a field exist or not when loading the info
	 *
	 * @return array Array of field data.
	 *
	 * @since 1.7.9
	 */
	public function load_fields( $params, $strict = false ) {

		// @todo Get away from using md5/serialize, I'm sure we can cache specific parts
		$cache_key = md5( serialize( $params ) );
		if ( isset( $this->fields_cache[ $cache_key ] ) ) {
			return $this->fields_cache[ $cache_key ];
		}

		$params = (object) pods_sanitize( $params );

		if ( ! isset( $params->pod ) || empty( $params->pod ) ) {
			$params->pod = '';
		}

		if ( ! isset( $params->pod_id ) || empty( $params->pod_id ) ) {
			$params->pod_id = 0;
		}

		if ( ! isset( $params->name ) || empty( $params->name ) ) {
			$params->name = array();
		} else {
			$params->name = (array) $params->name;
		}

		if ( ! isset( $params->id ) || empty( $params->id ) ) {
			$params->id = array();
		} else {
			$params->id = (array) $params->id;

			foreach ( $params->id as &$id ) {
				$id = pods_absint( $id );
			}
		}

		if ( ! isset( $params->type ) || empty( $params->type ) ) {
			$params->type = array();
		} else {
			$params->type = (array) $params->type;
		}

		if ( ! isset( $params->object_fields ) ) {
			$params->object_fields = true;
		} else {
			$params->object_fields = (boolean) $params->object_fields;
		}

		$fields = array();

		if ( ! empty( $params->pod ) || ! empty( $params->pod_id ) ) {
			$pod = $this->load_pod( array(
				'name'       => $params->pod,
				'id'         => $params->pod_id,
				'table_info' => true
			), false );

			if ( false === $pod ) {
				if ( $strict ) {
					return pods_error( __( 'Pod not found', 'pods' ), $this );
				}

				return $fields;
			}

			if ( $params->object_fields && ! empty( $pod['object_fields'] ) ) {
				$pod['fields'] = array_merge( $pod['object_fields'], $pod['fields'] );
			}

			foreach ( $pod['fields'] as $field ) {
				if ( empty( $params->name ) && empty( $params->id ) && empty( $params->type ) ) {
					$fields[ $field['name'] ] = $field;
				} elseif ( in_array( $fields['name'], $params->name ) || in_array( $fields['id'], $params->id ) || in_array( $fields['type'], $params->type ) ) {
					$fields[ $field['name'] ] = $field;
				}
			}
		} elseif ( ( isset( $params->options ) && ! empty( $params->options ) && is_array( $params->options ) ) || ( isset( $params->where ) && ! empty( $params->where ) && is_array( $params->where ) ) ) {
			$order   = 'ASC';
			$orderby = 'menu_order title';
			$limit   = - 1;
			$ids     = false;

			$meta_query = array();

			if ( isset( $params->options ) && ! empty( $params->options ) && is_array( $params->options ) ) {
				foreach ( $params->options as $option => $value ) {
					if ( ! is_array( $value ) ) {
						$value = array( $value );
					}

					$value = pods_trim( $value );

					sort( $value );

					$meta_query[] = array(
						'key'     => $option,
						'value'   => pods_sanitize( $value ),
						'compare' => 'IN'
					);
				}
			}

			if ( isset( $params->where ) && ! empty( $params->where ) && is_array( $params->where ) ) {
				$meta_query = array_merge( $meta_query, (array) $params->where );
			}

			$args = array(
				'post_type'      => '_pods_field',
				'nopaging'       => true,
				'posts_per_page' => $limit,
				'order'          => $order,
				'orderby'        => $orderby,
				'meta_query'     => $meta_query,
			);

			// Only set post__in if there are ids to filter (see https://core.trac.wordpress.org/ticket/28099)
			if ( false !== $ids ) {
				$args['post__in'] = $ids;
			}

			$fields = array();

			$_fields = get_posts( $args );

			foreach ( $_fields as $field ) {
				$field = $this->load_field( $field, false );

				if ( ! empty( $field ) ) {
					$fields[ $field['id'] ] = $field;
				}
			}
		} else {
			if ( empty( $params->name ) && empty( $params->id ) && empty( $params->type ) ) {
				return pods_error( __( 'Either Field Name / Field ID / Field Type, or Pod Name / Pod ID are required', 'pods' ), $this );
			}

			$lookup = array();

			if ( ! empty( $params->name ) ) {
				$fields = implode( "', '", $params->name );

				$lookup[] = "`post_name` IN ( '{$fields}' )";
			}

			if ( ! empty( $params->id ) ) {
				$fields = implode( ", ", $params->id );

				$lookup[] = "`ID` IN ( {$fields} )";
			}

			$lookup = implode( ' AND ', $lookup );

			$result = pods_query( "SELECT `ID`, `post_name`, `post_parent` FROM `@wp_posts` WHERE `post_type` = '_pods_field' AND ( {$lookup} )" );

			$fields = array();

			if ( ! empty( $result ) ) {
				foreach ( $result as $field ) {
					$field = $this->load_field( array(
						'id'     => $field->ID,
						'name'   => $field->post_name,
						'pod_id' => $field->post_parent
					), false );

					if ( ! empty( $field ) && ( empty( $params->type ) || in_array( $field['type'], $params->type ) ) ) {
						$fields[ $field['id'] ] = $field;
					}
				}
			}
		}
		if ( isset( $cache_key ) ) {
			$this->fields_cache[ $cache_key ] = $fields;
		}

		return $fields;
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

		if ( is_object( $params ) && isset( $params->post_title ) ) {
			$_object = get_object_vars( $params );
		} else {
			$params = (object) pods_sanitize( $params );

			if ( ! isset( $params->type ) || empty( $params->type ) ) {
				return pods_error( __( 'Object type is required', 'pods' ), $this );
			}

			if ( ( ! isset( $params->id ) || empty( $params->id ) ) && ( ! isset( $params->name ) || empty( $params->name ) ) ) {
				return pods_error( __( 'Either Object ID or Name are required', 'pods' ), $this );
			}

			/**
			 * @var $wpdb wpdb
			 */
			global $wpdb;

			if ( isset( $params->name ) ) {
				$_object = pods_by_title( $params->name, ARRAY_A, '_pods_' . $params->type, 'publish' );
			} else {
				$object = $params->id;

				$_object = get_post( $object, ARRAY_A );
			}

			if ( empty( $_object ) ) {
				if ( $strict ) {
					return pods_error( __( 'Object not found', 'pods' ), $this );
				}

				return false;
			}
		}

		$object = array(
			'id'   => $_object['ID'],
			'name' => $_object['post_title'],
			'code' => $_object['post_content'],
			'type' => str_replace( '_pods_', '', $_object['post_type'] ),
			'slug' => $_object['post_name']
		);

		$object['options'] = get_post_meta( $object['id'] );

		foreach ( $object['options'] as $option => &$value ) {
			if ( is_array( $value ) && 1 == count( $value ) ) {
				$value = current( $value );
			}
		}

		return $object;
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

		$params = (object) pods_sanitize( $params );

		if ( ! isset( $params->type ) || empty( $params->type ) ) {
			return pods_error( __( 'Pods Object type is required', 'pods' ), $this );
		}

		$order   = 'ASC';
		$orderby = 'menu_order';
		$limit   = - 1;
		$ids     = false;

		$meta_query = array();
		$cache_key  = '';

		if ( isset( $params->options ) && ! empty( $params->options ) && is_array( $params->options ) ) {
			foreach ( $params->options as $option => $value ) {
				if ( ! is_array( $value ) ) {
					$value = array( $value );
				}

				$value = pods_trim( $value );

				sort( $value );

				$meta_query[] = array(
					'key'     => $option,
					'value'   => pods_sanitize( $value ),
					'compare' => 'IN'
				);
			}
		}

		if ( isset( $params->where ) && is_array( $params->where ) ) {
			$meta_query = array_merge( $meta_query, (array) $params->where );
		}

		if ( isset( $params->order ) && ! empty( $params->order ) && in_array( strtoupper( $params->order ), array(
				'ASC',
				'DESC'
			) ) ) {
			$order = strtoupper( $params->order );
		}

		if ( isset( $params->orderby ) && ! empty( $params->orderby ) ) {
			$orderby = strtoupper( $params->orderby );
		}

		if ( isset( $params->limit ) && ! empty( $params->limit ) ) {
			$limit = pods_absint( $params->limit );
		}

		if ( isset( $params->ids ) && ! empty( $params->ids ) ) {
			$ids = $params->ids;

			if ( ! is_array( $ids ) ) {
				$ids = explode( ',', $ids );
			}
		}

		if ( empty( $ids ) ) {
			$ids = false;
		}

		if ( pods_api_cache() && empty( $meta_query ) && empty( $limit ) && ( empty( $orderby ) || 'menu_order' === $orderby ) && empty( $ids ) ) {
			$cache_key = 'pods_objects_' . $params->type;

			$the_objects = pods_transient_get( $cache_key );

			if ( false !== $the_objects ) {
				return $the_objects;
			}
		}

		$the_objects = array();

		$args = array(
			'post_type'      => '_pods_' . $params->type,
			'nopaging'       => true,
			'posts_per_page' => $limit,
			'order'          => $order,
			'orderby'        => $orderby,
			'meta_query'     => $meta_query,
		);

		// Only set post__in if there are ids to filter (see https://core.trac.wordpress.org/ticket/28099)
		if ( false !== $ids ) {
			$args['post__in'] = $ids;
		}

		$objects = get_posts( $args );

		foreach ( $objects as $object ) {
			$object = $this->load_object( $object );

			$the_objects[ $object['name'] ] = $object;
		}

		if ( pods_api_cache() && ! empty( $cache_key ) ) {
			pods_transient_set( $cache_key, $the_objects );
		}

		return $the_objects;
	}

	/**
	 * @see   PodsAPI::load_object
	 *
	 * Load a Pod Template
	 *
	 * $params['id'] int The template ID
	 * $params['name'] string The template name
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
		if ( ! isset( $params->name ) && isset( $params->uri ) ) {
			$params->name = $params->uri;
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
	 */
	public function load_helper( $params ) {

		if ( ! class_exists( 'Pods_Helpers' ) ) {
			return false;
		}

		$params       = (object) $params;
		$params->type = 'helper';

		return $this->load_object( $params );
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
	 */
	public function load_helpers( $params = null ) {

		if ( ! class_exists( 'Pods_Helpers' ) ) {
			return array();
		}

		$params       = (object) $params;
		$params->type = 'helper';

		return $this->load_objects( $params );
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
			$pod = $this->load_pod( array( 'name' => $params->pod, 'table_info' => false ), false );

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

		$related_pod = $this->load_pod( array( 'name' => $params->related_pod, 'table_info' => false ), false );

		if ( false === $related_pod || ( false !== $type && 'pod' !== $type && $type !== $related_pod['type'] ) ) {
			return pods_error( __( 'Related Pod not found', 'pods' ), $this );
		}

		$params->related_pod_id = $related_pod['id'];
		$params->related_pod    = $related_pod['name'];

		$sister_fields = array();

		foreach ( $related_pod['fields'] as $field ) {
			if ( 'pick' === $field['type'] && in_array( $field['pick_object'], array(
					$pod['type'],
					'pod'
				) ) && ( $params->pod == $field['pick_object'] || $params->pod == $field['pick_val'] ) ) {
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
	public function handle_field_validation( &$value, $field, $object_fields, $fields, $pod, $params ) {

		$tableless_field_types = PodsForm::tableless_field_types();

		$fields = array_merge( $fields, $object_fields );

		$options = $fields[ $field ];

		$id = ( is_object( $params ) ? $params->id : ( is_object( $pod ) ? $pod->id() : 0 ) );

		if ( is_object( $pod ) ) {
			$pod = $pod->pod_data;
		}

		$type  = $options['type'];
		$label = $options['label'];
		$label = empty( $label ) ? $field : $label;

		// Verify required fields
		if ( 1 == pods_var( 'required', $options['options'], 0 ) && 'slug' !== $type ) {
			if ( '' === $value || null === $value || array() === $value ) {
				return pods_error( sprintf( __( '%s is empty', 'pods' ), $label ), $this );
			}

			if ( 'multi' === pods_var( 'pick_format_type', $options['options'] ) && 'autocomplete' !== pods_var( 'pick_format_multi', $options['options'] ) ) {
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
		if ( 1 == pods_var( 'unique', $options['options'], 0 ) && '' !== $value && null !== $value && array() !== $value ) {
			if ( empty( $pod ) ) {
				return false;
			}

			if ( ! in_array( $type, $tableless_field_types ) ) {
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

		$validate = PodsForm::validate( $options['type'], $value, $field, array_merge( $options, pods_var( 'options', $options, array() ) ), $fields, $pod, $id, $params );

		$validate = $this->do_hook( 'field_validation', $validate, $value, $field, $object_fields, $fields, $pod, $params );

		return $validate;
	}

	/**
	 * Find items related to a parent field
	 *
	 * @param int   $field_id The Field ID
	 * @param int   $pod_id   The Pod ID
	 * @param mixed $ids      A comma-separated string (or array) of item IDs
	 * @param array $field    Field data array
	 * @param array $pod      Pod data array
	 *
	 * @return int[]
	 *
	 * @since 2.0.0
	 *
	 * @uses  pods_query()
	 */
	public function lookup_related_items( $field_id, $pod_id, $ids, $field = null, $pod = null ) {

		$related_ids = array();

		if ( ! is_array( $ids ) ) {
			$ids = explode( ',', $ids );
		}

		$ids = array_map( 'absint', $ids );

		$ids = array_unique( array_filter( $ids ) );

		$idstring = implode( ',', $ids );

		if ( 0 != $pod_id && 0 != $field_id && isset( self::$related_item_cache[ $pod_id ][ $field_id ][ $idstring ] ) ) {
			// Check cache first, no point in running the same query multiple times
			return self::$related_item_cache[ $pod_id ][ $field_id ][ $idstring ];
		}

		$tableless_field_types = PodsForm::tableless_field_types();

		$field_type = pods_v( 'type', $field );

		if ( empty( $ids ) || ! in_array( $field_type, $tableless_field_types ) ) {
			return array();
		}

		$related_pick_limit = 0;

		if ( empty( $field ) ) {
			$field = $this->load_field( array( 'id' => $field_id ) );
		}

		if ( ! empty( $field ) ) {
			$options = (array) pods_var_raw( 'options', $field, $field, null, true );

			$related_pick_limit = (int) pods_v( $field_type . '_limit', $options, 0 );

			if ( 'single' === pods_var_raw( $field_type . '_format_type', $options ) ) {
				$related_pick_limit = 1;
			}

			// Temporary hack until there's some better handling here
			$related_pick_limit = $related_pick_limit * count( $ids );
		}

		if ( 'taxonomy' === $field_type ) {
			$related = wp_get_object_terms( $ids, pods_v( 'name', $field ), array( 'fields' => 'ids' ) );

			if ( ! is_wp_error( $related ) ) {
				$related_ids = $related;
			}
		} elseif ( 'comment' === $field_type ) {
			$comment_args = array(
				'post__in' => $ids,
				'fields'   => 'ids',
			);

			$related = get_comments( $comment_args );

			if ( ! is_wp_error( $related ) ) {
				$related_ids = $related;
			}
		} elseif ( ! pods_tableless() ) {
			$ids = implode( ', ', $ids );

			$field_id  = (int) $field_id;
			$sister_id = (int) pods_var_raw( 'sister_id', $field, 0 );

			$related_where = "
				`field_id` = {$field_id}
				AND `item_id` IN ( {$ids} )
			";

			$sql = "
				SELECT item_id, related_item_id, related_field_id
				FROM `@wp_podsrel`
				WHERE
					{$related_where}
				ORDER BY `weight`
			";

			$relationships = pods_query( $sql );

			if ( ! empty( $relationships ) ) {
				foreach ( $relationships as $relation ) {
					if ( ! in_array( $relation->related_item_id, $related_ids ) ) {
						$related_ids[] = (int) $relation->related_item_id;
					} elseif ( 0 < $sister_id && $field_id == $relation->related_field_id && ! in_array( $relation->item_id, $related_ids ) ) {
						$related_ids[] = (int) $relation->item_id;
					}
				}
			}
		} else {
			if ( ! is_array( $pod ) ) {
				$pod = $this->load_pod( array( 'id' => $pod_id, 'table_info' => false ), false );
			}

			if ( ! empty( $pod ) && in_array( $pod['type'], array(
					'post_type',
					'media',
					'taxonomy',
					'user',
					'comment',
					'settings'
				) ) ) {
				$meta_type = $pod['type'];

				if ( in_array( $meta_type, array( 'post_type', 'media' ) ) ) {
					$meta_type = 'post';
				} elseif ( 'taxonomy' === $meta_type ) {
					$meta_type = 'term';
				}

				$no_conflict = pods_no_conflict_check( ( 'term' === $meta_type ? 'taxonomy' : $meta_type ) );

				if ( ! $no_conflict ) {
					pods_no_conflict_on( ( 'term' === $meta_type ? 'taxonomy' : $meta_type ) );
				}

				foreach ( $ids as $id ) {
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
				}

				if ( ! $no_conflict ) {
					pods_no_conflict_off( ( 'term' === $meta_type ? 'taxonomy' : $meta_type ) );
				}
			}
		}

		if ( is_array( $related_ids ) ) {
			$related_ids = array_unique( array_filter( $related_ids ) );

			if ( 0 < $related_pick_limit && ! empty( $related_ids ) ) {
				$related_ids = array_slice( $related_ids, 0, $related_pick_limit );
			}
		}
		if ( 0 != $pod_id && 0 != $field_id && ! empty( $related_ids ) ) {
			// Only cache if $pod_id and $field_id were passed
			self::$related_item_cache[ $pod_id ][ $field_id ][ $idstring ] = $related_ids;
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
			$options = (array) pods_var_raw( 'options', $field, $field, null, true );

			$related_pick_limit = (int) pods_v( 'pick_limit', $options, 0 );

			if ( 'single' === pods_var_raw( 'pick_format_type', $options ) ) {
				$related_pick_limit = 1;
			}
		}

		if ( ! pods_tableless() ) {
			$field_id  = (int) $field_id;
			$sister_id = (int) pods_var_raw( 'sister_id', $field, 0 );

			$related_where = "
				`field_id` = {$field_id}
				AND `related_item_id` = {$id}
			";

			$sql = "
				SELECT *
				FROM `@wp_podsrel`
				WHERE
					{$related_where}
				ORDER BY `weight`
			";

			$relationships = pods_query( $sql );

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

			if ( ! is_array( $pod ) ) {
				$pod = $this->load_pod( array( 'id' => $pod_id, 'table_info' => false ), false );
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

				if ( in_array( $meta_type, array( 'post_type', 'media' ) ) ) {
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

		$info = array();

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

			$pod = $this->load_pod( array( 'name' => $name, 'table_info' => false ), false );

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
				$pod = $this->load_pod( array( 'name' => $name, 'table_info' => false ), false );

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

			if ( is_array( $info['pod'] ) && 'pod' === pods_v( 'type', $info['pod'] ) ) {
				$info['meta_field_value'] = pods_v( 'pod_index', $info['pod']['options'], 'id', true );
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

				if ( 1 == pods_v( 'hierarchical', $info['pod']['options'], 0 ) ) {
					$parent_field = pods_v( 'pod_parent', $info['pod']['options'], 'id', true );

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
		 * @var $wpdb                         wpdb
		 * @var $sitepress                    SitePress
		 * @var $polylang                     object
		 */
		/*
		 * @todo wpml-comp Remove global object usage
		 */
		global $wpdb, $sitepress, $polylang;

		// @todo Handle $object arrays for Post Types, Taxonomies, Comments (table pulled from first object in array)

		$info = array(
			//'select' => '`t`.*',
			'object_type'         => $object_type,
			'type'                => null,
			'object_name'         => $object,
			'object_hierarchical' => false,

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
		}

		$pod_name = $pod;

		if ( is_array( $pod_name ) ) {
			$pod_name = pods_var_raw( 'name', $pod_name, ( version_compare( PHP_VERSION, '5.4.0', '>=' ) ? json_encode( $pod_name, JSON_UNESCAPED_UNICODE ) : json_encode( $pod_name ) ), null, true );
		} else {
			$pod_name = $object;
		}

		$field_name = $field;

		if ( is_array( $field_name ) ) {
			$field_name = pods_var_raw( 'name', $field_name, ( version_compare( PHP_VERSION, '5.4.0', '>=' ) ? json_encode( $pod_name, JSON_UNESCAPED_UNICODE ) : json_encode( $field_name ) ), null, true );
		}

		$transient = 'pods_' . $wpdb->prefix . '_get_table_info_' . md5( $object_type . '_object_' . $object . '_name_' . $name . '_pod_' . $pod_name . '_field_' . $field_name );

		$current_language       = false;
		$current_language_t_id  = 0;
		$current_language_tt_id = 0;

		// Get current language data
		$lang_data = PodsInit::$i18n->get_current_language_data();

		if ( $lang_data ) {
			if ( ! empty( $lang_data['language'] ) ) {
				$current_language = $lang_data['language'];
			}

			if ( ! empty( $lang_data['t_id'] ) ) {
				$current_language_t_id = $lang_data['t_id'];
			}

			if ( ! empty( $lang_data['tt_id'] ) ) {
				$current_language_tt_id = $lang_data['tt_id'];
			}

			if ( ! empty( $lang_data['tl_t_id'] ) ) {
				$current_language_tl_t_id = $lang_data['tl_t_id'];
			}

			if ( ! empty( $lang_data['tl_tt_id'] ) ) {
				$current_language_tl_tt_id = $lang_data['tl_tt_id'];
			}
		}

		if ( ! empty( $current_language ) ) {
			$transient = 'pods_' . $wpdb->prefix . '_get_table_info_' . $current_language . '_' . md5( $object_type . '_object_' . $object . '_name_' . $name . '_pod_' . $pod_name . '_field_' . $field_name );
		}

		$_info = false;

		if ( isset( self::$table_info_cache[ $transient ] ) ) {
			// Prefer info from the object internal cache
			$_info = self::$table_info_cache[ $transient ];
		} elseif ( pods_api_cache() ) {
			$_info = pods_transient_get( $transient );
			if ( false === $_info && ! did_action( 'init' ) ) {
				$_info = pods_transient_get( $transient . '_pre_init' );
			}
		}

		if ( false !== $_info && is_array( $_info ) ) {
			// Data was cached, use that
			$info = $_info;
		} else {
			// Data not cached, load it up
			$_info = $this->get_table_info_load( $object_type, $object, $name, $pod );
			if ( isset( $_info['type'] ) ) {
				// Allow function to override $object_type
				$object_type = $_info['type'];
			}
			$info = array_merge( $info, $_info );
		}

		if ( 0 === strpos( $object_type, 'post_type' ) || 'media' === $object_type || in_array( pods_var_raw( 'type', $info['pod'] ), array(
				'post_type',
				'media'
			) ) ) {
			$info['table']      = $wpdb->posts;
			$info['meta_table'] = $wpdb->postmeta;

			$info['field_id']            = 'ID';
			$info['field_index']         = 'post_title';
			$info['field_slug']          = 'post_name';
			$info['field_type']          = 'post_type';
			$info['field_parent']        = 'post_parent';
			$info['field_parent_select'] = '`t`.`' . $info['field_parent'] . '`';

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
				$info['pod_table'] = $wpdb->prefix . 'pods_media';
			} else {
				$info['pod_table'] = $wpdb->prefix . 'pods_' . pods_clean_name( $post_type, true, false );
			}

			$post_type_object = get_post_type_object( $post_type );

			if ( is_object( $post_type_object ) && $post_type_object->hierarchical ) {
				$info['object_hierarchical'] = true;
			}

			// Post Status default
			$post_status = array( 'publish' );

			// Pick field post_status option
			if ( ! empty( $field['options']['pick_post_status'] ) ) {
				$post_status = (array) $field['options']['pick_post_status'];
			} elseif ( ! empty( $field['pick_post_status'] ) ) {
				$post_status = (array) $field['pick_post_status'];
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

			$info['where'] = array(
				//'post_status' => '`t`.`post_status` IN ( "inherit", "publish" )', // @todo Figure out what statuses Attachments can be
				'post_type' => '`t`.`' . $info['field_type'] . '` = "' . $post_type . '"'
			);

			if ( 'post_type' === $object_type ) {
				$info['where_default'] = '`t`.`post_status` IN ( "' . implode( '", "', $post_status ) . '" )';
			}

			$info['orderby'] = '`t`.`menu_order`, `t`.`' . $info['field_index'] . '`, `t`.`post_date`';

			/*
			 * @todo wpml-comp Check if WPML filters can be applied afterwards
			 */
			// WPML support
			if ( did_action( 'wpml_loaded' ) && ! empty( $current_language ) && apply_filters( 'wpml_is_translated_post_type', false, $post_type ) && apply_filters( 'wpml_setting', true, 'auto_adjust_ids' ) ) {
				$info['join']['wpml_translations'] = "
						LEFT JOIN `{$wpdb->prefix}icl_translations` AS `wpml_translations`
							ON `wpml_translations`.`element_id` = `t`.`ID`
								AND `wpml_translations`.`element_type` = 'post_{$post_type}'
								AND `wpml_translations`.`language_code` = '{$current_language}'
					";

				$info['join']['wpml_languages'] = "
						LEFT JOIN `{$wpdb->prefix}icl_languages` AS `wpml_languages`
							ON `wpml_languages`.`code` = `wpml_translations`.`language_code` AND `wpml_languages`.`active` = 1
					";

				$info['where']['wpml_languages'] = "`wpml_languages`.`code` IS NOT NULL";
			} elseif ( ( function_exists( 'PLL' ) || is_object( $polylang ) ) && ! empty( $current_language ) && function_exists( 'pll_is_translated_post_type' ) && pll_is_translated_post_type( $post_type ) ) {
				// Polylang support
				$info['join']['polylang_languages'] = "
						LEFT JOIN `{$wpdb->term_relationships}` AS `polylang_languages`
							ON `polylang_languages`.`object_id` = `t`.`ID`
								AND `polylang_languages`.`term_taxonomy_id` = {$current_language_tt_id}
					";

				$info['where']['polylang_languages'] = "`polylang_languages`.`object_id` IS NOT NULL";
			}

			$info['object_fields'] = $this->get_wp_object_fields( $object_type, $info['pod'] );
		} elseif ( 0 === strpos( $object_type, 'taxonomy' ) || in_array( $object_type, array(
				'nav_menu',
				'post_format'
			) ) || 'taxonomy' === pods_var_raw( 'type', $info['pod'] ) ) {
			$info['table']      = $wpdb->terms;
			$info['meta_table'] = $wpdb->terms;

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
			$info['field_parent_select'] = '`tt`.`' . $info['field_parent'] . '`';

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

			if ( ! in_array( $object_type, array( 'nav_menu', 'post_format' ) ) ) {
				$object_type = 'taxonomy';
			}

			$taxonomy = pods_sanitize( ( empty( $object ) ? $name : $object ) );

			$info['pod_table'] = $wpdb->prefix . 'pods_' . pods_clean_name( $taxonomy, true, false );

			$taxonomy_object = get_taxonomy( $taxonomy );

			if ( is_object( $taxonomy_object ) && $taxonomy_object->hierarchical ) {
				$info['object_hierarchical'] = true;
			}

			$info['where'] = array(
				'tt.taxonomy' => '`tt`.`' . $info['field_type'] . '` = "' . $taxonomy . '"'
			);

			/*
			 * @todo wpml-comp WPML API call for is_translated_taxononomy
			 * @todo wpml-comp Check if WPML filters can be applied afterwards
			 */
			// WPML Support
			if ( is_object( $sitepress ) && ! empty( $current_language ) && $sitepress->is_translated_taxonomy( $taxonomy ) && apply_filters( 'wpml_setting', true, 'auto_adjust_ids' ) ) {
				$info['join']['wpml_translations'] = "
						LEFT JOIN `{$wpdb->prefix}icl_translations` AS `wpml_translations`
							ON `wpml_translations`.`element_id` = `tt`.`term_taxonomy_id`
								AND `wpml_translations`.`element_type` = 'tax_{$taxonomy}'
								AND `wpml_translations`.`language_code` = '{$current_language}'
					";

				$info['join']['wpml_languages'] = "
						LEFT JOIN `{$wpdb->prefix}icl_languages` AS `wpml_languages`
							ON `wpml_languages`.`code` = `wpml_translations`.`language_code` AND `wpml_languages`.`active` = 1
					";

				$info['where']['wpml_languages'] = "`wpml_languages`.`code` IS NOT NULL";
			} elseif ( ( function_exists( 'PLL' ) || is_object( $polylang ) ) && ! empty( $current_language ) && ! empty( $current_language_tl_tt_id ) && function_exists( 'pll_is_translated_taxonomy' ) && pll_is_translated_taxonomy( $taxonomy ) ) {
				// Polylang support
				$info['join']['polylang_languages'] = "
					LEFT JOIN `{$wpdb->term_relationships}` AS `polylang_languages`
						ON `polylang_languages`.`object_id` = `t`.`term_id`
							AND `polylang_languages`.`term_taxonomy_id` = {$current_language_tl_tt_id}
				";

				$info['where']['polylang_languages'] = "`polylang_languages`.`object_id` IS NOT NULL";
			}

			$info['object_fields'] = $this->get_wp_object_fields( $object_type, $info['pod'] );
		} elseif ( 'user' === $object_type || 'user' === pods_var_raw( 'type', $info['pod'] ) ) {
			$info['table']      = $wpdb->users;
			$info['meta_table'] = $wpdb->usermeta;
			$info['pod_table']  = $wpdb->prefix . 'pods_user';

			$info['field_id']    = 'ID';
			$info['field_index'] = 'display_name';
			$info['field_slug']  = 'user_nicename';

			$info['meta_field_id']    = 'user_id';
			$info['meta_field_index'] = 'meta_key';
			$info['meta_field_value'] = 'meta_value';

			$info['where'] = array(
				'user_status' => '`t`.`user_status` = 0'
			);

			$info['object_fields'] = $this->get_wp_object_fields( $object_type, $info['pod'] );
		} elseif ( 'comment' === $object_type || 'comment' === pods_var_raw( 'type', $info['pod'] ) ) {
			//$info[ 'object_hierarchical' ] = true;

			$info['table']      = $wpdb->comments;
			$info['meta_table'] = $wpdb->commentmeta;
			$info['pod_table']  = $wpdb->prefix . 'pods_comment';

			$info['field_id']            = 'comment_ID';
			$info['field_index']         = 'comment_date';
			$info['field_type']          = 'comment_type';
			$info['field_parent']        = 'comment_parent';
			$info['field_parent_select'] = '`t`.`' . $info['field_parent'] . '`';

			$info['meta_field_id']    = 'comment_id';
			$info['meta_field_index'] = 'meta_key';
			$info['meta_field_value'] = 'meta_value';

			$object = 'comment';

			$comment_type = ( empty( $object ) ? $name : $object );

			$comment_type_clause = '`t`.`' . $info['field_type'] . '` = "' . $comment_type . '"';

			if ( 'comment' === $comment_type ) {
				$comment_type_clause = '( ' . $comment_type_clause . ' OR `t`.`' . $info['field_type'] . '` = "" )';
			}

			$info['where'] = array(
				'comment_approved' => '`t`.`comment_approved` = 1',
				'comment_type'     => $comment_type_clause
			);

			$info['orderby'] = '`t`.`' . $info['field_index'] . '` DESC, `t`.`' . $info['field_id'] . '`';
		} elseif ( in_array( $object_type, array(
				'option',
				'settings'
			) ) || 'settings' === pods_var_raw( 'type', $info['pod'] ) ) {
			$info['table']      = $wpdb->options;
			$info['meta_table'] = $wpdb->options;

			$info['field_id']    = 'option_id';
			$info['field_index'] = 'option_name';

			$info['meta_field_id']    = 'option_id';
			$info['meta_field_index'] = 'option_name';
			$info['meta_field_value'] = 'option_value';

			$info['orderby'] = '`t`.`' . $info['field_index'] . '` ASC';
		} elseif ( is_multisite() && ( in_array( $object_type, array(
					'site_option',
					'site_settings'
				) ) || 'site_settings' === pods_var_raw( 'type', $info['pod'] ) ) ) {
			$info['table']      = $wpdb->sitemeta;
			$info['meta_table'] = $wpdb->sitemeta;

			$info['field_id']    = 'site_id';
			$info['field_index'] = 'meta_key';

			$info['meta_field_id']    = 'site_id';
			$info['meta_field_index'] = 'meta_key';
			$info['meta_field_value'] = 'meta_value';

			$info['orderby'] = '`t`.`' . $info['field_index'] . '` ASC';
		} elseif ( is_multisite() && 'network' === $object_type ) { // Network = Site
			$info['table']      = $wpdb->site;
			$info['meta_table'] = $wpdb->sitemeta;

			$info['field_id']    = 'id';
			$info['field_index'] = 'domain';

			$info['meta_field_id']    = 'site_id';
			$info['meta_field_index'] = 'meta_key';
			$info['meta_field_value'] = 'meta_value';

			$info['orderby'] = '`t`.`' . $info['field_index'] . '` ASC, `t`.`path` ASC, `t`.`' . $info['field_id'] . '`';
		} elseif ( is_multisite() && 'site' === $object_type ) { // Site = Blog
			$info['table'] = $wpdb->blogs;

			$info['field_id']    = 'blog_id';
			$info['field_index'] = 'domain';
			$info['field_type']  = 'site_id';

			$info['where'] = array(
				'archived' => '`t`.`archived` = 0',
				'spam'     => '`t`.`spam` = 0',
				'deleted'  => '`t`.`deleted` = 0',
				'site_id'  => '`t`.`' . $info['field_type'] . '` = ' . (int) get_current_site()->id
			);

			$info['orderby'] = '`t`.`' . $info['field_index'] . '` ASC, `t`.`path` ASC, `t`.`' . $info['field_id'] . '`';
		} elseif ( 'table' === $object_type || 'table' === pods_var_raw( 'type', $info['pod'] ) ) {
			$info['table']     = ( empty( $object ) ? $name : $object );
			$info['pod_table'] = $wpdb->prefix . 'pods_' . $info['table'];

			if ( ! empty( $field ) && is_array( $field ) ) {
				$info['table']       = pods_var_raw( 'pick_table', pods_var_raw( 'options', $field, $field ) );
				$info['field_id']    = pods_var_raw( 'pick_table_id', pods_var_raw( 'options', $field, $field ) );
				$info['meta_field_value'] = pods_var_raw( 'pick_table_index', pods_var_raw( 'options', $field, $field ) );
				$info['field_index']      = $info['meta_field_value'];
				$info['meta_field_index'] = $info['meta_field_value'];
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
			$info['orderby'] = '`t`.`' . $info['field_index'] . '`, `t`.`' . $info['field_id'] . '`';
		}

		if ( 'table' === pods_var_raw( 'storage', $info['pod'] ) && ! in_array( $object_type, array(
				'pod',
				'table'
			) ) ) {
			$info['join']['d'] = 'LEFT JOIN `' . $info['pod_table'] . '` AS `d` ON `d`.`id` = `t`.`' . $info['field_id'] . '`';
			//$info[ 'select' ] .= ', `d`.*';
		}

		if ( ! empty( $info['pod'] ) && is_array( $info['pod'] ) ) {
			$info['recurse'] = true;
		}

		$info['type']        = $object_type;
		$info['object_name'] = $object;

		if ( pods_api_cache() ) {
			if ( ! did_action( 'init' ) ) {
				$transient .= '_pre_init';
			}
			pods_transient_set( $transient, $info );
		}

		self::$table_info_cache[ $transient ] = apply_filters( 'pods_api_get_table_info', $info, $object_type, $object, $name, $pod, $field, $this );

		return self::$table_info_cache[ $transient ];
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

		$pod = $this->load_pod( array( 'name' => $this->pod ) );

		if ( false === $pod ) {
			return pods_error( __( 'Pod not found', 'pods' ), $this );
		}

		$fields = array_merge( $pod['fields'], $pod['object_fields'] );

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
							if ( in_array( $type, PodsForm::file_field_types() ) || 'media' === $pick_object ) {
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
										'name'       => $pick_val,
										'table_info' => true
									), false );
								}

								if ( empty( $related_pod ) ) {
									$related_pod = array(
										'id'   => 0,
										'type' => $pick_object
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
										$related_pod['type']
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
								} elseif ( in_array( $pick_object, $simple_tableless_objects ) ) {
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
					'pod'  => $this->pod,
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
			$pod = $this->pod;
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
	 * @param array $pod
	 *
	 * @return void
	 *
	 * @since 2.0.0
	 */
	public function cache_flush_pods( $pod = null ) {

		/**
		 * @var $wpdb wpdb
		 */
		global $wpdb;

		pods_transient_clear( 'pods' );
		pods_transient_clear( 'pods_components' );

		if ( null !== $pod && is_array( $pod ) ) {
			pods_transient_clear( 'pods_pod_' . $pod['name'] );
			pods_cache_clear( $pod['name'], 'pods-class' );

			foreach ( $pod['fields'] as $field ) {
				pods_transient_clear( 'pods_field_' . $pod['name'] . '_' . $field['name'] );
			}

			if ( in_array( $pod['type'], array( 'post_type', 'taxonomy' ) ) ) {
				pods_transient_clear( 'pods_wp_cpt_ct' );
			}
		} else {
			pods_transient_clear( 'pods_wp_cpt_ct' );
		}

		// Delete transients in the database
		$wpdb->query( "DELETE FROM `{$wpdb->options}` WHERE `option_name` LIKE '_transient_pods%'" );
		$wpdb->query( "DELETE FROM `{$wpdb->options}` WHERE `option_name` LIKE '_transient_timeout_pods%'" );

		// Delete Pods Options Cache in the database
		$wpdb->query( "DELETE FROM `{$wpdb->options}` WHERE `option_name` LIKE '_pods_option_%'" );

		pods_cache_clear( true );

		pods_transient_set( 'pods_flush_rewrites', 1 );

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

		$this->display_errors = false;

		$form = null;

		$nonce    = pods_var( '_pods_nonce', $params );
		$pod      = pods_var( '_pods_pod', $params );
		$id       = pods_var( '_pods_id', $params );
		$uri      = pods_var( '_pods_uri', $params );
		$form     = pods_var( '_pods_form', $params );
		$location = pods_var( '_pods_location', $params );

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

		$uid = @session_id();

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
			$data[ $field ] = pods_var_raw( 'pods_field_' . $field, $params, '' );
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
	 * Handle variables that have been deprecated
	 *
	 * @since 2.0.0
	 */
	public function __get( $name ) {

		$name = (string) $name;

		if ( ! isset( $this->deprecated ) ) {
			require_once( PODS_DIR . 'deprecated/classes/PodsAPI.php' );
			$this->deprecated = new PodsAPI_Deprecated( $this );
		}

		$var = null;

		if ( isset( $this->deprecated->{$name} ) ) {
			pods_deprecated( "PodsAPI->{$name}", '2.0' );

			$var = $this->deprecated->{$name};
		} else {
			pods_deprecated( "PodsAPI->{$name}", '2.0' );
		}

		return $var;
	}

	/**
	 * Handle methods that have been deprecated
	 *
	 * @since 2.0.0
	 */
	public function __call( $name, $args ) {

		$name = (string) $name;

		if ( ! isset( $this->deprecated ) ) {
			require_once( PODS_DIR . 'deprecated/classes/PodsAPI.php' );
			$this->deprecated = new PodsAPI_Deprecated( $this );
		}

		if ( method_exists( $this->deprecated, $name ) ) {
			return call_user_func_array( array( $this->deprecated, $name ), $args );
		} else {
			pods_deprecated( "PodsAPI::{$name}", '2.0' );
		}
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

}
