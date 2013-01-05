<?php
/**
 * @package Pods\Global
 */
/**
 * Standardize queries and error reporting. It replaces @wp_ with $wpdb->prefix.
 *
 * @see PodsData::query
 *
 * @param string $sql SQL Query
 * @param string $error (optional) The failure message
 * @param string $results_error (optional) Throw an error if a records are found
 * @param string $no_results_error (optional) Throw an error if no records are found
 *
 * @internal param string $query The SQL query
 * @return array|bool|mixed|null|void
 * @since 2.0.0
 */
function pods_query ( $sql, $error = 'Database Error', $results_error = null, $no_results_error = null ) {
    $podsdata = pods_data();
    $sql = apply_filters( 'pods_query_sql', $sql, $error, $results_error, $no_results_error );
    $sql = $podsdata->get_sql($sql);

    if ( is_array( $error ) ) {
        if ( !is_array( $sql ) )
            $sql = array( $sql, $error );

        $error = 'Database Error';
    }

    if ( 1 == pods_var( 'pods_debug_sql_all', 'get', 0 ) && is_user_logged_in() && ( is_super_admin() || current_user_can( 'delete_users' ) || current_user_can( 'pods' ) ) ) {
        $debug_sql = $sql;

        echo '<textarea cols="100" rows="24">';

        if ( is_array( $debug_sql ) )
            print_r( $debug_sql );
        else
            echo $debug_sql;

        echo '</textarea>';
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
 * @return mixed
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
 * Message / Notice handling for Admin UI
 *
 * @param string $message The notice / error message shown
 * @param string $type Message type
 */
function pods_message ( $message, $type = null ) {
    if ( empty( $type ) || !in_array( $type, array( 'notice', 'error' ) ) )
        $type = 'notice';

    $class = '';

    if ( 'notice' == $type )
        $class = 'updated';
    elseif ( 'error' == $type )
        $class = 'error';

    echo '<div id="message" class="' . $class . ' fade"><p>' . $message . '</p></div>';
}

/**
 * Error Handling which throws / displays errors
 *
 * @param string $error The error message to be thrown / displayed
 * @param object / boolean $obj If object, if $obj->display_errors is set, and is set to true: display errors;
 *                              If boolean, and is set to true: display errors
 * @throws Exception
 * @return mixed|void
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

    if ( is_array( $error ) ) {
        if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
            $error = __( 'The following issues occured:', 'pods' ) . "\n\n- " . implode( "\n- ", $error );
        else
            $error = __( 'The following issues occured:', 'pods' ) . "\n<ul><li>" . implode( "</li>\n<li>", $error ) . "</li></ul>";
    }
    elseif ( is_object( $error ) )
        $error = __( 'An unknown error has occurred', 'pods' );

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
    if ( !defined( 'DOING_AJAX' ) && !headers_sent() && ( is_admin() || false !== strpos( $_SERVER[ 'REQUEST_URI' ], 'wp-comments-post.php' ) ) )
        wp_die( $error );
    else
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
 * @param string $prefix
 * @internal param bool $identifier If set to true, an identifying # will be output
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

    $debug = ob_get_clean();

    if ( !defined( 'DOING_AJAX' ) || !DOING_AJAX )
        $debug = esc_html( $debug );

    if ( false === strpos( $debug, "<pre class='xdebug-var-dump' dir='ltr'>" ) )
        $debug = '<e><pre>' . $debug . '</pre>';
    else
        $debug = '<e>' . $debug;

    if ( 2 === $die )
        wp_die( $debug );
    elseif ( true === $die )
        die( $debug );

    echo $debug;
}

/**
 * Output a message in the WP Dashboard UI
 *
 * @param string $msg
 * @param bool $error Whether or not it is an error message
 *
 * @return bool
 *
 * @since 1.12
 */
function pods_ui_message ( $msg, $error = false ) {
    echo '<div id="message" class="' . ( $error ? 'error' : 'updated' ) . ' fade"><p>' . $msg . '</p></div>';

    return !$error;
}

/**
 * Output an error in the WP Dashboard UI
 *
 * @param string $msg
 *
 * @return bool
 *
 * @since 1.12
 */
function pods_ui_error ( $msg ) {
    return pods_ui_message( $msg, true );
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
    if ( !version_compare( $version, PODS_VERSION, '<=' ) && !version_compare( $version . '-a-0', PODS_VERSION, '<=' ) )
        return;

    do_action( 'deprecated_function_run', $function, $replacement, $version );

    // Allow plugin to filter the output error trigger
    if ( WP_DEBUG && apply_filters( 'deprecated_function_trigger_error', true ) ) {
        if ( !is_null( $replacement ) )
            $error = __( '%1$s has been <strong>deprecated</strong> since Pods version %2$s! Use %3$s instead.', 'pods' );
        else
            $error = __( '%1$s has been <strong>deprecated</strong> since Pods version %2$s with no alternative available.', 'pods' );

        trigger_error( sprintf( $error, $function, $version, $replacement ) );
    }
}

/**
 * Inline help
 *
 * @param string $text Help text
 * @param string $url Documentation URL
 *
 * @since 2.0.0
 */
function pods_help ( $text, $url = null ) {
    if ( !wp_script_is( 'jquery-qtip', 'registered' ) )
        wp_register_script( 'jquery-qtip', PODS_URL . 'ui/js/jquery.qtip.min.js', array( 'jquery' ), '2.0-2011-10-02' );
    if ( !wp_script_is( 'jquery-qtip', 'queue' ) && !wp_script_is( 'jquery-qtip', 'to_do' ) && !wp_script_is( 'jquery-qtip', 'done' ) )
        wp_enqueue_script( 'jquery-qtip' );

    if ( !wp_style_is( 'pods-qtip', 'registered' ) )
        wp_register_style( 'pods-qtip', PODS_URL . 'ui/css/jquery.qtip.min.css', array(), '2.0-2011-10-02' );
    if ( !wp_style_is( 'pods-qtip', 'queue' ) && !wp_style_is( 'pods-qtip', 'to_do' ) && !wp_style_is( 'pods-qtip', 'done' ) )
        wp_enqueue_style( 'pods-qtip' );

    if ( !wp_script_is( 'pods-qtip-init', 'registered' ) )
        wp_register_script( 'pods-qtip-init', PODS_URL . 'ui/js/qtip.js', array(
            'jquery',
            'jquery-qtip'
        ), PODS_VERSION );
    if ( !wp_script_is( 'pods-qtip-init', 'queue' ) && !wp_script_is( 'pods-qtip-init', 'to_do' ) && !wp_script_is( 'pods-qtip-init', 'done' ) )
        wp_enqueue_script( 'pods-qtip-init' );

    if ( is_array( $text ) ) {
        if ( isset( $text[ 1 ] ) )
            $url = $text[ 1 ];

        $text = $text[ 0 ];
    }

    if ( 'help' == $text )
        return;

    if ( 0 < strlen( $url ) )
        $text .= '<br /><br /><a href="' . $url . '" target="_blank">' . __( 'Find out more', 'pods' ) . ' &raquo;</a>';

    echo '<img src="' . PODS_URL . 'ui/images/help.png" alt="' . esc_attr( $text ) . '" class="pods-icon pods-qtip" />';
}

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
 * @param string $type (optional) get|url|post|request|server|session|cookie|constant|global|user|option|site-option|transient|site-transient|cache|date
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
                $output = ( $var < 0 ) ? pods_var_raw( count( $uri ) + $var, $uri ) : pods_var_raw( $var, $uri );
        }
        elseif ( 'url-relative' == $type ) {
            $url_raw = get_current_url();
            $prefix = get_bloginfo( 'wpurl' );

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
 * @since 2.0.0
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
 * Create a new URL off of the current one, with updated parameters
 *
 * @param array $array Parameters to be set (empty will remove it)
 * @param array $allowed Parameters to keep (if empty, all are kept)
 * @param array $excluded Parameters to always remove
 * @param string $url URL to base update off of
 *
 * @return mixed
 *
 * @since 2.0.0
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
        elseif ( !empty( $allowed ) && !in_array( $key, $allowed ) )
            unset( $get[ $key ] );
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

    return $url . '?' . http_build_query( $get );
}

/**
 * Create a slug from an input string
 *
 * @param $orig
 *
 * @return mixed|void
 *
 * @since 1.8.9
 */
function pods_create_slug ( $orig, $strict = true ) {
    $str = preg_replace( "/([_ ])/", "-", trim( $orig ) );

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
 * Return a lowercase alphanumeric name (with underscores)
 *
 * @param string $orig Input string to clean
 * @param boolean $lower Force lowercase
 * @param boolean $trim_underscores Whether to trim off underscores
 *
 * @return mixed|void
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
 * Get the Absolute Integer of a value
 *
 * @param string $maybeint
 * @param bool $strict (optional) Check if $maybeint is a integer.
 * @param bool $allow_negative (optional)
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
 * @param int $occurrences (optional)
 *
 * @return mixed
 * @version 2.0.0
 */
function pods_str_replace ( $find, $replace, $string, $occurrences = -1 ) {
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
 * @version 2.1.0
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
 * @version 2.1.0
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
 * @version 2.1.0
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
 * Check whether or not WordPress is a specific version minimum and/or maximum
 *
 * @param string $minimum_version Minimum WordPress version
 * @param string $comparison Comparison operator
 * @param string $maximum_version Maximum WordPress version
 *
 * @return bool
 */
function pods_wp_version ( $minimum_version, $comparison = '<=', $maximum_version = null ) {
    global $wp_version;

    if ( !empty( $minimum_version ) && !version_compare( $minimum_version, $wp_version, $comparison ) )
        return false;

    if ( !empty( $maximum_version ) && !version_compare( $wp_version, $maximum_version, $comparison ) )
        return false;

    return true;
}

/**
 * Run a Pods Helper
 *
 * @param string $helper_name Helper Name
 * @param string $value Value to run Helper on
 * @param string $name Field name.
 *
 * @return bool
 * @since 1.7.5
 */
function pods_helper ( $helper_name, $value = null, $name = null ) {
    return pods()->helper( $helper_name, $value, $name );
}

/**
 * Get current URL of any page
 *
 * @return string
 * @since 1.9.6
 */
if ( !function_exists( 'get_current_url' ) ) {
    /**
     * @return mixed|void
     */
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
 * See if the current user has a certain privilege
 *
 * @param mixed $privs The privilege name or names (array if multiple)
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

    if ( is_super_admin() || current_user_can( 'delete_users' ) || current_user_can( 'pods' ) || current_user_can( 'pods_content' ) )
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
 * @return string
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
        'helper' => null,
        'form' => null,
        'fields' => null,
        'label' => null,
        'thank_you' => null
    );

    if ( !empty( $tags ) )
        $tags = array_merge( $defaults, $tags );
    else
        $tags = $defaults;

    $tags = apply_filters( 'pods_shortcode', $tags );

    if ( empty( $content ) )
        $content = null;

    if ( empty( $tags[ 'name' ] ) ) {
        if ( in_the_loop() || is_singular() ) {
            $pod = pods( get_post_type(), get_the_ID(), false );

            if ( !empty( $pod ) ) {
                $tags[ 'name' ] = get_post_type();
                $id = $tags[ 'id' ] = get_the_ID();
            }
        }

        if ( empty( $tags[ 'name' ] ) )
            return '<p>Please provide a Pod name</p>';
    }

    if ( !empty( $tags[ 'col' ] ) ) {
        $tags[ 'field' ] = $tags[ 'col' ];

        unset( $tags[ 'col' ] );
    }

    if ( !empty( $tags[ 'order' ] ) ) {
        $tags[ 'orderby' ] = $tags[ 'order' ];

        unset( $tags[ 'order' ] );
    }

    if ( empty( $content ) && empty( $tags[ 'template' ] ) && empty( $tags[ 'field' ] ) && empty( $tags[ 'form' ] ) ) {
        return '<p>Please provide either a template or field name</p>';
    }

    if ( !isset( $id ) ) {
        // id > slug (if both exist)
        $id = empty( $tags[ 'slug' ] ) ? null : $tags[ 'slug' ];

        if ( !empty ( $tags[ 'id' ] ) ) {
            $id = $tags[ 'id' ];

            if ( is_numeric( $id ) )
                $id = absint( $id );
        }
    }

    if ( !isset( $pod ) )
        $pod = pods( $tags[ 'name' ], $id );

    $found = 0;

    if ( !empty( $tags[ 'form' ] ) )
        return $pod->form( $tags[ 'fields' ], $tags[ 'label' ], $tags[ 'thank_you' ] );
    elseif ( empty( $id ) ) {
        $params = array();

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
        if ( empty( $tags[ 'helper' ] ) )
            $val = $pod->display( $tags[ 'field' ] );
        else
            $val = $pod->helper( $tags[ 'helper' ], $pod->field( $tags[ 'field' ] ), $tags[ 'field' ] );

        return $val;
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
 * Split an array into human readable text (Item, Item, and Item)
 *
 * @param array $value
 * @param string $field
 * @param array $fields
 *
 * @return string
 */
function pods_serial_comma ( $value, $field = null, $fields = null ) {
    if ( is_object( $value ) )
        $value = get_object_vars( $value );

    $field_index = null;

    $simple = false;

    if ( !empty( $fields ) && is_array( $fields ) && isset( $fields[ $field ] ) ) {
        $field = $fields[ $field ];

        $tableless_field_types = apply_filters( 'pods_tableless_field_types', array( 'pick', 'file', 'avatar' ) );

        if ( !empty( $field ) && is_array( $field ) && in_array( $field[ 'type' ], $tableless_field_types ) ) {
            if ( in_array( $field[ 'type' ], apply_filters( 'pods_file_field_types', array( 'file', 'avatar' ) ) ) )
                $field_index = 'guid';
            elseif ( 'custom-simple' == $field[ 'pick_object' ] )
                $simple = true;
            else {
                $table = pods_api()->get_table_info( $field[ 'pick_object' ], $field[ 'pick_val' ] );

                if ( !empty( $table ) )
                    $field_index = $table[ 'field_index' ];
            }
        }
    }

    if ( $simple && is_array( $field ) && !is_array( $value ) && !empty( $value ) )
        $value = PodsForm::field_method( 'pick', 'simple_value', $value, $field );

    if ( !is_array( $value ) )
        return $value;

    $and = ' ' . __( 'and', 'pods' ) . ' ';

    $last = '';

		$original_value = $value;
    if ( !empty( $value ) )
        $last = array_pop( $value );

    if ( $simple && is_array( $field ) && !is_array( $last ) && !empty( $last ) )
        $last = PodsForm::field_method( 'pick', 'simple_value', $last, $field );

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

        if ( 1 == count( $value ) ) {
            if ( isset( $value[ 0 ] ) )
                $value = $value[ 0 ];

            if ( $simple && is_array( $field ) && !is_array( $value ) && !empty( $value ) )
                $value = PodsForm::field_method( 'pick', 'simple_value', $value, $field );

            if ( is_array( $value ) ) {
                if ( null !== $field_index && isset( $value[ $field_index ] ) )
                    $value = $value[ $field_index ];
                elseif ( $simple )
                    $value = implode( ', ', $value );
                else
                    $value = '';
            }

            $value = trim( $value, ', ' ) . ', ';
        }
        else {
            if ( null !== $field_index && isset( $value[ $field_index ] ) )
                return $value[ $field_index ];
            elseif ( !isset( $value[ 0 ] ) )
                $value = array( $value );

            foreach ( $value as $k => &$v ) {
                if ( $simple && is_array( $field ) && !is_array( $v ) && !empty( $v ) )
                    $v = PodsForm::field_method( 'pick', 'simple_value', $v, $field );

                if ( is_array( $v ) ) {
                    if ( null !== $field_index && isset( $v[ $field_index ] ) )
                        $v = $v[ $field_index ];
                    elseif ( $simple )
                        $v = trim( implode( ', ', $v ), ', ' );
                    else
                        unset( $value[ $k ] );
                }
            }

            $value = trim( implode( ', ', $value ), ', ' ) . ', ';
        }

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

    return (string) $value;
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
 * @param string $wp (optional) Wordpress version
 * @param string $php (optional) PHP Version
 * @param string $mysql (optional) MySQL Version
 *
 * @return bool
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
 * @param string $function_or_file Function or file name to look for.
 * @param string $function_name (optional) Function name to look for.
 * @param string $file_dir (optional) Drectory to look into
 * @param string $file_name (optional) Filename to look for
 *
 * @return mixed
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
 * Redirects to another page.
 *
 * @param string $location The path to redirect to
 * @param int $status Status code to use
 *
 * @since 2.0.0
 */
function pods_redirect ( $location, $status = 302 ) {
    if ( !headers_sent() ) {
        wp_redirect( $location, $status );
        die();
    }
    else {
        die( '<script type="text/javascript">'
            . 'document.location = "' . str_replace( '&amp;', '&', esc_js( $location ) ) . '";'
            . '</script>' );
    }
}

/**
 * Get the Attachment ID for a specific image field
 *
 * @param array|int|string $image The image field array, ID, or guid
 *
 * @return int Attachment ID
 */
function pods_image_id_from_field ( $image ) {
    $id = 0;

    if ( !empty( $image ) ) {
        if ( is_array( $image ) ) {
            if ( isset( $image[ 0 ] ) )
                $id = pods_image_id_from_field( $image[ 0 ] );
            elseif ( isset( $image[ 'ID' ] ) )
                $id = $image[ 'ID' ];
            elseif ( isset( $image[ 'guid' ] ) )
                $id = pods_image_id_from_field( $image[ 'guid' ] );
        }
        else {
            if ( false === strpos( $image, '.' ) && is_numeric( $image ) )
                $id = $image;
            else {
                $guid = pods_query( "SELECT `ID` FROM @wp_posts WHERE `post_type` = 'attachment' AND `guid` = %s", array( $image ) );

                if ( !empty( $guid ) )
                    $id = $guid[ 0 ]->ID;
            }
        }
    }

    $id = (int) $id;

    return $id;
}

/**
 * Get the <img> HTML for a specific image field
 *
 * @param array|int|string $image The image field array, ID, or guid
 * @param string $size Image size to use
 * @param int $default Default image to show if image not found, can be field array, ID, or guid
 * @param string|array $attributes <img> Attributes array or string (passed to wp_get_attachment_image
 *
 * @return string <img> HTML or empty if image not found
 */
function pods_image ( $image, $size = 'thumbnail', $default = 0, $attributes = '' ) {
    $html = '';

    $id = pods_image_id_from_field( $image );
    $default = pods_image_id_from_field( $default );

    if ( 0 < $id )
        $html = wp_get_attachment_image( $id, $size, false, $attributes );

    if ( empty( $html ) && 0 < $default )
        $html = wp_get_attachment_image( $id, $size, false, $attributes );

    return $html;
}

/**
 * Get the Image URL for a specific image field
 *
 * @param array|int|string $image The image field array, ID, or guid
 * @param string $size Image size to use
 * @param int $default Default image to show if image not found, can be field array, ID, or guid
 *
 * @return string Image URL or empty if image not found
 */
function pods_image_url ( $image, $size = 'thumbnail', $default = 0 ) {
    $url = '';

    $id = pods_image_id_from_field( $image );
    $default = pods_image_id_from_field( $default );

    if ( 0 < $id ) {
        $src = wp_get_attachment_image_src( $id, $size );

        if ( !empty( $src ) )
            $url = $src[ 0 ];
    }

    if ( empty( $url ) && 0 < $default ) {
        $src = wp_get_attachment_image_src( $default, $size );

        if ( !empty( $src ) )
            $url = $src[ 0 ];
    }

    return $url;
}

/**
 * Check if a user has permission to be doing something based on standard permission options
 *
 * @param array $options
 *
 * @since 2.0.5
 */
function pods_permission ( $options ) {
    $permission = false;

    if ( isset( $options[ 'options' ] ) )
        $options = $options[ 'options' ];

    if ( is_user_logged_in() && ( is_super_admin() || current_user_can( 'delete_users' ) || current_user_can( 'manage_options' ) ) )
        $permission = true;
    elseif ( 1 == pods_var( 'restrict_capability', $options, 0 ) ) {
        $capabilities = explode( ',', pods_var( 'capability_allowed', $options ) );
        $capabilities = array_unique( array_filter( $capabilities ) );

        foreach( $capabilities as $capability ) {
            $must_have_capabilities = explode( '&&', $capability );
            $must_have_capabilities = array_unique( array_filter( $must_have_capabilities ) );

            $must_have_permission = true;

            foreach ( $must_have_capabilities as $must_have_capability ) {
                if ( !current_user_can( $must_have_capability ) ) {
                    $must_have_permission = false;

                    break;
                }
            }

            if ( $must_have_permission ) {
                $permission = true;

                break;
            }
        }
    }
    elseif ( 0 == pods_var( 'admin_only', $options, 0 ) )
        $permission = true;

    return $permission;
}

/**
 * Return a variable if a user is logged in or anonymous, or a specific capability
 *
 * @param mixed $anon Variable to return if user is anonymous (not logged in)
 * @param mixed $user Variable to return if user is logged in
 * @param string|array $capability Capability or array of Capabilities to check to return $user on
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
 * Get a field value from a Pod
 *
 * @param string $pod The pod name
 * @param mixed $id (optional) The ID or slug, to load a single record; Provide array of $params to run 'find'
 * @param string|array $name The field name, or an associative array of parameters
 * @param boolean $single (optional) For tableless fields, to return the whole array or the just the first item
 *
 * @return mixed Field value
 */
function pods_field ( $pod, $id, $name, $single = false ) {
    return pods( $pod, $id )->field( $name, $single );
}

/**
 * Get a field display value from a Pod
 *
 * @param string $pod The pod name
 * @param mixed $id (optional) The ID or slug, to load a single record; Provide array of $params to run 'find'
 * @param string|array $name The field name, or an associative array of parameters
 * @param boolean $single (optional) For tableless fields, to return the whole array or the just the first item
 *
 * @return mixed Field value
 */
function pods_field_display ( $pod, $id, $name, $single = false ) {
    return pods( $pod, $id )->display( $name, $single );
}

/**
 * Get a field raw value from a Pod
 *
 * @param string $pod The pod name
 * @param mixed $id (optional) The ID or slug, to load a single record; Provide array of $params to run 'find'
 * @param string|array $name The field name, or an associative array of parameters
 * @param boolean $single (optional) For tableless fields, to return the whole array or the just the first item
 *
 * @return mixed Field value
 */
function pods_field_raw ( $pod, $id, $name, $single = false ) {
    return pods( $pod, $id )->raw( $name, $single );

}

/**
 * Include and Init the Pods class
 *
 * @see PodsInit
 *
 * @return PodsInit
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
 * @see PodsComponents
 *
 * @return PodsComponents
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
 * @see Pods
 *
 * @param string $type The pod name
 * @param mixed $id (optional) The ID or slug, to load a single record; Provide array of $params to run 'find'
 * @param bool $strict (optional) If set to true, return false instead of an object if the Pod doesn't exist
 *
 * @return bool|\Pods
 * @since 2.0.0
 * @link http://podsframework.org/docs/pods/
 */
function pods ( $type = null, $id = null, $strict = false ) {
    require_once( PODS_DIR . 'classes/Pods.php' );

    $pod = new Pods( $type, $id );

    if ( true === $strict && null !== $type && !$pod->valid() )
        return false;

    return $pod;
}

/**
 * Easily create content admin screens with in-depth customization. This is the primary interface function that Pods
 * runs off of. It's also the only function required to be run in order to have a fully functional Manage interface.
 *
 * @see PodsUI
 *
 * @param array|string|Pods $obj (optional) Configuration options for the UI
 * @param boolean $deprecated (optional) Whether to enable deprecated options (used by pods_ui_manage)
 *
 * @return PodsUI
 *
 * @since 2.0.0
 * @link http://podsframework.org/docs/pods-ui/
 */
function pods_ui ( $obj, $deprecated = false ) {
    require_once( PODS_DIR . 'classes/PodsUI.php' );

    return new PodsUI( $obj, $deprecated );
}

/**
 * Include and get the PodsAPI object, for use with all calls that Pods makes for add, save, delete, and more.
 *
 * @see PodsAPI
 *
 * @param string $pod (optional) (deprecated) The Pod name
 * @param string $format (optional) (deprecated) Format used in import() and export()
 *
 * @return PodsAPI
 *
 * @since 2.0.0
 * @link http://podsframework.org/docs/pods-api/
 */
function pods_api ( $pod = null, $format = null ) {
    require_once( PODS_DIR . 'classes/PodsAPI.php' );

    return new PodsAPI( $pod, $format );
}

/**
 * Include and Init the PodsData class
 *
 * @see PodsData
 *
 * @param string|\Pod $pod The pod object to load
 * @param int $id (optional) Id of the pod to fetch
 * @param bool $strict (optional) If true throw an error if pod does not exist
 *
 * @return PodsData
 *
 * @since 2.0.0
 */
function pods_data ( $pod = null, $id = null, $strict = true ) {
    require_once( PODS_DIR . 'classes/PodsData.php' );

    return new PodsData( $pod, $id );
}

/**
 * Include and Init the PodsFormUI class
 *
 * @see PodsForm
 *
 * @return PodsForm
 *
 * @since 2.0.0
 */
function pods_form () {
    require_once( PODS_DIR . 'classes/PodsForm.php' );

    return new PodsForm();
}

/**
 * Include and Init the PodsMeta class
 *
 * @see PodsMeta
 *
 * @return PodsMeta
 *
 * @since 2.0.0
 */
function pods_meta () {
    require_once( PODS_DIR . 'classes/PodsMeta.php' );

    return new PodsMeta();
}

/**
 * Include and Init the PodsAdmin class
 *
 * @see PodsAdmin
 *
 * @return PodsAdmin
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
 * @see PodsMigrate
 *
 * @return PodsMigrate
 *
 * @since 2.2
 */
function pods_migrate () {
    require_once( PODS_DIR . 'classes/PodsMigrate.php' );

    return new PodsMigrate();
}

/**
 * Include and Init the PodsUpgrade class
 *
 * @param string $version Version number of upgrade to get
 *
 * @see PodsUpgrade
 *
 * @return PodsUpgrade
 *
 * @since 2.1.0
 */
function pods_upgrade ( $version = '' ) {
		include_once PODS_DIR . 'sql/upgrade/PodsUpgrade.php';

    $class_name = str_replace( '.', '_', $version );
    $class_name = "PodsUpgrade_{$class_name}";

    $class_name = trim( $class_name, '_' );

    if ( !class_exists( $class_name ) ) {
        $file = PODS_DIR . 'sql/upgrade/' . basename( $class_name ) . '.php';

        if ( file_exists( $file ) )
            include_once $file;
    }

    $class = false;

    if ( class_exists( $class_name ) )
        $class = new $class_name();

    return $class;
}

/**
 * Include and Init the PodsArray class
 *
 * @see PodsArray
 *
 * @param mixed $container Object (or existing Array)
 *
 * @return PodsArray
 *
 * @since 2.0.0
 */
function pods_array ( $container ) {
    require_once( PODS_DIR . 'classes/PodsArray.php' );

    return new PodsArray( $container );
}

/**
 * Include a file that's child/parent theme-aware, and can be cached into object cache or transients
 *
 * @see PodsView::view
 *
 * @param string $view Path of the file to be included, this is relative to the current theme
 * @param array|null $data (optional) Data to pass on to the template, using variable => value format
 * @param int|bool $expires (optional) Time in seconds for the cache to expire, if false caching is disabled.
 * @param string $cache_mode (optional) Specify the caching method to use for the view, available options include cache, transient, or site-transient
 * @param bool $return (optional) Whether to return the view or not, defaults to false and will echo it
 *
 * @return string|bool The view output
 *
 * @since 2.0.0
 * @link http://podsframework.org/docs/pods-view/
 */
function pods_view ( $view, $data = null, $expires = false, $cache_mode = 'cache', $return = false ) {
    require_once( PODS_DIR . 'classes/PodsView.php' );

    $view = PodsView::view( $view, $data, $expires, $cache_mode );

    if ( $return )
        return $view;

    echo $view;
}

/**
 * Set a cached value
 *
 * @see PodsView::set
 *
 * @param string $key Key for the cache
 * @param mixed $value Value to add to the cache
 * @param int $expires (optional) Time in seconds for the cache to expire, if 0 caching is disabled.
 * @param string $cache_mode (optional) Decides the caching method to use for the view.
 * @param string $group Key for the group
 *
 * @return bool|mixed|null|string|void
 *
 * @since 2.0.0
 */
function pods_view_set ( $key, $value, $expires = 0, $cache_mode = 'cache', $group = '' ) {
    require_once( PODS_DIR . 'classes/PodsView.php' );

    return PodsView::set( $key, $value, $expires, $cache_mode, $group );
}

/**
 * Get a cached value
 *
 * @see PodsView::get
 *
 * @param string $key Key for the cache
 * @param string $cache_mode (optional) Decides the caching method to use for the view.
 * @param string $group Key for the group
 *
 * @return bool|mixed|null|void
 *
 * @since 2.0.0
 */
function pods_view_get ( $key, $cache_mode = 'cache', $group = '' ) {
    require_once( PODS_DIR . 'classes/PodsView.php' );

    return PodsView::get( $key, $cache_mode, $group );
}

/**
 * Clear a cached value
 *
 * @see PodsView::clear
 *
 * @param string|bool $key Key for the cache
 * @param string $cache_mode (optional) Decides the caching method to use for the view.
 * @param string $group Key for the group
 *
 * @return bool
 *
 * @since 2.0.0
 */
function pods_view_clear ( $key = true, $cache_mode = 'cache', $group = '' ) {
    require_once( PODS_DIR . 'classes/PodsView.php' );

    return PodsView::clear( $key, $cache_mode, $group );
}

/**
 * Set a cached value
 *
 * @see PodsView::set
 *
 * @param string $key Key for the cache
 * @param mixed $value Value to add to the cache
 * @param string $group Key for the group
 * @param int $expires (optional) Time in seconds for the cache to expire, if 0 caching is disabled.
 *
 * @return bool|mixed|null|string|void
 *
 * @since 2.0.0
 */
function pods_cache_set ( $key, $value, $group = '', $expires = 0) {
    return pods_view_set( $key, $value, $expires, 'cache', $group );
}

/**
 * Clear a cached value
 *
 * @see PodsView::clear
 *
 * @param string $key Key for the cache
 * @param string $group Key for the group
 *
 * @return bool
 *
 * @since 2.0.0
 */
function pods_cache_get ( $key, $group = '' ) {
    return pods_view_get( $key, 'cache', $group );
}

/**
 * Get a cached value
 *
 * @see PodsView::get
 *
 * @param string|bool $key Key for the cache
 * @param string $group Key for the group
 *
 * @return bool|mixed|null|void
 *
 * @since 2.0.0
 */
function pods_cache_clear ( $key = true, $group = '' ) {
    return pods_view_clear( $key, 'cache', $group );
}

/**
 * Set a cached value
 *
 * @see PodsView::set
 *
 * @param string $key Key for the cache
 * @param mixed $value Value to add to the cache
 * @param int $expires (optional) Time in seconds for the cache to expire, if 0 caching is disabled.
 *
 * @return bool|mixed|null|string|void
 *
 * @since 2.0.0
 */
function pods_transient_set ( $key, $value, $expires = 0 ) {
    return pods_view_set( $key, $value, $expires, 'transient' );
}

/**
 * Get a cached value
 *
 * @see PodsView::get
 *
 * @param string $key Key for the cache
 *
 * @return bool|mixed|null|void
 *
 * @since 2.0.0
 */
function pods_transient_get ( $key ) {
    return pods_view_get( $key, 'transient' );
}

/**
 * Clear a cached value
 *
 * @see PodsView::clear
 *
 * @param string|bool $key Key for the cache
 *
 * @return bool
 *
 * @since 2.0.0
 */
function pods_transient_clear ( $key = true ) {
    return pods_view_clear( $key, 'transient' );
}

/**
 * Add a new Pod outside of the DB
 *
 * @see PodsMeta::register
 *
 * @param string $type The pod type ('post_type', 'taxonomy', 'media', 'user', 'comment')
 * @param string $name The pod name
 * @param array $object (optional) Pod array, including any 'fields' arrays
 *
 * @since 2.1.0
 */
function pods_register_type ( $type, $name, $object = null ) {
    if ( empty( $object ) )
        $object = array();

    if ( !empty( $name ) )
        $object[ 'name' ] = $name;

    pods_meta()->register( $type, $object );
}

/**
 * Add a new Pod field outside of the DB
 *
 * @see PodsMeta::register_field
 *
 * @param string $pod The pod name
 * @param string $name The name of the Pod
 * @param array $object (optional) Pod array, including any 'fields' arrays
 *
 * @since 2.1.0
 */
function pods_register_field ( $pod, $name, $field = null ) {
    if ( empty( $field ) )
        $field = array();

    if ( !empty( $name ) )
        $field[ 'name' ] = $name;

    pods_meta()->register_field( $pod, $field );
}

/**
 * Add a meta group of fields to add/edit forms
 *
 * @see PodsMeta::group_add
 *
 * @param string|array $pod The pod or type of element to attach the group to.
 * @param string $label Title of the edit screen section, visible to user.
 * @param string|array $fields Either a comma separated list of text fields or an associative array containing field information.
 * @param string $context (optional) The part of the page where the edit screen section should be shown ('normal', 'advanced', or 'side').
 * @param string $priority (optional) The priority within the context where the boxes should show ('high', 'core', 'default' or 'low').
 * @param string $type (optional) Type of the post to attach to.
 *
 * @since 2.0.0
 * @link http://podsframework.org/docs/pods-group-add/
 */
function pods_group_add ( $pod, $label, $fields, $context = 'normal', $priority = 'default', $type = null ) {
    if ( !is_array( $pod ) && null !== $type ) {
        $pod = array(
            'name' => $pod,
            'type' => $type
        );
    }

    pods_meta()->group_add( $pod, $label, $fields, $context, $priority );
}

/**
 * Check if a plugin is active on non-admin pages (is_plugin_active() only available in admin)
 *
 * @param string $plugin Plugin name.
 *
 * @return bool
 *
 * @since 2.0.0
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
 *
 * @return bool
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
        $no_conflict[ 'filter' ] = array();

        $no_conflict[ 'action' ] = array(
            array( 'edit_term', array( PodsInit::$meta, 'save_taxonomy' ), 10, 3 ),
            array( 'create_term', array( PodsInit::$meta, 'save_taxonomy' ), 10, 3 )
        );
    }
    elseif ( 'media' == $object_type ) {
        $no_conflict[ 'filter' ] = array(
            array( 'wp_update_attachment_metadata', array( PodsInit::$meta, 'save_media' ), 10, 2 )
        );

        $no_conflict[ 'action' ] = array();
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
            array( 'pre_comment_approved', array( PodsInit::$meta, 'validate_comment' ), 10, 2 ),
            array( 'comment_post', array( PodsInit::$meta, 'save_comment' ) ),
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
 *
 * @return bool
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