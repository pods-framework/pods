/*global assert */
const inspect = require( 'util' ).inspect;
import {SelectView} from 'pods-dfv/_src/pick/views/select-view';
import {PodsDFVFieldModel} from 'pods-dfv/_src/core/pods-field-model';
import {RelationshipCollection} from 'pods-dfv/_src/pick/relationship-model';

const collection = new RelationshipCollection( [
	{ id: 0, name: 'zero' },
	{ id: 1, name: 'one' },
	{ id: 2, name: 'two' },
	{ id: 3, name: 'three' },
	{ id: 4, name: 'four' },
	{ id: 5, name: 'five' },
	{ id: 6, name: 'six' },
	{ id: 7, name: 'seven' },
	{ id: 8, name: 'eight' },
	{ id: 9, name: 'nine' },
] );

/**
 * @type {Object[]}  cases
 *
 * @type {string}    thisCase.name                 Descriptive name for this test case
 * @type {Object}    thisCase.fieldConfig          Field configuration for the view
 *
 * @type {Object[]}  thisCase.steps                Multiple steps, each making a selection
 * @type {string[]}  thisCase.steps.optionIndexes  select option indexes that get simulated clicks in this step
 * @type {string[]}  thisCase.steps.expected       Expected value after performing this step
 */
const cases = [
	{
		name: "Unlimited, individual selection",

		fieldConfig: {
			view_name       : 'select',
			pick_format_type: 'multi',
			pick_limit      : 0
		},

		steps: [
			{
				desc         : "First item is selected",
				optionIndexes: [ '0' ],
				expected     : [ '0' ]
			},
			{
				desc         : "Second item is selected",
				optionIndexes: [ '1' ],
				expected     : [ '0', '1' ]
			},
			{
				desc         : "Third item is selected",
				optionIndexes: [ '2' ],
				expected     : [ '0', '1', '2' ]
			},
			{
				desc         : "Fourth item is selected",
				optionIndexes: [ '3' ],
				expected     : [ '0', '1', '2', '3' ]
			},
		]
	},
	{
		name: "Limit 3, individual selection",

		fieldConfig: {
			view_name       : 'select',
			pick_format_type: 'multi',
			pick_limit      : 3
		},

		steps: [
			{
				desc         : "First item is selected",
				optionIndexes: [ '0' ],
				expected     : [ '0' ]
			},
			{
				desc         : "Second item is selected",
				optionIndexes: [ '1' ],
				expected     : [ '0', '1' ]
			},
			{
				desc         : "Third item is selected",
				optionIndexes: [ '2' ],
				expected     : [ '0', '1', '2' ]
			},
			{
				desc         : "Limit 3, the fourth item should be refused",
				optionIndexes: [ '3' ],
				expected     : [ '0', '1', '2' ]
			},
		]
	},
	{
		name: "Limit 3, multiple selection",

		fieldConfig: {
			view_name       : 'select',
			pick_format_type: 'multi',
			pick_limit      : 3
		},

		steps: [
			{
				desc         : "Select two staggered items",
				optionIndexes: [ '0', '2' ],
				expected     : [ '0', '2' ]
			},
			{
				desc         : "Attempt to select three more options, should refuse and revert to last good value",
				optionIndexes: [ '7', '8', '9' ],
				expected     : [ '0', '2' ]
			}
		]
	}
];

// Main test entry point
describe( 'Multiselect Selection Limits', function () {
	let $el;

	// Pre-test setup, before every it()
	beforeEach( function () {
		jQuery( document.body ).append( '<div id="target">' );
		$el = jQuery( '#target' );
	} );

	// Post-test clean-up, after every it()
	afterEach( function () {
		jQuery( document.body ).empty();
	} );

	// Iterate data-driven cases
	cases.forEach( function ( thisCase ) {
		testCase( $el, thisCase, collection );
	} );

} );

/**
 *
 * @param {jQuery} $el
 * @param {Object} thisCase
 * @param {RelationshipCollection} collection
 */
function testCase( $el, thisCase, collection ) {
	let $option;
	const view = createView( $el, thisCase.fieldConfig, collection );

	// Test this case
	it( thisCase.name, function () {

		// Loop through the steps for this case
		thisCase.steps.forEach( function ( thisStep ) {

			// Attempt to select all specified options
			thisStep.optionIndexes.forEach( function ( thisIndex ) {
				$option = view.$el.find( `option:eq(${thisIndex})` );
				$option.attr( 'selected', 'selected' );
			} );
			$option.parent().focus().change();

			assert.deepEqual( view.$el.val(), thisStep.expected,
				`Step: ${thisStep.desc} \n
				expected: ${inspect( thisStep.expected )} actual: ${inspect( view.$el.val() )}`
			);
		} );

	} );
}

/**
 *
 * @param {jQuery} $el
 * @param {Object} fieldConfig
 * @param {RelationshipCollection} collection
 */
function createView( $el, fieldConfig, collection ) {

	const fieldModel = new PodsDFVFieldModel( {
		htmlAttr   : {
			id  : 'test-view-id',
			name: 'test-view-name'
		},
		fieldConfig: fieldConfig
	} );

	const view = new SelectView( {
		$el       : $el,
		fieldModel: fieldModel,
		collection: collection
	} );

	view.render();

	return view;
}