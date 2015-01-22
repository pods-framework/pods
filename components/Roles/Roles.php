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
 * @package Pods\Components
 * @subpackage Roles
 */

if ( class_exists( 'Pods_Roles' ) )
    return;

class Pods_Roles extends PodsComponent {

    /**
     * Do things like register/enqueue scripts and stylesheets
     *
     * @since 2.0
     */
    public function __construct () {
        add_filter( 'pods_roles_get_capabilities', array( $this, 'remove_deprecated_capabilities' ) );
    }

    /**
     * Enqueue styles
     *
     * @since 2.0
     */
    public function admin_assets () {
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
    public function admin ( $options, $component ) {
        global $wp_roles;

        // Hook into Gravity Forms roles (since it only adds filter if Members plugin itself is activated
        if ( class_exists( 'RGForms' ) && !has_filter( 'members_get_capabilities', array( 'RGForms', 'members_get_capabilities' ) ) )
            add_filter( 'members_get_capabilities', array( 'RGForms', 'members_get_capabilities' ) );

        $default_role = get_option( 'default_role' );

        $roles = array();

        foreach ( $wp_roles->role_objects as $key => $role ) {
            $count = $this->count_users( $key );

            $roles[ $key ] = array(
                'id' => $key,
                'label' => $wp_roles->role_names[ $key ],
                'name' => $key,
                'capabilities' => count( (array) $role->capabilities ),
                'users' => sprintf( _n( '%s User', '%s Users', $count, 'pods' ), $count )
            );

            if ( $default_role == $key )
                $roles[ $key ][ 'label' ] .= ' (site default)';

            if ( 0 < $count && pods_is_admin( array( 'list_users' ) ) ) {
                $roles[ $key ][ 'users' ] .= '<br /><a href="'
                                             . admin_url( esc_url( 'users.php?role=' . $key ) ) . '">'
                                             . __( 'View Users', 'pods' ) . '</a>';
            }
        }

        $ui = array(
            'component' => $component,
            'data' => $roles,
            'total' => count( $roles ),
            'total_found' => count( $roles ),
            'items' => 'Roles',
            'item' => 'Role',
            'fields' => array(
                'manage' => array(
                    'label' => array( 'label' => __( 'Label', 'pods' ) ),
                    'name' => array( 'label' => __( 'Name', 'pods' ) ),
                    'capabilities' => array( 'label' => __( 'Capabilities', 'pods' ) ),
                    'users' => array(
                        'label' => __( 'Users', 'pods' ),
                        'type' => 'text',
                        'options' => array(
                            'text_allow_html' => 1,
                            'text_allowed_html_tags' => ''
                        )
                    )
                )
            ),
            'actions_disabled' => array( 'duplicate', 'view', 'export' ),
            'actions_custom' => array(
                'add' => array( $this, 'admin_add' ),
                'edit' => array( $this, 'admin_edit' ),
                'delete' => array( $this, 'admin_delete' )
            ),
            'search' => false,
            'searchable' => false,
            'sortable' => false,
            'pagination' => false
        );

        if ( isset( $roles[ pods_var( 'id', 'get', -1 ) ] ) )
            $ui[ 'row' ] = $roles[ pods_var( 'id', 'get', -1 ) ];

        if ( !pods_is_admin( array( 'pods_roles_add' ) ) )
            $ui[ 'actions_disabled' ][] = 'add';

        if ( !pods_is_admin( array( 'pods_roles_edit' ) ) )
            $ui[ 'actions_disabled' ][] = 'edit';

        if ( count( $roles ) < 2 || !pods_is_admin( array( 'pods_roles_delete' ) ) )
            $ui[ 'actions_disabled' ][] = 'delete';

        pods_ui( $ui );
    }

    function admin_add ( $obj ) {
        global $wp_roles;

        $capabilities = $this->get_capabilities();

        $defaults = $this->get_default_capabilities();

        $component = $obj->x[ 'component' ];

        $method = 'add'; // ajax_add

        pods_view( PODS_DIR . 'components/Roles/ui/add.php', compact( array_keys( get_defined_vars() ) ) );
    }

    function admin_edit ( $duplicate, $obj ) {
        global $wp_roles;

        $id = $obj->id;

        $capabilities = $this->get_capabilities();

        $role_name = $role_label = $role_capabilities = null;

        foreach ( $wp_roles->role_objects as $key => $role ) {
            if ( $key != $id )
                continue;

            $role_name = $key;
            $role_label = $wp_roles->role_names[ $key ];
            $role_capabilities = $role->capabilities;
        }

        if ( empty( $role ) )
            return $obj->error( __( 'Role not found, cannot edit it.', 'pods' ) );

        $component = $obj->x[ 'component' ];

        $method = 'edit'; // ajax_edit

        pods_view( PODS_DIR . 'components/Roles/ui/edit.php', compact( array_keys( get_defined_vars() ) ) );
    }

    function admin_delete ( $id, $obj ) {
        global $wp_roles;

        $id = $obj->id;

        if ( !isset( $obj->data[ $id ] ) )
            return $obj->error( __( 'Role not found, it cannot be deleted.', 'pods' ) );

        $default_role = get_option( 'default_role' );

        if ( $id == $default_role ) {
            return $obj->error( sprintf( __( 'You cannot remove the <strong>%s</strong> role, you must set a new default role for the site first.', 'pods' ), $obj->data[ $id ][ 'name' ] ) );
        }

        $wp_user_search = new WP_User_Search( '', '', $id );

        $users = $wp_user_search->get_results();

        if ( !empty( $users ) && is_array( $users ) ) {
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
                'id' => $key,
                'label' => $wp_roles->role_names[ $key ],
                'name' => $key,
                'capabilities' => count( (array) $role->capabilities ),
                'users' => sprintf( _n( '%s User', '%s Users', $count, 'pods' ), $count )
            );

            if ( $default_role == $key )
                $roles[ $key ][ 'label' ] .= ' (site default)';

            if ( 0 < $count && pods_is_admin( array( 'list_users' ) ) ) {
                $roles[ $key ][ 'users' ] .= '<br /><a href="'
                                             . admin_url( esc_url( 'users.php?role=' . $key ) ) . '">'
                                             . __( 'View Users', 'pods' ) . '</a>';
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
     * @return mixed|void
     */
    public function ajax_add ( $params ) {
        global $wp_roles;

        $role_name = pods_var_raw( 'role_name', $params );
        $role_label = pods_var_raw( 'role_label', $params );

        $params->capabilities = (array) pods_var_raw( 'capabilities', $params, array() );

        $params->custom_capabilities = (array) pods_var_raw( 'custom_capabilities', $params, array() );
        $params->custom_capabilities = array_filter( array_unique( $params->custom_capabilities ) );

        $capabilities = array();

        foreach ( $params->capabilities as $capability => $x ) {
            if ( empty( $capability ) || true !== (boolean) $x )
                continue;

            $capabilities[ esc_attr( $capability ) ] = true;
        }

        foreach ( $params->custom_capabilities as $x => $capability ) {
            if ( empty( $capability ) || '--1' == $x )
                continue;

            $capabilities[ esc_attr( $capability ) ] = true;
        }

        if ( empty( $role_name ) )
            return pods_error( __( 'Role name is required', 'pods' ) );

        if ( empty( $role_label ) )
            return pods_error( __( 'Role label is required', 'pods' ) );

        return add_role( $role_name, $role_label, $capabilities );
    }

    /**
     * Handle the Edit Role AJAX
     *
     * @todo allow rename role_label
     *
     * @param $params
     * @return bool|mixed|void
     */
    public function ajax_edit ( $params ) {
        global $wp_roles;

        $capabilities = $this->get_capabilities();

        $params->capabilities = (array) pods_var_raw( 'capabilities', $params, array() );

        $params->custom_capabilities = (array) pods_var_raw( 'custom_capabilities', $params, array() );
        $params->custom_capabilities = array_filter( array_unique( $params->custom_capabilities ) );

        if ( !isset( $params->id ) || empty( $params->id ) || !isset( $wp_roles->role_objects[ $params->id ] ) )
            return pods_error( __( 'Role not found, cannot edit it.', 'pods' ) );

        /**
         * @var $role WP_Role
         */
        $role = $wp_roles->role_objects[ $params->id ];
        $role_name = $params->id;
        $role_label = $wp_roles->role_names[ $params->id ];
        $role_capabilities = $role->capabilities;

        $new_capabilities = array();

        foreach ( $params->capabilities as $capability => $x ) {
            if ( empty( $capability ) || true !== (boolean) $x )
                continue;

            $new_capabilities[] = esc_attr( $capability );

            if ( !$role->has_cap( $capability ) )
                $role->add_cap( $capability );
        }

        foreach ( $params->custom_capabilities as $x => $capability ) {
            if ( empty( $capability ) )
                continue;

            if ( in_array( $capability, $new_capabilities ) )
                continue;

            $new_capabilities[] = esc_attr( $capability );

            if ( !$role->has_cap( $capability ) )
                $role->add_cap( $capability );
        }

        foreach ( $role_capabilities as $capability => $x ) {
            if ( !in_array( $capability, $new_capabilities ) && false === strpos( $capability, 'level_' ) )
                $role->remove_cap( $capability );
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
    function count_users ( $role ) {
        $count_users = count_users();

        $avail_roles = array();

        foreach ( $count_users[ 'avail_roles' ] as $count_role => $count ) {
            $avail_roles[ $count_role ] = $count;
        }

        if ( empty( $role ) )
            return $avail_roles;

        if ( !isset( $avail_roles[ $role ] ) )
            $avail_roles[ $role ] = 0;

        return $avail_roles[ $role ];
    }

    function get_capabilities () {
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

    function get_wp_capabilities () {
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

    function get_default_capabilities () {
        $capabilities = array(
            'read'
        );

        // To support Members filters
        $capabilities = apply_filters( 'members_new_role_default_capabilities', $capabilities );

        $capabilities = apply_filters( 'pods_roles_default_capabilities', $capabilities );

        return $capabilities;
    }

    function remove_deprecated_capabilities ( $capabilities ) {
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
