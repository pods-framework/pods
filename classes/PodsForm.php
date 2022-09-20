<?php

use Pods\Whatsit\Field;

/**
 * @package Pods
 */
class PodsForm {

	/**
	 * @var PodsForm
	 */
	protected static $instance = null;

	/**
	 * @var string
	 */
	public static $field = null;

	/**
	 * @var string
	 */
	public static $field_group = null;

	/**
	 * @var string
	 */
	public static $field_type = null;

	/**
	 * @var array
	 */
	public static $field_types = array();

	/**
	 * @var array
	 */
	public static $loaded = array();

	/**
	 * @var int
	 */
	public static $form_counter = 0;

	/**
	 * Singleton handling for a basic pods_form() request
	 *
	 * @return \PodsForm
	 *
	 * @since 2.3.5
	 */
	public static function init() {

		if ( ! is_object( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Master handler for all field / form methods
	 *
	 * @return \PodsForm
	 *
	 * @license http://www.gnu.org/licenses/gpl-2.0.html
	 * @since 2.0.0
	 */
	private function __construct() {

		add_action( 'admin_init', array( $this, 'admin_init' ), 14 );
	}

	/**
	 * Prevent clones
	 *
	 * @since 2.3.0
	 */
	private function __clone() {
		// Hulk smash
	}

	/**
	 * Output a field's label
	 *
	 * @since 2.0.0
	 */

	/**
	 * Output a field's label
	 *
	 * @param string $name    Field name
	 * @param string $label   Label text
	 * @param string $help    Help text
	 * @param array  $options Field options
	 *
	 * @return string Label HTML
	 *
	 * @since 2.0.0
	 */
	public static function label( $name, $label, $help = '', $options = null ) {

		if ( is_array( $label ) || is_object( $label ) ) {
			$options = $label;
			$label   = $options['label'];

			if ( empty( $label ) ) {
				$label = ucwords( str_replace( '_', ' ', $name ) );
			}

			$help = $options['help'];
		} else {
			$options = self::options( null, $options );
		}

		$label = apply_filters( 'pods_form_ui_label_text', $label, $name, $help, $options );
		$help  = apply_filters( 'pods_form_ui_label_help', $help, $name, $label, $options );

		ob_start();

		$name_clean      = self::clean( $name );
		$name_more_clean = self::clean( $name, true );

		$type                = 'label';
		$attributes          = array();
		$attributes['class'] = 'pods-form-ui-' . $type . ' pods-form-ui-' . $type . '-' . $name_more_clean;
		$attributes['for']   = ( false === strpos( $name_clean, 'pods-form-ui-' ) ? 'pods-form-ui-' : '' ) . $name_clean;
		$attributes          = self::merge_attributes( $attributes, $name, $type, $options, false );

		pods_view( PODS_DIR . 'ui/fields/_label.php', compact( array_keys( get_defined_vars() ) ) );

		$output = ob_get_clean();

		return apply_filters( "pods_form_ui_{$type}", $output, $name, $label, $help, $attributes, $options );
	}

	/**
	 * Output a Field Comment Paragraph
	 *
	 * @param string $name    Field name
	 * @param string $message Field comments
	 * @param array  $options Field options
	 *
	 * @return string Comment HTML
	 *
	 * @since 2.0.0
	 */
	public static function comment( $name, $message = null, $options = null ) {

		$options = self::options( null, $options );

		$name_more_clean = self::clean( $name, true );

		if ( ! empty( $options['description'] ) ) {
			$message = $options['description'];
		} elseif ( empty( $message ) ) {
			return '';
		}

		$message = apply_filters( 'pods_form_ui_comment_text', $message, $name, $options );

		ob_start();

		$type                = 'comment';
		$attributes          = array();
		$attributes['class'] = 'description pods-form-ui-' . $type . ' pods-form-ui-' . $type . '-' . $name_more_clean;
		$attributes          = self::merge_attributes( $attributes, $name, $type, $options, false );

		pods_view( PODS_DIR . 'ui/fields/_comment.php', compact( array_keys( get_defined_vars() ) ) );

		$output = ob_get_clean();

		return apply_filters( "pods_form_ui_{$type}", $output, $name, $message, $attributes, $options );
	}

	/**
	 * Output a field
	 *
	 * @param string     $name    Field name
	 * @param mixed      $value   Field value
	 * @param string     $type    Field type
	 * @param array      $options Field options
	 * @param array|Pods $pod     Pod data or the Pods object.
	 * @param int        $id      Item ID
	 *
	 * @return string Field HTML
	 *
	 * @since 2.0.0
	 */
	public static function field( $name, $value, $type = 'text', $options = null, $pod = null, $id = null ) {
		// Take a field array
		if ( is_array( $name ) || is_object( $name ) ) {
			$options = $name;

			if ( is_object( $type ) ) {
				$pod = $type;
				$id  = $options;
			}

			$name = pods_v( 'name', $options );
			$type = pods_v( 'type', $options );
		}

		$options = self::options( $type, $options );
		$options = apply_filters( "pods_form_ui_field_{$type}_options", $options, $value, $name, $pod, $id );

		if ( empty( $options['type'] ) ) {
			$options['type'] = $type;
		}

		if ( null === $value || ( '' === $value && 'boolean' === $type ) || ( ! empty( $pod ) && empty( $id ) ) ) {
			$value = self::default_value( $value, $type, $name, $options, $pod, $id );
		}

		// Fix double help qtip when using single checkboxes (boolean type)
		if ( 'boolean' === $type ) {
			$options['help'] = '';
		}

		if ( false === self::permission( $type, $name, $options, null, $pod, $id ) ) {
			return false;
		}

		$value           = apply_filters( "pods_form_ui_field_{$type}_value", $value, $name, $options, $pod, $id );
		$form_field_type = self::$field_type;

		$helper = false;

		/**
		 * Input helpers are deprecated and not guaranteed to work properly.
		 *
		 * They will be entirely removed in Pods 3.0.
		 *
		 * @deprecated 2.7.0
		 */
		if ( 0 < strlen( pods_v( 'input_helper', $options ) ) ) {
			$helper = pods_api()->load_helper( array( 'name' => $options['input_helper'] ) );
		}

		if ( empty( $type ) ) {
			return;
		}

		// @todo Move into DFV field method or Pods\Whatsit later
		if ( ( ! isset( $options['data'] ) || empty( $options['data'] ) ) && is_object( self::$loaded[ $type ] ) && method_exists( self::$loaded[ $type ], 'data' ) ) {
			$options['data'] = self::$loaded[ $type ]->data( $name, $value, $options, $pod, $id, true );
			$data            = $options['data'];
		}

		$repeatable_field_types = self::repeatable_field_types();

		// Start field render.
		ob_start();

		/**
		 * pods_form_ui_field_{$type}_override filter leaves too much to be done by developer.
		 *
		 * It will be replaced in Pods 3.0 with better documentation.
		 *
		 * @deprecated 2.7.0
		 */
		if ( true === apply_filters( "pods_form_ui_field_{$type}_override", false, $name, $value, $options, $pod, $id ) ) {
			/**
			 * pods_form_ui_field_{$type} action leaves too much to be done by developer.
			 *
			 * It will be replaced in Pods 3.0 with better documentation.
			 *
			 * @deprecated 2.7.0
			 */
			do_action( "pods_form_ui_field_{$type}", $name, $value, $options, $pod, $id );
		} elseif ( ! empty( $helper ) && 0 < strlen( pods_v( 'code', $helper ) ) && false === strpos( $helper['code'], '$this->' ) && ( ! defined( 'PODS_DISABLE_EVAL' ) || ! PODS_DISABLE_EVAL ) ) {
			/**
			 * Input helpers are deprecated and not guaranteed to work properly.
			 *
			 * They will be entirely removed in Pods 3.0.
			 *
			 * @deprecated 2.7.0
			 */
			eval( '?>' . $helper['code'] );
		} elseif ( method_exists( get_class(), 'field_' . $type ) ) {
			// @todo Move these custom field methods into real/faux field classes
			echo call_user_func( array( get_class(), 'field_' . $type ), $name, $value, $options );
		} elseif ( is_object( self::$loaded[ $type ] ) && method_exists( self::$loaded[ $type ], 'input' ) ) {
			// Force non-repeatable field types to be non-repeatable even if option is set to 1.
			if ( ! empty( $options['repeatable'] ) && ! in_array( $type, $repeatable_field_types, true ) ) {
				$options['repeatable'] = 0;
			}

			self::$loaded[ $type ]->input( $name, $value, $options, $pod, $id );
		} else {
			/**
			 * pods_form_ui_field_{$type} action leaves too much to be done by developer.
			 *
			 * It will be replaced in Pods 3.0 with better documentation.
			 *
			 * @deprecated 2.7.0
			 */
			do_action( "pods_form_ui_field_{$type}", $name, $value, $options, $pod, $id );
		}//end if

		$output = ob_get_clean();

		/**
		 * pods_form_ui_field_{$type} filter will remain supported.
		 *
		 * It is not intended for replacing but augmenting input markup.
		 */
		return apply_filters( "pods_form_ui_field_{$type}", $output, $name, $value, $options, $pod, $id );
	}

	/**
	 * Output field type 'db'
	 *
	 * Used for field names and other places where only [a-z0-9_] is accepted
	 *
	 * @since 2.0.0
	 *
	 * @param      $name
	 * @param null $value
	 * @param null $options
	 *
	 * @return mixed|void
	 */
	protected static function field_db( $name, $value = null, $options = null ) {

		$form_field_type = self::$field_type;

		ob_start();

		pods_view( PODS_DIR . 'ui/fields/_db.php', compact( array_keys( get_defined_vars() ) ) );

		$output = ob_get_clean();

		return apply_filters( 'pods_form_ui_field_db', $output, $name, $value, $options );
	}

	/**
	 * Output a hidden field
	 *
	 * @param      $name
	 * @param null $value
	 * @param null $options
	 *
	 * @return mixed|void
	 */
	protected static function field_hidden( $name, $value = null, $options = null ) {

		$form_field_type = self::$field_type;

		ob_start();

		pods_view( PODS_DIR . 'ui/fields/_hidden.php', compact( array_keys( get_defined_vars() ) ) );

		$output = ob_get_clean();

		return apply_filters( 'pods_form_ui_field_hidden', $output, $name, $value, $options );
	}

	/**
	 * Returns a submit button, with provided text and appropriate class, copied from WP Core for use on the frontend
	 *
	 * @see   get_submit_button
	 *
	 * @param string       $text             The text of the button (defaults to 'Save Changes')
	 * @param string       $type             The type of button. One of: primary, secondary, delete
	 * @param string       $name             The HTML name of the submit button. Defaults to "submit". If no id
	 *                                       attribute is given in $other_attributes below, $name will be used as the
	 *                                       button's id.
	 * @param bool         $wrap             True if the output button should be wrapped in a paragraph tag,
	 *                                       false otherwise. Defaults to true
	 * @param array|string $other_attributes Other attributes that should be output with the button,
	 *                                       mapping attributes to their values, such as array( 'tabindex' => '1' ).
	 *                                       These attributes will be output as attribute="value", such as
	 *                                       tabindex="1".
	 *                                       Defaults to no other attributes. Other attributes can also be provided as
	 *                                       a
	 *                                       string such as 'tabindex="1"', though the array format is typically
	 *                                       cleaner.
	 *
	 * @since 2.7.0
	 * @return string
	 */
	public static function submit_button( $text = null, $type = 'primary large', $name = 'submit', $wrap = true, $other_attributes = null ) {

		if ( function_exists( 'get_submit_button' ) ) {
			return get_submit_button( $text, $type, $name, $wrap, $other_attributes );
		}

		if ( ! is_array( $type ) ) {
			$type = explode( ' ', $type );
		}

		$button_shorthand = array(
			'primary',
			'small',
			'large',
		);

		$classes = array(
			'button',
		);

		foreach ( $type as $t ) {
			if ( 'secondary' === $t || 'button-secondary' === $t ) {
				continue;
			}

			$classes[] = in_array( $t, $button_shorthand ) ? 'button-' . $t : $t;
		}

		$class = implode( ' ', array_unique( $classes ) );

		if ( 'delete' === $type ) {
			$class = 'button-secondary delete';
		}

		$text = $text ? $text : __( 'Save Changes' );

		// Default the id attribute to $name unless an id was specifically provided in $other_attributes
		$id = $name;

		if ( is_array( $other_attributes ) && isset( $other_attributes['id'] ) ) {
			$id = $other_attributes['id'];
			unset( $other_attributes['id'] );
		}

		$attributes = '';

		if ( is_array( $other_attributes ) ) {
			foreach ( $other_attributes as $attribute => $value ) {
				$attributes .= $attribute . '="' . esc_attr( $value ) . '" ';
				// Trailing space is important
			}
		} elseif ( ! empty( $other_attributes ) ) {
			// Attributes provided as a string
			$attributes = $other_attributes;
		}

		$button  = '<input type="submit" name="' . esc_attr( $name ) . '" id="' . esc_attr( $id ) . '" class="' . esc_attr( $class );
		$button .= '" value="' . esc_attr( $text ) . '" ' . $attributes . ' />';

		if ( $wrap ) {
			$button = '<p class="submit">' . $button . '</p>';
		}

		return $button;

	}

	/**
	 * Output a row (label, field, and comment)
	 *
	 * @param string $name    Field name
	 * @param mixed  $value   Field value
	 * @param string $type    Field type
	 * @param array  $options Field options
	 * @param array  $pod     Pod data
	 * @param int    $id      Item ID
	 *
	 * @return string Row HTML
	 *
	 * @since 2.0.0
	 */
	public static function row( $name, $value, $type = 'text', $options = null, $pod = null, $id = null ) {

		$options = self::options( null, $options );

		ob_start();

		pods_view( PODS_DIR . 'ui/fields/_row.php', compact( array_keys( get_defined_vars() ) ) );

		$output = ob_get_clean();

		return apply_filters( 'pods_form_ui_field_row', $output, $name, $value, $options, $pod, $id );
	}

	/**
	 * Output a field's attributes
	 *
	 * @since 2.0.0
	 *
	 * @param      $attributes
	 * @param null       $name
	 * @param null       $type
	 * @param null       $options
	 */
	public static function attributes( $attributes, $name = null, $type = null, $options = null ) {

		$attributes = (array) apply_filters( "pods_form_ui_field_{$type}_attributes", $attributes, $name, $options );

		foreach ( $attributes as $attribute => $value ) {
			if ( null === $value ) {
				continue;
			}

			echo ' ' . esc_attr( (string) $attribute ) . '="' . esc_attr( (string) $value ) . '"';
		}
	}

	/**
	 * Output a field's data (for use with jQuery)
	 *
	 * @since 2.0.0
	 *
	 * @param      $data
	 * @param null $name
	 * @param null $type
	 * @param null $options
	 */
	public static function data( $data, $name = null, $type = null, $options = null ) {

		$data = (array) apply_filters( "pods_form_ui_field_{$type}_data", $data, $name, $options );

		foreach ( $data as $key => $value ) {
			if ( null === $value ) {
				continue;
			}

			$key = sanitize_title( $key );

			if ( is_array( $value ) ) {
				$value = implode( ',', $value );
			}

			echo ' data-' . esc_attr( (string) $key ) . '="' . esc_attr( (string) $value ) . '"';
		}
	}

	/**
	 * Merge attributes and handle classes
	 *
	 * @since 2.0.0
	 *
	 * @param        $attributes
	 * @param null       $name
	 * @param null       $type
	 * @param null       $options
	 * @param string     $classes
	 *
	 * @return array
	 */
	public static function merge_attributes( $attributes, $name = null, $type = null, $options = null, $classes = '' ) {

		$options = (array) $options;

		if ( ! in_array( $type, array( 'label', 'comment' ) ) ) {
			$name_clean                     = self::clean( $name );
			$name_more_clean                = self::clean( $name, true );
			$_attributes                    = array();
			$_attributes['name']            = $name;
			$_attributes['data-name-clean'] = $name_more_clean;

			if ( 0 < strlen( pods_v( 'label', $options, '' ) ) ) {
				$_attributes['data-label'] = strip_tags( pods_v( 'label', $options ) );
			}

			$_attributes['id']    = 'pods-form-ui-' . $name_clean . ( self::$form_counter > 1 ? '-' . self::$form_counter : '' );
			$_attributes['class'] = 'pods-form-ui-field pods-form-ui-field-type-' . $type . ' pods-form-ui-field-name-' . $name_more_clean;

			if ( isset( $options['dependency'] ) && false !== $options['dependency'] ) {
				$_attributes['class'] .= ' pods-dependent-toggle';
			}

			$attributes = array_merge( $_attributes, (array) $attributes );

			if ( isset( $options['attributes'] ) && is_array( $options['attributes'] ) && ! empty( $options['attributes'] ) ) {
				$attributes = array_merge( $attributes, $options['attributes'] );
			}
		} elseif ( isset( $options[ $type . '_attributes' ] ) && is_array( $options[ $type . '_attributes' ] ) && ! empty( $options[ $type . '_attributes' ] ) ) {
			$attributes = array_merge( $attributes, $options[ $type . '_attributes' ] );
		}//end if

		if ( isset( $options['class'] ) && ! empty( $options['class'] ) ) {
			if ( is_array( $options['class'] ) ) {
				$options['class'] = implode( ' ', $options['class'] );
			}

			$options['class'] = (string) $options['class'];
			if ( isset( $attributes['class'] ) ) {
				$attributes['class'] = $attributes['class'] . ' ' . $options['class'];
			} else {
				$attributes['class'] = $options['class'];
			}

			$attributes['class'] = trim( $attributes['class'] );
		}

		if ( ! empty( $classes ) ) {
			if ( isset( $attributes['class'] ) ) {
				$attributes['class'] = $attributes['class'] . ' ' . $classes;
			} else {
				$attributes['class'] = $classes;
			}
		}

		$placeholder = trim( pods_v( 'placeholder', $options, pods_v( $type . '_placeholder', $options ) ) );

		if ( ! empty( $placeholder ) ) {
			$attributes['placeholder'] = $placeholder;
		}

		if ( 1 === (int) pods_v( 'required', $options, 0 ) ) {
			$attributes['class'] .= ' pods-validate pods-validate-required';
		}

		$max_length = (int) pods_v( 'maxlength', $options, pods_v( $type . '_max_length', $options, 0 ) );

		if ( 0 < $max_length ) {
			$attributes['maxlength'] = $max_length;
		}

		$attributes = (array) apply_filters( "pods_form_ui_field_{$type}_merge_attributes", $attributes, $name, $options );

		return $attributes;
	}

	/**
	 * Setup options for a field and store them for later use
	 *
	 * @param $type
	 * @param $options
	 *
	 * @return array
	 *
	 * @static
	 *
	 * @since 2.0.0
	 */
	public static function options( $type, $options ) {
		if ( is_object( $options ) ) {
			$options_array                  = $options->get_args();
			$options_array['_field_object'] = $options;
		} else {
			$options_array                  = (array) $options;
			$options_array['_field_object'] = null;
		}

		$defaults = self::options_setup( $type );

		$core_defaults = [
			'id'          => 0,
			'label'       => '',
			'description' => '',
			'help'        => '',
			'default'     => null,
			'attributes'  => [],
			'class'       => '',
			'grouped'     => 0,
		];

		$defaults = array_merge( $core_defaults, $defaults );

		foreach ( $defaults as $option => $settings ) {
			$default = $core_defaults['default'];

			if ( ! is_array( $settings ) ) {
				$default = $settings;
			} elseif ( isset( $settings['default'] ) ) {
				$default = $settings['default'];
			}

			if ( ! isset( $options_array[ $option ] ) ) {
				$options_array[ $option ] = $default;
			}
		}

		return $options_array;
	}

	/**
	 * Get options for a field type and setup defaults
	 *
	 * @static
	 *
	 * @param      $type
	 *
	 * @param null $options
	 *
	 * @return array|null
	 * @since 2.0.0
	 */
	public static function options_setup( $type = null, $options = null ) {

		$core_defaults = array(
			'id'             => 0,
			'name'           => '',
			'label'          => '',
			'description'    => '',
			'help'           => '',
			'default'        => null,
			'attributes'     => array(),
			'class'          => '',
			'type'           => 'text',
			'group'          => 0,
			'grouped'        => 0,
			'developer_mode' => false,
			'dependency'     => false,
			'depends-on'     => array(),
			'depends-on-any' => array(),
			'excludes-on'    => array(),
			'wildcard-on'    => array(),
			'options'        => array(),
		);

		if ( ! empty( $options ) && is_array( $options ) ) {
			$core_defaults = array_merge( $core_defaults, $options );
		}

		if ( null === $type ) {
			return $core_defaults;
		} else {
			self::field_loader( $type );
		}

		$ui_options = apply_filters( "pods_field_{$type}_options", (array) self::$loaded[ $type ]->options(), $type );

		$first_field = reset( $ui_options );

		if ( ! empty( $ui_options ) && ! isset( $first_field['name'] ) && ! isset( $first_field['label'] ) ) {
			foreach ( $ui_options as $group => $group_options ) {
				$ui_options[ $group ] = self::fields_setup( $group_options, $core_defaults );
			}

			return $ui_options;
		}

		return self::fields_setup( $ui_options, $core_defaults );
	}

	/**
	 * Get Admin options for a field type and setup defaults
	 *
	 * @static
	 *
	 * @param $type
	 *
	 * @return array|null
	 *
	 * @since 2.0.0
	 */
	public static function ui_options( $type ) {

		$core_defaults = array(
			'id'             => 0,
			'name'           => '',
			'label'          => '',
			'description'    => '',
			'help'           => '',
			'default'        => null,
			'attributes'     => array(),
			'class'          => '',
			'type'           => 'text',
			'group'          => 0,
			'grouped'        => 0,
			'developer_mode' => false,
			'dependency'     => false,
			'depends-on'     => array(),
			'depends-on-any' => array(),
			'excludes-on'    => array(),
			'wildcard-on'    => array(),
			'options'        => array(),
		);

		self::field_loader( $type );

		$ui_options = apply_filters( "pods_field_{$type}_ui_options", (array) self::$loaded[ $type ]->ui_options(), $type );

		$first_field = reset( $ui_options );

		if ( ! empty( $ui_options ) && ! isset( $first_field['name'] ) && ! isset( $first_field['label'] ) ) {
			foreach ( $ui_options as $group => $group_options ) {
				$ui_options[ $group ] = self::fields_setup( $group_options, $core_defaults );
			}

			return $ui_options;
		}

		return self::fields_setup( $ui_options, $core_defaults );
	}

	/**
	 * Get options for a field and setup defaults
	 *
	 * @param null $fields
	 * @param null $core_defaults
	 * @param bool $single
	 *
	 * @return array|null
	 *
	 * @static
	 * @since 2.0.0
	 */
	public static function fields_setup( $fields = null, $core_defaults = null, $single = false ) {

		if ( empty( $core_defaults ) ) {
			$core_defaults = array(
				'id'             => 0,
				'name'           => '',
				'label'          => '',
				'description'    => '',
				'help'           => '',
				'default'        => null,
				'attributes'     => array(),
				'class'          => '',
				'type'           => 'text',
				'group'          => 0,
				'grouped'        => 0,
				'developer_mode' => false,
				'dependency'     => false,
				'depends-on'     => array(),
				'depends-on-any' => array(),
				'excludes-on'    => array(),
				'wildcard-on'    => array(),
				'options'        => array(),
			);
		}

		if ( $single ) {
			$fields = array( $fields );
		}

		foreach ( $fields as $f => $field ) {
			if ( ! $single && empty( $field['name'] ) ) {
				$field['name'] = $f;
			}

			$fields[ $f ] = self::field_setup( $field, $core_defaults, pods_v( 'type', $field, 'text' ) );
		}

		if ( $single ) {
			$fields = $fields[0];
		}

		return $fields;
	}

	/**
	 * Get options for a field and setup defaults
	 *
	 * @static
	 *
	 * @param null|array|string|Field $field
	 * @param null|array $core_defaults
	 * @param null|string $type
	 *
	 * @return array|null
	 *
	 * @since 2.0.0
	 */
	public static function field_setup( $field = null, $core_defaults = null, $type = null ) {

		$ui_options = array();

		if ( empty( $core_defaults ) ) {
			$core_defaults = array(
				'id'             => 0,
				'name'           => '',
				'label'          => '',
				'description'    => '',
				'help'           => '',
				'default'        => null,
				'attributes'     => array(),
				'class'          => '',
				'type'           => 'text',
				'group'          => 0,
				'grouped'        => 0,
				'developer_mode' => false,
				'dependency'     => false,
				'depends-on'     => array(),
				'depends-on-any' => array(),
				'excludes-on'    => array(),
				'wildcard-on'    => array(),
				'options'        => array(),
			);

			if ( null !== $type ) {
				self::field_loader( $type );

				if ( method_exists( self::$loaded[ $type ], 'options' ) ) {
					$ui_options = apply_filters( "pods_field_{$type}_options", (array) self::$loaded[ $type ]->options(), $type );
				}
			}
		}//end if

		$is_field_object = $field instanceof Field;

		if ( ! is_array( $field ) && ! $is_field_object ) {
			$field = [
				'default' => $field,
			];
		}

		// @todo Revisit this.
		if ( isset( $field['group'] ) && is_array( $field['group'] ) ) {
			$group = $field['group'];

			foreach ( $group as $g => $group_option ) {
				$group[ $g ] = array_merge( $core_defaults, $group_option );

				if ( ! isset( $group[ $g ] ) || '' === $group[ $g ]['name'] ) {
					$group[ $g ]['name'] = $g;
				}
			}

			$field['group'] = $group;
		}

		$field = pods_config_merge_data( $core_defaults, $field );

		foreach ( $ui_options as $option => $settings ) {
			if ( ! is_string( $option ) ) {
				$option = $settings['name'];
			}

			$default = null;

			if ( isset( $settings['default'] ) ) {
				$default = $settings['default'];
			}

			if ( $is_field_object ) {
				$option_value = $field->get_arg( $option );

				if ( null === $option_value ) {
					$field->set_arg( $option, $default );
				}
			} elseif ( ! isset( $field['options'][ $option ] ) ) {
				$field['options'][ $option ] = $default;
			}
		}

		return $field;
	}

	/**
	 * Setup dependency / exclusion classes
	 *
	 * @param array  $options array( 'depends-on' => ..., 'excludes-on' => ...)
	 * @param string $prefix
	 *
	 * @return array
	 * @static
	 * @since 2.0.0
	 */
	public static function dependencies( $options, $prefix = 'pods-form-ui-' ) {
		$options        = (array) $options;
		$classes        = [];
		$data           = [];

		$dependency_checks = [
			'depends-on',
			'depends-on-any',
			'excludes-on',
		];

		foreach ( $dependency_checks as $dependency_check ) {
			if ( ! isset( $options[ $dependency_check ] ) ) {
				continue;
			}

			$dependency_list = (array) $options[ $dependency_check ];

			if ( ! empty( $dependency_list ) ) {
				$classes[] = 'pods-' . $dependency_check;

				foreach ( $dependency_list as $depends => $on ) {
					$classes[] = 'pods-' . $dependency_check . '-' . $prefix . self::clean( $depends, true );

					if ( ! is_bool( $on ) ) {
						$on = (array) $on;

						foreach ( $on as $o ) {
							$classes[] = 'pods-' . $dependency_check . '-' . $prefix . self::clean( $depends, true ) . '-' . self::clean( $o, true );
						}
					}
				}
			}
		}

		if ( isset( $options['wildcard-on'] ) ) {
			$wildcard_on = (array) $options['wildcard-on'];

			if ( ! empty( $wildcard_on ) ) {
				$classes[] = 'pods-wildcard-on';

				// Add the appropriate classes and data attribs per value dependency
				foreach ( $wildcard_on as $target => $wildcards ) {
					$target                             = $prefix . self::clean( $target, true );
					$classes[]                          = 'pods-wildcard-on-' . $target;
					$data[ 'pods-wildcard-' . $target ] = $wildcards;
				}
			}
		}

		$classes = implode( ' ', $classes );

		return array(
			'classes' => $classes,
			'data'    => $data,
		);
	}

	/**
	 * Change the value of the field
	 *
	 * @param        $type
	 * @param mixed  $value
	 * @param string $name
	 * @param array  $options
	 * @param array  $pod
	 * @param int    $id
	 * @param array  $traverse
	 *
	 * @return array|mixed|null|object
	 * @internal param array $fields
	 * @since 2.3.0
	 */
	public static function value( $type, $value = null, $name = null, $options = null, $pod = null, $id = null, $traverse = null ) {

		self::field_loader( $type );

		$is_repeatable_field = (
			(
				(
					$options instanceof Field
					|| $options instanceof Value_Field
				)
				&& $options->is_repeatable()
			)
			|| (
				is_array( $options )
				&& in_array( $type, self::repeatable_field_types(), true )
				&& 1 === (int) pods_v( 'repeatable', $options )
				&& (
					'wysiwyg' !== $type
					|| 'tinymce' !== pods_v( 'wysiwyg_editor', $options, 'tinymce', true )
				)
			)
		);

		if ( $is_repeatable_field && ! is_array( $value ) ) {
			if ( is_string( $value ) && 0 < strlen( $value ) ) {
				$simple = @json_decode( $value, true );

				if ( is_array( $simple ) ) {
					$value = $simple;
				} else {
					$value = (array) $value;
				}
			} else {
				$value = [];
			}
		}

		if ( is_array( $value ) && in_array( $type, self::tableless_field_types(), true ) ) {
			foreach ( $value as &$display_value ) {
				$display_value = call_user_func( array(
					self::$loaded[ $type ],
					'value',
				), $display_value, $name, $options, $pod, $id, $traverse );
			}
		} else {
			$value = call_user_func( array(
				self::$loaded[ $type ],
				'value',
			), $value, $name, $options, $pod, $id, $traverse );
		}//end if

		return $value;
	}

	/**
	 * Change the way the value of the field is displayed with Pods::get
	 *
	 * @param        $type
	 * @param mixed  $value
	 * @param string $name
	 * @param array  $options
	 * @param array  $pod
	 * @param int    $id
	 * @param array  $traverse
	 *
	 * @return array|mixed|null|void
	 * @internal param array $fields
	 * @since 2.0.0
	 */
	public static function display( $type, $value = null, $name = null, $options = null, $pod = null, $id = null, $traverse = null ) {

		self::field_loader( $type );

		$tableless_field_types = self::tableless_field_types();

		if ( method_exists( self::$loaded[ $type ], 'display_list' ) ) {
			$value = call_user_func_array(
				array( self::$loaded[ $type ], 'display_list' ), array(
					$value,
					$name,
					$options,
					$pod,
					$id,
					$traverse,
				)
			);
		} elseif ( method_exists( self::$loaded[ $type ], 'display' ) ) {
			if ( is_array( $value ) && ! in_array( $type, $tableless_field_types ) ) {
				foreach ( $value as $k => $display_value ) {
					$value[ $k ] = call_user_func_array(
						array( self::$loaded[ $type ], 'display' ), array(
							$display_value,
							$name,
							$options,
							$pod,
							$id,
							$traverse,
						)
					);
				}
			} else {
				$value = call_user_func_array(
					array( self::$loaded[ $type ], 'display' ), array(
						$value,
						$name,
						$options,
						$pod,
						$id,
						$traverse,
					)
				);
			}//end if
		}//end if

		$value = apply_filters( "pods_form_display_{$type}", $value, $name, $options, $pod, $id, $traverse );

		return $value;
	}

	/**
	 * Setup regex for JS / PHP
	 *
	 * @static
	 *
	 * @param $type
	 * @param $options
	 *
	 * @return mixed|void
	 * @since 2.0.0
	 */
	public static function regex( $type, $options ) {

		self::field_loader( $type );

		$regex = false;

		if ( method_exists( self::$loaded[ $type ], 'regex' ) ) {
			$regex = self::$loaded[ $type ]->regex( $options );
		}

		$regex = apply_filters( "pods_field_{$type}_regex", $regex, $options, $type );

		return $regex;
	}

	/**
	 * Setup value preparation for sprintf
	 *
	 * @static
	 *
	 * @param $type
	 * @param $options
	 *
	 * @return mixed|void
	 * @since 2.0.0
	 */
	public static function prepare( $type, $options ) {

		self::field_loader( $type );

		$prepare = '%s';

		if ( method_exists( self::$loaded[ $type ], 'prepare' ) ) {
			$prepare = self::$loaded[ $type ]->prepare( $options );
		}

		$prepare = apply_filters( "pods_field_{$type}_prepare", $prepare, $options, $type );

		return $prepare;
	}

	/**
	 * Validate a value before it's saved
	 *
	 * @param string       $type
	 * @param mixed        $value
	 * @param string       $name
	 * @param array        $options
	 * @param array        $fields
	 * @param array        $pod
	 * @param int          $id
	 * @param array|object $params
	 *
	 * @static
	 *
	 * @since 2.0.0
	 * @return bool|mixed|void
	 */
	public static function validate( $type, $value, $name = null, $options = null, $fields = null, $pod = null, $id = null, $params = null ) {

		self::field_loader( $type );

		$validate = true;

		if ( 1 == pods_v( 'pre_save', $options, 1 ) && method_exists( self::$loaded[ $type ], 'validate' ) ) {
			$validate = self::$loaded[ $type ]->validate( $value, $name, $options, $fields, $pod, $id, $params );
		}

		$validate = apply_filters( "pods_field_{$type}_validate", $validate, $value, $name, $options, $fields, $pod, $id, $type, $params );

		return $validate;
	}

	/**
	 * Change the value or perform actions after validation but before saving to the DB
	 *
	 * @param string $type
	 * @param mixed  $value
	 * @param int    $id
	 * @param string $name
	 * @param array  $options
	 * @param array  $fields
	 * @param array  $pod
	 * @param object $params
	 *
	 * @static
	 *
	 * @since 2.0.0
	 * @return mixed
	 */
	public static function pre_save( $type, $value, $id = null, $name = null, $options = null, $fields = null, $pod = null, $params = null ) {

		self::field_loader( $type );

		if ( 1 == pods_v( 'field_pre_save', $options, 1 ) && method_exists( self::$loaded[ $type ], 'pre_save' ) ) {
			$value = self::$loaded[ $type ]->pre_save( $value, $id, $name, $options, $fields, $pod, $params );
		}

		return $value;
	}

	/**
	 * Save the value to the DB
	 *
	 * @param string $type
	 * @param mixed  $value
	 * @param int    $id
	 * @param string $name
	 * @param array  $options
	 * @param array  $fields
	 * @param array  $pod
	 * @param object $params
	 *
	 * @static
	 *
	 * @since 2.3.0
	 * @return null
	 */
	public static function save( $type, $value, $id = null, $name = null, $options = null, $fields = null, $pod = null, $params = null ) {

		self::field_loader( $type );

		$saved = null;

		if ( 1 == pods_v( 'field_save', $options, 1 ) && method_exists( self::$loaded[ $type ], 'save' ) ) {
			$saved = self::$loaded[ $type ]->save( $value, $id, $name, $options, $fields, $pod, $params );
		}

		return $saved;
	}

	/**
	 * Delete the value from the DB
	 *
	 * @param string $type
	 * @param int    $id
	 * @param string $name
	 * @param array  $options
	 * @param array  $pod
	 *
	 * @static
	 *
	 * @since 2.3.0
	 * @return null
	 */
	public static function delete( $type, $id = null, $name = null, $options = null, $pod = null ) {

		self::field_loader( $type );

		$deleted = null;

		if ( 1 == pods_v( 'field_delete', $options, 1 ) && method_exists( self::$loaded[ $type ], 'delete' ) ) {
			$deleted = self::$loaded[ $type ]->delete( $id, $name, $options, $pod );
		}

		return $deleted;
	}

	/**
	 * Check if a user has permission to be editing a field
	 *
	 * @param      $type
	 * @param null $name
	 * @param null $options
	 * @param null $fields
	 * @param null $pod
	 * @param null $id
	 * @param null $params
	 *
	 * @static
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	public static function permission( $type, $name = null, $options = null, $fields = null, $pod = null, $id = null, $params = null ) {
		$permission = pods_permission( $options );

		/**
		 * @since 2.0.0
		 * @deprecated 2.8.0
		 */
		return (boolean) apply_filters( 'pods_form_field_permission', $permission, $type, $name, $options, $fields, $pod, $id, $params );
	}

	/**
	 * Parse the default the value
	 *
	 * @since 2.0.0
	 *
	 * @param        $value
	 * @param string $type
	 * @param null   $name
	 * @param null   $options
	 * @param null   $pod
	 * @param null   $id
	 *
	 * @return mixed|void
	 */
	public static function default_value( $value, $type = 'text', $name = null, $options = null, $pod = null, $id = null ) {

		$default_value = pods_v( 'default_value', $options );

		if ( '' === $default_value || null === $default_value ) {
			$default_value = $value;
		}

		$default = pods_v( 'default', $options, $default_value, true );

		if ( is_string( $default ) ) {
			$default_value = str_replace( array( '{@', '}' ), '', $default );

			if ( $default !== $default_value && 1 === (int) pods_v( 'default_evaluate_tags', $options, 1 ) ) {
				$default = pods_evaluate_tags( $default );
			}
		}

		$default_value_parameter = pods_v( 'default_value_parameter', $options );

		if ( $default_value_parameter ) {
			$default_value = pods_v( $default_value_parameter, 'request', $default );

			if ( '' !== $default_value ) {
				$default = $default_value;
			}
		}

		if ( $default != $value ) {
			$value = $default;
		}

		if ( is_array( $value ) && 'multi' !== pods_v( $type . '_format_type' ) ) {
			$value = pods_serial_comma( $value, $name, [ $name => $options ] );
		}

		return apply_filters( 'pods_form_field_default_value', $value, $default, $type, $options, $pod, $id );
	}

	/**
	 * Clean a value for use in class / id
	 *
	 * @since 2.0.0
	 *
	 * @param      $input
	 * @param bool  $noarray
	 * @param bool  $db_field
	 *
	 * @return mixed|string
	 */
	public static function clean( $input, $noarray = false, $db_field = false ) {

		$output = trim( (string) $input );

		$output = str_replace( '--1', 'podsfixtemp1', $output );
		$output = str_replace( '__1', 'podsfixtemp2', $output );

		if ( false !== $noarray ) {
			$output = preg_replace( '/\[podsfixtemp\d+\]/', '-', $output );
			$output = preg_replace( '/\[\d*\]/', '-', $output );
		}

		$output = str_replace( array( '[', ']' ), '-', $output );

		$output = pods_clean_name( $output );

		$output = preg_replace( '/([^a-z0-9\-_])/', '', $output );
		$output = preg_replace( '/(_){2,}/', '_', $output );
		$output = preg_replace( '/(-){2,}/', '-', $output );

		if ( true !== $db_field ) {
			$output = str_replace( '_', '-', $output );
		}

		$output = rtrim( $output, '-' );

		$output = str_replace( 'podsfixtemp1', '--1', $output );
		$output = str_replace( 'podsfixtemp2', '__1', $output );

		return $output;
	}

	/**
	 * Run admin_init methods for each field type
	 *
	 * @since 2.3.0
	 */
	public function admin_init() {

		$admin_field_types = pods_transient_get( 'pods_form_admin_init_field_types' );

		if ( empty( $admin_field_types ) ) {
			$admin_field_types = array();

			$field_types = self::field_types();

			foreach ( $field_types as $field_type => $field_type_data ) {
				$has_admin_init = self::field_method( $field_type_data['type'], 'admin_init' );

				if ( false !== $has_admin_init ) {
					$admin_field_types[] = $field_type;
				}
			}

			pods_transient_set( 'pods_form_admin_init_field_types', $admin_field_types, WEEK_IN_SECONDS );
		} else {
			foreach ( $admin_field_types as $field_type ) {
				self::field_method( $field_type, 'admin_init' );
			}
		}
	}

	/**
	 * Autoload a Field Type's class
	 *
	 * @param string $field_type Field Type indentifier
	 * @param string $file       The Field Type class file location
	 *
	 * @return string
	 * @access public
	 * @static
	 * @since 2.0.0
	 */
	public static function field_loader( $field_type, $file = '' ) {

		if ( isset( self::$loaded[ $field_type ] ) ) {
			$class_vars = get_class_vars( get_class( self::$loaded[ $field_type ] ) );
			// PHP 5.2.x workaround
			self::$field_group = ( isset( $class_vars['group'] ) ? $class_vars['group'] : '' );
			self::$field_type  = $class_vars['type'];

			if ( 'Unknown' !== $class_vars['label'] ) {
				return self::$loaded[ $field_type ];
			}
		}

		$field_type = self::clean( $field_type, true, true );

		$class_name = ucfirst( $field_type );
		$class_name = "PodsField_{$class_name}";

		if ( ! class_exists( $class_name ) ) {
			if ( isset( self::$field_types[ $field_type ] ) && ! empty( self::$field_types[ $field_type ]['file'] ) ) {
				$file = realpath( self::$field_types[ $field_type ]['file'] );
			}

			/**
			 * The field type include path.
			 *
			 * @since unknown
			 *
			 * @param string $file The file path to include for the field type.
			 */
			$file = apply_filters( 'pods_form_field_include', $file, $field_type );

			$file = trim( $file );

			if ( '' !== $file ) {
				$located = pods_validate_safe_path( $file, 'all' );

				if ( $located ) {
					include_once $located;
				}
			}
		}

		if ( class_exists( $class_name ) ) {
			$class = new $class_name();
		} else {
			$class      = new PodsField();
			$class_name = 'PodsField';
		}

		$class_vars = get_class_vars( $class_name );

		// PHP 5.2.x workaround
		self::$field_group = ( isset( $class_vars['group'] ) ? $class_vars['group'] : '' );
		self::$field_type  = $class_vars['type'];

		self::$loaded[ $field_type ] =& $class;

		return self::$loaded[ $field_type ];
	}

	/**
	 * Run a method from a Field Type's class
	 *
	 * @return mixed
	 * @internal param string $field_type Field Type indentifier
	 * @internal param string $method Method name
	 * @internal param mixed $arg More arguments
	 *
	 * @access   public
	 * @static
	 * @since 2.0.0
	 */
	public static function field_method() {

		$args = func_get_args();

		if ( empty( $args ) && count( $args ) < 2 ) {
			return false;
		}

		$field_type = array_shift( $args );
		$method     = array_shift( $args );

		$class = self::field_loader( $field_type );

		if ( method_exists( $class, $method ) ) {
			return call_user_func_array( array( $class, $method ), $args );
		}

		return false;
	}

	/**
	 * Add a new Pod field type
	 *
	 * @param string $type The new field type identifier
	 * @param string $file The new field type class file location
	 *
	 * @return array Field Type data
	 *
	 * @since 2.3.0
	 */
	public static function register_field_type( $type, $file = null ) {

		$field_type = pods_transient_get( 'pods_field_type_' . $type );

		if ( empty( $field_type ) || $field_type['type'] != $type || $field_type['file'] != $file ) {
			self::field_loader( $type, $file );

			$class_vars = get_class_vars( get_class( self::$loaded[ $type ] ) );
			// PHP 5.2.x workaround
			self::$field_types[ $type ]         = $class_vars;
			self::$field_types[ $type ]['file'] = $file;

			pods_transient_set( 'pods_field_type_' . $type, self::$field_types[ $type ], WEEK_IN_SECONDS );
		} else {
			self::$field_types[ $type ] = $field_type;
		}

		return self::$field_types[ $type ];
	}

	/**
	 * Get a list of all available Pod types.
	 *
	 * @return string[] List of Pod types.
	 *
	 * @since 2.8.0
	 */
	public static function pod_types_list() {
		$pod_types = [
			'post_type',
			'taxonomy',
			'user',
			'media',
			'comment',
			'settings',
			'pod',
			'table',
		];

		/**
		 * Allow filtering of the supported Pod types.
		 *
		 * @since 2.8.0
		 *
		 * @param array $pod_types List of Pod types supported.
		 */
		return apply_filters( 'pods_api_pod_types', $pod_types );
	}

	/**
	 * Get a list of all available Field types.
	 *
	 * @return string[] List of Field types.
	 *
	 * @since 2.8.0
	 */
	public static function field_types_list() {
		$field_types = [
			'text',
			'website',
			// 'link',
			'phone',
			'email',
			'password',
			'paragraph',
			'wysiwyg',
			'code',
			'datetime',
			'date',
			'time',
			'number',
			'currency',
			'file',
			'avatar',
			'oembed',
			'pick',
			'boolean',
			'color',
			'slug',
			'heading',
			'html',
		];

		$field_types = array_merge( $field_types, array_keys( self::$field_types ) );

		$field_types = array_filter( array_unique( $field_types ) );

		return apply_filters( 'pods_api_field_types', $field_types );
	}

	/**
	 * Get a list of all available field types and include
	 *
	 * @return array Registered Field Types data
	 *
	 * @since 2.3.0
	 */
	public static function field_types() {

		$types = self::field_types_list();

		$field_types = pods_transient_get( 'pods_field_types' );

		if ( empty( $field_types ) || count( $types ) != count( $field_types ) ) {
			$field_types = array();

			foreach ( $types as $field_type ) {
				$file = null;

				if ( isset( self::$field_types[ $field_type ] ) ) {
					$file = self::$field_types[ $field_type ]['file'];
				}

				self::field_loader( $field_type, $file );

				if ( ! isset( self::$loaded[ $field_type ] ) || ! is_object( self::$loaded[ $field_type ] ) ) {
					continue;
				}

				$class_vars = get_class_vars( get_class( self::$loaded[ $field_type ] ) );
				// PHP 5.2.x workaround
				$field_types[ $field_type ]         = $class_vars;
				$field_types[ $field_type ]['file'] = $file;
			}

			self::$field_types = $field_types;

			pods_transient_set( 'pods_field_types', self::$field_types, WEEK_IN_SECONDS );
		} else {
			self::$field_types = array_merge( $field_types, self::$field_types );
		}//end if

		return self::$field_types;
	}

	/**
	 * Get the list of available tableless field types.
	 *
	 * @since 2.3.0
	 *
	 * @return array The list of available tableless field types.
	 */
	public static function tableless_field_types() {
		static $field_types = null;

		if ( null === $field_types ) {
			$field_types = [
				'pick',
				'file',
				'avatar',
				'taxonomy',
				'comment',
				'author',
			];

			$field_types = apply_filters( 'pods_tableless_field_types', $field_types );
		}

		return $field_types;
	}

	/**
	 * Get the list of available file field types.
	 *
	 * @since 2.3.0
	 *
	 * @return array The list of available file field types.
	 */
	public static function file_field_types() {
		static $field_types = null;

		if ( null === $field_types ) {
			$field_types = [
				'file',
				'avatar',
			];

			$field_types = apply_filters( 'pods_file_field_types', $field_types );
		}

		return $field_types;
	}

	/**
	 * Get the list of available repeatable field types.
	 *
	 * @since 2.3.0
	 *
	 * @return array The list of available repeatable field types.
	 */
	public static function repeatable_field_types() {
		static $field_types = null;

		if ( null === $field_types ) {
			$field_types = [
				'color',
				'currency',
				'date',
				'datetime',
				'email',
				'number',
				'oembed',
				'paragraph',
				'password',
				'phone',
				'text',
				'time',
				'website',
				'wysiwyg',
			];

			$field_types = (array) apply_filters( 'pods_repeatable_field_types', $field_types );
		}

		return $field_types;
	}

	/**
	 * Get the list of available number field types.
	 *
	 * @since 2.3.0
	 *
	 * @return array The list of available number field types.
	 */
	public static function number_field_types() {
		static $field_types = null;

		if ( null === $field_types ) {
			$field_types = [
				'currency',
				'number',
			];

			$field_types = apply_filters( 'pods_tableless_field_types', $field_types );
		}

		return $field_types;
	}

	/**
	 * Get the list of available date field types.
	 *
	 * @since 2.3.0
	 *
	 * @return array The list of available date field types.
	 */
	public static function date_field_types() {
		static $field_types = null;

		if ( null === $field_types ) {
			$field_types = [
				'date',
				'datetime',
				'time',
			];

			$field_types = apply_filters( 'pods_tableless_field_types', $field_types );
		}

		return $field_types;
	}

	/**
	 * Get the list of available text field types.
	 *
	 * @since 2.3.0
	 *
	 * @return array The list of available text field types.
	 */
	public static function text_field_types() {
		static $field_types = null;

		if ( null === $field_types ) {
			$field_types = [
				'code',
				'paragraph',
				'slug',
				'password',
				'text',
				'wysiwyg',
			];

			$field_types = apply_filters( 'pods_text_field_types', $field_types );
		}

		return $field_types;
	}

	/**
	 * Get the list of available Layout field types (backwards compatible version).
	 *
	 * @since 2.3.0
	 *
	 * @deprecated since 2.3.0
	 * @see PodsForm::layout_field_types()
	 *
	 * @return array The list of available Layout field types.
	 */
	public static function block_field_types() {
		_doing_it_wrong( 'PodsForm::layout_field_types', 'This function is deprecated, use PodsForm::layout_field_types instead.', '2.8.0' );

		return self::layout_field_types();
	}

	/**
	 * Get the list of available Layout field types.
	 *
	 * @since 2.8.0
	 *
	 * @return array The list of available Layout field types.
	 */
	public static function layout_field_types() {
		static $field_types = null;

		if ( null === $field_types ) {
			$field_types = [
				'heading',
				'html',
			];

			/**
			 * Allow filtering of the list of Layout field types.
			 *
			 * @since 2.8.0
			 *
			 * @param array $field_types The list of Layout field types.
			 */
			$field_types = apply_filters( 'pods_layout_field_types', $field_types );
		}

		return $field_types;
	}

	/**
	 * Get the list of available Non-Input field types.
	 *
	 * @since 2.8.0
	 *
	 * @return array The list of available Non-Input field types.
	 */
	public static function non_input_field_types() {
		static $field_types = null;

		if ( null === $field_types ) {
			$field_types = [
				'internal',
			];

			/**
			 * Allow filtering of the list of Non-Input field types.
			 *
			 * @since 2.8.0
			 *
			 * @param array $field_types The list of Non-Input field types.
			 */
			$field_types = apply_filters( 'pods_non_input_field_types', $field_types );
		}

		return $field_types;
	}

	/**
	 * Get the list of field types that do not use serial comma separators.
	 *
	 * @since 2.9.4
	 *
	 * @return array The list of field types that do not use serial comma separators.
	 */
	public static function separator_excluded_field_types() {
		static $field_types = null;

		if ( null === $field_types ) {
			$field_types = [
				'avatar',
				'code',
				'link',
				'oembed',
				'paragraph',
				'website',
				'wysiwyg',
			];

			/**
			 * Allow filtering of the list of field types that do not use serial comma separators.
			 *
			 * @since 2.8.0
			 *
			 * @param array $field_types The list of field types that do not use serial comma separators.
			 */
			$field_types = apply_filters( 'pods_separator_excluded_field_types', $field_types );
		}

		return $field_types;
	}

	/**
	 * Get the list of simple tableless objects.
	 *
	 * @since 2.3.0
	 *
	 * @return array The list of simple tableless objects.
	 */
	public static function simple_tableless_objects() {

		static $object_types = null;

		if ( null === $object_types ) {
			$object_types = self::field_method( 'pick', 'simple_objects' );
		}

		return $object_types;
	}

	/**
	 * Render the postbox header in a compatible way.
	 *
	 * @since 2.7.22
	 *
	 * @param string $title Header title.
	 */
	public static function render_postbox_header( $title ) {
		pods_view( PODS_DIR . 'ui/admin/postbox-header.php', compact( array_keys( get_defined_vars() ) ) );
	}
}
