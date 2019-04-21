import { initStore } from '../store';
import { initialUIState } from '../reducer';
import { STORE_KEY_EDIT_POD } from 'pods-dfv/src/admin/edit-pod/store/constants';

describe( 'store', () => {

	/**
	 * The select and dispatch object must be retrieved again every time the
	 * store is initialized, so wrap that whole procedure up
	 *
	 * @param initialState
	 * @return {*[]}
	 * @private
	 */
	const _initStore = ( initialState ) => {
		initStore( initialState );
		const select = wp.data.select( STORE_KEY_EDIT_POD );
		const dispatch = wp.data.dispatch( STORE_KEY_EDIT_POD );

		return [ select, dispatch ];
	};

	describe( 'initStore() empty', () => {
		const expected = {
			ui: initialUIState,
			podMeta: {},
			fields: [],
			labels: [],
		};

		it( 'Initializes properly', () => {
			const [ select ] = _initStore( {} );
			const result = select.getState();

			expect( result ).toEqual( expected );
		} );
	} );

	describe( 'initStore() with initialState', () => {
		const fields = [ 'field 1', 'field 2', 'field 3' ];
		const labels = [ 'label 1', 'label 2', 'label 3' ];
		const name = 'xyzzy';
		const initialState = {
			fields: fields,
			labels: labels,
			podMeta: {
				name: name,
			},
		};
		const expected = {
			ui: initialUIState,
			fields: fields,
			labels: labels,
			podMeta: {
				podName: name,
			},
		};

		it( 'Initializes properly', () => {
			const [ select ] = _initStore( initialState );
			const result = select.getState();

			expect( result ).toEqual( expected );
		} );
	} );

	describe( 'selectors', () => {
		describe( 'selector UI defaults', () => {
			const [ select ] = _initStore( {} );

			describe( 'getSaveStatus', () => {
				it( 'Should return the default on empty init', () => {
					const expected = initialUIState.saveStatus;
					const result = select.getSaveStatus();

					expect( result ).not.toBeUndefined();
					expect( result ).toEqual( expected );
				} );
			} );

			describe( 'isSaving', () => {
				it( 'Should not initialize to a saving state', () => {
					const expected = false;
					const result = select.isSaving();

					expect( result ).toEqual( expected );
				} );
			} );
		} );

		describe( 'getPodName', () => {
			const initialStoreState = { podMeta: { name: 'plugh' } };
			const [ select ] = _initStore( initialStoreState );

			it( 'Should retrieve the pod name', () => {
				const expected = initialStoreState.podMeta.name;
				const result = select.getPodName();

				expect( result ).not.toBeUndefined();
				expect( result ).toEqual( expected );
			} );
		} );

	} );
} );
