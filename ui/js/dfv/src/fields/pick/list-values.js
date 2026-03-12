/**
 * External dependencies
 */
import React, { useRef } from 'react';
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
import PropTypes from 'prop-types';

/**
 * Other Pods dependencies
 */
import ListItem from './list-item';

import './list-select.scss';

const ListValues = ( {
	fieldName,
	value: arrayOfValues,
	fieldItemData,
	setFieldItemData,
	setValue,
	isMulti,
	limit,
	defaultIcon,
	showIcon = false,
	showDownloadLink = false,
	showViewLink = false,
	showEditLink = false,
	showEditTitle = false,
	editIframeTitle,
	readOnly = false,
	onTitleChange,
	htmlAttrs = {},
} ) => {
	// Stable unique IDs for React keys. These move with items during
	// drag-and-drop reorder so React correctly tracks component instances.
	const nextIdRef = useRef( arrayOfValues.length );
	const itemIdsRef = useRef( arrayOfValues.map( ( _, i ) => i ) );

	// Ensure IDs array stays in sync if arrayOfValues length changes externally.
	if ( itemIdsRef.current.length < arrayOfValues.length ) {
		while ( itemIdsRef.current.length < arrayOfValues.length ) {
			itemIdsRef.current.push( nextIdRef.current++ );
		}
	} else if ( itemIdsRef.current.length > arrayOfValues.length ) {
		itemIdsRef.current = itemIdsRef.current.slice( 0, arrayOfValues.length );
	}

	const removeValueAtIndex = ( index = 0 ) => {
		itemIdsRef.current = [
			...itemIdsRef.current.slice( 0, index ),
			...itemIdsRef.current.slice( index + 1 ),
		];

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

	const swapValues = ( firstIndex, secondIndex ) => {
		if ( ! isMulti ) {
			return;
		}

		if (
			typeof arrayOfValues?.[ firstIndex ] === 'undefined' ||
			typeof arrayOfValues?.[ secondIndex ] === 'undefined'
		) {
			return;
		}

		const newIds = [ ...itemIdsRef.current ];
		const tempId = newIds[ secondIndex ];
		newIds[ secondIndex ] = newIds[ firstIndex ];
		newIds[ firstIndex ] = tempId;
		itemIdsRef.current = newIds;

		const newValues = [ ...arrayOfValues ];
		const tempValue = newValues[ secondIndex ];

		newValues[ secondIndex ] = newValues[ firstIndex ];
		newValues[ firstIndex ] = tempValue;

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

		const oldIndex = parseInt( active.id, 10 );
		const newIndex = parseInt( over.id, 10 );

		itemIdsRef.current = arrayMove( itemIdsRef.current, oldIndex, newIndex );

		const reorderedItems = arrayMove( arrayOfValues, oldIndex, newIndex );

		setValue( reorderedItems.map(
			( item ) => item.value )
		);
	};

	// May need to change the label, if it differs from the fieldItemData.
	const getValueWithCorrectedLabel = ( { label, value } ) => {
		const matchingFieldItemData = fieldItemData.find(
			( item ) => item.id.toString() === value.toString()
		);

		return {
			label: matchingFieldItemData?.name ? matchingFieldItemData.name : label,
			value,
		};
	};

	const isDraggable = ! readOnly && isMulti && ( 1 !== limit );

	return (
		<div className="pods-list-select-values-container">
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
					items={ arrayOfValues.map( ( valueItem, index ) => index.toString() ) }
					strategy={ verticalListSortingStrategy }
				>
					{ arrayOfValues.length ? (
						<div className="pods-list-select-values">
							{ arrayOfValues.map( ( valueItem, index ) => {
								return (
									<ListItem
										key={ `${ fieldName }-${ itemIdsRef.current[ index ] }` }
										fieldName={ fieldName }
										value={ getValueWithCorrectedLabel( valueItem ) }
										index={ index }
										isDraggable={ isDraggable }
										isRemovable={ ! readOnly }
										removeItem={ () => removeValueAtIndex( index ) }
										fieldItemData={ fieldItemData }
										setFieldItemData={ setFieldItemData }
										defaultIcon={ defaultIcon }
										showIcon={ showIcon }
										showDownloadLink={ showDownloadLink }
										showViewLink={ showViewLink }
										showEditLink={ ! readOnly && showEditLink }
										showEditTitle={ ! readOnly && showEditTitle }
										editIframeTitle={ editIframeTitle }
										onTitleChange={ onTitleChange }
										moveUp={
											( isDraggable && index !== 0 )
												? () => swapValues( index, index - 1 )
												: undefined
										}
										moveDown={
											( isDraggable && index !== ( arrayOfValues.length - 1 ) )
												? () => swapValues( index, index + 1 )
												: undefined
										}
										htmlAttrs={ htmlAttrs }
									/>
								);
							} ) }
						</div>
					) : null }
				</SortableContext>
			</DndContext>
		</div>
	);
};

ListValues.propTypes = {
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
	showIcon: PropTypes.bool,
	showDownloadLink: PropTypes.bool,
	showViewLink: PropTypes.bool,
	showEditLink: PropTypes.bool,
	showEditTitle: PropTypes.bool,
	editIframeTitle: PropTypes.string,
	readOnly: PropTypes.bool,
	onTitleChange: PropTypes.func,
	htmlAttrs: PropTypes.object,
};

export default ListValues;
