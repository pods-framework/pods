<?php

namespace Tribe\Editor\Compatibility;
/**
 * Editor Compatibility with classic editor plugins.
 *
 * @since 4.14.13
 */
class Classic_Editor {
	/**
	 * "Classic Editor" flag for blocks/classic
	 *
	 * @since 4.14.13
	 *
	 * @var string
	 */
	public static $classic_option_key = 'classic-editor-replace';

	/**
	 * "Classic Editor" original param for blocks->classic.
	 * Can be overridden by user choice.
	 *
	 * @since 4.14.13
	 *
	 * @var string
	 */
	public static $classic_param = 'classic-editor';

	/**
	 * "Classic Editor" term used for comparisons.
	 *
	 * @since 4.14.13
	 *
	 * @var string
	 */
	public static $classic_term = 'classic';

	/**
	 * "Blocks Editor" term used for comparisons.
	 *
	 * @since 4.14.13
	 *
	 * @var string
	 */
	public static $block_term = 'block';

	/**
	 * "Classic Editor" param for user override
	 *
	 * @since 4.14.13
	 *
	 * @var string
	 */
	public static $classic_override = 'classic-editor__forget';

	/**
	 * "User Choice" key for user override
	 *
	 * @since 4.14.13
	 *
	 * @var string
	 */
	public static $user_choice_key = 'classic-editor-allow-users';

	/**
	 * User meta "User Choice" key for user override
	 *
	 * @since 4.14.13
	 *
	 * @var string
	 */
	public static $user_meta_choice_key = 'classic-editor-settings';

	/**
	 * Post meta key used for CE "remembering" the last editor used.
	 * The bane of my existence.
	 *
	 * @since 4.14.13
	 *
	 * @var string
	 */
	public static $post_remember_meta_key = 'classic-editor-remember';

	/**
	 * Stores the values used by the Classic Editor plugin to indicate we're using the classic editor.
	 *
	 * @since 4.14.13
	 *
	 * @var array<string>
	 */
	public static $classic_values  = [
		'replace',
		'classic',
	];

	/**
	 * Placeholders
	 *
	 * @since 4.14.13
	 *
	 * @var [type]
	 */

	/**
	 * Holds whether Classic Editor allows user choice of editors.
	 *
	 * @since 4.14.13
	 *
	 * @var null|boolean
	 */
	public static $user_choice_allowed = null;

	/**
	 * Holds the user's preferred editor - set in user profile.
	 *
	 * @since 4.14.13
	 *
	 * @var null|string
	 */
	public static $user_profile_choice = null;

	/**
	 * Holds the GET variable value for enabling the classic editor, if set.
	 * (ie the default editor set)
	 *
	 * @since 4.14.13
	 *
	 * @var null|string
	 */
	public static $classic_url_param = null;

	/**
	 * Holds the GET variable value for overriding the classic editor, if set.
	 * (ie default is classic, this will change it to blocks)
	 *
	 * @since 4.14.13
	 *
	 * @var null|string
	 */
	public static $classic_url_override = null;

	/**
	 * Registers the hooks and filters required based on if the Classic Editor plugin is active.
	 *
	 * @since 4.14.13
	 */
	public function init() {
		if ( static::is_classic_plugin_active() ) {
			$this->hooks();
		}
	}

	/**
	 * Hooks for loading logic outside this class.
	 *
	 * @since 4.14.13
	 */
	public function hooks() {
		add_action( 'tribe_plugins_loaded', [ $this, 'set_classic_url_params' ], 22 );
		add_filter( 'tribe_editor_should_load_blocks', [ $this, 'filter_tribe_editor_should_load_blocks' ], 20 );
	}

	/**
	 * Sets the placeholders for the URL params.
	 *
	 * @since 4.14.13
	 */
	public function set_classic_url_params() {
		static::$classic_url_param    = static::get_classic_param();
		static::$classic_url_override = static::get_classic_override();
	}

	/**
	 * Gets the $classic_url_param placeholder if it's set.
	 * Sets it then returns it if it's not yet set.
	 *
	 * @since 4.14.13
	 *
	 * @return boolean
	 */
	public static function get_classic_param () {
		if ( null !== static::$classic_url_param ) {
			return static::$classic_url_param;
		}

		static::$classic_url_param = isset( $_GET[  static::$classic_param ] ) || isset( $_POST[  static::$classic_param ] );

		return static::$classic_url_param;
	}

	/**
	 * Gets the $classic_url_override placeholder if it's set.
	 * Sets it then returns it if it's not yet set.
	 *
	 * @since 4.14.13
	 *
	 * @return boolean
	 */
	public static function get_classic_override() {
		if ( null !== static::$classic_url_override ) {
			return static::$classic_url_override;
		}

		static::$classic_url_override = isset( $_GET[ static::$classic_override ] ) || isset( $_POST[ static::$classic_override ] );

		return static::$classic_url_override;
	}

	/**
	 * Filters tribe_editor_should_load_blocks based on internal logic.
	 *
	 * @since 4.14.13
	 *
	 * @param boolean $should_load_blocks Whether we should force blocks over classic.
	 *
	 * @return boolean Whether we should force blocks or classic.
	 */
	public function filter_tribe_editor_should_load_blocks( bool $should_load_blocks ) {

		if ( ! static::is_classic_plugin_active() ) {
			return $should_load_blocks;
		}

		if ( static::is_classic_option_active() ) {
			$should_load_blocks = false;
		}

		if ( ! static::get_user_choice_allowed() ) {
			return $should_load_blocks;
		}

		if ( static::get_classic_param() ) {
			$should_load_blocks = false;
		}

		// The override param inverts whatever else is set via parameter/preference.
		if ( static::get_classic_override() ) {
			$should_load_blocks = ! $should_load_blocks;
		}

		global $pagenow;

		// The profile and remember settings only apply to new posts/etc so bail out now if we're not in the admin and creating a new event.
		if ( ! empty( $pagenow ) && ! in_array( $pagenow, [ 'post-new.php' ] ) ) {
			$remember = static::classic_editor_remembers();

			if ( false !== $remember ) {
				$should_load_blocks = static::$block_term === $remember;
			}

			return $should_load_blocks;
		}

		$profile_choice = static::user_profile_choice();

		// Only override via $profile_choice if it is actually set.
		if ( empty( $profile_choice ) ) {
			return $should_load_blocks;
		}

		// Only override via $profile_choice if it contains an expected value.
		if ( static::$block_term === $profile_choice ) {
			$should_load_blocks = true;
		} else if ( static::$classic_term === $profile_choice ) {
			$should_load_blocks = false;
		}

		// The override param inverts whatever else is set via parameter/preference.
		if ( static::get_classic_override() ) {
			$should_load_blocks = ! $should_load_blocks;
		}

		return $should_load_blocks;
	}

	/**
	 * classic_editor_replace is function that is created by the plugin:
	 * used in ECP recurrence and TEC Meta
	 *
	 * @see https://wordpress.org/plugins/classic-editor/
	 *
	 * prior 1.3 version the Classic Editor plugin was bundle inside of a unique function:
	 * `classic_editor_replace` now all is bundled inside of a class `Classic_Editor`
	 *
	 * @since 4.14.13
	 *
	 * @return bool
	 */
	public static function is_classic_plugin_active() {
		$is_plugin_active = class_exists( 'Classic_Editor', false );
		/**
		 * Filter to change the output of calling: `is_classic_plugin_active`
		 *
		 * @since 4.9.12
		 * @since 4.14.13 moved to separate class.
		 *
		 * @param $is_plugin_active bool Value that indicates if the plugin is active or not.
		 */
		return (boolean) apply_filters( 'tribe_is_classic_editor_plugin_active', $is_plugin_active );
	}

	/**
	 * Check if the setting `classic-editor-replace` is set to `replace` that option means to
	 * replace the gutenberg editor with the Classic Editor.
	 *
	 * Prior to 1.3 on Classic Editor plugin the value to identify if is on classic the value
	 * was `replace`, now the value is `classic`
	 *
	 * @since 4.8
	 * @since 4.14.13 moved to separate class.
	 *
	 * @return bool
	 */
	public static function is_classic_option_active() {
		if ( ! static::is_classic_plugin_active() ) {
			return false;
		}

		$valid_values  = [ 'replace', 'classic' ];
		$replace       = in_array( (string) get_option( static::$classic_option_key ), $valid_values, true );

		return (boolean) $replace;
	}


	/**
	 * Get and store wether user choice is allowed - lets us bypass some checks.
	 *
	 * @since 4.14.13
	 *
	 * @return boolean
	 */
	public static function get_user_choice_allowed() {
		if ( null !== static::$user_choice_allowed ) {
			return static::$user_choice_allowed;
		}

		static::$user_choice_allowed = 'allow' === get_option( static::$user_choice_key, 'disallow' );

		return static::$user_choice_allowed;
	}

	/**
	 * Get the and store user's editor of choice - set in the user profile.
	 *
	 * @since 4.14.13
	 *
	 * @return string
	 */
	public static function user_profile_choice() {
		if ( null !== static::$user_profile_choice ) {
			return static::$user_profile_choice;
		}

		global $wpdb;

		$user    = get_current_user_id();
		static::$user_profile_choice = get_user_option( $wpdb->prefix . static::$user_meta_choice_key, $user );

		return static::$user_profile_choice;
	}

	/**
	 * Get whether CE has "remembered" the last editor for a given post.
	 * If so, this is what the default edit link will send us to.
	 *
	 * @since 4.14.13
	 *
	 * @return bool|string The string of the editor choice or false on fails.
	 */
	public static function classic_editor_remembers( $id = null ) {
		if ( empty( $id ) ) {
			$id = isset(  $_GET[ 'post' ] ) ? (int) $_GET[ 'post' ] : null;
		}


		$remember = get_post_meta( $id, static::$post_remember_meta_key, true );

		if ( empty( $remember ) ) {
			return static::$block_term;
		}

		// Why WP, why did you use a different term here?
		return str_replace( '-editor', '', $remember );
	}
}
