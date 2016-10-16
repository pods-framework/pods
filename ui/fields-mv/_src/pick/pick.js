/*global jQuery, _, Backbone, Marionette, wp */
import template from '~/ui/fields-mv/_src/pick/pick-layout.html';

import {PickFieldModel} from '~/ui/fields-mv/_src/pick/pick-field-model';
import {RadioView} from '~/ui/fields-mv/_src/pick/views/radio-view';
import {CheckboxView} from '~/ui/fields-mv/_src/pick/views/checkbox-view';
import {SelectView} from '~/ui/fields-mv/_src/pick/views/select-view';
import {Select2View} from '~/ui/fields-mv/_src/pick/views/select2-view';
import {FlexView} from '~/ui/fields-mv/_src/pick/views/flex-view';
import {AddNew} from '~/ui/fields-mv/_src/pick/views/add-new';

import {IframeFrame} from '~/ui/fields-mv/_src/core/iframe-frame';

const AJAX_ADD_NEW_ACTION = 'pods_relationship_popup';

const views = {
	'checkbox': CheckboxView,
	'select'  : SelectView,
	'radio'   : RadioView,
	'select2' : Select2View,
	'flexible': FlexView
};

/**
 * @extends Backbone.View
 */
export const Pick = Marionette.LayoutView.extend( {
	template: _.template( template ),

	regions: {
		list  : '.pods-pick-values',
		addNew: '.pods-ui-add-new'
	},

	/**
	 *
	 */
	onRender: function () {
		let viewName, View, list, addNew;

		this.fieldOptions = new PickFieldModel( this.model.get( 'options' ) );

		// Setup the view to be used
		viewName = this.fieldOptions.get( 'view_name' );
		if( views[ viewName ] === undefined ) {
			throw new Error( `Invalid view name "${viewName}"` );
		}
		View = views[ viewName ];
		list = new View( { collection: this.collection, fieldModel: this.model } );
		this.showChildView( 'list', list );

		// Show Add New?
		if ( this.fieldOptions.get( 'iframe_src' ) !== '' ) {
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
		const list = this.getChildView( 'list' );

		args.model.toggleSelected();
		list.render();
	},

	/**
	 * @param childView
	 */
	onChildviewAddNewClick: function ( childView ) {
		const options = this.model.get( 'options' );

		const modalFrame = new IframeFrame( {
			title: 'The Title',
			src  : options.iframe_src
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