/**
 * External dependencies
 */
import renderer from 'react-test-renderer';
import React from 'react';

/**
 * Internal dependencies
 */
import { withSaveData } from '@moderntribe/common/hoc';

const Block = ( props ) => <div { ...props }>With Save Data!</div>;
const props = {
	name: 'tribe/event',
	setInitialState: jest.fn(),
	setAttributes: jest.fn(),
	title: 'Modern Tribe!',
	description: 'The Next Generation of Digital Agency',
	organizers: [],
	attributes: {
		title: 'Modern Tribe',
		description: '',
		organizers: [],
	},
};

describe( 'HOC - With Details', () => {
	let Wrapper;
	let component;
	let instance;

	beforeEach( () => {
		Wrapper = withSaveData()( Block );
		component = renderer.create( <Wrapper { ...props } /> );
		instance = component.root;
	} );

	afterEach( () => {
		props.setInitialState.mockClear();
		props.setAttributes.mockClear();
		component.getInstance().unregisterBlock();
	} );

	it( 'Should render a component', () => {
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	it( 'Should render the inner component', () => {
		expect( instance ).not.toBe( null );
		expect( () => instance.findByType( Block ) ).not.toThrowError();
	} );

	it( 'Should set the initial state', () => {
		expect( props.setInitialState ).toHaveBeenCalled();
		expect( props.setInitialState ).toHaveBeenCalledTimes( 1 );
	} );

	it( 'Should generate the attributes', () => {
		const HOC = component.getInstance();
		expect( HOC.attrs ).toEqual( props.attributes );
	} );

	it( 'Should generate the keys', () => {
		const HOC = component.getInstance();
		expect( HOC.keys ).toEqual( Object.keys( props.attributes ) );
	} );

	it( 'Simulate componentDidUpdate call', () => {
		const wrapper = shallow( <Wrapper { ...props } /> );
		const wrapperInstance = wrapper.instance();
		expect( wrapperInstance.calculateDiff() ).toEqual( {
			description: 'The Next Generation of Digital Agency',
			title: 'Modern Tribe!',
		} );
		wrapper.setProps( {
			attributes: {
				title: 'Modern Tribe!',
				description: 'The Next Generation of Digital Agency',
				organizers: [],
			}
		} );
		expect( wrapperInstance.calculateDiff() ).toEqual( {} );
		wrapper.setProps( {
			organizers: [ 3 ],
			attributes: {
				title: 'Modern Tribe!',
				description: 'The Next Generation of Digital Agency',
				organizers: [ 3 ],
			},
		} );
		expect( wrapperInstance.calculateDiff() ).toEqual( {} );
		wrapper.setProps( {
			organizers: [ 2, 3 ],
			attributes: {
				title: 'Modern Tribe!',
				description: 'The Next Generation of Digital Agency',
				organizers: [ 3, 2 ],
			},
		} );
		expect( wrapperInstance.calculateDiff() ).toEqual( { organizers: [ 2, 3 ] } );
		wrapperInstance.unregisterBlock();
	} );

	it( 'Should calculate the diff', () => {
		const HOC = component.getInstance();
		const expected = {
			title: props.title,
			description: props.description,
		};
		expect( HOC.calculateDiff() ).toEqual( expected );
	} );

	it( 'Should count a single block', () => {
		const HOC = component.getInstance();
		expect( HOC.blockCount() ).toBe( 1 );
	} );
} );

describe( 'HOC - With Details on multiple instances', () => {
	afterEach( () => {
		props.setInitialState.mockClear();
		props.setAttributes.mockClear();
	} );

	it( 'Should register the initial state just once', () => {
		const WrapperComponent = withSaveData()( Block );

		renderer.create( <WrapperComponent { ...props } /> );
		renderer.create( <WrapperComponent { ...props } /> );
		renderer.create( <WrapperComponent { ...props } /> );

		expect( props.setInitialState ).toHaveBeenCalled();
		expect( props.setInitialState ).toHaveBeenCalledTimes( 1 );
	} );

	it( 'Should register the state multiple times on non isolated instances', () => {
		const WrapperComponent = withSaveData()( Block );
		props.isolated = true;
		renderer.create( <WrapperComponent { ...props } /> );
		renderer.create( <WrapperComponent { ...props } /> );
		renderer.create( <WrapperComponent { ...props } /> );

		expect( props.setInitialState ).toHaveBeenCalled();
		expect( props.setInitialState ).toHaveBeenCalledTimes( 3 );
	} );
} );

describe( 'HOC - test life cycle callbacks', () => {
	let Wrapper;
	let component;
	let instance;
	let properties = {};

	beforeAll( () => {
		props.onBlockCreated = jest.fn();
		props.onBlockRemoved = jest.fn();
	});

	beforeEach( () => {
		Wrapper = withSaveData()( Block );
		component = renderer.create( <Wrapper { ...props } /> );
		instance = component.root;
		properties = instance.props;
	} );

	afterEach( () => {
		props.setInitialState.mockClear();
		props.setAttributes.mockClear();
		props.onBlockCreated.mockClear();
		props.onBlockRemoved.mockClear();
	} );

	afterAll( () => {
		delete props.onBlockCreated;
		delete props.onBlockRemoved;
	} );

	it( 'Should call the onBlockCreated callback on mount', () => {
		expect( props.onBlockCreated ).toHaveBeenCalled();
		expect( props.onBlockCreated ).toHaveBeenCalledWith( properties );
		expect( props.onBlockRemoved ).not.toHaveBeenCalled();
	} );

	it( 'Should call the onBlockRemoved callback on unmount of the block', () => {
		component.unmount();
		expect( props.onBlockCreated ).toHaveBeenCalled();
		expect( props.onBlockCreated ).toHaveBeenCalledWith( properties );
		expect( props.onBlockRemoved ).toHaveBeenCalled();
		expect( props.onBlockRemoved ).toHaveBeenCalledWith( properties );
	} );
} );
