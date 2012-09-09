<?php
/**
 * Name: Helpers
 *
 * Description:
 *
 * Version: 2.0
 *
 * Menu Page: edit.php?post_type=_pods_helper
 * Menu Add Page: post-new.php?post_type=_pods_helper
 *
 * Developer Mode: on
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
