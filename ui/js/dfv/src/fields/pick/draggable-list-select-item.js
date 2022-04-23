/**
 * External dependencies
 */
 import React from 'react';
 import { useSortable } from '@dnd-kit/sortable';
 import { CSS } from '@dnd-kit/utilities';
 import PropTypes from 'prop-types';

 /**
  * Other Pods dependencies
  */
 import ListSelectItem from './list-select-item';

const DraggableListSelectItem = ( props ) => {
	const { value } = props;

	const {
		attributes,
		listeners,
		setNodeRef,
		transform,
		transition,
		isDragging,
	} = useSortable( {
		id: value.value.toString(),
		data: {
			value: value?.value.toString(),
			label: value?.label.toString(),
		},
	} );

	const style = {
		transform: CSS.Translate.toString( transform ),
		transition,
	};

	return (
		<ListSelectItem
			{...props}
			isDragging={ isDragging }
			ref={ setNodeRef }
			style={style}
			attributes={attributes}
			listeners={listeners}
		/>
	);
};

DraggableListSelectItem.propTypes = {
	fieldName: PropTypes.string.isRequired,
	value: PropTypes.shape( {
		label: PropTypes.string.isRequired,
		value: PropTypes.string.isRequired,
	} ),
	editLink: PropTypes.string,
	editIframeTitle: PropTypes.string,
	viewLink: PropTypes.string,
	icon: PropTypes.string,
	isDraggable: PropTypes.bool.isRequired,
	isRemovable: PropTypes.bool.isRequired,
	moveUp: PropTypes.func,
	moveDown: PropTypes.func,
	removeItem: PropTypes.func.isRequired,
	setFieldItemData: PropTypes.func.isRequired,
};

export default DraggableListSelectItem;
