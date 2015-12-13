<?php
/**
 * Class PodsRESTFields
 *
 * Creates an object that adds read/write handlers for Pods fields in default responses.
 *
 * @package Pods
 * @since   2.5.6
 */
class PodsRESTFields {

	/**
	 * Pods object
	 *
	 * @since 2.5.6
	 *
	 * @access protected
	 *
	 * @var Pods
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

		if ( ! function_exists( 'register_rest_field' ) ) {
			return;
		}

		$this->set_pod( $pod );

		if ( $this->pod ) {
			$this->add_fields();
		}

	}

	/**
	 * Set the Pods object
	 *
	 * @since  2.5.6
	 *
	 * @access protected
	 *
	 * @param string|Pods $pod Pods object or name of Pods object
	 */
	private function set_pod( $pod ) {

		if ( is_string( $pod ) ) {
			$this->set_pod( pods( $pod, null, true ) );

		} else {
			$type = $pod->pod_data['type'];

			if ( in_array( $type, array( 'post_type', 'user', 'taxonomy' ) ) ) {
				$this->pod = $pod;
			} else {
				$this->pod = false;
			}
		}

	}

	/**
	 * Add fields, based on options to REST read/write requests
	 *
	 * @since  2.5.6
	 *
	 * @access protected
	 */
	protected function add_fields() {

		$fields = $this->pod->fields();

		foreach ( $fields as $field_name => $field ) {
			$read  = self::field_allowed_to_extend( $field_name, $this->pod, 'read' );
			$write = self::field_allowed_to_extend( $field_name, $this->pod, 'write' );

			$this->register( $field_name, $read, $write );
		}

	}

	/**
	 * Register fields and their callbacks for read/write via REST
	 *
	 * @since  2.5.6
	 *
	 * @access protected
	 *
	 * @param string            $field_name Name of fields.
	 * @param bool|string|array $read       Allowing reading?
	 * @param bool|string|array $write      Allow writing?
	 */
	protected function register( $field_name, $read, $write ) {

		$args = array();

		switch ( $read ) {
			case true === $read :
				$args['get_callback'] = array( 'PodsRESTHandlers', 'get_handler' );
				break;
			case is_callable( $read ) :
				$args['get_callback'] = $read;
				$read                 = true;
				break;
		}

		switch ( $write ) {
			case true === $write :
				$args['update_callback'] = array( 'PodsRESTHandlers', 'write_handler' );
				break;
			case is_callable( $write ) :
				$args['update_callback'] = $write;
				$write                   = true;
				break;
		}

		if ( $read || $write ) {
			register_rest_field( $this->pod->pod, $field_name, $args );
		}

	}

	/**
	 * Check if a field supports read or write via the REST API.
	 *
	 * @since 2.5.6
	 *
	 * @param string      $field_name The field name.
	 * @param object|Pods $pod        Pods object.
	 * @param string      $mode       Are we checking read or write?
	 *
	 * @return bool If supports, true, else false.
	 */
	public static function field_allowed_to_extend( $field_name, $pod, $mode = 'read' ) {

		$allowed = false;

		if ( is_object( $pod ) ) {
			$fields = $pod->fields();

			if ( array_key_exists( $field_name, $fields ) ) {
				$pod_options = $pod->pod_data['options'];

				if ( 'read' === $mode && pods_v( 'read_all', $pod_options, false ) ) {
					$allowed = true;
				} elseif ( 'write' === $mode && pods_v( 'write_all', $pod_options, false ) ) {
					$allowed = true;
				} elseif ( isset( $fields[ $field_name ] ) ) {
					if ( 'read' === $mode && 1 == (int) $pod->fields( $field_name, 'rest_read' ) ) {
						$allowed = true;
					} elseif ( 'write' === $mode && 1 == (int) $pod->fields( $field_name, 'rest_write' ) ) {
						$allowed = true;
					}
				}
			}
		}

		return $allowed;

	}

}