import apiFetch from '@wordpress/api-fetch';

import { CURRENT_POD_ACTIONS } from 'dfv/src/store/constants';
import prepareApiFetchData from 'dfv/src/helpers/prepareApiFetchData';

const {
	API_REQUEST,
} = CURRENT_POD_ACTIONS;

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
				data: prepareApiFetchData( data ),
			}
		);

		if ( Array.isArray( onSuccess ) ) {
			onSuccess.forEach( ( actionCreator ) => dispatch( actionCreator( result ) ) );
		} else {
			dispatch( onSuccess( result ) );
		}
	} catch ( error ) {
		if ( Array.isArray( onFailure ) ) {
			onFailure.forEach( ( actionCreator ) => dispatch( actionCreator( error ) ) );
		} else {
			dispatch( onFailure( error ) );
		}
	}
};

export default apiMiddleware;
