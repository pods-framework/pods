<?php
/**
 * @package Pods\Fields
 */
class PodsField_Time extends PodsField {

    /**
     * Field Type Group
     *
     * @var string
     * @since 2.0.0
     */
    public static $group = 'Date / Time';

    /**
     * Field Type Identifier
     *
     * @var string
     * @since 2.0.0
     */
    public static $type = 'time';

    /**
     * Field Type Label
     *
     * @var string
     * @since 2.0.0
     */
    public static $label = 'Time';

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
     * @return array
     * @since 2.0.0
     */
    public function options () {
        $options = array(
            'time_type' => array(
                'label' => __( 'Time Format Type', 'pods' ),
                'default' => '12',
                'type' => 'pick',
                'data' => array(
                    '12' => __( '12 hour', 'pods' ),
                    '24' => __( '24 hour', 'pods' )
                )
            ),
            'time_format' => array(
                'label' => __( 'Time Format', 'pods' ),
                'default' => 'h_mma',
                'type' => 'pick',
                'data' => array(
                    'h_mm_A' => '1:25 PM',
                    'h_mm_ss_A' => '1:25:00 PM',
                    'hh_mm_A' => '01:25 PM',
                    'hh_mm_ss_A' => '01:25:00 PM',
                    'h_mma' => '1:25pm',
                    'hh_mma' => '01:25pm',
                    'h_mm' => '1:25',
                    'h_mm_ss' => '1:25:00',
                    'hh_mm' => '01:25',
                    'hh_mm_ss' => '01:25:00'
                )
            ),
            'time_allow_empty' => array(
                'label' => __( 'Allow empty value?', 'pods' ),
                'default' => 1,
                'type' => 'boolean'
            ),
            'time_html5' => array(
                'label' => __( 'Enable HTML5 Input Field?', 'pods' ),
                'default' => apply_filters( 'pods_form_ui_field_html5', 0, self::$type ),
                'type' => 'boolean'
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
        $schema = 'TIME NOT NULL default "00:00:00"';

        return $schema;
    }

    /**
     * Change the way the value of the field is displayed with Pods::get
     *
     * @param mixed $value
     * @param string $name
     * @param array $options
     * @param array $pod
     * @param int $id
     *
     * @return mixed|null|string
     * @since 2.0.0
     */
    public function display ( $value = null, $name = null, $options = null, $pod = null, $id = null ) {
        $format = $this->format( $options );

        if ( !empty( $value ) && !in_array( $value, array( '0000-00-00', '0000-00-00 00:00:00', '00:00:00' ) ) ) {
            $date = $this->createFromFormat( 'H:i:s', (string) $value );
            $date_local = $this->createFromFormat( $format, (string) $value );

            if ( false !== $date )
                $value = $date->format( $format );
            elseif ( false !== $date_local )
                $value = $date_local->format( $format );
            else
                $value = date_i18n( $format, strtotime( (string) $value ) );
        }
        elseif ( 0 == pods_var( 'time_allow_empty', $options, 1 ) )
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

        if ( is_array( $value ) )
            $value = implode( ' ', $value );

        // Format Value
        $value = $this->display( $value, $name, $options, null, $pod, $id );

        pods_view( PODS_DIR . 'ui/fields/time.php', compact( array_keys( get_defined_vars() ) ) );
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
     * @return mixed|string
     * @since 2.0.0
     */
    public function pre_save ( $value, $id = null, $name = null, $options = null, $fields = null, $pod = null, $params = null ) {
        $format = $this->format( $options );

        if ( !empty( $value ) && ( 0 == pods_var( 'time_allow_empty', $options, 1 ) || !in_array( $value, array( '0000-00-00', '0000-00-00 00:00:00', '00:00:00' ) ) ) )
            $value = $this->convert_date( $value, 'H:i:s', $format );
        elseif ( 1 == pods_var( 'time_allow_empty', $options, 1 ) )
            $value = '00:00:00';
        else
            $value = date_i18n( 'H:i:s' );

        return $value;
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
        $value = $this->display( $value, $name, $options, $pod, $id );

        if ( 1 == pods_var( 'datetime_allow_empty', $options, 1 ) && in_array( $value, array( '0000-00-00', '0000-00-00 00:00:00', '00:00:00' ) ) )
            $value = false;

        return $value;
    }

    /**
     * Build date/time format string based on options
     *
     * @param $options
     *
     * @return string
     * @since 2.0.0
     */
    public function format ( $options ) {
        $time_format = array(
            'h_mm_A' => 'g:i A',
            'h_mm_ss_A' => 'g:i:s A',
            'hh_mm_A' => 'h:i A',
            'hh_mm_ss_A' => 'h:i:s A',
            'h_mma' => 'g:ia',
            'hh_mma' => 'h:ia',
            'h_mm' => 'g:i',
            'h_mm_ss' => 'g:i:s',
            'hh_mm' => 'h:i',
            'hh_mm_ss' => 'h:i:s'
        );

        if ( 12 == pods_var( 'time_type', $options ) )
            $format = $time_format[ pods_var( 'time_format', $options, 'hh_mm', null, true ) ];
        else
            $format = str_replace( array( 'h:', 'g:' ), 'H:', $time_format[ pods_var( 'time_format', $options, 'hh_mm', null, true ) ] );

        return $format;
    }

    /**
     * @param $format
     * @param $date
     *
     * @return DateTime
     */
    public function createFromFormat ( $format, $date ) {
        if ( method_exists( 'DateTime', 'createFromFormat' ) )
            return DateTime::createFromFormat( $format, (string) $date );

        return new DateTime( date_i18n( 'H:i:s', strtotime( (string) $date ) ) );
    }

    /**
     * Convert a date from one format to another
     *
     * @param $date
     * @param $new_format
     * @param $original_format
     */
    public function convert_date ( $value, $new_format, $original_format = 'H:i:s' ) {
        if ( !empty( $value ) && !in_array( $value, array( '0000-00-00', '0000-00-00 00:00:00', '00:00:00' ) ) ) {
            $date = $this->createFromFormat( $original_format, (string) $value );

            if ( false !== $date )
                $value = $date->format( $new_format );
            else
                $value = date_i18n( $new_format, strtotime( (string) $value ) );
        }
        else
            $value = date_i18n( $new_format );

        return $value;
    }
}
