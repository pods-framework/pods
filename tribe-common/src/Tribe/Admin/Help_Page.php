<?php

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * Class with a few helpers for the Administration Pages
 *
 * @since  4.0
 *
 */
class Tribe__Admin__Help_Page {
	/**
	 * Static Singleton Factory Method
	 *
	 * @return Tribe__Admin__Help_Page
	 */
	public static function instance() {
		return tribe( static::class );
	}

	/**
	 * Checks if the current page is the Help one
	 *
	 * @since 4.5.7
	 *
	 * @return bool
	 */
	public function is_current_page() {
		return Tribe__Admin__Helpers::instance()->is_screen( 'tribe_events_page_tribe-help' ) || Tribe__Admin__Helpers::instance()->is_screen( 'settings_page_tribe-common-help-network' );
	}

	/**
	 * Register the Admin assets for the help page
	 *
	 * @since  4.9.12
	 *
	 * @return void
	 */
	public function register_assets() {
		$plugin = Tribe__Main::instance();
		tribe_asset(
			$plugin,
			'tribe-admin-help-page',
			'admin/help-page.js',
			[ 'tribe-clipboard', 'tribe-common' ],
			'admin_enqueue_scripts',
			[
				'conditionals' => [ $this, 'is_current_page' ],
				'localize' => [
					'name' => 'tribe_system_info',
					'data' => [
						'sysinfo_optin_nonce'   => wp_create_nonce( 'sysinfo_optin_nonce' ),
						'clipboard_btn_text'    => __( 'Copy to clipboard', 'tribe-common' ),
						'clipboard_copied_text' => __( 'System info copied', 'tribe-common' ),
						'clipboard_fail_text'   => __( 'Press "Cmd + C" to copy', 'tribe-common' ),
					],
				],
			]
		);
	}

	/**
	 * Get the list of plugins
	 *
	 * @since  4.0
	 *
	 * @param  string  $plugin_name    Should get only one plugin?
	 * @param  boolean $is_active Only get active plugins?
	 * @return array
	 */
	public function get_plugins( $plugin_name = null, $is_active = true ) {
		$plugins = [];

		$plugins['the-events-calendar'] = [
			'name'        => 'the-events-calendar',
			'title'       => esc_html__( 'The Events Calendar', 'tribe-common' ),
			'repo'        => 'https://wordpress.org/plugins/the-events-calendar/',
			'forum'       => 'https://wordpress.org/support/plugin/the-events-calendar/',
			'stars_url'   => 'https://wordpress.org/support/plugin/the-events-calendar/reviews/?filter=5',
			'description' => esc_html__(
				'The Events Calendar is a carefully crafted, extensible plugin that lets you easily share your events.',
				'tribe-common'
			),
			'is_active'   => false,
			'version'     => null,
		];

		if ( class_exists( 'Tribe__Events__Main' ) ) {
			$plugins['the-events-calendar']['version'] = Tribe__Events__Main::VERSION;
			$plugins['the-events-calendar']['is_active'] = true;
		}

		$plugins['event-tickets'] = [
			'name'        => 'event-tickets',
			'title'       => esc_html__( 'Event Tickets', 'tribe-common' ),
			'repo'        => 'https://wordpress.org/plugins/event-tickets/',
			'forum'       => 'https://wordpress.org/support/plugin/event-tickets',
			'stars_url'   => 'https://wordpress.org/support/plugin/event-tickets/reviews/?filter=5',
			'description' => esc_html__(
				'Events Tickets is a carefully crafted, extensible plugin that lets you easily sell tickets for your events.',
				'tribe-common'
			),
			'is_active'   => false,
			'version'     => null,
		];

		if ( class_exists( 'Tribe__Tickets__Main' ) ) {
			$plugins['event-tickets']['version'] = Tribe__Tickets__Main::VERSION;
			$plugins['event-tickets']['is_active'] = true;
		}

		$plugins['advanced-post-manager'] = [
			'name'        => 'advanced-post-manager',
			'title'       => esc_html__( 'Advanced Post Manager', 'tribe-common' ),
			'repo'        => 'https://wordpress.org/plugins/advanced-post-manager/',
			'forum'       => 'https://wordpress.org/support/plugin/advanced-post-manager/',
			'stars_url'   => 'https://wordpress.org/support/plugin/advanced-post-manager/reviews/?filter=5',
			'description' => esc_html__(
				'Turbo charge your posts admin for any custom post type with sortable filters and columns, and auto-registration of metaboxes.',
				'tribe-common'
			),
			'is_active'   => false,
			'version'     => null,
		];

		if ( class_exists( 'Tribe_APM' ) ) {
			$plugins['advanced-post-manager']['version'] = 1;
			$plugins['advanced-post-manager']['is_active'] = true;
		}

		$plugins = (array) apply_filters( 'tribe_help_plugins', $plugins );

		// Only active ones?
		if ( true === $is_active ) {
			foreach ( $plugins as $key => $plugin ) {
				if ( true !== $plugin['is_active'] ) {
					unset( $plugins[ $key ] );
				}
			}
		}

		// Do the search
		if ( is_string( $plugin_name ) ) {
			if ( isset( $plugins[ $plugin_name ] ) ) {
				return $plugins[ $plugin_name ];
			} else {
				return false;
			}
		} else {
			return $plugins;
		}
	}

	/**
	 * Get the formatted links of the possible plugins
	 *
	 * @since  4.0
	 *
	 * @param  boolean $is_active Filter only active plugins
	 * @return array
	 */
	public function get_plugin_forum_links( $is_active = true ) {
		$plugins = $this->get_plugins( null, $is_active );

		$list = [];
		foreach ( $plugins as $plugin ) {
			$list[] = '<a href="' . esc_url( $plugin['forum'] ) . '" target="_blank">' . $plugin['title'] . '</a>';
		}

		return $list;
	}

	/**
	 * Get the formatted text of the possible plugins
	 *
	 * @since  4.0
	 *
	 * @param  boolean $is_active Filter only active plugins
	 * @return string
	 */
	public function get_plugins_text( $is_active = true ) {
		$plugins = array_merge( $this->get_plugins( null, $is_active ), $this->get_addons( null, $is_active, true ) );

		$plugins_text = '';
		$i = 0;
		$count = count( $plugins );
		foreach ( $plugins as $plugin ) {
			$i++;
			if ( ! isset( $plugin['is_active'] ) || $plugin['is_active'] !== $is_active ) {
				continue;
			}

			$plugins_text .= $plugin['title'];

			if ( $i === $count - 1 ) {
				$plugins_text .= esc_html__( ' and ', 'tribe-common' );
			} elseif ( $i !== $count ) {
				$plugins_text .= ', ';
			}
		}

		return $plugins_text;
	}

	/**
	 * Get the Addons
	 *
	 * @since  4.0
	 *
	 * @param  string $plugin Plugin Name to filter
	 * @param  string $is_active Filter if it's active
	 * @param  string $is_important filter if the plugin is important
	 * @return array
	 */
	public function get_addons( $plugin = null, $is_active = null, $is_important = null ) {
		$addons = [];

		$addons['events-calendar-pro'] = [
			'id'           => 'events-calendar-pro',
			'title'        => esc_html__( 'Events Calendar PRO', 'tribe-common' ),
			'link'         => 'http://evnt.is/dr',
			'plugin'       => [ 'the-events-calendar' ],
			'is_active'    => class_exists( 'Tribe__Events__Pro__Main' ),
			'is_important' => true,
		];

		$addons['eventbrite-tickets'] = [
			'id'        => 'eventbrite-tickets',
			'title'     => esc_html__( 'Eventbrite Tickets', 'tribe-common' ),
			'link'      => 'http://evnt.is/ds',
			'plugin'    => [ 'the-events-calendar' ],
			'is_active' => class_exists( 'Tribe__Events__Tickets__Eventbrite__Main' ),
		];

		$addons['community-events'] = [
			'id'        => 'community-events',
			'title'     => esc_html__( 'Community Events', 'tribe-common' ),
			'link'      => 'http://evnt.is/dt',
			'plugin'    => [ 'the-events-calendar' ],
			'is_active' => class_exists( 'Tribe__Events__Community__Main' ),
		];

		$addons['event-aggregator'] = [
			'id'        => 'event-aggregator',
			'title'     => esc_html__( 'Event Aggregator', 'tribe-common' ),
			'link'      => 'http://evnt.is/19mk',
			'plugin'    => [ 'the-events-calendar' ],
			'is_active' => class_exists( 'Tribe__Events__Aggregator' ) && tribe(
					'events-aggregator.main'
				)->is_service_active(),
		];

		$addons['events-filter-bar'] = [
			'id'        => 'events-filter-bar',
			'title'     => esc_html__( 'Filter Bar', 'tribe-common' ),
			'link'      => 'http://evnt.is/hu',
			'plugin'    => [ 'the-events-calendar' ],
			'is_active' => class_exists( 'Tribe__Events__Filterbar__View' ),
		];

		$addons['events-virtual'] = [
			'id'        => 'events-virtual',
			'title'     => esc_html__( 'Virtual Events', 'tribe-common' ),
			'link'      => 'http://evnt.is/1alb',
			'plugin'    => [ 'the-events-calendar' ],
			'is_active' => class_exists( '\Tribe\Events\Virtual\Plugin' ),
		];

		$addons['event-tickets-plus'] = [
			'id'           => 'event-tickets-plus',
			'title'        => esc_html__( 'Event Tickets Plus', 'tribe-common' ),
			'link'         => 'http://evnt.is/18wa',
			'plugin'       => [ 'event-tickets' ],
			'is_active'    => class_exists( 'Tribe__Tickets_Plus__Main' ),
			'is_important' => true,
		];

		$addons['event-community-tickets'] = [
			'id'        => 'event-community-tickets',
			'title'     => esc_html__( 'Community Tickets', 'tribe-common' ),
			'link'      => 'http://evnt.is/18m2',
			'plugin'    => [ 'event-tickets' ],
			'is_active' => class_exists( 'Tribe__Events__Community__Tickets__Main' ),
		];

		/**
		 * Filter the array of premium addons upsold on the sidebar of the Settings > Help tab
		 *
		 * @param array $addons
		 */
		$addons = (array) apply_filters( 'tribe_help_addons', $addons );

		// Should I filter something
		if ( is_null( $plugin ) && is_null( $is_active ) && is_null( $is_important ) ) {
			return $addons;
		}

		// Allow for easily grab the addons for a plugin
		$filtered = [];
		foreach ( $addons as $id => $addon ) {
			if ( ! is_null( $plugin ) && ! in_array( $plugin, (array) $addon['plugin'] ) ) {
				continue;
			}

			// Filter by is_active
			if (
				! is_null( $is_active ) &&
				( ! isset( $addon['is_active'] ) || $is_active !== $addon['is_active'] )
			) {
				continue;
			}

			// Filter by is_important
			if (
				! is_null( $is_important ) &&
				( ! isset( $addon['is_important'] ) || $is_important !== $addon['is_important'] )
			) {
				continue;
			}

			$filtered[ $id ] = $addon;
		}

		return $filtered;
	}

	public function is_active( $should_be_active ) {
		$plugins = $this->get_plugins( null, true );
		$addons  = $this->get_addons( null, true );

		$actives   = array_merge( $plugins, $addons );
		$is_active = [];

		foreach ( $actives as $id => $active ) {
			if ( in_array( $id, (array) $should_be_active ) ) {
				$is_active[] = $id;
			}
		}

		return count( array_filter( $is_active ) ) === 0 ? false : true;
	}

	/**
	 * From a Given link returns it with a GA arguments
	 *
	 * @since  4.0
	 *
	 * @param  string  $link     An absolute or a Relative link
	 * @param  boolean $relative Is the Link absolute or relative?
	 * @return string            Link with the GA arguments
	 */
	public function get_ga_link( $link = null, $relative = true ) {
		$query_args = [
			'utm_source'   => 'helptab',
			'utm_medium'   => 'plugin-tec',
			'utm_campaign' => 'in-app',
		];

		if ( true === $relative ) {
			$link = trailingslashit( Tribe__Main::$tec_url . $link );
		}

		return esc_url( add_query_arg( $query_args, $link ) );
	}

	/**
	 * Gets the Feed items from The Events Calendar's Blog
	 *
	 * @since  4.0
	 *
	 * @return array Feed Title and Link
	 */
	public function get_feed_items() {
		$news_rss  = fetch_feed( Tribe__Main::FEED_URL );
		$news_feed = [];

		if ( ! is_wp_error( $news_rss ) ) {
			/**
			 * Filter the maximum number of items returned from the tribe news feed
			 *
			 * @param int $max_items default 5
			 */
			$maxitems  = $news_rss->get_item_quantity( apply_filters( 'tribe_help_rss_max_items', 5 ) );
			$rss_items = $news_rss->get_items( 0, $maxitems );
			if ( $maxitems > 0 ) {
				foreach ( $rss_items as $item ) {
					$item = [
						'title' => esc_html( $item->get_title() ),
						'link'  => esc_url( $item->get_permalink() ),
					];
					$news_feed[] = $item;
				}
			}
		}

		return $news_feed;
	}

	/**
	 * Get the information from the Plugin API data
	 *
	 * @since  4.0
	 *
	 * @param  object $plugin Plugin Object to be used
	 * @return object         An object with the API data
	 */
	private function get_plugin_api_data( $plugin = null ) {
		if ( is_scalar( $plugin ) ) {
			return false;
		}

		$plugin = (object) $plugin;

		/**
		 * Filter the amount of time (seconds) we will keep api data to avoid too many external calls
		 * @var int
		 */
		$timeout = apply_filters( 'tribe_help_api_data_timeout', 3 * HOUR_IN_SECONDS );
		$transient = 'tribe_help_api_data-' . $plugin->name;
		$data = get_transient( $transient );

		if ( false === $data ) {
			if ( ! function_exists( 'plugins_api' ) ) {
				include_once ABSPATH . '/wp-admin/includes/plugin-install.php';
			}

			// Fetch the data
			$data = plugins_api( 'plugin_information', [
					'slug'   => $plugin->name,
					'is_ssl' => is_ssl(),
					'fields' => [
							'banners'         => true,
							'reviews'         => true,
							'downloaded'      => true,
							'active_installs' => true,
					],
			] );

			if ( ! is_wp_error( $data ) ) {
				// Format Downloaded Infomation
				$data->downloaded = $data->downloaded ? number_format( $data->downloaded ) : _x( 'n/a', 'not available', 'tribe-common' );
			} else {
				// If there was a bug on the Current Request just leave
				return false;
			}

			set_transient( $transient, $data, $timeout );
		}
		$data->up_to_date = ( version_compare( $plugin->version, $data->version, '<' ) ) ? esc_html__( 'You need to upgrade!', 'tribe-common' ) : esc_html__( 'You are up to date!', 'tribe-common' );

		/**
		 * Filters the API data that was stored in the Transient option
		 *
		 * @var array
		 * @var object The plugin object, check `$this->get_plugins()` for more info
		 */
		return (object) apply_filters( 'tribe_help_api_data', $data, $plugin );
	}

	/**
	 * Parses the help text from an Array to the final HTML.
	 *
	 * It is the responsibility of code calling this function to ensure proper escaping
	 * within any HTML.
	 *
	 * @since  4.0
	 *
	 * @param  string|array $mixed The mixed value to create the HTML from
	 * @return string
	 */
	public function get_content_html( $mixed = '' ) {
		// If it's an StdObj or String it will be converted
		$mixed = (array) $mixed;

		// Loop to start the HTML
		foreach ( $mixed as &$line ) {
			// If we have content we use that
			if ( isset( $line->content ) ) {
				$line = $line->content;
			}

			if ( is_string( $line ) ) {
				continue;
			} elseif ( is_array( $line ) ) {
				// Allow the developer to pass some configuration
				if ( empty( $line['type'] ) ) {
					$line['type'] = 'ul';
				}

				$text = '<' . $line['type'] . '>' . "\n";
				foreach ( $line as $key => $item ) {
					// Don't add non-numeric items (a.k.a.: configuration)
					if ( ! is_numeric( $key ) ) {
						continue;
					}

					// Only add List Item if is a UL or OL
					if ( in_array( $line['type'], [ 'ul', 'ol' ] ) ) {
						$text .= '<li>' . "\n";
					}

					$text .= $this->get_content_html( $item );

					if ( in_array( $line['type'], [ 'ul', 'ol' ] ) ) {
						$text .= '</li>' . "\n";
					}
				}
				$text .= '</' . $line['type'] . '>' . "\n";

				// Create the list as html instead of array
				$line = $text;
			}
		}

		return wpautop( implode( "\n\n", $mixed ) );
	}

	/**
	 * A Private storage for sections.
	 *
	 * @since 4.0
	 *
	 * @access private
	 * @var array
	 */
	private $sections = [];

	/**
	 * Incremented with each method call, then stored in $section->uid.
	 *
	 * Used when sorting two instances whose priorities are equal.
	 *
	 * @since 4.0
	 *
	 * @static
	 * @access protected
	 * @var int
	 */
	protected static $section_count = 0;

	/**
	 * Helper function to compare two objects by priority, ensuring sort stability via uid.
	 *
	 * @since 4.0
	 *
	 * @access protected
	 * @param object $a Object A.
	 * @param object $b Object B.
	 *
	 * @return int
	 */
	protected function by_priority( $a, $b ) {
		if ( ! isset( $a->priority ) || ! isset( $b->priority ) || $a->priority === $b->priority ) {
			if ( ! isset( $a->unique_call_order ) || ! isset( $b->unique_call_order ) ) {
				return 0;
			} else {
				return $a->unique_call_order > $b->unique_call_order ? 1 : -1;
			}
		} else {
			return $a->priority > $b->priority ? 1 : -1;
		}
	}

	/**
	 * Adds a new section to the Help Page
	 *
	 * @since  4.0
	 *
	 * @param string  $id       HTML like ID
	 * @param string  $title    The Title of the section, doesn't allow HTML
	 * @param integer $priority A Numeric ordering for the Section
	 * @param string  $type     by default only 'default' or 'box'
	 *
	 * @return object The section added
	 */
	public function add_section( $id, $title = null, $priority = 10, $type = 'default' ) {
		if ( empty( $id ) ) {
			return false;
		}

		// Everytime you call this we will add this up
		self::$section_count++;

		$possible_types = (array) apply_filters( 'tribe_help_available_section_types', [ 'default', 'box' ] );

		// Set a Default type
		if ( empty( $type ) || ! in_array( $type, $possible_types ) ) {
			$type = 'default';
		}

		// Create the section and Sanitize the values to avoid having to do it later
		$section = (object) [
				'id'                => sanitize_html_class( $id ),
				'title'             => esc_html( $title ),
				'priority'          => absint( $priority ),
				'type'              => sanitize_html_class( $type ),

				// This Method Unique count integer used for ordering with priority
				'unique_call_order' => self::$section_count,

				// Counter for ordering Content
				'content_count'     => 0,

				// Setup the Base for the content to come
				'content'           => [],
		];

		$this->sections[ $section->id ] = $section;

		return $section;
	}

	/**
	 * Add a New content Item to a Help page Section
	 *
	 * @since  4.0
	 *
	 * @param string  $section_id Which section this content should be assigned to
	 * @param string|array  $content    Item text or array of items, will be passed to `$this->get_content_html`
	 * @param integer $priority   A Numeric priority
	 * @param array   $arguments  If you need arguments for item, they can be passed here
	 *
	 * @return object The content item added
	 */
	public function add_section_content( $section_id, $content, $priority = 10, $arguments = [] ) {
		$section_id = sanitize_html_class( $section_id );

		// Check if the section exists
		if ( empty( $this->sections[ $section_id ] ) ) {
			return false;
		}

		// Make sure we have arguments as Array
		if ( ! is_array( $arguments ) ) {
			return false;
		}

		$section = &$this->sections[ $section_id ];

		// Increment the content counter
		$section->content_count++;

		$item = (object) $arguments;

		// Set the priority
		$item->priority = absint( $priority );

		// Set the uid to help ordering
		$item->unique_call_order = $section->content_count;

		// Content is not Safe, will be Sanitized on Output
		$item->content = $content;

		$section->content[] = $item;

		return $item;
	}

	/**
	 * Remove a section based on the ID
	 * This method will remove any sections that are indexed at that ID on the sections array
	 * And the sections that have a propriety of `id` equals to the given $section_id argument
	 *
	 * @param  string|int $section_id You can use Numeric or String indexes to search
	 * @return bool|int               Returns `false` when no sections were removed and an `int` with the number of sections removed
	 */
	public function remove_section( $section_id ) {
		if (
			! isset( $this->sections[ $section_id ] ) &&
			! in_array( (object) [ 'id' => $section_id ], $this->sections, true )
		) {
			// There are no sections to remove, so false
			return false;
		}

		$removed = [];
		foreach ( $this->sections as $id => $section ) {
			if ( ! is_numeric( $id ) && ! is_numeric( $section_id ) && ! empty( $section->id ) ) {
				if ( $section->id === $section_id ) {
					unset( $this->sections[ $id ] );
					// Mark that this section was removed
					$removed[ $id ] = true;
				}
			} elseif ( $id === $section_id ) {
				unset( $this->sections[ $section_id ] );
				// Mark that this section was removed
				$removed[ $id ] = true;
			} else {
				// Mark that this section was NOT removed
				$removed[ $id ] = false;
			}
		}

		// Count how many were removed
		$total = count( array_filter( $removed ) );

		// if Zero just return false
		return $total === 0 ? false : $total;
	}

	/**
	 * Based on an Array of sections it render the Help Page contents
	 *
	 * @since  4.0
	 *
	 * @param  boolean $print    Return or Print the HTML after
	 * @return void|string
	 */
	public function get_sections( $print = true ) {
		/**
		 * Allow third-party sections here
		 *
		 * @var Tribe__Admin__Help_Page
		 */
		do_action( 'tribe_help_pre_get_sections', $this );

		/**
		 * Allow developers to filter all the sections at once
		 * NOTE: You should be using `tribe_help_add_sections` to add new sections or content
		 *
		 * @var array
		 */
		$sections = apply_filters( 'tribe_help_sections', $this->sections );

		if ( ! is_array( $sections ) || empty( $sections ) ) {
			return false;
		}

		// Sort by Priority
		uasort( $sections, [ $this, 'by_priority' ] );

		$html = [];

		foreach ( $sections as $index => $section ) {
			$section = (object) $section;

			// If it has no ID or Content, skip
			if ( empty( $section->id ) || empty( $section->content ) ) {
				continue;
			}

			// Set a Default type
			if ( empty( $section->type ) ) {
				$section->type = 'default';
			}

			/**
			 * Creates a way to filter a specific section based on the ID
			 *
			 * @var object
			 */
			$section = apply_filters( 'tribe_help_section_' . $section->id, $section, $this );

			// Sort by Priority
			uasort( $section->content, [ $this, 'by_priority' ] );

			$html[ $section->id . '-start' ] = '<div id="tribe-' . sanitize_html_class( $section->id ) . '" class="tribe-help-section clearfix tribe-section-type-' . sanitize_html_class( $section->type ) . '">';

			if ( ! empty( $section->title ) ) {
				$html[ $section->id . '-title' ] = '<h3 class="tribe-help-title">' . esc_html__( $section->title ) . '</h3>';
			}

			$html[ $section->id . '-content' ] = $this->get_content_html( $section->content );

			$html[ $section->id . '-end' ] = '</div>';
		}

		/**
		 * Creates a way for developers to hook to the final HTML
		 * @var array $html
		 * @var array $sections
		 */
		$html = apply_filters( 'tribe_help_sections_html', $html, $sections );

		if ( true === $print ) {
			echo implode( "\n", $html );
		} else {
			return $html;
		}

	}

	/**
	 * Prints the Plugin box for the given plugin
	 *
	 * @since  4.0
	 *
	 * @param  string $plugin Plugin Name key
	 * @return void
	 */
	public function print_plugin_box( $plugin ) {
		$plugin = (object) $this->get_plugins( $plugin, false );
		$api_data = $this->get_plugin_api_data( $plugin );
		$addons = $this->get_addons( $plugin->name );
		$plugins = get_plugins();

		if ( $api_data ) {
			if ( ! function_exists( 'install_plugin_install_status' ) ) {
				include_once ABSPATH . '/wp-admin/includes/plugin-install.php';
			}
			$status = install_plugin_install_status( $api_data );
			$plugin_active = is_plugin_active( $status['file'] );
			$plugin_exists = isset( $plugins[ $status['file'] ] );

			if ( 'install' !== $status['status'] && ! $plugin_active ) {
				$args = [
					'action'        => 'activate',
					'plugin'        => $status['file'],
					'plugin_status' => 'all',
					'paged'         => 1,
					's'             => '',
				];
				$activate_url = wp_nonce_url( add_query_arg( $args, 'plugins.php' ), 'activate-plugin_' . $status['file'] );
				$link = '<a class="button" href="' . $activate_url . '" aria-label="' . esc_attr( sprintf( esc_attr__( 'Activate %s', 'tribe-common' ), $plugin->name ) ) . '">' . esc_html__( 'Activate Plugin', 'tribe-common' ) . '</a>';
			} elseif ( 'update_available' === $status['status'] ) {
				$args = [
					'action' => 'upgrade-plugin',
					'plugin' => $status['file'],
				];
				$update_url = wp_nonce_url( add_query_arg( $args, 'update.php' ), 'upgrade-plugin_' . $status['file'] );

				$link = '<a class="button" href="' . $update_url . '">' . esc_html__( 'Upgrade Plugin', 'tribe-common' ) . '</a>';
			} elseif ( $plugin_exists ) {
				$link = '<a class="button disabled">' . esc_html__( 'You are up to date!', 'tribe-common' ) . '</a>';
			}
		}

		if ( ! isset( $link ) ) {
			if ( $api_data ) {
				$args = [
					'tab'       => 'plugin-information',
					'plugin'    => $plugin->name,
					'TB_iframe' => true,
					'width'     => 772,
					'height'    => 600,
				];
				$iframe_url = add_query_arg( $args, admin_url( '/plugin-install.php' ) );
				$link = '<a class="button thickbox" href="' . $iframe_url . '" aria-label="' . esc_attr( sprintf( esc_attr__( 'Install %s', 'tribe-common' ), $plugin->name ) ) . '">' . esc_html__( 'Install Plugin', 'tribe-common' ) . '</a>';
			} else {
				$link = null;
			}
		}
		?>
		<div class="tribe-help-plugin-info">
			<h3><a href="<?php echo esc_url( $plugin->repo ); ?>" target="_blank"><?php echo esc_html( $plugin->title ); ?></a></h3>

			<?php
			if ( ! empty( $plugin->description ) && ! $plugin->is_active ) {
				echo wpautop( $plugin->description );
			}
			?>

			<?php if ( $api_data ) { ?>
				<dl>
					<dt><?php esc_html_e( 'Latest Version:', 'tribe-common' ); ?></dt>
					<dd><?php echo esc_html( $api_data->version ); ?></dd>

					<dt><?php esc_html_e( 'Requires:', 'tribe-common' ); ?></dt>
					<dd><?php echo esc_html__( 'WordPress ', 'tribe-common' ) . esc_html( $api_data->requires ); ?>+</dd>

					<dt><?php esc_html_e( 'Active Users:', 'tribe-common' ); ?></dt>
					<dd><?php echo esc_html( number_format( $api_data->active_installs ) ); ?>+</dd>

					<dt><?php esc_html_e( 'Rating:', 'tribe-common' ); ?></dt>
					<dd>
						<a href="<?php echo esc_url( $plugin->stars_url ); ?>" target="_blank">
							<?php
							wp_star_rating( [
									'rating' => $api_data->rating,
									'type'   => 'percent',
									'number' => $api_data->num_ratings,
							] );
							?>
						</a>
					</dd>
				</dl>
			<?php } ?>

			<?php
			// Only show the link to the users can use it
			if ( current_user_can( 'update_plugins' ) && current_user_can( 'install_plugins' ) ) {
				echo $link ? '<p style="text-align: center;">' . $link . '</p>' : '';
			}
			?>

			<?php if ( ! empty( $addons ) ) { ?>
				<h3><?php esc_html_e( 'Premium Add-Ons', 'tribe-common' ); ?></h3>
				<ul class='tribe-list-addons'>
					<?php foreach ( $addons as $addon ) {
						$addon = (object) $addon;

						if ( isset( $addon->is_active ) && $addon->is_active ) {
							$active_title = __( 'Plugin Active', 'tribe-common' );
						} else {
							$active_title = __( 'Plugin Inactive', 'tribe-common' );
						}

						echo '<li title="' . esc_attr( $active_title ) . '" class="' . ( isset( $addon->is_active ) && $addon->is_active ? 'tribe-active-addon' : '' ) . '">';
						if ( isset( $addon->link ) ) {
							echo '<a href="' . esc_url( $addon->link ) . '" title="' . esc_attr__( 'Visit the Add-on Page', 'tribe-common' ) . '" target="_blank">';
						}
						echo esc_html( $addon->title );
						if ( isset( $addon->link ) ) {
							echo '</a>';
						}
						echo '</li>';
					} ?>
				</ul>
			<?php } ?>
		</div>
		<?php
	}
}
