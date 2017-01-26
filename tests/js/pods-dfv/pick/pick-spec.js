/*global assert */
import {PodsDFVFieldModel} from '~/ui/js/pods-dfv/_src/core/pods-field-model';
import {Pick} from '~/ui/js/pods-dfv/_src/pick/pick';

/**
 * Pick field tests
 */
describe( 'Pick field', function () {
	let field, $el;
	let fieldModel = new PodsDFVFieldModel();

	/**
	 * Pre-test setup, before every it()
	 */
	beforeEach( function () {
		jQuery( document.body ).append( '<div id="target">' );
		$el = jQuery( '#target' );
	} );

	/**
	 * Post-test clean-up, after every it()
	 */
	afterEach( function () {
		jQuery( document.body ).empty();

		if ( field instanceof Backbone.View ) {
			field.destroy();
		}
	} );

	/**
	 *
	 */
	it( 'Does not throw an exception with defaults', function () {
		field = new Pick( {
			el   : $el,
			model: fieldModel
		} );

		assert.doesNotThrow( field.render );
	} );

	/**
	 *
	 */
	it( 'Throws an exception on invalid view name', function () {
		fieldModel.set( 'fieldConfig', { view_name: 'not a view' } );

		field = new Pick( {
			el   : $el,
			model: fieldModel
		} );
		assert.throws( field.render, Error );
	} );

} );
