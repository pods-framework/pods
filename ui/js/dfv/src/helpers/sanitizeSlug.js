import sanitizeHtml from 'sanitize-html';
import { deburr, trim } from 'lodash';

const sanitizeSlug = ( value, fallback = '_' ) => {
	const withoutTags = sanitizeHtml(
		value.replace( /\&/g, '' ),
		{
			allowedTags: [],
			parser: { decodeEntities: false },
		}
	);

	const fallbackSeparator = fallback.toString();

	return deburr( withoutTags )
		.replace( /[\s\./\\+=]+/g, fallback )
		.replace( /[^\w\-_]+/g, '' )
		.toLowerCase();
};

export default sanitizeSlug;
