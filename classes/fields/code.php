<?php

/**
 * Handles code field data and operations
 *
 * @package Pods\Fields
 */
class PodsField_Code extends PodsField {

	/**
	 * {@inheritdoc}
	 */
	public static $group = 'Paragraph';

	/**
	 * {@inheritdoc}
	 */
	public static $type = 'code';

	/**
	 * {@inheritdoc}
	 */
	public static $label = 'Code (Syntax Highlighting)';

	/**
	 * {@inheritdoc}
	 */
	public static $prepare = '%s';

	/**
	 * {@inheritdoc}
	 */
	public function setup() {

		static::$group = __( 'Paragraph', 'pods' );
		static::$label = __( 'Code (Syntax Highlighting)', 'pods' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function options() {
		return [
			'output_options'              => [
				'label'         => __( 'Output Options', 'pods' ),
				'type'          => 'boolean_group',
				'boolean_group' => [
					static::$type . '_trim'             => [
						'label'   => __( 'Trim extra whitespace before/after contents', 'pods' ),
						'default' => 1,
						'type'    => 'boolean',
					],
					static::$type . '_trim_lines'       => [
						'label'   => __( 'Trim whitespace at the end of lines', 'pods' ),
						'default' => 0,
						'type'    => 'boolean',
					],
					static::$type . '_trim_p_brs'       => [
						'label'   => __( 'Remove blank lines including empty "p" tags and "br" tags', 'pods' ),
						'default' => 0,
						'type'    => 'boolean',
					],
					static::$type . '_trim_extra_lines' => [
						'label'   => __( 'Remove extra blank lines (when there are 3+ blank lines, replace with a maximum of 2)', 'pods' ),
						'default' => 0,
						'type'    => 'boolean',
					],
					static::$type . '_allow_shortcode'  => [
						'label'      => __( 'Allow Shortcodes', 'pods' ),
						'default'    => 0,
						'type'       => 'boolean',
						'dependency' => true,
					],
				],
			],
			static::$type . '_max_length' => [
				'label'   => __( 'Maximum Length', 'pods' ),
				'default' => - 1,
				'type'    => 'number',
				'help'    => __( 'Set to -1 for no limit', 'pods' ),
			],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function schema( $options = null ) {

		$length = (int) pods_v( static::$type . '_max_length', $options, 0 );

		$schema = 'LONGTEXT';

		if ( 0 < $length ) {
			$schema = 'VARCHAR(' . $length . ')';
		}

		return $schema;
	}

	/**
	 * {@inheritdoc}
	 */
	public function display( $value = null, $name = null, $options = null, $pod = null, $id = null ) {

		if ( 1 === (int) pods_v( static::$type . '_allow_shortcode', $options, 0 ) ) {
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

		$value = $this->normalize_value_for_input( $value, $options, "\n" );

		$field_type = 'codemirror';

		do_action( "pods_form_ui_field_code_{$field_type}", $name, $value, $options, $pod, $id );
		do_action( 'pods_form_ui_field_code', $field_type, $name, $value, $options, $pod, $id );

		if ( ! empty( $options['disable_dfv'] ) ) {
			return pods_view( PODS_DIR . 'ui/fields/' . $field_type . '.php', compact( array_keys( get_defined_vars() ) ) );
		}

		$type = pods_v( 'type', $options, static::$type );

		$args = compact( array_keys( get_defined_vars() ) );
		$args = (object) $args;

		$this->render_input_script( $args );
	}

	/**
	 * {@inheritdoc}
	 */
	public function pre_save( $value, $id = null, $name = null, $options = null, $fields = null, $pod = null, $params = null ) {
		$value = $this->trim_whitespace( $value, $options );

		$length = (int) pods_v( static::$type . '_max_length', $options, 0 );

		if ( 0 < $length && $length < pods_mb_strlen( $value ) ) {
			$value = pods_mb_substr( $value, 0, $length );
		}

		return $value;
	}
}
