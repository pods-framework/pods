<?php
class PodsField_Date extends PodsField {

    /**
     * Field Type Identifier
     *
     * @var string
     * @since 2.0.0
     */
    protected static $type = 'date';

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
            'date_format_type' => array(
                'label' => __( 'Format Type', 'pods' ),
                'default' => 'date',
                'type' => 'pick',
                'data' => array(
                    'date' => __( 'Date', 'pods' ),
                    'datetime' => __( 'Date + Time', 'pods' ),
                    'time' => __( 'Time', 'pods' )
                )
            ),
            'date_format' => array(
                'label' => __( 'Format Type', 'pods' ),
                'depends-on' => array( 'date_format_type' => array( 'date', 'datetime' ) ),
                'default' => 'mdy',
                'type' => 'pick',
                'data' => array(
                    'mdy' => 'mm/dd/yyyy',
                    'dmy' => 'dd/mm/yyyy',
                    'dmy_dash' => 'dd-mm-yyyy',
                    'dmy_dot' => 'dd.mm.yyyy',
                    'ymd_slash' => 'yyyy/mm/dd',
                    'ymd_dash' => 'yyyy-mm-dd',
                    'ymd_dot' => 'yyyy.mm.dd'
                )
            ),
            'date_time_type' => array(
                'label' => __( 'Format Type', 'pods' ),
                'depends-on' => array( 'date_format_type' => array( 'datetime', 'time' ) ),
                'default' => '12',
                'type' => 'pick',
                'data' => array(
                    '12' => __( '12 hour', 'pods' ),
                    '24' => __( '24 hour', 'pods' )
                )
            ),
            'date_time_format' => array(
                'label' => __( 'Format Type', 'pods' ),
                'depends-on' => array(
                    'date_format_type' => array( 'datetime', 'time' ),
                    'date_time_type' => '12',
                ),
                'default' => 'h_mma',
                'type' => 'pick',
                'data' => array(
                    'h_mma' => '1:25 PM',
                    'hh_mma' => '01:25 PM',
                    'h_mm' => '1:25',
                    'hh_mm' => '01:25'
                )
            ),
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

        return $value;
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

        pods_view( PODS_DIR . 'ui/fields/number.php', compact( $name, $value, self::$type, $options, $pod, $id ) );
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