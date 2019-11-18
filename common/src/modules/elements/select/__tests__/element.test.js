/**
 * External dependencies
 */
import React from 'react';

/**
 * Internal dependencies
 */
import Select from '../element.js';

const options = [
	{ label: 'Test 1', value: 'test-1' },
	{ label: 'Test 2', value: 'test-2' },
];

describe( 'Select element', () => {
	it( 'Should render the component', () => {
		const component = renderer.create( <Select options={ options } /> );
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	it( 'Should render the component with class', () => {
		const component = renderer.create( <Select options={ options } className="test-class" /> );
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	it( 'Should render the component with extra props', () => {
		const component = renderer.create( <Select options={ options } isSearchable={ false } /> );
		expect( component.toJSON() ).toMatchSnapshot();
	} );
} );
