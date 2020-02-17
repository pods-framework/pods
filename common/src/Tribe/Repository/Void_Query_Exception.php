<?php

/**
 * Class Tribe__Repository__Void_Query_Exception
 *
 * Thrown to indicate that a query would yield no results in read, write, delete or update operations.
 *
 * Repository implementations should handle this exception gracefully as
 * a signal, not an error.
 *
 * @since 4.7.19
 */
class Tribe__Repository__Void_Query_Exception extends Exception {

	/**
	 * Indicates that query would yield no results.
	 *
	 * @since 4.7.19
	 *
	 * @param string $reason
	 *
	 * @return Tribe__Repository__Void_Query_Exception
	 */
	public static function because_the_query_would_yield_no_results( $reason ) {
		return new self( "The query would yield no results due to {$reason}, this exception should be handled" );
	}
}
