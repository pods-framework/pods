/**
 * External dependencies
 */
import React from 'react';
import { noop } from 'lodash';

/**
 * Internal dependencies
 */
import ModalButton from '@moderntribe/common/elements/modal-button/element';

describe( 'Modal Button Element', () => {
	it( 'renders a modal button', () => {
		const component = renderer.create( <ModalButton /> );
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	it( 'renders a modal button with class', () => {
		const component = renderer.create( <ModalButton className="test-class" /> );
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	it( 'renders a modal button with onClick handler', () => {
		const component = renderer.create( <ModalButton onClick={ noop } /> );
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	it( 'renders a modal button with label', () => {
		const component = renderer.create( <ModalButton label="Test Label" /> );
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	it( 'executes onClick and onOpen handlers', () => {
		const props = {
			onClick: jest.fn(),
			onOpen: jest.fn(),
		};

		const component = mount( <ModalButton { ...props } /> );
		component.find( 'button.tribe-editor__modal-button__button' ).simulate( 'click' );
		expect( props.onClick ).toHaveBeenCalled();
		expect( props.onClick ).toHaveBeenCalledTimes( 1 );
		expect( props.onOpen ).toHaveBeenCalled();
		expect( props.onOpen ).toHaveBeenCalledTimes( 1 );
	} );
} );
