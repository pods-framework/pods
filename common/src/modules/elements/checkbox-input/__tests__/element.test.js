/**
 * External dependencies
 */
import React from 'react';
import { noop } from 'lodash';

/**
 * Internal dependencies
 */
import CheckboxInput from '../element.js';

describe( 'Input element', () => {
	it( 'Should render the component', () => {
		const component = renderer.create( <CheckboxInput /> );
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	it( 'Should render the component with checked', () => {
		const component1 = renderer.create( <CheckboxInput checked={ true } /> );
		expect( component1.toJSON() ).toMatchSnapshot();
		const component2 = renderer.create( <CheckboxInput checked={ false } /> );
		expect( component2.toJSON() ).toMatchSnapshot();
	} );

	it( 'Should render the component with class', () => {
		const component = renderer.create( <CheckboxInput className="checkbox-class" /> );
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	it( 'Should render the component with onChange handler', () => {
		const component = renderer.create( <CheckboxInput onChange={ noop } /> );
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	it( 'Should render the component with extra props', () => {
		const component = renderer.create( <CheckboxInput id="checkbox-id" /> );
		expect( component.toJSON() ).toMatchSnapshot();
	} );
} );
