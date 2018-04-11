<?php

/**
 * @package Pods
 */
class Pods_Term_Splitting {

	/** @var int ID of the formerly shared term */
	private $term_id;

	/** @var int ID of the new term created for the $term_taxonomy_id */
	private $new_term_id;

	/** @var string Taxonomy for the split term */
	private $taxonomy;

	/** @var string */
	private $progress_option_name;

	/** @var array */
	private $previous_progress = array();

	/**
	 * @param int    $term_id     ID of the formerly shared term.
	 * @param int    $new_term_id ID of the new term created for the $term_taxonomy_id.
	 * @param string $taxonomy    Taxonomy for the split term.
	 */
	public function __construct( $term_id, $new_term_id, $taxonomy ) {

		$this->term_id     = $term_id;
		$this->new_term_id = $new_term_id;
		$this->taxonomy    = $taxonomy;

		$this->progress_option_name = "_pods_term_split_{$term_id}_{$taxonomy}";

	}

	/**
	 *
	 */
	public function split_shared_term() {

		// Stash any previous progress
		$this->previous_progress = $this->get_progress();
		if ( empty( $this->previous_progress ) ) {
			$this->append_progress( 'started' );
			$this->append_progress( "new term ID: {$this->new_term_id}" );
		}

		// Get the Pod information if the taxonomy is a Pod
		$taxonomy_pod = $this->get_pod_info();

		// Is the taxonomy a Pod?
		if ( is_array( $taxonomy_pod ) ) {
			$this->update_podsrel_taxonomy( $taxonomy_pod['id'] );

			// Update the Pods table if the taxonomy is a table based Pod
			if ( 'table' === $taxonomy_pod['storage'] ) {
				$this->update_pod_table( $taxonomy_pod['pod_table'] );
			}
		}

		// Track down all fields related to the target taxonomy and update stored term IDs as necessary
		$this->update_relationships_to_term();

		// Clean up
		$this->delete_progress();

	}

	/**
	 * Return the Pod information for the specified taxonomy, or null if the taxonomy isn't a Pod
	 *
	 * @return array|bool|mixed|null
	 */
	private function get_pod_info() {

		$pod_info = null;

		if ( pods_api()->pod_exists( $this->taxonomy ) ) {

			// Load the taxonomy Pod
			$params   = array(
				'name'       => $this->taxonomy,
				'table_info' => true,
			);
			$pod_info = pods_api()->load_pod( $params, false );
		}

		return $pod_info;

	}

	/**
	 * @param int $pod_id
	 */
	private function update_podsrel_taxonomy( $pod_id ) {

		/** @global wpdb $wpdb */
		global $wpdb;

		$task = "update_podsrel_taxonomy_{$pod_id}";
		if ( ! $this->have_done( $task ) ) {

			// UPDATE {$wpdb->prefix}podsrel SET item_id = {$new_term_id} WHERE pod_id = {$pod_id} AND item_id = {$term_id}
			$table        = "{$wpdb->prefix}podsrel";
			$data         = array( 'item_id' => $this->new_term_id );
			$where        = array(
				'pod_id'  => $pod_id,
				'item_id' => $this->term_id,
			);
			$format       = '%d';
			$where_format = '%d';

			$wpdb->update( $table, $data, $where, $format, $where_format );

			$this->append_progress( $task );
		}

	}

	/**
	 * @param string $pod_table
	 */
	private function update_pod_table( $pod_table ) {

		/** @global wpdb $wpdb */
		global $wpdb;

		$task = "update_pod_table_{$pod_table}";
		if ( ! $this->have_done( $task ) ) {

			// Prime the values and update
			$data         = array( 'id' => $this->new_term_id );
			$where        = array( 'id' => $this->term_id );
			$format       = '%d';
			$where_format = '%d';
			$wpdb->update( $pod_table, $data, $where, $format, $where_format );

			$this->append_progress( $task );
		}

	}

	/**
	 * Track down all fields related to the target taxonomy and update stored term IDs as necessary
	 */
	private function update_relationships_to_term() {

		// Loop through all Pods
		$all_pods = pods_api()->load_pods();

		if ( ! is_array( $all_pods ) ) {
			return;
		}

		foreach ( $all_pods as $this_pod_id => $this_pod ) {

			// Loop through all fields in this Pod
			foreach ( $this_pod['fields'] as $this_field_name => $this_field ) {

				// Ignore everything except relationship fields to this taxonomy
				if ( 'pick' !== $this_field['type'] || 'taxonomy' !== $this_field['pick_object'] || $this->taxonomy != $this_field['pick_val'] ) {
					continue;
				}

				// Update the term ID in podsrel everywhere it is the value for this field
				$this->update_podsrel_related_term( $this_field['id'] );

				// Fix-up any special-case relationships that store term IDs in their own meta table and/or serialized
				switch ( $this_pod['type'] ) {

					case 'post_type':
						$this->update_postmeta( $this_pod['name'], $this_field_name );
						break;

					case 'comment':
						$this->update_commentmeta( $this_field_name );
						break;

					case 'user':
						$this->update_usermeta( $this_field_name );
						break;

					case 'settings':
						$this->update_setting_meta( $this_pod['name'], $this_field_name );
						break;
				}
			}//end foreach
		}//end foreach

	}

	/**
	 * @param int $field_id
	 */
	private function update_podsrel_related_term( $field_id ) {

		/** @global wpdb $wpdb */
		global $wpdb;

		$task = "update_podsrel_related_term_{$field_id}";
		if ( ! $this->have_done( $task ) ) {

			// UPDATE {$wpdb->prefix}podsrel SET related_item_id = {$new_term_id} WHERE field_id = {$field_id} AND related_item_id = {$term_id}
			$table        = "{$wpdb->prefix}podsrel";
			$data         = array(
				'related_item_id' => $this->new_term_id,
			);
			$where        = array(
				'field_id'        => $field_id,
				'related_item_id' => $this->term_id,
			);
			$format       = '%d';
			$where_format = '%d';
			$wpdb->update( $table, $data, $where, $format, $where_format );

			$this->append_progress( $task );
		}

	}

	/**
	 * Called for all fields related to the target taxonomy that are in a post_type
	 *
	 * @param string $pod_name
	 * @param string $field_name
	 */
	private function update_postmeta( $pod_name, $field_name ) {

		/** @global wpdb $wpdb */
		global $wpdb;

		// Fix up the unserialized data
		$task = "update_postmeta_{$pod_name}_{$field_name}_unserialized";
		if ( ! $this->have_done( $task ) ) {

			$wpdb->query(
				$wpdb->prepare(
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
				", $this->new_term_id, $field_name, $this->term_id, $pod_name
				)
			);

			$this->append_progress( $task );
		}//end if

		// Fix up the serialized data
		$task = "update_postmeta_{$pod_name}_{$field_name}_serialized";
		if ( ! $this->have_done( $task ) ) {

			$meta_key           = sprintf( '_pods_%s', $field_name );
			$target_serialized  = sprintf( ';i:%s;', $this->term_id );
			$replace_serialized = sprintf( ';i:%s;', $this->new_term_id );

			$wpdb->query(
				$wpdb->prepare(
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
				", $target_serialized, $replace_serialized, $meta_key, $pod_name, pods_sanitize_like( $target_serialized )
				)
			);

			$this->append_progress( $task );
		}//end if

	}

	/**
	 * Called for all fields related to the target taxonomy that are in a comment Pod
	 *
	 * @param string $field_name
	 */
	private function update_commentmeta( $field_name ) {

		/** @global wpdb $wpdb */
		global $wpdb;

		// Fix up the unserialized data
		$task = "update_commentmeta_{$field_name}_unserialized";
		if ( ! $this->have_done( $task ) ) {

			$table        = $wpdb->commentmeta;
			$data         = array( 'meta_value' => $this->new_term_id );
			$where        = array(
				'meta_key'   => $field_name,
				'meta_value' => $this->term_id,
			);
			$format       = '%s';
			$where_format = array( '%s', '%s' );
			$wpdb->update( $table, $data, $where, $format, $where_format );

			$this->append_progress( $task );
		}

		// Fix up the serialized data
		$task = "update_commentmeta_{$field_name}_serialized";
		if ( ! $this->have_done( $task ) ) {

			$meta_key           = sprintf( '_pods_%s', $field_name );
			$target_serialized  = sprintf( ';i:%s;', $this->term_id );
			$replace_serialized = sprintf( ';i:%s;', $this->new_term_id );

			$wpdb->query(
				$wpdb->prepare(
					"
				UPDATE
				    {$wpdb->commentmeta}
				SET
					meta_value = REPLACE( meta_value, %s, %s )
				WHERE
				    meta_key = %s
					AND meta_value LIKE '%%%s%%'
				", $target_serialized, $replace_serialized, $meta_key, pods_sanitize_like( $target_serialized )
				)
			);

			$this->append_progress( $task );
		}//end if

	}

	/**
	 * Called for all fields related to the target taxonomy that are in a user Pod
	 *
	 * @param string $field_name
	 */
	private function update_usermeta( $field_name ) {

		/** @global wpdb $wpdb */
		global $wpdb;

		// Fix up the unserialized data
		$task = "update_usermeta_{$field_name}_unserialized";
		if ( ! $this->have_done( $task ) ) {

			$table        = $wpdb->usermeta;
			$data         = array( 'meta_value' => $this->new_term_id );
			$where        = array(
				'meta_key'   => $field_name,
				'meta_value' => $this->term_id,
			);
			$format       = '%s';
			$where_format = array( '%s', '%s' );
			$wpdb->update( $table, $data, $where, $format, $where_format );

			$this->append_progress( $task );
		}

		// Fix up the serialized data
		$task = "update_usermeta_{$field_name}_serialized";
		if ( ! $this->have_done( $task ) ) {

			$meta_key           = sprintf( '_pods_%s', $field_name );
			$target_serialized  = sprintf( ';i:%s;', $this->term_id );
			$replace_serialized = sprintf( ';i:%s;', $this->new_term_id );

			$wpdb->query(
				$wpdb->prepare(
					"
				UPDATE
				    {$wpdb->usermeta}
				SET
					meta_value = REPLACE( meta_value, %s, %s )
				WHERE
				    meta_key = %s
					AND meta_value LIKE '%%%s%%'
				", $target_serialized, $replace_serialized, $meta_key, pods_sanitize_like( $target_serialized )
				)
			);

			$this->append_progress( $task );
		}//end if

	}

	/**
	 * Called for all fields related to the target taxonomy that are in a user Pod
	 *
	 * @param string $pod_name
	 * @param string $field_name
	 */
	private function update_setting_meta( $pod_name, $field_name ) {

		/** @global wpdb $wpdb */
		global $wpdb;

		$option_name = "{$pod_name}_{$field_name}";

		// Fix up the unserialized data
		$task = "update_setting_meta_{$pod_name}_{$field_name}_unserialized";
		if ( ! $this->have_done( $task ) ) {

			// UPDATE {$wpdb->options} SET option_value = '{$new_term_id}' WHERE option_name = '{$pod_name}_{$field_name}' AND option_value = '{$term_id}'
			$table        = $wpdb->options;
			$data         = array( 'option_value' => $this->new_term_id );
			$where        = array(
				'option_name'  => $option_name,
				'option_value' => $this->term_id,
			);
			$format       = '%s';
			$where_format = array( '%s', '%s' );
			$wpdb->update( $table, $data, $where, $format, $where_format );

			$this->append_progress( $task );
		}

		// Fix up the serialized data
		$task = "update_setting_meta_{$pod_name}_{$field_name}_serialized";
		if ( ! $this->have_done( $task ) ) {

			$target_serialized  = sprintf( ';i:%s;', $this->term_id );
			$replace_serialized = sprintf( ';i:%s;', $this->new_term_id );

			$wpdb->query(
				$wpdb->prepare(
					"
				UPDATE
					{$wpdb->options}
				SET
					option_value = REPLACE( option_value, %s, %s )
				WHERE
					option_name = %s
					AND option_value LIKE '%%%s%%'
				", $target_serialized, $replace_serialized, $option_name, pods_sanitize_like( $target_serialized )
				)
			);

			$this->append_progress( $task );
		}//end if

	}

	/**
	 * @param string $task_name
	 *
	 * @return bool
	 */
	private function have_done( $task_name ) {

		return in_array( $task_name, $this->previous_progress );

	}

	/**
	 * @return array
	 */
	private function get_progress() {

		return get_option( $this->progress_option_name, array() );
	}

	/**
	 * @param $data
	 */
	private function append_progress( $data ) {

		// Get the current progress array
		$current_progress = $this->get_progress();
		if ( ! is_array( $current_progress ) ) {
			$current_progress = array();
		}

		// Tack on the new data
		$updated_progress = array_merge( $current_progress, array( $data ) );

		// Note: we don't want autoload set and you cannot specify autoload via update_option
		if ( ! empty( $current_progress ) && is_array( $current_progress ) ) {
			update_option( $this->progress_option_name, $updated_progress );
		} else {
			add_option( $this->progress_option_name, $updated_progress, '', false );
		}

	}

	/**
	 *
	 */
	private function delete_progress() {

		delete_option( $this->progress_option_name );

	}

}
