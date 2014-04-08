<?php
/**
 * (PHP 4, PHP 5)<br/>
 * Get number of rows in result
 * @link http://php.net/manual/en/function.mysql-num-rows.php
 * @param resource $result <p>The result resource that is being evaluated. This result comes from a call to mysql_query().</p>
 * @return int <p>The number of rows in the result set on success or FALSE on failure. </p>
 */
function pods_mysql_num_rows( $result ) {

	/**
	 * @var $wpdb wpdb
	 */
	global $wpdb;

	if ( isset( $wpdb->use_mysqli ) && $wpdb->use_mysqli ) {
		return mysqli_num_rows( $result );
	}

	return mysql_num_rows( $result );

}

/**
 * (PHP 4 &gt;= 4.0.3, PHP 5)<br/>
 * Fetch a result row as an associative array
 * @link http://php.net/manual/en/function.mysql-fetch-assoc.php
 * @param resource $result
 * @return array an associative array of strings that corresponds to the fetched row, or
 * false if there are no more rows.
 * </p>
 * <p>
 * If two or more columns of the result have the same field names,
 * the last column will take precedence. To access the other
 * column(s) of the same name, you either need to access the
 * result with numeric indices by using
 * <b>mysql_fetch_row</b> or add alias names.
 * See the example at the <b>mysql_fetch_array</b>
 * description about aliases.
 */
function pods_mysql_fetch_assoc( $result ) {

	/**
	 * @var $wpdb wpdb
	 */
	global $wpdb;

	if ( isset( $wpdb->use_mysqli ) && $wpdb->use_mysqli ) {
		return mysqli_fetch_assoc( $result );
	}

	return mysql_fetch_assoc( $result );

}

/**
 * (PHP 4, PHP 5)<br/>
 * Fetch a result row as an associative array, a numeric array, or both
 * @link http://php.net/manual/en/function.mysql-fetch-array.php
 * @param resource $result
 * @param int $result_type [optional] <p>
 * The type of array that is to be fetched. It's a constant and can
 * take the following values: <b>MYSQL_ASSOC</b>,
 * <b>MYSQL_NUM</b>, and
 * <b>MYSQL_BOTH</b>.
 * </p>
 * @return array an array of strings that corresponds to the fetched row, or false
 * if there are no more rows. The type of returned array depends on
 * how <i>result_type</i> is defined. By using
 * <b>MYSQL_BOTH</b> (default), you'll get an array with both
 * associative and number indices. Using <b>MYSQL_ASSOC</b>, you
 * only get associative indices (as <b>mysql_fetch_assoc</b>
 * works), using <b>MYSQL_NUM</b>, you only get number indices
 * (as <b>mysql_fetch_row</b> works).
 * </p>
 * <p>
 * If two or more columns of the result have the same field names,
 * the last column will take precedence. To access the other column(s)
 * of the same name, you must use the numeric index of the column or
 * make an alias for the column. For aliased columns, you cannot
 * access the contents with the original column name.
 */
function pods_mysql_fetch_array( $result ) {

	/**
	 * @var $wpdb wpdb
	 */
	global $wpdb;

	if ( isset( $wpdb->use_mysqli ) && $wpdb->use_mysqli ) {
		return mysqli_fetch_array( $result );
	}

	return mysql_fetch_array( $result );

}

/**
 * (PHP 4, PHP 5)<br/>
 * Move internal result pointer
 * @link http://php.net/manual/en/function.mysql-data-seek.php
 * @param resource $result
 * @param int $row_number <p>
 * The desired row number of the new result pointer.
 * </p>
 * @return bool true on success or false on failure.
 */
function pods_mysql_data_seek( $result, $row_number ) {

	/**
	 * @var $wpdb wpdb
	 */
	global $wpdb;

	if ( isset( $wpdb->use_mysqli ) && $wpdb->use_mysqli ) {
		return mysqli_data_seek( $result, $row_number );
	}

	return mysql_data_seek( $result, $row_number );

}

/**
 * (PHP 4, PHP 5)<br/>
 * Send a MySQL query
 * @link http://php.net/manual/en/function.mysql-query.php
 * @param string $query <p>
 * An SQL query
 * </p>
 * <p>
 * The query string should not end with a semicolon.
 * Data inside the query should be properly escaped.
 * </p>
 * @param resource $link_identifier [optional]
 * @return resource For SELECT, SHOW, DESCRIBE, EXPLAIN and other statements returning resultset,
 * <b>mysql_query</b>
 * returns a resource on success, or false on
 * error.
 * </p>
 * <p>
 * For other type of SQL statements, INSERT, UPDATE, DELETE, DROP, etc,
 * <b>mysql_query</b> returns true on success
 * or false on error.
 * </p>
 * <p>
 * The returned result resource should be passed to
 * <b>mysql_fetch_array</b>, and other
 * functions for dealing with result tables, to access the returned data.
 * </p>
 * <p>
 * Use <b>mysql_num_rows</b> to find out how many rows
 * were returned for a SELECT statement or
 * <b>mysql_affected_rows</b> to find out how many
 * rows were affected by a DELETE, INSERT, REPLACE, or UPDATE
 * statement.
 * </p>
 * <p>
 * <b>mysql_query</b> will also fail and return false
 * if the user does not have permission to access the table(s) referenced by
 * the query.
 */
function pods_mysql_query( $query, $link_identifier = null ) {

	/**
	 * @var $wpdb wpdb
	 */
	global $wpdb;

	if ( isset( $wpdb->use_mysqli ) && $wpdb->use_mysqli ) {
		return mysqli_query( $link_identifier, $query );
	}

	return mysql_query( $query, $link_identifier );

}

/**
 * (PHP 4, PHP 5)<br/>
 * Returns the text of the error message from previous MySQL operation
 * @link http://php.net/manual/en/function.mysql-error.php
 * @param resource $link_identifier [optional]
 * @return string the error text from the last MySQL function, or
 * '' (empty string) if no error occurred.
 */
function pods_mysql_error( $link_identifier ) {

	/**
	 * @var $wpdb wpdb
	 */
	global $wpdb;

	if ( isset( $wpdb->use_mysqli ) && $wpdb->use_mysqli ) {
		return mysqli_error( $link_identifier );
	}

	return mysql_error( $link_identifier );

}

/**
 * (PHP 4, PHP 5)<br/>
 * Get the ID generated in the last query
 * @link http://php.net/manual/en/function.mysql-insert-id.php
 * @param resource $link_identifier [optional]
 * @return int The ID generated for an AUTO_INCREMENT column by the previous
 * query on success, 0 if the previous
 * query does not generate an AUTO_INCREMENT value, or false if
 * no MySQL connection was established.
 */
function pods_mysql_insert_id( $link_identifier ) {

	/**
	 * @var $wpdb wpdb
	 */
	global $wpdb;

	if ( isset( $wpdb->use_mysqli ) && $wpdb->use_mysqli ) {
		return mysqli_insert_id( $link_identifier );
	}

	return mysql_insert_id( $link_identifier );

}

/**
 * (PHP 4, PHP 5)<br/>
 * Get result data
 * @link http://php.net/manual/en/function.mysql-result.php
 * @param resource $result
 * @param int $row <p>
 * The row number from the result that's being retrieved. Row numbers
 * start at 0.
 * </p>
 * @param mixed $field [optional] <p>
 * The name or offset of the field being retrieved.
 * </p>
 * <p>
 * It can be the field's offset, the field's name, or the field's table
 * dot field name (tablename.fieldname). If the column name has been
 * aliased ('select foo as bar from...'), use the alias instead of the
 * column name. If undefined, the first field is retrieved.
 * </p>
 * @return string The contents of one cell from a MySQL result set on success, or
 * false on failure.
 */
function pods_mysql_result( $result, $row = 0, $field = 0 ) {

	/**
	 * @var $wpdb wpdb
	 */
	global $wpdb;

	if ( isset( $wpdb->use_mysqli ) && $wpdb->use_mysqli ) {
		if ( false === $result ) {
			return false;
		}

		if ( mysqli_num_rows( $result ) <= $row ) {
			return false;
		}

		if ( is_string( $field ) && false === strpos( $field, '.' ) ) {
			$t_field = explode( '.', $field );

			$field = -1;

			$t_fields = mysqli_fetch_fields( $result );

			for ( $id = 0; $id < mysqli_num_fields( $result ); $id++ ) {
				if ( $t_fields[ $id ]->table == $t_field[ 0 ] && $t_fields[ $id ]->name == $t_field[ 1 ] ) {
					$field = $id;

					break;
				}
			}

			if ( -1 == $field ) {
				return false;
			}
		}

		mysqli_data_seek( $result, $row );

		$line = mysqli_fetch_array( $result );

		if ( !isset( $line[ $field ] ) ) {
			return false;
		}

		return $line[ $field ];
	}

	return mysql_result( $result, $row );

}

/**
 * (PHP 4 &gt;= 4.3.0, PHP 5)<br/>
 * Escapes special characters in a string for use in an SQL statement
 * @link http://php.net/manual/en/function.mysql-real-escape-string.php
 * @param string $unescaped_string <p>
 * The string that is to be escaped.
 * </p>
 * @param resource $link_identifier [optional]
 * @return string the escaped string, or false on error.
 */
function pods_mysql_real_escape_string( $string ) {

	/**
	 * @var $wpdb wpdb
	 */
	global $wpdb;

	return $wpdb->_real_escape( $string );

}