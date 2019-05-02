import React from 'react';
import PropTypes from 'prop-types';
const { useState } = React;

const { Dashicon } = wp.components;

import { ManageFields } from 'pods-dfv/src/admin/edit-pod/main-tabs/manage-fields';
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
					fields={props.fields}
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
	fields: PropTypes.array.isRequired,
};

/**
 *
 */
const FieldGroup = ( props ) => {
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
				<div className="pods-field-group--name">{props.groupName}</div>
				<div className="pods-field-group--manage">
					{ expanded ?
						( <Dashicon icon='arrow-up' onClick={toggleExpanded} /> ) :
						( <Dashicon icon='arrow-down' onClick={toggleExpanded} /> )
					}
				</div>
			</div>
			{ expanded && <ManageFields fields={props.fields} /> }
		</div>
	);
};

FieldGroup.propTypes = {
	groupName: PropTypes.string.isRequired,
	fields: PropTypes.array.isRequired,
};

