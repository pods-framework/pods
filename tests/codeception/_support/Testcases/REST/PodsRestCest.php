<?php

namespace Pods_Unit_Tests\Testcases\REST;

use Restv1Tester;

class PodsRestCest extends BaseRestCest {

	protected $pod_id;
	protected $group_id;
	protected $group_id2;
	protected $field_id;
	protected $field_id2;
	protected $field_id3;
	protected $field_id4;

	public function _before( Restv1Tester $I ) {
		parent::_before( $I );

		$this->test_rest_url = $this->pods_rest_url . 'fields/%d';

		$api = pods_api();

		$this->pod_id = $api->save_pod( [
			'storage' => 'meta',
			'type'    => 'post_type',
			'name'    => 'my-pod',
		] );

		$this->group_id = $api->save_group( [
			'pod_id' => $this->pod_id,
			'name'   => 'my-group',
		] );

		$this->group_id2 = $api->save_group( [
			'pod_id' => $this->pod_id,
			'name'   => 'my-group2',
		] );

		$this->field_id = $api->save_field( [
			'pod_id'   => $this->pod_id,
			'group_id' => $this->group_id,
			'name'     => 'my-field',
			'type'     => 'text',
		] );

		$this->field_id2 = $api->save_field( [
			'pod_id'   => $this->pod_id,
			'group_id' => $this->group_id,
			'name'     => 'my-field2',
			'type'     => 'text',
		] );

		$this->field_id3 = $api->save_field( [
			'pod_id'   => $this->pod_id,
			'group_id' => $this->group_id2,
			'name'     => 'my-field3',
			'type'     => 'text',
		] );

		$this->field_id4 = $api->save_field( [
			'pod_id'   => $this->pod_id,
			'group_id' => $this->group_id2,
			'name'     => 'my-field4',
			'type'     => 'text',
		] );
	}

	public function _after( Restv1Tester $I ) {
		$this->pod_id    = null;
		$this->group_id  = null;
		$this->group_id2 = null;
		$this->field_id  = null;
		$this->field_id2 = null;
		$this->field_id3 = null;
		$this->field_id4 = null;

		parent::_after( $I );
	}
}