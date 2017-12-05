<?php
/**
 * @package Pods
 */
class PodsMeta {

    /**
     * @var PodsMeta
     */
    static $instance = null;

    /**
     * @var PodsAPI
     */
    private $api;

    /**
     * @var Pods
     */
    private static $current_pod;

    /**
     * @var array
     */
    private static $current_pod_data;

    /**
     * @var Pods
     */
    private static $current_field_pod;

    /**
     * @var int
     */
    public static $object_identifier = -1;

    /**
     * @var array
     */
    public static $advanced_content_types = array();

    /**
     * @var array
     */
    public static $post_types = array();

    /**
     * @var array
     */
    public static $taxonomies = array();

    /**
     * @var array
     */
    public static $media = array();

    /**
     * @var array
     */
    public static $user = array();

    /**
     * @var array
     */
    public static $comment = array();

    /**
     * @var array
     */
    public static $settings = array();

    /**
     * @var array
     */
    public static $queue = array();

    /**
     * @var array
     */
    public static $groups = array();

    /**
     * @var array
     */
    public static $old_post_status = array();

    /**
     * Singleton handling for a basic pods_meta() request
     *
     * @return \PodsMeta
     *
     * @since 2.3.5
     */
    public static function init () {
        if ( !is_object( self::$instance ) )
            self::$instance = new PodsMeta();

        return self::$instance;
    }

    /**
     * @return \PodsMeta
     *
     * @since 2.0
     */
    function __construct () {

    }

    /**
     * @return \PodsMeta
     */
    public function core () {
        self::$advanced_content_types = pods_api()->load_pods( array( 'type' => 'pod' ) );
        self::$post_types = pods_api()->load_pods( array( 'type' => 'post_type' ) );
        self::$taxonomies = pods_api()->load_pods( array( 'type' => 'taxonomy' ) );
        self::$media = pods_api()->load_pods( array( 'type' => 'media' ) );
        self::$user = pods_api()->load_pods( array( 'type' => 'user' ) );
        self::$comment = pods_api()->load_pods( array( 'type' => 'comment' ) );
        self::$settings = pods_api()->load_pods( array( 'type' => 'settings' ) );

        // Handle Post Type Editor (needed for Pods core)

        // Loop through and add meta boxes for individual types (can't use this, Tabify doesn't pick it up)
        /*
        foreach ( self::$post_types as $post_type ) {
            $post_type_name = $post_type[ 'name' ];

            if ( !empty( $post_type[ 'object' ] ) )
                $post_type_name = $post_type[ 'object' ];

            add_action( 'add_meta_boxes_' . $post_type_name, array( $this, 'meta_post_add' ) );
        }
        */

        add_action( 'add_meta_boxes', array( $this, 'meta_post_add' ) );
        add_action( 'transition_post_status', array( $this, 'save_post_detect_new' ), 10, 3 );
        add_action( 'save_post', array( $this, 'save_post' ), 10, 3 );
        add_filter( 'wp_insert_post_data', array( $this, 'save_post_track_changed_fields' ), 10, 2 );

        if ( apply_filters( 'pods_meta_handler', true, 'post' ) ) {
            // Handle *_post_meta
			if ( apply_filters( 'pods_meta_handler_get', true, 'post' ) ) {
            	add_filter( 'get_post_metadata', array( $this, 'get_post_meta' ), 10, 4 );
			}

            if ( !pods_tableless() ) {
                add_filter( 'add_post_metadata', array( $this, 'add_post_meta' ), 10, 5 );
                add_filter( 'update_post_metadata', array( $this, 'update_post_meta' ), 10, 5 );
                add_filter( 'delete_post_metadata', array( $this, 'delete_post_meta' ), 10, 5 );
            }
        }

        add_action( 'delete_post', array( $this, 'delete_post' ), 10, 1 );

        if ( !empty( self::$taxonomies ) ) {
			$has_fields = false;

            // Handle Taxonomy Editor
            foreach ( self::$taxonomies as $taxonomy ) {
				if ( empty( $taxonomy[ 'fields' ] ) ) {
					continue;
				}

				$has_fields = true;

                $taxonomy_name = $taxonomy[ 'name' ];

                if ( !empty( $taxonomy[ 'object' ] ) )
                    $taxonomy_name = $taxonomy[ 'object' ];

                add_action( $taxonomy_name . '_edit_form_fields', array( $this, 'meta_taxonomy' ), 10, 2 );
                add_action( $taxonomy_name . '_add_form_fields', array( $this, 'meta_taxonomy' ), 10, 1 );
            }

			if ( $has_fields ) {
				// Handle Term Editor
				add_action( 'edited_term', array( $this, 'save_taxonomy' ), 10, 3 );
				add_action( 'create_term', array( $this, 'save_taxonomy' ), 10, 3 );
				add_action( 'edit_terms', array( $this, 'save_taxonomy_track_changed_fields' ), 10, 2 );

				if ( apply_filters( 'pods_meta_handler', true, 'term' ) ) {
					// Handle *_term_meta
					if ( apply_filters( 'pods_meta_handler_get', true, 'term' ) ) {
						add_filter( 'get_term_metadata', array( $this, 'get_term_meta' ), 10, 4 );
					}

					if ( !pods_tableless() ) {
						add_filter( 'add_term_metadata', array( $this, 'add_term_meta' ), 10, 5 );
						add_filter( 'update_term_metadata', array( $this, 'update_term_meta' ), 10, 5 );
						add_filter( 'delete_term_metadata', array( $this, 'delete_term_meta' ), 10, 5 );
					}
				}
			}
        }

        /**
         * Fires after a previously shared taxonomy term is split into two separate terms.
         *
         * @since 4.2.0
         *
         * @param int    $term_id          ID of the formerly shared term.
         * @param int    $new_term_id      ID of the new term created for the $term_taxonomy_id.
         * @param int    $term_taxonomy_id ID for the term_taxonomy row affected by the split.
         * @param string $taxonomy         Taxonomy for the split term.
         */
        add_action( 'split_shared_term', array( $this, 'split_shared_term' ), 10, 4 );

        // Handle Delete
        add_action( 'delete_term_taxonomy', array( $this, 'delete_taxonomy' ), 10, 1 );

        if ( !empty( self::$media ) ) {
            add_action( 'add_meta_boxes', array( $this, 'meta_post_add' ) );
            add_action( 'wp_ajax_save-attachment-compat', array( $this, 'save_media_ajax' ), 0 );

            add_filter( 'attachment_fields_to_edit', array( $this, 'meta_media' ), 10, 2 );

            add_filter( 'attachment_fields_to_save', array( $this, 'save_media' ), 10, 2 );
            add_filter( 'wp_update_attachment_metadata', array( $this, 'save_media' ), 10, 2 );
            add_filter( 'wp_insert_attachment_data', array( $this, 'save_post_track_changed_fields' ), 10, 2 );

            if ( apply_filters( 'pods_meta_handler', true, 'post' ) ) {
                // Handle *_post_meta
                if ( !has_filter( 'get_post_metadata', array( $this, 'get_post_meta' ) ) ) {
                    if ( apply_filters( 'pods_meta_handler_get', true, 'post' ) ) {
                        add_filter( 'get_post_metadata', array( $this, 'get_post_meta' ), 10, 4 );
                    }

                    if ( !pods_tableless() ) {
                        add_filter( 'add_post_metadata', array( $this, 'add_post_meta' ), 10, 5 );
                        add_filter( 'update_post_metadata', array( $this, 'update_post_meta' ), 10, 5 );
                        add_filter( 'delete_post_metadata', array( $this, 'delete_post_meta' ), 10, 5 );
                    }
                }
            }
        }

        // Handle Delete
        add_action( 'delete_attachment', array( $this, 'delete_media' ), 10, 1 );

        if ( !empty( self::$user ) ) {
            // Handle User Editor
            add_action( 'show_user_profile', array( $this, 'meta_user' ) );
            add_action( 'edit_user_profile', array( $this, 'meta_user' ) );
            add_action( 'user_register', array( $this, 'save_user' ) );
            add_action( 'profile_update', array( $this, 'save_user' ), 10, 2 );
            add_filter( 'pre_user_login', array( $this, 'save_user_track_changed_fields' ));

            if ( apply_filters( 'pods_meta_handler', true, 'user' ) ) {
                // Handle *_user_meta
				if ( apply_filters( 'pods_meta_handler_get', true, 'user' ) ) {
                	add_filter( 'get_user_metadata', array( $this, 'get_user_meta' ), 10, 4 );
				}

                if ( !pods_tableless() ) {
                    add_filter( 'add_user_metadata', array( $this, 'add_user_meta' ), 10, 5 );
                    add_filter( 'update_user_metadata', array( $this, 'update_user_meta' ), 10, 5 );
                    add_filter( 'delete_user_metadata', array( $this, 'delete_user_meta' ), 10, 5 );
                }
            }
        }

        // Handle Delete
        add_action( 'delete_user', array( $this, 'delete_user' ), 10, 1 );

        if ( !empty( self::$comment ) ) {
            // Handle Comment Form / Editor
            add_action( 'comment_form_logged_in_after', array( $this, 'meta_comment_new_logged_in' ), 10, 2 );
            add_filter( 'comment_form_default_fields', array( $this, 'meta_comment_new' ) );
            add_action( 'add_meta_boxes_comment', array( $this, 'meta_comment_add' ) );
            add_filter( 'pre_comment_approved', array( $this, 'validate_comment' ), 10, 2 );
            add_action( 'comment_post', array( $this, 'save_comment' ) );
            add_action( 'edit_comment', array( $this, 'save_comment' ) );
            add_action( 'wp_update_comment_data', array( $this, 'save_comment_track_changed_fields' ), 10, 3 );

            if ( apply_filters( 'pods_meta_handler', true, 'comment' ) ) {
                // Handle *_comment_meta
                add_filter( 'get_comment_metadata', array( $this, 'get_comment_meta' ), 10, 4 );

                if ( !pods_tableless() ) {
                    add_filter( 'add_comment_metadata', array( $this, 'add_comment_meta' ), 10, 5 );
                    add_filter( 'update_comment_metadata', array( $this, 'update_comment_meta' ), 10, 5 );
                    add_filter( 'delete_comment_metadata', array( $this, 'delete_comment_meta' ), 10, 5 );
                }
            }
        }

        // Handle Delete
        add_action( 'delete_comment', array( $this, 'delete_comment' ), 10, 1 );

        // @todo Patch core to provide $option back in filters, patch core to add filter pre_add_option to add_option
        /*if ( !empty( self::$settings ) ) {
            foreach ( self::$settings as $setting_pod ) {
                foreach ( $setting_pod[ 'fields' ] as $option ) {
                    add_filter( 'pre_option_' . $setting_pod[ 'name' ] . '_' . $option[ 'name' ], array( $this, 'get_option' ), 10, 1 );
                    add_action( 'add_option_' . $setting_pod[ 'name' ] . '_' . $option[ 'name' ], array( $this, 'add_option' ), 10, 2 );
                    add_filter( 'pre_update_option_' . $setting_pod[ 'name' ] . '_' . $option[ 'name' ], array( $this, 'update_option' ), 10, 2 );
                }
            }
        }*/

        if ( is_admin() )
            $this->integrations();

        add_action( 'init', array( $this, 'enqueue' ), 9 );

        if ( function_exists( 'pll_current_language' ) )
            add_action( 'init', array( $this, 'cache_pods' ), 101 );

        do_action( 'pods_meta_init' );

        return $this;
    }

    public static function enqueue () {
        foreach ( self::$queue as $type => $objects ) {
            foreach ( $objects as $pod_name => $pod ) {
                pods_transient_set( 'pods_pod_' . $pod_name, $pod );
            }

            self::$$type = array_merge( self::$$type, $objects );
        }
    }

    /**
     * Go back through and cache the Pods now that Polylang has loaded
     */
    public function cache_pods () {
        self::$advanced_content_types = pods_api()->load_pods( array( 'type' => 'pod', 'refresh' => true ) );
        self::$post_types = pods_api()->load_pods( array( 'type' => 'post_type', 'refresh' => true ) );
        self::$taxonomies = pods_api()->load_pods( array( 'type' => 'taxonomy', 'refresh' => true ) );
        self::$media = pods_api()->load_pods( array( 'type' => 'media', 'refresh' => true ) );
        self::$user = pods_api()->load_pods( array( 'type' => 'user', 'refresh' => true ) );
        self::$comment = pods_api()->load_pods( array( 'type' => 'comment', 'refresh' => true ) );
        self::$settings = pods_api()->load_pods( array( 'type' => 'settings', 'refresh' => true ) );
    }

    public function register ( $type, $pod ) {
        $pod_type = $type;

        if ( 'post_type' == $type )
            $type = 'post_types';
        elseif ( 'taxonomy' == $type )
            $type = 'taxonomies';
        elseif ( 'pod' == $type )
            $type = 'advanced_content_types';

        if ( !isset( self::$queue[ $type ] ) )
            self::$queue[ $type ] = array();

        if ( is_array( $pod ) && !empty( $pod ) && !isset( $pod[ 'name' ] ) ) {
            $data = array();

            foreach ( $pod as $p ) {
                $data[] = $this->register( $type, $p );
            }

            return $data;
        }

        $pod[ 'type' ] = $pod_type;
        $pod = pods_api()->save_pod( $pod, false, false );

        if ( !empty( $pod ) ) {
            self::$object_identifier--;

            self::$queue[ $type ][ $pod[ 'name' ] ] = $pod;

            return $pod;
        }

        return false;
    }

    public function register_field ( $pod, $field ) {
        if ( is_array( $pod ) && !empty( $pod ) && !isset( $pod[ 'name' ] ) ) {
            $data = array();

            foreach ( $pod as $p ) {
                $data[] = $this->register_field( $p, $field );
            }

            return $data;
        }

        if ( empty( self::$current_pod_data ) || !is_object( self::$current_pod_data ) || self::$current_pod_data[ 'name' ] != $pod )
            self::$current_pod_data = pods_api()->load_pod( array( 'name' => $pod ), false );

        $pod = self::$current_pod_data;

        if ( !empty( $pod ) ) {
            $type = $pod[ 'type' ];

            if ( 'post_type' == $pod[ 'type' ] )
                $type = 'post_types';
            elseif ( 'taxonomy' == $pod[ 'type' ] )
                $type = 'taxonomies';
            elseif ( 'pod' == $pod[ 'type' ] )
                $type = 'advanced_content_types';

            if ( !isset( self::$queue[ $pod[ 'type' ] ] ) )
                self::$queue[ $type ] = array();

            $field = pods_api()->save_field( $field, false, false, $pod[ 'id' ] );

            if ( !empty( $field ) ) {
                $pod[ 'fields' ][ $field[ 'name' ] ] = $field;

                self::$queue[ $type ][ $pod[ 'name' ] ] = $pod;

                return $field;
            }
        }

        return false;
    }

	public function integrations () {

		// `ac_is_version_gte` is since AC 3.0+
		if ( ! function_exists( 'ac_is_version_gte' ) ) {
			// Codepress Admin Columns < 2.x
			add_filter( 'cac/storage_model/meta_keys', array( $this, 'cpac_meta_keys' ), 10, 2 );
			add_filter( 'cac/post_types', array( $this, 'cpac_post_types' ), 10, 1 );
			add_filter( 'cac/column/meta/value', array( $this, 'cpac_meta_value' ), 10, 3 );
		} else {
			// Codepress Admin Columns 3.x +
			add_filter( 'ac/column/custom_field/meta_keys', array( $this, 'cpac_meta_keys' ), 10, 2 );
			add_filter( 'ac/post_types', array( $this, 'cpac_post_types' ), 10, 1 );
			add_filter( 'ac/column/value', array( $this, 'cpac_meta_value' ), 10, 3 );
		}
	}

	/**
	 * Admin Columns: Remove internal meta keys + add existing (public) Pod field keys.
	 *
	 * @param array $meta_fields
	 * @param \AC_Settings_Column_CustomField $storage_model
	 *
	 * @return array
	 */
	public function cpac_meta_keys ( $meta_fields, $storage_model ) {
		$object_type = 'post_type';
		$object = null;
		$obj = null;

		if ( ! method_exists( $storage_model, 'get_column' ) ) {
			// Codepress Admin Columns < 2.x
			$object = $storage_model->key;
			$type = $storage_model->type;
		} else {
			// Codepress Admin Columns 3.x +
			$obj = $storage_model->get_column();
			$object = $obj->get_list_screen()->get_key();
			$type = $obj->get_list_screen()->get_meta_type();
		}

		if ( in_array( $type, array( 'wp-links', 'link' ), true ) ) {
			$object_type = $object = 'link';
		}
		elseif ( in_array( $type, array( 'wp-media', 'media' ), true ) ) {
			$object_type = $object = 'media';
		}
		elseif ( in_array( $type, array( 'wp-users', 'user' ), true ) ) {
			$object_type = $object = 'user';
		}
		elseif ( in_array( $type, array( 'wp-comments', 'comment' ), true ) ) {
			$object_type = $object = 'comment';
		}
		elseif ( 'taxonomy' === $type ) {
			$object_type = 'taxonomy';
			if ( ! $obj ) {
				// Codepress Admin Columns < 2.x
				$object = $storage_model->taxonomy;
			} else {
				// Codepress Admin Columns 3.x +
				$object = $obj->get_taxonomy();
			}
		}

		if ( empty( self::$current_pod_data ) || !is_object( self::$current_pod_data ) || self::$current_pod_data[ 'name' ] != $object )
			self::$current_pod_data = pods_api()->load_pod( array( 'name' => $object ), false );

		$pod = self::$current_pod_data;

		// Add Pods fields
		if ( !empty( $pod ) && $object_type == $pod[ 'type' ] ) {
			foreach ( $pod[ 'fields' ] as $field => $field_data ) {
				if ( !is_array( $meta_fields ) )
					$meta_fields = array();

				if ( !in_array( $field, $meta_fields ) )
					$meta_fields[] = $field;
			}
		}

		// Remove internal Pods fields
		if ( is_array( $meta_fields ) ) {
			foreach ( $meta_fields as $k => $meta_field ) {
				if ( 0 === strpos( $meta_field, '_pods_' ) )
					unset( $meta_fields[ $k ] );
			}
		}

		return $meta_fields;
	}

	/**
	 * Admin Columns: Remove internal Pods post types.
	 *
	 * @param  array $post_types
	 * @return array
	 */
	public function cpac_post_types ( $post_types ) {

		foreach ( $post_types as $post_type => $post_type_name ) {
			if ( 0 === strpos( $post_type, '_pods_' ) || 0 === strpos( $post_type_name, '_pods_' ) )
				unset( $post_types[ $post_type ] );
		}

		return $post_types;
	}

	/**
	 * Admin Columns: For custom field column types.
	 *
	 * @param mixed $meta
	 * @param int $id
	 * @param \AC_Column $obj
	 *
	 * @return mixed
	 */
	public function cpac_meta_value ( $meta, $id, $obj ) {
		$tableless_field_types = PodsForm::tableless_field_types();

		$object_type = 'post_type';
		$object = null;
		$type = null;

		if ( ! method_exists( $obj, 'get_list_screen' ) ) {
			// Codepress Admin Columns < 2.x
			$object = $obj->storage_model->key;
			$type = $obj->storage_model->type;
		} else {
			// Codepress Admin Columns 3.x +
			$object = $obj->get_list_screen()->get_key();
			$type = $obj->get_list_screen()->get_meta_type();
		}

		if ( in_array( $type, array( 'wp-links', 'link' ), true ) ) {
			$object_type = $object = 'link';
		}
		elseif ( in_array( $type, array( 'wp-media', 'media' ), true ) ) {
			$object_type = $object = 'media';
		}
		elseif ( in_array( $type, array( 'wp-users', 'user' ), true ) ) {
			$object_type = $object = 'user';
		}
		elseif ( in_array( $type, array( 'wp-comments', 'comment' ), true ) ) {
			$object_type = $object = 'comment';
		}
		elseif ( 'taxonomy' === $type ) {
			$object_type = 'taxonomy';
			if ( ! method_exists( $obj, 'get_taxonomy' ) ) {
				// Codepress Admin Columns < 2.x
				$object = $obj->storage_model->taxonomy;
			} else {
				// Codepress Admin Columns 3.x +
				$object = $obj->get_taxonomy();
			}
		}

		$field = ( "cpachidden" === substr( $obj->get_option( 'field' ), 0, 10 ) ) ? str_replace( 'cpachidden', '', $obj->get_option( 'field' ) ) : $obj->get_option( 'field' );
		$field_type = $obj->get_option( 'field_type' );

		if ( empty( self::$current_pod_data ) || ! is_object( self::$current_pod_data ) || self::$current_pod_data['name'] !== $object )
			self::$current_pod_data = pods_api()->load_pod( array( 'name' => $object ), false );

		$pod = self::$current_pod_data;

		// Add Pods fields
		if ( !empty( $pod ) && isset( $pod['fields'][ $field ] ) ) {
			if ( in_array( $pod['type'], array( 'post_type', 'media', 'taxonomy', 'user', 'comment', 'media' ), true ) && ( ! empty( $field_type ) || in_array( $pod['fields'][ $field ]['type'], $tableless_field_types, true ) ) ) {
				$metadata_type = $pod['type'];

				if ( in_array( $metadata_type, array( 'post_type', 'media' ), true ) ) {
					$metadata_type = 'post';
				} elseif ( 'taxonomy' === $metadata_type ) {
					$metadata_type = 'term';
				}

				if ( 'term' === $metadata_type && ! function_exists( 'get_term_meta' ) ) {
					$podterms = pods( $pod['name'], $id );

					$meta = $podterms->field( $field );
				} else {
					$meta = get_metadata( $metadata_type, $id, $field, ( 'array' !== $field_type ) );
				}
			}
			elseif ( 'taxonomy' === $pod['type'] ) {
				$podterms = pods( $pod['name'], $id );

				$meta = $podterms->field( $field );
			}

			$meta = PodsForm::field_method( $pod['fields'][ $field ]['type'], 'ui', $id, $meta, $field, array_merge( $pod['fields'][ $field ], $pod['fields'][ $field ]['options'] ), $pod['fields'], $pod );
		}

		return $meta;
	}

    /**
     * Add a meta group of fields to add/edit forms
     *
     * @param string|array $pod The pod or type of element to attach the group to.
     * @param string $label Title of the edit screen section, visible to user.
     * @param string|array $fields Either a comma separated list of text fields or an associative array containing field infomration.
     * @param string $context (optional) The part of the page where the edit screen section should be shown ('normal', 'advanced', or 'side').
     * @param string $priority (optional) The priority within the context where the boxes should show ('high', 'core', 'default' or 'low').
     *
     * @since 2.0
     *
     * @return mixed|void
     */
    public function group_add ( $pod, $label, $fields, $context = 'normal', $priority = 'default' ) {
        if ( is_array( $pod ) && !empty( $pod ) && !isset( $pod[ 'name' ] ) ) {
            foreach ( $pod as $p ) {
                $this->group_add( $p, $label, $fields, $context, $priority );
            }

            return true;
        }

        if ( !is_array( $pod ) ) {
            if ( empty( self::$current_pod_data ) || !is_object( self::$current_pod_data ) || self::$current_pod_data[ 'name' ] != $pod )
                self::$current_pod_data = pods_api()->load_pod( array( 'name' => $pod ), false );

            if ( !empty( self::$current_pod_data ) )
                $pod = self::$current_pod_data;
            else {
                $type = 'post_type';

                if ( in_array( $pod, array( 'media', 'user', 'comment' ) ) )
                    $type = $pod;

                $pod = array(
                    'name' => $pod,
                    'type' => $type
                );
            }
        }

        if ( is_array( $pod ) && !isset( $pod[ 'id' ] ) ) {
            $defaults = array(
                'name' => '',
                'type' => 'post_type'
            );

            $pod = array_merge( $defaults, $pod );
        }

        if ( 'post' == $pod[ 'type' ] )
            $pod[ 'type' ] = 'post_type';

        if ( empty( $pod[ 'name' ] ) && isset( $pod[ 'object' ] ) && !empty( $pod[ 'object' ] ) )
            $pod[ 'name' ] = $pod[ 'object' ];
        elseif ( !isset( $pod[ 'object' ] ) || empty( $pod[ 'object' ] ) )
            $pod[ 'object' ] = $pod[ 'name' ];

        if ( empty( $pod[ 'object' ] ) )
            return pods_error( __( 'Object required to add a Pods meta group', 'pods' ) );

        $object_name = $pod[ 'object' ];

        if ( 'pod' == $pod[ 'type' ] )
            $object_name = $pod[ 'name' ];

        if ( !isset( self::$groups[ $pod[ 'type' ] ] ) )
            self::$groups[ $pod[ 'type' ] ] = array();

        if ( !isset( self::$groups[ $pod[ 'type' ] ][ $object_name ] ) )
            self::$groups[ $pod[ 'type' ] ][ $object_name ] = array();

        $_fields = array();

        if ( !is_array( $fields ) )
            $fields = explode( ',', $fields );

        foreach ( $fields as $k => $field ) {
            $name = $k;

            $defaults = array(
                'name' => $name,
                'label' => $name,
                'type' => 'text'
            );

            if ( !is_array( $field ) ) {
                $name = trim( $field );

                $field = array(
                    'name' => $name,
                    'label' => $name
                );
            }

            $field = array_merge( $defaults, $field );

            $field[ 'name' ] = trim( $field[ 'name' ] );

            if ( isset( $pod[ 'fields' ] ) && isset( $pod[ 'fields' ][ $field[ 'name' ] ] ) )
                $field = array_merge( $field, $pod[ 'fields' ][ $field[ 'name' ] ] );

            $_fields[ $k ] = $field;
        }

        // Setup field options
        $fields = PodsForm::fields_setup( $_fields );

        $group = array(
            'pod' => $pod,
            'label' => $label,
            'fields' => $fields,
            'context' => $context,
            'priority' => $priority
        );

        // Filter group data, pass vars separately for reference down the line (in case array changed by other filter)
        $group = apply_filters( 'pods_meta_group_add_' . $pod[ 'type' ] . '_' . $object_name, $group, $pod, $label, $fields );
        $group = apply_filters( 'pods_meta_group_add_' . $pod[ 'type' ], $group, $pod, $label, $fields );
        $group = apply_filters( 'pods_meta_group_add', $group, $pod, $label, $fields );

        self::$groups[ $pod[ 'type' ] ][ $object_name ][] = $group;

        // Hook it up!
        if ( 'post_type' == $pod[ 'type' ] ) {
            if ( !has_action( 'add_meta_boxes', array( $this, 'meta_post_add' ) ) )
                add_action( 'add_meta_boxes', array( $this, 'meta_post_add' ) );

            /*if ( !has_action( 'save_post', array( $this, 'save_post' ), 10, 3 ) )
                add_action( 'save_post', array( $this, 'save_post' ), 10, 3 );*/
        }
        elseif ( 'taxonomy' == $pod[ 'type' ] ) {
            if ( !has_action( $pod[ 'object' ] . '_edit_form_fields', array( $this, 'meta_taxonomy' ), 10, 2 ) ) {
                add_action( $pod[ 'object' ] . '_edit_form_fields', array( $this, 'meta_taxonomy' ), 10, 2 );
                add_action( $pod[ 'object' ] . '_add_form_fields', array( $this, 'meta_taxonomy' ), 10, 1 );
            }

            if ( !has_action( 'edited_term', array( $this, 'save_taxonomy' ), 10, 3 ) ) {
                add_action( 'edited_term', array( $this, 'save_taxonomy' ), 10, 3 );
                add_action( 'create_term', array( $this, 'save_taxonomy' ), 10, 3 );
            }
        }
        elseif ( 'media' == $pod[ 'type' ] ) {
            if ( !has_filter( 'wp_update_attachment_metadata', array( $this, 'save_media' ), 10, 2 ) ) {
                add_action( 'add_meta_boxes', array( $this, 'meta_post_add' ) );
                add_action( 'wp_ajax_save-attachment-compat', array( $this, 'save_media_ajax' ), 0 );

                add_filter( 'attachment_fields_to_edit', array( $this, 'meta_media' ), 10, 2 );

                add_filter( 'attachment_fields_to_save', array( $this, 'save_media' ), 10, 2 );
                add_filter( 'wp_update_attachment_metadata', array( $this, 'save_media' ), 10, 2 );
            }
        }
        elseif ( 'user' == $pod[ 'type' ] ) {
            if ( !has_action( 'show_user_profile', array( $this, 'meta_user' ) ) ) {
                add_action( 'show_user_profile', array( $this, 'meta_user' ) );
                add_action( 'edit_user_profile', array( $this, 'meta_user' ) );
                add_action( 'user_register', array( $this, 'save_user' ) );
                add_action( 'profile_update', array( $this, 'save_user' ), 10, 2 );
            }
        }
        elseif ( 'comment' == $pod[ 'type' ] ) {
            if ( !has_action( 'comment_form_logged_in_after', array( $this, 'meta_comment_new_logged_in' ), 10, 2 ) ) {
                add_action( 'comment_form_logged_in_after', array( $this, 'meta_comment_new_logged_in' ), 10, 2 );
                add_filter( 'comment_form_default_fields', array( $this, 'meta_comment_new' ) );
                add_action( 'add_meta_boxes_comment', array( $this, 'meta_comment_add' ) );
                add_action( 'wp_insert_comment', array( $this, 'save_comment' ) );
                add_action( 'edit_comment', array( $this, 'save_comment' ) );
            }
        }
    }

    public function object_get ( $type, $name ) {
        $object = self::$post_types;

        if ( 'term' == $type ) {
        	$type = 'taxonomy';
        }

        if ( 'taxonomy' == $type )
            $object = self::$taxonomies;
        elseif ( 'media' == $type )
            $object = self::$media;
        elseif ( 'user' == $type )
            $object = self::$user;
        elseif ( 'comment' == $type )
            $object = self::$comment;

        if ( 'pod' != $type && !empty( $object ) && is_array( $object ) && isset( $object[ $name ] ) )
            $pod = $object[ $name ];
        else {
            if ( empty( self::$current_pod_data ) || !is_object( self::$current_pod_data ) || self::$current_pod_data[ 'name' ] != $name )
                self::$current_pod_data = pods_api()->load_pod( array( 'name' => $name ), false );

            $pod = self::$current_pod_data;
        }

        if ( empty( $pod ) )
            return array();

        $defaults = array(
            'name' => 'post',
            'object' => 'post',
            'type' => 'post_type'
        );

        $pod = array_merge( $defaults, (array) $pod );

        if ( empty( $pod[ 'name' ] ) )
            $pod[ 'name' ] = $pod[ 'object' ];
        elseif ( empty( $pod[ 'object' ] ) )
            $pod[ 'object' ] = $pod[ 'name' ];

        if ( $pod[ 'type' ] != $type )
            return array();

        return $pod;
    }

    /**
     * @param $type
     * @param $name
     * @param $default_fields
     *
     * @return array
     */
    public function groups_get ( $type, $name, $default_fields = null ) {
        if ( 'post_type' == $type && 'attachment' == $name ) {
            $type = 'media';
            $name = 'media';
        } elseif ( 'term' == $type ) {
            $type = 'taxonomy';
        }

        do_action( 'pods_meta_groups', $type, $name );

        $pod = array();
        $fields = array();

        $object = self::$post_types;

        if ( 'taxonomy' == $type )
            $object = self::$taxonomies;
        elseif ( 'media' == $type )
            $object = self::$media;
        elseif ( 'user' == $type )
            $object = self::$user;
        elseif ( 'comment' == $type )
            $object = self::$comment;
        elseif ( 'pod' == $type )
            $object = self::$advanced_content_types;

        if ( !empty( $object ) && is_array( $object ) && isset( $object[ $name ] ) )
            $fields = $object[ $name ][ 'fields' ];
        else {
            if ( empty( self::$current_pod_data ) || !is_object( self::$current_pod_data ) || self::$current_pod_data[ 'name' ] != $name )
                self::$current_pod_data = pods_api()->load_pod( array( 'name' => $name ), false );

            $pod = self::$current_pod_data;

            if ( !empty( $pod ) )
                $fields = $pod[ 'fields' ];
        }

        if ( null !== $default_fields ) {
            $fields = $default_fields;
        }

        $defaults = array(
            'name' => 'post',
            'object' => 'post',
            'type' => 'post_type'
        );

        $pod = array_merge( $defaults, (array) $pod );

        if ( empty( $pod[ 'name' ] ) )
            $pod[ 'name' ] = $pod[ 'object' ];
        elseif ( empty( $pod[ 'object' ] ) )
            $pod[ 'object' ] = $pod[ 'name' ];

        if ( $pod[ 'type' ] != $type )
            return array();

        $groups = array(
            array(
                'pod' => $pod,
				/**
				 * Filter the title of the Pods Metabox In The Post Editor
				 *
				 * @param string $title The title to use, default is 'More Fields'
				 * @param obj|Pod $pod Current Pods Object
				 * @param array $fields Array of fields that will go in the metabox
				 * @param string $type The type of Pod
				 * @params string $name Name of the Pod
				 *
				 * @returns string The title for the metabox.
				 *
				 * @since unknown
				 */
				'label' => apply_filters( 'pods_meta_default_box_title', __( 'More Fields', 'pods' ), $pod, $fields, $type, $name ),
                'fields' => $fields,
                'context' => 'normal',
                'priority' => 'default'
            )
        );

        if ( isset( self::$groups[ $type ] ) && isset( self::$groups[ $type ][ $name ] ) )
            $groups = self::$groups[ $type ][ $name ];

        /**
         * Filter the array of field groups
         *
         * @param array  $groups Array of groups
         * @param string  $type The type of Pod
         * @param string  $name Name of the Pod
         *
         * @since 2.6.6
         */
        return apply_filters( 'pods_meta_groups_get', $groups, $type, $name );
    }

    /**
     * @param $post_type
     * @param null $post
     */
    public function meta_post_add ( $post_type, $post = null ) {
        if ( 'comment' == $post_type )
            return;

        if ( is_object( $post ) )
            $post_type = $post->post_type;

        $groups = $this->groups_get( 'post_type', $post_type );
        $pods_field_found = false;

        foreach ( $groups as $group ) {
            if ( empty( $group[ 'fields' ] ) )
                continue;

            $field_found = false;
            $group_hidden = true;

            foreach ( $group[ 'fields' ] as $field ) {
                if ( false !== PodsForm::permission( $field[ 'type' ], $field[ 'name' ], $field, $group[ 'fields' ] ) ) {
                    $field_found = true;
                }
                if ( ! isset( $field['options']['hidden'] ) || 1 != (int) $field['options']['hidden'] ) {
                    $group_hidden = false;
                }
            }

            if ( $group_hidden )
                continue;

            if ( empty( $group[ 'label' ] ) )
                $group[ 'label' ] = get_post_type_object( $post_type )->labels->label;

            if ( $field_found ) {
                $pods_field_found = true;
                add_meta_box(
                    'pods-meta-' . sanitize_title( $group[ 'label' ] ),
                    $group[ 'label' ],
                    array( $this, 'meta_post' ),
                    $post_type,
                    $group[ 'context' ],
                    $group[ 'priority' ],
                    array( 'group' => $group )
                );

            }
        }

		if ( $pods_field_found ) {
			// Only add the classes to forms that actually have pods fields
			add_action( 'post_edit_form_tag', array( $this, 'add_class_submittable' ) );
		}
    }

    /**
     *
     * Called by 'post_edit_form_tag' action to include the classes in the <form> tag
     *
     */
    public function add_class_submittable () {
        echo ' class="pods-submittable pods-form"';
    }

    /**
     * @param $post
     * @param $metabox
     */
    public function meta_post ( $post, $metabox ) {
        wp_enqueue_style( 'pods-form' );
        wp_enqueue_script( 'pods' );

		$pod_type = 'post';

		if ( 'attachment' == $post->post_type ) {
			$pod_type = 'media';
		}

        do_action( 'pods_meta_' . __FUNCTION__, $post );

        $hidden_fields = array();

        $id = null;

        if ( is_object( $post ) && false === strpos( $_SERVER[ 'REQUEST_URI' ], '/post-new.php' ) )
            $id = $post->ID;

	if ( empty( self::$current_pod_data ) || !is_object( self::$current_pod ) || self::$current_pod->pod != $metabox[ 'args' ][ 'group' ][ 'pod' ][ 'name' ] ) {
		self::$current_pod = pods( $metabox[ 'args' ][ 'group' ][ 'pod' ][ 'name' ], $id, true );
	} elseif ( self::$current_pod->id() != $id ) {
		self::$current_pod->fetch( $id );
	}

        $pod = self::$current_pod;

	$fields = $metabox['args']['group']['fields'];

	/**
	 * Filter the fields used for the Pods metabox group
	 *
	 * @since 2.6.6
	 *
	 * @param array   $fields  Fields from the current Pod metabox group
	 * @param int     $id      Post ID
	 * @param WP_Post $post    Post object
	 * @param array	  $metabox Metabox args from the current Pod metabox group
	 * @param Pods    $pod     Pod object
	 */
 	$fields = apply_filters( 'pods_meta_post_fields', $fields, $id,  $post, $metabox, $pod  );

 	if ( empty( $fields ) ) {
 		_e( 'There are no fields to display', 'pods' );

 		return;
 	}
?>
    <table class="form-table pods-metabox pods-admin pods-dependency">
	<?php
	echo PodsForm::field( 'pods_meta', wp_create_nonce( 'pods_meta_' . $pod_type ), 'hidden' );

        foreach ( $fields as $field ) {
            if ( false === PodsForm::permission( $field[ 'type' ], $field[ 'name' ], $field[ 'options' ], $fields, $pod, $id ) ) {
                if ( pods_var( 'hidden', $field[ 'options' ], false ) )
                    $field[ 'type' ] = 'hidden';
                else
                    continue;
            }
            elseif ( !pods_has_permissions( $field[ 'options' ] ) && pods_var( 'hidden', $field[ 'options' ], false ) )
                $field[ 'type' ] = 'hidden';

            $value = '';

            if ( !empty( $pod ) ) {
                pods_no_conflict_on( 'post' );

                $value = $pod->field( array( 'name' => $field[ 'name' ], 'in_form' => true ) );

                pods_no_conflict_off( 'post' );
            }
            elseif ( !empty( $id ) )
                $value = get_post_meta( $id, $field[ 'name' ], true );

            if ( 'hidden' == $field[ 'type' ] ) {
                $hidden_fields[] = array(
                    'field' => $field,
                    'value' => $value
                );
            }
            else {
                $dep_options = PodsForm::dependencies( $field, 'pods-meta-' );
                $dep_classes = $dep_options[ 'classes' ];
                $dep_data = $dep_options[ 'data' ];

            do_action( 'pods_meta_' . __FUNCTION__ . '_' . $field[ 'name' ], $post, $field, $pod );
        ?>
            <tr class="form-field pods-field pods-field-input <?php echo esc_attr( 'pods-form-ui-row-type-' . $field[ 'type' ] . ' pods-form-ui-row-name-' . PodsForm::clean( $field[ 'name' ], true ) ); ?> <?php echo esc_attr( $dep_classes ); ?>" <?php PodsForm::data( $dep_data ); ?>">
                <th scope="row" valign="top"><?php echo PodsForm::label( 'pods_meta_' . $field[ 'name' ], $field[ 'label' ], $field[ 'help' ], $field ); ?></th>
                <td>
                    <?php
                        // Remove any extra ? help icons
                        if ( isset( $field[ 'help' ] ) )
                            unset( $field[ 'help' ] );
                    ?>
            <div class="pods-submittable-fields">
                    <?php echo PodsForm::field( 'pods_meta_' . $field[ 'name' ], $value, $field[ 'type' ], $field, $pod, $id ); ?>
                    <?php echo PodsForm::comment( 'pods_meta_' . $field[ 'name' ], $field[ 'description' ], $field ); ?>
            </div>
                </td>
            </tr>
        <?php
                do_action( 'pods_meta_' . __FUNCTION__ . '_' . $field[ 'name' ] . '_post', $post, $field, $pod );
            }
        }
        ?>
    </table>

    <?php
        do_action( 'pods_meta_' . __FUNCTION__ . '_post', $post );

        foreach ( $hidden_fields as $hidden_field ) {
            $field_data = $hidden_field[ 'field' ];

            echo PodsForm::field( 'pods_meta_' . $field_data[ 'name' ], $hidden_field[ 'value' ], 'hidden', $field_data );
        }
    ?>

    <script type="text/javascript">
        jQuery( function ( $ ) {
            $( document ).Pods( 'validate' );
            $( document ).Pods( 'submit_meta' );
            $( document ).Pods( 'dependency', true );
        } );
    </script>
<?php
    }

    /**
	 * Handle integration with the transition_post_status hook
	 *
     * @see wp_transition_post_status
	 *
     * @param string  $new_status
     * @param string  $old_status
     * @param WP_Post $post
     */
    public function save_post_detect_new ( $new_status, $old_status, $post ) {

    	if ( $post ) {
		    self::$old_post_status[ $post->post_type ] = $old_status;
	    }

    }

    /**
     * Handle integration with the save_post hook
     *
     * @see wp_insert_post
	 *
     * @param int       $post_id
     * @param WP_Post   $post
     * @param bool|null $update
     */
	public function save_post( $post_id, $post, $update = null ) {

		if ( empty( $post ) ) {
			return;
		}

		$is_new_item = false;

		if ( is_bool( $update ) ) {
			$is_new_item = ! $update;
		} // false is new item
		elseif ( isset( self::$old_post_status[ $post->post_type ] ) && in_array( self::$old_post_status[ $post->post_type ], array( 'new', 'auto-draft' ), true ) ) {
			$is_new_item = true;
		}

		$nonced = wp_verify_nonce( pods_v( 'pods_meta', 'post' ), 'pods_meta_post' );

		if ( ! $is_new_item && false === $nonced ) {
			return;
		}

		// Unset to avoid manual new post issues
		if ( isset( self::$old_post_status[ $post->post_type ] ) ) {
			unset( self::$old_post_status[ $post->post_type ] );
		}

		$blacklisted_types = array(
			'revision',
			'_pods_pod',
			'_pods_field'
		);

		$blacklisted_types = apply_filters( 'pods_meta_save_post_blacklist_types', $blacklisted_types, $post_id, $post );

		// @todo Figure out how to hook into autosave for saving meta

		// Block Autosave and Revisions
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || in_array( $post->post_type, $blacklisted_types ) ) {
			return;
		}

		// Block Quick Edits / Bulk Edits
		if ( 'edit.php' === pods_v( 'pagenow', 'global' ) && ( 'inline-save' === pods_v( 'action', 'post' ) || null !== pods_v( 'bulk_edit', 'get' ) || is_array( pods_v( 'post', 'get' ) ) ) ) {
			return;
		}

		// Block Trash
		if ( in_array( pods_v( 'action', 'get' ), array( 'untrash', 'trash' ), true ) ) {
			return;
		}

		// Block Auto-drafting and Trash (not via Admin action)
		$blacklisted_status = array(
			'auto-draft',
			'trash',
		);

		$blacklisted_status = apply_filters( 'pods_meta_save_post_blacklist_status', $blacklisted_status, $post_id, $post );

		if ( in_array( $post->post_status, $blacklisted_status ) ) {
			return;
		}

		$groups = $this->groups_get( 'post_type', $post->post_type );

		$id = $post_id;

		if ( ! is_object( self::$current_pod ) || self::$current_pod->pod !== $post->post_type ) {
			self::$current_pod = pods( $post->post_type, $id, true );
		} elseif ( is_object( self::$current_pod ) && (int) self::$current_pod->id() !== (int) $id ) {
			self::$current_pod->fetch( $id );
		}

		$pod  = self::$current_pod;
		$data = array();

		if ( $pod ) {
			$rest_enable = (boolean) pods_v( 'rest_enable', $pod->pod_data['options'], false );

			// Block REST API saves, we handle those separately in PodsRESTHandlers
			if ( defined( 'REST_REQUEST' ) && REST_REQUEST && $rest_enable ) {
				return;
			}
		}
		// The following code will run for all post_types (not just Pods)

		if ( false !== $nonced && ! empty( $groups ) ) {
			foreach ( $groups as $group ) {
				if ( empty( $group['fields'] ) ) {
					continue;
				}

				foreach ( $group['fields'] as $field ) {
					if ( false === PodsForm::permission( $field['type'], $field['name'], $field, $group['fields'], $pod, $id ) ) {
						if ( ! pods_v( 'hidden', $field['options'], false ) ) {
							continue;
						}
					}

					$data[ $field['name'] ] = '';

					if ( isset( $_POST[ 'pods_meta_' . $field['name'] ] ) ) {
						$data[ $field['name'] ] = $_POST[ 'pods_meta_' . $field['name'] ];
					}
				}
			}

			if ( $is_new_item ) {
				do_action( 'pods_meta_create_pre_post', $data, $pod, $id, $groups, $post, $post->post_type );
				do_action( "pods_meta_create_pre_post_{$post->post_type}", $data, $pod, $id, $groups, $post );
			}

			do_action( 'pods_meta_save_pre_post', $data, $pod, $id, $groups, $post, $post->post_type, $is_new_item );
			do_action( "pods_meta_save_pre_post_{$post->post_type}", $data, $pod, $id, $groups, $post, $is_new_item );
		}

		if ( $is_new_item || false !== $nonced ) {
			pods_no_conflict_on( 'post' );

			if ( ! empty( $pod ) ) {
				// Fix for Pods doing it's own sanitizing
				$data = pods_unslash( (array) $data );

				$pod->save( $data, null, null, array( 'is_new_item' => $is_new_item, 'podsmeta' => true ) );
			} elseif ( ! empty( $id ) ) {
				foreach ( $data as $field => $value ) {
					update_post_meta( $id, $field, $value );
				}
			}

			pods_no_conflict_off( 'post' );
		}

		if ( false !== $nonced && ! empty( $groups ) ) {
			if ( $is_new_item ) {
				do_action( 'pods_meta_create_post', $data, $pod, $id, $groups, $post, $post->post_type );
				do_action( "pods_meta_create_post_{$post->post_type}", $data, $pod, $id, $groups, $post );
			}

			do_action( 'pods_meta_save_post', $data, $pod, $id, $groups, $post, $post->post_type, $is_new_item );
			do_action( "pods_meta_save_post_{$post->post_type}", $data, $pod, $id, $groups, $post, $is_new_item );
		}

	}

	/**
	 * Track changed fields before save for posts.
	 *
	 * @param array $data
	 * @param array $postarr
	 *
	 * @return array
	 */
	public function save_post_track_changed_fields( $data, $postarr ) {

		$no_conflict = pods_no_conflict_check( 'post' );

		if ( ! $no_conflict && ! empty( $data['post_type'] ) && ! empty( $postarr['ID'] ) ) {
			$pod = $data['post_type'];
			$id  = $postarr['ID'];

			PodsAPI::handle_changed_fields( $pod, $id, 'reset' );
		}

		return $data;

	}

    /**
     * @param $form_fields
     * @param $post
     *
     * @return array
     */
    public function meta_media ( $form_fields, $post ) {
        $groups = $this->groups_get( 'media', 'media' );

        if ( empty( $groups ) || 'attachment' == pods_var( 'typenow', 'global' ) )
            return $form_fields;

        wp_enqueue_style( 'pods-form' );
        wp_enqueue_script( 'pods' );

        $id = null;

        if ( is_object( $post ) )
            $id = $post->ID;

        $pod = null;

		$meta_nonce = PodsForm::field( 'pods_meta', wp_create_nonce( 'pods_meta_media' ), 'hidden' );

        foreach ( $groups as $group ) {
            if ( empty( $group[ 'fields' ] ) )
                continue;

			if ( null === $pod || ( is_object( $pod ) && $pod->id() != $id ) ) {
				if ( !is_object( self::$current_pod ) || self::$current_pod->pod != $group[ 'pod' ][ 'name' ] )
					self::$current_pod = pods( $group[ 'pod' ][ 'name' ], $id, true );
				elseif ( self::$current_pod->id() != $id )
					self::$current_pod->fetch( $id );

				$pod = self::$current_pod;
			}

            foreach ( $group[ 'fields' ] as $field ) {
                if ( false === PodsForm::permission( $field[ 'type' ], $field[ 'name' ], $field, $group[ 'fields' ], $pod, $id ) ) {
                    if ( !pods_var( 'hidden', $field[ 'options' ], false ) )
                        continue;
                }

                $value = '';

                if ( !empty( $pod ) )
                    $value = $pod->field( array( 'name' => $field[ 'name' ], 'in_form' => true ) );
                elseif ( !empty( $id ) ) {
                    pods_no_conflict_on( 'post' );

                    $value = get_post_meta( $id, $field[ 'name' ], true );

                    pods_no_conflict_off( 'post' );
                }

                $form_fields[ 'pods_meta_' . $field[ 'name' ] ] = array(
                    'label' => $field[ 'label' ],
                    'input' => 'html',
                    'html' => PodsForm::field( 'pods_meta_' . $field[ 'name' ], $value, $field[ 'type' ], $field, $pod, $id ) . $meta_nonce,
                    'helps' => PodsForm::comment( 'pods_meta_' . $field[ 'name' ], $field[ 'description' ], $field )
                );
            }
        }

        $form_fields = apply_filters( 'pods_meta_' . __FUNCTION__, $form_fields );

        return $form_fields;
    }

    /**
     * @param $post
     * @param $attachment
     *
     * @return mixed
     */
    public function save_media ( $post, $attachment ) {
        $groups = $this->groups_get( 'media', 'media' );

        if ( empty( $groups ) )
            return $post;

        $post_id = $attachment;

        if ( empty( $_POST ) || !wp_verify_nonce( pods_v( 'pods_meta', 'post' ), 'pods_meta_media' ) ) {
            return $post;
		}

        if ( is_array( $post ) && !empty( $post ) && isset( $post[ 'ID' ] ) && 'attachment' == $post[ 'post_type' ] )
            $post_id = $post[ 'ID' ];

        if ( is_array( $post_id ) || empty( $post_id ) )
            return $post;

        $data = array();

        $id = $post_id;
        $pod = null;

        foreach ( $groups as $group ) {
            if ( empty( $group[ 'fields' ] ) )
                continue;

			if ( null === $pod || ( is_object( $pod ) && $pod->id() != $id ) ) {
				if ( !is_object( self::$current_pod ) || self::$current_pod->pod != $group[ 'pod' ][ 'name' ] )
					self::$current_pod = pods( $group[ 'pod' ][ 'name' ], $id, true );
				elseif ( self::$current_pod->id() != $id )
					self::$current_pod->fetch( $id );

				$pod = self::$current_pod;
			}

            foreach ( $group[ 'fields' ] as $field ) {

                if ( false === PodsForm::permission( $field[ 'type' ], $field[ 'name' ], $field, $group[ 'fields' ], $pod, $id ) ) {
                    if ( !pods_var( 'hidden', $field[ 'options' ], false ) )
                        continue;
                }

                $data[ $field[ 'name' ] ] = '';

                if ( isset( $_POST[ 'pods_meta_' . $field[ 'name' ] ] ) )
                    $data[ $field[ 'name' ] ] = $_POST[ 'pods_meta_' . $field[ 'name' ] ];
            }
        }

        if ( $pod ) {
			$rest_enable = (boolean) pods_v( 'rest_enable', $pod->pod_data['options'], false );

			// Block REST API saves, we handle those separately in PodsRESTHandlers
			if ( defined( 'REST_REQUEST' ) && REST_REQUEST && $rest_enable ) {
				return $post;
			}
		}

        do_action( 'pods_meta_save_pre_media', $data, $pod, $id, $groups, $post, $attachment );

        if ( !empty( $pod ) ) {
            // Fix for Pods doing it's own sanitization
            $data = pods_unslash( (array) $data );

            $pod->save( $data, null, null, array( 'podsmeta' => true ) );
        }
        elseif ( !empty( $id ) ) {
            pods_no_conflict_on( 'post' );

            foreach ( $data as $field => $value ) {
                update_post_meta( $id, $field, $value );
            }

            pods_no_conflict_off( 'post' );
        }

        do_action( 'pods_meta_save_media', $data, $pod, $id, $groups, $post, $attachment );

        return $post;
    }

    public function save_media_ajax () {
        if ( !isset( $_POST[ 'id' ] ) || empty( $_POST[ 'id' ] ) || absint( $_POST[ 'id' ] ) < 1 )
            return;

        $id = absint( $_POST[ 'id' ] );

        if ( !isset( $_POST[ 'nonce' ] ) || empty( $_POST[ 'nonce' ] ) )
            return;

        check_ajax_referer( 'update-post_' . $id, 'nonce' );

        if ( !current_user_can( 'edit_post', $id ) )
            return;

        $post = get_post( $id, ARRAY_A );

    	if ( 'attachment' != $post[ 'post_type' ] )
            return;

        // fix ALL THE THINGS

        if ( !isset( $_REQUEST[ 'attachments' ] ) )
            $_REQUEST[ 'attachments' ] = array();

        if ( !isset( $_REQUEST[ 'attachments' ][ $id ] ) )
            $_REQUEST[ 'attachments' ][ $id ] = array();

        if ( empty( $_REQUEST[ 'attachments' ][ $id ] ) )
            $_REQUEST[ 'attachments' ][ $id ][ '_fix_wp' ] = 1;
    }

    /**
     * @param $tag
     * @param null $taxonomy
     */
    public function meta_taxonomy ( $tag, $taxonomy = null ) {
        wp_enqueue_style( 'pods-form' );
        wp_enqueue_script( 'pods' );

        do_action( 'pods_meta_' . __FUNCTION__, $tag, $taxonomy );

        $taxonomy_name = $taxonomy;

        if ( !is_object( $tag ) )
            $taxonomy_name = $tag;

        $groups = $this->groups_get( 'taxonomy', $taxonomy_name );

        $id = null;

        if ( is_object( $tag ) )
            $id = $tag->term_id;

        $pod = null;

		echo PodsForm::field( 'pods_meta', wp_create_nonce( 'pods_meta_taxonomy' ), 'hidden' );

        foreach ( $groups as $group ) {
            if ( empty( $group[ 'fields' ] ) )
                continue;

			if ( null === $pod || ( is_object( $pod ) && $pod->id() != $id ) ) {
				if ( !is_object( self::$current_pod ) || self::$current_pod->pod != $group[ 'pod' ][ 'name' ] )
					self::$current_pod = pods( $group[ 'pod' ][ 'name' ], $id, true );
				elseif ( self::$current_pod->id() != $id )
					self::$current_pod->fetch( $id );

				$pod = self::$current_pod;
			}

            foreach ( $group[ 'fields' ] as $field ) {
                if ( false === PodsForm::permission( $field[ 'type' ], $field[ 'name' ], $field, $group[ 'fields' ], $pod, $id ) ) {
                    if ( pods_var( 'hidden', $field[ 'options' ], false ) )
                        $field[ 'type' ] = 'hidden';
                    else
                        continue;
                }
                elseif ( !pods_has_permissions( $field[ 'options' ] ) && pods_var( 'hidden', $field[ 'options' ], false ) )
                    $field[ 'type' ] = 'hidden';

                $value = '';

                if ( !empty( $pod ) )
                    $value = $pod->field( array( 'name' => $field[ 'name' ], 'in_form' => true ) );

                if ( !is_object( $tag ) ) {
            ?>
                <div class="form-field pods-field" style="<?php echo esc_attr( 'hidden' == $field[ 'type' ] ? 'display:none;' : '' ); ?>">
                    <?php
                        echo PodsForm::label( 'pods_meta_' . $field[ 'name' ], $field[ 'label' ], $field[ 'help' ], $field );
                        echo PodsForm::field( 'pods_meta_' . $field[ 'name' ], $value, $field[ 'type' ], $field, $pod, $id );
                        echo PodsForm::comment( 'pods_meta_' . $field[ 'name' ], $field[ 'description' ], $field );
                    ?>
                </div>
            <?php
                }
                else {
            ?>
                <tr class="form-field pods-field <?php echo esc_attr( 'pods-form-ui-row-type-' . $field[ 'type' ] . ' pods-form-ui-row-name-' . PodsForm::clean( $field[ 'name' ], true ) ); ?>" style="<?php echo esc_attr( 'hidden' == $field[ 'type' ] ? 'display:none;' : '' ); ?>">
                    <th scope="row" valign="top"><?php echo PodsForm::label( 'pods_meta_' . $field[ 'name' ], $field[ 'label' ], $field[ 'help' ], $field ); ?></th>
                    <td>
                        <?php
                            echo PodsForm::field( 'pods_meta_' . $field[ 'name' ], $value, $field[ 'type' ], $field, $pod, $id );
                            echo PodsForm::comment( 'pods_meta_' . $field[ 'name' ], $field[ 'description' ], $field );
                        ?>
                    </td>
                </tr>
            <?php
                }
            }
        }

        do_action( 'pods_meta_' . __FUNCTION__ . '_post', $tag, $taxonomy );
    }

    /**
     * @param $term_id
     * @param $term_taxonomy_id
     * @param $taxonomy
     */
    public function save_taxonomy ( $term_id, $term_taxonomy_id, $taxonomy ) {
        $is_new_item = false;

        if ( 'create_term' == current_filter() )
            $is_new_item = true;

        if ( empty( $_POST ) || !wp_verify_nonce( pods_v( 'pods_meta', 'post' ), 'pods_meta_taxonomy' ) ) {
            return $term_id;
		}

		// Block Quick Edits / Bulk Edits
		if ( 'inline-save-tax' == pods_var( 'action', 'post' ) || null != pods_var( 'delete_tags', 'post' ) ) {
            return $term_id;
		}

        $groups = $this->groups_get( 'taxonomy', $taxonomy );

        if ( empty( $groups ) )
            return $term_id;

		$term = null;

        $id = $term_id;
        $pod = null;

		$has_fields = false;

        foreach ( $groups as $group ) {
	        if ( empty( $group['fields'] ) ) {
		        continue;
	        }

	        if ( null === $term ) {
		        $term = get_term( $term_id, $taxonomy );

		        $data = array(
			        'name' => $term->name
		        );
	        }

	        $has_fields = true;

	        if ( null === $pod || ( is_object( $pod ) && $pod->id() != $id ) ) {
		        if ( ! is_object( self::$current_pod ) || self::$current_pod->pod != $group['pod']['name'] ) {
			        self::$current_pod = pods( $group['pod']['name'], $id, true );
		        } elseif ( self::$current_pod->id() != $id ) {
			        self::$current_pod->fetch( $id );
		        }

		        $pod = self::$current_pod;
	        }

	        foreach ( $group['fields'] as $field ) {
		        if ( false === PodsForm::permission( $field['type'], $field['name'], $field, $group['fields'], $pod, $id ) ) {
			        if ( ! pods_var( 'hidden', $field['options'], false ) ) {
				        continue;
			        }
		        }

		        $data[ $field['name'] ] = '';

		        if ( isset( $_POST[ 'pods_meta_' . $field['name'] ] ) ) {
			        $data[ $field['name'] ] = $_POST[ 'pods_meta_' . $field['name'] ];
		        }
	        }
        }

        if ( $pod ) {
			$rest_enable = (boolean) pods_v( 'rest_enable', $pod->pod_data['options'], false );

			// Block REST API saves, we handle those separately in PodsRESTHandlers
			if ( defined( 'REST_REQUEST' ) && REST_REQUEST && $rest_enable ) {
				return $term_id;
			}
		}

		if ( !$has_fields ) {
			return $term_id;
		}

        if ( $is_new_item ) {
            do_action( 'pods_meta_create_pre_taxonomy', $data, $pod, $id, $groups, $term_id, $term_taxonomy_id, $taxonomy );
            do_action( "pods_meta_create_pre_taxonomy_{$taxonomy}", $data, $pod, $id, $groups, $term_id, $term_taxonomy_id, $taxonomy );
        }

        do_action( 'pods_meta_save_pre_taxonomy', $data, $pod, $id, $groups, $term_id, $term_taxonomy_id, $taxonomy, $is_new_item );
        do_action( "pods_meta_save_pre_taxonomy_{$taxonomy}", $data, $pod, $id, $groups, $term_id, $term_taxonomy_id, $taxonomy, $is_new_item );

        pods_no_conflict_on( 'taxonomy' );

        if ( !empty( $pod ) ) {
            // Fix for Pods doing it's own sanitization
            $data = pods_unslash( (array) $data );

            $pod->save( $data, null, null, array( 'is_new_item' => $is_new_item, 'podsmeta' => true ) );
        }

        pods_no_conflict_off( 'taxonomy' );

        if ( $is_new_item ) {
            do_action( 'pods_meta_create_taxonomy', $data, $pod, $id, $groups, $term_id, $term_taxonomy_id, $taxonomy );
            do_action( "pods_meta_create_taxonomy_{$taxonomy}", $data, $pod, $id, $groups, $term_id, $term_taxonomy_id, $taxonomy );
        }

        do_action( 'pods_meta_save_taxonomy', $data, $pod, $id, $groups, $term_id, $term_taxonomy_id, $taxonomy, $is_new_item );
        do_action( "pods_meta_save_taxonomy_{$taxonomy}", $data, $pod, $id, $groups, $term_id, $term_taxonomy_id, $taxonomy, $is_new_item );

		return $term_id;
    }

	/**
	 * Track changed fields before save for terms.
	 *
	 * @param int    $term_id
	 * @param string $taxonomy
	 */
	public function save_taxonomy_track_changed_fields( $term_id, $taxonomy ) {

		$no_conflict = pods_no_conflict_check( 'term' );

		if ( ! $no_conflict ) {
			$pod = $taxonomy;
			$id  = $term_id;

			PodsAPI::handle_changed_fields( $pod, $id, 'reset' );
		}

	}

    /**
     * @param $user_id
     */
    public function meta_user ( $user_id ) {
        wp_enqueue_style( 'pods-form' );
        wp_enqueue_script( 'pods' );

        do_action( 'pods_meta_' . __FUNCTION__, $user_id );

        $groups = $this->groups_get( 'user', 'user' );

        if ( is_object( $user_id ) )
            $user_id = $user_id->ID;

        $id = $user_id;
        $pod = null;

        foreach ( $groups as $group ) {
            if ( empty( $group[ 'fields' ] ) )
                continue;

			if ( null === $pod || ( is_object( $pod ) && $pod->id() != $id ) ) {
				if ( !is_object( self::$current_pod ) || self::$current_pod->pod != $group[ 'pod' ][ 'name' ] )
					self::$current_pod = pods( $group[ 'pod' ][ 'name' ], $id, true );
				elseif ( self::$current_pod->id() != $id )
					self::$current_pod->fetch( $id );

				$pod = self::$current_pod;
			}

            $hidden_fields = array();
?>
    <h3><?php echo $group[ 'label' ]; ?></h3>

	<?php echo PodsForm::field( 'pods_meta', wp_create_nonce( 'pods_meta_user' ), 'hidden' ); ?>

    <table class="form-table pods-meta">
        <tbody>
            <?php
                foreach ( $group[ 'fields' ] as $field ) {

                    if ( false === PodsForm::permission( $field[ 'type' ], $field[ 'name' ], $field, $group[ 'fields' ], $pod, $id ) ) {
                        if ( pods_var( 'hidden', $field[ 'options' ], false ) )
                            $field[ 'type' ] = 'hidden';
                        else
                            continue;
                    }
                    elseif ( !pods_has_permissions( $field[ 'options' ] ) && pods_var( 'hidden', $field[ 'options' ], false ) )
                        $field[ 'type' ] = 'hidden';

                    $value = '';

                    if ( !empty( $pod ) )
                        $value = $pod->field( array( 'name' => $field[ 'name' ], 'in_form' => true ) );
                    elseif ( !empty( $id ) ) {
                        pods_no_conflict_on( 'user' );

                        $value = get_user_meta( $id, $field[ 'name' ], true );

                        pods_no_conflict_off( 'user' );
                    }

                    if ( 'hidden' == $field[ 'type' ] ) {
                        $hidden_fields[] = array(
                            'field' => $field,
                            'value' => $value
                        );
                    }
                    else {
            ?>
                <tr class="form-field pods-field <?php echo esc_attr( 'pods-form-ui-row-type-' . $field[ 'type' ] . ' pods-form-ui-row-name-' . PodsForm::clean( $field[ 'name' ], true ) ); ?>">
                    <th scope="row" valign="top"><?php echo PodsForm::label( 'pods_meta_' . $field[ 'name' ], $field[ 'label' ], $field[ 'help' ], $field ); ?></th>
                    <td>
                        <?php echo PodsForm::field( 'pods_meta_' . $field[ 'name' ], $value, $field[ 'type' ], $field, $pod, $id ); ?>
                        <?php echo PodsForm::comment( 'pods_meta_' . $field[ 'name' ], $field[ 'description' ], $field ); ?>
                    </td>
                </tr>
            <?php
                    }
                }
            ?>
        </tbody>
    </table>
<?php
            foreach ( $hidden_fields as $hidden_field ) {
                $field_data = $hidden_field[ 'field' ];

                echo PodsForm::field( 'pods_meta_' . $field_data[ 'name' ], $hidden_field[ 'value' ], 'hidden', $field_data );
            }
        }

        do_action( 'pods_meta_' . __FUNCTION__ . '_post', $user_id );
    }

	/**
	 * Handle integration with the user_register and profile_update hooks.
	 *
	 * @see wp_insert_user
	 *
	 * @param int         $user_id       User ID.
	 * @param object|null $old_user_data Object containing user's data prior to update.
	 */
	public function save_user( $user_id, $old_user_data = null ) {

		$is_new_item = false;

		if ( 'user_register' == current_filter() ) {
			$is_new_item = true;
		}

		$nonced = wp_verify_nonce( pods_v( 'pods_meta', 'post' ), 'pods_meta_user' );

		if ( ! $is_new_item && false === $nonced ) {
			return;
		}

		if ( is_object( $user_id ) ) {
			$user_id = $user_id->ID;
		}

		$groups = $this->groups_get( 'user', 'user' );

		$id = $user_id;

		if ( ! is_object( self::$current_pod ) || self::$current_pod->pod !== 'user' ) {
			self::$current_pod = pods( 'user', $id, true );
		} elseif ( is_object( self::$current_pod ) && (int) self::$current_pod->id() !== (int) $id ) {
			self::$current_pod->fetch( $id );
		}

		$pod  = self::$current_pod;
		$data = array();

        if ( $pod ) {
			$rest_enable = (boolean) pods_v( 'rest_enable', $pod->pod_data['options'], false );

			// Block REST API saves, we handle those separately in PodsRESTHandlers
			if ( defined( 'REST_REQUEST' ) && REST_REQUEST && $rest_enable ) {
				return;
			}
		}

		if ( false !== $nonced && ! empty( $groups ) ) {
			foreach ( $groups as $group ) {
				if ( empty( $group['fields'] ) ) {
					continue;
				}

				foreach ( $group['fields'] as $field ) {
					if ( false === PodsForm::permission( $field['type'], $field['name'], $field, $group['fields'], $pod, $id ) ) {
						if ( ! pods_v( 'hidden', $field['options'], false ) ) {
							continue;
						}
					}

					$data[ $field['name'] ] = '';

					if ( isset( $_POST[ 'pods_meta_' . $field['name'] ] ) ) {
						$data[ $field['name'] ] = $_POST[ 'pods_meta_' . $field['name'] ];
					}
				}
			}

			if ( $is_new_item ) {
				do_action( 'pods_meta_create_pre_user', $data, $pod, $id, $groups );
			}

			do_action( 'pods_meta_save_pre_user', $data, $pod, $id, $groups, $is_new_item );
		}

		if ( $is_new_item || false !== $nonced ) {
			pods_no_conflict_on( 'user' );

			if ( ! empty( $pod ) ) {
				// Fix for Pods doing it's own sanitizing
				$data = pods_unslash( (array) $data );

				$pod->save( $data, null, null, array( 'is_new_item' => $is_new_item, 'podsmeta' => true ) );
			} elseif ( ! empty( $id ) ) {
				foreach ( $data as $field => $value ) {
					update_user_meta( $id, $field, $value );
				}
			}

			pods_no_conflict_off( 'user' );
		}

		if ( false !== $nonced && ! empty( $groups ) ) {
			if ( $is_new_item ) {
				do_action( 'pods_meta_create_user', $data, $pod, $id, $groups );
			}

			do_action( 'pods_meta_save_user', $data, $pod, $id, $groups, $is_new_item );
		}

	}

	/**
	 * Track changed fields before save for users.
	 *
	 * @param string $user_login
	 *
	 * @return string
	 */
	public function save_user_track_changed_fields( $user_login ) {

		$no_conflict = pods_no_conflict_check( 'user' );

		if ( ! $no_conflict ) {
			$user = get_user_by( 'login', $user_login );

			if ( $user && ! is_wp_error( $user ) ) {
				$pod = 'user';
				$id  = $user->ID;

				PodsAPI::handle_changed_fields( $pod, $id, 'reset' );
			}
		}

		return $user_login;

	}

    /**
     * @param $commenter
     * @param $user_identity
     */
    public function meta_comment_new_logged_in ( $commenter, $user_identity ) {
        wp_enqueue_style( 'pods-form' );
        wp_enqueue_script( 'pods' );

        do_action( 'pods_meta_' . __FUNCTION__, $commenter, $user_identity );

        $groups = $this->groups_get( 'comment', 'comment' );

        $id = null;
        $pod = null;

		echo PodsForm::field( 'pods_meta', wp_create_nonce( 'pods_meta_comment' ), 'hidden' );

        foreach ( $groups as $group ) {
            if ( empty( $group[ 'fields' ] ) )
                continue;

			if ( null === $pod || ( is_object( $pod ) && $pod->id() != $id ) ) {
				if ( !is_object( self::$current_pod ) || self::$current_pod->pod != $group[ 'pod' ][ 'name' ] )
					self::$current_pod = pods( $group[ 'pod' ][ 'name' ], $id, true );
				elseif ( self::$current_pod->id() != $id )
					self::$current_pod->fetch( $id );

				$pod = self::$current_pod;
			}

            foreach ( $group[ 'fields' ] as $field ) {
                if ( false === PodsForm::permission( $field[ 'type' ], $field[ 'name' ], $field, $group[ 'fields' ], $pod, $id ) ) {
                    if ( pods_var( 'hidden', $field[ 'options' ], false ) )
                        $field[ 'type' ] = 'hidden';
                    else
                        continue;
                }
                elseif ( !pods_has_permissions( $field[ 'options' ] ) && pods_var( 'hidden', $field[ 'options' ], false ) )
                    $field[ 'type' ] = 'hidden';

                $value = '';

                if ( !empty( $pod ) )
                    $value = $pod->field( array( 'name' => $field[ 'name' ], 'in_form' => true ) );
                elseif ( !empty( $id ) ) {
                    pods_no_conflict_on( 'comment' );

                    $value = get_comment_meta( $id, $field[ 'name' ], true );

                    pods_no_conflict_off( 'comment' );
                }
                ?>
            <p class="comment-form-author comment-form-pods-meta-<?php echo esc_attr( $field[ 'name' ] ); ?>  pods-field" style="<?php echo esc_attr( 'hidden' == $field[ 'type' ] ? 'display:none;' : '' ); ?>">
                <?php
                    echo PodsForm::label( 'pods_meta_' . $field[ 'name' ], $field[ 'label' ], $field[ 'help' ], $field );
                    echo PodsForm::field( 'pods_meta_' . $field[ 'name' ], $value, $field[ 'type' ], $field, $pod, $id );
                    echo PodsForm::comment( 'pods_meta_' . $field[ 'name' ], $field[ 'description' ], $field );
                ?>
            </p>
            <?php
            }
        }

        do_action( 'pods_meta_' . __FUNCTION__ . '_post', $commenter, $user_identity );
    }

    /**
     * @param $form_fields
     *
     * @return array
     */
    public function meta_comment_new ( $form_fields ) {
        wp_enqueue_style( 'pods-form' );
        wp_enqueue_script( 'pods' );

        $groups = $this->groups_get( 'comment', 'comment' );

        $id = null;
        $pod = null;

		$form_fields[ 'pods_meta' ] = PodsForm::field( 'pods_meta', wp_create_nonce( 'pods_meta_comment' ), 'hidden' );

        foreach ( $groups as $group ) {
            if ( empty( $group[ 'fields' ] ) )
                continue;

			if ( null === $pod || ( is_object( $pod ) && $pod->id() != $id ) ) {
				if ( !is_object( self::$current_pod ) || self::$current_pod->pod != $group[ 'pod' ][ 'name' ] )
					self::$current_pod = pods( $group[ 'pod' ][ 'name' ], $id, true );
				elseif ( self::$current_pod->id() != $id )
					self::$current_pod->fetch( $id );

				$pod = self::$current_pod;
			}

            foreach ( $group[ 'fields' ] as $field ) {

                if ( false === PodsForm::permission( $field[ 'type' ], $field[ 'name' ], $field, $group[ 'fields' ], $pod, $id ) ) {
                    if ( pods_var( 'hidden', $field[ 'options' ], false ) )
                        $field[ 'type' ] = 'hidden';
                    else
                        continue;
                }
                elseif ( !pods_has_permissions( $field[ 'options' ] ) && pods_var( 'hidden', $field[ 'options' ], false ) )
                    $field[ 'type' ] = 'hidden';

                $value = '';

                if ( !empty( $pod ) )
                    $value = $pod->field( array( 'name' => $field[ 'name' ], 'in_form' => true ) );
                elseif ( !empty( $id ) ) {
                    pods_no_conflict_on( 'comment' );

                    $value = get_comment_meta( $id, $field[ 'name' ], true );

                    pods_no_conflict_off( 'comment' );
                }

                ob_start();
                ?>
            <p class="comment-form-author comment-form-pods-meta-<?php echo esc_attr( $field[ 'name' ] ); ?> pods-field" style="<?php echo esc_attr( 'hidden' == $field[ 'type' ] ? 'display:none;' : '' ); ?>">
                <?php
                    echo PodsForm::label( 'pods_meta_' . $field[ 'name' ], $field[ 'label' ], $field[ 'help' ], $field );
                    echo PodsForm::field( 'pods_meta_' . $field[ 'name' ], $value, $field[ 'type' ], $field, $pod, $id );
                    echo PodsForm::comment( 'pods_meta_' . $field[ 'name' ], $field[ 'description' ], $field );
                ?>
            </p>
            <?php
                $form_fields[ 'pods_meta_' . $field[ 'name' ] ] = ob_get_clean();
            }
        }

        $form_fields = apply_filters( 'pods_meta_' . __FUNCTION__, $form_fields );

        return $form_fields;
    }

    /**
     * @param $comment_type
     * @param null $comment
     */
    public function meta_comment_add ( $comment_type, $comment = null ) {
        if ( is_object( $comment ) && isset( $comment_type->comment_type ) )
            $comment_type = $comment->comment_type;

        if ( is_object( $comment_type ) && isset( $comment_type->comment_type ) ) {
            $comment = $comment_type;
            $comment_type = $comment_type->comment_type;
        }

        if ( is_object( $comment_type ) )
            return;
        elseif ( empty( $comment_type ) )
            $comment_type = 'comment';

        $groups = $this->groups_get( 'comment', $comment_type );

        foreach ( $groups as $group ) {
            if ( empty( $group[ 'fields' ] ) )
                continue;

            $field_found = false;

            foreach ( $group[ 'fields' ] as $field ) {
                if ( false === PodsForm::permission( $field[ 'type' ], $field[ 'name' ], $field, $group[ 'fields' ], null, null ) ) {
                    if ( pods_var( 'hidden', $field[ 'options' ], false ) ) {
                        $field_found = true;
                        break;
                    }
                    else {
                        continue;
                    }
                }
                else {
                    $field_found = true;
                    break;
                }
            }

            if ( $field_found ) {
                add_meta_box(
                    'pods-meta-' . sanitize_title( $group[ 'label' ] ),
                    $group[ 'label' ],
                    array( $this, 'meta_comment' ),
                    $comment_type,
                    $group[ 'context' ],
                    $group[ 'priority' ],
                    array( 'group' => $group )
                );
            }
        }
    }

    /**
     * @param $comment
     * @param $metabox
     */
    public function meta_comment ( $comment, $metabox ) {
        wp_enqueue_style( 'pods-form' );
        wp_enqueue_script( 'pods' );

        do_action( 'pods_meta_' . __FUNCTION__, $comment, $metabox );

        $hidden_fields = array();

		echo PodsForm::field( 'pods_meta', wp_create_nonce( 'pods_meta_comment' ), 'hidden' );
?>
    <table class="form-table editcomment pods-metabox">
        <?php
            $id = null;

            if ( is_object( $comment ) )
                $id = $comment->comment_ID;

            if ( !is_object( self::$current_pod ) || self::$current_pod->pod != $metabox[ 'args' ][ 'group' ][ 'pod' ][ 'name' ] )
                self::$current_pod = pods( $metabox[ 'args' ][ 'group' ][ 'pod' ][ 'name' ], $id, true );
			elseif ( self::$current_pod->id() != $id )
				self::$current_pod->fetch( $id );

            $pod = self::$current_pod;

            foreach ( $metabox[ 'args' ][ 'group' ][ 'fields' ] as $field ) {
                if ( false === PodsForm::permission( $field[ 'type' ], $field[ 'name' ], $field, $metabox[ 'args' ][ 'group' ][ 'fields' ], $pod, $id ) ) {
                    if ( pods_var( 'hidden', $field[ 'options' ], false ) )
                        $field[ 'type' ] = 'hidden';
                    else
                        continue;
                }
                elseif ( !pods_has_permissions( $field[ 'options' ] ) && pods_var( 'hidden', $field[ 'options' ], false ) )
                    $field[ 'type' ] = 'hidden';

                $value = '';

                if ( !empty( $pod ) )
                    $value = $pod->field( array( 'name' => $field[ 'name' ], 'in_form' => true ) );

                if ( 'hidden' == $field[ 'type' ] ) {
                    $hidden_fields[] = array(
                        'field' => $field,
                        'value' => $value
                    );
                }
                else {
        ?>
            <tr class="form-field pods-field <?php echo esc_attr( 'pods-form-ui-row-type-' . $field[ 'type' ] . ' pods-form-ui-row-name-' . PodsForm::clean( $field[ 'name' ], true ) ); ?>">
                <th scope="row" valign="top"><?php echo PodsForm::label( 'pods_meta_' . $field[ 'name' ], $field[ 'label' ], $field[ 'help' ], $field ); ?></th>
                <td>
                    <?php echo PodsForm::field( 'pods_meta_' . $field[ 'name' ], $value, $field[ 'type' ], $field, $pod, $id ); ?>
                    <?php echo PodsForm::comment( 'pods_meta_' . $field[ 'name' ], $field[ 'description' ], $field ); ?>
                </td>
            </tr>
        <?php
                }
            }
        ?>
    </table>
<?php
        foreach ( $hidden_fields as $hidden_field ) {
            $field_data = $hidden_field[ 'field' ];

            echo PodsForm::field( 'pods_meta_' . $field_data[ 'name' ], $hidden_field[ 'value' ], 'hidden', $field_data );
        }

        do_action( 'pods_meta_' . __FUNCTION__ . '_post', $comment, $metabox );
    }

    /**
     * @param $approved
     * @param $commentdata
     */
    public function validate_comment ( $approved, $commentdata ) {
        $groups = $this->groups_get( 'comment', 'comment' );

        if ( empty( $groups ) )
            return $approved;

        $data = array();

        $pod = null;
        $id = null;

        foreach ( $groups as $group ) {
            if ( empty( $group[ 'fields' ] ) )
                continue;

			if ( null === $pod || ( is_object( $pod ) && $pod->id() != $id ) ) {
				if ( !is_object( self::$current_pod ) || self::$current_pod->pod != $group[ 'pod' ][ 'name' ] )
					self::$current_pod = pods( $group[ 'pod' ][ 'name' ], $id, true );
				elseif ( self::$current_pod->id() != $id )
					self::$current_pod->fetch( $id );

				$pod = self::$current_pod;
			}

            foreach ( $group[ 'fields' ] as $field ) {

                if ( false === PodsForm::permission( $field[ 'type' ], $field[ 'name' ], $field, $group[ 'fields' ], $pod, $id ) ) {
                    if ( !pods_var( 'hidden', $field[ 'options' ], false ) )
                        continue;
                }

                $data[ $field[ 'name' ] ] = '';

                if ( isset( $_POST[ 'pods_meta_' . $field[ 'name' ] ] ) )
                    $data[ $field[ 'name' ] ] = $_POST[ 'pods_meta_' . $field[ 'name' ] ];

                $validate = pods_api()->handle_field_validation( $data[ $field[ 'name' ] ], $field[ 'name' ], pods_api()->get_wp_object_fields( 'comment' ), $pod->fields(), $pod, array() );

                if ( false === $validate )
                    $validate = sprintf( __( 'There was an issue validating the field %s', 'pods' ), $field[ 'label' ] );

                if ( !is_bool( $validate ) && !empty( $validate ) )
                    return pods_error( $validate, $this );
            }
        }

        return $approved;
    }

    /**
     * @param $comment_id
     */
    public function save_comment ( $comment_id ) {
        $groups = $this->groups_get( 'comment', 'comment' );

        if ( empty( $groups ) ) {
            return $comment_id;
		}
		elseif ( empty( $_POST ) ) {
			return $comment_id;
		}
		elseif ( !wp_verify_nonce( pods_v( 'pods_meta', 'post' ), 'pods_meta_comment' ) ) {
			return $comment_id;
		}

        $data = array();

        $id = $comment_id;
        $pod = null;

        foreach ( $groups as $group ) {
            if ( empty( $group[ 'fields' ] ) )
                continue;

			if ( null === $pod || ( is_object( $pod ) && $pod->id() != $id ) ) {
				if ( !is_object( self::$current_pod ) || self::$current_pod->pod != $group[ 'pod' ][ 'name' ] )
					self::$current_pod = pods( $group[ 'pod' ][ 'name' ], $id, true );
				elseif ( self::$current_pod->id() != $id )
					self::$current_pod->fetch( $id );

				$pod = self::$current_pod;
			}

            foreach ( $group[ 'fields' ] as $field ) {
                if ( false === PodsForm::permission( $field[ 'type' ], $field[ 'name' ], $field, $group[ 'fields' ], $pod, $id ) ) {
                    if ( !pods_var( 'hidden', $field[ 'options' ], false ) )
                        continue;
                }

                $data[ $field[ 'name' ] ] = '';

                if ( isset( $_POST[ 'pods_meta_' . $field[ 'name' ] ] ) )
                    $data[ $field[ 'name' ] ] = $_POST[ 'pods_meta_' . $field[ 'name' ] ];
            }
        }

        if ( $pod ) {
			$rest_enable = (boolean) pods_v( 'rest_enable', $pod->pod_data['options'], false );

			// Block REST API saves, we handle those separately in PodsRESTHandlers
			if ( defined( 'REST_REQUEST' ) && REST_REQUEST && $rest_enable ) {
				return $comment_id;
			}
		}

        do_action( 'pods_meta_save_pre_comment', $data, $pod, $id, $groups );

        if ( !empty( $pod ) ) {
            // Fix for Pods doing it's own sanitization
            $data = pods_unslash( (array) $data );

            $pod->save( $data, null, null, array( 'podsmeta' => true ) );
        }
        elseif ( !empty( $id ) ) {
            pods_no_conflict_on( 'comment' );

            foreach ( $data as $field => $value ) {
                update_comment_meta( $id, $field, $value );
            }

            pods_no_conflict_off( 'comment' );
        }

        do_action( 'pods_meta_save_comment', $data, $pod, $id, $groups );

        return $comment_id;
    }

	/**
	 * Track changed fields before save for comments.
	 *
	 * @param array $data       The new, processed comment data.
	 * @param array $comment    The old, unslashed comment data.
	 * @param array $commentarr The new, raw comment data.
	 *
	 * @return array
	 */
	public function save_comment_track_changed_fields( $data, $comment, $commentarr ) {

		$no_conflict = pods_no_conflict_check( 'user' );

		if ( ! $no_conflict && ! empty( $comment['comment_type'] ) && ! empty( $comment['comment_ID'] ) ) {
			$pod = $comment['comment_type'];
			$id  = $comment['comment_ID'];

			PodsAPI::handle_changed_fields( $pod, $id, 'reset' );
		}

		return $data;

	}

    /**
     * All *_*_meta filter handler aliases
     *
     * @return mixed
     */
    public function get_post_meta () {
        $args = func_get_args();

        array_unshift( $args, 'post_type' );

        $_null = apply_filters( 'pods_meta_get_post_meta', null, $args );

        if ( null !== $_null )
            return $_null;

        return call_user_func_array( array( $this, 'get_meta' ), $args );
    }

    /**
     * @return mixed
     */
    public function get_user_meta () {
        $args = func_get_args();

        array_unshift( $args, 'user' );

        $_null = apply_filters( 'pods_meta_get_user_meta', null, $args );

        if ( null !== $_null )
            return $_null;

        return call_user_func_array( array( $this, 'get_meta' ), $args );
    }

    /**
     * @return mixed
     */
    public function get_comment_meta () {
        $args = func_get_args();

        array_unshift( $args, 'comment' );

        $_null = apply_filters( 'pods_meta_get_comment_meta', null, $args );

        if ( null !== $_null )
            return $_null;

        return call_user_func_array( array( $this, 'get_meta' ), $args );
    }

    /**
     * @return mixed
     */
    public function get_term_meta () {
        $args = func_get_args();

        array_unshift( $args, 'term' );

        $_null = apply_filters( 'pods_meta_get_term_meta', null, $args );

        if ( null !== $_null )
            return $_null;

        return call_user_func_array( array( $this, 'get_meta' ), $args );
    }

    /**
     * All *_*_meta filter handler aliases
     *
     * @return mixed
     */
    public function get_option () {
        $args = func_get_args();

        array_unshift( $args, 'settings' );

        $_null = apply_filters( 'pods_meta_get_option', null, $args );

        if ( null !== $_null )
            return $_null;

        return call_user_func_array( array( $this, 'get_meta' ), $args );
    }

    /**
     * @return mixed
     */
    public function add_post_meta () {
        $args = func_get_args();

        array_unshift( $args, 'post_type' );

        $_null = apply_filters( 'pods_meta_add_post_meta', null, $args );

        if ( null !== $_null )
            return $_null;

        return call_user_func_array( array( $this, 'add_meta' ), $args );
    }

    /**
     * @return mixed
     */
    public function add_user_meta () {
        $args = func_get_args();

        array_unshift( $args, 'user' );

        $_null = apply_filters( 'pods_meta_add_user_meta', null, $args );

        if ( null !== $_null )
            return $_null;

        return call_user_func_array( array( $this, 'add_meta' ), $args );
    }

    /**
     * @return mixed
     */
    public function add_comment_meta () {
        $args = func_get_args();

        array_unshift( $args, 'comment' );

        $_null = apply_filters( 'pods_meta_add_comment_meta', null, $args );

        if ( null !== $_null )
            return $_null;

        return call_user_func_array( array( $this, 'add_meta' ), $args );
    }

    /**
     * @return mixed
     */
    public function add_term_meta () {
        $args = func_get_args();

        array_unshift( $args, 'term' );

        $_null = apply_filters( 'pods_meta_add_term_meta', null, $args );

        if ( null !== $_null )
            return $_null;

        return call_user_func_array( array( $this, 'add_meta' ), $args );
    }

    /**
     * @return mixed
     */
    public function add_option () {
        $args = func_get_args();

        array_unshift( $args, 'settings' );

        $_null = apply_filters( 'pods_meta_add_option', null, $args );

        if ( null !== $_null )
            return $_null;

        return call_user_func_array( array( $this, 'add_meta' ), $args );
    }

    /**
     * @return mixed
     */
    public function update_post_meta () {
        $args = func_get_args();

        array_unshift( $args, 'post_type' );

        $_null = apply_filters( 'pods_meta_update_post_meta', null, $args );

        if ( null !== $_null )
            return $_null;

        return call_user_func_array( array( $this, 'update_meta' ), $args );
    }

    /**
     * @return mixed
     */
    public function update_user_meta () {
        $args = func_get_args();

        array_unshift( $args, 'user' );

        $_null = apply_filters( 'pods_meta_update_user_meta', null, $args );

        if ( null !== $_null )
            return $_null;

        return call_user_func_array( array( $this, 'update_meta' ), $args );
    }

    /**
     * @return mixed
     */
    public function update_comment_meta () {
        $args = func_get_args();

        array_unshift( $args, 'comment' );

        $_null = apply_filters( 'pods_meta_update_comment_meta', null, $args );

        if ( null !== $_null )
            return $_null;

        return call_user_func_array( array( $this, 'update_meta' ), $args );
    }

    /**
     * @return mixed
     */
    public function update_term_meta () {
        $args = func_get_args();

        array_unshift( $args, 'term' );

        $_null = apply_filters( 'pods_meta_update_term_meta', null, $args );

        if ( null !== $_null )
            return $_null;

        return call_user_func_array( array( $this, 'update_meta' ), $args );
    }

    /**
     * @return mixed
     */
    public function update_option () {
        $args = func_get_args();

        array_unshift( $args, 'settings' );

        $_null = apply_filters( 'pods_meta_update_option', null, $args );

        if ( null !== $_null )
            return $_null;

        return call_user_func_array( array( $this, 'update_meta' ), $args );
    }

    /**
     * @return mixed
     */
    public function delete_post_meta () {
        $args = func_get_args();

        array_unshift( $args, 'post_type' );

        $_null = apply_filters( 'pods_meta_delete_post_meta', null, $args );

        if ( null !== $_null )
            return $_null;

        return call_user_func_array( array( $this, 'delete_meta' ), $args );
    }

    /**
     * @return mixed
     */
    public function delete_user_meta () {
        $args = func_get_args();

        array_unshift( $args, 'user' );

        $_null = apply_filters( 'pods_meta_delete_user_meta', null, $args );

        if ( null !== $_null )
            return $_null;

        return call_user_func_array( array( $this, 'delete_meta' ), $args );
    }

    /**
     * @return mixed
     */
    public function delete_comment_meta () {
        $args = func_get_args();

        array_unshift( $args, 'comment' );

        $_null = apply_filters( 'pods_meta_delete_comment_meta', null, $args );

        if ( null !== $_null )
            return $_null;

        return call_user_func_array( array( $this, 'delete_meta' ), $args );
    }

    /**
     * @return mixed
     */
    public function delete_term_meta () {
        $args = func_get_args();

        array_unshift( $args, 'term' );

        $_null = apply_filters( 'pods_meta_delete_term_meta', null, $args );

        if ( null !== $_null )
            return $_null;

        return call_user_func_array( array( $this, 'delete_meta' ), $args );
    }

    /**
     * @return mixed
     */
    public function delete_option () {
        $args = func_get_args();

        array_unshift( $args, 'settings' );

        $_null = apply_filters( 'pods_meta_delete_option', null, $args );

        if ( null !== $_null )
            return $_null;

        return call_user_func_array( array( $this, 'delete_meta' ), $args );
    }

    /*
     * The real meta functions
     */
    /**
     * @param $object_type
     * @param $object_id
     * @param string $aux
     *
     * @return bool|mixed
     */
    public function get_object ( $object_type, $object_id, $aux = '' ) {

    	global $wpdb;

    	if ( 'term' == $object_type ) {
    		$object_type = 'taxonomy';
    	}

        if ( 'post_type' == $object_type )
            $objects = self::$post_types;
        elseif ( 'taxonomy' == $object_type )
            $objects = self::$taxonomies;
        elseif ( 'media' == $object_type )
            $objects = self::$media;
        elseif ( 'user' == $object_type )
            $objects = self::$user;
        elseif ( 'comment' == $object_type )
            $objects = self::$comment;
        elseif ( 'settings' == $object_type )
            $objects = self::$settings;
        else
            return false;

        if ( empty( $objects ) || !is_array( $objects ) )
            return false;

        $object_name = null;

        if ( 'media' == $object_type )
            return @current( $objects );
        elseif ( 'user' == $object_type )
            return @current( $objects );
        elseif ( 'comment' == $object_type )
            return @current( $objects );
        elseif ( 'post_type' == $object_type ) {
            $object = get_post( $object_id );

            if ( !is_object( $object ) || !isset( $object->post_type ) )
                return false;

            $object_name = $object->post_type;
        }
        elseif ( 'taxonomy' == $object_type ) {
            $object = get_term( $object_id );

            if ( !is_object( $object ) || !isset( $object->taxonomy ) ){
                return false;
            }

            $object_name = $object->taxonomy;
            if ( empty( $aux ) ) {
                $object_name = $wpdb->get_var( $wpdb->prepare( "SELECT `taxonomy` FROM `{$wpdb->term_taxonomy}` WHERE `term_id` = %d", $object_id ) );
            } else {
                $object_name = $aux;
            }
        }
        elseif ( 'settings' == $object_type )
            $object = $object_id;
        else
            return false;

        $reserved_post_types = array(
			'revision'
        );

        $reserved_post_types = apply_filters( 'pods_meta_reserved_post_types', $reserved_post_types, $object_type, $object_id, $object_name, $objects );

        if ( empty( $object_name ) || ( 'post_type' == $object_type && ( 0 === strpos( $object_name, '_pods_' ) ) || in_array( $object_name, $reserved_post_types ) ) ) {
            return false;
		}
		elseif ( 'attachment' == $object_name ) {
			return @current( self::$media );
		}

        $recheck = array();

        // Return first created by Pods, save extended for later
        foreach ( $objects as $pod ) {
            if ( $object_name == $pod[ 'object' ] )
                $recheck[] = $pod;

            if ( '' == $pod[ 'object' ] && $object_name == $pod[ 'name' ] )
                return $pod;
        }

        // If no objects created by Pods, return first extended
        foreach ( $recheck as $pod ) {
            return $pod;
        }

        return false;
    }

    /**
     * @param $object_type
     * @param null $_null
     * @param int $object_id
     * @param string $meta_key
     * @param bool $single
     *
     * @return array|bool|int|mixed|null|string|void
     */
    public function get_meta ( $object_type, $_null = null, $object_id = 0, $meta_key = '', $single = false ) {
		// Enforce boolean as it can be a string sometimes
		$single = filter_var( $single, FILTER_VALIDATE_BOOLEAN );

        $meta_type = $object_type;

        if ( in_array( $meta_type, array( 'post_type', 'media' ) ) )
            $meta_type = 'post';
        elseif ( 'taxonomy' == $meta_type )
            $meta_type = 'term';

        if ( empty( $meta_key ) ) {
			if ( !defined( 'PODS_ALLOW_FULL_META' ) || !PODS_ALLOW_FULL_META ) {
				return $_null; // don't cover get_post_meta( $id )
			}

			$single = false;
		}

		if ( 'user' === $object_type && 'locale' === $meta_key ) {
			return $_null; // don't interfere with locale
		}

        $object = $this->get_object( $object_type, $object_id );

        if ( empty( $object_id ) || empty( $object ) )
            return $_null;

        $no_conflict = pods_no_conflict_check( $meta_type );

        if ( !$no_conflict )
            pods_no_conflict_on( $meta_type );

        $meta_cache = array();

        if ( !$single && isset( $GLOBALS[ 'wp_object_cache' ] ) && is_object( $GLOBALS[ 'wp_object_cache' ] ) ) {
            $meta_cache = wp_cache_get( $object_id, 'pods_' . $meta_type . '_meta' );

            if ( empty( $meta_cache ) ) {
                $meta_cache = wp_cache_get( $object_id, $meta_type . '_meta' );

                if ( empty( $meta_cache ) ) {
                    $meta_cache = update_meta_cache( $meta_type, array( $object_id ) );
                    $meta_cache = $meta_cache[ $object_id ];
                }
            }
        }

        if ( empty( $meta_cache ) || !is_array( $meta_cache ) )
            $meta_cache = array();

        if ( !is_object( self::$current_field_pod ) || self::$current_field_pod->pod != $object[ 'name' ] )
            self::$current_field_pod = pods( $object[ 'name' ], $object_id );
		elseif ( self::$current_field_pod->id() != $object_id )
			self::$current_field_pod->fetch( $object_id );

        $pod = self::$current_field_pod;

        $meta_keys = array( $meta_key );

        if ( empty( $meta_key ) )
            $meta_keys = array_keys( $meta_cache );

        $key_found = false;

        foreach ( $meta_keys as $meta_k ) {
            if ( !empty( $pod ) ) {
                if ( isset( $pod->fields[ $meta_k ] ) ) {
                    $key_found = true;

                    $meta_cache[ $meta_k ] = $pod->field( array( 'name' => $meta_k, 'single' => $single, 'get_meta' => true ) );

                    if ( ( !is_array( $meta_cache[ $meta_k ] ) || !isset( $meta_cache[ $meta_k ][ 0 ] ) ) ) {
                        if ( empty( $meta_cache[ $meta_k ] ) && !is_array( $meta_cache[ $meta_k ] ) && $single )
                            $meta_cache[ $meta_k ] = array();
                        else
                            $meta_cache[ $meta_k ] = array( $meta_cache[ $meta_k ] );
                    }

                    if ( in_array( $pod->fields[ $meta_k ][ 'type' ], PodsForm::tableless_field_types() ) && isset( $meta_cache[ '_pods_' . $meta_k ] ) )
                        unset( $meta_cache[ '_pods_' . $meta_k ] );
                }
                elseif ( false !== strpos( $meta_k, '.' ) ) {
                    $key_found = true;

                    $first = current( explode( '.', $meta_k ) );

                    if ( isset( $pod->fields[ $first ] ) ) {
                        $meta_cache[ $meta_k ] = $pod->field( array( 'name' => $meta_k, 'single' => $single, 'get_meta' => true ) );

                        if ( ( !is_array( $meta_cache[ $meta_k ] ) || !isset( $meta_cache[ $meta_k ][ 0 ] ) ) && $single ) {
                            if ( empty( $meta_cache[ $meta_k ] ) && !is_array( $meta_cache[ $meta_k ] ) && $single )
                                $meta_cache[ $meta_k ] = array();
                            else
                                $meta_cache[ $meta_k ] = array( $meta_cache[ $meta_k ] );
                        }

                        if ( in_array( $pod->fields[ $first ][ 'type' ], PodsForm::tableless_field_types() ) && isset( $meta_cache[ '_pods_' . $first ] ) )
                            unset( $meta_cache[ '_pods_' . $first ] );
                    }
                }
            }
        }

        if ( !$no_conflict )
            pods_no_conflict_off( $meta_type );

        unset( $pod ); // memory clear

        if ( !$key_found )
            return $_null;

        if ( !$single && isset( $GLOBALS[ 'wp_object_cache' ] ) && is_object( $GLOBALS[ 'wp_object_cache' ] ) )
            wp_cache_set( $object_id, $meta_cache, 'pods_' . $meta_type . '_meta' );

        if ( empty( $meta_key ) )
            return $meta_cache;
        elseif ( isset( $meta_cache[ $meta_key ] ) )
            $value = $meta_cache[ $meta_key ];
        else
            $value = '';

        if ( !is_numeric( $value ) && empty( $value ) ) {
            if ( $single )
                $value = '';
            else
                $value = array();
        }
        // get_metadata requires $meta[ 0 ] to be set for first value to be retrieved
        elseif ( !is_array( $value ) )
            $value = array( $value );

        return $value;
    }

    /**
     * @param $object_type
     * @param null $_null
     * @param int $object_id
     * @param string $meta_key
     * @param string $meta_value
     * @param bool $unique
     *
     * @return bool|int|null
     */
    public function add_meta ( $object_type, $_null = null, $object_id = 0, $meta_key = '', $meta_value = '', $unique = false ) {
        if ( pods_tableless() )
            return $_null;

        $object = $this->get_object( $object_type, $object_id );

        if ( empty( $object_id ) || empty( $object ) || !isset( $object[ 'fields' ][ $meta_key ] ) )
            return $_null;

        if ( in_array( $object[ 'fields' ][ $meta_key ][ 'type' ], PodsForm::tableless_field_types() ) ) {
            if ( !is_object( self::$current_field_pod ) || self::$current_field_pod->pod != $object[ 'name' ] )
                self::$current_field_pod = pods( $object[ 'name' ], $object_id );
			elseif ( self::$current_field_pod->id() != $object_id )
				self::$current_field_pod->fetch( $object_id );

            $pod = self::$current_field_pod;

            $pod->add_to( $meta_key, $meta_value );
        }
        else {
            if ( !is_object( self::$current_field_pod ) || self::$current_field_pod->pod != $object[ 'name' ] )
                self::$current_field_pod = pods( $object[ 'name' ] );

            $pod = self::$current_field_pod;

            $pod->save( $meta_key, $meta_value, $object_id, array( 'podsmeta_direct' => true, 'error_mode' => 'false' ) );
        }

        return $object_id;
    }

    /**
     * @param $object_type
     * @param null $_null
     * @param int $object_id
     * @param string $meta_key
     * @param string $meta_value
     * @param string $prev_value
     *
     * @return bool|int|null
     */
    public function update_meta ( $object_type, $_null = null, $object_id = 0, $meta_key = '', $meta_value = '', $prev_value = '' ) {
        if ( pods_tableless() )
            return $_null;

        $object = $this->get_object( $object_type, $object_id );

        if ( empty( $object_id ) || empty( $object ) || !isset( $object[ 'fields' ][ $meta_key ] ) )
            return $_null;

        if ( !is_object( self::$current_field_pod ) || self::$current_field_pod->pod != $object[ 'name' ] )
            self::$current_field_pod = pods( $object[ 'name' ] );

        $pod = self::$current_field_pod;

        if ( ( isset( $pod->fields[ $meta_key ] ) || false !== strpos( $meta_key, '.' ) ) && $pod->row !== null) {

            $key = $meta_key;
            if(false !== strpos( $meta_key, '.' )){
                $key = current( explode( '.', $meta_key ) );
            }

            $pod->row[ $meta_key ] = $meta_value;

            if ( isset( $pod->fields[ $key ] ) ) {
                if ( in_array( $pod->fields[ $key ][ 'type' ], PodsForm::tableless_field_types() ) && isset( $meta_cache[ '_pods_' . $key ] ) )
                    unset( $meta_cache[ '_pods_' . $key ] );
            }

        }

        $pod->save( $meta_key, $meta_value, $object_id, array( 'podsmeta_direct' => true, 'error_mode' => 'false' ) );

        return $object_id;
    }

    /**
     * @param $object_type
     * @param null $_null
     * @param int $object_id
     * @param string $meta_key
     * @param string $meta_value
     * @param bool $delete_all
     *
     * @return null
     */
    public function delete_meta ( $object_type, $_null = null, $object_id = 0, $meta_key = '', $meta_value = '', $delete_all = false ) {
        if ( pods_tableless() )
            return $_null;

        $object = $this->get_object( $object_type, $object_id );

        if ( empty( $object_id ) || empty( $object ) || !isset( $object[ 'fields' ][ $meta_key ] ) )
            return $_null;

        // @todo handle $delete_all (delete the field values from all pod items)
        if ( !empty( $meta_value ) && in_array( $object[ 'fields' ][ $meta_key ][ 'type' ], PodsForm::tableless_field_types() ) ) {
            if ( !is_object( self::$current_field_pod ) || self::$current_field_pod->pod != $object[ 'name' ] )
                self::$current_field_pod = pods( $object[ 'name' ], $object_id );
			elseif ( self::$current_field_pod->id() != $object_id )
				self::$current_field_pod->fetch( $object_id );

            $pod = self::$current_field_pod;

            $pod->remove_from( $meta_key, $meta_value );
        }
        else {
            if ( !is_object( self::$current_field_pod ) || self::$current_field_pod->pod != $object[ 'name' ] )
                self::$current_field_pod = pods( $object[ 'name' ] );

            $pod = self::$current_field_pod;

            $pod->save( array( $meta_key => null ), null, $object_id, array( 'podsmeta_direct' => true, 'error_mode' => 'false' ) );
        }

        return $_null;
    }

    public function delete_post ( $id ) {
        $post = get_post( $id );

        if ( empty( $post ) )
            return;

        $id = $post->ID;
        $post_type = $post->post_type;

        return $this->delete_object( 'post_type', $id, $post_type );
    }

    public function delete_taxonomy ( $id ) {
        /**
         * @var $wpdb WPDB
         */
        global $wpdb;

        $terms = $wpdb->get_results( "SELECT `term_id`, `taxonomy` FROM `{$wpdb->term_taxonomy}` WHERE `term_taxonomy_id` = {$id}" );

        if ( empty( $terms ) )
            return;

        foreach ( $terms as $term ) {
            $id = $term->term_id;
            $taxonomy = $term->taxonomy;

            $this->delete_object( 'taxonomy', $id, $taxonomy );
        }
    }

    /**
     * Hook the split_shared_term action and point it to this method
     *
     * Fires after a previously shared taxonomy term is split into two separate terms.
     *
     * @param int    $term_id          ID of the formerly shared term.
     * @param int    $new_term_id      ID of the new term created for the $term_taxonomy_id.
     * @param int    $term_taxonomy_id ID for the term_taxonomy row affected by the split.
     * @param string $taxonomy         Taxonomy for the split term.
     */
    public static function split_shared_term( $term_id, $new_term_id, $term_taxonomy_id, $taxonomy ) {

        require_once( PODS_DIR . 'classes/PodsTermSplitting.php' );

        $term_splitting = new Pods_Term_Splitting( $term_id, $new_term_id, $taxonomy );
        $term_splitting->split_shared_term();

    }

    public function delete_user ( $id ) {
        return $this->delete_object( 'user', $id );
    }

    public function delete_comment ( $id ) {
        return $this->delete_object( 'comment', $id );
    }

    public function delete_media ( $id ) {
        return $this->delete_object( 'media', $id );
    }

    public function delete_object ( $type, $id, $name = null ) {
        if ( empty( $name ) )
            $name = $type;

        $object = $this->object_get( $type, $name );

        if ( !empty( $object ) ) {
            $params = array(
                'pod' => pods_var( 'name', $object ),
                'pod_id' => pods_var( 'id', $object ),
                'id' => $id
            );

            return pods_api()->delete_pod_item( $params, false );
        }
        else
            return pods_api()->delete_object_from_relationships( $id, $type, $name );
    }
}
