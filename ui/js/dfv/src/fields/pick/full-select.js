/**
 * External dependencies
 */
import React from 'react';
import Select, { components } from 'react-select';
import AsyncSelect from 'react-select/async';
import AsyncCreatableSelect from 'react-select/async-creatable';
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
	restrictToHorizontalAxis,
} from '@dnd-kit/modifiers';
import {
	arrayMove,
	SortableContext,
	sortableKeyboardCoordinates,
	horizontalListSortingStrategy,
	useSortable,
} from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import PropTypes from 'prop-types';

/**
 * Other Pods dependencies
 */
import loadAjaxOptions from '../../helpers/loadAjaxOptions';

import { AJAX_DATA } from 'dfv/src/config/prop-types';

const SortableMultiValue = ( props ) => {
	const {
		attributes,
		listeners,
		setNodeRef,
		transform,
		transition,
		isDragging,
	} = useSortable( { id: props.data.value } );

	const style = {
		transform: CSS.Translate.toString( transform ),
		transition,
		cursor: isDragging ? 'grabbing' : 'grab',
	};

	const removeProps = {
		...props.removeProps,
		style: {
			cursor: 'pointer',
		},
	};

	return (
		<span
			ref={ setNodeRef }
			style={ style }
			aria-label="drag"
			// eslint-disable-next-line react/jsx-props-no-spreading
			{ ...listeners }
			// eslint-disable-next-line react/jsx-props-no-spreading
			{ ...attributes }
		>
			<components.MultiValue
				{ ...props }
				removeProps={ removeProps }
			/>
		</span>
	);
};

const FullSelect = ( {
	isTaggable,
	ajaxData,
	shouldRenderValue,
	formattedOptions,
	value,
	addNewItem,
	setValue,
	placeholder,
	isMulti,
	isClearable,
	isReadOnly,
} ) => {
	const useAsyncSelectComponent = isTaggable || ajaxData?.ajax;
	const AsyncSelectComponent = isTaggable ? AsyncCreatableSelect : AsyncSelect;

	const sensors = useSensors(
		useSensor( PointerSensor, {
			activationConstraint: {
				distance: 1,
			},
		} ),
		useSensor( KeyboardSensor, {
			coordinateGetter: sortableKeyboardCoordinates,
		} ),
	);

	const handleDragEnd = ( event ) => {
		const { active, over } = event;

		// Skip if not a multi-select field, or if the value isn't an array.
		if ( ! isMulti || ! Array.isArray( value ) ) {
			return;
		}

		if ( ! over?.id || active.id === over.id ) {
			return;
		}

		const oldIndex = value.findIndex(
			( item ) => ( item.value === active.id ),
		);

		const newIndex = value.findIndex(
			( item ) => ( item.value === over.id ),
		);

		const reorderedItems = arrayMove( value, oldIndex, newIndex );

		setValue( reorderedItems.map(
			( item ) => item.value )
		);
	};

	const selectStyles = {
		multiValueLabel: (provided, state) => ({
			...provided,
			wordBreak: 'break-word',
			whiteSpace: 'break-spaces',
		}),
		singleValue: (provided, state) => ({
			...provided,
			wordBreak: 'break-word',
			whiteSpace: 'break-spaces',
		}),
		menu: (provided, state) => ({
			...provided,
			zIndex: 2,
		}),
	};

	return (
		<DndContext
			sensors={ sensors }
			collisionDetection={ closestCenter }
			onDragEnd={ handleDragEnd }
			modifiers={ [
				restrictToParentElement,
				restrictToHorizontalAxis,
			] }
		>
			<SortableContext
				items={ Array.isArray( value ) ? value.map( ( item ) => item.value ) : [] }
				strategy={ horizontalListSortingStrategy }
			>
				{ useAsyncSelectComponent ? (
					<AsyncSelectComponent
						controlShouldRenderValue={ shouldRenderValue }
						defaultOptions={ formattedOptions }
						loadOptions={ ajaxData?.ajax ? loadAjaxOptions( ajaxData ) : undefined }
						value={ value }
						placeholder={ placeholder }
						isMulti={ isMulti }
						isClearable={ isClearable }
						onChange={ addNewItem }
						isDisabled={ isReadOnly }
						components={ {
							MultiValue: SortableMultiValue,
						} }
						styles={ selectStyles }
						classNamePrefix="pods-dfv-pick-full-select"
					/>
				) : (
					<Select
						controlShouldRenderValue={ shouldRenderValue }
						options={ formattedOptions }
						value={ value }
						placeholder={ placeholder }
						isMulti={ isMulti }
						isClearable={ isClearable }
						onChange={ addNewItem }
						isDisabled={ isReadOnly }
						components={ {
							MultiValue: SortableMultiValue,
						} }
						styles={ selectStyles }
						classNamePrefix="pods-dfv-pick-full-select"
					/>
				) }
			</SortableContext>
		</DndContext>
	);
};

const REACT_SELECT_VALUE_PROP_TYPE = PropTypes.shape( {
	label: PropTypes.string,
	value: PropTypes.string,
} );

FullSelect.propTypes = {
	/**
	 * True if new values can be created.
	 */
	isTaggable: PropTypes.bool.isRequired,

	/**
	 * Helper data for loading ajax results.
	 */
	ajaxData: AJAX_DATA,

	/**
	 * True if the value should be rendered (will be false for List Select fields).
	 */
	shouldRenderValue: PropTypes.bool.isRequired,

	/**
	 * Formatted options for the react-select component.
	 */
	formattedOptions: PropTypes.arrayOf( REACT_SELECT_VALUE_PROP_TYPE ),

	/**
	 * Value, either an array for multi or single value.
	 */
	value: PropTypes.oneOfType( [
		REACT_SELECT_VALUE_PROP_TYPE,
		PropTypes.arrayOf( REACT_SELECT_VALUE_PROP_TYPE ),
	] ),

	/**
	 * Callback to add an item to the value.
	 */
	addNewItem: PropTypes.func.isRequired,

	/**
	 * Callback to update the whole value.
	 */
	setValue: PropTypes.func.isRequired,

	/**
	 * Placeholder text.
	 */
	placeholder: PropTypes.string.isRequired,

	/**
	 * True if multiple values are allowed.
	 */
	isMulti: PropTypes.bool.isRequired,

	/**
	 * True if the field can be cleared.
	 */
	isClearable: PropTypes.bool.isRequired,

	/**
	 * True if the field should be read-only.
	 */
	isReadOnly: PropTypes.bool.isRequired,
};

export default FullSelect;
