/**
 * Internal dependencies
 */
import isMediaModal from '../isMediaModal';

describe( 'isMediaModal', () => {
	// Save the original location.pathname
	const originalLocationPathname = window.location.pathname;

	// Use Object.defineProperty to mock location.pathname since it's read-only
	const mockLocationPathname = ( value ) => {
		window.location.pathname = value;
	};

	afterAll( () => {
		window.location.pathname = originalLocationPathname;
	} );

	test( 'returns true when pathname is /wp-admin/upload.php', () => {
		expect( isMediaModal( '/wp-admin/upload.php' ) ).toEqual( true );
	} );

	test( 'returns true when pathname is /another-dir/wp-admin/upload.php', () => {
		expect( isMediaModal( '/another-dir/wp-admin/upload.php' ) ).toEqual( true );
	} );

	test( 'returns false when pathname is not /wp-admin/upload.php', () => {
		// Mock the location.pathname to a different admin screen
		expect( isMediaModal( '/wp-admin/edit.php' ) ).toEqual( false );
	} );

	test( 'returns false for similar but different paths', () => {
		// Test a path that's similar but not exactly the same
		expect( isMediaModal( '/wp-admin/uploads.php' ) ).toEqual( false );
	} );

	test( 'is case sensitive for the path check', () => {
		// Test with different case
		expect( isMediaModal( '/wp-admin/UPLOAD.php' ) ).toEqual( false );
	} );
} );
