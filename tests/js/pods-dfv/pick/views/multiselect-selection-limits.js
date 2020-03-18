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
				desc            : "Select the first option",
				optionIndexes   : [ '0' ],
				expectedValue   : [ '0' ],
				expectedDisabled: []
			},
			{
				desc            : "Select the second option",
				optionIndexes   : [ '1' ],
				expectedValue   : [ '0', '1' ],
				expectedDisabled: []
			},
			{
				desc            : "Select the third option",
				optionIndexes   : [ '2' ],
				expectedValue   : [ '0', '1', '2' ],
				expectedDisabled: []
			},
			{
				desc            : "Select the fourth option",
				optionIndexes   : [ '3' ],
				expectedValue   : [ '0', '1', '2', '3' ],
				expectedDisabled: []
			},
			{
				desc            : "Deselect the fourth option",
				optionIndexes   : [ '3' ],
				expectedValue   : [ '0', '1', '2' ],
				expectedDisabled: []
			}
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
				desc            : "Select the first option",
				optionIndexes   : [ '0' ],
				expectedValue   : [ '0' ],
				expectedDisabled: []
			},
			{
				desc            : "Select the second option",
				optionIndexes   : [ '1' ],
				expectedValue   : [ '0', '1' ],
				expectedDisabled: []
			},
			{
				desc            : "Select the third option, reach limit, disable other options",
				optionIndexes   : [ '2' ],
				expectedValue   : [ '0', '1', '2' ],
				expectedDisabled: [ '3', '4', '5', '6', '7', '8', '9' ]
			},
			{
				desc            : "Limit 3, the fourth option should be refused",
				optionIndexes   : [ '3' ],
				expectedValue   : [ '0', '1', '2' ],
				expectedDisabled: [ '3', '4', '5', '6', '7', '8', '9' ]
			},
			{
				desc            : "Deselect first option, should clear diabled options",
				optionIndexes   : [ '0' ],
				expectedValue   : [ '1', '2' ],
				expectedDisabled: []
			},
			{
				desc            : "Deselect second option",
				optionIndexes   : [ '1' ],
				expectedValue   : [ '2' ],
				expectedDisabled: []
			},
			{
				desc            : "Deselect third option, selection should be empty again",
				optionIndexes   : [ '2' ],
				expectedValue   : null,  // Note: behavior for val() changes to an empty array in jQuery 3.0
				expectedDisabled: []
			}
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
				desc            : "Select five items, should be refused",
				optionIndexes   : [ '0', '1', '2', '3', '4' ],
				expectedValue   : null, // Note: behavior for val() changes to an empty array in jQuery 3.0
				expectedDisabled: []
			},
			{
				desc            : "Select two staggered items",
				optionIndexes   : [ '0', '2' ],
				expectedValue   : [ '0', '2' ],
				expectedDisabled: []
			},
			{
				desc            : "Attempt to select three more options, should refuse and revert to last good value",
				optionIndexes   : [ '7', '8', '9' ],
				expectedValue   : [ '0', '2' ],
				expectedDisabled: []
			}
		]
	}
];

// Main test entry point
describe( 'Multiselect Selection Limits', function () {
	cases.forEach( function ( thisCase, index ) {
		// Note: do not refactor the testCase function inline here, we need the closure of the function due to Mocha's
		// asynch execution of it() blocks.
		testCase( thisCase, collection, index );
	} );
} );

/**
 *
 * @param {Object}                  thisCase
 * @param {RelationshipCollection}  collection
 * @param {number}                  index
 */
function testCase( thisCase, collection, index ) {
	// Note: this needs to be here, between the function and the it() block.  Move it and you'll probably be sorry
	const view = createView( thisCase.fieldConfig, collection, index );

	// Test this case
	it( thisCase.name, function () {
		const containerID = `target-${index}`;
		let $container;

		// Note: This setup cannout be placed outside the it() block. Mocha runs all it() blocks asynch
		jQuery( document.body ).append( `<form id="${containerID}"></form>` );
		$container = jQuery( `#${containerID}` );
		$container.append( view.$el );

		// Loop through the steps for this case
		thisCase.steps.forEach( function ( thisStep, stepIndex ) {
			let value, disabled;

			// Perform the step and grab the state
			doStep( thisStep, view );

			value = view.$el.val();
			disabled = view.$el.find( 'option:disabled' ).map( function () {
				return this.value;
			} ).toArray();

			// Check value
			assert.deepEqual( value, thisStep.expectedValue,
				`\nStep: ${thisStep.desc}` +
				`\nExpected value: ${inspect( thisStep.expectedValue )} ` +
				`Actual value: ${inspect( value )}\n`
			);

			// Check options we expect to be disabled
			assert.deepEqual( disabled, thisStep.expectedDisabled,
				`\nStep: ${thisStep.desc}` +
				`\nExpected disabled options: ${inspect( thisStep.expectedDisabled )} ` +
				`Actual disabled options: ${inspect( disabled )}\n`
			);
		} );

		// Note: Tear-down for unique references must be inside the it() block. Mocha runs all it() blocks asynch
		view.destroy();
	} );
}

/**
 *
 * @param {Object} thisStep
 * @param {Object} view
 */
function doStep( thisStep, view ) {

	// Attempt to "click" (toggle) the specified options, one at a time
	thisStep.optionIndexes.forEach( function ( thisIndex ) {
		const $option = view.$el.find( `option:eq( ${thisIndex} )` );

		// Respect disabled options, cannot be clicked in the UI and we're vaguely simulating mouse clicks
		if ( !$option.prop( 'disabled' ) ) {
			$option.prop( 'selected', !$option.prop( 'selected' ) );
		}
	} );
	view.$el.focus().change();
}

/**
 *
 * @param {Object}                  fieldConfig
 * @param {RelationshipCollection}  collection
 * @param {number}                  index
 */
function createView( fieldConfig, collection, index ) {
	let fieldModel, view;

	fieldModel = new PodsDFVFieldModel( {
		htmlAttr   : {
			id  : `test-view-id-${index}`,
			name: `test-view-name-${index}`
		},
		fieldConfig: fieldConfig
	} );

	view = new SelectView( {
		fieldModel: fieldModel,
		collection: collection
	} ).render();

	return view;
}