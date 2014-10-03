<?php
/**
 * Override the die handlers.
 *
 * Pods Framework uses the pods_die() function to die/exit the script
 * which is a simple wrapper for calling the wp_die() function, this class simply
 * overrides that die calls by returning false allowing us to make assertions when
 * pods_die() is called, for example in the AJAX functions or the API functions.
 *
 * @package Pods_Unit_Tests
 * @author Sunny Ratilal
 */
class Pods_Die_Handler {
	public $die = false;

	public function __construct() {
		$this->die_handler();
	}

	public function die_handler() {
		define( 'PODS_UNIT_TESTS', true );
		$this->die = true;
	}

	public function died() {
		return $this->die;
	}

	public function reset() {
		$this->die = false;
	}
}