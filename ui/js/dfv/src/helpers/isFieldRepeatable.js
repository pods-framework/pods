import { toBool } from 'dfv/src/helpers/booleans';

const isFieldRepeatable = ( fieldConfig ) => {
	const { type } = fieldConfig;

	if ( typeof type === 'undefined' ) {
		throw new Error( 'Invalid field config.' );
	}

	const supportedRepeatableTypes = [
		'text',
		'website',
		'phone',
		'email',
		'password',
		'paragraph',
		'wysiwyg',
		'datetime',
		'date',
		'time',
		'number',
		'currency',
		'oembed',
		'color',
	];

	if ( ! supportedRepeatableTypes.includes( type ) ) {
		return false;
	}

	return toBool( fieldConfig?.repeatable || false );
};

export default isFieldRepeatable;
