<?php
/**
 * @package Pods
 * @category Object Types
 */

/**
 * Class Pods_Object_Pod
 *
 * @property Pods_Object_Field[] $object_fields Object Fields
 * @property Pods_Object_Field[] $fields Fields
 * @property Pods_Object_Group[] $groups Object Groups
 * @property array $table_info Table information for Object
 */
class Pods_Object_Pod extends
	Pods_Object {

	/**
	 * Post type / meta key prefix for internal values
	 *
	 * @var string
	 */
	protected $_post_type = '_pods_pod';

	/**
	 * Deprecated keys / options
	 *
	 * @var array
	 */
	protected $_deprecated_keys = array(
		'ID'           => 'id',
		'post_title'   => 'label',
		'post_name'    => 'name',
		'post_content' => 'description',
		'post_parent'  => 'parent_id'
	);

	/**
	 * Method names for accessing internal keys
	 *
	 * @var array
	 */
	protected $_methods = array(
		'fields',
		'object_fields',
		'table_info',
		'groups'
	);

	/**
	 * Whether the Object is a fallback or not
	 *
	 * @var bool
	 */
	protected $_is_fallback = false;

	/**
	 * {@inheritDocs}
	 */
	public function load( $name = null, $id = 0, $parent = null ) {

		/**
		 * @var $pods_init \Pods_Init
		 */
		global $pods_init;

		// Post Object
		$_object = false;

		// Custom Object
		$object = false;

		if ( null === $name && 0 == $id && null === $parent ) {
			// Allow for refresh of object
			if ( $this->is_valid() ) {
				$id = $this->_object['id'];

				$this->destroy();
			} // Empty object
			else {
				return false;
			}
		}

		// Parent ID passed
		$parent_id = $parent;

		// Parent object passed
		if ( is_object( $parent_id ) && isset( $parent_id->id ) ) {
			$parent_id = $parent_id->id;
		} // Parent array passed
		elseif ( is_array( $parent_id ) && isset( $parent_id['id'] ) ) {
			$parent_id = $parent_id['id'];
		}

		$parent_id = (int) $parent_id;

		// Object ID passed
		if ( 0 < $id ) {
			$_object = get_post( (int) $id, ARRAY_A );

			// Fallback to Object name
			if ( empty( $_object ) || $this->_post_type != $_object['post_type'] ) {
				return $this->load( $name, 0 );
			}
		} // WP_Post of Object data passed
		elseif ( is_object( $name ) && 'WP_Post' == get_class( $name ) && $this->_post_type == $name->post_type ) {
			$_object = get_object_vars( $name );
		} // Fallback for pre-WP_Post
		elseif ( is_object( $name ) && isset( $name->post_type ) && $this->_post_type == $name->post_type ) {
			$_object = get_post( (int) $name->ID, ARRAY_A );
		} // Handle custom arrays
		elseif ( is_array( $name ) ) {
			$object = $name;
		} // Handle code-registered types
		elseif ( is_object( $pods_init ) && is_object( Pods_Init::$meta ) && $meta_object = Pods_Init::$meta->get_object( null, $name, null, true ) ) {
			$object = get_object_vars( $meta_object );
		} // Find Object by name
		elseif ( ! is_object( $name ) && 0 < strlen( $name ) ) {
			$find_args = array(
				'name'           => $name,
				'post_type'      => $this->_post_type,
				'posts_per_page' => 1,
				'post_parent'    => $parent_id
			);

			// Object found
			if ( 0 !== strpos( $name, '_pods_' ) && $find_object = get_posts( $find_args ) ) {
				$_object = $find_object[0];

				if ( 'WP_Post' == get_class( $_object ) ) {
					/**
					 * @var WP_Post $_object
					 */
					$_object = $_object->to_array();
				} else {
					$_object = get_object_vars( $_object );
				}
			} // Fallback for core WP User object
			elseif ( 'user' == $name ) {
				$object = array(
					'name'           => $name,
					'label'          => __( 'Users', 'pods' ),
					'label_singular' => __( 'User', 'pods' ),
					'type'           => $name
				);

				$this->_is_fallback = true;
			} // Fallback for core WP Media object
			elseif ( 'media' == $name ) {
				$object = array(
					'name'           => $name,
					'label'          => __( 'Media', 'pods' ),
					'label_singular' => __( 'Media', 'pods' ),
					'type'           => $name
				);

				$this->_is_fallback = true;
			} // Fallback for core WP Comment object
			elseif ( 'comment' == $name ) {
				$object = array(
					'name'           => $name,
					'label'          => __( 'Comments', 'pods' ),
					'label_singular' => __( 'Comment', 'pods' ),
					'object'         => $name,
					'type'           => $name
				);

				$this->_is_fallback = true;
			} // Fallback for core WP Post Type / Taxonomy
			else {
				$post_type = get_post_type_object( $name );

				if ( empty( $post_type ) && 0 === strpos( $name, '_post_type_' ) ) {
					$name = str_replace( '_post_type_', '', $name );

					$post_type = get_post_type_object( $name );
				}

				// Fallback for core WP Post Type
				if ( ! empty( $post_type ) ) {
					$object = array(
						'name'           => $name,
						'label'          => $post_type->labels->name,
						'label_singular' => $post_type->labels->singular_name,
						'object'         => $name,
						'type'           => 'post_type'
					);

					$this->_is_fallback = true;

					// Add labels
					$object = array_merge( get_object_vars( $post_type->labels ), $object );

					// @todo Import object settings and match up to Pod options
					/*unset( $post_type->name );
					unset( $post_type->labels );

					$object = array_merge( $object, get_object_vars( $post_type ) );*/
				}

				if ( empty( $object ) ) {
					$taxonomy = get_taxonomy( $name );

					if ( empty( $taxonomy ) && 0 === strpos( $name, '_taxonomy_' ) ) {
						$name = str_replace( '_taxonomy_', '', $name );

						$taxonomy = get_taxonomy( $name );
					}

					// Fallback for core WP Taxonomy
					if ( ! empty( $taxonomy ) ) {
						$object = array(
							'name'           => $name,
							'label'          => $taxonomy->labels->name,
							'label_singular' => $taxonomy->labels->singular_name,
							'object'         => $name,
							'type'           => 'taxonomy',
							'storage'        => 'none'
						);

						$this->_is_fallback = true;

						// Add labels
						$object = array_merge( get_object_vars( $taxonomy->labels ),  $object );

						// @todo Import object settings and match up to Pod options
						/*unset( $taxonomy->name );
						unset( $taxonomy->labels );

						$object = array_merge( $object, get_object_vars( $taxonomy ) );*/
					}
				}

				if ( empty( $object ) && ( 0 === strpos( $name, '_comment_' ) || function_exists( 'get_comment_type_object' ) ) ) {
					if ( function_exists( 'get_comment_type_object' ) ) {
						$comment = get_comment_type_object( $name );

						if ( empty( $comment ) && 0 === strpos( $name, '_comment_' ) ) {
							$name = str_replace( '_comment_', '', $name );

							$comment = get_comment_type_object( $name );
						}
					} else {
						$name    = str_replace( '_comment_', '', $name );
						$comment = false;
					}

					// Fallback for core WP Comment type
					if ( ! empty( $comment ) || ! function_exists( 'get_comment_type_object' ) ) {
						$label = __( ucwords( str_replace( array( '-', '_' ), ' ', $name ) ), 'pods' );

						$object = array(
							'name'           => $name,
							'label'          => $label,
							'label_singular' => $label,
							'object'         => $name,
							'type'           => 'comment'
						);

						$this->_is_fallback = true;

						if ( ! empty( $comment ) ) {
							// Add labels
							$object = array_merge( $object, get_object_vars( $comment->labels ) );

							// @todo Import object settings and match up to Pod options
							/*unset( $comment->name );
							unset( $comment->labels );

							$object = array_merge( $object, get_object_vars( $comment ) );*/
						}
					}
				}
			}
		}

		if ( ! empty( $_object ) || ! empty( $object ) ) {
			$defaults = array(
				'id'             => 0,
				'name'           => '',
				'label'          => '',
				'label_singular' => '',
				'description'    => '',
				'type'           => 'post_type',
				'storage'        => 'meta',
				'object'         => '',
				'alias'          => '',
				'show_in_menu'   => 1,
				'parent_id'      => $parent_id
			);

			if ( ! empty( $_object ) ) {
				$object = array(
					'id'          => $_object['ID'],
					'name'        => $_object['post_name'],
					'label'       => $_object['post_title'],
					'description' => $_object['post_content'],
				);
			}

			$object = array_merge( $defaults, $object );

			if ( strlen( $object['label'] ) < 1 ) {
				$object['label'] = $object['name'];
			}

			if ( strlen( $object['label_singular'] ) < 1 ) {
				$object['label_singular'] = $object['label'];
			}

			if ( 0 < $object['id'] ) {
				$meta = array(
					'type',
					'storage',
					'object',
					'alias',
					'show_in_menu',
					'label_singular'
				);

				foreach ( $meta as $meta_key ) {
					$value = $this->_meta( $meta_key, $object['id'], true );

					if ( null !== $value ) {
						$object[ $meta_key ] = $value;
					}
				}
			}

			// Force pod type
			if ( empty( $object['type'] ) ) {
				$object['type'] = 'post_type';
			}

			// Force pod storage
			if ( empty( $object['storage'] ) ) {
				$object['storage'] = 'none';

				if ( in_array( $object['type'], array( 'taxonomy', 'settings' ) ) ) {
					$object['storage'] = 'none';
				} elseif ( in_array( $object['type'], array( 'post_type', 'media', 'user', 'comment' ) ) ) {
					$object['storage'] = 'meta';
				} else {
					$object['storage'] = 'table';
				}
			}

			$this->_object = $object;

			return $this->_object['id'];
		}

		return false;

	}

	/**
	 * Check if the object is a fallback or not
	 *
	 * @return bool Whether the object is a fallback or not
	 *
	 * @since 3.0.0
	 */
	public function is_fallback() {

		return $this->_is_fallback;

	}

	/**
	 * {@inheritDocs}
	 */
	public function exists( $name = null, $id = 0, $parent = null ) {

		$pod = pods_object_pod( $name, $id, false, $parent );

		if ( ! empty( $pod ) && $pod->is_valid() ) {
			return true;
		}

		return false;

	}

	/**
	 * {@inheritDocs}
	 */
	public function object_fields( $field = null, $option = null, $alt = true ) {

		if ( empty( $this->_object_fields ) ) {
			if ( $this->is_custom() && isset( $this->_object['object_fields'] ) && ! empty( $this->_object['object_fields'] ) ) {
				$object_fields = $this->_object['object_fields'];
			} else {
				$object_fields = pods_api()->get_wp_object_fields( $this->_object['type'], $this->_object );
			}

			$this->_object_fields = array();

			foreach ( $object_fields as $object_field ) {
				$object_field = pods_object_field( $object_field, 0, $this->_live, $this->_object['id'] );

				if ( $object_field->is_valid() ) {
					$this->_object_fields[ $object_field['name'] ] = $object_field;
				}
			}
		}

		return $this->_fields( 'object_fields', $field, $option );

	}

	/**
	 * {@inheritDocs}
	 */
	public function table_info() {

		if ( ! $this->is_valid() ) {
			return array();
		}

		if ( empty( $this->_table_info ) ) {
			$this->_table_info = pods_api()->get_table_info( $this->_object['type'], $this->_object['object'], $this->_object['name'], $this );
		}

		return $this->_table_info;

	}

	/**
	 * Get list of Pod option tabs for Admin UI
	 *
	 * @return array
	 */
	public function admin_tabs() {

		$pod =& $this;

		$meta_boxes = true;
		$labels     = false;
		$admin_ui   = false;
		$advanced   = false;

		if ( 'post_type' == $pod['type'] && strlen( $pod['object'] ) < 1 ) {
			$labels   = true;
			$admin_ui = true;
			$advanced = true;
		} elseif ( 'taxonomy' == $pod['type'] && strlen( $pod['object'] ) < 1 ) {
			$labels   = true;
			$admin_ui = true;
			$advanced = true;
		} elseif ( 'pod' == $pod['type'] ) {
			$labels   = true;
			$admin_ui = true;
			$advanced = true;
		} elseif ( 'settings' == $pod['type'] ) {
			$labels   = true;
			$admin_ui = true;
		}

		if ( 'none' == pods_v( 'storage', $pod, 'none', true ) && 'settings' != $pod['type'] ) {
			$meta_boxes = false;
		}

		$tabs = array();

		if ( $meta_boxes ) {
			$tabs['manage-groups'] = __( 'Field Groups', 'pods' );
		}

		if ( $labels ) {
			$tabs['labels'] = __( 'Labels', 'pods' );
		}

		if ( $admin_ui ) {
			$tabs['admin-ui'] = __( 'Admin UI', 'pods' );
		}

		if ( $advanced ) {
			$tabs['advanced'] = __( 'Advanced Options', 'pods' );
		}

		if ( 'taxonomy' == $pod['type'] && ! $meta_boxes ) {
			$tabs['extra-fields'] = __( 'Extra Fields', 'pods' );
		}

		$tabs = apply_filters( 'pods_admin_setup_edit_tabs_' . $pod['type'] . '_' . $pod['name'], $tabs, $pod );
		$tabs = apply_filters( 'pods_admin_setup_edit_tabs_' . $pod['type'], $tabs, $pod );
		$tabs = apply_filters( 'pods_admin_setup_edit_tabs', $tabs, $pod );

		return $tabs;

	}

	/**
	 * Get list of Pod options for Admin UI
	 *
	 * @return array
	 */
	public function admin_options() {

		$pod =& $this;

		$options = array();

		if ( 'settings' == $pod['type'] ) {
			$options['labels'] = array(
				'label'     => array(
					'label'           => __( 'Page Title', 'pods' ),
					'help'            => __( 'help', 'pods' ),
					'type'            => 'text',
					'default'         => '',
					'text_max_length' => 30
				),
				'menu_name' => array(
					'label'           => __( 'Menu Name', 'pods' ),
					'help'            => __( 'help', 'pods' ),
					'type'            => 'text',
					'default'         => pods_v( 'label', $pod, ucwords( str_replace( '_', ' ', $pod['name'] ) ), true ),
					'text_max_length' => 30
				)
			);
		} elseif ( '' == $pod['object'] ) {
			$options['labels'] = array(
				'label'                            => array(
					'label'           => __( 'Label', 'pods' ),
					'help'            => __( 'help', 'pods' ),
					'type'            => 'text',
					'default'         => '',
					'text_max_length' => 30
				),
				'label_singular'                   => array(
					'label'           => __( 'Singular Label', 'pods' ),
					'help'            => __( 'help', 'pods' ),
					'type'            => 'text',
					'default'         => pods_v( 'label', $pod, ucwords( str_replace( '_', ' ', $pod['name'] ) ), true ),
					'text_max_length' => 30
				),
				'label_add_new'                    => array(
					'label'   => __( 'Add New', 'pods' ),
					'help'    => __( 'help', 'pods' ),
					'type'    => 'text',
					'default' => ''
				),
				'label_add_new_item'               => array(
					'label'   => sprintf( __( 'Add New %s', 'pods' ), '<span class="pods-slugged" data-sluggable="label_singular">' . __( 'Item', 'pods' ) . '</span>' ),
					'help'    => __( 'help', 'pods' ),
					'type'    => 'text',
					'default' => ''
				),
				'label_new_item'                   => array(
					'label'   => sprintf( __( 'New %s', 'pods' ), '<span class="pods-slugged" data-sluggable="label_singular">' . __( 'Item', 'pods' ) . '</span>' ),
					'help'    => __( 'help', 'pods' ),
					'type'    => 'text',
					'default' => ''
				),
				'label_edit'                       => array(
					'label'   => __( 'Edit', 'pods' ),
					'help'    => __( 'help', 'pods' ),
					'type'    => 'text',
					'default' => ''
				),
				'label_edit_item'                  => array(
					'label'   => sprintf( __( 'Edit %s', 'pods' ), '<span class="pods-slugged" data-sluggable="label_singular">' . __( 'Item', 'pods' ) . '</span>' ),
					'help'    => __( 'help', 'pods' ),
					'type'    => 'text',
					'default' => ''
				),
				'label_update_item'                => array(
					'label'   => sprintf( __( 'Update %s', 'pods' ), '<span class="pods-slugged" data-sluggable="label_singular">' . __( 'Item', 'pods' ) . '</span>' ),
					'help'    => __( 'help', 'pods' ),
					'type'    => 'text',
					'default' => ''
				),
				'label_view'                       => array(
					'label'   => __( 'View', 'pods' ),
					'help'    => __( 'help', 'pods' ),
					'type'    => 'text',
					'default' => ''
				),
				'label_view_item'                  => array(
					'label'   => sprintf( __( 'View %s', 'pods' ), '<span class="pods-slugged" data-sluggable="label_singular">' . __( 'Item', 'pods' ) . '</span>' ),
					'help'    => __( 'help', 'pods' ),
					'type'    => 'text',
					'default' => ''
				),
				'label_all_items'                  => array(
					'label'   => sprintf( __( 'All %s', 'pods' ), '<span class="pods-slugged" data-sluggable="label">' . __( 'Items', 'pods' ) . '</span>' ),
					'help'    => __( 'help', 'pods' ),
					'type'    => 'text',
					'default' => ''
				),
				'label_search_items'               => array(
					'label'   => sprintf( __( 'Search %s', 'pods' ), '<span class="pods-slugged" data-sluggable="label">' . __( 'Items', 'pods' ) . '</span>' ),
					'help'    => __( 'help', 'pods' ),
					'type'    => 'text',
					'default' => ''
				),
				'label_not_found'                  => array(
					'label'   => __( 'Not Found', 'pods' ),
					'help'    => __( 'help', 'pods' ),
					'type'    => 'text',
					'default' => ''
				),
				'label_not_found_in_trash'         => array(
					'label'   => __( 'Not Found in Trash', 'pods' ),
					'help'    => __( 'help', 'pods' ),
					'type'    => 'text',
					'default' => ''
				),
				'label_popular_items'              => array(
					'label'   => sprintf( __( 'Popular %s', 'pods' ), '<span class="pods-slugged" data-sluggable="label">' . __( 'Items', 'pods' ) . '</span>' ),
					'help'    => __( 'help', 'pods' ),
					'type'    => 'text',
					'default' => ''
				),
				'label_separate_items_with_commas' => array(
					'label'   => sprintf( __( 'Separate %s with commas', 'pods' ), '<span class="pods-slugged-lower" data-sluggable="label">' . __( 'Items', 'pods' ) . '</span>' ),
					'help'    => __( 'help', 'pods' ),
					'type'    => 'text',
					'default' => ''
				),
				'label_add_or_remove_items'        => array(
					'label'   => sprintf( __( 'Add or remove %s', 'pods' ), '<span class="pods-slugged-lower" data-sluggable="label">' . __( 'Items', 'pods' ) . '</span>' ),
					'help'    => __( 'help', 'pods' ),
					'type'    => 'text',
					'default' => ''
				),
				'label_choose_from_the_most_used'  => array(
					'label'   => __( 'Choose from the most used', 'pods' ),
					'help'    => __( 'help', 'pods' ),
					'type'    => 'text',
					'default' => ''
				)
			);

			if ( ! in_array( $pod['type'], array( 'post_type', 'comment' ) ) ) {
				unset( $options['labels']['label_not_found_in_trash'] );
			}

			if ( 'taxonomy' != $pod['type'] ) {
				unset( $options['labels']['label_popular_items'] );
				unset( $options['labels']['label_separate_items_with_commas'] );
				unset( $options['labels']['label_add_or_remove_items'] );
				unset( $options['labels']['label_choose_from_the_most_used'] );
			}
		}

		if ( 'post_type' == $pod['type'] ) {
			$options['admin-ui'] = array(
				'description'          => array(
					'label'   => __( 'Post Type Description', 'pods' ),
					'help'    => __( 'A short descriptive summary of what the post type is.', 'pods' ),
					'type'    => 'text',
					'default' => ''
				),
				'show_ui'              => array(
					'label'             => __( 'Show Admin UI', 'pods' ),
					'help'              => __( 'help', 'pods' ),
					'type'              => 'boolean',
					'default'           => true,
					'boolean_yes_label' => ''
				),
				'show_in_menu'         => array(
					'label'             => __( 'Show Admin Menu in Dashboard', 'pods' ),
					'help'              => __( 'help', 'pods' ),
					'type'              => 'boolean',
					'default'           => true,
					'dependency'        => true,
					'boolean_yes_label' => ''
				),
				'menu_location_custom' => array(
					'label'      => __( 'Parent Menu ID (optional)', 'pods' ),
					'help'       => __( 'help', 'pods' ),
					'type'       => 'text',
					'depends-on' => array( 'show_in_menu' => true )
				),
				'menu_name'            => array(
					'label'      => __( 'Menu Name', 'pods' ),
					'help'       => __( 'help', 'pods' ),
					'type'       => 'text',
					'default'    => '',
					'depends-on' => array( 'show_in_menu' => true )
				),
				'menu_position'        => array(
					'label'      => __( 'Menu Position', 'pods' ),
					'help'       => __( 'help', 'pods' ),
					'type'       => 'number',
					'default'    => 0,
					'depends-on' => array( 'show_in_menu' => true )
				),
				'menu_icon'            => array(
					'label'      => __( 'Menu Icon', 'pods' ),
					'help'       => __( 'URL or Dashicon name for the menu icon. You may specify the path to the icon using one of the <a href="http://pods.io/docs/build/special-magic-tags/#site-tags" target="_blank">site tag</a> type <a href="http://pods.io/docs/build/special-magic-tags/" target="_blank">special magic tags</a>. For example, for a file in your theme directory, use "{@template-url}/path/to/image.png". You may also use the name of a <a href="http://melchoyce.github.io/dashicons/" target="_blank">Dashicon</a>. For example, to use the empty star icon, use "dashicons-star-empty".', 'pods' ),
					'type'       => 'text',
					'default'    => '',
					'depends-on' => array( 'show_in_menu' => true )
				),
				'show_in_nav_menus'    => array(
					'label'             => __( 'Show in Navigation Menus', 'pods' ),
					'help'              => __( 'help', 'pods' ),
					'type'              => 'boolean',
					'default'           => true,
					'boolean_yes_label' => ''
				),
				'show_in_admin_bar'    => array(
					'label'             => __( 'Show in Admin Bar "New" Menu', 'pods' ),
					'help'              => __( 'help', 'pods' ),
					'type'              => 'boolean',
					'default'           => true,
					'boolean_yes_label' => ''
				)
			);

			$options['advanced'] = array(
				'public'                  => array(
					'label'             => __( 'Public', 'pods' ),
					'help'              => __( 'help', 'pods' ),
					'type'              => 'boolean',
					'default'           => true,
					'boolean_yes_label' => ''
				),
				'publicly_queryable'      => array(
					'label'             => __( 'Publicly Queryable', 'pods' ),
					'help'              => __( 'help', 'pods' ),
					'type'              => 'boolean',
					'default'           => true,
					'boolean_yes_label' => ''
				),
				'exclude_from_search'     => array(
					'label'             => __( 'Exclude from Search', 'pods' ),
					'help'              => __( 'help', 'pods' ),
					'type'              => 'boolean',
					'default'           => ! true,
					'boolean_yes_label' => ''
				),
				'capability_type'         => array(
					'label'      => __( 'User Capability', 'pods' ),
					'help'       => __( 'Uses these capabilties for access to this post type: edit_{capability}, read_{capability}, and delete_{capability}', 'pods' ),
					'type'       => 'pick',
					'default'    => 'post',
					'data'       => array(
						'post'   => 'post',
						'page'   => 'page',
						'custom' => __( 'Custom Capability', 'pods' )
					),
					'dependency' => true
				),
				'capability_type_custom'  => array(
					'label'      => __( 'Custom User Capability', 'pods' ),
					'help'       => __( 'help', 'pods' ),
					'type'       => 'text',
					'default'    => $pod['name'],
					'depends-on' => array( 'capability_type' => 'custom' )
				),
				'capability_type_extra'   => array(
					'label'             => __( 'Additional User Capabilities', 'pods' ),
					'help'              => __( 'Enables additional capabilities for this Post Type including: delete_{capability}s, delete_private_{capability}s, delete_published_{capability}s, delete_others_{capability}s, edit_private_{capability}s, and edit_published_{capability}s', 'pods' ),
					'type'              => 'boolean',
					'default'           => true,
					'boolean_yes_label' => ''
				),
				'has_archive'             => array(
					'label'             => __( 'Enable Archive Page', 'pods' ),
					'help'              => __( 'If enabled, creates an archive page with list of of items in this custom post type. Functions like a category page for posts. Can be controlled with a template in your theme called "archive-{$post-type}.php".', 'pods' ),
					'type'              => 'boolean',
					'default'           => false,
					'boolean_yes_label' => ''
				),
				'hierarchical'            => array(
					'label'             => __( 'Hierarchical', 'pods' ),
					'help'              => __( 'help', 'pods' ),
					'type'              => 'boolean',
					'default'           => false,
					'dependency'        => true,
					'boolean_yes_label' => ''
				),
				'label_parent_item_colon' => array(
					'label'      => __( '<strong>Label: </strong> Parent <span class="pods-slugged" data-sluggable="label_singular">Item</span>', 'pods' ),
					'help'       => __( 'help', 'pods' ),
					'type'       => 'text',
					'default'    => '',
					'depends-on' => array( 'hierarchical' => true )
				),
				'label_parent'            => array(
					'label'      => __( '<strong>Label: </strong> Parent', 'pods' ),
					'help'       => __( 'help', 'pods' ),
					'type'       => 'text',
					'default'    => '',
					'depends-on' => array( 'hierarchical' => true )
				),
				'rewrite'                 => array(
					'label'             => __( 'Rewrite', 'pods' ),
					'help'              => __( 'Allows you to use pretty permalinks, if set in WordPress Settings->Reading. If not enbabled, your links will be in the form of "example.com/?pod_name=post_slug" regardless of your permalink settings.', 'pods' ),
					'type'              => 'boolean',
					'default'           => true,
					'dependency'        => true,
					'boolean_yes_label' => ''
				),
				'rewrite_custom_slug'     => array(
					'label'      => __( 'Custom Rewrite Slug', 'pods' ),
					'help'       => __( 'Changes the first segment of the URL, which by default is the name of the Pod. For example, if your Pod is called "foo", if this field is left blank, your link will be "example.com/foo/post_slug", but if you were to enter "bar" your link will be "example.com/bar/post_slug".', 'pods' ),
					'type'       => 'text',
					'default'    => '',
					'depends-on' => array( 'rewrite' => true )
				),
				'rewrite_with_front'      => array(
					'label'             => __( 'Rewrite with Front', 'pods' ),
					'help'              => __( 'Allows permalinks to be prepended with your front base. For example, if your permalink structure is /blog/, then your links will be: Unchecked->/news/, Checked->/blog/news/', 'pods' ),
					'type'              => 'boolean',
					'default'           => true,
					'depends-on'        => array( 'rewrite' => true ),
					'boolean_yes_label' => ''
				),
				'rewrite_feeds'           => array(
					'label'             => __( 'Rewrite Feeds', 'pods' ),
					'help'              => __( 'Apply rewrites to RSS feeds.', 'pods' ),
					'type'              => 'boolean',
					'default'           => false,
					'depends-on'        => array( 'rewrite' => true ),
					'boolean_yes_label' => ''
				),
				'rewrite_pages'           => array(
					'label'             => __( 'Rewrite With Page Numbers', 'pods' ),
					'help'              => __( 'Add page numbers to URLs for paged posts or post type archives.', 'pods' ),
					'type'              => 'boolean',
					'default'           => true,
					'depends-on'        => array( 'rewrite' => true ),
					'boolean_yes_label' => ''
				),
				'query_var'               => array(
					'label'             => __( 'Query Var', 'pods' ),
					'help'              => __( 'The Query Var is used in the URL and underneath the WordPress Rewrite API to tell WordPress what page or post type you are on. For a list of reserved Query Vars, read <a href="http://codex.wordpress.org/WordPress_Query_Vars">WordPress Query Vars</a> from the WordPress Codex.', 'pods' ),
					'type'              => 'boolean',
					'default'           => true,
					'boolean_yes_label' => ''
				),
				'can_export'              => array(
					'label'             => __( 'Exportable', 'pods' ),
					'help'              => __( 'help', 'pods' ),
					'type'              => 'boolean',
					'default'           => true,
					'boolean_yes_label' => ''
				),
				'default_status'          => array(
					'label'       => __( 'Default Status', 'pods' ),
					'help'        => __( 'help', 'pods' ),
					'type'        => 'pick',
					'pick_object' => 'post-status',
					'default'     => apply_filters( 'pods_api_default_status_' . pods_v( 'name', $pod, 'post_type', true ), 'draft', $pod )
				)
			);
		} elseif ( 'taxonomy' == $pod['type'] ) {
			$options['admin-ui'] = array(
				'show_ui'              => array(
					'label'             => __( 'Show Admin UI', 'pods' ),
					'help'              => __( 'help', 'pods' ),
					'type'              => 'boolean',
					'default'           => true,
					'dependency'        => true,
					'boolean_yes_label' => ''
				),
				'menu_name'            => array(
					'label'      => __( 'Menu Name', 'pods' ),
					'help'       => __( 'help', 'pods' ),
					'type'       => 'text',
					'default'    => '',
					'depends-on' => array( 'show_ui' => true )
				),
				'menu_location'        => array(
					'label'      => __( 'Menu Location', 'pods' ),
					'help'       => __( 'help', 'pods' ),
					'type'       => 'pick',
					'default'    => 'default',
					'depends-on' => array( 'show_ui' => true ),
					'data'       => array(
						'default'     => __( 'Default - Add to associated Post Type(s) menus', 'pods' ),
						'settings'    => __( 'Add to Settings menu', 'pods' ),
						'appearances' => __( 'Add to Appearances menu', 'pods' ),
						'objects'     => __( 'Make a top-level menu item', 'pods' ),
						'top'         => __( 'Make a new top-level menu item below Settings', 'pods' ),
						'submenu'     => __( 'Add a submenu item to another menu', 'pods' )
					),
					'dependency' => true
				),
				'menu_location_custom' => array(
					'label'      => __( 'Custom Menu Location', 'pods' ),
					'help'       => __( 'help', 'pods' ),
					'type'       => 'text',
					'depends-on' => array( 'menu_location' => 'submenu' )
				),
				'menu_position'        => array(
					'label'      => __( 'Menu Position', 'pods' ),
					'help'       => __( 'help', 'pods' ),
					'type'       => 'number',
					'default'    => 0,
					'depends-on' => array( 'menu_location' => array( 'objects', 'top' ) )
				),
				'menu_icon'            => array(
					'label'      => __( 'Menu Icon', 'pods' ),
					'help'       => __( 'URL or Dashicon name for the menu icon. You may specify the path to the icon using one of the <a href="http://pods.io/docs/build/special-magic-tags/#site-tags" target="_blank">site tag</a> type <a href="http://pods.io/docs/build/special-magic-tags/" target="_blank">special magic tags</a>. For example, for a file in your theme directory, use "{@template-url}/path/to/image.png". You may also use the name of a <a href="http://melchoyce.github.io/dashicons/" target="_blank">Dashicon</a>. For example, to use the empty star icon, use "dashicons-star-empty".', 'pods' ),
					'type'       => 'text',
					'default'    => '',
					'depends-on' => array( 'menu_location' => array( 'objects', 'top' ) )
				),
				'show_in_nav_menus'    => array(
					'label'             => __( 'Show in Navigation Menus', 'pods' ),
					'help'              => __( 'help', 'pods' ),
					'type'              => 'boolean',
					'default'           => true,
					'boolean_yes_label' => ''
				),
				'show_tagcloud'        => array(
					'label'             => __( 'Allow in Tagcloud Widget', 'pods' ),
					'help'              => __( 'help', 'pods' ),
					'type'              => 'boolean',
					'default'           => true,
					'boolean_yes_label' => ''
				),
				'show_admin_column'    => array(
					'label'             => __( 'Show Taxonomy column on Post Types', 'pods' ),
					'help'              => __( 'Whether to add a column for this taxonomy on the associated post types manage screens', 'pods' ),
					'type'              => 'boolean',
					'default'           => false,
					'boolean_yes_label' => ''
				)
			);

			if ( pods_version_check( 'wp', '3.8' ) ) {
				$options['admin-ui']['hide_metabox'] = array(
					'label'             => __( 'Hide Meta Box in Post editor', 'pods' ),
					//'help' => __( 'Whether to add a column for this taxonomy on the associated post types manage screens', 'pods' ),
					'type'              => 'boolean',
					'default'           => false,
					'boolean_yes_label' => ''
				);
			}

			// Integration for Single Value Taxonomy UI
			if ( function_exists( 'tax_single_value_meta_box' ) ) {
				$options['admin-ui']['single_value'] = array(
					'label'             => __( 'Single Value Taxonomy', 'pods' ),
					'help'              => __( 'Use a drop-down for the input instead of the WordPress default', 'pods' ),
					'type'              => 'boolean',
					'default'           => false,
					'boolean_yes_label' => ''
				);

				$options['admin-ui']['single_value_required'] = array(
					'label'             => __( 'Single Value Taxonomy - Required', 'pods' ),
					'help'              => __( 'A term will be selected by default in the Post Editor, not optional', 'pods' ),
					'type'              => 'boolean',
					'default'           => false,
					'boolean_yes_label' => ''
				);
			}

			// @todo fill this in
			$options['advanced'] = array(
				'temporary' => 'This type has the fields hardcoded' // :(
			);
		} elseif ( 'settings' == $pod['type'] ) {
			$options['admin-ui'] = array(
				'ui_style'             => array(
					'label'      => __( 'Admin UI Style', 'pods' ),
					'help'       => __( 'help', 'pods' ),
					'type'       => 'pick',
					'default'    => 'settings',
					'data'       => array(
						'settings'  => __( 'Normal Settings Form', 'pods' ),
						'post_type' => __( 'Post Type UI', 'pods' ),
						'custom'    => __( 'Custom (hook into pods_admin_ui_custom or pods_admin_ui_custom_{podname} action)', 'pods' )
					),
					'dependency' => true
				),
				'menu_location'        => array(
					'label'      => __( 'Menu Location', 'pods' ),
					'help'       => __( 'help', 'pods' ),
					'type'       => 'pick',
					'default'    => 'settings',
					'data'       => array(
						'settings'    => __( 'Add to Settings menu', 'pods' ),
						'appearances' => __( 'Add to Appearances menu', 'pods' ),
						'top'         => __( 'Make a new top-level menu item below Settings', 'pods' ),
						'submenu'     => __( 'Add a submenu item to another menu', 'pods' )
					),
					'dependency' => true
				),
				'menu_location_custom' => array(
					'label'      => __( 'Custom Menu Location', 'pods' ),
					'help'       => __( 'help', 'pods' ),
					'type'       => 'text',
					'depends-on' => array( 'menu_location' => 'submenu' )
				),
				'menu_position'        => array(
					'label'      => __( 'Menu Position', 'pods' ),
					'help'       => __( 'help', 'pods' ),
					'type'       => 'number',
					'default'    => 0,
					'depends-on' => array( 'menu_location' => 'top' )
				),
				'menu_icon'            => array(
					'label'      => __( 'Menu Icon', 'pods' ),
					'help'       => __( 'URL or Dashicon name for the menu icon. You may specify the path to the icon using one of the <a href="http://pods.io/docs/build/special-magic-tags/#site-tags" target="_blank">site tag</a> type <a href="http://pods.io/docs/build/special-magic-tags/" target="_blank">special magic tags</a>. For example, for a file in your theme directory, use "{@template-url}/path/to/image.png". You may also use the name of a <a href="http://melchoyce.github.io/dashicons/" target="_blank">Dashicon</a>. For example, to use the empty star icon, use "dashicons-star-empty".', 'pods' ),
					'type'       => 'text',
					'default'    => '',
					'depends-on' => array( 'menu_location' => 'top' )
				)
			);

			$options['advanced'] = array(
				'restrict_role'       => array(
					'label'      => __( 'Restrict access by Role?', 'pods' ),
					'help'       => array(
						__( '<h6>Roles</h6> Roles are assigned to users to provide them access to specific functionality in WordPress. Please see the Roles and Capabilities component in Pods for an easy tool to add your own roles and edit existing ones.', 'pods' ),
						'http://codex.wordpress.org/Roles_and_Capabilities'
					),
					'default'    => 0,
					'type'       => 'boolean',
					'dependency' => true
				),
				'roles_allowed'       => array(
					'label'             => __( 'Role(s) Allowed', 'pods' ),
					'type'              => 'pick',
					'pick_object'       => 'role',
					'pick_format_type'  => 'multi',
					'pick_format_multi' => 'autocomplete',
					'pick_ajax'         => false,
					'default'           => '',
					'depends-on'        => array(
						'restrict_role' => true
					)
				),
				'restrict_capability' => array(
					'label'      => __( 'Restrict access by Capability?', 'pods' ),
					'help'       => array(
						__( '<h6>Capabilities</h6> Capabilities denote access to specific functionality in WordPress, and are assigned to specific User Roles. Please see the Roles and Capabilities component in Pods for an easy tool to add your own capabilities and roles.', 'pods' ),
						'http://codex.wordpress.org/Roles_and_Capabilities'
					),
					'default'    => 0,
					'type'       => 'boolean',
					'dependency' => true
				),
				'capability_allowed'  => array(
					'label'             => __( 'Capability Allowed', 'pods' ),
					'type'              => 'pick',
					'pick_object'       => 'capability',
					'pick_format_type'  => 'multi',
					'pick_format_multi' => 'autocomplete',
					'pick_ajax'         => false,
					'default'           => '',
					'depends-on'        => array(
						'restrict_capability' => true
					)
				)
			);
		} elseif ( 'pod' == $pod['type'] ) {
			$options['admin-ui'] = array(
				'ui_style'             => array(
					'label'      => __( 'Admin UI Style', 'pods' ),
					'help'       => __( 'help', 'pods' ),
					'type'       => 'pick',
					'default'    => 'settings',
					'data'       => array(
						'post_type' => __( 'Normal (Looks like the Post Type UI)', 'pods' ),
						'custom'    => __( 'Custom (hook into pods_admin_ui_custom or pods_admin_ui_custom_{podname} action)', 'pods' )
					),
					'dependency' => true
				),
				'show_in_menu'         => array(
					'label'             => __( 'Show Admin Menu in Dashboard', 'pods' ),
					'help'              => __( 'help', 'pods' ),
					'type'              => 'boolean',
					'default'           => false,
					'boolean_yes_label' => '',
					'dependency'        => true
				),
				'menu_location_custom' => array(
					'label'      => __( 'Parent Menu ID (optional)', 'pods' ),
					'help'       => __( 'help', 'pods' ),
					'type'       => 'text',
					'depends-on' => array( 'show_in_menu' => true )
				),
				'menu_position'        => array(
					'label'      => __( 'Menu Position', 'pods' ),
					'help'       => __( 'help', 'pods' ),
					'type'       => 'number',
					'default'    => 0,
					'depends-on' => array( 'show_in_menu' => true )
				),
				'menu_icon'            => array(
					'label'      => __( 'Menu Icon', 'pods' ),
					'help'       => __( 'URL or Dashicon name for the menu icon. You may specify the path to the icon using one of the <a href="http://pods.io/docs/build/special-magic-tags/#site-tags" target="_blank">site tag</a> type <a href="http://pods.io/docs/build/special-magic-tags/" target="_blank">special magic tags</a>. For example, for a file in your theme directory, use "{@template-url}/path/to/image.png". You may also use the name of a <a href="http://melchoyce.github.io/dashicons/" target="_blank">Dashicon</a>. For example, to use the empty star icon, use "dashicons-star-empty".', 'pods' ),
					'type'       => 'text',
					'default'    => '',
					'depends-on' => array( 'show_in_menu' => true )
				),
				'ui_icon'              => array(
					'label'           => __( 'Header Icon', 'pods' ),
					'help'            => __( 'This is the icon shown to the left of the heading text at the top of the manage pages for this content type.', 'pods' ),
					'type'            => 'file',
					'default'         => '',
					'file_edit_title' => 0,
					'depends-on'      => array( 'show_in_menu' => true )
				),
				'ui_actions_enabled'   => array(
					'label'            => __( 'Actions Available', 'pods' ),
					'help'             => __( 'help', 'pods' ),
					'type'             => 'pick',
					'default'          => ( 1 == $pod['ui_export']
						? array(
							'add',
							'edit',
							'duplicate',
							'delete',
							'export'
						)
						: array(
							'add',
							'edit',
							'duplicate',
							'delete'
						) ),
					'data'             => array(
						'add'       => __( 'Add New', 'pods' ),
						'edit'      => __( 'Edit', 'pods' ),
						'duplicate' => __( 'Duplicate', 'pods' ),
						'delete'    => __( 'Delete', 'pods' ),
						'reorder'   => __( 'Reorder', 'pods' ),
						'export'    => __( 'Export', 'pods' )
					),
					'pick_format_type' => 'multi',
					'dependency'       => true
				),
				'ui_reorder_field'     => array(
					'label'      => __( 'Reorder Field', 'pods' ),
					'help'       => __( 'This is the field that will be reordered on, it should be numeric.', 'pods' ),
					'type'       => 'text',
					'default'    => 'menu_order',
					'depends-on' => array( 'ui_actions_enabled' => 'reorder' )
				),
				'ui_fields_manage'     => array(
					'label'            => __( 'Admin Table Columns', 'pods' ),
					'help'             => __( 'help', 'pods' ),
					'type'             => 'pick',
					'default'          => array(),
					'data'             => array(),
					'pick_format_type' => 'multi'
				),
				'ui_filters'           => array(
					'label'            => __( 'Search Filters', 'pods' ),
					'help'             => __( 'help', 'pods' ),
					'type'             => 'pick',
					'default'          => array(),
					'data'             => array(),
					'pick_format_type' => 'multi'
				)
			);

			if ( ! empty( $pod['fields'] ) ) {
				if ( isset( $pod['fields'][ pods_v( 'pod_index', $pod, 'name', true ) ] ) ) {
					$options['admin-ui']['ui_fields_manage']['default'][] = pods_v( 'pod_index', $pod, 'name', true );
				}

				if ( isset( $pod['fields']['modified'] ) ) {
					$options['admin-ui']['ui_fields_manage']['default'][] = 'modified';
				}

				foreach ( $pod['fields'] as $field ) {
					$type = '';

					if ( isset( $field_types[ $field['type'] ] ) ) {
						$type = ' <small>(' . $field_types[ $field['type'] ]['label'] . ')</small>';
					}

					$options['admin-ui']['ui_fields_manage']['data'][ $field['name'] ] = $field['label'] . $type;
					$options['admin-ui']['ui_filters']['data'][ $field['name'] ]       = $field['label'] . $type;
				}

				$options['admin-ui']['ui_fields_manage']['data']['id'] = 'ID';
			} else {
				unset( $options['admin-ui']['ui_fields_manage'] );
				unset( $options['admin-ui']['ui_filters'] );
			}

			// @todo fill this in
			$options['advanced'] = array(
				'temporary' => 'This type has the fields hardcoded' // :(
			);
		}

		$options = apply_filters( 'pods_admin_setup_edit_options_' . $pod['type'] . '_' . $pod['name'], $options, $pod );
		$options = apply_filters( 'pods_admin_setup_edit_options_' . $pod['type'], $options, $pod );
		$options = apply_filters( 'pods_admin_setup_edit_options', $options, $pod );

		foreach ( $options as $option_group => $option_group_opts ) {
			foreach ( $option_group_opts as $option => $option_data ) {
				$this->_options[ $option ] = $option_data;
			}
		}

		return $options;

	}

	/**
	 * {@inheritDocs}
	 */
	public function save( $options = null, $value = null, $refresh = true ) {

		if ( null !== $value && ! is_array( $options ) && ! is_object( $options ) ) {
			$options = array(
				$options => $value
			);
		}

		if ( empty( $options ) ) {
			if ( $this->is_valid() ) {
				return $this->_object['id'];
			}

			return false;
		} elseif ( ! is_array( $options ) && ! is_object( $options ) ) {
			return false;
		}

		$tableless_field_types    = Pods_Form::tableless_field_types();
		$simple_tableless_objects = Pods_Form::simple_tableless_objects();

		$params = (object) $options;

		if ( $this->is_valid() ) {
			$params->id = $this->_object['id'];
		} elseif ( ! isset( $params->id ) ) {
			$params->id = 0;
		}

		if ( ! isset( $params->db ) ) {
			$params->db = true;
		}

		$api = pods_api();

		$pod_fields = $this->fields();

		$params = apply_filters( 'pods_object_pre_save_' . $this->_action_type, $params, $this );

		$old_id = $old_name = $old_storage = null;

		$old_fields = array();

		if ( isset( $params->name ) ) {
			$params->name = pods_clean_name( $params->name, true, false );

			if ( ! isset( $params->label ) ) {
				$params->label = $params->name;
			}
		}

		$old_pod = pods_object_pod( $params->name );

		if ( $old_pod->is_valid() ) {
			if ( $old_pod->is_custom() && ! $old_pod->is_fallback() ) {
				return pods_error( sprintf( __( 'Pod %s was registered through code, you cannot modify it.', 'pods' ), $params->name ) );
			}

			/*if ( isset( $params->id ) && 0 < $params->id ) {
				$old_id = $params->id;
			}*/

			$old_id      = $old_pod['id'];
			$old_name    = $old_pod['name'];
			$old_storage = $old_pod['storage'];
			$old_fields  = $old_pod->fields();

			if ( ! isset( $params->name ) && empty( $params->name ) ) {
				$params->name = $old_pod['name'];
			}

			if ( $old_name != $params->name && false !== $this->exists( $params->name ) ) {
				return pods_error( sprintf( __( 'Pod %s already exists, you cannot rename %s to that', 'pods' ), $params->name, $old_name ) );
			}

			$wp_object_types = array(
				'user',
				'comment',
				'media'
			);

			if ( $old_name != $params->name && in_array( $old_pod['type'], $wp_object_types ) && in_array( $old_pod['object'], $wp_object_types ) ) {
				return pods_error( sprintf( __( 'Pod %s cannot be renamed, it extends an existing WP Object', 'pods' ), $old_name ) );
			}

			if ( $old_name != $params->name && in_array( $old_pod['type'], array( 'post_type', 'taxonomy' ) ) && ! empty( $old_pod['object'] ) && $old_pod['object'] == $old_name ) {
				return pods_error( sprintf( __( 'Pod %s cannot be renamed, it extends an existing WP Object', 'pods' ), $old_name ) );
			}

			if ( $old_id != $params->id ) {
				if ( $params->type == $old_pod['type'] && isset( $params->object ) && $params->object == $old_pod['object'] ) {
					return pods_error( sprintf( __( 'Pod using %s already exists, you can not reuse an object across multiple pods', 'pods' ), $params->object ) );
				} else {
					return pods_error( sprintf( __( 'Pod %s already exists', 'pods' ), $params->name ) );
				}
			}

			$pod =& $this;
		} elseif ( in_array( $params->name, array( 'order', 'orderby', 'post_type' ) ) && 'post_type' == pods_v( 'type', $params ) ) {
			return pods_error( sprintf( 'There are certain names that a Custom Post Type cannot be named and unfortunately, %s is one of them.', $params->name ) );
		} else {
			$pod = array(
				'id'          => 0,
				'name'        => $params->name,
				'label'       => $params->label,
				'description' => '',
				'type'        => 'pod',
				'storage'     => 'table',
				'object'      => '',
				'alias'       => ''
			);

			$pod = pods_object_pod( $pod );
		}

		// Blank out fields and options for AJAX calls (everything should be sent to it for a full overwrite)
		// @todo Does this still work with pods_object_pod?
		if ( isset( $params->fields ) && defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			$pod_fields = array();
		}

		// Setup options
		$options = get_object_vars( $params );

		$options_ignore = array(
			'db',
			'method',
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

		if ( isset( $options['options'] ) ) {
			$options = array_merge( $options['options'], $options );

			unset( $options['options'] );
		}

		if ( isset( $options['fields'] ) ) {
			$pod_fields = $options['fields'];

			unset( $options['fields'] );
		}

		if ( pods_tableless() && ! in_array( $pod['type'], array( 'settings', 'table' ) ) ) {
			if ( 'pod' == $pod['type'] ) {
				$pod['type'] = 'post_type';
			}

			if ( 'table' == $pod['storage'] ) {
				if ( 'taxonomy' == $pod['type'] ) {
					$pod['storage'] = 'none';
				} else {
					$pod['storage'] = 'meta';
				}
			}
		}

		$pod->override( $options );

		/**
		 * @var WP_Query
		 */
		global $wp_query;

		$reserved_query_vars = array_keys( $wp_query->fill_query_vars( array() ) );

		if ( ! empty( $pod['query_var_string'] ) ) {
			if ( in_array( $pod['query_var_string'], $reserved_query_vars ) ) {
				$pod['query_var_string'] = $pod['type'] . '_' . $pod['query_var_string'];
			}
		} elseif ( ! empty( $pod['query_var'] ) ) {
			if ( in_array( $pod['query_var'], $reserved_query_vars ) ) {
				$pod['query_var'] = $pod['type'] . '_' . $pod['query_var'];
			}
		}

		if ( strlen( $pod['label'] ) < 1 ) {
			$pod['label'] = $pod['name'];
		}

		if ( 'post_type' == $pod['type'] ) {
			// Max length for post types are 20 characters
			$pod['name'] = substr( $pod['name'], 0, 20 );
		} elseif ( 'taxonomy' == $pod['type'] ) {
			// Max length for taxonomies are 32 characters
			$pod['name'] = substr( $pod['name'], 0, 32 );
		}

		$params->id   = $pod['id'];
		$params->name = $pod['name'];

		if ( null !== $old_name && $old_name != $pod['name'] && empty( $pod['object'] ) ) {
			if ( 'post_type' == $pod['type'] ) {
				$check = get_post_type_object( $pod['name'] );

				if ( ! empty( $check ) ) {
					return pods_error( sprintf( __( 'Post Type %s already exists, you cannot rename %s to that', 'pods' ), $pod['name'], $old_name ) );
				}
			} elseif ( 'taxonomy' == $pod['type'] ) {
				$check = get_taxonomy( $pod['name'] );

				if ( ! empty( $check ) ) {
					return pods_error( sprintf( __( 'Taxonomy %s already exists, you cannot rename %s to that', 'pods' ), $pod['name'], $old_name ) );
				}
			}
		}

		$field_table_operation = true;

		// Add new pod
		if ( empty( $pod['id'] ) ) {
			if ( strlen( $pod['name'] ) < 1 ) {
				return pods_error( __( 'Pod name cannot be empty', 'pods' ) );
			}

			$post_data = array(
				'post_name'    => $pod['name'],
				'post_title'   => $pod['label'],
				'post_content' => $pod['description'],
				'post_type'    => $this->_post_type,
				'post_status'  => 'publish'
			);

			if ( 'pod' == $pod['type'] && ( ! is_array( $pod_fields ) || empty( $pod_fields ) ) ) {
				$pod_fields = array();

				$pod_fields['name'] = array(
					'name'     => 'name',
					'label'    => 'Name',
					'type'     => 'text',
					'required' => '1'
				);

				$pod_fields['created'] = array(
					'name'                 => 'created',
					'label'                => 'Date Created',
					'type'                 => 'datetime',
					'datetime_format'      => 'ymd_slash',
					'datetime_time_type'   => '12',
					'datetime_time_format' => 'h_mm_ss_A'
				);

				$pod_fields['modified'] = array(
					'name'                 => 'modified',
					'label'                => 'Date Modified',
					'type'                 => 'datetime',
					'datetime_format'      => 'ymd_slash',
					'datetime_time_type'   => '12',
					'datetime_time_format' => 'h_mm_ss_A'
				);

				$pod_fields['author'] = array(
					'name'               => 'author',
					'label'              => 'Author',
					'type'               => 'pick',
					'pick_object'        => 'user',
					'pick_format_type'   => 'single',
					'pick_format_single' => 'autocomplete',
					'default_value'      => '{@user.ID}'
				);

				$pod_fields['permalink'] = array(
					'name'        => 'permalink',
					'label'       => 'Permalink',
					'type'        => 'slug',
					'description' => 'Leave blank to auto-generate from Name'
				);

				if ( ! isset( $pod['pod_index'] ) ) {
					$pod['pod_index'] = 'name';
				}
			}

			$override = $pod->export( 'data' );

			// @deprecated hook
			$override = apply_filters( 'pods_api_save_pod_default_pod', $override, $params, $pod );

			$override = apply_filters( 'pods_object_save_pod_default_pod', $override, $params, $pod );

			$pod->override( $override );

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

		if ( true === $params->db ) {
			if ( ! has_filter( 'wp_unique_post_slug', array( $api, 'save_slug_fix' ) ) ) {
				add_filter( 'wp_unique_post_slug', array( $api, 'save_slug_fix' ), 100, 6 );
			}

			$conflicted = false;

			// Headway compatibility fix
			if ( has_filter( 'wp_insert_post_data', 'headway_clean_slug', 0 ) ) {
				remove_filter( 'wp_insert_post_data', 'headway_clean_slug', 0 );

				$conflicted = true;
			}

			if ( ! empty( $params->id ) ) {
				$changed_meta = $pod->changed();
				$pod->override_save();
			} else {
				$changed_meta = $pod->export( 'data' );
			}

			$changed_meta = array_diff_key( $changed_meta, array( 'id' => '', 'name' => '', 'label' => '', 'description' => '' ) );

			$params->id = $api->save_wp_object( 'post', $post_data, $changed_meta );

			if ( $conflicted ) {
				add_filter( 'wp_insert_post_data', 'headway_clean_slug', 0 );
			}

			if ( false === $params->id ) {
				return pods_error( __( 'Cannot save Pod', 'pods' ) );
			}
		} elseif ( empty( $params->id ) ) {
			$params->id = (int) $params->db;
		}

		$pod['id'] = $params->id;

		// Setup / update tables
		if ( 'table' != $pod['type'] && 'table' == $pod['storage'] && $old_storage != $pod['storage'] && true === $params->db ) {
			$definitions = array( "`id` BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY" );

			$defined_fields = array();

			foreach ( $pod_fields as $field ) {
				if ( ( ! is_array( $field ) && ! is_object( $field ) ) || ! isset( $field['name'] ) || in_array( $field['name'], $defined_fields ) ) {
					continue;
				}

				$defined_fields[] = $field['name'];

				if ( ! in_array( $field['type'], $tableless_field_types ) || ( 'pick' == $field['type'] && in_array( pods_v( 'pick_object', $field ), $simple_tableless_objects ) ) ) {
					$definition = $api->get_field_definition( $field['type'], $field );

					if ( 0 < strlen( $definition ) ) {
						$definitions[] = "`{$field['name']}` " . $definition;
					}
				}
			}

			pods_query( "DROP TABLE IF EXISTS `@wp_pods_{$params->name}`" );

			$result = pods_query( "CREATE TABLE `@wp_pods_{$params->name}` (" . implode( ', ', $definitions ) . ") DEFAULT CHARSET utf8" );

			if ( empty( $result ) ) {
				return pods_error( __( 'Cannot add Database Table for Pod', 'pods' ) );
			}

		} elseif ( 'table' != $pod['type'] && 'table' == $pod['storage'] && $pod['storage'] == $old_storage && null !== $old_name && $old_name != $params->name && true === $params->db ) {
			$result = pods_query( "ALTER TABLE `@wp_pods_{$old_name}` RENAME `@wp_pods_{$params->name}`" );

			if ( empty( $result ) ) {
				return pods_error( __( 'Cannot update Database Table for Pod', 'pods' ), $this );
			}
		}

		/**
		 * @var $wpdb wpdb
		 */
		global $wpdb;

		if ( null !== $old_name && $old_name != $params->name && true === $params->db ) {
			// Rename items in the DB pointed at the old WP Object names
			if ( 'post_type' == $pod['type'] && empty( $pod['object'] ) ) {
				$api->rename_wp_object_type( 'post', $old_name, $params->name );
			} elseif ( 'taxonomy' == $pod['type'] && empty( $pod['object'] ) ) {
				$api->rename_wp_object_type( 'taxonomy', $old_name, $params->name );
			} elseif ( 'comment' == $pod['type'] && empty( $pod['object'] ) ) {
				$api->rename_wp_object_type( 'comment', $old_name, $params->name );
			} elseif ( 'settings' == $pod['type'] ) {
				$api->rename_wp_object_type( 'settings', $old_name, $params->name );
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

		if ( in_array( $pod['type'], array( 'post_type', 'taxonomy' ) ) && empty( $pod['object'] ) && true === $params->db ) {
			// Build list of 'built_in' for later
			$built_in = array();

			foreach ( $pod as $key => $val ) {
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

			$lookup_option = $lookup_built_in = false;

			$lookup_name = $pod['name'];

			if ( 'post_type' == $pod['type'] ) {
				$lookup_option   = 'built_in_post_types_' . $lookup_name;
				$lookup_built_in = 'taxonomy';
			} elseif ( 'taxonomy' == $pod['type'] ) {
				$lookup_option   = 'built_in_taxonomies_' . $lookup_name;
				$lookup_built_in = 'post_type';
			}

			if ( ! empty( $lookup_option ) && ! empty( $lookup_built_in ) && isset( $built_in[ $lookup_built_in ] ) ) {
				foreach ( $built_in[ $lookup_built_in ] as $built_in_object => $val ) {
					$search_val = 1;

					if ( 1 == $val ) {
						$search_val = 0;
					}

					$query = "
						SELECT p.ID FROM {$wpdb->posts} AS p
						LEFT JOIN {$wpdb->postmeta} AS pm ON pm.post_id = p.ID AND pm.meta_key = '{$lookup_option}'
						LEFT JOIN {$wpdb->postmeta} AS pm2 ON pm2.post_id = p.ID AND pm2.meta_key = 'type' AND pm2.meta_value = '{$lookup_built_in}'
						LEFT JOIN {$wpdb->postmeta} AS pm3 ON pm3.post_id = p.ID AND pm3.meta_key = 'object' AND pm3.meta_value = ''
						WHERE p.post_type = '_pods_pod' AND p.post_name = '{$built_in_object}'
							AND pm2.meta_id IS NOT NULL
							AND ( pm.meta_id IS NULL OR pm.meta_value = {$search_val} )
					";

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

		$field_index = pods_v( 'pod_index', $pod, 'id', true );

		if ( 'pod' == $pod['type'] && ! empty( $pod_fields ) && isset( $pod_fields[ $field_index ] ) ) {
			$field_index_id = $pod_fields[ $field_index ];
		}

		if ( isset( $params->fields ) || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			$fields = array();

			if ( isset( $params->fields ) ) {
				$params->fields = (array) $params->fields;

				$weight = 0;

				foreach ( $params->fields as $field ) {
					if ( ( ! is_array( $field ) && ! is_object( $field ) ) || ! isset( $field['name'] ) ) {
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

			foreach ( $pod_fields as $k => $field ) {
				if ( ! empty( $old_id ) && ( ( ! is_array( $field ) && ! is_object( $field ) ) || ! isset( $field['name'] ) || ! isset( $fields[ $field['name'] ] ) ) ) {
					// Iterative change handling for setup-edit.php
					if ( ( ! is_array( $field ) && ! is_object( $field ) ) && isset( $old_fields[ $k ] ) ) {
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

				if ( 0 < $field_index_id && pods_v( 'id', $field ) == $field_index_id ) {
					$field_index_change = $field['name'];
				}

				if ( 0 < pods_v( 'id', $field ) ) {
					$id_required = true;
				}

				if ( $id_required ) {
					$field['id_required'] = true;
				}

				$field_data = $field;

				$field = $api->save_field( $field_data, $field_table_operation, true, $params->db );

				if ( true === $params->db ) {
					if ( ! empty( $field ) && 0 < $field ) {
						$saved[ $field_data['name'] ] = true;
						$saved_field_ids[]            = $field;
					} else {
						$errors[] = sprintf( __( 'Cannot save the %s field', 'pods' ), $field_data['name'] );
					}
				} else {
					$pod_fields[ $k ]  = $field;
					$saved_field_ids[] = $field['id'];
				}
			}

			if ( true === $params->db ) {
				foreach ( $old_fields as $field ) {
					if ( isset( $pod_fields[ $field['name'] ] ) || isset( $saved[ $field['name'] ] ) || in_array( $field['id'], $saved_field_ids ) ) {
						continue;
					}

					if ( $field['id'] == $field_index_id ) {
						$field_index_change = 'id';
					} elseif ( $field['name'] == $field_index ) {
						$field_index_change = 'id';
					}

					$delete_field = array(
						'id'   => (int) $field['id'],
						'name' => $field['name'],
						'pod'  => $pod
					);

					$api->delete_field( $delete_field, $field_table_operation );
				}
			}

			// Update field index if the name has changed or the field has been removed
			if ( false !== $field_index_change ) {
				if ( true === $params->db ) {
					update_post_meta( $pod['id'], 'pod_index', $field_index_change );
				}

				$this->offsetSet( 'pod_index', $field_index_change );
			}
		}

		if ( ! empty( $errors ) ) {
			return pods_error( $errors );
		}

		// Refresh fields
		$this->_fields = array();
		$this->fields();

		$api->cache_flush_pods( $pod );

		// Register Post Types / Taxonomies / Comment Types post-registration from Pods_Init
		if ( did_action( 'pods_setup_content_types' ) && in_array( $pod['type'], array( 'post_type', 'taxonomy', 'comment' ) ) && empty( $pod['object'] ) ) {
			global $pods_init;

			$pods_init->setup_content_types( true );
		}

		$id = $pod['id'];

		// Refresh object
		if ( $refresh ) {
			$id = $this->load( null, $id );
		}

		if ( 0 < $id ) {
			$this->_action( 'pods_object_save_' . $this->_action_type, $id, $this, $params );
		}

		return $id;

	}

	/**
	 * {@inheritDocs}
	 */
	public function duplicate( $options = null, $value = null, $replace = false ) {

		if ( ! $this->is_valid() ) {
			return false;
		}

		if ( is_object( $options ) ) {
			$options = get_object_vars( $options );
		} elseif ( null !== $value && ! is_array( $options ) ) {
			$options = array(
				$options => $value
			);
		} elseif ( empty( $options ) || ! is_array( $options ) ) {
			$options = array();
		}

		// Must duplicate from the original Pod object
		if ( isset( $options['id'] ) && 0 < $options['id'] ) {
			return false;
		}

		$built_in = array(
			'id'       => '',
			'name'     => '',
			'new_name' => ''
		);

		$custom_options = array_diff( $options, $built_in );

		$params = (object) $options;

		if ( ! isset( $params->strict ) ) {
			$params->strict = pods_strict();
		}

		$params->name = $this->_object['name'];

		$params = apply_filters( 'pods_object_pre_duplicate_' . $this->_action_type, $params, $this );

		$pod = $this->export();

		if ( in_array( $pod['type'], array( 'media', 'user', 'comment' ) ) ) {
			if ( false !== $params->strict ) {
				return pods_error( __( 'Pod not allowed to be duplicated', 'pods' ) );
			}

			return false;
		}

		$pod['object'] = '';

		$pod = array_merge( $pod, $custom_options );

		unset( $pod['id'] );
		unset( $pod['object_fields'] );

		if ( isset( $params->new_name ) ) {
			$pod['name'] = $params->new_name;
		}

		$try = 2;

		$check_name = $pod['name'] . $try;
		$new_label  = $pod['label'] . $try;

		while ( $this->exists( $check_name ) ) {
			$try ++;

			$check_name = $pod['name'] . $try;
			$new_label  = $pod['label'] . $try;
		}

		$pod['name']  = $check_name;
		$pod['label'] = $new_label;

		foreach ( $pod['fields'] as $field => $field_data ) {
			unset( $pod['fields'][ $field ]['id'] );
		}

		$new_pod = pods_object_pod();

		$id = $new_pod->save( $pod );

		if ( 0 < $id ) {
			$this->_action( 'pods_object_duplicate_' . $this->_action_type, $id, $this, $new_pod, $params );

			if ( $replace ) {
				// Replace object
				$id = $this->load( null, $id );
			}
		}

		return $id;

	}

	/**
	 * {@inheritDocs}
	 */
	public function delete( $delete_all = false ) {

		global $wpdb;

		if ( ! $this->is_valid() ) {
			return false;
		}

		$params = (object) array(
			'id'   => $this->_object['id'],
			'name' => $this->_object['name']
		);

		$params = apply_filters( 'pods_object_pre_delete_' . $this->_action_type, $params, $this, $delete_all );

		$success = false;

		if ( 0 < $params->id ) {
			$fields = $this->fields();

			/**
			 * @var $field Pods_Object_Field
			 */
			foreach ( $fields as $field ) {
				$field->delete();
			}

			// Only delete the post once the fields are taken care of, it's not required anymore
			$success = wp_delete_post( $params->id );

			if ( ! $success ) {
				return pods_error( __( 'Pod unable to be deleted', 'pods' ) );
			}

			// Reset content
			if ( $delete_all ) {
				$this->reset();
			}

			if ( ! pods_tableless() ) {
				if ( 'table' == $this->_object['storage'] ) {
					try {
						pods_query( "DROP TABLE IF EXISTS `@wp_pods_{$params->name}`", false );
					} catch ( Exception $e ) {
						// Allow pod to be deleted if the table doesn't exist
						if ( false === strpos( $e->getMessage(), 'Unknown table' ) ) {
							return pods_error( $e->getMessage() );
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

			pods_api()->cache_flush_pods( $this );

			$success = true;
		}

		if ( $success ) {
			$this->_action( 'pods_object_delete_' . $this->_action_type, $params, $this, $delete_all );

			// Can't destroy object, so let's destroy the data and invalidate the object
			$this->destroy();
		}

		return $success;

	}

	/**
	 * {@inheritDocs}
	 */
	public function reset() {

		if ( ! $this->is_valid() ) {
			return false;
		}

		$params = (object) array(
			'id'   => $this->_object['id'],
			'name' => $this->_object['name']
		);

		$params = apply_filters( 'pods_object_pre_reset_' . $this->_action_type, $params, $this );

		$table_info = $this->table_info();

		if ( ! pods_tableless() ) {
			if ( 'table' == $this->_object['storage'] ) {
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
		if ( in_array( $this->_object['type'], array( 'post_type', 'media' ) ) ) {
			$type = $this->_object['object'];

			if ( empty( $type ) ) {
				$type = $this->_object['name'];
			}

			$sql = "
                DELETE `t`, `r`, `m`
                FROM `{$table_info['table']}` AS `t`
                LEFT JOIN `{$table_info['meta_table']}` AS `m`
                    ON `m`.`{$table_info['meta_field_id']}` = `t`.`{$table_info['field_id']}`
                LEFT JOIN `{$table_info['table']}` AS `r`
                    ON `r`.`post_parent` = `t`.`{$table_info['field_id']}` AND `r`.`post_status` = 'inherit'
                WHERE `t`.`{$table_info['field_type']}` = '{$type}'
            ";

			pods_query( $sql, false );
		} // Delete all terms from this taxonomy
		elseif ( 'taxonomy' == $this->_object['type'] ) {
			$sql = "
                DELETE FROM `{$table_info['table']}` AS `t`
                " . $table_info['join']['tt'] . "
                WHERE " . implode( ' AND ', $table_info['where'] ) . "
            ";

			pods_query( $sql, false );
		} // Delete all users except the current one
		elseif ( 'user' == $this->_object['type'] ) {
			$sql = "
                DELETE `t`, `m`
                FROM `{$table_info['table']}` AS `t`
                LEFT JOIN `{$table_info['meta_table']}` AS `m`
                    ON `m`.`{$table_info['meta_field_id']}` = `t`.`{$table_info['field_id']}`
                WHERE `t`.`{$table_info['field_id']}` != " . (int) get_current_user_id() . "
            ";

			pods_query( $sql, false );
		} // Delete all comments
		elseif ( 'comment' == $this->_object['type'] ) {
			$type = $this->_object['object'];

			if ( empty( $type ) ) {
				$type = $this->_object['name'];
			}

			$where = array(
				"`t`.`{$table_info['field_type']}` = '{$type}'"
			);

			if ( 'comment' == $type ) {
				$where[] = "`t`.`{$table_info['field_type']}` = ''";
			}

			$sql = "
                DELETE `t`, `m`
                FROM `{$table_info['table']}` AS `t`
                LEFT JOIN `{$table_info['meta_table']}` AS `m`
                    ON `m`.`{$table_info['meta_field_id']}` = `t`.`{$table_info['field_id']}`
                WHERE " . implode( ' AND ', $where ) . "
            ";

			pods_query( $sql, false );
		}

		pods_cache_clear( true ); // only way to reliably clear out cached data across an entire group

		$this->_action( 'pods_object_reset_' . $this->_action_type, $params, $this );

		return true;

	}
}