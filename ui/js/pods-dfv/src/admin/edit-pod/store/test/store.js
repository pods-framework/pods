import { initStore } from '../store';
import { initialUIState } from '../reducer';
import {
	STORE_KEY_EDIT_POD,
	uiConstants,
} from '../constants';

describe( 'store', () => {

	/**
	 * The select and dispatch objects must be retrieved every time the store
	 * is initialized, so wrap that whole procedure up
	 *
	 * @param initialState
	 * @return {{select, dispatch}}
	 * @private
	 */
	const _initStore = ( initialState ) => {
		initStore( initialState );
		const select = wp.data.select( STORE_KEY_EDIT_POD );
		const dispatch = wp.data.dispatch( STORE_KEY_EDIT_POD );

		return { select, dispatch };
	};

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
			const { select } = _initStore( initialState );
			const result = select.getState();

			expect( result ).toEqual( expected );
		} );
	} );

	describe( 'initStore() empty', () => {
		const expected = {
			ui: initialUIState,
			podMeta: {},
			fields: [],
			labels: [],
		};

		it( 'Initializes properly', () => {
			const { select } = _initStore( {} );
			const result = select.getState();

			expect( result ).toEqual( expected );
		} );
	} );

	describe( 'store integration', () => {

		describe( 'ui', () => {
			describe( 'Active tab', () => {
				const { select, dispatch } = _initStore( {} );

				it( 'getActiveTab() should return the default on empty init', () => {
					const expected = initialUIState.activeTab;
					const result = select.getActiveTab();

					expect( result ).not.toBeUndefined();
					expect( result ).toEqual( expected );
				} );

				it( 'setActiveTab() should change the active tab', () => {
					const newTab = uiConstants.tabNames.LABELS;
					dispatch.setActiveTab( newTab );
					const result = select.getActiveTab();

					expect( result ).not.toBeUndefined();
					expect( result ).toEqual( newTab );
				} );
			} );

			describe( 'Save status', () => {
				const { select, dispatch } = _initStore( {} );

				it( 'getSaveStatus() should return the default on empty init', () => {
					const expected = initialUIState.saveStatus;
					const result = select.getSaveStatus();

					expect( result ).not.toBeUndefined();
					expect( result ).toEqual( expected );
				} );

				it( 'isSaving() should not initialize to a saving state', () => {
					const expected = false;
					const result = select.isSaving();

					expect( result ).toEqual( expected );
				} );

				it( 'setSaveStatus() should change the status', () => {
					const expected = uiConstants.saveStatuses.SAVE_SUCCESS;
					dispatch.setSaveStatus( expected );
					const result = select.getSaveStatus();

					expect( result ).toEqual( expected );
				} );

				it( 'isSaving() should be true when saving', () => {
					const saving = uiConstants.saveStatuses.SAVING;
					dispatch.setSaveStatus( saving );
					const result = select.getSaveStatus();

					expect( select.isSaving() ).toBe( true );
				} );

				it( 'isSaving() should be false when not saving', () => {
					const notSaving = uiConstants.saveStatuses.NONE;
					dispatch.setSaveStatus( notSaving );
					const result = select.getSaveStatus();

					expect( select.isSaving() ).toBe( false );
				} );
			} );
		} );

		describe( 'podMeta', () => {
			const initialName = 'plugh';
			const rename = 'xyzzy';
			const initialStoreState = { podMeta: { name: initialName } };
			const { select, dispatch } = _initStore( initialStoreState );

			it( 'getPodName() should retrieve the pod name', () => {
				const expected = initialName;
				const result = select.getPodName();

				expect( result ).not.toBeUndefined();
				expect( result ).toEqual( expected );
			} );

			it( 'setPodName() should update the pod name', () => {
				const expected = rename;
				dispatch.setPodName( expected );
				const result = select.getPodName();

				expect( result ).toEqual( expected );
			} );
		} );

		describe( 'labels', () => {
			const initialStoreState = {
				labels: [
					{ name: 'xyzzy', value: 'label1' },
					{ name: 'plugh', value: 'label2' },
					{ name: 'abracadabra', value: 'label3' },
				],
			};
			const { select, dispatch } = _initStore( initialStoreState );

			it( 'getLabels() should return the labels array', () => {
				const expected = initialStoreState.labels;
				const result = select.getLabels();

				expect( result ).not.toBeUndefined();
				expect( result ).toEqual( expected );
			} );

			it( 'getLabelValue() should return a label value', () => {
				initialStoreState.labels.forEach( ( thisLabel ) => {
					const expected = thisLabel.value;
					const result = select.getLabelValue( thisLabel.name );

					expect( result ).not.toBeUndefined();
					expect( result ).toBe( expected );
				} );
			} );

			it( 'setLabelValue() should update a label value', () => {
				const targetLabelName = 'xyzzy';
				const expected = 'label 1 edited';
				dispatch.setLabelValue( targetLabelName, expected );
				const result = select.getLabelValue( targetLabelName );

				expect( result ).not.toBeUndefined();
				expect( result ).toEqual( expected );
			} );
		} );

		describe( 'fields', () => {
			const initialStoreState = {
				fields: [
					{ name: 'xyzzy', label: 'label1' },
					{ name: 'plugh', label: 'label2' },
					{ name: 'abracadabra', label: 'label3' },
				],
			};
			const { select } = _initStore( initialStoreState );

			it( 'getFields() should return the fields array', () => {
				const expected = initialStoreState.fields;
				const result = select.getFields();

				expect( result ).not.toBeUndefined();
				expect( result ).toEqual( expected );
			} );
		} );
	} );
} );
