import { __ } from '@wordpress/i18n';

import { PodsFileUploader } from 'dfv/src/fields/file/uploaders/pods-file-uploader';

export const MediaModal = PodsFileUploader.extend( {
	mediaObject: {},

	fileUploader: 'attachment',

	invoke() {
		if ( wp.Uploader.defaults.filters.mime_types === undefined ) {
			wp.Uploader.defaults.filters.mime_types = [ {
				title: __( 'Allowed Files', 'pods' ),
				extensions: '*',
			} ];
		}

		const defaultExt = wp.Uploader.defaults.filters.mime_types[ 0 ].extensions;

		// eslint-disable-next-line camelcase
		if ( this.fieldConfig?.limit_extensions ) {
			wp.Uploader.defaults.filters.mime_types[ 0 ].extensions = this.fieldConfig.limit_extensions;
		}

		// set our settings
		this.mediaObject = wp.media( {
			title: this.fieldConfig.file_modal_title,
			multiple: ( 1 !== parseInt( this.fieldConfig.file_limit, 10 ) ),
			library: {
				type: this.fieldConfig.limit_types,
				uploadedTo: this.fieldConfig?.file_post_id,
			},
			// Customize the submit button.
			button: {
				// Set the text of the button.
				text: this.fieldConfig.file_modal_add_button,
			},
		} );

		// One-shot callback ( event, callback, context )
		this.mediaObject.once( 'select', this.onMediaSelect, this );

		// open the frame
		this.mediaObject.open();
		this.mediaObject.content.mode( this.fieldConfig.file_attachment_tab );

		// Reset the allowed file extensions
		wp.Uploader.defaults.filters.mime_types[ 0 ].extensions = defaultExt;
	},

	onMediaSelect() {
		const selection = this.mediaObject.state().get( 'selection' );
		const newFiles = [];

		if ( ! selection ) {
			return;
		}

		// loop through the selected files
		selection.each( function( attachment ) {
			const sizes = attachment.attributes.sizes;
			let attachmentThumbnail;

			// by default use the generic icon
			attachmentThumbnail = attachment.attributes.icon;

			// only thumbnails have sizes which is what we're on the hunt for
			if ( sizes !== undefined ) {
				// Get thumbnail if it exists
				if ( sizes.thumbnail !== undefined && sizes.thumbnail.url !== undefined ) {
					attachmentThumbnail = sizes.thumbnail.url;
				} else if ( sizes.full !== undefined && sizes.full.url !== undefined ) {
					// If thumbnail doesn't exist, get full because this is a small image
					attachmentThumbnail = sizes.full.url;
				}
			}

			newFiles.push( {
				id: attachment.attributes.id,
				icon: attachmentThumbnail,
				name: attachment.attributes.title,
				edit_link: attachment.attributes.editLink,
				link: attachment.attributes.link,
				download: attachment.attributes.url,
			} );
		} );

		// Fire an event with an array of models to be added
		this.trigger( 'added:files', newFiles );
	},
} );
