<?php
/**
 * @package Pods\Fields
 */
class PodsField_Website extends PodsField {

	/**
	 * {@inheritdoc}
	 */
	public static $group = 'Text';

	/**
	 * {@inheritdoc}
	 */
	public static $type = 'website';

	/**
	 * {@inheritdoc}
	 */
	public static $label = 'Website';

	/**
	 * {@inheritdoc}
	 */
	public static $prepare = '%s';

	/**
	 * {@inheritdoc}
	 */
	public function setup() {

		self::$label = __( 'Website', 'pods' );

	}

	/**
	 * {@inheritdoc}
	 */
	public function options() {
		$options = array(
			static::$type . '_repeatable'  => array(
				'label'             => __( 'Repeatable Field', 'pods' ),
				'default'           => 0,
				'type'              => 'boolean',
				'help'              => __( 'Making a field repeatable will add controls next to the field which allows users to Add/Remove/Reorder additional values. These values are saved in the database as an array, so searching and filtering by them may require further adjustments".', 'pods' ),
				'boolean_yes_label' => '',
				'dependency'        => true,
				'developer_mode'    => true,
			),
			static::$type . '_format'      => array(
				'label'      => __( 'Format', 'pods' ),
				'default'    => 'normal',
				'type'       => 'pick',
				'data'       => array(
					'normal'            => __( 'http://example.com/', 'pods' ),
					'no-www'            => __( 'http://example.com/ (remove www)', 'pods' ),
					'force-www'         => __( 'http://www.example.com/ (force www if no sub-domain provided)', 'pods' ),
					'no-http'           => __( 'example.com', 'pods' ),
					'no-http-no-www'    => __( 'example.com (force removal of www)', 'pods' ),
					'no-http-force-www' => __( 'www.example.com (force www if no sub-domain provided)', 'pods' ),
					'none'              => __( 'No format', 'pods' ),
				),
				'dependency' => true,
			),
			static::$type . '_allow_port'  => array(
				'label'      => __( 'Allow port in URL?', 'pods' ),
				'default'    => apply_filters( 'pods_form_ui_field_website_port', 0, static::$type ),
				'type'       => 'boolean',
				'dependency' => true,
			),
			static::$type . '_clickable'   => array(
				'label'      => __( 'Output as a link?', 'pods' ),
				'default'    => apply_filters( 'pods_form_ui_field_website_clickable', 0, static::$type ),
				'type'       => 'boolean',
				'dependency' => true,
			),
			static::$type . '_new_window'  => array(
				'label'      => __( 'Open link in new window?', 'pods' ),
				'default'    => apply_filters( 'pods_form_ui_field_website_new_window', 0, static::$type ),
				'type'       => 'boolean',
				'depends-on' => array( static::$type . '_clickable' => true ),
			),
			static::$type . '_max_length'  => array(
				'label'   => __( 'Maximum Length', 'pods' ),
				'default' => 255,
				'type'    => 'number',
				'help'    => __( 'Set to -1 for no limit', 'pods' ),
			),
			static::$type . '_html5'       => array(
				'label'       => __( 'Enable HTML5 Input Field?', 'pods' ),
				'default'     => apply_filters( 'pods_form_ui_field_html5', 0, static::$type ),
				'type'        => 'boolean',
				'excludes-on' => array( static::$type . '_format' => array( 'no-http', 'no-http-no-www', 'no-http-force-www' ) ),
			),
			static::$type . '_placeholder' => array(
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
		// Ensure proper format
		$value = $this->pre_save( $value, $id, $name, $options, null, $pod );

		if ( 1 === (int) pods_v( static::$type . '_clickable', $options ) && 0 < strlen( $value ) ) {
			$link = '<a href="%s"%s>%s</a>';

			$atts = '';

			if ( 1 === (int) pods_v( static::$type . '_new_window', $options ) ) {
				$atts .= ' target="_blank"';
			}

			$value = sprintf( $link, esc_url( $value ), $atts, esc_html( $value ) );
		}

		return $value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function input( $name, $value = null, $options = null, $pod = null, $id = null ) {
		$options         = (array) $options;
		$form_field_type = PodsForm::$field_type;

		// Ensure proper format
		$value = $this->pre_save( $value, $id, $name, $options, null, $pod );

		$field_type = 'website';

		if ( isset( $options['name'] ) && false === PodsForm::permission( static::$type, $options['name'], $options, null, $pod, $id ) ) {
			if ( pods_v( 'read_only', $options, false ) ) {
				$options['readonly'] = true;

				$field_type = 'text';
			} else {
				return;
			}
		} elseif ( ! pods_has_permissions( $options ) && pods_v( 'read_only', $options, false ) ) {
			$options['readonly'] = true;

			$field_type = 'text';
		}

		pods_view( PODS_DIR . 'ui/fields/' . $field_type . '.php', compact( array_keys( get_defined_vars() ) ) );
	}

	/**
	 * {@inheritdoc}
	 */
	public function validate( $value, $name = null, $options = null, $fields = null, $pod = null, $id = null, $params = null ) {
		$errors = array();

		$label = strip_tags( pods_v( 'label', $options, ucwords( str_replace( '_', ' ', $name ) ) ) );

		$check = $this->pre_save( $value, $id, $name, $options, $fields, $pod, $params );

		if ( is_array( $check ) ) {
			$errors = $check;
		} else {
			if ( 0 < strlen( $value ) && '' === $check ) {
				if ( $this->is_required( $options ) ) {
					$errors[] = sprintf( __( 'The %s field is required.', 'pods' ), $label );
				} else {
					$errors[] = sprintf( __( 'Invalid website provided for the field %s.', 'pods' ), $label );
				}
			}
		}

		if ( ! empty( $errors ) ) {
			return $errors;
		}

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function pre_save( $value, $id = null, $name = null, $options = null, $fields = null, $pod = null, $params = null ) {
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

		$length = (int) pods_v( static::$type . '_max_length', $options, 255 );

		if ( 0 < $length && $length < pods_mb_strlen( $value ) ) {
			$value = pods_mb_substr( $value, 0, $length );
		}

		return $value;
	}

	/**
	 * Validate an URL with the options
	 *
	 * @param string|array $value   Field value.
	 * @param array|null   $options Field options.
	 *
	 * @return string
	 *
	 * @since 2.7.0
	 */
	public function validate_url( $value, $options = null ) {
		if ( empty( $value ) ) {
			return $value;
		}

		if ( 'none' === pods_v( static::$type . '_format', $options ) ) {
			return $this->strip_html( $value, $options );
		}

		if ( is_array( $value ) ) {
			if ( isset( $value['scheme'] ) ) {
				$value = $this->build_url( $value, $options );
			} else {
				$value = @implode( '', $value );
			}
		}

		if ( false === strpos( $value, '://' ) && 0 !== strpos( $value, '//' ) ) {
			$value = 'http://' . $value;
		}

		$url = wp_parse_url( $value );

		if ( empty( $url ) || count( $url ) < 2 ) {
			$value = '';
		} else {
			$defaults = array(
				'scheme'   => 'http',
				'host'     => '',
				'port'     => '',
				'path'     => '/',
				'query'    => '',
				'fragment' => '',
			);

			$url = array_merge( $defaults, $url );

			if ( 'normal' === pods_v( static::$type . '_format', $options ) ) {
				$value = $this->build_url( $url, $options );
			} elseif ( 'no-www' === pods_v( static::$type . '_format', $options ) ) {
				if ( 0 === strpos( $url['host'], 'www.' ) ) {
					$url['host'] = substr( $url['host'], 4 );
				}

				$value = $this->build_url( $url, $options );
			} elseif ( 'force-www' === pods_v( static::$type . '_format', $options ) ) {
				if ( false !== strpos( $url['host'], '.' ) && false === strpos( $url['host'], 'www', 0 ) ) {
					$url['host'] = 'www.' . $url['host'];
				}

				$value = $this->build_url( $url, $options );
			} elseif ( 'no-http' === pods_v( static::$type . '_format', $options ) ) {
				$value = $this->build_url( $url, $options );
				$value = str_replace( trim( $url['scheme'] . '://', ':' ), '', $value );

				if ( '/' === $url['path'] ) {
					$value = trim( $value, '/' );
				}
			} elseif ( 'no-http-no-www' === pods_v( static::$type . '_format', $options ) ) {
				if ( 0 === strpos( $url['host'], 'www.' ) ) {
					$url['host'] = substr( $url['host'], 4 );
				}

				$value = $this->build_url( $url, $options );
				$value = str_replace( trim( $url['scheme'] . '://', ':' ), '', $value );

				if ( '/' === $url['path'] ) {
					$value = trim( $value, '/' );
				}
			} elseif ( 'no-http-force-www' === pods_v( static::$type . '_format', $options ) ) {
				if ( false !== strpos( $url['host'], '.' ) && false === strpos( $url['host'], 'www', 0 ) ) {
					$url['host'] = 'www.' . $url['host'];
				}

				$value = $this->build_url( $url, $options );
				$value = str_replace( trim( $url['scheme'] . '://', ':' ), '', $value );

				if ( '/' === $url['path'] ) {
					$value = trim( $value, '/' );
				}
			}//end if
		}//end if

		return $value;
	}

	/**
	 * Validate an target attribute with the options
	 *
	 * @param string $value Field value.
	 *
	 * @return string
	 *
	 * @since 2.7.0
	 */
	public function validate_target( $value ) {
		if ( ! empty( $value ) && '_blank' === $value ) {
			$value = '_blank';
		} else {
			$value = '';
		}
		return $value;
	}

	/**
	 * Build a url from url parts
	 *
	 * @param array|string $url     URL value.
	 * @param array        $options Field options.
	 *
	 * @return string
	 */
	public function build_url( $url, $options = array() ) {

		$url = (array) $url;

		$allow_port = (int) pods_v( static::$type . '_allow_port', $options, 0 );

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
			'fragment' => '',
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
