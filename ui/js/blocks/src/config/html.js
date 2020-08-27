export const richText = {
	allowedTags: [
		// Block elements
		'blockquote', 'caption', 'div', 'figcaption', 'figure', 'h1', 'h2',
		'h3', 'h4', 'h5', 'h6', 'hr', 'li', 'ol', 'p', 'pre', 'section',
		'table', 'tbody', 'td', 'th', 'thead', 'tr', 'ul',
		// Inline elements
		'a', 'abbr', 'acronym', 'audio', 'b', 'bdi', 'bdo', 'big', 'br',
		'button', 'canvas', 'cite', 'code', 'data', 'datalist', 'del', 'dfn',
		'em', 'embed', 'i', 'iframe', 'img', 'input', 'ins', 'kbd', 'label',
		'map', 'mark', 'meter', 'noscript', 'object', 'output', 'picture',
		'progress', 'q', 'ruby', 's', 'samp', 'select', 'slot',
		'small', 'span', 'strong', 'sub', 'sup', 'svg', 'template', 'textarea',
		'time', 'u', 'tt', 'var', 'video', 'wbr',
	],
	allowedAttributes: {
		'*': [ 'class', 'id', 'data-*', 'style' ],
		iframe: [ '*' ],
		a: [ 'href', 'name', 'target' ],
		img: [ 'src', 'srcset', 'sizes', 'alt', 'width', 'height' ],
	},
	selfClosing: [
		'img', 'br', 'hr', 'area', 'base', 'basefont', 'input', 'link', 'meta',
	],
	allowedSchemes: [ 'http', 'https', 'ftp', 'mailto' ],
	allowedSchemesByTag: {},
	allowProtocolRelative: true,
};

export const richTextInlineOnly = {
	allowedTags: [
		'a', 'abbr', 'acronym', 'audio', 'b', 'bdi', 'bdo', 'big', 'br',
		'button', 'canvas', 'cite', 'code', 'data', 'datalist', 'del', 'dfn',
		'em', 'embed', 'i', 'iframe', 'img', 'input', 'ins', 'kbd', 'label',
		'map', 'mark', 'meter', 'noscript', 'object', 'output', 'picture',
		'progress', 'q', 'ruby', 's', 'samp', 'select', 'slot',
		'small', 'span', 'strong', 'sub', 'sup', 'svg', 'template', 'textarea',
		'time', 'u', 'tt', 'var', 'video', 'wbr',
	],
	allowedAttributes: {
		'*': [ 'class', 'id', 'data-*', 'style' ],
		a: [ 'href', 'name', 'target' ],
		img: [ 'src', 'srcset', 'sizes', 'alt', 'width', 'height' ],
	},
	selfClosing: [
		'img', 'br', 'hr', 'area', 'base', 'basefont', 'input', 'link', 'meta',
	],
	allowedSchemes: [ 'http', 'https', 'ftp', 'mailto' ],
	allowedSchemesByTag: {},
	allowProtocolRelative: true,
};

export const richTextNoLinks = {
	allowedTags: [
		'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote', 'p', 'ul', 'ol',
		'nl', 'li', 'b', 'i', 'strong', 'em', 'strike', 'code', 'cite', 'hr', 'br',
		'div', 'table', 'thead', 'caption', 'tbody', 'tr', 'th', 'td', 'pre', 'img',
		'figure', 'figcaption', 'iframe', 'section',
	],
	allowedAttributes: {
		'*': [ 'class', 'id', 'data-*', 'style' ],
		iframe: [ '*' ],
		img: [ 'src', 'srcset', 'sizes', 'alt', 'width', 'height' ],
	},
	selfClosing: [
		'img', 'br', 'hr', 'area', 'base', 'basefont', 'input', 'link', 'meta',
	],
};

export const plainText = {
	allowedTags: [],
	allowedAttributes: {},
};
