/**
 * External dependencies
 */
import { render, screen } from '@testing-library/react';

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

window.ResizeObserver =
	window.ResizeObserver ||
	jest.fn().mockImplementation(() => ({
		disconnect: jest.fn(),
		observe: jest.fn(),
		unobserve: jest.fn(),
	}));

describe( 'FieldInspectorControls', () => {
	test( 'all supported fields render as expected', () => {
		const { fields } = allFieldsBlock;

		const {
			attributes,
			setAttributes,
		} = allFieldsEditComponentProps;

		render(
			<FieldInspectorControls
				fields={ fields }
				attributes={ attributes }
				setAttributes={ setAttributes }
			/>
		);

		expect( screen.getByTestId( 'pods-inspector' ).tagName ).toEqual( 'DIV' );
	} );
} );
