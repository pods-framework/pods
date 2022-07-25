<?php

namespace Pods_Unit_Tests\Pods\Bug;

use Pods_Unit_Tests\Pods_UnitTestCase;

/**
 * @package Pods_Unit_Tests
 * @group   pods_acceptance_tests
 * @group   pods-issue-4097
 */
class Bug_4097Test extends Pods_UnitTestCase {

	protected $pod_name = 't4097';

	protected $pod_id;

	/**
	 * @param string $storage
	 * @param string $type
	 *
	 * @return string
	 */
	public function setup_pod( $storage = 'meta', $type = 'post_type' ) {
		$pod_name = $this->pod_name . '_' . substr( $storage, 2 ) . '_' . substr( $type, 2 );

		$api = pods_api();

		$this->pod_id = $api->save_pod( array(
			'storage' => $storage,
			'type'    => $type,
			'name'    => $pod_name,
		) );

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

		$api->save_field( $params );

		return $pod_name;
	}

	public function tearDown(): void {
		$this->pod_id = null;
	}

	/**
	 * @param $type
	 * @param $storage
	 *
	 * @dataProvider setup_providers
	 */
	public function test_setups( $storage, $type ) {
		$pod_name = $this->setup_pod( $storage, $type );

		codecept_debug( 'Test setup: ' . $type . ' | ' . $storage );

		$pod = pods( $pod_name );

		$this->assertNotFalse( $pod );

		$id = $pod->add( array(
			'color' => array(
				0 => 'green',
				1 => 'brown',
				2 => 'red',
			),
		) );

		$pod->fetch( $id );

		$value = $pod->display( 'color' );

		$this->assertEquals( 'Roheline, Pruun, and Punane', $value );

		$id = $pod->add( array(
			'color' => array(
				2 => 'green',
				3 => 'brown',
				4 => 'red',
			),
		) );

		$pod->fetch( $id );

		$value = $pod->display( 'color' );

		$this->assertEquals( 'Roheline, Pruun, and Punane', $value );

		$id = $pod->add( array(
			'color' => array(
				'yellow',
				'green',
				'brown',
				'red'
			)
		) );

		$pod->fetch( $id );

		$value = $pod->display( 'color' );

		$this->assertEquals( 'Kollane, Roheline, Pruun, and Punane', $value );
	}

	public function setup_providers() {
		return array(
			array( 'meta', 'post_type' ),
			array( 'table', 'post_type' ),
			array( 'table', 'pod' ),
		);
	}

}
