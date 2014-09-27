<?php
namespace Pods_Unit_Tests\Acceptance_Tests;

/**
 * @group pods_acceptance_tests
 */
class Test_Shortcodes extends \Pods_Unit_Tests\Pods_UnitTestCase {
	public function setUp()
	{
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

	public function tearDown()
	{
		pods_api()->delete_pod( $this->pod_id );
	}

	/**
	 * @since 3.0
	 */
	public function test_shortcode_pods()
	{
		//Initialize the items array
		$items = array();

		//add an item
		$item[1] = $this->pod->add( array( 'name' => 'Felucia', 'number_of_moons' => 5 ) );

		//test shortcode
		$this->assertEquals( '5', do_shortcode( '[pods name ="planet" where="t.number_of_moons=5"]{@number_of_moons}[/pods]' ) );

		//add another item
		$item[2] = $this->pod->add( array( 'name' => 'Geonosis', 'number_of_moons' => 7 ) );

		//test shortcode
		$this->assertEquals( '5', do_shortcode( '[pods name ="planet" where="t.number_of_moons=5"]{@number_of_moons}[/pods]' ) );

		//add third item
		$item[3] = $this->pod->add( array( 'name' => 'Naboo', 'number_of_moons' => 5 ) );

		//test shortcode
		$this->assertEquals( '55', do_shortcode( '[pods name ="planet" where="t.number_of_moons=5"]{@number_of_moons}[/pods]' ) );
	}

	/**
	 * PR 2339
	 *
	 * @link  https://github.com/pods-framework/pods/pull/2339
	 * @since 3.0
	 */
	public function test_shortcode_pods_field_in_shortcode()
	{
		//add an item
		$this->pod->add( array( 'name' => 'Felucia', 'number_of_moons' => 5 ) );

		//test shortcode
		$this->assertEquals( '5', do_shortcode( '[pods name ="planet" where="t.number_of_moons=5" field="number_of_moons"]' ) );
	}
}
