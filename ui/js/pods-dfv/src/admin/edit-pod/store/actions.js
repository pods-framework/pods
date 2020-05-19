import {
	uiConstants,
	currentPodConstants,
} from 'pods-dfv/src/admin/edit-pod/store/constants';

const { actions: UI_ACTIONS } = uiConstants;
const { actions: CURRENT_POD_ACTIONS } = currentPodConstants;

// UI
export const setActiveTab = ( activeTab ) => {
	return {
		type: UI_ACTIONS.SET_ACTIVE_TAB,
		activeTab,
	};
};

export const setSaveStatus = ( saveStatus, message = '' ) => {
	return {
		type: UI_ACTIONS.SET_SAVE_STATUS,
		saveStatus,
		message,
	};
};

export const setDeleteStatus = ( deleteStatus, message = '' ) => {
	return {
		type: UI_ACTIONS.SET_DELETE_STATUS,
		deleteStatus,
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

export const setGroupList = ( groupList ) => {
	return {
		type: CURRENT_POD_ACTIONS.SET_GROUP_LIST,
		groupList,
	};
};

export const moveGroup = ( oldIndex, newIndex ) => {
	return {
		type: CURRENT_POD_ACTIONS.MOVE_GROUP,
		oldIndex,
		newIndex,
	};
};

export const addGroupList = ( group ) => {
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
		groupName, field,
	};
};
