<?php
/**
 * Manages database locks using MySQL fucntions or queries.
 *
 * The MySQL functions used by this class are `GET_LOCK`, `IS_FREE_LOCK` and `RELEASE_LOCK`.
 * The functions are part of MySQL 5.6 and in line with WordPress minimum requirement of MySQL version (5.6).
 *
 * @see     https://dev.mysql.com/doc/refman/5.6/en/locking-functions.html#function_get-lock
 *
 * @since   4.12.6
 *
 * @package Tribe
 */

namespace Tribe;

/**
 * Class DB_Lock
 *
 * @since   4.12.6
 *
 * @package Tribe
 */
class DB_Lock {

	/**
	 * The prefix of the options used to manage the database lock without use of MySQL functions
	 * in the options table.
	 *
	 * @since 4.12.6
	 *
	 * @var string
	 */
	public static $db_lock_option_prefix = 'tribe_db_lock_';

	/**
	 * A map, shared among all instance of this trait in the session, of the currently held locks the
	 * time the locks where acquired, a UNIX timestamp w/ micro-seconds.
	 *
	 * @since 4.12.6
	 *
	 * @var array<string,float>
	 */
	protected static $held_db_locks = [];

	/**
	 * Prunes the stale locks stored in the options table.
	 *
	 * @since 4.12.6
	 *
	 * @return int|false The number of pruned locks, or `false` to indicate the query to prune the locks generated
	 *                   an error (logged).
	 */
	public static function prune_stale_db_locks() {
		global $wpdb;
		$prefix        = static::$db_lock_option_prefix;
		$affected_rows = $wpdb->query(
			"DELETE FROM {$wpdb->options}
				WHERE option_name LIKE '{$prefix}%'
				AND option_value < ( UNIX_TIMESTAMP() - 86400 )"
		);

		if ( false === $affected_rows ) {
			$log_data = [
				'message' => 'Error while trying to prune stale db locks.',
				'error'   => $wpdb->last_error
			];
			do_action( 'tribe_log', 'error', __CLASS__, $log_data );

			return false;
		}

		return (int) $affected_rows;
	}

	/**
	 * Acquires a db lock.
	 *
	 * To ensure back-compatibility with MySQL 5.6, the lock will hash the lock key using SHA1.
	 *
	 * @since 4.12.6
	 *
	 * @param string $lock_key The name of the db lock key to acquire.
	 *
	 * @return bool Whether the lock acquisition was successful or not.
	 */
	public function acquire_db_lock( $lock_key ) {
		/**
		 * Filters the timeout, in seconds, of the database lock acquisition attempts.
		 *
		 * The timeout will not be used when locks are managed using queries in place of
		 * MySQL functions.
		 *
		 * @since 4.12.6
		 *
		 * @param int    $timeout  The timeout, in seconds, of the lock acquisition attempt.
		 * @param string $lock_key The lock key the target of the acquisition attempt.
		 * @param static $this     The object that's trying to acquire the lock by means of the trait.
		 */
		$timeout = apply_filters( 'tribe_db_lock_timeout', 3, $lock_key, $this );

		if ( $this->manage_db_lock_w_mysql_functions() ) {
			return $this->acquire_db_lock_w_mysql_functions( $lock_key, $timeout );
		}

		return $this->acquire_db_lock_w_queries( $lock_key );
	}

	/**
	 * Returns whether the traits should try to acquire and release locks using MySQL `GET_LOCK` and `RELEASE_LOCK`
	 * functions or not.
	 *
	 * If not, then the trait will manage the locks by means of direct SQL queries on the options table.
	 *
	 * @since 4.12.6
	 *
	 * @return bool Whether the trait should use MySQL functions to manage the locks, or not.
	 */
	protected function manage_db_lock_w_mysql_functions() {
		/**
		 * Filters whether the database lock should be acquired using the `GET_LOCK` and `RELEASE_LOCK`
		 * MySQL functions or not.
		 *
		 * If the filter returns a falsy value, then the trait will attempt to manage locks using `SELECT`
		 * and `UPDATE` queries on the options table.
		 *
		 * @since 4.12.6
		 */
		return tribe_is_truthy( apply_filters( 'tribe_db_lock_use_msyql_functions', true ) );
	}

	/**
	 * Tries to acquire the database lock using MySQL functions (`GET_LOCK` and `IS_FREE_LOCK`).
	 *
	 * @since 4.12.6
	 *
	 * @param string $lock_key The lock key to try and acquire the lock for.
	 * @param int    $timeout  The timeout, in seconds, to try and acquire the lock.
	 *
	 * @return bool Whether the lock was acquired or not.
	 */
	protected function acquire_db_lock_w_mysql_functions( $lock_key, $timeout ) {
		/*
		 * On MySQL 5.6 if a session (a db connection) fires two requests of `GET_LOCK`, the lock is
		 * implicitly released and re-acquired.
		 * While this will not cause issues in the context of different db sessions (e.g. two diff. PHP
		 * processes competing for a lock), it would cause issues when the lock acquisition is attempted
		 * in the context of the same PHP process.
		 * To avoid a read-what-you-write issue in the context of the same request, we check if the lock is
		 * free, using `IS_FREE_LOCK` first.
		 */

		global $wpdb;

		$free = $wpdb->get_var(
			$wpdb->prepare( 'SELECT IS_FREE_LOCK( SHA1( %s ) )', $lock_key )
		);

		if ( ! $free ) {
			return false;
		}

		$acquired = $wpdb->get_var(
			$wpdb->prepare( 'SELECT GET_LOCK( SHA1( %s ),%d )', $lock_key, $timeout )

		);

		if ( false === $acquired ) {
			// Only log errors, a failure to acquire lock is not an error.
			$log_data = [
				'message' => 'Error while trying to acquire lock.',
				'key'     => $lock_key,
				'error'   => $wpdb->last_error
			];
			do_action( 'tribe_log', 'error', __CLASS__, $log_data );

			return false;
		}

		return true;
	}

	/**
	 * Tries to acquire the lock using SQL queries.
	 *
	 * This kind of lock does not support timeout to avoid sieging the MySQL server during processes
	 * that are most likely already stressing it. Either the lock is available the moment it's required or not.
	 * The method leverages `INSERT IGNORE` that it's available on MySQL 5.6 and is atomic provided one of the values
	 * we're trying to insert is UNIQUE or PRIMARY: `option_name` is UNIQUE in the `options` table.
	 *
	 * @since 4.12.6
	 *
	 * @param string $lock_key The lock key to try and acquire the lock for.
	 *
	 * @return bool Whether the lock was acquired or not.
	 */
	protected function acquire_db_lock_w_queries( $lock_key ) {
		global $wpdb;
		$option_name = $this->get_db_lock_option_name( $lock_key );
		$lock_time   = microtime( true );

		//phpcs:disable
		$rows_affected = $wpdb->query(
			$wpdb->prepare( "INSERT IGNORE INTO {$wpdb->options}
				(option_name, option_value, autoload)
				VALUES
				(%s, %s, 'no')",
				$option_name,
				$lock_time
			)
		);
		//phpcs:enable

		if ( false === $rows_affected ) {
			$log_data = [
				'message'     => 'Error while trying to acquire lock with database.',
				'key'         => $lock_key,
				'option_name' => $option_name,
				'error'       => $wpdb->last_error,
			];
			do_action( 'tribe_log', 'error', __CLASS__, $log_data );

			return false;
		}

		/*
		 * The `wpdb::query()` method will return the number of affected rows when using `INSERT`.
		 * 1 row affected means we could INSERT and have the lock, 0 rows affected means we could not INSERT
		 * and have not the lock.
		 */

		if ( $rows_affected ) {
			self::$held_db_locks[ $lock_key ] = $lock_time;
		}

		return (bool) $rows_affected;
	}

	/**
	 * Returns the option name used to manage the lock for a key in the options table.
	 *
	 * @since 4.12.6
	 *
	 * @param string $lock_key The lock key to build the option name for.
	 *
	 * @return string The name of the option that will be used to manage the lock for the specified key in the
	 *                options table.
	 */
	public function get_db_lock_option_name( $lock_key ) {
		return self::$db_lock_option_prefix . $lock_key;
	}

	/**
	 * Releases the database lock of the record.
	 *
	 * Release a not held db lock will return `null`, not `false`.
	 *
	 * @since 4.12.6
	 *
	 * @param string $lock_key The name of the lock to release.
	 *
	 * @return bool Whether the lock was correctly released or not.
	 */
	public function release_db_lock( $lock_key ) {
		if ( $this->manage_db_lock_w_mysql_functions() ) {
			return $this->release_db_lock_w_mysql_functions( $lock_key );
		}

		return $this->release_db_lock_w_queries( $lock_key );
	}

	/**
	 * Releases a DB lock held by the current database session (`$wpdb` instance) by
	 * using the MySQL `RELEASE_LOCK` function.
	 *
	 * @since 4.12.6
	 *
	 * @param string $lock_key The lock key to release the lock for.
	 *
	 * @return bool Whether the lock was correctly released or not.
	 */
	protected function release_db_lock_w_mysql_functions( $lock_key ) {
		global $wpdb;

		$released = $wpdb->query(
			$wpdb->prepare( "SELECT RELEASE_LOCK( SHA1( %s ) )", $lock_key )
		);

		if ( false === $released ) {
			$log_data = [
				'message' => 'Error while trying to release lock.',
				'key'     => $lock_key,
				'error'   => $wpdb->last_error
			];
			do_action( 'tribe_log', 'error', __CLASS__, $log_data );

			return false;
		}

		return true;
	}

	/**
	 * Releases a lock using SQL queries.
	 *
	 * Note: differently from the `release_db_lock_w_mysql_functions`, this method will release the lock
	 * even if the current session is not the one holding the lock.
	 * To protect from this the trait uses a map of registered locks and when the locks where registered.
	 *
	 * @since 4.12.6
	 *
	 * @param string $lock_key The lock key to release the lock for.
	 *
	 * @return bool Whether the lock was released or not, errors will be logged, a `false` value is returned if
	 *              the lock was not held to begin with.
	 */
	protected function release_db_lock_w_queries( $lock_key ) {
		if ( ! isset( self::$held_db_locks[ $lock_key ] ) ) {
			// Avoid sessions that do nothold the lock to release it.
			return false;
		}

		global $wpdb;
		$option_name = $this->get_db_lock_option_name( $lock_key );
		//phpcs:disable
		$rows_affected = $wpdb->delete(
			$wpdb->options,
			[ 'option_name' => $option_name ],
			[ '%s' ]
		);
		//phpcs:enable

		if ( false === $rows_affected ) {
			$log_data = [
				'message'     => 'Error while trying to release lock with database.',
				'key'         => $lock_key,
				'option_name' => $option_name,
				'error'       => $wpdb->last_error,
			];
			do_action( 'tribe_log', 'error', __CLASS__, $log_data );

			return false;
		}

		if ( $rows_affected ) {
			// Lock successfully released.
			unset( self::$held_db_locks[ $lock_key ] );
		}

		return (bool) $rows_affected;
	}
}
