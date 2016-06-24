<?php
/**
 * @package Pods\Fields
 */
class PodsField_OEmbed extends PodsField {

	/**
	 * Field Type Group
	 *
	 * @var string
	 * @since 2.0
	 */
	public static $group = 'Relationships / Media';

	/**
	 * Field Type Identifier
	 *
	 * @var string
	 * @since 2.0
	 */
	public static $type = 'oembed';

	/**
	 * Field Type Label
	 *
	 * @var string
	 * @since 2.0
	 */
	public static $label = 'oEmbed';

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
			self::$type . '_width' => array(
				'label' => __( 'Embed Width', 'pods' ),
				'default' => 0,
				'type' => 'number',
				'help' => __( 'Optional width to use for this oEmbed. Leave as 0 (zero) to default to none.', 'pods' )
			),
			self::$type . '_height' => array(
				'label' => __( 'Embed Height', 'pods' ),
				'default' => 0,
				'type' => 'number',
				'help' => __( 'Optional height to use for this oEmbed. Leave as 0 (zero) to default to none.', 'pods' )
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
		$schema = 'LONGTEXT';

		return $schema;
	}

	/**
	 * Change the way the value of the field is displayed with Pods::get
	 *
	 * @param mixed $value
	 * @param string $name
	 * @param array $options
	 * @param array $fields
	 * @param array $pod
	 * @param int $id
	 *
	 * @since 2.0
	 */
	public function display ( $value = null, $name = null, $options = null, $pod = null, $id = null ) {
		$value = $this->strip_html( $value, $options );

		/**
		 * @var $embed WP_Embed
		 */
		$embed = $GLOBALS[ 'wp_embed' ];
		$value = $embed->run_shortcode( $value );
		$value = $embed->autoembed( $value );

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
			$value = implode( ' ', $value );

		if ( isset( $options[ 'name' ] ) && false === PodsForm::permission( self::$type, $options[ 'name' ], $options, null, $pod, $id ) ) {
			if ( pods_var( 'read_only', $options, false ) )
				$options[ 'readonly' ] = true;
			else
				return;
		}
		elseif ( !pods_has_permissions( $options ) && pods_var( 'read_only', $options, false ) )
			$options[ 'readonly' ] = true;

		pods_view( PODS_DIR . 'ui/fields/oembed.php', compact( array_keys( get_defined_vars() ) ) );
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

		if ( is_array( $check ) )
			$errors = $check;
		else {
			if ( 0 < strlen( $value ) && strlen( $check ) < 1 ) {
				if ( 1 == pods_var( 'required', $options ) )
					$errors[] = __( 'This field is required.', 'pods' );
			}
		}

		if ( !empty( $errors ) )
			return $errors;

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
		$value = $this->strip_html( $value, $options );

		// Only allow ONE URL
		if ( ! empty( $value ) ) {
			$value = explode( ' ', $value );
			$value = $value[0];
		}

		// Strip shortcodes
		$value = strip_shortcodes( $value );

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
		$value = $this->strip_html( $value, $options );

		return $value;
	}

	/**
	 * Strip HTML based on options
	 *
	 * @param string $value
	 * @param array $options
	 *
	 * @return string
	 */
	public function strip_html ( $value, $options = null ) {
		if ( is_array( $value ) )
			$value = @implode( ' ', $value );

		$value = trim( $value );

		$value = strip_tags( $value );

		return $value;
	}

}
