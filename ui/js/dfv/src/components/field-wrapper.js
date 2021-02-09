/**
 * External dependencies
 */
import React from 'react';
import { isEqual } from 'lodash';
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';

/**
 * Pods components
 */
import DivFieldLayout from 'dfv/src/components/div-field-layout';
import FieldErrorBoundary from 'dfv/src/components/field-error-boundary';
import FieldDescription from 'dfv/src/components/field-description';
import FieldLabel from 'dfv/src/components/field-label';
import ValidationMessages from 'dfv/src/components/validation-messages';

/**
 * Other Pods dependencies
 */
import { requiredValidator } from 'dfv/src/helpers/validators';
import { toBool } from 'dfv/src/helpers/booleans';
import validateFieldDependencies from 'dfv/src/helpers/validateFieldDependencies';
import useValidation from 'dfv/src/hooks/useValidation';
import useBidirectionalFieldData from 'dfv/src/hooks/useBidirectionalFieldData';

import FIELD_MAP from 'dfv/src/fields/field-map';
import { FIELD_PROP_TYPE_SHAPE } from 'dfv/src/config/prop-types';

export const FieldWrapper = ( props ) => {
	const {
		field = {},
		value,
		setOptionValue,
		dependencyValues,
		exclusionValues,
	} = props;

	const {
		data,
		default: defaultValue,
		description,
		helpText,
		label,
		required,
		fieldEmbed = false,
		name,
		type: fieldType,
		html_no_label: htmlNoLabel = false,
		htmlAttr,
		'depends-on': dependsOn,
		'excludes-on': excludesOn,
	} = field;

	const dataOptions = useBidirectionalFieldData( data );

	// Find the component for the field type
	const FieldComponent = FIELD_MAP[ fieldType ]?.fieldComponent;

	// Workaround for the pick_object value: this value should be changed
	// to a combination of the `pick_object` sent by the API and the
	// `pick_val`. This was originally done to make the form easier to select.
	//
	// But this processing may not need to happen - it'll get set correctly
	// after a UI update, but will be wrong after the update from saving to the API,
	// so we'll check that the values haven't already been merged.
	let processedValue = value;
	const processedDependencyAllOptionValues = dependencyValues;
	const processedExclusionAllOptionValues = exclusionValues;

	if (
		'pick_object' === name &&
		dependencyValues.pick_val &&
		! value.includes( `-${ dependencyValues.pick_val }`, `-${ dependencyValues.pick_val }`.length )
	) {
		processedValue = `${ value }-${ dependencyValues.pick_val }`;
		processedDependencyAllOptionValues.pick_object = `${ value }-${ dependencyValues.pick_val }`;
	}

	if (
		'pick_object' === name &&
		exclusionValues.pick_val &&
		! value.includes( `-${ exclusionValues.pick_val }`, `-${ exclusionValues.pick_val }`.length )
	) {
		processedExclusionAllOptionValues.pick_object = `${ value }-${ exclusionValues.pick_val }`;
	}

	// Sort out different shapes that we could get the help text in.
	// It's possible to get an array of strings for the help text, but it
	// will usually be a string.
	const shouldShowHelpText = helpText && ( 'help' !== helpText );

	const helpTextString = Array.isArray( helpText ) ? helpText[ 0 ] : helpText;
	const helpLink = ( Array.isArray( helpText ) && !! helpText[ 1 ] )
		? helpText[ 1 ]
		: undefined;

	const showLabel = (
		'heading' !== fieldType &&
		( 'html' !== fieldType || ! htmlNoLabel ) &&
		! fieldEmbed
	);

	const showDescription = !! description && ! fieldEmbed;

	// The only one set up by default here
	// is to validate a required field, but the field child component
	// may set additional rules.
	const [ validationMessages, addValidationRules ] = useValidation(
		[
			{
				rule: requiredValidator( label ),
				condition: () => true === toBool( required ),
			},
		],
		value
	);

	const layoutComponent = showLabel ? (
		<FieldLabel
			label={ label }
			required={ toBool( required ) }
			htmlFor={ name }
			helpTextString={ shouldShowHelpText ? helpTextString : undefined }
			helpLink={ shouldShowHelpText ? helpLink : undefined }
		/>
	) : undefined;

	const descriptionComponent = showDescription ? (
		<FieldDescription description={ description } />
	) : undefined;

	const inputComponent = !! FieldComponent ? (
		<FieldErrorBoundary>
			<FieldComponent
				value={ processedValue || defaultValue || '' }
				setValue={ ( newValue ) => setOptionValue( name, newValue ) }
				isValid={ !! validationMessages.length }
				addValidationRules={ addValidationRules }
				htmlAttr={ htmlAttr }
				fieldConfig={ {
					...field,
					data: dataOptions || field.data,
				} }
			/>
		</FieldErrorBoundary>
	) : (
		<span className="pods-field-option__invalid-field">
			{ sprintf(
				// translators: %s is the field type.
				__( 'The field type \'%s\' was invalid.', 'pods' ),
				fieldType
			) }
		</span>
	);

	const validationMessagesComponent = validationMessages.length ? (
		<ValidationMessages
			messages={ validationMessages }
		/>
	) : undefined;

	// Don't render a field that hasn't had its dependencies met.
	if ( dependsOn && ! validateFieldDependencies( processedDependencyAllOptionValues, dependsOn ) ) {
		return null;
	}

	// Don't render a field that hasn't had its exclusions met, true here means it has failed.
	if ( excludesOn && validateFieldDependencies( processedExclusionAllOptionValues, excludesOn ) ) {
		return null;
	}

	// @todo include other layouts, how to know which one to use?
	return (
		<DivFieldLayout
			labelComponent={ layoutComponent }
			descriptionComponent={ descriptionComponent }
			inputComponent={ inputComponent }
			validationMessagesComponent={ validationMessagesComponent }
			fieldType={ fieldType }
		/>
	);
};

FieldWrapper.defaultProps = {
	podType: null,
	podName: null,
};

FieldWrapper.propTypes = {
	podType: PropTypes.string,
	podName: PropTypes.string,
	field: FIELD_PROP_TYPE_SHAPE,
	value: PropTypes.oneOfType( [
		PropTypes.string,
		PropTypes.bool,
		PropTypes.number,
		PropTypes.array,
	] ),
	setOptionValue: PropTypes.func.isRequired,
	dependencyValues: PropTypes.object.isRequired,
	exclusionValues: PropTypes.object.isRequired,
};

// Memoize to prevent unnecessary re-renders when the
// dependencyValues/exclusionValues prop changes.
const MemoizedFieldWrapper = React.memo(
	FieldWrapper,
	( prevProps, nextProps ) => isEqual( prevProps, nextProps )
);

export default MemoizedFieldWrapper;
