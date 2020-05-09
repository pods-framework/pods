/**
 * Returns an "attributes" object, as expected by registerBlockType(),
 * based on the field data.
 *
 * @param {Array} fields Array of field data, each containing a `name`
 *                       and array of `attributeOptions`.
 *
 * @returns {Object} Attributes object to pass to registerBlockType().
 */
const createAttributesFromFields = ( fields ) =>
	fields.reduce( ( attributes, currentField ) => {
		if ( ! currentField.name ) {
			return attributes;
		}

		const { name, attributeOptions } = currentField;

		return {
			...attributes,
			[ name ]: {
				...attributeOptions,
				// Default to setting the attribute type to "string"
				// if one wasn't provided.
				type: attributeOptions.type || 'string',
			},
		};
	}, {} );

export default createAttributesFromFields;
