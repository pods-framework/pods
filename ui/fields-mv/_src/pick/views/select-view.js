/*global jQuery, _, Backbone, Marionette, select2, wp */
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
 *
 * @extends Backbone.View
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
 *
 * @extends Backbone.View
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
		this.fieldConfig = this.fieldModel.get( 'fieldConfig' );
	},

	/**
	 * Set the proper child view (optgroups or no)
	 *
	 * @param item
	 * @returns {*}
	 */
	childView: function ( item ) {
		if ( this.fieldConfig.optgroup ) {
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

		if ( this.fieldConfig.optgroup ) {
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

		data.htmlAttr = fieldModel.get( 'attributes' );
		data.fieldConfig = fieldModel.get( 'fieldConfig' );

		return data;
	},

	/**
	 *
	 */
	attributes: function () {

		/**
		 * @param {string} htmlAttr.name
		 * @param {string} htmlAttr.class
		 * @param {string} htmlAttr.name_clean
		 * @param {string} htmlAttr.id
		 *
		 * @param {string} fieldConfig.pick_format_type 'single' or 'multi'
		 */
		const fieldModel = this.options.fieldModel;
		const htmlAttr = fieldModel.get( 'htmlAttr' );
		const fieldConfig = fieldModel.get( 'fieldConfig' );

		let name = htmlAttr.name;
		if ( fieldConfig.pick_format_type === 'multi' ) {
			name = name + '[]';
		}
		return {
			'name'           : name,
			'class'          : htmlAttr.class,
			'data-name-clean': htmlAttr.name_clean,
			'id'             : htmlAttr.id,
			'tabindex'       : '2',
			'multiple'       : ( fieldConfig.pick_format_type === 'multi' )
		};
	},

	onAttach: function() {
		if ( this.fieldConfig.view_name === 'select2' ) {
			this.$el.select2();
		}
	},

	/**
	 *
	 */
	onChangeSelected: function () {
		this.collection.setSelected( this.$el.val() );
	}

} );
