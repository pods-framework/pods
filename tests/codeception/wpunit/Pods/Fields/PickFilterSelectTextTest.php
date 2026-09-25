<?php

namespace Pods_Unit_Tests\Pods\Fields;

use PodsField_Pick;
use Pods_Unit_Tests\Pods_UnitTestCase;

/**
 * The front-end filter form (ui/front/filters.php) relies on the pick field
 * emitting an empty option, because that option is what clears an active filter.
 *
 * PodsField_Pick only adds the empty option when the value is empty, when the
 * field is not required, or when _select_text_always_show is set. A required
 * field that already has a value therefore loses its empty option -- and with it
 * any way to reset the filter -- unless the filter form opts in explicitly.
 *
 * @group  pods-filters-labels
 * @group  pods-issue-7561
 * @covers PodsField_Pick::data
 */
class PickFilterSelectTextTest extends Pods_UnitTestCase {

	/**
	 * @var PodsField_Pick
	 */
	protected $field;

	public function setUp(): void {
		parent::setUp();

		$this->field = new PodsField_Pick();
	}

	public function tearDown(): void {
		$this->field = null;

		parent::tearDown();
	}

	/**
	 * Option set applied by ui/front/filters.php to every filter dropdown.
	 *
	 * @param array $extra Additional/overriding options.
	 *
	 * @return array
	 */
	protected function filter_options( array $extra = [] ) {
		return array_merge(
			[
				'name'                         => 'test_pick',
				'label'                        => 'Test Pick',
				'type'                         => 'pick',
				'pick_object'                  => 'pick-custom',
				'pick_custom'                  => "red|Red\nblue|Blue",
				'pick_format_type'             => 'single',
				'pick_format_single'           => 'dropdown',
				'pick_select_text'             => '-- Test Pick --',
				'pick_show_select_text'        => 1,
				'pick_select_text_always_show' => 1,
			],
			$extra
		);
	}

	/**
	 * The empty option must survive on a required field that already has a value,
	 * otherwise the filter cannot be cleared.
	 */
	public function test_empty_option_present_for_required_field_with_value() {
		$options = $this->filter_options( [ 'required' => 1 ] );

		$data = $this->field->data( 'test_pick', 'red', $options );

		$this->assertArrayHasKey(
			'',
			$data,
			'A required filter that already has a value must still offer the empty option so it can be cleared.'
		);
	}

	/**
	 * The empty option must be labelled after the field, not the generic fallback.
	 */
	public function test_empty_option_uses_field_specific_select_text() {
		$options = $this->filter_options( [ 'required' => 1 ] );

		$data = $this->field->data( 'test_pick', 'red', $options );

		$this->assertSame( '-- Test Pick --', $data[''] );
		$this->assertNotSame( '-- Select One --', $data[''] );
	}

	/**
	 * Without the always-show opt-in, a required field with a value drops the
	 * empty option. This documents the behaviour the filter form works around.
	 */
	public function test_required_field_without_always_show_drops_empty_option() {
		$options = $this->filter_options( [ 'required' => 1 ] );

		unset( $options['pick_select_text_always_show'] );

		$data = $this->field->data( 'test_pick', 'red', $options );

		$this->assertArrayNotHasKey( '', $data );
	}
}
