<?php

namespace Pods\Tools;

use PodsForm;
use Pods\Whatsit\Field;
use Pods\Whatsit\Pod;

/**
 * Reset tool functionality.
 *
 * @since 2.9.10
 */
class Reset extends Base {

	/**
	 * Delete all content for a Pod.
	 *
	 * @since 2.9.10
	 *
	 * @param Pod    $pod  The Pod object.
	 * @param string $mode The reset mode (preview or full).
	 *
	 * @return array The results with information about the reset done.
	 */
	public function delete_all_content_for_pod( Pod $pod, $mode ) {
		$this->setup();

		$this->errors = [];

		$results = [];

		if ( 'preview' !== $mode ) {
			$this->api->reset_pod( [], $pod );
		}

		$results[ __( 'Delete all content for pod' ) ] = __( 'Pod content has been deleted' );

		$tool_heading = sprintf(
			// translators: %s: The Pod label.
			__( 'Reset results for %s', 'pods' ),
			$pod->get_label() . ' (' . $pod->get_name() . ')'
		);

		$results['message_html'] = $this->get_message_html( $tool_heading, $results, $mode );

		return $results;
	}

	/**
	 * Delete all relationship data for a Pod.
	 *
	 * @since 2.9.10
	 *
	 * @param Pod               $pod         The Pod object.
	 * @param null|string|array $field_names The fields to use (comma-separated or an array), otherwise delete relationships for all fields on Pod.
	 * @param string            $mode        The reset mode (preview or full).
	 *
	 * @return array The results with information about the reset done.
	 */
	public function delete_all_relationship_data_for_pod( Pod $pod, $field_names, $mode ) {
		$this->setup();

		$this->errors = [];

		if ( $field_names ) {
			$fields = [];

			if ( is_string( $field_names ) ) {
				$field_names = explode( ',', $field_names );
				$field_names = pods_trim( $field_names );
				$field_names = array_filter( $field_names );

				foreach ( $field_names as $field_name ) {
					$field = $pod->get_field( $field_name );

					if ( $field ) {
						$fields[] = $field;
					}
				}
			}
		} else {
			$fields = $pod->get_fields( [
				'type' => PodsForm::tableless_field_types(),
			] );
		}

		$results = [];

		global $wpdb;

		foreach ( $fields as $field_to_delete_from_podsrel ) {
			if ( 'preview' !== $mode ) {
				$total_deleted_from_podsrel = (int) $wpdb->query(
					$wpdb->prepare(
						"
							DELETE *
							FROM `{$wpdb->prefix}podsrel`
							WHERE `pod_id` = %d AND `field_id` = %d
						",
						[
							$pod->get_id(),
							$field_to_delete_from_podsrel->get_id(),
						]
					)
				);

				$total_related_deleted_from_podsrel = (int) $wpdb->query(
					$wpdb->prepare(
						"
							DELETE *
							FROM `{$wpdb->prefix}podsrel`
							WHERE `related_pod_id` = %d AND `related_field_id` = %d
						",
						[
							$pod->get_id(),
							$field_to_delete_from_podsrel->get_id(),
						]
					)
				);
			} else {
				$total_deleted_from_podsrel = (int) $wpdb->get_var(
					$wpdb->prepare(
						"
							SELECT COUNT(*)
							FROM `{$wpdb->prefix}podsrel`
							WHERE `pod_id` = %d AND `field_id` = %d
						",
						[
							$pod->get_id(),
							$field_to_delete_from_podsrel->get_id(),
						]
					)
				);

				$total_related_deleted_from_podsrel = (int) $wpdb->get_var(
					$wpdb->prepare(
						"
							SELECT COUNT(*)
							FROM `{$wpdb->prefix}podsrel`
							WHERE `related_pod_id` = %d AND `related_field_id` = %d
						",
						[
							$pod->get_id(),
							$field_to_delete_from_podsrel->get_id(),
						]
					)
				);
			}

			$heading = sprintf(
				// translators: %1$s: The field label; %2$s: The field name.
				__( 'Relationship data removed from podsrel table for field: %1$s (%2$s)', 'pods' ),
				$field_to_delete_from_podsrel->get_label(),
				$field_to_delete_from_podsrel->get_name()
			);

			$results[ $heading ] = sprintf(
				'%1$s %2$s',
				number_format_i18n( $total_deleted_from_podsrel ),
				_n( 'row', 'rows', $total_deleted_from_podsrel, 'pods' )
			);

			$heading = sprintf(
			// translators: %1$s: The field label; %2$s: The field name.
				__( 'Bidirectional Relationship data removed from podsrel table for field: %1$s (%2$s)', 'pods' ),
				$field_to_delete_from_podsrel->get_label(),
				$field_to_delete_from_podsrel->get_name()
			);

			$results[ $heading ] = sprintf(
				'%1$s %2$s',
				number_format_i18n( $total_related_deleted_from_podsrel ),
				_n( 'row', 'rows', $total_related_deleted_from_podsrel, 'pods' )
			);
		}

		$tool_heading = sprintf(
			// translators: %s: The Pod label.
			__( 'Reset results for %s', 'pods' ),
			$pod->get_label() . ' (' . $pod->get_name() . ')'
		);

		$results['message_html'] = $this->get_message_html( $tool_heading, $results, $mode );

		return $results;
	}

	/**
	 * Delete all Groups and Fields for a Pod.
	 *
	 * @since 2.9.10
	 *
	 * @param Pod    $pod  The Pod object.
	 * @param string $mode The reset mode (preview or full).
	 *
	 * @return array The results with information about the reset done.
	 */
	public function delete_all_groups_and_fields_for_pod( Pod $pod, $mode ) {
		$this->setup();

		$this->errors = [];

		$results = [];

		if ( 'preview' !== $mode ) {
			$results[ __( 'Delete all fields for pod', 'pods' ) ] = $this->delete_all_fields_for_pod( $pod, $mode );
			$results[ __( 'Delete all groups for pod', 'pods' ) ] = $this->delete_all_groups_for_pod( $pod, $mode );
		}

		$tool_heading = sprintf(
			// translators: %s: The Pod label.
			__( 'Reset results for %s', 'pods' ),
			$pod->get_label() . ' (' . $pod->get_name() . ')'
		);

		$results['message_html'] = $this->get_message_html( $tool_heading, $results, $mode );

		return $results;
	}

	/**
	 * Delete all Groups and Fields for a Pod.
	 *
	 * @since 2.9.10
	 *
	 * @param Pod    $pod  The Pod object.
	 * @param string $mode The reset mode (preview or full).
	 *
	 * @return string The text result.
	 */
	protected function delete_all_fields_for_pod( Pod $pod, $mode ) {
		$this->setup();

		$fields = $pod->get_fields();

		$total_deleted = count( $fields );

		foreach ( $fields as $field ) {
			if ( 'preview' !== $mode ) {
				$this->api->delete_field( $field );
			}
		}

		// translators: %1$s: The total number of fields deleted; %2$s: The singular or plural text for fields.
		return sprintf(
			_x( 'Deleted %1$s %2$s', 'The text for how many fields were deleted', 'pods' ),
			number_format_i18n( $total_deleted ),
			_n( 'field', 'fields', $total_deleted, 'pods' )
		);
	}

	/**
	 * Delete all Groups and Fields for a Pod.
	 *
	 * @since 2.9.10
	 *
	 * @param Pod    $pod  The Pod object.
	 * @param string $mode The reset mode (preview or full).
	 *
	 * @return string The text result.
	 */
	protected function delete_all_groups_for_pod( Pod $pod, $mode ) {
		$this->setup();

		$groups = $pod->get_groups();

		$total_deleted = count( $groups );

		foreach ( $groups as $group ) {
			if ( 'preview' !== $mode ) {
				$this->api->delete_group( $group );
			}
		}

		// translators: %1$s: The total number of groups deleted; %2$s: The singular or plural text for groups.
		return sprintf(
			_x( 'Deleted %1$s %2$s', 'The text for how many groups were deleted', 'pods' ),
			number_format_i18n( $total_deleted ),
			_n( 'group', 'groups', $total_deleted, 'pods' )
		);
	}

}
