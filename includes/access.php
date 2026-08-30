<?php

// Don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * @package Pods\Global\Functions\Access
 */

/**
 * Normalize Pod information with a Pods object or object info.
 *
 * @since 3.1.0
 *
 * @param array $args {
 *      The arguments to use.
 *
 *      @type string|null     $object_type The object type.
 *      @type string|null     $object_name The object name.
 *      @type int|string|null $item_id     The item ID.
 *      @type Pods|null       $pods        The Pods object.
 *      @type Pod|null        $pod         The Pod object.
 *      @type bool            $build_pods  Whether to try to build a Pods object from the object type/name/ID (false by default).
 *      @type bool            $build_pod   Whether to try to build a Pod object from the object type/name (false by default).
 * }
 *
 * @return array {
 *      The arguments to use.
 *
 *      @type string|null     $object_type The object type (if set).
 *      @type string|null     $object_name The object name (if set).
 *      @type int|string|null $item_id     The item ID (if set).
 *      @type Pods|null       $pods        The Pods object (if built or provided).
 *      @type Pod|null        $pod         The Pod object (if built or provided).
 *  }
 */
function pods_info_from_args( $args ) {
	$info = array(
		'object_type' => null,
		'object_name' => null,
		'item_id'     => null,
		'pods'        => null,
		'pod'         => null,
	);

	$build_pods = false;
	$build_pod  = false;

	if ( isset( $args['build_pods'] ) ) {
		$build_pods = $args['build_pods'];

		unset( $args['build_pods'] );
	}

	if ( isset( $args['build_pod'] ) ) {
		$build_pod = $args['build_pod'];

		unset( $args['build_pod'] );
	}

	// Merge in the args with the defaults.
	$info = array_merge( $info, $args );

	$object_type_set = null !== $info['object_type'];
	$object_name_set = null !== $info['object_name'];

	// Maybe auto-set the object name from the type if we can.
	if (
		$object_type_set
		&& ! $object_name_set
		&& in_array( $info['object_type'], array( 'comment', 'media', 'user' ), true )
	) {
		$info['object_name'] = $info['object_type'];

		$object_name_set = true;
	}

	// Normalize the Pods info to null if it's not valid.
	if (
		$info['pods'] instanceof Pods
		&& ! $info['pods']->valid()
	) {
		$info['pods'] = null;
	}

	// Maybe build the Pods object from the info.
	if (
		$build_pods
		&& $object_name_set
		&& ! $info['pods'] instanceof Pods
	) {
		$pods = pods( $info['object_name'], $info['item_id'], true );

		if (
			$pods instanceof Pods
			&& $pods->valid()
			&& (
				empty( $info['object_type'] )
				|| $info['object_type'] === $pods->pod_data['type']
			)
		) {
			$info['pods'] = $pods;

			if ( ! is_array( $info['pod'] ) ) {
				$info['pod'] = $pods->pod_data;
			}
		}
	} elseif (
		$info['pods'] instanceof Pods
		&& $info['pods']->valid()
		&& ! is_array( $info['pod'] )
	) {
		$info['pod'] = $info['pods']->pod_data;
	}

	// Maybe build the Pod object from the info.
	if (
		$build_pod
		&& $object_name_set
		&& ! is_array( $info['pod'] )
	) {
		try {
			$pod = pods_api()->load_pod( array(
				'name' => $info['object_name'],
			) );
		} catch ( Exception $e ) {
			$pod = null;
		}

		if (
			is_array( $pod )
			&& (
				empty( $info['object_type'] )
				|| $info['object_type'] === $pod['type']
			)
		) {
			$info['pod'] = $pod;
		}
	}

	if ( is_array( $info['pod'] ) ) {
		$info['object_type'] = $info['pod']['type'];
		$info['object_name'] = $info['pod']['name'];
	}

	return $info;
}

/**
 * Determine whether the current user has access to an object.
 *
 * @since 3.1.0
 *
 * @param array       $args {
 *      The arguments to use.
 *
 *      @type string|null     $object_type The object type.
 *      @type string|null     $object_name The object name.
 *      @type int|string|null $item_id     The item ID.
 *      @type Pods|null       $pods        The Pods object.
 *      @type Pod|null        $pod         The Pod object.
 *      @type bool            $build_pods  Whether to try to build a Pods object from the object type/name/ID (false by default).
 *      @type bool            $build_pod   Whether to try to build a Pod object from the object type/name (false by default).
 * }
 * @param int|null    $user_id     The user ID to check against, set to 0 or null for anonymous access check.
 * @param string      $access_type The type of access to check for (read, add, edit, delete).
 * @param string|null $context     The unique slug that can be referenced by hooks for context.
 *
 * @return bool Whether the current user has access to an object.
 */
function pods_user_can_access_object( $args, $user_id, $access_type = 'edit', $context = null ) {
	$info = pods_info_from_args( $args );

	if ( null === $user_id ) {
		$user_id = 0;
	}

	// Check if the user exists.
	$user = get_userdata( $user_id );

	if ( ! $user instanceof WP_User ) {
		// If the user does not exist and it was not anonymous, do not allow access to an invalid user.
		if ( 0 < $user_id ) {
			return false;
		}

		// If the user was 0 to begin with (anonymous) then set up a user object to work with.
		$user = new WP_User();
	}

	// Determine if this is a user in WP that has full access.
	if ( $user_id && pods_is_admin() ) {
		return true;
	}

	if ( 'pod' === $info['object_type'] || 'table' === $info['object_type'] ) {
		// If no object name is provided, we cannot check access.
		if ( empty( $info['object_name'] ) ) {
			return false;
		}

		// Determine if this user has full content access.
		if ( $user->has_cap( 'pods_content' ) ) {
			return true;
		}
	}

	$capabilities = pods_access_map_capabilities( $info, $user_id );

	// Unsupported capabilities returned.
	if ( null === $capabilities ) {
		return false;
	}

	/**
	 * Allow filtering the list of capabilities used for checking access against an object.
	 *
	 * @since 3.1.0
	 *
	 * @param array           $capabilities The list of capabilities used for checking access against an object.
	 * @param int             $user_id      The user ID to check against.
	 * @param array           $info {
	 *      The normalized Pod information referenced.
	 *
	 *      @type string|null     $object_type The object type (if set).
	 *      @type string|null     $object_name The object name (if set).
	 *      @type int|string|null $item_id     The item ID (if set).
	 *      @type Pods|null       $pods        The Pods object (if built or provided).
	 *      @type Pod|null        $pod         The Pod object (if built or provided).
	 * }
	 * @param string          $access_type  The type of access to check for (read, add, edit, delete).
	 * @param string|null     $context      The unique slug that can be referenced by hooks for context.
	 */
	$capabilities = (array) apply_filters(
		'pods_user_can_access_object_get_capabilities',
		$capabilities,
		$user_id,
		$info,
		$access_type,
		$context
	);

	// No capability mapped, do not allow access.
	if ( ! array_key_exists( $access_type, $capabilities ) ) {
		return false;
	}

	/**
	 * Allow filtering whether a user has access to an object before the normal capability check runs.
	 *
	 * @since 3.1.0
	 *
	 * @param null|bool       $can_access   Whether a user has access to an object (return null to run normal check).
	 * @param int             $user_id      The user ID to check against.
	 * @param array           $info {
	 *      The normalized Pod information referenced.
	 *
	 *      @type string|null     $object_type The object type (if set).
	 *      @type string|null     $object_name The object name (if set).
	 *      @type int|string|null $item_id     The item ID (if set).
	 *      @type Pods|null       $pods        The Pods object (if built or provided).
	 *      @type Pod|null        $pod         The Pod object (if built or provided).
	 * }
	 * @param string          $access_type  The type of access to check for (read, add, edit, delete).
	 * @param string|null     $context      The unique slug that can be referenced by hooks for context.
	 * @param array           $capabilities The list of capabilities used for checking access against an object.
	 */
	$can_access = apply_filters(
		'pods_user_can_access_object_pre_check',
		null,
		$user_id,
		$info,
		$access_type,
		$context,
		$capabilities
	);

	// Check for access override and return that instead.
	if ( null !== $can_access ) {
		return $can_access;
	}

	// If we are allowing all access, null will be set for the capability.
	if ( null === $capabilities[ $access_type ] ) {
		$can_access = true;
	} else {
		// Support multiple capability checks ("OR" logic).
		$capabilities[ $access_type ] = (array) $capabilities[ $access_type ];

		$can_access = false;

		foreach ( $capabilities[ $access_type ] as $capability ) {
			if ( $info['item_id'] ) {
				$can_access = $user->has_cap( $capability, $info['item_id'] );
			} else {
				$can_access = $user->has_cap( $capability );
			}

			if ( $can_access ) {
				break;
			}
		}
	}

	$is_read_access = 'read' === $access_type;

	// Check for password-protected post.
	if (
		$can_access
		&& 'post_type' === $info['object_type']
		&& $info['item_id']
		&& (
			(
				$is_read_access
				&& pods_access_bypass_post_with_password( $info )
			)
			|| (
				! $is_read_access
				&& post_password_required( $info['item_id'] )
			)
		)
	) {
		$can_access = false;
	}

	/**
	 * Allow filtering whether a user has access to an object after the normal capability check runs.
	 *
	 * @since 3.1.0
	 *
	 * @param bool            $can_access   Whether a user has access to an object.
	 * @param int             $user_id      The user ID to check against.
	 * @param array           $info {
	 *      The normalized Pod information referenced.
	 *
	 *      @type string|null     $object_type The object type (if set).
	 *      @type string|null     $object_name The object name (if set).
	 *      @type int|string|null $item_id     The item ID (if set).
	 *      @type Pods|null       $pods        The Pods object (if built or provided).
	 *      @type Pod|null        $pod         The Pod object (if built or provided).
	 * }
	 * @param string          $access_type  The type of access to check for (read, add, edit, delete).
	 * @param string|null     $context      The unique slug that can be referenced by hooks for context.
	 * @param array           $capabilities The list of capabilities used for checking access against an object.
	 */
	return (bool) apply_filters(
		'pods_user_can_access_object',
		$can_access,
		$user_id,
		$info,
		$access_type,
		$context,
		$capabilities
	);
}

/**
 * Determine whether the current user has access to an object.
 *
 * @since 3.1.0
 *
 * @param array       $args {
 *      The arguments to use.
 *
 *      @type string|null     $object_type The object type.
 *      @type string|null     $object_name The object name.
 *      @type int|string|null $item_id     The item ID.
 *      @type Pods|null       $pods        The Pods object.
 *      @type Pod|null        $pod         The Pod object.
 *      @type bool            $build_pods  Whether to try to build a Pods object from the object type/name/ID (false by default).
 *      @type bool            $build_pod   Whether to try to build a Pod object from the object type/name (false by default).
 * }
 * @param string      $access_type The type of access to check for (read, add, edit, delete).
 * @param string|null $context     The unique slug that can be referenced by hooks for context.
 *
 * @return bool Whether the current user has access to an object.
 */
function pods_current_user_can_access_object( $args, $access_type = 'edit', $context = null ) {
	$user_id = null;

	if ( is_user_logged_in() ) {
		$user_id = get_current_user_id();
	}

	return pods_user_can_access_object( $args, $user_id, $access_type, $context );
}

/**
 * Build and map the capabilities that a specific object type/name/ID have in relation to a user ID.
 *
 * @since 3.1.0
 *
 * @param array    $args {
 *      The arguments to use.
 *
 *      @type string|null     $object_type The object type.
 *      @type string|null     $object_name The object name.
 *      @type int|string|null $item_id     The item ID.
 *      @type Pods|null       $pods        The Pods object.
 *      @type Pod|null        $pod         The Pod object.
 *      @type bool            $build_pods  Whether to try to build a Pods object from the object type/name/ID (false by default).
 *      @type bool            $build_pod   Whether to try to build a Pod object from the object type/name (false by default).
 * }
 * @param int|null $user_id The user ID accessing the object.
 * @param bool     $strict  Whether to strictly get the capabilities or have the 'read' capability evaluate to null if it's public (defaults to false).
 *
 * @return array|null The capabilities that a specific object type/name/ID have in relation to a user ID, or null if invalid.
 */
function pods_access_map_capabilities( $args, $user_id = null, $strict = false ) {
	$args['build_pods'] = true;
	$args['build_pod']  = true;

	$info = pods_info_from_args( $args );

	// If no object type or name, we cannot check access.
	if ( empty( $info['object_type'] ) || empty( $info['object_name'] ) ) {
		return null;
	}

	$wp_object = null;

	$capabilities = array();

	if ( 'post_type' === $info['object_type'] ) {
		$info['item_id'] = (int) $info['item_id'];

		if ( $info['item_id'] ) {
			$capabilities['read']   = 'read_post';
			$capabilities['edit']   = 'edit_post';
			$capabilities['delete'] = 'delete_post';
		} else {
			$capabilities['read']   = 'read';
			$capabilities['edit']   = 'edit_posts';
			$capabilities['delete'] = 'delete_posts';
		}

		$capabilities['add']              = 'create_posts';
		$capabilities['read_private']     = 'read_private_posts';
		$capabilities['edit_others']      = 'edit_others_posts';
		$capabilities['delete_others']    = 'delete_others_posts';
		$capabilities['delete_published'] = 'delete_published_posts';
		$capabilities['delete_private']   = 'delete_private_posts';

		// Maybe map capabilities to the post type.
		$wp_object = get_post_type_object( $info['object_name'] );

		if ( $info['item_id'] ) {
			$post = get_post( $info['item_id'] );

			// If the post was found, do fine-grained access checks.
			if ( $post instanceof WP_Post ) {
				$status_obj = get_post_status_object( $post->post_status );

				// Check if the person is allowed to read other posts.
				if (
					$user_id
					&& $post->post_author
					&& (int) $user_id === (int) $post->post_author
				) {
					// This is their own post, they can have access.
					$capabilities['read'] = 'read';
				} elseif (
					! $status_obj
					|| $status_obj->private
				) {
					// This is a private post, check private post capability.
					$capabilities['read'] = $capabilities['read_private'];
				}
			}
		}
	} elseif ( 'taxonomy' === $info['object_type'] ) {
		$info['item_id'] = (int) $info['item_id'];

		$capabilities['read']   = 'read';
		$capabilities['add']    = 'manage_terms';
		$capabilities['edit']   = 'edit_terms';
		$capabilities['delete'] = 'delete_terms';

		// Maybe map capabilities to the post type.
		$wp_object = get_taxonomy( $info['object_name'] );
	} elseif ( 'user' === $info['object_type'] ) {
		$info['item_id'] = (int) $info['item_id'];

		$capabilities['read']   = 'list_users';
		$capabilities['add']    = 'create_users';
		$capabilities['edit']   = 'edit_users';
		$capabilities['delete'] = 'delete_users';

		// If an object ID is provided, check for access for that specific user.
		if ( ! empty( $info['item_id'] ) ) {
			$capabilities['edit']   = 'edit_user';
			$capabilities['delete'] = 'delete_user';
		}

		// Fake the WP object for the logic below.
		$wp_object = (object) array(
			'public' => false,
			'cap'    => (object) array(),
		);
	} elseif ( 'media' === $info['object_type'] ) {
		$info['item_id'] = (int) $info['item_id'];

		$capabilities['read']   = 'read';
		$capabilities['add']    = 'upload_files';
		$capabilities['edit']   = 'upload_files';
		$capabilities['delete'] = 'upload_files';

		// Fake the WP object for the logic below.
		$wp_object = (object) array(
			'public' => false,
			'cap'    => (object) array(),
		);
	} elseif ( 'comment' === $info['object_type'] ) {
		$info['item_id'] = (int) $info['item_id'];

		$capabilities['read']   = 'read';
		$capabilities['add']    = 1 === (int) get_option( 'comment_registration' ) ? 'read' : null;
		$capabilities['edit']   = 'moderate_comments';
		$capabilities['delete'] = 'moderate_comments';

		// If an object ID is provided, check for access for that specific user.
		if ( ! empty( $info['item_id'] ) ) {
			$capabilities['edit'] = 'edit_comment';
		}

		// Fake the WP object for the logic below.
		$wp_object = (object) array(
			'public' => true,
			'cap'    => (object) array(),
		);
	} elseif ( 'settings' === $info['object_type'] ) {
		$capabilities['read']   = 'manage_options';
		$capabilities['edit']   = 'manage_options';
		$capabilities['delete'] = 'manage_options';

		// Fake the WP object for the logic below.
		$wp_object = (object) array(
			'public' => false,
			'cap'    => (object) array(),
		);
	} elseif ( 'pod' === $info['object_type'] || 'table' === $info['object_type'] ) {
		$info['item_id'] = (int) $info['item_id'];

		$capabilities['read']          = 'pods_read_' . $info['object_name'];
		$capabilities['add']           = 'pods_add_' . $info['object_name'];
		$capabilities['edit']          = 'pods_edit_' . $info['object_name'];
		$capabilities['delete']        = 'pods_delete_' . $info['object_name'];
		$capabilities['edit_others']   = 'pods_edit_others_' . $info['object_name'];
		$capabilities['delete_others'] = 'pods_delete_others_' . $info['object_name'];

		$is_public = false;

		if ( $info['pods'] instanceof Pods && is_array( $info['pod'] ) ) {
			// If an object ID is provided, check for access for that specific item.
			if ( $info['item_id'] && $info['pods']->exists() ) {
				// Check for author field.
				$author_field = pods_v( 'author', $info['pod']['fields'] );

				$author_user_id = $author_field ? (int) $info['pods']->field( $author_field['name'] . '.ID' ) : null;

				// If we have an author field, check if they are the author.
				if ( $author_field ) {
					if ( $user_id && $author_user_id === $user_id ) {
						// This is their own post, they can also have access if have edit access.
						$capabilities['read'] = array(
							$capabilities['read'],
							'pods_edit_' . $info['object_name'],
						);
					} else {
						// This is not their post, check if they have access to others.
						$capabilities['edit']   = 'pods_edit_others_' . $info['object_name'];
						$capabilities['delete'] = 'pods_delete_others_' . $info['object_name'];
					}
				}
			}

			$is_public = pods_v( 'public', $info['pod'] );
			$is_public = filter_var( $is_public, FILTER_VALIDATE_BOOLEAN );

			// Fake the WP object for the logic below.
			$wp_object = (object) array(
				'public' => $is_public,
				'cap'    => (object) array(),
			);
		}

		if ( $is_public ) {
			$capabilities['read'] = 'read';
		}
	}

	// If no post type object is found, we cannot check access.
	if ( ! $wp_object ) {
		return null;
	}

	// Check if there are any capabilities mapped for this type object.
	foreach ( $capabilities as $access_type => $capability ) {
		if ( $capability ) {
			if ( is_array( $capability ) ) {
				foreach ( $capability as $k => $cap ) {
					if ( isset( $wp_object->cap->{$cap} ) ) {
						$capabilities[ $access_type ][ $k ] = $wp_object->cap->{$cap};
					}
				}
			} elseif ( isset( $wp_object->cap->{$capability} ) ) {
				$capabilities[ $access_type ] = $wp_object->cap->{$capability};
			}
		}
	}

	// If the object is public, allow read for anyone even logged out.
	if ( ! $strict && $wp_object->public && 'read' === $capabilities['read'] && ! $user_id ) {
		$capabilities['read'] = null;
	}

	/**
	 * Allow filtering the list of capabilities used for checking access against an object type or singular object.
	 *
	 * @since 3.1.0
	 *
	 * @param array           $capabilities The list of capabilities used for checking access against an object type or singular object.
	 * @param int             $user_id      The user ID to check against.
	 * @param array           $info {
	 *      The normalized Pod information referenced.
	 *
	 *      @type string|null     $object_type The object type (if set).
	 *      @type string|null     $object_name The object name (if set).
	 *      @type int|string|null $item_id     The item ID (if set).
	 *      @type Pods|null       $pods        The Pods object (if built or provided).
	 *      @type Pod|null        $pod         The Pod object (if built or provided).
	 * }
	 */
	return (array) apply_filters(
		'pods_access_map_capabilities',
		$capabilities,
		$user_id,
		$info
	);
}

/**
 * Determine whether the object type/name is public.
 *
 * @since 3.1.0
 *
 * @param array       $args {
 *      The arguments to use.
 *
 *      @type string|null     $object_type The object type.
 *      @type string|null     $object_name The object name.
 *      @type int|string|null $item_id     The item ID.
 *      @type Pods|null       $pods        The Pods object.
 *      @type Pod|null        $pod         The Pod object.
 *      @type bool            $build_pods  Whether to try to build a Pods object from the object type/name/ID (false by default).
 *      @type bool            $build_pod   Whether to try to build a Pod object from the object type/name (false by default).
 * }
 * @param string $context The context we are checking from (defaults to shortcode).
 *
 * @return bool Whether the object type/name is public.
 */
function pods_is_type_public( $args, $context = 'shortcode' ) {
	$args['build_pod'] = true;

	$info = pods_info_from_args( $args );

	$is_public = true;

	$pod_has_public = null;

	$is_post_type    = 'post_type' === $info['object_type'];
	$is_taxonomy     = 'taxonomy' === $info['object_type'];
	$is_pod          = 'pod' === $info['object_type'];
	$is_settings_pod = 'settings' === $info['object_type'];

	$is_shortcode_context = 'shortcode' === $context;

	if (
		is_array( $info['pod'] )
		&& (
			$is_post_type
			|| $is_taxonomy
			|| $is_pod
			|| $is_settings_pod
		)
	) {
		$is_extended = ! empty( $info['pod']['object'] );

		if ( ! $is_extended ) {
			$is_public = pods_v( 'public', $info['pod'] );

			if ( null !== $is_public ) {
				$pod_has_public = true;

				$is_public = filter_var( $is_public, FILTER_VALIDATE_BOOLEAN );

				if ( $is_post_type || $is_taxonomy ) {
					$is_public = $is_public && 1 === (int) pods_v( 'publicly_queryable', $info['pod'], $is_public );
				}
			}
		}
	}

	// Maybe handle looking up the visibility based on the object type.
	if ( null === $pod_has_public ) {
		if ( $is_post_type ) {
			// If no object name is provided, we cannot check if it is public.
			if ( empty( $info['object_name'] ) ) {
				$is_public = false;
			} else {
				$post_type_object = get_post_type_object( $info['object_name'] );

				// Post type not found.
				if ( ! $post_type_object ) {
					$is_public = false;
				} else {
					$is_public = $post_type_object->public && $post_type_object->publicly_queryable;
				}
			}
		} elseif ( $is_taxonomy ) {
			// If no object name is provided, we cannot check if it is public.
			if ( empty( $info['object_name'] ) ) {
				$is_public = false;
			} else {
				$taxonomy_object = get_taxonomy( $info['object_name'] );

				// Post type not found.
				if ( ! $taxonomy_object ) {
					$is_public = false;
				} else {
					$is_public = $taxonomy_object->public && $taxonomy_object->publicly_queryable;
				}
			}
		} elseif ( 'user' === $info['object_type'] ) {
			// Users are not public for shortcodes.
			if ( $is_shortcode_context ) {
				$is_public = false;
			}
		} elseif ( $is_pod || $is_settings_pod ) {
			// Pods need special default handling for shortcodes.
			if ( $is_shortcode_context ) {
				$first_pods_version = get_option( 'pods_framework_version_first' );
				$first_pods_version = '' === $first_pods_version ? PODS_VERSION : $first_pods_version;

				$is_public = version_compare( $first_pods_version, '3.1.0-a-1', '<' ) ? true : false;
			}
		}
	}

	/**
	 * Allow filtering whether the object type/name is public.
	 *
	 * @since 3.1.0
	 *
	 * @param bool        $is_public   Whether the object type/name is public.
	 * @param array       $info {
	 *      The normalized Pod information referenced.
	 *
	 *      @type string|null     $object_type The object type (if set).
	 *      @type string|null     $object_name The object name (if set).
	 *      @type int|string|null $item_id     The item ID (if set).
	 *      @type Pods|null       $pods        The Pods object (if built or provided).
	 *      @type Pod|null        $pod         The Pod object (if built or provided).
	 * }
	 * @param string|null $context     The context we are checking from (shortcode or null).
	 */
	return (bool) apply_filters(
		'pods_is_type_public',
		$is_public,
		$info,
		$context
	);
}

/**
 * Determine whether a post should be bypassed because it it has a password.
 *
 * @since 3.1.0
 *
 * @param array $args {
 *      The arguments to use.
 *
 *      @type string|null     $object_type The object type.
 *      @type string|null     $object_name The object name.
 *      @type int|string|null $item_id     The item ID.
 *      @type Pods|null       $pods        The Pods object.
 *      @type Pod|null        $pod         The Pod object.
 *      @type bool            $build_pods  Whether to try to build a Pods object from the object type/name/ID (false by default).
 *      @type bool            $build_pod   Whether to try to build a Pod object from the object type/name (false by default).
 * }
 *
 * @return bool Whether a post should be bypassed because it it has a password.
 */
function pods_access_bypass_post_with_password( $args ) {
	$info = pods_info_from_args( $args );

	if ( 'post_type' !== $info['object_type'] || ! $info['item_id'] ) {
		return false;
	}

	$post = get_post( (int) $info['item_id'] );

	if ( ! $post instanceof WP_Post ) {
		return false;
	}

	// Bypass posts that have a password required but not provided.
	$bypass_post_with_password = post_password_required( $post );

	/**
	 * Allow filtering whether a post should be bypassed because it it has a password.
	 *
	 * @since 3.1.0
	 *
	 * @param bool  $bypass_post_with_password Whether a post should be bypassed because it it has a password.
	 * @param array $info {
	 *      The normalized Pod information referenced.
	 *
	 *      @type string|null     $object_type The object type (if set).
	 *      @type string|null     $object_name The object name (if set).
	 *      @type int|string|null $item_id     The item ID (if set).
	 *      @type Pods|null       $pods        The Pods object (if built or provided).
	 *      @type Pod|null        $pod         The Pod object (if built or provided).
	 * }
	 */
	return (bool) apply_filters(
		'pods_access_bypass_post_with_password',
		$bypass_post_with_password,
		$info
	);
}

/**
 * Determine whether a post should be bypassed because it is private and capabilities are not met.
 *
 * @since 3.1.0
 *
 * @param array $args {
 *      The arguments to use.
 *
 *      @type string|null     $object_type The object type.
 *      @type string|null     $object_name The object name.
 *      @type int|string|null $item_id     The item ID.
 *      @type Pods|null       $pods        The Pods object.
 *      @type Pod|null        $pod         The Pod object.
 *      @type bool            $build_pods  Whether to try to build a Pods object from the object type/name/ID (false by default).
 *      @type bool            $build_pod   Whether to try to build a Pod object from the object type/name (false by default).
 * }
 *
 * @return bool Whether a post should be bypassed because it is private and capabilities are not met.
 */
function pods_access_bypass_private_post( $args ) {
	$info = pods_info_from_args( $args );

	if ( 'post_type' !== $info['object_type'] || ! $info['item_id'] ) {
		return false;
	}

	$post = get_post( $info['item_id'] );

	if ( ! $post instanceof WP_Post ) {
		return false;
	}

	$status_obj = get_post_status_object( $post->post_status );

	$bypass_private_post = false;

	if (
		! is_object( $status_obj ) ||
		! empty( $status_obj->internal ) ||
		! empty( $status_obj->protected )
	) {
		$is_public = false;
	} else {
		$is_public = ! empty( $status_obj->publicly_queryable ) || ( ! empty( $status_obj->_builtin ) && ! empty( $status_obj->public ) );
	}

	if ( ! $is_public ) {
		$bypass_private_post = ! pods_current_user_can_access_object( $info, 'read' );
	}

	/**
	 * Allow filtering whether a post should be bypassed because it is private.
	 *
	 * @since 3.1.0
	 *
	 * @param bool  $bypass_private_post Whether a post should be bypassed because it is private.
	 * @param array $info {
	 *      The normalized Pod information referenced.
	 *
	 *      @type string|null     $object_type The object type (if set).
	 *      @type string|null     $object_name The object name (if set).
	 *      @type int|string|null $item_id     The item ID (if set).
	 *      @type Pods|null       $pods        The Pods object (if built or provided).
	 *      @type Pod|null        $pod         The Pod object (if built or provided).
	 * }
	 */
	return (bool) apply_filters(
		'pods_access_bypass_private_post',
		$bypass_private_post,
		$info
	);
}

/**
 * Determine whether dynamic features can be used.
 *
 * @since 3.1.0
 *
 * @return bool Whether dynamic features can be used.
 */
function pods_can_use_dynamic_features( $pod = null ) {
	// Check if the constant is defined and only override if no $pod is set or dynamic features are totally disabled.
	if (
		defined( 'PODS_DYNAMIC_FEATURES_ALLOW' )
		&& (
			! $pod
			|| ! PODS_DYNAMIC_FEATURES_ALLOW
		)
	) {
		return PODS_DYNAMIC_FEATURES_ALLOW;
	}

	$can_use_dynamic_features = apply_filters( 'pods_access_can_use_dynamic_features', null, $pod );

	if ( is_bool( $can_use_dynamic_features ) ) {
		return $can_use_dynamic_features;
	}

	$dynamic_features_allow = true;

	if ( is_array( $pod ) ) {
		$dynamic_features_allow = pods_is_type_public(
			[
				'pod' => $pod,
			]
		);
	}

	return $dynamic_features_allow;
}

/**
 * Determine whether any or a specific dynamic feature can be used.
 *
 * @since 3.1.0
 *
 * @param string $type The dynamic feature type.
 *
 * @return bool Whether any or a specific dynamic feature can be used.
 */
function pods_can_use_dynamic_feature( $type ) {
	if ( ! pods_can_use_dynamic_features() ) {
		return false;
	}

	if ( empty( $type ) ) {
		return false;
	}

	// Handle the constants.
	if ( 'view' === $type && defined( 'PODS_SHORTCODE_ALLOW_VIEWS' ) && ! PODS_SHORTCODE_ALLOW_VIEWS ) {
		return false;
	}

	$can_use_dynamic_feature = apply_filters( 'pods_access_can_use_dynamic_feature', null, $type );

	if ( is_bool( $can_use_dynamic_feature ) ) {
		return $can_use_dynamic_feature;
	}

	$dynamic_features_enabled = array(
		'display',
		'form',
	);

	$constant_dynamic_features_enabled = defined( 'PODS_DYNAMIC_FEATURES_ENABLED' ) ? PODS_DYNAMIC_FEATURES_ENABLED : false;

	if ( false !== $constant_dynamic_features_enabled && ! is_array( $constant_dynamic_features_enabled ) ) {
		$constant_dynamic_features_enabled = explode( ',', $constant_dynamic_features_enabled );
		$constant_dynamic_features_enabled = array_filter( $constant_dynamic_features_enabled );

		$dynamic_features_enabled = $constant_dynamic_features_enabled;
	}

	if ( empty( $dynamic_features_enabled ) ) {
		return false;
	}

	return in_array( $type, $dynamic_features_enabled, true );
}

/**
 * Determine whether specific dynamic feature is unrestricted.
 *
 * @since 3.1.0
 *
 * @param array  $args {
 *      The arguments to use.
 *
 *      @type string|null     $object_type The object type.
 *      @type string|null     $object_name The object name.
 *      @type int|string|null $item_id     The item ID.
 *      @type Pods|null       $pods        The Pods object.
 *      @type Pod|null        $pod         The Pod object.
 *      @type bool            $build_pods  Whether to try to build a Pods object from the object type/name/ID (false by default).
 *      @type bool            $build_pod   Whether to try to build a Pod object from the object type/name (false by default).
 * }
 * @param string $type The dynamic feature type.
 * @param string $mode The dynamic feature mode (like "add" or "edit" for the form feature).
 *
 * @return bool Whether specific dynamic feature is unrestricted.
 */
function pods_can_use_dynamic_feature_unrestricted( $args, $type, $mode = null ) {
	if ( ! pods_can_use_dynamic_feature( $type ) ) {
		return false;
	}

	if ( defined( 'PODS_DYNAMIC_FEATURES_RESTRICT' ) && ! PODS_DYNAMIC_FEATURES_RESTRICT ) {
		return true;
	}

	$can_use_dynamic_features_unrestricted = apply_filters( 'pods_access_can_use_dynamic_features_unrestricted', null, $args, $type, $mode );

	if ( is_bool( $can_use_dynamic_features_unrestricted ) ) {
		return $can_use_dynamic_features_unrestricted;
	}

	$can_use_unrestricted = false;

	$args['build_pod'] = true;

	$info = pods_info_from_args( $args );

	if ( ! $info['pod'] ) {
		$can_use_unrestricted = false;
	} else {
		$is_public_content_type = pods_is_type_public( $info );

		$default_restricted_dynamic_features = array(
			'form',
		);

		if ( ! $is_public_content_type ) {
			$default_restricted_dynamic_features[] = 'display';
		}

		$default_restricted_dynamic_features_forms = array(
			'edit',
		);

		if ( ! $is_public_content_type ) {
			$default_restricted_dynamic_features_forms[] = 'add';
		}

		if ( ! empty( $type ) ) {
			$restricted_dynamic_features = $default_restricted_dynamic_features;

			if ( defined( 'PODS_DYNAMIC_FEATURES_RESTRICTED' ) && false !== PODS_DYNAMIC_FEATURES_RESTRICTED ) {
				$constant_restricted_dynamic_features = PODS_DYNAMIC_FEATURES_RESTRICTED;

				if ( ! is_array( $constant_restricted_dynamic_features ) ) {
					$constant_restricted_dynamic_features = explode( ',', $constant_restricted_dynamic_features );
				}

				$restricted_dynamic_features = $constant_restricted_dynamic_features;
			}

			$restricted_dynamic_features = array_filter( $restricted_dynamic_features );

			if ( empty( $restricted_dynamic_features ) ) {
				$can_use_unrestricted = true;
			} else {
				$can_use_unrestricted = ! in_array( $type, $restricted_dynamic_features, true );
			}

			if ( ! $can_use_unrestricted && 'form' === $type && $mode ) {
				$restricted_dynamic_features_forms = $default_restricted_dynamic_features_forms;

				if ( defined( 'PODS_DYNAMIC_FEATURES_RESTRICTED_FORMS' ) && false !== PODS_DYNAMIC_FEATURES_RESTRICTED_FORMS ) {
					$constant_restricted_dynamic_features_forms = PODS_DYNAMIC_FEATURES_RESTRICTED_FORMS;

					if ( ! is_array( $constant_restricted_dynamic_features_forms ) ) {
						$constant_restricted_dynamic_features_forms = explode( ',', $constant_restricted_dynamic_features_forms );
					}

					$restricted_dynamic_features_forms = $constant_restricted_dynamic_features_forms;
				}

				$restricted_dynamic_features_forms = array_filter( $restricted_dynamic_features_forms );

				if ( empty( $restricted_dynamic_features_forms ) ) {
					$can_use_unrestricted = true;
				} else {
					$can_use_unrestricted = ! in_array( $mode, $restricted_dynamic_features_forms, true );
				}
			}
		}
	}

	return $can_use_unrestricted;
}

/**
 * Get the access notice for admin user based on object type and object name.
 *
 * @since 3.1.0
 *
 * @param array $args {
 *      The arguments to use.
 *
 *      @type string|null     $object_type The object type.
 *      @type string|null     $object_name The object name.
 *      @type int|string|null $item_id     The item ID.
 *      @type Pods|null       $pods        The Pods object.
 *      @type Pod|null        $pod         The Pod object.
 *      @type bool            $build_pods  Whether to try to build a Pods object from the object type/name/ID (false by default).
 *      @type bool            $build_pod   Whether to try to build a Pod object from the object type/name (false by default).
 * }
 * @param bool  $force_message Whether to force the message to show even if messages are hidden by a setting.
 *
 * @return string The access notice for admin user based on object type and object name.
 */
function pods_get_access_admin_notice( $args, $force_message = false, $message = null ) {
	$args['build_pod'] = true;

	$info = pods_info_from_args( $args );

	$identifier_for_html = esc_html( json_encode( array(
		'object_type' => $info['object_type'],
		'object_name' => $info['object_name'],
		'item_id'     => $info['item_id'],
	) ) );

	// Check if constant is hiding all notices.
	if ( ! $force_message && defined( 'PODS_ACCESS_HIDE_NOTICES' ) && PODS_ACCESS_HIDE_NOTICES ) {
		return '<!-- pods:access-notices/admin/hidden-by-constant ' . $identifier_for_html . ' -->';
	}

	return '<!-- pods:access-notices/admin/content-hidden ' . $identifier_for_html . ' -->';
}

/**
 * Get the access notice for non-admin user based on object type and object name.
 *
 * @since 3.1.0
 *
 * @param array       $args {
 *      The arguments to use.
 *
 *      @type string|null     $object_type The object type.
 *      @type string|null     $object_name The object name.
 *      @type int|string|null $item_id     The item ID.
 *      @type Pods|null       $pods        The Pods object.
 *      @type Pod|null        $pod         The Pod object.
 *      @type bool            $build_pods  Whether to try to build a Pods object from the object type/name/ID (false by default).
 *      @type bool            $build_pod   Whether to try to build a Pod object from the object type/name (false by default).
 * }
 * @param bool        $force_message Whether to force the message to show even if messages are hidden by a setting.
 * @param string|null $message       A custom message to use for the notice text.
 *
 * @return string The access notice for non-admin user based on object type and object name.
 */
function pods_get_access_user_notice( $args, $force_message = false, $message = null ) {
	$args['build_pod'] = true;

	$info = pods_info_from_args( $args );

	$identifier_for_html = esc_html( json_encode( array(
		'object_type' => $info['object_type'],
		'object_name' => $info['object_name'],
		'item_id'     => $info['item_id'],
	) ) );

	// Check for password-protected post.
	if ( $info['item_id'] && pods_access_bypass_post_with_password( $info ) ) {
		$message = get_the_password_form( $info['item_id'] );

		return '<!-- pods:access-notices/user/protected/message ' . $identifier_for_html . ' -->' . $message;
	}

	// Check if constant is hiding all notices.
	if ( ! $force_message && defined( 'PODS_ACCESS_HIDE_NOTICES' ) && PODS_ACCESS_HIDE_NOTICES ) {
		return '<!-- pods:access-notices/user/hidden-by-constant ' . $identifier_for_html . ' -->';
	}

	return '<!-- pods:access-notices/user/content-hidden ' . $identifier_for_html . ' -->';
}

/**
 * Determine whether a callback can be used.
 *
 * Only plain function-name string callbacks are permitted by default. Closures,
 * invokable objects, array callables ( [ $object, 'method' ] / [ 'Class', 'method' ] ),
 * and string class method references ( "Class::method" ) are rejected unless
 * class callbacks are enabled via the PODS_ALLOW_CLASS_CALLBACKS constant or the
 * "pods_access_allow_class_callbacks" filter.
 *
 * @since 3.1.0
 *
 * @param string|callable $callback The callback to check.
 * @param array           $params   Parameters used by Pods::helper() method.
 *
 * @return bool Whether the callback can be used.
 */
function pods_access_callback_allowed( $callback, $params = array() ) {
	// Class-based callbacks are disabled by default; only plain function-name string callbacks are permitted. Set the PODS_ALLOW_CLASS_CALLBACKS constant to true (or use the "pods_access_allow_class_callbacks" filter) to permit closures, invokable objects, array callables, and "Class::method" strings.
	$allow_class_callbacks = defined( 'PODS_ALLOW_CLASS_CALLBACKS' ) && PODS_ALLOW_CLASS_CALLBACKS;

	/**
	 * Filter whether class-based callbacks are permitted (closures, invokable
	 * objects, array callables, and "Class::method" strings).
	 *
	 * @since 3.3.9.1
	 *
	 * @param bool            $allow_class_callbacks Whether class-based callbacks are allowed.
	 * @param string|callable $callback              The callback being checked.
	 * @param array           $params                Parameters used by Pods::helper() method.
	 */
	$allow_class_callbacks = (bool) apply_filters( 'pods_access_allow_class_callbacks', $allow_class_callbacks, $callback, $params );

	if ( ! is_string( $callback ) ) {
		return $allow_class_callbacks;
	}

	if ( ! pods_can_use_dynamic_feature( 'display' ) ) {
		return false;
	}

	if (
		defined( 'PODS_DISPLAY_CALLBACKS' )
		&& ! PODS_DISPLAY_CALLBACKS
	) {
		return false;
	}

	/**
	 * Allows changing whether callbacks are allowed to run.
	 *
	 * @param bool  $allow_callbacks Whether callbacks are allowed to run.
	 * @param array $params          Parameters used by Pods::helper() method.
	 *
	 * @since 2.8.0
	 */
	$allow_callbacks = (bool) apply_filters( 'pods_helper_allow_callbacks', true, $params );

	if ( ! $allow_callbacks ) {
		return false;
	}

	/*
	 * Allowed callbacks. A callback must appear here (or in a user/filter
	 * addition) to be usable. Comparison is case- and namespace-insensitive,
	 * so entries are lowercase.
	 */
	$allowed = array(
		// Escaping / output.
		'esc_attr',
		'esc_html',
		'esc_js',
		'esc_url',

		// Post display-by-ID.
		'get_permalink',
		'get_the_date',
		'get_the_excerpt',
		'get_the_modified_date',
		'get_the_modified_time',
		'get_the_post_thumbnail',
		'get_the_post_thumbnail_url',
		'get_the_time',
		'get_the_title',

		// Term display-by-ID.
		'get_cat_name',
		'get_category_link',
		'get_tag_link',
		'get_term_link',

		// User display-by-ID.
		'get_author_posts_url',
		'get_avatar',
		'get_avatar_url',

		// Formatting (PHP).
		'abs',
		'absint',
		'ceil',
		'floatval',
		'floor',
		'htmlentities',
		'htmlspecialchars',
		'intval',
		'ltrim',
		'nl2br',
		'normalize_whitespace',
		'number_format',
		'number_format_i18n',
		'round',
		'rtrim',
		'str_word_count',
		'strrev',
		'strtolower',
		'strtoupper',
		'trim',
		'ucfirst',
		'ucwords',
		'wordwrap',
		'wpautop',

		// Formatting (WP)
		'make_clickable',
		'sanitize_html_class',
		'sanitize_title',
		'sanitize_title_with_dashes',
		'strip_tags',
		'wp_kses_data',
		'wp_kses_post',
		'wp_strip_all_tags',
		'wp_trim_words',
		'wptexturize',

		// Formatting (Pods)
		'pods_serial_comma',
	);

	if ( defined( 'PODS_DISPLAY_CALLBACKS' ) ) {
		$display_callbacks = PODS_DISPLAY_CALLBACKS;
	} else {
		$display_callbacks = 'restricted';
	}

	if ( '0' === $display_callbacks ) {
		return false;
	}

	// Maybe specify additional allowed callbacks on top of the built-in list.
	if ( 'customized' === $display_callbacks ) {
		if ( defined( 'PODS_DISPLAY_CALLBACKS_ALLOWED' ) ) {
			$display_callbacks_allowed = PODS_DISPLAY_CALLBACKS_ALLOWED;
		} else {
			$display_callbacks_allowed = '';
		}

		if ( ! is_array( $display_callbacks_allowed ) ) {
			$display_callbacks_allowed = str_replace( "\n", ',', $display_callbacks_allowed );
			$display_callbacks_allowed = explode( ',', $display_callbacks_allowed );
		}

		$display_callbacks_allowed = array_map( 'trim', $display_callbacks_allowed );
		$display_callbacks_allowed = array_filter( $display_callbacks_allowed );

		/**
		 * Allow filtering the custom prefix used for the display callbacks that can be used with Pods.
		 *
		 * Default: custom_pods_callback_
		 *
		 * @since 3.3.9.2
		 *
		 * @param string $custom_prefix The custom prefix used for the display callbacks that can be used with Pods.
		 */
		$custom_prefix = apply_filters( 'pods_access_callbacks_custom_prefix', 'custom_pods_callback_' );

		$display_callbacks_allowed = array_values(
			array_filter(
				$display_callbacks_allowed,
				function ( $name ) use ( $custom_prefix ) {
					$normalized = ltrim( strtolower( (string) $name ), '\\' );

					return 0 === strpos( $normalized, $custom_prefix );
				}
			)
		);

		if ( ! empty( $display_callbacks_allowed ) ) {
			$allowed = array_merge( $allowed, $display_callbacks_allowed );
		}
	}

	/**
	 * Allows adjusting the allowed callbacks as needed.
	 *
	 * @param array $allowed List of callbacks explicitly allowed.
	 * @param array $params  Parameters used by Pods::helper() method.
	 *
	 * @since 2.7.0
	 */
	$allowed = apply_filters( 'pods_helper_allowed_callbacks', $allowed, $params );

	// Clean up helper callback (if string).
	if ( is_string( $callback ) ) {
		$callback = wp_strip_all_tags( str_replace( array( '`', chr( 96 ) ), "'", $callback ) );
	}

	/*
	 * Normalize for comparison. PHP function/method names are case-insensitive
	 * and may be written with a leading namespace separator, so "SYSTEM",
	 * "System", and "\system" must all be treated as "system". The allowed list
	 * is normalized the same way so matching is consistent.
	 */
	$normalized_callback = ltrim( strtolower( trim( (string) $callback ) ), '\\' );

	/*
	 * Reject class method callbacks expressed as strings unless class callbacks
	 * are explicitly enabled. The scope resolution operator "::" only appears in
	 * static method references such as "Class::method", "\Namespace\Class::method",
	 * or "parent::method".
	 */
	if ( ! $allow_class_callbacks && false !== strpos( $normalized_callback, '::' ) ) {
		pods_access_record_disallowed_display_callback( $callback );

		return false;
	}

	$allowed = array_map( 'strtolower', $allowed );

	/*
	 * Class method strings skip the built-in allow list when class callbacks
	 * are enabled.
	 */
	if ( $allow_class_callbacks && false !== strpos( $normalized_callback, '::' ) ) {
		return true;
	}

	$is_allowed = in_array( $normalized_callback, $allowed, true );

	if ( ! $is_allowed ) {
		pods_access_record_disallowed_display_callback( $callback );
	}

	return $is_allowed;
}

/**
 * Get the unique list of disallowed display callbacks stored in cache.
 *
 * @since 3.3.9.2
 *
 * @return string[] Unique callback names.
 */
function pods_get_disallowed_display_callbacks() {
	$existing = pods_transient_get( 'pods_disallowed_display_callbacks' );

	if ( empty( $existing ) ) {
		return array();
	}

	$existing = array_filter( array_map( 'trim', explode( ',', (string) $existing ) ) );

	return array_values( array_unique( $existing ) );
}

/**
 * Record a disallowed display callback into the cache.
 *
 * Stores a unique comma-separated list for up to 30 days. Recording is skipped
 * when display callback notices are disabled.
 *
 * @since 3.3.9.2
 *
 * @param string $callback The cleaned callback name that was rejected.
 */
function pods_access_record_disallowed_display_callback( $callback ) {
	$callback = trim( $callback );

	if ( '' === $callback ) {
		return;
	}

	$existing = pods_get_disallowed_display_callbacks();

	if ( in_array( $callback, $existing, true ) ) {
		return;
	}

	$existing[] = $callback;

	pods_transient_set(
		'pods_disallowed_display_callbacks',
		implode( ',', array_unique( $existing ) ),
		30 * DAY_IN_SECONDS
	);
}

/**
 * Clear the cached list of disallowed display callbacks.
 *
 * @since 3.3.9.2
 */
function pods_access_clear_disallowed_display_callbacks() {
	pods_transient_clear( 'pods_disallowed_display_callbacks' );
}

/**
 * Get the pod access tab options for a specific pod.
 *
 * @since 3.1.0
 *
 * @param string   $pod_type The pod type.
 * @param string   $pod_name The pod name.
 * @param null|Pod $pod      The pod object.
 *
 * @return array The pod access tab options for a specific pod.
 */
function pods_access_pod_options( $pod_type, $pod_name, $pod = null ) {
	$first_pods_version = get_option( 'pods_framework_version_first' );
	$first_pods_version = '' === $first_pods_version ? PODS_VERSION : $first_pods_version;

	$options = array();

	$options['security_access_rights_info'] = array(
		'label'        => __( 'How access rights work in Pods', 'pods' ),
		'type'         => 'html',
		'html_content' => sprintf(
			'
				<p>%1$s</p>
				<p><a href="https://docs.pods.io/displaying-pods/access-rights-in-pods/" target="_blank" rel="noopener noreferrer">%2$s</a> <span class="dashicon dashicons dashicons-external"></span></p>
			',
			__( 'Pods handles access rights similar to how WordPress itself works.', 'pods' ),
			__( 'Read more about how access rights work in Pods on our Documentation site', 'pods' )
		),
	);

	if ( 'pod' === $pod_type ) {
		$options['public'] = array(
			'label'             => __( 'Public', 'pods' ),
			'help'              => __( 'You can still embed Pods Content and Forms through PHP and make use of other features directly through code.', 'pods' ),
			'description'       => __( 'When a content type is public, it can be viewed by anyone when it is embedded through Dynamic Features. Otherwise, a user will need to have the corresponding "read" capability for the content type.', 'pods' ),
			'type'              => 'boolean',
			'default'           => version_compare( $first_pods_version, '3.1.0-a-1', '<' ) ? true : false,
			'boolean_yes_label' => '',
		);
	}

	if ( pods_can_use_dynamic_features() ) {
		$options['dynamic_features_allow'] = array(
			'label'              => __( 'Dynamic Features', 'pods' ),
			'help'               => array(
				__( 'Enabling Dynamic Features will also enable the additional access rights checks for user access. This ensures that people viewing embedded content and forms have the required capabilities. Even when Dynamic Features are disabled, you can still embed Pods Content and Forms through PHP and make use of other features directly through code.', 'pods' ),
				'https://docs.pods.io/displaying-pods/access-rights-in-pods/',
			),
			'description'        => __( 'Dynamic features include Pods Shortcodes, Blocks, and Widgets which let you embed content and forms on your site.', 'pods' ),
			'type'               => 'pick',
			'default'            => 'inherit',
			'pick_format_type'   => 'single',
			'pick_format_single' => 'radio',
			'data'               => array(
				'inherit' => __( 'WP Default - If the content type is marked "Public" with WordPress then Dynamic Features will be enabled.', 'pods' ),
				'1'       => __( 'Enable Dynamic Features including Pods Shortcodes, Blocks, and Widgets for this content type', 'pods' ),
				'0'       => __( 'Disable All Dynamic Features in Pods for this content type', 'pods' ),
			),
			'dependency'         => true,
		);

		$is_public_content_type = pods_is_type_public(
			array(
				'pod' => $pod,
			)
		);

		$options['restrict_dynamic_features'] = array(
			'label'              => __( 'Restrict Dynamic Features', 'pods' ),
			'help'               => array(
				__( 'This will check access rights for whether someone should have access to specific content before a they can view, modify, or interact with that content.', 'pods' ),
				'https://docs.pods.io/displaying-pods/access-rights-in-pods/',
			),
			'description'        => sprintf(
				'<strong>%1$s</strong> %2$s',
				esc_html__( 'Warning:', 'pods' ),
				esc_html__( 'If you have authors/contributors on your site then disabling this would give them access to embedding content/forms without access checks for them or whoever views the embeds on the front of your site. Caution is always advised before giving access to other users you may not trust.', 'pods' )
			),
			'type'               => 'pick',
			'default'            => '1',
			'pick_format_type'   => 'single',
			'pick_format_single' => 'radio',
			'data'               => array(
				'0' => __( 'Unrestricted - Do not check for access rights for embedded content (only use this if you trust ALL users who have access to create content)', 'pods' ),
				'1' => __( 'Restricted - Check access rights for embedded content', 'pods' ),
			),
			'excludes-on'        => array( 'dynamic_features_allow' => '0' ),
		);

		$default_restricted_dynamic_features = array(
			'form',
		);

		if ( ! $is_public_content_type ) {
			$default_restricted_dynamic_features[] = 'display';
		}

		$options['restricted_dynamic_features'] = array(
			'label'             => __( 'Dynamic Features to Restrict', 'pods' ),
			'help'              => array(
				__( 'This will check access rights for the dynamic feature for whether someone should have access to specific content before a they can view, modify, or interact with that content.', 'pods' ),
				'https://docs.pods.io/displaying-pods/access-rights-in-pods/',
			),
			'type'              => 'pick',
			'default'           => $default_restricted_dynamic_features,
			'pick_format_type'  => 'multi',
			'pick_format_multi' => 'checkbox',
			'data'              => array(
				'display' => __( 'Restricted Display - Shortcodes and Blocks that allow querying content from this Pod and displaying any field will check access rights.', 'pods' ),
				'form'    => __( 'Restricted Forms - The Form Shortcode and Block submitting new content or editing existing content will check access rights.', 'pods' ),
			),
			'depends-on'        => array( 'restrict_dynamic_features' => '1' ),
			'excludes-on'       => array( 'dynamic_features_allow' => '0' ),
		);

		$default_restricted_dynamic_features_forms = array(
			'edit',
		);

		if ( ! $is_public_content_type ) {
			$default_restricted_dynamic_features_forms[] = 'add';
		}

		$options['restricted_dynamic_features_forms'] = array(
			'label'             => __( 'Dynamic Features to Restrict for Forms', 'pods' ),
			'help'              => array(
				__( 'This will check access rights for whether someone should have access to specific content before a they can add or edit content.', 'pods' ),
				'https://docs.pods.io/displaying-pods/access-rights-in-pods/',
			),
			'type'              => 'pick',
			'default'           => $default_restricted_dynamic_features_forms,
			'pick_format_type'  => 'multi',
			'pick_format_multi' => 'checkbox',
			'data'              => array(
				'add'  => __( 'Restricted Add New Forms - Embedding the Form Shortcode and Block to allow for adding new content will check access rights.', 'pods' ),
				'edit' => __( 'Restricted Edit Forms - Embedding the Form Shortcode and Block to allow for editing existing content will check access rights.', 'pods' ),
			),
			'depends-on-multi'  => array( 'restricted_dynamic_features' => 'form' ),
			'excludes-on'       => array( 'dynamic_features_allow' => '0' ),
		);

		$options['show_access_restricted_messages'] = array(
			'label'              => __( 'Access-related Restricted Messages', 'pods' ),
			'help'               => array(
				__( 'Access-related Restricted Messages will show to anyone who does not have access to add/edit/read a specific item from a content type.', 'pods' ),
				'https://docs.pods.io/displaying-pods/access-rights-in-pods/',
			),
			'type'               => 'pick',
			'default'            => 'inherit',
			'pick_format_type'   => 'single',
			'pick_format_single' => 'radio',
			'data'               => array(
				'1'       => __( 'Enable access-related restricted messages for forms/content displayed (instead of the form/content output)', 'pods' ),
				'0'       => __( 'Disable access-related restricted messages for forms/content displayed (the form/content output will be blank)', 'pods' ),
				'inherit' => __( 'Default - Use the global Pods setting for this', 'pods' ),
			),
			'depends-on'         => array( 'restrict_dynamic_features' => '1' ),
			'excludes-on'        => array( 'dynamic_features_allow' => '0' ),
		);

		$options['show_access_admin_notices'] = array(
			'label'              => __( 'Access-related Admin Notices', 'pods' ),
			'help'               => array(
				__( 'Access-related Admin Notices will only show to admins and will appear above content/forms that may not be entirely public.', 'pods' ),
				'https://docs.pods.io/displaying-pods/access-rights-in-pods/',
			),
			'type'               => 'pick',
			'default'            => 'inherit',
			'pick_format_type'   => 'single',
			'pick_format_single' => 'radio',
			'data'               => array(
				'1'       => __( 'Enable access-related admin notices above forms/content displayed', 'pods' ),
				'0'       => __( 'Disable access-related admin notices above forms/content displayed', 'pods' ),
				'inherit' => __( 'Default - Use the global Pods setting for this', 'pods' ),
			),
			'depends-on'         => array( 'restrict_dynamic_features' => '1' ),
			'excludes-on'        => array( 'dynamic_features_allow' => '0' ),
		);
	}

	$options['security_access_rights_preview'] = array(
		'label'        => __( 'Capabilities preview', 'pods' ),
		'type'         => 'html',
		'html_content' => '
			<p>' . esc_html__( 'Below is a list of capabilities that a user will normally need for this content.', 'pods' ) . '</p>
		' . pods_access_get_capabilities_preview( $pod_type, $pod_name ),
	);

	return $options;
}

/**
 * Get the list of dynamic features allow options.
 *
 * @since 3.1.0
 *
 * @return array The list of dynamic features allow options.
 */
function pods_access_get_dynamic_features_allow_options() {
	return array(
		'inherit' => __( 'WP Default (if content type is Public)', 'pods' ),
		'1'       => __( 'Enabled', 'pods' ),
		'0'       => '🔒 ' . __( 'Disabled', 'pods' ),
	);
}

/**
 * Get the list of restricted dynamic features options.
 *
 * @since 3.1.0
 *
 * @return array The list of restricted dynamic features options.
 */
function pods_access_get_restricted_dynamic_features_options() {
	return array(
		'display' => '🔒 ' . __( 'Display', 'pods' ),
		'form'    => '🔒 ' . __( 'Form', 'pods' ),
	);
}

/**
 * Get the access rights capabilities preview HTML.
 *
 * @since 3.1.0
 *
 * @param string $pod_type The pod type.
 * @param string $pod_name The pod name.
 *
 * @return string The access rights capabilities preview HTML.
 */
function pods_access_get_capabilities_preview( $pod_type, $pod_name ) {
	$capabilities = pods_access_map_capabilities(
		array(
			'object_type' => $pod_type,
			'object_name' => $pod_name,
		),
		null,
		true
	);

	if ( null === $capabilities ) {
		$capabilities = array(
			'read'   => null,
			'add'    => null,
			'edit'   => null,
			'delete' => null,
		);
	}

	$capabilities_preview = array(
		'read'             => esc_html__( 'Read capability', 'pods' ),
		'add'              => esc_html__( 'Add New capability', 'pods' ),
		'edit'             => esc_html__( 'Edit capability', 'pods' ),
		'delete'           => esc_html__( 'Delete capability', 'pods' ),
		'read_private'     => esc_html__( 'Read Private capability', 'pods' ),
		'edit_others'      => esc_html__( 'Edit Others capability', 'pods' ),
		'delete_others'    => esc_html__( 'Delete Others capability', 'pods' ),
		'delete_published' => esc_html__( 'Delete Published capability', 'pods' ),
		'delete_private'   => esc_html__( 'Delete Private capability', 'pods' ),
	);

	$capabilities_preview_list = array(
		'<strong>' . $capabilities_preview['read'] . ':</strong> ' . ( $capabilities['read'] ?: __( 'Not restricted', 'pods' ) ),
	);

	if ( 'settings' !== $pod_type ) {
		$capabilities_preview_list[] = '<strong>' . $capabilities_preview['add'] . ':</strong> ' . ( $capabilities['add'] ?: __( 'Not restricted', 'pods' ) );
	}

	$capabilities_preview_list[] = '<strong>' . $capabilities_preview['edit'] . ':</strong> ' . ( $capabilities['edit'] ?: __( 'Not restricted', 'pods' ) );

	if ( 'settings' !== $pod_type ) {
		$capabilities_preview_list[] = '<strong>' . $capabilities_preview['delete'] . ':</strong> ' . ( $capabilities['delete'] ?: __( 'Not restricted', 'pods' ) );
	}

	if ( $capabilities && array_key_exists( 'read_private', $capabilities ) ) {
		$capabilities_preview_list[] = '<strong>' . $capabilities_preview['read_private'] . ':</strong> ' . ( $capabilities['read_private'] ?: __( 'Not restricted', 'pods' ) );
	}

	if ( $capabilities && array_key_exists( 'edit_others', $capabilities ) ) {
		$capabilities_preview_list[] = '<strong>' . $capabilities_preview['edit_others'] . ':</strong> ' . ( $capabilities['edit_others'] ?: __( 'Not restricted', 'pods' ) );
	}

	if ( $capabilities && array_key_exists( 'delete_others', $capabilities ) ) {
		$capabilities_preview_list[] = '<strong>' . $capabilities_preview['delete_others'] . ':</strong> ' . ( $capabilities['delete_others'] ?: __( 'Not restricted', 'pods' ) );
	}

	if ( $capabilities && array_key_exists( 'delete_published', $capabilities ) ) {
		$capabilities_preview_list[] = '<strong>' . $capabilities_preview['delete_published'] . ':</strong> ' . ( $capabilities['delete_published'] ?: __( 'Not restricted', 'pods' ) );
	}

	if ( $capabilities && array_key_exists( 'delete_private', $capabilities ) ) {
		$capabilities_preview_list[] = '<strong>' . $capabilities_preview['delete_private'] . ':</strong> ' . ( $capabilities['delete_private'] ?: __( 'Not restricted', 'pods' ) );
	}

	return '
		<ul>
			<li>' . implode( '</li><li>', $capabilities_preview_list ) . '</li>
		</ul>
	';
}

/**
 * Get the pod settings config for access-related settings.
 *
 * @since 3.1.0
 *
 * @return array The pod settings config for access-related settings.
 */
function pods_access_settings_config() {
	$first_pods_version = get_option( 'pods_framework_version_first' );
	$first_pods_version = '' === $first_pods_version ? PODS_VERSION : $first_pods_version;

	$fields = array();

	$fields['dynamic_features_allow'] = array(
		'name'               => 'dynamic_features_allow',
		'label'              => __( 'Dynamic Features', 'pods' ),
		'help'               => array(
			__( 'Enabling Dynamic Features will also enable the additional access rights checks for user access. This ensures that people viewing embedded content and forms have the required capabilties. Even when Dynamic Features are disabled, you can still embed Pods Content and Forms through PHP and make use of other features directly through code.', 'pods' ),
			'https://docs.pods.io/displaying-pods/access-rights-in-pods/',
		),
		'description'        => __( 'Dynamic features include Pods Shortcodes, Blocks, and Widgets which let you embed content and forms on your site.', 'pods' ),
		'type'               => 'pick',
		'default'            => '1',
		'pick_format_type'   => 'single',
		'pick_format_single' => 'radio',
		'data'               => array(
			'1' => __( 'Enable Dynamic Features including Pods Shortcodes, Blocks, and Widgets', 'pods' ),
			'0' => __( 'Disable All Dynamic Features in Pods', 'pods' ),
		),
		'site_health_data' => array(
			'1' => __( 'Enable', 'pods' ),
			'0' => __( 'Disable', 'pods' ),
		),
		'site_health_include_in_info' => true,
	);

	$fields['security_access_rights_info'] = array(
		'name'               => 'security_access_rights_info',
		'label'              => __( 'How access rights work in Pods', 'pods' ),
		'type'               => 'html',
		'html_content'       => sprintf(
			'
				<p>%1$s</p>
				<p><a href="https://docs.pods.io/displaying-pods/access-rights-in-pods/" target="_blank" rel="noopener noreferrer">%2$s</a> <span class="dashicon dashicons dashicons-external"></span></p>
			',
			__( 'Pods handles access rights similar to how WordPress itself works.', 'pods' ),
			__( 'Read more about how access rights work in Pods on our Documentation site', 'pods' )
		),
		'depends-on'         => array( 'dynamic_features_allow' => '1' ),
	);

	$fields['dynamic_features_enabled'] = array(
		'name'               => 'dynamic_features_enabled',
		'label'              => __( 'Dynamic Features to Enable', 'pods' ),
		'help'               => array(
			__( 'You can choose one or more dynamic features to enable. By default, only Display and Form are enabled.', 'pods' ),
			'https://docs.pods.io/displaying-pods/access-rights-in-pods/',
		),
		'type'                        => 'pick',
		'default'                     => array(
			'display',
			'form',
		),
		'pick_format_type'   => 'multi',
		'pick_format_multi'  => 'checkbox',
		'data'               => array(
			'display' => __( 'Display - Shortcodes and Blocks that allow querying content from *any* Pod and displaying any field (WordPress access rights are still checked).', 'pods' ),
			'form'    => __( 'Form - The Form Shortcode and Block that allows submitting new content or editing existing content from *any* Pod (WordPress access rights are still checked).', 'pods' ),
			'view'    => __( 'View - The View Shortcode and Block that allows embedding *any* theme file on a page.', 'pods' ),
		),
		'site_health_data' => array(
			'display' => __( 'Display', 'pods' ),
			'form'    => __( 'Form', 'pods' ),
			'view'    => __( 'View', 'pods' ),
		),
		'depends-on'                  => array( 'dynamic_features_allow' => '1' ),
		'site_health_include_in_info' => true,
	);

	$fields['show_access_restricted_messages'] = array(
		'name'               => 'show_access_restricted_messages',
		'label'              => __( 'Access-related Restricted Messages', 'pods' ),
		'help'               => array(
			__( 'Access-related Restricted Messages will show to anyone who does not have access to add/edit/read a specific item from a content type.', 'pods' ),
			'https://docs.pods.io/displaying-pods/access-rights-in-pods/',
		),
		'type'               => 'pick',
		'default'            => '0',
		'pick_format_type'   => 'single',
		'pick_format_single' => 'radio',
		'data'               => array(
			'1' => __( 'Enable access-related restricted messages for forms/content displayed (instead of the form/content output)', 'pods' ),
			'0' => __( 'Disable access-related restricted messages for forms/content displayed (the form/content output will be blank)', 'pods' ),
		),
		'site_health_data' => array(
			'1' => __( 'Enable', 'pods' ),
			'0' => __( 'Disable', 'pods' ),
		),
		'site_health_include_in_info' => true,
		'depends-on'         => array( 'dynamic_features_allow' => '1' ),
	);

	$fields['show_access_admin_notices'] = array(
		'name'               => 'show_access_admin_notices',
		'label'              => __( 'Access-related Admin Notices', 'pods' ),
		'help'               => array(
			__( 'Access-related Admin Notices will only show to admins and will appear above content/forms that may not be entirely public.', 'pods' ),
			'https://docs.pods.io/displaying-pods/access-rights-in-pods/',
		),
		'type'               => 'pick',
		'default'            => '1',
		'pick_format_type'   => 'single',
		'pick_format_single' => 'radio',
		'data'               => array(
			'1' => __( 'Enable access-related admin notices above forms/content displayed', 'pods' ),
			'0' => __( 'Disable access-related admin notices above forms/content displayed', 'pods' ),
		),
		'site_health_data' => array(
			'1' => __( 'Enable', 'pods' ),
			'0' => __( 'Disable', 'pods' ),
		),
		'site_health_include_in_info' => true,
		'depends-on'         => array( 'dynamic_features_allow' => '1' ),
	);

	$fields['dynamic_features_allow_sql_clauses'] = array(
		'name'               => 'dynamic_features_allow_sql_clauses',
		'label'              => __( 'Allow SQL clauses to be used in Dynamic Features', 'pods' ),
		'description'        => __( 'SQL clauses in general should only be enabled for sites with trusted users. Since WordPress allows anyone to enter any shortcode or block in the editor, any person with the Contributor role or higher could have access to use this.', 'pods' ),
		'type'               => 'pick',
		'default'            => version_compare( $first_pods_version, '3.1.0-a-1', '<' ) ? 'simple' : '0',
		'pick_format_type'   => 'single',
		'pick_format_single' => 'radio',
		'data'               => array(
			'all'    => __( 'Unrestricted - Enable ALL SQL clause usage through dynamic features (only use this if you trust ALL users who have access to create content)', 'pods' ),
			'simple' => __( 'Restricted - Enable Simple SQL clause usage (only SELECT, WHERE, and ORDER BY) through dynamic features (only use this if you trust ALL users who have access to create content)', 'pods' ),
			'0'      => __( 'Disable SQL clause usage through dynamic features', 'pods' ),
		),
		'site_health_data' => array(
			'all'    => __( 'Unrestricted', 'pods' ),
			'simple' => __( 'Restricted', 'pods' ),
			'0'      => __( 'Disable', 'pods' ),
		),
		'depends-on'                  => array(
			'dynamic_features_allow' => '1',
		),
		'depends-on-multi'            => array(
			'dynamic_features_enabled' => 'display',
		),
		'site_health_include_in_info' => true,
	);

	$fields['display_callbacks'] = array(
		'name'               => 'display_callbacks',
		'label'              => __( 'Display callbacks', 'pods' ),
		'description'        => __( 'Callbacks can be used when using Pods Templating syntax like {@my_field,my_callback} in your magic tags.', 'pods' ),
		'type'               => 'pick',
		'default'            => 'restricted',
		'pick_format_type'   => 'single',
		'pick_format_single' => 'radio',
		'data'               => array(
			'restricted' => __( 'Restricted - Certain system PHP functions are disallowed from being used for security reasons.', 'pods' ),
			'customized' => __( 'Customized - Only allow a list of specific PHP function callbacks.', 'pods' ),
			'0'          => __( 'Disable display callbacks', 'pods' ),
		),
		'site_health_data' => array(
			'restricted' => __( 'Restricted', 'pods' ),
			'customized' => __( 'Customized', 'pods' ),
			'0'          => __( 'Disable', 'pods' ),
		),
		'depends-on'                  => array(
			'dynamic_features_allow' => '1',
		),
		'depends-on-multi'     => array(
			'dynamic_features_enabled' => 'display',
		),
		'site_health_include_in_info' => true,
	);

	$fields['display_callbacks_allowed'] = array(
		'name'               => 'display_callbacks_allowed',
		'label'              => __( 'Display callbacks allowed', 'pods' ),
		'description'        => __( 'Please provide a comma-separated list of additional PHP function names to allow in callbacks, on top of the built-in safe list. Each additional function name must start with "custom_pods_callback_" and any other names are ignored for security purposes. You may choose to add custom PHP filter for "pods_access_callbacks_custom_prefix" to change this prefix.', 'pods' ),
		'type'               => 'text',
		'default'            => '',
		'depends-on'         => array(
			'dynamic_features_allow'   => '1',
			'display_callbacks'        => 'customized',
		),
		'depends-on-multi'            => array(
			'dynamic_features_enabled' => 'display',
		),
		'site_health_include_in_info' => true,
	);

	$fields['show_display_callback_notices'] = array(
		'name'                        => 'show_display_callback_notices',
		'label'                       => __( 'Display callback notices', 'pods' ),
		'description'                 => __( 'When enabled, Pods will record disallowed display callbacks as they are detected and show an admin notice on the Pods Settings page listing those callbacks.', 'pods' ),
		'type'                        => 'pick',
		'default'                     => '1',
		'pick_format_type'            => 'single',
		'pick_format_single'          => 'radio',
		'data'                        => array(
			'1' => __( 'Enable admin notices when disallowed display callbacks are detected', 'pods' ),
			'0' => __( 'Disable admin notices when disallowed display callbacks are detected', 'pods' ),
		),
		'site_health_data'            => array(
			'1' => __( 'Enable', 'pods' ),
			'0' => __( 'Disable', 'pods' ),
		),
		'depends-on'                  => array(
			'dynamic_features_allow' => '1',
		),
		'depends-on-multi'            => array(
			'dynamic_features_enabled' => 'display',
		),
		'site_health_include_in_info' => true,
	);

	return $fields;
}

/**
 * Get the bleep placeholder text.
 *
 * @since 3.1.0
 *
 * @return string The bleep placeholder text.
 */
function pods_access_bleep_placeholder() {
	return '****************';
}

/**
 * Process the value and bleep it if it needs to be.
 *
 * @since 3.1.0
 *
 * @param string|mixed $value The value to be bleeped.
 *
 * @return string|mixed The bleeped text if not empty, otherwise the value as it was.
 */
function pods_access_bleep_text( $value ) {
	$bleep_text = pods_access_bleep_placeholder();

	if ( 0 < strlen( (string) $value ) ) {
		$value = $bleep_text;
	}

	return $value;
}

/**
 * Process the data and bleep anything that needs to be.
 *
 * @since 3.1.0
 *
 * @param array|object $data                        The data to be bleeped.
 * @param array        $additional_bleep_properties The additional properties to be bleeped from objects and arrays.
 *
 * @return array|object The bleeped data.
 */
function pods_access_bleep_data( $data, $additional_bleep_properties = array() ) {
	$bleep_properties = array(
		'user_pass',
		'user_activation_key',
		'post_password',
	);

	/**
	 * Allow filtering the additional properties to be bleeped from objects and arrays.
	 *
	 * @since 3.1.0
	 *
	 * @param array        $additional_bleep_properties The additional properties to be bleeped from objects and arrays.
	 * @param array|object $data                        The data to be bleeped.
	 */
	$additional_bleep_properties = apply_filters( 'pods_access_bleep_properties', $additional_bleep_properties, $data );

	$bleep_properties = array_merge( $bleep_properties, $additional_bleep_properties );

	$bleep_text = pods_access_bleep_placeholder();

	if ( is_object( $data ) ) {
		foreach ( $bleep_properties as $bleep_property ) {
			if ( isset( $data->{$bleep_property} ) ) {
				$data->{$bleep_property} = 0 < strlen( (string) $data->{$bleep_property} ) ? $bleep_text : '';
			}
		}
	} elseif ( is_array( $data ) ) {
		foreach ( $bleep_properties as $bleep_property ) {
			if ( isset( $data[ $bleep_property ] ) ) {
				$data[ $bleep_property ] = 0 < strlen( (string) $data[ $bleep_property ] ) ? $bleep_text : '';
			}
		}
	}

	return $data;
}

/**
 * Process the data and bleep anything that needs to be.
 *
 * @since 3.1.0
 *
 * @param array $items                       The items to be bleeped.
 * @param array $additional_bleep_properties The additional properties to be bleeped from objects and arrays.
 *
 * @return array|object The bleeped data.
 */
function pods_access_bleep_items( $items, $additional_bleep_properties = array() ) {
	// Call the pods_access_bleep_data() function for all items in the $items array.
	return array_map(
		static function ( $item ) use ( $additional_bleep_properties ) {
			return pods_access_bleep_data( $item, $additional_bleep_properties );
		},
		$items
	);
}

/**
 * Determine whether the SQL fragment is allowed to be used.
 *
 * @since 3.1.0
 *
 * @param string $sql     The SQL fragment to check.
 * @param string $context The SQL fragment context.
 * @param array  $args    {
 *      The arguments to use.
 *
 *      @type string|null     $object_type The object type.
 *      @type string|null     $object_name The object name.
 *      @type int|string|null $item_id     The item ID.
 *      @type Pods|null       $pods        The Pods object.
 *      @type Pod|null        $pod         The Pod object.
 *      @type bool            $build_pods  Whether to try to build a Pods object from the object type/name/ID (false by default).
 *      @type bool            $build_pod   Whether to try to build a Pod object from the object type/name (false by default).
 * }
 *
 * @param object|null $params The parameters passed to Pods::find() or PodsData::select().
 *
 * @return bool Whether the SQL fragment is allowed to be used.
 */
function pods_access_sql_fragment_is_allowed( $sql, $context, $args = array(), $params = null ) {
	$context = strtoupper( $context );

	$info = pods_info_from_args( $args );

	/**
	 * Allows filtering whether the SQL fragment is allowed to be used.
	 *
	 * @since 3.1.0
	 *
	 * @param bool        $allowed Whether the SQL fragment is allowed to be used.
	 * @param string      $sql     The SQL fragment to check.
	 * @param string      $context The SQL fragment context.
	 * @param array       $info    Pod information.
	 * @param object|null $params  The parameters passed to Pods::find() or PodsData::select().
	 */
	return (bool) apply_filters( 'pods_access_sql_fragment_is_allowed', true, $sql, $context, $info, $params );
}

add_filter( 'pods_access_sql_fragment_is_allowed', 'pods_access_sql_fragment_disallow_mismatch_parenthesis', 10, 2 );
add_filter( 'pods_access_sql_fragment_is_allowed', 'pods_access_sql_fragment_disallow_comments', 10, 2 );
add_filter( 'pods_access_sql_fragment_is_allowed', 'pods_access_sql_fragment_disallow_unsafe_functions', 10, 2 );
add_filter( 'pods_access_sql_fragment_is_allowed', 'pods_access_sql_fragment_disallow_unsafe_keywords', 10, 2 );
add_filter( 'pods_access_sql_fragment_is_allowed', 'pods_access_sql_fragment_disallow_unsafe_tables', 10, 2 );
add_filter( 'pods_access_sql_fragment_is_allowed', 'pods_access_sql_fragment_disallow_double_hyphens', 10, 2 );
add_filter( 'pods_access_sql_fragment_is_allowed', 'pods_access_sql_fragment_disallow_subqueries', 10, 2 );
add_filter( 'pods_access_sql_fragment_is_allowed', 'pods_access_sql_fragment_disallow_post_status', 10, 5 );

/**
 * Disallow parenthesis in SQL fragments that are not balanced at every position.
 *
 * @since 3.1.0
 *
 * @param bool   $allowed Whether the SQL fragment is allowed to be used.
 * @param string $sql     The SQL fragment to check.
 *
 * @return bool Whether the SQL fragment is allowed to be used.
 */
function pods_access_sql_fragment_disallow_mismatch_parenthesis( $allowed, $sql ) {
	if ( ! $allowed ) {
		return $allowed;
	}

	// Remove quoted string literals ('' and "" quoting, with backslash/doubled-quote escaping).
	$stripped = preg_replace(
		array(
			"/'(?:[^'\\\\]|\\\\.|'')*'/s",
			'/"(?:[^"\\\\]|\\\\.|"")*"/s',
		),
		'',
		$sql
	);

	if ( null === $stripped ) {
		// preg_replace failed (e.g. malformed input); fail closed.
		return false;
	}

	$depth  = 0;
	$length = strlen( $stripped );

	for ( $i = 0; $i < $length; $i++ ) {
		$char = $stripped[ $i ];

		if ( '(' === $char ) {
			$depth++;
		} elseif ( ')' === $char ) {
			$depth--;

			// More closes than opens at this point: the fragment escapes its wrapping.
			if ( $depth < 0 ) {
				return false;
			}
		}
	}

	return 0 === $depth;
}

/**
 * Disallow unsafe functions from being used in SQL fragments.
 *
 * @since 3.1.0
 *
 * @param bool   $allowed Whether the SQL fragment is allowed to be used.
 * @param string $sql     The SQL fragment to check.
 *
 * @return bool Whether the SQL fragment is allowed to be used.
 */
function pods_access_sql_fragment_disallow_unsafe_functions( $allowed, $sql ) {
	if ( ! $allowed ) {
		return $allowed;
	}

	$unsafe_functions = array(
		// Server / database / session information functions.
		'USER',
		'CURRENT_USER',
		'SESSION_USER',
		'SYSTEM_USER',
		'DATABASE',
		'SCHEMA',
		'VERSION',
		'CONNECTION_ID',
		'CURRENT_ROLE',
		'ROW_COUNT',
		'LAST_INSERT_ID',
		'CHARSET',
		'COLLATION',
		'COERCIBILITY',
		'STATEMENT_DIGEST',
		'STATEMENT_DIGEST_TEXT',

		// Filesystem access.
		'LOAD_FILE',

		// Timing / locking functions.
		'SLEEP',
		'BENCHMARK',
		'GET_LOCK',
		'RELEASE_LOCK',
		'RELEASE_ALL_LOCKS',
		'IS_FREE_LOCK',
		'IS_USED_LOCK',
		'WAIT_FOR_EXECUTED_GTID_SET',
		'WAIT_UNTIL_SQL_THREAD_AFTER_GTIDS',
		'MASTER_POS_WAIT',
		'SOURCE_POS_WAIT',
		'GTID_SUBSET',
		'GTID_SUBTRACT',

		// Encoding / encryption / compression functions.
		'FROM_BASE64',
		'TO_BASE64',
		'UNHEX',
		'AES_ENCRYPT',
		'AES_DECRYPT',
		'DES_ENCRYPT',
		'DES_DECRYPT',
		'ENCODE',
		'DECODE',
		'COMPRESS',
		'UNCOMPRESS',
		'UNCOMPRESSED_LENGTH',

		// Error-based extraction (leak data through forced XPath / other errors).
		'EXTRACTVALUE',
		'UPDATEXML',

		// Deprecated analysis clause.
		'ANALYSE',

		// Common lib_mysqludf_sys UDFs.
		'SYS_EXEC',
		'SYS_EVAL',
	);

	/**
	 * Allow filtering the list of additional unsafe functions to disallow.
	 *
	 * @since 3.1.0
	 *
	 * @param array  $unsafe_functions The list of unsafe functions to disallow.
	 * @param string $sql              The SQL fragment to check.
	 */
	$additional_unsafe_functions = (array) apply_filters( 'pods_access_sql_fragment_disallow_unsafe_functions', $unsafe_functions, $sql );

	$unsafe_functions = array_unique( array_filter( array_merge( $unsafe_functions, $additional_unsafe_functions ) ) );

	foreach ( $unsafe_functions as $unsafe_function ) {
		if ( 1 === (int) preg_match( '/\s*' . preg_quote( $unsafe_function, '/' ) . '\s*\(/i', $sql ) ) {
			return false;
		}
	}

	return $allowed;
}

/**
 * Disallow unsafe tables from being used in SQL fragments.
 *
 * @since 3.1.0
 *
 * @param bool   $allowed Whether the SQL fragment is allowed to be used.
 * @param string $sql     The SQL fragment to check.
 *
 * @return bool Whether the SQL fragment is allowed to be used.
 */
function pods_access_sql_fragment_disallow_unsafe_tables( $allowed, $sql ) {
	if ( ! $allowed ) {
		return $allowed;
	}

	$unsafe_tables = array(
		'mysql.',
		'information_schema.',
		'performance_schema.',
		'sys.',
	);

	/**
	 * Allow filtering the list of unsafe tables to disallow.
	 *
	 * @since 3.1.0
	 *
	 * @param array  $unsafe_tables The list of unsafe tables to disallow.
	 * @param string $sql           The SQL fragment to check.
	 */
	$unsafe_tables = (array) apply_filters( 'pods_access_sql_fragment_disallow_unsafe_tables', $unsafe_tables, $sql );

	$unsafe_tables = array_filter( $unsafe_tables );

	/*
	 * Normalize the fragment before matching so that identifier quoting and
	 * spacing around the "." separator cannot be used to evade the check, e.g.
	 * "`information_schema`.`tables`" or "information_schema . tables" both
	 * normalize to "information_schema.tables".
	 */
	$normalized_sql = str_replace( '`', '', $sql );
	$normalized_sql = preg_replace( '/\s*\.\s*/', '.', $normalized_sql );

	foreach ( $unsafe_tables as $unsafe_table ) {
		if ( 1 === (int) preg_match( '/' . preg_quote( $unsafe_table, '/' ) . '/i', $normalized_sql ) ) {
			return false;
		}
	}

	return $allowed;
}

/**
 * Disallow double hyphens from being used in SQL fragments.
 *
 * @since 3.1.0
 *
 * @param bool   $allowed Whether the SQL fragment is allowed to be used.
 * @param string $sql     The SQL fragment to check.
 *
 * @return bool Whether the SQL fragment is allowed to be used.
 */
function pods_access_sql_fragment_disallow_double_hyphens( $allowed, $sql ) {
	return (
		$allowed
		&& false === strpos( $sql, '--' )
	);
}

/**
 * Disallow SQL comment markers from being used in SQL fragments.
 *
 * @since 3.1.0
 * @param bool   $allowed Whether the SQL fragment is allowed to be used.
 * @param string $sql     The SQL fragment to check.
 * @return bool Whether the SQL fragment is allowed to be used.
 */
function pods_access_sql_fragment_disallow_comments( $allowed, $sql ) {
	if ( ! $allowed ) {
		return $allowed;
	}

	if (
		false !== strpos( $sql, '--' )
		|| false !== strpos( $sql, '/*' )
		|| false !== strpos( $sql, '*/' )
	) {
		return false;
	}

	// Strip quoted string literals so a "#" inside a value is not treated as a comment.
	$stripped = preg_replace(
		array(
			"/'(?:[^'\\\\]|\\\\.|'')*'/s",
			'/"(?:[^"\\\\]|\\\\.|"")*"/s',
		),
		'',
		$sql
	);

	if ( null === $stripped ) {
		// preg_replace failed (e.g. malformed input); fail closed.
		return false;
	}

	return false === strpos( $stripped, '#' );
}

/**
 * Disallow unsafe keywords from being used in SQL fragments.
 *
 * @since 3.1.0
 * @param bool   $allowed Whether the SQL fragment is allowed to be used.
 * @param string $sql     The SQL fragment to check.
 * @return bool Whether the SQL fragment is allowed to be used.
 */
function pods_access_sql_fragment_disallow_unsafe_keywords( $allowed, $sql ) {
	if ( ! $allowed ) {
		return $allowed;
	}

	$unsafe_patterns = array(
		// System / session variables.
		'/@@/',
		// Combining result sets.
		'/\bUNION\b/i',
		// File output keywords.
		'/\bINTO\s+(?:OUTFILE|DUMPFILE)\b/i',
		// File read keywords.
		'/\bLOAD\s+DATA\b/i',
		// Statement separator.
		'/;/',
	);

	/**
	 * Allow filtering the list of unsafe keyword patterns to disallow.
	 *
	 * Each entry is a full PCRE pattern (including delimiters and flags) that is
	 * tested against the SQL fragment; a match disallows the fragment.
	 *
	 * @since 3.1.0
	 *
	 * @param array  $unsafe_patterns The list of unsafe keyword patterns to disallow.
	 * @param string $sql             The SQL fragment to check.
	 */
	$unsafe_patterns = (array) apply_filters( 'pods_access_sql_fragment_disallow_unsafe_keywords', $unsafe_patterns, $sql );

	$unsafe_patterns = array_filter( $unsafe_patterns );

	foreach ( $unsafe_patterns as $unsafe_pattern ) {
		if ( 1 === (int) preg_match( $unsafe_pattern, $sql ) ) {
			return false;
		}
	}

	return $allowed;
}

/**
 * Disallow subqueries from being used in SQL fragments.
 *
 * @since 3.1.0
 *
 * @param bool   $allowed Whether the SQL fragment is allowed to be used.
 * @param string $sql     The SQL fragment to check.
 *
 * @return bool Whether the SQL fragment is allowed to be used.
 */
function pods_access_sql_fragment_disallow_subqueries( $allowed, $sql ) {
	return (
		$allowed
		&& 0 === (int) preg_match( '/\s*SELECT(\s|\()+/i', $sql )
	);
}

/**
 * Disallow post_status from being used in the WHERE/HAVING/FIELD SQL fragment unless they have admin access,
 * can edit posts for the post type, or the fragment only compares post_status to publish.
 *
 * @since 3.1.0
 *
 * @param bool        $allowed Whether the SQL fragment is allowed to be used.
 * @param string      $sql     The SQL fragment to check.
 * @param string      $context The SQL fragment context.
 * @param array       $info    Pod information.
 * @param object|null $params  The parameters passed to Pods::find() or PodsData::select().
 *
 * @return bool Whether the SQL fragment is allowed to be used.
 */
function pods_access_sql_fragment_disallow_post_status( $allowed, $sql, $context, $info, $params = null ) {
	if ( ! $allowed ) {
		return $allowed;
	}

	if ( 'WHERE' !== $context && 'HAVING' !== $context && 'FIELD' !== $context ) {
		return true;
	}

	// Check if post_status is allowed.
	if ( false === stripos( $sql, 'post_status' ) ) {
		return true;
	}

	if ( empty( $params ) || empty( $params->from ) || ! in_array( $params->from, array( 'dynamic-embed', 'pick/get_object_data' ), true ) ) {
		return true;
	}

	if ( pods_is_admin() ) {
		return true;
	}

	if (
		! empty( $info['object_type'] )
		&& 'post_type' === $info['object_type']
		&& ! empty( $info['object_name'] )
	) {
		$post_type_object = get_post_type_object( $info['object_name'] );

		if (
			$post_type_object instanceof WP_Post_Type
			&& $post_type_object->cap->edit_posts
			&& current_user_can( $post_type_object->cap->edit_posts )
		) {
			return true;
		}
	}

	// Check for variations and exclude them, if post_status still matches then return false.
	$safe_sql = preg_replace(
		'/post_status\s*=\s*(?:\'publish\'|"publish")/i',
		'',
		$sql
	);

	return (
		null === $safe_sql
		|| false === stripos( $safe_sql, 'post_status' )
	);
}

/**
 * Safely unserialize data if it's PHP serialized.
 *
 * @since 3.1.0
 *
 * @param string|mixed $data The data to unserialize.
 *
 * @return array|string|mixed The unserialized data if it was PHP serialized, otherwise the data as it was.
 */
function pods_maybe_safely_unserialize( $data ) {
	// The $options parameter of unserialize() requires PHP 7.0+.
	if ( version_compare( PHP_VERSION, '7.0', '<' ) ) {
		// On PHP < 7, refuse payloads that contain a serialized object; other data falls back to the normal WP function, to help prevent security issues.
		if ( is_string( $data ) && preg_match( '/(?:^|;|{)[OC]:\d+:"/', $data ) ) {
			return $data;
		}

		// Fall back to normal WP function.
		return maybe_unserialize( $data );
	}

	// Check if the data is serialized.
	if ( is_serialized( $data ) ) {
		$data = trim( $data );

		// Unserialize the data but exclude classes.
		return @unserialize( $data, array( 'allowed_classes' => false ) );
	}

	return $data;
}

/**
 * Get the field name map used for Pods form nonce hidden inputs.
 *
 * @since 3.3.9.2
 *
 * @param string $context   The form context. Accepts 'form' or 'meta'.
 * @param string $group_key Optional group key used to suffix the field names so multiple
 *                          groups on the same page do not collide (defaults to empty).
 *
 * @return array {
 *     The hidden field names.
 *
 * @type string  $nonce   The nonce field name.
 * @type string  $pod     The pod field name.
 * @type string  $id      The item ID field name.
 * @type string  $uri     The URI hash field name.
 * @type string  $form    The field list field name.
 *                        }
 */
function pods_access_form_field_names( $context, $group_key = '' ) {
	$prefix = '_pods_';

	if ( 'meta' === $context ) {
		$prefix = 'pods_meta_';
	}

	$suffix = '';

	if ( is_scalar( $group_key ) && '' !== (string) $group_key ) {
		$group_key = sanitize_key( (string) $group_key );

		if ( '' !== $group_key ) {
			$suffix = '_' . $group_key;
		}
	}

	return array(
		'nonce' => $prefix . 'nonce' . $suffix,
		'pod'   => $prefix . 'pod' . $suffix,
		'id'    => $prefix . 'id' . $suffix,
		'uri'   => $prefix . 'uri' . $suffix,
		'form'  => $prefix . 'form' . $suffix,
	);
}

/**
 * Get the UID used for Pods form nonces.
 *
 * @since 3.3.9.2
 *
 * @return string The UID.
 */
function pods_access_form_uid() {
	if ( is_user_logged_in() ) {
		return 'user_' . get_current_user_id();
	}

	return pods_session_id();
}

/**
 * Get the URI hash used for Pods form nonces.
 *
 * @since 3.3.9.2
 *
 * @param string|null $path The request path. Defaults to the current path.
 *
 * @return string The URI hash.
 */
function pods_access_form_uri_hash( $path = null ) {
	if ( null === $path || '' === $path ) {
		$path = '/';

		if ( ! empty( $_SERVER['REQUEST_URI'] ) ) {
			$path = $_SERVER['REQUEST_URI'];
		}
	}

	return wp_create_nonce( 'pods_uri_' . (string) $path );
}

/**
 * Normalize a list of form fields to a comma-separated string.
 *
 * @since 3.3.9.2
 *
 * @param array|string $submitted_fields The fields array or comma-separated string.
 *
 * @return string The normalized field list.
 */
function pods_access_form_normalize_fields( $submitted_fields ) {
	if ( is_string( $submitted_fields ) ) {
		return $submitted_fields;
	}

	if ( ! is_array( $submitted_fields ) ) {
		return '';
	}

	if ( isset( $submitted_fields[0] ) && is_string( $submitted_fields[0] ) ) {
		$names = array();

		foreach ( $submitted_fields as $submitted_field ) {
			if ( ! is_scalar( $submitted_field ) ) {
				return implode( ',', array_keys( $submitted_fields ) );
			}

			$names[] = (string) $submitted_field;
		}

		return implode( ',', $names );
	}

	return implode( ',', array_keys( $submitted_fields ) );
}

/**
 * Get the field hash used for Pods form nonces.
 *
 * @since 3.3.9.2
 *
 * @param array|string $submitted_fields The fields array or comma-separated string.
 *
 * @return string The field hash.
 */
function pods_access_form_field_hash( $submitted_fields ) {
	$form = pods_access_form_normalize_fields( $submitted_fields );

	return wp_create_nonce( 'pods_fields_' . $form );
}

/**
 * Build the nonce action string for a Pods form.
 *
 * @since 3.3.9.2
 *
 * @param string       $pod              The Pod name.
 * @param int|string   $id               The item ID.
 * @param array|string $submitted_fields The fields array or comma-separated string.
 * @param string|null  $uri_hash         The URI hash. Defaults to the current path hash.
 * @param string|null  $uid              The UID. Defaults to the current user or session ID.
 *
 * @return string The nonce action string.
 */
function pods_access_form_nonce_action( $pod, $id, $submitted_fields, $uri_hash = null, $uid = null ) {
	if ( null === $uri_hash ) {
		$uri_hash = pods_access_form_uri_hash();
	}

	if ( null === $uid ) {
		$uid = pods_access_form_uid();
	}

	$field_hash = pods_access_form_field_hash( $submitted_fields );

	return 'pods_form_' . (string) $pod . '_' . (string) $uid . '_' . (int) $id . '_' . (string) $uri_hash . '_' . (string) $field_hash;
}

/**
 * Create a Pods form nonce.
 *
 * @since 3.3.9.2
 *
 * @param string       $pod              The Pod name.
 * @param int|string   $id               The item ID.
 * @param array|string $submitted_fields The fields array or comma-separated string.
 * @param string|null  $uri_hash         The URI hash. Defaults to the current path hash.
 *
 * @return string The nonce.
 */
function pods_access_create_form_nonce( $pod, $id, $submitted_fields, $uri_hash = null ) {
	return wp_create_nonce( pods_access_form_nonce_action( $pod, $id, $submitted_fields, $uri_hash ) );
}

/**
 * Verify a Pods form nonce.
 *
 * @since 3.3.9.2
 *
 * @param string       $nonce            The nonce value.
 * @param string       $pod              The Pod name.
 * @param int|string   $id               The item ID.
 * @param array|string $submitted_fields The fields array or comma-separated string.
 * @param string|null  $uri_hash         The URI hash. Defaults to the current path hash.
 *
 * @return bool Whether the nonce is valid.
 */
function pods_access_verify_form_nonce( $nonce, $pod, $id, $submitted_fields, $uri_hash = null ) {
	if ( ! is_scalar( $nonce ) || '' === $nonce ) {
		return false;
	}

	$uid = pods_access_form_uid();

	if ( empty( $uid ) ) {
		return false;
	}

	$action = pods_access_form_nonce_action( $pod, $id, $submitted_fields, $uri_hash, $uid );

	return false !== wp_verify_nonce( (string) $nonce, $action );
}

/**
 * Get hidden fields for a Pods form nonce as an HTML string.
 *
 * @since 3.3.9.2
 *
 * @param string       $pod               The Pod name.
 * @param int|string   $id                The item ID.
 * @param array|string $submitted_fields  The fields array or comma-separated string.
 * @param array|null   $nonce_field_names The hidden nonce field names. Defaults to standard nonce form fields.
 * @param string|null  $uri_hash          The URI hash. Defaults to the current path hash.
 *
 * @return string The hidden field HTML.
 */
function pods_access_get_form_nonce_fields( $pod, $id, $submitted_fields, $nonce_field_names = null, $uri_hash = null ) {
	if ( null === $nonce_field_names ) {
		$nonce_field_names = pods_access_form_field_names( 'form' );
	}

	if ( null === $uri_hash ) {
		$uri_hash = pods_access_form_uri_hash();
	}

	$form  = pods_access_form_normalize_fields( $submitted_fields );
	$nonce = pods_access_create_form_nonce( $pod, $id, $submitted_fields, $uri_hash );

	$html  = PodsForm::field( $nonce_field_names['nonce'], $nonce, 'hidden' );
	$html .= PodsForm::field( $nonce_field_names['pod'], (string) $pod, 'hidden' );
	$html .= PodsForm::field( $nonce_field_names['id'], (int) $id, 'hidden' );
	$html .= PodsForm::field( $nonce_field_names['uri'], (string) $uri_hash, 'hidden' );
	$html .= PodsForm::field( $nonce_field_names['form'], $form, 'hidden' );

	return $html;
}

/**
 * Output hidden fields for a Pods form nonce.
 *
 * @since 3.3.9.2
 *
 * @param string       $pod               The Pod name.
 * @param int|string   $id                The item ID.
 * @param array|string $submitted_fields  The fields array or comma-separated string.
 * @param array|null   $nonce_field_names The hidden nonce field names. Defaults to standard nonce form fields.
 * @param string|null  $uri_hash          The URI hash. Defaults to the current path hash.
 *
 * @return void
 */
function pods_access_output_form_nonce_fields( $pod, $id, $submitted_fields, $nonce_field_names = null, $uri_hash = null ) {
	echo pods_access_get_form_nonce_fields( $pod, $id, $submitted_fields, $nonce_field_names, $uri_hash );  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Verify a Pods form nonce from the request.
 *
 * @since 3.3.9.2
 *
 * @param array|null $nonce_field_names The hidden nonce field names. Defaults to standard nonce form fields.
 * @param string     $source            The request source. Defaults to 'post'.
 *
 * @return bool Whether the nonce is valid.
 */
function pods_access_verify_form_nonce_from_request( $nonce_field_names = null, $source = 'post' ) {
	if ( null === $nonce_field_names ) {
		$nonce_field_names = pods_access_form_field_names( 'form' );
	}

	$nonce = pods_v( $nonce_field_names['nonce'], $source );
	$pod   = pods_v( $nonce_field_names['pod'], $source );
	$id    = pods_v( $nonce_field_names['id'], $source );
	$uri   = pods_v( $nonce_field_names['uri'], $source );
	$form  = pods_v( $nonce_field_names['form'], $source );

	if (
		! is_string( $nonce )
		|| ! is_string( $pod )
		|| ( ! is_string( $id ) && ! is_numeric( $id ) )
		|| ! is_string( $uri )
		|| ! is_string( $form )
		|| '' === $nonce
		|| '' === $pod
		|| '' === $uri
		|| '' === $form
	) {
		return false;
	}

	return pods_access_verify_form_nonce( (string) $nonce, (string) $pod, (int) $id, (string) $form, (string) $uri );
}

/**
 * Determine whether a Pods form nonce is present in the request.
 *
 * This does not verify the nonce value, only whether the nonce field was submitted.
 *
 * @since 3.3.9.2
 *
 * @param string $context   The form context. Accepts 'form' or 'meta'.
 * @param string $group_key Optional group key used to suffix the field names so multiple
 *                          groups on the same page do not collide (defaults to empty).
 * @param string $source    The request source. Defaults to 'post'.
 *
 * @return bool Whether the nonce field is present.
 */
function pods_access_form_nonce_present_in_request( $context = 'form', $group_key = '', $source = 'post' ) {
	$nonce_field_names = pods_access_form_field_names( $context, $group_key );
	$nonce             = pods_v( $nonce_field_names['nonce'], $source );

	return is_string( $nonce );
}
