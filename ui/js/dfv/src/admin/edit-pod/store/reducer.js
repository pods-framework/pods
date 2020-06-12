import { omit } from 'lodash';

import { combineReducers } from '@wordpress/data';

import {
	GROUPS as GROUPS_PATH,
} from './state-paths';

import {
	SAVE_STATUSES,
	DELETE_STATUSES,
	UI_ACTIONS,
	CURRENT_POD_ACTIONS,
	INITIAL_UI_STATE,
} from './constants';

// Helper function
export const setObjectValue = ( object, key, value ) => {
	return {
		...object,
		[ key ]: value,
	};
};

export const ui = ( state = INITIAL_UI_STATE, action = {} ) => {
	switch ( action.type ) {
		case UI_ACTIONS.SET_ACTIVE_TAB: {
			return {
				...state,
				activeTab: action.activeTab,
			};
		}
		case UI_ACTIONS.SET_SAVE_STATUS: {
			const newStatus = Object.values( SAVE_STATUSES ).includes( action.saveStatus )
				? action.saveStatus
				: INITIAL_UI_STATE.saveStatus;

			return {
				...state,
				saveStatus: newStatus,
				saveMessage: action.message,
			};
		}
		case UI_ACTIONS.SET_DELETE_STATUS: {
			const newStatus = Object.values( DELETE_STATUSES ).includes( action.deleteStatus )
				? action.deleteStatus
				: INITIAL_UI_STATE.deleteStatus;

			return {
				...state,
				deleteStatus: newStatus,
				deleteMessage: action.message,
			};
		}

		case UI_ACTIONS.SET_GROUP_SAVE_STATUS: {
			const newStatus = Object.values( SAVE_STATUSES ).includes( action.saveStatus )
				? action.saveStatus
				: INITIAL_UI_STATE.saveStatus;

			// The group's name may have changed during the save. Because the map of
			// group save statuses uses the group name, we may need to remove the old name
			// and set the new name, or just update the old/same name.
			const hasNameChange = action.result?.group?.name === action.previousGroupName;

			const name = hasNameChange ? action.result?.group?.name : action.previousGroupName;

			const groupSaveStatuses = {
				...omit( state.groupSaveStatuses, [ action.previousGroupName ] ),
				[ name ]: newStatus,
			};

			return {
				...state,
				groupSaveStatuses,
			};
		}

		case UI_ACTIONS.SET_GROUP_DELETE_STATUS: {
			const newStatus = Object.values( DELETE_STATUSES ).includes( action.deleteStatus )
				? action.deleteStatus
				: INITIAL_UI_STATE.deleteStatus;

			if ( ! action.result?.group?.name ) {
				return state;
			}

			return {
				...state,
				groupDeleteStatuses: {
					...state.groupDeleteStatuses,
					[ action.result.group.name ]: newStatus,
				},
			};
		}

		case UI_ACTIONS.SET_FIELD_SAVE_STATUS: {
			const newStatus = Object.values( SAVE_STATUSES ).includes( action.saveStatus )
				? action.saveStatus
				: INITIAL_UI_STATE.saveStatus;

			// The group's name may have changed during the save. Because the map of
			// group save statuses uses the group name, we may need to remove the old name
			// and set the new name, or just update the old/same name.
			const hasNameChange = action.result?.field?.name === action.previousFieldName;

			const name = hasNameChange ? action.result?.field?.name : action.previousFieldName;

			const fieldSaveStatuses = {
				...omit( state.fieldSaveStatuses, [ action.previousFieldName ] ),
				[ name ]: newStatus,
			};

			return {
				...state,
				fieldSaveStatuses,
			};
		}

		case UI_ACTIONS.SET_FIELD_DELETE_STATUS: {
			const newStatus = Object.values( DELETE_STATUSES ).includes( action.deleteStatus )
				? action.deleteStatus
				: INITIAL_UI_STATE.deleteStatus;

			if ( ! action.result?.field?.name ) {
				return state;
			}

			return {
				...state,
				fieldDeleteStatuses: {
					...state.fieldDeleteStatuses,
					[ action.result.field.name ]: newStatus,
				},
			};
		}

		default:
			return state;
	}
};

export const currentPod = ( state = {}, action = {} ) => {
	switch ( action.type ) {
		case CURRENT_POD_ACTIONS.SET_POD_NAME: {
			return {
				...state,
				name: action.name,
			};
		}

		case CURRENT_POD_ACTIONS.SET_OPTION_VALUE: {
			const { optionName, value } = action;

			return {
				...state,
				[ optionName ]: value,
			};
		}

		case CURRENT_POD_ACTIONS.SET_OPTIONS_VALUES: {
			return {
				...state,
				...action.options,
			};
		}

		case CURRENT_POD_ACTIONS.MOVE_GROUP: {
			const { oldIndex, newIndex } = action;

			// Index bounds checking
			if ( null === oldIndex || null === newIndex || oldIndex === newIndex ) {
				return state;
			}
			if ( oldIndex >= state.groups.length || 0 > oldIndex ) {
				return state;
			}
			if ( newIndex >= state.groups.length || 0 > newIndex ) {
				return state;
			}

			const newGroupList = [ ...state.groups ];
			newGroupList.splice( newIndex, 0, newGroupList.splice( oldIndex, 1 )[ 0 ] );

			return {
				...state,
				groups: newGroupList,
			};
		}

		case CURRENT_POD_ACTIONS.SET_GROUP_LIST: {
			return {
				...state,
				[ GROUPS_PATH.tailPath ]: action.groupList,
			};
		}

		case CURRENT_POD_ACTIONS.ADD_GROUP: {
			if ( ! action?.result?.group?.id ) {
				return state;
			}

			return {
				...state,
				groups: [
					...state.groups,
					action?.result?.group,
				],
			};
		}

		case CURRENT_POD_ACTIONS.REMOVE_GROUP: {
			return {
				...state,
				groups: state.groups ? state.groups.filter(
					( group ) => group.id !== action.groupID
				) : undefined,
			};
		}

		case CURRENT_POD_ACTIONS.SET_GROUP_DATA: {
			const groups = state.groups.map( ( group ) => {
				if ( group.name !== action?.result?.group?.name ) {
					return group;
				}

				return {
					...action.result.group,
					fields: action?.result?.group?.fields || [],
				};
			} );

			return {
				...state,
				groups,
			};
		}

		case CURRENT_POD_ACTIONS.SET_GROUP_FIELDS: {
			const groups = state.groups.map( ( group ) => {
				if ( group.name !== action.groupName ) {
					return group;
				}

				return {
					...group,
					fields: action.fields,
				};
			} );

			return {
				...state,
				groups,
			};
		}

		case CURRENT_POD_ACTIONS.ADD_GROUP_FIELD: {
			if ( ! action?.result?.field?.id ) {
				return state;
			}

			const groups = state.groups.map( ( group ) => {
				if ( group.name !== action.groupName ) {
					return group;
				}

				return {
					...group,
					fields: [
						...group.fields,
						action.result.field,
					],
				};
			} );

			return {
				...state,
				groups,
			};
		}

		case CURRENT_POD_ACTIONS.REMOVE_GROUP_FIELD: {
			const groups = state.groups.map( ( group ) => {
				if ( group.id !== action.groupID ) {
					return group;
				}

				return {
					...group,
					fields: group.fields.filter(
						( field ) => field.id !== action.fieldID
					),
				};
			} );

			return {
				...state,
				groups,
			};
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
