<?php

namespace Pods_Unit_Tests\Pods\Field;

use Pods_Unit_Tests\Pods_UnitTestCase;
use PodsField_DateTime;

/**
 * @group              pods-field
 * @coversDefaultClass PodsField_DateTime
 */
class PodsField_DateTimeTest extends Pods_UnitTestCase {

	private $field;

	/**
	 * Default date format: mdy (m/d/Y)
	 * Default time type: 12h
	 * Default time format: h_mma (g:ia)
	 */
	public $defaultOptions = [
		'datetime_type'                  => 'format',
		'datetime_format_custom'         => '',
		'datetime_format_custom_js'      => '',
		'datetime_format'                => 'mdy',
		'datetime_time_type'             => '12',
		'datetime_time_format_custom'    => '',
		'datetime_time_format_custom_js' => '',
		'datetime_time_format'           => 'h_mma',
		'datetime_time_format_24'        => 'hh_mm',
		'datetime_allow_empty'           => 1,
	];

	public function setUp(): void {
		$this->field = new PodsField_DateTime();

		parent::setUp();
	}

	public function tearDown(): void {
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
		return [
			[ '2017-12-06', '12/06/2017 12:00am' ],
			[ '2017-12-06 15:04', '12/06/2017 3:04pm' ],
			[ '2017-12-06 15:04:50', '12/06/2017 3:04pm' ],
			[ '2017-06-12 15:04:50', '06/12/2017 3:04pm' ],
		];
	}

	/**
	 * @dataProvider displayCustomFormatDMYProvider
	 */
	public function test_display_custom_format_dmy( $value, $expected ) {
		$options = $this->defaultOptions;

		$options['datetime_type']          = 'custom';
		$options['datetime_format_custom'] = 'd/m/Y'; // Days and months switched.

		$this->assertEquals( $expected, $this->field->display( $value, null, $options ) );
	}

	public function displayCustomFormatDMYProvider() {
		return [
			[ '2017-12-06', '06/12/2017 12:00am' ],
			[ '2017-12-06 15:04', '06/12/2017 3:04pm' ],
			[ '2017-12-06 15:04:50', '06/12/2017 3:04pm' ],
			[ '2017-06-12 15:04:50', '12/06/2017 3:04pm' ],
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
			[ '2017-12-06', '2017-12-06 00:00:00' ],
			[ '2017-12-06 15:04', '2017-12-06 15:04:00' ],
			[ '2017-12-06 15:04:50', '2017-12-06 15:04:50' ],
			[ '2017-06-12 15:04:50', '2017-06-12 15:04:50' ],
			// Display format.
			[ '12/06/2017 12:00am', '2017-12-06 00:00:00' ],
			[ '12/06/2017 3:04pm', '2017-12-06 15:04:00' ],
			[ '06/12/2017 3:04pm', '2017-06-12 15:04:00' ],
		];
	}

	/**
	 * @dataProvider saveCustomFormatDMYProvider
	 */
	public function test_save_custom_format_dmy( $value, $expected ) {
		$options = $this->defaultOptions;

		$options['datetime_type']          = 'custom';
		$options['datetime_format_custom'] = 'd/m/Y'; // Days and months switched.

		$this->assertEquals( $expected, $this->field->pre_save( $value, null, null, $options ) );
	}

	public function saveCustomFormatDMYProvider() {
		return [
			[ '2017-12-06', '2017-12-06 00:00:00' ],
			[ '2017-12-06 15:04', '2017-12-06 15:04:00' ],
			[ '2017-12-06 15:04:50', '2017-12-06 15:04:50' ],
			[ '2017-06-12 15:04:50', '2017-06-12 15:04:50' ],
			// Display format d/m/Y.
			[ '12/06/2017 12:00am', '2017-06-12 00:00:00' ],
			[ '12/06/2017 3:04pm', '2017-06-12 15:04:00' ],
			[ '06/12/2017 3:04pm', '2017-12-06 15:04:00' ],
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
			[ '2017-12-06', true ],
			[ '2017-12-06 15:04', true ],
			[ '2017-12-06 15:04:50', true ],
			[ '2017-06-12 15:04:50', true ],
			// Display format.
			[ '12/06/2017 12:00am', true ],
			[ '12/06/2017 3:04pm', true ],
			[ '06/12/2017 3:04pm', true ],
		];
	}

	/**
	 * @dataProvider validateCustomFormatDMYProvider
	 */
	public function test_validate_custom_format_dmy( $value, $expected ) {
		$options = $this->defaultOptions;

		$options['datetime_type']          = 'custom';
		$options['datetime_format_custom'] = 'd/m/Y'; // Days and months switched.

		$this->assertEquals( $expected, $this->field->validate( $value, null, null, $options ) );
	}

	public function validateCustomFormatDMYProvider() {
		return [
			[ '2017-12-06', true ],
			[ '2017-12-06 15:04', true ],
			[ '2017-12-06 15:04:50', true ],
			[ '2017-06-12 15:04:50', true ],
			// Display format.
			[ '12/06/2017 12:00am', true ],
			[ '12/06/2017 3:04pm', true ],
			[ '06/12/2017 3:04pm', true ],
		];
	}

}
