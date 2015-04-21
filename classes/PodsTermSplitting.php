<?php
/**
 * @package Pods
 */
class Pods_Term_Splitting {

	/**
	 * Hook the split_shared_term action and point it to this method
	 *
	 * Fires after a previously shared taxonomy term is split into two separate terms.
	 *
	 * @since 4.2.0
	 *
	 * @param int    $term_id          ID of the formerly shared term.
	 * @param int    $new_term_id      ID of the new term created for the $term_taxonomy_id.
	 * @param int    $term_taxonomy_id ID for the term_taxonomy row affected by the split.
	 * @param string $taxonomy         Taxonomy for the split term.
	 */
	public static function split_shared_term( $term_id, $new_term_id, $term_taxonomy_id, $taxonomy ) {

		self::update_pod_table( $term_id, $new_term_id, $term_taxonomy_id, $taxonomy );
		self::update_podsrel( $term_id, $new_term_id, $term_taxonomy_id, $taxonomy );
		self::update_post_type_meta( $term_id, $new_term_id, $term_taxonomy_id, $taxonomy );
		self::update_comment_meta( $term_id, $new_term_id, $term_taxonomy_id, $taxonomy );
		self::update_user_meta( $term_id, $new_term_id, $term_taxonomy_id, $taxonomy );
		self::update_setting_meta( $term_id, $new_term_id, $term_taxonomy_id, $taxonomy );
		self::update_serialized( $term_id, $new_term_id, $term_taxonomy_id, $taxonomy );

		// ToDo: optionally store _pods_term_split_{$term_id}_{$taxonomy}

	}

	/**
	 * @param int $term_id ID of the formerly shared term.
	 * @param int $new_term_id ID of the new term created for the $term_taxonomy_id.
	 * @param int $term_taxonomy_id ID for the term_taxonomy row affected by the split.
	 * @param string $taxonomy Taxonomy for the split term.
	 *
	 * @return bool false on error
	 */
	public static function update_pod_table( $term_id, $new_term_id, $term_taxonomy_id, $taxonomy ) {

		/** @global wpdb $wpdb */
		global $wpdb;

		// Early exit if the taxonomy isn't a Pod
		if ( ! pods_api()->pod_exists( $taxonomy ) ) {
			return true;
		}

		// The taxonomy is a Pod, load it
		$params = array(
			'name'       => $taxonomy,
			'table_info' => true
		);
		$pod = pods_api()->load_pod( $params, false );
		if ( empty( $pod ) ) {
			return false; // Something went wrong loading the Pod
		}

		// We only need to update if table storage is on
		if ( 'table' !== $pod[ 'storage' ] ) {
			return true;
		}

		// Prime the values and update
		$table = $pod[ 'pod_table' ];
		$data = array('id' => $new_term_id );
		$where = array( 'id' => $term_id );
		$format = '%d';
		$where_format = '%d';
		$result = $wpdb->update( $table, $data, $where, $format, $where_format );

		if ( false === $result ) {
			return false;
		} else {
			return true;
		}

	}

	/**
	 * @param int    $term_id          ID of the formerly shared term.
	 * @param int    $new_term_id      ID of the new term created for the $term_taxonomy_id.
	 * @param int    $term_taxonomy_id ID for the term_taxonomy row affected by the split.
	 * @param string $taxonomy         Taxonomy for the split term.
	 */
	public static function update_podsrel( $term_id, $new_term_id, $term_taxonomy_id, $taxonomy ) {

	}

	/**
	 * @param int    $term_id          ID of the formerly shared term.
	 * @param int    $new_term_id      ID of the new term created for the $term_taxonomy_id.
	 * @param int    $term_taxonomy_id ID for the term_taxonomy row affected by the split.
	 * @param string $taxonomy         Taxonomy for the split term.
	 */
	public static function update_post_type_meta( $term_id, $new_term_id, $term_taxonomy_id, $taxonomy ) {

	}

	/**
	 * @param int    $term_id          ID of the formerly shared term.
	 * @param int    $new_term_id      ID of the new term created for the $term_taxonomy_id.
	 * @param int    $term_taxonomy_id ID for the term_taxonomy row affected by the split.
	 * @param string $taxonomy         Taxonomy for the split term.
	 */
	public static function update_comment_meta( $term_id, $new_term_id, $term_taxonomy_id, $taxonomy ) {

	}

	/**
	 * @param int    $term_id          ID of the formerly shared term.
	 * @param int    $new_term_id      ID of the new term created for the $term_taxonomy_id.
	 * @param int    $term_taxonomy_id ID for the term_taxonomy row affected by the split.
	 * @param string $taxonomy         Taxonomy for the split term.
	 */
	public static function update_user_meta( $term_id, $new_term_id, $term_taxonomy_id, $taxonomy ) {

	}

	/**
	 * @param int    $term_id          ID of the formerly shared term.
	 * @param int    $new_term_id      ID of the new term created for the $term_taxonomy_id.
	 * @param int    $term_taxonomy_id ID for the term_taxonomy row affected by the split.
	 * @param string $taxonomy         Taxonomy for the split term.
	 */
	public static function update_setting_meta( $term_id, $new_term_id, $term_taxonomy_id, $taxonomy ) {

	}

	/**
	 * @param int    $term_id          ID of the formerly shared term.
	 * @param int    $new_term_id      ID of the new term created for the $term_taxonomy_id.
	 * @param int    $term_taxonomy_id ID for the term_taxonomy row affected by the split.
	 * @param string $taxonomy         Taxonomy for the split term.
	 */
	public static function update_serialized( $term_id, $new_term_id, $term_taxonomy_id, $taxonomy ) {

	}

}