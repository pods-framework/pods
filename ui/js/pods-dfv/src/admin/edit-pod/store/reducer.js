import {
	optionConstants,
	podMetaConstants,
	uiConstants,
	initialUIState,
} from './constants';

const { combineReducers } = wp.data;

// Helper function
export const setObjectValue = ( object, key, value ) => {
	return {
		...object,
		[ key ]: value
	};
};

// UI
export const ui = ( state = initialUIState, action = {} ) => {
	const { actions, saveStatuses } = uiConstants;

	switch ( action.type ) {
		case actions.SET_ACTIVE_TAB:
			// Use the default if the tab name doesn't exist
			let newTab = initialUIState.activeTab;
			let tabIndex = state.tabs.orderedList.indexOf( action.activeTab );

			if ( -1 !== tabIndex ) {
				newTab = action.activeTab;
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

export const options = ( state = {}, action = {} ) => {
	const { actions } = optionConstants;

	switch ( action.type ) {
		case actions.SET_OPTION_ITEM_VALUE:
			const { optionName, itemName, itemValue } = action;
			return {
				...state,
				[ optionName ]: setObjectValue( state[ optionName ], itemName, itemValue )
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

export default ( combineReducers( {
	ui,
	podMeta,
	options,
	fields,
} ) );
