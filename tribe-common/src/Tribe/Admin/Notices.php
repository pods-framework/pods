<?php

use Tribe__Date_Utils as Dates;

/**
 * @since 4.3
 */
class Tribe__Admin__Notices {

	/**
	 * The name of the transient that will store transient notices.
	 *
	 * @since 4.3
	 *
	 * @var string
	 */
	public static $transient_notices_name = '_tribe_admin_notices';

	/**
	 * Whether, in this request, transient notices have been pruned already or not.
	 *
	 * @since 4.3
	 *
	 * @var bool
	 */
	protected $did_prune_transients = false;

	/**
	 * Static singleton variable
	 *
	 * @since 4.3
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Static Singleton Factory Method
	 *
	 * @since 4.3
	 *
	 * @return self
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * User Meta Key that stores which notices have been dismissed.
	 *
	 * @since 4.3
	 *
	 * @var string
	 */
	public static $meta_key = 'tribe-dismiss-notice';

	/**
	 * User Meta Key prefix that stores when notices have been dismissed.
	 *
	 * @since 4.13.0
	 *
	 * @var string
	 */
	public static $meta_key_time_prefix = 'tribe-dismiss-notice-time-';

	/**
	 * Stores all the Notices and it's configurations
	 *
	 * @since 4.3
	 *
	 * @var array
	 */
	protected $notices = [];

	/**
	 * Register the Methods in the correct places
	 *
	 * @since 4.3
	 *
	 */
	private function __construct() {
		// Not in the admin we don't even care
		if ( ! is_admin() ) {
			return;
		}

		// Before we bail on the
		add_action( 'wp_ajax_tribe_notice_dismiss', [ $this, 'maybe_dismiss' ] );

		// Doing AJAX? bail.
		if ( tribe( 'context' )->doing_ajax() ) {
			return;
		}

		// Hook the actual rendering of notices
		add_action( 'current_screen', [ $this, 'hook' ], 20 );

		// Add our notice dismissal script
		tribe_asset(
			Tribe__Main::instance(),
			'tribe-notice-dismiss',
			'notice-dismiss.js',
			[ 'jquery' ],
			null,
			[
				'groups' => 'tec-admin-notices',
			]
		);
	}

	/**
	 * This will happen on the `current_screen` and will hook to the correct actions and display the notices
	 *
	 * @since 4.3
	 *
	 * @return void
	 */
	public function hook() {
		$transients = $this->get_transients();

		foreach ( $transients as $slug => $transient ) {
			if ( $this->transient_notice_expired( $slug ) ) {
				continue;
			}
			list( $html, $args, $expire ) = $transients[ $slug ];
			$this->register( $slug, $html, $args );
		}

		foreach ( $this->notices as $notice ) {
			if ( ! $this->showing_notice( $notice->slug ) ) {
				continue;
			}

			add_action( $notice->action, $notice->callback, $notice->priority );
		}
	}

	/**
	 * This will allow the user to Dismiss the Notice using JS.
	 *
	 * We will dismiss the notice without checking to see if the slug was already
	 * registered (via a call to exists()) for the reason that, during dismissal
	 * ajax request, some valid notices may not have been registered yet.
	 *
	 * @since 4.3
	 *
	 * @return void
	 */
	public function maybe_dismiss() {
		if ( empty( $_GET[ self::$meta_key ] ) ) {
			wp_send_json( false );
		}

		$slug = sanitize_key( $_GET[ self::$meta_key ] );

		// Send a JSON answer with the status of dismissal
		wp_send_json( $this->dismiss( $slug ) );
	}

	/**
	 * Allows a Magic to remove the Requirement of creating a callback
	 *
	 * @since 4.3
	 *
	 * @param string $name      Name of the method used to create the slug of the notice.
	 * @param array  $arguments Which arguments were used, normally empty.
	 *
	 * @return string
	 */
	public function __call( $name, $arguments ) {
		// Transform from Method name to Notice number
		$slug = preg_replace( '/render_/', '', $name, 1 );

		if ( ! $this->exists( $slug ) ) {
			return false;
		}

		$notice = $this->get( $slug );

		if (
			empty( $notice->active_callback )
			|| (
				is_callable( $notice->active_callback )
				&& true == call_user_func( $notice->active_callback )
			)
		) {
			$content = $notice->content;
			$wrap    = isset( $notice->wrap ) ? $notice->wrap : false;

			if ( is_array( $content ) && isset( $content[0] ) && $content[0] instanceof __PHP_Incomplete_Class ) {
				// From a class that no longer exists (e.g. the plugin is not active), clean and bail.
				$this->remove( $slug );
				$this->remove_transient( $slug );

				return false;
			}

			if ( is_callable( $content ) ) {
				$content = call_user_func_array( $content, [ $notice ] );
			}

			if ( empty( $content ) ) {
				// There is nothing to render, let's avoid the empty notice frame.
				return false;
			}

			tribe_asset_enqueue_group( 'tec-admin-notices' );

			// Return the rendered HTML.
			$html = $this->render( $slug, $content, false, $wrap );

			// Remove the notice and the transient (if any) since it's been rendered.
			$this->remove( $slug );
			$this->remove_transient( $slug );

			return $html;
		}

		return false;
	}

	/**
	 * This is a helper to actually print the Message
	 *
	 * @since 4.3
	 *
	 * @param string      $slug    The name of the notice.
	 * @param string      $content The content of the notice.
	 * @param boolean     $return  Echo or return the content.
	 * @param string|bool $wrap    An optional HTML tag to wrap the content.
	 *
	 * @return bool|string
	 */
	public function render( $slug, $content = null, $return = true, $wrap = false ) {
		if ( ! $this->exists( $slug ) ) {
			return false;
		}

		// Bail if we already rendered
		if ( $this->is_rendered( $slug ) ) {
			if ( $this->is_rendered_html( $slug, $content ) && ! $return ) {
				echo $content;
			}

			return false;
		}

		$notice                              = $this->get( $slug );
		$this->notices[ $slug ]->is_rendered = true;

		$classes   = [ 'tribe-dismiss-notice', 'notice' ];
		$classes[] = sanitize_html_class( 'notice-' . $notice->type );
		$classes[] = sanitize_html_class( 'tribe-notice-' . $notice->slug );

		if ( $notice->dismiss ) {
			$classes[] = 'is-dismissible';
		}

		if ( $notice->inline ) {
			$classes[] = 'inline';
		}

		// Prevents Empty Notices
		if ( empty( $content ) ) {
			return false;
		}

		if ( is_string( $wrap ) ) {
			$content = sprintf( '<%1$s>' . $content . '</%1$s>', $wrap );
		}

		$html = sprintf( '<div class="%s" data-ref="%s">%s</div>', implode( ' ', $classes ), $notice->slug, $content );
		tribe_asset_enqueue_group( 'tec-admin-notices' );

		if ( ! $return ) {
			echo $html;
		}

		return $html;
	}

	/**
	 * This is a helper to print the message surrounded by `p` tags.
	 *
	 * @since 4.3
	 *
	 * @param string  $slug    The name of the notice.
	 * @param string  $content The content of the notice.
	 * @param boolean $return  Echo or return the content.
	 *
	 * @return boolean|string
	 */
	public function render_paragraph( $slug, $content = null, $return = true ) {
		return $this->render( $slug, $content, $return, 'p' );
	}

	/**
	 * Checks if a given notice is rendered
	 *
	 * @since  4.7.10
	 *
	 * @param string $slug Which notice to check.
	 *
	 * @return boolean
	 */
	public function is_rendered( $slug ) {
		if ( ! $this->exists( $slug ) ) {
			return false;
		}

		$notice = $this->get( $slug );

		return isset( $notice->is_rendered ) ? $notice->is_rendered : false;
	}

	/**
	 * Checks if a given string is a notice rendered
	 *
	 * @since  4.7.10
	 *
	 * @param string $slug Which notice to check.
	 * @param string $html Which html string we are check.
	 *
	 * @return boolean
	 */
	public function is_rendered_html( $slug, $html ) {
		if ( ! $this->exists( $slug ) ) {
			return false;
		}

		$search = sprintf( 'data-ref="%s"', $slug );

		return false !== strpos( $html, $search );
	}

	/**
	 * Checks if a given user has dismissed a given notice.
	 *
	 * @since 4.13.0
	 *
	 * @param string   $slug    The name of the notice.
	 * @param int|null $user_id The user ID.
	 *
	 * @return boolean
	 */
	public function has_user_dismissed( $slug, $user_id = null ) {

		if ( is_null( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		$dismissed_notices = get_user_meta( $user_id, self::$meta_key );

		if ( ! is_array( $dismissed_notices ) ) {
			return false;
		}

		if ( ! in_array( $slug, $dismissed_notices ) ) {
			return false;
		}

		$notice = $this->get( $slug );
		if (
			is_object( $notice )
			&& $notice->recurring
			&& $this->should_recurring_notice_show( $slug, $user_id )
		) {
			return false;
		}

		return true;
	}

	/**
	 * Checks if a given user has dismissed a given notice.
	 *
	 * @since      4.3
	 * @deprecated 4.13.0 Deprecated in favor of correcting the typo.
	 *
	 * @param string   $slug    The Name of the Notice
	 * @param int|null $user_id The user ID
	 *
	 * @return boolean
	 */
	public function has_user_dimissed( $slug, $user_id = null ) {
		return $this->has_user_dismissed( $slug, $user_id );
	}

	/**
	 * Gets the last Dismissal for a given notice slug and user.
	 *
	 * @since 4.13.0
	 *
	 * @param string   $slug    Slug of the notice to look for.
	 * @param int|null $user_id Which user? If null will default to current user.
	 *
	 * @return false|\Tribe\Utils\Date_I18n
	 */
	public function get_last_dismissal( $slug, $user_id = null ) {
		if ( is_null( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		$dismissed_time = get_user_meta( $user_id, static::$meta_key_time_prefix . $slug, true );

		if ( ! is_numeric( $dismissed_time ) ) {
			return false;
		}

		return Dates::build_date_object( $dismissed_time );
	}

	/**
	 * Determines if a given notice needs to be re-displayed in case of recurring notice.
	 *
	 * @since 4.13.0
	 *
	 * @param string   $slug    Slug of the notice to look for.
	 * @param int|null $user_id Which user? If null will default to current user.
	 *
	 * @return false|\Tribe\Utils\Date_I18n
	 */
	public function should_recurring_notice_show( $slug, $user_id = null ) {
		$notice = $this->get( $slug );
		if ( ! is_object( $notice ) ) {
			return false;
		}

		if ( ! $notice->recurring || ! $notice->recurring_interval ) {
			return false;
		}

		if ( is_null( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		$interval       = Dates::interval( $notice->recurring_interval );
		$last_dismissal = $this->get_last_dismissal( $slug, $user_id );
		if ( ! $last_dismissal ) {
			return false;
		}

		$next_dismissal = $last_dismissal->add( $interval );
		$now            = Dates::build_date_object( 'now' );

		if ( $now >= $next_dismissal ) {
			delete_user_meta( $user_id, self::$meta_key, $slug );

			return true;
		}

		return false;
	}

	/**
	 * A Method to actually add the Meta value telling that this notice has been dismissed
	 *
	 * @since 4.3
	 *
	 * @param string   $slug    The Name of the Notice
	 * @param int|null $user_id The user ID
	 *
	 * @return boolean
	 */
	public function dismiss( $slug, $user_id = null ) {
		if ( is_null( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		// If this user has dismissed we don't care either
		if ( $this->has_user_dismissed( $slug, $user_id ) ) {
			return true;
		}

		update_user_meta( $user_id, static::$meta_key_time_prefix . $slug, time() );

		return add_user_meta( $user_id, self::$meta_key, $slug, false );
	}

	/**
	 * Removes the User meta holding if a notice was dismissed
	 *
	 * @param string   $slug    The Name of the Notice
	 * @param int|null $user_id The user ID
	 *
	 * @return boolean
	 */
	public function undismiss( $slug, $user_id = null ) {
		if ( is_null( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		// If this user has dismissed we don't care either
		if ( ! $this->has_user_dismissed( $slug, $user_id ) ) {
			return false;
		}

		return delete_user_meta( $user_id, self::$meta_key, $slug );
	}

	/**
	 * Undismisses the specified notice for all users.
	 *
	 * @since 4.3
	 *
	 * @param string $slug
	 *
	 * @return int
	 */
	public function undismiss_for_all( $slug ) {
		$user_query = new WP_User_Query( [
			'meta_key'   => self::$meta_key,
			'meta_value' => $slug,
		] );

		$affected = 0;

		foreach ( $user_query->get_results() as $user ) {
			if ( $this->undismiss( $slug, $user->ID ) ) {
				$affected ++;
			}
		}

		return $affected;
	}

	/**
	 * Register a Notice and attach a callback to the required action to display it correctly
	 *
	 * @since 4.3
	 *
	 * @param string          $slug             Slug to save the notice
	 * @param callable|string $callback         A callable Method/Function to actually display the notice
	 * @param array           $arguments        Arguments to Setup a notice
	 * @param callable|null   $active_callback  An optional callback that should return bool values
	 *                                          to indicate whether the notice should display or not.
	 *
	 * @return stdClass
	 */
	public function register( $slug, $callback, $arguments = [], $active_callback = null ) {
		// Prevent weird stuff here
		$slug = sanitize_key( $slug );

		$defaults = [
			'callback'           => null,
			'content'            => null,
			'action'             => 'admin_notices',
			'priority'           => 10,
			'expire'             => false,
			'dismiss'            => false,
			'inline'             => false,
			'recurring'          => false,
			'recurring_interval' => null,
			'type'               => 'error',
			'is_rendered'        => false,
			'wrap'               => false,
		];

		$defaults['callback'] = [ $this, 'render_' . $slug ];
		$defaults['content']  = $callback;

		if ( is_callable( $active_callback ) ) {
			$defaults['active_callback'] = $active_callback;
		}

		// Merge Arguments
		$notice = (object) wp_parse_args( $arguments, $defaults );

		// Enforce this one
		$notice->slug = $slug;

		// Clean these
		$notice->priority  = absint( $notice->priority );
		$notice->expire    = (bool) $notice->expire;
		$notice->recurring = (bool) $notice->recurring;

		if ( ! is_callable( $notice->dismiss ) ) {
			$notice->dismiss   = (bool) $notice->dismiss;
		}
		if ( ! is_callable( $notice->inline ) ) {
			$notice->inline   = (bool) $notice->inline;
		}

		// Set the Notice on the array of notices
		$this->notices[ $slug ] = $notice;

		// Return the notice Object because it might be modified
		return $notice;
	}

	/**
	 * Create a transient Admin Notice easily.
	 *
	 * A transient admin notice is a "fire-and-forget" admin notice that will display once registered and
	 * until dismissed (if dismissible) without need, on the side of the source code, to register it on each request.
	 *
	 * @since  4.7.7
	 *
	 * @param string $slug      Slug to save the notice
	 * @param string $html      The notice output HTML code
	 * @param array  $arguments Arguments to Setup a notice
	 * @param int    $expire    After how much time (in seconds) the notice will stop showing.
	 *
	 * @return stdClass Which notice was registered
	 */
	public function register_transient( $slug, $html, $arguments = [], $expire = null ) {
		$notices          = $this->get_transients();
		$notices[ $slug ] = [ $html, $arguments, time() + $expire ];
		$this->set_transients( $notices );
	}

	/**
	 * Removes a transient notice based on its slug.
	 *
	 * @since 4.7.7
	 *
	 * @param string $slug
	 */
	public function remove_transient( $slug ) {
		$notices = $this->get_transients();
		unset( $notices[ $slug ] );
		$this->set_transients( $notices );
	}

	/**
	 * Removes a notice based on its slug.
	 *
	 * @since 4.3
	 *
	 * @param string $slug
	 *
	 * @return bool
	 */
	public function remove( $slug ) {
		if ( ! $this->exists( $slug ) ) {
			return false;
		}

		unset( $this->notices[ $slug ] );

		return true;
	}

	/**
	 * Gets the configuration for the Notices
	 *
	 * @since 4.3
	 *
	 * @param string $slug
	 *
	 * @return object|array|null
	 */
	public function get( $slug = null ) {
		if ( is_null( $slug ) ) {
			return $this->notices;
		}

		// Prevent weird stuff here
		$slug = sanitize_key( $slug );

		if ( ! empty( $this->notices[ $slug ] ) ) {
			// I want to avoid modifying the registered value.
			$notice = $this->notices[ $slug ];

			if ( is_callable( $notice->inline ) ) {
				$notice->inline = call_user_func( $notice->inline, $notice );
			}

			if ( is_callable( $notice->dismiss ) ) {
				$notice->dismiss = call_user_func( $notice->dismiss, $notice );
			}

			return $notice;
		}

		return null;
	}

	/**
	 * Checks if a given notice exists
	 *
	 * @since 4.3
	 *
	 * @param string $slug
	 *
	 * @return bool
	 */
	public function exists( $slug ) {
		return is_object( $this->get( $slug ) ) ? true : false;
	}

	/**
	 * Returns an array of registered transient notices.
	 *
	 * @since 4.7.7
	 *
	 * @return array An associative array in the shape [ <slug> => [ <html>, <args>, <expire timestamp> ] ]
	 */
	protected function get_transients() {
		$cached = tribe( 'cache' )['transient_admin_notices'];

		if ( false !== $cached ) {
			return $cached;
		}

		$transient = self::$transient_notices_name;
		$notices   = get_transient( $transient );
		$notices   = is_array( $notices ) ? $notices : [];

		if ( $this->did_prune_transients ) {
			$this->did_prune_transients = true;
			foreach ( $notices as $key => $notice ) {
				list( $html, $args, $expire_at ) = $notice;

				if ( $expire_at < time() ) {
					unset( $notices[ $key ] );
				}
			}
		}

		tribe( 'cache' )['transient_admin_notices'] = $notices;

		return $notices;
	}

	/**
	 * Updates/sets the transient notices transient.
	 *
	 * @since 4.7.7
	 *
	 * @param array $notices An associative array in the shape [ <slug> => [ <html>, <args>, <expire timestamp> ] ]
	 */
	protected function set_transients( $notices ) {
		$transient = self::$transient_notices_name;
		set_transient( $transient, $notices, MONTH_IN_SECONDS );
	}

	/**
	 * Checks whether a specific transient admin notices is being shown or not, depending on its expiration and
	 * dismissible status.
	 *
	 *
	 * @since 4.11.1
	 *
	 * @param string|array $slug The slug, or slugs, of the transient notices to check. This is the same slug used
	 *                           to register the transient notice in the `tribe_transient_notice` function or the
	 *                           `Tribe__Admin__Notices::register_transient()` method.
	 *
	 * @return bool Whether the transient notice is showing or not.
	 */
	public function showing_transient_notice( $slug ) {
		$transient_notices = (array) $this->get_transients();

		return isset( $transient_notices[ $slug ] )
		       && ! $this->has_user_dismissed( $slug )
		       && ! $this->transient_notice_expired( $slug );
	}

	/**
	 * Checks whether a transient notice expired or not.
	 *
	 * @since 4.11.1
	 *
	 * @param string|array $slug The slug, or slugs, of the transient notices to check. This is the same slug used
	 *                           to register the transient notice in the `tribe_transient_notice` function or the
	 *                           `Tribe__Admin__Notices::register_transient()` method.
	 *
	 * @return bool Whether the transient notice is expired or not.
	 */
	protected function transient_notice_expired( $slug ) {
		$transients = (array) $this->get_transients();

		if ( ! isset( $transients[ $slug ] ) ) {
			return true;
		}

		list( $html, $args, $expire ) = $transients[ $slug ];
		if ( $expire < time() ) {
			return true;
		}

		return false;
	}

	/**
	 * Checks whether a notice is being shown or not; the result takes the notice callback and dismissible status into
	 * account.
	 *
	 * @since 4.11.1
	 *
	 * @param string|array $slug The slug, or slugs, of the transient notices to check. This is the same slug used
	 *                           to register the transient notice in the `tribe_transient_notice` function or the
	 *                           `Tribe__Admin__Notices::register_transient()` method.
	 *
	 * @return bool Whether the notice is showing or not.
	 */
	public function showing_notice( $slug ) {
		if ( ! isset( $this->notices[ $slug ] ) ) {
			return false;
		}

		$notice = $this->notices[ $slug ];
		if ( $notice->dismiss && $this->has_user_dismissed( $notice->slug ) ) {
			return false;
		}

		if (
			! empty( $notice->active_callback )
			&& is_callable( $notice->active_callback )
			&& false == call_user_func( $notice->active_callback )
		) {
			return false;
		}

		return true;
	}
}
