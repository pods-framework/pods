/*global assert */
import {SelectView} from '~/ui/fields-mv/_src/pick/views/select-view';
import {PodsFieldModel} from '~/ui/fields-mv/_src/core/pods-field-model';
import {RelationshipCollection} from '~/ui/fields-mv/_src/pick/relationship-model';
let options = {};

let fieldModel = new PodsFieldModel( {
	type      : 'pick',
	attributes: {
		'name'      : 'field-name',
		'class'     : 'class list',
		'name_clean': 'data-name-clean',
		'id'        : 'select-test'
	},
	options   : options
} );

let data = [
	{ id: 123, name: 'asdf' },
	{ id: 124, name: 'zxcv', 'selected': true }
];
let collection = new RelationshipCollection( data );
let view;

/**
 *
 */
describe( 'SelectView', function () {
	let pickFormatTypes = [
		{ type: 'single', isMultiple: false },
		{ type: 'multi', isMultiple: true }
	];

	pickFormatTypes.forEach( function ( option ) {
		let multiple;

		it( 'respects ' + option.type + ' select', function () {
			options = { pick_format_type: option.type };
			fieldModel.set( 'options', options );

			view = new SelectView( {
				fieldModel: fieldModel,
				collection: collection
			} );
			view.render();
			jQuery( document.body ).append( view.$el );

			multiple = jQuery( 'select' ).prop( 'multiple' );
			assert.equal( multiple, option.isMultiple );
		} );
	} );

	afterEach( function () {
		if ( view ) {
			view.destroy();
		}
		jQuery( document.body ).empty();
	} );

} );

