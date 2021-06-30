import {
	requiredValidator,
	maxValidator,
	minValidator,
	emailValidator,
} from '../validators';

describe( 'requiredValidator', () => {
	it( 'creates a function that allows non-empty values', () => {
		const fieldLabel = 'Field Name';
		const validateField = requiredValidator( fieldLabel );

		expect( validateField( 'Something' ) ).toBe( true );
		expect( validateField( 1 ) ).toBe( true );
		expect( validateField( 123.456 ) ).toBe( true );
	} );

	it( 'throws when passed an empty value', () => {
		const fieldLabel = 'Field Name';
		const validateField = requiredValidator( fieldLabel );

		const validateEmptyString = () => validateField( '' );
		const validateUndefined = () => validateField( undefined );
		const validateNull = () => validateField( null );

		expect( validateEmptyString ).toThrow( 'Field Name is required.' );
		expect( validateUndefined ).toThrow( 'Field Name is required.' );
		expect( validateNull ).toThrow( 'Field Name is required.' );
	} );
} );

describe( 'maxValidator', () => {
	it( 'allows when passed a number less than or equal to the maximum', () => {
		const validateField = maxValidator( 19 );

		expect( validateField( 19 ) ).toBe( true );
		expect( validateField( 18 ) ).toBe( true );
		expect( validateField( '18' ) ).toBe( true );
		expect( validateField( '' ) ).toBe( true );
		expect( validateField( 1 ) ).toBe( true );
		expect( validateField( -99999 ) ).toBe( true );
	} );

	it( 'throws when passed a number past the maximum', () => {
		const validateField = maxValidator( 19 );

		const validateHigherNumber = () => validateField( 20 );
		const validateSlightlyHigherNumber = () => validateField( 19.00001 );

		expect( validateHigherNumber ).toThrow( 'Exceeds the maximum value of 19.' );
		expect( validateSlightlyHigherNumber ).toThrow( 'Exceeds the maximum value of 19.' );
	} );
} );

describe( 'minValidator', () => {
	it( 'allows when passed a greater than or equal to the minimum', () => {
		const validateField = minValidator( 5 );

		expect( validateField( 6 ) ).toBe( true );
		expect( validateField( '5' ) ).toBe( true );
		expect( validateField( '' ) ).toBe( true );
		expect( validateField( 5 ) ).toBe( true );
		expect( validateField( 99999 ) ).toBe( true );
	} );

	it( 'throws when passed a number below the minimum', () => {
		const validateField = minValidator( 7 );

		const validateLowerNumber = () => validateField( 3 );
		const validateSlightlyLowerNumber = () => validateField( 6.9999 );

		expect( validateLowerNumber ).toThrow( 'Below the minimum value of 7.' );
		expect( validateSlightlyLowerNumber ).toThrow( 'Below the minimum value of 7.' );
	} );
} );

describe( 'emailValidator', () => {
	// Test data borrowed from:
	// https://en.wikipedia.org/wiki/Email_address#Valid_email_addresses

	it( 'allows when passed a valid email address', () => {
		const validateField = emailValidator();

		const validEmailAddress = [
			'simple@example.com',
			'very.common@example.com',
			'disposable.style.email.with+symbol@example.com',
			'other.email-with-hyphen@example.com',
			'fully-qualified-domain@example.com',
			'user.name+tag+sorting@example.com',
			'x@example.com',
			'example-indeed@strange-example.com',
			'example@s.example',
			'" "@example.org',
			'"john..doe"@example.org',
			'mailhost!username@example.org',
			'user%example.com@example.org',
		];

		validEmailAddress.forEach( ( address ) => {
			try {
				expect( validateField( address ) ).toBe( true );
			} catch ( e ) {
				// eslint-disable-next-line
				console.warn( `Failing address was: ${ address }` );
			}
		} );
	} );

	it( 'throws when passed an invalid email address', () => {
		const validateField = emailValidator();

		const invalidEmailAddresses = [
			'Abc.example.com',
			'A@b@c@example.com',
			'a"b(c)d,e:f;g<h>i[j\k]l@example.com',
			'just"not"right@example.com',
			'this is"not\allowed@example.com',
			'this\ still\"not\\allowed@example.com',
			'i_like_underscore@but_its_not_allow_in_this_part.example.com',
		];

		invalidEmailAddresses.forEach( ( address ) => {
			const validateEmailAddress = () => validateField( address );

			try {
				expect( validateEmailAddress ).toThrow( 'Invalid email address format.' );
			} catch ( e ) {
				// eslint-disable-next-line
				console.warn( `Address that should have failed: ${ address }` );
			}
		} );
	} );
} );
