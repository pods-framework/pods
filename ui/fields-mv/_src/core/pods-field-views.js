/*global jQuery, _, Backbone, Marionette */
import {PodsFieldModel} from '~/ui/fields-mv/_src/core/pods-field-model';

/**
 *
 */
export const PodsFieldListView = Marionette.CollectionView.extend( {
	initialize: function ( options ) {
		this.fieldModel = options.fieldModel;
		this.childViewOptions = { fieldModel: options.fieldModel };
	}
} );

/**
 * @extends Backbone.View
 */
export const PodsFieldView = Marionette.View.extend( {
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
export const PodsMVFieldLayout = Marionette.View.extend( {

	initialize: function ( options ) {
		this.model = new PodsFieldModel( {
			htmlAttr   : options.htmlAttr,
			fieldConfig: options.fieldConfig
		} );

		this.fieldData = options.fieldData;
	}
} );
