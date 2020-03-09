<?php
// Don't load directly
defined( 'WPINC' ) or die;

if ( ! class_exists( 'Tribe__Plugins' ) ) {
	/**
	 * A list of Tribe's major plugins. Useful when encouraging users to download one of these.
	 */
	class Tribe__Plugins {

		/**
		 * A list of tribe plugin's details in this array format:
		 *
		 * [
		 *  'short_name'   => Common name for the plugin, used in places such as WP Admin messages
		 *  'class'        => Main plugin class
		 *  'thickbox_url' => Download or purchase URL for plugin from within /wp-admin/ thickbox
		 * ]
		 */
		private $tribe_plugins = [
			[
				'short_name'   => 'Event Tickets',
				'class'        => 'Tribe__Tickets__Main',
				'thickbox_url' => 'plugin-install.php?tab=plugin-information&plugin=event-tickets&TB_iframe=true',
			],
			[
				'short_name'   => 'Event Tickets Plus',
				'class'        => 'Tribe__Tickets_Plus__Main',
				'thickbox_url' => 'https://theeventscalendar.com/product/wordpress-event-tickets-plus/?TB_iframe=true',
			],
			[
				'short_name'   => 'The Events Calendar',
				'class'        => 'Tribe__Events__Main',
				'thickbox_url' => 'plugin-install.php?tab=plugin-information&plugin=the-events-calendar&TB_iframe=true',
			],
			[
				'short_name'   => 'Events Calendar Pro',
				'class'        => 'Tribe__Events__Pro__Main',
				'thickbox_url' => 'https://theeventscalendar.com/product/wordpress-events-calendar-pro/?TB_iframe=true',
			],
			[
				'short_name'   => 'Community Events',
				'class'        => 'Tribe__Events__Community__Main',
				'thickbox_url' => 'https://theeventscalendar.com/product/wordpress-community-events/?TB_iframe=true',
			],
			[
				'short_name'   => 'Community Tickets',
				'class'        => 'Tribe__Events__Community__Tickets__Main',
				'thickbox_url' => 'https://theeventscalendar.com/product/community-tickets/?TB_iframe=true',
			],
			[
				'short_name'   => 'Filter Bar',
				'class'        => 'Tribe__Events__Filterbar__View',
				'thickbox_url' => 'https://theeventscalendar.com/product/wordpress-events-filterbar/?TB_iframe=true',
			],
			[
				'short_name'   => 'Facebook Events',
				'class'        => 'Tribe__Events__Facebook__Importer',
				'thickbox_url' => 'https://theeventscalendar.com/product/facebook-events/?TB_iframe=true',
			],
			[
				'short_name'   => 'iCal Importer',
				'class'        => 'Tribe__Events__Ical_Importer__Main',
				'thickbox_url' => 'https://theeventscalendar.com/product/ical-importer/?TB_iframe=true',
			],
			[
				'short_name'   => 'Eventbrite Tickets',
				'class'        => 'Tribe__Events__Tickets__Eventbrite__Main',
				'thickbox_url' => 'https://theeventscalendar.com/product/wordpress-eventbrite-tickets/?TB_iframe=true',
			],
			[
				'short_name'   => 'Advanced Post Manager',
				'class'        => 'Tribe_APM',
				'thickbox_url' => 'plugin-install.php?tab=plugin-information&plugin=advanced-post-manager&TB_iframe=true',
			],
		];

		/**
		 * Searches the plugin list for key/value pair and return the full details for that plugin
		 *
		 * @param string $search_key The array key this value will appear in
		 * @param string $search_val The value itself
		 *
		 * @return array|null
		 */
		public function get_plugin_by_key( $search_key, $search_val ) {
			foreach ( $this->get_list() as $plugin ) {
				if ( isset( $plugin[ $search_key ] ) && $plugin[ $search_key ] === $search_val ) {
					return $plugin;
				}
			}

			return null;
		}

		/**
		 * Retrieves plugins details by plugin name
		 *
		 * @param string $name Common name for the plugin, not necessarily the lengthy name in the WP Admin Plugins list
		 *
		 * @return array|null
		 */
		public function get_plugin_by_name( $name ) {
			return $this->get_plugin_by_key( 'short_name', $name );
		}

		/**
		 * Retrieves plugins details by class name
		 *
		 * @param string $main_class Main/base class for this plugin
		 *
		 * @return array|null
		 */
		public function get_plugin_by_class( $main_class ) {
			return $this->get_plugin_by_key( 'class', $main_class );
		}

		/**
		 * Retrieves the entire list
		 *
		 * @return array
		 */
		public function get_list() {
			/**
			 * Gives an opportunity to filter the list of tribe plugins
			 *
			 * @since 4.7.18
			 *
			 * @param array Contains a list of all tribe plugins
			 */
			return apply_filters( 'tribe_plugins_get_list', $this->tribe_plugins );
		}

	}
}
