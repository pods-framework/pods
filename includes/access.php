<?php
/**
 * @package Pods\Global\Functions\Access
 */

use Pods\Whatsit\Pod;

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
function pods_info_from_args( array $args ) {
	$info = [
		'object_type' => null,
		'object_name' => null,
		'item_id'     => null,
		'pods'        => null,
		'pod'         => null,
	];

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
		&& in_array( $info['object_type'], [ 'comment', 'media', 'user' ], true )
	) {
		$info['object_name'] = $info['object_type'];

		$object_name_set = true;
	}

	// Normalize the Pods info to null if it's not valid.
	if (
		$info['pods'] instanceof Pods
		&& ! $info['pods']->is_valid()
	) {
		$info['pods'] = null;
	}

	// Maybe build the Pods object from the info.
	if (
		$build_pods
		&& $object_name_set
		&& ! $info['pods'] instanceof Pods
	) {
		$pods = pods_get_instance( $info['object_name'], $info['item_id'], true );

		if (
			$pods instanceof Pods
			&& $pods->is_valid()
			&& (
				empty( $info['object_type'] )
				|| $info['object_type'] === $pods->pod_data->get_type()
			)
		) {
			$info['pods'] = $pods;

			if ( ! $info['pod'] instanceof Pod ) {
				$info['pod'] = clone $pods->pod_data;
			}
		}
	} elseif (
		$info['pods'] instanceof Pods
		&& $info['pods']->is_valid()
		&& ! $info['pod'] instanceof Pod
	) {
		$info['pod'] = clone $info['pods']->pod_data;
	}

	// Maybe build the Pod object from the info.
	if (
		$build_pod
		&& $object_name_set
		&& ! $info['pod'] instanceof Pod
	) {
		try {
			$pod = pods_api()->load_pod( [
				'name' => $info['object_name'],
			] );
		} catch ( Exception $e ) {
			$pod = null;
		}

		if (
			$pod instanceof Pod
			&& (
				empty( $info['object_type'] )
				|| $info['object_type'] === $pod->get_type()
			)
		) {
			$info['pod'] = $pod;
		}
	}

	if ( $info['pod'] instanceof Pod ) {
		$info['object_type'] = $info['pod']->get_type();
		$info['object_name'] = $info['pod']->get_name();
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
function pods_user_can_access_object( array $args, $user_id, $access_type = 'edit', $context = null ) {
	$info = pods_info_from_args( $args );

	if ( null === $user_id ) {
		$user_id = 0;
	}

	// Check if the user exists.
	$user = get_userdata( $user_id );

	if ( ! $user || is_wp_error( $user ) ) {
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
		if ( $user->has_cap('pods_content' ) ) {
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
function pods_current_user_can_access_object( array $args, $access_type = 'edit', $context = null ): bool {
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
function pods_access_map_capabilities( array $args, $user_id = null, $strict = false ) {
	$args['build_pods'] = true;
	$args['build_pod']  = true;

	$info = pods_info_from_args( $args );

	// If no object type or name, we cannot check access.
	if ( empty( $info['object_type'] ) || empty( $info['object_name'] ) ) {
		return null;
	}

	$wp_object = null;

	$capabilities = [];

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
		$wp_object = (object) [
			'public' => false,
			'cap'    => (object) [],
		];
	} elseif ( 'media' === $info['object_type'] ) {
		$info['item_id'] = (int) $info['item_id'];

		$capabilities['read']   = 'read';
		$capabilities['add']    = 'upload_files';
		$capabilities['edit']   = 'upload_files';
		$capabilities['delete'] = 'upload_files';

		// Fake the WP object for the logic below.
		$wp_object = (object) [
			'public' => false,
			'cap'    => (object) [],
		];
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
		$wp_object = (object) [
			'public' => true,
			'cap'    => (object) [],
		];
	} elseif ( 'settings' === $info['object_type'] ) {
		$capabilities['read']   = 'manage_options';
		$capabilities['edit']   = 'manage_options';
		$capabilities['delete'] = 'manage_options';

		// Fake the WP object for the logic below.
		$wp_object = (object) [
			'public' => false,
			'cap'    => (object) [],
		];
	} elseif ( 'pod' === $info['object_type'] || 'table' === $info['object_type'] ) {
		$info['item_id'] = (int) $info['item_id'];

		$capabilities['read']   = 'pods_read_' . $info['object_name'];
		$capabilities['add']    = 'pods_add_' . $info['object_name'];
		$capabilities['edit']   = 'pods_edit_' . $info['object_name'];
		$capabilities['delete'] = 'pods_delete_' . $info['object_name'];
		$capabilities['edit_others']   = 'pods_edit_others_' . $info['object_name'];
		$capabilities['delete_others'] = 'pods_delete_others_' . $info['object_name'];

		$is_public = false;

		if ( $info['pods'] instanceof Pods && $info['pod'] instanceof Pod ) {
			// If an object ID is provided, check for access for that specific item.
			if ( $info['item_id'] && $info['pods']->exists() ) {
				// Check for author field.
				$author_field = $info['pod']->get_field( 'author' );

				$author_user_id = $author_field ? (int) $info['pods']->field( $author_field->get_name() . '.ID' ) : null;

				// If we have an author field, check if they are the author.
				if ( $author_field ) {
					if ( $user_id && $author_user_id === $user_id ) {
						// This is their own post, they can also have access if have edit access.
						$capabilities['read'] = [
							$capabilities['read'],
							'pods_edit_' . $info['object_name'],
						];
					} else {
						// This is not their post, check if they have access to others.
						$capabilities['edit']   = 'pods_edit_others_' . $info['object_name'];
						$capabilities['delete'] = 'pods_delete_others_' . $info['object_name'];
					}
				}
			}

			$is_public = $info['pod']->get_arg( 'public', '0', true );
			$is_public = filter_var( $is_public, FILTER_VALIDATE_BOOLEAN );

			// Fake the WP object for the logic below.
			$wp_object = (object) [
				'public' => $is_public,
				'cap'    => (object) [],
			];
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
function pods_is_type_public( array $args, $context = 'shortcode' ) {
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
		$info['pod'] instanceof Pod
		&& (
			$is_post_type
			|| $is_taxonomy
			|| $is_pod
			|| $is_settings_pod
		)
	) {
		$is_extended = $info['pod']->is_extended();

		if ( ! $is_extended ) {
			$is_public = $info['pod']->get_arg( 'public', null, true );

			if ( null !== $is_public ) {
				$pod_has_public = true;

				$is_public = filter_var( $is_public, FILTER_VALIDATE_BOOLEAN );

				if ( $is_post_type || $is_taxonomy ) {
					$is_public = $is_public && 1 === (int) $info['pod']->get_arg( 'publicly_queryable', $is_public, true );
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
	 * @param Pod|null    $pod         The Pod object if set.
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
function pods_access_bypass_post_with_password( array $args ) {
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
function pods_access_bypass_private_post( array $args ) {
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
	if ( defined( 'PODS_DYNAMIC_FEATURES_ALLOW' ) ) {
		return PODS_DYNAMIC_FEATURES_ALLOW;
	}

	$can_use_dynamic_features = apply_filters( 'pods_access_can_use_dynamic_features', null, $pod );

	if ( is_bool( $can_use_dynamic_features ) ) {
		return $can_use_dynamic_features;
	}

	$dynamic_features_allow = true;

	if ( $pod instanceof Pod ) {
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

	$dynamic_features_enabled = [
		'display',
		'form',
	];

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
 *
 * @param string $type The dynamic feature type.
 * @param string $mode The dynamic feature mode (like "add" or "edit" for the form feature).
 *
 * @return bool Whether specific dynamic feature is unrestricted.
 */
function pods_can_use_dynamic_feature_unrestricted( array $args, $type, $mode = null ) {
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

		$default_restricted_dynamic_features = [
			'form',
		];

		if ( ! $is_public_content_type ) {
			$default_restricted_dynamic_features[] = 'display';
		}

		$default_restricted_dynamic_features_forms = [
			'edit',
		];

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
 *
 * @param bool  $force_message Whether to force the message to show even if messages are hidden by a setting.
 *
 * @return string The access notice for admin user based on object type and object name.
 */
function pods_get_access_admin_notice( array $args, $force_message = false ) {
	$args['build_pod'] = true;

	$info = pods_info_from_args( $args );

	$identifier_for_html = esc_html( json_encode( [
		'object_type' => $info['object_type'],
		'object_name' => $info['object_name'],
		'item_id'     => $info['item_id'],
	] ) );

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
function pods_get_access_user_notice( array $args, $force_message = false, $message = null ) {
	$args['build_pod'] = true;

	$info = pods_info_from_args( $args );

	$identifier_for_html = esc_html( json_encode( [
		'object_type' => $info['object_type'],
		'object_name' => $info['object_name'],
		'item_id'     => $info['item_id'],
	] ) );

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
 * @since 3.1.0
 *
 * @param string|callable $callback The callback to check.
 * @param array           $params   Parameters used by Pods::helper() method.
 *
 * @return bool Whether the callback can be used.
 */
function pods_access_callback_allowed( $callback, array $params = [] ) {
	// Real callables are allowed because they are done through PHP calls.
	if ( ! is_string( $callback ) ) {
		return true;
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

	$disallowed = [
		// Regex related.
		'preg_replace',
		'preg_replace_array',
		'preg_replace_callback',
		'preg_replace_callback_array',
		'preg_match',
		'preg_match_all',
		// Shell/Eval related.
		'system',
		'exec',
		'passthru',
		'proc_close',
		'proc_get_status',
		'proc_nice',
		'proc_open',
		'proc_terminate',
		'shell_exec',
		'system',
		'eval',
		'create_function',
		// File related.
		'popen',
		'include',
		'include_once',
		'require',
		'require_once',
		'file_get_contents',
		'file_put_contents',
		'get_template_part',
		// Nonce related.
		'wp_nonce_url',
		'wp_nonce_field',
		'wp_create_nonce',
		'check_admin_referer',
		'check_ajax_referer',
		'wp_verify_nonce',
		// PHP related.
		'constant',
		'defined',
		'get_current_user',
		'get_defined_constants',
		'get_defined_functions',
		'get_defined_vars',
		'get_extension_funcs',
		'get_include_path',
		'get_included_files',
		'get_loaded_extensions',
		'get_required_files',
		'get_resources',
		'getenv',
		'getopt',
		'ini_alter',
		'ini_get',
		'ini_get_all',
		'ini_restore',
		'ini_set',
		'php_ini_loaded_file',
		'php_ini_scanned_files',
		'php_sapi_name',
		'php_uname',
		'phpinfo',
		'phpversion',
		'putenv',
		// WordPress related.
		'get_userdata',
		'get_currentuserinfo',
		'get_post',
		'get_term',
		'get_comment',
	];

	$allowed = [];

	if ( defined( 'PODS_DISPLAY_CALLBACKS' ) ) {
		$display_callbacks = PODS_DISPLAY_CALLBACKS;
	} else {
		$first_pods_version = get_option( 'pods_framework_version_first' );
		$first_pods_version = '' === $first_pods_version ? PODS_VERSION : $first_pods_version;

		$display_callbacks = 'restricted';
	}

	if ( '0' === $display_callbacks ) {
		return false;
	}

	// Maybe specify the list of allowed callbacks.
	if ( 'customized' === $display_callbacks ) {
		if ( defined( 'PODS_DISPLAY_CALLBACKS_ALLOWED' ) ) {
			$display_callbacks_allowed = PODS_DISPLAY_CALLBACKS_ALLOWED;
		} else {
			// Maybe specify the list of allowed callbacks
			$display_callbacks_allowed = 'esc_attr,esc_html';
		}

		if ( ! is_array( $display_callbacks_allowed ) ) {
			$display_callbacks_allowed = str_replace( "\n", ',', $display_callbacks_allowed );
			$display_callbacks_allowed = explode( ',', $display_callbacks_allowed );
		}

		$display_callbacks_allowed = array_map( 'trim', $display_callbacks_allowed );
		$display_callbacks_allowed = array_filter( $display_callbacks_allowed );

		if ( ! empty( $display_callbacks_allowed ) ) {
			$allowed = $display_callbacks_allowed;
		}
	}

	/**
	 * Allows adjusting the disallowed callbacks as needed.
	 *
	 * @param array $disallowed List of callbacks not allowed.
	 * @param array $params     Parameters used by Pods::helper() method.
	 *
	 * @since 2.7.0
	 */
	$disallowed = apply_filters( 'pods_helper_disallowed_callbacks', $disallowed, $params );

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
		$callback = strip_tags( str_replace( array( '`', chr( 96 ) ), "'", $callback ) );
	}

	return (
		! in_array( $callback, $disallowed, true )
		&& (
			empty( $allowed )
			|| in_array( $callback, $allowed, true )
		)
	);
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
function pods_access_bleep_data( $data, array $additional_bleep_properties = [] ) {
	$bleep_properties = [
		'user_pass',
		'user_activation_key',
		'post_password',
	];

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
function pods_access_bleep_items( array $items, array $additional_bleep_properties = [] ) {
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
 * @return bool Whether the SQL fragment is allowed to be used.
 */
function pods_access_sql_fragment_is_allowed( $sql, $context, array $args = [] ) {
	$context = strtoupper( $context );

	$info = pods_info_from_args( $args );

	/**
	 * Allows filtering whether the SQL fragment is allowed to be used.
	 *
	 * @since 3.1.0
	 *
	 * @param bool   $allowed Whether the SQL fragment is allowed to be used.
	 * @param string $sql     The SQL fragment to check.
	 * @param string $context The SQL fragment context.
	 * @param array  $info    Pod information.
	 */
	return (bool) apply_filters( 'pods_access_sql_fragment_is_allowed', true, $sql, $context, $info );
}

add_filter( 'pods_access_sql_fragment_is_allowed', 'pods_access_sql_fragment_disallow_mismatch_parenthesis', 10, 2 );
add_filter( 'pods_access_sql_fragment_is_allowed', 'pods_access_sql_fragment_disallow_unsafe_functions', 10, 2 );
add_filter( 'pods_access_sql_fragment_is_allowed', 'pods_access_sql_fragment_disallow_unsafe_tables', 10, 2 );
add_filter( 'pods_access_sql_fragment_is_allowed', 'pods_access_sql_fragment_disallow_double_hyphens', 10, 2 );
add_filter( 'pods_access_sql_fragment_is_allowed', 'pods_access_sql_fragment_disallow_subqueries', 10, 2 );
//add_filter( 'pods_access_sql_fragment_is_allowed', 'pods_access_sql_fragment_disallow_post_status', 10, 4 );

/**
 * Disallow mismatched parenthesis from being used in SQL fragments.
 *
 * @since 3.1.0
 *
 * @param bool   $allowed Whether the SQL fragment is allowed to be used.
 * @param string $sql     The SQL fragment to check.
 *
 * @return bool Whether the SQL fragment is allowed to be used.
 */
function pods_access_sql_fragment_disallow_mismatch_parenthesis( $allowed, $sql ) {
	return (
		$allowed
		&& substr_count( $sql, '(' ) === substr_count( $sql, ')' )
	);
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

	$unsafe_functions = [
		'USER',
		'DATABASE',
		'VERSION',
		'FROM_BASE64',
		'TO_BASE64',
		'SLEEP',
		'WAIT_FOR_EXECUTED_GTID_SET',
		'WAIT_UNTIL_SQL_THREAD_AFTER_GTIDS',
		'MASTER_POS_WAIT',
		'SOURCE_POS_WAIT',
		'LOAD_FILE',
	];

	/**
	 * Allow filtering the list of unsafe functions to disallow.
	 *
	 * @since 3.1.0
	 *
	 * @param array  $unsafe_functions The list of unsafe functions to disallow.
	 * @param string $sql              The SQL fragment to check.
	 */
	$unsafe_functions = (array) apply_filters( 'pods_access_sql_fragment_disallow_unsafe_functions', $unsafe_functions, $sql );

	$unsafe_functions = array_filter( $unsafe_functions );

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

	$unsafe_tables = [
		'mysql.',
		'information_schema.',
		'performance_schema.',
		'sys.',
	];

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

	foreach ( $unsafe_tables as $unsafe_table ) {
		if ( 1 === (int) preg_match( '/\s*' . preg_quote( $unsafe_table, '/' ) . '/i', $sql ) ) {
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
 * Disallow post_status from being used in the WHERE/HAVING SQL fragment unless they have admin access.
 *
 * @since 3.1.0
 *
 * @param bool   $allowed Whether the SQL fragment is allowed to be used.
 * @param string $sql     The SQL fragment to check.
 * @param string $context The SQL fragment context.
 * @param array  $info    Pod information.
 *
 * @return bool Whether the SQL fragment is allowed to be used.
 */
function pods_access_sql_fragment_disallow_post_status( $allowed, $sql, $context, array $info ) {
	if ( 'WHERE' !== $context && 'HAVING' !== $context ) {
		return $allowed;
	}

	return (
		$allowed
		&& (
			false === stripos( $sql, 'post_status' )
			|| pods_is_admin( 'edit_posts' )
		)
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
		// Fall back to normal WP function.
		return maybe_unserialize( $data );
	}

	// Check if the data is serialized.
	if ( is_serialized( $data ) ) {
		$data = trim( $data );

		// Unserialize the data but exclude classes.
		return @unserialize( $data, [ 'allowed_classes' => false ] );
	}

	return $data;
}
