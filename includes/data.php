<?php
/**
 * @package Pods\Global\Functions\Data
 */
/**
 * Filter input and return sanitized output
 *
 * @param mixed $input The string, array, or object to sanitize
 * @param array $params Additional options
 *
 * @return array|mixed|object|string|void
 *
 * @since 1.2.0
 *
 * @see wp_slash
 */
function pods_sanitize( $input, $params = array() ) {

	if ( '' === $input || is_int( $input ) || is_float( $input ) || empty( $input ) ) {
		return $input;
	}

	$output = array();

	$defaults = array(
		'nested' => false,
		'type' => null // %s %d %f etc
	);

	if ( !is_array( $params ) ) {
		$defaults[ 'type' ] = $params;

		$params = $defaults;
	}
	else {
		$params = array_merge( $defaults, (array) $params );
	}

	if ( is_object( $input ) ) {
		$input = get_object_vars( $input );

		$n_params = $params;
		$n_params[ 'nested' ] = true;

		foreach ( $input as $key => $val ) {
			$output[ pods_sanitize( $key ) ] = pods_sanitize( $val, $n_params );
		}

		$output = (object) $output;
	}
	elseif ( is_array( $input ) ) {
		$n_params = $params;
		$n_params[ 'nested' ] = true;

		foreach ( $input as $key => $val ) {
			$output[ pods_sanitize( $key ) ] = pods_sanitize( $val, $n_params );
		}
	}
	elseif ( !empty( $params[ 'type' ] ) && false !== strpos( $params[ 'type' ], '%' ) ) {
		/**
		 * @var $wpdb wpdb
		 */
		global $wpdb;

		$output = $wpdb->prepare( $params[ 'type' ], $output );
	}
	// @todo Switch this full over to esc_sql once we get sanitization sane again in PodsAPI so we *don't* have to unsanitize in various places
	elseif ( function_exists( 'wp_slash' ) ) {
		$output = wp_slash( $input );
	}
	else {
		$output = esc_sql( $input );
	}

	return $output;

}

/**
 * Filter input and return sanitized SQL LIKE output
 *
 * @param mixed $input The string, array, or object to sanitize
 *
 * @return array|mixed|object|string|void
 *
 * @since 2.3.9
 *
 * @see like_escape
 */
function pods_sanitize_like( $input ) {

	if ( '' === $input || is_int( $input ) || is_float( $input ) || empty( $input ) ) {
		return $input;
	}

	$output = array();

	if ( is_object( $input ) ) {
		$input = get_object_vars( $input );

		foreach ( $input as $key => $val ) {
			$output[ $key ] = pods_sanitize_like( $val );
		}

		$output = (object) $output;
	}
	elseif ( is_array( $input ) ) {
		foreach ( $input as $key => $val ) {
			$output[ $key ] = pods_sanitize_like( $val );
		}
	}
	else {
		global $wpdb;

		if ( pods_version_check( 'wp', '4.0' ) ) {
			$output = $wpdb->esc_like( pods_sanitize( $input ) );
		}
		else {
			// like_escape is deprecated in WordPress 4.0
			$output = like_escape( pods_sanitize( $input ) );
		}
	}

	return $output;

}

/**
 * Filter input and return slashed output
 *
 * @param mixed $input The string, array, or object to sanitize
 * @param array $params Additional options
 *
 * @return array|mixed|object|string|void
 *
 * @since 2.3.9
 *
 * @see wp_slash
 */
function pods_slash( $input, $params = array() ) {

	if ( '' === $input || is_int( $input ) || is_float( $input ) || empty( $input ) ) {
		return $input;
	}

	$output = array();

	$defaults = array(
		'type' => null // %s %d %f etc
	);

	if ( !is_array( $params ) ) {
		$defaults[ 'type' ] = $params;

		$params = $defaults;
	}
	else {
		$params = array_merge( $defaults, (array) $params );
	}

	if ( empty( $input ) ) {
		$output = $input;
	}
	elseif ( is_object( $input ) ) {
		$input = get_object_vars( $input );

		foreach ( $input as $key => $val ) {
			$output[ $key ] = pods_slash( $val, $params );
		}

		$output = (object) $output;
	}
	elseif ( is_array( $input ) ) {
		foreach ( $input as $key => $val ) {
			$output[ $key ] = pods_slash( $val, $params );
		}
	}
	elseif ( !empty( $params[ 'type' ] ) && false !== strpos( $params[ 'type' ], '%' ) ) {
		/**
		 * @var $wpdb wpdb
		 */
		global $wpdb;

		$output = $wpdb->prepare( $params[ 'type' ], $output );
	}
	elseif ( function_exists( 'wp_slash' ) ) {
		$output = wp_slash( $input );
	}
	else {
		$output = addslashes( $input );
	}

	return $output;

}

/**
 * Filter input and return unsanitized output
 *
 * @param mixed $input The string, array, or object to unsanitize
 * @param array $params Additional options
 *
 * @return array|mixed|object|string|void
 *
 * @since 1.2.0
 */
function pods_unsanitize( $input, $params = array() ) {

	if ( '' === $input || is_int( $input ) || is_float( $input ) || empty( $input ) ) {
		return $input;
	}

	$output = array();

	if ( empty( $input ) ) {
		$output = $input;
	}
	elseif ( is_object( $input ) ) {
		$input = get_object_vars( $input );

		$n_params = (array) $params;
		$n_params[ 'nested' ] = true;

		foreach ( $input as $key => $val ) {
			$output[ pods_unsanitize( $key ) ] = pods_unsanitize( $val, $n_params );
		}

		$output = (object) $output;
	}
	elseif ( is_array( $input ) ) {
		$n_params             = (array) $params;
		$n_params[ 'nested' ] = true;

		foreach ( $input as $key => $val ) {
			$output[ pods_unsanitize( $key ) ] = pods_unsanitize( $val, $n_params );
		}
	}
	else {
		// @todo Figure out what to do to unescape mysql_real_escape_string
		if ( pods_version_check( 'wp', '3.6' ) ) {
			$output = stripslashes( $input );
		}
		else {
			$output = stripslashes( $input );
		}
	}

	return $output;

}

/**
 * Filter input and return unslashed output
 *
 * @param mixed $input The string, array, or object to unsanitize
 *
 * @return array|mixed|object|string|void
 *
 * @since 2.3.9
 *
 * @see wp_unslash
 */
function pods_unslash( $input ) {

	if ( '' === $input || is_int( $input ) || is_float( $input ) || empty( $input ) ) {
		return $input;
	}

	$output = array();

	if ( empty( $input ) ) {
		$output = $input;
	}
	elseif ( is_object( $input ) ) {
		$input = get_object_vars( $input );

		foreach ( $input as $key => $val ) {
			$output[ $key ] = pods_unslash( $val );
		}

		$output = (object) $output;
	}
	elseif ( is_array( $input ) ) {
		foreach ( $input as $key => $val ) {
			$output[ $key ] = pods_unslash( $val );
		}
	}
	else {
		// @todo Figure out what to do to unescape mysql_real_escape_string
		if ( pods_version_check( 'wp', '3.6' ) ) {
			$output = wp_unslash( $input );
		}
		else {
			$output = stripslashes( $input );
		}
	}

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
		if ( 'l' == $lr ) {
			$output = ltrim( $input, $charlist );
		}
		elseif ( 'r' == $lr ) {
			$output = rtrim( $input, $charlist );
		}
		else {
			$output = trim( $input, $charlist );
		}
	}

	return $output;

}

/**
 * Return a variable (if exists)
 *
 * @param mixed $var The variable name, can also be a modifier for specific types
 * @param string|array|object $type (optional) Super globals, url/url-relative, constants, globals, options, transients, cache, user data, Pod field values, dates
 * @param mixed $default (optional) The default value to set if variable doesn't exist
 * @param bool $strict (optional) Only allow values (must not be empty)
 * @param array $params (optional) Set 'casting'=>true to cast value from $default, 'allowed'=>$allowed to restrict a value to what's allowed
 *
 * @return mixed The variable (if exists), or default value
 * @since 2.3.10
 */
function pods_v( $var = null, $type = 'get', $default = null, $strict = false, $params = array() ) {

	$defaults = array(
		'casting' => false,
		'allowed' => null
	);

	$params = (object) array_merge( $defaults, (array) $params );

	$output = null;

	if ( null === $type || '' === $type ) {
		// Invalid $type
	} elseif ( is_array( $type ) ) {
		if ( isset( $type[ $var ] ) ) {
			$output = $type[ $var ];
		}
	} elseif ( is_object( $type ) ) {
		if ( isset( $type->{$var} ) ) {
			$output = $type->{$var};
		}
	} else {
		$type = strtolower( (string) $type );
		switch ( $type ) {
			case 'get':
				if ( isset( $_GET[ $var ] ) ) {
					$output = pods_unslash( $_GET[ $var ] );
				}
				break;
			case 'post':
				if ( isset( $_POST[ $var ] ) ) {
					$output = pods_unslash( $_POST[ $var ] );
				}
				break;
			case 'request':
				if ( isset( $_REQUEST[ $var ] ) ) {
					$output = pods_unslash( $_REQUEST[ $var ] );
				}
				break;
			case 'url':
			case 'uri':
				$url = parse_url( pods_current_url() );
				$uri = trim( $url[ 'path' ], '/' );
				$uri = array_filter( explode( '/', $uri ) );

				if ( 'first' == $var ) {
					$var = 0;
				} elseif ( 'last' == $var ) {
					$var = - 1;
				}

				if ( is_numeric( $var ) ) {
					$output = ( $var < 0 ) ? pods_v( count( $uri ) + $var, $uri ) : pods_v( $var, $uri );
				}
				break;
			case 'url-relative':
				$url_raw = pods_current_url();
				$prefix  = get_site_url();

				if ( substr( $url_raw, 0, strlen( $prefix ) ) == $prefix ) {
					$url_raw = substr( $url_raw, strlen( $prefix ) + 1, strlen( $url_raw ) );
				}

				$url = parse_url( $url_raw );
				$uri = trim( $url[ 'path' ], '/' );
				$uri = array_filter( explode( '/', $uri ) );

				if ( 'first' == $var ) {
					$var = 0;
				} elseif ( 'last' == $var ) {
					$var = - 1;
				}

				if ( is_numeric( $var ) ) {
					$output = ( $var < 0 ) ? pods_v( count( $uri ) + $var, $uri ) : pods_v( $var, $uri );
				}
				break;
			case 'template-url':
				$output = get_template_directory_uri();
				break;
			case 'stylesheet-url':
				$output = get_stylesheet_directory_uri();
				break;
			case 'site-url':
				$blog_id = $scheme = null;
				$path    = '';

				if ( is_array( $var ) ) {
					if ( isset( $var[ 0 ] ) ) {
						$blog_id = $var[ 0 ];
					} elseif ( isset( $var[ 1 ] ) ) {
						$path = $var[ 1 ];
					} elseif ( isset( $var[ 2 ] ) ) {
						$scheme = $var[ 2 ];
					}
				} else {
					$blog_id = $var;
				}

				$output = get_site_url( $blog_id, $path, $scheme );
				break;
			case 'home-url':
				$blog_id = $scheme = null;
				$path    = '';

				if ( is_array( $var ) ) {
					if ( isset( $var[ 0 ] ) ) {
						$blog_id = $var[ 0 ];
					} elseif ( isset( $var[ 1 ] ) ) {
						$path = $var[ 1 ];
					} elseif ( isset( $var[ 2 ] ) ) {
						$scheme = $var[ 2 ];
					}
				} else {
					$blog_id = $var;
				}

				$output = get_home_url( $blog_id, $path, $scheme );
				break;
			case 'admin-url':
				$blog_id = $scheme = null;
				$path    = '';

				if ( is_array( $var ) ) {
					if ( isset( $var[ 0 ] ) ) {
						$blog_id = $var[ 0 ];
					} elseif ( isset( $var[ 1 ] ) ) {
						$path = $var[ 1 ];
					} elseif ( isset( $var[ 2 ] ) ) {
						$scheme = $var[ 2 ];
					}
				} else {
					$blog_id = $var;
				}

				$output = get_admin_url( $blog_id, $path, $scheme );
				break;
			case 'includes-url':
				$output = includes_url( $var );
				break;
			case 'content-url':
				$output = content_url( $var );
				break;
			case 'plugins-url':
				$path = $plugin = '';

				if ( is_array( $var ) ) {
					if ( isset( $var[ 0 ] ) ) {
						$path = $var[ 0 ];
					} elseif ( isset( $var[ 1 ] ) ) {
						$plugin = $var[ 1 ];
					}
				} else {
					$path = $var;
				}

				$output = plugins_url( $path, $plugin );
				break;
			case 'network-site-url':
				$path   = '';
				$scheme = null;

				if ( is_array( $var ) ) {
					if ( isset( $var[ 0 ] ) ) {
						$path = $var[ 0 ];
					} elseif ( isset( $var[ 1 ] ) ) {
						$scheme = $var[ 1 ];
					}
				} else {
					$path = $var;
				}

				$output = network_site_url( $path, $scheme );
				break;
			case 'network-home-url':
				$path   = '';
				$scheme = null;

				if ( is_array( $var ) ) {
					if ( isset( $var[ 0 ] ) ) {
						$path = $var[ 0 ];
					} elseif ( isset( $var[ 1 ] ) ) {
						$scheme = $var[ 1 ];
					}
				} else {
					$path = $var;
				}

				$output = network_home_url( $path, $scheme );
				break;
			case 'network-admin-url':
				$path   = '';
				$scheme = null;

				if ( is_array( $var ) ) {
					if ( isset( $var[ 0 ] ) ) {
						$path = $var[ 0 ];
					} elseif ( isset( $var[ 1 ] ) ) {
						$scheme = $var[ 1 ];
					}
				} else {
					$path = $var;
				}

				$output = network_admin_url( $path, $scheme );
				break;
			case 'user-admin-url':
				$path   = '';
				$scheme = null;

				if ( is_array( $var ) ) {
					if ( isset( $var[ 0 ] ) ) {
						$path = $var[ 0 ];
					} elseif ( isset( $var[ 1 ] ) ) {
						$scheme = $var[ 1 ];
					}
				} else {
					$path = $var;
				}

				$output = user_admin_url( $path, $scheme );
				break;
			case 'prefix':
				global $wpdb;

				$output = $wpdb->prefix;
				break;
			case 'server':
				if ( ! pods_strict() ) {
					if ( isset( $_SERVER[ $var ] ) ) {
						$output = pods_unslash( $_SERVER[ $var ] );
					} elseif ( isset( $_SERVER[ strtoupper( $var ) ] ) ) {
						$output = pods_unslash( $_SERVER[ strtoupper( $var ) ] );
					}
				}
				break;
			case 'session':
				if ( isset( $_SESSION[ $var ] ) )
					$output = $_SESSION[ $var ];
				break;
			case 'global':
			case 'globals':
				if ( isset( $GLOBALS[ $var ] ) )
					$output = $GLOBALS[ $var ];
				break;
			case 'cookie':
				if ( isset( $_COOKIE[ $var ] ) )
					$output = pods_unslash( $_COOKIE[ $var ] );
				break;
			case 'constant':
				if ( defined( $var ) )
					$output = constant( $var );
				break;
			case 'user':
				if ( is_user_logged_in() ) {
					$user = get_userdata( get_current_user_id() );

					if ( isset( $user->{$var} ) ) {
						$value = $user->{$var};
					} elseif ( 'role' == $var ) {
						$value = '';

						if ( ! empty( $user->roles ) ) {
							$value = array_shift( $user->roles );
						}
					} else {
						$value = get_user_meta( $user->ID, $var );
					}

					if ( is_array( $value ) && ! empty( $value ) ) {
						$output = $value;
					} elseif ( ! is_array( $value ) && 0 < strlen( $value ) ) {
						$output = $value;
					}
				}
				break;
			case 'option':
				$output = get_option( $var, $default );
				break;
			case 'site-option':
				$output = get_site_option( $var, $default );
				break;
			case 'transient':
				$output = get_transient( $var );
				break;
			case 'site-transient':
				$output = get_site_transient( $var );
				break;
			case 'cache':
				if ( isset( $GLOBALS[ 'wp_object_cache' ] ) && is_object( $GLOBALS[ 'wp_object_cache' ] ) ) {
					$group = 'default';
					$force = false;

					if ( ! is_array( $var ) ) {
						$var = explode( '|', $var );
					}

					if ( isset( $var[ 0 ] ) ) {
						if ( isset( $var[ 1 ] ) ) {
							$group = $var[ 1 ];
						}

						if ( isset( $var[ 2 ] ) ) {
							$force = $var[ 2 ];
						}

						$var = $var[ 0 ];

						$output = wp_cache_get( $var, $group, $force );
					}
				}
				break;
			case 'pods-transient':
				$callback = null;

				if ( ! is_array( $var ) ) {
					$var = explode( '|', $var );
				}

				if ( isset( $var[ 0 ] ) ) {
					if ( isset( $var[ 1 ] ) ) {
						$callback = $var[ 1 ];
					}

					$var = $var[ 0 ];

					$output = pods_transient_get( $var, $callback );
				}
				break;
			case 'pods-site-transient':
				$callback = null;

				if ( ! is_array( $var ) ) {
					$var = explode( '|', $var );
				}

				if ( isset( $var[ 0 ] ) ) {
					if ( isset( $var[ 1 ] ) ) {
						$callback = $var[ 1 ];
					}

					$var = $var[ 0 ];

					$output = pods_site_transient_get( $var, $callback );
				}
				break;
			case 'pods-cache':
				if ( isset( $GLOBALS[ 'wp_object_cache' ] ) && is_object( $GLOBALS[ 'wp_object_cache' ] ) ) {
					$group    = 'default';
					$callback = null;

					if ( ! is_array( $var ) ) {
						$var = explode( '|', $var );
					}

					if ( isset( $var[ 0 ] ) ) {
						if ( isset( $var[ 1 ] ) ) {
							$group = $var[ 1 ];
						}

						if ( isset( $var[ 2 ] ) ) {
							$callback = $var[ 2 ];
						}

						$var = $var[ 0 ];

						$output = pods_cache_get( $var, $group, $callback );
					}
				}
				break;
			case 'pods-option-cache':
				$group    = 'default';
				$callback = null;

				if ( ! is_array( $var ) ) {
					$var = explode( '|', $var );
				}

				if ( isset( $var[ 0 ] ) ) {
					if ( isset( $var[ 1 ] ) ) {
						$group = $var[ 1 ];
					}

					if ( isset( $var[ 2 ] ) ) {
						$callback = $var[ 2 ];
					}

					$var = $var[ 0 ];

					$output = pods_option_cache_get( $var, $group, $callback );
				}
				break;
			case 'date':
				$var = explode( '|', $var );

				if ( ! empty( $var ) ) {
					$output = date_i18n( $var[ 0 ], ( isset( $var[ 1 ] ) ? strtotime( $var[ 1 ] ) : false ) );
				}
				break;
			case 'pods':
			case 'pods_display':
				/**
				 * @var $pods Pods
				 */
				global $pods;

				if ( is_object( $pods ) && 'Pods' == get_class( $pods ) ) {
					if ( 'pods' === $type ) {
						$output = $pods->field( $var );

						if ( is_array( $output ) ) {
							$options = array(
								'field'  => $var,
								'fields' => $pods->fields
							);

							$output = pods_serial_comma( $output, $options );
						}
					} elseif ( 'pods_display' === $type ) {
						$output = $pods->display( $var );
					}
				}
				break;
			case 'post_id':
				if ( empty( $var ) ) {
					if ( ! empty( $default ) ) {
						$post_id = $default;
					} else {
						// If no $var and no $default then use current post ID
						$post_id = get_the_ID();
					}
				} else {
					$post_id = $var;
				}
				if ( did_action( 'wpml_loaded' ) ) {
					/* Only call filter if WPML is installed */
					$post_type = get_post_type( $post_id );
					$post_id = apply_filters( 'wpml_object_id', $post_id, $post_type, true );
				} elseif ( function_exists( 'pll_get_post' ) ) {
					$polylang_id = pll_get_post( $post_id );
					if ( ! empty( $polylang_id ) ) {
						$post_id = $polylang_id;
					}
				}
				// Add other translation plugin specific code here

				/**
				 * Filter to override post_id
				 *
				 * Generally used with language translation plugins in order to return the post id of a
				 * translated post
				 *
				 * @param  int $post_id The post ID of current post
				 * @param  mixed $default The default value to set if variable doesn't exist
				 * @param  mixed $var The variable name, can also be a modifier for specific types
				 * @param  bool $strict Only allow values (must not be empty)
				 * @param  array $params Set 'casting'=>true to cast value from $default, 'allowed'=>$allowed to restrict a value to what's allowed
				 *
				 * @since 2.6.6
				 */
				$output = apply_filters( 'pods_var_post_id', $post_id, $default, $var, $strict, $params );
				break;
			default:
				$output = apply_filters( 'pods_var_' . $type, $default, $var, $strict, $params );
		}
	}

	if ( null !== $default ) {
		// Set default
		if ( null === $output ) {
			$output = $default;
		}

		// Casting
		if ( true === $params->casting ) {
			$output = pods_cast( $output, $default );
		}
	}

	// Strict defaults for empty values
	if ( true === $strict ) {
		if ( empty( $output ) ) {
			$output = $default;
		}
	}

	// Allowed values
	if ( null !== $params->allowed ) {
		if ( is_array( $params->allowed ) ) {
			// Not in array and is not the same array
			if ( ! in_array( $output, $params->allowed ) && ( ! is_array( $output ) || $output !== $params->allowed ) ) {
				$output = $default;
			}
		} elseif ( $output !== $params->allowed ) {
			// Value doesn't match
			$output = $default;
		}
	}

	return $output;
}

/**
 * Return a sanitized variable (if exists)
 *
 * @param mixed $var The variable name, can also be a modifier for specific types
 * @param string|array|object $type (optional) Super globals, url/url-relative, constants, globals, options, transients, cache, user data, Pod field values, dates
 * @param mixed $default (optional) The default value to set if variable doesn't exist
 * @param bool $strict (optional) Only allow values (must not be empty)
 * @param array $params (optional) Set 'casting'=>true to cast value from $default, 'allowed'=>$allowed to restrict a value to what's allowed
 *
 * @return mixed The variable (if exists), or default value
 * @since 2.3.10
 *
 * @see pods_v
 */
function pods_v_sanitized( $var = null, $type = 'get', $default = null, $strict = false, $params = array() ) {

	$output = pods_v( $var, $type, $default, $strict, $params );

	$output = pods_sanitize( $output, $params );

	return $output;

}

/**
 * Set a variable
 *
 * @param mixed $value The value to be set
 * @param mixed $var The variable name, or URI segment position / query var name (if $type is 'url')
 * @param string|array|object $type (optional) Super globals, url/url-relative, constants, globals, user data, Pod field values
 *
 * @return mixed Updated URL (if $type is 'url'), $value (if $type is 'constant'), Item ID (if $type is 'pods'), $type, or false if not set
 * @since 2.3.10
 */
function pods_v_set( $value, $var, $type = 'get' ) {

	$ret = false;

	if ( null === $var || '' === $var ) {
		// Invalid $var
	}
	elseif ( null === $type || '' === $type ) {
		// Invalid $type
	}
	elseif ( is_array( $type ) ) {
		$type[ $var ] = $value;

		$ret = $type;
	}
	elseif ( is_object( $type ) ) {
		$type->{$var} = $value;

		$ret = $type;
	}
	else {
		$type = strtolower( $type );

		if ( 'get' == $type ) {
			$_GET[ $var ] = $value;

			$ret = $_GET;
		}
		elseif ( 'post' == $type ) {
			$_POST[ $var ] = $value;

			$ret = $_POST;
		}
		elseif ( 'request' == $type ) {
			$_REQUEST[ $var ] = $value;

			$ret = $_REQUEST;
		}
		elseif ( 'url' == $type ) {
			if ( is_numeric( $var ) && function_exists( 'http_build_url' ) ) {
				$url = parse_url( pods_current_url() );
				$uri = trim( $url[ 'path' ], '/' );
				$uri = array_filter( explode( '/', $uri ) );

				if ( 'first' == $var ) {
					$var = 0;
				}
				elseif ( 'last' == $var ) {
					$var = -1;
				}

				if ( $var < 0 ) {
					$uri[ count( $uri ) + $var ] = $value;
				}
				else {
					$uri[ $var ] = $value;
				}

				$url[ 'path' ] = '/' . implode( '/', $uri ) . '/';
				$url[ 'path' ] = trim( $url[ 'path' ], '/' );

				$ret = http_build_url( $url );
			}
			else {
				$ret = add_query_arg( array( $var => $value ) );
			}
		}
		elseif ( 'server' == $type ) {
			$_SERVER[ $var ] = $value;

			$ret = $_SERVER;
		}
		elseif ( in_array( $type, array( 'global', 'globals' ) ) ) {
			$GLOBALS[ $var ] = $value;

			$ret = $GLOBALS;
		}
		elseif ( 'session' == $type ) {
			// Session start
			pods_session_start();

			$_SESSION[ $var ] = $value;

			$ret = $_SESSION;
		}
		elseif ( 'cookie' == $type && !headers_sent() ) {
			setcookie( $var, $value, time() + 10 * DAY_IN_SECONDS, COOKIEPATH );

			$ret = $_COOKIE;
		}
		elseif ( 'constant' == $type && !defined( $var ) && ( is_scalar( $value ) || null === $value ) ) {
			define( $var, $value );

			$ret = constant( $var );
		}
		elseif ( 'user' == $type && is_user_logged_in() ) {
			$user = get_userdata( get_current_user_id() );

			if ( !pods_version_check( 'wp', '3.5' ) ) {
				$user_data = get_object_vars( $user->data );
			}
			else {
				$user_data = $user->to_array();
			}

			// Role
			if ( 'role' == $var ) {
				$user->set_role( $value );
			}
			// Core field
			elseif ( isset( $user_data[ $var ] ) ) {
				wp_update_user( array( 'ID' => $user->ID, $var => $value ) );
			}
			// Meta field
			else {
				update_user_meta( $user->ID, $var, $value );
			}

			$ret = get_userdata( $user->ID );
		}
		elseif ( 'pods' == $type ) {
			/**
			 * @var $pods Pods
			 */
			global $pods;

			if ( is_object( $pods ) && 'Pods' == get_class( $pods ) && $pods->exists() ) {
				$ret = $pods->save( $var, $value );
			}
		}
		else {
			$ret = apply_filters( 'pods_var_set_' . $type, $value, $var );
		}
	}

	return $ret;

}

/**
 * Return a variable (if exists)
 *
 * @param mixed $var The variable name or URI segment position
 * @param string $type (optional) Super globals, url/url-relative, constants, globals, options, transients, cache, user data, Pod field values, dates
 * @param mixed $default (optional) The default value to set if variable doesn't exist
 * @param mixed $allowed (optional) The value(s) allowed
 * @param bool $strict (optional) Only allow values (must not be empty)
 * @param bool $casting (optional) Whether to cast the value returned like provided in $default
 * @param string $context (optional) All returned values are sanitized unless this is set to 'raw'
 *
 * @return mixed The variable (if exists), or default value
 * @since 1.10.6
 *
 * @deprecated 2.4 Use pods_v() or pods_v_sanitized() instead.
 * @see pods_v_sanitized
 */
function pods_var( $var = 'last', $type = 'get', $default = null, $allowed = null, $strict = false, $casting = false, $context = 'display' ) {

	if ( 'raw' == $context ) {
		$output = pods_v( $var, $type, $default, $strict, array( 'allowed' => $allowed, 'casting' => $casting ) );
	}
	else {
		$output = pods_v_sanitized( $var, $type, $default, $strict, array( 'allowed' => $allowed, 'casting' => $casting ) );
	}

    return $output;

}

/**
 * Return a variable's raw value (if exists)
 *
 * @param mixed $var The variable name or URI segment position
 * @param string $type (optional) Super globals, url/url-relative, constants, globals, options, transients, cache, user data, Pod field values, dates
 * @param mixed $default (optional) The default value to set if variable doesn't exist
 * @param mixed $allowed (optional) The value(s) allowed
 * @param bool $strict (optional) Only allow values (must not be empty)
 * @param bool $casting (optional) Whether to cast the value returned like provided in $default
 *
 * @return mixed The variable (if exists), or default value
 * @since 2.0
 *
 * @deprecated 2.4 Use pods_v() instead.
 * @see pods_v
 */
function pods_var_raw( $var = 'last', $type = 'get', $default = null, $allowed = null, $strict = false, $casting = false ) {

    return pods_v( $var, $type, $default, $strict, array( 'allowed' => $allowed, 'casting' => $casting ) );

}

/**
 * Set a variable
 *
 * @param mixed $value The value to be set
 * @param mixed $var The variable name or URI segment position
 * @param string $type (optional) "url", "get", "post", "request", "server", "session", "cookie", "constant", or "user"
 *
 * @return mixed $value (if set), $type (if $type is array or object), or $url (if $type is 'url')
 * @since 1.10.6
 *
 * @deprecated 2.4 Use pods_v_set() instead.
 * @see pods_v_set
 */
function pods_var_set( $value, $var = 'last', $type = 'url' ) {

	return pods_v_set( $value, $var, $type );

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
 * @since 2.3.10
 *
 * @see add_query_arg
 */
function pods_query_arg( $array = null, $allowed = null, $excluded = null, $url = null ) {

	$array = (array) $array;
	$allowed = (array) $allowed;
	$excluded = (array) $excluded;

	if ( !isset( $_GET ) ) {
		$query_args = array();
	}
	else {
		$query_args = pods_unsanitize( $_GET );
	}

	foreach ( $query_args as $key => $val ) {
		if ( is_array( $val ) && empty( $val ) ) {
			$query_args[ $key ] = false;
		}
		elseif ( !is_array( $val ) && strlen( $val ) < 1 ) {
			$query_args[ $key ] = false;
		}
		elseif ( !empty( $allowed ) ) {
			$allow_it = false;

			foreach ( $allowed as $allow ) {
				if ( $allow == $key ) {
					$allow_it = true;
				}
				elseif ( false !== strpos( $allow, '*' ) && 0 === strpos( $key, trim( $allow, '*' ) ) ) {
					$allow_it = true;
				}
			}

			if ( !$allow_it ) {
				$query_args[ $key ] = false;
			}
		}
	}

	if ( !empty( $excluded ) ) {
		foreach ( $excluded as $exclusion ) {
			if ( isset( $query_args[ $exclusion ] ) && !in_array( $exclusion, $allowed ) ) {
				$query_args[ $exclusion ] = false;
			}
		}
	}

	if ( !empty( $array ) ) {
		foreach ( $array as $key => $val ) {
			if ( null !== $val || false === strpos( $key, '*' ) ) {
				if ( is_array( $val ) && !empty( $val ) ) {
					$query_args[ $key ] = $val;
				}
				elseif ( !is_array( $val ) && 0 < strlen( $val ) ) {
					$query_args[ $key ] = $val;
				}
				elseif ( isset( $query_args[ $key ] ) ) {
					$query_args[ $key ] = false;
				}
			}
			else {
				$key = str_replace( '*', '', $key );

				foreach ( $query_args as $k => $v ) {
					if ( false !== strpos( $k, $key ) ) {
						$query_args[ $k ] = false;
					}
				}
			}
		}
	}

	if ( null === $url ) {
		$url = add_query_arg( $query_args );
	}
	else {
		$url = add_query_arg( $query_args, $url );
	}

	return $url;

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
 *
 * @deprecated 2.4 Use pods_query_arg() instead.
 * @see pods_query_arg
 */
function pods_var_update( $array = null, $allowed = null, $excluded = null, $url = null ) {

	return pods_query_arg( $array, $allowed, $excluded, $url );

}

/**
 * Cast a value as a specific type
 *
 * @param mixed $value
 * @param mixed $cast_from
 *
 * @return bool
 *
 * @since 2.0
 */
function pods_cast( $value, $cast_from = null ) {

	if ( null !== $cast_from ) {
		if ( is_object( $value ) && is_array( $cast_from ) ) {
			$value = get_object_vars( $value );
		}
		elseif ( is_array( $value ) && is_object( $cast_from ) ) {
			$value = (object) $value;
		}
		else {
			settype( $value, gettype( $cast_from ) );
		}
	}

	return $value;

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
        $str = preg_replace( "/([^0-9a-z\-])/", "", strtolower( $str ) );
    else
        $str = urldecode( sanitize_title( strtolower( $str ) ) );

    $str = preg_replace( "/(\-){2,}/", "-", $str );
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
        $pod_id = pods_v_sanitized( 'id', $pod_data, 0 );
        $pod = pods_v_sanitized( 'name', $pod_data );
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
 * Return a lowercase alphanumeric name (use pods_js_name if you want "_" instead of "-" )
 *
 * @param string $orig Input string to clean
 * @param boolean $lower Force lowercase
 * @param boolean $trim_underscores Whether to trim off underscores
 *
 * @return string Sanitized name
 *
 * @since 1.2.0
 */
function pods_clean_name ( $orig, $lower = true, $trim_underscores = false ) {

	$str = trim( $orig );
	$str = preg_replace( '/(\s)/', '_', $str );
	$str = preg_replace( '/([^0-9a-zA-Z\-_])/', '', $str );
	$str = preg_replace( '/(_){2,}/', '_', $str );
	$str = preg_replace( '/(-){2,}/', '-', $str );

	if ( $lower ) {
		$str = strtolower( $str );
	}

	if ( $trim_underscores ) {
		$str = trim( $str, '_' );
	}

	return $str;
}

/**
 * Return a lowercase alphanumeric name (with underscores) for safe Javascript variable names
 *
 * @param string $orig Input string to clean
 * @param boolean $lower Force lowercase
 *
 * @return string Sanitized name
 *
 * @since 2.5.3
 */
function pods_js_name( $orig, $lower = true ) {

	$str = pods_clean_name( $orig, $lower );
	$str = str_replace( '-', '_', $str );

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
 * Use mb_strlen if available, otherwise fallback to strlen
 *
 * @param string $string
 *
 * @return int
 */
function pods_mb_strlen( $string ) {

	if ( function_exists( 'mb_strlen' ) ) {
		return mb_strlen( $string );
	}

	return strlen( $string );

}

/**
 * Use mb_substr if available, otherwise fallback to substr
 *
 * @param string $string
 * @param int $start
 * @param null|int $length
 * @param null|string $encoding
 *
 * @return string
 */
function pods_mb_substr( $string, $start, $length = null, $encoding = null ) {

	if ( function_exists( 'mb_substr' ) ) {
		if ( null === $encoding ) {
			$encoding = mb_internal_encoding();
		}

		return mb_substr( $string, $start, $length, $encoding );
	}

	return substr( $string, $start, $length );

}

/**
 * Evaluate tags like magic tags but through pods_var
 *
 * @param string|array|object $tags String to be evaluated
 * @param bool $sanitize Whether to sanitize tags
 *
 * @return string
 *
 * @version 2.1
 *
 * @see pods_evaluate_tag
 */
function pods_evaluate_tags ( $tags, $sanitize = false ) {

	if ( is_array( $tags ) ) {
		foreach ( $tags as $k => $tag ) {
			$tags[ $k ] = pods_evaluate_tags( $tag, $sanitize );
		}

		return $tags;
	}
	elseif ( is_object( $tags ) ) {
		$tags = get_object_vars( $tags );

		foreach ( $tags as $k => $tag ) {
			$tags[ $k ] = pods_evaluate_tags( $tag, $sanitize );
		}

		$tags = (object) $tags;

		return $tags;
	}

	$callback = 'pods_evaluate_tag';

	if ( true === $sanitize ) {
		$callback = 'pods_evaluate_tag_sanitized';
	}

	return preg_replace_callback( '/({@(.*?)})/m', $callback, (string) $tags );

}

/**
 * Evaluate tag like magic tag but mapped through pods_v_sanitized
 *
 * @param string|array $tag
 *
 * @return string
 *
 * @version 2.1
 *
 * @see pods_evaluate_tag
 */
function pods_evaluate_tag_sanitized( $tag ) {

	return pods_evaluate_tag( $tag, true );

}

/**
 * Evaluate tag like magic tag but mapped through pods_v
 *
 * @param string|array $tag
 * @param bool $sanitize Whether to sanitize tags
 *
 * @return string
 *
 * @version 2.1
 */
function pods_evaluate_tag( $tag, $sanitize = false ) {

	global $wpdb;

	// Handle pods_evaluate_tags
	if ( is_array( $tag ) ) {
		if ( !isset( $tag[ 2 ] ) && strlen( trim( $tag[ 2 ] ) ) < 1 ) {
			return '';
		}

		$tag = $tag[ 2 ];
	}

	$tag = trim( $tag, ' {@}' );
	$tag = explode( '.', $tag );

	if ( empty( $tag ) || !isset( $tag[ 0 ] ) || strlen( trim( $tag[ 0 ] ) ) < 1 ) {
		return '';
	}

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

	$single_supported = array(
		'template-url',
		'stylesheet-url',
		'site-url',
		'home-url',
		'admin-url',
		'includes-url',
		'content-url',
		'plugins-url',
		'network-site-url',
		'network-home-url',
		'network-admin-url',
		'user-admin-url',
		'prefix'
	);

	if ( in_array( $tag[ 0 ], $single_supported ) ) {
		$value = pods_v( '', $tag[ 0 ], '', true );
	}
	elseif ( 1 == count( $tag ) ) {
		$value = pods_v( $tag[ 0 ], 'get', '', true );
	}
	elseif ( 2 == count( $tag ) ) {
		$value = pods_v( $tag[ 1 ], $tag[ 0 ], '', true );
	}

	$value = apply_filters( 'pods_evaluate_tag', $value, $tag );

	if ( is_array( $value ) && 1 == count( $value ) ) {
		$value = current( $value );
	}

	if ( is_array( $value ) ) {
		$value = pods_serial_comma( $value );
	}

	if ( $sanitize ) {
		$value = pods_sanitize( $value );
	}

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

    $defaults = array(
        'field' => $field,
        'fields' => $fields,
        'and' => $and,
        'field_index' => $field_index,
        'separator' => ',',
        'serial' => true
    );

    if ( is_array( $field ) ) {
        $defaults[ 'field' ] = null;

        $params = array_merge( $defaults, $field );
    }
    else
        $params = $defaults;

    $params = (object) $params;

    $simple = false;

    if ( !empty( $params->fields ) && is_array( $params->fields ) && isset( $params->fields[ $params->field ] ) ) {
        $params->field = $params->fields[ $params->field ];

	    $simple_tableless_objects = PodsForm::simple_tableless_objects();

        if ( !empty( $params->field ) && is_array( $params->field ) && in_array( $params->field[ 'type' ], PodsForm::tableless_field_types() ) ) {
            if ( in_array( $params->field[ 'type' ], PodsForm::file_field_types() ) ) {
                if ( null === $params->field_index )
                    $params->field_index = 'guid';
            }
            elseif ( in_array( $params->field[ 'pick_object' ], $simple_tableless_objects ) )
                $simple = true;
            else {
                $table = pods_api()->get_table_info( $params->field[ 'pick_object' ], $params->field[ 'pick_val' ], null, null, $params->field );

                if ( !empty( $table ) ) {
                    if ( null === $params->field_index )
                        $params->field_index = $table[ 'field_index' ];
                }
            }
        }
    }
    else
        $params->field = null;

    if ( $simple && is_array( $params->field ) && !is_array( $value ) && '' !== $value && null !== $value )
        $value = PodsForm::field_method( 'pick', 'simple_value', $params->field[ 'name' ], $value, $params->field );

    if ( !is_array( $value ) )
        return $value;

    if ( null === $params->and )
        $params->and = ' ' . __( 'and', 'pods' ) . ' ';

    $last = '';

    $original_value = $value;

    if ( !empty( $value ) )
        $last = array_pop( $value );

    if ( $simple && is_array( $params->field ) && !is_array( $last ) && '' !== $last && null !== $last )
        $last = PodsForm::field_method( 'pick', 'simple_value', $params->field[ 'name' ], $last, $params->field );

    if ( is_array( $last ) ) {
        if ( null !== $params->field_index && isset( $last[ $params->field_index ] ) )
            $last = $last[ $params->field_index ];
        elseif ( isset( $last[ 0 ] ) )
            $last = $last[ 0 ];
        elseif ( $simple )
            $last = current( $last );
        else
            $last = '';
    }

    if ( !empty( $value ) ) {
        if ( null !== $params->field_index && isset( $original_value[ $params->field_index ] ) )
            return $original_value[ $params->field_index ];
        elseif ( null !== $params->field_index && isset( $value[ $params->field_index ] ) )
            return $value[ $params->field_index ];
        elseif ( !isset( $value[ 0 ] ) )
            $value = array( $value );

        foreach ( $value as $k => $v ) {
            if ( $simple && is_array( $params->field ) && !is_array( $v ) && '' !== $v && null !== $v )
                $v = PodsForm::field_method( 'pick', 'simple_value', $params->field[ 'name' ], $v, $params->field );

            if ( is_array( $v ) ) {
                if ( null !== $params->field_index && isset( $v[ $params->field_index ] ) )
                    $v = $v[ $params->field_index ];
                elseif ( $simple )
                    $v = trim( implode( $params->separator . ' ', $v ), $params->separator . ' ' );
                else {
                    unset( $value[ $k ] );

                    continue;
                }
            }

            $value[ $k ] = $v;
        }

        if ( 1 == count( $value ) || !$params->serial )
            $value = trim( implode( $params->separator . ' ', $value ), $params->separator . ' ' );
        else
            $value = trim( implode( $params->separator . ' ', $value ), $params->separator . ' ' ) . apply_filters( 'pods_serial_comma', $params->separator . ' ', $value, $original_value, $params );

        $value = trim( $value );
        $last = trim( $last );

        if ( 0 < strlen( $value ) && 0 < strlen( $last ) )
            $value = $value . $params->and . $last;
        elseif ( 0 < strlen( $last ) )
            $value = $last;
        else
            $value = '';
    }
    else
        $value = $last;

    $value = trim( $value, $params->separator . ' ' );

    $value = apply_filters( 'pods_serial_comma_value', $value, $original_value, $params );

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
            $list_item->{$args[ 'parent' ]} = (int) pods_v( $args[ 'parent' ], $list_item );

            if ( is_array( $list_item->{$args[ 'parent' ]} ) && isset( $list_item->{$args[ 'parent' ]}[ $args[ 'id' ] ] ) && $parent == $list_item->{$args[ 'parent' ]}[ $args[ 'id' ] ] )
                $list_item->{$args[ 'children' ]} = pods_hierarchical_list_recurse( $list_item->{$args[ 'id' ]}, $list, $args );
            elseif ( $parent == $list_item->{$args[ 'parent' ]} || ( 0 == $depth && $parent == $list_item->{$args[ 'id' ]} ) )
                $list_item->{$args[ 'children' ]} = pods_hierarchical_list_recurse( $list_item->{$args[ 'id' ]}, $list, $args );
            else
                continue;

            $args[ 'found' ][ $k ] = $list_item;
        }
        elseif ( is_array( $list_item ) && isset( $list_item[ $args[ 'id' ] ] ) ) {
            $list_item[ $args[ 'parent' ] ] = (int) pods_v( $args[ 'parent' ], $list_item );

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
