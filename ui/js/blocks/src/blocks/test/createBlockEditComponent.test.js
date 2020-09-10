/**
 * External dependencies
 */
import { mount } from 'enzyme';

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
		const wrapper = mount(
			<SimpleEditComponent { ...simpleEditComponentProps } />
		);

		// Look for the expected components
		expect( wrapper.find( 'div' ) ).toHaveLength( 3 );
	} );
} );
