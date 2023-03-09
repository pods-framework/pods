<?php

use Pods\Static_Cache;
use Pods\Whatsit\Pod;
use Pods\Whatsit\Field;
use Pods\Whatsit\Object_Field;
use Pods\API\Whatsit\Value_Field;
use Pods\Whatsit\Store;

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

		static::$group = __( 'Relationships / Media', 'pods' );
		static::$label = __( 'Relationship', 'pods' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function admin_init() {

		// AJAX for Relationship lookups.
		add_action( 'wp_ajax_pods_relationship', array( $this, 'admin_ajax_relationship' ) );
		add_action( 'wp_ajax_nopriv_pods_relationship', array( $this, 'admin_ajax_relationship' ) );

		// Handle modal input.
		add_action( 'pods_meta_box_pre', array( $this, 'admin_modal_input' ) );
		add_action( 'edit_form_top', array( $this, 'admin_modal_input' ) );
		add_action( 'show_user_profile', array( $this, 'admin_modal_input' ) );
		add_action( 'edit_user_profile', array( $this, 'admin_modal_input' ) );

		// Hook into every taxonomy form.
		$taxonomies = get_taxonomies();

		foreach ( $taxonomies as $taxonomy ) {
			if ( $taxonomy instanceof WP_Term ) {
				$taxonomy = $taxonomy->name;
			}

			add_action( $taxonomy . '_add_form', array( $this, 'admin_modal_input' ) );
			add_action( $taxonomy . '_edit_form', array( $this, 'admin_modal_input' ) );
		}

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
		// translators: %s: is the Documentation linked text.
		$fallback_help = __( 'More details on our %s.', 'pods' );

		$fallback_help_link = sprintf(
			'<a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>',
			esc_url( 'https://docs.pods.io/fields/relationship/' ),
			__( 'Field Type Documentation', 'pods' )
		);

		$fallback_help = sprintf( $fallback_help, $fallback_help_link );

		$simple_objects = $this->simple_objects();

		$options = [
			static::$type . '_format_type'              => [
				'label'                 => __( 'Selection Type', 'pods' ),
				'help'                  => $fallback_help,
				'default'               => 'single',
				'required'              => true,
				'type'                  => 'pick',
				'data'                  => [
					'single' => __( 'Single Select', 'pods' ),
					'multi'  => __( 'Multiple Select', 'pods' ),
				],
				'pick_show_select_text' => 0,
				'dependency'            => true,
			],
			static::$type . '_format_single'            => [
				'label'                 => __( 'Input Type', 'pods' ),
				'help'                  => $fallback_help,
				'depends-on'            => [
					static::$type . '_format_type' => 'single',
				],
				'default'               => 'dropdown',
				'required'              => true,
				'type'                  => 'pick',
				'data'                  => apply_filters( 'pods_form_ui_field_pick_format_single_options', [
					'dropdown'     => __( 'Drop Down', 'pods' ),
					'radio'        => __( 'Radio Buttons', 'pods' ),
					'autocomplete' => __( 'Autocomplete', 'pods' ),
					'list'         => __( 'List View (single value)', 'pods' ),
				] ),
				'pick_show_select_text' => 0,
				'dependency'            => true,
			],
			static::$type . '_format_multi'             => [
				'label'                 => __( 'Input Type', 'pods' ),
				'help'                  => $fallback_help,
				'depends-on'            => [
					static::$type . '_format_type' => 'multi',
				],
				'default'               => 'list',
				'required'              => true,
				'type'                  => 'pick',
				'data'                  => apply_filters( 'pods_form_ui_field_pick_format_multi_options', [
					'checkbox'     => __( 'Checkboxes', 'pods' ),
					'multiselect'  => __( 'Multi Select (basic selection)', 'pods' ),
					'autocomplete' => __( 'Autocomplete', 'pods' ),
					'list'         => __( 'List View (with reordering)', 'pods' ),
				] ),
				'pick_show_select_text' => 0,
				'dependency'            => true,
			],
			static::$type . '_display_format_multi'     => [
				'label'                 => __( 'Display Format', 'pods' ),
				'help'                  => __( 'Used as format for front-end display', 'pods' ) . ' ' . $fallback_help,
				'depends-on'            => [
					static::$type . '_format_type' => 'multi',
				],
				'default'               => 'default',
				'required'              => true,
				'type'                  => 'pick',
				'data'                  => [
					'default'    => __( 'Item 1, Item 2, and Item 3', 'pods' ),
					'non_serial' => __( 'Item 1, Item 2 and Item 3', 'pods' ),
					'custom'     => __( 'Custom separator (without "and")', 'pods' ),
				],
				'pick_show_select_text' => 0,
				'dependency'            => true,
			],
			static::$type . '_display_format_separator' => [
				'label'      => __( 'Display Format Separator', 'pods' ),
				'help'       => __( 'Used as separator for front-end display. This also turns off the "and" portion of the formatting.', 'pods' ) . ' ' . $fallback_help,
				'depends-on' => [
					static::$type . '_display_format_multi' => 'custom',
					static::$type . '_format_type'          => 'multi',
				],
				'default'    => ', ',
				'type'       => 'text',
			],
			static::$type . '_allow_add_new'            => [
				'label'       => __( 'Allow Add New', 'pods' ),
				'help'        => __( 'Allow new related records to be created in a modal window', 'pods' ) . ' ' . $fallback_help,
				'wildcard-on' => [
					static::$type . '_object' => [
						'^post_type-(?!(custom_css|customize_changeset)).*$',
						//'^taxonomy-.*$', @todo We need to finish adding support for add new on term form.
						'^user$',
						'^pod-.*$',
					],
				],
				'type'        => 'boolean',
				'default'     => 1,
			],
			static::$type . '_add_new_label'            => array(
					'label'       => __( 'Add New Label', 'pods' ),
					'placeholder' => __( 'Add New', 'pods' ),
					'default'     => '',
					'type'        => 'text',
					'depends-on'  => [ static::$type . '_allow_add_new' => true ]
			),
			static::$type . '_taggable'                 => [
				'label'          => __( 'Taggable', 'pods' ),
				'help'           => __( 'Allow new values to be inserted when using an Autocomplete field', 'pods' ) . ' ' . $fallback_help,
				'depends-on-any' => [
					static::$type . '_format_single' => 'autocomplete',
					static::$type . '_format_multi'  => 'autocomplete',
				],
				'excludes-on'    => [
					static::$type . '_object'        => array_merge( [
						'site',
						'network',
					], $simple_objects ),
					static::$type . '_allow_add_new' => false,
				],
				'type'           => 'boolean',
				'default'        => 0,
			],
			static::$type . '_show_icon'                => [
				'label'          => __( 'Show Icons', 'pods' ),
				'help'           => $fallback_help,
				'depends-on-any' => [
					static::$type . '_format_single' => 'list',
					static::$type . '_format_multi'  => 'list',
				],
				'excludes-on'    => [
					static::$type . '_object' => array_merge( [ 'site', 'network' ], $simple_objects ),
				],
				'type'           => 'boolean',
				'default'        => 1,
			],
			static::$type . '_show_edit_link'           => [
				'label'          => __( 'Show Edit Links', 'pods' ),
				'help'           => $fallback_help,
				'depends-on-any' => [
					static::$type . '_format_single' => 'list',
					static::$type . '_format_multi'  => 'list',
				],
				'excludes-on'    => [
					static::$type . '_object' => array_merge( [ 'site', 'network' ], $simple_objects ),
				],
				'type'           => 'boolean',
				'default'        => 1,
			],
			static::$type . '_show_view_link'           => [
				'label'          => __( 'Show View Links', 'pods' ),
				'help'           => $fallback_help,
				'depends-on-any' => [
					static::$type . '_format_single' => 'list',
					static::$type . '_format_multi'  => 'list',
				],
				'excludes-on'    => [
					static::$type . '_object' => array_merge( [ 'site', 'network' ], $simple_objects ),
				],
				'type'           => 'boolean',
				'default'        => 1,
			],
			static::$type . '_select_text'              => [
				'label'            => __( 'Default Select Text', 'pods' ),
				'help'             => __( 'This is the text used for the default "no selection" dropdown item. If left empty, it will default to "-- Select One --"', 'pods' ) . ' ' . $fallback_help,
				'depends-on'       => [
					static::$type . '_format_type'   => 'single',
					static::$type . '_format_single' => 'dropdown',
				],
				'default'          => '',
				'text_placeholder' => __( '-- Select One --', 'pods' ),
				'type'             => 'text',
			],
			static::$type . '_limit'                    => [
				'label'      => __( 'Selection Limit', 'pods' ),
				'help'       => __( 'Default is "0" for no limit, but you can enter 1 or more to limit the number of items that can be selected.', 'pods' ) . ' ' . $fallback_help,
				'depends-on' => [
					static::$type . '_format_type' => 'multi',
				],
				'default'    => 0,
				'type'       => 'number',
			],
			static::$type . '_table_id'                 => [
				'label'      => __( 'Table ID Column', 'pods' ),
				'help'       => __( 'You must provide the ID column name for the table, this will be used to keep track of the relationship', 'pods' ) . ' ' . $fallback_help,
				'depends-on' => [
					static::$type . '_object' => 'table',
				],
				'required'   => 1,
				'default'    => '',
				'type'       => 'text',
			],
			static::$type . '_table_index'              => [
				'label'      => __( 'Table Index Column', 'pods' ),
				'help'       => __( 'You must provide the index column name for the table, this may optionally also be the ID column name', 'pods' ) . ' ' . $fallback_help,
				'depends-on' => [
					static::$type . '_object' => 'table',
				],
				'required'   => 1,
				'default'    => '',
				'type'       => 'text',
			],
			static::$type . '_display'                  => [
				'label'       => __( 'Display Field in Selection List', 'pods' ),
				'help'        => __( 'Provide the name of a field on the related object to reference, example: {@post_title}', 'pods' ) . ' ' . $fallback_help,
				'excludes-on' => [
					static::$type . '_object' => array_merge( [ 'site', 'network' ], $simple_objects ),
				],
				'default'     => '',
				'type'        => 'text',
			],
			static::$type . '_user_role'                => [
				'label'            => __( 'Limit list by Role(s)', 'pods' ),
				'help'             => __( 'You can choose to limit Users available for selection by specific role(s).', 'pods' ) . ' ' . $fallback_help,
				'default'          => '',
				'type'             => 'pick',
				'pick_object'      => 'role',
				'pick_format_type' => 'multi',
				'depends-on'       => [
					static::$type . '_object' => 'user',
				],
			],
			static::$type . '_where'                    => [
				'label'       => __( 'Customized <em>WHERE</em>', 'pods' ),
				'help'        => $fallback_help,
				'excludes-on' => [
					static::$type . '_object' => array_merge( [ 'site', 'network' ], $simple_objects ),
				],
				'default'     => '',
				'type'        => 'text',
			],
			static::$type . '_orderby'                  => [
				'label'       => __( 'Customized <em>ORDER BY</em>', 'pods' ),
				'help'        => $fallback_help,
				'excludes-on' => [
					static::$type . '_object' => array_merge( [ 'site', 'network' ], $simple_objects ),
				],
				'default'     => '',
				'type'        => 'text',
			],
			static::$type . '_groupby'                  => [
				'label'       => __( 'Customized <em>GROUP BY</em>', 'pods' ),
				'help'        => $fallback_help,
				'excludes-on' => [
					static::$type . '_object' => array_merge( [ 'site', 'network' ], $simple_objects ),
				],
				'default'     => '',
				'type'        => 'text',
			],
		];

		$post_type_pick_objects = array();

		foreach ( get_post_types( '', 'names' ) as $post_type ) {
			$post_type_pick_objects[] = 'post_type-' . $post_type;
		}

		$options[ static::$type . '_post_status' ] = [
			'label'            => __( 'Limit list by Post Status', 'pods' ),
			'help'             => __( 'You can choose to limit Posts available for selection by one or more specific post status.', 'pods' ),
			'type'             => 'pick',
			'pick_object'      => 'post-status-with-any',
			'pick_format_type' => 'multi',
			'default'          => 'publish',
			'depends-on'       => [
				static::$type . '_object' => $post_type_pick_objects,
			],
		];

		$options[ static::$type . '_post_author' ] = [
			'label'      => __( 'Limit list to the same Post Author', 'pods' ),
			'help'       => __( 'You can choose to limit Posts available for selection to those created by the same Post Author. This only works if this pod is a Post Type and this field is related to a Post Type.', 'pods' ),
			'type'       => 'boolean',
			'default'    => 0,
			'depends-on' => [
				// @todo Support being able to depend on the current pod type like _pod_type => post_type or something.
				static::$type . '_object' => $post_type_pick_objects,
			],
		];

		return $options;

	}

	/**
	 * {@inheritdoc}
	 */
	public function prepare( $options = null ) {
		$format = static::$prepare;

		// Maybe use number format for storage if not a simple relationship and limit is one.
		if ( $options instanceof Field && ! $options->is_simple_relationship() && 1 === $options->get_limit() ) {
			$format = '%d';
		}

		return $format;
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

		if ( $related_object['data_callback'] instanceof Closure ) {
			return pods_error( 'Pods does not support closures for data callbacks' );
		}

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

			/**
			 * Allow filtering the list of Pods to show in the list of relationship objects.
			 *
			 * @since 2.8.0
			 *
			 * @param array $pod_options List of Pods to show in the list of relationship objects.
			 */
			$pod_options = apply_filters( 'pods_field_pick_setup_related_objects_pods', $pod_options );

			asort( $pod_options );

			foreach ( $pod_options as $pod => $label ) {
				self::$related_objects[ 'pod-' . $pod ] = array(
					'label'         => $label,
					'group'         => __( 'Advanced Content Types', 'pods' ),
					'bidirectional' => true,
				);
			}

			/**
			 * Prevent ability to extend core Pods content types.
			 *
			 * @param bool $ignore_internal Default is true, when set to false Pods internal content types can not be extended.
			 *
			 * @since 2.3.19
			 */
			$ignore_internal = apply_filters( 'pods_pick_ignore_internal', true );

			$pods_meta = pods_meta();

			// Public Post Types for relationships.
			$post_types = get_post_types( [ 'public' => true ] );
			asort( $post_types );

			foreach ( $post_types as $post_type => $label ) {
				if ( empty( $post_type ) || 'attachment' === $post_type || ! $pods_meta->is_type_covered( 'post_type', $post_type ) ) {
					unset( $post_types[ $post_type ] );

					continue;
				} elseif ( $ignore_internal && 0 === strpos( $post_type, '_pods_' ) ) {
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

			// Post Types for relationships.
			$post_types = get_post_types( [ 'public' => false ] );
			asort( $post_types );

			foreach ( $post_types as $post_type => $label ) {
				if ( empty( $post_type ) || 'attachment' === $post_type || ! $pods_meta->is_type_covered( 'post_type', $post_type ) ) {
					unset( $post_types[ $post_type ] );

					continue;
				} elseif ( $ignore_internal && 0 === strpos( $post_type, '_pods_' ) ) {
					unset( $post_types[ $post_type ] );

					continue;
				}

				$post_type = get_post_type_object( $post_type );

				self::$related_objects[ 'post_type-' . $post_type->name ] = array(
					'label'         => $post_type->label . ' (' . $post_type->name . ')',
					'group'         => __( 'Post Types (Private)', 'pods' ),
					'bidirectional' => true,
				);
			}

			// Taxonomies for relationships.
			$taxonomies = get_taxonomies();
			asort( $taxonomies );

			foreach ( $taxonomies as $taxonomy => $label ) {
				if ( empty( $taxonomy ) || ! $pods_meta->is_type_covered( 'taxonomy', $taxonomy ) ) {
					unset( $taxonomies[ $taxonomy ] );

					continue;
				} elseif ( $ignore_internal && 0 === strpos( $taxonomy, '_pods_' ) ) {
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

			self::$related_objects['post-status-with-any'] = array(
				'label'         => __( 'Post Status (with any)', 'pods' ),
				'group'         => __( 'Other WP Objects', 'pods' ),
				'simple'        => true,
				'data_callback' => array( $this, 'data_post_stati_with_any' ),
			);

			self::$related_objects['post-types'] = [
				'label'         => __( 'Post Type Objects', 'pods' ),
				'group'         => __( 'Other WP Objects', 'pods' ),
				'simple'        => true,
				'data_callback' => [ $this, 'data_post_types' ],
			];

			self::$related_objects['taxonomies'] = [
				'label'         => __( 'Taxonomy Objects', 'pods' ),
				'group'         => __( 'Other WP Objects', 'pods' ),
				'simple'        => true,
				'data_callback' => [ $this, 'data_taxonomies' ],
			];

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
				pods_transient_set( 'pods_related_objects', self::$related_objects, WEEK_IN_SECONDS );
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

		if ( $new_data_loaded ) {
			/**
			 * Allow hooking in when new data has been loaded.
			 *
			 * @since 2.8.0
			 */
			do_action( 'pods_form_ui_field_pick_related_objects_new_data_loaded' );
		}

		return true;

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
		if ( null !== self::$names_related ) {
			return self::$names_related;
		}

		$this->setup_related_objects( $force );

		$related_objects = array();

		foreach ( self::$related_objects as $related_object_name => $related_object ) {
			if ( ! isset( $related_objects[ $related_object['group'] ] ) ) {
				$related_objects[ $related_object['group'] ] = array();
			}

			$related_objects[ $related_object['group'] ][ $related_object_name ] = $related_object['label'];
		}

		self::$names_related = (array) apply_filters( 'pods_form_ui_field_pick_related_objects', $related_objects );

		return self::$names_related;
	}

	/**
	 * Return available simple object names
	 *
	 * @return array Simple object names
	 * @since 2.3.0
	 */
	public function simple_objects() {
		if ( null !== self::$names_simple ) {
			return self::$names_simple;
		}

		$this->setup_related_objects();

		$simple_objects = array();

		foreach ( self::$related_objects as $object => $related_object ) {
			if ( ! isset( $related_object['simple'] ) || ! $related_object['simple'] ) {
				continue;
			}

			$simple_objects[] = $object;
		}

		self::$names_simple = (array) apply_filters( 'pods_form_ui_field_pick_simple_objects', $simple_objects );

		return self::$names_simple;
	}

	/**
	 * Return available bidirectional object names
	 *
	 * @return array Bidirectional object names
	 * @since 2.3.4
	 */
	public function bidirectional_objects() {
		if ( null !== self::$names_bidirectional ) {
			return self::$names_bidirectional;
		}

		$this->setup_related_objects();

		$bidirectional_objects = array();

		foreach ( self::$related_objects as $object => $related_object ) {
			if ( ! isset( $related_object['bidirectional'] ) || ! $related_object['bidirectional'] ) {
				continue;
			}

			$bidirectional_objects[] = $object;
		}

		self::$names_bidirectional = (array) apply_filters( 'pods_form_ui_field_pick_bidirectional_objects', $bidirectional_objects );

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
			$fields = pods_config_get_all_fields( $pod );
		} elseif ( is_array( $pod ) && isset( $pod['fields'] ) ) {
			$fields = pods_config_get_all_fields( $pod );
		}

		$args = array(
			'field'  => $name,
			'fields' => $fields,
		);

		$display_format = pods_v( static::$type . '_display_format_multi', $options, 'default' );

		if ( 'non_serial' === $display_format ) {
			$args['serial'] = false;
		}

		if ( 'custom' === $display_format ) {
			$args['serial'] = false;

			$separator = pods_v( static::$type . '_display_format_separator', $options, ', ' );
			if ( ! empty( $separator ) ) {
				$args['separator'] = $separator;

				// Replicate separator behavior.
				$args['and'] = $args['separator'];
			}
		}

		return pods_serial_comma( $value, $args );
	}

	/**
	 * {@inheritdoc}
	 */
	public function input( $name, $value = null, $options = null, $pod = null, $id = null ) {
		$options = ( is_array( $options ) || is_object( $options ) ) ? $options : (array) $options;

		// Do anything we need to do here with options setup / enforcement.

		// Default labels.
		if ( empty( $options[ static::$type . '_add_new_label' ] ) ) {
			$options[ static::$type . '_add_new_label' ] = __( 'Add New', 'pods' );
		}

		parent::input( $name, $value, $options, $pod, $id );
	}

	/**
	 * {@inheritdoc}
	 */
	public function build_dfv_field_options( $options, $args ) {
		// Use field object if it was provided.
		if ( isset( $options['_field_object'] ) ) {
			$field = $options['_field_object'];

			unset( $options['_field_object'] );

			$options = pods_config_merge_data( $field, $options );
		}

		$field_options = $options instanceof \Pods\Whatsit ? $options->export() : $options;

		if ( ! isset( $field_options['id'] ) ) {
			$field_options['id'] = 0;
		}

		// Enforce defaults.
		$all_options = static::options();

		foreach ( $all_options as $option_name => $option ) {
			$default = pods_v( 'default', $option, '' );

			$field_options[ $option_name ] = pods_v( $option_name, $field_options, $default );

			if ( '' === $field_options[ $option_name ] ) {
				$field_options[ $option_name ] = $default;
			}
		}

		$field_options['grouped'] = 1;

		if ( empty( $field_options[ $args->type . '_object' ] ) ) {
			$field_options[ $args->type . '_object' ] = '';
		}

		if ( empty( $field_options[ $args->type . '_val' ] ) ) {
			$field_options[ $args->type . '_val' ] = '';
		}

		// Unset the table info for now.
		$field_options['table_info'] = [];

		$custom = pods_v( $args->type . '_custom', $field_options, false );

		$custom = apply_filters( 'pods_form_ui_field_pick_custom_values', $custom, $args->name, $args->value, $field_options, $args->pod, $args->id );

		$ajax = false;

		if ( $this->can_ajax( $args->type, $field_options ) ) {
			$ajax = true;

			$field_data = pods_static_cache_get( $field_options['name'] . '/' . $field_options['id'], __CLASS__ . '/field_data' ) ?: [];

			if ( isset( $field_data['autocomplete'] ) ) {
				$ajax = (boolean) $field_data['autocomplete'];
			}
		}

		$ajax = apply_filters( 'pods_form_ui_field_pick_ajax', $ajax, $args->name, $args->value, $field_options, $args->pod, $args->id );

		if ( 0 === (int) pods_v( $args->type . '_ajax', $field_options, 1 ) ) {
			$ajax = false;
		}

		$field_options[ $args->type . '_ajax' ] = (int) $ajax;

		$format_type = pods_v( $args->type . '_format_type', $field_options, 'single', true );

		$limit = 1;

		if ( 'single' === $format_type ) {
			$format_single = pods_v( $args->type . '_format_single', $field_options, 'dropdown', true );

			if ( 'dropdown' === $format_single ) {
				$field_options['view_name'] = 'select';
			} elseif ( 'radio' === $format_single ) {
				$field_options['view_name'] = 'radio';
			} elseif ( 'autocomplete' === $format_single ) {
				$field_options['view_name'] = 'select2';
			} elseif ( 'list' === $format_single ) {
				$field_options['view_name'] = 'list';
			} else {
				$field_options['view_name'] = $format_single;
			}
		} elseif ( 'multi' === $format_type ) {
			$format_multi = pods_v( $args->type . '_format_multi', $field_options, 'checkbox', true );

			if ( ! empty( $args->value ) && ! is_array( $args->value ) ) {
				$args->value = explode( ',', $args->value );
			}

			if ( 'checkbox' === $format_multi ) {
				$field_options['view_name'] = 'checkbox';
			} elseif ( 'multiselect' === $format_multi ) {
				$field_options['view_name'] = 'select';
			} elseif ( 'autocomplete' === $format_multi ) {
				$field_options['view_name'] = 'select2';
			} elseif ( 'list' === $format_multi ) {
				$field_options['view_name'] = 'list';
			} else {
				$field_options['view_name'] = $format_multi;
			}

			$limit = 0;

			if ( ! empty( $field_options[ $args->type . '_limit' ] ) ) {
				$limit = absint( $field_options[ $args->type . '_limit' ] );
			}
		} else {
			$field_options['view_name'] = $format_type;
		}

		$field_options[ $args->type . '_limit' ] = $limit;

		$field_options['ajax_data'] = $this->build_dfv_autocomplete_ajax_data( $field_options, $args, $ajax );
		$field_options['select2_overrides'] = null;

		if ( 'select2' === $field_options['view_name'] ) {
			// @todo Revisit this, they probably aren't used anymore now since this is DFV.
			wp_enqueue_style( 'pods-select2' );
			wp_enqueue_script( 'pods-select2' );

			/**
			 * Allow overriding some Select2/SelectWoo options used in the JS init.
			 *
			 * @since 2.7.0
			 *
			 * @param array|null $select2_overrides Override options for Select2/SelectWoo.
			 */
			$field_options['select2_overrides'] = apply_filters( 'pods_pick_select2_overrides', $field_options['select2_overrides'] );
		}

		return $field_options;
	}

	/**
	 * Build DFV autocomplete AJAX data.
	 *
	 * @param array|Field  $options DFV options.
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

		if ( is_array( $args->pod ) ) {
			$pod_name = $args->pod['name'];
		} elseif ( $args->pod instanceof Pods ) {
			$pod_name = $args->pod->pod;
		} elseif ( $args->pod instanceof Pod ) {
			$pod_name = $args->pod->get_name();
		} else {
			$pod_name = '';
		}

		if ( is_array( $options ) ) {
			$field_name = pods_v( 'name', $options );
		} elseif ( $options instanceof Field ) {
			$field_name = $options->get_name();
		} else {
			$field_name = '';
		}

		$id = (int) $args->id;

		if ( is_user_logged_in() ) {
			$uid = 'user_' . get_current_user_id();
		} else {
			$uid = pods_session_id();
		}

		$uri_hash = wp_create_nonce( 'pods_uri_' . $_SERVER['REQUEST_URI'] );

		$nonce_name  = 'pods_relationship:' . json_encode( compact( 'pod_name', 'field_name', 'uid', 'uri_hash', 'id' ) );
		$field_nonce = wp_create_nonce( $nonce_name );

		// Values can be overridden via the `pods_field_dfv_data` filter in $data['fieldConfig']['ajax_data'].
		return [
			'ajax'                 => $ajax,
			'delay'                => 300,
			'minimum_input_length' => 1,
			'pod_name'             => $pod_name,
			'field_name'           => $field_name,
			'id'                   => $id,
			'uri_hash'             => $uri_hash,
			'_wpnonce'             => $field_nonce,
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function build_dfv_field_config( $args ) {
		$config = parent::build_dfv_field_config( $args );

		// Ensure data is passed in for relationship fields.
		if ( ! isset( $config['data'] ) && ! empty( $args->options['data'] ) ) {
			$config['data'] = $this->get_raw_data( $args->options );
		}

		// Default optgroup handling to off.
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

		$config[ $args->type . '_taggable' ]  = filter_var( pods_v( $args->type . '_taggable', $config ), FILTER_VALIDATE_BOOLEAN );
		$config[ $args->type . '_allow_add_new' ]  = filter_var( pods_v( $args->type . '_allow_add_new', $config ), FILTER_VALIDATE_BOOLEAN );
		$config[ $args->type . '_show_edit_link' ] = filter_var( pods_v( $args->type . '_show_edit_link', $config ), FILTER_VALIDATE_BOOLEAN );

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

		$item_data = [];
		$data      = [];

		if ( ! empty( $args->options['data'] ) ) {
			$data = $this->get_raw_data( $args->options );
		} elseif ( ! empty( $args->data ) ) {
			$data = $args->data;
		}

		if ( [] !== $data ) {
			$item_data = $this->build_dfv_field_item_data_recurse( $data, $args );
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
			$new_item_data = [];

			foreach ( $args->value as $value_key => $value_item ) {
				if ( ! is_int( $value_key ) ) {
					if ( isset( $item_data[ $value_key ] ) ) {
						$value_item = $item_data[ $value_key ];
					}

					$new_item_data[ $value_key ] = $value_item;
				}
			}

			$item_data = array_merge( $new_item_data, $item_data );
		}

		// Convert from associative to numeric array
		return array_values( $item_data );

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
				$args->options['supports_thumbnails'] = true;

				$icon     = 'dashicons-admin-users';
				$img_icon = get_avatar_url( $item_id, array( 'size' => 150 ) );

				$edit_link = get_edit_user_link( $item_id );

				$link = get_author_posts_url( $item_id );

				break;

			case 'comment':
				$args->options['supports_thumbnails'] = true;

				$icon     = 'dashicons-admin-comments';
				$img_icon = get_avatar_url( get_comment( $item_id ), array( 'size' => 150 ) );

				$edit_link = get_edit_comment_link( $item_id );

				$link = get_comment_link( $item_id );

				break;

			case 'pod':
				if ( ! empty( $args->options['pick_val'] ) ) {

					$icon = pods_svg_icon( 'pods' );

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
			$icon = '';
		} elseif ( 0 === strpos( $icon, 'dashicons-' ) ) {
			$icon = sanitize_html_class( $icon );
		}

		// #5740 Check for WP_Error object.
		if ( ! is_string( $link ) ) {
			$link = '';
		}

		// Support modal editing
		if ( ! empty( $edit_link ) ) {
			// @todo: Replace string literal with defined constant
			$edit_link = add_query_arg( array( 'pods_modal' => '1' ), $edit_link );
		}

		// Determine if this is a selected item.
		// Issue history for setting selected: #4753, #4892, #5014.
		$selected = false;

		$values = array();

		// If we have values, let's cast them.
		if ( isset( $args->value ) ) {
			// The value may be a single non-array value.
			$values = (array) $args->value;
		}

		// Cast values in array as string.
		$values = array_map( static function( $value ) {
			if ( ! is_scalar( $value ) ) {
				return $value;
			}

			return (string) $value;
		}, $values );

		// If the value array has keys as IDs, let's check for matches from the keys first.
		if ( ! isset( $values[0] ) ) {
			// Get values from keys.
			$key_values = array_keys( $values );

			// Cast key values in array as string.
			$key_values = array_map( static function( $value ) {
				if ( ! is_scalar( $value ) ) {
					return $value;
				}

				return (string) $value;
			}, $key_values );

			// Let's check to see if the current $item_id matches any key values.
			if ( in_array( (string) $item_id, $key_values, true ) ) {
				$selected = true;
			}
		}

		// If we do not have a key match, the normal values may still match.
		if ( ! $selected ) {
			// Let's check to see if the current $item_id matches any values.
			if ( in_array( (string) $item_id, $values, true ) ) {
				$selected = true;
			}
		}

		$item = array(
			'id'        => html_entity_decode( esc_html( $item_id ) ),
			'icon'      => esc_attr( $icon ),
			'name'      => wp_strip_all_tags( html_entity_decode( $item_title ) ),
			'edit_link' => html_entity_decode( esc_url( $edit_link ) ),
			'link'      => html_entity_decode( esc_url( $link ) ),
			'selected'  => $selected,
		);

		return $item;

	}

	/**
	 * {@inheritdoc}
	 */
	public function validate( $value, $name = null, $options = null, $fields = null, $pod = null, $id = null, $params = null ) {
		$validate = parent::validate( $value, $name, $options, $fields, $pod, $id, $params );

		$errors = array();

		if ( is_array( $validate ) ) {
			$errors = $validate;
		}

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

		$related_sister_id = pods_v( 'sister_id', $options, 0 );

		if ( is_numeric( $related_sister_id ) ) {
			$related_sister_id = (int) $related_sister_id;
		} else {
			$related_sister_id = 0;
		}

		$options['id'] = (int) $options['id'];

		$related_data = pods_static_cache_get( $options['name'] . '/' . $options['id'], __CLASS__ . '/related_data' ) ?: [];

		if ( ! empty( $related_sister_id ) && ! in_array( $related_object, $simple_tableless_objects, true ) ) {
			$related_pod = self::$api->load_pod( [
				'name'       => $related_val,
				'auto_setup' => true,
			] );

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
					$current_ids = self::$api->lookup_related_items( $options['id'], $pod['id'], $id, $options, $pod );

					$related_data[ 'current_ids_' . $id ] = $current_ids;

					$value_ids = $value;

					// Convert values from a comma-separated string into an array.
					if ( ! is_array( $value_ids ) ) {
						$value_ids = explode( ',', $value_ids );
					}

					$value_ids = array_unique( array_filter( $value_ids ) );

					// Get ids to remove.
					$remove_ids = array_diff( $current_ids, $value_ids );

					$related_data[ 'remove_ids_' . $id ] = $remove_ids;

					$related_required   = (boolean) pods_v( 'required', $related_field, 0 );
					$related_pick_limit = (int) pods_v( static::$type . '_limit', $related_field, 0 );

					if ( 'single' === pods_v( static::$type . '_format_type', $related_field ) ) {
						$related_pick_limit = 1;
					}

					// Validate Required fields.
					if ( $related_required && ! empty( $remove_ids ) ) {
						foreach ( $remove_ids as $related_id ) {
							$bidirectional_ids = self::$api->lookup_related_items( $related_field['id'], $related_pod['id'], $related_id, $related_field, $related_pod );

							$related_data[ 'related_ids_' . $related_id ] = $bidirectional_ids;

							if ( empty( $bidirectional_ids ) || ( in_array( (int) $id, $bidirectional_ids, true ) && 1 === count( $bidirectional_ids ) ) ) {
								// Translators: %1$s and %2$s stand for field labels.
								$errors[] = sprintf( esc_html__( 'The %1$s field is required and cannot be removed by the %2$s field', 'pods' ), $related_field['label'], $options['label'] );
								return $errors;
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

		if ( ! empty( $related_data ) ) {
			$related_data['related_pod']        = $related_pod;
			$related_data['related_field']      = $related_field;
			$related_data['related_pick_limit'] = $related_pick_limit;

			pods_static_cache_set( $options['name'] . '/' . $options['id'], $related_data, __CLASS__ . '/related_data' );

			$pick_limit = (int) pods_v( static::$type . '_limit', $options, 0 );

			if ( 'single' === pods_v( static::$type . '_format_type', $options ) ) {
				$pick_limit = 1;
			}

			$related_field['id'] = (int) $related_field['id'];

			$bidirectional_related_data = pods_static_cache_get( $related_field['name'] . '/' . $related_field['id'], __CLASS__ . '/related_data' ) ?: [];

			if ( empty( $bidirectional_related_data ) ) {
				$bidirectional_related_data = [
					'related_pod'        => $pod,
					'related_field'      => $options,
					'related_pick_limit' => $pick_limit,
				];

				pods_static_cache_set( $related_field['name'] . '/' . $related_field['id'], $bidirectional_related_data, __CLASS__ . '/related_data' );
			}
		}//end if

		if ( ! empty( $errors ) ) {
			return $errors;
		}

		return $validate;

	}

	/**
	 * {@inheritdoc}
	 */
	public function save( $value, $id = null, $name = null, $options = null, $fields = null, $pod = null, $params = null ) {

		if ( empty( self::$api ) ) {
			self::$api = pods_api();
		}

		$options['id'] = (int) $options['id'];

		$related_pod        = null;
		$related_field      = null;
		$related_pick_limit = 0;
		$current_ids        = [];
		$remove_ids         = [];

		if ( null === $value ) {
			$value = [];
		} elseif ( ! is_array( $value ) ) {
			$value = [
				$value,
			];
		}

		$value_ids = array_unique( array_filter( $value ) );

		$related_data = pods_static_cache_get( $options['name'] . '/' . $options['id'], __CLASS__ . '/related_data' ) ?: [];

		if ( ! empty( $related_data ) && isset( $related_data['current_ids_' . $id ], $related_data['remove_ids_' . $id ] ) ) {
			$related_pod        = $related_data['related_pod'];
			$related_field      = $related_data['related_field'];
			$related_pick_limit = $related_data['related_pick_limit'];
			$current_ids        = $related_data[ 'current_ids_' . $id ];
			$remove_ids         = $related_data[ 'remove_ids_' . $id ];
		} elseif ( $options instanceof Field || $options instanceof Value_Field ) {
			$related_field = $options->get_bidirectional_field();

			if ( ! $related_field ) {
				return;
			}

			$related_pod        = $related_field->get_parent_object();
			$related_pick_limit = $related_field->get_limit();
			$current_ids        = self::$api->lookup_related_items( $options['id'], $pod['id'], $id, $options, $pod );

			// Get ids to remove.
			$remove_ids = array_diff( $current_ids, $value_ids );
		}

		if ( empty( $related_field ) || empty( $related_pod ) ) {
			return;
		}

		// Handle the bi-directional relationship updates.

		$no_conflict = true;

		// Only check no conflict mode if this isn't the current pod type.
		if ( $related_pod['type'] !== $pod['type'] ) {
			$no_conflict = pods_no_conflict_check( $related_pod['type'] );
		}

		if ( ! $no_conflict ) {
			pods_no_conflict_on( $related_pod['type'] );
		}

		if ( empty( $value_ids ) ) {
			// Remove all bidirectional relationships.
			if ( ! empty( $remove_ids ) ) {
				self::$api->delete_relationships( $remove_ids, $id, $related_pod, $related_field );
				self::$api->delete_relationships( $id, $remove_ids, $pod, $options );
			}

			if ( ! $no_conflict ) {
				pods_no_conflict_off( $related_pod['type'] );
			}

			return;
		}

		foreach ( $value_ids as $related_id ) {
			if ( ! empty( $related_data[ 'related_ids_' . $related_id ] ) ) {
				$bidirectional_ids = $related_data[ 'related_ids_' . $related_id ];
			} else {
				$bidirectional_ids = self::$api->lookup_related_items( $related_field['id'], $related_pod['id'], $related_id, $related_field, $related_pod );
			}

			$bidirectional_ids = array_filter( $bidirectional_ids );

			if ( empty( $bidirectional_ids ) ) {
				$bidirectional_ids = array();
			}

			$bidirectional_remove_ids = array();

			if ( 0 < $related_pick_limit && ! empty( $bidirectional_ids ) && ! in_array( $id, $bidirectional_ids, true ) ) {
				$total_bidirectional_ids = count( $bidirectional_ids );

				while ( $related_pick_limit <= $total_bidirectional_ids ) {
					$bidirectional_remove_ids[] = (int) array_pop( $bidirectional_ids );

					$total_bidirectional_ids = count( $bidirectional_ids );
				}
			}

			// Remove this item from related items no longer related to.
			$bidirectional_remove_ids = array_unique( array_filter( $bidirectional_remove_ids ) );

			if ( ! in_array( $id, $bidirectional_ids, true ) ) {
				// Add to related items.
				$bidirectional_ids[] = $id;
			} elseif ( empty( $remove_ids ) ) {
				// Nothing to change.
				continue;
			}

			self::$api->save_relationships( $related_id, $bidirectional_ids, $related_pod, $related_field );

			if ( ! empty( $bidirectional_remove_ids ) ) {
				self::$api->delete_relationships( $bidirectional_remove_ids, $related_id, $pod, $options );
			}
		}//end foreach

		if ( ! $no_conflict ) {
			pods_no_conflict_off( $related_pod['type'] );
		}
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
		$related_sister_id = pods_v( 'sister_id', $options, 0 );

		if ( is_numeric( $related_sister_id ) ) {
			$related_sister_id = (int) $related_sister_id;
		} else {
			$related_sister_id = 0;
		}

		if ( ! empty( $related_sister_id ) && ! in_array( $related_object, $simple_tableless_objects, true ) ) {
			$related_pod = self::$api->load_pod( [
				'name'       => $related_val,
				'auto_setup' => true,
			] );

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
	 * Get the raw data from the field data provided.
	 *
	 * @since 2.9.9
	 *
	 * @param array|Field $field The field data.
	 *
	 * @return array|mixed
	 */
	public function get_raw_data( $field ) {
		$data = pods_v( 'data', $field, null, true );

		if ( null !== $data ) {
			// Support late-initializing the data from a callback function passed in.
			if ( ! is_array( $data ) && ! is_string( $data ) && is_callable( $data ) ) {
				$data = $data();
			}

			$data = (array) $data;
		}

		return $data;
	}

	/**
	 * {@inheritdoc}
	 */
	public function data( $name, $value = null, $options = null, $pod = null, $id = null, $in_form = true ) {
		$data = $this->get_raw_data( $options );

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
			$data = (array) $this->get_object_data( $object_params );
		}

		/**
		 * Allow filtering whether to always show default select text for the Single select Dropdown field even if
		 * the field is required.
		 *
		 * @since 2.8.9
		 *
		 * @param bool            $always_show_default_select_text Whether to always show default select text.
		 * @param array           $data                            The object data.
		 * @param string|null     $name                            Field name.
		 * @param mixed|null      $value                           Current value.
		 * @param array|null      $options                         Field options.
		 * @param array|null      $pod                             Pod information.
		 * @param int|string|null $id                              Current item ID.
		 */
		$always_show_default_select_text = (bool) apply_filters( 'pods_field_pick_always_show_default_select_text', false, $data, $name, $value, $options, $pod, $id );

		if (
			'single' === pods_v( static::$type . '_format_type', $options, 'single' )
			&& 'dropdown' === pods_v( static::$type . '_format_single', $options, 'dropdown' )
			//&& 0 !== (int) pods_v( static::$type . '_show_select_text', $options, 1 )
			&& (
				$always_show_default_select_text
				|| empty( $value )
				|| 0 === (int) pods_v( 'required', $options, 0 )
			)
		) {
			$default_select = [
				'' => pods_v( static::$type . '_select_text', $options, __( '-- Select One --', 'pods' ), true ),
			];

			// Unset to prevent conflict.
			if ( isset( $data[''] ) ) {
				unset( $data[''] );
			}

			// Prevent resetting the numeric ID keys when adding the empty option via array_merge, use union instead.
			$data = $default_select + $data;
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

		if ( in_array( pods_v( static::$type . '_object', $options ), $this->simple_objects(), true ) ) {
			if ( ! is_array( $value ) && 0 < strlen( $value ) ) {
				$simple = @json_decode( $value, true );

				if ( is_array( $simple ) ) {
					$value = (array) $simple;
				}
			}

			$data = $this->get_raw_data( $options );

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

			if ( null !== $data ) {
				$data = (array) $data;
			} else {
				$data = (array) $this->get_object_data( $object_params );
			}

			$key = 0;

			if ( is_array( $value ) ) {
				if ( ! empty( $data ) ) {
					$val = array();

					foreach ( $value as $k => $v ) {
						if ( is_scalar( $v ) && isset( $data[ $v ] ) ) {
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
		$data = $this->get_raw_data( $options );

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
			$data = (array) $this->get_object_data( $object_params );
		}

		$labels = array();

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
	 * @param array        $deprecated       Field options array overrides.
	 * @param array        $object_params Additional get_object_data options.
	 *
	 * @return array An array of available items from a relationship field
	 */
	public function get_field_data( $field, $deprecated = null, $object_params = array() ) {
		$options = array();

		$is_field_object = $field instanceof Field;

		if ( is_array( $field ) || $is_field_object ) {
			$options = $field;
		}

		// Get field name from array.
		$field = pods_v( 'name', $options, $field, true );

		// Field name or options not set.
		if ( empty( $field ) || empty( $options ) ) {
			return array();
		}

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
		$data = $this->get_raw_data( $options );

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

		/**
		 * Overwrite parameters used by PodsField_Pick::get_object_data.
		 *
		 * @since 2.7.21
		 *
		 * @param array  $object_params       {
		 *     Get object parameters
		 *
		 *     @type string     $name        Field name.
		 *     @type mixed      $value       Current value.
		 *     @type array      $options     Field options.
		 *     @type array      $pod         Pod data.
		 *     @type int|string $id          Current item ID.
		 *     @type string     $context     Data context.
		 *     @type array      $data_params Data parameters.
		 *     @type int        $page        Page number of results to get.
		 *     @type int        $limit       How many data items to limit to (autocomplete defaults to 30, set to -1 or 1+ to override).
		 * }
		 */
		$object_params = apply_filters( 'pods_field_pick_object_data_params', $object_params );

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

		// Use field object if it was provided.
		if ( isset( $options['_field_object'] ) ) {
			$options = $options['_field_object'];

			$name = $options->get_name();
		}

		$data  = apply_filters( 'pods_field_pick_object_data', null, $name, $value, $options, $pod, $id, $object_params );
		$items = array();

		if ( ! isset( $options[ static::$type . '_object' ] ) ) {
			$data = $this->get_raw_data( $options );
		}

		$simple = false;

		if ( null === $data ) {
			$data = array();

			$pick_object = pods_v( static::$type . '_object', $options, null, true );

			// No pick object means this has no configuration to work from.
			if ( empty( $pick_object ) ) {
				return $data;
			}

			if ( 'custom-simple' === $pick_object ) {
				$custom = pods_v( static::$type . '_custom', $options, '' );

				$custom = apply_filters( 'pods_form_ui_field_pick_custom_values', $custom, $name, $value, $options, $pod, $id, $object_params );

				if ( ! empty( $custom ) ) {
					if ( ! is_array( $custom ) ) {
						$data = array();

						$custom = explode( "\n", trim( $custom ) );

						foreach ( $custom as $custom_value ) {
							$custom_value = trim( trim( $custom_value, '|' ) );
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
			} elseif (
				$pick_object
				&& $this->setup_related_objects()
				&& isset( self::$related_objects[ $pick_object ] )
				&& ! empty( self::$related_objects[$pick_object ]['data'] )
			) {
				$data = self::$related_objects[ $options[ static::$type . '_object' ] ]['data'];

				$simple = true;
			} elseif (
				$pick_object
				&& $this->setup_related_objects()
				&& isset( self::$related_objects[ $pick_object ] )
				&& isset( self::$related_objects[ $pick_object ]['data_callback'] )
				&& is_callable( self::$related_objects[ $pick_object ]['data_callback'] )
			) {
				$data = call_user_func_array(
					self::$related_objects[ $options[ static::$type . '_object' ] ]['data_callback'], [
						$name,
						$value,
						$options,
						$pod,
						$id,
					]
				);

				if ( 'data' === $context ) {
					pods_static_cache_set( $name . '/' . $options['id'], [
						'autocomplete' => false,
					], __CLASS__ . '/field_data' );
				}

				$simple = true;

				// Cache data from callback.
				if ( ! empty( $data ) ) {
					self::$related_objects[ $options[ static::$type . '_object' ] ]['data'] = $data;
				}
			} elseif ( 'simple_value' !== $context ) {
				$pick_val = pods_v( static::$type . '_val', $options );

				if ( 'table' === $pick_object ) {
					$pick_val = pods_v( static::$type . '_table', $options, $pick_val, true );
				}

				$current_pod = null;

				if ( $pod instanceof Pod ) {
					$current_pod = $pod;
				} elseif ( $pod instanceof Pods ) {
					$current_pod = $pod->pod_data;
				} elseif ( is_array( $pod ) ) {
					$current_pod = $pod;
				}

				$related_pod = null;

				if ( '__current__' === $pick_val ) {
					if ( $pod instanceof Pod ) {
						$pick_val = $pod->get_name();

						$related_pod = $pod;
					} elseif ( $pod instanceof Pods ) {
						$pick_val = $pod->pod_data->get_name();

						$related_pod = $pod->pod_data;
					} elseif ( is_array( $pod ) ) {
						$pick_val = $pod['name'];
					} elseif ( 0 < strlen( $pod ) ) {
						$pick_val = $pod;
					}
				}

				$table_info = pods_v( 'table_info', $options );

				if ( empty( $table_info ) && ! empty( $pick_object ) ) {
					$table_info = pods_api()->get_table_info( $pick_object, $pick_val, null, null, $options );
				}

				if ( null === $related_pod && $table_info && $table_info['pod'] ) {
					$related_pod = $table_info['pod'];
				}

				$search_data = pods_data( $related_pod );
				$search_data->table( $table_info );

				$default_field_index = $search_data->field_index;

				$params = array(
					'select'     => "`t`.`{$search_data->field_id}`, `t`.`{$search_data->field_index}`",
					'table'      => $search_data->table,
					'where'      => pods_v( static::$type . '_where', $options, (array) $table_info['where_default'], true ),
					'orderby'    => pods_v( static::$type . '_orderby', $options, null, true ),
					'having'     => pods_v( static::$type . '_having', $options, null, true ),
					'groupby'    => pods_v( static::$type . '_groupby', $options, null, true ),
					'pagination' => false,
					'search'     => false,
				);

				if ( in_array( $options[ static::$type . '_object' ], array( 'site', 'network' ), true ) ) {
					$params['select'] .= ', `t`.`path`';
				}

				if ( ! empty( $params['where'] ) && (array) $table_info['where_default'] !== $params['where'] ) {
					$params['where'] = pods_evaluate_tags( $params['where'], true );
				}

				if ( ! empty( $params['having'] ) ) {
					$params['having'] = pods_evaluate_tags( $params['having'], true );
				}

				if ( empty( $params['where'] ) || ( ! is_array( $params['where'] ) && '' === trim( $params['where'] ) ) ) {
					$params['where'] = array();
				}

				$params['where'] = (array) $params['where'];

				if ( 'value_to_label' === $context ) {
					$params['where'][] = "`t`.`{$search_data->field_id}` = " . number_format( $value, 0, '', '' );
				}

				// Check if we need to limit by the current post author.
				if (
					1 === (int) pods_v( static::$type . '_post_author', $options, 0 )
					&& $current_pod
					&& 'post_type' === $current_pod['type']
					&& 'post_type' === $options[ static::$type . '_object' ]
				) {
					$post_author_id = 0;

					if ( empty( $id ) ) {
						if ( is_user_logged_in() ) {
							$post_author_id = get_current_user_id();
						}
					} else {
						$post = get_post( $id );

						if ( $post ) {
							$post_author_id = $post->post_author;
						}
					}

					$params['where'][] = '`t`.`post_author` = ' . (int) $post_author_id;
				}

				$display = trim( (string) pods_v( static::$type . '_display', $options ), ' {@}' );

				$display_field       = "`t`.`{$search_data->field_index}`";
				$display_field_name  = $search_data->field_index;
				$display_field_alias = false;

				if ( 0 < strlen( $display ) ) {
					if ( ! empty( $table_info['pod'] ) ) {
						/** @var Pod $related_pod */
						$related_pod = $table_info['pod'];

						$related_storage     = $related_pod->get_storage();
						$related_type        = $related_pod->get_type();
						$found_display_field = $related_pod->get_field( $display );

						if ( $found_display_field ) {
							$display_field_name = $found_display_field->get_name();
						}

						if ( $found_display_field instanceof Object_Field ) {
							$display_field = "`t`.`{$display_field_name}`";
						} elseif (
							'table' === $related_storage
							&& ! in_array(
								$related_type, array(
									'pod',
									'table',
								), true
							)
						) {
							$display_field = "`d`.`{$display_field_name}`";
						} elseif ( 'meta' === $related_storage ) {
							$display_field = "`{$display_field_name}`.`meta_value`";

							$display_field_alias = true;
						} else {
							$display_field = "`t`.`{$display_field_name}`";
						}
					} elseif ( isset( $table_info['object_fields'] ) && isset( $table_info['object_fields'][ $display ] ) ) {
						$display_field_name = $table_info['object_fields'][ $display ];

						$display_field = "`t`.`{$display_field_name}`";
					}//end if
				}//end if

				if ( false === strpos( $params['select'], $display_field ) ) {
					$params['select'] .= ', ' . $display_field . ( $display_field_alias ? " AS `{$display_field_name}`" : '' );
				}

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

				if ( $hierarchy && $table_info['object_hierarchical'] && ! empty( $table_info['field_parent'] ) ) {
					if ( false === strpos( $params['select'], $table_info['field_parent_select'] ) ) {
						$params['select'] .= ', ' . $table_info['field_parent_select'];
					}
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

						if ( $display_field_name !== $search_data->field_index ) {
							$lookup_where[ $display_field_name ] = "{$display_field} LIKE '%" . pods_sanitize_like( $data_params['query'] ) . "%'";
						}

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
						$orderby[] = "( {$display_field} LIKE '%" . pods_sanitize_like( $data_params['query'] ) . "%' ) DESC";

						$pick_orderby = pods_v( static::$type . '_orderby', $options, null, true );

						if ( 0 < strlen( $pick_orderby ) ) {
							$orderby[] = $pick_orderby;
						}

						if ( ! in_array( $search_data->field_index, $orderby, true ) ) {
							$orderby[] = "`t`.`{$search_data->field_index}`";
						}

						if ( ! in_array( $search_data->field_id, $orderby, true ) ) {
							$orderby[] = "`t`.`{$search_data->field_id}`";
						}

						$params['orderby'] = $orderby;
					}//end if
				} elseif ( 0 < $limit ) {
					$params['limit'] = $limit;
					$params['page']  = $page;
				}//end if

				$extra = '';

				if ( $wpdb->posts === $search_data->table ) {
					$extra = '`t`.`post_type`, `t`.`menu_order`, `t`.`post_date`';
				} elseif ( $wpdb->terms === $search_data->table ) {
					$extra = '`tt`.`taxonomy`';
				} elseif ( $wpdb->comments === $search_data->table ) {
					$extra = '`t`.`comment_type`';
				} elseif ( $wpdb->site === $search_data->table ) {
					$extra = '`t`.`path`';
				} elseif ( $wpdb->blogs === $search_data->table ) {
					$extra = '`t`.`path`';
				}

				if ( '' !== $extra && false === strpos( $params['select'], $extra ) ) {
					$params['select'] .= ', ' . $extra;
				}

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

				if ( empty( $params['where'] ) ) {
					$params['where'] = null;
				}

				$results = $search_data->select( $params );

				if ( $autocomplete && 0 < $params['limit'] && $params['limit'] < $search_data->total_found() ) {
					if ( ! empty( $value ) ) {
						$ids = $value;

						if ( is_array( $ids ) ) {
							if ( isset( $ids[0] ) && is_array( $ids[0] ) ) {
								$ids = wp_list_pluck( $ids, $search_data->field_id );
							}

							if ( $params['limit'] < count( $ids ) ) {
								$params['limit'] = count( $ids );
							}

							$ids = array_map( 'absint', $ids );
							$ids = implode( ', ', $ids );
						} else {
							$ids = (int) $ids;
						}

						if ( is_array( $params['where'] ) ) {
							$params['where'] = implode( ' AND ', $params['where'] );
						}
						if ( ! empty( $params['where'] ) ) {
							$params['where'] = '(' . $params['where'] . ') AND ';
						}

						$params['where'] .= "`t`.`{$search_data->field_id}` IN ( {$ids} )";

						$results = $search_data->select( $params );
					}//end if
				} else {
					$autocomplete = false;
				}//end if

				if ( 'data' === $context ) {
					pods_static_cache_set( $name . '/' . $options['id'], [
						'autocomplete' => $autocomplete,
					], __CLASS__ . '/field_data' );
				}

				if ( $hierarchy && ! $autocomplete && ! empty( $results ) && $table_info['object_hierarchical'] && ! empty( $table_info['field_parent'] ) ) {
					$select_args = array(
						'id'     => $table_info['field_id'],
						'index'  => $display_field_name,
						'parent' => $table_info['field_parent'],
					);

					$results = pods_hierarchical_select( $results, $select_args );
				}

				$ids = array();

				if ( ! empty( $results ) ) {
					foreach ( $results as $result ) {
						$result      = get_object_vars( $result );
						$field_id    = $search_data->field_id;
						$field_index = $display_field_name;

						if ( ! isset( $result[ $field_index ] ) ) {
							$field_index = $default_field_index;
						}

						if ( ! isset( $result[ $field_id ], $result[ $field_index ] ) ) {
							continue;
						}

						$result[ $field_index ] = trim( $result[ $field_index ] );

						$object      = '';
						$object_type = '';

						if ( $wpdb->posts === $search_data->table && isset( $result['post_type'] ) ) {
							$object      = $result['post_type'];
							$object_type = 'post_type';
						} elseif ( $wpdb->terms === $search_data->table && isset( $result['taxonomy'] ) ) {
							$object      = $result['taxonomy'];
							$object_type = 'taxonomy';
						}

						$field_index_data_to_use = pods_v( $field_index, $search_data->object_fields );

						$display_filter = pods_v( 'display_filter', $field_index_data_to_use );

						if ( 0 < strlen( $display_filter ) ) {
							$display_filter_args = pods_v( 'display_filter_args', $field_index_data_to_use );

							$filter_args = array(
								$display_filter,
								$result[ $field_index ],
							);

							if ( ! empty( $display_filter_args ) ) {
								foreach ( (array) $display_filter_args as $display_filter_arg ) {
									// Manual solution to a problem that won't map correctly.
									if ( 'post_ID' === $display_filter_arg ) {
										$display_filter_arg = 'ID';
									}

									if ( isset( $result[ $display_filter_arg ] ) ) {
										$filter_args[] = $result[ $display_filter_arg ];
									}
								}
							}

							$result[ $field_index ] = call_user_func_array( 'apply_filters', $filter_args );
						}

						if ( in_array( $options[ static::$type . '_object' ], array( 'site', 'network' ), true ) ) {
							$result[ $field_index ] = $result[ $field_index ] . $result['path'];
						} elseif ( '' === $result[ $field_index ] ) {
							$result[ $field_index ] = '(No Title)';
						}

						if ( 'admin_ajax_relationship' === $context ) {
							$items[] = $this->build_dfv_field_item_data_recurse_item( $result[ $field_id ], $result[ $field_index ], (object) $object_params );
						} else {
							$data[ $result[ $field_id ] ] = $result[ $field_index ];
						}

						$ids[] = $result[ $field_id ];
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

		if ( ! isset( $params->_wpnonce, $params->pod_name, $params->field_name, $params->uri_hash, $params->id ) ) {
			pods_error( __( 'Unauthorized request', 'pods' ), PodsInit::$admin );
		}

		if ( ! isset( $params->query ) || '' === trim( $params->query ) ) {
			pods_error( __( 'Invalid field request', 'pods' ), PodsInit::$admin );
		}

		$_wpnonce   = $params->_wpnonce;
		$pod_name   = $params->pod_name;
		$field_name = $params->field_name;
		$uri_hash   = $params->uri_hash;
		$id         = (int) $params->id;

		$query = $params->query;
		$limit = pods_v( 'limit', $params, 15, true );
		$page  = pods_v( 'page', $params, 1, true );

		$uid = pods_session_id();

		if ( is_user_logged_in() ) {
			$uid = 'user_' . get_current_user_id();
		}

		$nonce_name = 'pods_relationship:' . json_encode( compact( 'pod_name', 'field_name', 'uid', 'uri_hash', 'id' ) );

		if ( false === wp_verify_nonce( $_wpnonce, $nonce_name ) ) {
			pods_error( __( 'Unauthorized request', 'pods' ), PodsInit::$admin );
		}

		if ( empty( self::$api ) ) {
			self::$api = pods_api();
		}

		$pod = self::$api->load_pod( [
			'name' => $pod_name,
		] );

		if ( ! $pod ) {
			pods_error( __( 'Invalid Pod configuration', 'pods' ), PodsInit::$admin );
		}

		$field = $pod->get_field( $field_name );

		if ( ! $field ) {
			pods_error( __( 'Invalid Field configuration', 'pods' ), PodsInit::$admin );
		}

		if ( ! $field->is_autocomplete_relationship() ) {
			pods_error( __( 'Invalid field', 'pods' ), PodsInit::$admin );
		}

		$object_params = array(
			// The name of the field.
			'name'        => $field['name'],
			// The value of the field.
			'value'       => null,
			// Field options.
			'options'     => $field,
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
		$data = [];

		$post_stati = get_post_stati( [], 'objects' );

		foreach ( $post_stati as $post_status ) {
			$data[ $post_status->name ] = $post_status->label;
		}

		return (array) apply_filters( 'pods_form_ui_field_pick_data_post_stati', $data, $name, $value, $options, $pod, $id );
	}

	/**
	 * Data callback for Post Stati (with any).
	 *
	 * @param string|null       $name    The name of the field.
	 * @param string|array|null $value   The value of the field.
	 * @param array|null        $options Field options.
	 * @param array|null        $pod     Pod data.
	 * @param int|null          $id      Item ID.
	 *
	 * @return array
	 *
	 * @since 2.9.10
	 */
	public function data_post_stati_with_any( $name = null, $value = null, $options = null, $pod = null, $id = null ) {
		$data = [
			'_pods_any' => esc_html__( 'Any Status (excluding Auto-Draft and Trashed)', 'pods' ),
		];

		$data = array_merge( $data, $this->data_post_stati( $name, $value, $options, $pod, $id ) );

		/**
		 * Allow filtering the list of post stati with any.
		 *
		 * @since 2.9.10
		 *
		 * @param array             $data    The list of post stati with any.
		 * @param string|null       $name    The name of the field.
		 * @param string|array|null $value   The value of the field.
		 * @param array|null        $options Field options.
		 * @param array|null        $pod     Pod data.
		 * @param int|null          $id      Item ID.
		 */
		return (array) apply_filters( 'pods_form_ui_field_pick_data_post_stati_with_any', $data, $name, $value, $options, $pod, $id );
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
	 * Data callback for Post Types
	 *
	 * @param string       $name    The name of the field
	 * @param string|array $value   The value of the field
	 * @param array        $options Field options
	 * @param array        $pod     Pod data
	 * @param int          $id      Item ID
	 *
	 * @return array
	 *
	 * @since 2.3.0
	 */
	public function data_post_types( $name = null, $value = null, $options = null, $pod = null, $id = null ) {

		$data = array();

		$post_types = get_post_types( array(), 'objects' );

		$ignore = [
			'revision',
			'nav_menu_item',
			'custom_css',
			'customize_changeset',
			'attachment',
			'oembed_cache',
			'user_request',
			'wp_block',
			'wp_template',
			'wp_template_part',
			'wp_global_styles',
			'wp_navigation',
		];

		foreach ( $post_types as $post_type ) {
			if ( in_array( $post_type->name, $ignore, true ) || 0 === strpos( $post_type->name, '_pods_' ) ) {
				continue;
			}

			$data[ $post_type->name ] = $post_type->label;
		}

		return apply_filters( 'pods_form_ui_field_pick_data_post_types', $data, $name, $value, $options, $pod, $id );
	}

	/**
	 * Data callback for Taxonomies
	 *
	 * @param string       $name    The name of the field
	 * @param string|array $value   The value of the field
	 * @param array        $options Field options
	 * @param array        $pod     Pod data
	 * @param int          $id      Item ID
	 *
	 * @return array
	 *
	 * @since 2.3.0
	 */
	public function data_taxonomies( $name = null, $value = null, $options = null, $pod = null, $id = null ) {

		$data = array();

		$taxonomies = get_taxonomies( array(), 'objects' );

		$ignore = array( 'nav_menu', 'post_format' );

		foreach ( $taxonomies as $taxonomy ) {
			if ( in_array( $taxonomy->name, $ignore, true ) ) {
				continue;
			}

			$data[ $taxonomy->name ] = $taxonomy->label;
		}

		return apply_filters( 'pods_form_ui_field_pick_data_taxonomies', $data, $name, $value, $options, $pod, $id );
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
	 * Data callback for Days of the Week.
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
	 * Data callback for Months of the Year.
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
				window.parent.postMessage( {
					type: 'PODS_MESSAGE',
					data: <?php echo wp_json_encode( $model_data, JSON_HEX_TAG ); ?>,
				}, window.location.origin );
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

		if ( function_exists( 'get_current_screen' ) ) {
			$screen = get_current_screen();

			if ( 'edit-tags' === $screen->base ) {
				// @todo Need more effort on the solution for add new handling.
				//add_action( 'admin_footer', [ $this, 'admin_modal_bail_term_action_add_new' ], 20 );
			}
		}

		add_action( 'created_term', array( $this, 'admin_modal_bail_term' ), 10, 3 );
		add_action( 'edited_term', array( $this, 'admin_modal_bail_term' ), 10, 3 );

	}

	/**
	 * Hook into term creation process to bail after success.
	 *
	 * @todo Try and catch the added tr node on the table tbody.
	 */
	public function admin_modal_bail_term_action_add_new() {
		?>
			<script type="text/javascript">
				jQuery( function ( $ ) {
					/** @var {jQuery.Event} e */
					$( '.tags' ).on( 'DOMSubtreeModified', function(e) {
						console.log( e );

						if ( !== e.target.is( 'tbody#the-list' ) ) {
							return;
						}

						const $theTermRow = $( e.target.innerHTML() );
						const titleRow = $theTermRow.find( '.column-name .row-title' );
						const actionView = $theTermRow.find( '.row-actions span.view a' );

						const termData = {
							id       : $theTermRow.find( '.check-column input' ).val(),
							icon     : '',
							name     : titleRow.text(),
							edit_link: titleRow.prop( 'href' ),
							link     : actionView[0] ? actionView.prop( 'href' ) : '',
							selected : true,
						};

						console.log( termData );

						window.parent.postMessage( {
							type : 'PODS_MESSAGE',
							data : termData,
						}, window.location.origin );
					} );
				} );
			</script>
		<?php
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
			$obj = pods_get_instance( $params['pod'] );
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

	/**
	 * Build field data for Pods DFV.
	 *
	 * @param object $args            {
	 *     Field information arguments.
	 *
	 *     @type string     $name            Field name.
	 *     @type string     $type            Field type.
	 *     @type array      $options         Field options.
	 *     @type mixed      $value           Current value.
	 *     @type array      $pod             Pod information.
	 *     @type int|string $id              Current item ID.
	 *     @type string     $form_field_type HTML field type.
	 * }
	 *
	 * @return array
	 */
	public function build_dfv_field_data( $args ) {
		$data = parent::build_dfv_field_data( $args );

		// Normalize arrays for multiple select.
		if ( is_array( $data['fieldValue'] ) ) {
			$data['fieldValue'] = array_values( $data['fieldValue'] );
		}

		return $data;
	}

}
