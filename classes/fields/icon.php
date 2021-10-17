<?php
/**
 * @package Pods\Fields
 */
class PodsField_Icon extends PodsField {

	/**
	 * Field Type Identifier
	 *
	 * @var string
	 * @since 2.0
	 */
	public static $type = 'icon';

	/**
	 * Field Type Label
	 *
	 * @var string
	 * @since 2.0
	 */
	public static $label = 'Icon Picker';

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
		self::$label = __( 'Icon Picker', 'pods' );

		// Load the icon picker library
		if ( ! class_exists( 'Icon_Picker' ) ) {
			require_once PODS_DIR . '/vendor/kucrut/icon-picker/icon-picker.php';
		}
		$icon_picker = Icon_Picker::instance();
	}

	/**
	 * Add options and set defaults to
	 *
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
				'label' => __( 'Icon libraries', 'pods' ),
				'group' => array(
				)
			),
		);

		return $options;
	}

	/**
	 * Define the current field's schema for DB table storage
	 *
	 * @param array $options
	 *
	 * @return string
	 * @since 2.0
	 */
	public function schema ( $options = null ) {
		$schema = 'LONGTEXT';
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
	 * @return mixed|null|string
	 * @since 2.0
	 */
	public function display ( $value = null, $name = null, $options = null, $pod = null, $id = null ) {
		//$value = $this->strip_html( $value, $options );
		//$value = icon_picker_get_icon_url( $value );

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

		if ( isset( $options[ 'name' ] ) && false === PodsForm::permission( self::$type, $options[ 'name' ], $options, null, $pod, $id ) ) {
			if ( pods_var( 'read_only', $options, false ) ) {
				$options[ 'readonly' ] = true;
			} else {
				return;
			}
		} elseif ( ! pods_has_permissions( $options ) && pods_var( 'read_only', $options, false ) ) {
			$options[ 'readonly' ] = true;
		}

		pods_view( PODS_DIR . 'ui/fields/icon.php', compact( array_keys( get_defined_vars() ) ) );
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
	 *
	 * @param null $params
	 * @return array|bool
	 * @since 2.0
	 */
	public function validate ( $value, $name = null, $options = null, $fields = null, $pod = null, $id = null, $params = null ) {
		$errors = array();

		$check = $this->pre_save( $value, $id, $name, $options, $fields, $pod, $params );

		if ( empty( $check ) ) {
			if ( pods_var( 'required', $options ) ) {
				$errors[] = __( 'This field is required.', 'pods' );
			}
		}

		if ( ! empty( $errors ) ) {
			return $errors;
		}

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

		$value = array_intersect_key( $value, array( 'type' => '', 'icon' => '' ) );

		if ( empty( $value['type'] ) || empty( $value['icon'] ) ) {
			$value = '';
		}

		$value = array_map( 'strip_tags', $value );

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
	 * @return mixed|string
	 * @since 2.0
	 */
	public function ui ( $id, $value, $name = null, $options = null, $fields = null, $pod = null ) {
		/*$value = $this->strip_html( $value, $options );

		if ( 0 == pods_var( self::$type . '_allow_html', $options, 0, null, true ) )
			$value = wp_trim_words( $value );*/

		return $value;
	}
}
