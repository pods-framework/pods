/*global window, jQuery, _, Backbone, Mn, wp, plupload, pods_ui */
(function ( $, app ) {
	'use strict';

	app.Plupload = app.PodsFileUploader.extend( {
		plupload: {},

		/**
		 * plupload needs references to a couple of elements already in the DOM
		 */
		initialize: function () {
			this.listenTo( this.main_layout, 'attached:view', this.onAttachedView );
		},

		onAttachedView: function ( layoutView ) {
			var button = layoutView.$el.find( '.pods-file-add' );

			if ( button.length > 0 ) {
				this.field_options.plupload_init.browse_button = button[ 0 ];
				this.plupload = new plupload.Uploader( this.field_options.plupload_init );
				this.plupload.init();

				// name, callback, context
				this.plupload.bind( 'FilesAdded', this.onFilesAdded, this );
				this.plupload.bind( 'UploadProgress', this.onUploadProgress, this );
				this.plupload.bind( 'FileUploaded', this.onFilesUploaded, this );
			}
		},

		/**
		 *
		 * @param up
		 * @param files
		 */
		onFilesAdded: function ( up, files ) {
			var file_queue = [];

			$.each( files, function ( index, file ) {
				file_queue.push( {
					id      : file.id,
					filename: file.name
				} );
			} );

			this.trigger( 'added:queue', file_queue );
			up.refresh();
			up.start();
		},

		/**
		 *
		 * @param up
		 * @param file
		 */
		onUploadProgress: function ( up, file ) {
			//var progress_bar = $( '#' + file.id ).find( '.progress-bar' );
			//progress_bar.css( 'width', file.percent + '%' );

			// @todo
			console.log( file.name, ': ', file.percent );
		},

		/**
		 *
		 * @param up
		 * @param file
		 * @param resp
		 */
		onFilesUploaded: function ( up, file, resp ) {
			var new_file = [];
			var file_div = $( '#' + file.id ),
				response = resp.response;

			if ( "Error: " == resp.response.substr( 0, 7 ) ) {
				response = response.substr( 7 );
				if ( window.console ) {
					console.log( response );
				}
			}
			else if ( "<e>" == resp.response.substr( 0, 3 ) ) {
				response = response.substr( 3 );
				if ( window.console ) {
					console.log( response );
				}
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
					//file_div.append( 'There was an issue with the file upload, please try again.' );
					return;
				}

				file_div.fadeOut( 800, function () {
					//list_pods_form_ui_pods_meta_single_file_1.show();

					if ( $( this ).parent().children().length == 1 ) {
						//$( '#pods-form-ui-pods-meta-single-file-1 ul.pods-files-queue' ).hide();
					}

					//$( this ).remove();
				} );

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