/**
 * External dependencies
 */
import { uniq } from 'lodash';

/**
 * Internal dependencies
 */
import { types } from '@moderntribe/common/data/plugins';

export default ( state = [], action ) => {
	switch ( action.type ) {
		case types.ADD_PLUGIN:
			return uniq( [ ...state, action.payload.name ] );
		case types.REMOVE_PLUGIN:
			return [ ...state ].filter( ( pluginName ) => pluginName !== action.payload.name );
		default:
			return state;
	}
};
