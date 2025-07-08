/**
 * External dependencies
 */
import { render, screen } from '@testing-library/react';

/**
 * Internal dependencies
 */
import createBlockEditComponent from '../createBlockEditComponent';

import {
	simpleBlock,
	allFieldsBlock,
	simpleBlockProps,
} from '../../testData';

const simpleEditComponentProps = {
	...simpleBlockProps,
	setAttributes: jest.fn(),
};

let SimpleEditComponent = null;
let AllFieldsEditComponent = null;

describe( 'createBlockEditComponent', () => {
	beforeAll( () => {
		SimpleEditComponent = createBlockEditComponent( simpleBlock );
		AllFieldsEditComponent = createBlockEditComponent( allFieldsBlock );
	} );

	test( 'creates a valid block "edit" component', () => {
		expect( typeof SimpleEditComponent ).toBe( 'function' );
		expect( typeof AllFieldsEditComponent ).toBe( 'function' );
	} );

	test( 'that the created "edit" component can be rendered', () => {
		render( <SimpleEditComponent { ...simpleEditComponentProps } /> );

		expect( screen.getAllByRole( 'document' ) ).toHaveLength( 1 );

		const mainDiv = screen.getByRole( 'document' ).firstChild;
		expect( mainDiv.tagName ).toEqual( 'DIV' );
		expect( mainDiv.textContent ).toEqual( 'Something else here and a field: Some content' );

		expect( screen.getAllByText( 'Some content' ) ).toHaveLength( 1 );
		expect( screen.getByText( 'Some content' ).tagName ).toEqual( 'DIV' );
	} );
} );
