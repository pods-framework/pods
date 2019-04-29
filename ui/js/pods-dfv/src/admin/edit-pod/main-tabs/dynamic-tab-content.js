import React from 'react';
import PropTypes from 'prop-types';
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
 *         default: '',
 *         depends-on: { optionName: dependentValue },
 *         help: 'help',
 *         label: 'XXX %s',
 *         label_param: 'OptionName',
 *         param_default: 'Item',
 *         type: 'text, checkbox, pick'
 *         value: ''
 *     }
 * }
 */

/**
 * DynamicTabContent
 */
export const DynamicTabContent = ( props ) => {
	const { tabOptions, getOptionValue, setOptionValue } = props;

	const getLabelValue = ( labelFormat, paramOption, paramDefault ) => {
		if ( !paramOption ) {
			return labelFormat;
		}

		const param = getOptionValue( paramOption ) || paramDefault || MISSING;

		return sprintf( labelFormat, param );
	};

	return tabOptions.map( thisOption => (
		<DependentFieldOption
			key={thisOption.name}
			name={thisOption.name}
			label={getLabelValue( thisOption.label, thisOption.label_param, thisOption.param_default )}
			value={thisOption.value}
			default={thisOption.default}
			dependents={thisOption[ 'depends-on' ]}
			getOptionValue={getOptionValue}
			setOptionValue={setOptionValue}
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
 */
const DependentFieldOption = ( props ) => {
	const { name, label, value, dependents } = props;
	const { getOptionValue, setOptionValue } = props;

	const onChange = ( e ) => {
		setOptionValue( name, e.target.value );
	};

	if ( !meetsDependencies( dependents, getOptionValue ) ) {
		return null;
	}

	return (
		<PodsFieldOption
			name={name}
			value={value}
			label={label}
			default={props.default} // default is a keyword and can't be a const name
			onChange={onChange}
		/>
	);
};
DependentFieldOption.propTypes = {
	name: PropTypes.string.isRequired,
	value: PropTypes.any.isRequired,
	label: PropTypes.string.isRequired,
	default: PropTypes.any,
	dependents: PropTypes.object,
	getOptionValue: PropTypes.func.isRequired,
	setOptionValue: PropTypes.func.isRequired,
};

/**
 *
 * @param {object}   dependencies   dictionary in the form optionName: requiredVal
 * @param {function} getOptionValue selector to lookup option values by name
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
