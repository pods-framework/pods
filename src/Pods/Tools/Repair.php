<?php

namespace Pods\Tools;

// Don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

use Exception;
use Throwable;
use PodsForm;
use Pods\Whatsit;
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
	 * Repair Pod configurations.
	 *
	 * @since 3.1.0
	 *
	 * @param string $mode The repair mode (preview, upgrade, or full).
	 *
	 * @return array The results with information about the repair done.
	 */
	public function repair_pods( $mode ) {
		$this->setup();

		$this->errors    = [];
		$this->conflicts = [];

		$is_preview_mode = 'preview' === $mode;

		$tool_heading = __( 'Repair results', 'pods' );

		$results = [];

		try {
			$results[ __( 'Check for duplicate Pods in the database', 'pods' ) ] = $this->maybe_resolve_pod_conflicts( $mode );
		} catch ( Conflict_Exception $exception ) {
			// Another plugin is altering the results we rely on, stop before anything else is changed.
			return $this->get_conflict_results( $tool_heading, $results, $mode );
		}

		// Check if changes were made to the Pod.
		$changes_made = [] !== array_filter( $results );

		if ( ! $is_preview_mode && $changes_made ) {
			$this->api->cache_flush_pods();
		}

		$results['message_html'] = $this->get_message_html( $tool_heading, $results, $mode );

		return $results;
	}

	/**
	 * Maybe resolve pod conflicts.
	 *
	 * @since 3.1.0
	 *
	 * @param string $mode The repair mode (preview, upgrade, or full).
	 *
	 * @return string[] The label, name, and ID for each pod resolved.
	 *
	 * @throws Conflict_Exception If another plugin is conflicting with the queries this tool relies on.
	 */
	protected function maybe_resolve_pod_conflicts( $mode ) {
		$this->setup();

		// Find any pod that has the same name as another pod.
		global $wpdb;

		$duplicate_pods = $wpdb->get_results(
			$wpdb->prepare(
				"
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
						AND `duplicate`.`ID` != `primary`.`ID`
						AND `duplicate`.`post_type` = `primary`.`post_type`
					ORDER BY `primary`.`ID`
				",
				'_pods_pod'
			)
		);

		$pods_to_resolve = [];

		$duplicate_ids = [];

		foreach ( $duplicate_pods as $duplicate_pod ) {
			// Skip if we have already referenced a primary pod.
			if (
				isset( $duplicate_ids[ $duplicate_pod->duplicate_id ] )
				&& $duplicate_ids[ $duplicate_pod->duplicate_id ] === $duplicate_pod->primary_id
			) {
				continue;
			}

			$duplicate_ids[ $duplicate_pod->primary_id ] = $duplicate_pod->duplicate_id;

			if ( ! isset( $pods_to_resolve[ $duplicate_pod->primary_name ] ) ) {
				$pods_to_resolve[ $duplicate_pod->primary_name ] = [];
			}

			try {
				$pod = $this->api->load_pod( [ 'id' => $duplicate_pod->duplicate_id ] );

				if ( $pod ) {
					// Confirm Pods loaded the same Pod that the database says it should have.
					$this->confirm_config_matches_db(
						__( 'Load duplicate Pod to resolve', 'pods' ),
						$pod,
						$duplicate_pod->duplicate_id,
						$duplicate_pod->duplicate_name
					);

					$pods_to_resolve[ $duplicate_pod->primary_name ][] = $pod;
				} else {
					throw new Exception( __( 'Failed to load duplicate pod to resolve.', 'pods' ) );
				}
			} catch ( Conflict_Exception $exception ) {
				throw $exception;
			} catch ( Throwable $exception ) {
				$this->errors[] = ucwords( str_replace( '_', ' ', __FUNCTION__ ) ) . ' > ' . $exception->getMessage() . ' (' . $duplicate_pod->duplicate_name . ' - #' . $duplicate_pod->duplicate_id . ' - Primary: ' . $duplicate_pod->primary_name . ' - #' . $duplicate_pod->primary_id . ')';
			}
		}

		$resolved_pods = [];

		foreach ( $pods_to_resolve as $primary_pod_name => $pods ) {
			foreach ( $pods as $pod ) {
				/** @var Pod $pod */
				try {
					if ( 'preview' !== $mode ) {
						// Prevent renaming the original pod data by using a temp one first, then renaming that.
						wp_update_post( [
							'ID'        => $pod->get_id(),
							'post_name' => '_temp_' . $primary_pod_name . '_' . $pod->get_id(),
						] );

						// Flush the pod cache.
						$this->api->cache_flush_pods();

						// Save the pod with the new name.
						$this->api->save_pod( [
							'id'       => $pod->get_id(),
							'old_name' => '_temp_' . $primary_pod_name . '_' . $pod->get_id(),
							'name'     => $primary_pod_name . '_' . $pod->get_id(),
							'label'    => $pod->get_label() . ' (' . $pod->get_id() . ')',
						], false );

						$pod->flush();
					}

					$resolved_pods[] = sprintf(
						'%1$s (%2$s: %3$s | %4$s: %5$s | %6$s: %7$d)',
						$pod->get_label(),
						__( 'Old Name', 'pods' ),
						$primary_pod_name,
						__( 'New Name', 'pods' ),
						$primary_pod_name . '_' . $pod->get_id(),
						__( 'ID', 'pods' ),
						$pod->get_id()
					);
				} catch ( Throwable $exception ) {
					$this->errors[] = ucwords( str_replace( '_', ' ', __FUNCTION__ ) ) . ' > ' . $exception->getMessage() . ' (' . $pod->get_name() . ' - #' . $pod->get_id() . ')';
				}
			}
		}

		return $resolved_pods;
	}

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

		$this->errors    = [];
		$this->conflicts = [];

		$is_upgrade_mode = 'upgrade' === $mode;

		$tool_heading = sprintf(
			// translators: %s: The Pod label.
			__( 'Repair results for %s', 'pods' ),
			$pod->get_label() . ' (' . $pod->get_name() . ')'
		);

		$results = [];

		try {
			$this->repair_groups_and_fields_for_pod_data( $pod, $mode, $results );
		} catch ( Conflict_Exception $exception ) {
			// Another plugin is altering the results we rely on, stop before anything else is changed.
			$results = $this->get_conflict_results( $tool_heading, $results, $mode );

			if ( $is_upgrade_mode ) {
				$results['upgraded_pod'] = $pod;
			}

			return $results;
		}

		$results['message_html'] = $this->get_message_html( $tool_heading, $results, $mode );

		if ( $is_upgrade_mode ) {
			$results['upgraded_pod'] = $pod;
		}

		return $results;
	}

	/**
	 * Run the repairs for the Groups and Fields of a Pod.
	 *
	 * @since 3.4.0
	 *
	 * @param Pod    $pod     The Pod object.
	 * @param string $mode    The repair mode (preview, upgrade, or full).
	 * @param array  $results The results with information about the repair done, passed by reference so that the
	 *                        repairs that completed are still reported if the tool has to stop.
	 *
	 * @throws Conflict_Exception If another plugin is conflicting with the queries this tool relies on.
	 */
	protected function repair_groups_and_fields_for_pod_data( Pod $pod, $mode, array &$results ) {
		$is_preview_mode = 'preview' === $mode;
		$is_upgrade_mode = 'upgrade' === $mode;
		$is_migrated     = 1 === (int) $pod->get_arg( '_migrated_28' );

		// Confirm the Pod configuration we are about to repair matches the database before changing anything.
		$this->confirm_pod_matches_db( $pod );

		// Maybe set up a new group if no groups are found for the Pod.
		$group_id = $this->maybe_setup_group_if_no_groups( $pod, $mode );

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
				$this->errors[] = ucwords( str_replace( '_', ' ', __FUNCTION__ ) ) . ' > ' . $exception->getMessage() . ' (' . $pod->get_name() . ' - #' . $pod->get_id() . ')';
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
	 *
	 * @throws Conflict_Exception If another plugin is conflicting with the queries this tool relies on.
	 */
	protected function maybe_setup_group_if_no_groups( Pod $pod, $mode ) {
		$this->setup();

		$groups = $pod->get_groups( [
			'fallback_mode' => false,
		] );

		// Creating a group when the Pod already has one in the database would add a duplicate group.
		$this->confirm_groups_match_db( $pod, $groups, __( 'Check whether the Pod has any groups', 'pods' ) );

		// Groups exist, no need to create a group.
		if ( ! empty( $groups ) ) {
			return null;
		}

		// For upgrade mode, we create the first group even if there are no fields.
		if ( 'upgrade' !== $mode ) {
			$fields = $pod->get_fields( [
				'fallback_mode' => false,
			] );

			$this->confirm_fields_match_db( $pod, $fields, __( 'Check whether the Pod has any fields', 'pods' ) );

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

			// Confirm the name we settled on is really not in use before we create a group with it.
			$this->confirm_config_name_available_in_db(
				__( 'Find an available name for the new group', 'pods' ),
				'group',
				$pod->get_id(),
				$group_name
			);

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
		} catch ( Conflict_Exception $exception ) {
			throw $exception;
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
	 *
	 * @throws Conflict_Exception If another plugin is conflicting with the queries this tool relies on.
	 */
	protected function maybe_resolve_group_conflicts( Pod $pod, $mode ) {
		$this->setup();

		// Find any group on the pod that has the same name as another group.
		global $wpdb;

		$duplicate_groups = $wpdb->get_results(
			$wpdb->prepare(
				"
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
				",
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
					// Confirm Pods loaded the same group that the database says it should have.
					$this->confirm_config_matches_db(
						__( 'Load duplicate group to resolve', 'pods' ),
						$group,
						$duplicate_group->ID,
						$duplicate_group->post_name
					);

					$groups_to_resolve[ $duplicate_group->post_name ][] = $group;
				} else {
					throw new Exception( __( 'Failed to load duplicate group to resolve.', 'pods' ) );
				}
			} catch ( Conflict_Exception $exception ) {
				throw $exception;
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
	 *
	 * @throws Conflict_Exception If another plugin is conflicting with the queries this tool relies on.
	 */
	protected function maybe_resolve_field_conflicts( Pod $pod, $mode ) {
		$this->setup();

		// Find any field on the pod that has the same name as another field.
		global $wpdb;

		$duplicate_fields = $wpdb->get_results(
			$wpdb->prepare(
				"
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
				",
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
					// Confirm Pods loaded the same field that the database says it should have.
					$this->confirm_config_matches_db(
						__( 'Load duplicate field to resolve', 'pods' ),
						$field,
						$duplicate_field->duplicate_id,
						$duplicate_field->duplicate_name
					);

					$fields_to_resolve[ $duplicate_field->primary_name ][] = $field;
				} else {
					throw new Exception( __( 'Failed to load duplicate field to resolve.', 'pods' ) );
				}
			} catch ( Conflict_Exception $exception ) {
				throw $exception;
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
	 *
	 * @throws Conflict_Exception If another plugin is conflicting with the queries this tool relies on.
	 */
	protected function maybe_reassign_fields_with_invalid_groups( Pod $pod, $group_id, $mode ) {
		$this->setup();

		// Get all known group IDs.
		$groups = $pod->get_groups( [
			'fallback_mode' => false,
		] );

		// A group that Pods did not return would make every field in it look like it has an invalid group.
		$this->confirm_groups_match_db( $pod, $groups, __( 'Get all known group IDs', 'pods' ) );

		$groups = wp_list_pluck( $groups, 'id' );
		$groups = array_values( array_filter( $groups ) );

		if ( $group_id ) {
			$groups[] = $group_id;
		}

		$groups = array_unique( $groups );

		$meta_query = [
			[
				'key'     => 'group',
				'value'   => $groups,
				'compare' => 'NOT IN',
			],
		];

		$fields = $pod->get_fields( [
			'fallback_mode' => false,
			'meta_query'    => $meta_query, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		] );

		$this->confirm_fields_match_db( $pod, $fields, __( 'Find fields assigned to a group that does not exist', 'pods' ), $meta_query );

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
	 *
	 * @throws Conflict_Exception If another plugin is conflicting with the queries this tool relies on.
	 */
	protected function maybe_reassign_orphan_fields( Pod $pod, $group_id, $mode ) {
		$this->setup();

		$fields = $pod->get_fields( [
			'fallback_mode' => false,
			'group'         => null,
		] );

		// The `group => null` argument becomes a "NOT EXISTS" meta query for the `group` meta key.
		$this->confirm_fields_match_db( $pod, $fields, __( 'Find fields not assigned to any group', 'pods' ), [
			[
				'key'     => 'group',
				'compare' => 'NOT EXISTS',
			],
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
	 *
	 * @throws Conflict_Exception If another plugin is conflicting with the queries this tool relies on.
	 */
	protected function reassign_fields_to_group( $fields, $group_id, $pod, $mode ) {
		$this->setup();

		$reassigned_fields = [];

		if ( empty( $fields ) ) {
			return $reassigned_fields;
		}

		// Reassigning fields to a group that is not really there would orphan every one of them.
		if ( 'preview' !== $mode ) {
			$this->confirm_group_exists_in_db( $pod, $group_id, __( 'Reassign fields to a group', 'pods' ) );
		}

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
	 *
	 * @throws Conflict_Exception If another plugin is conflicting with the queries this tool relies on.
	 */
	protected function maybe_fix_fields_with_invalid_field_type( Pod $pod, $mode ) {
		$this->setup();

		$supported_field_types = PodsForm::field_types_list();

		$meta_query = [
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
		];

		$fields = $pod->get_fields( [
			'fallback_mode' => false,
			'meta_query'    => $meta_query, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		] );

		// A broken meta query here would reset every field on the Pod to the "text" field type.
		$this->confirm_fields_match_db( $pod, $fields, __( 'Find fields with an invalid field type', 'pods' ), $meta_query );

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
	 *
	 * @throws Conflict_Exception If another plugin is conflicting with the queries this tool relies on.
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

			$meta_query = [
				$meta_query_check,
			];

			$fields = $pod->get_fields( [
				'fallback_mode' => false,
				'meta_query'    => $meta_query, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			] );

			// A broken meta query here would delete field settings that are perfectly valid.
			$this->confirm_fields_match_db(
				$pod,
				$fields,
				sprintf(
					// translators: %s: The field argument name.
					__( 'Find fields with the invalid "%s" argument', 'pods' ),
					$invalid_arg
				),
				$meta_query
			);

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
	 *
	 * @throws Conflict_Exception If another plugin is conflicting with the queries this tool relies on.
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

			// Confirm each argument we are about to delete is really stored on the field.
			$this->confirm_field_args_match_db(
				$field,
				array_keys( $found_invalid_args ),
				__( 'Remove invalid field arguments', 'pods' )
			);

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
		} catch ( Conflict_Exception $exception ) {
			throw $exception;
		} catch ( Throwable $exception ) {
			$this->errors[] = ucwords( str_replace( '_', ' ', __FUNCTION__ ) ) . ' > ' . $exception->getMessage() . ' (' . $field->get_name() . ' - #' . $field->get_id() . ')';
		}

		return false;
	}

	/*
	 * Plugin conflict detection.
	 *
	 * This tool decides what to repair from what Pods and WP_Query return. Those results travel through
	 * `pre_get_posts`, `posts_*`, `get_meta_*`, and object/transient caching, so another plugin can change
	 * them without Pods ever knowing. When that happens the tool repairs the wrong things, which is how
	 * configurations end up corrupted.
	 *
	 * Everything below re-runs those lookups as direct database queries that no filter or cache can reach,
	 * compares the two, and stops the tool when they disagree.
	 */

	/**
	 * Determine whether the configurations for a Pod can be verified against the database.
	 *
	 * Only Pods stored in the database have configurations to compare against. Pods registered in code
	 * (by a theme or another plugin) have no posts to look up, so there is nothing to verify.
	 *
	 * @since 3.4.0
	 *
	 * @param Pod $pod The Pod object.
	 *
	 * @return bool Whether the configurations for the Pod can be verified against the database.
	 */
	protected function can_verify_against_db( Pod $pod ) {
		return 'post_type' === $pod->get_object_storage_type() && 0 < (int) $pod->get_id();
	}

	/**
	 * Get the maximum number of configurations that a Pods lookup will return.
	 *
	 * @since 3.4.0
	 *
	 * @return int The maximum number of configurations that a Pods lookup will return.
	 */
	protected function get_find_limit() {
		/** This filter is documented in src/Pods/Whatsit/Storage/Post_Type.php */
		return (int) apply_filters( 'pods_whatsit_storage_post_type_find_limit', 300 );
	}

	/**
	 * Record a conflict with another plugin and stop the tool.
	 *
	 * @since 3.4.0
	 *
	 * @param string $context The lookup that was being verified.
	 * @param string $message The details about what did not match.
	 *
	 * @throws Conflict_Exception Always, so that the tool stops before it changes anything else.
	 */
	protected function stop_for_conflict( $context, $message ) {
		$conflict = $context . ' > ' . $message;

		$this->conflicts[] = $conflict;

		throw new Conflict_Exception( $conflict );
	}

	/**
	 * Get the configuration post IDs directly from the database.
	 *
	 * This runs a plain SQL query on purpose. It does not use Pods(), WP_Query, get_posts(), WP_Meta_Query,
	 * or any cache, so the results cannot be changed by another plugin.
	 *
	 * @since 3.4.0
	 *
	 * @param string   $object_type The Pods object type (pod, group, or field).
	 * @param null|int $parent_id   The parent post ID to limit the results to.
	 * @param array    $meta_checks The meta checks to run, in the same shape as a meta query but only
	 *                              supporting the `=`, `IN`, `NOT IN`, `LIKE`, `EXISTS`, and `NOT EXISTS`
	 *                              comparisons that this tool uses.
	 *
	 * @return int[] The list of configuration post IDs found.
	 */
	protected function get_config_ids_from_db( $object_type, $parent_id = null, array $meta_checks = [] ) {
		global $wpdb;

		$relation = 'AND';

		if ( isset( $meta_checks['relation'] ) ) {
			$relation = 'OR' === strtoupper( (string) $meta_checks['relation'] ) ? 'OR' : 'AND';

			unset( $meta_checks['relation'] );
		}

		$joins      = [];
		$where      = [];
		$where_meta = [];

		// Placeholder values are kept per clause because they have to be passed in the order they appear in the query.
		$join_args       = [];
		$where_args      = [];
		$where_meta_args = [];

		$where[]      = '`primary`.`post_type` = %s';
		$where_args[] = '_pods_' . $object_type;

		// Matches the post statuses that post type storage looks for.
		$where[] = "`primary`.`post_status` IN ( 'publish', 'draft' )";

		if ( null !== $parent_id ) {
			$where[]      = '`primary`.`post_parent` = %d';
			$where_args[] = (int) $parent_id;
		}

		foreach ( array_values( $meta_checks ) as $index => $meta_check ) {
			if ( ! is_array( $meta_check ) || empty( $meta_check['key'] ) ) {
				continue;
			}

			$alias   = 'meta_' . $index;
			$compare = isset( $meta_check['compare'] ) ? strtoupper( (string) $meta_check['compare'] ) : '=';
			$value   = array_key_exists( 'value', $meta_check ) ? $meta_check['value'] : null;

			$joins[]     = "LEFT JOIN `{$wpdb->postmeta}` AS `{$alias}` ON `{$alias}`.`post_id` = `primary`.`ID` AND `{$alias}`.`meta_key` = %s";
			$join_args[] = $meta_check['key'];

			switch ( $compare ) {
				case 'NOT EXISTS':
					$where_meta[] = "`{$alias}`.`meta_id` IS NULL";

					break;
				case 'EXISTS':
					$where_meta[] = "`{$alias}`.`meta_id` IS NOT NULL";

					break;
				case 'LIKE':
					$where_meta[]      = "`{$alias}`.`meta_value` LIKE %s";
					$where_meta_args[] = '%' . $wpdb->esc_like( (string) $value ) . '%';

					break;
				case 'IN':
				case 'NOT IN':
					$value = array_map( 'strval', (array) $value );

					if ( [] === $value ) {
						// An empty IN() matches nothing and an empty NOT IN() matches every row with the meta key.
						$where_meta[] = 'IN' === $compare ? '1 = 0' : "`{$alias}`.`meta_id` IS NOT NULL";

						break;
					}

					$placeholders = implode( ', ', array_fill( 0, count( $value ), '%s' ) );

					// A row without the meta key has a NULL meta value, which matches neither IN() nor NOT IN().
					$where_meta[]    = "`{$alias}`.`meta_value` {$compare} ( {$placeholders} )";
					$where_meta_args = array_merge( $where_meta_args, $value );

					break;
				default:
					$where_meta[]      = "`{$alias}`.`meta_value` = %s";
					$where_meta_args[] = (string) $value;

					break;
			}//end switch
		}//end foreach

		if ( ! empty( $where_meta ) ) {
			$where[] = '( ' . implode( ' ' . $relation . ' ', $where_meta ) . ' )';
		}

		// The JOIN placeholders come first in the query, then the WHERE ones, then the meta value ones.
		$prepare_args = array_merge( $join_args, $where_args, $where_meta_args );

		$ids = $wpdb->get_col(
			$wpdb->prepare(
				"
					SELECT DISTINCT `primary`.`ID`
					FROM `{$wpdb->posts}` AS `primary`
					" . implode( "\n\t\t\t\t\t", $joins ) . "
					WHERE " . implode( "\n\t\t\t\t\t\tAND ", $where ) . "
					ORDER BY `primary`.`ID`
				",
				$prepare_args
			)
		);

		return array_map( 'absint', (array) $ids );
	}

	/**
	 * Get the configuration names directly from the database, keyed by post ID.
	 *
	 * @since 3.4.0
	 *
	 * @param string   $object_type The Pods object type (pod, group, or field).
	 * @param null|int $parent_id   The parent post ID to limit the results to.
	 *
	 * @return string[] The list of configuration names, keyed by post ID.
	 */
	protected function get_config_names_from_db( $object_type, $parent_id = null ) {
		global $wpdb;

		$prepare_args = [
			'_pods_' . $object_type,
		];

		$parent_where = '';

		if ( null !== $parent_id ) {
			$parent_where   = 'AND `primary`.`post_parent` = %d';
			$prepare_args[] = (int) $parent_id;
		}

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"
					SELECT
						`primary`.`ID`,
						`primary`.`post_name`
					FROM `{$wpdb->posts}` AS `primary`
					WHERE
						`primary`.`post_type` = %s
						{$parent_where}
						AND `primary`.`post_status` IN ( 'publish', 'draft' )
				",
				$prepare_args
			)
		);

		$names = [];

		foreach ( (array) $results as $result ) {
			$names[ (int) $result->ID ] = (string) $result->post_name;
		}

		return $names;
	}

	/**
	 * Get the meta values for a configuration directly from the database.
	 *
	 * @since 3.4.0
	 *
	 * @param int    $post_id  The configuration post ID.
	 * @param string $meta_key The meta key to get the values for.
	 *
	 * @return string[] The list of meta values found.
	 */
	protected function get_config_meta_from_db( $post_id, $meta_key ) {
		global $wpdb;

		$values = $wpdb->get_col(
			$wpdb->prepare(
				"
					SELECT `meta_value`
					FROM `{$wpdb->postmeta}`
					WHERE
						`post_id` = %d
						AND `meta_key` = %s
				",
				[
					(int) $post_id,
					$meta_key,
				]
			)
		);

		return (array) $values;
	}

	/**
	 * Confirm the configurations returned by a Pods lookup match the ones found in the database.
	 *
	 * @since 3.4.0
	 *
	 * @param string $context      The lookup being verified.
	 * @param int[]  $queried_ids  The configuration IDs that the Pods lookup returned.
	 * @param int[]  $verified_ids The configuration IDs found with a direct database query.
	 * @param array  $names_by_id  The names of every configuration of this type on the Pod, keyed by ID.
	 *
	 * @throws Conflict_Exception If another plugin is conflicting with the lookup.
	 */
	protected function confirm_lookup_matches_db( $context, array $queried_ids, array $verified_ids, array $names_by_id ) {
		$queried_ids  = array_values( array_unique( array_filter( array_map( 'absint', $queried_ids ) ) ) );
		$verified_ids = array_values( array_unique( array_filter( array_map( 'absint', $verified_ids ) ) ) );

		// Anything Pods returned that the database does not agree with would be repaired for no reason.
		$unexpected_ids = array_diff( $queried_ids, $verified_ids );

		if ( ! empty( $unexpected_ids ) ) {
			$this->stop_for_conflict(
				$context,
				sprintf(
					// translators: 1: The number of configurations, 2: The list of configuration IDs.
					__( 'Pods returned %1$d configuration(s) that a direct database query did not match, repairing them would change configurations that are not broken (IDs: %2$s)', 'pods' ),
					count( $unexpected_ids ),
					implode( ', ', $unexpected_ids )
				)
			);
		}

		$missing_ids = array_diff( $verified_ids, $queried_ids );

		if ( empty( $missing_ids ) ) {
			return;
		}

		// Pods keys configurations by name, so only one of each duplicated name is ever returned.
		$queried_names = [];

		foreach ( $queried_ids as $queried_id ) {
			if ( isset( $names_by_id[ $queried_id ] ) ) {
				$queried_names[] = $names_by_id[ $queried_id ];
			}
		}

		$missing_ids = array_filter(
			$missing_ids,
			static function ( $missing_id ) use ( $names_by_id, $queried_names ) {
				return ! isset( $names_by_id[ $missing_id ] ) || ! in_array( $names_by_id[ $missing_id ], $queried_names, true );
			}
		);

		if ( empty( $missing_ids ) ) {
			return;
		}

		$find_limit = $this->get_find_limit();

		// Pods lookups are capped, so when the database has more than that, Pods cannot return them all.
		if ( count( $verified_ids ) > $find_limit ) {
			$this->errors[] = $context . ' > ' . sprintf(
				// translators: 1: The maximum number of configurations, 2: The number of configurations.
				__( 'Pods only looks up %1$d configuration(s) at a time and this Pod has more than that, so %2$d configuration(s) were skipped. Increase the "pods_whatsit_storage_post_type_find_limit" filter and run this tool again to repair the rest.', 'pods' ),
				$find_limit,
				count( $missing_ids )
			);

			return;
		}

		$this->stop_for_conflict(
			$context,
			sprintf(
				// translators: 1: The number of configurations, 2: The list of configuration IDs.
				__( 'A direct database query found %1$d configuration(s) that Pods did not return, repairing the rest without them would break your configuration (IDs: %2$s)', 'pods' ),
				count( $missing_ids ),
				implode( ', ', $missing_ids )
			)
		);
	}

	/**
	 * Confirm the groups returned for a Pod match the ones found in the database.
	 *
	 * @since 3.4.0
	 *
	 * @param Pod     $pod     The Pod object.
	 * @param Group[] $groups  The groups that the Pods lookup returned.
	 * @param string  $context The lookup being verified.
	 *
	 * @throws Conflict_Exception If another plugin is conflicting with the lookup.
	 */
	protected function confirm_groups_match_db( Pod $pod, array $groups, $context ) {
		if ( ! $this->can_verify_against_db( $pod ) ) {
			return;
		}

		$pod_id = $pod->get_id();

		$this->confirm_lookup_matches_db(
			$context,
			wp_list_pluck( $groups, 'id' ),
			$this->get_config_ids_from_db( 'group', $pod_id ),
			$this->get_config_names_from_db( 'group', $pod_id )
		);
	}

	/**
	 * Confirm the fields returned for a Pod match the ones found in the database.
	 *
	 * @since 3.4.0
	 *
	 * @param Pod     $pod         The Pod object.
	 * @param Field[] $fields      The fields that the Pods lookup returned.
	 * @param string  $context     The lookup being verified.
	 * @param array   $meta_checks The meta query that the Pods lookup used, to run directly against the database.
	 *
	 * @throws Conflict_Exception If another plugin is conflicting with the lookup.
	 */
	protected function confirm_fields_match_db( Pod $pod, array $fields, $context, array $meta_checks = [] ) {
		if ( ! $this->can_verify_against_db( $pod ) ) {
			return;
		}

		$pod_id = $pod->get_id();

		$this->confirm_lookup_matches_db(
			$context,
			wp_list_pluck( $fields, 'id' ),
			$this->get_config_ids_from_db( 'field', $pod_id, $meta_checks ),
			$this->get_config_names_from_db( 'field', $pod_id )
		);
	}

	/**
	 * Confirm the Pod configuration matches the database before any repairs are made.
	 *
	 * @since 3.4.0
	 *
	 * @param Pod $pod The Pod object.
	 *
	 * @throws Conflict_Exception If another plugin is conflicting with the queries this tool relies on.
	 */
	protected function confirm_pod_matches_db( Pod $pod ) {
		if ( ! $this->can_verify_against_db( $pod ) ) {
			return;
		}

		// The Pod type and storage decide what gets rewritten on the Pod itself.
		$this->confirm_pod_args_match_db( $pod, [
			'type',
			'storage',
		] );

		$this->confirm_groups_match_db(
			$pod,
			$pod->get_groups( [
				'fallback_mode' => false,
			] ),
			__( 'Load all groups for the Pod', 'pods' )
		);

		$this->confirm_fields_match_db(
			$pod,
			$pod->get_fields( [
				'fallback_mode' => false,
			] ),
			__( 'Load all fields for the Pod', 'pods' )
		);
	}

	/**
	 * Confirm the arguments loaded for a Pod match the ones stored in the database.
	 *
	 * @since 3.4.0
	 *
	 * @param Pod      $pod   The Pod object.
	 * @param string[] $args  The argument names to verify.
	 *
	 * @throws Conflict_Exception If another plugin is conflicting with the meta lookups.
	 */
	protected function confirm_pod_args_match_db( Pod $pod, array $args ) {
		foreach ( $args as $arg ) {
			// Get the argument as it was loaded, without any of the fallbacks that Pods applies.
			$loaded_value = $pod->get_arg( $arg, null, false, true );

			// Values that are stored serialized cannot be compared against the raw meta value.
			if ( is_array( $loaded_value ) || is_object( $loaded_value ) ) {
				continue;
			}

			$db_values = $this->get_config_meta_from_db( $pod->get_id(), $arg );
			$db_value  = [] === $db_values ? null : reset( $db_values );

			if ( (string) $loaded_value === (string) $db_value ) {
				continue;
			}

			$this->stop_for_conflict(
				__( 'Load the Pod configuration', 'pods' ),
				sprintf(
					// translators: 1: The argument name, 2: The loaded value, 3: The value in the database.
					__( 'Pods loaded the "%1$s" setting as "%2$s" but the database has "%3$s", repairing the Pod would save the wrong setting', 'pods' ),
					$arg,
					null === $loaded_value ? __( 'N/A', 'pods' ) : (string) $loaded_value,
					null === $db_value ? __( 'N/A', 'pods' ) : (string) $db_value
				)
			);
		}
	}

	/**
	 * Confirm the arguments about to be removed from a field are really stored in the database.
	 *
	 * @since 3.4.0
	 *
	 * @param Field    $field   The Field object.
	 * @param string[] $args    The argument names about to be removed.
	 * @param string   $context The repair being verified.
	 *
	 * @throws Conflict_Exception If another plugin is conflicting with the meta lookups.
	 */
	protected function confirm_field_args_match_db( Field $field, array $args, $context ) {
		$field_id = (int) $field->get_id();

		if ( $field_id <= 0 ) {
			return;
		}

		foreach ( $args as $arg ) {
			if ( [] !== $this->get_config_meta_from_db( $field_id, $arg ) ) {
				continue;
			}

			$this->stop_for_conflict(
				$context,
				sprintf(
					// translators: 1: The argument name, 2: The field name, 3: The field ID.
					__( 'Pods loaded the "%1$s" setting on a field but a direct database query did not find it, removing it would delete a setting that another plugin provides (%2$s - #%3$d)', 'pods' ),
					$arg,
					$field->get_name(),
					$field_id
				)
			);
		}
	}

	/**
	 * Confirm a configuration loaded by Pods is the one the database says it should be.
	 *
	 * @since 3.4.0
	 *
	 * @param string       $context       The lookup being verified.
	 * @param null|Whatsit $object        The configuration that Pods loaded.
	 * @param int          $expected_id   The configuration ID found in the database.
	 * @param string       $expected_name The configuration name found in the database.
	 *
	 * @throws Conflict_Exception If another plugin is conflicting with the lookup.
	 */
	protected function confirm_config_matches_db( $context, $object, $expected_id, $expected_name ) {
		$loaded_id   = $object instanceof Whatsit ? (int) $object->get_id() : 0;
		$loaded_name = $object instanceof Whatsit ? (string) $object->get_name() : '';

		if ( (int) $expected_id === $loaded_id && (string) $expected_name === $loaded_name ) {
			return;
		}

		$this->stop_for_conflict(
			$context,
			sprintf(
				// translators: 1: The loaded name, 2: The loaded ID, 3: The name in the database, 4: The ID in the database.
				__( 'Pods loaded "%1$s" (#%2$d) but the database has "%3$s" (#%4$d), repairing it would rename the wrong configuration', 'pods' ),
				'' === $loaded_name ? __( 'N/A', 'pods' ) : $loaded_name,
				$loaded_id,
				$expected_name,
				(int) $expected_id
			)
		);
	}

	/**
	 * Confirm a configuration name is really available according to the database.
	 *
	 * @since 3.4.0
	 *
	 * @param string $context     The lookup being verified.
	 * @param string $object_type The Pods object type (pod, group, or field).
	 * @param int    $parent_id   The parent post ID.
	 * @param string $name        The name that Pods reported as available.
	 *
	 * @throws Conflict_Exception If another plugin is conflicting with the lookup.
	 */
	protected function confirm_config_name_available_in_db( $context, $object_type, $parent_id, $name ) {
		global $wpdb;

		$found_ids = $wpdb->get_col(
			$wpdb->prepare(
				"
					SELECT `primary`.`ID`
					FROM `{$wpdb->posts}` AS `primary`
					WHERE
						`primary`.`post_type` = %s
						AND `primary`.`post_parent` = %d
						AND `primary`.`post_name` = %s
						AND `primary`.`post_status` IN ( 'publish', 'draft' )
				",
				[
					'_pods_' . $object_type,
					(int) $parent_id,
					$name,
				]
			)
		);

		if ( empty( $found_ids ) ) {
			return;
		}

		$this->stop_for_conflict(
			$context,
			sprintf(
				// translators: 1: The configuration name, 2: The list of configuration IDs.
				__( 'Pods reported that the name "%1$s" was available but a direct database query found it in use, using it would create a duplicate (IDs: %2$s)', 'pods' ),
				$name,
				implode( ', ', array_map( 'absint', $found_ids ) )
			)
		);
	}

	/**
	 * Confirm the group that fields are about to be reassigned to exists in the database.
	 *
	 * @since 3.4.0
	 *
	 * @param Pod    $pod      The Pod object.
	 * @param int    $group_id The group ID that fields will be reassigned to.
	 * @param string $context  The repair being verified.
	 *
	 * @throws Conflict_Exception If the group is not in the database.
	 */
	protected function confirm_group_exists_in_db( Pod $pod, $group_id, $context ) {
		if ( ! $this->can_verify_against_db( $pod ) ) {
			return;
		}

		if ( in_array( (int) $group_id, $this->get_config_ids_from_db( 'group', $pod->get_id() ), true ) ) {
			return;
		}

		$this->stop_for_conflict(
			$context,
			sprintf(
				// translators: %d: The group ID.
				__( 'The group (#%d) that fields would be reassigned to was not found in the database, reassigning them would orphan every one of them', 'pods' ),
				(int) $group_id
			)
		);
	}

}
