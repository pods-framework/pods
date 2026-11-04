<?php

namespace Pods_Unit_Tests\Pods\Field;

use Pods_Unit_Tests\Pods_UnitTestCase;
use PodsField_Pick;

/**
 * @group              pods-field
 * @coversDefaultClass PodsField_Pick
 */
class PodsField_PickTest extends Pods_UnitTestCase {

	/**
	 * @var PodsField_Pick
	 */
	private $field;

	public $defaultOptions = [
		'pick_format_type'              => 'single',
		'pick_format_single'            => 'dropdown',
		'pick_format_multi'             => 'checkbox',
		'pick_display_format_multi'     => 'default',
		'pick_display_format_separator' => ', ',
	];

	public function setUp(): void {
		$this->field = new PodsField_Pick();

		parent::setUp();
	}

	public function tearDown(): void {
		unset( $this->field );

		parent::tearDown();
	}

	/**
	 * Single values.
	 */
	public function test_format_defaults() {
		$options = $this->defaultOptions;

		$value = [
			'item1',
		];

		$expected = 'item1';

		$this->assertEquals( $expected, $this->field->display( $value, null, $options ) );
	}

	/**
	 * Multiple values and display formats.
	 */
	public function test_display_format_multi_simple() {
		$options                     = $this->defaultOptions;
		$options['pick_format_type'] = 'multi';

		$value = [
			'item1',
			'item2',
			'item3',
		];

		$expected = 'item1, item2, and item3';

		$this->assertEquals( $expected, $this->field->display( $value, null, $options ) );

		// no_serial display format.
		$options['pick_display_format_multi'] = 'non_serial';

		$expected = 'item1, item2 and item3';

		$this->assertEquals( $expected, $this->field->display( $value, null, $options ) );

		// custom display format.
		$options['pick_display_format_multi'] = 'custom';

		$expected = 'item1, item2, item3';

		$this->assertEquals( $expected, $this->field->display( $value, null, $options ) );

		// custom display format separator.
		$options['pick_display_format_multi']     = 'custom';
		$options['pick_display_format_separator'] = ' | ';

		$expected = 'item1 | item2 | item3';

		$this->assertEquals( $expected, $this->field->display( $value, null, $options ) );

		// List display format.

		$options['pick_display_format_multi'] = 'ul';

		$expected = '<ul><li>item1</li><li>item2</li><li>item3</li></ul>';

		$this->assertEquals( $expected, $this->field->display( $value, null, $options ) );

		// Line break display format.

		$options['pick_display_format_multi'] = 'br';

		$expected = 'item1<br />item2<br />item3';

		$this->assertEquals( $expected, $this->field->display( $value, null, $options ) );
	}

	/**
	 * @todo Cover display tests with actual relationship values.
	 */
	public function test_display_format_multi_relationship() {
		$this->markTestSkipped( 'not yet implemented' );
	}

	/**
	 * Relationship labels preserve literal comparison symbols.
	 */
	public function test_build_dfv_field_item_data_preserves_comparison_symbols() {
		$args = (object) [
			'options' => [
				'pick_object' => 'custom-simple',
			],
			'value'   => [],
		];

		$this->assertSame( '<1', $this->field->build_dfv_field_item_data_recurse_item( 1, '<1', $args )['name'] );
		$this->assertSame( '<1', $this->field->build_dfv_field_item_data_recurse_item( 2, '&lt;1', $args )['name'] );
		$this->assertSame( ">10'000", $this->field->build_dfv_field_item_data_recurse_item( 3, ">10'000", $args )['name'] );
		$this->assertSame( ">10'000", $this->field->build_dfv_field_item_data_recurse_item( 4, '&gt;10\'000', $args )['name'] );
		$this->assertSame( 'Price <100 and >50', $this->field->build_dfv_field_item_data_recurse_item( 5, 'Price <100 and >50', $args )['name'] );
		$this->assertSame( 'x < y', $this->field->build_dfv_field_item_data_recurse_item( 6, 'x < y', $args )['name'] );
		$this->assertSame( 'Label', $this->field->build_dfv_field_item_data_recurse_item( 7, '<strong>Label</strong>', $args )['name'] );
		$this->assertSame( '', $this->field->build_dfv_field_item_data_recurse_item( 8, '<script>alert(1)</script>', $args )['name'] );
		$this->assertSame( '', $this->field->build_dfv_field_item_data_recurse_item( 9, '<script data-value=">">alert(1)</script>', $args )['name'] );
		$this->assertSame( 'Label', $this->field->build_dfv_field_item_data_recurse_item( 10, " \nLabel\t", $args )['name'] );
	}

}
