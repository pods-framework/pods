import { uiConstants } from './constants';

// Everything
export const getState = state => state;

// UI
export const getTabs = state => {
	return state.ui.tabs;
};
export const getActiveTab = state => {
	return state.ui.activeTab;
};
export const getSaveStatus = state => {
	return state.ui.saveStatus;
};
export const isSaving = state => {
	return ( state.ui.saveStatus === uiConstants.saveStatuses.SAVING );
};

// Pod meta
export const getPodName = state => {
	return state.podMeta.name;
};
export const getPodMetaValue = ( state, key ) => {
	return state.podMeta[ key ];
};

// Fields
export const getFields = state => state.fields;

// Labels
export const getLabels = state => state.labels;

export const getLabelValue = ( state, labelName ) => {
	for ( let i = 0; i < state.labels.length; i++ ) {
		if ( state.labels[ i ].name === labelName ) {
			return state.labels[ i ].value;
		}
	}
	return null;
};
