import {
	SAVE_STATUSES,
	DELETE_STATUSES,
	UI_ACTIONS,
	CURRENT_POD_ACTIONS,
} from '../constants';

import {
	// UI
	setActiveTab,
	setSaveStatus,
	setDeleteStatus,
	setGroupSaveStatus,
	setGroupDeleteStatus,
	// @todo add Field tests:
	// setFieldSaveStatus,
	// setFieldDeleteStatus,

	// Current Pod options
	setPodName,
	setOptionValue,
	setOptionsValues,
	moveGroup,
	addGroup,

	// API
	savePod,
	deletePod,
	saveGroup,
	deleteGroup,

	// setGroupFields,
	// addGroupField,
	// saveField,
	// deleteField,
} from '../actions.js';

import { GROUP, SECOND_TEXT_FIELD } from '../testData';

describe( 'actions', () => {
	// UI
	describe( 'ui actions', () => {
		test( 'setActiveTab() creates an action to set the active tab', () => {
			const expected = {
				type: UI_ACTIONS.SET_ACTIVE_TAB,
				activeTab: 'labels',
			};

			expect( setActiveTab( 'labels' ) ).toEqual( expected );
		} );

		test( 'setSaveStatus() creates a function to create an action to change the save status', () => {
			const expected = {
				type: UI_ACTIONS.SET_SAVE_STATUS,
				saveStatus: SAVE_STATUSES.SAVE_SUCCESS,
				result: {
					message: 'Saved successfully.',
				},
				message: 'Saved successfully.',
			};

			const setSaveStatusSuccess = setSaveStatus( SAVE_STATUSES.SAVE_SUCCESS );

			const result = setSaveStatusSuccess( {
				message: 'Saved successfully.',
			} );

			expect( result ).toEqual( expected );
		} );

		test( 'setDeleteStatus() creates a function to create action to change the delete status', () => {
			const expected = {
				type: UI_ACTIONS.SET_DELETE_STATUS,
				deleteStatus: DELETE_STATUSES.DELETE_SUCCESS,
				result: {
					message: 'Deleted.',
				},
				message: 'Deleted.',
			};

			const setDeleteStatusDeleted = setDeleteStatus( DELETE_STATUSES.DELETE_SUCCESS );

			const result = setDeleteStatusDeleted( {
				message: 'Deleted.',
			} );

			expect( result ).toEqual( expected );
		} );

		test( 'setGroupSaveStatus() creates a function to create an action to change the group\'s save status', () => {
			const expected = {
				type: UI_ACTIONS.SET_GROUP_SAVE_STATUS,
				saveStatus: SAVE_STATUSES.SAVE_SUCCESS,
				result: {},
				message: 'Saved successfully.',
			};

			const result = setGroupSaveStatus(
				SAVE_STATUSES.SAVE_SUCCESS,
			)( {}, 'Saved successfully.' );

			expect( result ).toEqual( expected );
		} );

		test( 'setGroupDeleteStatus() creates a function to create action to change the group\'s delete status', () => {
			const expected = {
				type: UI_ACTIONS.SET_GROUP_DELETE_STATUS,
				deleteStatus: DELETE_STATUSES.DELETING,
				result: {},
				message: 'Deleted.',
			};

			const result = setGroupDeleteStatus(
				DELETE_STATUSES.DELETING,
			)( {}, 'Deleted.' );

			expect( result ).toEqual( expected );
		} );
	} );

	// Current Pod Options
	describe( 'option actions', () => {
		test( 'setPodName() should return an action to set the pod name', () => {
			const action = CURRENT_POD_ACTIONS.SET_POD_NAME;
			const name = 'xyzzyy';

			const expected = {
				type: action,
				name,
			};

			expect( setPodName( name ) ).toEqual( expected );
		} );

		test( 'setOptionValue() should return an action to set the option value', () => {
			const action = CURRENT_POD_ACTIONS.SET_OPTION_VALUE;
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
			const action = CURRENT_POD_ACTIONS.SET_OPTIONS_VALUES;

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
			const action = CURRENT_POD_ACTIONS.ADD_GROUP;

			const result = {
				group: {
					id: 115,
					name: 'new_group_name',
					label: 'New Group Name 123',
				},
			};

			const expected = {
				type: action,
				result,
			};

			expect( addGroup( result ) ).toEqual( expected );
		} );

		test( 'moveGroup() should return an action to move a group', () => {
			const oldIndex = 3;
			const newIndex = 1;

			const expected = {
				type: CURRENT_POD_ACTIONS.MOVE_GROUP,
				oldIndex,
				newIndex,
			};

			const result = moveGroup( oldIndex, newIndex );
			expect( result ).toEqual( expected );
		} );

		test( 'savePod() returns an action to save a pod by its ID', () => {
			const data = {
				// The following values should be stripped out.
				id: 117,
				label: 'Test Pod Label',
				name: 'test-pod-name',
				object_type: 'pod',
				storage: 'test',
				storage_type: 'collection',
				type: 'post_type',
				_locale: 'user',
				// The following should be included.
				label_add_new: 'Add New Something',
				rest_enable: '0',
				description: 'Test',
				// Groups/fields will change into the "order" data.
				groups: [
					GROUP,
					{
						...GROUP,
						id: 123,
						name: 'another-group',
						fields: [ SECOND_TEXT_FIELD ],
					},
				],
			};

			const result = savePod( data, 123 );

			expect( result.type ).toEqual( CURRENT_POD_ACTIONS.API_REQUEST );
			expect( result.payload.data ).toEqual( {
				label: 'Test Pod Label',
				name: 'test-pod-name',
				args: {
					label_add_new: 'Add New Something',
					rest_enable: '0',
					description: 'Test',
				},
				order: {
					groups: [
						{
							group_id: 122,
							fields: [ 119 ],
						},
						{
							group_id: 123,
							fields: [ 139 ],
						},
					],
				},
			} );
			expect( result.payload.onSuccess[ 0 ]().type ).toEqual( UI_ACTIONS.SET_SAVE_STATUS );
			expect( result.payload.onSuccess[ 1 ]().type ).toEqual( CURRENT_POD_ACTIONS.SET_OPTIONS_VALUES );
			expect( result.payload.onFailure().type ).toEqual( UI_ACTIONS.SET_SAVE_STATUS );
			expect( result.payload.onStart().type ).toEqual( UI_ACTIONS.SET_SAVE_STATUS );
		} );

		test( 'deletePod() returns an action to delete a pod by its ID', () => {
			const action = CURRENT_POD_ACTIONS.API_REQUEST;

			const result = deletePod( 123 );

			expect( result.type ).toEqual( action );
			expect( result.payload.onSuccess().type ).toEqual( UI_ACTIONS.SET_DELETE_STATUS );
			expect( result.payload.onFailure().type ).toEqual( UI_ACTIONS.SET_DELETE_STATUS );
			expect( result.payload.onStart().type ).toEqual( UI_ACTIONS.SET_DELETE_STATUS );
		} );

		test( 'saveGroup() returns an action to save a pod by its ID', () => {
			const action = CURRENT_POD_ACTIONS.API_REQUEST;

			const result = saveGroup( 123 );

			expect( result.type ).toEqual( action );
			expect( result.payload.onSuccess[ 0 ]().type ).toEqual( UI_ACTIONS.SET_GROUP_SAVE_STATUS );
			expect( result.payload.onSuccess[ 1 ]().type ).toEqual( CURRENT_POD_ACTIONS.ADD_GROUP );
			expect( result.payload.onFailure().type ).toEqual( UI_ACTIONS.SET_GROUP_SAVE_STATUS );
			expect( result.payload.onStart().type ).toEqual( UI_ACTIONS.SET_GROUP_SAVE_STATUS );
		} );

		test( 'deleteGroup() returns an action to delete a group by its ID', () => {
			const action = CURRENT_POD_ACTIONS.API_REQUEST;

			const result = deleteGroup( 123 );

			expect( result.type ).toEqual( action );
			expect( result.payload.onSuccess().type ).toEqual( UI_ACTIONS.SET_GROUP_DELETE_STATUS );
			expect( result.payload.onFailure().type ).toEqual( UI_ACTIONS.SET_GROUP_DELETE_STATUS );
			expect( result.payload.onStart().type ).toEqual( UI_ACTIONS.SET_GROUP_DELETE_STATUS );
		} );
	} );
} );
