import deepFreeze from 'deep-freeze';

import * as paths from '../state-paths';

import {
	// Everything
	getState,

	// Current Pod
	getPodID,
	getPodName,
	getPodOptions,
	getPodOption,

	//-- Pod Groups
	// @todo add these tests back in when working on
	// the Manage Groups functionality
	// getGroups,
	// getGroup,
	// getGroupFields,
	// groupFieldList,

	// Global Pod
	getGlobalPodOptions,
	getGlobalPodOption,
	getGlobalPodGroups,
	getGlobalPodGroup,
	getGlobalPodGroupFields,
	getGlobalGroupOptions,

	// UI
	getActiveTab,
	getSaveStatus,
	getSaveMessage,
	getDeleteStatus,
} from '../selectors';

import { uiConstants } from '../constants';

import {
	POD,
	GLOBAL_POD,
	GLOBAL_GROUP,
} from '../testData';

test( 'getState() returns the full state', () => {
	const state = deepFreeze( {
		foo: {
			xyzzy: 42,
			plugh: false,
		},
		bar: {
			name: 'bob',
			relationship: 'your uncle',
		},
		baz: [ 0, 1, 2 ],
	} );

	expect( getState( state ) ).toEqual( state );
} );

describe( 'Current Pod option selectors', () => {
	test( 'getPodID() returns the pod ID', () => {
		const state = deepFreeze(
			paths.CURRENT_POD.createTree( { id: 1998 } )
		);

		const result = getPodID( state );

		expect( result ).toEqual( 1998 );
	} );

	test( 'getPodName() returns return the Pod name', () => {
		const state = deepFreeze(
			paths.CURRENT_POD.createTree( { name: 'plugh' } )
		);

		const result = getPodName( state );

		expect( result ).toEqual( 'plugh' );
	} );

	test( 'getPodOptions returns all of the pod\'s options', () => {
		const state = deepFreeze(
			paths.CURRENT_POD.createTree( POD )
		);

		const result = getPodOptions( state );

		expect( result ).toEqual( POD );
	} );

	test( 'getPodOption returns the option value', () => {
		const key = 'foo';
		const expected = 'bar';

		const state = deepFreeze(
			paths.CURRENT_POD.createTree( { [ key ]: expected } )
		);

		const result = getPodOption( state, key );

		expect( result ).toEqual( 'bar' );
	} );
} );

describe( 'Global Pod option selectors', () => {
	let state;

	beforeEach( () => {
		state = deepFreeze(
			paths.GLOBAL_POD.createTree( GLOBAL_POD ),
			paths.GLOBAL_GROUP.createTree( GLOBAL_GROUP ),
		);
	} );

	test( 'getGlobalPodOptions returns all of the global pod\'s options', () => {
		const result = getGlobalPodOptions( state );

		expect( result ).toEqual( GLOBAL_POD );
	} );

	test( 'getGlobalPodOption returns the global option value', () => {
		const result = getGlobalPodOption( state, 'description' );

		expect( result ).toEqual( 'Pod configuration' );
	} );

	test( 'getGlobalPodGroups returns the global pod\'s groups', () => {
		const result = getGlobalPodGroups( state );

		expect( result.length ).toEqual( 2 );
		expect( result[ 0 ].label ).toEqual( 'Labels' );
		expect( result[ 1 ].label ).toEqual( 'REST API' );
	} );

	test( 'getGlobalPodGroup returns a specific global pod group', () => {
		const result = getGlobalPodGroup( state, 'rest-api' );

		expect( result.label ).toEqual( 'REST API' );
		expect( result.fields ).toBeDefined();
	} );

	test( 'getGlobalPodGroupFields returns a specific global pod group\'s fields', () => {
		const result = getGlobalPodGroupFields( state, 'rest-api' );

		expect( result.length ).toEqual( 2 );
	} );

	test( 'getGlobalGroupOptions returns a the group config fields', () => {
		const result = getGlobalGroupOptions( state );

		expect( result.length ).toEqual( 2 );
	} );
} );

describe( 'UI selectors', () => {
	test( 'getActiveTab() returns the active tab', () => {
		const state = deepFreeze(
			paths.ACTIVE_TAB.createTree( 'manage-fields' )
		);

		const result = getActiveTab( state );

		expect( result ).toEqual( 'manage-fields' );
	} );

	test( 'getSaveStatus() returns the save status', () => {
		const state = deepFreeze(
			paths.SAVE_STATUS.createTree( uiConstants.saveStatuses.SAVE_SUCCESS )
		);

		const result = getSaveStatus( state );

		expect( result ).toEqual( uiConstants.saveStatuses.SAVE_SUCCESS );
	} );

	test( 'getSaveMessage() returns the save message', () => {
		const saveMessage = 'Saved successfully.';
		const state = deepFreeze(
			paths.SAVE_MESSAGE.createTree( saveMessage )
		);

		const result = getSaveMessage( state );

		expect( result ).toEqual( saveMessage );
	} );

	test( 'getDeleteStatus() returns the delete status', () => {
		const deleteStatus = uiConstants.deleteStatuses.DELETE_SUCCESS;
		const state = deepFreeze(
			paths.DELETE_STATUS.createTree( deleteStatus )
		);

		const result = getDeleteStatus( state );

		expect( result ).toEqual( uiConstants.deleteStatuses.DELETE_SUCCESS );
	} );
} );
