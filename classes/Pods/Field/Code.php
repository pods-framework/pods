<?php
/**
 * @package Pods
 * @category Field Types
 */
class Pods_Field_Code extends Pods_Field {

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
	 * {@inheritdoc}
	 */
	public function __construct() {

	}

	/**
	 * {@inheritdoc}
	 */
	public function options() {

		$options = array(
			self::$type . '_repeatable' => array(
				'label'             => __( 'Repeatable Field', 'pods' ),
				'default'           => 0,
				'type'              => 'boolean',
				'help'              => __( 'Making a field repeatable will add controls next to the field which allows users to Add/Remove/Reorder additional values. These values are saved in the database as an array, so searching and filtering by them may require further adjustments".', 'pods' ),
				'boolean_yes_label' => '',
				'dependency'        => true,
				'developer_mode'    => true
			),
			'output_options'            => array(
				'label' => __( 'Output Options', 'pods' ),
				'group' => array(
					self::$type . '_allow_shortcode' => array(
						'label'      => __( 'Allow Shortcodes?', 'pods' ),
						'default'    => 0,
						'type'       => 'boolean',
						'dependency' => true
					)
				)
			),
			self::$type . '_max_length' => array(
				'label'   => __( 'Maximum Length', 'pods' ),
				'default' => - 1,
				'type'    => 'number',
				'help'    => __( 'Set to -1 for no limit', 'pods' )
			)
			/*,
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
	 * {@inheritdoc}
	 */
	public function schema( $options = null ) {

		$length = (int) pods_v( self::$type . '_max_length', $options, - 1, true );

		$schema = 'LONGTEXT';

		if ( 0 < $length ) {
			if ( $length <= 255 ) {
				$schema = 'VARCHAR(' . (int) $length . ')';
			} elseif ( $length <= 16777215 ) {
				$schema = 'MEDIUMTEXT';
			}
		}

		return $schema;

	}

	/**
	 * {@inheritdoc}
	 */
	public function display( $value = null, $name = null, $options = null, $pod = null, $id = null ) {

		if ( 1 == pods_v( self::$type . '_allow_shortcode', $options, 0 ) ) {
			$value = do_shortcode( $value );
		}

		return $value;

	}

	/**
	 * {@inheritdoc}
	 */
	public function input( $name, $value = null, $options = array(), $pod = null, $id = null ) {

		$form_field_type = Pods_Form::$field_type;

		if ( is_array( $value ) ) {
			$value = implode( "\n", $value );
		}

		$field_type = 'codemirror';

		do_action( 'pods_form_ui_field_code_' . $field_type, $name, $value, $options, $pod, $id );
		do_action( 'pods_form_ui_field_code', $field_type, $name, $value, $options, $pod, $id );

		pods_view( PODS_DIR . 'ui/fields/' . $field_type . '.php', compact( array_keys( get_defined_vars() ) ) );

	}

	/**
	 * {@inheritdoc}
	 */
	public function pre_save( $value, $id = null, $name = null, $options = null, $fields = null, $pod = null, $params = null ) {

		$length = (int) pods_v( self::$type . '_max_length', $options, - 1, true );

		if ( 0 < $length && $length < pods_mb_strlen( $value ) ) {
			$value = pods_mb_substr( $value, 0, $length );
		}

		return $value;

	}

}