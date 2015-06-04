<?php
/**
 * Name: Roles and Capabilities
 *
 * Menu Name: Roles &amp; Capabilities
 *
 * Description: Create and Manage WordPress User Roles and Capabilities; Uses the '<a href="http://wordpress.org/plugins/members/" target="_blank">Members</a>' plugin filters for additional plugin integrations; Portions of code based on the '<a href="http://wordpress.org/plugins/members/" target="_blank">Members</a>' plugin by Justin Tadlock
 *
 * Version: 1.0
 *
 * Category: Tools
 *
 * @package    Pods
 * @category   Components
 * @subpackage Roles
 */

if ( class_exists( 'Pods_Roles' ) ) {
	return;
}

class Pods_Roles extends Pods_Component {

	/**
	 * {@inheritdoc}
	 */
	public function __construct() {

		add_filter( 'pods_roles_get_capabilities', array( $this, 'remove_deprecated_capabilities' ) );

	}

	/**
	 * Enqueue styles
	 *
	 * @since 2.0
	 */
	public function admin_assets() {

		wp_enqueue_style( 'pods-wizard' );

	}

	/**
	 * Build admin area
	 *
	 * @param $options
	 * @param $component
	 *
	 * @return void
	 * @since 2.0
	 */
	public function admin( $options, $component ) {

		global $wp_roles;

		// Hook into Gravity Forms roles (since it only adds filter if Members plugin itself is activated
		if ( class_exists( 'RGForms' ) && ! has_filter( 'members_get_capabilities', array(
					'RGForms',
					'members_get_capabilities'
				) )
		) {
			add_filter( 'members_get_capabilities', array( 'RGForms', 'members_get_capabilities' ) );
		}

		$default_role = get_option( 'default_role' );

		$roles = array();

		foreach ( $wp_roles->role_objects as $key => $role ) {
			$count = $this->count_users( $key );

			$roles[ $key ] = array(
				'id'           => $key,
				'label'        => $wp_roles->role_names[ $key ],
				'name'         => $key,
				'capabilities' => count( (array) $role->capabilities ),
				'users'        => sprintf( _n( '%s User', '%s Users', $count, 'pods' ), $count )
			);

			if ( $default_role == $key ) {
				$roles[ $key ][ 'label' ] .= ' (site default)';
			}

			if ( 0 < $count && pods_is_admin( array( 'list_users' ) ) ) {
				$roles[ $key ][ 'users' ] .= '<br /><a href="' . admin_url( esc_url( 'users.php?role=' . $key ) ) . '">' . __( 'View Users', 'pods' ) . '</a>';
			}
		}

		$ui = array(
			'component'        => $component,
			'data'             => $roles,
			'total'            => count( $roles ),
			'total_found'      => count( $roles ),
			'icon'             => PODS_URL . 'ui/images/icon32.png',
			'items'            => 'Roles',
			'item'             => 'Role',
			'fields'           => array(
				'manage' => array(
					'label'        => array( 'label' => __( 'Label', 'pods' ) ),
					'name'         => array( 'label' => __( 'Name', 'pods' ) ),
					'capabilities' => array( 'label' => __( 'Capabilities', 'pods' ) ),
					'users'        => array(
						'label'   => __( 'Users', 'pods' ),
						'type'    => 'text',
						'options' => array(
							'text_allow_html'        => 1,
							'text_allowed_html_tags' => ''
						)
					)
				)
			),
			'actions_disabled' => array( 'duplicate', 'view', 'export' ),
			'actions_custom'   => array(
				'add'    => array( $this, 'admin_add' ),
				'edit'   => array( $this, 'admin_edit' ),
				'delete' => array( $this, 'admin_delete' )
			),
			'search'           => false,
			'searchable'       => false,
			'sortable'         => false,
			'pagination'       => false
		);

		if ( isset( $roles[ pods_v( 'id', 'get', - 1 ) ] ) ) {
			$ui[ 'row' ] = $roles[ pods_v( 'id', 'get', - 1 ) ];
		}

		if ( ! pods_is_admin( array( 'pods_roles_add' ) ) ) {
			$ui[ 'actions_disabled' ][] = 'add';
		}

		if ( ! pods_is_admin( array( 'pods_roles_edit' ) ) ) {
			$ui[ 'actions_disabled' ][] = 'edit';
		}

		if ( count( $roles ) < 2 || ! pods_is_admin( array( 'pods_roles_delete' ) ) ) {
			$ui[ 'actions_disabled' ][] = 'delete';
		}

		pods_ui( $ui );

	}

	/**
	 * @param $obj
	 */
	function admin_add( $obj ) {

		global $wp_roles;

		$capabilities = $this->get_capabilities();

		$defaults = $this->get_default_capabilities();

		$component = $obj->x[ 'component' ];

		$method = 'add'; // ajax_add

		pods_view( PODS_DIR . 'components/Roles/ui/add.php', compact( array_keys( get_defined_vars() ) ) );

	}

	/**
	 * @param $duplicate
	 * @param $obj
	 */
	function admin_edit( $duplicate, $obj ) {

		global $wp_roles;

		$id = $obj->id;

		$grouped_capabilities = $this->get_grouped_capabilities();

		$role_name = $role_label = $role_capabilities = null;

		foreach ( $wp_roles->role_objects as $key => $role ) {
			if ( $key != $id ) {
				continue;
			}

			$role_name = $key;
			$role_label = $wp_roles->role_names[ $key ];
			$role_capabilities = $role->capabilities;
		}

		if ( empty( $role ) ) {
			return $obj->error( __( 'Role not found, cannot edit it.', 'pods' ) );
		}

		$component = $obj->x[ 'component' ];

		$method = 'edit'; // ajax_edit

		pods_view( PODS_DIR . 'components/Roles/ui/edit.php', compact( array_keys( get_defined_vars() ) ) );

	}

	/**
	 * @param $id
	 * @param $obj
	 */
	function admin_delete( $id, $obj ) {

		global $wp_roles;

		$id = $obj->id;

		if ( ! isset( $obj->data[ $id ] ) ) {
			return $obj->error( __( 'Role not found, it cannot be deleted.', 'pods' ) );
		}

		$default_role = get_option( 'default_role' );

		if ( $id == $default_role ) {
			return $obj->error( sprintf( __( 'You cannot remove the <strong>%s</strong> role, you must set a new default role for the site first.', 'pods' ), $obj->data[ $id ][ 'name' ] ) );
		}

		$wp_user_search = new WP_User_Search( '', '', $id );

		$users = $wp_user_search->get_results();

		if ( ! empty( $users ) && is_array( $users ) ) {
			foreach ( $users as $user ) {
				$user_object = new WP_User( $user );

				if ( $user_object->has_cap( $id ) ) {
					$user_object->remove_role( $id );
					$user_object->set_role( $default_role );
				}
			}
		}

		remove_role( $id );

		$roles = array();

		foreach ( $wp_roles->role_objects as $key => $role ) {
			$count = $this->count_users( $key );

			$roles[ $key ] = array(
				'id'           => $key,
				'label'        => $wp_roles->role_names[ $key ],
				'name'         => $key,
				'capabilities' => count( (array) $role->capabilities ),
				'users'        => sprintf( _n( '%s User', '%s Users', $count, 'pods' ), $count )
			);

			if ( $default_role == $key ) {
				$roles[ $key ][ 'label' ] .= ' (site default)';
			}

			if ( 0 < $count && pods_is_admin( array( 'list_users' ) ) ) {
				$roles[ $key ][ 'users' ] .= '<br /><a href="' . admin_url( esc_url( 'users.php?role=' . $key ) ) . '">' . __( 'View Users', 'pods' ) . '</a>';
			}
		}

		$name = $obj->data[ $id ][ 'label' ] . ' (' . $obj->data[ $id ][ 'name' ] . ')';

		$obj->data = $roles;
		$obj->total = count( $roles );
		$obj->total_found = count( $roles );

		$obj->message( '<strong>' . $name . '</strong> ' . __( 'role removed from site.', 'pods' ) );

	}

	/**
	 * Handle the Add Role AJAX
	 *
	 * @param $params
	 *
	 * @return mixed|void
	 */
	public function ajax_add( $params ) {

		global $wp_roles;

		$role_name = pods_v( 'role_name', $params );
		$role_label = pods_v( 'role_label', $params );

		$params->capabilities = (array) pods_var_raw( 'capabilities', $params, array() );

		$params->custom_capabilities = (array) pods_var_raw( 'custom_capabilities', $params, array() );
		$params->custom_capabilities = array_filter( array_unique( $params->custom_capabilities ) );

		$capabilities = array();

		foreach ( $params->capabilities as $capability => $x ) {
			if ( empty( $capability ) || true !== (boolean) $x ) {
				continue;
			}

			$capabilities[ esc_attr( $capability ) ] = true;
		}

		foreach ( $params->custom_capabilities as $x => $capability ) {
			if ( empty( $capability ) || '--1' == $x ) {
				continue;
			}

			$capabilities[ esc_attr( $capability ) ] = true;
		}

		if ( empty( $role_name ) ) {
			return pods_error( __( 'Role name is required', 'pods' ) );
		}

		if ( empty( $role_label ) ) {
			return pods_error( __( 'Role label is required', 'pods' ) );
		}

		return add_role( $role_name, $role_label, $capabilities );

	}

	/**
	 * Handle the Edit Role AJAX
	 *
	 * @todo allow rename role_label
	 *
	 * @param $params
	 *
	 * @return bool|mixed|void
	 */
	public function ajax_edit( $params ) {

		global $wp_roles;

		$capabilities = $this->get_capabilities();

		$params->capabilities = (array) pods_var_raw( 'capabilities', $params, array() );

		$params->custom_capabilities = (array) pods_var_raw( 'custom_capabilities', $params, array() );
		$params->custom_capabilities = array_filter( array_unique( $params->custom_capabilities ) );

		if ( ! isset( $params->id ) || empty( $params->id ) || ! isset( $wp_roles->role_objects[ $params->id ] ) ) {
			return pods_error( __( 'Role not found, cannot edit it.', 'pods' ) );
		}

		/**
		 * @var $role WP_Role
		 */
		$role = $wp_roles->role_objects[ $params->id ];
		$role_name = $params->id;
		$role_label = $wp_roles->role_names[ $params->id ];
		$role_capabilities = $role->capabilities;

		$new_capabilities = array();

		foreach ( $params->capabilities as $capability => $x ) {
			if ( empty( $capability ) || true !== (boolean) $x ) {
				continue;
			}

			$new_capabilities[] = esc_attr( $capability );

			if ( ! $role->has_cap( $capability ) ) {
				$role->add_cap( $capability );
			}
		}

		foreach ( $params->custom_capabilities as $x => $capability ) {
			if ( empty( $capability ) ) {
				continue;
			}

			if ( in_array( $capability, $new_capabilities ) ) {
				continue;
			}

			$new_capabilities[] = esc_attr( $capability );

			if ( ! $role->has_cap( $capability ) ) {
				$role->add_cap( $capability );
			}
		}

		foreach ( $role_capabilities as $capability => $x ) {
			if ( ! in_array( $capability, $new_capabilities ) && false === strpos( $capability, 'level_' ) ) {
				$role->remove_cap( $capability );
			}
		}

		return true;

	}

	/**
	 * Basic logic from Members plugin, it counts users of a specific role
	 *
	 * @param $role
	 *
	 * @return array
	 */
	function count_users( $role ) {

		$count_users = count_users();

		$avail_roles = array();

		foreach ( $count_users[ 'avail_roles' ] as $count_role => $count ) {
			$avail_roles[ $count_role ] = $count;
		}

		if ( empty( $role ) ) {
			return $avail_roles;
		}

		if ( ! isset( $avail_roles[ $role ] ) ) {
			$avail_roles[ $role ] = 0;
		}

		return $avail_roles[ $role ];

	}

	function get_capabilities() {

		global $wp_roles;

		$default_caps = $this->get_wp_capabilities();

		$role_caps = array();

		foreach ( $wp_roles->role_objects as $key => $role ) {
			if ( is_array( $role->capabilities ) ) {
				foreach ( $role->capabilities as $cap => $grant ) {
					$role_caps[ $cap ] = $cap;
				}
			}
		}

		$role_caps = array_unique( $role_caps );

		$plugin_caps = array(
			'pods_roles_add',
			'pods_roles_delete',
			'pods_roles_edit'
		);

		$capabilities = array_merge( $default_caps, $role_caps, $plugin_caps );

		// To support Members filters
		$capabilities = apply_filters( 'members_get_capabilities', $capabilities );

		$capabilities = apply_filters( 'pods_roles_get_capabilities', $capabilities );

		sort( $capabilities );

		$capabilities = array_unique( $capabilities );

		return $capabilities;

	}

	function get_capability_group_map() {

		$defaults_capability_group = array(
			'activate_plugins'       => 'plugins',
			'add_users'              => 'users',
			'create_users'           => 'users',
			'delete_others_pages'    => 'pages',
			'delete_others_posts'    => 'posts',
			'delete_pages'           => 'pages',
			'delete_plugins'         => 'plugins',
			'delete_posts'           => 'posts',
			'delete_private_pages'   => 'pages',
			'delete_private_posts'   => 'posts',
			'delete_published_pages' => 'pages',
			'delete_published_posts' => 'posts',
			'delete_themes'          => 'themes',
			'delete_users'           => 'users',
			'edit_dashboard'         => 'other',
			'edit_files'             => 'files',
			'edit_others_pages'      => 'pages',
			'edit_others_posts'      => 'posts',
			'edit_pages'             => 'pages',
			'edit_plugins'           => 'plugins',
			'edit_posts'             => 'posts',
			'edit_private_pages'     => 'pages',
			'edit_private_posts'     => 'posts',
			'edit_published_pages'   => 'pages',
			'edit_published_posts'   => 'posts',
			'edit_theme_options'     => 'themes',
			'edit_themes'            => 'themes',
			'edit_users'             => 'users',
			'import'                 => 'other',
			'install_plugins'        => 'plugins',
			'install_themes'         => 'themes',
			'list_users'             => 'users',
			'manage_categories'      => 'other',
			'manage_links'           => 'other',
			'manage_options'         => 'other',
			'moderate_comments'      => 'other',
			'promote_users'          => 'users',
			'publish_pages'          => 'pages',
			'publish_posts'          => 'posts',
			'read'                   => 'other',
			'read_private_pages'     => 'pages',
			'read_private_posts'     => 'posts',
			'remove_users'           => 'users',
			'switch_themes'          => 'themes',
			'unfiltered_html'        => 'other',
			'unfiltered_upload'      => 'other',
			'update_core'            => 'other',
			'update_plugins'         => 'plugins',
			'update_themes'          => 'themes',
			'upload_files'           => 'files'
		);

		$pods_roles_capability_group = array(
			'pods_roles_add'    => 'pods',
			'pods_roles_delete' => 'pods',
			'pods_roles_edit'   => 'pods'
		);

		$pods_components = pods_components();
		$pods_components_capability_group = $pods_components->admin_capabilities( array() );
		$pods_components_capability_group = array_keys( array_flip( $pods_components_capability_group ) );
		$pods_components_capability_group = array_fill_keys( $pods_components_capability_group, 'pods' );

		$pods_capability_group = array();

		$pods = pods_api()->load_pods( array(
				'type' => array(
					'pod',
					'table',
					'post_type',
					'taxonomy',
					'settings'
				)
			) );

		$pods_capability_group[ 'pods' ] = 'pods';
		$pods_capability_group[ 'pods_content' ] = 'pods';
		$pods_capability_group[ 'pods_settings' ] = 'pods';
		$pods_capability_group[ 'pods_components' ] = 'pods';

		foreach ( $pods as $pod ) {
			if ( 'settings' == $pod[ 'type' ] ) {
				$pods_capability_group[ 'pods_edit_' . $pod[ 'name' ] ] = $pod[ 'name' ];
			} elseif ( 'post_type' == $pod[ 'type' ] ) {
				$capability_type = pods_var( 'capability_type_custom', $pod, pods_var_raw( 'name', $pod ) );

				if ( 'custom' == pods_var( 'capability_type', $pod ) && 0 < strlen( $capability_type ) ) {
					$pods_capability_group[ 'read_' . $capability_type ] = $pod[ 'name' ];
					$pods_capability_group[ 'edit_' . $capability_type ] = $pod[ 'name' ];
					$pods_capability_group[ 'delete_' . $capability_type ] = $pod[ 'name' ];

					if ( 1 == pods_var( 'capability_type_extra', $pod, 1 ) ) {
						$pods_capability_group[ 'read_private_' . $capability_type . 's' ] = $pod[ 'name' ];
						$pods_capability_group[ 'edit_' . $capability_type . 's' ] = $pod[ 'name' ];
						$pods_capability_group[ 'edit_others_' . $capability_type . 's' ] = $pod[ 'name' ];
						$pods_capability_group[ 'edit_private_' . $capability_type . 's' ] = $pod[ 'name' ];
						$pods_capability_group[ 'edit_published_' . $capability_type . 's' ] = $pod[ 'name' ];
						$pods_capability_group[ 'publish_' . $capability_type . 's' ] = $pod[ 'name' ];
						$pods_capability_group[ 'delete_' . $capability_type . 's' ] = $pod[ 'name' ];
						$pods_capability_group[ 'delete_private_' . $capability_type . 's' ] = $pod[ 'name' ];
						$pods_capability_group[ 'delete_published_' . $capability_type . 's' ] = $pod[ 'name' ];
						$pods_capability_group[ 'delete_others_' . $capability_type . 's' ] = $pod[ 'name' ];
					}
				}
			} elseif ( 'taxonomy' == $pod[ 'type' ] ) {
				if ( 1 == pods_var( 'capabilities', $pod, 0 ) ) {
					$capability_type = pods_var( 'capability_type_custom', $pod, pods_var_raw( 'name', $pod ) . 's' );

					$pods_capability_group[ 'manage_' . $capability_type ] = $pod[ 'name' ];
					$pods_capability_group[ 'edit_' . $capability_type ] = $pod[ 'name' ];
					$pods_capability_group[ 'delete_' . $capability_type ] = $pod[ 'name' ];
					$pods_capability_group[ 'assign_' . $capability_type ] = $pod[ 'name' ];
				}
			} else {
				$pods_capability_group[ 'pods_add_' . $pod[ 'name' ] ] = $pod[ 'name' ];
				$pods_capability_group[ 'pods_edit_' . $pod[ 'name' ] ] = $pod[ 'name' ];

				if ( isset( $pod[ 'fields' ][ 'author' ] ) && 'pick' == $pod[ 'fields' ][ 'author' ][ 'type' ] && 'user' == $pod[ 'fields' ][ 'author' ][ 'pick_object' ] ) {
					$pods_capability_group[ 'pods_edit_others_' . $pod[ 'name' ] ] = $pod[ 'name' ];
				}

				$pods_capability_group[ 'pods_delete_' . $pod[ 'name' ] ] = $pod[ 'name' ];

				if ( isset( $pod[ 'fields' ][ 'author' ] ) && 'pick' == $pod[ 'fields' ][ 'author' ][ 'type' ] && 'user' == $pod[ 'fields' ][ 'author' ][ 'pick_object' ] ) {
					$pods_capability_group[ 'pods_delete_others_' . $pod[ 'name' ] ] = $pod[ 'name' ];
				}

				$actions_enabled = pods_var_raw( 'ui_actions_enabled', $pod );

				if ( ! empty( $actions_enabled ) ) {
					$actions_enabled = (array) $actions_enabled;
				} else {
					$actions_enabled = array();
				}

				$available_actions = array(
					'add',
					'edit',
					'duplicate',
					'delete',
					'reorder',
					'export'
				);

				if ( ! empty( $actions_enabled ) ) {
					$actions_disabled = array(
						'view' => 'view'
					);

					foreach ( $available_actions as $action ) {
						if ( ! in_array( $action, $actions_enabled ) ) {
							$actions_disabled[ $action ] = $action;
						}
					}

					if ( ! in_array( 'export', $actions_disabled ) ) {
						$pods_capability_group[ 'pods_export_' . $pod[ 'name' ] ] = $pod[ 'name' ];
					}

					if ( ! in_array( 'reorder', $actions_disabled ) ) {
						$pods_capability_group[ 'pods_reorder_' . $pod[ 'name' ] ] = $pod[ 'name' ];
					}
				} elseif ( 1 == pods_var( 'ui_export', $pod, 0 ) ) {
					$pods_capability_group[ 'pods_export_' . $pod[ 'name' ] ] = $pod[ 'name' ];
				}
			}
		}

		$capability_group_map = array_merge( $defaults_capability_group, $pods_roles_capability_group, $pods_components_capability_group, $pods_capability_group );

		return $capability_group_map;

	}

	function get_grouped_capabilities() {

		$capabilities = $this->get_capabilities();
		$capability_group_map = $this->get_capability_group_map();

		$grouped_capabilities = array();

		foreach ( $capabilities as $capability ) {

			if ( array_key_exists( $capability, $capability_group_map ) ) {
				$group_name = $capability_group_map[ $capability ];
			} else {
				$group_name = 'other';
			}

			if ( ! array_key_exists( $group_name, $grouped_capabilities ) ) {
				$grouped_capabilities[ $group_name ] = array();
			}

			$grouped_capabilities[ $group_name ] = array_merge( $grouped_capabilities[ $group_name ], array( $capability ) );

		}

		ksort( $grouped_capabilities );

		return $grouped_capabilities;

	}

	function get_wp_capabilities() {

		$defaults = array(
			'activate_plugins',
			'add_users',
			'create_users',
			'delete_others_pages',
			'delete_others_posts',
			'delete_pages',
			'delete_plugins',
			'delete_posts',
			'delete_private_pages',
			'delete_private_posts',
			'delete_published_pages',
			'delete_published_posts',
			'delete_themes',
			'delete_users',
			'edit_dashboard',
			'edit_files',
			'edit_others_pages',
			'edit_others_posts',
			'edit_pages',
			'edit_plugins',
			'edit_posts',
			'edit_private_pages',
			'edit_private_posts',
			'edit_published_pages',
			'edit_published_posts',
			'edit_theme_options',
			'edit_themes',
			'edit_users',
			'import',
			'install_plugins',
			'install_themes',
			'list_users',
			'manage_categories',
			'manage_links',
			'manage_options',
			'moderate_comments',
			'promote_users',
			'publish_pages',
			'publish_posts',
			'read',
			'read_private_pages',
			'read_private_posts',
			'remove_users',
			'switch_themes',
			'unfiltered_html',
			'unfiltered_upload',
			'update_core',
			'update_plugins',
			'update_themes',
			'upload_files'
		);

		return $defaults;

	}

	function get_default_capabilities() {

		$capabilities = array(
			'read'
		);

		// To support Members filters
		$capabilities = apply_filters( 'members_new_role_default_capabilities', $capabilities );

		$capabilities = apply_filters( 'pods_roles_default_capabilities', $capabilities );

		return $capabilities;

	}

	function remove_deprecated_capabilities( $capabilities ) {

		$deprecated_capabilities = array(
			'level_0',
			'level_1',
			'level_2',
			'level_3',
			'level_4',
			'level_5',
			'level_6',
			'level_7',
			'level_8',
			'level_9',
			'level_10'
		);

		$capabilities = array_diff( $capabilities, $deprecated_capabilities );

		return $capabilities;

	}

}