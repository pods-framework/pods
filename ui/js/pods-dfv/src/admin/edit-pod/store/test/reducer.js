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

			it( 'Should define the SET_ACTIVE_TAB action', () => {
				expect( actions.SET_ACTIVE_TAB ).toBeDefined();
			} );

			it( 'Should properly change the active tab', () => {
				state = ui( { tabs: { orderedList: orderedList } } );
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
