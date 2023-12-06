describe( 'Edit pod screen', () => {
	beforeEach( () => {
		cy.visit( Cypress.env( 'host' ) + '/wp-admin/' );
		cy.login( Cypress.env( 'username' ), Cypress.env( 'password' ) );
	} );

	it( 'Edit pod screen - add field - has no detectable a11y violations on load (custom configuration)', () => {
		cy.visit( Cypress.env( 'host' ) + '/wp-admin/admin.php?page=pods&action=edit&id=6' )
			.injectAxe()
		/**
		 * cy.click() can only be called on a single element. Your subject contained 2 elements.
		 * Pass { multiple: true } if you want to serially click each element.
		 *
		 * @see https://docs.cypress.io/api/commands/click
		 */
			.get( '[aria-label="Add a new field to this field group"]:first-child' ).click()
		// Add a new group modal - default wp modal component.
			.checkA11y( '.components-modal__content' );
	} );
} );
