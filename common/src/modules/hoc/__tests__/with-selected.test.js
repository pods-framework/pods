/**
 * External dependencies
 */
import React from 'react';

/**
 * Internal dependencies
 */
import { withSelected } from '@moderntribe/common/hoc';

const Block = () => ( <div>With Selected!</div> );

describe( 'withSelected', () => {
	let HOC;
	const onFocus = jest.fn();
	const onBlur = jest.fn();
	const props = {
		onBlockFocus: onFocus,
		onBlockBlur: onBlur,
	};

	beforeEach( () => {
		HOC = withSelected()( Block );
	} );

	afterEach( () => {
		props.onBlockFocus.mockClear();
		props.onBlockBlur.mockClear();
	} );

	test( 'onBlur called when is not selected on mount', () => {
		props.isSelected = false;
		const component = mount( <HOC { ...props } /> );
		expect( props.onBlockBlur ).toHaveBeenCalled();
		expect( props.onBlockFocus ).not.toHaveBeenCalled();
	} );

	test( 'onFocus called when is selected on mount', () => {
		props.isSelected = true;
		const component = mount( <HOC { ...props } /> );
		expect( props.onBlockFocus ).toHaveBeenCalled();
		expect( props.onBlockBlur ).not.toHaveBeenCalled();
	} );

	test( 'trigger focus when isSelected changes after mounted', () => {
		props.isSelected = false;
		const component = mount( <HOC { ...props } /> );
		expect( props.onBlockBlur ).toHaveBeenCalled();
		expect( props.onBlockFocus ).not.toHaveBeenCalled();

		props.onBlockBlur.mockClear();
		props.onBlockFocus.mockClear();

		component.setProps( { isSelected: true } );

		expect( props.onBlockFocus ).toHaveBeenCalled();
		expect( props.onBlockBlur ).not.toHaveBeenCalled();
	} );

	test( 'trigger onBlur when isSelected changes after mounted', () => {
		props.isSelected = true;
		const component = mount( <HOC { ...props } /> );
		expect( props.onBlockFocus ).toHaveBeenCalled();
		expect( props.onBlockBlur ).not.toHaveBeenCalled();

		props.onBlockBlur.mockClear();
		props.onBlockFocus.mockClear();

		component.setProps( { isSelected: false } );

		expect( props.onBlockBlur ).toHaveBeenCalled();
		expect( props.onBlockFocus ).not.toHaveBeenCalled();
	} );

	test( 'blue and focus on the different props changes', () => {
		props.isSelected = false;
		const component = mount( <HOC { ...props } /> );
		expect( props.onBlockBlur ).toHaveBeenCalled();
		expect( props.onBlockFocus ).not.toHaveBeenCalled();

		props.onBlockBlur.mockClear();
		props.onBlockFocus.mockClear();

		component.setProps( { isSelected: true } );

		expect( props.onBlockFocus ).toHaveBeenCalled();
		expect( props.onBlockBlur ).not.toHaveBeenCalled();

		props.onBlockBlur.mockClear();
		props.onBlockFocus.mockClear();

		component.setProps( { isSelected: false } );

		expect( props.onBlockBlur ).toHaveBeenCalled();
		expect( props.onBlockFocus ).not.toHaveBeenCalled();
	} );
} );
