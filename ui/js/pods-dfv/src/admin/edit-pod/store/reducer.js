import _ from 'lodash';

import {
	GROUPS as GROUPS_PATH,
} from './state-paths';

import {
	uiConstants,
	currentPodConstants,
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

export const ui = ( state = initialUIState, action = {} ) => {
	const {
		actions: ACTIONS,
		saveStatuses: SAVE_STATUSES,
		deleteStatuses: DELETE_STATUSES,
	} = uiConstants;

	switch ( action.type ) {
		case ACTIONS.SET_ACTIVE_TAB: {
			return {
				...state,
				activeTab: action.activeTab,
			};
		}
		case ACTIONS.SET_SAVE_STATUS: {
			const newStatus = Object.values( SAVE_STATUSES ).includes( action.saveStatus )
				? action.saveStatus
				: initialUIState.saveStatus;

			return {
				...state,
				saveStatus: newStatus,
				saveMessage: action.message,
			};
		}
		case ACTIONS.SET_DELETE_STATUS: {
			const newStatus = Object.values( DELETE_STATUSES ).includes( action.deleteStatus )
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

export const currentPod = ( state = {}, action = {} ) => {
	const {
		actions: ACTIONS,
	} = currentPodConstants;

	switch ( action.type ) {
		case ACTIONS.SET_POD_NAME: {
			return {
				...state,
				name: action.name,
			};
		}

		case ACTIONS.SET_OPTION_VALUE: {
			const { optionName, value } = action;

			return {
				...state,
				[ optionName ]: value,
			};
		}

		case ACTIONS.SET_OPTIONS_VALUES: {
			return {
				...state,
				...action.options,
			};
		}

		case ACTIONS.MOVE_GROUP: {
			const { oldIndex, newIndex } = action;
			const groupList = GROUPS_PATH.tailGetFrom( state );

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
				[ GROUPS_PATH.tailPath ]: newGroupList,
			};
		}

		case ACTIONS.SET_GROUP_LIST: {
			return {
				...state,
				[ GROUPS_PATH.tailPath ]: action.groupList,
			};
		}

		case ACTIONS.ADD_GROUP: {
			return {
				...state,
				groups: [
					...state.groups,
					{
						name: action.group,
						label: action.group,
						fields: [],
					},
				],
			};
		}

		case ACTIONS.SET_GROUP_FIELDS: {
			const group = _.find( state.currentPod.groups, function( podGroup ) {
				return podGroup.name === action.groupName;
			} );
			group.fields = action.fields;

			return { ...state };
		}

		case ACTIONS.ADD_GROUP_FIELD: {
			const group = _.find( state.currentPod.groups, function( podGroup ) {
				return podGroup.name === action.groupName;
			} );
			group.fields.push( action.field );

			return { ...state };
		}

		default: {
			return state;
		}
	}
};

export const global = ( state = {} ) => {
	return state;
};

export default ( combineReducers( {
	ui,
	currentPod,
	global,
} ) );
