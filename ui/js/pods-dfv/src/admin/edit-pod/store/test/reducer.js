import deepFreeze from 'deep-freeze';

import * as paths from '../state-paths';

import {
	podMetaConstants,
	optionConstants,
	uiConstants,
	initialUIState,
} from '../constants';

import {
	podMeta,
	options,
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

		test( 'Should create a new option object if it doesn\'t exist', () => {
			const optionName = 'foo';
			const itemName = 'bar';
			const itemValue = 'baz';
			const action = {
				type: actions.SET_OPTION_ITEM_VALUE,
				optionName: optionName,
				itemName: itemName,
				itemValue: itemValue
			};

			const expected = { [ optionName ]: { [ itemName ]: itemValue } };
			const result = options( undefined, action );

			expect( result ).toBeDefined();
			expect( result ).toEqual( expected );
		} );

		test( 'Should update an existing option item\'s value', () => {
			const optionName = 'foo';
			const itemName = 'bar';
			const itemValue = 'baz';
			const initialState = deepFreeze( {
				[ optionName ]: { name: optionName, [ itemName ]: 'old value' }
			} );
			const action = {
				type: actions.SET_OPTION_ITEM_VALUE,
				optionName: optionName,
				itemName: itemName,
				itemValue: itemValue
			};

			const expected = { [ optionName ]: {
				name: optionName,
				[ itemName ]: itemValue }
			};
			const result = options( initialState, action );

			expect( result ).toBeDefined();
			expect( result ).toEqual( expected );
		} );
	} );

	// Fields
	describe( 'fields', () => {
		it( 'Should return an empty array by default', () => {
			expect( fields( undefined, undefined ) ).toEqual( [] );
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
				tabNames.REST_API
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

			it( 'Should define the SET_SAVE_STATUS action', () => {
				expect( actions.SET_SAVE_STATUS ).toBeDefined();
			} );

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

	} );
} );
