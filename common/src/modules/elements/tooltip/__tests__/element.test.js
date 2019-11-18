/**
 * External dependencies
 */
import React from 'react';
import renderer from 'react-test-renderer';

/**
 * Internal dependencies
 */
import Tooltip from '@moderntribe/common/elements/tooltip/element';

jest.mock( '@wordpress/components', () => ( {
	Tooltip: ({ text, position, children }) => (
		<div>
			<span>{ text }</span>
			<span>{ position }</span>
			<span>{ children }</span>
		</div>
	),
} ) );

describe( 'Tooltip Element', () => {
	it( 'renders a tooltip', () => {
		const props = {
			label: 'some label',
			labelClassName: 'label-class-name',
			position: 'bottom left',
			text: 'here is the tooltip text',
		};
		const component = renderer.create( <Tooltip { ...props } />)
		expect( component.toJSON() ).toMatchSnapshot()
	} );
} );
