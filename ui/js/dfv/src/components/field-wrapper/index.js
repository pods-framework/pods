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
import isFieldRepeatable from 'dfv/src/helpers/isFieldRepeatable';
import useConditionalLogic from 'dfv/src/hooks/useConditionalLogic';
import useValidation from 'dfv/src/hooks/useValidation';
import useHideContainerDOM from 'dfv/src/components/field-wrapper/useHideContainerDOM';

import FIELD_MAP from 'dfv/src/fields/field-map';
import { FIELD_PROP_TYPE_SHAPE } from 'dfv/src/config/prop-types';

import './field-wrapper.scss';
import RepeatableFieldList from './repeatable-field-list';
import useBlockEditor from '../../hooks/useBlockEditor';

export const FieldWrapper = ( props ) => {
	const {
		storeKey,
		field = {},
		podName,
		podType,
		allPodFieldsMap,
		value,
		values,
		setOptionValue,
		allPodValues,
	} = props;

	const {
		description,
		help: helpText,
		label,
		required,
		fieldEmbed = false,
		name,
		type: fieldType,
		html_no_label: htmlNoLabel = false,
		htmlAttr: htmlAttributes,
	} = field;

	// Find the component for the field type
	const FieldComponent = FIELD_MAP[ fieldType ]?.fieldComponent;
	const isBooleanGroupField = 'boolean_group' === fieldType;

	const fieldRef = useRef( null );

	// Custom placeholder on the "Add Pod" screen.
	const processedFieldConfig = {
		...field,
		htmlAttr: { ...htmlAttributes || {} },
	};

	if ( 'create_name' === name ) {
		processedFieldConfig.htmlAttr.placeholder = sanitizeSlug( allPodValues.create_label_singular );
	}

	// Sort out different shapes that we could get the help text in.
	// It's possible to get an array of strings for the help text, but it
	// will usually be a string.
	const shouldShowHelpText = helpText && ( 'help' !== helpText );

	const helpTextString = Array.isArray( helpText ) ? helpText[ 0 ] : helpText;
	const helpLink = ( Array.isArray( helpText ) && !! helpText[ 1 ] )
		? helpText[ 1 ]
		: undefined;

	// Some fields show a label, and some don't.
	// Others get a description and others don't.
	const showLabel = (
		'heading' !== fieldType &&
		( 'html' !== fieldType || ! htmlNoLabel ) &&
		! fieldEmbed
	);

	const showDescription = !! description && ! fieldEmbed;

	const isRepeatable = isFieldRepeatable( field );

	// Only the Boolean Group fields need both allPodValues and
	// allPodFieldsMap, because the subfields need to reference these.
	// The "Bidirectional Field"/'sister_id" field in the Edit Pod screen
	// also needs allPodValues.
	const passAllPodValues = isBooleanGroupField || 'sister_id' === name;

	const passAllPodFieldsMap = isBooleanGroupField;

	// Make all values into an array, to make handling repeatable fields easier.
	const valuesArray = ( isRepeatable && Array.isArray( value ) )
		? value
		: [ value ];

	// State
	const [ hasBlurred, setHasBlurred ] = useState( false );

	const setValue = ( newValue ) => {
		setOptionValue( name, newValue );
	};

	// Maybe handle updating the value to the default option if empty.
	if (
		'pick' === field?.type
		&& (
			'single' === field?.pick_format_type
			|| 'undefined' === typeof field.pick_format_type
		)
		&& (
			'dropdown' === field?.pick_format_single
			|| 'undefined' === typeof field.pick_format_single
		)
		&& ! Boolean( parseInt( field?.pick_show_select_text ) )
		&& field?.required
	) {
		// Check if we have a field value.
		let fieldValue = undefined;
		let fieldVariation = undefined;

		let variations = [
			field.name,
			'pods_meta_' + field.name,
			'pods_field_' + field.name,
		];

		variations.every( variation => {
			// Stop the loop if we found the value we were looking for.
			if ( 'undefined' !== typeof allPodValues[ variation ] ) {
				fieldValue = allPodValues[ variation ];
				fieldVariation = variation;

				return false;
			}

			// Continue to the next variation.
			return true;
		} );

		if (
			'' === fieldValue
			|| (
				'undefined' !== typeof field?.data['']
				&& field?.data[''] === fieldValue
			)
		) {
			setValue( field?.default ?? '' );

			allPodValues[ fieldVariation ] = field?.default ?? '';
		}
	}

	// Calculate dependencies.
	const meetsConditionalLogic = useConditionalLogic(
		field,
		allPodValues,
		allPodFieldsMap,
	);

	// Use hook to hide the container element
	useHideContainerDOM( name, fieldRef, meetsConditionalLogic );

	// The only validator set up by default is to validate a required
	// field, but the field child component may set additional rules.
	const [ validationMessages, addValidationRules ] = useValidation(
		[
			{
				rule: requiredValidator( label, isRepeatable ),
				condition: () => true === toBool( required ),
			},
		],
		value
	);

	// Handle Block Editor save lock.
	const blockEditor = useBlockEditor();
	useEffect( () => {
		if ( ! meetsConditionalLogic || ! validationMessages.length ) {
			blockEditor.unlockPostSaving( `pods-field-${ name }` );
		} else {
			blockEditor.lockPostSaving( `pods-field-${ name }`, validationMessages, () => setHasBlurred( true ) );
		}

		// Unlock on unmount.
		return () => {
			blockEditor.unlockPostSaving( `pods-field-${ name }` );
		};
	}, [ validationMessages, meetsConditionalLogic ] );

	// Don't render a field that hasn't had its dependencies met.
	if ( ! meetsConditionalLogic ) {
		return <span ref={ fieldRef } />;
	}

	const labelComponent = showLabel ? (
		<FieldLabel
			label={ label }
			required={ toBool( required ) }
			htmlFor={ processedFieldConfig.htmlAttr?.id || `pods-form-ui-${ name }` }
			helpTextString={ shouldShowHelpText ? helpTextString : undefined }
			helpLink={ shouldShowHelpText ? helpLink : undefined }
		/>
	) : undefined;

	const descriptionComponent = showDescription ? (
		<FieldDescription description={ description } />
	) : undefined;

	const inputComponents = !! FieldComponent ? (
		<FieldErrorBoundary>
			<div className="pods-field-wrapper">
				{ ( () => {
					if ( true === isBooleanGroupField ) {
						return (
							<FieldComponent
								storeKey={ storeKey }
								values={ values }
								podName={ podName }
								podType={ podType }
								allPodValues={ passAllPodValues ? allPodValues : undefined }
								allPodFieldsMap={ passAllPodFieldsMap ? allPodFieldsMap : undefined }
								setOptionValue={ setOptionValue }
								isValid={ ! validationMessages.length }
								addValidationRules={ addValidationRules }
								setHasBlurred={ () => setHasBlurred( true ) }
								fieldConfig={ field }
							/>
						);
					}

					if ( true === isRepeatable ) {
						return (
							<RepeatableFieldList
								storeKey={ storeKey }
								fieldConfig={ processedFieldConfig }
								valuesArray={ valuesArray }
								FieldComponent={ FieldComponent }
								podType={ podType }
								podName={ podName }
								allPodValues={ passAllPodValues ? allPodValues : undefined }
								allPodFieldsMap={ passAllPodFieldsMap ? allPodFieldsMap : undefined }
								setFullValue={ setValue }
								setHasBlurred={ () => setHasBlurred( true ) }
							/>
						);
					}

					return (
						<FieldComponent
							storeKey={ storeKey }
							value={ value }
							podName={ podName }
							podType={ podType }
							allPodValues={ allPodValues }
							allPodFieldsMap={ allPodFieldsMap }
							setValue={ setValue }
							isValid={ ! validationMessages.length }
							addValidationRules={ addValidationRules }
							setHasBlurred={ () => setHasBlurred( true ) }
							fieldConfig={ processedFieldConfig }
						/>
					);
				} )() }
			</div>
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

	return (
		<>
			<span ref={ fieldRef } />
			<DivFieldLayout
				labelComponent={ labelComponent }
				descriptionComponent={ descriptionComponent }
				inputComponent={ inputComponents }
				validationMessagesComponent={ validationMessagesComponent }
				fieldType={ fieldType }
			/>
		</>
	);
};

FieldWrapper.propTypes = {
	/**
	 * Redux store key.
	 */
	storeKey: PropTypes.string.isRequired,

	/**
	 * Field config.
	 */
	field: FIELD_PROP_TYPE_SHAPE,

	/**
	 * Pod type being edited.
	 */
	podType: PropTypes.string,

	/**
	 * Pod slug being edited.
	 */
	podName: PropTypes.string,

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

		// If Pod information has changed, re-render
		if (
			prevProps.podName !== nextProps.podName ||
			prevProps.podType !== nextProps.podType
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
		const allDependencyFieldSlugs = ( nextProps.field?.conditional_logic?.rules || [] ).map(
			( rule ) => rule.field
		);

		// If it's a boolean group, there are also subfields to check.
		if ( 'boolean_group' === nextProps.field?.type ) {
			const subfields = nextProps.field?.boolean_group;

			subfields.forEach( ( subfield ) => {
				allDependencyFieldSlugs.push(
					...( ( subfield?.conditional_logic?.rules || [] ).map(
						( rule ) => rule.field
					) )
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
					...( ( parentField?.conditional_logic?.rules || [] ).map(
						( rule ) => rule.field
					) )
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
