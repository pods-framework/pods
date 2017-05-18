/*global jQuery, _, Backbone, Marionette, wp, plupload, PodsI18n */
import {PodsFileUploader} from 'pods-dfv/_src/file-upload/uploaders/pods-file-uploader';
import {FileUploadQueueModel, FileUploadQueue} from 'pods-dfv/_src/file-upload/views/file-upload-queue';

export const Plupload = PodsFileUploader.extend( {
	plupload: {},

	fileUploader: 'plupload',

	initialize: function () {
		// Set the browse button argument for plupload... it's required
		this.fieldConfig[ 'plupload_init' ][ 'browse_button' ] = this.browseButton;

		this.plupload = new plupload.Uploader( this.fieldConfig[ 'plupload_init' ] );
		this.plupload.init();

		// Setup all callbacks: ( event_name, callback, context )
		this.plupload.bind( 'FilesAdded', this.onFilesAdded, this );
		this.plupload.bind( 'UploadProgress', this.onUploadProgress, this );
		this.plupload.bind( 'FileUploaded', this.onFileUploaded, this );
	},

	/**
	 * Fired after files have been selected from the dialog
	 *
	 * @param up
	 * @param files
	 */
	onFilesAdded: function ( up, files ) {
		let model,
			collection,
			view;

		// Assemble the collection data for the file queue
		collection = new Backbone.Collection();
		jQuery.each( files, function ( index, file ) {
			model = new FileUploadQueueModel( {
				id      : file.id,
				filename: file.name
			} );

			collection.add( model );
		} );

		// Create a new view based on the collection
		view = new FileUploadQueue( { collection: collection } );
		view.render();  // Generate the HTML, not attached to the DOM yet

		// Reset the region in case any error messages are hanging around from a previous upload
		// and show the new file upload queue
		this.uiRegion.reset();
		this.uiRegion.show( view );

		// Stash references
		this.queueCollection = collection;

		up.refresh();
		up.start();
	},

	/**
	 *
	 * @param up
	 * @param file
	 */
	onUploadProgress: function ( up, file ) {
		const model = this.queueCollection.get( file.id );
		model.set( { progress: file.percent } );
	},

	/**
	 *
	 * @param up
	 * @param file
	 * @param resp
	 */
	onFileUploaded: function ( up, file, resp ) {
		const model = this.queueCollection.get( file.id );
		let response = resp.response;
		let newFile = [];
		let json;

		// Error condition 1
		if ( "Error: " == resp.response.substr( 0, 7 ) ) {
			response = response.substr( 7 );
			if ( window.console ) {
				console.log( response );
			}

			model.set( {
				progress: 0,
				errorMsg: response
			} );
		}
		// Error condition 2
		else if ( "<e>" == resp.response.substr( 0, 3 ) ) {
			response = jQuery( response ).text(); // Strip tags, text only
			if ( window.console ) {
				console.log( response );
			}

			model.set( {
				progress: 0,
				errorMsg: response
			} );
		}
		else {
			json = response.match( /{.*}$/ );

			if ( null !== json && 0 < json.length ) {
				json = jQuery.parseJSON( json[ 0 ] );
			}
			else {
				json = {};
			}

			if ( 'object' != typeof json || jQuery.isEmptyObject( json ) ) {
				if ( window.console ) {
					console.log( response );
				}
				if ( window.console ) {
					console.log( json );
				}

				model.set( {
					progress: 0,
					errorMsg: PodsI18n.__( 'Error uploading file: ' ) + file.name
				} );
				return;
			}

			newFile = {
				id       : json.ID,
				icon     : json.thumbnail,
				name     : json.post_title,
				edit_link: json.edit_link,
				link     : json.link,
				download : json.download
			};

			// Remove the file from the upload queue model and trigger an event for the hosting container
			model.trigger( 'destroy', model );
			this.trigger( 'added:files', newFile );
		}
	}

} );

