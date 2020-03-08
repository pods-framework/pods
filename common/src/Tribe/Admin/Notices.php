<?php
// Don't load directly
defined( 'WPINC' ) or die;

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
	 * User Meta Key that stores which notices have been dimissed
	 *
	 * @since 4.3
	 *
	 * @var string
	 */
	public static $meta_key = 'tribe-dismiss-notice';

	/**
	 * Stores all the Notices and it's configurations
	 *
	 * @since 4.3
	 *
	 * @var array
	 */
	protected $notices = array();

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
		add_action( 'wp_ajax_tribe_notice_dismiss', array( $this, 'maybe_dismiss' ) );

		// Doing AJAX? bail.
		if ( tribe( 'context' )->doing_ajax() ) {
			return;
		}

		// Hook the actual rendering of notices
		add_action( 'current_screen', array( $this, 'hook' ), 20 );

		// Add our notice dismissal script
		tribe_asset(
			Tribe__Main::instance(),
			'tribe-notice-dismiss',
			'notice-dismiss.js',
			array( 'jquery' ),
			'admin_enqueue_scripts'
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
			list( $html, $args, $expire ) = $transient;
			if ( $expire < time() ) {
				continue;
			}
			$this->register( $slug, $html, $args );
		}

		foreach ( $this->notices as $notice ) {
			if ( $notice->dismiss && $this->has_user_dimissed( $notice->slug ) ) {
				continue;
			}

			if (
				! empty( $notice->active_callback )
				&& is_callable( $notice->active_callback )
				&& false == call_user_func( $notice->active_callback )
			) {
				continue;
			}

			add_action( $notice->action, $notice->callback, $notice->priority );
		}
	}

	/**
	 * This will allow the user to Dimiss the Notice using JS.
	 *
	 * We will dismiss the notice without checking to see if the slug was already
	 * registered (via a call to exists()) for the reason that, during a dismiss
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

		$slug = sanitize_title_with_dashes( $_GET[ self::$meta_key ] );

		// Send a JSON answer with the status of dimissal
		wp_send_json( $this->dismiss( $slug ) );
	}

	/**
	 * Allows a Magic to remove the Requirement of creating a callback
	 *
	 * @since 4.3
	 *
	 * @param  string $name       Name of the Method used to create the Slug of the Notice
	 * @param  array  $arguments  Which arguments were used, normally empty
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
			$wrap = isset( $notice->wrap ) ? $notice->wrap : false;

			if ( is_callable( $content ) ) {
				$content = call_user_func_array( $content, array( $notice ) );
			}

			// Return the rendered HTML
			return $this->render( $slug, $content, false, $wrap );
		}

		return false;
	}

	/**
	 * This is a helper to actually print the Message
	 *
	 * @since 4.3
	 *
	 * @param  string      $slug    The Name of the Notice
	 * @param  string      $content The content of the notice
	 * @param  boolean     $return  Echo or return the content
	 * @param  string|bool $wrap    An optional HTML tag to wrap the content.
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

		$notice = $this->get( $slug );
		$this->notices[ $slug ]->is_rendered = true;

		$classes = array( 'tribe-dismiss-notice', 'notice' );
		$classes[] = sanitize_html_class( 'notice-' . $notice->type );
		$classes[] = sanitize_html_class( 'tribe-notice-' . $notice->slug );

		if ( $notice->dismiss ) {
			$classes[] = 'is-dismissible';
		}

		// Prevents Empty Notices
		if ( empty( $content ) ) {
			return false;
		}

		if ( is_string( $wrap ) ) {
			$content = sprintf( '<%1$s>' . $content . '</%1$s>', $wrap );
		}

		$html = sprintf( '<div class="%s" data-ref="%s">%s</div>', implode( ' ', $classes ), $notice->slug, $content );

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
	 * @param  string  $slug    The Name of the Notice
	 * @param  string  $content The content of the notice
	 * @param  boolean $return  Echo or return the content
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
	 * @param  string  $slug  Which notice to check
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
	 * @param  string  $slug  Which notice to check
	 * @param  string  $html  Which html string we are check
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
	 * Checks if a given user has dimissed a given notice.
	 *
	 * @since 4.3
	 *
	 * @param  string    $slug    The Name of the Notice
	 * @param  int|null  $user_id The user ID
	 *
	 * @return boolean
	 */
	public function has_user_dimissed( $slug, $user_id = null ) {

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

		return true;
	}

	/**
	 * A Method to actually add the Meta value telling that this notice has been dismissed
	 *
	 * @since 4.3
	 *
	 * @param  string    $slug    The Name of the Notice
	 * @param  int|null  $user_id The user ID
	 *
	 * @return boolean
	 */
	public function dismiss( $slug, $user_id = null ) {
		if ( is_null( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		// If this user has dimissed we don't care either
		if ( $this->has_user_dimissed( $slug, $user_id ) ) {
			return true;
		}

		return add_user_meta( $user_id, self::$meta_key, $slug, false );
	}

	/**
	 * Removes the User meta holding if a notice was dimissed
	 *
	 * @param  string    $slug    The Name of the Notice
	 * @param  int|null  $user_id The user ID
	 *
	 * @return boolean
	 */
	public function undismiss( $slug, $user_id = null ) {
		if ( is_null( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		// If this user has dimissed we don't care either
		if ( ! $this->has_user_dimissed( $slug, $user_id ) ) {
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
		$user_query = new WP_User_Query( array(
			'meta_key'   => self::$meta_key,
			'meta_value' => $slug,
		) );

		$affected = 0;

		foreach ( $user_query->get_results() as $user ) {
			if ( $this->undismiss( $slug, $user->ID ) ) {
				$affected++;
			}
		}

		return $affected;
	}

	/**
	 * Register a Notice and attach a callback to the required action to display it correctly
	 *
	 * @since 4.3
	 *
	 * @param  string          $slug      Slug to save the notice
	 * @param  callable|string $callback  A callable Method/Fuction to actually display the notice
	 * @param  array           $arguments Arguments to Setup a notice
	 * @param callable|null    $active_callback An optional callback that should return bool values
	 *                                          to indicate whether the notice should display or not.
	 *
	 * @return stdClass
	 */
	public function register( $slug, $callback, $arguments = array(), $active_callback = null ) {
		// Prevent weird stuff here
		$slug = sanitize_title_with_dashes( $slug );

		$defaults = array(
			'callback'        => null,
			'content'         => null,
			'action'          => 'admin_notices',
			'priority'        => 10,
			'expire'          => false,
			'dismiss'         => false,
			'type'            => 'error',
			'is_rendered'     => false,
			'wrap'            => false,
		);

		$defaults['callback'] = array( $this, 'render_' . $slug );
		$defaults['content'] = $callback;

		if ( is_callable( $active_callback ) ) {
			$defaults['active_callback'] = $active_callback;
		}

		// Merge Arguments
		$notice = (object) wp_parse_args( $arguments, $defaults );

		// Enforce this one
		$notice->slug = $slug;

		// Clean these
		$notice->priority = absint( $notice->priority );
		$notice->expire = (bool) $notice->expire;
		$notice->dismiss = (bool) $notice->dismiss;

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
	 * @param  string $slug      Slug to save the notice
	 * @param  string $html      The notice output HTML code
	 * @param  array  $arguments Arguments to Setup a notice
	 * @param  int    $expire    After how much time (in seconds) the notice will stop showing.
	 *
	 * @return stdClass Which notice was registered
	 */
	public function register_transient( $slug, $html, $arguments = array(), $expire = null ) {
		$notices          = $this->get_transients();
		$notices[ $slug ] = array( $html, $arguments, time() + $expire );
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
	 * @return array|null
	 */
	public function get( $slug = null ) {
		// Prevent weird stuff here
		$slug = sanitize_title_with_dashes( $slug );

		if ( is_null( $slug ) ) {
			return $this->notices;
		}

		if ( ! empty( $this->notices[ $slug ] ) ) {
			return $this->notices[ $slug ];
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
		$transient = self::$transient_notices_name;
		$notices   = get_transient( $transient );
		$notices   = is_array( $notices ) ? $notices : array();

		if ( $this->did_prune_transients ) {
			$this->did_prune_transients = true;
			foreach ( $notices as $key => $notice ) {
				list( $html, $args, $expire_at ) = $notice;

				if ( $expire_at < time() ) {
					unset( $notices[ $key ] );
				}
			}
		}

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
}
