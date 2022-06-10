<?php

use Pods\Static_Cache;

/**
 * @package Pods\Fields
 */
class PodsField_Link extends PodsField_Website {

	/**
	 * {@inheritdoc}
	 */
	public static $group = 'Text';

	/**
	 * {@inheritdoc}
	 */
	public static $type = 'link';

	/**
	 * {@inheritdoc}
	 */
	public static $label = 'Link';

	/**
	 * {@inheritdoc}
	 */
	public static $prepare = '%s';

	/**
	 * {@inheritdoc}
	 */
	public function setup() {

		static::$group = __( 'Text', 'pods' );
		static::$label = __( 'Link', 'pods' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function options() {

		$options = array(
			static::$type . '_format'            => array(
				'label'   => __( 'Format', 'pods' ),
				'default' => 'normal',
				'type'    => 'pick',
				'data'    => array(
					'none'              => __( 'No URL format restrictions', 'pods' ),
					'normal'            => __( 'http://example.com/', 'pods' ),
					'no-www'            => __( 'http://example.com/ (remove www)', 'pods' ),
					'force-www'         => __( 'http://www.example.com/ (force www if no sub-domain provided)', 'pods' ),
					'no-http'           => __( 'example.com', 'pods' ),
					'no-http-no-www'    => __( 'example.com (force removal of www)', 'pods' ),
					'no-http-force-www' => __( 'www.example.com (force www if no sub-domain provided)', 'pods' ),
				),
				'pick_show_select_text' => 0,
			),
			static::$type . '_select_existing'   => array(
				'label'      => __( 'Enable Selecting from Existing Links', 'pods' ),
				'default'    => 1,
				'type'       => 'boolean',
				'dependency' => true,
			),
			static::$type . '_new_window'        => array(
				'label'      => __( 'Open link in new window by default', 'pods' ),
				'default'    => apply_filters( 'pods_form_ui_field_link_new_window', 0, static::$type ),
				'type'       => 'boolean',
				'dependency' => false,
			),
			'output_options'                     => array(
				'label' => __( 'Link Text Output Options', 'pods' ),
				'type'  => 'boolean_group',
				'boolean_group' => array(
					static::$type . '_allow_shortcode' => array(
						'label'      => __( 'Allow Shortcodes', 'pods' ),
						'default'    => 0,
						'type'       => 'boolean',
						'dependency' => true,
					),
					static::$type . '_allow_html'      => array(
						'label'      => __( 'Allow HTML', 'pods' ),
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
			static::$type . '_html5'             => array(
				'label'   => __( 'Enable HTML5 Input Field', 'pods' ),
				'default' => apply_filters( 'pods_form_ui_field_html5', 0, static::$type ),
				'type'    => 'boolean',
			),
		);

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

		// Validate for an array because display is also used for the get_post_meta filters along the function chain
		if ( ! is_array( $value ) ) {
			return $value;
		}

		// Ensure proper format
		$value = $this->pre_save( $value, $id, $name, $options, null, $pod );

		if ( ! empty( $value['text'] ) ) {
			$value['text'] = $this->strip_html( $value['text'], $options );
			$value['text'] = $this->strip_shortcodes( $value['text'], $options );
			$value['text'] = $this->trim_whitespace( $value['text'], $options );
		}

		if ( ! empty( $value['url'] ) ) {

			$link = '<a href="%s"%s>%s</a>';

			// Build the URL
			$url = $this->build_url( wp_parse_url( $value['url'] ) );

			// Display URL as text by default. If text provided, use the text input
			$text = $url;

			if ( ! empty( $value['text'] ) ) {
				$text = $value['text'];
			}

			$atts = '';

			if ( ! empty( $value['target'] ) || ( ! isset( $value['target'] ) && 1 === (int) pods_v( static::$type . '_new_window', $options ) ) ) {
				// Possible support for other targets in future
				$atts .= ' target="' . esc_attr( $value['target'] ) . '" rel="noopener noreferrer"';
			}

			// Do shortcodes if this is enabled
			if ( 1 === (int) pods_v( static::$type . '_allow_shortcode', $options ) ) {
				$text = do_shortcode( $text );
			}

			// Return the value
			$value = sprintf( $link, esc_url( $url ), $atts, $text );

		} elseif ( ! empty( $value['text'] ) ) {
			// No URL data found (probably database error), return text is this is available
			$value = $value['text'];
		}//end if

		// Return database value or display value if above conditions are met
		return $value;

	}

	/**
	 * Change the way the a list of values of the field are displayed with Pods::field
	 *
	 * @param mixed|null  $value   Field value.
	 * @param string|null $name    Field name.
	 * @param array|null  $options Field options.
	 * @param array|null  $pod     Pod options.
	 * @param int|null    $id      Item ID.
	 *
	 * @return mixed|null|string
	 *
	 * @since 2.7.0
	 */
	public function display_list( $value = null, $name = null, $options = null, $pod = null, $id = null ) {

		return call_user_func_array( array( $this, 'display' ), func_get_args() );

	}

	/**
	 * {@inheritdoc}
	 */
	public function input( $name, $value = null, $options = null, $pod = null, $id = null ) {

		$options         = ( is_array( $options ) || is_object( $options ) ) ? $options : (array) $options;
		$form_field_type = PodsForm::$field_type;
		$field_type      = 'link';

		// Ensure proper format
		$value = $this->pre_save( $value, $id, $name, $options, null, $pod );

		pods_view( PODS_DIR . 'ui/fields/' . $field_type . '.php', compact( array_keys( get_defined_vars() ) ) );
	}

	/**
	 * {@inheritdoc}
	 */
	public function validate( $value, $name = null, $options = null, $fields = null, $pod = null, $id = null, $params = null ) {
		$validate = parent::validate( $value, $name, $options, $fields, $pod, $id, $params );
		$check    = $this->pre_save( $value, $id, $name, $options, $fields, $pod, $params );

		// Remove valid field keys from returning in website field.
		if ( is_array( $value ) ) {
			$validate = array_diff_key( $validate, $check );
		}

		$errors = array();
		if ( is_array( $validate ) ) {
			$errors = $validate;
		}

		if ( ! empty( $value['url'] ) && 0 < strlen( $value['url'] ) && '' === $check['url'] ) {
			$label = strip_tags( pods_v( 'label', $options, ucwords( str_replace( '_', ' ', $name ) ) ) );

			if ( $this->is_required( $options ) ) {
				$errors[] = sprintf( __( 'The %s field is required.', 'pods' ), $label );
			} else {
				$errors[] = sprintf( __( 'Invalid link provided for the field %s.', 'pods' ), $label );
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

		// Update from a single (non array) input field (like website) if the field updates
		if ( is_string( $value ) ) {
			$value = array( 'url' => $value );
		}

		$value = array_merge(
			array(
				'url'    => '',
				'text'   => '',
				'target' => '',
			), (array) $value
		);

		// Start URL format
		if ( ! empty( $value['url'] ) ) {
			$value['url'] = $this->validate_url( $value['url'], $options );
		}

		// Start Title format
		if ( ! empty( $value['text'] ) ) {
			$value['text'] = $this->strip_html( $value['text'], $options );
			$value['text'] = $this->strip_shortcodes( $value['text'], $options );
			$value['text'] = $this->trim_whitespace( $value['text'], $options );
		}

		// Start Target format
		if ( ! empty( $value['target'] ) ) {
			$value['target'] = $this->validate_target( $value['target'] );
		} elseif ( ! isset( $value['target'] ) && 1 === (int) pods_v( static::$type . '_new_window', $options, 0 ) ) {
			$value['target'] = '_blank';
		}

		return $value;

	}

	/**
	 * Init the editor needed for WP Link modal to work
	 */
	public function validate_link_modal() {
		$init = (boolean) pods_static_cache_get( 'init', __METHOD__ );

		if ( $init ) {
			return;
		}

		if ( ! did_action( 'wp_enqueue_editor' ) && ! has_action( 'shutdown', [ $this, 'add_link_modal' ] ) ) {
			add_action( 'shutdown', [ $this, 'add_link_modal' ] );
		}

		pods_static_cache_set( 'init', 1, __METHOD__ );
	}

	/**
	 * Echo the link modal code
	 */
	public function add_link_modal() {

		if ( ! class_exists( '_WP_Editors', false ) && file_exists( ABSPATH . WPINC . '/class-wp-editor.php' ) ) {
			require_once ABSPATH . WPINC . '/class-wp-editor.php';
		}

		if ( class_exists( '_WP_Editors' ) && method_exists( '_WP_Editors', 'wp_link_dialog' ) ) {
			_WP_Editors::wp_link_dialog();
		} else {
			echo '<div style="display:none;">';
			wp_editor( '', 'pods-link-editor-hidden' );
			echo '</div>';
		}

	}
}
