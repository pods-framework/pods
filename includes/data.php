<?php
/**
 * @package Pods\Global\Functions\Data
 */
/**
 * Filter input and return sanitized output
 *
 * @param mixed $input The string, array, or object to sanitize
 * @param bool $nested
 *
 * @return array|mixed|object|string|void
 * @since 1.2.0
 */
function pods_sanitize ( $input, $nested = false ) {
    $output = array();

    if ( empty( $input ) )
        $output = $input;
    elseif ( is_object( $input ) ) {
        $input = get_object_vars( $input );

        foreach ( $input as $key => $val ) {
            $output[ pods_sanitize( $key ) ] = pods_sanitize( $val, true );
        }

        $output = (object) $output;
    }
    elseif ( is_array( $input ) ) {
        foreach ( $input as $key => $val ) {
            $output[ pods_sanitize( $key ) ] = pods_sanitize( $val, true );
        }
    }
    else
        $output = esc_sql( $input );

    if ( false === $nested )
        $output = apply_filters( 'pods_sanitize', $output, $input );

    return $output;
}

/**
 * Filter input and return unsanitized output
 *
 * @param mixed $input The string, array, or object to unsanitize
 * @param bool $nested
 *
 * @return array|mixed|object|string|void
 * @since 1.2.0
 */
function pods_unsanitize ( $input, $nested = false ) {
    $output = array();

    if ( empty( $input ) )
        $output = $input;
    elseif ( is_object( $input ) ) {
        $input = get_object_vars( $input );

        foreach ( $input as $key => $val ) {
            $output[ pods_unsanitize( $key ) ] = pods_unsanitize( $val, true );
        }

        $output = (object) $output;
    }
    elseif ( is_array( $input ) ) {
        foreach ( $input as $key => $val ) {
            $output[ pods_unsanitize( $key ) ] = pods_unsanitize( $val, true );
        }
    }
    else
        $output = stripslashes( $input );

    if ( false === $nested )
        $output = apply_filters( 'pods_unsanitize', $output, $input );

    return $output;
}

/**
 * Filter input and return sanitized output
 *
 * @param mixed $input The string, array, or object to sanitize
 * @param string $charlist (optional) List of characters to be stripped from the input.
 * @param string $lr Direction of the trim, can either be 'l' or 'r'.
 *
 * @return array|object|string
 * @since 1.2.0
 */
function pods_trim ( $input, $charlist = null, $lr = null ) {
    $output = array();

    if ( is_object( $input ) ) {
        $input = get_object_vars( $input );

        foreach ( $input as $key => $val ) {
            $output[ pods_sanitize( $key ) ] = pods_trim( $val, $charlist, $lr );
        }

        $output = (object) $output;
    }
    elseif ( is_array( $input ) ) {
        foreach ( $input as $key => $val ) {
            $output[ pods_sanitize( $key ) ] = pods_trim( $val, $charlist, $lr );
        }
    }
    else {
        if ( 'l' == $lr )
            $output = ltrim( $input, $charlist );
        elseif ( 'r' == $lr )
            $output = rtrim( $input, $charlist );
        else
            $output = trim( $input, $charlist );
    }

    return $output;
}

/**
 * Return a variable (if exists)
 *
 * @param mixed $var The variable name or URI segment position
 * @param string $type (optional) get|url|post|request|server|session|cookie|constant|globals|user|option|site-option|transient|site-transient|cache|date|pods
 * @param mixed $default (optional) The default value to set if variable doesn't exist
 * @param mixed $allowed (optional) The value(s) allowed
 * @param bool $strict (optional) Only allow values (must not be empty)
 * @param bool $casting (optional) Whether to cast the value returned like provided in $default
 * @param string $context (optional) All returned values are sanitized unless this is set to 'raw'
 *
 * @return mixed The variable (if exists), or default value
 * @since 1.10.6
 */
function pods_var ( $var = 'last', $type = 'get', $default = null, $allowed = null, $strict = false, $casting = false, $context = 'display' ) {
    $output = null;

    if ( is_array( $type ) )
        $output = isset( $type[ $var ] ) ? $type[ $var ] : $default;
    elseif ( is_object( $type ) )
        $output = isset( $type->$var ) ? $type->$var : $default;
    else {
        $type = strtolower( (string) $type );

        if ( 'get' == $type && isset( $_GET[ $var ] ) )
            $output = stripslashes_deep( $_GET[ $var ] );
        elseif ( in_array( $type, array( 'url', 'uri' ) ) ) {
            $url = parse_url( pods_current_url() );
            $uri = trim( $url[ 'path' ], '/' );
            $uri = array_filter( explode( '/', $uri ) );

            if ( 'first' == $var )
                $var = 0;
            elseif ( 'last' == $var )
                $var = -1;

            if ( is_numeric( $var ) )
                $output = ( $var < 0 ) ? pods_var_raw( count( $uri ) + $var, $uri ) : pods_var_raw( $var, $uri );
        }
        elseif ( 'url-relative' == $type ) {
            $url_raw = pods_current_url();
            $prefix = get_site_url();

            if ( substr( $url_raw, 0, strlen( $prefix ) ) == $prefix )
                $url_raw = substr( $url_raw, strlen( $prefix ) + 1, strlen( $url_raw ) );

            $url = parse_url( $url_raw );
            $uri = trim( $url[ 'path' ], '/' );
            $uri = array_filter( explode( '/', $uri ) );

            if ( 'first' == $var )
                $var = 0;
            elseif ( 'last' == $var )
                $var = -1;

            if ( is_numeric( $var ) )
                $output = ( $var < 0 ) ? pods_var_raw( count( $uri ) + $var, $uri ) : pods_var_raw( $var, $uri );
        }
        elseif ( 'template-url' == $type )
            $output = get_template_directory_uri();
        elseif ( 'stylesheet-url' == $type )
            $output = get_stylesheet_directory_uri();
        elseif ( in_array( $type, array( 'site-url', 'home-url', 'admin-url', 'includes-url', 'content-url', 'plugins-url', 'network-site-url', 'network-home-url', 'network-admin-url', 'user-admin-url' ) ) ) {
            if ( 'site-url' == $type ) {
                $blog_id = $scheme = null;
                $path = '';

                if ( is_array( $var ) ) {
                    if ( isset( $var[ 0 ] ) )
                        $blog_id = $var[ 0 ];
                    elseif ( isset( $var[ 1 ] ) )
                        $path = $var[ 1 ];
                    elseif ( isset( $var[ 2 ] ) )
                        $scheme = $var[ 2 ];
                }
                else
                    $blog_id = $var;

                $output = get_site_url( $blog_id, $path, $scheme );
            }
            elseif ( 'home-url' == $type ) {
                $blog_id = $scheme = null;
                $path = '';

                if ( is_array( $var ) ) {
                    if ( isset( $var[ 0 ] ) )
                        $blog_id = $var[ 0 ];
                    elseif ( isset( $var[ 1 ] ) )
                        $path = $var[ 1 ];
                    elseif ( isset( $var[ 2 ] ) )
                        $scheme = $var[ 2 ];
                }
                else
                    $blog_id = $var;

                $output = get_home_url( $blog_id, $path, $scheme );
            }
            elseif ( 'admin-url' == $type ) {
                $blog_id = $scheme = null;
                $path = '';

                if ( is_array( $var ) ) {
                    if ( isset( $var[ 0 ] ) )
                        $blog_id = $var[ 0 ];
                    elseif ( isset( $var[ 1 ] ) )
                        $path = $var[ 1 ];
                    elseif ( isset( $var[ 2 ] ) )
                        $scheme = $var[ 2 ];
                }
                else
                    $blog_id = $var;

                $output = get_admin_url( $blog_id, $path, $scheme );
            }
            elseif ( 'includes-url' == $type )
                $output = includes_url( $var );
            elseif ( 'content-url' == $type )
                $output = content_url( $var );
            elseif ( 'plugins-url' == $type ) {
                $path = $plugin = '';

                if ( is_array( $var ) ) {
                    if ( isset( $var[ 0 ] ) )
                        $path = $var[ 0 ];
                    elseif ( isset( $var[ 1 ] ) )
                        $plugin = $var[ 1 ];
                }
                else
                    $path = $var;

                $output = plugins_url( $path, $plugin );
            }
            elseif ( 'network-site-url' == $type ) {
                $path = '';
                $scheme = null;

                if ( is_array( $var ) ) {
                    if ( isset( $var[ 0 ] ) )
                        $path = $var[ 0 ];
                    elseif ( isset( $var[ 1 ] ) )
                        $scheme = $var[ 1 ];
                }
                else
                    $path = $var;

                $output = network_site_url( $path, $scheme );
            }
            elseif ( 'network-home-url' == $type ) {
                $path = '';
                $scheme = null;

                if ( is_array( $var ) ) {
                    if ( isset( $var[ 0 ] ) )
                        $path = $var[ 0 ];
                    elseif ( isset( $var[ 1 ] ) )
                        $scheme = $var[ 1 ];
                }
                else
                    $path = $var;

                $output = network_home_url( $path, $scheme );
            }
            elseif ( 'network-admin-url' == $type ) {
                $path = '';
                $scheme = null;

                if ( is_array( $var ) ) {
                    if ( isset( $var[ 0 ] ) )
                        $path = $var[ 0 ];
                    elseif ( isset( $var[ 1 ] ) )
                        $scheme = $var[ 1 ];
                }
                else
                    $path = $var;

                $output = network_admin_url( $path, $scheme );
            }
            elseif ( 'user-admin-url' == $type ) {
                $path = '';
                $scheme = null;

                if ( is_array( $var ) ) {
                    if ( isset( $var[ 0 ] ) )
                        $path = $var[ 0 ];
                    elseif ( isset( $var[ 1 ] ) )
                        $scheme = $var[ 1 ];
                }
                else
                    $path = $var;

                $output = user_admin_url( $path, $scheme );
            }
        }
        elseif ( 'post' == $type && isset( $_POST[ $var ] ) )
            $output = stripslashes_deep( $_POST[ $var ] );
        elseif ( 'request' == $type && isset( $_REQUEST[ $var ] ) )
            $output = stripslashes_deep( $_REQUEST[ $var ] );
        elseif ( 'server' == $type ) {
            if ( isset( $_SERVER[ $var ] ) )
                $output = stripslashes_deep( $_SERVER[ $var ] );
            elseif ( isset( $_SERVER[ strtoupper( $var ) ] ) )
                $output = stripslashes_deep( $_SERVER[ strtoupper( $var ) ] );
        }
        elseif ( 'session' == $type && isset( $_SESSION[ $var ] ) )
            $output = $_SESSION[ $var ];
        elseif ( in_array( $type, array( 'global', 'globals' ) ) && isset( $GLOBALS[ $var ] ) )
            $output = $GLOBALS[ $var ];
        elseif ( 'cookie' == $type && isset( $_COOKIE[ $var ] ) )
            $output = stripslashes_deep( $_COOKIE[ $var ] );
        elseif ( 'constant' == $type && defined( $var ) )
            $output = constant( $var );
        elseif ( 'user' == $type && is_user_logged_in() ) {
            $user = get_userdata( get_current_user_id() );

            if ( isset( $user->{$var} ) )
                $value = $user->{$var};
            else
                $value = get_user_meta( $user->ID, $var );

            if ( is_array( $value ) && !empty( $value ) )
                $output = $value;
            elseif ( !is_array( $value ) && 0 < strlen( $value ) )
                $output = $value;
        }
        elseif ( 'option' == $type )
            $output = get_option( $var, $default );
        elseif ( 'site-option' == $type )
            $output = get_site_option( $var, $default );
        elseif ( 'transient' == $type )
            $output = get_transient( $var );
        elseif ( 'site-transient' == $type )
            $output = get_site_transient( $var );
        elseif ( 'cache' == $type && isset( $GLOBALS[ 'wp_object_cache' ] ) && is_object( $GLOBALS[ 'wp_object_cache' ] ) ) {
            $group = 'default';
            $force = false;

            if ( is_array( $var ) ) {
                if ( isset( $var[ 1 ] ) )
                    $group = $var[ 1 ];

                if ( isset( $var[ 2 ] ) )
                    $force = $var[ 2 ];

                if ( isset( $var[ 0 ] ) )
                    $var = $var[ 0 ];
            }

            $output = wp_cache_get( $var, $group, $force );
        }
        elseif ( 'date' == $type ) {
            $var = explode( '|', $var );

            if ( !empty( $var ) )
                $output = date_i18n( $var[ 0 ], ( isset( $var[ 1 ] ) ? strtotime( $var[ 1 ] ) : false ) );
        }
        elseif ( 'pods' == $type ) {
            if ( isset( $GLOBALS[ 'pods' ] ) && 'Pods' == get_class( $GLOBALS[ 'pods' ] ) ) {
                $output = $GLOBALS[ 'pods' ]->field( $var );

                if ( is_array( $output ) )
                    $output = pods_serial_comma( $output, $var, $GLOBALS[ 'pods' ]->fields );
            }
        }
        else
            $output = apply_filters( 'pods_var_' . $type, $default, $var, $allowed, $strict, $casting, $context );
    }

    if ( null !== $allowed ) {
        if ( is_array( $allowed ) ) {
            if ( !in_array( $output, $allowed ) )
                $output = $default;
        }
        elseif ( $allowed !== $output )
            $output = $default;
    }

    if ( true === $strict ) {
        if ( empty( $output ) )
            $output = $default;
        elseif ( true === $casting )
            $output = pods_cast( $output, $default );
    }

    if ( 'raw' != $context )
        $output = pods_sanitize( $output );

    return $output;
}

/**
 * Return a variable's raw value (if exists)
 *
 * @param mixed $var The variable name or URI segment position
 * @param string $type (optional) get|url|post|request|server|session|cookie|constant|user|option|site-option|transient|site-transient|cache
 * @param mixed $default (optional) The default value to set if variable doesn't exist
 * @param mixed $allowed (optional) The value(s) allowed
 * @param bool $strict (optional) Only allow values (must not be empty)
 * @param bool $casting (optional) Whether to cast the value returned like provided in $default
 *
 * @return mixed The variable (if exists), or default value
 * @since 2.0
 */
function pods_var_raw ( $var = 'last', $type = 'get', $default = null, $allowed = null, $strict = false, $casting = false ) {
    return pods_var( $var, $type, $default, $allowed, $strict, $casting, 'raw' );
}

/**
 * Cast a variable as a specific type
 *
 * @param $var
 * @param null $default
 *
 * @return bool
 *
 * @since 2.0
 */
function pods_cast ( $var, $default = null ) {
    if ( is_object( $var ) && is_array( $default ) )
        $var = get_object_vars( $var );
    else
        settype( $var, gettype( $default ) );

    return $var;
}

/**
 * Set a variable
 *
 * @param mixed $value The value to be set
 * @param mixed $key The variable name or URI segment position
 * @param string $type (optional) "url", "get", "post", "request", "server", "session", "cookie", "constant", or "user"
 *
 * @return mixed $value (if set), $type (if $type is array or object), or $url (if $type is 'url')
 * @since 1.10.6
 */
function pods_var_set ( $value, $key = 'last', $type = 'url' ) {
    $type = strtolower( $type );
    $ret = false;

    if ( is_array( $type ) ) {
        $type[ $key ] = $value;
        $ret = $type;
    }
    elseif ( is_object( $type ) ) {
        $type->$key = $value;
        $ret = $type;
    }
    elseif ( 'url' == $type ) {
        $url = parse_url( pods_current_url() );
        $uri = trim( $url[ 'path' ], '/' );
        $uri = array_filter( explode( '/', $uri ) );

        if ( 'first' == $key )
            $key = 0;
        elseif ( 'last' == $key )
            $key = -1;

        if ( is_numeric( $key ) ) {
            if ( $key < 0 )
                $uri[ count( $uri ) + $key ] = $value;
            else
                $uri[ $key ] = $value;
        }

        $url[ 'path' ] = '/' . implode( '/', $uri ) . '/';
        $url[ 'path' ] = trim( $url[ 'path' ], '/' );

        $ret = http_build_url( $url );
    }
    elseif ( 'get' == $type )
        $ret = $_GET[ $key ] = $value;
    elseif ( 'post' == $type )
        $ret = $_POST[ $key ] = $value;
    elseif ( 'request' == $type )
        $ret = $_REQUEST[ $key ] = $value;
    elseif ( 'server' == $type )
        $ret = $_SERVER[ $key ] = $value;
    elseif ( 'session' == $type )
        $ret = $_SESSION[ $key ] = $value;
    elseif ( 'cookie' == $type )
        $ret = $_COOKIE[ $key ] = $value;
    elseif ( 'constant' == $type && !defined( $key ) ) {
        define( $key, $value );

        $ret = constant( $key );
    }
    elseif ( 'user' == $type && is_user_logged_in() ) {
        global $user_ID;

        get_currentuserinfo();

        update_user_meta( $user_ID, $key, $value );

        $ret = $value;
    }

    return apply_filters( 'pods_var_set', $ret, $value, $key, $type );
}

/**
 * Create a new URL off of the current one, with updated parameters
 *
 * @param array $array Parameters to be set (empty will remove it)
 * @param array $allowed Parameters to keep (if empty, all are kept)
 * @param array $excluded Parameters to always remove
 * @param string $url URL to base update off of
 *
 * @return mixed
 *
 * @since 2.0
 */
function pods_var_update ( $array = null, $allowed = null, $excluded = null, $url = null ) {
    $array = (array) $array;
    $allowed = (array) $allowed;
    $excluded = (array) $excluded;

    if ( empty( $url ) )
        $url = $_SERVER[ 'REQUEST_URI' ];

    if ( !isset( $_GET ) )
        $get = array();
    else
        $get = $_GET;

    $get = pods_unsanitize( $get );

    foreach ( $get as $key => $val ) {
        if ( is_array( $val ) && empty( $val ) )
            unset( $get[ $key ] );
        elseif ( !is_array( $val ) && strlen( $val ) < 1 )
            unset( $get[ $key ] );
        elseif ( !empty( $allowed ) ) {
            $allow_it = false;

            foreach ( $allowed as $allow ) {
                if ( $allow == $key )
                    $allow_it = true;
                elseif ( false !== strpos( $allow, '*' ) && 0 === strpos( $key, trim( $allow, '*' ) ) )
                    $allow_it = true;
            }

            if ( !$allow_it )
                unset( $get[ $key ] );
        }
    }

    if ( !empty( $excluded ) ) {
        foreach ( $excluded as $exclusion ) {
            if ( isset( $get[ $exclusion ] ) && !in_array( $exclusion, $allowed ) )
                unset( $get[ $exclusion ] );
        }
    }

    if ( !empty( $array ) ) {
        foreach ( $array as $key => $val ) {
            if ( null !== $val || false === strpos( $key, '*' ) ) {
                if ( is_array( $val ) && !empty( $val ) )
                    $get[ $key ] = $val;
                elseif ( !is_array( $val ) && 0 < strlen( $val ) )
                    $get[ $key ] = $val;
                elseif ( isset( $get[ $key ] ) )
                    unset( $get[ $key ] );
            }
            else {
                $key = str_replace( '*', '', $key );

                foreach ( $get as $k => $v ) {
                    if ( false !== strpos( $k, $key ) )
                        unset( $get[ $k ] );
                }
            }
        }
    }

    $url = current( explode( '#', current( explode( '?', $url ) ) ) );

    if ( !empty( $get ) )
        $url = $url . '?' . http_build_query( $get );

    return $url;
}

/**
 * Create a slug from an input string
 *
 * @param $orig
 *
 * @return string Sanitized slug
 *
 * @since 1.8.9
 */
function pods_create_slug ( $orig, $strict = true ) {
    $str = preg_replace( "/([_ \\/])/", "-", trim( $orig ) );

    if ( $strict )
        $str = preg_replace( "/([^0-9a-z-])/", "", strtolower( $str ) );
    else
        $str = urldecode( sanitize_title( strtolower( $str ) ) );

    $str = preg_replace( "/(-){2,}/", "-", $str );
    $str = trim( $str, '-' );
    $str = apply_filters( 'pods_create_slug', $str, $orig );

    return $str;
}

/**
 * Build a unique slug
 *
 * @param string $slug The slug value
 * @param string $column_name The column name
 * @param string|array $pod The Pod name or array of Pod data
 * @param int $pod_id The Pod ID
 * @param int $id The item ID
 * @param object $obj (optional)
 *
 * @return string The unique slug name
 * @since 1.7.2
 */
function pods_unique_slug ( $slug, $column_name, $pod, $pod_id = 0, $id = 0, $obj = null, $strict = true ) {
    $slug = pods_create_slug( $slug, $strict );

    $pod_data = array();

    if ( is_array( $pod ) ) {
        $pod_data = $pod;
        $pod_id = pods_var( 'id', $pod_data, 0 );
        $pod = pods_var( 'name', $pod_data );
    }

    $pod_id = absint( $pod_id );
    $id = absint( $id );

    if ( empty( $pod_data ) )
        $pod_data = pods_api()->load_pod( array( 'id' => $pod_id, 'name' => $pod ), false );

    if ( empty( $pod_data ) || empty( $pod_id ) || empty( $pod ) )
        return $slug;

    if ( 'table' != $pod_data[ 'storage' ] || !in_array( $pod_data[ 'type' ], array( 'pod', 'table' ) ) )
        return $slug;

    $check_sql = "
        SELECT DISTINCT `t`.`{$column_name}` AS `slug`
        FROM `@wp_pods_{$pod}` AS `t`
        WHERE `t`.`{$column_name}` = %s AND `t`.`id` != %d
        LIMIT 1
    ";

    $slug_check = pods_query( array( $check_sql, $slug, $id ), $obj );

    if ( !empty( $slug_check ) || apply_filters( 'pods_unique_slug_is_bad_flat_slug', false, $slug, $column_name, $pod, $pod_id, $id, $pod_data, $obj ) ) {
        $suffix = 2;

        do {
            $alt_slug = substr( $slug, 0, 200 - ( strlen( $suffix ) + 1 ) ) . "-{$suffix}";

            $slug_check = pods_query( array( $check_sql, $alt_slug, $id ), $obj );

            $suffix++;
        }
        while ( !empty( $slug_check ) || apply_filters( 'pods_unique_slug_is_bad_flat_slug', false, $alt_slug, $column_name, $pod, $pod_id, $id, $pod_data, $obj ) );

        $slug = $alt_slug;
    }

    $slug = apply_filters( 'pods_unique_slug', $slug, $id, $column_name, $pod, $pod_id, $obj );

    return $slug;
}

/**
 * Return a lowercase alphanumeric name (with underscores)
 *
 * @param string $orig Input string to clean
 * @param boolean $lower Force lowercase
 * @param boolean $trim_underscores Whether to trim off underscores
 *
 * @return string Sanitized name
 *
 * @since 1.2.0
 */
function pods_clean_name ( $orig, $lower = true, $trim_underscores = true ) {
    $str = preg_replace( "/([- ])/", "_", trim( $orig ) );

    if ( $lower )
        $str = strtolower( $str );

    $str = preg_replace( "/([^0-9a-zA-Z_])/", "", $str );
    $str = preg_replace( "/(_){2,}/", "_", $str );
    $str = trim( $str );

    if ( $trim_underscores )
        $str = trim( $str, '_' );

    $str = apply_filters( 'pods_clean_name', $str, $orig, $lower );

    return $str;
}

/**
 * Get the Absolute Integer of a value
 *
 * @param string $maybeint
 * @param bool $strict (optional) Check if $maybeint is a integer.
 * @param bool $allow_negative (optional)
 *
 * @return integer
 * @since 2.0
 */
function pods_absint ( $maybeint, $strict = true, $allow_negative = false ) {
    if ( true === $strict && !is_numeric( trim( $maybeint ) ) )
        return 0;

    if ( false !== $allow_negative )
        return intval( $maybeint );

    return absint( $maybeint );
}

/**
 * Functions like str_replace except it will restrict $occurrences
 *
 * @param mixed $find
 * @param mixed $replace
 * @param string $string
 * @param int $occurrences (optional)
 *
 * @return mixed
 * @version 2.0
 */
function pods_str_replace ( $find, $replace, $string, $occurrences = -1 ) {
    if ( is_array( $string ) ) {
        foreach ( $string as $k => $v ) {
            $string[ $k ] = pods_str_replace( $find, $replace, $v, $occurrences );
        }

        return $string;
    }
    elseif ( is_object( $string ) ) {
        $string = get_object_vars( $string );

        foreach ( $string as $k => $v ) {
            $string[ $k ] = pods_str_replace( $find, $replace, $v, $occurrences );
        }

        return (object) $string;
    }

    if ( is_array( $find ) ) {
        foreach ( $find as &$f ) {
            $f = '/' . preg_quote( $f, '/' ) . '/';
        }
    }
    else
        $find = '/' . preg_quote( $find, '/' ) . '/';

    return preg_replace( $find, $replace, $string, $occurrences );
}

/**
 * Evaluate tags like magic tags but through pods_var
 *
 * @param string|array $tags String to be evaluated
 * @param bool $sanitize Whether to sanitize tags
 *
 * @return string
 * @version 2.1
 */
function pods_evaluate_tags ( $tags, $sanitize = false ) {
    if ( is_array( $tags ) ) {
        foreach ( $tags as $k => $tag ) {
            $tags[ $k ] = pods_evaluate_tags( $tag, $sanitize );
        }

        return $tags;
    }

    if ( $sanitize )
        return preg_replace_callback( '/({@(.*?)})/m', 'pods_evaluate_tag_sanitized', (string) $tags );
    else
        return preg_replace_callback( '/({@(.*?)})/m', 'pods_evaluate_tag', (string) $tags );
}

/**
 * Evaluate tag like magic tag but through pods_var_raw and sanitized
 *
 * @param string|array $tag
 *
 * @return string
 * @version 2.1
 * @see pods_evaluate_tag
 */
function pods_evaluate_tag_sanitized ( $tag ) {
    return pods_evaluate_tag( $tag, true );
}

/**
 * Evaluate tag like magic tag but through pods_var_raw
 *
 * @param string|array $tag
 * @param bool $sanitize Whether to sanitize tags
 *
 * @return string
 * @version 2.1
 */
function pods_evaluate_tag ( $tag, $sanitize = false ) {
    // Handle pods_evaluate_tags
    if ( is_array( $tag ) ) {
        if ( !isset( $tag[ 2 ] ) && strlen( trim( $tag[ 2 ] ) ) < 1 )
            return;

        $tag = $tag[ 2 ];
    }

    $tag = trim( $tag, ' {@}' );
    $tag = explode( '.', $tag );

    if ( empty( $tag ) || !isset( $tag[ 0 ] ) || strlen( trim( $tag[ 0 ] ) ) < 1 )
        return;

    // Fix formatting that may be after the first .
    if ( 2 < count( $tag ) ) {
        $first_tag = $tag[ 0 ];
        unset( $tag[ 0 ] );

        $tag = array(
            $first_tag,
            implode( '.', $tag )
        );
    }

    foreach ( $tag as $k => $v ) {
        $tag[ $k ] = trim( $v );
    }

    $value = '';

    if ( 1 == count( $tag ) )
        $value = pods_var_raw( $tag[ 0 ], 'get', '', null, true );
    elseif ( 2 == count( $tag ) )
        $value = pods_var_raw( $tag[ 1 ], $tag[ 0 ], '', null, true );

    $value = apply_filters( 'pods_evaluate_tag', $value, $tag );

    if ( $sanitize )
        $value = pods_sanitize( $value );

    return $value;
}

/**
 * Split an array into human readable text (Item, Item, and Item)
 *
 * @param array $value
 * @param string $field
 * @param array $fields
 * @param string $and
 * @param string $field_index
 *
 * @return string
 *
 * @since 2.0
 */
function pods_serial_comma ( $value, $field = null, $fields = null, $and = null, $field_index = null ) {
    if ( is_object( $value ) )
        $value = get_object_vars( $value );

    $simple = false;

    if ( !empty( $fields ) && is_array( $fields ) && isset( $fields[ $field ] ) ) {
        $field = $fields[ $field ];

        $tableless_field_types = PodsForm::tableless_field_types();
        $simple_tableless_objects = PodsForm::field_method( 'pick', 'simple_objects' );

        if ( !empty( $field ) && is_array( $field ) && in_array( $field[ 'type' ], $tableless_field_types ) ) {
            if ( in_array( $field[ 'type' ], PodsForm::file_field_types() ) ) {
                if ( null === $field_index )
                    $field_index = 'guid';
            }
            elseif ( in_array( $field[ 'pick_object' ], $simple_tableless_objects ) )
                $simple = true;
            else {
                $table = pods_api()->get_table_info( $field[ 'pick_object' ], $field[ 'pick_val' ] );

                if ( !empty( $table ) ) {
                    if ( null === $field_index )
                        $field_index = $table[ 'field_index' ];
                }
            }
        }
    }

    if ( $simple && is_array( $field ) && !is_array( $value ) && !empty( $value ) )
        $value = PodsForm::field_method( 'pick', 'simple_value', $field[ 'name' ], $value, $field );

    if ( !is_array( $value ) )
        return $value;

    if ( null === $and )
        $and = ' ' . __( 'and', 'pods' ) . ' ';

    $last = '';

    $original_value = $value;

    if ( !empty( $value ) )
        $last = array_pop( $value );

    if ( $simple && is_array( $field ) && !is_array( $last ) && !empty( $last ) )
        $last = PodsForm::field_method( 'pick', 'simple_value', $field[ 'name' ], $last, $field );

    if ( is_array( $last ) ) {
        if ( null !== $field_index && isset( $last[ $field_index ] ) )
            $last = $last[ $field_index ];
        elseif ( isset( $last[ 0 ] ) )
            $last = $last[ 0 ];
        elseif ( $simple )
            $last = current( $last );
        else
            $last = '';
    }

    if ( !empty( $value ) ) {
        if ( null !== $field_index && isset( $original_value[ $field_index ] ) )
            return $original_value[ $field_index ];

        if ( null !== $field_index && isset( $value[ $field_index ] ) )
            return $value[ $field_index ];
        elseif ( !isset( $value[ 0 ] ) )
            $value = array( $value );

        foreach ( $value as $k => $v ) {
            if ( $simple && is_array( $field ) && !is_array( $v ) && !empty( $v ) )
                $v = PodsForm::field_method( 'pick', 'simple_value', $field[ 'name' ], $v, $field );

            if ( is_array( $v ) ) {
                if ( null !== $field_index && isset( $v[ $field_index ] ) )
                    $v = $v[ $field_index ];
                elseif ( $simple )
                    $v = trim( implode( ', ', $v ), ', ' );
                else {
                    unset( $value[ $k ] );

                    continue;
                }
            }

            $value[ $k ] = $v;
        }

        if ( 1 == count( $value ) )
            $value = trim( implode( ', ', $value ), ', ' );
        else
            $value = trim( implode( ', ', $value ), ', ' ) . apply_filters( 'pods_serial_comma', ', ', $value, $original_value, $field, $fields, $and, $field_index );

        $value = trim( $value );
        $last = trim( $last );

        if ( 0 < strlen( $value ) && 0 < strlen( $last ) )
            $value = $value . $and . $last;
        elseif ( 0 < strlen( $last ) )
            $value = $last;
        else
            $value = '';
    }
    else
        $value = $last;

    $value = trim( $value, ', ' );

    $value = apply_filters( 'pods_serial_comma_value', $value, $original_value, $field, $fields, $and, $field_index );

    return (string) $value;
}

/**
 * Return a variable if a user is logged in or anonymous, or a specific capability
 *
 * @param mixed $anon Variable to return if user is anonymous (not logged in)
 * @param mixed $user Variable to return if user is logged in
 * @param string|array $capability Capability or array of Capabilities to check to return $user on
 *
 * @return mixed $user Variable to return if user is logged in (if logged in), otherwise $anon
 *
 * @since 2.0.5
 */
function pods_var_user ( $anon = false, $user = true, $capability = null ) {
    $value = $anon;

    if ( is_user_logged_in() ) {
        if ( empty( $capability ) )
            $value = $user;
        else {
            $capabilities = (array) $capability;

            foreach ( $capabilities as $capability ) {
                if ( current_user_can( $capability ) ) {
                    $value = $user;

                    break;
                }
            }
        }
    }

    return $value;
}

/**
 * Take a one-level list of items and make it hierarchical
 *
 * @param array|object $list List of items
 * @param array $args Array of parent, children, and id keys to use
 *
 * @return array|object
 * @since 2.3
 */
function pods_hierarchical_list ( $list, $args = array() ) {
    if ( empty( $args ) || ( !is_object( $list ) && !is_array( $list ) ) )
        return $list;

    $defaults = array(
        'id' => 'id',
        'parent' => 'parent',
        'children' => 'children',
        'orphans' => true,
        'found' => array(),
        'list' => array(),
        'current_depth' => -1
    );

    $args = array_merge( $defaults, (array) $args );

    $list = pods_hierarchical_list_recurse( 0, $list, $args );

    return $list;
}

/**
 * Recurse list of items and make it hierarchical
 *
 * @param int $parent Parent ID
 * @param array|object $list List of items
 * @param array $args Array of parent, children, and id keys to use
 *
 * @return array|object
 * @since 2.3
 */
function pods_hierarchical_list_recurse ( $parent, $list, &$args ) {
    $new = array();

    $object = false;

    if ( is_object( $list ) ) {
        $object = true;
        $list = get_object_vars( $list );
    }

    $args[ 'current_depth' ]++;

    $depth = $args[ 'current_depth' ];

    if ( 0 == $depth )
        $args[ 'list' ] = $list;

    foreach ( $list as $k => $list_item ) {
        if ( is_object( $list_item ) && isset( $list_item->{$args[ 'id' ]} ) ) {
            $list_item->{$args[ 'parent' ]} = (int) pods_var_raw( $args[ 'parent' ], $list_item );

            if ( is_array( $list_item->{$args[ 'parent' ]} ) && isset( $list_item->{$args[ 'parent' ]}[ $args[ 'id' ] ] ) && $parent == $list_item->{$args[ 'parent' ]}[ $args[ 'id' ] ] )
                $list_item->{$args[ 'children' ]} = pods_hierarchical_list_recurse( $list_item->{$args[ 'id' ]}, $list, $args );
            elseif ( $parent == $list_item->{$args[ 'parent' ]} || ( 0 == $depth && $parent == $list_item->{$args[ 'id' ]} ) )
                $list_item->{$args[ 'children' ]} = pods_hierarchical_list_recurse( $list_item->{$args[ 'id' ]}, $list, $args );
            else
                continue;

            $args[ 'found' ][ $k ] = $list_item;
        }
        elseif ( is_array( $list_item ) && isset( $list_item[ $args[ 'id' ] ] ) ) {
            $list_item[ $args[ 'parent' ] ] = (int) pods_var_raw( $args[ 'parent' ], $list_item );

            if ( is_array( $list_item[ $args[ 'parent' ] ] ) && isset( $list_item[ $args[ 'parent' ] ][ $args[ 'id' ] ] ) && $parent == $list_item[ $args[ 'parent' ] ][ $args[ 'id' ] ] )
                $list_item[ $args[ 'children' ] ] = pods_hierarchical_list_recurse( $list_item[ $args[ 'id' ] ], $list, $args );
            elseif ( $parent == $list_item[ $args[ 'parent' ] ] || ( 0 == $depth && $parent == $list_item[ $args[ 'id' ] ] ) )
                $list_item[ $args[ 'children' ] ] = pods_hierarchical_list_recurse( $list_item[ $args[ 'id' ] ], $list, $args );
            else
                continue;

            $args[ 'found' ][ $k ] = $list_item;
        }
        else
            continue;

        $new[ $k ] = $list_item;

        $args[ 'current_depth' ] = $depth;
    }

    if ( 0 == $depth && empty( $new ) && !empty( $list ) ) {
        $first = current( array_slice( $list, 0, 1 ) );

        $new_parent = 0;

        $args[ 'current_depth' ] = -1;

        if ( is_object( $first ) && isset( $first->{$args[ 'parent' ]} ) )
            $new_parent = (int) $first->{$args[ 'parent' ]};
        elseif ( is_array( $first ) && isset( $first[ $args[ 'parent' ] ] ) )
            $new_parent = (int) $first[ $args[ 'parent' ] ];

        if ( !empty( $new_parent ) )
            $new = pods_hierarchical_list_recurse( $new_parent, $list, $args );
    }

    if ( 0 == $depth ) {
        $orphans = array();

        foreach ( $args[ 'list' ] as $k => $list_item ) {
            if ( !isset( $args[ 'found' ][ $k ] ) )
                $orphans[ $k ] = $list_item;
        }

        if ( !empty( $orphans ) ) {
            foreach ( $orphans as $orphan ) {
                $new[] = $orphan;
            }
        }
    }

    if ( $object )
        $new = (object) $new;

    return $new;
}

/**
 * Take a one-level list of items and make it hierarchical for <select>
 *
 * @param array|object $list List of items
 * @param array $args Array of index, parent, children, id, and prefix keys to use
 * @param string $children_key Key to recurse children into
 *
 * @return array|object
 * @since 2.3
 */
function pods_hierarchical_select ( $list, $args = array() ) {
    $object = false;

    if ( is_object( $list ) ) {
        $object = true;
        $list = get_object_vars( $list );
    }

    $list = pods_hierarchical_list( $list, $args );

    $defaults = array(
        'index' => 'name',
        'children' => 'children',
        'prefix' => '&nbsp;&nbsp;&nbsp;'
    );

    $args = array_merge( $defaults, (array) $args );

    $list = pods_hierarchical_select_recurse( $list, $args, 0 );

    if ( $object )
        $list = (object) $list;

    return $list;
}

/**
 * Recurse list of hierarchical data
 *
 * @param array|object $list List of items
 * @param array $args Array of children and prefix keys to use
 * @param string $children_key Key to recurse children into
 *
 * @see pods_hierarchical_select
 * @return array
 * @since 2.3
 */
function pods_hierarchical_select_recurse ( $items, $args, $depth = 0 ) {
    $data = array();

    foreach ( $items as $k => $v ) {
        $object = false;

        if ( is_object( $v ) ) {
            $object = true;
            $v = get_object_vars( $v );
        }

        if ( isset( $v[ $args[ 'index' ] ] ) )
            $v[ $args[ 'index' ] ] = ( 0 < $depth ? str_repeat( $args[ 'prefix' ], $depth ) : '' ) . $v[ $args[ 'index' ] ];

        $children = array();

        if ( isset( $v[ $args[ 'children' ] ] ) ) {
            if ( !empty( $v[ $args[ 'children' ] ] ) )
                $children = pods_hierarchical_select_recurse( $v[ $args[ 'children' ] ], $args, ( $depth + 1 ) );

            unset( $v[ $args[ 'children' ] ] );
        }

        if ( $object )
            $v = (object) $v;

        $data[ $k ] = $v;

        if ( !empty( $children ) ) {
            foreach ( $children as $ck => $cv ) {
                $data[ $ck ] = $cv;
            }
        }
    }

    return $data;
}

/**
 * Filters a list of objects or arrays, based on a set of key => value arguments.
 *
 * @param array|object $list An array or object, with objects/arrays to filter
 * @param array $args An array of key => value arguments to match against each object
 * @param string $operator The logical operation to perform:
 *    'AND' means all elements from the array must match;
 *    'OR' means only one element needs to match;
 *    'NOT' means no elements may match.
 *   The default is 'AND'.
 *
 * @see wp_list_filter
 * @return array
 * @since 2.3
 */
function pods_list_filter ( $list, $args = array(), $operator = 'AND' ) {
    if ( empty( $args ) )
        return $list;

    $data = $list;

    $object = false;

    if ( is_object( $data ) ) {
        $object = true;
        $data = get_object_vars( $data );
    }

    $operator = strtoupper( $operator );
    $count = count( $args );
    $filtered = array();

    foreach ( $data as $key => $obj ) {
        $to_match = $obj;

        if ( is_object( $to_match ) )
            $to_match = get_object_vars( $to_match );
        elseif ( !is_array( $to_match ) )
            continue;

        $matched = 0;

        foreach ( $args as $m_key => $m_value ) {
            if ( array_key_exists( $m_key, $to_match ) && $m_value == $to_match[ $m_key ] )
                $matched++;
        }

        if ( 'AND' == $operator && $matched == $count )
            $filtered[ $key ] = $obj;
        elseif ( 'OR' == $operator && $matched > 0 )
            $filtered[ $key ] = $obj;
        elseif ( 'NOT' == $operator && 0 == $matched )
            $filtered[ $key ] = $obj;
        else
            continue;
    }

    if ( $object )
        $filtered = (object) $filtered;

    return $filtered;
}