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
function pods_info_from_args( array $args ): array {
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
function pods_user_can_access_object( array $args, ?int $user_id, string $access_type = 'edit', ?string $context = null ): bool {
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
	if ( $user_id && pods_is_user_admin( $user_id ) ) {
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
function pods_current_user_can_access_object( array $args, string $access_type = 'edit', ?string $context = null ): bool {
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
function pods_access_map_capabilities( array $args, ?int $user_id = null, bool $strict = false ): ?array {
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
function pods_is_type_public( array $args, string $context = 'shortcode' ): bool {
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
function pods_access_bypass_post_with_password( array $args ): bool {
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
function pods_access_bypass_private_post( array $args ): bool {
	$info = pods_info_from_args( $args );

	if ( 'post_type' !== $info['object_type'] || ! $info['item_id'] ) {
		return false;
	}

	$post = get_post( $info['item_id'] );

	if ( ! $post instanceof WP_Post ) {
		return false;
	}

	$bypass_private_post = false;

	if ( ! is_post_publicly_viewable( $post ) ) {
		$can_use_unrestricted = false;

		// Check Pod dynamic features if the status is public.
		if ( is_post_status_viewable( $post->post_status ) ) {
			$can_use_unrestricted = pods_can_use_dynamic_feature_unrestricted( $info, 'display', 'read' );
		}

		if ( $can_use_unrestricted ) {
			$bypass_private_post = false;
		} else {
			$bypass_private_post = ! pods_current_user_can_access_object( $info, 'read' );
		}
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
function pods_can_use_dynamic_features( ?Pod $pod = null ): bool {
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

	// Check if all dynamic features are disabled.
	$dynamic_features_allow = pods_get_setting( 'dynamic_features_allow', '1' );
	$dynamic_features_allow = filter_var( $dynamic_features_allow, FILTER_VALIDATE_BOOLEAN );

	if ( $dynamic_features_allow && $pod instanceof Pod ) {
		// Check if all dynamic features are disabled for the Pod.
		$dynamic_features_allow = $pod->get_arg( 'dynamic_features_allow', 'inherit' );

		if ( 'inherit' === $dynamic_features_allow ) {
			$dynamic_features_allow = pods_is_type_public(
				[
					'pod' => $pod,
				]
			);
		} else {
			$dynamic_features_allow = filter_var( $dynamic_features_allow, FILTER_VALIDATE_BOOLEAN );
		}
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
function pods_can_use_dynamic_feature( string $type ): bool {
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

	$dynamic_features_enabled = (array) pods_get_setting( 'dynamic_features_enabled', [
		'display',
		'form',
	] );
	$dynamic_features_enabled = array_filter( $dynamic_features_enabled );

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
function pods_can_use_dynamic_feature_unrestricted( array $args, string $type, ?string $mode = null ): bool {
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

		// Check if all dynamic features are unrestricted.
		$restrict_dynamic_features = $info['pod']->get_arg( 'restrict_dynamic_features', '1' );
		$restrict_dynamic_features = filter_var( $restrict_dynamic_features, FILTER_VALIDATE_BOOLEAN );

		if ( ! $restrict_dynamic_features ) {
			$can_use_unrestricted = true;
		} elseif ( ! empty( $type ) ) {
			if ( defined( 'PODS_DYNAMIC_FEATURES_RESTRICTED' ) && false !== PODS_DYNAMIC_FEATURES_RESTRICTED ) {
				$constant_restricted_dynamic_features = PODS_DYNAMIC_FEATURES_RESTRICTED;

				if ( ! is_array( $constant_restricted_dynamic_features ) ) {
					$constant_restricted_dynamic_features = explode( ',', $constant_restricted_dynamic_features );
				}

				$restricted_dynamic_features = $constant_restricted_dynamic_features;
			} else {
				$restricted_dynamic_features = (array) $info['pod']->get_arg( 'restricted_dynamic_features', $default_restricted_dynamic_features );
			}

			$restricted_dynamic_features = array_filter( $restricted_dynamic_features );

			if ( empty( $restricted_dynamic_features ) ) {
				$can_use_unrestricted = true;
			} else {
				$can_use_unrestricted = ! in_array( $type, $restricted_dynamic_features, true );
			}

			if ( ! $can_use_unrestricted && 'form' === $type && $mode ) {
				if ( defined( 'PODS_DYNAMIC_FEATURES_RESTRICTED_FORMS' ) && false !== PODS_DYNAMIC_FEATURES_RESTRICTED_FORMS ) {
					$constant_restricted_dynamic_features_forms = PODS_DYNAMIC_FEATURES_RESTRICTED_FORMS;

					if ( ! is_array( $constant_restricted_dynamic_features_forms ) ) {
						$constant_restricted_dynamic_features_forms = explode( ',', $constant_restricted_dynamic_features_forms );
					}

					$restricted_dynamic_features_forms = $constant_restricted_dynamic_features_forms;
				} else {
					$restricted_dynamic_features_forms = (array) $info['pod']->get_arg( 'restricted_dynamic_features_forms', $default_restricted_dynamic_features_forms );
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
function pods_get_access_admin_notice( array $args, bool $force_message = false ): string {
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

	// Check notice setting for the Pod itself.
	if ( $info['pod'] instanceof Pod ) {
		$show_access_admin_notices_for_pod = $info['pod']->get_arg( 'show_access_admin_notices', 'inherit' );

		if ( 'inherit' !== $show_access_admin_notices_for_pod ) {
			$show_access_admin_notices_for_pod = filter_var( $show_access_admin_notices_for_pod, FILTER_VALIDATE_BOOLEAN );

			// Check if all notices have been dismissed for the pod.
			if ( ! $force_message && ! $show_access_admin_notices_for_pod ) {
				return '<!-- pods:access-notices/admin/hidden-by-pod ' . $identifier_for_html . ' -->';
			}
		}
	}

	// Show notice that this content may not be visible to others.
	$show_access_admin_notices = pods_get_setting( 'show_access_admin_notices', true );
	$show_access_admin_notices = filter_var( $show_access_admin_notices, FILTER_VALIDATE_BOOLEAN );

	// Check if all notices have been dismissed.
	if ( ! $force_message && ! $show_access_admin_notices ) {
		return '<!-- pods:access-notices/admin/hidden-by-setting ' . $identifier_for_html . ' -->';
	}

	$summary = esc_html__( 'Pods Access Rights: Admin-only Notice', 'pods' );

	$content = sprintf(
		'
			<p>
				%1$s
				<br />
				<span class="pods-ui-notice-action-links">
					<a href="%2$s" target="_blank" rel="noopener noreferrer">%3$s</a>
					| <a href="%4$s" target="_blank" rel="noopener noreferrer">%5$s</a>
				</span>
			</p>
		',
		esc_html__( 'The content below is not public and may not be available to everyone else.', 'pods' ),
		esc_url( 'https://docs.pods.io/displaying-pods/access-rights-in-pods/' ),
		esc_html__( 'How access rights work with Pods (Documentation)', 'pods' ),
		esc_url( admin_url( 'admin.php?page=pods-settings#heading-security' ) ),
		esc_html__( 'Edit other access right options', 'pods' )
	);

	return '<!-- pods:access-notices/admin/message ' . $identifier_for_html . ' -->'
		. pods_message(
			sprintf(
				'
					<details open>
						<summary><strong>%1$s</strong></summary>
						%2$s
					</details>
				',
				strip_tags( ! empty( $info['summary'] ) ? $info['summary'] : $summary ),
				! empty( $info['content'] ) ? wpautop( $info['content'] ) : $content
			),
			'notice',
			true
		);
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
function pods_get_access_user_notice( array $args, bool $force_message = false, ?string $message = null ): string {
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

		return '<!-- pods:access-notices/user/protected/message ' . $identifier_for_html . ' -->'
		       . pods_message(
			       sprintf(
				       '<p><strong>%1$s</strong></p> %2$s',
				       esc_html__( 'Access Restricted', 'pods' ),
				       $message
			       ),
			       'error',
			       true
		       );
	}

	// Check if constant is hiding all notices.
	if ( ! $force_message && defined( 'PODS_ACCESS_HIDE_NOTICES' ) && PODS_ACCESS_HIDE_NOTICES ) {
		return '<!-- pods:access-notices/user/hidden-by-constant ' . $identifier_for_html . ' -->';
	}

	// Check notice setting for the Pod itself.
	if ( $info['pod'] instanceof Pod ) {
		$show_access_restricted_messages_for_pod = $info['pod']->get_arg( 'show_access_restricted_messages', 'inherit' );

		if ( 'inherit' !== $show_access_restricted_messages_for_pod ) {
			$show_access_restricted_messages_for_pod = filter_var( $show_access_restricted_messages_for_pod, FILTER_VALIDATE_BOOLEAN );

			// Check if all notices have been dismissed for the pod.
			if ( ! $force_message && ! $show_access_restricted_messages_for_pod ) {
				return '<!-- pods:access-notices/user/hidden-by-pod ' . $identifier_for_html . ' -->';
			}
		}
	}

	// Show notice that this content may not be visible to others.
	$show_access_restricted_messages = pods_get_setting( 'show_access_restricted_messages', false );
	$show_access_restricted_messages = filter_var( $show_access_restricted_messages, FILTER_VALIDATE_BOOLEAN );

	// Check if all notices have been dismissed.
	if ( ! $force_message && ! $show_access_restricted_messages ) {
		return '<!-- pods:access-notices/user/hidden-by-setting ' . $identifier_for_html . ' -->';
	}

	$message = $message ?? esc_html__( 'You do not have access to this embedded content.', 'pods' );

	return '<!-- pods:access-notices/user/message ' . $identifier_for_html . ' -->'
		. pods_message(
			sprintf(
				'<p><strong>%1$s:</strong> %2$s</p>',
				esc_html__( 'Access Restricted', 'pods' ),
				$message
			),
			'error',
			true
		);
}

/**
 * Determine whether SQL clauses can be used with dynamic features.
 *
 * @since 3.1.0
 *
 * @param null|string $clause_type The clause type to check if allowed, if null used then it checks if any clauses are allowed.
 *
 * @return bool Whether SQL clauses can be used with dynamic features.
 */
function pods_can_use_dynamic_feature_sql_clauses( ?string $clause_type = null ): bool {
	if ( defined( 'PODS_DISABLE_SHORTCODE_SQL' ) ) {
		// Negate the check since this is a "disable" constant.
		return ! PODS_DISABLE_SHORTCODE_SQL;
	}

	if ( defined( 'PODS_DYNAMIC_FEATURES_ALLOW_SQL_CLAUSES' ) ) {
		$allow_sql_clauses = PODS_DYNAMIC_FEATURES_ALLOW_SQL_CLAUSES;
	} else {
		$first_pods_version = get_option( 'pods_framework_version_first' );
		$first_pods_version = '' === $first_pods_version ? PODS_VERSION : $first_pods_version;

		$allow_sql_clauses = pods_get_setting( 'dynamic_features_allow_sql_clauses', version_compare( $first_pods_version, '3.1.0-a-1', '<' ) ? 'simple' : '0' );
	}

	if (
		false === $allow_sql_clauses
		|| '0' === $allow_sql_clauses
	) {
		return false;
	}

	if ( null === $clause_type ) {
		return true;
	}

	if ( 'simple' === $clause_type && 'all' === $allow_sql_clauses ) {
		return true;
	}

	return $clause_type === $allow_sql_clauses;
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
function pods_access_callback_allowed( $callback, array $params = [] ): bool {
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

		$display_callbacks = pods_get_setting( 'display_callbacks', version_compare( $first_pods_version, '3.1.0-a-1', '<' ) ? 'restricted' : 'customized' );
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
			$display_callbacks_allowed = pods_get_setting( 'display_callbacks_allowed', 'esc_attr,esc_html' );
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
function pods_access_pod_options( string $pod_type, string $pod_name, ?Pod $pod = null ): array {
	$first_pods_version = get_option( 'pods_framework_version_first' );
	$first_pods_version = '' === $first_pods_version ? PODS_VERSION : $first_pods_version;

	$options = [];

	$options['security_access_rights_info'] = [
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
	];

	if ( 'pod' === $pod_type ) {
		$options['public'] = [
			'label'             => __( 'Public', 'pods' ),
			'help'              => __( 'You can still embed Pods Content and Forms through PHP and make use of other features directly through code.', 'pods' ),
			'description'       => __( 'When a content type is public, it can be viewed by anyone when it is embedded through Dynamic Features. Otherwise, a user will need to have the corresponding "read" capability for the content type.', 'pods' ),
			'type'              => 'boolean',
			'default'           => version_compare( $first_pods_version, '3.1.0-a-1', '<' ) ? true : false,
			'boolean_yes_label' => '',
		];
	}

	if ( pods_can_use_dynamic_features() ) {
		$options['dynamic_features_allow'] = [
			'label'              => __( 'Dynamic Features', 'pods' ),
			'help'               => [
				__( 'Enabling Dynamic Features will also enable the additional access rights checks for user access. This ensures that people viewing embedded content and forms have the required capabilties. Even when Dynamic Features are disabled, you can still embed Pods Content and Forms through PHP and make use of other features directly through code.', 'pods' ),
				'https://docs.pods.io/displaying-pods/access-rights-in-pods/',
			],
			'description'        => __( 'Dynamic features include Pods Shortcodes, Blocks, and Widgets which let you embed content and forms on your site.', 'pods' ),
			'type'               => 'pick',
			'default'            => 'inherit',
			'pick_format_type'   => 'single',
			'pick_format_single' => 'radio',
			'data'               => [
				'inherit' => __( 'WP Default - If the content type is marked "Public" with WordPress then Dynamic Features will be enabled.', 'pods' ),
				'1'       => __( 'Enable Dynamic Features including Pods Shortcodes, Blocks, and Widgets for this content type', 'pods' ),
				'0'       => __( 'Disable All Dynamic Features in Pods for this content type', 'pods' ),
			],
			'dependency'         => true,
		];

		$is_public_content_type = pods_is_type_public(
			[
				'pod' => $pod,
			]
		);

		$options['restrict_dynamic_features'] = [
			'label'              => __( 'Restrict Dynamic Features', 'pods' ),
			'help'               => [
				__( 'This will check access rights for whether someone should have access to specific content before a they can view, modify, or interact with that content.', 'pods' ),
				'https://docs.pods.io/displaying-pods/access-rights-in-pods/',
			],
			'description'        => sprintf(
				'<strong>%1$s</strong> %2$s',
				esc_html__( 'Warning:', 'pods' ),
				esc_html__( 'If you have authors/contributors on your site then disabling this would give them access to embedding content/forms without access checks for them or whoever views the embeds on the front of your site. Caution is always advised before giving access to other users you may not trust.', 'pods' )
			),
			'type'               => 'pick',
			'default'            => '1',
			'pick_format_type'   => 'single',
			'pick_format_single' => 'radio',
			'data'               => [
				'0' => __( 'Unrestricted - Do not check for access rights for embedded content (only use this if you trust ALL users who have access to create content)', 'pods' ),
				'1' => __( 'Restricted - Check access rights for embedded content', 'pods' ),
			],
			'excludes-on'        => [ 'dynamic_features_allow' => '0' ],
		];

		$default_restricted_dynamic_features = [
			'form',
		];

		if ( ! $is_public_content_type ) {
			$default_restricted_dynamic_features[] = 'display';
		}

		$options['restricted_dynamic_features'] = [
			'label'             => __( 'Dynamic Features to Restrict', 'pods' ),
			'help'              => [
				__( 'This will check access rights for the dynamic feature for whether someone should have access to specific content before a they can view, modify, or interact with that content.', 'pods' ),
				'https://docs.pods.io/displaying-pods/access-rights-in-pods/',
			],
			'type'              => 'pick',
			'default'           => $default_restricted_dynamic_features,
			'pick_format_type'  => 'multi',
			'pick_format_multi' => 'checkbox',
			'data'              => [
				'display' => __( 'Restricted Display - Shortcodes and Blocks that allow querying content from this Pod and displaying any field will check access rights.', 'pods' ),
				'form'    => __( 'Restricted Forms - The Form Shortcode and Block submitting new content or editing existing content will check access rights.', 'pods' ),
			],
			'depends-on'        => [ 'restrict_dynamic_features' => '1' ],
			'excludes-on'       => [ 'dynamic_features_allow' => '0' ],
		];

		$default_restricted_dynamic_features_forms = [
			'edit',
		];

		if ( ! $is_public_content_type ) {
			$default_restricted_dynamic_features_forms[] = 'add';
		}

		$options['restricted_dynamic_features_forms'] = [
			'label'             => __( 'Dynamic Features to Restrict for Forms', 'pods' ),
			'help'              => [
				__( 'This will check access rights for whether someone should have access to specific content before a they can add or edit content.', 'pods' ),
				'https://docs.pods.io/displaying-pods/access-rights-in-pods/',
			],
			'type'              => 'pick',
			'default'           => $default_restricted_dynamic_features_forms,
			'pick_format_type'  => 'multi',
			'pick_format_multi' => 'checkbox',
			'data'              => [
				'add'  => __( 'Restricted Add New Forms - Embedding the Form Shortcode and Block to allow for adding new content will check access rights.', 'pods' ),
				'edit' => __( 'Restricted Edit Forms - Embedding the Form Shortcode and Block to allow for editing existing content will check access rights.', 'pods' ),
			],
			'depends-on-multi'  => [ 'restricted_dynamic_features' => 'form' ],
			'excludes-on'       => [ 'dynamic_features_allow' => '0' ],
		];

		$options['show_access_restricted_messages'] = [
			'label'              => __( 'Access-related Restricted Messages', 'pods' ),
			'help'               => [
				__( 'Access-related Restricted Messages will show to anyone who does not have access to add/edit/read a specific item from a content type.', 'pods' ),
				'https://docs.pods.io/displaying-pods/access-rights-in-pods/',
			],
			'type'               => 'pick',
			'default'            => 'inherit',
			'pick_format_type'   => 'single',
			'pick_format_single' => 'radio',
			'data'               => [
				'1'       => __( 'Enable access-related restricted messages for forms/content displayed (instead of the form/content output)', 'pods' ),
				'0'       => __( 'Disable access-related restricted messages for forms/content displayed (the form/content output will be blank)', 'pods' ),
				'inherit' => __( 'Default - Use the global Pods setting for this', 'pods' ),
			],
			'depends-on'         => [ 'restrict_dynamic_features' => '1' ],
			'excludes-on'        => [ 'dynamic_features_allow' => '0' ],
		];

		$options['show_access_admin_notices'] = [
			'label'              => __( 'Access-related Admin Notices', 'pods' ),
			'help'               => [
				__( 'Access-related Admin Notices will only show to admins and will appear above content/forms that may not be entirely public.', 'pods' ),
				'https://docs.pods.io/displaying-pods/access-rights-in-pods/',
			],
			'type'               => 'pick',
			'default'            => 'inherit',
			'pick_format_type'   => 'single',
			'pick_format_single' => 'radio',
			'data'               => [
				'1'       => __( 'Enable access-related admin notices above forms/content displayed', 'pods' ),
				'0'       => __( 'Disable access-related admin notices above forms/content displayed', 'pods' ),
				'inherit' => __( 'Default - Use the global Pods setting for this', 'pods' ),
			],
			'depends-on'         => [ 'restrict_dynamic_features' => '1' ],
			'excludes-on'        => [ 'dynamic_features_allow' => '0' ],
		];
	}

	$options['security_access_rights_preview'] = [
		'label'        => __( 'Capabilities preview', 'pods' ),
		'type'         => 'html',
		'html_content' => '
			<p>' . esc_html__( 'Below is a list of capabilities that a user will normally need for this content.' ) . '</p>
		' . pods_access_get_capabilities_preview( $pod_type, $pod_name ),
	];

	return $options;
}

/**
 * Get the list of dynamic features allow options.
 *
 * @since 3.1.0
 *
 * @return array The list of dynamic features allow options.
 */
function pods_access_get_dynamic_features_allow_options(): array {
	return [
		'inherit' => __( 'WP Default (if content type is Public)', 'pods' ),
		'1'       => __( 'Enabled', 'pods' ),
		'0'       => '🔒 ' . __( 'Disabled', 'pods' ),
	];
}

/**
 * Get the list of restricted dynamic features options.
 *
 * @since 3.1.0
 *
 * @return array The list of restricted dynamic features options.
 */
function pods_access_get_restricted_dynamic_features_options(): array {
	return [
		'display' => '🔒 ' . __( 'Display', 'pods' ),
		'form'    => '🔒 ' . __( 'Form', 'pods' ),
	];
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
function pods_access_get_capabilities_preview( string $pod_type, string $pod_name ): string {
	$capabilities = pods_access_map_capabilities(
		[
			'object_type' => $pod_type,
			'object_name' => $pod_name,
		],
		null,
		true
	);

	if ( null === $capabilities ) {
		$capabilities = [
			'read'   => null,
			'add'    => null,
			'edit'   => null,
			'delete' => null,
		];
	}

	$capabilities_preview = [
		'read'             => esc_html__( 'Read capability', 'pods' ),
		'add'              => esc_html__( 'Add New capability', 'pods' ),
		'edit'             => esc_html__( 'Edit capability', 'pods' ),
		'delete'           => esc_html__( 'Delete capability', 'pods' ),
		'read_private'     => esc_html__( 'Read Private capability', 'pods' ),
		'edit_others'      => esc_html__( 'Edit Others capability', 'pods' ),
		'delete_others'    => esc_html__( 'Delete Others capability', 'pods' ),
		'delete_published' => esc_html__( 'Delete Published capability', 'pods' ),
		'delete_private'   => esc_html__( 'Delete Private capability', 'pods' ),
	];

	$capabilities_preview_list = [
		'<strong>' . $capabilities_preview['read'] . ':</strong> ' . ( $capabilities['read'] ?: __( 'Not restricted', 'pods' ) ),
	];

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
function pods_access_settings_config(): array {
	$first_pods_version = get_option( 'pods_framework_version_first' );
	$first_pods_version = '' === $first_pods_version ? PODS_VERSION : $first_pods_version;

	$fields = [];

	$fields['dynamic_features_allow'] = [
		'name'               => 'dynamic_features_allow',
		'label'              => __( 'Dynamic Features', 'pods' ),
		'help'               => [
			__( 'Enabling Dynamic Features will also enable the additional access rights checks for user access. This ensures that people viewing embedded content and forms have the required capabilties. Even when Dynamic Features are disabled, you can still embed Pods Content and Forms through PHP and make use of other features directly through code.', 'pods' ),
			'https://docs.pods.io/displaying-pods/access-rights-in-pods/',
		],
		'description'        => __( 'Dynamic features include Pods Shortcodes, Blocks, and Widgets which let you embed content and forms on your site.', 'pods' ),
		'type'               => 'pick',
		'default'            => '1',
		'pick_format_type'   => 'single',
		'pick_format_single' => 'radio',
		'data'               => [
			'1' => __( 'Enable Dynamic Features including Pods Shortcodes, Blocks, and Widgets', 'pods' ),
			'0' => __( 'Disable All Dynamic Features in Pods', 'pods' ),
		],
		'site_health_data' => [
			'1' => __( 'Enable', 'pods' ),
			'0' => __( 'Disable', 'pods' ),
		],
		'site_health_include_in_info' => true,
	];

	$fields['security_access_rights_info'] = [
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
		'depends-on'         => [ 'dynamic_features_allow' => '1' ],
	];

	$fields['dynamic_features_enabled'] = [
		'name'               => 'dynamic_features_enabled',
		'label'              => __( 'Dynamic Features to Enable', 'pods' ),
		'help'               => [
			__( 'You can choose one or more dynamic features to enable. By default, only Display and Form are enabled.', 'pods' ),
			'https://docs.pods.io/displaying-pods/access-rights-in-pods/',
		],
		'type'               => 'pick',
		'default'            => [
			'display',
			'form',
		],
		'pick_format_type'   => 'multi',
		'pick_format_multi'  => 'checkbox',
		'data'               => [
			'display' => __( 'Display - Shortcodes and Blocks that allow querying content from *any* Pod and displaying any field (WordPress access rights are still checked).', 'pods' ),
			'form'    => __( 'Form - The Form Shortcode and Block that allows submitting new content or editing existing content from *any* Pod (WordPress access rights are still checked).', 'pods' ),
			'view'    => __( 'View - The View Shortcode and Block that allows embedding *any* theme file on a page.', 'pods' ),
		],
		'site_health_data' => [
			'display' => __( 'Display', 'pods' ),
			'form'    => __( 'Form', 'pods' ),
			'view'    => __( 'View', 'pods' ),
		],
		'depends-on'         => [ 'dynamic_features_allow' => '1' ],
		'site_health_include_in_info' => true,
	];

	$fields['show_access_restricted_messages'] = [
		'name'               => 'show_access_restricted_messages',
		'label'              => __( 'Access-related Restricted Messages', 'pods' ),
		'help'               => [
			__( 'Access-related Restricted Messages will show to anyone who does not have access to add/edit/read a specific item from a content type.', 'pods' ),
			'https://docs.pods.io/displaying-pods/access-rights-in-pods/',
		],
		'type'               => 'pick',
		'default'            => '0',
		'pick_format_type'   => 'single',
		'pick_format_single' => 'radio',
		'data'               => [
			'1' => __( 'Enable access-related restricted messages for forms/content displayed (instead of the form/content output)', 'pods' ),
			'0' => __( 'Disable access-related restricted messages for forms/content displayed (the form/content output will be blank)', 'pods' ),
		],
		'site_health_data' => [
			'1' => __( 'Enable', 'pods' ),
			'0' => __( 'Disable', 'pods' ),
		],
		'site_health_include_in_info' => true,
		'depends-on'         => [ 'dynamic_features_allow' => '1' ],
	];

	$fields['show_access_admin_notices'] = [
		'name'               => 'show_access_admin_notices',
		'label'              => __( 'Access-related Admin Notices', 'pods' ),
		'help'               => [
			__( 'Access-related Admin Notices will only show to admins and will appear above content/forms that may not be entirely public.', 'pods' ),
			'https://docs.pods.io/displaying-pods/access-rights-in-pods/',
		],
		'type'               => 'pick',
		'default'            => '1',
		'pick_format_type'   => 'single',
		'pick_format_single' => 'radio',
		'data'               => [
			'1' => __( 'Enable access-related admin notices above forms/content displayed', 'pods' ),
			'0' => __( 'Disable access-related admin notices above forms/content displayed', 'pods' ),
		],
		'site_health_data' => [
			'1' => __( 'Enable', 'pods' ),
			'0' => __( 'Disable', 'pods' ),
		],
		'site_health_include_in_info' => true,
		'depends-on'         => [ 'dynamic_features_allow' => '1' ],
	];

	$fields['dynamic_features_allow_sql_clauses'] = [
		'name'               => 'dynamic_features_allow_sql_clauses',
		'label'              => __( 'Allow SQL clauses to be used in Dynamic Features', 'pods' ),
		'description'        => __( 'SQL clauses in general should only be enabled for sites with trusted users. Since WordPress allows anyone to enter any shortcode or block in the editor, any person with the Contributor role or higher could have access to use this.', 'pods' ),
		'type'               => 'pick',
		'default'            => version_compare( $first_pods_version, '3.1.0-a-1', '<' ) ? 'simple' : '0',
		'pick_format_type'   => 'single',
		'pick_format_single' => 'radio',
		'data'               => [
			'all'    => __( 'Unrestricted - Enable ALL SQL clause usage through dynamic features (only use this if you trust ALL users who have access to create content)', 'pods' ),
			'simple' => __( 'Restricted - Enable Simple SQL clause usage (only SELECT, WHERE, and ORDER BY) through dynamic features (only use this if you trust ALL users who have access to create content)', 'pods' ),
			'0'      => __( 'Disable SQL clause usage through dynamic features', 'pods' ),
		],
		'site_health_data' => [
			'all'    => __( 'Unrestricted', 'pods' ),
			'simple' => __( 'Restricted', 'pods' ),
			'0'      => __( 'Disable', 'pods' ),
		],
		'depends-on'         => [
			'dynamic_features_allow'   => '1',
		],
		'depends-on-multi'     => [
			'dynamic_features_enabled' => 'display',
		],
		'site_health_include_in_info' => true,
	];

	$fields['display_callbacks'] = [
		'name'               => 'display_callbacks',
		'label'              => __( 'Display callbacks', 'pods' ),
		'description'        => __( 'Callbacks can be used when using Pods Templating syntax like {@my_field,my_callback} in your magic tags.', 'pods' ),
		'type'               => 'pick',
		'default'            => version_compare( $first_pods_version, '3.1.0-a-1', '<' ) ? 'restricted' : 'customized',
		'pick_format_type'   => 'single',
		'pick_format_single' => 'radio',
		'data'               => [
			'restricted' => __( 'Restricted - Certain system PHP functions are disallowed from being used for security reasons.', 'pods' ),
			'customized' => __( 'Customized - Only allow a list of specific PHP function callbacks.', 'pods' ),
			'0'          => __( 'Disable display callbacks', 'pods' ),
		],
		'site_health_data' => [
			'restricted' => __( 'Restricted', 'pods' ),
			'customized' => __( 'Customized', 'pods' ),
			'0'          => __( 'Disable', 'pods' ),
		],
		'depends-on'         => [
			'dynamic_features_allow'   => '1',
		],
		'depends-on-multi'     => [
			'dynamic_features_enabled' => 'display',
		],
		'site_health_include_in_info' => true,
	];

	$fields['display_callbacks_allowed'] = [
		'name'               => 'display_callbacks_allowed',
		'label'              => __( 'Display callbacks allowed', 'pods' ),
		'description'        => __( 'Please provide a comma-separated list of PHP function names to allow in callbacks.', 'pods' ),
		'type'               => 'text',
		'default'            => 'esc_attr,esc_html',
		'depends-on'         => [
			'dynamic_features_allow'   => '1',
			'display_callbacks'        => 'customized',
		],
		'depends-on-multi'     => [
			'dynamic_features_enabled' => 'display',
		],
		'site_health_include_in_info' => true,
	];

	return $fields;
}

/**
 * Get the bleep placeholder text.
 *
 * @since 3.1.0
 *
 * @return string The bleep placeholder text.
 */
function pods_access_bleep_placeholder(): string {
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
function pods_access_sql_fragment_is_allowed( string $sql, string $context, array $args = [] ): bool {
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
add_filter( 'pods_access_sql_fragment_is_allowed', 'pods_access_sql_fragment_disallow_post_status', 10, 4 );

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
function pods_access_sql_fragment_disallow_mismatch_parenthesis( bool $allowed, string $sql ): bool {
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
function pods_access_sql_fragment_disallow_unsafe_functions( bool $allowed, string $sql ): bool {
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
function pods_access_sql_fragment_disallow_unsafe_tables( bool $allowed, string $sql ): bool {
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
function pods_access_sql_fragment_disallow_double_hyphens( bool $allowed, string $sql ): bool {
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
function pods_access_sql_fragment_disallow_subqueries( bool $allowed, string $sql ): bool {
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
function pods_access_sql_fragment_disallow_post_status( bool $allowed, string $sql, string $context, array $info ): bool {
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
