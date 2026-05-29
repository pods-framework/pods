/**
 * Internal dependencies
 */
import MediaModalUploader from './MediaModalUploader';
import PluploadUploader from './PluploadUploader';

/**
 * Registry of available uploaders
 */
const uploaders = [
	MediaModalUploader,
	PluploadUploader,
];

/**
 * Create the appropriate file uploader based on configuration
 *
 * @param {Object}        config                    - Uploader configuration
 * @param {string}        config.fileUploader       - Type of uploader ('attachment', 'plupload')
 * @param {Function}      config.onFilesAdded       - Callback when files are added
 * @param {Function}      config.onError            - Callback for errors
 * @param {string}        config.fileModalTitle     - Title for media modal
 * @param {string}        config.fileModalAddButton - Button text for media modal
 * @param {string|number} config.fileLimit          - Maximum number of files
 * @param {string}        config.limitTypes         - Allowed file types
 * @param {string}        config.limitExtensions    - Allowed file extensions
 * @param {string}        config.filePostId         - Post ID for upload context
 * @param {string}        config.fileAttachmentTab  - Default tab in media modal
 * @param {Object}        config.pluploadInit       - Plupload initialization config
 * @param {number}        config.currentFileCount   - Current number of files (for limit calculation)
 *
 * @return {FileUploader|null} The uploader instance or null if not found
 */
export const createUploader = ( config ) => {
	const uploaderType = config.fileUploader || 'attachment';

	// Find the uploader class that matches the type
	const UploaderClass = uploaders.find(
		( uploader ) => uploader.getType() === uploaderType
	);

	if ( ! UploaderClass ) {
		if ( window.console ) {
			// eslint-disable-next-line no-console
			console.error( `Could not locate file uploader '${ uploaderType }'` );
		}
		return null;
	}

	return new UploaderClass( config );
};

export default createUploader;

