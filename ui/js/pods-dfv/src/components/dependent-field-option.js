import React from 'react';
import * as PropTypes from 'prop-types';
import { each, isObject } from 'lodash';

// Pod dependencies
import PodsFieldOption from 'pods-dfv/src/components/field-option';

/**
 * Check if option meets dependencies, helper function.
 *
 * @param {Object|Object[]} dependencies Dictionary in the form optionName: requiredVal
 * @param {Function} getOptionValue Selector to lookup option values by name
 *
 * @return {boolean} Whether or not the specified dependencies are met
 */
const meetsDependencies = ( dependencies, getOptionValue ) => {
	let retVal = true;

	if ( dependencies && isObject( dependencies ) ) {
		each( dependencies, ( dependentValue, dependentOptionName ) => {
			// Loose comparison required, values may be 1/0 expecting true/false
			if ( getOptionValue( dependentOptionName ) !== dependentValue ) {
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
	dependents,
	help,
	getOptionValue,
	setOptionValue,
} ) => {
	const handleInputChange = ( e ) => {
		const target = e.target;
		const checkedValue = 'checkbox' === target.type ? target.checked : target.value;

		setOptionValue( name, checkedValue );
	};

	if ( ! meetsDependencies( dependents, getOptionValue ) ) {
		return null;
	}

	return (
		<PodsFieldOption
			fieldType={ fieldType }
			name={ name }
			value={ value }
			label={ label }
			onChange={ handleInputChange }
			help={ help }
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
	label: PropTypes.string.isRequired,
	dependents: PropTypes.object,
	help: PropTypes.string.isRequired,
	getOptionValue: PropTypes.func.isRequired,
	setOptionValue: PropTypes.func.isRequired,
};

export default DependentFieldOption;
