<?php
/**
 * Assembles a report of recently updated plugin views and template overrides in
 * possible revision, for each plugin that registers itself and its template
 * filepaths.
 */
class Tribe__Support__Template_Checker_Report {
	const VERSION_INDEX         = 0;
	const INCLUDED_VIEWS_INDEX  = 1;
	const THEME_OVERRIDES_INDEX = 2;

	/**
	 * Contains the individual view/template reports for each registered plugin.
	 *
	 * @var array
	 */
	protected static $plugin_reports = array();

	/**
	 * Container for finished report.
	 *
	 * @var string
	 */
	protected static $complete_report = '';

	/**
	 * Provides an up-to-date report concerning template changes.
	 *
	 * @return string
	 */
	public static function generate() {
		foreach ( self::registered_plugins() as $plugin_name => $plugin_template_system ) {
			self::generate_for( $plugin_name, $plugin_template_system );
		}

		self::wrap_report();
		return self::$complete_report;
	}

	protected static function registered_plugins() {
		/**
		 * Provides a mechanism for plugins to register information about their template/view
		 * setups.
		 *
		 * This should be done by adding an entry to $registere_template_systems where the key
		 * should be the plugin name and the element an array structured as follows:
		 *
		 *     [
		 *       plugin_version,
		 *       path_to_included_views,
		 *       path_to_theme_overrides
		 *     ]
		 *
		 * @var array $registered_template_systems
		 */
		return apply_filters( 'tribe_support_registered_template_systems', array() );
	}

	/**
	 * Creates a report for the specified plugin.
	 *
	 * @param string $plugin_name
	 * @param array  $template_system
	 */
	protected static function generate_for( $plugin_name, array $template_system ) {
		$report = '<dt>' . esc_html( $plugin_name ) . '</dt>';

		$scanner = new Tribe__Support__Template_Checker(
			$template_system[ self::VERSION_INDEX ],
			$template_system[ self::INCLUDED_VIEWS_INDEX ],
			$template_system[ self::THEME_OVERRIDES_INDEX ]
		);

		$newly_introduced_or_updated = $scanner->get_views_tagged_this_release();
		$outdated_or_unknown = $scanner->get_outdated_overrides( true );

		if ( empty( $newly_introduced_or_updated ) && empty( $outdated_or_unknown ) ) {
			$report .= '<dd>' . __( 'No notable changes detected', 'tribe-common' ) . '</dd>';
		}

		if ( ! empty( $newly_introduced_or_updated ) ) {
			$report .= '<dd><p>' . sprintf( __( 'Templates introduced or updated with this release (%s):', 'tribe-common' ), $template_system[ self::VERSION_INDEX ] ) . '</p><ul>';

			foreach ( $newly_introduced_or_updated as $view_name => $version ) {
				$report .= '<li>' . esc_html( $view_name ) . '</li>';
			}

			$report .= '</ul></dd>';
		}

		if ( ! empty( $outdated_or_unknown ) ) {
			$report .= '<dd><p>' . __( 'Existing theme overrides that may need revision:', 'tribe-common' ) . '</p><ul>';

			foreach ( $outdated_or_unknown as $view_name => $version ) {
				$version_note = empty( $version )
					? __( 'version data missing from override', 'tribe-common' )
					: sprintf( __( 'based on %s version', 'tribe-common' ), $version );

				$report .= '<li>' . esc_html( $view_name ) . ' (' . $version_note . ') </li>';
			}

			$report .= '</ul></dd>';
		}

		self::$plugin_reports[ $plugin_name ] = $report;
	}

	/**
	 * Wraps the individual plugin template reports ready for display.
	 */
	protected static function wrap_report() {
		if ( empty( self::$plugin_reports ) ) {
			self::$complete_report = '<p>' . __( 'No notable template changes detected.', 'tribe-common' ) . '</p>';
		} else {
			self::$complete_report = '<p>' . __( 'Information about recent template changes and potentially impacted template overrides is provided below.', 'tribe-common' ) . '</p>'
				. '<div class="template-updates-wrapper">' . join( ' ', self::$plugin_reports ) . '</div>';
		}
	}
}
