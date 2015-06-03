<?php
/**
 * @package Pods
 * @category Utilities
 *
 * Class Pods_Service_Definition
 */
class Pods_Service_Definition {

	public $class;

	public $parameters = array();

	/**
	 * @param       $className
	 * @param array $parameters
	 */
	public function __construct( $className, $parameters = array() ) {
		$this->class      = $className;
		$this->parameters = $parameters;
	}

	/**
	 * @param $parameters
	 */
	public function parameters( $parameters ) {
		$this->parameters = array_merge( $this->parameters, $parameters );

	}

	/**
	 * @param $key
	 * @param $value
	 */
	public function parameter( $key, $value ) {
		$this->parameters[ $key ] = $value;
	}

	/**
	 * @param       $className
	 * @param array $parameters
	 *
	 * @return Pods_Service_Definition
	 */
	public static function create( $className, $parameters = array() ) {
		$instance = new self( $className, $parameters );

		return $instance;
	}
}