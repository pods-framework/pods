<?php

/**
 * Class Tribe__Db
 *
 * Provides information about the database settings.
 */
class Tribe__Db {

	/**
	 * Gets the value of the `max_allowed_packet` setting.
	 *
	 * @since 4.7.12
	 *
	 * @return int
	 *
	 * @link  https://dev.mysql.com/doc/refman/5.7/en/packet-too-large.html
	 */
	public function get_max_allowed_packet_size() {
		/** @var wpdb $wpdb */
		global $wpdb;

		$max_size = $wpdb->get_results( "SHOW VARIABLES LIKE 'max_allowed_packet';", ARRAY_A );
		// default the size to 1MB
		$max_size = ! empty( $max_size[0]['Value'] ) ? $max_size[0]['Value'] : 1048576;

		/**
		 * Filters the size of the `max_allowed_packet` setting in bytes.
		 *
		 * @since 4.7.12
		 *
		 * @param int $max_size By default the `max_allowed_packet` from the database.
		 */
		$max_size = apply_filters( 'tribe_db_max_allowed_packet', $max_size );

		return $max_size;
	}
}
