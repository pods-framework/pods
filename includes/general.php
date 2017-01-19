<?php
/**
 * @package Pods\Global\Functions\General
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
 * @return array|bool|mixed|null|void
 * @since 2.0
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

    if ( 1 == pods_v( 'pods_debug_sql_all', 'get', 0 ) && is_user_logged_in() && pods_is_admin( array( 'pods' ) ) ) {
        $debug_sql = $sql;

        echo '<textarea cols="100" rows="24">';

        if ( is_array( $debug_sql ) ) {
            $debug_sql = print_r( $debug_sql, true );
        }

        echo esc_textarea( $debug_sql );

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
 * @since 2.0
 * @todo Need to figure out how to handle $scope = 'pods' for the Pods class
 */
function pods_do_hook ( $scope, $name, $args = null, $obj = null ) {
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
 *
 * @return void
 */
function pods_message ( $message, $type = null ) {
    if ( empty( $type ) || !in_array( $type, array( 'notice', 'error' ) ) )
        $type = 'notice';

    $class = '';

    if ( 'notice' == $type )
        $class = 'updated';
    elseif ( 'error' == $type )
        $class = 'error';

    echo '<div id="message" class="' . esc_attr( $class ) . ' fade"><p>' . $message . '</p></div>';
}

$GLOBALS['pods_errors'] = array();

/**
 * Error Handling which throws / displays errors
 *
 * @param string|array        $error The error message(s) to be thrown / displayed.
 * @param object|boolean|null $obj   If $obj->display_errors is set and is set to true it will display errors, if boolean and is set to true it will display errors.
 *
 * @throws Exception Throws exception for developer-oriented error handling.
 *
 * @return mixed
 *
 * @since 2.0
 */
function pods_error( $error, $obj = null ) {

	global $pods_errors;

	$error_mode = 'exception';

	if ( is_object( $obj ) && isset( $obj->display_errors ) ) {
		if ( true === $obj->display_errors ) {
			$error_mode = 'exit';
		} elseif ( false === $obj->display_errors ) {
			$error_mode = 'exception';
		} else {
			$error_mode = $obj->display_errors;
		}
	} elseif ( true === $obj ) {
		$error_mode = 'exit';
	} elseif ( false === $obj ) {
		$error_mode = 'exception';
	} elseif ( is_string( $obj ) ) {
		$error_mode = $obj;
	}

	if ( is_object( $error ) && 'Exception' === get_class( $error ) ) {
		$error = $error->getMessage();

		$error_mode = 'exception';
	}

	/**
	 * @var string $error_mode Throw an exception, exit with the message, return false, or return WP_Error
	 */
	if ( ! in_array( $error_mode, array( 'exception', 'exit', 'false', 'wp_error' ), true ) ) {
		$error_mode = 'exception';
	}

	/**
	 * Filter the error mode used by pods_error.
	 *
	 * @param string $error_mode Error mode
	 * @param string|array $error Error message(s)
	 * @param object|boolean|string|null $obj
	 */
	$error_mode = apply_filters( 'pods_error_mode', $error_mode, $error, $obj );

	if ( is_array( $error ) ) {
		$error = array_map( 'wp_kses_post', $error );

		if ( 1 === count( $error ) ) {
			$error = current( $error );

			// Create WP_Error for use later.
			$wp_error = new WP_Error( 'pods-error-' . md5( $error ), $error );
		} else {
			// Create WP_Error for use later.
			$wp_error = new WP_Error();

			foreach ( $error as $error_message ) {
				$wp_error->add( 'pods-error-' . md5( $error_message ), $error_message );
			}

			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
				$error = __( 'The following issue occurred:', 'pods' )
						 . "\n\n- " . implode( "\n- ", $error );
			} else {
				$error = __( 'The following issues occurred:', 'pods' )
						 . "\n<ul><li>" . implode( "</li>\n<li>", $error ) . "</li></ul>";
			}
		}
	} else {
		if ( is_object( $error ) ) {
			$error = __( 'An unknown error has occurred', 'pods' );
		}

		$error = wp_kses_post( $error );

		// Create WP_Error for use later.
		$wp_error = new WP_Error( 'pods-error-' . md5( $error ), $error );
	}

	$last_error = $pods_errors;

	$pods_errors = array();

	if ( $last_error === $error && 'exception' === $error_mode ) {
		$error_mode = 'exit';
	}

	if ( ! empty( $error ) ) {
		if ( 'exception' === $error_mode ) {
			$exception_bypass = apply_filters( 'pods_error_exception', null, $error );

			if ( null !== $exception_bypass ) {
				return $exception_bypass;
			}

			$pods_errors = $error;

			set_exception_handler( 'pods_error' );

			throw new Exception( $error );
		} elseif ( 'exit' === $error_mode ) {
			$die_bypass = apply_filters( 'pods_error_die', null, $error );

			if ( null !== $die_bypass ) {
				return $die_bypass;
			}

			// die with error
			if ( ! defined( 'DOING_AJAX' ) && ! headers_sent() && ( is_admin() || false !== strpos( $_SERVER['REQUEST_URI'], 'wp-comments-post.php' ) ) ) {
				wp_die( $error );
			} else {
				die( sprintf( '<e>%s</e>', $error ) );
			}
		} elseif ( 'wp_error' === $error_mode ) {
			return $wp_error;
		}
	}

	return false;

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
 *
 * @return void
 *
 * @since 2.0
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

    if ( false === strpos( $debug, "<pre class='xdebug-var-dump'" ) && ( !ini_get( 'xdebug.overload_var_dump' ) && !ini_get( 'html_errors' ) ) ) {
        if ( !defined( 'DOING_AJAX' ) || !DOING_AJAX )
            $debug = esc_html( $debug );

        $debug = '<pre>' . $debug . '</pre>';
    }

    $debug = '<e>' . $debug;

    if ( 2 === $die )
        wp_die( $debug );
    elseif ( true === $die )
        die( $debug );

    echo $debug;
}

/**
 * Determine if user has admin access
 *
 * @param string|array $cap Additional capabilities to check
 *
 * @return bool Whether user has admin access
 *
 * @since 2.3.5
 */
function pods_is_admin ( $cap = null ) {
    if ( is_user_logged_in() ) {
        $pods_admin_capabilities = array(
            'delete_users' // default is_super_admin checks against this
        );

        $pods_admin_capabilities = apply_filters( 'pods_admin_capabilities', $pods_admin_capabilities, $cap );

        if ( is_multisite() && is_super_admin() )
            return apply_filters( 'pods_is_admin', true, $cap, '_super_admin' );

        if ( empty( $cap ) )
            $cap = array();
        else
            $cap = (array) $cap;

        $cap = array_unique( array_filter( array_merge( $pods_admin_capabilities, $cap ) ) );

        foreach ( $cap as $capability ) {
            if ( current_user_can( $capability ) )
                return apply_filters( 'pods_is_admin', true, $cap, $capability );
        }
    }

    return apply_filters( 'pods_is_admin', false, $cap, null );
}

/**
 * Determine if Developer Mode is enabled
 *
 * @return bool Whether Developer Mode is enabled
 *
 * @since 2.3
 */
function pods_developer () {
    if ( defined( 'PODS_DEVELOPER' ) && PODS_DEVELOPER )
        return true;

    return false;
}

/**
 * Determine if Tableless Mode is enabled
 *
 * @return bool Whether Tableless Mode is enabled
 *
 * @since 2.3
 */
function pods_tableless () {
    if ( defined( 'PODS_TABLELESS' ) && PODS_TABLELESS )
        return true;

    return false;
}

/**
 * Determine if Strict Mode is enabled
 *
 * @param bool $include_debug Whether to include WP_DEBUG in strictness level
 *
 * @return bool Whether Strict Mode is enabled
 *
 * @since 2.3.5
 */
function pods_strict( $include_debug = true ) {

	if ( defined( 'PODS_STRICT' ) && PODS_STRICT ) {
		return true;
	}
	// @deprecated PODS_STRICT_MODE since 2.3.5
	elseif ( pods_allow_deprecated( false ) && defined( 'PODS_STRICT_MODE' ) && PODS_STRICT_MODE ) {
		return true;
	}
	elseif ( $include_debug && defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		return true;
	}

	return false;

}

/**
 * Determine if Deprecated Mode is enabled
 *
 * @param bool $include_debug Whether to include strict mode
 *
 * @return bool Whether Deprecated Mode is enabled
 *
 * @since 2.3.10
 */
function pods_allow_deprecated( $strict = true ) {

	if ( $strict && pods_strict() ) {
		return false;
	}
	elseif ( !defined( 'PODS_DEPRECATED' ) || PODS_DEPRECATED ) {
		return true;
	}

	return false;

}

/**
 * Determine if Pods API Caching is enabled
 *
 * @return bool Whether Pods API Caching is enabled
 *
 * @since 2.3.9
 */
function pods_api_cache () {
    if ( defined( 'PODS_API_CACHE' ) && !PODS_API_CACHE )
        return false;

    return true;
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
 * @since 2.0
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
 * @return void
 *
 * @since 2.0
 */
function pods_help ( $text, $url = null ) {

	if ( ! wp_script_is( 'jquery-qtip2', 'registered' ) ) {
		wp_register_script( 'jquery-qtip2', PODS_URL . 'ui/js/jquery.qtip.min.js', array( 'jquery' ), '2.2' );
	}
	elseif ( ! wp_script_is( 'jquery-qtip2', 'queue' ) && ! wp_script_is( 'jquery-qtip2', 'to_do' ) && ! wp_script_is( 'jquery-qtip2', 'done' ) ) {
		wp_enqueue_script( 'jquery-qtip2' );
	}

	if ( ! wp_style_is( 'jquery-qtip2', 'registered' ) ) {
		wp_register_style( 'jquery-qtip2', PODS_URL . 'ui/css/jquery.qtip.min.css', array(), '2.2' );
	}
	elseif ( ! wp_style_is( 'jquery-qtip2', 'queue' ) && ! wp_style_is( 'jquery-qtip2', 'to_do' ) && ! wp_style_is( 'jquery-qtip2', 'done' ) ) {
		wp_enqueue_style( 'jquery-qtip2' );
	}

	if ( ! wp_script_is( 'pods-qtip-init', 'registered' ) ) {
		wp_register_script( 'pods-qtip-init', PODS_URL . 'ui/js/qtip.js', array(
			'jquery',
			'jquery-qtip2'
		), PODS_VERSION );
	}
	elseif ( ! wp_script_is( 'pods-qtip-init', 'queue' ) && ! wp_script_is( 'pods-qtip-init', 'to_do' ) && ! wp_script_is( 'pods-qtip-init', 'done' ) ) {
		wp_enqueue_script( 'pods-qtip-init' );
	}

	if ( is_array( $text ) ) {
		if ( isset( $text[ 1 ] ) ) {
			$url = $text[ 1 ];
		}

		$text = $text[ 0 ];
	}

	if ( 'help' == $text ) {
		return;
	}

	if ( 0 < strlen( $url ) ) {
		$text .= '<br /><br /><a href="' . $url . '" target="_blank">' . __( 'Find out more', 'pods' ) . ' &raquo;</a>';
	}

    echo '<img src="' . esc_url( PODS_URL ) . 'ui/images/help.png" alt="' . esc_attr( $text ) . '" class="pods-icon pods-qtip" />';
}

/**
 * Check whether or not something is a specific version minimum and/or maximum
 *
 * @param string $minimum_version Minimum version
 * @param string $comparison Comparison operator
 * @param string $maximum_version Maximum version
 *
 * @return bool
 */
function pods_version_check ( $what, $minimum_version, $comparison = '<=', $maximum_version = null ) {
    global $wp_version, $wpdb;

    if ( 'php' == $what )
        $version = phpversion();
    elseif ( 'mysql' === $what )
        $version = $wpdb->db_version();
    else
        $version = $wp_version;

    if ( !empty( $minimum_version ) && !version_compare( $minimum_version, $version, $comparison ) )
        return false;

    if ( !empty( $maximum_version ) && !version_compare( $version, $maximum_version, $comparison ) )
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
 * Get the full URL of the current page
 *
 * @return string Full URL of the current page
 * @since 2.3
 */
function pods_current_url () {
    $url = 'http';

    if ( isset( $_SERVER[ 'HTTPS' ] ) && 'off' != $_SERVER[ 'HTTPS' ] && 0 != $_SERVER[ 'HTTPS' ] )
        $url = 'https';

    $url .= '://' . $_SERVER[ 'HTTP_HOST' ] . $_SERVER[ 'REQUEST_URI' ];

    return apply_filters( 'pods_current_url', $url );
}

/**
 * Find out if the current page has a valid $pods
 *
 * @param object $object The Pod Object currently checking (optional)
 *
 * @return bool
 * @since 2.0
 */
function is_pod ( $object = null ) {
    global $pods, $post;

    if ( is_object( $object ) && isset( $object->pod ) && !empty( $object->pod ) )
        return true;
    elseif ( is_object( $pods ) && isset( $pods->pod ) && !empty( $pods->pod ) )
        return true;
    elseif ( is_object( $post ) && isset( $post->post_type ) && pods_api()->pod_exists( $post->post_type, 'post_type' ) )
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

    if ( pods_is_admin( array( 'pods', 'pods_content' ) ) )
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

            if ( !isset( $approved_privs[ $priv ] ) )
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

	if ( defined( 'PODS_DISABLE_SHORTCODE' ) && PODS_DISABLE_SHORTCODE ) {
		return '';
	}

	// For enforcing pagination parameters when not displaying pagination
	$page = 1;
	$offset = 0;

	if ( isset( $tags['page'] ) ) {
		$page = (int) $tags['page'];
		$page = max( $page, 1 );
	}

	if ( isset( $tags['offset'] ) ) {
		$offset = (int) $tags['offset'];
		$offset = max( $offset, 0 );
	}

    $defaults = array(
    	'use_current' => false,
        'name' => null,
        'id' => null,
        'slug' => null,
        'select' => null,
        'join' => null,
        'order' => null,
        'orderby' => null,
        'limit' => null,
        'where' => null,
        'having' => null,
        'groupby' => null,
        'search' => true,
        'pagination' => false,
        'page' => null,
        'offset' => null,
        'filters' => false,
        'filters_label' => null,
        'filters_location' => 'before',
        'pagination_label' => null,
        'pagination_location' => 'after',
        'field' => null,
        'col' => null,
        'template' => null,
        'pods_page' => null,
        'helper' => null,
        'form' => null,
        'fields' => null,
        'label' => null,
        'thank_you' => null,
        'view' => null,
        'cache_mode' => 'none',
        'expires' => 0,
		'shortcodes' => false
    );

    if ( !empty( $tags ) )
        $tags = array_merge( $defaults, $tags );
    else
        $tags = $defaults;

    $tags = apply_filters( 'pods_shortcode', $tags );

	$tags[ 'pagination' ] = filter_var($tags[ 'pagination' ], FILTER_VALIDATE_BOOLEAN);
	$tags[ 'search' ] = filter_var($tags[ 'pagination' ], FILTER_VALIDATE_BOOLEAN);
	$tags[ 'use_current' ] = filter_var($tags[ 'use_current' ], FILTER_VALIDATE_BOOLEAN);

    if ( empty( $content ) )
        $content = null;

	// Allow views only if not targeting a file path (must be within theme)
    if ( 0 < strlen( $tags[ 'view' ] ) ) {
		$return = '';

		if ( !file_exists( $tags[ 'view' ] ) ) {
			$return = pods_view( $tags[ 'view' ], null, (int) $tags[ 'expires' ], $tags[ 'cache_mode' ] );

			if ( $tags[ 'shortcodes' ] && defined( 'PODS_SHORTCODE_ALLOW_SUB_SHORTCODES' ) && PODS_SHORTCODE_ALLOW_SUB_SHORTCODES ) {
				$return = do_shortcode( $return );
			}
		}

		return $return;
	}

    if ( ! $tags['use_current'] && empty( $tags[ 'name' ] ) ) {
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

    if ( empty( $content ) && empty( $tags[ 'pods_page' ] ) && empty( $tags[ 'template' ] ) && empty( $tags[ 'field' ] ) && empty( $tags[ 'form' ] ) ) {
        return '<p>Please provide either a template or field name</p>';
    }

    if ( ! $tags['use_current'] && !isset( $id ) ) {
        // id > slug (if both exist)
		$id = null;

		if ( !empty( $tags[ 'slug' ] ) ) {
			$id = $tags[ 'slug' ];

			if ( defined( 'PODS_SHORTCODE_ALLOW_EVALUATE_TAGS' ) && PODS_SHORTCODE_ALLOW_EVALUATE_TAGS ) {
				$id = pods_evaluate_tags( $id );
			}
		}

        if ( !empty( $tags[ 'id' ] ) ) {
            $id = $tags[ 'id' ];

            if ( defined( 'PODS_SHORTCODE_ALLOW_EVALUATE_TAGS' ) && PODS_SHORTCODE_ALLOW_EVALUATE_TAGS ) {
                $id = pods_evaluate_tags( $id );
            }

            if ( is_numeric( $id ) )
                $id = absint( $id );
        }
    }

    if ( !isset( $pod ) ) {
    	if ( ! $tags['use_current'] ) {
        	$pod = pods( $tags[ 'name' ], $id );
    	} else {
    		$pod = pods();
    		$id = $pod->id();
    	}
    }

    if ( empty( $pod ) || ! $pod->valid() )
        return '<p>Pod not found</p>';

	$found = 0;
	
	$is_singular = ( ! empty( $id ) || $tags['use_current'] );

	if ( ! $is_singular ) {
		$params = array();

		if ( !defined( 'PODS_DISABLE_SHORTCODE_SQL' ) || !PODS_DISABLE_SHORTCODE_SQL ) {
			if ( 0 < strlen( $tags[ 'orderby' ] ) ) {
				$params[ 'orderby' ] = $tags[ 'orderby' ];
			}

			if ( 0 < strlen( $tags[ 'where' ] ) ) {
				$params[ 'where' ] = $tags[ 'where' ];

				if ( defined( 'PODS_SHORTCODE_ALLOW_EVALUATE_TAGS' ) && PODS_SHORTCODE_ALLOW_EVALUATE_TAGS ) {
					$params[ 'where' ] = pods_evaluate_tags( $params[ 'where' ] );
				}
			}

			if ( 0 < strlen( $tags[ 'having' ] ) ) {
				$params[ 'having' ] = $tags[ 'having' ];

				if ( defined( 'PODS_SHORTCODE_ALLOW_EVALUATE_TAGS' ) && PODS_SHORTCODE_ALLOW_EVALUATE_TAGS ) {
					$params[ 'having' ] = pods_evaluate_tags( $id );
				}
			}

			if ( 0 < strlen( $tags[ 'groupby' ] ) ) {
				$params[ 'groupby' ] = $tags[ 'groupby' ];
			}

			if ( 0 < strlen( $tags[ 'select' ] ) ) {
				$params[ 'select' ] = $tags[ 'select' ];
			}
			if ( 0 < strlen( $tags[ 'join' ] ) ) {
				$params[ 'join' ] = $tags[ 'join' ];
			}
		}

		// Forms require params set
		if ( ! empty( $params ) || empty( $tags[ 'form' ] ) ) {
			if ( !empty( $tags[ 'limit' ] ) ) {
				$params[ 'limit' ] = (int) $tags[ 'limit' ];
			}

			$params[ 'search' ] = $tags[ 'search' ];

			$params[ 'pagination' ] = $tags[ 'pagination' ];

			// If we aren't displaying pagination, we need to enforce page/offset
			if ( ! $params['pagination'] ) {
				$params['page']   = $page;
				$params['offset'] = $offset;

				// Force pagination on, we need it and we're enforcing page/offset
				$params['pagination'] = true;
			} else {
				// If we are displaying pagination, allow page/offset override only if *set*

				if ( isset( $tags['page'] ) ) {
					$params['page'] = (int) $tags['page'];
					$params['page'] = max( $params['page'], 1 );
				}

				if ( isset( $tags['offset'] ) ) {
					$params['offset'] = (int) $tags['offset'];
					$params['offset'] = max( $params['offset'], 0 );
				}
			}

			if ( !empty( $tags[ 'cache_mode' ] ) && 'none' != $tags[ 'cache_mode' ] ) {
				$params[ 'cache_mode' ] = $tags[ 'cache_mode' ];
				$params[ 'expires' ] = (int) $tags[ 'expires' ];
			}

			$params = apply_filters( 'pods_shortcode_findrecords_params', $params, $pod, $tags );

			$pod->find( $params );

			$found = $pod->total();
		}
	}

    if ( !empty( $tags[ 'form' ] ) ) {
		if ( 'user' == $pod->pod ) {
			// Further hardening of User-based forms
			if ( false !== strpos( $tags[ 'fields' ], '_capabilities' ) || false !== strpos( $tags[ 'fields' ], '_user_level' ) ) {
				return '';
			}
			// Only explicitly allow user edit forms
			elseif ( $is_singular && ( !defined( 'PODS_SHORTCODE_ALLOW_USER_EDIT' ) || !PODS_SHORTCODE_ALLOW_USER_EDIT ) ) {
				return '';
			}
		}

        return $pod->form( $tags[ 'fields' ], $tags[ 'label' ], $tags[ 'thank_you' ] );
	}
    elseif ( !empty( $tags[ 'field' ] ) ) {
        if ( empty( $tags[ 'helper' ] ) )
            $return = $pod->display( $tags[ 'field' ] );
        else
            $return = $pod->helper( $tags[ 'helper' ], $pod->field( $tags[ 'field' ] ), $tags[ 'field' ] );

		if ( $tags[ 'shortcodes' ] && defined( 'PODS_SHORTCODE_ALLOW_SUB_SHORTCODES' ) && PODS_SHORTCODE_ALLOW_SUB_SHORTCODES ) {
			$return = do_shortcode( $return );
		}

		return $return;
    }
    elseif ( !empty( $tags[ 'pods_page' ] ) && class_exists( 'Pods_Pages' ) ) {
        $pods_page = Pods_Pages::exists( $tags[ 'pods_page' ] );

        if ( empty( $pods_page ) )
            return '<p>Pods Page not found</p>';

        $return = Pods_Pages::content( true, $pods_page );

		if ( $tags[ 'shortcodes' ] && defined( 'PODS_SHORTCODE_ALLOW_SUB_SHORTCODES' ) && PODS_SHORTCODE_ALLOW_SUB_SHORTCODES ) {
			$return = do_shortcode( $return );
		}

		return $return;
    }

    ob_start();

    if ( ! $is_singular && false !== $tags[ 'filters' ] && 'before' == $tags[ 'filters_location' ] )
        echo $pod->filters( $tags[ 'filters' ], $tags[ 'filters_label' ] );

    if ( ! $is_singular && 0 < $found && true === $tags[ 'pagination' ] && in_array( $tags[ 'pagination_location' ], array( 'before', 'both' ) ) )
        echo $pod->pagination( $tags[ 'pagination_label' ] );

    echo $pod->template( $tags[ 'template' ], $content );

    if ( ! $is_singular && 0 < $found && true === $tags[ 'pagination' ] && in_array( $tags[ 'pagination_location' ], array( 'after', 'both' ) ) )
        echo $pod->pagination( $tags[ 'pagination_label' ] );

    if ( ! $is_singular && false !== $tags[ 'filters' ] && 'after' == $tags[ 'filters_location' ] )
        echo $pod->filters( $tags[ 'filters' ], $tags[ 'filters_label' ] );

	$return = ob_get_clean();

	if ( $tags[ 'shortcodes' ] && defined( 'PODS_SHORTCODE_ALLOW_SUB_SHORTCODES' ) && PODS_SHORTCODE_ALLOW_SUB_SHORTCODES ) {
		$return = do_shortcode( $return );
	}

	return $return;
}

/**
 * Form Shortcode support for use anywhere that support WP Shortcodes
 *
 * @param array $tags An associative array of shortcode properties
 * @param string $content Not currently used
 *
 * @return string
 * @since 2.3
 */
function pods_shortcode_form ( $tags, $content = null ) {
    $tags[ 'form' ] = 1;

    return pods_shortcode( $tags );
}

/**
 * Fork of WordPress do_shortcode that allows specifying which shortcodes are ran.
 *
 * Search content for shortcodes and filter shortcodes through their hooks.
 *
 * If there are no shortcode tags defined, then the content will be returned
 * without any filtering. This might cause issues when plugins are disabled but
 * the shortcode will still show up in the post or content.
 *
 * @since 2.4.3
 *
 * @uses $shortcode_tags
 * @uses get_shortcode_regex() Gets the search pattern for searching shortcodes.
 *
 * @param string $content Content to search for shortcodes
 * @param array $shortcodes Array of shortcodes to run
 * @return string Content with shortcodes filtered out.
 */
function pods_do_shortcode( $content, $shortcodes ) {

	global $shortcode_tags;

	// No shortcodes in content
	if ( false === strpos( $content, '[' ) ) {
		return $content;
	}

	// No shortcodes registered
	if ( empty( $shortcode_tags ) || !is_array( $shortcode_tags ) ) {
		return $content;
	}

	// Store all shortcodes, to restore later
	$temp_shortcode_tags = $shortcode_tags;

	// Loop through all shortcodes and remove those not being used right now
	foreach ( $shortcode_tags as $tag => $callback ) {
		if ( ! in_array( $tag, $shortcodes ) ) {
			unset( $shortcode_tags[ $tag ] );
		}
	}

	// Build Shortcode regex pattern just for the shortcodes we want
	$pattern = get_shortcode_regex();

	// Call shortcode callbacks just for the shortcodes we want
	$content = preg_replace_callback( "/$pattern/s", 'do_shortcode_tag', $content );

	// Restore all shortcode tags
	$shortcode_tags = $temp_shortcode_tags;

	return $content;

}

/**
 * Check if Pods is compatible with WP / PHP / MySQL or not
 *
 * @return bool
 *
 * @since 1.10
 */
function pods_compatibility_check () {
    $compatible = true;

    if ( !pods_version_check( 'wp', PODS_WP_VERSION_MINIMUM ) ) {
        $compatible = false;

        add_action( 'admin_notices', 'pods_version_notice_wp' );
    }

    if ( !pods_version_check( 'php', PODS_PHP_VERSION_MINIMUM ) ) {
        $compatible = false;

        add_action( 'admin_notices', 'pods_version_notice_php' );
    }

    if ( !pods_version_check( 'mysql', PODS_MYSQL_VERSION_MINIMUM ) ) {
        $compatible = false;

        add_action( 'admin_notices', 'pods_version_notice_mysql' );
    }

    return $compatible;
}

/**
 * Show WP notice if WP version is incompatible
 *
 * @return void
 *
 * @since 1.10
 */
function pods_version_notice_wp () {
    global $wp_version;
?>
    <div class="error fade">
        <p>
            <strong><?php _e( 'NOTICE', 'pods' ); ?>:</strong> Pods <?php echo esc_html( PODS_VERSION ); ?> <?php _e( 'requires a minimum of', 'pods' ); ?>
            <strong>WordPress <?php echo esc_html( PODS_WP_VERSION_MINIMUM ); ?>+</strong> <?php _e( 'to function. You are currently running', 'pods' ); ?>
            <strong>WordPress <?php echo esc_html( $wp_version ); ?></strong> - <?php _e( 'Please upgrade your WordPress to continue.', 'pods' ); ?>
        </p>
    </div>
<?php
}

/**
 * Show WP notice if PHP version is incompatible
 *
 * @return void
 *
 * @since 1.10
 */
function pods_version_notice_php () {
?>
    <div class="error fade">
        <p>
            <strong><?php _e( 'NOTICE', 'pods' ); ?>:</strong> Pods <?php echo esc_html( PODS_VERSION ); ?> <?php _e( 'requires a minimum of', 'pods' ); ?>
            <strong>PHP <?php echo esc_html( PODS_PHP_VERSION_MINIMUM ); ?>+</strong> <?php _e( 'to function. You are currently running', 'pods' ); ?>
            <strong>PHP <?php echo esc_html( phpversion() ); ?></strong> - <?php _e( 'Please upgrade (or have your Hosting Provider upgrade it for you) your PHP version to continue.', 'pods' ); ?>
        </p>
    </div>
<?php
}

/**
 * Show WP notice if MySQL version is incompatible
 *
 * @return void
 *
 * @since 1.10
 */
function pods_version_notice_mysql () {
    global $wpdb;
    $mysql = $wpdb->db_version();
?>
    <div class="error fade">
        <p><strong><?php _e( 'NOTICE', 'pods' ); ?>:</strong> Pods <?php echo esc_html( PODS_VERSION ); ?> <?php _e( 'requires a minimum of', 'pods' ); ?>
            <strong>MySQL <?php echo esc_html( PODS_MYSQL_VERSION_MINIMUM ); ?>+</strong> <?php _e( 'to function. You are currently running', 'pods' ); ?>
            <strong>MySQL <?php echo esc_html( $mysql ); ?></strong> - <?php _e( 'Please upgrade (or have your Hosting Provider upgrade it for you) your MySQL version to continue.', 'pods' ); ?>
        </p>
    </div>
<?php
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
 * @return void
 *
 * @since 2.0
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
 * Check if a user has permission to be doing something based on standard permission options
 *
 * @param array $options
 *
 * @return bool Whether the user has permissions
 *
 * @since 2.0.5
 */
function pods_permission ( $options ) {
    global $current_user;

    wp_get_current_user();

    $permission = false;

    if ( isset( $options[ 'options' ] ) )
        $options = $options[ 'options' ];

    if ( pods_is_admin() )
        $permission = true;
    elseif ( 0 == pods_v( 'restrict_role', $options, 0 ) && 0 == pods_v( 'restrict_capability', $options, 0 ) && 0 == pods_v( 'admin_only', $options, 0 ) )
        $permission = true;

    if ( !$permission && 1 == pods_v( 'restrict_role', $options, 0 ) ) {
        $roles = pods_v( 'roles_allowed', $options );

        if ( !is_array( $roles ) )
            $roles = explode( ',', $roles );

        $roles = array_unique( array_filter( $roles ) );

        foreach( $roles as $role ) {
            if ( is_user_logged_in() && in_array( $role, $current_user->roles ) ) {
                $permission = true;

                break;
            }
        }
    }

    if ( !$permission && 1 == pods_v( 'restrict_capability', $options, 0 ) ) {
        $capabilities = pods_v( 'capability_allowed', $options );

        if ( !is_array( $capabilities ) )
            $capabilities = explode( ',', $capabilities );

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

            if ( $must_have_permission && is_user_logged_in() ) {
                $permission = true;

                break;
            }
        }
    }

    return $permission;
}

/**
 * Check if permissions are restricted
 *
 * @param array $options
 *
 * @return bool Whether the permissions are restricted
 *
 * @since 2.3.4
 */
function pods_has_permissions ( $options ) {
    $permission = false;

    if ( isset( $options[ 'options' ] ) )
        $options = $options[ 'options' ];

    if ( 1 == pods_v( 'restrict_role', $options, 0 ) || 1 == pods_v( 'restrict_capability', $options, 0 ) || 1 == pods_v( 'admin_only', $options, 0 ) )
        return true;

    return false;
}

/**
 * A fork of get_page_by_title that excludes items unavailable via access rights (by status)
 *
 * @see get_page_by_title
 *
 * @param string $title Title of item to get
 * @param string $output Optional. Output type. OBJECT, ARRAY_N, or ARRAY_A. Default OBJECT.
 * @param string $type Post Type
 * @param string|array $status Post statuses to include (default is what user has access to)
 *
 * @return WP_Post|null WP_Post on success or null on failure
 *
 * @since 2.3.4
 */
function pods_by_title ( $title, $output = OBJECT, $type = 'page', $status = null ) {
    // @todo support Pod item lookups, not just Post Types

    /**
     * @var $wpdb WPDB
     */
    global $wpdb;

    if ( empty( $status ) ) {
        $status = array(
            'publish'
        );

        if ( current_user_can( 'read_private_' . $type . 's') )
            $status[] = 'private';

        if ( current_user_can( 'edit_' . $type . 's' ) )
            $status[] = 'draft';
    }

    $status = (array) $status;

    $status_sql = ' AND `post_status` IN ( %s' . str_repeat( ', %s', count( $status ) -1 ) . ' )';

    $orderby_sql = ' ORDER BY ( `post_status` = %s ) DESC' . str_repeat( ', ( `post_status` = %s ) DESC', count( $status ) - 1 ) . ', `ID` DESC';

    $prepared = array_merge( array( $title, $type ), $status, $status ); // once for WHERE, once for ORDER BY

    $page = $wpdb->get_var( $wpdb->prepare( "SELECT `ID` FROM `{$wpdb->posts}` WHERE `post_title` = %s AND `post_type` = %s" . $status_sql . $orderby_sql, $prepared ) );

    if ( $page )
        return get_post( pods_v( $page, 'post_id' ), $output );

    return null;
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
 *
 * @since 2.1
 */
function pods_field ( $pod, $id = false, $name = null, $single = false ) {
    // allow for pods_field( 'field_name' );
    if ( null === $name ) {
        $name = $pod;
        $single = (boolean) $id;

        $pod = get_post_type();
        $id = get_the_ID();
    }

    $pod = pods( $pod, $id );

	if ( is_object( $pod ) ) {
		return $pod->field( $name, $single );
	}

	return null;
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
 *
 * @since 2.1
 */
function pods_field_display ( $pod, $id = false, $name = null, $single = false ) {
    // allow for pods_field_display( 'field_name' );
    if ( null === $name ) {
        $name = $pod;
        $single = (boolean) $id;

        $pod = get_post_type();
        $id = get_the_ID();
    }

    $pod = pods( $pod, $id );

	if ( is_object( $pod ) ) {
		return $pod->display( $name, $single );
	}

	return null;
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
 *
 * @since 2.1
 */
function pods_field_raw ( $pod, $id = false, $name = null, $single = false ) {
    // allow for pods_field_raw( 'field_name' );
    if ( null === $name ) {
        $name = $pod;
        $single = (boolean) $id;

        $pod = get_post_type();
        $id = get_the_ID();
    }

    return pods( $pod, $id )->raw( $name, $single );

}

/**
 * Set a cached value
 *
 * @see PodsView::set
 *
 * @param string $key Key for the cache
 * @param mixed $value Value to add to the cache
 * @param int $expires (optional) Time in seconds for the cache to expire, if 0 no expiration.
 * @param string $cache_mode (optional) Decides the caching method to use for the view.
 * @param string $group (optional) Key for the group
 *
 * @return bool|mixed|null|string|void
 *
 * @since 2.0
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
 * @param string $group (optional) Key for the group
 * @param string $callback (optional) Callback function to run to set the value if not cached
 *
 * @return bool|mixed|null|void
 *
 * @since 2.0
 */
function pods_view_get ( $key, $cache_mode = 'cache', $group = '', $callback = null ) {
    require_once( PODS_DIR . 'classes/PodsView.php' );

    return PodsView::get( $key, $cache_mode, $group, $callback );
}

/**
 * Clear a cached value
 *
 * @see PodsView::clear
 *
 * @param string|bool $key Key for the cache
 * @param string $cache_mode (optional) Decides the caching method to use for the view.
 * @param string $group (optional) Key for the group
 *
 * @return bool
 *
 * @since 2.0
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
 * @param string $group (optional) Key for the group
 * @param int $expires (optional) Time in seconds for the cache to expire, if 0 no expiration.
 *
 * @return bool|mixed|null|string|void
 *
 * @since 2.0
 */
function pods_cache_set ( $key, $value, $group = '', $expires = 0) {
    return pods_view_set( $key, $value, $expires, 'cache', $group );
}

/**
 * Get a cached value
 *
 * @see PodsView::get
 *
 * @param string $key Key for the cache
 * @param string $group (optional) Key for the group
 * @param string $callback (optional) Callback function to run to set the value if not cached
 *
 * @return bool
 *
 * @since 2.0
 */
function pods_cache_get ( $key, $group = '', $callback = null ) {
    return pods_view_get( $key, 'cache', $group, $callback );
}

/**
 * Clear a cached value
 *
 * @see PodsView::clear
 *
 * @param string|bool $key Key for the cache
 * @param string $group (optional) Key for the group
 *
 * @return bool|mixed|null|void
 *
 * @since 2.0
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
 * @param int $expires (optional) Time in seconds for the cache to expire, if 0 no expiration.
 *
 * @return bool|mixed|null|string|void
 *
 * @since 2.0
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
 * @param string $callback (optional) Callback function to run to set the value if not cached
 *
 * @return bool|mixed|null|void
 *
 * @since 2.0
 */
function pods_transient_get ( $key, $callback = null ) {
    return pods_view_get( $key, 'transient', '', $callback );
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
 * @since 2.0
 */
function pods_transient_clear ( $key = true ) {
    return pods_view_clear( $key, 'transient' );
}

/**
 * Set a cached value
 *
 * @see PodsView::set
 *
 * @param string $key Key for the cache
 * @param mixed $value Value to add to the cache
 * @param int $expires (optional) Time in seconds for the cache to expire, if 0 no expiration.
 *
 * @return bool|mixed|null|string|void
 *
 * @since 2.3.10
 */
function pods_site_transient_set ( $key, $value, $expires = 0 ) {
    return pods_view_set( $key, $value, $expires, 'site-transient' );
}

/**
 * Get a cached value
 *
 * @see PodsView::get
 *
 * @param string $key Key for the cache
 * @param string $callback (optional) Callback function to run to set the value if not cached
 *
 * @return bool|mixed|null|void
 *
 * @since 2.3.10
 */
function pods_site_transient_get ( $key, $callback = null ) {
    return pods_view_get( $key, 'site-transient', '', $callback );
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
 * @since 2.3.10
 */
function pods_site_transient_clear ( $key = true ) {
    return pods_view_clear( $key, 'site-transient' );
}

/**
 * Set a cached value
 *
 * @see PodsView::set
 *
 * @param string $key Key for the cache
 * @param mixed $value Value to add to the cache
 * @param int $expires (optional) Time in seconds for the cache to expire, if 0 no expiration.
 * @param string $group (optional) Key for the group
 *
 * @return bool|mixed|null|string|void
 *
 * @since 2.3.10
 */
function pods_option_cache_set ( $key, $value, $expires = 0, $group = '' ) {
    return pods_view_set( $key, $value, $expires, 'option-cache', $group );
}

/**
 * Get a cached value
 *
 * @see PodsView::get
 *
 * @param string $key Key for the cache
 * @param string $group (optional) Key for the group
 * @param string $callback (optional) Callback function to run to set the value if not cached
 *
 * @return bool|mixed|null|void
 *
 * @since 2.3.10
 */
function pods_option_cache_get ( $key, $group = '', $callback = null ) {
    return pods_view_get( $key, 'option-cache', $group, $callback );
}

/**
 * Clear a cached value
 *
 * @see PodsView::clear
 *
 * @param string|bool $key Key for the cache
 * @param string $group (optional) Key for the group
 *
 * @return bool
 *
 * @since 2.3.10
 */
function pods_option_cache_clear ( $key = true, $group = '' ) {
    return pods_view_clear( $key, 'option-cache', $group );
}

/**
 * Scope variables and include a template like get_template_part that's child-theme aware
 *
 * @see get_template_part
 *
 * @param string|array $template Template names (see get_template_part)
 * @param array $data Data to scope to the include
 * @param bool $return Whether to return the output (echo by default)
 * @return string|null Template output
 *
 * @since 2.3.9
 */
function pods_template_part ( $template, $data = null, $return = false ) {
    $part = PodsView::get_template_part( $template, $data );

    if ( !$return ) {
        echo $part;

        return null;
    }

    return $part;
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
 * @return array|boolean Pod data or false if unsuccessful
 * @since 2.1
 */
function pods_register_type ( $type, $name, $object = null ) {
    if ( empty( $object ) )
        $object = array();

    if ( !empty( $name ) )
        $object[ 'name' ] = $name;

    return pods_meta()->register( $type, $object );
}

/**
 * Add a new Pod field outside of the DB
 *
 * @see PodsMeta::register_field
 *
 * @param string|array $pod The pod name or array of pod names
 * @param string $name The name of the Pod
 * @param array $object (optional) Pod array, including any 'fields' arrays
 *
 * @return array|boolean Field data or false if unsuccessful
 * @since 2.1
 */
function pods_register_field ( $pod, $name, $field = null ) {
    if ( empty( $field ) )
        $field = array();

    if ( !empty( $name ) )
        $field[ 'name' ] = $name;

    return pods_meta()->register_field( $pod, $field );
}

/**
 * Add a new Pod field type
 *
 * @see PodsForm::register_field_type
 *
 * @param string $type The new field type identifier
 * @param string $file The new field type class file location
 *
 * @return array Field type array
 * @since 2.3
 */
function pods_register_field_type ( $type, $file = null ) {
    return PodsForm::register_field_type( $type, $file );
}

/**
 * Register a related object
 *
 * @param string $name Object name
 * @param string $label Object label
 * @param array $options Object options
 *
 * @return array|boolean Object array or false if unsuccessful
 * @since 2.3
 */
function pods_register_related_object ( $name, $label, $options = null ) {
    return PodsForm::field_method( 'pick', 'register_related_object', $name, $label, $options );
}

/**
 * Require a component (always-on)
 *
 * @param string $component Component ID
 *
 * @return void
 *
 * @since 2.3
 */
function pods_require_component ( $component ) {
    add_filter( 'pods_component_require_' . $component, '__return_true' );
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
 * @return void
 *
 * @since 2.0
 * @link http://pods.io/docs/pods-group-add/
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
 * @since 2.0
 */
function pods_is_plugin_active ( $plugin ) {
    $active = false;

    if ( function_exists( 'is_plugin_active' ) )
        $active = is_plugin_active( $plugin );

    if ( !$active ) {
        $active_plugins = (array) get_option( 'active_plugins', array() );

        if ( in_array( $plugin, $active_plugins ) )
            $active = true;

        if ( !$active && is_multisite() ) {
            $plugins = get_site_option( 'active_sitewide_plugins' );

            if ( isset( $plugins[ $plugin ] ) )
                $active = true;
        }
    }

    return $active;
}

/**
 * Check if Pods no conflict is on or not
 *
 * @param string $object_type
 *
 * @return bool
 *
 * @since 2.3
 */
function pods_no_conflict_check ( $object_type = 'post' ) {
    if ( 'post_type' == $object_type )
        $object_type = 'post';
    elseif ( 'term' == $object_type )
        $object_type = 'taxonomy';

    if ( ! class_exists( 'PodsInit' ) ) 
        pods_init();

    if ( !empty( PodsInit::$no_conflict ) && isset( PodsInit::$no_conflict[ $object_type ] ) && !empty( PodsInit::$no_conflict[ $object_type ] ) )
        return true;

    return false;
}

/**
 * Turn off conflicting / recursive actions for an object type that Pods hooks into
 *
 * @param string $object_type
 * @param string $object
 *
 * @return bool
 *
 * @since 2.0
 */
function pods_no_conflict_on ( $object_type = 'post', $object = null ) {

    if ( 'post_type' == $object_type )
        $object_type = 'post';
    elseif ( 'term' == $object_type )
        $object_type = 'taxonomy';

    if ( ! class_exists( 'PodsInit' ) ) 
        pods_init();

    if ( !empty( PodsInit::$no_conflict ) && isset( PodsInit::$no_conflict[ $object_type ] ) && !empty( PodsInit::$no_conflict[ $object_type ] ) )
        return true;

    if ( !is_object( PodsInit::$meta ) )
        return false;

    $no_conflict = array(
		'filter' => array()
	);

    // Filters = Usually get/update/delete meta functions
    // Actions = Usually insert/update/save/delete object functions
    if ( 'post' == $object_type ) {
		if ( apply_filters( 'pods_meta_handler', true, 'post' ) ) {
            // Handle *_post_meta
			if ( apply_filters( 'pods_meta_handler_get', true, 'post' ) ) {
				$no_conflict[ 'filter' ] = array(
					array( 'get_post_metadata', array( PodsInit::$meta, 'get_post_meta' ), 10, 4 ),
				);
			}

			if ( !pods_tableless() ) {
				$no_conflict[ 'filter' ] = array_merge( $no_conflict[ 'filter' ], array(
					array( 'add_post_metadata', array( PodsInit::$meta, 'add_post_meta' ), 10, 5 ),
					array( 'update_post_metadata', array( PodsInit::$meta, 'update_post_meta' ), 10, 5 ),
					array( 'delete_post_metadata', array( PodsInit::$meta, 'delete_post_meta' ), 10, 5 )
				) );
			}
		}

        $no_conflict[ 'action' ] = array(
            array( 'transition_post_status', array( PodsInit::$meta, 'save_post_detect_new' ), 10, 3 ),
            array( 'save_post', array( PodsInit::$meta, 'save_post' ), 10, 2 )
        );
    }
    elseif ( 'taxonomy' == $object_type ) {
		if ( apply_filters( 'pods_meta_handler', true, 'term' ) ) {
            // Handle *_term_meta
			if ( apply_filters( 'pods_meta_handler_get', true, 'term' ) ) {
				$no_conflict[ 'filter' ] = array_merge( $no_conflict[ 'filter' ], array(
					array( 'get_term_metadata', array( PodsInit::$meta, 'get_term_meta' ), 10, 4 )
				) );
			}
			
			if ( !pods_tableless() ) {
				$no_conflict[ 'filter' ] = array_merge( $no_conflict[ 'filter' ], array(
					array( 'add_term_metadata', array( PodsInit::$meta, 'add_term_meta' ), 10, 5 ),
					array( 'update_term_metadata', array( PodsInit::$meta, 'update_term_meta' ), 10, 5 ),
					array( 'delete_term_metadata', array( PodsInit::$meta, 'delete_term_meta' ), 10, 5 )
				) );
			}

			$no_conflict[ 'action' ] = array(
				array( 'edited_term', array( PodsInit::$meta, 'save_taxonomy' ), 10, 3 ),
				array( 'create_term', array( PodsInit::$meta, 'save_taxonomy' ), 10, 3 )
			);
		}
    }
    elseif ( 'media' == $object_type ) {
		$no_conflict[ 'filter' ] = array(
			array( 'wp_update_attachment_metadata', array( PodsInit::$meta, 'save_media' ), 10, 2 )
		);

		if ( apply_filters( 'pods_meta_handler', true, 'post' ) ) {
            // Handle *_post_meta
			if ( apply_filters( 'pods_meta_handler_get', true, 'post' ) ) {
				$no_conflict[ 'filter' ] = array_merge( $no_conflict[ 'filter' ], array(
					array( 'get_post_metadata', array( PodsInit::$meta, 'get_post_meta' ), 10, 4 )
				) );
			}

			if ( !pods_tableless() ) {
				$no_conflict[ 'filter' ] = array_merge( $no_conflict[ 'filter' ], array(
					array( 'add_post_metadata', array( PodsInit::$meta, 'add_post_meta' ), 10, 5 ),
					array( 'update_post_metadata', array( PodsInit::$meta, 'update_post_meta' ), 10, 5 ),
					array( 'delete_post_metadata', array( PodsInit::$meta, 'delete_post_meta' ), 10, 5 )
				) );
			}

			$no_conflict[ 'action' ] = array();
		}
    }
    elseif ( 'user' == $object_type ) {
		if ( apply_filters( 'pods_meta_handler', true, 'user' ) ) {
            // Handle *_term_meta
			if ( apply_filters( 'pods_meta_handler_get', true, 'user' ) ) {
				$no_conflict[ 'filter' ] = array(
					array( 'get_user_metadata', array( PodsInit::$meta, 'get_user_meta' ), 10, 4 ),
				);
			}

			if ( !pods_tableless() ) {
				$no_conflict[ 'filter' ] = array_merge( $no_conflict[ 'filter' ], array(
					array( 'add_user_metadata', array( PodsInit::$meta, 'add_user_meta' ), 10, 5 ),
					array( 'update_user_metadata', array( PodsInit::$meta, 'update_user_meta' ), 10, 5 ),
					array( 'delete_user_metadata', array( PodsInit::$meta, 'delete_user_meta' ), 10, 5 )
				) );
			}
		}

        $no_conflict[ 'action' ] = array(
            array( 'user_register', array( PodsInit::$meta, 'save_user' ) ),
            array( 'profile_update', array( PodsInit::$meta, 'save_user' ), 10, 2 )
        );
    }
    elseif ( 'comment' == $object_type ) {
		if ( apply_filters( 'pods_meta_handler', true, 'comment' ) ) {
            // Handle *_term_meta
			if ( apply_filters( 'pods_meta_handler_get', true, 'comment' ) ) {
				$no_conflict[ 'filter' ] = array(
					array( 'get_comment_metadata', array( PodsInit::$meta, 'get_comment_meta' ), 10, 4 ),
				);
			}

			if ( !pods_tableless() ) {
				$no_conflict[ 'filter' ] = array_merge( $no_conflict[ 'filter' ], array(
					array( 'add_comment_metadata', array( PodsInit::$meta, 'add_comment_meta' ), 10, 5 ),
					array( 'update_comment_metadata', array( PodsInit::$meta, 'update_comment_meta' ), 10, 5 ),
					array( 'delete_comment_metadata', array( PodsInit::$meta, 'delete_comment_meta' ), 10, 5 )
				) );
			}
		}

        $no_conflict[ 'action' ] = array(
            array( 'pre_comment_approved', array( PodsInit::$meta, 'validate_comment' ), 10, 2 ),
            array( 'comment_post', array( PodsInit::$meta, 'save_comment' ) ),
            array( 'edit_comment', array( PodsInit::$meta, 'save_comment' ) )
        );
    }
    elseif ( 'settings' == $object_type ) {
        $no_conflict[ 'filter' ] = array();

        // @todo Better handle settings conflicts apart from each other
        /*if ( empty( $object ) ) {
            foreach ( PodsMeta::$settings as $setting_pod ) {
                foreach ( $setting_pod[ 'fields' ] as $option ) {
                    $no_conflict[ 'filter' ][] = array( 'pre_option_' . $setting_pod[ 'name' ] . '_' . $option[ 'name' ], array( PodsInit::$meta, 'get_option' ), 10, 1 );
                    $no_conflict[ 'filter' ][] = array( 'pre_update_option_' . $setting_pod[ 'name' ] . '_' . $option[ 'name' ], array( PodsInit::$meta, 'update_option' ), 10, 2 );
                }
            }
        }
        elseif ( isset( PodsMeta::$settings[ $object ] ) ) {
            foreach ( PodsMeta::$settings[ $object ][ 'fields' ] as $option ) {
                $no_conflict[ 'filter' ][] = array( 'pre_option_' . $object . '_' . $option[ 'name' ], array( PodsInit::$meta, 'get_option' ), 10, 1 );
                $no_conflict[ 'filter' ][] = array( 'pre_update_option_' . $object . '_' . $option[ 'name' ], array( PodsInit::$meta, 'update_option' ), 10, 2 );
            }
        }*/
    }

    $conflicted = false;

    foreach ( $no_conflict as $action_filter => $conflicts ) {
        foreach ( $conflicts as $k => $args ) {
            if ( call_user_func_array( 'has_' . $action_filter, array_slice( $args, 0, 2 ) ) ) {
                call_user_func_array( 'remove_' . $action_filter, array_slice( $args, 0, 3 ) );

                $conflicted = true;
            }
            else
                unset( $no_conflict[ $action_filter ][ $k ] );
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
 *
 * @since 2.0
 */
function pods_no_conflict_off ( $object_type = 'post' ) {
    if ( 'post_type' == $object_type )
        $object_type = 'post';
    elseif ( 'term' == $object_type )
        $object_type = 'taxonomy';

    if ( ! class_exists( 'PodsInit' ) ) 
        pods_init();

    if ( empty( PodsInit::$no_conflict ) || !isset( PodsInit::$no_conflict[ $object_type ] ) || empty( PodsInit::$no_conflict[ $object_type ] ) )
        return false;

    if ( !is_object( PodsInit::$meta ) )
        return false;

    $no_conflict = PodsInit::$no_conflict[ $object_type ];

    $conflicted = false;

    foreach ( $no_conflict as $action_filter => $conflicts ) {
        foreach ( $conflicts as $args ) {
            if ( !call_user_func_array( 'has_' . $action_filter, array_slice( $args, 0, 2 ) ) ) {
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

/**
 * Safely start a new session (without whitescreening on certain hosts,
 * which have no session path or isn't writable)
 *
 * @since 2.3.10
 */
function pods_session_start() {

	$save_path = session_save_path();

	// Check if headers were sent
	if ( false !== headers_sent() ) {
		return false;
	}
	// Allow for bypassing Pods session autostarting
	elseif ( defined( 'PODS_SESSION_AUTO_START' ) && !PODS_SESSION_AUTO_START ) {
		return false;
	}
	// Allow for non-file based sessions, like Memcache
	elseif ( 0 === strpos( $save_path, 'tcp://' ) ) {
		// This is OK, but we don't want to check if file_exists on next statement
	}
	// Check if session path exists and can be written to, avoiding PHP fatal errors
	elseif ( empty( $save_path ) || !@file_exists( $save_path ) || !is_writable( $save_path ) ) {
		return false;
	}
	// Check if session ID is already set
	elseif ( '' != session_id() ) {
		return false;
	}

	// Start session
	@session_start();

	return true;

}
