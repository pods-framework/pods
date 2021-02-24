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
	allPodFields,
	allPodValues,
	setOptionValue,
} ) => {
	const getLabelValue = ( labelFormat, paramOption, paramDefault ) => {
		if ( ! paramOption ) {
			return labelFormat;
		}

		const param = allPodValues[ paramOption ] || paramDefault || MISSING;
		return sprintf( labelFormat, param );
	};

	const fields = tabOptions.map( ( tabOption ) => {
		const {
			label: optionLabel,
			label_param: optionLabelParam,
			label_param_default: optionLabelParamDefault,
		} = tabOption;

		return {
			...tabOption,
			label: getLabelValue( optionLabel, optionLabelParam, optionLabelParamDefault ),
		};
	} );

	return (
		<FieldSet
			fields={ fields }
			allPodFields={ allPodFields }
			allPodValues={ allPodValues }
			setOptionValue={ setOptionValue }
		/>
	);
};

DynamicTabContent.propTypes = {
	/**
	 * Array of fields that should be rendered.
	 */
	tabOptions: PropTypes.arrayOf( FIELD_PROP_TYPE_SHAPE ).isRequired,

	/**
	 * All fields from the Pod, including ones that belong to other groups.
	 */
	allPodFields: PropTypes.arrayOf( FIELD_PROP_TYPE_SHAPE ).isRequired,

	/**
	 * A map object with all of the Pod's current values.
	 */
	allPodValues: PropTypes.object.isRequired,

	/**
	 * Function to update the field's value on change.
	 */
	setOptionValue: PropTypes.func.isRequired,
};

export default DynamicTabContent;
