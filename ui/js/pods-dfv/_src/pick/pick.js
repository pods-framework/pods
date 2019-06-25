/* eslint-disable camelcase */
/*global jQuery, _, Backbone, PodsMn, wp, PodsI18n */

import template from 'pods-dfv/_src/pick/pick-layout.html';

import { PodsDFVFieldModel } from 'pods-dfv/_src/core/pods-field-model';
import { PodsDFVFieldLayout } from 'pods-dfv/_src/core/pods-field-views';

import { IframeFrame } from 'pods-dfv/_src/core/iframe-frame';

import { RelationshipCollection } from 'pods-dfv/_src/pick/relationship-model';
import { PickFieldModel } from 'pods-dfv/_src/pick/pick-field-model';

import { RadioView } from 'pods-dfv/_src/pick/views/radio-view';
import { CheckboxView } from 'pods-dfv/_src/pick/views/checkbox-view';
import { SelectView } from 'pods-dfv/_src/pick/views/select-view';
import { ListView } from 'pods-dfv/_src/pick/views/list-view';
import { AddNew } from 'pods-dfv/_src/pick/views/add-new';

const views = {
	'checkbox': CheckboxView,
	'select': SelectView,
	'select2': SelectView,  // SelectView handles select2 as well
	'radio': RadioView,
	'list': ListView
};

let modalIFrame;

/**
 * @extends Backbone.View
 */
export const Pick = PodsDFVFieldLayout.extend( {
	childViewEventPrefix: false, // Disable implicit event listeners in favor of explicit childViewTriggers and childViewEvents

	template: _.template( template ),

	regions: {
		autocomplete: '.pods-ui-list-autocomplete',
		list: '.pods-pick-values',
		addNew: '.pods-ui-add-new'
	},

	childViewEvents: {
		'childview:remove:item:click': 'onChildviewRemoveItemClick',
		'childview:edit:item:click': 'onChildviewEditItemClick',
		'childview:selection:limit:over': 'onChildviewSelectionLimitOver',
		'childview:selection:limit:under': 'onChildviewSelectionLimitUnder',
		'childview:change:selected': 'onChildviewChangeSelected',
		'childview:add:new': 'onChildviewAddNew'
	},

	/**
	 *
	 */
	onBeforeRender: function () {
		if ( this.collection === undefined ) {
			this.collection = new RelationshipCollection( this.fieldItemData );
		}
	},

	/**
	 *
	 */
	onRender: function () {
		this.fieldConfig = new PickFieldModel( this.model.get( 'fieldConfig' ) );

		// Add New?
		// noinspection EqualityComparisonWithCoercionJS (why would we reject "1"?)
		if ( '' !== this.fieldConfig.get( 'iframe_src' ) && 1 == this.fieldConfig.get( 'pick_allow_add_new' ) ) {
			this.showAddNew();
		}

		// Autocomplete?
		if ( 'list' === this.fieldConfig.get( 'view_name' ) ) {
			this.buildAutocomplete();
		}

		// Build the list last, events fired by the list (like selection limit) may impact state in other views we manage
		this.showList();
	},

	/**
	 * This is for the List View's autocomplete for select from existing
	 */
	buildAutocomplete: function () {
		let fieldConfig, model, collection, view;
		const pickLimit = +this.fieldConfig.get( 'pick_limit' ); // Unary plus forces cast to number

		fieldConfig = {
			view_name: 'select2',
			pick_format_type: 'multi',
			selectFromExisting: true,
			ajax_data: this.fieldConfig.get( 'ajax_data' ),
			select2_overrides: this.fieldConfig.get( 'select2_overrides' ),
			label: this.fieldConfig.get( 'label' ),
			pick_limit: pickLimit
		};

		// The autocomplete portion of List View doesn't track selected items; disable if we're at the selection limit
		if ( this.collection.filterBySelected().length >= pickLimit && 0 !== pickLimit ) {

			fieldConfig.limitDisable = true;
			this.onChildviewSelectionLimitOver();

		} else {

			this.onChildviewSelectionLimitUnder();
		}

		model = new PodsDFVFieldModel( { fieldConfig: fieldConfig } );
		collection = this.collection.filterByUnselected();
		view = new SelectView( { collection: collection, fieldModel: model } );

		// Provide a custom list filter for the autocomplete portion's AJAX data lists
		view.filterAjaxList = this.filterAjaxList.bind( this );

		// Rebuild from scratch
		this.showChildView( 'autocomplete', view );
	},

	/**
	 *
	 */
	showList: function () {
		let viewName, View, list;

		// Setup the view to be used
		viewName = this.fieldConfig.get( 'view_name' );
		if ( views[ viewName ] === undefined ) {
			throw new Error( `Invalid view name "${viewName}"` );
		}
		View = views[ viewName ];
		list = new View( { collection: this.collection, fieldModel: this.model } );

		this.showChildView( 'list', list );
	},

	/**
	 *
	 */
	showAddNew: function () {
		let addNew = new AddNew( { fieldModel: this.model } );
		this.showChildView( 'addNew', addNew );
	},

	/**
	 * List Views need to filter items already selected from their select from existing list.  The AJAX function
	 * itself does not filter.
	 *
	 * @param data
	 */
	filterAjaxList: function ( data ) {
		const selectedItems = this.collection.filterBySelected();
		const returnList = [];

		// Loop through the items returned via ajax
		_.each( data.results, function ( element ) {
			element.text = element.name; // Select2 needs the "text" key but our model uses "name"

			// Only keep choices that haven't been selected yet, we don't want selected items in the autocomplete portion
			if ( !selectedItems.get( element.id ) ) {
				returnList.push( element );
			}
		} );

		// The collection may be partial in ajax mode, make sure we add any items we didn't yet have
		this.collection.add( returnList );
		this.getChildView( 'autocomplete' ).setCollection( this.collection.filterByUnselected() );

		return { 'results': returnList };
	},

	/**
	 *
	 * @param childView
	 */
	onChildviewSelectionLimitOver: function ( childView ) {
		const addNew = this.getChildView( 'addNew' );
		if ( addNew ) {
			addNew.disable();
		}
	},

	/**
	 *
	 * @param childView
	 */
	onChildviewSelectionLimitUnder: function ( childView ) {
		const addNew = this.getChildView( 'addNew' );
		if ( addNew ) {
			addNew.enable();
		}
	},

	/**
	 * "Remove" in list view just toggles an item's selected attribute
	 *
	 * @param childView
	 */
	onChildviewRemoveItemClick: function ( childView ) {
		childView.model.toggleSelected();
		this.getChildView( 'list' ).render();

		// Keep autocomplete in sync, removed items should now be available choices
		if ( 'list' === this.fieldConfig.get( 'view_name' ) ) {
			this.buildAutocomplete();
		}
	},

	/**
	 * @param childView
	 */
	onChildviewAddNew: function ( childView ) {
		const fieldConfig = this.model.get( 'fieldConfig' );

		modalIFrame = new IframeFrame( {
			title: fieldConfig.iframe_title_add,
			src: fieldConfig.iframe_src
		} );

		this.setModalListeners();
		modalIFrame.modal.open();
	},

	/**
	 * @param childView
	 */
	onChildviewEditItemClick: function ( childView ) {
		const fieldConfig = this.model.get( 'fieldConfig' );

		modalIFrame = new IframeFrame( {
			title: fieldConfig.iframe_title_edit,
			src: childView.ui.editButton.attr( 'href' )
		} );

		this.setModalListeners();
		modalIFrame.modal.open();
	},

	/**
	 *
	 * @param childView
	 */
	onChildviewChangeSelected: function ( childView ) {

		// Refresh the autocomplete and List View lists on autocomplete selection
		if ( childView.fieldConfig.selectFromExisting ) {
			_.defer( this.buildAutocomplete.bind( this ) );
			this.getChildView( 'list' ).render();
		}
	},

	setModalListeners: function () {
		jQuery( window ).on( 'dfv:modal:update', this.modalSuccess.bind( this ) );
		jQuery( window ).on( 'dfv:modal:cancel', this.modalCancel.bind( this ) );
	},

	clearModalListeners: function () {
		jQuery( window ).off( 'dfv:modal:update' );
		jQuery( window ).off( 'dfv:modal:cancel' );
	},

	/**
	 * @param event
	 * @param data
	 */
	modalSuccess: function ( event, data ) {
		const itemModel = this.collection.get( data.id );

		if ( itemModel ) {
			// Edit: update an existing model and force a re-render
			itemModel.set( data );
			this.getChildView( 'list' ).render();
		} else {
			// Add new: create a new model in the collection
			this.collection.add( data );
		}

		this.clearModalListeners();
		modalIFrame.modal.close( {} );
	},

	/**
	 *
	 */
	modalCancel: function () {
		this.clearModalListeners();
	}

} );
