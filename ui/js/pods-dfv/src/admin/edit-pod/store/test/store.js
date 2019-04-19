import { initStore } from '../store';
import { initialUIState } from '../reducer';

describe( 'store', () => {
	describe( 'initStore() empty', () => {
		const initializedWithEmptyState = {
			fields: [],
			labels: [],
			ui: initialUIState
		};
		const store = initStore( {} );

		it( 'Initializes properly', () => {
			expect( store.getState() ).toEqual( initializedWithEmptyState );
		} );
	} );

	describe( 'initStore() with initialState', () => {
		const initialState = {
			fields: [ 'field 1', 'field 2', 'field 3' ],
			labels: [ 'label 1', 'label 2', 'label 3' ],
		};
		const initializedWithInitialState = {
			fields: [ 'field 1', 'field 2', 'field 3' ],
			labels: [ 'label 1', 'label 2', 'label 3' ],
			ui: initialUIState
		};
		const store = initStore( initialState );

		it( 'Initializes properly', () => {
			expect( store.getState() ).toEqual( initializedWithInitialState );
		} );
	} );
} );
