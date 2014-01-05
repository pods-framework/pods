<?php
/**
 * @package Pods
 */
class Pods_Admin {

    /**
     * @var Pods_Admin
     */
    static $instance = null;

	/**
	 * @var bool|Pods_Object_Pod
	 */
	static $admin_row = false;

    /**
     * Singleton handling for a basic pods_admin() request
     *
     * @return \Pods_Admin
     *
     * @since 2.3.5
     */
    public static function init() {
        if ( !is_object( self::$instance ) )
            self::$instance = new Pods_Admin();

        return self::$instance;
    }

    /**
     * Setup and Handle Admin functionality
     *
     * @return \Pods_Admin
     *
     * @license http://www.gnu.org/licenses/gpl-2.0.html
     * @since 2.0
     */
    public function __construct() {
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
    public function admin_init() {
        // Fix for plugins that *don't do it right* so we don't cause issues for users
        if ( defined( 'DOING_AJAX' ) && !empty( $_POST ) ) {
            $pods_admin_ajax_actions = array(
                'pods_admin',
                'pods_relationship',
                'pods_upload',
                'pods_admin_components'
            );

            $pods_admin_ajax_actions = apply_filters( 'pods_admin_ajax_actions', $pods_admin_ajax_actions );

            if ( in_array( pods_v( 'action' ), $pods_admin_ajax_actions ) || in_array( pods_v( 'action', 'post' ), $pods_admin_ajax_actions ) ) {
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
    public function admin_head() {
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
                elseif ( 'pods-wizard' == $page || 'pods-upgrade' == $page || ( in_array( $page, array( 'pods', 'pods-add-new' ) ) && in_array( pods_v( 'action', 'get', 'manage' ), array( 'add', 'manage' ) ) ) ) {
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
    public function admin_menu() {
        $taxonomies = Pods_Meta::$taxonomies;

        $advanced_content_types = Pods_Meta::$advanced_content_types = pods_api()->load_pods( array( 'type' => 'pod' ) );
        $settings = Pods_Meta::$settings = pods_api()->load_pods( array( 'type' => 'settings' ) );

        $all_pods = pods_api()->load_pods( array( 'count' => true ) );

        if ( !Pods_Init::$upgrade_needed || ( pods_is_admin() && 1 == pods_v( 'pods_upgrade_bypass' ) ) ) {
            $submenu_items = array();

            if ( !empty( $advanced_content_types ) ) {
                $submenu = array();

                $pods_pages = 0;

                foreach ( (array) $advanced_content_types as $pod ) {
					if ( !pods_is_admin( array( 'pods', 'pods_content', 'pods_add_' . $pod[ 'name' ], 'pods_edit_' . $pod[ 'name' ], 'pods_delete_' . $pod[ 'name' ] ) ) )
                        continue;

                    if ( 1 == pods_v( 'show_in_menu', $pod, 0 ) ) {
                        $page_title = pods_var_raw( 'label', $pod, ucwords( str_replace( '_', ' ', $pod[ 'name' ] ) ), null, true );
                        $page_title = apply_filters( 'pods_admin_menu_page_title', $page_title, $pod );

                        $menu_label = pods_v( 'menu_icon', $pod, '', true );
                        $menu_label = apply_filters( 'pods_admin_menu_label', $menu_label, $pod );

                        $singular_label = pods_var_raw( 'label_singular', $pod, pods_var_raw( 'label', $pod, ucwords( str_replace( '_', ' ', $pod[ 'name' ] ) ), null, true ), null, true );
                        $plural_label = pods_var_raw( 'label', $pod, ucwords( str_replace( '_', ' ', $pod[ 'name' ] ) ), null, true );

                        $menu_location = pods_v( 'menu_location', $pod, 'objects' );
                        $menu_location_custom = pods_v( 'menu_location_custom', $pod, '' );

                        $menu_position = pods_v( 'menu_icon', $pod, '', true );
                        $menu_icon = pods_evaluate_tags( pods_v( 'menu_icon', $pod, '', true ), true );

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

                                if ( $page == pods_v( 'page' ) ) {
                                    if ( 'edit' == pods_v( 'action', 'get', 'manage' ) )
                                        $all_title = __( 'Edit', 'pods' ) . ' ' . $singular_label;
                                    elseif ( 'add' == pods_v( 'action', 'get', 'manage' ) )
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
                        $singular_label = pods_var_raw( 'label_singular', $item, pods_var_raw( 'label', $item, ucwords( str_replace( '_', ' ', $item[ 'name' ] ) ), null, true ), null, true );
                        $plural_label = pods_var_raw( 'label', $item, ucwords( str_replace( '_', ' ', $item[ 'name' ] ) ), null, true );

                        if ( pods_is_admin( array( 'pods', 'pods_content', 'pods_edit_' . $item[ 'name' ], 'pods_delete_' . $item[ 'name' ] ) ) ) {
                            $page = 'pods-manage-' . $item[ 'name' ];

                            if ( null === $parent_page ) {
                                $parent_page = $page;

                                add_menu_page( 'Pods', 'Pods', 'read', $parent_page, null, PODS_URL . 'ui/images/icon16.png', '58.5' );
                            }

                            $all_title = $plural_label;
                            $all_label = __( 'Manage', 'pods' ) . ' ' . $plural_label;

                            if ( $page == pods_v( 'page' ) ) {
                                if ( 'edit' == pods_v( 'action', 'get', 'manage' ) )
                                    $all_title = __( 'Edit', 'pods' ) . ' ' . $singular_label;
                                elseif ( 'add' == pods_v( 'action', 'get', 'manage' ) )
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

                    $menu_label = pods_v( 'menu_icon', $pod, '', true );
                    $menu_label = apply_filters( 'pods_admin_menu_label', $menu_label, $pod );

                    $menu_position = pods_v( 'menu_icon', $pod, '', true );
                    $menu_icon = pods_evaluate_tags( pods_v( 'menu_icon', $pod, '', true ), true );

                    if ( empty( $menu_position ) )
                        $menu_position = null;

                    $menu_slug = 'edit-tags.php?taxonomy=' . $pod[ 'name' ];
                    $menu_location = pods_v( 'menu_location', $pod, 'default' );
                    $menu_location_custom = pods_v( 'menu_location_custom', $pod, '' );

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
                    if ( !pods_is_admin( array( 'pods', 'pods_content', 'pods_edit_' . $pod[ 'name' ] ) ) || !pods_permission( $pod ) )
                        continue;

                    $page_title = pods_var_raw( 'label', $pod, ucwords( str_replace( '_', ' ', $pod[ 'name' ] ) ), null, true );
                    $page_title = apply_filters( 'pods_admin_menu_page_title', $page_title, $pod );

                    $menu_label = pods_v( 'menu_name', $pod, $page_title, true );
                    $menu_label = apply_filters( 'pods_admin_menu_label', $menu_label, $pod );

                    $menu_position = pods_v( 'menu_position', $pod, '', true );
                    $menu_icon = pods_evaluate_tags( pods_v( 'menu_icon', $pod, '', true ), true );

                    if ( empty( $menu_position ) )
                        $menu_position = null;

                    $menu_slug = 'pods-settings-' . $pod[ 'name' ];
                    $menu_location = pods_v( 'menu_location', $pod, 'settings' );
                    $menu_location_custom = pods_v( 'menu_location_custom', $pod, '' );

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
			elseif ( 'pods' == pods_v( 'page' ) ) {
				$admin_menus[ 'pods' ][ 'title' ] = __( 'Edit Pod', 'pods' );

				if ( 'add' == pods_v( 'action_group' ) ) {
					$admin_menus[ 'pods' ][ 'title' ] = __( 'Add Field Group', 'pods' );
				}
				elseif ( 'edit' == pods_v( 'action_group' ) ) {
					$admin_menus[ 'pods' ][ 'title' ] = __( 'Edit Field Group', 'pods' );
				}
			}
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
                if ( !pods_is_admin( pods_v( 'access', $menu_item ) ) )
                    continue;

                // Don't just show the help page
                if ( false === $parent && 'pods-help' == $page )
                    continue;

                if ( !isset( $menu_item[ 'label' ] ) )
                    $menu_item[ 'label' ] = $page;

                if ( false === $parent ) {
                    $parent = $page;

                    $menu_title = __( 'Pods Admin', 'pods' );

                    if ( 'pods-upgrade' == $parent )
                        $menu_title = __( 'Pods Upgrade', 'pods' );

                    add_menu_page( $menu_title, $menu_title, 'read', $parent, null, PODS_URL . 'ui/images/icon16.png' );
                }

				$menu_title = $page_title = $menu_item[ 'label' ];

				if ( isset( $menu_item[ 'title' ] ) ) {
					$page_title = $menu_item[ 'title' ];
				}

                add_submenu_page( $parent, $page_title, $menu_title, 'read', $page, $menu_item[ 'function' ] );

                if ( 'pods-components' == $page )
                    Pods_Init::$components->menu( $parent );
            }
        }
    }

    public function upgrade_notice() {
        echo '<div class="error fade"><p>';
        echo sprintf(
            __( '<strong>NOTICE:</strong> Pods %s requires your action to complete the upgrade. Please run the <a href="%s">Upgrade Wizard</a>.', 'pods' ),
            PODS_VERSION,
            admin_url( 'admin.php?page=pods-upgrade' )
        );
        echo '</p></div>';
    }

    /**
     * Create Pods_UI content for the administration pages
     */
    public function admin_content() {

		global $pods;

        $pod_name = str_replace( array( 'pods-manage-', 'pods-add-new-' ), '', $_GET[ 'page' ] );

        $pods = pods( $pod_name, pods_v_sanitized( 'id', 'get', null, true ) );

        if ( false !== strpos( $_GET[ 'page' ], 'pods-add-new-' ) )
            $_GET[ 'action' ] = pods_v_sanitized( 'action', 'get', 'add' );

        $pods->ui();

    }

    /**
     * Create Pods_UI content for the settings administration pages
     */
    public function admin_content_settings() {

		global $pods;

        $pod_name = str_replace( 'pods-settings-', '', $_GET[ 'page' ] );

        $pods = pods( $pod_name );

        if ( 'custom' != pods_v( 'ui_style', $pods->pod_data, 'settings', true ) ) {
            $actions_disabled = array(
                'manage' => 'manage',
                'add' => 'add',
                'delete' => 'delete',
                'duplicate' => 'duplicate',
                'view' => 'view',
                'export' => 'export'
            );

            $_GET[ 'action' ] = 'edit';

            $page_title = pods_var_raw( 'label', $pods->pod_data, ucwords( str_replace( '_', ' ', $pods->pod_data[ 'name' ] ) ), null, true );

            $ui = array(
                'pod' => $pods,
                'fields' => array(
                    'edit' => $pods->pod_data[ 'fields' ]
                ),
                'header' => array(
                    'edit' => $page_title
                ),
                'label' => array(
                    'edit' => __( 'Save Changes', 'pods' )
                ),
                'style' => pods_v( 'ui_style', $pods->pod_data, 'settings', true ),
                'icon' => pods_evaluate_tags( pods_v( 'menu_icon', $pods->pod_data ), true ),
                'actions_disabled' => $actions_disabled
            );

            $ui = apply_filters( 'pods_admin_ui_' . $pods->pod, apply_filters( 'pods_admin_ui', $ui, $pods->pod, $pods ), $pods->pod, $pods );

            // Force disabled actions, do not pass go, do not collect $two_hundred
            $ui[ 'actions_disabled' ] = $actions_disabled;

            pods_ui( $ui );
        }
        else {
            do_action( 'pods_admin_ui_custom', $pods );
            do_action( 'pods_admin_ui_custom_' . $pods->pod, $pods );
        }

    }

    /**
     * Add media button for Pods shortcode
     *
     * @param $context
     *
     * @return string
     */
    public function media_button( $context = null ) {
        $current_page = basename( $_SERVER[ 'PHP_SELF' ] );
        $current_page = explode( '?', $current_page );
        $current_page = explode( '#', $current_page[ 0 ] );
        $current_page = $current_page[ 0 ];

        // Only show the button on post type pages
        if ( !in_array( $current_page, array( 'post-new.php', 'post.php' ) ) )
            return $context;

        add_action( 'admin_footer', array( $this, 'mce_popup' ) );

        echo '<style>';
        echo '.pod-media-icon { background:url(' . PODS_URL . 'ui/images/icon16.png) no-repeat top left; display: inline-block; height: 16px; margin: 0 2px 0 0; vertical-align: text-top; width: 16px; } .wp-core-ui a.pods-media-button { padding-left: 0.4em; }';
        echo '</style>';

        echo '<a href="#TB_inline?width=640&inlineId=pods_shortcode_form" class="thickbox button pods-media-button" title="Embed Content"><span class="pod-media-icon"></span> Embed Content</a>';
    }

    /**
     * Enqueue assets for Media Library Popup
     */
    public function register_media_assets() {
        if ( 'pods_media_attachment' == pods_v( 'inlineId' ) )
            wp_enqueue_style( 'pods-attach' );
    }

    /**
     * Output Pods shortcode popup window
     */
    public function mce_popup() {
        pods_view( PODS_DIR . 'ui/admin/shortcode.php', compact( array_keys( get_defined_vars() ) ) );
    }

    /**
     * Handle main Pods Setup area for managing Pods and Fields
     */
    public function admin_setup() {
        $all_pods = pods_api()->load_pods();

        $view = pods_v( 'view', 'get', 'all', true );

        if ( empty( $all_pods ) && !isset( $_GET[ 'action' ] ) )
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

		$data = array();

		foreach ( $all_pods as $the_pod ) {
			$pod = array(
                'id' => $the_pod[ 'id' ],
                'label' => $the_pod[ 'label' ],
                'name' => $the_pod[ 'name' ],
                'object' => $the_pod[ 'object' ],
                'type' => $the_pod[ 'type' ],
                'real_type' => $the_pod[ 'type' ],
                'storage' => $the_pod[ 'storage' ],
                'field_count' => count( $the_pod[ 'fields' ] )
			);

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
                    continue;
                }

                $pod[ 'type' ] = $types[ $pod[ 'type' ] ];
            }
            elseif ( 'all' != $view ) {
                continue;
			}

            $pod[ 'storage' ] = ucwords( $pod[ 'storage' ] );

            if ( $pod[ 'id' ] == pods_v( 'id' ) && 'delete' != pods_v( 'action' ) ) {
                $row = $pod;

				self::$admin_row = $the_pod;
			}

            $total_fields += $pod[ 'field_count' ];

			$data[ $pod[ 'id' ] ] = $pod;
        }

        if ( false === $row && 0 < pods_v( 'id' ) && 'delete' != pods_v( 'action' ) ) {
            pods_message( 'Pod not found', 'error' );

            unset( $_GET[ 'id' ] );
            unset( $_GET[ 'action' ] );
        }

		if ( false !== $row && !in_array( pods_v( 'action_group', 'get', 'manage' ), array( 'manage', 'delete' ) ) ) {
			$this->admin_setup_groups();

			return;
		}

        $ui = array(
            'data' => $data,
            'row' => $row,
            'total' => count( $data ),
            'total_found' => count( $data ),
            'icon' => PODS_URL . 'ui/images/icon32.png',
            'items' => 'Pods',
            'item' => 'Pod',
            'fields' => array(
                'manage' => $fields
            ),
            'actions_disabled' => array( 'view', 'export', 'bulk_delete' ),
            'actions_custom' => array(
                'add' => array( $this, 'admin_setup_add' ),
                'edit' => array( $this, 'admin_setup_edit' ),
                'duplicate' => array(
					'callback' => array( $this, 'admin_setup_duplicate' ),
					'restrict_callback' => array( $this, 'admin_setup_duplicate_restrict' )
				),
                'reset' => array(
                    'label' => __( 'Delete All Items', 'pods' ),
                    'confirm' => __( 'Are you sure you want to delete all items from this Pod? If this is an extended Pod, it will remove the original items extended too.', 'pods' ),
                    'callback' => array( $this, 'admin_setup_reset' ),
					'restrict_callback' => array( $this, 'admin_setup_reset_restrict' )
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
     * @param Pods_UI $obj
     */
    public function admin_setup_add( $obj ) {
        pods_view( PODS_DIR . 'ui/admin/setup-add.php', compact( array_keys( get_defined_vars() ) ) );
    }

    /**
     * Get the edit page of an object
     *
     * @param bool $duplicate
     * @param Pods_UI $obj
     */
    public function admin_setup_edit( $duplicate, $obj ) {

		$pods_admin =& $this;

        pods_view( PODS_DIR . 'ui/admin/setup-edit.php', compact( array_keys( get_defined_vars() ) ) );

    }

    /**
     * Duplicate a pod
     *
     * @param int $id
     * @param Pods_UI $obj
     *
     * @return mixed
     */
    public function admin_setup_duplicate( $obj ) {
        $new_id = pods_api()->duplicate_pod( array( 'id' => $obj->id ) );

        if ( 0 < $new_id ) {
            pods_redirect( pods_var_update( array( 'action' => 'edit', 'id' => $new_id, 'do' => 'duplicate' ) ) );
		}
		else {
			pods_message( 'Pod could not be duplicated', 'error' );

			$obj->manage();
		}
    }

	/**
	 * Restrict Duplicate action to custom types, not extended
	 *
	 * @param bool $restricted
	 * @param array $restrict
	 * @param string $action
	 * @param array $row
	 * @param Pods_UI $obj
	 *
	 * @since 2.3.10
	 */
	public function admin_setup_duplicate_restrict( $restricted, $restrict, $action, $row, $obj ) {

		if ( in_array( $row[ 'real_type' ], array( 'user', 'media', 'comment' ) ) ) {
			$restricted = true;
		}

		return $restricted;

	}

    /**
     * Reset a pod
     *
     * @param Pods_UI $obj
     *
     * @return mixed
     */
    public function admin_setup_reset( $obj, $id ) {
        $pod = pods_api()->load_pod( array( 'id' => $id ), __METHOD__ );

        if ( empty( $pod ) )
            return $obj->error( __( 'Pod not found.', 'pods' ) );

        pods_api()->reset_pod( array( 'id' => $id ) );

        $obj->message( __( 'Pod reset successfully.', 'pods' ) );

        $obj->manage();
    }

	/**
	 * Restrict Reset action from users and media
	 *
	 * @param bool $restricted
	 * @param array $restrict
	 * @param string $action
	 * @param array $row
	 * @param Pods_UI $obj
	 *
	 * @since 2.3.10
	 */
	public function admin_setup_reset_restrict( $restricted, $restrict, $action, $row, $obj ) {

		if ( in_array( $row[ 'real_type' ], array( 'user', 'media' ) ) ) {
			$restricted = true;
		}

		return $restricted;

	}

    /**
     * Delete a pod
     *
     * @param int $id
     * @param Pods_UI $obj
     *
     * @return mixed
     */
    public function admin_setup_delete( $id, $obj ) {
        $pod = pods_api()->load_pod( array( 'id' => $id ), __METHOD__ );

        if ( empty( $pod ) )
            return $obj->error( __( 'Pod not found.', 'pods' ) );

        pods_api()->delete_pod( array( 'id' => $id ) );

        unset( $obj->data[ $pod[ 'id' ] ] );

        $obj->total = count( $obj->data );
        $obj->total_found = count( $obj->data );

        $obj->message( __( 'Pod deleted successfully.', 'pods' ) );
    }

    /**
     * Handle main Pods Setup area for managing Pods and Fields
     */
    public function admin_setup_groups() {

		$field_groups = pods( '_pods_group' );

        $fields = array(
            'post_title' => array(
				'label' => __( 'Group title', 'pods' )
			),
            'rules' => array(
                'label' => __( 'Rules', 'pods' ),
				'custom_display' => array( $this, 'admin_setup_groups_field_rules' ),
                'width' => '40%'
            ),
            'field_count' => array(
                'label' => __( 'Number of Fields', 'pods' ),
				'custom_display' => array( $this, 'admin_setup_groups_field_count' ),
                'width' => '15%'
            )
        );

		$total_fields = count( self::$admin_row->fields() );

        $ui = array(
			'num' => 'group',
            'icon' => PODS_URL . 'ui/images/icon32.png',
            'items' => 'Field Groups',
            'item' => 'Field Group',
            'fields' => array(
                'manage' => $fields
            ),
            'actions_disabled' => array( 'view', 'export', 'bulk_delete', 'duplicate' ),
            'actions_custom' => array(
                'add' => array( $this, 'admin_setup_groups_add' ),
                'edit' => array( $this, 'admin_setup_groups_edit' ),
                'duplicate' => array( $this, 'admin_setup_groups_duplicate' ),
                'delete' => array( $this, 'admin_setup_groups_delete' )
            ),
			'params' => array(
				'where' => 't.post_parent = ' . self::$admin_row[ 'id' ]
			),
            'search' => false,
            'searchable' => false,
            'sortable' => true,
            'pagination' => false,
            'extra' => array(
                'total' => ', ' . number_format_i18n( $total_fields ) . ' ' . _n( 'field', 'fields', $total_fields, 'pods' )
            )
        );

		$field_groups->ui( $ui, true );

    }

    /**
     * Custom value handler for 'Rules' in the Groups Pods_UI
     *
     * @param array|Pods_Object|Pods_Object_Group $row Row data
     * @param Pods_UI $obj Pods_UI object
     * @param mixed $row_value Row value
     * @param string $field Field name
     * @param array|Pods_Object|Pods_Object_Field $attributes Field options
     * @param array $fields Fields
     */
	public function admin_setup_groups_field_rules( $row, $obj, $row_value, $field, $attributes, $fields ) {

		$options = $row->admin_options();

		$rules = array();

		foreach ( $options[ 'rules' ] as $option => $option_data ) {
			if ( 'rules_taxonomy' == $option ) {
				continue;
			}

			$value = $row[ $option ];

			if ( !empty( $value ) ) {
				$value = Pods_Form::field_method( 'pick', 'value_to_label', $option, $value, $option_data, $obj->pod->pod_data, $obj->id );

				if ( !empty( $value ) ) {
					$rule_label = $option_data[ 'label' ];
					$rule_label = str_replace( __( 'Show Group based on', 'pods' ) . ' ', '', $rule_label );

					$rules[ $rule_label ] = pods_serial_comma( $value );
				}
			}
		}

		$row_value = __( 'No rules set.', 'pods' );

		if ( !empty( $rules ) ) {
			$row_value = '<ul>';

			foreach ( $rules as $rule => $value ) {
				$row_value .= '<li><strong>' . esc_html( $rule ) . ':</strong> ' . esc_html( $value );
			}

			$row_value .= '</ul>';
		}

		return $row_value;

	}

    /**
     * Custom value handler for 'Field Count' in the Groups Pods_UI
     *
     * @param array|Pods_Object|Pods_Object_Group $row Row data
     * @param Pods_UI $obj Pods_UI object
     * @param mixed $row_value Row value
     * @param string $field Field name
     * @param array|Pods_Object|Pods_Object_Field $attributes Field options
     * @param array $fields Fields
     */
	public function admin_setup_groups_field_count( $row, $obj, $row_value, $field, $attributes, $fields ) {

		$field_count = count( $row->fields() );

		return $field_count;

	}

    /**
     * Get the add page of an object
     *
     * @param Pods_UI $obj
     */
    public function admin_setup_groups_add( $obj ) {

        pods_view( PODS_DIR . 'ui/admin/setup-edit-group.php', compact( array_keys( get_defined_vars() ) ) );

    }

    /**
     * Get the edit page of an object
     *
     * @param bool $duplicate
     * @param Pods_UI $obj
     */
    public function admin_setup_groups_edit( $duplicate, $obj ) {

        pods_view( PODS_DIR . 'ui/admin/setup-edit-group.php', compact( array_keys( get_defined_vars() ) ) );

    }

    /**
     * Duplicate a pod
     *
     * @param int $id
     * @param Pods_UI $obj
     *
     * @return mixed
     */
    public function admin_setup_groups_duplicate( $obj ) {

		$group = pods_object_group( null, $obj->id );

        if ( !$group->is_valid() )
            return $obj->error( __( 'Field Group not found.', 'pods' ) );

		$new_id = $group->duplicate();

        if ( 0 < $new_id ) {
            pods_redirect( pods_var_update( array( 'action' . $obj->num => 'edit', 'id' . $obj->num => $new_id, 'do' . $obj->num => 'duplicate' ) ) );
		}
		else {
			pods_message( 'Field Group could not be duplicated', 'error' );

			$obj->manage();
		}
    }

    /**
     * Delete a pod
     *
     * @param int $id
     * @param Pods_UI $obj
     *
     * @return mixed
     */
    public function admin_setup_groups_delete( $id, $obj ) {

		$group = pods_object_group( null, $obj->id );

        if ( !$group->is_valid() )
            return $obj->error( __( 'Field Group not found.', 'pods' ) );

		$group->delete();

        unset( $obj->data[ $obj->id ] );

        $obj->total = count( $obj->data );
        $obj->total_found = count( $obj->data );

        $obj->message( __( 'Field Group deleted successfully.', 'pods' ) );
    }

    /**
     * Get advanced administration view.
     */
    public function admin_advanced() {
        pods_view( PODS_DIR . 'ui/admin/advanced.php', compact( array_keys( get_defined_vars() ) ) );
    }

    /**
     * Get settings administration view
     */
    public function admin_settings() {
        pods_view( PODS_DIR . 'ui/admin/settings.php', compact( array_keys( get_defined_vars() ) ) );
    }

    /**
     * Get components administration UI
     */
    public function admin_components() {
        $components = Pods_Init::$components->components;

        $view = pods_v( 'view', 'get', 'all', true );

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
            elseif ( 'dev' == $view && pods_developer() && !pods_v( 'DeveloperMode', $component_data, false ) ) {
                unset( $components[ $component ] );

                continue;
            }
            elseif ( pods_v( 'DeveloperMode', $component_data, false ) && !pods_developer() ) {
                unset( $components[ $component ] );

                continue;
            }
            elseif ( !pods_v( 'TablelessMode', $component_data, false ) && pods_tableless() ) {
                unset( $components[ $component ] );

                continue;
            }

            $component_data[ 'Name' ] = strip_tags( $component_data[ 'Name' ] );

            if ( pods_v( 'DeveloperMode', $component_data, false ) )
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
                'mustuse' => pods_v( 'MustUse', $component_data, false ),
                'toggle' => 0
            );

            if ( !empty( $component_data[ 'category' ] ) ) {
                $category_url = pods_var_update( array( 'view' => sanitize_title( $component_data[ 'category' ] ), 'pg' => '', 'page' => $_GET[ 'page' ] ) );

                $component_data[ 'category' ] = '<a href="' . $category_url . '">' . $component_data[ 'category' ] . '</a>';
            }

            if ( isset( Pods_Init::$components->settings[ 'components' ][ $component_data[ 'id' ] ] ) && 0 != Pods_Init::$components->settings[ 'components' ][ $component_data[ 'id' ] ] )
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
						'text_allow_html' => true
                    ),
                    'category' => array(
                        'label' => __( 'Category', 'pods' ),
                        'width' => '10%',
                        'type' => 'text',
						'text_allow_html' => true
                    ),
                    'description' => array(
                        'label' => __( 'Description', 'pods' ),
                        'width' => '60%',
                        'type' => 'text',
						'text_allow_html' => true,
						'text_allowed_html_tags' => 'strong em a ul ol li b i br div'
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
     * @param Pods_UI $ui
     *
     * @return bool
     */
    public function admin_components_toggle( Pods_UI $ui ) {
        $component = $_GET[ 'id' ];

        if ( !empty( Pods_Init::$components->components[ $component ][ 'PluginDependency' ] ) ) {
            $dependency = explode( '|', Pods_Init::$components->components[ $component ][ 'PluginDependency' ] );

            if ( !pods_is_plugin_active( $dependency[ 1 ] ) ) {
                $website = 'http://wordpress.org/extend/plugins/' . dirname( $dependency[ 1 ] ) . '/';

                if ( isset( $dependency[ 2 ] ) )
                    $website = $dependency[ 2 ];

                if ( !empty( $website ) )
                    $website = ' ' . sprintf( __( 'You can find it at %s', 'pods' ), '<a href="' . $website . '" target="_blank">' . $website . '</a>' );

                $message = sprintf( __( 'The %s component requires that you have the <strong>%s</strong> plugin installed and activated.', 'pods' ), Pods_Init::$components->components[ $component ][ 'Name' ], $dependency[ 0 ] ) . $website;

                $ui->error( $message );

                $ui->manage();

                return;
            }
        }

        if ( !empty( Pods_Init::$components->components[ $component ][ 'ThemeDependency' ] ) ) {
            $dependency = explode( '|', Pods_Init::$components->components[ $component ][ 'ThemeDependency' ] );

            if ( strtolower( $dependency[ 1 ] ) != strtolower( get_template() ) && strtolower( $dependency[ 1 ] ) != strtolower( get_stylesheet() ) ) {
                $website = '';

                if ( isset( $dependency[ 2 ] ) )
                    $website = ' ' . sprintf( __( 'You can find it at %s', 'pods' ), '<a href="' . $dependency[ 2 ] . '" target="_blank">' . $dependency[ 2 ] . '</a>' );

                $message = sprintf( __( 'The %s component requires that you have the <strong>%s</strong> theme installed and activated.', 'pods' ), Pods_Init::$components->components[ $component ][ 'Name' ], $dependency[ 0 ] ) . $website;

                $ui->error( $message );

                $ui->manage();

                return;
            }
        }

        if ( !empty( Pods_Init::$components->components[ $component ][ 'MustUse' ] ) ) {
            $message = sprintf( __( 'The %s component can not be disabled from here. You must deactivate the plugin or theme that added it.', 'pods' ), Pods_Init::$components->components[ $component ][ 'Name' ] );

            $ui->error( $message );

            $ui->manage();

            return;
        }

        if ( 1 == pods_v( 'toggled' ) ) {
            $toggle = Pods_Init::$components->toggle( $component );

            if ( true === $toggle )
                $ui->message( Pods_Init::$components->components[ $component ][ 'Name' ] . ' ' . __( 'Component enabled', 'pods' ) );
            elseif ( false === $toggle )
                $ui->message( Pods_Init::$components->components[ $component ][ 'Name' ] . ' ' . __( 'Component disabled', 'pods' ) );

            $components = Pods_Init::$components->components;

            foreach ( $components as $component => &$component_data ) {
                $toggle = 0;

                if ( isset( Pods_Init::$components->settings[ 'components' ][ $component_data[ 'ID' ] ] ) ) {
                    if ( 0 != Pods_Init::$components->settings[ 'components' ][ $component_data[ 'ID' ] ] )
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
        elseif ( 1 == pods_v( 'toggle' ) )
            $ui->message( Pods_Init::$components->components[ $component ][ 'Name' ] . ' ' . __( 'Component enabled', 'pods' ) );
        else
            $ui->message( Pods_Init::$components->components[ $component ][ 'Name' ] . ' ' . __( 'Component disabled', 'pods' ) );

        $ui->manage();
    }

    /**
     * Get the admin upgrade page
     */
    public function admin_upgrade() {
        foreach ( Pods_Init::$upgrades as $old_version => $new_version ) {
            if ( version_compare( $old_version, Pods_Init::$version_last, '<=' ) && version_compare( Pods_Init::$version_last, $new_version, '<' ) ) {
                $new_version = str_replace( '.', '_', $new_version );

                pods_view( PODS_DIR . 'ui/admin/upgrade/upgrade_' . $new_version . '.php', compact( array_keys( get_defined_vars() ) ) );

                break;
            }
        }
    }

    /**
     * Get the admin help page
     */
    public function admin_help() {
        pods_view( PODS_DIR . 'ui/admin/help.php', compact( array_keys( get_defined_vars() ) ) );
    }

    /**
     * Add pods specific capabilities.
     *
     * @param $capabilities List of extra capabilities to add
     *
     * @return array
     */
    public function admin_capabilities( $capabilities ) {
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
                $capability_type = pods_var( 'capability_type_custom', $pod, pods_v( 'name', $pod ) );

                if ( 'custom' == pods_v( 'capability_type', $pod ) && 0 < strlen( $capability_type ) ) {
                    $capabilities[] = 'read_' . $capability_type;
                    $capabilities[] = 'edit_' . $capability_type;
                    $capabilities[] = 'delete_' . $capability_type;

                    if ( 1 == pods_v( 'capability_type_extra', $pod, 1 ) ) {
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
                if ( 1 == pods_v( 'capabilities', $pod, 0 ) ) {
                    $capability_type = pods_var( 'capability_type_custom', $pod, pods_v( 'name', $pod ) . 's' );

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

                $actions_enabled = pods_v( 'ui_actions_enabled', $pod );

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
                elseif ( 1 == pods_v( 'ui_export', $pod, 0 ) )
                    $capabilities[] = 'pods_export_' . $pod[ 'name' ];
            }
        }

        return $capabilities;
    }

    /**
     * Handle ajax calls for the administration
     */
    public function admin_ajax() {
        if ( false === headers_sent() ) {
			pods_session_start();

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
            'save_pod_group' => array( 'priv' => true ),
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
            elseif ( 'save_pod_group' == $method->name ) {
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
