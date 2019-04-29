import React from 'react';
import PropTypes from 'prop-types';
import { each, isObject } from 'lodash';

import { PodsFieldOption } from 'pods-dfv/src/components/field-option';

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
	const getLabelParam = ( optionName ) => {
		return optionName ? props.getOptionValue( optionName ) : null;
	};

	return props.tabOptions.map( thisOption => (
		<DependentFieldOption
			key={thisOption.name}
			name={thisOption.name}
			labelFormat={thisOption.label}
			labelParam={getLabelParam( thisOption.label_param )}
			labelParamDefault={thisOption.param_default}
			value={thisOption.value}
			default={thisOption.default}
			dependents={thisOption[ 'depends-on' ]}
			getOptionValue={props.getOptionValue}
			setOptionValue={props.setOptionValue}
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
	const { name, labelFormat, labelParam, labelParamDefault, value, dependents } = props;
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
			labelFormat={labelFormat}
			labelParam={labelParam}
			labelParamDefault={labelParamDefault}
			default={props.default} // default is a keyword and can't be a const name
			onChange={onChange}
		/>
	);
};
DependentFieldOption.propTypes = {
	name: PropTypes.string.isRequired,
	value: PropTypes.any.isRequired,
	labelFormat: PropTypes.string.isRequired,
	labelParam: PropTypes.string,
	labelParamDefault: PropTypes.string,
	default: PropTypes.any,
	dependents: PropTypes.object,
	getOptionValue: PropTypes.func.isRequired,
	setOptionValue: PropTypes.func.isRequired,
};

/**
 *
 * @param {object}   dependencies   dictionary in the form optionName: requiredVal
 * @param {function} getOptionValue selector to lookup option values
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
