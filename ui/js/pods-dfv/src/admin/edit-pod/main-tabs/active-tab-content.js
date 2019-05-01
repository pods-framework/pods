import React from 'react';
import PropTypes from 'prop-types';

// Pods dependencies
import { ManageFieldsTab } from './manage-fields-tab';
import { DynamicTabContent } from './dynamic-tab-content';

/**
 * ActiveTabContent
 *
 * Display the content for the active tab, manage-fields is treated special
 */
export const ActiveTabContent = ( props ) => {
	let Component;

	if ( 'manage-fields' === props.activeTab ) {
		Component = ( <ManageFieldsTab fields={props.fields} /> );
	} else {
		Component = (
			<DynamicTabContent
				tabOptions={props.tabOptions}
				getOptionValue={props.getOptionValue}
				setOptionValue={props.setOptionValue}
			/>
		);
	}

	return (
		<div id='post-body-content' className='pods-nav-tab-group pods-manage-field'>
			{Component}
		</div>
	);
};

ActiveTabContent.propTypes = {
	fields: PropTypes.array.isRequired,
	activeTab: PropTypes.string.isRequired,
	tabOptions: PropTypes.array.isRequired,
	getOptionValue: PropTypes.func.isRequired,
	setOptionValue: PropTypes.func.isRequired,
};
