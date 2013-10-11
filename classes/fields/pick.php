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
     * @since 2.3
     */
    public static $related_objects = array();

    /**
     * Custom Related Objects
     *
     * @var array
     * @since 2.3
     */
    public static $custom_related_objects = array();

    /**
     * Data used during validate / save to avoid extra queries
     *
     * @var array
     * @since 2.3
     */
    public static $related_data = array();

    /**
     * Data used during input method (mainly for autocomplete)
     *
     * @var array
     * @since 2.3
     */
    public static $field_data = array();

    /**
     * API caching for fields that need it during validate/save
     *
     * @var \PodsAPI
     * @since 2.3
     */
    protected static $api = false;

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
                    ) + ( ( pods_developer() && 1 == 0 ) ? array( 'flexible' => __( 'Flexible', 'pods' ) ) : array() ) // Disable for now
                ),
                'dependency' => true
            ),
			'pick_select_text' => array(
                'label' => __( 'Default Select Text', 'pods' ),
                'help' => __( 'This is the text use for the default "no selection" dropdown item, if empty, it will default to "-- Select One --"', 'pods' ),
                'depends-on' => array(
					'pick_format_type' => 'single',
					'pick_format_single' => 'dropdown'
				),
                'default' => '',
                'type' => 'text'
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
     * @since 2.3
     */
    public function register_related_object ( $name, $label, $options = null ) {
        if ( empty( $name ) || empty( $label ) )
            return false;

        $related_object = array(
            'label' => $label,
            'group' => 'Custom Relationships',
            'simple' => true,
            'bidirectional' => false,
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
     * @since 2.3
     */
    public function setup_related_objects ( $force = false ) {
        $related_objects = pods_transient_get( 'pods_related_objects' );

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
            $pod_options = array();

			// Include PodsMeta if not already included
			pods_meta();

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
                    'group' => __( 'Pods', 'pods' ),
                    'bidirectional' => true
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
                elseif ( 0 === strpos( $post_type, '_pods_' ) ) {
                    unset( $post_types[ $post_type ] );

                    continue;
                }

                $post_type = get_post_type_object( $post_type );

                self::$related_objects[ 'post_type-' . $post_type->name ] = array(
                    'label' => $post_type->label . ' (' . $post_type->name . ')',
                    'group' => __( 'Post Types', 'pods' ),
                    'bidirectional' => true
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
                    'label' => $taxonomy->label . ' (' . $taxonomy->name . ')',
                    'group' => __( 'Taxonomies', 'pods' ),
                    'bidirectional' => true
                );
            }

            // Other WP Objects
            self::$related_objects[ 'user' ] = array(
                'label' => __( 'Users', 'pods' ),
                'group' => __( 'Other WP Objects', 'pods' ),
                'bidirectional' => true
            );

            self::$related_objects[ 'role' ] = array(
                'label' => __( 'User Roles', 'pods' ),
                'group' => __( 'Other WP Objects', 'pods' ),
                'simple' => true,
                'data_callback' => array( $this, 'data_roles' )
            );

            self::$related_objects[ 'capability' ] = array(
                'label' => __( 'User Capabilities', 'pods' ),
                'group' => __( 'Other WP Objects', 'pods' ),
                'simple' => true,
                'data_callback' => array( $this, 'data_capabilities' )
            );

            self::$related_objects[ 'media' ] = array(
                'label' => __( 'Media', 'pods' ),
                'group' => __( 'Other WP Objects', 'pods' ),
                'bidirectional' => true
            );

            self::$related_objects[ 'comment' ] = array(
                'label' => __( 'Comments', 'pods' ),
                'group' => __( 'Other WP Objects', 'pods' ),
                'bidirectional' => true
            );

            self::$related_objects[ 'image-size' ] = array(
                'label' => __( 'Image Sizes', 'pods' ),
                'group' => __( 'Other WP Objects', 'pods' ),
                'simple' => true,
                'data_callback' => array( $this, 'data_image_sizes' )
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
                'simple' => true,
                'data_callback' => array( $this, 'data_post_stati' )
            );

            do_action( 'pods_form_ui_field_pick_related_objects_other' );

            self::$related_objects[ 'country' ] = array(
                'label' => __( 'Countries', 'pods' ),
                'group' => __( 'Predefined Lists', 'pods' ),
                'simple' => true,
                'data_callback' => array( $this, 'data_countries' )
            );

            self::$related_objects[ 'us_state' ] = array(
                'label' => __( 'US States', 'pods' ),
                'group' => __( 'Predefined Lists', 'pods' ),
                'simple' => true,
                'data_callback' => array( $this, 'data_us_states' )
            );

            self::$related_objects[ 'days_of_week' ] = array(
                'label' => __( 'Calendar - Days of Week', 'pods' ),
                'group' => __( 'Predefined Lists', 'pods' ),
                'simple' => true,
                'data_callback' => array( $this, 'data_days_of_week' )
            );

            self::$related_objects[ 'months_of_year' ] = array(
                'label' => __( 'Calendar - Months of Year', 'pods' ),
                'group' => __( 'Predefined Lists', 'pods' ),
                'simple' => true,
                'data_callback' => array( $this, 'data_months_of_year' )
            );

            do_action( 'pods_form_ui_field_pick_related_objects_predefined' );

            if ( did_action( 'init' ) )
                pods_transient_set( 'pods_related_objects', self::$related_objects );
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
     * @since 2.3
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
     * @since 2.3
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
     * Return available bidirectional object names
     *
     * @return array Bidirectional object names
     * @since 2.3.4
     */
    public function bidirectional_objects () {
        $this->setup_related_objects();

        $bidirectional_objects = array();

        foreach ( self::$related_objects as $object => $related_object ) {
            if ( !isset( $related_object[ 'bidirectional' ] ) || !$related_object[ 'bidirectional' ] )
                continue;

            $bidirectional_objects[] = $object;
        }

        return (array) apply_filters( 'pods_form_ui_field_pick_bidirectional_objects', $bidirectional_objects );
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

        $simple_tableless_objects = $this->simple_objects();

        if ( in_array( pods_var( 'pick_object', $options ), $simple_tableless_objects ) )
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

        return pods_serial_comma( $value, array( 'field' => $name, 'fields' => $fields ) );
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

        if ( ( 'custom-simple' != pods_var( 'pick_object', $options ) || empty( $custom ) ) && '' != pods_var( 'pick_object', $options, '', null, true ) )
            $ajax = true;

        if ( !empty( self::$field_data ) && self::$field_data[ 'id' ] == $options[ 'id' ] ) {
            $ajax = (boolean) self::$field_data[ 'autocomplete' ];
        }

        $ajax = apply_filters( 'pods_form_ui_field_pick_ajax', $ajax, $name, $value, $options, $pod, $id );

        if ( 0 == pods_var( 'pick_ajax', $options, 1 ) )
            $ajax = false;

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
            if ( !empty( $value ) && !is_array( $value ) )
                $value = explode( ',', $value );

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
     * Validate a value before it's saved
     *
     * @param mixed $value
     * @param string $name
     * @param array $options
     * @param array $fields
     * @param array $pod
     * @param int $id
     *
     * @param null $params
     * @return array|bool
     * @since 2.0
     */
    public function validate ( $value, $name = null, $options = null, $fields = null, $pod = null, $id = null, $params = null ) {
        if ( empty( self::$api ) )
            self::$api = pods_api();

        $simple_tableless_objects = $this->simple_objects();

        $related_pick_limit = 0;
        $related_field = $related_pod = $current_related_ids = false;

        // Bidirectional relationship requirement checks
        $related_object = pods_var( 'pick_object', $options, '' ); // pod, post_type, taxonomy, etc..
        $related_val = pods_var( 'pick_val', $options, $related_object, null, true ); // pod name, post type name, taxonomy name, etc..
        $related_sister_id = (int) pods_var( 'sister_id', $options, 0 );

        $options[ 'id' ] = (int) $options[ 'id' ];

        if ( !isset( self::$related_data[ $options[ 'id' ] ] ) || empty( self::$related_data[ $options[ 'id' ] ] ) )
            self::$related_data[ $options[ 'id' ] ] = array();

        if ( !empty( $related_sister_id ) && !in_array( $related_object, $simple_tableless_objects ) ) {
            $related_pod = self::$api->load_pod( array( 'name' => $related_val, 'table_info' => false ), false );

            if ( false !== $related_pod && ( 'pod' == $related_object || $related_object == $related_pod[ 'type' ] ) ) {
                $related_field = false;

                // Ensure sister_id exists on related Pod
                foreach ( $related_pod[ 'fields' ] as $related_pod_field ) {
                    if ( 'pick' == $related_pod_field[ 'type' ] && $related_sister_id == $related_pod_field[ 'id' ] ) {
                        $related_field = $related_pod_field;

                        break;
                    }
                }

                if ( !empty( $related_field ) ) {
                    $current_ids = self::$api->lookup_related_items( $fields[ $name ][ 'id' ], $pod[ 'id' ], $id, $fields[ $name ], $pod );

                    self::$related_data[ $options[ 'id' ] ][ 'current_ids' ] = $current_ids;

                    $value_ids = $value;

                    // Convert values from a comma-separated string into an array
                    if ( !is_array( $value_ids ) )
                        $value_ids = explode( ',', $value_ids );

                    $value_ids = array_unique( array_filter( $value_ids ) );

                    // Get ids to remove
                    $remove_ids = array_diff( $current_ids, $value_ids );

                    $related_required = (boolean) pods_var( 'required', $related_field[ 'options' ], 0 );
                    $related_pick_limit = (int) pods_var( 'pick_limit', $related_field[ 'options' ], 0 );

                    if ( 'single' == pods_var_raw( 'pick_format_type', $related_field[ 'options' ] ) )
                        $related_pick_limit = 1;

                    // Validate Required
                    if ( $related_required && !empty( $remove_ids ) ) {
                        foreach ( $remove_ids as $related_id ) {
                            $bidirectional_ids = self::$api->lookup_related_items( $related_field[ 'id' ], $related_pod[ 'id' ], $related_id, $related_field, $related_pod );

                            self::$related_data[ $options[ 'id' ] ][ 'related_ids_' . $related_id ] = $bidirectional_ids;

                            if ( empty( $bidirectional_ids ) || ( in_array( $id, $bidirectional_ids ) && 1 == count( $bidirectional_ids ) ) )
                                return sprintf( __( 'The %s field is required and cannot be removed by the %s field', 'pods' ), $related_field[ 'label' ], $options[ 'label' ] );
                        }
                    }
                }
                else
                    $related_pod = false;
            }
            else
                $related_pod = false;
        }

        if ( empty( self::$related_data[ $options[ 'id' ] ] ) )
            unset( self::$related_data[ $options[ 'id' ] ] );
        else {
            self::$related_data[ $options[ 'id' ] ][ 'related_pod' ] = $related_pod;
            self::$related_data[ $options[ 'id' ] ][ 'related_field' ] = $related_field;
            self::$related_data[ $options[ 'id' ] ][ 'related_pick_limit' ] = $related_pick_limit;

            $pick_limit = (int) pods_var( 'pick_limit', $options[ 'options' ], 0 );

            if ( 'single' == pods_var_raw( 'pick_format_type', $options[ 'options' ] ) )
                $pick_limit = 1;

            $related_field[ 'id' ] = (int) $related_field[ 'id' ];

            if ( !isset( self::$related_data[ $related_field[ 'id' ] ] ) || empty( self::$related_data[ $related_field[ 'id' ] ] ) ) {
                self::$related_data[ $related_field[ 'id' ] ] = array(
                    'related_pod' => $pod,
                    'related_field' => $options,
                    'related_pick_limit' => $pick_limit
                );
            }
        }

        return true;
    }

    /**
     * Save the value to the DB
     *
     * @param mixed $value
     * @param int $id
     * @param string $name
     * @param array $options
     * @param array $fields
     * @param array $pod
     * @param object $params
     *
     * @since 2.3
     */
    public function save ( $value, $id = null, $name = null, $options = null, $fields = null, $pod = null, $params = null ) {
        if ( empty( self::$api ) )
            self::$api = pods_api();

        $options[ 'id' ] = (int) $options[ 'id' ];

        if ( !isset( self::$related_data[ $options[ 'id' ] ] ) )
            return;

        $related_pod = self::$related_data[ $options[ 'id' ] ][ 'related_pod' ];
        $related_field = self::$related_data[ $options[ 'id' ] ][ 'related_field' ];
        $related_pick_limit = self::$related_data[ $options[ 'id' ] ][ 'related_pick_limit' ];

        // Bidirectional relationship updates
        if ( !empty( $related_field ) ) {
            // Don't use no conflict mode unless this isn't the current pod type
            $no_conflict = true;

            if ( $related_pod[ 'type' ] != $pod[ 'type' ] )
                $no_conflict = pods_no_conflict_check( $related_pod[ 'type' ] );

            if ( !$no_conflict )
                pods_no_conflict_on( $related_pod[ 'type' ] );

            $value = array_filter( $value );

            foreach ( $value as $related_id ) {
                if ( isset( self::$related_data[ $options[ 'id' ] ][ 'related_ids_' . $related_id ] ) && !empty( self::$related_data[ $options[ 'id' ] ][ 'related_ids_' . $related_id ] ) )
                    $bidirectional_ids = self::$related_data[ $options[ 'id' ] ][ 'related_ids_' . $related_id ];
                else
                    $bidirectional_ids = self::$api->lookup_related_items( $related_field[ 'id' ], $related_pod[ 'id' ], $related_id, $related_field, $related_pod );

                $bidirectional_ids = array_filter( $bidirectional_ids );

                if ( empty( $bidirectional_ids ) )
                    $bidirectional_ids = array();

                $remove_ids = array();

                if ( 0 < $related_pick_limit && !empty( $bidirectional_ids ) && !in_array( $id, $bidirectional_ids ) ) {
                    while ( $related_pick_limit <= count( $bidirectional_ids ) ) {
                        $remove_ids[] = (int) array_pop( $bidirectional_ids );
                    }
                }

                // Remove this item from related items no longer related to
                $remove_ids = array_unique( array_filter( $remove_ids ) );

                // Add to related items
                if ( !in_array( $id, $bidirectional_ids ) )
                    $bidirectional_ids[] = $id;
                // Nothing to change
                elseif ( empty( $remove_ids ) )
                    continue;

                self::$api->save_relationships( $related_id, $bidirectional_ids, $related_pod, $related_field );

                if ( !empty( $remove_ids ) )
                    self::$api->delete_relationships( $remove_ids, $related_id, $pod, $options );
            }

            if ( !$no_conflict )
                pods_no_conflict_off( $related_pod[ 'type' ] );
        }
    }

    /**
     * Delete the value from the DB
     *
     * @param int $id
     * @param string $name
     * @param array $options
     * @param array $pod
     *
     * @since 2.3
     */
    public function delete ( $id = null, $name = null, $options = null, $pod = null ) {
        if ( empty( self::$api ) )
            self::$api = pods_api();

        $simple_tableless_objects = $this->simple_objects();

        // Bidirectional relationship requirement checks
        $related_object = pods_var( 'pick_object', $options, '' ); // pod, post_type, taxonomy, etc..
        $related_val = pods_var( 'pick_val', $options, $related_object, null, true ); // pod name, post type name, taxonomy name, etc..
        $related_sister_id = (int) pods_var( 'sister_id', $options, 0 );

        if ( !empty( $related_sister_id ) && !in_array( $related_object, $simple_tableless_objects ) ) {
            $related_pod = self::$api->load_pod( array( 'name' => $related_val, 'table_info' => false ), false );

            if ( false !== $related_pod && ( 'pod' == $related_object || $related_object == $related_pod[ 'type' ] ) ) {
                $related_field = false;

                // Ensure sister_id exists on related Pod
                foreach ( $related_pod[ 'fields' ] as $related_pod_field ) {
                    if ( 'pick' == $related_pod_field[ 'type' ] && $related_sister_id == $related_pod_field[ 'id' ] ) {
                        $related_field = $related_pod_field;

                        break;
                    }
                }

                if ( !empty( $related_field ) ) {
                    $values = self::$api->lookup_related_items( $options[ 'id' ], $pod[ 'id' ], $id, $options, $pod );

                    if ( !empty( $values ) ) {
                        $no_conflict = pods_no_conflict_check( $related_pod[ 'type' ] );

                        if ( !$no_conflict )
                            pods_no_conflict_on( $related_pod[ 'type' ] );

                        self::$api->delete_relationships( $values, $id, $related_pod, $related_field );

                        if ( !$no_conflict )
                            pods_no_conflict_off( $related_pod[ 'type' ] );
                    }
                }
            }
        }
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
    public function ui ( $id, $value, $name = null, $options = null, $fields = null, $pod = null ) {
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
     * @param boolean $in_form
     *
     * @return array Array of possible field data
     *
     * @since 2.0
     */
    public function data ( $name, $value = null, $options = null, $pod = null, $id = null, $in_form = true ) {
        if ( isset( $options[ 'options' ] ) ) {
            $options = array_merge( $options, $options[ 'options' ] );

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
            $data = array( '' => pods_var_raw( 'pick_select_text', $options, __( '-- Select One --', 'pods' ), null, true ) ) + $data;

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
                $options = array_merge( $options, $options[ 'options' ] );

                unset( $options[ 'options' ] );
            }

            if ( !is_array( $value ) && 0 < strlen( $value ) ) {
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

            if ( null === $data )
                $data = $this->get_object_data( $object_params );

            $data = (array) $data;

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
            $options = array_merge( $options, $options[ 'options' ] );

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
	 * Get available items from a relationship field
	 *
	 * @param array|string $field Field array or field name
	 * @param array $options [optional] Field options array overrides
	 * @param array $object_params [optional] Additional get_object_data options
	 *
	 * @return array An array of available items from a relationship field
	 */
	public function get_field_data( $field, $options = array(), $object_params = array() ) {

		// Handle field array overrides
		if ( is_array( $field ) ) {
			$options = array_merge( $field, $options );
		}

		// Get field name from array
		$field = pods_var_raw( 'name', $options, $field, null, true );

		// Field name or options not set
		if ( empty( $field ) || empty( $options ) ) {
			return array();
		}

		// Options normalization
		$options = array_merge( $options, pods_var_raw( 'options', $options, array(), null, true ) );

		// Setup object params
        $object_params = array_merge(
			array(
				'name' => $field, // The name of the field
				'options' => $options, // Field options
			),
			$object_params
        );

		// Get data override
        $data = pods_var_raw( 'data', $options, null, null, true );

		// Return data override
        if ( null !== $data ) {
            $data = (array) $data;
		}
		// Get object data
        else {
            $data = $this->get_object_data( $object_params );
		}

		return $data;

	}

    /**
     * Get data from relationship objects
     *
     * @param array $object_params Object data parameters
     *
     * @return array|bool Object data
     */
    public function get_object_data ( $object_params = null ) {
        global $wpdb, $polylang, $sitepress, $icl_adjust_id_url_filter_off;

        $current_language = false;

        // WPML support
        if ( is_object( $sitepress ) && !$icl_adjust_id_url_filter_off )
            $current_language = pods_sanitize( ICL_LANGUAGE_CODE );
        // Polylang support
        elseif ( function_exists( 'pll_current_language' ) )
            $current_language = pll_current_language( 'slug' );

        $object_params = array_merge(
            array(
                'name' => '', // The name of the field
                'value' => '', // The value of the field
                'options' => array(), // Field options
                'pod' => '', // Pod data
                'id' => '', // Item ID
                'context' => '', // Data context
                'data_params' => array(
                    'query' => '' // Query being searched
                ),
                'page' => 1, // Page number of results to get
				'limit' => 0 // How many data items to limit to (autocomplete defaults to 30, set to -1 or 1+ to override)
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
        $page = min( 1, (int) $object_params[ 'page' ] );
        $limit = (int) $object_params[ 'limit' ];

        if ( isset( $options[ 'options' ] ) ) {
            $options = array_merge( $options, $options[ 'options' ] );

            unset( $options[ 'options' ] );
        }

        $data = apply_filters( 'pods_field_pick_object_data', null, $name, $value, $options, $pod, $id, $object_params );
        $items = array();

        if ( !isset( $options[ 'pick_object' ] ) )
            $data = pods_var_raw( 'data', $options, array(), null, true );

		$simple = false;

        if ( null === $data ) {
            $data = array();

            if ( 'custom-simple' == $options[ 'pick_object' ] ) {
                $custom = pods_var_raw( 'pick_custom', $options, '' );

                $custom = apply_filters( 'pods_form_ui_field_pick_custom_values', $custom, $name, $value, $options, $pod, $id, $object_params );

                if ( !empty( $custom ) ) {
                    if ( !is_array( $custom ) ) {
                        $data = array();

                        $custom = explode( "\n", trim( $custom ) );

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

                            $data[ (string) $custom_value ] = (string) $custom_label;
                        }
                    }
                    else
                        $data = $custom;

					$simple = true;
                }
            }
            elseif ( isset( self::$related_objects[ $options[ 'pick_object' ] ] ) && isset( self::$related_objects[ $options[ 'pick_object' ] ][ 'data' ] ) && !empty( self::$related_objects[ $options[ 'pick_object' ] ][ 'data' ] ) ) {
                $data = self::$related_objects[ $options[ 'pick_object' ] ][ 'data' ];

				$simple = true;
			}
            elseif ( isset( self::$related_objects[ $options[ 'pick_object' ] ] ) && isset( self::$related_objects[ $options[ 'pick_object' ] ][ 'data_callback' ] ) && is_callable( self::$related_objects[ $options[ 'pick_object' ] ][ 'data_callback' ] ) ) {
                $data = call_user_func_array(
                    self::$related_objects[ $options[ 'pick_object' ] ][ 'data_callback' ],
                    array( $name, $value, $options, $pod, $id )
                );

				$simple = true;

                // Cache data from callback
                if ( !empty( $data ) )
                    self::$related_objects[ $options[ 'pick_object' ] ][ 'data' ] = $data;
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
                    'groupby' => pods_var_raw( 'pick_groupby', $options, null, null, true ),
                    //'having' => pods_var_raw( 'pick_having', $options, null, null, true ),
					'pagination' => false,
					'search' => false
                );

                if ( in_array( $options[ 'pick_object' ], array( 'site', 'network' ) ) )
                    $params[ 'select' ] .= ', `t`.`path`';

                if ( !empty( $params[ 'where' ] ) && (array) $options[ 'table_info' ][ 'where_default' ] != $params[ 'where' ] )
                    $params[ 'where' ] = pods_evaluate_tags( $params[ 'where' ], true );

                if ( empty( $params[ 'where' ] ) || ( !is_array( $params[ 'where' ] ) && strlen( trim( $params[ 'where' ] ) ) < 1 ) )
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
					if ( 0 == $limit ) {
						$limit = 30;
					}

                    $params[ 'limit' ] = apply_filters( 'pods_form_ui_field_pick_autocomplete_limit', $limit, $name, $value, $options, $pod, $id, $object_params );
                    $params[ 'page' ] = $page;

                    if ( 'admin_ajax_relationship' == $context ) {
                        $lookup_where = array(
                            $search_data->field_index => "`t`.`{$search_data->field_index}` LIKE '%" . pods_sanitize_like( $data_params[ 'query' ] ) . "%'"
                        );

                        // @todo Hook into WPML for each table
                        if ( $wpdb->users == $search_data->table ) {
                            $lookup_where[ 'display_name' ] = "`t`.`display_name` LIKE '%" . pods_sanitize_like( $data_params[ 'query' ] ) . "%'";
                            $lookup_where[ 'user_login' ] = "`t`.`user_login` LIKE '%" . pods_sanitize_like( $data_params[ 'query' ] ) . "%'";
                            $lookup_where[ 'user_email' ] = "`t`.`user_email` LIKE '%" . pods_sanitize_like( $data_params[ 'query' ] ) . "%'";
                        }
                        elseif ( $wpdb->posts == $search_data->table ) {
                            $lookup_where[ 'post_title' ] = "`t`.`post_title` LIKE '%" . pods_sanitize_like( $data_params[ 'query' ] ) . "%'";
                            $lookup_where[ 'post_name' ] = "`t`.`post_name` LIKE '%" . pods_sanitize_like( $data_params[ 'query' ] ) . "%'";
                            $lookup_where[ 'post_content' ] = "`t`.`post_content` LIKE '%" . pods_sanitize_like( $data_params[ 'query' ] ) . "%'";
                            $lookup_where[ 'post_excerpt' ] = "`t`.`post_excerpt` LIKE '%" . pods_sanitize_like( $data_params[ 'query' ] ) . "%'";
                        }
                        elseif ( $wpdb->terms == $search_data->table ) {
                            $lookup_where[ 'name' ] = "`t`.`name` LIKE '%" . pods_sanitize_like( $data_params[ 'query' ] ) . "%'";
                            $lookup_where[ 'slug' ] = "`t`.`slug` LIKE '%" . pods_sanitize_like( $data_params[ 'query' ] ) . "%'";
                        }
                        elseif ( $wpdb->comments == $search_data->table ) {
                            $lookup_where[ 'comment_content' ] = "`t`.`comment_content` LIKE '%" . pods_sanitize_like( $data_params[ 'query' ] ) . "%'";
                            $lookup_where[ 'comment_author' ] = "`t`.`comment_author` LIKE '%" . pods_sanitize_like( $data_params[ 'query' ] ) . "%'";
                            $lookup_where[ 'comment_author_email' ] = "`t`.`comment_author_email` LIKE '%" . pods_sanitize_like( $data_params[ 'query' ] ) . "%'";
                        }

                        $lookup_where = apply_filters( 'pods_form_ui_field_pick_autocomplete_lookup', $lookup_where, $data_params[ 'query' ], $name, $value, $options, $pod, $id, $object_params, $search_data );

                        if ( !empty( $lookup_where ) )
                            $params[ 'where' ][] = implode( ' OR ', $lookup_where );

                        $orderby = array();
                        $orderby[] = "(`t`.`{$search_data->field_index}` LIKE '%" . pods_sanitize_like( $data_params[ 'query' ] ) . "%' ) DESC";

                        $pick_orderby = pods_var_raw( 'pick_orderby', $options, null, null, true );

                        if ( 0 < strlen( $pick_orderby ) )
                            $orderby[] = $pick_orderby;

                        $orderby[] = "`t`.`{$search_data->field_index}`";
                        $orderby[] = "`t`.`{$search_data->field_id}`";

                        $params[ 'orderby' ] = $orderby;
                    }
                }
				elseif ( 0 < $limit ) {
                    $params[ 'limit' ] = $limit;
                    $params[ 'page' ] = $page;
				}

                $extra = '';

                if ( $wpdb->posts == $search_data->table )
                    $extra = ', `t`.`post_type`';
                elseif ( $wpdb->terms == $search_data->table )
                    $extra = ', `tt`.`taxonomy`';
                elseif ( $wpdb->comments == $search_data->table )
                    $extra = ', `t`.`comment_type`';

                $params[ 'select' ] .= $extra;

                if ( 'user' == pods_var( 'pick_object', $options ) ) {
                    $roles = pods_var( 'pick_user_role', $options );

                    if ( !empty( $roles ) ) {
                        $where = array();

                        foreach ( (array) $roles as $role ) {
                            if ( empty( $role ) || ( pods_clean_name( $role ) != $role && sanitize_title( $role ) != $role ) )
                                continue;

                            $where[] = 'wp_' . ( ( is_multisite() && !is_main_site() ) ? get_current_blog_id() . '_' : '' ) . 'capabilities.meta_value LIKE "%\"' . pods_sanitize_like( $role ) . '\"%"';
                        }

                        if ( !empty( $where ) ) {
                            $params[ 'where' ][] = implode( ' OR ', $where );
                        }
                    }
                }

                $results = $search_data->select( $params );

                if ( $autocomplete && $params[ 'limit' ] < $search_data->total_found() ) {
                    if ( !empty( $value ) ) {
                        $ids = $value;

						if ( is_array( $ids ) && isset( $ids[ 0 ] ) && is_array( $ids[ 0 ] ) ) {
							$ids = wp_list_pluck( $ids, $search_data->field_id );
						}

                        if ( is_array( $ids ) )
                            $ids = implode( ', ', $ids );

                        if ( is_array( $params[ 'where' ] ) )
                            $params[ 'where' ] = implode( ' AND ', $params[ 'where' ] );
                        if ( !empty( $params[ 'where' ] ) )
                            $params[ 'where' ] .= ' AND ';

                        $params[ 'where' ] .= "`t`.`{$search_data->field_id}` IN ( " . $ids . " )";

                        $results = $search_data->select( $params );
                    }
                }
                else
                    $autocomplete = false;

                if ( 'data' == $context ) {
                    self::$field_data = array(
                        'field' => $name,
                        'id' => $options[ 'id' ],
                        'autocomplete' => $autocomplete
                    );
                }

                if ( $hierarchy && !$autocomplete && !empty( $results ) && $options[ 'table_info' ][ 'object_hierarchical' ] && !empty( $options[ 'table_info' ][ 'field_parent' ] ) ) {
                    $args = array(
                        'id' => $options[ 'table_info' ][ 'field_id' ],
                        'index' => $options[ 'table_info' ][ 'field_index' ],
                        'parent' => $options[ 'table_info' ][ 'field_parent' ],
                    );

                    $results = pods_hierarchical_select( $results, $args );
                }

                $ids = array();

                if ( !empty( $results ) ) {
                    $display_filter = pods_var( 'display_filter', pods_var_raw( 'options', pods_var_raw( $search_data->field_index, $search_data->pod_data[ 'object_fields' ] ) ) );

                    foreach ( $results as $result ) {
                        $result = get_object_vars( $result );

                        if ( !isset( $result[ $search_data->field_id ] ) || !isset( $result[ $search_data->field_index ] ) )
                            continue;

                        $result[ $search_data->field_index ] = trim( $result[ $search_data->field_index ] );

                        $object = $object_type = '';

                        if ( $wpdb->posts == $search_data->table && isset( $result[ 'post_type' ] ) ) {
                            $object = $result[ 'post_type' ];
                            $object_type = 'post_type';
                        }
                        elseif ( $wpdb->terms == $search_data->table && isset( $result[ 'taxonomy' ] ) ) {
                            $object = $result[ 'taxonomy' ];
                            $object_type = 'taxonomy';
                        }

                        // WPML integration for Post Types and Taxonomies
                        if ( is_object( $sitepress ) && in_array( $object_type, array( 'post_type', 'taxonomy' ) ) ) {
                            $translated = false;

                            if ( 'post_type' == $object_type && $sitepress->is_translated_post_type( $object ) )
                                $translated = true;
                            elseif ( 'taxonomy' == $object_type && $sitepress->is_translated_taxonomy( $object ) )
                                $translated = true;

                            if ( $translated ) {
                                $object_id = icl_object_id( $result[ $search_data->field_id ], $object, false, $current_language );

                                if ( 0 < $object_id && !in_array( $object_id, $ids ) ) {
                                    $text = $result[ $search_data->field_index ];

                                    if ( $result[ $search_data->field_id ] != $object_id ) {
                                        if ( $wpdb->posts == $search_data->table )
                                            $text = trim( get_the_title( $object_id ) );
                                        elseif ( $wpdb->terms == $search_data->table )
                                            $text = trim( get_term( $object_id, $object )->name );
                                    }

                                    $result[ $search_data->field_id ] = $object_id;
                                    $result[ $search_data->field_index ] = $text;
                                }
                                else
                                    continue;
                            }
                        }
                        // Polylang integration for Post Types and Taxonomies
                        elseif ( is_object( $polylang ) && in_array( $object_type, array( 'post_type', 'taxonomy' ) ) && method_exists( $polylang, 'get_translation' ) ) {
                            $translated = false;

                            if ( 'post_type' == $object_type && pll_is_translated_post_type( $object ) )
                                $translated = true;
                            elseif ( 'taxonomy' == $object_type && pll_is_translated_taxonomy( $object ) )
                                $translated = true;

                            if ( $translated ) {
                                $object_id = $polylang->get_translation( $object, $result[ $search_data->field_id ], $current_language );

                                if ( 0 < $object_id && !in_array( $object_id, $ids ) ) {
                                    $text = $result[ $search_data->field_index ];

                                    if ( $result[ $search_data->field_id ] != $object_id ) {
                                        if ( $wpdb->posts == $search_data->table )
                                            $text = trim( get_the_title( $object_id ) );
                                        elseif ( $wpdb->terms == $search_data->table )
                                            $text = trim( get_term( $object_id, $object )->name );
                                    }

                                    $result[ $search_data->field_id ] = $object_id;
                                    $result[ $search_data->field_index ] = $text;
                                }
                                else
                                    continue;
                            }
                        }

                        if ( 0 < strlen( $display_filter ) ) {
                            $display_filter_args = pods_var( 'display_filter_args', pods_var_raw( 'options', pods_var_raw( $search_data->field_index, $search_data->pod_data[ 'object_fields' ] ) ) );

                            $args = array(
                                $display_filter,
                                $result[ $search_data->field_index ]
                            );

                            if ( !empty( $display_filter_args ) ) {
                                foreach ( (array) $display_filter_args as $display_filter_arg ) {
                                    if ( isset( $result[ $display_filter_arg ] ) )
                                        $args[] = $result[ $display_filter_arg ];
                                }
                            }

                            $result[ $search_data->field_index ] = call_user_func_array( 'apply_filters', $args );
                        }

                        if ( in_array( $options[ 'pick_object' ], array( 'site', 'network' ) ) )
                            $result[ $search_data->field_index ] = $result[ $search_data->field_index ] . $result[ 'path' ];
                        elseif ( strlen( $result[ $search_data->field_index ] ) < 1 )
                            $result[ $search_data->field_index ] = '(No Title)';

                        if ( 'admin_ajax_relationship' == $context ) {
                            $items[] = array(
                                'id' => $result[ $search_data->field_id ],
                                'text' => $result[ $search_data->field_index ],
                                'image' => ''
                            );
                        }
                        else
                            $data[ $result[ $search_data->field_id ] ] = $result[ $search_data->field_index ];

                        $ids[] = $result[ $search_data->field_id ];
                    }
                }
            }

			if ( $simple && 'admin_ajax_relationship' == $context ) {
				$found_data = array();

				foreach ( $data as $k => $v ) {
					if ( false !== stripos( $v, $data_params[ 'query' ] ) || false !== stripos( $k, $data_params[ 'query' ] ) ) {
						$found_data[ $k ] = $v;
					}
				}

				$data = $found_data;
			}
        }

        if ( 'admin_ajax_relationship' == $context ) {
            if ( empty( $items ) && !empty( $data ) ) {
                foreach ( $data as $k => $v ) {
                    $items[] = array(
                        'id' => $k,
                        'text' => $v,
                        'image' => ''
                    );
                }
            }

            return $items;
        }

        return $data;
    }

    /**
     * Handle autocomplete AJAX
     *
     * @since 2.3
     */
    public function admin_ajax_relationship () {
		pods_session_start();

        // Sanitize input
        $params = pods_unslash( (array) $_POST );

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
        $id = (int) $params->id;

        $limit = 15;

        if ( isset( $params->limit ) )
            $limit = (int) $params->limit;

        $page = 1;

        if ( isset( $params->page ) )
            $page = (int) $params->page;

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
            'options' => array_merge( $field, $field[ 'options' ] ), // Field options
            'pod' => $pod, // Pod data
            'id' => $id, // Item ID
            'context' => 'admin_ajax_relationship', // Data context
            'data_params' => $params,
            'page' => $page,
            'limit' => $limit
        );

        $pick_data = apply_filters( 'pods_field_pick_data_ajax', null, $field[ 'name' ], null, $field, $pod, $id );

        if ( null !== $pick_data )
            $items = $pick_data;
        else
            $items = $this->get_object_data( $object_params );

        if ( !empty( $items ) && isset( $items[ 0 ] ) && !is_array( $items[ 0 ] ) ) {
            $new_items = array();

            foreach ( $items as $id => $text ) {
                $new_items[] = array(
                    'id' => $id,
                    'text' => $text,
                    'image' => ''
                );
            }

            $items = $new_items;
        }

        $items = apply_filters( 'pods_field_pick_data_ajax_items', $items, $field[ 'name' ], null, $field, $pod, $id );

        $items = array(
            'results' => $items
        );

        wp_send_json( $items );

        die(); // KBAI!
    }

    /**
     * Data callback for Post Stati
     *
     * @param string $name The name of the field
     * @param string|array $value The value of the field
     * @param array $options Field options
     * @param array $pod Pod data
     * @param int $id Item ID
     *
     * @return array
     *
     * @since 2.3
     */
    public function data_post_stati ( $name = null, $value = null, $options = null, $pod = null, $id = null ) {
        $data = array();

        $post_stati = get_post_stati( array(), 'objects' );

        foreach ( $post_stati as $post_status ) {
            $data[ $post_status->name ] = $post_status->label;
        }

        return apply_filters( 'pods_form_ui_field_pick_' . __FUNCTION__, $data, $name, $value, $options, $pod, $id );
    }

    /**
     * Data callback for User Roles
     *
     * @param string $name The name of the field
     * @param string|array $value The value of the field
     * @param array $options Field options
     * @param array $pod Pod data
     * @param int $id Item ID
     *
     * @return array
     *
     * @since 2.3
     */
    public function data_roles ( $name = null, $value = null, $options = null, $pod = null, $id = null ) {
        $data = array();

        global $wp_roles;

        foreach ( $wp_roles->role_objects as $key => $role ) {
            $data[ $key ] = $wp_roles->role_names[ $key ];
        }

        return apply_filters( 'pods_form_ui_field_pick_' . __FUNCTION__, $data, $name, $value, $options, $pod, $id );
    }

    /**
     * Data callback for User Capabilities
     *
     * @param string $name The name of the field
     * @param string|array $value The value of the field
     * @param array $options Field options
     * @param array $pod Pod data
     * @param int $id Item ID
     *
     * @return array
     *
     * @since 2.3
     */
    public function data_capabilities ( $name = null, $value = null, $options = null, $pod = null, $id = null ) {
        $data = array();

        global $wp_roles;

        $default_caps = array(
            'activate_plugins',
            'add_users',
            'create_users',
            'delete_others_pages',
            'delete_others_posts',
            'delete_pages',
            'delete_plugins',
            'delete_posts',
            'delete_private_pages',
            'delete_private_posts',
            'delete_published_pages',
            'delete_published_posts',
            'delete_users',
            'edit_dashboard',
            'edit_files',
            'edit_others_pages',
            'edit_others_posts',
            'edit_pages',
            'edit_plugins',
            'edit_posts',
            'edit_private_pages',
            'edit_private_posts',
            'edit_published_pages',
            'edit_published_posts',
            'edit_theme_options',
            'edit_themes',
            'edit_users',
            'import',
            'install_plugins',
            'install_themes',
            'list_users',
            'manage_categories',
            'manage_links',
            'manage_options',
            'moderate_comments',
            'promote_users',
            'publish_pages',
            'publish_posts',
            'read',
            'read_private_pages',
            'read_private_posts',
            'remove_users',
            'switch_themes',
            'unfiltered_html',
            'unfiltered_upload',
            'update_core',
            'update_plugins',
            'update_themes',
            'upload_files'
        );

        $role_caps = array();

        foreach ( $wp_roles->role_objects as $key => $role ) {
            if ( is_array( $role->capabilities ) ) {
                foreach ( $role->capabilities as $cap => $grant ) {
                    $role_caps[ $cap ] = $cap;
                }
            }
        }

        $role_caps = array_unique( $role_caps );

        $capabilities = array_merge( $default_caps, $role_caps );

        // To support Members filters
        $capabilities = apply_filters( 'members_get_capabilities', $capabilities );

        $capabilities = apply_filters( 'pods_roles_get_capabilities', $capabilities );

        sort( $capabilities );

        $capabilities = array_unique( $capabilities );

        global $wp_roles;

        foreach ( $capabilities as $capability ) {
            $data[ $capability ] = $capability;
        }

        return apply_filters( 'pods_form_ui_field_pick_' . __FUNCTION__, $data, $name, $value, $options, $pod, $id );
    }

    /**
     * Data callback for Image Sizes
     *
     * @param string $name The name of the field
     * @param string|array $value The value of the field
     * @param array $options Field options
     * @param array $pod Pod data
     * @param int $id Item ID
     *
     * @return array
     *
     * @since 2.3
     */
    public function data_image_sizes ( $name = null, $value = null, $options = null, $pod = null, $id = null ) {
        $data = array();

        $image_sizes = get_intermediate_image_sizes();

        foreach ( $image_sizes as $image_size ) {
            $data[ $image_size ] = ucwords( str_replace( '-', ' ', $image_size ) );
        }

        return apply_filters( 'pods_form_ui_field_pick_' . __FUNCTION__, $data, $name, $value, $options, $pod, $id );
    }

    /**
     * Data callback for Countries
     *
     * @param string $name The name of the field
     * @param string|array $value The value of the field
     * @param array $options Field options
     * @param array $pod Pod data
     * @param int $id Item ID
     *
     * @return array
     *
     * @since 2.3
     */
    public function data_countries ( $name = null, $value = null, $options = null, $pod = null, $id = null ) {
        $data = array(
            'AF' => __( 'Afghanistan' ),
            'AL' => __( 'Albania' ),
            'DZ' => __( 'Algeria' ),
            'AS' => __( 'American Samoa' ),
            'AD' => __( 'Andorra' ),
            'AO' => __( 'Angola' ),
            'AI' => __( 'Anguilla' ),
            'AQ' => __( 'Antarctica' ),
            'AG' => __( 'Antigua and Barbuda' ),
            'AR' => __( 'Argentina' ),
            'AM' => __( 'Armenia' ),
            'AW' => __( 'Aruba' ),
            'AU' => __( 'Australia' ),
            'AT' => __( 'Austria' ),
            'AZ' => __( 'Azerbaijan' ),
            'BS' => __( 'Bahamas' ),
            'BH' => __( 'Bahrain' ),
            'BD' => __( 'Bangladesh' ),
            'BB' => __( 'Barbados' ),
            'BY' => __( 'Belarus' ),
            'BE' => __( 'Belgium' ),
            'BZ' => __( 'Belize' ),
            'BJ' => __( 'Benin' ),
            'BM' => __( 'Bermuda' ),
            'BT' => __( 'Bhutan' ),
            'BO' => __( 'Bolivia' ),
            'BA' => __( 'Bosnia and Herzegovina' ),
            'BW' => __( 'Botswana' ),
            'BV' => __( 'Bouvet Island' ),
            'BR' => __( 'Brazil' ),
            'BQ' => __( 'British Antarctic Territory' ),
            'IO' => __( 'British Indian Ocean Territory' ),
            'VG' => __( 'British Virgin Islands' ),
            'BN' => __( 'Brunei' ),
            'BG' => __( 'Bulgaria' ),
            'BF' => __( 'Burkina Faso' ),
            'BI' => __( 'Burundi' ),
            'KH' => __( 'Cambodia' ),
            'CM' => __( 'Cameroon' ),
            'CA' => __( 'Canada' ),
            'CT' => __( 'Canton and Enderbury Islands' ),
            'CV' => __( 'Cape Verde' ),
            'KY' => __( 'Cayman Islands' ),
            'CF' => __( 'Central African Republic' ),
            'TD' => __( 'Chad' ),
            'CL' => __( 'Chile' ),
            'CN' => __( 'China' ),
            'CX' => __( 'Christmas Island' ),
            'CC' => __( 'Cocos [Keeling] Islands' ),
            'CO' => __( 'Colombia' ),
            'KM' => __( 'Comoros' ),
            'CG' => __( 'Congo - Brazzaville' ),
            'CD' => __( 'Congo - Kinshasa' ),
            'CK' => __( 'Cook Islands' ),
            'CR' => __( 'Costa Rica' ),
            'HR' => __( 'Croatia' ),
            'CU' => __( 'Cuba' ),
            'CY' => __( 'Cyprus' ),
            'CZ' => __( 'Czech Republic' ),
            'CI' => __( 'Côte d’Ivoire' ),
            'DK' => __( 'Denmark' ),
            'DJ' => __( 'Djibouti' ),
            'DM' => __( 'Dominica' ),
            'DO' => __( 'Dominican Republic' ),
            'NQ' => __( 'Dronning Maud Land' ),
            'DD' => __( 'East Germany' ),
            'EC' => __( 'Ecuador' ),
            'EG' => __( 'Egypt' ),
            'SV' => __( 'El Salvador' ),
            'GQ' => __( 'Equatorial Guinea' ),
            'ER' => __( 'Eritrea' ),
            'EE' => __( 'Estonia' ),
            'ET' => __( 'Ethiopia' ),
            'FK' => __( 'Falkland Islands' ),
            'FO' => __( 'Faroe Islands' ),
            'FJ' => __( 'Fiji' ),
            'FI' => __( 'Finland' ),
            'FR' => __( 'France' ),
            'GF' => __( 'French Guiana' ),
            'PF' => __( 'French Polynesia' ),
            'TF' => __( 'French Southern Territories' ),
            'FQ' => __( 'French Southern and Antarctic Territories' ),
            'GA' => __( 'Gabon' ),
            'GM' => __( 'Gambia' ),
            'GE' => __( 'Georgia' ),
            'DE' => __( 'Germany' ),
            'GH' => __( 'Ghana' ),
            'GI' => __( 'Gibraltar' ),
            'GR' => __( 'Greece' ),
            'GL' => __( 'Greenland' ),
            'GD' => __( 'Grenada' ),
            'GP' => __( 'Guadeloupe' ),
            'GU' => __( 'Guam' ),
            'GT' => __( 'Guatemala' ),
            'GG' => __( 'Guernsey' ),
            'GN' => __( 'Guinea' ),
            'GW' => __( 'Guinea-Bissau' ),
            'GY' => __( 'Guyana' ),
            'HT' => __( 'Haiti' ),
            'HM' => __( 'Heard Island and McDonald Islands' ),
            'HN' => __( 'Honduras' ),
            'HK' => __( 'Hong Kong SAR China' ),
            'HU' => __( 'Hungary' ),
            'IS' => __( 'Iceland' ),
            'IN' => __( 'India' ),
            'ID' => __( 'Indonesia' ),
            'IR' => __( 'Iran' ),
            'IQ' => __( 'Iraq' ),
            'IE' => __( 'Ireland' ),
            'IM' => __( 'Isle of Man' ),
            'IL' => __( 'Israel' ),
            'IT' => __( 'Italy' ),
            'JM' => __( 'Jamaica' ),
            'JP' => __( 'Japan' ),
            'JE' => __( 'Jersey' ),
            'JT' => __( 'Johnston Island' ),
            'JO' => __( 'Jordan' ),
            'KZ' => __( 'Kazakhstan' ),
            'KE' => __( 'Kenya' ),
            'KI' => __( 'Kiribati' ),
            'KW' => __( 'Kuwait' ),
            'KG' => __( 'Kyrgyzstan' ),
            'LA' => __( 'Laos' ),
            'LV' => __( 'Latvia' ),
            'LB' => __( 'Lebanon' ),
            'LS' => __( 'Lesotho' ),
            'LR' => __( 'Liberia' ),
            'LY' => __( 'Libya' ),
            'LI' => __( 'Liechtenstein' ),
            'LT' => __( 'Lithuania' ),
            'LU' => __( 'Luxembourg' ),
            'MO' => __( 'Macau SAR China' ),
            'MK' => __( 'Macedonia' ),
            'MG' => __( 'Madagascar' ),
            'MW' => __( 'Malawi' ),
            'MY' => __( 'Malaysia' ),
            'MV' => __( 'Maldives' ),
            'ML' => __( 'Mali' ),
            'MT' => __( 'Malta' ),
            'MH' => __( 'Marshall Islands' ),
            'MQ' => __( 'Martinique' ),
            'MR' => __( 'Mauritania' ),
            'MU' => __( 'Mauritius' ),
            'YT' => __( 'Mayotte' ),
            'FX' => __( 'Metropolitan France' ),
            'MX' => __( 'Mexico' ),
            'FM' => __( 'Micronesia' ),
            'MI' => __( 'Midway Islands' ),
            'MD' => __( 'Moldova' ),
            'MC' => __( 'Monaco' ),
            'MN' => __( 'Mongolia' ),
            'ME' => __( 'Montenegro' ),
            'MS' => __( 'Montserrat' ),
            'MA' => __( 'Morocco' ),
            'MZ' => __( 'Mozambique' ),
            'MM' => __( 'Myanmar [Burma]' ),
            'NA' => __( 'Namibia' ),
            'NR' => __( 'Nauru' ),
            'NP' => __( 'Nepal' ),
            'NL' => __( 'Netherlands' ),
            'AN' => __( 'Netherlands Antilles' ),
            'NT' => __( 'Neutral Zone' ),
            'NC' => __( 'New Caledonia' ),
            'NZ' => __( 'New Zealand' ),
            'NI' => __( 'Nicaragua' ),
            'NE' => __( 'Niger' ),
            'NG' => __( 'Nigeria' ),
            'NU' => __( 'Niue' ),
            'NF' => __( 'Norfolk Island' ),
            'KP' => __( 'North Korea' ),
            'VD' => __( 'North Vietnam' ),
            'MP' => __( 'Northern Mariana Islands' ),
            'NO' => __( 'Norway' ),
            'OM' => __( 'Oman' ),
            'PC' => __( 'Pacific Islands Trust Territory' ),
            'PK' => __( 'Pakistan' ),
            'PW' => __( 'Palau' ),
            'PS' => __( 'Palestinian Territories' ),
            'PA' => __( 'Panama' ),
            'PZ' => __( 'Panama Canal Zone' ),
            'PG' => __( 'Papua New Guinea' ),
            'PY' => __( 'Paraguay' ),
            'YD' => __( "People's Democratic Republic of Yemen" ),
            'PE' => __( 'Peru' ),
            'PH' => __( 'Philippines' ),
            'PN' => __( 'Pitcairn Islands' ),
            'PL' => __( 'Poland' ),
            'PT' => __( 'Portugal' ),
            'PR' => __( 'Puerto Rico' ),
            'QA' => __( 'Qatar' ),
            'RO' => __( 'Romania' ),
            'RU' => __( 'Russia' ),
            'RW' => __( 'Rwanda' ),
            'RE' => __( 'Réunion' ),
            'BL' => __( 'Saint Barthélemy' ),
            'SH' => __( 'Saint Helena' ),
            'KN' => __( 'Saint Kitts and Nevis' ),
            'LC' => __( 'Saint Lucia' ),
            'MF' => __( 'Saint Martin' ),
            'PM' => __( 'Saint Pierre and Miquelon' ),
            'VC' => __( 'Saint Vincent and the Grenadines' ),
            'WS' => __( 'Samoa' ),
            'SM' => __( 'San Marino' ),
            'SA' => __( 'Saudi Arabia' ),
            'SN' => __( 'Senegal' ),
            'RS' => __( 'Serbia' ),
            'CS' => __( 'Serbia and Montenegro' ),
            'SC' => __( 'Seychelles' ),
            'SL' => __( 'Sierra Leone' ),
            'SG' => __( 'Singapore' ),
            'SK' => __( 'Slovakia' ),
            'SI' => __( 'Slovenia' ),
            'SB' => __( 'Solomon Islands' ),
            'SO' => __( 'Somalia' ),
            'ZA' => __( 'South Africa' ),
            'GS' => __( 'South Georgia and the South Sandwich Islands' ),
            'KR' => __( 'South Korea' ),
            'ES' => __( 'Spain' ),
            'LK' => __( 'Sri Lanka' ),
            'SD' => __( 'Sudan' ),
            'SR' => __( 'Suriname' ),
            'SJ' => __( 'Svalbard and Jan Mayen' ),
            'SZ' => __( 'Swaziland' ),
            'SE' => __( 'Sweden' ),
            'CH' => __( 'Switzerland' ),
            'SY' => __( 'Syria' ),
            'ST' => __( 'São Tomé and Príncipe' ),
            'TW' => __( 'Taiwan' ),
            'TJ' => __( 'Tajikistan' ),
            'TZ' => __( 'Tanzania' ),
            'TH' => __( 'Thailand' ),
            'TL' => __( 'Timor-Leste' ),
            'TG' => __( 'Togo' ),
            'TK' => __( 'Tokelau' ),
            'TO' => __( 'Tonga' ),
            'TT' => __( 'Trinidad and Tobago' ),
            'TN' => __( 'Tunisia' ),
            'TR' => __( 'Turkey' ),
            'TM' => __( 'Turkmenistan' ),
            'TC' => __( 'Turks and Caicos Islands' ),
            'TV' => __( 'Tuvalu' ),
            'UM' => __( 'U.S. Minor Outlying Islands' ),
            'PU' => __( 'U.S. Miscellaneous Pacific Islands' ),
            'VI' => __( 'U.S. Virgin Islands' ),
            'UG' => __( 'Uganda' ),
            'UA' => __( 'Ukraine' ),
            'SU' => __( 'Union of Soviet Socialist Republics' ),
            'AE' => __( 'United Arab Emirates' ),
            'GB' => __( 'United Kingdom' ),
            'US' => __( 'United States' ),
            'ZZ' => __( 'Unknown or Invalid Region' ),
            'UY' => __( 'Uruguay' ),
            'UZ' => __( 'Uzbekistan' ),
            'VU' => __( 'Vanuatu' ),
            'VA' => __( 'Vatican City' ),
            'VE' => __( 'Venezuela' ),
            'VN' => __( 'Vietnam' ),
            'WK' => __( 'Wake Island' ),
            'WF' => __( 'Wallis and Futuna' ),
            'EH' => __( 'Western Sahara' ),
            'YE' => __( 'Yemen' ),
            'ZM' => __( 'Zambia' ),
            'ZW' => __( 'Zimbabwe' ),
            'AX' => __( 'Åland Islands' )
        );

        return apply_filters( 'pods_form_ui_field_pick_' . __FUNCTION__, $data, $name, $value, $options, $pod, $id );
    }

    /**
     * Data callback for US States
     *
     * @param string $name The name of the field
     * @param string|array $value The value of the field
     * @param array $options Field options
     * @param array $pod Pod data
     * @param int $id Item ID
     *
     * @return array
     *
     * @since 2.3
     */
    public function data_us_states ( $name = null, $value = null, $options = null, $pod = null, $id = null ) {
        $data = array(
            'AL' => __( 'Alabama' ),
            'AK' => __( 'Alaska' ),
            'AZ' => __( 'Arizona' ),
            'AR' => __( 'Arkansas' ),
            'CA' => __( 'California' ),
            'CO' => __( 'Colorado' ),
            'CT' => __( 'Connecticut' ),
            'DE' => __( 'Delaware' ),
            'DC' => __( 'District Of Columbia' ),
            'FL' => __( 'Florida' ),
            'GA' => __( 'Georgia' ),
            'HI' => __( 'Hawaii' ),
            'ID' => __( 'Idaho' ),
            'IL' => __( 'Illinois' ),
            'IN' => __( 'Indiana' ),
            'IA' => __( 'Iowa' ),
            'KS' => __( 'Kansas' ),
            'KY' => __( 'Kentucky' ),
            'LA' => __( 'Louisiana' ),
            'ME' => __( 'Maine' ),
            'MD' => __( 'Maryland' ),
            'MA' => __( 'Massachusetts' ),
            'MI' => __( 'Michigan' ),
            'MN' => __( 'Minnesota' ),
            'MS' => __( 'Mississippi' ),
            'MO' => __( 'Missouri' ),
            'MT' => __( 'Montana' ),
            'NE' => __( 'Nebraska' ),
            'NV' => __( 'Nevada' ),
            'NH' => __( 'New Hampshire' ),
            'NJ' => __( 'New Jersey' ),
            'NM' => __( 'New Mexico' ),
            'NY' => __( 'New York' ),
            'NC' => __( 'North Carolina' ),
            'ND' => __( 'North Dakota' ),
            'OH' => __( 'Ohio' ),
            'OK' => __( 'Oklahoma' ),
            'OR' => __( 'Oregon' ),
            'PA' => __( 'Pennsylvania' ),
            'RI' => __( 'Rhode Island' ),
            'SC' => __( 'South Carolina' ),
            'SD' => __( 'South Dakota' ),
            'TN' => __( 'Tennessee' ),
            'TX' => __( 'Texas' ),
            'UT' => __( 'Utah' ),
            'VT' => __( 'Vermont' ),
            'VA' => __( 'Virginia' ),
            'WA' => __( 'Washington' ),
            'WV' => __( 'West Virginia' ),
            'WI' => __( 'Wisconsin' ),
            'WY' => __( 'Wyoming' )
        );

        return apply_filters( 'pods_form_ui_field_pick_' . __FUNCTION__, $data, $name, $value, $options, $pod, $id );
    }

    /**
     * Data callback for US States
     *
     * @param string $name The name of the field
     * @param string|array $value The value of the field
     * @param array $options Field options
     * @param array $pod Pod data
     * @param int $id Item ID
     *
     * @return array
     *
     * @since 2.3
     */
    public function data_days_of_week ( $name = null, $value = null, $options = null, $pod = null, $id = null ) {

		/**
		 * @var WP_Locale
		 */
		global $wp_locale;

		return $wp_locale->weekday;

    }

    /**
     * Data callback for US States
     *
     * @param string $name The name of the field
     * @param string|array $value The value of the field
     * @param array $options Field options
     * @param array $pod Pod data
     * @param int $id Item ID
     *
     * @return array
     *
     * @since 2.3
     */
    public function data_months_of_year ( $name = null, $value = null, $options = null, $pod = null, $id = null ) {

		/**
		 * @var WP_Locale
		 */
		global $wp_locale;

		return $wp_locale->month;

    }
}
