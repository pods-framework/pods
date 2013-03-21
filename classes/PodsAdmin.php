<?php
/**
 * @package Pods
 */
class PodsAdmin {

    /**
     * @var PodsAPI
     */
    private $api;

    /**
     * @var PodsData
     */
    private $data;

    /**
     * Setup and Handle Admin functionality
     *
     * @license http://www.gnu.org/licenses/gpl-2.0.html
     * @since 2.0
     */
    public function __construct () {
        $this->api = pods_api();
        $this->data = pods_data();

        // Scripts / Stylesheets
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_head' ) );

        // AJAX $_POST fix
        add_action( 'admin_init', array( $this, 'admin_init' ), 9 );

        // Menus
        add_action( 'admin_menu', array( $this, 'admin_menu' ), 99 );

        // AJAX for Admin
        add_action( 'wp_ajax_pods_admin', array( $this, 'admin_ajax' ) );
        add_action( 'wp_ajax_nopriv_pods_admin', array( $this, 'admin_ajax' ) );

        // Add Media Bar button for Shortcode
        add_action( 'media_buttons', array( $this, 'media_button' ), 12 );

        // Add the Pods capabilities
        add_filter( 'members_get_capabilities', array( $this, 'admin_capabilities' ) );

        add_action( 'admin_head-media-upload-popup', array( $this, 'register_media_assets' ) );
    }

    /**
     * Init the admin area
     *
     * @since 2.0
     */
    public function admin_init () {
        // Fix for plugins that *don't do it right* so we don't cause issues for users
        if ( defined( 'DOING_AJAX' ) && !empty( $_POST ) && ( in_array( pods_var( 'action', 'get' ), array( 'pods_admin', 'pods_relationship', 'pods_upload', 'pods_admin_components' ) ) || in_array( pods_var( 'action', 'post' ), array( 'pods_admin', 'pods_relationship', 'pods_upload', 'pods_admin_components' ) ) ) ) {
            foreach ( $_POST as $key => $value ) {
                if ( 'action' == $key )
                    continue;

                unset( $_POST[ $key ] );

                $_POST[ '_podsfix_' . $key ] = $value;
            }
        }
    }

    /**
     * Attach requirements to admin header
     *
     * @since 2.0
     */
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
                wp_enqueue_script( 'jquery-qtip' );
                wp_enqueue_script( 'pods-qtip-init' );

                wp_enqueue_script( 'pods' );

                if ( 0 === strpos( $page, 'pods-manage-' ) || 0 === strpos( $page, 'pods-add-new-' ) )
                    wp_enqueue_script( 'post' );
                elseif ( 0 === strpos( $page, 'pods-settings-' ) ) {
                    wp_enqueue_script( 'post' );
                    wp_enqueue_style( 'pods-admin' );
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
                elseif ( 'pods-packages' == $page )
                    wp_enqueue_style( 'pods-wizard' );
                elseif ( 'pods-wizard' == $page || 'pods-upgrade' == $page || ( in_array( $page, array( 'pods', 'pods-add-new' ) ) && in_array( pods_var( 'action', 'get', 'manage' ), array( 'add', 'manage' ) ) ) ) {
                    wp_enqueue_style( 'pods-wizard' );

                    if ( 'pods-upgrade' == $page )
                        wp_enqueue_script( 'pods-upgrade' );
                }
            }
        }
    }

    /**
     * Buld the admin menus
     *
     * @since 2.0
     */
    public function admin_menu () {
        $advanced_content_types = PodsMeta::$advanced_content_types;
        $settings = PodsMeta::$settings;

        $all_pods = $this->api->load_pods( array( 'count' => true ) );

        if ( !PodsInit::$upgrade_needed || ( ( is_super_admin() || current_user_can( 'delete_users' ) ) && 1 == pods_var( 'pods_upgrade_bypass' ) ) ) {
            if ( !empty( $advanced_content_types ) ) {
                $submenu = array();

                $pods_pages = 0;

                foreach ( (array) $advanced_content_types as $pod ) {
                    if ( empty( $pod[ 'fields' ] ) )
                        continue;
                    elseif ( !is_super_admin() && !current_user_can( 'delete_users' ) && !current_user_can( 'pods' ) && !current_user_can( 'pods_content' ) && !current_user_can( 'pods_add_' . $pod[ 'name' ] ) && !current_user_can( 'pods_edit_' . $pod[ 'name' ] ) && !current_user_can( 'pods_delete_' . $pod[ 'name' ] ) )
                        continue;

                    if ( 1 == pods_var( 'show_in_menu', $pod[ 'options' ], 0 ) ) {
                        $page_title = pods_var_raw( 'label', $pod, ucwords( str_replace( '_', ' ', $pod[ 'name' ] ) ), null, true );
                        $page_title = apply_filters( 'pods_admin_menu_page_title', $page_title, $pod );

                        $menu_label = pods_var_raw( 'menu_name', $pod[ 'options' ], $page_title, null, true );
                        $menu_label = apply_filters( 'pods_admin_menu_label', $menu_label, $pod );

                        $singular_label = pods_var_raw( 'label_singular', $pod[ 'options' ], pods_var_raw( 'label', $pod, ucwords( str_replace( '_', ' ', $pod[ 'name' ] ) ), null, true ), null, true );
                        $plural_label = pods_var_raw( 'label', $pod, ucwords( str_replace( '_', ' ', $pod[ 'name' ] ) ), null, true );

                        $menu_icon = pods_evaluate_tags( pods_var_raw( 'menu_icon', $pod[ 'options' ], '', null, true ), true );
                        $menu_location_custom = pods_var( 'menu_location_custom', $pod[ 'options' ], '' );

                        $parent_page = null;

                        if ( is_super_admin() || current_user_can( 'delete_users' ) || current_user_can( 'pods' ) || current_user_can( 'pods_content' ) || current_user_can( 'pods_edit_' . $pod[ 'name' ] ) || current_user_can( 'pods_delete_' . $pod[ 'name' ] ) ) {
                            if ( !empty( $menu_location_custom ) ) {
                                add_submenu_page( $menu_location_custom, $page_title, $menu_label, 'read', 'pods-manage-' . $pod[ 'name' ], array( $this, 'admin_content' ) );

                                continue;
                            }
                            else {
                                $pods_pages++;

                                $parent_page = $page = 'pods-manage-' . $pod[ 'name' ];

                                add_menu_page( $page_title, $menu_label, 'read', $parent_page, '', $menu_icon, '57.' . ( 10 < count( $pods_pages ) ? $pods_pages : '0' . $pods_pages ) );

                                $all_title = $plural_label;
                                $all_label = __( 'All', 'pods' ) . ' ' . $plural_label;

                                if ( $page == pods_var( 'page', 'get' ) ) {
                                    if ( 'edit' == pods_var( 'action', 'get', 'manage' ) )
                                        $all_title = __( 'Edit', 'pods' ) . ' ' . $singular_label;
                                    elseif ( 'add' == pods_var( 'action', 'get', 'manage' ) )
                                        $all_title = __( 'Add New', 'pods' ) . ' ' . $singular_label;
                                }

                                add_submenu_page( $parent_page, $all_title, $all_label, 'read', $page, array( $this, 'admin_content' ) );
                            }
                        }

                        if ( is_super_admin() || current_user_can( 'delete_users' ) || current_user_can( 'pods' ) || current_user_can( 'pods_content' ) || current_user_can( 'pods_add_' . $pod[ 'name' ] ) ) {
                            $page = 'pods-add-new-' . $pod[ 'name' ];

                            if ( null === $parent_page ) {
                                $pods_pages++;

                                $parent_page = $page;

                                add_menu_page( $page_title, $menu_label, 'read', $parent_page, '', $menu_icon, '57.' . ( 10 < count( $pods_pages ) ? $pods_pages : '0' . $pods_pages ) );
                            }

                            $add_title = __( 'Add New', 'pods' ) . ' ' . $singular_label;
                            $add_label = __( 'Add New', 'pods' );

                            add_submenu_page( $parent_page, $add_title, $add_label, 'read', $page, array( $this, 'admin_content' ) );
                        }
                    }
                    else
                        $submenu[] = $pod;
                }

                $submenu = apply_filters( 'pods_admin_menu_secondary_content', $submenu );

                if ( !empty( $submenu ) && ( !defined( 'PODS_DISABLE_CONTENT_MENU' ) || !PODS_DISABLE_CONTENT_MENU ) ) {
                    $parent_page = null;

                    foreach ( $submenu as $item ) {
                        $singular_label = pods_var_raw( 'label_singular', $item[ 'options' ], pods_var_raw( 'label', $item, ucwords( str_replace( '_', ' ', $item[ 'name' ] ) ), null, true ), null, true );
                        $plural_label = pods_var_raw( 'label', $item, ucwords( str_replace( '_', ' ', $item[ 'name' ] ) ), null, true );

                        if ( is_super_admin() || current_user_can( 'delete_users' ) || current_user_can( 'pods' ) || current_user_can( 'pods_content' ) || current_user_can( 'pods_edit_' . $item[ 'name' ] ) || current_user_can( 'pods_delete_' . $item[ 'name' ] ) ) {
                            $page = 'pods-manage-' . $item[ 'name' ];

                            if ( null === $parent_page ) {
                                $parent_page = $page;

                                add_menu_page( 'Pods', 'Pods', 'read', $parent_page, null, PODS_URL . 'ui/images/icon16.png', '58.5' );
                            }

                            $all_title = $plural_label;
                            $all_label = __( 'Manage', 'pods' ) . ' ' . $plural_label;

                            if ( $page == pods_var( 'page', 'get' ) ) {
                                if ( 'edit' == pods_var( 'action', 'get', 'manage' ) )
                                    $all_title = __( 'Edit', 'pods' ) . ' ' . $singular_label;
                                elseif ( 'add' == pods_var( 'action', 'get', 'manage' ) )
                                    $all_title = __( 'Add New', 'pods' ) . ' ' . $singular_label;
                            }

                            add_submenu_page( $parent_page, $all_title, $all_label, 'read', $page, array( $this, 'admin_content' ) );
                        }
                        elseif ( current_user_can( 'pods_add_' . $item[ 'name' ] ) ) {
                            $page = 'pods-add-new-' . $item[ 'name' ];

                            if ( null === $parent_page ) {
                                $parent_page = $page;

                                add_menu_page( 'Pods', 'Pods', 'read', $parent_page, null, PODS_URL . 'ui/images/icon16.png', '58.5' );
                            }

                            $add_title = __( 'Add New', 'pods' ) . ' ' . $singular_label;
                            $add_label = __( 'Manage', 'pods' ) . ' ' . $plural_label;

                            add_submenu_page( $parent_page, $add_title, $add_label, 'read', $page, array( $this, 'admin_content' ) );
                        }
                    }
                }
            }

            if ( !empty( $settings ) ) {
                $settings_submenu = array();

                foreach ( (array) $settings as $pod ) {
                    if ( !is_super_admin() && !current_user_can( 'delete_users' ) && !current_user_can( 'pods' ) && !current_user_can( 'pods_content' ) && !current_user_can( 'pods_edit_' . $pod[ 'name' ] ) )
                        continue;

                    $page_title = pods_var_raw( 'label', $pod, ucwords( str_replace( '_', ' ', $pod[ 'name' ] ) ), null, true );
                    $page_title = apply_filters( 'pods_admin_menu_page_title', $page_title, $pod );

                    $menu_label = pods_var_raw( 'menu_name', $pod[ 'options' ], $page_title, null, true );
                    $menu_label = apply_filters( 'pods_admin_menu_label', $menu_label, $pod );

                    $menu_position = pods_var_raw( 'menu_position', $pod[ 'options' ], '', null, true );
                    $menu_icon = pods_evaluate_tags( pods_var_raw( 'menu_icon', $pod[ 'options' ], '', null, true ), true );

                    if ( empty( $menu_position ) )
                        $menu_position = null;

                    $menu_slug = 'pods-settings-' . $pod[ 'name' ];
                    $menu_location = pods_var( 'menu_location', $pod[ 'options' ], 'settings' );
                    $menu_location_custom = pods_var( 'menu_location_custom', $pod[ 'options' ], '' );

                    if ( 'settings' == $menu_location )
                        add_options_page( $page_title, $menu_label, 'read', $menu_slug, array( $this, 'admin_content_settings' ) );
                    elseif ( 'appearances' == $menu_location )
                        add_theme_page( $page_title, $menu_label, 'read', $menu_slug, array( $this, 'admin_content_settings' ) );
                    elseif ( 'objects' == $menu_location ) {
                        if ( empty( $menu_position ) )
                            add_object_page( $page_title, $menu_label, 'read', $menu_slug, array( $this, 'admin_content_settings' ), $menu_icon );
                        else
                            add_menu_page( $page_title, $menu_label, 'read', $menu_slug, array( $this, 'admin_content_settings' ), $menu_icon, $menu_position );
                    }
                    elseif ( 'top' == $menu_location )
                        add_menu_page( $page_title, $menu_label, 'read', $menu_slug, array( $this, 'admin_content_settings' ), $menu_icon, $menu_position );
                    elseif ( 'submenu' == $menu_location && !empty( $menu_location_custom ) )
                        $settings_submenu[ $menu_location_custom ] = array( $menu_location_custom, $page_title, $menu_label, 'read', $menu_slug, array( $this, 'admin_content_settings' ) );
                }

                foreach ( $settings_submenu as $item ) {
                    call_user_func_array( 'add_submenu_page', $item );
                }
            }

            $admin_menus = array(
                'pods' => array(
                    'label' => __( 'Edit Pods', 'pods' ),
                    'function' => array( $this, 'admin_setup' ),
                    'access' => 'pods'
                ),
                'pods-add-new' => array(
                    'label' => __( 'Add New', 'pods' ),
                    'function' => array( $this, 'admin_setup' ),
                    'access' => 'pods'
                ),
                'pods-components' => array(
                    'label' => __( 'Components', 'pods' ),
                    'function' => array( $this, 'admin_components' ),
                    'access' => 'pods_components'
                ),
                'pods-settings' => array(
                    'label' => __( 'Settings', 'pods' ),
                    'function' => array( $this, 'admin_settings' ),
                    'access' => 'pods_settings'
                ),
                'pods-help' => array(
                    'label' => __( 'Help', 'pods' ),
                    'function' => array( $this, 'admin_help' )
                )
            );

            if ( empty( $all_pods ) )
                unset( $admin_menus[ 'pods' ] );
        }
        else {
            $admin_menus = array(
                'pods-upgrade' => array(
                    'label' => __( 'Upgrade', 'pods' ),
                    'function' => array( $this, 'admin_upgrade' ),
                    'access' => 'manage_options'
                ),
                'pods-settings' => array(
                    'label' => __( 'Settings', 'pods' ),
                    'function' => array( $this, 'admin_settings' ),
                    'access' => 'pods_settings'
                ),
                'pods-help' => array(
                    'label' => __( 'Help', 'pods' ),
                    'function' => array( $this, 'admin_help' )
                )
            );

            add_action( 'admin_notices', array( $this, 'upgrade_notice' ) );
        }

        $admin_menus = apply_filters( 'pods_admin_menu', $admin_menus );

        $parent = false;

        if ( !empty( $admin_menus ) && ( !defined( 'PODS_DISABLE_ADMIN_MENU' ) || !PODS_DISABLE_ADMIN_MENU ) ) {
            foreach ( $admin_menus as $page => $menu_item ) {
                if ( !is_super_admin() && !current_user_can( 'delete_users' ) && isset( $menu_item[ 'access' ] ) ) {
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

                // Don't just show the help page
                if ( false === $parent && 'pods-help' == $page )
                    continue;

                if ( !isset( $menu_item[ 'label' ] ) )
                    $menu_item[ 'label' ] = $page;

                if ( false === $parent ) {
                    $parent = $page;

                    $menu = __( 'Pods Admin', 'pods' );

                    if ( 'pods-upgrade' == $parent )
                        $menu = __( 'Pods Upgrade', 'pods' );

                    add_menu_page( $menu, $menu, 'read', $parent, null, PODS_URL . 'ui/images/icon16.png' );
                }

                add_submenu_page( $parent, $menu_item[ 'label' ], $menu_item[ 'label' ], 'read', $page, $menu_item[ 'function' ] );

                if ( 'pods-components' == $page )
                    PodsInit::$components->menu( $parent );
            }
        }
    }

    public function upgrade_notice () {
        echo '<div class="error fade"><p>';
        echo sprintf(
            __( '<strong>NOTICE:</strong> Pods %s requires your action to complete the upgrade. Please run the <a href="%s">Upgrade Wizard</a>.', 'pods' ),
            PODS_VERSION,
            admin_url( 'admin.php?page=pods-upgrade' )
        );
        echo '</p></div>';
    }

    /**
     * Create PodsUI content for the administration pages
     */
    public function admin_content () {
        $pod_name = str_replace( array( 'pods-manage-', 'pods-add-new-' ), '', $_GET[ 'page' ] );

        $pod = pods( $pod_name, pods_var( 'id', 'get', null, null, true ) );

        $default = 'manage';

        if ( false !== strpos( $_GET[ 'page' ], 'pods-add-new-' ) )
            $default = 'add';

        $actions_enabled = pods_var_raw( 'ui_actions_enabled', $pod->pod_data[ 'options' ] );

        if ( !empty( $actions_enabled ) )
            $actions_enabled = (array) $actions_enabled;
        else
            $actions_enabled = array();

        $available_actions = array(
            'add',
            'edit',
            'duplicate',
            'delete',
            'reorder',
            'export'
        );

        if ( !empty( $actions_enabled ) ) {
            $actions_disabled = array(
                'view' => 'view'
            );

            foreach ( $available_actions as $action ) {
                if ( !in_array( $action, $actions_enabled ) )
                    $actions_disabled[ $action ] = $action;
            }
        }
        else {
            $actions_disabled = array(
                'duplicate' => 'duplicate',
                'view' => 'view',
                'export' => 'export'
            );

            if ( 1 == pods_var( 'ui_export', $pod->pod_data[ 'options' ], 0 ) )
                unset( $actions_disabled[ 'export' ] );
        }

        $author_restrict = false;

        if ( isset( $pod->fields[ 'author' ] ) && 'pick' == $pod->fields[ 'author' ][ 'type' ] && 'user' == $pod->fields[ 'author' ][ 'pick_object' ] )
            $author_restrict = 'author.ID';

        if ( !is_super_admin() && !current_user_can( 'delete_users' ) && !current_user_can( 'pods' ) && !current_user_can( 'pods_content' ) ) {
            if ( !current_user_can( 'pods_add_' . $pod_name ) ) {
                $actions_disabled[ 'add' ] = 'add';
                $default = 'manage';
            }

            if ( !$author_restrict && !current_user_can( 'pods_edit_' . $pod_name ) && !current_user_can( 'pods_edit_others_' . $pod_name ) )
                $actions_disabled[ 'edit' ] = 'edit';

            if ( !$author_restrict && !current_user_can( 'pods_delete_' . $pod_name ) && !current_user_can( 'pods_delete_others_' . $pod_name ) )
                $actions_disabled[ 'delete' ] = 'delete';

            if ( !current_user_can( 'pods_reorder_' . $pod_name ) )
                $actions_disabled[ 'reorder' ] = 'reorder';

            if ( !current_user_can( 'pods_export_' . $pod_name ) )
                $actions_disabled[ 'export' ] = 'export';
        }

        $_GET[ 'action' ] = pods_var( 'action', 'get', $default );

        $index = $pod->pod_data[ 'field_id' ];
        $label = __( 'ID', 'pods' );

        if ( isset( $pod->pod_data[ 'fields' ][ $pod->pod_data[ 'field_index' ] ] ) ) {
            $index = $pod->pod_data[ 'field_index' ];
            $label = $pod->pod_data[ 'fields' ][ $pod->pod_data[ 'field_index' ] ];
        }

        $manage = array(
            $index => $label
        );

        if ( isset( $pod->pod_data[ 'fields' ][ 'modified' ] ) )
            $manage[ 'modified' ] = $pod->pod_data[ 'fields' ][ 'modified' ][ 'label' ];

        $manage_fields = pods_var_raw( 'ui_fields_manage', $pod->pod_data[ 'options' ] );

        if ( !empty( $manage_fields ) ) {
            $manage_new = array();

            foreach ( $manage_fields as $manage_field ) {
                if ( isset( $pod->pod_data[ 'fields' ][ $manage_field ] ) )
                    $manage_new[ $manage_field ] = $pod->pod_data[ 'fields' ][ $manage_field ];
                elseif ( isset( $pod->pod_data[ 'object_fields' ][ $manage_field ] ) )
                    $manage_new[ $manage_field ] = $pod->pod_data[ 'object_fields' ][ $manage_field ];
            }

            if ( !empty( $manage_new ) )
                $manage = $manage_new;
        }

        $manage = apply_filters( 'pods_admin_ui_fields_' . $pod->pod, apply_filters( 'pods_admin_ui_fields', $manage, $pod->pod, $pod ), $pod->pod, $pod );

        $icon = pods_var_raw( 'ui_icon', $pod->pod_data[ 'options' ] );

        if ( !empty( $icon ) )
            $icon = pods_image_url( $icon, '32x32' );

        $filters = pods_var_raw( 'ui_filters', $pod->pod_data[ 'options' ] );

        if ( !empty( $filters ) ) {
            $filters_new = array();

            foreach ( $filters as $filter_field ) {
                if ( isset( $pod->pod_data[ 'fields' ][ $filter_field ] ) )
                    $filters_new[ $filter_field ] = $pod->pod_data[ 'fields' ][ $filter_field ];
                elseif ( isset( $pod->pod_data[ 'object_fields' ][ $filter_field ] ) )
                    $filters_new[ $filter_field ] = $pod->pod_data[ 'object_fields' ][ $filter_field ];
            }

            if ( !empty( $filters_new ) )
                $filters = $filters_new;
        }

        $ui = array(
            'pod' => $pod,
            'fields' => array(
                'manage' => $manage,
                'add' => $pod->pod_data[ 'fields' ],
                'edit' => $pod->pod_data[ 'fields' ],
                'duplicate' => $pod->pod_data[ 'fields' ]
            ),
            'icon' => $icon,
            'actions_disabled' => $actions_disabled
        );

        if ( !empty( $filters ) ) {
            $ui[ 'fields' ][ 'search' ] = $filters;
            $ui[ 'filters' ] = array_keys( $filters );
            $ui[ 'filters_enhanced' ] = true;
        }

        $reorder_field = pods_var_raw( 'ui_reorder_field', $pod->pod_data[ 'options' ] );

        if ( in_array( 'reorder', $actions_enabled ) && !in_array( 'reorder', $actions_disabled ) && !empty( $reorder_field ) && ( ( !empty( $pod->pod_data[ 'object_fields' ] ) && isset( $pod->pod_data[ 'object_fields' ][ $reorder_field ] ) ) || isset( $pod->pod_data[ 'fields' ][ $reorder_field ] ) ) ) {
            $ui[ 'reorder' ] = array( 'on' => $reorder_field );
            $ui[ 'orderby' ] = $reorder_field;
            $ui[ 'orderby_dir' ] = 'ASC';
        }

        if ( !empty( $author_restrict ) )
            $ui[ 'restrict' ] = array( 'author_restrict' => $author_restrict );

        if ( !in_array( 'delete', $ui[ 'actions_disabled' ] ) ) {
            $ui[ 'actions_bulk' ] = array(
                'delete' => array(
                    'label' => __( 'Delete', 'pods' )
                    // callback not needed, Pods has this built-in for delete
                )
            );
        }

        if ( strlen( pods_var( 'detail_url', $pod->pod_data ) ) < 1 ) {
            $ui[ 'actions_custom' ] = array(
                'view_url' => array(
                    'label' => 'View',
                    'link' => get_site_url() . '/' . pods_var( 'detail_url', $pod->pod_data[ 'options' ] )
                )
            );
        }

        // @todo Customize the Add New / Manage links to point to their correct menu items

        $ui = apply_filters( 'pods_admin_ui_' . $pod->pod, apply_filters( 'pods_admin_ui', $ui, $pod->pod, $pod ), $pod->pod, $pod );

        pods_ui( $ui );
    }

    /**
     * Create PodsUI content for the settings administration pages
     */
    public function admin_content_settings () {
        $pod_name = str_replace( 'pods-settings-', '', $_GET[ 'page' ] );

        $pod = pods( $pod_name );

        if ( 'custom' != pods_var( 'ui_style', $pod->pod_data[ 'options' ], 'settings', null, true ) ) {
            $actions_disabled = array(
                'manage' => 'manage',
                'add' => 'add',
                'delete' => 'delete',
                'duplicate' => 'duplicate',
                'view' => 'view',
                'export' => 'export'
            );

            $_GET[ 'action' ] = 'edit';

            $page_title = pods_var_raw( 'label', $pod->pod_data[ 'options' ], ucwords( str_replace( '_', ' ', $pod->pod_data[ 'name' ] ) ), null, true );

            $ui = array(
                'pod' => $pod,
                'fields' => array(
                    'edit' => $pod->pod_data[ 'fields' ]
                ),
                'header' => array(
                    'edit' => $page_title
                ),
                'label' => array(
                    'edit' => __( 'Save Changes', 'pods' )
                ),
                'style' => pods_var( 'ui_style', $pod->pod_data[ 'options' ], 'settings', null, true ),
                'icon' => pods_evaluate_tags( pods_var_raw( 'menu_icon', $pod->pod_data[ 'options' ] ), true ),
                'actions_disabled' => $actions_disabled
            );

            $ui = apply_filters( 'pods_admin_ui_' . $pod->pod, apply_filters( 'pods_admin_ui', $ui, $pod->pod, $pod ), $pod->pod, $pod );

            // Force disabled actions, do not pass go, do not collect $two_hundred
            $ui[ 'actions_disabled' ] = $actions_disabled;

            pods_ui( $ui );
        }
        else {
            do_action( 'pods_admin_ui_custom', $pod );
            do_action( 'pods_admin_ui_custom_' . $pod->pod, $pod );
        }
    }

    /**
     * Add media button for Pods shortcode
     *
     * @param $context
     *
     * @return string
     */
    public function media_button ( $context = null ) {
        $current_page = basename( $_SERVER[ 'PHP_SELF' ] );
        $current_page = explode( '?', $current_page );
        $current_page = explode( '#', $current_page[ 0 ] );
        $current_page = $current_page[ 0 ];

        // Only show the button on post type pages
        if ( !in_array( $current_page, array( 'post-new.php', 'post.php' ) ) )
            return $context;

        add_action( 'admin_footer', array( $this, 'mce_popup' ) );

        echo '<a href="#TB_inline?width=640&inlineId=pods_shortcode_form" class="thickbox" id="add_pod_button" title="Pods Shortcode"><img src="' . PODS_URL . 'ui/images/icon16.png" alt="Pods Shortcode" /></a>';
    }

    /**
     * Enqueue assets for Media Library Popup
     */
    public function register_media_assets () {
        if ( 'pods_media_attachment' == pods_var( 'inlineId', 'get' ) )
            wp_enqueue_style( 'pods-attach' );
    }

    /**
     * Output Pods shortcode popup window
     */
    public function mce_popup () {
        pods_view( PODS_DIR . 'ui/admin/shortcode.php', compact( array_keys( get_defined_vars() ) ) );
    }

    /**
     * Handle main Pods Setup area for managing Pods and Fields
     */
    public function admin_setup () {
        $pods = $this->api->load_pods();

        $view = pods_var( 'view', 'get', 'all', null, true );

        if ( empty( $pods ) && !isset( $_GET[ 'action' ] ) )
            $_GET[ 'action' ] = 'add';

        if ( 'pods-add-new' == $_GET[ 'page' ] ) {
            if ( isset( $_GET[ 'action' ] ) && 'add' != $_GET[ 'action' ] )
                pods_redirect( pods_var_update( array( 'page' => 'pods', 'action' => $_GET[ 'action' ] ) ) );
            else
                $_GET[ 'action' ] = 'add';
        }
        elseif ( isset( $_GET[ 'action' ] ) && 'add' == $_GET[ 'action' ] )
            pods_redirect( pods_var_update( array( 'page' => 'pods-add-new', 'action' => '' ) ) );

        $types = array(
            'post_type' => __( 'Post Type (extended)', 'pods' ),
            'taxonomy' => __( 'Taxonomy (extended)', 'pods' ),
            'cpt' => __( 'Custom Post Type', 'pods' ),
            'ct' => __( 'Custom Taxonomy', 'pods' ),
            'user' => __( 'User (extended)', 'pods' ),
            'media' => __( 'Media (extended)', 'pods' ),
            'comment' => __( 'Comments (extended)', 'pods' ),
            'pod' => __( 'Advanced Content Type', 'pods' ),
            'settings' => __( 'Custom Settings Page', 'pods' )
        );

        $row = false;

        $pod_types_found = array();

        foreach ( $pods as $k => &$pod ) {
            if ( isset( $types[ $pod[ 'type' ] ] ) ) {
                if ( in_array( $pod[ 'type' ], array( 'post_type', 'taxonomy' ) ) ) {
                    if ( empty( $pod[ 'object' ] ) ) {
                        if ( 'post_type' == $pod[ 'type' ] )
                            $pod[ 'type' ] = 'cpt';
                        else
                            $pod[ 'type' ] = 'ct';
                    }
                }

                if ( !isset( $pod_types_found[ $pod[ 'type' ] ] ) )
                    $pod_types_found[ $pod[ 'type' ] ] = 1;
                else
                    $pod_types_found[ $pod[ 'type' ] ]++;

                if ( 'all' != $view && $view != $pod[ 'type' ] ) {
                    unset( $pods[ $k ] );

                    continue;
                }

                $pod[ 'type' ] = $types[ $pod[ 'type' ] ];
            }
            elseif ( 'all' != $view )
                continue;

            $pod[ 'storage' ] = ucwords( $pod[ 'storage' ] );

            if ( $pod[ 'id' ] == pods_var( 'id' ) && 'delete' != pods_var( 'action' ) )
                $row = $pod;
        }

        if ( false === $row && 0 < pods_var( 'id' ) && 'delete' != pods_var( 'action' ) ) {
            pods_message( 'Pod not found', 'error' );

            unset( $_GET[ 'id' ] );
            unset( $_GET[ 'action' ] );
        }

        $ui = array(
            'data' => $pods,
            'row' => $row,
            'total' => count( $pods ),
            'total_found' => count( $pods ),
            'icon' => PODS_URL . 'ui/images/icon32.png',
            'items' => 'Pods',
            'item' => 'Pod',
            'fields' => array(
                'manage' => array(
                    'label' => array( 'label' => __( 'Label', 'pods' ) ),
                    'name' => array( 'label' => __( 'Name', 'pods' ) ),
                    'type' => array( 'label' => __( 'Type', 'pods' ) ),
                    'storage' => array( 'label' => __( 'Storage Type', 'pods' ) )
                )
            ),
            'actions_disabled' => array( 'view', 'export' ),
            'actions_custom' => array(
                'add' => array( $this, 'admin_setup_add' ),
                'edit' => array( $this, 'admin_setup_edit' ),
                'duplicate' => array( $this, 'admin_setup_duplicate' ),
                'reset' => array(
                    'label' => __( 'Delete All Items', 'pods' ),
                    'confirm' => __( 'Are you sure you want to delete all items from this Pod?', 'pods' ),
                    'callback' => array( $this, 'admin_setup_reset' )
                ),
                'delete' => array( $this, 'admin_setup_delete' )
            ),
            'action_links' => array(
                'add' => pods_var_update( array( 'page' => 'pods-add-new', 'action' => '', 'id' => '', 'do' => '' ) )
            ),
            'search' => false,
            'searchable' => false,
            'sortable' => true,
            'pagination' => false
        );

        if ( 1 < count( $pod_types_found ) ) {
            $ui[ 'views' ] = array( 'all' => __( 'All', 'pods' ) );
            $ui[ 'view' ] = $view;
            $ui[ 'heading' ] = array( 'views' => __( 'Type', 'pods' ) );
            $ui[ 'filters_enhanced' ] = true;

            foreach ( $pod_types_found as $pod_type => $number_found ) {
                $ui[ 'views' ][ $pod_type ] = $types[ $pod_type ];
            }
        }

        pods_ui( $ui );
    }

    /**
     * Get the add page of an object
     *
     * @param $obj
     */
    public function admin_setup_add ( $obj ) {
        pods_view( PODS_DIR . 'ui/admin/setup-add.php', compact( array_keys( get_defined_vars() ) ) );
    }

    /**
     * Get the edit page of an object
     *
     * @param $duplicate
     * @param $obj
     */
    public function admin_setup_edit ( $duplicate, $obj ) {
        pods_view( PODS_DIR . 'ui/admin/setup-edit.php', compact( array_keys( get_defined_vars() ) ) );
    }

    /**
     * Duplicate a pod
     *
     * @param $id
     * @param $obj
     *
     * @return mixed
     */
    public function admin_setup_duplicate ( &$obj ) {
        $new_id = $this->api->duplicate_pod( array( 'id' => $obj->id ) );

        if ( 0 < $new_id )
            pods_redirect( pods_var_update( array( 'action' => 'edit', 'id' => $new_id, 'do' => 'duplicate' ) ) );
    }

    /**
     * Reset a pod
     *
     * @param $id
     * @param $obj
     *
     * @return mixed
     */
    public function admin_setup_reset ( &$obj, $id ) {
        $pod = $this->api->load_pod( array( 'id' => $id ), false );

        if ( empty( $pod ) )
            return $obj->error( __( 'Pod not found.', 'pods' ) );

        $this->api->reset_pod( array( 'id' => $id ) );

        $obj->message( __( 'Pod reset successfully.', 'pods' ) );

        $obj->manage();
    }

    /**
     * Delete a pod
     *
     * @param $id
     * @param $obj
     *
     * @return mixed
     */
    public function admin_setup_delete ( $id, &$obj ) {
        $pod = $this->api->load_pod( array( 'id' => $id ), false );

        if ( empty( $pod ) )
            return $obj->error( __( 'Pod not found.', 'pods' ) );

        $this->api->delete_pod( array( 'id' => $id ) );

        unset( $obj->data[ $pod[ 'id' ] ] );

        $obj->total = count( $obj->data );
        $obj->total_found = count( $obj->data );

        $obj->message( __( 'Pod deleted successfully.', 'pods' ) );
    }

    /**
     * Get advanced administration view.
     */
    public function admin_advanced () {
        pods_view( PODS_DIR . 'ui/admin/advanced.php', compact( array_keys( get_defined_vars() ) ) );
    }

    /**
     * Get settings administration view
     */
    public function admin_settings () {
        pods_view( PODS_DIR . 'ui/admin/settings.php', compact( array_keys( get_defined_vars() ) ) );
    }

    /**
     * Get components administration UI
     */
    public function admin_components () {
        $components = PodsInit::$components->components;

        $view = pods_var( 'view', 'get', 'all', null, true );

        foreach ( $components as $component => &$component_data ) {
            if ( 'all' != $view && ( !isset( $component_data[ 'Category' ] ) || $view != sanitize_title( $component_data[ 'Category' ] ) ) ) {
                unset( $components[ $component ] );

                continue;
            }

            $component_data[ 'Name' ] = strip_tags( $component_data[ 'Name' ] );

            $meta = array();

            if ( !empty( $component_data[ 'Version' ] ) )
                $meta[] = 'Version ' . $component_data[ 'Version' ];

            if ( empty( $component_data[ 'Author' ] ) ) {
                $component_data[ 'Author' ] = 'Pods Framework Team';
                $component_data[ 'AuthorURI' ] = 'http://pods.io/';
            }

            if ( !empty( $component_data[ 'AuthorURI' ] ) )
                $component_data[ 'Author' ] = '<a href="' . $component_data[ 'AuthorURI' ] . '">' . $component_data[ 'Author' ] . '</a>';

            $meta[] = sprintf( __( 'by %s', 'pods' ), $component_data[ 'Author' ] );

            if ( !empty( $component_data[ 'URI' ] ) )
                $meta[] = '<a href="' . $component_data[ 'URI' ] . '">' . __( 'Visit component site', 'pods' ) . '</a>';

            $component_data[ 'Description' ] = wpautop( make_clickable( strip_tags( $component_data[ 'Description' ], 'em,strong' ) ) );

            if ( !empty( $meta ) )
                $component_data[ 'Description' ] .= '<div class="pods-component-version-author-uri">' . implode( ' | ', $meta ) . '</div>';

            $component_data = array(
                'id' => $component_data[ 'ID' ],
                'name' => $component_data[ 'Name' ],
                'category' => $component_data[ 'Category' ],
                'description' => $component_data[ 'Description' ],
                'developermode' => pods_var_raw( 'DeveloperMode', $component_data, false ),
                'mustuse' => pods_var_raw( 'MustUse', $component_data, false ),
                'toggle' => 0
            );

            if ( $component_data[ 'developermode' ] ) {
                if ( !pods_developer() ) {
                    unset( $components[ $component ] );

                    continue;
                }

                $component_data[ 'name' ] .= ' <em style="font-weight: normal;">(Developer Preview)</em>';
            }

            if ( isset( PodsInit::$components->settings[ 'components' ][ $component_data[ 'id' ] ] ) && 0 != PodsInit::$components->settings[ 'components' ][ $component_data[ 'id' ] ] )
                $component_data[ 'toggle' ] = 1;
            elseif ( $component_data[ 'mustuse' ] )
                $component_data[ 'toggle' ] = 1;
        }

        $ui = array(
            'data' => $components,
            'total' => count( $components ),
            'total_found' => count( $components ),
            'icon' => PODS_URL . 'ui/images/icon32.png',
            'items' => 'Components',
            'item' => 'Component',
            'fields' => array(
                'manage' => array(
                    'name' => array(
                        'label' => __( 'Name', 'pods' ),
                        'width' => '30%',
                        'type' => 'text'
                    ),
                    'category' => array(
                        'label' => __( 'Category', 'pods' ),
                        'width' => '10%',
                        'type' => 'text'
                    ),
                    'description' => array(
                        'label' => __( 'Description', 'pods' ),
                        'width' => '60%',
                        'type' => 'text'
                    )
                )
            ),
            'actions_disabled' => array( 'duplicate', 'view', 'export', 'add', 'edit', 'delete' ),
            'actions_custom' => array(
                'toggle' => array( 'callback' => array( $this, 'admin_components_toggle' ) )
            ),
            'filters_enhanced' => true,
            'views' => array(
                'all' => __( 'All', 'pods' ),
                'field-types' => __( 'Field Types', 'pods' ),
                'tools' => __( 'Tools', 'pods' ),
                'integration' => __( 'Integration', 'pods' ),
                'migration' => __( 'Migration', 'pods' ),
                'advanced' => __( 'Advanced', 'pods' )
            ),
            'view' => $view,
            'heading' => array(
                'views' => __( 'Category', 'pods' )
            ),
            'search' => false,
            'searchable' => false,
            'sortable' => false,
            'pagination' => false
        );

        pods_ui( $ui );
    }

    /**
     * Toggle a component on or off
     *
     * @param PodsUI $ui
     *
     * @return bool
     */
    public function admin_components_toggle ( PodsUI $ui ) {
        $component = $_GET[ 'id' ];

        if ( !empty( PodsInit::$components->components[ $component ][ 'PluginDependency' ] ) ) {
            $dependency = explode( '|', PodsInit::$components->components[ $component ][ 'PluginDependency' ] );

            if ( !pods_is_plugin_active( $dependency[ 1 ] ) ) {
                $website = 'http://wordpress.org/extend/plugins/' . dirname( $dependency[ 1 ] ) . '/';

                if ( isset( $dependency[ 2 ] ) )
                    $website = $dependency[ 2 ];

                if ( !empty( $website ) )
                    $website = ' ' . sprintf( __( 'You can find it at %s', 'pods' ), '<a href="' . $website . '" target="_blank">' . $website . '</a>' );

                $message = sprintf( __( 'The %s component requires that you have the <strong>%s</strong> plugin installed and activated.', 'pods' ), PodsInit::$components->components[ $component ][ 'Name' ], $dependency[ 0 ] ) . $website;

                $ui->error( $message );

                $ui->manage();

                return;
            }
        }

        if ( !empty( PodsInit::$components->components[ $component ][ 'ThemeDependency' ] ) ) {
            $dependency = explode( '|', PodsInit::$components->components[ $component ][ 'ThemeDependency' ] );

            if ( strtolower( $dependency[ 1 ] ) != strtolower( get_template() ) && strtolower( $dependency[ 1 ] ) != strtolower( get_stylesheet() ) ) {
                $website = '';

                if ( isset( $dependency[ 2 ] ) )
                    $website = ' ' . sprintf( __( 'You can find it at %s', 'pods' ), '<a href="' . $dependency[ 2 ] . '" target="_blank">' . $dependency[ 2 ] . '</a>' );

                $message = sprintf( __( 'The %s component requires that you have the <strong>%s</strong> theme installed and activated.', 'pods' ), PodsInit::$components->components[ $component ][ 'Name' ], $dependency[ 0 ] ) . $website;

                $ui->error( $message );

                $ui->manage();

                return;
            }
        }

        if ( !empty( PodsInit::$components->components[ $component ][ 'MustUse' ] ) ) {
            $message = sprintf( __( 'The %s component can not be disabled from here. You must deactivate the plugin or theme that added it.', 'pods' ), PodsInit::$components->components[ $component ][ 'Name' ] );

            $ui->error( $message );

            $ui->manage();

            return;
        }

        if ( 1 == pods_var( 'toggled' ) ) {
            $toggle = PodsInit::$components->toggle( $component );

            if ( true === $toggle )
                $ui->message( PodsInit::$components->components[ $component ][ 'Name' ] . ' ' . __( 'Component enabled', 'pods' ) );
            elseif ( false === $toggle )
                $ui->message( PodsInit::$components->components[ $component ][ 'Name' ] . ' ' . __( 'Component disabled', 'pods' ) );

            $components = PodsInit::$components->components;

            foreach ( $components as $component => &$component_data ) {
                $toggle = 0;

                if ( isset( PodsInit::$components->settings[ 'components' ][ $component_data[ 'ID' ] ] ) ) {
                    if ( 0 != PodsInit::$components->settings[ 'components' ][ $component_data[ 'ID' ] ] )
                        $toggle = 1;
                }
                if ( true === $component_data[ 'DeveloperMode' ] ) {
                    if ( !pods_developer() ) {
                        unset( $components[ $component ] );
                        continue;
                    }
                }

                $component_data = array(
                    'id' => $component_data[ 'ID' ],
                    'name' => $component_data[ 'Name' ],
                    'description' => make_clickable( $component_data[ 'Description' ] ),
                    'version' => $component_data[ 'Version' ],
                    'author' => $component_data[ 'Author' ],
                    'toggle' => $toggle
                );
            }

            $ui->data = $components;

            pods_transient_clear( 'pods_components' );

            $url = pods_var_update( array( 'toggled' => null ) );

            pods_redirect( $url );
        }
        elseif ( 1 == pods_var( 'toggle' ) )
            $ui->message( PodsInit::$components->components[ $component ][ 'Name' ] . ' ' . __( 'Component enabled', 'pods' ) );
        else
            $ui->message( PodsInit::$components->components[ $component ][ 'Name' ] . ' ' . __( 'Component disabled', 'pods' ) );

        $ui->manage();
    }

    /**
     * Get the admin upgrade page
     */
    public function admin_upgrade () {
        foreach ( PodsInit::$upgrades as $old_version => $new_version ) {
            if ( version_compare( $old_version, PodsInit::$version_last, '<=' ) && version_compare( PodsInit::$version_last, $new_version, '<' ) ) {
                $new_version = str_replace( '.', '_', $new_version );

                pods_view( PODS_DIR . 'ui/admin/upgrade/upgrade_' . $new_version . '.php', compact( array_keys( get_defined_vars() ) ) );

                break;
            }
        }
    }

    /**
     * Get the admin help page
     */
    public function admin_help () {
        pods_view( PODS_DIR . 'ui/admin/help.php', compact( array_keys( get_defined_vars() ) ) );
    }

    /**
     * Add pods specific capabilities.
     *
     * @param $capabilities List of extra capabilities to add
     *
     * @return array
     */
    public function admin_capabilities ( $capabilities ) {
        $pods = $this->api->load_pods();

        $capabilities[] = 'pods';
        $capabilities[] = 'pods_content';
        $capabilities[] = 'pods_settings';
        $capabilities[] = 'pods_components';

        foreach ( $pods as $pod ) {
            if ( !in_array( $pod[ 'type' ], array( 'pod', 'table', 'post_type' ) ) )
                continue;

            if ( 'post_type' == $pod[ 'type' ] ) {
                $capability_type = pods_var( 'capability_type_custom', $pod[ 'options' ], pods_var_raw( 'name', $pod ) );

                if ( 'custom' == pods_var( 'capability_type', $pod[ 'options' ] ) && 0 < strlen( $capability_type ) ) {
                    $capabilities[] = 'read_' . $capability_type;
                    $capabilities[] = 'read_private_' . $capability_type . 's';
                    $capabilities[] = 'edit_' . $capability_type;
                    $capabilities[] = 'edit_' . $capability_type . 's';
                    $capabilities[] = 'edit_others_' . $capability_type . 's';
                    $capabilities[] = 'edit_private_' . $capability_type . 's';
                    $capabilities[] = 'edit_published_' . $capability_type . 's';
                    $capabilities[] = 'publish_' . $capability_type . 's';
                    $capabilities[] = 'delete_' . $capability_type;
                    $capabilities[] = 'delete_' . $capability_type . 's';
                    $capabilities[] = 'delete_private_' . $capability_type . 's';
                    $capabilities[] = 'delete_published_' . $capability_type . 's';
                    $capabilities[] = 'delete_others_' . $capability_type . 's';
                }
            }
            else {
                $capabilities[] = 'pods_add_' . $pod[ 'name' ];
                $capabilities[] = 'pods_edit_' . $pod[ 'name' ];

                if ( isset( $pod[ 'fields' ][ 'author' ] ) && 'pick' == $pod[ 'fields' ][ 'author' ][ 'type' ] && 'user' == $pod[ 'fields' ][ 'author' ][ 'pick_object' ] )
                    $capabilities[] = 'pods_edit_others_' . $pod[ 'name' ];

                $capabilities[] = 'pods_delete_' . $pod[ 'name' ];

                if ( isset( $pod[ 'fields' ][ 'author' ] ) && 'pick' == $pod[ 'fields' ][ 'author' ][ 'type' ] && 'user' == $pod[ 'fields' ][ 'author' ][ 'pick_object' ] )
                    $capabilities[] = 'pods_delete_others_' . $pod[ 'name' ];

                $actions_enabled = pods_var_raw( 'ui_actions_enabled', $pod[ 'options' ] );

                if ( !empty( $actions_enabled ) )
                    $actions_enabled = (array) $actions_enabled;
                else
                    $actions_enabled = array();

                $available_actions = array(
                    'add',
                    'edit',
                    'duplicate',
                    'delete',
                    'reorder',
                    'export'
                );

                if ( !empty( $actions_enabled ) ) {
                    $actions_disabled = array(
                        'view' => 'view'
                    );

                    foreach ( $available_actions as $action ) {
                        if ( !in_array( $action, $actions_enabled ) )
                            $actions_disabled[ $action ] = $action;
                    }

                    if ( !in_array( 'export', $actions_disabled ) )
                        $capabilities[] = 'pods_export_' . $pod[ 'name' ];

                    if ( !in_array( 'reorder', $actions_disabled ) )
                        $capabilities[] = 'pods_reorder_' . $pod[ 'name' ];
                }
                elseif ( 1 == pods_var( 'ui_export', $pod[ 'options' ], 0 ) )
                    $capabilities[] = 'pods_export_' . $pod[ 'name' ];
            }
        }

        return $capabilities;
    }

    /**
     * Handle ajax calls for the administration
     */
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

        $params = (object) $params;

        $methods = array(
            'add_pod' => array( 'priv' => true ),
            'save_pod' => array( 'priv' => true ),
            'load_sister_fields' => array( 'priv' => true ),
            'process_form' => array( 'custom_nonce' => true ), // priv handled through nonce
            'upgrade' => array( 'priv' => true )
        );

        $methods = apply_filters( 'pods_admin_ajax_methods', $methods, $this );

        if ( !isset( $params->method ) || !isset( $methods[ $params->method ] ) )
            pods_error( 'Invalid AJAX request', $this );

        $defaults = array(
            'priv' => null,
            'name' => $params->method,
            'custom_nonce' => null
        );

        $method = (object) array_merge( $defaults, (array) $methods[ $params->method ] );

        if ( true !== $method->custom_nonce && ( !isset( $params->_wpnonce ) || false === wp_verify_nonce( $params->_wpnonce, 'pods-' . $params->method ) ) )
            pods_error( __( 'Unauthorized request', 'pods' ), $this );

        // Cleaning up $params
        unset( $params->action );
        unset( $params->method );

        if ( true !== $method->custom_nonce )
            unset( $params->_wpnonce );

        // Check permissions (convert to array to support multiple)
        if ( !empty( $method->priv ) && !is_super_admin() && !current_user_can( 'delete_users' ) && !current_user_can( 'pods' ) ) {
            if ( true !== $method->priv ) {
                foreach ( (array) $method->priv as $priv_val ) {
                    if ( !current_user_can( $priv_val ) )
                        pods_error( __( 'Access denied', 'pods' ), $this );
                }
            }
        }

        $params->method = $method->name;

        $params = apply_filters( 'pods_api_' . $method->name, $params, $method );

        if ( 'upgrade' == $method->name )
            $output = (string) pods_upgrade( $params->version )->ajax( $params );
        else {
            if ( !method_exists( $this->api, $method->name ) )
                pods_error( 'API method does not exist', $this );
            elseif ( 'save_pod' == $method->name ) {
                if ( isset( $params->field_data_json ) && is_array( $params->field_data_json ) ) {
                    $params->fields = $params->field_data_json;

                    unset( $params->field_data_json );

                    foreach ( $params->fields as $k => $v ) {
                        if ( empty( $v ) )
                            unset( $params->fields[ $k ] );
                        elseif ( !is_array( $v ) )
                            $params->fields[ $k ] = (array) @json_decode( $v, true );
                    }
                }
            }

            // Dynamically call the API method
            $params = (array) $params;

            $output = call_user_func( array( $this->api, $method->name ), $params );
        }

        // Output in json format
        if ( false !== $output ) {
            if ( is_array( $output ) || is_object( $output ) )
                wp_send_json( $output );
            else
                echo $output;
        }
        else
            pods_error( 'There was a problem with your request.' );

        die(); // KBAI!
    }
}
