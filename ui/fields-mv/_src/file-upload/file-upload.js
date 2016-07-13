/*global jQuery, _, Backbone, Mn */
import * as templateImport from '~/ui/fields-mv/_src/file-upload/file-upload-layout.html';
const template = templateImport.default || templateImport; // Currently two differnt style string importers for build and test

import { FileUploadCollection, FileUploadModel } from '~/ui/fields-mv/_src/file-upload/models/file-upload-model';
import { FileUploadList } from '~/ui/fields-mv/_src/file-upload/views/file-upload-list';
import { FileUploadForm } from '~/ui/fields-mv/_src/file-upload/views/file-upload-form';

import { Plupload } from '~/ui/fields-mv/_src/file-upload/uploaders/plupload';
import { MediaModal } from '~/ui/fields-mv/_src/file-upload/uploaders/media-modal';

const Uploaders = [
	Plupload,
	MediaModal
];

const UNLIMITED_FILES = 0;

export const FileUpload = Mn.LayoutView.extend( {
	template: _.template( template ),

	regions: {
		list    : '.pods-ui-file-list',
		uiRegion: '.pods-ui-region', // "Utility" container for uploaders to use
		form    : '.pods-ui-form'
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
	 * Concrete uploader implementations simply need to: this.trigger( 'added:files', newFiles )
	 *
	 * @param {Object[]} data An array of model objects to be added
	 */
	onAddedFiles: function ( data ) {
		const options = this.model.get( 'options' );
		const fileLimit = +options[ 'file_limit' ]; // Unary plus to force to number
		let newCollection, filteredModels;

		// Get a copy of the existing collection with the new files added
		newCollection = this.collection.clone();
		newCollection.add( data );

		// Enforce the file limit option if one is set
		if ( UNLIMITED_FILES === fileLimit ) {
			filteredModels = newCollection.models;
		}
		else {
			// Number of uploads is limited: keep the last N models, FIFO/queue style
			filteredModels = newCollection.filter( function ( model ) {
				return ( newCollection.indexOf( model ) >= newCollection.length - fileLimit );
			} );
		}

		this.collection.reset( filteredModels );
	},

	createUploader: function () {
		const options = this.model.get( 'options' );
		const targetUploader = options[ 'file_uploader' ];
		let Uploader;

		jQuery.each( Uploaders, function ( index, thisUploader ) {
			if ( targetUploader === thisUploader.prototype.fileUploader ) {
				Uploader = thisUploader;
				return false;
			}
		} );

		if ( Uploader !== undefined ) {
			this.uploader = new Uploader( {
				// We provide regular DOM element for the button
				browseButton: this.getRegion( 'form' ).getEl( '.pods-flex-add' ).get(),
				uiRegion    : this.getRegion( 'uiRegion' ),
				fieldOptions: options
			} );
			return this.uploader;
		}
		else {
			throw "Could not locate file uploader '" + targetUploader + "'";
		}
	}
} );
