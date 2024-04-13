<?php

namespace Pods\WP;

use Pods\Whatsit\Pod;
use PodsMeta;
use PodsForm;

/**
 * Meta specific functionality.
 *
 * @since 3.2.0
 */
class Meta {

	/**
	 * The list of registered meta.
	 *
	 * @since 3.2.0
	 */
	private $registered_meta = [];

	/**
	 * Add the class hooks.
	 *
	 * @since 3.2.0
	 */
	public function hook() {
		$this->register_meta();
	}

	/**
	 * Remove the class hooks.
	 *
	 * @since 3.2.0
	 */
	public function unhook() {
		$this->unregister_meta();
	}

	/**
	 * Determine whether to register meta fields.
	 *
	 * @since 3.2.0
	 *
	 * @return bool Whether to register meta fields.
	 */
	public function should_register_meta(): bool {
		$should_register_meta = filter_var( pods_get_setting( 'register_meta_integration', false ), FILTER_VALIDATE_BOOLEAN );

		/**
		 * Allow filtering whether to register meta fields.
		 *
		 * @since 3.2.0
		 *
		 * @param bool $should_register_meta Whether to register meta fields.
		 */
		return (bool) apply_filters( 'pods_wp_meta_should_register_meta', $should_register_meta );
	}

	/**
	 * Register the meta fields only if needed.
	 *
	 * @since 3.2.0
	 */
	public function register_meta() {
		if ( ! $this->should_register_meta() ) {
			return;
		}

		$can_use_dynamic_feature_display = pods_can_use_dynamic_feature( 'display' );

		$pods_to_register = [
			'post' => [],
		];

		foreach ( PodsMeta::$post_types as $pod ) {
			if ( ! $pod instanceof Pod || ! pods_can_use_dynamic_features( $pod ) ) {
				continue;
			}

			$pods_to_register['post'][] = $pod;
		}

		foreach ( $pods_to_register as $type => $pods ) {
			/** @var array<int,Pod> $pods */
			foreach ( $pods as $pod ) {
				$pod_name    = $pod->get_name();
				$pod_type    = $pod->get_type();
				$pod_storage = $pod->get_storage();
				$fields      = $pod->get_fields();

				// REST API pod config.
				$pod_rest_enabled = filter_var( $pod->get_arg( 'rest_enable', false ), FILTER_VALIDATE_BOOLEAN );

				$pod_rest_field_location = $pod->get_arg( 'rest_api_field_location', 'object', true );

				$post_type_has_custom_fields = (
					$pod_rest_enabled
					&& 'post_type' === $pod_type
					&& post_type_supports( $pod_name, 'custom-fields' )
				);

				$all_fields_have_rest = (
					$can_use_dynamic_feature_display
					&& $pod_rest_enabled
					&& $post_type_has_custom_fields
					&& filter_var( $pod->get_arg( 'read_all', false ), FILTER_VALIDATE_BOOLEAN )
				);

				// Revision pod config.
				$all_fields_have_revisions = (
					'post_type' === $pod_type
					&& 'meta' === $pod_storage
					&& filter_var( $pod->get_arg( 'read_all', false ), FILTER_VALIDATE_BOOLEAN )
				);

				foreach ( $fields as $field ) {
					$field_is_repeatable = $field->is_repeatable();

					// REST API field config.
					$field_has_rest = (
						'meta' === $pod_rest_field_location
						&& $post_type_has_custom_fields
						&& $can_use_dynamic_feature_display
						&& (
							$all_fields_have_rest
							|| (
								$pod_rest_enabled
								&& filter_var( $field->get_arg( 'rest_read', false ), FILTER_VALIDATE_BOOLEAN )
							)
						)
					);

					// Revision field config.
					$field_has_revisions = (
						$all_fields_have_revisions
						|| (
							'post_type' === $pod_type
							&& 'meta' === $pod_storage
							&& filter_var( $pod->get_arg( 'revisions_revision_field', false ), FILTER_VALIDATE_BOOLEAN )
						)
					);

					register_meta(
						$type,
						$field['name'],
						[
							'object_subtype'    => $pod_name,
							'type'              => 'string',
							'description'       => $field['label'],
							'default'           => $field->get_arg( 'default' ),
							'single'            => $field_is_repeatable,
							'show_in_rest'      => $field_has_rest,
							'revisions_enabled' => $field_has_revisions,
						]
					);

					if ( ! isset( $this->registered_meta[ $type ] ) ) {
						$this->registered_meta[ $type ] = [];
					}

					$this->registered_meta[] = [
						'object_type'    => $type,
						'meta_key'       => $field['name'],
						'object_subtype' => $pod_name,
					];
				}
			}
		}
	}

	/**
	 * Unregister the meta fields.
	 *
	 * @since 3.2.0
	 */
	public function unregister_meta() {
		foreach ( $this->registered_meta as $field ) {
			unregister_meta_key( $field['object_type'], $field['meta_key'], $field['object_subtype'] );
		}

		$this->registered_meta = [];
	}

}
