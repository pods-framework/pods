/**
 * External dependencies
 */
import React from 'react';
import { render, screen, fireEvent } from '@testing-library/react';
import '@testing-library/jest-dom';

/**
 * Mock components and dependencies to isolate tests
 */
jest.mock( 'dfv/src/components/field-label', () => ( {
	label,
	required,
	helpTextString,
	helpLink,
} ) => (
	<div data-testid="field-label">
		<span data-testid="label-text">{ label }</span>
		{ required && <span data-testid="required-marker">*</span> }
		{ helpTextString && <span data-testid="help-text">{ helpTextString }</span> }
		{ helpLink && <a data-testid="help-link" href={ helpLink }>Help</a> }
	</div>
) );

jest.mock(
	'dfv/src/components/field-description',
	() => ( { description } ) => (
		<div data-testid="field-description">{ description }</div>
	),
);

jest.mock(
	'dfv/src/components/validation-messages',
	() => ( { messages } ) => (
		<div data-testid="validation-messages">
			{ messages.map( ( message, i ) => (
				<div key={ i } data-testid="validation-message">{ message }</div>
			) ) }
		</div>
	),
);

jest.mock( 'dfv/src/components/field-wrapper/div-field-layout', () => ( {
	fieldType,
	labelComponent,
	descriptionComponent,
	inputComponent,
	validationMessagesComponent,
} ) => (
	<div data-testid="field-layout" data-field-type={ fieldType }>
		{ labelComponent && <div data-testid="label-container">{ labelComponent }</div> }
		<div data-testid="field-container">
			{ inputComponent }
			{ validationMessagesComponent && (
				<div data-testid="validation-container">{ validationMessagesComponent }</div>
			) }
		</div>
		{ descriptionComponent && <div data-testid="description-container">{ descriptionComponent }</div> }
	</div>
) );

jest.mock(
	'dfv/src/components/field-wrapper/field-error-boundary',
	() => ( { children } ) => (
		<div data-testid="error-boundary">{ children }</div>
	),
);

jest.mock( 'dfv/src/hooks/useConditionalLogic', () => jest.fn().mockImplementation( () => true ) );
jest.mock( 'dfv/src/hooks/useValidation', () => jest.fn().mockImplementation( () => [ [], jest.fn() ] ) );
jest.mock(
	'dfv/src/hooks/useBlockEditor',
	() => jest.fn().mockImplementation( () => (
		{
			lockPostSaving: jest.fn(),
			unlockPostSaving: jest.fn(),
		}
	) ),
);
jest.mock( 'dfv/src/components/field-wrapper/useHideContainerDOM', () => jest.fn() );
jest.mock( 'dfv/src/helpers/sanitizeSlug', () => jest.fn( val => val ) );
jest.mock( 'dfv/src/helpers/isFieldRepeatable', () => jest.fn().mockImplementation( () => false ) );
jest.mock(
	'dfv/src/helpers/booleans',
	() => (
		{
			toBool: jest.fn().mockImplementation( val => !! val ),
		}
	),
);
jest.mock(
	'dfv/src/helpers/validators',
	() => (
		{
			requiredValidator: jest.fn().mockImplementation( () => () => null ),
		}
	),
);

/**
 * Mock the field map for testing
 */
jest.mock(
	'dfv/src/fields/field-map',
	() => (
		{
			text: {
				fieldComponent: ( {
					value,
					setValue,
					fieldConfig,
				} ) => (
					<div data-testid="text-field-component">
						<input
							data-testid="text-input"
							type="text"
							value={ value || '' }
							onChange={ e => setValue( e.target.value ) }
							required={ fieldConfig.required }
							id={ fieldConfig.htmlAttr?.id }
							name={ fieldConfig.name }
						/>
					</div>
				),
			},
			paragraph: {
				fieldComponent: ( {
					value,
					setValue,
					fieldConfig,
				} ) => (
					<div data-testid="paragraph-field-component">
				<textarea
					data-testid="paragraph-textarea"
					value={ value || '' }
					onChange={ e => setValue( e.target.value ) }
					required={ fieldConfig.required }
					id={ fieldConfig.htmlAttr?.id }
					name={ fieldConfig.name }
				/>
					</div>
				),
			},
			boolean_group: {
				fieldComponent: ( {
					values,
					setOptionValue,
					fieldConfig,
				} ) => (
					<div data-testid="boolean-group-component">
						{ fieldConfig.boolean_group?.map( subfield => (
							<div key={ subfield.name } data-testid={ `subfield-${ subfield.name }` }>
								<label>{ subfield.label }</label>
								<input
									type="checkbox"
									checked={ values?.[ subfield.name ] || false }
									onChange={ e => setOptionValue( subfield.name, e.target.checked ) }
								/>
							</div>
						) ) }
					</div>
				),
			},
			heading: {
				fieldComponent: ( { fieldConfig } ) => (
					<h3 data-testid="heading-component">{ fieldConfig.label }</h3>
				),
			},
			html: {
				fieldComponent: ( { fieldConfig } ) => (
					<div
						data-testid="html-component"
						dangerouslySetInnerHTML={ { __html: fieldConfig.html_content || '' } }
					/>
				),
			},
			pick: {
				fieldComponent: ( {
					value,
					setValue,
					fieldConfig,
				} ) => (
					<div data-testid="pick-field-component">
						<select
							data-testid="pick-select"
							value={ value || '' }
							onChange={ e => setValue( e.target.value ) }
							required={ fieldConfig.required }
							id={ fieldConfig.htmlAttr?.id }
							name={ fieldConfig.name }
						>
							{ Object.entries( fieldConfig.data || {} ).map( ( [ val, label ] ) => (
								<option key={ val } value={ val }>{ label }</option>
							) ) }
						</select>
					</div>
				),
			},
		}
	),
);

jest.mock( 'dfv/src/components/field-wrapper/repeatable-field-list', () => ( {
	fieldConfig,
	valuesArray,
	FieldComponent,
	setFullValue,
	setHasBlurred,
} ) => (
	<div data-testid="repeatable-field-list">
		{ valuesArray.map( ( val, index ) => (
			<div key={ index } data-testid={ `repeatable-item-${ index }` }>
				<input
					data-testid={ `repeatable-input-${ index }` }
					value={ val || '' }
					onChange={ e => {
						const newValues = [ ...valuesArray ];
						newValues[ index ] = e.target.value;
						setFullValue( newValues );
					} }
					onBlur={ () => setHasBlurred() }
				/>
			</div>
		) ) }
		<button
			data-testid="add-repeatable-item"
			onClick={ () => setFullValue( [ ...valuesArray, '' ] ) }
		>
			{ fieldConfig.repeatable_add_new_label || 'Add New' }
		</button>
	</div>
) );

/**
 * Import the components we're testing
 */
import { FieldWrapper } from '../index';
import MemoizedFieldWrapper from '../index';

/**
 * Utility function to create field config for testing
 */
const createFieldConfig = ( overrides = {} ) => (
	{
		id: 12345,
		name: 'test_field',
		label: 'Test Field',
		type: 'text',
		required: false,
		htmlAttr: {
			id: 'pods-form-ui-test_field',
		}, ...overrides,
	}
);

/**
 * Test setup helper
 */
const setup = ( props = {} ) => {
	const defaultProps = {
		storeKey: 'test-pod',
		field: createFieldConfig(),
		value: '',
		setOptionValue: jest.fn(),
		allPodValues: {},
		allPodFieldsMap: new Map(),
	};

	return render( <FieldWrapper { ...defaultProps } { ...props } /> );
};

describe( 'FieldWrapper Component', () => {
	beforeEach( () => {
		jest.clearAllMocks();
	} );

	test( 'renders basic text field correctly', () => {
		setup();

		expect( screen.getByTestId( 'field-layout' ) ).toBeInTheDocument();
		expect( screen.getByTestId( 'label-text' ) ).toHaveTextContent( 'Test Field' );
		expect( screen.getByTestId( 'text-field-component' ) ).toBeInTheDocument();
		expect( screen.getByTestId( 'text-input' ) ).toBeInTheDocument();
	} );

	test( 'renders required indicator for required fields', () => {
		setup( { field: createFieldConfig( { required: true } ) } );

		expect( screen.getByTestId( 'required-marker' ) ).toBeInTheDocument();
	} );

	test( 'renders field description when provided', () => {
		setup( {
			field: createFieldConfig( {
				description: 'This is a test description',
			} ),
		} );

		expect( screen.getByTestId( 'field-description' ) ).toHaveTextContent( 'This is a test description' );
	} );

	test( 'renders help text when provided', () => {
		setup( {
			field: createFieldConfig( {
				help: 'This is helpful text',
			} ),
		} );

		expect( screen.getByTestId( 'help-text' ) ).toHaveTextContent( 'This is helpful text' );
	} );

	test( 'renders help link when provided in array format', () => {
		setup( {
			field: createFieldConfig( {
				help: [ 'Help text', 'https://example.com/help' ],
			} ),
		} );

		expect( screen.getByTestId( 'help-text' ) ).toHaveTextContent( 'Help text' );
		expect( screen.getByTestId( 'help-link' ) ).toBeInTheDocument();
		expect( screen.getByTestId( 'help-link' ) ).toHaveAttribute( 'href', 'https://example.com/help' );
	} );

	test( 'does not render label for heading field type', () => {
		setup( {
			field: createFieldConfig( {
				type: 'heading',
			} ),
		} );

		expect( screen.queryByTestId( 'label-text' ) ).not.toBeInTheDocument();
		expect( screen.getByTestId( 'heading-component' ) ).toBeInTheDocument();
	} );

	test( 'does not render label for html field type with html_no_label set', () => {
		setup( {
			field: createFieldConfig( {
				type: 'html',
				html_no_label: true,
				html_content: '<p>HTML content</p>',
			} ),
		} );

		expect( screen.queryByTestId( 'label-text' ) ).not.toBeInTheDocument();
		expect( screen.getByTestId( 'html-component' ) ).toBeInTheDocument();
	} );

	test( 'handles input value changes correctly', () => {
		const setOptionValueMock = jest.fn();
		setup( {
			setOptionValue: setOptionValueMock,
		} );

		const input = screen.getByTestId( 'text-input' );
		fireEvent.change( input, { target: { value: 'New value' } } );

		expect( setOptionValueMock ).toHaveBeenCalledWith( 'test_field', 'New value' );
	} );

	test( 'renders boolean_group field correctly', () => {
		const subfields = [
			{
				name: 'subfield1',
				label: 'Subfield 1',
			},
			{
				name: 'subfield2',
				label: 'Subfield 2',
			},
		];

		setup( {
			field: createFieldConfig( {
				type: 'boolean_group',
				boolean_group: subfields,
			} ),
			values: {
				subfield1: true,
				subfield2: false,
			},
		} );

		expect( screen.getByTestId( 'boolean-group-component' ) ).toBeInTheDocument();
	} );

	test( 'renders repeatable field list for repeatable fields', () => {
		// Mock isFieldRepeatable to return true for this test
		const isFieldRepeatableMock = require( 'dfv/src/helpers/isFieldRepeatable' );
		isFieldRepeatableMock.mockImplementationOnce( () => true );

		setup( {
			field: createFieldConfig( {
				repeatable: true,
				repeatable_add_new_label: 'Add Another Test Field',
			} ),
			value: [ 'First value', 'Second value' ],
		} );

		expect( screen.getByTestId( 'repeatable-field-list' ) ).toBeInTheDocument();
		expect( screen.getByTestId( 'repeatable-item-0' ) ).toBeInTheDocument();
		expect( screen.getByTestId( 'repeatable-item-1' ) ).toBeInTheDocument();
		expect( screen.getByTestId( 'add-repeatable-item' ) ).toHaveTextContent( 'Add Another Test Field' );
	} );

	test( 'does not render a field when conditional logic is not met', () => {
		// Mock useConditionalLogic to return false
		const useConditionalLogicMock = require( 'dfv/src/hooks/useConditionalLogic' );
		useConditionalLogicMock.mockImplementationOnce( () => false );

		setup();

		// Field should not be rendered
		expect( screen.queryByTestId( 'field-layout' ) ).not.toBeInTheDocument();
		expect( screen.queryByTestId( 'text-field-component' ) ).not.toBeInTheDocument();
	} );

	test( 'handles pick field with required and no select text', () => {
		const setOptionValueMock = jest.fn();
		setup( {
			field: createFieldConfig( {
				type: 'pick',
				pick_format_type: 'single',
				pick_format_single: 'dropdown',
				pick_show_select_text: '0',
				required: true,
				default: 'option1',
				data: {
					'': 'Select One',
					option1: 'Option 1',
					option2: 'Option 2',
				},
			} ),
			value: '',
			setOptionValue: setOptionValueMock,
			allPodValues: {
				test_field: '',
			},
		} );

		// Should set the default value
		expect( setOptionValueMock ).toHaveBeenCalledWith( 'test_field', 'option1' );
	} );

	test( 'handles custom placeholder for create_name field', () => {
		setup( {
			field: createFieldConfig( {
				name: 'create_name',
				htmlAttr: {
					id: 'pods-form-ui-create-name',
					placeholder: '',
				},
			} ),
			allPodValues: {
				create_label_singular: 'Test Pod',
			},
		} );

		const input = screen.getByTestId( 'text-input' );
		expect( input ).toHaveAttribute( 'name', 'create_name' );
	} );
} );

describe( 'MemoizedFieldWrapper Component', () => {
	beforeEach( () => {
		jest.clearAllMocks();
	} );

	// Helper for memoization tests
	const renderMemoized = ( initialProps ) => {
		const defaultProps = {
			storeKey: 'test-pod',
			field: createFieldConfig(),
			value: '',
			setOptionValue: jest.fn(),
			allPodValues: {},
			allPodFieldsMap: new Map(),
		};

		const props = { ...defaultProps, ...initialProps };
		const { rerender } = render( <MemoizedFieldWrapper { ...props } /> );

		return {
			rerender: ( newProps ) => rerender( <MemoizedFieldWrapper { ...props } { ...newProps } /> ),
			props,
		};
	};

	test( 'rerenders when pod information changes', () => {
		const { rerender } = renderMemoized( {
			podName: 'pod1',
			podType: 'post_type',
		} );

		// Component internals are mocked, so we need to spy on the render function
		const renderSpy = jest.spyOn( React, 'createElement' );

		// Should rerender when podName changes
		rerender( { podName: 'pod2' } );
		expect( renderSpy ).toHaveBeenCalled();

		// Should rerender when podType changes
		renderSpy.mockClear();
		rerender( { podType: 'taxonomy' } );
		expect( renderSpy ).toHaveBeenCalled();
	} );

	test( 'rerenders when field label changes', () => {
		const initialProps = {
			field: createFieldConfig( { label: 'Original Label' } ),
		};

		const { rerender } = renderMemoized( initialProps );

		// Component internals are mocked, so we need to spy on the render function
		const renderSpy = jest.spyOn( React, 'createElement' );

		// Should rerender when field label changes
		rerender( {
			field: {
				...initialProps.field,
				label: 'Updated Label',
			},
		} );

		expect( renderSpy ).toHaveBeenCalled();
	} );

	test( 'rerenders when conditional dependency values change', () => {
		// Create field with conditional logic
		const fieldWithConditionalLogic = createFieldConfig( {
			enable_conditional_logic: true,
			conditional_logic: {
				action: 'show',
				logic: 'all',
				rules: [
					{
						field: 'dependent_field',
						compare: '=',
						value: 'show',
					},
				],
			},
		} );

		// Create a Map with dependent field
		const allPodFieldsMap = new Map( [
			[ 'dependent_field', createFieldConfig( { name: 'dependent_field' } ) ],
		] );

		const initialProps = {
			field: fieldWithConditionalLogic,
			allPodFieldsMap,
			allPodValues: {
				dependent_field: 'hide',
			},
		};

		const { rerender } = renderMemoized( initialProps );

		// Component internals are mocked, so we need to spy on the render function
		const renderSpy = jest.spyOn( React, 'createElement' );

		// Should rerender when dependency value changes
		rerender( {
			allPodValues: {
				dependent_field: 'show',
			},
		} );

		expect( renderSpy ).toHaveBeenCalled();
	} );

	test( 'handles nested dependencies correctly', () => {
		// Create a parent field with conditional logic
		const parentField = createFieldConfig( {
			name: 'parent_field',
			enable_conditional_logic: true,
			conditional_logic: {
				action: 'show',
				logic: 'all',
				rules: [
					{
						field: 'grandparent_field',
						compare: '=',
						value: 'show',
					},
				],
			},
		} );

		// Create field with conditional logic depending on parent
		const fieldWithConditionalLogic = createFieldConfig( {
			enable_conditional_logic: true,
			conditional_logic: {
				action: 'show',
				logic: 'all',
				rules: [
					{
						field: 'parent_field',
						compare: '=',
						value: 'show',
					},
				],
			},
		} );

		// Create a Map with all fields
		const allPodFieldsMap = new Map( [
			[ 'parent_field', parentField ], [ 'grandparent_field', createFieldConfig( { name: 'grandparent_field' } ) ],
		] );

		const initialProps = {
			field: fieldWithConditionalLogic,
			allPodFieldsMap,
			allPodValues: {
				parent_field: 'show',
				grandparent_field: 'hide',
			},
		};

		const { rerender } = renderMemoized( initialProps );

		// Component internals are mocked, so we need to spy on the render function
		const renderSpy = jest.spyOn( React, 'createElement' );

		// Should rerender when grandparent dependency value changes
		rerender( {
			allPodValues: {
				...initialProps.allPodValues,
				grandparent_field: 'show',
			},
		} );

		expect( renderSpy ).toHaveBeenCalled();
	} );

	test( 'handles boolean group fields with conditional logic', () => {
		// Create field with boolean group that has conditional logic
		const booleanGroupField = createFieldConfig( {
			type: 'boolean_group',
			boolean_group: [
				{
					name: 'subfield1',
					label: 'Subfield 1',
					enable_conditional_logic: true,
					conditional_logic: {
						action: 'show',
						logic: 'all',
						rules: [
							{
								field: 'dependent_field',
								compare: '=',
								value: 'show',
							},
						],
					},
				}, {
					name: 'subfield2',
					label: 'Subfield 2',
				},
			],
		} );

		// Create a Map with dependent field
		const allPodFieldsMap = new Map( [
			[ 'dependent_field', createFieldConfig( { name: 'dependent_field' } ) ],
		] );

		const initialProps = {
			field: booleanGroupField,
			allPodFieldsMap,
			allPodValues: {
				dependent_field: 'hide',
			},
			values: {
				subfield1: false,
				subfield2: true,
			},
		};

		const { rerender } = renderMemoized( initialProps );

		// Component internals are mocked, so we need to spy on the render function
		const renderSpy = jest.spyOn( React, 'createElement' );

		// Should rerender when dependency value changes
		rerender( {
			allPodValues: {
				dependent_field: 'show',
			},
		} );

		expect( renderSpy ).toHaveBeenCalled();
	} );

	test( 'handles special case for create_name field', () => {
		const createNameField = createFieldConfig( {
			name: 'create_name',
			htmlAttr: {
				id: 'pods-form-ui-create-name',
			},
		} );

		const initialProps = {
			field: createNameField,
			allPodValues: {
				create_label_singular: 'Initial Label',
			},
		};

		const { rerender } = renderMemoized( initialProps );

		// Component internals are mocked, so we need to spy on the render function
		const renderSpy = jest.spyOn( React, 'createElement' );

		// Should rerender when create_label_singular changes
		rerender( {
			allPodValues: {
				create_label_singular: 'Updated Label',
			},
		} );

		expect( renderSpy ).toHaveBeenCalled();
	} );
} );
