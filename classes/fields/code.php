<?php
/**
 * Handles code field data and operations
 *
 * @package Pods\Fields
 */
class PodsField_Code extends PodsField {

    /**
     * Field Type Group
     *
     * @var string
     * @since 2.0
     */
    public static $group = 'Paragraph';

    /**
     * Field Type Identifier
     *
     * @var string
     * @since 2.0
     */
    public static $type = 'code';

    /**
     * Field Type Label
     *
     * @var string
     * @since 2.0
     */
    public static $label = 'Code (Syntax Highlighting)';

    /**
     * Field Type Preparation
     *
     * @var string
     * @since 2.0
     */
    public static $prepare = '%s';

    /**
     * Do things like register/enqueue scripts and stylesheets
     *
     * @since 2.0
     */
    public function __construct () {

    }

    /**
     * Add options and set defaults to
     *
     * @return array
     * @since 2.0
     */
    public function options () {
        $options = array(
            self::$type . '_repeatable' => array(
                'label' => __( 'Repeatable Field', 'pods' ),
                'default' => 0,
                'type' => 'boolean',
                'help' => __( 'Making a field repeatable will add controls next to the field which allows users to Add/Remove/Reorder additional values. These values are saved in the database as an array, so searching and filtering by them may require further adjustments".', 'pods' ),
                'boolean_yes_label' => '',
                'dependency' => true,
                'developer_mode' => true
            ),
            'output_options' => array(
                'label' => __( 'Output Options', 'pods' ),
                'group' => array(
                    self::$type . '_allow_shortcode' => array(
                        'label' => __( 'Allow Shortcodes?', 'pods' ),
                        'default' => 0,
                        'type' => 'boolean',
                        'dependency' => true
                    )
                )
            ),
            self::$type . '_max_length' => array(
                'label' => __( 'Maximum Length', 'pods' ),
                'default' => 0,
                'type' => 'number',
                'help' => __( 'Set to -1 for no limit', 'pods' )
            )/*,
            self::$type . '_size' => array(
                'label' => __( 'Field Size', 'pods' ),
                'default' => 'medium',
                'type' => 'pick',
                'data' => array(
                    'small' => __( 'Small', 'pods' ),
                    'medium' => __( 'Medium', 'pods' ),
                    'large' => __( 'Large', 'pods' )
                )
            )*/
        );

        return $options;
    }

    /**
     * Define the current field's schema for DB table storage
     *
     * @param array $options
     *
     * @return array
     * @since 2.0
     */
    public function schema ( $options = null ) {
        $length = (int) pods_v( self::$type . '_max_length', $options, 0 );

        $schema = 'LONGTEXT';

		if ( 0 < $length ) {
        	$schema = 'VARCHAR(' . $length . ')';
		}

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
     * @since 2.0
     */
    public function display ( $value = null, $name = null, $options = null, $pod = null, $id = null ) {
        if ( 1 == pods_v( self::$type . '_allow_shortcode', $options, 0 ) )
            $value = do_shortcode( $value );

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
     * @since 2.0
     */
    public function input ( $name, $value = null, $options = null, $pod = null, $id = null ) {
        $options = (array) $options;
        $form_field_type = PodsForm::$field_type;

        if ( is_array( $value ) )
            $value = implode( "\n", $value );

        $field_type = 'codemirror';

        do_action( 'pods_form_ui_field_code_' . $field_type, $name, $value, $options, $pod, $id );
        do_action( 'pods_form_ui_field_code', $field_type, $name, $value, $options, $pod, $id );

        pods_view( PODS_DIR . 'ui/fields/' . $field_type . '.php', compact( array_keys( get_defined_vars() ) ) );
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
     * @since 2.0
     */
    public function pre_save ( $value, $id = null, $name = null, $options = null, $fields = null, $pod = null, $params = null ) {
		$length = (int) pods_var( self::$type . '_max_length', $options, 0 );

		if ( 0 < $length && $length < pods_mb_strlen( $value ) ) {
			$value = pods_mb_substr( $value, 0, $length );
		}

        return $value;
    }
}
