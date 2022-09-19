<?php

namespace Pods\Tools;

use Exception;
use PodsAPI;
use Pods\Whatsit\Field;
use Pods\Whatsit\Group;
use Pods\Whatsit\Pod;

/**
 * Recover tool functionality.
 *
 * @since 2.9.4
 */
class Recover {

	/**
	 * @var PodsAPI
	 */
	private $api;

	/**
	 * Recover Groups and Fields for a Pod.
	 *
	 * @since 2.9.4
	 *
	 * @param Pod    $pod  The Pod object.
	 * @param string $mode The recover mode (upgrade or full).
	 *
	 * @return array The results with information about the recovery done.
	 */
	public function recover_groups_and_fields_for_pod( Pod $pod, $mode ) {
		$this->api = pods_api();

		$is_upgrade_mode = 'upgrade' === $mode;
		$is_migrated     = 1 === (int) $pod->get_arg( '_migrated_28' );

		// Maybe set up a new group if no groups are found for the Pod.
		$group_id = $this->maybe_setup_group_if_no_groups( $pod );

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
			$results['maybe_setup_group_if_no_groups'] = __( 'First group created.', 'pods' );
		}

		if ( ! $is_upgrade_mode || $is_migrated ) {
			// Maybe resolve group conflicts.
			$results['maybe_resolve_group_conflicts'] = $this->maybe_resolve_group_conflicts( $pod );

			// Maybe resolve field conflicts.
			$results['maybe_resolve_field_conflicts'] = $this->maybe_resolve_field_conflicts( $pod );
		}

		// If we have a group to work with, use that.
		if ( null !== $group_id ) {
			if ( ! $is_upgrade_mode || $is_migrated ) {
				// Maybe reassign fields with invalid groups.
				$results['maybe_reassign_fields_with_invalid_groups'] = $this->maybe_reassign_fields_with_invalid_groups( $pod, $group_id );
			}

			// Maybe reassign orphan fields to the first group.
			$results['maybe_reassign_orphan_fields'] = $this->maybe_reassign_orphan_fields( $pod, $group_id );
		}

		// Mark the pod as migrated if upgrading.
		if ( $is_upgrade_mode ) {
			$pod->set_arg( '_migrated_28', 1 );

			try {
				$this->api->save_pod( $pod );
			} catch ( Exception $e ) {
				// Do nothing.
			}
		}

		$this->api->cache_flush_pods( $pod );

		$pod->flush();

		$results['message_html'] = $this->get_message_html( $pod, $results );

		return $results;
	}

	/**
	 * Get the message HTML from the recovery results.
	 *
	 * @since 2.9.4
	 *
	 * @param Pod   $pod  The Pod object.
	 * @param array $results The recovery results.
	 *
	 * @return string The message HTML.
	 */
	protected function get_message_html( Pod $pod, array $results ) {
		$messages = [
			sprintf(
				'<h3>%s</h3>',
				// translators: The Pod label.
				sprintf(
					esc_html__( 'Recovery results for %s', 'pods' ),
					$pod->get_label() . ' (' . $pod->get_name() . ')'
				)
			),
		];

		if ( ! empty( $results['maybe_setup_group_if_no_groups'] ) ) {
			$recovery_result = $results['maybe_setup_group_if_no_groups'];

			$messages[] = sprintf(
				'<h4>%1$s</h4><ul><li>%2$s</li></ul>',
				esc_html__( 'Setup group if there were no groups', 'pods' ),
				esc_html( $recovery_result )
			);
		}

		if ( ! empty( $results['maybe_resolve_group_conflicts'] ) ) {
			$recovery_result = $results['maybe_resolve_group_conflicts'];
			$recovery_result = array_map( 'esc_html', $recovery_result );

			$messages[] = sprintf(
				'<h4>%1$s</h4><ul><li>%2$s</li></ul>',
				esc_html__( 'Resolved group conflicts', 'pods' ),
				implode( '</li><li>', $recovery_result )
			);
		}

		if ( ! empty( $results['maybe_resolve_field_conflicts'] ) ) {
			$recovery_result = $results['maybe_resolve_field_conflicts'];
			$recovery_result = array_map( 'esc_html', $recovery_result );

			$messages[] = sprintf(
				'<h4>%1$s</h4><ul><li>%2$s</li></ul>',
				esc_html__( 'Resolved field conflicts', 'pods' ),
				implode( '</li><li>', $recovery_result )
			);
		}

		if ( ! empty( $results['maybe_reassign_fields_with_invalid_groups'] ) ) {
			$recovery_result = $results['maybe_reassign_fields_with_invalid_groups'];
			$recovery_result = array_map( 'esc_html', $recovery_result );

			$messages[] = sprintf(
				'<h4>%1$s</h4><ul><li>%2$s</li></ul>',
				esc_html__( 'Reassigned fields with invalid groups', 'pods' ),
				implode( '</li><li>', $recovery_result )
			);
		}

		if ( ! empty( $results['maybe_reassign_orphan_fields'] ) ) {
			$recovery_result = $results['maybe_reassign_orphan_fields'];
			$recovery_result = array_map( 'esc_html', $recovery_result );

			$messages[] = sprintf(
				'<h4>%1$s</h4><ul><li>%2$s</li></ul>',
				esc_html__( 'Reassigned orphan fields', 'pods' ),
				implode( '</li><li>', $recovery_result )
			);
		}

		if ( 1 === count( $messages ) ) {
			$messages[] = esc_html__( 'No recovery actions were needed.', 'pods' );
		}

		return wpautop( implode( "\n\n", $messages ) );
	}

	/**
	 * Maybe setup group if there are no groups.
	 *
	 * @since 2.9.4
	 *
	 * @param Pod $pod The Pod object.
	 *
	 * @return int|null The group ID if created, otherwise null if recovery not needed.
	 */
	protected function maybe_setup_group_if_no_groups( Pod $pod ) {
		$groups = $pod->get_groups( [
			'fallback_mode' => false,
		] );

		if ( ! empty( $groups ) ) {
			return null;
		}

		$label = __( 'Details', 'pods' );

		if ( in_array( $pod->get_type(), [ 'post_type', 'taxonomy', 'user', 'comment', 'media' ], true ) ) {
			$label = __( 'More Fields', 'pods' );
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
		$label = apply_filters( 'pods_meta_default_box_title', $label, $pod, $pod->get_fields(), $pod->get_type(), $pod->get_name() );

		$name  = sanitize_key( pods_js_name( sanitize_title( $label ) ) );

		try {
			// Setup first group.
			$group_id = $this->api->save_group( [
				'pod'   => $pod,
				'name'  => $name,
				'label' => $label,
			] );

			if ( $group_id && is_numeric( $group_id ) ) {
				return $group_id;
			}
		} catch ( Exception $exception ) {
			// Nothing to do for now.
		}

		return null;
	}

	/**
	 * Maybe resolve group conflicts.
	 *
	 * @since 2.9.4
	 *
	 * @param Pod $pod The Pod object.
	 *
	 * @return string[] The label, name, and ID for each group resolved.
	 */
	protected function maybe_resolve_group_conflicts( Pod $pod ) {
		// Find any group on the pod that has the same name as another group.
		global $wpdb;

		$sql = "
			SELECT
				`primary`.`ID`,
				`primary`.`post_name`
			FROM `$wpdb->posts}` AS `primary`
			LEFT JOIN `$wpdb->posts}` AS `duplicate`
				ON `duplicate`.`post_name` = `primary`.`post_name`
				       AND `duplicate`.`ID` != `primary`.`ID`
			WHERE
				`primary`.`post_type` = %s
				AND `primary`.`post_parent` = %d
				AND `duplicate`.`post_type` = %s
			ORDER BY `primary`.`ID`
		";

		$duplicate_groups = $wpdb->get_results(
			$wpdb->prepare(
				$sql,
				[
					'_pods_group',
					$pod->get_id(),
					'_pods_group',
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

				$groups_to_resolve[ $duplicate_group->post_name ][] = $group;
			} catch ( Exception $exception ) {
				// Nothing to do for now.
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
					$this->api->save_group( [
						'id'       => $group->get_id(),
						'pod_data' => $pod,
						'group'    => $group,
						'new_name' => $group_name . '_' . $group->get_id(),
					], false );

					$resolved_groups[] = sprintf(
						'%1$s (%2$s: %3$s | %4$s: %5$d | %6$s: %7$s)',
						$group->get_label(),
						__( 'Old Name', 'pods' ),
						$group_name,
						__( 'New Name', 'pods' ),
						$group_name . '_' . $group->get_id(),
						__( 'ID', 'pods' ),
						$group->get_id()
					);
				} catch ( Exception $exception ) {
					// Nothing to do for now.
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
	 * @param Pod $pod The Pod object.
	 *
	 * @return string[] The label, name, and ID for each field resolved.
	 */
	protected function maybe_resolve_field_conflicts( Pod $pod ) {
		// Find any field on the pod that has the same name as another field.
		global $wpdb;

		$sql = "
			SELECT
				`primary`.`ID`,
				`primary`.`post_name`
			FROM `$wpdb->posts}` AS `primary`
			LEFT JOIN `$wpdb->posts}` AS `duplicate`
				ON `duplicate`.`post_name` = `primary`.`post_name`
				       AND `duplicate`.`ID` != `primary`.`ID`
			WHERE
				`primary`.`post_type` = %s
				AND `primary`.`post_parent` = %d
				AND `duplicate`.`post_type` = %s
			ORDER BY `primary`.`ID`
		";

		$duplicate_fields = $wpdb->get_results(
			$wpdb->prepare(
				$sql,
				[
					'_pods_field',
					$pod->get_id(),
					'_pods_field',
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

				$fields_to_resolve[ $duplicate_field->post_name ][] = $field;
			} catch ( Exception $exception ) {
				// Nothing to do for now.
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
					$this->api->save_field( [
						'id'       => $field->get_id(),
						'pod_data' => $pod,
						'field'    => $field,
						'new_name' => $field_name . '_' . $field->get_id(),
					], false );

					$resolved_fields[] = sprintf(
						'%1$s (%2$s: %3$s | %4$s: %5$d | %6$s: %7$s)',
						$field->get_label(),
						__( 'Old Name', 'pods' ),
						$field_name,
						__( 'New Name', 'pods' ),
						$field_name . '_' . $field->get_id(),
						__( 'ID', 'pods' ),
						$field->get_id()
					);
				} catch ( Exception $exception ) {
					// Nothing to do for now.
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
	 * @param Pod $pod      The Pod object.
	 * @param int $group_id The group ID.
	 *
	 * @return string[] The label, name, and ID for each field reassigned.
	 */
	protected function maybe_reassign_fields_with_invalid_groups( Pod $pod, $group_id ) {
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

		return $this->reassign_fields_to_group( $fields, $group_id, $pod );
	}

	/**
	 * Maybe reassign orphan fields.
	 *
	 * @since 2.9.4
	 *
	 * @param Pod $pod      The Pod object.
	 * @param int $group_id The group ID.
	 *
	 * @return string[] The label, name, and ID for each field reassigned.
	 */
	protected function maybe_reassign_orphan_fields( Pod $pod, $group_id ) {
		$fields = $pod->get_fields( [
			'fallback_mode' => false,
			'group'         => null,
		] );

		return $this->reassign_fields_to_group( $fields, $group_id, $pod );
	}

	/**
	 * Reassign fields to a specific group.
	 *
	 * @since 2.9.4
	 *
	 * @param Pod $pod      The Pod object.
	 * @param int $group_id The group ID.
	 *
	 * @return string[] The label, name, and ID for each field reassigned.
	 */
	protected function reassign_fields_to_group( $fields, $group_id, $pod ) {
		$reassigned_fields = [];

		foreach ( $fields as $field ) {
			try {
				$this->api->save_field( [
					'id'           => $field->get_id(),
					'pod_data'     => $pod,
					'field'        => $field,
					'new_group_id' => $group_id,
				], false );

				$field->set_arg( 'group_id', $group_id );

				$reassigned_fields[] = sprintf(
					'%1$s (Name: %2$s | ID: %3$d)',
					$field->get_label(),
					$field->get_name(),
					$field->get_id()
				);
			} catch ( Exception $exception ) {
				// Nothing to do for now.
			}
		}

		return $reassigned_fields;
	}

}
