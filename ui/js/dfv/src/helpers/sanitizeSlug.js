import sanitizeHtml from 'sanitize-html';
import { deburr } from 'lodash';

const sanitizeSlug = ( value, separator = '_' ) => {
	const withoutTags = sanitizeHtml(
		value.replace( /\&/g, '' ),
		{
			allowedTags: [],
			parser: { decodeEntities: false },
		}
	);

	return deburr( withoutTags )
		.replace( /[\s\./\\+=]+/g, separator )
		.replace( /[^\w\-_]+/g, '' )
		.toLowerCase();
};

export default sanitizeSlug;
