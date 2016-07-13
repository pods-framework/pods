/*global jQuery, _, Backbone, Mn, wp */
import * as templateImport from '~/ui/fields-mv/_src/pick/pick-layout.html';
const template = templateImport.default || templateImport; // Currently two differnt style string importers for build and test

import {IframeFrame} from '~/ui/fields-mv/_src/core/iframe-frame';
//import { RelationshipModel, RelationshipCollection } from './models/relationship-model';
import {PickViewSelector} from '~/ui/fields-mv/_src/pick/views/view-selector';
import {CheckboxView} from '~/ui/fields-mv/_src/pick/views/checkbox-view';
import {SelectView} from '~/ui/fields-mv/_src/pick/views/select-view';
import {FlexView} from '~/ui/fields-mv/_src/pick/views/flex-view';
import {AddNew} from '~/ui/fields-mv/_src/pick/views/add-new';

const AJAX_ADD_NEW_ACTION = 'pods_relationship_popup';

/**
 *
 */
export const Pick = Mn.LayoutView.extend( {
	template: _.template( template ),

	regions: {
		viewSelector: '.view-selector',
		list        : '.pods-pick-values',
		addNew      : '.pods-ui-add-new'
	},

	onRender: function () {
		const list = new CheckboxView( { collection: this.collection, fieldModel: this.model } );
		const addNew = new AddNew( { fieldModel: this.model } );

		this.showChildView( 'viewSelector', new PickViewSelector( {} ) );
		this.showChildView( 'list', list );
		this.showChildView( 'addNew', addNew );
	},

	/** "Remove" in flex view just toggles an item's selected attribute
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
	 *
	 * @param childView
	 */
	onChildviewAddNewClick: function ( childView ) {
		const options = this.model.get( 'options' );

		const modalFrame = new IframeFrame( {
			title: 'The Title',
			src  : options[ 'iframe_src' ]
		} );
		modalFrame.modal.open();
	},

	addNewSuccess: function ( response ) {
		console.log( response );
	},

	/**
	 * Fun for testing
	 */
	onChildviewCheckboxViewClick: function ( childView ) {
		const view = new CheckboxView( { collection: this.collection, fieldModel: this.model } );
		this.showChildView( 'list', view );
	},

	onChildviewSelectViewClick: function ( childView ) {
		const view = new SelectView( { collection: this.collection, fieldModel: this.model } );
		this.showChildView( 'list', view );
	},

	onChildviewFlexViewClick: function ( childView ) {
		const view = new FlexView( { collection: this.collection, fieldModel: this.model } );
		this.showChildView( 'list', view );
	}

} );