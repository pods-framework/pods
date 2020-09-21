<?php

/**
 * @package Pods
 */
class PodsInit {

	/**
	 * @var PodsInit
	 */
	public static $instance = null;

	/**
	 * @var array
	 */
	public static $no_conflict = array();

	/**
	 * @var array
	 */
	public static $content_types_registered = array();

	/**
	 * @var PodsComponents
	 */
	public static $components;

	/**
	 * @var PodsMeta
	 */
	public static $meta;

	/**
	 * @var PodsI18n
	 */
	public static $i18n;

	/**
	 * @var PodsAdmin
	 */
	public static $admin;

	/**
	 * @var mixed|void
	 */
	public static $version;

	/**
	 * @var mixed|void
	 */
	public static $version_last;

	/**
	 * @var mixed|void
	 */
	public static $db_version;

	/**
	 * Upgrades to trigger (last installed version => upgrade version)
	 *
	 * @var array
	 */
	public static $upgrades = array(
		'1.0.0' => '2.0.0',
		// '2.0.0' => '2.1.0'
	);

	/**
	 * Whether an Upgrade for 1.x has happened
	 *
	 * @var bool
	 */
	public static $upgraded;

	/**
	 * Whether an Upgrade is needed
	 *
	 * @var bool
	 */
	public static $upgrade_needed = false;

	/**
	 * Freemius object.
	 *
	 * @since 1.0.0
	 *
	 * @var \Freemius
	 */
	protected $freemius;

	/**
	 * Singleton handling for a basic pods_init() request
	 *
	 * @return \PodsInit
	 *
	 * @since 2.3.5
	 */
	public static function init() {

		if ( ! is_object( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Setup and Initiate Pods
	 *
	 * @return \PodsInit
	 *
	 * @license http://www.gnu.org/licenses/gpl-2.0.html
	 * @since   1.8.9
	 */
	public function __construct() {

		self::$version      = get_option( 'pods_framework_version' );
		self::$version_last = get_option( 'pods_framework_version_last' );
		self::$db_version   = get_option( 'pods_framework_db_version' );
		self::$upgraded     = (int) get_option( 'pods_framework_upgraded_1_x' );

		if ( empty( self::$version_last ) && 0 < strlen( get_option( 'pods_version' ) ) ) {
			$old_version = get_option( 'pods_version' );

			if ( ! empty( $old_version ) ) {
				if ( false === strpos( $old_version, '.' ) ) {
					$old_version = pods_version_to_point( $old_version );
				}

				update_option( 'pods_framework_version_last', $old_version );

				self::$version_last = $old_version;
			}
		}

		self::$upgrade_needed = $this->needs_upgrade();

		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
		add_action( 'plugins_loaded', array( $this, 'activate_install' ), 9 );
		add_action( 'after_setup_theme', array( $this, 'after_setup_theme' ) );

		add_action( 'wp_loaded', array( $this, 'flush_rewrite_rules' ) );

		$this->run();

	}

	/**
	 * Load the plugin textdomain and set default constants
	 */
	public function plugins_loaded() {

		if ( ! defined( 'PODS_LIGHT' ) ) {
			define( 'PODS_LIGHT', false );
		}

		if ( ! defined( 'PODS_TABLELESS' ) ) {
			define( 'PODS_TABLELESS', false );
		}

		load_plugin_textdomain( 'pods' );

		$this->freemius();

	}

	/**
	 * Handle Freemius SDK registration.
	 *
	 * @since 1.0.0
	 *
	 * @return \Freemius
	 */
	public function freemius() {
		// Admin only.
		if ( ! is_admin() ) {
			return;
		}

		global $pagenow;

		// Pods admin pages or plugins/update page only.
		if (
			'plugins.php' !== $pagenow
			&& 'update-core.php' !== $pagenow
			&& 'update.php' !== $pagenow
			&& ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX )
			&& ( ! isset( $_GET['page'] ) || 0 !== strpos( $_GET['page'], 'pods' ) )
		) {
			return;
		}

		if ( $this->freemius ) {
			return $this->freemius;
		}

		require_once dirname( __DIR__ ) . '/vendor/freemius/wordpress-sdk/start.php';

		try {
			$this->freemius = fs_dynamic_init( array(
				'id'             => '5347',
				'slug'           => 'pods',
				'type'           => 'plugin',
				'public_key'     => 'pk_737105490825babae220297e18920',
				'is_premium'     => false,
				'has_addons'     => true,
				'has_paid_plans' => false,
				'menu'           => array(
					'slug'        => 'pods-settings',
					'contact'     => false,
					'support'     => false,
					'affiliation' => false,
					'account'     => true,
					'pricing'     => false,
					'addons'      => true,
					'parent'      => array(
						'slug' => 'pods',
					),
				),
			) );

			$this->override_freemius_strings();

			add_filter( 'fs_plugins_api', array( $this, 'filter_freemius_plugins_api_data' ), 15 );

			$this->freemius->add_filter( 'templates/add-ons.php', array( $this, 'filter_freemius_addons_html' ) );
			$this->freemius->add_filter( 'download_latest_url', array( $this, 'get_freemius_action_link' ) );

			/**
			 * Allow hooking into the Freemius registration after Pods has registered it's own Freemius.
			 */
			do_action( 'pods_freemius_init' );
		} catch ( \Exception $exception ) {
			return null;
		}

		return $this->freemius;
	}

	/**
	 * Override Freemius strings.
	 */
	public function override_freemius_strings() {
		$override_text = array(
			'free'                     => __( 'Free (WordPress.org)', 'pods' ),
			'install-free-version-now' => __( 'Install Now', 'pods' ),
			'download-latest'          => __( 'Donate', 'pods' ),
			'complete-the-install'     => __( 'complete the process', 'pods' ),
		);

		$freemius_addons = $this->get_freemius_addons();

		fs_override_i18n( $override_text, 'pods' );

		foreach ( $freemius_addons as $addon_slug => $addon ) {
			fs_override_i18n( $override_text, $addon_slug );
		}
	}

	/**
	 * Filter the Freemius plugins API data.
	 *
	 * @since 2.7.17
	 *
	 * @param object $data Freemius plugins API data.
	 *
	 * @return object Freemius plugins API data.
	 */
	public function filter_freemius_plugins_api_data( $data ) {
		if ( empty( $data->sections['features'] ) ) {
			return $data;
		}

		$data->sections['features'] = preg_replace( '/(<span\s+class="fs-price"><\/span>)/Uim', '<span class="fs-price">Friends-only</span>', $data->sections['features'] );

		return $data;
	}

	/**
	 * Filter the Freemius add-ons HTML.
	 *
	 * @since 2.7.17
	 *
	 * @param string $html Freemius add-ons HTML.
	 *
	 * @return string Freemius add-ons HTML.
	 */
	public function filter_freemius_addons_html( $html ) {
		$freemius_friends_addons = $this->get_freemius_friends_addons();

		// Replace blank prices with Friends-only.
		$html = preg_replace( '/<span\s+class="fs-price"><\/span>/Uim', '<span class="fs-price">Friends-only</span>', $html );

		// Remove dropdown arrow for action links.
		$html = preg_replace( '/<div\s+class="button button-primary fs-dropdown-arrow-button">/Uim', '<div class="hidden">', $html );

		// Use landing page for Become a Friend link.
		$replace = '$1<a target="_blank" rel="noopener noreferrer" href="' . esc_url( $this->get_freemius_action_link() ) . '"$2class="$3">';

		// Replace all Friends-only add-on links.
		foreach ( $freemius_friends_addons as $addon_slug => $addon ) {
			$pattern = '/(<li class="fs-card fs-addon" data-slug="' . preg_quote( esc_attr( $addon_slug ), '/' ) . '">\s+)<a href="[^"]+"([^>]+)class="thickbox([^>]+)">/Uim';

			$html = preg_replace( $pattern, $replace, $html );
		}

		return $html;
	}

	/**
	 * Get action link URL.
	 *
	 * @since 2.7.17
	 *
	 * @param string $url Action link URL.
	 *
	 * @return string Action link URL.
	 */
	public function get_freemius_action_link( $url = null ) {
		return 'https://friends.pods.io/add-ons/';
	}

	/**
	 * Get list of add-ons for Freemius.
	 *
	 * @since 2.7.17
	 *
	 * @return array List of add-ons for Freemius.
	 */
	public function get_freemius_addons() {
		return array(
			'pods-beaver-builder-themer-add-on' => 'Pods Beaver Themer Add-On',
			'pods-gravity-forms'                => 'Pods Gravity Forms Add-On',
			'pods-alternative-cache'            => 'Pods Alternative Cache',
			'pods-simple-relationships'         => 'Pods Simple Relationships',
			'pods-seo'                          => 'Pods SEO',
			'pods-ajax-views'                   => 'Pods AJAX Views',
		);
	}

	/**
	 * Get list of Friends-only add-ons for Freemius.
	 *
	 * @since TB2.7.17D
	 *
	 * @return array List of Friends-only add-ons for Freemius.
	 */
	public function get_freemius_friends_addons() {
		return array(
			'pods-simple-relationships' => 'Pods Simple Relationships',
		);
	}

	/**
	 * Add compatibility for other plugins.
	 *
	 * @since 2.7.17
	 */
	public function after_setup_theme() {

		if ( ! defined( 'PODS_COMPATIBILITY' ) ) {
			define( 'PODS_COMPATIBILITY', true );
		}

		if ( ! PODS_COMPATIBILITY || is_admin() ) {
			return;
		}

		require_once PODS_DIR . 'includes/compatibility/acf.php';

	}

	/**
	 * Load Pods Components
	 */
	public function load_components() {

		if ( empty( self::$version ) ) {
			return;
		}

		if ( ! pods_light() ) {
			self::$components = pods_components();
		}

	}

	/**
	 * Load Pods Meta
	 */
	public function load_meta() {

		self::$meta = pods_meta()->core();
	}

	/**
	 *
	 */
	public function load_i18n() {

		self::$i18n = pods_i18n();
	}

	/**
	 * Set up the Pods core
	 */
	public function core() {

		if ( empty( self::$version ) ) {
			return;
		}

		// Session start
		pods_session_start();

		add_shortcode( 'pods', 'pods_shortcode' );
		add_shortcode( 'pods-form', 'pods_shortcode_form' );

		$security_settings = array(
			'pods_disable_file_browser'     => 0,
			'pods_files_require_login'      => 1,
			'pods_files_require_login_cap'  => '',
			'pods_disable_file_upload'      => 0,
			'pods_upload_require_login'     => 1,
			'pods_upload_require_login_cap' => '',
		);

		foreach ( $security_settings as $security_setting => $setting ) {
			$setting = get_option( $security_setting );
			if ( ! empty( $setting ) ) {
				$security_settings[ $security_setting ] = $setting;
			}
		}

		foreach ( $security_settings as $security_setting => $setting ) {
			if ( 0 === (int) $setting ) {
				$setting = false;
			} elseif ( 1 === (int) $setting ) {
				$setting = true;
			}

			if ( in_array(
				$security_setting, array(
					'pods_files_require_login',
					'pods_upload_require_login',
				), true
			) ) {
				if ( 0 < strlen( $security_settings[ $security_setting . '_cap' ] ) ) {
					$setting = $security_settings[ $security_setting . '_cap' ];
				}
			} elseif ( in_array(
				$security_setting, array(
					'pods_files_require_login_cap',
					'pods_upload_require_login_cap',
				), true
			) ) {
				continue;
			}

			if ( ! defined( strtoupper( $security_setting ) ) ) {
				define( strtoupper( $security_setting ), $setting );
			}
		}//end foreach

		$this->register_pods();

		$avatar = PodsForm::field_loader( 'avatar' );

		if ( method_exists( $avatar, 'get_avatar' ) ) {
			add_filter( 'get_avatar', array( $avatar, 'get_avatar' ), 10, 4 );
		}
	}

	/**
	 * Register Scripts and Styles
	 */
	public function register_assets() {

		$maybe_min = SCRIPT_DEBUG ? '' : '.min';

		wp_register_script( 'pods-json', PODS_URL . 'ui/js/jquery.json.js', array( 'jquery' ), '2.3' );

		if ( ! wp_script_is( 'jquery-qtip2', 'registered' ) ) {
			wp_register_script( 'jquery-qtip2', PODS_URL . 'ui/js/jquery.qtip.min.js', array( 'jquery' ), '2.2' );
		}

		wp_register_script(
			'pods', PODS_URL . 'ui/js/jquery.pods.js', array(
				'jquery',
				'pods-dfv',
				'pods-i18n',
				'pods-json',
				'jquery-qtip2',
			), PODS_VERSION, true
		);

		wp_register_script( 'pods-cleditor', PODS_URL . 'ui/js/jquery.cleditor.min.js', array( 'jquery' ), '1.3.0' );

		wp_register_script( 'pods-codemirror', PODS_URL . 'ui/js/codemirror.js', array(), '4.8', true );
		wp_register_script( 'pods-codemirror-loadmode', PODS_URL . 'ui/js/codemirror/addon/mode/loadmode.js', array( 'pods-codemirror' ), '4.8', true );
		wp_register_script( 'pods-codemirror-overlay', PODS_URL . 'ui/js/codemirror/addon/mode/overlay.js', array( 'pods-codemirror' ), '4.8', true );
		wp_register_script( 'pods-codemirror-hints', PODS_URL . 'ui/js/codemirror/addon/mode/show-hint.js', array( 'pods-codemirror' ), '4.8', true );
		wp_register_script( 'pods-codemirror-mode-xml', PODS_URL . 'ui/js/codemirror/mode/xml/xml.js', array( 'pods-codemirror' ), '4.8', true );
		wp_register_script( 'pods-codemirror-mode-html', PODS_URL . 'ui/js/codemirror/mode/htmlmixed/htmlmixed.js', array( 'pods-codemirror' ), '4.8', true );
		wp_register_script( 'pods-codemirror-mode-css', PODS_URL . 'ui/js/codemirror/mode/css/css.js', array( 'pods-codemirror' ), '4.8', true );

		if ( ! wp_script_is( 'jquery-ui-slideraccess', 'registered' ) ) {
			// No need to add dependencies. All managed by jquery-ui-timepicker.
			wp_register_script( 'jquery-ui-slideraccess', PODS_URL . 'ui/js/timepicker/jquery-ui-sliderAccess.js', array(), '0.3' );
		}

		if ( ! wp_script_is( 'jquery-ui-timepicker', 'registered' ) ) {
			wp_register_script(
				'jquery-ui-timepicker', PODS_URL . 'ui/js/timepicker/jquery-ui-timepicker-addon.min.js', array(
					'jquery',
					'jquery-ui-core',
					'jquery-ui-datepicker',
					'jquery-ui-slider',
					'jquery-ui-slideraccess',
				), '1.6.3'
			);
		}
		if ( ! wp_style_is( 'jquery-ui-timepicker', 'registered' ) ) {
			wp_register_style( 'jquery-ui-timepicker', PODS_URL . 'ui/js/timepicker/jquery-ui-timepicker-addon.min.css', array(), '1.6.3' );
		}

		wp_register_script(
			'pods-select2', PODS_URL . "ui/js/selectWoo/selectWoo{$maybe_min}.js", array(
				'jquery',
				'pods-i18n',
			), '1.0.1'
		);
		wp_register_style( 'pods-select2', PODS_URL . "ui/js/selectWoo/selectWoo{$maybe_min}.css", array(), '1.0.2' );

		// Marionette dependencies for MV fields
		wp_register_script( 'backbone.radio', PODS_URL . 'ui/js/marionette/backbone.radio.min.js', array( 'backbone' ), '2.0.0', true );
		wp_register_script(
			'marionette',
			PODS_URL . 'ui/js/marionette/backbone.marionette.min.js',
			array(
				'backbone',
				'backbone.radio',
			), '3.3.1', true
		);
		wp_add_inline_script(
			'marionette',
			'PodsMn = Backbone.Marionette.noConflict();'
		);

		// MV stuff
		wp_register_script(
			'pods-dfv', PODS_URL . 'ui/js/pods-dfv/pods-dfv.min.js', array(
				'jquery',
				'jquery-ui-core',
				'jquery-ui-sortable',
				'pods-i18n',
				'marionette',
				'media-views',
				'media-models',
			), PODS_VERSION, true
		);

		// Check if Pod is a Modal Window
		if ( pods_is_modal_window() ) {
			add_filter( 'body_class', array( $this, 'add_classes_to_modal_body' ) );
			add_filter( 'admin_body_class', array( $this, 'add_classes_to_modal_body' ) );
		}

		// Deal with specifics on admin pages
		if ( is_admin() && function_exists( 'get_current_screen' ) ) {
			$screen = get_current_screen();

			// DFV must be enqueued on the media library page for items in grid mode (#4785)
			// and for posts due to the possibility that post-thumbnails are enabled (#4945)
			if ( $screen->base && in_array( $screen->base, array( 'upload', 'post' ), true ) ) {
				wp_enqueue_script( 'pods-dfv' );
			}
		}

		$this->maybe_register_handlebars();

		// As of 2.7 we combine styles to just three .css files
		wp_register_style( 'pods-styles', PODS_URL . 'ui/styles/dist/pods.css', array(), PODS_VERSION );
		wp_register_style( 'pods-wizard', PODS_URL . 'ui/styles/dist/pods-wizard.css', array(), PODS_VERSION );
		wp_register_style( 'pods-form', PODS_URL . 'ui/styles/dist/pods-form.css', array(), PODS_VERSION );

		/**
		 * Filter to enabled loading of the DFV script on frontend.
		 * By default, Pods does not load DFV on frontend.
		 *
		 * Example: add_filter( 'pods_enqueue_dfv_on_front', '__return_true' );
		 *
		 * @param bool Whether or not to enqueue by default
		 *
		 * @since 2.7.13
		 */
		if ( apply_filters( 'pods_enqueue_dfv_on_front', false ) ) {
			wp_enqueue_script( 'pods-dfv' );
			wp_enqueue_style( 'pods-form' );
		}
	}

	/**
	 * Register handlebars where needed
	 *
	 * @since 2.7.2
	 */
	private function maybe_register_handlebars() {

		$register_handlebars = apply_filters( 'pods_script_register_handlebars', true );

		if ( is_admin() && function_exists( 'get_current_screen' ) ) {
			$screen = get_current_screen();

			// Deregister the outdated Pods handlebars script on TEC event screen
			if ( $screen && 'tribe_events' === $screen->post_type ) {
				$register_handlebars = false;
			}
		}

		if ( $register_handlebars ) {
			wp_register_script( 'pods-handlebars', PODS_URL . 'ui/js/handlebars.js', array(), '1.0.0.beta.6' );
		}
	}

	/**
	 * @param string|array $classes Body classes.
	 *
	 * @return string|array
	 */
	public function add_classes_to_modal_body( $classes ) {

		if ( is_array( $classes ) ) {
			$classes[] = 'pods-modal-window';
		} else {
			$classes .= ' pods-modal-window';
		}

		return $classes;
	}

	/**
	 * Register internal Post Types
	 */
	public function register_pods() {

		$args = array(
			'label'           => 'Pods',
			'labels'          => array( 'singular_name' => 'Pod' ),
			'public'          => false,
			'can_export'      => false,
			'query_var'       => false,
			'rewrite'         => false,
			'capability_type' => 'pods_pod',
			'has_archive'     => false,
			'hierarchical'    => false,
			'supports'        => array( 'title', 'author' ),
			'menu_icon'       => 'dashicons-pods',
		);

		$args = self::object_label_fix( $args, 'post_type' );

		register_post_type( '_pods_pod', apply_filters( 'pods_internal_register_post_type_pod', $args ) );

		$args = array(
			'label'           => 'Pod Fields',
			'labels'          => array( 'singular_name' => 'Pod Field' ),
			'public'          => false,
			'can_export'      => false,
			'query_var'       => false,
			'rewrite'         => false,
			'capability_type' => 'pods_pod',
			'has_archive'     => false,
			'hierarchical'    => true,
			'supports'        => array( 'title', 'editor', 'author' ),
			'menu_icon'       => 'dashicons-pods',
		);

		$args = self::object_label_fix( $args, 'post_type' );

		register_post_type( '_pods_field', apply_filters( 'pods_internal_register_post_type_field', $args ) );
	}

	/**
	 * Include Admin
	 */
	public function admin_init() {

		self::$admin = pods_admin();
	}

	/**
	 * Register Post Types and Taxonomies
	 *
	 * @param bool $force
	 */
	public function setup_content_types( $force = false ) {

		if ( empty( self::$version ) ) {
			return;
		}

		require_once PODS_DIR . 'classes/PodsRESTHandlers.php';
		require_once PODS_DIR . 'classes/PodsRESTFields.php';

		$post_types = PodsMeta::$post_types;
		$taxonomies = PodsMeta::$taxonomies;

		$existing_post_types = get_post_types();
		$existing_taxonomies = get_taxonomies();

		$pods_cpt_ct = pods_transient_get( 'pods_wp_cpt_ct' );

		$cpt_positions = array();

		if ( empty( $pods_cpt_ct ) && ( ! empty( $post_types ) || ! empty( $taxonomies ) ) ) {
			$force = true;
		} elseif ( ! empty( $pods_cpt_ct ) && empty( $pods_cpt_ct['post_types'] ) && ! empty( $post_types ) ) {
			$force = true;
		} elseif ( ! empty( $pods_cpt_ct ) && empty( $pods_cpt_ct['taxonomies'] ) && ! empty( $taxonomies ) ) {
			$force = true;
		}

		if ( false === $pods_cpt_ct || $force ) {
			/**
			 * @var WP_Query
			 */
			global $wp_query;

			$reserved_query_vars = array(
				'post_type',
				'taxonomy',
				'output',
			);

			if ( is_object( $wp_query ) ) {
				$reserved_query_vars = array_merge( $reserved_query_vars, array_keys( $wp_query->fill_query_vars( array() ) ) );
			}

			$pods_cpt_ct = array(
				'post_types' => array(),
				'taxonomies' => array(),
			);

			$pods_post_types      = array();
			$pods_taxonomies      = array();
			$supported_post_types = array();
			$supported_taxonomies = array();

			$post_format_post_types = array();

			foreach ( $post_types as $post_type ) {
				if ( isset( $pods_cpt_ct['post_types'][ $post_type['name'] ] ) ) {
					// Post type was setup already
					continue;
				} elseif ( ! empty( $post_type['object'] ) && isset( $existing_post_types[ $post_type['object'] ] ) ) {
					// Post type exists already
					continue;
				} elseif ( ! $force && isset( $existing_post_types[ $post_type['name'] ] ) ) {
					// Post type was setup and exists already, but we aren't forcing it to be setup again
					continue;
				}

				$post_type['options']['name'] = $post_type['name'];
				$post_type                    = array_merge( $post_type, (array) $post_type['options'] );

				$post_type_name = pods_v_sanitized( 'name', $post_type );

				// Labels
				$cpt_label    = esc_html( pods_v( 'label', $post_type, ucwords( str_replace( '_', ' ', pods_v( 'name', $post_type ) ) ), true ) );
				$cpt_singular = esc_html( pods_v( 'label_singular', $post_type, ucwords( str_replace( '_', ' ', pods_v( 'label', $post_type, $post_type_name, true ) ) ), true ) );

				$cpt_labels                             = array();
				$cpt_labels['name']                     = $cpt_label;
				$cpt_labels['singular_name']            = $cpt_singular;
				$cpt_labels['menu_name']                = pods_v( 'menu_name', $post_type, '', true );
				$cpt_labels['name_admin_bar']           = pods_v( 'name_admin_bar', $post_type, '', true );
				$cpt_labels['add_new']                  = pods_v( 'label_add_new', $post_type, '', true );
				$cpt_labels['add_new_item']             = pods_v( 'label_add_new_item', $post_type, '', true );
				$cpt_labels['new_item']                 = pods_v( 'label_new_item', $post_type, '', true );
				$cpt_labels['edit']                     = pods_v( 'label_edit', $post_type, '', true );
				$cpt_labels['edit_item']                = pods_v( 'label_edit_item', $post_type, '', true );
				$cpt_labels['view']                     = pods_v( 'label_view', $post_type, '', true );
				$cpt_labels['view_item']                = pods_v( 'label_view_item', $post_type, '', true );
				$cpt_labels['view_items']               = pods_v( 'label_view_items', $post_type, '', true );
				$cpt_labels['all_items']                = pods_v( 'label_all_items', $post_type, '', true );
				$cpt_labels['search_items']             = pods_v( 'label_search_items', $post_type, '', true );
				$cpt_labels['not_found']                = pods_v( 'label_not_found', $post_type, '', true );
				$cpt_labels['not_found_in_trash']       = pods_v( 'label_not_found_in_trash', $post_type, '', true );
				$cpt_labels['parent']                   = pods_v( 'label_parent', $post_type, '', true );
				$cpt_labels['parent_item_colon']        = pods_v( 'label_parent_item_colon', $post_type, '', true );
				$cpt_labels['archives']                 = pods_v( 'label_archives', $post_type, '', true );
				$cpt_labels['attributes']               = pods_v( 'label_attributes', $post_type, '', true );
				$cpt_labels['insert_into_item']         = pods_v( 'label_insert_into_item', $post_type, '', true );
				$cpt_labels['uploaded_to_this_item']    = pods_v( 'label_uploaded_to_this_item', $post_type, '', true );
				$cpt_labels['featured_image']           = pods_v( 'label_featured_image', $post_type, '', true );
				$cpt_labels['set_featured_image']       = pods_v( 'label_set_featured_image', $post_type, '', true );
				$cpt_labels['remove_featured_image']    = pods_v( 'label_remove_featured_image', $post_type, '', true );
				$cpt_labels['use_featured_image']       = pods_v( 'label_use_featured_image', $post_type, '', true );
				$cpt_labels['filter_items_list']        = pods_v( 'label_filter_items_list', $post_type, '', true );
				$cpt_labels['items_list_navigation']    = pods_v( 'label_items_list_navigation', $post_type, '', true );
				$cpt_labels['items_list']               = pods_v( 'label_items_list', $post_type, '', true );
				$cpt_labels['item_published']           = pods_v( 'label_item_published', $post_type, '', true );
				$cpt_labels['item_published_privately'] = pods_v( 'label_item_published_privately', $post_type, '', true );
				$cpt_labels['item_reverted_to_draft']   = pods_v( 'label_item_reverted_to_draft', $post_type, '', true );
				$cpt_labels['item_scheduled']           = pods_v( 'label_item_scheduled', $post_type, '', true );
				$cpt_labels['item_updated']             = pods_v( 'label_item_updated', $post_type, '', true );

				// Supported
				$cpt_supported = array(
					'title'           => (boolean) pods_v( 'supports_title', $post_type, false ),
					'editor'          => (boolean) pods_v( 'supports_editor', $post_type, false ),
					'author'          => (boolean) pods_v( 'supports_author', $post_type, false ),
					'thumbnail'       => (boolean) pods_v( 'supports_thumbnail', $post_type, false ),
					'excerpt'         => (boolean) pods_v( 'supports_excerpt', $post_type, false ),
					'trackbacks'      => (boolean) pods_v( 'supports_trackbacks', $post_type, false ),
					'custom-fields'   => (boolean) pods_v( 'supports_custom_fields', $post_type, false ),
					'comments'        => (boolean) pods_v( 'supports_comments', $post_type, false ),
					'revisions'       => (boolean) pods_v( 'supports_revisions', $post_type, false ),
					'page-attributes' => (boolean) pods_v( 'supports_page_attributes', $post_type, false ),
					'post-formats'    => (boolean) pods_v( 'supports_post_formats', $post_type, false ),
				);

				// Custom Supported
				$cpt_supported_custom = pods_v_sanitized( 'supports_custom', $post_type, '' );

				if ( ! empty( $cpt_supported_custom ) ) {
					$cpt_supported_custom = explode( ',', $cpt_supported_custom );
					$cpt_supported_custom = array_filter( array_unique( $cpt_supported_custom ) );

					foreach ( $cpt_supported_custom as $cpt_support ) {
						$cpt_supported[ $cpt_support ] = true;
					}
				}

				// Genesis Support
				if ( function_exists( 'genesis' ) ) {
					$cpt_supported['genesis-seo']             = (boolean) pods_v( 'supports_genesis_seo', $post_type, false );
					$cpt_supported['genesis-layouts']         = (boolean) pods_v( 'supports_genesis_layouts', $post_type, false );
					$cpt_supported['genesis-simple-sidebars'] = (boolean) pods_v( 'supports_genesis_simple_sidebars', $post_type, false );
				}

				// YARPP Support
				if ( defined( 'YARPP_VERSION' ) ) {
					$cpt_supported['yarpp_support'] = (boolean) pods_v( 'supports_yarpp_support', $post_type, false );
				}

				// Jetpack Support
				if ( class_exists( 'Jetpack' ) ) {
					$cpt_supported['supports_jetpack_publicize'] = (boolean) pods_v( 'supports_jetpack_publicize', $post_type, false );
					$cpt_supported['supports_jetpack_markdown']  = (boolean) pods_v( 'supports_jetpack_markdown', $post_type, false );
				}

				$cpt_supports = array();

				foreach ( $cpt_supported as $cpt_support => $supported ) {
					if ( true === $supported ) {
						$cpt_supports[] = $cpt_support;

						if ( 'post-formats' === $cpt_support ) {
							$post_format_post_types[] = $post_type_name;
						}
					}
				}

				if ( empty( $cpt_supports ) ) {
					$cpt_supports = false;
				}

				// Rewrite
				$cpt_rewrite       = (boolean) pods_v( 'rewrite', $post_type, true );
				$cpt_rewrite_array = array(
					'slug'       => pods_v( 'rewrite_custom_slug', $post_type, str_replace( '_', '-', $post_type_name ), true ),
					'with_front' => (boolean) pods_v( 'rewrite_with_front', $post_type, true ),
					'feeds'      => (boolean) pods_v( 'rewrite_feeds', $post_type, (boolean) pods_v( 'has_archive', $post_type, false ) ),
					'pages'      => (boolean) pods_v( 'rewrite_pages', $post_type, true ),
				);

				if ( false !== $cpt_rewrite ) {
					$cpt_rewrite = $cpt_rewrite_array;
				}

				$capability_type = pods_v( 'capability_type', $post_type, 'post' );

				if ( 'custom' === $capability_type ) {
					$capability_type = pods_v( 'capability_type_custom', $post_type, 'post' );
				}

				$show_in_menu = (boolean) pods_v( 'show_in_menu', $post_type, true );

				if ( $show_in_menu && 0 < strlen( pods_v( 'menu_location_custom', $post_type ) ) ) {
					$show_in_menu = pods_v( 'menu_location_custom', $post_type );
				}

				$menu_icon = pods_v( 'menu_icon', $post_type );

				if ( ! empty( $menu_icon ) ) {
					$menu_icon = pods_evaluate_tags( $menu_icon );
				}

				// Register Post Type
				$pods_post_types[ $post_type_name ] = array(
					'label'               => $cpt_label,
					'labels'              => $cpt_labels,
					'description'         => esc_html( pods_v( 'description', $post_type ) ),
					'public'              => (boolean) pods_v( 'public', $post_type, true ),
					'publicly_queryable'  => (boolean) pods_v( 'publicly_queryable', $post_type, (boolean) pods_v( 'public', $post_type, true ) ),
					'exclude_from_search' => (boolean) pods_v( 'exclude_from_search', $post_type, ( (boolean) pods_v( 'public', $post_type, true ) ? false : true ) ),
					'show_ui'             => (boolean) pods_v( 'show_ui', $post_type, (boolean) pods_v( 'public', $post_type, true ) ),
					'show_in_menu'        => $show_in_menu,
					'show_in_nav_menus'   => (boolean) pods_v( 'show_in_nav_menus', $post_type, (boolean) pods_v( 'public', $post_type, true ) ),
					'show_in_admin_bar'   => (boolean) pods_v( 'show_in_admin_bar', $post_type, (boolean) pods_v( 'show_in_menu', $post_type, true ) ),
					'menu_position'       => (int) pods_v( 'menu_position', $post_type, 0, true ),
					'menu_icon'           => $menu_icon,
					'capability_type'     => $capability_type,
					// 'capabilities' => $cpt_capabilities,
					'map_meta_cap'        => (boolean) pods_v( 'capability_type_extra', $post_type, true ),
					'hierarchical'        => (boolean) pods_v( 'hierarchical', $post_type, false ),
					'supports'            => $cpt_supports,
					// 'register_meta_box_cb' => array($this, 'manage_meta_box'),
					// 'permalink_epmask' => EP_PERMALINK,
					'has_archive'         => pods_v( 'has_archive_slug', $post_type, (boolean) pods_v( 'has_archive', $post_type, false ), true ),
					'rewrite'             => $cpt_rewrite,
					'query_var'           => ( false !== (boolean) pods_v( 'query_var', $post_type, true ) ? pods_v( 'query_var_string', $post_type, $post_type_name, true ) : false ),
					'can_export'          => (boolean) pods_v( 'can_export', $post_type, true ),
				);

				// REST API
				$rest_enabled = (boolean) pods_v( 'rest_enable', $post_type, false );

				if ( $rest_enabled ) {
					$rest_base = sanitize_title( pods_v( 'rest_base', $post_type, $post_type_name ) );

					$pods_post_types[ $post_type_name ]['show_in_rest']          = true;
					$pods_post_types[ $post_type_name ]['rest_base']             = $rest_base;
					$pods_post_types[ $post_type_name ]['rest_controller_class'] = 'WP_REST_Posts_Controller';
				}

				// YARPP doesn't use 'supports' array option (yet)
				if ( ! empty( $cpt_supports['yarpp_support'] ) ) {
					$pods_post_types[ $post_type_name ]['yarpp_support'] = true;
				}

				// Prevent reserved query_var issues
				if ( in_array( $pods_post_types[ $post_type_name ]['query_var'], $reserved_query_vars, true ) ) {
					$pods_post_types[ $post_type_name ]['query_var'] = 'post_type_' . $pods_post_types[ $post_type_name ]['query_var'];
				}

				if ( 25 === (int) $pods_post_types[ $post_type_name ]['menu_position'] ) {
					$pods_post_types[ $post_type_name ]['menu_position'] ++;
				}

				if ( $pods_post_types[ $post_type_name ]['menu_position'] < 1 || in_array( $pods_post_types[ $post_type_name ]['menu_position'], $cpt_positions, true ) ) {
					unset( $pods_post_types[ $post_type_name ]['menu_position'] );
				} else {
					$cpt_positions[] = $pods_post_types[ $post_type_name ]['menu_position'];

					// This would be nice if WP supported floats in menu_position
					// $pods_post_types[ $post_type_name ][ 'menu_position' ] = $pods_post_types[ $post_type_name ][ 'menu_position' ] . '.1';
				}

				// Taxonomies
				$cpt_taxonomies = array();
				$_taxonomies    = get_taxonomies();
				$_taxonomies    = array_merge_recursive( $_taxonomies, $pods_taxonomies );
				$ignore         = array( 'nav_menu', 'link_category', 'post_format' );

				foreach ( $_taxonomies as $taxonomy => $label ) {
					if ( in_array( $taxonomy, $ignore, true ) ) {
						continue;
					}

					if ( false !== (boolean) pods_v( 'built_in_taxonomies_' . $taxonomy, $post_type, false ) ) {
						$cpt_taxonomies[] = $taxonomy;

						if ( isset( $supported_post_types[ $taxonomy ] ) && ! in_array( $post_type_name, $supported_post_types[ $taxonomy ], true ) ) {
							$supported_post_types[ $taxonomy ][] = $post_type_name;
						}
					}
				}

				if ( isset( $supported_taxonomies[ $post_type_name ] ) ) {
					$supported_taxonomies[ $post_type_name ] = array_merge( (array) $supported_taxonomies[ $post_type_name ], $cpt_taxonomies );
				} else {
					$supported_taxonomies[ $post_type_name ] = $cpt_taxonomies;
				}
			}//end foreach

			foreach ( $taxonomies as $taxonomy ) {
				if ( isset( $pods_cpt_ct['taxonomies'][ $taxonomy['name'] ] ) ) {
					// Taxonomy was setup already
					continue;
				} elseif ( ! empty( $taxonomy['object'] ) && isset( $existing_taxonomies[ $taxonomy['object'] ] ) ) {
					// Taxonomy exists already
					continue;
				} elseif ( ! $force && isset( $existing_taxonomies[ $taxonomy['name'] ] ) ) {
					// Taxonomy was setup and exists already, but we aren't forcing it to be setup again
					continue;
				}

				$taxonomy['options']['name'] = $taxonomy['name'];
				$taxonomy                    = array_merge( $taxonomy, (array) $taxonomy['options'] );

				$taxonomy_name = pods_v( 'name', $taxonomy );

				// Labels
				$ct_label    = esc_html( pods_v( 'label', $taxonomy, ucwords( str_replace( '_', ' ', pods_v( 'name', $taxonomy ) ) ), true ) );
				$ct_singular = esc_html( pods_v( 'label_singular', $taxonomy, ucwords( str_replace( '_', ' ', pods_v( 'label', $taxonomy, pods_v( 'name', $taxonomy ), true ) ) ), true ) );

				$ct_labels                               = array();
				$ct_labels['name']                       = $ct_label;
				$ct_labels['singular_name']              = $ct_singular;
				$ct_labels['menu_name']                  = pods_v( 'menu_name', $taxonomy, '', true );
				$ct_labels['search_items']               = pods_v( 'label_search_items', $taxonomy, '', true );
				$ct_labels['popular_items']              = pods_v( 'label_popular_items', $taxonomy, '', true );
				$ct_labels['all_items']                  = pods_v( 'label_all_items', $taxonomy, '', true );
				$ct_labels['parent_item']                = pods_v( 'label_parent_item', $taxonomy, '', true );
				$ct_labels['parent_item_colon']          = pods_v( 'label_parent_item_colon', $taxonomy, '', true );
				$ct_labels['edit_item']                  = pods_v( 'label_edit_item', $taxonomy, '', true );
				$ct_labels['update_item']                = pods_v( 'label_update_item', $taxonomy, '', true );
				$ct_labels['view_item']                  = pods_v( 'label_view_item', $taxonomy, '', true );
				$ct_labels['add_new_item']               = pods_v( 'label_add_new_item', $taxonomy, '', true );
				$ct_labels['new_item_name']              = pods_v( 'label_new_item_name', $taxonomy, '', true );
				$ct_labels['separate_items_with_commas'] = pods_v( 'label_separate_items_with_commas', $taxonomy, '', true );
				$ct_labels['add_or_remove_items']        = pods_v( 'label_add_or_remove_items', $taxonomy, '', true );
				$ct_labels['choose_from_most_used']      = pods_v( 'label_choose_from_the_most_used', $taxonomy, '', true );
				$ct_labels['not_found']                  = pods_v( 'label_not_found', $taxonomy, '', true );
				$ct_labels['no_terms']                   = pods_v( 'label_no_terms', $taxonomy, '', true );
				$ct_labels['items_list']                 = pods_v( 'label_items_list', $taxonomy, '', true );
				$ct_labels['items_list_navigation']      = pods_v( 'label_items_list_navigation', $taxonomy, '', true );

				// Rewrite
				$ct_rewrite       = (boolean) pods_v( 'rewrite', $taxonomy, true );
				$ct_rewrite_array = array(
					'slug'         => pods_v( 'rewrite_custom_slug', $taxonomy, str_replace( '_', '-', $taxonomy_name ), true ),
					'with_front'   => (boolean) pods_v( 'rewrite_with_front', $taxonomy, true ),
					'hierarchical' => (boolean) pods_v( 'rewrite_hierarchical', $taxonomy, (boolean) pods_v( 'hierarchical', $taxonomy, false ) ),
				);

				if ( false !== $ct_rewrite ) {
					$ct_rewrite = $ct_rewrite_array;
				}

				/**
				 * Default tax capabilities
				 *
				 * @see https://codex.wordpress.org/Function_Reference/register_taxonomy
				 */
				$capability_type  = pods_v( 'capability_type', $taxonomy, 'default' );
				$tax_capabilities = array();

				if ( 'custom' === $capability_type ) {
					$capability_type = pods_v( 'capability_type_custom', $taxonomy, 'default' );
					if ( ! empty( $capability_type ) && 'default' !== $capability_type ) {
						$capability_type       .= '_term';
						$capability_type_plural = $capability_type . 's';
						$tax_capabilities       = array(
							// Singular
							'edit_term'    => 'edit_' . $capability_type,
							'delete_term'  => 'delete_' . $capability_type,
							'assign_term'  => 'assign_' . $capability_type,
							// Plural
							'manage_terms' => 'manage_' . $capability_type_plural,
							'edit_terms'   => 'edit_' . $capability_type_plural,
							'delete_terms' => 'delete_' . $capability_type_plural,
							'assign_terms' => 'assign_' . $capability_type_plural,
						);
					}
				}

				// Register Taxonomy
				$pods_taxonomies[ $taxonomy_name ] = array(
					'label'                 => $ct_label,
					'labels'                => $ct_labels,
					'description'           => esc_html( pods_v( 'description', $taxonomy ) ),
					'public'                => (boolean) pods_v( 'public', $taxonomy, true ),
					'show_ui'               => (boolean) pods_v( 'show_ui', $taxonomy, (boolean) pods_v( 'public', $taxonomy, true ) ),
					'show_in_menu'          => (boolean) pods_v( 'show_in_menu', $taxonomy, (boolean) pods_v( 'public', $taxonomy, true ) ),
					'show_in_nav_menus'     => (boolean) pods_v( 'show_in_nav_menus', $taxonomy, (boolean) pods_v( 'public', $taxonomy, true ) ),
					'show_tagcloud'         => (boolean) pods_v( 'show_tagcloud', $taxonomy, (boolean) pods_v( 'show_ui', $taxonomy, (boolean) pods_v( 'public', $taxonomy, true ) ) ),
					'show_tagcloud_in_edit' => (boolean) pods_v( 'show_tagcloud_in_edit', $taxonomy, (boolean) pods_v( 'show_tagcloud', $taxonomy, (boolean) pods_v( 'show_ui', $taxonomy, (boolean) pods_v( 'public', $taxonomy, true ) ) ) ),
					'show_in_quick_edit'    => (boolean) pods_v( 'show_in_quick_edit', $taxonomy, (boolean) pods_v( 'show_ui', $taxonomy, (boolean) pods_v( 'public', $taxonomy, true ) ) ),
					'hierarchical'          => (boolean) pods_v( 'hierarchical', $taxonomy, false ),
					// 'capability_type'       => $capability_type,
					'capabilities'          => $tax_capabilities,
					// 'map_meta_cap'          => (boolean) pods_v( 'capability_type_extra', $taxonomy, true ),
					'update_count_callback' => pods_v( 'update_count_callback', $taxonomy, null, true ),
					'query_var'             => ( false !== (boolean) pods_v( 'query_var', $taxonomy, true ) ? pods_v( 'query_var_string', $taxonomy, $taxonomy_name, true ) : false ),
					'rewrite'               => $ct_rewrite,
					'show_admin_column'     => (boolean) pods_v( 'show_admin_column', $taxonomy, false ),
					'sort'                  => (boolean) pods_v( 'sort', $taxonomy, false ),
				);

				if ( is_array( $ct_rewrite ) && ! $pods_taxonomies[ $taxonomy_name ]['query_var'] ) {
					$pods_taxonomies[ $taxonomy_name ]['query_var'] = pods_v( 'query_var_string', $taxonomy, $taxonomy_name, true );
				}

				// Prevent reserved query_var issues
				if ( in_array( $pods_taxonomies[ $taxonomy_name ]['query_var'], $reserved_query_vars, true ) ) {
					$pods_taxonomies[ $taxonomy_name ]['query_var'] = 'taxonomy_' . $pods_taxonomies[ $taxonomy_name ]['query_var'];
				}

				// REST API
				$rest_enabled = (boolean) pods_v( 'rest_enable', $taxonomy, false );

				if ( $rest_enabled ) {
					$rest_base = sanitize_title( pods_v( 'rest_base', $taxonomy, $taxonomy_name ) );

					$pods_taxonomies[ $taxonomy_name ]['show_in_rest']          = true;
					$pods_taxonomies[ $taxonomy_name ]['rest_base']             = $rest_base;
					$pods_taxonomies[ $taxonomy_name ]['rest_controller_class'] = 'WP_REST_Terms_Controller';
				}

				// Integration for Single Value Taxonomy UI
				if ( function_exists( 'tax_single_value_meta_box' ) ) {
					$pods_taxonomies[ $taxonomy_name ]['single_value'] = (boolean) pods_v( 'single_value', $taxonomy, false );
					$pods_taxonomies[ $taxonomy_name ]['required']     = (boolean) pods_v( 'single_value_required', $taxonomy, false );
				}

				// Post Types
				$ct_post_types = array();
				$_post_types   = get_post_types();
				$_post_types   = array_merge_recursive( $_post_types, $pods_post_types );
				$ignore        = array( 'revision' );

				foreach ( $_post_types as $post_type => $options ) {
					if ( in_array( $post_type, $ignore, true ) ) {
						continue;
					}

					if ( false !== (boolean) pods_v( 'built_in_post_types_' . $post_type, $taxonomy, false ) ) {
						$ct_post_types[] = $post_type;

						if ( isset( $supported_taxonomies[ $post_type ] ) && ! in_array( $taxonomy_name, $supported_taxonomies[ $post_type ], true ) ) {
							$supported_taxonomies[ $post_type ][] = $taxonomy_name;
						}
					}
				}

				if ( isset( $supported_post_types[ $taxonomy_name ] ) ) {
					$supported_post_types[ $taxonomy_name ] = array_merge( $supported_post_types[ $taxonomy_name ], $ct_post_types );
				} else {
					$supported_post_types[ $taxonomy_name ] = $ct_post_types;
				}
			}//end foreach

			$pods_post_types = apply_filters( 'pods_wp_post_types', $pods_post_types );
			$pods_taxonomies = apply_filters( 'pods_wp_taxonomies', $pods_taxonomies );

			$supported_post_types = apply_filters( 'pods_wp_supported_post_types', $supported_post_types );
			$supported_taxonomies = apply_filters( 'pods_wp_supported_taxonomies', $supported_taxonomies );

			foreach ( $pods_taxonomies as $taxonomy => $options ) {
				$ct_post_types = null;

				if ( isset( $supported_post_types[ $taxonomy ] ) && ! empty( $supported_post_types[ $taxonomy ] ) ) {
					$ct_post_types = $supported_post_types[ $taxonomy ];
				}

				$pods_cpt_ct['taxonomies'][ $taxonomy ] = array(
					'post_types' => $ct_post_types,
					'options'    => $options,
				);
			}

			foreach ( $pods_post_types as $post_type => $options ) {
				if ( isset( $supported_taxonomies[ $post_type ] ) && ! empty( $supported_taxonomies[ $post_type ] ) ) {
					$options['taxonomies'] = $supported_taxonomies[ $post_type ];
				}

				$pods_cpt_ct['post_types'][ $post_type ] = $options;
			}

			$pods_cpt_ct['post_format_post_types'] = $post_format_post_types;

			pods_transient_set( 'pods_wp_cpt_ct', $pods_cpt_ct );
		}//end if

		foreach ( $pods_cpt_ct['taxonomies'] as $taxonomy => $options ) {
			if ( isset( self::$content_types_registered['taxonomies'] ) && in_array( $taxonomy, self::$content_types_registered['taxonomies'], true ) ) {
				continue;
			}

			$ct_post_types = $options['post_types'];
			$options       = $options['options'];

			$options = self::object_label_fix( $options, 'taxonomy' );

			/**
			 * Hide tagcloud compatibility
			 *
			 * @todo check https://core.trac.wordpress.org/ticket/36964
			 * @see  wp-admin/edit-tags.php L389
			 */
			if ( true !== (boolean) pods_v( 'show_tagcloud_in_edit', $options, (boolean) pods_v( 'show_tagcloud', $options, true ) ) ) {
				$options['labels']['popular_items'] = null;
			}

			// Max length for taxonomies are 32 characters
			$taxonomy = substr( $taxonomy, 0, 32 );

			/**
			 * Allow filtering of taxonomy options per taxonomy.
			 *
			 * @param array  $options       Taxonomy options
			 * @param string $taxonomy      Taxonomy name
			 * @param array  $ct_post_types Associated Post Types
			 */
			$options = apply_filters( "pods_register_taxonomy_{$taxonomy}", $options, $taxonomy, $ct_post_types );

			/**
			 * Allow filtering of taxonomy options.
			 *
			 * @param array  $options       Taxonomy options
			 * @param string $taxonomy      Taxonomy name
			 * @param array  $ct_post_types Associated post types
			 */
			$options = apply_filters( 'pods_register_taxonomy', $options, $taxonomy, $ct_post_types );

			if ( 1 === (int) pods_v( 'pods_debug_register', 'get', 0 ) && pods_is_admin( array( 'pods' ) ) ) {
				pods_debug( array( 'register_taxonomy', compact( 'taxonomy', 'ct_post_types', 'options' ) ) );
			}

			register_taxonomy( $taxonomy, $ct_post_types, $options );

			if ( ! empty( $options['show_in_rest'] ) ) {
				new PodsRESTFields( $taxonomy );
			}

			if ( ! isset( self::$content_types_registered['taxonomies'] ) ) {
				self::$content_types_registered['taxonomies'] = array();
			}

			self::$content_types_registered['taxonomies'][] = $taxonomy;
		}//end foreach

		foreach ( $pods_cpt_ct['post_types'] as $post_type => $options ) {
			if ( isset( self::$content_types_registered['post_types'] ) && in_array( $post_type, self::$content_types_registered['post_types'], true ) ) {
				continue;
			}

			$options = self::object_label_fix( $options, 'post_type' );

			// Max length for post types are 20 characters
			$post_type = substr( $post_type, 0, 20 );

			/**
			 * Allow filtering of post type options per post type.
			 *
			 * @param array  $options   Post type options
			 * @param string $post_type Post type name
			 */
			$options = apply_filters( "pods_register_post_type_{$post_type}", $options, $post_type );

			/**
			 * Allow filtering of post type options.
			 *
			 * @param array  $options   Post type options
			 * @param string $post_type Post type name
			 */
			$options = apply_filters( 'pods_register_post_type', $options, $post_type );

			if ( 1 === (int) pods_v( 'pods_debug_register', 'get', 0 ) && pods_is_admin( array( 'pods' ) ) ) {
				pods_debug( array( 'register_post_type', compact( 'post_type', 'options' ) ) );
			}

			register_post_type( $post_type, $options );

			// Register post format taxonomy for this post type
			if ( isset( $pods_cpt_ct['post_format_post_types'] ) && in_array( $post_type, $pods_cpt_ct['post_format_post_types'], true ) ) {
				register_taxonomy_for_object_type( 'post_format', $post_type );
			}

			if ( ! empty( $options['show_in_rest'] ) ) {
				new PodsRESTFields( $post_type );
			}

			if ( ! isset( self::$content_types_registered['post_types'] ) ) {
				self::$content_types_registered['post_types'] = array();
			}

			self::$content_types_registered['post_types'][] = $post_type;
		}//end foreach

		// Handle existing post types / taxonomies settings (just REST for now)
		global $wp_post_types, $wp_taxonomies;

		$post_type_names = wp_list_pluck( $post_types, 'name', 'id' );
		$taxonomy_names  = wp_list_pluck( $taxonomies, 'name', 'id' );

		foreach ( $existing_post_types as $post_type_name => $post_type_name_again ) {
			if ( isset( self::$content_types_registered['post_types'] ) && in_array( $post_type_name, self::$content_types_registered['post_types'], true ) ) {
				// Post type already registered / setup by Pods
				continue;
			}

			$pod_id = array_search( $post_type_name, $post_type_names, true );

			if ( ! $pod_id ) {
				// Post type not a pod
				continue;
			}

			$pod = $post_types[ $pod_id ];

			// REST API
			$rest_enabled = (boolean) pods_v( 'rest_enable', $pod['options'], false );

			if ( $rest_enabled ) {
				if ( empty( $wp_post_types[ $post_type_name ]->show_in_rest ) ) {
					$rest_base = sanitize_title( pods_v( 'rest_base', $pod['options'], pods_v( 'rest_base', $wp_post_types[ $post_type_name ] ), true ) );

					$wp_post_types[ $post_type_name ]->show_in_rest          = true;
					$wp_post_types[ $post_type_name ]->rest_base             = $rest_base;
					$wp_post_types[ $post_type_name ]->rest_controller_class = 'WP_REST_Posts_Controller';
				}

				new PodsRESTFields( $post_type_name );
			}
		}//end foreach

		foreach ( $existing_taxonomies as $taxonomy_name => $taxonomy_name_again ) {
			if ( isset( self::$content_types_registered['taxonomies'] ) && in_array( $taxonomy_name, self::$content_types_registered['taxonomies'], true ) ) {
				// Taxonomy already registered / setup by Pods
				continue;
			}

			$pod_id = array_search( $taxonomy_name, $taxonomy_names, true );

			if ( ! $pod_id ) {
				// Taxonomy not a pod
				continue;
			}

			$pod = $taxonomies[ $pod_id ];

			// REST API
			$rest_enabled = (boolean) pods_v( 'rest_enable', $pod['options'], false );

			if ( $rest_enabled ) {
				if ( empty( $wp_taxonomies[ $taxonomy_name ]->show_in_rest ) ) {
					$rest_base = sanitize_title( pods_v( 'rest_base', $pod['options'], pods_v( 'rest_base', $wp_taxonomies[ $taxonomy_name ] ), true ) );

					$wp_taxonomies[ $taxonomy_name ]->show_in_rest          = true;
					$wp_taxonomies[ $taxonomy_name ]->rest_base             = $rest_base;
					$wp_taxonomies[ $taxonomy_name ]->rest_controller_class = 'WP_REST_Terms_Controller';
				}

				new PodsRESTFields( $taxonomy_name );
			}
		}//end foreach

		if ( ! empty( PodsMeta::$user ) ) {
			$pod = current( PodsMeta::$user );

			$rest_enabled = (boolean) pods_v( 'rest_enable', $pod['options'], false );

			if ( $rest_enabled ) {
				new PodsRESTFields( $pod['name'] );
			}
		}

		if ( ! empty( PodsMeta::$media ) ) {
			$pod = current( PodsMeta::$media );

			$rest_enabled = (boolean) pods_v( 'rest_enable', $pod['options'], false );

			if ( $rest_enabled ) {
				new PodsRESTFields( $pod['name'] );
			}
		}

	}

	/**
	 * Check if we need to flush WordPress rewrite rules
	 * This gets run during 'init' action late in the game to give other plugins time to register their rewrite rules
	 */
	public function flush_rewrite_rules() {

		// Only run $wp_rewrite->flush_rules() in an admin context.
		if ( ! is_admin() ) {
			return;
		}

		$flush = (int) pods_transient_get( 'pods_flush_rewrites' );

		if ( 1 === $flush ) {
			/**
			 * @var $wp_rewrite WP_Rewrite
			 */
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
	public function setup_updated_messages( $messages ) {

		global $post, $post_ID;

		$post_types          = PodsMeta::$post_types;
		$existing_post_types = get_post_types();

		$pods_cpt_ct = pods_transient_get( 'pods_wp_cpt_ct' );

		if ( empty( $pods_cpt_ct ) || empty( $post_types ) ) {
			return $messages;
		}

		/**
		 * Use get_preview_post_link function added in 4.4, which eventually applies preview_post_link filter
		 * Before 4.4, this filter is defined in wp-admin/includes/meta-boxes.php, $post parameter added in 4.0
		 * there wasn't post parameter back in 3.8
		 * Let's add $post in the filter as it won't hurt anyway.
		 *
		 * @since 2.6.8.1
		 */
		$preview_post_link = function_exists( 'get_preview_post_link' ) ? get_preview_post_link( $post ) : apply_filters( 'preview_post_link', add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ), $post );

		foreach ( $post_types as $post_type ) {
			if ( ! isset( $pods_cpt_ct['post_types'][ $post_type['name'] ] ) ) {
				continue;
			}

			$labels = self::object_label_fix( $pods_cpt_ct['post_types'][ $post_type['name'] ], 'post_type' );
			$labels = $labels['labels'];

			$messages[ $post_type['name'] ] = array(
				1  => sprintf( __( '%1$s updated. <a href="%2$s">%3$s</a>', 'pods' ), $labels['singular_name'], esc_url( get_permalink( $post_ID ) ), $labels['view_item'] ),
				2  => __( 'Custom field updated.', 'pods' ),
				3  => __( 'Custom field deleted.', 'pods' ),
				4  => sprintf( __( '%s updated.', 'pods' ), $labels['singular_name'] ),
				/* translators: %s: date and time of the revision */
				5  => isset( $_GET['revision'] ) ? sprintf( __( '%1$s restored to revision from %2$s', 'pods' ), $labels['singular_name'], wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
				6  => sprintf( __( '%1$s published. <a href="%2$s">%3$s</a>', 'pods' ), $labels['singular_name'], esc_url( get_permalink( $post_ID ) ), $labels['view_item'] ),
				7  => sprintf( __( '%s saved.', 'pods' ), $labels['singular_name'] ),
				8  => sprintf( __( '%1$s submitted. <a target="_blank" rel="noopener noreferrer" href="%2$s">Preview %3$s</a>', 'pods' ), $labels['singular_name'], esc_url( $preview_post_link ), $labels['singular_name'] ),
				9  => sprintf(
					__( '%1$s scheduled for: <strong>%2$s</strong>. <a target="_blank" rel="noopener noreferrer" href="%3$s">Preview %4$s</a>', 'pods' ), $labels['singular_name'],
					// translators: Publish box date format, see http://php.net/date
					date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ), $labels['singular_name']
				),
				10 => sprintf( __( '%1$s draft updated. <a target="_blank" rel="noopener noreferrer" href="%2$s">Preview %3$s</a>', 'pods' ), $labels['singular_name'], esc_url( $preview_post_link ), $labels['singular_name'] ),
			);

			if ( false === (boolean) $pods_cpt_ct['post_types'][ $post_type['name'] ]['public'] ) {
				$messages[ $post_type['name'] ][1] = sprintf( __( '%s updated.', 'pods' ), $labels['singular_name'] );
				$messages[ $post_type['name'] ][6] = sprintf( __( '%s published.', 'pods' ), $labels['singular_name'] );
				$messages[ $post_type['name'] ][8] = sprintf( __( '%s submitted.', 'pods' ), $labels['singular_name'] );
				$messages[ $post_type['name'] ][9] = sprintf(
					__( '%1$s scheduled for: <strong>%2$s</strong>.', 'pods' ), $labels['singular_name'],
					// translators: Publish box date format, see http://php.net/date
					date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) )
				);
				$messages[ $post_type['name'] ][10] = sprintf( __( '%s draft updated.', 'pods' ), $labels['singular_name'] );
			}
		}//end foreach

		return $messages;
	}

	/**
	 * @param        $args
	 * @param string $type
	 *
	 * @return array
	 */
	public static function object_label_fix( $args, $type = 'post_type' ) {

		if ( empty( $args ) || ! is_array( $args ) ) {
			$args = array();
		}

		if ( ! isset( $args['labels'] ) || ! is_array( $args['labels'] ) ) {
			$args['labels'] = array();
		}

		$label          = pods_v( 'name', $args['labels'], pods_v( 'label', $args, __( 'Items', 'pods' ), true ), true );
		$singular_label = pods_v( 'singular_name', $args['labels'], pods_v( 'label_singular', $args, __( 'Item', 'pods' ), true ), true );

		$labels = $args['labels'];

		$labels['name']          = $label;
		$labels['singular_name'] = $singular_label;

		if ( 'post_type' === $type ) {
			$labels['menu_name']                = pods_v( 'menu_name', $labels, $label, true );
			$labels['name_admin_bar']           = pods_v( 'name_admin_bar', $labels, $singular_label, true );
			$labels['add_new']                  = pods_v( 'add_new', $labels, __( 'Add New', 'pods' ), true );
			$labels['add_new_item']             = pods_v( 'add_new_item', $labels, sprintf( __( 'Add New %s', 'pods' ), $singular_label ), true );
			$labels['new_item']                 = pods_v( 'new_item', $labels, sprintf( __( 'New %s', 'pods' ), $singular_label ), true );
			$labels['edit']                     = pods_v( 'edit', $labels, __( 'Edit', 'pods' ), true );
			$labels['edit_item']                = pods_v( 'edit_item', $labels, sprintf( __( 'Edit %s', 'pods' ), $singular_label ), true );
			$labels['view']                     = pods_v( 'view', $labels, sprintf( __( 'View %s', 'pods' ), $singular_label ), true );
			$labels['view_item']                = pods_v( 'view_item', $labels, sprintf( __( 'View %s', 'pods' ), $singular_label ), true );
			$labels['view_items']               = pods_v( 'view_items', $labels, sprintf( __( 'View %s', 'pods' ), $label ), true );
			$labels['all_items']                = pods_v( 'all_items', $labels, sprintf( __( 'All %s', 'pods' ), $label ), true );
			$labels['search_items']             = pods_v( 'search_items', $labels, sprintf( __( 'Search %s', 'pods' ), $label ), true );
			$labels['not_found']                = pods_v( 'not_found', $labels, sprintf( __( 'No %s Found', 'pods' ), $label ), true );
			$labels['not_found_in_trash']       = pods_v( 'not_found_in_trash', $labels, sprintf( __( 'No %s Found in Trash', 'pods' ), $label ), true );
			$labels['parent']                   = pods_v( 'parent', $labels, sprintf( __( 'Parent %s', 'pods' ), $singular_label ), true );
			$labels['parent_item_colon']        = pods_v( 'parent_item_colon', $labels, sprintf( __( 'Parent %s:', 'pods' ), $singular_label ), true );
			$labels['featured_image']           = pods_v( 'featured_image', $labels, __( 'Featured Image', 'pods' ), true );
			$labels['set_featured_image']       = pods_v( 'set_featured_image', $labels, __( 'Set featured image', 'pods' ), true );
			$labels['remove_featured_image']    = pods_v( 'remove_featured_image', $labels, __( 'Remove featured image', 'pods' ), true );
			$labels['use_featured_image']       = pods_v( 'use_featured_image', $labels, __( 'Use as featured image', 'pods' ), true );
			$labels['archives']                 = pods_v( 'archives', $labels, sprintf( __( '%s Archives', 'pods' ), $singular_label ), true );
			$labels['attributes']               = pods_v( 'attributes', $labels, sprintf( __( '%s Attributes', 'pods' ), $singular_label ), true );
			$labels['insert_into_item']         = pods_v( 'insert_into_item', $labels, sprintf( __( 'Insert into %s', 'pods' ), $singular_label ), true );
			$labels['uploaded_to_this_item']    = pods_v( 'uploaded_to_this_item', $labels, sprintf( __( 'Uploaded to this %s', 'pods' ), $singular_label ), true );
			$labels['filter_items_list']        = pods_v( 'filter_items_list', $labels, sprintf( __( 'Filter %s lists', 'pods' ), $label ), true );
			$labels['items_list_navigation']    = pods_v( 'items_list_navigation', $labels, sprintf( __( '%s navigation', 'pods' ), $label ), true );
			$labels['items_list']               = pods_v( 'items_list', $labels, sprintf( __( '%s list', 'pods' ), $label ), true );
			$labels['item_published']           = pods_v( 'item_published', $labels, sprintf( __( '%s published', 'pods' ), $singular_label ), true );
			$labels['item_published_privately'] = pods_v( 'item_published_privately', $labels, sprintf( __( '%s published privately', 'pods' ), $singular_label ), true );
			$labels['item_reverted_to_draft']   = pods_v( 'item_reverted_to_draft', $labels, sprintf( __( '%s reverted to draft', 'pods'), $singular_label ), true );
			$labels['item_scheduled']           = pods_v( 'item_scheduled', $labels, sprintf( __( '%s scheduled', 'pods' ), $singular_label ), true );
			$labels['item_updated']             = pods_v( 'item_updated', $labels, sprintf( __( '%s updated', 'pods' ), $singular_label ), true );
		} elseif ( 'taxonomy' === $type ) {
			$labels['menu_name']                  = pods_v( 'menu_name', $labels, $label, true );
			$labels['search_items']               = pods_v( 'search_items', $labels, sprintf( __( 'Search %s', 'pods' ), $label ), true );
			$labels['popular_items']              = pods_v( 'popular_items', $labels, sprintf( __( 'Popular %s', 'pods' ), $label ), true );
			$labels['all_items']                  = pods_v( 'all_items', $labels, sprintf( __( 'All %s', 'pods' ), $label ), true );
			$labels['parent_item']                = pods_v( 'parent_item', $labels, sprintf( __( 'Parent %s', 'pods' ), $singular_label ), true );
			$labels['parent_item_colon']          = pods_v( 'parent_item_colon', $labels, sprintf( __( 'Parent %s :', 'pods' ), $singular_label ), true );
			$labels['edit_item']                  = pods_v( 'edit_item', $labels, sprintf( __( 'Edit %s', 'pods' ), $singular_label ), true );
			$labels['view_item']                  = pods_v( 'view_item', $labels, sprintf( __( 'View %s', 'pods' ), $singular_label ), true );
			$labels['update_item']                = pods_v( 'update_item', $labels, sprintf( __( 'Update %s', 'pods' ), $singular_label ), true );
			$labels['add_new_item']               = pods_v( 'add_new_item', $labels, sprintf( __( 'Add New %s', 'pods' ), $singular_label ), true );
			$labels['new_item_name']              = pods_v( 'new_item_name', $labels, sprintf( __( 'New %s Name', 'pods' ), $singular_label ), true );
			$labels['separate_items_with_commas'] = pods_v( 'separate_items_with_commas', $labels, sprintf( __( 'Separate %s with commas', 'pods' ), $label ), true );
			$labels['add_or_remove_items']        = pods_v( 'add_or_remove_items', $labels, sprintf( __( 'Add or remove %s', 'pods' ), $label ), true );
			$labels['choose_from_most_used']      = pods_v( 'choose_from_most_used', $labels, sprintf( __( 'Choose from the most used %s', 'pods' ), $label ), true );
			$labels['not_found']                  = pods_v( 'not_found', $labels, sprintf( __( 'No %s found.', 'pods' ), $label ), true );
			$labels['no_terms']                   = pods_v( 'no_terms', $labels, sprintf( __( 'No %s', 'pods' ), $label ), true );
			$labels['items_list_navigation']      = pods_v( 'items_list_navigation', $labels, sprintf( __( '%s navigation', 'pods' ), $label ), true );
			$labels['items_list']                 = pods_v( 'items_list', $labels, sprintf( __( '%s list', 'pods' ), $label ), true );
		}//end if

		$args['labels'] = $labels;

		return $args;
	}

	/**
	 * Activate and Install
	 */
	public function activate_install() {

		register_activation_hook( PODS_DIR . 'init.php', array( $this, 'activate' ) );
		register_deactivation_hook( PODS_DIR . 'init.php', array( $this, 'deactivate' ) );

		// WP 5.1+.
		add_action( 'wp_insert_site', array( $this, 'new_blog' ) );
		// WP < 5.1. (Gets automaticaly removed if `wp_insert_site` is called.
		add_action( 'wpmu_new_blog', array( $this, 'new_blog' ) );

		if ( empty( self::$version ) || version_compare( self::$version, PODS_VERSION, '<' ) || version_compare( self::$version, PODS_DB_VERSION, '<=' ) || self::$upgrade_needed ) {
			$this->setup();
		} elseif ( self::$version !== PODS_VERSION ) {
			delete_option( 'pods_framework_version' );
			add_option( 'pods_framework_version', PODS_VERSION, '', 'yes' );

			self::$version = PODS_VERSION;

			pods_api()->cache_flush_pods();
		}

	}

	/**
	 *
	 */
	public function activate() {

		global $wpdb;

		if ( is_multisite() && 1 === (int) pods_v( 'networkwide' ) ) {
			$_blog_ids = $wpdb->get_col( "SELECT `blog_id` FROM `{$wpdb->blogs}`" );

			foreach ( $_blog_ids as $_blog_id ) {
				$this->setup( $_blog_id );
			}
		} else {
			$this->setup();
		}
	}

	/**
	 *
	 */
	public function deactivate() {

		delete_option( 'pods_callouts' );

		pods_api()->cache_flush_pods();

	}

	/**
	 * @param null $current
	 * @param null $last
	 *
	 * @return bool
	 */
	public function needs_upgrade( $current = null, $last = null ) {

		if ( null === $current ) {
			$current = self::$version;
		}

		if ( null === $last ) {
			$last = self::$version_last;
		}

		$upgrade_needed = false;

		if ( ! empty( $current ) ) {
			foreach ( self::$upgrades as $old_version => $new_version ) {
				/*
				if ( '2.1.0' === $new_version && ( is_developer() ) )
					continue;*/

				if ( version_compare( $last, $old_version, '>=' ) && version_compare( $last, $new_version, '<' ) && version_compare( $current, $new_version, '>=' ) && 1 !== self::$upgraded ) {
					$upgrade_needed = true;

					break;
				}
			}
		}

		return $upgrade_needed;
	}

	/**
	 * @todo  Remove `wpmu_new_blog` once support for WP < 5.1 gets dropped.
	 * @param WP_Site|int $_blog_id
	 */
	public function new_blog( $_blog_id ) {
		// WP 5.1+.
		if ( doing_action( 'wp_insert_site' ) ) {
			remove_action( 'wpmu_new_blog', array( $this, 'new_blog' ) );
		}

		if ( class_exists( 'WP_Site' ) && $_blog_id instanceof WP_Site ) {
			$_blog_id = $_blog_id->id;
		}

		if ( is_multisite() && is_plugin_active_for_network( basename( PODS_DIR ) . '/init.php' ) ) {
			$this->setup( $_blog_id );
		}
	}

	/**
	 * @param null $_blog_id
	 */
	public function setup( $_blog_id = null ) {

		global $wpdb;

		// Switch DB table prefixes
		if ( null !== $_blog_id && $_blog_id !== $wpdb->blogid ) {
			switch_to_blog( pods_absint( $_blog_id ) );
		} else {
			$_blog_id = null;
		}

		// Setup DB tables
		$pods_version      = get_option( 'pods_framework_version' );
		$pods_version_last = get_option( 'pods_framework_version_last' );

		if ( empty( $pods_version ) ) {
			// Install Pods
			pods_upgrade()->install( $_blog_id );

			$old_version = get_option( 'pods_version' );

			if ( ! empty( $old_version ) ) {
				if ( false === strpos( $old_version, '.' ) ) {
					$old_version = pods_version_to_point( $old_version );
				}

				delete_option( 'pods_framework_version_last' );
				add_option( 'pods_framework_version_last', $pods_version, '', 'yes' );

				self::$version_last = $old_version;
			}
		} elseif ( $this->needs_upgrade( $pods_version, $pods_version_last ) ) {
			// Upgrade Wizard needed
			// Do not do anything
			return;
		} elseif ( version_compare( $pods_version, PODS_VERSION, '<=' ) ) {
			// Update Pods and run any required DB updates
			if ( false !== apply_filters( 'pods_update_run', null, PODS_VERSION, $pods_version, $_blog_id ) && ! isset( $_GET['pods_bypass_update'] ) ) {
				do_action( 'pods_update', PODS_VERSION, $pods_version, $_blog_id );

				// Update 2.0 alpha / beta sites
				if ( version_compare( '2.0.0-a-1', $pods_version, '<=' ) && version_compare( $pods_version, '2.0.0-b-15', '<=' ) ) {
					include PODS_DIR . 'sql/update-2.0-beta.php';
				}

				if ( version_compare( $pods_version, PODS_DB_VERSION, '<=' ) ) {
					include PODS_DIR . 'sql/update.php';
				}

				do_action( 'pods_update_post', PODS_VERSION, $pods_version, $_blog_id );
			}

			delete_option( 'pods_framework_version_last' );
			add_option( 'pods_framework_version_last', $pods_version, '', 'yes' );

			self::$version_last = $pods_version;
		}//end if

		delete_option( 'pods_framework_version' );
		add_option( 'pods_framework_version', PODS_VERSION, '', 'yes' );

		delete_option( 'pods_framework_db_version' );
		add_option( 'pods_framework_db_version', PODS_DB_VERSION, '', 'yes' );

		self::$version    = PODS_VERSION;
		self::$db_version = PODS_DB_VERSION;

		pods_api()->cache_flush_pods();

		// Restore DB table prefix (if switched)
		if ( null !== $_blog_id ) {
			restore_current_blog();
		}

	}

	/**
	 * @param null $_blog_id
	 */
	public function reset( $_blog_id = null ) {

		global $wpdb;

		// Switch DB table prefixes
		if ( null !== $_blog_id && $_blog_id !== $wpdb->blogid ) {
			switch_to_blog( pods_absint( $_blog_id ) );
		} else {
			$_blog_id = null;
		}

		$api = pods_api();

		$pods = $api->load_pods(
			array(
				'names_ids'  => true,
				'table_info' => false,
			)
		);

		foreach ( $pods as $pod_id => $pod_label ) {
			$api->delete_pod( array( 'id' => $pod_id ) );
		}

		$templates = $api->load_templates();

		foreach ( $templates as $template ) {
			$api->delete_template( array( 'id' => $template['id'] ) );
		}

		$pages = $api->load_pages();

		foreach ( $pages as $page ) {
			$api->delete_page( array( 'id' => $page['id'] ) );
		}

		$helpers = $api->load_helpers();

		foreach ( $helpers as $helper ) {
			$api->delete_helper( array( 'id' => $helper['id'] ) );
		}

		$tables = $wpdb->get_results( "SHOW TABLES LIKE '{$wpdb->prefix}pods%'", ARRAY_N );

		if ( ! empty( $tables ) ) {
			foreach ( $tables as $table ) {
				$table = $table[0];

				pods_query( "DROP TABLE `{$table}`", false );
			}
		}

		// Remove any orphans
		$wpdb->query(
			"
                DELETE `p`, `pm`
                FROM `{$wpdb->posts}` AS `p`
                LEFT JOIN `{$wpdb->postmeta}` AS `pm`
                    ON `pm`.`post_id` = `p`.`ID`
                WHERE
                    `p`.`post_type` LIKE '_pods_%'
            "
		);

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
		if ( null !== $_blog_id ) {
			restore_current_blog();
		}
	}

	public function run() {

		static $ran;

		if ( ! empty( $ran ) ) {
			return;
		}

		$ran = true;

		$this->load_i18n();

		if ( ! did_action( 'plugins_loaded' ) ) {
			add_action( 'plugins_loaded', array( $this, 'load_components' ), 11 );
		} else {
			$this->load_components();
		}

		if ( ! did_action( 'setup_theme' ) ) {
			add_action( 'setup_theme', array( $this, 'load_meta' ), 14 );
		} else {
			$this->load_meta();
		}

		if ( ! did_action( 'init' ) ) {
			add_action( 'init', array( $this, 'core' ), 11 );
			add_action( 'init', array( $this, 'setup_content_types' ), 11 );

			if ( is_admin() ) {
				add_action( 'init', array( $this, 'admin_init' ), 12 );
			}
		} else {
			$this->core();
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

		// Compatibility with WP 5.4 privacy export.
		add_filter( 'wp_privacy_additional_user_profile_data', array( $this, 'filter_wp_privacy_additional_user_profile_data' ), 10, 3 );

		// Compatibility for Query Monitor conditionals
		add_filter( 'query_monitor_conditionals', array( $this, 'filter_query_monitor_conditionals' ) );

		// Add WP-CLI commands
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			require_once PODS_DIR . 'classes/cli/Pods_CLI_Command.php';
			require_once PODS_DIR . 'classes/cli/PodsAPI_CLI_Command.php';
		}

	}

	/**
	 * Delete Attachments from relationships
	 *
	 * @param int $_ID
	 */
	public function delete_attachment( $_ID ) {

		global $wpdb;

		$_ID = (int) $_ID;

		do_action( 'pods_delete_attachment', $_ID );

		$file_types = "'" . implode( "', '", PodsForm::file_field_types() ) . "'";

		if ( ! pods_tableless() ) {
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
		if ( ! empty( PodsMeta::$post_types ) ) {
			$sql = "
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
		if ( ! empty( PodsMeta::$user ) ) {
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
		if ( ! empty( PodsMeta::$comment ) ) {
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
	public function register_widgets() {

		$widgets = array(
			'PodsWidgetSingle',
			'PodsWidgetList',
			'PodsWidgetField',
			'PodsWidgetForm',
			'PodsWidgetView',
		);

		foreach ( $widgets as $widget ) {
			if ( ! file_exists( PODS_DIR . 'classes/widgets/' . $widget . '.php' ) ) {
				continue;
			}

			require_once PODS_DIR . 'classes/widgets/' . $widget . '.php';

			register_widget( $widget );
		}
	}

	/**
	 * Add Admin Bar links
	 */
	public function admin_bar_links() {

		global $wp_admin_bar, $pods;

		if ( ! is_user_logged_in() || ! is_admin_bar_showing() ) {
			return;
		}

		$all_pods = pods_api()->load_pods(
			array(
				'type'       => 'pod',
				'fields'     => false,
				'table_info' => false,
			)
		);

		// Add New item links for all pods
		foreach ( $all_pods as $pod ) {
			if ( 0 === (int) $pod['options']['show_in_menu'] ) {
				continue;
			}

			if ( ! pods_is_admin( array( 'pods', 'pods_content', 'pods_add_' . $pod['name'] ) ) ) {
				continue;
			}

			$singular_label = pods_v( 'label_singular', $pod['options'], pods_v( 'label', $pod, ucwords( str_replace( '_', ' ', $pod['name'] ) ), true ), true );

			$wp_admin_bar->add_node(
				array(
					'id'     => 'new-pod-' . $pod['name'],
					'title'  => $singular_label,
					'parent' => 'new-content',
					'href'   => admin_url( 'admin.php?page=pods-manage-' . $pod['name'] . '&action=add' ),
				)
			);
		}

		// Add edit link if we're on a pods page
		if ( is_object( $pods ) && ! is_wp_error( $pods ) && ! empty( $pods->id ) && isset( $pods->pod_data ) && ! empty( $pods->pod_data ) && 'pod' === $pods->pod_data['type'] ) {
			$pod = $pods->pod_data;

			if ( pods_is_admin( array( 'pods', 'pods_content', 'pods_edit_' . $pod['name'] ) ) ) {
				$singular_label = pods_v( 'label_singular', $pod['options'], pods_v( 'label', $pod, ucwords( str_replace( '_', ' ', $pod['name'] ) ), true ), true );

				$wp_admin_bar->add_node(
					array(
						'title' => sprintf( __( 'Edit %s', 'pods' ), $singular_label ),
						'id'    => 'edit-pod',
						'href'  => admin_url( 'admin.php?page=pods-manage-' . $pod['name'] . '&action=edit&id=' . $pods->id() ),
					)
				);
			}
		}

	}

	/**
	 * Add Pod fields to user export.
	 * Requires WordPress 5.4+
	 *
	 * @since 2.7.17
	 *
	 * @param array   $additional_user_profile_data {
	 *     An array of name-value pairs of additional user data items.  Default: the empty array.
	 *
	 *     @type string $name  The user-facing name of an item name-value pair, e.g. 'IP Address'.
	 *     @type string $value The user-facing value of an item data pair, e.g. '50.60.70.0'.
	 * }
	 * @param WP_User $user           The user whose data is being exported.
	 * @param array   $reserved_names An array of reserved names.  Any item in
	 *                                 `$additional_user_data` that uses one of these
	 *                                 for it's `name` will not be included in the export.
	 *
	 * @return array
	 */
	public function filter_wp_privacy_additional_user_profile_data( $additional_user_profile_data, $user, $reserved_names ) {
		$pod = pods( 'user', $user->ID );

		if ( ! $pod->valid() ) {
			return $additional_user_profile_data;
		}

		foreach ( $pod->fields as $name => $field ) {
			$additional_user_profile_data[] = array(
				'name'  => apply_filters( 'pods_form_ui_label_text', $field['label'], $name, '', $field ),
				'value' => $pod->display( $name ),
			);
		}

		return $additional_user_profile_data;
	}

	/**
	 * Add Pods conditional functions to Query Monitor.
	 *
	 * @param  array $conditionals
	 * @return array
	 */
	public function filter_query_monitor_conditionals( $conditionals ) {
		$conditionals[] = 'pods_developer';
		$conditionals[] = 'pods_tableless';
		$conditionals[] = 'pods_light';
		$conditionals[] = 'pods_strict';
		$conditionals[] = 'pods_allow_deprecated';
		$conditionals[] = 'pods_api_cache';
		$conditionals[] = 'pods_shortcode_allow_evaluate_tags';
		return $conditionals;
	}
}
