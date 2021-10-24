import React, { useMemo } from 'react';
import PropTypes from 'prop-types';

/**
 * Pods dependencies
 */
import FieldWrapper from 'dfv/src/components/field-wrapper';
import { FIELD_PROP_TYPE_SHAPE } from 'dfv/src/config/prop-types';

const FieldSet = ( {
	fields,
	podType,
	podName,
	allPodFields,
	allPodValues,
	setOptionValue,
} ) => {
	// Only calculate this once - this assumes that the array of all fields
	// for the Pod does not change, to save render time.
	const allPodFieldsMap = useMemo( () => {
		return new Map(
			allPodFields.map( ( fieldData ) => [ fieldData.name, fieldData ] )
		);
	}, [] );

	return fields.map( ( field ) => {
		const {
			type,
			name,
			boolean_group: booleanGroup,
		} = field;

		// Boolean Group fields get a map of values instead of a single value.
		const isGroupField = 'boolean_group' === type;

		const valuesWithDefaults = {};

		if ( isGroupField ) {
			( booleanGroup || [] ).forEach( ( subField ) => {
				// If the value is undefined (not falsy), use the default value.
				valuesWithDefaults[ subField.name ] = ( 'undefined' !== typeof allPodValues[ subField.name ] )
					? allPodValues[ subField.name ]
					: subField.default;
			} );
		}

		// If the value is undefined (not falsy), use the default value.
		const valueOrDefault = ( 'undefined' !== typeof allPodValues[ name ] )
			? allPodValues[ name ]
			: field?.default;

		return (
			<FieldWrapper
				key={ name }
				field={ field }
				value={ isGroupField ? undefined : valueOrDefault }
				values={ isGroupField ? valuesWithDefaults : undefined }
				setOptionValue={ setOptionValue }
				podType={ podType }
				podName={ podName }
				allPodFieldsMap={ allPodFieldsMap }
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
	 * Pod type being edited.
	 */
	podType: PropTypes.string.isRequired,

	/**
	 * Pod slug being edited.
	 */
	podName: PropTypes.string.isRequired,

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
