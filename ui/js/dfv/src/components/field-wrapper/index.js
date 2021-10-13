/**
 * External dependencies
 */
import React, { useRef, useState } from 'react';
import { isEqual, uniq } from 'lodash';
import PropTypes from 'prop-types';
import {
	DndContext,
	closestCenter,
	KeyboardSensor,
	PointerSensor,
	useSensor,
	useSensors,
} from '@dnd-kit/core';
import {
	restrictToParentElement,
	restrictToVerticalAxis,
} from '@dnd-kit/modifiers';
import {
	arrayMove,
	SortableContext,
	sortableKeyboardCoordinates,
	verticalListSortingStrategy,
} from '@dnd-kit/sortable';

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import {
	Button,
	ToolbarGroup,
	ToolbarButton,
} from '@wordpress/components';
import {
	chevronUp,
	chevronDown,
	close,
} from '@wordpress/icons';

/**
 * Pods components
 */
import FieldErrorBoundary from 'dfv/src/components/field-wrapper/field-error-boundary';
import DivFieldLayout from 'dfv/src/components/field-wrapper/div-field-layout';
import SubfieldWrapper from 'dfv/src/components/field-wrapper/subfield-wrapper';

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
import useDependencyCheck from 'dfv/src/hooks/useDependencyCheck';
import useValidation from 'dfv/src/hooks/useValidation';
import useHideContainerDOM from 'dfv/src/components/field-wrapper/useHideContainerDOM';

import FIELD_MAP from 'dfv/src/fields/field-map';
import { FIELD_PROP_TYPE_SHAPE } from 'dfv/src/config/prop-types';

import './field-wrapper.scss';

export const FieldWrapper = ( props ) => {
	const {
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

	// Helper functions for setting, moving, adding, and deleting the value
	// or subvalues.
	const setSingleValue = ( newValue ) => setOptionValue( name, newValue );

	const setRepeatableValue = ( index ) => ( newValue ) => {
		const newValues = [ ...valuesArray ];
		newValues[ index ] = newValue;

		setOptionValue( name, newValues );
	};

	const deleteValueAtIndex = ( index ) => {
		// @todo confirmation

		const newValues = [
			...( valuesArray || [] ).slice( 0, index ),
			...( valuesArray || [] ).slice( index + 1 ),
		];

		setOptionValue( name, newValues );
	};

	const addValue = () => {
		const newValues = [
			...valuesArray,
			// @todo does an empty string always work?
			'',
		];

		setOptionValue( name, newValues );
	};

	const swapValues = ( firstIndex, secondIndex ) => {
		if (
			typeof valuesArray?.[ firstIndex ] === 'undefined' ||
			typeof valuesArray?.[ secondIndex ] === 'undefined'
		) {
			return;
		}

		const newValues = [ ...valuesArray ];
		const tempValue = newValues[ secondIndex ];

		newValues[ secondIndex ] = newValues[ firstIndex ];
		newValues[ firstIndex ] = tempValue;

		setOptionValue( name, newValues );
	};

	// Set up drag-and-drop
	const sensors = useSensors(
		useSensor( PointerSensor ),
		useSensor( KeyboardSensor, {
			coordinateGetter: sortableKeyboardCoordinates,
		} ),
	);

	const handleDragEnd = ( event ) => {
		const { active, over } = event;

		if ( ! over?.id || active.id === over.id ) {
			return;
		}

		const oldIndex = parseInt( active.id, 10 );
		const newIndex = parseInt( over.id, 10 );

		setOptionValue(
			name,
			arrayMove( valuesArray, oldIndex, newIndex )
		);

		setHasBlurred( true );
	};

	// Calculate dependencies.
	const meetsDependencies = useDependencyCheck(
		field,
		allPodValues,
		allPodFieldsMap,
	);

	// Use hook to hide the container element
	useHideContainerDOM( name, fieldRef, meetsDependencies );

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

	// Don't render a field that hasn't had its dependencies met.
	if ( ! meetsDependencies ) {
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
				{ isBooleanGroupField ? (
					<div className="pods-field-wrapper__item">
						<FieldComponent
							values={ values }
							podName={ podName }
							podType={ podType }
							allPodValues={ passAllPodValues ? allPodValues : undefined }
							allPodFieldsMap={ passAllPodFieldsMap ? allPodFieldsMap : undefined }
							setOptionValue={ setOptionValue }
							isValid={ !! validationMessages.length }
							addValidationRules={ addValidationRules }
							setHasBlurred={ () => setHasBlurred( true ) }
							fieldConfig={ field }
						/>
					</div>
				) : (
					<DndContext
						sensors={ sensors }
						collisionDetection={ closestCenter }
						onDragEnd={ handleDragEnd }
						modifiers={ [
							restrictToParentElement,
							restrictToVerticalAxis,
						] }
					>
						<SortableContext
							items={ valuesArray.map( ( valueItem, index ) => index.toString() ) }
							strategy={ verticalListSortingStrategy }
						>
							{ valuesArray.map( ( valueItem, index ) => {
								return (
									<SubfieldWrapper
										fieldConfig={ processedFieldConfig }
										FieldComponent={ FieldComponent }
										isRepeatable={ isRepeatable }
										index={ index }
										value={ valueItem }
										podType={ podType }
										podName={ podName }
										allPodValues={ passAllPodValues ? allPodValues : undefined }
										allPodFieldsMap={ passAllPodFieldsMap ? allPodFieldsMap : undefined }
										validationMessages={ validationMessages }
										addValidationRules={ addValidationRules }
										setValue={ isRepeatable ? setRepeatableValue( index ) : setSingleValue }
										setHasBlurred={ setHasBlurred }
										isDraggable={ ( isRepeatable && valuesArray.length > 1 ) }
										endControls={ ( isRepeatable && valuesArray.length > 1 ) ? (
											<>
												<ToolbarGroup className="pods-field-wrapper__movers">
													<ToolbarButton
														disabled={ index === 0 }
														onClick={ () => swapValues( index, index - 1 ) }
														icon={ chevronUp }
														label={ __( 'Move up', 'pods' ) }
														showTooltip
														className="pods-field-wrapper__mover"
													/>

													<ToolbarButton
														disabled={ index === ( valuesArray.length - 1 ) }
														onClick={ () => swapValues( index, index + 1 ) }
														icon={ chevronDown }
														label={ __( 'Move down', 'pods' ) }
														showTooltip
														className="pods-field-wrapper__mover"
													/>
												</ToolbarGroup>

												<ToolbarButton
													onClick={ ( event ) => {
														event.stopPropagation();

														// eslint-disable-next-line no-alert
														const confirmation = confirm(
															// eslint-disable-next-line @wordpress/i18n-no-collapsible-whitespace
															__( 'Are you sure you want to delete this value?', 'pods' )
														);

														if ( confirmation ) {
															deleteValueAtIndex( index );
														}
													} }
													icon={ close }
													label={ __( 'Delete', 'pods' ) }
													showTooltip
												/>
											</>
										) : null }
										key={ `${ field.name }-${ index }` }
									/>
								);
							} ) }
						</SortableContext>
					</DndContext>
				) }

				{ isRepeatable ? (
					<Button
						onClick={ addValue }
						isSecondary
					>
						Add
					</Button>
				) : null }
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
