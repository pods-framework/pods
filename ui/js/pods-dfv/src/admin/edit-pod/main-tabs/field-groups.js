import React from 'react';
import PropTypes from 'prop-types';
import { Container, Draggable } from 'react-smooth-dnd';
import { FieldList } from 'pods-dfv/src/admin/edit-pod/main-tabs/field-list';
import './field-groups.scss';

const { useState } = React;
const { Dashicon } = wp.components;

/**
 *
 */
export const FieldGroups = ( props ) => {
	const handleGroupDrop = ( dragResult ) => {
		const { removedIndex, addedIndex, payload } = dragResult;
		props.reorderGroupItem( removedIndex, addedIndex );
	};

	const containerProps = {
		groupName: 'groups',
		lockAxis: 'y',
		dragHandleSelector: '.pods-field-group--handle',
		dragClass: 'opacity-ghost',
		onDrop: handleGroupDrop,
	};

	// noinspection RequiredAttributes
	return (
		<div className="field-groups">
			<Container {...containerProps}>
				{props.groups.map( thisGroup => (
					<Draggable key={thisGroup.name}>
						<FieldGroup
							groupName={thisGroup.name}
							getGroupFields={props.getGroupFields}
						/>
					</Draggable>
				) )}
			</Container>
			<div className="pods-button-group--container">
				<a href="#">Add Group</a> <a href="#">Add Field</a>
			</div>
		</div>
	);
};

FieldGroups.propTypes = {
	groups: PropTypes.array.isRequired,
	getGroupFields: PropTypes.func.isRequired,
	reorderGroupItem: PropTypes.func.isRequired,
};

/**
 *
 */
const FieldGroup = ( props ) => {
	let Toggle;
	const { groupName, getGroupFields } = props;
	const [ expanded, setExpanded ] = useState( false );

	const toggleExpanded = () => {
		setExpanded( !expanded );
	};

	if ( expanded ) {
		Toggle = ( <Dashicon icon='arrow-up' /> );
	} else {
		Toggle = ( <Dashicon icon='arrow-down' /> );
	}

	// noinspection RequiredAttributes
	return (
		<div className="pods-field-group-wrapper">
			<div className="pods-field-group--title" onClick={toggleExpanded}>
				<div className="pods-field-group--handle">
					<Dashicon icon='menu' />
				</div>
				<div className="pods-field-group--name">{groupName}</div>
				<div className="pods-field-group--manage">
					<div className="pods-field-group--toggle">
						{Toggle}
					</div>
				</div>
			</div>
			{expanded && <FieldList fields={getGroupFields( groupName )} />}
		</div>
	);
};

FieldGroup.propTypes = {
	groupName: PropTypes.string.isRequired,
	getGroupFields: PropTypes.func.isRequired,
};

