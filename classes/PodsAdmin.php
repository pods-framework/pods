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
        add_action( 'admin_menu', array( $this, 'admin_menu' ), 99 );
        add_action( 'admin_init', array( $this, 'admin_init' ), 9 );
        if ( is_admin() ) {
            add_action( 'wp_ajax_pods_admin', array( $this, 'admin_ajax' ) );
            add_action( 'wp_ajax_nopriv_pods_admin', array( $this, 'admin_ajax' ) );

            add_action( 'wp_ajax_pods_upload', array( $this, 'admin_ajax_upload' ) );
            add_action( 'wp_ajax_nopriv_pods_upload', array( $this, 'admin_ajax_upload' ) );

            add_filter( 'media_buttons_context', array( $this, 'media_button' ) );
        }

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

                if ( false !== strpos( $page, 'pods-manage-' ) && 0 === strpos( $page, 'pods-manage-' ) ) {
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
                    wp_enqueue_style('jquery-select2');

                    wp_enqueue_script( 'pods-file-attach' );
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
            }
        }
    }

    public function admin_menu () {
        global $pods_components;

        $submenu = array();

        $results = $this->api->load_pods( array( //'options' => array('disable_manage' => 0),
            'type' => 'pod'
        ) );

        $can_manage = pods_access( 'manage_content' );

        if ( !defined( 'PODS_DEVELOPER' ) || !PODS_DEVELOPER )
            $results = false; // not yet!

        if ( false !== $results ) {
            foreach ( (array) $results as $item ) {
                if ( !pods_access( 'pod_' . $item[ 'name' ] ) && !$can_manage )
                    continue;

                $item[ 'options' ][ 'label' ] = ( !empty( $item[ 'options' ][ 'label' ] ) ) ? $item[ 'options' ][ 'label' ] : ucwords( str_replace( '_', ' ', $item[ 'name' ] ) );
                $item[ 'options' ][ 'label' ] = apply_filters( 'pods_admin_menu_label', $item[ 'options' ][ 'label' ], $item );

                if ( 1 == $item[ 'options' ][ 'is_toplevel' ] ) {
                    add_object_page( $item[ 'options' ][ 'label' ], $item[ 'options' ][ 'label' ], 'read', "pods-manage-{$item['name']}" );

                    add_submenu_page( "pods-manage-{$item['name']}", 'Edit', 'Edit', 'read', "pods-manage-{$item['name']}", array(
                        $this,
                        'admin_content'
                    ) );

                    add_submenu_page( "pods-manage-{$item['name']}", 'Add New', 'Add New', 'read', "pods-add-new-{$item['name']}", array(
                        $this,
                        'admin_content'
                    ) );
                }
                else
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

                    add_submenu_page( $parent, "Manage {$item['options']['label']}", "Manage {$item['options']['label']}", 'read', $page, array(
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
                'access' => 'manage_pods'
            ),
            'pods-components' => array(
                'label' => 'Components',
                'function' => array( $this, 'admin_components' ),
                'access' => 'manage_components'
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
                    'access' => 'manage_pods'
                ),
                /*'pods-ui' => array('label' => 'Admin UI',
'function' => array($this, 'admin_ui'),
'access' => array('admin_setup',
                'admin_ui')),*/
                'pods-advanced' => array(
                    'label' => 'Advanced',
                    'function' => array( $this, 'admin_advanced' ),
                    'access' => array(
                        'manage_templates',
                        'manage_pod_pages',
                        'manage_helpers',
                        'manage_roles'
                    )
                ),
                'pods-settings' => array(
                    'label' => 'Settings',
                    'function' => array( $this, 'admin_settings' ),
                    'access' => 'manage_settings'
                ),
                'pods-packages' => array(
                    'label' => 'Packages',
                    'function' => array( $this, 'admin_packages' ),
                    'access' => 'manage_packages'
                ),
                'pods-components' => array(
                    'label' => 'Components',
                    'function' => array( $this, 'admin_components' ),
                    'access' => 'manage_components'
                ),
                'pods-help' => array(
                    'label' => 'Help',
                    'function' => array( $this, 'admin_help' )
                )
            );
        }
        $admin_menus = apply_filters( 'pods_admin_menu', $admin_menus );

        $parent = false;
        foreach ( $admin_menus as $page => $menu_item ) {
            if ( isset( $menu_item[ 'access' ] ) && !pods_access( $menu_item[ 'access' ] ) )
                continue;
            if ( !isset( $menu_item[ 'label' ] ) )
                $menu_item[ 'label' ] = $page;
            if ( false === $parent ) {
                $parent = $page;
                add_menu_page( 'Pods Admin', 'Pods Admin', 'manage_options', $parent, null, PODS_URL . '/ui/images/icon16.png' );
            }
            add_submenu_page( $parent, $menu_item[ 'label' ], $menu_item[ 'label' ], 'manage_options', $page, $menu_item[ 'function' ] );
            if ( 'pods-components' == $page && defined( 'PODS_DEVELOPER' ) )
                $pods_components->menu( $parent );
        }
    }

    public function admin_content () {
        $pod = str_replace( 'pods-manage-', '', $_GET[ 'page' ] );
        pods_ui( array(
            'pod' => $pod,
            'actions_custom' => array( 'form' => array( $this, 'admin_content_form' ) )
        ) );
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

        unset( $obj->data[ $pod[ 'name' ] ] );

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
        global $pods_components;

        $components = $pods_components->components;

        foreach ( $components as $component => &$component_data ) {
            $component_data = array(
                'id' => $component_data[ 'ID' ],
                'name' => $component_data[ 'Name' ],
                'description' => make_clickable( $component_data[ 'Description' ] ),
                'version' => $component_data[ 'Version' ],
                'author' => $component_data[ 'Author' ],
                'toggle' => 0
            );

            if ( isset( $pods_components->settings[ 'components' ][ $component_data[ 'id' ] ] ) && 0 != $pods_components->settings[ 'components' ][ $component_data[ 'id' ] ] )
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
        global $pods_components;

        $component = str_replace( 'pods-component-', '', $_GET[ 'page' ] );

        $pods_components->admin( $component );
    }

    public function admin_components_toggle ( PodsUI $ui ) {
        global $pods_components;

        $component = $_GET[ 'id' ];

        if ( !empty( $pods_components->components[ $component ][ 'PluginDependency' ] ) ) {
            $dependency = explode( '|', $pods_components->components[ $component ][ 'PluginDependency' ] );

            if ( !is_plugin_active( $dependency[ 1 ] ) ) {
                $website = 'http://wordpress.org/extend/plugins/' . dirname( $dependency[ 1 ] ) . '/';

                if ( isset( $dependency[ 2 ] ) )
                    $website = $dependency[ 2 ];

                $message = sprintf( __( 'This component requires that you have the <strong>%s</strong> plugin installed and activated. You can find it at %s', 'pods' ), $dependency[ 0 ], '<a href="' . $website . '" target="_blank">' . $website . '</a>' );

                return $ui->error( $message );
            }
        }

        $toggle = $pods_components->toggle( $component );

        if ( $toggle )
            $ui->message( $pods_components->components[ $component ][ 'Name' ] . ' ' . __( 'Component enabled', 'pods' ) );
        else
            $ui->message( $pods_components->components[ $component ][ 'Name' ] . ' ' . __( 'Component disabled', 'pods' ) );

        $components = $pods_components->components;

        foreach ( $components as $component => &$component_data ) {
            $component_data = array(
                'id' => $component_data[ 'ID' ],
                'name' => $component_data[ 'Name' ],
                'description' => make_clickable( $component_data[ 'Description' ] ),
                'version' => $component_data[ 'Version' ],
                'author' => $component_data[ 'Author' ],
                'toggle' => ( 0 != $pods_components->settings[ 'components' ][ $component_data[ 'ID' ] ] ? 1 : 0 )
            );
        }

        $ui->data = $components;

        $ui->manage();
    }

    public function admin_help () {
        require_once PODS_DIR . 'ui/admin/help.php';
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
        );

        $methods = apply_filters( 'pods_admin_ajax_methods', $methods, $this );

        if ( !isset( $params->method ) || !isset( $methods[ $params->method ] ) )
            pods_error( 'Invalid AJAX request', $this );

        if ( !method_exists( $this->api, $params->method ) )
            pods_error( 'API method does not exist', $this );

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

        $params = apply_filters( 'pods_api_' . $method->name, $params, $method );

        if ( 'security_settings' == $method->name ) {
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
            // Dynamically call the API method
            $params = (array) $params;
            $output = $this->api->{$method->name}( $params );
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
