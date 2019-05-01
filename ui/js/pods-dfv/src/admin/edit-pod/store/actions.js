import {
	uiConstants,
	optionConstants,
	podMetaConstants,
} from 'pods-dfv/src/admin/edit-pod/store/constants';

export const setActiveTab = ( activeTab ) => {
	return {
		type: uiConstants.actions.SET_ACTIVE_TAB,
		activeTab: activeTab
	};
};

export const setSaveStatus = ( saveStatus ) => {
	return {
		type: uiConstants.actions.SET_SAVE_STATUS,
		saveStatus: saveStatus
	};
};

// Options
export const setOptionValue = ( name, value ) => setOptionItemValue( name, 'value', value );
export const setOptionItemValue = ( optionName, itemName, itemValue ) => {
	return {
		type: optionConstants.actions.SET_OPTION_ITEM_VALUE,
		optionName: optionName,
		itemName: itemName,
		itemValue: itemValue
	};
};


// Pod meta
export const setPodName = ( name ) => {
	return {
		type: podMetaConstants.actions.SET_POD_NAME,
		name: name
	};
};

export const setPodMetaValue = ( key, value ) => {
	return {
		type: podMetaConstants.actions.SET_POD_META_VALUE,
		key: key,
		value: value
	};
};
