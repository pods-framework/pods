const AJAX_ACTION = 'pods_admin_proto';

export const handleSubmit = ( e, props ) => {
	e.preventDefault();

	const requestData = {
		id: props.podMeta.id,
		name: props.podMeta.name,
		old_name: props.podMeta.name,
		_wpnonce: props.nonce,
		fields: props.fields,
	};

	/*
	props.setSaveStatus( saveStatuses.SAVING );
	fetch( `${ajaxurl}?pods_ajax=1&action=${AJAX_ACTION}`, {
		method: 'POST',
		headers: {
			'Accept': 'application/json',
			'Content-Type': 'application/json'
		},
		body: JSON.stringify( requestData )
	} )
	.then(
		( result ) => {
			console.log( result );
			props.setSaveStatus( saveStatuses.SAVE_SUCCESS );
		},
		( error ) => {
			console.log( error );
			props.setSaveStatus( saveStatuses.SAVE_ERROR );
		}
	);
	 */
};
