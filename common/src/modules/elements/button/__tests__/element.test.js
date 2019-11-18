/**
 * External dependencies
 */
import React from 'react';
import renderer from 'react-test-renderer';

/**
 * Internal dependencies
 */
import { Button } from '@moderntribe/common/elements';

describe( 'Button Element', () => {
	it( 'renders button', () => {
		const component = renderer.create( <Button /> );
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	it( 'renders button with class', () => {
		const component = renderer.create( <Button className="test-class" /> );
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	it( 'renders disabled button', () => {
		const component = renderer.create( <Button isDisabled={ true } /> );
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	it( 'renders button with label', () => {
		const component = renderer.create( <Button>Hello</Button> );
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	it( 'renders button with onClick handler', () => {
		const onClick = jest.fn();
		const component = renderer.create( <Button onClick={ onClick } /> );
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	it( 'executes the onClick handler', () => {
		const onClick = jest.fn();
		const component = mount( <Button onClick={ onClick } /> );
		component.find( 'button' ).simulate( 'click' );
		expect( onClick ).toHaveBeenCalled();
		expect( onClick ).toHaveBeenCalledTimes( 1 );
	} );

	it( 'renders button with set type', () => {
		const component = renderer.create( <Button type="submit" /> );
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	it( 'renders button with extra attributes', () => {
		const attrs = {
			test: 'one-two-three',
			hello: 'world',
		};
		const component = renderer.create( <Button { ...attrs } /> );
		expect( component.toJSON() ).toMatchSnapshot();
	} );
} );
