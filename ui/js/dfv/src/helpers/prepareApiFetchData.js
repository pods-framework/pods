/**
 * Prepares the apiFetch data so that they can deal with the way it expects data types to be.
 *
 * @returns object
 */
const prepareApiFetchData = ( data ) => {
	// Map true/false to 1/0 in data because the API will not send through true/false values to the request.
	Object.entries( data ).forEach( ( [ key, value ] ) => {
		if ( 'boolean' !== typeof value ) {
			return;
		}

		data[ key ] = value ? 1 : 0;
	} );

	if ( 'undefined' !== typeof data.args ) {
		// Map true/false to 1/0 in data because the API will not send through true/false values to the request.
		Object.entries( data.args ).forEach( ( [ key, value ] ) => {
			if ( 'boolean' !== typeof value ) {
				return;
			}

			data.args[ key ] = value ? 1 : 0;
		} );
	}

	return data;
};

export default prepareApiFetchData;
