<?php

namespace Pods\REST\V1\Validator;

use Tribe__Validator__Base as Validator_Base;
use Tribe__Validator__Interface as Validator_Interface;

/**
 * Class Base
 *
 * @since 2.8.0
 */
class Base extends Validator_Base implements Validator_Interface {
	/**
	 * Whether the value corresponds to an existing post ID or not.
	 *
	 * @since 2.8.0
	 *
	 * @param mixed $value
	 *
	 * @return bool
	 */
	public function is_post_id( $value ) {
		return is_numeric( $value ) && get_post( (int) $value );
	}

	/**
	 * Determine whether a Pod / Item ID is valid.
	 *
	 * @since 2.8.11
	 *
	 * @param string     $pod        The pod name.
	 * @param int|string $id_or_slug The Item ID or slug.
	 *
	 * @return bool Whether the Pod / Item ID is valid.
	 */
	public function is_pod_item_id_or_slug_valid( $pod, $id_or_slug ) {
		$pod = pods_get_instance( $pod, $id_or_slug );

		return $pod && ! is_wp_error( $pod ) && $pod->valid() && $pod->exists();
	}

	/**
	 * Determine whether a Pod / Item ID is valid.
	 *
	 * @since 2.8.11
	 *
	 * @param string     $pod The pod name.
	 * @param int|string $id  The item ID.
	 *
	 * @return bool Whether the Pod / Item ID is valid.
	 */
	public function is_pod_item_id_valid( $pod, $id ) {
		return is_numeric( $id ) && $this->is_pod_id_or_slug_valid( $pod, (int) $id );
	}

	/**
	 * Determine whether a Pod / Item slug is valid.
	 *
	 * @since 2.8.11
	 *
	 * @param string     $pod  The pod name.
	 * @param int|string $slug The Item slug.
	 *
	 * @return bool Whether the Pod / Item slug is valid.
	 */
	public function is_pod_item_slug_valid( $pod, $slug ) {
		return $this->is_pod_id_or_slug_valid( $pod, $slug );
	}

	/**
	 * Whether the value corresponds to an existing Pod slug or not.
	 *
	 * @since 2.8.0
	 *
	 * @param mixed $value
	 *
	 * @return bool
	 */
	public function is_pod_slug( $value ) {
		return null !== get_page_by_path( $value, OBJECT, '_pods_pod' );
	}

	/**
	 * Whether the value corresponds to an existing Group slug or not.
	 *
	 * @since 2.8.0
	 *
	 * @param mixed $value
	 *
	 * @return bool
	 */
	public function is_group_slug( $value ) {
		return null !== get_page_by_path( $value, OBJECT, '_pods_group' );
	}

	/**
	 * Whether the value corresponds to an existing Field slug or not.
	 *
	 * @since 2.8.0
	 *
	 * @param mixed $value
	 *
	 * @return bool
	 */
	public function is_field_slug( $value ) {
		return null !== get_page_by_path( $value, OBJECT, '_pods_field' );
	}

	/**
	 * Whether the value corresponds to an existing Pod ID or not.
	 *
	 * @since 2.8.0
	 *
	 * @param mixed $value
	 *
	 * @return bool
	 */
	public function is_pod_id( $value ) {
		if ( ! is_numeric( $value ) ) {
			return false;
		}

		return '_pods_pod' === get_post_type( (int) $value );
	}

	/**
	 * Whether the value corresponds to an existing Group ID or not.
	 *
	 * @since 2.8.0
	 *
	 * @param mixed $value
	 *
	 * @return bool
	 */
	public function is_group_id( $value ) {
		if ( ! is_numeric( $value ) ) {
			return false;
		}

		return '_pods_group' === get_post_type( (int) $value );
	}

	/**
	 * Whether the value corresponds to an existing Field ID or not.
	 *
	 * @since 2.8.0
	 *
	 * @param mixed $value
	 *
	 * @return bool
	 */
	public function is_field_id( $value ) {
		if ( ! is_numeric( $value ) ) {
			return false;
		}

		return '_pods_field' === get_post_type( (int) $value );
	}

	/**
	 * Determine whether the value is valid JSON.
	 *
	 * @param mixed $value The value.
	 *
	 * @return bool Whether the value is valid JSON.
	 */
	public function is_json( $value ) {
		return is_string( $value ) && ! is_scalar( json_decode( $value ) );
	}

	/**
	 * Handle other potential methods automatically if possible.
	 *
	 * @since 2.8.11
	 *
	 * @param string $name      The method name.
	 * @param array  $arguments The arguments provided to the method.
	 *
	 * @return mixed The method return response.
	 */
	public function __call( $name, array $arguments ) {
		$mapped = [
			'is_pod_item_id_valid_for_pod_'         => 'is_pod_item_id_valid',
			'is_pod_item_slug_valid_for_pod_'       => 'is_pod_item_slug_valid',
			'is_pod_item_id_or_slug_valid_for_pod_' => 'is_pod_item_id_or_slug_valid',
		];

		foreach ( $mapped as $prefix_method => $mapped_method ) {
			if ( 0 === strpos( $name, $prefix_method ) ) {
				$pod_name = str_replace( $prefix_method, '', $name );

				array_unshift( $pod_name, $arguments );

				return $this->{$mapped_method}( ...$arguments );
			}
		}
	}
}
