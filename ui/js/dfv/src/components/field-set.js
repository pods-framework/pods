import React from 'react';
import PropTypes from 'prop-types';

/**
 * Pods dependencies
 */
import FieldWrapper from 'dfv/src/components/field-wrapper';
import { FIELD_PROP_TYPE_SHAPE } from 'dfv/src/config/prop-types';

const FieldSet = ( {
	fields,
	allPodFields,
	allPodValues,
	setOptionValue,
} ) => {
	return fields.map( ( field ) => {
		const { name } = field;

		return (
			<FieldWrapper
				key={ name }
				field={ field }
				value={ allPodValues[ name ] }
				setOptionValue={ setOptionValue }
				allPodFields={ allPodFields }
				allPodValues={ allPodValues }
			/>
		);
	} );
};

FieldSet.propTypes = {
	/**
	 * Array of fields that should be rendered in the set.
	 */
	fields: PropTypes.arrayOf( FIELD_PROP_TYPE_SHAPE ).isRequired,

	/**
	 * All fields from the Pod, including ones that belong to other groups.
	 */
	allPodFields: PropTypes.arrayOf( FIELD_PROP_TYPE_SHAPE ).isRequired,

	/**
	 * A map object with all of the Pod's current values.
	 */
	allPodValues: PropTypes.object.isRequired,

	/**
	 * Function to update the field's value on change.
	 */
	setOptionValue: PropTypes.func.isRequired,
};

export default FieldSet;
