<?php

/**
 * Class Tribe__Process__Queue
 *
 * @since 4.7.12
 * @since 4.9.5 Removed dependency on `WP_Background_Process` class.
 *
 * The base class to process queues asynchronously.
 */
abstract class Tribe__Process__Queue extends Tribe__Process__Handler {

	/**
	 * A constant to allow some "sugar" while using the processing system.
	 * Returning `false` to indicate the successful processing of an item might
	 * not be intuitive.
	 */
	const ITEM_DONE = false;

	/**
	 * The default action name.
	 *
	 * @var string
	 */
	protected $action = 'background_process';

	/**
	 * Start time of current process.
	 *
	 * @var int
	 */
	protected $start_time = 0;

	/**
	 * The process Cron_hook_identifier.
	 *
	 * @var mixed
	 */
	protected $healthcheck_cron_hook_id;

	/**
	 * The process cron interval identifier.
	 *
	 * @var mixed
	 */
	protected $healthcheck_cron_interval_id;

	/**
	 * @var string The common identified prefix to all our async process handlers.
	 */
	protected $prefix = 'tribe_queue';

	/**
	 * @var string The base that should be used to build the queue id.
	 */
	protected $id_base;

	/**
	 * @var string The queue unique identifier
	 */
	protected $id;

	/**
	 * @var int How many items this instance processed.
	 */
	protected $done = 0;

	/**
	 * @var int
	 */
	protected $original_batch_count = 0;

	/**
	 * @var int The maximum size of a fragment in bytes.
	 */
	protected $max_frag_size;

	/**
	 * @var bool Whether the current handling is sync or not.
	 */
	protected $doing_sync = false;

	/**
	 * @var bool Whether the queue `save` method was already called or not.
	 */
	protected $did_save = false;

	/**
	 * @var string The batch key used by the queue.
	 */
	protected $batch_key;

	/**
	 * An instance of the feature detection abstraction object.
	 *
	 * @var Tribe__Feature_Detection
	 */
	protected $feature_detection;

	/**
	 * The default lock time for a queued process.
	 *
	 * @var int
	 */
	protected $queue_lock_time = 60;

	/**
	 * The amount, in seconds, to check on the queue health.
	 *
	 * @var int
	 */
	protected $healthcheck_cron_interval = 5;

	/**
	 * Tribe__Process__Queue constructor.
	 *
	 * @since 4.7.12
	 * @since 4.9.5 Pulled method code from the `WP_Background_Process` class.
	 */
	public function __construct() {
		$class        = get_class( $this );
		$this->action = call_user_func( [ $class, 'action' ] );
		$this->feature_detection = tribe( 'feature-detection' );

		parent::__construct();

		$this->healthcheck_cron_hook_id     = $this->identifier . '_cron';
		$this->healthcheck_cron_interval_id = $this->identifier . '_cron_interval';

		add_action( $this->healthcheck_cron_hook_id, [ $this, 'handle_cron_healthcheck' ] );
		add_filter( 'cron_schedules', [ $this, 'schedule_cron_healthcheck' ] );

		/*
		 * This object might have been built while processing crons so
		 * we hook on the the object cron identifier to handle the task
		 * if the cron-triggered action ever fires.
		 */
		add_action( $this->identifier, [ $this, 'maybe_handle' ] );
	}

	/**
	 * Stops a queue that might be running.
	 *
	 * The queue process results are not rolled back (e.g. 200 posts to create, stopped
	 * after 50, those 50 posts will persist).
	 *
	 * @since 4.7.12
	 *
	 * @param string $queue_id The unique identifier of the queue that should be stopped.
	 *
	 * @see   Tribe__Process__Queue::save() to get the queue unique id.
	 *
	 * @return bool Whether the queue was correctly stopped, and its information
	 *              deleted, or not.
	 */
	public static function stop_queue( $queue_id ) {
		$meta = (array) get_transient( $queue_id . '_meta' );
		delete_transient( $queue_id . '_meta' );

		if ( ! empty( $meta['identifier'] ) ) {
			delete_transient( $meta['identifier'] . '_process_lock' );
		}

		return delete_option( $queue_id );
	}

	/**
	 * Whether a queue process is stuck or not.
	 *
	 * A queue process that has not been doing anything for an amount
	 * of time is considered "stuck".
	 *
	 * @since 4.7.18
	 *
	 * @param string $queue_id The queue process unique identifier.
	 *
	 * @return bool
	 */
	public static function is_stuck( $queue_id ) {
		$queue_status = self::get_status_of( $queue_id );
		$is_stuck     = false;

		/**
		 * Filters the maximum allowed time a queue process can go without updates
		 * before being considered stuck.
		 *
		 * @since 4.7.18
		 *
		 * @param int $time_limit A value in seconds, defaults to 5'.
		 */
		$limit = (float) apply_filters( 'tribe_process_queue_time_limit', 300 );

		if ( ! empty( $queue_status['last_update'] ) && is_numeric( $queue_status['last_update'] ) ) {
			$is_stuck = time() - (int) $queue_status['last_update'] > $limit;
		} else {
			$queue_status['last_update'] = time();
			set_transient( $queue_id . '_meta', $queue_status->to_array(), DAY_IN_SECONDS );
		}

		/**
		 * Filters whether a queue is considered "stuck" or not.
		 *
		 * @since 4.7.18
		 *
		 * @param bool $is_stuck
		 * @param string $queue_id
		 * @param Tribe__Data $queue_status
		 */
		return apply_filters( 'tribe_process_queue_is_stuck', $is_stuck, $queue_id, $queue_status );
	}

	/**
	 * Returns a queue status and information.
	 *
	 * @since 4.7.12
	 *
	 * @param string $queue_id
	 *
	 * @return Tribe__Data An object containing information about the queue.
	 *
	 * @see   Tribe__Process__Queue::save() to get the queue unique id.
	 */
	public static function get_status_of( $queue_id ) {
		$meta = (array) get_transient( $queue_id . '_meta' );
		$data = [
			'identifier'  => $queue_id,
			'done'        => (int) Tribe__Utils__Array::get( $meta, 'done', 0 ),
			'total'       => (int) Tribe__Utils__Array::get( $meta, 'total', 0 ),
			'fragments'   => (int) Tribe__Utils__Array::get( $meta, 'fragments', 0 ),
			'last_update' => (int) Tribe__Utils__Array::get( $meta, 'last_update', false ),
		];

		return new Tribe__Data( $data, 0 );
	}

	/**
	 * Deletes all queues for a specific action.
	 *
	 * @since 4.7.19
	 *
	 * @param string $action The action (prefix) of the queues to delete.
	 *
	 * @return int The number of delete queues.
	 */
	public static function delete_all_queues( $action ) {
		global $wpdb;

		$action = $wpdb->esc_like( 'tribe_queue_' . $action ) . '%';

		$queues = $wpdb->get_col( $wpdb->prepare( "
			SELECT DISTINCT(option_name)
			FROM {$wpdb->options}
			WHERE option_name LIKE %s
		", $action ) );

		if ( empty( $queues ) ) {
			return 0;
		}

		$deleted = 0;

		foreach ( $queues as $queue ) {
			$deleted ++;
			self::delete_queue( $queue );
		}

		return $deleted;
	}

	/**
	 * {@inheritdoc}
	 */
	public function delete( $key ) {
		self::delete_queue( $key );

		return $this;
	}

	/**
	 * Deletes a queue batch(es) and meta information.
	 *
	 * @since 4.7.18
	 *
	 * @param string $key
	 */
	public static function delete_queue( $key ) {
		global $wpdb;

		$meta_key = $key . '_meta';

		$key = $wpdb->esc_like( $key ) . '%';

		$wpdb->query( $wpdb->prepare( "
			DELETE
			FROM {$wpdb->options}
			WHERE option_name LIKE %s
		", $key ) );

		delete_transient( $meta_key );
	}

	/**
	 * Upates the queue and meta data for the process.
	 *
	 * @since 4.7.12
	 * @since 4.9.5 Pulled method from the `WP_Background_Process` class.
	 *
	 * @param string $key The key of the data to save.
	 * @param array  $data The data to save.
	 *
	 * @return $this This process instance.
	 */
	public function update( $key, $data ) {
		$meta_key = $this->get_meta_key( $key );
		$meta     = (array) get_transient( $meta_key );
		$done     = $this->original_batch_count - count( $data );

		$update_data = array_merge( $meta, [
			'done'        => $meta['done'] + $done,
			'last_update' => time(),
		] );

		/**
		 * Filters the information that will be updated in the database for this queue type.
		 *
		 * @since 4.7.12
		 *
		 * @param array $update_data
		 * @param self $this
		 */
		$update_data = apply_filters( "tribe_process_queue_{$this->identifier}_update_data", $update_data, $this );

		set_transient( $meta_key, $update_data, DAY_IN_SECONDS );

		if ( ! empty( $data ) ) {
			update_option( $key, $data );
		}

		return $this;
	}

	/**
	 * Returns the name of the transient that will store the queue meta information
	 * for the specific key.
	 *
	 * @since 4.7.12
	 *
	 * @param string $key
	 *
	 * @return string
	 */
	public function get_meta_key( $key ) {
		$key = preg_replace( '/^(.*)_\\d+$/', '$1', $key );

		return $key . '_meta';
	}

	/**
	 * {@inheritdoc}
	 */
	public function save() {
		$key = $this->generate_key();

		$fragments_count = $this->save_split_data( $key, $this->data );

		$save_data = [
			'identifier'  => $this->identifier,
			'done'        => 0,
			'total'       => count( $this->data ),
			'fragments'   => $fragments_count,
			'last_update' => time(),
		];

		/**
		 * Filters the information that will be saved to the database for this queue type.
		 *
		 * @since 4.7.12
		 *
		 * @param array $save_data
		 * @param self $this
		 */
		$save_data = apply_filters( "tribe_process_queue_{$this->identifier}_save_data", $save_data, $this );

		set_transient( $this->get_meta_key( $key ), $save_data );

		$this->did_save = true;
		$this->id       = $key;

		return $this;
	}

	/**
	 * Generates the unique key for the queue optionally using the client provided
	 * id.
	 *
	 * @since 4.7.12
	 *
	 * @param int $length The lengthy of the key to generate, longer keys will
	 *                    add more entropy; default to 64.
	 *
	 * @return string The generated batch key.
	 */
	protected function generate_key( $length = 64 ) {
		if ( empty( $this->id_base ) ) {
			$this->id_base = md5( microtime() . mt_rand() );
		}

		$prepend = $this->identifier . '_batch_';

		$this->batch_key = substr( $prepend . $this->id_base, 0, $length );

		return $this->batch_key;
	}

	/**
	 * Saves the queue data to the database taking max_packet_size into account.
	 *
	 * In some instances the serialized size of the data might be bigger than the
	 * database `max_packet_size`; trying to write all the data in one query would
	 * make the db "go away...".
	 * Here we try to read the database `max_packet_size` setting and use that information
	 * to avoid overloading the query.
	 *
	 * @param       string $key
	 * @param array $data
	 *
	 * @return int The number of fragments the data was split and stored into.
	 */
	protected function save_split_data( $key, array $data ) {
		if ( empty( $data ) ) {
			return 0;
		}

		$max_frag_size = $this->get_max_frag_size();
		// we add a 15% to the size to take the serialization and query overhead into account when fragmenting
		$serialized_size = strlen( utf8_decode( maybe_serialize( $data ) ) ) * 1.15;
		$frags_count     = (int) ceil( $serialized_size / $max_frag_size );
		$per_frag        = max( (int) floor( count( $data ) / $frags_count ), 1 );

		$split_data = array_chunk( $data, $per_frag );

		if ( empty( $split_data ) ) {
			return 0;
		}

		foreach ( $split_data as $i => $iValue ) {
			$postfix = 0 === $i ? '' : "_{$i}";
			update_option( $key . $postfix, $split_data[ $i ] );
		}

		return count( $split_data );
	}

	/**
	 * Returns the max frag size in bytes.
	 *
	 * The bottleneck here is the database `max_packet_size` so we try to read
	 * it from the database.
	 *
	 * @return int The max size, in bytes, of a data fragment.
	 */
	protected function get_max_frag_size() {
		if ( ! empty( $this->max_frag_size ) ) {
			return $this->max_frag_size;
		}

		return tribe( 'db' )->get_max_allowed_packet_size();
	}

	/**
	 * Sets the maximum size, in bytes, of the queue fragments.
	 *
	 * This will prevent the class from trying to read the value from the database.
	 *
	 * @since 4.7.12
	 *
	 * @param int $max_frag_size
	 */
	public function set_max_frag_size( $max_frag_size ) {
		$this->max_frag_size = $max_frag_size;
	}

	/**
	 * Returns the queue unique identifier.
	 *
	 * Mind that an id will only be available after saving a queue.
	 *
	 * @since 4.7.12
	 *
	 * @return string
	 * @throws RuntimeException if trying to get the queue id before saving it.
	 */
	public function get_id() {
		if ( null === $this->id ) {
			// not localized as this is a developer-land error
			throw new RuntimeException( 'Can only get the id of queue after saving it.' );
		}

		return $this->id;
	}

	/**
	 * Sets the queue unique id.
	 *
	 * When using this method the client code takes charge of the queue id uniqueness;
	 * the class will not check it.
	 *
	 * @since 4.7.12
	 *
	 * @param string $queue_id
	 *
	 * @throws RuntimeException If trying to set the queue id after saving it.
	 */
	public function set_id( $queue_id ) {
		if ( $this->did_save ) {
			throw new RuntimeException( 'The queue id can be set only before saving it.' );
		}

		$queue_id = preg_replace( '/^' . preg_quote( $this->identifier, '/' ) . '_batch_/', '', $queue_id );

		$this->id_base = $queue_id;
	}

	/**
	 * Overrides the base `dispatch` method to allow for constants and/or environment vars to run
	 * async requests in sync mode.
	 *
	 * @since 4.7.12
	 * @since 4.9.5 Pulled method code from the `WP_Background_Process` class.
	 *
	 * @return mixed
	 */
	public function dispatch() {
		if (
			( defined( 'TRIBE_NO_ASYNC' ) && true === TRIBE_NO_ASYNC )
			|| true === (bool) getenv( 'TRIBE_NO_ASYNC' )
			|| (bool) tribe_get_request_var( 'tribe_queue_sync', false )
			|| tribe_is_truthy( tribe_get_option( 'tribe_queue_sync', false ) )
		) {
			$result = $this->sync_process();
			$this->complete();

			return $result;
		}

		if ( $this->feature_detection->supports_async_process() ) {
			// Schedule the cron health-check.
			$this->schedule_event();

			// Perform remote post.
			return parent::dispatch();
		}

		/*
		 * If async AJAX-based processing is not available then we "dispatch"
		 * by scheduling a single cron event immediately (as soon as possible)
		 * for this handler cron identifier.
		 */
		if ( ! wp_next_scheduled( $this->identifier ) ) {
			// Schedule the event to happen as soon as possible.
			$scheduled = wp_schedule_single_event( time() - 1, $this->identifier );

			if ( false === $scheduled ) {
				/** @var Tribe__Log__Logger $logger */
				$logger = tribe( 'logger' );
				$class  = get_class( $this );
				$src    = call_user_func( [ $class, 'action' ] );
				$logger->log( 'Could not schedule event for cron-based processing', Tribe__Log::ERROR, $src );
			}
		}

		return true;
	}

	/**
	 * Handles the process immediately, not in an async manner.
	 *
	 * @since 4.7.12
	 *
	 * @return array An array containing the result of each item handling.
	 */
	public function sync_process() {
		$result           = [];
		$this->doing_sync = true;

		foreach ( $this->data as $item ) {
			$result[] = $this->task( $item );
		}

		return $result;
	}

	/**
	 * Returns the name of the option used by the queue to store its batch(es).
	 *
	 * Mind that this value will be set only when first saving the queue and it will not be set
	 * in following queue processing.
	 *
	 * @since 4.7.12
	 *
	 * @param int $n The number of a specific batch option name to get; defaults to `0` to get the
	 *               option name of the first one.
	 *
	 * @return string
	 *
	 * @throws RuntimeException If trying to get the value before saving the queue or during following
	 *                          processing.
	 */
	public function get_batch_key( $n = 0 ) {
		if ( null === $this->batch_key || ! $this->did_save ) {
			throw new RuntimeException( 'The batch key will only be set after the queue is first saved' );
		}

		return empty( $n ) ? $this->batch_key : $this->batch_key . '_' . (int) $n;
	}

	/**
	 * Returns the queue action identifier.
	 *
	 * @since 4.9.5 Pulled from the `WP_Background_Process` class.
	 *
	 * @return string The queue action identifier.
	 */
	public function get_identifier() {
		return $this->identifier;
	}

	/**
	 * Returns a batch of items to process from the queue.
	 *
	 * @since 4.7.12
	 * @since 4.9.5 Pulled method code from the `WP_Background_Process` class.
	 *
	 * @return stdClass The first batch of items from the queue.
	 */
	protected function get_batch() {
		global $wpdb;

		$key = $wpdb->esc_like( $this->identifier . '_batch_' ) . '%';

		$query = $wpdb->get_row( $wpdb->prepare( "
			SELECT *
			FROM {$wpdb->options}
			WHERE option_name LIKE %s
			ORDER BY option_id ASC
			LIMIT 1
		", $key ) );

		$batch       = new stdClass();
		$batch->key  = $query->option_name;
		$batch->data = maybe_unserialize( $query->option_value );

		$this->original_batch_count = ! empty( $batch->data ) ? count( $batch->data ) : 0;

		return $batch;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_post_args() {
		$post_args = parent::get_post_args();

		/**
		 * While sending the data into the body makes sense for the async process it does
		 * not make sense when processing a queue since the data will be stored and read
		 * from the database; furthermore this could raise issues with the max POST size.
		 */
		$post_args['body'] = [];

		return $post_args;
	}

	/**
	 * Maybe handle the process request in async or sync mode depending on the
	 * supported mode.
	 *
	 * @param array|null $data_source An optional data source.
	 *
	 * @since 4.9.5
	 */
	public function maybe_handle( $data_source = null ) {
		// Don't lock up other requests while processing
		session_write_close();

		if ( $this->feature_detection->supports_async_process() ) {
			return $this->maybe_handle_async();
		}

		return $this->maybe_handle_sync();
	}

	/**
	 * Push an item to the process queue.
	 *
	 * @since 4.9.5 Pulled from the `WP_Background_Process` class.
	 *
	 * @param mixed $data An item to process.
	 *
	 * @return $this This process instance.
	 */
	public function push_to_queue( $data ) {
		$this->data[] = $data;

		return $this;
	}

	/**
	 * Maybe handle this process request in async mode.
	 *
	 * @since 4.9.5
	 */
	protected function maybe_handle_async() {
		if ( $this->is_process_running() ) {
			// Background process already running.
			wp_die();
		}

		if ( $this->is_queue_empty() ) {
			// No data to process: we're done.
			$this->complete();
			wp_die();
		}

		check_ajax_referer( $this->identifier, 'nonce' );

		$this->handle();

		wp_die();
	}

	/**
	 * Handle the process request in sync mode.
	 *
	 * @since 4.9.5
	 */
	protected function maybe_handle_sync() {
		if ( $this->is_process_running() ) {
			// Background process already running.
			return;
		}

		if ( $this->is_queue_empty() ) {
			// No data to process: we're done.
			$this->complete();

			return;
		}

		$this->handle();
	}

	/**
	 * Checks whether the queue is empty or not.
	 *
	 * @since 4.9.5 Pulled from the `WP_Background_Process` class.
	 *
	 * @return bool Whether the queue is empty or not.
	 */
	protected function is_queue_empty() {
		global $wpdb;

		$key = $wpdb->esc_like( $this->identifier . '_batch_' ) . '%';

		$count = $wpdb->get_var( $wpdb->prepare( "
			SELECT COUNT(*)
			FROM {$wpdb->options}
			WHERE option_name LIKE %s
		", $key ) );

		return $count <= 0;
	}

	/**
	 * Checks whether the process is currently running or not.
	 *
	 * @since 4.9.5 Pulled from the `WP_Background_Process` class.
	 */
	protected function is_process_running() {
		if ( get_transient( $this->identifier . '_process_lock' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Locks the process so that other instances cannot spawn and run.
	 *
	 * Lock the process so that multiple instances can't run simultaneously.
	 * Override if applicable, but the duration should be greater than that
	 * defined in the `time_exceeded()` method.
	 *
	 * @since 4.9.5 Pulled from the `WP_Background_Process` class.
	 */
	protected function lock_process() {
		// Set start time of current process.
		$this->start_time = time();

		$lock_duration = $this->queue_lock_time;

		/**
		 * Filters the duration of the lock acquired by a process instance.
		 *
		 * The lock duration should be larger than the maximum time a process is allowed to run.
		 *
		 * @since 4.9.5
		 *
		 * @param int    $lock_duration The lock duration in seconds; defaults to one minute.
		 * @param static $this          This process instance.
		 */
		$lock_duration = apply_filters( $this->identifier . '_queue_lock_time', $lock_duration, $this );

		set_transient( $this->identifier . '_process_lock', microtime(), $lock_duration );
	}

	/**
	 * Releases the process lock so that other instances can spawn and run.
	 *
	 * @since 4.9.5 Pulled from the `WP_Background_Process` class.
	 *
	 * @return $this This process instance.
	 */
	protected function unlock_process() {
		delete_transient( $this->identifier . '_process_lock' );

		return $this;
	}

	/**
	 * Handles the process request.
	 *
	 * Pass each queue item to the task handler, while remaining
	 * within server memory and time limit constraints.
	 *
	 * @since 4.9.5 Pulled from the `WP_Background_Process` class.
	 *
	 * @param array|null $data_source Unused and kept for compatibility with parent; the queue
	 *                                data is stored and read from the database.
	 */
	protected function handle( array $data_source = null ) {
		$this->lock_process();

		do {
			$batch = $this->get_batch();

			foreach ( $batch->data as $key => $value ) {
				$task = $this->task( $value );

				if ( false !== $task ) {
					$batch->data[ $key ] = $task;
				} else {
					unset( $batch->data[ $key ] );
				}

				if ( $this->time_exceeded() || $this->memory_exceeded() ) {
					// Batch limits reached.
					break;
				}
			}

			// Update or delete current batch.
			if ( ! empty( $batch->data ) ) {
				$this->update( $batch->key, $batch->data );
			} else {
				$this->delete( $batch->key );
			}
		} while ( ! $this->time_exceeded() && ! $this->memory_exceeded() && ! $this->is_queue_empty() );

		$this->unlock_process();

		// Start next batch or complete process.
		if ( ! $this->is_queue_empty() ) {
			$this->dispatch();
		} else {
			$this->complete();
		}

		if ( doing_action( $this->identifier ) ) {
			/*
			 * We're probably acting in the context of a cron request or
			 * in the context of an explicitly triggered action: let's not
			 * die.
			 */
			return;
		}

		wp_die();
	}

	/**
	 * Checks whether the memory limit was exceeded.
	 *
	 * Ensures the batch process never exceeds 90%
	 * of the maximum WordPress memory.
	 *
	 * @since 4.9.5 Pulled from the `WP_Background_Process` class.
	 *
	 * @return bool
	 */
	protected function memory_exceeded() {
		$memory_limit   = $this->get_memory_limit() * 0.9; // 90% of max memory
		$current_memory = memory_get_usage( true );
		$return         = false;

		if ( $current_memory >= $memory_limit ) {
			$return = true;
		}

		/**
		 * Filters whether the process did exceed the allowed memory limit or not.
		 *
		 * @since 4.9.5
		 *
		 * @param bool   $return Whether the process did exceed the allowed memory limit or not.
		 * @param static $this   This process instance.
		 */
		return apply_filters( $this->identifier . '_memory_exceeded', $return, $this );
	}

	/**
	 * Returns the memory limit for this process.
	 *
	 * @since 4.9.5 Pulled from the `WP_Background_Process` class.
	 *
	 * @return int The memory limit in bytes.
	 */
	protected function get_memory_limit() {
		if ( function_exists( 'ini_get' ) ) {
			$memory_limit = ini_get( 'memory_limit' );
		} else {
			// Sensible default.
			$memory_limit = '128M';
		}

		if ( ! $memory_limit || -1 === (int) $memory_limit ) {
			// Unlimited, set to 32GB.
			$memory_limit = '32000M';
		}

		return (int) $memory_limit * 1024 * 1024;
	}

	/**
	 * Checks whether the execution time was exceeded or not.
	 *
	 * Ensures the batch never exceeds a sensible time limit.
	 * A timeout limit of 30s is common on shared hosting.
	 *
	 * @since 4.9.5 Pulled from the `WP_Background_Process` class.
	 *
	 * @return bool Whether the execution time was exceeded or not.
	 */
	protected function time_exceeded() {
		/**
		 * Filters the maximum time the process can operate before continuing in another
		 * request.
		 * We pick a safe default of 20 seconds but this value can be adjusted to suit the system
		 * timeout settings.
		 *
		 * @since 4.9.5
		 *
		 * @param int    $default_time_limit The time limit for the process.
		 * @param static $this               This process instance.
		 */
		$time_limit = apply_filters( $this->identifier . '_default_time_limit', 20, $this );

		$finish = $this->start_time + $time_limit;
		$return = false;

		if ( time() >= $finish ) {
			$return = true;
		}

		/**
		 * Filters whether a process instance should be marked as having exceeded the time limit or not.
		 *
		 * @since 4.9.5
		 *
		 * @param bool   $return Whether the process did exceed the time limit or not.
		 * @param static $this   This process instance.
		 */
		return apply_filters( $this->identifier . '_time_exceeded', $return );
	}

	/**
	 * Completes the processing, cleaning up after it.
	 *
	 * Override if applicable, but ensure that the below actions are
	 * performed, or, call parent::complete().
	 *
	 * @since 4.9.5 Pulled from the `WP_Background_Process` class.
	 */
	protected function complete() {
		// Unschedule the cron health-check.
		$this->clear_scheduled_event();
	}

	/**
	 * Schedules a cron-based health-check to restart the queue if stuck.
	 *
	 * Filters the `cron_schedules` filter to add a check every 5 minutes.
	 *
	 * @since 4.9.5 Pulled from the `WP_Background_Process` class.
	 *
	 * @param mixed $schedules The cron schedules to check.
	 *
	 * @return mixed The updated cron schedules.
	 */
	public function schedule_cron_healthcheck( $schedules ) {
		/**
		 * Filters the number of minutes to schedule the cron health-check.
		 *
		 * @since 4.9.5
		 *
		 * @param int    $interval The number of minutes to schedule the cron health-check; defaults to 5.
		 * @param static $this     This process instance.
		 */
		$interval = apply_filters( $this->identifier . '_cron_interval', $this->healthcheck_cron_interval, $this );

		// Adds every 5 minutes to the existing schedules.
		$schedules[ $this->identifier . '_cron_interval' ] = [
			'interval' => MINUTE_IN_SECONDS * $interval,
			'display'  => sprintf( __( 'Every %d Minutes', 'tribe-common' ), $interval ),
		];

		return $schedules;
	}

	/**
	 * Handles the cron health-check.
	 *
	 * Restart the background process if not already running
	 * and data exists in the queue.
	 *
	 * @since 4.9.5 Pulled from the `WP_Background_Process` class.
	 */
	public function handle_cron_healthcheck() {
		if ( $this->is_process_running() ) {
			// Background process already running.
			exit;
		}

		if ( $this->is_queue_empty() ) {
			// No data to process.
			$this->clear_scheduled_event();
			exit;
		}

		$this->handle();

		exit;
	}

	/**
	 * Schedules the cron health-check event.
	 *
	 * @since 4.9.5 Pulled from the `WP_Background_Process` class.
	 */
	protected function schedule_event() {
		if ( ! wp_next_scheduled( $this->healthcheck_cron_hook_id ) ) {
			wp_schedule_event( time(), $this->healthcheck_cron_interval_id, $this->healthcheck_cron_hook_id );
		}
	}

	/**
	 * Clears the scheduled health-check cron event.
	 *
	 * @since 4.9.5 Pulled from the `WP_Background_Process` class.
	 */
	protected function clear_scheduled_event() {
		$timestamp = wp_next_scheduled( $this->healthcheck_cron_hook_id );

		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, $this->healthcheck_cron_hook_id );
		}
	}

	/**
	 * Cancels the current process.
	 *
	 * Stops processing queue items and clean up.
	 *
	 * @since 4.9.5 Pulled from the `WP_Background_Process` class.
	 */
	public function cancel_process() {
		if ( ! $this->is_queue_empty() ) {
			$batch = $this->get_batch();

			$this->delete( $batch->key );

			wp_clear_scheduled_hook( $this->healthcheck_cron_hook_id );
		}

	}

	/**
	 * Executes the process task on a single item.
	 *
	 * Override this method to perform any actions required on each
	 * queue item. Return the modified item for further processing
	 * in the next pass through. Or, return false to remove the
	 * item from the queue.
	 *
	 * @since 4.9.5 Pulled from the `WP_Background_Process` class.
	 *
	 * @param mixed $item Queue item to iterate over.
	 *
	 * @return mixed
	 */
	abstract protected function task( $item );

	/**
	 * Concrete implementation of the base handler method.
	 *
	 * Just a proxy to the `sync_process` method.
	 *
	 * @since 4.9.5
	 *
	 * @param array|null $data_source If not provided the method will read the handler data from the
	 *                                request array.
	 *
	 * @return array|mixed|null The synchronous process result.
	 */
	public function sync_handle( array $data_source = null ) {
		// In the base implementation the data source is unused and read from the database.
		return $this->sync_process();
	}
}
