/**
 * Base class for file uploaders in React components.
 * Provides a common interface for different upload strategies (media modal, plupload, etc.)
 */
class FileUploader {
	constructor( config ) {
		this.config = config;
		this.onFilesAdded = config.onFilesAdded || ( () => {} );
		this.onError = config.onError || ( () => {} );
	}

	/**
	 * Open/invoke the uploader interface
	 */
	open() {
		throw new Error( 'FileUploader.open() must be implemented by subclass' );
	}

	/**
	 * Clean up any resources when component unmounts
	 */
	cleanup() {
		// Override in subclass if needed
	}

	/**
	 * Get the uploader type identifier
	 */
	static getType() {
		throw new Error( 'FileUploader.getType() must be implemented by subclass' );
	}
}

export default FileUploader;

