/*global jQuery, _, Backbone, Mn, wp */
// Note: this is a template-less view
import { PodsFieldListView, PodsFieldView } from '../../../_src/core/pods-field-views';

/**
 *
 */
export const SelectItem = PodsFieldView.extend( {
	tagName: 'option',

	template: false,

	initialize: function () {
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
		'change': 'change:selected'
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

		return {
			'name'           : fieldAttributes.name + '[]',
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
