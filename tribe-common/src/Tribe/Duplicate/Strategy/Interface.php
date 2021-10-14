<?php

/**
 * Interface Tribe__Duplicate__Strategy__Interface
 *
 * The API implemented by each duplicate finding strategy.
 *
 * @since 4.6
 */
interface Tribe__Duplicate__Strategy__Interface {
	/**
	 * Returns a string suitable to be used as a WHERE clause in a SQL query.
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return string
	 */
	public function where( $key, $value );

	/**
	 * Returns a string suitable to be used as a WHERE clause in a SQL query for a custom field JOIN.
	 *
	 * @param string $key
	 * @param mixed  $value
	 * @param string $table_alias
	 *
	 * @return string
	 */
	public function where_custom_field( $key, $value, $table_alias );
}
