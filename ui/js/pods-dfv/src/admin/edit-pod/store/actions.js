import {
	uiConstants,
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
