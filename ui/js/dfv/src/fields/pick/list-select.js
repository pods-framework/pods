/**
 * External dependencies
 */
import React from 'react';
import Select from 'react-select';
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
	useSortable,
} from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Other Pods dependencies
 */
import { PICK_OPTIONS } from 'dfv/src/config/prop-types';

const ListSelectItem = ( {
	itemName,
	itemId,
	value,
	removeItem,
	isDraggable,
	isRemovable,
	isViewable,
	isEditable,
} ) => {
	const {
		attributes,
		listeners,
		setNodeRef,
		transform,
		transition,
	} = useSortable( { id: value.value } );

	const style = {
		transform: CSS.Transform.toString( transform ),
		transition: transition || undefined,
	};

	return (
		<li
			className="pods-dfv-list-item pods-relationship"
			ref={ setNodeRef }
			style={ style }
		>
			<input
				name={ itemName }
				id={ itemId }
				type="hidden"
				value={ value.value }
			/>

			<ul className="pods-dfv-list-meta relationship-item">
				{ isDraggable && (
					<li
						className="pods-dfv-list-col pods-dfv-list-handle"
						// eslint-disable-next-line react/jsx-props-no-spreading
						{ ...listeners }
						// eslint-disable-next-line react/jsx-props-no-spreading
						{ ...attributes }
					>
						<span>{ __( 'Reorder', 'pods' ) }</span>
					</li>
				) }

				{ /*
				@todo icon logic, see:
				https://github.com/pods-framework/pods/blob/main/ui/js/pods-dfv/_src/pick/views/list-item.html#L16-L32
				*/ }

				<li className="pods-dfv-list-col pods-dfv-list-name">
					{ value.label }
				</li>

				{ isRemovable && (
					<li className="pods-dfv-list-col pods-dfv-list-remove">
						<a
							href="#remove"
							title={ __( 'Deselect', 'pods' ) }
							onClick={ removeItem }
						>
							{ __( 'Deselect', 'pods' ) }
						</a>
					</li>
				) }

				{ isViewable && (
					<li className="pods-dfv-list-col pods-dfv-list-link">
						{ /* eslint-disable-next-line jsx-a11y/anchor-is-valid */ }
						<a
							href="#" // @todo
							title={ __( 'View', 'pods' ) }
							target="_blank"
						>
							{ __( 'View', 'pods' ) }
						</a>
					</li>
				) }

				{ isEditable && (
					<li className="pods-dfv-list-col pods-dfv-list-edit">
						{ /* eslint-disable-next-line jsx-a11y/anchor-is-valid */ }
						<a
							href="#" // @todo
							title={ __( 'Edit', 'pods' ) }
							target="_blank"
						>
							{ __( 'Edit', 'pods' ) }
						</a>
					</li>
				) }
			</ul>
		</li>
	);
};

ListSelectItem.propTypes = {
	itemName: PropTypes.string.isRequired,
	itemId: PropTypes.string.isRequired,
	value: PropTypes.shape( {
		label: PropTypes.string.isRequired,
		value: PropTypes.string.isRequired,
	} ),
	removeItem: PropTypes.func.isRequired,
	isDraggable: PropTypes.bool.isRequired,
	isRemovable: PropTypes.bool.isRequired,
};

const ListSelect = ( {
	htmlAttributes,
	name,
	value,
	options,
	setValue,
	placeholder,
	isMulti,
	limit,
	// showIcon,
	showViewLink,
	showEditLink,
	readOnly = false,
} ) => {
	// Always have an array of values for the list, even if
	// we were just passed a single object.
	let arrayOfValues = [];

	if ( value ) {
		arrayOfValues = isMulti ? value : [ value ];
	}

	const sensors = useSensors(
		useSensor( PointerSensor ),
		useSensor( KeyboardSensor, {
			coordinateGetter: sortableKeyboardCoordinates,
		} ),
	);

	const removeValueAtIndex = ( index = 0 ) => {
		if ( isMulti ) {
			setValue(
				[
					...arrayOfValues.slice( 0, index ),
					...arrayOfValues.slice( index + 1 ),
				].map( ( item ) => item.value )
			);
		} else {
			setValue( undefined );
		}
	};

	const handleDragEnd = ( event ) => {
		const { active, over } = event;

		if ( ! over?.id || active.id === over.id ) {
			return;
		}

		const oldIndex = arrayOfValues.findIndex(
			( item ) => ( item.value === active.id ),
		);

		const newIndex = arrayOfValues.findIndex(
			( item ) => ( item.value === over.id ),
		);

		const updatedItems = arrayMove( arrayOfValues, oldIndex, newIndex );

		// We can assume `isMulti` is true when it's a drag-and-drop event.
		setValue( updatedItems.map( ( selection ) => selection.value ) );
	};

	return (
		<>
			{ ! readOnly && (
				<div className="pods-ui-list-autocomplete">
					<Select
						name={ htmlAttributes.name || name }
						options={ options }
						value={ value }
						placeholder={ placeholder }
						isMulti={ isMulti }
						controlShouldRenderValue={ false }
						onChange={ ( newOption ) => {
							if ( isMulti ) {
								setValue( newOption.map( ( selection ) => selection.value ) );
							} else {
								setValue( newOption.value );
							}
						} }
					/>
				</div>
			) }

			<div className="pods-pick-values">
				{ !! arrayOfValues.length && (
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
							items={ arrayOfValues.map( ( item ) => item.value ) }
							strategy={ verticalListSortingStrategy }
						>
							<ul className="pods-dfv-list pods-relationship">
								{ arrayOfValues.map( ( valueItem, index ) => {
									const itemName = isMulti ? `name[${ index }]` : name;
									const itemId = isMulti ? `name[${ index }]` : name;

									return (
										<ListSelectItem
											key={ `${ name }-${ index }` }
											itemName={ itemName }
											itemId={ itemId }
											value={ valueItem }
											removeItem={ () => removeValueAtIndex( index ) }
											isDraggable={ ! readOnly && ( 1 !== limit ) }
											isRemovable={ ! readOnly }
											isViewable={ showViewLink }
											isEditable={ ! readOnly && showEditLink }
										/>
									);
								} ) }
							</ul>
						</SortableContext>
					</DndContext>
				) }
			</div>
		</>
	);
};

ListSelect.propTypes = {
	htmlAttributes: PropTypes.shape( {
		id: PropTypes.string,
		class: PropTypes.string,
		name: PropTypes.string,
	} ),
	name: PropTypes.string.isRequired,
	value: PropTypes.oneOfType( [
		PropTypes.shape( {
			label: PropTypes.string.isRequired,
			value: PropTypes.string.isRequired,
		} ),
		PropTypes.arrayOf(
			PropTypes.shape( {
				label: PropTypes.string.isRequired,
				value: PropTypes.string.isRequired,
			} )
		),
	] ),
	setValue: PropTypes.func.isRequired,
	options: PICK_OPTIONS.isRequired,
	placeholder: PropTypes.string.isRequired,
	isMulti: PropTypes.bool.isRequired,
	limit: PropTypes.number.isRequired,
	showIcon: PropTypes.bool.isRequired,
	showViewLink: PropTypes.bool.isRequired,
	showEditLink: PropTypes.bool.isRequired,
	readOnly: PropTypes.bool,
};

export default ListSelect;
