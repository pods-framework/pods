<?php


/**
 * Class Tribe__Tabbed_View
 *
 * Models a tabbed view containing tabs.
 */
class Tribe__Tabbed_View {

	/**
	 * A list of all the tabs registered for the tabbed view.
	 *
	 * @var array An associative array in the [<slug> => <instance>] format.
	 */
	protected $items = [];

	/**
	 * The slug of the default tab
	 *
	 * @var string
	 */
	protected $default_tab;

	/**
	 * @var string The absolute path to this tabbed view template file.
	 */
	protected $template;

	/**
	 * An array or value object of data that should be used to render the tabbed view.
	 *
	 * @var array|object
	 */
	protected $data = [];

	/**
	 * @var string
	 */
	protected $label;

	/**
	 * @var string
	 */
	protected $url;

	/**
	 * @var string
	 */
	protected $active;

	/**
	 * Returns the tabbed view URL.
	 *
	 * @param array|string $args     Query String or Array with the arguments
	 * @param boolean      $relative Return a relative URL or absolute
	 *
	 * @return string
	 */
	public function get_url( $args, $relative ) {
		$relative_path = add_query_arg( $args, $this->url );

		return $relative ? $relative_path : admin_url( $relative_path );
	}

	/**
	 * The currently set template for this tabbed view.
	 *
	 * @return string
	 */
	public function get_template() {
		return ! empty( $this->template ) ? $this->template : $this->get_default_template_path();
	}

	/**
	 * @param string $template
	 */
	public function set_template( $template ) {
		$this->template = $template;
	}

	/**
	 * @return string
	 */
	public function get_label() {
		return $this->label;
	}

	/**
	 * @param string $label
	 */
	public function set_label( $label ) {
		$this->label = $label;
	}

	/**
	 * Returns only the visible tabs for this tabbed view.
	 *
	 * @return Tribe__Tabbed_View__Tab[] An array of all the active and visible tabs.
	 */
	public function get_visibles() {
		return array_filter( $this->get(), [ $this, 'is_tab_visible' ] );
	}

	/**
	 * @param string $url
	 */
	public function set_url( $url ) {
		$this->url = $url;
	}

	/**
	 * Sets the slug of the currently active tab.
	 *
	 * This value, if the tab exists, will override the value specified in the GET request.
	 *
	 * @param string $active
	 */
	public function set_active( $active ) {
		$this->active = $active;
	}

	/**
	 * A method to sort tabs by priority in ascending order.
	 *
	 * @param  object $a First tab to compare
	 * @param  object $b Second tab to compare
	 *
	 * @return int
	 */
	protected function sort_by_priority( $a, $b ) {
		$a_priority = $a->get_priority();
		$b_priority = $b->get_priority();

		if ( $a_priority == $b_priority ) {
			return 0;
		}

		return ( $a_priority < $b_priority ) ? - 1 : 1;
	}

	/**
	 * Removes a tab from the tabbed view items.
	 *
	 * @param  string $slug The slug of the tab to remove
	 *
	 * @return boolean `true` if the slug was registered and removed, `false` otherwise
	 */
	public function remove( $slug ) {
		if ( ! $this->exists( $slug ) ) {
			return false;
		}

		unset( $this->items[ $slug ] );

		return true;
	}

	/**
	 * Checks if a given tab exist
	 *
	 * @param  string $slug The slug of the tab
	 *
	 * @return boolean
	 */
	public function exists( $slug ) {
		return is_object( $this->get( $slug ) ) ? true : false;
	}

	/**
	 * Fetches the Instance of the Tab or all the tabs
	 *
	 * @param  string $slug (optional) The Slug of the Tab
	 *
	 * @return null|array|object        If we couldn't find the tab it will be null, if the slug is null will return all tabs
	 */
	public function get( $slug = null ) {
		uasort( $this->items, [ $this, 'sort_by_priority' ] );

		if ( is_null( $slug ) ) {
			return $this->items;
		}

		// Prevent weird stuff here
		$slug = sanitize_title_with_dashes( $slug );

		if ( ! empty( $this->items[ $slug ] ) ) {
			return $this->items[ $slug ];
		}

		return null;
	}

	/**
	 * Checks if a given Tab (slug) is active
	 *
	 * @param  string $slug The Slug of the Tab
	 *
	 * @return boolean       Is this tab active?
	 */
	public function is_active( $slug = null ) {
		$slug = $this->get_requested_slug( $slug );
		$tab  = $this->get_active();

		return $slug === $tab->get_slug();
	}

	/**
	 * Returns the slug of tab requested in the `_GET` array or the default one.
	 *
	 * @param string|null $slug
	 * @param mixed       $default A default value to return if the tab was not requested.
	 *
	 * @return string|bool Either the slug of the requested tab or `false` if no slug was requested
	 *                     and no default tab is set.
	 */
	protected function get_requested_slug( $slug = null, $default = null ) {
		if ( is_null( $slug ) ) {
			$default = null === $default ? $this->get_default_tab() : $default;
			// Set the slug
			$slug = ! empty( $_GET['tab'] ) && $this->exists( $_GET['tab'] ) ? $_GET['tab'] : $default;
		}

		return $slug;
	}

	/**
	 * Fetches the current active tab instance.
	 *
	 * @return Tribe__Tabbed_View__Tab|bool The active tab, the default tab if no tab is active,
	 *                                      `false` if no tabs are registered in the Tabbed View.
	 */
	public function get_active() {
		if ( ! empty( $this->active ) && $this->exists( $this->active ) ) {
			return $this->get( $this->active );
		}

		$tab = ! empty( $_GET['tab'] ) && $this->exists( $_GET['tab'] ) ? $_GET['tab'] : $this->get_default_tab();

		// Return the active tab or the default one
		return ! empty( $tab ) ? $this->get( $tab ) : false;
	}

	/**
	 * Returns the slug of the default tab for this tabbed view.
	 *
	 * @return string The slug of the default tab, the slug of the first tab if
	 *                a default tab is not set, `false` otherwise.
	 */
	public function get_default_tab() {
		if ( ! empty( $this->default_tab ) && $this->exists( $this->default_tab ) ) {
			return $this->default_tab;
		}

		$tabs = $this->get_tabs();

		if ( empty( $tabs ) ) {
			return false;
		}

		return reset( $tabs )->get_slug();
	}

	/**
	 * @param Tribe__Tabbed_View__Tab|string $tab
	 *
	 * @return Tribe__Tabbed_View__Tab
	 */
	public function register( $tab ) {
		$is_object = $tab instanceof Tribe__Tabbed_View__Tab;
		if ( ! ( $is_object || ( is_string( $tab ) && class_exists( $tab ) ) ) ) {
			return false;
		}

		if ( ! $is_object ) {
			$tab = $this->get_new_tab_instance( $tab );
		}

		// Set the Tab Item on the array of Tabs
		$tab_slug = $tab->get_slug();

		if ( empty( $tab_slug ) ) {
			return false;
		}

		$this->items[ $tab_slug ] = $tab;

		// Return the tab
		return $tab;
	}

	/**
	 * Returns all the registered tabs.
	 *
	 * @return Tribe__Tabbed_View__Tab[]
	 */
	public function get_tabs() {
		uasort( $this->items, [ $this, 'sort_by_priority' ] );

		return array_values( $this->items );
	}

	/**
	 * Builds an instance of the specified tab class.
	 *
	 * @param string $tab_class
	 *
	 * @return Tribe__Tabbed_View__Tab
	 */
	protected function get_new_tab_instance( $tab_class ) {
		return new $tab_class( $this );
	}

	/**
	 * Renders the tabbed view and returns the resulting HTML.
	 *
	 * @return string
	 */
	public function render() {
		$visibles = $this->get_visibles();
		if ( empty( $visibles ) ) {
			return '';
		}

		if ( empty( $this->template ) ) {
			$this->template = $this->get_default_template_path();
		}

		$template = $this->template;

		if ( empty( $template ) ) {
			return '';
		}

		$default_data = [
			'view' => $this,
		];

		$data = array_merge( $default_data, (array) $this->data );

		extract( $data );

		ob_start();

		include $template;

		$html = ob_get_clean();

		return $html;
	}

	/**
	 * Sets the default tab for the tabbed view.
	 *
	 * Please note that no check is made on the tabbed view items to ensure the value
	 * corresponds to a registered tab.
	 *
	 * @param string $default_tab The slug of the default tab.
	 */
	public function set_default_tab( $default_tab ) {
		$this->default_tab = $default_tab;
	}

	/**
	 * @param Tribe__Tabbed_View__Tab $tab
	 *
	 * @return bool
	 */
	protected function is_tab_visible( Tribe__Tabbed_View__Tab $tab ) {
		return $tab->is_visible();
	}

	/**
	 * Returns the absolute path to the default template for the tabbed view.
	 *
	 * @return string
	 */
	public function get_default_template_path() {
		return Tribe__Main::instance()->plugin_path . '/src/admin-views/tabbed-view/tabbed-view.php';
	}
}
