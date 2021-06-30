<?php

namespace Pods_Unit_Tests\Traits\REST;

trait Auth {

	/**
	 * Authenticates a user with a role for the scope of the test.
	 *
	 * The method will create a user in WordPress with the "user" login and password, create a valid "wp_rest" nonce
	 * for the user and set the nonce on the "X-WP-Nonce" header.
	 *
	 * @param string $role A valid WordPress user role, e.g. 'subscriber' or `administrator`
	 *
	 * @return string The generated and valid nonce.
	 * @see https://codex.wordpress.org/Roles_and_Capabilities#Summary_of_Roles
	 *
	 */
	public function generate_nonce_for_role( $role ) {
		$I = $this;

		$user_id = $I->haveUserInDatabase( 'user', $role, [ 'user_pass' => 'user' ] );

		// login to get the cookies
		$I->loginAs( 'user', 'user' );

		// nonce recipes
		$_COOKIE[ LOGGED_IN_COOKIE ] = $I->grabCookie( LOGGED_IN_COOKIE );
		wp_set_current_user( $user_id );

		$nonce = wp_create_nonce( 'wp_rest' );

		$I->haveHttpHeader( 'X-WP-Nonce', $nonce );

		return $nonce;
	}
}