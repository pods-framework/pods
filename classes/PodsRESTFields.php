<?php

use Pods\Whatsit\Pod;
use Pods\Whatsit\Field;
use Pods\WP\Revisions;

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
	protected $pod = null;

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
			if ( 'object' !== $this->pod->get_arg( 'rest_api_field_location', 'object', true ) ) {
				return;
			}

			add_action( 'rest_api_init', [ $this, 'add_fields' ] );
			add_filter( 'rest_' . $this->pod->get_name() . '_query', [ $this, 'query_fields' ], 10, 2 );
		}
	}

	/**
	 * Get the Pod object.
	 *
	 * @since 3.2.6
	 *
	 * @return Pod|null The Pod object.
	 */
	public function get_pod(): ?Pod {
		return $this->pod;
	}

	/**
	 * Set the Pod object.
	 *
	 * @since 2.5.6
	 *
	 * @param string|object|Pods|Pod $pod The Pod object which will be normalized and stored.
	 */
	public function set_pod( $pod ) {
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
	 * @since  3.0.9
	 *
	 * @param \WP_REST_Request $request ArrayAccess
	 * @param array $args
	 *
	 * @return array
	 */
	public function query_fields( $args, $request ) {
		$fields     = $this->pod->get_fields();
		$meta_query = [];

		foreach ( $fields as $field ) {
			$name = $field->get_name();
			if ( isset( $request[ $name ] ) ) {
				$value        = $request[ $name ];
				$meta_query[] = [
					'key'     => $name,
					'compare' => is_array( $value ) ? 'IN' : '=',
					'value'   => $value,
				];
			}
		}

		if ( $meta_query ) {
			$args['meta_query']['pods'] = $meta_query;
		}

		return $args;
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
	 * @param Field $field The field object.
	 */
	public function register( $field ) {
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
			$disallowed_field_names = [
				'id',
				'date',
				'date_gmt',
				'guid',
				'modified',
				'modified_gmt',
				'slug',
				'status',
				'type',
				'link',
				'title',
				'content',
				'excerpt',
				'author',
				'featured_media',
				'comment_status',
				'ping_status',
				'sticky',
				'template',
				'format',
				'meta',
				'categories',
				'tags',
				'_links',
			];

			$field_name = $field->get_name();

			if ( in_array( $field_name, $disallowed_field_names, true ) ) {
				return;
			}

			register_rest_field( $object_type, $field_name, $rest_args );
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

		$pod_mode_arg = $mode . '_all';

		$all_fields_can_use_mode = filter_var( $pod->get_arg( $pod_mode_arg, false ), FILTER_VALIDATE_BOOLEAN );
		$all_fields_access       = 'read' === $mode && filter_var( $pod->get_arg( 'read_all_access', false ), FILTER_VALIDATE_BOOLEAN );

		// Check if user must be logged in to access all fields and override whether they can use it.
		if ( $all_fields_can_use_mode && $all_fields_access ) {
			$all_fields_can_use_mode = is_user_logged_in();
		}

		// Maybe get the Field object from the Pod.
		if ( is_string( $field ) ) {
			$field = $pod->get_field( $field );
		}

		// Check if we have a valid $field.
		if ( ! $field instanceof Field ) {
			return $all_fields_can_use_mode;
		}

		// Field arguments are prefixed with `rest`;
		$mode_arg        = 'rest_' . $mode;
		$mode_access_arg = 'rest_' . $mode . '_access';

		$can_use_mode_value     = $field->get_arg( $mode_arg );
		$can_use_mode_has_value = null !== $can_use_mode_value;

		// Check if we have a value for this mode on the field itself.
		if ( ! $can_use_mode_has_value ) {
			return $all_fields_can_use_mode;
		}

		$can_use_mode = filter_var( $can_use_mode_value, FILTER_VALIDATE_BOOLEAN );
		$access       = 'read' === $mode && filter_var( $field->get_arg( $mode_access_arg, false ), FILTER_VALIDATE_BOOLEAN );

		// Check if user must be logged in to access field and override whether they can use it.
		if ( $can_use_mode && $access ) {
			$can_use_mode = is_user_logged_in();
		}

		return $can_use_mode;
	}

}
