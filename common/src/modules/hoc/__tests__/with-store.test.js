/**
 * External dependencies
 */
import renderer from 'react-test-renderer';
import React from 'react';

/**
 * Internal dependencies
 */
import { withStore } from '@moderntribe/common/hoc';

describe( 'HOC - With Store', () => {
	it( 'Should add the store property', () => {
		const Block = ( props ) => <div { ...props } />;
		const Wrapper = withStore()( Block );
		const component = renderer.create( <Wrapper /> );
		expect( component.toJSON() ).toMatchSnapshot();

		const instance = component.root;
		expect( instance ).not.toBe( null );
		const props = instance.findByType( Block ).props;
		expect( props ).toHaveProperty( 'store' );
		const { store } = props;
		expect( store ).toHaveProperty( 'dispatch' );
		expect( store ).toHaveProperty( 'getState' );
	} );
} );

