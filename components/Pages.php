<?php
/**
 * Name: Pages
 *
 * Menu Name: Pod Pages
 *
 * Description: Creates advanced URL structures using wildcards in order to enable the front-end display of Pods Advanced Content Types. Not recommended for use with other content types.
 *
 * Version: 2.3
 *
 * Category: Advanced
 *
 * Menu Page: edit.php?post_type=_pods_page
 * Menu Add Page: post-new.php?post_type=_pods_page
 *
 * @package    Pods\Components
 * @subpackage Pages
 */

use Pods\Whatsit\Field;
use Pods\Whatsit\Page;
use Pods\Whatsit\Storage;

if ( class_exists( 'Pods_Pages' ) ) {
	return;
}

/**
 * Class Pods_Pages
 */
class Pods_Pages extends PodsComponent {

	/**
	 * Current Pod Page
	 *
	 * @var array
	 *
	 * @since 2.0.0
	 */
	public static $exists = null;

	/**
	 * Object type
	 *
	 * @var string
	 *
	 * @since 2.0.0
	 */
	private $object_type = '_pods_page';

	/**
	 * Whether the page has been checked already
	 *
	 * @var bool
	 *
	 * @since 2.1.0
	 */
	public static $checked = false;

	/**
	 * Keep track of if pods_content has been called yet
	 *
	 * @var bool
	 *
	 * @since 2.3.0
	 */
	public static $content_called = false;

	/**
	 * The capability type.
	 *
	 * @link https://codex.wordpress.org/Function_Reference/register_post_type
	 * @var string
	 */
	private $capability_type = 'pods_page';

	/**
	 * {@inheritdoc}
	 */
	public function init() {
		$this->register_config();

		add_shortcode( 'pods-content', array( $this, 'shortcode' ) );

		add_filter( 'post_type_link', array( $this, 'post_type_link' ), 10, 2 );

		if ( ! is_admin() ) {
			add_action( 'load_textdomain', array( $this, 'page_check' ), 12 );
		} else {
			add_filter( 'post_updated_messages', array( $this, 'setup_updated_messages' ), 10, 1 );

			add_action( 'add_meta_boxes_' . $this->object_type, array( $this, 'edit_page_form' ) );

			add_filter( 'get_post_metadata', array( $this, 'get_meta' ), 10, 4 );
			add_filter( 'update_post_metadata', array( $this, 'save_meta' ), 10, 4 );

			add_action( 'pods_meta_save_pre_post__pods_page', array( $this, 'fix_filters' ), 10, 5 );
			add_action( 'post_updated', array( $this, 'clear_cache' ), 10, 3 );
			add_action( 'delete_post', array( $this, 'clear_cache' ), 10, 1 );
			add_filter( 'post_row_actions', array( $this, 'remove_row_actions' ), 10, 2 );
			add_filter( 'bulk_actions-edit-' . $this->object_type, array( $this, 'remove_bulk_actions' ) );

			add_filter( 'builder_layout_filter_non_layout_post_types', array( $this, 'disable_builder_layout' ) );
		}

		add_filter( 'members_get_capabilities', array( $this, 'get_capabilities' ) );
	}

	/**
	 * Register the configuration for this object.
	 *
	 * @since 2.9.9
	 */
	public function register_config() {
		$args = array(
			'label'            => 'Pod Pages',
			'labels'           => array( 'singular_name' => 'Pod Page' ),
			'public'           => false,
			'can_export'       => false,
			'show_ui'          => true,
			'show_in_menu'     => false,
			'query_var'        => false,
			'rewrite'          => false,
			'has_archive'      => false,
			'hierarchical'     => false,
			'supports'         => array( 'title', 'author', 'revisions' ),
			'menu_icon'        => pods_svg_icon( 'pods' ),
			'delete_with_user' => false,
		);

		if ( ! pods_is_admin() ) {
			$args['capability_type'] = $this->capability_type;
		}

		$args = PodsInit::object_label_fix( $args, 'post_type' );

		register_post_type( $this->object_type, apply_filters( 'pods_internal_register_post_type_object_page', $args ) );

		$args = [
			'internal'           => true,
			'type'               => 'post_type',
			'storage'            => 'meta',
			'name'               => $this->object_type,
			'label'              => 'Pod Pages',
			'label_singular'     => 'Pod Page',
			'description'        => '',
			'public'             => 0,
			'show_ui'            => 1,
			'rest_enable'        => 0,
			'supports_title'     => 1,
			'supports_editor'    => 0,
			'supports_author'    => 1,
			'supports_revisions' => 1,
		];

		if ( ! pods_is_admin() ) {
			$args['capability_type']        = 'custom';
			$args['capability_type_custom'] = $this->capability_type;
		}

		pods_register_type( 'post_type', $this->object_type, $args );

		$page_templates = static function() {
			if ( ! function_exists( 'get_page_templates' ) ) {
				include_once ABSPATH . 'wp-admin/includes/theme.php';
			}

			$wp_page_templates = apply_filters( 'pods_page_templates', get_page_templates() );

			$page_templates = [];

			foreach ( $wp_page_templates as $page_template => $file ) {
				$page_templates[ $page_template . ' - ' . $file ] = $file;
			}

			$page_templates[ __( '-- Select a Page Template --', 'pods' ) ] = '';

			$page_templates[ __( 'Custom (uses only Pod Page content)', 'pods' ) ] = '_custom';

			if ( ! in_array( 'pods.php', $page_templates, true ) && locate_template( [ 'pods.php', false ] ) ) {
				$page_templates[ __( 'Pods (Pods Default)', 'pods' ) . ' - pods.php' ] = 'pods.php';
			}

			if ( ! in_array( 'page.php', $page_templates, true ) && locate_template( [ 'page.php', false ] ) ) {
				$page_templates[ __( 'Page (WP Default)', 'pods' ) . ' - page.php' ] = 'page.php';
			}

			if ( ! in_array( 'index.php', $page_templates, true ) && locate_template( [ 'index.php', false ] ) ) {
				$page_templates[ __( 'Index (WP Fallback)', 'pods' ) . ' - index.php' ] = 'index.php';
			}

			ksort( $page_templates );

			return array_flip( $page_templates );
		};

		$page_fields = [
			[
				'name'  => 'page_title',
				'label' => __( 'Page Title', 'pods' ),
				'type'  => 'text',
			],
			[
				'name'          => 'code',
				'label'         => __( 'Page Code', 'pods' ),
				'type'          => 'code',
				'attributes'    => [
					'id' => 'content',
				],
				'label_options' => [
					'attributes' => [
						'for' => 'content',
					],
				],
			],
			[
				'name'  => 'precode',
				'label' => __( 'Page Precode', 'pods' ),
				'type'  => 'code',
				'help'  => __( 'Precode will run before your theme outputs the page. It is expected that this value will be a block of PHP. You must open the PHP tag here, as we do not open it for you by default.', 'pods' ),
			],
			[
				'name'                  => 'page_template',
				'label'                 => __( 'Page Template', 'pods' ),
				'default'               => '',
				'type'                  => 'pick',
				'pick_object'           => 'custom-simple',
				'pick_format_type'      => 'single',
				'data'                  => $page_templates,
				'override_object_field' => true,
			],
		];

		$associated_pods = static function() {
			$associated_pods = [
				0 => __( '-- Select a Pod --', 'pods' ),
			];

			$all_pods = pods_api()->load_pods( [ 'labels' => true ] );

			if ( ! empty( $all_pods ) ) {
				foreach ( $all_pods as $pod_name => $pod_label ) {
					$associated_pods[ $pod_name ] = $pod_label . ' (' . $pod_name . ')';
				}
			} else {
				$associated_pods[0] = __( 'None Found', 'pods' );
			}

			return $associated_pods;
		};

		$association_fields = [
			[
				'name'             => 'pod',
				'label'            => __( 'Associated Pod', 'pods' ),
				'default'          => 0,
				'type'             => 'pick',
				'pick_object'      => 'custom-simple',
				'pick_format_type' => 'single',
				'placeholder'      => __( 'Select Pod', 'pods' ),
				'data'             => $associated_pods,
				'dependency'       => true,
			],
			[
				'name'        => 'pod_slug',
				'label'       => __( 'Wildcard Slug', 'pods' ),
				'help'        => __( 'Setting the Wildcard Slug is an easy way to setup a detail page. You can use the special tag {@url.2} to match the *third* level of the URL of a Pod Page named "first/second/*" part of the pod page. This is functionally the same as using pods_v_sanitized( 2, "url" ) in PHP.', 'pods' ),
				'type'        => 'text',
				'excludes-on' => [ 'pod' => 0 ],
			],
		];

		$restrict_fields = [
			[
				'name'       => 'admin_only',
				'label'      => __( 'Restrict access to Admins', 'pods' ),
				'default'    => 0,
				'type'       => 'boolean',
				'dependency' => true,
			],
			[
				'name'       => 'restrict_role',
				'label'      => __( 'Restrict access by Role', 'pods' ),
				'help'       => [
					__( '<h6>Roles</h6> Roles are assigned to users to provide them access to specific functionality in WordPress. Please see the Roles and Capabilities component in Pods for an easy tool to add your own roles and edit existing ones.', 'pods' ),
					'http://codex.wordpress.org/Roles_and_Capabilities',
				],
				'default'    => 0,
				'type'       => 'boolean',
				'dependency' => true,
			],
			[
				'name'              => 'roles_allowed',
				'label'             => __( 'Role(s) Allowed', 'pods' ),
				'type'              => 'pick',
				'pick_object'       => 'role',
				'pick_format_type'  => 'multi',
				'pick_format_multi' => 'autocomplete',
				'pick_ajax'         => false,
				'default'           => '',
				'depends-on'        => [
					'pods_meta_restrict_role' => true,
				],
			],
			[
				'name'       => 'restrict_capability',
				'label'      => __( 'Restrict access by Capability', 'pods' ),
				'help'       => [
					__( '<h6>Capabilities</h6> Capabilities denote access to specific functionality in WordPress, and are assigned to specific User Roles. Please see the Roles and Capabilities component in Pods for an easy tool to add your own capabilities and roles.', 'pods' ),
					'http://codex.wordpress.org/Roles_and_Capabilities',
				],
				'default'    => 0,
				'type'       => 'boolean',
				'dependency' => true,
			],
			[
				'name'              => 'capability_allowed',
				'label'             => __( 'Capability Allowed', 'pods' ),
				'type'              => 'pick',
				'pick_object'       => 'capability',
				'pick_format_type'  => 'multi',
				'pick_format_multi' => 'autocomplete',
				'pick_ajax'         => false,
				'default'           => '',
				'depends-on'        => [
					'pods_meta_restrict_capability' => true,
				],
			],
			[
				'name'       => 'restrict_redirect',
				'label'      => __( 'Redirect if Restricted', 'pods' ),
				'default'    => 0,
				'type'       => 'boolean',
				'dependency' => true,
			],
			[
				'name'       => 'restrict_redirect_login',
				'label'      => __( 'Redirect to WP Login page', 'pods' ),
				'default'    => 0,
				'type'       => 'boolean',
				'dependency' => true,
				'depends-on' => [
					'pods_meta_restrict_redirect' => true,
				],
			],
			[
				'name'       => 'restrict_redirect_url',
				'label'      => __( 'Redirect to a Custom URL', 'pods' ),
				'default'    => '',
				'type'       => 'text',
				'depends-on' => [
					'pods_meta_restrict_redirect'       => true,
					'pods_meta_restrict_redirect_login' => false,
				],
			],
		];

		pods_register_group(
			[
				'name'              => 'pod-page',
				'label'             => __( 'Page', 'pods' ),
				'description'       => '',
				'weight'            => 0,
				'meta_box_context'  => 'normal',
				'meta_box_priority' => 'high',
			],
			$this->object_type,
			$page_fields
		);

		pods_register_group(
			[
				'name'              => 'pod-association',
				'label'             => __( 'Pod Association', 'pods' ),
				'description'       => '',
				'weight'            => 1,
				'meta_box_context'  => 'normal',
				'meta_box_priority' => 'high',
			],
			$this->object_type,
			$association_fields
		);

		pods_register_group(
			[
				'name'              => 'restrict-content',
				'label'             => __( 'Restrict Content', 'pods' ),
				'description'       => '',
				'weight'            => 2,
				'meta_box_context'  => 'normal',
				'meta_box_priority' => 'high',
			],
			$this->object_type,
			$restrict_fields
		);
	}

	/**
	 * @param $caps
	 *
	 * @return array
	 */
	public function get_capabilities( $caps ) {

		$caps = array_merge(
			$caps, array(
				'edit_' . $this->capability_type,
				'read_' . $this->capability_type,
				'delete_' . $this->capability_type,
				'edit_' . $this->capability_type . 's',
				'edit_others_' . $this->capability_type . 's',
				'publish_' . $this->capability_type . 's',
				'read_private_' . $this->capability_type . 's',
				'edit_' . $this->capability_type . 's',
			)
		);

		return $caps;
	}

	/**
	 * Pod Page Content Shortcode support for use anywhere that supports WP Shortcodes
	 *
	 * @param array  $tags    An associative array of shortcode properties
	 * @param string $content Not currently used
	 *
	 * @return string
	 * @since 2.3.9
	 */
	public function shortcode( $tags, $content = null ) {

		if ( ! isset( $tags['page'] ) || empty( $tags['page'] ) ) {
			$tags['page'] = null;
		}

		$pods_page = self::exists( $tags['page'] );

		if ( empty( $pods_page ) ) {
			return '<p>Pods Page not found</p>';
		}

		return self::content( true, $pods_page );
	}

	/**
	 * Disable this Post Type from appearing in the Builder layouts list
	 *
	 * @param array $post_types
	 *
	 * @return array
	 */
	public function disable_builder_layout( $post_types ) {

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
	public function setup_updated_messages( $messages ) {

		global $post, $post_ID;

		$post_type = get_post_type_object( $this->object_type );

		$labels = $post_type->labels;

		$messages[ $post_type->name ] = array(
			1  => sprintf( __( '%1$s updated. <a href="%2$s">%3$s</a>', 'pods' ), $labels->singular_name, esc_url( get_permalink( $post_ID ) ), $labels->view_item ),
			2  => __( 'Custom field updated.', 'pods' ),
			3  => __( 'Custom field deleted.', 'pods' ),
			4  => sprintf( __( '%s updated.', 'pods' ), $labels->singular_name ),
			/* translators: %s: date and time of the revision */
			5  => isset( $_GET['revision'] ) ? sprintf( __( '%1$s restored to revision from %2$s', 'pods' ), $labels->singular_name, wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6  => sprintf( __( '%1$s published. <a href="%2$s">%3$s</a>', 'pods' ), $labels->singular_name, esc_url( get_permalink( $post_ID ) ), $labels->view_item ),
			7  => sprintf( __( '%s saved.', 'pods' ), $labels->singular_name ),
			8  => sprintf( __( '%1$s submitted. <a target="_blank" rel="noopener noreferrer" href="%2$s">Preview %3$s</a>', 'pods' ), $labels->singular_name, esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ), $labels->singular_name ),
			9  => sprintf(
				__( '%1$s scheduled for: <strong>%2$s</strong>. <a target="_blank" rel="noopener noreferrer" href="%3$s">Preview %4$s</a>', 'pods' ), $labels->singular_name,
				// translators: Publish box date format, see http://php.net/date
				date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ), $labels->singular_name
			),
			10 => sprintf( __( '%1$s draft updated. <a target="_blank" rel="noopener noreferrer" href="%2$s">Preview %3$s</a>', 'pods' ), $labels->singular_name, esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ), $labels->singular_name ),
		);

		if ( false === (boolean) $post_type->public ) {
			$messages[ $post_type->name ][1] = sprintf( __( '%s updated.', 'pods' ), $labels->singular_name );
			$messages[ $post_type->name ][6] = sprintf( __( '%s published.', 'pods' ), $labels->singular_name );
			$messages[ $post_type->name ][8] = sprintf( __( '%s submitted.', 'pods' ), $labels->singular_name );
			$messages[ $post_type->name ][9] = sprintf(
				__( '%1$s scheduled for: <strong>%2$s</strong>.', 'pods' ), $labels->singular_name,
				// translators: Publish box date format, see http://php.net/date
				date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) )
			);
			$messages[ $post_type->name ][10] = sprintf( __( '%s draft updated.', 'pods' ), $labels->singular_name );
		}

		return $messages;
	}

	/**
	 * Enqueue styles
	 *
	 * @since 2.0.0
	 */
	public function admin_assets() {
		wp_enqueue_script( 'pods-dfv' );
		wp_enqueue_style( 'pods-styles' );
	}

	/**
	 * Fix filters, specifically removing balanceTags
	 *
	 * @since 2.0.1
	 *
	 * @param      $data
	 * @param null $pod
	 * @param null $id
	 * @param null $groups
	 * @param null $post
	 */
	public function fix_filters( $data, $pod = null, $id = null, $groups = null, $post = null ) {

		remove_filter( 'content_save_pre', 'balanceTags', 50 );
	}

	/**
	 * Remove unused row actions
	 *
	 * @since 2.0.5
	 *
	 * @param $actions
	 * @param $post
	 *
	 * @return
	 */
	public function remove_row_actions( $actions, $post ) {

		global $current_screen;

		if ( ! is_object( $current_screen ) || $this->object_type != $current_screen->post_type ) {
			return $actions;
		}

		if ( isset( $actions['view'] ) ) {
			unset( $actions['view'] );
		}

		if ( isset( $actions['inline hide-if-no-js'] ) ) {
			unset( $actions['inline hide-if-no-js'] );
		}

		// W3 Total Cache
		if ( isset( $actions['pgcache_purge'] ) ) {
			unset( $actions['pgcache_purge'] );
		}

		return $actions;
	}

	/**
	 * Remove unused bulk actions
	 *
	 * @since 2.0.5
	 *
	 * @param $actions
	 *
	 * @return
	 */
	public function remove_bulk_actions( $actions ) {

		if ( isset( $actions['edit'] ) ) {
			unset( $actions['edit'] );
		}

		return $actions;
	}

	/**
	 * Clear cache on save
	 *
	 * @since 2.0.0
	 *
	 * @param      $data
	 * @param null $pod
	 * @param null $id
	 * @param null $groups
	 * @param null $post
	 */
	public function clear_cache( $data, $pod = null, $id = null, $groups = null, $post = null ) {

		$old_post = $id;

		if ( ! is_object( $id ) ) {
			$old_post = null;
		}

		if ( ! is_array( $data ) && 0 < $data ) {
			$post = $data;
			$post = get_post( $post );
		}

		if ( ! is_object( $post ) || $this->object_type !== $post->post_type ) {
			return;
		}

		pods_transient_clear( 'pods_object_pages' );

		if ( $old_post instanceof WP_Post && $this->object_type === $old_post->post_type ) {
			pods_cache_clear( $old_post->post_title, 'pods_object_page_wildcard' );
		}

		pods_cache_clear( $post->post_title, 'pods_object_page_wildcard' );

		self::flush_rewrites();
	}

	/**
	 * Change post title placeholder text
	 *
	 * @since 2.0.0
	 *
	 * @param $text
	 * @param $post
	 *
	 * @return string|void
	 */
	public function set_title_text( $text, $post ) {
		return __( 'Enter URL here', 'pods' );
	}

	/**
	 * Edit page form
	 *
	 * @since 2.0.0
	 */
	public function edit_page_form() {

		global $post_type;

		if ( $this->object_type !== $post_type ) {
			return;
		}

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ), 21 );
		add_filter( 'enter_title_here', array( $this, 'set_title_text' ), 10, 2 );
	}

	/**
	 * Filter permalinks and adjust for pod pages
	 *
	 * @param $post_link
	 * @param $post
	 *
	 * @return mixed
	 */
	public function post_type_link( $post_link, $post ) {

		if ( empty( $post ) || $this->object_type != $post->post_type ) {
			return $post_link;
		}

		$post_link = get_site_url() . '/';

		if ( false === strpos( $post->post_title, '*' ) ) {
			$post_link .= trim( $post->post_title, '/ ' ) . '/';
		}

		return $post_link;
	}

	/**
	 * Get the fields
	 *
	 * @param null   $_null
	 * @param int    $post_ID
	 * @param string $meta_key
	 * @param bool   $single
	 *
	 * @return array|bool|int|mixed|null|string|void
	 */
	public function get_meta( $_null, $post_ID = null, $meta_key = null, $single = false ) {
		if ( 'code' !== $meta_key ) {
			return $_null;
		}

		$post = get_post( $post_ID );

		if ( ! is_object( $post ) || $this->object_type !== $post->post_type ) {
			return $_null;
		}

		return $post->post_content;
	}

	/**
	 * Save the fields
	 *
	 * @param        $_null
	 * @param int    $post_ID
	 * @param string $meta_key
	 * @param string $meta_value
	 *
	 * @return bool|int|null
	 */
	public function save_meta( $_null, $post_ID = null, $meta_key = null, $meta_value = null ) {
		if ( 'code' !== $meta_key ) {
			return $_null;
		}

		$post = get_post( $post_ID );

		if ( ! is_object( $post ) || $this->object_type !== $post->post_type ) {
			return $_null;
		}

		$postdata = array(
			'ID'           => $post_ID,
			'post_content' => $meta_value,
		);

		remove_filter( current_filter(), array( $this, __FUNCTION__ ) );

		$revisions = false;

		if ( has_action( 'pre_post_update', 'wp_save_post_revision' ) ) {
			remove_action( 'pre_post_update', 'wp_save_post_revision' );

			$revisions = true;
		}

		wp_update_post( (object) $postdata );

		// Flush the find posts cache.
		pods_cache_clear( true, 'pods_post_type_storage_' . $this->object_type );

		// objects will be automatically sanitized
		if ( $revisions ) {
			add_action( 'pre_post_update', 'wp_save_post_revision' );
		}

		return true;
	}

	/**
	 * Flush Pod Page Rewrite cache
	 *
	 * @return array Pod Page Rewrites
	 *
	 * @since 2.3.4
	 */
	public static function flush_rewrites() {

		$args = array(
			'post_type'      => '_pods_page',
			'nopaging'       => true,
			'posts_per_page' => - 1,
			'post_status'    => 'publish',
			'order'          => 'ASC',
			'orderby'        => 'title',
		);

		$pod_pages = get_posts( $args );

		$pod_page_rewrites = array();

		foreach ( $pod_pages as $pod_page ) {
			$pod_page_rewrites[ $pod_page->ID ] = $pod_page->post_title;
		}

		uksort( $pod_page_rewrites, 'pods_page_length_sort' );

		pods_transient_set( 'pods_object_page_rewrites', $pod_page_rewrites, WEEK_IN_SECONDS );

		$pod_page_rewrites = array_flip( $pod_page_rewrites );

		return $pod_page_rewrites;
	}

	/**
	 * Check to see if Pod Page exists and return data
	 *
	 * $uri not required, if NULL then returns REQUEST_URI matching Pod Page
	 *
	 * @param string $uri The Pod Page URI to check if exists
	 *
	 * @return array|bool
	 */
	public static function exists( $uri = null ) {
		if ( null === $uri ) {
			$uri = pods_current_path();
		}

		if ( empty( $uri ) ) {
			return false;
		}

		$uri = explode( '?', $uri );
		$uri = explode( '#', $uri[0] );
		$uri = $uri[0];

		$home_path = wp_parse_url( get_home_url(), PHP_URL_PATH );

		if ( ! empty( $home_path ) && '/' !== $home_path ) {
			$uri = substr( $uri, strlen( $home_path ) );
		}

		$uri       = trim( $uri, '/' );
		$uri_depth = count( array_filter( explode( '/', $uri ) ) ) - 1;

		$pods_page_exclusions = array(
			'wp-admin',
			'wp-content',
			'wp-includes',
			'index.php',
			'wp-login.php',
			'wp-signup.php',
		);

		$pods_page_exclusions = apply_filters( 'pods_page_exclusions', $pods_page_exclusions );

		if ( is_admin() || empty( $uri ) ) {
			return false;
		}

		foreach ( $pods_page_exclusions as $exclusion ) {
			if ( 0 === strpos( $uri, $exclusion ) ) {
				return false;
			}
		}

		$object = apply_filters( 'pods_page_exists', false, $uri );
		if ( ! empty( $object ) ) {
			return $object;
		}

		if ( false === strpos( $uri, '*' ) && ! apply_filters( 'pods_page_regex_matching', false ) ) {
			$object = pods_by_title( $uri, ARRAY_A, '_pods_page', 'publish' );
		}

		$wildcard = false;

		if ( empty( $object ) ) {
			if ( false === strpos( $uri, '*' ) ) {
				$object = pods_cache_get( $uri, 'pods_object_page_wildcard' );

				if ( ! empty( $object ) ) {
					return $object;
				}
			}

			$pod_page_rewrites = pods_transient_get( 'pods_object_page_rewrites' );

			if ( empty( $pod_page_rewrites ) ) {
				$pod_page_rewrites = self::flush_rewrites();
			} else {
				$pod_page_rewrites = array_flip( $pod_page_rewrites );
			}

			$found_rewrite_page_id = 0;

			if ( ! empty( $pod_page_rewrites ) ) {
				foreach ( $pod_page_rewrites as $pod_page => $pod_page_id ) {
					if ( ! apply_filters( 'pods_page_regex_matching', false ) ) {
						if ( false === strpos( $pod_page, '*' ) ) {
							continue;
						}

						$depth_check = strlen( $pod_page ) - strlen( str_replace( '/', '', $pod_page ) );

						$pod_page = preg_quote( $pod_page, '/' );

						$pod_page = str_replace( '\\*', '(.*)', $pod_page );

						if ( $uri_depth == $depth_check && preg_match( '/^' . $pod_page . '$/', $uri ) ) {
							$found_rewrite_page_id = $pod_page_id;

							break;
						}
					} elseif ( preg_match( '/^' . str_replace( '/', '\\/', $pod_page ) . '$/', $uri ) ) {
						$found_rewrite_page_id = $pod_page_id;

						break;
					}//end if
				}//end foreach

				if ( ! empty( $found_rewrite_page_id ) ) {
					$object = get_post( $found_rewrite_page_id, ARRAY_A );

					if ( empty( $object ) || '_pods_page' !== $object['post_type'] ) {
						$object = false;
					}
				}
			}//end if

			$wildcard = true;
		}//end if

		if ( ! empty( $object ) ) {
			$object = array(
				'id'            => $object['ID'],
				'uri'           => $object['post_title'],
				'code'          => $object['post_content'],
				'phpcode'       => $object['post_content'],
				// phpcode is deprecated
				'precode'       => get_post_meta( $object['ID'], 'precode', true ),
				'page_template' => get_post_meta( $object['ID'], 'page_template', true ),
				'title'         => get_post_meta( $object['ID'], 'page_title', true ),
				'options'       => array(
					'admin_only'              => (boolean) get_post_meta( $object['ID'], 'admin_only', true ),
					'restrict_role'           => (boolean) get_post_meta( $object['ID'], 'restrict_role', true ),
					'restrict_capability'     => (boolean) get_post_meta( $object['ID'], 'restrict_capability', true ),
					'roles_allowed'           => get_post_meta( $object['ID'], 'roles_allowed', true ),
					'capability_allowed'      => get_post_meta( $object['ID'], 'capability_allowed', true ),
					'restrict_redirect'       => (boolean) get_post_meta( $object['ID'], 'restrict_redirect', true ),
					'restrict_redirect_login' => (boolean) get_post_meta( $object['ID'], 'restrict_redirect_login', true ),
					'restrict_redirect_url'   => get_post_meta( $object['ID'], 'restrict_redirect_url', true ),
					'pod'                     => get_post_meta( $object['ID'], 'pod', true ),
					'pod_slug'                => get_post_meta( $object['ID'], 'pod_slug', true ),
				),
			);

			if ( $wildcard ) {
				pods_cache_set( $uri, $object, 'pods_object_page_wildcard', 3600 );
			}

			return $object;
		}//end if

		return false;
	}

	/**
	 * Check if a Pod Page exists
	 */
	public function page_check() {

		if ( self::$checked ) {
			return;
		}

		global $pods;

		// Fix any global confusion wherever this runs
		if ( isset( $pods ) && ! isset( $GLOBALS['pods'] ) ) {
			$GLOBALS['pods'] =& $pods;
		} elseif ( ! isset( $pods ) && isset( $GLOBALS['pods'] ) ) {
			$pods =& $GLOBALS['pods'];
		}

		if ( ! defined( 'PODS_DISABLE_POD_PAGE_CHECK' ) || ! PODS_DISABLE_POD_PAGE_CHECK ) {
			if ( null === self::$exists ) {
				self::$exists = pod_page_exists();
			}

			if ( false !== self::$exists ) {
				$pods = apply_filters( 'pods_global', $pods, self::$exists );

				if ( ! is_wp_error( $pods ) && ( is_object( $pods ) || 404 != $pods ) ) {
					add_action( 'template_redirect', array( $this, 'template_redirect' ) );
					add_filter( 'redirect_canonical', '__return_false' );
					add_action( 'wp_head', array( $this, 'wp_head' ) );
					add_filter( 'wp_title', array( $this, 'wp_title' ), 0, 3 );
					add_filter( 'body_class', array( $this, 'body_class' ), 0, 1 );
					add_filter( 'status_header', array( $this, 'status_header' ) );
					add_action( 'after_setup_theme', array( $this, 'precode' ) );
					add_action( 'wp', array( $this, 'silence_404' ), 1 );

					// Genesis theme integration.
					add_action( 'genesis_loop', 'pods_content', 11 );
				}
			}

			self::$checked = true;
		}//end if
	}

	/**
	 * Output Pod Page Content
	 *
	 * @param bool $return Whether to return or not (default is to echo)
	 *
	 * @param bool $pods_page
	 *
	 * @return string
	 */
	public static function content( $return = false, $pods_page = false ) {

		if ( empty( $pods_page ) ) {
			$pods_page = self::$exists;
		}

		$content = false;

		if ( $pods_page == self::$exists && self::$content_called ) {
			return $content;
		}

		if ( ! empty( $pods_page ) ) {
			/**
			 * @var $pods \Pods
			 */
			global $pods;

			// Fix any global confusion wherever this runs
			if ( isset( $pods ) && ! isset( $GLOBALS['pods'] ) ) {
				$GLOBALS['pods'] =& $pods;
			} elseif ( ! isset( $pods ) && isset( $GLOBALS['pods'] ) ) {
				$pods =& $GLOBALS['pods'];
			}

			if ( 0 < strlen( trim( $pods_page['code'] ) ) ) {
				$content = trim( $pods_page['code'] );
			}

			ob_start();

			do_action( 'pods_content_pre', $pods_page, $content );

			if ( 0 < strlen( $content ) ) {
				if ( false !== strpos( $content, '<?' ) && ( ! defined( 'PODS_DISABLE_EVAL' ) || ! PODS_DISABLE_EVAL ) ) {
					pods_deprecated( 'Pod Page PHP code has been deprecated, please use WP Page Templates or hook into the pods_content filter instead of embedding PHP.', '2.1' );

					eval( "?>$content" );
				} elseif ( is_object( $pods ) && ! empty( $pods->id ) ) {
					echo $pods->do_magic_tags( $content );
				} else {
					echo $content;
				}
			}

			do_action( 'pods_content_post', $pods_page, $content );

			$content = ob_get_clean();

			if ( $pods_page == self::$exists ) {
				self::$content_called = true;
			}
		}//end if

		$content = apply_filters( 'pods_content', $content, $pods_page );

		if ( $return ) {
			return $content;
		}

		echo $content;
	}

	/**
	 * Run any precode for current Pod Page
	 */
	public function precode() {

		global $pods;

		// Fix any global confusion wherever this runs
		if ( isset( $pods ) && ! isset( $GLOBALS['pods'] ) ) {
			$GLOBALS['pods'] =& $pods;
		} elseif ( ! isset( $pods ) && isset( $GLOBALS['pods'] ) ) {
			$pods =& $GLOBALS['pods'];
		}

		if ( false !== self::$exists ) {
			$permission = pods_permission( self::$exists );

			$permission = (boolean) apply_filters( 'pods_pages_permission', $permission, self::$exists );

			if ( $permission ) {
				$content = false;

				if ( ! is_object( $pods ) && 404 != $pods && 0 < strlen( (string) pods_var( 'pod', self::$exists['options'] ) ) ) {
					$slug = pods_var_raw( 'pod_slug', self::$exists['options'], null, null, true );

					$has_slug = 0 < strlen( $slug );

					// Handle special magic tags
					if ( $has_slug ) {
						$slug = pods_evaluate_tags( $slug, true );
					}

					$pods = pods_get_instance( pods_var( 'pod', self::$exists['options'] ), $slug );

					// Auto 404 handling if item doesn't exist
					if ( $has_slug && ( empty( $slug ) || ! $pods->exists() ) && apply_filters( 'pods_pages_auto_404', true, $slug, $pods, self::$exists ) ) {
						$pods = 404;
					}
				}

				if ( 0 < strlen( trim( self::$exists['precode'] ) ) ) {
					$content = self::$exists['precode'];
				}

				if ( false !== $content && ( ! defined( 'PODS_DISABLE_EVAL' ) || ! PODS_DISABLE_EVAL ) ) {
					pods_deprecated( 'Pod Page Precode has been deprecated, please use WP Page Templates or hook into the pods_content filter instead of embedding PHP.', '2.1' );

					eval( "?>$content" );
				}

				do_action( 'pods_page_precode', self::$exists, $pods, $content );
			} elseif ( self::$exists['options']['restrict_redirect'] ) {
				$redirect_url = '';

				if ( self::$exists['options']['restrict_redirect_login'] ) {
					$redirect_url = wp_login_url( pods_current_url() );
				} elseif ( ! empty( self::$exists['options']['restrict_redirect_url'] ) ) {
					$redirect_url = self::$exists['options']['restrict_redirect_url'];
				}

				if ( ! empty( $redirect_url ) ) {
					wp_redirect( $redirect_url );
					die();
				}
			}//end if

			if ( ! $permission || ( ! is_object( $pods ) && ( 404 == $pods || is_wp_error( $pods ) ) ) ) {
				remove_action( 'template_redirect', array( $this, 'template_redirect' ) );
				remove_action( 'wp_head', array( $this, 'wp_head' ) );
				remove_filter( 'redirect_canonical', '__return_false' );
				remove_filter( 'wp_title', array( $this, 'wp_title' ) );
				remove_filter( 'body_class', array( $this, 'body_class' ) );
				remove_filter( 'status_header', array( $this, 'status_header' ) );
				remove_action( 'wp', array( $this, 'silence_404' ), 1 );
			}
		}//end if
	}

	/**
	 *
	 */
	public function wp_head() {

		global $pods;

		do_action( 'pods_wp_head' );

		if ( ! defined( 'PODS_DISABLE_VERSION_OUTPUT' ) || ! PODS_DISABLE_VERSION_OUTPUT ) {
			?>
			<!-- Pods Framework <?php echo esc_html( PODS_VERSION ); ?> -->
			<?php
		}
		if ( ( ! defined( 'PODS_DISABLE_META' ) || ! PODS_DISABLE_META ) && is_object( $pods ) && ! is_wp_error( $pods ) ) {

			if ( isset( $pods->meta ) && is_array( $pods->meta ) && ! empty( $pods->meta ) ) {
				foreach ( $pods->meta as $name => $content ) {
					if ( 'title' === $name ) {
						continue;
					}
					?>
					<meta name="<?php echo esc_attr( $name ); ?>" content="<?php echo esc_attr( $content ); ?>" />
					<?php
				}
			}

			if ( isset( $pods->meta_properties ) && is_array( $pods->meta_properties ) && ! empty( $pods->meta_properties ) ) {
				foreach ( $pods->meta_properties as $property => $content ) {
					?>
					<meta property="<?php echo esc_attr( $property ); ?>" content="<?php echo esc_attr( $content ); ?>" />
					<?php
				}
			}

			if ( isset( $pods->meta_extra ) && 0 < strlen( $pods->meta_extra ) ) {
				echo $pods->meta_extra;
			}
		}//end if
	}

	/**
	 * @param $title
	 * @param $sep
	 * @param $seplocation
	 *
	 * @return mixed|void
	 */
	public function wp_title( $title, $sep, $seplocation ) {

		global $pods;

		$page_title = trim( self::$exists['title'] );

		if ( 0 < strlen( $page_title ) ) {
			if ( is_object( $pods ) && ! is_wp_error( $pods ) ) {
				$page_title = $pods->do_magic_tags( $page_title );
			}

			$title = ( 'right' === $seplocation ) ? "{$page_title} {$sep} " : " {$sep} {$page_title}";
		} elseif ( strlen( trim( $title ) ) < 1 ) {
			$uri = explode( '?', $_SERVER['REQUEST_URI'] );
			$uri = preg_replace( '@^([/]?)(.*?)([/]?)$@', '$2', $uri[0] );
			$uri = preg_replace( '@(-|_)@', ' ', $uri );
			$uri = explode( '/', $uri );

			$title = '';

			foreach ( $uri as $key => $page_title ) {
				$title .= ( 'right' === $seplocation ) ? ucwords( $page_title ) . " {$sep} " : " {$sep} " . ucwords( $page_title );
			}
		}

		if ( ( ! defined( 'PODS_DISABLE_META' ) || ! PODS_DISABLE_META ) && is_object( $pods ) && ! is_wp_error( $pods ) && isset( $pods->meta ) && is_array( $pods->meta ) && isset( $pods->meta['title'] ) ) {
			$title = $pods->meta['title'];
		}

		return apply_filters( 'pods_title', $title, $sep, $seplocation, self::$exists );
	}

	/**
	 * @param $classes
	 *
	 * @return mixed|void
	 */
	public function body_class( $classes ) {

		global $pods;

		if ( defined( 'PODS_DISABLE_BODY_CLASSES' ) && PODS_DISABLE_BODY_CLASSES ) {
			return $classes;
		}

		$classes[] = 'pods';

		$uri = explode( '?', self::$exists['uri'] );
		$uri = explode( '#', $uri[0] );

		$class = str_replace( array( '*', '/' ), array( '_w_', '-' ), $uri[0] );
		$class = sanitize_title( $class );
		$class = str_replace( array( '_', '--', '--' ), '-', $class );
		$class = trim( $class, '-' );

		$classes[] = 'pod-page-' . $class;

		if ( is_object( $pods ) && ! is_wp_error( $pods ) ) {
			$class     = sanitize_title( $pods->pod );
			$class     = str_replace( array( '_', '--', '--' ), '-', $class );
			$class     = trim( $class, '-' );
			$classes[] = 'pod-' . $class;
		}

		if ( is_object( $pods ) && ! is_wp_error( $pods ) && isset( $pods->body_classes ) ) {
			$classes[] = $pods->body_classes;
		}

		return apply_filters( 'pods_body_class', $classes, $uri );
	}

	/**
	 * @return string
	 */
	public function status_header() {

		return $_SERVER['SERVER_PROTOCOL'] . ' 200 OK';
	}

	/**
	 *
	 */
	public function silence_404() {

		global $wp_query;

		$wp_query->query_vars['error'] = '';
		$wp_query->is_404              = false;
	}

	/**
	 * Handle overriding the template used for a Pods Page.
	 *
	 * @since 2.8.11
	 *
	 * @param string $original_template The template to include.
	 *
	 * @return string The template to include.
	 */
	public function template_include( $original_template ) {
		global $pods;

		// Default to original template if pod page was not found.
		$template = $original_template;

		if ( false !== self::$exists ) {
			/*
			 * Create pods.php in your theme directory, and
			 * style it to suit your needs. Some helpful functions:
			 *
			 * get_header()
			 * pods_content()
			 * get_sidebar()
			 * get_footer()
			 */
			$template = self::$exists['page_template'];
			$template = apply_filters( 'pods_page_template', $template, self::$exists );

			$render_function = apply_filters( 'pods_template_redirect', null, $template, self::$exists );

			if ( '_custom' === $template ) {
				pods_content();
				die();
			} elseif ( null !== $render_function && is_callable( $render_function ) ) {
				call_user_func( $render_function, $template, self::$exists );
				die();
			} elseif ( ( ! defined( 'PODS_DISABLE_DYNAMIC_TEMPLATE' ) || ! PODS_DISABLE_DYNAMIC_TEMPLATE ) && is_object( $pods ) && ! is_wp_error( $pods ) && ! empty( $pods->page_template ) ) {
				$template = $pods->page_template;
				// found the template and included it, we're good to go!
			} elseif ( ! empty( self::$exists['page_template'] ) ) {
				$template = self::$exists['page_template'];
				// found the template and included it, we're good to go!
			} else {
				$located_template = apply_filters( 'pods_page_locate_template', $template, self::$exists );

				if ( $template !== $located_template ) {
					$template = $located_template;
				} else {
					$default_templates = array();

					$uri = explode( '?', self::$exists['uri'] );
					$uri = explode( '#', $uri[0] );

					$page_path = explode( '/', $uri[0] );

					while ( $last = array_pop( $page_path ) ) {
						$file_name = str_replace( '*', '-w-', implode( '/', $page_path ) . '/' . $last );
						$sanitized = sanitize_title( $file_name );

						$default_templates[] = 'pods/' . trim( str_replace( '--', '-', $sanitized ), ' -' ) . '.php';
						$default_templates[] = 'pods-' . trim( str_replace( '--', '-', $sanitized ), ' -' ) . '.php';
					}

					$default_templates[] = 'pods.php';

					$default_templates = apply_filters( 'pods_page_default_templates', $default_templates );

					$template = locate_template( $default_templates );

					if ( '' !== $template ) {
						// found the template and included it, we're good to go!
					} else {
						$template = false;

						// templates not found in theme, default output
						do_action( 'pods_page_default', $template, self::$exists );

						get_header();
						pods_content();
						get_sidebar();
						get_footer();
						die();
					}
				}//end if
			}//end if
		}

		/**
		 * Allow filtering the template to include for a Pods Page.
		 *
		 * @since 2.8.11
		 *
		 * @param string $template The template to use.
		 * @param array  $exists   The Pods Page data.
		 */
		$template = apply_filters( 'pods_page_template_include', $template, self::$exists );

		// Attempt to set up a basic WP post object.
		if ( $template !== $original_template && function_exists( '\Roots\bootloader' ) && function_exists( 'resource_path' ) ) {
			$paths_to_check = [
				get_theme_file_path( '/resources/views' ),
				get_parent_theme_file_path( '/resources/views' ),
				resource_path( 'views' ),
			];

			foreach ( $paths_to_check as $path_to_check ) {
				$file_path = $path_to_check . DIRECTORY_SEPARATOR . $template;

				if ( file_exists( $file_path ) ) {
					$template = $file_path;

					break;
				}
			}
		}

		return $template;
	}

	/**
	 *
	 */
	public function template_redirect() {
		global $pods;

		// Support the Sage theme, eventually we can implement template_include everywhere else after more testing.
		if ( function_exists( '\Roots\bootloader' ) ) {
			if ( ! empty( $pods ) && 0 < $pods->id() && 'post_type' === $pods->pod_data['type'] ) {
				// Set up the post object using the pod.
				query_posts( 'p=' . $pods->id() . '&post_type=' . $pods->pod_data['name'] );

				$pod_post = get_post( $pods->id() );

				if ( $pod_post ) {
					setup_postdata( $pod_post );
				}
			} elseif ( null === get_queried_object() ) {
				// Maybe set up the post using the front page for now.
				$front_page = (int) get_option( 'page_on_front' );

				if ( 0 < $front_page ) {
					query_posts( 'page_id=' . $front_page );

					$front_page_post = get_post( $front_page );

					if ( $front_page_post ) {
						setup_postdata( $front_page_post );
					}
				}
			}

			add_filter( 'template_include', [ $this, 'template_include' ] );

			return;
		}

		if ( false !== self::$exists ) {
			/*
			 * Create pods.php in your theme directory, and
			 * style it to suit your needs. Some helpful functions:
			 *
			 * get_header()
			 * pods_content()
			 * get_sidebar()
			 * get_footer()
			 */
			$template = self::$exists['page_template'];
			$template = apply_filters( 'pods_page_template', $template, self::$exists );

			$render_function = apply_filters( 'pods_template_redirect', null, $template, self::$exists );

			do_action( 'pods_page', $template, self::$exists );

			if ( '_custom' === $template ) {
				pods_content();
			} elseif ( null !== $render_function && is_callable( $render_function ) ) {
				call_user_func( $render_function, $template, self::$exists );
			} elseif ( ( ! defined( 'PODS_DISABLE_DYNAMIC_TEMPLATE' ) || ! PODS_DISABLE_DYNAMIC_TEMPLATE ) && is_object( $pods ) && ! is_wp_error( $pods ) && isset( $pods->page_template ) && ! empty( $pods->page_template ) && '' != locate_template( array( $pods->page_template ), true ) ) {
				$template = $pods->page_template;
				// found the template and included it, we're good to go!
			} elseif ( ! empty( self::$exists['page_template'] ) && '' != locate_template( array( self::$exists['page_template'] ), true ) ) {
				$template = self::$exists['page_template'];
				// found the template and included it, we're good to go!
			} else {
				$located_template = apply_filters( 'pods_page_locate_template', $template, self::$exists );

				if ( $template !== $located_template ) {
					$template = $located_template;
				} else {
					$default_templates = array();

					$uri = explode( '?', self::$exists['uri'] );
					$uri = explode( '#', $uri[0] );

					$page_path = explode( '/', $uri[0] );

					while ( $last = array_pop( $page_path ) ) {
						$file_name = str_replace( '*', '-w-', implode( '/', $page_path ) . '/' . $last );
						$sanitized = sanitize_title( $file_name );

						$default_templates[] = 'pods/' . trim( str_replace( '--', '-', $sanitized ), ' -' ) . '.php';
						$default_templates[] = 'pods-' . trim( str_replace( '--', '-', $sanitized ), ' -' ) . '.php';
					}

					$default_templates[] = 'pods.php';

					$default_templates = apply_filters( 'pods_page_default_templates', $default_templates );

					$template = locate_template( $default_templates, true );

					if ( '' !== $template ) {
						// found the template and included it, we're good to go!
					} else {
						$template = false;

						// templates not found in theme, default output
						do_action( 'pods_page_default', $template, self::$exists );

						get_header();
						pods_content();
						get_sidebar();
						get_footer();
					}
				}//end if
			}//end if

			do_action( 'pods_page_end', $template, self::$exists );

			exit;
		}//end if
	}
}

/**
 * Find out if the current page is a Pod Page
 *
 * @param string $uri The Pod Page URI to check if currently on
 *
 * @return bool
 * @since 1.7.5
 */
function is_pod_page( $uri = null ) {

	if ( false !== Pods_Pages::$exists && ( null === $uri || $uri == Pods_Pages::$exists['uri'] || $uri == Pods_Pages::$exists['id'] ) ) {
		return true;
	}

	return false;
}

/**
 * Check for a specific page template for the current pod page
 *
 * @param string $template The theme template file
 *
 * @return bool
 * @since 2.3.7
 */
function is_pod_page_template( $template = null ) {

	if ( false !== Pods_Pages::$exists && $template == Pods_Pages::$exists['page_template'] ) {
		return true;
	}

	return false;
}

/**
 * Get the current Pod Page URI
 *
 * @return string|bool
 * @since 2.3.3
 */
function get_pod_page_uri() {

	$pod_page = Pods_Pages::exists();

	if ( ! empty( $pod_page ) ) {
		return $pod_page['uri'];
	}

	return false;
}

/**
 * Check to see if Pod Page exists and return data
 *
 * $uri not required, if NULL then returns REQUEST_URI matching Pod Page
 *
 * @param string $uri The Pod Page URI to check if exists
 *
 * @return array
 *
 * @since 1.7.5
 */
function pod_page_exists( $uri = null ) {
	return Pods_Pages::exists( $uri );
}

/**
 * Output Pod Page Content
 *
 * @param bool $return Whether to return or not (default is to echo)
 *
 * @param bool $pods_page
 *
 * @return string
 * @since 1.7.0
 */
function pods_content( $return = false, $pods_page = false ) {

	return Pods_Pages::content( $return, $pods_page );
}

/**
 * Sort an array by length of items, descending, for use with uksort()
 *
 * @param string $a First array item
 * @param string $b Second array item
 *
 * @return int Length difference
 *
 * @since 2.3.4
 */
function pods_page_length_sort( $a, $b ) {

	return strlen( $b ) - strlen( $a );
}

/**
 * Flush Pod Page Rewrite cache
 *
 * @return array Pod Page Rewrites
 *
 * @since 2.3.4
 */
function pods_page_flush_rewrites() {

	return Pods_Pages::flush_rewrites();
}

/*
 * Deprecated global variable
 */
$GLOBALS['pod_page_exists'] =& Pods_Pages::$exists;
