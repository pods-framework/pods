/**
 * Internal dependencies
 */
import loadAjaxOptions from '../loadAjaxOptions';

global.fetch = jest.fn();
global.ajaxurl = 'http://example.com/wp-admin/admin-ajax.php';

describe( 'loadAjaxOptions', () => {
	beforeEach( () => {
		fetch.mockClear();
	} );

	test( 'fetches and formats options with requested page', async () => {
		fetch.mockResolvedValueOnce( {
			json: () => Promise.resolve( {
				has_more: true,
				results: [ { id: 1, name: 'Option 1' } ],
			} ),
		} );

		const result = await loadAjaxOptions( {
			_wpnonce: 'abc123',
			pod_name: 'post',
			field_name: 'category',
			uri_hash: 'hash123',
			id: 42,
		} )( 'search term', 3 );

		expect( fetch ).toHaveBeenCalledWith(
			'http://example.com/wp-admin/admin-ajax.php?pods_ajax=1',
			expect.objectContaining( { method: 'POST', body: expect.any( FormData ) } ),
		);
		expect( fetch.mock.calls[ 0 ][ 1 ].body.get( 'page' ) ).toBe( '3' );
		expect( [ ...result ] ).toEqual( [ { label: 'Option 1', value: 1 } ] );
		expect( result.hasMore ).toBe( true );
	} );

	test( 'uses defaults for missing ajax data', async () => {
		fetch.mockResolvedValueOnce( {
			json: () => Promise.resolve( { results: [] } ),
		} );

		const result = await loadAjaxOptions( { _wpnonce: 'abc123' } )();

		expect( [ ...result ] ).toEqual( [] );
		expect( result.hasMore ).toBe( false );
	} );

	test( 'throws when response is invalid', async () => {
		fetch.mockResolvedValueOnce( {
			json: () => Promise.resolve( { error: 'Invalid response' } ),
		} );

		await expect( loadAjaxOptions( { _wpnonce: 'abc123' } )() )
			.rejects.toThrow( 'Invalid response.' );
	} );

	test( 'throws original fetch error', async () => {
		const networkError = new Error( 'Network failure' );
		fetch.mockRejectedValueOnce( networkError );

		await expect( loadAjaxOptions( { _wpnonce: 'abc123' } )() )
			.rejects.toEqual( networkError );
	} );
} );
