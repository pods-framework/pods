import deepFreeze from 'deep-freeze';

import { select, dispatch } from '@wordpress/data';

import { initEditPodStore } from '../store';
import * as paths from '../state-paths';
import {
	STORE_KEY_EDIT_POD,
	INITIAL_UI_STATE,
} from '../constants';

import { TEST_CONFIG_DATA } from '../testData';

const testStore = {
	select: null,
	dispatch: null,

	initStore: ( initialState ) => {
		initEditPodStore( initialState );
		testStore.select = select( STORE_KEY_EDIT_POD );
		testStore.dispatch = dispatch( STORE_KEY_EDIT_POD );
	},
};

describe( 'store', () => {
	test( 'initEditPodStore() with initialState initializes properly', () => {
		const initialState = {
			...paths.UI.createTree( INITIAL_UI_STATE ),
			...TEST_CONFIG_DATA,
		};

		testStore.initStore( deepFreeze( initialState ) );
		const result = testStore.select.getState();

		expect( result ).toEqual( initialState );
	} );

	test( 'initEditPodStore() empty initializes properly', () => {
		const expected = {
			...paths.UI.createTree( INITIAL_UI_STATE ),
			currentPod: {},
			global: {},
			data: {
				fieldTypes: {},
				relatedObjects: {},
			},
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
		const expected = INITIAL_UI_STATE.activeTab;
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
		const expected = INITIAL_UI_STATE.saveStatus;
		const result = testStore.select.getSaveStatus();

		expect( result ).toEqual( expected );
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

	test( 'moveGroup() should reorder the group list', () => {
		const initialGroups = [ 'group0', 'group1', 'group2', 'group3' ];
		const initialState = paths.GROUPS.createTree( initialGroups );

		const expected = [ 'group3', 'group0', 'group1', 'group2' ];

		testStore.initStore( deepFreeze( initialState ) );
		testStore.dispatch.moveGroup( 3, 0 );
		const result = testStore.select.getGroups();

		expect( result ).toEqual( expected );
	} );
} );
