/**
 * External dependencies
 */
import React from 'react';

/**
 * Internal dependencies
 */
import { Image } from '@moderntribe/common/elements';

let imageProps;

describe( 'Image Element', () => {
	beforeEach( () => {
		imageProps = {
			src: 'test-src',
			alt: 'test-alt',
		};
	} );

	it( 'renders image', () => {
		const component = renderer.create( <Image { ...imageProps } /> );
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	it( 'renders image with class', () => {
		imageProps.className = 'test-class';
		const component = renderer.create( <Image { ...imageProps } /> );
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	it( 'renders image with extra props', () => {
		imageProps.width = "42";
		imageProps.height = "42";
		const component = renderer.create( <Image { ...imageProps } /> );
		expect( component.toJSON() ).toMatchSnapshot();
	} );
} );
