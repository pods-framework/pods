<?php

namespace Pods_Unit_Tests\Pods\Field;

use Pods_Unit_Tests\Pods_UnitTestCase;
use PodsField_Currency;

/**
 * @group              pods-field
 * @coversDefaultClass PodsField_Currency
 */
class PodsField_CurrencyTest extends Pods_UnitTestCase {

	/**
	 * @var PodsField_Currency
	 */
	private $field;

	public $defaultOptions = [
		'currency_format_type'      => 'number',
		'currency_format_sign'      => 'usd',
		'currency_format_placement' => 'before',
		'currency_format'           => '9,999.99',
		'currency_decimals'         => 2,
		'currency_decimal_handling' => 'default',
	];

	public function setUp() {
		$this->field = new PodsField_Currency();

		parent::setUp();
	}

	public function tearDown() {
		unset( $this->field );

		parent::tearDown();
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
			[ '-1', '-1.00' ],
			[ '0', '0.00' ],
			[ '1', '1.00' ],
			[ '1.', '1.00' ],
			[ '1.5', '1.50' ],
			[ '10', '10.00' ],
			[ '10.01', '10.01' ],
			[ '100', '100.00' ],
			[ '1000', '1,000.00' ],
			[ '10000', '10,000.00' ],
			[ '1000000', '1,000,000.00' ],
		];
	}

	/**
	 * @dataProvider formatDecimalCommaProvider
	 */
	public function test_format_decimal_comma( $value, $expected ) {
		$options                    = $this->defaultOptions;
		$options['currency_format'] = '9.999,99';

		$this->assertEquals( $expected, $this->field->format( $value, null, $options ) );
	}

	public function formatDecimalCommaProvider() {
		return [
			[ '-1', '-1,00' ],
			[ '0', '0,00' ],
			[ '1', '1,00' ],
			[ '1.', '1,00' ],
			[ '1.5', '1,50' ],
			[ '10', '10,00' ],
			[ '10.01', '10,01' ],
			[ '100', '100,00' ],
			[ '1000', '1.000,00' ],
			[ '10000', '10.000,00' ],
			[ '1000000', '1.000.000,00' ],
		];
	}

	/**
	 * @dataProvider formatThousandsQuoteProvider
	 */
	public function test_format_thousands_quote( $value, $expected ) {
		$options                    = $this->defaultOptions;
		$options['currency_format'] = '9\'999.99';

		$this->assertEquals( $expected, $this->field->format( $value, null, $options ) );
	}

	public function formatThousandsQuoteProvider() {
		return [
			[ '1000', '1\'000.00' ],
			[ '10000', '10\'000.00' ],
			[ '1000000', '1\'000\'000.00' ],
		];
	}

	/**
	 * @dataProvider formatSpaceCommaProvider
	 */
	public function test_format_space_comma( $value, $expected ) {
		$options                    = $this->defaultOptions;
		$options['currency_format'] = '9 999,99';

		$this->assertEquals( $expected, $this->field->format( $value, null, $options ) );
	}

	public function formatSpaceCommaProvider() {
		return [
			[ '1000', '1 000,00' ],
			[ '10000', '10 000,00' ],
			[ '1000000', '1 000 000,00' ],
		];
	}

	/**
	 * @dataProvider formatDecimalDashProvider
	 * @covers ::trim_decimals
	 */
	public function test_format_decimal_dash( $value, $expected ) {
		$options                              = $this->defaultOptions;
		$options['currency_decimal_handling'] = 'dash';

		$this->assertEquals( $expected, $this->field->format( $value, null, $options ) );
	}

	public function formatDecimalDashProvider() {
		return [
			[ '-1', '-1.-' ],
			[ '0', '0.-' ],
			[ '1', '1.-' ],
			[ '1.', '1.-' ],
			[ '1.5', '1.50' ],
			[ '10', '10.-' ],
			[ '10.01', '10.01' ],
			[ '100', '100.-' ],
			[ '1000', '1,000.-' ],
			[ '10000', '10,000.-' ],
			[ '1000000', '1,000,000.-' ],
		];
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
			[ '-1', '$-1.00' ],
			[ '0', '$0.00' ],
			[ '1', '$1.00' ],
			[ '1.', '$1.00' ],
			[ '1.5', '$1.50' ],
			[ '10', '$10.00' ],
			[ '10.01', '$10.01' ],
			[ '100', '$100.00' ],
			[ '1000', '$1,000.00' ],
			[ '10000', '$10,000.00' ],
			[ '1000000', '$1,000,000.00' ],
		];
	}

	/**
	 * @dataProvider displayFormatCurrencyAfterProvider
	 */
	public function test_display_format_currency_after( $value, $expected ) {
		$options                              = $this->defaultOptions;
		$options['currency_format_placement'] = 'after';

		$this->assertEquals( $expected, $this->field->display( $value, null, $options ) );
	}

	public function displayFormatCurrencyAfterProvider() {
		return [
			[ '-1', '-1.00$' ],
			[ '0', '0.00$' ],
			[ '1', '1.00$' ],
			[ '1.', '1.00$' ],
			[ '1.5', '1.50$' ],
			[ '10', '10.00$' ],
			[ '10.01', '10.01$' ],
			[ '100', '100.00$' ],
			[ '1000', '1,000.00$' ],
			[ '10000', '10,000.00$' ],
			[ '1000000', '1,000,000.00$' ],
		];
	}

	/**
	 * @dataProvider displayFormatCurrencyAfterSpaceProvider
	 */
	public function test_display_format_currency_after_space( $value, $expected ) {
		$options                              = $this->defaultOptions;
		$options['currency_format_placement'] = 'after_space';

		$this->assertEquals( $expected, $this->field->display( $value, null, $options ) );
	}

	public function displayFormatCurrencyAfterSpaceProvider() {
		return [
			[ '-1', '-1.00 $' ],
			[ '0', '0.00 $' ],
			[ '1', '1.00 $' ],
			[ '1.', '1.00 $' ],
			[ '1.5', '1.50 $' ],
			[ '10', '10.00 $' ],
			[ '10.01', '10.01 $' ],
			[ '100', '100.00 $' ],
			[ '1000', '1,000.00 $' ],
			[ '10000', '10,000.00 $' ],
			[ '1000000', '1,000,000.00 $' ],
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
			[ '-1.00', '-1.00' ],
			[ '0.00', '0.00' ],
			[ '1', '1.00' ],
			[ '1.00', '1.00' ],
			[ '1.0000', '1.00' ],
			[ '1.50', '1.50' ],
			[ '10.00', '10.00' ],
			[ '10.01', '10.01' ],
			[ '100.00', '100.00' ],
			[ '1,000.00', '1000.00' ],
			[ '10,000.00', '10000.00' ],
			[ '1,000,000.00', '1000000.00' ],
		];
	}

	/**
	 * @dataProvider saveFormatDecimalCommaProvider
	 */
	public function test_save_format_decimal_comma( $value, $expected ) {
		$options                    = $this->defaultOptions;
		$options['currency_format'] = '9.999,99';

		$this->assertEquals( $expected, $this->field->pre_save( $value, null, null, $options ) );
	}

	public function saveFormatDecimalCommaProvider() {
		return [
			[ '-1,00', '-1.00' ],
			[ '0,00', '0.00' ],
			[ '1', '1.00' ],
			[ '1,', '1.00' ],
			[ '1,00', '1.00' ],
			[ '1,0000', '1.00' ],
			[ '1,50', '1.50' ],
			[ '10,00', '10.00' ],
			[ '10,01', '10.01' ],
			[ '100,00', '100.00' ],
			[ '1.000,00', '1000.00' ],
			[ '10.000,00', '10000.00' ],
			[ '1.000.000,00', '1000000.00' ],
		];
	}

	/**
	 * @dataProvider saveFormatThousandsQuoteProvider
	 */
	public function test_save_format_thousands_quote( $value, $expected ) {
		$options                    = $this->defaultOptions;
		$options['currency_format'] = '9\'999.99';

		$this->assertEquals( $expected, $this->field->pre_save( $value, null, null, $options ) );
	}

	public function saveFormatThousandsQuoteProvider() {
		return [
			[ '1\'000.00', '1000.00' ],
			[ '10\'000.00', '10000.00' ],
			[ '1\'000\'000.00', '1000000.00' ],
		];
	}

	/**
	 * @dataProvider saveFormatSpaceCommaProvider
	 */
	public function test_save_format_space_comma_formats( $value, $expected ) {
		$options                    = $this->defaultOptions;
		$options['currency_format'] = '9 999,99';

		$this->assertEquals( $expected, $this->field->pre_save( $value, null, null, $options ) );
	}

	public function saveFormatSpaceCommaProvider() {
		return [
			[ '1 000,00', '1000.00' ],
			[ '10 000,00', '10000.00' ],
			[ '1 000 000,00', '1000000.00' ],
		];
	}

	/**
	 * @dataProvider saveFormatDecimalDashProvider
	 */
	public function test_save_format_decimal_dash( $value, $expected ) {
		$options                              = $this->defaultOptions;
		$options['currency_decimal_handling'] = 'dash';

		$this->assertEquals( $expected, $this->field->pre_save( $value, null, null, $options ) );
	}

	public function saveFormatDecimalDashProvider() {
		return [
			[ '-1.-', '-1.00' ],
			[ '0.-', '0.00' ],
			[ '1', '1.00' ],
			[ '1.-', '1.00' ],
			[ '1.--', '1.00' ],
			[ '1.00', '1.00' ],
			[ '1.0000', '1.00' ],
			[ '1.50', '1.50' ],
			[ '10.-', '10.00' ],
			[ '10.01', '10.01' ],
			[ '100.-', '100.00' ],
			[ '1,000.-', '1000.00' ],
			[ '10,000.-', '10000.00' ],
			[ '1,000,000.-', '1000000.00' ],
		];
	}

	/**
	 * @dataProvider saveFormatCurrencyDefaultProvider
	 */
	public function test_save_format_defaults_currency( $value, $expected ) {
		$options = $this->defaultOptions;

		$this->assertEquals( $expected, $this->field->pre_save( $value, null, null, $options ) );
	}

	public function saveFormatCurrencyDefaultProvider() {
		return [
			[ '$-1.00', '-1.00' ],
			[ '$0.00', '0.00' ],
			[ '$1', '1.00' ],
			[ '$1.00', '1.00' ],
			[ '$1.0000', '1.00' ],
			[ '$1.50', '1.50' ],
			[ '$10.00', '10.00' ],
			[ '$10.01', '10.01' ],
			[ '$100.00', '100.00' ],
			[ '$1,000.00', '1000.00' ],
			[ '$10,000.00', '10000.00' ],
			[ '$1,000,000.00', '1000000.00' ],
		];
	}

	/**
	 * @dataProvider saveFormatCurrencyAfterProvider
	 */
	public function test_save_format_currency_after( $value, $expected ) {
		$options                              = $this->defaultOptions;
		$options['currency_format_placement'] = 'after';

		$this->assertEquals( $expected, $this->field->pre_save( $value, null, null, $options ) );
	}

	public function saveFormatCurrencyAfterProvider() {
		return [
			[ '-1.00$', '-1.00' ],
			[ '0.00$', '0.00' ],
			[ '1$', '1.00' ],
			[ '1.00$', '1.00' ],
			[ '1.0000$', '1.00' ],
			[ '1.50$', '1.50' ],
			[ '10.00$', '10.00' ],
			[ '10.01$', '10.01' ],
			[ '100.00$', '100.00' ],
			[ '1,000.00$', '1000.00' ],
			[ '10,000.00$', '10000.00' ],
			[ '1,000,000.00$', '1000000.00' ],
		];
	}

	/**
	 * @dataProvider saveFormatCurrencyAfterSpaceProvider
	 */
	public function test_save_format_currency_after_space( $value, $expected ) {
		$options                              = $this->defaultOptions;
		$options['currency_format_placement'] = 'after_space';

		$this->assertEquals( $expected, $this->field->pre_save( $value, null, null, $options ) );
	}

	public function saveFormatCurrencyAfterSpaceProvider() {
		return [
			[ '-1.00 $', '-1.00' ],
			[ '0.00 $', '0.00' ],
			[ '1 $', '1.00' ],
			[ '1.00 $', '1.00' ],
			[ '1.0000 $', '1.00' ],
			[ '1.50 $', '1.50' ],
			[ '10.00 $', '10.00' ],
			[ '10.01 $', '10.01' ],
			[ '100.00 $', '100.00' ],
			[ '1,000.00 $', '1000.00' ],
			[ '10,000.00 $', '10000.00' ],
			[ '1,000,000.00 $', '1000000.00' ],
		];
	}

}
