/*global jQuery, _, Backbone, Mn, wp, plupload */
const $ = jQuery;

import { PodsFileUploader } from './pods-file-uploader';
import { FileUploadQueueModel, FileUploadQueue } from '../views/file-upload-queue';

export const Plupload = PodsFileUploader.extend( {
	plupload: {},

	initialize: function () {

		// Set the browse button argument for plupload... it's required
		this.field_options.plupload_init.browse_button = this.browse_button;

		this.plupload = new plupload.Uploader( this.field_options.plupload_init );
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
		var model,
			collection,
			view;

		// Assemble the collection data for the file queue
		collection = new Backbone.Collection();
		$.each( files, function ( index, file ) {
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
		this.ui_region.reset();
		this.ui_region.show( view );

		// Stash references
		this.queue_collection = collection;

		up.refresh();
		up.start();
	},

	/**
	 *
	 * @param up
	 * @param file
	 */
	onUploadProgress: function ( up, file ) {
		var model = this.queue_collection.get( file.id );
		model.set( { progress: file.percent } );
	},

	/**
	 *
	 * @param up
	 * @param file
	 * @param resp
	 */
	onFileUploaded: function ( up, file, resp ) {
		var response = resp.response,
			new_file = [],
			model = this.queue_collection.get( file.id );

		// Error condition 1
		if ( "Error: " == resp.response.substr( 0, 7 ) ) {
			response = response.substr( 7 );
			if ( window.console ) {
				console.log( response );
			}

			model.set( {
				progress : 0,
				error_msg: response
			} );
		}
		// Error condition 2
		else if ( "<e>" == resp.response.substr( 0, 3 ) ) {
			response = $( response ).text(); // Strip tags, text only
			if ( window.console ) {
				console.log( response );
			}

			model.set( {
				progress : 0,
				error_msg: response
			} );
		}
		else {
			var json = response.match( /{.*}$/ );

			if ( null !== json && 0 < json.length ) {
				json = $.parseJSON( json[ 0 ] );
			}
			else {
				json = {};
			}

			if ( 'object' != typeof json || $.isEmptyObject( json ) ) {
				if ( window.console ) {
					console.log( response );
				}
				if ( window.console ) {
					console.log( json );
				}

				model.set( {
					progress : 0,
					error_msg: 'There was an issue with the file upload, please try again.'
				} );
				return;
			}

			new_file = {
				id  : json.ID,
				icon: json.thumbnail,
				name: json.post_title,
				link: json.link
			};

			// Remove the file from the upload queue model and trigger an event for the hosting container
			model.trigger( 'destroy', model );
			this.trigger( 'added:files', new_file );
		}
	},

	// This should never be called as plupload intercepts the button click event itself
	invoke: function () {
		return;
	}

} );

