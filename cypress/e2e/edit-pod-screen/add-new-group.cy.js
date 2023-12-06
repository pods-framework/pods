describe( 'Edit pod screen', () => {
	beforeEach( () => {
		cy.visit( Cypress.env( 'host' ) + '/wp-admin/' );
		cy.login( Cypress.env( 'username' ), Cypress.env( 'password' ) );
	} );

	it( 'Edit pod screen - add new group - has no detectable a11y violations on load (custom configuration)', () => {
		cy.visit( Cypress.env( 'host' ) + '/wp-admin/admin.php?page=pods&action=edit&id=6' )
			.injectAxe()
		/**
		 * cy.click() can only be called on a single element. Your subject contained 2 elements.
		 * Pass { multiple: true } if you want to serially click each element.
		 *
		 * @see https://docs.cypress.io/api/commands/click
		 */
			.get( '.pods-button-group_container:first-child [aria-label="Add new field group to the Pod"]:first' ).click()
		// Add a new group modal - default wp modal component.
			.checkA11y( '.components-modal__screen-overlay' );
	} );

	it( 'Edit pod screen - add new group - advanced tab - has no detectable a11y violations on load (custom configuration)', () => {
		cy.visit( Cypress.env( 'host' ) + '/wp-admin/admin.php?page=pods&action=edit&id=6' )
			.injectAxe()
		/**
		 * cy.click() can only be called on a single element. Your subject contained 2 elements.
		 * Pass { multiple: true } if you want to serially click each element.
		 *
		 * @see https://docs.cypress.io/api/commands/click
		 */
			.get( '.pods-button-group_container:first-child [aria-label="Add new field group to the Pod"]:first' ).click()
			.get( '[aria-controls="advanced-tab"]' ).click()
		// Add a new group modal - default wp modal component.
			.checkA11y( '.components-modal__content' );
	} );
} );
