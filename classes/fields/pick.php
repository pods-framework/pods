<?php
/**
 * @package Pods\Fields
 */
class PodsField_Pick extends PodsField {

    /**
     * Field Type Group
     *
     * @var string
     * @since 2.0.0
     */
    public static $group = 'Relationships / Media';

    /**
     * Field Type Identifier
     *
     * @var string
     * @since 2.0.0
     */
    public static $type = 'pick';

    /**
     * Field Type Label
     *
     * @var string
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function __construct () {

    }

    /**
     * Add options and set defaults to
     *
     * @return array
     *
     * @since 2.0.0
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
                    ) + ( ( pods_developer() ) ? array( 'flexible' => __( 'Flexible', 'pods' ) ) : array() )
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
                    ) + ( ( pods_developer() ) ? array( 'flexible' => __( 'Flexible', 'pods' ) ) : array() )
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
            'group' => $label,
            'simple' => false,
            'data' => array()
            //'data_callback' => false,
            //'value_to_label_callback' => false,
            //'simple_value_callback' => false
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
                if ( in_array( $post_type, $ignore ) || empty( $post_type ) || 0 === strpos( $post_type, '_pods_' ) ) {
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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

            if ( 'single' == pods_var( 'pick_format_type', $options ) && 'autocomplete' == pods_var( 'pick_format_single', $options ) )
                $autocomplete = true;
            elseif ( 'multi' == pods_var( 'pick_format_type', $options ) && 'autocomplete' == pods_var( 'pick_format_multi', $options ) )
                $autocomplete = true;

            $params[ 'limit' ] = -1;

            if ( $autocomplete )
                $params[ 'limit' ] = apply_filters( 'pods_form_ui_field_pick_autocomplete_limit', 30, $name, $value, $options, $pod, $id );

            $ajax = true;
        }

        if ( 'single' == pods_var( 'pick_format_type', $options ) ) {
            if ( 'dropdown' == pods_var( 'pick_format_single', $options ) )
                $field_type = 'select';
            elseif ( 'radio' == pods_var( 'pick_format_single', $options ) )
                $field_type = 'radio';
            elseif ( 'autocomplete' == pods_var( 'pick_format_single', $options ) )
                $field_type = 'select2';
            else {
                // Support custom integration
                do_action( 'pods_form_ui_field_pick_input_' . pods_var( 'pick_format_type', $options ) . '_' . pods_var( 'pick_format_single', $options ), $name, $value, $options, $pod, $id );
                do_action( 'pods_form_ui_field_pick_input', pods_var( 'pick_format_type', $options ), $name, $value, $options, $pod, $id );
                return;
            }
        }
        elseif ( 'multi' == pods_var( 'pick_format_type', $options ) ) {
            if ( 'checkbox' == pods_var( 'pick_format_multi', $options ) )
                $field_type = 'checkbox';
            elseif ( 'multiselect' == pods_var( 'pick_format_multi', $options ) )
                $field_type = 'select';
            elseif ( 'autocomplete' == pods_var( 'pick_format_multi', $options ) )
                $field_type = 'select2';
            else {
                // Support custom integration
                do_action( 'pods_form_ui_field_pick_input_' . pods_var( 'pick_format_type', $options ) . '_' . pods_var( 'pick_format_multi', $options ), $name, $value, $options, $pod, $id );
                do_action( 'pods_form_ui_field_pick_input', pods_var( 'pick_format_type', $options ), $name, $value, $options, $pod, $id );
                return;
            }
        }

        pods_view( PODS_DIR . 'ui/fields/' . $field_type . '.php', compact( array_keys( get_defined_vars() ) ) );
    }

    /**
     * Get the data from the field
     *
     * @param string $name The name of the field
     * @param string|array $value The value of the field
     * @param array $options
     * @param array $pod
     * @param int $id
     *
     * @return array Array of possible field data
     *
     * @since 2.0.0
     */
    public function data ( $name, $value = null, $options = null, $pod = null, $id = null, $in_form = true ) {
        $data = array( '' => pods_var_raw( 'pick_select_text', $options, __( '-- Select One --', 'pods' ), null, true ) );

        if ( 'single' != pods_var( 'pick_format_type', $options ) || 'dropdown' != pods_var( 'pick_format_single', $options ) )
            $data = array();

        if ( isset( $options[ 'data' ] ) && !empty( $options[ 'data' ] ) )
            $data = (array) $options[ 'data' ];

        $custom = trim( pods_var_raw( 'pick_custom', $options, '' ) );

        $custom = apply_filters( 'pods_form_ui_field_pick_custom_values', $custom, $name, $value, $options, $pod, $id );

        if ( 'custom-simple' == pods_var( 'pick_object', $options ) && !empty( $custom ) ) {
            if ( !is_array( $custom ) ) {
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
            else {
                foreach ( $custom as $custom_value => $custom_label ) {
                    $data[ $custom_value ] = $custom_label;
                }
            }
        }
        elseif ( '' != pods_var( 'pick_object', $options, '' ) && array() == pods_var_raw( 'data', $options, array(), null, true ) ) {
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
            else {
                $db = true;

                if ( isset( self::$related_objects[ $options[ 'pick_object' ] ] ) ) {
                    if ( !empty( self::$related_objects[ $options[ 'pick_object' ][ 'data' ] ] ) ) {
                        $data = self::$related_objects[ $options[ 'pick_object' ] ][ 'data' ];
                        $db = false;
                    }
                    elseif ( isset( self::$related_objects[ $options[ 'pick_object' ] ][ 'data_callback' ] ) && is_callable( self::$related_objects[ $options[ 'pick_object' ] ][ 'data_callback' ] ) ) {
                        $data = call_user_func_array(
                            self::$related_objects[ $options[ 'pick_object' ] ][ 'data_callback' ],
                            array( compact( array( 'name', 'value', 'options', 'pod', 'id' ) ) )
                        );
                        $db = false;
                    }
                }

                if ( $db ) {
                    $pick_val = pods_var( 'pick_val', $options );

                    if ( 'table' == pods_var( 'pick_object', $options ) )
                        $pick_val = pods_var( 'pick_table', $options, $pick_val, null, true );

                    $options[ 'table_info' ] = pods_api()->get_table_info( pods_var( 'pick_object', $options ), $pick_val, null, null, $options );

                    $search_data = pods_data();
                    $search_data->table = $options[ 'table_info' ][ 'table' ];
                    $search_data->join = $options[ 'table_info' ][ 'join' ];
                    $search_data->field_id = $options[ 'table_info' ][ 'field_id' ];
                    $search_data->field_index = $options[ 'table_info' ][ 'field_index' ];
                    $search_data->where = $options[ 'table_info' ][ 'where' ];
                    $search_data->orderby = $options[ 'table_info' ][ 'orderby' ];

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

                    if ( !empty( $params[ 'where' ] ) && (array) $options[ 'table_info' ][ 'where_default' ] != $params[ 'where' ]  )
                        $params[ 'where' ] = pods_evaluate_tags( $params[ 'where' ], true );

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

                    if ( 'single' == pods_var( 'pick_format_type', $options ) && 'autocomplete' == pods_var( 'pick_format_single', $options ) )
                        $autocomplete = true;
                    elseif ( 'multi' == pods_var( 'pick_format_type', $options ) && 'autocomplete' == pods_var( 'pick_format_multi', $options ) )
                        $autocomplete = true;

                    $hierarchy = false;

                    if ( !$autocomplete ) {
                        if ( 'single' == pods_var( 'pick_format_type', $options ) && 'dropdown' == pods_var( 'pick_format_single', $options ) )
                            $hierarchy = true;
                        elseif ( 'multi' == pods_var( 'pick_format_type', $options ) && 'multiselect' == pods_var( 'pick_format_multi', $options ) )
                            $hierarchy = true;
                    }

                    if ( $hierarchy && $options[ 'table_info' ][ 'object_hierarchical' ] && !empty( $options[ 'table_info' ][ 'field_parent' ] ) )
                        $params[ 'select' ] .= ', `' . ( 'taxonomy' == $options[ 'table_info' ][ 'object_type' ] ? 'tt' : 't' ) . '`.`' . $options[ 'table_info' ][ 'field_parent' ] .'`';

                    if ( $autocomplete )
                        $params[ 'limit' ] = apply_filters( 'pods_form_ui_field_pick_autocomplete_limit', 30, $name, $value, $options, $pod, $id );

                    $results = $search_data->select( $params );

                    if ( !empty( $results ) && $hierarchy && $options[ 'table_info' ][ 'object_hierarchical' ] && !empty( $options[ 'table_info' ][ 'field_parent' ] ) ) {
                        $args = array(
                            'id' => $options[ 'table_info' ][ 'field_id' ],
                            'index' => $options[ 'table_info' ][ 'field_index' ],
                            'parent' => $options[ 'table_info' ][ 'field_parent' ],
                        );

                        $results = pods_hierarchical_select( $results, $args );
                    }

                    if ( !empty( $results ) && ( !$autocomplete || $search_data->total_found() <= $params[ 'limit' ] ) ) {
                        foreach ( $results as $result ) {
                            $result = get_object_vars( $result );

                            $result[ $search_data->field_index ] = trim( $result[ $search_data->field_index ] );

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

                                if ( strlen( $result[ $search_data->field_index ] ) < 1 )
                                    $result[ $search_data->field_index ] = '(No Title)';

                                $data[ $result[ $search_data->field_id ] ] = $result[ $search_data->field_index ];
                            }
                        }
                    }
                }
            }
        }

        $data = apply_filters( 'pods_field_pick_data', $data, $name, $value, $options, $pod, $id );

        return $data;
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
     * @since 2.0.0
     */
    public function ui ( $id, $value, $name = null, $options = null, $fields = null, $pod = null ) {
        $value = $this->simple_value( $value, $options );

        return $this->display( $value, $name, $options, $pod, $id );
    }

    /**
     * Convert a simple value to the correct value
     *
     * @param mixed $value Value of the field
     * @param array $options Field options
     * @param boolean $raw Whether to return the raw list of keys (true) or convert to key=>value (false)
     */
    public function simple_value ( $value, $options, $raw = false ) {
        if ( isset( $options[ 'options' ] ) ) {
            $options = array_merge( $options[ 'options' ], $options );

            unset( $options[ 'options' ] );
        }

        if ( in_array( pods_var( 'pick_object', $options ), self::simple_objects() ) ) {
            $data = array();

            if ( 'custom-simple' == $options[ 'pick_object' ] ) {
                $custom = trim( pods_var_raw( 'pick_custom', $options, '' ) );

                $custom = apply_filters( 'pods_form_ui_field_pick_custom_values', $custom, pods_var( 'name', $options ), $value, $options, null, null );

                if ( !empty( $custom ) ) {
                    if ( !is_array( $custom ) ) {
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
            elseif ( 'post-status' == $options[ 'pick_object' ] ) {
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
                if ( !empty( self::$related_objects[ $options[ 'pick_object' ][ 'data' ] ] ) )
                    $data = self::$related_objects[ $options[ 'pick_object' ] ][ 'data' ];
                elseif ( isset( self::$related_objects[ $options[ 'pick_object' ] ][ 'simple_value_callback' ] ) && is_callable( self::$related_objects[ $options[ 'pick_object' ] ][ 'simple_value_callback' ] ) ) {
                    $data = call_user_func_array(
                        self::$related_objects[ $options[ 'pick_object' ] ][ 'simple_value_callback' ],
                        array( compact( array( 'value', 'options', 'raw' ) ) )
                    );
                }
            }

            $simple = false;
            $key = 0;

            if ( !is_array( $value ) && !empty( $value ) )
                $simple = @json_decode( $value, true );

            if ( is_array( $simple ) )
                $value = $simple;

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
     * @param string|array $pod An array of Pod data or Pod name
     * @param string|array $field An array of field data or field name
     * @param string|array $value The value to return label(s) for
     *
     * @return string
     *
     * @since 2.2
     */
    public function value_to_label ( $pod, $field, $value ) {
        if ( !is_array( $pod ) && !empty( $pod ) )
            $pod = pods_api()->load_pod( array( 'name' => $pod ) );

        if ( empty( $pod ) )
            return false;

        if ( !is_array( $field ) && !empty( $field ) )
            $field = pods_api()->load_field( array( 'name' => $field, 'pod' => $pod[ 'name' ] ) );

        if ( empty( $field ) )
            return false;

        $options = array_merge( $field[ 'options' ], $field );

        $custom = trim( pods_var_raw( 'pick_custom', $options, '' ) );

        $custom = apply_filters( 'pods_form_ui_field_pick_custom_values', $custom, $field[ 'name' ], $value, $options, $pod, 0 );

        $data = pods_var_raw( 'data', $options, array(), null, true );

        if ( 'custom-simple' == pods_var( 'pick_object', $options ) && !empty( $custom ) ) {
            if ( !is_array( $custom ) ) {
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

                    if ( $value == $custom_value ) {
                        $data = $custom_label;

                        break;
                    }
                }
            }
            else {
                foreach ( $custom as $custom_value => $custom_label ) {
                    if ( $value == $custom_value ) {
                        $data = $custom_label;

                        break;
                    }
                }
            }
        }
        elseif ( '' != pods_var( 'pick_object', $options, '' ) && empty( $data ) ) {
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
            else {
                $db = true;

                if ( isset( self::$related_objects[ $options[ 'pick_object' ] ] ) ) {
                    if ( !empty( self::$related_objects[ $options[ 'pick_object' ][ 'data' ] ] ) ) {
                        $data = self::$related_objects[ $options[ 'pick_object' ] ][ 'data' ];
                        $db = false;
                    }
                    elseif ( isset( self::$related_objects[ $options[ 'pick_object' ] ][ 'data_callback' ] ) && is_callable( self::$related_objects[ $options[ 'pick_object' ] ][ 'data_callback' ] ) ) {
                        $data = call_user_func_array(
                            self::$related_objects[ $options[ 'pick_object' ] ][ 'value_to_label_callback' ],
                            array( compact( array( 'pod', 'field', 'value' ) ) )
                        );
                        $db = false;
                    }
                }

                if ( $db ) {
                    $pick_val = pods_var( 'pick_val', $options );

                    if ( 'table' == pods_var( 'pick_object', $options ) )
                        $pick_val = pods_var( 'pick_table', $options, $pick_val, null, true );

                    $options[ 'table_info' ] = pods_api()->get_table_info( pods_var( 'pick_object', $options ), $pick_val, null, null, $options );

                    $search_data = pods_data();
                    $search_data->table = $options[ 'table_info' ][ 'table' ];
                    $search_data->join = $options[ 'table_info' ][ 'join' ];
                    $search_data->field_id = $options[ 'table_info' ][ 'field_id' ];
                    $search_data->field_index = $options[ 'table_info' ][ 'field_index' ];
                    $search_data->where = $options[ 'table_info' ][ 'where' ];
                    $search_data->orderby = $options[ 'table_info' ][ 'orderby' ];

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

                    if ( !is_array( $params[ 'where' ] ) )
                        $params[ 'where' ] = (array) $params[ 'where' ];

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

                    if ( 'single' == pods_var( 'pick_format_type', $options ) && 'autocomplete' == pods_var( 'pick_format_single', $options ) )
                        $autocomplete = true;
                    elseif ( 'multi' == pods_var( 'pick_format_type', $options ) && 'autocomplete' == pods_var( 'pick_format_multi', $options ) )
                        $autocomplete = true;

                    if ( $autocomplete )
                        $params[ 'limit' ] = apply_filters( 'pods_form_ui_field_pick_autocomplete_limit', 30, $field[ 'name' ], $value, $options, $pod, 0 );

                    $results = $search_data->select( $params );

                    if ( !empty( $results ) && ( !$autocomplete || $search_data->total_found() <= $params[ 'limit' ] ) ) {
                        foreach ( $results as $result ) {
                            $result = get_object_vars( $result );

                            $result[ $search_data->field_index ] = trim( $result[ $search_data->field_index ] );

                            if ( in_array( $options[ 'pick_object' ], array( 'site', 'network' ) ) )
                                $result[ $search_data->field_index ] = $result[ $search_data->field_index ] . $result[ 'path' ];
                            elseif ( strlen( $result[ $search_data->field_index ] ) < 1 )
                                $result[ $search_data->field_index ] = '(No Title)';

                            $data = $result[ $search_data->field_index ];
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

                                if ( strlen( $result[ $search_data->field_index ] ) < 1 )
                                    $result[ $search_data->field_index ] = '(No Title)';

                                $data = $result[ $search_data->field_index ];
                            }
                        }
                    }
                }
            }
        }

        $data = apply_filters( 'pods_field_pick_value_data', $data, $pod, $field, $value );

        $data = pods_serial_comma( $data );

        return $data;
    }
}
