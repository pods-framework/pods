<?php
/**
 * Standardize queries and error reporting
 *
 * @see PodsData::query
 *
 * @param string $query The SQL query
 * @param string $error (optional) The failure message
 * @param string $results_error (optional) Throw an error if a records are found
 * @param string $no_results_error (optional) Throw an error if no records are found
 *
 * @since 2.0.0
 */
function pods_query ( $sql, $error = 'Database Error', $results_error = null, $no_results_error = null ) {
    $podsdata = pods_data();
    global $wpdb;
    $sql = apply_filters( 'pods_query_sql', $sql, $error, $results_error, $no_results_error );
    $sql = str_replace( '@wp_users', $wpdb->users, $sql );
    $sql = str_replace( '@wp_', $wpdb->prefix, $sql );
    $sql = str_replace( '{prefix}', '@wp_', $sql );

    if ( is_array( $error ) ) {
        if ( !is_array( $sql ) )
            $sql = array( $sql, $error );

        $error = 'Database Error';
    }

    return $podsdata->query( $sql, $error, $results_error, $no_results_error );
}

/**
 * Standardize filters / actions
 *
 * @param string $scope Scope of the filter / action (ui for PodsUI, api for PodsAPI, etc..)
 * @param string $name Name of filter / action to run
 * @param mixed $args (optional) Arguments to send to filter / action
 * @param object $obj (optional) Object to reference for filter / action
 *
 * @since 2.0.0
 * @todo Need to figure out how to handle $scope = 'pods' for the Pods class
 */
function pods_do_hook ( $scope, $name, $args = null, &$obj = null ) {
    // Add filter name
    array_unshift( $args, "pods_{$scope}_{$name}" );

    // Add object
    $args[] = $obj;

    // Run apply_filters and give it all the arguments
    $args = call_user_func_array( 'apply_filters', $args );

    return $args;
}

/**
 * Error Handling which throws / displays errors
 *
 * @param string $error The error message to be thrown / displayed
 * @param object / boolean $obj If object, if $obj->display_errors is set, and is set to true: display errors;
 *                              If boolean, and is set to true: display errors
 */
function pods_error ( $error, $obj = null ) {
    $display_errors = false;

    if ( is_object( $obj ) && isset( $obj->display_errors ) && true === $obj->display_errors )
        $display_errors = true;
    elseif ( is_bool( $obj ) && true === $obj )
        $display_errors = true;

    if ( is_object( $error ) && 'Exception' == get_class( $error ) ) {
        $error = $error->getMessage();
        $display_errors = false;
    }

    if ( is_array( $error ) )
        $error = __( 'The following issues occured:', 'pods' ) . "\n<ul><li>" . implode( "</li>\n<li>", $error ) . "</li></ul>";

    // log error in WP
    $log_error = new WP_Error( 'pods-error-' . md5( $error ), $error );

    // throw error as Exception and return false if silent
    if ( false !== $display_errors && !empty( $error ) ) {
        $exception_bypass = apply_filters( 'pods_error_exception', null, $error );

        if ( null !== $exception_bypass )
            return $exception_bypass;

        set_exception_handler( 'pods_error' );

        throw new Exception( $error );
    }

    $die_bypass = apply_filters( 'pods_error_die', null, $error );

    if ( null !== $die_bypass )
        return $die_bypass;

    // die with error
    die( "<e>$error</e>" );
}

/**
 * Debug variable used in pods_debug to count the instances debug is used
 */
global $pods_debug;
$pods_debug = 0;
/**
 * Debugging common issues using this function saves a few lines and is compatible with
 *
 * @param mixed $debug The error message to be thrown / displayed
 * @param boolean $die If set to true, a die() will occur, if set to (int) 2 then a wp_die() will occur
 * @param boolean $identifier If set to true, an identifying # will be output
 */
function pods_debug ( $debug = '_null', $die = false, $prefix = '_null' ) {
    global $pods_debug;
    $pods_debug++;
    ob_start();
    if ( '_null' !== $prefix )
        var_dump( $prefix );
    if ( '_null' !== $debug )
        var_dump( $debug );
    else
        var_dump( 'Pods Debug #' . $pods_debug );
    $debug = '<e><pre>' . ob_get_clean() . '</pre>';
    if ( 2 === $die )
        wp_die( $debug );
    elseif ( true === $die )
        die( $debug );
    echo $debug;
}

/**
 * Marks a function as deprecated and informs when it has been used.
 *
 * There is a hook deprecated_function_run that will be called that can be used
 * to get the backtrace up to what file and function called the deprecated
 * function.
 *
 * The current behavior is to trigger a user error if WP_DEBUG is true.
 *
 * This function is to be used in every function that is deprecated.
 *
 * @uses do_action() Calls 'deprecated_function_run' and passes the function name, what to use instead,
 *   and the version the function was deprecated in.
 * @uses apply_filters() Calls 'deprecated_function_trigger_error' and expects boolean value of true to do
 *   trigger or false to not trigger error.
 *
 * @param string $function The function that was called
 * @param string $version The version of WordPress that deprecated the function
 * @param string $replacement Optional. The function that should have been called
 *
 * @since 2.0.0
 */
function pods_deprecated ( $function, $version, $replacement = null ) {
    if ( !version_compare( $version, PODS_VERSION, '<=' ) )
        return;

    do_action( 'deprecated_function_run', $function, $replacement, $version );

    // Allow plugin to filter the output error trigger
    if ( WP_DEBUG && apply_filters( 'deprecated_function_trigger_error', true ) ) {
        if ( !is_null( $replacement ) )
            trigger_error( sprintf( __( '%1$s is <strong>deprecated</strong> since Pods version %2$s! Use %3$s instead.', 'pods' ), $function, $version, $replacement ) );
        else
            trigger_error( sprintf( __( '%1$s is <strong>deprecated</strong> since Pods version %2$s with no alternative available.', 'pods' ), $function, $version ) );
    }
}

/**
 * Inline help
 *
 * @param string $text Help text
 *
 * @since 2.0.0
 */
function pods_help ( $text ) {
    if ( !wp_script_is( 'pods-qtip', 'registered' ) )
        wp_register_script( 'pods-qtip', PODS_URL . 'ui/js/jquery.qtip.min.js', array( 'jquery' ), '2.0-2011-10-02' );
    if ( !wp_script_is( 'pods-qtip', 'queue' ) && !wp_script_is( 'pods-qtip', 'to_do' ) && !wp_script_is( 'pods-qtip', 'done' ) )
        wp_enqueue_script( 'pods-qtip' );

    if ( !wp_style_is( 'pods-qtip', 'registered' ) )
        wp_register_style( 'pods-qtip', PODS_URL . 'ui/css/jquery.qtip.min.css', array(), '2.0-2011-10-02' );
    if ( !wp_style_is( 'pods-qtip', 'queue' ) && !wp_style_is( 'pods-qtip', 'to_do' ) && !wp_style_is( 'pods-qtip', 'done' ) )
        wp_enqueue_style( 'pods-qtip' );

    if ( !wp_script_is( 'pods-qtip-init', 'registered' ) )
        wp_register_script( 'pods-qtip-init', PODS_URL . 'ui/js/qtip.js', array(
            'jquery',
            'pods-qtip'
        ), PODS_VERSION );
    if ( !wp_script_is( 'pods-qtip-init', 'queue' ) && !wp_script_is( 'pods-qtip-init', 'to_do' ) && !wp_script_is( 'pods-qtip-init', 'done' ) )
        wp_enqueue_script( 'pods-qtip-init' );

    echo '<img src="' . PODS_URL . 'ui/images/help.png" alt="' . esc_attr( $text ) . '" class="pods-icon pods-qtip" />';
}

/**
 * Filter input and return sanitized output
 *
 * @param mixed $input The string, array, or object to sanitize
 *
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
 *
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
 *
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
 * @param string $type (optional) get|url|post|request|server|session|cookie|constant|user|option|site-option|transient|site-transient|cache
 * @param mixed $default (optional) The default value to set if variable doesn't exist
 * @param mixed $allowed (optional) The value(s) allowed
 * @param bool $strict (optional) Only allow values (must not be empty)
 *
 * @return mixed The variable (if exists), or default value
 * @since 1.10.6
 */
function pods_var ( $var = 'last', $type = 'get', $default = null, $allowed = null, $strict = false, $casting = true ) {
    if ( is_array( $type ) )
        $output = isset( $type[ $var ] ) ? $type[ $var ] : $default;
    elseif ( is_object( $type ) )
        $output = isset( $type->$var ) ? $type->$var : $default;
    else {
        $type = strtolower( (string) $type );

        if ( 'get' == $type && isset( $_GET[ $var ] ) )
            $output = stripslashes_deep( $_GET[ $var ] );
        elseif ( in_array( $type, array( 'url', 'uri' ) ) ) {
            $url = parse_url( get_current_url() );
            $uri = trim( $url[ 'path' ], '/' );
            $uri = array_filter( explode( '/', $uri ) );

            if ( 'first' == $var )
                $var = 0;
            elseif ( 'last' == $var )
                $var = -1;

            if ( is_numeric( $var ) )
                $output = ( $var < 0 ) ? $uri[ count( $uri ) + $var ] : $uri[ $var ];
        }
        elseif ( 'post' == $type && isset( $_POST[ $var ] ) )
            $output = stripslashes_deep( $_POST[ $var ] );
        elseif ( 'request' == $type && isset( $_REQUEST[ $var ] ) )
            $output = stripslashes_deep( $_REQUEST[ $var ] );
        elseif ( 'server' == $type && isset( $_SERVER[ $var ] ) )
            $output = stripslashes_deep( $_SERVER[ $var ] );
        elseif ( 'session' == $type && isset( $_SESSION[ $var ] ) )
            $output = $_SESSION[ $var ];
        elseif ( 'cookie' == $type && isset( $_COOKIE[ $var ] ) )
            $output = stripslashes_deep( $_COOKIE[ $var ] );
        elseif ( 'constant' == $type && defined( $var ) )
            $output = constant( $var );
        elseif ( 'user' == $type && is_user_logged_in() ) {
            global $user_ID;

            get_currentuserinfo();

            $value = get_user_meta( $user_ID, $var, true );

            if ( is_array( $value ) || 0 < strlen( $value ) )
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
        elseif ( 'cache' == $type ) {
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
        else
            $output = apply_filters( 'pods_var_' . $type, $default, $var, $allowed, $strict );
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

    return pods_sanitize( $output );
}

/**
 * Cast a variable as a specific type
 *
 * @param $var
 * @param null $default
 *
 * @return bool
 */
function pods_cast ( $var, $default = null ) {
    return settype( $var, gettype( $default ) );
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
        $url = parse_url( get_current_url() );
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
 * @param array $array
 * @param array $allowed
 * @param array $excluded
 * @param string $url
 *
 * @return mixed
 */
function pods_var_update ( $array = null, $allowed = null, $excluded = null, $url = null ) {
    if ( empty( $allowed ) )
        $allowed = array();

    if ( empty( $excluded ) )
        $excluded = array();

    if ( !isset( $_GET ) )
        $get = array();
    else
        $get = $_GET;

    if ( is_array( $array ) ) {
        foreach ( $excluded as $exclusion ) {
            if ( !isset( $array[ $exclusion ] ) && !in_array( $exclusion, $allowed ) )
                unset( $get[ $exclusion ] );

            if ( !isset( $array[ $exclusion ] ) && !in_array( $exclusion, $allowed ) )
                unset( $get[ $exclusion ] );
        }
        foreach ( $array as $key => $val ) {
            if ( 0 < strlen( $val ) )
                $get[ $key ] = $val;
            else
                unset( $get[ $key ] );
        }
    }

    if ( empty( $url ) )
        $url = $_SERVER[ 'REQUEST_URI' ];

    $url = current( explode( '#', current( explode( '?', $url ) ) ) );

    return $url . '?' . http_build_query( $get );
}

/**
 * Create a slug from an input string
 *
 * @param string $str
 *
 * @since 1.8.9
 */
function pods_create_slug ( $orig ) {
    $str = preg_replace( "/([_ ])/", "-", trim( $orig ) );
    $str = preg_replace( "/([^0-9a-z-])/", "", strtolower( $str ) );
    $str = preg_replace( "/(-){2,}/", "-", $str );
    $str = trim( $str, '-' );
    $str = apply_filters( 'pods_create_slug', $str, $orig );

    return $str;
}

/**
 * Return a lowercase alphanumeric name (with underscores)
 *
 * @param string $name Input string to clean
 *
 * @since 1.2.0
 */
function pods_clean_name ( $orig ) {
    $str = preg_replace( "/([- ])/", "_", trim( $orig ) );
    $str = preg_replace( "/([^0-9a-z_])/", "", strtolower( $str ) );
    $str = preg_replace( "/(_){2,}/", "_", $str );
    $str = trim( $str, '_' );
    $str = apply_filters( 'pods_clean_name', $str, $orig );

    return $str;
}

/**
 * Build a unique slug
 *
 * @param string $value The slug value
 * @param string $column_name The column name
 * @param string $pod The datatype name
 * @param int $pod_id The datatype ID
 *
 * @return string The unique slug name
 * @since 1.7.2
 */
function pods_unique_slug ( $slug, $column_name, $pod, $pod_id = 0, &$obj = null ) {
    $slug = pods_create_slug( $slug );

    $id = 0;

    if ( is_object( $pod ) ) {
        if ( isset( $pod->id ) )
            $id = $pod->id;

        if ( isset( $pod->pod_id ) )
            $pod_id = $pod->pod_id;

        if ( isset( $pod->datatype ) )
            $pod = $pod->datatype;
        else
            $pod = '';
    }

    $pod_id = absint( $pod_id );
    $id = absint( $id );

    $check_sql = "
        SELECT DISTINCT `t`.`{$column_name}` AS `slug`
        FROM `@wp_pods_tbl_{$pod}` AS `t`
        WHERE `t`.`{$column_name}` = %s AND `t`.`id` != %d
        LIMIT 1
    ";

    $slug_check = pods_query( array( $check_sql, $slug, $id ), $obj );

    if ( $slug_check || apply_filters( 'pods_unique_slug_is_bad_flat_slug', false, $slug, $id, $column_name, $pod, $pod_id, $obj ) ) {
        $suffix = 2;

        do {
            $alt_slug = substr( $slug, 0, 200 - ( strlen( $suffix ) + 1 ) ) . "-{$suffix}";
            $slug_check = pods_query( array( $check_sql, $alt_slug, $id ), $obj );
            $suffix++;
        }
        while ( $slug_check );

        $slug = $alt_slug;
    }

    $slug = apply_filters( 'pods_unique_slug', $slug, $id, $column_name, $pod, $pod_id, $obj );

    return $slug;
}

/**
 * Get the Absolute Integer of a value
 *
 * @param string $maybeint
 *
 * @return integer
 * @since 2.0.0
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
 * @param int $occurrences
 *
 * @return mixed
 */
function pods_str_replace( $find, $replace, $string, $occurrences = -1 ) {
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
 * Run a Pods Helper
 *
 * @param string $uri The Pod Page URI to check if currently on
 *
 * @return bool
 * @since 1.7.5
 */
function pods_helper ( $helper_name, $value = null, $name = null ) {
    $pod = new Pod();
    return $pod->helper( $helper_name, $value, $name );
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
    global $pod_page_exists;
    if ( false !== $pod_page_exists ) {
        if ( null === $uri || $uri == $pod_page_exists[ 'uri' ] ) {
            return true;
        }
    }
    return false;
}

/**
 * Get current URL of any page
 *
 * @return string
 * @since 1.9.6
 */
if ( !function_exists( 'get_current_url' ) ) {
    function get_current_url () {
        $url = 'http';
        if ( isset( $_SERVER[ 'HTTPS' ] ) && 'off' != $_SERVER[ 'HTTPS' ] && 0 != $_SERVER[ 'HTTPS' ] )
            $url = 'https';
        $url .= '://' . $_SERVER[ 'HTTP_HOST' ] . $_SERVER[ 'REQUEST_URI' ];
        return apply_filters( 'get_current_url', $url );
    }
}

/**
 * Find out if the current page has a valid $pods
 *
 * @param object $object The Pod Object currently checking (optional)
 *
 * @return bool
 * @since 2.0.0
 */
function is_pod ( $object = null ) {
    global $pods;
    if ( is_object( $object ) && isset( $object->pod ) && !empty( $object->pod ) )
        return true;
    if ( is_object( $pods ) && isset( $pods->pod ) && !empty( $pods->pod ) )
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
    $sql = "SELECT * FROM `@wp_posts` WHERE `post_type` = '_pods_object_page' AND `post_title` = %s LIMIT 1";
    $sql = array( $sql, array( $uri ) );

    $result = pods_query( $sql );

    if ( empty( $result ) ) {
        // Find any wildcards
        $sql = "SELECT * FROM `@wp_posts` WHERE `post_type` = '_pods_object_page' AND %s LIKE REPLACE(`post_title`, '*', '%%') AND (LENGTH(`post_title`) - LENGTH(REPLACE(`post_title`, '/', ''))) = %d ORDER BY LENGTH(`post_title`) DESC, `post_title` DESC LIMIT 1";
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
 * See if the current user has a certain privilege
 *
 * @param mixed $priv The privilege name or names (array if multiple)
 * @param string $method The access method ("AND", "OR")
 *
 * @return bool
 * @since 1.2.0
 */
function pods_access ( $privs, $method = 'OR' ) {
    // Convert $privs to an array
    $privs = (array) $privs;

    // Convert $method to uppercase
    $method = strtoupper( $method );

    $check = apply_filters( 'pods_access', null, $privs, $method );
    if ( null !== $check && is_bool( $check ) )
        return $check;

    if ( !is_user_logged_in() )
        return false;

    if ( current_user_can( 'administrator' ) || current_user_can( 'pods_administrator' ) || is_super_admin() )
        return true;

    // Store approved privs when using "AND"
    $approved_privs = array();

    // Loop through the user's roles
    foreach ( $privs as $priv ) {
        if ( 0 === strpos( $priv, 'pod_' ) )
            $priv = pods_str_replace( 'pod_', 'pods_edit_', $priv, 1 );

        if ( 0 === strpos( $priv, 'manage_' ) )
            $priv = pods_str_replace( 'manage_', 'pods_', $priv, 1 );

        if ( current_user_can( $priv ) ) {
            if ( 'OR' == $method )
                return true;

            $approved_privs[ $priv ] = true;
        }
    }
    if ( 'AND' == strtoupper( $method ) ) {
        foreach ( $privs as $priv ) {
            if ( 0 === strpos( $priv, 'pod_' ) )
                $priv = pods_str_replace( 'pod_', 'pods_edit_', $priv, 1 );

            if ( 0 === strpos( $priv, 'manage_' ) )
                $priv = pods_str_replace( 'manage_', 'pods_', $priv, 1 );

            if ( isset( $approved_privs[ $priv ] ) )
                return false;
        }

        return true;
    }

    return false;
}

/**
 * Shortcode support for use anywhere that support WP Shortcodes
 *
 * @param array $tags An associative array of shortcode properties
 * @param string $content A string that represents a template override
 *
 * @since 1.6.7
 */
function pods_shortcode ( $tags, $content = null ) {
    $defaults = array(
        'name' => null,
        'id' => null,
        'slug' => null,
        'select' => null,
        'order' => null,
        'orderby' => null,
        'limit' => null,
        'where' => null,
        'search' => true,
        'page' => null,
        'filters' => false,
        'filters_label' => null,
        'filters_location' => 'before',
        'pagination' => false,
        'pagination_label' => null,
        'pagination_location' => 'after',
        'field' => null,
        'col' => null,
        'template' => null,
        'helper' => null
    );

    $tags = shortcode_atts( $defaults, $tags );
    $tags = apply_filters( 'pods_shortcode', $tags );

    if ( empty( $content ) )
        $content = null;

    if ( empty( $tags[ 'name' ] ) ) {
        return '<e>Please provide a Pod name';
    }

    if ( !empty( $tags[ 'col' ] ) ) {
        $tags[ 'field' ] = $tags[ 'col' ];
        unset( $tags[ 'col' ] );
    }

    if ( empty( $tags[ 'template' ] ) && empty( $tags[ 'field' ] ) ) {
        return '<e>Please provide either a template or field name';
    }

    // id > slug (if both exist)
    $id = empty( $tags[ 'slug' ] ) ? null : $tags[ 'slug' ];

    if ( !empty ( $tags[ 'id' ] ) ) {
        $id = $tags[ 'id' ];

        if ( is_numeric( $id ) )
            $id = absint( $id );
    }

    $pod = pods( $tags[ 'name' ], $id );

    $found = 0;

    if ( empty( $id ) ) {
        $params = array();

        if ( 0 < strlen( $tags[ 'order' ] ) )
            $params[ 'orderby' ] = $tags[ 'order' ];

        if ( 0 < strlen( $tags[ 'orderby' ] ) )
            $params[ 'orderby' ] = $tags[ 'orderby' ];

        if ( !empty( $tags[ 'limit' ] ) )
            $params[ 'limit' ] = $tags[ 'limit' ];

        if ( 0 < strlen( $tags[ 'where' ] ) )
            $params[ 'where' ] = $tags[ 'where' ];

        if ( 0 < strlen( $tags[ 'select' ] ) )
            $params[ 'select' ] = $tags[ 'select' ];

        if ( empty( $tags[ 'search' ] ) )
            $params[ 'search' ] = false;

        if ( 0 < absint( $tags[ 'page' ] ) )
            $params[ 'page' ] = absint( $tags[ 'page' ] );

        $params = apply_filters( 'pods_shortcode_findrecords_params', $params );

        $pod->find( $params );

        $found = $pod->total();
    }
    elseif ( !empty( $tags[ 'field' ] ) ) {
        $val = $pod->field( $tags[ 'field' ] );

        return empty( $tags[ 'helper' ] ) ? $val : $pod->helper( $tags[ 'helper' ], $val );
    }

    ob_start();

    if ( empty( $id ) && false !== $tags[ 'filters' ] && 'before' == $tags[ 'filters_location' ] )
        echo $pod->filters( $tags[ 'filters' ], $tags[ 'filters_label' ] );

    if ( empty( $id ) && 0 < $found && false !== $tags[ 'pagination' ] && 'before' == $tags[ 'pagination_location' ] )
        echo $pod->pagination( $tags[ 'pagination_label' ] );

    echo $pod->template( $tags[ 'template' ], $content );

    if ( empty( $id ) && 0 < $found && false !== $tags[ 'pagination' ] && 'after' == $tags[ 'pagination_location' ] )
        echo $pod->pagination( $tags[ 'pagination_label' ] );

    if ( empty( $id ) && false !== $tags[ 'filters' ] && 'after' == $tags[ 'filters_location' ] )
        echo $pod->filters( $tags[ 'filters' ], $tags[ 'filters_label' ] );

    return ob_get_clean();
}

/**
 * Generate form key - INTERNAL USE
 *
 * @since 1.2.0
 */
function pods_generate_key ( $datatype, $uri_hash, $columns, $form_count = 1 ) {
    $token = wp_create_nonce( 'pods-form-' . $datatype . '-' . (int) $form_count . '-' . $uri_hash . '-' . json_encode( $columns ) );
    $token = apply_filters( 'pods_generate_key', $token, $datatype, $uri_hash, $columns, (int) $form_count );
    $_SESSION[ 'pods_form_' . $token ] = $columns;
    return $token;
}

/**
 * Validate form key - INTERNAL USE
 *
 * @since 1.2.0
 */
function pods_validate_key ( $token, $datatype, $uri_hash, $columns = null, $form_count = 1 ) {
    if ( null === $columns && !empty( $_SESSION ) && isset( $_SESSION[ 'pods_form_' . $token ] ) )
        $columns = $_SESSION[ 'pods_form_' . $token ];
    $success = false;
    if ( false !== wp_verify_nonce( $token, 'pods-form-' . $datatype . '-' . (int) $form_count . '-' . $uri_hash . '-' . json_encode( $columns ) ) )
        $success = $columns;
    return apply_filters( 'pods_validate_key', $success, $token, $datatype, $uri_hash, $columns, (int) $form_count );
}

/**
 * Output Pod Page Content
 */
function pods_content () {
    global $pod_page_exists;

    $content = false;

    do_action( 'pods_content_pre', $pod_page_exists );

    if ( false !== $pod_page_exists ) {
        if ( 0 < strlen( trim( $pod_page_exists[ 'phpcode' ] ) ) )
            $content = $pod_page_exists[ 'phpcode' ];

        ob_start();

        if ( false !== $content ) {
            if ( !defined( 'PODS_DISABLE_EVAL' ) || PODS_DISABLE_EVAL ) {
                pods_deprecated( 'Use WP Page Templates or hook into the pods_content filter instead of using Pod Page PHP code', '2.1.0' );
                eval( "?>$content" );
            }
            else
                echo $content;
        }

        $content = ob_get_clean();

        echo apply_filters( 'pods_content', $content );
    }

    do_action( 'pods_content_post', $pod_page_exists, $content );
}

/**
 * Get a Point value from a Pods Version number
 *
 * @since 1.10.1
 */
function pods_point_to_version ( $point ) {
    $version_tmp = explode( '.', $point );
    $version = '';
    for ( $x = 0; $x < 3; $x++ ) { // 3 points max - MAJOR.MINOR.PATCH
        if ( !isset( $version_tmp[ $x ] ) || strlen( $version_tmp[ $x ] ) < 1 )
            $version_tmp[ $x ] = '000';
        $version_temp = str_split( $version_tmp[ $x ] );
        if ( 3 == count( $version_temp ) )
            $version .= $version_tmp[ $x ];
        elseif ( 2 == count( $version_temp ) )
            $version .= '0' . $version_tmp[ $x ];
        elseif ( 1 == count( $version_temp ) )
            $version .= '00' . $version_tmp[ $x ];
    }
    $version = (int) $version;
    return $version;
}

/**
 * Get a Point value from a Pods Version number
 *
 * @since 1.10
 */
function pods_version_to_point ( $version ) {
    $point_tmp = $version;
    if ( strlen( $point_tmp ) < 9 ) {
        if ( 8 == strlen( $point_tmp ) )
            $point_tmp = '0' . $point_tmp;
        if ( 7 == strlen( $point_tmp ) )
            $point_tmp = '00' . $point_tmp;
        if ( 3 == strlen( $version ) ) // older versions prior to 1.9.9
            return implode( '.', str_split( $version ) );
    }
    $point_tmp = str_split( $point_tmp, 3 );
    $point = array();
    foreach ( $point_tmp as $the_point ) {
        $point[] = (int) $the_point;
    }
    $point = implode( '.', $point );
    return $point;
}

/**
 * Check if Pods is compatible with WP / PHP / MySQL or not
 *
 * @since 1.10
 */
function pods_compatible ( $wp = null, $php = null, $mysql = null ) {
    global $wp_version, $wpdb;
    if ( null === $wp )
        $wp = $wp_version;
    if ( null === $php )
        $php = phpversion();
    if ( null === $mysql )
        $mysql = $wpdb->db_version();
    $compatible = true;
    if ( !version_compare( $wp, PODS_WP_VERSION_MINIMUM, '>=' ) ) {
        $compatible = false;
        add_action( 'admin_notices', 'pods_version_notice_wp' );
        function pods_version_notice_wp () {
?>
    <div class="error fade">
        <p><strong><?php _e( 'NOTICE', 'pods' ); ?>:</strong> Pods <?php echo PODS_VERSION_FULL; ?> <?php _e( 'requires a minimum of', 'pods' ); ?>
            <strong>WordPress <?php echo PODS_WP_VERSION_MINIMUM; ?>+</strong> <?php _e( 'to function. You are currently running', 'pods' ); ?>
            <strong>WordPress <?php echo get_bloginfo( "version" ); ?></strong> - <?php _e( 'Please upgrade your WordPress to continue.', 'pods' ); ?>
        </p>
    </div>
<?php
        }
    }
    if ( !version_compare( $php, PODS_PHP_VERSION_MINIMUM, '>=' ) ) {
        $compatible = false;
        add_action( 'admin_notices', 'pods_version_notice_php' );
        function pods_version_notice_php () {
?>
    <div class="error fade">
        <p><strong><?php _e( 'NOTICE', 'pods' ); ?>:</strong> Pods <?php echo PODS_VERSION_FULL; ?> <?php _e( 'requires a minimum of', 'pods' ); ?>
            <strong>PHP <?php echo PODS_PHP_VERSION_MINIMUM; ?>+</strong> <?php _e( 'to function. You are currently running', 'pods' ); ?>
            <strong>PHP <?php echo phpversion(); ?></strong> - <?php _e( 'Please upgrade (or have your Hosting Provider upgrade it for you) your PHP version to continue.', 'pods' ); ?>
        </p>
    </div>
<?php
        }
    }
    if ( !@version_compare( $mysql, PODS_MYSQL_VERSION_MINIMUM, '>=' ) ) {
        $compatible = false;
        add_action( 'admin_notices', 'pods_version_notice_mysql' );
        function pods_version_notice_mysql () {
            global $wpdb;
            $mysql = $wpdb->db_version();
?>
    <div class="error fade">
        <p><strong><?php _e( 'NOTICE', 'pods' ); ?>:</strong> Pods <?php echo PODS_VERSION_FULL; ?> <?php _e( 'requires a minimum of', 'pods' ); ?>
            <strong>MySQL <?php echo PODS_MYSQL_VERSION_MINIMUM; ?>+</strong> <?php _e( 'to function. You are currently running', 'pods' ); ?>
            <strong>MySQL <?php echo $mysql; ?></strong> - <?php _e( 'Please upgrade (or have your Hosting Provider upgrade it for you) your MySQL version to continue.', 'pods' ); ?>
        </p>
    </div>
<?php
        }
    }
    return $compatible;
}

/**
 * Check if a Function exists or File exists in Theme / Child Theme
 *
 * @since 1.12
 */
function pods_function_or_file ( $function_or_file, $function_name = null, $file_dir = null, $file_name = null ) {
    $found = false;
    $function_or_file = (string) $function_or_file;
    if ( false !== $function_name ) {
        if ( null === $function_name )
            $function_name = $function_or_file;
        $function_name = str_replace( array(
                                          '__',
                                          '__',
                                          '__'
                                      ), '_', preg_replace( '/[^a-z^A-Z^_][^a-z^A-Z^0-9^_]*/', '_', (string) $function_name ) );
        if ( function_exists( 'pods_custom_' . $function_name ) )
            $found = array( 'function' => 'pods_custom_' . $function_name );
        elseif ( function_exists( $function_name ) )
            $found = array( 'function' => $function_name );
    }
    if ( false !== $file_name && false === $found ) {
        if ( null === $file_name )
            $file_name = $function_or_file;
        $file_name = str_replace( array(
                                      '__',
                                      '__',
                                      '__'
                                  ), '_', preg_replace( '/[^a-z^A-Z^0-9^_]*/', '_', (string) $file_name ) ) . '.php';
        $custom_location = apply_filters( 'pods_file_directory', null, $function_or_file, $function_name, $file_dir, $file_name );
        if ( defined( 'PODS_FILE_DIRECTORY' ) && false !== PODS_FILE_DIRECTORY )
            $custom_location = PODS_FILE_DIRECTORY;
        if ( !empty( $custom_location ) && locate_template( trim( $custom_location, '/' ) . '/' . ( !empty( $file_dir ) ? $file_dir . '/' : '' ) . $file_name ) )
            $found = array( 'file' => trim( $custom_location, '/' ) . '/' . ( !empty( $file_dir ) ? $file_dir . '/' : '' ) . $file_name );
        elseif ( locate_template( 'pods/' . ( !empty( $file_dir ) ? $file_dir . '/' : '' ) . $file_name ) )
            $found = array( 'file' => 'pods/' . ( !empty( $file_dir ) ? $file_dir . '/' : '' ) . $file_name );
        elseif ( locate_template( 'pods-' . ( !empty( $file_dir ) ? $file_dir . '-' : '' ) . $file_name ) )
            $found = array( 'file' => 'pods-' . ( !empty( $file_dir ) ? $file_dir . '-' : '' ) . $file_name );
        elseif ( locate_template( 'pods/' . ( !empty( $file_dir ) ? $file_dir . '-' : '' ) . $file_name ) )
            $found = array( 'file' => 'pods/' . ( !empty( $file_dir ) ? $file_dir . '-' : '' ) . $file_name );
    }
    return apply_filters( 'pods_function_or_file', $found, $function_or_file, $function_name, $file_name );
}

/**
 * Include and Init the Pods class
 *
 * @since 2.0.0
 */
function pods_init () {
    require_once( PODS_DIR . 'classes/PodsInit.php' );

    return new PodsInit();
}

/**
 * Include and Init the Pods Components class
 *
 * @since 2.0.0
 */
function pods_components () {
    require_once( PODS_DIR . 'classes/PodsComponents.php' );

    return new PodsComponents();
}

/**
 * Include and Init the Pods class
 *
 * @since 2.0.0
 *
 * $pods = pods('bunny', array('orderby' => 't.name',
 *                             'where' => 't.active=1', 'search' => false));
 */
function pods ( $type = null, $id = null ) {
    require_once( PODS_DIR . 'classes/Pods.php' );

    return new Pods( $type, $id );
}

/**
 * Include and Init the PodsUI class
 *
 * @since 2.0.0
 */
function pods_ui ( $obj = null ) {
    require_once( PODS_DIR . 'classes/PodsUI.php' );

    return new PodsUI( $obj );
}

/**
 * Include and Init the PodsAPI class
 *
 * @since 2.0.0
 */
function pods_api ( $pod = null, $format = 'php' ) {
    require_once( PODS_DIR . 'classes/PodsAPI.php' );

    return new PodsAPI( $pod, $format );
}

/**
 * Include and Init the PodsData class
 *
 * @since 2.0.0
 */
function pods_data ( $pod = null, $id = null ) {
    require_once( PODS_DIR . 'classes/PodsData.php' );

    return new PodsData( $pod, $id );
}

/**
 * Include and Init the PodsFormUI class
 *
 * @since 2.0.0
 */
function pods_form () {
    require_once( PODS_DIR . 'classes/PodsForm.php' );

    return new PodsForm();
}

/**
 * Include and Init the PodsFormUI class
 *
 * @since 2.0.0
 */
function pods_meta () {
    require_once( PODS_DIR . 'classes/PodsMeta.php' );

    return new PodsMeta();
}

/**
 * Include and Init the PodsAdminUI class
 *
 * @since 2.0.0
 */
function pods_admin () {
    require_once( PODS_DIR . 'classes/PodsAdmin.php' );

    return new PodsAdmin();
}

/**
 * Include and Init the PodsMigrate class
 *
 * @since 2.0.0
 */
function pods_migrate ( $type = null, $delimiter = null, $data = null ) {
    require_once( PODS_DIR . 'classes/PodsMigrate.php' );

    return new PodsMigrate( $type, $delimiter, $data );
}

/**
 * Include and Init the PodsArray class
 *
 * @since 2.0.0
 */
function pods_array ( $container ) {
    require_once( PODS_DIR . 'classes/PodsArray.php' );

    return new PodsArray( $container );
}

/**
 * Load a view
 *
 * @since 2.0.0
 */
function pods_view ( $view, $data = null, $expires = 0, $cache_mode = 'cache', $return = false ) {
    require_once( PODS_DIR . 'classes/PodsView.php' );

    $view = PodsView::view( $view, $data, $expires, $cache_mode );

    if ( $return )
        return $view;

    echo $view;
}

/**
 * Set a cached value
 *
 * @since 2.0.0
 */
function pods_cache_set ( $key, $value, $expires = 0, $cache_mode = 'cache' ) {
    require_once( PODS_DIR . 'classes/PodsView.php' );

    return PodsView::set( $key, $value, $expires, $cache_mode );
}

/**
 * Set a cached value
 *
 * @since 2.0.0
 */
function pods_cache_get ( $key, $cache_mode = 'cache' ) {
    require_once( PODS_DIR . 'classes/PodsView.php' );

    return PodsView::get( $key, $cache_mode );
}

/**
 * Clear a cached value
 *
 * @since 2.0.0
 */
function pods_cache_clear ( $key, $cache_mode = 'cache' ) {
    require_once( PODS_DIR . 'classes/PodsView.php' );

    return PodsView::clear( $key, $cache_mode );
}

/**
 * Add a meta group of fields to add/edit forms
 *
 * @param $pod
 * @param $label
 * @param $fields
 */
function pods_group_add ( $pod, $label, $fields ) {
    pods_meta()->group_add( $pod, $label, $fields );
}

/**
 * Check if a plugin is active on non-admin pages (is_plugin_active() only available in admin)
 *
 * @param $plugin
 *
 * @return bool
 */
function pods_is_plugin_active ( $plugin ) {
    if ( function_exists( 'is_plugin_active' ) )
        return is_plugin_active( $plugin );

    $active_plugins = (array) get_option( 'active_plugins', array() );

    if ( in_array( $plugin, $active_plugins ) )
        return true;

    return false;
}

/**
 * Turn off conflicting / recursive actions for an object type that Pods hooks into
 *
 * @param string $object_type
 */
function pods_no_conflict_on ( $object_type = 'post' ) {
    if ( 'post_type' == $object_type )
        $object_type = 'post';

    if ( !empty( PodsInit::$no_conflict ) && isset( PodsInit::$no_conflict[ $object_type ] ) && !empty( PodsInit::$no_conflict[ $object_type ] ) )
        return true;

    if ( !is_object( PodsInit::$meta ) )
        return false;

    $no_conflict = array();

    // Filters = Usually get/update/delete meta functions
    // Actions = Usually insert/update/save/delete object functions
    if ( 'post' == $object_type ) {
        $no_conflict[ 'filter' ] = array(
            array( 'get_post_metadata', array( PodsInit::$meta, 'get_post_meta' ), 10, 4 ),
            array( 'add_post_metadata', array( PodsInit::$meta, 'add_post_meta' ), 10, 5 ),
            array( 'update_post_metadata', array( PodsInit::$meta, 'update_post_meta' ), 10, 5 ),
            array( 'delete_post_metadata', array( PodsInit::$meta, 'delete_post_meta' ), 10, 5 )
        );

        $no_conflict[ 'action' ] = array(
            array( 'save_post', array( PodsInit::$meta, 'save_post' ), 10, 2 )
        );
    }
    elseif ( 'taxonomy' == $object_type ) {
        $no_conflict[ 'filter' ] = array(

        );

        $no_conflict[ 'action' ] = array(
            array( 'edit_term', array( PodsInit::$meta, 'save_taxonomy' ), 10, 3 ),
            array( 'create_term', array( PodsInit::$meta, 'save_taxonomy' ), 10, 3 )
        );
    }
    elseif ( 'media' == $object_type ) {
        $no_conflict[ 'filter' ] = array(
            array( 'wp_update_attachment_metadata', array( PodsInit::$meta, 'save_media' ), 10, 2 )
        );

        $no_conflict[ 'action' ] = array(

        );
    }
    elseif ( 'user' == $object_type ) {
        $no_conflict[ 'filter' ] = array(
            array( 'get_user_metadata', array( PodsInit::$meta, 'get_user_meta' ), 10, 4 ),
            array( 'add_user_metadata', array( PodsInit::$meta, 'add_user_meta' ), 10, 5 ),
            array( 'update_user_metadata', array( PodsInit::$meta, 'update_user_meta' ), 10, 5 ),
            array( 'delete_user_metadata', array( PodsInit::$meta, 'delete_user_meta' ), 10, 5 )
        );

        $no_conflict[ 'action' ] = array(
            array( 'personal_options_update', array( PodsInit::$meta, 'save_user' ) ),
            array( 'edit_user_profile_update', array( PodsInit::$meta, 'save_user' ) )
        );
    }
    elseif ( 'comment' == $object_type ) {
        $no_conflict[ 'filter' ] = array(
            array( 'get_comment_metadata', array( PodsInit::$meta, 'get_comment_meta' ), 10, 4 ),
            array( 'add_comment_metadata', array( PodsInit::$meta, 'add_comment_meta' ), 10, 5 ),
            array( 'update_comment_metadata', array( PodsInit::$meta, 'update_comment_meta' ), 10, 5 ),
            array( 'delete_comment_metadata', array( PodsInit::$meta, 'delete_comment_meta' ), 10, 5 )
        );

        $no_conflict[ 'action' ] = array(
            array( 'wp_insert_comment', array( PodsInit::$meta, 'save_comment' ) ),
            array( 'edit_comment', array( PodsInit::$meta, 'save_comment' ) )
        );
    }

    $conflicted = false;

    foreach ( $no_conflict as $action_filter => $conflicts ) {
        foreach ( $conflicts as $args ) {
            if ( call_user_func_array( 'has_' . $action_filter, $args ) ) {
                call_user_func_array( 'remove_' . $action_filter, $args );

                $conflicted = true;
            }
        }
    }

    if ( $conflicted ) {
        PodsInit::$no_conflict[ $object_type ] = $no_conflict;

        return true;
    }

    return false;
}

/**
 * Turn on actions after running code during pods_conflict
 *
 * @param string $object_type
 */
function pods_no_conflict_off ( $object_type = 'post' ) {
    if ( 'post_type' == $object_type )
        $object_type = 'post';

    if ( empty( PodsInit::$no_conflict ) || !isset( PodsInit::$no_conflict[ $object_type ] ) || empty( PodsInit::$no_conflict[ $object_type ] ) )
        return false;

    if ( !is_object( PodsInit::$meta ) )
        return false;

    $no_conflict = PodsInit::$no_conflict[ $object_type ];

    $conflicted = false;

    foreach ( $no_conflict as $action_filter => $conflicts ) {
        foreach ( $conflicts as $args ) {
            if ( !call_user_func_array( 'has_' . $action_filter, $args ) ) {
                call_user_func_array( 'add_' . $action_filter, $args );

                $conflicted = true;
            }
        }
    }

    if ( $conflicted ) {
        unset( PodsInit::$no_conflict[ $object_type ] );

        return true;
    }

    return false;
}