/**
 * External dependencies
 */
import { noop, get, inRange } from 'lodash';
import 'whatwg-fetch';

/**
 * Internal dependencies
 */
import { rest } from '@moderntribe/common/utils/globals';
import { types } from '@moderntribe/common/store/middlewares/request';

export default () => ( next ) => async ( action ) => {
	if ( action.type !== types.WP_REQUEST ) {
		return next( action );
	}

	const { meta = {} } = action;

	const {
		path = '',
		params = {},
	} = meta;

	next( action );

	const { url = '', nonce = {} } = rest();
	const wpRESTNonce = nonce.wp_rest || '';
	const namespaces = rest.namespaces || {};
	const core = namespaces.core || 'wp/v2';
	const BASE = `${ url }${ core }`;

	const actions = {
		start: noop,
		success: noop,
		error: noop,
		none: noop,
		...get( meta, 'actions', {} ),
	};

	if ( path === '' ) {
		actions.none( path );
		return;
	}

	const endpoint = `${ BASE }/${ path }`;

	actions.start( endpoint, params );

	const headers = {
		'Accept': 'application/json',
		'Content-Type': 'application/json',
		...get( params, 'headers', {} ),
		'X-WP-Nonce': wpRESTNonce,
	};

	try {
		const response = await fetch( endpoint, {
			...params,
			credentials: 'include',
			headers,
		} );

		const { status } = response;
		// inRange includes 200 but excludes 300 from the range so it's from 200 up to 299
		if ( ! inRange( status, 200, 300 ) ) {
			throw response;
		}
		const body = await response.json();
		actions.success( { body, headers: response.headers } );
		return [ response, body ];
	} catch ( error ) {
		actions.error( error );
		return error;
	}
};
