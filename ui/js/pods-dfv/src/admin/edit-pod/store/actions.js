import {
	uiConstants,
	optionConstants,
	groupConstants,
	podMetaConstants,
} from 'pods-dfv/src/admin/edit-pod/store/constants';

/**
 * UI
 */
export const setActiveTab = ( activeTab ) => {
	return {
		type: uiConstants.actions.SET_ACTIVE_TAB,
		activeTab
	};
};

export const setSaveStatus = ( saveStatus ) => {
	return {
		type: uiConstants.actions.SET_SAVE_STATUS,
		saveStatus
	};
};

/**
 * Options
 */
export const setOptionValue = ( name, value ) => setOptionItemValue( name, 'value', value );
export const setOptionItemValue = ( optionName, itemName, itemValue ) => {
	return {
		type: optionConstants.actions.SET_OPTION_ITEM_VALUE,
		optionName,
		itemName,
		itemValue
	};
};

/**
 * Groups
 */
export const setGroupList = ( groupList ) => {
	return {
		type: groupConstants.actions.SET_GROUP_LIST,
		groupList
	};
};

export const moveGroup = ( oldIndex, newIndex ) => {
	return {
		type: groupConstants.actions.MOVE_GROUP,
		oldIndex,
		newIndex
	};
};

export const addGroupList = ( group ) => {
	return {
		type: groupConstants.actions.ADD_GROUP,
		group
	}
}

export const setGroupFields = (groupName, fields) => {
	return {
		type: groupConstants.actions.SET_GROUP_FIELDS,
		groupName, fields
	}
}

export const addGroupField = (groupName, field) => {
	return {
		type: groupConstants.actions.ADD_GROUP_FIELD,
		groupName, field
	}
}

/**
 * Pod meta
 */
export const setPodName = ( name ) => {
	return {
		type: podMetaConstants.actions.SET_POD_NAME,
		name
	};
};

export const setPodMetaValue = ( key, value ) => {
	return {
		type: podMetaConstants.actions.SET_POD_META_VALUE,
		key,
		value
	};
};
