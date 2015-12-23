<?php
/**
 * Class PodsRESTHandlers
 *
 * Handlers for reading and writing Pods fields via REST API
 *
 * @package Pods
 * @since   2.5.6
 */
class PodsRESTHandlers {

	/**
	 * Holds a Pods object to avoid extra DB queries
	 *
	 * @since 2.5.6
	 *
	 * @var Pods
	 */
	private static $pod;

	/**
	 * Get Pod object
	 *
	 * @since 2.5.6
	 *
	 * @param $pod_name
	 * @param $id
	 *
	 * @return bool|Pods
	 */
	protected static function get_pod( $pod_name, $id ) {

		if ( ! self::$pod || self::$pod->pod != $pod_name ) {
			self::$pod = pods( $pod_name, $id, true );
		}

		if ( self::$pod && self::$pod->id != $id ) {
			self::$pod->fetch( $id );
		}

		return self::$pod;

	}

	/**
	 * Handler for getting custom field data.
	 *
	 * @since 2.5.6
	 *
	 * @param array           $object     The object from the response
	 * @param string          $field_name Name of field
	 * @param WP_REST_Request $request    Current request
	 *
	 * @return mixed
	 */
	public static function get_handler( $object, $field_name, $request ) {

		$pod_name = pods_v( 'type', $object );
		$id       = pods_v( 'id', $object );
		$pod      = self::get_pod( $pod_name, $id );
		if ( $pod && PodsRESTFields::field_allowed_to_extend( $field_name, $pod, 'read' ) ) {
			$params     = null;
			$field_data = $pod->fields( $field_name );
			if ( 'pick' == pods_v( 'type', $field_data ) ) {
				$output_type = pods_v( 'rest_pick_response', $field_data['options'], 'array' );
				if ( 'array' == $output_type ) {

					$related_pod = $pod->field( $field_name, array( 'output' => 'pod' ) );
					$fields      = $related_pod->fields();
					$fields      = array_keys( $fields );
					if ( isset( $related_pod->pod_data['object_fields'] ) && ! empty( $related_pod->pod_data['object_fields'] ) ) {
						$fields = array_merge( $fields, array_keys( $related_pod->pod_data['object_fields'] ) );
					}

					/**
					 * What fields to show in a related field REST response.
					 *
					 * @since 0.0.1
					 *
					 * @param array                  $fields     The fields to show
					 * @param string                 $field_name The name of the field
					 * @param object|Pods            $pod        The Pods object for Pod relationship is from.
					 * @param object|Pods            $pod        The Pods object for Pod relationship is to.
					 * @param int                    $id         Current item ID
					 * @param object|WP_REST_Request Current     request object.
					 */
					$fields = apply_filters( 'pods_rest_api_fields_for_relationship_response', $fields, $field_name, $pod, $related_pod, $id, $request );

					$depth = pods_v( 'rest_pick_depth', $field_data['options'], 2 );

					/**
					 * What depth to use for a related field REST response.
					 *
					 * @since 0.0.1
					 *
					 * @param array                  $depth      The depth.
					 * @param string                 $field_name The name of the field
					 * @param object|Pods            $pod        The Pods object for Pod relationship is from.
					 * @param object|Pods            $pod        The Pods object for Pod relationship is to.
					 * @param int                    $id         Current item ID
					 * @param object|WP_REST_Request Current     request object.
					 */
					$depth = apply_filters( 'pods_rest_api_depth_for_relationship_response', $depth, $field_name, $pod, $related_pod, $id, $request );

					$params = array(
						'fields' => $fields,
						'depth'  => $depth

					);

					$data = $pod->api->export_pod_item( $related_pod, $params );

					return $data;

				}

				$params           = array();
				$params['output'] = $output_type;
			}

			return $pod->field( $field_name, $params );

		}

		return false;

	}

	/**
	 * Handler for updating custom field data.
	 *
	 * @since 2.5.6
	 *
	 * @param mixed  $value      Value to write
	 * @param object $object     The object from the response
	 * @param string $field_name Name of field
	 *
	 * @return bool|int
	 */
	public function write_handler( $value, $object, $field_name ) {

		$pod_name = pods_v( 'type', $object );

		$id = pods_v( 'id', $object );

		$pod = self::get_pod( $pod_name, $id );

		if ( $pod && PodsRESTFields::field_allowed_to_extend( $field_name, $pod, 'write' ) ) {
			$pod->save( $field_name, $value, $id );

			return $pod->field( $field_name );
		}

		return false;

	}

	/**
	 * Add REST API support to a post type
	 *
	 * @since 2.5.6
	 *
	 * @param string     $post_type_name Name of post type
	 * @param bool|false $rest_base      Optional. Base url segment. If not set, post type name is used
	 * @param string     $controller     Optional, controller class for route. If not set "WP_REST_Posts_Controller" is
	 *                                   used.
	 */
	public static function post_type_rest_support( $post_type_name, $rest_base = false, $controller = 'WP_REST_Posts_Controller' ) {

		global $wp_post_types;

		if ( isset( $wp_post_types[ $post_type_name ] ) ) {
			if ( ! $rest_base ) {
				$rest_base = $post_type_name;
			}

			$wp_post_types[ $post_type_name ]->show_in_rest          = true;
			$wp_post_types[ $post_type_name ]->rest_base             = $rest_base;
			$wp_post_types[ $post_type_name ]->rest_controller_class = $controller;
		}

	}

	/**
	 * Add REST API support to an already registered taxonomy.
	 *
	 * @since 2.5.6
	 *
	 * @param string     $taxonomy_name Taxonomy name.
	 * @param bool|false $rest_base     Optional. Base url segment. If not set, taxonomy name is used.
	 * @param string     $controller    Optional, controller class for route. If not set "WP_REST_Terms_Controller" is
	 *                                  used.
	 */
	public static function taxonomy_rest_support( $taxonomy_name, $rest_base = false, $controller = 'WP_REST_Terms_Controller' ) {

		global $wp_taxonomies;

		if ( isset( $wp_taxonomies[ $taxonomy_name ] ) ) {
			if ( ! $rest_base ) {
				$rest_base = $taxonomy_name;
			}

			$wp_taxonomies[ $taxonomy_name ]->show_in_rest          = true;
			$wp_taxonomies[ $taxonomy_name ]->rest_base             = $rest_base;
			$wp_taxonomies[ $taxonomy_name ]->rest_controller_class = $controller;
		}

	}

	/**
	 * Check if a Pod supports extending core REST response.
	 *
	 * @since 2.5.6
	 *
	 * @param array|Pods $pod Pod object or the pod_data array
	 *
	 * @return bool
	 */
	public static function pod_extends_core_route( $pod ) {

	    $enabled = false;

	    if ( is_object( $pod ) ) {
	        $pod = $pod->pod_data;
	    }

	    if ( is_array( $pod ) ) {
	        $enabled = (boolean) pods_v( 'rest_enable', $pod['options'], false );
	    }

	    return $enabled;

	}

}