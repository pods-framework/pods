import { DragSource } from 'react-dnd';
import { uiConstants } from 'pods-dfv/src/admin/edit-pod/store/constants';

const { dragItemTypes } = uiConstants;

const dragSpec = {
	beginDrag: props => {
		props.handleBeginDrag();

		return {
			groupName: props.groupName,
			index: props.index,
		};
	},
	endDrag: ( props, monitor ) => {

		// Items are re-ordered on the fly, be sure to reset on cancel
		if ( !monitor.didDrop() ) {
			props.handleDragCancel();
		}
	},
};

const collect = ( connect, monitor ) => ( {
	connectDragSource: connect.dragSource(),
	connectDragPreview: connect.dragPreview(),
	isDragging: monitor.isDragging(),
} );

export default DragSource( dragItemTypes.GROUP, dragSpec, collect );
