/**
 * External dependencies
 */
import React, { useEffect, useRef } from 'react';
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
import sanitizeSlug from 'dfv/src/helpers/sanitizeSlug';
import validateFieldDependencies from 'dfv/src/helpers/validateFieldDependencies';
import useValidation from 'dfv/src/hooks/useValidation';
import useBidirectionalFieldData from 'dfv/src/hooks/useBidirectionalFieldData';

import FIELD_MAP from 'dfv/src/fields/field-map';
import { FIELD_PROP_TYPE_SHAPE } from 'dfv/src/config/prop-types';

// The dependencies could be stacked, so if Field B is not shown because
// Field A is not set correctly, then Field C which depends on a specific
// Field B value should fail, even if Field B had the correct matching value.
// To find these, look up each key in the dependsOn (and similar) maps, find
// the relevant field, and add its dependsOn (or similar) values to the full map.
const unstackDependencies = (
	dependencyMap = {},
	allFieldsMap = new Map(),
	dependencyKey = 'depends-on'
) => {
	if ( ! dependencyMap || 0 === Object.keys( dependencyMap ).length ) {
		return {};
	}

	return Object.entries( dependencyMap ).reduce(
		( accumulator, dependencyEntry ) => {
			const fieldName = dependencyEntry[ 0 ];

			// Look up the field config by that key, and add its dependencies
			// recursively, unless they've already been added.
			const dependencyField = allFieldsMap.get( fieldName );

			if ( ! dependencyField?.[ dependencyKey ] ) {
				return accumulator;
			}

			// Call recursively to continue looking for dependencies.
			const nextLevelDependencies = unstackDependencies(
				dependencyField[ dependencyKey ],
				allFieldsMap,
				dependencyKey
			);

			return {
				...accumulator,
				...nextLevelDependencies,
			};
		},
		{
			...dependencyMap,
		}
	);
};

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
		'depends-on': dependsOn,
		'depends-on-any': dependsOnAny,
		'excludes-on': excludesOn,
		'wildcard-on': wildcardOn,
	} = field;

	const isBooleanGroupField = 'boolean_group' === fieldType;

	const fieldRef = useRef( null );

	const dataOptions = useBidirectionalFieldData( data );

	// Find the component for the field type
	const FieldComponent = FIELD_MAP[ fieldType ]?.fieldComponent;

	// Calculate dependencies, trying to skip as many of these checks as
	// we can because they're expensive.
	let meetsDependencies = true;

	if ( dependsOn && Object.keys( dependsOn ).length ) {
		const unstackedDependsOn = unstackDependencies( dependsOn, allPodFieldsMap, 'depends-on' );

		if ( ! validateFieldDependencies( allPodValues, unstackedDependsOn, 'depends-on' ) ) {
			meetsDependencies = false;
		}
	} else if ( dependsOnAny && Object.keys( dependsOnAny ).length ) {
		const unstackedDependsOnAny = unstackDependencies( dependsOn, allPodFieldsMap, 'depends-on-any' );

		if ( ! validateFieldDependencies( allPodValues, unstackedDependsOnAny, 'depends-on-any' ) ) {
			meetsDependencies = false;
		}
	} else if ( excludesOn && Object.keys( excludesOn ).length ) {
		const unstackedExcludesOn = unstackDependencies( excludesOn, allPodFieldsMap, 'excludes-on' );

		if ( ! validateFieldDependencies( allPodValues, unstackedExcludesOn, 'excludes' ) ) {
			meetsDependencies = false;
		}
	} else if ( wildcardOn ) {
		const unstackedWildcardOn = unstackDependencies( wildcardOn, allPodFieldsMap, 'wildcard-on' );

		if ( ! validateFieldDependencies( allPodValues, unstackedWildcardOn, 'wildcard' ) ) {
			meetsDependencies = false;
		}
	}

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
				setOptionValue={ isBooleanGroupField ? setOptionValue : undefined }
				setValue={ isBooleanGroupField ? undefined : ( newValue ) => setOptionValue( name, newValue ) }
				isValid={ !! validationMessages.length }
				addValidationRules={ addValidationRules }
				htmlAttr={ processedHtmlAttr }
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
	 * Field values (for boolean_group).
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
			// This is a shallow compare - we may want to use _.isEqual eventually.
			prevProps.values !== nextProps.values
		) {
			return false;
		}

		// If there are no dependencies, skip the expensive dependency checks.
		const dependsOn = nextProps.field[ 'depends-on' ];
		const dependsOnAny = nextProps.field[ 'depends-on-any' ];
		const excludesOn = nextProps.field[ 'excludes-on' ];
		const wildcardOn = nextProps.field[ 'wildcard-on' ];

		if (
			( ! dependsOn || 0 === Object.keys( dependsOn ).length ) &&
			( ! dependsOnAny || 0 === Object.keys( dependsOnAny ).length ) &&
			( ! excludesOn || 0 === Object.keys( excludesOn ).length ) &&
			( ! wildcardOn || 0 === Object.keys( wildcardOn ).length )
		) {
			return true;
		}

		// If any of the field values that we have dependencies on have changed, re-render.
		// If not, try to avoid it.
		const unstackedDependsOn = unstackDependencies(
			dependsOn,
			nextProps.allPodFieldsMap,
			'depends-on'
		);

		const unstackedDependsOnAny = unstackDependencies(
			dependsOnAny,
			nextProps.allPodFieldsMap,
			'depends-on-any'
		);

		const unstackedExcludesOn = unstackDependencies(
			excludesOn,
			nextProps.allPodFieldsMap,
			'excludes-on'
		);

		const unstackedWildcardOn = unstackDependencies(
			wildcardOn,
			nextProps.allPodFieldsMap,
			'wildcard-on'
		);

		const allFieldSlugsWithDependencies = [
			...Object.keys( unstackedDependsOn ),
			...Object.keys( unstackedDependsOnAny ),
			...Object.keys( unstackedExcludesOn ),
			...Object.keys( unstackedWildcardOn ),
		];

		const haveAnyDependenciesChanged = allFieldSlugsWithDependencies.some( ( slug ) => {
			return prevProps.allPodValues[ slug ] !== nextProps.allPodValues[ slug ];
		} );

		return ! haveAnyDependenciesChanged;
	}
);

export default MemoizedFieldWrapper;
