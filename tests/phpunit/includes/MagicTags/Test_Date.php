<?php
namespace Pods_Unit_tests\MagicTags;

/**
 * @package Pods_Unit_Tests
 * @group pods_magictags
 * @group pods_acceptance_tests
 */
class Date extends \Pods_Unit_Tests\Pods_UnitTestCase {
	/**
	 * @group pods-issue-4525
	 */
	public function test_create_template() {
		update_option( 'date_format', 'F j, Y' );

		$pod_name = __FUNCTION__;
		$pod_id = pods_api()->save_pod( array( 'storage' => 'meta', 'type' => 'post_type', 'name' => $pod_name ) );
		$params = array(
			'pod' => $pod_name,
			'pod_id' => $pod_id,
			'name' => 'testtext',
			'type' => 'text',
		);
		pods_api()->save_field( $params );

		$pod = Pods( $pod_name );
		$id = $pod->add( array( 'testtext' => 'RIP Fats Domino', 'post_date' => '2017-10-25 12:34' ) );
		$this->assertGreaterThan( 0, $id );

		$pod->fetch( $id );
		$result = $pod->do_magic_tags( '{@post_date}' );
		$this->assertEquals( 'October 25, 2017', $result );

		// Some additional tests which aren't exactly related to the Magic tag but further test the Pods functionality
		// Eventually these should get broken off to some field/post date specific tests
		$this->assertEquals( 'October 25, 2017', get_the_date( null, $id ) );
		$this->assertEquals( '2017-10-25 12:34:00', $pod->field( 'post_date' ) );
		$this->assertEquals( 'October 25, 2017', $pod->display( 'post_date' ) );
	}
}
