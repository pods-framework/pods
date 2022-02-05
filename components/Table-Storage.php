<?php
/**
 * Name: Table Storage
 *
 * Description: Enable a custom database table for your custom fields on Post Types, Media, Taxonomies, Users, and Comments.
 *
 * Version: 2.3
 *
 * Category: Advanced
 *
 * Tableless Mode: No
 *
 * @package    Pods\Components
 * @subpackage Advanced Content Types
 */

if ( class_exists( 'Pods_Table_Storage' ) ) {
	return;
}

/**
 * Class Pods_Table_Storage
 */
class Pods_Table_Storage extends PodsComponent {

	/**
	 * {@inheritdoc}
	 */
	public function init() {
		// Bypass if Pods is in types-only mode.
		if ( pods_is_types_only() ) {
			return;
		}

		// Bypass if Pods is in tableless mode.
		if ( pods_tableless() ) {
			return;
		}

		add_filter( 'pods_admin_setup_add_create_storage', '__return_true' );
		add_filter( 'pods_admin_setup_add_create_taxonomy_storage', '__return_true' );

		add_filter( 'pods_admin_setup_add_extend_storage', '__return_true' );
		add_filter( 'pods_admin_setup_add_extend_taxonomy_storage', '__return_true' );
	}

}
