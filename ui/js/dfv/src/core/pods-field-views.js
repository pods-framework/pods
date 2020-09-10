/*global jQuery, _, Backbone, PodsMn */

/**
 *
 */
export const PodsFieldListView = PodsMn.CollectionView.extend( {
	childViewEventPrefix: false, // Disable implicit event listeners in favor of explicit childViewTriggers and childViewEvents

	initialize( options ) {
		this.fieldModel = options.fieldModel;
		this.childViewOptions = { fieldModel: options.fieldModel };
	},
} );

/**
 * @augments Backbone.View
 */
export const PodsFieldView = PodsMn.View.extend( {
	childViewEventPrefix: false, // Disable implicit event listeners in favor of explicit childViewTriggers and childViewEvents

	serializeData() {
		const fieldModel = this.options.fieldModel;
		const data = this.model ? this.model.toJSON() : {};

		data.htmlAttr = fieldModel.get( 'htmlAttr' );
		data.fieldConfig = fieldModel.get( 'fieldConfig' );

		return data;
	},
} );

/**
 * Top-level "main field container"
 */
export const PodsDFVFieldLayout = PodsMn.View.extend( {
	childViewEventPrefix: false, // Disable implicit event listeners in favor of explicit childViewTriggers and childViewEvents

	initialize( options ) {
		this.fieldItemData = options.fieldItemData;
	},
} );
