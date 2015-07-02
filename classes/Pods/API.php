<?php
/**
 * @package Pods
 * @category API
 */
class Pods_API {

	/**
	 * @var Pods_API
	 */
	static $instance = null;

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
	 * @deprecated 2.0
	 */
	public $format = null;

	/**
	 * @var
	 */
	private $deprecated;

	/**
	 * @var array
	 * @since 2.5
	 */
	private static $table_info_cache = array();

	/**
	 * @var array
	 * @since 2.5
	 */
	private static $related_item_cache = array();

	/**
	 * Singleton handling for a basic pods_api() request
	 *
	 * @param string $pod    (optional) The pod name
	 * @param string $format (deprecated) Format for import/export, "php" or "csv"
	 *
	 * @return \Pods_API
	 *
	 * @since 2.3.5
	 */
	public static function init( $pod = null, $format = null ) {

		$class = get_called_class();

		if ( null !== $pod || null !== $format ) {
			return new $class( $pod, $format );
		} elseif ( ! is_object( self::$instance ) ) {
			self::$instance = new $class();
		}

		return self::$instance;
	}

	/**
	 * Store and retrieve data programatically
	 *
	 * @param string $pod    (optional) The pod name
	 * @param string $format (deprecated) Format for import/export, "php" or "csv"
	 *
	 * @return \Pods_API
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

			$pod = pods_clean_name( $pod, true, false );

			$pod = $this->load_pod( array( 'name' => $pod ), false );

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
	 * @param string $object_type Object type: post|user|comment|setting
	 * @param array  $data        All post data to be saved
	 * @param array  $meta        (optional) Associative array of meta keys and values
	 * @param bool   $strict      (optional) Decides whether the previous saved meta should be deleted or not
	 * @param bool   $sanitized   (optional) Will unsanitize the data, should be passed if the data is sanitized before sending.
	 * @param array  $fields      (optional) The array of fields and their options, for further processing with
	 *
	 * @return bool|mixed
	 *
	 * @since 2.0
	 */
	public function save_wp_object( $object_type, $data, $meta = array(), $strict = false, $sanitized = false, $fields = array() ) {
		if ( in_array( $object_type, array( 'post_type', 'media' ) ) ) {
			$object_type = 'post';
		}

		if ( $sanitized ) {
			$data = pods_unsanitize( $data );
			$meta = pods_unsanitize( $meta );
		}

		if ( in_array( $object_type, array( 'post', 'user', 'comment' ) ) ) {
			return call_user_func( array( $this, 'save_' . $object_type ), $data, $meta, $strict, false, $fields );
		} elseif ( 'settings' == $object_type ) {
			// Nothing to save
			if ( empty( $meta ) ) {
				return true;
			}

			return $this->save_setting( pods_v( 'option_id', $data ), $meta, false );
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
	 * @since 2.0
	 */
	public function delete_wp_object( $object_type, $id, $force_delete = true ) {
		if ( in_array( $object_type, array( 'post_type', 'media' ) ) ) {
			$object_type = 'post';
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
	 * Save an object's meta
	 *
	 * @param string    $meta_type      Object Type
	 * @param int       $id             Object ID
	 * @param array     $object_meta    All meta to be saved (set value to null to delete)
	 * @param bool      $strict         Whether to delete previously saved meta not in $object_meta
	 * @param array     $fields         (optional) The array of fields and their options, for further processing with
	 *
	 * @return int Object ID
	 *
	 * @since 3.0
	 */
	public function save_meta( $meta_type, $id, $object_meta = array(), $strict = false, $fields = array() ) {

		if ( array() === $object_meta && ! $strict ) {
			return $id;
		}

		$simple_tableless_objects = Pods_Form::simple_tableless_objects();

		$id = (int) $id;

		$conflicted = pods_no_conflict_check( $meta_type );

		if ( ! $conflicted ) {
			pods_no_conflict_on( $meta_type );
		}

		if ( ! is_array( $object_meta ) ) {
			$object_meta = array();
		}

		$all_meta = get_metadata( $meta_type, $id );

		foreach ( $all_meta as $k => $value ) {
			if ( is_array( $value ) && 1 == count( $value ) ) {
				$all_meta[ $k ] = current( $value );
			}
		}

		foreach ( $object_meta as $meta_key => $meta_value ) {
			if ( null === $meta_value || ( $strict && '' === $object_meta[ $meta_key ] ) ) {
				$old_meta_value = '';

				if ( isset( $all_meta[ $meta_key ] ) ) {
					$old_meta_value = $all_meta[ $meta_key ];
				}

				delete_metadata( $meta_type, $id, $meta_key, $old_meta_value );
			} else {
				$simple     = false;
				$pick_field = false;

				if ( isset( $fields[ $meta_key ] ) ) {
					$field_data = $fields[ $meta_key ];

					if ( 'pick' == $field_data['type'] ) {
						$pick_field = true;

						$pick_object = pods_var( 'pick_object', $field_data );

						if ( $pick_object && in_array( $pick_object, $simple_tableless_objects ) ) {
							$simple = true;
						}
					}
				}

				if ( $simple || $pick_field ) {
					delete_post_meta( $id, $meta_key );
					delete_post_meta( $id, '_pods_' . $meta_key );

					if ( ! is_array( $meta_value ) ) {
						$meta_value = array( $meta_value );
					}

					if ( 1 < count( $meta_value ) ) {
						add_metadata( $meta_type, $id, '_pods_' . $meta_key, $meta_value );
					}

					foreach ( $meta_value as $value ) {
						add_metadata( $meta_type, $id, $meta_key, $value );
					}
				} else {
					update_metadata( $meta_type, $id, $meta_key, $meta_value );
				}
			}
		}

		if ( $strict ) {
			foreach ( $all_meta as $meta_key => $meta_value ) {
				if ( ! isset( $object_meta[ $meta_key ] ) ) {
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
	 * @param bool  $sanitized (optional) Will unsanitize the data, should be passed if the data is sanitized before sending.
	 * @param array $fields    (optional) The array of fields and their options, for further processing with
	 *
	 * @return mixed|void
	 *
	 * @since 2.0
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
			wp_update_post( $post_data );
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
	 * @return int Post ID
	 *
	 * @since 2.0
	 */
	public function save_post_meta( $id, $post_meta = null, $strict = false, $fields = array() ) {

		return $this->save_meta( 'post', $id, $post_meta, $strict, $fields );

	}

	/**
	 * Save a user and it's meta
	 *
	 * @param array $user_data All user data to be saved (using wp_insert_user / wp_update_user)
	 * @param array $user_meta (optional) All meta to be saved (set value to null to delete)
	 * @param bool  $strict    (optional) Whether to delete previously saved meta not in $user_meta
	 * @param bool  $sanitized (optional) Will unsanitize the data, should be passed if the data is sanitized before sending.
	 * @param array $fields    (optional) The array of fields and their options, for further processing with
	 *
	 * @return int Returns user id on success
	 *
	 * @since 2.0
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
	 * @since 2.0
	 *
	 */
	public function save_user_meta( $id, $user_meta = null, $strict = false, $fields = array() ) {

		return $this->save_meta( 'user', $id, $user_meta, $strict, $fields );

	}

	/**
	 * Save a comment and it's meta
	 *
	 * @param array $comment_data All comment data to be saved (using wp_insert_comment / wp_update_comment)
	 * @param array $comment_meta (optional) All meta to be saved (set value to null to delete)
	 * @param bool  $strict       (optional) Whether to delete previously saved meta not in $comment_meta
	 * @param bool  $sanitized    (optional) Will unsanitize the data, should be passed if the data is sanitized before sending.
	 * @param array $fields       (optional) The array of fields and their options, for further processing with
	 *
	 * @return int Comment ID
	 *
	 * @since 2.0
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
	 * @since 2.0
	 */
	public function save_comment_meta( $id, $comment_meta = null, $strict = false, $fields = array() ) {

		return $this->save_meta( 'comment', $id, $comment_meta, $strict, $fields );

	}

	/**
	 * Save a taxonomy's term
	 *
	 * @param int    $term_ID   Term ID, leave empty to add
	 * @param string $term      Term name
	 * @param string $taxonomy  Taxonomy name
	 * @param array  $term_data All term data to be saved (using wp_insert_term / wp_update_term)
	 * @param array  $term_meta (optional) All meta to be saved (set value to null to delete)
	 * @param bool   $strict    (optional) Whether to delete previously saved meta not in $term_meta
	 * @param bool   $sanitized (optional) Will unsanitize the data, should be passed if the data is sanitized before sending.
	 * @param array  $fields    (optional) The array of fields and their options, for further processing with
	 *
	 * @return int Term ID
	 *
	 * @since 2.0
	 */
	public function save_term( $term_ID, $term, $taxonomy, $term_data, $term_meta = null, $strict = false, $sanitized = false, $fields = array() ) {
		$conflicted = pods_no_conflict_check( 'taxonomy' );

		if ( ! $conflicted ) {
			pods_no_conflict_on( 'taxonomy' );
		}

		if ( ! is_array( $term_data ) ) {
			$term_data = array();
		}

		$term_ID = (int) $term_ID;

		if ( $sanitized ) {
			$term      = pods_unsanitize( $term );
			$taxonomy  = pods_unsanitize( $taxonomy );
			$term_data = pods_unsanitize( $term_data );
		}

		if ( empty( $term_ID ) ) {
			$term_ID = wp_insert_term( $term, $taxonomy, $term_data );
		} else {
			if ( 0 < strlen( $term ) ) {
				$term_data['term'] = $term;
			}

			if ( empty( $term_data ) ) {
				if ( ! $conflicted ) {
					pods_no_conflict_off( 'taxonomy' );
				}

				return pods_error( __( 'Taxonomy term data is required but is either invalid or empty', 'pods' ), $this );
			}

			wp_update_term( $term_ID, $taxonomy, $term_data );
		}

		if ( is_wp_error( $term_ID ) ) {
			if ( ! $conflicted ) {
				pods_no_conflict_off( 'taxonomy' );
			}

			return pods_error( $term_ID->get_error_message(), $this );
		} elseif ( is_array( $term_ID ) ) {
			$term_ID = $term_ID['term_id'];
		}

		$this->save_term_meta( $term_ID, $term_meta, $strict, $fields );

		if ( ! $conflicted ) {
			pods_no_conflict_off( 'taxonomy' );
		}

		return $term_ID;
	}

	/**
	 * Save a term meta
	 *
	 * @param int   $id        Term ID
	 * @param array $term_meta (optional) All meta to be saved (set value to null to delete)
	 * @param bool  $strict    (optional) Whether to delete previously saved meta not in $term_meta
	 * @param array $fields    (optional) The array of fields and their options, for further processing with
	 *
	 * @return int Term ID
	 *
	 * @since 3.0
	 */
	public function save_term_meta( $id, $comment_meta = null, $strict = false, $fields = array() ) {

		return $this->save_meta( 'term', $id, $comment_meta, $strict, $fields );

	}

	/**
	 * Save a set of options
	 *
	 * @param string $setting     Setting group name
	 * @param array  $option_data All option data to be saved
	 * @param bool   $sanitized   (optional) Will unsanitize the data, should be passed if the data is sanitized before sending.
	 *
	 * @return bool
	 *
	 * @since 2.3
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
	 * @since 2.0
	 */
	public function rename_wp_object_type( $object_type, $old_name, $new_name ) {
		/**
		 * @var $wpdb wpdb
		 */
		global $wpdb;

		if ( 'post_type' == $object_type ) {
			$object_type = 'post';
		}

		if ( 'post' == $object_type ) {
			pods_query( "UPDATE `{$wpdb->posts}` SET `post_type` = %s WHERE `post_type` = %s",
				array(
					$new_name,
					$old_name
				) );
		} elseif ( 'taxonomy' == $object_type ) {
			pods_query( "UPDATE `{$wpdb->term_taxonomy}` SET `taxonomy` = %s WHERE `taxonomy` = %s",
				array(
					$new_name,
					$old_name
				) );
		} elseif ( 'comment' == $object_type ) {
			pods_query( "UPDATE `{$wpdb->comments}` SET `comment_type` = %s WHERE `comment_type` = %s",
				array(
					$new_name,
					$old_name
				) );
		} elseif ( 'settings' == $object_type ) {
			pods_query( "UPDATE `{$wpdb->options}` SET `option_name` = REPLACE( `option_name`, %s, %s ) WHERE `option_name` LIKE '" . pods_sanitize_like( $old_name ) . "_%'",
				array(
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
	 * @since 2.0
	 */
	public function get_wp_object_fields( $object = 'post_type', $pod = null, $refresh = false ) {

		$pod_name = pods_v( 'name', $pod, $object, true );

		if ( 'media' == $pod_name ) {
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

		if ( 'post_type' == $object ) {
			$fields = array(
				'ID'                    => array(
					'name'          => 'ID',
					'label'         => 'ID',
					'type'          => 'number',
					'alias'         => array( 'id' ),
					'number_format' => '9999.99'
				),
				'post_title'            => array(
					'name'                => 'post_title',
					'label'               => 'Title',
					'type'                => 'text',
					'alias'               => array( 'title', 'name' ),
					'display_filter'      => 'the_title',
					'display_filter_args' => array( 'post_ID' )
				),
				'post_content'          => array(
					'name'                      => 'post_content',
					'label'                     => 'Content',
					'type'                      => 'wysiwyg',
					'alias'                     => array( 'content' ),
					'wysiwyg_allowed_html_tags' => '',
					'display_filter'            => 'the_content',
					'pre_save'                  => 0
				),
				'post_excerpt'          => array(
					'name'                        => 'post_excerpt',
					'label'                       => 'Excerpt',
					'type'                        => 'paragraph',
					'alias'                       => array( 'excerpt' ),
					'paragraph_allow_html'        => 1,
					'paragraph_allowed_html_tags' => '',
					'display_filter'              => 'the_excerpt',
					'pre_save'                    => 0
				),
				'post_author'           => array(
					'name'               => 'post_author',
					'label'              => 'Author',
					'type'               => 'pick',
					'alias'              => array( 'author' ),
					'pick_object'        => 'user',
					'pick_format_type'   => 'single',
					'pick_format_single' => 'autocomplete',
					'default_value'      => '{@user.ID}'
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
					'default'     => $this->do_hook( 'default_status_' . $pod_name, pods_v( 'default_status', $pod, 'draft', true ), $pod ),
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
					'name'                        => 'post_content_filtered',
					'label'                       => 'Content (filtered)',
					'type'                        => 'paragraph',
					'alias'                       => array(),
					'hidden'                      => true,
					'paragraph_allow_html'        => 1,
					'paragraph_oembed'            => 1,
					'paragraph_wptexturize'       => 1,
					'paragraph_convert_chars'     => 1,
					'paragraph_wpautop'           => 1,
					'paragraph_allow_shortcode'   => 1,
					'paragraph_allowed_html_tags' => ''
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
					'name'          => 'menu_order',
					'label'         => 'Menu Order',
					'type'          => 'number',
					'alias'         => array(),
					'number_format' => '9999.99'
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
				)
			);

			if ( ! empty( $pod ) ) {
				$taxonomies = get_object_taxonomies( $pod_name, 'objects' );

				foreach ( $taxonomies as $taxonomy ) {
					$fields[$taxonomy->name] = array(
						'name'                 => $taxonomy->name,
						'label'                => $taxonomy->labels->name,
						'type'                 => 'taxonomy',
						'pick_object'          => 'taxonomy',
						'pick_val'             => $taxonomy->name,
						'alias'                => array(),
						'hidden'               => true,
						'taxonomy_format_type' => 'multi'
					);
				}
			}
		} elseif ( 'user' == $object ) {
			$fields = array(
	            'ID'             => array(
		            'name'          => 'ID',
		            'label'         => 'ID',
		            'type'          => 'number',
		            'alias'         => array( 'id' ),
		            'number_format' => '9999.99'
	            ),
				'user_login'      => array(
					'name'     => 'user_login',
					'label'    => 'Title',
					'type'     => 'text',
					'alias'    => array( 'login' ),
					'required' => 1
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
					'name'             => 'user_pass',
					'label'            => 'Password',
					'type'             => 'text',
					'alias'            => array( 'password', 'pass' ),
					'required'         => 1,
					'text_format_type' => 'password'
				),
				'user_email'      => array(
					'name'             => 'user_email',
					'label'            => 'E-mail',
					'type'             => 'text',
					'alias'            => array( 'email' ),
					'required'         => 1,
					'text_format_type' => 'email'
				),
				'user_url'        => array(
					'name'                => 'user_url',
					'label'               => 'URL',
					'type'                => 'text',
					'alias'               => array( 'url', 'website' ),
					'required'            => 0,
					'text_format_type'    => 'website',
					'text_format_website' => 'normal'
				),
				'user_registered' => array(
					'name'             => 'user_registered',
					'label'            => 'Registration Date',
					'type'             => 'date',
					'alias'            => array( 'created', 'date', 'registered' ),
					'date_format_type' => 'datetime'
				)
			);
		} elseif ( 'comment' == $object ) {
			$fields = array(
	            'comment_ID'       => array(
		            'name'          => 'comment_ID',
		            'label'         => 'ID',
		            'type'          => 'number',
		            'alias'         => array( 'id', 'ID', 'comment_id' ),
		            'number_format' => '9999.99'
	            ),
				'comment_content'  => array(
					'name'  => 'comment_content',
					'label' => 'Content',
					'type'  => 'wysiwyg',
					'alias' => array( 'content' )
				),
				'comment_approved' => array(
					'name'          => 'comment_approved',
					'label'         => 'Approved',
					'type'          => 'number',
					'alias'         => array( 'approved' ),
					'number_format' => '9999.99'
				),
				'comment_post_ID'  => array(
					'name'  => 'comment_post_ID',
					'label' => 'Post',
					'type'  => 'pick',
					'alias' => array( 'post', 'post_id' ),
					'data'  => array()
				),
				'user_id'          => array(
					'name'        => 'user_id',
					'label'       => 'Author',
					'type'        => 'pick',
					'alias'       => array( 'author' ),
					'pick_object' => 'user',
					'data'        => array()
				),
				'comment_date'     => array(
					'name'             => 'comment_date',
					'label'            => 'Date',
					'type'             => 'date',
					'alias'            => array( 'created', 'date' ),
					'date_format_type' => 'datetime'
				),
                'comment_author' => array(
                    'name' => 'comment_author',
                    'label' => 'Author',
                    'type' => 'text',
                    'alias' => array( 'author' )
                ),
                'comment_author_email' => array(
                    'name' => 'comment_author_email',
                    'label' => 'Author E-mail',
                    'type' => 'email',
                    'alias' => array( 'author_email' )
                ),
                'comment_author_url' => array(
                    'name' => 'comment_author_url',
                    'label' => 'Author URL',
                    'type' => 'text',
                    'alias' => array( 'author_url' )
                ),
                'comment_author_IP' => array(
                    'name' => 'comment_author_IP',
                    'label' => 'Author IP',
                    'type' => 'text',
                    'alias' => array( 'author_IP' )
                ),
                'comment_type' => array(
                    'name' => 'comment_type',
                    'label' => 'Type',
                    'type' => 'text',
                    'alias' => array( 'type' ),
                    'hidden' => true
                ),
                'comment_parent' => array(
                    'name' => 'comment_parent',
                    'label' => 'Parent',
                    'type' => 'pick',
                    'pick_object' => 'comment',
                    'pick_val' => '__current__',
                    'alias' => array( 'parent' ),
                    'data' => array(),
                    'hidden' => true
                )
			);
		} elseif ( 'taxonomy' == $object ) {
			$fields = array(
				'term_id'          => array(
					'name'          => 'term_id',
					'label'         => 'ID',
					'type'          => 'number',
					'alias'         => array( 'id', 'ID' ),
					'number_format' => '9999.99'
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
					'type'  => 'pick',
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
					'name'          => 'term_taxonomy_id',
					'label'         => 'Term Taxonomy ID',
					'type'          => 'number',
					'alias'         => array(),
					'hidden'        => true,
					'number_format' => '9999.99'
				),
				'term_group'       => array(
					'name'          => 'term_group',
					'label'         => 'Term Group',
					'type'          => 'number',
					'alias'         => array( 'group' ),
					'hidden'        => true,
					'number_format' => '9999.99'
				),
				'count'            => array(
					'name'          => 'count',
					'label'         => 'Count',
					'type'          => 'number',
					'alias'         => array(),
					'hidden'        => true,
					'number_format' => '9999.99'
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

			$fields[$field] = pods_object_field( $options );
		}

		if ( did_action( 'init' ) && pods_api_cache() ) {
			pods_transient_set( trim( 'pods_api_object_fields_' . $object . $pod_name . '_', '_' ), $fields );
		}

		return $fields;
	}

	/**
	 *
	 * @see   Pods_API::save_pod
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
	 * @since 2.0
	 */
	public function add_pod( $params ) {

		$defaults = array(
			'create_extend'           => 'create',
			'create_pod_type'         => 'post_type',
			'create_name'             => '',
			'create_label_singular'   => '',
			'create_label_plural'     => '',
			'create_storage'          => 'meta',
			'create_storage_taxonomy' => 'none',
			'create_setting_name'     => '',
			'create_label_title'      => '',
			'create_label_menu'       => '',
			'create_menu_location'    => 'settings',
			'extend_pod_type'         => 'post_type',
			'extend_post_type'        => 'post',
			'extend_taxonomy'         => 'category',
			'extend_table'            => '',
			'extend_storage_taxonomy' => 'table',
			'extend_storage'          => 'meta'
		);

		$params = (object) array_merge( $defaults, (array) $params );

		if ( empty( $params->create_extend ) || ! in_array( $params->create_extend, array( 'create', 'extend' ) ) ) {
			return pods_error( __( 'Please choose whether to Create or Extend a Content Type', 'pods' ), $this );
		}

		$pod_params = array(
			'name'    => '',
			'label'   => '',
			'type'    => '',
			'storage' => 'table',
			'object'  => ''
		);

		if ( 'create' == $params->create_extend ) {
			$label = ucwords( str_replace( '_', ' ', $params->create_name ) );

			if ( ! empty( $params->create_label_singular ) ) {
				$label = $params->create_label_singular;
			}

			$pod_params = array(
				'name'           => $params->create_name,
				'label'          => ( ! empty( $params->create_label_plural ) ? $params->create_label_plural : $label ),
				'type'           => $params->create_pod_type,
				'label_singular' => ( ! empty( $params->create_label_singular ) ? $params->create_label_singular : $pod_params['label'] ),
				'public'         => 1,
				'show_ui'        => 1
			);

			// Auto-generate name if not provided
			if ( empty( $pod_params['name'] ) && ! empty( $pod_params['label_singular'] ) ) {
				$pod_params['name'] = pods_clean_name( $pod_params['label_singular'] );
			}

			if ( 'post_type' == $pod_params['type'] ) {
				if ( empty( $pod_params['name'] ) ) {
					return pods_error( 'Please enter a Name for this Pod', $this );
				}

				$pod_params['storage'] = $params->create_storage;

				if ( pods_tableless() ) {
					$pod_params['storage'] = 'meta';
				}
			} elseif ( 'taxonomy' == $pod_params['type'] ) {
				if ( empty( $pod_params['name'] ) ) {
					return pods_error( 'Please enter a Name for this Pod', $this );
				}

				$pod_params['storage'] = $params->create_storage_taxonomy;

				if ( pods_tableless() ) {
					$pod_params['storage'] = 'none';
				}
			} elseif ( 'pod' == $pod_params['type'] ) {
				if ( empty( $pod_params['name'] ) ) {
					return pods_error( 'Please enter a Name for this Pod', $this );
				}

				if ( pods_tableless() ) {
					$pod_params['type']    = 'post_type';
					$pod_params['storage'] = 'meta';
				}
			} elseif ( 'settings' == $pod_params['type'] ) {
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
					return pods_error( 'Please enter a Name for this Pod', $this );
				}
			}
		} elseif ( 'extend' == $params->create_extend ) {
			$pod_params['type'] = $params->extend_pod_type;

			if ( 'post_type' == $pod_params['type'] ) {
				$pod_params['storage'] = $params->extend_storage;

				if ( pods_tableless() ) {
					$pod_params['storage'] = 'meta';
				}

				$pod_params['name'] = $params->extend_post_type;
			} elseif ( 'taxonomy' == $pod_params['type'] ) {
				$pod_params['storage'] = $params->extend_storage_taxonomy;

				if ( pods_tableless() ) {
					$pod_params['storage'] = 'none';
				}

				$pod_params['name'] = $params->extend_taxonomy;
			} elseif ( 'table' == $pod_params['type'] ) {
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
			if ( 'post_type' == $pod_params['type'] ) {
				$check = get_post_type_object( $pod_params['name'] );

				if ( ! empty( $check ) ) {
					return pods_error( sprintf( __( 'Post Type %s already exists, try extending it instead', 'pods' ), $pod_params['name'] ), $this );
				}

				$pod_params['supports_title']  = 1;
				$pod_params['supports_editor'] = 1;
			} elseif ( 'taxonomy' == $pod_params['type'] ) {
				$check = get_taxonomy( $pod_params['name'] );

				if ( ! empty( $check ) ) {
					return pods_error( sprintf( __( 'Taxonomy %s already exists, try extending it instead', 'pods' ), $pod_params['name'] ), $this );
				}
			}
		}

		if ( ! empty( $pod_params ) ) {
			$pod = pods_object_pod( '_pods_pod' );

			return $pod->save( $pod_params );
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
	 * @param bool     $sanitized (deprecated)
	 * @param bool|int $db        (optional) Whether to save into the DB or just return Pod array.
	 *
	 * @return int Pod ID
	 * @since 1.7.9
	 */
	public function save_pod( $params, $sanitized = false, $db = true ) {

		$load_params = (object) $params;

		if ( isset( $load_params->id ) && 0 < $load_params->id && isset( $load_params->name ) ) {
			unset( $load_params->name );
		}

		if ( isset( $load_params->old_name ) ) {
			$load_params->name = $load_params->old_name;
		}

		$pod = $this->load_pod( $load_params, __METHOD__ );

		$params = (object) $params;

		if ( ! isset( $params->db ) ) {
			$params->db = $db;
		}

		if ( empty( $pod ) ) {
			$pod = pods_object_pod();
		}

		$id = $pod->save( $params );

		if ( $db ) {
			return $id;
		}

		return $pod;

	}

	/**
	 * Add or edit a Pod Group
	 *
	 * $params['id'] int The Group ID
	 * $params['name'] string The Group name
	 * $params['label'] string The Group label
	 * $params['pod'] string The Pod name
	 * $params['pod_id'] string The Pod ID
	 *
	 * @param array    $params An associative array of parameters
	 * @param bool|int $db     (optional) Whether to save into the DB or just return Pod array.
	 *
	 * @return int Pod ID
	 * @since 1.7.9
	 */
	public function save_pod_group( $params, $db = true ) {

		$load_params = (object) $params;

		if ( isset( $load_params->id ) && 0 < $load_params->id ) {
			$group = pods_object_group( null, $load_params->id );
		} else {
			$pod = false;

			if ( isset( $load_params->pod_id ) && ! empty( $load_params->pod_id ) ) {
				$pod = $this->load_pod( array( 'id' => $load_params->pod_id ), __METHOD__ );
			} elseif ( isset( $load_params->pod ) && ! empty( $load_params->pod ) ) {
				$pod = $this->load_pod( array( 'name' => $load_params->pod ), __METHOD__ );
			}

			if ( empty( $pod ) ) {
				return pods_error( __( 'Pod not found', 'pods' ), $this );
			}

			if ( isset( $load_params->old_name ) ) {
				$group = pods_object_group( $load_params->old_name, 0, false, $pod['id'] );
			} elseif ( isset( $load_params->name ) ) {
				$group = pods_object_group( $load_params->name, 0, false, $pod['id'] );
			} else {
				$group = pods_object_group( null, 0, false, $pod['id'] );
			}
		}

		$id = $group->save( $params );

		if ( $db ) {
			return $id;
		}

		return $group;

	}

	/**
	 * Add or edit a field within a Pod
	 *
	 * $params['id'] int Field ID (id OR pod_id+pod+name required)
	 * $params['pod_id'] int Pod ID (id OR pod_id+pod+name required)
	 * $params['pod'] string Pod name (id OR pod_id+pod+name required)
	 * $params['name'] string Field name (id OR pod_id+pod+name required)
	 * $params['label'] string (optional) Field label
	 * $params['type'] string (optional) Field type (avatar, boolean, code, color, currency, date, datetime, email, file, number, paragraph, password, phone, pick, slug, text, time, website, wysiwyg)
	 * $params['pick_object'] string (optional) Related Object (for relationships)
	 * $params['pick_val'] string (optional) Related Object name (for relationships)
	 * $params['sister_id'] int (optional) Related Field ID (for bidirectional relationships)
	 * $params['weight'] int (optional) Order in which the field appears
	 * $params['options'] array (optional) Options
	 *
	 * @param array    $params          An associative array of parameters
	 * @param bool     $table_operation (optional) Whether or not to handle table operations
	 * @param bool     $sanitized       (deprecated)
	 * @param bool|int $db              (optional) Whether to save into the DB or just return field array.
	 *
	 * @return int|array The field ID or field array (if !$db)
	 * @since 1.7.9
	 */
	public function save_field( $params, $table_operation = true, $sanitized = false, $db = true ) {

		$params = (object) $params;

		if ( ! is_bool( $db ) ) {
			$params->pod_id = (int) $db;
		}

		$load_params = array(
			'output' => OBJECT
		);

		if ( isset( $params->id ) ) {
			$load_params['id'] = $params->id;
		} elseif ( isset( $params->name ) && ( isset( $params->pod_id ) || isset( $params->pod ) ) ) {
			$load_params['name'] = $params->name;

			if ( isset( $params->pod_id ) ) {
				$load_params['pod_id'] = $params->pod_id;
			} else {
				$load_params['pod'] = $params->pod;
			}
		} else {
			// @todo Throw error, field not found
		}

		$field = $this->load_field( $load_params, __METHOD__ );

		if ( empty( $field ) || ! $field->is_valid() ) {
			$field = pods_object_field();
		}

		if ( ! isset( $params->table_operation ) ) {
			$params->table_operation = $table_operation;
		}

		if ( ! isset( $params->db ) ) {
			$params->db = $db;
		}

		$id = $field->save( $params, null, true );

		if ( true === $db ) {
			return $id;
		}

		return $field;

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
	 * @param bool         $sanitized (optional) Decides whether the params have been sanitized before being passed, will sanitize them if false.
	 *
	 * @return int The Object ID
	 * @since 2.0
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
			'id'   => 0,
			'name' => $params->name,
			'type' => $params->type,
			'code' => ''
		);

		// Setup options
		$options = get_object_vars( $params );

		if ( isset( $options['method'] ) ) {
			unset( $options['method'] );
		}

		// Deprecated
		if ( isset( $options['options'] ) ) {
			$options = array_merge( $options, $options['options'] );

			unset( $options['options'] );
		}

		$object = array_merge( $object, $options );

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

		if ( null !== pods_v( 'status', $object, null, true ) ) {
			$post_data['post_status'] = pods_v( 'status', $object, null, true );
		}

		remove_filter( 'content_save_pre', 'balanceTags', 50 );

		$post_data = pods_sanitize( $post_data );

		$post_meta = array_diff_key( $object, array( 'id' => '', 'name' => '', 'code' => '', 'type' => '' ) );

		$params->id = $this->save_post( $post_data, $post_meta, true, true );

		pods_transient_clear( 'pods_objects_' . $params->type );
		pods_transient_clear( 'pods_objects_' . $params->type . '_get' );

		return $params->id;

	}

	/**
	 * @see   Pods_API::save_object
	 *
	 * Add or edit a Pod Template
	 *
	 * $params['id'] int The template ID
	 * $params['name'] string The template name
	 * $params['code'] string The template code
	 *
	 * @param array|object $params    An associative array of parameters
	 * @param bool         $sanitized (optional) Decides wether the params have been sanitized before being passed, will sanitize them if false.
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
	 * @see   Pods_API::save_object
	 *
	 * Add or edit a Pod Page
	 *
	 * $params['id'] int The page ID
	 * $params['name'] string The page URI
	 * $params['code'] string The page code
	 *
	 * @param array|object $params    An associative array of parameters
	 * @param bool         $sanitized (optional) Decides wether the params have been sanitized before being passed, will sanitize them if false.
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
	 * @see   Pods_API::save_object
	 *
	 * Add or edit a Pod Helper
	 *
	 * $params['id'] int The helper ID
	 * $params['name'] string The helper name
	 * $params['helper_type'] string The helper type ("pre_save", "display", etc)
	 * $params['code'] string The helper code
	 *
	 * @param array $params    An associative array of parameters
	 * @param bool  $sanitized (optional) Decides wether the params have been sanitized before being passed, will sanitize them if false.
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
	 * $params['id'] int The item ID
	 * $params['data'] array (optional) Associative array of field names + values
	 * $params['bypass_helpers'] bool Set to true to bypass running pre-save and post-save helpers
	 *
	 * @param array|object $params An associative array of parameters
	 *
	 * @return int The item ID
	 *
	 * @since 1.7.9
	 */
	public function save_pod_item( $params ) {

		global $wpdb;

		$params = (object) pods_str_replace( '@wp_', '{prefix}', $params );

		$tableless_field_types    = Pods_Form::tableless_field_types();
		$repeatable_field_types   = Pods_Form::repeatable_field_types();
		$block_field_types        = Pods_Form::block_field_types();
		$simple_tableless_objects = Pods_Form::simple_tableless_objects();

		// @deprecated 2.0
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
					pods_deprecated( 'Pods_API::save_pod_items', '2.0' );

					return $this->save_pod_items( $params, $params->data );
				}
			}
		}

		// @deprecated 2.0
		if ( isset( $params->tbl_row_id ) ) {
			pods_deprecated( '$params->id instead of $params->tbl_row_id', '2.0' );

			$params->id = $params->tbl_row_id;

			unset( $params->tbl_row_id );
		}

		// @deprecated 2.0
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

		/**
		 * Override $params['track_changed_fields']
		 *
		 * Use for globally setting field change tracking.
		 *
		 * @param bool
		 *
		 * @since 2.3.19
		 */
		$track_changed_fields = apply_filters( 'pods_api_save_pod_item_track_changed_fields_' . $params->pod, (boolean) $params->track_changed_fields, $params );
		$changed_fields       = array();

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
		$pod = $this->load_pod( array( 'id' => $params->pod_id, 'name' => $params->pod ) );

		if ( false === $pod ) {
			return pods_error( __( 'Pod not found', 'pods' ), $this );
		}

		$params->pod    = $pod['name'];
		$params->pod_id = $pod['id'];

		if ( 'settings' == $pod['type'] ) {
			$params->id = $pod['id'];
		}

		$fields = $pod->fields();

		$object_fields = array();

		$fields_active = array();
		$custom_data   = array();

		// Find the active fields (loop through $params->data to retain order)
		if ( ! empty( $params->data ) && is_array( $params->data ) ) {
			$custom_fields = array();

			foreach ( $params->data as $field => $value ) {
				if ( isset( $object_fields[$field] ) ) {
					if ( in_array( $object_fields[$field]['type'], $block_field_types ) ) {
						continue;
					}

					$object_fields[$field]['value'] = $value;
					$fields_active[]                = $field;
				} elseif ( isset( $fields[$field] ) ) {
					if ( in_array( $fields[$field]['type'], $block_field_types ) ) {
						continue;
					}

					if ( 'save' == $params->from || true === Pods_Form::permission( $fields[$field]['type'], $field, $fields[$field], $fields, $pod, $params->id, $params ) ) {
						$fields[$field]['value'] = $value;
						$fields_active[]         = $field;
					} elseif ( ! pods_has_permissions( $fields[$field] ) && pods_v( 'hidden', $fields[$field], false ) ) {
						$fields[$field]['value'] = $value;
						$fields_active[]         = $field;
					}
				} else {
					$found = false;

					foreach ( $object_fields as $object_field => $object_field_opt ) {
						if ( in_array( $field, $object_field_opt['alias'] ) ) {
							$found = true;

							if ( in_array( $object_fields[$object_field]['type'], $block_field_types ) ) {
								break;
							}

							$object_fields[$object_field]['value'] = $value;
							$fields_active[]                       = $object_field;

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
					$custom_data[$field] = $params->data[$field];
				}
			}

			if ( $pod[ 'type' ] === 'taxonomy' && isset( $params->data )  && !empty( $params->data ) ) {
				$term_data = $params->data;
			}

			unset( $params->data );
		}

		if ( 'pod' == $pod['type'] ) {
			if ( empty( $params->id ) && ! in_array( 'created', $fields_active ) && isset( $fields['created'] ) ) {
				$fields['created']['value'] = current_time( 'mysql' );
				$fields_active[]            = 'created';
			}

			if ( ! in_array( 'modified', $fields_active ) && isset( $fields['modified'] ) ) {
				$fields['modified']['value'] = current_time( 'mysql' );
				$fields_active[]             = 'modified';
			}

			if ( empty( $params->id ) && ! empty( $pod['pod_field_index'] ) && in_array( $pod['pod_field_index'], $fields_active ) && ! in_array( $pod['pod_field_slug'], $fields_active ) && isset( $fields[$pod['pod_field_slug']] ) ) {
				$fields[$pod['pod_field_slug']]['value'] = ''; // this will get picked up by slug pre_save method
				$fields_active[]                         = $pod['pod_field_slug'];
			}
		}

		// Handle default values
		if ( empty( $params->id ) ) {
			foreach ( $object_fields as $field => $field_data ) {
				if ( in_array( $field, $fields_active ) ) {
					continue;
				}

				$value = Pods_Form::default_value( pods_v( $field, 'post' ), $field_data['type'], $field, $field_data, $pod, $params->id );

				if ( null !== $value && '' !== $value && false !== $value ) {
					$object_fields[$field]['value'] = $value;
					$fields_active[]                = $field;
				}
			}

			foreach ( $fields as $field => $field_data ) {
				if ( in_array( $field, $fields_active ) ) {
					continue;
				}

				$value = Pods_Form::default_value( pods_v( $field, 'post' ), $field_data['type'], $field, $field_data, $pod, $params->id );

				if ( null !== $value && '' !== $value && false !== $value ) {
					$fields[$field]['value'] = $value;
					$fields_active[]         = $field;
				}
			}
		}

		$columns            =& $fields; // @deprecated 2.0
		$active_columns     =& $fields_active; // @deprecated 2.0
		$params->tbl_row_id =& $params->id; // @deprecated 2.0

		$pre_save_helpers = $post_save_helpers = array();

		if ( false === $bypass_helpers ) {
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

			if ( false === $bypass_helpers ) {
				// Plugin hooks

				/**
				 * Filter items of any Pod before saving.
				 *
				 * @param array $pieces {
				 * 		Field data to be saved
				 *
			     *		@type array $fields An array of fields in the Pod, 'value' key stores the *new* value if it's been set to save
				 * 		@type obj $params Parameters sent to Pods_API::save_pod_item
				 * 		@type array	$pod Information about the Pod, including id, name, label, etc
				 * 		@type array $fields_active An array of fields that are currently being saved. If saving via WP admin all fields will be included. If saving via API, only specified fields will be included. You must add additonal fields to thsi array before saving.
				 *      @type array $object_fields An array of the WP Object fields for the Pod (WP-based content types).
				 *      @type array $custom_fields An array of the custom fields (ones that aren't actually fields on the Pod) being saved, this cannot be changed (meta-based content types.)
 				 * 		@type array $custom_data An array of the custom field values being saved, you can change this to add other custom fields to the saving process (meta-based content types).
				 * }
				 * @param bool $is_new_item True if new item is being created, false if item already exists.
				 * @param int	$id ID of item being saved.
				 *
				 * @since unknown
				 *
				 * @return array Array to be saved.
				 */
				$hooked = apply_filters( 'pods_api_pre_save_pod_item', compact( $pieces ), $is_new_item, $params->id );

				if ( is_array( $hooked ) && ! empty( $hooked ) ) {
					extract( $hooked );
				}

				/**
				 * Filter items of a specific Pod, before saving.
				 *
				 * Parameters are the same as pods_api_pre_save_pod_item
				 *
				 * @since unknown
				 *
				 * @return array Array to be saved.
				 */
				$hooked = apply_filters( "pods_api_pre_save_pod_item_{$params->pod}", compact( $pieces ), $is_new_item, $params->id );

				if ( is_array( $hooked ) && ! empty( $hooked ) ) {
					extract( $hooked );
				}

				if ( $is_new_item ) {
					/**
					 * Filter a new item, of any Pod, before it is created.
					 *
					 * @param array $pieces See pods_api_pre_save_pod_item
					 *
					 * @since unknown
					 *
					 * @return array Array to be saved.
					 */
					$hooked = apply_filters( 'pods_api_pre_create_pod_item', compact( $pieces ) );

					if ( is_array( $hooked ) && ! empty( $hooked ) ) {
						extract( $hooked );
					}

					/**
					 * Filter a new item, of a specific Pod, before it is created.
					 *
					 * @param array $pieces See pods_api_pre_save_pod_item
					 *
					 * @since unknown
					 *
					 * @return array Array to be saved.
					 */
					$hooked = apply_filters( "pods_api_pre_create_pod_item_{$params->pod}", compact( $pieces ) );

					if ( is_array( $hooked ) && ! empty( $hooked ) ) {
						extract( $hooked );
					}
				} else {
					/**
					 * Filter an existing item, of any Pod, when it is being edited.
					 *
					 * @param array $pieces See pods_api_pre_save_pod_item
					 * @param int $id ID of item being saved.
					 *
					 * @since unknown
					 *
					 * @return array Array to be saved.
					 */
					$hooked = apply_filters( 'pods_api_pre_edit_pod_item', compact( $pieces ), $params->id );

					if ( is_array( $hooked ) && ! empty( $hooked ) ) {
						extract( $hooked );
					}

					/**
					 * Filter an existing item, of a specific Pod, when it is being edited.
					 *
					 * @param array $pieces See pods_api_pre_save_pod_item
					 * @param int $id ID of item being saved.
					 *
					 * @since unknown
					 *
					 * @return array Array to be saved.
					 */
					$hooked = apply_filters( "pods_api_pre_edit_pod_item_{$params->pod}", compact( $pieces ), $params->id );

					if ( is_array( $hooked ) && ! empty( $hooked ) ) {
						extract( $hooked );
					}
				}

				// Call any pre-save helpers (if not bypassed)
				if ( ! defined( 'PODS_DISABLE_EVAL' ) || ! PODS_DISABLE_EVAL ) {
					if ( ! empty( $pod ) && ( is_array( $pod ) || is_object( $pod ) ) ) {
						$helpers = array( 'pre_save_helpers', 'post_save_helpers' );

						foreach ( $helpers as $helper ) {
							if ( isset( $pod[$helper] ) && ! empty( $pod[$helper] ) ) {
								${$helper} = explode( ',', $pod[$helper] );
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
		}

		if ( $track_changed_fields ) {
			$changed_fields = $this->get_changed_fields( compact( $pieces ) );
		}

		$table_data = $table_formats = $update_values = $rel_fields = $rel_field_ids = array();

		$object_type = $pod['type'];

		$object_ID = 'ID';

		if ( 'comment' == $object_type ) {
			$object_ID = 'comment_ID';
		}

		$object_data = $object_meta = $post_term_data = array();

		if ( 'settings' == $object_type ) {
			$object_data['option_id'] = $pod['name'];
		} elseif ( ! empty( $params->id ) ) {
			$object_data[$object_ID] = $params->id;
		}

		$fields_active = array_unique( $fields_active );

		// Loop through each active field, validating and preparing the table data
		foreach ( $fields_active as $field ) {
			if ( isset( $object_fields[$field] ) ) {
				$field_data = $object_fields[$field];
			} elseif ( isset( $fields[$field] ) ) {
				$field_data = $fields[$field];
			} else {
				continue;
			}

			$value = $field_data['value'];
			$type  = $field_data['type'];

			// WPML AJAX compatibility
			if ( is_admin() && isset( $_GET['page'] ) && false !== strpos( $_GET['page'], '/menu/languages.php' ) && isset( $_POST['icl_ajx_action'] ) && isset( $_POST['_icl_nonce'] ) && false !== wp_verify_nonce( $_POST['_icl_nonce'], $_POST['icl_ajx_action'] . '_nonce' ) ) {
				$field_data['unique'] = $fields[$field]['unique'] = $fields[$field]['required'] = 0;
			} else {
				// Validate value
				$validate = $this->handle_field_validation( $value, $field, $object_fields, $fields, $pod, $params );

				if ( false === $validate ) {
					$validate = sprintf( __( 'There was an issue validating the field %s', 'pods' ), $field_data['label'] );
				} elseif ( true !== $validate ) {
					$validate = (array) $validate;
				}

				if ( ! is_bool( $validate ) && ! empty( $validate ) ) {
					return pods_error( $validate, $this );
				}
			}

			$value = Pods_Form::pre_save( $field_data['type'], $value, $params->id, $field, $field_data, array_merge( $fields, $object_fields ), $pod, $params );

			$field_data['value'] = $value;

			if ( isset( $object_fields[$field] ) ) {
				if ( 'taxonomy' == $object_fields[$field]['type'] ) {
					$post_term_data[$field] = $value;
				} else {
					$object_data[$field] = $value;
				}
			} else {
				$simple = ( 'pick' == $type && in_array( pods_v( 'pick_object', $field_data ), $simple_tableless_objects ) );
				$simple = (boolean) $this->do_hook( 'tableless_custom', $simple, $field_data, $field, $fields, $pod, $params );

				// Handle Simple Relationships
				if ( $simple ) {
					if ( ! is_array( $value ) ) {
						$value = explode( ',', $value );
					}

					$pick_limit = (int) pods_v( 'pick_limit', $field_data, 0 );

					if ( 'single' == pods_v( 'pick_format_type', $field_data ) ) {
						$pick_limit = 1;
					}

					if ( 'custom-simple' == pods_v( 'pick_object', $field_data ) ) {
						$custom = pods_v( 'pick_custom', $field_data, '' );

						$custom = apply_filters( 'pods_form_ui_field_pick_custom_values', $custom, $field_data['name'], $value, $field_data, $pod, $params->id );

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

										$custom_label[0] = trim( (string) $custom_label[0] );
										$custom_label[1] = trim( (string) $custom_label[1] );
										$custom_values[$custom_label[0]] = $custom_label[1];
									}
								}
							} else {
								$custom_values = $custom;
							}

							$values = array();

							foreach ( $value as $k => $v ) {
								$v = pods_unsanitize( $v );

								if ( isset( $custom_values[$v] ) ) {
									$values[$k] = $v;
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
						// If there's just one item, don't save as an array, save the string
						if ( 1 == $pick_limit || 1 == count( $value ) ) {
							$value = implode( '', $value );
						} // If storage is set to table, json encode, otherwise WP will serialize automatically
						elseif ( 'table' == pods_v( 'storage', $pod ) ) {
							$value = version_compare( PHP_VERSION, '5.4.0', '>=' ) ? json_encode( $value, JSON_UNESCAPED_UNICODE ) : json_encode( $value );
						}
					}
				}

				// Prepare all table / meta data
				if ( ! in_array( $type, $tableless_field_types ) || $simple ) {
					if ( in_array( $type, $repeatable_field_types ) && 1 == pods_v( $type . '_repeatable', $field_data, 0 ) ) {
						// Don't save an empty array, just make it an empty string
						if ( empty( $value ) ) {
							$value = '';
						} elseif ( is_array( $value ) ) {
							// If there's just one item, don't save as an array, save the string
							if ( 1 == count( $value ) ) {
								$value = implode( '', $value );
							} // If storage is set to table, json encode, otherwise WP will serialize automatically
							elseif ( 'table' == pods_v( 'storage', $pod ) ) {
								$value = version_compare( PHP_VERSION, '5.4.0', '>=' ) ? json_encode( $value, JSON_UNESCAPED_UNICODE ) : json_encode( $value );
							}
						}
					}

					$table_data[$field] = str_replace( array( '{prefix}', '@wp_' ), array( '{/prefix/}', '{prefix}' ), $value ); // Fix for pods_query
					$table_formats[]    = Pods_Form::prepare( $type, $field_data );

					$object_meta[$field] = $value;
				} // Store relational field data to be looped through later
				else {
					// Convert values from a comma-separated string into an array
					if ( ! is_array( $value ) ) {
						$value = explode( ',', $value );
					}

					$rel_fields[$type][$field] = $value;
					$rel_field_ids[]           = $field_data['id'];
				}
			}
		}

		if ( 'post_type' == $pod['type'] ) {
			$post_type = $pod['name'];

			if ( ! empty( $pod['object'] ) ) {
				$post_type = $pod['object'];
			}

			$object_data['post_type'] = $post_type;
		}

		if ( ( 'meta' == $pod['storage'] || 'settings' == $pod['type'] ) && ! in_array( $pod['type'], array( 'pod', 'table', '' ) ) ) {
			if ( $allow_custom_fields && ! empty( $custom_data ) ) {
				$object_meta = array_merge( $custom_data, $object_meta );
			}

			$fields_to_send = array_flip( array_keys( $object_meta ) );

			foreach ( $fields_to_send as $field => $field_data ) {
				if ( isset( $object_fields[$field] ) ) {
					$field_data = $object_fields[$field];
				} elseif ( isset( $fields[$field] ) ) {
					$field_data = $fields[$field];
				} else {
					unset( $fields_to_send[$field] );
				}

				$fields_to_send[$field] = $field_data;
			}

			if ( 'taxonomy' == $pod['type'] ) {
				$term = pods_v( $object_fields['name']['name'], $object_data, '', true );

				if ( !isset( $term_data ) ) {
					$term_data = array();
				}

				if ( empty( $params->id ) || ! empty( $term_data ) ) {
					$taxonomy = $pod['name'];

					if ( ! empty( $pod['object'] ) ) {
						$taxonomy = $pod['object'];
					}

					$params->id = $this->save_term( $params->id, $term, $taxonomy, $term_data, $object_meta, false, true, $fields_to_send );
				}
			} else {
				$params->id = $this->save_wp_object( $object_type, $object_data, $object_meta, false, true, $fields_to_send );
			}

			if ( ! empty( $params->id ) && 'settings' == $object_type ) {
				$params->id = $pod['id'];
			}
		} else {
			if ( ! in_array( $pod['type'], array( 'taxonomy', 'pod', 'table', '' ) ) ) {
				$params->id = $this->save_wp_object( $object_type, $object_data, array(), false, true );
			} elseif ( 'taxonomy' == $pod['type'] ) {
				$term = pods_v( 'name', $object_data, '', true );

				if ( !isset( $term_data ) ) {
					$term_data = array();
				}

				if ( empty( $params->id ) || ! empty( $term_data ) ) {
					$taxonomy = $pod['name'];

					if ( ! empty( $pod['object'] ) ) {
						$taxonomy = $pod['object'];
					}

					$params->id = $this->save_term( $params->id, $term, $taxonomy, $term_data, array(), false, true );
				}
			}

			if ( 'table' == $pod['storage'] ) {
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
		}

		$params->id = (int) $params->id;

		// Save terms for taxonomies associated to a post type
		if ( 0 < $params->id && 'post_type' == $pod['type'] && ! empty( $post_term_data ) ) {
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
					$pick_val = pods_v( 'pick_val', $fields[$field] );

					if ( 'table' == pods_v( 'pick_object', $fields[$field] ) ) {
						$pick_val = pods_v( 'pick_table', $fields[$field], $pick_val, true );
					}

					if ( '__current__' == $pick_val ) {
						if ( is_object( $pod ) ) {
							$pick_val = $pod->pod;
						} elseif ( is_array( $pod ) || is_object( $pod ) ) {
							$pick_val = $pod['name'];
						} elseif ( 0 < strlen( $pod ) ) {
							$pick_val = $pod;
						}
					}

					$fields[$field]['table_info'] = pods_api()->get_table_info( pods_v( 'pick_object', $fields[$field] ), $pick_val, null, null, $fields[$field] );

					if ( isset( $fields[$field]['table_info']['pod'] ) && ! empty( $fields[$field]['table_info']['pod'] ) && isset( $fields[$field]['table_info']['pod']['name'] ) ) {
						$search_data = pods( $fields[$field]['table_info']['pod']['name'] );

						$data_mode = 'pods';
					} else {
						$search_data = pods_data();
						$search_data->table( $fields[$field]['table_info'] );

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

					$related_limit = (int) pods_v( $type . '_limit', $fields[$field], 0 );

					if ( 'single' == pods_v( $type . '_format_type', $fields[$field] ) ) {
						$related_limit = 1;
					}

					// Enforce integers / unique values for IDs
					$value_ids = array();

					$is_file_field = in_array( $type, Pods_Form::file_field_types() );
					$is_taggable   = ( in_array( $type, Pods_Form::tableless_field_types() ) && 1 == pods_v( $type . '_taggable', $fields[$field] ) );

					// @todo Handle simple relationships eventually
					foreach ( $values as $v ) {
						if ( ! empty( $v ) ) {
							if ( ! is_array( $v ) ) {
								if ( ! preg_match( '/[^0-9]*/', $v ) ) {
									$v = (int) $v;
								} // File handling
								elseif ( $is_file_field ) {
									// Get ID from GUID
									$v = pods_image_id_from_field( $v );

									// If file not found, add it
									if ( empty( $v ) ) {
										$v = pods_attachment_import( $v );
									}
								} // Reference by slug
								else {
									$v_data = false;

									if ( false !== $find_rel_params ) {
										$rel_params          = $find_rel_params;
										$rel_params['where'] = $wpdb->prepare( $rel_params['where'], array( $v, $v ) );

										$search_data->select( $rel_params );

										$v_data = $search_data->fetch( $v );
									}

									if ( ! empty( $v_data ) && isset( $v_data[$search_data->field_id] ) ) {
										$v = (int) $v_data[$search_data->field_id];
									} // Allow tagging for Pods objects
									elseif ( $is_taggable && 'pods' == $data_mode ) {
										$tag_data = array(
											$search_data->field_index => $v
										);

										if ( 'post_type' == $search_data->pod_data['type'] ) {
											$tag_data['post_status'] = 'publish';
										}

										/**
										 * Filter for changing tag before adding new item.
										 *
										 * @param array  $tag_data    Fields for creating new item.
										 * @param int    $v           Field ID of tag.
										 * @param obj    $search_data Search object for tag.
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

					// Limit values
					if ( 0 < $related_limit && ! empty( $value_ids ) ) {
						$value_ids = array_slice( $value_ids, 0, $related_limit );
					}

					// Get current values
					if ( 'pick' == $type && isset( Pods_Field_Pick::$related_data[$fields[$field]['id']] ) && isset( Pods_Field_Pick::$related_data[$fields[$field]['id']]['current_ids'] ) ) {
						$related_ids = Pods_Field_Pick::$related_data[$fields[$field]['id']]['current_ids'];
					} else {
						$related_ids = $this->lookup_related_items( $fields[$field]['id'], $pod['id'], $params->id, $fields[$field], $pod );
					}

					// Get ids to remove
					$remove_ids = array_diff( $related_ids, $value_ids );

					// Delete relationships
					if ( ! empty( $remove_ids ) ) {
						$this->delete_relationships( $params->id, $remove_ids, $pod, $fields[$field] );
					}

					// Save relationships
					if ( ! empty( $value_ids ) ) {
						$this->save_relationships( $params->id, $value_ids, $pod, $fields[$field] );
					}

					// Run save function for field type (where needed)
					Pods_Form::save( $type, $values, $params->id, $field, $fields[$field], array_merge( $fields, $object_fields ), $pod, $params );
				}

				// Unset data no longer needed
				if ( 'pick' == $type ) {
					foreach ( $data as $field => $values ) {
						if ( isset( Pods_Field_Pick::$related_data[$fields[$field]['id']] ) ) {
							unset( Pods_Field_Pick::$related_data[Pods_Field_Pick::$related_data[$fields[$field]['id']]['related_field']['id']] );
							unset( Pods_Field_Pick::$related_data[$fields[$field]['id']] );
						}
					}
				}
			}
		}

		if ( ! $no_conflict ) {
			pods_no_conflict_off( $pod['type'] );
		}

		if ( false === $bypass_helpers ) {
			$pieces = array(
				'fields',
				'params',
				'pod',
				'fields_active',
				'object_fields',
				'custom_fields',
				'custom_data'
			);

			$pieces = compact( $pieces );

			// Plugin hooks
			/**
			 * Runs after an item of any Pod is updated
			 *
			 * @param array $pieces {
			 * 		Field data that was saved
			 *
			 *		@type array $fields An array of fields in the Pod, 'value' key stores the *new* value if it's been set to save
			 * 		@type obj $params Parameters sent to Pods_API::save_pod_item
			 * 		@type array	$pod Information about the Pod, including id, name, label, etc
			 * 		@type array $fields_active An array of fields that are currently being saved. If saving via WP admin all fields will be included. If saving via API, only specified fields will be included. You must add additonal fields to thsi array before saving.
			 *      @type array $object_fields An array of the WP Object fields for the Pod (WP-based content types).
			 *      @type array $custom_fields An array of the custom fields (ones that aren't actually fields on the Pod) being saved, this cannot be changed (meta-based content types.)
			 * 		@type array $custom_data An array of the custom field values being saved, you can change this to add other custom fields to the saving process (meta-based content types).
			 * }
			 * @param bool $is_new_item True if new item is being created, false if item already exists.
			 * @param int	$id ID of item being saved.
			 *
			 * @since unknown
			 */
			do_action( 'pods_api_post_save_pod_item', $pieces, $is_new_item, $params->id );

			/**
			 * Runs after a specific Pod has been updated.
			 *
			 * Parameters are the same as pods_api_post_save_pod_item
			 *
			 * @since unknown
			 *
			 */
			do_action( "post_save_pod_item_{$params->pod}", $pieces, $is_new_item, $params->id );

			if ( $is_new_item ) {
				/**
				 * Runs after an item, in any Pod, is created.
				 *
				 * @param array $pieces See pods_api_post_save_pod_item
				 *
				 * @since unknown
				 */
				do_action( 'pods_api_post_create_pod_item', $pieces, $params->id );

				/**
				 * Runs after an item, in a specific Pod, is created.
				 *
				 * @param array $pieces See pods_api_post_save_pod_item
				 *
				 * @since unknown
				 */
				do_action( "post_create_pod_item_{$params->pod}", $pieces, $params->id );
			}
			else {
				/**
				 * Runs after an existing item, in any Pod, is edited.
				 *
				 * @param array $pieces See pods_api_post_save_pod_item
				 * @param int $id ID of item being updated.
				 *
				 * @since unknown
				 */
				do_action( 'pods_api_post_edit_pod_item', $pieces, $params->id );

				/**
				 * Runs after an existing item, in a specific Pod, is edited.
				 *
				 * @param array $pieces See pods_api_post_save_pod_item
				 * @param int $id ID of item being updated.
				 *
				 * @since unknown
				 */
				do_action( "pods_api_post_edit_pod_item_{$params->pod}", $pieces, $params->id );
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

		// Clear cache
		pods_cache_clear( $params->id, 'pods_items_' . $pod['name'] );

		if ( $params->clear_slug_cache && ! empty( $pod['field_slug'] ) ) {
			$slug = pods( $pod['name'], $params->id )->field( $pod['field_slug'] );

			if ( 0 < strlen( $slug ) ) {
				pods_cache_clear( $slug, 'pods_items_' . $pod['name'] );
			}
		}

		// Clear WP meta cache
		if ( in_array( $pod['type'], array( 'post_type', 'taxonomy', 'user', 'comment' ) ) ) {
			$meta_type = $pod['type'];

			if ( 'post_type' == $meta_type ) {
				$meta_type = 'post';
			}

			wp_cache_delete( $params->id, $meta_type . '_meta' );
			wp_cache_delete( $params->id, 'pods_' . $meta_type . '_meta' );
		}

		// Success! Return the id
		return $params->id;
	}

	/**
	 * @see   Pods_API::save_pod_item
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
	 * @since 2.0
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
	 * Get the fields that have changed during a save
	 *
	 * @param array $pieces Pieces array from save_pod_item
	 *
	 * @return array Array of fields and values that have changed
	 */
	public function get_changed_fields( $pieces ) {

		$fields        = $pieces['fields'];
		$fields_active = $pieces['fields_active'];

		$fields_changed = array();

		if ( 0 < $pieces['params']->id ) {
			$pod = pods( $pieces['params']->pod, $pieces['params']->id );

			foreach ( $fields_active as $field ) {
				if ( isset( $fields[$field] ) && $pod->raw( $field ) != $fields[$field]['value'] ) {
					$fields_changed[$field] = $fields[$field]['value'];
				}
			}
		}

		return $fields_changed;

	}

	/**
	 * Save relationships
	 *
	 * @param int       $id          ID of item
	 * @param int|array $related_ids ID or IDs to save
	 * @param array     $pod         Pod data
	 * @param array     $field       Field data
	 *
	 */
	public function save_relationships( $id, $related_ids, $pod, $field ) {
		// Get current values
		if ( 'pick' == $field['type'] && isset( Pods_Field_Pick::$related_data[$field['id']] ) && isset( Pods_Field_Pick::$related_data[$field['id']]['current_ids'] ) ) {
			$current_ids = Pods_Field_Pick::$related_data[$field['id']]['current_ids'];
		} else {
			$current_ids = $this->lookup_related_items( $field['id'], $pod['id'], $id, $field, $pod );
		}

		if ( ! is_array( $related_ids ) ) {
			$related_ids = implode( ',', $related_ids );
		}

		foreach ( $related_ids as $k => $related_id ) {
			$related_ids[$k] = (int) $related_id;
		}

		$related_ids = array_unique( array_filter( $related_ids ) );

		$related_limit = (int) pods_v( $field['type'] . '_limit', $field, 0 );

		if ( 'single' == pods_v( $field['type'] . '_format_type', $field ) ) {
			$related_limit = 1;
		}

		// Limit values
		if ( 0 < $related_limit && ! empty( $related_ids ) ) {
			$related_ids = array_slice( $related_ids, 0, $related_limit );
		}

		// Post Types, Media, Users, and Comments (meta-based)
		if ( in_array( $pod['type'], array( 'post_type', 'media', 'user', 'comment' ) ) ) {
			$object_type = $pod['type'];

			if ( 'post_type' == $object_type || 'media' == $object_type ) {
				$object_type = 'post';
			}

			delete_metadata( $object_type, $id, $field['name'] );
			delete_metadata( $object_type, $id, '_pods_' . $field['name'] );

			if ( ! empty( $related_ids ) ) {
				if ( 1 < count( $related_ids ) ) {
					add_metadata( $object_type, $id, '_pods_' . $field['name'], $related_ids );
				}

				foreach ( $related_ids as $related_id ) {
					add_metadata( $object_type, $id, $field['name'], $related_id );
				}
			}
		} // Custom Settings Pages (options-based)
		elseif ( 'settings' == $pod['type'] ) {
			if ( ! empty( $related_ids ) ) {
				update_option( $pod['name'] . '_' . $field['name'], $related_ids );
			} else {
				delete_option( $pod['name'] . '_' . $field['name'] );
			}
		}

		$related_pod_id = $related_field_id = 0;

		if ( 'pick' == $field['type'] && isset( Pods_Field_Pick::$related_data[$field['id']] ) && ! empty( Pods_Field_Pick::$related_data[$field['id']]['related_field'] ) ) {
			$related_pod_id   = Pods_Field_Pick::$related_data[$field['id']]['related_pod']['id'];
			$related_field_id = Pods_Field_Pick::$related_data[$field['id']]['related_field']['id'];
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
                    ",
						array(
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
                    ",
						array(
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
	 * @since 2.3
	 */
	public function duplicate_pod( $params, $strict = false ) {

		if ( ! is_object( $params ) && ! is_array( $params ) ) {
			if ( is_numeric( $params ) ) {
				$params = array(
					'id' => $params
				);
			} else {
				$params = array(
					'name' => $params
				);
			}
		}

		$params = (object) $params;

		if ( ! isset( $params->strict ) ) {
			$params->strict = $strict;
		}

		$pod = $this->load_pod( $params, $params->strict );

		if ( empty( $pod ) ) {
			return false;
		}

		if ( isset( $params->id ) ) {
			unset( $params->id );
		}

		return $pod->duplicate( $params );

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
				$params = array(
					'id' => $params
				);
			} else {
				if ( false !== $strict ) {
					return pods_error( __( 'Field not found', 'pods' ), $this );
				}

				return false;
			}
		}

		$params = (object) $params;

		if ( ! isset( $params->strict ) ) {
			$params->strict = $strict;
		}

		$params->output = OBJECT;

		$field = $this->load_field( $params, __METHOD__ );

		if ( empty( $field ) || ! $field->is_valid() ) {
			if ( false !== $strict ) {
				return pods_error( __( 'Field not found', 'pods' ), $this );
			}

			return false;
		}

		if ( isset( $params->id ) ) {
			unset( $params->id );
		}

		return $field->duplicate( $params );

	}

	/**
	 * @see   Pods_API::save_pod_item
	 *
	 * Duplicate a pod item
	 *
	 * $params['pod'] string The Pod name
	 * $params['id'] int The item's ID from the wp_pods_* table
	 *
	 * @param array $params An associative array of parameters
	 *
	 * @return int The table row ID
	 * @since 1.12
	 */
	public function duplicate_pod_item( $params ) {
		$params = (object) pods_sanitize( $params );

		$pod = $this->load_pod( array( 'name' => $params->pod ) );

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
			'pod'  => $params->pod,
			'data' => array()
		);

		foreach ( $fields as $field ) {
			$value = $pod->field( array( 'name' => $field['name'], 'output' => 'ids' ) );

			if ( ! empty( $value ) || ( ! is_array( $value ) && 0 < strlen( $value ) ) ) {
				$save_params['data'][$field['name']] = $value;
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
		if ( ! is_object( $pod ) || 'Pods' != get_class( $pod ) ) {
			if ( empty( $params ) ) {
				return false;
			}

			$params = (object) pods_sanitize( $params );

			$pod = pods( $params->pod, $params->id, false );

			if ( empty( $pod ) ) {
				return false;
			}
		}

		$fields        = (array) pods_var_raw( 'fields', $params, array(), null, true );
		$depth         = (int) pods_v( 'depth', $params, 2, true );
		$object_fields = (array) pods_var_raw( 'object_fields', $pod->pod_data, array(), null, true );
		$flatten       = (boolean) pods_v( 'flatten', $params, false, true );

		if ( empty( $fields ) ) {
			$fields = $pod->fields;
			$fields = array_merge( $fields, $object_fields );
		}

		$data = $this->export_pod_item_level( $pod, $fields, $depth, $flatten );

		$data = $this->do_hook( 'export_pod_item', $data, $pod->pod, $pod->id(), $pod, $fields, $depth, $flatten );

		return $data;
	}

	/**
	 * Export a pod item by depth level
	 *
	 * @param Pods    $pod           Pods object
	 * @param array   $fields        Fields to export
	 * @param int     $depth         Depth limit
	 * @param boolean $flatten       Whether to flatten arrays for display
	 * @param int     $current_depth Current depth level
	 *
	 * @return array Data array
	 *
	 * @since 2.3
	 */
	private function export_pod_item_level( $pod, $fields, $depth, $flatten = false, $current_depth = 1 ) {
		$tableless_field_types    = Pods_Form::tableless_field_types();
		$simple_tableless_objects = Pods_Form::simple_tableless_objects();

		$object_fields = (array) pods_var_raw( 'object_fields', $pod->pod_data, array(), null, true );

		$export_fields = array();

		foreach ( $fields as $k => $field ) {
			if ( ! is_array( $field ) && ! is_object( $field ) ) {
				$name = $field;

				$field = array(
					'name' => $name
				);

				$field = pods_object_field( $field );
			} elseif ( is_array( $field ) ) {
				$field = pods_object_field( $field );
			}

			if ( isset( $pod->fields[$field['name']] ) ) {
				$field                = $pod->fields[$field['name']];
				$field['lookup_name'] = $field['name'];

				if ( in_array( $field['type'], $tableless_field_types ) && ! in_array( pods_v( 'pick_object', $field ), $simple_tableless_objects ) ) {
					if ( 'pick' == $field['type'] ) {
						if ( empty( $field['table_info'] ) ) {
							$field['table_info'] = $this->get_table_info( pods_v( 'pick_object', $field ), pods_v( 'pick_val', $field ), null, null, $field );
						}

						if ( ! empty( $field['table_info'] ) ) {
							$field['lookup_name'] .= '.' . $field['table_info']['field_id'];
						}
					} elseif ( in_array( $field['type'], Pods_Form::file_field_types() ) ) {
						$field['lookup_name'] .= '.guid';
					}
				}

				$export_fields[$field['name']] = $field;
			} elseif ( isset( $object_fields[$field['name']] ) ) {
				$field                = $object_fields[$field['name']];
				$field['lookup_name'] = $field['name'];

				$export_fields[$field['name']] = $field;
			} elseif ( $field['name'] == $pod->pod_data['field_id'] ) {
				$field['type']        = 'number';
				$field['lookup_name'] = $field['name'];

				$export_fields[$field['name']] = $field;
			}
		}

		$data = array();

		foreach ( $export_fields as $field ) {
			// Return IDs (or guid for files) if only one level deep
			if ( 1 == $depth ) {
				$data[$field['name']] = $pod->field( array( 'name' => $field['lookup_name'], 'output' => 'arrays' ) );
			} // Recurse depth levels for pick fields if $depth allows
			elseif ( ( - 1 == $depth || $current_depth < $depth ) && 'pick' == $field['type'] && ! in_array( pods_v( 'pick_object', $field ), $simple_tableless_objects ) ) {
				$related_data = array();

				$related_ids = $pod->field( array( 'name' => $field['name'], 'output' => 'ids' ) );

				if ( ! empty( $related_ids ) ) {
					$related_ids = (array) $related_ids;

					$pick_object = pods_v( 'pick_object', $field );

					$related_pod = pods( pods_v( 'pick_val', $field ), null, false );

					// If this isn't a Pod, return data exactly as Pods does normally
					if ( empty( $related_pod ) || ( 'pod' != $pick_object && $pick_object != $related_pod->pod_data['type'] ) || $related_pod->pod == $pod->pod ) {
						$related_data = $pod->field( array( 'name' => $field['name'], 'output' => 'arrays' ) );
					} else {
						$related_object_fields = (array) pods_var_raw( 'object_fields', $related_pod->pod_data, array(), null, true );

						$related_fields = array_merge( $related_pod->fields, $related_object_fields );

						foreach ( $related_ids as $related_id ) {
							if ( $related_pod->fetch( $related_id ) ) {
								$related_item = $this->export_pod_item_level( $related_pod, $related_fields, $depth, $flatten, ( $current_depth + 1 ) );

								$related_data[$related_id] = $this->do_hook( 'export_pod_item_level', $related_item, $related_pod->pod, $related_pod->id(), $related_pod, $related_fields, $depth, $flatten, ( $current_depth + 1 ) );
							}
						}

						if ( $flatten && ! empty( $related_data ) ) {
							$related_data = pods_serial_comma( array_values( $related_data ), array( 'and' => '', 'field_index' => $related_pod->pod_data['field_index'] ) );
						}
					}
				}

				$data[$field['name']] = $related_data;
			} // Return data exactly as Pods does normally
			else {
				$data[$field['name']] = $pod->field( array( 'name' => $field['name'], 'output' => 'arrays' ) );
			}

			if ( $flatten && is_array( $data[$field['name']] ) ) {
				$data[$field['name']] = pods_serial_comma( $data[$field['name']], array( 'field' => $field['name'], 'fields' => $export_fields, 'and' => '' ) );
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

		// @deprecated 2.0
		if ( isset( $params->datatype ) ) {
			pods_deprecated( __( '$params->pod instead of $params->datatype', 'pods' ), '2.0' );

			$params->pod = $params->datatype;

			unset( $params->datatype );
		}

		if ( null === pods_v( 'pod', $params, null, true ) ) {
			return pods_error( __( '$params->pod is required', 'pods' ), $this );
		}

		if ( ! is_array( $params->order ) ) {
			$params->order = explode( ',', $params->order );
		}

		$pod = $this->load_pod( array( 'name' => $params->pod ) );

		$params->name = $pod['name'];

		if ( false === $pod ) {
			return pods_error( __( 'Pod is required', 'pods' ), $this );
		}

		foreach ( $params->order as $order => $id ) {
			if ( isset( $pod['fields'][$params->field] ) || isset( $pod['object_fields'][$params->field] ) ) {
				if ( 'table' == $pod['storage'] && ! pods_tableless() ) {
					if ( isset( $pod['fields'][$params->field] ) ) {
						pods_query( "UPDATE `@wp_pods_{$params->name}` SET `{$params->field}` = " . pods_absint( $order ) . " WHERE `id` = " . pods_absint( $id ) . " LIMIT 1" );
					} else {
						pods_query( "UPDATE `{$pod['table']}` SET `{$params->field}` = " . pods_absint( $order ) . " WHERE `{$pod['field_id']}` = " . pods_absint( $id ) . " LIMIT 1" );
					}
				} else {
					$this->save_pod_item( array( 'pod' => $params->pod, 'pod_id' => $params->pod_id, 'id' => $id, 'data' => array( $params->field => pods_absint( $order ) ) ) );
				}
			}
		}

		return true;
	}

	/**
	 * Delete all content for a Pod
	 *
	 * $params['id'] int The Pod ID
	 * $params['name'] string The Pod name
	 *
	 * @param array      $params An associative array of parameters
	 * @param array|bool $pod    (optional) Pod data
	 *
	 * @return bool Whether the Content was successfully deleted
	 *
	 * @uses  pods_query
	 * @uses  pods_cache_clear
	 *
	 * @since 1.9.0
	 */
	public function reset_pod( $params, $pod = false ) {

		if ( empty( $pod ) ) {
			$pod = $this->load_pod( $params );
		} elseif ( is_array( $pod ) || is_object( $pod ) ) {
			$params = false;

			if ( isset( $pod['id'] ) && 0 < $pod['id'] ) {
				$params = array(
					'id' => $pod['id']
				);
			} elseif ( isset( $pod['name'] ) ) {
				$params = array(
					'name' => $pod['name']
				);
			}

			if ( ! empty( $params ) ) {
				$pod = $this->load_pod( $params );
			} else {
				$pod = false;
			}
		} else {
			$pod = false;
		}

		if ( empty( $pod ) ) {
			return pods_error( __( 'Pod not found', 'pods' ), $this );
		}

		return $pod->reset();

	}

	/**
	 * Delete a Pod and all its content
	 *
	 * $params['id'] int The Pod ID
	 * $params['name'] string The Pod name
	 *
	 * @param array $params     An associative array of parameters
	 * @param bool  $strict     (deprecated)
	 * @param bool  $delete_all (optional) Whether to delete all content from a WP object
	 *
	 * @uses  Pods_API::load_pod
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

		$params = (object) $params;

		if ( isset( $params->id ) && 0 < $params->id && isset( $params->name ) ) {
			unset( $params->name );
		}

		if ( isset( $params->old_name ) ) {
			$params->name = $params->old_name;
		}

		$pod = $this->load_pod( $params, __METHOD__ );

		if ( empty( $pod ) ) {
			return false;
		}

		return $pod->delete( $delete_all );

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
	 * @uses  Pods_API::load_field
	 * @uses  wp_delete_post
	 * @uses  pods_query
	 *
	 * @return bool
	 * @since 1.7.9
	 */
	public function delete_field( $params, $table_operation = true ) {

		$params = (object) $params;

		$load_params = array(
			'output' => OBJECT
		);

		if ( isset( $params->id ) ) {
			$load_params['id'] = $params->id;
		} elseif ( isset( $params->name ) && ( isset( $params->pod_id ) || isset( $params->pod ) ) ) {
			$load_params['name'] = $params->name;

			if ( isset( $params->pod_id ) ) {
				$load_params['pod_id'] = $params->pod_id;
			} else {
				$load_params['pod'] = $params->pod;
			}
		}

		$field = $this->load_field( $load_params, __METHOD__ );

		if ( empty( $field ) || ! $field->is_valid() ) {
			return false;
		}

		return $field->delete( false, $table_operation );

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
	 * @since 2.0
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
	 * @see   Pods_API::delete_object
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
	 * @see   Pods_API::delete_object
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
	 * @see   Pods_API::delete_object
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

		// @deprecated 2.0
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

		$pod = $this->load_pod( array( 'name' => $params->pod, 'id' => $params->pod_id ) );

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

		$pre_delete_helpers = $post_delete_helpers = array();

		if ( false === $bypass_helpers ) {
			// Plugin hook
			$this->do_hook( 'pre_delete_pod_item', $params, $pod );
			$this->do_hook( "pre_delete_pod_item_{$params->pod}", $params, $pod );

			// Call any pre-save helpers (if not bypassed)
			if ( ! defined( 'PODS_DISABLE_EVAL' ) || ! PODS_DISABLE_EVAL ) {
				if ( ! empty( $pod ) && ( is_array( $pod ) || is_object( $pod ) ) ) {
					$helpers = array( 'pre_delete_helpers', 'post_delete_helpers' );

					foreach ( $helpers as $helper ) {
						if ( isset( $pod[$helper] ) && ! empty( $pod[$helper] ) ) {
							${$helper} = explode( ',', $pod[$helper] );
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

		if ( 'table' == $pod['storage'] ) {
			pods_query( "DELETE FROM `@wp_pods_{$params->pod}` WHERE `id` = {$params->id} LIMIT 1" );
		}

		if ( $wp && 'taxonomy' == $pod['type'] ) {
			$taxonomy = $pod['name'];

			if ( ! empty( $pod['object'] ) ) {
				$taxonomy = $pod['object'];
			}

			wp_delete_term( $params->id, $taxonomy );
		} elseif ( $wp && ! in_array( $pod['type'], array( 'pod', 'table', '', 'taxonomy' ) ) ) {
			$this->delete_wp_object( $pod['type'], $params->id );
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
	 * @param int                 $id
	 * @param string|array|object $object
	 * @param string              $name
	 *
	 * @return bool
	 *
	 * @since 2.3
	 */
	public function delete_object_from_relationships( $id, $object, $name = null ) {

		/**
		 * @var $pods_init \Pods_Init
		 */
		global $pods_init;

		$pod = false;

		// Run any bidirectional delete operations
		if ( is_array( $object ) ) {
			$pod = $object;
		} elseif ( is_object( $object ) && 0 === strpos( get_class( $object ), 'Pods_Object' ) ) {
			$pod = $object;
		} elseif ( ! empty( $name ) ) {
			$pod = pods_object_pod( $name );
		}

		if ( ! empty( $pod ) ) {
			$object = $pod['type'];
			$name   = $pod['name'];

			foreach ( $pod['fields'] as $field ) {
				Pods_Form::delete( $field['type'], $id, $field['name'], $field, $pod );
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
			),
			'output' => OBJECT
		);

		if ( ! empty( $name ) && $name != $object ) {
			$params['where'][] = array(
				'key'   => 'pick_val',
				'value' => $name
			);
		}

		$fields = $this->load_fields( $params, false );

		if ( ! empty( $pod ) && 'media' == $pod['type'] ) {
			$params['where'] = array(
				array(
					'key'   => 'type',
					'value' => 'file'
				)
			);

			$fields = array_merge( $fields, $this->load_fields( $params, false ) );
		}

		if ( ! empty( $fields ) ) {
			foreach ( $fields as $related_field ) {
				$related_pod = $this->load_pod( array( 'id' => $related_field['pod_id'] ), __METHOD__ );

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
            ",
				array(
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
	 * @since 2.3
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

		unset( $related_ids[ array_search( $id, $related_ids ) ] );

		$no_conflict = pods_no_conflict_check( $related_pod['type'] );

		if ( ! $no_conflict ) {
			pods_no_conflict_on( $related_pod['type'] );
		}

		// Post Types, Media, Users, and Comments (meta-based)
		if ( in_array( $related_pod['type'], array( 'post_type', 'media', 'user', 'comment' ) ) ) {
			$object_type = $related_pod['type'];

			if ( 'post_type' == $object_type || 'media' == $object_type ) {
				$object_type = 'post';
			}

			delete_metadata( $object_type, $related_id, $related_field['name'] );
			delete_metadata( $object_type, $related_id, '_pods_' . $related_field['name'] );

			if ( ! empty( $related_ids ) ) {
				if ( 1 < count( $related_ids ) ) {
					add_metadata( $object_type, $related_id, '_pods_' . $related_field['name'], $related_ids );
				}

				foreach ( $related_ids as $rel_id ) {
					add_metadata( $object_type, $related_id, $related_field['name'], $rel_id );
				}
			}
		} // Custom Settings Pages (options-based)
		elseif ( 'settings' == $related_pod['type'] ) {
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
            ",
				array(
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
	 * @param array       $params An associative array of parameters
	 * @param null|string $type
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
				$pod = get_post( $dummy = (int) $params->id );
			} else {
				$pod = get_posts( array(
					'name'           => $params->name,
					'post_type'      => '_pods_pod',
					'posts_per_page' => 1
				) );
			}

			if ( ! empty( $pod ) && ( empty( $type ) || $type == get_post_meta( $pod->ID, 'type', true ) ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Load a Pod and all of its fields
	 *
	 * $params['id'] int The Pod ID
	 * $params['name'] string The Pod name
	 * $params['fields'] bool Whether to load fields (default is true)
	 *
	 * @param array|object $params An associative array of parameters or pod name as a string
	 * @param bool         $strict Makes sure the pod exists, throws an error if it doesn't work
	 *
	 * @return Pods_Object_Pod|bool|mixed|void
	 * @since 1.7.9
	 */
	public function load_pod( $params, $strict = true ) {

		// Debug override for strict
		if ( ! is_bool( $strict ) ) {
			$strict = apply_filters( 'pods_strict', false, __FUNCTION__, $strict );
		} else {
			$strict = ( ! $strict ? pods_strict( true ) : $strict );
		}

		if ( is_object( $params ) && isset( $params->post_name ) ) {
			if ( pods_api_cache() ) {
				$pod = pods_transient_get( 'pods_pod_' . $params->post_name );

				if ( is_object( $pod ) ) {
					return $pod;
				}
			}

			$pod = pods_object_pod( $params );
		} else {
			$params = (object) $params;

			if ( ( ! isset( $params->id ) || empty( $params->id ) ) && ( ! isset( $params->name ) || empty( $params->name ) ) ) {
				if ( $strict ) {
					return pods_error( 'Either Pod ID or Name are required', $this );
				}

				return false;
			}

			if ( isset( $params->name ) ) {
				if ( pods_api_cache() ) {
					$pod = pods_transient_get( 'pods_pod_' . $params->name );

					if ( is_object( $pod ) ) {
						return $pod;
					}
				}

				$pod = pods_object_pod( $params->name );
			} else {
				$pod = pods_object_pod( null, $params->id );
			}
		}

		if ( empty( $pod ) || ! $pod->is_valid() ) {
			if ( $strict ) {
				return pods_error( __( 'Pod not found', 'pods' ), $this );
			}

			return false;
		}

		if ( pods_api_cache() ) {
			pods_transient_set( 'pods_pod_' . $pod['name'], $pod );
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
	 * $params['key_names'] boolean Return pods keyed by name
	 * $params['output'] string OBJECT|ARRAY_A
	 *
	 * @param array $params An associative array of parameters
	 *
	 * @return array|mixed
	 *
	 * @uses  Pods_API::load_pod
	 *
	 * @since 2.0
	 */
	public function load_pods( $params = null ) {

		/**
		 * @var $sitepress SitePress
		 */
		global $sitepress, $icl_adjust_id_url_filter_off;

		$current_language = false;

		// WPML support
		if ( is_object( $sitepress ) && ! $icl_adjust_id_url_filter_off ) {
			$current_language = pods_sanitize( ICL_LANGUAGE_CODE );
		} // Polylang support
		elseif ( function_exists( 'pll_current_language' ) ) {
			$current_language = pll_current_language( 'slug' );
		}

		$params = (object) pods_sanitize( $params );

		$order   = 'ASC';
		$orderby = 'menu_order title';
		$limit   = - 1;
		$ids     = false;

		$meta_query = array();
		$cache_key  = '';

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

			if ( 0 < count( $params->object ) ) {
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

			$cache_key .= '_options_' . serialize( $params->options );
		}

		if ( isset( $params->where ) && is_array( $params->where ) ) {
			$meta_query = array_merge( $meta_query, (array) $params->where );
		}

		if ( isset( $params->order ) && ! empty( $params->order ) && in_array( strtoupper( $params->order ), array( 'ASC', 'DESC' ) ) ) {
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

		$pre_key .= '_get';

		if ( empty( $cache_key ) ) {
			$cache_key = 'pods' . $pre_key . '_all';
		} else {
			$cache_key = 'pods' . $pre_key . $cache_key;
		}

		if ( pods_api_cache() && ! empty( $cache_key ) && ( 'pods_get_all' != $cache_key || empty( $meta_query ) ) && $limit < 1 && ( empty( $orderby ) || 'menu_order title' == $orderby ) && empty( $ids ) ) {
			$the_pods = pods_transient_get( $cache_key );

			if ( false === $the_pods ) {
				$the_pods = pods_cache_get( $cache_key, 'pods' );
			}

			if ( ! is_array( $the_pods ) && 'none' == $the_pods ) {
				return array();
			} elseif ( false !== $the_pods ) {
				return pods_objects_unserialize( $the_pods, 'pods_object_pod' );
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
		if ( false != $ids ) {
			$args[ 'post__in' ] = $ids;
		}

		$_pods = get_posts( $args );

		$total_fields = 0;

		if ( isset( $params->count ) && $params->count ) {
			$the_pods = count( $_pods );
		} else {
			foreach ( $_pods as $pod ) {
				if ( isset( $params->names ) && $params->names ) {
					$the_pods[$pod->post_name] = $pod->post_title;
				} elseif ( isset( $params->names_ids ) && $params->names_ids ) {
					$the_pods[$pod->ID] = $pod->post_name;
				} else {
					$pod = pods_object_pod( $pod );

					$total_fields += count( $pod->fields );

					if ( isset( $params->key_names ) && $params->key_names ) {
						$the_pods[$pod['name']] = $pod;
					} else {
						$the_pods[$pod['id']] = $pod;
					}
				}
			}
		}

		if ( pods_api_cache() ) {
			if ( ( isset( $params->refresh ) && $params->refresh ) && ! empty( $cache_key ) && ( 'pods' != $cache_key || empty( $meta_query ) ) && $limit < 1 && ( empty( $orderby ) || 'menu_order title' == $orderby ) && empty( $ids ) ) {
				pods_transient_clear( $cache_key );

				if ( empty( $the_pods ) && ( ! isset( $params->count ) || ! $params->count ) ) {
					pods_transient_set( $cache_key, 'none' );
				} else {
					pods_transient_set( $cache_key, pods_objects_serialize( $the_pods ) );
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

		$field = pods_object_field();

		if ( isset( $params->id ) || ( isset( $params->name ) && isset( $params->pod_id ) ) ) {
			if ( isset( $params->id ) && $field->exists( null, $params->id ) ) {
				return true;
			} elseif ( isset( $params->name ) && isset( $params->pod_id ) && $field->exists( $params->name, 0, $params->pod_id ) ) {
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
	 *
	 * @param array   $params An associative array of parameters
	 * @param boolean $strict Whether to require a field exist or not when loading the info
	 *
	 * @return Pods_Object_Field|bool Array with field data, false if field not found
	 * @since 1.7.9
	 */
	public function load_field( $params, $strict = false ) {

		// Debug override for strict
		if ( ! is_bool( $strict ) ) {
			$strict = apply_filters( 'pods_strict', false, __FUNCTION__, $strict );
		} else {
			$strict = ( ! $strict ? pods_strict( true ) : $strict );
		}

		$output = ARRAY_A;

		if ( is_object( $params ) && isset( $params->post_name ) ) {
			$field = pods_object_field( $params );

			$output = OBJECT;
		} else {
			$params = (object) $params;

			if ( ! empty( $params->output ) && in_array( $params->output, array( OBJECT, ARRAY_A ) ) ) {
				$output = $params->output;
			}

			$field = false;

			if ( empty( $params->name ) && empty( $params->id ) && empty( $params->pod ) && empty( $params->pod_id ) ) {
				if ( $strict ) {
					return pods_error( __( 'Either Field Name or ID and a Pod Name or ID are required', 'pods' ), $this );
				}

				return false;
			}

			if ( isset( $params->id ) ) {
				if ( ! empty( $params->pod_id ) ) {
					$field = pods_object_field( $params->name, 0, false, $params->pod_id );
				} else {
					$field = pods_object_field( null, $params->id );
				}
			} elseif ( isset( $params->name ) ) {
				if ( ! empty( $params->pod_id ) ) {
					$field = pods_object_field( $params->name, 0, false, $params->pod_id );
				} elseif ( ! empty( $params->pod ) ) {
					$pod = pods_object_pod( $params->pod );

					if ( ! $pod->is_valid() ) {
						if ( $strict ) {
							return pods_error( __( 'Pod not found', 'pods' ), $this );
						}

						return false;
					}

					$field = $pod->fields( $params->name );
				}
			} elseif ( isset( $params->id ) ) {
				$field = pods_object_field( null, $params->id );
			}
		}

		if ( empty( $field ) || ! $field->is_valid() ) {
			if ( $strict ) {
				return pods_error( __( 'Field not found', 'pods' ), $this );
			}

			return false;
		}

		// Export object into full array
		if ( ARRAY_A == $output ) {
			$field->export();
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
	 * $params['output'] string OBJECT|ARRAY_A
	 *
	 * @param array $params An associative array of parameters
	 * @param bool  $strict Whether to require a field exist or not when loading the info
	 *
	 * @return array Array of field data.
	 *
	 * @since 1.7.9
	 */
	public function load_fields( $params, $strict = false ) {

		/**
		 * @var $sitepress SitePress
		 */
		global $sitepress, $icl_adjust_id_url_filter_off;

		$current_language = false;

		// WPML support
		if ( is_object( $sitepress ) && ! $icl_adjust_id_url_filter_off ) {
			$current_language = pods_sanitize( ICL_LANGUAGE_CODE );
		} // Polylang support
		elseif ( function_exists( 'pll_current_language' ) ) {
			$current_language = pll_current_language( 'slug' );
		}

		$params = (object) pods_sanitize( $params );

		if ( empty( $params->pod ) ) {
			$params->pod = '';
		}

		if ( empty( $params->pod_id ) ) {
			$params->pod_id = 0;
		}

		if ( empty( $params->name ) ) {
			$params->name = array();
		} else {
			$params->name = (array) $params->name;
		}

		if ( empty( $params->id ) ) {
			$params->id = array();
		} else {
			$params->id = (array) $params->id;
		}

		if ( empty( $params->type ) ) {
			$params->type = array();
		} else {
			$params->type = (array) $params->type;
		}

		// Only support meta_query arrays
		if ( empty( $params->where ) || ! is_array( $params->where ) ) {
			$params->where = array();
		}

		if ( empty( $params->options ) || ! is_array( $params->options ) ) {
			$params->options = array();
		}

		$cache_key = '';

		if ( ! empty( $current_language ) ) {
			$cache_key .= '_' . $current_language;
		}

		if ( ! empty( $params->pod ) ) {
			$cache_key .= '_pod_' . $params->pod;
		}

		if ( ! empty( $params->pod_id ) ) {
			$cache_key .= '_pod_id_' . $params->pod_id;
		}

		if ( ! empty( $params->name ) ) {
			$cache_key .= '_name_' . trim( implode( '', $params->name ) );
		}

		if ( ! empty( $params->id ) ) {
			$cache_key .= '_id_' . trim( implode( '', $params->id ) );
		}

		if ( ! empty( $params->type ) ) {
			$cache_key .= '_type_' . trim( implode( '', $params->type ) );
		}

		if ( ! empty( $params->where ) ) {
			$cache_key .= '_where_' . trim( implode( '', $params->where ) );
		}

		if ( ! empty( $params->options ) ) {
			$cache_key .= '_options_' . trim( implode( '', $params->options ) );
		}

		if ( empty( $params->output ) || ! in_array( $params->output, array( OBJECT, ARRAY_A ) ) ) {
			$params->output = ARRAY_A;
		}

		if ( empty( $cache_key ) ) {
			$cache_key = 'pods_fields_all';
		} else {
			$cache_key = 'pods_fields' . $cache_key;
		}

		if ( pods_api_cache() && ! empty( $cache_key ) && ( 'pods_field_get_all' != $cache_key || empty( $meta_query ) ) && $limit < 1 && ( empty( $orderby ) || 'menu_order title' == $orderby ) && empty( $ids ) ) {
			$the_fields = pods_transient_get( $cache_key );

			if ( false === $the_fields ) {
				$the_fields = pods_cache_get( $cache_key, 'pods' );
			}

			if ( ! is_array( $the_fields ) && 'none' == $the_fields ) {
				return array();
			} elseif ( false !== $the_fields ) {
				return pods_objects_unserialize( $the_fields, 'pods_object_field' );
			}
		}

		$fields = array();

		$meta_query = array();
		$ids = array();

		if ( ! empty( $params->id ) ) {
			$ids = array_map( 'absint', $params->id );
		}

		if ( ! empty( $params->pod ) || ! empty( $params->pod_id ) ) {
			$pod = $this->load_pod( array( 'name' => $params->pod, 'id' => $params->pod_id ), __METHOD__ );

			if ( false === $pod ) {
				if ( $strict ) {
					return pods_error( __( 'Pod not found', 'pods' ), $this );
				}

				return $fields;
			}

			$pod['fields'] = array_merge( pods_var_raw( 'object_fields', $pod, array() ), $pod['fields'] );

			foreach ( $pod['fields'] as $field ) {
				if ( empty( $params->name ) && empty( $params->id ) && empty( $params->type ) ) {
					$fields[$field['name']] = $field;
				} elseif ( in_array( $fields['name'], $params->name ) || in_array( $fields['id'], $params->id ) || in_array( $fields['type'], $params->type ) ) {
					$fields[$field['name']] = $field;
				}
			}
		} else {
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

			if ( ! empty( $params->type ) ) {
				$meta_query[] = array(
					'key'     => 'type',
					'value'   => pods_sanitize( $params->type ),
					'compare' => 'IN',
				);
			}

			if ( ! empty( $params->where ) ) {
				$meta_query = array_merge( $meta_query, $params->where );
			}

			if ( empty( $params->options ) && empty( $params->where ) ) {
				if ( empty( $params->name ) && empty( $params->id ) && empty( $params->type ) ) {
					return pods_error( __( 'Either Field Name / Field ID / Field Type, or Pod Name / Pod ID are required', 'pods' ), $this );
				}

				$lookup = array();

				if ( ! empty( $params->name ) ) {
					$field_names = implode( "', '", pods_sanitize( $params->name ) );

					$lookup[] = "`post_name` IN ( '{$field_names}' )";
				}

				if ( ! empty( $params->id ) ) {
					$field_ids = implode( ", ", $ids );

					$lookup[] = "`ID` IN ( {$field_ids} )";
				}

				$lookup = implode( ' AND ', $lookup );

				$ids = pods_query( "SELECT `ID` FROM `@wp_posts` WHERE `post_type` = '_pods_field' AND ( {$lookup} )" );

				if ( ! empty( $ids ) ) {
					$ids = wp_list_pluck( $ids, 'ID' );
					$ids = array_map( 'absint', $ids );
				}
			}
		}

		if ( empty( $fields ) ) {
			$order   = 'ASC';
			$orderby = 'menu_order title';
			$limit   = -1;

			$args = array(
				'post_type'      => '_pods_field',
				'nopaging'       => true,
				'posts_per_page' => $limit,
				'order'          => $order,
				'orderby'        => $orderby,
			);

			if ( ! empty( $ids ) ) {
				$args['post__in'] = $ids;
			}

			if ( ! empty( $meta_query ) ) {
				$args['meta_query'] = $meta_query;
			}

			$_fields = get_posts( $args );

			foreach ( $_fields as $field ) {
				$field = pods_object_field( $field );

				if ( ! empty( $field ) && $field->is_valid() ) {
					$field_id = $field->id;

					// Export object into full array
					if ( ARRAY_A == $params->output ) {
						$field->export();
					}

					$fields[ $field_id ] = $field;
				}
			}
		}

		if ( pods_api_cache() ) {
			if ( ( isset( $params->refresh ) && $params->refresh ) && ! empty( $cache_key ) && ( empty( $meta_query ) ) && ( empty( $orderby ) || 'menu_order title' == $orderby ) && empty( $ids ) ) {
				pods_transient_clear( $cache_key );

				if ( empty( $fields ) && ( ! isset( $params->count ) || ! $params->count ) ) {
					pods_transient_set( $cache_key, 'none' );
				} else {
					pods_transient_set( $cache_key, pods_objects_serialize( $fields ) );
				}
			}
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
	 * @since 2.0
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

		$object = array_merge( get_post_meta( $object['id'] ), $object );

		foreach ( $object as $option => &$value ) {
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
	 * @since 2.0
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

		if ( isset( $params->order ) && ! empty( $params->order ) && in_array( strtoupper( $params->order ), array( 'ASC', 'DESC' ) ) ) {
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

		if ( pods_api_cache() && empty( $meta_query ) && empty( $limit ) && ( empty( $orderby ) || 'menu_order' == $orderby ) && empty( $ids ) ) {
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
		if ( false != $ids ) {
			$args[ 'post__in' ] = $ids;
		}

		$objects = get_posts( $args );

		foreach ( $objects as $object ) {
			$object = $this->load_object( $object );

			$the_objects[$object['name']] = $object;
		}

		if ( pods_api_cache() && ! empty( $cache_key ) ) {
			pods_transient_set( $cache_key, $the_objects );
		}

		return $the_objects;
	}

	/**
	 * @see   Pods_API::load_object
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
	 * @see   Pods_API::load_objects
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
	 * @since 2.0
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
	 * @see   Pods_API::load_object
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
	 * @see   Pods_API::load_objects
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
	 * @since 2.0
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
	 * @see   Pods_API::load_object
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
	 * @see   Pods_API::load_objects
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
	 * @since 2.0
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
	 * @since 2.0
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
	 * @uses  Pods_API::load_pod
	 */
	public function load_sister_fields( $params, $pod = null ) {
		$params = (object) pods_sanitize( $params );

		if ( empty( $pod ) ) {
			$pod = $this->load_pod( array( 'name' => $params->pod ), __METHOD__ );

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
		}

		$related_pod = $this->load_pod( array( 'name' => $params->related_pod ), __METHOD__ );

		if ( false === $related_pod || ( false !== $type && 'pod' != $type && $type != $related_pod['type'] ) ) {
			return pods_error( __( 'Related Pod not found', 'pods' ), $this );
		}

		$params->related_pod_id = $related_pod['id'];
		$params->related_pod    = $related_pod['name'];

		$sister_fields = array();

		foreach ( $related_pod['fields'] as $field ) {
			if ( 'pick' == $field['type'] && in_array( $field['pick_object'], array( $pod['type'], 'pod' ) ) && ( $params->pod == $field['pick_object'] || $params->pod == $field['pick_val'] ) ) {
				$sister_fields[$field['id']] = esc_html( $field['label'] . ' (' . $field['name'] . ')' );
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
	 * @since 2.0
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

		return ( array_key_exists( $sql_field, $field_to_field_map ) ) ? $field_to_field_map[$sql_field] : 'paragraph';
	}

	/**
	 * Gets all field types
	 *
	 * @return array Array of field types
	 *
	 * @uses  Pods_Form::field_loader
	 *
	 * @since 2.0
	 * @deprecated
	 */
	public function get_field_types() {
		return Pods_Form::field_types();
	}

	/**
	 * Gets the schema definition of a field.
	 *
	 * @param string $type    Field type to look for
	 * @param array  $options (optional) Options of the field to pass to the schema function.
	 *
	 * @return array|bool|mixed|null
	 *
	 * @since 2.0
	 */
	public function get_field_definition( $type, $options = null ) {
		$definition = Pods_Form::field_method( $type, 'schema', $options );

		return $this->do_hook( 'field_definition', $definition, $type, $options );
	}

	/**
	 * @see   Pods_Form:validate
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
	 * @uses  Pods_Form::validate
	 *
	 * @since 2.0
	 */
	public function handle_field_validation( &$value, $field, $object_fields, $fields, $pod, $params ) {
		$tableless_field_types = Pods_Form::tableless_field_types();

		$fields = array_merge( $fields, $object_fields );

		$options = $fields[$field];

		$id = ( is_object( $params ) ? $params->id : ( is_object( $pod ) ? $pod->id() : 0 ) );

		if ( is_object( $pod ) ) {
			$pod = $pod->pod_data;
		}

		$type  = $options['type'];
		$label = $options['label'];
		$label = empty( $label ) ? $field : $label;

		// Verify required fields
		if ( 1 == pods_v( 'required', $options, 0 ) && 'slug' != $type ) {
			if ( '' == $value || null === $value || array() === $value ) {
				return pods_error( sprintf( __( '%s is empty', 'pods' ), $label ), $this );
			}

			if ( 'multi' == pods_v( 'pick_format_type', $options ) && 'autocomplete' != pods_v( 'pick_format_multi', $options ) ) {
				$has_value = false;

				$check_value = (array) $value;

				foreach ( $check_value as $val ) {
					if ( '' != $val && null !== $val && 0 !== $val && '0' !== $val ) {
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
		if ( 1 == pods_v( 'unique', $options, 0 ) && '' !== $value && null !== $value && array() !== $value ) {
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
				if ( 'table' == $pod['storage'] ) {
					$check = pods_query( "SELECT `id` FROM `@wp_pods_" . $pod['name'] . "` WHERE `{$field}` = '{$check_value}' {$exclude} LIMIT 1", $this );
				}

				if ( ! empty( $check ) ) {
					return pods_error( sprintf( __( '%s needs to be unique', 'pods' ), $label ), $this );
				}
			} else {
				// @todo handle tableless check
			}
		}

		$validate = Pods_Form::validate( $options['type'], $value, $field, $options, $fields, $pod, $id, $params );

		$validate = $this->do_hook( 'field_validation', $validate, $value, $field, $object_fields, $fields, $pod, $params, $options );

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
	 * @return array
	 *
	 * @since 2.0
	 *
	 * @uses  pods_query()
	 */
	public function lookup_related_items( $field_id, $pod_id, $ids, $field = null, $pod = null ) {

		$related_ids = array();

		if ( ! is_array( $ids ) ) {
			$ids = explode( ',', $ids );
		}

		$ids = array_map( 'absint', $ids );
		$ids = array_filter( $ids );
		$ids = array_unique( $ids );

		$tableless_field_types = Pods_Form::tableless_field_types();

		if ( empty( $field ) ) {
			$field = $this->load_field( array( 'id' => $field_id, 'output' => OBJECT ) );
		} elseif ( is_array( $field ) ) {
			$field = pods_object_field( $field );
		}

		$field_type = $field['type'];

		if ( empty( $ids ) || ! in_array( $field_type, $tableless_field_types ) ) {
			return array();
		}

		$idstring = false;

		if ( 0 != $pod_id && 0 != $field_id ) {
	        $idstring = implode( ',', $ids );
		}

	    if ( false !== $idstring && isset( self::$related_item_cache[ $pod_id ][ $field_id ][ $idstring ] ) ) {
		    // Check cache first, no point in running the same query multiple times
		    return self::$related_item_cache[ $pod_id ][ $field_id ][ $idstring ];
	    }

		$related_pick_limit = 0;

		if ( ! empty( $field ) && $field->is_valid() ) {
			$related_pick_limit = (int) pods_v( $field_type . '_limit', $field, 0 );

			if ( 'single' == pods_v( $field_type . '_format_type', $field ) ) {
				$related_pick_limit = 1;
			}

			// Temporary hack until there's some better handling here
			$related_pick_limit = $related_pick_limit * count( $ids );
		}

		if ( 'taxonomy' == $field_type ) {
			$related = wp_get_object_terms( $ids, pods_v( 'name', $field ), array( 'fields' => 'ids' ) );

			if ( ! is_wp_error( $related ) ) {
				$related_ids = $related;
			}
		} elseif ( ! pods_tableless() ) {
			$ids = implode( ', ', $ids );

			$field_id  = (int) $field_id;
			$sister_id = (int) pods_v( 'sister_id', $field, 0 );

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
			if ( ! is_array( $pod ) && ! is_object( $pod ) ) {
				$pod = $this->load_pod( array( 'id' => $pod_id ), __METHOD__ );
			}

			if ( ! empty( $pod ) && in_array( $pod['type'], array( 'post_type', 'media', 'user', 'comment', 'settings' ) ) ) {
				$meta_type = $pod['type'];

				if ( in_array( $meta_type, array( 'post_type', 'media' ) ) ) {
					$meta_type = 'post';
				}

				$no_conflict = pods_no_conflict_check( $meta_type );

				if ( ! $no_conflict ) {
					pods_no_conflict_on( $meta_type );
				}

				foreach ( $ids as $id ) {
					if ( 'settings' == $meta_type ) {
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

		if ( false !== $idstring ) {
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
	 * @since 2.3
	 *
	 * @uses  pods_query()
	 */
	public function lookup_related_items_from( $field_id, $pod_id, $id, $field = null, $pod = null ) {
		$related_ids = false;

		$id = (int) $id;

		$tableless_field_types = Pods_Form::tableless_field_types();

		if ( empty( $id ) || ! in_array( pods_v( 'type', $field ), $tableless_field_types ) ) {
			return false;
		}

		$related_pick_limit = 0;

		if ( ! empty( $field ) ) {
			$options = (array) pods_v( 'options', $field, $field, true );

			$related_pick_limit = (int) pods_v( 'pick_limit', $options, 0 );

			if ( 'single' == pods_v( 'pick_format_type', $options ) ) {
				$related_pick_limit = 1;
			}
		}

		if ( ! pods_tableless() ) {
			$field_id  = (int) $field_id;
			$sister_id = (int) pods_v( 'sister_id', $field, 0 );

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

			if ( ! is_array( $pod ) && ! is_object( $pod ) ) {
				$pod = $this->load_pod( array( 'id' => $pod_id ), __METHOD__ );
			}

			if ( ! empty( $pod ) && in_array( $pod['type'], array( 'post_type', 'media', 'user', 'comment', 'settings' ) ) ) {
				$related_ids = array();

				$meta_type = $pod['type'];

				if ( in_array( $pod['type'], array( 'post_type', 'media' ) ) ) {
					$meta_type = 'post';
				}

				$no_conflict = pods_no_conflict_check( $meta_type );

				if ( ! $no_conflict ) {
					pods_no_conflict_on( $meta_type );
				}

				if ( 'settings' == $meta_type ) {
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
					pods_no_conflict_off( $meta_type );
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
	 * @param $object_type
	 * @param string $object The object to look for
	 * @param null $name (optional) Name of the pod to load
	 * @param array $pod (optional) Array with pod information
	 *
	 * @return array
	 *
	 * @since 2.5
	 */
	public function get_table_info_load ( $object_type, $object, $name = null, $pod = null ) {

		$info = array();

		if ( 'pod' == $object_type && null === $pod ) {
			if ( empty( $name ) ) {
				$prefix = 'pod-';

				// Make sure we actually have the prefix before trying anything with the name
				if ( 0 === strpos( $object_type, $prefix ) )
					$name = substr( $object_type, strlen( $prefix ), strlen( $object_type ) );
			}

			if ( empty( $name ) && !empty( $object ) )
				$name = $object;

			$pod = $this->load_pod( array( 'name' => $name ), false );

			if ( !empty( $pod ) ) {
				$object_type = $pod[ 'type' ];
				$name = $pod[ 'name' ];
				$object = $pod[ 'object' ];

				$info[ 'pod' ] = $pod;
			}
		}
		elseif ( null === $pod ) {
			if ( empty( $name ) ) {
				$prefix = $object_type . '-';

				// Make sure we actually have the prefix before trying anything with the name
				if ( 0 === strpos( $object_type, $prefix ) )
					$name = substr( $object_type, strlen( $prefix ), strlen( $object_type ) );
			}

			if ( empty( $name ) && !empty( $object ) )
				$name = $object;

			if ( !empty( $name ) ) {
				$pod = $this->load_pod( array( 'name' => $name ), false );

				if ( !empty( $pod ) && ( null === $object_type || $object_type == $pod[ 'type' ] ) ) {
					$object_type = $pod[ 'type' ];
					$name = $pod[ 'name' ];
					$object = $pod[ 'object' ];

					$info[ 'pod' ] = $pod;
				}
			}
		}
		elseif ( !empty( $pod ) )
			$info[ 'pod' ] = $pod;

		if ( 0 === strpos( $object_type, 'pod' ) ) {
			if ( empty( $name ) ) {
				$prefix = 'pod-';

				// Make sure we actually have the prefix before trying anything with the name
				if ( 0 === strpos( $object_type, $prefix ) )
					$name = substr( $object_type, strlen( $prefix ), strlen( $object_type ) );
			}

			$info[ 'type' ] = 'pod';
			global $wpdb;

			$info[ 'table' ] = $info[ 'meta_table' ] = $wpdb->prefix . 'pods_' . ( empty( $object ) ? $name : $object );

			if ( is_array( $info[ 'pod' ] ) && 'pod' == pods_v( 'type', $info[ 'pod' ] ) ) {
				$info[ 'pod_field_index' ] = $info[ 'field_index' ] = $info[ 'meta_field_index' ] = $info[ 'meta_field_value' ] = pods_v( 'pod_index', $info[ 'pod' ], 'id', true );

				$slug_field = get_posts( array(
					'post_type' => '_pods_field',
					'posts_per_page' => 1,
					'nopaging' => true,
					'post_parent' => $info[ 'pod' ][ 'id' ],
					'orderby' => 'menu_order',
					'order' => 'ASC',
					'meta_query' => array(
						array(
							'key' => 'type',
							'value' => 'slug',
						)
					)
				) );

				if ( !empty( $slug_field ) ) {
					$slug_field = $slug_field[ 0 ];

					$info[ 'field_slug' ] = $info[ 'pod_field_slug' ] = $slug_field->post_name;
				}

				if ( 1 == pods_v( 'hierarchical', $info[ 'pod' ][ 'options' ], 0 ) ) {
					$parent_field = pods_v( 'pod_parent', $info[ 'pod' ], 'id', true );

					if ( !empty( $parent_field ) && isset( $info[ 'pod' ][ 'fields' ][ $parent_field ] ) ) {
						$info[ 'object_hierarchical' ] = true;

						$info[ 'pod_field_parent' ] = $info[ 'field_parent' ] = $parent_field . '_select';
						$info[ 'field_parent_select' ] = '`' . $parent_field . '`.`id` AS `' . $info[ 'field_parent' ] . '`';
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
	 * @param Pods_Object_Pod|array  $pod    (optional) Array with pod information
	 * @param Pods_Object_Field|array  $field  (optional) Array with field information
	 *
	 * @return array|bool
	 *
	 * @since 2.0
	 */
	public function get_table_info( $object_type, $object, $name = null, $pod = null, $field = null ) {
		/**
		 * @var $wpdb                         wpdb
		 * @var $sitepress                    SitePress
		 * @var $icl_adjust_id_url_filter_off boolean
		 */
		global $wpdb, $sitepress, $icl_adjust_id_url_filter_off, $polylang;

		// @todo Handle $object arrays for Post Types, Taxonomies, Comments (table pulled from first object in array)

		$original_pod = $pod;

		if ( empty( $object_type ) ) {
			$object_type = 'post_type';
			$object      = 'post';
		}
	    elseif ( empty( $object ) && in_array( $object_type, array( 'user', 'media', 'comment' ) ) ) {
		    $object = $object_type;
	    }

		if ( '__current__' == $object && is_object( $pod ) ) {
			$object = $pod->name;
		} elseif ( '__current__' == $object && is_array( $pod ) ) {
			$object = $pod['name'];
		} elseif ( '__current__' == $object && is_object( $field ) ) {
			$pod = $field->parent_id;
		} elseif ( '__current__' == $object && is_array( $field ) ) {
			$pod = $this->load_pod( array( 'name' => $field['pod'] ), 'get_table_info' );
		}

		$info = array(
			//'select' => '`t`.*',
			'object_type'         => $object_type,
			'type'                => null,
			'object_name'         => $object,
			'object_hierarchical' => false,
			'table'               => $object,
			'meta_table'          => $object,
			'pod_table'           => $wpdb->prefix . 'pods_' . ( empty( $object ) ? $name : $object ),
			'field_id'            => 'id',
			'field_index'         => 'name',
			'field_slug'          => null,
			'field_type'          => null,
			'field_parent'        => null,
			'field_parent_select' => null,
			'meta_field_id'       => 'id',
			'meta_field_index'    => 'name',
			'meta_field_value'    => 'name',
			'pod_field_id'        => 'id',
			'pod_field_index'     => 'name',
			'pod_field_slug'      => null,
			'pod_field_parent'    => null,
			'join'                => array(),
			'where'               => null,
			'where_default'       => null,
			'orderby'             => null,
			'recurse'             => false
		);

		$pod_name = $pod;

		if ( is_array( $pod_name ) || is_object( $pod_name ) ) {
			$pod_name = pods_var_raw( 'name', $pod_name, ( version_compare( PHP_VERSION, '5.4.0', '>=' ) ? json_encode( $pod_name, JSON_UNESCAPED_UNICODE ) : json_encode( $pod_name ) ), null, true );
		} else {
		    $pod_name = $object;
	    }

		$field_name = $field;

		if ( is_array( $field_name ) || is_object( $field_name ) ) {
			$field_name = pods_v( 'name', $field_name, '', true );
		}

		$transient = 'pods_get_table_info_' . md5( $object_type . '_object_' . $object . '_name_' . $name . '_pod_' . $pod_name . '_field_' . $field_name );

		$current_language      = false;
		$current_language_t_id = $current_language_tt_id = 0;

		// WPML support
		if ( is_object( $sitepress ) && ! $icl_adjust_id_url_filter_off ) {
			$current_language = pods_sanitize( ICL_LANGUAGE_CODE );
		} // Polylang support
		elseif ( is_object( $polylang ) && function_exists( 'pll_current_language' ) ) {
			$current_language = pods_sanitize( pll_current_language( 'slug' ) );

			if ( ! empty( $current_language ) ) {
				$current_language_t_id  = (int) $polylang->get_language( $current_language )->term_id;
				$current_language_tt_id = (int) $polylang->get_language( $current_language )->term_taxonomy_id;
			}
		}

		if ( ! empty( $current_language ) ) {
			$transient = 'pods_get_table_info_' . $current_language . '_' . md5( $object_type . '_object_' . $object . '_name_' . $name . '_pod_' . $pod_name . '_field_' . $field_name );
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

	    if ( false !== $_info ) {
		    // Data was cached, use that
		    $info = $_info;
	    } else {
	        // Data not cached, load it up
		    $_info = $this->get_table_info_load( $object_type, $object, $name, $pod );
		    if ( isset( $_info[ 'type' ] ) ) {
			    // Allow function to override $object_type
			    $object_type = $_info[ 'type' ];
		    }
		    $info = array_merge( $info, $_info );
	    }

		if ( 'pod' == $object_type && null === $pod ) {
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

			$pod = $this->load_pod( array( 'name' => $name ), 'get_table_info' );

			if ( ! empty( $pod ) ) {
				$object_type = $pod['type'];
				$name        = $pod['name'];
				$object      = $pod['object'];
			} else {
				$pod = null;
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
				$pod = $this->load_pod( array( 'name' => $name ), 'get_table_info' );

				if ( ! empty( $pod ) && ( null === $object_type || $object_type == $pod['type'] ) ) {
					$object_type = $pod['type'];
					$name        = $pod['name'];
					$object      = $pod['object'];
				} else {
					$pod = null;
				}
			}
		}

		if ( 0 === strpos( $object_type, 'pod' ) ) {
			if ( empty( $name ) ) {
				$prefix = 'pod-';

				// Make sure we actually have the prefix before trying anything with the name
				if ( 0 === strpos( $object_type, $prefix ) ) {
					$name = substr( $object_type, strlen( $prefix ), strlen( $object_type ) );
				}
			}

			$object_type = 'pod';

			$info['table'] = $info['meta_table'] = $info['pod_table'] = $wpdb->prefix . 'pods_' . ( empty( $object ) ? $name : $object );

			if ( ! empty( $pod ) && 'pod' == $pod['type'] ) {
				$info['pod_field_index'] = $info['field_index'] = $info['meta_field_index'] = $info['meta_field_value'] = pods_v( 'pod_index', $pod, 'id', true );

				$slug_field = get_posts( array(
					'post_type'      => '_pods_field',
					'posts_per_page' => 1,
					'nopaging'       => true,
					'post_parent'    => $pod['id'],
					'orderby'        => 'menu_order',
					'order'          => 'ASC',
					'meta_query'     => array(
						array(
							'key'   => 'type',
							'value' => 'slug',
						)
					)
				) );

				if ( ! empty( $slug_field ) ) {
					$slug_field = $slug_field[0];

					$info['field_slug'] = $info['pod_field_slug'] = $slug_field->post_name;
				}

				if ( 1 == pods_v( 'hierarchical', $pod, 0 ) ) {
					$parent_field = pods_v_sanitized( 'pod_parent', $pod, 'id', true );

					if ( ! empty( $parent_field ) && $pod->fields( $parent_field ) ) {
						$info['object_hierarchical'] = true;

						$info['pod_field_parent']    = $info['field_parent'] = $parent_field . '_select';
						$info['field_parent_select'] = '`' . $parent_field . '`.`id` AS `' . $info['field_parent'] . '`';
					}
				}
			}
		}

		if ( 0 === strpos( $object_type, 'post_type' ) || 'media' == $object_type || ( ! empty( $pod ) && in_array( $pod['type'], array( 'post_type', 'media' ) ) ) ) {
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

			if ( 'media' == $object_type ) {
				$object = 'attachment';
			}

			if ( empty( $name ) ) {
				$prefix = 'post_type-';

				// Make sure we actually have the prefix before trying anything with the name
				if ( 0 === strpos( $object_type, $prefix ) ) {
					$name = substr( $object_type, strlen( $prefix ), strlen( $object_type ) );
				}
			}

			if ( 'media' != $object_type ) {
				$object_type = 'post_type';
			}

			$post_type = pods_sanitize( ( empty( $object ) ? $name : $object ) );

			if ( 'attachment' == $post_type || 'media' == $object_type ) {
				$info['pod_table'] = $wpdb->prefix . 'pods_media';
			} else {
				$info['pod_table'] = $wpdb->prefix . 'pods_' . pods_clean_name( $post_type, true, false );
			}

			$post_type_object = get_post_type_object( $post_type );

			if ( is_object( $post_type_object ) && $post_type_object->hierarchical ) {
				$info['object_hierarchical'] = true;
			}

			$post_status = array( 'publish' );

			/**
			 * Default Post Status to query for.
			 *
			 * Use to change "default" post status from publish to any other status or statuses.
			 *
			 * @param  array  $post_status List of post statuses. Default is 'publish'
			 * @param  string $post_type  Post type of current object
			 * @param  array  $info       Array of information about the object.
			 * @param  string $object     Type of object
			 * @param  string $name       Name of pod to load
			 * @param  array  $pod        Array with Pod information. Result of Pods_API::load_pod()
			 * @param  array  $field      Array with field information
			 *
			 * @since unknown
			 */
			$post_status = apply_filters( 'pods_api_get_table_info_default_post_status', $post_status, $post_type, $info, $object_type, $object, $name, $pod, $field );

			$info['where'] = array(
				//'post_status' => '`t`.`post_status` IN ( "inherit", "publish" )', // @todo Figure out what statuses Attachments can be
				'post_type' => '`t`.`' . $info['field_type'] . '` = "' . $post_type . '"'
			);

			if ( 'post_type' == $object_type ) {
				$info['where_default'] = '`t`.`post_status` IN ( "' . implode( '", "', $post_status ) . '" )';
			}

			$info['orderby'] = '`t`.`menu_order`, `t`.`' . $info['field_index'] . '`, `t`.`post_date`';

			// WPML support
			if ( is_object( $sitepress ) && $sitepress->is_translated_post_type( $post_type ) && ! $icl_adjust_id_url_filter_off ) {
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
			} // Polylang support
			elseif ( is_object( $polylang ) && ! empty( $current_language ) && function_exists( 'pll_is_translated_post_type' ) && pll_is_translated_post_type( $post_type ) ) {
				$info['join']['polylang_languages'] = "
                    LEFT JOIN `{$wpdb->term_relationships}` AS `polylang_languages`
                        ON `polylang_languages`.`object_id` = `t`.`ID`
                            AND `polylang_languages`.`term_taxonomy_id` = {$current_language_tt_id}
                ";

				$info['where']['polylang_languages'] = "`polylang_languages`.`object_id` IS NOT NULL";
			}

			$info['object_fields'] = $this->get_wp_object_fields( $object_type, $pod );
		} elseif ( 0 === strpos( $object_type, 'taxonomy' ) || in_array( $object_type, array( 'nav_menu', 'post_format' ) ) || ( ! empty( $pod ) && 'taxonomy' == $pod['type'] ) ) {
			$info['table'] = $info['meta_table'] = $wpdb->terms;

			$info['join']['tt']          = "LEFT JOIN `{$wpdb->term_taxonomy}` AS `tt` ON `tt`.`term_id` = `t`.`term_id`";
			$info['field_id']            = $info['meta_field_id'] = 'term_id';
			$info['field_index']         = $info['meta_field_index'] = $info['meta_field_value'] = 'name';
			$info['field_slug']          = 'slug';
			$info['field_type']          = 'taxonomy';
			$info['field_parent']        = 'parent';
			$info['field_parent_select'] = '`tt`.`' . $info['field_parent'] . '`';

			if ( 'nav_menu' == $object_type ) {
				$object = 'nav_menu';
			} elseif ( 'post_format' == $object_type ) {
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

			// WPML Support
			if ( is_object( $sitepress ) && $sitepress->is_translated_taxonomy( $taxonomy ) && ! $icl_adjust_id_url_filter_off ) {
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
			} // Polylang support
			elseif ( is_object( $polylang ) && ! empty( $current_language ) && function_exists( 'pll_is_translated_taxonomy' ) && pll_is_translated_taxonomy( $taxonomy ) ) {
				$info['join']['polylang_languages'] = "
                    LEFT JOIN `{$wpdb->termmeta}` AS `polylang_languages`
                        ON `polylang_languages`.`term_id` = `t`.`term_id`
                            AND `polylang_languages`.`meta_value` = {$current_language_t_id}
                ";

				$info['where']['polylang_languages'] = "`polylang_languages`.`term_id` IS NOT NULL";
			}

			$info['object_fields'] = $this->get_wp_object_fields( $object_type, $pod );
		} elseif ( 'user' == $object_type || ( ! empty( $pod ) && 'user' == $pod['type'] ) ) {
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

			$info['object_fields'] = $this->get_wp_object_fields( $object_type, $pod );
		} elseif ( 'comment' == $object_type || ( ! empty( $pod ) && 'comment' == $pod['type'] ) ) {
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

			if ( 'comment' == $comment_type ) {
				$comment_type_clause = '( ' . $comment_type_clause . ' OR `t`.`' . $info['field_type'] . '` = "" )';
			}

			$info['where'] = array(
				'comment_approved' => '`t`.`comment_approved` = 1',
				'comment_type'     => $comment_type_clause
			);

			$info['orderby'] = '`t`.`' . $info['field_index'] . '` DESC, `t`.`' . $info['field_id'] . '`';
		} elseif ( in_array( $object_type, array( 'option', 'settings' ) ) || ( ! empty( $pod ) && 'settings' == $pod['type'] ) ) {
			$info['table']      = $wpdb->options;
			$info['meta_table'] = $wpdb->options;

			$info['field_id']    = 'option_id';
			$info['field_index'] = 'option_name';

			$info['meta_field_id']    = 'option_id';
			$info['meta_field_index'] = 'option_name';
			$info['meta_field_value'] = 'option_value';

			$info['orderby'] = '`t`.`' . $info['field_index'] . '` ASC';
		} elseif ( is_multisite() && ( in_array( $object_type, array( 'site_option', 'site_settings' ) ) || ( ! empty( $pod ) && 'site_settings' == $pod['type'] ) ) ) {
			$info['table']      = $wpdb->sitemeta;
			$info['meta_table'] = $wpdb->sitemeta;

			$info['field_id']    = 'site_id';
			$info['field_index'] = 'meta_key';

			$info['meta_field_id']    = 'site_id';
			$info['meta_field_index'] = 'meta_key';
			$info['meta_field_value'] = 'meta_value';

			$info['orderby'] = '`t`.`' . $info['field_index'] . '` ASC';
		} elseif ( is_multisite() && 'network' == $object_type ) { // Network = Site
			$info['table']      = $wpdb->site;
			$info['meta_table'] = $wpdb->sitemeta;

			$info['field_id']    = 'id';
			$info['field_index'] = 'domain';

			$info['meta_field_id']    = 'site_id';
			$info['meta_field_index'] = 'meta_key';
			$info['meta_field_value'] = 'meta_value';

			$info['orderby'] = '`t`.`' . $info['field_index'] . '` ASC, `t`.`path` ASC, `t`.`' . $info['field_id'] . '`';
		} elseif ( is_multisite() && 'site' == $object_type ) { // Site = Blog
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
		} elseif ( 'table' == $object_type || ( ! empty( $pod ) && 'table' == $pod['type'] ) ) {
			$info['table']     = ( empty( $object ) ? $name : $object );
			$info['pod_table'] = $wpdb->prefix . 'pods_' . $info['table'];

			if ( ! empty( $field ) ) {
				$info['table'] = $field['pick_table'];

				if ( ! empty( $field['pick_table_id'] ) ) {
					$info['field_id'] = $field['pick_table_id'];
				}

				if ( ! empty( $field['pick_table_index'] ) ) {
					$info['field_index'] = $info['meta_field_index'] = $info['meta_field_value'] = $field['pick_table_index'];
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
			$info['orderby'] = '`t`.`' . $info['field_index'] . '`, `t`.`' . $info['field_id'] . '`';
		}

		if ( ! empty( $pod ) && 'table' == $pod['storage'] && ! in_array( $object_type, array( 'pod', 'table' ) ) ) {
			$info['join']['d'] = 'LEFT JOIN `' . $info['pod_table'] . '` AS `d` ON `d`.`id` = `t`.`' . $info['field_id'] . '`';
			//$info[ 'select' ] .= ', `d`.*';
		}

		if ( ! empty( $pod ) ) {
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

		if ( empty( $original_pod ) ) {
			$info['pod'] = $pod;
		}

	    self::$table_info_cache[ $transient ] = apply_filters( 'pods_api_get_table_info', $info, $object_type, $object, $name, $pod, $field, $this );

		if ( isset( self::$table_info_cache[ $transient ]['pod'] ) && ! empty( $original_pod ) ) {
			unset( self::$table_info_cache[ $transient ]['pod'] );
		}

        return self::$table_info_cache[ $transient ];

	}

	/**
	 * Export a package
	 *
	 * $params['pod'] string Pod Type IDs to export
	 * $params['template'] string Template IDs to export
	 * $params['podpage'] string Pod Page IDs to export
	 * $params['helper'] string Helper IDs to export
	 *
	 * @param array $params An associative array of parameters
	 *
	 * @return array|bool
	 *
	 * @since      1.9.0
	 * @deprecated 2.0
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
	 * @deprecated 2.0
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
	 * @deprecated 2.0
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
	 * @param array|bool|string $data   (optional) An associative array containing a package, or the json encoded package
	 * @param bool              $output (optional)
	 *
	 * @return array|bool
	 *
	 * @since      1.9.0
	 * @deprecated 2.0
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

		if ( 'csv' == $format && ! is_array( $import_data ) ) {
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

		$simple_tableless_objects = Pods_Form::simple_tableless_objects();

		foreach ( $import_data as $key => $data_row ) {
			$data = array();

			// Loop through each field (use $fields so only valid fields get parsed)
			foreach ( $fields as $field_name => $field_data ) {
				if ( ! isset( $data_row[$field_name] ) && ! isset( $data_row[$field_data['label']] ) ) {
					continue;
				}

				$field_id    = $field_data['id'];
				$type        = $field_data['type'];
				$pick_object = isset( $field_data['pick_object'] ) ? $field_data['pick_object'] : '';
				$pick_val    = isset( $field_data['pick_val'] ) ? $field_data['pick_val'] : '';

				if ( isset( $data_row[$field_name] ) ) {
					$field_value = $data_row[$field_name];
				} else {
					$field_value = $data_row[$field_data['label']];
				}

				if ( null !== $field_value && false !== $field_value && '' !== $field_value ) {
					if ( 'pick' == $type || in_array( $type, Pods_Form::file_field_types() ) ) {
						$field_values = is_array( $field_value ) ? $field_value : array( $field_value );
						$pick_values  = array();

						foreach ( $field_values as $pick_value ) {
							if ( in_array( $type, Pods_Form::file_field_types() ) || 'media' == $pick_object ) {
								$where = "`guid` = '" . pods_sanitize( $pick_value ) . "'";

								if ( 0 < pods_absint( $pick_value ) && false !== $numeric_mode ) {
									$where = "`ID` = " . pods_absint( $pick_value );
								}

								$result = pods_query( "SELECT `ID` AS `id` FROM `{$wpdb->posts}` WHERE `post_type` = 'attachment' AND {$where} ORDER BY `ID`", $this );

								if ( ! empty( $result ) ) {
									$pick_values[] = $result[0]->id;
								}
							} // @todo This could and should be abstracted better and simplified
							elseif ( 'pick' == $type ) {
								$related_pod = false;

								if ( 'pod' == $pick_object ) {
									$related_pod = $this->load_pod( array( 'name' => $pick_val ), __METHOD__ );
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

									$result = pods_query( "SELECT `t`.`term_id` AS `id` FROM `{$wpdb->term_taxonomy}` AS `tt` LEFT JOIN `{$wpdb->terms}` AS `t` ON `t`.`term_id` = `tt`.`term_id` WHERE `taxonomy` = '{$pick_val}' AND {$where} ORDER BY `t`.`term_id`", $this );

									if ( ! empty( $result ) ) {
										$pick_values[] = $result[0]->id;
									}
								} elseif ( in_array( 'post_type', array( $pick_object, $related_pod['type'] ) ) || in_array( 'media', array( $pick_object, $related_pod['type'] ) ) ) {
									$where = "`post_title` = '" . pods_sanitize( $pick_value ) . "'";

									if ( 0 < pods_absint( $pick_value ) && false !== $numeric_mode ) {
										$where = "`ID` = " . pods_absint( $pick_value );
									}

									$result = pods_query( "SELECT `ID` AS `id` FROM `{$wpdb->posts}` WHERE `post_type` = '{$pick_val}' AND {$where} ORDER BY `ID`", $this );

									if ( ! empty( $result ) ) {
										$pick_values[] = $result[0]->id;
									}
								} elseif ( in_array( 'user', array( $pick_object, $related_pod['type'] ) ) ) {
									$where = "`user_login` = '" . pods_sanitize( $pick_value ) . "'";

									if ( 0 < pods_absint( $pick_value ) && false !== $numeric_mode ) {
										$where = "`ID` = " . pods_absint( $pick_value );
									}

									$result = pods_query( "SELECT `ID` AS `id` FROM `{$wpdb->users}` WHERE {$where} ORDER BY `ID`", $this );

									if ( ! empty( $result ) ) {
										$pick_values[] = $result[0]->id;
									}
								} elseif ( in_array( 'comment', array( $pick_object, $related_pod['type'] ) ) ) {
									$where = "`comment_ID` = " . pods_absint( $pick_value );

									$result = pods_query( "SELECT `comment_ID` AS `id` FROM `{$wpdb->comments}` WHERE {$where} ORDER BY `ID`", $this );

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

									$result = pods_query( "SELECT `" . $related_pod['field_id'] . "` AS `id` FROM `" . $related_pod['table'] . "` WHERE {$where} ORDER BY `" . $related_pod['field_id'] . "`", $this );

									if ( ! empty( $result ) ) {
										$pick_values[] = $result[0]->id;
									}
								}
							}
						}

						$field_value = implode( ',', $pick_values );
					}

					$data[$field_name] = $field_value;
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
			$id = (int) $pod->id();

			$data[ $id ] = $this->export_pod_item( $params, $pod );
		}

		$data = $this->do_hook( 'export', $data, $pod->pod, $pod );

		return $data;
	}

	/**
	 * Convert CSV to a PHP array
	 *
	 * @param string $data The CSV input
	 * @param string $delimiter
	 *
	 * @return array
	 * @since      1.7.1
	 *
	 * @deprecated 2.3.5
	 */
	public function csv_to_php( $data, $delimiter = ',' ) {
		pods_deprecated( "Pods_API->csv_to_php", '2.3.5' );

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
	 * @since 2.0
	 */
	public function cache_flush_pods( $pod = null ) {
		/**
		 * @var $wpdb wpdb
		 */
		global $wpdb;

		pods_transient_clear( 'pods' );
		pods_transient_clear( 'pods_components' );

		if ( null !== $pod && ( is_array( $pod ) || is_object( $pod ) ) ) {
			if ( pods_api_cache() ) {
				pods_transient_clear( 'pods_pod_' . $pod['name'] );
			}

			pods_cache_clear( $pod['name'], 'pods-class' );

			if ( in_array( $pod['type'], array( 'post_type', 'taxonomy' ) ) ) {
				pods_transient_clear( 'pods_wp_cpt_ct' );
			}
		} else {
			pods_transient_clear( 'pods_wp_cpt_ct' );
		}

		$wpdb->query( "DELETE FROM `{$wpdb->options}` WHERE `option_name` LIKE '_transient_pods%'" );
		$wpdb->query( "DELETE FROM `{$wpdb->options}` WHERE `option_name` LIKE '_transient_timeout_pods%'" );

		pods_cache_clear( true );

		if ( null !== $pod && is_object( $pod ) ) {
			pods_transient_set( 'pods_pod_' . $pod['name'], $pod );
		}

		delete_option( 'pods_flush_rewrites' );
		add_option( 'pods_flush_rewrites', 1, '', 'yes' );
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
	 * @since 2.0
	 */
	public function process_form( $params, $obj = null, $fields = null, $thank_you = null ) {
		$this->display_errors = false;

		$form = null;

		$nonce = pods_v( '_pods_nonce', $params );
		$pod   = pods_v( '_pods_pod', $params );
		$id    = pods_v( '_pods_id', $params );
		$uri   = pods_v( '_pods_uri', $params );
		$form  = pods_v( '_pods_form', $params );

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
			$data[$field] = pods_v( 'pods_field_' . $field, $params, '' );
		}

		$params = array(
			'pod'  => $pod,
			'id'   => $id,
			'data' => $data,
			'from' => 'process_form'
		);

		$id = $this->save_pod_item( $params );

		if ( 0 < $id && ! empty( $thank_you ) ) {
			$thank_you = str_replace( 'X_ID_X', $id, $thank_you );

			pods_redirect( $thank_you );
		}

		return $id;
	}

	/**
	 * Handle filters / actions for the class
	 *
	 * @since 2.0
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
	 * @since 2.0
	 */
	public function __get( $name ) {
		$name = (string) $name;

		if ( ! isset( $this->deprecated ) ) {
			$this->deprecated = new Pods_API_Deprecated( $this );
		}

		$var = null;

		if ( isset( $this->deprecated->{$name} ) ) {
			pods_deprecated( "Pods_API->{$name}", '2.0' );

			$var = $this->deprecated->{$name};
		} else {
			pods_deprecated( "Pods_API->{$name}", '2.0' );
		}

		return $var;
	}

	/**
	 * Handle methods that have been deprecated
	 *
	 * @since 2.0
	 */
	public function __call( $name, $args ) {
		$name = (string) $name;

		if ( ! isset( $this->deprecated ) ) {
			$this->deprecated = new Pods_API_Deprecated( $this );
		}

		if ( method_exists( $this->deprecated, $name ) ) {
			return call_user_func_array( array( $this->deprecated, $name ), $args );
		} else {
			pods_deprecated( "Pods_API::{$name}", '2.0' );
		}

		return null;
	}
}
