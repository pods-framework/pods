<?php
/**
 *
 */
class PodsField_Pick extends PodsField {

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
     * @param array $options
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
                    )
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
                    )
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
                'help' => __( 'You can use {@magic_tags} to reference field names on the related object.', 'pods' ),
                'excludes-on' => array( 'pick_object' => 'custom-simple' ),
                'default' => '{@name}',
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
            ),
            'pick_size' => array(
                'label' => __( 'Field Size', 'pods' ),
                'default' => 'medium',
                'type' => 'pick',
                'data' => array(
                    'small' => __( 'Small', 'pods' ),
                    'medium' => __( 'Medium', 'pods' ),
                    'large' => __( 'Large', 'pods' )
                )
            )
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
        return $value;
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

        if ( 'custom-simple' == pods_var( 'pick_object', $options ) && !empty( $custom ) ) {
            if ( !is_array( $custom ) )
                $custom = explode( "\n", $custom );

            $options[ 'data' ] = array();

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

                $options[ 'data' ][ $custom_value ] = $custom_label;
            }
        }
        elseif ( '' != pods_var( 'pick_object', $options, '' ) && array() == pods_var( 'data', $options, array() ) ) {
            $options[ 'data' ] = array();

            if ( 'single' == pods_var( 'pick_format_type', $options ) && 'dropdown' == pods_var( 'pick_format_single', $options ) )
                $options[ 'data' ] = array( '' => __( '-- Select One --', 'pods' ) );

            $options[ 'table_info' ] = pods_api()->get_table_info( pods_var( 'pick_object', $options ), pods_var( 'pick_val', $options ) );

            $data = pods_data();
            $data->table = $options[ 'table_info' ][ 'table' ];
            $data->join = $options[ 'table_info' ][ 'join' ];
            $data->field_id = $options[ 'table_info' ][ 'field_id' ];
            $data->field_index = $options[ 'table_info' ][ 'field_index' ];
            $data->where = $options[ 'table_info' ][ 'where' ];
            $data->orderby = $options[ 'table_info' ][ 'orderby' ];

            $params = array(
                'select' => "`t`.`{$data->field_id}`, `t`.`{$data->field_index}`",
                'table' => $data->table,
                'where' => pods_var( 'pick_where', $options, null, null, true ),
                'orderby' => pods_var( 'pick_orderby', $options, null, null, true ),
                'groupby' => pods_var( 'pick_groupby', $options, null, null, true )
            );

            if ( isset( $options[ 'table_info' ][ 'pod' ] ) && !empty( $options[ 'table_info' ][ 'pod' ] ) ) {
                $data->pod = $options[ 'table_info' ][ 'pod' ][ 'name' ];
                $data->fields = $options[ 'table_info' ][ 'pod' ][ 'fields' ];
            }

            $autocomplete = false;

            if ( 'single' == pods_var( 'pick_format_type', $options ) && 'autocomplete' == pods_var( 'pick_format_single', $options ) )
                $autocomplete = true;
            elseif ( 'multi' == pods_var( 'pick_format_type', $options ) && 'autocomplete' == pods_var( 'pick_format_multi', $options ) )
                $autocomplete = true;

            if ( $autocomplete )
                $params[ 'limit' ] = apply_filters( 'pods_form_ui_field_pick_autocomplete_limit', 30, $name, $value, $options, $pod, $id  );

            $results = $data->select( $params );

            if ( !empty( $results ) && ( !$autocomplete || $data->total_found() <= $params[ 'limit' ] ) ) {
                foreach ( $results as $result ) {
                    $result = get_object_vars( $result );

                    $options[ 'data' ][ $result[ $data->field_id ] ] = $result[ $data->field_index ];
                }
            }
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
     * Build regex necessary for JS validation
     *
     * @param mixed $value
     * @param string $name
     * @param array $options
     * @param string $pod
     * @param int $id
     *
     * @since 2.0.0
     */
    public function regex ( $value = null, $name = null, $options = null, $pod = null, $id = null ) {
        return false;
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
     * @since 2.0.0
     */
    public function validate ( &$value, $name = null, $options = null, $fields = null, $pod = null, $id = null, $params = null ) {
        return true;
    }

    /**
     * Change the value or perform actions after validation but before saving to the DB
     *
     * @param mixed $value
     * @param int $id
     * @param string $name
     * @param array $options
     * @param array $fields
     * @param array $pod
     * @param object $params
     *
     * @since 2.0.0
     */
    public function pre_save ( $value, $id = null, $name = null, $options = null, $fields = null, $pod = null, $params = null ) {
        return $value;
    }

    /**
     * Perform actions after saving to the DB
     *
     * @param mixed $value
     * @param int $id
     * @param string $name
     * @param array $options
     * @param array $fields
     * @param array $pod
     * @param object $params
     *
     * @since 2.0.0
     */
    public function post_save ( $value, $id = null, $name = null, $options = null, $fields = null, $pod = null, $params = null ) {

    }

    /**
     * Perform actions before deleting from the DB
     *
     * @param int $id
     * @param string $name
     * @param array $options
     * @param object $pod
     *
     * @since 2.0.0
     */
    public function pre_delete ( $id = null, $name = null, $options = null, $pod = null ) {

    }

    /**
     * Perform actions after deleting from the DB
     *
     * @param int $id
     * @param string $name
     * @param array $options
     * @param object $pod
     *
     * @since 2.0.0
     */
    public function post_delete ( $id = null, $name = null, $options = null, $pod = null ) {

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

    }
}