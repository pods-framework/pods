/**
 * Internal dependencies
 */
import { checkRequestIds } from '@moderntribe/common/utils/slide';

describe( 'Tests for slide.js', () => {
	it( 'should return null for up and down', () => {
		const ids = checkRequestIds( 'test-id' );
		expect( ids.up ).toEqual( null );
		expect( ids.down ).toEqual( null );
	} );
} );
