/**
 * External dependencies
 */
import React from 'react';
import PropTypes from 'prop-types';

/**
 * Pods components
 */
import BooleanGroupSubfield from 'dfv/src/fields/boolean-group/boolean-group-subfield';

/**
 * Other Pods dependencies
 */
import { FIELD_PROP_TYPE_SHAPE } from 'dfv/src/config/prop-types';
import { toBool } from 'dfv/src/helpers/booleans';

import './boolean-group.scss';

const BooleanGroup = ( {
	fieldConfig = {},
	setOptionValue,
	values,
	allPodValues,
	allPodFieldsMap,
	setHasBlurred,
} ) => {
	const {
		boolean_group: booleanGroup = [],
	} = fieldConfig;

	const toggleChange = ( name ) => () => {
		console.log( 'changing boolean subgroup value', name, ! toBool( values[ name ] ) );

		setOptionValue( name, ! toBool( values[ name ] ) );

		setHasBlurred();
	};

	return (
		<ul className="pods-boolean-group">
			{ booleanGroup.map( ( subField ) => {
				const { name } = subField;

				return (
					<BooleanGroupSubfield
						subfieldConfig={ {
							...subField,
						} }
						checked={ toBool( values[ name ] ) }
						toggleChange={ toggleChange( name ) }
						allPodValues={ allPodValues }
						allPodFieldsMap={ allPodFieldsMap }
						key={ subField.name }
					/>
				);
			} ) }
		</ul>
	);
};

BooleanGroup.propTypes = {
	/**
	 * Field config.
	 */
	fieldConfig: FIELD_PROP_TYPE_SHAPE,

	/**
	 * Function to update the field's value on change.
	 */
	setOptionValue: PropTypes.func.isRequired,

	/**
	 * Used to notify the FieldWrapper that an onBlur event has
	 * occurred, for validating purposes.
	 */
	setHasBlurred: PropTypes.func.isRequired,

	/**
	 * Subfield values.
	 */
	values: PropTypes.object,

	/**
	 * All field values for the Pod to use for
	 * validating dependencies.
	 */
	allPodValues: PropTypes.object.isRequired,

	/**
	 * All fields from the Pod, including ones that belong to other groups. This
	 * should be a Map object, keyed by the field name, to make lookup easier.
	 */
	allPodFieldsMap: PropTypes.object,
};

export default BooleanGroup;
