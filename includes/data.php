<?php
/**
 * @package Pods\Global\Functions\Data
 */

use Pods\Whatsit\Field;

/**
 * Filter input and return sanitized output
 *
 * @param mixed $input  The string, array, or object to sanitize
 * @param array $params Additional options
 *
 * @return array|mixed|object|string|void
 *
 * @since 1.2.0
 *
 * @see   wp_slash
 */
function pods_sanitize( $input, $params = array() ) {
	if ( '' === $input || is_int( $input ) || is_float( $input ) || empty( $input ) ) {
		return $input;
	}

	$output = array();

	$defaults = array(
		'nested' => false,
		'type'   => null,
		// %s %d %f etc
	);

	if ( ! is_array( $params ) ) {
		$defaults['type'] = $params;

		$params = $defaults;
	} else {
		$params = array_merge( $defaults, (array) $params );
	}

	if ( is_object( $input ) ) {
		$input = get_object_vars( $input );

		$n_params           = $params;
		$n_params['nested'] = true;

		foreach ( $input as $key => $val ) {
			$output[ pods_sanitize( $key ) ] = pods_sanitize( $val, $n_params );
		}

		$output = (object) $output;
	} elseif ( is_array( $input ) ) {
		$n_params           = $params;
		$n_params['nested'] = true;

		foreach ( $input as $key => $val ) {
			$output[ pods_sanitize( $key ) ] = pods_sanitize( $val, $n_params );
		}
	} elseif ( ! empty( $params['type'] ) && false !== strpos( $params['type'], '%' ) ) {
		/**
		 * @var $wpdb wpdb
		 */
		global $wpdb;

		$output = $wpdb->prepare( $params['type'], $output );
	} elseif ( function_exists( 'wp_slash' ) ) {
		// @todo Switch this full over to esc_sql once we get sanitization sane again in PodsAPI so we *don't* have to unsanitize in various places
		$output = wp_slash( $input );
	} else {
		$output = esc_sql( $input );
	}//end if

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
 * @see   like_escape
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
	} elseif ( is_array( $input ) ) {
		foreach ( $input as $key => $val ) {
			$output[ $key ] = pods_sanitize_like( $val );
		}
	} else {
		global $wpdb;
		$input = pods_unslash( $input );

		$output = pods_sanitize( $wpdb->esc_like( $input ) );
	}

	return $output;
}

/**
 * Filter input and return slashed output
 *
 * @param mixed $input  The string, array, or object to sanitize
 * @param array $params Additional options
 *
 * @return array|mixed|object|string|void
 *
 * @since 2.3.9
 *
 * @see   wp_slash
 */
function pods_slash( $input, $params = array() ) {
	if ( '' === $input || is_int( $input ) || is_float( $input ) || is_bool( $input ) || empty( $input ) ) {
		return $input;
	}

	$output = array();

	$defaults = array(
		'type' => null,
		// %s %d %f etc
	);

	if ( ! is_array( $params ) ) {
		$defaults['type'] = $params;

		$params = $defaults;
	} else {
		$params = array_merge( $defaults, (array) $params );
	}

	if ( empty( $input ) ) {
		$output = $input;
	} elseif ( is_object( $input ) ) {
		$input = get_object_vars( $input );

		foreach ( $input as $key => $val ) {
			$output[ $key ] = pods_slash( $val, $params );
		}

		$output = (object) $output;
	} elseif ( is_array( $input ) ) {
		foreach ( $input as $key => $val ) {
			$output[ $key ] = pods_slash( $val, $params );
		}
	} elseif ( ! empty( $params['type'] ) && false !== strpos( $params['type'], '%' ) ) {
		/**
		 * @var $wpdb wpdb
		 */
		global $wpdb;

		$output = $wpdb->prepare( $params['type'], $output );
	} elseif ( function_exists( 'wp_slash' ) ) {
		$output = wp_slash( $input );
	} else {
		$output = addslashes( $input );
	}//end if

	return $output;
}

/**
 * Filter input and return unsanitized output
 *
 * @param mixed $input  The string, array, or object to unsanitize
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
	} elseif ( is_object( $input ) ) {
		$input = get_object_vars( $input );

		$n_params           = (array) $params;
		$n_params['nested'] = true;

		foreach ( $input as $key => $val ) {
			$output[ pods_unsanitize( $key ) ] = pods_unsanitize( $val, $n_params );
		}

		$output = (object) $output;
	} elseif ( is_array( $input ) ) {
		$n_params           = (array) $params;
		$n_params['nested'] = true;

		foreach ( $input as $key => $val ) {
			$output[ pods_unsanitize( $key ) ] = pods_unsanitize( $val, $n_params );
		}
	} else {
		$output = wp_unslash( $input );
	}//end if

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
 * @see   wp_unslash
 */
function pods_unslash( $input ) {
	if ( '' === $input || is_int( $input ) || is_float( $input ) || empty( $input ) ) {
		return $input;
	}

	$output = array();

	if ( empty( $input ) ) {
		$output = $input;
	} elseif ( is_object( $input ) ) {
		$input = get_object_vars( $input );

		foreach ( $input as $key => $val ) {
			$output[ $key ] = pods_unslash( $val );
		}

		$output = (object) $output;
	} elseif ( is_array( $input ) ) {
		foreach ( $input as $key => $val ) {
			$output[ $key ] = pods_unslash( $val );
		}
	} else {
		$output = wp_unslash( $input );
	}

	return $output;
}

/**
 * Filter input and return sanitized output
 *
 * @param mixed  $input    The string, array, or object to sanitize
 * @param string $charlist (optional) List of characters to be stripped from the input.
 * @param string $lr       Direction of the trim, can either be 'l' or 'r'.
 *
 * @return array|object|string
 * @since 1.2.0
 */
function pods_trim( $input, $charlist = " \t\n\r\0\x0B", $lr = null ) {
	$output = array();

	if ( is_object( $input ) ) {
		$input = get_object_vars( $input );

		foreach ( $input as $key => $val ) {
			$output[ pods_sanitize( $key ) ] = pods_trim( $val, $charlist, $lr );
		}

		$output = (object) $output;
	} elseif ( is_array( $input ) ) {
		foreach ( $input as $key => $val ) {
			$output[ pods_sanitize( $key ) ] = pods_trim( $val, $charlist, $lr );
		}
	} else {
		$args = array( $input );
		if ( null !== $charlist ) {
			$args[] = $charlist;
		}
		if ( 'l' === $lr ) {
			$function = 'ltrim';
		} elseif ( 'r' === $lr ) {
			$function = 'rtrim';
		} else {
			$function = 'trim';
		}
		$output = call_user_func_array( $function, $args );
	}//end if

	return $output;
}

/**
 * Traverse an array or object by array values order or a string (name.name.name).
 *
 * @since 2.7.18
 *
 * @param array|string|int $traverse The traversal names/keys.
 * @param array|object     $value    The value to traverse into.
 *
 * @return mixed
 */
function pods_traverse( $traverse, $value ) {
	if ( ! $traverse && ! is_numeric( $traverse ) ) {
		return $value;
	}

	if ( is_scalar( $value ) ) {
		return null;
	}

	if ( is_object( $value ) ) {
		$value = (array) $value;
	}

	if ( ! is_array( $traverse ) ) {
		$traverse = explode( '.', $traverse );
	}

	$key = array_shift( $traverse );

	if ( ! isset( $value[ $key ] ) ) {
		return null;
	}

	$value = $value[ $key ];

	if ( $traverse ) {
		$value = pods_traverse( $traverse, $value );
	}

	return $value;
}

/**
 * Return a variable (if exists)
 *
 * @param mixed               $var     The variable name, can also be a modifier for specific types
 * @param string|array|object $type    (optional) Super globals, url/url-relative, constants, globals, options,
 *                                     transients, cache, user data, Pod field values, dates
 * @param mixed               $default (optional) The default value to set if variable doesn't exist
 * @param bool                $strict  (optional) Only allow values (must not be empty)
 * @param array               $params  (optional) Set 'casting'=>true to cast value from $default, 'allowed'=>$allowed
 *                                     to restrict a value to what's allowed
 *
 * @return mixed The variable (if exists), or default value
 * @since 2.3.10
 */
function pods_v( $var = null, $type = 'get', $default = null, $strict = false, $params = array() ) {
	$defaults = array(
		'casting' => false,
		'allowed' => null,
		'source'  => null,
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

		if ( $params->source ) {
			// Using keys for faster isset() checks instead of in_array().
			$disallowed_types = [];

			if ( 'magic-tag' === $params->source ) {
				$disallowed_types = [
					'server'              => false,
					'session'             => false,
					'global'              => false,
					'globals'             => false,
					'cookie'              => false,
					'constant'            => false,
					'option'              => false,
					'site-option'         => false,
					'transient'           => false,
					'site-transient'      => false,
					'cache'               => false,
					'pods-transient'      => false,
					'pods-site-transient' => false,
					'pods-cache'          => false,
					'pods-option-cache'   => false,
				];
			}

			/**
			 * Allow filtering the list of disallowed variable types for the source.
			 *
			 * @since 2.9.4
			 *
			 * @param array  $disallowed_types The list of disallowed variable types for the source.
			 * @param string $source           The source calling pods_v().
			 */
			$disallowed_types = apply_filters( "pods_v_disallowed_types_for_source_{$params->source}", $disallowed_types, $params->source );

			if ( isset( $disallowed_types[ $type ] ) ) {
				return $default;
			}
		}

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
			case 'query':
				$output = get_query_var( $var, $default );
				break;
			case 'url':
			case 'uri':
				$url = parse_url( pods_current_url() );
				$uri = trim( $url['path'], '/' );
				$uri = array_filter( explode( '/', $uri ) );

				if ( 'first' === $var ) {
					$var = 0;
				} elseif ( 'last' === $var ) {
					$var = -1;
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
				$uri = trim( $url['path'], '/' );
				$uri = array_filter( explode( '/', $uri ) );

				if ( 'first' === $var ) {
					$var = 0;
				} elseif ( 'last' === $var ) {
					$var = -1;
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
				$blog_id = null;
				$scheme  = null;
				$path    = '';

				if ( is_array( $var ) ) {
					if ( isset( $var[0] ) ) {
						$blog_id = $var[0];
					}
					if ( isset( $var[1] ) ) {
						$path = $var[1];
					}
					if ( isset( $var[2] ) ) {
						$scheme = $var[2];
					}
				} else {
					$blog_id = $var;
				}

				$output = get_site_url( $blog_id, $path, $scheme );
				break;
			case 'home-url':
				$blog_id = null;
				$scheme  = null;
				$path    = '';

				if ( is_array( $var ) ) {
					if ( isset( $var[0] ) ) {
						$blog_id = $var[0];
					}
					if ( isset( $var[1] ) ) {
						$path = $var[1];
					}
					if ( isset( $var[2] ) ) {
						$scheme = $var[2];
					}
				} else {
					$blog_id = $var;
				}

				$output = get_home_url( $blog_id, $path, $scheme );
				break;
			case 'admin-url':
				$blog_id = null;
				$scheme  = null;
				$path    = '';

				if ( is_array( $var ) ) {
					if ( isset( $var[0] ) ) {
						$blog_id = $var[0];
					}
					if ( isset( $var[1] ) ) {
						$path = $var[1];
					}
					if ( isset( $var[2] ) ) {
						$scheme = $var[2];
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
				$path   = '';
				$plugin = '';

				if ( is_array( $var ) ) {
					if ( isset( $var[0] ) ) {
						$path = $var[0];
					}
					if ( isset( $var[1] ) ) {
						$plugin = $var[1];
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
					if ( isset( $var[0] ) ) {
						$path = $var[0];
					}
					if ( isset( $var[1] ) ) {
						$scheme = $var[1];
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
					if ( isset( $var[0] ) ) {
						$path = $var[0];
					}
					if ( isset( $var[1] ) ) {
						$scheme = $var[1];
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
					if ( isset( $var[0] ) ) {
						$path = $var[0];
					}
					if ( isset( $var[1] ) ) {
						$scheme = $var[1];
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
					if ( isset( $var[0] ) ) {
						$path = $var[0];
					}
					if ( isset( $var[1] ) ) {
						$scheme = $var[1];
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
				if ( ! pods_strict( false ) ) {
					if ( isset( $_SERVER[ $var ] ) ) {
						$output = pods_unslash( $_SERVER[ $var ] );
					} elseif ( isset( $_SERVER[ strtoupper( $var ) ] ) ) {
						$output = pods_unslash( $_SERVER[ strtoupper( $var ) ] );
					}
				}
				break;
			case 'session':
				if ( isset( $_SESSION[ $var ] ) ) {
					$output = $_SESSION[ $var ];
				}
				break;
			case 'global':
			case 'globals':
				if ( isset( $GLOBALS[ $var ] ) ) {
					$output = $GLOBALS[ $var ];
				}
				break;
			case 'cookie':
				if ( isset( $_COOKIE[ $var ] ) ) {
					$output = pods_unslash( $_COOKIE[ $var ] );
				}
				break;
			case 'constant':
				if ( defined( $var ) ) {
					$output = constant( $var );
				}
				break;
			case 'user':
				// Prevent deprecation notice from WP.
				if ( 'id' === $var ) {
					$var = 'ID';
				}

				if ( is_user_logged_in() ) {
					$user = get_userdata( get_current_user_id() );

					if ( 'user_pass' === $var || 'user_activation_key' === $var ) {
						$value = '';
					} elseif ( isset( $user->{$var} ) ) {
						$value = $user->{$var};
					} elseif ( 'role' === $var ) {
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
				} elseif ( 'ID' === $var ) {
					// Return 0 when logged out and calling the ID.
					$output = 0;
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
				if ( isset( $GLOBALS['wp_object_cache'] ) && is_object( $GLOBALS['wp_object_cache'] ) ) {
					$group = 'default';
					$force = false;

					if ( ! is_array( $var ) ) {
						$var = explode( '|', $var );
					}

					if ( isset( $var[0] ) ) {
						if ( isset( $var[1] ) ) {
							$group = $var[1];
						}

						if ( isset( $var[2] ) ) {
							$force = $var[2];
						}

						$var = $var[0];

						$output = wp_cache_get( $var, $group, $force );
					}
				}//end if
				break;
			case 'pods-transient':
				$callback = null;

				if ( ! is_array( $var ) ) {
					$var = explode( '|', $var );
				}

				if ( isset( $var[0] ) ) {
					if ( isset( $var[1] ) ) {
						$callback = $var[1];
					}

					$var = $var[0];

					$output = pods_transient_get( $var, $callback );
				}
				break;
			case 'pods-site-transient':
				$callback = null;

				if ( ! is_array( $var ) ) {
					$var = explode( '|', $var );
				}

				if ( isset( $var[0] ) ) {
					if ( isset( $var[1] ) ) {
						$callback = $var[1];
					}

					$var = $var[0];

					$output = pods_site_transient_get( $var, $callback );
				}
				break;
			case 'pods-cache':
				if ( isset( $GLOBALS['wp_object_cache'] ) && is_object( $GLOBALS['wp_object_cache'] ) ) {
					$group    = 'default';
					$callback = null;

					if ( ! is_array( $var ) ) {
						$var = explode( '|', $var );
					}

					if ( isset( $var[0] ) ) {
						if ( isset( $var[1] ) ) {
							$group = $var[1];
						}

						if ( isset( $var[2] ) ) {
							$callback = $var[2];
						}

						$var = $var[0];

						$output = pods_cache_get( $var, $group, $callback );
					}
				}//end if
				break;
			case 'pods-option-cache':
				$group    = 'default';
				$callback = null;

				if ( ! is_array( $var ) ) {
					$var = explode( '|', $var );
				}

				if ( isset( $var[0] ) ) {
					if ( isset( $var[1] ) ) {
						$group = $var[1];
					}

					if ( isset( $var[2] ) ) {
						$callback = $var[2];
					}

					$var = $var[0];

					$output = pods_option_cache_get( $var, $group, $callback );
				}
				break;
			case 'date':
				$var = explode( '|', $var );

				if ( ! empty( $var ) ) {
					$output = date_i18n( $var[0], ( isset( $var[1] ) ? strtotime( $var[1] ) : false ) );
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
								'fields' => $pods->fields,
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

				// Add other translation plugin specific code here
				/**
				 * Filter to override post_id
				 *
				 * Generally used with language translation plugins in order to return the post id of a
				 * translated post
				 *
				 * @param  int   $post_id The post ID of current post
				 * @param  mixed $default The default value to set if variable doesn't exist
				 * @param  mixed $var     The variable name, can also be a modifier for specific types
				 * @param  bool  $strict  Only allow values (must not be empty)
				 * @param  array $params  Set 'casting'=>true to cast value from $default, 'allowed'=>$allowed to restrict a value to what's allowed
				 *
				 * @since 2.6.6
				 */
				$output = apply_filters( 'pods_var_post_id', $post_id, $default, $var, $strict, $params );
				break;
			default:
				$output = apply_filters( "pods_var_{$type}", $default, $var, $strict, $params );
		}//end switch
	}//end if

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
			if ( ! in_array( $output, $params->allowed, true ) && ( ! is_array( $output ) || $output !== $params->allowed ) ) {
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
 * @param mixed               $var     The variable name, can also be a modifier for specific types
 * @param string|array|object $type    (optional) Super globals, url/url-relative, constants, globals, options,
 *                                     transients, cache, user data, Pod field values, dates
 * @param mixed               $default (optional) The default value to set if variable doesn't exist
 * @param bool                $strict  (optional) Only allow values (must not be empty)
 * @param array               $params  (optional) Set 'casting'=>true to cast value from $default, 'allowed'=>$allowed
 *                                     to restrict a value to what's allowed
 *
 * @return mixed The variable (if exists), or default value
 * @since 2.3.10
 *
 * @see   pods_v
 */
function pods_v_sanitized( $var = null, $type = 'get', $default = null, $strict = false, $params = array() ) {
	$output = pods_v( $var, $type, $default, $strict, $params );

	$output = pods_sanitize( $output, $params );

	return $output;
}

/**
 * Set a variable
 *
 * @param mixed               $value The value to be set
 * @param mixed               $var   The variable name, or URI segment position / query var name (if $type is 'url')
 * @param string|array|object $type  (optional) Super globals, url/url-relative, constants, globals, user data, Pod
 *                                   field values
 *
 * @return mixed Updated URL (if $type is 'url'), $value (if $type is 'constant'), Item ID (if $type is 'pods'), $type,
 *               or false if not set
 * @since 2.3.10
 */
function pods_v_set( $value, $var, $type = 'get' ) {
	$ret = false;

	if ( null === $var || '' === $var ) {
		// Invalid $var
	} elseif ( null === $type || '' === $type ) {
		// Invalid $type
	} elseif ( is_array( $type ) ) {
		$type[ $var ] = $value;

		$ret = $type;
	} elseif ( is_object( $type ) ) {
		$type->{$var} = $value;

		$ret = $type;
	} else {
		$type = strtolower( $type );

		if ( 'get' === $type ) {
			$_GET[ $var ] = $value;

			$ret = $_GET;
		} elseif ( 'post' === $type ) {
			$_POST[ $var ] = $value;

			$ret = $_POST;
		} elseif ( 'request' === $type ) {
			$_REQUEST[ $var ] = $value;

			$ret = $_REQUEST;
		} elseif ( 'url' === $type ) {
			if ( is_numeric( $var ) && function_exists( 'http_build_url' ) ) {
				$url = parse_url( pods_current_url() );
				$uri = trim( $url['path'], '/' );
				$uri = array_filter( explode( '/', $uri ) );

				if ( 'first' === $var ) {
					$var = 0;
				} elseif ( 'last' === $var ) {
					$var = - 1;
				}

				if ( $var < 0 ) {
					$uri[ count( $uri ) + $var ] = $value;
				} else {
					$uri[ $var ] = $value;
				}

				$url['path'] = '/' . implode( '/', $uri ) . '/';
				$url['path'] = trim( $url['path'], '/' );

				$ret = http_build_url( $url );
			} else {
				$ret = add_query_arg( array( $var => $value ) );
			}//end if
		} elseif ( 'server' === $type ) {
			$_SERVER[ $var ] = $value;

			$ret = $_SERVER;
		} elseif ( in_array( $type, array( 'global', 'globals' ), true ) ) {
			$GLOBALS[ $var ] = $value;

			$ret = $GLOBALS;
		} elseif ( 'session' === $type ) {
			// Session start
			pods_session_start();

			$_SESSION[ $var ] = $value;

			$ret = $_SESSION;
		} elseif ( 'cookie' === $type && ! headers_sent() ) {
			setcookie( $var, $value, time() + 10 * DAY_IN_SECONDS, COOKIEPATH );

			$ret = $_COOKIE;
		} elseif ( 'constant' === $type && ! defined( $var ) && ( is_scalar( $value ) || null === $value ) ) {
			define( $var, $value );

			$ret = constant( $var );
		} elseif ( 'user' === $type && is_user_logged_in() ) {
			$user = get_userdata( get_current_user_id() );

			$user_data = $user->to_array();

			if ( 'role' === $var ) {
				// Role
				$user->set_role( $value );
			} elseif ( isset( $user_data[ $var ] ) ) {
				// Core field
				wp_update_user( array(
						'ID' => $user->ID,
						$var => $value,
					) );
			} else {
				// Meta field
				update_user_meta( $user->ID, $var, $value );
			}

			$ret = get_userdata( $user->ID );
		} elseif ( 'pods' === $type ) {
			/**
			 * @var $pods Pods
			 */
			global $pods;

			if ( is_object( $pods ) && 'Pods' == get_class( $pods ) && $pods->exists() ) {
				$ret = $pods->save( $var, $value );
			}
		} else {
			$ret = apply_filters( "pods_var_set_{$type}", $value, $var );
		}//end if
	}//end if

	return $ret;
}

/**
 * Return a variable (if exists)
 *
 * @param mixed  $var     The variable name or URI segment position
 * @param string $type    (optional) Super globals, url/url-relative, constants, globals, options, transients, cache,
 *                        user data, Pod field values, dates
 * @param mixed  $default (optional) The default value to set if variable doesn't exist
 * @param mixed  $allowed (optional) The value(s) allowed
 * @param bool   $strict  (optional) Only allow values (must not be empty)
 * @param bool   $casting (optional) Whether to cast the value returned like provided in $default
 * @param string $context (optional) All returned values are sanitized unless this is set to 'raw'
 *
 * @return mixed The variable (if exists), or default value
 * @since      1.10.6
 *
 * @deprecated 2.4.0 Use pods_v() or pods_v_sanitized() instead.
 * @see        pods_v_sanitized
 */
function pods_var( $var = 'last', $type = 'get', $default = null, $allowed = null, $strict = false, $casting = false, $context = 'display' ) {
	if ( 'raw' === $context ) {
		$output = pods_v( $var, $type, $default, $strict, array(
				'allowed' => $allowed,
				'casting' => $casting,
			) );
	} else {
		$output = pods_v_sanitized( $var, $type, $default, $strict, array(
				'allowed' => $allowed,
				'casting' => $casting,
			) );
	}

	return $output;
}

/**
 * Return a variable's raw value (if exists)
 *
 * @param mixed  $var     The variable name or URI segment position
 * @param string $type    (optional) Super globals, url/url-relative, constants, globals, options, transients, cache,
 *                        user data, Pod field values, dates
 * @param mixed  $default (optional) The default value to set if variable doesn't exist
 * @param mixed  $allowed (optional) The value(s) allowed
 * @param bool   $strict  (optional) Only allow values (must not be empty)
 * @param bool   $casting (optional) Whether to cast the value returned like provided in $default
 *
 * @return mixed The variable (if exists), or default value
 * @since      2.0.0
 *
 * @deprecated 2.4.0 Use pods_v() instead.
 * @see        pods_v
 */
function pods_var_raw( $var = 'last', $type = 'get', $default = null, $allowed = null, $strict = false, $casting = false ) {
	return pods_v( $var, $type, $default, $strict, array(
			'allowed' => $allowed,
			'casting' => $casting,
		) );
}

/**
 * Set a variable
 *
 * @param mixed  $value The value to be set
 * @param mixed  $var   The variable name or URI segment position
 * @param string $type  (optional) "url", "get", "post", "request", "server", "session", "cookie", "constant", or "user"
 *
 * @return mixed $value (if set), $type (if $type is array or object), or $url (if $type is 'url')
 * @since      1.10.6
 *
 * @deprecated 2.4.0 Use pods_v_set() instead.
 * @see        pods_v_set
 */
function pods_var_set( $value, $var = 'last', $type = 'url' ) {
	return pods_v_set( $value, $var, $type );
}

/**
 * Create a new URL off of the current one, with updated parameters
 *
 * @param array  $array    Parameters to be set (empty will remove it)
 * @param array  $allowed  Parameters to keep (if empty, all are kept)
 * @param array  $excluded Parameters to always remove
 * @param string $url      URL to base update off of
 *
 * @return mixed
 *
 * @since 2.3.10
 *
 * @see   add_query_arg
 */
function pods_query_arg( $array = null, $allowed = null, $excluded = null, $url = null ) {
	$array    = (array) $array;
	$allowed  = (array) $allowed;
	$excluded = (array) $excluded;

	// Support for globally defined arguments.
	global $pods_query_args;

	if ( empty( $pods_query_args ) ) {
		$pods_query_args = [
			'allowed'  => array(),
			'excluded' => array(),
		];
	}

	// Merge any global args that we need to.
	$allowed  = array_unique( array_merge( $pods_query_args['allowed'], $allowed ) );
	$excluded = array_unique( array_merge( $pods_query_args['excluded'], $excluded ) );

	if ( ! isset( $_GET ) || $url ) {
		$query_args = array();
	} else {
		$query_args = pods_unsanitize( $_GET );
	}

	foreach ( $query_args as $key => $val ) {
		if ( is_array( $val ) && empty( $val ) ) {
			$query_args[ $key ] = false;
		} elseif ( ! is_array( $val ) && '' === $val ) {
			$query_args[ $key ] = false;
		} elseif ( ! empty( $allowed ) ) {
			$allow_it = false;

			foreach ( $allowed as $allow ) {
				if ( $allow === $key ) {
					$allow_it = true;
				} elseif ( false !== strpos( $allow, '*' ) && 0 === strpos( $key, trim( $allow, '*' ) ) ) {
					$allow_it = true;
				}
			}

			if ( ! $allow_it ) {
				$query_args[ $key ] = false;
			}
		}
	}//end foreach

	if ( ! empty( $excluded ) ) {
		foreach ( $excluded as $exclusion ) {
			if ( isset( $query_args[ $exclusion ] ) && ! in_array( $exclusion, $allowed, true ) ) {
				$query_args[ $exclusion ] = false;
			}
		}
	}

	if ( ! empty( $array ) ) {
		foreach ( $array as $key => $val ) {
			$is_value_null = null === $val;

			if ( ! $is_value_null || false === strpos( $key, '*' ) ) {
				$is_value_array = is_array( $val );

				if ( $is_value_array && ! empty( $val ) ) {
					$query_args[ $key ] = $val;
				} elseif ( ! $is_value_null && ! $is_value_array && 0 < strlen( $val ) ) {
					$query_args[ $key ] = $val;
				} else {
					$query_args[ $key ] = false;
				}
			} else {
				$key = str_replace( '*', '', $key );

				foreach ( $query_args as $k => $v ) {
					if ( false !== strpos( $k, $key ) ) {
						$query_args[ $k ] = false;
					}
				}
			}
		}
	}//end if

	if ( null === $url ) {
		$url = add_query_arg( $query_args );
	} else {
		$url = add_query_arg( $query_args, $url );
	}

	return $url;
}

/**
 * Create a new URL off of the current one, with updated parameters
 *
 * @param array  $array    Parameters to be set (empty will remove it)
 * @param array  $allowed  Parameters to keep (if empty, all are kept)
 * @param array  $excluded Parameters to always remove
 * @param string $url      URL to base update off of
 *
 * @return mixed
 *
 * @since      2.0.0
 *
 * @deprecated 2.4.0 Use pods_query_arg() instead.
 * @see        pods_query_arg
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
 * @since 2.0.0
 */
function pods_cast( $value, $cast_from = null ) {
	if ( null !== $cast_from ) {
		if ( is_object( $value ) && is_array( $cast_from ) ) {
			$value = get_object_vars( $value );
		} elseif ( is_array( $value ) && is_object( $cast_from ) ) {
			$value = (object) $value;
		} else {
			settype( $value, gettype( $cast_from ) );
		}
	}

	return $value;
}

/**
 * Create a slug from an input string
 *
 * @param string $orig   Original string.
 * @param bool   $strict Whether to only support 0-9, a-z, A-Z, and dash characters.
 *
 * @return string Sanitized slug
 *
 * @since 1.8.9
 */
function pods_create_slug( $orig, $strict = true ) {
	$str = remove_accents( $orig );
	$str = preg_replace( '/([_ \\/])/', '-', trim( $orig ) );

	if ( $strict ) {
		$str = preg_replace( '/([^0-9a-z\-])/', '', strtolower( $str ) );
	} else {
		$str = urldecode( sanitize_title( strtolower( $str ) ) );
	}

	$str = preg_replace( '/(\-){2,}/', '-', $str );
	$str = trim( $str, '-' );
	$str = apply_filters( 'pods_create_slug', $str, $orig );

	return $str;
}

/**
 * Build a unique slug
 *
 * @param string       $slug        The slug value
 * @param string       $column_name The column name
 * @param string|array $pod         The Pod name or array of Pod data
 * @param int          $pod_id      The Pod ID
 * @param int          $id          The item ID
 * @param object       $obj         (optional)
 *
 * @param bool         $strict
 *
 * @return string The unique slug name
 * @since 1.7.2
 */
function pods_unique_slug( $slug, $column_name, $pod, $pod_id = 0, $id = 0, $obj = null, $strict = true ) {
	$slug = pods_create_slug( $slug, $strict );

	$pod_data = array();

	if ( is_array( $pod ) || $pod instanceof Pods\Whatsit ) {
		$pod_data = $pod;
		$pod_id   = pods_v_sanitized( 'id', $pod_data, 0 );
		$pod      = pods_v_sanitized( 'name', $pod_data );
	}

	$pod_id = absint( $pod_id );
	$id     = absint( $id );

	if ( empty( $pod_data ) ) {
		$pod_data = pods_api()->load_pod( array(
			'id'   => $pod_id,
			'name' => $pod,
		), false );
	}

	if ( empty( $pod_data ) || empty( $pod_id ) || empty( $pod ) ) {
		return $slug;
	}

	if ( 'table' !== $pod_data['storage'] || ! in_array( $pod_data['type'], array( 'pod', 'table' ), true ) ) {
		return $slug;
	}

	$check_sql = "
        SELECT DISTINCT `t`.`{$column_name}` AS `slug`
        FROM `@wp_pods_{$pod}` AS `t`
        WHERE `t`.`{$column_name}` = %s AND `t`.`id` != %d
        LIMIT 1
    ";

	$slug_check = pods_query( array( $check_sql, $slug, $id ), $obj );

	if ( ! empty( $slug_check ) || apply_filters( 'pods_unique_slug_is_bad_flat_slug', false, $slug, $column_name, $pod, $pod_id, $id, $pod_data, $obj ) ) {
		$suffix = 2;

		do {
			$alt_slug = substr( $slug, 0, 200 - ( strlen( $suffix ) + 1 ) ) . "-{$suffix}";

			$slug_check = pods_query( array( $check_sql, $alt_slug, $id ), $obj );

			$suffix ++;
		} while ( ! empty( $slug_check ) || apply_filters( 'pods_unique_slug_is_bad_flat_slug', false, $alt_slug, $column_name, $pod, $pod_id, $id, $pod_data, $obj ) );

		$slug = $alt_slug;
	}

	$slug = apply_filters( 'pods_unique_slug', $slug, $id, $column_name, $pod, $pod_id, $obj );

	return $slug;
}

/**
 * Return a lowercase alphanumeric name (use pods_js_name if you want "_" instead of "-" )
 *
 * @param string  $orig             Input string to clean
 * @param boolean $lower            Force lowercase
 * @param boolean $trim_underscores Whether to trim off underscores
 *
 * @return string Sanitized name
 *
 * @since 1.2.0
 */
function pods_clean_name( $orig, $lower = true, $trim_underscores = false ) {
	if ( null === $orig ) {
		return '';
	}

	$str = trim( (string) $orig );
	$str = remove_accents( $str );
	$str = preg_replace( '/([^0-9a-zA-Z\-_\s])/', '', $str );
	$str = preg_replace( '/(\s_)/', '_', $str );
	$str = preg_replace( '/(\s+)/', '_', $str );
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
 * Return a lowercase alphanumeric name (with underscores) for safe JavaScript variable names.
 *
 * @param string  $orig  Input string to clean.
 * @param boolean $lower Whether to force lowercase.
 *
 * @return string The sanitized name.
 *
 * @since 2.5.3
 */
function pods_js_name( $orig, $lower = true ) {
	$str = pods_clean_name( $orig, $lower );
	$str = str_replace( '-', '_', $str );

	return $str;
}

/**
 * Return a camelCase alphanumeric name for safe JavaScript variable names.
 *
 * @param string $orig Input string to clean.
 *
 * @return string The sanitized name as camelCase.
 *
 * @since 2.8.0
 */
function pods_js_camelcase_name( $orig ) {
	// Clean the name for JS.
	$str = pods_js_name( $orig, false );

	// Replace _ with spaces and then Upper Case the words.
	$str = ucwords( str_replace( '_', ' ', $str ) );

	// Remove the spaces and lower case the firstWord.
	return lcfirst( str_replace( ' ', '', $str ) );
}

/**
 * Get the Absolute Integer of a value
 *
 * @param string $maybeint
 * @param bool   $strict         (optional) Check if $maybeint is a integer.
 * @param bool   $allow_negative (optional)
 *
 * @return integer
 * @since 2.0.0
 */
function pods_absint( $maybeint, $strict = true, $allow_negative = false ) {
	if ( is_null( $maybeint ) ) {
		$maybeint = 0;
	} elseif ( is_bool( $maybeint ) ) {
		$maybeint = (int) $maybeint;
	}

	if ( true === $strict && ! is_numeric( trim( $maybeint ) ) ) {
		return 0;
	}

	if ( false !== $allow_negative ) {
		return intval( $maybeint );
	}

	return absint( $maybeint );
}

/**
 * Functions like str_replace except it will restrict $occurrences
 *
 * @since 2.0
 *
 * @param mixed  $find
 * @param mixed  $replace
 * @param string $string
 * @param int    $occurrences (optional)
 *
 * @return mixed
 */
function pods_str_replace( $find, $replace, $string, $occurrences = - 1 ) {
	if ( is_array( $string ) ) {
		foreach ( $string as $k => $v ) {
			$string[ $k ] = pods_str_replace( $find, $replace, $v, $occurrences );
		}

		return $string;
	} elseif ( is_object( $string ) ) {
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
	} else {
		$find = '/' . preg_quote( $find, '/' ) . '/';
	}
	if ( is_string( $string ) ) {
		return preg_replace( $find, $replace, $string, $occurrences );
	} else {
		// Occasionally we will receive non string values (true, false, null).  Allow those to pass through
		return $string;
	}
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
 * @param string      $string
 * @param int         $start
 * @param null|int    $length
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
 * Evaluate tags like magic tags but through pods_v and sanitize for SQL with an empty double quote if needed.
 *
 * @since 2.7.23
 *
 * @param string|array|object $tags String to be evaluated.
 * @param array               $args {
 *     Function arguments.
 *     @type bool       $sanitize        Whether to sanitize.
 *     @type null|mixed $fallback        The fallback value to use if not set, should already be sanitized.
 *     @type Pods       $pod             Pod to parse the tags through.
 *     @type bool       $use_current_pod Whether to auto-detect the current Pod.
 * }
 *
 * @return string Evaluated string.
 *
 * @see pods_evaluate_tag
 */
function pods_evaluate_tags_sql( $tags, $args = array() ) {
	// The temporary placeholder we will use.
	$placeholder = '__PODS__TMP__EMPTY_VALUE__';

	// Store and overwrite fallback argument.
	$fallback = '""';
	if ( isset( $args['fallback'] ) ) {
		$fallback         = $args['fallback'];
		$args['fallback'] = $placeholder;
	}

	$defaults = array(
		'sanitize' => true,
		'fallback' => $placeholder,
	);

	// Set default arguments to use.
	$args = array_merge( $defaults, $args );

	// Evaluate the magic tags.
	$evaluated = pods_evaluate_tags( $tags, $args );

	$find = array(
		'= ' . $placeholder,
		'=' . $placeholder,
		$placeholder,
	);

	$replace = array(
		'= ' . $fallback,
		'=' . $fallback,
		'',
	);

	// Finish sanitizing the string so it is SQL-safe.
	$sanitized = str_replace( $find, $replace, $evaluated );

	/**
	 * Allow filtering the result of how we evaluate and sanitize the SQL.
	 *
	 * @since 2.7.23
	 *
	 * @param string              $sanitized The evaluated and sanitized string.
	 * @param string              $evaluated The evaluated string.
	 * @param string|array|object $tags      Original string to be evaluated.
	 * @param array               $args      Additional function arguments.
	 */
	return apply_filters( 'pods_evaluate_tags_sql', $sanitized, $evaluated, $tags, $args );
}

/**
 * Evaluate tags like magic tags but through pods_v.
 *
 * @param string|array|object $tags String to be evaluated.
 * @param array               $args {
 *     Function arguments.
 *     @type bool       $sanitize        Whether to sanitize.
 *     @type null|mixed $fallback        The fallback value to use if not set, should already be sanitized.
 *     @type Pods       $pod             Pod to parse the tags through.
 *     @type bool       $use_current_pod Whether to auto-detect the current Pod.
 * }
 *
 * @return string
 *
 * @since 2.1
 *
 * @see pods_evaluate_tag
 */
function pods_evaluate_tags( $tags, $args = array() ) {

	// Back compat.
	if ( ! is_array( $args ) ) {
		$prev_args = array( 'tags', 'sanitize', 'fallback' );
		$args      = func_get_args();
		$args      = array_combine( array_slice( $prev_args, 0, count( $args ) ), $args );
		unset( $args['tags'] );
	}

	if ( is_array( $tags ) ) {
		foreach ( $tags as $k => $tag ) {
			$tags[ $k ] = pods_evaluate_tags( $tag, $args );
		}

		return $tags;
	}

	if ( is_object( $tags ) ) {
		$tags = get_object_vars( $tags );

		// Evaluate array and cast as object.
		$tags = (object) pods_evaluate_tags( $tags, $args );

		return $tags;
	}

	return preg_replace_callback(
		'/({@(.*?)})/m',
		function ( $tag ) use ( $args ) {
			return pods_evaluate_tag( $tag, $args );
		},
		(string) $tags
	);
}

/**
 * Evaluate tag like magic tag but sanitized.
 *
 * @since 2.1
 *
 * @param string|array $tag String to be evaluated.
 * @param array        $args {
 *     Function arguments.
 *     @type null|mixed $fallback        The fallback value to use if not set, should already be sanitized.
 *     @type Pods       $pod             Pod to parse the tags through.
 *     @type bool       $use_current_pod Whether to auto-detect the current Pod.
 * }
 *
 * @return string Evaluated content.
 *
 * @see pods_evaluate_tag
 */
function pods_evaluate_tag_sanitized( $tag, $args = array() ) {
	if ( ! is_array( $args ) ) {
		$args = array();
	}
	$args['sanitize'] = true;
	return pods_evaluate_tag( $tag, $args );
}

/**
 * Evaluate tag like magic tag but mapped through pods_v.
 *
 * @since 2.1
 *
 * @param string|array $tag String to be evaluated.
 * @param array        $args {
 *     Function arguments.
 *     @type bool       $sanitize        Whether to sanitize.
 *     @type null|mixed $fallback        The fallback value to use if not set, should already be sanitized.
 *     @type bool|Pods  $pod             Pod to parse the tags through.
 *     @type bool       $use_current_pod Whether to auto-detect the current Pod.
 * }
 *
 * @return string Evaluated content.
 */
function pods_evaluate_tag( $tag, $args = array() ) {
	$defaults = array(
		'sanitize'        => false,
		'fallback'        => null,
		'pod'             => null,
		'use_current_pod' => false,
	);

	// Back compat.
	if ( ! is_array( $args ) ) {
		$prev_args = array( 'tag', 'sanitize', 'fallback' );
		$args      = func_get_args();
		$args      = array_combine( array_slice( $prev_args, 0, count( $args ) ), $args );
		unset( $args['tag'] );
	}

	$args     = wp_parse_args( $args, $defaults );
	$sanitize = $args['sanitize'];
	$fallback = $args['fallback'];
	$pod      = $args['pod'];

	// Handle pods_evaluate_tags
	if ( is_array( $tag ) ) {
		if ( ! isset( $tag[2] ) && '' === trim( $tag[2] ) ) {
			if ( null === $fallback ) {
				return '';
			}

			return $fallback;
		}

		$tag = $tag[2];
	}

	if ( $args['use_current_pod'] ) {
		$pod = pods();
	}

	// Handle Pod fields.
	// The Pod will call this function without Pod param if no field is found.
	if ( $pod instanceof Pods ) {
		$value = $pod->do_magic_tags( '{@' . $tag . '}' );

		if ( ! $value ) {
			if ( null === $fallback ) {
				return '';
			}

			return $fallback;
		}

		if ( $sanitize ) {
			$value = pods_sanitize( $value );
		}

		return $value;
	}

	$tag = trim( $tag, ' {@}' );
	$tag = explode( ',', $tag );
	$tag = pods_trim( $tag );

	$value  = '';
	$helper = isset( $tag[1] ) ? $tag[1] : null;
	$before = isset( $tag[2] ) ? $tag[2] : '';
	$after  = isset( $tag[3] ) ? $tag[3] : '';

	$tag = explode( '.', $tag[0] );

	if ( empty( $tag ) || ! isset( $tag[0] ) || '' === trim( $tag[0] ) ) {
		if ( null === $fallback ) {
			return '';
		}

		return $fallback;
	}

	// Fix formatting that may be after the first .
	if ( 2 < count( $tag ) ) {
		$first_tag = $tag[0];
		unset( $tag[0] );

		$tag = array(
			$first_tag,
			implode( '.', $tag ),
		);
	}

	$tag = pods_trim( $tag );

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
		'prefix',
	);

	$pods_v_var  = '';
	$pods_v_type = 'get';

	if ( in_array( $tag[0], $single_supported, true ) ) {
		$pods_v_type = $tag[0];
	} elseif ( 1 === count( $tag ) ) {
		$pods_v_var = $tag[0];
	} elseif ( 2 === count( $tag ) ) {
		$pods_v_var  = $tag[1];
		$pods_v_type = $tag[0];
	} else {
		// Some magic tags support traversal.
		$pods_v_var  = array_slice( $tag, 1 );
		$pods_v_type = $tag[0];
	}

	$value = pods_v( $pods_v_var, $pods_v_type, null, false, [
		'source' => 'magic-tag',
	] );

	if ( $helper ) {
		if ( ! $pod instanceof Pods ) {
			$pod = pods();
		}

		$value = $pod->helper( $helper, $value );
	}

	/**
	 * Allow filtering the evaluated tag value.
	 *
	 * @since unknown
	 *
	 * @param mixed      $value    The evaluated tag value.
	 * @param string     $tag      The evaluated tag name.
	 * @param null|mixed $fallback The fallback value to use if not set, should already be sanitized.
	 */
	$value = apply_filters( 'pods_evaluate_tag', $value, $tag, $fallback );

	if ( is_array( $value ) && 1 === count( $value ) ) {
		$value = current( $value );
	}

	if ( is_array( $value ) ) {
		$value = pods_serial_comma( $value );
	}

	if ( null === $value ) {
		$value = '';
	}

	if ( $sanitize ) {
		$value = pods_sanitize( $value );
	}

	if ( null !== $fallback && '' === $value ) {
		$value = $fallback;
	}

	return $before . $value . $after;
}

/**
 * Split an array into human readable text (Item, Item, and Item)
 *
 * @param array  $value
 * @param string $field
 * @param array  $fields
 * @param string $and
 * @param string $field_index
 *
 * @return string
 *
 * @since 2.0.0
 */
function pods_serial_comma( $value, $field = null, $fields = null, $and = null, $field_index = null ) {
	if ( is_object( $value ) ) {
		$value = get_object_vars( $value );
	}

	$defaults = array(
		'field'       => $field,
		'fields'      => $fields,
		'and'         => $and,
		'field_index' => $field_index,
		'separator'   => null,
		'serial'      => true,
	);

	if ( is_array( $field ) ) {
		$defaults['field'] = null;

		$params = array_merge( $defaults, $field );
	} else {
		$params = $defaults;
	}

	$params = (object) $params;

	$simple = false;

	if ( ! empty( $params->fields ) && is_array( $params->fields ) && isset( $params->fields[ $params->field ] ) ) {
		$params->field = $params->fields[ $params->field ];

		if ( 1 === (int) pods_v( 'repeatable', $params->field, 0 ) ) {
			$format = pods_v( 'repeatable_format', $params->field, 'default', true );

			if ( 'default' !== $format ) {

				switch ( $format ) {
					case 'ul':
					case 'ol':
						$value = '<' . $format . '><li>' . implode( '</li><li>', (array) $value ) . '</li></' . $format . '>';
					break;
					case 'br':
						if ( is_array( $value ) ) {
							$value = implode( '<br />', $value );
						}
					break;
					case 'non_serial':
						$params->serial = false;
					break;
					case 'custom':
						$params->serial = false;

						$separator = pods_v( 'repeatable_format_separator', $params->field );

						// Default to comma separator.
						if ( '' === $separator ) {
							$separator = ', ';
						}

						$params->and       = $separator;
						$params->separator = $separator;
					break;
				}
			}
		}

		$params->field = pods_config_for_field( $params->field );

		if ( ! empty( $params->field ) && $params->field->is_relationship() ) {
			if ( $params->field->is_file() ) {
				if ( null === $params->field_index ) {
					$params->field_index = 'guid';
				}
			} elseif ( $params->field->is_simple_relationship() ) {
				$simple = true;
			} elseif ( empty( $params->field_index ) ) {
				$table = $params->field->get_table_info();

				if ( ! empty( $table ) ) {
					$params->field_index = $table['field_index'];
				}
			}
		}
	} else {
		$params->field = null;
	}//end if

	if ( $simple && $params->field && ! is_array( $value ) && '' !== $value && null !== $value ) {
		$value = PodsForm::field_method( 'pick', 'simple_value', $params->field->get_name(), $value, $params->field );
	}

	if ( ! is_array( $value ) ) {
		return $value;
	}

	// If something happens with table info, and this is a single select relationship, avoid letting user pass through.
	if ( isset( $value['user_pass'] ) ) {
		unset( $value['user_pass'] );

		// Since we know this is a single select, just pass display name through as the fallback.
		if ( isset( $value['display_name'] ) ) {
			$value = array( $value['display_name'] );
		}
	}

	$original_value = $value;

	$basic_separator = $params->field && $params->field->is_separator_excluded();

	if ( $basic_separator ) {
		$params->separator = ' ';
		$params->and       = ' ';
		$params->serial    = false;
	}

	if ( in_array( $params->separator, array( '', null ), true ) ) {
		$params->separator = ', ';
	}

	if ( null === $params->and ) {
		$params->and = ' ' . __( 'and', 'pods' ) . ' ';
	}

	/**
	 * Allow filtering the "and" content used for pods_serial_comma.
	 *
	 * @since 2.7.17
	 *
	 * @param string|null $and The "and" content used, return null to disable.
	 * @param string $value    The value input into pods_serial_comma.
	 * @param object $params   The list of the setup parameters for pods_serial_comma.
	 */
	$params->and = apply_filters( 'pods_serial_comma_and', $params->and, $value, $params );

	/**
	 * Allow filtering the "separator" content used for pods_serial_comma.
	 *
	 * @since 2.7.17
	 *
	 * @param string $separator The "separator" content used (default ", ").
	 * @param string $value     The value input into pods_serial_comma.
	 * @param object $params    The list of the setup parameters for pods_serial_comma.
	 */
	$params->separator = apply_filters( 'pods_serial_comma_separator', $params->separator, $value, $params );

	$last = '';

	if ( ! empty( $value ) ) {
		$last = array_pop( $value );
	}

	if ( $simple && ! is_array( $last ) && '' !== $last && null !== $last ) {
		$last = PodsForm::field_method( 'pick', 'simple_value', $params->field['name'], $last, $params->field );
	}

	if ( is_array( $last ) ) {
		if ( null !== $params->field_index && isset( $last[ $params->field_index ] ) ) {
			$last = $last[ $params->field_index ];
		} elseif ( isset( $last[0] ) ) {
			$last = $last[0];
		} elseif ( $simple ) {
			$last = current( $last );
		} else {
			$last = '';
		}
	}

	if ( ! empty( $value ) ) {
		if ( null !== $params->field_index && isset( $original_value[ $params->field_index ] ) ) {
			return $original_value[ $params->field_index ];
		} elseif ( null !== $params->field_index && isset( $value[ $params->field_index ] ) ) {
			return $value[ $params->field_index ];
		} elseif ( ! is_array( $value ) ) {
			$value = array( $value );
		}

		foreach ( $value as $k => $v ) {
			if ( $simple && ! is_array( $v ) && '' !== $v && null !== $v ) {
				$v = PodsForm::field_method( 'pick', 'simple_value', $params->field['name'], $v, $params->field );
			}

			if ( is_array( $v ) ) {
				if ( null !== $params->field_index && isset( $v[ $params->field_index ] ) ) {
					$v = $v[ $params->field_index ];
				} elseif ( $simple ) {
					$v = trim( implode( $params->separator, $v ), $params->separator . ' ' );
				} else {
					unset( $value[ $k ] );

					continue;
				}
			}

			$value[ $k ] = $v;
		}

		$has_serial_comma = $params->serial && 1 < count( $value );

		$value = trim( implode( $params->separator, $value ), $params->separator . ' ' );

		// Add final serial comma.
		if ( $has_serial_comma ) {
			/**
			 * Allow filtering the final serial comma (before the "and") used for pods_serial_comma.
			 *
			 * @since unknown
			 *
			 * @param string $serial_comma   The serial comma content used, return an empty string to disable (default ", ").
			 * @param string $value          The formatted value.
			 * @param string $original_value The original value input into pods_serial_comma.
			 * @param object $params         The list of the setup parameters for pods_serial_comma.
			 */
			$serial_comma = apply_filters( 'pods_serial_comma', $params->separator, $value, $original_value, $params );

			if ( '' !== $serial_comma ) {
				$value .= $serial_comma;
			}
		}

		$value = trim( $value );
		$last  = trim( $last );

		if ( 0 < strlen( $value ) && 0 < strlen( $last ) ) {
			$value = $value . $params->and . $last;
		} elseif ( 0 < strlen( $last ) ) {
			$value = $last;
		} else {
			$value = '';
		}
	} else {
		$value = $last;
	}//end if

	$value = trim( $value, $params->separator . ' ' );

	$value = apply_filters( 'pods_serial_comma_value', $value, $original_value, $params );

	return (string) $value;
}

/**
 * Return a variable if a user is logged in or anonymous, or a specific capability
 *
 * @param mixed        $anon       Variable to return if user is anonymous (not logged in)
 * @param mixed        $user       Variable to return if user is logged in
 * @param string|array $capability Capability or array of Capabilities to check to return $user on
 *
 * @return mixed $user Variable to return if user is logged in (if logged in), otherwise $anon
 *
 * @since 2.0.5
 */
function pods_var_user( $anon = false, $user = true, $capability = null ) {
	$value = $anon;

	if ( is_user_logged_in() ) {
		if ( empty( $capability ) ) {
			$value = $user;
		} else {
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
 * @param array        $args Array of parent, children, and id keys to use
 *
 * @return array|object
 * @since 2.3.0
 */
function pods_hierarchical_list( $list, $args = array() ) {
	if ( empty( $args ) || ( ! is_object( $list ) && ! is_array( $list ) ) ) {
		return $list;
	}

	$defaults = array(
		'id'            => 'id',
		'parent'        => 'parent',
		'children'      => 'children',
		'orphans'       => true,
		'found'         => array(),
		'list'          => array(),
		'current_depth' => - 1,
	);

	$args = array_merge( $defaults, (array) $args );

	$list = pods_hierarchical_list_recurse( 0, $list, $args );

	return $list;
}

/**
 * Recurse list of items and make it hierarchical
 *
 * @param int          $parent Parent ID
 * @param array|object $list   List of items
 * @param array        $args   Array of parent, children, and id keys to use
 *
 * @return array|object
 * @since 2.3.0
 */
function pods_hierarchical_list_recurse( $parent, $list, &$args ) {
	$new = array();

	$object = false;

	if ( is_object( $list ) ) {
		$object = true;
		$list   = get_object_vars( $list );
	}

	$args['current_depth'] ++;

	$depth = $args['current_depth'];

	if ( 0 == $depth ) {
		$args['list'] = $list;
	}

	foreach ( $list as $k => $list_item ) {
		if ( is_object( $list_item ) && isset( $list_item->{$args['id']} ) ) {
			$list_item->{$args['parent']} = (int) pods_v( $args['parent'], $list_item );

			if ( is_array( $list_item->{$args['parent']} ) && isset( $list_item->{$args['parent']}[ $args['id'] ] ) && $parent == $list_item->{$args['parent']}[ $args['id'] ] ) {
				$list_item->{$args['children']} = pods_hierarchical_list_recurse( $list_item->{$args['id']}, $list, $args );
			} elseif ( $parent == $list_item->{$args['parent']} || ( 0 == $depth && $parent == $list_item->{$args['id']} ) ) {
				$list_item->{$args['children']} = pods_hierarchical_list_recurse( $list_item->{$args['id']}, $list, $args );
			} else {
				continue;
			}

			$args['found'][ $k ] = $list_item;
		} elseif ( is_array( $list_item ) && isset( $list_item[ $args['id'] ] ) ) {
			$list_item[ $args['parent'] ] = (int) pods_v( $args['parent'], $list_item );

			if ( is_array( $list_item[ $args['parent'] ] ) && isset( $list_item[ $args['parent'] ][ $args['id'] ] ) && $parent == $list_item[ $args['parent'] ][ $args['id'] ] ) {
				$list_item[ $args['children'] ] = pods_hierarchical_list_recurse( $list_item[ $args['id'] ], $list, $args );
			} elseif ( $parent == $list_item[ $args['parent'] ] || ( 0 == $depth && $parent == $list_item[ $args['id'] ] ) ) {
				$list_item[ $args['children'] ] = pods_hierarchical_list_recurse( $list_item[ $args['id'] ], $list, $args );
			} else {
				continue;
			}

			$args['found'][ $k ] = $list_item;
		} else {
			continue;
		}//end if

		$new[ $k ] = $list_item;

		$args['current_depth'] = $depth;
	}//end foreach

	if ( 0 == $depth && empty( $new ) && ! empty( $list ) ) {
		$first = current( array_slice( $list, 0, 1 ) );

		$new_parent = 0;

		$args['current_depth'] = - 1;

		if ( is_object( $first ) && isset( $first->{$args['parent']} ) ) {
			$new_parent = (int) $first->{$args['parent']};
		} elseif ( is_array( $first ) && isset( $first[ $args['parent'] ] ) ) {
			$new_parent = (int) $first[ $args['parent'] ];
		}

		if ( ! empty( $new_parent ) ) {
			$new = pods_hierarchical_list_recurse( $new_parent, $list, $args );
		}
	}

	if ( 0 == $depth ) {
		$orphans = array();

		foreach ( $args['list'] as $k => $list_item ) {
			if ( ! isset( $args['found'][ $k ] ) ) {
				$orphans[ $k ] = $list_item;
			}
		}

		if ( ! empty( $orphans ) ) {
			foreach ( $orphans as $orphan ) {
				$new[] = $orphan;
			}
		}
	}

	if ( $object ) {
		$new = (object) $new;
	}

	return $new;
}

/**
 * Take a one-level list of items and make it hierarchical for <select>
 *
 * @param array|object $list List of items
 * @param array        $args Array of index, parent, children, id, and prefix keys to use
 *
 * @return array|object
 * @internal param string $children_key Key to recurse children into
 *
 * @since    2.3.0
 */
function pods_hierarchical_select( $list, $args = array() ) {
	$object = false;

	if ( is_object( $list ) ) {
		$object = true;
		$list   = get_object_vars( $list );
	}

	$list = pods_hierarchical_list( $list, $args );

	$defaults = array(
		'index'    => 'name',
		'children' => 'children',
		'prefix'   => '&nbsp;&nbsp;&nbsp;',
	);

	$args = array_merge( $defaults, (array) $args );

	$list = pods_hierarchical_select_recurse( $list, $args, 0 );

	if ( $object ) {
		$list = (object) $list;
	}

	return $list;
}

/**
 * Recurse list of hierarchical data
 *
 * @param array $items Items to recurse.
 * @param array $args  Array of children and prefix keys to use.
 * @param int   $depth Current depth of recursion.
 *
 * @return array
 * @internal param array|object $list List of items
 * @internal param string $children_key Key to recurse children into
 *
 * @see      pods_hierarchical_select
 * @since    2.3.0
 */
function pods_hierarchical_select_recurse( $items, $args, $depth = 0 ) {
	$data = array();

	foreach ( $items as $k => $v ) {
		$object = false;

		if ( is_object( $v ) ) {
			$object = true;
			$v      = get_object_vars( $v );
		}

		if ( isset( $v[ $args['index'] ] ) ) {
			$v[ $args['index'] ] = ( 0 < $depth ? str_repeat( $args['prefix'], $depth ) : '' ) . $v[ $args['index'] ];
		}

		$children = array();

		if ( isset( $v[ $args['children'] ] ) ) {
			if ( ! empty( $v[ $args['children'] ] ) ) {
				$children = pods_hierarchical_select_recurse( $v[ $args['children'] ], $args, ( $depth + 1 ) );
			}

			unset( $v[ $args['children'] ] );
		}

		if ( $object ) {
			$v = (object) $v;
		}

		$data[ $k ] = $v;

		if ( ! empty( $children ) ) {
			foreach ( $children as $ck => $cv ) {
				$data[ $ck ] = $cv;
			}
		}
	}//end foreach

	return $data;
}

/**
 * Filters a list of objects or arrays, based on a set of key => value arguments.
 *
 * @param array|object $list     An array or object, with objects/arrays to filter
 * @param array        $args     An array of key => value arguments to match against each object
 * @param string       $operator The logical operation to perform:
 *                               'AND' means all elements from the array must match;
 *                               'OR' means only one element needs to match;
 *                               'NOT' means no elements may match.
 *                               The default is 'AND'.
 *
 * @see   wp_list_filter
 * @return array
 * @since 2.3.0
 */
function pods_list_filter( $list, $args = array(), $operator = 'AND' ) {
	if ( empty( $args ) ) {
		return $list;
	}

	$data = $list;

	$object = false;

	if ( is_object( $data ) ) {
		$object = true;
		$data   = get_object_vars( $data );
	}

	$filtered = wp_list_filter( $data, $args, $operator );

	if ( $object ) {
		$filtered = (object) $filtered;
	}

	return $filtered;
}

/**
 * Clean extra line breaks to prevent empty <p></p> when it eventually goes into wpautop().
 *
 * @since 2.8.0
 *
 * @param string $content The content to be cleaned up.
 *
 * @return string The content that has been cleaned up.
 */
function pods_clean_linebreaks( $content ) {
	// Replace \n\n\n (or more) with \n\n.
	$content = preg_replace( '/(\n+[ \t]*\n+[ \t]*\n+)+/m', "\n\n", $content );

	// Replace extra whitespace at the end of lines.
	$content = preg_replace( '/([ \t]+\n)/m', "\n", $content );

	if ( ! $content || ! is_string( $content ) ) {
		return '';
	}

	return $content;
}

add_filter( 'pods_template_content', 'pods_clean_linebreaks' );

/**
 * Convert the value from a boolean to an integer.
 *
 * @since 2.8.2
 *
 * @param bool|mixed $value The value to convert from boolean to integer.
 *
 * @return int|mixed The value as an integer if it was boolean, or the value as it was passed in.
 */
function pods_bool_to_int( $value ) {
	if ( ! is_bool( $value ) ) {
		return $value;
	}

	return (int) $value;
}

/**
 * Check whether the value is truthy. Handles null, boolean, integer, float, and string validation.
 *
 * Strings will check for "1", "true", "on", "yes", and "y".
 *
 * @since 2.9.13
 *
 * @param null|bool|int|float|string $value The value to check.
 *
 * @return bool Whether the value is truthy.
 */
function pods_is_truthy( $value ) {
	// Check for null.
	if ( is_null( $value ) ) {
		return false;
	}

	// Check boolean for true.
	if ( is_bool( $value ) ) {
		return true === $value;
	}

	// Check integer / float for 1.
	if ( is_int( $value ) || is_float( $value ) ) {
		return 1 === $value;
	}

	// We only support strings from this point forward.
	if ( ! is_string( $value ) ) {
		return false;
	}

	// Normalize the string to lowercase.
	$value = trim( strtolower( $value ) );

	// This is the list of strings we will support as truthy.
	$supported_strings = [
		'1'    => true,
		'true' => true,
		'on'   => true,
		'yes'  => true,
		'y'    => true,
	];

	return isset( $supported_strings[ $value ] );
}

/**
 * Check whether the value is falsey. Handles null, boolean, integer, float, and string validation.
 *
 * Strings will check for "0", "false", "off", "no", and "n".
 *
 * Note: If the variable type is not supported, this will always return false as it cannot be validated as falsey.
 *
 * @since 2.9.13
 *
 * @param null|bool|int|float|string $value The value to check.
 *
 * @return bool Whether the value is falsey.
 */
function pods_is_falsey( $value ) {
	// Check for null.
	if ( is_null( $value ) ) {
		return true;
	}

	// Check boolean for false.
	if ( is_bool( $value ) ) {
		return false === $value;
	}

	// Check integer / float for 0.
	if ( is_int( $value ) || is_float( $value ) ) {
		return 0 === $value;
	}

	// We only support strings from this point forward.
	if ( ! is_string( $value ) ) {
		/*
		 * This is a falsey check but it seems that if we are checking specifically for a falsey,
		 * then this cannot be validated so it's not falsey.
		 */
		return false;
	}

	// Normalize the string to lowercase.
	$value = trim( strtolower( $value ) );

	// This is the list of strings we will support as falsey.
	$supported_strings = [
		'0'     => true,
		'false' => true,
		'off'   => true,
		'no'    => true,
		'n'     => true,
	];

	return isset( $supported_strings[ $value ] );
}

/**
 * Make replacements to a string using key=>value pairs.
 *
 * @since 2.8.11
 *
 * @param string|array|mixed $value        The value to do replacements on.
 * @param array              $replacements The key=>value replacements to make.
 *
 * @return string|array|mixed The value with the replacements made.
 */
function pods_replace_keys_to_values( $value, $replacements ) {
	if ( is_array( $value ) ) {
		return array_map( 'pods_replace_keys_to_values', $value );
	}

	if ( ! is_string( $value ) ) {
		return $value;
	}

	$replacements_prepared = array_map(
		static function( $replacement ) {
			return preg_quote( $replacement, '/' );
		},
		array_keys( $replacements )
	);

	$replacements_prepared = implode( '|', $replacements_prepared );

	$pattern = '/(?<!\\\\)(' . $replacements_prepared . ')/';

	return preg_replace_callback(
		$pattern,
		static function( $data ) use ( $replacements ) {
			return $replacements[ $data[0] ];
		},
		$value
	);
}

/**
 * Validate that a file path is safe and within the expected path(s).
 *
 * @since 2.8.18
 *
 * @param string            $path           The file path.
 * @param null|array|string $paths_to_check The list of path types to check, defaults to just checking 'pods'.
 *                                          Available: 'pods', 'plugins', 'content', 'theme', 'abspath',
 *                                          or 'all' to check all supported paths.
 *
 * @return false|string False if the path was not allowed or did not exist, otherwise it returns the normalized path.
 */
function pods_validate_safe_path( $path, $paths_to_check = null ) {
	static $available_checks;

	if ( null === $paths_to_check ) {
		$paths_to_check = [
			'pods',
		];
	}

	if ( ! $available_checks ) {
		$available_checks = [
			'pods'    => realpath( PODS_DIR ),
			'plugins' => [
				realpath( WP_PLUGIN_DIR ),
				realpath( WPMU_PLUGIN_DIR ),
			],
			'content' => realpath( WP_CONTENT_DIR ),
			'theme'   => [
				realpath( get_stylesheet_directory() ),
				realpath( get_template_directory() ),
			],
			'abspath' => realpath( ABSPATH ),
		];

		$available_checks['plugins'] = array_unique( array_filter( $available_checks['plugins'] ) );
		$available_checks['theme']   = array_unique( array_filter( $available_checks['theme'] ) );
		$available_checks            = array_filter( $available_checks );
	}

	if ( 'all' === $paths_to_check ) {
		$paths_to_check = array_keys( $available_checks );
	}

	if ( empty( $paths_to_check ) ) {
		return false;
	}

	$path = trim( str_replace( '\\', '/', (string) $path ) );
	$path = str_replace( '/', DIRECTORY_SEPARATOR, $path );

	$match_count = 1;

	// Replace the ../ usage as many times as it may need to be replaced.
	while ( $match_count ) {
		$path = str_replace( '..' . DIRECTORY_SEPARATOR, '', $path, $match_count );
	}

	$real_path = realpath( $path );

	$path_match = false;

	foreach ( $paths_to_check as $check_type ) {
		if ( ! isset( $available_checks[ $check_type ] ) ) {
			continue;
		}

		$check_type_paths = (array) $available_checks[ $check_type ];

		$is_theme = 'theme' === $check_type;

		foreach ( $check_type_paths as $path_to_check ) {
			if ( $real_path && 0 === strpos( $real_path, $path_to_check ) && file_exists( $real_path ) ) {
				// Check the path starts with the one we are checking for and that the file exists.
				$path_match = true;

				$path = $real_path;

				break;
			} elseif ( $is_theme ) {
				// Check the theme directories.
				$path_localized_for_theme = trim( $path, DIRECTORY_SEPARATOR );

				// Confirm the file exists.
				if ( file_exists( $path_to_check . DIRECTORY_SEPARATOR . $path_localized_for_theme ) ) {
					$path_match = true;

					$path = $path_to_check . DIRECTORY_SEPARATOR . $path_localized_for_theme;

					break;
				}
			}
		}
	}

	if ( ! $path_match ) {
		return false;
	}

	return $path;
}

/**
 * Maybe sleep and help avoid hitting memory limit.
 *
 * @since 2.8.18
 *
 * @param int $sleep_time The amount of seconds to sleep (if sleep is needed).
 * @param int $after      The number of triggers needed to run the logic.
 */
function pods_maybe_clean_memory( $sleep_time = 0, $after = 100 ) {
	static $counter = 0;

	$counter++;

	if ( $after === $counter ) {
		$counter = 0;

		pods_clean_memory( $sleep_time );
	}
}

/**
 * Sleep and help avoid hitting memory limit.
 *
 * @since 2.8.18
 *
 * @param int $sleep_time The amount of seconds to sleep (if sleep is needed).
 */
function pods_clean_memory( $sleep_time = 0 ) {
	if ( 0 < $sleep_time ) {
		sleep( $sleep_time );
	}

	global $wpdb, $wp_object_cache;

	$wpdb->queries = [];

	if ( ! is_object( $wp_object_cache ) ) {
		return;
	}

	$wp_object_cache->group_ops      = [];
	$wp_object_cache->stats          = [];
	$wp_object_cache->memcache_debug = [];
	$wp_object_cache->cache          = [];

	if ( is_callable( $wp_object_cache, '__remoteset' ) ) {
		call_user_func( [ $wp_object_cache, '__remoteset' ] ); // important
	}
}

/**
 * Get the host from a URL.
 *
 * @since 2.9.0
 *
 * @param string $url The URL to get the host from.
 *
 * @return string The host if found, otherwise the URL.
 */
function pods_host_from_url( $url ) {
	$url_parsed = wp_parse_url( $url );

	// Check if we have a valid URL.
	if ( empty( $url_parsed ) || count( $url_parsed ) < 2 ) {
		return esc_html( $url );
	}

	// Check if we should remove the www from the host.
	if ( 0 === strpos( $url_parsed['host'], 'www.' ) ) {
		$url_parsed['host'] = substr( $url_parsed['host'], 4 );
	}

	return esc_html( $url_parsed['host'] );
}

/**
 * Clone a list of objects.
 *
 * @since 2.9.12
 *
 * @param object[] $objects The list of objects to clone.
 *
 * @return object[] The cloned list of objects.
 */
function pods_clone_objects( $objects ) {
	return array_map( 'pods_clone_object', $objects );
}

/**
 * Clone an object.
 *
 * @since 2.9.12
 *
 * @param object $object The object to clone.
 *
 * @return object The cloned object.
 */
function pods_clone_object( $object ) {
	return clone $object;
}

/**
 * Get the item object based on object type.
 *
 * @param int    $item_id     The item ID.
 * @param string $object_type The object type.
 *
 * @return WP_Post|WP_Term|WP_User|WP_Comment|null The item object or null if not found.
 */
function pods_get_item_object( $item_id, $object_type ) {
	$object = null;

	switch ( $object_type ) {
		case 'post':
		case 'post_type':
		case 'media':
			$object = get_post( $item_id );

			break;
		case 'term':
		case 'taxonomy':
			$object = get_term( $item_id );

			break;
		case 'user':
			$object = get_userdata( $item_id );

			break;
		case 'comment':
			$object = get_comment( $item_id );

			break;
	}

	if ( is_object( $object ) && ! is_wp_error( $object ) ) {
		return $object;
	}

	return null;
}

/**
 * Filters text content and strips out disallowed HTML.
 *
 * This function makes sure that only the allowed HTML element names, attribute
 * names, attribute values, and HTML entities will occur in the given text string.
 *
 * This function expects unslashed data.
 *
 * @see wp_kses() for the original code for this.
 *
 * @since 3.0
 *
 * @param string         $content              Text content to filter.
 * @param string|array[] $context              The context for which to retrieve tags. Allowed values are 'post',
 *                                             'strip', 'data', 'entities', or the name of a field filter such as
 *                                             'pre_user_description', or an array of allowed HTML elements and attributes.
 * @param array[]        $disallowed_html      An array of disallowed HTML elements and attributes,
 *                                             or a context name such as 'post'. See wp_kses_allowed_html()
 *                                             for the list of accepted context names.
 * @param string[]       $disallowed_protocols Optional. Array of disllowed URL protocols.
 *                                             Defaults allowing the result of wp_allowed_protocols().
 *
 * @return string Filtered content containing only the allowed HTML.
 */
function pods_kses_exclude_tags( $content, $context = 'post', $disallowed_html = [], $disallowed_protocols = [] ) {
	$allowed_protocols = wp_allowed_protocols();
	$allowed_html      = wp_kses_allowed_html( $context );

	// Maybe disallow some HTML tags / attributes.
	if ( ! empty( $disallowed_html ) ) {
		foreach ( $disallowed_html as $tag => $attributes ) {
			// Check if the tag is allowed.
			if ( ! isset( $allowed_html[ $tag ] ) ) {
				continue;
			}

			// Check if we need to handle attributes or not.
			if ( is_array( $attributes ) ) {
				$attributes = array_keys( $attributes );

				foreach ( $attributes as $attribute ) {
					if ( isset( $allowed_html[ $tag ][ $attribute ] ) ) {
						unset( $allowed_html[ $tag ][ $attribute ] );
					}
				}
			} else {
				// Disallow the whole tag.
				unset( $allowed_html[ $tag ] );
			}
		}
	}

	// Maybe disallow some protocols.
	if ( ! empty( $disallowed_protocols ) ) {
		$allowed_protocols = array_values( array_diff( $disallowed_protocols, $allowed_protocols ) );
	}

	return wp_kses( $content, $allowed_html, $allowed_protocols );
}

/**
 * Filters text content and strips out disallowed HTML including the p tag.
 *
 * This function expects unslashed data.
 *
 * @since 3.0
 *
 * @param string $content Text content to filter.
 *
 * @return string Filtered content containing only the allowed HTML.
 */
function pods_kses_exclude_p( $content ) {
	return pods_kses_exclude_tags(
		$content,
		'post',
		[
			'p' => true,
		]
	);
}
