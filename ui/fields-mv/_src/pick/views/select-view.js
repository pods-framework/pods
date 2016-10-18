/*global jQuery, _, Backbone, Marionette, wp */
// Note: this is a template-less view
import {PodsFieldListView, PodsFieldView} from '~/ui/fields-mv/_src/core/pods-field-views';
import {RelationshipCollection} from '~/ui/fields-mv/_src/pick/relationship-model';

/**
 * option
 *
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
 * optgroup
 */
export const Optgroup = PodsFieldListView.extend( {
	tagName  : 'optgroup',
	childView: SelectItem,

	attributes: function () {
		return {
			label: this.model.get( 'label' )
		};
	}
} );

/**
 * select
 */
export const SelectView = Marionette.CollectionView.extend( {
	tagName: 'select',

	triggers: {
		"change": {
			event          : "change:selected",
			stopPropagation: false
		}
	},

	initialize: function ( options ) {
		this.fieldModel = options.fieldModel;
		this.fieldOptions = this.fieldModel.get( 'options' );
	},

	/**
	 * Set the proper child view (optgroups or no)
	 *
	 * @param item
	 * @returns {*}
	 */
	childView: function ( item ) {
		if ( this.fieldOptions.optgroup ) {
			return Optgroup;
		}
		else {
			return SelectItem;
		}
	},

	/**
	 * todo: We're bypassing the PodsFieldListView functionality, need to explicitly include it for now
	 *
	 * @param model
	 * @param index
	 * @returns {{fieldModel: *}}
	 */
	childViewOptions: function ( model, index ) {
		let returnOptions = { fieldModel: this.fieldModel };

		if ( this.fieldOptions.optgroup ) {
			returnOptions.collection = new RelationshipCollection( model.get( 'collection' ) );
		}

		return returnOptions;
	},

	/**
	 * todo: We're bypassing the PodsFieldListView functionality, need to explicitly include it for now
	 *
	 * @returns {{}}
	 */
	serializeData: function () {
		const fieldModel = this.options.fieldModel;
		let data = this.model ? this.model.toJSON() : {};

		data.attr = fieldModel.get( 'attributes' );
		data.options = fieldModel.get( 'options' );

		return data;
	},

	/**
	 *
	 */
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

	/**
	 *
	 */
	onChangeSelected: function () {
		this.collection.setSelected( this.$el.val() );
	}

} );
