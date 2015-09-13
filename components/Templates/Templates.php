<?php
/**
 * Name: Templates
 *
 * Description: An easy to use templating engine for Pods. Use {@field_name} magic tags to output values, within your HTML markup.
 *
 * Version: 2.3
 *
 * Category: Advanced
 *
 * Menu Page: edit.php?post_type=_pods_template
 * Menu Add Page: post-new.php?post_type=_pods_template
 *
 * @package Pods\Components
 * @subpackage Templates
 */

// Pull in the functions
require_once( plugin_dir_path( __FILE__ ) . '/includes/functions-view_template.php' );
require_once( plugin_dir_path( __FILE__ ) . '/includes/functions-pod_reference.php' );

// Pull in the Frontier Template System
require_once( plugin_dir_path( __FILE__ ) . 'class-pods_templates.php' );

//Pull in Auto Template
require_once( dirname( __FILE__ ) . '/includes/auto-template/Pods_Templates_Auto_Template_Settings.php' );
new Pods_Templates_Auto_Template_Settings();

Pods_Templates_Frontier::get_instance();


class Pods_Templates extends PodsComponent {

	/**
	 * Pods object
	 *
	 * @var object
	 *
	 * @since 2.0
	 */
	static $obj = null;

	/**
	 * Whether to enable deprecated functionality based on old function usage
	 *
	 * @var bool
	 *
	 * @since 2.0
	 */
	static $deprecated = false;

	/**
	 * Object type
	 *
	 * @var string
	 *
	 * @since 2.0
	 */
	private $object_type = '_pods_template';

	/**
	 * Do things like register/enqueue scripts and stylesheets
	 *
	 * @since 2.0
	 */
	public function __construct () {
		$args = array(
			'label' => 'Pod Templates',
			'labels' => array( 'singular_name' => 'Pod Template' ),
			'public' => false,
			'can_export' => false,
			'show_ui' => true,
			'show_in_menu' => false,
			'query_var' => false,
			'rewrite' => false,
			'has_archive' => false,
			'hierarchical' => false,
            'supports' => array( 'title', 'author', 'revisions' ),
            'menu_icon' => 'dashicons-pods'
		);

		if ( !pods_is_admin() )
			$args[ 'capability_type' ] = 'pods_template';

		$args = PodsInit::object_label_fix( $args, 'post_type' );

		register_post_type( $this->object_type, apply_filters( 'pods_internal_register_post_type_object_template', $args ) );

		if ( is_admin() ) {
			add_filter( 'post_updated_messages', array( $this, 'setup_updated_messages' ), 10, 1 );

			add_action( 'dbx_post_advanced', array( $this, 'edit_page_form' ), 10 );

			add_action( 'pods_meta_groups', array( $this, 'add_meta_boxes' ) );

			add_filter( 'get_post_metadata', array( $this, 'get_meta' ), 10, 4 );
			add_filter( 'update_post_metadata', array( $this, 'save_meta' ), 10, 4 );

			add_action( 'pods_meta_save_pre_post__pods_template', array( $this, 'fix_filters' ), 10, 5 );
			add_action( 'post_updated', array( $this, 'clear_cache' ), 10, 3 );
			add_action( 'delete_post', array( $this, 'clear_cache' ), 10, 1 );
			add_filter( 'post_row_actions', array( $this, 'remove_row_actions' ), 10, 2 );
			add_filter( 'bulk_actions-edit-' . $this->object_type, array( $this, 'remove_bulk_actions' ) );

			add_filter( 'builder_layout_filter_non_layout_post_types', array( $this, 'disable_builder_layout' ) );

		}
	}

	public function disable_builder_layout ( $post_types ) {
		$post_types[] = $this->object_type;

		return $post_types;
	}

	/**
	 * Update Post Type messages
	 *
	 * @param array $messages
	 *
	 * @return array
	 * @since 2.0.2
	 */
	public function setup_updated_messages ( $messages ) {
		global $post, $post_ID;

		$post_type = get_post_type_object( $this->object_type );

		$labels = $post_type->labels;

		$messages[ $post_type->name ] = array(
			1 => sprintf( __( '%s updated. <a href="%s">%s</a>', 'pods' ), $labels->singular_name, esc_url( get_permalink( $post_ID ) ), $labels->view_item ),
			2 => __( 'Custom field updated.', 'pods' ),
			3 => __( 'Custom field deleted.', 'pods' ),
			4 => sprintf( __( '%s updated.', 'pods' ), $labels->singular_name ),
			/* translators: %s: date and time of the revision */
			5 => isset( $_GET[ 'revision' ] ) ? sprintf( __( '%s restored to revision from %s', 'pods' ), $labels->singular_name, wp_post_revision_title( (int) $_GET[ 'revision' ], false ) ) : false,
			6 => sprintf( __( '%s published. <a href="%s">%s</a>', 'pods' ), $labels->singular_name, esc_url( get_permalink( $post_ID ) ), $labels->view_item ),
			7 => sprintf( __( '%s saved.', 'pods' ), $labels->singular_name ),
			8 => sprintf( __( '%s submitted. <a target="_blank" href="%s">Preview %s</a>', 'pods' ),
				$labels->singular_name,
				esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ),
				$labels->singular_name
			),
			9 => sprintf( __( '%s scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview %s</a>', 'pods' ),
				$labels->singular_name,
				// translators: Publish box date format, see http://php.net/date
				date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ),
				esc_url( get_permalink( $post_ID ) ),
				$labels->singular_name
			),
			10 => sprintf( __( '%s draft updated. <a target="_blank" href="%s">Preview %s</a>', 'pods' ), $labels->singular_name, esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ), $labels->singular_name )
		);

		if ( false === (boolean) $post_type->public ) {
			$messages[ $post_type->name ][ 1 ] = sprintf( __( '%s updated.', 'pods' ), $labels->singular_name );
			$messages[ $post_type->name ][ 6 ] = sprintf( __( '%s published.', 'pods' ), $labels->singular_name );
			$messages[ $post_type->name ][ 8 ] = sprintf( __( '%s submitted.', 'pods' ), $labels->singular_name );
			$messages[ $post_type->name ][ 9 ] = sprintf( __( '%s scheduled for: <strong>%1$s</strong>.', 'pods' ),
				$labels->singular_name,
				// translators: Publish box date format, see http://php.net/date
				date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) )
			);
			$messages[ $post_type->name ][ 10 ] = sprintf( __( '%s draft updated.', 'pods' ), $labels->singular_name );
		}

		return $messages;
	}

	/**
	 * Enqueue styles
	 *
	 * @since 2.0
	 */
	public function admin_assets () {
		wp_enqueue_style( 'pods-admin' );
	}

	/**
	 * Fix filters, specifically removing balanceTags
	 *
	 * @since 2.0.1
	 */
	public function fix_filters ( $data, $pod = null, $id = null, $groups = null, $post = null ) {
		remove_filter( 'content_save_pre', 'balanceTags', 50 );
	}

	/**
	 * Remove unused row actions
	 *
	 * @since 2.0.5
	 */
	function remove_row_actions ( $actions, $post ) {
		global $current_screen;

		if ( !is_object( $current_screen ) || $this->object_type != $current_screen->post_type )
			return $actions;

		if ( isset( $actions[ 'view' ] ) )
			unset( $actions[ 'view' ] );

		if ( isset( $actions[ 'inline hide-if-no-js' ] ) )
			unset( $actions[ 'inline hide-if-no-js' ] );

		// W3 Total Cache
		if ( isset( $actions[ 'pgcache_purge' ] ) )
			unset( $actions[ 'pgcache_purge' ] );

		return $actions;
	}

	/**
	 * Remove unused bulk actions
	 *
	 * @since 2.0.5
	 */
	public function remove_bulk_actions ( $actions ) {
		if ( isset( $actions[ 'edit' ] ) )
			unset( $actions[ 'edit' ] );

		return $actions;
	}

	/**
	 * Clear cache on save
	 *
	 * @since 2.0
	 */
	public function clear_cache ( $data, $pod = null, $id = null, $groups = null, $post = null ) {
		$old_post = $id;

		if ( !is_object( $id ) )
			$old_post = null;

		if ( is_object( $post ) && $this->object_type != $post->post_type )
			return;

		if ( !is_array( $data ) && 0 < $data ) {
			$post = $data;
			$post = get_post( $post );
		}

		if ( $this->object_type == $post->object_type )
			pods_transient_clear( 'pods_object_templates' );
	}

	/**
	 * Change post title placeholder text
	 *
	 * @since 2.0
	 */
	public function set_title_text ( $text, $post ) {
		return __( 'Enter template name here', 'pods' );
	}

	/**
	 * Edit page form
	 *
	 * @since 2.0
	 */
	public function edit_page_form () {
		global $post_type;

		if ( $this->object_type != $post_type )
			return;

		add_filter( 'enter_title_here', array( $this, 'set_title_text' ), 10, 2 );
	}

	/**
	 * Add meta boxes to the page
	 *
	 * @since 2.0
	 */
	public function add_meta_boxes () {
		$pod = array(
			'name' => $this->object_type,
			'type' => 'post_type'
		);

		if ( isset( PodsMeta::$post_types[ $pod[ 'name' ] ] ) )
			return;

		$fields = array(
			array(
				'name' => 'admin_only',
				'label' => __( 'Show to Admins Only?', 'pods' ),
				'default' => 0,
				'type' => 'boolean',
				'dependency' => true
			),
			array(
				'name' => 'restrict_capability',
				'label' => __( 'Restrict access by Capability?', 'pods' ),
				'help' => array(
					__( '<h6>Capabilities</h6> Capabilities denote access to specific functionality in WordPress, and are assigned to specific User Roles. Please see the Roles and Capabilities component in Pods for an easy tool to add your own capabilities and roles.', 'pods' ),
					'http://codex.wordpress.org/Roles_and_Capabilities'
				),
				'default' => 0,
				'type' => 'boolean',
				'dependency' => true
			),
			array(
				'name' => 'capability_allowed',
				'label' => __( 'Capability Allowed', 'pods' ),
				'type' => 'pick',
				'pick_object' => 'capability',
				'pick_format_type' => 'multi',
				'pick_format_multi' => 'autocomplete',
				'pick_ajax' => false,
				'default' => '',
				'depends-on' => array(
					'restrict_capability' => true
				)
			)
		);

		pods_group_add( $pod, __( 'Restrict Access', 'pods' ), $fields, 'normal', 'high' );
	}

	/**
	 * Get the fields
	 *
	 * @param null $_null
	 * @param int $post_ID
	 * @param string $meta_key
	 * @param bool $single
	 *
	 * @return array|bool|int|mixed|null|string|void
	 */
	public function get_meta ( $_null, $post_ID = null, $meta_key = null, $single = false ) {
		if ( 'code' == $meta_key ) {
			$post = get_post( $post_ID );

			if ( is_object( $post ) && $this->object_type == $post->post_type )
				return $post->post_content;
		}

		return $_null;
	}

	/**
	 * Save the fields
	 *
	 * @param $_null
	 * @param int $post_ID
	 * @param string $meta_key
	 * @param string $meta_value
	 *
	 * @return bool|int|null
	 */
	public function save_meta ( $_null, $post_ID = null, $meta_key = null, $meta_value = null ) {
		if ( 'code' == $meta_key ) {
			$post = get_post( $post_ID );

			if ( is_object( $post ) && $this->object_type == $post->post_type ) {
				$postdata = array(
					'ID' => $post_ID,
					'post_content' => $meta_value
				);

				remove_filter( current_filter(), array( $this, __FUNCTION__ ), 10 );

				$revisions = false;

				if ( has_action( 'pre_post_update', 'wp_save_post_revision' ) ) {
					remove_action( 'pre_post_update', 'wp_save_post_revision' );

					$revisions = true;
				}

				wp_update_post( (object) $postdata ); // objects will be automatically sanitized

				if ( $revisions )
					add_action( 'pre_post_update', 'wp_save_post_revision' );

				return true;
			}
		}

		return $_null;
	}

	/**
	 * Display the page template
	 *
	 * @param string $template_name The template name
	 * @param string $code Custom template code to use instead
	 * @param object $obj The Pods object
	 * @param bool $deprecated Whether to use deprecated functionality based on old function usage
	 *
	 * @return mixed|string|void
	 * @since 2.0
	 */
	public static function template ( $template_name, $code = null, $obj = null, $deprecated = false ) {
		if ( !empty( $obj ) )
			self::$obj =& $obj;
		else
			$obj =& self::$obj;

		self::$deprecated = $deprecated;

		if ( empty( $obj ) || !is_object( $obj ) )
			return '';

		$template = array(
			'id' => 0,
			'slug' => $template_name,
			'code' => $code,
			'options' => array(),
		);

		if ( empty( $code ) && !empty( $template_name ) ) {
			$template_obj = $obj->api->load_template( array( 'name' => $template_name ) );

			if ( !empty( $template_obj ) ) {
				$template = $template_obj;

				if ( !empty( $template[ 'code' ] ) )
					$code = $template[ 'code' ];

				$permission = pods_permission( $template[ 'options' ] );

				$permission = (boolean) apply_filters( 'pods_templates_permission', $permission, $code, $template, $obj );

				if ( !$permission ) {
					return apply_filters( 'pods_templates_permission_denied', __( 'You do not have access to view this content.', 'pods' ), $code, $template, $obj );
				}
			}
		}

		$code = apply_filters( 'pods_templates_pre_template', $code, $template, $obj );
		$code = apply_filters( 'pods_templates_pre_template_' . $template[ 'slug' ], $code, $template, $obj );

		ob_start();

		if ( !empty( $code ) ) {
			// Only detail templates need $this->id
			if ( empty( $obj->id ) ) {
				while ( $obj->fetch() ) {
					echo self::do_template( $code, $obj );
				}
			}
			else
				echo self::do_template( $code, $obj );
		}
		elseif ( $template_name == trim( preg_replace( '/[^a-zA-Z0-9_\-\/]/', '', $template_name ), ' /-' ) ) {
			$default_templates = array(
				'pods/' . $template_name,
				'pods-' . $template_name,
				$template_name
			);

			$default_templates = apply_filters( 'pods_template_default_templates', $default_templates );

			if ( empty( $obj->id ) ) {
				while ( $obj->fetch() ) {
					pods_template_part( $default_templates, compact( array_keys( get_defined_vars() ) ) );
				}
			}
			else
				pods_template_part( $default_templates, compact( array_keys( get_defined_vars() ) ) );

		}

		$out = ob_get_clean();

		$out = apply_filters( 'pods_templates_post_template', $out, $code, $template, $obj );
		$out = apply_filters( 'pods_templates_post_template_' . $template[ 'slug' ], $out, $code, $template, $obj );

		return $out;
	}

	/**
	 * Parse a template string
	 *
	 * @param string $code The template string to parse
	 * @param object $obj The Pods object
	 *
	 * @since 1.8.5
	 */
	public static function do_template ( $code, $obj = null ) {
		if ( !empty( $obj ) )
			self::$obj =& $obj;
		else
			$obj =& self::$obj;

		if ( empty( $obj ) || !is_object( $obj ) )
			return '';

		$code = trim( $code );

		if ( false !== strpos( $code, '<?' ) && ( !defined( 'PODS_DISABLE_EVAL' ) || !PODS_DISABLE_EVAL ) ) {
			pods_deprecated( 'Pod Template PHP code has been deprecated, please use WP Templates instead of embedding PHP.', '2.3' );

			$code = str_replace( '$this->', '$obj->', $code );

			ob_start();

			eval( "?>$code" );

			$out = ob_get_clean();
		}
		else
			$out = $code;

		$out = $obj->do_magic_tags( $out );

		return apply_filters( 'pods_templates_do_template', $out, $code, $obj );
	}

}
