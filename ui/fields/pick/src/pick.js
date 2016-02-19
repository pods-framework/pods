/*global jQuery, _, Backbone, Mn, wp */
import * as layout_template from './templates/pick-layout.html';

import { RelationshipModel, RelationshipCollection } from './models/relationship-model';
import { CheckboxView } from './views/checkbox-view';
import { SelectView } from './views/select-view';

export const Pick = Mn.LayoutView.extend( {
	template: _.template( layout_template.default ),

	regions: {
		list: '.pods-pick-values'
	},

	onRender: function () {
		var listView = new CheckboxView( { collection: this.collection, fieldModel: this.model } );
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