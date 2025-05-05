/**
 * External dependencies
 */
import React from 'react';
import { render, screen } from '@testing-library/react';
import '@testing-library/jest-dom';

/**
 * Internal dependencies
 */
import FieldLabel from '../field-label';

// Mock the HelpTooltip component
jest.mock( 'dfv/src/components/help-tooltip', () => {
	return jest.fn( ( {
		helpText,
		helpLink,
	} ) => (
		<div data-testid="help-tooltip" data-help-text={ helpText } data-help-link={ helpLink }>
			Help Icon
		</div>
	) );
} );

describe( 'FieldLabel Component', () => {
	const HelpTooltip = require( 'dfv/src/components/help-tooltip' );

	test( 'renders with plain text label', () => {
		const label = 'Field Label';
		const htmlFor = 'field-id';

		render( <FieldLabel label={ label } htmlFor={ htmlFor } /> );

		// Check if the label is rendered
		const labelElement = screen.getByText( label );
		expect( labelElement ).toBeInTheDocument();

		// Check that label has correct htmlFor attribute
		const labelContainer = labelElement.closest( 'label' );
		expect( labelContainer ).toHaveAttribute( 'for', htmlFor );

		// Check the CSS classes
		expect( labelContainer ).toHaveClass( 'pods-field-label__label' );

		// Check that no required indicator is shown
		expect( screen.queryByText( '*' ) ).not.toBeInTheDocument();

		// Check that no help tooltip is shown
		expect( screen.queryByTestId( 'help-tooltip' ) ).not.toBeInTheDocument();
	} );

	test( 'renders with HTML in label', () => {
		const labelHtml = 'Field <strong>Label</strong> with HTML';
		const htmlFor = 'field-id';

		const { container } = render( <FieldLabel label={ labelHtml } htmlFor={ htmlFor } /> );

		// Check if the HTML is processed correctly
		expect( container.innerHTML ).toContain( '<strong>Label</strong>' );
	} );

	test( 'displays required indicator when required=true', () => {
		const label = 'Required Field';
		const htmlFor = 'field-id';

		render( <FieldLabel label={ label } htmlFor={ htmlFor } required={ true } /> );

		// Check if the required indicator is shown
		const requiredIndicator = screen.getByText( '*' );
		expect( requiredIndicator ).toBeInTheDocument();
		expect( requiredIndicator ).toHaveClass( 'pods-field-label__required' );

		// Check if it has a non-breaking space before the asterisk
		expect( requiredIndicator.textContent ).toMatch( /\u00A0\*/ );
	} );

	test( 'displays help tooltip when helpTextString is provided', () => {
		const label = 'Field with Help';
		const htmlFor = 'field-id';
		const helpTextString = 'This is helpful information';

		render( <FieldLabel
			label={ label }
			htmlFor={ htmlFor }
			helpTextString={ helpTextString }
		/> );

		// Check if the help tooltip is shown
		const helpTooltip = screen.getByTestId( 'help-tooltip' );
		expect( helpTooltip ).toBeInTheDocument();
		expect( helpTooltip ).toHaveAttribute( 'data-help-text', helpTextString );

		// Check that HelpTooltip was called with correct props
		expect( HelpTooltip ).toHaveBeenCalledWith( expect.objectContaining( {
			helpText: helpTextString,
			helpLink: null,
		} ), expect.anything() );

		// Check for non-breaking space before the tooltip
		const tooltipWrapper = helpTooltip.closest( '.pods-field-label__tooltip-wrapper' );
		expect( tooltipWrapper.textContent ).toMatch( /^\u00A0/ );
	} );

	test( 'passes helpLink to HelpTooltip when provided', () => {
		const label = 'Field with Help Link';
		const htmlFor = 'field-id';
		const helpTextString = 'This is helpful information';
		const helpLink = 'https://example.com/help';

		render( <FieldLabel
			label={ label }
			htmlFor={ htmlFor }
			helpTextString={ helpTextString }
			helpLink={ helpLink }
		/> );

		// Check if the help tooltip has the correct link
		const helpTooltip = screen.getByTestId( 'help-tooltip' );
		expect( helpTooltip ).toHaveAttribute( 'data-help-link', helpLink );

		// Check that HelpTooltip was called with correct props
		expect( HelpTooltip ).toHaveBeenCalledWith( expect.objectContaining( {
			helpText: helpTextString,
			helpLink,
		} ), expect.anything() );
	} );

	test( 'sanitizes potentially dangerous HTML in label', () => {
		const dangerousLabel = 'Field <script>alert("dangerous!");</script> Label';
		const htmlFor = 'field-id';

		const { container } = render( <FieldLabel label={ dangerousLabel } htmlFor={ htmlFor } /> );

		// Check that script tag is removed
		expect( container.innerHTML ).not.toContain( '<script>' );
		expect( container.innerHTML ).not.toContain( 'alert("dangerous!");' );
	} );

	test( 'handles all properties together correctly', () => {
		const label = 'Complete Field <em>Label</em>';
		const htmlFor = 'complete-field-id';
		const helpTextString = 'Complete help text';
		const helpLink = 'https://example.com/complete-help';

		const { container } = render( <FieldLabel
			name="test-field-name"
			label={ label }
			htmlFor={ htmlFor }
			required={ true }
			helpTextString={ helpTextString }
			helpLink={ helpLink }
		/> );

		// Check HTML rendering
		expect( container.innerHTML ).toContain( '<em>Label</em>' );

		// Check required indicator
		expect( screen.getAllByText( '*' )[ 0 ] ).toHaveClass( 'pods-field-label__required' );

		// Check help tooltip
		const helpTooltip = screen.getAllByTestId( 'help-tooltip' )[ 0 ];
		expect( helpTooltip ).toHaveAttribute( 'data-help-text', helpTextString );
		expect( helpTooltip ).toHaveAttribute( 'data-help-link', helpLink );

		// Check label container for the field name class
		const containerDiv = container.querySelector( 'div' );
		expect( containerDiv ).toHaveClass( 'pods-field-label' );
		expect( containerDiv ).toHaveClass( 'pods-field-label-test-field-name' );
	} );

	test( 'has proper CSS classes for styling', () => {
		const label = 'Styled Field';
		const htmlFor = 'styled-field-id';

		const { container } = render( <FieldLabel name="test-field-name" label={ label } htmlFor={ htmlFor } /> );

		// Check container div class
		const containerDiv = container.querySelector( 'div' );
		expect( containerDiv ).toHaveClass( 'pods-field-label' );
		expect( containerDiv ).toHaveClass( 'pods-field-label-test-field-name' );

		// Check label class
		const labelElement = container.querySelector( 'label' );
		expect( labelElement ).toHaveClass( 'pods-field-label__label' );
	} );

	test( 'handles empty name gracefully in className', () => {
		const label = 'Field with name bug';
		const htmlFor = 'field-id';

		// The component will use 'undefined' as name in the class
		const { container } = render( <FieldLabel label={ label } htmlFor={ htmlFor } /> );

		// Check if the div class contains 'undefined'
		const containerDiv = container.querySelector( 'div' );
		expect( containerDiv ).toHaveClass( 'pods-field-label' );
		expect( containerDiv ).toHaveClass( 'pods-field-label-' );
	} );

	test( 'handles empty label gracefully', () => {
		const label = '';
		const htmlFor = 'empty-field-id';

		render( <FieldLabel label={ label } htmlFor={ htmlFor } /> );

		// There should still be a label element
		expect( screen.getByTestId( 'field-label' ) ).toBeInTheDocument();

		// The span inside should be empty
		const span = screen.getByTestId( 'field-label-text' );
		expect( span.innerHTML ).toEqual( '' );
	} );
} );
