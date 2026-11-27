<?php

namespace Pods_Unit_Tests\Bugs;

use Pods\Admin\Config\Pod as Config_Pod;
use PodsAdmin;

/**
 * Tests for the ACT "Fallback Edit in Dashboard" option default.
 *
 * The settings form default and the admin menu read default have to agree, otherwise a pod
 * that never stored the option shows an unchecked box but still lands in the fallback menu.
 *
 * @package Pods_Unit_Tests
 * @group   pods_acceptance_tests
 * @group   pods-issue-7656
 */
class Bug7656Test extends \Pods_Unit_Tests\Pods_UnitTestCase {

	/**
	 * Pod name used by these tests.
	 *
	 * @var string
	 */
	protected $pod_name = 'bug7656act';

	public function setUp(): void {
		parent::setUp();

		$administrator = self::factory()->user->create( [ 'role' => 'administrator' ] );

		wp_set_current_user( $administrator );
	}

	/**
	 * Save an advanced content type and return the pods PodsAdmin adds to the fallback menu.
	 *
	 * @param array $args Extra save_pod args.
	 *
	 * @return array List of pod names in the fallback content menu.
	 */
	protected function get_fallback_menu_pods( array $args = [] ) {
		$params = array_merge( [
			'name'         => $this->pod_name,
			'type'         => 'pod',
			'show_in_menu' => 0,
		], $args );

		pods_api()->save_pod( $params );

		pods_meta()->cache_pods();

		$submenu = [];

		$capture = function ( $secondary_content ) use ( &$submenu ) {
			$submenu = $secondary_content;

			return $secondary_content;
		};

		add_filter( 'pods_admin_menu_secondary_content', $capture );

		$admin = new PodsAdmin();

		$admin->admin_menu();

		remove_filter( 'pods_admin_menu_secondary_content', $capture );

		$names = [];

		foreach ( (array) $submenu as $pod ) {
			$names[] = $pod['name'];
		}

		return $names;
	}

	/**
	 * Get the admin-ui field config for the fallback option.
	 *
	 * @return array
	 */
	protected function get_fallback_field_config() {
		$pod = pods_api()->load_pod( [ 'name' => $this->pod_name ] );

		$config = pods_container( Config_Pod::class );

		$fields = $config->get_fields( $pod, $config->get_tabs( $pod ) );

		$this->assertArrayHasKey( 'use_submenu_fallback', $fields['admin-ui'] );

		return $fields['admin-ui']['use_submenu_fallback'];
	}

	/**
	 * The settings form shows the box checked, because the menu treats an unset option as on.
	 */
	public function test_config_default_matches_menu_default() {
		$this->get_fallback_menu_pods();

		$field = $this->get_fallback_field_config();

		$this->assertTrue( $field['default'] );
	}

	/**
	 * A pod that never stored the option uses the fallback menu, which is the older behavior.
	 */
	public function test_fallback_menu_used_when_option_not_stored() {
		$names = $this->get_fallback_menu_pods();

		$pod = pods_api()->load_pod( [ 'name' => $this->pod_name ] );

		$this->assertFalse( metadata_exists( 'post', $pod->get_id(), 'use_submenu_fallback' ) );
		$this->assertContains( $this->pod_name, $names );
	}

	/**
	 * Saving the option off keeps the pod out of the fallback menu.
	 */
	public function test_fallback_menu_skipped_when_option_saved_off() {
		$names = $this->get_fallback_menu_pods( [ 'use_submenu_fallback' => 0 ] );

		$this->assertNotContains( $this->pod_name, $names );
	}

	/**
	 * Saving the option on keeps the pod in the fallback menu.
	 */
	public function test_fallback_menu_used_when_option_saved_on() {
		$names = $this->get_fallback_menu_pods( [ 'use_submenu_fallback' => 1 ] );

		$this->assertContains( $this->pod_name, $names );
	}

	/**
	 * A pod with its own admin menu is never part of the fallback menu.
	 */
	public function test_fallback_menu_skipped_for_pod_with_own_menu() {
		$names = $this->get_fallback_menu_pods( [
			'show_in_menu'         => 1,
			'use_submenu_fallback' => 1,
		] );

		$this->assertNotContains( $this->pod_name, $names );
	}
}
