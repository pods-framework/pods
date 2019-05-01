import { merge } from 'lodash';
import deepFreeze from 'deep-freeze';

import { initStore } from '../store';
import * as paths from '../state-paths';
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
		const initialState = merge(
			paths.POD_NAME.createTree( 'Podname' ),
			paths.POD_ID.createTree( 42 ),
			paths.OPTIONS.createTree( {
				xyzzy: { name: 'xyzzy', value: 'Value 1' },
				plugh: { name: 'plugh', value: 'Value 2' },
			} ),
			paths.FIELDS.createTree( [ 'field 1', 'field 2', 'field 3' ] )
		);

		it( 'Initializes properly', () => {
			testStore.initStore( deepFreeze( initialState ) );
			const result = testStore.select.getState();
			const expected = merge(
				paths.UI.createTree( initialUIState ),
				initialState
			);

			expect( result ).toBeDefined();
			expect( result ).toEqual( expected );
		} );
	} );

	describe( 'initStore() empty', () => {
		it( 'Initializes properly', () => {
			testStore.initStore( deepFreeze( {} ) );
			const result = testStore.select.getState();
			const expected = merge(
				paths.UI.createTree( initialUIState ),
				paths.POD_META.createTree( {} ),
				paths.OPTIONS.createTree( {} ),
				paths.FIELDS.createTree( [] ),
			);

			expect( result ).toBeDefined();
			expect( result ).toEqual( expected );
		} );
	} );

	describe( 'store integration', () => {
		describe( 'ui', () => {
			describe( 'Active tab', () => {
				test( 'orderedList is initialized properly', () => {
					const orderedList = [
						uiConstants.tabNames.MANAGE_FIELDS,
						uiConstants.tabNames.LABELS,
						uiConstants.tabNames.ADMIN_UI,
						uiConstants.tabNames.ADVANCED_OPTIONS,
						uiConstants.tabNames.AUTO_TEMPLATE_OPTIONS,
						uiConstants.tabNames.REST_API
					];
					const initialState = paths.TABS_LIST.createTree( orderedList );

					testStore.initStore( deepFreeze( initialState ) );
					const state = testStore.select.getState();
					const result = paths.TABS_LIST.getFrom( state );

					expect( result ).toBeDefined();
					expect( result ).toEqual( orderedList );
				} );

				test( 'getActiveTab() should return the default on empty init', () => {
					const expected = initialUIState.activeTab;
					const result = testStore.select.getActiveTab();

					expect( result ).toBeDefined();
					expect( result ).toEqual( expected );
				} );

				test( 'setActiveTab() should change the active tab', () => {
					const newTab = uiConstants.tabNames.LABELS;
					testStore.dispatch.setActiveTab( newTab );
					const result = testStore.select.getActiveTab();

					expect( result ).toBeDefined();
					expect( result ).toEqual( newTab );
				} );
			} );

			describe( 'Save status', () => {
				test( 'Initializes state with the proper default value', () => {
					testStore.initStore( deepFreeze( {} ) );
					const expected = initialUIState.saveStatus;
					const state = testStore.select.getState();
					const result = paths.SAVE_STATUS.getFrom( state );

					expect( result ).toBeDefined();
					expect( result ).toEqual( expected );
				} );

				test( 'getSaveStatus() should return the default on empty init', () => {
					const expected = initialUIState.saveStatus;
					const result = testStore.select.getSaveStatus();

					expect( result ).toBeDefined();
					expect( result ).toEqual( expected );
				} );

				test( 'isSaving() should initially be false', () => {
					expect( testStore.select.isSaving() ).toBe( false );
				} );

				test( 'setSaveStatus() should change the status', () => {
					const newStatus = uiConstants.saveStatuses.SAVE_SUCCESS;
					testStore.dispatch.setSaveStatus( newStatus );
					const result = testStore.select.getSaveStatus();

					expect( result ).toEqual( newStatus );
				} );

				test( 'isSaving() should be true when saving', () => {
					testStore.dispatch.setSaveStatus( uiConstants.saveStatuses.SAVING );
					expect( testStore.select.isSaving() ).toBe( true );
				} );

				test( 'isSaving() should be false when not saving', () => {
					testStore.dispatch.setSaveStatus( uiConstants.saveStatuses.NONE );
					expect( testStore.select.isSaving() ).toBe( false );
				} );
			} );
		} );

		describe( 'podMeta', () => {
			describe( 'Pod name/id', () => {
				const testID = 42;
				const initialName = 'plugh';
				const rename = 'xyzzy';

				test( 'Initializes state with the Pod ID when provided', () => {
					const initialState = paths.POD_ID.createTree( testID );
					testStore.initStore( deepFreeze( initialState ) );
					const result = paths.POD_ID.getFrom( testStore.select.getState() );

					expect( result ).toBeDefined();
					expect( result ).toEqual( testID );
				} );

				test( 'Initializes state with the Pod name when provided', () => {
					const initialState = paths.POD_NAME.createTree( initialName );
					testStore.initStore( deepFreeze( initialState ) );
					const result = paths.POD_NAME.getFrom( testStore.select.getState() );

					expect( result ).toBeDefined();
					expect( result ).toEqual( initialName );
				} );

				test( 'getPodName() should retrieve the pod name', () => {
					const expected = initialName;
					const result = testStore.select.getPodName();

					expect( result ).toBeDefined();
					expect( result ).toEqual( expected );
				} );

				test( 'setPodName() should update the pod name', () => {
					testStore.dispatch.setPodName( rename );
					const result = testStore.select.getPodName();

					expect( result ).toBeDefined();
					expect( result ).toEqual( rename );
				} );
			} );

			describe( 'General meta', () => {
				test( 'initializes with an empty object for podMeta', () => {
					testStore.initStore( deepFreeze( {} ) );
					const state = testStore.select.getState();
					const result = paths.POD_META.getFrom( state );

					expect( result ).toBeDefined();
					expect( result ).toEqual( {} );
				} );

				test( 'setPodMetaValue() should create a new meta value', () => {
					const key = 'foo';
					const value = 'bar';
					testStore.dispatch.setPodMetaValue( key, value );
					const result = testStore.select.getPodMetaValue( key );

					expect( result ).toBeDefined();
					expect( result ).toEqual( value );
				} );

				test( 'setPodMetaValue() should update an existing meta value', () => {
					const key = 'foo';
					const value = 'baz';
					testStore.dispatch.setPodMetaValue( key, value );
					const result = testStore.select.getPodMetaValue( key );

					expect( result ).toBeDefined();
					expect( result ).toEqual( value );
				} );
			} );
		} );

		describe( 'fields', () => {
			const fieldArray = 				[
				{ name: 'foo', label: 'label1' },
				{ name: 'bar', label: 'label2' },
				{ name: 'baz', label: 'label3' },
			];
			const initialState = paths.FIELDS.createTree( fieldArray );

			it( 'Initializes the state with fields when provided', () => {
				testStore.initStore( deepFreeze( initialState ) );
				const result = paths.FIELDS.getFrom( testStore.select.getState() );

				expect( result ).toBeDefined();
				expect( result ).toEqual( fieldArray );
			} );

			test( 'getFields() should return the fields array', () => {
				const result = testStore.select.getFields();

				expect( result ).toBeDefined();
				expect( result ).toEqual( fieldArray );
			} );
		} );
	} );
} );
