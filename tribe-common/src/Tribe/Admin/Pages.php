<?php
namespace Tribe\Admin;

/**
 * Class Pages.
 *
 * @since 4.15.0
 */
class Pages {
	/**
	 * Current page ID (or false if not registered with this controller).
	 *
	 * @since 4.15.0
	 *
	 * @var string|null
	 */
	private $current_page = null;

	/**
	 * Registered pages
	 * Contains information (breadcrumbs, menu info) about TEC admin pages.
	 *
	 * @since 4.15.0
	 *
	 * @var array<string,mixed>
	 */
	private $pages = [];

	/**
	 * Get registered pages.
	 *
	 * @since 4.15.0
	 *
	 * @return array $pages {
	 *     Array containing the registered pages.
	 *
	 *     @type array $page_id {
	 *         @type string      id           Id to reference the page.
	 *         @type array       title        Page title. Used in menus and breadcrumbs.
	 *         @type string|null parent       Parent ID. Null for new top level page.
	 *         @type string      path         Path for this page, full path in app context; ex /analytics/report
	 *         @type string      capability   Capability needed to access the page.
	 *         @type string      icon         Icon. Dashicons helper class, base64-encoded SVG, or 'none'.
	 *         @type int         position     Menu item position.
	 *         @type int         order        Navigation item order.
	 *         @type callable    callback     The function to be called to output the content for the page.
	 *     }
	 * }
	 */
	public function get_pages() {
		/**
		 * Filters the list of registered TEC admin pages.
		 *
		 * @since 4.15.0
		 *
		 * @param array $pages {
		 *     Array containing the registered pages to be filtered
		 *
		 *     @type array $page_id {
		 *         @type string      id           Id to reference the page.
		 *         @type array       title        Page title. Used in menus and breadcrumbs.
		 *         @type string|null parent       Parent ID. Null for new top level page.
		 *         @type string      path         Path for this page, full path in app context; ex /analytics/report
		 *         @type string      capability   Capability needed to access the page.
		 *         @type string      icon         Icon. Dashicons helper class, base64-encoded SVG, or 'none'.
		 *         @type int         position     Menu item position.
		 *         @type int         order        Navigation item order.
		 *         @type callable    callback     The function to be called to output the content for the page.
		 *     }
		 * }
		 */
		$pages = apply_filters( 'tec_admin_pages', $this->pages );

		return $pages;
	}

	/**
	 * Adds a page to `tec-admin`.
	 *
	 * @since 4.15.0
	 *
	 * @param array $options {
	 *   Array describing the page.
	 *
	 *   @type string      id           Id to reference the page.
	 *   @type string      title        Page title. Used in menus and breadcrumbs.
	 *   @type string|null parent       Parent ID. Null for new top level page.
	 *   @type string      path         Path for this page, full path in app context; ex /analytics/report
	 *   @type string      capability   Capability needed to access the page.
	 *   @type string      icon         Icon. Dashicons helper class, base64-encoded SVG, or 'none'.
	 *   @type int         position     Menu item position.
	 *   @type int         order        Navigation item order.
	 *   @type callable    callback     The function to be called to output the content for the page.
	 * }
	 *
	 * @return string $page The resulting page's hook_suffix.
	 *
	 */
	public function register_page( $options = [] ) {
		$defaults = [
			'id'         => null,
			'parent'     => null,
			'title'      => '',
			'capability' => self::get_capability(),
			'path'       => '',
			'icon'       => '',
			'position'   => null,
			'callback'   => [ __CLASS__, 'render_page' ],
		];

		$options = wp_parse_args( $options, $defaults );

		if ( is_null( $options['parent'] ) ) {
			$page = add_menu_page(
				$options['title'],
				$options['title'],
				$options['capability'],
				$options['path'],
				$options['callback'],
				$options['icon'],
				$options['position']
			);
		} else {
			$page = add_submenu_page(
				$options['parent'],
				$options['title'],
				$options['title'],
				$options['capability'],
				$options['path'],
				$options['callback']
			);
		}

		$this->connect_page( $options );

		return $page;
	}

	/**
	 * Get the current page.
	 *
	 * @since 4.15.0
	 *
	 * @return string|boolean Current page or false if not registered with this controller.
	 */
	public function get_current_page() {
		if ( is_null( $this->current_page ) ) {
			$this->determine_current_page();
		}

		return $this->current_page;
	}

	/**
	 * Determine the current page.
	 *
	 * @since 4.15.0
	 *
	 * @return string|boolean Current page or false if not registered with this controller.
	 */
	public function determine_current_page() {
		$current_screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

		if ( is_null( $current_screen ) ) {
			$this->current_page = tribe_get_request_var( 'page' );
			return $this->current_page;
		}

		$this->current_page = $current_screen->id;

		return $this->current_page;
	}

	/**
	 * Connect an existing page to wp-admin.
	 *
	 * @since 4.15.0
	 *
	 * @param array $options {
	 *   Array describing the page.
	 *
	 *   @type string       id           Id to reference the page.
	 *   @type string|array title        Page title. Used in menus and breadcrumbs.
	 *   @type string|null  parent       Parent ID. Null for new top level page.
	 *   @type string       path         Path for this page. E.g. admin.php?page=wc-settings&tab=checkout
	 *   @type string       capability   Capability needed to access the page.
	 *   @type string       icon         Icon. Dashicons helper class, base64-encoded SVG, or 'none'.
	 *   @type int          position     Menu item position.
	 * }
	 */
	public function connect_page( $options = [] ) {
		if ( ! is_array( $options['title'] ) ) {
			$options['title'] = array( $options['title'] );
		}

		/**
		 * Filter the options when connecting or registering a page.
		 *
		 * @param array $options {
		 *   Array describing the page.
		 *
		 *   @type string       id           Id to reference the page.
		 *   @type string|array title        Page title. Used in menus and breadcrumbs.
		 *   @type string|null  parent       Parent ID. Null for new top level page.
		 *   @type string       screen_id    The screen ID that represents the connected page. (Not required for registering).
		 *   @type string       path         Path for this page. E.g. admin.php?page=wc-settings&tab=checkout
		 *   @type string       capability   Capability needed to access the page.
		 *   @type string       icon         Icon. Dashicons helper class, base64-encoded SVG, or 'none'.
		 *   @type int          position     Menu item position.
		 *   @type boolean      js_page      If this is a JS-powered page.
		 * }
		 */
		$options = apply_filters( 'tec_admin_pages_connect_page_options', $options );

		$this->pages[ $options['id'] ] = $options;
	}

	/**
	 * Get the capability.
	 *
	 * @param string $capability The capability required for a TEC page to be displayed to the user.
	 *
	 * @since 4.15.0
	 *
	 * @return string The capability required for a TEC page to be displayed to the user.
	 */
	public static function get_capability( $capability = 'manage_options' ) {
		/**
		 * Filters the default capability for Tribe admin pages.
		 *
		 * @param string $capability The capability required for a TEC page to be displayed to the user.
		 *
		 * @todo: We'll need to deprecate this one in favor of the one below.
		 */
		$capability = apply_filters( 'tribe_common_event_page_capability', $capability );

		/**
		 * Filters the default capability for TEC admin pages.
		 *
		 * @param string $capability The capability required for a TEC page to be displayed to the user.
		 *
		 * @since 4.15.0
		 */
		$capability = apply_filters( 'tec_admin_pages_capability', $capability );

		return $capability;
	}

	/**
	 * Define if is a `tec` admin page (registered).
	 *
	 * @since 4.15.0
	 *
	 * @param string $page_id The ID of the page to check if is a `tec` admin page.
	 *
	 * @return boolean True if is a `tec` admin page, false otherwise.
	 */
	public function is_tec_page( $page_id = '' ) {
		return in_array( $page_id, array_keys( $this->pages ), true );
	}

	/**
	 * Get pages with tabs.
	 * @since 4.15.0
	 *
	 * @param array $pages The list of pages with tabs.
	 * @return array $pages The list of pages with tabs, filtered.
	 */
	public function get_pages_with_tabs( $pages = [] ) {
		/**
		* Filters the pages with tabs.
		*
		* @param array $pages Pages with tabs.
		*
		* @since 4.15.0
		*/
		return apply_filters(
			'tec_admin_pages_with_tabs',
			$pages
		);
	}

	/**
	 * Check if the current page has tabs.
	 *
	 * @since 4.15.0
	 *
	 * @param string $page The page slug.
	 * @return boolean True if the page has tabs, false otherwise.
	 */
	public function has_tabs( $page = '' ) {
		if ( empty( $page ) ) {
			$page = $this->get_current_page();
		}

		return in_array( $page, $this->get_pages_with_tabs() );
	}

	/**
	 * Generic page.
	 *
	 * @since 4.15.0
	 */
	public static function render_page() {
		?>
		<div class="wrap"></div>
		<?php
	}
}