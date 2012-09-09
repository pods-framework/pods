<?php
/**
 * Name: Pages
 *
 * Description: Create advanced URL structures using wildcards, they can exist on-top of any existing WordPress URL or be entirely custom. Add a path and select the WP Template to use, the rest is up to you!
 *
 * Version: 2.0
 *
 * Menu Page: edit.php?post_type=_pods_page
 * Menu Add Page: post-new.php?post_type=_pods_page
 *
 * Developer Mode: on
 *
 * @package pods
 * @subpackage pages
 */

class Pods_Pages extends PodsComponent {

    /**
     * Current Pod Page
     *
     * @var array
     *
     * @since 2.0.0
     */
    static $exists = null;

    /**
     * Do things like register/enqueue scripts and stylesheets
     *
     * @since 2.0.0
     */
    public function __construct () {
        $args = array(
            'label' => 'Pages',
            'labels' => array( 'singular_name' => 'Page' ),
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
            $args[ 'capability_type' ] = 'pods_page';

        $args = PodsInit::object_label_fix( $args, 'post_type' );

        register_post_type( '_pods_page', apply_filters( 'pods_internal_register_post_type_object_page', $args ) );

        if ( !is_admin() )
            add_action( 'init', array( $this, 'page_check' ), 12 );
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
     * Check if a Pod Page exists
     */
    public function page_check () {
        global $pods;

        if ( !defined( 'PODS_DISABLE_POD_PAGE_CHECK' ) || !PODS_DISABLE_POD_PAGE_CHECK ) {
            if ( null === self::$exists )
                self::$exists = pod_page_exists();

            if ( false !== self::$exists ) {
                $pods = apply_filters( 'pods_global', $pods, self::$exists );

                if ( 404 != $pods && ( !is_object( $pods ) || !is_wp_error( $pods ) ) ) {
                    add_action( 'template_redirect', array( $this, 'template_redirect' ) );
                    add_filter( 'redirect_canonical', '__return_false' );
                    add_action( 'wp_head', array( $this, 'wp_head' ) );
                    add_filter( 'wp_title', array( $this, 'wp_title' ), 0, 3 );
                    add_filter( 'body_class', array( $this, 'body_class' ), 0, 1 );
                    add_filter( 'status_header', array( $this, 'status_header' ) );
                    add_action( 'after_setup_theme', array( $this, 'precode' ) );
                    add_action( 'wp', array( $this, 'silence_404' ) );
                }
            }
        }
    }

    /**
     * Output Pod Page Content
     *
     * @param bool $return Whether to return or not (default is to echo)
     *
     * @return string
     */
    public static function content ( $return = false ) {
        $content = false;

        if ( false !== self::$exists ) {
            if ( 0 < strlen( trim( self::$exists[ 'phpcode' ] ) ) )
                $content = self::$exists[ 'phpcode' ];

            ob_start();

            do_action( 'pods_content_pre', self::$exists, $content );

            if ( false !== $content ) {
                if ( !defined( 'PODS_DISABLE_EVAL' ) || !PODS_DISABLE_EVAL ) {
                    pods_deprecated( 'Use WP Page Templates or hook into the pods_content filter instead of using Pod Page PHP code', '2.1.0' );

                    eval( "?>$content" );
                }
                else
                    echo $content;
            }

            do_action( 'pods_content_post', self::$exists, $content );

            $content = ob_get_clean();
        }

        $content = apply_filters( 'pods_content', $content, self::$exists );

        if ( $return )
            return $content;

        echo $content;
    }

    /**
     * Check to see if Pod Page exists and return data
     *
     * $uri not required, if NULL then returns REQUEST_URI matching Pod Page
     *
     * @param string $uri The Pod Page URI to check if exists
     *
     * @return array|bool
     */
    public static function exists ( $uri = null ) {
        if ( null === $uri ) {
            $uri = parse_url( get_current_url() );
            $uri = $uri[ 'path' ];
            $home = parse_url( get_bloginfo( 'url' ) );

            if ( !empty( $home ) && isset( $home[ 'path' ] ) && '/' != $home[ 'path' ] )
                $uri = substr( $uri, strlen( $home[ 'path' ] ) );
        }

        $uri = trim( $uri, '/' );
        $uri_depth = count( array_filter( explode( '/', $uri ) ) ) - 1;

        if ( false !== strpos( $uri, 'wp-admin' ) || false !== strpos( $uri, 'wp-includes' ) )
            return false;

        // See if the custom template exists
        $sql = "SELECT * FROM `@wp_posts` WHERE `post_type` = '_pods_page' AND `post_title` = %s LIMIT 1";
        $sql = array( $sql, array( $uri ) );

        $result = pods_query( $sql );

        if ( empty( $result ) ) {
            // Find any wildcards
            $sql = "
                    SELECT *
                    FROM `@wp_posts`
                    WHERE
                        `post_type` = '_pods_page'
                        AND %s LIKE REPLACE(`post_title`, '*', '%%')
                        AND (LENGTH(`post_title`) - LENGTH(REPLACE(`post_title`, '/', ''))) = %d
                    ORDER BY LENGTH(`post_title`) DESC, `post_title` DESC
                    LIMIT 1
                ";
            $sql = array( $sql, array( $uri, $uri_depth ) );

            $result = pods_query( $sql );
        }

        if ( !empty( $result ) ) {
            $_object = get_object_vars( $result[ 0 ] );

            $object = array(
                'ID' => $_object[ 'ID' ],
                'uri' => $_object[ 'post_title' ],
                'phpcode' => $_object[ 'post_content' ],
                'precode' => get_post_meta( $_object[ 'ID' ], 'precode', true ),
                'page_template' => get_post_meta( $_object[ 'ID' ], 'page_template', true ),
                'title' => get_post_meta( $_object[ 'ID' ], 'page_title', true )
            );

            return $object;
        }

        return false;
    }

    /**
     * Run any precode for current Pod Page
     */
    public function precode () {
        global $pods;

        if ( false !== self::$exists ) {
            $content = false;

            if ( 0 < strlen( trim( self::$exists[ 'precode' ] ) ) )
                $content = self::$exists[ 'precode' ];

            if ( false !== $content && ( !defined( 'PODS_DISABLE_EVAL' ) || !PODS_DISABLE_EVAL ) ) {
                pods_deprecated( 'Use WP Page Templates or hook into the pods_page_precode action instead of using Pod Page Precode', '2.1.0' );

                eval( "?>$content" );
            }

            do_action( 'pods_page_precode', self::$exists, $pods, $content );

            if ( !is_object( $pods ) && ( 404 == $pods || is_wp_error( $pods ) ) ) {
                remove_action( 'template_redirect', array( $this, 'template_redirect' ) );
                remove_action( 'wp_head', array( $this, 'wp_head' ) );
                remove_filter( 'redirect_canonical', '__return_false' );
                remove_filter( 'wp_title', array( $this, 'wp_title' ) );
                remove_filter( 'body_class', array( $this, 'body_class' ) );
                remove_filter( 'status_header', array( $this, 'status_header' ) );
                remove_action( 'wp', array( $this, 'silence_404' ) );
            }
        }
    }

    /**
     *
     */
    public function wp_head () {
        global $pods;

        do_action( 'pods_wp_head' );

        if ( !defined( 'PODS_DISABLE_VERSION_OUTPUT' ) || !PODS_DISABLE_VERSION_OUTPUT ) {
?>
    <!-- Pods Framework <?php echo PODS_VERSION; ?> -->
<?php
        }
        if ( ( !defined( 'PODS_DISABLE_META' ) || !PODS_DISABLE_META ) && is_object( $pods ) && !is_wp_error( $pods ) ) {

            if ( isset( $pods->meta ) && is_array( $pods->meta ) ) {
                foreach ( $pods->meta as $name => $content ) {
                    if ( 'title' == $name )
                        continue;
?>
    <meta name="<?php echo esc_attr( $name ); ?>" content="<?php echo esc_attr( $content ); ?>" />
<?php
                }
            }

            if ( isset( $pods->meta_properties ) && is_array( $pods->meta_properties ) ) {
                foreach ( $pods->meta_properties as $property => $content ) {
?>
    <meta property="<?php echo esc_attr( $property ); ?>" content="<?php echo esc_attr( $content ); ?>" />
<?php
                }
            }

            if ( isset( $pods->meta_extra ) && 0 < strlen( $pods->meta_extra ) )
                echo $pods->meta_extra;
        }
    }

    /**
     * @param $title
     * @param $sep
     * @param $seplocation
     * @return mixed|void
     */
    public function wp_title ( $title, $sep, $seplocation ) {
        global $pods;

        $page_title = self::$exists[ 'title' ];

        if ( 0 < strlen( trim( $page_title ) ) ) {
            if ( is_object( $pods ) && !is_wp_error( $pods ) )
                $page_title = preg_replace_callback( "/({@(.*?)})/m", array( $pods, "parse_magic_tags" ), $page_title );

            $title = ( 'right' == $seplocation ) ? $page_title . " $sep " : " $sep " . $page_title;
        }
        else {
            $uri = explode( '?', $_SERVER[ 'REQUEST_URI' ] );
            $uri = preg_replace( "@^([/]?)(.*?)([/]?)$@", "$2", $uri[ 0 ] );
            $uri = preg_replace( "@(-|_)@", " ", $uri );
            $uri = explode( '/', $uri );

            $title = '';

            foreach ( $uri as $key => $page_title ) {
                $title .= ( 'right' == $seplocation ) ? ucwords( $page_title ) . " $sep " : " $sep " . ucwords( $page_title );
            }
        }

        if ( ( !defined( 'PODS_DISABLE_META' ) || !PODS_DISABLE_META ) && is_object( $pods ) && !is_wp_error( $pods ) && isset( $pods->meta ) && is_array( $pods->meta ) && isset( $pods->meta[ 'title' ] ) )
            $title = $pods->meta[ 'title' ];

        return apply_filters( 'pods_title', $title, $sep, $seplocation );
    }

    /**
     * @param $classes
     * @return mixed|void
     */
    public function body_class ( $classes ) {
        global $pods;

        if ( defined( 'PODS_DISABLE_BODY_CLASSES' ) && PODS_DISABLE_BODY_CLASSES )
            return $classes;

        $classes[] = 'pods';

        $uri = explode( '?', self::$exists[ 'uri' ] );
        $uri = explode( '#', $uri[ 0 ] );

        $class = str_replace( array( '*', '/' ), array( '_w_', '-' ), $uri[ 0 ] );
        $class = sanitize_title( $class );
        $class = str_replace( array( '_', '--', '--' ), '-', $class );
        $class = trim( $class, '-' );

        $classes[] = 'pod-page-' . $class;

        if ( is_object( $pods ) && !is_wp_error( $pods ) ) {
            $class = sanitize_title( $pods->pod );
            $class = str_replace( array( '_', '--', '--' ), '-', $class );
            $class = trim( $class, '-' );
            $classes[] = 'pod-' . $class;
        }

        if ( is_object( $pods ) && !is_wp_error( $pods ) && isset( $pods->body_classes ) )
            $classes[] = $pods->body_classes;

        return apply_filters( 'pods_body_class', $classes, $uri );
    }

    /**
     * @return string
     */
    public function status_header () {
        return $_SERVER[ 'SERVER_PROTOCOL' ] . ' 200 OK';
    }

    /**
     *
     */
    public function silence_404 () {
        global $wp_query;

        $wp_query->query_vars[ 'error' ] = '';
        $wp_query->is_404 = false;
    }

    /**
     *
     */
    public function template_redirect () {
        global $pods;

        if ( false !== self::$exists ) {
            /*
             * Create pods.php in your theme directory, and
             * style it to suit your needs. Some helpful functions:
             *
             * get_header()
             * pods_content()
             * get_sidebar()
             * get_footer()
             */
            $template = self::$exists[ 'page_template' ];
            $template = apply_filters( 'pods_page_template', $template, self::$exists );

            $render_function = apply_filters( 'pods_template_redirect', null, $template, self::$exists );

            do_action( 'pods_page', $template, self::$exists );

            if ( null !== $render_function && is_callable( $render_function ) )
                call_user_func( $render_function, $template, self::$exists );
            elseif ( ( !defined( 'PODS_DISABLE_DYNAMIC_TEMPLATE' ) || !PODS_DISABLE_DYNAMIC_TEMPLATE ) && is_object( $pods ) && !is_wp_error( $pods ) && isset( $pods->page_template ) && !empty( $pods->page_template ) && '' != locate_template( array( $pods->page_template ), true ) ) {
                $template = $pods->page_template;
                // found the template and included it, we're good to go!
            }
            elseif ( !empty( self::$exists[ 'page_template' ] ) && '' != locate_template( array( self::$exists[ 'page_template' ] ), true ) ) {
                $template = self::$exists[ 'page_template' ];
                // found the template and included it, we're good to go!
            }
            elseif ( '' != locate_template( apply_filters( 'pods_page_default_templates', array( 'pods.php' ) ), true ) ) {
                $template = 'pods.php';
                // found the template and included it, we're good to go!
            }
            else {
                // templates not found in theme, default output
                do_action( 'pods_page_default', $template, self::$exists );

                get_header();
                pods_content();
                get_sidebar();
                get_footer();
            }

            do_action( 'pods_page_end', $template, self::$exists );

            exit;
        }
    }
}

/**
 * Find out if the current page is a Pod Page
 *
 * @param string $uri The Pod Page URI to check if currently on
 *
 * @return bool
 * @since 1.7.5
 */
function is_pod_page ( $uri = null ) {
    if ( false !== Pods_Pages::$exists && ( null === $uri || $uri == Pods_Pages::$exists[ 'uri' ] ) )
        return true;

    return false;
}

/**
 * Check to see if Pod Page exists and return data
 *
 * $uri not required, if NULL then returns REQUEST_URI matching Pod Page
 *
 * @param string $uri The Pod Page URI to check if exists
 *
 * @return array
 */
function pod_page_exists ( $uri = null ) {
    return Pods_Pages::exists( $uri );
}

/**
 * Output Pod Page Content
 *
 * @param bool $return Whether to return or not (default is to echo)
 *
 * @return string
 */
function pods_content ( $return = false ) {
    return Pods_Pages::content( $return );
}

/*
 * Deprecated global variable
 */
global $pod_page_exists;
$pod_page_exists =& Pods_Pages::$exists;