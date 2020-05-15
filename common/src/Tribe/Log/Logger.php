<?php
/**
 * Specifies the minimal interface required of all logging implementations.
 */
interface Tribe__Log__Logger {
	/**
	 * Indicates if the logger will work in the current environment.
	 *
	 * @return bool
	 */
	public function is_available();

	/**
	 * Returns a 'human friendly' name for the logging implementation.
	 *
	 * @return string
	 */
	public function get_name();

	/**
	 * Responsible for commiting the entry to the log (but only if the debug level
	 * is appropriate).
	 *
	 * @param string $entry
	 * @param string $type
	 * @param string $src
	 */
	public function log( $entry, $type = Tribe__Log::DEBUG, $src = '' );

	/**
	 * Retrieve up to $limit most recent log entries in reverse chronological
	 * order. If $limit is a negative or zero value, there is no limit.
	 *
	 * Implementation-specific arguments can optionally be provided as a second
	 * parameter. This may include support for a 'log' param where an identifer
	 * obtained via the list_availalbe_logs() method is passed in order to query
	 * a specific archived log.
	 *
	 * @see Tribe__Log__Logger::list_available_logs()
	 *
	 * @param int   $limit
	 * @param array $args
	 *
	 * @return array
	 */
	public function retrieve( $limit = 0, array $args = array() );

	/**
	 * Returns a list of currently accessible logs (current first, oldest last).
	 *
	 * This can be useful if, for instance, a particular logger organizes logging
	 * by dates and keeps an archive of upto 1 week's worth of logs - in which case
	 * the array might look like:
	 *
	 *     [ '2016-12-31',
	 *       '2016-12-30',
	 *       '2016-12-30',
	 *       '2016-12-30',
	 *       '2016-12-30', ... ]
	 *
	 * Note that a) the array may be empty and b) it won't necessarily contain
	 * date strings, it could contain identifiers like 'current', 'prev', 'prev2'
	 * or really anything the logging engine prefers.
	 *
	 * @return array
	 */
	public function list_available_logs();

	/**
	 * Switches to the specified log.
	 *
	 * If optional param $create is true the logger will try to create a new log
	 * using the provided identifier if it doesn't already exist.
	 *
	 * @param mixed $log_identifier
	 * @param bool $create
	 *
	 * @return bool
	 */
	public function use_log( $log_identifier, $create = false );


	/**
	 * Performs routine maintenance and cleanup work (such as log rotation)
	 * whenever it is called.
	 */
	public function cleanup();
}
