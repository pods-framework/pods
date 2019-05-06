import { DropTarget } from 'react-dnd';
import { uiConstants } from 'pods-dfv/src/admin/edit-pod/store/constants';

export default DropTarget(
	uiConstants.dragItemTypes.GROUP,
	{
		hover ( props, monitor, component ) {
			if ( !component ) {
				return null;
			}
			// node = HTML Div element from imperative API
			const node = component.getNode();
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
			const hoverMiddleY = ( hoverBoundingRect.bottom - hoverBoundingRect.top ) / 2;
			const clientOffset = monitor.getClientOffset();
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
			// Time to actually perform the action

			props.moveGroup( dragIndex, hoverIndex );
			// Note: we're mutating the monitor item here!
			// Generally it's better to avoid mutations,
			// but it's good here for the sake of performance
			// to avoid expensive index searches.

			monitor.getItem().index = hoverIndex;
		},
	},
	connect => ( {
		connectDropTarget: connect.dropTarget(),
	} ),
);
