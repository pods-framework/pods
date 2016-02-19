/*global jQuery, _, Backbone, Mn, wp */
import { PodsFieldListView, PodsFieldView } from '../../../core/pods-field-views';

/**
 *
 */
export const SelectItem = PodsFieldView.extend( {
	tagName: 'option',

	template: false,

	attributes: function () {
		return {
			'value'   : this.model.get( 'id' ),
			'selected': ( this.model.get( 'selected' ) ) ? 'selected="selected"' : ''
		}
	},

	onRender: function () {

	}
} );

/**
 *
 */
export const SelectView = PodsFieldListView.extend( {
	tagName: 'select',

	template: false,

	childView: SelectItem,

	attributes: function () {
		const fieldModel = this.options.fieldModel;
		const fieldAttributes = fieldModel.get( 'attributes' );
		const fieldOptions = fieldModel.get( 'options' );

		return {
			'name'           : fieldAttributes.name + '[]',
			'data-name-clean': fieldAttributes[ 'name_clean' ],
			'id'             : fieldAttributes.id,
			'tabindex'       : '2',
			'multiple'       : fieldOptions[ 'pick_format_type' ]
		}
	}
} );
