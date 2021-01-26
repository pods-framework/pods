import React from 'react';
import PropTypes from 'prop-types';

// WordPress Dependencies
import { __, sprintf } from '@wordpress/i18n';

// Pods dependencies
import FieldOption from 'dfv/src/components/field-wrapper';
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

		const dependencyValueEntries = Object
			.keys( field[ 'depends-on' ] || {} )
			.map( ( fieldName ) => ( [
				fieldName,
				optionValues[ fieldName ],
			] ) );

		return (
			<FieldOption
				key={ name }
				field={ {
					...field,
					label: getLabelValue( label, labelParam, defaultValue ),
				} }
				value={ optionValues[ name ] }
				setOptionValue={ setOptionValue }
				dependencyValues={ Object.fromEntries( dependencyValueEntries ) }
			/>
		);
	} );
};

DynamicTabContent.propTypes = {
	podType: PropTypes.string.isRequired,
	podName: PropTypes.string.isRequired,
	tabOptions: PropTypes.arrayOf( FIELD_PROP_TYPE_SHAPE ).isRequired,
	optionValues: PropTypes.object.isRequired,
	setOptionValue: PropTypes.func.isRequired,
};

export default DynamicTabContent;
