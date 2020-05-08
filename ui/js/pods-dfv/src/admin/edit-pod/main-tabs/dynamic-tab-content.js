import React from 'react';
import * as PropTypes from 'prop-types';
import { each, isObject } from 'lodash';

// WordPress Dependencies
// noinspection JSUnresolvedVariable
const { sprintf, __ } = wp.i18n;

// Pod dependencies
import { PodsFieldOption } from 'pods-dfv/src/components/field-option';

const MISSING = __( '[MISSING DEFAULT]', 'pods' );

/**
 * option data format
 * {
 *     optionName: {
 *         // default may get removed... merge it into value on the server side,
 *         // it's a one-time thing
 *         default: '',
 *         depends-on: { optionName: dependentValue },
 *         help: 'help',
 *         label: 'XXX %s',
 *         label_param: 'optionName',
 *         param_default: 'Item',
 *         type: 'text, boolean, number, pick, file'
 *         value: ''
 *     }
 * }
 */

/**
 * DynamicTabContent
 *
 * @param props
 */
export const DynamicTabContent = ( props ) => {
	const { tabOptions, getOptionValue, setOptionValue } = props;

	const getLabelValue = ( labelFormat, paramOption, paramDefault ) => {
		if ( ! paramOption ) {
			return labelFormat;
		}

		const param = getOptionValue( paramOption ) || paramDefault || MISSING;
		return sprintf( labelFormat, param );
	};

	return tabOptions.map( ( option ) => (
		<DependentFieldOption
			key={ option.name }
			fieldType={ option.type }
			name={ option.name }
			label={ getLabelValue( option.label, option.label_param, option.param_default ) }
			value={ option.value || '' }
			dependents={ option[ 'depends-on' ] }
			helpText={ option.help }
			getOptionValue={ getOptionValue }
			setOptionValue={ setOptionValue }
		/>
	) );
};
DynamicTabContent.propTypes = {
	tabOptions: PropTypes.array.isRequired,
	getOptionValue: PropTypes.func.isRequired,
	setOptionValue: PropTypes.func.isRequired,
};

/**
 * DependentFieldOption
 *
 * Conditionally display a FieldOption (depends-on support)
 *
 * @param props
 */
const DependentFieldOption = ( props ) => {
	const { fieldType, name, label, value, dependents } = props;
	const { getOptionValue, setOptionValue } = props;

	const handleInputChange = ( e ) => {
		const target = e.target;
		const value = 'checkbox' === target.type ? target.checked : target.value;

		setOptionValue( name, value );
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
			helpText={ props.helpText }
		/>
	);
};
DependentFieldOption.propTypes = {
	fieldType: PropTypes.string.isRequired,
	name: PropTypes.string.isRequired,
	value: PropTypes.any.isRequired,
	label: PropTypes.string.isRequired,
	dependents: PropTypes.object,
	helpText: PropTypes.any,
	getOptionValue: PropTypes.func.isRequired,
	setOptionValue: PropTypes.func.isRequired,
};

/**
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
			// noinspection EqualityComparisonWithCoercionJS
			if ( getOptionValue( dependentOptionName ) != dependentValue ) {
				retVal = false;
				return false; // Early-exits the loop only, not the function
			}
		} );
	}

	return retVal;
};
