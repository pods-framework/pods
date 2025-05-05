/**
 * External dependencies
 */
import React from 'react';
import { render, screen, fireEvent } from '@testing-library/react';
import '@testing-library/jest-dom';

/**
 * Internal dependencies
 */
import PodsNavTab from '../pods-nav-tab';

describe( 'PodsNavTab Component', () => {
	// Sample test data
	const defaultProps = {
		tabs: [
			{
				name: 'general',
				label: 'General',
			}, {
				name: 'advanced',
				label: 'Advanced',
			}, {
				name: 'custom',
				label: 'Custom',
			},
		],
		activeTab: 'general',
		setActiveTab: jest.fn(),
	};

	test( 'renders all tabs correctly', () => {
		render( <PodsNavTab { ...defaultProps } /> );

		// Check if all tabs are rendered
		expect( screen.getByText( 'General' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Advanced' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Custom' ) ).toBeInTheDocument();

		// Check the wrapper class
		const wrapper = screen.getByRole( 'heading' );
		expect( wrapper ).toHaveClass( 'nav-tab-wrapper' );
		expect( wrapper ).toHaveClass( 'pods-nav-tabs' );
	} );

	test( 'applies active class to the active tab', () => {
		render( <PodsNavTab { ...defaultProps } /> );

		// Check that the active tab has the active class
		const generalTab = screen.getByText( 'General' ).closest( 'a' );
		expect( generalTab ).toHaveClass( 'nav-tab-active' );

		// Check that other tabs don't have the active class
		const advancedTab = screen.getByText( 'Advanced' ).closest( 'a' );
		const customTab = screen.getByText( 'Custom' ).closest( 'a' );
		expect( advancedTab ).not.toHaveClass( 'nav-tab-active' );
		expect( customTab ).not.toHaveClass( 'nav-tab-active' );
	} );

	test( 'calls setActiveTab with correct tab name when clicked', () => {
		render( <PodsNavTab { ...defaultProps } /> );

		// Find and click the Advanced tab
		const advancedTab = screen.getByText( 'Advanced' );
		fireEvent.click( advancedTab );

		// Check that setActiveTab was called with 'advanced'
		expect( defaultProps.setActiveTab ).toHaveBeenCalledWith( 'advanced' );

		// Find and click the Custom tab
		const customTab = screen.getByText( 'Custom' );
		fireEvent.click( customTab );

		// Check that setActiveTab was called with 'custom'
		expect( defaultProps.setActiveTab ).toHaveBeenCalledWith( 'custom' );
	} );

	test( 'generates correct href attributes for tabs', () => {
		render( <PodsNavTab { ...defaultProps } /> );

		// Check href attributes for each tab
		const generalTab = screen.getByText( 'General' ).closest( 'a' );
		const advancedTab = screen.getByText( 'Advanced' ).closest( 'a' );
		const customTab = screen.getByText( 'Custom' ).closest( 'a' );

		expect( generalTab ).toHaveAttribute( 'href', '#pods-general' );
		expect( advancedTab ).toHaveAttribute( 'href', '#pods-advanced' );
		expect( customTab ).toHaveAttribute( 'href', '#pods-custom' );
	} );

	test( 'applies correct class names to tabs', () => {
		render( <PodsNavTab { ...defaultProps } /> );

		// Check that all tabs have the common classes
		const generalTab = screen.getByText( 'General' ).closest( 'a' );
		const advancedTab = screen.getByText( 'Advanced' ).closest( 'a' );
		const customTab = screen.getByText( 'Custom' ).closest( 'a' );

		expect( generalTab ).toHaveClass( 'nav-tab' );
		expect( generalTab ).toHaveClass( 'pods-nav-tab-link' );
		expect( advancedTab ).toHaveClass( 'nav-tab' );
		expect( advancedTab ).toHaveClass( 'pods-nav-tab-link' );
		expect( customTab ).toHaveClass( 'nav-tab' );
		expect( customTab ).toHaveClass( 'pods-nav-tab-link' );
	} );

	test( 'updates active tab when activeTab prop changes', () => {
		const { rerender } = render( <PodsNavTab { ...defaultProps } /> );

		// Initially, 'general' should be active
		let generalTab = screen.getByText( 'General' ).closest( 'a' );
		let advancedTab = screen.getByText( 'Advanced' ).closest( 'a' );

		expect( generalTab ).toHaveClass( 'nav-tab-active' );
		expect( advancedTab ).not.toHaveClass( 'nav-tab-active' );

		// Update the activeTab prop
		rerender( <PodsNavTab { ...defaultProps } activeTab="advanced" /> );

		// Now 'advanced' should be active
		generalTab = screen.getByText( 'General' ).closest( 'a' );
		advancedTab = screen.getByText( 'Advanced' ).closest( 'a' );

		expect( generalTab ).not.toHaveClass( 'nav-tab-active' );
		expect( advancedTab ).toHaveClass( 'nav-tab-active' );
	} );

	test( 'renders correctly with empty tabs array', () => {
		const emptyTabsProps = {
			...defaultProps,
			tabs: [],
		};

		render( <PodsNavTab { ...emptyTabsProps } /> );

		// Should still render the wrapper but no tabs
		const wrapper = screen.getByRole( 'heading' );
		expect( wrapper ).toBeInTheDocument();
		expect( wrapper ).toHaveClass( 'nav-tab-wrapper' );
		expect( wrapper ).toHaveClass( 'pods-nav-tabs' );

		// There should be no links
		const tabs = screen.queryAllByRole( 'link' );
		expect( tabs ).toHaveLength( 0 );
	} );

	test( 'sets key prop correctly on tab links', () => {
		// This test verifies the React key prop is set correctly,
		// which helps with performance and prevents warnings

		// We need to mock React.createElement to capture the key props
		const originalCreateElement = React.createElement;
		const mockCreateElement = jest.fn( originalCreateElement );
		React.createElement = mockCreateElement;

		render( <PodsNavTab { ...defaultProps } /> );

		// Find calls that created the <a> elements
		const createLinkCalls = mockCreateElement.mock.calls.filter( call => call[ 0 ] === 'a' );

		// Check that each tab name is used as a key
		expect( createLinkCalls.length ).toBe( defaultProps.tabs.length );
		defaultProps.tabs.forEach( ( tab, index ) => {
			// Find the props object (second argument)
			const props = createLinkCalls[ index ][ 1 ];
			expect( props.key ).toBe( tab.name );
		} );

		// Restore original React.createElement
		React.createElement = originalCreateElement;
	} );

	test( 'handles tabs with duplicate names', () => {
		const duplicateTabsProps = {
			...defaultProps,
			tabs: [
				{
					name: 'general',
					label: 'General',
				}, {
					name: 'general',
					label: 'General 2',
				}, {
					name: 'advanced',
					label: 'Advanced',
				},
			],
		};

		// Expect the render to have errored.
		// We need to mock console.error to prevent test failure
		const originalError = console.error;
		console.error = jest.fn();

		render( <PodsNavTab { ...duplicateTabsProps } /> );

		// Check that the error was logged
		expect( console.error ).toHaveBeenCalledWith(
			expect.stringContaining( 'Warning: Encountered two children with the same key' ),
			'general',
			expect.anything(),
		);

		console.error = originalError;

		// Both tabs with the same name should be rendered
		expect( screen.getByText( 'General' ) ).toBeInTheDocument();
		expect( screen.getByText( 'General 2' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Advanced' ) ).toBeInTheDocument();
	} );
} );
