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
 * @package pods
 * @subpackage helpers
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
     * Do things like register/enqueue scripts and stylesheets
     *
     * @since 2.0.0
     */
    public function __construct () {
        $args = array(
            'label' => 'Pod Helpers',
            'labels' => array( 'singular_name' => 'Pod Helper' ),
            'public' => false,
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

        register_post_type( '_pods_helper', apply_filters( 'pods_internal_register_post_type_object_helper', $args ) );

        add_action( 'dbx_post_advanced', array( $this, 'edit_page_form' ), 10 );
        add_action( 'pods_meta_save_post__pods_template', array( $this, 'clear_cache' ), 10, 5 );
        add_action( 'delete_post', array( $this, 'clear_cache' ), 10, 1 );
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
     * Clear cache on save
     *
     * @since 2.0.0
     */
    public function clear_cache ( $data, $pod = null, $id = null, $groups = null, $post = null ) {
        if ( !is_array( $data ) && 0 < $data ) {
            $post = $data;
            $post = get_post( $post );

            if ( '_pods_helper' != $post->post_type )
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

        if ( '_pods_helper' != $post_type )
            return;

        add_filter( 'enter_title_here', array( $this, 'set_title_text' ), 10, 2 );

        $this->add_meta_boxes();
    }

    /**
     * Add meta boxes to the page
     *
     * @since 2.0.0
     */
    public function add_meta_boxes () {
        $pod = array(
            'name' => '_pods_helper',
            'type' => 'post_type'
        );

        $fields = array(
            array(
                'name' => 'code',
                'label' => __( 'Code', 'pods' ),
                'type' => 'paragraph',
                'options' => array(
                    'paragraph_format_type' => 'codemirror'
                )
            )
        );

        pods_group_add( $pod, __( 'Helper', 'pods' ), $fields, 'normal', 'high' );

        add_filter( 'update_post_metadata', array( $this, 'save_meta' ), 9, 5 );
        add_filter( 'get_post_metadata', array( $this, 'get_meta' ), 9, 4 );
    }

    /**
     * Save the fields
     *
     * @param $object_type
     * @param null $_null
     * @param int $object_id
     * @param string $meta_key
     * @param string $meta_value
     * @param string $prev_value
     *
     * @return bool|int|null
     */
    public function save_meta ( $_null = null, $object_id = 0, $meta_key = '', $meta_value = '', $prev_value = '' ) {
        if ( 'code' == $meta_key ) {
            $postdata = array(
                'ID' => $object_id,
                'post_content' => $meta_value
            );

            wp_update_post( $postdata );

            return true;
        }

        return $_null;
    }

    /**
     * Get the fields
     *
     * @param null $_null
     * @param int $object_id
     * @param string $meta_key
     * @param bool $single
     *
     * @return array|bool|int|mixed|null|string|void
     */
    public function get_meta ( $_null = null, $object_id = 0, $meta_key = '', $single = false ) {
        if ( 'code' == $meta_key ) {
            $post = get_post( $object_id );

            return $post->post_content;
        }

        return $_null;
    }

    /**
     * Run a helper within a Pod Page or WP Template
     *
     * $params['helper'] string Helper name
     * $params['value'] string Value to run Helper on
     * $params['name'] string Field name
     *
     * @param array $params An associative array of parameters
     *
     * @return mixed Anything returned by the helper
     * @since 2.0.0
     */
    public function helper ( $params, $obj = null ) {
        if ( !empty( $obj ) )
            self::$obj =& $obj;
        else
            $obj =& self::$obj;

        if ( empty( $obj ) || !is_object( $obj ) )
            return '';

        $defaults = array(
            'helper' => '',
            'value' => '',
            'name' => ''
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

        if ( !isset( $params->name ) )
            $params->name = null;

        $helper = $obj->api->load_helper( array( 'name' => $params->helper ) );

        ob_start();

        if ( !empty( $helper ) && !empty( $helper[ 'code' ] ) ) {
            if ( !defined( 'PODS_DISABLE_EVAL' ) || !PODS_DISABLE_EVAL )
                eval( "?>{$helper['code']}" );
            else
                echo $helper[ 'code' ];
        }
        elseif ( is_callable( (string) $params->helper ) )
            echo call_user_func( (string) $params->helper, $params->value, $params->name, $params, $obj );

        $out = ob_get_clean();

        $out = apply_filters( 'pods_helpers_post_helper', $out, $params, $helper );
        $out = apply_filters( "pods_helpers_post_helper_{$params->helper}", $out, $params, $helper );

        return $out;
    }
}
