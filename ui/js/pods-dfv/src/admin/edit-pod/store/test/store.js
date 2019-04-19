import { initStore } from '../store';

const initializedWithEmptyState = {
	fields: [],
	labels: [],
	ui: { activeTab: 'MANAGE_FIELDS', saveStatus: '' }
};

const initialState = {
	fields: [ 'field 1', 'field 2', 'field 3' ],
	labels: [ 'label 1', 'label 2', 'label 3' ],
};
const initializedWithInitialState = {
	fields: [ 'field 1', 'field 2', 'field 3' ],
	labels: [ 'label 1', 'label 2', 'label 3' ],
	ui: {
		activeTab: 'MANAGE_FIELDS',
		saveStatus: ''
	}
};

describe( 'store', () => {
	describe( 'initStore() empty', () => {
		const store = initStore( {} );

		it( 'Initializes properly', () => {
			expect( store.getState() ).toMatchObject( initializedWithEmptyState );
		} );
	} );

	describe( 'initStore() with initialState', () => {
		const store = initStore( initialState );

		it( 'Initializes properly', () => {
			expect( store.getState() ).toMatchObject( initializedWithInitialState );
		} );
	} );
} );
