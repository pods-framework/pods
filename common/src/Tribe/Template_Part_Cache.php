<?php

/**
 * Class Tribe__Template_Part_Cache
 *
 * @uses TribeEventsCache
 */
class Tribe__Template_Part_Cache {

	/**
	 * @var string
	 */
	private $template;

	/**
	 * @var int
	 */
	private $expiration;

	/**
	 * @var string
	 */
	private $expiration_trigger;

	/**
	 * @var TribeEventsCache
	 */
	private $cache;

	/**
	 * @var string
	 */
	private $html;

	/**
	 ** Short description
	 *
	 * @param $template           - which template in the views directory is being cached (relative path).
	 * @param $id                 - a unique identifier for this fragment.
	 * @param $expiration         - expiration time for the cached fragment.
	 * @param $expiration_trigger - wordpress hook to expire on.
	 */
	public function __construct( $template, $id, $expiration, $expiration_trigger ) {
		$this->template           = $template;
		$this->key                = $template . '_' . $id;
		$this->expiration         = $expiration;
		$this->expiration_trigger = $expiration_trigger;
		$this->cache              = new Tribe__Cache();

		$this->add_hooks();
	}

	/**
	 * Hook in to show cached content and bypass queries where needed
	 */
	public function add_hooks() {

		// set the cached html in transients after the template part is included
		add_filter( 'tribe_get_template_part_content', array( $this, 'set' ), 10, 2 );

		// get the cached html right before the setup_view runs so it's available for bypassing any view logic
		add_action( 'tribe_events_before_view', array( $this, 'get' ), 9, 1 );

		// when the specified template part is included, show the cached html instead
		add_filter( 'tribe_get_template_part_path_' . $this->template, array( $this, 'display' ) );
	}

	/**
	 * Checks if there is a cached html fragment in the transients, if it's there,
	 * don't include the requested file path. If not, just return the file path like normal
	 *
	 * @param $path file path to the month view template part
	 *
	 * @return bool
	 * @uses tribe_get_template_part_path_[template] hook
	 */
	public function display( $path ) {

		if ( $this->html !== false ) {
			echo $this->html;

			return false;
		}

		return $path;

	}

	/**
	 * Set cached html in transients
	 *
	 * @param $html
	 * @param $template
	 *
	 * @return string
	 * @uses tribe_get_template_part_content hook
	 */
	public function set( $html, $template ) {
		if ( $template == $this->template ) {
			$this->cache->set_transient( $this->key, $html, $this->expiration, $this->expiration_trigger );
		}

		return $html;
	}

	/**
	 * Retrieve the cached html from transients, set class property
	 *
	 * @uses tribe_events_before_view hook
	 */
	public function get() {

		if ( isset( $this->html ) ) {

			return $this->html;
		}

		$this->html = $this->cache->get_transient( $this->key, $this->expiration_trigger );

		return $this->html;

	}
}
