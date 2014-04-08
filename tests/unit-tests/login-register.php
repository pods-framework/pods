<?php
namespace Pods_Unit_Tests;

/**
 * @group pods_login_register
 */
class Tests_Login_Register extends Pods_UnitTestCase {
	public function setUp() {
		parent::setUp();
	}

	/**
     * Testthat the login form returns the expected string
     */
	public function test_login_form() {
		$this->assertEquals( '<p class="pods-logged-in">You are already logged in</p>', pods_login_form() );
	}

	/**
     * Test that the pods_log_user_in() function successfully logs the user in
     */
	public function test_log_user_in() {
		wp_logout();
		pods_log_user_in( 1 );
		$this->assertTrue( is_user_logged_in() );
	}
}