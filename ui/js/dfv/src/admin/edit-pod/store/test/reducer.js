import deepFreeze from 'deep-freeze';

import * as paths from '../state-paths';

import {
	SAVE_STATUSES,
	DELETE_STATUSES,
	CURRENT_POD_ACTIONS,
	UI_ACTIONS,
	INITIAL_UI_STATE,
} from '../constants';

import {
	ui,
	currentPod,
	global,
} from '../reducer';

describe( 'UI reducer', () => {
	let state;

	beforeEach( () => {
		state = { ...INITIAL_UI_STATE };
	} );

	afterEach( () => {
		state = undefined;
	} );

	it( 'has the proper defaults', () => {
		const newState = ui( undefined, undefined );

		expect( newState ).toEqual( INITIAL_UI_STATE );
	} );

	it( 'changes the active tab', () => {
		const action = {
			type: UI_ACTIONS.SET_ACTIVE_TAB,
			activeTab: 'labels',
		};

		const newState = ui( state, action );

		expect( newState.activeTab ).toEqual( 'labels' );
	} );

	it( 'changes the save status', () => {
		const action = {
			type: UI_ACTIONS.SET_SAVE_STATUS,
			saveStatus: SAVE_STATUSES.SAVING,
			message: 'Saving...',
		};

		const newState = ui( state, action );

		expect( newState.saveStatus ).toEqual( action.saveStatus );
		expect( newState.saveMessage ).toEqual( 'Saving...' );
	} );

	it( 'uses the default for an unknown save status', () => {
		const action = {
			type: UI_ACTIONS.SET_SAVE_STATUS,
			saveStatus: 'xyzzy',
		};

		const newState = ui( state, action );

		expect( newState.saveStatus ).toEqual( INITIAL_UI_STATE.saveStatus );
	} );

	it( 'changes the delete status', () => {
		const action = {
			type: UI_ACTIONS.SET_DELETE_STATUS,
			deleteStatus: DELETE_STATUSES.DELETING,
			message: 'Deleting...',
		};

		const newState = ui( state, action );

		expect( newState.deleteStatus ).toEqual( DELETE_STATUSES.DELETING );
		expect( newState.deleteMessage ).toEqual( 'Deleting...' );
	} );

	it( 'uses the default for an unknown delete status', () => {
		const action = {
			type: UI_ACTIONS.SET_DELETE_STATUS,
			deleteStatus: 'xyzzy',
		};

		const newState = ui( state, action );

		expect( newState.deleteStatus ).toEqual( INITIAL_UI_STATE.deleteStatus );
	} );
} );

describe( 'currentPod Reducer', () => {
	const { GROUPS } = paths;

	it( 'returns an empty object by default', () => {
		expect( currentPod( undefined, undefined ) ).toEqual( {} );
	} );

	it( 'updates the pod name', () => {
		const action = {
			type: CURRENT_POD_ACTIONS.SET_POD_NAME,
			name: 'plugh',
		};

		const newState = currentPod( undefined, action );

		expect( newState.name ).toEqual( 'plugh' );
	} );

	it( 'Should update pod options', () => {
		const action = {
			type: CURRENT_POD_ACTIONS.SET_OPTION_VALUE,
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
			type: CURRENT_POD_ACTIONS.SET_OPTIONS_VALUES,
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

	describe( 'should move groups', () => {
		const initialGroupList = [ 'zero', 'one', 'two', 'three' ];

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
				type: CURRENT_POD_ACTIONS.MOVE_GROUP,
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
