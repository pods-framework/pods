/**
 * External dependencies
 */
import React from 'react';
import { noop } from 'lodash';

/**
 * Internal dependencies
 */
import NumberInput from '../element.js';

describe( 'Input element', () => {
	it( 'Should render the component', () => {
		const component = renderer.create( <NumberInput /> );
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	it( 'Should render the component with class', () => {
		const component = renderer.create( <NumberInput className="input-class" /> );
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	it( 'Should render the component with max', () => {
		const component = renderer.create( <NumberInput max={ 42 } /> );
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	it( 'Should render the component with min', () => {
		const component = renderer.create( <NumberInput min={ -42 } /> );
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	it( 'Should render the component with onChange handler', () => {
		const component = renderer.create( <NumberInput onChange={ noop } /> );
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	it( 'Should render the component with step', () => {
		const component = renderer.create( <NumberInput step={ 10 } /> );
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	it( 'Should render the component with extra props', () => {
		const component = renderer.create( <NumberInput id="input-id" /> );
		expect( component.toJSON() ).toMatchSnapshot();
	} );
} );
