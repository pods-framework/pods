<?php
/**
 * @package Pods
 */
class PodsForm {

    /**
     * @var null
     */
    static $field = null;

    /**
     * @var null
     */
    static $field_group = null;

    /**
     * @var null
     */
    static $field_type = null;

    /**
     * @var array
     */
    static $loaded = array();

    /**
     * @var int
     */
    static $form_counter = 0;

    /**
     * Generate UI for a Form and it's Fields
     *
     * @license http://www.gnu.org/licenses/gpl-2.0.html
     * @since 2.0.0
     */
    public function __construct () {

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
        $attributes[ 'for' ] = ( false === strpos( $name_clean, 'pods-form-ui-' ) ? 'pods-form-ui-' : '' ) . $name_clean;
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

        if ( null === $value || ( !empty( $pod ) && empty( $id ) ) )
            $value = self::default_value( $value, $type, $name, $options, $pod, $id );

        if ( false === PodsForm::permission( $type, $name, $options, null, $pod, $id ) )
            return false;

        $value = apply_filters( 'pods_form_ui_field_' . $type . '_value', $value, $name, $options, $pod, $id );

        ob_start();

        $helper = false;

        if ( 0 < strlen( pods_var_raw( 'input_helper', $options ) ) )
            $helper = pods_api()->load_helper( array( 'name' => $options[ 'input_helper' ] ) );

        if ( is_object( self::$loaded[ $type ] ) && method_exists( self::$loaded[ $type ], 'data' ) )
            $data = $options[ 'data' ] = self::$loaded[ $type ]->data( $name, $value, $options, $pod, $id );

        if ( !empty( $helper ) && 0 < strlen( pods_var_raw( 'code', $helper ) ) && ( !defined( 'PODS_DISABLE_EVAL' ) || !PODS_DISABLE_EVAL ) )
            eval( '?>' . $helper[ 'code' ] );
        elseif ( method_exists( get_class(), 'field_' . $type ) )
            echo call_user_func( array( get_class(), 'field_' . $type ), $name, $value, $options );
        elseif ( is_object( self::$loaded[ $type ] ) && method_exists( self::$loaded[ $type ], 'input' ) )
            self::$loaded[ $type ]->input( $name, $value, $options, $pod, $id );
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
        ob_start();

        pods_view( PODS_DIR . 'ui/fields/_db.php', compact( array_keys( get_defined_vars() ) ) );

        $output = ob_get_clean();

        return apply_filters( 'pods_form_ui_field_db', $output, $name, $value, $options );
    }

    /**
     * Output a hidden field
     */
    protected function field_hidden ( $name, $value = null, $options = null ) {
        ob_start();

        pods_view( PODS_DIR . 'ui/fields/_hidden.php', compact( array_keys( get_defined_vars() ) ) );

        $output = ob_get_clean();

        return apply_filters( 'pods_form_ui_field_hidden', $output, $name, $value, $options );
    }

    /**
     * Output a row (label, field, and comment)
     */
    public static function row ( $name, $value, $type = 'text', $options = null, $pod = null, $id = null ) {
        $options = self::options( null, $options );

        ob_start();

        pods_view( PODS_DIR . 'ui/fields/_row.php', compact( array_keys( get_defined_vars() ) ) );

        $output = ob_get_clean();

        return apply_filters( 'pods_form_ui_field_row', $output, $name, $value, $options, $pod, $id );
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
     * Output a field's data (for use with jQuery)
     *
     * @since 2.0.0
     */
    public static function data ( $data, $name = null, $type = null, $options = null ) {
        $data = (array) apply_filters( 'pods_form_ui_field_' . $type . '_data', $data, $name, $options );

        foreach ( $data as $key => $value ) {
            if ( null === $value )
                continue;

            $key = sanitize_title( $key );

            if ( is_array( $value ) )
                $value = implode( ',', $value );

            echo ' data-' . esc_attr( (string) $key ) . '="' . esc_attr( (string) $value ) . '"';
        }
    }

    /**
     * Merge attributes and handle classes
     *
     * @since 2.0.0
     */
    public static function merge_attributes ( $attributes, $name = null, $type = null, $options = null, $classes = '' ) {
        $options = (array) $options;

        if ( !in_array( $type, array( 'label', 'comment' ) ) ) {
            $name_clean = self::clean( $name );
            $name_more_clean = self::clean( $name, true );
            $_attributes = array();
            $_attributes[ 'name' ] = $name;
            $_attributes[ 'data-name-clean' ] = $name_more_clean;

            if ( 0 < strlen( pods_var_raw( 'label', $options, '' ) ) )
                $_attributes[ 'data-label' ] = strip_tags( pods_var_raw( 'label', $options ) );

            $_attributes[ 'id' ] = 'pods-form-ui-' . $name_clean;
            $_attributes[ 'class' ] = 'pods-form-ui-field-type-' . $type . ' pods-form-ui-field-name-' . $name_more_clean;

            if ( isset( $options[ 'dependency' ] ) && false !== $options[ 'dependency' ] )
                $_attributes[ 'class' ] .= ' pods-dependent-toggle';

            $attributes = array_merge( $_attributes, (array) $attributes );
        }

        if ( isset( $options[ 'attributes' ] ) && is_array( $options[ 'attributes' ] ) && !empty( $options[ 'attributes' ] ) )
            $attributes = array_merge( $attributes, $options[ 'attributes' ] );

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

        if ( !empty( $classes ) ) {
            if ( isset( $attributes[ 'class' ] ) )
                $attributes[ 'class' ] = $attributes[ 'class' ] . ' ' . $classes;
            else
                $attributes[ 'class' ] = $classes;
        }

        if ( 1 == pods_var( 'required', $options, 0 ) )
            $attributes[ 'class' ] .= ' pods-validate pods-validate-required';

        if ( isset( $options[ 'maxlength' ] ) && !empty( $options[ 'maxlength' ] ) )
            $attributes[ 'maxlength' ] = (int) $options[ 'maxlength' ];
        elseif ( isset( $options[ $type . '_max_length' ] ) && !empty( $options[ $type . '_max_length' ] ) )
            $attributes[ 'maxlength' ] = (int) $options[ $type . '_max_length' ];

        $attributes = (array) apply_filters( 'pods_form_ui_field_' . $type . '_merge_attributes', $attributes, $name, $options );
        return $attributes;
    }

    /*
     * Setup options for a field and store them for later use
     *
     * @since 2.0.0
     */
    /**
     * @static
     *
     * @param $type
     * @param $options
     *
     * @return array
     */
    public static function options ( $type, $options ) {
        $options = (array) $options;

        if ( isset( $options[ 'options' ] ) ) {
            $options_temp = $options[ 'options' ];

            unset( $options[ 'options' ] );

            $options = array_merge( $options_temp, $options );

            $override = array(
                'class'
            );

            foreach ( $override as $check ) {
                if ( isset( $options_temp[ $check ] ) )
                    $options[ $check ] = $options_temp[ $check ];
            }
        }

        $defaults = self::options_setup( $type, $options );

        $core_defaults = array(
            'id' => 0,
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
     * Get options for a field type and setup defaults
     *
     * @since 2.0.0
     */
    /**
     * @static
     *
     * @param $type
     *
     * @return array|null
     */
    public static function options_setup ( $type ) {
        $core_defaults = array(
            'id' => 0,
            'name' => '',
            'label' => '',
            'description' => '',
            'help' => '',
            'default' => null,
            'attributes' => array(),
            'class' => '',
            'type' => 'text',
            'group' => 0,
            'grouped' => 0,
            'dependency' => false,
            'depends-on' => array(),
            'excludes-on' => array(),
            'options' => array()
        );

        if ( null === $type )
            return $core_defaults;
        else
            self::field_loader( $type );

        $options = apply_filters( 'pods_field_' . $type . '_options', (array) self::$loaded[ $type ]->options(), $type, $core_defaults );

        return self::fields_setup( $options, $core_defaults );
    }

    /*
     * Get options for a field and setup defaults
     *
     * @since 2.0.0
     */
    /**
     * @static
     *
     * @param null $fields
     * @param null $core_defaults
     * @param bool $single
     *
     * @return array|null
     */
    public static function fields_setup ( $fields = null, $core_defaults = null, $single = false ) {
        if ( empty( $core_defaults ) ) {
            $core_defaults = array(
                'id' => 0,
                'name' => '',
                'label' => '',
                'description' => '',
                'help' => '',
                'default' => null,
                'attributes' => array(),
                'class' => '',
                'type' => 'text',
                'group' => 0,
                'grouped' => 0,
                'dependency' => false,
                'depends-on' => array(),
                'excludes-on' => array(),
                'options' => array()
            );
        }

        if ( $single )
            $fields = array( $fields );

        foreach ( $fields as $f => $field ) {
            $fields[ $f ] = self::field_setup( $field, $core_defaults, pods_var( 'type', $field, 'text' ) );
        }

        if ( $single )
            $fields = $fields[ 0 ];

        return $fields;
    }

    /*
     * Get options for a field and setup defaults
     *
     * @since 2.0.0
     */
    /**
     * @static
     *
     * @param null $field
     * @param null $core_defaults
     * @param null $type
     *
     * @return array|null
     */
    public static function field_setup ( $field = null, $core_defaults = null, $type = null ) {
        $options = array();

        if ( empty( $core_defaults ) ) {
            $core_defaults = array(
                'id' => 0,
                'name' => '',
                'label' => '',
                'description' => '',
                'help' => '',
                'default' => null,
                'attributes' => array(),
                'class' => '',
                'type' => 'text',
                'group' => 0,
                'grouped' => 0,
                'dependency' => false,
                'depends-on' => array(),
                'excludes-on' => array(),
                'options' => array()
            );

            if ( null !== $type ) {
                self::field_loader( $type );

                if ( method_exists( self::$loaded[ $type ], 'options' ) )
                    $options = apply_filters( 'pods_field_' . $type . '_options', (array) self::$loaded[ $type ]->options(), $type );
            }
        }

        if ( !is_array( $field ) )
            $field = array( 'default' => $field );

        if ( isset( $field[ 'group' ] ) && is_array( $field[ 'group' ] ) ) {
            foreach ( $field[ 'group' ] as $g => $group_option ) {
                $field[ 'group' ][ $g ] = array_merge( $core_defaults, $group_option );
            }
        }

        $field = array_merge( $core_defaults, $field );

        foreach ( $options as $option => $settings ) {
            $v = null;

            if ( isset( $settings[ 'default' ] ) )
                $v = $settings[ 'default' ];

            if ( !isset( $field[ 'options' ][ $option ] ) )
                $field[ 'options' ][ $option ] = $v;
        }

        return $field;
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

                if ( !is_bool( $on ) ) {
                    $on = (array) $on;

                    foreach ( $on as $o ) {
                        $classes[] = 'pods-depends-on-' . $prefix . self::clean( $depends, true ) . '-' . self::clean( $o, true );
                    }
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

    /**
     * Change the way the value of the field is displayed with Pods::get
     *
     * @param mixed $value
     * @param string $name
     * @param array $options
     * @param array $fields
     * @param array $pod
     * @param int $id
     * @param array $traverse
     *
     * @since 2.0.0
     */
    public static function display ( $type, $value = null, $name = null, $options = null, $pod = null, $id = null, $traverse = null ) {
        self::field_loader( $type );

        $tableless_field_types = apply_filters( 'pods_tableless_field_types', array( 'pick', 'file', 'avatar' ) );

        if ( method_exists( self::$loaded[ $type ], 'display' ) ) {
            if ( is_array( $value ) && in_array( $type, $tableless_field_types ) ) {
                foreach ( $value as &$display_value ) {
                    $display_value = call_user_func_array( array( self::$loaded[ $type ], 'display' ), array( $display_value, $name, $options, $pod, $id, $traverse ) );
                }
            }
            else
                $value = call_user_func_array( array( self::$loaded[ $type ], 'display' ), array( $value, $name, $options, $pod, $id, $traverse ) );
        }

        return $value;
    }

    /**
     * Setup regex for JS / PHP
     *
     * @static
     *
     * @param $type
     * @param $options
     *
     * @return mixed|void
     * @since 2.0.0
     */
    public static function regex ( $type, $options ) {
        self::field_loader( $type );

        $regex = false;

        if ( method_exists( self::$loaded[ $type ], 'regex' ) )
            $regex = self::$loaded[ $type ]->regex( $options );

        $regex = apply_filters( 'pods_field_' . $type . '_regex', $regex, $options, $type );

        return $regex;
    }

    /**
     * Setup value preparation for sprintf
     *
     * @static
     *
     * @param $type
     * @param $options
     *
     * @return mixed|void
     * @since 2.0.0
     */
    public static function prepare ( $type, $options ) {
        self::field_loader( $type );

        $prepare = '%s';

        if ( method_exists( self::$loaded[ $type ], 'prepare' ) )
            $prepare = self::$loaded[ $type ]->prepare( $options );

        $prepare = apply_filters( 'pods_field_' . $type . '_prepare', $prepare, $options, $type );

        return $prepare;
    }

    /**
     * Validate a value before it's saved
     *
     * @param string $type
     * @param mixed $value
     * @param string $name
     * @param array $options
     * @param array $fields
     * @param array $pod
     * @param int $id
     * @param array|object $params
     *
     * @static
     *
     * @since 2.0.0
     */
    public static function validate ( $type, &$value, $name = null, $options = null, $fields = null, $pod = null, $id = null, $params = null ) {
        self::field_loader( $type );

        $validate = true;

        if ( method_exists( self::$loaded[ $type ], 'validate' ) )
            $validate = self::$loaded[ $type ]->validate( $value, $name, $options, $fields, $pod, $id, $params );

        $validate = apply_filters( 'pods_field_' . $type . '_validate', $validate, $value, $name, $options, $fields, $pod, $id, $type, $params );

        return $validate;
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
     * @static
     *
     * @since 2.0.0
     */
    public static function pre_save ( $type, $value, $id = null, $name = null, $options = null, $fields = null, $pod = null, $params = null ) {
        self::field_loader( $type );

        if ( method_exists( self::$loaded[ $type ], 'pre_save' ) )
            $value = self::$loaded[ $type ]->pre_save( $value, $id, $name, $options, $fields, $pod, $params );

        return $value;
    }

    /**
     * Check if a user has permission to be editing a field
     *
     * @param $type
     * @param null $name
     * @param null $options
     * @param null $fields
     * @param null $pod
     * @param null $id
     * @param null $params
     *
     * @static
     *
     * @since 2.0.0
     */
    public static function permission ( $type, $name = null, $options = null, $fields = null, $pod = null, $id = null, $params = null ) {
        $permission = pods_permission( $options );

        $permission = (boolean) apply_filters( 'pods_form_field_permission', $permission, $type, $name, $options, $fields, $pod, $id, $params );

        return $permission;
    }

    /**
     * Parse the default the value
     *
     * @since 2.0.0
     */
    public static function default_value ( $value, $type = 'text', $name = null, $options = null, $pod = null, $id = null ) {
        $default_value = pods_var_raw( 'default_value', $options, $value, null, true );
        $default = pods_var_raw( 'default', $options, $default_value, null, true );

        $default_value = str_replace( array( '{@', '}' ), '', trim( $default ) );

        if ( $default != $default_value )
            $default = pods_evaluate_tags( $default );

        $default = pods_var_raw( pods_var_raw( 'default_value_parameter', $options ), 'request', $default, null, true );

        if ( $default != $value )
            $value = $default;

        if ( is_array( $value ) )
            $value = pods_serial_comma( $value );

        return apply_filters( 'pods_form_field_default_value', $value, $default, $type, $options, $pod, $id );
    }

    /**
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
        if ( isset( self::$loaded[ $field_type ] ) ) {
            $class_vars = get_class_vars( get_class( self::$loaded[ $field_type ] ) ); // PHP 5.2.x workaround

            self::$field_group = ( isset( $class_vars[ 'group' ] ) ? $class_vars[ 'group' ] : '' );
            self::$field_type = $class_vars[ 'type' ];

            return self::$loaded[ $field_type ];
        }

        include_once PODS_DIR . 'classes/PodsField.php';

        $field_type = self::clean( $field_type, true, true );

        $class_name = ucfirst( $field_type );
        $class_name = "PodsField_{$class_name}";

        if ( !class_exists( $class_name ) ) {
            $file = str_replace( '../', '', apply_filters( 'pods_form_field_include', PODS_DIR . 'classes/fields/' . basename( $field_type ) . '.php', $field_type ) );

            if ( 0 < strlen( untrailingslashit( WP_CONTENT_DIR ) ) && 0 === strpos( $file, untrailingslashit( WP_CONTENT_DIR ) ) && file_exists( $file ) )
                include_once $file;

            if ( 0 < strlen( untrailingslashit( ABSPATH ) ) && 0 === strpos( $file, untrailingslashit( ABSPATH ) ) && file_exists( $file ) )
                include_once $file;
        }

        if ( class_exists( $class_name ) )
            $class = new $class_name();
        else {
            $class = new PodsField();
            $class_name = 'PodsField';
        }

        $class_vars = get_class_vars( $class_name ); // PHP 5.2.x workaround

        self::$field_group = ( isset( $class_vars[ 'group' ] ) ? $class_vars[ 'group' ] : '' );
        self::$field_type = $class_vars[ 'type' ];

        self::$loaded[ $field_type ] =& $class;

        return self::$loaded[ $field_type ];
    }

    /**
     * Run a method from a Field Type's class
     *
     * @param string $field_type Field Type indentifier
     * @param string $method Method name
     * @param mixed $arg More arguments
     *
     * @return mixed
     * @access public
     * @static
     * @since 2.0.0
     */
    public static function field_method () {
        $args = func_get_args();

        if ( empty( $args ) && count( $args ) < 2 )
            return false;

        $field_type = array_shift( $args );
        $method = array_shift( $args );

        $class = self::field_loader( $field_type );

        if ( method_exists( $class, $method ) )
            return call_user_func_array( array( $class, $method ), $args );

        return false;
    }
}
