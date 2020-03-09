<?php
/**
 * Used for maintaining post-level histories/audit trails.
 *
 * @internal
 * @since 4.3
 */
class Tribe__Post_History {
	/**
	 * Used to identify history/audit trail post meta records.
	 */
	const HISTORY_KEY = '_tribe_post_history';

	/**
	 * The post this history object is concerned with.
	 *
	 * @var int
	 */
	protected $post_id;


	/**
	 * Returns a Tribe__Post_History object for the specified post.
	 *
	 * @param int $post_id
	 *
	 * @return Tribe__Post_History
	 */
	public static function load( $post_id ) {
		return new self( $post_id );
	}

	/**
	 * Returns a Tribe__Post_History object for the specified post.
	 *
	 * @param int $post_id
	 *
	 * @return Tribe__Post_History
	 */
	public function __construct( $post_id ) {
		$this->post_id = $post_id;
	}

	/**
	 * Records a new history entry for the current post.
	 *
	 * @param string $message
	 * @param array $data
	 */
	public function add_entry( $message, array $data = array() ) {
		$datetime = current_time( 'mysql' );
		$checksum = uniqid( substr( hash( 'md5', $datetime . $message . serialize( $data ) ), 0, 8 ) . '_' );

		$log_entry = wp_slash( json_encode( array(
			'datetime' => $datetime,
			'message'  => $message,
			'data'     => $data,
			'checksum' => $checksum,
		) ) );

		add_post_meta( $this->post_id, self::HISTORY_KEY, $log_entry );
	}

	/**
	 * Indicates if any history exists for the current post.
	 *
	 * @return bool
	 */
	public function has_entries() {
		$first_available_entry = get_post_meta( $this->post_id, self::HISTORY_KEY, true );
		return ! empty( $first_available_entry );
	}

	/**
	 * Returns all historical records for the current post as an array
	 * of objects, each object taking the form:
	 *
	 * {
	 *     "datetime": "yyyy-mm-dd hh:ii:ss",
	 *     "message":  "...",
	 *     "data":     []
	 * }
	 *
	 * @return array
	 */
	public function get_entries() {
		$entries = array();

		foreach ( get_post_meta( $this->post_id, self::HISTORY_KEY ) as $log_entry ) {
			$log_entry = json_decode( $log_entry );

			if ( ! $log_entry ) {
				continue;
			}

			$entries[] = $log_entry;
		}

		return $entries;
	}

	/**
	 * Deletes all entries for the current post that match the provided datetime
	 * string and (optionally) also match the provided checksum.
	 *
	 * Returns the total number of deleted entries, which may be zero if none were matched;
	 * can also be more than one if multiple entries were logged at the same time and no
	 * checksum is provided.
	 *
	 * @param string $datetime
	 * @param string $checksum optional value to more precisely specify the entry to be deleted
	 *
	 * @return int
	 */
	public function delete_entry( $datetime, $checksum = null ) {
		$deleted = 0;

		foreach ( $this->get_entries() as $entry ) {
			if ( $entry->datetime !== $datetime ) {
				continue;
			}

			if ( null !== $checksum && $entry->checksum !== $checksum ) {
				continue;
			}

			if ( delete_post_meta( $this->post_id, self::HISTORY_KEY, json_encode( $entry ) ) ) {
				$deleted++;
			}
		}

		return $deleted;
	}
}
