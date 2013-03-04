<?php
/**
 * @package Pods\Fields
 */
class PodsField_Pick extends PodsField {

    /**
     * Field Type Group
     *
     * @var string
     * @since 2.0
     */
    public static $group = 'Relationships / Media';

    /**
     * Field Type Identifier
     *
     * @var string
     * @since 2.0
     */
    public static $type = 'pick';

    /**
     * Field Type Label
     *
     * @var string
     * @since 2.0
     */
    public static $label = 'Relationship';

    /**
     * Available Related Objects
     *
     * @var array
     * @since 2.3.0
     */
    private static $related_objects = array();

    /**
     * Custom Related Objects
     *
     * @var array
     * @since 2.3.0
     */
    private static $custom_related_objects = array();

    /**
     * Setup related objects list
     *
     * @since 2.0
     */
    public function __construct () {

    }

    /**
     * Add admin_init actions
     *
     * @since 2.3
     */
    public function admin_init () {
        // AJAX for Relationship lookups
        add_action( 'wp_ajax_pods_relationship', array( $this, 'admin_ajax_relationship' ) );
        add_action( 'wp_ajax_nopriv_pods_relationship', array( $this, 'admin_ajax_relationship' ) );
    }

    /**
     * Add options and set defaults to
     *
     * @return array
     *
     * @since 2.0
     */
    public function options () {
        $options = array(
            'pick_format_type' => array(
                'label' => __( 'Selection Type', 'pods' ),
                'help' => __( 'help', 'pods' ),
                'default' => 'single',
                'type' => 'pick',
                'data' => array(
                    'single' => __( 'Single Select', 'pods' ),
                    'multi' => __( 'Multiple Select', 'pods' )
                ),
                'dependency' => true
            ),
            'pick_format_single' => array(
                'label' => __( 'Format', 'pods' ),
                'help' => __( 'help', 'pods' ),
                'depends-on' => array( 'pick_format_type' => 'single' ),
                'default' => 'dropdown',
                'type' => 'pick',
                'data' => apply_filters(
                    'pods_form_ui_field_pick_format_single_options',
                    array(
                        'dropdown' => __( 'Drop Down', 'pods' ),
                        'radio' => __( 'Radio Buttons', 'pods' ),
                        'autocomplete' => __( 'Autocomplete', 'pods' )
                    ) + ( ( pods_developer() && 1 == 0 ) ? array( 'flexible' => __( 'Flexible', 'pods' ) ) : array() ) // Disable for now
                ),
                'dependency' => true
            ),
            'pick_format_multi' => array(
                'label' => __( 'Format', 'pods' ),
                'help' => __( 'help', 'pods' ),
                'depends-on' => array( 'pick_format_type' => 'multi' ),
                'default' => 'checkbox',
                'type' => 'pick',
                'data' => apply_filters(
                    'pods_form_ui_field_pick_format_multi_options',
                    array(
                        'checkbox' => __( 'Checkboxes', 'pods' ),
                        'multiselect' => __( 'Multi Select', 'pods' ),
                        'autocomplete' => __( 'Autocomplete', 'pods' )
                    ) + ( ( pods_developer() && 1 == 0 ) ? array( 'flexible' => __( 'Flexible', 'pods' ) ) : array() )
                ),
                'dependency' => true
            ),
            'pick_limit' => array(
                'label' => __( 'Selection Limit', 'pods' ),
                'help' => __( 'help', 'pods' ),
                'depends-on' => array( 'pick_format_type' => 'multi' ),
                'default' => 0,
                'type' => 'number'
            ),
            'pick_table_id' => array(
                'label' => __( 'Table ID Column', 'pods' ),
                'help' => __( 'You must provide the ID column name for the table, this will be used to keep track of the relationship', 'pods' ),
                'depends-on' => array( 'pick_object' => 'table' ),
                'required' => 1,
                'default' => '',
                'type' => 'text'
            ),
            'pick_table_index' => array(
                'label' => __( 'Table Index Column', 'pods' ),
                'help' => __( 'You must provide the index column name for the table, this may optionally also be the ID column name', 'pods' ),
                'depends-on' => array( 'pick_object' => 'table' ),
                'required' => 1,
                'default' => '',
                'type' => 'text'
            ),
            'pick_display' => array(
                'label' => __( 'Display Field in Selection List', 'pods' ),
                'help' => __( 'Provide the name of a field on the related object to reference, example: {@post_title}', 'pods' ),
                'excludes-on' => array(
                    'pick_object' => array_merge(
                        array( 'site', 'network' ),
                        self::simple_objects()
                    )
                ),
                'default' => '',
                'type' => 'text'
            ),
            'pick_user_role' => array(
                'label' => __( 'Limit list to Role(s)', 'pods' ),
                'help' => __( 'help', 'pods' ),
                'depends-on' => array( 'pick_object' => 'user' ),
                'default' => '',
                'type' => 'pick',
                'pick_object' => 'role',
                'pick_format_type' => 'multi'
            ),/*
            'pick_user_site' => array(
                'label' => __( 'Limit list to Site(s)', 'pods' ),
                'help' => __( 'help', 'pods' ),
                'depends-on' => array( 'pick_object' => 'user' ),
                'default' => '',
                'type' => 'pick',
                'pick_object' => 'site',
                'pick_format_type' => 'multi'
            ),*/
            'pick_where' => array(
                'label' => __( 'Customized <em>WHERE</em>', 'pods' ),
                'help' => __( 'help', 'pods' ),
                'excludes-on' => array(
                    'pick_object' => array_merge(
                        array( 'site', 'network' ),
                        self::simple_objects()
                    )
                ),
                'default' => '',
                'type' => 'text'
            ),
            'pick_orderby' => array(
                'label' => __( 'Customized <em>ORDER BY</em>', 'pods' ),
                'help' => __( 'help', 'pods' ),
                'excludes-on' => array(
                    'pick_object' => array_merge(
                        array( 'site', 'network' ),
                        self::simple_objects()
                    )
                ),
                'default' => '',
                'type' => 'text'
            ),
            'pick_groupby' => array(
                'label' => __( 'Customized <em>GROUP BY</em>', 'pods' ),
                'help' => __( 'help', 'pods' ),
                'excludes-on' => array(
                    'pick_object' => array_merge(
                        array( 'site', 'network' ),
                        self::simple_objects()
                    )
                ),
                'default' => '',
                'type' => 'text'
            )
            /*,
            'pick_size' => array(
                'label' => __( 'Field Size', 'pods' ),
                'default' => 'medium',
                'type' => 'pick',
                'data' => array(
                    'small' => __( 'Small', 'pods' ),
                    'medium' => __( 'Medium', 'pods' ),
                    'large' => __( 'Large', 'pods' )
                )
            )*/
        );

        /*if ( !is_multisite() )
            unset( $options[ 'pick_user_site' ] );*/

        return $options;
    }

    /**
     * Register a related object
     *
     * @param string $name Object name
     * @param string $label Object label
     * @param array $options Object options
     *
     * @return array|boolean Object array or false if unsuccessful
     * @since 2.3.0
     */
    public function register_related_object ( $name, $label, $options = null ) {
        if ( empty( $name ) || empty( $label ) )
            return false;

        $related_object = array(
            'label' => $label,
            'group' => 'Custom Relationships',
            'simple' => true,
            'data' => array(),
            'data_callback' => null
        );

        $related_object = array_merge( $related_object, $options );

        self::$custom_related_objects[ $name ] = $related_object;

        return true;
    }

    /**
     * Setup related objects
     *
     * @param boolean $force Whether to force refresh of related objects
     *
     * @since 2.3.0
     */
    public function setup_related_objects ( $force = false ) {
        $related_objects = get_transient( 'pods_related_objects' );

        if ( !$force && !empty( $related_objects ) )
            self::$related_objects = $related_objects;
        else {
            // Custom
            self::$related_objects[ 'custom-simple' ] = array(
                'label' => __( 'Simple (custom defined list)', 'pods' ),
                'group' => __( 'Custom', 'pods' ),
                'simple' => true
            );

            // Pods
            // @todo Upgrade should convert to proper type selections (pods-pod_name >> post_type-pod_name
            $pod_options = array();

            // Advanced Content Types
            $_pods = PodsMeta::$advanced_content_types;

            foreach ( $_pods as $pod ) {
                $pod_options[ $pod[ 'name' ] ] = $pod[ 'label' ] . ' (' . $pod[ 'name' ] . ')';
            }

            // Settings
            $_pods = PodsMeta::$settings;

            foreach ( $_pods as $pod ) {
                $pod_options[ $pod[ 'name' ] ] = $pod[ 'label' ] . ' (' . $pod[ 'name' ] . ')';
            }

            asort( $pod_options );

            foreach ( $pod_options as $pod => $label ) {
                self::$related_objects[ 'pod-' . $pod ] = array(
                    'label' => $label,
                    'group' => __( 'Pods', 'pods' )
                );
            }

            // Post Types
            $post_types = get_post_types();
            asort( $post_types );

            $ignore = array( 'attachment', 'revision', 'nav_menu_item' );

            foreach ( $post_types as $post_type => $label ) {
                if ( in_array( $post_type, $ignore ) || empty( $post_type ) ) {
                    unset( $post_types[ $post_type ] );

                    continue;
                }
                elseif ( 0 === strpos( $post_type, '_pods_' ) && !in_array( $post_type, array( '_pods_page', '_pods_template' ) ) ) {
                    unset( $post_types[ $post_type ] );

                    continue;
                }

                $post_type = get_post_type_object( $post_type );

                self::$related_objects[ 'post_type-' . $post_type->name ] = array(
                    'label' => $post_type->label,
                    'group' => __( 'Post Types', 'pods' )
                );
            }

            // Taxonomies
            $taxonomies = get_taxonomies();
            asort( $taxonomies );

            $ignore = array( 'nav_menu', 'post_format' );

            foreach ( $taxonomies as $taxonomy => $label ) {
                if ( in_array( $taxonomy, $ignore ) || empty( $taxonomy ) )
                    continue;

                $taxonomy = get_taxonomy( $taxonomy );

                self::$related_objects[ 'taxonomy-' . $taxonomy->name ] = array(
                    'label' => $taxonomy->label,
                    'group' => __( 'Taxonomies', 'pods' )
                );
            }

            // Other WP Objects
            self::$related_objects[ 'user' ] = array(
                'label' => __( 'Users', 'pods' ),
                'group' => __( 'Other WP Objects', 'pods' )
            );

            self::$related_objects[ 'role' ] = array(
                'label' => __( 'User Roles', 'pods' ),
                'group' => __( 'Other WP Objects', 'pods' ),
                'simple' => true
            );

            self::$related_objects[ 'media' ] = array(
                'label' => __( 'Media', 'pods' ),
                'group' => __( 'Other WP Objects', 'pods' )
            );

            self::$related_objects[ 'comment' ] = array(
                'label' => __( 'Comments', 'pods' ),
                'group' => __( 'Other WP Objects', 'pods' )
            );

            self::$related_objects[ 'image-size' ] = array(
                'label' => __( 'Image Sizes', 'pods' ),
                'group' => __( 'Other WP Objects', 'pods' ),
                'simple' => true
            );

            self::$related_objects[ 'nav_menu' ] = array(
                'label' => __( 'Navigation Menus', 'pods' ),
                'group' => __( 'Other WP Objects', 'pods' )
            );

            self::$related_objects[ 'post_format' ] = array(
                'label' => __( 'Post Formats', 'pods' ),
                'group' => __( 'Other WP Objects', 'pods' )
            );

            self::$related_objects[ 'post-status' ] = array(
                'label' => __( 'Post Status', 'pods' ),
                'group' => __( 'Other WP Objects', 'pods' ),
                'simple' => true
            );

            self::$related_objects[ 'sidebar' ] = array(
                'label' => __( 'Sidebars', 'pods' ),
                'group' => __( 'Other WP Objects', 'pods' ),
                'simple' => true
            );

            // Advanced Objects
            self::$related_objects[ 'table' ] = array(
                'label' => __( 'Database Table', 'pods' ),
                'group' => __( 'Advanced Objects', 'pods' )
            );

            self::$related_objects[ 'site' ] = array(
                'label' => __( 'Multisite Sites', 'pods' ),
                'group' => __( 'Advanced Objects', 'pods' )
            );

            self::$related_objects[ 'network' ] = array(
                'label' => __( 'Multisite Networks', 'pods' ),
                'group' => __( 'Advanced Objects', 'pods' )
            );

            self::$related_objects[ 'theme' ] = array(
                'label' => __( 'Themes', 'pods' ),
                'group' => __( 'Advanced Objects', 'pods' ),
                'simple' => true
            );

            self::$related_objects[ 'page-template' ] = array(
                'label' => __( 'Page Templates', 'pods' ),
                'group' => __( 'Advanced Objects', 'pods' ),
                'simple' => true
            );

            self::$related_objects[ 'post-types' ] = array(
                'label' => __( 'Post Types', 'pods' ),
                'group' => __( 'Advanced Objects', 'pods' ),
                'simple' => true
            );

            self::$related_objects[ 'taxonomies' ] = array(
                'label' => __( 'Taxonomies', 'pods' ),
                'group' => __( 'Advanced Objects', 'pods' ),
                'simple' => true
            );

            if ( did_action( 'init' ) )
                set_transient( 'pods_related_objects', self::$related_objects );
        }

        foreach ( self::$custom_related_objects as $object => $related_object ) {
            self::$related_objects[ $object ] = $related_object;
        }
    }

    /**
     * Return available related objects
     *
     * @param boolean $force Whether to force refresh of related objects
     *
     * @return array Field selection array
     * @since 2.3.0
     */
    public function related_objects ( $force = false ) {
        $this->setup_related_objects( $force );

        $related_objects = array();

        foreach ( self::$related_objects as $related_object_name => $related_object ) {
            if ( !isset( $related_objects[ $related_object[ 'group' ] ] ) )
                $related_objects[ $related_object[ 'group' ] ] = array();

            $related_objects[ $related_object[ 'group' ] ][ $related_object_name ] = $related_object[ 'label' ];
        }

        return (array) apply_filters( 'pods_form_ui_field_pick_related_objects', $related_objects );
    }

    /**
     * Return available simple object names
     *
     * @return array Simple object names
     * @since 2.3.0
     */
    public function simple_objects () {
        $this->setup_related_objects();

        $simple_objects = array();

        foreach ( self::$related_objects as $object => $related_object ) {
            if ( !isset( $related_object[ 'simple' ] ) || !$related_object[ 'simple' ] )
                continue;

            $simple_objects[] = $object;
        }

        return (array) apply_filters( 'pods_form_ui_field_pick_simple_objects', $simple_objects );
    }

    /**
     * Define the current field's schema for DB table storage
     *
     * @param array $options
     *
     * @return array
     * @since 2.0
     */
    public function schema ( $options = null ) {
        $schema = false;

        if ( in_array( pods_var( 'pick_object', $options ), array( 'custom-simple', 'role', 'post-types', 'taxonomies' ) ) )
            $schema = 'LONGTEXT';

        return $schema;
    }

    /**
     * Change the way the value of the field is displayed with Pods::get
     *
     * @param mixed $value
     * @param string $name
     * @param array $options
     * @param array $fields
     * @param array $pod
     * @param int $id
     *
     * @since 2.0
     */
    public function display ( $value = null, $name = null, $options = null, $pod = null, $id = null ) {
        $fields = null;

        if ( is_object( $pod ) && isset( $pod->fields ) )
            $fields = $pod->fields;
        elseif ( is_array( $pod ) && isset( $pod[ 'fields' ] ) )
            $fields = $pod[ 'fields' ];

        return pods_serial_comma( $value, $name, $fields );
    }

    /**
     * Customize output of the form field
     *
     * @param string $name
     * @param mixed $value
     * @param array $options
     * @param array $pod
     * @param int $id
     *
     * @since 2.0
     */
    public function input ( $name, $value = null, $options = null, $pod = null, $id = null ) {
        global $wpdb;

        $options = (array) $options;
        $form_field_type = PodsForm::$field_type;

        $options[ 'grouped' ] = 1;

        $options[ 'table_info' ] = array();

        $custom = pods_var_raw( 'pick_custom', $options, false );

        $custom = apply_filters( 'pods_form_ui_field_pick_custom_values', $custom, $name, $value, $options, $pod, $id );

        $ajax = false;

        if ( ( 'custom-simple' != pods_var( 'pick_object', $options ) || empty( $custom ) ) && '' != pods_var( 'pick_object', $options, '', null, true ) ) {
            $autocomplete = false;

            if ( 'single' == pods_var( 'pick_format_type', $options, 'single' ) && 'autocomplete' == pods_var( 'pick_format_single', $options, 'dropdown' ) )
                $autocomplete = true;
            elseif ( 'multi' == pods_var( 'pick_format_type', $options, 'single' ) && 'autocomplete' == pods_var( 'pick_format_multi', $options, 'checkbox' ) )
                $autocomplete = true;

            $params[ 'limit' ] = -1;

            if ( $autocomplete )
                $params[ 'limit' ] = apply_filters( 'pods_form_ui_field_pick_autocomplete_limit', 30, $name, $value, $options, $pod, $id );

            $ajax = true;
        }

        if ( 'single' == pods_var( 'pick_format_type', $options, 'single' ) ) {
            if ( 'dropdown' == pods_var( 'pick_format_single', $options, 'dropdown' ) )
                $field_type = 'select';
            elseif ( 'radio' == pods_var( 'pick_format_single', $options, 'dropdown' ) )
                $field_type = 'radio';
            elseif ( 'autocomplete' == pods_var( 'pick_format_single', $options, 'dropdown' ) )
                $field_type = 'select2';
            else {
                // Support custom integration
                do_action( 'pods_form_ui_field_pick_input_' . pods_var( 'pick_format_type', $options, 'single' ) . '_' . pods_var( 'pick_format_single', $options, 'dropdown' ), $name, $value, $options, $pod, $id );
                do_action( 'pods_form_ui_field_pick_input', pods_var( 'pick_format_type', $options, 'single' ), $name, $value, $options, $pod, $id );
                return;
            }
        }
        elseif ( 'multi' == pods_var( 'pick_format_type', $options, 'single' ) ) {
            if ( 'checkbox' == pods_var( 'pick_format_multi', $options, 'checkbox' ) )
                $field_type = 'checkbox';
            elseif ( 'multiselect' == pods_var( 'pick_format_multi', $options, 'checkbox' ) )
                $field_type = 'select';
            elseif ( 'autocomplete' == pods_var( 'pick_format_multi', $options, 'checkbox' ) )
                $field_type = 'select2';
            else {
                // Support custom integration
                do_action( 'pods_form_ui_field_pick_input_' . pods_var( 'pick_format_type', $options, 'single' ) . '_' . pods_var( 'pick_format_multi', $options, 'checkbox' ), $name, $value, $options, $pod, $id );
                do_action( 'pods_form_ui_field_pick_input', pods_var( 'pick_format_type', $options, 'single' ), $name, $value, $options, $pod, $id );
                return;
            }
        }
        else {
            // Support custom integration
            do_action( 'pods_form_ui_field_pick_input', pods_var( 'pick_format_type', $options, 'single' ), $name, $value, $options, $pod, $id );
            return;
        }

        pods_view( PODS_DIR . 'ui/fields/' . $field_type . '.php', compact( array_keys( get_defined_vars() ) ) );
    }

    /**
     * Customize the Pods UI manage table column output
     *
     * @param int $id
     * @param mixed $value
     * @param string $name
     * @param array $options
     * @param array $fields
     * @param array $pod
     *
     * @since 2.0
     */
    public function ui( $id, $value, $name = null, $options = null, $fields = null, $pod = null ) {
        $value = $this->simple_value( $name, $value, $options, $pod, $id );

        return $this->display( $value, $name, $options, $pod, $id );
    }

    /**
     * Get the data from the field
     *
     * @param string $name The name of the field
     * @param string|array $value The value of the field
     * @param array $options Field options
     * @param array $pod Pod data
     * @param int $id Item ID
     *
     * @return array Array of possible field data
     *
     * @since 2.0
     */
    public function data ( $name, $value = null, $options = null, $pod = null, $id = null, $in_form = true ) {
        if ( isset( $options[ 'options' ] ) ) {
            $options = array_merge( $options[ 'options' ], $options );

            unset( $options[ 'options' ] );
        }

        $data = pods_var_raw( 'data', $options, null, null, true );

        $object_params = array(
            'name' => $name, // The name of the field
            'value' => $value, // The value of the field
            'options' => $options, // Field options
            'pod' => $pod, // Pod data
            'id' => $id, // Item ID
            'context' => 'data', // Data context
        );

        if ( null !== $data )
            $data = (array) $data;
        else
            $data = $this->get_object_data( $object_params );

        if ( 'single' == pods_var( 'pick_format_type', $options, 'single' ) && 'dropdown' == pods_var( 'pick_format_single', $options, 'dropdown' ) )
            $data = array_merge( array( '' => pods_var_raw( 'pick_select_text', $options, __( '-- Select One --', 'pods' ), null, true ) ), $data );

        $data = apply_filters( 'pods_field_pick_data', $data, $name, $value, $options, $pod, $id );

        return $data;
    }

    /**
     * Convert a simple value to the correct value
     *
     * @param string $name The name of the field
     * @param string|array $value The value of the field
     * @param array $options Field options
     * @param array $pod Pod data
     * @param int $id Item ID
     * @param boolean $raw Whether to return the raw list of keys (true) or convert to key=>value (false)
     *
     * @return mixed Corrected value
     */
    public function simple_value ( $name, $value = null, $options = null, $pod = null, $id = null, $raw = false ) {
        if ( in_array( pods_var( 'pick_object', $options ), self::simple_objects() ) ) {
            if ( isset( $options[ 'options' ] ) ) {
                $options = array_merge( $options[ 'options' ], $options );

                unset( $options[ 'options' ] );
            }

            if ( !is_array( $value ) && !empty( $value ) ) {
                $simple = @json_decode( $value, true );

                if ( is_array( $simple ) )
                    $value = $simple;
            }

            $data = pods_var_raw( 'data', $options, null, null, true );

            $object_params = array(
                'name' => $name, // The name of the field
                'value' => $value, // The value of the field
                'options' => $options, // Field options
                'pod' => $pod, // Pod data
                'id' => $id, // Item ID
                'context' => 'simple_value', // Data context
            );

            if ( null !== $data )
                $data = (array) $data;
            else
                $data = $this->get_object_data( $object_params );

            $key = 0;

            if ( is_array( $value ) ) {
                if ( !empty( $data ) ) {
                    $val = array();

                    foreach ( $value as $k => $v ) {
                        if ( isset( $data[ $v ] ) ) {
                            if ( false === $raw ) {
                                $k = $v;
                                $v = $data[ $v ];
                            }

                            $val[ $k ] = $v;
                        }
                    }

                    $value = $val;
                }
            }
            elseif ( isset( $data[ $value ] ) && false === $raw ) {
                $key = $value;
                $value = $data[ $value ];
            }

            $single_multi = pods_var( 'pick_format_type', $options, 'single' );

            if ( 'multi' == $single_multi )
                $limit = (int) pods_var( 'pick_limit', $options, 0 );
            else
                $limit = 1;

            if ( is_array( $value ) && 0 < $limit ) {
                if ( 1 == $limit )
                    $value = current( $value );
                else
                    $value = array_slice( $value, 0, $limit, true );
            }
            elseif ( !is_array( $value ) && null !== $value && 0 < strlen( $value ) ) {
                if ( 1 != $limit || ( true === $raw && 'multi' == $single_multi ) ) {
                    $value = array(
                        $key => $value
                    );
                }
            }
        }

        return $value;
    }

    /**
     * Get the label from a pick value
     *
     * @param string $name The name of the field
     * @param string|array $value The value of the field
     * @param array $options Field options
     * @param array $pod Pod data
     * @param int $id Item ID
     *
     * @return string
     *
     * @since 2.2
     */
    public function value_to_label ( $name, $value = null, $options = null, $pod = null, $id = null ) {
        if ( isset( $options[ 'options' ] ) ) {
            $options = array_merge( $options[ 'options' ], $options );

            unset( $options[ 'options' ] );
        }

        $data = pods_var_raw( 'data', $options, null, null, true );

        $object_params = array(
            'name' => $name, // The name of the field
            'value' => $value, // The value of the field
            'options' => $options, // Field options
            'pod' => $pod, // Pod data
            'id' => $id, // Item ID
            'context' => 'value_to_label', // Data context
        );

        if ( null !== $data )
            $data = (array) $data;
        else
            $data = $this->get_object_data( $object_params );

        $labels = array();

        foreach ( $data as $v => $l ) {
            if ( !in_array( $l, $labels ) && ( $value == $v || ( is_array( $value ) && in_array( $v, $value ) ) ) )
                $labels[] = $l;
        }

        $labels = apply_filters( 'pods_field_pick_value_to_label', $labels, $name, $value, $options, $pod, $id );

        $labels = pods_serial_comma( $labels );

        return $labels;
    }

    /**
     * Get data from relationship objects
     *
     * @param array $object_params Object data parameters
     *
     * @return array|bool Object data
     */
    private function get_object_data ( $object_params = null ) {
        global $wpdb, $polylang;

        $object_params = array_merge(
            array(
                'name' => '', // The name of the field
                'value' => '', // The value of the field
                'options' => array(), // Field options
                'pod' => '', // Pod data
                'id' => '', // Item ID
                'context' => '', // Data context
                'data_params' => array(
                    'query' => ''
                )
            ),
            $object_params
        );

        $name = $object_params[ 'name' ];
        $value = $object_params[ 'value' ];
        $options = $object_params[ 'options' ] = (array) $object_params[ 'options' ];
        $pod = $object_params[ 'pod' ];
        $id = $object_params[ 'id' ];
        $context = $object_params[ 'context' ];
        $data_params = $object_params[ 'data_params' ] = (array) $object_params[ 'data_params' ];

        if ( isset( $options[ 'options' ] ) ) {
            $options = array_merge( $options[ 'options' ], $options );

            unset( $options[ 'options' ] );
        }

        $data = apply_filters( 'pods_field_pick_object_data', null, $object_params );

        if ( null === $data ) {
            $data = null;

            if ( 'post-status' == $options[ 'pick_object' ] ) {
                $post_stati = get_post_stati( array(), 'objects' );

                foreach ( $post_stati as $post_status ) {
                    $data[ $post_status->name ] = $post_status->label;
                }
            }
            elseif ( 'role' == $options[ 'pick_object' ] ) {
                global $wp_roles;

                foreach ( $wp_roles->role_objects as $key => $role ) {
                    $data[ $key ] = $wp_roles->role_names[ $key ];
                }
            }
            elseif ( 'sidebar' == $options[ 'pick_object' ] ) {
                global $wp_registered_sidebars;

                if ( !empty( $wp_registered_sidebars ) ) {
                    foreach ( $wp_registered_sidebars as $sidebar ) {
                        $data[ $sidebar[ 'id' ] ] = $sidebar[ 'name' ];
                    }
                }
            }
            elseif ( 'image-size' == $options[ 'pick_object' ] ) {
                $image_sizes = get_intermediate_image_sizes();

                foreach ( $image_sizes as $image_size ) {
                    $data[ $image_size ] = ucwords( str_replace( '-', ' ', $image_size ) );
                }
            }
            elseif ( 'theme' == $options[ 'pick_object' ] ) {
                $themes = wp_get_themes( array( 'allowed' => true ) );

                foreach ( $themes as $theme ) {
                    $data[ $theme->Template ] = $theme->Name;
                }
            }
            elseif ( 'page-template' == $options[ 'pick_object' ] ) {
                if ( !function_exists( 'get_page_templates' ) )
                    include_once ABSPATH . 'wp-admin/includes/theme.php';

                $page_templates = apply_filters( 'pods_page_templates', get_page_templates() );

                if ( !in_array( 'page.php', $page_templates ) && locate_template( array( 'page.php', false ) ) )
                    $page_templates[ 'Page (WP Default)' ] = 'page.php';

                if ( !in_array( 'index.php', $page_templates ) && locate_template( array( 'index.php', false ) ) )
                    $page_templates[ 'Index (WP Fallback)' ] = 'index.php';

                ksort( $page_templates );

                $page_templates = array_flip( $page_templates );

                foreach ( $page_templates as $page_template_file => $page_template ) {
                    $data[ $page_template_file ] = $page_template;
                }
            }
            elseif ( 'post-types' == $options[ 'pick_object' ] ) {
                $post_types = get_post_types( array(), 'objects' );

                $ignore = array( 'revision', 'nav_menu_item' );

                foreach ( $post_types as $post_type ) {
                    if ( in_array( $post_type->name, $ignore ) || 0 === strpos( $post_type->name, '_pods_' ) )
                        continue;

                    $data[ $post_type->name ] = $post_type->label;
                }
            }
            elseif ( 'taxonomies' == $options[ 'pick_object' ] ) {
                $taxonomies = get_taxonomies( array(), 'objects' );

                $ignore = array( 'nav_menu', 'post_format' );

                foreach ( $taxonomies as $taxonomy ) {
                    if ( in_array( $taxonomy->name, $ignore ) )
                        continue;

                    $data[ $taxonomy->name ] = $taxonomy->label;
                }
            }
            elseif ( isset( self::$related_objects[ $options[ 'pick_object' ] ] ) ) {
                if ( isset( self::$related_objects[ $options[ 'pick_object' ] ][ 'data' ] ) && !empty( self::$related_objects[ $options[ 'pick_object' ] ][ 'data' ] ) )
                    $data = self::$related_objects[ $options[ 'pick_object' ] ][ 'data' ];
                elseif ( isset( self::$related_objects[ $options[ 'pick_object' ] ][ 'data_callback' ] ) && is_callable( self::$related_objects[ $options[ 'pick_object' ] ][ 'data_callback' ] ) ) {
                    $data = call_user_func_array(
                        self::$related_objects[ $options[ 'pick_object' ] ][ 'data_callback' ],
                        array( $name, $value, $options, $pod, $id )
                    );
                }
            }
            elseif ( 'custom-simple' == $options[ 'pick_object' ] ) {
                $custom = trim( pods_var_raw( 'pick_custom', $options, '' ) );

                $custom = apply_filters( 'pods_form_ui_field_pick_custom_values', $custom, $name, $value, $options, $pod, $id );

                if ( !empty( $custom ) ) {
                    if ( !is_array( $custom ) ) {
                        $data = array();

                        $custom = explode( "\n", $custom );

                        foreach ( $custom as $custom_value ) {
                            $custom_label = explode( '|', $custom_value );

                            if ( empty( $custom_label ) )
                                continue;

                            if ( 1 == count( $custom_label ) )
                                $custom_label = $custom_value;
                            else {
                                $custom_value = $custom_label[ 0 ];
                                $custom_label = $custom_label[ 1 ];
                            }

                            $data[ $custom_value ] = $custom_label;
                        }
                    }
                    else
                        $data = $custom;
                }
            }
            elseif ( 'simple_value' != $context ) {
                $pick_val = pods_var( 'pick_val', $options );

                if ( 'table' == pods_var( 'pick_object', $options ) )
                    $pick_val = pods_var( 'pick_table', $options, $pick_val, null, true );

                if ( '__current__' == $pick_val ) {
                    if ( is_object( $pod ) )
                        $pick_val = $pod->pod;
                    elseif ( is_array( $pod ) )
                        $pick_val = $pod[ 'name' ];
                    elseif ( 0 < strlen( $pod ) )
                        $pick_val = $pod;
                }

                $options[ 'table_info' ] = pods_api()->get_table_info( pods_var( 'pick_object', $options ), $pick_val, null, null, $options );

                $search_data = pods_data();
                $search_data->table( $options[ 'table_info' ] );

                if ( isset( $options[ 'table_info' ][ 'pod' ] ) && !empty( $options[ 'table_info' ][ 'pod' ] ) && isset( $options[ 'table_info' ][ 'pod' ][ 'name' ] ) ) {
                    $search_data->pod = $options[ 'table_info' ][ 'pod' ][ 'name' ];
                    $search_data->fields = $options[ 'table_info' ][ 'pod' ][ 'fields' ];
                }

                $params = array(
                    'select' => "`t`.`{$search_data->field_id}`, `t`.`{$search_data->field_index}`",
                    'table' => $search_data->table,
                    'where' => pods_var_raw( 'pick_where', $options, (array) $options[ 'table_info' ][ 'where_default' ], null, true ),
                    'orderby' => pods_var_raw( 'pick_orderby', $options, null, null, true ),
                    'groupby' => pods_var_raw( 'pick_groupby', $options, null, null, true )
                );

                if ( in_array( $options[ 'pick_object' ], array( 'site', 'network' ) ) )
                    $params[ 'select' ] .= ', `t`.`path`';

                if ( !empty( $params[ 'where' ] ) && (array) $options[ 'table_info' ][ 'where_default' ] != $params[ 'where' ] )
                    $params[ 'where' ] = pods_evaluate_tags( $params[ 'where' ], true );

                if ( empty( $params[ 'where' ] ) )
                    $params[ 'where' ] = array();
                elseif ( !is_array( $params[ 'where' ] ) )
                    $params[ 'where' ] = (array) $params[ 'where' ];

                if ( 'value_to_label' == $context )
                    $params[ 'where' ][] = "`t`.`{$search_data->field_id}` = " . number_format( $value, 0, '', '' );

                /* not needed yet
                if ( !empty( $params[ 'orderby' ] ) )
                    $params[ 'orderby' ] = pods_evaluate_tags( $params[ 'orderby' ], true );

                if ( !empty( $params[ 'groupby' ] ) )
                    $params[ 'groupby' ] = pods_evaluate_tags( $params[ 'groupby' ], true );*/

                $display = trim( pods_var( 'pick_display', $options ), ' {@}' );

                if ( 0 < strlen( $display ) ) {
                    if ( isset( $options[ 'table_info' ][ 'pod' ] ) && !empty( $options[ 'table_info' ][ 'pod' ] ) ) {
                        if ( isset( $options[ 'table_info' ][ 'pod' ][ 'object_fields' ] ) && isset( $options[ 'table_info' ][ 'pod' ][ 'object_fields' ][ $display ] ) ) {
                            $search_data->field_index = $display;

                            $params[ 'select' ] = "`t`.`{$search_data->field_id}`, `t`.`{$search_data->field_index}`";
                        }
                        elseif ( isset( $options[ 'table_info' ][ 'pod' ][ 'fields' ][ $display ] ) ) {
                            $search_data->field_index = $display;

                            if ( 'table' == $options[ 'table_info' ][ 'pod' ][ 'storage' ] && !in_array( $options[ 'table_info' ][ 'pod' ][ 'type' ], array( 'pod', 'table' ) ) )
                                $params[ 'select' ] = "`t`.`{$search_data->field_id}`, `d`.`{$search_data->field_index}`";
                            else
                                $params[ 'select' ] = "`t`.`{$search_data->field_id}`, `t`.`{$search_data->field_index}`";
                        }
                    }
                    elseif ( isset( $options[ 'table_info' ][ 'object_fields' ] ) && isset( $options[ 'table_info' ][ 'object_fields' ][ $display ] ) ) {
                        $search_data->field_index = $display;

                        $params[ 'select' ] = "`t`.`{$search_data->field_id}`, `t`.`{$search_data->field_index}`";
                    }
                }

                $autocomplete = false;

                if ( 'single' == pods_var( 'pick_format_type', $options, 'single' ) && 'autocomplete' == pods_var( 'pick_format_single', $options, 'dropdown' ) )
                    $autocomplete = true;
                elseif ( 'multi' == pods_var( 'pick_format_type', $options, 'single' ) && 'autocomplete' == pods_var( 'pick_format_multi', $options, 'checkbox' ) )
                    $autocomplete = true;

                $hierarchy = false;

                if ( 'data' == $context && !$autocomplete ) {
                    if ( 'single' == pods_var( 'pick_format_type', $options, 'single' ) && in_array( pods_var( 'pick_format_single', $options, 'dropdown' ), array( 'dropdown', 'radio' ) ) )
                        $hierarchy = true;
                    elseif ( 'multi' == pods_var( 'pick_format_type', $options, 'single' ) && in_array( pods_var( 'pick_format_multi', $options, 'checkbox' ), array( 'multiselect', 'checkbox' ) ) )
                        $hierarchy = true;
                }

                if ( $hierarchy && $options[ 'table_info' ][ 'object_hierarchical' ] && !empty( $options[ 'table_info' ][ 'field_parent' ] ) )
                    $params[ 'select' ] .= ', ' . $options[ 'table_info' ][ 'field_parent_select' ];

                if ( $autocomplete ) {
                    $params[ 'limit' ] = apply_filters( 'pods_form_ui_field_pick_autocomplete_limit', 30, $name, $value, $options, $pod, $id );

                    if ( 'admin_ajax_relationship' == $context ) {
                        $lookup_where = array(
                            "`t`.`{$search_data->field_index}` LIKE '%" . like_escape( $data_params[ 'query' ] ) . "%'"
                        );

                        $extra = '';

                        // @todo Hook into WPML for each table
                        if ( $wpdb->users == $search_data->table ) {
                            $lookup_where[ ] = "`t`.`display_name` LIKE '%" . like_escape( $data_params[ 'query' ] ) . "%'";
                            $lookup_where[ ] = "`t`.`user_login` LIKE '%" . like_escape( $data_params[ 'query' ] ) . "%'";
                            $lookup_where[ ] = "`t`.`user_email` LIKE '%" . like_escape( $data_params[ 'query' ] ) . "%'";
                        }
                        elseif ( $wpdb->posts == $search_data->table ) {
                            $lookup_where[ ] = "`t`.`post_name` LIKE '%" . like_escape( $data_params[ 'query' ] ) . "%'";
                            $lookup_where[ ] = "`t`.`post_content` LIKE '%" . like_escape( $data_params[ 'query' ] ) . "%'";
                            $lookup_where[ ] = "`t`.`post_excerpt` LIKE '%" . like_escape( $data_params[ 'query' ] ) . "%'";
                            $extra = ', `t`.`post_type`';
                        }
                        elseif ( $wpdb->terms == $search_data->table ) {
                            $lookup_where[ ] = "`t`.`slug` LIKE '%" . like_escape( $data_params[ 'query' ] ) . "%'";
                            $extra = ', `tt`.`taxonomy`';
                        }
                        elseif ( $wpdb->comments == $search_data->table ) {
                            $lookup_where[ ] = "`t`.`comment_content` LIKE '%" . like_escape( $data_params[ 'query' ] ) . "%'";
                            $lookup_where[ ] = "`t`.`comment_author` LIKE '%" . like_escape( $data_params[ 'query' ] ) . "%'";
                            $lookup_where[ ] = "`t`.`comment_author_email` LIKE '%" . like_escape( $data_params[ 'query' ] ) . "%'";
                        }

                        if ( !empty( $lookup_where ) )
                            $data_params[ 'where' ][ ] = ' ( ' . implode( ' OR ', $lookup_where ) . ' ) ';

                        $orderby = array();
                        $orderby[ ] = "(`t`.`{$search_data->field_index}` LIKE '%" . like_escape( $data_params[ 'query' ] ) . "%' ) DESC";

                        $pick_orderby = pods_var_raw( 'pick_orderby', $field[ 'options' ], null, null, true );

                        if ( 0 < strlen( $pick_orderby ) )
                            $orderby[ ] = $pick_orderby;

                        $orderby[ ] = "`t`.`{$search_data->field_index}`";
                        $orderby[ ] = "`t`.`{$search_data->field_id}`";

                        $data_params[ 'select' ] .= $extra;
                        $data_params[ 'orderby' ] = $orderby;
                    }
                }

                if ( 'user' == pods_var( 'pick_object', $options ) ) {
                    $roles = pods_var( 'pick_user_role', $options );

                    if ( !empty( $roles ) ) {
                        $where = array();

                        foreach ( (array) $roles as $role ) {
                            if ( empty( $role ) )
                                continue;

                            $where[] = 'wp_' . ( is_multisite() ? get_current_blog_id() . '_' : '' ) . 'capabilities.meta_value LIKE "%\"' . $role . '\"%"';
                        }

                        if ( !empty( $where ) )
                            $params[ 'where' ][] = '( ' . implode( ' OR ', $where ) . ' )';
                    }
                }

                $results = $search_data->select( $params );

                if ( $hierarchy && !empty( $results ) && $options[ 'table_info' ][ 'object_hierarchical' ] && !empty( $options[ 'table_info' ][ 'field_parent' ] ) ) {
                    $args = array(
                        'id' => $options[ 'table_info' ][ 'field_id' ],
                        'index' => $options[ 'table_info' ][ 'field_index' ],
                        'parent' => $options[ 'table_info' ][ 'field_parent' ],
                    );

                    $results = pods_hierarchical_select( $results, $args );
                }

                if ( !empty( $results ) && ( !$autocomplete || $search_data->total_found() <= $params[ 'limit' ] ) ) {
                    $display_filter = pods_var( 'display_filter', pods_var_raw( 'options', pods_var_raw( $search_data->field_index, $search_data->pod_data[ 'object_fields' ] ) ) );

                    foreach ( $results as $result ) {
                        $result = get_object_vars( $result );

                        $result[ $search_data->field_index ] = trim( $result[ $search_data->field_index ] );

                        if ( 0 < strlen( $display_filter ) )
                            $value = apply_filters( $display_filter, $value );

                        if ( in_array( $options[ 'pick_object' ], array( 'site', 'network' ) ) )
                            $result[ $search_data->field_index ] = $result[ $search_data->field_index ] . $result[ 'path' ];
                        elseif ( strlen( $result[ $search_data->field_index ] ) < 1 )
                            $result[ $search_data->field_index ] = '(No Title)';

                        $data[ $result[ $search_data->field_id ] ] = $result[ $search_data->field_index ];
                    }
                }
                elseif ( !empty( $value ) && $autocomplete && $params[ 'limit' ] < $search_data->total_found() ) {
                    $ids = $value;

                    if ( is_array( $ids ) )
                        $ids = implode( ', ', $ids );

                    if ( is_array( $params[ 'where' ] ) )
                        $params[ 'where' ] = implode( ' AND ', $params[ 'where' ] );
                    if ( !empty( $params[ 'where' ] ) )
                        $params[ 'where' ] .= ' AND ';

                    $params[ 'where' ] .= "`t`.`{$search_data->field_id}` IN ( " . $ids . " )";

                    $results = $search_data->select( $params );

                    if ( !empty( $results ) ) {
                        foreach ( $results as $result ) {
                            $result = get_object_vars( $result );

                            $result[ $search_data->field_index ] = trim( $result[ $search_data->field_index ] );

                            $object = '';

                            if ( $wpdb->posts == $search_data->table )
                                $object = $result[ 'post_type' ];
                            elseif ( $wpdb->terms == $search_data->table )
                                $object = $result[ 'taxonomy' ];

                            // WPML integration for Post Types and Taxonomies
                            if ( in_array( $search_data->table, array( $wpdb->posts, $wpdb->terms ) ) && function_exists( 'icl_object_id' ) ) {
                                $id = icl_object_id( $result[ $search_data->field_id ], $object, false );

                                if ( 0 < $id && !in_array( $id, $ids ) ) {
                                    $text = $result[ $search_data->field_index ];

                                    if ( $result[ $search_data->field_id ] != $id ) {
                                        if ( $wpdb->posts == $search_data->table )
                                            $text = trim( get_the_title( $id ) );
                                        elseif ( $wpdb->terms == $search_data->table )
                                            $text = trim( get_term( $id, $object )->name );
                                    }

                                    $result[ $search_data->field_index ] = $text;
                                }
                            }
                            // Polylang integration for Post Types and Taxonomies
                            elseif ( in_array( $search_data->table, array( $wpdb->posts, $wpdb->terms ) ) && is_object( $polylang ) && method_exists( $polylang, 'get_translation' ) ) {
                                $id = $polylang->get_translation( $object, $result[ $search_data->field_id ] );

                                if ( 0 < $id && !in_array( $id, $ids ) ) {
                                    $text = $result[ $search_data->field_index ];

                                    if ( $result[ $search_data->field_id ] != $id ) {
                                        if ( $wpdb->posts == $search_data->table )
                                            $text = trim( get_the_title( $id ) );
                                        elseif ( $wpdb->terms == $search_data->table )
                                            $text = trim( get_term( $id, $object )->name );
                                    }

                                    $result[ $search_data->field_index ] = $text;
                                }
                            }

                            if ( strlen( $result[ $search_data->field_index ] ) < 1 )
                                $result[ $search_data->field_index ] = '(No Title)';

                            $data[ $result[ $search_data->field_id ] ] = $result[ $search_data->field_index ];

                            $items[] = array(
                                'id' => $result[ $search_data->field_id ],
                                'text' => $result[ $search_data->field_index ]
                            );

                            $ids[] = $result[ $search_data->field_id ];
                        }
                    }
                }
            }
        }

        return $data;
    }

    /**
     * Handle autocomplete AJAX
     *
     * @since 2.3
     */
    public function admin_ajax_relationship () {
        if ( false === headers_sent() ) {
            if ( '' == session_id() )
                @session_start();
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

        $uid = @session_id();

        if ( is_user_logged_in() )
            $uid = 'user_' . get_current_user_id();

        $nonce_check = 'pods_relationship_' . (int) $params->pod . '_' . $uid . '_' . $params->uri . '_' . (int) $params->field;

        if ( !isset( $params->_wpnonce ) || false === wp_verify_nonce( $params->_wpnonce, $nonce_check ) )
            pods_error( __( 'Unauthorized request', 'pods' ), PodsInit::$admin );

        $api = pods_api();

        $pod = $api->load_pod( array( 'id' => (int) $params->pod ) );
        $field = $api->load_field( array( 'id' => (int) $params->field, 'table_info' => true ) );

        if ( !isset( $params->query ) || strlen( trim( $params->query ) ) < 1 )
            pods_error( __( 'Invalid field request', 'pods' ), PodsInit::$admin );
        elseif ( empty( $pod ) || empty( $field ) || $pod[ 'id' ] != $field[ 'pod_id' ] || !isset( $pod[ 'fields' ][ $field[ 'name' ] ] ) )
            pods_error( __( 'Invalid field request', 'pods' ), PodsInit::$admin );
        elseif ( 'pick' != $field[ 'type' ] || empty( $field[ 'table_info' ] ) )
            pods_error( __( 'Invalid field', 'pods' ), PodsInit::$admin );
        elseif ( 'single' == pods_var( 'pick_format_type', $field ) && 'autocomplete' == pods_var( 'pick_format_single', $field ) )
            pods_error( __( 'Invalid field', 'pods' ), PodsInit::$admin );
        elseif ( 'multi' == pods_var( 'pick_format_type', $field ) && 'autocomplete' == pods_var( 'pick_format_multi', $field ) )
            pods_error( __( 'Invalid field', 'pods' ), PodsInit::$admin );

        $object_params = array(
            'name' => $field[ 'name' ], // The name of the field
            'value' => null, // The value of the field
            'options' => array_merge( $field, $field), // Field options
            'pod' => $pod, // Pod data
            'id' => 0, // Item ID
            'context' => 'admin_ajax_relationship', // Data context
            'data_params' => $params
        );

        $pick_data = apply_filters( 'pods_field_pick_data_ajax', null, $field[ 'name' ], null, $field, $pod, 0 );

        if ( null !== $pick_data )
            $items = $pick_data;
        else
            $items = $this->get_object_data( $object_params );

        if ( !empty( $items ) && isset( $items[ 0 ] ) && !is_array( $items[ 0 ] ) ) {
            $new_items = array();

            foreach ( $items as $id => $text ) {
                $new_items[] = array(
                    'id' => $id,
                    'text' => $text
                );
            }

            $items = $new_items;
        }

        $items = array(
            'results' => $items
        );

        wp_send_json( $items );

        die(); // KBAI!
    }
}
