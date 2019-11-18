/**
 * External dependencies
 */
import React from 'react';

/**
 * Internal dependencies
 */
import { LabeledItem } from '@moderntribe/common/elements';

describe( 'Labeled Item Element', () => {
	it( 'renders labeled item', () => {
		const component = renderer.create( <LabeledItem>Test</LabeledItem> );
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	it( 'renders labeled item with class', () => {
		const component = renderer.create( <LabeledItem className="test-class">Test</LabeledItem> );
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	it( 'renders labeled item with label', () => {
		const component = renderer.create( <LabeledItem label="test label">Test</LabeledItem> );
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	it( 'renders labeled item with label element and for id', () => {
		const component = renderer.create(
			<LabeledItem
				label="test label"
				isLabel={ true }
				forId="test-id"
			>
				Test
			</LabeledItem>
		);
		expect( component.toJSON() ).toMatchSnapshot();
	} );
} );
