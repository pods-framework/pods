import React from 'react';
import Placeholder from '../element';

describe( '<Placeholder> component', () => {
	test( 'Default behavior', () => {
		const component = renderer.create(
			<Placeholder>Custom Text</Placeholder>
		);
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	test( 'Custom Class name attached', () => {
		const component = renderer.create(
			<Placeholder className='custom-class-name'>Custom Text</Placeholder>
		);
		expect( component.toJSON() ).toMatchSnapshot();
	} );
} );
