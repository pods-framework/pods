/**
 * Create chainable validator with isRequired check
 * @param {function} validator
 */
export const createChainableValidator = ( validator ) => {
	const createChainedValidator = (
		isRequired,
		props,
		propName,
		componentName,
	) => {
		const propValue = props[ propName ];

		if ( propValue == null ) {
			if ( isRequired ) {
				if ( propValue === null ) {
					/* eslint-disable-next-line max-len */
					return new Error( `The prop \`${propName}\` is marked as required in \`${componentName}\`, but its value is \`null\`.` );
				}
				/* eslint-disable-next-line max-len */
				return new Error( `The prop \`${propName}\` is marked as required in \`${componentName}\`, but its value is \`undefined\`.` );
			}
			return null;
		} else {
			return validator( props, propName, componentName );
		}
	};

	const chainedValidator = createChainedValidator.bind( null, false );
	chainedValidator.isRequired = createChainedValidator.bind( null, true );

	return chainedValidator;
}

export const timeRegex = /^([01]?[0-9]|2[0-3]):[0-5][0-9]$/;

/**
 * PropTypes check for type string and time format using 24h clock in hh:mm format
 * e.g. 00:24, 03:57, 21:12
 *
 * @param {object} props
 * @param {string} propName
 * @param {string} componentName
 */
export const timeFormat = ( props, propName, componentName ) => {
	const propValue = props[ propName ];

	if ( typeof propValue !== 'string' ) {
		const type = typeof propValue;
		/* eslint-disable-next-line max-len */
		return new Error( `Invalid prop \`${propName}\` of type \`${type}\` supplied to \`${componentName}\`, expected \`string\`.` );
	}

	if ( ! timeRegex.test( propValue ) ) {
		/* eslint-disable-next-line max-len */
		return new Error( `Invalid prop \`${propName}\` format supplied to \`${componentName}\`, expected \`hh:mm\`.` );
	}

	return null;
};

export const nullType = ( props, propName, componentName ) => {
	if ( null !== props[ propName ] ) {
		return new Error(
			`Invalid prop: \`${propName}\` supplied to \`${ componentName }\`, expect null.`
		);
	}
}

export default {
	timeFormat: createChainableValidator( timeFormat ),
	nullType: createChainableValidator( nullType ),
};
