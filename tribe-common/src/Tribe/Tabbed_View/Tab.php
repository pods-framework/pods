<?php


/**
 * Class Tribe__Tabbed_View__Tab
 *
 * Models a tab part of a tabbed view.
 */
class Tribe__Tabbed_View__Tab {

	/**
	 * To Order the Tabs on the UI you need to change the priority
	 *
	 * @var integer
	 */
	public $priority = 50;

	/**
	 * An array or value object of data that should be used to render the tabbed view.
	 *
	 * @var array|object
	 */
	protected $data = [];

	/**
	 * The template file that should be used to render the tab.
	 *
	 * @var string
	 */
	protected $template;

	/**
	 * The tabbed view instance containing this tab.
	 *
	 * @var Tribe__Tabbed_View
	 */
	protected $tabbed_view;

	/**
	 * @var string
	 */
	protected $slug;

	/**
	 * @var bool
	 */
	protected $visible = true;

	/**
	 * @var string
	 */
	protected $label = '';

	/**
	 * @var string
	 */
	protected $url = '';

	/**
	 * Tribe__Tabbed_View__Tab constructor.
	 *
	 * @param Tribe__Tabbed_View $tabbed_view
	 * @param string             $slug
	 */
	public function __construct( Tribe__Tabbed_View $tabbed_view, $slug = null ) {
		$this->tabbed_view = $tabbed_view;
		$this->slug        = ! empty( $slug ) ? $slug : $this->slug;
	}

	/**
	 * @return int
	 */
	public function get_priority() {
		return $this->priority;
	}

	/**
	 * @param $priority
	 */
	public function set_priority( $priority ) {
		$this->priority = $priority;
	}

	/**
	 * @return array|object
	 */
	public function get_data() {
		return $this->data;
	}

	/**
	 * @param array $data
	 */
	public function set_data( $data ) {
		$this->data = $data;
	}

	/**
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
	 * Returns the absolute path to the default template for the tab.
	 *
	 * @return string
	 */
	public function get_default_template_path() {
		return Tribe__Main::instance()->plugin_path . '/src/admin-views/tabbed-view/tab.php';
	}

	/**
	 * Whether the tab should display or not.
	 *
	 * @return boolean
	 */
	public function is_visible() {
		return $this->visible;
	}

	/**
	 * @param boolean $visible
	 */
	public function set_visible( $visible ) {
		$this->visible = $visible;
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
	 * Creates a way to include the this tab HTML easily
	 *
	 * @return string HTML content of the tab
	 */
	public function render() {
		if ( empty( $this->template ) ) {
			$this->template = Tribe__Main::instance()->plugin_path . '/src/admin-views/tabbed-view/tab.php';
		}

		$template = $this->template;

		if ( empty( $template ) ) {
			return '';
		}

		$default_data = [
			'tab' => $this,
		];

		$data = array_merge( $default_data, (array) $this->data );

		extract( $data );

		ob_start();

		include $template;

		$html = ob_get_clean();

		return $html;
	}

	/**
	 * Returns the link to this tab
	 *
	 * @param array|string $args     Query String or Array with the arguments
	 * @param boolean      $relative Return a relative URL or absolute
	 *
	 * @return string
	 */
	public function get_url( $args = [], $relative = false ) {
		if ( ! empty( $this->url ) ) {
			return $this->url;
		}

		$defaults = [
			'tab' => $this->get_slug(),
		];

		// Allow the link to be "changed" on the fly
		$args = wp_parse_args( $args, $defaults );

		// Escape after the filter
		return $this->tabbed_view->get_url( $args, $relative );
	}

	/**
	 * Sets this tab URL.
	 *
	 * This URL will override the tab natural URL.
	 *
	 * @param string $url
	 */
	public function set_url( $url ) {
		$this->url = $url;
	}

	/**
	 * Returns the tab slug
	 *
	 * @return string
	 */
	public function get_slug() {
		return $this->slug;
	}

	/**
	 * Determines if this Tab is currently displayed
	 *
	 * @return boolean
	 */
	public function is_active() {
		$active = $this->tabbed_view->get_active();

		return ! empty( $active ) ? $this->get_slug() === $active->get_slug() : false;
	}
}
