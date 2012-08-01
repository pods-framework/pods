<?php
class PodsField_Date extends PodsField {

    /**
     * Field Type Identifier
     *
     * @var string
     * @since 2.0.0
     */
    public static $type = 'date';

    /**
     * Field Type Label
     *
     * @var string
     * @since 2.0.0
     */
    public static $label = 'Date / Time';

    /**
     * Field Type Preparation
     *
     * @var string
     * @since 2.0.0
     */
    public static $prepare = '%s';

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
                ),
                'dependency' => true
            ),
            'date_format' => array(
                'label' => __( 'Date Format', 'pods' ),
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
                'label' => __( 'Time Format Type', 'pods' ),
                'depends-on' => array( 'date_format_type' => array( 'datetime', 'time' ) ),
                'default' => '12',
                'type' => 'pick',
                'data' => array(
                    '12' => __( '12 hour', 'pods' ),
                    '24' => __( '24 hour', 'pods' )
                ),
                'dependency' => true
            ),
            'date_time_format' => array(
                'label' => __( 'Time Format', 'pods' ),
                'excludes-on' => array(
                    'date_format_type' => 'date',
                    'date_time_type' => '24',
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
            'date_size' => array(
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
        $schema = 'DATETIME NOT NULL default "0000-00-00 00:00:00"';

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
        $format = $this->format( $options );

        if ( !empty( $value ) ) {
            $date = DateTime::createFromFormat( 'Y-m-d H:i:s', (string) $value );

            if ( false !== $date )
                $value = $date->format( $format );
            else
                $value = date_i18n( $format, strtotime( (string) $value ) );
        }
        else
            $value = date_i18n( $format );

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

        // Format Value
        $this->display( $value, $name, $options, null, $pod, $id );

        pods_view( PODS_DIR . 'ui/fields/date.php', compact( array_keys( get_defined_vars() ) ) );
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
        if ( !empty( $value ) ) {
            $format = $this->format( $options );

            $date = DateTime::createFromFormat( $format, (string) $value );

            if ( false !== $date )
                $value = $date->format( 'Y-m-d H:i:s' );
            else
                $value = date_i18n( 'Y-m-d H:i:s', strtotime( (string) $value ) );
        }
        else
            $value = date_i18n( 'Y-m-d H:i:s' );

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
    public function ui ( $id, &$value, $name = null, $options = null, $fields = null, $pod = null ) {

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