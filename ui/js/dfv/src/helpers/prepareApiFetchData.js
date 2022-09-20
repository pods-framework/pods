/**
 * Prepares the apiFetch data so that they can deal with the way it expects data types to be.
 *
 * @returns object
 */
const prepareApiFetchData = ( data ) => {
	// Only process objects.
	if ( 'object' !== typeof data ) {
		return data;
	}

	// Map true/false to 1/0 in data because the API will not send through true/false values to the request.
	Object.entries( data ).forEach( ( [ key, value ] ) => {
		if ( 'boolean' === typeof value ) {
			data[ key ] = value ? 1 : 0;
		} else if ( 'undefined' === typeof value ) {
			data[ key ] = '';
		}
	} );

	if ( 'undefined' !== typeof data.args ) {
		// Map true/false to 1/0 in data because the API will not send through true/false values to the request.
		Object.entries( data.args ).forEach( ( [ key, value ] ) => {
			if ( 'boolean' === typeof value ) {
				data.args[ key ] = value ? 1 : 0;
			} else if ( 'undefined' === typeof value ) {
				data.args[ key ] = '';
			}
		} );
	}

	return data;
};

export default prepareApiFetchData;
