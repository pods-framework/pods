import deepFreeze from 'deep-freeze';

import { select, dispatch } from '@wordpress/data';

import { initStore } from '../store';
import * as paths from '../state-paths';
import {
	STORE_KEY_EDIT_POD,
	uiConstants,
	initialUIState,
} from '../constants';

import { TEST_CONFIG_DATA } from '../testData';

const testStore = {
	select: null,
	dispatch: null,

	initStore: ( initialState ) => {
		initStore( { config: initialState } );
		testStore.select = select( STORE_KEY_EDIT_POD );
		testStore.dispatch = dispatch( STORE_KEY_EDIT_POD );
	},
};

describe( 'store', () => {
	test( 'initStore() with initialState initializes properly', () => {
		const initialState = {
			...paths.UI.createTree( initialUIState ),
			...TEST_CONFIG_DATA,
		};

		testStore.initStore( deepFreeze( initialState ) );
		const result = testStore.select.getState();

		expect( result ).toEqual( initialState );
	} );

	test( 'initStore() empty initializes properly', () => {
		const expected = {
			...paths.UI.createTree( initialUIState ),
			currentPod: {},
			global: {},
		};

		testStore.initStore( deepFreeze( {} ) );
		const result = testStore.select.getState();

		expect( result ).toEqual( expected );
	} );
} );

describe( 'UI store integration', () => {
	beforeEach( () => {
		testStore.initStore( deepFreeze( {} ) );
	} );

	test( 'getActiveTab() should return the default on empty init', () => {
		const expected = initialUIState.activeTab;
		const result = testStore.select.getActiveTab();

		expect( result ).toEqual( expected );
	} );

	test( 'setActiveTab() should change the active tab', () => {
		const newTab = 'labels';

		testStore.dispatch.setActiveTab( newTab );
		const result = testStore.select.getActiveTab();

		expect( result ).toEqual( newTab );
	} );

	test( 'getSaveStatus() should return the default on empty init', () => {
		const expected = initialUIState.saveStatus;
		const result = testStore.select.getSaveStatus();

		expect( result ).toEqual( expected );
	} );

	test( 'setSaveStatus() should change the status', () => {
		const newStatus = uiConstants.saveStatuses.SAVE_SUCCESS;

		testStore.dispatch.setSaveStatus( newStatus );
		const result = testStore.select.getSaveStatus();

		expect( result ).toEqual( newStatus );
	} );
} );

describe( 'current pod options', () => {
	const testPodID = 42;
	const intialPodName = 'plugh';
	const newPodName = 'xyzzy';

	test( 'Initializes state with the Pod ID when provided', () => {
		const initialState = paths.POD_ID.createTree( testPodID );

		testStore.initStore( deepFreeze( initialState ) );
		const result = testStore.select.getPodID();

		expect( result ).toEqual( testPodID );
	} );

	test( 'Initializes state with the Pod name when provided', () => {
		const initialState = paths.POD_NAME.createTree( intialPodName );

		testStore.initStore( deepFreeze( initialState ) );
		const result = testStore.select.getPodName();

		expect( result ).toEqual( intialPodName );
	} );

	test( 'setPodName() should update the pod name', () => {
		const initialState = paths.POD_NAME.createTree( intialPodName );
		testStore.initStore( deepFreeze( initialState ) );

		testStore.dispatch.setPodName( newPodName );
		const result = testStore.select.getPodName();

		expect( result ).toEqual( newPodName );
	} );

	test( 'setOptionValue() should create a new option value', () => {
		const name = 'foo1';
		const value = 'Foo1 Value';

		testStore.initStore( deepFreeze( {} ) );
		testStore.dispatch.setOptionValue( name, value );
		const result = testStore.select.getPodOption( name );

		expect( result ).toEqual( value );
	} );

	test( 'setOptionValue() should update an existing option value', () => {
		const name = 'foo1';
		const value = 'Foo1 New Value';
		const secondValue = 'Foo2 New Value';

		testStore.initStore( deepFreeze( {} ) );
		testStore.dispatch.setOptionValue( name, value );
		testStore.dispatch.setOptionValue( name, secondValue );

		const result = testStore.select.getPodOption( name );

		expect( result ).toEqual( secondValue );
	} );

	// @todo re-enable and fix when doing the manage group work
	test.skip( 'moveGroup() should reorder the group list', () => {
		const expected = [ 'group3', 'group0', 'group1', 'group2' ];

		testStore.initStore( deepFreeze( {} ) );
		testStore.dispatch.moveGroup( 3, 0 );
		// const result = testStore.select.getGroupList();

		expect( result ).toEqual( expected );
	} );
} );
