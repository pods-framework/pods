<?php

/**
 * @package Pods\Fields
 */
class PodsField_Text extends PodsField {

	/**
	 * {@inheritdoc}
	 */
	public static $group = 'Text';

	/**
	 * {@inheritdoc}
	 */
	public static $type = 'text';

	/**
	 * {@inheritdoc}
	 */
	public static $label = 'Plain Text';

	/**
	 * {@inheritdoc}
	 */
	public static $prepare = '%s';

	/**
	 * {@inheritdoc}
	 */
	public function setup() {

		static::$group = __( 'Text', 'pods' );
		static::$label = __( 'Plain Text', 'pods' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function options() {

		$options = array(
			'output_options'                     => array(
				'label' => __( 'Output Options', 'pods' ),
				'type'  => 'boolean_group',
				'boolean_group' => array(
					static::$type . '_trim'      => array(
						'label'      => __( 'Trim extra whitespace before/after contents', 'pods' ),
						'default'    => 1,
						'type'       => 'boolean',
						'dependency' => true,
					),
					static::$type . '_allow_html'      => array(
						'label'      => __( 'Allow HTML', 'pods' ),
						'default'    => 0,
						'type'       => 'boolean',
						'dependency' => true,
					),
					static::$type . '_allow_shortcode' => array(
						'label'      => __( 'Allow Shortcodes', 'pods' ),
						'default'    => 0,
						'type'       => 'boolean',
						'dependency' => true,
					),
				),
			),
			static::$type . '_allowed_html_tags' => array(
				'label'      => __( 'Allowed HTML Tags', 'pods' ),
				'depends-on' => array( static::$type . '_allow_html' => true ),
				'default'    => 'strong em a ul ol li b i',
				'type'       => 'text',
			),
			static::$type . '_max_length'        => array(
				'label'   => __( 'Maximum Length', 'pods' ),
				'default' => 255,
				'type'    => 'number',
				'help'    => __( 'Set to -1 for no limit', 'pods' ),
			),
			static::$type . '_placeholder'       => array(
				'label'   => __( 'HTML Placeholder', 'pods' ),
				'default' => '',
				'type'    => 'text',
				'help'    => array(
					__( 'Placeholders can provide instructions or an example of the required data format for a field. Please note: It is not a replacement for labels or description text, and it is less accessible for people using screen readers.', 'pods' ),
					'https://www.w3.org/WAI/tutorials/forms/instructions/#placeholder-text',
				),
			),
		);

		return $options;
	}

	/**
	 * {@inheritdoc}
	 */
	public function schema( $options = null ) {

		$length = (int) pods_v( static::$type . '_max_length', $options, 255 );

		$schema = 'VARCHAR(' . $length . ')';

		if ( 255 < $length || $length < 1 ) {
			$schema = 'LONGTEXT';
		}

		return $schema;
	}

	/**
	 * {@inheritdoc}
	 */
	public function display( $value = null, $name = null, $options = null, $pod = null, $id = null ) {
		$value = $this->strip_html( $value, $options );
		$value = $this->strip_shortcodes( $value, $options );
		$value = $this->trim_whitespace( $value, $options );

		if ( 1 === (int) pods_v( static::$type . '_allow_shortcode', $options ) ) {
			$value = do_shortcode( $value );
		}

		return $value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function input( $name, $value = null, $options = null, $pod = null, $id = null ) {

		$options         = ( is_array( $options ) || is_object( $options ) ) ? $options : (array) $options;
		$form_field_type = PodsForm::$field_type;

		$value = $this->normalize_value_for_input( $value, $options );

		$is_read_only = (boolean) pods_v( 'read_only', $options, false );

		if ( isset( $options['name'] ) && ! pods_permission( $options ) ) {
			if ( $is_read_only ) {
				$options['readonly'] = true;
			} else {
				return;
			}
		} elseif ( ! pods_has_permissions( $options ) && $is_read_only ) {
			$options['readonly'] = true;
		}

		if ( ! empty( $options['disable_dfv'] ) ) {
			return pods_view( PODS_DIR . 'ui/fields/text.php', compact( array_keys( get_defined_vars() ) ) );
		}

		$type = pods_v( 'type', $options, static::$type );

		$args = compact( array_keys( get_defined_vars() ) );
		$args = (object) $args;

		$this->render_input_script( $args );
	}

	/**
	 * {@inheritdoc}
	 */
	public function validate( $value, $name = null, $options = null, $fields = null, $pod = null, $id = null, $params = null ) {
		$validate = parent::validate( $value, $name, $options, $fields, $pod, $id, $params );

		$errors = array();

		if ( is_array( $validate ) ) {
			$errors = $validate;
		}

		$check = $this->pre_save( $value, $id, $name, $options, $fields, $pod, $params );

		if ( is_array( $check ) ) {
			$errors = $check;
		} else {
			if ( '' !== $value && '' === $check ) {
				if ( $this->is_required( $options ) ) {
					$errors[] = __( 'This field is required.', 'pods' );
				}
			}
		}

		if ( ! empty( $errors ) ) {
			return $errors;
		}

		return $validate;
	}

	/**
	 * {@inheritdoc}
	 */
	public function pre_save( $value, $id = null, $name = null, $options = null, $fields = null, $pod = null, $params = null ) {
		$value = $this->strip_html( $value, $options );
		$value = $this->strip_shortcodes( $value, $options );
		$value = $this->trim_whitespace( $value, $options );

		$length = (int) pods_v( static::$type . '_max_length', $options, 255 );

		if ( 0 < $length && $length < pods_mb_strlen( $value ) ) {
			$value = pods_mb_substr( $value, 0, $length );
		}

		return $value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function ui( $id, $value, $name = null, $options = null, $fields = null, $pod = null ) {
		$value = $this->strip_html( $value, $options );
		$value = $this->strip_shortcodes( $value, $options );
		$value = $this->trim_whitespace( $value, $options );

		if ( 0 === (int) pods_v( static::$type . '_allow_html', $options, 0, true ) ) {
			$value = wp_trim_words( $value );
		}

		return $value;
	}
}
