/* global ajaxurl */

const loadAjaxOptions = ( ajaxData = {} ) => async ( inputValue = '' ) => {
	const data = {
		_wpnonce: ajaxData?._wpnonce,
		action: 'pods_relationship',
		method: 'select2',
		pod_name: ajaxData?.pod_name ?? '',
		field_name: ajaxData?.field_name ?? '',
		uri_hash: ajaxData?.uri_hash ?? '',
		id: ajaxData?.id ?? 0,
		query: inputValue,
	};

	const formData = new FormData();

	Object.keys( data ).forEach( ( key ) => {
		formData.append( key, data[ key ] );
	} );

	try {
		const response = await fetch(
			ajaxurl + '?pods_ajax=1',
			{
				method: 'POST',
				body: formData,
			},
		);

		const resultBody = await response.json();

		if ( ! resultBody?.results ) {
			throw new Error( 'Invalid response.' );
		}

		const formattedResults = resultBody.results.map( ( result ) => ( {
			label: result?.name,
			value: result?.id,
		} ) );

		return formattedResults;
	} catch ( e ) {
		throw e;
	}
};

export default loadAjaxOptions;
