/**
 * External dependencies
 */
import React from 'react';
import renderer from 'react-test-renderer';

/**
 * Internal dependencies
 */
import { Counter } from '@moderntribe/common/elements';

describe( 'Counter Element', () => {
	it( 'renders counter', () => {
		const component = renderer.create( <Counter /> );
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	it( 'renders counter with class', () => {
		const component = renderer.create( <Counter className="test-class" /> );
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	it( 'renders counter with count', () => {
		const component = renderer.create( <Counter count={ 42 } /> );
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	it( 'renders counter with label', () => {
		const component = renderer.create( <Counter label="test-label" /> );
		expect( component.toJSON() ).toMatchSnapshot();
	} );
} );
