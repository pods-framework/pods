<?php

namespace Pods_Unit_Tests\Pods\Bug;

use Pods_Unit_Tests\Pods_UnitTestCase;

/**
 * @package Pods_Unit_Tests
 * @group   pods_acceptance_tests
 * @group   pods-issue-7374
 */
class Bug_7374Test extends Pods_UnitTestCase {

	protected $pod_name = 't7374';

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

		$api->save_field( array(
			'pod'              => $pod_name,
			'pod_id'           => $this->pod_id,
			'name'             => 'price',
			'type'             => 'pick',
			'pick_object'      => 'custom-simple',
			'pick_custom'      => "$3,500 - $9,999|Tier One\n$10,000 - $24,999|Tier Two\n$25,000 - $49,999|Tier Three\n",
			'pick_format_type'   => 'single',
			'pick_format_single' => 'dropdown',
		) );

		return $pod_name;
	}

	public function tearDown(): void {
		$this->pod_id = null;

		parent::tearDown();
	}

	/**
	 * @param string $type
	 * @param string $storage
	 *
	 * @dataProvider setup_providers
	 */
	public function test_single_dropdown_saves_comma_value( $storage, $type ) {
		$pod_name = $this->setup_pod( $storage, $type );
		$pod      = pods( $pod_name );

		$this->assertNotFalse( $pod );

		$id = $pod->add( array(
			'price' => '$3,500 - $9,999',
		) );

		$pod->fetch( $id );

		$this->assertSame( '$3,500 - $9,999', $pod->field( 'price' ) );
		$this->assertSame( 'Tier One', $pod->display( 'price' ) );
	}

	public function setup_providers() {
		return array(
			array( 'meta', 'post_type' ),
			array( 'table', 'post_type' ),
			array( 'table', 'pod' ),
		);
	}

}
