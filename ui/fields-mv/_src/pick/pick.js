/*global jQuery, _, Backbone, Mn, wp */
import template from '~/ui/fields-mv/_src/pick/pick-layout.html';

import {IframeFrame} from '~/ui/fields-mv/_src/core/iframe-frame';
import {RadioView} from '~/ui/fields-mv/_src/pick/views/radio-view';
import {CheckboxView} from '~/ui/fields-mv/_src/pick/views/checkbox-view';
import {SelectView} from '~/ui/fields-mv/_src/pick/views/select-view';
import {FlexView} from '~/ui/fields-mv/_src/pick/views/flex-view';
import {AddNew} from '~/ui/fields-mv/_src/pick/views/add-new';

const AJAX_ADD_NEW_ACTION = 'pods_relationship_popup';

const views = {
	'checkbox'    : CheckboxView,
	'dropdown'    : SelectView,
	'multiselect' : SelectView,
	'radio'       : RadioView,
	'autocomplete': 'select2',
	'flexible'    : FlexView
};

/**
 *
 */
export const Pick = Mn.LayoutView.extend( {
	template: _.template( template ),

	regions: {
		list  : '.pods-pick-values',
		addNew: '.pods-ui-add-new'
	},

	onRender: function () {
		let View, viewKey, list, addNew;
		const fieldOptions = this.model.attributes.options;

		// ToDo: don't follow the legacy badness here, require the caller to give us the proper view name
		if ( fieldOptions.pick_format_type !== 'multi' ) {
			viewKey = fieldOptions.pick_format_single;
		}
		else {
			viewKey = fieldOptions.pick_format_multi;
		}
		View = views[ viewKey ];

		// ToDo: need better handling than this
		if ( typeof View === "function" ) {
			list = new View( { collection: this.collection, fieldModel: this.model } );
			this.showChildView( 'list', list );
		}

		if ( fieldOptions.iframe_src !== '' ) {
			addNew = new AddNew( { fieldModel: this.model } );
			this.showChildView( 'addNew', addNew );
		}
	},

	/** "Remove" in flex view just toggles an item's selected attribute
	 *
	 * @param childView
	 * @param args
	 */
	onChildviewRemoveItemClick: function ( childView, args ) {
		const list = this.getChildView( 'list' );

		args.model.toggleSelected();
		list.render();
	},

	/**
	 *
	 * @param childView
	 */
	onChildviewAddNewClick: function ( childView ) {
		const options = this.model.get( 'options' );

		const modalFrame = new IframeFrame( {
			title: 'The Title',
			src  : options.iframe_src
		} );
		modalFrame.modal.open();
	},

	addNewSuccess: function ( response ) {
		console.log( response );
	}

} );