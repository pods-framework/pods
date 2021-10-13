/**
 * External dependencies
 */
import React from 'react';
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
import { __ } from '@wordpress/i18n';
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
import SubfieldWrapper from 'dfv/src/components/field-wrapper/subfield-wrapper';

/**
 * Other Pods dependencies
 */
import { FIELD_PROP_TYPE_SHAPE } from 'dfv/src/config/prop-types';

const RepeatableFieldList = ( {
	fieldConfig,
	valuesArray,
	FieldComponent,
	podType,
	podName,
	allPodValues,
	allPodFieldsMap,
	validationMessages,
	addValidationRules,
	setFullValue,
	setHasBlurred,
} ) => {
	// Helper functions for setting, moving, adding, and deleting the value
	// or subvalues.
	const createSetValueAtIndex = ( index ) => ( newValue ) => {
		const newValues = [ ...valuesArray ];
		newValues[ index ] = newValue;

		setFullValue( newValues );
	};

	const deleteValueAtIndex = ( index ) => {
		const newValues = [
			...( valuesArray || [] ).slice( 0, index ),
			...( valuesArray || [] ).slice( index + 1 ),
		];

		setFullValue( newValues );
	};

	const addValue = () => {
		const newValues = [
			...valuesArray,
			// @todo does an empty string always work?
			'',
		];

		setFullValue( newValues );
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

		setFullValue( newValues );
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

		setFullValue(
			arrayMove( valuesArray, oldIndex, newIndex )
		);

		setHasBlurred( true );
	};

	return (
		<div className="pods-field-wrapper__repeatable-fields">
			<div className="pods-field-wrapper__repeatable-field-table">
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
									fieldConfig={ fieldConfig }
									FieldComponent={ FieldComponent }
									isRepeatable={ true }
									index={ index }
									value={ valueItem }
									podType={ podType }
									podName={ podName }
									allPodValues={ allPodValues }
									allPodFieldsMap={ allPodFieldsMap }
									validationMessages={ validationMessages }
									addValidationRules={ addValidationRules }
									setValue={ createSetValueAtIndex( index ) }
									setHasBlurred={ setHasBlurred }
									isDraggable={ valuesArray.length > 1 }
									endControls={ ( valuesArray.length > 1 ) ? (
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
									key={ `${ fieldConfig.name }-${ index }` }
								/>
							);
						} ) }
					</SortableContext>
				</DndContext>
			</div>

			<Button
				onClick={ addValue }
				isSecondary
				className="pods-field-wrapper__add-button"
			>
				{ __( 'Add', 'pods' ) }
			</Button>
		</div>
	);
};

RepeatableFieldList.propTypes = {
	/**
	 * Field config.
	 */
	fieldConfig: FIELD_PROP_TYPE_SHAPE.isRequired,

	/**
	 * Array of subfield values.
	 */
	valuesArray: PropTypes.arrayOf(
		PropTypes.oneOfType(
			[
				PropTypes.string,
				PropTypes.bool,
				PropTypes.number,
				PropTypes.array,
			]
		)
	),

	/**
	 * Pod type being edited.
	 */
	podType: PropTypes.string,

	/**
	 * Pod slug being edited.
	 */
	podName: PropTypes.string,

	/**
	 * All values for the Pod (not needed on most field types) (optional).
	 */
	allPodValues: PropTypes.object,

	/**
	 * All fields from the Pod, including ones that belong to other groups. This
	 * should be a Map object, keyed by the field name, to make lookup easier (optional).
	 */
	allPodFieldsMap: PropTypes.object,

	/**
	 * Component to render.
	 */
	FieldComponent: PropTypes.elementType.isRequired,

	/**
	 * Array of validation messages.
	 */
	validationMessages: PropTypes.arrayOf( PropTypes.string ).isRequired,

	/**
	 * Callback to add additional validation rules.
	 */
	addValidationRules: PropTypes.func.isRequired,

	/**
	 * Function to update the field's full value on change.
	 */
	setFullValue: PropTypes.func.isRequired,

	/**
	 * Function to call when a field has blurred.
	 */
	setHasBlurred: PropTypes.func.isRequired,
};

export default RepeatableFieldList;
