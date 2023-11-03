describe( 'Edit pod screen', () => {
	beforeEach( () => {
		cy.visit( Cypress.env( 'host' ) + '/wp-admin/' );
		cy.login( Cypress.env( 'username' ), Cypress.env( 'password' ) );
	} );

	it( 'Edit pod screen has no detectable a11y violations on load (custom configuration)', () => {
		cy.visit( Cypress.env( 'host' ) + '/wp-admin/admin.php?page=pods&action=edit&id=6' );
		cy.injectAxe();
		cy.checkA11y( '#wpbody-content' );
	} );
} );
