/*global jQuery, _, Backbone, Mn */
const $ = jQuery;

import * as checkbox_item from '../templates/checkbox-item.html';

/**
 * Represents the markup of a single row of relationship data
 */
export const CheckboxItem = Mn.LayoutView.extend( {
	tagName: 'li',

	className: 'pods-pick',

	ui: {
		checkbox: 'input.pods-form-ui-field-type-pick'
	},

	template: _.template( checkbox_item.default ),

	triggers: {
		'click @ui.checkbox': 'checkbox:click'
	},

	modelEvents: {
		'change': 'modelChanged'
	},

	templateHelpers: function () {
		return {
			ordinal: this.model.collection.indexOf( this.model )
		}
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
	},

	modelChanged: function () {
		this.render();
	},

} );

/**
 *  Represents the markup of the container as a whole
 */
export const CheckboxView = Mn.CollectionView.extend( {
	tagName: 'ul',

	childView: CheckboxItem,

	initialize: function ( options ) {
		this.childViewOptions = options.field_meta;
	}

} );