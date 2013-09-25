<?php
/**
 * @package Pods
 */
class PodsForm {

    /**
     * @var PodsForm
     */
    protected static $instance = null;

    /**
     * @var string
     */
    static $field = null;

    /**
     * @var string
     */
    static $field_group = null;

    /**
     * @var string
     */
    static $field_type = null;

    /**
     * @var array
     */
    static $field_types = array();

    /**
     * @var array
     */
    static $loaded = array();

    /**
     * @var int
     */
    static $form_counter = 0;

    /**
     * Singleton handling for a basic pods_form() request
     *
     * @return \PodsForm
     *
     * @since 2.3.5
     */
    public static function init () {
        if ( !is_object( self::$instance ) )
            self::$instance = new PodsForm();

        return self::$instance;
    }

    /**
     * Master handler for all field / form methods
     *
     * @return \PodsForm
     *
     * @license http://www.gnu.org/licenses/gpl-2.0.html
     * @since 2.0
     */
    private function __construct () {
        add_action( 'admin_init', array( $this, 'admin_init' ), 14 );
    }

    /**
     * Prevent clones
     *
     * @since 2.3
     */
    private function __clone () {
        // Hulk smash
    }

    /**
     * Output a field's label
     *
     * @since 2.0
     */

    /**
     * Output a field's label
     *
     * @param string $name Field name
     * @param string $label Label text
     * @param string $help Help text
     * @param array $options Field options
     *
     * @return string Label HTML
     *
     * @since 2.0
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
     *
     * @param string $name Field name
     * @param string $message Field comments
     * @param array $options Field options
     *
     * @return string Comment HTML
     *
     * @since 2.0
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
     * @param string $name Field name
     * @param mixed $value Field value
     * @param string $type Field type
     * @param array $options Field options
     * @param array $pod Pod data
     * @param int $id Item ID
     *
     * @return string Field HTML
     *
     * @since 2.0
     */
    public static function field ( $name, $value, $type = 'text', $options = null, $pod = null, $id = null ) {
		// Take a field array
		if ( is_array( $name ) ) {
			$options = $name;

			if ( is_object( $type ) ) {
				$pod = $type;
				$id = $options;
			}

			$name = pods_var_raw( 'name', $options );
			$type = pods_var_raw( 'type', $options );
		}

        $options = self::options( $type, $options );

        if ( null === $value || ( '' === $value && 'boolean' == $type ) || ( !empty( $pod ) && empty( $id ) ) )
            $value = self::default_value( $value, $type, $name, $options, $pod, $id );

        if ( false === self::permission( $type, $name, $options, null, $pod, $id ) )
            return false;

        $value = apply_filters( 'pods_form_ui_field_' . $type . '_value', $value, $name, $options, $pod, $id );
        $form_field_type = self::$field_type;

        ob_start();

        $helper = false;

        if ( 0 < strlen( pods_var_raw( 'input_helper', $options ) ) )
            $helper = pods_api()->load_helper( array( 'name' => $options[ 'input_helper' ] ) );

        if ( ( !isset( $options[ 'data' ] ) || empty( $options[ 'data' ] ) ) && is_object( self::$loaded[ $type ] ) && method_exists( self::$loaded[ $type ], 'data' ) )
            $data = $options[ 'data' ] = self::$loaded[ $type ]->data( $name, $value, $options, $pod, $id, true );

        if ( true === apply_filters( 'pods_form_ui_field_' . $type . '_override', false, $name, $value, $options, $pod, $id ) )
            do_action( 'pods_form_ui_field_' . $type, $name, $value, $options, $pod, $id );
        elseif ( !empty( $helper ) && 0 < strlen( pods_var_raw( 'code', $helper ) ) && false === strpos( $helper[ 'code' ], '$this->' ) && ( !defined( 'PODS_DISABLE_EVAL' ) || !PODS_DISABLE_EVAL ) )
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
     * @since 2.0
     */
    protected static function field_db ( $name, $value = null, $options = null ) {
        $form_field_type = self::$field_type;

        ob_start();

        pods_view( PODS_DIR . 'ui/fields/_db.php', compact( array_keys( get_defined_vars() ) ) );

        $output = ob_get_clean();

        return apply_filters( 'pods_form_ui_field_db', $output, $name, $value, $options );
    }

    /**
     * Output a hidden field
     */
    protected static function field_hidden ( $name, $value = null, $options = null ) {
        $form_field_type = self::$field_type;

        ob_start();

        pods_view( PODS_DIR . 'ui/fields/_hidden.php', compact( array_keys( get_defined_vars() ) ) );

        $output = ob_get_clean();

        return apply_filters( 'pods_form_ui_field_hidden', $output, $name, $value, $options );
    }

    /**
     * Output a row (label, field, and comment)
     *
     * @param string $name Field name
     * @param mixed $value Field value
     * @param string $type Field type
     * @param array $options Field options
     * @param array $pod Pod data
     * @param int $id Item ID
     *
     * @return string Row HTML
     *
     * @since 2.0
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
     * @since 2.0
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
     * @since 2.0
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
     * @since 2.0
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

            if ( isset( $options[ 'attributes' ] ) && is_array( $options[ 'attributes' ] ) && !empty( $options[ 'attributes' ] ) )
                $attributes = array_merge( $attributes, $options[ 'attributes' ] );
        }
        elseif ( isset( $options[ $type . '_attributes' ] ) && is_array( $options[ $type . '_attributes' ] ) && !empty( $options[ $type . '_attributes' ] ) )
            $attributes = array_merge( $attributes, $options[ $type . '_attributes' ] );

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

        $max_length = (int) pods_var( 'maxlength', $options, pods_var( $type . '_max_length', $options, 0 ), null, true );

        if ( 0 < $max_length )
            $attributes[ 'maxlength' ] = $max_length;

        $attributes = (array) apply_filters( 'pods_form_ui_field_' . $type . '_merge_attributes', $attributes, $name, $options );
        return $attributes;
    }

    /**
     * Setup options for a field and store them for later use
     *
     * @param $type
     * @param $options
     *
     * @return array
     *
     * @static
     *
     * @since 2.0
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

    /**
     * Get options for a field type and setup defaults
     *
     * @static
     *
     * @param $type
     *
     * @return array|null
     *
     * @since 2.0
     */
    public static function options_setup ( $type = null, $options = null ) {
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
            'developer_mode' => false,
            'dependency' => false,
            'depends-on' => array(),
            'excludes-on' => array(),
            'options' => array()
        );

        if ( !empty( $options ) && is_array( $options ) )
            $core_defaults = array_merge( $core_defaults, $options );

        if ( null === $type )
            return $core_defaults;
        else
            self::field_loader( $type );

        $options = apply_filters( 'pods_field_' . $type . '_options', (array) self::$loaded[ $type ]->options(), $type );

        $first_field = current( $options );

        if ( !empty( $options ) && !isset( $first_field[ 'name' ] ) && !isset( $first_field[ 'label' ] ) ) {
            $all_options = array();

            foreach ( $options as $group => $group_options ) {
                $all_options = array_merge( $all_options, self::fields_setup( $group_options, $core_defaults ) );
            }

            $options = $all_options;
        }
        else
            $options = self::fields_setup( $options, $core_defaults );

        return $options;
    }

    /**
     * Get Admin options for a field type and setup defaults
     *
     * @static
     *
     * @param $type
     *
     * @return array|null
     *
     * @since 2.0
     */
    public static function ui_options ( $type ) {
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
            'developer_mode' => false,
            'dependency' => false,
            'depends-on' => array(),
            'excludes-on' => array(),
            'options' => array()
        );

        self::field_loader( $type );

        $options = apply_filters( 'pods_field_' . $type . '_ui_options', (array) self::$loaded[ $type ]->ui_options(), $type );

        $first_field = current( $options );

        if ( !empty( $options ) && !isset( $first_field[ 'name' ] ) && !isset( $first_field[ 'label' ] ) ) {
            foreach ( $options as $group => $group_options ) {
                $options[ $group ] = self::fields_setup( $group_options, $core_defaults );
            }
        }
        else
            $options = self::fields_setup( $options, $core_defaults );

        return $options;
    }

    /**
     * Get options for a field and setup defaults
     *
     *
     * @param null $fields
     * @param null $core_defaults
     * @param bool $single
     *
     * @return array|null
     *
     * @static
     * @since 2.0
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
                'developer_mode' => false,
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

            if ( !$single && strlen( $fields[ $f ][ 'name' ] ) < 1 )
                $fields[ $f ][ 'name' ] = $f;
        }

        if ( $single )
            $fields = $fields[ 0 ];

        return $fields;
    }

    /**
     * Get options for a field and setup defaults
     *
     * @static
     *
     * @param null $field
     * @param null $core_defaults
     * @param null $type
     *
     * @return array|null
     *
     * @since 2.0
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
                'developer_mode' => false,
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

                if ( strlen( $field[ 'group' ][ $g ][ 'name' ] ) < 1 )
                    $field[ 'group' ][ $g ][ 'name' ] = $g;
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
     * @since 2.0
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
     * Change the value of the field
     *
     * @param mixed $value
     * @param string $name
     * @param array $options
     * @param array $fields
     * @param array $pod
     * @param int $id
     * @param array $traverse
     *
     * @since 2.3
     */
    public static function value ( $type, $value = null, $name = null, $options = null, $pod = null, $id = null, $traverse = null ) {
        self::field_loader( $type );

        $tableless_field_types = self::tableless_field_types();
        $repeatable_field_types = self::repeatable_field_types();

        if ( in_array( $type, $repeatable_field_types ) && 1 == pods_var( $type . '_repeatable', $options, 0 ) && !is_array( $value ) ) {
            if ( 0 < strlen( $value ) ) {
                $simple = @json_decode( $value, true );

                if ( is_array( $simple ) )
                    $value = $simple;
                else
                    $value = (array) $value;
            }
            else
                $value = array();
        }

        if ( method_exists( self::$loaded[ $type ], 'value' ) ) {
            if ( is_array( $value ) && in_array( $type, $tableless_field_types ) ) {
                foreach ( $value as &$display_value ) {
                    $display_value = call_user_func_array( array( self::$loaded[ $type ], 'value' ), array( $display_value, $name, $options, $pod, $id, $traverse ) );
                }
            }
            else
                $value = call_user_func_array( array( self::$loaded[ $type ], 'value' ), array( $value, $name, $options, $pod, $id, $traverse ) );
        }

        return $value;
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
     * @since 2.0
     */
    public static function display ( $type, $value = null, $name = null, $options = null, $pod = null, $id = null, $traverse = null ) {
        self::field_loader( $type );

        $tableless_field_types = self::tableless_field_types();

        if ( method_exists( self::$loaded[ $type ], 'display' ) ) {
            if ( is_array( $value ) && !in_array( $type, $tableless_field_types ) ) {
                foreach ( $value as $k => $display_value ) {
                    $value[ $k ] = call_user_func_array( array( self::$loaded[ $type ], 'display' ), array( $display_value, $name, $options, $pod, $id, $traverse ) );
                }
            }
            else
                $value = call_user_func_array( array( self::$loaded[ $type ], 'display' ), array( $value, $name, $options, $pod, $id, $traverse ) );
        }

        $value = apply_filters( 'pods_form_display_' . $type, $value, $name, $options, $pod, $id, $traverse );

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
     * @since 2.0
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
     * @since 2.0
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
     * @since 2.0
     */
    public static function validate ( $type, $value, $name = null, $options = null, $fields = null, $pod = null, $id = null, $params = null ) {
        self::field_loader( $type );

        $validate = true;

        if ( 1 == pods_var( 'pre_save', $options, 1 ) && method_exists( self::$loaded[ $type ], 'validate' ) )
            $validate = self::$loaded[ $type ]->validate( $value, $name, $options, $fields, $pod, $id, $params );

        $validate = apply_filters( 'pods_field_' . $type . '_validate', $validate, $value, $name, $options, $fields, $pod, $id, $type, $params );

        return $validate;
    }

    /**
     * Change the value or perform actions after validation but before saving to the DB
     *
     * @param string $type
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
     * @since 2.0
     */
    public static function pre_save ( $type, $value, $id = null, $name = null, $options = null, $fields = null, $pod = null, $params = null ) {
        self::field_loader( $type );

        if ( 1 == pods_var( 'field_pre_save', $options, 1 ) && method_exists( self::$loaded[ $type ], 'pre_save' ) )
            $value = self::$loaded[ $type ]->pre_save( $value, $id, $name, $options, $fields, $pod, $params );

        return $value;
    }

    /**
     * Save the value to the DB
     *
     * @param string $type
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
     * @since 2.3
     */
    public static function save ( $type, $value, $id = null, $name = null, $options = null, $fields = null, $pod = null, $params = null ) {
        self::field_loader( $type );

        $saved = null;

        if ( 1 == pods_var( 'field_save', $options, 1 ) && method_exists( self::$loaded[ $type ], 'save' ) )
            $saved = self::$loaded[ $type ]->save( $value, $id, $name, $options, $fields, $pod, $params );

        return $saved;
    }

    /**
     * Delete the value from the DB
     *
     * @param string $type
     * @param int $id
     * @param string $name
     * @param array $options
     * @param array $pod
     *
     * @static
     *
     * @since 2.3
     */
    public static function delete ( $type, $id = null, $name = null, $options = null, $pod = null ) {
        self::field_loader( $type );

        $deleted = null;

        if ( 1 == pods_var( 'field_delete', $options, 1 ) && method_exists( self::$loaded[ $type ], 'delete' ) )
            $deleted = self::$loaded[ $type ]->delete( $id, $name, $options, $pod );

        return $deleted;
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
     * @since 2.0
     */
    public static function permission ( $type, $name = null, $options = null, $fields = null, $pod = null, $id = null, $params = null ) {
        $permission = pods_permission( $options );

        $permission = (boolean) apply_filters( 'pods_form_field_permission', $permission, $type, $name, $options, $fields, $pod, $id, $params );

        return $permission;
    }

    /**
     * Parse the default the value
     *
     * @since 2.0
     */
    public static function default_value ( $value, $type = 'text', $name = null, $options = null, $pod = null, $id = null ) {
        $default_value = pods_var_raw( 'default_value', $options, $value, null, true );
        $default = pods_var_raw( 'default', $options, $default_value, null, true );

        $default_value = str_replace( array( '{@', '}' ), '', trim( $default ) );

        if ( $default != $default_value && 1 == (int) pods_var_raw( 'default_evaluate_tags', $options, 1 ) )
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
     * @since 2.0
     */
    public static function clean ( $input, $noarray = false, $db_field = false ) {
        $input = str_replace( array( '--1', '__1' ), '00000', (string) $input );
        if ( false !== $noarray )
            $input = preg_replace( '/\[\d*\]/', '-', $input );
        $output = str_replace( array( '[', ']' ), '-', strtolower( $input ) );
        $output = preg_replace( '/([^a-z0-9\-_])/', '', $output );
        $output = trim( str_replace( array( '__', '_', '--' ), '-', $output ), '-' );
        $output = str_replace( '00000', '--1', $output );
        if ( false !== $db_field )
            $output = str_replace( '-', '_', $output );
        return $output;
    }

    /**
     * Run admin_init methods for each field type
     *
     * @since 2.3
     */
    public function admin_init () {
        $admin_field_types = pods_transient_get( 'pods_form_admin_init_field_types' );

        if ( empty( $admin_field_types ) ) {
            $admin_field_types = array();

            $field_types = self::field_types();

            foreach ( $field_types as $field_type => $field_type_data ) {
                $has_ajax = self::field_method( $field_type_data[ 'type' ], 'admin_init' );

                if ( false !== $has_ajax )
                    $admin_field_types[] = $field_type;
            }

            pods_transient_set( 'pods_form_admin_init_field_types', $admin_field_types );
        }
        else {
            foreach ( $admin_field_types as $field_type ) {
                self::field_method( $field_type, 'admin_init' );
            }
        }
    }

    /**
     * Autoload a Field Type's class
     *
     * @param string $field_type Field Type indentifier
     * @param string $file The Field Type class file location
     *
     * @return string
     * @access public
     * @static
     * @since 2.0
     */
    public static function field_loader ( $field_type, $file = '' ) {
        if ( isset( self::$loaded[ $field_type ] ) ) {
            $class_vars = get_class_vars( get_class( self::$loaded[ $field_type ] ) ); // PHP 5.2.x workaround

            self::$field_group = ( isset( $class_vars[ 'group' ] ) ? $class_vars[ 'group' ] : '' );
            self::$field_type = $class_vars[ 'type' ];

            if ( 'Unknown' != $class_vars[ 'label' ] )
                return self::$loaded[ $field_type ];
        }

        include_once PODS_DIR . 'classes/PodsField.php';

        $field_type = self::clean( $field_type, true, true );

        $class_name = ucfirst( $field_type );
        $class_name = "PodsField_{$class_name}";

        $content_dir = realpath( WP_CONTENT_DIR );
        $plugins_dir = realpath( WP_PLUGIN_DIR );
        $muplugins_dir = realpath( WPMU_PLUGIN_DIR );
        $abspath_dir = realpath( ABSPATH );

        if ( !class_exists( $class_name ) ) {
            if ( isset( self::$field_types[ $field_type ] ) && !empty( self::$field_types[ $field_type ][ 'file' ] ) )
                $file = realpath( self::$field_types[ $field_type ][ 'file' ] );

            if ( !empty( $file ) && 0 === strpos( $file, $abspath_dir ) && file_exists( $file ) )
                include_once $file;
            else {
                $file = str_replace( '../', '', apply_filters( 'pods_form_field_include', PODS_DIR . 'classes/fields/' . basename( $field_type ) . '.php', $field_type ) );
                $file = realpath( $file );

                if ( file_exists( $file ) && ( 0 === strpos( $file, $content_dir ) || 0 === strpos( $file, $plugins_dir ) || 0 === strpos( $file, $muplugins_dir ) || 0 === strpos( $file, $abspath_dir ) ) )
                    include_once $file;
            }
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
     * @since 2.0
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

    /**
     * Add a new Pod field type
     *
     * @param string $type The new field type identifier
     * @param string $file The new field type class file location
     *
     * @return array Field Type data
     *
     * @since 2.3
     */
    public static function register_field_type ( $type, $file = null ) {
        $field_type = pods_transient_get( 'pods_field_type_' . $type );

        if ( empty( $field_type ) || $field_type[ 'type' ] != $type || $field_type[ 'file' ] != $file ) {
            self::field_loader( $type, $file );

            $class_vars = get_class_vars( get_class( self::$loaded[ $type ] ) ); // PHP 5.2.x workaround

            self::$field_types[ $type ] = $class_vars;
            self::$field_types[ $type ][ 'file' ] = $file;

            pods_transient_set( 'pods_field_type_' . $type, self::$field_types[ $type ] );
        }
        else
            self::$field_types[ $type ] = $field_type;

        return self::$field_types[ $type ];
    }

    /**
     * Get a list of all available field types and include
     *
     * @return array Registered Field Types data
     *
     * @since 2.3
     */
    public static function field_types () {
        $field_types = array(
            'text',
            'website',
            'phone',
            'email',
            'password',
            'paragraph',
            'wysiwyg',
            'code',
            'datetime',
            'date',
            'time',
            'number',
            'currency',
            'file',
            'avatar',
            'pick',
            'boolean',
            'color',
            'slug'
        );

		if ( pods_developer() ) {
			$field_types[] = 'html';
		}

        $field_types = array_merge( $field_types, array_keys( self::$field_types ) );

        $field_types = array_filter( array_unique( $field_types ) );

        $types = apply_filters( 'pods_api_field_types', $field_types );

        $field_types = pods_transient_get( 'pods_field_types' );

        if ( empty( $field_types ) || count( $types ) != count( $field_types ) ) {
            $field_types = array();

            foreach ( $types as $field_type ) {
                $file = null;

                if ( isset( self::$field_types[ $field_type ] ) )
                    $file = self::$field_types[ $field_type ][ 'file' ];

                self::field_loader( $field_type, $file );

                if ( !isset( self::$loaded[ $field_type ] ) || !is_object( self::$loaded[ $field_type ] ) )
                    continue;

                $class_vars = get_class_vars( get_class( self::$loaded[ $field_type ] ) ); // PHP 5.2.x workaround

                $field_types[ $field_type ] = $class_vars;
                $field_types[ $field_type ][ 'file' ] = $file;
            }

            self::$field_types = $field_types;

            pods_transient_set( 'pods_field_types', self::$field_types );
        }
        else
            self::$field_types = array_merge( $field_types, self::$field_types );

        return self::$field_types;
    }

    /**
     * Get list of available tableless field types
     *
     * @return array Tableless field types
     *
     * @since 2.3
     */
    public static function tableless_field_types () {
        $field_types = array( 'pick', 'file', 'avatar', 'taxonomy' );

        return apply_filters( 'pods_tableless_field_types', $field_types );
    }

    /**
     * Get list of available file field types
     *
     * @return array File field types
     *
     * @since 2.3
     */
    public static function file_field_types () {
        $field_types = array( 'file', 'avatar' );

        return apply_filters( 'pods_file_field_types', $field_types );
    }

    /**
     * Get list of available repeatable field types
     *
     * @return array Repeatable field types
     *
     * @since 2.3
     */
    public static function repeatable_field_types () {
        $field_types = array( 'code', 'color', 'currency', 'date', 'datetime', 'email', 'number', 'paragraph', 'phone', 'text', 'time', 'website', 'wysiwyg' );

        return apply_filters( 'pods_repeatable_field_types', $field_types );
    }

    /**
     * Get list of available number field types
     *
     * @return array Number field types
     *
     * @since 2.3
     */
    public static function number_field_types () {
        $field_types = array( 'currency', 'number' );

        return apply_filters( 'pods_tableless_field_types', $field_types );
    }

    /**
     * Get list of available date field types
     *
     * @return array Date field types
     *
     * @since 2.3
     */
    public static function date_field_types () {
        $field_types = array( 'date', 'datetime', 'time' );

        return apply_filters( 'pods_tableless_field_types', $field_types );
    }

    /**
     * Get list of available text field types
     *
     * @return array Text field types
     *
     * @since 2.3
     */
    public static function text_field_types () {
        $field_types = array( 'code', 'paragraph', 'slug','password', 'text', 'wysiwyg' );

        return apply_filters( 'pods_text_field_types', $field_types );
    }

    /**
     * Get list of available text field types
     *
     * @return array Text field types
     *
     * @since 2.3
     */
    public static function block_field_types () {
        $field_types = array( 'html', 'section' );

        return apply_filters( 'pods_block_field_types', $field_types );
    }
}
