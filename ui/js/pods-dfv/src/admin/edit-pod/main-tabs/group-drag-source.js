import { DragSource } from 'react-dnd';
import { uiConstants } from 'pods-dfv/src/admin/edit-pod/store/constants';

export default DragSource(
	uiConstants.dragItemTypes.GROUP,
	{
		beginDrag: props => {
			props.handleBeginDrag();

			return {
				groupName: props.groupName,
				index: props.index,
			};
		},
		endDrag: ( props, monitor ) => {
			// Items are re-ordered on the fly, be sure to reset on cancel
			if ( monitor.didDrop() ) {
				props.handleEndDrag();
			} else {
				props.handleEndDragCancel();
			}
		},
	},
	( connect, monitor ) => ( {
		connectDragSource: connect.dragSource(),
		connectDragPreview: connect.dragPreview(),
		isDragging: monitor.isDragging(),
	} ),
);
