<?php
defined( 'WPINC' ) || die; // Do not load directly.

/**
 * Filters meta links in the WP Admin > Plugins list
 */
class Tribe__Plugin_Meta_Links {
	/**
	 * Class instance
	 *
	 * @var Tribe__Plugin_Meta_Links The singleton instance.
	 */
	private static $instance;

	/**
	 * The various meta links that will be added
	 *
	 * @var array {
	 *      Each plugin that will be filtered.
	 *
	 *      @type array $plugin_basename {
	 *          Meta links added to this plugin.
	 *
	 *          @type array {
	 *              Each individual link.
	 *
	 *              @type string $html   The full HTML for this link.
	 *              @type bool   $remove Whether we are adding or removing this link.
	 *          }
	 *      }
	 * }
	 */
	private $meta_links = array();

	/**
	 * Returns the singleton instance of this class.
	 *
	 * @return Tribe__Plugin_Meta_Links instance.
	 */
	public static function instance() {
		return null === self::$instance ? new self() : self::$instance;
	}

	private function __construct() {
		add_action( 'plugin_row_meta', array( $this, 'filter_meta_links' ), 10, 2 );
	}

	/**
	 * Adds an <a> link to the meta list
	 *
	 * @param string $plugin     Path to plugin file.
	 * @param string $text       Inner text for HTML element.
	 * @param string $href       URL for the link.
	 * @param array  $attributes Key => value attributes for element.
	 */
	public function add_link( $plugin, $title, $href, $attributes = array() ) {
		$attributes['href'] = $href;

		// Build the <a> element.
		$html = '<a';

		foreach ( $attributes as $att => $val ) {
			$html .= ' ' . $att . '="' . esc_attr( $val ) . '"';
		}

		$html .= '>' . esc_html( $title ) . '</a>';

		$this->set( $plugin, $html, false );
	}

	/**
	 * Adds or removes the specified HTML link
	 *
	 * @param string $plugin Path to plugin file.
	 * @param string $html   Full HTML for this link.
	 * @param bool   $remove Whether to add this HTML/link or match and remove it.
	 */
	public function set( $plugin, $html, $remove = false ) {
		$basename = plugin_basename( $plugin );

		// Get any current links for this plugin.
		$cur_links = Tribe__Utils__Array::get( $this->meta_links, $basename, array() );

		$cur_links[] = array(
			'html' => $html,
			'remove' => $remove,
		);

		$this->meta_links = Tribe__Utils__Array::set( $this->meta_links, $basename, $cur_links );
	}

	/**
	 * Filters meta links on the plugins list page
	 *
	 * @param array  $links    The current plugin's links.
	 * @param string $basename The plugin currently being filtered.
	 *
	 * @return array Filtered action links array.
	 */
	public function filter_meta_links( $links, $basename ) {
		// Gets any links that are set for this plugin, defaults to an empty array.
		$set_links = Tribe__Utils__Array::get( $this->meta_links, $basename, array() );

		foreach ( $set_links as $link ) {

			if ( true === $link['remove'] ) {
				// Remove a link.
				$pos = array_search( $link['html'], $links );

				if ( false !== $pos ) {
					unset( $links[ $pos ] );
				}
			} else {
				// Add a link.
				$links[] = $link['html'];
			}
		}

		return $links;
	}

	/**
	 * Prevent cloning the singleton with 'clone' operator
	 *
	 * @return void
	 */
	final private function __clone() {
		_doing_it_wrong(
			__FUNCTION__,
			'Can not use this method on singletons.',
			'4.3'
		);
	}

	/**
	 * Prevent unserializing the singleton instance
	 *
	 * @return void
	 */
	final private function __wakeup() {
		_doing_it_wrong(
			__FUNCTION__,
			'Can not use this method on singletons.',
			'4.3'
		);
	}
}
