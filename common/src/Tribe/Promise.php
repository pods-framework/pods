<?php
/**
 * Models a promise to do something in asynchronous mode.
 *
 * Example usage:
 *
 *      $promise = new Promise( 'wp_insert_post', $a_lot_of_posts_to_insert );
 *      $promise->save()->dispatch();
 *      $promise_id = $promise->get_id();
 *
 * The promise is really a background process in disguise and will work, for all
 * intents and purposes, like one.
 *
 * @since 4.9.5
 */

class Tribe__Promise extends Tribe__Process__Queue {

	/**
	 * The action that will be done when the promise is done.
	 *
	 * @var string
	 */
	protected $resolved;

	/**
	 * An array of arguments that will be used to call a callback on completion.
	 *
	 * @var array
	 */
	protected $resolved_args;

	/**
	 * The action that will be done when the promise fails due to an error.
	 *
	 * @var string
	 */
	protected $rejected;

	/**
	 * An array of arguments that will be used to call a callback on failure.
	 *
	 * @var array
	 */
	protected $rejected_args;

	/**
	 * Whether this promise did resolve correctly or not.
	 *
	 * @var bool
	 */
	protected $resolved_correctly = true;

	/**
	 * Tribe__Promise constructor.
	 *
	 * @param string|array|Tribe__Utils__Callback $callback   The callback that should run to perform the promise task.
	 * @param   array                             $items      The items to process, each item will be passed as first
	 *                                                        argument to the callback at run-time.
	 * @param array                               $extra_args An array of extra arguments that will be passed to the
	 *                                                        callback function.
	 */
	public function __construct( $callback = null, array $items = null, array $extra_args = array() ) {
		parent::__construct();

		if ( ! empty( $callback ) && ! empty( $items ) ) {
			foreach ( $items as $target ) {
				$item['callback'] = $callback;
				$item['args']     = array_merge( array( $target ), $extra_args );
				$this->push_to_queue( $item );
			}
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public static function action() {
		return 'promise';
	}

	/**
	 * Sets a callback, and optional arguments, that will be called when the promise
	 * is resolved.
	 *
	 * The callback and arguments must be serializable and make sense in the context of,
	 * potentially, a different call from the one where this method is called.
	 *
	 * @since 4.9.5
	 *
	 * @param callable|Tribe__Utils__Callback $resolved            The callback to call on success.
	 * @param callable|Tribe__Utils__Callback $rejected            The callback to call on failure.
	 * @param array                           $resolved_args       The arguments that will be passed to the resolved
	 *                                                             callback.
	 * @param array                           $rejected_args       The arguments that will be passed to the rejected
	 *                                                             callback.
	 *
	 * @return Tribe__Promise This promise.
	 *
	 * @throws LogicException If this method is called after saving the promise.
	 */
	public function then( $resolved, $rejected = null, array $resolved_args = null, array $rejected_args = null ) {
		if ( $this->did_save ) {
			throw new LogicException( 'The promise "then" method should be called before the "save" one' );
		}

		$this->resolved      = $resolved;
		$this->resolved_args = $resolved_args;
		$this->rejected      = $rejected;
		$this->rejected_args = $rejected_args;

		foreach ( $this->data as &$item ) {
			$item['resolved']      = $this->resolved;
			$item['resolved_args'] = $this->resolved_args;
			$item['rejected']      = $this->rejected;
			$item['rejected_args'] = $this->rejected_args;
		}

		return $this;
	}

	/**
	 * Overrides the  base method to save before dispatching.
	 *
	 * @since 4.9.5
	 *
	 * @return mixed The dispatch return value.
	 */
	public function dispatch() {
		if ( empty( $this->data ) ) {
			$this->complete();

			return null;
		}

		if ( ! $this->did_save ) {
			$this->save();
		}

		return parent::dispatch();
	}

	/**
	 * A custom error handler to log any error tha might happen while invoking a promise
	 * callback.
	 *
	 * @since 4.9.5
	 *
	 * @param int    $code          The error code.
	 * @param string $error_message The error message.
	 *
	 * @see   set_error_handler()
	 */
	public function error_handler( $code, $error_message ) {
		$message = 'There was an error (' . $code . ') while invoking a promise callback:';
		$message .= "\n\t" . $error_message;
		tribe( 'logger' )->log( $message, Tribe__Log::ERROR, __CLASS__ );
	}

	/**
	 * Performs the task associated with the promise.
	 *
	 * The promise is really just a flexible background process that
	 *
	 * @since 4.9.5
	 *
	 * @param array                           $item      The promise payload, keys:
	 *                                                   {
	 * @param callable|Tribe__Utils__Callback $callback  The callback this promise will
	 *                                                   call to perform the task.
	 * @param array                           $args      An array of arguments that will be passed to the callback.
	 * @param callable|Tribe__Utils__Callback $then      The callback this promise will
	 *                                                   call when complete.
	 * @param array                           $then_args An array of arguments that will be passed to the then callback.
	 *                                                   }
	 *
	 *
	 * @return bool `true` if the task needs to run again, `false` if the task is complete.
	 */
	protected function task( $item ) {
		if ( isset( $item['resolved'] ) ) {
			$this->resolved = $item['resolved'];
			if ( isset( $item['resolved_args'] ) ) {
				$this->resolved_args = $item['resolved_args'];
			}
		}

		if ( isset( $item['rejected'] ) ) {
			$this->rejected = $item['rejected'];
			if ( isset( $item['rejected_args'] ) ) {
				$this->rejected_args = $item['rejected_args'];
			}
		}

		$callback_args = isset( $item['args'] ) ? $item['args'] : null;
		$done          = $this->do_callback( $item['callback'], $callback_args );

		// If we are done then return `false` to indicate "no need to run again".
		return $done ? false : true;
	}

	/**
	 * Overrides the base method to allow building promises on empty objects
	 * without actually writing to the database.
	 *
	 * A fake queue id is set for compatibility reasons.
	 *
	 * @since 4.9.5
	 *
	 * @return Tribe__Process__Queue This object.
	 */
	public function save() {
		if ( empty( $this->data ) ) {
			$this->id = uniqid( 'promise_', true );

			return $this;
		}

		return parent::save();
	}

	/**
	 * Invokes a callback function with optional arguments.
	 *
	 * If the callback invocation results in an exception or error  then the callback will return `true`
	 * and log.
	 *
	 * @since 4.9.5
	 *
	 * @param            callable|Tribe__Utils__Callback $callback      The callback to call.
	 * @param array|null                                 $callback_args An optional array of arguments to call the
	 *                                                                  callback with.
	 *
	 * @return mixed The callback invocation return value.
	 */
	protected function do_callback( $callback, array $callback_args = null ) {
		try {
			set_error_handler( [ $this, 'error_handler' ] );

			$callback = $this->unpack_callback( $callback );

			if ( count( $callback_args ) ) {
				$done = call_user_func_array( $callback, $callback_args );
			} else {
				$done = call_user_func( $callback );
			}

			restore_error_handler();

			return $done;
		} catch ( Exception $e ) {
			$message = 'Exception (' . get_class( $e ) . ') thrown while invoking a promise callback:';
			$message .= "\n\t" . $e->getMessage();
			tribe( 'logger' )->log( $message, Tribe__Log::ERROR, __CLASS__ );

			$this->resolved_correctly = false;
			$this->complete();

			return true;
		}
	}

	/**
	 * Unpacks a callback returning a callable array for callbacks wrapped using the
	 * Tribe__Utils__Callback class.
	 *
	 * @since 4.9.5
	 *
	 * @param string|array|Tribe__Utils__Callback $callback The callback to unpack.
	 *
	 * @return array|string A callable array of string.
	 */
	protected function unpack_callback( $callback ) {
		if ( $callback instanceof Tribe__Utils__Callback ) {
			$callback = array( tribe( $callback->get_slug() ), $callback->get_method() );
		}

		return $callback;
	}

	/**
	 * Overrides the base method to call the success callback on completion.
	 *
	 * @since 4.9.5
	 */
	protected function complete() {
		parent::complete();

		if ( $this->resolved_correctly && null !== $this->resolved ) {
			$callback_args = isset( $this->resolved_args ) ? $this->resolved_args : null;
			$this->do_callback( $this->resolved, $callback_args );
		} elseif ( ! $this->resolved_correctly && null !== $this->rejected ) {
			$callback_args = isset( $this->rejected_args ) ? $this->rejected_args : null;
			$this->do_callback( $this->rejected, $callback_args );
		}
	}

	/**
	 * An alias of the dispatch method to stick with the expected naming
	 * standard.
	 *
	 * @since 4.9.5
	 *
	 * @return mixed The dispatch operation return value.
	 */
	public function resolve() {
		return $this->dispatch();
	}
}