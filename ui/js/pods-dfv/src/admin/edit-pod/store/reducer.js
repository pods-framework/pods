import { uiActions, labelActions, tabNames, saveStatuses } from './constants';

const { combineReducers } = wp.data;

const initialUIState = {
	activeTab: tabNames.MANAGE_FIELDS,
	saveStatus: saveStatuses.NONE,
};

// Fields
export const fields = ( state = [], action ) => {
	return state;
};

// Labels
export const labels = ( state = [], action ) => {
	switch ( action.type ) {
		case labelActions.SET_LABEL_VALUE:
			return state.map( ( thisLabel, index ) => {
				if ( thisLabel.name !== action.labelName ) {
					return thisLabel; // Not the target, return it as-is
				} else {
					return {
						...thisLabel,
						value: action.newValue
					};
				}
			} );

		default:
			return state;
	}
};

// UI
export const ui = ( state = initialUIState, action ) => {
	switch ( action.type ) {
		case uiActions.SET_ACTIVE_TAB:
			let newTab = action.activeTab;
			if ( !Object.values( tabNames ).includes( newTab ) ) {
				newTab = initialUIState.activeTab;
			}
			return {
				...state,
				activeTab: newTab
			};

		case uiActions.SET_SAVE_STATUS:
			let newStatus = action.saveStatus;
			if ( !Object.values( saveStatuses ).includes( newStatus ) ) {
				newStatus = initialUIState.saveStatus;
			}
			return {
				...state,
				saveStatus: newStatus
			};

		default:
			return state;
	}
};

export default ( combineReducers( {
	fields,
	labels,
	ui
} ) );
