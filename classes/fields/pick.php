<?php

/**
 * @package Pods\Fields
 */
class PodsField_Pick extends PodsField {

	/**
	 * {@inheritdoc}
	 */
	public static $group = 'Relationships / Media';

	/**
	 * {@inheritdoc}
	 */
	public static $type = 'pick';

	/**
	 * {@inheritdoc}
	 */
	public static $label = 'Relationship';

	/**
	 * {@inheritdoc}
	 */
	protected static $api = false;

	/**
	 * Available Related Objects.
	 *
	 * @var array
	 * @since 2.3.0
	 */
	public static $related_objects = array();

	/**
	 * Custom Related Objects
	 *
	 * @var array
	 * @since 2.3.0
	 */
	public static $custom_related_objects = array();

	/**
	 * Data used during validate / save to avoid extra queries.
	 *
	 * @var array
	 * @since 2.3.0
	 */
	public static $related_data = array();

	/**
	 * Data used during input method (mainly for autocomplete).
	 *
	 * @var array
	 * @since 2.3.0
	 */
	public static $field_data = array();

	/**
	 * Saved array of simple relationship names.
	 *
	 * @var array
	 * @since 2.5.0
	 */
	private static $names_simple = null;

	/**
	 * Saved array of relationship names
	 *
	 * @var array
	 * @since 2.5.0
	 */
	private static $names_related = null;

	/**
	 * Saved array of bidirectional relationship names
	 *
	 * @var array
	 * @since 2.5.0
	 */
	private static $names_bidirectional = null;

	/**
	 * {@inheritdoc}
	 */
	public function setup() {

		self::$label = __( 'Relationship', 'pods' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function admin_init() {

		// AJAX for Relationship lookups.
		add_action( 'wp_ajax_pods_relationship', array( $this, 'admin_ajax_relationship' ) );
		add_action( 'wp_ajax_nopriv_pods_relationship', array( $this, 'admin_ajax_relationship' ) );

		// Handle modal input.
		add_action( 'edit_form_top', array( $this, 'admin_modal_input' ) );
		add_action( 'show_user_profile', array( $this, 'admin_modal_input' ) );
		add_action( 'edit_user_profile', array( $this, 'admin_modal_input' ) );
		add_action( 'edit_category_form', array( $this, 'admin_modal_input' ) );
		add_action( 'edit_link_category_form', array( $this, 'admin_modal_input' ) );
		add_action( 'edit_tag_form', array( $this, 'admin_modal_input' ) );
		add_action( 'add_tag_form', array( $this, 'admin_modal_input' ) );
		add_action( 'pods_meta_box_pre', array( $this, 'admin_modal_input' ) );

		// Handle modal saving.
		add_filter( 'redirect_post_location', array( $this, 'admin_modal_bail_post_redirect' ), 10, 2 );
		add_action( 'load-edit-tags.php', array( $this, 'admin_modal_bail_term_action' ) );
		add_action( 'load-categories.php', array( $this, 'admin_modal_bail_term_action' ) );
		add_action( 'load-edit-link-categories.php', array( $this, 'admin_modal_bail_term_action' ) );
		add_action( 'personal_options_update', array( $this, 'admin_modal_bail_user_action' ) );
		add_action( 'user_register', array( $this, 'admin_modal_bail_user_action' ) );
		add_action( 'pods_api_processed_form', array( $this, 'admin_modal_bail_pod' ), 10, 3 );

	}

	/**
	 * {@inheritdoc}
	 */
	public function options() {

		$options = array(
			static::$type . '_format_type'    => array(
				'label'      => __( 'Selection Type', 'pods' ),
				'help'       => __( 'help', 'pods' ),
				'default'    => 'single',
				'type'       => 'pick',
				'data'       => array(
					'single' => __( 'Single Select', 'pods' ),
					'multi'  => __( 'Multiple Select', 'pods' ),
				),
				'dependency' => true,
			),
			static::$type . '_format_single'  => array(
				'label'      => __( 'Format', 'pods' ),
				'help'       => __( 'help', 'pods' ),
				'depends-on' => array( static::$type . '_format_type' => 'single' ),
				'default'    => 'dropdown',
				'type'       => 'pick',
				'data'       => apply_filters(
					'pods_form_ui_field_pick_format_single_options', array(
						'dropdown'     => __( 'Drop Down', 'pods' ),
						'radio'        => __( 'Radio Buttons', 'pods' ),
						'autocomplete' => __( 'Autocomplete', 'pods' ),
						'list'         => __( 'List view', 'pods' ),
					)
				),
				'dependency' => true,
			),
			static::$type . '_format_multi'   => array(
				'label'      => __( 'Format', 'pods' ),
				'help'       => __( 'help', 'pods' ),
				'depends-on' => array( static::$type . '_format_type' => 'multi' ),
				'default'    => 'checkbox',
				'type'       => 'pick',
				'data'       => apply_filters(
					'pods_form_ui_field_pick_format_multi_options', array(
						'checkbox'     => __( 'Checkboxes', 'pods' ),
						'multiselect'  => __( 'Multi Select', 'pods' ),
						'autocomplete' => __( 'Autocomplete', 'pods' ),
						'list'         => __( 'List view', 'pods' ),
					)
				),
				'dependency' => true,
			),
			static::$type . '_allow_add_new'  => array(
				'label'       => __( 'Allow Add New', 'pods' ),
				'help'        => __( 'Allow new related records to be created in a modal window', 'pods' ),
				'wildcard-on' => array(
					static::$type . '_object' => array( '^post-type-(?!(custom-css|customize-changeset)).*$', '^taxonomy-.*$', '^user$', '^pod-.*$' ),
				),
				'type'        => 'boolean',
				'default'     => 1,
			),
			static::$type . '_taggable'       => array(
				'label'       => __( 'Taggable', 'pods' ),
				'help'        => __( 'Allow new values to be inserted when using an Autocomplete field', 'pods' ),
				'excludes-on' => array(
					static::$type . '_format_single' => array( 'dropdown', 'radio', 'list' ),
					static::$type . '_format_multi'  => array( 'checkbox', 'multiselect', 'list' ),
					static::$type . '_object'        => array_merge( array( 'site', 'network' ), self::simple_objects() ),
				),
				'type'        => 'boolean',
				'default'     => 0,
			),
			static::$type . '_show_icon'      => array(
				'label'       => __( 'Show Icons', 'pods' ),
				'excludes-on' => array(
					static::$type . '_format_single' => array( 'dropdown', 'radio', 'autocomplete' ),
					static::$type . '_format_multi'  => array( 'checkbox', 'multiselect', 'autocomplete' ),
					static::$type . '_object'        => array_merge( array( 'site', 'network' ), self::simple_objects() ),
				),
				'type'        => 'boolean',
				'default'     => 1,
			),
			static::$type . '_show_edit_link' => array(
				'label'       => __( 'Show Edit Links', 'pods' ),
				'excludes-on' => array(
					static::$type . '_format_single' => array( 'dropdown', 'radio', 'autocomplete' ),
					static::$type . '_format_multi'  => array( 'checkbox', 'multiselect', 'autocomplete' ),
					static::$type . '_object'        => array_merge( array( 'site', 'network' ), self::simple_objects() ),
				),
				'type'        => 'boolean',
				'default'     => 1,
			),
			static::$type . '_show_view_link' => array(
				'label'       => __( 'Show View Links', 'pods' ),
				'excludes-on' => array(
					static::$type . '_format_single' => array( 'dropdown', 'radio', 'autocomplete' ),
					static::$type . '_format_multi'  => array( 'checkbox', 'multiselect', 'autocomplete' ),
					static::$type . '_object'        => array_merge( array( 'site', 'network' ), self::simple_objects() ),
				),
				'type'        => 'boolean',
				'default'     => 1,
			),
			static::$type . '_select_text'    => array(
				'label'      => __( 'Default Select Text', 'pods' ),
				'help'       => __( 'This is the text use for the default "no selection" dropdown item, if empty, it will default to "-- Select One --"', 'pods' ),
				'depends-on' => array(
					static::$type . '_format_type'   => 'single',
					static::$type . '_format_single' => 'dropdown',
				),
				'default'    => '',
				'type'       => 'text',
			),
			static::$type . '_limit'          => array(
				'label'      => __( 'Selection Limit', 'pods' ),
				'help'       => __( 'help', 'pods' ),
				'depends-on' => array( static::$type . '_format_type' => 'multi' ),
				'default'    => 0,
				'type'       => 'number',
			),
			static::$type . '_table_id'       => array(
				'label'      => __( 'Table ID Column', 'pods' ),
				'help'       => __( 'You must provide the ID column name for the table, this will be used to keep track of the relationship', 'pods' ),
				'depends-on' => array( static::$type . '_object' => 'table' ),
				'required'   => 1,
				'default'    => '',
				'type'       => 'text',
			),
			static::$type . '_table_index'    => array(
				'label'      => __( 'Table Index Column', 'pods' ),
				'help'       => __( 'You must provide the index column name for the table, this may optionally also be the ID column name', 'pods' ),
				'depends-on' => array( static::$type . '_object' => 'table' ),
				'required'   => 1,
				'default'    => '',
				'type'       => 'text',
			),
			static::$type . '_display'        => array(
				'label'       => __( 'Display Field in Selection List', 'pods' ),
				'help'        => __( 'Provide the name of a field on the related object to reference, example: {@post_title}', 'pods' ),
				'excludes-on' => array(
					static::$type . '_object' => array_merge( array( 'site', 'network' ), self::simple_objects() ),
				),
				'default'     => '',
				'type'        => 'text',
			),
			static::$type . '_user_role'      => array(
				'label'            => __( 'Limit list to Role(s)', 'pods' ),
				'help'             => __( 'help', 'pods' ),
				'depends-on'       => array( static::$type . '_object' => 'user' ),
				'default'          => '',
				'type'             => 'pick',
				'pick_object'      => 'role',
				'pick_format_type' => 'multi',
			),
			static::$type . '_where'          => array(
				'label'       => __( 'Customized <em>WHERE</em>', 'pods' ),
				'help'        => __( 'help', 'pods' ),
				'excludes-on' => array(
					static::$type . '_object' => array_merge( array( 'site', 'network' ), self::simple_objects() ),
				),
				'default'     => '',
				'type'        => 'text',
			),
			static::$type . '_orderby'        => array(
				'label'       => __( 'Customized <em>ORDER BY</em>', 'pods' ),
				'help'        => __( 'help', 'pods' ),
				'excludes-on' => array(
					static::$type . '_object' => array_merge( array( 'site', 'network' ), self::simple_objects() ),
				),
				'default'     => '',
				'type'        => 'text',
			),
			static::$type . '_groupby'        => array(
				'label'       => __( 'Customized <em>GROUP BY</em>', 'pods' ),
				'help'        => __( 'help', 'pods' ),
				'excludes-on' => array(
					static::$type . '_object' => array_merge( array( 'site', 'network' ), self::simple_objects() ),
				),
				'default'     => '',
				'type'        => 'text',
			),
		);

		$post_type_pick_objects = array();

		foreach ( get_post_types( '', 'names' ) as $post_type ) {
			$post_type_pick_objects[] = 'post-type_' . $post_type;
		}

		$options[ static::$type . '_post_status' ] = array(
			'name'             => 'post_status',
			'label'            => __( 'Post Status', 'pods' ),
			'help'             => __( 'help', 'pods' ),
			'type'             => 'pick',
			'pick_object'      => 'post-status',
			'pick_format_type' => 'multi',
			'default'          => 'publish',
			'depends-on'       => array(
				static::$type . '_object' => $post_type_pick_objects,
			),
		);

		return $options;

	}

	/**
	 * Register a related object.
	 *
	 * @param string $name    Object name.
	 * @param string $label   Object label.
	 * @param array  $options Object options.
	 *
	 * @return array|boolean Object array or false if unsuccessful
	 * @since 2.3.0
	 */
	public function register_related_object( $name, $label, $options = null ) {

		if ( empty( $name ) || empty( $label ) ) {
			return false;
		}

		$related_object = array(
			'label'         => $label,
			'group'         => 'Custom Relationships',
			'simple'        => true,
			'bidirectional' => false,
			'data'          => array(),
			'data_callback' => null,
		);

		$related_object = array_merge( $related_object, $options );

		self::$custom_related_objects[ $name ] = $related_object;

		return true;

	}

	/**
	 * Setup related objects.
	 *
	 * @param boolean $force Whether to force refresh of related objects.
	 *
	 * @return bool True when data has been loaded
	 * @since 2.3.0
	 */
	public function setup_related_objects( $force = false ) {

		$new_data_loaded = false;

		if ( ! $force && empty( self::$related_objects ) ) {
			// Only load transient if we aren't forcing a refresh.
			self::$related_objects = pods_transient_get( 'pods_related_objects' );

			if ( false !== self::$related_objects ) {
				$new_data_loaded = true;
			}
		} elseif ( $force ) {
			// If we are rebuilding, make sure we start with a clean slate.
			self::$related_objects = array();
		}

		if ( empty( self::$related_objects ) ) {
			// Do a complete build of related_objects.
			$new_data_loaded = true;

			// Custom simple relationship lists.
			self::$related_objects['custom-simple'] = array(
				'label'  => __( 'Simple (custom defined list)', 'pods' ),
				'group'  => __( 'Custom', 'pods' ),
				'simple' => true,
			);

			// Pods options.
			$pod_options = array();

			// Include PodsMeta if not already included.
			pods_meta();

			// Advanced Content Types for relationships.
			$_pods = PodsMeta::$advanced_content_types;

			foreach ( $_pods as $pod ) {
				$pod_options[ $pod['name'] ] = $pod['label'] . ' (' . $pod['name'] . ')';
			}

			// Settings pods for relationships.
			$_pods = PodsMeta::$settings;

			foreach ( $_pods as $pod ) {
				$pod_options[ $pod['name'] ] = $pod['label'] . ' (' . $pod['name'] . ')';
			}

			asort( $pod_options );

			foreach ( $pod_options as $pod => $label ) {
				self::$related_objects[ 'pod-' . $pod ] = array(
					'label'         => $label,
					'group'         => __( 'Pods', 'pods' ),
					'bidirectional' => true,
				);
			}

			// Post Types for relationships.
			$post_types = get_post_types();
			asort( $post_types );

			$ignore = array( 'attachment', 'revision', 'nav_menu_item' );

			foreach ( $post_types as $post_type => $label ) {
				if ( in_array( $post_type, $ignore, true ) || empty( $post_type ) ) {
					unset( $post_types[ $post_type ] );

					continue;
				} elseif ( 0 === strpos( $post_type, '_pods_' ) && apply_filters( 'pods_pick_ignore_internal', true ) ) {
					unset( $post_types[ $post_type ] );

					continue;
				}

				$post_type = get_post_type_object( $post_type );

				self::$related_objects[ 'post_type-' . $post_type->name ] = array(
					'label'         => $post_type->label . ' (' . $post_type->name . ')',
					'group'         => __( 'Post Types', 'pods' ),
					'bidirectional' => true,
				);
			}

			// Taxonomies for relationships.
			$taxonomies = get_taxonomies();
			asort( $taxonomies );

			$ignore = array( 'nav_menu', 'post_format' );

			foreach ( $taxonomies as $taxonomy => $label ) {
				/**
				 * Prevent ability to extend core Pods content types.
				 *
				 * @param bool $ignore_internal Default is true, when set to false Pods internal content types can not be extended.
				 *
				 * @since 2.3.19
				 */
				$ignore_internal = apply_filters( 'pods_pick_ignore_internal', true );

				if ( in_array( $taxonomy, $ignore, true ) || empty( $taxonomy ) ) {
					unset( $taxonomies[ $taxonomy ] );

					continue;
				} elseif ( 0 === strpos( $taxonomy, '_pods_' ) && $ignore_internal ) {
					unset( $taxonomies[ $taxonomy ] );

					continue;
				}

				$taxonomy = get_taxonomy( $taxonomy );

				self::$related_objects[ 'taxonomy-' . $taxonomy->name ] = array(
					'label'         => $taxonomy->label . ' (' . $taxonomy->name . ')',
					'group'         => __( 'Taxonomies', 'pods' ),
					'bidirectional' => true,
				);
			}//end foreach

			// Other WP Objects for relationships.
			self::$related_objects['user'] = array(
				'label'         => __( 'Users', 'pods' ),
				'group'         => __( 'Other WP Objects', 'pods' ),
				'bidirectional' => true,
			);

			self::$related_objects['role'] = array(
				'label'         => __( 'User Roles', 'pods' ),
				'group'         => __( 'Other WP Objects', 'pods' ),
				'simple'        => true,
				'data_callback' => array( $this, 'data_roles' ),
			);

			self::$related_objects['capability'] = array(
				'label'         => __( 'User Capabilities', 'pods' ),
				'group'         => __( 'Other WP Objects', 'pods' ),
				'simple'        => true,
				'data_callback' => array( $this, 'data_capabilities' ),
			);

			self::$related_objects['media'] = array(
				'label'         => __( 'Media', 'pods' ),
				'group'         => __( 'Other WP Objects', 'pods' ),
				'bidirectional' => true,
			);

			self::$related_objects['comment'] = array(
				'label'         => __( 'Comments', 'pods' ),
				'group'         => __( 'Other WP Objects', 'pods' ),
				'bidirectional' => true,
			);

			self::$related_objects['image-size'] = array(
				'label'         => __( 'Image Sizes', 'pods' ),
				'group'         => __( 'Other WP Objects', 'pods' ),
				'simple'        => true,
				'data_callback' => array( $this, 'data_image_sizes' ),
			);

			self::$related_objects['nav_menu'] = array(
				'label' => __( 'Navigation Menus', 'pods' ),
				'group' => __( 'Other WP Objects', 'pods' ),
			);

			self::$related_objects['post_format'] = array(
				'label' => __( 'Post Formats', 'pods' ),
				'group' => __( 'Other WP Objects', 'pods' ),
			);

			self::$related_objects['post-status'] = array(
				'label'         => __( 'Post Status', 'pods' ),
				'group'         => __( 'Other WP Objects', 'pods' ),
				'simple'        => true,
				'data_callback' => array( $this, 'data_post_stati' ),
			);

			do_action( 'pods_form_ui_field_pick_related_objects_other' );

			self::$related_objects['country'] = array(
				'label'         => __( 'Countries', 'pods' ),
				'group'         => __( 'Predefined Lists', 'pods' ),
				'simple'        => true,
				'data_callback' => array( $this, 'data_countries' ),
			);

			self::$related_objects['us_state'] = array(
				'label'         => __( 'US States', 'pods' ),
				'group'         => __( 'Predefined Lists', 'pods' ),
				'simple'        => true,
				'data_callback' => array( $this, 'data_us_states' ),
			);

			self::$related_objects['ca_province'] = array(
				'label'         => __( 'CA Provinces', 'pods' ),
				'group'         => __( 'Predefined Lists', 'pods' ),
				'simple'        => true,
				'data_callback' => array( $this, 'data_ca_provinces' ),
			);

			self::$related_objects['days_of_week'] = array(
				'label'         => __( 'Calendar - Days of Week', 'pods' ),
				'group'         => __( 'Predefined Lists', 'pods' ),
				'simple'        => true,
				'data_callback' => array( $this, 'data_days_of_week' ),
			);

			self::$related_objects['months_of_year'] = array(
				'label'         => __( 'Calendar - Months of Year', 'pods' ),
				'group'         => __( 'Predefined Lists', 'pods' ),
				'simple'        => true,
				'data_callback' => array( $this, 'data_months_of_year' ),
			);

			do_action( 'pods_form_ui_field_pick_related_objects_predefined' );

			if ( did_action( 'init' ) ) {
				pods_transient_set( 'pods_related_objects', self::$related_objects );
			}
		}//end if

		/**
		 * Allow custom related objects to be defined
		 */
		do_action( 'pods_form_ui_field_pick_related_objects_custom' );

		foreach ( self::$custom_related_objects as $object => $related_object ) {
			if ( ! isset( self::$related_objects[ $object ] ) ) {
				$new_data_loaded = true;

				self::$related_objects[ $object ] = $related_object;
			}
		}

		return $new_data_loaded;

	}

	/**
	 * Return available related objects
	 *
	 * @param boolean $force Whether to force refresh of related objects.
	 *
	 * @return array Field selection array
	 * @since 2.3.0
	 */
	public function related_objects( $force = false ) {

		if ( $this->setup_related_objects( $force ) || null === self::$names_related ) {
			$related_objects = array();

			foreach ( self::$related_objects as $related_object_name => $related_object ) {
				if ( ! isset( $related_objects[ $related_object['group'] ] ) ) {
					$related_objects[ $related_object['group'] ] = array();
				}

				$related_objects[ $related_object['group'] ][ $related_object_name ] = $related_object['label'];
			}

			self::$names_related = (array) apply_filters( 'pods_form_ui_field_pick_related_objects', $related_objects );
		}

		return self::$names_related;

	}

	/**
	 * Return available simple object names
	 *
	 * @return array Simple object names
	 * @since 2.3.0
	 */
	public function simple_objects() {

		if ( $this->setup_related_objects() || null === self::$names_simple ) {
			$simple_objects = array();

			foreach ( self::$related_objects as $object => $related_object ) {
				if ( ! isset( $related_object['simple'] ) || ! $related_object['simple'] ) {
					continue;
				}

				$simple_objects[] = $object;
			}

			self::$names_simple = (array) apply_filters( 'pods_form_ui_field_pick_simple_objects', $simple_objects );
		}

		return self::$names_simple;

	}

	/**
	 * Return available bidirectional object names
	 *
	 * @return array Bidirectional object names
	 * @since 2.3.4
	 */
	public function bidirectional_objects() {

		if ( $this->setup_related_objects() || null === self::$names_bidirectional ) {
			$bidirectional_objects = array();

			foreach ( self::$related_objects as $object => $related_object ) {
				if ( ! isset( $related_object['bidirectional'] ) || ! $related_object['bidirectional'] ) {
					continue;
				}

				$bidirectional_objects[] = $object;
			}

			self::$names_bidirectional = (array) apply_filters( 'pods_form_ui_field_pick_bidirectional_objects', $bidirectional_objects );
		}

		return self::$names_bidirectional;

	}

	/**
	 * {@inheritdoc}
	 */
	public function schema( $options = null ) {

		$schema = false;

		$simple_tableless_objects = $this->simple_objects();

		if ( in_array( pods_v( static::$type . '_object', $options ), $simple_tableless_objects, true ) ) {
			$schema = 'LONGTEXT';
		}

		return $schema;

	}

	/**
	 * {@inheritdoc}
	 */
	public function display( $value = null, $name = null, $options = null, $pod = null, $id = null ) {

		$fields = null;

		if ( is_object( $pod ) && isset( $pod->fields ) ) {
			/**
			 * @var $pod Pods Pods object.
			 */
			$fields = $pod->fields;

			if ( ! empty( $pod->pod_data['object_fields'] ) ) {
				$fields = array_merge( $fields, $pod->pod_data['object_fields'] );
			}
		} elseif ( is_array( $pod ) && isset( $pod['fields'] ) ) {
			$fields = $pod['fields'];

			if ( ! empty( $pod['object_fields'] ) ) {
				$fields = array_merge( $fields, $pod['object_fields'] );
			}
		}

		return pods_serial_comma(
			$value, array(
				'field'  => $name,
				'fields' => $fields,
			)
		);

	}

	/**
	 * {@inheritdoc}
	 */
	public function input( $name, $value = null, $options = null, $pod = null, $id = null ) {

		$options = (array) $options;

		$type = pods_v( 'type', $options, static::$type );

		$args = compact( array_keys( get_defined_vars() ) );
		$args = (object) $args;

		wp_enqueue_script( 'pods-dfv' );

		wp_enqueue_style( 'pods-select2' );
		wp_enqueue_script( 'pods-select2' );

		$this->render_input_script( $args );

		/**
		 * @todo Support custom integrations.
		 *
		 * Run the action 'pods_form_ui_field_pick_input_' . pods_v( static::$type . '_format_type', $options, 'single' ) . '_' . pods_v( static::$type . '_format_multi', $options, 'checkbox' )
		 * Run the action 'pods_form_ui_field_pick_input'
		 * Pass the arguments: $name, $value, $options, $pod, $id
		 */

	}

	/**
	 * {@inheritdoc}
	 */
	public function build_dfv_field_options( $options, $args ) {

		$options['grouped'] = 1;

		if ( empty( $options[ $args->type . '_object' ] ) ) {
			$options[ $args->type . '_object' ] = '';
		}

		if ( empty( $options[ $args->type . '_val' ] ) ) {
			$options[ $args->type . '_val' ] = '';
		}

		$options['table_info'] = array();

		$custom = pods_v( $args->type . '_custom', $options, false );

		$custom = apply_filters( 'pods_form_ui_field_pick_custom_values', $custom, $args->name, $args->value, $options, $args->pod, $args->id );

		$ajax = false;

		if ( $this->can_ajax( $args->type, $options ) ) {
			$ajax = true;

			if ( ! empty( self::$field_data ) && self::$field_data['id'] === $options['id'] ) {
				$ajax = (boolean) self::$field_data['autocomplete'];
			}
		}

		$ajax = apply_filters( 'pods_form_ui_field_pick_ajax', $ajax, $args->name, $args->value, $options, $args->pod, $args->id );

		if ( 0 === (int) pods_v( $args->type . '_ajax', $options, 1 ) ) {
			$ajax = false;
		}

		$options[ $args->type . '_ajax' ] = (int) $ajax;

		$format_type = pods_v( $args->type . '_format_type', $options, 'single', true );

		$limit = 1;

		if ( 'single' === $format_type ) {
			$format_single = pods_v( $args->type . '_format_single', $options, 'dropdown', true );

			if ( 'dropdown' === $format_single ) {
				$options['view_name'] = 'select';
			} elseif ( 'radio' === $format_single ) {
				$options['view_name'] = 'radio';
			} elseif ( 'autocomplete' === $format_single ) {
				$options['view_name'] = 'select2';
			} elseif ( 'list' === $format_single ) {
				$options['view_name'] = 'list';
			} else {
				$options['view_name'] = $format_single;
			}
		} elseif ( 'multi' === $format_type ) {
			$format_multi = pods_v( $args->type . '_format_multi', $options, 'checkbox', true );

			if ( ! empty( $args->value ) && ! is_array( $args->value ) ) {
				$args->value = explode( ',', $args->value );
			}

			if ( 'checkbox' === $format_multi ) {
				$options['view_name'] = 'checkbox';
			} elseif ( 'multiselect' === $format_multi ) {
				$options['view_name'] = 'select';
			} elseif ( 'autocomplete' === $format_multi ) {
				$options['view_name'] = 'select2';
			} elseif ( 'list' === $format_multi ) {
				$options['view_name'] = 'list';
			} else {
				$options['view_name'] = $format_multi;
			}

			$limit = 0;

			if ( ! empty( $options[ $args->type . '_limit' ] ) ) {
				$limit = absint( $options[ $args->type . '_limit' ] );
			}
		} else {
			$options['view_name'] = $format_type;
		}//end if

		$options[ $args->type . '_limit' ] = $limit;

		$options['ajax_data'] = $this->build_dfv_autocomplete_ajax_data( $options, $args, $ajax );

		/**
		 * Allow overriding some of the Select2 options used in the JS init.
		 *
		 * @param array|null $select2_overrides Override options for Select2/SelectWoo.
		 *
		 * @since 2.7.0
		 */
		$options['select2_overrides'] = apply_filters( 'pods_pick_select2_overrides', null );

		return $options;

	}

	/**
	 * Build DFV autocomplete AJAX data.
	 *
	 * @param array  $options DFV options.
	 * @param object $args    {
	 *  Field information arguments.
	 *
	 *     @type string     $name    Field name.
	 *     @type string     $type    Field type.
	 *     @type array      $options Field options.
	 *     @type mixed      $value   Current value.
	 *     @type array      $pod     Pod information.
	 *     @type int|string $id      Current item ID.
	 * }
	 * @param bool   $ajax    True if ajax mode should be used.
	 *
	 * @return array
	 */
	public function build_dfv_autocomplete_ajax_data( $options, $args, $ajax = false ) {

		if ( is_object( $args->pod ) ) {
			$pod_id = (int) $args->pod->pod_id;
		} else {
			$pod_id = 0;
		}

		$field_id = (int) $options['id'];

		$id = (int) $args->id;

		if ( is_user_logged_in() ) {
			$uid = 'user_' . get_current_user_id();
		} else {
			$uid = @session_id();
		}

		$uri_hash = wp_create_nonce( 'pods_uri_' . $_SERVER['REQUEST_URI'] );

		$field_nonce = wp_create_nonce( 'pods_relationship_' . $pod_id . '_' . $uid . '_' . $uri_hash . '_' . $field_id );

		// Values can be overridden via the `pods_field_dfv_data` filter in $data['fieldConfig']['ajax_data'].
		return array(
			'ajax'                 => $ajax,
			'delay'                => 300,
			'minimum_input_length' => 1,
			'pod'                  => $pod_id,
			'field'                => $field_id,
			'id'                   => $id,
			'uri'                  => $uri_hash,
			'_wpnonce'             => $field_nonce,
		);

	}

	/**
	 * {@inheritdoc}
	 */
	public function build_dfv_field_config( $args ) {

		$config = parent::build_dfv_field_config( $args );

		if ( ! isset( $config['optgroup'] ) ) {
			$config['optgroup'] = false;
		}

		/**
		 * Filter on whether to allow modals to be used on the front of the site (in an non-admin area).
		 *
		 * @param boolean $show_on_front
		 * @param array $config
		 * @param array $args
		 *
		 * @since 2.7.0
		 */
		$show_on_front = apply_filters( 'pods_ui_dfv_pick_modals_show_on_front', false, $config, $args );

		/**
		 * Filter on whether to allow nested modals to be used (modals within modals).
		 *
		 * @param boolean $allow_nested_modals
		 * @param array $config
		 * @param array $args
		 *
		 * @since 2.7.0
		 */
		$allow_nested_modals = apply_filters( 'pods_ui_dfv_pick_modals_allow_nested', false, $config, $args );

		// Disallow add/edit outside the admin and when we're already in a modal
		if ( ( ! $show_on_front && ! is_admin() ) || ( ! $allow_nested_modals && pods_is_modal_window() ) ) {
			$config[ $args->type . '_allow_add_new' ]  = false;
			$config[ $args->type . '_show_edit_link' ] = false;
		}

		$iframe = array(
			'src'        => '',
			'url'        => '',
			'query_args' => array(),
		);

		// Set the file name and args based on the content type of the relationship
		switch ( $args->options['pick_object'] ) {
			case 'post_type':
				if ( ! empty( $args->options['pick_val'] ) ) {
					$post_type_obj = get_post_type_object( $args->options['pick_val'] );

					if ( $post_type_obj && current_user_can( $post_type_obj->cap->create_posts ) ) {
						$iframe['url']        = admin_url( 'post-new.php' );
						$iframe['query_args'] = array(
							'post_type' => $args->options['pick_val'],
						);
					}
				}

				// Determine the default icon to use for this post type,
				// default to the dashicon for posts
				$config[ 'default_icon' ] = 'dashicons-admin-post';

				// Any custom specified menu icon gets priority
				$post_type = get_post_type_object( $args->options[ 'pick_val' ] );
				if ( ! empty( $post_type->menu_icon ) ) {
					$config[ 'default_icon' ] = $post_type->menu_icon;

				// Page and attachment have their own dashicons
				} elseif ( isset( $post_type->name ) ) {
					switch ( $post_type->name ) {
						case 'page':
							// Default for pages.
							$config[ 'default_icon' ] = 'dashicons-admin-page';
							break;
						case 'attachment':
							// Default for attachments.
							$config[ 'default_icon' ] = 'dashicons-admin-media';
							break;
					}
				}

				break;

			case 'taxonomy':
				/*
				 * @todo Fix add new modal issues
				if ( ! empty( $args->options['pick_val'] ) ) {
					$taxonomy_obj = get_taxonomy( $args->options['pick_val'] );

					if ( $taxonomy_obj && current_user_can( $taxonomy_obj->cap->edit_terms ) ) {
						$iframe['url']  = admin_url( 'edit-tags.php' );
						$iframe['query_args'] = array(
							'taxonomy' => $args->options['pick_val'],
						);
					}
				}
				*/

				break;

			case 'user':
				if ( current_user_can( 'create_users' ) ) {
					$iframe['url'] = admin_url( 'user-new.php' );
				}

				break;

			case 'pod':
				if ( ! empty( $args->options['pick_val'] ) ) {
					if ( pods_is_admin( array( 'pods', 'pods_content', 'pods_edit_' . $args->options['pick_val'] ) ) ) {
						$iframe['url']        = admin_url( 'admin.php' );
						$iframe['query_args'] = array(
							'page'   => 'pods-manage-' . $args->options['pick_val'],
							'action' => 'add',
						);

					}
				}

				break;
		}//end switch

		// Potential valid modal target if we've set the file name
		if ( ! empty( $iframe['url'] ) ) {
			// @todo: Replace string literal with defined constant
			$iframe['query_args']['pods_modal'] = 1;

			// Add args we always need
			$iframe['src'] = add_query_arg( $iframe['query_args'], $iframe['url'] );
		}

		$iframe['title_add']  = sprintf( __( '%s: Add New', 'pods' ), $args->options['label'] );
		$iframe['title_edit'] = sprintf( __( '%s: Edit', 'pods' ), $args->options['label'] );

		/**
		 * Allow filtering iframe configuration
		 *
		 * @param array $iframe
		 * @param array $config
		 * @param array $args
		 *
		 * @since 2.7.0
		 */
		$iframe = apply_filters( 'pods_ui_dfv_pick_modals_iframe', $iframe, $config, $args );

		if ( ! empty( $iframe['src'] ) ) {
			// We extend wp.media.view.Modal for modal add/edit, we must ensure we load the template for it
			wp_enqueue_media();

		}

		$config['iframe_src']        = $iframe['src'];
		$config['iframe_title_add']  = $iframe['title_add'];
		$config['iframe_title_edit'] = $iframe['title_edit'];

		return $config;

	}

	/**
	 * {@inheritdoc}
	 */
	public function build_dfv_field_item_data( $args ) {

		$args->options['supports_thumbnails'] = null;

		$item_data = array();

		if ( ! empty( $args->options['data'] ) ) {
			$item_data = $this->build_dfv_field_item_data_recurse( $args->options['data'], $args );
		}

		return $item_data;

	}

	/**
	 * Loop through relationship data and expand item data with additional information for DFV.
	 *
	 * @param array  $data    Item data to expand.
	 * @param object $args    {
	 *      Field information arguments.
	 *
	 *     @type string     $name    Field name.
	 *     @type string     $type    Field type.
	 *     @type array      $options Field options.
	 *     @type mixed      $value   Current value.
	 *     @type array      $pod     Pod information.
	 *     @type int|string $id      Current item ID.
	 * }
	 *
	 * @return array
	 */
	public function build_dfv_field_item_data_recurse( $data, $args ) {

		$item_data = array();

		foreach ( $data as $item_id => $item_title ) {
			if ( is_array( $item_title ) ) {
				$args->options['optgroup'] = true;

				$item_data[] = array(
					'label'      => $item_id,
					'collection' => $this->build_dfv_field_item_data_recurse( $item_title, $args ),
				);
			} else {
				// Key by item_id temporarily to be able to sort based on $args->value
				$item_data[ $item_id ] = $this->build_dfv_field_item_data_recurse_item( $item_id, $item_title, $args );
			}
		}

		// Maintain any saved sort order from $args->value
		if ( is_array( $args->value ) && 1 < count( $args->value ) && $this->is_autocomplete( $args->options ) ) {
			$item_data = array_replace( $args->value, $item_data );
		}

		// Convert from associative to numeric array
		$item_data = array_values( $item_data );

		return $item_data;

	}

	/**
	 * Loop through relationship data and expand item data with additional information for DFV.
	 *
	 * @param int|string $item_id    Item ID.
	 * @param string     $item_title Item title.
	 * @param object     $args       {
	 *      Field information arguments.
	 *
	 *     @type string      $name    Field name.
	 *     @type string      $type    Field type.
	 *     @type array       $options Field options.
	 *     @type mixed       $value   Current value.
	 *     @type array       $pod     Pod information.
	 *     @type int|string  $id      Current item ID.
	 * }
	 *
	 * @return array
	 */
	public function build_dfv_field_item_data_recurse_item( $item_id, $item_title, $args ) {

		$icon         = '';
		$img_icon     = '';
		$edit_link    = '';
		$link         = '';

		if ( ! isset( $args->options['supports_thumbnails'] ) ) {
			$args->options['supports_thumbnails'] = null;
		}

		switch ( $args->options['pick_object'] ) {
			case 'post_type':
				$item_id = (int) $item_id;

				if ( null === $args->options['supports_thumbnails'] && ! empty( $args->options['pick_val'] ) ) {
					$args->options['supports_thumbnails'] = post_type_supports( $args->options['pick_val'], 'thumbnail' );
				}

				if ( true === $args->options['supports_thumbnails'] ) {
					$post_thumbnail_id = get_post_thumbnail_id( $item_id );

					if ( $post_thumbnail_id ) {
						$thumb = wp_get_attachment_image_src( $post_thumbnail_id, 'thumbnail', false );
					}

					if ( ! empty( $thumb[0] ) ) {
						$img_icon = $thumb[0];
					}
				}

				if ( empty( $img_icon ) ) {

					// Default icon for posts.
					$icon = 'dashicons-admin-post';

					// Post type icons.
					$post_type = (array) get_post_type_object( get_post_type( $item_id ) );

					if ( ! empty( $post_type['menu_icon'] ) ) {
						// Post specific icon.
						$icon = $post_type['menu_icon'];
					} elseif ( isset( $post_type['name'] ) && 'page' ) {
						switch ( $post_type['name'] ) {
							case 'page':
								// Default for pages.
								$icon = 'dashicons-admin-page';
								break;
							case 'attachment':
								// Default for attachments.
								$icon = 'dashicons-admin-media';
								break;
						}
					}
				}//end if

				$edit_link = get_edit_post_link( $item_id, 'raw' );

				$link = get_permalink( $item_id );

				break;

			case 'taxonomy':
				$item_id = (int) $item_id;

				if ( ! empty( $args->options['pick_val'] ) ) {

					// Default icon for taxonomy.
					$icon = 'dashicons-category';

					// Change icon for non-hierarchical taxonomies.
					$taxonomy = get_term( $item_id );
					if ( isset( $taxonomy->taxonomy ) ) {
						$taxonomy = (array) get_taxonomy( $taxonomy->taxonomy );
						if ( isset( $taxonomy['hierarchical'] ) && ! $taxonomy['hierarchical'] ) {
							$icon = 'dashicons-tag';
						}
					}

					$edit_link = get_edit_term_link( $item_id, $args->options['pick_val'] );

					$link = get_term_link( $item_id, $args->options['pick_val'] );
				}

				break;

			case 'user':
				$item_id = (int) $item_id;

				$args->options['supports_thumbnails'] = true;

				$icon     = 'dashicons-admin-users';
				$img_icon = get_avatar_url( $item_id, array( 'size' => 150 ) );

				$edit_link = get_edit_user_link( $item_id );

				$link = get_author_posts_url( $item_id );

				break;

			case 'comment':
				$item_id = (int) $item_id;

				$args->options['supports_thumbnails'] = true;

				$icon     = 'dashicons-admin-comments';
				$img_icon = get_avatar_url( get_comment( $item_id ), array( 'size' => 150 ) );

				$edit_link = get_edit_comment_link( $item_id );

				$link = get_comment_link( $item_id );

				break;

			case 'pod':
				$item_id = (int) $item_id;

				if ( ! empty( $args->options['pick_val'] ) ) {

					$icon = 'dashicons-pods';

					if ( pods_is_admin( array( 'pods', 'pods_content', 'pods_edit_' . $args->options['pick_val'] ) ) ) {
						$file_name  = 'admin.php';
						$query_args = array(
							'page'   => 'pods-manage-' . $args->options['pick_val'],
							'action' => 'edit',
							'id'     => $item_id,
						);

						$edit_link = add_query_arg( $query_args, admin_url( $file_name ) );
					}

					// @todo Add $link support
					$link = '';
				}

				break;
		}//end switch

		// Image icons always overwrite default icons
		if ( ! empty( $img_icon ) ) {
			$icon = $img_icon;
		}

		// Parse icon type
		if ( 'none' === $icon || 'div' === $icon ) {
			$icon         = '';
		} elseif ( 0 === strpos( $icon, 'data:image/svg+xml;base64,' ) ) {
			$icon         = esc_attr( $icon );
		} elseif ( 0 === strpos( $icon, 'dashicons-' ) ) {
			$icon         = sanitize_html_class( $icon );
		}

		// Support modal editing
		if ( ! empty( $edit_link ) ) {
			// @todo: Replace string literal with defined constant
			$edit_link = add_query_arg( array( 'pods_modal' => '1' ), $edit_link );
		}

		// Determine if this is a selected item
		$selected = false;

		if ( is_array( $args->value ) ) {
			if ( ! isset( $args->value[0] ) ) {
				$keys = array_map( 'strval', array_keys( $args->value ) );

				if ( in_array( (string) $item_id, $keys, true ) ) {
					$selected = true;
				}
			}

			if ( ! $selected ) {
				// Cast values in array as string.
				$args->value = array_map( 'strval', $args->value );

				if ( in_array( (string) $item_id, $args->value, true ) ) {
					$selected = true;
				}
			}
		} elseif ( (string) $item_id === (string) $args->value ) {
			$selected = true;
		}

		$item = array(
			'id'           => $item_id,
			'icon'         => $icon,
			'name'         => $item_title,
			'edit_link'    => $edit_link,
			'link'         => $link,
			'selected'     => $selected,
		);

		return $item;

	}

	/**
	 * {@inheritdoc}
	 */
	public function validate( $value, $name = null, $options = null, $fields = null, $pod = null, $id = null, $params = null ) {

		if ( empty( self::$api ) ) {
			self::$api = pods_api();
		}

		$simple_tableless_objects = $this->simple_objects();

		$related_pick_limit  = 0;
		$related_field       = false;
		$related_pod         = false;
		$current_related_ids = false;

		// Bidirectional relationship requirement checks
		$related_object = pods_v( static::$type . '_object', $options, '' );
		// pod, post_type, taxonomy, etc..
		$related_val = pods_v( static::$type . '_val', $options, $related_object, null, true );
		// pod name, post type name, taxonomy name, etc..
		if ( empty( $related_val ) ) {
			$related_val = $related_object;
		}

		$related_sister_id = (int) pods_v( 'sister_id', $options, 0 );

		$options['id'] = (int) $options['id'];

		if ( ! isset( self::$related_data[ $options['id'] ] ) || empty( self::$related_data[ $options['id'] ] ) ) {
			self::$related_data[ $options['id'] ] = array();
		}

		if ( ! empty( $related_sister_id ) && ! in_array( $related_object, $simple_tableless_objects, true ) ) {
			$related_pod = self::$api->load_pod(
				array(
					'name'       => $related_val,
					'table_info' => false,
				), false
			);

			if ( false !== $related_pod && ( 'pod' === $related_object || $related_object === $related_pod['type'] ) ) {
				$related_field = false;

				// Ensure sister_id exists on related Pod.
				foreach ( $related_pod['fields'] as $related_pod_field ) {
					if ( 'pick' === $related_pod_field['type'] && $related_sister_id === $related_pod_field['id'] ) {
						$related_field = $related_pod_field;

						break;
					}
				}

				if ( ! empty( $related_field ) ) {
					$current_ids = self::$api->lookup_related_items( $fields[ $name ]['id'], $pod['id'], $id, $fields[ $name ], $pod );

					self::$related_data[ $options['id'] ]['current_ids'] = $current_ids;

					$value_ids = $value;

					// Convert values from a comma-separated string into an array.
					if ( ! is_array( $value_ids ) ) {
						$value_ids = explode( ',', $value_ids );
					}

					$value_ids = array_unique( array_filter( $value_ids ) );

					// Get ids to remove.
					$remove_ids = array_diff( $current_ids, $value_ids );

					$related_required   = (boolean) pods_v( 'required', $related_field['options'], 0 );
					$related_pick_limit = (int) pods_v( static::$type . '_limit', $related_field['options'], 0 );

					if ( 'single' === pods_v( static::$type . '_format_type', $related_field['options'] ) ) {
						$related_pick_limit = 1;
					}

					// Validate Required fields.
					if ( $related_required && ! empty( $remove_ids ) ) {
						foreach ( $remove_ids as $related_id ) {
							$bidirectional_ids = self::$api->lookup_related_items( $related_field['id'], $related_pod['id'], $related_id, $related_field, $related_pod );

							self::$related_data[ $options['id'] ][ 'related_ids_' . $related_id ] = $bidirectional_ids;

							if ( empty( $bidirectional_ids ) || ( in_array( (int) $id, $bidirectional_ids, true ) && 1 === count( $bidirectional_ids ) ) ) {
								return sprintf( __( 'The %1$s field is required and cannot be removed by the %2$s field', 'pods' ), $related_field['label'], $options['label'] );
							}
						}
					}
				} else {
					$related_pod = false;
				}//end if
			} else {
				$related_pod = false;
			}//end if
		}//end if

		if ( empty( self::$related_data[ $options['id'] ] ) ) {
			unset( self::$related_data[ $options['id'] ] );
		} else {
			self::$related_data[ $options['id'] ]['related_pod']        = $related_pod;
			self::$related_data[ $options['id'] ]['related_field']      = $related_field;
			self::$related_data[ $options['id'] ]['related_pick_limit'] = $related_pick_limit;

			$pick_limit = (int) pods_v( static::$type . '_limit', $options['options'], 0 );

			if ( 'single' === pods_v( static::$type . '_format_type', $options['options'] ) ) {
				$pick_limit = 1;
			}

			$related_field['id'] = (int) $related_field['id'];

			if ( ! isset( self::$related_data[ $related_field['id'] ] ) || empty( self::$related_data[ $related_field['id'] ] ) ) {
				self::$related_data[ $related_field['id'] ] = array(
					'related_pod'        => $pod,
					'related_field'      => $options,
					'related_pick_limit' => $pick_limit,
				);
			}
		}//end if

		return true;

	}

	/**
	 * {@inheritdoc}
	 */
	public function save( $value, $id = null, $name = null, $options = null, $fields = null, $pod = null, $params = null ) {

		if ( empty( self::$api ) ) {
			self::$api = pods_api();
		}

		$options['id'] = (int) $options['id'];

		if ( ! isset( self::$related_data[ $options['id'] ] ) ) {
			return;
		}

		$related_pod        = self::$related_data[ $options['id'] ]['related_pod'];
		$related_field      = self::$related_data[ $options['id'] ]['related_field'];
		$related_pick_limit = self::$related_data[ $options['id'] ]['related_pick_limit'];

		// Bidirectional relationship updates.
		if ( ! empty( $related_field ) ) {
			// Don't use no conflict mode unless this isn't the current pod type.
			$no_conflict = true;

			if ( $related_pod['type'] !== $pod['type'] ) {
				$no_conflict = pods_no_conflict_check( $related_pod['type'] );
			}

			if ( ! $no_conflict ) {
				pods_no_conflict_on( $related_pod['type'] );
			}

			$value = array_filter( $value );

			foreach ( $value as $related_id ) {
				if ( isset( self::$related_data[ $options['id'] ][ 'related_ids_' . $related_id ] ) && ! empty( self::$related_data[ $options['id'] ][ 'related_ids_' . $related_id ] ) ) {
					$bidirectional_ids = self::$related_data[ $options['id'] ][ 'related_ids_' . $related_id ];
				} else {
					$bidirectional_ids = self::$api->lookup_related_items( $related_field['id'], $related_pod['id'], $related_id, $related_field, $related_pod );
				}

				$bidirectional_ids = array_filter( $bidirectional_ids );

				if ( empty( $bidirectional_ids ) ) {
					$bidirectional_ids = array();
				}

				$remove_ids = array();

				if ( 0 < $related_pick_limit && ! empty( $bidirectional_ids ) && ! in_array( $id, $bidirectional_ids, true ) ) {
					$total_bidirectional_ids = count( $bidirectional_ids );

					while ( $related_pick_limit <= $total_bidirectional_ids ) {
						$remove_ids[] = (int) array_pop( $bidirectional_ids );

						$total_bidirectional_ids = count( $bidirectional_ids );
					}
				}

				// Remove this item from related items no longer related to.
				$remove_ids = array_unique( array_filter( $remove_ids ) );

				if ( ! in_array( $id, $bidirectional_ids, true ) ) {
					// Add to related items.
					$bidirectional_ids[] = $id;
				} elseif ( empty( $remove_ids ) ) {
					// Nothing to change.
					continue;
				}

				self::$api->save_relationships( $related_id, $bidirectional_ids, $related_pod, $related_field );

				if ( ! empty( $remove_ids ) ) {
					self::$api->delete_relationships( $remove_ids, $related_id, $pod, $options );
				}
			}//end foreach

			if ( ! $no_conflict ) {
				pods_no_conflict_off( $related_pod['type'] );
			}
		}//end if

	}

	/**
	 * Delete the value from the DB
	 *
	 * @param int|null    $id      Item ID.
	 * @param string|null $name    Field name.
	 * @param array|null  $options Field options.
	 * @param array|null  $pod     Pod options.
	 *
	 * @since 2.3.0
	 */
	public function delete( $id = null, $name = null, $options = null, $pod = null ) {

		if ( empty( self::$api ) ) {
			self::$api = pods_api();
		}

		$simple_tableless_objects = $this->simple_objects();

		// Bidirectional relationship requirement checks.
		$related_object = pods_v( static::$type . '_object', $options, '' );
		// pod, post_type, taxonomy, etc..
		$related_val = pods_v( static::$type . '_val', $options, $related_object, true );
		// pod name, post type name, taxonomy name, etc..
		$related_sister_id = (int) pods_v( 'sister_id', $options, 0 );

		if ( ! empty( $related_sister_id ) && ! in_array( $related_object, $simple_tableless_objects, true ) ) {
			$related_pod = self::$api->load_pod(
				array(
					'name'       => $related_val,
					'table_info' => false,
				), false
			);

			if ( false !== $related_pod && ( 'pod' === $related_object || $related_object === $related_pod['type'] ) ) {
				$related_field = false;

				// Ensure sister_id exists on related Pod.
				foreach ( $related_pod['fields'] as $related_pod_field ) {
					if ( 'pick' === $related_pod_field['type'] && (int) $related_sister_id === (int) $related_pod_field['id'] ) {
						$related_field = $related_pod_field;

						break;
					}
				}

				if ( ! empty( $related_field ) ) {
					$values = self::$api->lookup_related_items( $options['id'], $pod['id'], $id, $options, $pod );

					if ( ! empty( $values ) ) {
						$no_conflict = pods_no_conflict_check( $related_pod['type'] );

						if ( ! $no_conflict ) {
							pods_no_conflict_on( $related_pod['type'] );
						}

						self::$api->delete_relationships( $values, $id, $related_pod, $related_field );

						if ( ! $no_conflict ) {
							pods_no_conflict_off( $related_pod['type'] );
						}
					}
				}
			}//end if
		}//end if

	}

	/**
	 * {@inheritdoc}
	 */
	public function ui( $id, $value, $name = null, $options = null, $fields = null, $pod = null ) {

		$value = $this->simple_value( $name, $value, $options, $pod, $id );

		return $this->display( $value, $name, $options, $pod, $id );

	}

	/**
	 * {@inheritdoc}
	 */
	public function data( $name, $value = null, $options = null, $pod = null, $id = null, $in_form = true ) {

		if ( isset( $options['options'] ) ) {
			$options = array_merge( $options, $options['options'] );

			unset( $options['options'] );
		}

		$data = pods_v( 'data', $options, null, true );

		$object_params = array(
			// The name of the field.
			'name'    => $name,
			// The value of the field.
			'value'   => $value,
			// Field options.
			'options' => $options,
			// Pod data.
			'pod'     => $pod,
			// Item ID.
			'id'      => $id,
			// Data context.
			'context' => 'data',
		);

		if ( null !== $data ) {
			$data = (array) $data;
		} else {
			$data = $this->get_object_data( $object_params );
		}

		if ( 'single' === pods_v( static::$type . '_format_type', $options, 'single' ) && 'dropdown' === pods_v( static::$type . '_format_single', $options, 'dropdown' ) ) {
			$data = array( '' => pods_v( static::$type . '_select_text', $options, __( '-- Select One --', 'pods' ), true ) ) + $data;
		}

		$data = apply_filters( 'pods_field_pick_data', $data, $name, $value, $options, $pod, $id );

		return $data;

	}

	/**
	 * Convert a simple value to the correct value
	 *
	 * @param string            $name    The name of the field.
	 * @param string|array|null $value   The value of the field.
	 * @param array|null        $options Field options.
	 * @param array|null        $pod     Pod data.
	 * @param int|null          $id      Item ID.
	 * @param boolean           $raw     Whether to return the raw list of keys (true) or convert to key=>value (false).
	 *
	 * @return mixed Corrected value
	 */
	public function simple_value( $name, $value = null, $options = null, $pod = null, $id = null, $raw = false ) {

		if ( in_array( pods_v( static::$type . '_object', $options ), self::simple_objects(), true ) ) {
			if ( isset( $options['options'] ) ) {
				$options = array_merge( $options, $options['options'] );

				unset( $options['options'] );
			}

			if ( ! is_array( $value ) && 0 < strlen( $value ) ) {
				$simple = @json_decode( $value, true );

				if ( is_array( $simple ) ) {
					$value = $simple;
				}
			}

			$data = pods_v( 'data', $options, null, true );

			$object_params = array(
				// The name of the field.
				'name'    => $name,
				// The value of the field.
				'value'   => $value,
				// Field options.
				'options' => $options,
				// Pod data.
				'pod'     => $pod,
				// Item ID.
				'id'      => $id,
				// Data context.
				'context' => 'simple_value',
			);

			if ( null === $data ) {
				$data = $this->get_object_data( $object_params );
			}

			$data = (array) $data;

			$key = 0;

			if ( is_array( $value ) ) {
				if ( ! empty( $data ) ) {
					$val = array();

					foreach ( $value as $k => $v ) {
						if ( isset( $data[ $v ] ) ) {
							if ( false === $raw ) {
								$k = $v;
								$v = $data[ $v ];
							}

							$val[ $k ] = $v;
						}
					}

					$value = $val;
				}
			} elseif ( isset( $data[ $value ] ) && false === $raw ) {
				$key   = $value;
				$value = $data[ $value ];
			}//end if

			$single_multi = pods_v( static::$type . '_format_type', $options, 'single' );

			if ( 'multi' === $single_multi ) {
				$limit = (int) pods_v( static::$type . '_limit', $options, 0 );
			} else {
				$limit = 1;
			}

			if ( is_array( $value ) && 0 < $limit ) {
				if ( 1 === $limit ) {
					$value = current( $value );
				} else {
					$value = array_slice( $value, 0, $limit, true );
				}
			} elseif ( ! is_array( $value ) && null !== $value && 0 < strlen( $value ) ) {
				if ( 1 !== $limit || ( true === $raw && 'multi' === $single_multi ) ) {
					$value = array(
						$key => $value,
					);
				}
			}
		}//end if

		return $value;

	}

	/**
	 * Get the label from a pick value.
	 *
	 * @param string            $name    The name of the field.
	 * @param string|array|null $value   The value of the field.
	 * @param array|null        $options Field options.
	 * @param array|null        $pod     Pod data.
	 * @param int|null          $id      Item ID.
	 *
	 * @return string
	 *
	 * @since 2.2.0
	 */
	public function value_to_label( $name, $value = null, $options = null, $pod = null, $id = null ) {

		if ( isset( $options['options'] ) ) {
			$options = array_merge( $options, $options['options'] );

			unset( $options['options'] );
		}

		$data = pods_v( 'data', $options, null, true );

		$object_params = array(
			// The name of the field.
			'name'    => $name,
			// The value of the field.
			'value'   => $value,
			// Field options.
			'options' => $options,
			// Pod data.
			'pod'     => $pod,
			// Item ID.
			'id'      => $id,
			// Data context.
			'context' => 'value_to_label',
		);

		if ( null !== $data ) {
			$data = (array) $data;
		} else {
			$data = $this->get_object_data( $object_params );
		}

		$labels = array();

		$check_value = $value;

		foreach ( $check_value as $check_k => $check_v ) {
			$check_value[ $check_k ] = (string) $check_v;
		}

		foreach ( $data as $v => $l ) {
			if ( ! in_array( (string) $l, $labels, true ) && ( (string) $value === (string) $v || ( is_array( $value ) && in_array( (string) $v, $value, true ) ) ) ) {
				$labels[] = (string) $l;
			}
		}

		$labels = apply_filters( 'pods_field_pick_value_to_label', $labels, $name, $value, $options, $pod, $id );

		$labels = pods_serial_comma( $labels );

		return $labels;

	}

	/**
	 * Get available items from a relationship field.
	 *
	 * @param array|string $field         Field array or field name.
	 * @param array        $options       Field options array overrides.
	 * @param array        $object_params Additional get_object_data options.
	 *
	 * @return array An array of available items from a relationship field
	 */
	public function get_field_data( $field, $options = array(), $object_params = array() ) {

		// Handle field array overrides.
		if ( is_array( $field ) ) {
			$options = array_merge( $field, $options );
		}

		// Get field name from array.
		$field = pods_v( 'name', $options, $field, true );

		// Field name or options not set.
		if ( empty( $field ) || empty( $options ) ) {
			return array();
		}

		// Options normalization.
		$options = array_merge( $options, pods_v( 'options', $options, array(), true ) );

		// Setup object params.
		$object_params = array_merge(
			array(
				// The name of the field.
				'name'    => $field,
				// Field options.
				'options' => $options,
			), $object_params
		);

		// Get data override.
		$data = pods_v( 'data', $options, null, true );

		if ( null !== $data ) {
			// Return data override.
			$data = (array) $data;
		} else {
			// Get object data.
			$data = $this->get_object_data( $object_params );
		}

		return $data;

	}

	/**
	 * Get data from relationship objects.
	 *
	 * @param array $object_params Object data parameters.
	 *
	 * @return array|bool Object data
	 */
	public function get_object_data( $object_params = null ) {

		/**
		 * @var $wpdb wpdb
		 */
		global $wpdb;

		$object_params = array_merge(
			array(
				// The name of the field.
				'name'        => '',
				// The value of the field.
				'value'       => '',
				// Field options.
				'options'     => array(),
				// Pod data.
				'pod'         => '',
				// Item ID.
				'id'          => '',
				// Data context.
				'context'     => '',
				// Data parameters.
				'data_params' => array(
					// Query being searched.
					'query' => '',
				),
				// Page number of results to get.
				'page'        => 1,
				// How many data items to limit to (autocomplete defaults to 30, set to -1 or 1+ to override).
				'limit'       => 0,
			), $object_params
		);

		$object_params['options']     = (array) $object_params['options'];
		$object_params['data_params'] = (array) $object_params['data_params'];

		$name         = $object_params['name'];
		$value        = $object_params['value'];
		$options      = $object_params['options'];
		$pod          = $object_params['pod'];
		$id           = $object_params['id'];
		$context      = $object_params['context'];
		$data_params  = $object_params['data_params'];
		$page         = min( 1, (int) $object_params['page'] );
		$limit        = (int) $object_params['limit'];
		$autocomplete = false;

		if ( isset( $options['options'] ) ) {
			$options = array_merge( $options, $options['options'] );

			unset( $options['options'] );
		}

		$data  = apply_filters( 'pods_field_pick_object_data', null, $name, $value, $options, $pod, $id, $object_params );
		$items = array();

		if ( ! isset( $options[ static::$type . '_object' ] ) ) {
			$data = pods_v( 'data', $options, array(), true );
		}

		$simple = false;

		if ( null === $data ) {
			$data = array();

			if ( 'custom-simple' === $options[ static::$type . '_object' ] ) {
				$custom = pods_v( static::$type . '_custom', $options, '' );

				$custom = apply_filters( 'pods_form_ui_field_pick_custom_values', $custom, $name, $value, $options, $pod, $id, $object_params );

				if ( ! empty( $custom ) ) {
					if ( ! is_array( $custom ) ) {
						$data = array();

						$custom = explode( "\n", trim( $custom ) );

						foreach ( $custom as $custom_value ) {
							$custom_label = explode( '|', $custom_value );

							if ( empty( $custom_label ) ) {
								continue;
							}

							if ( 1 === count( $custom_label ) ) {
								$custom_label = $custom_value;
							} else {
								$custom_value = $custom_label[0];
								$custom_label = $custom_label[1];
							}

							$custom_value = trim( (string) $custom_value );
							$custom_label = trim( (string) $custom_label );

							$data[ $custom_value ] = $custom_label;
						}
					} else {
						$data = $custom;
					}//end if

					$simple = true;
				}//end if
			} elseif ( isset( self::$related_objects[ $options[ static::$type . '_object' ] ] ) && isset( self::$related_objects[ $options[ static::$type . '_object' ] ]['data'] ) && ! empty( self::$related_objects[ $options[ static::$type . '_object' ] ]['data'] ) ) {
				$data = self::$related_objects[ $options[ static::$type . '_object' ] ]['data'];

				$simple = true;
			} elseif ( isset( self::$related_objects[ $options[ static::$type . '_object' ] ] ) && isset( self::$related_objects[ $options[ static::$type . '_object' ] ]['data_callback'] ) && is_callable( self::$related_objects[ $options[ static::$type . '_object' ] ]['data_callback'] ) ) {
				$data = call_user_func_array(
					self::$related_objects[ $options[ static::$type . '_object' ] ]['data_callback'], array(
						$name,
						$value,
						$options,
						$pod,
						$id,
					)
				);

				if ( 'data' === $context ) {
					self::$field_data = array(
						'field'        => $name,
						'id'           => $options['id'],
						'autocomplete' => false,
					);
				}

				$simple = true;

				// Cache data from callback.
				if ( ! empty( $data ) ) {
					self::$related_objects[ $options[ static::$type . '_object' ] ]['data'] = $data;
				}
			} elseif ( 'simple_value' !== $context ) {
				$pick_val = pods_v( static::$type . '_val', $options );

				if ( 'table' === pods_v( static::$type . '_object', $options ) ) {
					$pick_val = pods_v( static::$type . '_table', $options, $pick_val, true );
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

				$options['table_info'] = pods_api()->get_table_info( pods_v( static::$type . '_object', $options ), $pick_val, null, null, $object_params );

				$search_data = pods_data();
				$search_data->table( $options['table_info'] );

				if ( isset( $options['table_info']['pod'] ) && ! empty( $options['table_info']['pod'] ) && isset( $options['table_info']['pod']['name'] ) ) {
					$search_data->pod    = $options['table_info']['pod']['name'];
					$search_data->fields = $options['table_info']['pod']['fields'];
				}

				$params = array(
					'select'     => "`t`.`{$search_data->field_id}`, `t`.`{$search_data->field_index}`",
					'table'      => $search_data->table,
					'where'      => pods_v( static::$type . '_where', $options, (array) $options['table_info']['where_default'], true ),
					'orderby'    => pods_v( static::$type . '_orderby', $options, null, true ),
					'groupby'    => pods_v( static::$type . '_groupby', $options, null, true ),
					'pagination' => false,
					'search'     => false,
				);

				if ( in_array( $options[ static::$type . '_object' ], array( 'site', 'network' ), true ) ) {
					$params['select'] .= ', `t`.`path`';
				}

				if ( ! empty( $params['where'] ) && (array) $options['table_info']['where_default'] !== $params['where'] ) {
					$params['where'] = pods_evaluate_tags( $params['where'], true );
				}

				if ( empty( $params['where'] ) || ( ! is_array( $params['where'] ) && '' === trim( $params['where'] ) ) ) {
					$params['where'] = array();
				} elseif ( ! is_array( $params['where'] ) ) {
					$params['where'] = (array) $params['where'];
				}

				if ( 'value_to_label' === $context ) {
					$params['where'][] = "`t`.`{$search_data->field_id}` = " . number_format( $value, 0, '', '' );
				}

				$display = trim( pods_v( static::$type . '_display', $options ), ' {@}' );

				if ( 0 < strlen( $display ) ) {
					if ( isset( $options['table_info']['pod'] ) && ! empty( $options['table_info']['pod'] ) ) {
						if ( isset( $options['table_info']['pod']['object_fields'] ) && isset( $options['table_info']['pod']['object_fields'][ $display ] ) ) {
							$search_data->field_index = $display;

							$params['select'] = "`t`.`{$search_data->field_id}`, `t`.`{$search_data->field_index}`";
						} elseif ( isset( $options['table_info']['pod']['fields'][ $display ] ) ) {
							$search_data->field_index = $display;

							if ( 'table' === $options['table_info']['pod']['storage'] && ! in_array(
								$options['table_info']['pod']['type'], array(
									'pod',
									'table',
								), true
							)
							) {
								$params['select'] = "`t`.`{$search_data->field_id}`, `d`.`{$search_data->field_index}`";
							} elseif ( 'meta' === $options['table_info']['pod']['storage'] ) {
								$params['select'] = "`t`.`{$search_data->field_id}`, `{$search_data->field_index}`.`meta_value` AS {$search_data->field_index}";
							} else {
								$params['select'] = "`t`.`{$search_data->field_id}`, `t`.`{$search_data->field_index}`";
							}
						}//end if
					} elseif ( isset( $options['table_info']['object_fields'] ) && isset( $options['table_info']['object_fields'][ $display ] ) ) {
						$search_data->field_index = $display;

						$params['select'] = "`t`.`{$search_data->field_id}`, `t`.`{$search_data->field_index}`";
					}//end if
				}//end if

				$autocomplete = $this->is_autocomplete( $options );

				$hierarchy = false;

				if ( 'data' === $context && ! $autocomplete ) {
					if ( 'single' === pods_v( static::$type . '_format_type', $options, 'single' ) && in_array(
						pods_v( static::$type . '_format_single', $options, 'dropdown' ), array(
							'dropdown',
							'radio',
						), true
					)
					) {
						$hierarchy = true;
					} elseif ( 'multi' === pods_v( static::$type . '_format_type', $options, 'single' ) && in_array(
						pods_v( static::$type . '_format_multi', $options, 'checkbox' ), array(
							'multiselect',
							'checkbox',
						), true
					)
					) {
						$hierarchy = true;
					}
				}

				if ( $hierarchy && $options['table_info']['object_hierarchical'] && ! empty( $options['table_info']['field_parent'] ) ) {
					$params['select'] .= ', ' . $options['table_info']['field_parent_select'];
				}

				if ( $autocomplete ) {
					if ( 0 === $limit ) {
						$limit = 30;
					}

					$params['limit'] = apply_filters( 'pods_form_ui_field_pick_autocomplete_limit', $limit, $name, $value, $options, $pod, $id, $object_params );

					if ( is_array( $value ) && $params['limit'] < count( $value ) ) {
						$params['limit'] = count( $value );
					}

					$params['page'] = $page;

					if ( 'admin_ajax_relationship' === $context ) {
						$lookup_where = array(
							$search_data->field_index => "`t`.`{$search_data->field_index}` LIKE '%" . pods_sanitize_like( $data_params['query'] ) . "%'",
						);

						// @todo Hook into WPML for each table
						if ( $wpdb->users === $search_data->table ) {
							$lookup_where['display_name'] = "`t`.`display_name` LIKE '%" . pods_sanitize_like( $data_params['query'] ) . "%'";
							$lookup_where['user_login']   = "`t`.`user_login` LIKE '%" . pods_sanitize_like( $data_params['query'] ) . "%'";
							$lookup_where['user_email']   = "`t`.`user_email` LIKE '%" . pods_sanitize_like( $data_params['query'] ) . "%'";
						} elseif ( $wpdb->posts === $search_data->table ) {
							$lookup_where['post_title']   = "`t`.`post_title` LIKE '%" . pods_sanitize_like( $data_params['query'] ) . "%'";
							$lookup_where['post_name']    = "`t`.`post_name` LIKE '%" . pods_sanitize_like( $data_params['query'] ) . "%'";
							$lookup_where['post_content'] = "`t`.`post_content` LIKE '%" . pods_sanitize_like( $data_params['query'] ) . "%'";
							$lookup_where['post_excerpt'] = "`t`.`post_excerpt` LIKE '%" . pods_sanitize_like( $data_params['query'] ) . "%'";
						} elseif ( $wpdb->terms === $search_data->table ) {
							$lookup_where['name'] = "`t`.`name` LIKE '%" . pods_sanitize_like( $data_params['query'] ) . "%'";
							$lookup_where['slug'] = "`t`.`slug` LIKE '%" . pods_sanitize_like( $data_params['query'] ) . "%'";
						} elseif ( $wpdb->comments === $search_data->table ) {
							$lookup_where['comment_content']      = "`t`.`comment_content` LIKE '%" . pods_sanitize_like( $data_params['query'] ) . "%'";
							$lookup_where['comment_author']       = "`t`.`comment_author` LIKE '%" . pods_sanitize_like( $data_params['query'] ) . "%'";
							$lookup_where['comment_author_email'] = "`t`.`comment_author_email` LIKE '%" . pods_sanitize_like( $data_params['query'] ) . "%'";
						}

						$lookup_where = apply_filters( 'pods_form_ui_field_pick_autocomplete_lookup', $lookup_where, $data_params['query'], $name, $value, $options, $pod, $id, $object_params, $search_data );

						if ( ! empty( $lookup_where ) ) {
							$params['where'][] = implode( ' OR ', $lookup_where );
						}

						$orderby   = array();
						$orderby[] = "(`t`.`{$search_data->field_index}` LIKE '%" . pods_sanitize_like( $data_params['query'] ) . "%' ) DESC";

						$pick_orderby = pods_v( static::$type . '_orderby', $options, null, true );

						if ( 0 < strlen( $pick_orderby ) ) {
							$orderby[] = $pick_orderby;
						}

						$orderby[] = "`t`.`{$search_data->field_index}`";
						$orderby[] = "`t`.`{$search_data->field_id}`";

						$params['orderby'] = $orderby;
					}//end if
				} elseif ( 0 < $limit ) {
					$params['limit'] = $limit;
					$params['page']  = $page;
				}//end if

				$extra = '';

				if ( $wpdb->posts === $search_data->table ) {
					$extra = ', `t`.`post_type`';
				} elseif ( $wpdb->terms === $search_data->table ) {
					$extra = ', `tt`.`taxonomy`';
				} elseif ( $wpdb->comments === $search_data->table ) {
					$extra = ', `t`.`comment_type`';
				}

				$params['select'] .= $extra;

				if ( 'user' === pods_v( static::$type . '_object', $options ) ) {
					$roles = pods_v( static::$type . '_user_role', $options );

					if ( ! empty( $roles ) ) {
						$where = array();

						foreach ( (array) $roles as $role ) {
							if ( empty( $role ) || ( pods_clean_name( $role ) !== $role && sanitize_title( $role ) !== $role ) ) {
								continue;
							}

							$prefix = $wpdb->base_prefix;

							if ( is_multisite() && ! is_main_site() ) {
								$prefix .= get_current_blog_id() . '_';
							}

							$where[] = $prefix . 'capabilities.meta_value LIKE "%\"' . pods_sanitize_like( $role ) . '\"%"';
						}

						if ( ! empty( $where ) ) {
							$params['where'][] = implode( ' OR ', $where );
						}
					}//end if
				}//end if

				$results = $search_data->select( $params );

				if ( $autocomplete && $params['limit'] < $search_data->total_found() ) {
					if ( ! empty( $value ) ) {
						$ids = $value;

						if ( is_array( $ids ) && isset( $ids[0] ) && is_array( $ids[0] ) ) {
							$ids = wp_list_pluck( $ids, $search_data->field_id );
						}

						if ( is_array( $ids ) ) {
							$ids = implode( ', ', $ids );
						}

						if ( is_array( $params['where'] ) ) {
							$params['where'] = implode( ' AND ', $params['where'] );
						}
						if ( ! empty( $params['where'] ) ) {
							$params['where'] .= ' AND ';
						}

						$params['where'] .= "`t`.`{$search_data->field_id}` IN ( {$ids} )";

						$results = $search_data->select( $params );
					}//end if
				} else {
					$autocomplete = false;
				}//end if

				if ( 'data' === $context ) {
					self::$field_data = array(
						'field'        => $name,
						'id'           => $options['id'],
						'autocomplete' => $autocomplete,
					);
				}

				if ( $hierarchy && ! $autocomplete && ! empty( $results ) && $options['table_info']['object_hierarchical'] && ! empty( $options['table_info']['field_parent'] ) ) {
					$select_args = array(
						'id'     => $options['table_info']['field_id'],
						'index'  => $options['table_info']['field_index'],
						'parent' => $options['table_info']['field_parent'],
					);

					$results = pods_hierarchical_select( $results, $select_args );
				}

				$ids = array();

				if ( ! empty( $results ) ) {
					$display_filter = pods_v( 'display_filter', pods_v( 'options', pods_v( $search_data->field_index, $search_data->pod_data['object_fields'] ) ) );

					foreach ( $results as $result ) {
						$result = get_object_vars( $result );

						if ( ! isset( $result[ $search_data->field_id ], $result[ $search_data->field_index ] ) ) {
							continue;
						}

						$result[ $search_data->field_index ] = trim( $result[ $search_data->field_index ] );

						$object      = '';
						$object_type = '';

						if ( $wpdb->posts === $search_data->table && isset( $result['post_type'] ) ) {
							$object      = $result['post_type'];
							$object_type = 'post_type';
						} elseif ( $wpdb->terms === $search_data->table && isset( $result['taxonomy'] ) ) {
							$object      = $result['taxonomy'];
							$object_type = 'taxonomy';
						}

						if ( 0 < strlen( $display_filter ) ) {
							$display_filter_args = pods_v( 'display_filter_args', pods_v( 'options', pods_v( $search_data->field_index, $search_data->pod_data['object_fields'] ) ) );

							$filter_args = array(
								$display_filter,
								$result[ $search_data->field_index ],
							);

							if ( ! empty( $display_filter_args ) ) {
								foreach ( (array) $display_filter_args as $display_filter_arg ) {
									if ( isset( $result[ $display_filter_arg ] ) ) {
										$filter_args[] = $result[ $display_filter_arg ];
									}
								}
							}

							$result[ $search_data->field_index ] = call_user_func_array( 'apply_filters', $filter_args );
						}

						if ( in_array( $options[ static::$type . '_object' ], array( 'site', 'network' ), true ) ) {
							$result[ $search_data->field_index ] = $result[ $search_data->field_index ] . $result['path'];
						} elseif ( '' === $result[ $search_data->field_index ] ) {
							$result[ $search_data->field_index ] = '(No Title)';
						}

						if ( 'admin_ajax_relationship' === $context ) {
							$items[] = $this->build_dfv_field_item_data_recurse_item( $result[ $search_data->field_id ], $result[ $search_data->field_index ], (object) $object_params );
						} else {
							$data[ $result[ $search_data->field_id ] ] = $result[ $search_data->field_index ];
						}

						$ids[] = $result[ $search_data->field_id ];
					}//end foreach
				}//end if
			}//end if

			if ( $simple && 'admin_ajax_relationship' === $context ) {
				$found_data = array();

				foreach ( $data as $k => $v ) {
					if ( false !== stripos( $v, $data_params['query'] ) || false !== stripos( $k, $data_params['query'] ) ) {
						$found_data[ $k ] = $v;
					}
				}

				$data = $found_data;
			}
		}//end if

		if ( 'admin_ajax_relationship' === $context ) {
			if ( empty( $items ) && ! empty( $data ) ) {
				foreach ( $data as $k => $v ) {
					$items[] = array(
						'id'   => $k,
						'text' => $v,
					);
				}
			}

			$data = $items;
		}

		return $data;

	}

	/**
	 * Check if field is autocomplete enabled.
	 *
	 * @param array $options Field options.
	 *
	 * @return bool
	 *
	 * @since 2.7.0
	 */
	private function is_autocomplete( $options ) {

		$autocomplete = false;

		if ( 'single' === pods_v( static::$type . '_format_type', $options, 'single' ) ) {
			if ( in_array( pods_v( static::$type . '_format_single', $options, 'dropdown' ), array( 'autocomplete', 'list' ), true ) ) {
				$autocomplete = true;
			}
		} elseif ( 'multi' === pods_v( static::$type . '_format_type', $options, 'single' ) ) {
			if ( in_array( pods_v( static::$type . '_format_multi', $options, 'checkbox' ), array( 'autocomplete', 'list' ), true ) ) {
				$autocomplete = true;
			}
		}

		return $autocomplete;
	}

	/**
	 * Check if a field type is a tableless text field type.
	 *
	 * @since 2.7.4
	 *
	 * @param string $type    Field type.
	 * @param array  $options Field options.
	 * @return bool True if the field type is a tableless text field type, false otherwise.
	 */
	private function is_simple_tableless( $type, array $options ) {
		$field_object = pods_v( $type . '_object', $options );

		return in_array( $field_object, PodsForm::simple_tableless_objects(), true );
	}

	/**
	 * Check if a field supports AJAX mode
	 *
	 * @param string $type    Field type.
	 * @param array  $options Field options.
	 *
	 * @return bool
	 * @since 2.7.4
	 */
	private function can_ajax( $type, $options ) {
		return $this->is_autocomplete( $options ) && ! $this->is_simple_tableless( $type, $options );
	}


	/**
	 * Handle autocomplete AJAX.
	 *
	 * @since 2.3.0
	 */
	public function admin_ajax_relationship() {

		pods_session_start();

		// Sanitize input.
		// @codingStandardsIgnoreLine
		$params = pods_unslash( (array) $_POST );

		foreach ( $params as $key => $value ) {
			if ( 'action' === $key ) {
				continue;
			}

			unset( $params[ $key ] );

			$params[ str_replace( '_podsfix_', '', $key ) ] = $value;
		}

		$params = (object) $params;

		$uid = @session_id();

		if ( is_user_logged_in() ) {
			$uid = 'user_' . get_current_user_id();
		}

		$nonce_check = 'pods_relationship_' . (int) $params->pod . '_' . $uid . '_' . $params->uri . '_' . (int) $params->field;

		if ( ! isset( $params->_wpnonce ) || false === wp_verify_nonce( $params->_wpnonce, $nonce_check ) ) {
			pods_error( __( 'Unauthorized request', 'pods' ), PodsInit::$admin );
		}

		if ( empty( self::$api ) ) {
			self::$api = pods_api();
		}

		$pod   = self::$api->load_pod( array( 'id' => (int) $params->pod ) );
		$field = self::$api->load_field(
			array(
				'id'         => (int) $params->field,
				'table_info' => true,
			)
		);
		$id    = (int) $params->id;

		$limit = 15;

		if ( isset( $params->limit ) ) {
			$limit = (int) $params->limit;
		}

		$page = 1;

		if ( isset( $params->page ) ) {
			$page = (int) $params->page;
		}

		if ( ! isset( $params->query ) || '' === trim( $params->query ) ) {
			pods_error( __( 'Invalid field request', 'pods' ), PodsInit::$admin );
		} elseif ( empty( $pod ) || empty( $field ) || (int) $pod['id'] !== (int) $field['pod_id'] || ! isset( $pod['fields'][ $field['name'] ] ) ) {
			pods_error( __( 'Invalid field request', 'pods' ), PodsInit::$admin );
		} elseif ( 'pick' !== $field['type'] || empty( $field['table_info'] ) ) {
			pods_error( __( 'Invalid field', 'pods' ), PodsInit::$admin );
		} elseif ( 'single' === pods_v( static::$type . '_format_type', $field ) && 'autocomplete' === pods_v( static::$type . '_format_single', $field ) ) {
			pods_error( __( 'Invalid field', 'pods' ), PodsInit::$admin );
		} elseif ( 'multi' === pods_v( static::$type . '_format_type', $field ) && 'autocomplete' === pods_v( static::$type . '_format_multi', $field ) ) {
			pods_error( __( 'Invalid field', 'pods' ), PodsInit::$admin );
		}

		$object_params = array(
			// The name of the field.
			'name'        => $field['name'],
			// The value of the field.
			'value'       => null,
			// Field options.
			'options'     => array_merge( $field, $field['options'] ),
			// Pod data.
			'pod'         => $pod,
			// Item ID.
			'id'          => $id,
			// Data context.
			'context'     => 'admin_ajax_relationship',
			'data_params' => $params,
			'page'        => $page,
			'limit'       => $limit,
		);

		$pick_data = apply_filters( 'pods_field_pick_data_ajax', null, $field['name'], null, $field, $pod, $id );

		if ( null !== $pick_data ) {
			$items = $pick_data;
		} else {
			$items = $this->get_object_data( $object_params );
		}

		if ( ! empty( $items ) && isset( $items[0] ) && ! is_array( $items[0] ) ) {
			$new_items = array();

			foreach ( $items as $id => $text ) {
				$new_items[] = array(
					'id'    => $id,
					'text'  => $text,
					'image' => '',
				);
			}

			$items = $new_items;
		}

		$items = apply_filters( 'pods_field_pick_data_ajax_items', $items, $field['name'], null, $field, $pod, $id );

		$items = array(
			'results' => $items,
		);

		wp_send_json( $items );

		die();
		// KBAI!
	}

	/**
	 * Data callback for Post Stati.
	 *
	 * @param string|null       $name    The name of the field.
	 * @param string|array|null $value   The value of the field.
	 * @param array|null        $options Field options.
	 * @param array|null        $pod     Pod data.
	 * @param int|null          $id      Item ID.
	 *
	 * @return array
	 *
	 * @since 2.3.0
	 */
	public function data_post_stati( $name = null, $value = null, $options = null, $pod = null, $id = null ) {

		$data = array();

		$post_stati = get_post_stati( array(), 'objects' );

		foreach ( $post_stati as $post_status ) {
			$data[ $post_status->name ] = $post_status->label;
		}

		return apply_filters( 'pods_form_ui_field_pick_data_post_stati', $data, $name, $value, $options, $pod, $id );

	}

	/**
	 * Data callback for User Roles.
	 *
	 * @param string|null       $name    The name of the field.
	 * @param string|array|null $value   The value of the field.
	 * @param array|null        $options Field options.
	 * @param array|null        $pod     Pod data.
	 * @param int|null          $id      Item ID.
	 *
	 * @return array
	 *
	 * @since 2.3.0
	 */
	public function data_roles( $name = null, $value = null, $options = null, $pod = null, $id = null ) {

		$data = array();

		global $wp_roles;

		foreach ( $wp_roles->role_objects as $key => $role ) {
			$data[ $key ] = $wp_roles->role_names[ $key ];
		}

		return apply_filters( 'pods_form_ui_field_pick_data_roles', $data, $name, $value, $options, $pod, $id );

	}

	/**
	 * Data callback for User Capabilities.
	 *
	 * @param string|null       $name    The name of the field.
	 * @param string|array|null $value   The value of the field.
	 * @param array|null        $options Field options.
	 * @param array|null        $pod     Pod data.
	 * @param int|null          $id      Item ID.
	 *
	 * @return array
	 *
	 * @since 2.3.0
	 */
	public function data_capabilities( $name = null, $value = null, $options = null, $pod = null, $id = null ) {

		$data = array();

		global $wp_roles;

		$default_caps = array(
			'activate_plugins',
			'add_users',
			'create_users',
			'delete_others_pages',
			'delete_others_posts',
			'delete_pages',
			'delete_plugins',
			'delete_posts',
			'delete_private_pages',
			'delete_private_posts',
			'delete_published_pages',
			'delete_published_posts',
			'delete_users',
			'edit_dashboard',
			'edit_files',
			'edit_others_pages',
			'edit_others_posts',
			'edit_pages',
			'edit_plugins',
			'edit_posts',
			'edit_private_pages',
			'edit_private_posts',
			'edit_published_pages',
			'edit_published_posts',
			'edit_theme_options',
			'edit_themes',
			'edit_users',
			'import',
			'install_plugins',
			'install_themes',
			'list_users',
			'manage_categories',
			'manage_links',
			'manage_options',
			'moderate_comments',
			'promote_users',
			'publish_pages',
			'publish_posts',
			'read',
			'read_private_pages',
			'read_private_posts',
			'remove_users',
			'switch_themes',
			'unfiltered_html',
			'unfiltered_upload',
			'update_core',
			'update_plugins',
			'update_themes',
			'upload_files',
		);

		$role_caps = array();

		foreach ( $wp_roles->role_objects as $key => $role ) {
			if ( is_array( $role->capabilities ) ) {
				foreach ( $role->capabilities as $cap => $grant ) {
					$role_caps[ $cap ] = $cap;
				}
			}
		}

		$role_caps = array_unique( $role_caps );

		$capabilities = array_merge( $default_caps, $role_caps );

		// To support Members filters.
		$capabilities = apply_filters( 'members_get_capabilities', $capabilities );

		$capabilities = apply_filters( 'pods_roles_get_capabilities', $capabilities );

		sort( $capabilities );

		$capabilities = array_unique( $capabilities );

		global $wp_roles;

		foreach ( $capabilities as $capability ) {
			$data[ $capability ] = $capability;
		}

		return apply_filters( 'pods_form_ui_field_pick_data_capabilities', $data, $name, $value, $options, $pod, $id );

	}

	/**
	 * Data callback for Image Sizes.
	 *
	 * @param string|null       $name    The name of the field.
	 * @param string|array|null $value   The value of the field.
	 * @param array|null        $options Field options.
	 * @param array|null        $pod     Pod data.
	 * @param int|null          $id      Item ID.
	 *
	 * @return array
	 *
	 * @since 2.3.0
	 */
	public function data_image_sizes( $name = null, $value = null, $options = null, $pod = null, $id = null ) {

		$data = array();

		$image_sizes = get_intermediate_image_sizes();

		foreach ( $image_sizes as $image_size ) {
			$data[ $image_size ] = ucwords( str_replace( '-', ' ', $image_size ) );
		}

		return apply_filters( 'pods_form_ui_field_pick_data_image_sizes', $data, $name, $value, $options, $pod, $id );

	}

	/**
	 * Data callback for Countries.
	 *
	 * @param string|null       $name    The name of the field.
	 * @param string|array|null $value   The value of the field.
	 * @param array|null        $options Field options.
	 * @param array|null        $pod     Pod data.
	 * @param int|null          $id      Item ID.
	 *
	 * @return array
	 *
	 * @since 2.3.0
	 */
	public function data_countries( $name = null, $value = null, $options = null, $pod = null, $id = null ) {

		$data = array(
			'AF' => __( 'Afghanistan' ),
			'AL' => __( 'Albania' ),
			'DZ' => __( 'Algeria' ),
			'AS' => __( 'American Samoa' ),
			'AD' => __( 'Andorra' ),
			'AO' => __( 'Angola' ),
			'AI' => __( 'Anguilla' ),
			'AQ' => __( 'Antarctica' ),
			'AG' => __( 'Antigua and Barbuda' ),
			'AR' => __( 'Argentina' ),
			'AM' => __( 'Armenia' ),
			'AW' => __( 'Aruba' ),
			'AU' => __( 'Australia' ),
			'AT' => __( 'Austria' ),
			'AZ' => __( 'Azerbaijan' ),
			'BS' => __( 'Bahamas' ),
			'BH' => __( 'Bahrain' ),
			'BD' => __( 'Bangladesh' ),
			'BB' => __( 'Barbados' ),
			'BY' => __( 'Belarus' ),
			'BE' => __( 'Belgium' ),
			'BZ' => __( 'Belize' ),
			'BJ' => __( 'Benin' ),
			'BM' => __( 'Bermuda' ),
			'BT' => __( 'Bhutan' ),
			'BO' => __( 'Bolivia' ),
			'BA' => __( 'Bosnia and Herzegovina' ),
			'BW' => __( 'Botswana' ),
			'BV' => __( 'Bouvet Island' ),
			'BR' => __( 'Brazil' ),
			'BQ' => __( 'British Antarctic Territory' ),
			'IO' => __( 'British Indian Ocean Territory' ),
			'VG' => __( 'British Virgin Islands' ),
			'BN' => __( 'Brunei' ),
			'BG' => __( 'Bulgaria' ),
			'BF' => __( 'Burkina Faso' ),
			'BI' => __( 'Burundi' ),
			'KH' => __( 'Cambodia' ),
			'CM' => __( 'Cameroon' ),
			'CA' => __( 'Canada' ),
			'CT' => __( 'Canton and Enderbury Islands' ),
			'CV' => __( 'Cape Verde' ),
			'KY' => __( 'Cayman Islands' ),
			'CF' => __( 'Central African Republic' ),
			'TD' => __( 'Chad' ),
			'CL' => __( 'Chile' ),
			'CN' => __( 'China' ),
			'CX' => __( 'Christmas Island' ),
			'CC' => __( 'Cocos [Keeling] Islands' ),
			'CO' => __( 'Colombia' ),
			'KM' => __( 'Comoros' ),
			'CG' => __( 'Congo - Brazzaville' ),
			'CD' => __( 'Congo - Kinshasa' ),
			'CK' => __( 'Cook Islands' ),
			'CR' => __( 'Costa Rica' ),
			'HR' => __( 'Croatia' ),
			'CU' => __( 'Cuba' ),
			'CY' => __( 'Cyprus' ),
			'CZ' => __( 'Czech Republic' ),
			'CI' => __( 'Côte d’Ivoire' ),
			'DK' => __( 'Denmark' ),
			'DJ' => __( 'Djibouti' ),
			'DM' => __( 'Dominica' ),
			'DO' => __( 'Dominican Republic' ),
			'NQ' => __( 'Dronning Maud Land' ),
			'DD' => __( 'East Germany' ),
			'EC' => __( 'Ecuador' ),
			'EG' => __( 'Egypt' ),
			'SV' => __( 'El Salvador' ),
			'GQ' => __( 'Equatorial Guinea' ),
			'ER' => __( 'Eritrea' ),
			'EE' => __( 'Estonia' ),
			'ET' => __( 'Ethiopia' ),
			'FK' => __( 'Falkland Islands' ),
			'FO' => __( 'Faroe Islands' ),
			'FJ' => __( 'Fiji' ),
			'FI' => __( 'Finland' ),
			'FR' => __( 'France' ),
			'GF' => __( 'French Guiana' ),
			'PF' => __( 'French Polynesia' ),
			'TF' => __( 'French Southern Territories' ),
			'FQ' => __( 'French Southern and Antarctic Territories' ),
			'GA' => __( 'Gabon' ),
			'GM' => __( 'Gambia' ),
			'GE' => __( 'Georgia' ),
			'DE' => __( 'Germany' ),
			'GH' => __( 'Ghana' ),
			'GI' => __( 'Gibraltar' ),
			'GR' => __( 'Greece' ),
			'GL' => __( 'Greenland' ),
			'GD' => __( 'Grenada' ),
			'GP' => __( 'Guadeloupe' ),
			'GU' => __( 'Guam' ),
			'GT' => __( 'Guatemala' ),
			'GG' => __( 'Guernsey' ),
			'GN' => __( 'Guinea' ),
			'GW' => __( 'Guinea-Bissau' ),
			'GY' => __( 'Guyana' ),
			'HT' => __( 'Haiti' ),
			'HM' => __( 'Heard Island and McDonald Islands' ),
			'HN' => __( 'Honduras' ),
			'HK' => __( 'Hong Kong SAR China' ),
			'HU' => __( 'Hungary' ),
			'IS' => __( 'Iceland' ),
			'IN' => __( 'India' ),
			'ID' => __( 'Indonesia' ),
			'IR' => __( 'Iran' ),
			'IQ' => __( 'Iraq' ),
			'IE' => __( 'Ireland' ),
			'IM' => __( 'Isle of Man' ),
			'IL' => __( 'Israel' ),
			'IT' => __( 'Italy' ),
			'JM' => __( 'Jamaica' ),
			'JP' => __( 'Japan' ),
			'JE' => __( 'Jersey' ),
			'JT' => __( 'Johnston Island' ),
			'JO' => __( 'Jordan' ),
			'KZ' => __( 'Kazakhstan' ),
			'KE' => __( 'Kenya' ),
			'KI' => __( 'Kiribati' ),
			'KW' => __( 'Kuwait' ),
			'KG' => __( 'Kyrgyzstan' ),
			'LA' => __( 'Laos' ),
			'LV' => __( 'Latvia' ),
			'LB' => __( 'Lebanon' ),
			'LS' => __( 'Lesotho' ),
			'LR' => __( 'Liberia' ),
			'LY' => __( 'Libya' ),
			'LI' => __( 'Liechtenstein' ),
			'LT' => __( 'Lithuania' ),
			'LU' => __( 'Luxembourg' ),
			'MO' => __( 'Macau SAR China' ),
			'MK' => __( 'Macedonia' ),
			'MG' => __( 'Madagascar' ),
			'MW' => __( 'Malawi' ),
			'MY' => __( 'Malaysia' ),
			'MV' => __( 'Maldives' ),
			'ML' => __( 'Mali' ),
			'MT' => __( 'Malta' ),
			'MH' => __( 'Marshall Islands' ),
			'MQ' => __( 'Martinique' ),
			'MR' => __( 'Mauritania' ),
			'MU' => __( 'Mauritius' ),
			'YT' => __( 'Mayotte' ),
			'FX' => __( 'Metropolitan France' ),
			'MX' => __( 'Mexico' ),
			'FM' => __( 'Micronesia' ),
			'MI' => __( 'Midway Islands' ),
			'MD' => __( 'Moldova' ),
			'MC' => __( 'Monaco' ),
			'MN' => __( 'Mongolia' ),
			'ME' => __( 'Montenegro' ),
			'MS' => __( 'Montserrat' ),
			'MA' => __( 'Morocco' ),
			'MZ' => __( 'Mozambique' ),
			'MM' => __( 'Myanmar [Burma]' ),
			'NA' => __( 'Namibia' ),
			'NR' => __( 'Nauru' ),
			'NP' => __( 'Nepal' ),
			'NL' => __( 'Netherlands' ),
			'AN' => __( 'Netherlands Antilles' ),
			'NT' => __( 'Neutral Zone' ),
			'NC' => __( 'New Caledonia' ),
			'NZ' => __( 'New Zealand' ),
			'NI' => __( 'Nicaragua' ),
			'NE' => __( 'Niger' ),
			'NG' => __( 'Nigeria' ),
			'NU' => __( 'Niue' ),
			'NF' => __( 'Norfolk Island' ),
			'KP' => __( 'North Korea' ),
			'VD' => __( 'North Vietnam' ),
			'MP' => __( 'Northern Mariana Islands' ),
			'NO' => __( 'Norway' ),
			'OM' => __( 'Oman' ),
			'PC' => __( 'Pacific Islands Trust Territory' ),
			'PK' => __( 'Pakistan' ),
			'PW' => __( 'Palau' ),
			'PS' => __( 'Palestinian Territories' ),
			'PA' => __( 'Panama' ),
			'PZ' => __( 'Panama Canal Zone' ),
			'PG' => __( 'Papua New Guinea' ),
			'PY' => __( 'Paraguay' ),
			'YD' => __( "People's Democratic Republic of Yemen" ),
			'PE' => __( 'Peru' ),
			'PH' => __( 'Philippines' ),
			'PN' => __( 'Pitcairn Islands' ),
			'PL' => __( 'Poland' ),
			'PT' => __( 'Portugal' ),
			'PR' => __( 'Puerto Rico' ),
			'QA' => __( 'Qatar' ),
			'RO' => __( 'Romania' ),
			'RU' => __( 'Russia' ),
			'RW' => __( 'Rwanda' ),
			'RE' => __( 'Réunion' ),
			'BL' => __( 'Saint Barthélemy' ),
			'SH' => __( 'Saint Helena' ),
			'KN' => __( 'Saint Kitts and Nevis' ),
			'LC' => __( 'Saint Lucia' ),
			'MF' => __( 'Saint Martin' ),
			'PM' => __( 'Saint Pierre and Miquelon' ),
			'VC' => __( 'Saint Vincent and the Grenadines' ),
			'WS' => __( 'Samoa' ),
			'SM' => __( 'San Marino' ),
			'SA' => __( 'Saudi Arabia' ),
			'SN' => __( 'Senegal' ),
			'RS' => __( 'Serbia' ),
			'CS' => __( 'Serbia and Montenegro' ),
			'SC' => __( 'Seychelles' ),
			'SL' => __( 'Sierra Leone' ),
			'SG' => __( 'Singapore' ),
			'SK' => __( 'Slovakia' ),
			'SI' => __( 'Slovenia' ),
			'SB' => __( 'Solomon Islands' ),
			'SO' => __( 'Somalia' ),
			'ZA' => __( 'South Africa' ),
			'GS' => __( 'South Georgia and the South Sandwich Islands' ),
			'KR' => __( 'South Korea' ),
			'ES' => __( 'Spain' ),
			'LK' => __( 'Sri Lanka' ),
			'SD' => __( 'Sudan' ),
			'SR' => __( 'Suriname' ),
			'SJ' => __( 'Svalbard and Jan Mayen' ),
			'SZ' => __( 'Swaziland' ),
			'SE' => __( 'Sweden' ),
			'CH' => __( 'Switzerland' ),
			'SY' => __( 'Syria' ),
			'ST' => __( 'São Tomé and Príncipe' ),
			'TW' => __( 'Taiwan' ),
			'TJ' => __( 'Tajikistan' ),
			'TZ' => __( 'Tanzania' ),
			'TH' => __( 'Thailand' ),
			'TL' => __( 'Timor-Leste' ),
			'TG' => __( 'Togo' ),
			'TK' => __( 'Tokelau' ),
			'TO' => __( 'Tonga' ),
			'TT' => __( 'Trinidad and Tobago' ),
			'TN' => __( 'Tunisia' ),
			'TR' => __( 'Turkey' ),
			'TM' => __( 'Turkmenistan' ),
			'TC' => __( 'Turks and Caicos Islands' ),
			'TV' => __( 'Tuvalu' ),
			'UM' => __( 'U.S. Minor Outlying Islands' ),
			'PU' => __( 'U.S. Miscellaneous Pacific Islands' ),
			'VI' => __( 'U.S. Virgin Islands' ),
			'UG' => __( 'Uganda' ),
			'UA' => __( 'Ukraine' ),
			'SU' => __( 'Union of Soviet Socialist Republics' ),
			'AE' => __( 'United Arab Emirates' ),
			'GB' => __( 'United Kingdom' ),
			'US' => __( 'United States' ),
			'ZZ' => __( 'Unknown or Invalid Region' ),
			'UY' => __( 'Uruguay' ),
			'UZ' => __( 'Uzbekistan' ),
			'VU' => __( 'Vanuatu' ),
			'VA' => __( 'Vatican City' ),
			'VE' => __( 'Venezuela' ),
			'VN' => __( 'Vietnam' ),
			'WK' => __( 'Wake Island' ),
			'WF' => __( 'Wallis and Futuna' ),
			'EH' => __( 'Western Sahara' ),
			'YE' => __( 'Yemen' ),
			'ZM' => __( 'Zambia' ),
			'ZW' => __( 'Zimbabwe' ),
			'AX' => __( 'Åland Islands' ),
		);

		return apply_filters( 'pods_form_ui_field_pick_data_countries', $data, $name, $value, $options, $pod, $id );

	}

	/**
	 * Data callback for US States.
	 *
	 * @param string|null       $name    The name of the field.
	 * @param string|array|null $value   The value of the field.
	 * @param array|null        $options Field options.
	 * @param array|null        $pod     Pod data.
	 * @param int|null          $id      Item ID.
	 *
	 * @return array
	 *
	 * @since 2.3.0
	 */
	public function data_us_states( $name = null, $value = null, $options = null, $pod = null, $id = null ) {

		$data = array(
			'AL' => __( 'Alabama' ),
			'AK' => __( 'Alaska' ),
			'AZ' => __( 'Arizona' ),
			'AR' => __( 'Arkansas' ),
			'CA' => __( 'California' ),
			'CO' => __( 'Colorado' ),
			'CT' => __( 'Connecticut' ),
			'DE' => __( 'Delaware' ),
			'DC' => __( 'District Of Columbia' ),
			'FL' => __( 'Florida' ),
			'GA' => __( 'Georgia' ),
			'HI' => __( 'Hawaii' ),
			'ID' => __( 'Idaho' ),
			'IL' => __( 'Illinois' ),
			'IN' => __( 'Indiana' ),
			'IA' => __( 'Iowa' ),
			'KS' => __( 'Kansas' ),
			'KY' => __( 'Kentucky' ),
			'LA' => __( 'Louisiana' ),
			'ME' => __( 'Maine' ),
			'MD' => __( 'Maryland' ),
			'MA' => __( 'Massachusetts' ),
			'MI' => __( 'Michigan' ),
			'MN' => __( 'Minnesota' ),
			'MS' => __( 'Mississippi' ),
			'MO' => __( 'Missouri' ),
			'MT' => __( 'Montana' ),
			'NE' => __( 'Nebraska' ),
			'NV' => __( 'Nevada' ),
			'NH' => __( 'New Hampshire' ),
			'NJ' => __( 'New Jersey' ),
			'NM' => __( 'New Mexico' ),
			'NY' => __( 'New York' ),
			'NC' => __( 'North Carolina' ),
			'ND' => __( 'North Dakota' ),
			'OH' => __( 'Ohio' ),
			'OK' => __( 'Oklahoma' ),
			'OR' => __( 'Oregon' ),
			'PA' => __( 'Pennsylvania' ),
			'RI' => __( 'Rhode Island' ),
			'SC' => __( 'South Carolina' ),
			'SD' => __( 'South Dakota' ),
			'TN' => __( 'Tennessee' ),
			'TX' => __( 'Texas' ),
			'UT' => __( 'Utah' ),
			'VT' => __( 'Vermont' ),
			'VA' => __( 'Virginia' ),
			'WA' => __( 'Washington' ),
			'WV' => __( 'West Virginia' ),
			'WI' => __( 'Wisconsin' ),
			'WY' => __( 'Wyoming' ),
		);

		return apply_filters( 'pods_form_ui_field_pick_data_us_states', $data, $name, $value, $options, $pod, $id );

	}

	/**
	 * Data callback for CA Provinces.
	 *
	 * @param string|null       $name    The name of the field.
	 * @param string|array|null $value   The value of the field.
	 * @param array|null        $options Field options.
	 * @param array|null        $pod     Pod data.
	 * @param int|null          $id      Item ID.
	 *
	 * @return array
	 *
	 * @since 2.3.0
	 */
	public function data_ca_provinces( $name = null, $value = null, $options = null, $pod = null, $id = null ) {

		$data = array(
			'AB' => __( 'Alberta' ),
			'BC' => __( 'British Columbia' ),
			'MB' => __( 'Manitoba' ),
			'NB' => __( 'New Brunswick' ),
			'NL' => __( 'Newfoundland and Labrador' ),
			'NT' => __( 'Northwest Territories' ),
			'NS' => __( 'Nova Scotia' ),
			'NU' => __( 'Nunavut' ),
			'ON' => __( 'Ontario' ),
			'PE' => __( 'Prince Edward Island' ),
			'QC' => __( 'Quebec' ),
			'SK' => __( 'Saskatchewan' ),
			'YT' => __( 'Yukon' ),
		);

		return apply_filters( 'pods_form_ui_field_pick_data_ca_provinces', $data, $name, $value, $options, $pod, $id );

	}

	/**
	 * Data callback for US States.
	 *
	 * @param string|null       $name    The name of the field.
	 * @param string|array|null $value   The value of the field.
	 * @param array|null        $options Field options.
	 * @param array|null        $pod     Pod data.
	 * @param int|null          $id      Item ID.
	 *
	 * @return array
	 *
	 * @since 2.3.0
	 */
	public function data_days_of_week( $name = null, $value = null, $options = null, $pod = null, $id = null ) {

		/**
		 * @var WP_Locale
		 */
		global $wp_locale;

		return $wp_locale->weekday;

	}

	/**
	 * Data callback for US States.
	 *
	 * @param string|null       $name    The name of the field.
	 * @param string|array|null $value   The value of the field.
	 * @param array|null        $options Field options.
	 * @param array|null        $pod     Pod data.
	 * @param int|null          $id      Item ID.
	 *
	 * @return array
	 *
	 * @since 2.3.0
	 */
	public function data_months_of_year( $name = null, $value = null, $options = null, $pod = null, $id = null ) {

		/**
		 * @var WP_Locale
		 */
		global $wp_locale;

		return $wp_locale->month;

	}

	/**
	 * Add our modal input to the form so we can track whether we're in our modal during saving or not.
	 */
	public function admin_modal_input() {

		if ( ! pods_is_modal_window() ) {
			return;
		}

		echo '<input name="pods_modal" type="hidden" value="1" />';

	}

	/**
	 * Bail to send new saved data back to our modal handler.
	 *
	 * @param int    $item_id    Item ID.
	 * @param string $item_title Item title.
	 * @param object $field_args Field arguments.
	 */
	public function admin_modal_bail( $item_id, $item_title, $field_args ) {

		$model_data = $this->build_dfv_field_item_data_recurse_item( $item_id, $item_title, $field_args );
		?>
			<script type="text/javascript">
				window.parent.jQuery( window.parent ).trigger(
					'dfv:modal:update',
					<?php echo wp_json_encode( $model_data, JSON_HEX_TAG ); ?>
				);
			</script>
		<?php

		die();

	}

	/**
	 * Bail to send new saved data back to our modal handler.
	 *
	 * @param int    $item_id    Item ID.
	 * @param string $item_title Item title.
	 * @param object $field_args Field arguments.
	 */
	public function admin_modal_bail_JSON( $item_id, $item_title, $field_args ) {

		$model_data = $this->build_dfv_field_item_data_recurse_item( $item_id, $item_title, $field_args );
		echo wp_json_encode( $model_data, JSON_HEX_TAG );

		die();
	}

	/**
	 * Bail on Post save redirect for Admin modal.
	 *
	 * @param string $location The destination URL.
	 * @param int    $post_id  The post ID.
	 *
	 * @return string
	 */
	public function admin_modal_bail_post_redirect( $location, $post_id ) {

		if ( ! pods_is_modal_window() ) {
			return $location;
		}

		$post_title = get_the_title( $post_id );

		$field_args = (object) array(
			'options' => array(
				'pick_object' => 'post_type',
				'pick_val'    => get_post_type( $post_id ),
			),
			'value'   => array(
				$post_id => $post_title,
			),
		);

		$this->admin_modal_bail( $post_id, $post_title, $field_args );

		return $location;

	}

	/**
	 * Hook into term updating process to bail on redirect.
	 */
	public function admin_modal_bail_term_action() {

		if ( ! pods_is_modal_window() ) {
			return;
		}

		add_action( 'created_term', array( $this, 'admin_modal_bail_term' ), 10, 3 );
		add_action( 'edited_term', array( $this, 'admin_modal_bail_term' ), 10, 3 );

	}

	/**
	 * Bail on Term save redirect for Admin modal.
	 *
	 * @param int    $term_id  Term ID.
	 * @param int    $tt_id    Term taxonomy ID.
	 * @param string $taxonomy Taxonomy slug.
	 */
	public function admin_modal_bail_term( $term_id, $tt_id, $taxonomy ) {

		if ( ! pods_is_modal_window() ) {
			return;
		}

		$term = get_term( $term_id );

		if ( ! $term || is_wp_error( $term ) ) {
			return;
		}

		$field_args = (object) array(
			'options' => array(
				'pick_object' => 'taxonomy',
				'pick_val'    => $term->taxonomy,
			),
			'value'   => array(
				$term->term_id => $term->name,
			),
		);

		$this->admin_modal_bail( $term->term_id, $term->name, $field_args );

	}

	/**
	 * Hook into user updating process to bail on redirect.
	 */
	public function admin_modal_bail_user_action() {

		if ( ! pods_is_modal_window() ) {
			return;
		}

		add_filter( 'wp_redirect', array( $this, 'admin_modal_bail_user_redirect' ) );

	}

	/**
	 * Bail on User save redirect for Admin modal.
	 *
	 * @param string $location The destination URL.
	 *
	 * @return string
	 */
	public function admin_modal_bail_user_redirect( $location ) {

		if ( ! pods_is_modal_window() ) {
			return $location;
		}

		global $user_id;

		$user = get_userdata( $user_id );

		if ( ! $user || is_wp_error( $user ) ) {
			return $location;
		}

		$field_args = (object) array(
			'options' => array(
				'pick_object' => 'user',
				'pick_val'    => '',
			),
			'value'   => array(
				$user->ID => $user->display_name,
			),
		);

		$this->admin_modal_bail( $user->ID, $user->display_name, $field_args );

		return $location;

	}

	/**
	 * Bail on Pod item save for Admin modal.
	 *
	 * @param int       $id     Item ID.
	 * @param array     $params save_pod_item parameters.
	 * @param null|Pods $obj    Pod object (if set).
	 */
	public function admin_modal_bail_pod( $id, $params, $obj ) {

		if ( ! pods_is_modal_window() ) {
			return;
		}

		if ( ! $obj ) {
			$obj = pods( $params['pod'] );
		}

		if ( ! $obj || ! $obj->fetch( $id ) ) {
			return;
		}

		$item_id    = $obj->id();
		$item_title = $obj->index();

		$field_args = (object) array(
			'options' => array(
				'pick_object' => $obj->pod_data['type'],
				'pick_val'    => $obj->pod,
			),
			'value'   => array(
				$obj->id() => $item_title,
			),
		);

		$this->admin_modal_bail_JSON( $item_id, $item_title, $field_args );

	}

}
