import { DragSource } from 'react-dnd';
import { uiConstants } from 'pods-dfv/src/admin/edit-pod/store/constants';

export default DragSource( uiConstants.dragItemTypes.GROUP,
	{
		beginDrag: props => {
			props.setDragInProgress( true );
			return {
				groupName: props.groupName,
				index: props.index,
			};
		},
		endDrag: props => {
			props.setDragInProgress( false );
		}
	},
	( connect, monitor ) => ( {
		connectDragSource: connect.dragSource(),
		connectDragPreview: connect.dragPreview(),
		isDragging: monitor.isDragging(),
	} ),
);
