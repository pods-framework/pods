/*global assert */
import {PodsDFVFieldModel} from 'pods-dfv/_src/core/pods-field-model';
import {Pick} from 'pods-dfv/_src/pick/pick';

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

	/**
	 * Test pick_allow_add_new option
	 */
	it( "Does not show add new when it shouldn't", function () {
		const add_new_not_allowed = [
			{
				description: 'Default values (empty)',
				options    : {}
			},
			{
				description: 'Add new option as a string',
				options    : {
					iframe_src        : 'xxx',
					pick_allow_add_new: '0'
				}
			},
			{
				description: 'Add new option as a non 1/0 string',
				options    : {
					iframe_src        : 'xxx',
					pick_allow_add_new: 'bob'
				}
			},
			{
				description: 'Add new option as a number',
				options    : {
					iframe_src        : 'xxx',
					pick_allow_add_new: 0
				}
			},
			{
				description: 'Add new option as a Boolean',
				options    : {
					iframe_src        : 'xxx',
					pick_allow_add_new: false
				}
			},
			{
				description: 'Add new allowed but no iframe source specified',
				options    : {
					pick_allow_add_new: '1'
				}
			},
		];

		add_new_not_allowed.forEach( function ( thisOption ) {
			const description = thisOption.description;
			const options = thisOption.options;

			fieldModel.set( 'fieldConfig', options );

			field = new Pick( {
				el   : $el,
				model: fieldModel
			} );

			field.render();

			assert.equal( $el.find( '.pods-related-add-new' ).length, 0, description );
		} );

	} );

	it( "Shows add new when it should", function () {
		const add_new_allowed = [
			{
				description: 'Add new option as a string',
				options    : {
					iframe_src        : 'xxx',
					pick_allow_add_new: '1'
				}
			},
			{
				description: 'Add new option as a number',
				options    : {
					iframe_src        : 'xxx',
					pick_allow_add_new: 1
				}
			},
			{
				description: 'Add new option as a Boolean',
				options    : {
					iframe_src        : 'xxx',
					pick_allow_add_new: true
				}
			},
		];

		add_new_allowed.forEach( function ( thisOption ) {
			const description = thisOption.description;
			const options = thisOption.options;

			fieldModel.set( 'fieldConfig', options );

			field = new Pick( {
				el   : $el,
				model: fieldModel
			} );

			field.render();

			assert.equal( $el.find( '.pods-related-add-new' ).length, 1, description );
		} );

	} );

} );
