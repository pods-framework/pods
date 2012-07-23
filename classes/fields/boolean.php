<?php
class PodsField_Boolean extends PodsField {

    /**
     * Field Type Identifier
     *
     * @var string
     * @since 2.0.0
     */
    public static $type = 'boolean';

    /**
     * Field Type Label
     *
     * @var string
     * @since 2.0.0
     */
    public static $label = 'Yes / No';

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
            'boolean_format_type' => array(
                'label' => __( 'Format Type', 'pods' ),
                'default' => 'checkbox',
                'type' => 'pick',
                'data' => array(
                    'checkbox' => __( 'Checkbox', 'pods' ),
                    'radio' => __( 'Radio Buttons', 'pods' ),
                    'dropdown' => __( 'Drop Down', 'pods' )
                ),
                'dependency' => true
            ),
            'boolean_yes_label' => array(
                'label' => __( 'Yes Label', 'pods' ),
                'default' => __( 'Yes', 'pods' ),
                'type' => 'text'
            ),
            'boolean_no_label' => array(
                'label' => __( 'No Label', 'pods' ),
                'default' => __( 'No', 'pods' ),
                'type' => 'text'
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
        $schema = 'BOOL DEFAULT 0';

        return $schema;
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
    public function display ( &$value, $name, $options, $fields, $pod, $id ) {
        $yesno = array(
            1 => $options[ 'boolean_yes_label' ],
            0 => $options[ 'boolean_no_label' ]
        );

        // Deprecated handling for 1.x
        if ( !parent::$deprecated )
            $value = $yesno[ $value ];
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

        $field_type = 'checkbox';

        if ( 'checkbox' != $options[ 'boolean_format_type' ] ) {
            $options[ 'data' ] = array(
                1 => $options[ 'boolean_yes_label' ],
                0 => $options[ 'boolean_no_label' ]
            );

            if ( 'radio' == $options[ 'boolean_format_type' ] )
                $field_type = 'radio';
            elseif ( 'dropdown' == $options[ 'boolean_format_type' ] )
                $field_type = 'select';
        }
        else {
            $options[ 'data' ] = array(
                1 => $options[ 'boolean_yes_label' ]
            );
        }

        pods_view( PODS_DIR . 'ui/fields/' . $field_type . '.php', compact( array_keys( get_defined_vars() ) ) );
    }

    /**
     * Change the value or perform actions after validation but before saving to the DB
     *
     * @param string $value
     *
     * @since 2.0.0
     */
    public function pre_save ( &$value ) {
        // Only allow 0 / 1
        $value = ( 1 == (int) $value ? 1 : 0 );
    }
}
