<?php

namespace Pods_Unit_Tests\Bugs;

/**
 * Regression test for pods-framework/pods issue #7133 (Bug 1).
 *
 * Importing a pods.json package whose fields carry "old_name" values
 * should not cause save_field() to bail out with a fail_on_load lookup
 * error. The import pipeline must strip old_name before delegating to
 * save_field() since old_name only has meaning for in-place renames.
 *
 * @group pods_acceptance_tests
 * @group pods-issue-7133
 */
class Bug_7133Test extends \Pods_Unit_Tests\Pods_UnitTestCase {

	/**
	 * Verify import_pod_setup_objects() strips old_name from import data.
	 */
	public function test_import_strips_old_name() {
		$pod        = array(
			'name'        => 'rename-test',
			'label'       => 'Rename Test',
			'type'        => 'post_type',
			'storage'     => 'meta',
			'old_name'    => 'previous-rename-test',
		);
		$group      = array(
			'name'      => 'rename-group',
			'label'     => 'Rename Group',
			'old_name'  => 'previous-rename-group',
			'fields'    => array(
				array(
					'name'      => 'rename-field',
					'label'     => 'Rename Field',
					'type'      => 'text',
					'old_name'  => 'previous-rename-field',
				),
			),
		);
		$pod['groups'] = array( $group );

		// Build the call args to import_pod_setup_objects via reflection.
		$migrate = $this->get_migrate_packages();

		$existing = array();

		$reflection = new \ReflectionClass( $migrate );
		$method     = $reflection->getMethod( 'import_pod_setup_objects' );
		$method->setAccessible( true );

		$result = $method->invoke( $migrate, $group['fields'], $existing );

		$this->assertArrayNotHasKey( 'old_name', $result[0], 'old_name must be stripped from field data passed to save_field().' );
	}

	/**
	 * End-to-end: import a package with old_name values on fields. Should
	 * succeed without producing "Cannot save the X field" errors.
	 */
	public function test_import_with_old_name_does_not_block_fields() {
		$migrate = $this->get_migrate_packages();

		$pkg = json_encode( array(
			'@meta'  => array(
				'version' => defined( 'PODS_VERSION' ) ? PODS_VERSION : '0',
			),
			'type'    => 'package',
			'pods'    => array(
				array(
					'name'     => 'old-name-rename',
					'label'    => 'Old Name Rename',
					'type'     => 'post_type',
					'storage'  => 'meta',
					'groups'   => array(
						array(
							'name'    => 'main',
							'label'   => 'Main',
							'fields'  => array(
								array(
									'name'     => 'first',
									'label'    => 'First',
									'type'     => 'text',
									'old_name' => 'previous-first',
								),
								array(
									'name'     => 'second',
									'label'    => 'Second',
									'type'     => 'text',
									'old_name' => 'previous-second',
								),
							),
						),
					),
				),
			),
		) );

		// Suppress AJAX-style exceptions the legacy throw produces; check the return.
		$api                  = pods_api();
		$api->display_errors  = false;

		$result = $migrate->import( $pkg, true );

		$api->display_errors  = true;

		$this->assertIsArray( $result, 'import() should return an array of pods on success.' );
		$this->assertNotEmpty( $result, 'import() should not return empty for a well-formed package.' );

		// Load the pod by name to find its ID; verify both fields were saved.
		$pod        = $api->load_pod( array( 'name' => 'old-name-rename' ) );
		$this->assertNotInstanceOf( '\WP_Error', $pod, 'load_pod must return a pod, not a WP_Error.' );

		$pod_id     = $pod['id'];
		$field_ids  = $api->load_fields( array(
			'pod_id'  => $pod_id,
			'refresh' => true,
		) );

		$this->assertCount( 2, $field_ids, 'Both fields with old_name should be imported, not dropped.' );

		// Clean up so other tests can run.
		$api->delete_pod( 'old-name-rename' );
	}

	/**
	 * @return \Pods_Migrate_Packages
	 */
	private function get_migrate_packages() {
		$components = \PodsInit::$components;
		$components->load();
		$active = $components->activate_component( 'migrate-packages' );
		$this->assertTrue( $active );

		$components->load();

		/** @var \Pods_Migrate_Packages $migrate */
		$migrate = $components->components['migrate-packages']['object'];

		return $migrate;
	}
}
