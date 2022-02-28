/**
 * External dependencies
 */
import React from 'react';
import {
	DndContext,
	DragOverlay,
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
import PropTypes from 'prop-types';

/**
 * Other Pods dependencies
 */
import DraggableListSelectItem from './draggable-list-select-item';

import './list-select.scss';

const ListSelectValues = ( {
	fieldName,
	value: arrayOfValues,
	fieldItemData,
	setFieldItemData,
	setValue,
	isMulti,
	limit,
	defaultIcon,
	showIcon,
	showViewLink,
	showEditLink,
	editIframeTitle,
	readOnly = false,
} ) => {
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

	const swapItems = ( oldIndex, newIndex ) => {
		if ( ! isMulti ) {
			throw 'Swap items shouldn\'nt be called on a single ListSelect';
		}

		const newValues = [ ...arrayOfValues ];
		const tempValue = newValues[ newIndex ];

		newValues[ newIndex ] = newValues[ oldIndex ];
		newValues[ oldIndex ] = tempValue;

		setValue(
			newValues.map( ( item ) => item.value ),
		);
	};

	const sensors = useSensors(
		useSensor( PointerSensor ),
		useSensor( KeyboardSensor, {
			coordinateGetter: sortableKeyboardCoordinates,
		} ),
	);

	const handleDragEnd = ( event ) => {
		const { active, over } = event;

		// Skip if not a multi-select field.
		if ( ! isMulti ) {
			return;
		}

		if ( ! over?.id || active.id === over.id ) {
			return;
		}

		const oldIndex = arrayOfValues.findIndex(
			( item ) => ( item.value === active.id ),
		);

		const newIndex = arrayOfValues.findIndex(
			( item ) => ( item.value === over.id ),
		);

		const reorderedItems = arrayMove( arrayOfValues, oldIndex, newIndex );

		setValue( reorderedItems.map(
			( item ) => item.value )
		);
	};

	return (
		<div className="pods-pick-values">
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
					items={ arrayOfValues.map( ( item ) => item.value.toString() ) }
					strategy={ verticalListSortingStrategy }
				>
					{ !! arrayOfValues.length && (
						<ul className="pods-dfv-list pods-relationship">
							{ arrayOfValues.map( ( valueItem, index ) => {
								// There may be additional data in an object from the fieldItemData
								// array.
								const moreData = fieldItemData.find(
									( item ) => item?.id === valueItem.value
								);

								const icon = showIcon ? ( moreData?.icon || defaultIcon ) : undefined;

								// May need to change the label, if it differs from the provided value.
								const displayValue = valueItem;

								const matchingFieldItemData = fieldItemData.find(
									( item ) => Number( item.id ) === Number( valueItem.value )
								);

								if ( matchingFieldItemData && matchingFieldItemData.name ) {
									displayValue.label = matchingFieldItemData.name;
								}

								return (
									<DraggableListSelectItem
										key={ `${ fieldName }-${ index }` }
										fieldName={ fieldName }
										value={ displayValue }
										isDraggable={ ! readOnly && ( 1 !== limit ) }
										isRemovable={ ! readOnly }
										editLink={ ! readOnly && showEditLink ? moreData?.edit_link : undefined }
										viewLink={ showViewLink ? moreData?.link : undefined }
										editIframeTitle={ editIframeTitle }
										icon={ icon }
										removeItem={ () => removeValueAtIndex( index ) }
										setFieldItemData={ setFieldItemData }
										moveUp={
											( ! readOnly && index !== 0 )
												? () => swapItems( index, index - 1 )
												: undefined
										}
										moveDown={
											( ! readOnly && index !== ( arrayOfValues.length - 1 ) )
												? () => swapItems( index, index + 1 )
												: undefined
										}
									/>
								);
							} ) }
						</ul>
					) }
				</SortableContext>
			</DndContext>
		</div>
	);
};

ListSelectValues.propTypes = {
	fieldName: PropTypes.string.isRequired,
	value: PropTypes.arrayOf(
		PropTypes.shape( {
			label: PropTypes.string.isRequired,
			value: PropTypes.string.isRequired,
		} )
	),
	setValue: PropTypes.func.isRequired,
	fieldItemData: PropTypes.arrayOf(
		PropTypes.any,
	),
	setFieldItemData: PropTypes.func.isRequired,
	isMulti: PropTypes.bool.isRequired,
	limit: PropTypes.number.isRequired,
	defaultIcon: PropTypes.string,
	showIcon: PropTypes.bool.isRequired,
	showViewLink: PropTypes.bool.isRequired,
	showEditLink: PropTypes.bool.isRequired,
	editIframeTitle: PropTypes.string,
	readOnly: PropTypes.bool,
};

export default ListSelectValues;
