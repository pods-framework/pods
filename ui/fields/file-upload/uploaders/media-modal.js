/*global jQuery, _, Backbone, Mn, wp, pods_ui */
(function ( $, app ) {
	'use strict';

	app.MediaModal = {

		go: function ( field_options, file_collection ) {
			var media_object;

			if ( wp.Uploader.defaults.filters.mime_types === undefined ) {
				wp.Uploader.defaults.filters.mime_types = [ { title: 'Allowed Files', extensions: '*' } ];
			}

			var default_ext = wp.Uploader.defaults.filters.mime_types[ 0 ].extensions;

			wp.Uploader.defaults.filters.mime_types[ 0 ].extensions = field_options[ 'limit_extensions' ];

			// set our settings
			media_object = wp.media( {
				title   : field_options[ 'file_modal_title' ],
				multiple: ( 1 != field_options[ 'file_limit' ] ),
				library : {
					type: field_options[ 'limit_types' ]
				},
				// Customize the submit button.
				button  : {
					// Set the text of the button.
					text: field_options[ 'file_modal_add_button' ]
				}
			} );

			media_object.once( 'select', function () {
				var selection = media_object.state().get( 'selection' );
				if ( !selection ) {
					return;
				}

				// loop through the selected files
				selection.each( function ( attachment ) {
					var attachment_thumbnail;
					var sizes = attachment.attributes.sizes;

					// by default use the generic icon
					attachment_thumbnail = attachment.attributes.icon;

					// only thumbnails have sizes which is what we're on the hunt for
					if ( sizes !== undefined ) {
						// Get thumbnail if it exists
						if ( sizes.thumbnail !== undefined && sizes.thumbnail.url !== undefined ) {
							attachment_thumbnail = sizes.thumbnail.url;
						}// If thumbnail doesn't exist, get full because this is a small image
						else if ( sizes.full !== undefined && sizes.full.url !== undefined ) {
							attachment_thumbnail = sizes.full.url;
						}
					}

					file_collection.add( {
						id      : attachment[ 'id' ],
						icon    : attachment_thumbnail,
						name    : attachment.attributes.title,
						filename: attachment.filename,
						link    : attachment.attributes.url
					} );
				} );

			} );

			// open the frame
			media_object.open();
			media_object.content.mode( field_options[ 'file_attachment_tab' ] );

			// Reset the allowed file extensions
			wp.Uploader.defaults.filters.mime_types[ 0 ].extensions = default_ext;
		}
	};

}( jQuery, pods_ui ) );