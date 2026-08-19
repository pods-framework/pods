/**
 * External dependencies
 */
import { render } from '@testing-library/react';
import '@testing-library/jest-dom';

/**
 * Internal dependencies
 */
import FullSelect from '../full-select';

const BASE_PROPS = {
	isTaggable: false,
	shouldRenderValue: true,
	formattedOptions: [
		{ label: 'Option One', value: '1' },
		{ label: 'Option Two', value: '2' },
	],
	value: undefined,
	addNewItem: () => {},
	setValue: () => {},
	placeholder: 'Select something',
	isMulti: false,
	isClearable: true,
	isReadOnly: false,
};

describe( 'FullSelect label association', () => {
	it( 'applies inputId to a labelable control so `<label for>` resolves', () => {
		const { container } = render(
			<FullSelect { ...BASE_PROPS } inputId="pods-form-ui-my_field" />
		);

		const target = container.querySelector( '#pods-form-ui-my_field' );

		// The id must exist and must land on a labelable form control,
		// not on a wrapper <div> (which `<label for>` cannot address and
		// which would shadow the control in document.getElementById()).
		expect( target ).not.toBeNull();
		expect( target.tagName ).toBe( 'INPUT' );
	} );

	it( 'renders exactly one element carrying the field id', () => {
		const { container } = render(
			<FullSelect { ...BASE_PROPS } inputId="pods-form-ui-my_field" />
		);

		expect(
			container.querySelectorAll( '#pods-form-ui-my_field' )
		).toHaveLength( 1 );
	} );
} );
