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
		return is_numeric( $value ) && (bool) get_post( (int) $value );
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
}
