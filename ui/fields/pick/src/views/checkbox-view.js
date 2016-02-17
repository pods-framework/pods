/*global jQuery, _, Backbone, Mn */
const $ = jQuery;

import * as checkbox_item from '../templates/checkbox-item.html';

/**
 * Represents the markup of a single row of relationship data
 */
export const CheckboxItem = Mn.LayoutView.extend( {
	tagName: 'li',

	className: 'pods-pick',

	ui: {},

	template: _.template( checkbox_item.default ),

	triggers: {
		'click @ui.checkbox': 'checkbox:click'
	},

	/**
	 * @todo: abstract this out.  All fields need access to the field meta and individual views shouldn't have to
	 * worry about marshalling that data around.
	 *
	 * The return value here is what will be made available to the template
	 */
	serializeData: function () {
		var data = this.model.toJSON();

		data.attr = this.options[ 'field_attributes' ];
		data.options = this.options[ 'field_options' ];

		return data;
	}

} );

/**
 *  Represents the markup of the container as a whole
 */
export const CheckboxList = Mn.CollectionView.extend( {
	tagName: 'ul',

	childView: CheckboxItem,

	initialize: function ( options ) {
		this.childViewOptions = options.field_meta;
	}

} );