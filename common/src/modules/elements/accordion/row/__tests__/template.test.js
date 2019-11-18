/**
 * External dependencies
 */
import React from 'react';

/**
 * Internal dependencies
 */
import Row from '@moderntribe/common/elements/accordion/row/template';

let row;

describe( 'Accordion Row Element', () => {
	beforeEach( () => {
		row = {
			accordionId: '123',
			content: 'this is a content',
			contentAttrs: { 'data-attr': 'content-attr-value' },
			contentClassName: 'content-class',
			header: 'this is header',
			headerAttrs: { 'data-attr': 'header-attr-value' },
			headerClassName: 'header-class',
			onClick: jest.fn(),
			onClose: jest.fn(),
			onOpen: jest.fn(),
		};
	} );

	it( 'renders an accordion row', () => {
		const component = renderer.create( <Row { ...row } /> );
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	it( 'executes onClick handler', () => {
		const component = mount( <Row { ...row } /> );
		component.find( 'button' ).simulate( 'click' );
		expect( row.onClick ).toHaveBeenCalled();
		expect( row.onClick ).toHaveBeenCalledTimes( 1 );
	} );

	it( 'executes onOpen and onClose handlers', async () => {
		const component = mount( <Row { ...row } /> );
		component.find( 'button' ).simulate( 'click' );
		await setTimeout( () => {
			component.find( 'button' ).simulate( 'click' );
			setTimeout( () => {
				expect( row.onOpen ).toHaveBeenCalled();
				expect( row.onOpen ).toHaveBeenCalledTimes( 1 );
				expect( row.onClose ).toHaveBeenCalled();
				expect( row.onClose ).toHaveBeenCalledTimes( 1 );
				expect( row.onClick ).toHaveBeenCalled();
				expect( row.onClick ).toHaveBeenCalledTimes( 2 );
			}, 250 );
		}, 250 );
	} );
} );
