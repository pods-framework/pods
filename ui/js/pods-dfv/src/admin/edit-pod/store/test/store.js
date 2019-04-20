import { initStore } from '../store';
import { initialUIState } from '../reducer';
import { STORE_KEY_EDIT_POD } from 'pods-dfv/src/admin/edit-pod/store/constants';

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
		const fields = [ 'field 1', 'field 2', 'field 3' ];
		const labels = [ 'label 1', 'label 2', 'label 3' ];
		const podName = 'xyzzy';
		const initialState = {
			fields: fields,
			labels: labels,
			podInfo: {
				name: podName,
			},
		};
		const expected = {
			ui: initialUIState,
			fields: fields,
			labels: labels,
			podMeta: {
				podName: podName,
			},
		};
		const store = initStore( initialState );

		it( 'Initializes properly', () => {
			expect( store.getState() ).toEqual( expected );
		} );
	} );

	describe( 'selector UI defaults', () => {
		initStore( {} );
		const selectors = wp.data.select( STORE_KEY_EDIT_POD );

		describe( 'getActiveTab', () => {
			it( 'Should return the default on empty init', () => {
				expect( selectors.getActiveTab() ).toEqual( initialUIState.activeTab );
			} );
		} );

		describe( 'getSaveStatus', () => {
			it( 'Should return the default on empty init', () => {
				expect( selectors.getSaveStatus() ).toEqual( initialUIState.saveStatus );
			} );
		} );

		describe( 'isSaving', () => {
			it( 'Should not initialize to a saving state', () => {
				expect( selectors.isSaving() ).toEqual( false );
			} );
		} );
	} );

	describe( 'dispatch', () => {

	} );
} );
