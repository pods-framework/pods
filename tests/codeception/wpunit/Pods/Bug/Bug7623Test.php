<?php

namespace Pods_Unit_Tests\Pods\Bug;

use Exception;
use Pods_Unit_Tests\Pods_UnitTestCase;

/**
 * @package Pods_Unit_Tests
 * @group   pods_acceptance_tests
 * @group   pods-issue-7623
 */
class Bug_7623Test extends Pods_UnitTestCase {

	protected $pod_name = 't7623';

	protected $pod_id;

	/**
	 * @param string $storage
	 * @param string $type
	 *
	 * @return string
	 */
	public function setup_pod( $storage = 'table', $type = 'pod' ) {
		$pod_name = $this->pod_name . '_' . substr( $storage, 2 ) . '_' . substr( $type, 2 );

		$api = pods_api();

		$this->pod_id = $api->save_pod( array(
			'storage' => $storage,
			'type'    => $type,
			'name'    => $pod_name,
		) );

		$params = array(
			'pod'     => $pod_name,
			'pod_id'  => $this->pod_id,
			'name'    => 'social_security_number',
			'label'   => 'Social Security Number',
			'type'    => 'text',
			'unique'  => 1,
		);

		$api->save_field( $params );

		return $pod_name;
	}

	public function tearDown(): void {
		$this->pod_id = null;

		parent::tearDown();
	}

	/**
	 * @param string $storage
	 * @param string $type
	 *
	 * @dataProvider setup_providers
	 */
	public function test_unique_field_blocks_duplicate_value( $storage, $type ) {
		$pod_name = $this->setup_pod( $storage, $type );

		codecept_debug( 'Test setup: ' . $type . ' | ' . $storage );

		$pod = pods( $pod_name );

		$this->assertNotFalse( $pod );

		$id = $pod->add( array(
			'social_security_number' => '196101011259',
		) );

		$this->assertNotEmpty( $id );

		// A duplicate value must not be saved.
		$duplicate_id = null;

		try {
			$duplicate_id = $pod->add( array(
				'social_security_number' => '196101011259',
			) );
		} catch ( Exception $e ) {
			// Expected: unique validation throws an error.
			$duplicate_id = false;
		}

		$this->assertEmpty( $duplicate_id );

		// A unique value still saves fine.
		$id2 = $pod->add( array(
			'social_security_number' => '197505054321',
		) );

		$this->assertNotEmpty( $id2 );

		$pod->fetch( $id2 );

		$this->assertEquals( '197505054321', $pod->display( 'social_security_number' ) );
	}

	public function setup_providers() {
		return array(
			array( 'table', 'pod' ),
			array( 'table', 'post_type' ),
		);
	}

}
