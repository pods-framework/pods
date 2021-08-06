/* global ajaxurl */

const loadAjaxOptions = ( ajaxData = {} ) => ( inputValue = '' ) => {
	const data = {
		_wpnonce: ajaxData?._wpnonce,
		action: 'pods_relationship',
		method: 'select2',
		pod: ajaxData?.pod,
		field: ajaxData?.field,
		uri: ajaxData?.uri,
		id: ajaxData?.id,
		query: inputValue,
	};

	const formData = new FormData();

	Object.keys( data ).forEach( ( key ) => {
		formData.append( key, data[ key ] );
	} );

	return new Promise(
		( resolve ) => {
			fetch(
				ajaxurl + '?pods_ajax=1',
				{
					method: 'POST',
					body: formData,
				},
			).then( ( results ) => {
				console.log( 'ajax results', results );

				const formattedResults = results.map( ( result ) => ( {
					label: result?.name,
					value: result?.id,
				} ) );

				resolve( formattedResults );
			} );
		}
	);
};

export default loadAjaxOptions;
