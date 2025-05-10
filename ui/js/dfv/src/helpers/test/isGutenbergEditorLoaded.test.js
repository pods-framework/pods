/**
 * Internal dependencies
 */
import isGutenbergEditorLoaded from '../isGutenbergEditorLoaded';

// Mock the WordPress data module
jest.mock(
	'@wordpress/data',
	() => (
		{
			select: jest.fn(),
		}
	),
);

describe( 'isGutenbergEditorLoaded', () => {
	// Get the mocked select function
	const select = require( '@wordpress/data' ).select;

	beforeEach( () => {
		// Clear all mock implementations before each test
		select.mockClear();
	} );

	test( 'returns true when the block editor is active', () => {
		// Mock the select function to return an object when called with 'core/editor'
		select.mockImplementation( ( storeName ) => {
			if ( storeName === 'core/editor' ) {
				return {};
			}
			return undefined;
		} );

		// Check the result
		expect( isGutenbergEditorLoaded() ).toEqual( true );
	} );

	test( 'returns false when the block editor is not active', () => {
		// Mock the select function to return undefined when called with 'core/editor'
		select.mockImplementation( () => undefined );

		// Check the result
		expect( isGutenbergEditorLoaded() ).toEqual( false );
	} );

	test( 'correctly calls the select function with core/editor', () => {
		// Set up a mock implementation
		select.mockImplementation( () => (
			{}
		) );

		// Call the function
		isGutenbergEditorLoaded();

		// Verify that select was called with the correct store name
		expect( select ).toHaveBeenCalledWith( 'core/editor' );
	} );
} );
