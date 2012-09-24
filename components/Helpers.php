<?php
/**
 * Name: Helpers
 *
 * Description: A holdover from Pods 1.x, you most likely don't need these and we recommend you use our WP filters and actions instead.
 *
 * Version: 2.0
 *
 * Menu Page: edit.php?post_type=_pods_helper
 * Menu Add Page: post-new.php?post_type=_pods_helper
 *
 * @package Pods\Components
 * @subpackage Helpers
 */

class Pods_Helpers extends PodsComponent {

    /**
     * Pods object
     *
     * @var object
     *
     * @since 2.0.0
     */
    static $obj = null;

    /**
     * Object type
     *
     * @var string
     *
     * @since 2.0.0
     */
    private $object_type = '_pods_helper';

    /**
     * Do things like register/enqueue scripts and stylesheets
     *
     * @since 2.0.0
     */
    public function __construct () {
        $args = array(
            'label' => 'Pod Helpers',
            'labels' => array( 'singular_name' => 'Pod Helper' ),
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
            $args[ 'capability_type' ] = 'pods_helper';

        $args = PodsInit::object_label_fix( $args, 'post_type' );

        register_post_type( $this->object_type, apply_filters( 'pods_internal_register_post_type_object_helper', $args ) );

        if ( is_admin() ) {
            add_action( 'dbx_post_advanced', array( $this, 'edit_page_form' ), 10 );

            add_action( 'pods_meta_groups', array( $this, 'add_meta_boxes' ) );
            add_filter( 'get_post_metadata', array( $this, 'get_meta' ), 10, 4 );
            add_filter( 'update_post_metadata', array( $this, 'save_meta' ), 10, 4 );

            add_action( 'pods_meta_save_pre__pods_helper', array( $this, 'fix_filters' ), 10, 5 );
            add_action( 'pods_meta_save_post__pods_helper', array( $this, 'clear_cache' ), 10, 5 );
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
    public function fix_filters( $data, $pod = null, $id = null, $groups = null, $post = null ) {
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

        delete_transient( 'pods_object_helper' );
        delete_transient( 'pods_object_helper_' . $post->post_title );
    }

    /**
     * Change post title placeholder text
     *
     * @since 2.0.0
     */
    public function set_title_text ( $text, $post ) {
        return __( 'Enter helper name here', 'pods' );
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
                'name' => 'helper_type',
                'label' => __( 'Helper Type', 'pods' ),
                'type' => 'pick',
                'default' => 'display',
                'data' => array(
                    'input' => 'Input (change form fields)',
                    'display' => 'Display (change field output when using magic tags)',
                    'pre_save' => 'Pre-Save (change form fields before saving)',
                    'post_save' => 'Post-Save',
                    'pre_delete' => 'Pre-Delete',
                    'post_delete' => 'Post-Delete',
                )
            ),
            array(
                'name' => 'code',
                'label' => __( 'Code', 'pods' ),
                'type' => 'code'
            )
        );

        pods_group_add( $pod, __( 'Helper', 'pods' ), $fields, 'normal', 'high' );
    }

    /**
     * Get the fields
     *
     * @param null $_null
     * @param null $post_ID
     * @param null $meta_key
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
    public function save_meta ( $_null, $post_ID = null, $meta_key = null, $meta_value = null ) {
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
     * @static
     *
     * Run a helper within a Pod Page or WP Template
     *
     * $params['helper'] string Helper name
     * $params['value'] string Value to run Helper on
     * $params['name'] string Field name
     *
     * @param array $params An associative array of parameters
     * @param null $obj
     *
     * @return mixed Anything returned by the helper
     * @since 2.0.0
     */
    public static function helper ( $params, $obj = null ) {
        if ( !empty( $obj ) )
            self::$obj =& $obj;
        else
            $obj =& self::$obj;

        if ( empty( $obj ) || !is_object( $obj ) )
            return '';

        $defaults = array(
            'helper' => '',
            'value' => '',
            'name' => '',
            'deprecated' => false
        );

        if ( is_array( $params ) )
            $params = array_merge( $defaults, $params );
        else
            $params = $defaults;

        $params = (object) $params;

        if ( empty( $params->helper ) )
            return pods_error( 'Helper name required', $obj );

        if ( !isset( $params->value ) )
            $params->value = null;

        if ( true === $params->deprecated && is_array( $params->value ) && !empty( $params->value ) && !isset( $params->value[ 0 ] ) )
            $params->value = array( $params->value );

        if ( !isset( $params->name ) )
            $params->name = null;

        $helper = $obj->api->load_helper( array( 'name' => $params->helper ) );

        ob_start();

        if ( !empty( $helper ) && !empty( $helper[ 'code' ] ) ) {
            $code = $helper[ 'code' ];

            $code = str_replace( '$this->', '$obj->', $code );
            $value =& $params->value;
            $_safe_params = $params;

            if ( !defined( 'PODS_DISABLE_EVAL' ) || !PODS_DISABLE_EVAL )
                eval( "?>{$code}" );
            else
                echo $code;

            $params = $_safe_params;
        }
        elseif ( is_callable( (string) $params->helper ) )
            echo call_user_func( (string) $params->helper, $params->value, $params->name, $params, $obj );

        $out = ob_get_clean();

        $out = apply_filters( 'pods_helpers_post_helper', $out, $params, $helper );
        $out = apply_filters( "pods_helpers_post_helper_{$params->helper}", $out, $params, $helper );

        return $out;
    }
}
