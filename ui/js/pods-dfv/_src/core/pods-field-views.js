/*global jQuery, _, Backbone, Marionette */

/**
 *
 */
export const PodsFieldListView = Marionette.CollectionView.extend( {
	childViewEventPrefix: false, // Disable implicit event listeners in favor of explicit childViewTriggers and childViewEvents

	initialize: function ( options ) {
		this.fieldModel = options.fieldModel;
		this.childViewOptions = { fieldModel: options.fieldModel };
	}
} );

/**
 * @extends Backbone.View
 */
export const PodsFieldView = Marionette.View.extend( {
	childViewEventPrefix: false, // Disable implicit event listeners in favor of explicit childViewTriggers and childViewEvents

	serializeData: function () {
		const fieldModel = this.options.fieldModel;
		let data = this.model ? this.model.toJSON() : {};

		data.htmlAttr = fieldModel.get( 'htmlAttr' );
		data.fieldConfig = fieldModel.get( 'fieldConfig' );

		return data;
	}
} );

/**
 * Top-level "main field container"
 */
export const PodsDFVFieldLayout = Marionette.View.extend( {
	childViewEventPrefix: false, // Disable implicit event listeners in favor of explicit childViewTriggers and childViewEvents

	initialize: function ( options ) {
		this.fieldItemData = options.fieldItemData;
	}
} );
