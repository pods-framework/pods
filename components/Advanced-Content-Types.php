<?php
/**
 * Name: Advanced Content Types
 *
 * Description: A content type that exists outside of the WordPress post and postmeta table and uses custom tables instead. You most likely don't need these and we strongly recommend that you use Custom Post Types or Custom Taxonomies instead. FOR ADVANCED USERS ONLY.
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

if ( class_exists( 'Pods_Advanced_Content_Types' ) ) {
	return;
}

/**
 * Class Pods_Advanced_Content_Types
 */
class Pods_Advanced_Content_Types extends PodsComponent {

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

		add_filter( 'pods_admin_setup_add_create_pod_type', array( $this, 'add_pod_type' ) );
	}

	/**
	 * Enable Advanced Content Type option in setup-add.php
	 *
	 * @param array $data Pod Type options
	 *
	 * @return array
	 */
	public function add_pod_type( $data ) {
		$data['pod'] = __( 'Advanced Content Type (separate from WP, blank slate, in its own table)', 'pods' );

		return $data;
	}

}
