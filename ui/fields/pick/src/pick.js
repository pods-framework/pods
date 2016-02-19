/*global jQuery, _, Backbone, Mn, wp */
import * as layout_template from './templates/pick-layout.html';

import { RelationshipModel, RelationshipCollection } from './models/relationship-model';
import { PickViewSelector } from './views/view-selector';
import { CheckboxView } from './views/checkbox-view';
import { SelectView } from './views/select-view';

export const Pick = Mn.LayoutView.extend( {
	template: _.template( layout_template.default ),

	regions: {
		viewSelector: '.view-selector',
		list       : '.pods-pick-values'
	},

	onRender: function () {
		const view = new CheckboxView( { collection: this.collection, fieldModel: this.model } );
		this.showChildView( 'list', view );
		this.showChildView( 'viewSelector', new PickViewSelector( {} ) );
	},

	/**
	 * Fired by a chechbox:click trigger in any child view
	 *
	 * @param childView View that was the source of the event
	 */
	onChildviewToggleSelected: function ( childView ) {
		childView.model.toggleSelected();
		console.log( 'toggle' );
	},

	onChildviewCheckboxViewClick: function( childView ) {
		const view = new CheckboxView( { collection: this.collection, fieldModel: this.model } );
		this.showChildView( 'list', view );
	},

	onChildviewSelectViewClick: function( childView ) {
		const view = new SelectView( { collection: this.collection, fieldModel: this.model } );
		this.showChildView( 'list', view );
	}
} );