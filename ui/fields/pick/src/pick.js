/*global jQuery, _, Backbone, Mn */
const $ = jQuery;

import * as layout_template from './templates/pick-layout.html';

import { RelationshipModel, RelationshipCollection } from './models/relationship-model';
import { CheckboxList } from './views/checkbox-view';

export const Pick = Mn.LayoutView.extend( {
	template: _.template( layout_template.default ),

	regions: {
		list: '.pods-pick-checkbox'
	},

	initialize: function () {
		// @todo: abstract this out.  All fields need access to the field meta and individual views shouldn't have to
		// worry about marshalling that data around.
		this.field_meta = this.getOption( 'field_meta' );

		this.collection = new RelationshipCollection( this.getOption( 'model_data' ), this.field_meta );
		this.model = new RelationshipModel();
	},

	onRender: function () {
		// @todo: abstract this out.  All fields need access to the field meta and individual views shouldn't have to
		// worry about marshalling that data around.
		var listView = new CheckboxList( { collection: this.collection, field_meta: this.field_meta } );

		this.showChildView( 'list', listView );
	}

} );