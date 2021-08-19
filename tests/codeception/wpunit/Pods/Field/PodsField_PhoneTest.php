<?php

namespace Pods_Unit_Tests\Pods\Field;

use Pods_Unit_Tests\Pods_UnitTestCase;
use PodsField_Phone;

/**
 * @group              pods-field
 * @coversDefaultClass PodsField_Phone
 */
class PodsField_PhoneTest extends Pods_UnitTestCase {

	private $field;

	public $defaultOptions = [
		'phone_format'                 => '999-999-9999 x999',
		'phone_enable_phone_extension' => '0',
		'phone_max_length'             => '25',
		'phone_html5'                  => '0',
		'phone_placeholder'            => '',
	];

	public function setUp() : void {
		$this->field = new PodsField_Phone();

		parent::setUp();
	}

	public function tearDown() : void {
		unset( $this->field );

		parent::tearDown();
	}

	/**
	 * @dataProvider displayDefaultProvider
	 */
	public function test_display_default( $value, $expected ) {
		$options = $this->defaultOptions;

		$this->assertEquals( $expected, $this->field->display( $value, null, $options ) );
	}

	public function displayDefaultProvider() {
		// Display does not reformat, formatting happens on save.
		return [
			[ '123-123-1234', '123-123-1234' ],
			[ '(123) 123-1234', '(123) 123-1234' ],
			[ '+1 123-123-1234', '+1 123-123-1234' ],
			[ '+1 123-123-1234', '+1 123-123-1234' ],
			[ '+1 123.123.1234', '+1 123.123.1234' ],
			[ '123-123-1234 x555', '123-123-1234 x555' ],
			[ '(123) 123-1234 x555', '(123) 123-1234 x555' ],
			[ '+1 123-123-1234 x555', '+1 123-123-1234 x555' ],
			[ '+1 123-123-1234 x555', '+1 123-123-1234 x555' ],
			[ '+1 123.123.1234 x555', '+1 123.123.1234 x555' ],
		];
	}

	/**
	 * @dataProvider displayParenthesisProvider
	 */
	public function test_display_parenthesis( $value, $expected ) {
		$options = $this->defaultOptions;

		$options['phone_format'] = '(999) 999-9999 x999';

		$this->assertEquals( $expected, $this->field->display( $value, null, $options ) );
	}

	public function displayParenthesisProvider() {
		// Display does not reformat, formatting happens on save.
		return [
			[ '123-123-1234', '123-123-1234' ],
			[ '(123) 123-1234', '(123) 123-1234' ],
			[ '+1 123-123-1234', '+1 123-123-1234' ],
			[ '+1 123-123-1234', '+1 123-123-1234' ],
			[ '+1 123.123.1234', '+1 123.123.1234' ],
			[ '123-123-1234 x555', '123-123-1234 x555' ],
			[ '(123) 123-1234 x555', '(123) 123-1234 x555' ],
			[ '+1 123-123-1234 x555', '+1 123-123-1234 x555' ],
			[ '+1 123-123-1234 x555', '+1 123-123-1234 x555' ],
			[ '+1 123.123.1234 x555', '+1 123.123.1234 x555' ],
		];
	}

	/**
	 * @dataProvider displayDotProvider
	 */
	public function test_display_dot( $value, $expected ) {
		$options = $this->defaultOptions;

		$options['phone_format'] = '999.999.9999 x999';

		$this->assertEquals( $expected, $this->field->display( $value, null, $options ) );
	}

	public function displayDotProvider() {
		// Display does not reformat, formatting happens on save.
		return [
			[ '123-123-1234', '123-123-1234' ],
			[ '(123) 123-1234', '(123) 123-1234' ],
			[ '+1 123-123-1234', '+1 123-123-1234' ],
			[ '+1 123-123-1234', '+1 123-123-1234' ],
			[ '+1 123.123.1234', '+1 123.123.1234' ],
			[ '123-123-1234 x555', '123-123-1234 x555' ],
			[ '(123) 123-1234 x555', '(123) 123-1234 x555' ],
			[ '+1 123-123-1234 x555', '+1 123-123-1234 x555' ],
			[ '+1 123-123-1234 x555', '+1 123-123-1234 x555' ],
			[ '+1 123.123.1234 x555', '+1 123.123.1234 x555' ],
		];
	}

	/**
	 * @dataProvider displayInternationalProvider
	 */
	public function test_display_international( $value, $expected ) {
		$options = $this->defaultOptions;

		$options['phone_format'] = 'international';

		$this->assertEquals( $expected, $this->field->display( $value, null, $options ) );
	}

	public function displayInternationalProvider() {
		// Display does not reformat, formatting happens on save.
		return [
			[ '123-123-1234', '123-123-1234' ],
			[ '(123) 123-1234', '(123) 123-1234' ],
			[ '+1 123-123-1234', '+1 123-123-1234' ],
			[ '+1 123-123-1234', '+1 123-123-1234' ],
			[ '+1 123.123.1234', '+1 123.123.1234' ],
			[ '123-123-1234 x555', '123-123-1234 x555' ],
			[ '(123) 123-1234 x555', '(123) 123-1234 x555' ],
			[ '+1 123-123-1234 x555', '+1 123-123-1234 x555' ],
			[ '+1 123-123-1234 x555', '+1 123-123-1234 x555' ],
			[ '+1 123.123.1234 x555', '+1 123.123.1234 x555' ],
		];
	}

	/**
	 * @dataProvider saveDefaultsProvider
	 */
	public function test_save_defaults( $value, $expected ) {
		$options = $this->defaultOptions;

		$this->assertEquals( $expected, $this->field->pre_save( $value, null, null, $options ) );
	}

	public function saveDefaultsProvider() {
		return [
			[ '123-123-1234', '123-123-1234' ],
			[ '(123) 123-1234', '123-123-1234' ],
			[ '+1 123-123-1234', '123-123-1234' ],
			[ '+1 123-123-1234', '123-123-1234' ],
			[ '+1 123.123.1234', '123-123-1234' ],
			[ '123-123-1234 x555', '123-123-1234' ],
			[ '(123) 123-1234 x555', '123-123-1234' ],
			[ '+1 123-123-1234 x555', '123-123-1234' ],
			[ '+1 123-123-1234 x555', '123-123-1234' ],
			[ '+1 123.123.1234 x555', '123-123-1234' ],
		];
	}

	/**
	 * @dataProvider saveParenthesisProvider
	 */
	public function test_save_parenthesis( $value, $expected ) {
		$options = $this->defaultOptions;

		$options['phone_format'] = '(999) 999-9999 x999';

		$this->assertEquals( $expected, $this->field->pre_save( $value, null, null, $options ) );
	}

	public function saveParenthesisProvider() {
		return [
			[ '123-123-1234', '(123) 123-1234' ],
			[ '(123) 123-1234', '(123) 123-1234' ],
			[ '+1 123-123-1234', '(123) 123-1234' ],
			[ '+1 123-123-1234', '(123) 123-1234' ],
			[ '+1 123.123.1234', '(123) 123-1234' ],
			[ '123-123-1234 x555', '(123) 123-1234' ],
			[ '(123) 123-1234 x555', '(123) 123-1234' ],
			[ '+1 123-123-1234 x555', '(123) 123-1234' ],
			[ '+1 123-123-1234 x555', '(123) 123-1234' ],
			[ '+1 123.123.1234 x555', '(123) 123-1234' ],
		];
	}

	/**
	 * @dataProvider saveDotProvider
	 */
	public function test_save_dot( $value, $expected ) {
		$options = $this->defaultOptions;

		$options['phone_format'] = '999.999.9999 x999';

		$this->assertEquals( $expected, $this->field->pre_save( $value, null, null, $options ) );
	}

	public function saveDotProvider() {
		return [
			[ '123-123-1234', '123.123.1234' ],
			[ '(123) 123-1234', '123.123.1234' ],
			[ '+1 123-123-1234', '123.123.1234' ],
			[ '+1 123-123-1234', '123.123.1234' ],
			[ '+1 123.123.1234', '123.123.1234' ],
			[ '123-123-1234 x555', '123.123.1234' ],
			[ '(123) 123-1234 x555', '123.123.1234' ],
			[ '+1 123-123-1234 x555', '123.123.1234' ],
			[ '+1 123-123-1234 x555', '123.123.1234' ],
			[ '+1 123.123.1234 x555', '123.123.1234' ],
		];
	}

	/**
	 * @dataProvider saveInternationalProvider
	 */
	public function test_save_international( $value, $expected ) {
		$options = $this->defaultOptions;

		$options['phone_format'] = 'international';

		$this->assertEquals( $expected, $this->field->pre_save( $value, null, null, $options ) );
	}

	public function saveInternationalProvider() {
		return [
			[ '123-123-1234', '123-123-1234' ],
			[ '(123) 123-1234', '(123) 123-1234' ],
			[ '+1 123-123-1234', '+1 123-123-1234' ],
			[ '+1 123-123-1234', '+1 123-123-1234' ],
			[ '+1 123.123.1234', '+1 123.123.1234' ],
			[ '123-123-1234 x555', '123-123-1234 x555' ],
			[ '(123) 123-1234 x555', '(123) 123-1234 x555' ],
			[ '+1 123-123-1234 x555', '+1 123-123-1234 x555' ],
			[ '+1 123-123-1234 x555', '+1 123-123-1234 x555' ],
			[ '+1 123.123.1234 x555', '+1 123.123.1234 x555' ],
		];
	}

	/**
	 * @dataProvider validateDefaultsProvider
	 */
	public function test_validate_defaults( $value, $expected ) {
		$options = $this->defaultOptions;

		$this->assertEquals( $expected, $this->field->validate( $value, null, null, $options ) );
	}

	public function validateDefaultsProvider() {
		return [
			[ '123-123-1234', true ],
			[ '(123) 123-1234', true ],
			[ '+1 123-123-1234', true ],
			[ '+1 123-123-1234', true ],
			[ '+1 123.123.1234', true ],
			[ '123-123-1234 x555', true ],
			[ '(123) 123-1234 x555', true ],
			[ '+1 123-123-1234 x555', true ],
			[ '+1 123-123-1234 x555', true ],
			[ '+1 123.123.1234 x555', true ],
		];
	}

}
