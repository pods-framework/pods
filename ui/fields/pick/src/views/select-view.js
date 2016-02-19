/*global jQuery, _, Backbone, Mn, wp */
/**
 *
 */
export const SelectItem = Mn.ItemView.extend( {
	tagName: 'option',

	template: false,

	attributes: function () {
		return {
			'value'   : this.model.get( 'id' ),
			'selected': ( this.model.get( 'selected' ) ) ? 'selected="selected"' : ''
		}
	},

	onRender: function() {


	}
} );

/**
 *
 */
export const SelectView = Mn.CollectionView.extend( {
	tagName: 'select',

	template: false,

	childView: SelectItem,

	attributes: function () {
		const field_meta = this.options.field_meta;
		const field_attributes = field_meta.field_attributes;
		const field_options = field_meta.field_options;

		return {
			'name'           : field_attributes.name + '[]',
			'data-name-clean': field_attributes.name_clean,
			'id'             : field_attributes.id,
			'tabindex'       : '2',
			'multiple'       : field_options.pick_format_type
		}
	},

	initialize: function ( options ) {
		this.childViewOptions = options.field_meta;
		this.field_attributes = options.field_meta.field_attributes;
	}

} );
