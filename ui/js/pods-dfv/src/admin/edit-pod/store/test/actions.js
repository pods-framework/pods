import {
	uiConstants,
	podMetaConstants,
} from '../constants';

import {
	setPodName,
	setPodMetaValue,
	setSaveStatus,
	setActiveTab,
} from '../actions.js';

describe( 'actions', () => {

	// UI
	describe( 'ui actions', () => {
		const { actions } = uiConstants;

		describe( 'setActiveTab()', () => {
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

		describe( 'setSaveStatus()', () => {
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

		describe( 'setPodName()', () => {
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

		describe( 'setPodMetaValue()', () => {
			const action = actions.SET_POD_META_VALUE;

			it( 'Should define the SET_POD_META_VALUE action', () => {
				expect( actions.SET_POD_META_VALUE ).not.toBeUndefined();
			} );

			it( `Should return ${action} action`, () => {
				const key = 'foo';
				const value = 'bar';
				const expected = {
					type: action,
					key: key,
					value: value,
				};
				const result = setPodMetaValue( key, value );

				expect( result ).toEqual( expected );
			} );
		} );
	} );
} );
