/**
 * Internal dependencies
 */
import reducer, { actions } from '@moderntribe/common/data/plugins';

describe( 'Plugins reducer', () => {
	it( 'Should return the default state', () => {
		expect( reducer( undefined, {} ) ).toEqual( [] );
	} );

	it( 'Should add a new plugin as active', () => {
		expect( reducer( {}, actions.addPlugin( 'events' ) ) ).toEqual( [ 'events' ] );
	} );

	it( 'Should avoid adding duplicates entries', () => {
		const state = reducer( {}, actions.addPlugin( 'events' ) );
		expect( reducer( state, actions.addPlugin( 'events' ) ) ).toEqual( [ 'events' ] );
	} );

	it( 'Should remove the plugin from the reducer', () => {
		const state = reducer( {}, actions.addPlugin( 'events' ) );
		expect( state ).toEqual( [ 'events' ] );
		expect( reducer( state, actions.removePlugin( 'events' ) ) ).toEqual( [] );
	} );
} );
