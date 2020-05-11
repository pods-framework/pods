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

	public function setUp() {
		$this->field = new PodsField_Pick();

		parent::setUp();
	}

	public function tearDown() {
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
	}

	/**
	 * @todo Cover display tests with actual relationship values.
	 */
	public function test_display_format_multi_relationship() {
		$this->markTestIncomplete( 'not yet implemented' );
	}

}
