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

        $this->rest_admin();

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

            /**
             * Admin AJAX Callbacks
             *
             * @since unknown
             *
             * @param array $pods_admin_ajax_actions Array of actions to handle
             */
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

        wp_register_style( 'pods-font', PODS_URL . 'ui/css/pods-font.css', array(), PODS_VERSION );

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
                var PODS_URL = "<?php echo esc_js( PODS_URL ); ?>";
            </script>
            <?php
                wp_enqueue_script( 'jquery' );
                wp_enqueue_script( 'jquery-ui-core' );
                wp_enqueue_script( 'jquery-ui-sortable' );

                wp_enqueue_style( 'jquery-ui' );

                wp_enqueue_script( 'pods-floatmenu' );

                wp_enqueue_style( 'jquery-qtip2' );
                wp_enqueue_script( 'jquery-qtip2' );
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

        wp_enqueue_style( 'pods-font' );
    }

    /**
     * Build the admin menus
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
                                    $menu_position = null;
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
                                    $menu_position = null;
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

                                add_menu_page( 'Pods', 'Pods', 'read', $parent_page, null, 'dashicons-pods', '58.5' );
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

                                add_menu_page( 'Pods', 'Pods', 'read', $parent_page, null, 'dashicons-pods', '58.5' );
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
                            $menu_position = null;
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
                            $menu_position = null;
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

            add_filter( 'parent_file' , array( $this, 'parent_file' ) );
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

		/**
		 * Add or change Pods Admin menu items
		 *
		 * @params array $admin_menus The submenu items in Pods Admin menu.
		 *
		 * @since unknown
		 */
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

                    add_menu_page( $menu, $menu, 'read', $parent, null, 'dashicons-pods' );
                }

                add_submenu_page( $parent, $menu_item[ 'label' ], $menu_item[ 'label' ], 'read', $page, $menu_item[ 'function' ] );

                if ( 'pods-components' == $page )
                    PodsInit::$components->menu( $parent );
            }
        }
    }

    /**
     * Set the correct parent_file to highlight the correct top level menu
     *
     * @param $parent_file The parent file
     *
     * @return mixed|string
     *
     * @since unknown
     */
    public function parent_file ( $parent_file ) {
        global $current_screen;

        if ( isset( $current_screen ) && ! empty( $current_screen->taxonomy ) ) {
            $taxonomies = PodsMeta::$taxonomies;
            if ( !empty( $taxonomies ) ) {
                foreach ( (array) $taxonomies as $pod ) {
                    if ( $current_screen->taxonomy !== $pod[ 'name' ] )
                        continue;

                    $menu_slug = 'edit-tags.php?taxonomy=' . $pod[ 'name' ];
                    $menu_location = pods_var( 'menu_location', $pod[ 'options' ], 'default' );
                    $menu_location_custom = pods_var( 'menu_location_custom', $pod[ 'options' ], '' );

                    if ( 'settings' == $menu_location )
                        $parent_file = 'options-general.php';
                    elseif ( 'appearances' == $menu_location )
                        $parent_file = 'themes.php';
                    elseif ( 'objects' == $menu_location )
                        $parent_file = $menu_slug;
                    elseif ( 'top' == $menu_location )
                        $parent_file = $menu_slug;
                    elseif ( 'submenu' == $menu_location && !empty( $menu_location_custom ) ) {
                        $parent_file = $menu_location_custom;
                    }

                    break;
                }
            }
        }

        if ( isset( $current_screen ) && ! empty( $current_screen->post_type ) ) {
            global $submenu_file;
            $components = PodsInit::$components->components;
            foreach ( $components as $component => $component_data ) {
                if ( ! empty( $component_data[ 'MenuPage' ] ) && $parent_file === $component_data[ 'MenuPage' ] ) {
                    $parent_file = 'pods';
                    $submenu_file = $component_data[ 'MenuPage' ];
                }
            }
        }

        return $parent_file;
    }

    public function upgrade_notice () {
        echo '<div class="error fade"><p>';
        echo sprintf(
            __( '<strong>NOTICE:</strong> Pods %s requires your action to complete the upgrade. Please run the <a href="%s">Upgrade Wizard</a>.', 'pods' ),
            esc_html( PODS_VERSION ),
            esc_url( admin_url( 'admin.php?page=pods-upgrade' ) )
        );
        echo '</p></div>';
    }

    /**
     * Create PodsUI content for the administration pages
     */
    public function admin_content () {
        $pod_name = str_replace( array( 'pods-manage-', 'pods-add-new-' ), '', $_GET[ 'page' ] );

        $pod = pods( $pod_name, pods_var( 'id', 'get', null, null, true ) );

        if ( false !== strpos( $_GET[ 'page' ], 'pods-add-new-' ) )
            $_GET[ 'action' ] = pods_var( 'action', 'get', 'add' );

        $pod->ui();
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

            $page_title = pods_var_raw( 'label', $pod->pod_data, ucwords( str_replace( '_', ' ', $pod->pod_data[ 'name' ] ) ), null, true );

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
        // If shortcodes are disabled don't show the button
        if ( defined( 'PODS_DISABLE_SHORTCODE' ) && PODS_DISABLE_SHORTCODE ) {
            return '';
        }

		/**
		 * Filter to remove Pods shortcode button from the post editor.
		 *
		 * @param bool. Set to false to block the shortcode button from appearing.
		 * @param string $context
		 *
		 * @since 2.3.19
		 */
		if ( !apply_filters( 'pods_admin_media_button', true, $context ) ) {
			return '';
		}

        $current_page = basename( $_SERVER[ 'PHP_SELF' ] );
        $current_page = explode( '?', $current_page );
        $current_page = explode( '#', $current_page[ 0 ] );
        $current_page = $current_page[ 0 ];

        // Only show the button on post type pages
        if ( !in_array( $current_page, array( 'post-new.php', 'post.php' ) ) )
            return '';

        add_action( 'admin_footer', array( $this, 'mce_popup' ) );

        echo '<a href="#TB_inline?width=640&inlineId=pods_shortcode_form" class="thickbox button" id="add_pod_button" title="Pods Shortcode"><img style="padding: 0px 6px 0px 0px; margin: -3px 0px 0px;" src="' . PODS_URL . 'ui/images/icon16.png" alt="' . __('Pods Shortcode' ,'pods') . '" />' . __('Pods Shortcode' ,'pods') . '</a>';
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
                pods_redirect( pods_query_arg( array( 'page' => 'pods', 'action' => $_GET[ 'action' ] ) ) );
            else
                $_GET[ 'action' ] = 'add';
        }
        elseif ( isset( $_GET[ 'action' ] ) && 'add' == $_GET[ 'action' ] )
            pods_redirect( pods_query_arg( array( 'page' => 'pods-add-new', 'action' => '' ) ) );

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

				$pod[ 'real_type' ] = $pod[ 'type' ];
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
                'object' => pods_var_raw( 'object', $pod ),
                'type' => pods_var_raw( 'type', $pod ),
                'real_type' => pods_var_raw( 'real_type', $pod ),
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
            'items' => 'Pods',
            'item' => 'Pod',
            'fields' => array(
                'manage' => $fields
            ),
            'actions_disabled' => array( 'view', 'export' ),
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
					'restrict_callback' => array( $this, 'admin_setup_reset_restrict' ),
					'nonce' => true
                ),
                'delete' => array( $this, 'admin_setup_delete' )
            ),
            'action_links' => array(
                'add' => pods_query_arg( array( 'page' => 'pods-add-new', 'action' => '', 'id' => '', 'do' => '' ) )
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

        if ( ! function_exists( 'get_term_meta' ) && 'none' == pods_var( 'storage', $pod, 'none', null, true ) && 'taxonomy' == pods_var( 'type', $pod ) )
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

		$addtl_args = compact( array( 'fields', 'labels', 'admin_ui', 'advanced' ) );

		/**
		 * Add or modify tabs in Pods editor for a specific Pod
		 *
		 * @params array $tabs Tabs to set.
		 * @params object $pod Current Pods object
		 * @params array $addtl_args Additional args.
		 *
		 * @since unknown
		 */
		$tabs = apply_filters( 'pods_admin_setup_edit_tabs_' . $pod[ 'type' ] . '_' . $pod[ 'name' ], $tabs, $pod, $addtl_args );

		/**
		 * Add or modify tabs for any Pod in Pods editor of a specific post type.
		 */
		$tabs = apply_filters( 'pods_admin_setup_edit_tabs_' . $pod[ 'type' ], $tabs, $pod, $addtl_args );

		/**
		 * Add or modify tabs in Pods editor for all pods.
		 */
		$tabs = apply_filters( 'pods_admin_setup_edit_tabs', $tabs, $pod, $addtl_args );

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
                    'label' => __( 'Menu Icon', 'pods' ),
                    'help' => __( 'URL or Dashicon name for the menu icon. You may specify the path to the icon using one of the <a href="http://pods.io/docs/build/special-magic-tags/#site-tags" target="_blank">site tag</a> type <a href="http://pods.io/docs/build/special-magic-tags/" target="_blank">special magic tags</a>. For example, for a file in your theme directory, use "{@template-url}/path/to/image.png". You may also use the name of a <a href="https://developer.wordpress.org/resource/dashicons/" target="_blank">Dashicon</a>. For example, to use the empty star icon, use "dashicons-star-empty".', 'pods' ),
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
                    'label' => __( 'Enable Archive Page', 'pods' ),
                    'help' => __( 'If enabled, creates an archive page with list of of items in this custom post type. Functions like a category page for posts. Can be controlled with a template in your theme called "archive-{$post-type}.php".', 'pods' ),
                    'type' => 'boolean',
                    'default' => false,
                    'dependency' => true,
                    'boolean_yes_label' => ''
                ),
                'has_archive_slug' => array(
                    'label' => __( 'Archive Page Slug Override', 'pods' ),
                    'help' => __( 'If archive page is enabled, you can override the slug used by WordPress, which defaults to the name of the post type.', 'pods' ),
                    'type' => 'text',
                    'default' => '',
                    'depends-on' => array( 'has_archive' => true )
                ),
                'hierarchical' => array(
                    'label' => __( 'Hierarchical', 'pods' ),
                    'help' => __( 'Allows for parent/ child relationships between items, just like with Pages. Note: To edit relationships in the post editor, you must enable "Page Attributes" in the "Supports" section below.', 'pods' ),
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
                    'help' => __( 'Allows you to use pretty permalinks, if set in WordPress Settings->Reading. If not enbabled, your links will be in the form of "example.com/?pod_name=post_slug" regardless of your permalink settings.', 'pods' ),
                    'type' => 'boolean',
                    'default' => true,
                    'dependency' => true,
                    'boolean_yes_label' => ''
                ),
                'rewrite_custom_slug' => array(
                    'label' => __( 'Custom Rewrite Slug', 'pods' ),
                    'help' => __( 'Changes the first segment of the URL, which by default is the name of the Pod. For example, if your Pod is called "foo", if this field is left blank, your link will be "example.com/foo/post_slug", but if you were to enter "bar" your link will be "example.com/bar/post_slug".', 'pods' ),
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
                    'help' => __( 'The Query Var is used in the URL and underneath the WordPress Rewrite API to tell WordPress what page or post type you are on. For a list of reserved Query Vars, read <a href="http://codex.wordpress.org/WordPress_Query_Vars">WordPress Query Vars</a> from the WordPress Codex.', 'pods' ),
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
                ),
                'default_status' => array(
                    'label' => __( 'Default Status', 'pods' ),
                    'help' => __( 'help', 'pods' ),
                    'type' => 'pick',
                    'pick_object' => 'post-status',
                    'default' => apply_filters( 'pods_api_default_status_' . pods_var_raw( 'name', $pod, 'post_type', null, true ), 'draft', $pod )
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

			// Integration for Single Value Taxonomy UI
			if ( function_exists( 'tax_single_value_meta_box' ) ) {
                $options[ 'admin-ui' ][ 'single_value' ] = array(
                    'label' => __( 'Single Value Taxonomy', 'pods' ),
                    'help' => __( 'Use a drop-down for the input instead of the WordPress default', 'pods' ),
                    'type' => 'boolean',
                    'default' => false,
                    'boolean_yes_label' => ''
                );

                $options[ 'admin-ui' ][ 'single_value_required' ] = array(
                    'label' => __( 'Single Value Taxonomy - Required', 'pods' ),
                    'help' => __( 'A term will be selected by default in the Post Editor, not optional', 'pods' ),
                    'type' => 'boolean',
                    'default' => false,
                    'boolean_yes_label' => ''
                );
			}

            $options[ 'advanced' ] = array(
                'public' => array(
                    'label' => __( 'Public', 'pods' ),
                    'help' => __( 'help', 'pods' ),
                    'type' => 'boolean',
                    'default' => true,
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
                'label_no_terms' => array(
                    'label' => __( '<strong>Label: </strong> No <span class="pods-slugged" data-sluggable="label">Items</span>', 'pods' ),
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
                    'label' => __( 'Allow Front Prepend', 'pods' ),
                    'help' => __( 'Allows permalinks to be prepended with front base (example: if your permalink structure is /blog/, then your links will be: Checked->/news/, Unchecked->/blog/news/)', 'pods' ),
                    'type' => 'boolean',
                    'default' => true,
                    'boolean_yes_label' => '',
                    'depends-on' => array( 'rewrite' => true )
                ),
                'rewrite_hierarchical' => array(
                    'label' => __( 'Hierarchical Permalinks', 'pods' ),
                    'help' => __( 'help', 'pods' ),
                    'type' => 'boolean',
                    'default' => true,
                    'boolean_yes_label' => '',
                    'depends-on' => array( 'rewrite' => true )
                ),
                'capability_type' => array(
                    'label' => __( 'User Capability', 'pods' ),
                    'help' => __( 'Uses WordPress term capabilities by default', 'pods' ),
                    'type' => 'pick',
                    'default' => 'default',
                    'data' => array(
                        'default' => 'Default',
                        'custom' => __( 'Custom Capability', 'pods' )
                    ),
                    'dependency' => true
                ),
                'capability_type_custom' => array(
                    'label' => __( 'Custom User Capability', 'pods' ),
                    'help' => __( 'Enables additional capabilities for this Taxonomy including: manage_{capability}_terms, edit_{capability}_terms, assign_{capability}_terms, and delete_{capability}_terms', 'pods' ),
                    'type' => 'text',
                    'default' => pods_v( 'name', $pod ),
                    'depends-on' => array( 'capability_type' => 'custom' )
                ),
                'query_var' => array(
                    'label' => __( 'Query Var', 'pods' ),
                    'help' => __( 'help', 'pods' ),
                    'type' => 'boolean',
                    'default' => false,
                    'boolean_yes_label' => ''
                ),
                'query_var' => array(
                    'label' => __( 'Query Var', 'pods' ),
                    'help' => __( 'help', 'pods' ),
                    'type' => 'boolean',
                    'default' => false,
                    'dependency' => true,
                    'boolean_yes_label' => ''
                ),
                'query_var_string' => array(
                    'label' => __( 'Custom Query Var Name', 'pods' ),
                    'help' => __( 'help', 'pods' ),
                    'type' => 'text',
                    'default' => '',
                    'depends-on' => array( 'query_var' => true )
                ),
                'sort' => array(
                    'label' => __( 'Remember order saved on Post Types', 'pods' ),
                    'help' => __( 'help', 'pods' ),
                    'type' => 'boolean',
                    'default' => false,
                    'boolean_yes_label' => ''
                ),
                'update_count_callback' => array(
                    'label' => __( 'Function to call when updating counts', 'pods' ),
                    'help' => __( 'help', 'pods' ),
                    'type' => 'text',
                    'default' => ''
                ),
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

		/**
		 * Add admin fields to the Pods editor for a specific Pod
		 *
		 * @params array $options The Options fields
		 * @params object $pod Current Pods object
		 *
		 * @since unkown
		 */
		$options = apply_filters( 'pods_admin_setup_edit_options_' . $pod[ 'type' ] . '_' . $pod[ 'name' ], $options, $pod );

		/**
		 * Add admin fields to the Pods editor for any Pod of a specific content type.
		 */
		$options = apply_filters( 'pods_admin_setup_edit_options_' . $pod[ 'type' ], $options, $pod );

		/**
		 * Add admin fields to the Pods editor for all Pods
		 */
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

        /**
         * Field option tabs
         *
         * Use to add new tabs, default tabs are added after this filter (IE you can't remove/modify them with this, kthanksbye).
         *
         * @since unknown
         *
         * @param array $tabs Tabs to add, starts empty
         * @param object|Pod Current Pods object
         */
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
            unset( $options[ 'advanced' ][ 'input_helper' ] );

        /**
         * Modify tabs and their contents for field options
         *
         * @since unknown
         *
         * @param array $options Tabs, indexed by label
         * @param object|Pods Pods object for the Pod this UI is for.
         */
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
    public function admin_setup_duplicate ( $obj ) {
        $new_id = pods_api()->duplicate_pod( array( 'id' => $obj->id ) );

        if ( 0 < $new_id )
            pods_redirect( pods_query_arg( array( 'action' => 'edit', 'id' => $new_id, 'do' => 'duplicate' ) ) );
    }

	/**
	 * Restrict Duplicate action to custom types, not extended
	 *
	 * @param bool $restricted
	 * @param array $restrict
	 * @param string $action
	 * @param array $row
	 * @param PodsUI $obj
	 *
	 * @since 2.3.10
     *
     * @return bool
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
     * @param $obj
     *
     * @return mixed
     */
    public function admin_setup_reset ( $obj, $id ) {
        $pod = pods_api()->load_pod( array( 'id' => $id ), false );

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
	 * @param PodsUI $obj
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
     * @param $id
     * @param $obj
     *
     * @return mixed
     */
    public function admin_setup_delete ( $id, $obj ) {
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
                $category_url = pods_query_arg( array( 'view' => sanitize_title( $component_data[ 'category' ] ), 'pg' => '', 'page' => $_GET[ 'page' ] ) );

                $component_data[ 'category' ] = '<a href="' . esc_url( $category_url ) . '">' . $component_data[ 'category' ] . '</a>';
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
                'toggle' => array(
	                'callback' => array( $this, 'admin_components_toggle' ),
                    'nonce' => true
                )
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

        if ( '1' == pods_v( 'toggled' ) ) {
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

            $url = pods_query_arg( array( 'toggled' => null ) );

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
        $pods = pods_api()->load_pods( array( 'type' => array( 'settings', 'post_type', 'taxonomy' ), 'fields' => false, 'table_info' => false ) );
        $other_pods = pods_api()->load_pods( array( 'type' => array( 'pod', 'table' ), 'table_info' => false ) );

        $pods = array_merge( $pods, $other_pods );

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
                if ( 'custom' == pods_var( 'capability_type', $pod[ 'options' ], 'terms' ) ) {
                    $capability_type = pods_var( 'capability_type_custom', $pod[ 'options' ], pods_var_raw( 'name', $pod ) . 's' );

                    $capability_type .= '_terms';

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
			pods_session_start();

            header( 'Content-Type: text/html; charset=' . get_bloginfo( 'charset' ) );
        }

        // Sanitize input
        $params = pods_unslash( (array) $_POST );

		foreach ( $params as $key => $value ) {
			if ( 'action' == $key )
				continue;

			// Fixup $_POST data
			$_POST[ str_replace( '_podsfix_', '', $key ) ] = $_POST[ $key ];

			// Fixup $params with unslashed data
			$params[ str_replace( '_podsfix_', '', $key ) ] = $value;

			// Unset the _podsfix_* keys
			unset( $params[ $key ] );
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

        /**
         * AJAX Callbacks in field editor
         *
         * @since unknown
         *
         * @param array $method Callback method map
         * @param object|PodsAdmin Class object
         */
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

	    $api->display_errors = false;

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

	/**
	 * Profiles the Pods configuration
	 *
	 * @param null|string|array $pod. Optional. Which Pod(s) to get configuration for. Can be a the name of one Pod, or an array of names of Pods, or null, which is the default, to profile all Pods.
	 * @param bool $full_field_info Optional. If true all info about each field is returned. If false, which is the default only name and type, will be returned.
	 *
	 * @return array
	 *
	 * @since 3.0.0
	 */
	function configuration( $pod = null, $full_field_info = false ){
		$api = pods_api();

		if ( is_null( $pod ) ) {
			$the_pods = $api->load_pods();
		}
		elseif( is_array( $pod ) ) {
			foreach ( $pod as $p ) {
				$the_pods[] = $api->load_pod( $p );
			}
		}
		else {
			$the_pods[] = $api->load_pod( $pod );
		}

		foreach( $the_pods as $pod ) {
			$configuration[ $pod[ 'name' ] ] = array(
				'name' 		=> $pod['name'],
				'ID' 		=> $pod[ 'id' ],
				'storage' 	=> $pod[ 'storage' ],
				'fields' 	=> $pod[ 'fields' ],
			);
		}

		if ( ! $full_field_info ) {
			foreach ( $the_pods as $pod ) {
				$fields = $configuration[ $pod['name'] ][ 'fields' ];
				unset( $configuration[ $pod['name'] ][ 'fields' ] );
				foreach ( $fields as $field ) {
					$info = array (
						'name' => $field[ 'name' ],
						'type' => $field[ 'type' ],
					);

					if ( $info[ 'type' ]  === 'pick' ) {
						$info[ 'pick_object' ] = $field[ 'pick_object' ];
						if ( isset ( $field[ 'pick_val' ] ) && $field[ 'pick_val' ] !== '' ) {
							$info[ 'pick_val' ] = $field[ 'pick_val' ];
						}
					}

					if ( is_array( $info ) ) {
						$configuration[ $pod[ 'name' ] ][ 'fields' ][ $field[ 'name' ] ] = $info;
					}

					unset( $info );

				}

			}

		}

		if ( is_array ( $configuration ) ) {
			return $configuration;

		}

	}

    /**
     * Build UI for extending REST API, if makes sense to do so.
     *
     * @since 2.6.0
     *
     * @access protected
     */
    protected function rest_admin() {
	    if( function_exists( 'register_rest_field' ) ) {
		    add_filter( 'pods_admin_setup_edit_field_options', array( $this, 'add_rest_fields_to_field_editor' ), 12, 2 );
		    add_filter( 'pods_admin_setup_edit_field_tabs', array( $this, 'add_rest_field_tab' ), 12 );
	    }

	    add_filter( 'pods_admin_setup_edit_tabs', array( $this, 'add_rest_settings_tab' ), 12, 2 );
	    add_filter( 'pods_admin_setup_edit_options', array( $this, 'add_rest_settings_tab_fields' ), 12, 2 );

    }

    /**
     * Check if Pod type <em>could</em> extend core REST API response
     *
     * @since 2.5.6
     *
     * @access protected
     *
     * @param array $pod
     *
     * @return bool
     */
    protected function restable_pod( $pod ) {
        $type =  $pod[ 'type' ];
        if( in_array( $type, array(
                'post_type',
                'user',
                'taxonomy'
            )
        )
        ) {
            return true;

        }

    }


    /**
     * Add a rest api tab.
     *
     * @since 2.6.0
     *
     * @param array $tabs
     * @param array $pod
     *
     * @return array
     */
    public function add_rest_settings_tab( $tabs, $pod ) {

        $tabs[ 'rest-api' ] = __( 'REST API', 'pods' );

        return $tabs;

    }

    /**
     * Populate REST API tab.
     *
     * @since 0.1.0
     *
     * @param array $options
     * @param array $pod
     *
     * @return array
     */
    public function add_rest_settings_tab_fields( $options, $pod ) {
	    if ( ! function_exists( 'register_rest_field' ) ) {
		    $options[ 'rest-api' ] = array(
			    'no_dependencies' => array(
				    'label'      => __( sprintf( 'Pods REST API support requires WordPress 4.3.1 or later and the %s or later.', '<a href="http://pods.io/docs/build/extending-core-wordpress-rest-api-routes-with-pods/" target="_blank">WordPress REST API 2.0-beta9</a>' ), 'pods' ),
				    'help'       => __( sprintf( 'See %s for more information.', '<a href="http://pods.io/docs/build/extending-core-wordpress-rest-api-routes-with-pods/" target="_blank">http://pods.io/docs/build/extending-core-wordpress-rest-api-routes-with-pods/</a>'), 'pods' ),
				    'type'       => 'html',
			    ),
		    );
	    } elseif ( $this->restable_pod( $pod ) ) {
		    $options[ 'rest-api' ] = array(
			    'rest_enable' => array(
				    'label'      => __( 'Enable', 'pods' ),
				    'help'       => __( 'Add REST API support for this Pod.', 'pods' ),
				    'type'       => 'boolean',
				    'default'    => '',
				    'dependency' => true,
			    ),
			    'rest_base'   => array(
				    'label'             => __( 'REST Base', 'pods' ),
				    'help'              => __( 'This will form the url for the route.', 'pods' ),
				    'type'              => 'text',
				    'default'           => pods_v( 'name', $pod ),
				    'boolean_yes_label' => '',
				    'depends-on'        => array( 'rest_enable' => true ),
			    ),
			    'read_all'    => array(
				    'label'             => __( 'Show All Fields?', 'pods' ),
				    'help'              => __( 'Show all fields in REST API. If unchecked fields must be enabled on a field by field basis.', 'pods' ),
				    'type'              => 'boolean',
				    'default'           => '',
				    'boolean_yes_label' => '',
				    'depends-on'        => array( 'rest_enable' => true ),
			    ),
			    'write_all'   => array(
				    'label'             => __( 'Allow All Fields To Be Updated?', 'pods' ),
				    'help'              => __( 'Allow all fields to be updated via the REST API. If unchecked fields must be enabled on a field by field basis.', 'pods' ),
				    'type'              => 'boolean',
				    'default'           => pods_v( 'name', $pod ),
				    'boolean_yes_label' => '',
				    'depends-on'        => array( 'rest_enable' => true ),
			    )

		    );

	    } else {
		    $options[ 'rest-api' ] = array(
			    'not_restable' => array(
				    'label'      => __( 'Pods REST API support covers post type, taxonomy and user Pods.', 'pods' ),
				    'help'       => __( sprintf( 'See %s for more information.', '<a href="http://pods.io/docs/build/extending-core-wordpress-rest-api-routes-with-pods/" target="_blank">http://pods.io/docs/build/extending-core-wordpress-rest-api-routes-with-pods/"</a>'), 'pods' ),
				    'type'       => 'html',
			    ),
		    );

	    }


        return $options;

    }

    /**
     * Add a REST API section to advanced tab of field editor.
     *
     * @since 2.5.6
     *
     * @param array $options
     * @param array $pod
     *
     * @return array
     */
    public function add_rest_fields_to_field_editor( $options, $pod ) {

        if( $this->restable_pod( $pod ) ) {
            $options[ 'rest' ][ __( 'Read/ Write', 'pods' ) ] =
                array(
                    'rest_read' => array(
                        'label' => __( 'Read via REST API?', 'pods' ),
                        'help' => __( 'Should this field be readable via the REST API? You must enable REST API support for this Pod.', 'pods' ),
                        'type' => 'boolean',
                        'default' => '',
                    ),
                    'rest_write' => array(
                        'label' => __( 'Write via REST API?', 'pods' ),
                        'help' => __( 'Should this field be readable via the REST API? You must enable REST API support for this Pod.', 'pods' ),
                        'type' => 'boolean',
                        'default' => '',
                    ),
                );
            $options[ 'rest' ][ __( 'Relationship Field Options', 'pods' ) ] =
                array(
                    'rest_pick_response' => array(
                        'label' => __( 'Response Type', 'pods' ),
                        'help' => __( 'Should this field be readable via the REST API? You must enable REST API support for this Pod.', 'pods' ),
                        'type' => 'pick',
                        'default' => 'array',
                        'depends-on' => array( 'type' => 'pick' ),
                        'data' => array(
                            'array' => __( 'Full', 'pods' ),
                            'id' => __( 'ID only', 'pods' ),
                            'name' => __( 'Name', 'pods' )

                        ),
                    ),
                    'rest_pick_depth' => array(
                        'label' => __( 'Depth', 'pods' ),
                        'help' => __( 'How far to traverse relationships in response', 'pods' ),
                        'type' => 'number',
                        'default' => '2',
                        'depends-on' => array( 'type' => 'pick' ),

                    )

                );


        }

        return $options;

    }

    /**
     * Add REST field tab
     *
     * @since 2.5.6
     *
     * @param array $tabs
     *
     * @return array
     */
    public function add_rest_field_tab( $tabs ) {
        $tabs[ 'rest' ] = __( 'REST API', 'pods' );
        return $tabs;
    }

}
