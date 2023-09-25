<?php

namespace Pods\Tools;

use Exception;
use Throwable;
use PodsForm;
use Pods\Whatsit\Field;
use Pods\Whatsit\Group;
use Pods\Whatsit\Pod;

/**
 * Repair tool functionality.
 *
 * @since 2.9.4
 */
class Repair extends Base {

	/**
	 * Repair Groups and Fields for a Pod.
	 *
	 * @since 2.9.4
	 *
	 * @param Pod    $pod  The Pod object.
	 * @param string $mode The repair mode (preview, upgrade, or full).
	 *
	 * @return array The results with information about the repair done.
	 */
	public function repair_groups_and_fields_for_pod( Pod $pod, $mode ) {
		$this->setup();

		$this->errors = [];

		$is_preview_mode = 'preview' === $mode;
		$is_upgrade_mode = 'upgrade' === $mode;
		$is_migrated     = 1 === (int) $pod->get_arg( '_migrated_28' );

		// Maybe set up a new group if no groups are found for the Pod.
		$group_id = $this->maybe_setup_group_if_no_groups( $pod, $mode );

		$results = [];

		// Maybe fix fields with invalid pod/storage type.
		$results[ __( 'Fixed pod with invalid pod type', 'pods' ) ]         = $this->maybe_fix_pod_with_invalid_pod_type( $pod, $mode );
		$results[ __( 'Fixed pod with invalid pod storage type', 'pods' ) ] = $this->maybe_fix_pod_with_invalid_pod_storage_type( $pod, $mode );

		// If no group needed to be created, attempt to find the first group ID.
		if ( null === $group_id ) {
			$groups = $pod->get_groups( [
				'fallback_mode' => false,
				'limit'         => 1,
			] );

			$groups = wp_list_pluck( $groups, 'id' );
			$groups = array_filter( $groups );

			// Get the first group ID.
			if ( ! empty( $groups ) ) {
				$group_id = reset( $groups );
			}
		} else {
			$results[ __( 'Setup group if there were no groups', 'pods' ) ] = __( 'First group created.', 'pods' );
		}

		if ( ! $is_upgrade_mode || $is_migrated ) {
			// Maybe resolve group conflicts.
			$results[ __( 'Resolved group conflicts', 'pods' ) ] = $this->maybe_resolve_group_conflicts( $pod, $mode );

			// Maybe resolve field conflicts.
			$results[ __( 'Resolved field conflicts', 'pods' ) ] = $this->maybe_resolve_field_conflicts( $pod, $mode );
		}

		// If we have a group to work with, use that.
		if ( null !== $group_id ) {
			if ( ! $is_upgrade_mode || $is_migrated ) {
				// Maybe reassign fields with invalid groups.
				$results[ __( 'Reassigned fields with invalid groups', 'pods' ) ] = $this->maybe_reassign_fields_with_invalid_groups( $pod, $group_id, $mode );
			}

			// Maybe reassign orphan fields to the first group.
			$results[ __( 'Reassigned orphan fields', 'pods' ) ] = $this->maybe_reassign_orphan_fields( $pod, $group_id, $mode );
		}

		// Maybe fix fields with invalid field type.
		$results[ __( 'Fixed fields with invalid field type', 'pods' ) ] = $this->maybe_fix_fields_with_invalid_field_type( $pod, $mode );

		// Maybe fix fields with invalid arguments.
		$results[ __( 'Fixed fields with invalid arguments', 'pods' ) ] = $this->maybe_fix_fields_with_invalid_args( $pod, $mode );

		// Check if changes were made to the Pod.
		$changes_made = [] !== array_filter( $results );

		// Mark the pod as migrated if upgrading and only save the Pod if changes were made or the migrated tag is not set.
		if (
			$is_upgrade_mode
			&& (
				$changes_made
				|| 0 === (int) $pod->get_arg( '_migrated_28', 0 )
			)
		) {
			$pod->set_arg( '_migrated_28', 1 );

			try {
				$this->api->save_pod( $pod );
			} catch ( Throwable $exception ) {
				pods_debug_log( $exception );
			}

			// Refresh pod object.
			$pod->flush();
		} elseif ( ! $is_preview_mode && $changes_made ) {
			$this->api->cache_flush_pods( $pod );

			// Refresh pod object.
			$pod->flush();
		}

		$tool_heading = sprintf(
			// translators: %s: The Pod label.
			__( 'Repair results for %s', 'pods' ),
			$pod->get_label() . ' (' . $pod->get_name() . ')'
		);

		$results['message_html'] = $this->get_message_html( $tool_heading, $results, $mode );

		if ( $is_upgrade_mode ) {
			$results['upgraded_pod'] = $pod;
		}

		return $results;
	}

	/**
	 * Maybe fix pods with invalid pod type.
	 *
	 * @since 2.9.15
	 *
	 * @param Pod    $pod  The Pod object.
	 * @param string $mode The repair mode (preview, upgrade, or full).
	 *
	 * @return string[] The label, name, and ID for each pod fixed.
	 */
	protected function maybe_fix_pod_with_invalid_pod_type( Pod $pod, $mode ) {
		$this->setup();

		$supported_pod_types = pods_api()->get_pod_types();

		$old_type = $pod->get_type();

		$messages = [];

		if ( ! isset( $supported_pod_types[ $old_type ] ) ) {
			try {
				if ( $pod->get_id() <= 0 ) {
					$this->errors[] = __( 'Unable to repair a Pod that was not registered in the database.', 'pods' );

					return [];
				}

				if ( 'preview' !== $mode ) {
					$this->api->save_pod( [
						'id'   => $pod->get_id(),
						'type' => 'post_type',
					] );

					$pod->set_arg( 'type', 'post_type' );
				}

				$messages[] = sprintf(
					'%1$s (%2$s: %3$s | %4$s: %5$s | %6$s: %7$d)',
					$pod->get_label(),
					__( 'Old Type', 'pods' ),
					$old_type,
					__( 'Name', 'pods' ),
					$pod->get_name(),
					__( 'ID', 'pods' ),
					$pod->get_id()
				);
			} catch ( Throwable $exception ) {
				$this->errors[] = ucwords( str_replace( '_', ' ', __FUNCTION__ ) ) . ' > ' . $exception->getMessage() . ' (' . $field->get_name() . ' - #' . $field->get_id() . ')';
			}
		}

		return $messages;
	}

	/**
	 * Maybe fix pods with invalid pod storage type.
	 *
	 * @since 2.9.15
	 *
	 * @param Pod    $pod  The Pod object.
	 * @param string $mode The repair mode (preview, upgrade, or full).
	 *
	 * @return string[] The label, name, and ID for each pod fixed.
	 */
	protected function maybe_fix_pod_with_invalid_pod_storage_type( Pod $pod, $mode ) {
		$this->setup();

		$supported_storage_types = pods_api()->get_storage_types();

		$old_storage_type = $pod->get_storage( true );

		if ( empty( $old_storage_type ) ) {
			$old_storage_type = 'n/a';
		}

		$pod_type = $pod->get_type();

		$force_storage_update = false;

		if ( 'meta' === $old_storage_type && in_array( $pod_type, [ 'pod', 'table', 'settings' ], true ) ) {
			$force_storage_update = true;
		}

		$new_storage_type = $pod->get_default_storage();

		$messages = [];

		if ( $force_storage_update || ! isset( $supported_storage_types[ $old_storage_type ] ) ) {
			try {
				if ( $pod->get_id() <= 0 ) {
					$this->errors[] = __( 'Unable to repair a Pod that was not registered in the database.', 'pods' );

					return [];
				}

				if ( 'preview' !== $mode ) {
					// Save the pod but don't overwrite the DB table schema if it exists.
					$this->api->save_pod(
						[
							'id'                     => $pod->get_id(),
							'storage'                => $new_storage_type,
							'overwrite_table_schema' => false,
						]
					);

					$pod->set_arg( 'storage', $new_storage_type );
				}

				$messages[] = sprintf(
					'%1$s (%2$s: %3$s | %4$s: %5$s | %6$s: %7$s | %8$s: %9$s | %10$s: %11$d)',
					$pod->get_label(),
					__( 'Old Storage Type', 'pods' ),
					$old_storage_type,
					__( 'New Storage Type', 'pods' ),
					$new_storage_type,
					__( 'Name', 'pods' ),
					$pod->get_name(),
					__( 'Type', 'pods' ),
					$pod_type,
					__( 'ID', 'pods' ),
					$pod->get_id()
				);
			} catch ( Throwable $exception ) {
				$this->errors[] = ucwords( str_replace( '_', ' ', __FUNCTION__ ) ) . ' > ' . $exception->getMessage() . ' (' . $pod->get_name() . ' - #' . $pod->get_id() . ')';
			}
		}

		return $messages;
	}

	/**
	 * Maybe setup group if there are no groups.
	 *
	 * @since 2.9.4
	 *
	 * @param Pod    $pod  The Pod object.
	 * @param string $mode The repair mode (upgrade or full).
	 *
	 * @return int|null The group ID if created, otherwise null if repair not needed.
	 */
	protected function maybe_setup_group_if_no_groups( Pod $pod, $mode ) {
		$this->setup();

		$groups = $pod->get_groups( [
			'fallback_mode' => false,
		] );

		// Groups exist, no need to create a group.
		if ( ! empty( $groups ) ) {
			return null;
		}

		// For upgrade mode, we create the first group even if there are no fields.
		if ( 'upgrade' !== $mode ) {
			$fields = $pod->get_fields( [
				'fallback_mode' => false,
			] );

			// No fields, no need to create a group.
			if ( empty( $fields ) ) {
				return null;
			}
		}

		$group_label = __( 'Details', 'pods' );

		if ( in_array( $pod->get_type(), [ 'post_type', 'taxonomy', 'user', 'comment', 'media' ], true ) ) {
			$group_label = __( 'More Fields', 'pods' );
		}

		/**
		 * Filter the title of the Pods Metabox used in the post editor.
		 *
		 * @since unknown
		 *
		 * @param string  $title  The title to use, default is 'More Fields'.
		 * @param obj|Pod $pod    Current Pods Object.
		 * @param array   $fields Array of fields that will go in the metabox.
		 * @param string  $type   The type of Pod.
		 * @param string  $name   Name of the Pod.
		 */
		$group_label = apply_filters( 'pods_meta_default_box_title', $group_label, $pod, $pod->get_fields(), $pod->get_type(), $pod->get_name() );

		$group_name  = sanitize_key( pods_js_name( sanitize_title( $group_label ) ) );

		try {
			$new_group_name = $group_name;

			$counter = 2;

			do {
				$conflicting_group_found = $this->api->load_group( [
					'pod'  => $pod,
					'name' => $group_name,
				] );

				if ( $conflicting_group_found ) {
					$group_name = $new_group_name . '-' . $counter;
				}

				$counter ++;
			} while ( $this->api->load_group( [
				'pod'  => $pod,
				'name' => $group_name,
			] ) );

			if ( 'preview' !== $mode ) {
				// Setup first group.
				$group_id = $this->api->save_group( [
					'pod'   => $pod,
					'name'  => $group_name,
					'label' => $group_label,
				] );
			} else {
				$group_id = 1234567890123456789;
			}

			if ( $group_id && is_numeric( $group_id ) ) {
				return $group_id;
			}

			throw new Exception( __( 'Failed to create new default group.', 'pods' ) );
		} catch ( Throwable $exception ) {
			$this->errors[] = ucwords( str_replace( '_', ' ', __FUNCTION__ ) ) . ' > ' . $exception->getMessage() . ' (' . $group_name . ')';
		}

		return null;
	}

	/**
	 * Maybe resolve group conflicts.
	 *
	 * @since 2.9.4
	 *
	 * @param Pod    $pod  The Pod object.
	 * @param string $mode The repair mode (preview, upgrade, or full).
	 *
	 * @return string[] The label, name, and ID for each group resolved.
	 */
	protected function maybe_resolve_group_conflicts( Pod $pod, $mode ) {
		$this->setup();

		// Find any group on the pod that has the same name as another group.
		global $wpdb;

		$sql = "
			SELECT DISTINCT
				`primary`.`ID`,
				`primary`.`post_name`
			FROM `{$wpdb->posts}` AS `primary`
			LEFT JOIN `{$wpdb->posts}` AS `duplicate`
				ON `duplicate`.`post_name` = `primary`.`post_name`
			WHERE
				`primary`.`post_type` = %s
				AND `primary`.`post_parent` = %d
				AND `duplicate`.`ID` != `primary`.`ID`
				AND `duplicate`.`post_type` = `primary`.`post_type`
				AND `duplicate`.`post_parent` = `primary`.`post_parent`
			ORDER BY `primary`.`ID`
		";

		$duplicate_groups = $wpdb->get_results(
			$wpdb->prepare(
				$sql,
				[
					'_pods_group',
					$pod->get_id(),
				]
			)
		);

		$groups_to_resolve = [];

		foreach ( $duplicate_groups as $duplicate_group ) {
			if ( ! isset( $groups_to_resolve[ $duplicate_group->post_name ] ) ) {
				$groups_to_resolve[ $duplicate_group->post_name ] = [];
			}

			try {
				$group = $this->api->load_group( [ 'id' => $duplicate_group->ID ] );

				if ( $group ) {
					$groups_to_resolve[ $duplicate_group->post_name ][] = $group;
				} else {
					throw new Exception( __( 'Failed to load duplicate group to resolve.', 'pods' ) );
				}
			} catch ( Throwable $exception ) {
				$this->errors[] = ucwords( str_replace( '_', ' ', __FUNCTION__ ) ) . ' > ' . $exception->getMessage() . ' (' . $duplicate_group->post_name . ' - #' . $duplicate_group->ID . ')';
			}
		}

		$resolved_groups = [];

		foreach ( $groups_to_resolve as $group_name => $groups ) {
			if ( 1 < count( $groups ) ) {
				// Remove the first group.
				array_shift( $groups );
			}

			foreach ( $groups as $group ) {
				/** @var Group $group */
				try {
					if ( 'preview' !== $mode ) {
						$this->api->save_group( [
							'id'       => $group->get_id(),
							'pod_data' => $pod,
							'group'    => $group,
							'new_name' => $group_name . '_' . $group->get_id(),
						] );
					}

					$resolved_groups[] = sprintf(
						'%1$s (%2$s: %3$s | %4$s: %5$s | %6$s: %7$d)',
						$group->get_label(),
						__( 'Old Name', 'pods' ),
						$group_name,
						__( 'New Name', 'pods' ),
						$group_name . '_' . $group->get_id(),
						__( 'ID', 'pods' ),
						$group->get_id()
					);
				} catch ( Throwable $exception ) {
					$this->errors[] = ucwords( str_replace( '_', ' ', __FUNCTION__ ) ) . ' > ' . $exception->getMessage() . ' (' . $group->get_name() . ' - #' . $group->get_id() . ')';
				}
			}
		}

		return $resolved_groups;
	}

	/**
	 * Maybe resolve field conflicts.
	 *
	 * @since 2.9.4
	 *
	 * @param Pod    $pod  The Pod object.
	 * @param string $mode The repair mode (preview, upgrade, or full).
	 *
	 * @return string[] The label, name, and ID for each field resolved.
	 */
	protected function maybe_resolve_field_conflicts( Pod $pod, $mode ) {
		$this->setup();

		// Find any field on the pod that has the same name as another field.
		global $wpdb;

		$sql = "
			SELECT DISTINCT
				`primary`.`ID` AS `primary_id`,
				`primary`.`post_name` AS `primary_name`,
				`duplicate`.`ID` AS `duplicate_id`,
				`duplicate`.`post_name` AS `duplicate_name`
			FROM `{$wpdb->posts}` AS `primary`
			LEFT JOIN `{$wpdb->posts}` AS `duplicate`
				ON `duplicate`.`post_name` = `primary`.`post_name`
			WHERE
				`primary`.`post_type` = %s
				AND `primary`.`post_parent` = %d
				AND `duplicate`.`ID` != `primary`.`ID`
				AND `duplicate`.`post_type` = `primary`.`post_type`
				AND `duplicate`.`post_parent` = `primary`.`post_parent`
			ORDER BY `primary`.`ID`
		";

		$duplicate_fields = $wpdb->get_results(
			$wpdb->prepare(
				$sql,
				[
					'_pods_field',
					$pod->get_id(),
				]
			)
		);

		$fields_to_resolve = [];

		foreach ( $duplicate_fields as $duplicate_field ) {
			if ( ! isset( $fields_to_resolve[ $duplicate_field->primary_name ] ) ) {
				$fields_to_resolve[ $duplicate_field->primary_name ] = [];
			}

			try {
				$field = $this->api->load_field( [ 'id' => $duplicate_field->duplicate_id ] );

				if ( $field ) {
					$fields_to_resolve[ $duplicate_field->primary_name ][] = $field;
				} else {
					throw new Exception( __( 'Failed to load duplicate field to resolve.', 'pods' ) );
				}
			} catch ( Throwable $exception ) {
				$this->errors[] = ucwords( str_replace( '_', ' ', __FUNCTION__ ) ) . ' > ' . $exception->getMessage() . ' (' . $duplicate_field->duplicate_name . ' - #' . $duplicate_field->duplicate_id . ' - Primary: ' . $duplicate_field->primary_name . ' - #' . $duplicate_field->primary_id . ')';
			}
		}

		$resolved_fields = [];

		foreach ( $fields_to_resolve as $primary_field_name => $fields ) {
			foreach ( $fields as $field ) {
				/** @var Field $field */
				try {
					if ( 'preview' !== $mode ) {
						// Prevent renaming the original field data by using a temp one first, then renaming that.
						wp_update_post( [
							'ID'        => $field->get_id(),
							'post_name' => '_temp_' . $primary_field_name . '_' . $field->get_id(),
						] );

						// Flush the field cache.
						$this->api->cache_flush_fields();

						// Save the field with the new name.
						$this->api->save_field( [
							'id'       => $field->get_id(),
							'pod_data' => $pod,
							'field'    => $field,
							'new_name' => $primary_field_name . '_' . $field->get_id(),
						], false );
					}

					$resolved_fields[] = sprintf(
						'%1$s (%2$s: %3$s | %4$s: %5$s | %6$s: %7$d)',
						$field->get_label(),
						__( 'Old Name', 'pods' ),
						$primary_field_name,
						__( 'New Name', 'pods' ),
						$primary_field_name . '_' . $field->get_id(),
						__( 'ID', 'pods' ),
						$field->get_id()
					);
				} catch ( Throwable $exception ) {
					$this->errors[] = ucwords( str_replace( '_', ' ', __FUNCTION__ ) ) . ' > ' . $exception->getMessage() . ' (' . $field->get_name() . ' - #' . $field->get_id() . ')';
				}
			}
		}

		return $resolved_fields;
	}

	/**
	 * Maybe reassign fields with invalid groups.
	 *
	 * @since 2.9.4
	 *
	 * @param Pod    $pod      The Pod object.
	 * @param int    $group_id The group ID.
	 * @param string $mode     The repair mode (preview, upgrade, or full).
	 *
	 * @return string[] The label, name, and ID for each field reassigned.
	 */
	protected function maybe_reassign_fields_with_invalid_groups( Pod $pod, $group_id, $mode ) {
		$this->setup();

		// Get all known group IDs.
		$groups = $pod->get_groups( [
			'fallback_mode' => false,
		] );

		$groups = wp_list_pluck( $groups, 'id' );
		$groups = array_values( array_filter( $groups ) );

		if ( $group_id ) {
			$groups[] = $group_id;
		}

		$groups = array_unique( $groups );

		$fields = $pod->get_fields( [
			'fallback_mode' => false,
			'meta_query'    => [
				[
					'key'     => 'group',
					'value'   => $groups,
					'compare' => 'NOT IN',
				],
			],
		] );

		return $this->reassign_fields_to_group( $fields, $group_id, $pod, $mode );
	}

	/**
	 * Maybe reassign orphan fields.
	 *
	 * @since 2.9.4
	 *
	 * @param Pod    $pod      The Pod object.
	 * @param int    $group_id The group ID.
	 * @param string $mode     The repair mode (preview, upgrade, or full).
	 *
	 * @return string[] The label, name, and ID for each field reassigned.
	 */
	protected function maybe_reassign_orphan_fields( Pod $pod, $group_id, $mode ) {
		$this->setup();

		$fields = $pod->get_fields( [
			'fallback_mode' => false,
			'group'         => null,
		] );

		return $this->reassign_fields_to_group( $fields, $group_id, $pod, $mode );
	}

	/**
	 * Reassign fields to a specific group.
	 *
	 * @since 2.9.4
	 *
	 * @param Pod    $pod      The Pod object.
	 * @param int    $group_id The group ID.
	 * @param string $mode     The repair mode (preview, upgrade, or full).
	 *
	 * @return string[] The label, name, and ID for each field reassigned.
	 */
	protected function reassign_fields_to_group( $fields, $group_id, $pod, $mode ) {
		$this->setup();

		$reassigned_fields = [];

		foreach ( $fields as $field ) {
			if ( $field->get_arg( 'group' ) === $group_id ) {
				continue;
			}

			try {
				if ( 'preview' !== $mode ) {
					$this->api->save_field( [
						'id'           => $field->get_id(),
						'pod_data'     => $pod,
						'field'        => $field,
						'new_group_id' => $group_id,
					] );

					$field->set_arg( 'group', $group_id );
				}

				$reassigned_fields[] = sprintf(
					'%1$s (%2$s: %3$s | %4$s: %5$d)',
					$field->get_label(),
					__( 'Name', 'pods' ),
					$field->get_name(),
					__( 'ID', 'pods' ),
					$field->get_id()
				);
			} catch ( Throwable $exception ) {
				$this->errors[] = ucwords( str_replace( '_', ' ', __FUNCTION__ ) ) . ' > ' . $exception->getMessage() . ' (' . $field->get_name() . ' - #' . $field->get_id() . ')';
			}
		}

		return $reassigned_fields;
	}

	/**
	 * Maybe fix fields with invalid field type.
	 *
	 * @since 2.9.4
	 *
	 * @param Pod    $pod  The Pod object.
	 * @param string $mode The repair mode (preview, upgrade, or full).
	 *
	 * @return string[] The label, name, and ID for each field fixed.
	 */
	protected function maybe_fix_fields_with_invalid_field_type( Pod $pod, $mode ) {
		$this->setup();

		$supported_field_types = PodsForm::field_types_list();

		$fields = $pod->get_fields( [
			'fallback_mode' => false,
			'meta_query'    => [
				'relation' => 'OR',
				[
					'key'     => 'type',
					'value'   => $supported_field_types,
					'compare' => 'NOT IN',
				],
				[
					'key'     => 'type',
					'compare' => 'NOT EXISTS',
				],
			],
		] );

		$fixed_fields = [];

		foreach ( $fields as $field ) {
			try {
				$old_type = $field->get_type();

				if ( empty( $old_type ) ) {
					$old_type = __( 'N/A', 'pods' );
				}

				if ( 'preview' !== $mode ) {
					$this->api->save_field( [
						'id'       => $field->get_id(),
						'pod_data' => $pod,
						'field'    => $field,
						'type'     => 'text',
					] );

					$field->set_arg( 'type', 'text' );
				}

				$fixed_fields[] = sprintf(
					'%1$s (%2$s: %3$s | %4$s: %5$s | %6$s: %7$d)',
					$field->get_label(),
					__( 'Old Type', 'pods' ),
					$old_type,
					__( 'Name', 'pods' ),
					$field->get_name(),
					__( 'ID', 'pods' ),
					$field->get_id()
				);
			} catch ( Throwable $exception ) {
				$this->errors[] = ucwords( str_replace( '_', ' ', __FUNCTION__ ) ) . ' > ' . $exception->getMessage() . ' (' . $field->get_name() . ' - #' . $field->get_id() . ')';
			}
		}

		return $fixed_fields;
	}

	/**
	 * Maybe fix pod fields with invalid arguments.
	 *
	 * @since 3.0.4
	 *
	 * @param Pod    $pod  The Pod object.
	 * @param string $mode The repair mode (preview, upgrade, or full).
	 *
	 * @return string[] The label, name, and ID for each field fixed.
	 */
	protected function maybe_fix_fields_with_invalid_args( Pod $pod, $mode ) {
		$this->setup();

		$invalid_args = [
			'conditional_logic',
			'attributes',
			'grouped',
			'depends-on',
			'depends-on-any',
			'depends-on-multi',
			'excludes-on',
			'wildcard-on',
		];

		$fixed_fields = [];

		foreach ( $invalid_args as $invalid_arg ) {
			$meta_query_check = [
				'key'     => $invalid_arg,
				'compare' => 'EXISTS',
			];

			if ( 'conditional_logic' === $invalid_arg ) {
				$meta_query_check['value']   = 'a:0:{}';
				$meta_query_check['compare'] = 'LIKE';
			}

			$fields = $pod->get_fields( [
				'fallback_mode' => false,
				'meta_query'    => [
					$meta_query_check,
				],
			] );

			foreach ( $fields as $field ) {
				$fixed_field = $this->maybe_fix_fields_with_invalid_args_for_field( $pod, $field, $invalid_arg, $mode );

				if ( $fixed_field ) {
					$fixed_fields[] = $fixed_field;
				}
			}
		}

		return $fixed_fields;
	}

	/**
	 * Maybe fix a field with invalid arguments.
	 *
	 * @since 3.0.4
	 *
	 * @param Pod    $pod         The Pod object.
	 * @param Field  $field       The Field object.
	 * @param string $invalid_arg The invalid argument.
	 * @param string $mode        The repair mode (preview, upgrade, or full).
	 *
	 * @return string[]|false The label, name, and ID for the field fixed, or false if not fixed.
	 */
	protected function maybe_fix_fields_with_invalid_args_for_field( Pod $pod, Field $field, string $invalid_arg, $mode ) {
		$this->setup();

		$field_id = $field->get_id();

		if ( empty( $field_id ) ) {
			return false;
		}

		$invalid_args = [
			'conditional_logic',
			'attributes',
			'grouped',
			'depends-on',
			'depends-on-any',
			'depends-on-multi',
			'excludes-on',
			'wildcard-on',
		];

		try {
			$found_invalid_args = [
				$invalid_arg => null,
			];

			foreach ( $invalid_args as $other_invalid_arg ) {
				$arg_value = $field->get_arg( $other_invalid_arg, null, false, true );

				if ( null !== $arg_value ) {
					if (
						'conditional_logic' !== $invalid_arg
						&& 'conditional_logic' === $other_invalid_arg
						&& (
							empty( $arg_value )
							|| is_array( $arg_value )
						)
					) {
						continue;
					}

					$found_invalid_args[ $other_invalid_arg ] = $arg_value;
				}
			}

			if ( 'preview' !== $mode ) {
				foreach ( $found_invalid_args as $found_invalid_arg => $arg_value ) {
					if ( 'conditional_logic' === $found_invalid_arg ) {
						update_post_meta( $field_id, 'enable_conditional_logic', 0 );

						$field->set_arg( 'enable_conditional_logic', 0 );
					}

					delete_post_meta( $field_id, $found_invalid_arg );

					$field->set_arg( $found_invalid_arg, null );
				}

				pods_api()->cache_flush_fields();
			}

			return sprintf(
				'%1$s (%2$s: [%3$s] | %4$s: %5$s | %6$s: %7$d)',
				$field->get_label(),
				__( 'Fixed invalid conditional logic args', 'pods' ),
				implode( ', ', array_keys( $found_invalid_args ) ),
				__( 'Name', 'pods' ),
				$field->get_name(),
				__( 'ID', 'pods' ),
				$field->get_id()
			);
		} catch ( Throwable $exception ) {
			$this->errors[] = ucwords( str_replace( '_', ' ', __FUNCTION__ ) ) . ' > ' . $exception->getMessage() . ' (' . $field->get_name() . ' - #' . $field->get_id() . ')';
		}

		return false;
	}

}
