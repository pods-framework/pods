import {
	uiConstants,
	initialUIState,
	labelConstants,
	podMetaConstants
} from './constants';

const { combineReducers } = wp.data;

/**
 *
 * @param {Object[]} tabs
 * @param {string}   tabs[].name
 * @param {string}   tabName
 *
 * @returns {(number|null)} The index of the targeted tab, or null if not found
 */
const getTabIndexFromName = ( tabs, tabName ) => {
	let returnIndex = null;

	tabs.forEach( ( thisTab, index ) => {
		if ( thisTab.name === tabName ) {
			returnIndex = index;
		}
	} );

	return returnIndex;
};

// UI
export const ui = ( state = initialUIState, action = {} ) => {
	const { actions, saveStatuses } = uiConstants;

	switch ( action.type ) {
		case actions.SET_ACTIVE_TAB:
			// Use the default if the tab name doesn't exist
			let newTab = initialUIState.activeTab;
			const tabIndex = getTabIndexFromName( state.tabs, action.activeTab );

			if ( null !== tabIndex ) {
				newTab = state.tabs[ tabIndex ].name;
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
