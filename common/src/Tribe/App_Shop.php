<?php

// don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( ! class_exists( 'Tribe__App_Shop' ) ) {
	/**
	 * Class that handles the integration with our Shop App API
	 */
	class Tribe__App_Shop {

		/**
		 * Slug of the WP admin menu item
		 */
		const MENU_SLUG = 'tribe-app-shop';

		/**
		 * Singleton instance
		 *
		 * @var null or Tribe__App_Shop
		 */
		private static $instance = null;
		/**
		 * The slug for the new admin page
		 *
		 * @var string
		 */
		private $admin_page = null;

		/**
		 * Class constructor
		 */
		public function __construct() {
			add_action( 'admin_menu', array( $this, 'add_menu_page' ), 100 );
			add_action( 'wp_before_admin_bar_render', array( $this, 'add_toolbar_item' ), 20 );

			$this->register_assets();
		}

		/**
		 * Adds the page to the admin menu
		 */
		public function add_menu_page() {
			if ( ! Tribe__Settings::instance()->should_setup_pages() ) {
				return;
			}

			$page_title = esc_html__( 'Event Add-Ons', 'tribe-common' );
			$menu_title = esc_html__( 'Event Add-Ons', 'tribe-common' );
			$capability = apply_filters( 'tribe_events_addon_page_capability', 'install_plugins' );

			$where = Tribe__Settings::instance()->get_parent_slug();

			$this->admin_page = add_submenu_page( $where, $page_title, $menu_title, $capability, self::MENU_SLUG, array( $this, 'do_menu_page' ) );
		}

		/**
		 * Adds a link to the shop app to the WP admin bar
		 */
		public function add_toolbar_item() {

			$capability = apply_filters( 'tribe_events_addon_page_capability', 'install_plugins' );

			// prevent users who cannot install plugins from seeing addons link
			if ( current_user_can( $capability ) ) {
				global $wp_admin_bar;

				$wp_admin_bar->add_menu( array(
					'id'     => 'tribe-events-app-shop',
					'title'  => esc_html__( 'Event Add-Ons', 'tribe-common' ),
					'href'   => Tribe__Settings::instance()->get_url( array( 'page' => self::MENU_SLUG ) ),
					'parent' => 'tribe-events-settings-group',
				) );
			}
		}

		/**
		 * Registers the plugin assets
		 */
		protected function register_assets() {
			tribe_assets(
				Tribe__Main::instance(),
				array(
					array( 'tribe-app-shop-css', 'app-shop.css' ),
					array( 'tribe-app-shop-js', 'app-shop.js', array( 'jquery' ) ),
				),
				'admin_enqueue_scripts',
				array(
					'conditionals' => array( $this, 'is_current_page' ),
				)
			);
		}

		/**
		 * Checks if the current page is the app shop
		 *
		 * @since 4.5.7
		 *
		 * @return bool
		 */
		public function is_current_page() {
			if ( ! Tribe__Settings::instance()->should_setup_pages() || ! did_action( 'admin_menu' ) ) {
				return false;
			}

			if ( is_null( $this->admin_page ) ) {
				_doing_it_wrong(
					__FUNCTION__,
					'Function was called before it is possible to accurately determine what the current page is.',
					'4.5.6'
				);
				return false;
			}

			return Tribe__Admin__Helpers::instance()->is_screen( $this->admin_page );
		}

		/**
		 * Renders the Shop App page
		 */
		public function do_menu_page() {
			$main = Tribe__Main::instance();
			$products = $this->get_all_products();
			include_once Tribe__Main::instance()->plugin_path . 'src/admin-views/app-shop.php';
		}

		/**
		 * Get's all products from the API
		 *
		 * @return array|WP_Error
		 */
		private function get_all_products() {
			$all_products = tribe( 'plugins.api' )->get_products();

			$products = array(
				(object) $all_products['event-aggregator'],
				(object) $all_products['events-calendar-pro'],
				(object) $all_products['event-tickets-plus'],
				(object) $all_products['promoter'],
				(object) $all_products['tribe-filterbar'],
				(object) $all_products['events-community'],
				(object) $all_products['events-community-tickets'],
				(object) $all_products['tribe-eventbrite'],
				(object) $all_products['image-widget-plus'],
			);

			return $products;
		}

		/**
		 * Static Singleton Factory Method
		 *
		 * @return Tribe__App_Shop
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) ) {
				$className      = __CLASS__;
				self::$instance = new $className;
			}

			return self::$instance;
		}
	}
}
