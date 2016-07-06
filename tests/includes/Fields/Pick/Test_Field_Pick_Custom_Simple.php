<?php
namespace Pods_Unit_Tests\Fields\Pick;

/**
 * @group pods_acceptance_tests
 */
class Test_Fields_Pick_Custom_Simple extends \Pods_Unit_Tests\Fields\Test_Fields {

	protected $pod_name = 'pick_custom_simple';

	/** @var int|null */
	protected $pod_id = null;

	/** @var \Pods|false|null */
	protected $pod = null;
	
	public function dataprovider_simple() {
		return $this->add_types( array(
			array( 'valid_simple_field', 'ABC', 'assertEquals', 'ABC' ),
			array( 'valid_multiple_simple_field', array( 'ABC', 'DEF' ), 'assertEquals', array( 'ABC', 'DEF' ) ),
			//array( 'valid_simple_field_with_a_really_long_name_aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', 'ABC', 'assertEquals', 'ABC' ),
			array( 'invalid_simple_field', 'invalid field', 'assertNotEquals', 'invalid field' ),
			//array( '2"x2"', 'assertEquals', '2"x2"' ),
		) );
	}
	
	/**
	 * @param $value
	 * @param $test
	 * @param $equals
	 * @param $storage
	 * @param $pod_type
	 * @dataProvider dataprovider_simple
	 * @group pods-fields
	 * @group pods-fields-pick
	 * @group pods-fields-pick-custom-simple
	 */
	public function test_fields( $field_name, $value, $test, $equals, $storage, $pod_type ) {
		$this->pod_name = wp_generate_password( 4, false );
		//$field_name = __FUNCTION__ . '_field';
		//$field_name = "{$field_name}_{$storage}_{$pod_type}";
		$params = array(
			'pod'    => $this->pod_name,
			'pod_id' => $this->pod_id,
			'name'   => $field_name,
			'type'   => 'pick',
			'pick_object' => 'custom-simple',
			'pick_custom' => "ABC\nDEF\nabc|abc\ndef|def",
			'pick_format_type' => 'multi',
		);
		$this->setup_pod( $this->pod_name, $storage, $pod_type );
		pods_api()->save_field( $params );
		
		$ID = $this->pod->add( array( 'name' => __FUNCTION__, 'post_status' => 'publish', $field_name => $value ) );
		$res = $this->pod->find( array( 'name' => __FUNCTION__, 'ID' => $ID ) );
		$this->pod->fetch();
		if ( is_array( $equals ) ) {
			$field = $this->pod->field( $field_name );
		} else {
			$field = $this->pod->display( $field_name );
		}
		$this->$test( $equals, $field );

	}

	public function dataprovider_simple_quoted() {
		return $this->add_types( array(
			array( 'valid_quoted_field', '5"x5"', 'assertEquals', '5"x5"' ),
			array( 'invalid_quoted_field', '"invalid quoted text"', 'assertNotEquals', '"invalid quoted text"' ),

		) );
	}
	
	/**
	 * @param $value
	 * @param $test
	 * @param $equals
	 * @dataProvider dataprovider_simple_quoted
	 * @group pods-fields
	 * @group pods-fields-pick
	 * @group pods-fields-pick-custom-simple
	 */
	public function test_fields_quoted( $field_name, $value, $test, $equals, $storage, $pod_type ) {
		$this->pod_name = wp_generate_password( 4, false );
		//$field_name = __FUNCTION__ . '_field';
		$params = array(
			'pod'    => $this->pod_name,
			'pod_id' => $this->pod_id,
			'name'   => $field_name,
			'type'   => 'pick',
			'pick_object' => 'custom-simple',
			'pick_custom' => "5\"x5\"\n10\"x10\"\n1\"x1\"|1\"x1\"",
			'pick_format_type' => 'single',
		);
		$this->setup_pod( $this->pod_name, $storage, $pod_type );
		pods_api()->save_field( $params );

		$ID = $this->pod->add( array( 'name' => __FUNCTION__, 'post_status' => 'publish', $field_name => $value ) );
		$res = $this->pod->find( array( 'name' => __FUNCTION__, 'ID' => $ID ) );
		$this->pod->fetch();
		$field = $this->pod->display( $field_name );
		$this->$test( $equals, $field );

	}


	public function dataprovider_predefined_usstates() {
		return $this->add_types( array(
			array( 'valid_us_state', 'WA', 'assertEquals', 'WA' ),
			array( 'invalid_us_state', 'invalid state', 'assertNotEquals', 'invalid state' ),

		) );
	}

	/**
	 * @param $value
	 * @param $test
	 * @param $equals
	 * @dataProvider dataprovider_predefined_usstates
	 * @group pods-fields
	 * @group pods-fields-pick
	 * @group pods-fields-pick-predefined
	 */
	public function test_fields_predefined_usstates( $field_name, $value, $test, $equals, $storage, $pod_type ) {
		$this->pod_name = wp_generate_password( 4, false );
		//$field_name = __FUNCTION__ . '_field';
		$params = array(
			'pod'    => $this->pod_name,
			'pod_id' => $this->pod_id,
			'name'   => $field_name,
			'type'   => 'pick',
			'pick_object' => 'us_state',
//			'pick_custom' => "5\"x5\"\n10\"x10\"\n1\"x1\"|1\"x1\"",
			'pick_format_type' => 'single',
		);
		$this->setup_pod( $this->pod_name, $storage, $pod_type );
		pods_api()->save_field( $params );

		$ID = $this->pod->add( array( 'name' => __FUNCTION__, 'post_status' => 'publish', $field_name => $value ) );
		$res = $this->pod->find( array( 'name' => __FUNCTION__, 'ID' => $ID ) );
		$this->pod->fetch();
		$field = $this->pod->display( $field_name );
		$this->$test( $equals, $field );

	}


}
