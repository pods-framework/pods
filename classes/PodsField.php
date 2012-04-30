<?php
class PodsField {

    /**
     * Whether this field is running under 1.x deprecated forms
     *
     * @var bool
     * @since 2.0.0
     */
    public static $deprecated = false;

    /**
     * Field Type Identifier
     *
     * @var string
     * @since 2.0.0
     */
    protected static $type = 'text';

    /**
     * Do things like register/enqueue scripts and stylesheets
     *
     * @since 2.0.0
     */
    public function __construct () {

    }

    /**
     * Add options and set defaults for field type, shows in admin area
     *
     * @return array $options
     *
     * @since 2.0.0
     */
    public function options () {
        $options = array();
        
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
        $attributes[ 'value' ] = $value;
        $attributes = self::merge_attributes( $attributes, $name, self::$type, $options );
        if ( isset( $options[ 'default' ] ) && strlen( $attributes[ 'value' ] ) < 1 )
            $attributes[ 'value' ] = $options[ 'default' ];
        $attributes[ 'value' ] = apply_filters( 'pods_form_ui_field_' . self::$type . '_value', $attributes[ 'value' ], $name, $attributes, $options );

        return pods_view( PODS_DIR . 'ui/fields/text.php', compact( $attributes, $name, $value, self::$type, $options, $pod, $id ) );
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
    public function display ( &$value, $name, $options, $fields, $pod, $id ) {

    }

    /**
     * Change the value or perform actions before saving to the DB
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
    public function pre_save ( &$value, $name, $options, $data, &$api, $pod, $id = false ) {

    }

    /**
     * Perform actions after saving to the DB
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
    public function post_save ( &$value, $name, $options, $data, &$api, $pod, $id = false ) {

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
    public function pre_delete ( $name, $options, $pod, $id, &$api ) {

    }

    /**
     * Perform actions after deleting from the DB
     *
     * @param string $pod
     * @param int $id
     * @param object $api
     *
     * @since 2.0.0
     */
    public function post_delete ( $pod, $id, &$api ) {

    }
}