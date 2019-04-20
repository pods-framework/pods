import {
	uiConstants,
	labelConstants,
	podMetaConstants
} from './constants';

const { combineReducers } = wp.data;

export const initialUIState = {
	activeTab: uiConstants.tabNames.MANAGE_FIELDS,
	saveStatus: uiConstants.saveStatuses.NONE,
};

// UI
export const ui = ( state = initialUIState, action = {} ) => {
	const { actions, saveStatuses, tabNames } = uiConstants;

	switch ( action.type ) {
		case actions.SET_ACTIVE_TAB:
			let newTab = action.activeTab;
			if ( !Object.values( tabNames ).includes( newTab ) ) {
				newTab = initialUIState.activeTab;
			}
			return {
				...state,
				activeTab: newTab
			};

		case actions.SET_SAVE_STATUS:
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

// Pod meta
export const podMeta = ( state = {}, action = {} ) => {
	const { actions } = podMetaConstants;

	switch ( action.type ) {
		case actions.SET_POD_NAME:
			return {
				...state,
				podName: action.podName
			};

		default:
			return state;
	}
};

// Fields
export const fields = ( state = [], action = {} ) => {
	return state;
};

// Labels
export const labels = ( state = [], action = {} ) => {
	const { actions } = labelConstants;

	switch ( action.type ) {
		case actions.SET_LABEL_VALUE:
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

export default ( combineReducers( {
	ui,
	podMeta,
	fields,
	labels,
} ) );
