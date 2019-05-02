import React from 'react';
import PropTypes from 'prop-types';
const { useState } = React;

const { Dashicon } = wp.components;

import { FieldList } from 'pods-dfv/src/admin/edit-pod/main-tabs/field-list';
import './field-groups.scss';

/**
 *
 */
export const FieldGroups = ( props ) => {
	return (
		<div className="field-groups">
			{props.groups.map( thisGroup => (
				<FieldGroup
					key={thisGroup.name}
					groupName={thisGroup.name}
					getGroupFields={props.getGroupFields}
				/>
			) ) }
			<div className="pods-button-group--container">
				<a href="#">Add Group</a> <a href="#">Add Field</a>
			</div>
		</div>
	);
};

FieldGroups.propTypes = {
	groups: PropTypes.array.isRequired,
	getGroupFields: PropTypes.func.isRequired,
};

/**
 *
 */
const FieldGroup = ( props ) => {
	const { groupName, getGroupFields } = props;
	const [ expanded, setExpanded ] = useState( false );

	const toggleExpanded = () => {
		setExpanded( !expanded );
	};

	return (
		<div className="pods-field-group-wrapper">
			<div className="pods-field-group--title">
				<div className="pods-field-group--handle">
					<Dashicon icon='menu' />
				</div>
				<div className="pods-field-group--name">{groupName}</div>
				<div className="pods-field-group--manage">
					{ expanded ?
						( <Dashicon icon='arrow-up' onClick={toggleExpanded} /> ) :
						( <Dashicon icon='arrow-down' onClick={toggleExpanded} /> )
					}
				</div>
			</div>
			{ expanded && <FieldList fields={getGroupFields( groupName )} /> }
		</div>
	);
};

FieldGroup.propTypes = {
	groupName: PropTypes.string.isRequired,
	getGroupFields: PropTypes.func.isRequired,
};

