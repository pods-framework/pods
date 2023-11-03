describe( 'Pods Manage Pods screen', () => {
	beforeEach( () => {
		cy.visit( Cypress.env( 'host' ) + '/wp-admin/' );
		cy.login( Cypress.env( 'username' ), Cypress.env( 'password' ) );
	} );

	it( 'Manage pods screen has no detectable a11y violations on load (custom configuration)', () => {
		cy.visit( Cypress.env( 'host' ) + '/wp-admin/admin.php?page=pods' );
		cy.injectAxe();
		cy.checkA11y( '#wpbody-content' );
	} );
} );
