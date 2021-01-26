/**
 * External dependencies
 */
import React from 'react';
import PropTypes from 'prop-types';

/**
 * WordPress Dependencies
 */
import { __, sprintf } from '@wordpress/i18n';

/**
 * Pods dependencies
 */
import FieldSet from 'dfv/src/components/field-set';
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

	const fields = tabOptions.map( ( tabOption ) => {
		return {
			...tabOption,
			label: getLabelValue( tabOption.label, tabOption.labelParam, tabOption.defaultValue ),
		};
	} );

	return (
		<FieldSet
			fields={ fields }
			optionValues={ optionValues }
			setOptionValue={ setOptionValue }
		/>
	);
};

DynamicTabContent.propTypes = {
	tabOptions: PropTypes.arrayOf( FIELD_PROP_TYPE_SHAPE ).isRequired,
	optionValues: PropTypes.object.isRequired,
	setOptionValue: PropTypes.func.isRequired,
};

export default DynamicTabContent;
