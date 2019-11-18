/**
 * Internal dependencies
 */
import { PREFIX_COMMON_STORE } from '@moderntribe/common/data/utils';
import { types } from '@moderntribe/common/data/plugins';

describe( 'Plugin types', () => {
	it( 'Should return the types with a prefix', () => {
		expect( types.ADD_PLUGIN ).toBe( `${ PREFIX_COMMON_STORE }/ADD_PLUGIN` );
		expect( types.REMOVE_PLUGIN ).toBe( `${ PREFIX_COMMON_STORE }/REMOVE_PLUGIN` );
	} );
} );
