/*global jQuery, _, Backbone, Mn, wp */
import { PodsFileUploader } from './pods-file-uploader';

export const MediaModal = PodsFileUploader.extend( {
	mediaObject: {},

	fileUploader: 'attachment',

	invoke: function () {

		if ( wp.Uploader.defaults.filters.mime_types === undefined ) {
			wp.Uploader.defaults.filters.mime_types = [ { title: pods_localized_strings.__allowed_files, extensions: '*' } ];
		}

		let defaultExt = wp.Uploader.defaults.filters.mime_types[ 0 ].extensions;

		wp.Uploader.defaults.filters.mime_types[ 0 ].extensions = this.fieldOptions[ 'limit_extensions' ];

		// set our settings
		this.mediaObject = wp.media( {
			title   : this.fieldOptions[ 'file_modal_title' ],
			multiple: ( 1 != this.fieldOptions[ 'file_limit' ] ),
			library : {
				type: this.fieldOptions[ 'limit_types' ]
			},
			// Customize the submit button.
			button  : {
				// Set the text of the button.
				text: this.fieldOptions[ 'file_modal_add_button' ]
			}
		} );

		// One-shot callback ( event, callback, context )
		this.mediaObject.once( 'select', this.onMediaSelect, this );

		// open the frame
		this.mediaObject.open();
		this.mediaObject.content.mode( this.fieldOptions[ 'file_attachment_tab' ] );

		// Reset the allowed file extensions
		wp.Uploader.defaults.filters.mime_types[ 0 ].extensions = defaultExt;
	},

	onMediaSelect: function () {
		const selection = this.mediaObject.state().get( 'selection' );
		let newFiles = [];

		if ( !selection ) {
			return;
		}

		// loop through the selected files
		selection.each( function ( attachment ) {
			const sizes = attachment.attributes.sizes;
			let attachmentThumbnail;

			// by default use the generic icon
			attachmentThumbnail = attachment.attributes.icon;

			// only thumbnails have sizes which is what we're on the hunt for
			if ( sizes !== undefined ) {
				// Get thumbnail if it exists
				if ( sizes.thumbnail !== undefined && sizes.thumbnail.url !== undefined ) {
					attachmentThumbnail = sizes.thumbnail.url;
				}// If thumbnail doesn't exist, get full because this is a small image
				else if ( sizes.full !== undefined && sizes.full.url !== undefined ) {
					attachmentThumbnail = sizes.full.url;
				}
			}

			newFiles.push( {
				id       : attachment.attributes.id,
				icon     : attachmentThumbnail,
				name     : attachment.attributes.title,
				edit_link: attachment.attributes.editLink,
				link     : attachment.attributes.link,
				download : attachment.attributes.url
			} );
		} );

		// Fire an event with an array of models to be added
		this.trigger( 'added:files', newFiles );
	}

} );
