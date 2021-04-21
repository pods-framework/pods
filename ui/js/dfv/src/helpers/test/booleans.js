import {
	toBool,
	toNumericBool,
} from '../booleans';

describe( 'toBool', () => {
	// Note: these are the most important to test, because
	// the Pods API usually returns them instead of actual booleans,
	// and JavaScript would normally consider '0' to be truthy.
	it( 'converts numeric 1 and 0 strings', () => {
		const falseValue = toBool( '0' );
		const trueValue = toBool( '1' );

		expect( falseValue ).toBe( false );
		expect( trueValue ).toBe( true );
	} );

	it( 'doesn\'t change actual boolean values', () => {
		const falseValue = toBool( false );
		const trueValue = toBool( true );

		expect( falseValue ).toBe( false );
		expect( trueValue ).toBe( true );
	} );
} );

describe( 'toNumericBool', () => {
	it( 'does not change numeric strings', () => {
		const falseValue = toNumericBool( '0' );
		const trueValue = toNumericBool( '1' );

		expect( falseValue ).toBe( '0' );
		expect( trueValue ).toBe( '1' );
	} );

	it( 'converts simple booleans', () => {
		const falseValue = toNumericBool( false );
		const trueValue = toNumericBool( true );

		expect( falseValue ).toBe( '0' );
		expect( trueValue ).toBe( '1' );
	} );

	it( 'handles empty strings and null/undefined values', () => {
		const falseEmptyStringValue = toNumericBool( '' );
		const falseNullValue = toNumericBool( null );
		const falseUndefinedValue = toNumericBool( undefined );

		expect( falseEmptyStringValue ).toBe( '0' );
		expect( falseNullValue ).toBe( '0' );
		expect( falseUndefinedValue ).toBe( '0' );
	} );
} );
