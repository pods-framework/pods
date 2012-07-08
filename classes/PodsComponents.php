<?php
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
}


class PodsComponents {

    /**
     * Root of Components directory
     *
     * @var string
     *
     * @private
     * @since 2.0
     */
    private $components_dir = null;

    /**
     * Available components
     *
     * @var string
     *
     * @static
     * @since 2.0
     */
    static $components = array();

    /**
     * Components settings
     *
     * @var string
     *
     * @static
     * @since 2.0
     */
    static $settings = array( 'components' => array() );

    /**
     * Setup actions and get options
     *
     * @since 2.0
     */
    public function __construct ( $admins = null ) {
        self::$components_dir = apply_filters( 'pods_components_dir', PODS_DIR . 'components/' );

        $settings = get_option( 'pods_component_settings', '' );

        if ( !empty( $settings ) )
            self::$settings = (array) json_decode( $settings, true );

        if ( !isset( self::$settings[ 'components' ] ) )
            self::$settings[ 'components' ] = array();

        // Get components
        add_action( 'after_setup_theme', array( $this, 'get_components' ), 11 );

        // Load in components
        add_action( 'after_setup_theme', array( $this, 'load' ), 12 );

        if ( is_admin() ) {
            // Add menu
            add_action( 'admin_menu', array( $this, 'menu' ), 12 );
        }
    }

    /**
     * Add menu item
     *
     * @since 2.0
     */
    public function menu () {
        // @todo add menu items (if any)
    }

    /**
     * Load activated components and init component
     *
     * @since 2.0
     */
    public function load () {
        foreach ( (array) self::$settings[ 'components' ] as $component => $options ) {
            if ( ( !isset( self::$components[ $component ] ) || 'yes' == self::$components[ $component ][ 'Autoload' ] ) && 0 == $options )
                continue;
            elseif ( isset( self::$components[ $component ] ) && file_exists( self::$components_dir . $component ) ) {
                $component_data = self::$components[ $component ];

                include_once self::$components_dir . $component;

                if ( !empty( $component_data[ 'Class' ] ) && class_exists( $component_data[ 'Class' ] ) && !isset( self::$components[ $component ][ 'object' ] ) ) {
                    self::$components[ $component ][ 'object' ] = new $component_data[ 'Class' ];

                    if ( method_exists( self::$components[ $component ][ 'object' ], 'options' ) ) {
                        self::$components[ $component ][ 'options' ] = self::$components[ $component ][ 'object' ]->options();

                        self::options( $component, self::$components[ $component ][ 'options' ] );
                    }

                    if ( method_exists( self::$components[ $component ][ 'object' ], 'handler' ) )
                        self::$components[ $component ][ 'object' ]->handler( self::$settings[ 'components' ][ $component ] );
                }
            }
        }
    }

    /**
     * Get list of components available
     *
     * @since 2.0
     */
    public function get_components () {
        $components = get_transient( 'pods_components' );

        if ( 5 < strlen( $components ) && ( !isset( $_GET[ 'page' ] ) || 'pods-components' != $_GET[ 'page' ] || !isset( $_GET[ 'reload_components' ] ) ) )
            $components = json_decode( $components, true );
        else {
            $component_dir = @opendir( rtrim( self::$components_dir, '/' ) );
            $component_files = array();

            if ( false !== $component_dir ) {
                while (false !== ( $file = readdir( $component_dir ) )) {
                    if ( '.' == substr( $file, 0, 1 ) )
                        continue;
                    elseif ( is_dir( $component_dir . $file ) ) {
                        $component_subdir = @opendir( self::$components_dir . $file );

                        if ( $component_subdir ) {
                            while (false !== ( $subfile = readdir( $component_subdir ) )) {
                                if ( '.' == substr( $subfile, 0, 1 ) )
                                    continue;
                                elseif ( '.php' == substr( $subfile, -4 ) )
                                    $component_files[] = self::$components_dir . $file . '/' . $subfile;
                            }

                            closedir( $component_subdir );
                        }
                    }
                    elseif ( '.php' == substr( $file, -4 ) )
                        $component_files[] = self::$components_dir . $file;
                }

                closedir( $component_dir );
            }

            $default_headers = array(
                'ID' => 'ID',
                'Name' => 'Name',
                'MenuName' => 'Menu Name',
                'Description' => 'Description',
                'Version' => 'Version',
                'Class' => 'Class',
                'Autoload' => 'Autoload',
                'Hide' => 'Hide'
            );

            $components = array();
            foreach ( $component_files as $component_file ) {
                if ( !is_readable( $component_file ) )
                    continue;

                $component_data = get_file_data( $component_file, $default_headers, 'pods_component' );

                if ( empty( $component_data[ 'Name' ] ) || 'yes' == $component_data[ 'Hide' ] )
                    continue;
                if ( empty( $component_data[ 'MenuName' ] ) )
                    $component_data[ 'MenuName' ] = $component_data[ 'Name' ];
                elseif ( empty( $component_data[ 'ID' ] ) ) {
                    $find = array( self::$components_dir, '.php' );

                    $component_data[ 'ID' ] = sanitize_title( str_replace( $find, '', $component_file ) );
                }

                $components[ str_replace( self::$components_dir, '', $component_file ) ] = $component_data;
            }

            set_transient( 'pods_components', json_encode( $components ), ( 60 * 60 * 24 * 1 ) ); // seconds * minutes * hours * days
        }

        ksort( $components );

        self::$components = $components;

        return self::$components;
    }

    public function options ( $component, $options ) {

        if ( !isset( self::$settings[ 'components' ][ $component ] ) || !is_array( self::$settings[ 'components' ][ $component ] ) )
            self::$settings[ 'components' ][ $component ] = array();

        foreach ( $options as $option => $data ) {
            if ( !isset( self::$settings[ 'components' ][ $component ][ $option ] ) && isset( $data[ 'default' ] ) )
                self::$settings[ 'components' ][ $component ][ $option ] = $data[ 'default' ];
        }
    }

    public function components () {
        return self::$components;
    }
}