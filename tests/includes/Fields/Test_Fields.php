<?php
namespace Pods_Unit_Tests\Fields;

//use Pods_Unit_Tests\Pods_UnitTestCase;

/**
 * @group pods_acceptance_tests
 */
class Test_Fields extends \Pods_Unit_Tests\Pods_UnitTestCase {

	protected $pod_name = __CLASS__;

	/** @var int|null */
	protected $pod_id = null;

	/** @var \Pods|false|null */
	protected $pod = null;

	protected $pod_storage = 'meta';
	// meta or table

	protected $pod_type = 'post_type';
	// post_type
	// pod
	// Maybe 'pod' 'table'

	/**
	 * List of possible combinations of pod storage types to test
	 * @var array
	 */
	public $pod_types = array(
		array( 'storage' => 'meta', 'pod_type' => 'post_type' ),
		array( 'storage' => 'table', 'pod_type' => 'post_type' ),
		array( 'storage' => 'table', 'pod_type' => 'pod' ),
	);

	public function add_types( $data ) {
		$new_data = array();
		foreach ( $data as $k => $v ) {
			foreach ( $this->pod_types as $types ) {
				$new_data[] = array_merge( $v, $types );
			}
		}
		return $new_data;
	}

	public function setup_pod( $name, $storage, $type ) {
		$this->pod_id = pods_api()->save_pod( array( 'name' => $name, 'storage' => $storage, 'type' => $type ) );
		$this->pod    = pods( $this->pod_name );
		return $this->pod;
	}


	public function setUp() {
		global $wpdb;
		$wpdb->query( 'SET autocommit = 0;' );
		$wpdb->query( 'START TRANSACTION;' );

	}

	public function tearDown() {
		global $wpdb;
		pods_api()->delete_pod( array( 'id' => $this->pod_id ) );
		$wpdb->query( 'ROLLBACK' );
	}

}
