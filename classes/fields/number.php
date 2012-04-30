<?php
class PodsField_Number extends PodsField {

    /**
     * Field Type Identifier
     *
     * @var string
     * @since 2.0.0
     */
    protected static $type = 'number';

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
                'default' => 'plain',
                'type' => 'pick',
                'values' => array(
                    'plain' => 'Plain Number',
                    'currency' => 'Currency'
                )
            ),
            'depends-1' => array(
                'on' => array( 'number_format_type' => 'currency' ),
                'fields' => array(
                    'number_format_currency_sign' => array(
                        'default' => 'usd',
                        'type' => 'pick',
                        'values' => apply_filters( 'pods_form_ui_field_number_currency_options', array(
                            'usd' => '$ (USD)',
                            'cad' => '$ (CAD)'
                        ) )
                    ),
                    'number_format_currency_placement' => array(
                        'default' => 'before',
                        'type' => 'pick',
                        'values' => array(
                            'before' => 'Before ($100)',
                            'after' => 'After (100$)',
                            'none' => 'None (100)',
                            'beforeaftercode' => 'Before with Currency Code after ($100 USD)'
                        )
                    )
                )
            ),
            'number_format' => array(
                'default' => 'i18n',
                'type' => 'pick',
                'values' => array(
                    'i18n' => 'Localized Default',
                    '9,999.99' => '1,234.00',
                    '9999.99' => '1234.00',
                    '9.999,99' => '1.234,00',
                    '9999,99' => '1234,00'
                )
            ),
            'number_decimals' => array(
                'default' => 0,
                'type' => 'number'
            ),
            'max_length' => 255,
            'size' => 'medium'
        );
        return $options;
    }

    /**
     * Change the value before it's sent to be displayed or saved
     *
     * @param mixed $value
     * @param array $options
     *
     * @since 2.0.0
     */
    public function value ( &$value, $options ) {
        $decimals = 0;
        if ( isset( $options[ 'number_decimals' ] ) )
            $decimals = (int) $options[ 'number_decimals' ];
        $value = number_format( (float) $value, $decimals, '.', '' );
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
        $attributes = array();
        $attributes[ 'value' ] = 1;
        $attributes[ 'checked' ] = ( 1 == $value || true === $value ) ? 'CHECKED' : null;
        $attributes = self::merge_attributes( $attributes, $name, self::$type, $options );
        if ( isset( $options[ 'default' ] ) && strlen( $attributes[ 'value' ] ) < 1 )
            $attributes[ 'value' ] = $options[ 'default' ];
        $attributes[ 'value' ] = apply_filters( 'pods_form_ui_field_' . self::$type . '_value', $attributes[ 'value' ], $name, $attributes, $options );

        pods_view( PODS_DIR . 'ui/fields/text.php', compact( $attributes, $name, $value, self::$type, $options, $pod, $id ) );
    }

    /**
     * Change the way the value of the field is displayed, optionally called with Pods::get
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
    public function display ( $value, $name, $options, $fields, $pod, $id ) {
        $number_format = '9,999.99';
        if ( isset( $options[ 'number_format' ] ) ) {
            $number_format = (int) $options[ 'number_format' ];
        }

        $decimals = 0;
        if ( isset( $options[ 'number_decimals' ] ) ) {
            $decimals = (int) $options[ 'number_decimals' ];
        }

        $thousands = ',';
        $dot = '.';
        if ( '9999.99' == $number_format )
            $thousands = '';
        elseif ( '9999,99' == $number_format ) {
            $thousands = '';
            $dot = ',';
        }
        elseif ( '9.999,99' == $number_format ) {
            $thousands = '.';
            $dot = ',';
        }

        if ( 'i18n' == $number_format )
            $value = number_format_i18n( $value, $decimals );
        else
            $value = number_format( $value, $decimals, $dot, $thousands );

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

        return $value;
    }
}