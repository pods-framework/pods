/*global assert */
import {SelectView} from '~/ui/fields-mv/_src/pick/views/select-view';
import {PodsFieldModel} from '~/ui/fields-mv/_src/core/pods-field-model';
import {RelationshipCollection} from '~/ui/fields-mv/_src/pick/relationship-model';

let pickFormatTypes = [
	{ type: 'single', isMultiple: false },
	{ type: 'multi', isMultiple: true }
];

/**
 * SelectView tests
 */
describe( 'SelectView', function () {
	let view;
	let fieldModel = new PodsFieldModel();
	let collection = new RelationshipCollection();

	/**
	 * Post-test clean-up, after every it()
	 */
	afterEach( function () {
		jQuery( document.body ).empty();

		if ( view instanceof Backbone.View ) {
			view.destroy();
		}
	} );

	/**
	 * Test single/multi select
	 */
	pickFormatTypes.forEach( function ( option ) {
		let multiple;

		it( 'respects ' + option.type + ' select', function () {
			fieldModel.set( 'options', { pick_format_type: option.type } );

			view = new SelectView( {
				fieldModel: fieldModel
			} );
			view.render();
			jQuery( document.body ).append( view.$el );

			multiple = jQuery( 'select' ).prop( 'multiple' );
			assert.equal( multiple, option.isMultiple );
		} );
	} );

} );

