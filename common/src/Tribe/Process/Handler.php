<?php

/**
 * Class Tribe__Process__Handler
 *
 * The base class for all Modern Tribe async process handlers.
 *
 * @since 4.7.12
 * @since 4.9.5 Removed dependency on `WP_Async_Request` class.
 *
 * @see   Tribe__Service_Providers__Processes for more insight about this class utility.
 */
abstract class Tribe__Process__Handler {
	/**
	 * The default handler action name.
	 *
	 * @var string
	 */
	protected $action = 'async_request';

	/**
	 * The handler identifier.
	 *
	 * @var string
	 */
	protected $identifier;

	/**
	 * An array of data for the process.
	 *
	 * @var array
	 */
	protected $data = [];

	/**
	 * This handler cron identifier.
	 *
	 * @var string
	 */
	protected $healthcheck_cron_hook_id;
	/**
	 * @var string The common identified prefix to all our async process handlers.
	 */
	protected $prefix = 'tribe_process';
	/**
	 * An instance of the object abstracting the feature detection functionality.
	 *
	 * @var Tribe__Feature_Detection
	 */
	protected $feature_detection;

	/**
	 * An array of query arguments that should be used in place of the default ones.
	 * Extending classes can override/set this to replace the default entirely.
	 *
	 * @var array
	 */
	protected $query_args;

	/**
	 * The absolute URL that should be used to POST requests.
	 * Extending classes can override/set this to replace the default entirely.
	 *
	 * @var string
	 */
	protected $query_url;

	/**
	 * An array of arguments that should be used as body of the POST request.
	 * Extending classes can override/set this to replace the default entirely.
	 *
	 * @var array
	 */
	protected $post_args;

	/**
	 * Tribe__Process__Handler constructor.
	 *
	 * @since 4.7.12
	 */
	public function __construct() {
		$class            = get_class( $this );
		$this->action     = call_user_func( [ $class, 'action' ] );
		$this->identifier = $this->prefix . '_' . $this->action;

		add_action( 'wp_ajax_' . $this->identifier, [ $this, 'maybe_handle' ] );

		/**
		 * Filters whether background processing should be triggered and handled on
		 * non-private AJAX requests as the ones triggered by a non logged in user.
		 * Defaults to `true` to exploit any possible chance to process.
		 *
		 * @since 4.9.5
		 *
		 * @param bool   $allow_nopriv Whether background processing should be triggered and handled on
		 *                             non-private AJAX requests or not.
		 * @param static $this         This handler instance.
		 */
		$allow_nopriv = apply_filters( 'tribe_process_allow_nopriv_handling', true, $this );

		if ( $allow_nopriv ) {
			add_action( 'wp_ajax_nopriv_' . $this->identifier, [ $this, 'maybe_handle' ] );
		}

		$this->healthcheck_cron_hook_id = $this->identifier;
		$this->feature_detection        = tribe( 'feature-detection' );

		/*
		 * This object might have been built while processing crons so
		 * we hook on the the object cron identifier to handle the task
		 * if the cron-triggered action ever fires.
		 */
		add_action( $this->healthcheck_cron_hook_id, [ $this, 'maybe_handle' ] );
	}

	/**
	 * Returns the async process action name.
	 *
	 * Extending classes must override this method to return their unique action slug.
	 *
	 * @since 4.7.12
	 *
	 * @return string
	 *
	 * @throws RuntimeException If the extending class does not override this method.
	 */
	public static function action() {
		$class = static::class;
		throw new RuntimeException( "Class {$class} should override the `action` method to define its own unique identifier." );
	}

	/**
	 * Handles the process request if valid and if authorized.
	 *
	 * @since 4.7.23
	 * @since 4.9.5 Pulled the `maybe_handle` implementation of the `WP_Async_Request` class.
	 *
	 * @param array|null $data_source A source of data if not provided in the request; used for
	 *                                cron-based fallback.
	 */
	public function maybe_handle( $data_source = null ) {
		$data_source = (array) $data_source;

		if ( $this->feature_detection->supports_async_process() ) {
			// Don't lock up other requests while processing.
			session_write_close();

			check_ajax_referer( $this->identifier, 'nonce' );

			// Let's make sure to hydrate date from the request if not set.
			if ( count( array_filter( $data_source ) ) < 1 ) {
				$data_source = $_POST;
			}

			do_action(
				'tribe_log',
				'debug',
				$this->identifier,
				[
					'action'      => 'async_handling',
					'data_source' => $data_source,
					'payload'     => $_POST,
				]
			);

			$this->handle( $data_source );

			wp_die();
		}

		/*
		 * If the environment does not support AJAX-based async processing then
		 * fallback to use the cron-based approach and just call the handle method
		 * removing it first from the action to avoid multiple calls.
		 */
		wp_clear_scheduled_hook( $this->healthcheck_cron_hook_id, [ $data_source ] );

		do_action(
			'tribe_log',
			'debug',
			$this->identifier,
			array_merge( [ 'action' => 'cron_handling' ], $data_source ) );

		$this->handle( $data_source );
	}

	/**
	 * Overrides the base `dispatch` method to allow for constants and/or environment vars to run
	 * async requests in sync mode.
	 *
	 * @since 4.7.12
	 * @since 4.9.5 Pulled `dispatch` method logic from the `WP_Async_Request` class.
	 *
	 * @return mixed
	 */
	public function dispatch() {
		if (
			( defined( 'TRIBE_NO_ASYNC' ) && true === TRIBE_NO_ASYNC )
			|| true === (bool) getenv( 'TRIBE_NO_ASYNC' )
		) {
			do_action( 'tribe_log', 'debug', $this->identifier, [ 'action' => 'sync_handle', 'data' => $this->data ] );

			return $this->sync_handle( $this->data );
		}

		if ( $this->feature_detection->supports_async_process() ) {
			$url  = add_query_arg( $this->get_query_args(), $this->get_query_url() );
			$args = $this->get_post_args();

			do_action( 'tribe_log', 'debug', $this->identifier, [ 'action' => 'async_dispatch', 'data' => $this->data ] );

			return wp_remote_post( esc_url_raw( $url ), $args );
		}

		/*
		 * If async AJAX-based processing is not available then we "dispatch"
		 * by scheduling a single cron event immediately (as soon as possible)
		 * for this handler cron identifier.
		 */
		if ( ! wp_next_scheduled( $this->healthcheck_cron_hook_id, [ $this->data ] ) ) {
			// Schedule the event to happen as soon as possible.
			$scheduled = wp_schedule_single_event( time() - 1, $this->healthcheck_cron_hook_id, [ $this->data ] );

			if ( false === $scheduled ) {
				/** @var Tribe__Log__Logger $logger */
				$logger = tribe( 'logger' );
				$class  = get_class( $this );
				$src    = call_user_func( [ $class, 'action' ] );
				$logger->log( 'Could not schedule event for cron-based handling', Tribe__Log::ERROR, $src );

				do_action(
					'tribe_log',
					'error',
					$this->identifier,
					[ 'action' => 'schedule_cron', 'data' => $this->data ]
				);
			}

			do_action(
				'tribe_log',
				'debug',
				$this->identifier,
				[ 'action' => 'schedule_cron', 'data' => $this->data ]
			);
		}

		return true;
	}

	/**
	 * Handles the process immediately, not in an async manner.
	 *
	 * @since 4.7.12
	 *
	 * @param array|null $data_source If not provided the method will read the handler data from the
	 *                                request array.
	 *
	 * @return mixed|null The result of the synchronous handling.
	 */
	abstract public function sync_handle( array $data_source = null );

	/**
	 * Returns an array of arguments that will be used to send the POST request.
	 *
	 * @since 4.9.5 Pulled from the `WP_Async_Request` class.
	 *
	 * @return array An array of arguments for the POST request.
	 */
	protected function get_query_args() {
		if ( null !== $this->query_args ) {
			return $this->query_args;
		}

		return [
			'action' => $this->identifier,
			'nonce'  => wp_create_nonce( $this->identifier ),
		];
	}

	/**
	 * Returns the URL that wil be used to post the request.
	 *
	 * @since 4.9.5 Pulled from the `WP_Async_Request` class.
	 *
	 * @return string The URL that will be used to POST the dispatch request; defaults
	 *                to the `admin-ajax.php` one.
	 */
	protected function get_query_url() {
		if ( null !== $this->query_url ) {
			return $this->query_url;
		}

		return admin_url( 'admin-ajax.php' );
	}

	/**
	 * Returns the arguments that will be used to send the POST request.
	 *
	 * @since 4.9.5 Pulled from the `WP_Async_Request` class.
	 *
	 * @return array An array of arguments that will be used to send the POST request.
	 */
	protected function get_post_args() {
		if ( null !== $this->post_args ) {
			return $this->post_args;
		}

		return [
			'timeout'   => 0.01,
			'blocking'  => false,
			'body'      => $this->data,
			'cookies'   => $_COOKIE,
			'sslverify' => apply_filters( 'https_local_ssl_verify', false ),
		];
	}

	/**
	 * Returns this handler cron hook identifier.
	 *
	 * The handler cron hook identifier is the one that the handler
	 * will use to schedule a single cron event when the `dispatch`
	 * method is called and the environment does not support async
	 * processing.
	 *
	 * @since 4.7.23
	 *
	 * @return string The complete cron hook name (identifier) for
	 *                this handler.
	 */
	public function get_healthcheck_cron_hook_id() {
		return $this->healthcheck_cron_hook_id;
	}

	/**
	 * Sets the that will be used during the request.
	 *
	 * @since 4.9.5 Pulled from the `WP_Async_Request` class.
	 *
	 * @param array $data Data.
	 *
	 * @return $this This handler instance.
	 */
	public function data( $data ) {
		$this->data = $data;

		return $this;
	}

	/**
	 * Handles the request and performs an action.
	 *
	 * @since 4.9.5 Pulled from the `WP_Async_Request` class.
	 *
	 * @param array|null $data_source A source of data if not provided in the request; used for
	 *                                cron-based fallback.
	 *
	 * @return null|array Depending on the context of the call, cron or async, either the result
	 *                    of the handling (cron) or nothing (async).
	 */
	abstract protected function handle( array $data_source = null );
}
