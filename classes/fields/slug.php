<?php
/**
 * @package Pods\Fields
 */
class PodsField_Slug extends PodsField {

    /**
     * Field Type Identifier
     *
     * @var string
     * @since 2.0
     */
    public static $type = 'slug';

    /**
     * Field Type Label
     *
     * @var string
     * @since 2.0
     */
    public static $label = 'Permalink (url-friendly)';

    /**
     * Field Type Preparation
     *
     * @var string
     * @since 2.0
     */
    public static $prepare = '%s';

    /**
     * Pod Types supported on (true for all, false for none, or give array of specific types supported)
     *
     * @var array|bool
     * @since 2.1
     */
    public static $pod_types = array( 'pod', 'table' );

    /**
     * Do things like register/enqueue scripts and stylesheets
     *
     * @since 2.0
     */
    public function __construct () {
	    self::$label = __( 'Permalink (url-friendly)', 'pods' );
    }

	/**
	 * {@inheritdoc}
	 */
	public function options () {

		$options = array(
			self::$type . '_placeholder' => array(
				'label' => __( 'HTML Placeholder', 'pods' ),
				'default' => '',
				'type' => 'text',
				'help' => array(
					__( 'Placeholders can provide instructions or an example of the required data format for a field. Please note: It is not a replacement for labels or description text, and it is less accessible for people using screen readers.', 'pods' ),
					'https://www.w3.org/WAI/tutorials/forms/instructions/#placeholder-text',
				),
			),
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
        $schema = 'VARCHAR(200)';

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
     * @return mixed|null
     * @since 2.0
     */
    public function display ( $value = null, $name = null, $options = null, $pod = null, $id = null ) {
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
            $value = implode( '-', $value );

        $field_type = 'slug';

        if ( isset( $options[ 'name' ] ) && false === PodsForm::permission( self::$type, $options[ 'name' ], $options, null, $pod, $id ) ) {
            if ( pods_var( 'read_only', $options, false ) ) {
                $options[ 'readonly' ] = true;

                $field_type = 'text';
            }
            else
                return;
        }
        elseif ( !pods_has_permissions( $options ) && pods_var( 'read_only', $options, false ) ) {
            $options[ 'readonly' ] = true;

            $field_type = 'text';
        }

        pods_view( PODS_DIR . 'ui/fields/' . $field_type . '.php', compact( array_keys( get_defined_vars() ) ) );
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
     * @return bool
     * @since 2.0
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
     * @param null $params
     *
     * @return bool
     * @since 2.0
     */
    public function validate ( $value, $name = null, $options = null, $fields = null, $pod = null, $id = null, $params = null ) {
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
     * @return mixed|string
     * @since 2.0
     */
    public function pre_save ( $value, $id = null, $name = null, $options = null, $fields = null, $pod = null, $params = null ) {
        $index = pods_var( 'pod_index', pods_var( 'options', $pod, $pod, null, true ), 'id', null, true );

        if ( empty( $value ) && isset( $fields[ $index ] ) )
            $value = $fields[ $index ][ 'value' ];

        $value = pods_unique_slug( $value, $name, $pod, 0, $params->id, null, false );

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
     * @return mixed|void
     * @since 2.0
     */
    public function ui ( $id, $value, $name = null, $options = null, $fields = null, $pod = null ) {
        return $this->display( $value, $name, $options, $pod, $id );
    }
}
