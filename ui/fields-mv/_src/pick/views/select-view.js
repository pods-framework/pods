/*global jQuery, _, Backbone, Marionette, wp */
// Note: this is a template-less view
import {PodsFieldListView, PodsFieldView} from '~/ui/fields-mv/_src/core/pods-field-views';

/**
 * @extends Backbone.View
 */
export const SelectItem = PodsFieldView.extend( {
	tagName: 'option',

	template: false,

	initialize: function ( options ) {
		this.$el.val( this.model.get( 'id' ) );

		this.$el.html( this.model.get( 'name' ) );

		if ( this.model.get( 'selected' ) ) {
			this.$el.prop( 'selected', 'selected' );
		}
	}
} );

/**
 *
 */
export const SelectView = PodsFieldListView.extend( {
	tagName: 'select',

	childView: SelectItem,

	triggers: {
		"change": {
			event          : "change:selected",
			stopPropagation: false
		}
	},

	attributes: function () {

		/**
		 * @param {string} fieldAttributes.name
		 * @param {string} fieldAttributes.class
		 * @param {string} fieldAttributes.name_clean
		 * @param {string} fieldAttributes.id
		 *
		 * @param {string} fieldOptions.pick_format_type 'single' or 'multi'
		 */
		const fieldModel = this.options.fieldModel;
		const fieldAttributes = fieldModel.get( 'attributes' );
		const fieldOptions = fieldModel.get( 'options' );

		let name = fieldAttributes.name;
		if ( fieldOptions.pick_format_type === 'multi' ) {
			name = name + '[]';
		}
		return {
			'name'           : name,
			'class'          : fieldAttributes.class,
			'data-name-clean': fieldAttributes.name_clean,
			'id'             : fieldAttributes.id,
			'tabindex'       : '2',
			'multiple'       : ( fieldOptions.pick_format_type === 'multi' )
		};
	},

	onChangeSelected: function () {
		this.collection.setSelected( this.$el.val() );
	}

} );

/**
 * @extends Backbone.View
 */
export const SelectGroupItem = SelectView.extend( {
	tagName: 'optgroup',

	childView: SelectItem,

	initialize: function ( options ) {
		this.$el.prop( 'label', this.model.get( 'name' ) );
	}
} );

/**
 * @extends Backbone.View
 */
export const SelectGroupView = SelectView.extend( {
	childView: SelectGroupItem
} );
