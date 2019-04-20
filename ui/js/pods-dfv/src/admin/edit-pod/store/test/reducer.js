import { podMetaConstants, uiConstants } from '../constants';
import {
	podMeta,
	fields,
	labels,
	ui,
	initialUIState
} from '../reducer';

describe( 'reducer', () => {

	describe( 'podMeta', () => {
		const { actions } = podMetaConstants;

		it( 'Should return an empty object by default', () => {
			expect( podMeta( undefined, undefined ) ).toEqual( {} );
		} );

		it( 'Should update the pod name', () => {
			const action = {
				type: actions.SET_POD_NAME,
				podName: 'plugh'
			};
			const state = podMeta( undefined, action );
			expect ( state.podName ).toEqual( action.podName );
		} );
	} );

	describe ( 'fields', () => {
		it( 'Should return an empty array by default', () => {
			expect( fields( undefined, undefined ) ).toEqual( [] );
		} );
	} );


	describe ( 'labels', () => {

	} );


	describe ( 'ui', () => {
		const { actions } = uiConstants;
		let state;

		it( 'Should have proper defaults', () => {
			state = ui( undefined, undefined );
			expect( state ).toEqual( initialUIState );
		} );

		describe ( 'tabs', () => {
			const { tabNames } = uiConstants;

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
			const { saveStatuses } = uiConstants;

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
