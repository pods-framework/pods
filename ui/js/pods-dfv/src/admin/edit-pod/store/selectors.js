import { uiConstants } from './constants';

// Everything
export const getState = state => state;

/**
 * UI
 */

//-- Tabs and options
export const getOption = ( state, optionName ) => state.ui.options[ optionName ];
export const getActiveTab = state => state.ui.activeTab;
export const getTab = ( state, tabName ) => state.ui.tabs.byName[ tabName ];
export const getOrderedTabList = state => state.ui.tabs.orderedList;

export const getTabs = state =>
	getOrderedTabList( state ).map( tabName => getTab( state, tabName ) );

export const getOrderedTabOptionList = ( state, tabName ) =>
	getTab( state, tabName ).optionList;

export const getTabOptions = ( state, tabName ) =>
	getOrderedTabOptionList( state, tabName ).map( optionName => getOption( state, optionName ) );

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
