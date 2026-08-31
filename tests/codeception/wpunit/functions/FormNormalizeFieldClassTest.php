<?php

namespace Pods_Unit_Tests\Functions;

use Pods_Unit_Tests\Pods_UnitTestCase;

/**
 * The field `class` option accepts a string or an array (PodsForm::merge_attributes()
 * implodes arrays), so row templates must not concatenate it directly.
 *
 * @group  pods-forms
 * @group  pods-issue-7292
 * @covers ::pods_form_normalize_field_class
 */
class FormNormalizeFieldClassTest extends Pods_UnitTestCase {

	public function test_string_class_is_returned() {
		$this->assertSame( 'my-class', pods_form_normalize_field_class( 'my-class' ) );
	}

	/**
	 * The regression: an array previously produced "Array to string conversion".
	 */
	public function test_array_class_is_imploded() {
		$this->assertSame(
			'one two',
			pods_form_normalize_field_class( [ 'one', 'two' ] )
		);
	}

	public function test_empty_values_return_empty_string() {
		$this->assertSame( '', pods_form_normalize_field_class( '' ) );
		$this->assertSame( '', pods_form_normalize_field_class( null ) );
		$this->assertSame( '', pods_form_normalize_field_class( [] ) );
	}

	/**
	 * Unsafe tokens must be stripped, matching every other consumer of the option.
	 */
	public function test_unsafe_characters_are_stripped() {
		$result = pods_form_normalize_field_class( 'good"onclick=alert(1)' );

		$this->assertStringNotContainsString( '"', $result );
		$this->assertStringNotContainsString( '(', $result );
	}
}
