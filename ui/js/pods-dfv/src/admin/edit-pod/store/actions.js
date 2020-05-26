import { omit } from 'lodash';

import {
	uiConstants,
	currentPodConstants,
} from 'pods-dfv/src/admin/edit-pod/store/constants';

const {
	actions: UI_ACTIONS,
	saveStatuses: SAVE_STATUSES,
	deleteStatuses: DELETE_STATUSES,
} = uiConstants;

const { actions: CURRENT_POD_ACTIONS } = currentPodConstants;

// UI
export const setActiveTab = ( activeTab ) => {
	return {
		type: UI_ACTIONS.SET_ACTIVE_TAB,
		activeTab,
	};
};

export const setSaveStatus = ( saveStatus ) => ( result = {}, message = '' ) => {
	return {
		type: UI_ACTIONS.SET_SAVE_STATUS,
		saveStatus,
		result,
		message,
	};
};

export const setDeleteStatus = ( deleteStatus ) => ( result = {}, message = '' ) => {
	return {
		type: UI_ACTIONS.SET_DELETE_STATUS,
		deleteStatus,
		result,
		message,
	};
};

export const setGroupSaveStatus = ( saveStatus ) => ( result = {}, message = '' ) => {
	return {
		type: UI_ACTIONS.SET_GROUP_SAVE_STATUS,
		saveStatus,
		result,
		message,
	};
};

export const setGroupDeleteStatus = ( deleteStatus ) => ( result = {}, message = '' ) => {
	return {
		type: UI_ACTIONS.SET_GROUP_DELETE_STATUS,
		deleteStatus,
		result,
		message,
	};
};

export const setFieldSaveStatus = ( saveStatus ) => ( result = {}, message = '' ) => {
	return {
		type: UI_ACTIONS.SET_FIELD_SAVE_STATUS,
		saveStatus,
		result,
		message,
	};
};

export const setFieldDeleteStatus = ( deleteStatus ) => ( result = {}, message = '' ) => {
	return {
		type: UI_ACTIONS.SET_FIELD_DELETE_STATUS,
		deleteStatus,
		result,
		message,
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

export const addGroup = ( group ) => {
	return {
		type: CURRENT_POD_ACTIONS.ADD_GROUP,
		group,
	};
};

export const setGroupFields = ( groupName, fields ) => {
	return {
		type: CURRENT_POD_ACTIONS.SET_GROUP_FIELDS,
		groupName, fields,
	};
};

export const addGroupField = ( groupName, field ) => {
	return {
		type: CURRENT_POD_ACTIONS.ADD_GROUP_FIELD,
		groupName,
		field,
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
		]
	);

	const cleanedData = {
		name: data.name || '',
		label: data.label || '',
		args: relevantArgs,
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

export const saveGroup = ( data, groupId ) => {
	return {
		type: CURRENT_POD_ACTIONS.API_REQUEST,
		payload: {
			url: groupId ? `/pods/v1/groups/${ groupId }` : '/pods/v1/groups',
			method: 'POST',
			data,
			onSuccess: setGroupSaveStatus( SAVE_STATUSES.SAVE_SUCCESS ),
			onFailure: setGroupSaveStatus( SAVE_STATUSES.SAVE_ERROR ),
			onStart: setGroupSaveStatus( SAVE_STATUSES.SAVING ),
		},
	};
};

export const deleteGroup = ( groupId ) => {
	return {
		type: CURRENT_POD_ACTIONS.API_REQUEST,
		payload: {
			url: `/pods/v1/groups/${ groupId }`,
			method: 'DELETE',
			onSuccess: setGroupDeleteStatus( DELETE_STATUSES.DELETE_SUCCESS ),
			onFailure: setGroupDeleteStatus( DELETE_STATUSES.DELETE_ERROR ),
			onStart: setGroupDeleteStatus( DELETE_STATUSES.DELETING ),
		},
	};
};

export const saveField = ( data, fieldId ) => {
	return {
		type: CURRENT_POD_ACTIONS.API_REQUEST,
		payload: {
			url: fieldId ? `/pods/v1/fields/${ fieldId }` : '/pods/v1/fields',
			method: 'POST',
			data,
			onSuccess: setFieldSaveStatus( SAVE_STATUSES.SAVE_SUCCESS ),
			onFailure: setFieldSaveStatus( SAVE_STATUSES.SAVE_ERROR ),
			onStart: setFieldSaveStatus( SAVE_STATUSES.SAVING ),
		},
	};
};

export const deleteField = ( data, fieldId ) => {
	return {
		type: CURRENT_POD_ACTIONS.API_REQUEST,
		payload: {
			url: `/pods/v1/fields/${ fieldId }`,
			method: 'DELETE',
			onSuccess: setFieldDeleteStatus( DELETE_STATUSES.DELETE_SUCCESS ),
			onFailure: setFieldDeleteStatus( DELETE_STATUSES.DELETE_ERROR ),
			onStart: setFieldDeleteStatus( DELETE_STATUSES.DELETING ),
		},
	};
};
