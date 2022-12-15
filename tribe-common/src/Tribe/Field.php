<?php

// Don't load directly

use Tribe\Admin\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( ! class_exists( 'Tribe__Field' ) ) {
	/**
	 * helper class that creates fields for use in Settings, MetaBoxes, Users, anywhere.
	 * Instantiate it whenever you need a field
	 *
	 */
	class Tribe__Field {

		/**
		 * the field's id
		 * @var string
		 */
		public $id;

		/**
		 * the field's name (also known as it's label)
		 * @var string
		 */
		public $name;

		/**
		 * the fieldset attributes
		 * @var array
		 */
		public $fieldset_attributes;

		/**
		 * the field attributes
		 * @var array
		 */
		public $attributes;

		/**
		 * the field's arguments
		 * @var array
		 */
		public $args;

		/**
		 * field defaults (static)
		 * @var array
		 */
		public $defaults;

		/**
		 * valid field types (static)
		 * @var array
		 */
		public $valid_field_types;


		/**
		 * Class constructor
		 *
		 * @param string     $id    the field id
		 * @param array      $field the field settings
		 * @param null|mixed $value the field's current value
		 *
		 * @return void
		 */
		public function __construct( $id, $field, $value = null ) {

			// setup the defaults
			$this->defaults = [
				'type'                => 'html',
				'name'                => $id,
				'fieldset_attributes' => [],
				'attributes'          => [],
				'class'               => null,
				'label'               => null,
				'label_attributes'    => null,
				'placeholder'         => null,
				'tooltip'             => null,
				'size'                => 'medium',
				'html'                => null,
				'error'               => false,
				'value'               => $value,
				'options'             => null,
				'conditional'         => true,
				'display_callback'    => null,
				'if_empty'            => null,
				'can_be_empty'        => false,
				'clear_after'         => true,
				'tooltip_first'       => false,
				'allow_clear'         => false,
			];

			// a list of valid field types, to prevent screwy behavior
			$this->valid_field_types = [
				'heading',
				'html',
				'text',
				'textarea',
				'wysiwyg',
				'radio',
				'checkbox_bool',
				'checkbox_list',
				'dropdown',
				'dropdown',
				'dropdown_select2', // Deprecated use `dropdown`
				'dropdown_chosen', // Deprecated use `dropdown`
				'license_key',
				'number',
				'wrapped_html',
				'email',
				'color',
				'image',
			];

			$this->valid_field_types = apply_filters( 'tribe_valid_field_types', $this->valid_field_types );

			// parse args with defaults and extract them
			$args = wp_parse_args( $field, $this->defaults );

			// sanitize the values just to be safe
			$id         = esc_attr( $id );
			$type       = esc_attr( $args['type'] );
			$name       = esc_attr( $args['name'] );
			$placeholder = esc_attr( $args['placeholder'] );
			$class = $this->sanitize_class_attribute( $args['class'] );
			$label      = wp_kses(
				$args['label'], [
					'a'      => [ 'href' => [], 'title' => [] ],
					'br'     => [],
					'em'     => [],
					'strong' => [],
					'b'      => [],
					'i'      => [],
					'u'      => [],
					'img'    => [
						'title' => [],
						'src'   => [],
						'alt'   => [],
					],
					'span'      => [ 'class' => [] ],
				]
			);
			$label_attributes = $args['label_attributes'];
			$tooltip    = wp_kses(
				$args['tooltip'], [
					'a'      => [  'class' => [], 'href' => [], 'title' => [], 'target' => [], 'rel' => [] ],
					'br'     => [],
					'em'     => [ 'class' => [] ],
					'strong' => [ 'class' => [] ],
					'b'      => [ 'class' => [] ],
					'i'      => [ 'class' => [] ],
					'u'      => [ 'class' => [] ],
					'img'    => [
						'class' => [],
						'title' => [],
						'src'   => [],
						'alt'   => [],
					],
					'code'   => [ 'span' => [] ],
					'span'   => [ 'class' => [] ],
				]
			);
			$fieldset_attributes = [];
			if ( is_array( $args['fieldset_attributes'] ) ) {
				foreach ( $args['fieldset_attributes'] as $key => $val ) {
					$fieldset_attributes[ $key ] = esc_attr( $val );
				}
			}
			$attributes = [];
			if ( is_array( $args['attributes'] ) ) {
				foreach ( $args['attributes'] as $key => $val ) {
					$attributes[ $key ] = esc_attr( $val );
				}
			}
			if ( is_array( $args['options'] ) ) {
				$options = [];
				foreach ( $args['options'] as $key => $val ) {
					$options[ $key ] = $val;
				}
			} else {
				$options = $args['options'];
			}
			$size             = esc_attr( $args['size'] );
			$html             = $args['html'];
			$error            = (bool) $args['error'];
			$value            = is_array( $value ) ? array_map( 'esc_attr', $value ) : esc_attr( $value );
			$conditional      = $args['conditional'];
			$display_callback = $args['display_callback'];
			$if_empty         = is_string( $args['if_empty'] ) ? trim( $args['if_empty'] ) : $args['if_empty'];
			$can_be_empty     = (bool) $args['can_be_empty'];
			$clear_after      = (bool) $args['clear_after'];
			$tooltip_first    = (bool) $args['tooltip_first'];
			$allow_clear      = (bool) $args['allow_clear'];

			// set the ID
			$this->id = apply_filters( 'tribe_field_id', $id );

			// set each instance variable and filter
			foreach ( array_keys( $this->defaults ) as $key ) {
				$this->{$key} = apply_filters( 'tribe_field_' . $key, $$key, $this->id );
			}

			// epicness
			$this->do_field();
		}

		/**
		 * Determines how to handle this field's creation
		 * either calls a callback function or runs this class' course of action
		 * logs an error if it fails
		 *
		 * @return void
		 */
		public function do_field() {

			if ( $this->conditional ) {

				if ( $this->display_callback && is_callable( $this->display_callback ) ) {

					// if there's a callback, run it
					call_user_func( $this->display_callback );

				} elseif ( in_array( $this->type, $this->valid_field_types ) ) {

					// the specified type exists, run the appropriate method
					$field = call_user_func( [ $this, $this->type ] );

					// filter the output
					$field = apply_filters( 'tribe_field_output_' . $this->type, $field, $this->id, $this );
					echo apply_filters( 'tribe_field_output_' . $this->type . '_' . $this->id, $field, $this->id, $this );

				} else {

					// fail, log the error
					Tribe__Debug::debug( esc_html__( 'Invalid field type specified', 'tribe-common' ), $this->type, 'notice' );

				}
			}
		}

		/**
		 * returns the field's start
		 *
		 * @return string the field start
		 */
		public function do_field_start() {
			$return = '<fieldset id="tribe-field-' . $this->id . '"';
			$return .= ' class="tribe-field tribe-field-' . $this->type;
			$return .= ( $this->error ) ? ' tribe-error' : '';
			$return .= ( $this->size ) ? ' tribe-size-' . $this->size : '';
			$return .= ( $this->class ) ? ' ' . $this->class . '"' : '"';
			$return .= ( $this->fieldset_attributes ) ? ' ' . $this->do_fieldset_attributes() : '';
			$return .= '>';

			return apply_filters( 'tribe_field_start', $return, $this->id, $this->type, $this->error, $this->class, $this );
		}

		/**
		 * returns the field's end
		 *
		 * @return string the field end
		 */
		public function do_field_end() {
			$return = '</fieldset>';
			$return .= ( $this->clear_after ) ? '<div class="clear"></div>' : '';

			return apply_filters( 'tribe_field_end', $return, $this->id, $this );
		}

		/**
		 * returns the field's label
		 *
		 * @return string the field label
		 */
		public function do_field_label() {
			$return = '';
			if ( $this->label ) {
				if ( isset( $this->label_attributes ) ) {
					$this->label_attributes['class'] = isset( $this->label_attributes['class'] ) ?
						implode( ' ', array_merge( [ 'tribe-field-label' ], $this->label_attributes['class'] ) ) :
						[ 'tribe-field-label' ];
					$this->label_attributes = $this->concat_attributes( $this->label_attributes );
				}
				$return = sprintf( '<legend class="tribe-field-label" %s>%s</legend>', $this->label_attributes, $this->label );
			}

			return apply_filters( 'tribe_field_label', $return, $this->label, $this );
		}

		/**
		 * returns the field's div start
		 *
		 * @return string the field div start
		 */
		public function do_field_div_start() {
			$return = '<div class="tribe-field-wrap">';

			if ( true === $this->tooltip_first ) {
				$return .= $this->do_tool_tip();
				// and empty it to avoid it from being printed again
				$this->tooltip = '';
			}

			return apply_filters( 'tribe_field_div_start', $return, $this );
		}

		/**
		 * returns the field's div end
		 *
		 * @return string the field div end
		 */
		public function do_field_div_end() {
			$return = $this->do_tool_tip();
			$return .= '</div>';

			return apply_filters( 'tribe_field_div_end', $return, $this );
		}

		/**
		 * returns the field's tooltip/description
		 *
		 * @return string the field tooltip
		 */
		public function do_tool_tip() {
			$return = '';
			if ( $this->tooltip ) {
				$return = '<p class="tooltip description">' . $this->tooltip . '</p>';
			}

			return apply_filters( 'tribe_field_tooltip', $return, $this->tooltip, $this );
		}

		/**
		 * returns the screen reader label
		 *
		 * @return string the screen reader label
		 */
		public function do_screen_reader_label() {
			$return = '';
			if ( $this->tooltip ) {
				$return = '<label class="screen-reader-text">' . $this->tooltip . '</label>';
			}

			return apply_filters( 'tribe_field_screen_reader_label', $return, $this->tooltip, $this );
		}

		/**
		 * returns the field's value
		 *
		 * @return string the field value
		 */
		public function do_field_value() {
			$return = '';
			if ( $this->value ) {
				$return = ' value="' . $this->value . '"';
			}

			return apply_filters( 'tribe_field_value', $return, $this->value, $this );
		}

		/**
		 * returns the field's name
		 *
		 * @param bool $multi
		 *
		 * @return string the field name
		 */
		public function do_field_name( $multi = false ) {
			$return = '';
			if ( $this->name ) {
				if ( $multi ) {
					$return = ' name="' . $this->name . '[]"';
				} else {
					$return = ' name="' . $this->name . '"';
				}
			}

			return apply_filters( 'tribe_field_name', $return, $this->name, $this );
		}

		/**
		 * returns the field's placeholder
		 *
		 * @return string the field value
		 */
		public function do_field_placeholder() {
			$return = '';
			if ( $this->placeholder ) {
				$return = ' placeholder="' . $this->placeholder . '"';
			}

			return apply_filters( 'tribe_field_placeholder', $return, $this->placeholder, $this );
		}

		/**
		 * Return a string of attributes for the field
		 *
		 * @return string
		 **/
		public function do_field_attributes() {
			$return = '';
			if ( ! empty( $this->attributes ) ) {
				foreach ( $this->attributes as $key => $value ) {
					$return .= ' ' . $key . '="' . $value . '"';
				}
			}

			return apply_filters( 'tribe_field_attributes', $return, $this->name, $this );
		}

		/**
		 * Return a string of attributes for the fieldset
		 *
		 * @return string
		 **/
		public function do_fieldset_attributes() {
			$return = '';
			if ( ! empty( $this->fieldset_attributes ) ) {
				foreach ( $this->fieldset_attributes as $key => $value ) {
					$return .= ' ' . $key . '="' . $value . '"';
				}
			}

			return apply_filters( 'tribe_fieldset_attributes', $return, $this->name, $this );
		}

		/**
		 * generate a heading field
		 *
		 * @return string the field
		 */
		public function heading() {
			$field = '<h3>' . $this->label . '</h3>';

			return $field;
		}

		/**
		 * generate an html field
		 *
		 * @return string the field
		 */
		public function html() {
			$field = $this->do_field_label();
			$field .= $this->html;

			return $field;
		}

		/**
		 * generate a simple text field
		 *
		 * @return string the field
		 */
		public function text() {
			$field = $this->do_field_start();
			$field .= $this->do_field_label();
			$field .= $this->do_field_div_start();
			$field .= '<input';
			$field .= ' type="text"';
			$field .= $this->do_field_name();
			$field .= $this->do_field_value();
			$field .= $this->do_field_placeholder();
			$field .= $this->do_field_attributes();
			$field .= '/>';
			$field .= $this->do_screen_reader_label();
			$field .= $this->do_field_div_end();
			$field .= $this->do_field_end();

			return $field;
		}

		/**
		 * generate a textarea field
		 *
		 * @return string the field
		 */
		public function textarea() {
			$field = $this->do_field_start();
			$field .= $this->do_field_label();
			$field .= $this->do_field_div_start();
			$field .= '<textarea';
			$field .= $this->do_field_name();
			$field .= $this->do_field_attributes();
			$field .= '>';
			$field .= esc_html( stripslashes( $this->value ) );
			$field .= '</textarea>';
			$field .= $this->do_screen_reader_label();
			$field .= $this->do_field_div_end();
			$field .= $this->do_field_end();

			return $field;
		}

		/**
		 * generate a wp_editor field
		 *
		 * @return string the field
		 */
		public function wysiwyg() {
			$settings = [
				'teeny'   => true,
				'wpautop' => true,
			];
			ob_start();
			wp_editor( html_entity_decode( ( $this->value ) ), $this->name, $settings );
			$editor = ob_get_clean();
			$field  = $this->do_field_start();
			$field .= $this->do_field_label();
			$field .= $this->do_field_div_start();
			$field .= $editor;
			$field .= $this->do_screen_reader_label();
			$field .= $this->do_field_div_end();
			$field .= $this->do_field_end();

			return $field;
		}

		/**
		 * generate a radio button field
		 *
		 * @return string the field
		 */
		public function radio() {
			$field = $this->do_field_start();
			$field .= $this->do_field_label();
			$field .= $this->do_field_div_start();
			if ( is_array( $this->options ) ) {
				foreach ( $this->options as $option_id => $title ) {
					$field_id = sprintf(
						'%1$s-%2$s',
						sanitize_html_class( trim( $this->id ) ),
						sanitize_html_class( trim( $option_id ) )
					);

					$field .= '<label title="' . esc_attr( strip_tags( $title ) ) . '">';
					$field .= '<input type="radio"';
					$field .= ' id="tribe-field-' . esc_attr( $field_id ) . '"';
					$field .= $this->do_field_name();
					$field .= ' value="' . esc_attr( $option_id ) . '" ' . checked( $this->value, $option_id, false ) . '/>';
					$field .= $title;
					$field .= '</label>';
				}
			} else {
				$field .= '<span class="tribe-error">' . esc_html__( 'No radio options specified', 'tribe-common' ) . '</span>';
			}
			$field .= $this->do_field_div_end();
			$field .= $this->do_field_end();

			return $field;
		}

		/**
		 * generate a checkbox_list field
		 *
		 * @return string the field
		 */
		public function checkbox_list() {
			$field = $this->do_field_start();
			$field .= $this->do_field_label();
			$field .= $this->do_field_div_start();

			if ( ! is_array( $this->value ) ) {
				if ( ! empty( $this->value ) ) {
					$this->value = [ $this->value ];
				} else {
					$this->value = [];
				}
			}

			if ( is_array( $this->options ) ) {
				foreach ( $this->options as $option_id => $title ) {
					$field .= '<label title="' . esc_attr( $title ) . '">';
					$field .= '<input type="checkbox"';
					$field .= $this->do_field_name( true );
					$field .= ' value="' . esc_attr( $option_id ) . '" ' . checked( in_array( $option_id, $this->value ), true, false ) . '/>';
					$field .= $title;
					$field .= '</label>';
				}
			} else {
				$field .= '<span class="tribe-error">' . esc_html__( 'No checkbox options specified', 'tribe-common' ) . '</span>';
			}
			$field .= $this->do_field_div_end();
			$field .= $this->do_field_end();

			return $field;
		}

		/**
		 * generate a boolean checkbox field
		 *
		 * @return string the field
		 */
		public function checkbox_bool() {
			$field = $this->do_field_start();
			$field .= $this->do_field_label();
			$field .= $this->do_field_div_start();
			$field .= '<input type="checkbox"';
			$field .= $this->do_field_name();
			$field .= ' value="1" ' . checked( $this->value, true, false );
			$field .= $this->do_field_attributes();
			$field .= '/>';
			$field .= $this->do_screen_reader_label();
			$field .= $this->do_field_div_end();
			$field .= $this->do_field_end();

			return $field;
		}

		/**
		 * generate a dropdown field
		 *
		 * @return string the field
		 */
		public function dropdown() {
			$field = $this->do_field_start();
			$field .= $this->do_field_label();
			$field .= $this->do_field_div_start();
			if ( is_array( $this->options ) && ! empty( $this->options ) ) {
				$field .= '<select';
				$field .= $this->do_field_name();
				$field .= " id='{$this->id}-select'";
				$field .= " class='tribe-dropdown'";
				if ( empty( $this->allow_clear ) ) {
					$field .= " data-prevent-clear='true'";
				}
				$field .= $this->do_field_attributes();
				$field .= '>';
				foreach ( $this->options as $option_id => $title ) {
					$field .= '<option value="' . esc_attr( $option_id ) . '"';
					if ( is_array( $this->value ) ) {
						$field .= isset( $this->value[0] ) ? selected( $this->value[0], $option_id, false ) : '';
					} else {
						$field .= selected( $this->value, $option_id, false );
					}
					$field .= '>' . esc_html( $title ) . '</option>';
				}
				$field .= '</select>';
				$field .= $this->do_screen_reader_label();
			} elseif ( $this->if_empty ) {
				$field .= '<span class="empty-field">' . (string) $this->if_empty . '</span>';
			} else {
				$field .= '<span class="tribe-error">' . esc_html__( 'No select options specified', 'tribe-common' ) . '</span>';
			}
			$field .= $this->do_field_div_end();
			$field .= $this->do_field_end();

			return $field;
		}

		/**
		 * generate a chosen dropdown field - the same as the
		 * regular dropdown but wrapped so it can have the
		 * right css class applied to it
		 *
		 * @deprecated
		 *
		 * @return string the field
		 */
		public function dropdown_chosen() {
			$field = $this->dropdown();

			return $field;
		}

		/**
		 * generate a select2 dropdown field - the same as the
		 * regular dropdown but wrapped so it can have the
		 * right css class applied to it
		 *
		 * @deprecated
		 *
		 * @return string the field
		 */
		public function dropdown_select2() {
			$field = $this->dropdown();

			return $field;
		}

		/**
		 * generate a license key field
		 *
		 * @return string the field
		 */
		public function license_key() {
			$field = $this->do_field_start();
			$field .= $this->do_field_label();
			$field .= $this->do_field_div_start();
			$field .= '<input';
			$field .= ' type="text"';
			$field .= $this->do_field_name();
			$field .= $this->do_field_value();
			$field .= $this->do_field_attributes();
			$field .= '/>';
			$field .= '<p class="license-test-results"><img src="' . esc_url( admin_url( 'images/wpspin_light.gif' ) ) . '" class="ajax-loading-license" alt="Loading" style="display: none"/>';
			$field .= '<span class="key-validity"></span>';
			$field .= $this->do_screen_reader_label();
			$field .= $this->do_field_div_end();
			$field .= $this->do_field_end();

			return $field;
		}

		/**
		 * Generate a color field.
		 *
		 * @since 5.0.0
		 *
		 * @return string The field.
		 */
		public function color() {

			tribe( Settings::class )->maybe_load_color_field_assets();

			$field = $this->do_field_start();
			$field .= $this->do_field_label();
			$field .= $this->do_field_div_start();
			$field .= '<input';
			$field .= ' type="text"';
			$field .= ' class="tec-admin__settings-color-field-input"';
			$field .= $this->do_field_name();
			$field .= $this->do_field_value();
			$field .= $this->do_field_attributes();
			$field .= '/>';
			$field .= $this->do_screen_reader_label();
			$field .= $this->do_field_div_end();
			$field .= $this->do_field_end();

			return $field;
		}

		/**
		 * Generate an image field.
		 *
		 * @since 5.0.0
		 *
		 * @return string The field.
		 */
		public function image() {

			tribe( Settings::class )->maybe_load_image_field_assets();

			$image_exists = ! empty( $this->value );
			$upload_image_text = esc_html__( 'Select Image', 'tribe-common' );
			$remove_image_text = esc_html__( 'Remove Image', 'tribe-common' );

			// Add default fieldset attributes if none exist.
			$image_fieldset_attributes = [
				'data-select-image-text' => esc_html__( 'Select an image', 'tribe-common' ),
				'data-use-image-text'    => esc_html__( 'Use this image', 'tribe-common' ),
			];
			$this->fieldset_attributes = array_merge( $image_fieldset_attributes, $this->fieldset_attributes );

			$field = $this->do_field_start();
			$field .= $this->do_field_label();
			$field .= $this->do_field_div_start();
			$field .= '<input';
			$field .= ' type="hidden"';
			$field .= ' class="tec-admin__settings-image-field-input"';
			$field .= $this->do_field_name();
			$field .= $this->do_field_value();
			$field .= $this->do_field_attributes();
			$field .= '/>';
			$field .= '<button type="button" class="button tec-admin__settings-image-field-btn-add">' . $upload_image_text . '</button>';
			$field .= '<div class="tec-admin__settings-image-field-image-container hidden">';
			if ( $image_exists ) {
				$field .= '<img src="' . esc_url( $this->value ) . '" />';
			}
			$field .= '</div>';
			$field .= '<button class="tec-admin__settings-image-field-btn-remove hidden">' . $remove_image_text . '</button>';
			$field .= $this->do_screen_reader_label();
			$field .= $this->do_field_div_end();
			$field .= $this->do_field_end();

			return $field;
		}

		/* deprecated camelCase methods */
		public function doField() {
			_deprecated_function( __METHOD__, '4.3', __CLASS__ . '::do_field' );
			return $this->do_field();
		}

		public function doFieldStart() {
			_deprecated_function( __METHOD__, '4.3', __CLASS__ . '::do_field_start' );
			return $this->do_field_start();
		}

		public function doFieldEnd() {
			_deprecated_function( __METHOD__, '4.3', __CLASS__ . '::do_field_end' );
			return $this->do_field_end();
		}

		public function doFieldLabel() {
			_deprecated_function( __METHOD__, '4.3', __CLASS__ . '::do_field_label' );
			return $this->do_field_label();
		}

		public function doFieldDivStart() {
			_deprecated_function( __METHOD__, '4.3', __CLASS__ . '::do_field_div_start' );
			return $this->do_field_div_start();
		}

		public function doFieldDivEnd() {
			_deprecated_function( __METHOD__, '4.3', __CLASS__ . '::do_field_div_end' );
			return $this->do_field_div_end();
		}

		public function doToolTip() {
			_deprecated_function( __METHOD__, '4.3', __CLASS__ . '::do_tool_tip' );
			return $this->do_tool_tip();
		}

		public function doFieldValue() {
			_deprecated_function( __METHOD__, '4.3', __CLASS__ . '::do_field_value' );
			return $this->do_field_value();
		}

		public function doFieldName( $multi = false ) {
			_deprecated_function( __METHOD__, '4.3', __CLASS__ . '::do_field_name' );
			return $this->do_field_name( $multi );
		}

		public function doFieldAttributes() {
			_deprecated_function( __METHOD__, '4.3', __CLASS__ . '::do_field_attributes' );
			return $this->do_field_attributes();
		}

		public function doScreenReaderLabel() {
			_deprecated_function( __METHOD__, '4.3', __CLASS__ . '::do_screen_reader_label' );
			return $this->do_screen_reader_label();
		}

		/**
		 * Generate a wrapped html field.
		 *
		 * This is useful to print some HTML that should be inline with the other fieldsets.
		 *
		 * @return string The field markup.
		 */
		public function wrapped_html() {
			$field = $this->do_field_start();
			$field .= $this->do_field_label();
			$field .= $this->do_field_div_start();
			$field .= $this->html;
			$field .= $this->do_field_div_start();
			$field .= $this->do_field_end();

			return $field;
		}

		/**
		 * Concatenates an array of attributes to use in HTML tags.
		 *
		 * Example usage:
		 *
		 *      $attrs = [ 'class' => ['one', 'two'], 'style' => 'color:red;' ];
		 *      printf ( '<p %s>%s</p>', tribe_concat_attributes( $attrs ), 'bar' );
		 *
		 *      // <p> class="one two" style="color:red;">bar</p>
		 *
		 * @param array $attributes An array of attributes in the format
		 *                          [<attribute1> => <value>, <attribute2> => <value>]
		 *                          where `value` can be a string or an array.
		 *
		 * @return string The concatenated attributes.
		 */
		protected function concat_attributes( array $attributes = [] ) {
			if ( empty( $attributes ) ) {
				return '';
			}

			$concat = [];
			foreach ( $attributes as $attribute => $value ) {
				if ( is_array( $value ) ) {
					$value = implode( ' ', $value );
				}
				$quote     = false !== strpos( $value, '"' ) ? "'" : '"';
				$concat[] = esc_attr( $attribute ) . '=' . $quote . esc_attr( $value ) . $quote;
			}

			return implode( ' ', $concat );
		}

		/**
		 * Generate an email address field
		 *
		 * @since 4.7.4
		 *
		 * @return string The field
		 */
		public function email() {
			$this->value = trim( $this->value );
			return $this->text();
		}

		/**
		 * Sanitizes a space-separated or array of classes.
		 *
		 * @since 4.7.7
		 *
		 * @param string|array $class A single class, a space-separated list of classes
		 *                            or an array of classes.
		 *
		 * @return string A space-separated list of classes.
		 */
		protected function sanitize_class_attribute( $class ) {
			$classes   = is_array( $class ) ? $class : explode( ' ', $class );
			$sanitized = array_map( 'sanitize_html_class', $classes );

			return implode( ' ', $sanitized );
		}
	} // end class
} // endif class_exists
