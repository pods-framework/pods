import {
	uiConstants,
	optionConstants,
	groupConstants,
	podMetaConstants,
} from 'pods-dfv/src/admin/edit-pod/store/constants';

// UI
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

// Options
export const setOptionValue = ( name, value ) => setOptionItemValue( name, 'value', value );
export const setOptionItemValue = ( optionName, itemName, itemValue ) => {
	return {
		type: optionConstants.actions.SET_OPTION_ITEM_VALUE,
		optionName,
		itemName,
		itemValue
	};
};

// Groups
export const reorderGroupItem = ( oldIndex, newIndex ) => {
	return {
		type: groupConstants.actions.REORDER_GROUP_ITEM,
		oldIndex,
		newIndex
	};
};

// Pod meta
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
