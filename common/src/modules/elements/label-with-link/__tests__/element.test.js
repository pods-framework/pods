/**
 * External dependencies
 */
import React from 'react';

/**
 * Internal dependencies
 */
import LabelWithLink from '../element';

describe( 'Label With Link Element', () => {
	it( 'renders a label with link', () => {
		const component = renderer.create(
			<LabelWithLink linkHref="#" linkText="test-text" />
		);
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	it( 'renders a label with link with class', () => {
		const component = renderer.create(
			<LabelWithLink linkHref="#" linkText="test-text" className="test-class" />
		);
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	it( 'renders a label with link with label', () => {
		const component = renderer.create(
			<LabelWithLink linkHref="#" linkText="test-text" label="test label" />
		);
		expect( component.toJSON() ).toMatchSnapshot();
	} );
} );
