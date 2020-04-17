<?php
namespace Pods_Unit_Tests;

use PodsField_DateTime;

require_once PODS_TEST_PLUGIN_DIR . '/classes/fields/datetime.php';

/**
 * Class Test_PodsField_Boolean
 *
 * @package            Pods_Unit_Tests
 * @group              pods-field
 * @coversDefaultClass PodsField_DateTime
 */
class Test_PodsField_DateTime extends Pods_UnitTestCase {

	private $field;

	/**
	 * Default date format: mdy (m/d/Y)
	 * Default time type: 12h
	 * Default time format: h_mma (g:ia)
	 */
	public $defaultOptions = array();

	public function setUp() {

		$this->field = new PodsField_DateTime();
	}

	public function tearDown() {

		unset( $this->field );
	}

	/**
	 * @dataProvider displayDefaultProvider
	 */
	public function test_display_default( $value, $expected ) {
		$options = $this->defaultOptions;

		$this->assertEquals( $expected, $this->field->display( $value, null, $options ) );
	}

	public function displayDefaultProvider() {

		return array(
			'2017-12-06'          => '12/06/2017 12:00am',
			'2017-12-06 15:04'    => '12/06/2017 3:04pm',
			'2017-12-06 15:04:50' => '12/06/2017 3:04pm',
			'2017-06-12 15:04:50' => '06/12/2017 3:04pm',
		);
	}

	/**
	 * @dataProvider saveDefaultsProvider
	 */
	public function test_save_defaults( $value, $expected ) {
		$options = $this->defaultOptions;

		$this->assertEquals( $expected, $this->field->pre_save( $value, null, null, $options ) );
	}

	public function saveDefaultsProvider() {

		return array(
			'2017-12-06'          => '2017-12-06 00:00:00',
			'2017-12-06 15:04'    => '2017-12-06 15:04:00',
			'2017-12-06 15:04:50' => '2017-12-06 15:04:50',
			'2017-06-12 15:04:50' => '2017-06-12 15:04:50',
			// Display format.
			'12/06/2017 12:00am'  => '2017-12-06 00:00:00',
			'12/06/2017 3:04pm'   => '2017-12-06 15:04:00',
			'06/12/2017 3:04pm'   => '2017-06-12 15:04:00',
		);
	}

	/**
	 * @dataProvider validateDefaultsProvider
	 */
	public function test_validate_defaults( $value, $expected ) {
		$options = $this->defaultOptions;

		$this->assertEquals( $expected, $this->field->validate( $value, null, null, $options ) );
	}

	public function validateDefaultsProvider() {

		return array(
			'2017-12-06'          => true,
			'2017-12-06 15:04'    => true,
			'2017-12-06 15:04:50' => true,
			'2017-06-12 15:04:50' => true,
			// Display format.
			'12/06/2017 12:00am'  => true,
			'12/06/2017 3:04pm'   => true,
			'06/12/2017 3:04pm'   => true,
		);
	}

}
