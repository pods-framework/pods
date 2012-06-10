<?php
class PodsForm {

    static $field = null;

    static $field_type = null;

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
        if ( is_array( $label ) ) {
            $options = $label;
            $label = $options[ 'label' ];
            if ( empty( $label ) )
                $label = ucwords( str_replace( '_', ' ', $name ) );
            $help = $options[ 'help' ];
        }
        else
            $options = self::options( null, $options );

        $label = apply_filters( 'pods_form_ui_label_text', $label, $name, $help, $options );
        $help = apply_filters( 'pods_form_ui_label_help', $help, $name, $label, $options );

        ob_start();

        $name_clean = self::clean( $name );
        $name_more_clean = self::clean( $name, true );

        $type = 'label';
        $attributes = array();
        $attributes[ 'class' ] = 'pods-form-ui-' . $type . ' pods-form-ui-' . $type . '-' . $name_more_clean;
        $attributes[ 'for' ] = 'pods-form-ui-' . $name_clean;
        $attributes = self::merge_attributes( $attributes, $name, $type, $options, false );

        pods_view( PODS_DIR . 'ui/fields/_label.php', compact( array_keys( get_defined_vars() ) ) );

        $output = ob_get_clean();

        return apply_filters( 'pods_form_ui_' . $type, $output, $name, $label, $help, $attributes, $options );
    }

    /**
     * Output a Field Comment Paragraph
     */
    public static function comment ( $name, $message = null, $options = null ) {
        $options = self::options( null, $options );

        $name_more_clean = self::clean( $name, true );

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

        pods_view( PODS_DIR . 'ui/fields/_comment.php', compact( array_keys( get_defined_vars() ) ) );

        $output = ob_get_clean();

        return apply_filters( 'pods_form_ui_' . $type, $output, $name, $message, $attributes, $options );
    }

    /**
     * Output a field
     *
     * @since 2.0.0
     */
    public static function field ( $name, $value, $type = 'text', $options = null, $pod = null, $id = null ) {
        $options = self::options( $type, $options );

        if ( isset( $options[ 'default' ] ) && null === $value )
            $value = $options[ 'default' ];
        $value = apply_filters( 'pods_form_ui_field_' . $type . '_value', $value, $name, $options, $pod, $id );

        ob_start();

        if ( method_exists( get_class(), 'field_' . $type ) )
            call_user_func( array( get_class(), 'field_' . $type ), $name, $value, $options );
        elseif ( is_object( self::$field ) && method_exists( self::$field, 'input' ) )
            self::$field->input( $name, $value, $options, $pod, $id );
        else
            do_action( 'pods_form_ui_field_' . $type, $name, $value, $options, $pod, $id );

        $output = ob_get_clean();

        return apply_filters( 'pods_form_ui_field_' . $type, $output, $name, $value, $options, $pod, $id );
    }

    /**
     * Output field type 'db'
     *
     * Used for field names and other places where only [a-z0-9_] is accepted
     *
     * @since 2.0.0
     */
    protected function field_db ( $name, $value = null, $options = null ) {
        $options = self::options( null, $options );

        pods_view( PODS_DIR . 'ui/fields/_db.php', compact( array_keys( get_defined_vars() ) ) );
    }

    /**
     * Output a hidden field
     */
    protected function field_hidden ( $name, $value = null, $options = null ) {
        $options = self::options( null, $options );

        pods_view( PODS_DIR . 'ui/fields/_hidden.php', compact( array_keys( get_defined_vars() ) ) );
    }

    /**
     * Output a row (label, field, and comment)
     */
    public static function row ( $name, $value, $type = 'text', $options = null, $pod = null, $id = null ) {
        $options = self::options( null, $options );

        pods_view( PODS_DIR . 'ui/fields/_row.php', compact( array_keys( get_defined_vars() ) ) );
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
    public static function merge_attributes ( $attributes, $name = null, $type = null, $options = null ) {
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

            $attributes[ 'class' ] = trim( $attributes[ 'class' ] );
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

        $defaults = self::options_setup( $type, $options );

        $core_defaults = array(
            'label' => '',
            'description' => '',
            'help' => '',
            'default' => null,
            'attributes' => array(),
            'class' => '',
            'grouped' => 0,
        );

        $defaults = array_merge( $core_defaults, $defaults );

        foreach ( $defaults as $option => $settings ) {
            $default = $settings;
            if ( is_array( $settings ) && isset( $settings[ 'default' ] ) )
                $default = $settings[ 'default' ];
            if ( !isset( $options[ $option ] ) )
                $options[ $option ] = $default;
        }

        return $options;
    }

    /*
     * Get options for a field and setup defaults
     *
     * @since 2.0.0
     */
    public static function options_setup ( $type, $options = null ) {
        $core_defaults = array(
            'label' => '',
            'description' => '',
            'help' => '',
            'default' => null,
            'attributes' => array(),
            'class' => '',
            'type' => 'text',
            'group' => 0,
            'grouped' => 0,
            'depends-on' => array(),
            'excludes-on' => array()
        );

        if ( null === $type )
            return $core_defaults;
        else
            self::field_loader( $type );


        if ( !method_exists( self::$field, 'options' ) )
            return $core_defaults;

        $options = (array) self::$field->options();

        foreach ( $options as $option => &$defaults ) {
            if ( !is_array( $defaults ) )
                $defaults = array( 'default' => $defaults );

            if ( isset( $defaults[ 'group' ] ) && is_array( $defaults[ 'group' ] ) )
                foreach ( $defaults[ 'group' ] as &$group_option ) {
                    $group_option = array_merge( $core_defaults, $group_option );
                }

            $defaults = array_merge( $core_defaults, $defaults );
        }

        return $options;
    }

    /**
     * Setup dependency / exclusion classes
     *
     * @param array $options array( 'depends-on' => ..., 'excludes-on' => ...)
     * @param string $prefix
     *
     * @return string
     * @static
     * @since 2.0.0
     */
    public static function dependencies ( $options, $prefix = '' ) {
        $options = (array) $options;

        $depends_on = $excludes_on = array();
        if ( isset( $options[ 'depends-on' ] ) )
            $depends_on = (array) $options[ 'depends-on' ];
        if ( isset( $options[ 'excludes-on' ] ) )
            $excludes_on = (array) $options[ 'excludes-on' ];

        $classes = array();

        if ( !empty( $depends_on ) ) {
            $classes[] = 'pods-depends-on';

            foreach ( $depends_on as $depends => $on ) {
                $classes[] = 'pods-depends-on-' . $prefix . self::clean( $depends, true );

                $on = (array) $on;

                foreach ( $on as $o ) {
                    $classes[] = 'pods-depends-on-' . $prefix . self::clean( $depends, true ) . '-' . self::clean( $o, true );
                }
            }
        }

        if ( !empty( $excludes_on ) ) {
            $classes[] = 'pods-excludes-on';
            foreach ( $excludes_on as $excludes => $on ) {
                $classes[] = 'pods-excludes-on-' . $prefix . self::clean( $excludes, true );

                $on = (array) $on;

                foreach ( $on as $o ) {
                    $classes[] = 'pods-excludes-on-' . $prefix . self::clean( $excludes, true ) . '-' . self::clean( $o, true );
                }
            }
        }

        $classes = implode( ' ', $classes );

        return $classes;
    }

    public static function regex ( $type, $options ) {
        // build and output regex based on options
    }

    /*
     * Clean a value for use in class / id
     *
     * @since 2.0.0
     */
    public static function clean ( $input, $noarray = false, $db_field = false ) {
        $input = str_replace( array( '--1', '__1' ), '00000', (string) $input );
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

    /**
     * Autoload a Field Type's class
     *
     * @param string $field_type Field Type indentifier
     *
     * @return string
     * @access public
     * @static
     * @since 2.0.0
     */
    public static function field_loader ( $field_type ) {
        include_once PODS_DIR . 'classes/PodsField.php';

        $field_type = self::clean( $field_type, true, true );

        $class = ucfirst( $field_type );
        $class = "PodsField_{$class}";

        if ( !class_exists( $class ) ) {
            $file = str_replace( '../', '', apply_filters( 'pods_form_field_include', PODS_DIR . 'classes/fields/' . basename( $field_type ) . '.php', $field_type ) );

            if ( 0 === strpos( $file, ABSPATH ) && file_exists( $file ) )
                include_once $file;
        }

        if ( class_exists( $class ) )
            $class = new $class();
        else
            $class = self::field_loader( 'text' ); // load basic text field

        self::$field = $class;
        self::$field_type = $class::$type;

        return $class;
    }
}
