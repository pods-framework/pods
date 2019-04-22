import { initStore } from '../store';
import {
	STORE_KEY_EDIT_POD,
	uiConstants,
	initialUIState,
} from '../constants';

const initTestStore = ( initialState ) => {
	initStore( initialState );
	select = wp.data.select( STORE_KEY_EDIT_POD );
	dispatch = wp.data.dispatch( STORE_KEY_EDIT_POD );
};

let select, dispatch;

describe( 'store', () => {
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
				name: name,
			},
		};

		it( 'Initializes properly', () => {
			initTestStore( initialState );
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
			initTestStore( {} );
			const result = select.getState();

			expect( result ).toEqual( expected );
		} );
	} );

	describe( 'store integration', () => {
		describe( 'ui', () => {
			describe( 'Active tab', () => {
				const { tabNames } = uiConstants;

				test ( 'initialized properly', () => {
					const allTabs = {
						[ tabNames.MANAGE_FIELDS ]: {},
						[ tabNames.LABELS ]: {},
						[ tabNames.ADMIN_UI ]: {},
						[ tabNames.ADVANCED_OPTIONS ]: {},
						[ tabNames.AUTO_TEMPLATE_OPTIONS ]: {},
						[ tabNames.REST_API ]: {}
					};
					const initialState = { ui: { tabs: allTabs } };
					initTestStore( initialState );
				} );

				test( 'getActiveTab() should return the default on empty init', () => {
					const expected = initialUIState.activeTab;
					const result = select.getActiveTab();

					expect( result ).not.toBeUndefined();
					expect( result ).toEqual( expected );
				} );

				test( 'setActiveTab() should change the active tab', () => {
					const newTab = tabNames.LABELS;
					dispatch.setActiveTab( newTab );
					const result = select.getActiveTab();

					expect( result ).not.toBeUndefined();
					expect( result ).toEqual( newTab );
				} );
			} );

			describe( 'Save status', () => {
				const { saveStatuses } = uiConstants;
				initTestStore( {} );

				test( 'getSaveStatus() should return the default on empty init', () => {
					const expected = initialUIState.saveStatus;
					const result = select.getSaveStatus();

					expect( result ).not.toBeUndefined();
					expect( result ).toEqual( expected );
				} );

				test( 'isSaving() should not initialize to a saving state', () => {
					const expected = false;
					const result = select.isSaving();

					expect( result ).toEqual( expected );
				} );

				test( 'setSaveStatus() should change the status', () => {
					const expected = saveStatuses.SAVE_SUCCESS;
					dispatch.setSaveStatus( expected );
					const result = select.getSaveStatus();

					expect( result ).toEqual( expected );
				} );

				test( 'isSaving() should be true when saving', () => {
					const saving = saveStatuses.SAVING;
					dispatch.setSaveStatus( saving );

					expect( select.isSaving() ).toBe( true );
				} );

				test( 'isSaving() should be false when not saving', () => {
					const notSaving = saveStatuses.NONE;
					dispatch.setSaveStatus( notSaving );

					expect( select.isSaving() ).toBe( false );
				} );
			} );
		} );

		describe( 'podMeta', () => {
			describe( 'Pod name', () => {
				const initialName = 'plugh';
				const rename = 'xyzzy';

				it( 'initializes', () => {
					const initialState = { podMeta: { name: initialName } };
					initTestStore( initialState );
				} );

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

			describe( 'General meta', () => {
				initTestStore( {} );

				test( 'setPodMetaValue() should create a new meta value', () => {
					const key = 'foo';
					const value = 'bar';
					dispatch.setPodMetaValue( key, value );
					const result = select.getPodMetaValue( key );

					expect( result ).toEqual( value );
				} );

				test( 'setPodMetaValue() should update an existing meta value', () => {
					const key = 'foo';
					const value = 'baz';
					dispatch.setPodMetaValue( key, value );
					const result = select.getPodMetaValue( key );

					expect( result ).toEqual( value );
				} );
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

			it( 'initializes', () => {
				initTestStore( initialStoreState );
			} );

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

			it( 'initializes', () => {
				initTestStore( initialStoreState );
			} );

			it( 'getFields() should return the fields array', () => {
				const expected = initialStoreState.fields;
				const result = select.getFields();

				expect( result ).not.toBeUndefined();
				expect( result ).toEqual( expected );
			} );
		} );
	} );
} );
