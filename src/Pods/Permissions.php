<?php

namespace Pods;

use PodsForm;
use Pods\Whatsit;
use WP_User;

/**
 * Permissions class.
 *
 * @since 2.8.0
 */
class Permissions {

	/**
	 * Get the normalized user object.
	 *
	 * @since 2.8.0
	 *
	 * @param null|int|WP_User $user The user ID or object (default: current user).
	 *
	 * @return WP_User|false The user object or false if not found.
	 */
	public function get_user( $user = null ) {
		// Get current user ID if needed.
		if ( null === $user ) {
			$user = is_user_logged_in() ? get_current_user_id() : false;
		}

		// Get user object if we have an ID.
		if ( is_numeric( $user ) ) {
			$user = 0 < $user ? get_userdata( $user ) : false;
		}

		if ( ! $user || ! $user->exists() ){
			return false;
		}

		return $user;
	}

	/**
	 * Determine whether a user has permission to an object.
	 *
	 * @since 2.8.0
	 *
	 * @param array|Whatsit    $object The object data.
	 * @param null|int|WP_User $user   The user ID or object (default: current user).
	 *
	 * @return bool Whether a user has permission to an object.
	 */
	public function user_has_permission( $object, $user = null ) {
		$user = $this->get_user( $user );

		// Merge config options if pre Pods 2.8 format provided.
		if ( isset( $object['options'] ) ) {
			$object = pods_config_merge_data( $object, $object['options'] );
		}

		if ( $this->is_input_disallowed( $object ) ) {
			$user_has_permission = false;
		} elseif ( $this->is_admin_only( $object ) ) {
			$user_has_permission = $this->is_user_an_admin( null, $user );
		} else {
			$user_has_permission = (
				(
					! $this->is_logged_in_only( $object )
					|| is_user_logged_in()
				)
				&& ! $this->are_roles_restricted_for_user( $object, $user )
				&& ! $this->are_capabilities_restricted_for_user( $object, $user )
			);
		}

		/**
		 * Allow filtering whether a user has permission to an object.
		 *
		 * @since 2.8.0
		 *
		 * @param bool             $user_has_permission Whether a user has permission to an object.
		 * @param array|Whatsit    $object              The object data.
		 * @param null|int|WP_User $user                The user ID or object (default: current user).
		 */
		return apply_filters( 'pods_permissions_user_has_permission', $user_has_permission, $object, $user );
	}

	/**
	 * Check if permissions are restricted for an object.
	 *
	 * @since 2.8.0
	 *
	 * @param array|Whatsit $object The object data.
	 *
	 * @return bool Whether the permissions are restricted for an object.
	 */
	public function are_permissions_restricted( $object ) {
		if ( isset( $object['options'] ) ) {
			$object = pods_config_merge_data( $object, $object['options'] );
		}

		$are_permissions_restricted = (
			$this->is_input_disallowed( $object )
			|| $this->is_logged_in_only( $object )
			|| $this->is_admin_only( $object )
			|| $this->get_restricted_roles( $object )
			|| $this->get_restricted_capabilities( $object )
		);

		/**
		 * Allow filtering whether permissions are restricted for an object.
		 *
		 * @since 2.8.0
		 *
		 * @param bool          $are_permissions_restricted Whether the permissions are restricted for an object.
		 * @param array|Whatsit $object                     The object data.
		 */
		return apply_filters( 'pods_permissions_are_permissions_restricted', $are_permissions_restricted, $object );
	}

	/**
	 * Determine whether roles are restricted for user on an object.
	 *
	 * @since 2.8.0
	 *
	 * @param array|Whatsit    $object The object data.
	 * @param null|int|WP_User $user   The user ID or object (default: current user).
	 *
	 * @return bool Whether roles are restricted for user on an object.
	 */
	public function are_roles_restricted_for_user( $object, $user = null ) {
		$restricted_roles = $this->get_restricted_roles( $object );

		// Do not restrict if no restricted roles provided.
		if ( ! $restricted_roles ) {
			return false;
		}

		$user = $this->get_user( $user );

		// Restrict for invalid users.
		if ( ! $user ) {
			return true;
		}

		$matching_roles = array_intersect( $restricted_roles, $user->roles );

		// Restrict if we do not have any matching roles.
		return empty( $matching_roles );
	}

	/**
	 * Get the list of restricted capabilities.
	 *
	 * @since 2.8.0
	 *
	 * @param array|Whatsit $object The object data.
	 *
	 * @return array|false The list of restricted capabilities or false if not restricted.
	 */
	public function get_restricted_roles( $object ) {
		if ( 0 === (int) pods_v( 'restrict_role', $object, 0 ) ) {
			return false;
		}

		$roles_allowed = pods_v( 'roles_allowed', $object, '' );

		if ( '' !== $roles_allowed ) {
			$roles_allowed = maybe_unserialize( $roles_allowed );

			if ( ! is_array( $roles_allowed ) ) {
				$roles_allowed = explode( ',', $roles_allowed );
			}

			$roles_allowed = array_unique( array_filter( $roles_allowed ) );
		}

		return ! empty( $roles_allowed ) ? $roles_allowed : false;
	}

	/**
	 * Determine whether capabilities are restricted for user on an object.
	 *
	 * @since 2.8.0
	 *
	 * @param array|Whatsit    $object The object data.
	 * @param null|int|WP_User $user   The user ID or object (default: current user).
	 *
	 * @return bool Whether capabilities are restricted for user on an object.
	 */
	public function are_capabilities_restricted_for_user( $object, $user = null ) {
		$restricted_capabilities = $this->get_restricted_capabilities( $object );

		// Do not restrict if no restricted capabilities provided.
		if ( ! $restricted_capabilities ) {
			return false;
		}

		$user = $this->get_user( $user );

		// Restrict for invalid users.
		if ( ! $user ) {
			return true;
		}

		$is_restricted = true;

		// Check if user has ANY of the capabilities.
		foreach ( $restricted_capabilities as $capabilities ) {
			$is_set_restricted = false;

			// Check if user has ALL of the capabilities.
			foreach ( $capabilities as $capability ) {
				if ( ! $user->has_cap( $capability ) ) {
					$is_set_restricted = true;

					break;
				}
			}

			if ( ! $is_set_restricted ) {
				$is_restricted = false;

				break;
			}
		}

		return $is_restricted;
	}

	/**
	 * Get the list of restricted capabilities.
	 *
	 * @since 2.8.0
	 *
	 * @param array|Whatsit $object The object data.
	 *
	 * @return array[]|false The list of restricted sets of capabilities or false if not restricted.
	 */
	public function get_restricted_capabilities( $object ) {
		if ( 0 === (int) pods_v( 'restrict_capability', $object, 0 ) ) {
			return false;
		}

		$capability_allowed = pods_v( 'capability_allowed', $object, '' );

		if ( '' !== $capability_allowed ) {
			$capability_allowed = maybe_unserialize( $capability_allowed );

			if ( ! is_array( $capability_allowed ) ) {
				$capability_allowed = explode( ',', $capability_allowed );
			}

			// Force all to lowercase.
			$capability_allowed = array_map( 'strtolower', $capability_allowed );

			// Get unique list of capabilities.
			$capability_allowed = array_unique( $capability_allowed );

			foreach ( $capability_allowed as $k => $capability ) {
				if ( ! is_array( $capability ) ) {
					$capability = explode( '&&', $capability );
				}

				// Force all to lowercase.
				$capability = array_map( 'strtolower', $capability );

				$capability = array_unique( array_filter( $capability ) );

				$capability_allowed[ $k ] = $capability;
			}

			$capability_allowed = array_filter( $capability_allowed );
		}

		return ! empty( $capability_allowed ) ? $capability_allowed : false;
	}

	/**
	 * Determine whether permissions are restricted to admins only.
	 *
	 * @since 2.8.0
	 *
	 * @param array|Whatsit $object The object data.
	 *
	 * @return bool Whether permissions are restricted to admins only.
	 */
	public function is_logged_in_only( $object ) {
		return 1 === (int) pods_v( 'logged_in_only', $object, 0 );
	}

	/**
	 * Determine whether permissions are restricted to admins only.
	 *
	 * @since 2.8.0
	 *
	 * @param array|Whatsit $object The object data.
	 *
	 * @return bool Whether permissions are restricted to admins only.
	 */
	public function is_admin_only( $object ) {
		return 1 === (int) pods_v( 'admin_only', $object, 0 );
	}

	/**
	 * Determine whether input is disallowed.
	 *
	 * @since 2.8.0
	 *
	 * @param array|Whatsit $object The object data.
	 *
	 * @return bool Whether input is disallowed.
	 */
	public function is_input_disallowed( $object ) {
		$non_input_field_types = PodsForm::non_input_field_types();

		return in_array( pods_v( 'type', $object ), $non_input_field_types, true );
	}

	/**
	 * Determine whether a user is a Pods Admin.
	 *
	 * @since 2.8.0
	 *
	 * @param string|array     $additional_capabilities Additional capabilities to check.
	 * @param null|int|WP_User $user                    The user ID or object (default: current user).
	 *
	 * @return bool Whether a user is a Pods Admin.
	 */
	public function is_user_an_admin( $additional_capabilities = null, $user = null ) {
		$user = $this->get_user( $user );

		// Invalid user is not an admin.
		if ( ! $user ) {
			return false;
		}

		$is_multisite = is_multisite();

		if ( empty( $additional_capabilities ) ) {
			$additional_capabilities = [];
		} elseif ( ! is_array( $additional_capabilities ) ) {
			$additional_capabilities = explode( ',', $additional_capabilities );
		}

		if ( $is_multisite && is_super_admin( $user->ID ) ) {
			/**
			 * Allow filtering whether a user is a Pods Admin.
			 *
			 * @since 2.3.5
			 *
			 * @param bool    $is_admin                Whether a user is a Pods Admin.
			 * @param array   $additional_capabilities Additional capabilities to check.
			 * @param string  $capability_match        The matching capability.
			 * @param WP_User $user                    The user object.
			 */
			return apply_filters( 'pods_is_admin', true, $additional_capabilities, '_super_admin', $user );
		}

		$pods_admin_capabilities = [];

		if ( ! $is_multisite ) {
			// Default is_super_admin() checks against this capability.
			$pods_admin_capabilities[] = 'delete_users';
		}

		/**
		 * Allow filtering whether a user is a Pods Admin.
		 *
		 * @since 2.3.5
		 *
		 * @param array   $pods_admin_capabilities The list of capabilities to check for a Pods Admin.
		 * @param array   $additional_capabilities Additional capabilities to check.
		 * @param WP_User $user                    The user object.
		 */
		$pods_admin_capabilities = apply_filters( 'pods_admin_capabilities', $pods_admin_capabilities, $additional_capabilities, $user );

		$check_capabilities = array_unique( array_filter( array_merge( $pods_admin_capabilities, $additional_capabilities ) ) );

		$match            = false;
		$capability_match = null;

		foreach ( $check_capabilities as $capability ) {
			if ( $user->has_cap( $capability ) ) {
				$match = true;

				$capability_match = $capability;

				break;
			}
		}

		/**
		 * Allow filtering whether a user is a Pods Admin.
		 *
		 * @since 2.3.5
		 *
		 * @param bool    $is_admin                Whether a user is a Pods Admin.
		 * @param array   $additional_capabilities Additional capabilities to check.
		 * @param string  $capability_match        The matching capability.
		 * @param WP_User $user                    The user object.
		 */
		return apply_filters( 'pods_is_admin', $match, $additional_capabilities, $capability_match );
	}

}
