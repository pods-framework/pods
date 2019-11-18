/**
 * Internal dependencies
 */
import { volatile } from '@moderntribe/common/data/forms/reducers';
import { actions } from '@moderntribe/common/data/forms';

describe( '[STORE] - Volatile reducer', () => {
	it( 'Should return the default state', () => {
		expect( volatile( undefined, {} ) ).toEqual( [] );
	} );

	it( 'Should add a new volatile value', () => {
		expect( volatile( [], actions.addVolatile( 20 ) ) ).toEqual( [ 20 ] );
		expect( volatile( [ 20 ], actions.addVolatile( 10 ) ) ).toEqual( [ 20, 10 ] );
	} );

	it( 'Should remove a volatile value', () => {
		expect( volatile( [], actions.removeVolatile( 20 ) ) ).toEqual( [] );
		expect( volatile( [ 20, 10 ], actions.removeVolatile( 20 ) ) ).toEqual( [ 10 ] );
		expect( volatile( [ 20, 10 ], actions.removeVolatile( 10 ) ) ).toEqual( [ 20 ] );
		expect( volatile( [ 10 ], actions.removeVolatile( 10 ) ) ).toEqual( [] );
	} );
} );
