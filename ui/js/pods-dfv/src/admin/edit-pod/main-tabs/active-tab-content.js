/* eslint-disable react/prop-types */
import React from 'react';
import { STORE_KEY_EDIT_POD, tabNames } from 'pods-dfv/src/admin/edit-pod/store/constants';
import { TabManageFields } from './tab-manage-fields';
import { TabLabels } from './tab-labels';
import { TabAdminUI } from './tab-admin-ui';
import { TabAdvancedOptions } from './tab-advanced-options';
import { TabAutoTemplateOptions } from './tab-auto-template-options';
import { TabRestAPI } from './tab-rest-api';

const { withSelect } = wp.data;

export const ActiveTabContent = withSelect( ( select ) => {
	return {
		activeTab: select( STORE_KEY_EDIT_POD ).getActiveTab()
	};
} )
( ( props ) => {
	const getActiveTabComponent = () => {
		switch ( props.activeTab ) {
			case tabNames.LABELS:
				return ( <TabLabels /> );

			case tabNames.ADMIN_UI:
				return ( <TabAdminUI /> );

			case tabNames.ADVANCED_OPTIONS:
				return ( <TabAdvancedOptions /> );

			case tabNames.AUTO_TEMPLATE_OPTIONS:
				return ( <TabAutoTemplateOptions /> );

			case tabNames.REST_API:
				return ( <TabRestAPI /> );

			case tabNames.MANAGE_FIELDS:
			default:
				return ( <TabManageFields /> );
		}
	};

	return (
		<div id='post-body-content' className='pods-nav-tab-group'>
			{getActiveTabComponent()}
		</div>
	);
} );
