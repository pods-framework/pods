import deepFreeze from 'deep-freeze';

import * as paths from '../state-paths';

import {
	podMetaConstants,
	optionConstants,
	groupConstants,
	uiConstants,
	initialUIState,
} from '../constants';

import {
	podMeta,
	options,
	groups,
	fields,
	ui,
} from '../reducer';

describe( 'reducer', () => {
	// Pod Meta
	describe( 'podMeta', () => {
		const { actions } = podMetaConstants;

		it( 'Should return an empty object by default', () => {
			expect( podMeta( undefined, undefined ) ).toEqual( {} );
		} );

		it( 'Should define the SET_POD_NAME action', () => {
			expect( actions.SET_POD_NAME ).toBeDefined();
		} );

		it( 'Should update the pod name', () => {
			const action = {
				type: actions.SET_POD_NAME,
				name: 'plugh',
			};
			const state = podMeta( undefined, action );
			const expected = action.name;
			const result = state.name;

			expect( result ).toBeDefined();
			expect( result ).toEqual( expected );
		} );

		it( 'Should update pod meta values', () => {
			const action = {
				type: actions.SET_POD_META_VALUE,
				key: 'foo',
				value: 'bar',
			};
			const expected = { [ action.key ]: action.value };
			const result = podMeta( undefined, action );

			expect( result ).toBeDefined();
			expect( result ).toEqual( expected );
		} );
	} );

	// Options
	describe( 'options', () => {
		const { actions } = optionConstants;

		it( 'Should return an empty object by default', () => {
			expect( options( undefined, undefined ) ).toEqual( {} );
		} );

		it( 'Should define the SET_OPTION_ITEM_VALUE action', () => {
			expect( actions.SET_OPTION_ITEM_VALUE ).toBeDefined();
		} );

		test( 'When passed a SET_OPTION_ITEM_VALUE action, should create a new options object if it doesn\'t exist', () => {
			const optionName = 'foo';
			const itemName = 'bar';
			const itemValue = 'baz';
			const action = {
				type: actions.SET_OPTION_ITEM_VALUE,
				optionName,
				itemName,
				itemValue,
			};

			const expected = { [ optionName ]: { [ itemName ]: itemValue } };
			const result = options( undefined, action );

			expect( result ).toBeDefined();
			expect( result ).toEqual( expected );
		} );

		test( 'When passed a SET_OPTION_ITEM_VALUE action, should update an existing option item\'s value', () => {
			const optionName = 'foo';
			const itemName = 'bar';
			const itemValue = 'baz';
			const initialState = deepFreeze( {
				[ optionName ]: { name: optionName, [ itemName ]: 'old value' },
			} );
			const action = {
				type: actions.SET_OPTION_ITEM_VALUE,
				optionName,
				itemName,
				itemValue,
			};

			const expected = {
				[ optionName ]: {
					name: optionName,
					[ itemName ]: itemValue,
				},
			};
			const result = options( initialState, action );

			expect( result ).toBeDefined();
			expect( result ).toEqual( expected );
		} );

		test( 'When passed a SET_OPTIONS_VALUES value, should update options based on the provided keys and values', () => {
			const initialState = deepFreeze( {
				first: {
					value: 'Old Value',
					anotherValue: true,
				},
				second: {
					value: 'Another old Value',
					somethingElse: 'something',
				},
				third: {
					value: false,
				},
				fourth: {
					value: 3,
				},
				fifth: {
					value: 'Remains unchanged',
				},
			} );

			const action = {
				type: actions.SET_OPTIONS_VALUES,
				options: {
					first: 'First Value',
					second: 'Second Value',
					third: true,
					fourth: 12,
					sixth: 'New option',
				},
			};

			const expected = {
				first: {
					value: 'First Value',
					anotherValue: true,
				},
				second: {
					value: 'Second Value',
					somethingElse: 'something',
				},
				third: {
					value: true,
				},
				fourth: {
					value: 12,
				},
				fifth: {
					value: 'Remains unchanged',
				},
				sixth: {
					value: 'New option',
				},
			};

			const result = options( initialState, action );

			expect( result ).toEqual( expected );
		} );
	} );

	// Groups
	describe( 'groups', () => {
		const { actions } = groupConstants;
		const { GROUP_LIST } = paths;
		const initialGroupList = [ 'zero', 'one', 'two', 'three' ];

		it( 'Should return an empty object by default', () => {
			expect( groups( undefined, undefined ) ).toEqual( {} );
		} );

		describe( 'set group list', () => {
			const actionType = actions.SET_GROUP_LIST;

			test( 'The action constant should be defined', () => {
				expect( actionType ).toBeDefined();
			} );

			it( 'Should create a new group list if it doesn\'t exist', () => {
				const action = {
					type: actionType,
					groupList: initialGroupList,
				};
				const expected = GROUP_LIST.tailCreateTree( initialGroupList );
				const result = groups( undefined, action );

				expect( result ).toBeDefined();
				expect( result ).toEqual( expected );
			} );
		} );

		describe( 'move group', () => {
			const actionType = actions.MOVE_GROUP;
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

			test( 'The action constant should be defined', () => {
				expect( actionType ).toBeDefined();
			} );

			test.each( cases )( 'Attempt to move %i to %i', ( oldIndex, newIndex, expected ) => {
				const initialState = GROUP_LIST.tailCreateTree( initialGroupList );
				const action = {
					type: actionType,
					oldIndex,
					newIndex,
				};
				const result = groups( initialState, action );

				expect( result ).toEqual( GROUP_LIST.tailCreateTree( expected ) );
			} );
		} );
	} );

	// Fields
	describe( 'fields', () => {
		it( 'Should return an empty object by default', () => {
			expect( fields( undefined, undefined ) ).toEqual( {} );
		} );
	} );

	// UI
	describe( 'ui', () => {
		const { actions } = uiConstants;
		let state;

		it( 'Should have proper defaults', () => {
			state = ui( undefined, undefined );
			expect( state ).toEqual( initialUIState );
		} );

		describe( 'tabs', () => {
			const { tabNames } = uiConstants;
			const orderedList = [
				tabNames.MANAGE_FIELDS,
				tabNames.LABELS,
				tabNames.ADMIN_UI,
				tabNames.ADVANCED_OPTIONS,
				tabNames.AUTO_TEMPLATE_OPTIONS,
				tabNames.REST_API,
			];

			it( 'Should define the proper action', () => {
				expect( actions.SET_ACTIVE_TAB ).toBeDefined();
			} );

			it( 'Should properly change the active tab', () => {
				state = paths.TAB_LIST.tailCreateTree( orderedList );
				const action = {
					type: actions.SET_ACTIVE_TAB,
					activeTab: tabNames.LABELS,
				};
				state = ui( state, action );

				expect( state.activeTab ).toEqual( action.activeTab );
			} );

			it( 'Should use the default for an unknown tab', () => {
				const action = {
					type: actions.SET_ACTIVE_TAB,
					activeTab: 'xyzzy',
				};
				state = ui( state, action );
				expect( state.activeTab ).toEqual( initialUIState.activeTab );
			} );
		} );

		describe( 'save status', () => {
			const { saveStatuses } = uiConstants;

			it( 'Should properly change the save status', () => {
				const action = {
					type: actions.SET_SAVE_STATUS,
					saveStatus: saveStatuses.SAVING,
				};
				state = ui( state, action );
				expect( state.saveStatus ).toEqual( action.saveStatus );
			} );

			it( 'Should use the default for an unknown status', () => {
				const action = {
					type: actions.SET_SAVE_STATUS,
					saveStatus: 'xyzzy',
				};
				state = ui( state, action );
				expect( state.saveStatus ).toEqual( initialUIState.saveStatus );
			} );
		} );

		describe( 'delete status', () => {
			const { deleteStatuses } = uiConstants;

			it( 'Should properly change the delete status', () => {
				const action = {
					type: actions.SET_DELETE_STATUS,
					deleteStatus: deleteStatuses.DELETING,
					message: '',
				};
				state = ui( state, action );
				expect( state.deleteStatus ).toEqual( deleteStatuses.DELETING );
			} );

			it( 'Should use the default for an unknown status', () => {
				const action = {
					type: actions.SET_DELETE_STATUS,
					deleteStatus: 'xyzzy',
				};
				state = ui( state, action );
				expect( state.deleteStatus ).toEqual( initialUIState.deleteStatus );
			} );
		} );
	} );
} );
