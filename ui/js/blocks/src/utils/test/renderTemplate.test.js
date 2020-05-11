/**
 * External dependencies
 */
import { mount } from 'enzyme';

/**
 * Internal dependencies
 */
import renderTemplate from '../renderTemplate';

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

describe( 'renderTemplate', () => {
	it( 'renders simple template with no setAttributes function included', () => {
		const renderedTree = renderTemplate(
			basicTemplate,
			fields,
			{
				textField: 'Test value',
			},
			renderField
		);

		const wrapper = mount( renderedTree );

		expect( wrapper.find( '.field--TextControl' ) ).toHaveLength( 1 );
		expect( wrapper.find( '.field--TextControl' ).text() ).toBe( 'Test value' );
	} );

	it( 'renders more complex template with no setAttributes function included', () => {
		const renderedTree = renderTemplate(
			multipleFieldsTemplate,
			fields,
			{
				textField: 'Test value',
				numberField: 4,
			},
			renderField
		);

		const wrapper = mount( renderedTree );

		expect( wrapper.find( 'div' ) ).toHaveLength( 3 );
		expect( wrapper.find( '.field--TextControl' ).text() ).toBe( 'Test value' );
		expect( wrapper.find( '.field--NumberControl' ).text() ).toBe( '4' );
	} );
} );
