/**
 * External dependencies
 */
import { selectors } from '@moderntribe/common/data/forms';
import { DEFAULT_STATE } from '@moderntribe/common/data/forms/reducers/form';

const state = {
	events: {
	},
	forms: {
		byId: {
			99: {
				...DEFAULT_STATE,
				type: 'tribe_organizers',
				create: true,
				fields: {
					name: 'Modern Tribe',
					description: 'The Next Generation of Digital Agency',
				},
			},
			100: {
				...DEFAULT_STATE,
				edit: true,
			},
			101: {
				...DEFAULT_STATE,
				submit: true,
			},
			102: {
				...DEFAULT_STATE,
				saving: true,
			},
		},
		volatile: [ 100, 102 ],
	},
};

describe( '[STORE] - Forms selectors', () => {
	it( 'Should return the forms blocks', () => {
		expect( selectors.formSelector( state, { name: 99 } ) ).toEqual( state.forms.byId[ '99' ] );
		expect( selectors.formSelector( state, { name: 100 } ) ).toEqual( state.forms.byId[ '100' ] );
		expect( selectors.formSelector( state, { name: 200 } ) ).toBe( undefined );
	} );

	it( 'Should return the form type', () => {
		expect( selectors.getFormType( state, { name: 99 } ) ).toBe( 'tribe_organizers' );
		expect( selectors.getFormType( state, { name: 100 } ) ).toBe( DEFAULT_STATE.type );
	} );

	it( 'Should return the edit value', () => {
		expect( selectors.getFormEdit( state, { name: 100 } ) ).toBe( true );
		expect( selectors.getFormEdit( state, { name: 99 } ) ).toBe( false );
		expect( selectors.getFormEdit( state, { name: 101 } ) ).toBe( false );
		expect( selectors.getFormEdit( state, { name: 102 } ) ).toBe( false );
	} );

	it( 'Should return the create value', () => {
		expect( selectors.getFormCreate( state, { name: 99 } ) ).toBe( true );
		expect( selectors.getFormCreate( state, { name: 100 } ) ).toBe( false );
		expect( selectors.getFormCreate( state, { name: 101 } ) ).toBe( false );
		expect( selectors.getFormCreate( state, { name: 102 } ) ).toBe( false );
	} );

	it( 'Should return the submit value', () => {
		expect( selectors.getFormSubmit( state, { name: 101 } ) ).toBe( true );
		expect( selectors.getFormSubmit( state, { name: 99 } ) ).toBe( false );
		expect( selectors.getFormSubmit( state, { name: 100 } ) ).toBe( false );
		expect( selectors.getFormSubmit( state, { name: 102 } ) ).toBe( false );
	} );

	it( 'Should return the saving value', () => {
		expect( selectors.getFormSaving( state, { name: 102 } ) ).toBe( true );
		expect( selectors.getFormSaving( state, { name: 99 } ) ).toBe( false );
		expect( selectors.getFormSaving( state, { name: 100 } ) ).toBe( false );
		expect( selectors.getFormSaving( state, { name: 101 } ) ).toBe( false );
	} );

	it( 'Should return the form fields', () => {
		expect( selectors.getFormFields( state, { name: 99 } ) )
			.toEqual( state.forms.byId[ '99' ].fields );
		expect( selectors.getFormFields( state, { name: 100 } ) ).toEqual( {} );
		expect( selectors.getFormFields( state, { name: 101 } ) ).toEqual( {} );
		expect( selectors.getFormFields( state, { name: 102 } ) ).toEqual( {} );
	} );

	it( 'Should return the volatile fields', () => {
		expect( selectors.getVolatile( state ) ).toEqual( [ 100, 102 ] );
	} );
} );
