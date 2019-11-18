/**
 * Internal dependencies
 */
import * as types from './types';

export const addPlugin = ( name ) => ( {
	type: types.ADD_PLUGIN,
	payload: {
		name,
	},
} );

export const removePlugin = ( name ) => ( {
	type: types.REMOVE_PLUGIN,
	payload: {
		name,
	},
} );
