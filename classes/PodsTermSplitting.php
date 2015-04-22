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

		// Check if Pod $taxonomy exists and is a taxonomy
		// UPDATE {$wpdb->prefix}podsrel SET item_id = {$new_term_id} WHERE pod_id = {$pod_id} AND item_id = {$term_id}
		
		// Find all Relationship Fields with pick_type = 'taxonomy' and pick_val = {$taxonomy}
		// UPDATE {$wpdb->prefix}podsrel SET related_item_id = {$new_term_id} WHERE field_id = {$field_id} AND related_item_id = {$term_id}

	}

	/**
	 * @param int    $term_id          ID of the formerly shared term.
	 * @param int    $new_term_id      ID of the new term created for the $term_taxonomy_id.
	 * @param int    $term_taxonomy_id ID for the term_taxonomy row affected by the split.
	 * @param string $taxonomy         Taxonomy for the split term.
	 */
	public static function update_post_type_meta( $term_id, $new_term_id, $term_taxonomy_id, $taxonomy ) {

		// Find all Relationship Fields with pick_type = 'taxonomy' and pick_val = {$taxonomy} AND are on Pods with type = 'post_type'
		
		// UPDATE {$wpdb->postmeta} SET meta_value = $new_term_id WHERE meta_key = {$field_name} AND meta_value = {$term_id}
		
		// $find_serialized = pods_sanitize_like( ';i:' . $term_id . ';' );
		// $pod_name = $field['pod_name']
		// SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_pods_{$field_name}' AND post_id IN ( SELECT ID FROM {$wpdb->posts} WHERE post_type = '{$pod_name}' ) AND meta_value LIKE '%{$find_serialized}%'
		
		// Loop through each meta record
		// Replace the $term_id with $new_term_id in array
		// update_post_meta( $meta_row->post_id, '_pods_' . $field_name, $new_meta_value );

	}

	/**
	 * @param int    $term_id          ID of the formerly shared term.
	 * @param int    $new_term_id      ID of the new term created for the $term_taxonomy_id.
	 * @param int    $term_taxonomy_id ID for the term_taxonomy row affected by the split.
	 * @param string $taxonomy         Taxonomy for the split term.
	 */
	public static function update_comment_meta( $term_id, $new_term_id, $term_taxonomy_id, $taxonomy ) {

		// Find all Relationship Fields with pick_type = 'taxonomy' and pick_val = {$taxonomy} AND are on Pods with type = 'comment'
		
		// UPDATE {$wpdb->commentmeta} SET meta_value = $new_term_id WHERE meta_key = {$field_name} AND meta_value = {$term_id}
		
		// $find_serialized = pods_sanitize_like( ';i:' . $term_id . ';' );
		// $pod_name = $field['pod_name']
		// SELECT comment_id, meta_value FROM {$wpdb->commentmeta} WHERE meta_key = '_pods_{$field_name}' AND comment_id IN ( SELECT comment_ID FROM {$wpdb->comments} WHERE comment_type = '{$pod_name}' OR comment_type = '' ) AND meta_value LIKE '%{$find_serialized}%'
		// Note: Comment type might be empty for normal 'comment' which is all that Pods officially supports right now
		
		// Loop through each meta record
		// Replace the $term_id with $new_term_id in array
		// update_comment_meta( $meta_row->comment_id, '_pods_' . $field_name, $new_meta_value );
		
	}

	/**
	 * @param int    $term_id          ID of the formerly shared term.
	 * @param int    $new_term_id      ID of the new term created for the $term_taxonomy_id.
	 * @param int    $term_taxonomy_id ID for the term_taxonomy row affected by the split.
	 * @param string $taxonomy         Taxonomy for the split term.
	 */
	public static function update_user_meta( $term_id, $new_term_id, $term_taxonomy_id, $taxonomy ) {

		// Find all Relationship Fields with pick_type = 'taxonomy' and pick_val = {$taxonomy} AND are on Pods with type = 'user'
		
		// UPDATE {$wpdb->usermeta} SET meta_value = $new_term_id WHERE meta_key = {$field_name} AND meta_value = {$term_id}
		
		// $find_serialized = pods_sanitize_like( ';i:' . $term_id . ';' );
		// $pod_name = $field['pod_name']
		// SELECT user_id, meta_value FROM {$wpdb->usermeta} WHERE meta_key = '_pods_{$field_name}' AND meta_value LIKE '%{$find_serialized}%'
		
		// Loop through each meta record
		// Replace the $term_id with $new_term_id in array
		// update_user_meta( $meta_row->user_id, '_pods_' . $field_name, $new_meta_value );
		
	}

	/**
	 * @param int    $term_id          ID of the formerly shared term.
	 * @param int    $new_term_id      ID of the new term created for the $term_taxonomy_id.
	 * @param int    $term_taxonomy_id ID for the term_taxonomy row affected by the split.
	 * @param string $taxonomy         Taxonomy for the split term.
	 */
	public static function update_setting_meta( $term_id, $new_term_id, $term_taxonomy_id, $taxonomy ) {

		// Find all Relationship Fields with pick_type = 'taxonomy' and pick_val = {$taxonomy} AND are on Pods with type = 'setting'
		
		// $find_serialized = pods_sanitize_like( ';i:' . $term_id . ';' );
		// $pod_name = $field['pod_name']
		// if ( 'multiple' == $field['pick_format_type'] )
		// SELECT option_value FROM {$wpdb->options} WHERE option_name = '{pod_name}_{$field_name}' AND option_value LIKE '%{$find_serialized}%'
		// else
		// UPDATE {$wpdb->options} SET option_value = {$new_term_id} WHERE option_name = '{pod_name}_{$field_name}' AND option_value = {$term_id}'
		
		// if ( 'multiple' == $field['pick_format_type'] )
		// $value = get_option( $pod_name . '_' . $field_name )
		// Replace the $term_id with $new_term_id in array
		// update_option( $pod_name . '_' . $field_name, $new_value );
		
	}

	/**
	 * @param int    $term_id          ID of the formerly shared term.
	 * @param int    $new_term_id      ID of the new term created for the $term_taxonomy_id.
	 * @param int    $term_taxonomy_id ID for the term_taxonomy row affected by the split.
	 * @param string $taxonomy         Taxonomy for the split term.
	 */
	public static function update_serialized( $term_id, $new_term_id, $term_taxonomy_id, $taxonomy ) {

		// See stuff above
		
	}

}
