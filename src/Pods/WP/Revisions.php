<?php

namespace Pods\WP;

use Pods\Whatsit\Pod;
use PodsForm;

/**
 * Revisions specific functionality.
 *
 * @since 3.2.0
 */
class Revisions {

	/**
	 * Add the class hooks.
	 *
	 * @since 3.2.0
	 */
	public function hook() {
		add_filter( 'wp_post_revision_meta_keys', [ $this, 'wp_post_revision_meta_keys' ], 10, 2 );
	}

	/**
	 * Remove the class hooks.
	 *
	 * @since 3.2.0
	 */
	public function unhook() {
		remove_filter( 'wp_post_revision_meta_keys', [ $this, 'wp_post_revision_meta_keys' ] );
	}

	/**
	 * Filter the available post revision meta keys to include the ones registered by Pods.
	 *
	 * @since 3.2.0
	 *
	 * @param array  $revisioned_keys The list of revisioned meta keys.
	 * @param string $post_type       The post type.
	 *
	 * @return array The list of revisioned meta keys.
	 */
	public function wp_post_revision_meta_keys( $revisioned_keys, $post_type ): array {
		$meta = pods_container( Meta::class );

		// Determine if we need to revision keys manually or if meta is already being registered.
		if ( $meta->should_register_meta() ) {
			return $revisioned_keys;
		}

		$api = pods_api();

		$pod = $api->load_pod( [ 'name' => $post_type ] );

		if ( ! $pod instanceof Pod || 'post_type' !== $pod->get_type() || 'meta' !== $pod->get_storage() ) {
			return $revisioned_keys;
		}

		$revisionable_field_types = self::get_revisionable_field_types();

		// Get the fields that are enabled for revisioning.
		if ( 1 === (int) $pod->get_arg( 'revisions_revision_all_fields', 0 ) ) {
			$revisionable_fields = $pod->get_fields( [
				'type'  => $revisionable_field_types,
				'names' => true,
			] );
		} else {
			$revisionable_fields = $pod->get_fields( [
				'type'  => $revisionable_field_types,
				'args'  => [
					'revisions_revision_field' => 1,
				],
				'names' => true,
			] );
		}

		$revisioned_keys = array_merge( $revisioned_keys, array_values( $revisionable_fields ) );
		$revisioned_keys = array_unique( array_values( $revisioned_keys ) );

		return $revisioned_keys;
	}

	/**
	 * Get the list of revisionable field types.
	 *
	 * @since 3.2.0
	 *
	 * @return array The list of revisionable field types.
	 */
	public static function get_revisionable_field_types(): array {
		$field_types           = PodsForm::field_types_list();
		$tableless_field_types = PodsForm::tableless_field_types();
		$layout_field_types    = PodsForm::layout_field_types();

		$revisionable_field_types = [];

		foreach ( $field_types as $field_type ) {
			if (
				in_array( $field_type, $tableless_field_types, true )
				|| in_array( $field_type, $layout_field_types, true )
			) {
				continue;
			}

			$revisionable_field_types[] = $field_type;
		}

		return $revisionable_field_types;
	}

}
