<?php
/**
 * @package Pods
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
     * @param bool|int|array $expires (optional) Time in seconds for the cache to expire, if 0 caching is disabled.
     * @param string $cache_mode (optional) Decides the caching method to use for the view.
     *
     * @return bool|mixed|null|string|void
     *
     * @since 2.0.0
     */
    public static function view ( $view, $data = null, $expires = false, $cache_mode = 'cache' ) {
        // Different $expires if user is anonymous or logged in or specific capability
        if ( is_array( $expires ) ) {
            $anon = pods_var_raw( 0, $expires, false );
            $user = pods_var_raw( 1, $expires, false );
            $capability = pods_var_raw( 2, $expires, null, null, true );

            $expires = pods_var_user( $anon, $user, $capability );
        }

        if ( false !== $expires && empty( $expires ) )
            $expires = 0;

        if ( !in_array( $cache_mode, self::$cache_modes ) )
            $cache_mode = 'cache';

        $cache_key = sanitize_title( pods_str_replace( array( PODS_DIR . 'ui/', PODS_DIR . 'components/', ABSPATH, WP_CONTENT_DIR, '.php' ), array( 'ui-', 'ui-', 'custom-', 'custom-', '' ), $view, 1 ) );

        $view = apply_filters( 'pods_view_inc_' . $cache_key, $view, $data, $expires, $cache_mode );

        if ( false === strpos( $view, PODS_DIR . 'ui/' ) && false === strpos( $view, PODS_DIR . 'components/' ) && false === strpos( $view, WP_CONTENT_DIR ) && false === strpos( $view, ABSPATH ) ) {
            $output = self::get( 'pods-view-' . $cache_key, $cache_mode, 'pods_view' );

            if ( false !== $output && null !== $output ) {
                if ( false !== $expires )
                    return $output;
                else
                    self::clear( 'pods-view-' . $cache_key, $cache_mode, 'pods_view' );
            }
        }

        $output = self::get_template_part( $view, $data );

        if ( false === $output )
            return false;

        if ( false !== $expires )
            self::set( 'pods-view-' . $cache_key, $output, $expires, $cache_mode, 'pods_view' );

        $output = apply_filters( 'pods_view_output_' . $cache_key, $output, $view, $data, $expires, $cache_mode );
        $output = apply_filters( 'pods_view_output', $output, $view, $data, $expires, $cache_mode );

        return $output;
    }

    /**
     * @static
     *
     * @param string $key Key for the cache
     * @param string $cache_mode (optional) Decides the caching method to use for the view.
     * @param string $group (optional) Set the group of the value.
     *
     * @return bool|mixed|null|void
     *
     * @since 2.0.0
     */
    public static function get ( $key, $cache_mode = 'cache', $group = '' ) {
        $object_cache = false;

        if ( isset( $GLOBALS[ 'wp_object_cache' ] ) && is_object( $GLOBALS[ 'wp_object_cache' ] ) )
            $object_cache = true;

        if ( !in_array( $cache_mode, self::$cache_modes ) )
            $cache_mode = 'cache';

        $group_key = '';

        if ( !empty( $group ) )
            $group_key = $group . '_';

        $original_key = $key;

        // Patch for limitations in DB
        if ( 44 < strlen( $group_key . $key ) ) {
            $key = md5( $key );

            if ( empty( $group_key ) )
                $group_key = 'pods_';
        }

        $value = null;

        if ( 'transient' == $cache_mode )
            $value = get_transient( $group_key . $key );
        elseif ( 'site-transient' == $cache_mode )
            $value = get_site_transient( $group_key . $key );
        elseif ( 'cache' == $cache_mode && $object_cache )
            $value = wp_cache_get( $key, ( empty( $group ) ? 'pods_view' : $group ) );

        $value = apply_filters( 'pods_view_get_' . $cache_mode, $value, $original_key, $group );

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
     * @param string $group (optional) Set the group of the value.
     *
     * @return bool|mixed|null|string|void
     *
     * @since 2.0.0
     */
    public static function set ( $key, $value, $expires = 0, $cache_mode = null, $group = '' ) {
        $object_cache = false;

        if ( isset( $GLOBALS[ 'wp_object_cache' ] ) && is_object( $GLOBALS[ 'wp_object_cache' ] ) )
            $object_cache = true;

        if ( (int) $expires < 1 )
            $expires = 0;

        if ( !in_array( $cache_mode, self::$cache_modes ) )
            $cache_mode = 'cache';

        $group_key = '';

        if ( !empty( $group ) )
            $group_key = $group . '_';

        $original_key = $key;

        // Patch for limitations in DB
        if ( 44 < strlen( $group_key . $key ) ) {
            $key = md5( $key );

            if ( empty( $group_key ) )
                $group_key = 'pods_';
        }

        if ( 'transient' == $cache_mode )
            set_transient( $group_key . $key, $value, $expires );
        elseif ( 'site-transient' == $cache_mode )
            set_site_transient( $group_key . $key, $value, $expires );
        elseif ( 'cache' == $cache_mode && $object_cache )
            wp_cache_set( $key, $value, ( empty( $group ) ? 'pods_view' : $group ), $expires );

        do_action( 'pods_view_set_' . $cache_mode, $original_key, $value, $expires, $group );

        return $value;
    }

    /**
     * @static
     *
     * Clear a cached value
     *
     * @param string|bool $key Key for the cache
     * @param string $cache_mode (optional) Decides the caching method to use for the view.
     * @param string $group (optional) Set the group.
     *
     * @return bool
     *
     * @since 2.0.0
     */
    public static function clear ( $key = true, $cache_mode = null, $group = '' ) {
        $object_cache = false;

        if ( isset( $GLOBALS[ 'wp_object_cache' ] ) && is_object( $GLOBALS[ 'wp_object_cache' ] ) )
            $object_cache = true;

        global $wpdb;

        if ( !in_array( $cache_mode, self::$cache_modes ) )
            $cache_mode = 'cache';

        $group_key = '';

        if ( !empty( $group ) )
            $group_key = $group . '_';

        $original_key = $key;

        // Patch for limitations in DB
        if ( 44 < strlen( $group_key . $key ) ) {
            $key = md5( $key );

            if ( empty( $group_key ) )
                $group_key = 'pods_';
        }

        if ( 'transient' == $cache_mode ) {
            if ( true === $key ) {
                $wpdb->query( "DELETE FROM `{$wpdb->options}` WHERE option_name LIKE '_transient_{$group_key}%'" );

                if ( $object_cache )
                    wp_cache_flush();
            }
            else
                delete_transient( $group_key . $key );
        }
        elseif ( 'site-transient' == $cache_mode ) {
            if ( true === $key ) {
                $wpdb->query( "DELETE FROM `{$wpdb->options}` WHERE option_name LIKE '_site_transient_{$group_key}%'" );

                if ( $object_cache )
                    wp_cache_flush();
            }
            else
                delete_site_transient( $group_key . $key );
        }
        elseif ( 'cache' == $cache_mode && $object_cache ) {
            if ( true === $key )
                wp_cache_flush();
            else
                wp_cache_delete( ( empty( $key ) ? 'pods_view' : $key ), 'pods_view' );
        }

        do_action( 'pods_view_clear_' . $cache_mode, $original_key, $group );

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
