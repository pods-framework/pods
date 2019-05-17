import { uiConstants, podMetaConstants, optionConstants, groupConstants } from '../constants';

import {
	setPodName,
	setPodMetaValue,
	setSaveStatus,
	setActiveTab,
	setOptionValue,
	setOptionItemValue,
	setGroupList,
	moveGroup,
} from '../actions.js';

describe( 'actions', () => {

	// UI
	describe( 'ui actions', () => {
		const { actions, tabNames, saveStatuses } = uiConstants;

		describe( 'setActiveTab()', () => {
			const action = actions.SET_ACTIVE_TAB;

			it( 'Should define the action constant', () => {
				expect( action ).toBeDefined();
			} );

			it( 'Should return the correct action', () => {
				const activeTab = tabNames.LABELS;
				const expected = {
					type: action,
					activeTab: activeTab,
				};

				expect( setActiveTab( activeTab ) ).toEqual( expected );
			} );
		} );

		describe( 'setSaveStatus()', () => {
			const action = actions.SET_SAVE_STATUS;

			it( 'Should define the action constant', () => {
				expect( action ).toBeDefined();
			} );

			it( 'Should return the correct action', () => {
				const saveStatus = saveStatuses.SAVE_SUCCESS;
				const expected = {
					type: action,
					saveStatus: saveStatus,
				};

				expect( setSaveStatus( saveStatus ) ).toEqual( expected );
			} );
		} );
	} );

	// Options
	describe( 'option actions', () => {
		const { actions } = optionConstants;

		describe( 'setOptionValue()/setOptionItemValue()', () => {
			const action = actions.SET_OPTION_ITEM_VALUE;

			it( 'Should define the action constant', () => {
				expect( action ).toBeDefined();
			} );

			test( 'setOptionItemValue() should return the correct action', () => {
				const optionName = 'foo';
				const itemName = 'bar';
				const itemValue = 'baz';
				const expected = {
					type: action,
					optionName: optionName,
					itemName: itemName,
					itemValue: itemValue
				};
				const result = setOptionItemValue( optionName, itemName, itemValue );

				expect( result ).toEqual( expected );
			} );

			test( 'setOptionValue() should return the correct action', () => {
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

	// Groups
	describe( 'group actions', () => {
		const { actions } = groupConstants;

		describe( 'setGroupList()', () => {
			const action = actions.SET_GROUP_LIST;

			test( 'The action constant is defined', () => {
				expect( action ).toBeDefined();
			} );

			test( 'setGroupList() should return the correct action', () => {
				const groupList = [ 'foo', 'bar', 'baz' ];
				const expected = {
					type: action,
					groupList: groupList
				};

				const result = setGroupList( groupList );
				expect( result ).toEqual( expected );
			} );
		} );

		describe( 'moveGroup()', () => {
			const action = actions.MOVE_GROUP;

			test( 'The action constant is defined', () => {
				expect( action ).toBeDefined();
			} );

			test( 'moveGroup() should return the correct action', () => {
				const oldIndex = 3;
				const newIndex = 1;
				const expected = {
					type: action,
					oldIndex,
					newIndex
				};

				const result = moveGroup( oldIndex, newIndex );
				expect( result ).toEqual( expected );
			} );
		} );
	} );

	// Pod meta
	describe( 'pod meta actions', () => {
		const { actions } = podMetaConstants;

		describe( 'setPodName()', () => {
			const action = actions.SET_POD_NAME;

			it( 'Should define the action constant', () => {
				expect( action ).toBeDefined();
			} );

			it( 'Should return the correct action', () => {
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

			it( 'Should define the action constant', () => {
				expect( action ).toBeDefined();
			} );

			it( 'Should return the correct action', () => {
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
