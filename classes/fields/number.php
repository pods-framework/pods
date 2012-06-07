<?php
class PodsField_Number extends PodsField {

    /**
     * Field Type Identifier
     *
     * @var string
     * @since 2.0.0
     */
    protected static $type = 'number';

    /**
     * Field Type Label
     *
     * @var string
     * @since 2.0.0
     */
    protected static $label = 'Number';

    /**
     * Currency Formats
     *
     * @var array
     * @since 2.0.0
     */
    protected static $currencies = array(
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
                )
            ),
            'number_format_currency_sign' => array(
                'label' => __( 'Currency Sign', 'pods' ),
                'depends-on' => array( 'number_format_type' => 'currency' ),
                'default' => 'usd',
                'type' => 'pick',
                'data' =>
                    apply_filters( 'pods_form_ui_field_number_currency_options',
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
            ),
            'number_range' => array(
                'label' => __( 'Number Range', 'pods' ),
                'depends-on' => array( 'number_format_type' => 'range' ),
                'default' => '0,100',
                'type' => 'range'
            ),
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
            'max_length' => 255,
            'size' => 'medium'
        );
        return $options;
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