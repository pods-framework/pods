/**
 * Internal dependencies
 */
import { form } from '@moderntribe/common/data/forms/reducers';
import { actions } from '@moderntribe/common/data/forms';
import { DEFAULT_STATE } from '@moderntribe/common/data/forms/reducers/form';

describe( '[STORE] - form reducer', () => {
	it( 'Should return the default state', () => {
		expect( form( undefined, {} ) ).toEqual( DEFAULT_STATE );
	} );

	it( 'Should add a new form', () => {
		expect( form( DEFAULT_STATE, actions.registerForm( 20, 'tribe_organizers' ) ) ).toMatchSnapshot();
	} );

	it( 'Should clear a form', () => {
		const state = {
			...DEFAULT_STATE,
			type: 'tribe_organizers',
		};
		expect( form( state, actions.clearForm( 20 ) ) ).toMatchSnapshot();
	} );

	it( 'Should create a form draft', () => {
		const fields = {
			id: 20,
			title: 'Modern Tribe',
		};
		expect( form( DEFAULT_STATE, actions.createDraft( 20, fields ) ) ).toMatchSnapshot();
	} );

	it( 'Should toggle the saving form flag', () => {
		expect( form( DEFAULT_STATE, actions.setSaving( 20, true ) ) ).toMatchSnapshot();
		expect( form( DEFAULT_STATE, actions.setSaving( 20, false ) ) ).toMatchSnapshot();
	} );

	it( 'Should edit the form entry', () => {
		const fields = {
			title: 'Tribe',
			description: '',
		};
		expect( form( DEFAULT_STATE, actions.editEntry( 20, fields ) ) ).toMatchSnapshot();
	} );

	it( 'Should submit the form', () => {
		expect( form( DEFAULT_STATE, actions.setSubmit( 20 ) ) ).toMatchSnapshot();
	} );
} );
