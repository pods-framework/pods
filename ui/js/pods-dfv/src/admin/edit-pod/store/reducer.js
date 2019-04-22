import {
	uiConstants,
	initialUIState,
	labelConstants,
	podMetaConstants
} from './constants';

const { combineReducers } = wp.data;

// UI
export const ui = ( state = initialUIState, action = {} ) => {
	const { actions, saveStatuses, tabNames } = uiConstants;

	switch ( action.type ) {
		case actions.SET_ACTIVE_TAB:
			let newTab = action.activeTab;
			if ( !state.tabs.hasOwnProperty( newTab ) ) {
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
				name: action.name
			};

		case actions.SET_POD_META_VALUE:
			return {
				...state,
				[ action.key ]: action.value
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
