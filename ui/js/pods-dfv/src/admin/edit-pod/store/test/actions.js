import { uiConstants, podMetaConstants, optionConstants } from '../constants';

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
		describe( 'setActiveTab()', () => {
			test( 'The SET_ACTIVE_TAB action should be defined', () => {
				expect( uiConstants.actions.SET_ACTIVE_TAB ).toBeDefined();
			} );

			it( 'Should return the SET_ACTIVE_TAB action', () => {
				const activeTab = uiConstants.tabNames.LABELS;
				const expected = {
					type: uiConstants.actions.SET_ACTIVE_TAB,
					activeTab: activeTab,
				};

				expect( setActiveTab( activeTab ) ).toEqual( expected );
			} );
		} );

		describe( 'setSaveStatus()', () => {
			test( 'The SET_SAVE_STATUS action is defined', () => {
				expect( uiConstants.actions.SET_SAVE_STATUS ).toBeDefined();
			} );

			it( 'Should return the SET_SAVE_STATUS action', () => {
				const saveStatus = uiConstants.saveStatuses.SAVE_SUCCESS;
				const expected = {
					type: uiConstants.actions.SET_SAVE_STATUS,
					saveStatus: saveStatus,
				};

				expect( setSaveStatus( saveStatus ) ).toEqual( expected );
			} );
		} );
	} );

	// Options
	describe( 'option actions', () => {
		describe( 'setOptionValue()/setOptionItemValue()', () => {

			it( 'Should define the SET_OPTION_ITEM_VALUE action', () => {
				expect( optionConstants.actions.SET_OPTION_ITEM_VALUE ).toBeDefined();
			} );

			test( 'setOptionItemValue() should return the SET_OPTION_ITEM_VALUE action', () => {
				const optionName = 'foo';
				const itemName = 'bar';
				const itemValue = 'baz';
				const expected = {
					type: optionConstants.actions.SET_OPTION_ITEM_VALUE,
					optionName: optionName,
					itemName: itemName,
					itemValue: itemValue
				};
				const result = setOptionItemValue( optionName, itemName, itemValue );

				expect( result ).toEqual( expected );
			} );

			test( 'setOptionValue() should return the SET_OPTION_ITEM_VALUE action', () => {
				const name = 'foo';
				const value = 'bar';
				const expected = {
					type: optionConstants.actions.SET_OPTION_ITEM_VALUE,
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
		describe( 'setPodName()', () => {
			test( 'The SET_POD_NAME action is defined', () => {
				expect( podMetaConstants.actions.SET_POD_NAME ).toBeDefined();
			} );

			it( 'Should return SET_POD_NAME action', () => {
				const name = 'xyzzyy';
				const expected = {
					type: podMetaConstants.actions.SET_POD_NAME,
					name: name,
				};

				expect( setPodName( name ) ).toEqual( expected );
			} );
		} );

		describe( 'setPodMetaValue()', () => {
			test( 'The SET_POD_META_VALUE action should be defined', () => {
				expect( podMetaConstants.actions.SET_POD_META_VALUE ).toBeDefined();
			} );

			it( 'Should return SET_POD_META_VALUE action', () => {
				const key = 'foo';
				const value = 'bar';
				const expected = {
					type: podMetaConstants.actions.SET_POD_META_VALUE,
					key: key,
					value: value,
				};
				const result = setPodMetaValue( key, value );

				expect( result ).toEqual( expected );
			} );
		} );
	} );
} );
