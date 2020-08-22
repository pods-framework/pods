import React from 'react';
import * as PropTypes from 'prop-types';

// Pod dependencies
import PodsFieldOption from 'dfv/src/components/field-option';
import validateFieldDependencies from 'dfv/src/helpers/validateFieldDependencies';

// Conditionally display a FieldOption (depends-on support)
const DependentFieldOption = ( {
	fieldType,
	name,
	required,
	label,
	value,
	default: defaultValue,
	data,
	allOptionValues,
	dependents,
	description,
	helpText,
	setOptionValue,
} ) => {
	const handleInputChange = ( event ) => {
		const { target } = event;

		// If there's a default, then don't allow an empty value.
		const newValue = target.value || defaultValue;

		if ( 'checkbox' === target.type ) {
			const binaryStringFromBoolean = target.checked ? '1' : '0';

			setOptionValue( name, binaryStringFromBoolean );
		} else {
			setOptionValue( name, newValue );
		}
	};

	if ( ! validateFieldDependencies( allOptionValues, dependents ) ) {
		return null;
	}

	return (
		<PodsFieldOption
			fieldType={ fieldType }
			name={ name }
			required={ required }
			value={ value || defaultValue }
			label={ label }
			data={ data }
			onChange={ handleInputChange }
			helpText={ helpText }
			description={ description }
		/>
	);
};

DependentFieldOption.propTypes = {
	fieldType: PropTypes.string.isRequired,
	name: PropTypes.string.isRequired,
	required: PropTypes.bool.isRequired,
	data: PropTypes.oneOfType( [
		PropTypes.object,
		PropTypes.array,
	] ),
	default: PropTypes.oneOfType( [
		PropTypes.string,
		PropTypes.bool,
		PropTypes.number,
	] ),
	description: PropTypes.string,
	label: PropTypes.string.isRequired,
	dependents: PropTypes.oneOfType( [
		// The API may provide an empty array if empty, or an object
		// if there are any values.
		PropTypes.array,
		PropTypes.object,
	] ),
	helpText: PropTypes.oneOfType( [
		PropTypes.string,
		PropTypes.arrayOf( PropTypes.string ),
	] ),
	value: PropTypes.oneOfType( [
		PropTypes.string,
		PropTypes.bool,
		PropTypes.number,
	] ),
	setOptionValue: PropTypes.func.isRequired,
};

export default DependentFieldOption;
