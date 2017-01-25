<?php

/**
 * @package Pods
 */
class PodsField {

	/**
	 * Whether this field is running under 1.x deprecated forms
	 *
	 * @var bool
	 * @since 2.0
	 */
	public static $deprecated = false;

	/**
	 * Field Type Identifier
	 *
	 * @var string
	 * @since 2.0
	 */
	public static $type = 'text';

	/**
	 * Field Type Label
	 *
	 * @var string
	 * @since 2.0
	 */
	public static $label = 'Unknown';

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
	public static $pod_types = true;

	/**
	 * API caching for fields that need it during validate/save
	 *
	 * @var \PodsAPI
	 * @since 2.3
	 */
	private static $api = false;

	/**
	 * Do things like register/enqueue scripts and stylesheets
	 *
	 * @since 2.0
	 */
	public function __construct() {

		// Placeholder

	}

	/**
	 * Add options and set defaults for field type, shows in admin area
	 *
	 * @return array $options
	 *
	 * @since 2.0
	 * @see   PodsField::ui_options
	 */
	public function options() {

		$options = array(
			/*
            'option_name' => array(
                'label' => 'Option Label',
                'depends-on' => array( 'another_option' => 'specific-value' ),
                'default' => 'default-value',
                'type' => 'field_type',
                'data' => array(
                    'value1' => 'Label 1',

                    // Group your options together
                    'Option Group' => array(
                        'gvalue1' => 'Option Label 1',
                        'gvalue2' => 'Option Label 2'
                    ),

                    // below is only if the option_name above is the "{$fieldtype}_format_type"
                    'value2' => array(
                        'label' => 'Label 2',
                        'regex' => '[a-zA-Z]' // Uses JS regex validation for the value saved if this option selected
                    )
                ),

                // below is only for a boolean group
                'group' => array(
                    'option_boolean1' => array(
                        'label' => 'Option boolean 1?',
                        'default' => 1,
                        'type' => 'boolean'
                    ),
                    'option_boolean2' => array(
                        'label' => 'Option boolean 2?',
                        'default' => 0,
                        'type' => 'boolean'
                    )
                )
            )
            */
		);

		return $options;

	}

	/**
	 * Options for the Admin area, defaults to $this->options()
	 *
	 * @return array $options
	 *
	 * @since 2.0
	 * @see   PodsField::options
	 */
	public function ui_options() {

		return $this->options();

	}

	/**
	 * Define the current field's schema for DB table storage
	 *
	 * @param array|null $options
	 *
	 * @return string
	 *
	 * @since 2.0
	 */
	public function schema( $options = null ) {

		$schema = 'VARCHAR(255)';

		return $schema;

	}

	/**
	 * Define the current field's preparation for sprintf
	 *
	 * @param array|null $options
	 *
	 * @return string
	 *
	 * @since 2.0
	 */
	public function prepare( $options = null ) {

		$format = self::$prepare;

		return $format;

	}

	/**
	 * Change the value of the field
	 *
	 * @param mixed|null  $value
	 * @param string|null $name
	 * @param array|null  $options
	 * @param array|null  $pod
	 * @param int|null    $id
	 *
	 * @return mixed|null|string
	 *
	 * @since 2.3
	 */
	public function value( $value = null, $name = null, $options = null, $pod = null, $id = null ) {

		return $value;

	}

	/**
	 * Change the way the value of the field is displayed with Pods::get
	 *
	 * @param mixed|null  $value
	 * @param string|null $name
	 * @param array|null  $options
	 * @param array|null  $pod
	 * @param int|null    $id
	 *
	 * @return mixed|null|string
	 *
	 * @since 2.0
	 */
	public function display( $value = null, $name = null, $options = null, $pod = null, $id = null ) {

		return $value;

	}

	/**
	 * Customize output of the form field
	 *
	 * @param string $name
	 * @param mixed|null  $value
	 * @param array|null  $options
	 * @param array|null  $pod
	 * @param int|null    $id
	 *
	 * @return void
	 *
	 * @since 2.0
	 */
	public function input( $name, $value = null, $options = null, $pod = null, $id = null ) {

		$options = (array) $options;

		$form_field_type = PodsForm::$field_type;

		if ( is_array( $value ) ) {
			$value = implode( ' ', $value );
		}

		pods_view( PODS_DIR . 'ui/fields/text.php', compact( array_keys( get_defined_vars() ) ) );

	}

	/**
	 * Get the data from the field
	 *
	 * @param string       $name  The name of the field
	 * @param string|array|null $value The value of the field
	 * @param array|null        $options
	 * @param array|null        $pod
	 * @param int|null          $id
	 * @param boolean      $in_form
	 *
	 * @return array Array of possible field data
	 *
	 * @since 2.0
	 */
	public function data( $name, $value = null, $options = null, $pod = null, $id = null, $in_form = true ) {

		return (array) $value;

	}

	/**
	 * Build regex necessary for JS validation
	 *
	 * @param mixed|null  $value
	 * @param string|null $name
	 * @param array|null  $options
	 * @param string|null $pod
	 * @param int|null    $id
	 *
	 * @return bool
	 *
	 * @since 2.0
	 */
	public function regex( $value = null, $name = null, $options = null, $pod = null, $id = null ) {

		return false;

	}

	/**
	 * Validate a value before it's saved
	 *
	 * @param mixed  $value
	 * @param string|null $name
	 * @param array|null  $options
	 * @param array|null  $fields
	 * @param array|null  $pod
	 * @param int|null    $id
	 * @param array|null  $params
	 *
	 * @return bool
	 *
	 * @since 2.0
	 */
	public function validate( $value, $name = null, $options = null, $fields = null, $pod = null, $id = null, $params = null ) {

		return true;

	}

	/**
	 * Change the value or perform actions after validation but before saving to the DB
	 *
	 * @param mixed  $value
	 * @param int|null    $id
	 * @param string|null $name
	 * @param array|null  $options
	 * @param array|null  $fields
	 * @param array|null  $pod
	 * @param object|null $params
	 *
	 * @return mixed
	 *
	 * @since 2.0
	 */
	public function pre_save( $value, $id = null, $name = null, $options = null, $fields = null, $pod = null, $params = null ) {

		return $value;

	}

	/**
	 * Save the value to the DB
	 *
	 * @param mixed  $value
	 * @param int|null    $id
	 * @param string|null $name
	 * @param array|null  $options
	 * @param array|null  $fields
	 * @param array|null  $pod
	 * @param object|null $params
	 *
	 * @return bool|void Whether the value was saved
	 *
	 * @since 2.3
	 */
	public function save( $value, $id = null, $name = null, $options = null, $fields = null, $pod = null, $params = null ) {

		return null;

	}

	/**
	 * Perform actions after saving to the DB
	 *
	 * @param mixed  $value
	 * @param int|null    $id
	 * @param string|null $name
	 * @param array|null  $options
	 * @param array|null  $fields
	 * @param array|null  $pod
	 * @param object|null $params
	 *
	 * @return void
	 *
	 * @since 2.0
	 */
	public function post_save( $value, $id = null, $name = null, $options = null, $fields = null, $pod = null, $params = null ) {

		// Placeholder

	}

	/**
	 * Perform actions before deleting from the DB
	 *
	 * @param int|null    $id
	 * @param string|null $name
	 * @param array|null   $options
	 * @param string|null $pod
	 *
	 * @return void
	 *
	 * @since 2.0
	 */
	public function pre_delete( $id = null, $name = null, $options = null, $pod = null ) {

		// Placeholder

	}

	/**
	 * Delete the value from the DB
	 *
	 * @param int|null    $id
	 * @param string|null $name
	 * @param array|null  $options
	 * @param array|null  $pod
	 *
	 * @return void
	 *
	 * @since 2.3
	 */
	public function delete( $id = null, $name = null, $options = null, $pod = null ) {

		// Placeholder

	}

	/**
	 * Perform actions after deleting from the DB
	 *
	 * @param int|null    $id
	 * @param string|null $name
	 * @param array|null  $options
	 * @param array|null  $pod
	 *
	 * @return void
	 *
	 * @since 2.0
	 */
	public function post_delete( $id = null, $name = null, $options = null, $pod = null ) {

		// Placeholder

	}

	/**
	 * Customize the Pods UI manage table column output
	 *
	 * @param int    $id
	 * @param mixed  $value
	 * @param string|null $name
	 * @param array|null  $options
	 * @param array|null  $fields
	 * @param array|null  $pod
	 *
	 * @return string Value to be shown in the UI
	 *
	 * @since 2.0
	 */
	public function ui( $id, $value, $name = null, $options = null, $fields = null, $pod = null ) {

		return $value;

	}

	/**
	 * Placeholder function to allow var_export() use with classes
	 *
	 * @param array $properties
	 *
	 * @return object|void
	 */
	public static function __set_state( $properties ) {

		// Placeholder

	}

}
