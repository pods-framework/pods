import { DropTarget } from 'react-dnd';
import { uiConstants } from 'pods-dfv/src/admin/edit-pod/store/constants';

const { dragItemTypes } = uiConstants;

const dropSpec = {
	hover ( props, monitor, component ) {
		if ( !component ) {
			return null;
		}
		// node = HTML Div element from imperative API
		const node = component.getWrapperNode();
		if ( !node ) {
			return null;
		}
		const dragIndex = monitor.getItem().index;
		const hoverIndex = props.index;

		// Don't replace items with themselves
		if ( dragIndex === hoverIndex ) {
			return;
		}
		const hoverBoundingRect = node.getBoundingClientRect();
		const hoverMiddleY =
			( hoverBoundingRect.bottom - hoverBoundingRect.top ) / 2;
		const clientOffset = monitor.getClientOffset();
		const hoverClientY = clientOffset.y - hoverBoundingRect.top;

		// Only perform the move when the mouse has crossed half of the item's height

		// Dragging downwards, only move when the cursor is below 50%
		if ( dragIndex < hoverIndex && hoverClientY < hoverMiddleY ) {
			return;
		}
		// Dragging upwards, only move when the cursor is above 50%
		if ( dragIndex > hoverIndex && hoverClientY > hoverMiddleY ) {
			return;
		}

		// Time to actually perform the action
		props.moveGroup( dragIndex, hoverIndex );

		// Note: we're mutating the monitor item here! Generally it's
		// better to avoid mutations but it's good here for the sake of
		// performance to avoid expensive index searches.
		monitor.getItem().index = hoverIndex;
	},
};

const collect = ( connect ) => ( {
	connectDropTarget: connect.dropTarget(),
} );

export default DropTarget( dragItemTypes.GROUP, dropSpec, collect );
