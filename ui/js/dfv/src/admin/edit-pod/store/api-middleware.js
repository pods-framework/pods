import apiFetch from '@wordpress/api-fetch';

import { currentPodConstants } from 'dfv/src/admin/edit-pod/store/constants';

const {
	actions: {
		API_REQUEST,
	},
} = currentPodConstants;

const apiMiddleware = ( { dispatch } ) => ( next ) => async ( action ) => {
	next( action );

	if ( API_REQUEST !== action.type ) {
		return;
	}

	const {
		url,
		method,
		data,
		onSuccess,
		onFailure,
		onStart,
	} = action.payload;

	if ( onStart ) {
		dispatch( onStart() );
	}

	try {
		const result = await apiFetch(
			{
				path: url,
				method,
				parse: true,
				data,
			}
		);

		if ( Array.isArray( onSuccess ) ) {
			onSuccess.forEach( ( actionCreator ) => dispatch( actionCreator( result ) ) );
		} else {
			dispatch( onSuccess( result ) );
		}
	} catch ( error ) {
		if ( Array.isArray( onSuccess ) ) {
			onFailure.forEach( ( actionCreator ) => dispatch( actionCreator( error ) ) );
		} else {
			dispatch( onFailure( error ) );
		}
	}
};

export default apiMiddleware;
