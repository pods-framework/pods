/**
 * Internal dependencies
 */
import * as types from '@moderntribe/common/data/forms/types';

export default ( state = [], action ) => {
	switch ( action.type ) {
		case types.ADD_VOLATILE_ID:
			return [ ...state, action.payload.id ];
		case types.REMOVE_VOLATILE_ID:
			return state.filter( ( id ) => id !== action.payload.id );
		default:
			return state;
	}
};
