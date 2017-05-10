/*global jQuery, _, Backbone, Marionette, wp */
import template from '~/ui/js/pods-dfv/_src/pick/views/list-item.html';

import {PodsFieldListView, PodsFieldView} from '~/ui/js/pods-dfv/_src/core/pods-field-views';

/**
 *
 */
export const ListItem = PodsFieldView.extend( {
	tagName: 'li',

	className: 'pods-dfv-list-item pods-relationship',

	template: _.template( template ),

	ui: {
		removeButton: '.pods-dfv-list-remove a'
	},

	triggers: {
		'click @ui.removeButton': 'remove:item:click'
	},

	templateContext: function () {
		return {
			ordinal: this.model.collection.indexOf( this.model )
		}
	}

} );

/**
 *  Represents the markup of the container as a whole
 */
export const ListView = PodsFieldListView.extend( {	// Cache the template function for the overall container
	tagName: 'ul',

	className: 'pods-dfv-list pods-relationship',

	childView: ListItem,

	filter: function ( child, index, collection ) {
		return child.attributes.selected;
	},

	onAttach: function () {

		// @todo
		// http://stackoverflow.com/questions/1735372/jquery-sortable-list-scroll-bar-jumps-up-when-sorting/4187833#4187833

		// init sortable
		this.$el.sortable( {
			containment      : 'parent',
			axis             : 'y',
			scrollSensitivity: 40,
			tolerance        : 'pointer',
			opacity          : 0.6
		} );
	}

} );