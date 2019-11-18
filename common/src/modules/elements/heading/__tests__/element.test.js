import React from 'react';

import Heading from '../element';

describe( '<Heading>', () => {
	test( '<h1>', () => {
		const component = renderer.create(
			<Heading level={ 1 }>Modern Tribe</Heading>,
		);
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	test( '<h2>', () => {
		const component = renderer.create(
			<Heading level={ 2 }>Modern Tribe</Heading>,
		);
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	test( '<h3>', () => {
		const component = renderer.create(
			<Heading level={ 3 }>Modern Tribe</Heading>,
		);
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	test( '<h4>', () => {
		const component = renderer.create(
			<Heading level={ 4 }>Modern Tribe</Heading>,
		);
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	test( '<h5>', () => {
		const component = renderer.create(
			<Heading level={ 5 }>Modern Tribe</Heading>,
		);
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	test( '<h6>', () => {
		const component = renderer.create(
			<Heading level={ 6 }>Modern Tribe</Heading>,
		);
		expect( component.toJSON() ).toMatchSnapshot();
	} );
} );
