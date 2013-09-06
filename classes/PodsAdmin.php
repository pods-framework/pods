<?php
/**
 * @package Pods
 */
class PodsAdmin {

    /**
     * @var PodsAdmin
     */
    static $instance = null;

    /**
     * Singleton handling for a basic pods_admin() request
     *
     * @return \PodsAdmin
     *
     * @since 2.3.5
     */
    public static function init () {
        if ( !is_object( self::$instance ) )
            self::$instance = new PodsAdmin();

        return self::$instance;
    }

    /**
     * Setup and Handle Admin functionality
     *
     * @return \PodsAdmin
     *
     * @license http://www.gnu.org/licenses/gpl-2.0.html
     * @since 2.0
     */
    public function __construct () {
        // Scripts / Stylesheets
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_head' ), 20 );

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
        if ( defined( 'DOING_AJAX' ) && !empty( $_POST ) ) {
            $pods_admin_ajax_actions = array(
                'pods_admin',
                'pods_relationship',
                'pods_upload',
                'pods_admin_components'
            );

            $pods_admin_ajax_actions = apply_filters( 'pods_admin_ajax_actions', $pods_admin_ajax_actions );

            if ( in_array( pods_var( 'action', 'get' ), $pods_admin_ajax_actions ) || in_array( pods_var( 'action', 'post' ), $pods_admin_ajax_actions ) ) {
                foreach ( $_POST as $key => $value ) {
                    if ( 'action' == $key || 0 === strpos( $key, '_podsfix_' ) )
                        continue;

                    unset( $_POST[ $key ] );

                    $_POST[ '_podsfix_' . $key ] = $value;
                }
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

        wp_register_script( 'pods-migrate', PODS_URL . 'ui/js/jquery.pods.migrate.js', array(), PODS_VERSION );

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
        $taxonomies = PodsMeta::$taxonomies;
        $settings = PodsMeta::$settings;

        $all_pods = pods_api()->load_pods( array( 'count' => true ) );

        if ( !PodsInit::$upgrade_needed || ( pods_is_admin() && 1 == pods_var( 'pods_upgrade_bypass' ) ) ) {
            $submenu_items = array();

            if ( !empty( $advanced_content_types ) ) {
                $submenu = array();

                $pods_pages = 0;

                foreach ( (array) $advanced_content_types as $pod ) {
                    if ( !isset( $pod[ 'name' ] ) || !isset( $pod[ 'options' ] ) || empty( $pod[ 'fields' ] ) )
                        continue;
                    elseif ( !pods_is_admin( array( 'pods', 'pods_content', 'pods_add_' . $pod[ 'name' ], 'pods_edit_' . $pod[ 'name' ], 'pods_delete_' . $pod[ 'name' ] ) ) )
                        continue;

                    if ( 1 == pods_var( 'show_in_menu', $pod[ 'options' ], 0 ) ) {
                        $page_title = pods_var_raw( 'label', $pod, ucwords( str_replace( '_', ' ', $pod[ 'name' ] ) ), null, true );
                        $page_title = apply_filters( 'pods_admin_menu_page_title', $page_title, $pod );

                        $menu_label = pods_var_raw( 'menu_name', $pod[ 'options' ], $page_title, null, true );
                        $menu_label = apply_filters( 'pods_admin_menu_label', $menu_label, $pod );

                        $singular_label = pods_var_raw( 'label_singular', $pod[ 'options' ], pods_var_raw( 'label', $pod, ucwords( str_replace( '_', ' ', $pod[ 'name' ] ) ), null, true ), null, true );
                        $plural_label = pods_var_raw( 'label', $pod, ucwords( str_replace( '_', ' ', $pod[ 'name' ] ) ), null, true );

                        $menu_location = pods_var( 'menu_location', $pod[ 'options' ], 'objects' );
                        $menu_location_custom = pods_var( 'menu_location_custom', $pod[ 'options' ], '' );

                        $menu_position = pods_var_raw( 'menu_position', $pod[ 'options' ], '', null, true );
                        $menu_icon = pods_evaluate_tags( pods_var_raw( 'menu_icon', $pod[ 'options' ], '', null, true ), true );

                        if ( empty( $menu_position ) )
                            $menu_position = null;

                        $parent_page = null;

                        if ( pods_is_admin( array( 'pods', 'pods_content', 'pods_edit_' . $pod[ 'name' ], 'pods_delete_' . $pod[ 'name' ] ) ) ) {
                            if ( !empty( $menu_location_custom ) ) {
                                if ( !isset( $submenu_items[ $menu_location_custom ] ) )
                                    $submenu_items[ $menu_location_custom ] = array();

                                $submenu_items[ $menu_location_custom ][] = array( $menu_location_custom, $page_title, $menu_label, 'read', 'pods-manage-' . $pod[ 'name' ], array( $this, 'admin_content' ) );

                                continue;
                            }
                            else {
                                $pods_pages++;

                                $parent_page = $page = 'pods-manage-' . $pod[ 'name' ];

                                if ( empty( $menu_position ) )
                                    add_object_page( $page_title, $menu_label, 'read', $parent_page, '', $menu_icon );
                                else
                                    add_menu_page( $page_title, $menu_label, 'read', $parent_page, '', $menu_icon, $menu_position );

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

                        if ( pods_is_admin( array( 'pods', 'pods_content', 'pods_add_' . $pod[ 'name' ] ) ) ) {
                            $page = 'pods-add-new-' . $pod[ 'name' ];

                            if ( null === $parent_page ) {
                                $pods_pages++;

                                $parent_page = $page;

                                if ( empty( $menu_position ) )
                                    add_object_page( $page_title, $menu_label, 'read', $parent_page, '', $menu_icon );
                                else
                                    add_menu_page( $page_title, $menu_label, 'read', $parent_page, '', $menu_icon, $menu_position );
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

                        if ( pods_is_admin( array( 'pods', 'pods_content', 'pods_edit_' . $item[ 'name' ], 'pods_delete_' . $item[ 'name' ] ) ) ) {
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

            if ( !empty( $taxonomies ) ) {
                foreach ( (array) $taxonomies as $pod ) {
                    if ( !pods_is_admin( array( 'pods', 'pods_content', 'pods_edit_' . $pod[ 'name' ] ) ) )
                        continue;

                    $page_title = pods_var_raw( 'label', $pod, ucwords( str_replace( '_', ' ', $pod[ 'name' ] ) ), null, true );
                    $page_title = apply_filters( 'pods_admin_menu_page_title', $page_title, $pod );

                    $menu_label = pods_var_raw( 'menu_name', $pod[ 'options' ], $page_title, null, true );
                    $menu_label = apply_filters( 'pods_admin_menu_label', $menu_label, $pod );

                    $menu_position = pods_var_raw( 'menu_position', $pod[ 'options' ], '', null, true );
                    $menu_icon = pods_evaluate_tags( pods_var_raw( 'menu_icon', $pod[ 'options' ], '', null, true ), true );

                    if ( empty( $menu_position ) )
                        $menu_position = null;

                    $menu_slug = 'edit-tags.php?taxonomy=' . $pod[ 'name' ];
                    $menu_location = pods_var( 'menu_location', $pod[ 'options' ], 'default' );
                    $menu_location_custom = pods_var( 'menu_location_custom', $pod[ 'options' ], '' );

                    if ( 'default' == $menu_location )
                        continue;

                    $taxonomy_data = get_taxonomy( $pod[ 'name' ] );

                    foreach ( (array) $taxonomy_data->object_type as $post_type ) {
                        if ( 'post' == $post_type )
                            remove_submenu_page( 'edit.php', 'edit-tags.php?taxonomy=' . $pod[ 'name' ] );
                        elseif ( 'attachment' == $post_type )
                            remove_submenu_page( 'upload.php', 'edit-tags.php?taxonomy=' . $pod[ 'name' ] . '&amp;post_type=' . $post_type );
                        else
                            remove_submenu_page( 'edit.php?post_type=' . $post_type, 'edit-tags.php?taxonomy=' . $pod[ 'name' ] . '&amp;post_type=' . $post_type );
                    }

                    if ( 'settings' == $menu_location )
                        add_options_page( $page_title, $menu_label, 'read', $menu_slug );
                    elseif ( 'appearances' == $menu_location )
                        add_theme_page( $page_title, $menu_label, 'read', $menu_slug );
                    elseif ( 'objects' == $menu_location ) {
                        if ( empty( $menu_position ) )
                            add_object_page( $page_title, $menu_label, 'read', $menu_slug, '', $menu_icon );
                        else
                            add_menu_page( $page_title, $menu_label, 'read', $menu_slug, '', $menu_icon, $menu_position );
                    }
                    elseif ( 'top' == $menu_location )
                        add_menu_page( $page_title, $menu_label, 'read', $menu_slug, '', $menu_icon, $menu_position );
                    elseif ( 'submenu' == $menu_location && !empty( $menu_location_custom ) ) {
                        if ( !isset( $submenu_items[ $menu_location_custom ] ) )
                            $submenu_items[ $menu_location_custom ] = array();

                        $submenu_items[ $menu_location_custom ][] = array( $menu_location_custom, $page_title, $menu_label, 'read', $menu_slug, '' );
                    }
                }
            }

            if ( !empty( $settings ) ) {
                foreach ( (array) $settings as $pod ) {
                    if ( !pods_is_admin( array( 'pods', 'pods_content', 'pods_edit_' . $pod[ 'name' ] ) ) )
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
                    elseif ( 'submenu' == $menu_location && !empty( $menu_location_custom ) ) {
                        if ( !isset( $submenu_items[ $menu_location_custom ] ) )
                            $submenu_items[ $menu_location_custom ] = array();

                        $submenu_items[ $menu_location_custom ][] = array( $menu_location_custom, $page_title, $menu_label, 'read', $menu_slug, array( $this, 'admin_content_settings' ) );
                    }
                }
            }

            foreach ( $submenu_items as $items ) {
                foreach ( $items as $item ) {
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
                if ( !pods_is_admin( pods_var_raw( 'access', $menu_item ) ) )
                    continue;

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

        if ( 'custom' != pods_var( 'ui_style', $pod->pod_data[ 'options' ], 'post_type', null, true ) ) {
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

            if ( !pods_is_admin( array( 'pods', 'pods_content' ) ) ) {
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
                    elseif ( $manage_field == $pod->pod_data[ 'field_id' ] ) {
                        $field = array(
                            'name' => $manage_field,
                            'label' => 'ID',
                            'type' => 'number',
                            'width' => '8%'
                        );

                        $manage_new[ $manage_field ] = PodsForm::field_setup( $field, null, $field[ 'type' ] );
                    }
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
        else {
            do_action( 'pods_admin_ui_custom', $pod );
            do_action( 'pods_admin_ui_custom_' . $pod->pod, $pod );
        }
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
        $pods = pods_api()->load_pods( array( 'fields' => false ) );

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

        $fields = array(
            'label' => array( 'label' => __( 'Label', 'pods' ) ),
            'name' => array( 'label' => __( 'Name', 'pods' ) ),
            'type' => array( 'label' => __( 'Type', 'pods' ) ),
            'storage' => array(
                'label' => __( 'Storage Type', 'pods' ),
                'width' => '10%'
            ),
            'field_count' => array(
                'label' => __( 'Number of Fields', 'pods' ),
                'width' => '8%'
            )
        );

        $total_fields = 0;

        foreach ( $pods as $k => $pod ) {
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

            $pod = array(
                'id' => $pod[ 'id' ],
                'label' => pods_var_raw( 'label', $pod ),
                'name' => pods_var_raw( 'name', $pod ),
                'type' => pods_var_raw( 'type', $pod ),
                'storage' => pods_var_raw( 'storage', $pod ),
                'field_count' => count( $pod[ 'fields' ] )
            );

            $total_fields += $pod[ 'field_count' ];

            $pods[ $k ] = $pod;
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
                'manage' => $fields
            ),
            'actions_disabled' => array( 'view', 'export' ),
            'actions_custom' => array(
                'add' => array( $this, 'admin_setup_add' ),
                'edit' => array( $this, 'admin_setup_edit' ),
                'duplicate' => array( $this, 'admin_setup_duplicate' ),
                'reset' => array(
                    'label' => __( 'Delete All Items', 'pods' ),
                    'confirm' => __( 'Are you sure you want to delete all items from this Pod? If this is an extended Pod, it will remove the original items extended too.', 'pods' ),
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
            'pagination' => false,
            'extra' => array(
                'total' => ', ' . number_format_i18n( $total_fields ) . ' ' . _n( 'field', 'fields', $total_fields, 'pods' )
            )
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
     * Get list of Pod option tabs
     *
     * @return array
     */
    public function admin_setup_edit_tabs ( $pod ) {
        $fields = true;
        $labels = false;
        $admin_ui = false;
        $advanced = false;

        if ( 'post_type' == pods_var( 'type', $pod ) && strlen( pods_var( 'object', $pod ) ) < 1 ) {
            $labels = true;
            $admin_ui = true;
            $advanced = true;
        }
        elseif ( 'taxonomy' == pods_var( 'type', $pod ) && strlen( pods_var( 'object', $pod ) ) < 1 ) {
            $labels = true;
            $admin_ui = true;
            $advanced = true;
        }
        elseif ( 'pod' == pods_var( 'type', $pod ) ) {
            $labels = true;
            $admin_ui = true;
            $advanced = true;
        }
        elseif ( 'settings' == pods_var( 'type', $pod ) ) {
            $labels = true;
            $admin_ui = true;
        }

        if ( 'none' == pods_var( 'storage', $pod, 'none', null, true ) && 'settings' != pods_var( 'type', $pod ) )
            $fields = false;

        $tabs = array();

        if ( $fields )
            $tabs[ 'manage-fields' ] = __( 'Manage Fields', 'pods' );

        if ( $labels )
            $tabs[ 'labels' ] = __( 'Labels', 'pods' );

        if ( $admin_ui )
            $tabs[ 'admin-ui' ] = __( 'Admin UI', 'pods' );

        if ( $advanced )
            $tabs[ 'advanced' ] = __( 'Advanced Options', 'pods' );

        if ( 'taxonomy' == pods_var( 'type', $pod ) && !$fields )
            $tabs[ 'extra-fields' ] = __( 'Extra Fields', 'pods' );

        $tabs = apply_filters( 'pods_admin_setup_edit_tabs', $tabs, $pod, compact( array( 'fields', 'labels', 'admin_ui', 'advanced' ) ) );

        return $tabs;
    }

    /**
     * Get list of Pod options
     *
     * @return array
     */
    public function admin_setup_edit_options ( $pod ) {
        $options = array();

        // @todo fill this in
        $options[ 'labels' ] = array(
            'temporary' => 'This has the fields hardcoded' // :(
        );

        if ( 'post_type' == $pod[ 'type' ] ) {
            $options[ 'admin-ui' ] = array(
                'description' => array(
                    'label' => __( 'Post Type Description', 'pods' ),
                    'help' => __( 'A short descriptive summary of what the post type is.', 'pods' ),
                    'type' => 'text',
                    'default' => ''
                ),
                'show_ui' => array(
                    'label' => __( 'Show Admin UI', 'pods' ),
                    'help' => __( 'help', 'pods' ),
                    'type' => 'boolean',
                    'default' => pods_var_raw( 'public', $pod, true ),
                    'boolean_yes_label' => ''
                ),
                'show_in_menu' => array(
                    'label' => __( 'Show Admin Menu in Dashboard', 'pods' ),
                    'help' => __( 'help', 'pods' ),
                    'type' => 'boolean',
                    'default' => pods_var_raw( 'public', $pod, true ),
                    'dependency' => true,
                    'boolean_yes_label' => ''
                ),
                'menu_location_custom' => array(
                    'label' => __( 'Parent Menu ID (optional)', 'pods' ),
                    'help' => __( 'help', 'pods' ),
                    'type' => 'text',
                    'depends-on' => array( 'show_in_menu' => true )
                ),
                'menu_name' => array(
                    'label' => __( 'Menu Name', 'pods' ),
                    'help' => __( 'help', 'pods' ),
                    'type' => 'text',
                    'default' => '',
                    'depends-on' => array( 'show_in_menu' => true )
                ),
                'menu_position' => array(
                    'label' => __( 'Menu Position', 'pods' ),
                    'help' => __( 'help', 'pods' ),
                    'type' => 'number',
                    'default' => 0,
                    'depends-on' => array( 'show_in_menu' => true )
                ),
                'menu_icon' => array(
                    'label' => __( 'Menu Icon URL', 'pods' ),
                    'help' => __( 'help', 'pods' ),
                    'type' => 'text',
                    'default' => '',
                    'depends-on' => array( 'show_in_menu' => true )
                ),
                'show_in_nav_menus' => array(
                    'label' => __( 'Show in Navigation Menus', 'pods' ),
                    'help' => __( 'help', 'pods' ),
                    'type' => 'boolean',
                    'default' => true,
                    'boolean_yes_label' => ''
                ),
                'show_in_admin_bar' => array(
                    'label' => __( 'Show in Admin Bar "New" Menu', 'pods' ),
                    'help' => __( 'help', 'pods' ),
                    'type' => 'boolean',
                    'default' => true,
                    'boolean_yes_label' => ''
                )
            );

            $options[ 'advanced' ] = array(
                'public' => array(
                    'label' => __( 'Public', 'pods' ),
                    'help' => __( 'help', 'pods' ),
                    'type' => 'boolean',
                    'default' => true,
                    'boolean_yes_label' => ''
                ),
                'publicly_queryable' => array(
                    'label' => __( 'Publicly Queryable', 'pods' ),
                    'help' => __( 'help', 'pods' ),
                    'type' => 'boolean',
                    'default' => pods_var_raw( 'public', $pod, true ),
                    'boolean_yes_label' => ''
                ),
                'exclude_from_search' => array(
                    'label' => __( 'Exclude from Search', 'pods' ),
                    'help' => __( 'help', 'pods' ),
                    'type' => 'boolean',
                    'default' => !pods_var_raw( 'public', $pod, true ),
                    'boolean_yes_label' => ''
                ),
                'capability_type' => array(
                    'label' => __( 'User Capability', 'pods' ),
                    'help' => __( 'Uses these capabilties for access to this post type: edit_{capability}, read_{capability}, and delete_{capability}', 'pods' ),
                    'type' => 'pick',
                    'default' => 'post',
                    'data' => array(
                        'post' => 'post',
                        'page' => 'page',
                        'custom' => __( 'Custom Capability', 'pods' )
                    ),
                    'dependency' => true
                ),
                'capability_type_custom' => array(
                    'label' => __( 'Custom User Capability', 'pods' ),
                    'help' => __( 'help', 'pods' ),
                    'type' => 'text',
                    'default' => pods_var_raw( 'name', $pod ),
                    'depends-on' => array( 'capability_type' => 'custom' )
                ),
                'capability_type_extra' => array(
                    'label' => __( 'Additional User Capabilities', 'pods' ),
                    'help' => __( 'Enables additional capabilities for this Post Type including: delete_{capability}s, delete_private_{capability}s, delete_published_{capability}s, delete_others_{capability}s, edit_private_{capability}s, and edit_published_{capability}s', 'pods' ),
                    'type' => 'boolean',
                    'default' => true,
                    'boolean_yes_label' => ''
                ),
                'has_archive' => array(
                    'label' => __( 'Has Archive', 'pods' ),
                    'help' => __( 'help', 'pods' ),
                    'type' => 'boolean',
                    'default' => false,
                    'boolean_yes_label' => ''
                ),
                'hierarchical' => array(
                    'label' => __( 'Hierarchical', 'pods' ),
                    'help' => __( 'help', 'pods' ),
                    'type' => 'boolean',
                    'default' => false,
                    'dependency' => true,
                    'boolean_yes_label' => ''
                ),
                'label_parent_item_colon' => array(
                    'label' => __( '<strong>Label: </strong> Parent <span class="pods-slugged" data-sluggable="label_singular">Item</span>', 'pods' ),
                    'help' => __( 'help', 'pods' ),
                    'type' => 'text',
                    'default' => '',
                    'depends-on' => array( 'hierarchical' => true )
                ),
                'label_parent' => array(
                    'label' => __( '<strong>Label: </strong> Parent', 'pods' ),
                    'help' => __( 'help', 'pods' ),
                    'type' => 'text',
                    'default' => '',
                    'depends-on' => array( 'hierarchical' => true )
                ),
                'rewrite' => array(
                    'label' => __( 'Rewrite', 'pods' ),
                    'help' => __( 'help', 'pods' ),
                    'type' => 'boolean',
                    'default' => true,
                    'dependency' => true,
                    'boolean_yes_label' => ''
                ),
                'rewrite_custom_slug' => array(
                    'label' => __( 'Custom Rewrite Slug', 'pods' ),
                    'help' => __( 'help', 'pods' ),
                    'type' => 'text',
                    'default' => '',
                    'depends-on' => array( 'rewrite' => true )
                ),
                'rewrite_with_front' => array(
                    'label' => __( 'Rewrite with Front', 'pods' ),
                    'help' => __( 'Allows permalinks to be prepended with your front base (example: if your permalink structure is /blog/, then your links will be: Unchecked->/news/, Checked->/blog/news/)', 'pods' ),
                    'type' => 'boolean',
                    'default' => true,
                    'depends-on' => array( 'rewrite' => true ),
                    'boolean_yes_label' => ''
                ),
                'rewrite_feeds' => array(
                    'label' => __( 'Rewrite Feeds', 'pods' ),
                    'help' => __( 'help', 'pods' ),
                    'type' => 'boolean',
                    'default' => false,
                    'depends-on' => array( 'rewrite' => true ),
                    'boolean_yes_label' => ''
                ),
                'rewrite_pages' => array(
                    'label' => __( 'Rewrite Pages', 'pods' ),
                    'help' => __( 'help', 'pods' ),
                    'type' => 'boolean',
                    'default' => true,
                    'depends-on' => array( 'rewrite' => true ),
                    'boolean_yes_label' => ''
                ),
                'query_var' => array(
                    'label' => __( 'Query Var', 'pods' ),
                    'help' => __( 'help', 'pods' ),
                    'type' => 'boolean',
                    'default' => true,
                    'boolean_yes_label' => ''
                ),
                'can_export' => array(
                    'label' => __( 'Exportable', 'pods' ),
                    'help' => __( 'help', 'pods' ),
                    'type' => 'boolean',
                    'default' => true,
                    'boolean_yes_label' => ''
                )
            );
        }
        elseif ( 'taxonomy' == $pod[ 'type' ] ) {
            $options[ 'admin-ui' ] = array(
                'show_ui' => array(
                    'label' => __( 'Show Admin UI', 'pods' ),
                    'help' => __( 'help', 'pods' ),
                    'type' => 'boolean',
                    'default' => pods_var_raw( 'public', $pod, true ),
                    'dependency' => true,
                    'boolean_yes_label' => ''
                ),
                'menu_name' => array(
                    'label' => __( 'Menu Name', 'pods' ),
                    'help' => __( 'help', 'pods' ),
                    'type' => 'text',
                    'default' => '',
                    'depends-on' => array( 'show_ui' => true )
                ),
                'menu_location' => array(
                    'label' => __( 'Menu Location', 'pods' ),
                    'help' => __( 'help', 'pods' ),
                    'type' => 'pick',
                    'default' => 'default',
                    'depends-on' => array( 'show_ui' => true ),
                    'data' => array(
                        'default' => __( 'Default - Add to associated Post Type(s) menus', 'pods' ),
                        'settings' => __( 'Add to Settings menu', 'pods' ),
                        'appearances' => __( 'Add to Appearances menu', 'pods' ),
                        'objects' => __( 'Make a top-level menu item', 'pods' ),
                        'top' => __( 'Make a new top-level menu item below Settings', 'pods' ),
                        'submenu' => __( 'Add a submenu item to another menu', 'pods' )
                    ),
                    'dependency' => true
                ),
                'menu_location_custom' => array(
                    'label' => __( 'Custom Menu Location', 'pods' ),
                    'help' => __( 'help', 'pods' ),
                    'type' => 'text',
                    'depends-on' => array( 'menu_location' => 'submenu' )
                ),
                'menu_position' => array(
                    'label' => __( 'Menu Position', 'pods' ),
                    'help' => __( 'help', 'pods' ),
                    'type' => 'number',
                    'default' => 0,
                    'depends-on' => array( 'menu_location' => array( 'objects', 'top' ) )
                ),
                'menu_icon' => array(
                    'label' => __( 'Menu Icon URL', 'pods' ),
                    'help' => __( 'help', 'pods' ),
                    'type' => 'text',
                    'default' => '',
                    'depends-on' => array( 'menu_location' => array( 'objects', 'top' ) )
                ),
                'show_in_nav_menus' => array(
                    'label' => __( 'Show in Navigation Menus', 'pods' ),
                    'help' => __( 'help', 'pods' ),
                    'type' => 'boolean',
                    'default' => pods_var_raw( 'public', $pod, true ),
                    'boolean_yes_label' => ''
                ),
                'show_tagcloud' => array(
                    'label' => __( 'Allow in Tagcloud Widget', 'pods' ),
                    'help' => __( 'help', 'pods' ),
                    'type' => 'boolean',
                    'default' => pods_var_raw( 'show_ui', $pod, pods_var_raw( 'public', $pod, true ) ),
                    'boolean_yes_label' => ''
                )
            );

            if ( pods_version_check( 'wp', '3.5' ) ) {
                $options[ 'admin-ui' ][ 'show_admin_column' ] = array(
                    'label' => __( 'Show Taxonomy column on Post Types', 'pods' ),
                    'help' => __( 'Whether to add a column for this taxonomy on the associated post types manage screens', 'pods' ),
                    'type' => 'boolean',
                    'default' => false,
                    'boolean_yes_label' => ''
                );
            }

            // @todo fill this in
            $options[ 'advanced' ] = array(
                'temporary' => 'This type has the fields hardcoded' // :(
            );
        }
        elseif ( 'settings' == $pod[ 'type' ] ) {
            $options[ 'admin-ui' ] = array(
                'ui_style' => array(
                    'label' => __( 'Admin UI Style', 'pods' ),
                    'help' => __( 'help', 'pods' ),
                    'type' => 'pick',
                    'default' => 'settings',
                    'data' => array(
                        'settings' => __( 'Normal Settings Form', 'pods' ),
                        'post_type' => __( 'Post Type UI', 'pods' ),
                        'custom' => __( 'Custom (hook into pods_admin_ui_custom or pods_admin_ui_custom_{podname} action)', 'pods' )
                    ),
                    'dependency' => true
                ),
                'menu_location' => array(
                    'label' => __( 'Menu Location', 'pods' ),
                    'help' => __( 'help', 'pods' ),
                    'type' => 'pick',
                    'default' => 'settings',
                    'data' => array(
                        'settings' => __( 'Add to Settings menu', 'pods' ),
                        'appearances' => __( 'Add to Appearances menu', 'pods' ),
                        'top' => __( 'Make a new top-level menu item below Settings', 'pods' ),
                        'submenu' => __( 'Add a submenu item to another menu', 'pods' )
                    ),
                    'dependency' => true
                ),
                'menu_location_custom' => array(
                    'label' => __( 'Custom Menu Location', 'pods' ),
                    'help' => __( 'help', 'pods' ),
                    'type' => 'text',
                    'depends-on' => array( 'menu_location' => 'submenu' )
                ),
                'menu_position' => array(
                    'label' => __( 'Menu Position', 'pods' ),
                    'help' => __( 'help', 'pods' ),
                    'type' => 'number',
                    'default' => 0,
                    'depends-on' => array( 'menu_location' => 'top' )
                ),
                'menu_icon' => array(
                    'label' => __( 'Menu Icon URL', 'pods' ),
                    'help' => __( 'help', 'pods' ),
                    'type' => 'text',
                    'default' => '',
                    'depends-on' => array( 'menu_location' => 'top' )
                )
            );

            // @todo fill this in
            $options[ 'advanced' ] = array(
                'temporary' => 'This type has the fields hardcoded' // :(
            );
        }
        elseif ( 'pod' == $pod[ 'type' ] ) {
            $options[ 'admin-ui' ] = array(
                'ui_style' => array(
                    'label' => __( 'Admin UI Style', 'pods' ),
                    'help' => __( 'help', 'pods' ),
                    'type' => 'pick',
                    'default' => 'settings',
                    'data' => array(
                        'post_type' => __( 'Normal (Looks like the Post Type UI)', 'pods' ),
                        'custom' => __( 'Custom (hook into pods_admin_ui_custom or pods_admin_ui_custom_{podname} action)', 'pods' )
                    ),
                    'dependency' => true
                ),
                'show_in_menu' => array(
                    'label' => __( 'Show Admin Menu in Dashboard', 'pods' ),
                    'help' => __( 'help', 'pods' ),
                    'type' => 'boolean',
                    'default' => false,
                    'boolean_yes_label' => '',
                    'dependency' => true
                ),
                'menu_location_custom' => array(
                    'label' => __( 'Parent Menu ID (optional)', 'pods' ),
                    'help' => __( 'help', 'pods' ),
                    'type' => 'text',
                    'depends-on' => array( 'show_in_menu' => true )
                ),
                'menu_position' => array(
                    'label' => __( 'Menu Position', 'pods' ),
                    'help' => __( 'help', 'pods' ),
                    'type' => 'number',
                    'default' => 0,
                    'depends-on' => array( 'show_in_menu' => true )
                ),
                'menu_icon' => array(
                    'label' => __( 'Menu Icon URL', 'pods' ),
                    'help' => __( 'This is the icon shown to the left of the menu text for this content type.', 'pods' ),
                    'type' => 'text',
                    'default' => '',
                    'depends-on' => array( 'show_in_menu' => true )
                ),
                'ui_icon' => array(
                    'label' => __( 'Header Icon', 'pods' ),
                    'help' => __( 'This is the icon shown to the left of the heading text at the top of the manage pages for this content type.', 'pods' ),
                    'type' => 'file',
                    'default' => '',
                    'file_edit_title' => 0,
                    'depends-on' => array( 'show_in_menu' => true )
                ),
                'ui_actions_enabled' => array(
                    'label' => __( 'Actions Available', 'pods' ),
                    'help' => __( 'help', 'pods' ),
                    'type' => 'pick',
                    'default' => ( 1 == pods_var( 'ui_export', $pod ) ? array( 'add', 'edit', 'duplicate', 'delete', 'export' ) : array( 'add', 'edit', 'duplicate', 'delete' ) ),
                    'data' => array(
                        'add' => __( 'Add New', 'pods' ),
                        'edit' => __( 'Edit', 'pods' ),
                        'duplicate' => __( 'Duplicate', 'pods' ),
                        'delete' => __( 'Delete', 'pods' ),
                        'reorder' => __( 'Reorder', 'pods' ),
                        'export' => __( 'Export', 'pods' )
                    ),
                    'pick_format_type' => 'multi',
                    'dependency' => true
                ),
                'ui_reorder_field' => array(
                    'label' => __( 'Reorder Field', 'pods' ),
                    'help' => __( 'This is the field that will be reordered on, it should be numeric.', 'pods' ),
                    'type' => 'text',
                    'default' => 'menu_order',
                    'depends-on' => array( 'ui_actions_enabled' => 'reorder' )
                ),
                'ui_fields_manage' => array(
                    'label' => __( 'Admin Table Columns', 'pods' ),
                    'help' => __( 'help', 'pods' ),
                    'type' => 'pick',
                    'default' => array(),
                    'data' => array(),
                    'pick_format_type' => 'multi'
                ),
                'ui_filters' => array(
                    'label' => __( 'Search Filters', 'pods' ),
                    'help' => __( 'help', 'pods' ),
                    'type' => 'pick',
                    'default' => array(),
                    'data' => array(),
                    'pick_format_type' => 'multi'
                )
            );

            if ( !empty( $pod[ 'fields' ] ) ) {
                if ( isset( $pod[ 'fields' ][ pods_var_raw( 'pod_index', $pod, 'name' ) ] ) )
                    $options[ 'admin-ui' ][ 'ui_fields_manage' ][ 'default' ][] = pods_var_raw( 'pod_index', $pod, 'name' );

                if ( isset( $pod[ 'fields' ][ 'modified' ] ) )
                    $options[ 'admin-ui' ][ 'ui_fields_manage' ][ 'default' ][] = 'modified';

                foreach ( $pod[ 'fields' ] as $field ) {
                    $type = '';

                    if ( isset( $field_types[ $field[ 'type' ] ] ) )
                        $type = ' <small>(' . $field_types[ $field[ 'type' ] ][ 'label' ] . ')</small>';

                    $options[ 'admin-ui' ][ 'ui_fields_manage' ][ 'data' ][ $field[ 'name' ] ] = $field[ 'label' ] . $type;
                    $options[ 'admin-ui' ][ 'ui_filters' ][ 'data' ][ $field[ 'name' ] ] = $field[ 'label' ] . $type;
                }

                $options[ 'admin-ui' ][ 'ui_fields_manage' ][ 'data' ][ 'id' ] = 'ID';
            }
            else {
                unset( $options[ 'admin-ui' ][ 'ui_fields_manage' ] );
                unset( $options[ 'admin-ui' ][ 'ui_filters' ] );
            }

            // @todo fill this in
            $options[ 'advanced' ] = array(
                'temporary' => 'This type has the fields hardcoded' // :(
            );
        }

        $options = apply_filters( 'pods_admin_setup_edit_options', $options, $pod );

        return $options;
    }

    /**
     * Get list of Pod field option tabs
     *
     * @return array
     */
    public function admin_setup_edit_field_tabs ( $pod ) {
        $core_tabs = array(
            'basic' => __( 'Basic', 'pods' ),
            'additional-field' => __( 'Additional Field Options', 'pods' ),
            'advanced' => __( 'Advanced', 'pods' )
        );

        $tabs = apply_filters( 'pods_admin_setup_edit_field_tabs', array(), $pod );

        $tabs = array_merge( $core_tabs, $tabs );

        return $tabs;
    }

    /**
     * Get list of Pod field options
     *
     * @return array
     */
    public function admin_setup_edit_field_options ( $pod ) {
        $options = array();

        $options[ 'additional-field' ] = array();

        $field_types = PodsForm::field_types();

        foreach ( $field_types as $type => $field_type_data ) {
            /**
             * @var $field_type PodsField
             */
            $field_type = PodsForm::field_loader( $type, $field_type_data[ 'file' ] );

            $field_type_vars = get_class_vars( get_class( $field_type ) );

            if ( !isset( $field_type_vars[ 'pod_types' ] ) )
                $field_type_vars[ 'pod_types' ] = true;

            $options[ 'additional-field' ][ $type ] = array();

            // Only show supported field types
            if ( true !== $field_type_vars[ 'pod_types' ] ) {
                if ( empty( $field_type_vars[ 'pod_types' ] ) )
                    continue;
                elseif ( is_array( $field_type_vars[ 'pod_types' ] ) && !in_array( pods_var( 'type', $pod ), $field_type_vars[ 'pod_types' ] ) )
                    continue;
                elseif ( !is_array( $field_type_vars[ 'pod_types' ] ) && pods_var( 'type', $pod ) != $field_type_vars[ 'pod_types' ] )
                    continue;
            }

            $options[ 'additional-field' ][ $type ] = PodsForm::ui_options( $type );
        }

        $input_helpers = array(
            '' => '-- Select --'
        );

        if ( class_exists( 'Pods_Helpers' ) ) {
            $helpers = pods_api()->load_helpers( array( 'options' => array( 'helper_type' => 'input' ) ) );

            foreach ( $helpers as $helper ) {
                $input_helpers[ $helper[ 'name' ] ] = $helper[ 'name' ];
            }
        }

        $options[ 'advanced' ] = array(
            __( 'Visual', 'pods' ) => array(
                'class' => array(
                    'name' => 'class',
                    'label' => __( 'Additional CSS Classes', 'pods' ),
                    'help' => __( 'help', 'pods' ),
                    'type' => 'text',
                    'default' => ''
                ),
                'input_helper' => array(
                    'name' => 'input_helper',
                    'label' => __( 'Input Helper', 'pods' ),
                    'help' => __( 'help', 'pods' ),
                    'type' => 'pick',
                    'default' => '',
                    'data' => $input_helpers
                )
            ),
            __( 'Values', 'pods' ) => array(
                'default_value' => array(
                    'name' => 'default_value',
                    'label' => __( 'Default Value', 'pods' ),
                    'help' => __( 'help', 'pods' ),
                    'type' => 'text',
                    'default' => ''
                ),
                'default_value_parameter' => array(
                    'name' => 'default_value_parameter',
                    'label' => __( 'Set Default Value via Parameter', 'pods' ),
                    'help' => __( 'help', 'pods' ),
                    'type' => 'text',
                    'default' => ''
                )
            ),
            __( 'Visibility', 'pods' ) => array(
                'restrict_access' => array(
                    'name' => 'restrict_access',
                    'label' => __( 'Restrict Access', 'pods' ),
                    'group' => array(
                        'admin_only' => array(
                            'name' => 'admin_only',
                            'label' => __( 'Restrict access to Admins?', 'pods' ),
                            'default' => 0,
                            'type' => 'boolean',
                            'dependency' => true,
                            'help' => __( 'This field will only be able to be edited by users with the ability to manage_options or delete_users, or super admins of a WordPress Multisite network', 'pods' )
                        ),
                        'restrict_role' => array(
                            'name' => 'restrict_role',
                            'label' => __( 'Restrict access by Role?', 'pods' ),
                            'default' => 0,
                            'type' => 'boolean',
                            'dependency' => true
                        ),
                        'restrict_capability' => array(
                            'name' => 'restrict_capability',
                            'label' => __( 'Restrict access by Capability?', 'pods' ),
                            'default' => 0,
                            'type' => 'boolean',
                            'dependency' => true
                        ),
                        'hidden' => array(
                            'name' => 'hidden',
                            'label' => __( 'Hide field from UI', 'pods' ),
                            'default' => 0,
                            'type' => 'boolean',
                            'help' => __( 'This option is overriden by access restrictions. If the user does not have access to edit this field, it will be hidden. If no access restrictions are set, this field will always be hidden.', 'pods' )
                        ),
                        'read_only' => array(
                            'name' => 'read_only',
                            'label' => __( 'Make field "Read Only" in UI', 'pods' ),
                            'default' => 0,
                            'type' => 'boolean',
                            'help' => __( 'This option is overriden by access restrictions. If the user does not have access to edit this field, it will be read only. If no access restrictions are set, this field will always be read only.', 'pods' ),
                            'depends-on' => array(
                                'type' => array(
                                    'boolean',
                                    'color',
                                    'currency',
                                    'date',
                                    'datetime',
                                    'email',
                                    'number',
                                    'paragraph',
                                    'password',
                                    'phone',
                                    'slug',
                                    'text',
                                    'time',
                                    'website'
                                )
                            )
                        )
                    )
                ),
                'roles_allowed' => array(
                    'name' => 'roles_allowed',
                    'label' => __( 'Role(s) Allowed', 'pods' ),
                    'help' => __( 'help', 'pods' ),
                    'type' => 'pick',
                    'pick_object' => 'role',
                    'pick_format_type' => 'multi',
                    'default' => 'administrator',
                    'depends-on' => array(
                        'restrict_role' => true
                    )
                ),
                'capability_allowed' => array(
                    'name' => 'capability_allowed',
                    'label' => __( 'Capability Allowed', 'pods' ),
                    'help' => __( 'Comma-separated list of cababilities, for example add_podname_item, please see the Roles and Capabilities component for the complete list and a way to add your own.', 'pods' ),
                    'type' => 'text',
                    'default' => '',
                    'depends-on' => array(
                        'restrict_capability' => true
                    )
                )
                /*,
                        'search' => array(
                            'label' => __( 'Include in searches', 'pods' ),
                            'help' => __( 'help', 'pods' ),
                            'default' => 1,
                            'type' => 'boolean',
                        )*/
            )
            /*,
                __( 'Validation', 'pods' ) => array(
                    'regex_validation' => array(
                        'label' => __( 'RegEx Validation', 'pods' ),
                        'help' => __( 'help', 'pods' ),
                        'type' => 'text',
                        'default' => ''
                    ),
                    'message_regex' => array(
                        'label' => __( 'Message if field does not pass RegEx', 'pods' ),
                        'help' => __( 'help', 'pods' ),
                        'type' => 'text',
                        'default' => ''
                    ),
                    'message_required' => array(
                        'label' => __( 'Message if field is blank', 'pods' ),
                        'help' => __( 'help', 'pods' ),
                        'type' => 'text',
                        'default' => '',
                        'depends-on' => array( 'required' => true )
                    ),
                    'message_unique' => array(
                        'label' => __( 'Message if field is not unique', 'pods' ),
                        'help' => __( 'help', 'pods' ),
                        'type' => 'text',
                        'default' => '',
                        'depends-on' => array( 'unique' => true )
                    )
                )*/
        );

        if ( !class_exists( 'Pods_Helpers' ) )
            unset( $options[ 'advanced-options' ][ 'input_helper' ] );

        $options = apply_filters( 'pods_admin_setup_edit_field_options', $options, $pod );

        return $options;
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
        $new_id = pods_api()->duplicate_pod( array( 'id' => $obj->id ) );

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
        $pod = pods_api()->load_pod( array( 'id' => $id ), false );

        if ( empty( $pod ) )
            return $obj->error( __( 'Pod not found.', 'pods' ) );

        pods_api()->reset_pod( array( 'id' => $id ) );

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
        $pod = pods_api()->load_pod( array( 'id' => $id ), false );

        if ( empty( $pod ) )
            return $obj->error( __( 'Pod not found.', 'pods' ) );

        pods_api()->delete_pod( array( 'id' => $id ) );

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

        $recommended = array(
            'advanced-relationships',
            'advanced-content-types',
            'migrate-packages',
            'roles-and-capabilities',
            'pages',
            'table-storage',
            'templates'
        );

        foreach ( $components as $component => &$component_data ) {
            if ( !in_array( $view, array( 'all', 'recommended', 'dev' ) ) && ( !isset( $component_data[ 'Category' ] ) || $view != sanitize_title( $component_data[ 'Category' ] ) ) ) {
                unset( $components[ $component ] );

                continue;
            }
            elseif ( 'recommended' == $view && !in_array( $component_data[ 'ID' ], $recommended ) ) {
                unset( $components[ $component ] );

                continue;
            }
            elseif ( 'dev' == $view && pods_developer() && !pods_var_raw( 'DeveloperMode', $component_data, false ) ) {
                unset( $components[ $component ] );

                continue;
            }
            elseif ( pods_var_raw( 'DeveloperMode', $component_data, false ) && !pods_developer() ) {
                unset( $components[ $component ] );

                continue;
            }
            elseif ( !pods_var_raw( 'TablelessMode', $component_data, false ) && pods_tableless() ) {
                unset( $components[ $component ] );

                continue;
            }

            $component_data[ 'Name' ] = strip_tags( $component_data[ 'Name' ] );

            if ( pods_var_raw( 'DeveloperMode', $component_data, false ) )
                $component_data[ 'Name' ] .= ' <em style="font-weight: normal; color:#333;">(Developer Preview)</em>';

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

            $component_data[ 'Description' ] = wpautop( trim( make_clickable( strip_tags( $component_data[ 'Description' ], 'em,strong' ) ) ) );

            if ( !empty( $meta ) )
                $component_data[ 'Description' ] .= '<div class="pods-component-meta" ' . ( !empty( $component_data[ 'Description' ] ) ? ' style="padding:8px 0 4px;"' : '' ) . '>' . implode( '&nbsp;&nbsp;|&nbsp;&nbsp;', $meta ) . '</div>';

            $component_data = array(
                'id' => $component_data[ 'ID' ],
                'name' => $component_data[ 'Name' ],
                'category' => $component_data[ 'Category' ],
                'version' => '',
                'description' => $component_data[ 'Description' ],
                'mustuse' => pods_var_raw( 'MustUse', $component_data, false ),
                'toggle' => 0
            );

            if ( !empty( $component_data[ 'category' ] ) ) {
                $category_url = pods_var_update( array( 'view' => sanitize_title( $component_data[ 'category' ] ), 'pg' => '', 'page' => $_GET[ 'page' ] ) );

                $component_data[ 'category' ] = '<a href="' . $category_url . '">' . $component_data[ 'category' ] . '</a>';
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
                        'type' => 'text',
                        'options' => array(
                            'text_allow_html' => true
                        )
                    ),
                    'category' => array(
                        'label' => __( 'Category', 'pods' ),
                        'width' => '10%',
                        'type' => 'text',
                        'options' => array(
                            'text_allow_html' => true
                        )
                    ),
                    'description' => array(
                        'label' => __( 'Description', 'pods' ),
                        'width' => '60%',
                        'type' => 'text',
                        'options' => array(
                            'text_allow_html' => true,
                            'text_allowed_html_tags' => 'strong em a ul ol li b i br div'
                        )
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
                //'recommended' => __( 'Recommended', 'pods' ),
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

        if ( pods_developer() )
            $ui[ 'views' ][ 'dev' ] = __( 'Developer Preview', 'pods' );

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
        $pods = pods_api()->load_pods( array( 'type' => array( 'pod', 'table', 'post_type', 'taxonomy', 'settings' ) ) );

        $capabilities[] = 'pods';
        $capabilities[] = 'pods_content';
        $capabilities[] = 'pods_settings';
        $capabilities[] = 'pods_components';

        foreach ( $pods as $pod ) {
            if ( 'settings' == $pod[ 'type' ] ) {
                $capabilities[] = 'pods_edit_' . $pod[ 'name' ];
            }
            elseif ( 'post_type' == $pod[ 'type' ] ) {
                $capability_type = pods_var( 'capability_type_custom', $pod[ 'options' ], pods_var_raw( 'name', $pod ) );

                if ( 'custom' == pods_var( 'capability_type', $pod[ 'options' ] ) && 0 < strlen( $capability_type ) ) {
                    $capabilities[] = 'read_' . $capability_type;
                    $capabilities[] = 'edit_' . $capability_type;
                    $capabilities[] = 'delete_' . $capability_type;

                    if ( 1 == pods_var( 'capability_type_extra', $pod[ 'options' ], 1 ) ) {
                        $capabilities[] = 'read_private_' . $capability_type . 's';
                        $capabilities[] = 'edit_' . $capability_type . 's';
                        $capabilities[] = 'edit_others_' . $capability_type . 's';
                        $capabilities[] = 'edit_private_' . $capability_type . 's';
                        $capabilities[] = 'edit_published_' . $capability_type . 's';
                        $capabilities[] = 'publish_' . $capability_type . 's';
                        $capabilities[] = 'delete_' . $capability_type . 's';
                        $capabilities[] = 'delete_private_' . $capability_type . 's';
                        $capabilities[] = 'delete_published_' . $capability_type . 's';
                        $capabilities[] = 'delete_others_' . $capability_type . 's';
                    }
                }
            }
            elseif ( 'taxonomy' == $pod[ 'type' ] ) {
                if ( 1 == pods_var( 'capabilities', $pod[ 'options' ], 0 ) ) {
                    $capability_type = pods_var( 'capability_type_custom', $pod[ 'options' ], pods_var_raw( 'name', $pod ) . 's' );

                    $capabilities[] = 'manage_' . $capability_type;
                    $capabilities[] = 'edit_' . $capability_type;
                    $capabilities[] = 'delete_' . $capability_type;
                    $capabilities[] = 'assign_' . $capability_type;
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
        $params = pods_unslash( (array) $_POST );

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
            'upgrade' => array( 'priv' => true ),
            'migrate' => array( 'priv' => true )
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
        if ( !empty( $method->priv ) && !pods_is_admin( array( 'pods' ) ) && true !== $method->priv && !pods_is_admin( $method->priv ) )
            pods_error( __( 'Access denied', 'pods' ), $this );

        $params->method = $method->name;

        $params = apply_filters( 'pods_api_' . $method->name, $params, $method );

        $api = pods_api();

        if ( 'upgrade' == $method->name )
            $output = (string) pods_upgrade( $params->version )->ajax( $params );
        elseif ( 'migrate' == $method->name )
            $output = (string) apply_filters( 'pods_api_migrate_run', $params );
        else {
            if ( !method_exists( $api, $method->name ) )
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

            $output = call_user_func( array( $api, $method->name ), $params );
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
