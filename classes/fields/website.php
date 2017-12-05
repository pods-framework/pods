<?php
/**
 * @package Pods\Fields
 */
class PodsField_Website extends PodsField {

	/**
	 * Field Type Group
	 *
	 * @var string
	 * @since 2.0
	 */
	public static $group = 'Text';

	/**
	 * Field Type Identifier
	 *
	 * @var string
	 * @since 2.0
	 */
	public static $type = 'website';

	/**
	 * Field Type Label
	 *
	 * @var string
	 * @since 2.0
	 */
	public static $label = 'Website';

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

		self::$label = __( 'Website', 'pods' );

	}

	/**
	 * Add options and set defaults to
	 *
	 * @param array $options
	 *
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
			self::$type . '_format' => array(
				'label' => __( 'Format', 'pods' ),
				'default' => 'normal',
				'type' => 'pick',
				'data' => array(
					'normal' => __( 'http://example.com/', 'pods' ),
					'no-www' => __( 'http://example.com/ (remove www)', 'pods' ),
					'force-www' => __( 'http://www.example.com/ (force www if no sub-domain provided)', 'pods' ),
					'no-http' => __( 'example.com', 'pods' ),
					'no-http-no-www' => __( 'example.com (force removal of www)', 'pods' ),
					'no-http-force-www' => __( 'www.example.com (force www if no sub-domain provided)', 'pods' ),
					'none' => __( 'No format', 'pods' ),
				),
				'dependency' => true,
			),
			self::$type . '_allow_port' => array(
				'label' => __( 'Allow port in URL?', 'pods' ),
				'default' => apply_filters( 'pods_form_ui_field_website_port', 0, self::$type ),
				'type' => 'boolean',
				'dependency' => true,
			),
			self::$type . '_clickable' => array(
				'label' => __( 'Output as a link?', 'pods' ),
				'default' => apply_filters( 'pods_form_ui_field_website_clickable', 0, self::$type ),
				'type' => 'boolean',
				'dependency' => true,
			),
			self::$type . '_new_window' => array(
				'label' => __( 'Open link in new window?', 'pods' ),
				'default' => apply_filters( 'pods_form_ui_field_website_new_window', 0, self::$type ),
				'type' => 'boolean',
				'depends-on' => array( self::$type . '_clickable' => true ),
			),
			self::$type . '_max_length' => array(
				'label' => __( 'Maximum Length', 'pods' ),
				'default' => 255,
				'type' => 'number',
				'help' => __( 'Set to -1 for no limit', 'pods' ),
			),
			self::$type . '_html5' => array(
				'label' => __( 'Enable HTML5 Input Field?', 'pods' ),
				'default' => apply_filters( 'pods_form_ui_field_html5', 0, self::$type ),
				'type' => 'boolean',
				'excludes-on' => array( self::$type . '_format' => array( 'no-http', 'no-http-no-www', 'no-http-force-www' ) ),
			),
			self::$type . '_placeholder' => array(
				'label' => __( 'HTML Placeholder', 'pods' ),
				'default' => '',
				'type' => 'text',
				'help' => array(
					__( 'Placeholders can provide instructions or an example of the required data format for a field. Please note: It is not a replacement for labels or description text, and it is less accessible for people using screen readers.', 'pods' ),
					'https://www.w3.org/WAI/tutorials/forms/instructions/#placeholder-text',
				),
			),/*,
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
	 * Define the current field's schema for DB table storage
	 *
	 * @param array $options
	 *
	 * @return array
	 * @since 2.0
	 */
	public function schema ( $options = null ) {
		$length = (int) pods_var( self::$type . '_max_length', $options, 255 );

		$schema = 'VARCHAR(' . $length . ')';

		if ( 255 < $length || $length < 1 )
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
	 * @return mixed|null
	 * @since 2.0
	 */
	public function display ( $value = null, $name = null, $options = null, $pod = null, $id = null ) {
		// Ensure proper format
		$value = $this->pre_save( $value, $id, $name, $options, null, $pod );

		if ( 1 == pods_v( self::$type . '_clickable', $options ) && 0 < strlen( $value ) ) {
			$link = '<a href="%s"%s>%s</a>';

			$atts = '';

			if ( 1 == pods_v( self::$type . '_new_window', $options ) ) {
				$atts .= ' target="_blank"';
			}

			$value = sprintf( $link, esc_url( $value ), $atts, esc_html( $value ) );
		}

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

		// Ensure proper format
		$value = $this->pre_save( $value, $id, $name, $options, null, $pod );

		$field_type = 'website';

		if ( isset( $options[ 'name' ] ) && false === PodsForm::permission( self::$type, $options[ 'name' ], $options, null, $pod, $id ) ) {
			if ( pods_var( 'read_only', $options, false ) ) {
				$options[ 'readonly' ] = true;

				$field_type = 'text';
			}
			else
				return;
		}
		elseif ( !pods_has_permissions( $options ) && pods_var( 'read_only', $options, false ) ) {
			$options[ 'readonly' ] = true;

			$field_type = 'text';
		}

		pods_view( PODS_DIR . 'ui/fields/' . $field_type . '.php', compact( array_keys( get_defined_vars() ) ) );
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
	 * @return bool|array
	 *
	 * @since 2.0
	 */
	public function validate ( $value, $name = null, $options = null, $fields = null, $pod = null, $id = null, $params = null ) {
		$errors = array();

		$label = strip_tags( pods_var_raw( 'label', $options, ucwords( str_replace( '_', ' ', $name ) ) ) );

		$check = $this->pre_save( $value, $id, $name, $options, $fields, $pod, $params );

		if ( is_array( $check ) )
			$errors = $check;
		else {
			if ( 0 < strlen( $value ) && strlen( $check ) < 1 ) {
				if ( 1 == pods_var( 'required', $options ) )
					$errors[] = sprintf( __( 'The %s field is required.', 'pods' ), $label );
				else
					$errors[] = sprintf( __( 'Invalid website provided for the field %s.', 'pods' ), $label );
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
	 * @return string
	 *
	 * @since 2.0
	 */
	public function pre_save ( $value, $id = null, $name = null, $options = null, $fields = null, $pod = null, $params = null ) {
		$options = (array) $options;

		// Update from a array input field (like link) if the field updates
		if ( is_array( $value ) ) {
			if ( isset( $value['url'] ) ) {
				$value = $value['url'];
			} else {
				$value = implode( ' ', $value );
			}
		}

		$value = $this->validate_url( $value, $options );

		$length = (int) pods_var( self::$type . '_max_length', $options, 255 );

		if ( 0 < $length && $length < pods_mb_strlen( $value ) ) {
			$value = pods_mb_substr( $value, 0, $length );
		}

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
	 * @return string
	 *
	 * @since 2.0
	 */
	public function ui ( $id, $value, $name = null, $options = null, $fields = null, $pod = null ) {
		$value = $this->display( $value, $name, $options, $pod, $id );

		return $value;
	}

	/**
	 * Validate an URL with the options
	 *
	 * @param string $value
	 * @param array $options
	 *
	 * @return string
	 *
	 * @since 2.7
	 */
	public function validate_url( $value, $options = null ) {
		if ( empty( $value ) ) {
			return $value;
		}

		if ( 'none' === pods_var( self::$type . '_format', $options ) ) {
			return $this->strip_html( $value, $options );
		}

		if ( is_array( $value ) ) {
			if ( isset( $value[ 'scheme' ] ) )
				$value = $this->build_url( $value, $options );
			else
				$value = implode( '', $value );
		}

		if ( false === strpos( $value, '://' ) && 0 !== strpos( $value, '//' ) )
			$value = 'http://' . $value;

		$url = @parse_url( $value );

		if ( empty( $url ) || count( $url ) < 2 )
			$value = '';
		else {
			$defaults = array(
				'scheme' => 'http',
				'host' => '',
				'port' => '',
				'path' => '/',
				'query' => '',
				'fragment' => ''
			);

			$url = array_merge( $defaults, $url );

			if ( 'normal' == pods_var( self::$type . '_format', $options ) )
				$value = $this->build_url( $url, $options );
			elseif ( 'no-www' == pods_var( self::$type . '_format', $options ) ) {
				if ( 0 === strpos( $url[ 'host' ], 'www.' ) )
					$url[ 'host' ] = substr( $url[ 'host' ], 4 );

				$value = $this->build_url( $url, $options );
			}
			elseif ( 'force-www' == pods_var( self::$type . '_format', $options ) ) {
				if ( false !== strpos( $url[ 'host' ], '.' ) && false === strpos( $url[ 'host' ], '.', 1 ) )
					$url[ 'host' ] = 'www.' . $url[ 'host' ];

				$value = $this->build_url( $url, $options );
			}
			elseif ( 'no-http' == pods_var( self::$type . '_format', $options ) ) {
				$value = $this->build_url( $url, $options );
				$value = str_replace( trim( $url[ 'scheme' ] . '://', ':' ), '', $value );

				if ( '/' == $url[ 'path' ] )
					$value = trim( $value, '/' );
			}
			elseif ( 'no-http-no-www' == pods_var( self::$type . '_format', $options ) ) {
				if ( 0 === strpos( $url[ 'host' ], 'www.' ) )
					$url[ 'host' ] = substr( $url[ 'host' ], 4 );

				$value = $this->build_url( $url, $options );
				$value = str_replace( trim( $url[ 'scheme' ] . '://', ':' ), '', $value );

				if ( '/' == $url[ 'path' ] )
					$value = trim( $value, '/' );
			}
			elseif ( 'no-http-force-www' == pods_var( self::$type . '_format', $options ) ) {
				if ( false !== strpos( $url[ 'host' ], '.' ) && false === strpos( $url[ 'host' ], '.', 1 ) )
					$url[ 'host' ] = 'www.' . $url[ 'host' ];

				$value = $this->build_url( $url, $options );
				$value = str_replace( trim( $url[ 'scheme' ] . '://', ':' ), '', $value );

				if ( '/' == $url[ 'path' ] )
					$value = trim( $value, '/' );
			}
		}

		return $value;
	}

	/**
	 * Strip HTML based on options
	 *
	 * @param string $value
	 * @param array $options
	 *
	 * @return string
	 *
	 * @since 2.7
	 */
	public function strip_html ( $value, $options = null ) {
		if ( is_array( $value ) )
			$value = @implode( ' ', $value );

		$value = trim( $value );

		if ( empty( $value ) )
			return $value;

		$options = (array) $options;

		if ( 1 == pods_var( self::$type . '_allow_html', $options, 0, null, true ) ) {
			$allowed_html_tags = '';

			if ( 0 < strlen( pods_var( self::$type . '_allowed_html_tags', $options ) ) ) {
				$allowed_html_tags = explode( ' ', trim( pods_var( self::$type . '_allowed_html_tags', $options ) ) );
				$allowed_html_tags = '<' . implode( '><', $allowed_html_tags ) . '>';
			}

			if ( !empty( $allowed_html_tags ) && '<>' != $allowed_html_tags )
				$value = strip_tags( $value, $allowed_html_tags );
		}
		else
			$value = strip_tags( $value );

		return $value;
	}

	/**
	 * Validate an target attribute with the options
	 *
	 * @param string $value
	 *
	 * @return string
	 *
	 * @since 2.7
	 */
	public function validate_target( $value ) {
		if ( ! empty( $value ) && $value == '_blank' ) {
			$value = '_blank';
		} else {
			$value = '';
		}
		return $value;
	}

	/**
	 * Build a url from url parts
	 *
	 * @param array|string $url
	 * @param array        $options
	 *
	 * @return string
	 */
	public function build_url( $url, $options = array() ) {

		$url = (array) $url;

		$allow_port = (int) pods_v( self::$type . '_allow_port', $options, 0 );

		// If port is not allowed, always set to empty
		if ( 0 === $allow_port ) {
			$url['port'] = '';
		}

		if ( function_exists( 'http_build_url' ) ) {
			return http_build_url( $url );
		}

		$defaults = array(
			'scheme'   => 'http',
			'host'     => '',
			'port'     => '',
			'path'     => '/',
			'query'    => '',
			'fragment' => ''
		);

		$url = array_merge( $defaults, $url );

		$new_url = array();

		$new_url[] = trim( $url['scheme'] . '://', ':' );
		$new_url[] = $url['host'];

		if ( ! empty( $url['port'] ) ) {
			$new_url[] = ':' . $url['port'];
		}

		$new_url[] = '/' . ltrim( $url['path'], '/' );

		if ( ! empty( $url['query'] ) ) {
			$new_url[] = '?' . ltrim( $url['query'], '?' );
		}

		if ( ! empty( $url['fragment'] ) ) {
			$new_url[] = '#' . ltrim( $url['fragment'], '#' );
		}

		// Pull all of the parts back together
		$new_url = implode( '', $new_url );

		return $new_url;

	}

}
