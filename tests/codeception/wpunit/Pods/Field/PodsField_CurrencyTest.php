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

	public $defaultOptions = array(
		'currency_format_type'      => 'number',
		'currency_format_sign'      => 'usd',
		'currency_format_placement' => 'before',
		'currency_format'           => '9,999.99',
		'currency_decimals'         => 2,
		'currency_decimal_handling' => 'default'
	);

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
		return array(
			array( '-1', '-1.00' ),
			array( '0', '0.00' ),
			array( '1', '1.00' ),
			array( '1.', '1.00' ),
			array( '1.5', '1.50' ),
			array( '10', '10.00' ),
			array( '10.01', '10.01' ),
			array( '100', '100.00' ),
			array( '1000', '1,000.00' ),
			array( '10000', '10,000.00' ),
			array( '1000000', '1,000,000.00' ),
		);
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
		return array(
			array( '-1', '-1,00' ),
			array( '0', '0,00' ),
			array( '1', '1,00' ),
			array( '1.', '1,00' ),
			array( '1.5', '1,50' ),
			array( '10', '10,00' ),
			array( '10.01', '10,01' ),
			array( '100', '100,00' ),
			array( '1000', '1.000,00' ),
			array( '10000', '10.000,00' ),
			array( '1000000', '1.000.000,00' ),
		);
	}

	/**
	 * @dataProvider formatDecimalDashProvider
	 */
	public function test_format_decimal_dash( $value, $expected ) {
		$options                              = $this->defaultOptions;
		$options['currency_decimal_handling'] = 'dash';

		$this->assertEquals( $expected, $this->field->format( $value, null, $options ) );
	}

	public function formatDecimalDashProvider() {
		return array(
			array( '-1', '-1.-' ),
			array( '0', '0.-' ),
			array( '1', '1.-' ),
			array( '1.', '1.-' ),
			array( '1.5', '1.50' ),
			array( '10', '10.-' ),
			array( '10.01', '10.01' ),
			array( '100', '100.-' ),
			array( '1000', '1,000.-' ),
			array( '10000', '10,000.-' ),
			array( '1000000', '1,000,000.-' ),
		);
	}

	/**
	 * @dataProvider displayDefaultsProvider
	 */
	public function test_display_defaults( $value, $expected ) {
		$options = $this->defaultOptions;

		$this->assertEquals( $expected, $this->field->display( $value, null, $options ) );
	}

	public function displayDefaultsProvider() {
		return array(
			array( '-1', '$-1.00' ),
			array( '0', '$0.00' ),
			array( '1', '$1.00' ),
			array( '1.', '$1.00' ),
			array( '1.5', '$1.50' ),
			array( '10', '$10.00' ),
			array( '10.01', '$10.01' ),
			array( '100', '$100.00' ),
			array( '1000', '$1,000.00' ),
			array( '10000', '$10,000.00' ),
			array( '1000000', '$1,000,000.00' ),
		);
	}

	/**
	 * @dataProvider displayFormatAfterProvider
	 */
	public function test_display_format_after( $value, $expected ) {
		$options                              = $this->defaultOptions;
		$options['currency_format_placement'] = 'after';

		$this->assertEquals( $expected, $this->field->display( $value, null, $options ) );
	}

	public function displayFormatAfterProvider() {
		return array(
			array( '-1', '-1.00$' ),
			array( '0', '0.00$' ),
			array( '1', '1.00$' ),
			array( '1.', '1.00$' ),
			array( '1.5', '1.50$' ),
			array( '10', '10.00$' ),
			array( '10.01', '10.01$' ),
			array( '100', '100.00$' ),
			array( '1000', '1,000.00$' ),
			array( '10000', '10,000.00$' ),
			array( '1000000', '1,000,000.00$' ),
		);
	}

	/**
	 * @dataProvider displayFormatAfterSpaceProvider
	 */
	public function test_display_format_after_space( $value, $expected ) {
		$options                              = $this->defaultOptions;
		$options['currency_format_placement'] = 'after_space';

		$this->assertEquals( $expected, $this->field->display( $value, null, $options ) );
	}

	public function displayFormatAfterSpaceProvider() {
		return array(
			array( '-1', '-1.00 $' ),
			array( '0', '0.00 $' ),
			array( '1', '1.00 $' ),
			array( '1.', '1.00 $' ),
			array( '1.5', '1.50 $' ),
			array( '10', '10.00 $' ),
			array( '10.01', '10.01 $' ),
			array( '100', '100.00 $' ),
			array( '1000', '1,000.00 $' ),
			array( '10000', '10,000.00 $' ),
			array( '1000000', '1,000,000.00 $' ),
		);
	}

}
