<?php
/**
 * @package Pods
 * @category Tests
 */
namespace Pods_Unit_Tests\Acceptance_Tests;

use Pods_Unit_Tests\Pods_UnitTestCase;

/**
 * @group pods_acceptance_tests
 */
class Test_Shortcodes extends Pods_UnitTestCase {

	/** @var int|null */
	private $pod_id = null;

	/** @var \Pods|false|null */
	private $pod = null;

	public function setUp() {

		//Create a pod
		$this->pod_id = pods_api()->save_pod( array( 'storage' => 'table', 'type' => 'pod', 'name' => 'planet' ) );
		$this->pod    = pods( 'planet' );

		//register the fields
		$params = array(
			'pod'    => 'planet',
			'pod_id' => $this->pod_id,
			'name'   => 'number_of_moons',
			'type'   => 'number'
		);
		pods_api()->save_field( $params );
	}

	public function tearDown() {

		pods_api()->delete_pod( array( 'id' => $this->pod_id ) );
	}

	/**
	 * @since 3.0
	 */
	public function test_shortcode_pods() {

		//add an item
		$this->pod->add( array( 'name' => 'Tatooine', 'number_of_moons' => 5 ) );

		//test shortcode
		$this->assertEquals( '5', do_shortcode( '[pods name ="planet" where="t.number_of_moons=5"]{@number_of_moons}[/pods]' ) );

		//add another item
		$this->pod->add( array( 'name' => 'Alderaan', 'number_of_moons' => 7 ) );

		//test shortcode
		$this->assertEquals( '5', do_shortcode( '[pods name ="planet" where="t.number_of_moons=5"]{@number_of_moons}[/pods]' ) );

		//add third item
		$this->pod->add( array( 'name' => 'Hoth', 'number_of_moons' => 5 ) );

		//test shortcode
		$this->assertEquals( '55', do_shortcode( '[pods name ="planet" where="t.number_of_moons=5"]{@number_of_moons}[/pods]' ) );

		// Test the pagination parameter
		/** @see http://php.net/manual/en/filter.filters.validate.php FILTER_VALIDATE_BOOLEAN */
		$this->assertContains( '<a', do_shortcode( '[pods name="planet" pagination="1" limit="2"]~[/pods]' ) );
		$this->assertContains( '<a', do_shortcode( '[pods name="planet" pagination="true" limit="2"]~[/pods]' ) );
		$this->assertContains( '<a', do_shortcode( '[pods name="planet" pagination="on" limit="2"]~[/pods]' ) );
		$this->assertContains( '<a', do_shortcode( '[pods name="planet" pagination="yes" limit="2"]~[/pods]' ) );
		$this->assertContains( '<a', do_shortcode( '[pods name="planet" pagination=1 limit="2"]~[/pods]' ) );
		$this->assertContains( '<a', do_shortcode( '[pods name="planet" pagination=true limit="2"]~[/pods]' ) );

		$this->assertEquals( '~~', do_shortcode( '[pods name="planet" pagination="0" limit="2"]~[/pods]' ) );
		$this->assertEquals( '~~', do_shortcode( '[pods name="planet" pagination="false" limit="2"]~[/pods]' ) );
		$this->assertEquals( '~~', do_shortcode( '[pods name="planet" pagination="off" limit="2"]~[/pods]' ) );
		$this->assertEquals( '~~', do_shortcode( '[pods name="planet" pagination="no" limit="2"]~[/pods]' ) );
		$this->assertEquals( '~~', do_shortcode( '[pods name="planet" pagination=0 limit="2"]~[/pods]' ) );
		$this->assertEquals( '~~', do_shortcode( '[pods name="planet" pagination=false limit="2"]~[/pods]' ) );
		$this->assertEquals( '~~', do_shortcode( '[pods name="planet" pagination=-1 limit="2"]~[/pods]' ) );
		$this->assertEquals( '~~', do_shortcode( '[pods name="planet" pagination=xyzzy limit="2"]~[/pods]' ) );

		/** @link https://github.com/pods-framework/pods/pull/2807 */
		$this->assertEquals( '57', do_shortcode( '[pods name="planet" page="1" limit="2"]{@number_of_moons}[/pods]' ) );
		$this->assertEquals( '5', do_shortcode( '[pods name="planet" page="2" limit="2"]{@number_of_moons}[/pods]' ) );

		// Not enough records to trigger pagination even if on
		$this->assertNotContains( '<a', do_shortcode( '[pods name="planet" pagination="1" limit="100"]~[/pods]' ) );

	}

	/**
	 * PR 2339
	 *
	 * @link  https://github.com/pods-framework/pods/pull/2339
	 * @since 3.0
	 */
	public function test_shortcode_pods_field_in_shortcode() {

		//add an item
		$this->pod->add( array( 'name' => 'Dagobah', 'number_of_moons' => 5 ) );

		//test shortcode
		$this->assertEquals( '5', do_shortcode( '[pods name ="planet" where="t.number_of_moons=5" field="number_of_moons"]' ) );
	}

}
