import sanitizeHtml from 'sanitize-html';
import { deburr, trim } from 'lodash';

const sanitizeSlug = ( value ) => {
	const withoutTags = sanitizeHtml(
		value.replace( /\&/g, '' ),
		{
			allowedTags: [],
			parser: { decodeEntities: false },
		}
	);

	return trim(
		deburr( withoutTags )
			.replace( /[\s\./]+/g, '_' )
			.replace( /[^\w-]+/g, '' )
			.toLowerCase(),
		'-'
	);
};

export default sanitizeSlug;
