<?php
class PodsView {
    protected static $cache_modes = array( 'transient', 'site-transient', 'cache' );

    private function __construct () {
    }

    public static function view ( $view, $data = null, $expires = 0, $cache_mode = 'cache' ) {
        if ( (int) $expires < 1 )
            $expires = 0;

        if ( !in_array( $cache_mode, self::$cache_modes ) )
            $cache_mode = 'cache';

        $cache_key = sanitize_title( str_replace( PODS_DIR . 'ui/', 'pods-ui-', $view ) );

        if ( false === strpos( $view, PODS_DIR . 'ui/' ) ) {

            $output = self::get( $cache_key );
            if ( null !== $output ) {
                if ( 0 < $expires )
                    return $output;
                else
                    self::clear( $cache_key );
            }
        }

        $output = self::get_template_part( $view, $data );
        if ( false === $output )
            return false;

        if ( 0 < $expires )
            self::set( $cache_key, $output );

        $output = apply_filters( 'pods_view', $output, $view, $data, $expires, $cache_mode );

        return $output;
    }

    public static function get ( $key, $cache_mode = 'cache' ) {
        if ( !in_array( $cache_mode, self::$cache_modes ) )
            $cache_mode = 'cache';

        $value = null;
        if ( 'transient' == $cache_mode )
            $value = get_transient( 'pods_view_' . $key );
        if ( 'site-transient' == $cache_mode )
            $value = get_site_transient( 'pods_view_' . $key );
        elseif ( 'cache' == $cache_mode )
            $value = wp_cache_get( $key, 'pods_view' );

        $value = apply_filters( 'pods_view_get_' . $cache_mode, $value, $key );

        return $value;
    }

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
    }

    public static function clear ( $key, $cache_mode = null ) {
        if ( !in_array( $cache_mode, self::$cache_modes ) )
            $cache_mode = 'cache';

        if ( 'transient' == $cache_mode ) {
            if ( true === $key ) {
                global $wpdb;
                $wpdb->query("DELETE FROM `{$wpdb->options}` WHERE option_name LIKE '_transient_pods_view_%'");
            }
            else
                delete_transient( 'pods_view_' . $key );
        }
        elseif ( 'site-transient' == $cache_mode ) {
            if ( true === $key ) {
                global $wpdb;
                $wpdb->query( "DELETE FROM `{$wpdb->options}` WHERE option_name LIKE '_site_transient_pods_view_%'" );
            }
            else
                delete_site_transient( 'pods_view_' . $key );
        }
        elseif ( 'cache' == $cache_mode ) {
            if ( true === $key )
                wp_cache_flush( 'pods_view' );
            else
                wp_cache_delete( $key, 'pods_view' );
        }

        do_action( 'pods_view_clear_' . $cache_mode, $key );
    }

    private static function get_template_part ( $_view, $_data = null ) {
        $_view = self::locate_template( $_view );
        if ( empty( $_view ) )
            return $_view;

        if ( is_array( $_data ) )
            extract( $_data, EXTR_SKIP );

        ob_start();
        require $_view;
        $output = ob_get_clean();

        return $output;
    }

    private static function locate_template ( $_view ) {
        // Keep it safe
        $_view = trim( str_replace( '../', '', (string) $_view ) );

        $located = false;

        if ( empty( $_view ) )
            return false;
        elseif ( false === strpos( $_view, PODS_DIR . 'ui/' ) ) {
            $_view = trim( $_view, '/' );

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
