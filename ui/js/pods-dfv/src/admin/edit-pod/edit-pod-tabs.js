/* eslint-disable react/prop-types */
import React from 'react';

export const PodsDFVEditPodTabs = ()  => {

	return (
		<h2 className='nav-tab-wrapper pods-nav-tabs'>
			<a href='#pods-manage-fields' className='nav-tab nav-tab-active pods-nav-tab-link'> Manage Fields </a>
			<a href='#pods-labels' className='nav-tab pods-nav-tab-link'> Labels </a>
			<a href='#pods-admin-ui' className='nav-tab pods-nav-tab-link'> Admin UI </a>
			<a href='#pods-advanced' className='nav-tab pods-nav-tab-link'> Advanced Options </a>
			<a href='#pods-pods-pfat' className='nav-tab pods-nav-tab-link'> Auto Template Options </a>
			<a href='#pods-rest-api' className='nav-tab pods-nav-tab-link'> REST API </a>
		</h2>
	);
};
