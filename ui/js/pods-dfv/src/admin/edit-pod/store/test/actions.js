import { uiConstants, podMetaConstants } from '../constants';

import {
	setPodName,
	setPodMetaValue,
	setSaveStatus,
	setActiveTab,
	setOptionValue,
	setOptionItemValue,
} from '../actions.js';

describe( 'actions', () => {

	// UI
	describe( 'ui actions', () => {
		const { actions } = uiConstants;

		describe( 'setActiveTab()', () => {
			const action = actions.SET_ACTIVE_TAB;

			it( 'Should define the SET_ACTIVE_TAB action', () => {
				expect( actions.SET_ACTIVE_TAB ).toBeDefined();
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
				expect( actions.SET_SAVE_STATUS ).toBeDefined();
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

		describe( 'setOptionValue()/setOptionItemValue()', () => {
			const action = actions.SET_OPTION_ITEM_VALUE;

			it( 'Should define the SET_OPTION_ITEM_VALUE action', () => {
				expect( actions.SET_OPTION_ITEM_VALUE ).toBeDefined();
			} );

			test( `setOptionItemValue() should return ${action} action`, () => {
				const optionName = 'foo';
				const itemName = 'bar';
				const itemValue = 'baz';
				const expected = {
					type: action,
					optionName: optionName,
					itemName: itemName,
					itemValue: itemValue
				};
				const result = setOptionItemValue( optionName, itemName, itemValue);

				expect( result ).toEqual( expected );
			} );

			test( `setOptionValue() should return ${action} action`, () => {
				const name = 'foo';
				const value = 'bar';
				const expected = {
					type: action,
					optionName: name,
					itemName: 'value',
					itemValue: value
				};

				expect( setOptionValue( name, value ) ).toEqual( expected );
			} );
		} );
	} );

	// Pod meta
	describe( 'pod meta actions', () => {
		const { actions } = podMetaConstants;

		describe( 'setPodName()', () => {
			const action = actions.SET_POD_NAME;

			it( 'Should define the SET_POD_NAME action', () => {
				expect( actions.SET_POD_NAME ).toBeDefined();
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
				expect( actions.SET_POD_META_VALUE ).toBeDefined();
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
