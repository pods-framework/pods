/**
 * Internal dependencies
 */
import { EVENT } from '@moderntribe/common/data/editor/post-types';
import * as types from '@moderntribe/common/data/forms/types';

export const DEFAULT_STATE = {
	edit: false,
	create: false,
	submit: false,
	saving: false,
	fields: {},
	type: EVENT,
};

export default ( state = DEFAULT_STATE, action ) => {
	switch ( action.type ) {
		case types.ADD_FORM:
			return {
				...state,
				type: action.payload.type,
			};
		case types.CLEAR_FORM:
			return {
				...state,
				...DEFAULT_STATE,
				type: state.type,
			};
		case types.CREATE_FORM_DRAFT:
			return {
				...state,
				submit: false,
				edit: false,
				create: true,
				fields: action.payload.fields,
			};
		case types.SET_SAVING_FORM:
			return {
				...state,
				saving: action.payload.saving,
			};
		case types.EDIT_FORM_ENTRY:
			return {
				...state,
				create: false,
				submit: false,
				edit: true,
				fields: action.payload.fields,
			};
		case types.SUBMIT_FORM:
			return {
				...state,
				submit: true,
			};
		default:
			return state;
	}
};
