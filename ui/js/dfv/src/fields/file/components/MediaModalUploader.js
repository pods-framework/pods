/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import FileUploader from './FileUploader';

/**
 * MediaModal uploader - Uses WordPress media library modal
 */
class MediaModalUploader extends FileUploader {
	constructor( config ) {
		super( config );
		this.mediaFrame = null;
		this.setupMediaFrame();
	}

	static getType() {
		return 'attachment';
	}

	setupMediaFrame() {
		const {
			fileModalTitle,
			fileModalAddButton,
			fileLimit,
			limitTypes,
			limitExtensions,
			filePostId,
			fileAttachmentTab,
		} = this.config;

		// Set up mime type filters
		if ( wp.Uploader.defaults.filters.mime_types === undefined ) {
			wp.Uploader.defaults.filters.mime_types = [ {
				title: __( 'Allowed Files', 'pods' ),
				extensions: '*',
			} ];
		}

		const defaultExt = wp.Uploader.defaults.filters.mime_types[ 0 ].extensions;

		if ( limitExtensions ) {
			wp.Uploader.defaults.filters.mime_types[ 0 ].extensions = limitExtensions;
		}

		// Create the media frame
		this.mediaFrame = wp.media( {
			title: fileModalTitle || __( 'Select or Upload Files', 'pods' ),
			multiple: ( 1 !== parseInt( fileLimit, 10 ) ),
			library: {
				type: limitTypes,
				uploadedTo: filePostId,
			},
			button: {
				text: fileModalAddButton || __( 'Add File', 'pods' ),
			},
		} );

		// Handle selection
		this.mediaFrame.on( 'select', () => {
			const selection = this.mediaFrame.state().get( 'selection' );
			const newFiles = [];

			if ( ! selection ) {
				return;
			}

			// Loop through selected files
			selection.each( ( attachment ) => {
				const sizes = attachment.attributes.sizes;
				let attachmentThumbnail = attachment.attributes.icon;

				if ( sizes !== undefined ) {
					if ( sizes.thumbnail !== undefined && sizes.thumbnail.url !== undefined ) {
						attachmentThumbnail = sizes.thumbnail.url;
					} else if ( sizes.full !== undefined && sizes.full.url !== undefined ) {
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

			this.onFilesAdded( newFiles );
		} );

		wp.Uploader.defaults.filters.mime_types[ 0 ].extensions = defaultExt;
		this.fileAttachmentTab = fileAttachmentTab;
	}

	open() {
		if ( this.mediaFrame ) {
			this.mediaFrame.open();

			if ( this.fileAttachmentTab ) {
				this.mediaFrame.content.mode( this.fileAttachmentTab );
			}
		}
	}

	cleanup() {
		if ( this.mediaFrame ) {
			this.mediaFrame.off( 'select' );
			this.mediaFrame = null;
		}
	}
}

export default MediaModalUploader;

