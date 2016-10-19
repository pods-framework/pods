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

		data.attr = fieldModel.get( 'attributes' );
		data.options = fieldModel.get( 'options' );

		return data;
	}
} );

/**
 *
 */
export const PodsMVFieldLayout = Marionette.View.extend( {

	initialize: function ( options ) {
		this.model = new PodsFieldModel( {
			attributes: options.fieldAttributes,
			options   : options.fieldOptions
		} );

		this.fieldData = options.fieldData;
	}
} );
