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

	console.log( 'meetsDependencies', dependencies, allOptionValues );

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
	label,
	value,
	allOptionValues,
	dependents,
	description,
	helpText,
	setOptionValue,
} ) => {
	const handleInputChange = ( event ) => {
		const { target } = event;

		if ( 'checkbox' === target.type ) {
			const binaryStringFromBoolean = target.checked ? '1' : '0';

			setOptionValue( name, binaryStringFromBoolean );
		} else {
			setOptionValue( name, target.value );
		}
	};

	if ( ! meetsDependencies( dependents, allOptionValues ) ) {
		return null;
	}

	return (
		<PodsFieldOption
			fieldType={ fieldType }
			name={ name }
			value={ value }
			label={ label }
			onChange={ handleInputChange }
			helpText={ helpText }
			description={ description }
		/>
	);
};

DependentFieldOption.propTypes = {
	fieldType: PropTypes.string.isRequired,
	name: PropTypes.string.isRequired,
	default: PropTypes.oneOfType( [
		PropTypes.string,
		PropTypes.bool,
		PropTypes.number,
	] ),
	description: PropTypes.string,
	label: PropTypes.string.isRequired,
	dependents: PropTypes.object,
	helpText: PropTypes.string,
	value: PropTypes.oneOfType( [
		PropTypes.string,
		PropTypes.bool,
		PropTypes.number,
	] ),
	setOptionValue: PropTypes.func.isRequired,
};

export default DependentFieldOption;
