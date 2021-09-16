/**
 * External dependencies
 */
import { renderToString } from '@wordpress/element';
import parse from 'html-react-parser';
import sanitizeHtml from 'sanitize-html';

/**
 * Internal dependencies
 */
import { richText } from '../config/html';

/**
 * Renders the edit template into an edit component.
 *
 * @todo Is there a more efficient way to do this without
 * rendering React components to a string, then parsing the
 * final string back to a React component? The parser may have
 * callbacks that we can use.
 *
 * @param {string} renderTemplate Template string.
 * @param {Object} fields Fields used in the Block.
 * @param {Object} attributes Block attributes with values.
 * @param {Function} renderField Function that should return a rendered field.
 * @param {Function} setAttributes setAttributes function for the block (Optional).
 */
const renderBlockTemplate = (
	renderTemplate = '',
	fields = [],
	attributes = {},
	renderField,
	setAttributes
) => {
	let htmlWithRenderedFields = sanitizeHtml( renderTemplate, richText );

	// Replace all of the placeholders in the format of `{@fieldName}` with the
	// rendered field. To do this, we first need to create the React component for
	// the field, then convert it down into a string. To avoid losing any of the props
	// during this conversion, we're saving any props from the component based on the name.
	// At the end, the whole string will be parsed back into React components.
	const savedProps = [];

	fields.forEach( ( field ) => {
		// Our renderField may or may not take a setAttributes function as a parameter.
		const fieldComponent = ( 'function' === typeof setAttributes )
			? renderField( field, attributes, setAttributes )
			: renderField( field, attributes );

		if ( !! fieldComponent ) {
			savedProps[ field.name ] = { ...fieldComponent.props };
		}

		const renderedField = !! fieldComponent ? renderToString( fieldComponent ) : '';

		htmlWithRenderedFields = htmlWithRenderedFields.replace(
			new RegExp( `{@${ field.name }}`, 'g' ),
			renderedField
		);
	} );

	// @todo Support <InnerBlocks>
	return parse( htmlWithRenderedFields );
};

export default renderBlockTemplate;
