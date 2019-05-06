import React from 'react';
import PropTypes from 'prop-types';

import { FieldList } from 'pods-dfv/src/admin/edit-pod/main-tabs/field-list';

const { useState } = React;
const { Dashicon } = wp.components;

/**
 *
 */
const FieldGroup = ( { groupName, getGroupFields, dragInProgress } ) => {
	const [ expanded, setExpanded ] = useState( false );

	const toggleExpanded = () => {
		setExpanded( !expanded );
	};

	let Toggle;
	if ( expanded ) {
		Toggle = ( <Dashicon icon='arrow-up' /> );
	} else {
		Toggle = ( <Dashicon icon='arrow-down' /> );
	}

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
			{expanded && !dragInProgress && <FieldList fields={getGroupFields( groupName )} />}
		</div>
	);
};

FieldGroup.propTypes = {
	groupName: PropTypes.string.isRequired,
	getGroupFields: PropTypes.func.isRequired,
	dragInProgress: PropTypes.bool.isRequired,
};

export default FieldGroup;
