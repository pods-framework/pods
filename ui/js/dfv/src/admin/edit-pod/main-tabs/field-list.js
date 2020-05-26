import React, { useRef } from 'react';
import * as PropTypes from 'prop-types';
import { useDrag, useDrop } from 'react-dnd';

// WordPress dependencies
import { __ } from '@wordpress/i18n';
import { Dashicon } from '@wordpress/components';
import { FIELD_PROP_TYPE_SHAPE } from 'dfv/src/prop-types';

import './manage-fields.scss';

export const FieldListItem = ( props, ref ) => {
	const {
		field: {
			id,
			name,
			label,
			required,
			type,
		},
		index,
		moveField,
		groupName,
		cloneField,
		deleteField,
	} = props;

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
			moveField( name, dragIndex, hoverIndex, item );
			// Note: we're mutating the monitor item here!
			// Generally it's better to avoid mutations,
			// but it's good here for the sake of performance
			// to avoid expensive index searches.
			item.index = hoverIndex;
		},
	} );
	const [ { isDragging }, drag ] = useDrag( {
		item: { type: 'field-list-item', id, index, groupName, name },
		collect: ( monitor ) => ( {
			isDragging: monitor.isDragging(),
		} ),
	} );

	// @todo is this variable going to be used?
	// eslint-disable-next-line no-unused-vars
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
				{ label }<span className={ required ? 'pods-field_required' : '' }>*</span>
				<div className="pods-field_id"> [id = { id }]</div>
			</div>
			<div className="pods-field pods-field_name">
				<b>Field Name</b>
				<br />
				{ name }
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
					e.stopPropagation(); cloneField( type );
				} } />
				<Dashicon icon="trash" onClick={ ( e ) => {
					e.stopPropagation(); deleteField( name );
				} } />
			</div>
		</div>
	);
};

FieldListItem.propTypes = {
	field: FIELD_PROP_TYPE_SHAPE,
	// position: PropTypes.number.isRequired,
	index: PropTypes.number.isRequired,
	groupName: PropTypes.string.isRequired,
	moveField: PropTypes.func.isRequired,
	cloneField: PropTypes.func.isRequired,
	deleteField: PropTypes.func.isRequired,
};

const FieldList = ( props ) => {
	const { groupName, addField, cloneField, deleteField, moveField } = props;

	if ( 0 === props.fields.length ) {
		return (
			<div className="pods-manage-fields no-fields">
				<button
					className="pods-field-group_add_field_link"
					onClick={ () => addField() }
				>
					{ __( 'Add Field', 'pods' ) }
				</button>
				{ __( 'There are no fields in this group', 'pods' ) }
			</div>
		);
	}

	return (
		<div className="pods-manage-fields">
			<div className="pods-field_wrapper-labels">
				<div className="pods-field_wrapper-label-items">Label</div>
				<div className="pods-field_wrapper-label-items">Name</div>
				<div className="pods-field_wrapper-label-items">Field Type</div>
			</div>

			<button
				className="pods-field-group_add_field_link"
				onClick={ () => addField() }
			>
				{ __( 'Add Field', 'pods' ) }
			</button>

			{ props.fields.map( ( field, index ) => (
				<FieldListItem
					key={ field.id }
					field={ field }
					index={ index }
					// position={ field.position }
					moveField={ moveField }
					groupName={ groupName }
					cloneField={ cloneField }
					deleteField={ deleteField }
				/>
			) ) }
		</div>
	);
};

FieldList.propTypes = {
	fields: PropTypes.arrayOf(
		FIELD_PROP_TYPE_SHAPE
	).isRequired,
	addField: PropTypes.func.isRequired,
	groupName: PropTypes.string.isRequired,
	cloneField: PropTypes.func.isRequired,
	deleteField: PropTypes.func.isRequired,
	moveField: PropTypes.func.isRequired,
};

export default FieldList;
