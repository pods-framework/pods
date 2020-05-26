import React from 'react';
import * as PropTypes from 'prop-types';

// WordPress dependencies
import { __ } from '@wordpress/i18n';

// Pods dependencies
import Sluggable from 'dfv/src/components/sluggable';

const EditPodName = ( {
	podName,
	setPodName,
} ) => {
	return (
		<h2>
			{ __( 'Edit Pod: ', 'pods' ) }
			{ '\u00A0' /* &nbsp; */ }
			<Sluggable
				value={ podName }
				updateValue={ setPodName }
			/>
		</h2>
	);
};

EditPodName.propTypes = {
	podName: PropTypes.string.isRequired,
	setPodName: PropTypes.func.isRequired,
};

export default EditPodName;
