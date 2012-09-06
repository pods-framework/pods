<?php
/**
 *
 */
class PodsField_Number extends PodsField {

    /**
     * Field Type Identifier
     *
     * @var string
     * @since 2.0.0
     */
    public static $type = 'number';

    /**
     * Field Type Label
     *
     * @var string
     * @since 2.0.0
     */
    public static $label = 'Number';

    /**
     * Field Type Preparation
     *
     * @var string
     * @since 2.0.0
     */
    public static $prepare = '%d';

    /**
     * Currency Formats
     *
     * @var array
     * @since 2.0.0
     */
    public static $currencies = array(
        'usd' => '$',
        'cad' => '$'
    );

    /**
     * Do things like register/enqueue scripts and stylesheets
     *
     * @since 2.0.0
     */
    public function __construct () {
        self::$currencies = apply_filters( 'pods_form_ui_field_number_currencies', self::$currencies );
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
            'number_format_type' => array(
                'label' => __( 'Format Type', 'pods' ),
                'default' => 'plain',
                'type' => 'pick',
                'data' => array(
                    'plain' => __( 'Plain Number', 'pods' ),
                    'currency' => __( 'Currency', 'pods' )
                    //'range' => __( 'Range', 'pods' )
                ),
                'dependency' => true
            ),
            'number_format_currency_sign' => array(
                'label' => __( 'Currency Sign', 'pods' ),
                'depends-on' => array( 'number_format_type' => 'currency' ),
                'default' => 'usd',
                'type' => 'pick',
                'data' => apply_filters( 'pods_form_ui_field_number_currency_options',
                    array(
                        'usd' => '$ (USD)',
                        'cad' => '$ (CAD)'
                    )
                )
            ),
            'number_format_currency_placement' => array(
                'label' => __( 'Currency Placement', 'pods' ),
                'depends-on' => array( 'number_format_type' => 'currency' ),
                'default' => 'before',
                'type' => 'pick',
                'data' => array(
                    'before' => __( 'Before ($100)', 'pods' ),
                    'after' => __( 'After (100$)', 'pods' ),
                    'none' => __( 'None (100)', 'pods' ),
                    'beforeaftercode' => __( 'Before with Currency Code after ($100 USD)', 'pods' )
                )
            ),/*
            'number_range' => array(
                'label' => __( 'Number Range', 'pods' ),
                'depends-on' => array( 'number_format_type' => 'range' ),
                'default' => '0,100',
                'type' => 'range'
            ),*/
            'number_format' => array(
                'label' => __( 'Format', 'pods' ),
                'default' => 'i18n',
                'type' => 'pick',
                'data' => array(
                    'i18n' => __( 'Localized Default', 'pods' ),
                    '9,999.99' => '1,234.00',
                    '9999.99' => '1234.00',
                    '9.999,99' => '1.234,00',
                    '9999,99' => '1234,00'
                )
            ),
            'number_decimals' => array(
                'label' => __( 'Decimals', 'pods' ),
                'default' => 0,
                'type' => 'number'
            ),
            'number_max_length' => array(
                'label' => __( 'Maximum Length', 'pods' ),
                'default' => 12,
                'type' => 'number'
            ),
            'number_size' => array(
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
        $length = (int) pods_var( 'number_max_length', $options, 12, null, true );

        if ( $length < 1 )
            $length = 12;

        $decimals = (int) pods_var( 'number_decimals', $options, 0, null, true );

        if ( $decimals < 1 )
            $decimals = 0;

        $schema = 'DECIMAL(' . $length . ',' . $decimals . ')';

        return $schema;
    }

    /**
     * Define the current field's preparation for sprintf
     *
     * @param array $options
     *
     * @return array
     * @since 2.0.0
     */
    public function prepare ( $options = null ) {
        $format = self::$prepare;

        if ( 0 < (int) pods_var( 'number_decimals', $options, 0 ) )
            $format = '%01.' . (int) pods_var( 'number_decimals', $options ) . 'f';

        return $format;
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
        $value = $this->format( $value, $name, $options, $pod, $id );

        if ( 'currency' == pods_var( 'number_format_type', $options ) ) {
            $currency = 'usd';

            if ( isset( self::$currencies[ pods_var( 'number_format_currency_sign', $options, -1 ) ] ) )
                $currency = pods_var( 'number_format_currency_sign', $options );

            $currency_sign = self::$currencies[ $currency ];

            $placement = pods_var( 'number_format_currency_placement', $options, 'before', null, true );

            if ( 'before' == $placement )
                $value = $currency_sign . $value;
            elseif ( 'after' == $placement )
                $value .= $currency_sign;
            elseif ( 'beforeaftercode' == $placement )
                $value = $currency_sign . $value . ' ' . strtoupper( $currency );
        }

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
        $options = (array) $options;

        if ( is_array( $value ) )
            $value = implode( '', $value );

        $value = $this->pre_save( $value, $id, $name, $options, null, $pod );

        $field_type = 'number';

        if ( 'range' == pods_var( 'number_format_type', $options ) )
            $field_type = 'range';

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
        $thousands = ',';
        $dot = '.';

        if ( '9999.99' == pods_var( 'number_format', $options ) )
            $thousands = '';
        elseif ( '9999,99' == pods_var( 'number_format', $options ) ) {
            $thousands = '';
            $dot = ',';
        }
        elseif ( '9.999,99' == pods_var( 'number_format', $options ) ) {
            $thousands = '.';
            $dot = ',';
        }

        return '[0-9\\' . implode( '\\', array_filter( array( $dot, $thousands ) ) ) . ']+';
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
        $label = pods_var( 'label', $options, ucwords( str_replace( '_', ' ', $name ) ) );

        $thousands = ',';
        $dot = '.';

        if ( '9999,99' == pods_var( 'number_format', $options ) ) {
            $thousands = '.';
            $dot = ',';
        }
        elseif ( '9.999,99' == pods_var( 'number_format', $options ) ) {
            $thousands = '.';
            $dot = ',';
        }

        $check = str_replace( array( $thousands, $dot ), array( '', '.' ), $value );

        $check = preg_replace( '/[^0-9\.]/', '', $check );

        if ( !is_numeric( $check ) )
            return pods_error( sprintf( __( '%s is not numeric', 'pods' ), $label, $this ) );

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
        $thousands = ',';
        $dot = '.';

        if ( '9999,99' == pods_var( 'number_format', $options ) ) {
            $thousands = '.';
            $dot = ',';
        }
        elseif ( '9.999,99' == pods_var( 'number_format', $options ) ) {
            $thousands = '.';
            $dot = ',';
        }

        $value = str_replace( array( $thousands, $dot ), array( '', '.' ), $value );

        $value = preg_replace( '/[^0-9\.]/', '', $value );

        $decimals = pods_absint( (int) pods_var( 'number_decimals', $options, 0, null, true ) );

        $value = number_format( (float) $value, $decimals, '.', '' );

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
     * @param string $name
     * @param string $pod
     * @param int $id
     * @param object $api
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
     * @param array $pod
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

    /**
     * Reformat a number to the way the value of the field is displayed
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
    public function format ( $value = null, $name = null, $options = null, $pod = null, $id = null ) {
        $thousands = ',';
        $dot = '.';

        if ( '9999.99' == pods_var( 'number_format', $options ) )
            $thousands = '';
        elseif ( '9999,99' == pods_var( 'number_format', $options ) ) {
            $thousands = '';
            $dot = ',';
        }
        elseif ( '9.999,99' == pods_var( 'number_format', $options ) ) {
            $thousands = '.';
            $dot = ',';
        }

        if ( 'i18n' == pods_var( 'number_format', $options ) )
            $value = number_format_i18n( (float) $value, (int) pods_var( 'number_decimals', $options ) );
        else
            $value = number_format( (float) $value, (int) pods_var( 'number_decimals', $options ), $dot, $thousands );

        return $value;
    }
}
