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
		'code',
		'datetime',
		'date',
		'time',
		'number',
		'currency',
		'oembed',
		'color',
		'slug',
		// Later these will be supported:
		// 'pick',
		// 'file',
		// 'avatar'
	];

	if ( ! supportedRepeatableTypes.includes( type ) ) {
		return false;
	}

	console.log( 'isFieldRepeatable', fieldConfig.name, )

	return toBool( fieldConfig?.[ `${type}_repeatable` ] || false );
};

export default isFieldRepeatable;
