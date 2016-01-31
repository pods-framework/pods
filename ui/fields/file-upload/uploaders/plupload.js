/*global window, jQuery, _, Backbone, Mn, wp, plupload, pods_ui, console */
(function ( $, app ) {
	'use strict';

	app.Plupload = app.PodsFileUploader.extend( {
		plupload: {},

		initialize: function () {
			this.field_options.plupload_init.browse_button = this.browse_button;
			this.plupload = new plupload.Uploader( this.field_options.plupload_init );
			this.plupload.init();

			// name, callback, context
			this.plupload.bind( 'FilesAdded', this.onFilesAdded, this );
			this.plupload.bind( 'UploadProgress', this.onUploadProgress, this );
			this.plupload.bind( 'FileUploaded', this.onFilesUploaded, this );
		},

		/**
		 * Fired after files have been selected from the dialog
		 *
		 * @param up
		 * @param files
		 */
		onFilesAdded: function ( up, files ) {
			var new_view,
				file_queue = [];

			// Assemble the data for the file queue
			$.each( files, function ( index, file ) {
				file_queue.push( {
					id      : file.id,
					filename: file.name
				} );
			} );

			// Create a new view based on the collection
			this.queue_collection = new Backbone.Collection( file_queue );
			new_view = new app.FileUploadQueue( { collection: this.queue_collection } );
			new_view.render();  // Generate the HTML, not attached to the DOM yet

			// Reset the region in case any error messages are hanging around from a previous upload
			// and show the new file upload queue
			this.ui_region.reset();
			this.ui_region.show( new_view );

			// Stash a reference for other callbacks
			this.queue_view = new_view;

			up.refresh();
			up.start();
		},

		/**
		 *
		 * @param up
		 * @param file
		 */
		onUploadProgress: function ( up, file ) {
			var progress_bar = this.queue_view.$el.find( '#' + file.id + ' .progress-bar' );
			progress_bar.css( 'width', file.percent + '%' );
		},

		/**
		 *
		 * @param up
		 * @param file
		 * @param resp
		 */
		onFilesUploaded: function ( up, file, resp ) {
			var new_file = [],
				file_div = $( '#' + file.id ),
				progress_bar_column = file_div.find( '.pods-progress' ),
				error_msg_container = file_div.find( '.error' ),
				response = resp.response;

			// Error condition 1
			if ( "Error: " == resp.response.substr( 0, 7 ) ) {
				response = response.substr( 7 );
				if ( window.console ) {
					console.log( response );
				}

				progress_bar_column.hide();
				error_msg_container.text( response );
			}
			// Error condition 2
			else if ( "<e>" == resp.response.substr( 0, 3 ) ) {
				response = $( response ).text(); // Strip tags, text only
				if ( window.console ) {
					console.log( response );
				}

				progress_bar_column.hide();
				error_msg_container.text( response ).show();
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
					error_msg_container.text( 'There was an issue with the file upload, please try again.' );
					return;
				}

				file_div.fadeOut( 800 ).remove();

				new_file = {
					id  : json.ID,
					icon: json.thumbnail,
					name: json.post_title,
					link: json.link
				};

				this.trigger( 'added:files', new_file );
			}
		},

		// This should never be called as plupload intercepts the button click event itself
		invoke: function () {
			return;
		}

	} );

}( jQuery, pods_ui ) );