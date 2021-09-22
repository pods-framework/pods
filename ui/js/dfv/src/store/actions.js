import { omit } from 'lodash';

import {
	SAVE_STATUSES,
	DELETE_STATUSES,
	UI_ACTIONS,
	CURRENT_POD_ACTIONS,
} from 'dfv/src/store/constants';

// UI
export const setActiveTab = ( activeTab ) => {
	return {
		type: UI_ACTIONS.SET_ACTIVE_TAB,
		activeTab,
	};
};

export const setSaveStatus = ( saveStatus ) => ( result = {} ) => {
	return {
		type: UI_ACTIONS.SET_SAVE_STATUS,
		saveStatus,
		result,
	};
};

export const setDeleteStatus = ( deleteStatus ) => ( result = {} ) => {
	return {
		type: UI_ACTIONS.SET_DELETE_STATUS,
		deleteStatus,
		result,
	};
};

export const setGroupSaveStatus = ( saveStatus, previousGroupName ) => ( result = {} ) => {
	return {
		type: UI_ACTIONS.SET_GROUP_SAVE_STATUS,
		// The group name may not have changed, but we call it "previousGroupName" just
		// in case it has so that the old one is changed.
		previousGroupName,
		saveStatus,
		result,
	};
};

export const resetGroupSaveStatus = ( groupName ) => {
	return {
		type: UI_ACTIONS.SET_GROUP_SAVE_STATUS,
		previousGroupName: groupName,
		saveStatus: SAVE_STATUSES.NONE,
		result: {},
	};
};

export const setGroupDeleteStatus = ( deleteStatus, name ) => ( result = {} ) => {
	return {
		type: UI_ACTIONS.SET_GROUP_DELETE_STATUS,
		name,
		deleteStatus,
		result,
	};
};

export const setFieldSaveStatus = ( saveStatus, previousFieldName ) => ( result = {} ) => {
	return {
		type: UI_ACTIONS.SET_FIELD_SAVE_STATUS,
		previousFieldName,
		saveStatus,
		result,
	};
};

export const resetFieldSaveStatus = ( fieldName ) => {
	return {
		type: UI_ACTIONS.SET_FIELD_SAVE_STATUS,
		previousFieldName: fieldName,
		saveStatus: SAVE_STATUSES.NONE,
		result: {},
	};
};

export const setFieldDeleteStatus = ( deleteStatus, name ) => ( result = {} ) => {
	return {
		type: UI_ACTIONS.SET_FIELD_DELETE_STATUS,
		name,
		deleteStatus,
		result,
	};
};

// Options
export const setPodName = ( name ) => {
	return {
		type: CURRENT_POD_ACTIONS.SET_POD_NAME,
		name,
	};
};

export const setOptionValue = ( optionName, value ) => {
	return {
		type: CURRENT_POD_ACTIONS.SET_OPTION_VALUE,
		optionName,
		value,
	};
};

export const setOptionsValues = ( options = {} ) => {
	return {
		type: CURRENT_POD_ACTIONS.SET_OPTIONS_VALUES,
		options,
	};
};

export const refreshPodData = ( data ) => setOptionsValues( data?.pod || {} );

export const moveGroup = ( oldIndex, newIndex ) => {
	return {
		type: CURRENT_POD_ACTIONS.MOVE_GROUP,
		oldIndex,
		newIndex,
	};
};

export const addGroup = ( result ) => {
	return {
		type: CURRENT_POD_ACTIONS.ADD_GROUP,
		result,
	};
};

export const removeGroup = ( groupID ) => {
	return {
		type: CURRENT_POD_ACTIONS.REMOVE_GROUP,
		groupID,
	};
};

export const setGroupData = ( result = {} ) => {
	return {
		type: CURRENT_POD_ACTIONS.SET_GROUP_DATA,
		result,
	};
};

export const setGroupFields = ( groupName, fields ) => {
	return {
		type: CURRENT_POD_ACTIONS.SET_GROUP_FIELDS,
		groupName,
		fields,
	};
};

export const addGroupField = ( groupName, index ) => ( result ) => {
	return {
		type: CURRENT_POD_ACTIONS.ADD_GROUP_FIELD,
		groupName,
		index,
		result,
	};
};

export const removeGroupField = ( groupID, fieldID ) => {
	return {
		type: CURRENT_POD_ACTIONS.REMOVE_GROUP_FIELD,
		groupID,
		fieldID,
	};
};

export const setGroupFieldData = ( groupName ) => ( result = {} ) => {
	return {
		type: CURRENT_POD_ACTIONS.SET_GROUP_FIELD_DATA,
		groupName,
		result,
	};
};

// API
export const savePod = ( data, podId ) => {
	// Not all values that exist need to be passed.
	const relevantArgs = omit(
		data,
		[
			'id',
			'label',
			'name',
			'object_type',
			'storage',
			'storage_type',
			'type',
			'_locale',
			'groups',
		]
	);

	const order = {
		groups: ( data.groups || [] ).map( ( group ) => {
			return {
				group_id: group.id,
				fields: ( group.fields || [] ).map( ( field ) => field.id ),
			};
		} ),
	};

	const cleanedData = {
		name: data.name || '',
		label: data.label || '',
		args: relevantArgs,
		order,
	};

	return {
		type: CURRENT_POD_ACTIONS.API_REQUEST,
		payload: {
			url: podId ? `/pods/v1/pods/${ podId }` : '/pods/v1/pods',
			method: 'POST',
			data: cleanedData,
			onSuccess: [
				setSaveStatus( SAVE_STATUSES.SAVE_SUCCESS ),
				refreshPodData,
			],
			onFailure: setSaveStatus( SAVE_STATUSES.SAVE_ERROR ),
			onStart: setSaveStatus( SAVE_STATUSES.SAVING ),
		},
	};
};

export const deletePod = ( podId ) => {
	return {
		type: CURRENT_POD_ACTIONS.API_REQUEST,
		payload: {
			url: `/pods/v1/pods/${ podId }`,
			method: 'DELETE',
			onSuccess: setDeleteStatus( DELETE_STATUSES.DELETE_SUCCESS ),
			onFailure: setDeleteStatus( DELETE_STATUSES.DELETE_ERROR ),
			onStart: setDeleteStatus( DELETE_STATUSES.DELETING ),
		},
	};
};

export const saveGroup = (
	podID,
	previousName,
	name,
	label,
	args = {},
	groupId
) => {
	return {
		type: CURRENT_POD_ACTIONS.API_REQUEST,
		payload: {
			url: groupId ? `/pods/v1/groups/${ groupId }` : '/pods/v1/groups',
			method: 'POST',
			data: {
				pod_id: podID.toString(),
				name,
				label,
				args,
			},
			onSuccess: [
				setGroupSaveStatus( SAVE_STATUSES.SAVE_SUCCESS, previousName ),
				groupId ? setGroupData : addGroup,
			],
			onFailure: setGroupSaveStatus( SAVE_STATUSES.SAVE_ERROR, previousName ),
			onStart: setGroupSaveStatus( SAVE_STATUSES.SAVING, previousName ),
		},
	};
};

export const deleteGroup = ( groupId, name ) => {
	return {
		type: CURRENT_POD_ACTIONS.API_REQUEST,
		payload: {
			url: `/pods/v1/groups/${ groupId }`,
			method: 'DELETE',
			onSuccess: setGroupDeleteStatus( DELETE_STATUSES.DELETE_SUCCESS, name ),
			onFailure: setGroupDeleteStatus( DELETE_STATUSES.DELETE_ERROR, name ),
			onStart: setGroupDeleteStatus( DELETE_STATUSES.DELETING, name ),
		},
	};
};

export const saveField = (
	podID,
	groupID,
	groupName,
	previousName,
	name,
	label,
	type,
	args,
	fieldID,
	index = null
) => {
	return {
		type: CURRENT_POD_ACTIONS.API_REQUEST,
		payload: {
			url: fieldID ? `/pods/v1/fields/${ fieldID }` : '/pods/v1/fields',
			method: 'POST',
			data: {
				pod_id: podID.toString(),
				group_id: groupID.toString(),
				name,
				label,
				type,
				args,
			},
			onSuccess: [
				setFieldSaveStatus( SAVE_STATUSES.SAVE_SUCCESS, previousName ),
				fieldID ? setGroupFieldData( groupName ) : addGroupField( groupName, index ),
			],
			onFailure: setFieldSaveStatus( SAVE_STATUSES.SAVE_ERROR, previousName ),
			onStart: setFieldSaveStatus( SAVE_STATUSES.SAVING, previousName ),
		},
	};
};

export const deleteField = ( fieldID, name ) => {
	return {
		type: CURRENT_POD_ACTIONS.API_REQUEST,
		payload: {
			url: `/pods/v1/fields/${ fieldID }`,
			method: 'DELETE',
			onSuccess: setFieldDeleteStatus( DELETE_STATUSES.DELETE_SUCCESS, name ),
			onFailure: setFieldDeleteStatus( DELETE_STATUSES.DELETE_ERROR, name ),
			onStart: setFieldDeleteStatus( DELETE_STATUSES.DELETING, name ),
		},
	};
};
