/**
 * External dependencies
 */
import React from 'react';

/**
 * Internal dependencies
 */
import { Checkbox } from '@moderntribe/common/elements';

describe( 'Checkbox Element', () => {
	it( 'renders checkbox', () => {
		const component = renderer.create( <Checkbox /> );
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	it( 'renders checked checkbox', () => {
		const component = renderer.create( <Checkbox checked={ true } /> );
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	it( 'renders unchecked checkbox', () => {
		const component = renderer.create( <Checkbox checked={ false } /> );
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	it( 'renders checkbox with class', () => {
		const component = renderer.create( <Checkbox className="test-class" /> );
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	it( 'renders disabled checkbox', () => {
		const component = renderer.create( <Checkbox disabled={ true } /> );
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	it( 'renders checkbox with id', () => {
		const component = renderer.create( <Checkbox id="test-id" /> );
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	it( 'renders checkbox with label', () => {
		const component = renderer.create( <Checkbox label="Test Label" /> );
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	it( 'renders checkbox with name', () => {
		const component = renderer.create( <Checkbox name="test-name" /> );
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	it( 'renders checkbox with value', () => {
		const component = renderer.create( <Checkbox value="Test Value" /> );
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	it( 'renders checkbox with onChange handler', () => {
		const onChange = jest.fn();
		const component = renderer.create( <Checkbox onChange={ onChange } /> );
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	it( 'executes checkbox with onChange handler', () => {
		const onChange = jest.fn();
		const component = mount( <Checkbox onChange={ onChange } /> );
		component.find( 'input' ).simulate( 'change' );
		expect( onChange ).toHaveBeenCalled();
		expect( onChange ).toHaveBeenCalledTimes( 1 );
	} );
} );
