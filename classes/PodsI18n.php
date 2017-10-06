<?php
/**
 * @package Pods
 * @since   2.7
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
	private static $current_language_data = null;

	/**
	 * Singleton handling for a basic pods_i18n() request
	 *
	 * @since 2.7
	 */
	private function __construct() {
		self::$instance = $this;

		// Hook all enqueue scripts actions
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'login_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// Polylang
		add_filter( 'pll_get_post_types', array( $this, 'pll_get_post_types' ), 10, 2 );
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
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * @since 2.7
	 */
	public function enqueue_scripts() {

		// Register our i18n script for JS
		wp_register_script( 'sprintf', PODS_URL. 'ui/js/sprintf/sprintf.min.js', array(), '1.1.0', true );
		wp_register_script( 'pods-i18n', PODS_URL . 'ui/js/pods-i18n.js', array( 'sprintf' ), PODS_VERSION, true );

		self::localize_assets();
	}

	/**
	 * Localize assets:
	 *     * Build localizations strings from the defaults and those provided via filter
	 *     * Provide a global JavaScript object with the assembled localization strings via `wp_localize_script`
	 *
	 * @since 2.7
	 */
	private static function localize_assets() {

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

		self::$strings = array_merge( $strings_extra, self::default_strings() );

		foreach ( self::$strings as $key => $str ) {
			self::register( $key, $str );
		}

		// Some other stuff we need to pass through
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
	 * @since 2.7
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
	 * @since 2.7
	 */
	private static function default_strings() {

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

			'Deselect' =>
				__( 'Deselect', 'pods' ),

			'Download' =>
				__( 'Download', 'pods' ),

			'View' =>
				__( 'View', 'pods' ),

			'Edit' =>
				__( 'Edit', 'pods' ),

			'Search' =>
				__( 'Search', 'pods' ),

			'Navigating away from this page will discard any changes you have made.' =>
				__( 'Navigating away from this page will discard any changes you have made.', 'pods' ),

			'Some fields have changes that were not saved yet, please save them or cancel the changes before saving the Pod.' =>
				__( 'Some fields have changes that were not saved yet, please save them or cancel the changes before saving the Pod.', 'pods' ),

			'Unable to process request, please try again.' =>
				__( 'Unable to process request, please try again.', 'pods' ),

			'Error uploading file: ' =>
				__( 'Error uploading file: ', 'pods' ),

			'Allowed Files' =>
				__( 'Allowed Files', 'pods' ),

			'The Title' =>
				__( 'The Title', 'pods' ),

			'Select from existing' =>
				__( 'Select from existing', 'pods' ),

			'You can only select' =>
				__( 'You can only select', 'pods' ),

			'%s item' =>
				__( '%s item', 'pods' ),

			'%s items' =>
				__( '%s items', 'pods' ),

			'Icon' =>
				__( 'Icon', 'pods' ),

		);

	}

	/**
	 * Get current locale information from Multilingual plugins
	 *
	 * @since 2.7
	 *
	 * @param array $args (optional) {
	 *     @type bool $refresh Rerun get_current_language() logic?
	 * }
	 *
	 * @return string
	 */
	public function get_current_language( $args = array() ) {

		$args = wp_parse_args( $args, array(
			'refresh' => false,
		) );

		if ( ! $args['refresh'] && ! empty( self::$current_language ) ) {
			return self::$current_language;
		}

		$this->get_current_language_data( $args );
		return self::$current_language;
	}

	/**
	 * Get current language information from Multilingual plugins
	 *
	 * @since 2.6.6
	 * @since 2.7 Moved to this class from PodsAPI
	 *
	 * @param array $args (optional) {
	 *     @type bool $refresh Rerun logic?
	 * }
	 *
	 * @return array
	 */
	public function get_current_language_data( $args = array() ) {

		$args = wp_parse_args( $args, array(
			'refresh' => false,
		) );

		if ( ! $args['refresh'] && ! empty( self::$current_language_data ) ) {
			return self::$current_language_data;
		}

		/**
		 * @var $sitepress SitePress object
		 * @var $polylang  Polylang object
		 */
		/*
		 * @todo wpml-comp Remove global object usage
		 */
		global $sitepress, $polylang;

		$lang_data        = false;
		$translator       = false;
		$current_language = false;

		// Multilingual support
		if ( did_action( 'wpml_loaded' ) && apply_filters( 'wpml_setting', true, 'auto_adjust_ids' ) ) {
			// WPML support
			$translator = 'WPML';

			// Get the global current language (if set)
			$wpml_language = apply_filters( 'wpml_current_language', null );
			$current_language = ( $wpml_language != 'all' ) ? $wpml_language : '';

		} elseif ( ( function_exists( 'PLL' ) || is_object( $polylang ) ) && function_exists( 'pll_current_language' ) ) {
			// Polylang support
			$translator = 'PLL';

			// Get the global current language (if set)
			$current_language = pll_current_language( 'slug' );
		}

		/**
		 * Admin functions that overwrite the current language
		 *
		 * @since 2.6.6
		 */
		if ( is_admin() && ! empty( $translator ) ) {
			if ( $translator == 'PLL' ) {
				/**
				 * Polylang support
				 * Get the current user's preferred language.
				 * This is a user meta setting that will overwrite the language returned from pll_current_language()
				 * @see polylang/admin/admin-base.php -> init_user()
				 */
				$current_language = get_user_meta( get_current_user_id(), 'pll_filter_content', true );
			}

			// Get current language based on the object language if available
			if ( function_exists( 'get_current_screen' ) ) {
				$current_screen = get_current_screen();

				/**
				 * Overwrite the current language if needed for post types
				 */
				if ( isset( $current_screen->base ) && ( $current_screen->base == 'post' || $current_screen->base == 'edit' ) ) {
					if ( ! empty( $_GET['post'] ) ) {
						/**
						 * WPML support
						 * In WPML the current language is always set to default on an edit screen
						 * We need to overwrite this when the current object is not-translatable to enable relationships with different languages
						 */
						if (   $translator == 'WPML'
						       && ! apply_filters( 'wpml_is_translated_post_type', false, ( get_post_type( $_GET['post'] ) ) )
						) {
							// Overwrite the current language to nothing if this is a NOT-translatable post_type
							$current_language = '';
						}

						/**
						 * Polylang support (1.5.4+)
						 * In polylang the preferred language could be anything.
						 * We only want the related objects if they are not translatable OR the same language as the current object
						 */
						if (   $translator == 'PLL'
						       && function_exists( 'pll_get_post_language' )
						       && pll_is_translated_post_type( get_post_type( $_GET['post'] ) )
						) {
							// Overwrite the current language if this is a translatable post_type
							$current_language = pll_get_post_language( (int) $_GET['post'] );
						}
					}

					/**
					 * Polylang support (1.0.1+)
					 * In polylang the preferred language could be anything.
					 * When we're adding a new object and language is set we only want the related objects if they are not translatable OR the same language
					 */
					if (   $translator == 'PLL'
					       && ! empty( $_GET['new_lang'] )
					       && ! empty( $_GET['post_type'] )
					       && pll_is_translated_post_type( sanitize_text_field( $_GET['post_type'] ) )
					) {
						$current_language = $_GET['new_lang'];
					}

					/**
					 * Overwrite the current language if needed for taxonomies
					 */
				} elseif ( isset( $current_screen->base ) && ( $current_screen->base == 'term' || $current_screen->base == 'edit-tags' ) ) {
					// @todo MAYBE: Similar function like get_post_type for taxonomies so we don't need to check for $_GET['taxonomy']
					if ( ! empty( $_GET['taxonomy'] ) ) {
						/*
						 * @todo wpml-comp API call for taxonomy needed!
						 * Suggested API call:
						 * add_filter( 'wpml_is_translated_taxonomy', $_GET['taxonomy'], 10, 2 );
						 */
						/**
						 * WPML support
						 * In WPML the current language is always set to default on an edit screen
						 * We need to overwrite this when the current object is not-translatable to enable relationships with different languages
						 */
						if (   $translator == 'WPML'
						       && method_exists( $sitepress, 'is_translated_taxonomy')
						       && ! $sitepress->is_translated_taxonomy( $_GET['taxonomy'] )
						) {
							// Overwrite the current language to nothing if this is a NOT-translatable taxonomy
							$current_language = '';
						}

						/**
						 * Polylang support (1.5.4+)
						 * In polylang the preferred language could be anything.
						 * We only want the related objects if they are not translatable OR the same language as the current object
						 */
						if (   $translator == 'PLL'
						       && ! empty( $_GET['tag_ID'] )
						       && function_exists( 'pll_get_term_language' )
						       && pll_is_translated_taxonomy( sanitize_text_field( $_GET['taxonomy'] ) )
						) {
							// Overwrite the current language if this is a translatable taxonomy
							$current_language = pll_get_term_language( (int) $_GET['tag_ID'] );
						}
					}

					/**
					 * Polylang support (1.0.1+)
					 * In polylang the preferred language could be anything.
					 * When we're adding a new object and language is set we only want the related objects if they are not translatable OR the same language
					 */
					if (   $translator == 'PLL'
					       && ! empty( $_GET['new_lang'] )
					       && ! empty( $_GET['taxonomy'] )
					       && pll_is_translated_taxonomy( sanitize_text_field( $_GET['taxonomy'] ) )
					) {
						$current_language = $_GET['new_lang'];
					}
				}
			}
		}

		$current_language = pods_sanitize( sanitize_text_field( $current_language ) );

		if ( ! empty( $current_language ) ) {
			// We need to return language data
			$lang_data = array(
				'language' => $current_language,
				't_id'     => 0,
				'tt_id'    => 0,
				'term'     => null,
			);

			/**
			 * Polylang support
			 * Get the language taxonomy object for the current language
			 */
			if ( $translator == 'PLL' ) {
				$current_language_t = false;

				// Get the language term object
				if ( function_exists( 'PLL' ) && isset( PLL()->model ) && method_exists( PLL()->model, 'get_language' ) ) {
					// Polylang 1.8 and newer
					$current_language_t = PLL()->model->get_language( $current_language );
				} elseif ( is_object( $polylang ) && isset( $polylang->model ) && method_exists( $polylang->model, 'get_language' ) ) {
					// Polylang 1.2 - 1.7.x
					$current_language_t = $polylang->model->get_language( $current_language );
				} elseif ( is_object( $polylang ) && method_exists( $polylang, 'get_language' ) ) {
					// Polylang 1.1.x and older
					$current_language_t = $polylang->get_language( $current_language );
				}

				// If the language object exists, add it!
				if ( $current_language_t && ! empty( $current_language_t->term_id ) ) {
					$lang_data['t_id']  = (int) $current_language_t->term_id;
					$lang_data['tt_id'] = (int) $current_language_t->term_taxonomy_id;
					$lang_data['tl_t_id'] = (int) $current_language_t->tl_term_id;
					$lang_data['tl_tt_id'] = (int) $current_language_t->tl_term_taxonomy_id;
					$lang_data['term']  = $current_language_t;
				}
			}
		}

		/**
		 * Override language data used by Pods.
		 *
		 * @since 2.6.6
		 *
		 * @param array|false    $lang_data {
		 *      Language data
		 *
		 *      @type string       $language  Language slug
		 *      @type int          $t_id      Language term_id
		 *      @type int          $tt_id     Language term_taxonomy_id
		 *      @type WP_Term      $term      Language term object
		 * }
		 * @param string|boolean $translator Language plugin used
		 */
		$lang_data = apply_filters( 'pods_get_current_language', $lang_data, $translator );

		self::$current_language = $lang_data['language'];
		self::$current_language_data = $lang_data;

		return $lang_data;

	}

	/**
	 * Add Pods templates to possible i18n enabled post-types (polylang settings).
	 *
	 * @since  2.7
	 *
	 * @param  array  $post_types
	 * @param  bool   $is_settings
	 *
	 * @return array  mixed
	 */
	public function pll_get_post_types( $post_types, $is_settings = false ) {
		if ( $is_settings ) {
			$post_types['_pods_template'] = '_pods_template';
		}/* else {
			// Force enable this post type for translation.
			$post_types['_pods_template'] = '_pods_template';
		}*/
		return $post_types;
	}

}
