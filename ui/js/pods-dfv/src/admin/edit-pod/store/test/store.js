import { initStore } from '../store';
import { initialUIState } from '../reducer';
import {
	STORE_KEY_EDIT_POD,
	uiConstants,
} from '../constants';

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

		// Selector default values
		describe( 'selector UI defaults', () => {
			const [ select ] = _initStore( {} );

			describe( 'getActiveTab()', () => {
				const expected = initialUIState.activeTab;
				const result = select.getActiveTab();

				it( 'Should return the default on empty init', () => {
					expect( result ).not.toBeUndefined();
					expect( result ).toEqual( expected );
				} );
			} );

			describe( 'getSaveStatus()', () => {
				it( 'Should return the default on empty init', () => {
					const expected = initialUIState.saveStatus;
					const result = select.getSaveStatus();

					expect( result ).not.toBeUndefined();
					expect( result ).toEqual( expected );
				} );
			} );

			describe( 'isSaving()', () => {
				it( 'Should not initialize to a saving state', () => {
					const expected = false;
					const result = select.isSaving();

					expect( result ).toEqual( expected );
				} );
			} );
		} );

		// Selectors
		describe( 'getActiveTab()', () => {
			const tabName = uiConstants.tabNames.LABELS;
			const initialStoreState = { ui: { activeTab: tabName } };
			const [ select ] = _initStore( initialStoreState );

			it( 'Should return the active tab', () => {
				const expected = tabName;
				const result = select.getActiveTab();

				expect( result ).not.toBeUndefined();
				expect( result ).toEqual( expected );
			} );
		} );

		describe( 'getSaveStatus()', () => {
			const status = uiConstants.saveStatuses.SAVE_SUCCESS;
			const initialStoreState = { ui: { saveStatus: status } };
			const [ select ] = _initStore( initialStoreState );

			it( 'Should return the save status', () => {
				const expected = status;
				const result = select.getSaveStatus();

				expect( result ).not.toBeUndefined();
				expect( result ).toEqual( expected );
			} );
		} );

		describe( 'isSaving()', () => {
			it( 'Should return true when saving', () => {
				const status = uiConstants.saveStatuses.SAVING;
				const initialStoreState = { ui: { saveStatus: status } };
				const [ select ] = _initStore( initialStoreState );
				const result = select.isSaving();

				expect( result ).toEqual( true );
			} );

			it( 'Should return false when not saving', () => {
				const status = uiConstants.saveStatuses.NONE;
				const initialStoreState = { ui: { saveStatus: status } };
				const [ select ] = _initStore( initialStoreState );
				const result = select.isSaving();

				expect( result ).toEqual( false );
			} );
		} );

		describe( 'getPodName()', () => {
			const initialStoreState = { podMeta: { name: 'plugh' } };
			const [ select ] = _initStore( initialStoreState );

			it( 'Should retrieve the pod name', () => {
				const expected = initialStoreState.podMeta.name;
				const result = select.getPodName();

				expect( result ).not.toBeUndefined();
				expect( result ).toEqual( expected );
			} );
		} );

		describe( 'getFields()', () => {
			const initialStoreState = {
				fields: [
					{ name: 'xyzzy', label: 'label1' },
					{ name: 'plugh', label: 'label2' },
					{ name: 'abracadabra', label: 'label3' },
				],
			};
			const [ select ] = _initStore( initialStoreState );

			it( 'Should return the fields array', () => {
				const expected = initialStoreState.fields;
				const result = select.getFields();

				expect( result ).not.toBeUndefined();
				expect( result ).toEqual( expected );
			} );
		} );

		describe( 'getLabels()', () => {
			const initialStoreState = {
				labels: [
					{ name: 'xyzzy', value: 'label1' },
					{ name: 'plugh', value: 'label2' },
					{ name: 'abracadabra', value: 'label3' },
				],
			};
			const [ select ] = _initStore( initialStoreState );

			it( 'Should return the labels array', () => {
				const expected = initialStoreState.labels;
				const result = select.getLabels();

				expect( result ).not.toBeUndefined();
				expect( result ).toEqual( expected );
			} );
		} );

		describe( 'getLabelValue', () => {
			const initialStoreState = {
				labels: [
					{ name: 'xyzzy', value: 'label1' },
					{ name: 'plugh', value: 'label2' },
					{ name: 'abracadabra', value: 'label3' },
				],
			};
			const [ select ] = _initStore( initialStoreState );

			it( 'Should return label values', () => {
				initialStoreState.labels.forEach( ( thisLabel ) => {
					const expected = thisLabel.value;
					const result = select.getLabelValue( thisLabel.name );

					expect( result ).not.toBeUndefined();
					expect( result ).toBe( expected );
				} );
			} );
		} );
	} );
} );
