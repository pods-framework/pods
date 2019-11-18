/**
 * External Dependencies
 */
import React from 'react';
import renderer from 'react-test-renderer';

/**
 * Internal Dependencies
 */
import PluginBlockHooks from '../component';

jest.mock( '@wordpress/editor', () => ( {
	InnerBlocks: 'InnerBlocks',
} ) );
jest.mock( '@wordpress/data', () => ( {
	select: jest.fn( () => ( {
		getBlockTypes: jest.fn( () => ( [
			{ name: 'tribe/event-datetime' },
			{ name: 'tribe/event-pro-recurring' },
			{ name: 'tribe/event-pro-exclusion' },
			{ name: 'tribe/event-cool-container1' },
			{ name: 'tribe/event-cool-container2' },
		] ) ),
	} ) ),
} ) );

describe( 'PluginBlockHooks', () => {
	let props;
	beforeEach( () => {
		props = {
			plugins: [ 'events', 'events-pro', 'events-cool' ],
			pluginTemplates: {
				'events': [
					[ 'tribe/event-datetime', {}],
				],
				'events-pro': [
					[ 'tribe/event-pro-recurring', {}],
					[ 'tribe/event-pro-exclusion', {}],
				],
			},
		};
	} );

	test( 'should match snapshot', () => {
		const component = renderer.create(
			<PluginBlockHooks { ...props } />
		);
		expect( component.toJSON() ).toMatchSnapshot();
	} );
	test( 'should only render events templates', () => {
		props.plugins = props.plugins.filter( plugin => plugin === 'events' );
		const component = renderer.create(
			<PluginBlockHooks { ...props } />
		);
		expect( component.toJSON() ).toMatchSnapshot();
	} );
	test( 'should only render events-pro templates', () => {
		props.plugins = props.plugins.filter( plugin => plugin === 'events-pro' );
		const component = renderer.create(
			<PluginBlockHooks { ...props } />
		);
		expect( component.toJSON() ).toMatchSnapshot();
	} );
	test( 'should not hook in unregistered blocks', () => {
		props.pluginTemplates.events.push( [ 'i-dont-exist', {}] );
		const component = renderer.create(
			<PluginBlockHooks { ...props } />
		);
		expect( component.toJSON() ).toMatchSnapshot();
	} );
	test( 'should recursively hook blocks', () => {
		delete props.pluginTemplates.events;
		delete props.pluginTemplates[ 'events-pro' ];
		props.pluginTemplates[ 'events-cool' ] = [
			[ 'tribe/event-pro-recurring', {}],
			[ 'tribe/event-cool-container1', {}, [
				[ 'tribe/event-pro-recurring', {}],
				[ 'tribe/event-cool-container2', {}, [
					[ 'tribe/event-pro-recurring', {}],
					[ 'dont-register-me', {}],
				] ],
				[ 'dont-register-2me', {}],
			],
			[ 'dont-register-me', {}],
			],
		];
		const component = renderer.create(
			<PluginBlockHooks { ...props } />
		);
		expect( component.toJSON() ).toMatchSnapshot();
	} );
} );
