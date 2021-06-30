/**
 * External dependencies
 */
import { mount } from 'enzyme';

/**
 * WordPress dependencies
 */
import { RichText } from '@wordpress/block-editor';

import {
	CheckboxControl,
	DateTimePicker,
	TextControl,
	TextareaControl,
	RadioControl,
} from '@wordpress/components';

/**
 * Internal dependencies
 */
import FieldInspectorControls from '../FieldInspectorControls';
import CheckboxGroup from '../../../components/CheckboxGroup';
import NumberControl from '../../../components/NumberControl';

import {
	allFieldsBlock,
	allFieldsBlockProps,
} from '../../../testData';

const allFieldsEditComponentProps = {
	...allFieldsBlockProps,
	setAttributes: jest.fn(),
};

describe( 'FieldInspectorControls', () => {
	test( 'all supported fields render as expected', () => {
		const { fields } = allFieldsBlock;

		const {
			attributes,
			setAttributes,
		} = allFieldsEditComponentProps;

		const wrapper = mount(
			<FieldInspectorControls
				fields={ fields }
				attributes={ attributes }
				setAttributes={ setAttributes }
			/>
		);

		// Several components inside core's DateTimePicker cause React to log
		// the "Warning: componentWillReceiveProps has been renamed, and is not recommended for use."
		// warning, so we need to check that console.warn has been called... I don't know
		// another way around the jest-console package causing tests to fail.
		expect( console ).toHaveWarned();

		expect( wrapper.find( TextControl ) ).toHaveLength( 1 );
		expect( wrapper.find( TextareaControl ) ).toHaveLength( 1 );
		expect( wrapper.find( RichText ) ).toHaveLength( 1 );
		expect( wrapper.find( CheckboxControl ).first().props().label ).toBe( 'Checkbox Test' );
		expect( wrapper.find( CheckboxGroup ) ).toHaveLength( 1 );
		expect( wrapper.find( RadioControl ) ).toHaveLength( 1 );
		expect( wrapper.find( DateTimePicker ) ).toHaveLength( 1 );
		expect( wrapper.find( NumberControl ) ).toHaveLength( 1 );
	} );
} );
