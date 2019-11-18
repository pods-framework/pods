/**
 * Internal dependencies
 */
import reducer, { actions } from '@moderntribe/common/data/forms';
import form, { DEFAULT_STATE } from '@moderntribe/common/data/forms/reducers/form';

jest.mock( '@moderntribe/common/data/forms/reducers/form', () => {
	const original = require.requireActual( '@moderntribe/common/data/forms/reducers/form' );
	return {
		__esModule: true,
		...original,
		default: jest.fn( ( state = original.DEFAULT_STATE ) => state ),
	};
} );

jest.mock( '@moderntribe/common/data/forms/reducers/volatile', () => {
	return {
		__esModule: true,
		default: jest.fn( ( state = [] ) => state ),
	};
} );

describe( '[STORE] - form reducer', () => {
	beforeEach( () => {
		form.mockClear();
	} );

	it( 'Should return the default state', () => {
		expect( reducer( undefined, {} ) ).toEqual( { byId: {}, volatile: [] } );
	} );

	it( 'Should add a new form', () => {
		const state = {
			byId: {},
			volatile: [],
		};
		expect( reducer( state, actions.registerForm( 20, 'tribe_organizer' ) ) ).toEqual( {
			byId: {
				20: DEFAULT_STATE,
			},
			volatile: [],
		} );
	} );

	it( 'Should pass the actions to the child reducer when block not present', () => {
		const groupAction = [
			actions.registerForm( 10, 'tribe_venue' ),
			actions.editEntry( 20, { title: 'Modern tribe' } ),
			actions.createDraft( 20, { title: 'Tribe' } ),
			actions.setSubmit( 20 ),
			actions.clearForm( 20 ),
			actions.setSaving( 20, true ),
		];

		groupAction.forEach( ( action ) => {
			reducer( {}, action );
			expect( form ).toHaveBeenCalledWith( undefined, action );
			expect( form ).toHaveBeenCalledTimes( 1 );
			form.mockClear();
		} );
	} );

	it( 'It should pass the block to the child reducer', () => {
		const groupAction = [
			actions.registerForm( 10, 'tribe_venue' ),
			actions.editEntry( 20, { title: 'Modern tribe' } ),
			actions.createDraft( 20, { title: 'Tribe' } ),
			actions.setSubmit( 20 ),
			actions.clearForm( 20 ),
			actions.setSaving( 20, true ),
		];

		const state = {
			byId: {
				10: DEFAULT_STATE,
				20: DEFAULT_STATE,
			},
		};

		groupAction.forEach( ( action ) => {
			reducer( state, action );
			expect( form ).toHaveBeenCalledWith( DEFAULT_STATE, action );
			expect( form ).toHaveBeenCalledTimes( 1 );
			form.mockClear();
		} );
	} );
} );

