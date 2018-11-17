<?php

namespace Pods_Unit_tests\Bugs;

/**
 * @package Pods_Unit_Tests
 * @group   pods_acceptance_tests
 * @group   pods-issue-4097
 */
class Bug_4097 extends \Pods_Unit_Tests\Pods_UnitTestCase {

	protected $pod_name = 't4097';

	protected $pod_id;

	/**
	 * @param string $storage
	 * @param string $type
	 *
	 * @return string
	 */
	public function setup_pod( $storage = 'meta', $type = 'post_type' ) {

		$pod_name     = $this->pod_name . '_' . substr( $storage, 2 ) . '_' . substr( $type, 2 );
		$this->pod_id = pods_api()->save_pod(
			array(
				'storage' => $storage,
				'type'    => $type,
				'name'    => $pod_name,
			)
		);

		$params = array(
			'pod'               => $pod_name,
			'pod_id'            => $this->pod_id,
			'name'              => 'color',
			'type'              => 'pick',
			'pick_object'       => 'custom-simple',
			'pick_custom'       => "yellow|Kollane\ngreen|Roheline\nbrown|Pruun\nred|Punane\n",
			'pick_format_type'  => 'multi',
			'pick_format_multi' => 'checkbox',
		);
		pods_api()->save_field( $params );

		return $pod_name;
	}

	public function tearDown() {

		if ( isset( $this->pod_id ) ) {
			pods_api()->delete_pod( array( 'id' => $this->pod_id ) );
			$this->pod_id = null;
		}
	}

	/**
	 * @param $type
	 * @param $storage
	 */
	public function run_test( $type, $storage ) {

		$pod_name = $this->setup_pod( $type, $storage );
		$pod      = pods( $pod_name );
		$id       = $pod->add(
			array(
				'color' => array(
					0 => 'green',
					1 => 'brown',
					2 => 'red',
				),
			)
		);
		$pod->fetch( $id );
		$value = $pod->display( 'color' );
		$this->assertEquals( 'Roheline, Pruun, and Punane', $value );

		$id = $pod->add(
			array(
				'color' => array(
					2 => 'green',
					3 => 'brown',
					4 => 'red',
				),
			)
		);
		$pod->fetch( $id );
		$value = $pod->display( 'color' );
		$this->assertEquals( 'Roheline, Pruun, and Punane', $value );

		$id = $pod->add( array( 'color' => array( 'yellow', 'green', 'brown', 'red' ) ) );
		$pod->fetch( $id );
		$value = $pod->display( 'color' );

		$this->assertEquals( 'Kollane, Roheline, Pruun, and Punane', $value );
	}

	public function test_cpt() {

		$this->run_test( 'meta', 'post_type' );
	}

	public function test_cpt_table() {

		$this->run_test( 'table', 'post_type' );
	}

	public function test_act() {

		$this->run_test( 'table', 'pod' );
	}

}
