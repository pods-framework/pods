import * as paths from './state-paths';
import { uiConstants } from './constants';

// Everything
export const getState = state => state;

/**
 * UI
 */

//-- Tabs and options
export const getOption = ( state, optionName ) =>
	paths.get( state, paths.OPTIONS )[ optionName ];

export const getActiveTab = state => paths.get( state, paths.ACTIVE_TAB );

export const getTab = ( state, tabName ) =>
	paths.get( state, paths.TAB_BY_NAME )[ tabName ];

export const getOrderedTabList = state =>
	paths.get( state, paths.TABS ).orderedList;

export const getTabs = state =>
	getOrderedTabList( state ).map( tabName => getTab( state, tabName ) );

export const getOrderedTabOptionList = ( state, tabName ) =>
	getTab( state, tabName ).optionList;

export const getTabOptions = ( state, tabName ) =>
	getOrderedTabOptionList( state, tabName ).map(
		optionName => getOption( state, optionName )
	);

//-- Save status
export const getSaveStatus = state => paths.get( state, paths.SAVE_STATUS );

export const isSaving = state =>
	paths.get( state, paths.SAVE_STATUS ) === uiConstants.saveStatuses.SAVING;

/**
 * Pod meta
 */
export const getPodName = state => {
	return paths.get( state, paths.POD_NAME );
};

export const getPodMetaValue = ( state, key ) => {
	return paths.get( state, paths.POD_META )[ key ];
};

/**
 * Fields
 */
export const getFields = state => paths.get( state, paths.FIELDS );
