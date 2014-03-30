<?php

class Pods_Service {

	public $class;

	public $parameters = array();

	public function __construct( $className, $parameters = array() ) {
		$this->class      = $className;
		$this->parameters = $parameters;
	}

	public function parameters( $parameters ) {
		$this->parameters = array_merge( $this->parameters, $parameters );

	}

	public function parameter( $key, $value ) {
		$this->parameters[ $key ] = $value;
	}

	public static function create( $className, $parameters = array() ) {
		$instance = new self( $className, $parameters );

		return $instance;
	}
}