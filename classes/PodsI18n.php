<?php

/**
 * @package Pods
 * @since 2.7.0
 */
final class PodsI18n {

	/**
	 * @var PodsI18n Singleton instance
	 */
	private static $instance = null;

	/**
	 * @var array Key/value pairs with label/translation
	 */
	private static $strings = array();

	/**
	 * @var mixed Current language locale
	 */
	private static $current_language = null;

	/**
	 * @var mixed Current language data
	 */
	private static $current_language_context = null;

	/**
	 * Singleton handling for a basic pods_i18n() request
	 *
	 * @since 2.7.0
	 */
	private function __construct() {

		self::$instance = $this;

		// Hook all enqueue scripts actions
		add_action( 'pods_before_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Singleton handling for a basic pods_i18n() request
	 *
	 * @return \PodsI18n
	 *
	 * @since 2.7.0
	 */
	public static function get_instance() {

		// Initialize if the class hasn't been setup yet for some reason
		if ( ! is_object( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * @since 2.7.0
	 */
	public function enqueue_scripts() {

		// Register our i18n script for JS
		wp_register_script( 'sprintf', PODS_URL . 'ui/js/sprintf/sprintf.min.js', array(), '1.1.0', true );
		wp_register_script( 'pods-i18n', PODS_URL . 'ui/js/pods-i18n.js', array( 'sprintf' ), PODS_VERSION, true );

		self::localize_assets();
	}

	/**
	 * Localize assets:
	 *     * Build localizations strings from the defaults and those provided via filter
	 *     * Provide a global JavaScript object with the assembled localization strings via `wp_localize_script`
	 *
	 * @since 2.7.0
	 */
	private static function localize_assets() {

		/**
		 * Add strings to the localization
		 * Setting the key of your string to the original (non translated) value is mandatory
		 * Note: Existing keys in this class will overwrite the ones of this filter!
		 *
		 * @since 2.7.0
		 * @see   default_strings()
		 *
		 * @param array
		 *
		 * @return array format: 'Untranslated string' => 'Translated string with use of WP translate functions'
		 */
		$strings_extra = apply_filters( 'pods_localized_strings', array() );

		self::$strings = array_merge( $strings_extra, self::default_strings() );

		foreach ( self::$strings as $key => $str ) {
			self::register( $key, $str );
		}

		// Some other stuff we need to pass through.
		$i18n_base = array(
			'debug' => ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG == true ) ? true : false,
		);
		// Add localization to our i18n script
		wp_localize_script( 'pods-i18n', 'podsLocalizedStrings', array_merge( self::$strings, $i18n_base ) );
	}

	/**
	 * Register function that creates the references and combines these with the translated strings
	 *
	 * @param string $string_key
	 * @param string $translation
	 *
	 * @since 2.7.0
	 */
	private static function register( $string_key, $translation ) {

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
	 * @since 2.7.0
	 */
	private static function default_strings() {

		return array(

			// Translators: %s stands for a name/identifier.
			'%s is required.'                              => __( '%s is required.', 'pods' ),

			'This field is required.'                      => __( 'This field is required.', 'pods' ),

			'Add'                                          => __( 'Add', 'pods' ),

			'Add New'                                      => __( 'Add New', 'pods' ),

			'Add New Record'                               => __( 'Add New Record', 'pods' ),

			'Added!'                                       => __( 'Added!', 'pods' ),

			'Added! Choose another or <a href="#">close this box</a>' => __( 'Added! Choose another or <a href="#">close this box</a>', 'pods' ),

			'Copy'                                         => __( 'Copy', 'pods' ),

			'Reorder'                                      => __( 'Reorder', 'pods' ),

			'Remove'                                       => __( 'Remove', 'pods' ),

			'Deselect'                                     => __( 'Deselect', 'pods' ),

			'Download'                                     => __( 'Download', 'pods' ),

			'View'                                         => __( 'View', 'pods' ),

			'Edit'                                         => __( 'Edit', 'pods' ),

			'Search'                                       => __( 'Search', 'pods' ),

			// Translators: %s stands for a name/identifier.
			'Search %s'                                    => __( 'Search %s', 'pods' ),

			'Navigating away from this page will discard any changes you have made.' => __( 'Navigating away from this page will discard any changes you have made.', 'pods' ),

			'Some fields have changes that were not saved yet, please save them or cancel the changes before saving the Pod.' => __( 'Some fields have changes that were not saved yet, please save them or cancel the changes before saving the Pod.', 'pods' ),

			'Unable to process request, please try again.' => __( 'Unable to process request, please try again.', 'pods' ),

			'Error uploading file: '                       => __( 'Error uploading file: ', 'pods' ),

			'Allowed Files'                                => __( 'Allowed Files', 'pods' ),

			'The Title'                                    => __( 'The Title', 'pods' ),

			'Select from existing'                         => __( 'Select from existing', 'pods' ),

			'You can only select'                          => __( 'You can only select', 'pods' ),

			// Translators: %s stands for a number.
			'%s item'                                      => __( '%s item', 'pods' ),

			// Translators: %s stands for a number.
			'%s items'                                     => __( '%s items', 'pods' ),

			// Translators: %s stands for a number.
			'You can only select %s item'                  => __( 'You can only select %s item', 'pods' ),

			// Translators: %s stands for a number.
			'You can only select %s items'                 => __( 'You can only select %s items', 'pods' ),

			'Icon'                                         => __( 'Icon', 'pods' ),

		);

	}

	/**
	 * Get current locale information from Multilingual plugins
	 *
	 * @since 2.7.0
	 *
	 * @param array $args    (optional) {
	 *
	 * @type bool   $refresh Rerun get_current_language() logic?
	 * }
	 *
	 * @return string
	 */
	public function get_current_language( $args = array() ) {

		$defaults = array(
			'refresh' => empty( self::$current_language ),
		);

		$args = wp_parse_args( $args, $defaults );

		if ( doing_filter( 'pods_get_current_language' ) ) {
			// Prevent loop.
			$args['refresh'] = false;
		}

		if ( ! $args['refresh'] ) {
			return self::$current_language;
		}

		/**
		 * Override language data used by Pods.
		 *
		 * @since 2.8.0
		 *
		 * @param string $language Language slug
		 * @param array  $context  Language context
		 * @param array  $args     Arguments
		 */
		self::$current_language = apply_filters( 'pods_get_current_language', self::$current_language, self::get_current_language_context( $args ), $args );

		return self::$current_language;
	}

	/**
	 * Get current language context information.
	 *
	 * @since 2.6.6
	 * @since 2.7 Moved to this class from PodsAPI
	 * @since 2.8.0 Refactored from get_current_language_data()
	 *
	 * @param array $args    (optional) {
	 *     @type bool   $refresh Rerun logic?
	 * }
	 *
	 * @return array $context  {
	 *     Language data.
	 *     @type bool   $is_admin            Is admin.
	 *     @type bool   $is_ajax             Is AJAX call.
	 *     @type bool   $is_pods_ajax        Is Pods AJAX call.
	 *     @type string $current_page        Current admin page.
	 *     @type string $current_object_type Current object type (post / term / comment / user).
	 *     @type int    $current_item_id     Current item id.
	 *     @type string $current_item_type   Current item type.
	 * }
	 */
	public function get_current_language_context( $args = array() ) {

		$defaults = array(
			'refresh' => empty( self::$current_language_context ),
		);

		$args = wp_parse_args( $args, $defaults );

		if ( doing_filter( 'pods_get_current_language_context' ) ) {
			// Prevent loop.
			$args['refresh'] = false;
		}

		if ( ! $args['refresh'] ) {
			return self::$current_language_context;
		}

		$pods_ajax = pods_v( 'pods_ajax', 'request', false );

		$context = [
			'is_admin'            => is_admin(),
			'is_ajax'             => defined( 'DOING_AJAX' ) && DOING_AJAX,
			'is_pods_ajax'        => $pods_ajax,
			'current_page'        => '',
			'current_object_type' => '',
			'current_item_id'     => '',
			'current_item_type'   => '',
		];

		/**
		 * Admin functions that overwrite the current language context.
		 *
		 * @since 2.6.6
		 * @since 2.8.0 Refactored for current context instead of language data.
		 */
		if ( is_admin() ) {

			// Get current language based on the object language if available.
			$page = basename( pods_v( 'SCRIPT_NAME', $_SERVER, '' ) );
			if ( $pods_ajax && 'admin-ajax.php' === $page ) {
				$page = basename( pods_v( 'HTTP_REFERER', $_SERVER, '' ) );
			}
			$page = explode( '?', $page );
			$page = reset( $page );

			$context['current_page'] = $page;

			switch ( $page ) {

				case 'post.php':
				case 'edit.php':
					$context['current_object_type'] = 'post';

					$current_post_id = (int) pods_v( 'post', 'request', 0 );
					if ( $pods_ajax ) {
						$current_post_id = (int) pods_v( 'id', 'request', $current_post_id );
					}

					$current_post_type = pods_v( 'post_type', 'request', '' );
					if ( ! $current_post_type && $current_post_id ) {
						$current_post_type = get_post_type( $current_post_id );
					}

					$context['current_item_id']   = $current_post_id;
					$context['current_item_type'] = $current_post_type;
					break;

				case 'term.php':
				case 'edit-tags.php':
					$context['current_object_type'] = 'term';

					$current_term_id = (int) pods_v( 'tag_ID', 'request', 0 );
					if ( $pods_ajax ) {
						$current_term_id = (int) pods_v( 'id', 'request', $current_term_id );
					}

					$current_taxonomy = pods_v( 'taxonomy', 'request', '' );
					if ( ! $current_taxonomy && $current_term_id ) {
						$current_taxonomy = pods_v( 'taxonomy', get_term( $current_term_id ), null );
					}

					$context['current_item_id']   = $current_term_id;
					$context['current_item_type'] = $current_taxonomy;
					break;

				case 'comment.php':
					$context['current_object_type'] = 'comment';
					$context['current_item_type']   = 'comment';

					$current_comment_id = (int) pods_v( 'c', 'request', 0 );
					if ( $pods_ajax ) {
						$current_comment_id = (int) pods_v( 'id', 'request', $current_comment_id );
					}

					$context['current_item_id']   = $current_comment_id;
					break;

				case 'user-edit.php':
					$context['current_object_type'] = 'user';
					$context['current_item_type']   = 'user';

					$current_user_id = (int) pods_v( 'user_id', 'request', 0 );
					if ( $pods_ajax ) {
						$current_user_id = (int) pods_v( 'id', 'request', $current_user_id );
					}

					$context['current_item_id']   = $current_user_id;
					break;
			}

		}//end if (admin)

		/**
		 * Override language context used by Pods.
		 *
		 * @since 2.8.0
		 *
		 * @param array $context  {
		 *     Language data.
		 *     @type bool   $is_admin            Is admin.
		 *     @type bool   $is_ajax             Is AJAX call.
		 *     @type bool   $is_pods_ajax        Is Pods AJAX call.
		 *     @type string $current_page        Current admin page.
		 *     @type string $current_object_type Current object type (post / term / comment / user).
		 *     @type int    $current_item_id     Current item id.
		 *     @type string $current_item_type   Current item type.
		 * }
		 */
		self::$current_language_context = apply_filters( 'pods_get_current_language_context', $context );

		return self::$current_language_context;

	}

}
