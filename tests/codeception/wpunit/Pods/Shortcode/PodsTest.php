<?php

namespace Pods_Unit_Tests\Pods\Shortcode;

use Pods_Unit_Tests\Pods_UnitTestCase;
use Pods;

/**
 * Class PodsTest
 *
 * @group pods-shortcode
 * @group pods-shortcode-pods
 */
class PodsTest extends Pods_UnitTestCase {

	/**
	 * @var string
	 */
	protected $pod_name = 'test_pods';

	/**
	 * @var int
	 */
	protected $pod_id = 0;

	/**
	 * @var Pods
	 */
	protected $pod;

	/**
	 *
	 */
	public function setUp(): void {
		parent::setUp();

		$api = pods_api();

		$this->pod_id = $api->save_pod( array(
			'type' => 'pod',
			'name' => $this->pod_name,
		) );

		$params = array(
			'pod_id' => $this->pod_id,
			'name'   => 'number1',
			'type'   => 'number',
		);

		$api->save_field( $params );

		$this->pod = pods( $this->pod_name );
	}

	/**
	 *
	 */
	public function tearDown(): void {
		$this->pod_id = null;
		$this->pod    = null;

		parent::tearDown();
	}

	/**
	 *
	 */
	public function test_shortcode_pods() {
		$this->assertNotFalse( $this->pod );

		$pod_name = $this->pod_name;

		// add an item
		$this->pod->add( array(
			'name'    => 'Tatooine',
			'number1' => 5,
		) );

		// test shortcode
		$this->assertEquals( '5', do_shortcode( '[pods name="' . $pod_name . '" where="t.number1=5"]{@number1}[/pods]' ) );

		// add another item
		$this->pod->add( array(
			'name'    => 'Alderaan',
			'number1' => 7,
		) );

		// test shortcode
		$this->assertEquals( '5', do_shortcode( '[pods name="' . $pod_name . '" where="t.number1=5"]{@number1}[/pods]' ) );

		// add third item
		$this->pod->add( array(
			'name'    => 'Hoth',
			'number1' => 5,
		) );

		// test shortcode
		$this->assertEquals( '55', do_shortcode( '[pods name="' . $pod_name . '" where="t.number1=5"]{@number1}[/pods]' ) );

		// Test the pagination parameter
		/** @see http://php.net/manual/en/filter.filters.validate.php FILTER_VALIDATE_BOOLEAN */
		$this->assertContains( '<a', do_shortcode( '[pods name="' . $pod_name . '" pagination="1" limit="2"]~[/pods]' ) );
		$this->assertContains( '<a', do_shortcode( '[pods name="' . $pod_name . '" pagination="true" limit="2"]~[/pods]' ) );
		$this->assertContains( '<a', do_shortcode( '[pods name="' . $pod_name . '" pagination="on" limit="2"]~[/pods]' ) );
		$this->assertContains( '<a', do_shortcode( '[pods name="' . $pod_name . '" pagination="yes" limit="2"]~[/pods]' ) );
		$this->assertContains( '<a', do_shortcode( '[pods name="' . $pod_name . '" pagination="1" limit="2"]~[/pods]' ) );
		$this->assertContains( '<a', do_shortcode( '[pods name="' . $pod_name . '" pagination="true" limit="2"]~[/pods]' ) );

		$this->assertEquals( '~~', do_shortcode( '[pods name="' . $pod_name . '" pagination="0" limit="2"]~[/pods]' ) );
		$this->assertEquals( '~~', do_shortcode( '[pods name="' . $pod_name . '" pagination="false" limit="2"]~[/pods]' ) );
		$this->assertEquals( '~~', do_shortcode( '[pods name="' . $pod_name . '" pagination="off" limit="2"]~[/pods]' ) );
		$this->assertEquals( '~~', do_shortcode( '[pods name="' . $pod_name . '" pagination="no" limit="2"]~[/pods]' ) );
		$this->assertEquals( '~~', do_shortcode( '[pods name="' . $pod_name . '" pagination="0" limit="2"]~[/pods]' ) );
		$this->assertEquals( '~~', do_shortcode( '[pods name="' . $pod_name . '" pagination="false" limit="2"]~[/pods]' ) );
		$this->assertEquals( '~~', do_shortcode( '[pods name="' . $pod_name . '" pagination="-1" limit="2"]~[/pods]' ) );
		$this->assertEquals( '~~', do_shortcode( '[pods name="' . $pod_name . '" pagination="xyzzy" limit="2"]~[/pods]' ) );

		// Not enough records to trigger pagination even if on
		$this->assertNotContains( '<a', do_shortcode( '[pods name="' . $pod_name . '" pagination="1" limit="100"]~[/pods]' ) );

		/** @link https://github.com/pods-framework/pods/pull/2807 */
		$this->assertEquals( '57', do_shortcode( '[pods name="' . $pod_name . '" page="1" limit="2"]{@number1}[/pods]' ) );
		$this->assertEquals( '5', do_shortcode( '[pods name="' . $pod_name . '" page="2" limit="2"]{@number1}[/pods]' ) );
	}

	/**
	 * PR 2339
	 *
	 * @link  https://github.com/pods-framework/pods/pull/2339
	 * @since 2.8.0
	 */
	public function test_shortcode_pods_field_in_shortcode() {
		$this->assertNotFalse( $this->pod );

		$pod_name = $this->pod_name;

		// add an item
		$this->pod->add( array(
			'name'    => 'Dagobah',
			'number1' => 5,
		) );

		$this->pod->find( array( 'where' => 't.number1=5' ) );

		// test shortcode
		$this->assertEquals( '5', do_shortcode( '[pods name="' . $pod_name . '" where="t.number1=5" field="number1"]' ) );
	}

}
