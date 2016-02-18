/*global jQuery, _, Backbone, Mn */
const $ = jQuery;

import * as layout_template from './templates/pick-layout.html';

import { RelationshipModel, RelationshipCollection } from './models/relationship-model';
import { CheckboxView } from './views/checkbox-view';
import { SelectView } from './views/select-view';

export const Pick = Mn.LayoutView.extend( {
	template: _.template( layout_template.default ),

	regions: {
		list: '.pods-pick-values'
	},

	initialize: function () {
		// @todo: abstract this out.  All fields need access to the field meta and individual views shouldn't have to
		// worry about marshalling that data around.
		this.field_meta = this.getOption( 'field_meta' );
	},

	onRender: function () {
		// @todo: abstract this out.  All fields need access to the field meta and individual views shouldn't have to
		// worry about marshalling that data around.
		var listView = new CheckboxView( { collection: this.collection, field_meta: this.field_meta } );

		this.showChildView( 'list', listView );
	},

	/**
	 * Fired by a chechbox:click trigger in any child view
	 *
	 * @param childView View that was the source of the event
	 */
	onChildviewCheckboxClick: function ( childView ) {
		childView.model.toggle_selected();
	}

} );