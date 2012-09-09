<?php
/**
 * Name: Packages
 *
 * Description: Import and Export your Pods, Fields, and other settings from one Pods site to any other
 *
 * Version: 2.0
 *
 * Developer Mode: on
 *
 * @package pods
 * @subpackage helpers
 */

class Pods_Packages extends PodsComponent {

    /**
     * Do things like register/enqueue scripts and stylesheets
     *
     * @since 2.0.0
     */
    public function __construct () {
        
    }

    /**
     * Enqueue styles
     *
     * @since 2.0.0
     */
    public function admin_assets () {
        wp_enqueue_style( 'pods-wizard' );
    }

    /**
     * Build admin area
     *
     * @param $options
     *
     * @since 2.0.0
     */
    public function admin ( $options, $component ) {
        global $wp_roles;

        $default_role = get_option( 'default_role' );

        $roles = array();

        foreach ( $wp_roles->role_objects as $key => $role ) {
            $roles[ $key ] = array(
                'id' => $key,
                'label' => $wp_roles->role_names[ $key ],
                'name' => $key,
                'capabilities' => count( (array) $role->capabilities ),
                'users' => sprintf( _n( '%s User', '%s Users', $this->count_users( $key ), 'pods' ), $this->count_users( $key ) )
            );

            if ( $default_role == $key )
                $roles[ $key ][ 'label' ] .= ' (site default)';

            if ( current_user_can( 'list_users' ) )
                $roles[ $key ][ 'users' ] = '<a href="' . admin_url( esc_url( 'users.php?role=' . $key ) ) . '">' . $roles[ $key ][ 'users' ] . '</a>';
        }

        $ui = array(
            'component' => $component,
            'data' => $roles,
            'total' => count( $roles ),
            'total_found' => count( $roles ),
            'icon' => PODS_URL . 'ui/images/icon32.png',
            'items' => 'Roles',
            'item' => 'Role',
            'fields' => array(
                'manage' => array(
                    'label' => array( 'label' => __( 'Label', 'pods' ) ),
                    'name' => array( 'label' => __( 'Name', 'pods' ) ),
                    'capabilities' => array( 'label' => __( 'Capabilities', 'pods' ) ),
                    'users' => array( 'label' => __( 'Users', 'pods' ) )
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

        if ( !is_super_admin() && !current_user_can( 'pods_roles_add' ) )
            $ui[ 'actions_disabled' ][ ] = 'add';

        if ( !is_super_admin() && !current_user_can( 'pods_roles_edit' ) )
            $ui[ 'actions_disabled' ][ ] = 'edit';

        if ( count( $roles ) < 2 || ( !is_super_admin() && !current_user_can( 'pods_roles_delete' ) ) )
            $ui[ 'actions_disabled' ][ ] = 'delete';

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

    function admin_delete ( $id, &$obj ) {
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
            $roles[ $key ] = array(
                'id' => $key,
                'label' => $wp_roles->role_names[ $key ],
                'name' => $key,
                'capabilities' => count( (array) $role->capabilities ),
                'users' => sprintf( _n( '%s User', '%s Users', $this->count_users( $key ), 'pods' ), $this->count_users( $key ) )
            );

            if ( $default_role == $key )
                $roles[ $key ][ 'label' ] .= ' (site default)';

            if ( current_user_can( 'list_users' ) )
                $roles[ $key ][ 'users' ] = '<a href="' . admin_url( esc_url( 'users.php?role=' . $key ) ) . '">' . $roles[ $key ][ 'users' ] . '</a>';
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
            if ( true !== (boolean) $x )
                continue;

            $capabilities[ esc_attr( $capability ) ] = true;
        }

        foreach ( $params->custom_capabilities as $x => $capability ) {
            if ( '--1' == $x )
                continue;

            $capabilities[ esc_attr( $capability ) ] = true;
        }

        if ( empty( $role_name ) )
            return pods_error( __( 'Role name is required', 'pods' ) );

        if ( empty( $role_label ) )
            return pods_error( __( 'Role label is required', 'pods' ) );

        if ( !isset( $wp_roles ) )
            $wp_roles = new WP_Roles();

        return $wp_roles->add_role( $role_name, $role_label, $capabilities );
    }

    /**
     * Handle the Edit Role AJAX
     *
     * @todo allow rename role_label
     *
     * @param $params
     */
    public function ajax_edit ( $params ) {
        global $wp_roles;

        $capabilities = $this->get_capabilities();

        $params->capabilities = (array) pods_var_raw( 'capabilities', $params, array() );

        $params->custom_capabilities = (array) pods_var_raw( 'custom_capabilities', $params, array() );
        $params->custom_capabilities = array_filter( array_unique( $params->custom_capabilities ) );

        if ( !isset( $params->id ) || empty( $params->id ) || !isset( $wp_roles->role_objects[ $params->id ] ) )
            return pods_error( __( 'Role not found, cannot edit it.', 'pods' ) );

        $role = $wp_roles->role_objects[ $params->id ];
        $role_name = $params->id;
        $role_label = $wp_roles->role_names[ $params->id ];
        $role_capabilities = $role->capabilities;

        $new_capabilities = array();

        foreach ( $params->capabilities as $capability => $x ) {
            if ( true !== (boolean) $x )
                continue;

            $new_capabilities[ ] = esc_attr( $capability );

            if ( !$role->has_cap( $capability ) )
                $role->add_cap( $capability );
        }

        foreach ( $params->custom_capabilities as $x => $capability ) {
            if ( '--1' == $x )
                continue;

            if ( !in_array( $capability, $new_capabilities ) )
                continue;

            $new_capabilities[ ] = esc_attr( $capability );

            if ( !$role->has_cap( $capability ) )
                $role->add_cap( $capability );
        }

        foreach ( $role_capabilities as $capability => $x ) {
            if ( !in_array( $capability, $new_capabilities ) )
                $role->remove_cap( $capability );
        }

        return true;
    }

    /**
     * Count the number of users by role
     *
     * @since  2.0.0
     * 
     * @param $key role
     */
    public function count_users( $key ) {
        $users = count_users();

        $num_users = isset( $users[ 'avail_roles' ][ $key ] )
                   ? (int)$users[ 'avail_roles' ][ $key ]
                   : 0;

        return $num_users;
    }
}
