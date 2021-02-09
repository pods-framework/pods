/**
 * External dependencies
 */
import React, { useState, useEffect, useRef } from 'react';
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
		wildcardValues,
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
		'wildcard-on': wildcardOn,
	} = field;

	const [ meetsDependencies, setMeetsDependencies ] = useState( false );

	const fieldRef = useRef( null );

	const dataOptions = useBidirectionalFieldData( data );

	// Find the component for the field type
	const FieldComponent = FIELD_MAP[ fieldType ]?.fieldComponent;

	useEffect( () => {
		const dependsOnLength = Object.keys( dependsOn || {} ).length;
		const excludesOnLength = Object.keys( excludesOn || {} ).length;
		const wildcardOnLength = Object.keys( wildcardOn || {} ).length;

		if (
			( dependsOnLength && ! validateFieldDependencies( dependencyValues, dependsOn ) ) ||
			( excludesOnLength && ! validateFieldDependencies( exclusionValues, excludesOn, 'excludes' ) ) ||
			( wildcardOnLength && ! validateFieldDependencies( wildcardValues, wildcardOn, 'wildcard' ) )
		) {
			setMeetsDependencies( false );
		} else {
			setMeetsDependencies( true );
		}
	}, [ dependencyValues, exclusionValues, wildcardValues, dependsOn, excludesOn, wildcardOn, setMeetsDependencies ] );

	// Hacky thing to hide the container. This isn't needed on every screen.
	// @todo rework how some fields render so that we don't need to do this.
	useEffect( () => {
		if ( ! fieldRef?.current ) {
			return;
		}

		const outsideOfReactFieldContainer = fieldRef.current.closest( '.pods-field' );

		if ( ! outsideOfReactFieldContainer ) {
			return;
		}

		if ( meetsDependencies ) {
			outsideOfReactFieldContainer.style.display = 'block';
		} else {
			outsideOfReactFieldContainer.style.display = 'none';
		}
	}, [ name, fieldRef, meetsDependencies ] );

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
				value={ value || defaultValue || '' }
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
	if ( ! meetsDependencies ) {
		return <span ref={ fieldRef } />;
	}

	// @todo include other layouts, how to know which one to use?
	return (
		<>
			<span ref={ fieldRef } />
			<DivFieldLayout
				labelComponent={ layoutComponent }
				descriptionComponent={ descriptionComponent }
				inputComponent={ inputComponent }
				validationMessagesComponent={ validationMessagesComponent }
				fieldType={ fieldType }
			/>
		</>
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
	wildcardValues: PropTypes.object.isRequired,
};

// Memoize to prevent unnecessary re-renders when the
// dependencyValues/exclusionValues prop changes.
const MemoizedFieldWrapper = React.memo(
	FieldWrapper,
	( prevProps, nextProps ) => isEqual( prevProps, nextProps )
);

export default MemoizedFieldWrapper;
