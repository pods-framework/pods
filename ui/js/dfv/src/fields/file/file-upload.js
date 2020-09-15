/* global jQuery, _ */
import template from 'dfv/src/fields/file/file-upload-layout.html';

import { PodsDFVFieldLayout } from 'dfv/src/core/pods-field-views';

import { FileUploadCollection } from 'dfv/src/fields/file/file-upload-model';

import { FileUploadList } from 'dfv/src/fields/file/views/file-upload-list';
import { FileUploadForm } from 'dfv/src/fields/file/views/file-upload-form';

import { Plupload } from 'dfv/src/fields/file/uploaders/plupload';
import { MediaModal } from 'dfv/src/fields/file/uploaders/media-modal';

const Uploaders = [
	Plupload,
	MediaModal,
];

const UNLIMITED_FILES = 0;

export const File = PodsDFVFieldLayout.extend( {
	childViewEventPrefix: false, // Disable implicit event listeners in favor of explicit childViewTriggers and childViewEvents

	template: _.template( template ),

	regions: {
		list: '.pods-ui-file-list',
		uiRegion: '.pods-ui-region', // "Utility" container for uploaders to use
		form: '.pods-ui-form',
	},

	childViewEvents: {
		'childview:remove:file:click': 'onChildviewRemoveFileClick',
		'childview:add:file:click': 'onChildviewAddFileClick',
	},

	uploader: {},

	/**
	 *
	 */
	onBeforeRender() {
		if ( this.collection === undefined ) {
			this.collection = new FileUploadCollection( this.fieldItemData );
		}
	},

	onRender() {
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
	 * @param {Object} childView View that was the source of the event
	 */
	onChildviewRemoveFileClick( childView ) {
		this.collection.remove( childView.model );
	},

	/**
	 * Fired by a add:file:click trigger in any child view
	 *
	 * plupload fields should never generate this event, it places a shim over our button and handles the
	 * event internally.  But this event does still come through with plupload fields in some browser
	 * environments for reasons we've been unable to determine.
	 */
	onChildviewAddFileClick() {
		// Invoke the uploader
		if ( 'function' === typeof this.uploader.invoke ) {
			this.uploader.invoke();
		}
	},

	/**
	 * Concrete uploader implementations simply need to: this.trigger( 'added:files', newFiles )
	 *
	 * @param {Object[]} data An array of model objects to be added
	 */
	onAddedFiles( data ) {
		const fieldConfig = this.model.get( 'fieldConfig' );
		const fileLimit = +fieldConfig.file_limit; // Unary plus to force to number
		let filteredModels;

		// Get a copy of the existing collection with the new files added
		const newCollection = this.collection.clone();
		newCollection.add( data );

		// Enforce the file limit option if one is set
		if ( UNLIMITED_FILES === fileLimit ) {
			filteredModels = newCollection.models;
		} else {
			// Number of uploads is limited: keep the last N models, FIFO/queue style
			filteredModels = newCollection.filter( function( model ) {
				return ( newCollection.indexOf( model ) >= newCollection.length - fileLimit );
			} );
		}

		this.collection.reset( filteredModels );
	},

	createUploader() {
		const fieldConfig = this.model.get( 'fieldConfig' );
		const targetUploader = fieldConfig.file_uploader;
		let Uploader;

		jQuery.each( Uploaders, function( index, thisUploader ) {
			if ( targetUploader === thisUploader.prototype.fileUploader ) {
				Uploader = thisUploader;
				return false;
			}
		} );

		if ( Uploader !== undefined ) {
			this.uploader = new Uploader( {
				// We provide regular DOM element for the button
				browseButton: this.getRegion( 'form' ).getEl( '.pods-dfv-list-add' ).get(),
				uiRegion: this.getRegion( 'uiRegion' ),
				fieldConfig,
			} );

			return this.uploader;
		}

		// @todo sprintf type with PodsI18n.__()
		throw `Could not locate file uploader '${ targetUploader }'`;
	},
} );
