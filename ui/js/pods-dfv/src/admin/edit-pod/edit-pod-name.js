import React from 'react';

// Pods dependencies
import { STORE_KEY_EDIT_POD } from 'pods-dfv/src/admin/edit-pod/store/constants';
import { PodsDFVSluggable } from 'pods-dfv/src/admin/edit-pod/sluggable';

// WordPress dependencies
import { __ } from '@wordpress/i18n';
const { withSelect, withDispatch } = wp.data;
const { compose } = wp.compose;

const EditPodName = ( props ) => {
	return (
		<h2>
			{ __( 'Edit Pod: ', 'pods' ) }
			{ '\u00A0' /* &nbsp; */ }
			<PodsDFVSluggable
				value={ props.podName }
				updateValue={ props.setPodName }
			/>
		</h2>
	);
};

export default compose( [
	withSelect( ( select ) => {
		return {
			podName: select( STORE_KEY_EDIT_POD ).getPodName(),
		};
	} ),
	withDispatch( ( dispatch ) => {
		return {
			setPodName: dispatch( STORE_KEY_EDIT_POD ).setPodName,
		};
	} ),
] )( EditPodName );
