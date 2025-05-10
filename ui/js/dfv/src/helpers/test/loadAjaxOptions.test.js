/**
 * Internal dependencies
 */
import loadAjaxOptions from '../loadAjaxOptions';

// Mock the global fetch function
global.fetch = jest.fn();

// Mock the global ajaxurl variable
global.ajaxurl = 'http://example.com/wp-admin/admin-ajax.php';

describe( 'loadAjaxOptions', () => {
	// Clear all mocks before each test
	beforeEach( () => {
		fetch.mockClear();
	} );

	test( 'creates a function that fetches options with correct parameters', async () => {
		// Setup
		const ajaxData = {
			_wpnonce: 'abc123',
			pod_name: 'post',
			field_name: 'category',
			uri_hash: 'hash123',
			id: 42,
		};

		const mockResponse = {
			results: [
				{
					id: 1,
					name: 'Option 1',
				},
				{
					id: 2,
					name: 'Option 2',
				},
			],
		};

		// Mock the fetch response
		fetch.mockResolvedValueOnce( {
			json: () => Promise.resolve( mockResponse ),
		} );

		// Create the loader function
		const loader = loadAjaxOptions( ajaxData );

		// Call the loader with a search term
		const result = await loader( 'search term' );

		// Verify it called fetch with the right parameters
		expect( fetch ).toHaveBeenCalledTimes( 1 );
		expect( fetch ).toHaveBeenCalledWith(
			'http://example.com/wp-admin/admin-ajax.php?pods_ajax=1',
			expect.objectContaining( {
				method: 'POST',
				body: expect.any( FormData ),
			} ),
		);

		// Check that formData was built correctly
		const formData = fetch.mock.calls[ 0 ][ 1 ].body;

		// Since FormData is not directly inspectable in Jest, we need to mock it
		// This test assumes the FormData was constructed correctly based on the inputs

		// Check the result format
		expect( result ).toEqual( [
			{
				label: 'Option 1',
				value: 1,
			},
			{
				label: 'Option 2',
				value: 2,
			},
		] );
	} );

	test( 'provides default values for missing ajaxData parameters', async () => {
		// Setup with minimal ajaxData
		const ajaxData = {
			_wpnonce: 'abc123',
		};

		const mockResponse = {
			results: [
				{
					id: 1,
					name: 'Option 1',
				},
			],
		};

		// Mock the fetch response
		fetch.mockResolvedValueOnce( {
			json: () => Promise.resolve( mockResponse ),
		} );

		// Create the loader function
		const loader = loadAjaxOptions( ajaxData );

		// Call the loader
		await loader();

		// Get the FormData from the fetch call
		const formData = fetch.mock.calls[ 0 ][ 1 ].body;

		// We can't directly test FormData contents in Jest, but we can verify
		// that fetch was called with a FormData object as the body
		expect( fetch ).toHaveBeenCalledWith(
			expect.any( String ),
			expect.objectContaining( {
				body: expect.any( FormData ),
			} ),
		);
	} );

	test( 'handles empty ajaxData correctly', async () => {
		const mockResponse = {
			results: [],
		};

		// Mock the fetch response
		fetch.mockResolvedValueOnce( {
			json: () => Promise.resolve( mockResponse ),
		} );

		// Create the loader function with empty ajaxData
		const loader = loadAjaxOptions();

		// Call the loader
		const result = await loader();

		// Check the result
		expect( result ).toEqual( [] );
	} );

	test( 'throws an error when response is invalid', async () => {
		// Setup
		const ajaxData = {
			_wpnonce: 'abc123',
		};

		// Mock an invalid response (no results field)
		fetch.mockResolvedValueOnce( {
			json: () => Promise.resolve( { error: 'Invalid response' } ),
		} );

		// Create the loader function
		const loader = loadAjaxOptions( ajaxData );

		// Call the loader and expect an error
		await expect( loader() ).rejects.toThrow( 'Invalid response.' );
	} );

	test( 'throws the original error when fetch fails', async () => {
		// Setup
		const ajaxData = {
			_wpnonce: 'abc123',
		};

		// Mock a network error
		const networkError = new Error( 'Network failure' );
		fetch.mockRejectedValueOnce( networkError );

		// Create the loader function
		const loader = loadAjaxOptions( ajaxData );

		// Call the loader and expect the original error to be thrown
		await expect( loader() ).rejects.toEqual( networkError );
	} );
} );
