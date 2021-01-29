import React from 'react';
import PropTypes from 'prop-types';

/**
 * Pods dependencies
 */
import FieldWrapper from 'dfv/src/components/field-wrapper';
import { FIELD_PROP_TYPE_SHAPE } from 'dfv/src/config/prop-types';

const FieldSet = ( {
	fields,
	optionValues,
	setOptionValue,
} ) => {
	return fields.map( ( field ) => {
		const { name } = field;

		const dependencyValueEntries = Object
			.keys( field[ 'depends-on' ] || {} )
			.map( ( fieldName ) => ( [
				fieldName,
				optionValues[ fieldName ],
			] ) );

		return (
			<FieldWrapper
				key={ name }
				field={ field }
				value={ optionValues[ name ] }
				setOptionValue={ setOptionValue }
				dependencyValues={ Object.fromEntries( dependencyValueEntries ) }
			/>
		);
	} );
};

FieldSet.propTypes = {
	fields: PropTypes.arrayOf( FIELD_PROP_TYPE_SHAPE ).isRequired,
	setOptionValue: PropTypes.func.isRequired,
};

export default FieldSet;
