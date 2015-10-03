<?php

/**
 * Class PodsRESTFields
 *
 * Creates an object that adds read/write handlers for Pods fields in default responses.
 *
 * @package Pods
 * @since 2.5.6
 */
class PodsRESTFields {

	/**
	 * Pods object
	 *
	 * @since 2.5.6
	 *
	 * @acces protected
	 *
	 * @param Pods
	 */
	protected $pod;

	/**
	 * Constructor for class
	 *
	 * @since 2.5.6
	 *
	 * @param string|object|Pods $pod Pods object
	 */
	public function __construct( $pod ) {
		if( ! function_exists( 'register_api_field' ) ) {
			return;
		}

		$this->set_pod( $pod );
		if( $this->pod ) {
			$this->add_fields();
		}

	}

	/**
	 * Set the Pods object
	 *
	 * @since 2.5.6
	 *
	 * @access protected
	 *
	 * @param string|Pods $pod Pods object or name of Pods object
	 */
	private function set_pod( $pod ) {
		if( is_string( $pod ) ) {
			$this->set_pod( pods( $pod, null, true ) );

		}else {
			$type =  $pod->pod_data[ 'type' ];
			if( in_array( $type, array(
					'post_type',
					'user',
					'taxonomy'
				)
			)
			) {
				$this->pod = $pod;
			}else{
				$this->pod = false;
			}
		}

	}

	/**
	 * Add fields, based on options to REST read/write requests
	 *
	 * @since 2.5.6
	 *
	 * @access protected
	 */
	protected function add_fields() {
		$fields = $this->pod->fields();
		foreach( $fields as $field_name => $field ) {
			$read = pods_rest_api_field_allowed_to_extend( $field_name, $this->pod, true );
			$write = pods_rest_api_field_allowed_to_extend( $field_name, $this->pod, false );
			$this->register( $field_name, $read, $write );
		}

	}

	/**
	 * Register fields and their callbacks for read/write via REST
	 *
	 * @since 2.5.6
	 *
	 * @access protected
	 *
	 * @param string $field_name Name of fields.
	 * @param bool $read Allowing reading?
	 * @param bool $write Allow writing?
	 */
	protected function register( $field_name, $read, $write ) {
		$args = array();
		switch ( $read ){
			case true == $read :
				$args[ 'get_callback' ] = array( 'PodsRESTHandlers', 'get_handler' );
				break;
			case is_callable( $read ) :
				$args[ 'get_callback' ] = $read;
				$read = true;
				break;
		}

		switch ( $write ){
			case true == $write :
				$args[ 'update_callback' ] = array( 'PodsRESTHandlers', 'write_handler' );
				break;
			case is_callable( $write ) :
				$args[ 'update_callback' ] = $write;
				$write = true;
				break;
		}

		if( $read || $write ) {
			register_api_field( $this->pod->pod, $field_name, $args );
		}

	}

}
