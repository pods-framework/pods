import { uiConstants, labelConstants } from './constants';
const { combineReducers } = wp.data;

export const initialUIState = {
	activeTab: uiConstants.tabNames.MANAGE_FIELDS,
	saveStatus: uiConstants.saveStatuses.NONE,
};

// Fields
export const fields = ( state = [], action = {} ) => {
	return state;
};

// Labels
export const labels = ( state = [], action = {} ) => {
	switch ( action.type ) {
		case labelConstants.actions.SET_LABEL_VALUE:
			return state.map( ( thisLabel ) => {
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
export const ui = ( state = initialUIState, action = {} ) => {
	switch ( action.type ) {
		case uiConstants.actions.SET_ACTIVE_TAB:
			let newTab = action.activeTab;
			if ( !Object.values( uiConstants.tabNames ).includes( newTab ) ) {
				newTab = initialUIState.activeTab;
			}
			return {
				...state,
				activeTab: newTab
			};

		case uiConstants.actions.SET_SAVE_STATUS:
			let newStatus = action.saveStatus;
			if ( !Object.values( uiConstants.saveStatuses ).includes( newStatus ) ) {
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
