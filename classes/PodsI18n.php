<?php 
/**
 * @package Pods
 * @since 2.7
 */
class PodsI18n {

	/**
	 * @var PodsI18n
	 */
	static $instance = null;

	/**
	 * The strings
	 */
	public $strings = array();

	/**
	 * The localized strings
	 */
	public $localized = array();

	/**
	 * Singleton handling for a basic pods_i18n() request
	 *
	 * @return \PodsI18n
	 *
	 * @since 2.7
	 */
	public static function init() {

		if ( ! is_object( self::$instance ) ) {
			self::$instance = new PodsI18n();
		}

		return self::$instance;
	}

	/**
	 * Constructur
	 */
	public function __construct() {
		//self::init();
		$this->localize_assets();
	}

	/**
	 * Localize assets
	 * 
	 * @since 2.7
	 */
	public function localize_assets() {
		// Create existing strings of this class
		$this->strings = $this->create_strings();

		/**
		 * Add strings to the localization
		 * Setting the key of your string to the original (non translated) value is mandatory
		 * Note: Existing keys in this class will overwrite the ones of this filter!
		 * 
		 * @since 2.7
		 * @see create_strings()
		 * @param array
		 * @return array format: 'Untranslated string' => 'Translated string with use of WP translate functions'
		 */
		$strings_extra = apply_filters( 'pods_localized_strings', array() );

		$this->strings = array_merge( $strings_extra, $this->strings );

		foreach ( $this->strings as $key => $str ) {
			$this->register( $key, $str );
		}

		// Register our i18n script for JS
		wp_register_script( 'pods-i18n', PODS_URL . 'ui/js/pods-i18n.js', array(), PODS_VERSION, true );

		// Some other stuff we need to pass through
		$localize = array(
			'debug' => ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG == true ) ? true : false,
		);
		// Add localization to our i18n script
		wp_localize_script( 'pods-i18n', 'pods_localized_strings', array_merge( $this->strings, $localize ) );
	}

	/**
	 * Register function that creates the references and combines these with the translated strings
	 * 
	 * @since 2.7
	 */
	public function register( $string_key, $translation ) {
		/**
		 * Converts string into reference object variable
		 * Uses the same logic as PHP to create the same references
		 * 
		 * 1. Remove capitals
		 * 2. Remove all punctuation etc.
		 * 3. Trim
		 * 4. Convert whitespaces to underscores
		 */
		$ref = strtolower( $string_key );
		$ref = preg_replace( '/[^a-z]+/i', ' ', $ref );
		$ref = trim( $ref );
		$ref = preg_replace( '/\s+/', '_', $ref );

		// Add it to the strings localized
		$this->strings[ '__' . $ref ] = $translation;
		// Remove the old key
		unset( $this->strings[ $string_key ] );
	}

	/**
	 * Register our labels to use in JS
	 * We need to register them as normal string to convert to JS references
	 * And we need to register the translations to attach to these references, these may not be variables!
	 * 
	 * @since 2.7
	 */
	public function create_strings() {

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
