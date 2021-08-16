<?php

/**
 * Class Tribe__Duplicate__Strategy__Like
 *
 * Models a loose similarity strategy, punctuation is removed from string and words can be in any order.
 *
 * @since 4.6
 */
class Tribe__Duplicate__Strategy__Like
	extends Tribe__Duplicate__Strategy__Base
	implements Tribe__Duplicate__Strategy__Interface {

	/**
	 * Returns a string suitable to be used as a WHERE clause in a SQL query.
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return string
	 *
	 * @since 4.6
	 */
	public function where( $key, $value ) {
		/** @var wpdb $wpdb */
		global $wpdb;

		if ( $this->is_a_numeric_post_field( $key ) ) {
			return $wpdb->prepare( "{$key} = %d", $value );
		}

		$frags = $this->get_where_frags( $value );

		$where_frags = [];
		foreach ( $frags as $frag ) {
			$formatted_frag = '%' . $wpdb->esc_like( strtolower( trim( $frag ) ) ) . '%';
			$where_frags[]  = $wpdb->prepare( "{$key} LIKE %s", $formatted_frag );
		}

		return sprintf( '(%s)', implode( ' AND ', $where_frags ) );
	}

	/**
	 * Removes anything that's not letters, numbers, hypens and underscores from the string and returns its frags.
	 *
	 * @param string $value
	 *
	 * @return array
	 *
	 * @since 4.6
	 */
	protected function get_where_frags( $value ) {
		$snaked = preg_replace( '/[^a-z\d-]+/i', '_', $value );
		$frags = array_filter( explode( '_', $snaked ) );

		return $frags;
	}

	/**
	 * Returns a string suitable to be used as a WHERE clause in a SQL query for a custom field JOIN.
	 *
	 * @param string $key
	 * @param mixed  $value
	 * @param string $table_alias
	 *
	 * @return string
	 *
	 * @since 4.6
	 */
	public function where_custom_field( $key, $value, $table_alias ) {
		/** @var wpdb $wpdb */
		global $wpdb;

		$frags = $this->get_where_frags( $value );

		$where_frags = [ $wpdb->prepare( "{$table_alias}.meta_key = %s", $key ) ];
		foreach ( $frags as $frag ) {
			$formatted_frag = '%' . $wpdb->esc_like( strtolower( trim( $frag ) ) ) . '%';
			$query          = "{$table_alias}.meta_value LIKE %s";
			$where_frags[]  = $wpdb->prepare( $query, $formatted_frag );
		}

		return sprintf( '(%s)', implode( " \n\tAND ", $where_frags ) );
	}
}
