<?php
namespace Pods_Unit_Tests;

use PodsField_Currency;

require PODS_TEST_PLUGIN_DIR . '/classes/fields/currency.php';

/**
 * Class Test_PodsField_Boolean
 *
 * @package            Pods_Unit_Tests
 * @group              pods-field
 * @coversDefaultClass PodsField_Currency
 */
class Test_PodsField_Currency extends Pods_UnitTestCase {

	private $field;

	public $defaultOptions = array(
		"currency_format_type"      => "number",
		"currency_format_sign"      => "usd",
		"currency_format_placement" => "before",
		"currency_format"           => "9,999.99",
		"currency_decimals"         => 2,
		"currency_decimal_handling" => "default"
	);

	public function setUp() {

		$this->field = new PodsField_Currency();
	}

	public function tearDown() {

		unset( $this->field );
	}

	/**
	 * @dataProvider formatDefaultsProvider
	 */
	public function test_format_defaults( $value, $expected ) {
		$options = $this->defaultOptions;

		$this->assertEquals( $expected, $this->field->format( $value, null, $options ) );
	}

	public function formatDefaultsProvider() {

		return [
			[ "-1", "-1.00" ],
			[ "0", "0.00" ],
			[ "1", "1.00" ],
			[ "1.", "1.00" ],
			[ "1.5", "1.50" ],
			[ "10", "10.00" ],
			[ "10.01", "10.01" ],
			[ "100", "100.00" ],
			[ "1000", "1,000.00" ],
			[ "10000", "10,000.00" ],
			[ "1000000", "1,000,000.00" ],
		];
	}

	/**
	 * @dataProvider formatDecimalDashProvider
	 */
	public function test_format_decimal_dash( $value, $expected ) {
		$options = $this->defaultOptions;
		$options[ 'currency_decimal_handling' ] = 'dash';

		$this->assertEquals( $expected, $this->field->format( $value, null, $options ) );
	}

	public function formatDecimalDashProvider() {

		return [
			[ "-1", "-1.-" ],
			[ "0", "0.-" ],
			[ "1", "1.-" ],
			[ "1.", "1.-" ],
			[ "1.5", "1.50" ],
			[ "10", "10.-" ],
			[ "10.01", "10.01" ],
			[ "100", "100.-" ],
			[ "1000", "1,000.-" ],
			[ "10000", "10,000.-" ],
			[ "1000000", "1,000,000.-" ],
		];
	}

	/**
	 * @dataProvider displayDefaultsProvider
	 */
	public function test_display_defaults( $value, $expected ) {
		$options = $this->defaultOptions;

		$this->assertEquals( $expected, $this->field->display( $value, null, $options ) );
	}

	public function displayDefaultsProvider() {

		return [
			[ "-1", "$-1.00" ],
			[ "0", "$0.00" ],
			[ "1", "$1.00" ],
			[ "1.", "$1.00" ],
			[ "1.5", "$1.50" ],
			[ "10", "$10.00" ],
			[ "10.01", "$10.01" ],
			[ "100", "$100.00" ],
			[ "1000", "$1,000.00" ],
			[ "10000", "$10,000.00" ],
			[ "1000000", "$1,000,000.00" ],
		];
	}

}
