/**
 * External dependencies
 */
import React from 'react';
import renderer from 'react-test-renderer';
import { noop } from 'lodash';

/**
 * Internal dependencies
 */
import Input from '../element.js';

describe( 'Input element', () => {
	it( 'Should render the component', () => {
		const component = renderer.create( <Input type="text" /> );
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	it( 'Should render the component with class', () => {
		const component = renderer.create( <Input type="text" className="input-class" /> );
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	it( 'Should render the component with extra props', () => {
		const component = renderer.create( <Input type="text" onChange={ noop } /> );
		expect( component.toJSON() ).toMatchSnapshot();
	} );
} );
