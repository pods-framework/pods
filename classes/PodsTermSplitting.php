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

		// Get the Pod information if the taxonomy is a Pod
		$taxonomy_pod = self::get_pod_info( $taxonomy );

		// Is the taxonomy a Pod?
		if ( is_array( $taxonomy_pod ) ) {

			//self::update_podsrel_taxonomy( $taxonomy_pod[ 'pod_id' ], $term_id, $new_term_id );

			// Update the Pods table if the taxonomy is a table based Pod
			if ( 'table' == $taxonomy_pod[ 'storage' ] ) {
				//self::update_pod_table( $taxonomy_pod[ 'pod_table' ], $term_id, $new_term_id );
			}
		}

		self::update_relationships_to_term( $term_id, $new_term_id, $taxonomy );

	}

	/**
	 * Return the Pod information for the specified taxonomy, or null if the taxonomy isn't a Pod
	 *
	 * @param string $taxonomy
	 *
	 * @return array|bool|mixed|null
	 */
	public static function get_pod_info( $taxonomy ) {

		$pod_info = null;

		if ( pods_api()->pod_exists( $taxonomy ) ) {

			// Load the taxonomy Pod
			$params = array(
				'name'       => $taxonomy,
				'table_info' => true
			);
			$pod_info = pods_api()->load_pod( $params, false );
		}

		return $pod_info;

	}

	/**
	 * @param int $pod_id
	 * @param int $term_id ID of the formerly shared term.
	 * @param int $new_term_id ID of the new term created.
	 */
	public static function update_podsrel_taxonomy( $pod_id, $term_id, $new_term_id ) {

		/** @global wpdb $wpdb */
		global $wpdb;

		// UPDATE {$wpdb->prefix}podsrel SET item_id = {$new_term_id} WHERE pod_id = {$pod_id} AND item_id = {$term_id}
		$table = "{$wpdb->prefix}podsrel";
		$data = array( 'item_id' => $new_term_id );
		$where = array(
			'pod_id' => $pod_id,
			'item_id' => $term_id
		);
		$format = '%d';
		$where_format = '%d';

		$wpdb->update( $table, $data, $where, $format, $where_format );

	}

	/**
	 * @param string $pod_table
	 * @param int $term_id ID of the formerly shared term.
	 * @param int $new_term_id ID of the new term created.
	 */
	public static function update_pod_table( $pod_table, $term_id, $new_term_id ) {

		/** @global wpdb $wpdb */
		global $wpdb;

		// Prime the values and update
		$data = array( 'id' => $new_term_id );
		$where = array( 'id' => $term_id );
		$format = '%d';
		$where_format = '%d';
		$wpdb->update( $pod_table, $data, $where, $format, $where_format );

	}

	/**
	 * Build the $all_relationships, $post_relationships, $comment_relationships, $user_relationships, and
	 * $settings_relationships arrays
	 *
	 * @param $term_id
	 * @param $new_term_id
	 * @param string $taxonomy
	 */
	public static function update_relationships_to_term( $term_id, $new_term_id, $taxonomy )  {

		// Loop through all Pods
		$all_pods = pods_api()->load_pods();
		foreach ( $all_pods as $this_pod_id => $this_pod ) {

			// Loop through all fields in this Pod
			foreach ( $this_pod[ 'fields' ] as $this_field_name => $this_field ) {

				// Ignore everything except relationship fields to this taxonomy
				if ( 'pick' != $this_field[ 'type' ] || 'taxonomy' != $this_field[ 'pick_object' ] || $taxonomy != $this_field[ 'pick_val' ] ) {
					continue;
				}

				// Update the term ID in podsrel everywhere it is the value for this field
				self::update_podsrel_related_term( $this_field[ 'id' ], $term_id, $new_term_id );

				// Fix-up any special-case relationships that store term IDs in their own meta table and/or serialized
				switch ( $this_pod[ 'type' ] ) {

					case 'post_type':
						self::update_postmeta( $this_pod[ 'name' ], $this_field_name, $term_id, $new_term_id );
						break;

					case 'comment':
						self::update_commentmeta( $this_pod[ 'name' ], $this_field_name, $term_id, $new_term_id );
						break;

					case 'user':
						self::update_usermeta( $this_pod[ 'name' ], $this_field_name, $term_id, $new_term_id );
						break;

					case 'settings':
						self::update_setting_meta( $this_pod[ 'name' ], $this_field_name, $term_id, $new_term_id );
						break;
				}
			}
		}

	}

	/**
	 * @param int $field_id
	 * @param int $term_id
	 * @param int $new_term_id
	 */
	public static function update_podsrel_related_term( $field_id, $term_id, $new_term_id ) {

		/** @global wpdb $wpdb */
		global $wpdb;

		// UPDATE {$wpdb->prefix}podsrel SET related_item_id = {$new_term_id} WHERE field_id = {$field_id} AND related_item_id = {$term_id}
		$table = "{$wpdb->prefix}podsrel";
		$data = array( 'related_item_id' => $new_term_id );
		$where = array(
			'field_id'        => $field_id,
			'related_item_id' => $term_id
		);
		$format = '%d';
		$where_format = '%d';

		$wpdb->update( $table, $data, $where, $format, $where_format );

	}

	/**
	 * Called for all fields related to the target taxonomy that are in a post_type
	 *
	 * @param string $pod_name
	 * @param string $field_name
	 * @param int $term_id ID of the formerly shared term.
	 * @param int $new_term_id ID of the new term created for the $term_taxonomy_id.
	 */
	public static function update_postmeta( $pod_name, $field_name, $term_id, $new_term_id ) {

		/** @global wpdb $wpdb */
		global $wpdb;

		// Fix up the non-serialized data
		$wpdb->query(
			"
			UPDATE
				{$wpdb->postmeta} AS meta
				LEFT JOIN {$wpdb->posts} AS t
				ON meta.post_id = t.ID
			SET
				meta_value = '{$new_term_id}'
			WHERE
				meta_key = '{$field_name}'
				AND meta_value = '{$term_id}'
				AND t.post_type = '$pod_name'
			"
		);

		// Fix up the serialized data
		$meta_key = sprintf( '_pods_%s', $field_name );
		$target_serialized = sprintf( ';i:%s;', $term_id );
		$replace_serialized = sprintf( ';i:%s;', $new_term_id );

		$wpdb->query( $wpdb->prepare(
			"
			UPDATE
			    {$wpdb->postmeta} AS meta
			    LEFT JOIN {$wpdb->posts} AS t
				ON meta.post_id = t.ID
			SET
				meta.meta_value = REPLACE( meta.meta_value, '{$target_serialized}', '{$replace_serialized}' )
			WHERE
			    meta.meta_key = '{$meta_key}'
				AND t.post_type = '{$pod_name}'
				AND meta_value LIKE '%%%s%%'
			",
			pods_sanitize_like( $target_serialized )
		) );

	}

	/**
	 * Called for all fields related to the target taxonomy that are in a comment Pod
	 *
	 * @param string $pod_name
	 * @param string $field_name
	 * @param int $term_id ID of the formerly shared term.
	 * @param int $new_term_id ID of the new term created for the $term_taxonomy_id.
	 */
	public static function update_commentmeta( $pod_name, $field_name, $term_id, $new_term_id ) {

		/** @global wpdb $wpdb */
		global $wpdb;

		// Fix up the non-serialized data
		$wpdb->query(
			"
			UPDATE
				{$wpdb->commentmeta}
			SET
				meta_value = '{$new_term_id}'
			WHERE
				meta_key = '{$field_name}'
				AND meta_value = '{$term_id}'
			"
		);

		// Fix up the serialized data
		$meta_key = sprintf( '_pods_%s', $field_name );
		$target_serialized = sprintf( ';i:%s;', $term_id );
		$replace_serialized = sprintf( ';i:%s;', $new_term_id );

		$wpdb->query( $wpdb->prepare(
			"
			UPDATE
			    {$wpdb->commentmeta}
			SET
				meta_value = REPLACE( meta_value, '{$target_serialized}', '{$replace_serialized}' )
			WHERE
			    meta_key = '{$meta_key}'
				AND meta_value LIKE '%%%s%%'
			",
			pods_sanitize_like( $target_serialized )
		) );

	}

	/**
	 * Called for all fields related to the target taxonomy that are in a user Pod
	 *
	 * @param string $pod_name
	 * @param string $field_name
	 * @param int $term_id ID of the formerly shared term.
	 * @param int $new_term_id ID of the new term created for the $term_taxonomy_id.
	 */
	public static function update_usermeta( $pod_name, $field_name, $term_id, $new_term_id ) {

		/** @global wpdb $wpdb */
		global $wpdb;

		// Fix up the non-serialized data
		$wpdb->query(
			"
			UPDATE
				{$wpdb->usermeta}
			SET
				meta_value = '{$new_term_id}'
			WHERE
				meta_key = '{$field_name}'
				AND meta_value = '{$term_id}'
			"
		);

		// Fix up the serialized data
		$meta_key = sprintf( '_pods_%s', $field_name );
		$target_serialized = sprintf( ';i:%s;', $term_id );
		$replace_serialized = sprintf( ';i:%s;', $new_term_id );

		$wpdb->query( $wpdb->prepare(
			"
			UPDATE
			    {$wpdb->usermeta}
			SET
				meta_value = REPLACE( meta_value, '{$target_serialized}', '{$replace_serialized}' )
			WHERE
			    meta_key = '{$meta_key}'
				AND meta_value LIKE '%%%s%%'
			",
			pods_sanitize_like( $target_serialized )
		) );

	}

	/**
	 * Called for all fields related to the target taxonomy that are in a user Pod
	 *
	 * @param string $pod_name
	 * @param string $field_name
	 * @param int $term_id ID of the formerly shared term.
	 * @param int $new_term_id ID of the new term created for the $term_taxonomy_id.
	 */
	public static function update_setting_meta( $pod_name, $field_name, $term_id, $new_term_id ) {

		/** @global wpdb $wpdb */
		global $wpdb;

		$option_name = sprintf( '%s_%s', $pod_name, $field_name );
		$target_serialized = sprintf( ';i:%s;', $term_id );
		$replace_serialized = sprintf( ';i:%s;', $new_term_id );

		$wpdb->query( $wpdb->prepare(
			"
			UPDATE
				{$wpdb->options}
			SET
				option_value = REPLACE( option_value, '{$target_serialized}', '{$replace_serialized}' )
			WHERE
				option_name = '{$option_name}'
				AND option_value LIKE '%%%s%%'
			",
			pods_sanitize_like( $target_serialized )
		) );

	}

}