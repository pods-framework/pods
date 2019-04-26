import { initStore } from '../store';
import {
	STORE_KEY_EDIT_POD,
	uiConstants,
	initialUIState,
} from '../constants';

const testStore = {
	select: null,
	dispatch: null,

	initStore: ( initialState ) => {
		initStore( initialState );
		testStore.select = wp.data.select( STORE_KEY_EDIT_POD );
		testStore.dispatch = wp.data.dispatch( STORE_KEY_EDIT_POD );
	}
};

describe( 'store', () => {
	describe( 'initStore() with initialState', () => {
		const fields = [ 'field 1', 'field 2', 'field 3' ];
		const labels = [ 'label 1', 'label 2', 'label 3' ];
		const name = 'xyzzy';
		const initialState = {
			fields: fields,
			podMeta: {
				name: name,
			},
		};
		const expected = {
			ui: initialUIState,
			fields: fields,
			podMeta: {
				name: name,
			},
		};

		it( 'Initializes properly', () => {
			testStore.initStore( initialState );
			const result = testStore.select.getState();

			expect( result ).toEqual( expected );
		} );
	} );

	describe( 'initStore() empty', () => {
		const expected = {
			ui: initialUIState,
			podMeta: {},
			fields: []
		};

		it( 'Initializes properly', () => {
			testStore.initStore( {} );
			const result = testStore.select.getState();

			expect( result ).toEqual( expected );
		} );
	} );

	describe( 'store integration', () => {
		describe( 'ui', () => {
			describe( 'Active tab', () => {
				const { tabNames } = uiConstants;
				const orderedList = [
					tabNames.MANAGE_FIELDS,
					tabNames.LABELS,
					tabNames.ADMIN_UI,
					tabNames.ADVANCED_OPTIONS,
					tabNames.AUTO_TEMPLATE_OPTIONS,
					tabNames.REST_API
				];
				const initialState = { ui: { tabs: { orderedList: orderedList } } };

				test( 'orderedList is initialized properly', () => {
					testStore.initStore( initialState );
					const fullState = testStore.select.getState();

					expect( fullState.ui.tabs.orderedList ).toEqual( orderedList );
				} );

				test( 'getActiveTab() should return the default on empty init', () => {
					const expected = initialUIState.activeTab;
					const result = testStore.select.getActiveTab();

					expect( result ).toBeDefined();
					expect( result ).toEqual( expected );
				} );

				test( 'setActiveTab() should change the active tab', () => {
					const newTab = tabNames.LABELS;
					testStore.dispatch.setActiveTab( newTab );
					const result = testStore.select.getActiveTab();

					expect( result ).toBeDefined();
					expect( result ).toEqual( newTab );
				} );
			} );

			describe( 'Save status', () => {
				const { saveStatuses } = uiConstants;

				test( 'Initializes with ui defaults', () => {
					testStore.initStore( {} );
					const fullState = testStore.select.getState();

					expect( fullState.ui ).toBeDefined();
					expect( fullState.ui ).toEqual( expect.objectContaining( initialUIState ) );
				} );

				test( 'getSaveStatus() should return the default on empty init', () => {
					const expected = initialUIState.saveStatus;
					const result = testStore.select.getSaveStatus();

					expect( result ).toBeDefined();
					expect( result ).toEqual( expected );
				} );

				test( 'isSaving() should not initialize to a saving state', () => {
					const expected = false;
					const result = testStore.select.isSaving();

					expect( result ).toEqual( expected );
				} );

				test( 'setSaveStatus() should change the status', () => {
					const expected = saveStatuses.SAVE_SUCCESS;
					testStore.dispatch.setSaveStatus( expected );
					const result = testStore.select.getSaveStatus();

					expect( result ).toEqual( expected );
				} );

				test( 'isSaving() should be true when saving', () => {
					const saving = saveStatuses.SAVING;
					testStore.dispatch.setSaveStatus( saving );

					expect( testStore.select.isSaving() ).toBe( true );
				} );

				test( 'isSaving() should be false when not saving', () => {
					const notSaving = saveStatuses.NONE;
					testStore.dispatch.setSaveStatus( notSaving );
					const result = testStore.select.isSaving();

					expect( result ).toBe( false );
				} );
			} );
		} );

		describe( 'podMeta', () => {
			describe( 'Pod name', () => {
				const initialName = 'plugh';
				const rename = 'xyzzy';

				test( 'The Pod name is initialized when provided', () => {
					const initialState = { podMeta: { name: initialName } };
					testStore.initStore( initialState );
					const fullState = testStore.select.getState();

					expect( fullState ).toEqual( expect.objectContaining( initialState ) );
				} );

				test( 'getPodName() should retrieve the pod name', () => {
					const expected = initialName;
					const result = testStore.select.getPodName();

					expect( result ).toBeDefined();
					expect( result ).toEqual( expected );
				} );

				test( 'setPodName() should update the pod name', () => {
					const expected = rename;
					testStore.dispatch.setPodName( expected );
					const result = testStore.select.getPodName();

					expect( result ).toEqual( expected );
				} );
			} );

			describe( 'General meta', () => {

				test( 'initializes with an empty object for podMeta', () => {
					testStore.initStore( {} );
					const fullState = testStore.select.getState();

					expect( fullState.podMeta ).toEqual( {} );
				} );

				test( 'setPodMetaValue() should create a new meta value', () => {
					const key = 'foo';
					const value = 'bar';
					testStore.dispatch.setPodMetaValue( key, value );
					const result = testStore.select.getPodMetaValue( key );

					expect( result ).toEqual( value );
				} );

				test( 'setPodMetaValue() should update an existing meta value', () => {
					const key = 'foo';
					const value = 'baz';
					testStore.dispatch.setPodMetaValue( key, value );
					const result = testStore.select.getPodMetaValue( key );

					expect( result ).toEqual( value );
				} );
			} );
		} );

		describe( 'fields', () => {
			const initialState = {
				fields: [
					{ name: 'xyzzy', label: 'label1' },
					{ name: 'plugh', label: 'label2' },
					{ name: 'abracadabra', label: 'label3' },
				],
			};

			it( 'initializes with fields when provided', () => {
				testStore.initStore( initialState );
				const fullState = testStore.select.getState();

				expect( fullState ).toEqual( expect.objectContaining( initialState ) );
			} );

			test( 'getFields() should return the fields array', () => {
				const expected = initialState.fields;
				const result = testStore.select.getFields();

				expect( result ).toBeDefined();
				expect( result ).toEqual( expected );
			} );
		} );
	} );
} );
