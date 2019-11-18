/**
 * External dependencies
 */
import renderer from 'react-test-renderer';
import React from 'react';
import configureStore from 'redux-mock-store';
import thunk from 'redux-thunk';

/**
 * Internal dependencies
 */
import { withForm } from '@moderntribe/common/hoc';

const initialState = {
	events: {
	},
	forms: {
		byId: {},
	},
};
// here it is possible to pass in any middleware if needed into //configureStore
const mockStore = configureStore( [ thunk ] );
const store = mockStore( initialState );

const Block = () => <div>With Form!</div>;
let setFormID;
let Wrapper;
let component;
let instance;

describe( 'HOC - With Form', () => {
	beforeEach( () => {
		setFormID = jest.fn( () => 'posts' );
		Wrapper = withForm( setFormID )( Block );
		component = renderer.create( <Wrapper store={ store } postType="post"/> );
		instance = component.root;
	} );

	afterEach( () => {
		mockStore( initialState );
		store.clearActions();
		setFormID.mockClear();
	} );

	it( 'Should render a component', () => {
		expect( component.toJSON() ).toMatchSnapshot();
	} );

	it( 'Should render the inner component', () => {
		expect( instance ).not.toBe( null );
		expect( () => instance.findByType( Block ) ).not.toThrowError();
	} );

	it( 'Should attach the form properties', () => {
		const expected = {
			edit: false,
			create: false,
			fields: {},
			submit: false,
		};
		expect( instance.findByType( Block ).props ).toMatchObject( expected );
	} );

	it( 'Should have properties as functions', () => {
		const props = instance.findByType( Block ).props;
		const expectedProps = [
			'maybeRemoveEntry',
			'setSubmit',
			'sendForm',
			'editEntry',
			'createDraft',
		];

		expectedProps.forEach( ( property ) => {
			expect( props[ property ] ).not.toBeUndefined();
			expect( typeof props[ property ] ).toBe( 'function' );
		} );
	} );

	it( 'Should register the postType by dispatching the actions', () => {
		expect( store.getActions() ).toMatchSnapshot();
	} );

	it( 'Should register the ID of the form', () => {
		expect( setFormID ).toHaveBeenCalled();
		expect( setFormID ).toHaveBeenCalledTimes( 3 );
	} );
} );

