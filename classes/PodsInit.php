<?php
/**
 * @package Pods
 */
class PodsInit {

    /**
     * @var PodsInit
     */
    static $instance = null;

    /**
     * @var array
     */
    static $no_conflict = array();

    /**
     * @var array
     */
    static $content_types_registered = array();

    /**
     * @var PodsComponents
     */
    static $components;

    /**
     * @var PodsMeta
     */
    static $meta;

    /**
     * @var PodsAdmin
     */
    static $admin;

    /**
     * @var mixed|void
     */
    static $version;

    /**
     * @var mixed|void
     */
    static $version_last;

    /**
     * @var mixed|void
     */
    static $db_version;

    /**
     * Upgrades to trigger (last installed version => upgrade version)
     *
     * @var array
     */
    static $upgrades = array(
        '1.0.0' => '2.0.0'
        //'2.0.0' => '2.1.0'
    );

    /**
     * Whether an Upgrade for 1.x has happened
     *
     * @var bool
     */
    static $upgraded;

    /**
     * Whether an Upgrade is needed
     *
     * @var bool
     */
    static $upgrade_needed = false;

    /**
     * Singleton handling for a basic pods_init() request
     *
     * @return \PodsInit
     *
     * @since 2.3.5
     */
    public static function init () {
        if ( !is_object( self::$instance ) )
            self::$instance = new PodsInit();

        return self::$instance;
    }

    /**
     * Setup and Initiate Pods
     *
     * @return \PodsInit
     *
     * @license http://www.gnu.org/licenses/gpl-2.0.html
     * @since 1.8.9
     */
    function __construct () {
        self::$version = get_option( 'pods_framework_version' );
        self::$version_last = get_option( 'pods_framework_version_last' );
        self::$db_version = get_option( 'pods_framework_db_version' );
        self::$upgraded = (int) get_option( 'pods_framework_upgraded_1_x' );

        if ( empty( self::$version_last ) && 0 < strlen( get_option( 'pods_version' ) ) ) {
            $old_version = get_option( 'pods_version' );

            if ( !empty( $old_version ) ) {
                if ( false === strpos( $old_version, '.' ) )
                    $old_version = pods_version_to_point( $old_version );

                update_option( 'pods_framework_version_last', $old_version );

                self::$version_last = $old_version;
            }
        }

        self::$upgrade_needed = $this->needs_upgrade();

        add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );

        add_action( 'init', array( $this, 'activate_install' ), 9 );
        add_action( 'wp_loaded', array( $this, 'flush_rewrite_rules' ) );

        if ( !empty( self::$version ) ) {
	        $this->run();
        }
    }

    /**
     * Load the plugin textdomain and set default constants
     */
    public function plugins_loaded () {
        if ( !defined( 'PODS_LIGHT' ) )
            define( 'PODS_LIGHT', false );

        if ( !defined( 'PODS_TABLELESS' ) )
            define( 'PODS_TABLELESS', false );

        load_plugin_textdomain( 'pods', false, dirname( plugin_basename( PODS_DIR . 'init.php' ) ) . '/languages/' );
    }

    /**
     * Load Pods Components
     */
    public function load_components () {
        if ( !defined( 'PODS_LIGHT' ) || !PODS_LIGHT )
            self::$components = pods_components();
    }

    /**
     * Load Pods Meta
     */
    public function load_meta () {
        self::$meta = pods_meta()->core();
    }

    /**
     * Set up the Pods core
     */
    public function core () {
        // Session start
		pods_session_start();

        add_shortcode( 'pods', 'pods_shortcode' );
        add_shortcode( 'pods-form', 'pods_shortcode_form' );

        $security_settings = array(
            'pods_disable_file_browser' => 0,
            'pods_files_require_login' => 1,
            'pods_files_require_login_cap' => '',
            'pods_disable_file_upload' => 0,
            'pods_upload_require_login' => 1,
            'pods_upload_require_login_cap' => ''
        );

        foreach ( $security_settings as $security_setting => $setting ) {
            $setting = get_option( $security_setting );
            if ( !empty( $setting ) )
                $security_settings[ $security_setting ] = $setting;
        }

        foreach ( $security_settings as $security_setting => $setting ) {
            if ( 0 == $setting )
                $setting = false;
            elseif ( 1 == $setting )
                $setting = true;

            if ( in_array( $security_setting, array( 'pods_files_require_login', 'pods_upload_require_login' ) ) ) {
                if ( 0 < strlen( $security_settings[ $security_setting . '_cap' ] ) )
                    $setting = $security_settings[ $security_setting . '_cap' ];
            }
            elseif ( in_array( $security_setting, array( 'pods_files_require_login_cap', 'pods_upload_require_login_cap' ) ) )
                continue;

            if ( !defined( strtoupper( $security_setting ) ) )
                define( strtoupper( $security_setting ), $setting );
        }

        $this->register_pods();

        $avatar = PodsForm::field_loader( 'avatar' );

        if ( method_exists( $avatar, 'get_avatar' ) )
            add_filter( 'get_avatar', array( $avatar, 'get_avatar' ), 10, 4 );
    }

    /**
     * Register Scripts and Styles
     */
    public function register_assets () {
        if ( !wp_style_is( 'jquery-ui', 'registered' ) )
            wp_register_style( 'jquery-ui', PODS_URL . 'ui/css/smoothness/jquery-ui.custom.css', array(), '1.8.16' );

        wp_register_script( 'pods-json', PODS_URL . 'ui/js/jquery.json.js', array( 'jquery' ), '2.3' );

	    if ( ! wp_style_is( 'jquery-qtip2', 'registered' ) ) {
		    wp_register_style( 'jquery-qtip2', PODS_URL . 'ui/css/jquery.qtip.min.css', array(), '2.2' );
	    }

	    if ( ! wp_script_is( 'jquery-qtip2', 'registered' ) ) {
		    wp_register_script( 'jquery-qtip2', PODS_URL . 'ui/js/jquery.qtip.min.js', array( 'jquery' ), '2.2' );
	    }

        wp_register_script( 'pods', PODS_URL . 'ui/js/jquery.pods.js', array( 'jquery', 'pods-json', 'jquery-qtip2' ), PODS_VERSION );

        wp_register_style( 'pods-form', PODS_URL . 'ui/css/pods-form.css', array(), PODS_VERSION );

        wp_register_style( 'pods-cleditor', PODS_URL . 'ui/css/jquery.cleditor.css', array(), '1.3.0' );
        wp_register_script( 'pods-cleditor', PODS_URL . 'ui/js/jquery.cleditor.min.js', array( 'jquery' ), '1.3.0' );

        wp_register_style( 'pods-codemirror', PODS_URL . 'ui/css/codemirror.css', array(), '4.8' );
        wp_register_script( 'pods-codemirror', PODS_URL . 'ui/js/codemirror.js', array(), '4.8', true );
        wp_register_script( 'pods-codemirror-loadmode', PODS_URL . 'ui/js/codemirror/addon/mode/loadmode.js', array( 'pods-codemirror' ), '4.8', true );
        wp_register_script( 'pods-codemirror-overlay', PODS_URL . 'ui/js/codemirror/addon/mode/overlay.js', array( 'pods-codemirror' ), '4.8', true );
        wp_register_script( 'pods-codemirror-hints', PODS_URL . 'ui/js/codemirror/addon/mode/show-hint.js', array( 'pods-codemirror' ), '4.8', true );
        wp_register_script( 'pods-codemirror-mode-xml', PODS_URL . 'ui/js/codemirror/mode/xml/xml.js', array( 'pods-codemirror' ), '4.8', true );
        wp_register_script( 'pods-codemirror-mode-html', PODS_URL . 'ui/js/codemirror/mode/htmlmixed/htmlmixed.js', array( 'pods-codemirror' ), '4.8', true );
        wp_register_script( 'pods-codemirror-mode-css', PODS_URL . 'ui/js/codemirror/mode/css/css.js', array( 'pods-codemirror' ), '4.8', true );

        if ( !wp_style_is( 'jquery-ui-timepicker', 'registered' ) )
            wp_register_style( 'jquery-ui-timepicker', PODS_URL . 'ui/css/jquery.ui.timepicker.css', array(), '1.1.1' );

        if ( !wp_script_is( 'jquery-ui-timepicker', 'registered' ) ) {
            wp_register_script( 'jquery-ui-timepicker', PODS_URL . 'ui/js/jquery.ui.timepicker.min.js', array(
                'jquery',
                'jquery-ui-core',
                'jquery-ui-datepicker',
                'jquery-ui-slider'
            ), '1.1.1' );
        }

        wp_register_style( 'pods-attach', PODS_URL . 'ui/css/jquery.pods.attach.css', array(), PODS_VERSION );
        wp_register_script( 'pods-attach', PODS_URL . 'ui/js/jquery.pods.attach.js', array(), PODS_VERSION );

        wp_register_style( 'pods-select2', PODS_URL . 'ui/js/select2/select2.css', array(), '3.3.1' );
        wp_register_script( 'pods-select2', PODS_URL . 'ui/js/select2/select2.min.js', array( 'jquery' ), '3.3.1' );

        wp_register_script( 'pods-handlebars', PODS_URL . 'ui/js/handlebars.js', array(), '1.0.0.beta.6' );
    }

    /**
     * Register internal Post Types
     */
    public function register_pods () {
        $args = array(
            'label' => 'Pods',
            'labels' => array( 'singular_name' => 'Pod' ),
            'public' => false,
            'can_export' => false,
            'query_var' => false,
            'rewrite' => false,
            'capability_type' => 'pods_pod',
            'has_archive' => false,
            'hierarchical' => false,
            'supports' => array( 'title', 'author' ),
            'menu_icon' => 'dashicons-pods'
        );

        $args = self::object_label_fix( $args, 'post_type' );

        register_post_type( '_pods_pod', apply_filters( 'pods_internal_register_post_type_pod', $args ) );

        $args = array(
            'label' => 'Pod Fields',
            'labels' => array( 'singular_name' => 'Pod Field' ),
            'public' => false,
            'can_export' => false,
            'query_var' => false,
            'rewrite' => false,
            'capability_type' => 'pods_pod',
            'has_archive' => false,
            'hierarchical' => true,
            'supports' => array( 'title', 'editor', 'author' ),
            'menu_icon' => 'dashicons-pods'
        );

        $args = self::object_label_fix( $args, 'post_type' );

        register_post_type( '_pods_field', apply_filters( 'pods_internal_register_post_type_field', $args ) );
    }

    /**
     * Include Admin
     */
    public function admin_init () {
        self::$admin = pods_admin();
    }

    /**
     * Register Post Types and Taxonomies
     */
    public function setup_content_types ( $force = false ) {
        $post_types = PodsMeta::$post_types;
        $taxonomies = PodsMeta::$taxonomies;

        $existing_post_types = get_post_types();
        $existing_taxonomies = get_taxonomies();

        $pods_cpt_ct = pods_transient_get( 'pods_wp_cpt_ct' );

        $cpt_positions = array();

        if ( empty( $pods_cpt_ct ) && ( !empty( $post_types ) || !empty( $taxonomies ) ) )
            $force = true;
        elseif ( !empty( $pods_cpt_ct ) && empty( $pods_cpt_ct[ 'post_types' ] ) && !empty( $post_types ) )
            $force = true;
        elseif ( !empty( $pods_cpt_ct ) && empty( $pods_cpt_ct[ 'taxonomies' ] ) && !empty( $taxonomies ) )
            $force = true;

        if ( false === $pods_cpt_ct || $force ) {
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

            $pods_cpt_ct = array(
                'post_types' => array(),
                'taxonomies' => array()
            );

            $pods_post_types = $pods_taxonomies = array();
            $supported_post_types = $supported_taxonomies = array();

            foreach ( $post_types as $post_type ) {
                // Post Type exists already
                if ( isset( $pods_cpt_ct[ 'post_types' ][ $post_type[ 'name' ] ] ) )
                    continue;
                elseif ( !empty( $post_type[ 'object' ] ) && isset( $existing_post_types[ $post_type[ 'object' ] ] ) )
                    continue;
                elseif ( !$force && isset( $existing_post_types[ $post_type[ 'name' ] ] ) )
                    continue;

                $post_type[ 'options' ][ 'name' ] = $post_type[ 'name' ];
                $post_type = array_merge( $post_type, (array) $post_type[ 'options' ] );

				$post_type_name = pods_var( 'name', $post_type );

                // Labels
                $cpt_label = esc_html( pods_var_raw( 'label', $post_type, ucwords( str_replace( '_', ' ', pods_var_raw( 'name', $post_type ) ) ), null, true ) );
                $cpt_singular = esc_html( pods_var_raw( 'label_singular', $post_type, ucwords( str_replace( '_', ' ', pods_var_raw( 'label', $post_type, $post_type_name, null, true ) ) ), null, true ) );

                $cpt_labels = array();
                $cpt_labels[ 'name' ] = $cpt_label;
                $cpt_labels[ 'singular_name' ] = $cpt_singular;
                $cpt_labels[ 'menu_name' ] = pods_var_raw( 'menu_name', $post_type, '', null, true );
                $cpt_labels[ 'add_new' ] = pods_var_raw( 'label_add_new', $post_type, '', null, true );
                $cpt_labels[ 'add_new_item' ] = pods_var_raw( 'label_add_new_item', $post_type, '', null, true );
                $cpt_labels[ 'new_item' ] = pods_var_raw( 'label_new_item', $post_type, '', null, true );
                $cpt_labels[ 'edit' ] = pods_var_raw( 'label_edit', $post_type, '', null, true );
                $cpt_labels[ 'edit_item' ] = pods_var_raw( 'label_edit_item', $post_type, '', null, true );
                $cpt_labels[ 'view' ] = pods_var_raw( 'label_view', $post_type, '', null, true );
                $cpt_labels[ 'view_item' ] = pods_var_raw( 'label_view_item', $post_type, '', null, true );
                $cpt_labels[ 'all_items' ] = pods_var_raw( 'label_all_items', $post_type, '', null, true );
                $cpt_labels[ 'search_items' ] = pods_var_raw( 'label_search_items', $post_type, '', null, true );
                $cpt_labels[ 'not_found' ] = pods_var_raw( 'label_not_found', $post_type, '', null, true );
                $cpt_labels[ 'not_found_in_trash' ] = pods_var_raw( 'label_not_found_in_trash', $post_type, '', null, true );
                $cpt_labels[ 'parent' ] = pods_var_raw( 'label_parent', $post_type, '', null, true );
                $cpt_labels[ 'parent_item_colon' ] = pods_var_raw( 'label_parent_item_colon', $post_type, '', null, true );

                // Supported
                $cpt_supported = array(
                    'title' => (boolean) pods_var( 'supports_title', $post_type, false ),
                    'editor' => (boolean) pods_var( 'supports_editor', $post_type, false ),
                    'author' => (boolean) pods_var( 'supports_author', $post_type, false ),
                    'thumbnail' => (boolean) pods_var( 'supports_thumbnail', $post_type, false ),
                    'excerpt' => (boolean) pods_var( 'supports_excerpt', $post_type, false ),
                    'trackbacks' => (boolean) pods_var( 'supports_trackbacks', $post_type, false ),
                    'custom-fields' => (boolean) pods_var( 'supports_custom_fields', $post_type, false ),
                    'comments' => (boolean) pods_var( 'supports_comments', $post_type, false ),
                    'revisions' => (boolean) pods_var( 'supports_revisions', $post_type, false ),
                    'page-attributes' => (boolean) pods_var( 'supports_page_attributes', $post_type, false ),
                    'post-formats' => (boolean) pods_var( 'supports_post_formats', $post_type, false )
                );

                // Custom Supported
                $cpt_supported_custom = pods_var( 'supports_custom', $post_type, '' );

                if ( !empty( $cpt_supported_custom ) ) {
                    $cpt_supported_custom = explode( ',', $cpt_supported_custom );
                    $cpt_supported_custom = array_filter( array_unique( $cpt_supported_custom ) );

                    foreach ( $cpt_supported_custom as $cpt_support ) {
                        $cpt_supported[ $cpt_support ] = true;
                    }
                }

                // Genesis Support
                if ( function_exists( 'genesis' ) ) {
                    $cpt_supported[ 'genesis-seo' ] = (boolean) pods_var( 'supports_genesis_seo', $post_type, false );
                    $cpt_supported[ 'genesis-layouts' ] = (boolean) pods_var( 'supports_genesis_layouts', $post_type, false );
                    $cpt_supported[ 'genesis-simple-sidebars' ] = (boolean) pods_var( 'supports_genesis_simple_sidebars', $post_type, false );
                }

				// YARPP Support
				if ( defined( 'YARPP_VERSION' ) ) {
                    $cpt_supported[ 'yarpp_support' ] = (boolean) pods_var( 'supports_yarpp_support', $post_type, false );
				}

				// Jetpack Support
				if ( class_exists( 'Jetpack' ) ) {
                    $cpt_supported[ 'supports_jetpack_publicize' ] = (boolean) pods_var( 'supports_jetpack_publicize', $post_type, false );
                    $cpt_supported[ 'supports_jetpack_markdown' ] = (boolean) pods_var( 'supports_jetpack_markdown', $post_type, false );
				}

                // WP needs something, if this was empty and none were enabled, it would show title+editor pre 3.5 :(
                $cpt_supports = array();

                if ( !pods_version_check( 'wp', '3.5' ) )
                    $cpt_supports = array( '_bug_fix_pre_35' );

                foreach ( $cpt_supported as $cpt_support => $supported ) {
                    if ( true === $supported )
                        $cpt_supports[] = $cpt_support;
                }

                if ( empty( $cpt_supports ) && pods_version_check( 'wp', '3.5' ) )
                    $cpt_supports = false;

                // Rewrite
                $cpt_rewrite = (boolean) pods_var( 'rewrite', $post_type, true );
                $cpt_rewrite_array = array(
                    'slug' => pods_var( 'rewrite_custom_slug', $post_type, str_replace( '_', '-', $post_type_name ), null, true ),
                    'with_front' => (boolean) pods_var( 'rewrite_with_front', $post_type, true ),
                    'feeds' => (boolean) pods_var( 'rewrite_feeds', $post_type, (boolean) pods_var( 'has_archive', $post_type, false ) ),
                    'pages' => (boolean) pods_var( 'rewrite_pages', $post_type, true )
                );

                if ( false !== $cpt_rewrite )
                    $cpt_rewrite = $cpt_rewrite_array;

                $capability_type = pods_var( 'capability_type', $post_type, 'post' );

                if ( 'custom' == $capability_type )
                    $capability_type = pods_var( 'capability_type_custom', $post_type, 'post' );

                $show_in_menu = (boolean) pods_var( 'show_in_menu', $post_type, true );

                if ( $show_in_menu && 0 < strlen( pods_var_raw( 'menu_location_custom', $post_type ) ) )
                    $show_in_menu = pods_var_raw( 'menu_location_custom', $post_type );

				$menu_icon = pods_var( 'menu_icon', $post_type, null, null, true );

				if ( !empty( $menu_icon ) ) {
					$menu_icon = pods_evaluate_tags( $menu_icon );
				}

                // Register Post Type
                $pods_post_types[ $post_type_name ] = array(
                    'label' => $cpt_label,
                    'labels' => $cpt_labels,
                    'description' => esc_html( pods_var_raw( 'description', $post_type ) ),
                    'public' => (boolean) pods_var( 'public', $post_type, true ),
                    'publicly_queryable' => (boolean) pods_var( 'publicly_queryable', $post_type, (boolean) pods_var( 'public', $post_type, true ) ),
                    'exclude_from_search' => (boolean) pods_var( 'exclude_from_search', $post_type, ( (boolean) pods_var( 'public', $post_type, true ) ? false : true ) ),
                    'show_ui' => (boolean) pods_var( 'show_ui', $post_type, (boolean) pods_var( 'public', $post_type, true ) ),
                    'show_in_menu' => $show_in_menu,
                    'show_in_nav_menus' => (boolean) pods_var( 'show_in_nav_menus', $post_type, (boolean) pods_var( 'public', $post_type, true ) ),
                    'show_in_admin_bar' => (boolean) pods_var( 'show_in_admin_bar', $post_type, (boolean) pods_var( 'show_in_menu', $post_type, true ) ),
                    'menu_position' => (int) pods_var( 'menu_position', $post_type, 0, null, true ),
                    'menu_icon' => $menu_icon,
                    'capability_type' => $capability_type,
                    //'capabilities' => $cpt_capabilities,
                    'map_meta_cap' => (boolean) pods_var( 'capability_type_extra', $post_type, true ),
                    'hierarchical' => (boolean) pods_var( 'hierarchical', $post_type, false ),
                    'supports' => $cpt_supports,
                    //'register_meta_box_cb' => array($this, 'manage_meta_box'),
                    //'permalink_epmask' => EP_PERMALINK,
                    'has_archive' => pods_v( 'has_archive_slug', $post_type, (boolean) pods_v( 'has_archive', $post_type, false ), true ),
                    'rewrite' => $cpt_rewrite,
                    'query_var' => ( false !== (boolean) pods_var( 'query_var', $post_type, true ) ? pods_var( 'query_var_string', $post_type, $post_type_name, null, true ) : false ),
                    'can_export' => (boolean) pods_var( 'can_export', $post_type, true )
                );

                // YARPP doesn't use 'supports' array option (yet)
                if ( ! empty( $cpt_supports[ 'yarpp_support' ] ) ) {
                    $pods_post_types[ $post_type_name ][ 'yarpp_support' ] = true;
                }

				// Prevent reserved query_var issues
				if ( in_array( $pods_post_types[ $post_type_name ][ 'query_var' ], $reserved_query_vars ) ) {
					$pods_post_types[ $post_type_name ][ 'query_var' ] = 'post_type_' . $pods_post_types[ $post_type_name ][ 'query_var' ];
				}

                if ( 25 == $pods_post_types[ $post_type_name ][ 'menu_position' ] )
                    $pods_post_types[ $post_type_name ][ 'menu_position' ]++;

                if ( $pods_post_types[ $post_type_name ][ 'menu_position' ] < 1 || in_array( $pods_post_types[ $post_type_name ][ 'menu_position' ], $cpt_positions ) )
                    unset( $pods_post_types[ $post_type_name ][ 'menu_position' ] );
                else {
                    $cpt_positions[] = $pods_post_types[ $post_type_name ][ 'menu_position' ];

                    // This would be nice if WP supported floats in menu_position
                    // $pods_post_types[ $post_type_name ][ 'menu_position' ] = $pods_post_types[ $post_type_name ][ 'menu_position' ] . '.1';
                }

                // Taxonomies
                $cpt_taxonomies = array();
                $_taxonomies = get_taxonomies();
                $_taxonomies = array_merge_recursive( $_taxonomies, $pods_taxonomies );
                $ignore = array( 'nav_menu', 'link_category', 'post_format' );

                foreach ( $_taxonomies as $taxonomy => $label ) {
                    if ( in_array( $taxonomy, $ignore ) )
                        continue;

                    if ( false !== (boolean) pods_var( 'built_in_taxonomies_' . $taxonomy, $post_type, false ) ) {
                        $cpt_taxonomies[] = $taxonomy;

                        if ( isset( $supported_post_types[ $taxonomy ] ) && !in_array( $post_type_name, $supported_post_types[ $taxonomy ] ) )
                            $supported_post_types[ $taxonomy ][] = $post_type_name;
                    }
                }

                if ( isset( $supported_taxonomies[ $post_type_name ] ) )
                    $supported_taxonomies[ $post_type_name ] = array_merge( (array) $supported_taxonomies[ $post_type_name ], $cpt_taxonomies );
                else
                    $supported_taxonomies[ $post_type_name ] = $cpt_taxonomies;
            }

            foreach ( $taxonomies as $taxonomy ) {
                // Taxonomy Type exists already
                if ( isset( $pods_cpt_ct[ 'taxonomies' ][ $taxonomy[ 'name' ] ] ) )
                    continue;
                elseif ( !empty( $taxonomy[ 'object' ] ) && isset( $existing_taxonomies[ $taxonomy[ 'object' ] ] ) )
                    continue;
                elseif ( !$force && isset( $existing_taxonomies[ $taxonomy[ 'name' ] ] ) )
                    continue;

                $taxonomy[ 'options' ][ 'name' ] = $taxonomy[ 'name' ];
                $taxonomy = array_merge( $taxonomy, (array) $taxonomy[ 'options' ] );

				$taxonomy_name = pods_var( 'name', $taxonomy );

                // Labels
                $ct_label = esc_html( pods_var_raw( 'label', $taxonomy, ucwords( str_replace( '_', ' ', pods_var_raw( 'name', $taxonomy ) ) ), null, true ) );
                $ct_singular = esc_html( pods_var_raw( 'label_singular', $taxonomy, ucwords( str_replace( '_', ' ', pods_var_raw( 'label', $taxonomy, pods_var_raw( 'name', $taxonomy ), null, true ) ) ), null, true ) );

                $ct_labels = array();
                $ct_labels[ 'name' ] = $ct_label;
                $ct_labels[ 'singular_name' ] = $ct_singular;
                $ct_labels[ 'menu_name' ] = pods_var_raw( 'menu_name', $taxonomy, '', null, true );
                $ct_labels[ 'search_items' ] = pods_var_raw( 'label_search_items', $taxonomy, '', null, true );
                $ct_labels[ 'popular_items' ] = pods_var_raw( 'label_popular_items', $taxonomy, '', null, true );
                $ct_labels[ 'all_items' ] = pods_var_raw( 'label_all_items', $taxonomy, '', null, true );
                $ct_labels[ 'parent_item' ] = pods_var_raw( 'label_parent_item', $taxonomy, '', null, true );
                $ct_labels[ 'parent_item_colon' ] = pods_var_raw( 'label_parent_item_colon', $taxonomy, '', null, true );
                $ct_labels[ 'edit_item' ] = pods_var_raw( 'label_edit_item', $taxonomy, '', null, true );
                $ct_labels[ 'update_item' ] = pods_var_raw( 'label_update_item', $taxonomy, '', null, true );
                $ct_labels[ 'add_new_item' ] = pods_var_raw( 'label_add_new_item', $taxonomy, '', null, true );
                $ct_labels[ 'new_item_name' ] = pods_var_raw( 'label_new_item_name', $taxonomy, '', null, true );
                $ct_labels[ 'separate_items_with_commas' ] = pods_var_raw( 'label_separate_items_with_commas', $taxonomy, '', null, true );
                $ct_labels[ 'add_or_remove_items' ] = pods_var_raw( 'label_add_or_remove_items', $taxonomy, '', null, true );
                $ct_labels[ 'choose_from_most_used' ] = pods_var_raw( 'label_choose_from_the_most_used', $taxonomy, '', null, true );

                // Rewrite
                $ct_rewrite = (boolean) pods_var( 'rewrite', $taxonomy, true );
                $ct_rewrite_array = array(
                    'slug' => pods_var( 'rewrite_custom_slug', $taxonomy, str_replace( '_', '-', $taxonomy_name ), null, true ),
                    'with_front' => (boolean) pods_var( 'rewrite_with_front', $taxonomy, true ),
                    'hierarchical' => (boolean) pods_var( 'rewrite_hierarchical', $taxonomy, (boolean) pods_var( 'hierarchical', $taxonomy, false ) )
                );

                if ( false !== $ct_rewrite )
                    $ct_rewrite = $ct_rewrite_array;

                // Register Taxonomy
                $pods_taxonomies[ $taxonomy_name ] = array(
                    'label' => $ct_label,
                    'labels' => $ct_labels,
                    'public' => (boolean) pods_var( 'public', $taxonomy, true ),
                    'show_in_nav_menus' => (boolean) pods_var( 'show_in_nav_menus', $taxonomy, (boolean) pods_var( 'public', $taxonomy, true ) ),
                    'show_ui' => (boolean) pods_var( 'show_ui', $taxonomy, (boolean) pods_var( 'public', $taxonomy, true ) ),
                    'show_tagcloud' => (boolean) pods_var( 'show_tagcloud', $taxonomy, (boolean) pods_var( 'show_ui', $taxonomy, (boolean) pods_var( 'public', $taxonomy, true ) ) ),
                    'hierarchical' => (boolean) pods_var( 'hierarchical', $taxonomy, false ),
                    'update_count_callback' => pods_var( 'update_count_callback', $taxonomy, null, null, true ),
                    'query_var' => ( false !== (boolean) pods_var( 'query_var', $taxonomy, true ) ? pods_var( 'query_var_string', $taxonomy, $taxonomy_name, null, true ) : false ),
                    'rewrite' => $ct_rewrite,
                    'show_admin_column' => (boolean) pods_var( 'show_admin_column', $taxonomy, false ),
                    'sort' => (boolean) pods_var( 'sort', $taxonomy, false )
                );

                if ( is_array( $ct_rewrite ) && !$pods_taxonomies[ $taxonomy_name ][ 'query_var' ] )
                    $pods_taxonomies[ $taxonomy_name ]['query_var'] = pods_var( 'query_var_string', $taxonomy, $taxonomy_name, null, true );;

				// Prevent reserved query_var issues
				if ( in_array( $pods_taxonomies[ $taxonomy_name ][ 'query_var' ], $reserved_query_vars ) ) {
					$pods_taxonomies[ $taxonomy_name ][ 'query_var' ] = 'taxonomy_' . $pods_taxonomies[ $taxonomy_name ][ 'query_var' ];
				}

				// Integration for Single Value Taxonomy UI
				if ( function_exists( 'tax_single_value_meta_box' ) ) {
					$pods_taxonomies[ $taxonomy_name ][ 'single_value' ] = (boolean) pods_var( 'single_value', $taxonomy, false );
					$pods_taxonomies[ $taxonomy_name ][ 'required' ] = (boolean) pods_var( 'single_value_required', $taxonomy, false );
				}

                // Post Types
                $ct_post_types = array();
                $_post_types = get_post_types();
                $_post_types = array_merge_recursive( $_post_types, $pods_post_types );
                $ignore = array( 'revision' );

                foreach ( $_post_types as $post_type => $options ) {
                    if ( in_array( $post_type, $ignore ) )
                        continue;

                    if ( false !== (boolean) pods_var( 'built_in_post_types_' . $post_type, $taxonomy, false ) ) {
                        $ct_post_types[] = $post_type;

                        if ( isset( $supported_taxonomies[ $post_type ] ) && !in_array( $taxonomy_name, $supported_taxonomies[ $post_type ] ) )
                            $supported_taxonomies[ $post_type ][] = $taxonomy_name;
                    }
                }

                if ( isset( $supported_post_types[ $taxonomy_name ] ) )
                    $supported_post_types[ $taxonomy_name ] = array_merge( $supported_post_types[ $taxonomy_name ], $ct_post_types );
                else
                    $supported_post_types[ $taxonomy_name ] = $ct_post_types;
            }

            $pods_post_types = apply_filters( 'pods_wp_post_types', $pods_post_types );
            $pods_taxonomies = apply_filters( 'pods_wp_taxonomies', $pods_taxonomies );

            $supported_post_types = apply_filters( 'pods_wp_supported_post_types', $supported_post_types );
            $supported_taxonomies = apply_filters( 'pods_wp_supported_taxonomies', $supported_taxonomies );

            foreach ( $pods_taxonomies as $taxonomy => $options ) {
                $ct_post_types = null;

                if ( isset( $supported_post_types[ $taxonomy ] ) && !empty( $supported_post_types[ $taxonomy ] ) )
                    $ct_post_types = $supported_post_types[ $taxonomy ];

                $pods_cpt_ct[ 'taxonomies' ][ $taxonomy ] = array(
                    'post_types' => $ct_post_types,
                    'options' => $options
                );
            }

            foreach ( $pods_post_types as $post_type => $options ) {
                if ( isset( $supported_taxonomies[ $post_type ] ) && !empty( $supported_taxonomies[ $post_type ] ) )
                    $options[ 'taxonomies' ] = $supported_taxonomies[ $post_type ];

                $pods_cpt_ct[ 'post_types' ][ $post_type ] = $options;
            }

            pods_transient_set( 'pods_wp_cpt_ct', $pods_cpt_ct );
        }

        foreach ( $pods_cpt_ct[ 'taxonomies' ] as $taxonomy => $options ) {
            if ( isset( self::$content_types_registered[ 'taxonomies' ] ) && in_array( $taxonomy, self::$content_types_registered[ 'taxonomies' ] ) )
                continue;

            $ct_post_types = $options[ 'post_types' ];
            $options = $options[ 'options' ];

            $options = apply_filters( 'pods_register_taxonomy_' . $taxonomy, $options, $taxonomy );
            $options = apply_filters( 'pods_register_taxonomy', $options, $taxonomy );

            $options = self::object_label_fix( $options, 'taxonomy' );

            // Max length for taxonomies are 32 characters
            $taxonomy = substr( $taxonomy, 0, 32 );

            // i18n compatibility for plugins that override it
            if ( is_array( $options[ 'rewrite' ] ) && isset( $options[ 'rewrite' ][ 'slug' ] ) && !empty( $options[ 'rewrite' ][ 'slug' ] ) )
                $options[ 'rewrite' ][ 'slug' ] = _x( $options[ 'rewrite' ][ 'slug' ], 'URL taxonomy slug', 'pods' );

            if ( 1 == pods_var( 'pods_debug_register', 'get', 0 ) && pods_is_admin( array( 'pods' ) ) )
                pods_debug( array( $taxonomy, $ct_post_types, $options ) );

            register_taxonomy( $taxonomy, $ct_post_types, $options );

            if ( !isset( self::$content_types_registered[ 'taxonomies' ] ) )
                self::$content_types_registered[ 'taxonomies' ] = array();

            self::$content_types_registered[ 'taxonomies' ][] = $taxonomy;
        }

        foreach ( $pods_cpt_ct[ 'post_types' ] as $post_type => $options ) {
            if ( isset( self::$content_types_registered[ 'post_types' ] ) && in_array( $post_type, self::$content_types_registered[ 'post_types' ] ) )
                continue;

            $options = apply_filters( 'pods_register_post_type_' . $post_type, $options, $post_type );
            $options = apply_filters( 'pods_register_post_type', $options, $post_type );

            $options = self::object_label_fix( $options, 'post_type' );

            // Max length for post types are 20 characters
            $post_type = substr( $post_type, 0, 20 );

            // i18n compatibility for plugins that override it
            if ( is_array( $options[ 'rewrite' ] ) && isset( $options[ 'rewrite' ][ 'slug' ] ) && !empty( $options[ 'rewrite' ][ 'slug' ] ) )
                $options[ 'rewrite' ][ 'slug' ] = _x( $options[ 'rewrite' ][ 'slug' ], 'URL slug', 'pods' );

            if ( 1 == pods_var( 'pods_debug_register', 'get', 0 ) && pods_is_admin( array( 'pods' ) ) )
                pods_debug( array( $post_type, $options ) );

            register_post_type( $post_type, $options );

            if ( !isset( self::$content_types_registered[ 'post_types' ] ) )
                self::$content_types_registered[ 'post_types' ] = array();

            self::$content_types_registered[ 'post_types' ][] = $post_type;
        }

    }

	/**
     * Check if we need to flush WordPress rewrite rules
     * This gets run during 'init' action late in the game to give other plugins time to register their rewrite rules
     *
     */
    public function flush_rewrite_rules() {
        $flush = pods_transient_get( 'pods_flush_rewrites' );

        if ( 1 == $flush ) {
            global $wp_rewrite;
            $wp_rewrite->flush_rules();
            $wp_rewrite->init();

            pods_transient_set( 'pods_flush_rewrites', 0 );
        }
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

        $post_types = PodsMeta::$post_types;
        $existing_post_types = get_post_types();

        $pods_cpt_ct = pods_transient_get( 'pods_wp_cpt_ct' );

        if ( empty( $pods_cpt_ct ) || empty( $post_types ) )
            return $messages;

        foreach ( $post_types as $post_type ) {
            if ( !isset( $pods_cpt_ct[ 'post_types' ][ $post_type[ 'name' ] ] ) )
                continue;

            $labels = self::object_label_fix( $pods_cpt_ct[ 'post_types' ][ $post_type[ 'name' ] ], 'post_type' );
            $labels = $labels[ 'labels' ];

            $messages[ $post_type[ 'name' ] ] = array(
                1 => sprintf( __( '%s updated. <a href="%s">%s</a>', 'pods' ), $labels[ 'singular_name' ], esc_url( get_permalink( $post_ID ) ), $labels[ 'view_item' ] ),
                2 => __( 'Custom field updated.', 'pods' ),
                3 => __( 'Custom field deleted.', 'pods' ),
                4 => sprintf( __( '%s updated.', 'pods' ), $labels[ 'singular_name' ] ),
                /* translators: %s: date and time of the revision */
                5 => isset( $_GET[ 'revision' ] ) ? sprintf( __( '%s restored to revision from %s', 'pods' ), $labels[ 'singular_name' ], wp_post_revision_title( (int) $_GET[ 'revision' ], false ) ) : false,
                6 => sprintf( __( '%s published. <a href="%s">%s</a>', 'pods' ), $labels[ 'singular_name' ], esc_url( get_permalink( $post_ID ) ), $labels[ 'view_item' ] ),
                7 => sprintf( __( '%s saved.', 'pods' ), $labels[ 'singular_name' ] ),
                8 => sprintf( __( '%s submitted. <a target="_blank" href="%s">Preview %s</a>', 'pods' ), $labels[ 'singular_name' ], esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ), $labels[ 'singular_name' ] ),
                9 => sprintf( __( '%s scheduled for: <strong>%s</strong>. <a target="_blank" href="%s">Preview %s</a>', 'pods' ),
                    $labels[ 'singular_name' ],
                    // translators: Publish box date format, see http://php.net/date
                    date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ),
                    esc_url( get_permalink( $post_ID ) ),
                    $labels[ 'singular_name' ]
                ),
                10 => sprintf( __( '%s draft updated. <a target="_blank" href="%s">Preview %s</a>', 'pods' ), $labels[ 'singular_name' ], esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ), $labels[ 'singular_name' ] )
            );

            if ( false === (boolean) $pods_cpt_ct[ 'post_types' ][ $post_type[ 'name' ] ][ 'public' ] ) {
                $messages[ $post_type[ 'name' ] ][ 1 ] = sprintf( __( '%s updated.', 'pods' ), $labels[ 'singular_name' ] );
                $messages[ $post_type[ 'name' ] ][ 6 ] = sprintf( __( '%s published.', 'pods' ), $labels[ 'singular_name' ] );
                $messages[ $post_type[ 'name' ] ][ 8 ] = sprintf( __( '%s submitted.', 'pods' ), $labels[ 'singular_name' ] );
                $messages[ $post_type[ 'name' ] ][ 9 ] = sprintf( __( '%s scheduled for: <strong>%1$s</strong>.', 'pods' ),
                    $labels[ 'singular_name' ],
                    // translators: Publish box date format, see http://php.net/date
                    date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) )
                );
                $messages[ $post_type[ 'name' ] ][ 10 ] = sprintf( __( '%s draft updated.', 'pods' ), $labels[ 'singular_name' ] );
            }
        }

        return $messages;
    }

    /**
     * @param $args
     * @param string $type
     *
     * @return array
     */
    public static function object_label_fix ( $args, $type = 'post_type' ) {
        if ( empty( $args ) || !is_array( $args ) )
            $args = array();

        if ( !isset( $args[ 'labels' ] ) || !is_array( $args[ 'labels' ] ) )
            $args[ 'labels' ] = array();

        $label = pods_var_raw( 'name', $args[ 'labels' ], pods_var_raw( 'label', $args, __( 'Items', 'pods' ), null, true ), null, true );
        $singular_label = pods_var_raw( 'singular_name', $args[ 'labels' ], pods_var_raw( 'label_singular', $args, __( 'Item', 'pods' ), null, true ), null, true );

        $labels = $args[ 'labels' ];

        $labels[ 'name' ] = $label;
        $labels[ 'singular_name' ] = $singular_label;

        if ( 'post_type' == $type ) {
            $labels[ 'menu_name' ] = pods_var_raw( 'menu_name', $labels, $label, null, true );
            $labels[ 'add_new' ] = pods_var_raw( 'add_new', $labels, __( 'Add New', 'pods' ), null, true );
            $labels[ 'add_new_item' ] = pods_var_raw( 'add_new_item', $labels, sprintf( __( 'Add New %s', 'pods' ), $singular_label ), null, true );
            $labels[ 'new_item' ] = pods_var_raw( 'new_item', $labels, sprintf( __( 'New %s', 'pods' ), $singular_label ), null, true );
            $labels[ 'edit' ] = pods_var_raw( 'edit', $labels, __( 'Edit', 'pods' ), null, true );
            $labels[ 'edit_item' ] = pods_var_raw( 'edit_item', $labels, sprintf( __( 'Edit %s', 'pods' ), $singular_label ), null, true );
            $labels[ 'view' ] = pods_var_raw( 'view', $labels, sprintf( __( 'View %s', 'pods' ), $singular_label ), null, true );
            $labels[ 'view_item' ] = pods_var_raw( 'view_item', $labels, sprintf( __( 'View %s', 'pods' ), $singular_label ), null, true );
            $labels[ 'all_items' ] = pods_var_raw( 'all_items', $labels, sprintf( __( 'All %s', 'pods' ), $label ), null, true );
            $labels[ 'search_items' ] = pods_var_raw( 'search_items', $labels, sprintf( __( 'Search %s', 'pods' ), $label ), null, true );
            $labels[ 'not_found' ] = pods_var_raw( 'not_found', $labels, sprintf( __( 'No %s Found', 'pods' ), $label ), null, true );
            $labels[ 'not_found_in_trash' ] = pods_var_raw( 'not_found_in_trash', $labels, sprintf( __( 'No %s Found in Trash', 'pods' ), $label ), null, true );
            $labels[ 'parent' ] = pods_var_raw( 'parent', $labels, sprintf( __( 'Parent %s', 'pods' ), $singular_label ), null, true );
            $labels[ 'parent_item_colon' ] = pods_var_raw( 'parent_item_colon', $labels, sprintf( __( 'Parent %s:', 'pods' ), $singular_label ), null, true );
            $labels[ 'feature_image' ] = pods_var_raw( 'feature_image', $labels, __( 'Featured Image', 'pods' ), null, true );
            $labels[ 'set_featured_image' ] = pods_var_raw( 'set_featured_image', $labels, __( 'Set featured image', 'pods' ), null, true );
            $labels[ 'remove_featured_image' ] = pods_var_raw( 'remove_featured_image', $labels, __( 'Remove featured image', 'pods' ), null, true );
            $labels[ 'use_featured_image' ] = pods_var_raw( 'use_featured_image', $labels, __( 'Use as featured image', 'pods' ), null, true );
            $labels[ 'archives' ] = pods_var_raw( 'archives', $labels, sprintf( __( '%s Archives', 'pods' ), $singular_label ), null, true );
            $labels[ 'insert_into_item' ] = pods_var_raw( 'insert_into_item', $labels, sprintf( __( 'Insert into %s', 'pods' ), $singular_label ), null, true );
            $labels[ 'uploaded_to_this_item' ] = pods_var_raw( 'uploaded_to_this_item', $labels, sprintf( __( 'Uploaded to this %s', 'pods' ), $singular_label ), null, true );
            $labels[ 'filter_items_list' ] = pods_var_raw( 'filter_items_list', $labels, sprintf( __( 'Filter %s lists', 'pods' ), $label ), null, true );
            $labels[ 'items_list_navigation' ] = pods_var_raw( 'items_list_navigation', $labels, sprintf( __( '%s navigation', 'pods' ), $label ), null, true );
            $labels[ 'items_list' ] = pods_var_raw( 'items_list', $labels, sprintf( __( '%s list', 'pods' ), $label ), null, true );
        }
        elseif ( 'taxonomy' == $type ) {
            $labels[ 'menu_name' ] = pods_var_raw( 'menu_name', $labels, $label, null, true );
            $labels[ 'search_items' ] = pods_var_raw( 'search_items', $labels, sprintf( __( 'Search %s', 'pods' ), $label ), null, true );
            $labels[ 'popular_items' ] = pods_var_raw( 'popular_items', $labels, sprintf( __( 'Popular %s', 'pods' ), $label ), null, true );
            $labels[ 'all_items' ] = pods_var_raw( 'all_items', $labels, sprintf( __( 'All %s', 'pods' ), $label ), null, true );
            $labels[ 'parent_item' ] = pods_var_raw( 'parent_item', $labels, sprintf( __( 'Parent %s', 'pods' ), $singular_label ), null, true );
            $labels[ 'parent_item_colon' ] = pods_var_raw( 'parent_item_colon', $labels, sprintf( __( 'Parent %s :', 'pods' ), $singular_label ), null, true );
            $labels[ 'edit_item' ] = pods_var_raw( 'edit_item', $labels, sprintf( __( 'Edit %s', 'pods' ), $singular_label ), null, true );
            $labels[ 'update_item' ] = pods_var_raw( 'update_item', $labels, sprintf( __( 'Update %s', 'pods' ), $singular_label ), null, true );
            $labels[ 'add_new_item' ] = pods_var_raw( 'add_new_item', $labels, sprintf( __( 'Add New %s', 'pods' ), $singular_label ), null, true );
            $labels[ 'new_item_name' ] = pods_var_raw( 'new_item_name', $labels, sprintf( __( 'New %s Name', 'pods' ), $singular_label ), null, true );
            $labels[ 'separate_items_with_commas' ] = pods_var_raw( 'separate_items_with_commas', $labels, sprintf( __( 'Separate %s with commas', 'pods' ), $label ), null, true );
            $labels[ 'add_or_remove_items' ] = pods_var_raw( 'add_or_remove_items', $labels, sprintf( __( 'Add or remove %s', 'pods' ), $label ), null, true );
            $labels[ 'choose_from_most_used' ] = pods_var_raw( 'choose_from_most_used', $labels, sprintf( __( 'Choose from the most used %s', 'pods' ), $label ), null, true );
            $labels[ 'no_terms' ] = pods_var_raw( 'no_terms', $labels, sprintf( __( 'No %s', 'pods' ), $label ), null, true );
            $labels[ 'items_list_navigation' ] = pods_var_raw( 'items_list_navigation', $labels, sprintf( __( '%s navigation', 'pods' ), $label ), null, true );
            $labels[ 'items_list' ] = pods_var_raw( 'items_list', $labels, sprintf( __( '%s list', 'pods' ), $label ), null, true );
        }

        $args[ 'labels' ] = $labels;

        return $args;
    }

    /**
     * Activate and Install
     */
    public function activate_install () {
        register_activation_hook( PODS_DIR . 'init.php', array( $this, 'activate' ) );
        register_deactivation_hook( PODS_DIR . 'init.php', array( $this, 'deactivate' ) );

        add_action( 'wpmu_new_blog', array( $this, 'new_blog' ), 10, 6 );

        if ( empty( self::$version ) || version_compare( self::$version, PODS_VERSION, '<' ) || version_compare( self::$version, PODS_DB_VERSION, '<=' ) || self::$upgrade_needed )
            $this->setup();
        elseif ( self::$version != PODS_VERSION ) {
            delete_option( 'pods_framework_version' );
            add_option( 'pods_framework_version', PODS_VERSION, '', 'yes' );

            pods_api()->cache_flush_pods();
        }
    }

    /**
     *
     */
    public function activate () {
        global $wpdb;

        if ( function_exists( 'is_multisite' ) && is_multisite() && isset( $_GET[ 'networkwide' ] ) && 1 == $_GET[ 'networkwide' ] ) {
            $_blog_ids = $wpdb->get_col( "SELECT `blog_id` FROM `{$wpdb->blogs}`" );

            foreach ( $_blog_ids as $_blog_id ) {
                $this->setup( $_blog_id );
            }
        }
        else
            $this->setup();
    }

    /**
     *
     */
    public function deactivate () {
        pods_api()->cache_flush_pods();
    }

    /**
     *
     */
    public function needs_upgrade ( $current = null, $last = null ) {
        if ( null === $current )
            $current = self::$version;

        if ( null === $last )
            $last = self::$version_last;

        $upgrade_needed = false;

        if ( !empty( $current ) ) {
            foreach ( self::$upgrades as $old_version => $new_version ) {
                /*if ( '2.1.0' == $new_version && ( is_developer() ) )
                    continue;*/

                if ( version_compare( $last, $old_version, '>=' )
                     && version_compare( $last, $new_version, '<' )
                     && version_compare( $current, $new_version, '>=' )
                     && 1 != self::$upgraded
                ) {
                    $upgrade_needed = true;

                    break;
                }
            }
        }

        return $upgrade_needed;
    }

    /**
     * @param $_blog_id
     * @param $user_id
     * @param $domain
     * @param $path
     * @param $site_id
     * @param $meta
     */
    public function new_blog ( $_blog_id, $user_id, $domain, $path, $site_id, $meta ) {
        if ( function_exists( 'is_multisite' ) && is_multisite() && is_plugin_active_for_network( basename( PODS_DIR ) . '/init.php' ) )
            $this->setup( $_blog_id );
    }

    /**
     * @param null $_blog_id
     */
    public function setup ( $_blog_id = null ) {
        global $wpdb;

        // Switch DB table prefixes
        if ( null !== $_blog_id && $_blog_id != $wpdb->blogid )
            switch_to_blog( pods_absint( $_blog_id ) );
        else
            $_blog_id = null;

        // Setup DB tables
        $pods_version = get_option( 'pods_framework_version' );
        $pods_version_last = get_option( 'pods_framework_version_last' );

        // Install Pods
        if ( empty( $pods_version ) ) {
            pods_upgrade()->install( $_blog_id );

            $old_version = get_option( 'pods_version' );

            if ( !empty( $old_version ) ) {
                if ( false === strpos( $old_version, '.' ) )
                    $old_version = pods_version_to_point( $old_version );

                delete_option( 'pods_framework_version_last' );
                add_option( 'pods_framework_version_last', $pods_version, '', 'yes' );

                self::$version_last = $old_version;
            }
        }
        // Upgrade Wizard needed
        elseif ( $this->needs_upgrade( $pods_version, $pods_version_last ) ) {
            // Do not do anything
            return;
        }
        // Update Pods and run any required DB updates
        elseif ( version_compare( $pods_version, PODS_VERSION, '<=' ) ) {
            if ( false !== apply_filters( 'pods_update_run', null, PODS_VERSION, $pods_version, $_blog_id ) && !isset( $_GET[ 'pods_bypass_update' ] ) ) {
                do_action( 'pods_update', PODS_VERSION, $pods_version, $_blog_id );

                // Update 2.0 alpha / beta sites
                if ( version_compare( '2.0.0-a-1', $pods_version, '<=' ) && version_compare( $pods_version, '2.0.0-b-15', '<=' ) )
                    include( PODS_DIR . 'sql/update-2.0-beta.php' );

                if ( version_compare( $pods_version, PODS_DB_VERSION, '<=' ) )
                    include( PODS_DIR . 'sql/update.php' );

                do_action( 'pods_update_post', PODS_VERSION, $pods_version, $_blog_id );
            }

            delete_option( 'pods_framework_version_last' );
            add_option( 'pods_framework_version_last', $pods_version, '', 'yes' );

            self::$version_last = $pods_version;
        }

        delete_option( 'pods_framework_version' );
        add_option( 'pods_framework_version', PODS_VERSION, '', 'yes' );

        delete_option( 'pods_framework_db_version' );
        add_option( 'pods_framework_db_version', PODS_DB_VERSION, '', 'yes' );

        pods_api()->cache_flush_pods();

        // Restore DB table prefix (if switched)
        if ( null !== $_blog_id )
            restore_current_blog();
	    else {
		    $this->run();
	    }
    }

    /**
     * @param null $_blog_id
     */
    public function reset ( $_blog_id = null ) {
        global $wpdb;

        // Switch DB table prefixes
        if ( null !== $_blog_id && $_blog_id != $wpdb->blogid )
            switch_to_blog( pods_absint( $_blog_id ) );
        else
            $_blog_id = null;

        $api = pods_api();

        $pods = $api->load_pods( array( 'names_ids' => true ) );

        foreach ( $pods as $pod_id => $pod_label ) {
            $api->delete_pod( array( 'id' => $pod_id ) );
        }

        $templates = $api->load_templates();

        foreach ( $templates as $template ) {
            $api->delete_template( array( 'id' => $template[ 'id' ] ) );
        }

        $pages = $api->load_pages();

        foreach ( $pages as $page ) {
            $api->delete_page( array( 'id' => $page[ 'id' ] ) );
        }

        $helpers = $api->load_helpers();

        foreach ( $helpers as $helper ) {
            $api->delete_helper( array( 'id' => $helper[ 'id' ] ) );
        }

        $tables = $wpdb->get_results( "SHOW TABLES LIKE '{$wpdb->prefix}pods%'", ARRAY_N );

        if ( !empty( $tables ) ) {
            foreach ( $tables as $table ) {
                $table = $table[ 0 ];

                pods_query( "DROP TABLE `{$table}`", false );
            }
        }

        // Remove any orphans
        $wpdb->query( "
                DELETE `p`, `pm`
                FROM `{$wpdb->posts}` AS `p`
                LEFT JOIN `{$wpdb->postmeta}` AS `pm`
                    ON `pm`.`post_id` = `p`.`ID`
                WHERE
                    `p`.`post_type` LIKE '_pods_%'
            " );

        delete_option( 'pods_framework_version' );
        delete_option( 'pods_framework_db_version' );
        delete_option( 'pods_framework_upgrade_2_0' );
        delete_option( 'pods_framework_upgraded_1_x' );

        // @todo Make sure all entries are being cleaned and do something about the pods_framework_upgrade_{version} dynamic entries created by PodsUpgrade
        delete_option( 'pods_framework_upgrade_2_0_0' );
        delete_option( 'pods_framework_upgrade_2_0_sister_ids' );
        delete_option( 'pods_framework_version_last' );

        delete_option( 'pods_component_settings' );

        $api->cache_flush_pods();

        pods_transient_clear( 'pods_flush_rewrites' );

        self::$version = '';

        // Restore DB table prefix (if switched)
        if ( null !== $_blog_id )
            restore_current_blog();
    }

	public function run () {

		if ( ! did_action( 'plugins_loaded' ) ) {
			add_action( 'plugins_loaded', array( $this, 'load_components' ), 11 );
		}
		else {
			$this->load_components();
		}

		if ( ! did_action( 'setup_theme' ) ) {
			add_action( 'setup_theme', array( $this, 'load_meta' ), 14 );
		}
		else {
			$this->load_meta();
		}

		if ( ! did_action( 'init' ) ) {
			add_action( 'init', array( $this, 'core' ), 11 );
            add_action( 'init', array( $this, 'add_rest_support' ), 12 );
	        add_action( 'init', array( $this, 'setup_content_types' ), 11 );

	        if ( is_admin() ) {
		        add_action( 'init', array( $this, 'admin_init' ), 12 );
	        }
		}
		else {
			$this->core();
			$this->add_rest_support();
			$this->setup_content_types();

			if ( is_admin() ) {
				$this->admin_init();
			}
		}

        add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ), 15 );
        add_action( 'admin_enqueue_scripts', array( $this, 'register_assets' ), 15 );
        add_action( 'login_enqueue_scripts', array( $this, 'register_assets' ), 15 );

        add_filter( 'post_updated_messages', array( $this, 'setup_updated_messages' ), 10, 1 );
        add_action( 'delete_attachment', array( $this, 'delete_attachment' ) );

        // Register widgets
        add_action( 'widgets_init', array( $this, 'register_widgets' ) );

        // Show admin bar links
        add_action( 'admin_bar_menu', array( $this, 'admin_bar_links' ), 81 );

	}

    /**
     * Delete Attachments from relationships
     *
     * @param int $_ID
     */
    public function delete_attachment ( $_ID ) {
        global $wpdb;

        $_ID = (int) $_ID;

        do_action( 'pods_delete_attachment', $_ID );

        $file_types = "'" . implode( "', '", PodsForm::file_field_types() ) . "'";

        if ( !pods_tableless() ) {
            $sql = "
                DELETE `rel`
                FROM `@wp_podsrel` AS `rel`
                LEFT JOIN `{$wpdb->posts}` AS `p`
                    ON
                        `p`.`post_type` = '_pods_field'
                        AND ( `p`.`ID` = `rel`.`field_id` OR `p`.`ID` = `rel`.`related_field_id` )
                LEFT JOIN `{$wpdb->postmeta}` AS `pm`
                    ON
                        `pm`.`post_id` = `p`.`ID`
                        AND `pm`.`meta_key` = 'type'
                        AND `pm`.`meta_value` IN ( {$file_types} )
                WHERE
                    `p`.`ID` IS NOT NULL
                    AND `pm`.`meta_id` IS NOT NULL
                    AND `rel`.`item_id` = {$_ID}";

            pods_query( $sql, false );
        }

        // Post Meta
        if ( !empty( PodsMeta::$post_types ) ) {
            $sql =  "
                DELETE `rel`
                FROM `@wp_postmeta` AS `rel`
                LEFT JOIN `{$wpdb->posts}` AS `p`
                    ON
                        `p`.`post_type` = '_pods_field'
                LEFT JOIN `{$wpdb->postmeta}` AS `pm`
                    ON
                        `pm`.`post_id` = `p`.`ID`
                        AND `pm`.`meta_key` = 'type'
                        AND `pm`.`meta_value` IN ( {$file_types} )
                WHERE
                    `p`.`ID` IS NOT NULL
                    AND `pm`.`meta_id` IS NOT NULL
                    AND `rel`.`meta_key` = `p`.`post_name`
                    AND `rel`.`meta_value` = '{$_ID}'";

            pods_query( $sql, false );
        }

        // User Meta
        if ( !empty( PodsMeta::$user ) ) {
            $sql = "
                DELETE `rel`
                FROM `@wp_usermeta` AS `rel`
                LEFT JOIN `{$wpdb->posts}` AS `p`
                    ON
                        `p`.`post_type` = '_pods_field'
                LEFT JOIN `{$wpdb->postmeta}` AS `pm`
                    ON
                        `pm`.`post_id` = `p`.`ID`
                        AND `pm`.`meta_key` = 'type'
                        AND `pm`.`meta_value` IN ( {$file_types} )
                WHERE
                    `p`.`ID` IS NOT NULL
                    AND `pm`.`meta_id` IS NOT NULL
                    AND `rel`.`meta_key` = `p`.`post_name`
                    AND `rel`.`meta_value` = '{$_ID}'";

            pods_query( $sql, false );
        }

        // Comment Meta
        if ( !empty( PodsMeta::$comment ) ) {
            $sql = "
                DELETE `rel`
                FROM `@wp_commentmeta` AS `rel`
                LEFT JOIN `{$wpdb->posts}` AS `p`
                    ON
                        `p`.`post_type` = '_pods_field'
                LEFT JOIN `{$wpdb->postmeta}` AS `pm`
                    ON
                        `pm`.`post_id` = `p`.`ID`
                        AND `pm`.`meta_key` = 'type'
                        AND `pm`.`meta_value` IN ( {$file_types} )
                WHERE
                    `p`.`ID` IS NOT NULL
                    AND `pm`.`meta_id` IS NOT NULL
                    AND `rel`.`meta_key` = `p`.`post_name`
                    AND `rel`.`meta_value` = '{$_ID}'";

            pods_query( $sql, false );
        }
    }

    /**
     * Register widgets for Pods
     */
    public function register_widgets () {
        $widgets = array(
            'PodsWidgetSingle',
            'PodsWidgetList',
            'PodsWidgetField',
            'PodsWidgetForm',
            'PodsWidgetView'
        );

        foreach ( $widgets as $widget ) {
            if ( !file_exists( PODS_DIR . 'classes/widgets/' . $widget . '.php' ) )
                continue;

            require_once PODS_DIR . 'classes/widgets/' . $widget . '.php';

            register_widget( $widget );
        }
    }

    /**
     * Add Admin Bar links
     */
    public function admin_bar_links () {
        global $wp_admin_bar, $pods;

        if ( !is_user_logged_in() || !is_admin_bar_showing() )
            return;

        $all_pods = pods_api()->load_pods( array( 'type' => 'pod', 'fields' => false ) );

        // Add New item links for all pods
        foreach ( $all_pods as $pod ) {
            if ( 0 == $pod[ 'options' ][ 'show_in_menu' ] )
                continue;

            if ( !pods_is_admin( array( 'pods', 'pods_content', 'pods_add_' . $pod[ 'name' ] ) ) )
                continue;

            $singular_label = pods_var_raw( 'label_singular', $pod[ 'options' ], pods_var_raw( 'label', $pod, ucwords( str_replace( '_', ' ', $pod[ 'name' ] ) ), null, true ), null, true );

            $wp_admin_bar->add_node( array(
                'id' => 'new-pod-' . $pod[ 'name' ],
                'title' => $singular_label,
                'parent' => 'new-content',
                'href' => admin_url( 'admin.php?page=pods-manage-' . $pod[ 'name' ] . '&action=add' )
            ) );
        }

        // Add edit link if we're on a pods page
        if ( is_object( $pods ) && !is_wp_error( $pods ) && !empty( $pods->id ) && isset( $pods->pod_data ) && !empty( $pods->pod_data ) && 'pod' == $pods->pod_data[ 'type' ] ) {
            $pod = $pods->pod_data;

            if ( pods_is_admin( array( 'pods', 'pods_content', 'pods_edit_' . $pod[ 'name' ] ) ) ) {
                $singular_label = pods_var_raw( 'label_singular', $pod[ 'options' ], pods_var_raw( 'label', $pod, ucwords( str_replace( '_', ' ', $pod[ 'name' ] ) ), null, true ), null, true );

                $wp_admin_bar->add_node( array(
                    'title' => sprintf( __( 'Edit %s', 'pods' ), $singular_label ),
                    'id' => 'edit-pod',
                    'href' => admin_url( 'admin.php?page=pods-manage-' . $pod[ 'name' ] . '&action=edit&id=' . $pods->id() )
                ) );
            }
        }

    }

    /**
     * Add REST API support to post type and taxonomy objects.
     *
     * @uses "init"
     *
     * @since 2.5.6
     */
    public function add_rest_support() {

        static $rest_support_added;

        if ( ! function_exists( 'register_rest_field' ) ) {
            return;
        }

        include_once( PODS_DIR . 'classes/PodsRESTFields.php' );
        include_once( PODS_DIR . 'classes/PodsRESTHandlers.php' );

        $rest_bases = pods_transient_get( 'pods_rest_bases' );

        if ( empty( $rest_bases ) ) {
            $pods = pods_api()->load_pods();

            $rest_bases = array();

            if ( ! empty( $pods ) && is_array( $pods ) ) {
                foreach ( $pods as $pod ) {
                    $type = $pod['type'];

                    if ( in_array( $type, array( 'post_type', 'taxonomy' ) ) ) {
                        if ( $pod && PodsRESTHandlers::pod_extends_core_route( $pod ) ) {
                            $rest_bases[ $pod['name'] ] = array(
                                'type' => $type,
                                'base' => sanitize_title( pods_v( 'rest_base', $pod['options'], $pod['name'] ) ),
                            );
                        }
                    }
                }
            }

            if ( empty( $rest_bases ) ) {
                $rest_bases = 'none';
            }

            pods_transient_set( 'pods_rest_bases', $rest_bases );
        }

        if ( empty( $rest_support_added ) && ! empty( $rest_bases ) && 'none' !== $rest_bases ) {
            foreach ( $rest_bases as $pod_name => $pod_info ) {
                $pod_type  = $pod_info['type'];
                $rest_base = $pod_info['base'];

                if ( 'post_type' == $pod_type ) {
                    PodsRESTHandlers::post_type_rest_support( $pod_name, $rest_base );
                } elseif ( 'taxonomy' == $pod_type ) {
                    PodsRESTHandlers::taxonomy_rest_support( $pod_name, $rest_base );
                }

                new PodsRESTFields( $pod_name );
            }

            $rest_support_added = true;
        }

    }
}
