<?php
class PodsForm {

    static $field = null;
    static $type = null;
    static $options = array();

    /**
     * Generate UI for a Form and it's Fields
     *
     * @license http://www.gnu.org/licenses/gpl-2.0.html
     * @since 2.0.0
     */
    public function __construct () {
        add_filter( 'pods_form_ui_label_text', 'wp_kses_post', 9, 1 );
        add_filter( 'pods_form_ui_label_help', 'wp_kses_post', 9, 1 );
        add_filter( 'pods_form_ui_comment_text', 'wp_kses_post', 9, 1 );
        add_filter( 'pods_form_ui_comment_text', 'the_content', 9, 1 );
    }

    /**
     * Output a field's label
     *
     * @since 2.0.0
     */
    public static function label ( $name, $label, $help = '', $options = null ) {
        $name_clean = self::clean( $name );
        $name_more_clean = self::clean( $name, true );

        if ( null === $options && !empty( self::$options ) )
            $options = self::$options;
        else
            $options = self::options( null, $options );

        $label = apply_filters( 'pods_form_ui_label_text', $label, $name, $help, $options );
        $help = apply_filters( 'pods_form_ui_label_help', $help, $name, $label, $options );

        ob_start();

        $type = 'label';
        $attributes = array();
        $attributes[ 'class' ] = 'pods-form-ui-' . $type . ' pods-form-ui-' . $type . '-' . $name_more_clean;
        $attributes[ 'for' ] = 'pods-form-ui-' . $name_clean;
        $attributes = self::merge_attributes( $attributes, $name, $type, $options, false );

        pods_view( PODS_DIR . 'ui/fields/_label.php', compact( $name, $label, $help, $attributes, $options ) );

        $output = ob_get_clean();

        return apply_filters( 'pods_form_ui_' . $type, $output, $name, $label, $help, $attributes, $options );
    }

    /**
     * Output a Field Comment Paragraph
     */
    public static function comment ( $name, $message = null, $options = null ) {
        $name_more_clean = self::clean( $name, true );

        if ( null === $options && !empty( self::$options ) )
            $options = self::$options;
        else
            $options = self::options( null, $options );

        if ( isset( $options[ 'description' ] ) && !empty( $options[ 'description' ] ) )
            $message = $options[ 'description' ];
        elseif ( empty( $message ) )
            return;

        $message = apply_filters( 'pods_form_ui_comment_text', $message, $name, $options );

        ob_start();

        $type = 'comment';
        $attributes = array();
        $attributes[ 'class' ] = 'pods-form-ui-' . $type . ' pods-form-ui-' . $type . '-' . $name_more_clean;
        $attributes = self::merge_attributes( $attributes, $name, $type, $options, false );

        pods_view( PODS_DIR . 'ui/fields/_comment.php', compact( $name, $attributes, $options ) );

        $output = ob_get_clean();

        return apply_filters( 'pods_form_ui_' . $type, $output, $name, $message, $attributes, $options );
    }

    /**
     * Output a field
     *
     * @since 2.0.0
     */
    public static function field ($name, $value, $type = 'text', $options = null, $pod = null, $id = null) {
        $options = self::options( $type, $options );

        if ( isset( $options[ 'default' ] ) && null === $value )
            $value = $options[ 'default' ];
        $value = apply_filters( 'pods_form_ui_field_' . $type . '_value', $value, $name, $options, $pod, $id );

        ob_start();

        if ( method_exists( get_class(), 'field_' . $type ) )
            call_user_func( array( get_class(), 'field_' . $type ), $name, $value, $options );
        elseif ( is_object( self::$field ) && class_exists( self::$field ) && method_exists( self::$field, 'input' ) )
            call_user_func( array( self::$field, 'input' ), $name, $value, $options, $pod, $id );
        else
            do_action('pods_form_ui_field_' . $type, $name, $value, $options, $pod, $id);

        $output = ob_get_clean();

        return apply_filters('pods_form_ui_field_' . $type, $output, $name, $value, $options, $pod, $id);
    }

    /**
     * Output field type 'db'
     *
     * Used for field names and other places where only [a-z0-9_] is accepted
     *
     * @since 2.0.0
     */
    protected function field_db ($name, $value = null, $options = null) {
        $options = (array) $options;

        pods_view( PODS_DIR . 'ui/fields/_db.php', compact( $name, $value, $options ) );
    }

	/**
	 * Output a hidden field
	 */
	protected function field_hidden($name, $value = null, $options = null) {
        $options = (array) $options;

        pods_view( PODS_DIR . 'ui/fields/_hidden.php', compact( $name, $value, $options ) );
	}

    /**
     * Output a field's attributes
     *
     * @since 2.0.0
     */
    public static function attributes ( $attributes, $name = null, $type = null, $options = null ) {
        $attributes = (array) apply_filters( 'pods_form_ui_field_' . $type . '_attributes', $attributes, $name, $options );
        foreach ( $attributes as $attribute => $value ) {
            if ( null === $value )
                continue;
            echo ' ' . esc_attr( (string) $attribute ) . '="' . esc_attr( (string) $value ) . '"';
        }
    }

    /**
     * Merge attributes and handle classes
     *
     * @since 2.0.0
     */
    protected function merge_attributes ( $attributes, $name = null, $type = null, $options = null ) {
        $options = (array) $options;
        if ( !in_array( $type, array( 'label', 'comment' ) ) ) {
            $name_clean = self::clean( $name );
            $name_more_clean = self::clean( $name, true );
            $_attributes = array();
            $_attributes[ 'name' ] = $name;
            $_attributes[ 'data-name-clean' ] = $name_more_clean;
            $_attributes[ 'id' ] = 'pods-form-ui-' . $name_clean;
            $_attributes[ 'class' ] = 'pods-form-ui-field-type-' . $type . ' pods-form-ui-field-name-' . $name_more_clean;
            $attributes = array_merge( $_attributes, (array) $attributes );
        }
        if ( isset( $options[ 'attributes' ] ) && is_array( $options[ 'attributes' ] ) && !empty( $options[ 'attributes' ] ) ) {
            $attributes = array_merge( $attributes, $options[ 'attributes' ] );
        }
        if ( isset( $options[ 'class' ] ) && !empty( $options[ 'class' ] ) ) {
            if ( is_array( $options[ 'class' ] ) )
                $options[ 'class' ] = implode( ' ', $options[ 'class' ] );
            $options[ 'class' ] = (string) $options[ 'class' ];
            if ( isset( $attributes[ 'class' ] ) )
                $attributes[ 'class' ] = $attributes[ 'class' ] . ' ' . $options[ 'class' ];
            else
                $attributes[ 'class' ] = $options[ 'class' ];
        }
        $attributes = (array) apply_filters( 'pods_form_ui_field_' . $type . '_merge_attributes', $attributes, $name, $options );
        return $attributes;
    }

    /*
     * Setup options for a field and store them for later use
     *
     * @since 2.0.0
     */
    public static function options ( $type, $options ) {
        $options = (array) $options;
        $defaults = array();

        $core_defaults = array(
            'description' => '',
            'default' => null,
            'attributes' => array(),
            'class' => '',
            'max_length' => null,
            'size' => 'medium'
        );

        if ( $type != self::$type ) {
            $class = "PodsField_{$type}";
            if ( class_exists( $class ) )
                self::$field = new $class();
            else
                self::$field = null;
        }

        if ( is_object( self::$field ) && method_exists( self::$field, 'options' ) ) {
            $defaults = (array) call_user_func( array( self::$field, 'options' ) );

            // Map generic options to type-specific
            if ( 0 < strlen( $options[ 'format_type' ] ) && isset( $defaults[ self::$type . '_format_type' ] ) ) {
                $options[ self::$type . '_format_type' ] = $options[ 'format_type' ];
                unset( $options[ 'format_type' ] );
            }
        }

        $defaults = array_merge_recursive( $core_defaults, $defaults );

        foreach ( $defaults as $option => $settings ) {
            $default = $settings;
            if ( is_array( $settings ) && isset( $settings[ 'default' ] ) )
                $default = $settings[ 'default' ];
            if ( !isset( $options[ $option ] ) )
                $options[ $option ] = $default;
        }

        self::$type = $type;
        self::$options = $options;

        return self::$options;
    }

    /*
     * Clean a value for use in class / id
     *
     * @since 2.0.0
     */
    public static function clean ( $input, $noarray = false, $db_field = false ) {
        $input = str_replace( array( '--1', '__1' ), '00000', $input );
        if ( false !== $noarray )
            $input = preg_replace( '/\[\d*\]/', '-', $input );
        $output = str_replace( array( '[', ']' ), '-', strtolower( $input ) );
        $output = preg_replace( '/([^a-z0-9-_])/', '', $output );
        $output = trim( str_replace( array( '__', '_', '--' ), '-', $output ), '-' );
        $output = str_replace( '00000', '--1', $output );
        if ( false !== $db_field )
            $output = str_replace( '-', '_', $output );
        return $output;
    }
}
