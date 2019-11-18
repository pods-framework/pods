/**
 * External dependencies
 */
import configureStore from 'redux-mock-store';
import thunk from 'redux-thunk';
/**
 * Internal dependencies
 */
import { actions } from '@moderntribe/common/data/forms';

const middlewares = [ thunk ];
const mockStore = configureStore( middlewares );

describe( '[STORE] - Form actions', () => {
	test( 'Register form action', () => {
		expect( actions.registerForm( 20, 'tribe_organizers' ) ).toMatchSnapshot();
	} );

	test( 'Create draft entry', () => {
		expect( actions.createDraft( 20, { title: 'Modern Tribe' } ) ).toMatchSnapshot();
	} );

	test( 'Edit the entry action', () => {
		expect( actions.editEntry( 20, { title: 'Tribe' } ) ).toMatchSnapshot();
	} );

	test( 'Clear form action', () => {
		expect( actions.clearForm( 20 ) ).toMatchSnapshot();
	} );

	test( 'Set submit form', () => {
		expect( actions.setSubmit( 20 ) ).toMatchSnapshot();
	} );

	test( 'Set saving action', () => {
		expect( actions.setSaving( 20, true ) ).toMatchSnapshot();
		expect( actions.setSaving( 20, false ) ).toMatchSnapshot();
	} );

	test( 'Add volatile action', () => {
		expect( actions.addVolatile( 20 ) ).toMatchSnapshot();
	} );

	test( 'Remove volatile action', () => {
		expect( actions.removeVolatile( 20 ) ).toMatchSnapshot();
	} );
} );

describe( '[STORE] - form thunk actions', () => {
	let store = {};
	beforeAll( () => {
		store = mockStore( {
			events: {
			},
			forms: {
				byId: {
					20: {
						create: true,
						type: 'tribe_organizer',
						fields: {},
					},
					21: {
						create: false,
						type: 'tribe_venue',
						fields: {
							id: 21,
						},
					},
				},
				volatile: [],
			},
		} );
	} );

	afterEach( () => store.clearActions() );

	test( 'Send the form action when creating', () => {
		store.dispatch( actions.sendForm( 20, { title: 'Modern Tribe' } ) );
		expect( store.getActions() ).toMatchSnapshot();
	} );

	test( 'Send the form when editing', () => {
		store.dispatch( actions.sendForm( 21, { title: 'Tribe' } ) );
		expect( store.getActions() ).toMatchSnapshot();
	} );

	test( 'Maybe remove entry action without details', () => {
		store.dispatch( actions.maybeRemoveEntry( 20, {} ) );
		expect( store.getActions() ).toMatchSnapshot();
	} );

	test( 'Maybe remove entry action with details', () => {
		store.dispatch( actions.maybeRemoveEntry( 21, { id: 21, title: 'Modern Tribe' } ) );
		expect( store.getActions() ).toMatchSnapshot();
	} );
} );
