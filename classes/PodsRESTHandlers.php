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

		if ( ! self::$pod || self::$pod->pod !== $pod_name ) {
			self::$pod = pods( $pod_name, $id, true );
		}

		if ( self::$pod && (int) self::$pod->id !== (int) $id ) {
			self::$pod->fetch( $id );
		}

		return self::$pod;

	}

	/**
	 * Handler for getting custom field data.
	 *
	 * @since 2.5.6
	 *
	 * @param array           $object      The object from the response
	 * @param string          $field_name  Name of field
	 * @param WP_REST_Request $request     Current request
	 * @param string          $object_type Type of object
	 *
	 * @return mixed
	 */
	public static function get_handler( $object, $field_name, $request, $object_type ) {

		$pod_name = pods_v( 'type', $object );

		/**
		 * If $pod_name in the line above is empty then the route invoked
		 * may be for a taxonomy, so lets try and check for that
		 *
		 */
		if ( empty( $pod_name ) ) {
			$pod_name = pods_v( 'taxonomy', $object );
		}

		/**
		 * $pod_name is still empty, so check lets check $object_type
		 *
		 */

		if ( empty( $pod_name ) ) {
			if ( 'attachment' === $object_type ) {
				$pod_name = 'media';
			} else {
				$pod_name = $object_type;
			}
		}

		/**
		 * Filter the pod name
		 *
		 * @since 2.6.7
		 *
		 * @param array           $pod_name    Pod name
		 * @param Pods            $object      Rest object
		 * @param string          $field_name  Name of the field
		 * @param WP_REST_Request $request     Current request
		 * @param string          $object_type Rest Object type
		 */
		$pod_name = apply_filters( 'pods_rest_api_pod_name', $pod_name, $object, $field_name, $request, $object_type  );

		$id  = pods_v( 'id', $object );

		if ( empty( $id ) ) {
			$id = pods_v( 'ID', $object );
		}

		$pod = self::get_pod( $pod_name, $id );

		$value = false;

		if ( $pod && PodsRESTFields::field_allowed_to_extend( $field_name, $pod, 'read' ) ) {
			$params = null;

			$field_data = $pod->fields( $field_name );

			if ( 'pick' === pods_v( 'type', $field_data ) ) {
				$output_type = pods_v( 'rest_pick_response', $field_data['options'], 'array' );

				/**
				 * What output type to use for a related field REST response.
				 *
				 * @since 2.7
				 *
				 * @param string                 $output_type The pick response output type.
				 * @param string                 $field_name  The name of the field
				 * @param array                  $field_data  The field data
				 * @param object|Pods            $pod         The Pods object for Pod relationship is from.
				 * @param int                    $id          Current item ID
				 * @param object|WP_REST_Request Current      request object.
				 */
				$output_type = apply_filters( 'pods_rest_api_output_type_for_relationship_response', $output_type, $field_name, $field_data, $pod, $id, $request );

				if ( 'array' === $output_type ) {
					$related_pod_items = $pod->field( $field_name, array( 'output' => 'pod' ) );

					if ( $related_pod_items ) {
						$fields = false;
						$items  = array();
						$depth  = pods_v( 'rest_pick_depth', $field_data['options'], 2 );

						if ( ! is_array( $related_pod_items ) ) {
							$related_pod_items = array( $related_pod_items );
						}

						/**
						 * @var $related_pod Pods
						 */
						foreach ( $related_pod_items as $related_pod ) {
							if ( ! is_object( $related_pod ) || ! is_a( $related_pod, 'Pods' ) ) {
								$items = $related_pod_items;

								break;
							}

							if ( false === $fields ) {
								$fields = $related_pod->fields();
								$fields = array_keys( $fields );

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
							}

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
								'fields'  => $fields,
								'depth'   => $depth,
								'context' => 'rest',
							);

							$items[] = $related_pod->export( $params );
						}

						$value = $items;
					}
				}

				$params = array(
					'output' => $output_type,
				);
			}

			// If no value set yet, get normal field value
			if ( ! $value && ! is_array( $value ) ) {
				$value = $pod->field( $field_name, $params );
			}
		}

		return $value;

	}

	/**
	 * Handle saving of Pod fields from REST API write requests.
	 *
	 * @param WP_Post|WP_Term|WP_User|WP_Comment $object   Inserted or updated object.
	 * @param WP_REST_Request                    $request  Request object.
	 * @param bool                               $creating True when creating an item, false when updating.
	 */
	public static function save_handler( $object, $request, $creating ) {

		if ( is_a( $object, 'WP_Post' ) ) {
			$pod_name = $object->post_type;

			if ( 'attachment' === $pod_name ) {
				$pod_name = 'media';
			}

			$id = $object->ID;
		} elseif ( is_a( $object, 'WP_Term' ) ) {
			$pod_name = $object->taxonomy;

			$id = $object->term_id;
		} elseif ( is_a( $object, 'WP_User' ) ) {
			$pod_name = 'user';

			$id = $object->ID;
		} elseif ( is_a( $object, 'WP_Comment' ) ) {
			$pod_name = 'comment';

			$id = $object->comment_ID;
		} else {
			// Not a supported object
			return;
		}

		$pod = self::get_pod( $pod_name, $id );

		global $wp_rest_additional_fields;

		$rest_enable = (boolean) pods_v( 'rest_enable', $pod->pod_data['options'], false );

		if ( $pod && $rest_enable && ! empty( $wp_rest_additional_fields[ $pod_name ] ) ) {
			$fields = $pod->fields();

			$save_fields = array();

			$params = array(
				'is_new_item' => $creating,
			);

			foreach ( $fields as $field_name => $field ) {
				if ( empty( $wp_rest_additional_fields[ $pod_name ][ $field_name ]['pods_update'] ) ) {
					continue;
				} elseif ( ! isset( $request[ $field_name ] ) ) {
					continue;
				} elseif ( ! PodsRESTFields::field_allowed_to_extend( $field_name, $pod, 'write' ) ) {
					continue;
				}

				$save_fields[ $field_name ] = $request[ $field_name ];
			}

			if ( ! empty( $save_fields ) || $creating ) {
				$pod->save( $save_fields, null, null, $params );
			}
		}

	}

}
