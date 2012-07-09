<?php
/**
 * Name: Roles and Capabilities
 *
 * Description: Create and Manage WordPress User Roles and Capabilities; Uses the 'Members' plugin filters for additional plugin integrations
 *
 * Version: 1.0
 *
 * @package pods
 * @subpackage roles
 */

class Pods_Roles extends PodsComponent {

    /**
     * Do things like register/enqueue scripts and stylesheets
     *
     * @since 2.0.0
     */
    public function __construct () {

    }

    /**
     * Add options and set defaults for field type, shows in admin area
     *
     * @return array $options
     *
     * @since 2.0.0
     */
    public function options () {
        $options = array( /*
            'option_name' => array(
                'label' => 'Option Label',
                'depends-on' => array( 'another_option' => 'specific-value' ),
                'default' => 'default-value',
                'type' => 'field_type',
                'data' => array(
                    'value1' => 'Label 1',

                    // Group your options together
                    'Option Group' => array(
                        'gvalue1' => 'Option Label 1',
                        'gvalue2' => 'Option Label 2'
                    ),

                    // below is only if the option_name above is the "{$fieldtype}_format_type"
                    'value2' => array(
                        'label' => 'Label 2',
                        'regex' => '[a-zA-Z]' // Uses JS regex validation for the value saved if this option selected
                    )
                ),

                // below is only for a boolean group
                'group' => array(
                    'option_boolean1' => array(
                        'label' => 'Option boolean 1?',
                        'default' => 1,
                        'type' => 'boolean'
                    ),
                    'option_boolean2' => array(
                        'label' => 'Option boolean 2?',
                        'default' => 0,
                        'type' => 'boolean'
                    )
                )
            ) */
        );

        return $options;
    }

    /**
     * Handler to run code based on $options
     *
     * @param $options
     *
     * @since 2.0.0
     */
    public function handler ( $options ) {
        // run code based on $options set
    }

    /**
     * Build admin area
     *
     * @param $options
     *
     * @since 2.0.0
     */
    public function admin ( $options ) {
        global $wp_roles;

        $default_role = get_option( 'default_role' );

        $roles = array();

        foreach ( $wp_roles->role_objects as $key => $role ) {
            $roles[ $key ] = array(
                'id' => $key,
                'name' => ucwords( str_replace( '_', ' ', $key ) ),
                'capabilities' => count( (array) $role->capabilities )
            );
        }

        foreach ( $wp_roles->role_names as $role => $name ) {
            $roles[ $role ][ 'users' ] = 0;
            $roles[ $role ][ 'name' ] = $name;

            if ( $default_role == $role )
                $roles[ $role ][ 'name' ] .= ' (site default)';
        }

        $ui = array(
            'data' => $roles,
            'total' => count( $roles ),
            'total_found' => count( $roles ),
            'icon' => PODS_URL . 'ui/images/icon32.png',
            'items' => 'Roles',
            'item' => 'Role',
            'fields' => array( 'manage' => array( 'name', 'capabilities' ) ),
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

        if ( count( $roles ) < 2 )
            $ui[ 'actions_custom' ][] = 'delete';

        pods_ui( $ui );
    }

    function admin_add () {

    }

    function admin_edit () {

    }

    function admin_delete ( $id, &$ui ) {
        global $wp_roles;

        $id = $_GET[ 'id' ];

        $default_role = get_option( 'default_role' );

        if ( $id == $default_role ) {
            return $ui->error( sprintf( __( 'You cannot remove the <strong>%s</strong> role, you must set a new default role for the site first.', 'pods' ), $ui->data[ $id ][ 'name' ] ) );
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
                'name' => ucwords( str_replace( '_', ' ', $key ) ),
                'capabilities' => count( (array) $role->capabilities )
            );
        }

        foreach ( $wp_roles->role_names as $role => $name ) {
            $roles[ $role ][ 'users' ] = 0;
            $roles[ $role ][ 'name' ] = $name;

            if ( $default_role == $role )
                $roles[ $role ][ 'name' ] .= ' (site default)';
        }

        $name = $ui->data[ $id ][ 'name' ];

        $ui->data = $roles;
        $ui->total = count( $roles );
        $ui->total_found = count( $roles );

        $ui->message( '<strong>' . $name . '</strong> ' . __( 'role removed from site.', 'pods' ) );
    }
}