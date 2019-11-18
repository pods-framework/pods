import React from 'react';

import Paragraph, { SIZES } from '../element';

describe( '<Paragraph>', () => {
	test( 'default paragraph', () => {
		const component = renderer.create(
			<Paragraph>Modern Tribe</Paragraph>,
		);
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	test( 'smaller paragraph', () => {
		const component = renderer.create(
			<Paragraph size={ SIZES.small }>Modern Tribe</Paragraph>,
		);
		expect( component.toJSON() ).toMatchSnapshot();
	} );
} );
