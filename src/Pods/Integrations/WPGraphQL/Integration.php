<?php

namespace Pods\Integrations\WPGraphQL;

use Exception;
use Pods\Whatsit\Field as Pod_Field;
use Pods\Whatsit\Pod;
use Pods\Integrations\WPGraphQL\Field;
use PodsForm;

/**
 * Integration specific functionality.
 *
 * @since   2.9.0
 */
class Integration {

	/**
	 * Get the list of requirement checks and error messages.
	 *
	 * @since 2.9.0
	 *
	 * @return array List of requirement checks and error messages.
	 */
	public function get_requirements() {
		return [
			[
				// WPGraphQL should be installed.
				'check'   => defined( 'WPGRAPHQL_VERSION' ),
			],
			[
				// WPGraphQL should be the minimum required version.
				'check'   => defined( 'WPGRAPHQL_VERSION' ) && version_compare( '1.1.3', WPGRAPHQL_VERSION, '<=' ),
				'message' => __( 'You need WPGraphQL 1.1.3+ installed and activated in order to use the Pods WPGraphQL integration.', 'pods' ),
			],
			[
				// Pods Pro WPGraphQL should be deactivated.
				'check'   => ! class_exists( \Pods_Pro\WPGraphQL\Plugin::class ),
				'message' => __( 'You can now deactivate the Pods Pro WPGraphQL Add-On because it is now officially available as part of Pods 2.9+.', 'pods' ),
			],
		];
	}

	/**
	 * Add the class hooks.
	 *
	 * @since 2.9.0
	 */
	public function hook() {
		add_filter( 'graphql_register_types', [ $this, 'register_types' ] );
		add_filter( 'graphql_register_types', [ $this, 'register_connections' ], 99 );
		add_filter( 'register_post_type_args', [ $this, 'add_graphql_support_for_post_type' ], 10, 2 );
		add_filter( 'register_taxonomy_args', [ $this, 'add_graphql_support_for_taxonomy' ], 10, 2 );
	}

	/**
	 * Remove the class hooks.
	 *
	 * @since 2.9.0
	 */
	public function unhook() {
		remove_filter( 'graphql_register_types', [ $this, 'register_types' ] );
		remove_filter( 'graphql_register_types', [ $this, 'register_connections' ], 99 );
		remove_filter( 'register_post_type_args', [ $this, 'add_graphql_support_for_post_type' ] );
		remove_filter( 'register_taxonomy_args', [ $this, 'add_graphql_support_for_taxonomy' ] );
	}

	/**
	 * Register the types and their fields with GraphQL.
	 *
	 * @since 2.9.0
	 */
	public function register_types() {
		// @todo Fetch list of Pods and set up custom types.
		$api = pods_api();

		$params = [
			'options' => [
				'wpgraphql_enabled' => 1,
			],
		];

		$pods = $api->load_pods( $params );

		foreach ( $pods as $pod ) {
			$pod_graphql_info = $this->get_graphql_info_for_pod( $pod );

			// Skip the Pod if GraphQL is not enabled.
			if ( ! $pod_graphql_info ) {
				continue;
			}

			$field_params = [];

			// Only fetch the fields that are enabled if all are not enabled.
			if ( ! $pod_graphql_info['all_fields_enabled'] ) {
				$field_params = [
					'options' => [
						'wpgraphql_enabled' => 1,
					],
				];
			}

			$fields = $pod->get_fields( $field_params );

			foreach ( $fields as $field ) {
				$field_graphql_info = $this->get_graphql_info_for_field( $field, $pod_graphql_info );

				// Skip the Field if GraphQL is not enabled.
				if ( ! $pod_graphql_info['all_fields_enabled'] && ! $field_graphql_info ) {
					continue;
				}

				$args = [
					'type_name'         => $pod_graphql_info['singular_name'],
					'related_type_name' => $field_graphql_info['related_to_name'],
					'field_name'        => $field_graphql_info['singular_name'],
					'graphql_type'      => $field_graphql_info['type'],
					'graphql_format'    => '',
					'related_limit'     => $field_graphql_info['related_limit'],
				];

				if ( 'pick' === $field['type'] ) {
					$args['graphql_format'] = $field_graphql_info['pick_format'];
				} elseif ( 'file' === $field['type'] ) {
					$args['graphql_format'] = $field_graphql_info['file_format'];
				}

				$args = array_merge( $field_graphql_info, $args );

				new Field( $field, $pod, $args );
			}
		}
	}

	/**
	 * Get the GraphQL information for the Pod.
	 *
	 * @since 2.9.0
	 *
	 * @param Pod        $pod    The pod object.
	 * @param null|array $labels The list of labels or null if not referenced.
	 *
	 * @return array|null The GraphQL information for the Pod or null if not setup correctly.
	 */
	public function get_graphql_info_for_pod( $pod, $labels = null ) {
		static $graphql_cached = [];

		$pod_name = $pod->get_name();

		if ( isset( $graphql_cached[ $pod_name ] ) ) {
			return $graphql_cached[ $pod_name ];
		}

		$graphql_info = [
			'enabled'            => filter_var( $pod->get_arg( 'wpgraphql_enabled', $pod->get_arg( 'pods_pro_wpgraphql_enabled', false ) ), FILTER_VALIDATE_BOOLEAN ),
			'all_fields_enabled' => filter_var( $pod->get_arg( 'wpgraphql_all_fields_enabled', $pod->get_arg( 'pods_pro_wpgraphql_all_fields_enabled', false ) ), FILTER_VALIDATE_BOOLEAN ),
			'pick_format'        => $pod->get_arg( 'wpgraphql_pick_format', $pod->get_arg( 'pods_pro_wpgraphql_pick_format', 'connection', true ), true ),
			'file_format'        => $pod->get_arg( 'wpgraphql_file_format', $pod->get_arg( 'pods_pro_wpgraphql_file_format', 'connection', true ), true ),
			'singular_name'      => $pod_name,
			'plural_name'        => $pod_name,
		];

		$pod_wpgraphql_singular_name = $pod->get_arg( 'wpgraphql_singular_name', $pod->get_arg( 'pods_pro_wpgraphql_singular_name' ) );
		$pod_wpgraphql_plural_name   = $pod->get_arg( 'wpgraphql_plural_name', $pod->get_arg( 'pods_pro_wpgraphql_plural_name' ) );

		// Get the singular name from the pod and fall back to the singular label of the object.
		if ( ! empty( $pod_wpgraphql_singular_name ) ) {
			$graphql_info['singular_name'] = $pod_wpgraphql_singular_name;
		} elseif ( $labels && ! empty( $labels['singular_name'] ) ) {
			$graphql_info['singular_name'] = $labels['singular_name'];
		} elseif ( ! empty( $pod['label_singular'] ) ) {
			$graphql_info['singular_name'] = $pod['label_singular'];
		}

		// Get the plural name from the pod and fall back to the plural label of the object.
		if ( ! empty( $pod_wpgraphql_plural_name ) ) {
			$graphql_info['plural_name'] = $pod_wpgraphql_plural_name;
		} elseif ( $labels && ! empty( $labels['name'] ) ) {
			$graphql_info['plural_name'] = $labels['name'];
		} elseif ( ! empty( $pod['label'] ) ) {
			$graphql_info['plural_name'] = $pod['label'];
		}

		// Enforce slugs for singular and plural names.
		$graphql_info['singular_name'] = pods_js_name( pods_create_slug( $graphql_info['singular_name'] ) );
		$graphql_info['plural_name']   = pods_js_name( pods_create_slug( $graphql_info['plural_name'] ) );

		// If plural is the same as singular, add an "s" to plural.
		if ( $graphql_info['singular_name'] === $graphql_info['plural_name'] ) {
			$graphql_info['plural_name'] .= 's';
		}

		// If the names don't fit the requirements, we need to bail because WPGraphQL does not support that.
		if ( false === preg_match( '/^[_a-zA-Z][_a-zA-Z0-9]*$/', $graphql_info['singular_name'] ) || false === preg_match( '/^[_a-zA-Z][_a-zA-Z0-9]*$/', $graphql_info['plural_name'] ) ) {
			return null;
		}

		$graphql_cached[ $pod_name ] = $graphql_info;

		return $graphql_cached[ $pod_name ];
	}

	/**
	 * Get the GraphQL information for the Field.
	 *
	 * @since 2.9.0
	 *
	 * @param Pod_Field $field            The field object.
	 * @param array     $pod_graphql_info The Pod GraphQL options.
	 *
	 * @return array|null The GraphQL information for the Field or null if not setup correctly.
	 */
	public function get_graphql_info_for_field( $field, array $pod_graphql_info ) {
		$graphql_info = [
			'enabled'             => filter_var( $field->get_arg( 'wpgraphql_enabled', $field->get_arg( 'pods_pro_wpgraphql_enabled', false ) ), FILTER_VALIDATE_BOOLEAN ),
			'pick_format'         => $field->get_arg( 'wpgraphql_pick_format', $field->get_arg( 'pods_pro_wpgraphql_pick_format', 'connection', true ), true ),
			'file_format'         => $field->get_arg( 'wpgraphql_file_format', $field->get_arg( 'pods_pro_wpgraphql_file_format', 'connection', true ), true ),
			'singular_name'       => $field->get_name(),
			'plural_name'         => $field->get_name(),
			'type'                => 'String',
			'related_to_name'     => '',
			'related_object_type' => $field->get_related_object_type(),
			'related_object_name' => $field->get_related_object_name(),
			'related_limit'       => $field->get_limit(),
		];

		if ( ! empty( $pod_graphql_info['pick_format'] ) && 'inherit' !== $pod_graphql_info['pick_format'] ) {
			$graphql_info['pick_format'] = $pod_graphql_info['pick_format'];
		}

		if ( ! empty( $pod_graphql_info['file_format'] ) && 'inherit' !== $pod_graphql_info['file_format'] ) {
			$graphql_info['file_format'] = $pod_graphql_info['file_format'];
		}

		$field_wpgraphql_singular_name = $field->get_arg( 'wpgraphql_singular_name', $field->get_arg( 'pods_pro_wpgraphql_singular_name' ) );
		$field_wpgraphql_plural_name   = $field->get_arg( 'wpgraphql_plural_name', $field->get_arg( 'pods_pro_wpgraphql_plural_name' ) );

		// Get the singular name from the pod and fall back to the singular label of the object.
		if ( ! empty( $field_wpgraphql_singular_name ) ) {
			$graphql_info['singular_name'] = $field_wpgraphql_singular_name;
		}

		// Get the plural name from the pod and fall back to the plural label of the object.
		if ( ! empty( $field_wpgraphql_plural_name ) ) {
			$graphql_info['plural_name'] = $field_wpgraphql_plural_name;
		}

		// Enforce slugs for singular and plural names.
		$graphql_info['singular_name'] = pods_js_name( pods_create_slug( $graphql_info['singular_name'] ) );
		$graphql_info['plural_name']   = pods_js_name( pods_create_slug( $graphql_info['plural_name'] ) );

		// If plural is the same as singular, add an "s" to plural.
		if ( $graphql_info['singular_name'] === $graphql_info['plural_name'] ) {
			$graphql_info['plural_name'] .= 's';
		}

		// If the names don't fit the requirements, we need to bail because WPGraphQL does not support that.
		if ( false === preg_match( '/^[_a-zA-Z][_a-zA-Z0-9]*$/', $graphql_info['singular_name'] ) || false === preg_match( '/^[_a-zA-Z][_a-zA-Z0-9]*$/', $graphql_info['plural_name'] ) ) {
			return null;
		}

		$number_field_types = PodsForm::number_field_types();

		$field_type = $field->get_type();

		if ( in_array( $field_type, $number_field_types, true ) ) {
			$graphql_info['type'] = 'Float';
		} elseif ( 'boolean' === $field_type ) {
			$graphql_info['type'] = 'Boolean';
		} elseif ( 'pick' === $field_type ) {
			// Set the related GraphQL name.
			$graphql_info['related_to_name'] = $this->get_related_type_from_field( $field, $graphql_info );

			// Handle single/multiple.
			if ( null !== $graphql_info['related_limit'] && 1 !== $graphql_info['related_limit'] ) {
				$graphql_info['type'] = [
					'list_of' => $graphql_info['type'],
				];
			}
		} elseif ( 'file' === $field_type ) {
			// Set the related GraphQL name.
			$graphql_info['related_to_name'] = $this->get_related_type_from_field( $field, $graphql_info );

			// Handle single/multiple.
			if ( null !== $graphql_info['related_limit'] && 1 !== $graphql_info['related_limit'] ) {
				$graphql_info['type'] = [
					'list_of' => $graphql_info['type'],
				];
			}
		}

		return $graphql_info;
	}

	/**
	 * Get the related GraphQL type from the field.
	 *
	 * @since 2.9.0
	 *
	 * @param Pod_Field $field        The field object.
	 * @param array     $graphql_info The GraphQL information for the field.
	 *
	 * @return string|null The GraphQL type or null if not found.
	 */
	public function get_related_type_from_field( $field, array $graphql_info ) {
		if ( empty( $graphql_info['related_object_type'] ) ) {
			return null;
		}

		$object_type = $graphql_info['related_object_type'];
		$object_name = $graphql_info['related_object_name'];

		switch ( $object_type ) {
			case 'post_type':
				if ( empty( $object_name ) ) {
					return null;
				}

				$post_type_object = get_post_type_object( $object_name );

				if ( ! $post_type_object || ! $post_type_object->show_in_graphql || empty( $post_type_object->graphql_single_name ) ) {
					return null;
				}

				return $post_type_object->graphql_single_name;
			case 'post_type_object':
				return 'contentType';
			case 'taxonomy':
				if ( empty( $object_name ) ) {
					return null;
				}

				$taxonomy_object = get_taxonomy( $object_name );

				if ( ! $taxonomy_object || ! $taxonomy_object->show_in_graphql || empty( $taxonomy_object->graphql_single_name ) ) {
					return null;
				}

				return $taxonomy_object->graphql_single_name;
			case 'taxonomy_object':
				return 'taxonomy';
			case 'user':
			case 'comment':
			case 'plugin':
			case 'theme':
			case 'menu':
				return $object_type;
			case 'user_role':
				return 'userRole';
			case 'attachment':
			case 'media':
				return 'mediaItem';
			case 'menu_item':
				return 'menuItem';
			case 'pod':
				// @todo Support ACTs.
				return null;
			case 'pod_type':
				// @todo Support Pod types.
				return null;
			default:
				return null;
		}
	}

	/**
	 * Register the connections with GraphQL.
	 *
	 * @since 2.9.0
	 */
	public function register_connections() {
		// Register the connections that were found when registering the fields with GraphQL.
		Field::register_connections();
	}

	/**
	 * Add GraphQL support to post types.
	 *
	 * @since 2.9.0
	 *
	 * @param array  $args List of arguments for registering a post type.
	 * @param string $name The post type name.
	 *
	 * @return array List of arguments for registering a post type.
	 */
	public function add_graphql_support_for_post_type( $args, $name ) {
		return $this->add_graphql_support( $args, $name, 'post_type' );
	}

	/**
	 * Add GraphQL support to taxonomies.
	 *
	 * @since 2.9.0
	 *
	 * @param array  $args List of arguments for registering a taxonomy.
	 * @param string $name The taxonomy name.
	 *
	 * @return array List of arguments for registering a taxonomy.
	 */
	public function add_graphql_support_for_taxonomy( $args, $name ) {
		return $this->add_graphql_support( $args, $name, 'taxonomy' );
	}

	/**
	 * Add GraphQL support to post types and taxonomies.
	 *
	 * @since 2.9.0
	 *
	 * @param array  $args                  List of arguments for registering a post type or taxonomy.
	 * @param string $name                  The post type or taxonomy name.
	 * @param string $post_type_or_taxonomy Whether the type is a 'post_type' or 'taxonomy'.
	 *
	 * @return array List of arguments for registering a post type or taxonomy.
	 */
	public function add_graphql_support( $args, $name, $post_type_or_taxonomy ) {
		// Do not override other graphql integrations that may already be set up.
		if ( isset( $args['show_in_graphql'] ) ) {
			return $args;
		}

		try {
			$api = pods_api();

			$params = [
				'name' => $name,
			];

			$pod = $api->load_pod( $params );
		} catch ( Exception $exception ) {
			// Something else happened and we should bail.
			pods_debug_log( $exception );

			return $args;
		}

		// The pod does not exist.
		if ( ! $pod ) {
			return $args;
		}

		// The pod is not the right type.
		if ( $post_type_or_taxonomy !== $pod['type'] ) {
			return $args;
		}

		$pod_graphql_args = $this->get_graphql_info_for_pod( $pod, $args['labels'] );

		// If the GraphQL is not enabled or not set up properly.
		if ( ! $pod_graphql_args || ! $pod_graphql_args['enabled'] ) {
			return $args;
		}

		// Set up WPGraphQL arguments.
		$graphql_args = [
			'show_in_graphql'     => $pod_graphql_args['enabled'],
			'graphql_single_name' => $pod_graphql_args['singular_name'],
			'graphql_plural_name' => $pod_graphql_args['plural_name'],
		];

		// Set the WPGraphQL arguments but do not override if they have already been manually set.
		return array_merge( $graphql_args, $args );
	}

}
