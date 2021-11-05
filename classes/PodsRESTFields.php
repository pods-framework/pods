<?php

use Pods\Whatsit\Pod;
use Pods\Whatsit\Field;

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
	 * Pod object
	 *
	 * @since  2.5.6
	 *
	 * @access protected
	 *
	 * @var null|Pod
	 */
	protected $pod;

	/**
	 * Constructor for class
	 *
	 * @since 2.5.6
	 *
	 * @param string|object|Pods|Pod $pod Pods object
	 */
	public function __construct( $pod ) {
		if ( ! function_exists( 'register_rest_field' ) ) {
			return;
		}

		$this->set_pod( $pod );

		if ( $this->pod ) {
			add_action( 'rest_api_init', [ $this, 'add_fields' ] );
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
		$this->pod = null;

		// Normalize the $pod object.
		$pod = pods_config_for_pod( $pod );

		// Check if the $pod is valid.
		if ( false === $pod ) {
			return;
		}

		$type = $pod->get_type();

		$supported_pod_types = [
			'post_type' => true,
			'taxonomy'  => true,
			'media'     => true,
			'user'      => true,
			'comment'   => true,
		];

		// Check if the $type is supported.
		if ( ! isset( $supported_pod_types[ $type ] ) ) {
			return;
		}

		$rest_enable = filter_var( $pod->get_arg( 'rest_enable', false ), FILTER_VALIDATE_BOOLEAN );

		// Check if this Pod has REST API integration enabled.
		if ( ! $rest_enable ) {
			return;
		}

		$this->pod = $pod;
	}

	/**
	 * Add fields, based on options to REST read/write requests
	 *
	 * @since  2.5.6
	 */
	public function add_fields() {
		$pod_name = $this->pod->get_name();
		$pod_type = $this->pod->get_type();
		$fields   = $this->pod->get_fields();

		$rest_hook_name = $pod_name;

		if ( 'media' === $pod_name ) {
			$rest_hook_name = 'attachment';
		} elseif ( 'comment' === $pod_type ) {
			$rest_hook_name = 'comment';
		}

		$rest_hook_name = 'rest_insert_' . $rest_hook_name;

		if ( ! has_action( $rest_hook_name, [ 'PodsRESTHandlers', 'save_handler' ] ) ) {
			add_action( $rest_hook_name, [ 'PodsRESTHandlers', 'save_handler' ], 10, 3 );
		}

		foreach ( $fields as $field ) {
			$this->register( $field );
		}
	}

	/**
	 * Register fields and their callbacks for read/write via REST
	 *
	 * @since  2.5.6
	 *
	 * @access protected
	 *
	 * @param Field $field The field object.
	 */
	protected function register( $field ) {
		$rest_read  = self::field_allowed_to_extend( $field, $this->pod, 'read' );
		$rest_write = self::field_allowed_to_extend( $field, $this->pod, 'write' );

		// Check if we have any access.
		if ( ! $rest_read && ! $rest_write ) {
			return;
		}

		$rest_args = [];

		if ( $rest_read ) {
			$rest_args['get_callback'] = [ 'PodsRESTHandlers', 'get_handler' ];
		}

		if ( $rest_write ) {
			$rest_args['pods_update'] = true;
		}

		// Get the object type for register_rest_field(), this is documented weird and we should just pass the object name.
		$object_type = $this->pod->get_name();

		// Use the "attachment" post type if the Pod name is "media".
		if ( 'media' === $object_type ) {
			$object_type = 'attachment';
		}

		if ( ! empty( $rest_args ) ) {
			register_rest_field( $object_type, $field->get_name(), $rest_args );
		}
	}

	/**
	 * Check if a field supports read or write via the REST API.
	 *
	 * @since 2.5.6
	 *
	 * @param string|Field $field The field object or name.
	 * @param Pod|Pods     $pod   The Pod object.
	 * @param string       $mode  The mode to use (read or write).
	 *
	 * @return bool If supports, true, else false.
	 */
	public static function field_allowed_to_extend( $field, $pod, $mode ) {
		// Normalize the $pod object.
		$pod = pods_config_for_pod( $pod );

		// Check if the $pod is valid.
		if ( false === $pod ) {
			return false;
		}

		$all_fields_access = filter_var( $pod->get_arg( $mode . '_all', false ), FILTER_VALIDATE_BOOLEAN );

		// Check for access on all fields.
		if ( $all_fields_access ) {
			return true;
		}

		// Maybe get the Field object from the Pod.
		if ( is_string( $field ) ) {
			$field = $pod->get_field( $field );
		}

		// Check if we have a valid $field.
		if ( ! $field instanceof Field ) {
			return false;
		}

		// Field arguments are prefixed with `rest`;
		$mode_arg = 'rest_' . $mode;

		return filter_var( $field->get_arg( $mode_arg, false ), FILTER_VALIDATE_BOOLEAN );
	}

}
