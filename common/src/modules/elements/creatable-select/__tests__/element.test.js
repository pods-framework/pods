/**
 * External dependencies
 */
import React from 'react';

/**
 * Internal dependencies
 */
import CreatableSelect from '../element.js';

const options = [
	{ label: 'Test 1', value: 'test-1' },
	{ label: 'Test 2', value: 'test-2' },
];

describe( 'CreatableSelect element', () => {
	it( 'Should render the component', () => {
		const component = renderer.create( <CreatableSelect options={ options } /> );
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	it( 'Should render the component with class', () => {
		const component = renderer.create( <CreatableSelect options={ options } className="test-class" /> );
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	it( 'Should render the component with extra props', () => {
		const component = renderer.create( <CreatableSelect options={ options } isSearchable={ false } /> );
		expect( component.toJSON() ).toMatchSnapshot();
	} );
} );
