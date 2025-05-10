/**
 * External dependencies
 */
import React from 'react';
import { render, screen } from '@testing-library/react';
import '@testing-library/jest-dom';

/**
 * Internal dependencies
 */
import HelpTooltip from '../help-tooltip';

// Mock Tippy component
jest.mock( '@tippyjs/react', () => {
	return jest.fn( ( {
		children,
		content,
		className,
		trigger,
		interactive,
		zIndex,
	} ) => (
		<div className={ className } data-testid="tippy-wrapper" data-z-index={ zIndex } data-interactive={ interactive } data-trigger={ trigger }>
			{ children }
			<div data-testid="tippy-content" className="tippy-content">
				{ content }
			</div>
		</div>
	) );
} );

// Mock Dashicon
jest.mock(
	'@wordpress/components',
	() => (
		{
			Dashicon: jest.fn( ( { icon } ) => (
				<span data-testid={ `dashicon-${ icon }` } className={ `dashicon dashicon-${ icon }` }>
      { icon }
    </span>
			) ),
		}
	),
);

describe( 'HelpTooltip Component', () => {
	// Import the mocked modules to use in tests
	const Tippy = require( '@tippyjs/react' );
	const sanitizeHtml = require( 'sanitize-html' );
	const { Dashicon } = require( '@wordpress/components' );

	beforeEach( () => {
		// Clear mock implementations before each test
		Tippy.mockClear();
		Dashicon.mockClear();
	} );

	test( 'renders help icon button', () => {
		render( <HelpTooltip helpText="Help text" /> );

		// Check if the help icon is rendered
		const helpIcon = screen.getByTestId( 'dashicon-editor-help' );
		expect( helpIcon ).toBeInTheDocument();

		// Check if the button wrapper has correct attributes
		const button = screen.getByRole( 'button' );
		expect( button ).toHaveClass( 'pods-help-tooltip__icon' );
		expect( button ).toHaveAttribute( 'tabIndex', '0' );
	} );

	test( 'renders Tippy component with correct props', () => {
		render( <HelpTooltip helpText="Help text" /> );

		// Check Tippy props
		expect( Tippy ).toHaveBeenCalledWith( expect.objectContaining( {
			className: 'pods-help-tooltip',
			trigger: 'click',
			zIndex: 100001,
			interactive: true,
		} ), expect.anything() );

		// Check if the wrapper has the correct class
		const tippyWrapper = screen.getByTestId( 'tippy-wrapper' );
		expect( tippyWrapper ).toHaveClass( 'pods-help-tooltip' );
		expect( tippyWrapper ).toHaveAttribute( 'data-trigger', 'click' );
		expect( tippyWrapper ).toHaveAttribute( 'data-z-index', '100001' );
		expect( tippyWrapper ).toHaveAttribute( 'data-interactive', 'true' );
	} );

	test( 'renders tooltip content with sanitized HTML', () => {
		const helpText = 'This is <strong>important</strong> info';

		render( <HelpTooltip helpText={ helpText } /> );

		// Check the content in the tooltip
		const tippyContent = screen.getByTestId( 'tippy-content' );
		expect( tippyContent ).toBeInTheDocument();

		// The content should be wrapped in a span
		const contentSpan = tippyContent.querySelector( 'span' );
		expect( contentSpan ).toBeInTheDocument();

		expect( screen.getByTestId( 'tippy-content' ).innerHTML ).toEqual( '<span>This is <strong>important</strong> info</span>' );
	} );

	test( 'renders tooltip with link when helpLink is provided', () => {
		const helpText = 'Click for more information';
		const helpLink = 'https://pods.io/docs/';

		render( <HelpTooltip helpText={ helpText } helpLink={ helpLink } /> );

		// Check the content in the tooltip
		const tippyContent = screen.getByTestId( 'tippy-content' );

		// The content should be wrapped in an anchor tag
		const linkElement = tippyContent.querySelector( 'a' );
		expect( linkElement ).toBeInTheDocument();
		expect( linkElement ).toHaveAttribute( 'href', helpLink );
		expect( linkElement ).toHaveAttribute( 'target', '_blank' );
		expect( linkElement ).toHaveAttribute( 'rel', 'noopener noreferrer' );

		// It should have an external icon
		const externalIcon = screen.getByTestId( 'dashicon-external' );
		expect( externalIcon ).toBeInTheDocument();
		expect( linkElement ).toContainElement( externalIcon );
	} );

	test( 'sanitizes potentially dangerous HTML in tooltip content', () => {
		const dangerousHelpText = 'Safe text <script>alert("dangerous!");</script>';

		render( <HelpTooltip helpText={ dangerousHelpText } /> );

		expect( screen.getByTestId( 'tippy-content' ).innerHTML ).toEqual( '<span>Safe text </span>' );
	} );

	test( 'uses richTextInlineOnly configuration for sanitization', () => {
		const helpText = 'Formatted <strong>text</strong>';

		render( <HelpTooltip helpText={ helpText } /> );

		expect( screen.getByTestId( 'tippy-content' ).innerHTML ).toEqual( '<span>Formatted <strong>text</strong></span>' );
	} );

	test( 'has proper accessibility attributes', () => {
		render( <HelpTooltip helpText="Accessible help text" /> );

		// Check if the button has correct accessibility attributes
		const button = screen.getByRole( 'button' );
		expect( button ).toHaveAttribute( 'tabIndex', '0' );
		expect( button ).toHaveAttribute( 'role', 'button' );
	} );

	test( 'handles empty helpText gracefully', () => {
		render( <HelpTooltip helpText="" /> );

		// Even with empty text, the component should render
		const helpIcon = screen.getByTestId( 'dashicon-editor-help' );
		expect( helpIcon ).toBeInTheDocument();
	} );

	test( 'uses editor-help icon for the help button', () => {
		render( <HelpTooltip helpText="Help text" /> );

		// Check that Dashicon was called with the correct icon
		expect( Dashicon ).toHaveBeenCalledWith( expect.objectContaining( {
			icon: 'editor-help',
		} ), expect.anything() );

		// The help icon should be visible
		const helpIcon = screen.getByTestId( 'dashicon-editor-help' );
		expect( helpIcon ).toBeInTheDocument();

		expect( screen.getByTestId( 'tippy-content' ).innerHTML ).toEqual( '<span>Help text</span>' );
	} );

	test( 'handles HTML entities in helpText', () => {
		const helpTextWithEntities = 'Special characters: &amp; &lt; &gt;';

		render( <HelpTooltip helpText={ helpTextWithEntities } /> );

		expect( screen.getByTestId( 'tippy-content' ).innerHTML ).toEqual( '<span>Special characters: &amp; &lt; &gt;</span>' );
	} );

	test( 'tooltip wrapper has correct class for styling', () => {
		render( <HelpTooltip helpText="Styled help text" /> );

		const tippyWrapper = screen.getByTestId( 'tippy-wrapper' );
		expect( tippyWrapper ).toHaveClass( 'pods-help-tooltip' );

		expect( screen.getByTestId( 'tippy-content' ).innerHTML ).toEqual( '<span>Styled help text</span>' );
	} );
} );
