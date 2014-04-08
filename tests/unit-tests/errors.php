<?php
namespace Pods_Unit_Tests;

/**
 * @group pods_errors
 */
class Tests_Errors extends Pods_UnitTestCase {
	public function setUp() {
		parent::setUp();

		pods_set_error( 'invalid_email', 'Please enter a valid email address.' );
		pods_set_error( 'invalid_user', 'The user information is invalid.' );
		pods_set_error( 'username_incorrect', 'The username you entered does not exist' );
		pods_set_error( 'password_incorrect', 'The password you entered is incorrect' );
	}

	public function tearDown() {
		parent::tearDown();
	}

	public function test_set_errors() {
		$errors = Pods()->session->get( 'pods_errors' );

		$this->assertArrayHasKey( 'invalid_email', $errors );
		$this->assertArrayHasKey( 'invalid_user', $errors );
		$this->assertArrayHasKey( 'username_incorrect', $errors );
		$this->assertArrayHasKey( 'password_incorrect', $errors );
	}

	public function test_clear_errors() {
		$errors = pods_clear_errors();
		$this->assertFalse( Pods()->session->get( 'pods_errors' ) );
	}

	public function test_unset_error() {
		$error = pods_unset_error( 'invalid_email' );
		$errors = Pods()->session->get( 'pods_errors' );

		$expected = array(
			'invalid_user' => 'The user information is invalid.',
			'username_incorrect' => 'The username you entered does not exist',
			'password_incorrect' => 'The password you entered is incorrect'
		);

		$this->assertEquals( $expected, $errors );
	}
}