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
     * Do things like register/enqueue scripts and stylesheets
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
                    ) + ( ( defined( 'PODS_DEVELOPER' ) && PODS_DEVELOPER ) ? array( 'flexible' => __( 'Flexible', 'pods' ) ) : array() )
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
                    ) + ( ( defined( 'PODS_DEVELOPER' ) && PODS_DEVELOPER ) ? array( 'flexible' => __( 'Flexible', 'pods' ) ) : array() )
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
            'pick_display' => array(
                'label' => __( 'Display Field in Selection List', 'pods' ),
                'help' => __( 'Provide the name of a field on the related object to reference, example: {@post_title}', 'pods' ),
                'excludes-on' => array( 'pick_object' => 'custom-simple' ),
                'default' => '',
                'type' => 'text'
            ),
            'pick_where' => array(
                'label' => __( 'Customized <em>WHERE</em>', 'pods' ),
                'help' => __( 'help', 'pods' ),
                'excludes-on' => array( 'pick_object' => 'custom-simple' ),
                'default' => '',
                'type' => 'text'
            ),
            'pick_orderby' => array(
                'label' => __( 'Customized <em>ORDER BY</em>', 'pods' ),
                'help' => __( 'help', 'pods' ),
                'excludes-on' => array( 'pick_object' => 'custom-simple' ),
                'default' => '',
                'type' => 'text'
            ),
            'pick_groupby' => array(
                'label' => __( 'Customized <em>GROUP BY</em>', 'pods' ),
                'help' => __( 'help', 'pods' ),
                'excludes-on' => array( 'pick_object' => 'custom-simple' ),
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
     * Define the current field's schema for DB table storage
     *
     * @param array $options
     *
     * @return array
     * @since 2.0.0
     */
    public function schema ( $options = null ) {
        $schema = false;

        if ( 'custom-simple' == pods_var( 'pick_object', $options ) )
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
    public function data ( $name, $value = null, $options = null, $pod = null, $id = null ) {
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
            $options[ 'table_info' ] = pods_api()->get_table_info( pods_var( 'pick_object', $options ), pods_var( 'pick_val', $options ) );

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

            if ( $autocomplete )
                $params[ 'limit' ] = apply_filters( 'pods_form_ui_field_pick_autocomplete_limit', 30, $name, $value, $options, $pod, $id );

            $results = $search_data->select( $params );

            if ( !empty( $results ) && ( !$autocomplete || $search_data->total_found() <= $params[ 'limit' ] ) ) {
                foreach ( $results as $result ) {
                    $result = get_object_vars( $result );

                    $result[ $search_data->field_index ] = trim( $result[ $search_data->field_index ] );

                    if ( strlen( $result[ $search_data->field_index ] ) < 1 )
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

        if ( 'custom-simple' == pods_var( 'pick_object', $options ) ) {
            $simple_data = array();

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

                        $simple_data[ $custom_value ] = $custom_label;
                    }
                }
                else
                    $simple_data = $custom;
            }

            $simple = false;
            $key = 0;

            if ( !is_array( $value ) && !empty( $value ) )
                $simple = @json_decode( $value, true );

            if ( is_array( $simple ) )
                $value = $simple;

            if ( is_array( $value ) ) {
                if ( !empty( $simple_data ) ) {
                    $val = array();

                    foreach ( $value as $k => $v ) {
                        if ( isset( $simple_data[ $v ] ) ) {
                            if ( false === $raw ) {
                                $k = $v;
                                $v = $simple_data[ $v ];
                            }

                            $val[ $k ] = $v;
                        }
                    }

                    $value = $val;
                }
            }
            elseif ( isset( $simple_data[ $value ] ) && false === $raw ) {
                $key = $value;
                $value = $simple_data[ $value ];
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

        $data = null;

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
        elseif ( '' != pods_var( 'pick_object', $options, '' ) && array() == pods_var_raw( 'data', $options, array(), null, true ) ) {
            $options[ 'table_info' ] = pods_api()->get_table_info( pods_var( 'pick_object', $options ), pods_var( 'pick_val', $options ) );

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

                        if ( 'table' == $options[ 'table_info' ][ 'pod' ][ 'storage' ] && !in_array( $options[ 'table_info' ][ 'pod' ][ 'type' ], array(
                            'pod',
                            'table'
                        ) )
                        )
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

                    if ( strlen( $result[ $search_data->field_index ] ) < 1 )
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

        $data = apply_filters( 'pods_field_pick_value_data', $data, $pod, $field, $value );

        $data = pods_serial_comma( $data );

        return $data;
    }
}
