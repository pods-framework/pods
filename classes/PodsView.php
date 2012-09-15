<?php
/**
 *
 */
class PodsView {

    /**
     * @var array $cache_modes Array of available cache modes
     */
    static $cache_modes = array( 'transient', 'site-transient', 'cache' );

    /**
     *
     */
    private function __construct () {
    }

    /**
     * @static
     *
     * @param string $view Path of the view file
     * @param array|null $data (optional) Data to pass on to the template
     * @param int $expires (optional) Time in seconds for the cache to expire, if 0 caching is disabled.
     * @param string $cache_mode (optional) Decides the caching method to use for the view.
     * @internal param bool $return (optional) If true doesn not echo the result of the view, the function returns it
     *
     * @return bool|mixed|null|string|void
     *
     * @since 2.0.0
     */
    public static function view ( $view, $data = null, $expires = 0, $cache_mode = 'cache' ) {
        if ( (int) $expires < 1 )
            $expires = 0;

        if ( !in_array( $cache_mode, self::$cache_modes ) )
            $cache_mode = 'cache';

        $cache_key = sanitize_title( pods_str_replace( array( PODS_DIR . 'ui/', PODS_DIR . 'components/', ABSPATH, WP_CONTENT_DIR, '.php' ), array( 'ui-', 'ui-', 'custom-', 'custom-', '' ), $view, 1 ) );

        $view = apply_filters( 'pods_view_inc_' . $cache_key, $view, $data, $expires, $cache_mode );

        if ( false === strpos( $view, PODS_DIR . 'ui/' ) && false === strpos( $view, PODS_DIR . 'components/' ) && false === strpos( $view, WP_CONTENT_DIR ) && false === strpos( $view, ABSPATH ) ) {
            $output = self::get( 'pods-view-' . $cache_key, $cache_mode );

            if ( false !== $output && null !== $output ) {
                if ( 0 < $expires )
                    return $output;
                else
                    self::clear( 'pods-view-' . $cache_key, $cache_mode );
            }
        }

        $output = self::get_template_part( $view, $data );

        if ( false === $output )
            return false;

        if ( 0 < $expires )
            self::set( 'pods-view-' . $cache_key, $output, $cache_mode );

        $output = apply_filters( 'pods_view_output_' . $cache_key, $output, $view, $data, $expires, $cache_mode );
        $output = apply_filters( 'pods_view_output', $output, $view, $data, $expires, $cache_mode );

        return $output;
    }

    /**
     * @static
     *
     * @param string $key Key for the cache
     * @param string $cache_mode (optional) Decides the caching method to use for the view.
     *
     * @return bool|mixed|null|void
     *
     * @since 2.0.0
     */
    public static function get ( $key, $cache_mode = 'cache' ) {
        if ( !in_array( $cache_mode, self::$cache_modes ) )
            $cache_mode = 'cache';

        $value = null;

        if ( 'transient' == $cache_mode )
            $value = get_transient( 'pods_view_' . $key );
        elseif ( 'site-transient' == $cache_mode )
            $value = get_site_transient( 'pods_view_' . $key );
        elseif ( 'cache' == $cache_mode )
            $value = wp_cache_get( $key, 'pods_view' );

        $value = apply_filters( 'pods_view_get_' . $cache_mode, $value, $key );

        return $value;
    }

    /**
     * @static
     *
     * Set a cached value
     *
     * @param string $key Key for the cache
     * @param mixed $value Value to add to the cache
     * @param int $expires (optional) Time in seconds for the cache to expire, if 0 caching is disabled.
     * @param string $cache_mode (optional) Decides the caching method to use for the view.
     *
     * @return bool|mixed|null|string|void
     *
     * @since 2.0.0
     */
    public static function set ( $key, $value, $expires = 0, $cache_mode = null ) {
        if ( (int) $expires < 1 )
            $expires = 0;

        if ( !in_array( $cache_mode, self::$cache_modes ) )
            $cache_mode = 'cache';

        if ( 'transient' == $cache_mode )
            set_transient( 'pods_view_' . $key, $value, $expires );
        elseif ( 'site-transient' == $cache_mode )
            set_site_transient( 'pods_view_' . $key, $value, $expires );
        elseif ( 'cache' == $cache_mode )
            wp_cache_set( $key, $value, 'pods_view', $expires );

        do_action( 'pods_view_set_' . $cache_mode, $key, $value, $expires );

        return $value;
    }

    /**
     * @static
     *
     * Clear a cached value
     *
     * @param string $key Key for the cache
     * @param string $cache_mode (optional) Decides the caching method to use for the view.
     *
     * @return bool
     *
     * @since 2.0.0
     */
    public static function clear ( $key, $cache_mode = null ) {
        global $wpdb;

        if ( !in_array( $cache_mode, self::$cache_modes ) )
            $cache_mode = 'cache';

        if ( 'transient' == $cache_mode ) {
            if ( true === $key ) {
                $wpdb->query( "DELETE FROM `{$wpdb->options}` WHERE option_name LIKE '_transient_pods_view_%'" );
                wp_cache_flush();
            }
            else
                delete_transient( 'pods_view_' . $key );
        }
        elseif ( 'site-transient' == $cache_mode ) {
            if ( true === $key ) {
                $wpdb->query( "DELETE FROM `{$wpdb->options}` WHERE option_name LIKE '_site_transient_pods_view_%'" );
                wp_cache_flush();
            }
            else
                delete_site_transient( 'pods_view_' . $key );
        }
        elseif ( 'cache' == $cache_mode ) {
            if ( true === $key )
                wp_cache_flush();
            else
                wp_cache_delete( $key, 'pods_view' );
        }

        do_action( 'pods_view_clear_' . $cache_mode, $key );

        return true;
    }

    /**
     * @static
     *
     * @param $_view
     * @param null|array $_data
     *
     * @return bool|mixed|string|void
     */
    private static function get_template_part ( $_view, $_data = null ) {
        $_view = self::locate_template( $_view );
        if ( empty( $_view ) )
            return $_view;

        if ( !empty( $_data ) && is_array( $_data ) )
            extract( $_data, EXTR_SKIP );

        ob_start();
        require $_view;
        $output = ob_get_clean();

        return $output;
    }

    /**
     * @static
     *
     * @param $_view
     *
     * @return bool|mixed|string|void
     */
    private static function locate_template ( $_view ) {
        // Keep it safe
        $_view = trim( str_replace( '../', '', (string) $_view ) );

        $located = false;

        if ( empty( $_view ) )
            return false;
        elseif ( false === strpos( $_view, PODS_DIR . 'ui/' ) && false === strpos( $_view, PODS_DIR . 'components/' ) && false === strpos( $_view, WP_CONTENT_DIR ) && false === strpos( $_view, ABSPATH ) ) {
            $_view = rtrim( $_view, '/' );

            if ( empty( $_view ) )
                return false;

            if ( file_exists( STYLESHEETPATH . '/' . $_view ) )
                $located = STYLESHEETPATH . '/' . $_view;
            elseif ( file_exists( TEMPLATEPATH . '/' . $_view ) )
                $located = TEMPLATEPATH . '/' . $_view;
        }
        elseif ( file_exists( $_view ) )
            $located = $_view;
        else
            $located = apply_filters( 'pods_view_locate_template', $located, $_view );

        return $located;
    }
}
