import { saveStatuses } from './constants';

// Everything
export const getState = state => state;

// UI
export const getActiveTab = state => state.ui.activeTab;
export const getSaveStatus = state => state.ui.saveStatus;
export const isSaving = state => ( state.ui.saveStatus === saveStatuses.SAVING );

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
