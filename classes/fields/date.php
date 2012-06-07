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
     * Field Type Label
     *
     * @var string
     * @since 2.0.0
     */
    protected static $label = 'Date / Time';

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
                    'h_mm_A' => '1:25 PM',
                    'hh_mm_A' => '01:25 PM',
                    'h_mma' => '1:25pm',
                    'hh_mma' => '01:25pm',
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
        $format = $this->format( $options );

        if ( !empty( $value ) ) {
            $date = DateTime::createFromFormat( 'Y-m-d H:i:s', (string) $value );
            if ( false !== $date )
                $value = $date->format( $format );
            else
                $value = date_i18n( $format );
        }
        else
            $value = date_i18n( $format );
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

        // Format Value
        $this->display( $value, $name, $options, null, $pod, $id );

        pods_view( PODS_DIR . 'ui/fields/date.php', compact( array_keys( get_defined_vars() ) ) );
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

        if ( !empty( $value ) ) {
            $format = $this->format( $options );
            $date = DateTime::createFromFormat( $format, (string) $value );
            if ( false !== $date )
                $value = $date->format( 'Y-m-d H:i:s' );
            else
                $value = date_i18n( 'Y-m-d H:i:s' );
        }
        else
            $value = date_i18n( 'Y-m-d H:i:s' );
    }

    /**
     * Build date/time format string based on options
     *
     * @param $options
     *
     * @return string
     * @since 2.0.0
     */
    private function format ( $options ) {
        $date_format = array(
            'mdy' => 'm/d/Y',
            'dmy' => 'd/m/Y',
            'dmy_dash' => 'd-m-Y',
            'dmy_dot' => 'd.m.Y',
            'ymd_slash' => 'Y/m/d',
            'ymd_dash' => 'Y-m-d',
            'ymd_dot' => 'Y.m.d'
        );
        $time_format = array(
            'h_mm_A' => 'g:i A',
            'hh_mm_A' => 'h:i A',
            'h_mma' => 'g:ia',
            'hh_mma' => 'h:ia',
            'h_mm' => 'g:i',
            'hh_mm' => 'h:i'
        );

        $format = 'Y-m-d H:i:s';
        if ( 'date' == $options[ 'date_format_type' ] )
            $format = $date_format[ $options[ 'date_format' ] ];
        elseif ( 'datetime' == $options[ 'date_format_type' ] ) {
            $format = $date_format[ $options[ 'date_format' ] ] . ' ';
            if ( 12 == $options[ 'date_time_type' ] )
                $format .= $time_format[ $options[ 'date_time_format' ] ];
            else
                $format .= 'H:i';
        }
        elseif ( 'time' == $options[ 'date_format_type' ] ) {
            if ( 12 == $options[ 'date_time_type' ] )
                $format = $time_format[ $options[ 'date_time_format' ] ];
            else
                $format = 'H:i';
        }

        return $format;
    }
}