<?php
namespace Pods_Unit_Tests;

use PodsField_Currency;

require_once PODS_TEST_PLUGIN_DIR . '/classes/fields/currency.php';

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

		return array(
			array( "-1", "-1.00" ),
			array( "0", "0.00" ),
			array( "1", "1.00" ),
			array( "1.", "1.00" ),
			array( "1.5", "1.50" ),
			array( "10", "10.00" ),
			array( "10.01", "10.01" ),
			array( "100", "100.00" ),
			array( "1000", "1,000.00" ),
			array( "10000", "10,000.00" ),
			array( "1000000", "1,000,000.00" ),
		);
	}

	/**
	 * @dataProvider formatDecimalCommaProvider
	 */
	public function test_format_decimal_comma( $value, $expected ) {
		$options = $this->defaultOptions;
		$options[ 'currency_format' ] = '9.999,99';

		$this->assertEquals( $expected, $this->field->format( $value, null, $options ) );
	}

	public function formatDecimalCommaProvider() {

		return array(
			array( "-1", "-1,00" ),
			array( "0", "0,00" ),
			array( "1", "1,00" ),
			array( "1.", "1,00" ),
			array( "1.5", "1,50" ),
			array( "10", "10,00" ),
			array( "10.01", "10,01" ),
			array( "100", "100,00" ),
			array( "1000", "1.000,00" ),
			array( "10000", "10.000,00" ),
			array( "1000000", "1.000.000,00" ),
		);
	}

	/**
	 * @dataProvider formatThousandsQuoteProvider
	 */
	public function test_format_thousands_quote( $value, $expected ) {
		$options = $this->defaultOptions;
		$options[ 'currency_format' ] = '9\'999.99';

		$this->assertEquals( $expected, $this->field->format( $value, null, $options ) );
	}

	public function formatThousandsQuoteProvider() {
		return array(
			array( "1000", "1'000.00" ),
			array( "10000", "10'000.00" ),
			array( "1000000", "1'000'000.00" ),
		);
	}

	/**
	 * @dataProvider formatSpaceCommaProvider
	 */
	public function test_format_space_comma( $value, $expected ) {
		$options = $this->defaultOptions;
		$options[ 'currency_format' ] = '9 999,99';

		$this->assertEquals( $expected, $this->field->format( $value, null, $options ) );
	}

	public function formatSpaceCommaProvider() {

		return array(
			array( "1000", "1 000,00" ),
			array( "10000", "10 000,00" ),
			array( "1000000", "1 000 000,00" ),
		);
	}

	/**
	 * @dataProvider formatDecimalDashProvider
	 * @covers ::trim_decimals
	 */
	public function test_format_decimal_dash( $value, $expected ) {
		$options = $this->defaultOptions;
		$options[ 'currency_decimal_handling' ] = 'dash';

		$this->assertEquals( $expected, $this->field->format( $value, null, $options ) );
	}

	public function formatDecimalDashProvider() {

		return array(
			array( "-1", "-1.-" ),
			array( "0", "0.-" ),
			array( "1", "1.-" ),
			array( "1.", "1.-" ),
			array( "1.5", "1.50" ),
			array( "10", "10.-" ),
			array( "10.01", "10.01" ),
			array( "100", "100.-" ),
			array( "1000", "1,000.-" ),
			array( "10000", "10,000.-" ),
			array( "1000000", "1,000,000.-" ),
		);
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
			array( "-1", "$-1.00" ),
			array( "0", "$0.00" ),
			array( "1", "$1.00" ),
			array( "1.", "$1.00" ),
			array( "1.5", "$1.50" ),
			array( "10", "$10.00" ),
			array( "10.01", "$10.01" ),
			array( "100", "$100.00" ),
			array( "1000", "$1,000.00" ),
			array( "10000", "$10,000.00" ),
			array( "1000000", "$1,000,000.00" ),
		);
	}

	/**
	 * @dataProvider displayFormatCurrencyAfterProvider
	 */
	public function test_display_format_currency_after( $value, $expected ) {
		$options = $this->defaultOptions;
		$options[ 'currency_format_placement' ] = 'after';

		$this->assertEquals( $expected, $this->field->display( $value, null, $options ) );
	}

	public function displayFormatCurrencyAfterProvider() {

		return array(
			array( "-1", "-1.00$" ),
			array( "0", "0.00$" ),
			array( "1", "1.00$" ),
			array( "1.", "1.00$" ),
			array( "1.5", "1.50$" ),
			array( "10", "10.00$" ),
			array( "10.01", "10.01$" ),
			array( "100", "100.00$" ),
			array( "1000", "1,000.00$" ),
			array( "10000", "10,000.00$" ),
			array( "1000000", "1,000,000.00$" ),
		);
	}

	/**
	 * @dataProvider displayFormatCurrencyAfterSpaceProvider
	 */
	public function test_display_format_currency_after_space( $value, $expected ) {
		$options = $this->defaultOptions;
		$options[ 'currency_format_placement' ] = 'after_space';

		$this->assertEquals( $expected, $this->field->display( $value, null, $options ) );
	}

	public function displayFormatCurrencyAfterSpaceProvider() {

		return array(
			array( "-1", "-1.00 $" ),
			array( "0", "0.00 $" ),
			array( "1", "1.00 $" ),
			array( "1.", "1.00 $" ),
			array( "1.5", "1.50 $" ),
			array( "10", "10.00 $" ),
			array( "10.01", "10.01 $" ),
			array( "100", "100.00 $" ),
			array( "1000", "1,000.00 $" ),
			array( "10000", "10,000.00 $" ),
			array( "1000000", "1,000,000.00 $" ),
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
			array( "-1.00", "-1.00" ),
			array( "0.00", "0.00" ),
			array( "1", "1.00" ),
			array( "1.00", "1.00" ),
			array( "1.0000", "1.00" ),
			array( "1.50", "1.50" ),
			array( "10.00", "10.00" ),
			array( "10.01", "10.01" ),
			array( "100.00", "100.00" ),
			array( "1,000.00", "1000.00" ),
			array( "10,000.00", "10000.00" ),
			array( "1,000,000.00", "1000000.00" ),
		);
	}

	/**
	 * @dataProvider saveFormatDecimalCommaProvider
	 */
	public function test_save_format_decimal_comma( $value, $expected ) {
		$options = $this->defaultOptions;
		$options[ 'currency_format' ] = '9.999,99';

		$this->assertEquals( $expected, $this->field->pre_save( $value, null, null, $options ) );
	}

	public function saveFormatDecimalCommaProvider() {

		return array(
			array( "-1,00", "-1.00" ),
			array( "0,00", "0.00" ),
			array( "1", "1.00" ),
			array( "1,", "1.00" ),
			array( "1,00", "1.00" ),
			array( "1,0000", "1.00" ),
			array( "1,50", "1.50" ),
			array( "10,00", "10.00" ),
			array( "10,01", "10.01" ),
			array( "100,00", "100.00" ),
			array( "1.000,00", "1000.00" ),
			array( "10.000,00", "10000.00" ),
			array( "1.000.000,00", "1000000.00" ),
		);
	}

	/**
	 * @dataProvider saveFormatThousandsQuoteProvider
	 */
	public function test_save_format_thousands_quote( $value, $expected ) {
		$options = $this->defaultOptions;
		$options[ 'currency_format' ] = '9\'999.99';

		$this->assertEquals( $expected, $this->field->pre_save( $value, null, null, $options ) );
	}

	public function saveFormatThousandsQuoteProvider() {

		return array(
			array( "1'000.00", "1000.00" ),
			array( "10'000.00", "10000.00" ),
			array( "1'000'000.00", "1000000.00" ),
		);
	}

	/**
	 * @dataProvider saveFormatSpaceCommaProvider
	 */
	public function test_save_format_space_comma_formats( $value, $expected ) {
		$options = $this->defaultOptions;
		$options[ 'currency_format' ] = '9 999,99';

		$this->assertEquals( $expected, $this->field->pre_save( $value, null, null, $options ) );
	}

	public function saveFormatSpaceCommaProvider() {

		return array(
			array( "1 000,00", "1000.00" ),
			array( "10 000,00", "10000.00" ),
			array( "1 000 000,00", "1000000.00" ),
		);
	}

	/**
	 * @dataProvider saveFormatDecimalDashProvider
	 */
	public function test_save_format_decimal_dash( $value, $expected ) {
		$options = $this->defaultOptions;
		$options[ 'currency_decimal_handling' ] = 'dash';

		$this->assertEquals( $expected, $this->field->pre_save( $value, null, null, $options ) );
	}

	public function saveFormatDecimalDashProvider() {

		return array(
			array( "-1.-", "-1.00" ),
			array( "0.-", "0.00" ),
			array( "1", "1.00" ),
			array( "1.-", "1.00" ),
			array( "1.--", "1.00" ),
			array( "1.00", "1.00" ),
			array( "1.0000", "1.00" ),
			array( "1.50", "1.50" ),
			array( "10.-", "10.00" ),
			array( "10.01", "10.01" ),
			array( "100.-", "100.00" ),
			array( "1,000.-", "1000.00" ),
			array( "10,000.-", "10000.00" ),
			array( "1,000,000.-", "1000000.00" ),
		);
	}

	/**
	 * @dataProvider saveFormatCurrencyDefaultProvider
	 */
	public function test_save_format_defaults_currency( $value, $expected ) {
		$options = $this->defaultOptions;

		$this->assertEquals( $expected, $this->field->pre_save( $value, null, null, $options ) );
	}

	public function saveFormatCurrencyDefaultProvider() {

		return array(
			array( "$-1.00", "-1.00" ),
			array( "$0.00", "0.00" ),
			array( "$1", "1.00" ),
			array( "$1.00", "1.00" ),
			array( "$1.0000", "1.00" ),
			array( "$1.50", "1.50" ),
			array( "$10.00", "10.00" ),
			array( "$10.01", "10.01" ),
			array( "$100.00", "100.00" ),
			array( "$1,000.00", "1000.00" ),
			array( "$10,000.00", "10000.00" ),
			array( "$1,000,000.00", "1000000.00" ),
		);
	}

	/**
	 * @dataProvider saveFormatCurrencyAfterProvider
	 */
	public function test_save_format_currency_after( $value, $expected ) {
		$options = $this->defaultOptions;
		$options[ 'currency_format_placement' ] = 'after';

		$this->assertEquals( $expected, $this->field->pre_save( $value, null, null, $options ) );
	}

	public function saveFormatCurrencyAfterProvider() {

		return array(
			array( "-1.00$", "-1.00" ),
			array( "0.00$", "0.00" ),
			array( "1$", "1.00" ),
			array( "1.00$", "1.00" ),
			array( "1.0000$", "1.00" ),
			array( "1.50$", "1.50" ),
			array( "10.00$", "10.00" ),
			array( "10.01$", "10.01" ),
			array( "100.00$", "100.00" ),
			array( "1,000.00$", "1000.00" ),
			array( "10,000.00$", "10000.00" ),
			array( "1,000,000.00$", "1000000.00" ),
		);
	}

	/**
	 * @dataProvider saveFormatCurrencyAfterSpaceProvider
	 */
	public function test_save_format_currency_after_space( $value, $expected ) {
		$options = $this->defaultOptions;
		$options[ 'currency_format_placement' ] = 'after_space';

		$this->assertEquals( $expected, $this->field->pre_save( $value, null, null, $options ) );
	}

	public function saveFormatCurrencyAfterSpaceProvider() {

		return array(
			array( "-1.00 $", "-1.00" ),
			array( "0.00 $", "0.00" ),
			array( "1 $", "1.00" ),
			array( "1.00 $", "1.00" ),
			array( "1.0000 $", "1.00" ),
			array( "1.50 $", "1.50" ),
			array( "10.00 $", "10.00" ),
			array( "10.01 $", "10.01" ),
			array( "100.00 $", "100.00" ),
			array( "1,000.00 $", "1000.00" ),
			array( "10,000.00 $", "10000.00" ),
			array( "1,000,000.00 $", "1000000.00" ),
		);
	}

}
