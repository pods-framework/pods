<?php

/**
 * @package Pods
 */
class Pods_Term_Splitting {

	/** @var int ID of the formerly shared term */
	var $term_id;

	/** @var int ID of the new term created for the $term_taxonomy_id */
	var $new_term_id;

	/** @var string Taxonomy for the split term */
	var $taxonomy;

	/**
	 * @param int $term_id ID of the formerly shared term.
	 * @param int $new_term_id ID of the new term created for the $term_taxonomy_id.
	 * @param string $taxonomy Taxonomy for the split term.
	 */
	public function __construct( $term_id, $new_term_id, $taxonomy ) {

		$this->term_id = $term_id;
		$this->new_term_id = $new_term_id;
		$this->taxonomy = $taxonomy;

	}

	/**
	 *
	 */
	public function split_shared_term() {

		// Get the Pod information if the taxonomy is a Pod
		$taxonomy_pod = $this->get_pod_info();

		// Is the taxonomy a Pod?
		if ( is_array( $taxonomy_pod ) ) {
			$this->update_podsrel_taxonomy( $taxonomy_pod[ 'pod_id' ] );

			// Update the Pods table if the taxonomy is a table based Pod
			if ( 'table' == $taxonomy_pod[ 'storage' ] ) {
				$this->update_pod_table( $taxonomy_pod[ 'pod_table' ] );
			}
		}

		// Track down all fields related to the target taxonomy and update stored term IDs as necessary
		$this->update_relationships_to_term();

	}

	/**
	 * Return the Pod information for the specified taxonomy, or null if the taxonomy isn't a Pod
	 *
	 * @return array|bool|mixed|null
	 */
	public function get_pod_info() {

		$pod_info = null;

		if ( pods_api()->pod_exists( $this->taxonomy ) ) {

			// Load the taxonomy Pod
			$params = array(
				'name'       => $this->taxonomy,
				'table_info' => true
			);
			$pod_info = pods_api()->load_pod( $params, false );
		}

		return $pod_info;

	}

	/**
	 * @param int $pod_id
	 */
	public function update_podsrel_taxonomy( $pod_id ) {

		/** @global wpdb $wpdb */
		global $wpdb;

		// UPDATE {$wpdb->prefix}podsrel SET item_id = {$new_term_id} WHERE pod_id = {$pod_id} AND item_id = {$term_id}
		$table = "{$wpdb->prefix}podsrel";
		$data = array( 'item_id' => $this->new_term_id );
		$where = array(
			'pod_id'  => $pod_id,
			'item_id' => $this->term_id
		);
		$format = '%d';
		$where_format = '%d';

		$wpdb->update( $table, $data, $where, $format, $where_format );

	}

	/**
	 * @param string $pod_table
	 */
	public function update_pod_table( $pod_table ) {

		/** @global wpdb $wpdb */
		global $wpdb;

		// Prime the values and update
		$data = array( 'id' => $this->new_term_id );
		$where = array( 'id' => $this->term_id );
		$format = '%d';
		$where_format = '%d';
		$wpdb->update( $pod_table, $data, $where, $format, $where_format );

	}

	/**
	 * Track down all fields related to the target taxonomy and update stored term IDs as necessary
	 */
	public function update_relationships_to_term() {

		// Loop through all Pods
		$all_pods = pods_api()->load_pods();

		if ( ! is_array( $all_pods ) ) {
			return;
		}

		foreach ( $all_pods as $this_pod_id => $this_pod ) {

			// Loop through all fields in this Pod
			foreach ( $this_pod[ 'fields' ] as $this_field_name => $this_field ) {

				// Ignore everything except relationship fields to this taxonomy
				if ( 'pick' != $this_field[ 'type' ] || 'taxonomy' != $this_field[ 'pick_object' ] || $this->taxonomy != $this_field[ 'pick_val' ] ) {
					continue;
				}

				// Update the term ID in podsrel everywhere it is the value for this field
				$this->update_podsrel_related_term( $this_field[ 'id' ] );

				// Fix-up any special-case relationships that store term IDs in their own meta table and/or serialized
				switch ( $this_pod[ 'type' ] ) {

					case 'post_type':
						$this->update_postmeta( $this_pod[ 'name' ], $this_field_name );
						break;

					case 'comment':
						$this->update_commentmeta( $this_field_name );
						break;

					case 'user':
						$this->update_usermeta( $this_field_name );
						break;

					case 'settings':
						$this->update_setting_meta( $this_pod[ 'name' ], $this_field_name );
						break;
				}
			}
		}

	}

	/**
	 * @param int $field_id
	 */
	public function update_podsrel_related_term( $field_id ) {

		/** @global wpdb $wpdb */
		global $wpdb;

		// UPDATE {$wpdb->prefix}podsrel SET related_item_id = {$new_term_id} WHERE field_id = {$field_id} AND related_item_id = {$term_id}
		$table = "{$wpdb->prefix}podsrel";
		$data = array(
			'related_item_id' => $this->new_term_id
		);
		$where = array(
			'field_id'        => $field_id,
			'related_item_id' => $this->term_id
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
	 */
	public function update_postmeta( $pod_name, $field_name ) {

		/** @global wpdb $wpdb */
		global $wpdb;

		// Fix up the non-serialized data
		$wpdb->query( $wpdb->prepare(
			"
			UPDATE
				{$wpdb->postmeta} AS meta
				LEFT JOIN {$wpdb->posts} AS t
				ON meta.post_id = t.ID
			SET
				meta_value = %s
			WHERE
				meta_key = %s
				AND meta_value = %s
				AND t.post_type = %s
			",
			$this->new_term_id,
			$field_name,
			$this->term_id,
			$pod_name
		) );

		// Fix up the serialized data
		$meta_key = sprintf( '_pods_%s', $field_name );
		$target_serialized = sprintf( ';i:%s;', $this->term_id );
		$replace_serialized = sprintf( ';i:%s;', $this->new_term_id );

		$wpdb->query( $wpdb->prepare(
			"
			UPDATE
			    {$wpdb->postmeta} AS meta
		    LEFT JOIN {$wpdb->posts} AS t
				ON meta.post_id = t.ID
			SET
				meta.meta_value = REPLACE( meta.meta_value, %s, %s )
			WHERE
			    meta.meta_key = %s
				AND t.post_type = %s
				AND meta_value LIKE '%%%s%%'
			",
			$target_serialized,
			$replace_serialized,
			$meta_key,
			$pod_name,
			pods_sanitize_like( $target_serialized )
		) );

	}

	/**
	 * Called for all fields related to the target taxonomy that are in a comment Pod
	 *
	 * @param string $field_name
	 */
	public function update_commentmeta( $field_name ) {

		/** @global wpdb $wpdb */
		global $wpdb;

		// Fix up the non-serialized data
		$wpdb->update(
			$wpdb->commentmeta,
			array(
				'meta_value' => $this->new_term_id
			),
			array(
				'meta_key'   => $field_name,
				'meta_value' => $this->term_id
			),
			array(
				'%s'
			),
			array(
				'%s',
				'%s'
			)
		);

		// Fix up the serialized data
		$meta_key = sprintf( '_pods_%s', $field_name );
		$target_serialized = sprintf( ';i:%s;', $this->term_id );
		$replace_serialized = sprintf( ';i:%s;', $this->new_term_id );

		$wpdb->query( $wpdb->prepare(
			"
			UPDATE
			    {$wpdb->commentmeta}
			SET
				meta_value = REPLACE( meta_value, %s, %s )
			WHERE
			    meta_key = %s
				AND meta_value LIKE '%%%s%%'
			",
			$target_serialized,
			$replace_serialized,
			$meta_key,
			pods_sanitize_like( $target_serialized )
		) );

	}

	/**
	 * Called for all fields related to the target taxonomy that are in a user Pod
	 *
	 * @param string $field_name
	 */
	public function update_usermeta( $field_name ) {

		/** @global wpdb $wpdb */
		global $wpdb;

		// Fix up the non-serialized data
		$wpdb->update(
			$wpdb->usermeta,
			array(
				'meta_value' => $this->new_term_id
			),
			array(
				'meta_key'   => $field_name,
				'meta_value' => $this->term_id
			),
			array(
				'%s'
			),
			array(
				'%s',
				'%s'
			)
		);

		// Fix up the serialized data
		$meta_key = sprintf( '_pods_%s', $field_name );
		$target_serialized = sprintf( ';i:%s;', $this->term_id );
		$replace_serialized = sprintf( ';i:%s;', $this->new_term_id );

		$wpdb->query( $wpdb->prepare(
			"
			UPDATE
			    {$wpdb->usermeta}
			SET
				meta_value = REPLACE( meta_value, %s, %s )
			WHERE
			    meta_key = %s
				AND meta_value LIKE '%%%s%%'
			",
			$target_serialized,
			$replace_serialized,
			$meta_key,
			pods_sanitize_like( $target_serialized )
		) );

	}

	/**
	 * Called for all fields related to the target taxonomy that are in a user Pod
	 *
	 * @param string $pod_name
	 * @param string $field_name
	 */
	public function update_setting_meta( $pod_name, $field_name ) {

		/** @global wpdb $wpdb */
		global $wpdb;

		$option_name = sprintf( '%s_%s', $pod_name, $field_name );
		$target_serialized = sprintf( ';i:%s;', $this->term_id );
		$replace_serialized = sprintf( ';i:%s;', $this->new_term_id );

		$wpdb->query( $wpdb->prepare(
			"
			UPDATE
				{$wpdb->options}
			SET
				option_value = REPLACE( option_value, %s, %s )
			WHERE
				option_name = %s
				AND option_value LIKE '%%%s%%'
			",
			$target_serialized,
			$replace_serialized,
			$option_name,
			pods_sanitize_like( $target_serialized )
		) );

	}

}