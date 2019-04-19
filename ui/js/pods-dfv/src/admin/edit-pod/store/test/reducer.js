import {
	fields,
	labels,
	ui,
	initialUIState
} from '../reducer';

describe( 'reducer', () => {
	describe ( 'fields', () => {
		it( 'Should return an empty array by default', () => {
			expect( fields( undefined, undefined ) ).toEqual( [] );
		} );
	} );


	describe ( 'labels', () => {

	} );


	describe ( 'ui', () => {
		it( 'Should have proper defaults', () => {
			expect( ui( undefined, undefined ) ).toEqual( initialUIState );
		} );

	} );
} );
