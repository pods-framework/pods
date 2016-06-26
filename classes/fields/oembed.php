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
	 * Current embed width
	 * 
	 * @var int
	 * @since 2.7
	 */
	private $width = 0;
	
	/**
	 * Current embed height
	 * 
	 * @var int
	 * @since 2.7
	 */
	private $height = 0;

	/**
	 * Do things like register/enqueue scripts and stylesheets
	 *
	 * @since 2.0
	 */
	public function __construct () {
	}

    /**
     * Add admin_init actions
     *
     * @since 2.3
     */
    public function admin_init() {
        // AJAX for Uploads
        add_action( 'wp_ajax_oembed_update_preview', array( $this, 'admin_ajax_oembed_update_preview' ) );
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
			self::$type . '_show_preview' => array(
				'label' => __( 'Show preview', 'pods' ),
				'default' => 0,
				'type' => 'boolean'
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
		$value = $this->pre_save( $value, $id, $name, $options, null, $pod );

		$width = (int) pods_v( self::$type . '_width', $options );
		$height = (int) pods_v( self::$type . '_height', $options );
		$args = array();
		if ( $width > 0 ) {
			$args['width'] = $width;
		}
		if ( $height > 0 ) {
			$args['height'] = $height;
		}

		$value = wp_oembed_get( $value, $args );

		/**
		 * @var $embed WP_Embed
		 */
		/*$embed = $GLOBALS[ 'wp_embed' ];

		if ( 0 < $width && 0 < $height ) {
			$this->width = $width;
			$this->height = $height;
			
			// Setup [embed] shortcodes with set width/height
			$value = $this->autoembed( $value );
			
			// Run [embed] shortcodes
			$value = $embed->run_shortcode( $value );
		} else {
			// Autoembed URL normally
			$value = $embed->autoembed( $value );
		}*/

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
			$value = esc_url( $value[0] );
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
	 * @return mixed|string
	 * @since 2.0
	 */
	public function ui ( $id, $value, $name = null, $options = null, $fields = null, $pod = null ) {
		$value = $this->pre_save( $value, $id, $name, $options, $fields, $pod );

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

		// Strip HTML
		$value = strip_tags( $value );

		// Strip shortcodes
		$value = strip_shortcodes( $value );

		return $value;
	}

	/**
	 * Passes any unlinked URLs that are on their own line to {@link WP_Embed::shortcode()} for potential embedding.
	 *
	 * @see WP_Embed::autoembed()
	 * @see WP_Embed::autoembed_callback()
	 * 
	 * @uses PodsField_OEmbed::autoembed_callback()
	 *
	 * @param string $content The content to be searched.
	 * @return string Potentially modified $content.
	 * 
	 * @since 2.7
	 */
	public function autoembed( $content ) {

		// Replace line breaks from all HTML elements with placeholders.
		$content = wp_replace_in_html_tags( $content, array( "\n" => '<!-- wp-line-break -->' ) );

		// Find URLs that are on their own line.
		$content = preg_replace_callback( '|^(\s*)(https?://[^\s"]+)(\s*)$|im', array( $this, 'autoembed_callback' ), $content );

		// Put the line breaks back.
		return str_replace( '<!-- wp-line-break -->', "\n", $content );

	}

	/**
	 * Callback function for {@link WP_Embed::autoembed()}.
	 *
	 * @param array $match A regex match array.
	 * @return string The embed shortcode
	 * 
	 * @since 2.7
	 */
	public function autoembed_callback( $match ) {
		
		$shortcode = '[embed width="' . $this->width . '" height="' . $this->height . '"]' . $match[2] . '[/embed]';
		
		return $shortcode;

	}

    /**
     * Handle update preview AJAX
     *
     * @since 2.7
     */
	public function admin_ajax_oembed_update_preview() {

        // Sanitize input
        $params = pods_unslash( (array) $_POST );

        if (   isset( $params['_nonce_pods_oembed'] ) 
        	&& isset( $params['pods_field_oembed_value'] ) 
        	&& wp_verify_nonce( $params['_nonce_pods_oembed'], 'pods_field_oembed_preview' ) 
        ) {
        	$value = $this->strip_html( $params['pods_field_oembed_value'] );
        	$name = ( isset( $params['pods_field_oembed_name'] ) ) ? $params['pods_field_oembed_name'] : '';
        	$options = ( isset( $params['pods_field_oembed_options'] ) ) ? json_decode( $params['pods_field_oembed_options'], true ) : array();
        	$options['oembed_width'] = ( isset( $options['oembed_width'] ) ) ? (int) $options['oembed_width'] : 0;
        	$options['oembed_height'] = ( isset( $options['oembed_height'] ) ) ? (int) $options['oembed_height'] : 0;
        	$options['width'] = $options['oembed_width'];
        	$options['height'] = $options['oembed_height'];

        	wp_send_json_success( wp_oembed_get( $value, $options ) ); //$this->display( $value, $name, $options )
        }
        wp_send_json_error();

		die(); // Kill it!
	}

}
