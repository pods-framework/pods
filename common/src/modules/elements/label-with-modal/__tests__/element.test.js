/**
 * External dependencies
 */
import React from 'react';

/**
 * Internal dependencies
 */
import LabelWithModal from '@moderntribe/common/elements/label-with-modal/element';

describe( 'Label With Modal Element', () => {
	it( 'renders a label with modal', () => {
		const component = renderer.create( <LabelWithModal /> );
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	it( 'renders a label with modal with class', () => {
		const component = renderer.create( <LabelWithModal className="test-class" /> );
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	it( 'renders a label with modal with label', () => {
		const component = renderer.create( <LabelWithModal label="test label" /> );
		expect( component.toJSON() ).toMatchSnapshot();
	} );
} );
