/**
 * External dependencies
 */
import React from 'react';
import { render, screen } from '@testing-library/react';
import '@testing-library/jest-dom';

/**
 * Internal dependencies
 */
import FieldSet from '../field-set';

describe( 'FieldSet Component', () => {
	// Sample test data
	const defaultProps = {
		storeKey: 'test-store',
		fields: [
			{
				id: 12345,
				name: 'text_field',
				type: 'text',
				label: 'Text Field',
				default: 'Default Text',
			}, {
				id: 12345,
				name: 'number_field',
				type: 'number',
				label: 'Number Field',
				default: 42,
			},
		],
		podType: 'post_type',
		podName: 'post',
		allPodFields: [
			{
				id: 12345,
				name: 'text_field',
				type: 'text',
				label: 'Text Field',
				default: 'Default Text',
			}, {
				id: 12345,
				name: 'number_field',
				type: 'number',
				label: 'Number Field',
				default: 42,
			}, {
				id: 12345,
				name: 'checkbox_field',
				type: 'boolean',
				label: 'Checkbox Field',
				default: true,
			},
		],
		allPodValues: {
			text_field: 'Sample Text',
			number_field: 123,
		},
		setOptionValue: jest.fn(),
		setOptionsValues: jest.fn(),
	};

	test( 'passes correct values to FieldWrapper', () => {
		render( <FieldSet { ...defaultProps } /> );

		// Check displayed values
		expect( screen.getByLabelText( 'Text Field' ).value ).toEqual( 'Sample Text' );
		expect( screen.getByLabelText( 'Number Field' ).value ).toEqual( '123' );
	} );

	test( 'handles undefined values by applying defaults on mount', () => {
		// Create a modified version of props with an undefined value
		const propsWithUndefined = {
			...defaultProps,
			allPodValues: {
				// text_field is undefined
				number_field: 123,
			},
		};

		render( <FieldSet { ...propsWithUndefined } /> );

		// The useEffect should set the default value for text_field
		expect( propsWithUndefined.allPodValues.text_field ).toBe( 'Default Text' );
	} );

	test( 'applies default values for boolean group subfields', () => {
		// Create props with a boolean group field with undefined subfield values
		const propsWithUndefinedSubfields = {
			...defaultProps,
			fields: [
				{
					id: 12345,
					name: 'group_field',
					type: 'boolean_group',
					label: 'Group Field',
					boolean_group: [
						{
							name: 'subfield1',
							type: 'boolean',
							label: 'Subfield 1',
							default: 1,
						}, {
							name: 'subfield2',
							type: 'boolean',
							label: 'Subfield 2',
							default: 0,
						},
					],
				},
			],
			allPodFields: [
				{
					id: 12345,
					name: 'group_field',
					type: 'boolean_group',
					label: 'Group Field',
					boolean_group: [
						{
							name: 'subfield1',
							type: 'boolean',
							label: 'Subfield 1',
							default: 1,
						}, {
							name: 'subfield2',
							type: 'boolean',
							label: 'Subfield 2',
							default: 0,
						},
					],
				},
			],
			allPodValues: {
				// subfield1 and subfield2 are undefined
			},
		};

		render( <FieldSet { ...propsWithUndefinedSubfields } /> );

		// The useEffect should set the default values for subfields
		expect( propsWithUndefinedSubfields.allPodValues.subfield1 ).toBe( 1 );
		expect( propsWithUndefinedSubfields.allPodValues.subfield2 ).toBe( 0 );
	} );

	test( 'does not apply default values for null defaults', () => {
		// Create props with null default values
		const propsWithNullDefaults = {
			...defaultProps,
			fields: [
				{
					id: 12345,
					name: 'null_default_field',
					type: 'text',
					label: 'Null Default Field',
					default: null,
				},
			],
			allPodFields: [
				{
					id: 12345,
					name: 'null_default_field',
					type: 'text',
					label: 'Null Default Field',
					default: null,
				},
			],
			allPodValues: {
				// null_default_field is undefined
			},
		};

		render( <FieldSet { ...propsWithNullDefaults } /> );

		// The field should still be undefined because the default is null
		expect( propsWithNullDefaults.allPodValues.null_default_field ).toBeUndefined();
	} );

	test( 'does not modify falsy values that are defined', () => {
		// Create props with falsy but defined values
		const propsWithFalsyValues = {
			...defaultProps,
			fields: [
				{
					id: 12345,
					name: 'empty_string_field',
					type: 'text',
					label: 'Empty String Field',
					default: 'Default Text',
				}, {
					id: 12345,
					name: 'zero_field',
					type: 'number',
					label: 'Zero Field',
					default: 42,
				},
			],
			allPodValues: {
				empty_string_field: '', // Defined but empty string
				zero_field: 0, // Defined but zero
			},
		};

		render( <FieldSet { ...propsWithFalsyValues } /> );

		// Values should not be changed to defaults
		expect( propsWithFalsyValues.allPodValues.empty_string_field ).toBe( '' );
		expect( propsWithFalsyValues.allPodValues.zero_field ).toBe( 0 );
	} );
} );
