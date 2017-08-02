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
			$pod = pods( $pod, null, true );

			$this->set_pod( $pod );
		} else {
			$type = $pod->pod_data['type'];

			$supported_pod_types = array(
				'post_type',
				'taxonomy',
				'media',
				'user',
				'comment',
			);

			if ( in_array( $type, $supported_pod_types, true ) ) {
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

		$rest_hook_name = $this->pod->pod_data['name'];

		if ( 'media' === $rest_hook_name ) {
			$rest_hook_name = 'attachment';
		} elseif ( 'comment' === $this->pod->pod_data['type'] ) {
			$rest_hook_name = 'comment';
		}

		$rest_hook_name = 'rest_insert_' . $rest_hook_name;

		if ( ! has_action( $rest_hook_name, array( 'PodsRESTHandlers', 'save_handler' ) ) ) {
			add_action( $rest_hook_name, array( 'PodsRESTHandlers', 'save_handler' ), 10, 3 );
		}

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
	 * @param string $field_name Name of field
	 * @param bool   $read       Field allows REST API read access
	 * @param bool   $write      Field allows REST API write access
	 */
	protected function register( $field_name, $read, $write ) {

		$args = array();

		if ( $read ) {
			$args['get_callback'] = array( 'PodsRESTHandlers', 'get_handler' );
		}

		if ( $write ) {
			$args['pods_update'] = true;
		}

		$object_type = $this->pod->pod;

		if ( 'media' === $object_type ) {
			$object_type = 'attachment';
		}

		if ( $read || $write ) {
			register_rest_field( $object_type, $field_name, $args );
		}

	}

	/**
	 * Check if a field supports read or write via the REST API.
	 *
	 * @since 2.5.6
	 *
	 * @param string $field_name The field name.
	 * @param Pods   $pod        Pods object.
	 * @param string $mode       Are we checking read or write?
	 *
	 * @return bool If supports, true, else false.
	 */
	public static function field_allowed_to_extend( $field_name, $pod, $mode = 'read' ) {

		$allowed = false;

		if ( is_object( $pod ) ) {
			$field = $pod->fields( $field_name );

			if ( $field ) {
				$pod_options = $pod->pod_data['options'];

				$read_all  = (int) pods_v( 'read_all', $pod_options, 0 );
				$write_all = (int) pods_v( 'write_all', $pod_options, 0 );

				if ( 'read' === $mode && 1 === $read_all ) {
					$allowed = true;
				} elseif ( 'write' === $mode && 1 === $write_all ) {
					$allowed = true;
				} else {
					$rest_read  = (int) $pod->fields( $field_name, 'rest_read' );
					$rest_write = (int) $pod->fields( $field_name, 'rest_write' );

					if ( 'read' === $mode && 1 === $rest_read ) {
						$allowed = true;
					} elseif ( 'write' === $mode && 1 === $rest_write ) {
						$allowed = true;
					}
				}
			}
		}

		return $allowed;

	}

}
