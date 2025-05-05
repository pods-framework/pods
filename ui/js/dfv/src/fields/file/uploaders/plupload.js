/*global plupload */
import Backbone from 'backbone';

import { __ } from '@wordpress/i18n';

import { PodsFileUploader } from 'dfv/src/fields/file/uploaders/pods-file-uploader';
import { FileUploadQueueModel, FileUploadQueue } from 'dfv/src/fields/file/views/file-upload-queue';

export const Plupload = PodsFileUploader.extend( {
	plupload: {},

	fileUploader: 'plupload',

	pendingModels: [],
	pendingFiles: [],

	/**
	 * @property {Backbone.Collection} queueCollection
	 */

	initialize() {
		// Set the browse button argument for plupload... it's required
		this.fieldConfig.plupload_init.browse_button = this.browseButton[0];

		this.plupload = new plupload.Uploader( this.fieldConfig.plupload_init );
		this.plupload.init();

		// Setup all callbacks: ( event_name, callback, context )
		this.plupload.bind( 'FilesAdded', this.onFilesAdded, this );
		this.plupload.bind( 'UploadProgress', this.onUploadProgress, this );
		this.plupload.bind( 'FileUploaded', this.onFileUploaded, this );
		this.plupload.bind( 'UploadComplete', this.onUploadComplete, this );

		this.pendingFiles = [];
		this.pendingModels = [];
	},

	onFilesAdded( up, files ) {
		let model;

		// Assemble the collection data for the file queue
		const collection = new Backbone.Collection();

		const fileLimit = parseInt( this.fieldConfig?.file_limit ?? 0, 10 );

		if ( 0 < fileLimit ) {
			const fullFileCount = files.length + this.fileCollection.models.length;

			// Check if we have more files selected and to be uploaded than the limit allows.
			if ( fileLimit < fullFileCount ) {
				let filesToRemove = [];

				if ( 0 < ( fileLimit - this.fileCollection.models.length ) ) {
					const fileLimitRemaining = fileLimit - this.fileCollection.models.length;

					filesToRemove = files.slice( fileLimitRemaining );

					files = files.slice( 0, fileLimitRemaining );
				} else {
					filesToRemove = files;

					files = [];
				}

				const pluploadInstance = this.plupload;

				filesToRemove.forEach( function( file ) {
					pluploadInstance.removeFile( file );
				} );
			}
		}

		files.forEach( function( file ) {
			model = new FileUploadQueueModel( {
				id: file.id,
				filename: file.name,
			} );

			collection.add( model );
		} );

		// Reset the region in case any error messages are hanging around from a previous upload
		// and show the new file upload queue
		this.uiRegion.reset();

		up.refresh();

		if ( 0 === files.length ) {
			return;
		}

		// Create a new view based on the collection
		const view = new FileUploadQueue( { collection } );
		view.render(); // Generate the HTML, not attached to the DOM yet

		this.uiRegion.show( view );

		// Stash references
		this.queueCollection = collection;

		up.start();
	},

	onUploadProgress( up, file ) {
		const model = this.queueCollection.get( file.id );

		if ( 'undefined' === typeof model ) {
			return;
		}

		model.set( { progress: file.percent } );
	},

	onFileUploaded( up, file, resp ) {
		const model = this.queueCollection.get( file.id );
		let response = resp.response;
		let newFile = [];
		let json;

		// Error condition 1
		if ( 'Error: ' === resp.response.substr( 0, 7 ) ) {
			response = response.substr( 7 );
			if ( window.console ) {
				// eslint-disable-next-line no-console
				console.debug( response );
			}

			model.set( {
				progress: 0,
				errorMsg: response,
			} );

			// Error condition 2
		} else if ( '<e>' === resp.response.substr( 0, 3 ) ) {
			// Strip tags, text only.
			response = response.replace( /(<([^>]+)>)/ig, '' );
			if ( window.console ) {
				// eslint-disable-next-line no-console
				console.debug( response );
			}

			model.set( {
				progress: 0,
				errorMsg: response,
			} );
		} else {
			json = response.match( /{.*}$/ );

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

				model.set( {
					progress: 0,
					errorMsg: __( 'Error uploading file: ' ) + file.name,
				} );
				return;
			}

			newFile = {
				id: json.ID,
				icon: json.thumbnail,
				name: json.post_title,
				edit_link: json.edit_link,
				link: json.link,
				download: json.download,
			};

			this.pendingModels.push( model );
			this.pendingFiles.push( newFile );
		}
	},

	onUploadComplete( up, files ) {
		const pendingModels = this.pendingModels;
		const pendingFiles = this.pendingFiles;

		this.pendingModels = [];
		this.pendingFiles = [];

		pendingModels.forEach( function( model ) {
			if ( 'undefined' === typeof model ) {
				return;
			}

			// Remove the file from the upload queue model and trigger an event for the hosting container
			model.trigger( 'destroy', model );
		} );

		this.trigger( 'added:files', pendingFiles );
	},
} );

