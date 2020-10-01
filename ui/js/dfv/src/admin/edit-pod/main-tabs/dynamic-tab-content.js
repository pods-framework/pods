import React from 'react';
import * as PropTypes from 'prop-types';

// WordPress Dependencies
import { __, sprintf } from '@wordpress/i18n';

// Pod dependencies
import DependentFieldOption from 'dfv/src/components/dependent-field-option';
import { FIELD_PROP_TYPE_SHAPE } from 'dfv/src/prop-types';

const MISSING = __( '[MISSING DEFAULT]', 'pods' );

const DynamicTabContent = ( {
	tabOptions,
	optionValues,
	setOptionValue,
} ) => {
	const getLabelValue = ( labelFormat, paramOption, paramDefault ) => {
		if ( ! paramOption ) {
			return labelFormat;
		}

		const param = optionValues[ paramOption ] || paramDefault || MISSING;
		return sprintf( labelFormat, param );
	};

	return tabOptions.map( ( {
		name,
		required = false,
		default: defaultValue,
		description,
		data,
		type,
		label,
		label_param: labelParam,
		help,
		'depends-on': dependsOn,
	} ) => (
		<DependentFieldOption
			key={ name }
			fieldType={ type }
			name={ name }
			required={ required }
			description={ description }
			label={ getLabelValue( label, labelParam, defaultValue ) }
			data={ data }
			allOptionValues={ optionValues }
			value={ optionValues[ name ] }
			default={ defaultValue }
			dependents={ dependsOn }
			helpText={ help }
			setOptionValue={ setOptionValue }
		/>
	) );
};

DynamicTabContent.propTypes = {
	tabOptions: PropTypes.arrayOf( FIELD_PROP_TYPE_SHAPE ).isRequired,
	optionValues: PropTypes.object.isRequired,
	setOptionValue: PropTypes.func.isRequired,
};

export default DynamicTabContent;
