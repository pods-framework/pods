/*global jQuery, _, Backbone, Mn */
import * as layout_template from './templates/file-upload-layout.html';
import { FileUploadCollection, FileUploadModel } from './models/file-upload-model';
import { FileUploadList } from './views/file-upload-list';
import { FileUploadForm } from './views/file-upload-form';

import { Plupload } from './uploaders/plupload';
import { MediaModal } from './uploaders/media-modal';

const Uploaders = [
	Plupload,
	MediaModal
];

export const FileUpload = Mn.LayoutView.extend( {
	template: _.template( layout_template.default ),

	regions: {
		list     : '.pods-ui-file-list',
		ui_region: '.pods-ui-region', // "Utility" container for uploaders to use
		form     : '.pods-ui-form'
	},

	uploader: {},

	onRender: function () {
		const listView = new FileUploadList( { collection: this.collection, fieldModel: this.model } );
		const formView = new FileUploadForm( { fieldModel: this.model } );

		this.showChildView( 'list', listView );
		this.showChildView( 'form', formView );

		// Setup the uploader and listen for a response event
		this.uploader = this.createUploader();
		this.listenTo( this.uploader, 'added:files', this.onAddedFiles );
	},

	/**
	 * Fired by a remove:file:click trigger in any child view
	 *
	 * @param childView View that was the source of the event
	 */
	onChildviewRemoveFileClick: function ( childView ) {
		this.collection.remove( childView.model );
	},

	/**
	 * Fired by a add:file:click trigger in any child view
	 *
	 * plupload fields should never generate this event as it places a shim over our button and handles the event
	 * internally
	 */
	onChildviewAddFileClick: function () {
		// Invoke the uploader
		this.uploader.invoke();
	},

	/**
	 * Concrete uploader implementations simply need to: this.trigger( 'added:files', new_files )
	 *
	 * @param {Object[]} data An array of model objects to be added
	 */
	onAddedFiles: function ( data ) {
		this.collection.add( data );
	},

	createUploader: function () {
		const options = this.model.get( 'options' );
		const targetUploader = options[ 'file_uploader' ];
		let Uploader;

		for ( let thisUploader of Uploaders ) {
			if ( targetUploader === thisUploader.prototype.fileUploader ) {
				Uploader = thisUploader;
				break;
			}
		}

		if ( Uploader !== undefined ) {
			this.uploader = new Uploader( {
				// We provide regular DOM element for the button
				browse_button: this.getRegion( 'form' ).getEl( '.pods-file-add' ).get(),
				ui_region    : this.getRegion( 'ui_region' ),
				field_options: options
			} );
			return this.uploader;
		}
		else {
			throw "Could not locate file uploader '" + targetUploader + ";";
		}
	}

} );
