<?php
/**
 * Name: Templates
 *
 * Description: An easy to use templating engine for Pods. Use {@field_name} magic tags to output values, within your HTML markup.
 *
 * Version: 2.0
 *
 * Menu Page: edit.php?post_type=_pods_template
 * Menu Add Page: post-new.php?post_type=_pods_template
 *
 * @package Pods\Components
 * @subpackage Templates
 */

class Pods_Templates extends PodsComponent {

    /**
     * Pods object
     *
     * @var object
     *
     * @since 2.0.0
     */
    static $obj = null;

    /**
     * Whether to enable deprecated functionality based on old function usage
     *
     * @var bool
     *
     * @since 2.0.0
     */
    static $deprecated = false;

    /**
     * Object type
     *
     * @var string
     *
     * @since 2.0.0
     */
    private $object_type = '_pods_template';

    /**
     * Do things like register/enqueue scripts and stylesheets
     *
     * @since 2.0.0
     */
    public function __construct () {
        $args = array(
            'label' => 'Pod Templates',
            'labels' => array( 'singular_name' => 'Pod Template' ),
            'public' => false,
            'can_export' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'query_var' => false,
            'rewrite' => false,
            'has_archive' => false,
            'hierarchical' => false,
            'supports' => array( 'title', 'author', 'revisions' ),
            'menu_icon' => PODS_URL . 'ui/images/icon16.png'
        );

        if ( !is_super_admin() )
            $args[ 'capability_type' ] = 'pods_template';

        $args = PodsInit::object_label_fix( $args, 'post_type' );

        register_post_type( $this->object_type, apply_filters( 'pods_internal_register_post_type_object_template', $args ) );

        if ( is_admin() ) {
            add_action( 'dbx_post_advanced', array( $this, 'edit_page_form' ), 10 );

            add_action( 'pods_meta_groups', array( $this, 'add_meta_boxes' ) );
            add_filter( 'get_post_metadata', array( $this, 'get_meta' ), 10, 4 );
            add_filter( 'update_post_metadata', array( $this, 'save_meta' ), 10, 4 );

            add_action( 'pods_meta_save_pre__pods_template', array( $this, 'fix_filters' ), 10, 5 );
            add_action( 'pods_meta_save_post__pods_template', array( $this, 'clear_cache' ), 10, 5 );
            add_action( 'delete_post', array( $this, 'clear_cache' ), 10, 1 );
        }
    }

    /**
     * Enqueue styles
     *
     * @since 2.0.0
     */
    public function admin_assets () {
        wp_enqueue_style( 'pods-admin' );
    }

    /**
     * Fix filters, specifically removing balanceTags
     *
     * @since 2.0.1
     */
    public function fix_filters ( $data, $pod = null, $id = null, $groups = null, $post = null ) {
        remove_filter( 'content_save_pre', 'balanceTags', 50 );
    }

    /**
     * Clear cache on save
     *
     * @since 2.0.0
     */
    public function clear_cache ( $data, $pod = null, $id = null, $groups = null, $post = null ) {
        if ( !is_array( $data ) && 0 < $data ) {
            $post = $data;
            $post = get_post( $post );

            if ( $this->object_type != $post->post_type )
                return;
        }

        delete_transient( 'pods_object_template' );
        delete_transient( 'pods_object_template_' . $post->post_title );
    }

    /**
     * Change post title placeholder text
     *
     * @since 2.0.0
     */
    public function set_title_text ( $text, $post ) {
        return __( 'Enter template name here', 'pods' );
    }

    /**
     * Edit page form
     *
     * @since 2.0.0
     */
    public function edit_page_form () {
        global $post_type;

        if ( $this->object_type != $post_type )
            return;

        add_filter( 'enter_title_here', array( $this, 'set_title_text' ), 10, 2 );
    }

    /**
     * Add meta boxes to the page
     *
     * @since 2.0.0
     */
    public function add_meta_boxes () {
        $pod = array(
            'name' => $this->object_type,
            'type' => 'post_type'
        );

        if ( isset( PodsMeta::$post_types[ $pod[ 'name' ] ] ) )
            return;

        $fields = array(
            array(
                'name' => 'code',
                'label' => __( 'Content', 'pods' ),
                'type' => 'code'
            )
        );

        pods_group_add( $pod, __( 'Template', 'pods' ), $fields, 'normal', 'high' );

        add_filter( 'update_post_metadata', array( $this, 'save_meta' ), 9, 5 );
        add_filter( 'get_post_metadata', array( $this, 'get_meta' ), 9, 4 );
    }

    /**
     * Get the fields
     *
     * @param null $_null
     * @param int $post_ID
     * @param string $meta_key
     * @param bool $single
     *
     * @return array|bool|int|mixed|null|string|void
     */
    public function get_meta( $_null, $post_ID = null, $meta_key = null, $single = false ) {
        if ( 'code' == $meta_key ) {
            $post = get_post( $post_ID );

            if ( is_object( $post ) && $this->object_type == $post->post_type )
                return $post->post_content;
        }

        return $_null;
    }

    /**
     * Save the fields
     *
     * @param $_null
     * @param int $post_ID
     * @param string $meta_key
     * @param string $meta_value
     *
     * @return bool|int|null
     */
    public function save_meta( $_null, $post_ID = null, $meta_key = null, $meta_value = null ) {
        if ( 'code' == $meta_key ) {
            $post = get_post( $post_ID );

            if ( is_object( $post ) && $this->object_type == $post->post_type ) {
                $postdata = array(
                    'ID' => $post_ID,
                    'post_content' => $meta_value
                );

                remove_filter( current_filter(), array( $this, __FUNCTION__ ), 10 );

                wp_update_post( $postdata );

                return true;
            }
        }

        return $_null;
    }

    /**
     * Display the page template
     *
     * @param string $template The template name
     * @param string $code Custom template code to use instead
     * @param object $obj The Pods object
     * @param bool $deprecated Whether to use deprecated functionality based on old function usage
     *
     * @return mixed|string|void
     * @since 2.0.0
     */
    public static function template ( $template, $code = null, $obj = null, $deprecated = false ) {
        if ( !empty( $obj ) )
            self::$obj =& $obj;
        else
            $obj =& self::$obj;

        self::$deprecated = $deprecated;

        if ( empty( $obj ) || !is_object( $obj ) )
            return '';

        if ( empty( $code ) && !empty( $template ) ) {
            $template = $obj->api->load_template( array( 'name' => $template ) );

            if ( !empty( $template ) && !empty( $template[ 'code' ] ) )
                $code = $template[ 'code' ];
        }

        $code = apply_filters( 'pods_templates_pre_template', $code, $template, $obj );
        $code = apply_filters( "pods_templates_pre_template_{$template}", $code, $template, $obj );

        ob_start();

        if ( !empty( $code ) ) {
            // Only detail templates need $this->id
            if ( empty( $obj->id ) ) {
                while ( $obj->fetch() ) {
                    echo self::do_template( $code );
                }
            }
            else
                echo self::do_template( $code );
        }

        $out = ob_get_clean();

        $out = apply_filters( 'pods_templates_post_template', $out, $code, $template, $obj );
        $out = apply_filters( "pods_templates_post_template_{$template}", $out, $code, $template, $obj );

        return $out;
    }

    /**
     * Parse a template string
     *
     * @param string $code The template string to parse
     * @param object $obj The Pods object
     *
     * @since 1.8.5
     */
    public static function do_template ( $code, $obj = null ) {
        $php = true;

        if ( !empty( $obj ) ) {
            self::$obj =& $obj;

            $php = false;
        }
        else
            $obj =& self::$obj;

        if ( empty( $obj ) || !is_object( $obj ) )
            return '';

        $code = str_replace( '$this->', '$obj->', $code );

        if ( $php && ( !defined( 'PODS_DISABLE_EVAL' ) || !PODS_DISABLE_EVAL ) ) {
            ob_start();

            eval( "?>$code" );

            $out = ob_get_clean();
        }
        else
            $out = $code;

        $out = preg_replace_callback( '/({@(.*?)})/m', array( 'self', 'do_magic_tags' ), $out );

        return apply_filters( 'pods_templates_do_template', $out, $code, $obj );
    }

    /**
     * Replace magic tags with their values
     *
     * @param string $tag The magic tag to evaluate
     * @param object $obj The Pods object
     *
     * @since 1.x
     */
    public static function do_magic_tags ( $tag, $obj = null ) {
        if ( !empty( $obj ) )
            self::$obj =& $obj;
        else
            $obj =& self::$obj;

        if ( empty( $obj ) || !is_object( $obj ) )
            return '';

        if ( is_array( $tag ) ) {
            if ( !isset( $tag[ 2 ] ) && strlen( trim( $tag[ 2 ] ) ) < 1 )
                return;

            $tag = $tag[ 2 ];
        }

        $tag = trim( $tag, ' {@}' );
        $tag = explode( ',', $tag );

        if ( empty( $tag ) || !isset( $tag[ 0 ] ) || strlen( trim( $tag[ 0 ] ) ) < 1 )
            return;

        foreach ( $tag as $k => $v ) {
            $tag[ $k ] = trim( $v );
        }

        $field_name = $tag[ 0 ];

        if ( 'type' == $field_name )
            $value = $obj->pod;
        else
            $value = $obj->field( $field_name );

        $helper_name = $before = $after = '';

        if ( isset( $tag[ 1 ] ) && !empty( $tag[ 1 ] ) ) {
            $helper_name = $tag[ 1 ];

            $params = array(
                'helper' => $helper_name,
                'value' => $value,
                'name' => $field_name,
                'deprecated' => self::$deprecated
            );

            if ( class_exists( 'Pods_Helpers' ) )
                $value = Pods_Helpers::helper( $params, $obj );
        }

        if ( isset( $tag[ 2 ] ) && !empty( $tag[ 2 ] ) )
            $before = $tag[ 2 ];

        if ( isset( $tag[ 3 ] ) && !empty( $tag[ 3 ] ) )
            $after = $tag[ 3 ];

        $value = apply_filters( 'pods_templates_do_magic_tags', $value, $field_name, $helper_name, $before, $after );

        if ( is_array( $value ) )
            $value = pods_serial_comma( $value, $field_name, $obj->fields );

        if ( null !== $value && false !== $value )
            return $before . $value . $after;

        return;

    }
}
