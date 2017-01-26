/*global jQuery, _, Backbone, Marionette, wp */
import template from '~/ui/js/pods-dfv/_src/pick/pick-layout.html';

import {PodsDFVFieldLayout} from '~/ui/js/pods-dfv/_src/core/pods-field-views';
import {IframeFrame} from '~/ui/js/pods-dfv/_src/core/iframe-frame';

import {RelationshipCollection} from '~/ui/js/pods-dfv/_src/pick/relationship-model';
import {PickFieldModel} from '~/ui/js/pods-dfv/_src/pick/pick-field-model';

import {RadioView} from '~/ui/js/pods-dfv/_src/pick/views/radio-view';
import {CheckboxView} from '~/ui/js/pods-dfv/_src/pick/views/checkbox-view';
import {SelectView} from '~/ui/js/pods-dfv/_src/pick/views/select-view';
import {FlexView} from '~/ui/js/pods-dfv/_src/pick/views/flex-view';
import {AddNew} from '~/ui/js/pods-dfv/_src/pick/views/add-new';

const AJAX_ADD_NEW_ACTION = 'pods_relationship_popup';

const views = {
	'checkbox': CheckboxView,
	'select'  : SelectView,
	'select2' : SelectView,  // SelectView handles select2 as well
	'radio'   : RadioView,
	'flexible': FlexView
};

/**
 * @extends Backbone.View
 */
export const Pick = PodsDFVFieldLayout.extend( {
	template: _.template( template ),

	regions: {
		list  : '.pods-pick-values',
		addNew: '.pods-ui-add-new'
	},

	/**
	 *
	 */
	onBeforeRender: function () {
		if ( this.collection === undefined ) {
			this.collection = new RelationshipCollection( this.fieldData );
		}
	},

	/**
	 *
	 */
	onRender: function () {
		let viewName, View, list, addNew;

		this.fieldConfig = new PickFieldModel( this.model.get( 'fieldConfig' ) );

		// Setup the view to be used
		viewName = this.fieldConfig.get( 'view_name' );
		if ( views[ viewName ] === undefined ) {
			throw new Error( `Invalid view name "${viewName}"` );
		}
		View = views[ viewName ];
		list = new View( { collection: this.collection, fieldModel: this.model } );
		this.showChildView( 'list', list );

		// Show Add New?
		if ( this.fieldConfig.get( 'iframe_src' ) !== '' ) {
			addNew = new AddNew( { fieldModel: this.model } );
			this.showChildView( 'addNew', addNew );
		}
	},

	/**
	 *"Remove" in flex view just toggles an item's selected attribute
	 *
	 * @param childView
	 * @param args
	 */
	onChildviewRemoveItemClick: function ( childView, args ) {
		const list = this.childView( 'list' );

		args.model.toggleSelected();
		list.render();
	},

	/**
	 * @param childView
	 */
	onChildviewAddNewClick: function ( childView ) {
		const fieldConfig = this.model.get( 'fieldConfig' );

		const modalFrame = new IframeFrame( {
			title: 'The Title',
			src  : fieldConfig.iframe_src
		} );
		modalFrame.modal.open();
	},

	/**
	 * @param response
	 */
	addNewSuccess: function ( response ) {
		console.log( response );
	}

} );