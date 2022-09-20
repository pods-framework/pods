<?php

use Pods\Whatsit\Field;
use Pods\Whatsit\Pod;
use Pods\Whatsit\Value_Field;
use Pod as Pod_Deprecated;

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
	 * @param array|Pods|null $pod     Pod data or the Pods object.
	 * @param int|string|null $id      Current item ID.
	 *
	 * @since 2.0.0
	 */
	public function input( $name, $value = null, $options = null, $pod = null, $id = null ) {
		$options = ( is_array( $options ) || is_object( $options ) ) ? $options : (array) $options;

		$type = pods_v( 'type', $options, static::$type );

		$args = compact( array_keys( get_defined_vars() ) );
		$args = (object) $args;

		$this->render_input_script( $args );
	}

	/**
	 * Render input script for Pods DFV
	 *
	 * @param array|object $args    {
	 *     Field information arguments.
	 *
	 *     @type string          $name            Field name.
	 *     @type string          $type            Field type.
	 *     @type array           $options         Field options.
	 *     @type Field|null      $field           Field object (if provided).
	 *     @type mixed           $value           Current value.
	 *     @type array|Pods|null $pod             Pod data or the Pods object.
	 *     @type int|string      $id              Current item ID.
	 *     @type string          $form_field_type HTML field type.
	 * }
	 */
	public function render_input_script( $args ) {
		// Only show placeholder text if in REST API block preview.
		if ( wp_is_json_request() && did_action( 'rest_api_init' ) ) {
			return '<em>[' . esc_html__( 'This is a placeholder. Filters and form fields are not included in block previews.', 'pods' ) . ']</em>';
		}

		pods_form_enqueue_script( 'pods-dfv' );

		if ( is_array( $args ) ) {
			$args = (object) $args;
		}

		// Detect field object being passed to the $options array upstream.
		if ( ! empty( $args->options['_field_object'] ) ) {
			$args->field   = $args->options['_field_object'];

			unset( $args->options['_field_object'] );
		}

		// Update options so it's as expected.
		if ( ! empty( $args->field ) ) {
			$args->options = pods_config_merge_data( $args->options, clone $args->field );
		}

		// Remove potential 2.8 beta fragments.
		if ( ! empty( $args->options['pod_data'] ) ) {
			unset( $args->options['pod_data'] );
		}

		$disable_dfv = ! empty( $args->options['disable_dfv'] );

		$field_class = "pods-form-ui-field pods-dfv-field";

		if ( ! $disable_dfv ) {
			$field_class .= ' pods-dfv-field--unloaded';
		}

		$pod_name   = '';
		$item_id    = 0;
		$group_name = '';

		if ( ! empty( $args->pod ) ) {
			if ( $args->pod instanceof Pods || $args->pod instanceof Pod_Deprecated ) {
				$pod_name = $args->pod->pod_data['name'];
			} elseif ( $args->pod instanceof Pod || is_array( $args->pod ) ) {
				$pod_name = $args->pod['name'];
			}
		}

		if ( isset( $args->id ) && '' !== $args->id ) {
			$item_id = $args->id;
		}

		if ( $args->options instanceof Field ) {
			$group_name = $args->options->get_group_name();
		}

		if ( empty( $group_name ) ) {
			$group_name = $pod_name;
		}

		$dfv_field_data = $this->build_dfv_field_data( $args );
		$script_content = wp_json_encode( $dfv_field_data, JSON_HEX_TAG );
		?>
		<div class="<?php echo esc_attr( $field_class ); ?>">
			<?php if ( ! $disable_dfv ) : ?>
				<span class="pods-dfv-field__loading-indicator" role="progressbar"></span>
			<?php endif; ?>
			<?php
				// Important! The script tag must be all on one line or wptexturize will eat it up :( the regex matching breaks.
			?>
			<script type="application/json" class="pods-dfv-field-data" data-pod="<?php echo esc_attr( $pod_name ); ?>" data-group="<?php echo esc_attr( $group_name ); ?>" data-item-id="<?php echo esc_attr( $item_id ); ?>" data-form-counter="<?php echo esc_attr( PodsForm::$form_counter ); ?>"><?php
				// @codingStandardsIgnoreLine
				echo $script_content;
			?></script>
		</div>
		<?php

	}

	/**
	 * Build field data for Pods DFV
	 *
	 * @param object $args            {
	 *     Field information arguments.
	 *
	 *     @type string       $name            Field name.
	 *     @type string       $type            Field type.
	 *     @type array        $options         Field options.
	 *     @type Field|null   $field           Field object (if provided).
	 *     @type mixed        $value           Current value.
	 *     @type array        $pod             Pod information.
	 *     @type int|string   $id              Current item ID.
	 *     @type string       $form_field_type HTML field type.
	 * }
	 *
	 * @return array
	 */
	public function build_dfv_field_data( $args ) {
		$options = $args->options;

		// Handle DFV options.
		$args->options = $this->build_dfv_field_options( $options, $args );

		// Handle DFV attributes.
		$attributes = PodsForm::merge_attributes( array(), $args->name, $args->type, $args->options );
		$attributes = $this->build_dfv_field_attributes( $attributes, $args );
		$attributes = array_map( 'esc_attr', $attributes );

		$default_value = '';

		if ( 'multi' === pods_v( $args->type . '_format_type' ) ) {
			$default_value = [];
		}

		// Build DFV field data.
		$data = [
			'htmlAttr'      => [
				'id'         => $attributes['id'],
				'class'      => $attributes['class'],
				'name'       => $attributes['name'],
				'name_clean' => $attributes['data-name-clean'],
			],
			'fieldType'     => $args->type,
			'fieldItemData' => $this->build_dfv_field_item_data( $args ),
			'fieldConfig'   => $this->build_dfv_field_config( $args ),
			'fieldEmbed'    => true,
			'fieldValue'    => isset( $args->value ) ? $args->value : PodsForm::default_value( $default_value, $args->type, pods_v( 'name', $options, $args->name ), $options, $args->pod, $args->id ),
		];

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
	 *     @type string       $name            Field name.
	 *     @type string       $type            Field type.
	 *     @type array        $options         Field options.
	 *     @type Field|null   $field         Field object (if provided).
	 *     @type mixed        $value           Current value.
	 *     @type array        $pod             Pod information.
	 *     @type int|string   $id              Current item ID.
	 *     @type string       $form_field_type HTML field type.
	 * }
	 *
	 * @return array
	 */
	public function build_dfv_field_config( $args ) {
		if ( $args->options instanceof Field ) {
			$config = $args->options->export();

			$config['repeatable']                  = $args->options->is_repeatable();
			$config['repeatable_add_new_label']    = $args->options->get_arg( 'repeatable_add_new_label', __( 'Add New', 'pods' ), true );
			$config['repeatable_reorder']          = filter_var( $args->options->get_arg( 'repeatable_reorder', true ), FILTER_VALIDATE_BOOLEAN );
			$config['repeatable_limit']            = $args->options->get_limit();
			$config['repeatable_format']           = $args->options->get_arg( 'repeatable_format', 'default', true );
			$config['repeatable_format_separator'] = $args->options->get_arg( 'repeatable_format_separator', ', ', true );
		} else {
			$config = (array) $args->options;
		}

		// Backcompat readonly argument handling.
		if ( isset( $config['readonly'] ) ) {
			if ( ! isset( $config['read_only'] ) ) {
				$config['read_only'] = (int) $config['readonly'];
			}

			unset( $config['readonly'] );
		}

		unset( $config['data'] );

		$config['item_id'] = (int) $args->id;

		// Support passing missing options.
		$check_missing = [
			'type',
			'name',
			'label',
			'id',
		];

		// Fix weird serialization issues.
		foreach ( $config as $key => $value ) {
			if ( 'a:0:{}' === $value ) {
				$config[ $key ] = [];
			}
		}

		foreach ( $check_missing as $missing_name ) {
			if ( ! empty( $args->{$missing_name} ) ) {
				$config[ $missing_name ] = $args->{$missing_name};
			}
		}

		// Set up default placeholder option.
		if ( ! isset( $config['placeholder'] ) || ! is_string( $config['placeholder'] ) ) {
			$config['placeholder'] = '';
		}

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

		if ( ! empty( $args->options['fieldItemData'] ) && is_array( $args->options['fieldItemData'] ) ) {
			$data = $args->options['fieldItemData'];
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

		/**
		 * Filter field validation return.
		 *
		 * @since 2.7.24
		 *
		 * @param true            $true    Default validation return.
		 * @param mixed|null      $value   Current value.
		 * @param string|null     $name    Field name.
		 * @param array|null      $options Field options.
		 * @param array|null      $fields  Pod fields.
		 * @param array|null      $pod     Pod information.
		 * @param int|string|null $id      Current item ID.
		 * @param array|null      $params  Additional parameters.
		 */
		$validate = apply_filters( 'pods_field_validate_' . static::$type, true, $value, $name, $options, $fields, $pod, $id, $params );

		if ( ! is_bool( $validate ) ) {
			$validate = (array) $validate;
		}

		return $validate;

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
			foreach ( $value as $k => $v ) {
				$value[ $k ] = $this->strip_html( $v, $options );
			}

			return $value;
		}

		if ( empty( $value ) ) {
			return $value;
		}

		if ( $options ) {
			$options = ( is_array( $options ) || is_object( $options ) ) ? $options : (array) $options;

			// Strip HTML
			if ( 1 === (int) pods_v( static::$type . '_allow_html', $options, 0 ) ) {
				$allowed_tags = pods_v( static::$type . '_allowed_html_tags', $options );

				if ( 0 < strlen( $allowed_tags ) ) {
					$allowed_tags = trim( str_replace( [ '<', '>', ',' ], ' ', $allowed_tags ) );
					$allowed_tags = explode( ' ', $allowed_tags );
					$allowed_tags = array_unique( array_filter( $allowed_tags ) );

					if ( ! empty( $allowed_tags ) ) {
						$allowed_html_tags = '<' . implode( '><', $allowed_tags ) . '>';

						$value = strip_tags( $value, $allowed_html_tags );
					}
				}

				return $value;
			}
		}

		return strip_tags( $value );
	}

	/**
	 * Strip shortcodes based on options.
	 *
	 * @since 2.8.0
	 *
	 * @param string|array     $value   The field value.
	 * @param array|Field|null $options The field options.
	 *
	 * @return string The field value.
	 */
	public function strip_shortcodes( $value, $options = null ) {
		if ( is_array( $value ) ) {
			foreach ( $value as $k => $v ) {
				$value[ $k ] = $this->strip_shortcodes( $v, $options );
			}

			return $value;
		}

		if ( empty( $value ) ) {
			return $value;
		}

		if ( $options ) {
			$options = ( is_array( $options ) || is_object( $options ) ) ? $options : (array) $options;

			// Check if we should strip shortcodes.
			if ( 1 === (int) pods_v( static::$type . '_allow_shortcode', $options, 0 ) ) {
				return $value;
			}
		}

		return strip_shortcodes( $value );
	}

	/**
	 * Trim whitespace based on options.
	 *
	 * @since 2.8.0
	 *
	 * @param string|array     $value   The field value.
	 * @param array|Field|null $options The field options.
	 *
	 * @return string The field value.
	 */
	public function trim_whitespace( $value, $options = null ) {
		if ( is_array( $value ) ) {
			foreach ( $value as $k => $v ) {
				$value[ $k ] = $this->trim_whitespace( $v, $options );
			}

			return $value;
		}

		if ( $options ) {
			$options = ( is_array( $options ) || is_object( $options ) ) ? $options : (array) $options;

			// Check if we should trim the content.
			if ( 0 === (int) pods_v( static::$type . '_trim', $options, 1 ) ) {
				return $value;
			}
		}

		return trim( $value );
	}

	/**
	 * Normalize the field value for the input.
	 *
	 * @param mixed       $value     The field value.
	 * @param Field|array $field     The field object or the field options array.
	 * @param string      $separator The separator to use if the field does not support multiple values.
	 *
	 * @return mixed The field normalized value.
	 */
	public function normalize_value_for_input( $value, $field, $separator = ' ' ) {
		if (
			(
				(
					$field instanceof Field
					|| $field instanceof Value_Field
				)
				&& $field->is_repeatable()
			)
			|| (
				is_array( $field )
				&& 1 === (int) pods_v( 'repeatable', $field )
				&& (
					'wysiwyg' !== pods_v( 'type', $field )
					|| 'tinymce' !== pods_v( 'wysiwyg_editor', $field, 'tinymce', true )
				)
			)
		) {
			if ( ! is_array( $value ) ) {
				if ( '' === $value || null === $value ) {
					$value = [
						'',
					];
				} else {
					$value = (array) $value;
				}
			}

			return $value;
		}

		if ( ! is_array( $value ) ) {
			return $value;
		}

		return implode( $separator, $value );
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
