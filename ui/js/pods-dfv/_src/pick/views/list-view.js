/*global jQuery, _, Backbone, PodsMn, wp */
import template from 'pods-dfv/_src/pick/views/list-item.html';

import { PodsFieldListView, PodsFieldView } from 'pods-dfv/_src/core/pods-field-views';

/**
 *
 */
export const ListItem = PodsFieldView.extend( {
	childViewEventPrefix: false, // Disable implicit event listeners in favor of explicit childViewTriggers and childViewEvents

	tagName: 'li',

	className: 'pods-dfv-list-item pods-relationship',

	template: _.template( template ),

	ui: {
		removeButton: '.pods-dfv-list-remove a',
		editButton: '.pods-dfv-list-edit a'
	},

	triggers: {
		'click @ui.removeButton': 'remove:item:click',
		'click @ui.editButton': 'edit:item:click'
	},

	templateContext: function () {
		return {
			ordinal: this.model.collection.indexOf( this.model )
		};
	}

} );

/**
 *  Represents the markup of the container as a whole
 */
export const ListView = PodsFieldListView.extend( {	// Cache the template function for the overall container
	childViewEventPrefix: false, // Disable implicit event listeners in favor of explicit childViewTriggers and childViewEvents

	tagName: 'ul',

	className: 'pods-dfv-list pods-relationship',

	childView: ListItem,

	// Pass these up the containment chain
	childViewTriggers: {
		'remove:item:click': 'childview:remove:item:click',
		'edit:item:click': 'childview:edit:item:click'
	},

	filter: function ( child, index, collection ) {
		return child.attributes.selected;
	},

	onAttach: function () {
		const fieldConfig = this.options.fieldModel.get( 'fieldConfig' );

		// @todo
		// http://stackoverflow.com/questions/1735372/jquery-sortable-list-scroll-bar-jumps-up-when-sorting/4187833#4187833

		if ( 1 !== fieldConfig[ 'pick_limit' ] ) {
			// init sortable
			this.$el.sortable( {
				containment: 'parent',
				axis: 'y',
				scrollSensitivity: 40,
				tolerance: 'pointer',
				opacity: 0.6
			} );
		}
	}

} );
