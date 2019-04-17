import { uiActions, labelActions } from 'pods-dfv/src/admin/edit-pod/store/constants';

export const setLabelValue = ( labelName, newValue ) => {
	return {
		type: labelActions.SET_LABEL_VALUE,
		labelName: labelName,
		newValue: newValue
	};
};

export const setActiveTab = ( activeTab ) => {
	return {
		type: uiActions.SET_ACTIVE_TAB,
		activeTab: activeTab
	};
};

export const setSaveStatus = ( saveStatus ) => {
	return {
		type: uiActions.SET_SAVE_STATUS,
		saveStatus: saveStatus
	};
};
