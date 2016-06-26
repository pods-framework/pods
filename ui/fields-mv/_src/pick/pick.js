/*global jQuery, _, Backbone, Mn, wp */
import * as template from './pick-layout.html';

import {IframeFrame} from '../core/iframe-frame';
//import { RelationshipModel, RelationshipCollection } from './models/relationship-model';
import {PickViewSelector} from './views/view-selector';
import {CheckboxView} from './views/checkbox-view';
import {SelectView} from './views/select-view';
import {FlexView} from './views/flex-view';
import {AddNew} from './views/add-new';

const AJAX_ADD_NEW_ACTION = 'pods_relationship_popup';

/**
 *
 */
export const Pick = Mn.LayoutView.extend( {
	template: _.template( template.default ),

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
			title: pods_localized_strings.__the_title,
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