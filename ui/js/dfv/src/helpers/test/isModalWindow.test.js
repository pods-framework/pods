/**
 * Internal dependencies
 */
import isModalWindow from '../isModalWindow';

describe( 'isModalWindow', () => {
	// Save the original location.search
	const originalLocationSearch = window.location.search;

	// Use Object.defineProperty to mock location.search since it's read-only
	const mockLocationSearch = ( value ) => {
		Object.defineProperty( window, 'location', {
			value: {
				...window.location,
				search: value,
			},
			writable: true,
		} );
	};

	afterAll( () => {
		// Restore the original location.search after all tests
		Object.defineProperty( window, 'location', {
			value: {
				...window.location,
				search: originalLocationSearch,
			},
			writable: true,
		} );
	} );

	test( 'returns true when pods_modal parameter is in the URL', () => {
		// Mock the location.search with pods_modal parameter
		mockLocationSearch( '?pods_modal=1' );

		// Check the result
		expect( isModalWindow() ).toEqual( true );
	} );

	test( 'returns false when pods_modal parameter is not in the URL', () => {
		// Mock the location.search without pods_modal parameter
		mockLocationSearch( '?other_param=value' );

		// Check the result
		expect( isModalWindow() ).toEqual( false );
	} );

	test( 'returns true regardless of pods_modal parameter value', () => {
		// Mock the location.search with different pods_modal parameter values
		mockLocationSearch( '?pods_modal=1' );
		expect( isModalWindow() ).toEqual( true );

		mockLocationSearch( '?pods_modal=0' );
		expect( isModalWindow() ).toEqual( true );

		mockLocationSearch( '?pods_modal=anything' );
		expect( isModalWindow() ).toEqual( true );

		mockLocationSearch( '?other_param=value&pods_modal=1' );
		expect( isModalWindow() ).toEqual( true );

		mockLocationSearch( '?pods_modal=1&other_param=value' );
		expect( isModalWindow() ).toEqual( true );
	} );

	test( 'returns false with empty search string', () => {
		// Mock empty location.search
		mockLocationSearch( '' );

		// Check the result
		expect( isModalWindow() ).toEqual( false );
	} );

	test( 'returns false when pods_modal is a substring but not a parameter', () => {
		// Mock location.search with a parameter that contains pods_modal as a substring
		mockLocationSearch( '?something_pods_modal_something=1' );

		// Check the result
		expect( isModalWindow() ).toEqual( false );
	} );
} );
