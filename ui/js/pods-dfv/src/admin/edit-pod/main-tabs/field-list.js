import React, { forwardRef, useEffect, useImperativeHandle, useRef, useState, useCallback } from 'react';
import * as PropTypes from 'prop-types';
import { useDrag, useDrop } from 'react-dnd';

import './manage-fields.scss';

// WordPress dependencies
// noinspection JSUnresolvedVariable
const { __ } = wp.i18n;
const { Dashicon } = wp.components;

export const FieldList = ( props ) => {
	const { groupName, cloneField, deleteField, fields, moveField } = props;

	if ( 0 === props.fields.length ) {
		return (
			<div className="pods-manage-fields no-fields">
				{ __( 'There are no fields in this group', 'pods' ) }
			</div>
		);
	}

	return (
		<div className="pods-manage-fields">
			<FieldHeader />
			{ props.fields.map( ( field, index ) => (
				<FieldListItem
					key={ field.id }
					id={ field.id }
					index={ index }
					fieldLabel={ field.label }
					fieldName={ field.name }
					required={ field.required }
					type={ field.type }
					position={ field.position }
					moveField={ moveField }
					groupName={ groupName }
					cloneField={ cloneField }
					deleteField={ deleteField }
				/>
			) ) }
			<FieldHeader />
		</div>
	);
};

FieldList.propTypes = {
	fields: PropTypes.array.isRequired,
};

/**
 * @param props
 * @param ref
 */
export const FieldListItem = ( props, ref ) => {
	const { id, fieldName, fieldLabel, required, type, index, moveField, groupName, cloneField, deleteField } = props;

	const wref = useRef( ref );
	const [ , drop ] = useDrop( {
		accept: 'field-list-item',
		hover( item, monitor ) {
			if ( ! wref.current ) {
				return;
			}
			const dragIndex = item.index;
			const hoverIndex = index;
			// Don't replace items with themselves
			if ( dragIndex === hoverIndex ) {
				return;
			}
			// Determine rectangle on screen
			const hoverBoundingRect = wref.current.getBoundingClientRect();
			// Get vertical middle
			const hoverMiddleY =
			( hoverBoundingRect.bottom - hoverBoundingRect.top ) / 2;
			// Determine mouse position
			const clientOffset = monitor.getClientOffset();
			// Get pixels to the top
			const hoverClientY = clientOffset.y - hoverBoundingRect.top;
			// Only perform the move when the mouse has crossed half of the items height
			// When dragging downwards, only move when the cursor is below 50%
			// When dragging upwards, only move when the cursor is above 50%
			// Dragging downwards

			if ( dragIndex < hoverIndex && hoverClientY < hoverMiddleY ) {
				return;
			}
			// Dragging upwards
			if ( dragIndex > hoverIndex && hoverClientY > hoverMiddleY ) {
				return;
			}
			// console.log("movefield")
			// Time to actually perform the action
			moveField( groupName, fieldName, dragIndex, hoverIndex, item );
			// Note: we're mutating the monitor item here!
			// Generally it's better to avoid mutations,
			// but it's good here for the sake of performance
			// to avoid expensive index searches.
			item.index = hoverIndex;
		},
	} );
	const [ { isDragging }, drag ] = useDrag( {
		item: { type: 'field-list-item', id, index, groupName, fieldName },
		collect: ( monitor ) => ( {
			isDragging: monitor.isDragging(),
		} ),
	} );
	const opacity = isDragging ? 0 : 1;
	drag( drop( wref ) );
	return (
		<div className="pods-field_wrapper" ref={ wref }>
			<div className="pods-field pods-field_handle">
				<Dashicon icon="menu" />
			</div>
			<div className="pods-field pods-field_label">
				<b>Field Label</b>
				<br />
				{ fieldLabel }<span className={ required ? 'pods-field_required' : '' }>*</span>
				<div className="pods-field_id"> [id = { id }]</div>
			</div>
			<div className="pods-field pods-field_name">
				<b>Field Name</b>
				<br />
				{ fieldName }
			</div>
			<div className="pods-field pods-field_type">
				<b>Type</b>
				<br />
				{ type }
				<div className="pods-field_id"> [type = [STILL NEED THIS]]</div>
			</div>
			<div className="pods-field pods-field_actions">
				<Dashicon icon="edit" />
				<Dashicon icon="admin-page" onClick={ ( e ) => {
					e.stopPropagation(); cloneField( groupName, type );
				} } />
				<Dashicon icon="trash" onClick={ ( e ) => {
					e.stopPropagation(); deleteField( groupName, fieldName );
				} } />
			</div>
		</div>
	);
};

FieldListItem.propTypes = {
	id: PropTypes.string.isRequired,
	fieldName: PropTypes.string.isRequired,
	fieldLabel: PropTypes.string.isRequired,
	required: PropTypes.bool.isRequired,
	type: PropTypes.string.isRequired,
};

/**
 *
 */
export const FieldHeader = () => {
	return (
		<div className="pods-field_wrapper-labels">
			<div className="pods-field_wrapper-label-items">Label</div>
			<div className="pods-field_wrapper-label-items">Name</div>
			<div className="pods-field_wrapper-label-items">Field Type</div>
		</div>
	);
};
