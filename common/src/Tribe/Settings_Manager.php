<?php

class Tribe__Settings_Manager {
	protected static $network_options;
	public static $tribe_events_mu_defaults;

	/**
	 * constructor
	 */
	public function __construct() {
		$this->add_hooks();

		// Load multisite defaults
		if ( is_multisite() ) {
			$tribe_events_mu_defaults = array();
			if ( file_exists( WP_CONTENT_DIR . '/tribe-events-mu-defaults.php' ) ) {
				require_once WP_CONTENT_DIR . '/tribe-events-mu-defaults.php';
			}
			self::$tribe_events_mu_defaults = apply_filters( 'tribe_events_mu_defaults', $tribe_events_mu_defaults );
		}
	}

	public function add_hooks() {
		// option pages
		add_action( '_network_admin_menu', array( $this, 'init_options' ) );
		add_action( '_admin_menu', array( $this, 'init_options' ) );

		add_action( 'admin_menu', array( $this, 'add_help_admin_menu_item' ), 50 );
		add_action( 'tribe_settings_do_tabs', array( $this, 'do_setting_tabs' ) );
		add_action( 'tribe_settings_do_tabs', array( $this, 'do_network_settings_tab' ), 400 );
		add_action( 'tribe_settings_validate_tab_network', array( $this, 'save_all_tabs_hidden' ) );
	}

	/**
	 * Init the settings API and add a hook to add your own setting tabs
	 *
	 * @return void
	 */
	public function init_options() {
		Tribe__Settings::instance();
	}

	/**
	 * Create setting tabs
	 *
	 * @return void
	 */
	public function do_setting_tabs() {
		// Make sure Thickbox is available regardless of which admin page we're on
		add_thickbox();

		include_once Tribe__Main::instance()->plugin_path . 'src/admin-views/tribe-options-general.php';
		include_once Tribe__Main::instance()->plugin_path . 'src/admin-views/tribe-options-display.php';

		$showNetworkTabs = $this->get_network_option( 'showSettingsTabs', false );

		new Tribe__Settings_Tab( 'general', esc_html__( 'General', 'tribe-common' ), $generalTab );
		new Tribe__Settings_Tab( 'display', esc_html__( 'Display', 'tribe-common' ), $displayTab );

		$this->do_licenses_tab();
	}

	/**
	 * Get all options for the Events Calendar
	 *
	 * @return array of options
	 */
	public static function get_options() {
		$options = (array) get_option( Tribe__Main::OPTIONNAME, array() );
		if ( has_filter( 'tribe_get_options' ) ) {
			_deprecated_function( 'tribe_get_options', '3.10', 'option_' . Tribe__Main::OPTIONNAME );
			$options = apply_filters( 'tribe_get_options', $options );
		}
		return $options;
	}

	/**
	 * Get value for a specific option
	 *
	 * @param string $option_name name of option
	 * @param string $default     default value
	 *
	 * @return mixed results of option query
	 */
	public static function get_option( $option_name, $default = '' ) {
		if ( ! $option_name ) {
			return null;
		}
		$options = self::get_options();

		$option = $default;
		if ( array_key_exists( $option_name, $options ) ) {
			$option = $options[ $option_name ];
		} elseif ( is_multisite() && isset( self::$tribe_events_mu_defaults ) && is_array( self::$tribe_events_mu_defaults ) && in_array( $option_name, array_keys( self::$tribe_events_mu_defaults ) ) ) {
			$option = self::$tribe_events_mu_defaults[ $option_name ];
		}

		return apply_filters( 'tribe_get_single_option', $option, $default, $option_name );
	}

	/**
	 * Saves the options for the plugin
	 *
	 * @param array $options formatted the same as from get_options()
	 * @param bool  $apply_filters
	 *
	 * @return bool
	 */
	public static function set_options( $options, $apply_filters = true ) {
		if ( ! is_array( $options ) ) {
			return false;
		}
		if ( $apply_filters == true ) {
			$options = apply_filters( 'tribe-events-save-options', $options );
		}
		return update_option( Tribe__Main::OPTIONNAME, $options );
	}

	/**
	 * Set an option
	 *
	 * @param string $name
	 * @param mixed  $value
	 *
	 * @return bool
	 */
	public static function set_option( $name, $value ) {
		$newOption        = array();
		$newOption[ $name ] = $value;
		$options          = self::get_options();
		return self::set_options( wp_parse_args( $newOption, $options ) );
	}

	/**
	 * Get all network options for the Events Calendar
	 *
	 * @return array of options
	 * @TODO add force option, implement in setNetworkOptions
	 */
	public static function get_network_options() {
		if ( ! isset( self::$network_options ) ) {
			$options              = get_site_option( Tribe__Main::OPTIONNAMENETWORK, array() );
			self::$network_options = apply_filters( 'tribe_get_network_options', $options );
		}

		return self::$network_options;
	}

	/**
	 * Get value for a specific network option
	 *
	 * @param string $option_name name of option
	 * @param string $default    default value
	 *
	 * @return mixed results of option query
	 */
	public static function get_network_option( $option_name, $default = '' ) {
		if ( ! $option_name ) {
			return null;
		}

		if ( ! isset( self::$network_options ) ) {
			self::get_network_options();
		}

		if ( isset( self::$network_options[ $option_name ] ) ) {
			$option = self::$network_options[ $option_name ];
		} else {
			$option = $default;
		}

		return apply_filters( 'tribe_get_single_network_option', $option, $default );
	}

	/**
	 * Saves the network options for the plugin
	 *
	 * @param array $options formatted the same as from get_options()
	 * @param bool  $apply_filters
	 *
	 * @return void
	 */
	public static function set_network_options( $options, $apply_filters = true ) {
		if ( ! is_array( $options ) ) {
			return;
		}
		if ( $apply_filters == true ) {
			$options = apply_filters( 'tribe-events-save-network-options', $options );
		}

		// @TODO use getNetworkOptions + force
		if ( update_site_option( Tribe__Main::OPTIONNAMENETWORK, $options ) ) {
			self::$network_options = apply_filters( 'tribe_get_network_options', $options );
		} else {
			self::$network_options = self::get_network_options();
		}
	}

	/**
	 * Add the network admin options page
	 *
	 * @return void
	 */
	public static function add_network_options_page() {
		$tribe_settings = Tribe__Settings::instance();
		add_submenu_page(
			'settings.php', $tribe_settings->menuName, $tribe_settings->menuName, 'manage_network_options', 'tribe-common', array(
				$tribe_settings,
				'generatePage',
			)
		);
	}

	/**
	 * Render network admin options view
	 *
	 * @return void
	 */
	public static function do_network_settings_tab() {
		include_once Tribe__Main::instance()->plugin_path . 'src/admin-views/tribe-options-network.php';

		new Tribe__Settings_Tab( 'network', esc_html__( 'Network', 'tribe-common' ), $networkTab );
	}

	/**
	 * Registers the license key management tab in the Events > Settings screen,
	 * only if premium addons are detected.
	 */
	protected function do_licenses_tab() {
		$show_tab = ( current_user_can( 'activate_plugins' ) && $this->have_addons() );

		/**
		 * Provides an oppotunity to override the decision to show or hide the licenses tab
		 *
		 * Normally it will only show if the current user has the "activate_plugins" capability
		 * and there are some currently-activated premium plugins.
		 *
		 * @var bool
		 */
		if ( ! apply_filters( 'tribe_events_show_licenses_tab', $show_tab ) ) {
			return;
		}

		/**
		 * @var $licenses_tab
		 */
		include Tribe__Main::instance()->plugin_path . 'src/admin-views/tribe-options-licenses.php';

		/**
		 * Allows the fields displayed in the licenses tab to be modified.
		 *
		 * @var array
		 */
		$license_fields = apply_filters( 'tribe_license_fields', $licenses_tab );

		new Tribe__Settings_Tab( 'licenses', esc_html__( 'Licenses', 'tribe-common' ), array(
			'priority'      => '40',
			'fields'        => $license_fields,
			'network_admin' => is_network_admin() ? true : false,
		) );
	}

	/**
	 * Create the help tab
	 */
	public function do_help_tab() {
		/**
		 * Include Help tab Assets here
		 */

		include_once Tribe__Main::instance()->plugin_path . 'src/admin-views/tribe-options-help.php';
	}

	/**
	 * Add help menu item to the admin (unless blocked via network admin settings).
	 *
	 * @todo move to an admin class
	 */
	public function add_help_admin_menu_item() {
		$hidden_settings_tabs = self::get_network_option( 'hideSettingsTabs', array() );
		if ( in_array( 'help', $hidden_settings_tabs ) ) {
			return;
		}

		$parent = class_exists( 'Tribe__Events__Main' ) ? Tribe__Settings::$parent_page : Tribe__Settings::$parent_slug;
		$title  = esc_html__( 'Help', 'tribe-common' );
		$slug   = 'tribe-help';

		add_submenu_page( $parent, $title, $title, 'manage_options', $slug, array( $this, 'do_help_tab' ) );
	}

	/**
	 * Tries to discover if licensable addons are activated on the same site.
	 *
	 * @return bool
	 */
	protected function have_addons() {
		$addons = apply_filters( 'tribe_licensable_addons', array() );
		return ! empty( $addons );
	}

	/**
	 * Save hidden tabs
	 *
	 * @return void
	 */
	public function save_all_tabs_hidden() {
		$all_tabs_keys = array_keys( apply_filters( 'tribe_settings_all_tabs', array() ) );

		$network_options = (array) get_site_option( Tribe__Main::OPTIONNAMENETWORK );

		if ( isset( $_POST['hideSettingsTabs'] ) && $_POST['hideSettingsTabs'] == $all_tabs_keys ) {
			$network_options['allSettingsTabsHidden'] = '1';
		} else {
			$network_options['allSettingsTabsHidden'] = '0';
		}

		$this->set_network_options( $network_options );
	}

	/**
	 * Static Singleton Factory Method
	 *
	 * @return Tribe__Settings_Manager
	 */
	public static function instance() {
		return tribe( 'settings.manager' );
	}
}
