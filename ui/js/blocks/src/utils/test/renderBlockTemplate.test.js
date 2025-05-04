/**
 * External dependencies
 */
import { render, screen } from '@testing-library/react';

/**
 * Internal dependencies
 */
import renderBlockTemplate from '../renderBlockTemplate';

import {
	textField,
	numberField,
	basicTemplate,
	multipleFieldsTemplate,
} from '../../testData';

// Simplest possible function to render a field.
const renderField = ( field, attributes = {} ) => {
	const { name, type } = field;

	const fieldValue = attributes[ name ] || null;

	return (
		<span key={ name } className={ `field--${ type }` }>
			{ fieldValue }
		</span>
	);
};

const fields = [
	textField,
	numberField,
];

describe( 'renderBlockTemplate', () => {
	it( 'renders simple template with no setAttributes function included', () => {
		const renderedTree = renderBlockTemplate(
			basicTemplate,
			fields,
			{
				textField: 'Test value',
			},
			renderField
		);

		render( renderedTree );

		const wrapper = screen.getByTestId( 'wrapper' );
		expect( wrapper.textContent ).toEqual( 'Something else here and a field: Test value' );

		const textControl = screen.getByText( 'Test value' );
		expect( textControl.tagName ).toEqual( 'SPAN' );
		expect( textControl.className ).toEqual( 'field--TextControl' );
	} );

	it( 'renders more complex template with no setAttributes function included', () => {
		const renderedTree = renderBlockTemplate(
			multipleFieldsTemplate,
			fields,
			{
				textField: 'Test value',
				numberField: 4,
			},
			renderField
		);

		render( renderedTree );

		const wrapper = screen.getByTestId( 'wrapper' );
		expect( wrapper.textContent ).toEqual( 'A field with content: Test valueAnd a number: 4' );

		const textControl = screen.getByText( 'Test value' );
		expect( textControl.tagName ).toEqual( 'SPAN' );
		expect( textControl.className ).toEqual( 'field--TextControl' );

		const numberControl = screen.getByText( '4' );
		expect( numberControl.tagName ).toEqual( 'SPAN' );
		expect( numberControl.className ).toEqual( 'field--NumberControl' );
	} );
} );
