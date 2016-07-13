/*global jQuery, _, Backbone, Mn, wp */
import * as templateImport from '~/ui/fields-mv/_src/pick/views/flex-item.html';
const template = templateImport.default || templateImport; // Currently two differnt style string importers for build and test

import {PodsFieldListView, PodsFieldView} from '~/ui/fields-mv/_src/core/pods-field-views';

/**
 *
 */
export const FlexItem = PodsFieldView.extend( {
	tagName: 'li',

	className: 'pods-flex-item pods-relationship',

	template: _.template( template ),

	ui: {
		removeButton: '.pods-flex-remove a'
	},

	triggers: {
		'click @ui.removeButton': 'remove:item:click'
	},

	templateHelpers: function () {
		return {
			ordinal: this.model.collection.indexOf( this.model )
		}
	}

} );

/**
 *  Represents the markup of the container as a whole
 */
export const FlexView = PodsFieldListView.extend( {	// Cache the template function for the overall container
	tagName: 'ul',

	className: 'pods-flex-list pods-relationship',

	childView: FlexItem,

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