import React from 'react';
import * as PropTypes from 'prop-types';
import { each, isObject } from 'lodash';

// Pod dependencies
import PodsFieldOption from 'dfv/src/components/field-option';

/**
 * Check if option meets dependencies, helper function.
 *
 * @param {Object|Object[]} dependencies Dictionary in the form optionName: requiredVal
 * @param {Object} allOptionValues Map of all option values.
 *
 * @return {boolean} Whether or not the specified dependencies are met
 */
const meetsDependencies = ( dependencies, allOptionValues ) => {
	let retVal = true;

	if ( dependencies && isObject( dependencies ) ) {
		each( dependencies, ( dependentValue, dependentOptionName ) => {
			// Loose comparison required, values may be 1/0 expecting true/false
			// eslint-disable-next-line eqeqeq
			if ( allOptionValues[ dependentOptionName ] != dependentValue ) {
				retVal = false;
				return false; // Early-exits the loop only, not the function
			}
		} );
	}

	return retVal;
};

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

	if ( ! meetsDependencies( dependents, allOptionValues ) ) {
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
