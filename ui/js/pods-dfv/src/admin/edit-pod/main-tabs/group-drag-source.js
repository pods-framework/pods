import { DragSource } from 'react-dnd';
import { uiConstants } from 'pods-dfv/src/admin/edit-pod/store/constants';

const { dragItemTypes } = uiConstants;

const dragSpec = {
	beginDrag: ( props, monitor, component ) => {
		const wrapperNode = component.decoratedRef.current.getWrapperNode();
		const handleNode = component.decoratedRef.current.getHandleNode();
		const wrapperRect = wrapperNode.getBoundingClientRect();
		const handleRect = handleNode.getBoundingClientRect();

		return {
			groupName: props.group.name,
			groupLabel: props.group.label,
			groupID: props.group.id,
			index: props.index,
			width: wrapperRect.width,
			left: wrapperRect.left - handleRect.left,
		};
	},
};

const collect = ( connect, monitor ) => ( {
	connectDragSource: connect.dragSource(),
	connectDragPreview: connect.dragPreview(),
	isDragging: monitor.isDragging(),
} );

export default DragSource( dragItemTypes.GROUP, dragSpec, collect );
