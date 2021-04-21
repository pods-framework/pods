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

/**
 * Process pod values, to correct some inconsistencies from how
 * the fields expect values compared to the API, specifically for
 * pick_object fields.
 *
 * @param {Array} fields        Array of all field data.
 * @param {Object} allPodValues Map of all field keys to values.
 *
 * @return {Array} Updated map of all field keys to values.
 */
const processAllPodValues = ( fields, allPodValues ) => {
	// Workaround for the pick_object value: this value should be changed
	// to a combination of the `pick_object` sent by the API and the
	// `pick_val`. This was originally done to make the form easier to select.
	//
	// But this processing may not need to happen - it'll get set correctly
	// after a UI update, but will be wrong after the update from saving to the API,
	// so we'll check that the values haven't already been merged.
	if ( ! allPodValues.pick_object || ! allPodValues.pick_val ) {
		return allPodValues;
	}

	const pickObjectField = fields.find( ( field ) => 'pick_object' === field.name );

	if ( ! pickObjectField ) {
		return allPodValues;
	}

	// Each of the options are under a header to distinguish the types.
	const pickObjectFieldPossibleOptions = Object.keys( pickObjectField.data || {} ).reduce(
		( accumulator, currentKey ) => {
			if ( 'string' === typeof pickObjectField.data?.[ currentKey ] ) {
				return [
					...accumulator,
					pickObjectField.data[ currentKey ],
				];
			} else if ( 'object' === typeof pickObjectField.data?.[ currentKey ] ) {
				return [
					...accumulator,
					...( Object.keys( pickObjectField.data[ currentKey ] ) ),
				];
			}
			return accumulator;
		},
		[]
	);

	const pickObject = allPodValues.pick_object;
	const pickVal = allPodValues.pick_val;

	if (
		! pickObjectFieldPossibleOptions.includes( pickObject ) &&
		! pickObject.endsWith( `-${ pickVal }` )
	) {
		allPodValues.pick_object = `${ pickObject }-${ pickVal }`;
	}

	return allPodValues;
};

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
			allPodValues={ processAllPodValues( allPodFields, allPodValues ) }
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
