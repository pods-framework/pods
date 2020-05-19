import {
	uiConstants,
	currentPodConstants,
} from '../constants';

import {
	// UI
	setActiveTab,
	setSaveStatus,
	setDeleteStatus,

	// Current Pod options
	setPodName,
	setOptionValue,
	setOptionsValues,
	setGroupList,
	moveGroup,

	// @todo create these tests when working on the Manage Groups functionality
	addGroup,
	// setGroupFields,
	// addGroupField,
} from '../actions.js';

describe( 'actions', () => {
	// UI
	describe( 'ui actions', () => {
		const {
			actions,
			saveStatuses,
			deleteStatuses,
		} = uiConstants;

		test( 'setActiveTab() creates an action to set the active tab', () => {
			const expected = {
				type: actions.SET_ACTIVE_TAB,
				activeTab: 'labels',
			};

			expect( setActiveTab( 'labels' ) ).toEqual( expected );
		} );

		test( 'setSaveStatus() creates an action to change the save status', () => {
			const expected = {
				type: actions.SET_SAVE_STATUS,
				saveStatus: saveStatuses.SAVE_SUCCESS,
				message: 'Saved successfully.',
			};

			const result = setSaveStatus(
				saveStatuses.SAVE_SUCCESS,
				'Saved successfully.'
			);

			expect( result ).toEqual( expected );
		} );

		test( 'setDeleteStatus() creates an action to change the delete status', () => {
			const expected = {
				type: actions.SET_DELETE_STATUS,
				deleteStatus: deleteStatuses.DELETING,
				message: 'Deleted.',
			};

			const result = setDeleteStatus(
				deleteStatuses.DELETING,
				'Deleted.'
			);

			expect( result ).toEqual( expected );
		} );
	} );

	// Current Pod Options
	describe( 'option actions', () => {
		const { actions } = currentPodConstants;

		test( 'setPodName() should return an action to set the pod name', () => {
			const action = actions.SET_POD_NAME;
			const name = 'xyzzyy';

			const expected = {
				type: action,
				name,
			};

			expect( setPodName( name ) ).toEqual( expected );
		} );

		test( 'setOptionValue() should return an action to set the option value', () => {
			const action = actions.SET_OPTION_VALUE;
			const name = 'foo';
			const value = 'bar';

			const expected = {
				type: action,
				optionName: name,
				value,
			};

			expect( setOptionValue( name, value ) ).toEqual( expected );
		} );

		test( 'setOptionsValues()', () => {
			const action = actions.SET_OPTIONS_VALUES;

			const newOptions = {
				first: 'First Value',
				second: 'Second Value',
				third: true,
				fourth: 12,
			};

			const expected = {
				type: action,
				options: {
					first: 'First Value',
					second: 'Second Value',
					third: true,
					fourth: 12,
				},
			};

			expect( setOptionsValues( newOptions ) ).toEqual( expected );
		} );

		test( 'addGroup() returns an action to create a new group', () => {
			const action = actions.ADD_GROUP;

			const expected = {
				type: action,
				group: 'New Group Name 123',
			};

			expect( addGroup( 'New Group Name 123' ) ).toEqual( expected );
		} );

		test( 'setGroupList() should return an action to set the group list', () => {
			const action = actions.SET_GROUP_LIST;
			const groupList = [ 'foo', 'bar', 'baz' ];

			const expected = {
				type: action,
				groupList,
			};

			expect( setGroupList( groupList ) ).toEqual( expected );
		} );

		test( 'moveGroup() should return an action to move a group', () => {
			const action = actions.MOVE_GROUP;
			const oldIndex = 3;
			const newIndex = 1;

			const expected = {
				type: action,
				oldIndex,
				newIndex,
			};

			const result = moveGroup( oldIndex, newIndex );
			expect( result ).toEqual( expected );
		} );
	} );
} );
