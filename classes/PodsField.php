<?php

/**
 * Pods Field class for common type-specific methods.
 *
 * @package Pods
 */
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
	public static $type = 'text';

	/**
	 * Field Type Label
	 *
	 * @var string
	 * @since 2.0.0
	 */
	public static $label = 'Unknown';

	/**
	 * Field Type Preparation
	 *
	 * @var string
	 * @since 2.0.0
	 */
	public static $prepare = '%s';

	/**
	 * Pod Types supported on (true for all, false for none, or give array of specific types supported)
	 *
	 * @var array|bool
	 * @since 2.1.0
	 */
	public static $pod_types = true;

	/**
	 * API caching for fields that need it during validate/save
	 *
	 * @var \PodsAPI
	 * @since 2.3.0
	 */
	private static $api;

	/**
	 * Initial setup of class object.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		// Run any setup needed.
		$this->setup();
	}

	/**
	 * Do things like register/enqueue scripts+stylesheets, set labels, etc.
	 *
	 * @since 2.7.2
	 */
	public function setup() {

		// Subclasses utilize this method if needed.
	}

	/**
	 * Add admin_init actions.
	 *
	 * @since 2.3.0
	 */
	public function admin_init() {

		// Add admin actions here.
	}

	/**
	 * Add options and set defaults for field type, shows in admin area
	 *
	 * @return array $options
	 *
	 * @since 2.0.0
	 * @see   PodsField::ui_options
	 */
	public function options() {

		$options = array();

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

		return $options;

	}

	/**
	 * Options for the Admin area, defaults to $this->options()
	 *
	 * @return array $options
	 *
	 * @since 2.0.0
	 * @see   PodsField::options
	 */
	public function ui_options() {

		return $this->options();

	}

	/**
	 * Define the current field's schema for DB table storage
	 *
	 * @param array|null $options Field options.
	 *
	 * @return string|false
	 *
	 * @since 2.0.0
	 */
	public function schema( $options = null ) {

		$schema = 'VARCHAR(255)';

		return $schema;

	}

	/**
	 * Define the current field's preparation for sprintf
	 *
	 * @param array|null $options Field options.
	 *
	 * @return string
	 *
	 * @since 2.0.0
	 */
	public function prepare( $options = null ) {

		$format = self::$prepare;

		return $format;

	}

	/**
	 * Check if the field is empty.
	 *
	 * @param mixed $value Field value.
	 *
	 * @return bool
	 *
	 * @since 2.7.0
	 */
	public function is_empty( $value ) {

		$is_empty = false;

		if ( is_string( $value ) ) {
			$value = trim( $value );
		}

		if ( empty( $value ) ) {
			$is_empty = true;
		}

		return $is_empty;

	}

	/**
	 * Check if the field values are empty.
	 *
	 * @param array|mixed $values Field values.
	 * @param boolean     $strict Whether to check if any of the values are non-empty in an array.
	 *
	 * @return bool
	 *
	 * @since 2.7.0
	 */
	public function values_are_empty( $values, $strict = true ) {

		$is_empty = false;

		if ( is_array( $values ) && isset( $values[0] ) ) {
			if ( $strict ) {
				foreach ( $values as $value ) {
					$is_empty = true;

					if ( ! $this->is_empty( $value ) ) {
						$is_empty = false;

						break;
					}
				}
			} elseif ( empty( $values ) ) {
				$is_empty = true;
			}
		} else {
			$is_empty = $this->is_empty( $values );
		}

		return $is_empty;

	}

	/**
	 * Change the value of the field
	 *
	 * @param mixed|null      $value   Current value.
	 * @param string|null     $name    Field name.
	 * @param array|null      $options Field options.
	 * @param array|null      $pod     Pod information.
	 * @param int|string|null $id      Current item ID.
	 *
	 * @return mixed|null|string
	 *
	 * @since 2.3.0
	 */
	public function value( $value = null, $name = null, $options = null, $pod = null, $id = null ) {

		return $value;

	}

	/**
	 * Change the way the value of the field is displayed with Pods::get
	 *
	 * @param mixed|null      $value   Current value.
	 * @param string|null     $name    Field name.
	 * @param array|null      $options Field options.
	 * @param array|null      $pod     Pod information.
	 * @param int|string|null $id      Current item ID.
	 *
	 * @return mixed|null|string
	 *
	 * @since 2.0.0
	 */
	public function display( $value = null, $name = null, $options = null, $pod = null, $id = null ) {

		return $value;

	}

	/**
	 * Reformat a number to the way the value of the field is displayed.
	 *
	 * @param mixed|null      $value   Current value.
	 * @param string|null     $name    Field name.
	 * @param array|null      $options Field options.
	 * @param array|null      $pod     Pod information.
	 * @param int|string|null $id      Current item ID.
	 *
	 * @return string|null
	 * @since 2.0.0
	 */
	public function format( $value = null, $name = null, $options = null, $pod = null, $id = null ) {

		return $value;

	}

	/**
	 * Customize output of the form field
	 *
	 * @param string|null     $name    Field name.
	 * @param mixed|null      $value   Current value.
	 * @param array|null      $options Field options.
	 * @param array|null      $pod     Pod information.
	 * @param int|string|null $id      Current item ID.
	 *
	 * @since 2.0.0
	 */
	public function input( $name, $value = null, $options = null, $pod = null, $id = null ) {

		$options = (array) $options;

		$form_field_type = PodsForm::$field_type;

		if ( is_array( $value ) ) {
			$value = implode( ' ', $value );
		}

		pods_view( PODS_DIR . 'ui/fields/text.php', compact( array_keys( get_defined_vars() ) ) );

		/*
		 * @todo Eventually use this code
		$options = (array) $options;

		$type = pods_v( 'type', $options, static::$type );

		$args = compact( array_keys( get_defined_vars() ) );
		$args = (object) $args;

		$this->render_input_script( $args );
		*/

	}

	/**
	 * Render input script for Pods DFV
	 *
	 * @param array|object $args    {
	 *     Field information arguments.
	 *
	 *     @type string     $name            Field name.
	 *     @type string     $type            Field type.
	 *     @type array      $options         Field options.
	 *     @type mixed      $value           Current value.
	 *     @type array      $pod             Pod information.
	 *     @type int|string $id              Current item ID.
	 *     @type string     $form_field_type HTML field type.
	 * }
	 */
	public function render_input_script( $args ) {

		if ( is_array( $args ) ) {
			$args = (object) $args;
		}

		$script_content = wp_json_encode( $this->build_dfv_field_data( $args ), JSON_HEX_TAG );
		?>
		<div class="pods-form-ui-field pods-dfv-field">
			<?php // @codingStandardsIgnoreLine ?>
			<script type="application/json" class="pods-dfv-field-data"><?php echo $script_content; ?></script>
		</div>
		<?php

	}

	/**
	 * Build field data for Pods DFV
	 *
	 * @param object $args            {
	 *     Field information arguments.
	 *
	 *     @type string     $name            Field name.
	 *     @type string     $type            Field type.
	 *     @type array      $options         Field options.
	 *     @type mixed      $value           Current value.
	 *     @type array      $pod             Pod information.
	 *     @type int|string $id              Current item ID.
	 *     @type string     $form_field_type HTML field type.
	 * }
	 *
	 * @return array
	 */
	public function build_dfv_field_data( $args ) {

		// Handle DFV options.
		$args->options = $this->build_dfv_field_options( $args->options, $args );

		// Handle DFV attributes.
		$attributes = PodsForm::merge_attributes( array(), $args->name, $args->type, $args->options );
		$attributes = $this->build_dfv_field_attributes( $attributes, $args );
		$attributes = array_map( 'esc_attr', $attributes );

		// Build DFV field data.
		$data = array(
			'htmlAttr'      => array(
				'id'         => $attributes['id'],
				'class'      => $attributes['class'],
				'name'       => $attributes['name'],
				'name_clean' => $attributes['data-name-clean'],
			),
			'fieldType'     => $args->type,
			'fieldItemData' => $this->build_dfv_field_item_data( $args ),
			'fieldConfig'   => $this->build_dfv_field_config( $args ),
		);

		/**
		 * Filter Pods DFV field data to further customize functionality.
		 *
		 * @since 2.7.0
		 *
		 * @param array  $data       DFV field data
		 * @param object $args       {
		 *     Field information arguments.
		 *
		 *     @type string     $name            Field name.
		 *     @type string     $type            Field type.
		 *     @type array      $options         Field options.
		 *     @type mixed      $value           Current value.
		 *     @type array      $pod             Pod information.
		 *     @type int|string $id              Current item ID.
		 *     @type string     $form_field_type HTML field type.
		 * }
		 *
		 * @param array  $attributes HTML attributes
		 */
		$data = apply_filters( 'pods_field_dfv_data', $data, $args, $attributes );

		return $data;

	}

	/**
	 * Build field options and handle any validation/customization for Pods DFV
	 *
	 * @param array  $options Field options.
	 * @param object $args    {
	 *     Field information arguments.
	 *
	 *     @type string     $name            Field name.
	 *     @type string     $type            Field type.
	 *     @type array      $options         Field options.
	 *     @type mixed      $value           Current value.
	 *     @type array      $pod             Pod information.
	 *     @type int|string $id              Current item ID.
	 *     @type string     $form_field_type HTML field type.
	 * }
	 *
	 * @return array
	 */
	public function build_dfv_field_options( $options, $args ) {

		return $options;

	}

	/**
	 * Build field HTML attributes for Pods DFV.
	 *
	 * @param array  $attributes Default HTML attributes from field and PodsForm::merge_attributes.
	 * @param object $args       {
	 *     Field information arguments.
	 *
	 *     @type string     $name            Field name.
	 *     @type string     $type            Field type.
	 *     @type array      $options         Field options.
	 *     @type mixed      $value           Current value.
	 *     @type array      $pod             Pod information.
	 *     @type int|string $id              Current item ID.
	 *     @type string     $form_field_type HTML field type.
	 * }
	 *
	 * @return array
	 */
	public function build_dfv_field_attributes( $attributes, $args ) {

		return $attributes;

	}

	/**
	 * Build field config for Pods DFV using field options.
	 *
	 * This is for customizing the options and adding output-specific config values.
	 *
	 * @param object $args {
	 *     Field information arguments.
	 *
	 *     @type string     $name            Field name.
	 *     @type string     $type            Field type.
	 *     @type array      $options         Field options.
	 *     @type mixed      $value           Current value.
	 *     @type array      $pod             Pod information.
	 *     @type int|string $id              Current item ID.
	 *     @type string     $form_field_type HTML field type.
	 * }
	 *
	 * @return array
	 */
	public function build_dfv_field_config( $args ) {

		$config = $args->options;

		unset( $config['data'] );

		$config['item_id'] = (int) $args->id;

		return $config;

	}

	/**
	 * Build array of item data for Pods DFV.
	 *
	 * @param object $args {
	 *     Field information arguments.
	 *
	 *     @type string     $name            Field name.
	 *     @type string     $type            Field type.
	 *     @type array      $options         Field options.
	 *     @type mixed      $value           Current value.
	 *     @type array      $pod             Pod information.
	 *     @type int|string $id              Current item ID.
	 *     @type string     $form_field_type HTML field type.
	 * }
	 *
	 * @return array
	 */
	public function build_dfv_field_item_data( $args ) {

		$data = array();

		if ( ! empty( $args->options['data'] ) && is_array( $args->options['data'] ) ) {
			$data = $args->options['data'];
		}

		return $data;

	}

	/**
	 * Get the data from the field.
	 *
	 * @param string|null     $name    Field name.
	 * @param mixed|null      $value   Current value.
	 * @param array|null      $options Field options.
	 * @param array|null      $pod     Pod information.
	 * @param int|string|null $id      Current item ID.
	 * @param boolean         $in_form Whether we are in the form context.
	 *
	 * @return array Array of possible field data.
	 *
	 * @since 2.0.0
	 */
	public function data( $name, $value = null, $options = null, $pod = null, $id = null, $in_form = true ) {

		return (array) $value;

	}

	/**
	 * Build regex necessary for JS validation.
	 *
	 * @param mixed|null      $value   Current value.
	 * @param string|null     $name    Field name.
	 * @param array|null      $options Field options.
	 * @param array|null      $pod     Pod information.
	 * @param int|string|null $id      Current item ID.
	 *
	 * @return string|false
	 *
	 * @since 2.0.0
	 */
	public function regex( $value = null, $name = null, $options = null, $pod = null, $id = null ) {

		return false;

	}

	/**
	 * Validate a value before it's saved.
	 *
	 * @param mixed|null      $value   Current value.
	 * @param string|null     $name    Field name.
	 * @param array|null      $options Field options.
	 * @param array|null      $fields  Pod fields.
	 * @param array|null      $pod     Pod information.
	 * @param int|string|null $id      Current item ID.
	 * @param array|null      $params  Additional parameters.
	 *
	 * @return bool
	 *
	 * @since 2.0.0
	 */
	public function validate( $value, $name = null, $options = null, $fields = null, $pod = null, $id = null, $params = null ) {

		return true;

	}

	/**
	 * Change the value or perform actions after validation but before saving to the DB
	 *
	 * @param mixed|null      $value   Current value.
	 * @param int|string|null $id      Current Item ID.
	 * @param string|null     $name    Field name.
	 * @param array|null      $options Field options.
	 * @param array|null      $fields  Pod fields.
	 * @param array|null      $pod     Pod information.
	 * @param array|null      $params  Additional parameters.
	 *
	 * @return mixed
	 *
	 * @since 2.0.0
	 */
	public function pre_save( $value, $id = null, $name = null, $options = null, $fields = null, $pod = null, $params = null ) {

		return $value;

	}

	/**
	 * Save the value to the DB
	 *
	 * @param mixed|null      $value   Current value.
	 * @param int|string|null $id      Current Item ID.
	 * @param string|null     $name    Field name.
	 * @param array|null      $options Field options.
	 * @param array|null      $fields  Pod fields.
	 * @param array|null      $pod     Pod information.
	 * @param array|null      $params  Additional parameters.
	 *
	 * @return bool|null Whether the value was saved, returning null means no save needed to occur
	 *
	 * @since 2.3.0
	 */
	public function save( $value, $id = null, $name = null, $options = null, $fields = null, $pod = null, $params = null ) {

		return null;

	}

	/**
	 * Perform actions after saving to the DB
	 *
	 * @param mixed|null      $value   Current value.
	 * @param int|string|null $id      Current Item ID.
	 * @param string|null     $name    Field name.
	 * @param array|null      $options Field options.
	 * @param array|null      $fields  Pod fields.
	 * @param array|null      $pod     Pod information.
	 * @param array|null      $params  Additional parameters.
	 *
	 * @since 2.0.0
	 */
	public function post_save( $value, $id = null, $name = null, $options = null, $fields = null, $pod = null, $params = null ) {

		// Subclasses utilize this method if needed.
	}

	/**
	 * Perform actions before deleting from the DB
	 *
	 * @param int|string|null $id      Current Item ID.
	 * @param string|null     $name    Field name.
	 * @param array|null      $options Field options.
	 * @param array|null      $pod     Pod information.
	 *
	 * @since 2.0.0
	 */
	public function pre_delete( $id = null, $name = null, $options = null, $pod = null ) {

		// Subclasses utilize this method if needed.
	}

	/**
	 * Delete the value from the DB
	 *
	 * @param int|string|null $id      Current Item ID.
	 * @param string|null     $name    Field name.
	 * @param array|null      $options Field options.
	 * @param array|null      $pod     Pod information.
	 *
	 * @since 2.3.0
	 */
	public function delete( $id = null, $name = null, $options = null, $pod = null ) {

		// Subclasses utilize this method if needed.
	}

	/**
	 * Perform actions after deleting from the DB
	 *
	 * @param int|string|null $id      Current Item ID.
	 * @param string|null     $name    Field name.
	 * @param array|null      $options Field options.
	 * @param array|null      $pod     Pod information.
	 *
	 * @since 2.0.0
	 */
	public function post_delete( $id = null, $name = null, $options = null, $pod = null ) {

		// Subclasses utilize this method if needed.
	}

	/**
	 * Customize the Pods UI manage table column output
	 *
	 * @param int|string|null $id      Current Item ID.
	 * @param mixed|null      $value   Current value.
	 * @param string|null     $name    Field name.
	 * @param array|null      $options Field options.
	 * @param array|null      $fields  Pod fields.
	 * @param array|null      $pod     Pod information.
	 *
	 * @return string Value to be shown in the UI
	 *
	 * @since 2.0.0
	 */
	public function ui( $id, $value, $name = null, $options = null, $fields = null, $pod = null ) {

		return $this->display( $value, $name, $options, $pod, $id );

	}

	/**
	 * Check if the field is required.
	 *
	 * @param array $options Field options.
	 *
	 * @return bool
	 *
	 * @since 2.7.18
	 */
	public function is_required( $options ) {
		return filter_var( pods_v( 'required', $options, false ), FILTER_VALIDATE_BOOLEAN );
	}

	/**
	 * Strip HTML based on options.
	 *
	 * @param string|array $value   Field value.
	 * @param array|null   $options Field options.
	 *
	 * @return string
	 */
	public function strip_html( $value, $options = null ) {

		if ( is_array( $value ) ) {
			// @codingStandardsIgnoreLine
			$value = @implode( ' ', $value );
		}

		$value = trim( $value );

		if ( empty( $value ) ) {
			return $value;
		}

		$options = (array) $options;

		// Strip HTML
		if ( 1 === (int) pods_v( static::$type . '_allow_html', $options, 0 ) ) {
			$allowed_html_tags = '';

			if ( 0 < strlen( pods_v( static::$type . '_allowed_html_tags', $options ) ) ) {
				$allowed_tags = pods_v( static::$type . '_allowed_html_tags', $options );
				$allowed_tags = trim( str_replace( array( '<', '>', ',' ), ' ', $allowed_tags ) );
				$allowed_tags = explode( ' ', $allowed_tags );
				$allowed_tags = array_unique( array_filter( $allowed_tags ) );

				if ( ! empty( $allowed_tags ) ) {
					$allowed_html_tags = '<' . implode( '><', $allowed_tags ) . '>';
				}
			}

			if ( ! empty( $allowed_html_tags ) ) {
				$value = strip_tags( $value, $allowed_html_tags );
			}
		} else {
			$value = strip_tags( $value );
		}

		// Strip shortcodes
		if ( 0 === (int) pods_v( static::$type . '_allow_shortcode', $options ) ) {
			$value = strip_shortcodes( $value );
		}

		return $value;
	}

	/**
	 * Placeholder function to allow var_export() use with classes.
	 *
	 * @param array $properties Properties to export.
	 *
	 * @return void
	 */
	public static function __set_state( $properties ) {

	}

}
