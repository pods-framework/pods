/**
 * External dependencies
 */
import React from 'react';
import PropTypes from 'prop-types';

/**
 * File upload queue item component
 * Shows upload progress or errors for a single file
 */
const FileUploadQueueItem = ( { file } ) => {
	const { filename, progress, errorMsg } = file;
	const hasError = errorMsg && '' !== errorMsg;

	return (
		<li className="pods-dfv-list-item" id={ file.id }>
			<ul className="pods-dfv-list-meta media-item">
				{ ! hasError && (
					<li className="pods-dfv-list-col pods-progress">
						<div
							className="progress-bar"
							style={ { width: `${ progress }%` } }
						/>
					</li>
				) }
				<li className="pods-dfv-list-col pods-dfv-list-name">
					{ filename }
				</li>
			</ul>
			{ hasError && (
				<div className="error">{ errorMsg }</div>
			) }
		</li>
	);
};

FileUploadQueueItem.propTypes = {
	file: PropTypes.shape( {
		id: PropTypes.string.isRequired,
		filename: PropTypes.string.isRequired,
		progress: PropTypes.number,
		errorMsg: PropTypes.string,
	} ).isRequired,
};

/**
 * File upload queue component
 * Shows upload progress for multiple files
 */
const FileUploadQueue = ( { files } ) => {
	if ( ! files || 0 === files.length ) {
		return null;
	}

	return (
		<ul className="pods-dfv-list pods-dfv-list-queue">
			{ files.map( ( file ) => (
				<FileUploadQueueItem key={ file.id } file={ file } />
			) ) }
		</ul>
	);
};

FileUploadQueue.propTypes = {
	files: PropTypes.arrayOf(
		PropTypes.shape( {
			id: PropTypes.string.isRequired,
			filename: PropTypes.string.isRequired,
			progress: PropTypes.number,
			errorMsg: PropTypes.string,
		} )
	),
};

export default FileUploadQueue;

