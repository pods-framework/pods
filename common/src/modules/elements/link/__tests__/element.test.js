/**
 * External dependencies
 */
import React from 'react';

/**
 * Internal dependencies
 */
import Link from '../element.js';

describe( 'Link Element', () => {
	it( 'renders link', () => {
		const component = renderer.create(
			<Link href="#">label</Link>
		);
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	it( 'renders button with class', () => {
		const component = renderer.create(
			<Link className="test-class" href="#">label</Link>
		);
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	it( 'renders button with target', () => {
		const component = renderer.create(
			<Link href="#" target="_blank">label</Link>
		);
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	it( 'renders button with prop', () => {
		const component = renderer.create(
			<Link href="#" title="title">label</Link>
		);
		expect( component.toJSON() ).toMatchSnapshot();
	} );
} );
