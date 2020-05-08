import * as paths from './state-paths';

import {
	uiConstants,
	groupConstants,
	optionConstants,
	podMetaConstants,
	initialUIState,
} from './constants';

const { combineReducers } = wp.data;

// Helper function
export const setObjectValue = ( object, key, value ) => {
	return {
		...object,
		[ key ]: value,
	};
};

// UI
export const ui = ( state = initialUIState, action = {} ) => {
	const {
		actions,
		saveStatuses,
		deleteStatuses,
	} = uiConstants;

	switch ( action.type ) {
		case actions.SET_ACTIVE_TAB: {
			// Use the default if the tab name doesn't exist
			let newTab = initialUIState.activeTab;
			const tabIndex = paths.TAB_LIST.tailGetFrom( state ).indexOf( action.activeTab );

			if ( -1 !== tabIndex ) {
				newTab = action.activeTab;
			}

			return {
				...state,
				activeTab: newTab,
			};
		}
		case actions.SET_SAVE_STATUS: {
			const newStatus = Object.values( saveStatuses ).includes( action.saveStatus )
				? action.saveStatus
				: initialUIState.saveStatus;

			return {
				...state,
				saveStatus: newStatus,
				saveMessage: action.message,
			};
		}
		case actions.SET_DELETE_STATUS: {
			const newStatus = Object.values( deleteStatuses ).includes( action.deleteStatus )
				? action.deleteStatus
				: initialUIState.deleteStatus;

			return {
				...state,
				deleteStatus: newStatus,
				deleteMessage: action.message,
			};
		}

		default:
			return state;
	}
};

export const groups = ( state = {}, action = {} ) => {
	const { actions } = groupConstants;

	if ( actions.MOVE_GROUP === action.type ) {
		const { oldIndex, newIndex } = action;
		const groupList = paths.GROUP_LIST.tailGetFrom( state );

		// Index bounds checking
		if ( null === oldIndex || null === newIndex || oldIndex === newIndex ) {
			return state;
		}
		if ( oldIndex >= groupList.length || 0 > oldIndex ) {
			return state;
		}
		if ( newIndex >= groupList.length || 0 > newIndex ) {
			return state;
		}

		const newGroupList = [ ...groupList ];
		newGroupList.splice( newIndex, 0, newGroupList.splice( oldIndex, 1 )[ 0 ] );
		return {
			...state,
			[ paths.GROUP_LIST.tailPath ]: newGroupList,
		};
	} else if ( actions.SET_GROUP_LIST === action.type ) {
		return {
			...state,
			[ paths.GROUP_LIST.tailPath ]: action.groupList,
		};
	} else if ( actions.ADD_GROUP === action.type ) {
		state.currentPod.groups.push( {
			name: action.group,
			label: action.group,
			fields: [],
		} );

		return { ...state };
	} else if ( actions.SET_GROUP_FIELDS === action.type ) {
		var group = _.find( state.currentPod.groups, function( group ) {
			return group.name == action.groupName;
		} );
		group.fields = action.fields;

		return { ...state };
	} else if ( actions.ADD_GROUP_FIELD === action.type ) {
		var group = _.find( state.currentPod.groups, function( group ) {
			return group.name == action.groupName;
		} );
		group.fields.push( action.field );

		return { ...state };
	}

	return state;
};

export const options = ( state = {}, action = {} ) => {
	const { actions } = optionConstants;

	switch ( action.type ) {
		case actions.SET_OPTION_ITEM_VALUE: {
			const { optionName, itemName, itemValue } = action;

			return {
				...state,
				[ optionName ]: setObjectValue( state[ optionName ], itemName, itemValue ),
			};
		}
		case actions.SET_OPTIONS_VALUES: {
			const entries = Object.entries( action.options );

			const updatedOptions = entries.reduce(
				( accumulator, currentValue ) => {
					const [ optionName, value ] = currentValue;

					return {
						...accumulator,
						[ optionName ]: setObjectValue( state[ optionName ], 'value', value ),
					};
				},
				{}
			);

			return {
				...state,
				...updatedOptions,
			};
		}
		default: {
			return state;
		}
	}
};

// Pod meta
export const podMeta = ( state = {}, action = {} ) => {
	const { actions } = podMetaConstants;

	switch ( action.type ) {
		case actions.SET_POD_NAME:
			return {
				...state,
				name: action.name,
			};

		case actions.SET_POD_META_VALUE:
			return {
				...state,
				[ action.key ]: action.value,
			};

		default:
			return state;
	}
};

// Fields
export const fields = ( state = {}, action = {} ) => {
	return state;
};

export default ( combineReducers( {
	ui,
	podMeta,
	options,
	groups,
	fields,
} ) );
