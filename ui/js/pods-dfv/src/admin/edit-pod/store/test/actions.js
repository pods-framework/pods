import {
	uiConstants,
	podMetaConstants,
	labelConstants,
} from '../constants';

import {
	setLabelValue,
	setPodName,
	setSaveStatus,
	setActiveTab,
} from '../actions.js';

describe( 'actions', () => {

	// UI
	describe( 'ui actions', () => {
		const { actions } = uiConstants;

		describe( 'setActiveTab', () => {
			const action = actions.SET_ACTIVE_TAB;

			it( 'Should define the SET_ACTIVE_TAB action', () => {
				expect( actions.SET_ACTIVE_TAB ).not.toBeUndefined();
			} );

			it( `Should return ${action} action`, () => {
				const activeTab = uiConstants.tabNames.LABELS;
				const expected = {
					type: action,
					activeTab: activeTab,
				};

				expect( setActiveTab( activeTab ) ).toEqual( expected );
			} );
		} );

		describe( 'setSaveStatus', () => {
			const action = actions.SET_SAVE_STATUS;

			it( 'Should define the SET_SAVE_STATUS action', () => {
				expect( actions.SET_SAVE_STATUS ).not.toBeUndefined();
			} );

			it( `Should return ${action} action`, () => {
				const saveStatus = uiConstants.saveStatuses.SAVE_SUCCESS;
				const expected = {
					type: action,
					saveStatus: saveStatus,
				};

				expect( setSaveStatus( saveStatus ) ).toEqual( expected );
			} );
		} );
	} );

	// Pod meta
	describe( 'pod meta actions', () => {
		const { actions } = podMetaConstants;

		describe( 'setPodName', () => {
			const action = actions.SET_POD_NAME;

			it( 'Should define the SET_POD_NAME action', () => {
				expect( actions.SET_POD_NAME ).not.toBeUndefined();
			} );

			it( `Should return ${action} action`, () => {
				const name = 'xyzzyy';
				const expected = {
					type: action,
					name: name,
				};

				expect( setPodName( name ) ).toEqual( expected );
			} );
		} );
	} );

	// Labels
	describe( 'label actions', () => {
		const { actions } = labelConstants;

		describe( 'setLabelValue', () => {
			const action = actions.SET_LABEL_VALUE;

			it( 'Should define the SET_LABEL_VALUE action', () => {
				expect( actions.SET_LABEL_VALUE ).not.toBeUndefined();
			} );

			it( `Should return ${action} action`, () => {
				const labelName = 'xxx';
				const newValue = 'yyy';
				const expected = {
					type: action,
					labelName: labelName,
					newValue: newValue,
				};
				const result = setLabelValue( labelName, newValue );

				expect( result ).toEqual( expected );
			} );
		} );
	} );
} );
