<?php
class PodsAdmin {

    private $api;

    private $data;

    /**
     * Setup and Handle Admin functionality
     *
     * @license http://www.gnu.org/licenses/gpl-2.0.html
     * @since 2.0.0
     */
    public function __construct () {
        $this->api = pods_api();
        $this->data = pods_data();

        add_action( 'admin_enqueue_scripts', array( $this, 'admin_head' ) );

        add_action( 'admin_init', array( $this, 'admin_init' ), 9 );

        add_action( 'admin_menu', array( $this, 'admin_menu' ), 99 );

        if ( is_admin() ) {
            add_action( 'wp_ajax_pods_admin', array( $this, 'admin_ajax' ) );
            add_action( 'wp_ajax_nopriv_pods_admin', array( $this, 'admin_ajax' ) );

            add_action( 'wp_ajax_pods_upload', array( $this, 'admin_ajax_upload' ) );
            add_action( 'wp_ajax_nopriv_pods_upload', array( $this, 'admin_ajax_upload' ) );

            add_filter( 'media_buttons_context', array( $this, 'media_button' ) );
        }

        add_filter( 'members_get_capabilities', array( $this, 'admin_capabilities' ) );
    }

    public function admin_init () {
        // Fix for plugins that *don't do it right* so we don't cause issues for users
        if ( defined( 'DOING_AJAX' ) && !empty( $_POST ) && ( in_array( pods_var( 'action', 'get' ), array( 'pods_admin', 'pods_upload', 'pods_admin_components' ) ) || in_array( pods_var( 'action', 'post' ), array( 'pods_admin', 'pods_upload', 'pods_admin_components' ) ) ) ) {
            foreach ( $_POST as $key => $value ) {
                if ( 'action' == $key )
                    continue;

                unset( $_POST[ $key ] );

                $_POST[ '_podsfix_' . $key ] = $value;
            }
        }
    }

    public function admin_head () {
        wp_register_style( 'pods-admin', PODS_URL . 'ui/css/pods-admin.css', array(), PODS_VERSION );

        wp_register_script( 'pods-floatmenu', PODS_URL . 'ui/js/floatmenu.js', array(), PODS_VERSION );

        wp_register_script( 'pods-admin-importer', PODS_URL . 'ui/js/admin-importer.js', array(), PODS_VERSION );

        wp_register_style( 'pods-manage', PODS_URL . 'ui/css/pods-manage.css', array(), PODS_VERSION );

        wp_register_style( 'pods-wizard', PODS_URL . 'ui/css/pods-wizard.css', array(), PODS_VERSION );

        wp_register_script( 'pods-upgrade', PODS_URL . 'ui/js/jquery.pods.upgrade.js', array(), PODS_VERSION );

        if ( isset( $_GET[ 'page' ] ) ) {
            $page = $_GET[ 'page' ];
            if ( 'pods' == $page || ( false !== strpos( $page, 'pods-' ) && 0 === strpos( $page, 'pods-' ) ) ) {
?>
    <script type="text/javascript">
        var PODS_URL = "<?php echo PODS_URL; ?>";
    </script>
<?php
                wp_enqueue_script( 'jquery' );
                wp_enqueue_script( 'jquery-ui-core' );
                wp_enqueue_script( 'jquery-ui-sortable' );

                wp_enqueue_style( 'jquery-ui' );

                wp_enqueue_script( 'pods-floatmenu' );

                wp_enqueue_style( 'pods-qtip' );
                wp_enqueue_script( 'pods-qtip' );
                wp_enqueue_script( 'pods-qtip-init' );

                wp_enqueue_script( 'pods' );

                if ( ( false !== strpos( $page, 'pods-manage-' ) && 0 === strpos( $page, 'pods-manage-' ) ) || ( false !== strpos( $page, 'pods-add-new-' ) && 0 === strpos( $page, 'pods-add-new-' ) ) ) {
                    wp_enqueue_script( 'post' );
                    /*
                    wp_enqueue_style( 'pods-manage' );

                    // Just for demo
                    wp_enqueue_script( 'pods-forms' );

                    wp_enqueue_style( 'pods-cleditor' );

                    wp_enqueue_script( 'jquery-effects-core' );
                    wp_enqueue_script( 'jquery-effects-slide' );
                    wp_enqueue_script( 'jquery-ui-slider' );
                    wp_enqueue_script( 'jquery-ui-button' );
                    wp_enqueue_script( 'jquery-ui-autocomplete' );
                    wp_enqueue_script( 'pods-cleditor-min' );
                    // Date
                    wp_enqueue_script( 'jquery-ui-datepicker' );

                    // Date + Time
                    wp_enqueue_script( 'jquery-ui-timepicker' );
                    wp_enqueue_style( 'jquery-ui-timepicker' );

                    // File Upload
                    wp_enqueue_script( 'thickbox' );
                    wp_enqueue_style( 'thickbox' );

                    // Plupload scripts
                    wp_enqueue_script( 'plupload' );
                    wp_enqueue_script( 'plupload-html5' );
                    wp_enqueue_script( 'plupload-flash' );
                    wp_enqueue_script( 'plupload-silverlight' );
                    wp_enqueue_script( 'plupload-html4' );
                    wp_enqueue_script( 'handlebars' );

                    // Select2
                    wp_enqueue_script('jquery-select2');
                    wp_enqueue_style('jquery-select2');*/
                }
                else
                    wp_enqueue_style( 'pods-admin' );

                if ( 'pods-advanced' == $page ) {
                    wp_register_style( 'pods-advanced', PODS_URL . 'ui/css/pods-advanced.css', array(), '1.0' );
                    wp_enqueue_style( 'pods-advanced' );

                    wp_enqueue_script( 'jquery-ui-effects-core', PODS_URL . 'ui/js/jquery-ui/jquery.effects.core.js', array( 'jquery' ), '1.8.8' );
                    wp_enqueue_script( 'jquery-ui-effects-fade', PODS_URL . 'ui/js/jquery-ui/jquery.effects.fade.js', array( 'jquery' ), '1.8.8' );
                    wp_enqueue_script( 'jquery-ui-dialog' );

                    wp_register_script( 'pods-advanced', PODS_URL . 'ui/js/advanced.js', array(), PODS_VERSION );
                    wp_enqueue_script( 'pods-advanced' );
                }
                elseif ( 'pods-wizard' == $page || 'pods-upgrade' == $page || ( 'pods' == $page && in_array( pods_var( 'action', 'get', 'manage' ), array( 'add', 'manage' ) ) ) ) {
                    wp_enqueue_style( 'pods-wizard' );

                    if ( 'pods-upgrade' == $page )
                        wp_enqueue_script( 'pods-upgrade' );
                }
            }
        }
    }

    public function admin_menu () {
        $submenu = array();

        $results = $this->api->load_pods( array(
            'type' => 'pod'
        ) );

        $old_pods = get_option( 'pods_version' );
        $upgraded = get_option( 'pods_framework_upgraded_1_x' );
        $upgraded = 1; // Upgrade not ready yet!

        if ( ( empty( $old_pods ) || 1 == $upgraded ) && false !== $results ) {
            foreach ( (array) $results as $item ) {
                if ( !is_super_admin() && !current_user_can( 'pods_new_' . $item[ 'name' ] ) && !current_user_can( 'pods_edit_' . $item[ 'name' ] ) && !current_user_can( 'pods_delete_' . $item[ 'name' ] ) )
                    continue;

                $label = pods_var( 'menu_name', $item[ 'options' ], pods_var( 'label', $item[ 'options' ], ucwords( str_replace( '_', ' ', $item[ 'name' ] ) ), null, true ), null, true );
                $label = apply_filters( 'pods_admin_menu_label', $label, $item );

                $singular_label = pods_var( 'label_singular', $item[ 'options' ], pods_var( 'label', $item[ 'options' ], ucwords( str_replace( '_', ' ', $item[ 'name' ] ) ), null, true), null, true );
                $plural_label = pods_var( 'label', $item[ 'options' ], ucwords( str_replace( '_', ' ', $item[ 'name' ] ) ), null, true );

                if ( 1 == $item[ 'options' ][ 'show_in_menu' ] ) {
                    add_object_page( $label, $label, 'read', "pods-manage-{$item['name']}" );

                    if ( is_super_admin() || current_user_can( 'pods_edit_' . $item[ 'name' ] ) || current_user_can( 'pods_delete_' . $item[ 'name' ] ) ) {
                        $all_label = $plural_label;

                        if ( "pods-manage-{$item['name']}" == pods_var( 'page', 'get' ) ) {
                            if ( 'edit' == pods_var( 'action', 'get', 'manage' ) )
                                $all_label = __( 'Edit', 'pods' ) . ' ' . $singular_label;
                            elseif ( 'add' == pods_var( 'action', 'get', 'manage' ) )
                                $all_label = __( 'Add New', 'pods' ) . ' ' . $singular_label;
                        }

                        add_submenu_page( "pods-manage-{$item['name']}", $all_label, __( 'All', 'pods' ), 'read', "pods-manage-{$item['name']}", array(
                            $this,
                            'admin_content'
                        ) );
                    }

                    if ( is_super_admin() || current_user_can( 'pods_add_' . $item[ 'name' ] ) ) {
                        $page = "pods-add-new-{$item['name']}";

                        if ( !is_super_admin() && !current_user_can( 'pods_edit_' . $item[ 'name' ] ) && !current_user_can( 'pods_delete_' . $item[ 'name' ] ) )
                            $page = "pods-manage-{$item['name']}";

                        $add_label = __( 'Add New', 'pods' ) . ' ' . $singular_label;

                        if ( $page == pods_var( 'page', 'get' ) ) {
                            if ( 'edit' == pods_var( 'action', 'get', 'add' ) )
                                $add_label = __( 'Edit', 'pods' ) . ' ' . $singular_label;
                            elseif ( 'manage' == pods_var( 'action', 'get', 'add' ) )
                                $add_label = $plural_label;
                        }

                        add_submenu_page( "pods-manage-{$item['name']}", $add_label, __( 'Add New', 'pods' ), 'read', $page, array(
                            $this,
                            'admin_content'
                        ) );
                    }
                }
                elseif ( !current_user_can( 'pods_edit_' . $item[ 'name' ] ) && !current_user_can( 'pods_delete_' . $item[ 'name' ] ) )
                    $submenu[] = $item;
            }

            if ( !empty( $submenu ) ) {
                $parent = false;

                foreach ( $submenu as $item ) {
                    $page = "pods-manage-{$item['name']}";

                    if ( false === $parent ) {
                        $parent = $page;

                        add_object_page( 'Pods', 'Pods', 'read', $parent, null, PODS_URL . '/ui/images/icon16.png' );
                    }

                    $label = pods_var( 'menu_name', $item[ 'options' ], pods_var( 'label', $item[ 'options' ], ucwords( str_replace( '_', ' ', $item[ 'name' ] ) ), null, true ), null, true );
                    $label = apply_filters( 'pods_admin_menu_label', $label, $item );

                    $singular_label = pods_var( 'label_singular', $item[ 'options' ], pods_var( 'label', $item[ 'options' ], ucwords( str_replace( '_', ' ', $item[ 'name' ] ) ), null, true ), null, true );
                    $plural_label = pods_var( 'label', $item[ 'options' ], ucwords( str_replace( '_', ' ', $item[ 'name' ] ) ), null, true );

                    $all_label = $plural_label;

                    if ( $page == pods_var( 'page', 'get' ) ) {
                        if ( 'edit' == pods_var( 'action', 'get', 'manage' ) )
                            $all_label = __( 'Edit', 'pods' ) . ' ' . $singular_label;
                        elseif ( 'add' == pods_var( 'action', 'get', 'manage' ) )
                            $all_label = __( 'Add New', 'pods' ) . ' ' . $singular_label;
                    }

                    add_submenu_page( $parent, $all_label, "Manage {$label}", 'read', $page, array(
                        $this,
                        'admin_content'
                    ) );
                }
            }
        }

        $admin_menus = array(
            'pods' => array(
                'label' => 'Setup',
                'function' => array( $this, 'admin_setup' ),
                'access' => 'pods'
            ),
            'pods-components' => array(
                'label' => 'Components',
                'function' => array( $this, 'admin_components' ),
                'access' => 'pods_components'
            ),
            'pods-help' => array(
                'label' => 'Help',
                'function' => array( $this, 'admin_help' )
            )
        );

        if ( defined( 'PODS_DEVELOPER' ) ) {
            $admin_menus = array(
                'pods' => array(
                    'label' => 'Setup',
                    'function' => array( $this, 'admin_setup' ),
                    'access' => 'pods'
                ),
                'pods-advanced' => array(
                    'label' => 'Advanced',
                    'function' => array( $this, 'admin_advanced' ),
                    'access' => array(
                        'pods_templates',
                        'pods_pod_pages',
                        'pods_helpers'
                    )
                ),
                'pods-settings' => array(
                    'label' => 'Settings',
                    'function' => array( $this, 'admin_settings' ),
                    'access' => 'pods_settings'
                ),
                'pods-packages' => array(
                    'label' => 'Import / Export',
                    'function' => array( $this, 'admin_packages' ),
                    'access' => 'pods_packages'
                ),
                'pods-components' => array(
                    'label' => 'Components',
                    'function' => array( $this, 'admin_components' ),
                    'access' => 'pods_components'
                ),
                'pods-upgrade' => array(
                    'label' => 'Upgrade',
                    'function' => array( $this, 'admin_upgrade' )
                ),
                'pods-help' => array(
                    'label' => 'Help',
                    'function' => array( $this, 'admin_help' )
                )
            );
        }

        if ( !empty( $old_pods ) && 1 != $upgraded ) {
            $admin_menus = array(
                'pods-upgrade' => array(
                    'label' => 'Upgrade',
                    'function' => array( $this, 'admin_upgrade' )
                )
            );
        }

        $admin_menus = apply_filters( 'pods_admin_menu', $admin_menus );

        $parent = false;

        foreach ( $admin_menus as $page => $menu_item ) {
            if ( !is_super_admin() && isset( $menu_item[ 'access' ] ) ) {
                $access = (array) $menu_item[ 'access' ];

                $ok = false;

                foreach ( $access as $cap ) {
                    if ( current_user_can( $cap ) ) {
                        $ok = true;

                        break;
                    }
                }

                if ( !$ok )
                    continue;
            }

            if ( !isset( $menu_item[ 'label' ] ) )
                $menu_item[ 'label' ] = $page;

            if ( false === $parent ) {
                $parent = $page;

                $menu = __( 'Pods Admin', 'pods' );

                if ( 'pods-upgrade' == $parent )
                    $menu = __( 'Pods Upgrade', 'pods' );

                add_menu_page( $menu, $menu, 'read', $parent, null, PODS_URL . '/ui/images/icon16.png' );
            }

            add_submenu_page( $parent, $menu_item[ 'label' ], $menu_item[ 'label' ], 'read', $page, $menu_item[ 'function' ] );

            if ( 'pods-components' == $page && defined( 'PODS_DEVELOPER' ) )
                PodsInit::$components->menu( $parent );
        }
    }

    public function admin_content () {
        $pod = str_replace( array( 'pods-manage-', 'pods-add-new-' ), '', $_GET[ 'page' ] );
        $default = 'manage';

        if ( false !== strpos( $_GET[ 'page' ], 'pods-add-new-' ) )
            $default = 'add';

        $actions_disabled = array( 'duplicate', 'view', 'export' );

        if ( !is_super_admin() ) {
            if ( !current_user_can( 'pods_add_' . $pod ) ) {
                $actions_disabled[] = 'add';
                $default = 'manage';
            }

            if ( !is_super_admin() && !current_user_can( 'pods_edit_' . $pod ) )
                $actions_disabled[] = 'edit';

            if ( !is_super_admin() && !current_user_can( 'pods_delete_' . $pod ) )
                $actions_disabled[] = 'delete';
        }

        $_GET[ 'action' ] = pods_var( 'action', 'get', $default );

        $pod = pods( $pod, pods_var( 'id', 'get', null, null, true ) );

        $ui = array(
            'pod' => $pod,
            'fields' => array(
                'manage' => array(
                    $pod->pod_data[ 'field_index' ] => __( 'Name', 'pods' )
                ),
                'add' => $pod->pod_data[ 'fields' ],
                'edit' => $pod->pod_data[ 'fields' ],
                'duplicate' => $pod->pod_data[ 'fields' ]
            ),
            'item' => pods_var( 'label_singular', $pod->pod_data[ 'options' ], pods_var( 'label_singular', $pod->pod_data[ 'options' ], ucwords( str_replace( '_', ' ', $pod->pod ) ), null, true ), null, true ),
            'items' => pods_var( 'label', $pod->pod_data[ 'options' ], ucwords( str_replace( '_', ' ', $pod->pod ) ), null, true ),
            'orderby' => 'id',
            'orderby_dir' => 'desc',
            'actions_disabled' => $actions_disabled
        );

        // @todo Customize the Add New / Manage links to point to their correct menu items

        $ui = apply_filters( 'pods_admin_ui_' . $pod->pod, apply_filters( 'pods_admin_ui', $ui, $pod->pod ), $pod );

        pods_ui( $ui );
    }

    public function media_button ( $context ) {
        $current_page = basename( $_SERVER['PHP_SELF'] );

        if ( $current_page == 'index.php' )
            return $context;

        add_action( 'admin_footer', array( $this, 'mce_popup' ) );

        $button = '<a href="#TB_inline?width=640&inlineId=pods_shortcode_form" class="thickbox" id="add_pod_button" title="Embed Pods"><img src="' . PODS_URL . 'ui/images/icon16.png" alt="Embed Pods" /></a>';
        $context .= $button;
        return $context;
    }

    public function mce_popup () {
        pods_view( PODS_DIR . 'ui/admin/shortcode.php' );
    }

    public function admin_setup () {
        $pods = pods_api()->load_pods();

        if ( empty( $pods ) && !isset( $_GET[ 'action' ] ) )
            $_GET[ 'action' ] = 'add';

        pods_ui( array(
            'data' => $pods,
            'total' => count( $pods ),
            'total_found' => count( $pods ),
            'icon' => PODS_URL . 'ui/images/icon32.png',
            'items' => 'Pods',
            'item' => 'Pod',
            'fields' => array( 'manage' => array( 'label', 'name', 'type', 'storage' ) ),
            'actions_disabled' => array( 'duplicate', 'view', 'export' ),
            'actions_custom' => array(
                'add' => array( $this, 'admin_setup_add' ),
                'edit' => array( $this, 'admin_setup_edit' ),
                'delete' => array( $this, 'admin_setup_delete' )
            ),
            'search' => false,
            'searchable' => false,
            'sortable' => false,
            'pagination' => false
        ) );
    }

    public function admin_setup_add ( $obj ) {
        require_once PODS_DIR . 'ui/admin/setup_add.php';
    }

    public function admin_setup_edit ( $duplicate, $obj ) {
        require_once PODS_DIR . 'ui/admin/setup_edit.php';
    }

    public function admin_setup_delete ( $id, $obj ) {
        $pod = $this->api->load_pod ( array( 'id' => $id ) );

        if ( empty( $pod ) )
            return $obj->error( __( 'Pod not found.', 'pods' ) );

        $this->api->delete_pod( array( 'id' => $id ) );

        unset( $obj->data[ $pod[ 'id' ] ] );

        $obj->message( __( 'Pod deleted successfully.', 'pods' ) );
    }

    public function admin_advanced () {
        require_once PODS_DIR . 'ui/admin/advanced.php';
    }

    public function admin_settings () {

    }

    public function admin_packages () {
        /*pods_ui(array('sql' => array('table' => '@wp_pods_objects'),
                      'icon' => PODS_URL .'ui/images/icon32.png',
                      'items' => 'Packages',
                      'item' => 'Package',
                      'orderby' => 'name',
                      'where' => 'type="package"',
                      'fields' => array('manage' => array('name')),
                      'actions_disabled' => array('edit', 'duplicate', 'view', 'export'),
                      'actions_custom' => array('add' => array($this, 'admin_packages_add'))));*/
    }

    public function admin_components () {
        $components = PodsInit::$components->components;

        foreach ( $components as $component => &$component_data ) {
            $component_data = array(
                'id' => $component_data[ 'ID' ],
                'name' => $component_data[ 'Name' ],
                'description' => make_clickable( $component_data[ 'Description' ] ),
                'version' => $component_data[ 'Version' ],
                'author' => $component_data[ 'Author' ],
                'toggle' => 0
            );

            if ( isset( PodsInit::$components->settings[ 'components' ][ $component_data[ 'id' ] ] ) && 0 != PodsInit::$components->settings[ 'components' ][ $component_data[ 'id' ] ] )
                $component_data[ 'toggle' ] = 1;
        }

        pods_ui( array(
            'data' => $components,
            'total' => count( $components ),
            'total_found' => count( $components ),
            'icon' => PODS_URL . 'ui/images/icon32.png',
            'items' => 'Components',
            'item' => 'Component',
            'fields' => array( 'manage' => array( 'name', 'description' ) ), //, 'version', 'author' ) ),
            'actions_disabled' => array( 'duplicate', 'view', 'export', 'add', 'edit', 'delete' ),
            'actions_custom' => array(
                'toggle' => array( 'callback' => array( $this, 'admin_components_toggle' ) )
            ),
            'search' => false,
            'searchable' => false,
            'sortable' => false,
            'pagination' => false
        ) );
    }

    public function admin_components_handler () {
        $component = str_replace( 'pods-component-', '', $_GET[ 'page' ] );

        PodsInit::$components->admin( $component );
    }

    public function admin_components_toggle ( PodsUI $ui ) {
        $component = $_GET[ 'id' ];

        if ( !empty( PodsInit::$components->components[ $component ][ 'PluginDependency' ] ) ) {
            $dependency = explode( '|', PodsInit::$components->components[ $component ][ 'PluginDependency' ] );

            if ( !pods_is_plugin_active( $dependency[ 1 ] ) ) {
                $website = 'http://wordpress.org/extend/plugins/' . dirname( $dependency[ 1 ] ) . '/';

                if ( isset( $dependency[ 2 ] ) )
                    $website = $dependency[ 2 ];

                $message = sprintf( __( 'This component requires that you have the <strong>%s</strong> plugin installed and activated. You can find it at %s', 'pods' ), $dependency[ 0 ], '<a href="' . $website . '" target="_blank">' . $website . '</a>' );

                return $ui->error( $message );
            }
        }

        $toggle = PodsInit::$components->toggle( $component );

        if ( $toggle )
            $ui->message( PodsInit::$components->components[ $component ][ 'Name' ] . ' ' . __( 'Component enabled', 'pods' ) );
        else
            $ui->message( PodsInit::$components->components[ $component ][ 'Name' ] . ' ' . __( 'Component disabled', 'pods' ) );

        $components = PodsInit::$components->components;

        foreach ( $components as $component => &$component_data ) {
            $component_data = array(
                'id' => $component_data[ 'ID' ],
                'name' => $component_data[ 'Name' ],
                'description' => make_clickable( $component_data[ 'Description' ] ),
                'version' => $component_data[ 'Version' ],
                'author' => $component_data[ 'Author' ],
                'toggle' => ( 0 != PodsInit::$components->settings[ 'components' ][ $component_data[ 'ID' ] ] ? 1 : 0 )
            );
        }

        $ui->data = $components;

        $ui->manage();
    }

    public function admin_upgrade () {
        require_once PODS_DIR . 'ui/admin/upgrade.php';
    }

    public function admin_help () {
        require_once PODS_DIR . 'ui/admin/help.php';
    }

    public function admin_capabilities ( $capabilities ) {
        $pods = pods_api()->load_pods();

        $capabilities[] = 'pods';
        $capabilities[] = 'pods_templates';
        $capabilities[] = 'pods_pages';
        $capabilities[] = 'pods_helpers';
        $capabilities[] = 'pods_settings';
        $capabilities[] = 'pods_packages';
        $capabilities[] = 'pods_components';

        foreach ( $pods as $pod ) {
            if ( !in_array( $pod[ 'type' ], array( 'pod', 'table' ) ) )
                continue;

            $capabilities[] = 'pods_add_' . $pod[ 'name' ];
            $capabilities[] = 'pods_edit_' . $pod[ 'name' ];
            $capabilities[] = 'pods_delete_' . $pod[ 'name' ];
        }

        return $capabilities;
    }

    public function admin_ajax () {
        if ( false === headers_sent() ) {
            if ( '' == session_id() )
                @session_start();

            header( 'Content-Type: text/html; charset=' . get_bloginfo( 'charset' ) );
        }

        // Sanitize input
        $params = stripslashes_deep( (array) $_POST );
        foreach ( $params as $key => $value ) {
            if ( 'action' == $key )
                continue;

            unset( $params[ $key ] );

            $params[ str_replace( '_podsfix_', '', $key ) ] = $value;
        }

        if ( !defined( 'PODS_STRICT_MODE' ) || !PODS_STRICT_MODE )
            $params = pods_sanitize( $params );

        $params = (object) $params;

        $methods = array(
            'add_pod' => array( 'priv' => 'manage_pods' ),
            'save_pod' => array( 'priv' => 'manage_pods' ),
            'save_field' => array( 'priv' => 'manage_pods' ),
            'save_template' => array( 'priv' => 'manage_templates' ),
            'save_page' => array( 'priv' => 'manage_pod_pages' ),
            'save_helper' => array( 'priv' => 'manage_helpers' ),
            'save_roles' => array( 'priv' => 'manage_roles' ),
            'save_pod_item' => array(),
            'reorder_pod_item' => array( 'access_pod_specific' => true ),
            'delete_pod' => array( 'priv' => 'manage_pods' ),
            'delete_field' => array( 'priv' => 'manage_pods' ),
            'delete_template' => array( 'priv' => 'manage_templates' ),
            'delete_page' => array( 'priv' => 'manage_pod_pages' ),
            'delete_helper' => array( 'priv' => 'manage_helpers' ),
            'delete_pod_item' => array( 'access_pod_specific' => true ),
            'load_pod' => array( 'priv' => 'manage_pods', 'format' => 'json' ),
            'load_field' => array( 'priv' => 'manage_pods', 'format' => 'json' ),
            'load_template' => array( 'priv' => 'manage_templates', 'format' => 'json' ),
            'load_page' => array( 'priv' => 'manage_pod_pages', 'format' => 'json' ),
            'load_helper' => array( 'priv' => 'manage_helpers', 'format' => 'json' ),
            'load_sister_fields' => array( 'priv' => 'manage_pods', 'format' => 'json' ),
            'load_pod_item' => array( 'access_pod_specific' => true ),
            'load_files' => array( 'priv' => 'upload_files' ),
            'export_package' => array( 'priv' => 'manage_packages', 'format' => 'json', 'safe' => true ),
            'import_package' => array( 'priv' => 'manage_packages' ),
            'validate_package' => array( 'priv' => 'manage_packages' ),
            'replace_package' => array( 'priv' => 'manage_packages' ),
            'security_settings' => array( 'priv' => 'manage_settings' ),
            'select2_ajax' => array('priv' => 'manage_pds', 'format' => 'json'),
            'upgrade' => array( 'priv' => 'manage_pods' ),
            'process_form' => array()
        );

        $methods = apply_filters( 'pods_admin_ajax_methods', $methods, $this );

        if ( !isset( $params->method ) || !isset( $methods[ $params->method ] ) )
            pods_error( 'Invalid AJAX request', $this );

        if ( !isset( $params->_wpnonce ) || false === wp_verify_nonce( $params->_wpnonce, 'pods-' . $params->method ) )
            pods_error( 'Unauthorized request', $this );

        $defaults = array(
            'priv' => null,
            'format' => null,
            'safe' => null,
            'access_pod_specific' => null,
            'name' => $params->method
        );

        $method = (object) array_merge( $defaults, (array) $methods[ $params->method ] );

        // Cleaning up $params
        unset( $params->action );
        unset( $params->method );
        unset( $params->_wpnonce );

        if ( true === $method->access_pod_specific ) {
            $priv_val = false;
            if ( isset( $params->pod ) )
                $priv_val = 'pod_' . $params->pod;
            if ( false === $priv_val || ( !pods_access( $priv_val ) && !pods_access( 'manage_content' ) ) )
                pods_error( 'Access denied', $this );
        }

        // Check permissions (convert to array to support multiple)
        if ( !empty( $method->priv ) ) {
            foreach ( (array) $method->priv as $priv_val ) {
                if ( !pods_access( $priv_val ) )
                    pods_error( 'Access denied', $this );
            }
        }

        if ( 'save_pod_item' == $method->name ) {
            $columns = pods_validate_key( $params->token, $params->pod, $params->uri_hash, null, $params->form_count );

            if ( false === $columns )
                pods_error( 'This form has expired. Please reload the page and ensure your session is still active.', $this );

            if ( is_array( $columns ) ) {
                foreach ( $columns as $key => $val ) {
                    $column = is_array( $val ) ? $key : $val;

                    if ( !isset( $params->$column ) )
                        unset( $columns[ $column ] );
                    else
                        $columns[ $column ] = $params->$column;
                }
            }
            else {
                $tmp = $this->api->load_pod( array( 'name' => $params->pod ) );
                $columns = array();

                foreach ( $tmp[ 'fields' ] as $field_data ) {
                    $column = $field_data[ 'name' ];

                    if ( !isset( $params->$column ) )
                        continue;

                    $columns[ $column ] = $params->$column;
                }
            }

            $params->data = $columns;
        }
        elseif ( 'process_form' == $method->name ) {
            // save data
        }
        elseif ( 'save_pod' == $method->name ) {
            if ( isset( $params->field_data ) && !is_array( $params->field_data ) ) {
                $params->field_data = stripslashes( $params->field_data );
                $params->field_data = (array) @json_decode( $params->field_data, true );
            }
        }

        $params = apply_filters( 'pods_api_' . $method->name, $params, $method );

        if ( 'upgrade' == $method->name ) {
            require_once( PODS_DIR . 'sql/PodsUpgrade.php' );

            $upgrade = new PodsUpgrade_2_0();

            $output = (string) $upgrade->ajax( $params );
        }
        elseif ( 'security_settings' == $method->name ) {
            delete_option( 'pods_disable_file_browser' );
            add_option( 'pods_disable_file_browser', ( isset( $params->disable_file_browser ) ? $params->disable_file_browser : 1 ) );

            delete_option( 'pods_files_require_login' );
            add_option( 'pods_files_require_login', ( isset( $params->files_require_login ) ? $params->files_require_login : 1 ) );

            delete_option( 'pods_files_require_login_cap' );
            add_option( 'pods_files_require_login_cap', ( isset( $params->files_require_login_cap ) ? $params->files_require_login_cap : 'upload_files' ) );

            delete_option( 'pods_disable_file_upload' );
            add_option( 'pods_disable_file_upload', ( isset( $params->disable_file_upload ) ? $params->disable_file_upload : 0 ) );

            delete_option( 'pods_upload_require_login' );
            add_option( 'pods_upload_require_login', ( isset( $params->upload_require_login ) ? $params->upload_require_login : 1 ) );

            delete_option( 'pods_upload_require_login_cap' );
            add_option( 'pods_upload_require_login_cap', ( isset( $params->upload_require_login_cap ) ? $params->upload_require_login_cap : 'upload_files' ) );
        }
        else {
            if ( !method_exists( $this->api, $method->name ) )
                pods_error( 'API method does not exist', $this );

            // Dynamically call the API method
            $params = (array) $params;

            $output = call_user_func( array( $this->api, $method->name ), $params );
        }

        // Output in PHP or JSON format
        if ( 'json' == $method->format && false !== $output )
            $output = json_encode( $output );

        // If output for on-page to go into a textarea
        if ( true === $method->safe )
            $output = esc_textarea( $output );

        if ( !is_bool( $output ) )
            echo $output;

        die(); // KBAI!
    }

    public function admin_ajax_upload () {
        if ( false === headers_sent() ) {
            if ( '' == session_id() )
                @session_start();

            header( 'Content-Type: text/html; charset=' . get_bloginfo( 'charset' ) );
        }

        // Sanitize input
        $params = stripslashes_deep( (array) $_POST );
        foreach ( $params as $key => $value ) {
            if ( 'action' == $key )
                continue;
            unset( $params[ $key ] );
            $params[ str_replace( '_podsfix_', '', $key ) ] = $value;
        }
        if ( !defined( 'PODS_STRICT_MODE' ) || !PODS_STRICT_MODE )
            $params = pods_sanitize( $params );

        $params = (object) $params;

        $methods = array(
            'upload',
        );

        if ( !isset( $params->method ) || !in_array( $params->method, $methods ) || !isset( $params->id ) || empty( $params->id ) )
            pods_error( 'Invalid AJAX request', $this );

        // Flash often fails to send cookies with the POST or upload, so we need to pass it in GET or POST instead
        if ( is_ssl() && empty( $_COOKIE[ SECURE_AUTH_COOKIE ] ) && !empty( $_REQUEST[ 'auth_cookie' ] ) )
            $_COOKIE[ SECURE_AUTH_COOKIE ] = $_REQUEST[ 'auth_cookie' ];
        elseif ( empty( $_COOKIE[ AUTH_COOKIE ] ) && !empty( $_REQUEST[ 'auth_cookie' ] ) )
            $_COOKIE[ AUTH_COOKIE ] = $_REQUEST[ 'auth_cookie' ];
        if ( empty( $_COOKIE[ LOGGED_IN_COOKIE ] ) && !empty( $_REQUEST[ 'logged_in_cookie' ] ) )
            $_COOKIE[ LOGGED_IN_COOKIE ] = $_REQUEST[ 'logged_in_cookie' ];
        global $current_user;
        unset( $current_user );

        /**
         * Access Checking
         */
        $upload_disabled = false;
        if ( defined( 'PODS_DISABLE_FILE_UPLOAD' ) && true === PODS_DISABLE_FILE_UPLOAD )
            $upload_disabled = true;
        elseif ( defined( 'PODS_UPLOAD_REQUIRE_LOGIN' ) && is_bool( PODS_UPLOAD_REQUIRE_LOGIN ) && true === PODS_UPLOAD_REQUIRE_LOGIN && !is_user_logged_in() )
            $upload_disabled = true;
        elseif ( defined( 'PODS_UPLOAD_REQUIRE_LOGIN' ) && !is_bool( PODS_UPLOAD_REQUIRE_LOGIN ) && ( !is_user_logged_in() || !current_user_can( PODS_UPLOAD_REQUIRE_LOGIN ) ) )
            $upload_disabled = true;

        if ( true === $upload_disabled || !isset( $params->_wpnonce ) || false === wp_verify_nonce( $params->_wpnonce, 'pods-' . $params->method . '-' . $params->id ) )
            pods_error( 'Unauthorized request', $this );

        $method = $params->method;

        // Cleaning up $params
        unset( $params->action );
        unset( $params->method );
        unset( $params->_wpnonce );

        /**
         * Upload a new file (advanced - returns URL and ID)
         */
        if ( 'upload' == $method ) {
            $attachment_id = media_handle_upload( 'Filedata', 0 );
            if ( is_object( $attachment_id ) ) {
                $errors = array();

                foreach ( $attachment_id->errors[ 'upload_error' ] as $error_code => $error_message ) {
                    $errors[] = '[' . $error_code . '] ' . $error_message;
                }

                echo 'Error: <div style="color:#FF0000">' . implode( '</div><div>', $errors ) . '</div>';
            }
            else {
                $attachment = get_post( $attachment_id, ARRAY_A );
                $attachment[ 'filename' ] = basename( $attachment[ 'guid' ] );
                $thumb = wp_get_attachment_image_src( $attachment[ 'ID' ], 'thumbnail', true );
                $attachment[ 'thumbnail' ] = $thumb[ 0 ];

                echo json_encode( $attachment );
            }
        }

        die(); // KBAI!
    }

}
