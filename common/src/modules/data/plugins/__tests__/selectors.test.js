/**
 * Internal dependencies
 */
import { selectors } from '@moderntribe/common/data/plugins';

describe( 'Plugin selectors', () => {
	let state;

	beforeEach( () => {
		state = {
			plugins: [ 'events' ],
		};
	} );

	it( 'should have plugin', () => {
		expect( selectors.hasPlugin( state, 'events' ) ).toEqual( true );
		expect( selectors.hasPlugin( state )( 'events' ) ).toEqual( true );
	} );
	it( 'should not have plugin', () => {
		expect( selectors.hasPlugin( state, 'events-pro' ) ).toEqual( false );
		expect( selectors.hasPlugin( state )( 'events-pro' ) ).toEqual( false );
	} );
} );
