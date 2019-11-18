/**
 * Internal dependencies
 */
import { utils } from '@moderntribe/common/store/middlewares/request';

const wpParamsExpected = {
	orderby: 'title',
	status: [ 'draft', 'publish' ],
	order: 'asc',
	page: 1,
};

describe( 'Request utils', () => {
	it( 'Should generate the WP params', () => {
		expect( utils.toWpParams( {} ) ).toEqual( wpParamsExpected );
	} );

	it( 'Should order by relevance if has search', () => {
		expect( utils.toWpParams( { search: 'tribe' } ) )
			.toEqual( {
				...wpParamsExpected,
				search: 'tribe',
				orderby: 'relevance',
			} );
	} );

	it( 'Should update the exclude parameter', () => {
		expect( utils.toWpParams( { exclude: [] } ) ).toEqual( wpParamsExpected );
		expect( utils.toWpParams( { exclude: [ 1, 2 ] } ) )
			.toEqual( {
				...wpParamsExpected,
				exclude: [ 1, 2 ],
			} );
	} );

	it( 'Should generate a WP Query', () => {
		expect( utils.toWPQuery() ).toBe( 'orderby=title&status=draft%2Cpublish&order=asc&page=1' );
		expect( utils.toWPQuery( { search: 'Modern Tribe' } ) )
			.toBe( 'orderby=relevance&status=draft%2Cpublish&order=asc&page=1&search=Modern%20Tribe' );
	} );

	it( 'Should return the total of pages', () => {
		const headers = new Headers();
		headers.append( 'x-wp-totalpages', 5 );
		expect( headers.get( 'x-wp-totalpages' ) ).toBe( '5' );
		expect( utils.getTotalPages( headers ) ).toBe( 5 );

		headers.set( 'x-wp-totalpages', '5' );
		expect( headers.get( 'x-wp-totalpages' ) ).toBe( '5' );
		expect( utils.getTotalPages( headers ) ).toBe( 5 );

		headers.set( 'x-wp-totalpages', '5.3' );
		expect( headers.get( 'x-wp-totalpages' ) ).toBe( '5.3' );
		expect( utils.getTotalPages( headers ) ).toBe( 5 );

		headers.delete( 'x-wp-totalpages' );
		headers.set( 'x-wp', 5 );
		expect( utils.getTotalPages( headers ) ).toBe( 0 );
		expect( utils.getTotalPages( new Headers() ) ).toBe( 0 );
	} );
} );
