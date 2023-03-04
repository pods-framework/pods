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
			if ( ! isset( $fields_to_resolve[ $duplicate_field->post_name ] ) ) {
				$fields_to_resolve[ $duplicate_field->post_name ] = [];
			}

			try {
				$field = $this->api->load_field( [ 'id' => $duplicate_field->ID ] );

				if ( $field ) {
					$fields_to_resolve[ $duplicate_field->post_name ][] = $field;
				} else {
					throw new Exception( __( 'Failed to load duplicate field to resolve.', 'pods' ) );
				}
			} catch ( Throwable $exception ) {
				$this->errors[] = ucwords( str_replace( '_', ' ', __FUNCTION__ ) ) . ' > ' . $exception->getMessage() . ' (' . $duplicate_field->post_name . ' - #' . $duplicate_field->ID . ')';
			}
		}

		$resolved_fields = [];

		foreach ( $fields_to_resolve as $field_name => $fields ) {
			if ( 1 < count( $fields ) ) {
				// Remove the first field.
				array_shift( $fields );
			}

			foreach ( $fields as $field ) {
				/** @var Field $field */
				try {
					if ( 'preview' !== $mode ) {
						$this->api->save_field( [
							'id'       => $field->get_id(),
							'pod_data' => $pod,
							'field'    => $field,
							'new_name' => $field_name . '_' . $field->get_id(),
						], false );
					}

					$resolved_fields[] = sprintf(
						'%1$s (%2$s: %3$s | %4$s: %5$s | %6$s: %7$d)',
						$field->get_label(),
						__( 'Old Name', 'pods' ),
						$field_name,
						__( 'New Name', 'pods' ),
						$field_name . '_' . $field->get_id(),
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
		$groups = array_filter( $groups );

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

}
