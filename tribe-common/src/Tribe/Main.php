<?php
/**
 * Main Tribe Common class.
 */

// Don't load directly
use Tribe\DB_Lock;

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( class_exists( 'Tribe__Main' ) ) {
	return;
}

class Tribe__Main {
	const EVENTSERROROPT      = '_tribe_events_errors';
	const OPTIONNAME          = 'tribe_events_calendar_options';
	const OPTIONNAMENETWORK   = 'tribe_events_calendar_network_options';

	const VERSION             = '4.13.0.1';

	const FEED_URL            = 'https://theeventscalendar.com/feed/';

	protected $plugin_context;
	protected $plugin_context_class;

	public static $tribe_url = 'http://tri.be/';
	public static $tec_url = 'https://theeventscalendar.com/';

	public $plugin_dir;
	public $plugin_path;
	public $plugin_url;

	/**
	 * Static Singleton Holder
	 * @var self
	 */
	protected static $instance;

	/**
	 * Get (and instantiate, if necessary) the instance of the class
	 *
	 * @param  mixed $context An instance of the Main class of the plugin that instantiated Common
	 *
	 * @return self
	 */
	public static function instance( $context = null ) {
		if ( ! self::$instance ) {
			self::$instance = new self( $context );
		}

		return self::$instance;
	}

	/**
	 * Constructor for Common Class
	 *
	 * We are using a `public` constructor here for backwards compatibility.
	 *
	 * The way our code used to work we would have `new Tribe__Main()` called directly
	 * which causes fatals if you have an older version of Core/Tickets active along side a new one
	 */
	public function __construct( $context = null ) {
		if ( self::$instance ) {
			return;
		}

		require_once realpath( dirname( dirname( dirname( __FILE__ ) ) ) . '/vendor/autoload.php' );

		// the DI container class
		require_once dirname( __FILE__ ) . '/Container.php';

		if ( is_object( $context ) ) {
			$this->plugin_context = $context;
			$this->plugin_context_class = get_class( $context );
		}

		$this->plugin_path = trailingslashit( dirname( dirname( dirname( __FILE__ ) ) ) );
		$this->plugin_dir  = trailingslashit( basename( $this->plugin_path ) );
		$parent_plugin_dir = trailingslashit( plugin_basename( $this->plugin_path ) );
		$this->plugin_url  = plugins_url( $parent_plugin_dir === $this->plugin_dir ? $this->plugin_dir : $parent_plugin_dir );

		$this->promoter_connector();

		add_action( 'plugins_loaded', [ $this, 'plugins_loaded' ], 1 );
		add_action( 'tribe_common_loaded', [ $this, 'tribe_common_app_store' ], 10 );
	}

	/**
	 *
	 */
	public function plugins_loaded() {

		$this->init_autoloading();

		$this->bind_implementations();
		$this->init_libraries();
		$this->add_hooks();

		/**
		 * Runs once all common libs are loaded and initial hooks are in place.
		 *
		 * @since 4.3
		 */
		do_action( 'tribe_common_loaded' );

		/**
		 * Runs to register loaded plugins
		 *
		 * @since 4.9
		 */
		do_action( 'tribe_plugins_loaded ' );
	}

	/**
	 * Setup the autoloader for common files
	 */
	protected function init_autoloading() {
		if ( ! class_exists( 'Tribe__Autoloader' ) ) {
			require_once dirname( __FILE__ ) . '/Autoloader.php';
		}

		$autoloader = Tribe__Autoloader::instance();

		$prefixes = [ 'Tribe__' => dirname( __FILE__ ) ];
		$autoloader->register_prefixes( $prefixes );

		foreach ( glob( $this->plugin_path . 'src/deprecated/*.php' ) as $file ) {
			$class_name = str_replace( '.php', '', basename( $file ) );
			$autoloader->register_class( $class_name, $file );
		}

		$autoloader->register_autoloader();
	}

	public function tribe_common_app_store() {
		Tribe__Extension_Loader::instance();
	}

	/**
	 * Gets the instantiated context of this class. I.e. the object that instantiated this one.
	 */
	public function context() {
		return $this->plugin_context;
	}

	/**
	 * Gets the class name of the instantiated plugin context of this class. I.e. the class name of the object that instantiated this one.
	 */
	public function context_class() {
		return $this->plugin_context_class;
	}

	/**
	 * initializes all required libraries
	 */
	public function init_libraries() {
		require_once $this->plugin_path . 'src/functions/utils.php';
		require_once $this->plugin_path . 'src/functions/url.php';
		require_once $this->plugin_path . 'src/functions/query.php';
		require_once $this->plugin_path . 'src/functions/multibyte.php';
		require_once $this->plugin_path . 'src/functions/template-tags/general.php';
		require_once $this->plugin_path . 'src/functions/template-tags/date.php';
		require_once $this->plugin_path . 'src/functions/template-tags/html.php';
		require_once $this->plugin_path . 'src/functions/template-tags/post.php';

		Tribe__Debug::instance();
		tribe( 'assets' );
		tribe( 'assets.pipeline' );
		tribe( 'settings.manager' );
		tribe( 'tracker' );
		tribe( 'plugins.api' );
		tribe( 'pue.notices' );
		tribe( 'ajax.dropdown' );
		tribe( 'logger' );
	}

	/**
	 * Registers resources that can/should be enqueued
	 */
	public function load_assets() {
		// These ones are only registered
		tribe_assets(
			$this,
			[
				[ 'tribe-accessibility-css', 'accessibility.css' ],
				[ 'tribe-query-string', 'utils/query-string.js' ],
				[ 'tribe-clipboard', 'vendor/clipboard/clipboard.js' ],
				[ 'datatables', 'vendor/datatables/datatables.js', [ 'jquery' ] ],
				[ 'tribe-select2', 'vendor/tribe-selectWoo/dist/js/selectWoo.full.js', [ 'jquery' ] ],
				[ 'tribe-select2-css', 'vendor/tribe-selectWoo/dist/css/selectWoo.css' ],
				[ 'tribe-utils-camelcase', 'utils-camelcase.js', [ 'underscore' ] ],
				[ 'tribe-moment', 'vendor/momentjs/moment.js' ],
				[ 'tribe-tooltipster', 'vendor/tooltipster/tooltipster.bundle.js', [ 'jquery' ] ],
				[ 'tribe-tooltipster-css', 'vendor/tooltipster/tooltipster.bundle.css' ],
				[ 'datatables-css', 'datatables.css' ],
				[ 'tribe-datatables', 'tribe-datatables.js', [ 'datatables' ] ],
				[ 'tribe-bumpdown', 'bumpdown.js', [ 'jquery', 'underscore', 'hoverIntent' ] ],
				[ 'tribe-bumpdown-css', 'bumpdown.css' ],
				[ 'tribe-buttonset-style', 'buttonset.css' ],
				[ 'tribe-dropdowns', 'dropdowns.js', [ 'jquery', 'underscore', 'tribe-select2', 'tribe-common' ] ],
				[ 'tribe-jquery-timepicker', 'vendor/jquery-tribe-timepicker/jquery.timepicker.js', [ 'jquery' ] ],
				[ 'tribe-jquery-timepicker-css', 'vendor/jquery-tribe-timepicker/jquery.timepicker.css' ],
				[ 'tribe-timepicker', 'timepicker.js', [ 'jquery', 'tribe-jquery-timepicker' ] ],
				[ 'tribe-attrchange', 'vendor/attrchange/js/attrchange.js' ],
			]
		);

		tribe_assets(
			$this,
			[
				[ 'tribe-common-skeleton-style', 'common-skeleton.css' ],
				[ 'tribe-common-full-style', 'common-full.css', [ 'tribe-common-skeleton-style' ] ],
			],
			null
		);

		// These ones will be enqueued on `admin_enqueue_scripts` if the conditional method on filter is met
		tribe_assets(
			$this,
			[
				[ 'tribe-ui', 'tribe-ui.css' ],
				[ 'tribe-buttonset', 'buttonset.js', [ 'jquery', 'underscore' ] ],
				[ 'tribe-common-admin', 'tribe-common-admin.css', [ 'tribe-dependency-style', 'tribe-bumpdown-css', 'tribe-buttonset-style', 'tribe-select2-css' ] ],
				[ 'tribe-validation', 'validation.js', [ 'jquery', 'underscore', 'tribe-common', 'tribe-utils-camelcase', 'tribe-tooltipster' ] ],
				[ 'tribe-validation-style', 'validation.css', [ 'tribe-tooltipster-css' ] ],
				[ 'tribe-dependency', 'dependency.js', [ 'jquery', 'underscore', 'tribe-common' ] ],
				[ 'tribe-dependency-style', 'dependency.css', [ 'tribe-select2-css' ] ],
				[ 'tribe-pue-notices', 'pue-notices.js', [ 'jquery' ] ],
				[ 'tribe-datepicker', 'datepicker.css' ],
			],
			'admin_enqueue_scripts',
			[
				'conditionals' => [ $this, 'should_load_common_admin_css' ],
				'priority' => 5,
			]
		);

		tribe_asset(
			$this,
			'tribe-common',
			'tribe-common.js',
			[],
			'admin_enqueue_scripts',
			[
				'priority' => 0,
			]
		);

		tribe_asset(
			$this,
			'tribe-admin-url-fragment-scroll',
			'admin/url-fragment-scroll.js',
			[ 'tribe-common' ],
			'admin_enqueue_scripts',
			[
				'conditionals' => [ $this, 'should_load_common_admin_css' ],
				'priority'     => 5,
			]
		);

		tribe( Tribe__Admin__Help_Page::class )->register_assets();
	}

	/**
	 * Load Common's text domain, then fire the hook for other plugins to do the same.
	 *
	 * Make sure this fires on 'init', per WordPress best practices.
	 *
	 * @since 4.12.0
	 *
	 * @return bool
	 */
	public function hook_load_text_domain() {
		$loaded = $this->load_text_domain(
			'tribe-common',
			basename( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) ) . '/common/lang/'
		);

		/**
		 * After attempting (hopefully successfully) to load Common's text domain.
		 *
		 * Load other plugin text domains on this hook, but make sure they're setup on this hook prior to 'init'.
		 *
		 * @since 4.12.0
		 *
		 * @param bool $loaded Whether or not Common's text domain was loaded.
		 *
		 * @return bool
		 */
		do_action( 'tribe_load_text_domains', $loaded );

		return $loaded;
	}

	/**
	 * Load All localization data create by `asset.data`
	 *
	 * @since  4.7
	 *
	 * @return void
	 */
	public function load_localize_data() {
		$datepicker_months = array_values( Tribe__Date_Utils::get_localized_months_full() );

		tribe( 'asset.data' )->add( 'tribe_l10n_datatables', [
			'aria' => [
				'sort_ascending' => __( ': activate to sort column ascending', 'tribe-common' ),
				'sort_descending' => __( ': activate to sort column descending', 'tribe-common' ),
			],
			'length_menu'       => __( 'Show _MENU_ entries', 'tribe-common' ),
			'empty_table'       => __( 'No data available in table', 'tribe-common' ),
			'info'              => __( 'Showing _START_ to _END_ of _TOTAL_ entries', 'tribe-common' ),
			'info_empty'        => __( 'Showing 0 to 0 of 0 entries', 'tribe-common' ),
			'info_filtered'     => __( '(filtered from _MAX_ total entries)', 'tribe-common' ),
			'zero_records'      => __( 'No matching records found', 'tribe-common' ),
			'search'            => __( 'Search:', 'tribe-common' ),
			'all_selected_text' => __( 'All items on this page were selected. ', 'tribe-common' ),
			'select_all_link'   => __( 'Select all pages', 'tribe-common' ),
			'clear_selection'   => __( 'Clear Selection.', 'tribe-common' ),
			'pagination' => [
				'all' => __( 'All', 'tribe-common' ),
				'next' => __( 'Next', 'tribe-common' ),
				'previous' => __( 'Previous', 'tribe-common' ),
			],
			'select' => [
				'rows' => [
					0 => '',
					'_' => __( ': Selected %d rows', 'tribe-common' ),
					1 => __( ': Selected 1 row', 'tribe-common' ),
				],
			],
			'datepicker' => [
				'dayNames'        => Tribe__Date_Utils::get_localized_weekdays_full(),
				'dayNamesShort'   => Tribe__Date_Utils::get_localized_weekdays_short(),
				'dayNamesMin'     => Tribe__Date_Utils::get_localized_weekdays_initial(),
				'monthNames'      => $datepicker_months,
				'monthNamesShort' => $datepicker_months, // We deliberately use full month names here,
				'monthNamesMin'   => array_values( Tribe__Date_Utils::get_localized_months_short() ),
 				'nextText'        => esc_html__( 'Next', 'the-events-calendar' ),
				'prevText'        => esc_html__( 'Prev', 'the-events-calendar' ),
				'currentText'     => esc_html__( 'Today', 'the-events-calendar' ),
				'closeText'       => esc_html__( 'Done', 'the-events-calendar' ),
				'today'           => esc_html__( 'Today', 'the-events-calendar' ),
				'clear'           => esc_html__( 'Clear', 'the-events-calendar' ),
			],
		] );
	}

	/**
	 * Adds core hooks
	 */
	public function add_hooks() {
		add_action( 'plugins_loaded', [ 'Tribe__App_Shop', 'instance' ] );
		add_action( 'plugins_loaded', [ $this, 'tribe_plugins_loaded' ], PHP_INT_MAX );

		// Register for the assets to be available everywhere
		add_action( 'tribe_common_loaded', [ $this, 'load_assets' ], 1 );
		add_action( 'init', [ $this, 'hook_load_text_domain' ] );
		add_action( 'init', [ $this, 'load_localize_data' ] );
		add_action( 'plugins_loaded', [ 'Tribe__Admin__Notices', 'instance' ], 1 );
		add_action( 'admin_enqueue_scripts', [ $this, 'store_admin_notices' ] );

		add_filter( 'body_class', [ $this, 'add_js_class' ] );
		add_action( 'wp_footer', [ $this, 'toggle_js_class' ] );
	}

	/**
	 * Adds `tribe-no-js` class to all pages when common is active.
	 *
	 * @since 4.3.4
	 *
	 * @param array|string $classes Previous classes on body.
	 *
	 * @return array       All classes that will be printed on `<body>`.
	 */
	public function add_js_class( $classes = [] ) {
		if ( ! is_array( $classes ) ) {
			$classes = explode( ' ', $classes );
		}

		$classes[] = 'tribe-no-js';

		return array_filter( array_unique( $classes ) );
	}

	/**
	 * Removes `tribe-no-js` and replaces with `tribe-js` when the Javascript of the page is enabled.
	 *
	 * @since 4.3.4
	 *
	 * @return void  This method only prints HTML to the screen no return.
	 */
	public function toggle_js_class() {
		?>
		<script>
		( function ( body ) {
			'use strict';
			body.className = body.className.replace( /\btribe-no-js\b/, 'tribe-js' );
		} )( document.body );
		</script>
		<?php
	}

	/**
	 * Tells us if we're on an admin screen that needs the Common admin CSS.
	 *
	 * Currently this includes post type screens, the Plugins page, Settings pages
	 * and tabs, Tribe App Shop page, and the Help screen.
	 *
	 * @since 4.5.7
	 *
	 * @return bool Whether we should load Common Admin CSS or not.
	 */
	public function should_load_common_admin_css() {
		$helper = Tribe__Admin__Helpers::instance();

		// Are we on a post type screen?
		$is_post_type = $helper->is_post_type_screen();

		// Are we on the Plugins page?
		$is_plugins = $helper->is_screen( 'plugins' );

		// Are we on the Widgets page?
		$is_widgets = $helper->is_screen( 'widgets' );

		// Are we viewing a generic Tribe screen?
		// Includes: Events > Settings, Events > Help, App Shop page, and more.
		$is_tribe_screen = $helper->is_screen();

		return $is_post_type || $is_plugins || $is_widgets || $is_tribe_screen;
	}

	/**
	 * A Helper method to load text domain
	 * First it tries to load the wp-content/languages translation then if falls to the try to load $dir language files.
	 *
	 * @since  4.0.1 Introduced.
	 * @since  4.2   Included $domain and $dir params.
	 *
	 * @param string       $domain The text domain that will be loaded.
	 * @param string|false $dir    What directory should be used to try to load if the default doesn't work.
	 *
	 * @return bool  If it was able to load the text domain.
	 */
	public function load_text_domain( $domain, $dir = false ) {
		// Added safety just in case this runs twice...
		if ( is_textdomain_loaded( $domain ) && ! $GLOBALS['l10n'][ $domain ] instanceof NOOP_Translations ) {
			return true;
		}

		$locale = get_locale();
		$plugin_rel_path = WP_LANG_DIR . '/plugins/';

		/**
		 * Allows users to filter the file location for a given text domain..
		 * Be careful when using this filter, it will apply across the whole plugin suite.
		 *
		 * @param string      $plugin_rel_path The relative path for the language files.
		 * @param string      $domain Which plugin domain we are trying to load.
		 * @param string      $locale Which Language we will load.
		 * @param string|bool $dir    If there was a custom directory passed on the method call.
		 */
		$plugin_rel_path = apply_filters( 'tribe_load_text_domain', $plugin_rel_path, $domain, $locale, $dir );

		$loaded = load_plugin_textdomain( $domain, false, $plugin_rel_path );

		if ( $dir !== false && ! $loaded ) {
			return load_plugin_textdomain( $domain, false, $dir );
		}

		return $loaded;
	}

	/**
	 * Returns the post types registered by Tribe plugins.
	 *
	 * @since 4.0.1 Introduced the method.
	 *
	 * @return array Slugs for all Post Types registered.
	 */
	public static function get_post_types() {
		/**
		 * We default the post type array to empty in tribe-common. Plugins like TEC add to it.
		 *
		 * @since 4.0.1
		 *
		 * @param array Slugs for all Post Types registered.
		 */
		return apply_filters( 'tribe_post_types', [] );
	}

	/**
	 * Insert an array after a specified key within another array.
	 *
	 * @param $key
	 * @param $source_array
	 * @param $insert_array
	 *
	 * @return array
	 */
	public static function array_insert_after_key( $key, $source_array, $insert_array ) {
		if ( array_key_exists( $key, $source_array ) ) {
			$position     = array_search( $key, array_keys( $source_array ) ) + 1;
			$source_array = array_slice( $source_array, 0, $position, true ) + $insert_array + array_slice( $source_array, $position, null, true );
		} else {
			// If no key is found, then add it to the end of the array.
			$source_array += $insert_array;
		}

		return $source_array;
	}

	/**
	 * Insert an array immediately before a specified key within another array.
	 *
	 * @param $key
	 * @param $source_array
	 * @param $insert_array
	 *
	 * @return array
	 */
	public static function array_insert_before_key( $key, $source_array, $insert_array ) {
		if ( array_key_exists( $key, $source_array ) ) {
			$position     = array_search( $key, array_keys( $source_array ) );
			$source_array = array_slice( $source_array, 0, $position, true ) + $insert_array + array_slice( $source_array, $position, null, true );
		} else {
			// If no key is found, then add it to the end of the array.
			$source_array += $insert_array;
		}

		return $source_array;
	}

	/**
	 * Get the Post ID from a passed integer, a passed WP_Post object, or the current post.
	 *
	 * Helper function for getting Post ID. Accepts `null` or a Post ID. If attempting
	 * to detect $post object and it is not found, returns `false` to avoid a PHP Notice.
	 *
	 * @param  null|int|WP_Post  $candidate  Post ID or object, `null` to get the ID of the global post object.
	 *
	 * @return int|false The ID of the passed or global post, `false` if the passes object is not a post or the global
	 *                   post is not set.
	 */
	public static function post_id_helper( $candidate = null ) {
		$candidate_post = get_post( $candidate );

		return $candidate_post instanceof WP_Post ? $candidate_post->ID : false;
	}

	/**
	 * Adds a hook
	 *
	 */
	public function store_admin_notices( $page ) {
		if ( 'plugins.php' !== $page ) {
			return;
		}
		$notices = apply_filters( 'tribe_plugin_notices', [] );
		wp_localize_script( 'tribe-pue-notices', 'tribe_plugin_notices', $notices );
	}

	/**
	 * Runs tribe_plugins_loaded action, should be hooked to the end of plugins_loaded
	 */
	public function tribe_plugins_loaded() {
		tribe( 'admin.notice.php.version' );
		tribe( 'cache' );
		tribe_singleton( 'feature-detection', 'Tribe__Feature_Detection' );
		tribe_register_provider( 'Tribe__Service_Providers__Processes' );

		if ( ! defined( 'TRIBE_HIDE_MARKETING_NOTICES' ) ) {
			tribe( 'admin.notice.marketing' );
		}

		tribe( \Tribe\Admin\Notice\WP_Version::class );

		/**
		 * Runs after all plugins including Tribe ones have loaded
		 *
		 * @since 4.3
		 */
		do_action( 'tribe_plugins_loaded' );
	}

	/**
	 * Registers the slug bound to the implementations in the container.
	 *
	 * @since 4.4
	 *
	 * @return void Implementation of components loader doesn't return anything.
	 */
	public function bind_implementations() {
		tribe_singleton( 'settings.manager', 'Tribe__Settings_Manager' );
		tribe_singleton( 'settings', 'Tribe__Settings', [ 'hook' ] );
		tribe_singleton( 'ajax.dropdown', 'Tribe__Ajax__Dropdown', [ 'hook' ] );
		tribe_singleton( 'assets', 'Tribe__Assets' );
		tribe_singleton( 'assets.pipeline', 'Tribe__Assets_Pipeline', [ 'hook' ] );
		tribe_singleton( 'asset.data', 'Tribe__Asset__Data', [ 'hook' ] );
		tribe_singleton( 'admin.helpers', 'Tribe__Admin__Helpers' );
		tribe_singleton( 'tracker', 'Tribe__Tracker', [ 'hook' ] );
		tribe_singleton( 'chunker', 'Tribe__Meta__Chunker', [ 'set_post_types', 'hook' ] );
		tribe_singleton( 'cache', 'Tribe__Cache', [ 'hook' ] );
		tribe_singleton( 'languages.locations', 'Tribe__Languages__Locations' );
		tribe_singleton( 'plugins.api', new Tribe__Plugins_API );
		tribe_singleton( 'logger', 'Tribe__Log' );
		tribe_singleton( 'cost-utils', [ 'Tribe__Cost_Utils', 'instance' ] );
		tribe_singleton( 'post-duplicate.strategy-factory', 'Tribe__Duplicate__Strategy_Factory' );
		tribe_singleton( 'post-duplicate', 'Tribe__Duplicate__Post' );
		tribe_singleton( 'context', 'Tribe__Context' );
		tribe_singleton( 'post-transient', 'Tribe__Post_Transient' );
		tribe_singleton( 'db', 'Tribe__Db' );
		tribe_singleton( 'db-lock', DB_Lock::class );
		tribe_singleton( 'freemius', 'Tribe__Freemius' );
		tribe_singleton( 'customizer', 'Tribe__Customizer' );

		tribe_singleton( Tribe__Dependency::class, Tribe__Dependency::class );

		tribe_singleton( 'callback', 'Tribe__Utils__Callback' );
		tribe_singleton( 'pue.notices', 'Tribe__PUE__Notices' );

		tribe_singleton( Tribe__Admin__Help_Page::class, Tribe__Admin__Help_Page::class );

		tribe_singleton( 'admin.notice.php.version', 'Tribe__Admin__Notice__Php_Version', [ 'hook' ] );
		tribe_singleton( 'admin.notice.marketing', 'Tribe__Admin__Notice__Marketing', [ 'hook' ] );
		tribe_singleton( \Tribe\Admin\Notice\WP_Version::class, \Tribe\Admin\Notice\WP_Version::class, [ 'hook' ] );

		tribe_register_provider( Tribe__Editor__Provider::class );
		tribe_register_provider( Tribe__Service_Providers__Debug_Bar::class );
		tribe_register_provider( Tribe__Service_Providers__Promoter::class );
		tribe_register_provider( Tribe\Service_Providers\Tooltip::class );
		tribe_register_provider( Tribe\Service_Providers\Dialog::class );
		tribe_register_provider( Tribe\Service_Providers\PUE::class );
		tribe_register_provider( Tribe\Service_Providers\Shortcodes::class );
		tribe_register_provider( Tribe\Service_Providers\Body_Classes::class );
		tribe_register_provider( Tribe\Log\Service_Provider::class );
		tribe_register_provider( Tribe\Service_Providers\Crons::class );
		tribe_register_provider( Tribe\Service_Providers\Widgets::class );
	}

	/**
	 * Create the Promoter connector singleton early to allow hook into the filters early.
	 *
	 * Add a filter to determine_current_user during the setup of common library.
	 *
	 * @since 4.9.20
	 *
	 * @return void  Internal method without any return.
	 */
	public function promoter_connector() {
		tribe_singleton( 'promoter.connector', 'Tribe__Promoter__Connector' );

		add_filter(
			'determine_current_user',
			tribe_callback( 'promoter.connector', 'authenticate_user_with_connector' )
		);
	}


	/************************
	 *                      *
	 *  Deprecated Methods  *
	 *                      *
	 ************************/
	// @codingStandardsIgnoreStart

	/**
	 * Helper function to indicate whether the current execution context is AJAX
	 *
	 * This method exists to allow us test code that behaves differently depending on the execution
	 * context.
	 *
	 * @since 4.0
	 *
	 * @todo Add warning with '_deprecated_function'
	 *
	 * @param bool $doing_ajax An injectable status to override the `DOING_AJAX` check.
	 *
	 * @deprecated 4.7.12
	 *
	 * @return boolean
	 */
	public function doing_ajax( $doing_ajax = null ) {
		return tribe( 'context' )->doing_ajax( $doing_ajax );
	}

	/**
	 * Manages PUE license key notifications.
	 *
	 * It's important for the sanity of our users that only one instance of this object
	 * be created. However, multiple Tribe__Main objects can and will be instantiated, hence
	 * why for the time being we need to make this field static.
	 *
	 * @see https://central.tri.be/issues/65755
	 *
	 * @deprecated 4.7.10
	 *
	 * @return Tribe__PUE__Notices
	 */
	public function pue_notices() {
		return tribe( 'pue.notices' );
	}

	/**
	 *
	 * @deprecated 4.7.10
	 *
	 * @return Tribe__Log
	 */
	public function log() {
		return tribe( 'logger' );
	}
	// @codingStandardsIgnoreEnd
}
