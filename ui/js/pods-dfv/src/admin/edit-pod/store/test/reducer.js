import deepFreeze from 'deep-freeze';

import * as paths from '../state-paths';

import {
	uiConstants,
	currentPodConstants,
	initialUIState,
} from '../constants';

import {
	ui,
	currentPod,
	global,
} from '../reducer';

describe( 'UI reducer', () => {
	const {
		actions,
		saveStatuses,
		deleteStatuses,
	} = uiConstants;

	let state;

	beforeEach( () => {
		state = { ...initialUIState };
	} );

	afterEach( () => {
		state = undefined;
	} );

	it( 'has the proper defaults', () => {
		const newState = ui( undefined, undefined );

		expect( newState ).toEqual( initialUIState );
	} );

	it( 'changes the active tab', () => {
		const action = {
			type: actions.SET_ACTIVE_TAB,
			activeTab: 'labels',
		};

		const newState = ui( state, action );

		expect( newState.activeTab ).toEqual( 'labels' );
	} );

	it( 'changes the save status', () => {
		const action = {
			type: actions.SET_SAVE_STATUS,
			saveStatus: saveStatuses.SAVING,
			message: 'Saving...',
		};

		const newState = ui( state, action );

		expect( newState.saveStatus ).toEqual( action.saveStatus );
		expect( newState.saveMessage ).toEqual( 'Saving...' );
	} );

	it( 'uses the default for an unknown save status', () => {
		const action = {
			type: actions.SET_SAVE_STATUS,
			saveStatus: 'xyzzy',
		};

		const newState = ui( state, action );

		expect( newState.saveStatus ).toEqual( initialUIState.saveStatus );
	} );

	it( 'changes the delete status', () => {
		const action = {
			type: actions.SET_DELETE_STATUS,
			deleteStatus: deleteStatuses.DELETING,
			message: 'Deleting...',
		};

		const newState = ui( state, action );

		expect( newState.deleteStatus ).toEqual( deleteStatuses.DELETING );
		expect( newState.deleteMessage ).toEqual( 'Deleting...' );
	} );

	it( 'uses the default for an unknown delete status', () => {
		const action = {
			type: actions.SET_DELETE_STATUS,
			deleteStatus: 'xyzzy',
		};

		const newState = ui( state, action );

		expect( newState.deleteStatus ).toEqual( initialUIState.deleteStatus );
	} );
} );

describe( 'currentPod Reducer', () => {
	const { actions } = currentPodConstants;
	const { GROUPS } = paths;

	const initialGroupList = [ 'zero', 'one', 'two', 'three' ];

	it( 'returns an empty object by default', () => {
		expect( currentPod( undefined, undefined ) ).toEqual( {} );
	} );

	it( 'updates the pod name', () => {
		const action = {
			type: actions.SET_POD_NAME,
			name: 'plugh',
		};

		const newState = currentPod( undefined, action );

		expect( newState.name ).toEqual( 'plugh' );
	} );

	it( 'Should update pod options', () => {
		const action = {
			type: actions.SET_OPTION_VALUE,
			optionName: 'foo',
			value: 'bar',
		};
		const expected = { foo: 'bar' };

		const result = currentPod( undefined, action );

		expect( result ).toEqual( expected );
	} );

	test( 'When passed a SET_OPTIONS_VALUES value, updates options based on the provided keys and values', () => {
		const initialState = deepFreeze( {
			first: 'Old Value',
			second: 'Another string value',
			third: false,
			fourth: 3,
			fifth: 'Remains unchanged',
		} );

		const action = {
			type: actions.SET_OPTIONS_VALUES,
			options: {
				first: 'First Value',
				second: 'Another string value',
				third: true,
				fourth: 12,
				sixth: 'New option',
			},
		};

		const expected = {
			first: 'First Value',
			second: 'Another string value',
			third: true,
			fourth: 12,
			fifth: 'Remains unchanged',
			sixth: 'New option',
		};

		const result = currentPod( initialState, action );

		expect( result ).toEqual( expected );
	} );

	// @todo re-enable and fix these when working on Managing Groups work
	test.skip( 'create a new group list if it doesn\'t exist', () => {
		const action = {
			type: actions.SET_GROUP_LIST,
			groupList: initialGroupList,
		};
		const expected = GROUPS.tailCreateTree( initialGroupList );

		const result = currentPod( undefined, action );

		expect( result ).toEqual( expected );
	} );

	describe.skip( 'should move groups', () => {
		const cases = [
			[ 0, 0, initialGroupList ], // Nothing changes
			[ -1, 0, initialGroupList ], // oldIndex out of bounds low
			[ 42, 2, initialGroupList ], // oldIndex out of bounds high
			[ 3, -1, initialGroupList ], // newIndex out of bounds low
			[ 1, 42, initialGroupList ], // newIndex out of bounds, high
			[ 0, 1, [ 'one', 'zero', 'two', 'three' ] ],
			[ 0, 2, [ 'one', 'two', 'zero', 'three' ] ],
			[ 0, 3, [ 'one', 'two', 'three', 'zero' ] ],
			[ 3, 2, [ 'zero', 'one', 'three', 'two' ] ],
			[ 3, 1, [ 'zero', 'three', 'one', 'two' ] ],
			[ 3, 0, [ 'three', 'zero', 'one', 'two' ] ],
			[ 1, 2, [ 'zero', 'two', 'one', 'three' ] ],
			[ 2, 1, [ 'zero', 'two', 'one', 'three' ] ],
		];

		test.each( cases )( 'Attempt to move %i to %i', ( oldIndex, newIndex, expected ) => {
			const initialState = GROUPS.tailCreateTree( initialGroupList );
			const action = {
				type: actions.MOVE_GROUP,
				oldIndex,
				newIndex,
			};
			const result = currentPod( initialState, action );

			expect( result ).toEqual( GROUPS.tailCreateTree( expected ) );
		} );
	} );
} );

describe( 'global Reducer', () => {
	it( 'returns an empty object by default', () => {
		expect( global( undefined, undefined ) ).toEqual( {} );
	} );
} );
