<?php

/**
 * @package Pods\Fields
 */
class PodsField_OEmbed extends PodsField {

	/**
	 * {@inheritdoc}
	 */
	public static $group = 'Relationships / Media';

	/**
	 * {@inheritdoc}
	 */
	public static $type = 'oembed';

	/**
	 * {@inheritdoc}
	 */
	public static $label = 'oEmbed';

	/**
	 * {@inheritdoc}
	 */
	public static $prepare = '%s';

	/**
	 * Available oEmbed providers
	 *
	 * @var array
	 * @since 2.7.0
	 */
	private $providers = array();

	/**
	 * Current embed width
	 *
	 * @var int
	 * @since 2.7.0
	 */
	private $width = 0;

	/**
	 * Current embed height
	 *
	 * @var int
	 * @since 2.7.0
	 */
	private $height = 0;

	/**
	 * {@inheritdoc}
	 */
	public function setup() {

		self::$label = __( 'oEmbed', 'pods' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function admin_init() {

		// AJAX for Uploads
		add_action( 'wp_ajax_oembed_update_preview', array( $this, 'admin_ajax_oembed_update_preview' ) );
	}

	/**
	 * {@inheritdoc}
	 */
	public function options() {

		$options = array(
			static::$type . '_repeatable'   => array(
				'label'             => __( 'Repeatable Field', 'pods' ),
				'default'           => 0,
				'type'              => 'boolean',
				'help'              => __( 'Making a field repeatable will add controls next to the field which allows users to Add/Remove/Reorder additional values. These values are saved in the database as an array, so searching and filtering by them may require further adjustments".', 'pods' ),
				'boolean_yes_label' => '',
				'dependency'        => true,
				'developer_mode'    => true,
			),
			static::$type . '_width'        => array(
				'label'   => __( 'Embed Width', 'pods' ),
				'default' => 0,
				'type'    => 'number',
				'help'    => __( 'Optional width to use for this oEmbed. Leave as 0 (zero) to default to none.', 'pods' ),
			),
			static::$type . '_height'       => array(
				'label'   => __( 'Embed Height', 'pods' ),
				'default' => 0,
				'type'    => 'number',
				'help'    => __( 'Optional height to use for this oEmbed. Leave as 0 (zero) to default to none.', 'pods' ),
			),
			static::$type . '_show_preview' => array(
				'label'   => __( 'Show preview', 'pods' ),
				'default' => 0,
				'type'    => 'boolean',
			),
		);

		// Get all unique provider host names
		$unique_providers = array();
		foreach ( $this->get_providers() as $provider ) {
			if ( ! in_array( $provider['host'], $unique_providers, true ) ) {
				$unique_providers[] = $provider['host'];
			}
		}
		sort( $unique_providers );

		// Only add the options if we have data
		if ( ! empty( $unique_providers ) ) {
			$options[ static::$type . '_restrict_providers' ] = array(
				'label'      => __( 'Restrict to providers', 'pods' ),
				'help'       => __( 'Restrict input to specific WordPress oEmbed compatible providers.', 'pods' ),
				'type'       => 'boolean',
				'default'    => 0,
				'dependency' => true,
			);
			$options[ static::$type . '_enable_providers' ]   = array(
				'label'      => __( 'Select enabled providers', 'pods' ),
				'depends-on' => array( static::$type . '_restrict_providers' => true ),
				'group'      => array(),
			);
			// Add all the oEmbed providers
			foreach ( $unique_providers as $provider ) {
				$options[ static::$type . '_enable_providers' ]['group'][ static::$type . '_enabled_providers_' . tag_escape( $provider ) ] = array(
					'label'   => $provider,
					'type'    => 'boolean',
					'default' => 0,
				);
			}
		}//end if

		return $options;
	}

	/**
	 * {@inheritdoc}
	 */
	public function schema( $options = null ) {

		$schema = 'LONGTEXT';

		return $schema;
	}

	/**
	 * {@inheritdoc}
	 */
	public function display( $value = null, $name = null, $options = null, $pod = null, $id = null ) {

		$value = $this->pre_save( $value, $id, $name, $options, null, $pod );

		$width  = (int) pods_v( static::$type . '_width', $options );
		$height = (int) pods_v( static::$type . '_height', $options );
		$args   = array();
		if ( $width > 0 ) {
			$args['width'] = $width;
		}
		if ( $height > 0 ) {
			$args['height'] = $height;
		}

		$value = wp_oembed_get( $value, $args );

		return $value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function input( $name, $value = null, $options = null, $pod = null, $id = null ) {

		$options         = (array) $options;
		$form_field_type = PodsForm::$field_type;

		if ( is_array( $value ) ) {
			$value = implode( ' ', $value );
		}

		if ( isset( $options['name'] ) && false === PodsForm::permission( static::$type, $options['name'], $options, null, $pod, $id ) ) {
			if ( pods_v( 'read_only', $options, false ) ) {
				$options['readonly'] = true;
			} else {
				return;
			}
		} elseif ( ! pods_has_permissions( $options ) && pods_v( 'read_only', $options, false ) ) {
			$options['readonly'] = true;
		}

		pods_view( PODS_DIR . 'ui/fields/oembed.php', compact( array_keys( get_defined_vars() ) ) );
	}

	/**
	 * {@inheritdoc}
	 */
	public function validate( $value, $name = null, $options = null, $fields = null, $pod = null, $id = null, $params = null ) {

		$errors = array();

		$check = $this->pre_save( $value, $id, $name, $options, $fields, $pod, $params );

		if ( is_array( $check ) ) {
			$errors = $check;
		} else {
			if ( 0 < strlen( $value ) && '' === $check ) {
				if ( $this->is_required( $options ) ) {
					$errors[] = __( 'This field is required.', 'pods' );
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

		$value = $this->strip_html( $value, $options );

		// Only allow ONE URL
		if ( ! empty( $value ) ) {
			$value = explode( ' ', $value );
			$value = esc_url( $value[0] );
		}

		if ( $this->validate_provider( $value, $options ) ) {
			return $value;
		} else {
			return false;
		}

	}

	/**
	 * {@inheritdoc}
	 */
	public function ui( $id, $value, $name = null, $options = null, $fields = null, $pod = null ) {

		$value = $this->pre_save( $value, $id, $name, $options, $fields, $pod );

		return $value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function strip_html( $value, $options = null ) {

		if ( is_array( $value ) ) {
			// @codingStandardsIgnoreLine
			$value = @implode( ' ', $value );
		}

		$value = trim( $value );

		if ( empty( $value ) ) {
			return $value;
		}

		// Strip HTML
		$value = strip_tags( $value );

		// Strip shortcodes
		$value = strip_shortcodes( $value );

		return $value;
	}

	/**
	 * Passes any unlinked URLs that are on their own line to {@link WP_Embed::shortcode()} for potential embedding.
	 *
	 * @see   WP_Embed::autoembed()
	 * @see   WP_Embed::autoembed_callback()
	 *
	 * @uses  PodsField_OEmbed::autoembed_callback()
	 *
	 * @param string $content The content to be searched.
	 *
	 * @return string Potentially modified $content.
	 *
	 * @since 2.7.0
	 */
	public function autoembed( $content ) {

		// Replace line breaks from all HTML elements with placeholders.
		$content = wp_replace_in_html_tags( $content, array( "\n" => '<!-- wp-line-break -->' ) );

		// Find URLs that are on their own line.
		$content = preg_replace_callback(
			'|^(\s*)(https?://[^\s"]+)(\s*)$|im', array(
				$this,
				'autoembed_callback',
			), $content
		);

		// Put the line breaks back.
		return str_replace( '<!-- wp-line-break -->', "\n", $content );

	}

	/**
	 * Callback function for {@link WP_Embed::autoembed()}.
	 *
	 * @param array $match A regex match array.
	 *
	 * @return string The embed shortcode
	 *
	 * @since 2.7.0
	 */
	public function autoembed_callback( $match ) {

		$shortcode = '[embed width="' . $this->width . '" height="' . $this->height . '"]' . $match[2] . '[/embed]';

		return $shortcode;

	}

	/**
	 * Get a list of available providers from the WP_oEmbed class
	 *
	 * @see   wp-includes/class-oembed.php
	 * @return array $providers {
	 *     Array of provider data with regex as key
	 *
	 * @type string URL for this provider
	 * @type int
	 * @type string Hostname for this provider
	 * }
	 *
	 * @since 2.7.0
	 */
	public function get_providers() {

		// Return class property if already set
		if ( ! empty( $this->providers ) ) {
			return $this->providers;
		}

		if ( ! class_exists( 'WP_oEmbed' ) && file_exists( ABSPATH . WPINC . '/class-oembed.php' ) ) {
			require_once ABSPATH . WPINC . '/class-oembed.php';
		}

		// Return an empty array if no providers could be found
		$providers = array();

		if ( function_exists( '_wp_oembed_get_object' ) ) {
			$wp_oembed = _wp_oembed_get_object();
			$providers = $wp_oembed->providers;

			foreach ( $providers as $key => $provider ) {
				$url  = wp_parse_url( $provider[0] );
				$host = $url['host'];
				$tmp  = explode( '.', $host );

				if ( count( $tmp ) === 3 ) {
					// Take domain names like .co.uk in consideration
					if ( ! in_array( 'co', $tmp, true ) ) {
						unset( $tmp[0] );
					}
				} elseif ( count( $tmp ) === 4 ) {
					// Take domain names like .co.uk in consideration
					unset( $tmp[0] );
				}

				$host = implode( '.', $tmp );

				$providers[ $key ]['host'] = $host;
			}

			$this->providers = $providers;
		}//end if

		return $providers;

	}

	/**
	 * Takes a URL and returns the corresponding oEmbed provider's URL, if there is one.
	 *
	 * @since 2.7.0
	 * @access public
	 *
	 * @see    WP_oEmbed::get_provider()
	 *
	 * @param string       $url  The URL to the content.
	 * @param string|array $args Optional provider arguments.
	 *
	 * @return false|string False on failure, otherwise the oEmbed provider URL.
	 */
	public function get_provider( $url, $args = array() ) {

		if ( ! class_exists( 'WP_oEmbed' ) && file_exists( ABSPATH . WPINC . '/class-oembed.php' ) ) {
			require_once ABSPATH . WPINC . '/class-oembed.php';
		}

		if ( function_exists( '_wp_oembed_get_object' ) ) {
			$wp_oembed = _wp_oembed_get_object();

			if ( is_callable( array( $wp_oembed, 'get_provider' ) ) ) {
				return $wp_oembed->get_provider( $url, $args );
			}
		}

		return false;
	}

	/**
	 * Validate a value with the enabled oEmbed providers (if required).
	 *
	 * @since 2.7.0
	 *
	 * @param string $value   Field value.
	 * @param array  $options Field options.
	 *
	 * @return bool
	 */
	public function validate_provider( $value, $options ) {

		// Check if we need to validate.
		if ( 0 === (int) pods_v( static::$type . '_restrict_providers', $options ) ) {
			return true;
		}

		$providers = $this->get_providers();

		// Filter existing providers.
		foreach ( $providers as $key => $provider ) {
			$fieldname = static::$type . '_enabled_providers_' . tag_escape( $provider['host'] );

			/**
			 * @todo Future compat to enable serialised strings as field options
			 */

			/**
			 * Current solution: all separate field options.
			 */
			if ( empty( $options[ $fieldname ] ) ) {
				unset( $providers[ $key ] );
			}
		}

		// Value validation.
		$provider_match = $this->get_provider( $value );

		foreach ( $providers as $match => $provider ) {
			if ( $provider_match === $match ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Handle update preview AJAX.
	 *
	 * @since 2.7.0
	 */
	public function admin_ajax_oembed_update_preview() {

		// Sanitize input.
		// @codingStandardsIgnoreLine
		$params = pods_unslash( (array) $_POST );

		if ( ! empty( $params['_nonce_pods_oembed'] ) && ! empty( $params['pods_field_oembed_value'] ) && wp_verify_nonce( $params['_nonce_pods_oembed'], 'pods_field_oembed_preview' ) ) {
			$value = $this->strip_html( $params['pods_field_oembed_value'] );

			$name    = '';
			$options = array();

			if ( ! empty( $params['pods_field_oembed_name'] ) ) {
				$name = $this->strip_html( $params['pods_field_oembed_name'] );
			}

			if ( ! empty( $params['pods_field_oembed_options'] ) ) {
				$options = $params['pods_field_oembed_options'];
			}

			// Load the field to get it's options.
			$options = pods_api()->load_field( (object) $options );

			// Field options are stored here, if not, just stay with the full options array.
			if ( ! empty( $options['options'] ) ) {
				$options = $options['options'];
			}

			// Run display function to run oEmbed.
			$value = $this->display( $value, $name, $options );

			if ( empty( $value ) ) {
				$value = __( 'Please choose a valid oEmbed URL.', 'pods' );
				wp_send_json_error( $value );
			} else {
				wp_send_json_success( $value );
			}
		}//end if
		wp_send_json_error( __( 'Unauthorized request', 'pods' ) );

		die();
		// Kill it!
	}

}
