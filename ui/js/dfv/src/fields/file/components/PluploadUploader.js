/* global plupload */

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import FileUploader from './FileUploader';

/**
 * Plupload uploader - Uses plupload library for direct file uploads
 */
class PluploadUploader extends FileUploader {
	constructor( config ) {
		super( config );
		this.plupload = null;
		this.browseButtonElement = null;
		this.pendingFiles = [];
	}

	static getType() {
		return 'plupload';
	}

	/**
	 * Initialize plupload with a browse button element
	 * @param {HTMLElement} browseButton The button element to attach plupload to
	 */
	initialize( browseButton ) {
		if ( ! browseButton || this.plupload ) {
			return;
		}

		this.browseButtonElement = browseButton;

		const {
			pluploadInit,
		} = this.config;

		// Set the browse button - required by plupload
		const pluploadConfig = {
			...pluploadInit,
			browse_button: browseButton,
		};

		this.plupload = new plupload.Uploader( pluploadConfig );
		this.plupload.init();

		// Setup callbacks
		this.plupload.bind( 'FilesAdded', ( up, files ) => this.handlePluploadFilesAdded( up, files ) );
		this.plupload.bind( 'UploadProgress', ( up, file ) => this.handleUploadProgress( up, file ) );
		this.plupload.bind( 'FileUploaded', ( up, file, resp ) => this.handleFileUploaded( up, file, resp ) );
		this.plupload.bind( 'UploadComplete', () => this.handleUploadComplete() );
		this.plupload.bind( 'Error', ( up, err ) => this.handleError( up, err ) );
	}

	handlePluploadFilesAdded( up, files ) {
		const { fileLimit, currentFileCount = 0 } = this.config;
		const parsedLimit = parseInt( fileLimit, 10 );

		if ( 0 < parsedLimit ) {
			const fullFileCount = files.length + currentFileCount;

			// Check if we have more files than the limit allows
			if ( parsedLimit < fullFileCount ) {
				let filesToRemove;

				if ( 0 < ( parsedLimit - currentFileCount ) ) {
					const fileLimitRemaining = parsedLimit - currentFileCount;
					filesToRemove = files.slice( fileLimitRemaining );
					files = files.slice( 0, fileLimitRemaining );
				} else {
					filesToRemove = files;
					files = [];
				}

				filesToRemove.forEach( ( file ) => {
					this.plupload.removeFile( file );
				} );
			}
		}

		// Initialize upload queue with files
		this.uploadQueue = files.map( ( file ) => ( {
			id: file.id,
			filename: file.name,
			progress: 0,
			errorMsg: '',
		} ) );

		// Notify parent of upload queue
		this.onProgressUpdate( this.uploadQueue );

		up.refresh();

		if ( 0 === files.length ) {
			return;
		}

		// Start the upload
		up.start();
	}

	handleUploadProgress( up, file ) {
		// Update progress for this file in the queue
		this.uploadQueue = this.uploadQueue.map( ( queueFile ) => {
			if ( queueFile.id === file.id ) {
				return {
					...queueFile,
					progress: file.percent,
				};
			}
			return queueFile;
		} );

		// Notify parent of progress update
		this.onProgressUpdate( this.uploadQueue );
	}

	handleFileUploaded( up, file, resp ) {
		let response = resp.response;

		// Error condition 1
		if ( 'Error: ' === resp.response.substr( 0, 7 ) ) {
			response = response.substr( 7 );
			if ( window.console ) {
				// eslint-disable-next-line no-console
				console.debug( response );
			}

			// Update queue with error
			this.uploadQueue = this.uploadQueue.map( ( queueFile ) => {
				if ( queueFile.id === file.id ) {
					return {
						...queueFile,
						progress: 0,
						errorMsg: response,
					};
				}
				return queueFile;
			} );

			this.onProgressUpdate( this.uploadQueue );
			this.onError( { message: response, file } );
			return;
		}

		// Error condition 2
		if ( '<e>' === resp.response.substr( 0, 3 ) ) {
			// Strip tags, text only
			response = response.replace( /(<([^>]+)>)/ig, '' );
			if ( window.console ) {
				// eslint-disable-next-line no-console
				console.debug( response );
			}

			// Update queue with error
			this.uploadQueue = this.uploadQueue.map( ( queueFile ) => {
				if ( queueFile.id === file.id ) {
					return {
						...queueFile,
						progress: 0,
						errorMsg: response,
					};
				}
				return queueFile;
			} );

			this.onProgressUpdate( this.uploadQueue );
			this.onError( { message: response, file } );
			return;
		}

		// Parse JSON response
		let json = response.match( /{.*}$/ );

		if ( null !== json && 0 < json.length ) {
			json = JSON.parse( json[ 0 ] );
		} else {
			json = {};
		}

		if ( 'object' !== typeof json || 0 === Object.keys( json ).length ) {
			if ( window.console ) {
				// eslint-disable-next-line no-console
				console.debug( { response, json } );
			}

			const errorMsg = __( 'Error uploading file: ', 'pods' ) + file.name;

			// Update queue with error
			this.uploadQueue = this.uploadQueue.map( ( queueFile ) => {
				if ( queueFile.id === file.id ) {
					return {
						...queueFile,
						progress: 0,
						errorMsg,
					};
				}
				return queueFile;
			} );

			this.onProgressUpdate( this.uploadQueue );
			this.onError( {
				message: errorMsg,
				file,
			} );
			return;
		}

		// Successfully uploaded file
		const newFile = {
			id: json.ID,
			icon: json.thumbnail,
			name: json.post_title,
			edit_link: json.edit_link,
			link: json.link,
			download: json.download,
		};

		this.pendingFiles.push( newFile );
	}

	handleError( up, err ) {
		// Update queue with error for the file
		if ( err.file ) {
			this.uploadQueue = this.uploadQueue.map( ( queueFile ) => {
				if ( queueFile.id === err.file.id ) {
					return {
						...queueFile,
						progress: 0,
						errorMsg: err.message,
					};
				}
				return queueFile;
			} );

			this.onProgressUpdate( this.uploadQueue );
		}

		// Call the error callback
		this.onError( err );
	}

	handleUploadComplete() {
		// Ensure pendingFiles is always an array
		if ( ! Array.isArray( this.pendingFiles ) ) {
			if ( window.console ) {
				// eslint-disable-next-line no-console
				console.error( 'pendingFiles is not an array:', this.pendingFiles );
			}
			this.pendingFiles = [];
		}

		const completedFiles = [ ...this.pendingFiles ];
		this.pendingFiles = [];

		// Clear upload queue after completion
		this.uploadQueue = [];
		this.onProgressUpdate( this.uploadQueue );

		if ( completedFiles.length > 0 ) {
			// Call the onFilesAdded callback with completed files
			this.onFilesAdded( completedFiles );
		}
	}

	open() {
		// Plupload handles the click automatically via the browse button
		// This method is here for interface compatibility
	}

	cleanup() {
		if ( this.plupload ) {
			this.plupload.destroy();
			this.plupload = null;
		}
		this.browseButtonElement = null;
		this.pendingFiles = [];
		this.uploadQueue = [];
	}
}

export default PluploadUploader;

