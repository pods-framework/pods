/*global assert */
const inspect = require( 'util' ).inspect;

import {CheckboxView} from 'pods-dfv/_src/pick/views/checkbox-view';
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
		name: "Unlimited selections",

		fieldConfig: {
			view_name       : 'checkbox',
			pick_format_type: 'multi',
			pick_limit      : 0
		},

		steps: [
			{
				desc            : "Select the first checkbox",
				targetItem      : 0,
				expectedValue   : [ '0' ],
				expectedDisabled: []
			},
			{
				desc            : "Select the second checkbox",
				targetItem      : 1,
				expectedValue   : [ '0', '1' ],
				expectedDisabled: []
			},
			{
				desc            : "Select the third checkbox",
				targetItem      : 2,
				expectedValue   : [ '0', '1', '2' ],
				expectedDisabled: []
			},
			{
				desc            : "Select the fourth checkbox",
				targetItem      : 3,
				expectedValue   : [ '0', '1', '2', '3' ],
				expectedDisabled: []
			},
			{
				desc            : "Deselect the first checkbox",
				targetItem      : 0,
				expectedValue   : [ '1', '2', '3' ],
				expectedDisabled: []
			},
			{
				desc            : "Deselect the second checkbox",
				targetItem      : 1,
				expectedValue   : [ '2', '3' ],
				expectedDisabled: []
			},
			{
				desc            : "Deselect the third checkbox",
				targetItem      : 2,
				expectedValue   : [ '3' ],
				expectedDisabled: []
			},
			{
				desc            : "Deselect the fourth checkbox",
				targetItem      : 3,
				expectedValue   : [],
				expectedDisabled: []
			}
		]
	},
	{
		name: "Limit 3",

		fieldConfig: {
			view_name       : 'checkbox',
			pick_format_type: 'multi',
			pick_limit      : 3
		},

		steps: [
			{
				desc            : "Select the first checkbox",
				targetItem      : 0,
				expectedValue   : [ '0' ],
				expectedDisabled: []
			},
			{
				desc            : "Select the second checkbox",
				targetItem      : 1,
				expectedValue   : [ '0', '1' ],
				expectedDisabled: []
			},
			{
				desc            : "Select the third checkbox, hit selection limit, disable other checkboxes",
				targetItem      : 2,
				expectedValue   : [ '0', '1', '2' ],
				expectedDisabled: [ '3', '4', '5', '6', '7', '8', '9' ]
			},
			{
				desc            : "Limit 3, selecting the fourth checkbox should refuse",
				targetItem      : 3,
				expectedValue   : [ '0', '1', '2' ],
				expectedDisabled: [ '3', '4', '5', '6', '7', '8', '9' ]
			},
			{
				desc            : "Deselect the third checkbox, disabled checkboxes should re-enable",
				targetItem      : 2,
				expectedValue   : [ '0', '1' ],
				expectedDisabled: []
			},
		]
	}
];

// Main test entry point
describe( 'Checkbox Selection Limits', function () {
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
			let $checkbox, value, disabled;

			// Perform the step and grab the state
			view.$el.find( `input[type=checkbox][value=${thisStep.targetItem}]` ).trigger( 'click' );

			value = view.$el.find( 'input[type=checkbox]:checked' ).map( function ( _, el ) {
				return jQuery( el ).val();
			} ).get();
			disabled = view.$el.find( 'input[type=checkbox]:disabled' ).map( function () {
				return this.value;
			} ).toArray();

			// Test value
			assert.deepEqual( value, thisStep.expectedValue,
				`\nStep: ${thisStep.desc}` +
				`\nExpected value: ${inspect( thisStep.expectedValue )} ` +
				`Actual value: ${inspect( value )}\n`
			);

			// Test checkboxes we expect to be disabled
			assert.deepEqual( disabled, thisStep.expectedDisabled,
				`\nStep: ${thisStep.desc}` +
				`\nExpected disabled checkboxes: ${inspect( thisStep.expectedDisabled )} ` +
				`Actual disabled checkboxes: ${inspect( disabled )}\n`
			);
		} );

		// Note: Tear-down for unique references must be inside the it() block. Mocha runs all it() blocks asynch
		view.destroy();
	} );
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

	view = new CheckboxView( {
		fieldModel: fieldModel,
		collection: collection
	} ).render();

	return view;
}