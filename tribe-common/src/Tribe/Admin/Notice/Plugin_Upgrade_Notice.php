<?php
/**
 * When appropriate, displays a plugin upgrade message "inline" within the plugin
 * admin screen.
 *
 * This is drawn from the Upgrade Notice section of the plugin readme.txt file (ie,
 * the one belonging to the current stable accessible via WP SVN - at least by
 * default).
 */
class Tribe__Admin__Notice__Plugin_Upgrade_Notice {
	/**
	 * Currently installed version of the plugin
	 *
	 * @var string
	 */
	protected $current_version = '';

	/**
	 * The plugin path as it is within the plugins directory, ie
	 * "some-plugin/main-file.php".
	 *
	 * @var string
	 */
	protected $plugin_path = '';

	/**
	 * Contains the plugin upgrade notice (empty if none are available).
	 *
	 * @var string
	 */
	protected $upgrade_notice = '';


	/**
	 * Test for and display any plugin upgrade messages (if any are available) inline
	 * beside the plugin listing itself.
	 *
	 * The optional third param is the object which actually checks to see if there
	 * are any upgrade notices worth displaying. If not provided, an object of the
	 * default type will be created (which connects to WP SVN).
	 *
	 * @param string $current_version
	 * @param string $plugin_path (ie "plugin-dir/main-file.php")
	 */
	public function __construct( $current_version, $plugin_path ) {
		$this->current_version = $current_version;
		$this->plugin_path     = $plugin_path;

		add_action( "in_plugin_update_message-$plugin_path", [ $this, 'maybe_run' ] );
	}

	/**
	 * Test if there is a plugin upgrade notice and displays it if so.
	 *
	 * Expects to fire during "in_plugin_update_message-{plugin_path}", therefore
	 * this should only run if WordPress has detected that an upgrade is indeed
	 * available.
	 */
	public function maybe_run() {
		$this->test_for_upgrade_notice();

		if ( $this->upgrade_notice ) {
			$this->display_message();
		}
	}

	/**
	 * Tests to see if an upgrade notice is available.
	 */
	protected function test_for_upgrade_notice() {
		$cache_key = $this->cache_key();
		$this->upgrade_notice = get_transient( $cache_key );

		if ( false === $this->upgrade_notice ) {
			$this->discover_upgrade_notice();
		}

		set_transient( $cache_key, $this->upgrade_notice, $this->cache_expiration() );
	}

	/**
	 * Returns a cache key unique to the current plugin path and version, that
	 * still fits within the 45-char limit of regular WP transient keys.
	 *
	 * @return string
	 */
	protected function cache_key() {
		return 'tribe_plugin_upgrade_notice-' . hash( 'crc32b', $this->plugin_path . $this->current_version );
	}

	/**
	 * Returns the period of time (in seconds) for which to cache plugin upgrade messages.
	 *
	 * @return int
	 */
	protected function cache_expiration() {
		/**
		 * Number of seconds to cache plugin upgrade messages for.
		 *
		 * Defaults to one day, which provides a decent balance between efficiency savings
		 * and allowing for the possibility that some upgrade messages may be changed or
		 * rescinded.
		 *
		 * @var int $cache_expiration
		 */
		return (int) apply_filters( 'tribe_plugin_upgrade_notice_expiration', DAY_IN_SECONDS, $this->plugin_path );
	}

	/**
	 * Looks at the current stable plugin readme.txt and parses to try and find the first
	 * available upgrade notice relating to a plugin version higher than this one.
	 *
	 * By default, WP SVN is the source.
	 */
	protected function discover_upgrade_notice() {
		/**
		 * The URL for the current plugin readme.txt file.
		 *
		 * @var string $url
		 * @var string $plugin_path
		 */
		$readme_url = apply_filters( 'tribe_plugin_upgrade_readme_url',
			$this->form_wp_svn_readme_url(),
			$this->plugin_path
		);

		if ( ! empty( $readme_url ) ) {
			$response = wp_safe_remote_get( $readme_url );
		}

		if ( ! empty( $response ) && ! is_wp_error( $response ) ) {
			$readme = $response['body'];
		}

		if ( ! empty( $readme ) ) {
			$this->parse_for_upgrade_notice( $readme );
			$this->format_upgrade_notice();
		}

		/**
		 * The upgrade notice for the current plugin (may be empty).
		 *
		 * @var string $upgrade_notice
		 * @var string $plugin_path
		 */
		return apply_filters( 'tribe_plugin_upgrade_notice',
			$this->upgrade_notice,
			$this->plugin_path
		);
	}

	/**
	 * Forms the expected URL to the trunk readme.txt file as it is on WP SVN
	 * or an empty string if for any reason it cannot be determined.
	 *
	 * @return string
	 */
	protected function form_wp_svn_readme_url() {
		$parts = explode( '/', $this->plugin_path );
		$slug = empty( $parts[0] ) ? '' : $parts[0];
		return esc_url( "https://plugins.svn.wordpress.org/$slug/trunk/readme.txt" );
	}

	/**
	 * Given a standard Markdown-format WP readme.txt file, finds the first upgrade
	 * notice (if any) for a version higher than $this->current_version.
	 *
	 * @param  string $readme
	 * @return string
	 */
	protected function parse_for_upgrade_notice( $readme ) {
		$in_upgrade_notice = false;
		$in_version_notice = false;
		$readme_lines      = explode( "\n", $readme );

		foreach ( $readme_lines as $line ) {
			// Once we leave the Upgrade Notice section (ie, we encounter a new section header), bail
			if ( $in_upgrade_notice && 0 === strpos( $line, '==' ) ) {
				break;
			}

			// Look out for the start of the Upgrade Notice section
			if ( ! $in_upgrade_notice && preg_match( '/^==\s*Upgrade\s+Notice\s*==/i', $line ) ) {
				$in_upgrade_notice = true;
			}

			// Also test to see if we have left the version specific note (ie, we encounter a new sub heading or header)
			if ( $in_upgrade_notice && $in_version_notice && 0 === strpos( $line, '=' ) ) {
				break;
			}

			// Look out for the first applicable version-specific note within the Upgrade Notice section
			if ( $in_upgrade_notice && ! $in_version_notice && preg_match( '/^=\s*\[?([0-9\.]{3,})\]?\s*=/', $line, $matches ) ) {
				// Is this a higher version than currently installed?
				if ( version_compare( $matches[1], $this->current_version, '>' ) ) {
					$in_version_notice = true;
				}
			}

			// Copy the details of the upgrade notice for the first higher version we find
			if ( $in_upgrade_notice && $in_version_notice ) {
				$this->upgrade_notice .= $line . "\n";
			}
		}
	}

	/**
	 * Convert the plugin version header and any links from Markdown to HTML.
	 */
	protected function format_upgrade_notice() {
		// Convert [links](http://...) to <a href="..."> tags
		$this->upgrade_notice = preg_replace(
			'/\[([^\]]*)\]\(([^\)]*)\)/',
			'<a href="${2}">${1}</a>',
			$this->upgrade_notice
		);

		// Convert =4.0= headings to <h4 class="version">4.0</h4> tags
		$this->upgrade_notice = preg_replace(
			'/=\s*([a-zA-Z0-9\.]{3,})\s*=/',
			'<h4 class="version">${1}</h4>',
			$this->upgrade_notice
		);
	}

	/**
	 * Render the actual upgrade notice.
	 *
	 * Please note if plugin-specific styling is required for the message, you can
	 * use an ID generated by WordPress for one of the message's parent elements
	 * which takes the form "{plugin_name}-update". Example:
	 *
	 *     #the-events-calendar-update .tribe-plugin-update-message { ... }
	 */
	public function display_message() {
		$notice = wp_kses_post( $this->upgrade_notice );
		echo "<div class='tribe-plugin-update-message'> $notice </div>";
	}
}
