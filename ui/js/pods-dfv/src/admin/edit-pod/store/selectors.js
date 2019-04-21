import { uiConstants } from './constants';

// Everything
export const getState = state => state;

// UI
export const getActiveTab = state => {
	state.ui = state.ui || {};
	return state.ui.activeTab;
};
export const getSaveStatus = state => {
	state.ui = state.ui || {};
	return state.ui.saveStatus;
};
export const isSaving = state => {
	state.ui = state.ui || {};
	return ( state.ui.saveStatus === uiConstants.saveStatuses.SAVING );
};

// Pod meta
export const getPodName = state => {
	state.podMeta = state.podMeta || {};
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
	state.labels = state.labels || {};

	for ( let i = 0; i < state.labels.length; i++ ) {
		if ( state.labels[ i ].name === labelName ) {
			return state.labels[ i ].value;
		}
	}
	return null;
};
