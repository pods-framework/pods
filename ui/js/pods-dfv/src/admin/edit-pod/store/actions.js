import { uiConstants, labelConstants } from 'pods-dfv/src/admin/edit-pod/store/constants';

export const setLabelValue = ( labelName, newValue ) => {
	return {
		type: labelConstants.actions.SET_LABEL_VALUE,
		labelName: labelName,
		newValue: newValue
	};
};

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
