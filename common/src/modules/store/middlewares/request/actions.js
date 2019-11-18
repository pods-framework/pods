/**
 * Internal dependencies
 */
import * as types from './types';

export const wpRequest = ( meta ) => ( {
	type: types.WP_REQUEST,
	meta,
} );
