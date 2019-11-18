/**
 * Internal dependencies
 */
import { PREFIX_COMMON_STORE } from '@moderntribe/common/data/utils';

describe( 'prefix', () => {
	it( 'Should return the prefix', () => {
		expect( PREFIX_COMMON_STORE ).toBe('@@MT/COMMON');
	} );
} );
