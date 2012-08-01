<?php
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
                'default' => 255,
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
        $schema = 'DECIMAL(12,2)';

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
            $format = '%01.' . (int) $options[ 'number_decimals' ] . 'f';

        return $format;
    }

    /**
     * Change the way the value of the field is displayed with Pods::get
     *
     * @param mixed $value
     * @param string $name
     * @param array $options
     * @param array $fields
     * @param string $pod
     * @param int $id
     *
     * @since 2.0.0
     */
    public function display ( &$value, $name, $options, $fields, &$pod, $id ) {
        $thousands = ',';
        $dot = '.';
        if ( '9999.99' == $options[ 'number_format' ] )
            $thousands = '';
        elseif ( '9999,99' == $options[ 'number_format' ] ) {
            $thousands = '';
            $dot = ',';
        }
        elseif ( '9.999,99' == $options[ 'number_format' ] ) {
            $thousands = '.';
            $dot = ',';
        }

        if ( 'i18n' == $options[ 'number_format' ] )
            $value = number_format_i18n( $value, (int) $options[ 'number_decimals' ] );
        else
            $value = number_format( $value, (int) $options[ 'number_decimals' ], $dot, $thousands );

        if ( isset( $options[ 'number_format_type' ] ) && 'currency' == $options[ 'number_format_type' ] ) {
            $currency = 'usd';
            if ( isset( $options[ 'number_format_currency_sign' ] ) && isset( self::$currencies[ $options[ 'number_format_currency_sign' ] ] ) )
                $currency = $options[ 'number_format_currency_sign' ];

            $currency_sign = self::$currencies[ $currency ];

            $placement = 'before';
            if ( isset( $options[ 'number_format_currency_placement' ] ) )
                $placement = $options[ 'number_format_currency_placement' ];

            if ( 'before' == $placement )
                $value = $currency_sign . $value;
            elseif ( 'after' == $placement )
                $value .= $currency_sign;
            elseif ( 'beforeaftercode' == $placement )
                $value = $currency_sign . $value . ' ' . strtoupper( $currency );
        }
    }

    /**
     * Customize output of the form field
     *
     * @param string $name
     * @param string $value
     * @param array $options
     * @param string $pod
     * @param int $id
     *
     * @since 2.0.0
     */
    public function input ( $name, $value = null, $options = null, $pod = null, $id = null ) {
        $options = (array) $options;

        if ( is_array( $value ) )
            $value = implode( '', $value );

        $field_type = 'number';

        if ( 'range' == $options[ 'number_format_type' ] )
            $field_type = 'range';

        pods_view( PODS_DIR . 'ui/fields/' . $field_type . '.php', compact( array_keys( get_defined_vars() ) ) );
    }

    /**
     * Build regex necessary for JS validation
     *
     * @param string $name
     * @param string $value
     * @param array $options
     * @param string $pod
     * @param int $id
     *
     * @since 2.0.0
     */
    public function regex ( $name, $value = null, $options = null, &$pod = null, $id = null ) {
        $thousands = ',';
        $dot = '.';
        if ( '9999.99' == $options[ 'number_format' ] )
            $thousands = '';
        elseif ( '9999,99' == $options[ 'number_format' ] ) {
            $thousands = '';
            $dot = ',';
        }
        elseif ( '9.999,99' == $options[ 'number_format' ] ) {
            $thousands = '.';
            $dot = ',';
        }

        return '[0-9' . implode( '\\', array_filter( array( $dot, $thousands ) ) ) . ']+';
    }

    /**
     * Change the value or perform actions after validation but before saving to the DB
     *
     * @param string $value
     * @param string $name
     * @param array $options
     * @param array $data
     * @param object $api
     * @param string $pod
     * @param int $id
     *
     * @since 2.0.0
     */
    public function pre_save ( &$value, $name, $options, $data, &$api, &$pod, $id = false ) {
        $decimals = 0;
        if ( 0 < (int) $options[ 'number_decimals' ] )
            $decimals = (int) $options[ 'number_decimals' ];

        $value = number_format( (float) $value, $decimals, '.', '' );
    }
}