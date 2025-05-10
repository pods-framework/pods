/**
 * External dependencies
 */
import React from 'react';
import { render, screen, fireEvent } from '@testing-library/react';
import '@testing-library/jest-dom';

/**
 * Internal dependencies
 */
import IframeModal from '../iframe-modal';

// Mock the WordPress Modal component
jest.mock(
	'@wordpress/components',
	() => (
		{
			Modal: jest.fn( ( {
				children,
				title,
				className,
				isDismissible,
				onRequestClose,
				focusOnMount,
				shouldCloseOnEsc,
				shouldCloseOnClickOutside,
			} ) => (
				<div
					data-testid="wp-modal"
					className={ className }
					data-title={ title }
					data-dismissible={ isDismissible ? 'true' : 'false' }
					data-focus-on-mount={ focusOnMount ? 'true' : 'false' }
					data-close-on-esc={ shouldCloseOnEsc ? 'true' : 'false' }
					data-close-on-outside-click={ shouldCloseOnClickOutside ? 'true' : 'false' }
				>
					<button
						data-testid="close-button"
						onClick={ onRequestClose }
					>
						Close
					</button>
					<div data-testid="modal-content">
						{ children }
					</div>
				</div>
			) ),
		}
	),
);

describe( 'IframeModal Component', () => {
	// Import the mocked Modal component
	const { Modal } = require( '@wordpress/components' );

	beforeEach( () => {
		// Clear mock implementation before each test
		Modal.mockClear();
	} );

	// Sample test data
	const defaultProps = {
		title: 'Test Modal Title',
		iframeSrc: 'https://example.com/embed',
		onClose: jest.fn(),
	};

	test( 'renders Modal component with correct props', () => {
		render( <IframeModal { ...defaultProps } /> );

		// Check if Modal was called
		expect( Modal ).toHaveBeenCalled();

		// Get the modal element
		const modal = screen.getByTestId( 'wp-modal' );

		// Check for the correct class
		expect( modal ).toHaveClass( 'pods-iframe-modal' );

		// Check that title is passed correctly
		expect( modal ).toHaveAttribute( 'data-title', defaultProps.title );

		// Check modal configuration
		expect( modal ).toHaveAttribute( 'data-dismissible', 'true' );
		expect( modal ).toHaveAttribute( 'data-focus-on-mount', 'true' );
		expect( modal ).toHaveAttribute( 'data-close-on-esc', 'false' );
		expect( modal ).toHaveAttribute( 'data-close-on-outside-click', 'false' );
	} );

	test( 'renders iframe with correct src and title', () => {
		render( <IframeModal { ...defaultProps } /> );

		// Get the modal content
		const modalContent = screen.getByTestId( 'modal-content' );

		// Check for iframe within the modal content
		const iframe = modalContent.querySelector( 'iframe' );
		expect( iframe ).toBeInTheDocument();

		// Check iframe attributes
		expect( iframe ).toHaveAttribute( 'src', defaultProps.iframeSrc );
		expect( iframe ).toHaveAttribute( 'title', defaultProps.title );
		expect( iframe ).toHaveClass( 'pods-iframe-modal__iframe' );
	} );

	test( 'calls onClose when close button is clicked', () => {
		render( <IframeModal { ...defaultProps } /> );

		// Find and click the close button
		const closeButton = screen.getByTestId( 'close-button' );
		fireEvent.click( closeButton );

		// Check if onClose was called
		expect( defaultProps.onClose ).toHaveBeenCalledTimes( 1 );
	} );

	test( 'passes onRequestClose correctly to Modal', () => {
		render( <IframeModal { ...defaultProps } /> );

		// Check Modal was called with onRequestClose matching our onClose function
		expect( Modal ).toHaveBeenCalledWith( expect.objectContaining( {
			onRequestClose: defaultProps.onClose,
		} ), expect.anything() );
	} );

	test( 'handles different props correctly', () => {
		// Create props with different values
		const customProps = {
			title: 'Custom Modal Title',
			iframeSrc: 'https://pods.io/iframe',
			onClose: jest.fn(),
		};

		render( <IframeModal { ...customProps } /> );

		// Get the modal
		const modal = screen.getByTestId( 'wp-modal' );
		expect( modal ).toHaveAttribute( 'data-title', customProps.title );

		// Get the iframe
		const iframe = modal.querySelector( 'iframe' );
		expect( iframe ).toHaveAttribute( 'src', customProps.iframeSrc );
		expect( iframe ).toHaveAttribute( 'title', customProps.title );
	} );

	test( 'sets Modal to be dismissible', () => {
		render( <IframeModal { ...defaultProps } /> );

		// Check isDismissible is true
		expect( Modal ).toHaveBeenCalledWith( expect.objectContaining( {
			isDismissible: true,
		} ), expect.anything() );
	} );

	test( 'should not close Modal on Escape key press', () => {
		render( <IframeModal { ...defaultProps } /> );

		// Check shouldCloseOnEsc is false
		expect( Modal ).toHaveBeenCalledWith( expect.objectContaining( {
			shouldCloseOnEsc: false,
		} ), expect.anything() );
	} );

	test( 'should not close Modal on outside click', () => {
		render( <IframeModal { ...defaultProps } /> );

		// Check shouldCloseOnClickOutside is false
		expect( Modal ).toHaveBeenCalledWith( expect.objectContaining( {
			shouldCloseOnClickOutside: false,
		} ), expect.anything() );
	} );

	test( 'sets focus on mount for the Modal', () => {
		render( <IframeModal { ...defaultProps } /> );

		// Check focusOnMount is true
		expect( Modal ).toHaveBeenCalledWith( expect.objectContaining( {
			focusOnMount: true,
		} ), expect.anything() );
	} );

	test( 'applies correct CSS classes', () => {
		render( <IframeModal { ...defaultProps } /> );

		// Check Modal class
		const modal = screen.getByTestId( 'wp-modal' );
		expect( modal ).toHaveClass( 'pods-iframe-modal' );

		// Check iframe class
		const iframe = modal.querySelector( 'iframe' );
		expect( iframe ).toHaveClass( 'pods-iframe-modal__iframe' );
	} );
} );
