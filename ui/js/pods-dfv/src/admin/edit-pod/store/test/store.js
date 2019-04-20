import { initStore } from '../store';
import { initialUIState } from '../reducer';

describe( 'store', () => {
	describe( 'initStore() empty', () => {
		const expected = {
			ui: initialUIState,
			podMeta: {},
			fields: [],
			labels: [],
		};
		const store = initStore( {} );

		it( 'Initializes properly', () => {
			expect( store.getState() ).toEqual( expected );
		} );
	} );

	describe( 'initStore() with initialState', () => {
		const fields = [ 'field 1', 'field 2', 'field 3'];
		const labels = [ 'label 1', 'label 2', 'label 3' ];
		const podName = 'xyzzy';
		const initialState = {
			fields: fields,
			labels: labels,
			podInfo: {
				name: podName
			}
		};
		const expected = {
			ui: initialUIState,
			fields: fields,
			labels: labels,
			podMeta: {
				podName: podName
			},
		};
		const store = initStore( initialState );

		it( 'Initializes properly', () => {
			expect( store.getState() ).toEqual( expected );
		} );
	} );
} );
