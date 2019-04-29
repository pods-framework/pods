import {
	uiConstants,
	initialUIState,
	podMetaConstants
} from './constants';

const { combineReducers } = wp.data;

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

		case actions.SET_OPTION_ITEM_VALUE:
			return {
				...state,
				options: options( state.options, action )
			};

		default:
			return state;
	}
};

export const options = ( state = {}, action = {} ) => {
	const { actions } = uiConstants;

	switch ( action.type ) {
		case actions.SET_OPTION_ITEM_VALUE:
			return {
				...state,
				[ action.optionName ]: setObjectValue( state[ action.optionName ], action.itemName, action.itemValue )
			};


		default:
			return state;
	}
};

export const setObjectValue = ( object, key, value ) => {
	return {
		...object,
		[ key ]: value
	};
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
	fields,
} ) );
