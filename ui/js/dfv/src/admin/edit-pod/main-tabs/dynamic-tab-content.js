import React from 'react';
import * as PropTypes from 'prop-types';

// WordPress Dependencies
import { __, sprintf } from '@wordpress/i18n';

// Pod dependencies
import DependentFieldOption from 'dfv/src/components/dependent-field-option';
import { FIELD_PROP_TYPE_SHAPE } from 'dfv/src/config/prop-types';

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

	return tabOptions.map( ( field ) => {
		const {
			defaultValue,
			label,
			labelParam,
			name,
		} = field;

		return (
			<DependentFieldOption
				key={ name }
				field={ {
					...field,
					label: getLabelValue( label, labelParam, defaultValue ),
				} }
				value={ optionValues[ name ] }
				allOptionValues={ optionValues }
				setOptionValue={ setOptionValue }
			/>
		);
	} );
};

DynamicTabContent.propTypes = {
	tabOptions: PropTypes.arrayOf( FIELD_PROP_TYPE_SHAPE ).isRequired,
	optionValues: PropTypes.object.isRequired,
	setOptionValue: PropTypes.func.isRequired,
};

export default DynamicTabContent;
