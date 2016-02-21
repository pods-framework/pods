/*global jQuery, _, Backbone, Mn */

/**
 *
 */
export const PodsFieldListView = Mn.CollectionView.extend( {
	initialize: function ( options ) {
		this.fieldModel = options.fieldModel;
		this.childViewOptions = { fieldModel: options.fieldModel };
	}
} );

/**
 *
 */
export const PodsFieldView = Mn.LayoutView.extend( {
	serializeData: function () {
		const fieldModel = this.options.fieldModel;
		let data = this.model ? this.model.toJSON() : {};

		data.field_type = fieldModel.get( 'type' );
		data.attr = fieldModel.get( 'attributes' );
		data.options = fieldModel.get( 'options' );

		return data;
	}
} );
