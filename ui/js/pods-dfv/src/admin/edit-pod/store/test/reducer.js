import {
	fields,
	labels,
	ui,
	initialUIState
} from '../reducer';
import { uiConstants } from 'pods-dfv/src/admin/edit-pod/store/constants';

describe( 'reducer', () => {

	describe ( 'fields', () => {
		it( 'Should return an empty array by default', () => {
			expect( fields( undefined, undefined ) ).toEqual( [] );
		} );
	} );


	describe ( 'labels', () => {

	} );


	describe ( 'ui', () => {
		const { actions, saveStatuses, tabNames } = uiConstants;
		let state;

		it( 'Should have proper defaults', () => {
			state = ui( undefined, undefined );
			expect( state ).toEqual( initialUIState );
		} );

		describe ( 'tabs', () => {
			it( 'Should properly change the active tab', () => {
				const action = {
					type: actions.SET_ACTIVE_TAB,
					activeTab: tabNames.LABELS
				};
				state = ui( state, action );

				expect( state.activeTab ).toEqual( action.activeTab );
			} );

			it( 'Should use the default for an unknown tab', () => {
				const action = {
					type: actions.SET_ACTIVE_TAB,
					activeTab: 'xyzzy'
				};
				state = ui( state, action );

				expect( state.activeTab ).toEqual( initialUIState.activeTab );
			} );
		} );

		describe ( 'save status', () => {
			it( 'Should properly change the save status', () => {
				const action = {
					type: actions.SET_SAVE_STATUS,
					saveStatus: saveStatuses.SAVING
				};
				state = ui( state, action );
				expect ( state.saveStatus ).toEqual( action.saveStatus );
			} );

			it( 'Should use the default for an unknown status', () => {
				const action = {
					type: actions.SET_SAVE_STATUS,
					saveStatus: 'xyzzy'
				};
				state = ui( state, action );
				expect( state.saveStatus ).toEqual( initialUIState.saveStatus );
			} );
		} );

	} );
} );
