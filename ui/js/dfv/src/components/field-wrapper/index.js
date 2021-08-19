/**
 * External dependencies
 */
import React, { useEffect, useRef, useState } from 'react';
import { isEqual, uniq } from 'lodash';
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';

/**
 * Pods components
 */
import FieldErrorBoundary from 'dfv/src/components/field-wrapper/field-error-boundary';
import DivFieldLayout from 'dfv/src/components/field-wrapper/div-field-layout';

import FieldDescription from 'dfv/src/components/field-description';
import FieldLabel from 'dfv/src/components/field-label';
import ValidationMessages from 'dfv/src/components/validation-messages';

/**
 * Other Pods dependencies
 */
import { requiredValidator } from 'dfv/src/helpers/validators';
import { toBool } from 'dfv/src/helpers/booleans';
import sanitizeSlug from 'dfv/src/helpers/sanitizeSlug';
import useDependencyCheck from 'dfv/src/hooks/useDependencyCheck';
import useValidation from 'dfv/src/hooks/useValidation';
import useBidirectionalFieldData from 'dfv/src/hooks/useBidirectionalFieldData';

import FIELD_MAP from 'dfv/src/fields/field-map';
import { FIELD_PROP_TYPE_SHAPE } from 'dfv/src/config/prop-types';

export const FieldWrapper = ( props ) => {
	const {
		field = {},
		allPodFieldsMap,
		value,
		values,
		setOptionValue,
		allPodValues,
	} = props;

	const {
		data,
		default: defaultValue,
		description,
		help: helpText,
		label,
		required,
		fieldEmbed = false,
		name,
		type: fieldType,
		html_no_label: htmlNoLabel = false,
		htmlAttr,
	} = field;

	const isBooleanGroupField = 'boolean_group' === fieldType;

	const fieldRef = useRef( null );

	const dataOptions = useBidirectionalFieldData( data );

	// Find the component for the field type
	const FieldComponent = FIELD_MAP[ fieldType ]?.fieldComponent;

	const [ hasBlurred, setHasBlurred ] = useState( false );

	// Calculate dependencies.
	const meetsDependencies = useDependencyCheck(
		field,
		allPodValues,
		allPodFieldsMap,
	);

	// Hacky thing to hide the container. This isn't needed on every screen.
	// @todo rework how some fields render so that we don't need to do this.
	useEffect( () => {
		if ( ! fieldRef?.current ) {
			return;
		}

		const outsideOfReactFieldContainer = fieldRef.current.closest( '.pods-field__container' );

		if ( ! outsideOfReactFieldContainer ) {
			return;
		}

		if ( meetsDependencies ) {
			outsideOfReactFieldContainer.style.display = '';
		} else {
			outsideOfReactFieldContainer.style.display = 'none';
		}
	}, [ name, fieldRef, meetsDependencies ] );

	// Custom placeholder on the "Add Pod" screen.
	const processedHtmlAttr = htmlAttr;

	if ( 'create_name' === name ) {
		processedHtmlAttr.placeholder = sanitizeSlug( allPodValues.create_label_singular );
		console.log( 'setting create_name placeholder', processedHtmlAttr.placeholder );
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

	const labelComponent = showLabel ? (
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
				values={ isBooleanGroupField ? values : undefined }
				// Only the Boolean Group fields need allPodValues and allPodFieldsMap,
				// because the subfields need to reference these.
				allPodValues={ isBooleanGroupField ? allPodValues : undefined }
				allPodFieldsMap={ isBooleanGroupField ? allPodFieldsMap : undefined }
				setOptionValue={ isBooleanGroupField ? setOptionValue : undefined }
				setValue={ isBooleanGroupField ? undefined : ( newValue ) => setOptionValue( name, newValue ) }
				isValid={ !! validationMessages.length }
				addValidationRules={ addValidationRules }
				htmlAttr={ processedHtmlAttr }
				setHasBlurred={ () => setHasBlurred( true ) }
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

	const validationMessagesComponent = ( hasBlurred && validationMessages.length ) ? (
		<ValidationMessages messages={ validationMessages } />
	) : undefined;

	// Don't render a field that hasn't had its dependencies met.
	if ( ! meetsDependencies ) {
		return <span ref={ fieldRef } />;
	}

	return (
		<>
			<span ref={ fieldRef } />
			<DivFieldLayout
				labelComponent={ labelComponent }
				descriptionComponent={ descriptionComponent }
				inputComponent={ inputComponent }
				validationMessagesComponent={ validationMessagesComponent }
				fieldType={ fieldType }
			/>
		</>
	);
};

FieldWrapper.propTypes = {
	/**
	 * Field config.
	 */
	field: FIELD_PROP_TYPE_SHAPE,

	/**
	 * All fields from the Pod, including ones that belong to other groups. This
	 * should be a Map object, keyed by the field name, to make lookup easier.
	 */
	allPodFieldsMap: PropTypes.object,

	/**
	 * Field value (for all fields except boolean_group).
	 */
	value: PropTypes.oneOfType( [
		PropTypes.string,
		PropTypes.bool,
		PropTypes.number,
		PropTypes.array,
	] ),

	/**
	 * Subfield values (for boolean_group).
	 */
	values: PropTypes.object,

	/**
	 * Function to update the field's value on change.
	 */
	setOptionValue: PropTypes.func.isRequired,

	/**
	 * All field values for the Pod to use for
	 * validating dependencies.
	 */
	allPodValues: PropTypes.object.isRequired,
};

// Memoize to prevent unnecessary re-renders
const MemoizedFieldWrapper = React.memo(
	FieldWrapper,
	( prevProps, nextProps ) => {
		// If the value has changed, rerender.
		if (
			prevProps.value !== nextProps.value ||
			! isEqual( prevProps.values, nextProps.values )
		) {
			return false;
		}

		// The label usually won't change, EXCEPT on the "Edit Pod" Labels tab.
		if (
			prevProps.field?.label &&
			nextProps.field?.label &&
			prevProps.field.label !== nextProps.field.label
		) {
			return false;
		}

		// Add an extra dependency for the slug placeholder on the "Add Pod" screen.
		if (
			'create_name' === nextProps.field?.name &&
			'pods-form-ui-create-name' === nextProps.field?.htmlAttr?.id &&
			prevProps.allPodValues?.create_label_singular !== nextProps.allPodValues?.create_label_singular
		) {
			return false;
		}

		// Look up the dependencies, we may need to re-render if any of the
		// values have changed.
		const allDependencyFieldSlugs = [
			...Object.keys( nextProps.field[ 'depends-on' ] || {} ),
			...Object.keys( nextProps.field[ 'depends-on-any' ] || {} ),
			...Object.keys( nextProps.field[ 'excludes-on' ] || {} ),
			...Object.keys( nextProps.field[ 'wildcard-on' ] || {} ),
		];

		// If it's a boolean group, there are also subfields to check.
		if ( 'boolean_group' === nextProps.field?.type ) {
			const subfields = nextProps.field?.boolean_group;

			subfields.forEach( ( subfield ) => {
				allDependencyFieldSlugs.push(
					...Object.keys( subfield[ 'depends-on' ] || {} ),
					...Object.keys( subfield[ 'depends-on-any' ] || {} ),
					...Object.keys( subfield[ 'excludes-on' ] || {} ),
					...Object.keys( subfield[ 'wildcard-on' ] || {} ),
				);
			} );
		}

		// If there were no dependencies, we don't need to look any further.
		if ( 0 === allDependencyFieldSlugs.length ) {
			return true;
		}

		// Look up the tree of dependencies, for parents of the dependencies.
		const unstackParentDependencies = ( dependencyFieldSlugs = [], allPodFieldsMap ) => {
			const parentDependencySlugs = [];

			if ( ! allPodFieldsMap ) {
				return dependencyFieldSlugs;
			}

			dependencyFieldSlugs.forEach( ( fieldSlug ) => {
				const parentField = allPodFieldsMap.get( fieldSlug );

				if ( ! parentField ) {
					return;
				}

				parentDependencySlugs.push(
					...Object.keys( parentField[ 'depends-on' ] || {} ),
					...Object.keys( parentField[ 'depends-on-any' ] || {} ),
					...Object.keys( parentField[ 'excludes-on' ] || {} ),
					...Object.keys( parentField[ 'wildcard-on' ] || {} ),
				);
			} );

			const nextLevelSlugs = parentDependencySlugs.length
				? unstackParentDependencies( parentDependencySlugs )
				: [];

			return uniq(
				[
					...dependencyFieldSlugs,
					...parentDependencySlugs,
					...nextLevelSlugs,
				]
			);
		};

		const unstackedDependencySlugs = unstackParentDependencies(
			allDependencyFieldSlugs,
			nextProps.allPodFieldsMap,
		);

		// If any of the field values that we have dependencies on have
		// changed, re-render. If not, try to avoid it.
		const haveAnyDependenciesChanged = unstackedDependencySlugs.some( ( slug ) => {
			return prevProps.allPodValues[ slug ] !== nextProps.allPodValues[ slug ];
		} );

		return ! haveAnyDependenciesChanged;
	}
);

export default MemoizedFieldWrapper;
