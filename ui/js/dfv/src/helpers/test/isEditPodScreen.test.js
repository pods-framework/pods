/**
 * Internal dependencies
 */
import isEditPodScreen from '../isEditPodScreen';

describe( 'isEditPodScreen', () => {
	// Save the original window.podsAdminConfig state
	const originalPodsAdminConfig = window.podsAdminConfig;

	beforeEach( () => {
		// Reset window.podsAdminConfig before each test
		delete window.podsAdminConfig;
	} );

	afterAll( () => {
		// Restore the original window.podsAdminConfig after all tests
		if ( originalPodsAdminConfig !== undefined ) {
			window.podsAdminConfig = originalPodsAdminConfig;
		} else {
			delete window.podsAdminConfig;
		}
	} );

	test( 'returns true when podsAdminConfig is defined', () => {
		// Set up the window.podsAdminConfig
		window.podsAdminConfig = {};

		// Check the result
		expect( isEditPodScreen() ).toEqual( true );
	} );

	test( 'returns false when podsAdminConfig is undefined', () => {
		// podsAdminConfig is already undefined from beforeEach

		// Check the result
		expect( isEditPodScreen() ).toEqual( false );
	} );

	test( 'returns true regardless of podsAdminConfig content', () => {
		// Set up the window.podsAdminConfig with different values
		window.podsAdminConfig = null;
		expect( isEditPodScreen() ).toEqual( true );

		window.podsAdminConfig = { key: 'value' };
		expect( isEditPodScreen() ).toEqual( true );

		window.podsAdminConfig = [];
		expect( isEditPodScreen() ).toEqual( true );

		window.podsAdminConfig = '';
		expect( isEditPodScreen() ).toEqual( true );

		window.podsAdminConfig = 0;
		expect( isEditPodScreen() ).toEqual( true );
	} );
} );
