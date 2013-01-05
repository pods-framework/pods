<?php
/**
 * Component managing class
 *
 * @package Pods
 */
class PodsComponents {

    /**
     * Root of Components directory
     *
     * @var string
     *
     * @private
     * @since 2.0.0
     */
    private $components_dir = null;

    /**
     * Available components
     *
     * @var string
     *
     * @since 2.0.0
     */
    public $components = array();

    /**
     * Components settings
     *
     * @var string
     *
     * @since 2.0.0
     */
    public $settings = array();

    /**
     * Setup actions and get options
     *
     * @since 2.0.0
     */
    public function __construct () {
        $this->components_dir = realpath( apply_filters( 'pods_components_dir', PODS_DIR . 'components' ) ) . '/';

        $settings = get_option( 'pods_component_settings', '' );

        if ( !empty( $settings ) )
            $this->settings = (array) json_decode( $settings, true );

        if ( !isset( $this->settings[ 'components' ] ) )
            $this->settings[ 'components' ] = array();

        // Get components (give it access to theme)
        add_action( 'setup_theme', array( $this, 'get_components' ), 11 );

        // Load in components
        add_action( 'setup_theme', array( $this, 'load' ), 12 );

        // AJAX handling
        if ( is_admin() ) {
            add_action( 'wp_ajax_pods_admin_components', array( $this, 'admin_ajax' ) );
            add_action( 'wp_ajax_nopriv_pods_admin_components', array( $this, 'admin_ajax' ) );

            // Add the Pods Components capabilities
            add_filter( 'members_get_capabilities', array( $this, 'admin_capabilities' ) );
        }
    }

    /**
     * Add menu item
     *
     * @param string $parent The parent slug.
     *
     * @since 2.0.0
     *
     * @uses add_submenu_page
     */
    public function menu ( $parent ) {
        global $submenu;

        $custom_component_menus = array();

        foreach ( $this->components as $component => $component_data ) {
            if ( !isset( $this->settings[ 'components' ][ $component_data[ 'ID' ] ] ) || 0 == $this->settings[ 'components' ][ $component_data[ 'ID' ] ] )
                continue;

            if ( !empty( $component_data[ 'Hide' ] ) )
                continue;

            if ( true === (boolean) pods_var( 'DeveloperMode', $component_data, false ) && ( !defined( 'PODS_DEVELOPER' ) || !PODS_DEVELOPER ) )
                continue;

            if ( empty( $component_data[ 'MenuPage' ] ) && ( !isset( $component_data[ 'object' ] ) || !method_exists( $component_data[ 'object' ], 'admin' ) ) )
                continue;

            $component_data[ 'File' ] = realpath( $this->components_dir . $component_data[ 'File' ] );

            if ( !file_exists( $component_data[ 'File' ] ) ) {
                pods_message( 'Pods Component not found: ' . $component_data[ 'File' ] );

                pods_transient_clear( 'pods_components' );

                continue;
            }

            $capability = 'pods_component_' . str_replace( '-', '_', sanitize_title( str_replace( ' and ', ' ', strip_tags( $component_data[ 'Name' ] ) ) ) );

            if ( 0 < strlen( $component_data[ 'Capability' ] ) )
                $capability = $component_data[ 'Capability' ];

            if ( !is_super_admin() && !current_user_can( 'delete_users' ) && !current_user_can( 'pods' ) && !current_user_can( 'pods_components' ) && !current_user_can( $capability ) )
                continue;

            $menu_page = 'pods-component-' . $component_data[ 'ID' ];

            if ( !empty( $component_data[ 'MenuPage' ] ) )
                $custom_component_menus[ $menu_page ] = $component_data;

            $page = add_submenu_page(
                $parent,
                strip_tags( $component_data[ 'Name' ] ),
                '- ' . strip_tags( $component_data[ 'MenuName' ] ),
                'read',
                $menu_page,
                array( $this, 'admin_handler' )
            );

            if ( isset( $component_data[ 'object' ] ) && method_exists( $component_data[ 'object' ], 'admin_assets' ) )
                add_action( 'admin_print_styles-' . $page, array( $component_data[ 'object' ], 'admin_assets' ) );
        }

        if ( !empty( $custom_component_menus ) ) {
            foreach ( $custom_component_menus as $menu_page => $component_data ) {
                if ( isset( $submenu[ $parent ] ) ) {
                    foreach ( $submenu[ $parent ] as $sub => &$menu ) {
                        if ( $menu[ 2 ] == $menu_page ) {
                            $menu_page = $component_data[ 'MenuPage' ];

                            /*if ( !empty( $component_data[ 'MenuAddPage' ] ) ) {
                                if ( false !== strpos( $_SERVER[ 'REQUEST_URI' ], $component_data[ 'MenuAddPage' ] ) )
                                    $menu_page = $component_data[ 'MenuAddPage' ];
                            }*/

                            $menu[ 2 ] = $menu_page;

                            $page = current( explode( '?', $menu[ 2 ] ) );

                            if ( isset( $component_data[ 'object' ] ) && method_exists( $component_data[ 'object' ], 'admin_assets' ) )
                                add_action( 'admin_print_styles-' . $page, array( $component_data[ 'object' ], 'admin_assets' ) );

                            break;
                        }
                    }
                }
            }
        }
    }

    /**
     * Load activated components and init component
     *
     * @since 2.0.0
     */
    public function load () {
        foreach ( (array) $this->settings[ 'components' ] as $component => $options ) {
            if ( !isset( $this->components[ $component ] ) || 0 == $options )
                continue;

            if ( !empty( $this->components[ $component ][ 'PluginDependency' ] ) ) {
                $dependency = explode( '|', $this->components[ $component ][ 'PluginDependency' ] );

                if ( !pods_is_plugin_active( $dependency[ 1 ] ) )
                    continue;
            }

            if ( !empty( $this->components[ $component ][ 'ThemeDependency' ] ) ) {
                $dependency = explode( '|', $this->components[ $component ][ 'ThemeDependency' ] );

                if ( strtolower( $dependency[ 1 ] ) != strtolower( get_template() ) )
                    continue;
            }

            $component_data = $this->components[ $component ];

            $component_data[ 'File' ] = realpath( $this->components_dir . $component_data[ 'File' ] );

            if ( empty( $component_data[ 'File' ] ) ) {
                pods_transient_clear( 'pods_components' );

                continue;
            }

            if ( !file_exists( $component_data[ 'File' ] ) ) {
                pods_message( 'Pods Component not found: ' . $component_data[ 'File' ] );

                pods_transient_clear( 'pods_components' );

                continue;
            }

            include_once $component_data[ 'File' ];

            if ( !empty( $component_data[ 'Class' ] ) && class_exists( $component_data[ 'Class' ] ) && !isset( $this->components[ $component ][ 'object' ] ) ) {
                $this->components[ $component ][ 'object' ] = new $component_data[ 'Class' ];

                if ( method_exists( $this->components[ $component ][ 'object' ], 'options' ) ) {
                    $this->components[ $component ][ 'options' ] = $this->components[ $component ][ 'object' ]->options();

                    $this->options( $component, $this->components[ $component ][ 'options' ] );
                }

                if ( method_exists( $this->components[ $component ][ 'object' ], 'handler' ) )
                    $this->components[ $component ][ 'object' ]->handler( $this->settings[ 'components' ][ $component ] );
            }
        }
    }

    /**
     * Get list of components available
     *
     * @since 2.0.0
     */
    public function get_components () {
        $components = pods_transient_get( 'pods_components' );

        if ( PodsInit::$version != PODS_VERSION || !is_array( $components ) || empty( $components ) || ( is_admin() && isset( $_GET[ 'page' ] ) && 'pods-components' == $_GET[ 'page' ] && false !== pods_transient_get( 'pods_components_refresh' ) ) ) {
            $component_dir = @opendir( untrailingslashit( $this->components_dir ) );
            $component_files = array();

            if ( false !== $component_dir ) {
                while ( false !== ( $file = readdir( $component_dir ) ) ) {
                    if ( '.' == substr( $file, 0, 1 ) )
                        continue;
                    elseif ( is_dir( $this->components_dir . $file ) ) {
                        $component_subdir = @opendir( $this->components_dir . $file );

                        if ( $component_subdir ) {
                            while ( false !== ( $subfile = readdir( $component_subdir ) ) ) {
                                if ( '.' == substr( $subfile, 0, 1 ) )
                                    continue;
                                elseif ( '.php' == substr( $subfile, -4 ) )
                                    $component_files[] = str_replace( '\\', '/', $file . '/' . $subfile );
                            }

                            closedir( $component_subdir );
                        }
                    }
                    elseif ( '.php' == substr( $file, -4 ) )
                        $component_files[] = $file;
                }

                closedir( $component_dir );
            }

            $default_headers = array(
                'ID' => 'ID',
                'Name' => 'Name',
                'URI' => 'URI',
                'MenuName' => 'Menu Name',
                'MenuPage' => 'Menu Page',
                'MenuAddPage' => 'Menu Add Page',
                'Description' => 'Description',
                'Version' => 'Version',
                'Author' => 'Author',
                'AuthorURI' => 'Author URI',
                'Class' => 'Class',
                'Hide' => 'Hide',
                'PluginDependency' => 'Plugin Dependency',
                'ThemeDependency' => 'Theme Dependency',
                'DeveloperMode' => 'Developer Mode',
                'Capability' => 'Capability'
            );

            $components = array();

            foreach ( $component_files as $component_file ) {
                if ( !is_readable( $this->components_dir . $component_file ) )
                    continue;

                $component_data = get_file_data( $this->components_dir . $component_file, $default_headers, 'pods_component' );

                if ( empty( $component_data[ 'Name' ] ) || 'yes' == $component_data[ 'Hide' ] )
                    continue;

                if ( empty( $component_data[ 'MenuName' ] ) )
                    $component_data[ 'MenuName' ] = $component_data[ 'Name' ];

                if ( empty( $component_data[ 'Class' ] ) )
                    $component_data[ 'Class' ] = 'Pods_' . pods_clean_name( basename( $this->components_dir . $component_file, '.php' ), false );

                if ( empty( $component_data[ 'ID' ] ) )
                    $component_data[ 'ID' ] = sanitize_title( $component_data[ 'Name' ] );

                if ( 'on' == $component_data[ 'DeveloperMode' ] || 1 == $component_data[ 'DeveloperMode' ] )
                    $component_data[ 'DeveloperMode' ] = true;
                else
                    $component_data[ 'DeveloperMode' ] = false;

                $component_data[ 'File' ] = $component_file;

                $components[ $component_data[ 'ID' ] ] = $component_data;
            }

            ksort( $components );

            pods_transient_set( 'pods_components_refresh', 1, ( 60 * 60 * 12 ) );

            pods_transient_set( 'pods_components', $components );
        }

        if ( 1 == pods_var( 'pods_debug_components', 'get', 0 ) && is_user_logged_in() && ( is_super_admin() || current_user_can( 'delete_users' ) || current_user_can( 'pods' ) ) )
            pods_debug( $components );

        $this->components = $components;

        return $this->components;
    }

    /**
     * Set component options
     *
     * @param $component
     * @param $options
     *
     * @since 2.0.0
     */
    public function options ( $component, $options ) {
        if ( !isset( $this->settings[ 'components' ][ $component ] ) || !is_array( $this->settings[ 'components' ][ $component ] ) )
            $this->settings[ 'components' ][ $component ] = array();

        foreach ( $options as $option => $data ) {
            if ( !isset( $this->settings[ 'components' ][ $component ][ $option ] ) && isset( $data[ 'default' ] ) )
                $this->settings[ 'components' ][ $component ][ $option ] = $data[ 'default' ];
        }
    }

    /**
     * Call component specific admin functions
     *
     * @since 2.0.0
     */
    public function admin_handler () {
        $component = str_replace( 'pods-component-', '', $_GET[ 'page' ] );

        if ( isset( $this->components[ $component ] ) && isset( $this->components[ $component ][ 'object' ] ) && method_exists( $this->components[ $component ][ 'object' ], 'admin' ) )
            $this->components[ $component ][ 'object' ]->admin( $this->settings[ 'components' ][ $component ], $component );
    }

    /**
     * Toggle a component on or off
     *
     * @param string $component The component name to toggle
     *
     * @return bool
     *
     * @since 2.0.0
     */
    public function toggle ( $component ) {
        $toggle = null;

        if ( isset( $this->components[ $component ] ) ) {
            if ( 1 == pods_var( 'toggle', 'get' ) && ( !isset( $this->settings[ 'components' ][ $component ] ) || 0 == $this->settings[ 'components' ][ $component ] ) ) {
                $this->settings[ 'components' ][ $component ] = array();
                $toggle = true;
            }
            elseif ( 0 == pods_var( 'toggle', 'get' ) ) {
                $this->settings[ 'components' ][ $component ] = 0;
                $toggle = false;
            }
        }

        $settings = version_compare( PHP_VERSION, '5.4.0', '>=' ) ? json_encode( $this->settings, JSON_UNESCAPED_UNICODE ) : json_encode( $this->settings );

        update_option( 'pods_component_settings', $settings );

        return $toggle;
    }

    /**
     * Add pods specific capabilities.
     *
     * @param $capabilities List of extra capabilities to add
     *
     * @return array
     */
    public function admin_capabilities ( $capabilities ) {
        foreach ( $this->components as $component => $component_data ) {
            if ( !empty( $component_data[ 'Hide' ] ) )
                continue;

            if ( true === (boolean) pods_var( 'DeveloperMode', $component_data, false ) && ( !defined( 'PODS_DEVELOPER' ) || !PODS_DEVELOPER ) )
                continue;

            if ( empty( $component_data[ 'MenuPage' ] ) && ( !isset( $component_data[ 'object' ] ) || !method_exists( $component_data[ 'object' ], 'admin' ) ) )
                continue;

            $capability = 'pods_component_' . str_replace( '-', '_', sanitize_title( str_replace( ' and ', ' ', strip_tags( $component_data[ 'Name' ] ) ) ) );

            if ( 0 < strlen ( $component_data[ 'Capability' ] ) )
                $capability = $component_data[ 'Capability' ];

            if ( !in_array( $capability, $capabilities ) )
                $capabilities[] = $capability;
        }

        return $capabilities;
    }

    /**
     * Handle admin ajax
     *
     * @since 2.0.0
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

        $component = $params->component;
        $method = $params->method;

        if ( !isset( $component ) || !isset( $this->components[ $component ] ) || !isset( $this->settings[ 'components' ][ $component ] ) )
            pods_error( 'Invalid AJAX request', $this );

        if ( !isset( $params->_wpnonce ) || false === wp_verify_nonce( $params->_wpnonce, 'pods-component-' . $component . '-' . $method ) )
            pods_error( 'Unauthorized request', $this );

        if ( !isset( $this->components[ $component ][ 'object' ] ) || !method_exists( $this->components[ $component ][ 'object' ], 'ajax_' . $method ) )
            pods_error( 'API method does not exist', $this );

        // Cleaning up $params
        unset( $params->action );
        unset( $params->component );
        unset( $params->method );
        unset( $params->_wpnonce );

        $params = (object) apply_filters( 'pods_component_ajax_' . $component . '_' . $method, $params, $component, $method );

        $method = 'ajax_' . $method;

        // Dynamically call the component method
        $output = call_user_func( array( $this->components[ $component ][ 'object' ], $method ), $params );

        if ( !is_bool( $output ) )
            echo $output;

        die(); // KBAI!
    }
}

/**
 * The base component class, all components should extend this.
 *
 * @package Pods
 */
class PodsComponent {

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
    public function admin ( $options ) {
    // run code based on $options set
    }
     */
}
