import React from 'react';
import PropTypes from 'prop-types';

// WordPress dependencies
import { __ } from '@wordpress/i18n';

// Pods dependencies
import Sluggable from 'dfv/src/components/sluggable';

const EditPodName = ( {
	podName,
	setPodName,
	isEditable,
} ) => {
	return (
		<h2>
			{ __( 'Edit Pod: ', 'pods' ) }
			{ '\u00A0' /* &nbsp; */ }
			{ isEditable ? (
				<Sluggable
					value={ podName }
					updateValue={ setPodName }
				/>
			) : (
				<strong>
					{ podName }
				</strong>
			) }
		</h2>
	);
};

EditPodName.propTypes = {
	podName: PropTypes.string.isRequired,
	setPodName: PropTypes.func.isRequired,
	isEditable: PropTypes.bool.isRequired,
};

export default EditPodName;
