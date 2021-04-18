<?php
/**
 * Provides methods to acquire and release a database (SQL) lock using the `Tribe\DB_Lock` class.
 *
 * @since   4.12.6
 *
 * @package Tribe\Traits
 */

namespace Tribe\Traits;

/**
 * Trait With_Db_Lock
 *
 * @since   4.12.6
 *
 * @package Tribe\Traits
 */
trait With_DB_Lock {

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
	private function acquire_db_lock( $lock_key ) {
		return tribe( 'db-lock' )->acquire_db_lock( $lock_key );
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
	private function release_db_lock( $lock_key ) {
		return tribe( 'db-lock' )->release_db_lock( $lock_key );
	}
}
