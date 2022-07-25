/**
 * External dependencies
 */
import React from 'react';
import PropTypes from 'prop-types';

/**
 * WordPress Dependencies
 */
import { withSelect, withDispatch } from '@wordpress/data';
import { compose } from '@wordpress/compose';
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

const getDynamicParamValue = ( paramFormat, paramOption, paramDefault, value ) => {
	// No param option set, just return the plain param value.
	if ( ! paramOption ) {
		return paramFormat;
	}

	// Replace the %s with the value as necessary.
	return sprintf( paramFormat, value || paramDefault || MISSING );
};

const DynamicTabContent = ( {
	storeKey,
	podType,
	podName,
	tabOptions,
	allPodFields,
	allPodValues,
	setOptionValue,
	setOptionsValues,
} ) => {
	const fields = tabOptions.map( ( tabOption ) => {
		const {
			description: optionDescription,
			description_param: optionDescriptionParam,
			description_param_default: optionDescriptionParamDefault,
			help: optionHelp,
			help_param: optionHelpParam,
			help_param_default: optionHelpParamDefault,
			label: optionLabel,
			label_param: optionLabelParam,
			label_param_default: optionLabelParamDefault,
			placeholder: optionPlaceholder,
			placeholder_param: optionPlaceholderParam,
			placeholder_param_default: optionPlaceholderParamDefault,
			html_content: optionHtmlContent,
			html_content_param: optionHtmlContentParam,
			html_content_param_default: optionHtmlContentParamDefault,
		} = tabOption;

		return {
			...tabOption,
			description: getDynamicParamValue(
				optionDescription,
				optionDescriptionParam,
				optionDescriptionParamDefault,
				allPodValues[ optionDescriptionParam ]
			),
			help: getDynamicParamValue(
				optionHelp,
				optionHelpParam,
				optionHelpParamDefault,
				allPodValues[ optionHelpParam ]
			),
			label: getDynamicParamValue(
				optionLabel,
				optionLabelParam,
				optionLabelParamDefault,
				allPodValues[ optionLabelParam ]
			),
			placeholder: getDynamicParamValue(
				optionPlaceholder,
				optionPlaceholderParam,
				optionPlaceholderParamDefault,
				allPodValues[ optionPlaceholderParam ]
			),
			html_content: getDynamicParamValue(
				optionHtmlContent,
				optionHtmlContentParam,
				optionHtmlContentParamDefault,
				allPodValues[ optionHtmlContentParam ]
			),
		};
	} );

	return (
		<FieldSet
			storeKey={ storeKey }
			podType={ podType }
			podName={ podName }
			fields={ fields }
			allPodFields={ allPodFields }
			allPodValues={ processAllPodValues( allPodFields, allPodValues ) }
			setOptionValue={ setOptionValue }
			setOptionsValues={ setOptionsValues }
		/>
	);
};

DynamicTabContent.propTypes = {
	/**
	 * Redux store key.
	 */
	storeKey: PropTypes.string.isRequired,

	/**
	 * Pod type being edited.
	 */
	podType: PropTypes.string.isRequired,

	/**
	 * Pod slug being edited.
	 */
	podName: PropTypes.string.isRequired,

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

	/**
	 * Function to update the values of multiple options.
	 */
	setOptionsValues: PropTypes.func.isRequired,
};

export default compose( [
	withSelect( ( select, ownProps ) => {
		const { storeKey } = ownProps;

		const storeSelect = select( storeKey );

		return {
			podType: storeSelect.getPodOption( 'type' ),
			podName: storeSelect.getPodOption( 'name' ),
		};
	} ),
	withDispatch( ( dispatch, ownProps ) => {
		const { storeKey } = ownProps;

		return {
			setOptionsValues: dispatch( storeKey ).setOptionsValues,
		};
	} ),
] )( DynamicTabContent );
