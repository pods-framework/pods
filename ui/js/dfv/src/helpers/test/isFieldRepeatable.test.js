/**
 * Internal dependencies
 */
import isFieldRepeatable from '../isFieldRepeatable';

// Mock the booleans helper
jest.mock(
	'dfv/src/helpers/booleans',
	() => (
		{
			toBool: jest.fn( ( value ) => !! value ),
		}
	),
);

describe( 'isFieldRepeatable', () => {
	const { toBool } = require( 'dfv/src/helpers/booleans' );

	beforeEach( () => {
		// Clear all mock implementations before each test
		toBool.mockClear();
	} );

	test( 'throws an error when field config is missing type property', () => {
		// Create an invalid field config without a type property
		const invalidFieldConfig = {
			name: 'test_field',
		};

		// Expect the function to throw an error
		expect( () => isFieldRepeatable( invalidFieldConfig ) ).toThrow( 'Invalid field config.' );
	} );

	test( 'returns false for unsupported field types', () => {
		// Create field configs for unsupported types
		const unsupportedTypes = [
			'file',
			'image',
			'avatar',
			'relationship',
			'comment',
			'boolean',
			'custom',
			'invalid_type',
		];

		unsupportedTypes.forEach( type => {
			const fieldConfig = {
				type,
				repeatable: '1', // Would be repeatable if type was supported
			};

			// Check that the function returns false for unsupported types
			expect( isFieldRepeatable( fieldConfig ) ).toEqual( false );

			// toBool should not be called for unsupported types
			expect( toBool ).not.toHaveBeenCalled();
		} );
	} );

	test( 'returns false when repeatable property is falsy', () => {
		// Create field configs with falsy repeatable values
		const supportedType = 'text';
		const falsyValues = [ false, 0, '0', null, undefined, '' ];

		falsyValues.forEach( falsyValue => {
			// Reset the toBool mock before each iteration
			toBool.mockClear();

			const fieldConfig = {
				type: supportedType,
				repeatable: falsyValue,
			};

			// Mock toBool to return false for this test
			toBool.mockReturnValueOnce( false );

			// Check that the function returns false
			expect( isFieldRepeatable( fieldConfig ) ).toEqual( false );

			// Verify toBool was called with the correct argument
			expect( toBool ).toHaveBeenLastCalledWith( falsyValue || false );
		} );
	} );

	test( 'returns true for supported field types with repeatable property set to true', () => {
		// Create field configs for supported types with repeatable set to true
		const supportedTypes = [
			'text',
			'website',
			'phone',
			'email',
			'password',
			'paragraph',
			'wysiwyg',
			'datetime',
			'date',
			'time',
			'number',
			'currency',
			'oembed',
			'color',
		];

		supportedTypes.forEach( type => {
			// Reset the toBool mock before each iteration
			toBool.mockClear();

			const fieldConfig = {
				type,
				repeatable: '1', // Truthy value that should be converted to true
			};

			// Mock toBool to return true for this test
			toBool.mockReturnValueOnce( true );

			// Check that the function returns true
			expect( isFieldRepeatable( fieldConfig ) ).toEqual( true );

			// Verify toBool was called with the correct argument
			expect( toBool ).toHaveBeenCalledWith( '1' );
		} );
	} );

	test( 'handles missing repeatable property by using default false', () => {
		// Create a field config without a repeatable property
		const fieldConfig = {
			type: 'text',
		};

		// Mock toBool to work with the expected false value
		toBool.mockReturnValueOnce( false );

		// Check that the function returns false
		expect( isFieldRepeatable( fieldConfig ) ).toEqual( false );

		// Verify toBool was called with false as the default value
		expect( toBool ).toHaveBeenCalledWith( false );
	} );

	test( 'passes various repeatable values to toBool correctly', () => {
		// Test with different values for the repeatable property
		const values = [
			'1',
			'0',
			1,
			0,
			true,
			false,
			'yes',
			'no',
			'true',
			'false',
		];

		values.forEach( value => {
			// Reset the toBool mock before each iteration
			toBool.mockClear();

			const fieldConfig = {
				type: 'text',
				repeatable: value,
			};

			// Let toBool handle the return value
			expect( isFieldRepeatable( fieldConfig ) ).toEqual( toBool( value || false ) );

			// Verify toBool was called with the correct argument
			expect( toBool ).toHaveBeenCalledWith( value || false );
		} );
	} );
} );
