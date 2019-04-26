import { uiConstants } from './constants';

// Everything
export const getState = state => state;

/**
 * UI
 */

//-- Tabs
export const getActiveTab = state => state.ui.activeTab;
//--Todo: Check for any usages of this outside of tests after the API fills out some, I doubt it's going to be needed
export const getTabs = state => state.ui.tabs.byName;
export const getTab = ( state, tabName ) => state.ui.tabs.byName[ tabName ];
export const getOrderedTabList = state => state.ui.tabs.orderedList;
export const getOrderedTabs = state => getOrderedTabList( state ).map( tabName => getTab( state, tabName ) );
export const getOrderedTabOptions = ( state, tabName ) => {
	const tab = getTab( state, tabName );

	if ( !tab ) {
		return undefined;
	} else {
		return tab.options;
	}
};

//-- Save status
export const getSaveStatus = state => state.ui.saveStatus;
export const isSaving = state => state.ui.saveStatus === uiConstants.saveStatuses.SAVING;

/**
 * Pod meta
 */
export const getPodName = state => {
	return state.podMeta.name;
};
export const getPodMetaValue = ( state, key ) => {
	return state.podMeta[ key ];
};

/**
 * Fields
 */
export const getFields = state => state.fields;
