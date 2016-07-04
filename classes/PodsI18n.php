<?php

/**
 * @package Pods
 * @since   2.7
 */
class PodsI18n {

	/**
	 * @var PodsI18n Singleton instance
	 */
	private static $instance = null;

	/**
	 * @var array Key/value pairs with label/translation
	 */
	private static $strings = array();

	/**
	 * @return PodsI18n
	 *
	 * @since 2.7
	 */
	public static function init() {

		if ( ! is_object( self::$instance ) ) {
			self::$instance = new PodsI18n();
			self::localize_assets();
		}

		return self::$instance;
	}

	/**
	 * Singleton handling for a basic pods_i18n() request
	 *
	 * @return \PodsI18n
	 *
	 * @since 2.7
	 */
	public static function get_instance() {

		// Initialize if the class hasn't been setup yet for some reason
		if ( ! is_object( self::$instance ) ) {
			self::init();
		}

		return self::$instance;
	}

	/**
	 * Localize assets:
	 *     * Build localizations strings from the defaults and those provided via filter
	 *     * Register the script that contains the JavaScript localization object
	 *     * Provide access to a global JavaScript object with the assembled localization strings
	 *
	 * @since 2.7
	 */
	private static function localize_assets() {

		// Create existing strings of this class
		self::$strings = self::default_strings();

		/**
		 * Add strings to the localization
		 * Setting the key of your string to the original (non translated) value is mandatory
		 * Note: Existing keys in this class will overwrite the ones of this filter!
		 *
		 * @since 2.7
		 * @see   default_strings()
		 *
		 * @param array
		 *
		 * @return array format: 'Untranslated string' => 'Translated string with use of WP translate functions'
		 */
		$strings_extra = apply_filters( 'pods_localized_strings', array() );

		self::$strings = array_merge( $strings_extra, self::$strings );

		foreach ( self::$strings as $key => $str ) {
			self::register( $key, $str );
		}

		// Register our i18n script for JS
		wp_register_script( 'pods-i18n', PODS_URL . 'ui/js/pods-i18n.js', array(), PODS_VERSION, true );

		// Some other stuff we need to pass through
		$localize = array(
			'debug' => ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG == true ) ? true : false,
		);
		// Add localization to our i18n script
		wp_localize_script( 'pods-i18n', 'podsLocalizedStrings', array_merge( self::$strings, $localize ) );
	}

	/**
	 * Register function that creates the references and combines these with the translated strings
	 *
	 * @param string $string_key
	 * @param string $translation
	 *
	 * @since 2.7
	 */
	private function register( $string_key, $translation ) {

		/**
		 * Converts string into reference object variable
		 * Uses the same logic as JS to create the same references
		 */
		$ref = '__' . $string_key;

		// Add it to the strings localized
		self::$strings[ $ref ] = $translation;

		// Remove the old key
		unset( self::$strings[ $string_key ] );
	}

	/**
	 * Register our labels to use in JS
	 * We need to register them as normal string to convert to JS references
	 * And we need to register the translations to attach to these references, these may not be variables!
	 *
	 * @return array Key/value pairs with label/translation
	 *
	 * @since 2.7
	 */
	private function default_strings() {

		return array(

			'%s is required.' =>
				__( '%s is required.', 'pods' ),

			'This field is required.' =>
				__( 'This field is required.', 'pods' ),

			'Add' =>
				__( 'Add', 'pods' ),

			'Add New' =>
				__( 'Add New', 'pods' ),

			'Add New Record' =>
				__( 'Add New Record', 'pods' ),

			'Added!' =>
				__( 'Added!', 'pods' ),

			'Added! Choose another or <a href="#">close this box</a>' =>
				__( 'Added! Choose another or <a href="#">close this box</a>', 'pods' ),

			'Copy' =>
				__( 'Copy', 'pods' ),

			'Reorder' =>
				__( 'Reorder', 'pods' ),

			'Remove' =>
				__( 'Remove', 'pods' ),

			'Download' =>
				__( 'Download', 'pods' ),

			'View' =>
				__( 'View', 'pods' ),

			'Edit' =>
				__( 'Edit', 'pods' ),

			'Navigating away from this page will discard any changes you have made.' =>
				__( 'Navigating away from this page will discard any changes you have made.', 'pods' ),

			'Unable to process request, please try again.' =>
				__( 'Unable to process request, please try again.', 'pods' ),

			'There was an issue with the file upload, please try again.' =>
				__( 'There was an issue with the file upload, please try again.', 'pods' ),

			'Allowed Files' =>
				__( 'Allowed Files', 'pods' ),

			'The Title' =>
				__( 'The Title', 'pods' ),

		);

	}

}
