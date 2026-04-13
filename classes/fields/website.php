<?php

// Don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

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

		static::$group = __( 'Text', 'pods' );
		static::$label = __( 'Website', 'pods' );

	}

	/**
	 * {@inheritdoc}
	 */
	public function options() {
		$options = [
			static::$type . '_format'      => [
				'label'                 => __( 'Format', 'pods' ),
				'default'               => 'normal',
				'type'                  => 'pick',
				'data'                  => [
					'normal'            => __( 'https://example.com/', 'pods' ),
					'no-www'            => __( 'https://example.com/ (remove www)', 'pods' ),
					'force-www'         => __( 'https://www.example.com/ (force www if no sub-domain provided)', 'pods' ),
					'no-http'           => __( 'example.com', 'pods' ),
					'no-http-no-www'    => __( 'example.com (force removal of www)', 'pods' ),
					'no-http-force-www' => __( 'www.example.com (force www if no sub-domain provided)', 'pods' ),
					'none'              => __( 'No format', 'pods' ),
				],
				'pick_format_single'    => 'dropdown',
				'pick_show_select_text' => 0,
				'dependency'            => true,
			],
			static::$type . '_allow_port'  => [
				'label'      => __( 'Allow port in URL', 'pods' ),
				'default'    => apply_filters( 'pods_form_ui_field_website_port', 0, static::$type ),
				'type'       => 'boolean',
				'dependency' => true,
			],
			static::$type . '_clickable'   => [
				'label'      => __( 'Output as a link', 'pods' ),
				'default'    => apply_filters( 'pods_form_ui_field_website_clickable', 0, static::$type ),
				'type'       => 'boolean',
				'dependency' => true,
			],
			static::$type . '_new_window'  => [
				'label'      => __( 'Open link in new window', 'pods' ),
				'default'    => apply_filters( 'pods_form_ui_field_website_new_window', 0, static::$type ),
				'type'       => 'boolean',
				'depends-on' => [ static::$type . '_clickable' => true ],
			],
			static::$type . '_nofollow'    => [
				'label'      => __( 'Make link "nofollow" to exclude from search engines', 'pods' ),
				'default'    => apply_filters( 'pods_form_ui_field_website_nofollow', 0, static::$type ),
				'type'       => 'boolean',
				'depends-on' => [ static::$type . '_clickable' => true ],
			],
			static::$type . '_max_length'  => [
				'label'   => __( 'Maximum Length', 'pods' ),
				'default' => 255,
				'type'    => 'number',
				'help'    => __( 'Set to -1 for no limit', 'pods' ),
			],
			static::$type . '_html5'       => [
				'label'       => __( 'Enable HTML5 Input Field', 'pods' ),
				'default'     => apply_filters( 'pods_form_ui_field_html5', 0, static::$type ),
				'type'        => 'boolean',
				'excludes-on' => [ static::$type . '_format' => [ 'no-http', 'no-http-no-www', 'no-http-force-www' ] ],
			],
			static::$type . '_placeholder' => [
				'label'   => __( 'HTML Placeholder', 'pods' ),
				'default' => '',
				'type'    => 'text',
				'help'    => [
					__( 'Placeholders can provide instructions or an example of the required data format for a field. Please note: It is not a replacement for labels or description text, and it is less accessible for people using screen readers.', 'pods' ),
					'https://www.w3.org/WAI/tutorials/forms/instructions/#placeholder-text',
				],
			],
		];

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

		if ( 1 === (int) pods_v( static::$type . '_clickable', $options ) && 0 < strlen( (string) $value ) ) {
			$link = '<a href="%s"%s>%s</a>';

			$atts = '';
			$rel  = [];

			if ( 1 === (int) pods_v( static::$type . '_nofollow', $options ) ) {
				$rel[] = 'nofollow';
			}

			if ( 1 === (int) pods_v( static::$type . '_new_window', $options ) ) {
				$rel[] = 'noopener';
				$rel[] = 'noreferrer';

				$atts .= ' target="_blank"';
			}

			if ( ! empty( $rel ) ) {
				$atts .= ' rel="' . esc_attr( implode( ' ', $rel ) ) . '"';
			}

			$value = sprintf( $link, esc_url( $value ), $atts, esc_html( $value ) );
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

		// Ensure proper format
		if ( is_array( $value ) ) {
			foreach ( $value as $k => $repeatable_value ) {
				$value[ $k ] = $this->pre_save( $repeatable_value, $id, $name, $options, null, $pod );
			}
		} else {
			$value = $this->pre_save( $value, $id, $name, $options, null, $pod );
		}

		$field_type = 'website';

		if ( isset( $options['name'] ) && ! pods_permission( $options ) ) {
			if ( pods_v_bool( 'read_only_restricted', $options ) ) {
				$options['readonly'] = true;

				$field_type = 'text';
			} else {
				return;
			}
		} elseif ( ! pods_has_permissions( $options ) ) {
			if ( pods_v_bool( 'read_only', $options ) ) {
				$options['readonly'] = true;

				$field_type = 'text';
			}
		}

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
	public function validate( $value, $name = null, $options = null, $fields = null, $pod = null, $id = null, $params = null ) {
		$validate = parent::validate( $value, $name, $options, $fields, $pod, $id, $params );

		$errors = [];

		if ( is_array( $validate ) ) {
			$errors = $validate;
		}

		$label = wp_strip_all_tags( pods_v( 'label', $options, ucwords( str_replace( '_', ' ', $name ) ) ) );

		$check = $this->pre_save( $value, $id, $name, $options, $fields, $pod, $params );

		if ( is_array( $check ) ) {
			$errors = $check;
		} else {
			if ( 0 < strlen( (string) $value ) && '' === $check ) {
				if ( $this->is_required( $options ) ) {
					// translators: %s is the field label.
					$errors[] = sprintf( __( 'The %s field is required.', 'pods' ), $label );
				} else {
					// translators: %s is the field label.
					$errors[] = sprintf( __( 'Invalid website provided for the field %s.', 'pods' ), $label );
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
		$options = ( is_array( $options ) || is_object( $options ) ) ? $options : (array) $options;

		// Update from an array input field (like link) if the field updates
		if ( is_array( $value ) ) {
			if ( isset( $value['url'] ) ) {
				$value = $value['url'];
			} else {
				$value = $this->normalize_value_for_input( $value, $options );

				// @todo Eventually rework this further.
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
			$value = $this->strip_html( $value, $options );
			$value = $this->strip_shortcodes( $value, $options );
			$value = $this->trim_whitespace( $value, $options );

			return $value;
		}

		if ( is_array( $value ) ) {
			if ( isset( $value['scheme'] ) ) {
				$value = $this->build_url( $value, $options );
			} else {
				$value = @implode( '', $value );
			}
		}

		if ( false === strpos( (string) $value, '://' ) && 0 !== strpos( (string) $value, '//' ) ) {
			$value = 'http://' . $value;
		}

		$url = wp_parse_url( $value );

		if ( empty( $url ) || count( $url ) < 2 ) {
			$value = '';
		} else {
			$defaults = [
				'scheme'   => 'http',
				'host'     => '',
				'port'     => '',
				'path'     => '/',
				'query'    => '',
				'fragment' => '',
			];

			$url = array_merge( $defaults, $url );

			if ( 'normal' === pods_v( static::$type . '_format', $options ) ) {
				$value = $this->build_url( $url, $options );
			} elseif ( 'no-www' === pods_v( static::$type . '_format', $options ) ) {
				if ( 0 === strpos( (string) $url['host'], 'www.' ) ) {
					$url['host'] = substr( (string) $url['host'], 4 );
				}

				$value = $this->build_url( $url, $options );
			} elseif ( 'force-www' === pods_v( static::$type . '_format', $options ) ) {
				if ( false !== strpos( (string) $url['host'], '.' ) && false === strpos( (string) $url['host'], 'www', 0 ) ) {
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
				if ( 0 === strpos( (string) $url['host'], 'www.' ) ) {
					$url['host'] = substr( (string) $url['host'], 4 );
				}

				$value = $this->build_url( $url, $options );
				$value = str_replace( trim( $url['scheme'] . '://', ':' ), '', $value );

				if ( '/' === $url['path'] ) {
					$value = trim( $value, '/' );
				}
			} elseif ( 'no-http-force-www' === pods_v( static::$type . '_format', $options ) ) {
				if ( false !== strpos( (string) $url['host'], '.' ) && false === strpos( (string) $url['host'], 'www', 0 ) ) {
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
	public function build_url( $url, $options = [] ) {

		$url = (array) $url;

		$allow_port = (int) pods_v( static::$type . '_allow_port', $options, 0 );

		// If port is not allowed, always set to empty
		if ( 0 === $allow_port ) {
			$url['port'] = '';
		}

		if ( function_exists( 'http_build_url' ) ) {
			return http_build_url( $url );
		}

		$defaults = [
			'scheme'   => 'http',
			'host'     => '',
			'port'     => '',
			'path'     => '/',
			'query'    => '',
			'fragment' => '',
		];

		$url = array_merge( $defaults, $url );

		$new_url = [];

		$new_url[] = trim( $url['scheme'] . '://', ':' );
		$new_url[] = $url['host'];

		if ( ! empty( $url['port'] ) ) {
			$new_url[] = ':' . $url['port'];
		}

		$new_url[] = '/' . ltrim( $url['path'], '/' );

		if ( isset( $url['query'] ) ) {
			$query = ltrim( $url['query'], '?' );

			if ( ! empty( $query ) ) {
				$new_url[] = '?' . $query;
			}
		}

		if ( isset( $url['fragment'] ) ) {
			$fragment = ltrim( $url['fragment'], '#' );

			if ( ! empty( $fragment ) ) {
				$new_url[] = '#' . $fragment;
			}
		}

		// Pull all of the parts back together
		$new_url = implode( '', $new_url );

		return $new_url;

	}

}
