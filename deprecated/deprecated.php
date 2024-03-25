<?php
/**
 * @package Pods\Deprecated
 */

/**
 *
 */

// JSON support
if ( ! function_exists( 'json_encode' ) ) {
	require_once ABSPATH . '/wp-includes/js/tinymce/plugins/spellchecker/classes/utils/JSON.php';

	/**
	 * @param mixed $str Data to encode.
	 *
	 * @return mixed
	 */
	function json_encode( $str ) {
		$json = new Moxiecode_JSON();

		return $json->encode( $str );
	}

	/**
	 * @param string $str JSON string.
	 *
	 * @return mixed
	 */
	function json_decode( $str ) {
		$json = new Moxiecode_JSON();

		return $json->decode( $str );
	}
}//end if

// WP 3.4.x support
if ( ! function_exists( 'wp_send_json' ) ) {
	/**
	 * @param array $response Response data.
	 */
	function wp_send_json( $response ) {
		@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
		echo json_encode( $response );
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			wp_die();
		} else {
			die;
		}
	}
}

/**
 * Get the full URL of the current page
 *
 * @return string
 * @since      1.9.6
 *
 * @deprecated 2.3.0
 */
if ( ! function_exists( 'get_current_url' ) ) {
	/**
	 * @return mixed|void
	 */
	function get_current_url() {
		$url = pods_current_url();

		return apply_filters( 'get_current_url', $url );
	}
}

/**
 * Mapping function to new function name (following normalization of function names from pod_ to pods_)
 *
 * @since      1.x.x
 * @deprecated 2.0.0
 *
 * @param string      $sql              SQL query.
 * @param string      $error            Error message on failure.
 * @param null|string $results_error    Error message if results returned.
 * @param null|string $no_results_error Error message if no results returned.
 *
 * @return array|bool|mixed|null|void Result of the query
 */
function pod_query( $sql, $error = 'SQL failed', $results_error = null, $no_results_error = null ) {
	pods_deprecated( 'pod_query', '2.0', 'pods_query' );
	global $wpdb;

	$sql = trim( $sql );

	// Using @wp_users is deprecated! use $wpdb->users instead!
	$sql = str_replace( '@wp_pod_tbl_', $wpdb->prefix . 'pods_', $sql );
	$sql = str_replace( '@wp_users', $wpdb->users, $sql );
	$sql = str_replace( '@wp_', $wpdb->prefix, $sql );
	$sql = str_replace( '{prefix}', '@wp_', $sql );

	$sql = apply_filters( 'pod_query', $sql, $error, $results_error, $no_results_error );

	$result = pods_query( $sql, $error, $results_error, $no_results_error );

	$result = apply_filters( 'pod_query_return', $result, $sql, $error, $results_error, $no_results_error );

	return $result;
}

/**
 * Include and Init the Pods class
 *
 * @since      1.x.x
 * @deprecated 2.0.0
 * @package    Pods\Deprecated
 */
class Pod {

	private $new;

	public static $deprecated_notice = true;

	public $body_classes;

	public $ui = array();

	public $meta = array();

	public $meta_properties = array();

	public $meta_extra = '';

	/**
	 * Pod constructor.
	 *
	 * @param null $type
	 * @param null $id
	 */
	public function __construct( $type = null, $id = null ) {
		if ( self::$deprecated_notice ) {
			pods_deprecated( 'PodAPI (class)', '2.0', 'pods_api (function)' );
		}

		pods_deprecated( 'Pod (class)', '2.0', 'pods (function)' );

		$this->new = pods( $type, $id );
	}

	/**
	 * Handle variables that have been deprecated
	 *
	 * @since 2.0.0
	 *
	 * @param string $name Property name.
	 *
	 * @return array|bool|int|mixed|PodsData
	 */
	#[\ReturnTypeWillChange]
	public function __get( $name ) {
		$name = (string) $name;

		if ( 'data' === $name ) {
			if ( self::$deprecated_notice ) {
				pods_deprecated( "Pods->{$name}", '2.0', 'Pods->row()' );
			}

			$var = $this->new->row();
		} elseif ( '_data' === $name ) {
			$var = $this->new->data;
		} elseif ( 'total' === $name ) {
			if ( self::$deprecated_notice ) {
				pods_deprecated( "Pods->{$name}", '2.0', 'Pods->total()' );
			}

			$var = $this->new->total();
		} elseif ( 'total_rows' === $name ) {
			if ( self::$deprecated_notice ) {
				pods_deprecated( "Pods->{$name}", '2.0', 'Pods->total_found()' );
			}

			$var = $this->new->total_found();
		} elseif ( 'zebra' === $name ) {
			if ( self::$deprecated_notice ) {
				pods_deprecated( "Pods->{$name}", '2.0', 'Pods->zebra()' );
			}

			$var = $this->new->zebra();
		} else {
			$var = $this->new->{$name};
		}//end if

		return $var;
	}

	/**
	 * Handle variables that have been deprecated
	 *
	 * @since 2.0.0
	 *
	 * @param string $name  Property name.
	 * @param mixed  $value Property value to set.
	 */
	public function __set( $name, $value ): void {
		$name = (string) $name;

		$this->new->{$name} = $value;
	}

	/**
	 * Handle methods that have been deprecated
	 *
	 * @since 2.0.0
	 *
	 * @param string $name Call name.
	 * @param array  $arguments Call arguments.
	 *
	 * @return mixed
	 */
	#[\ReturnTypeWillChange]
	public function __call( $name, $arguments ) {
		$name = (string) $name;

		return call_user_func_array( array( $this->new, $name ), $arguments );
	}

	/**
	 * Handle variables that have been deprecated
	 *
	 * @since 2.0.0
	 *
	 * @param string $name Property name.
	 *
	 * @return bool
	 */
	public function __isset( $name ): bool {
		$name = (string) $name;

		if ( in_array( $name, array( '_data', 'data', 'total', 'total_rows', 'zebra' ), true ) ) {
			return true;
		} elseif ( in_array( $name, array( 'meta', 'meta_properties', 'meta_extra' ), true ) ) {
			return true;
		} else {
			return isset( $this->new->{$name} );
		}
	}
}

/**
 * Include and Init the PodsAPI class
 *
 * @since      1.x.x
 * @deprecated 2.0.0
 * @package    Pods\Deprecated
 */
class PodAPI {

	private $new;

	public static $deprecated_notice = true;

	/**
	 * PodAPI constructor.
	 *
	 * @param null $type
	 * @param null $format
	 */
	public function __construct( $type = null, $format = null ) {
		if ( self::$deprecated_notice ) {
			pods_deprecated( 'PodAPI (class)', '2.0', 'pods_api (function)' );
		}

		$this->new = pods_api( $type, $format );
	}

	/**
	 * Handle variables that have been deprecated
	 *
	 * @since 2.0.0
	 *
	 * @param string $name Property name.
	 *
	 * @return null|mixed
	 */
	#[\ReturnTypeWillChange]
	public function __get( $name ) {
		$name = (string) $name;

		$var = $this->new->{$name};

		return $var;
	}

	/**
	 * Handle methods that have been deprecated
	 *
	 * @since 2.0.0
	 *
	 * @param string $name Call name.
	 * @param array  $arguments Call arguments.
	 *
	 * @return mixed
	 */
	#[\ReturnTypeWillChange]
	public function __call( $name, $arguments ) {
		$name = (string) $name;

		return call_user_func_array( array( $this->new, $name ), $arguments );
	}
}

/**
 * Include and Init the PodsUI class
 *
 * @since      2.0.0
 * @deprecated 2.0.0
 *
 * @param Pods $obj Pods object.
 *
 * @return PodsUI
 */
function pods_ui_manage( $obj ) {
	pods_deprecated( 'pods_ui_manage', '2.0', 'pods_ui' );

	return pods_ui( $obj, true );
}

/**
 * Limit Access based on Field Value
 *
 * @since      1.x.x
 * @deprecated 2.0.0
 *
 * @param Pods   $object Pods object.
 * @param array  $access Access array.
 * @param string $what   Action name.
 *
 * @return bool
 */
function pods_ui_access( $object, $access, $what ) {
	pods_deprecated( 'pods_ui_access', '2.0' );
	if ( is_array( $access ) ) {
		foreach ( $access as $field => $match ) {
			if ( is_array( $match ) ) {
				$okay = false;
				foreach ( $match as $the_field => $the_match ) {
					if ( $object->get_field( $the_field ) == $the_match ) {
						$okay = true;
					}
				}
				if ( false === $okay ) {
					return false;
				}
			} elseif ( $object->get_field( $field ) != $match ) {
				return false;
			}
		}
	}

	return true;
}

/**
 * Return a GET, POST, COOKIE, SESSION, or URI string segment
 *
 * @param mixed  $key  The variable name or URI segment position
 * @param string $type (optional) "uri", "get", "post", "request", "server", "session", or "cookie"
 *
 * @return string The requested value, or null
 * @since      1.6.2
 * @deprecated 2.0.0
 */
function pods_url_variable( $key = 'last', $type = 'url' ) {
	$output = apply_filters( 'pods_url_variable', pods_var( $key, $type ), $key, $type );

	return $output;
}

/**
 * Generate form key - INTERNAL USE
 *
 * @since      1.2.0
 * @deprecated 2.0.0
 *
 * @param string $datatype   Pod name.
 * @param string $uri_hash   URI hash for session.
 * @param array  $columns    List of columns.
 * @param int    $form_count Form counter.
 *
 * @return mixed|string|void
 */
function pods_generate_key( $datatype, $uri_hash, $columns, $form_count = 1 ) {
	$token                             = wp_create_nonce( 'pods-form-' . $datatype . '-' . (int) $form_count . '-' . $uri_hash . '-' . json_encode( $columns ) );
	$token                             = apply_filters( 'pods_generate_key', $token, $datatype, $uri_hash, $columns, (int) $form_count );
	$_SESSION[ 'pods_form_' . $token ] = $columns;

	return $token;
}

/**
 * Validate form key - INTERNAL USE
 *
 * @since      1.2.0
 * @deprecated 2.0.0
 *
 * @param string     $token      Nonce token.
 * @param string     $datatype   Pod name.
 * @param string     $uri_hash   URI hash for session.
 * @param null|array $columns    List of columns.
 * @param int        $form_count Form counter.
 *
 * @return mixed|void
 */
function pods_validate_key( $token, $datatype, $uri_hash, $columns = null, $form_count = 1 ) {
	if ( null === $columns && ! empty( $_SESSION ) && isset( $_SESSION[ 'pods_form_' . $token ] ) ) {
		$columns = $_SESSION[ 'pods_form_' . $token ];
	}
	$success = false;
	if ( false !== wp_verify_nonce( $token, 'pods-form-' . $datatype . '-' . (int) $form_count . '-' . $uri_hash . '-' . json_encode( $columns ) ) ) {
		$success = $columns;
	}

	return apply_filters( 'pods_validate_key', $success, $token, $datatype, $uri_hash, $columns, (int) $form_count );
}

/**
 * Output a message in the WP Dashboard UI
 *
 * @param string $message
 * @param bool   $error Whether or not it is an error message
 *
 * @return bool
 *
 * @since     1.12
 * @deprcated 2.3
 */
function pods_ui_message( $message, $error = false ) {
	pods_deprecated( 'pods_message', '2.3' );

	pods_message( $message, ( $error ? 'error' : 'notice' ) );
}

/**
 * Output an error in the WP Dashboard UI
 *
 * @param string $message
 *
 * @return bool
 *
 * @since     1.12
 * @deprcated 2.3
 */
function pods_ui_error( $message ) {
	pods_deprecated( 'pods_message', '2.3' );

	pods_message( $message, 'error' );
}

/**
 * Get a Point value from a Pods Version number
 *
 * @since     1.10.1
 * @deprcated 2.3
 *
 * @param string $point Version number with points.
 *
 * @return int|string
 */
function pods_point_to_version( $point ) {
	$version_tmp = explode( '.', $point );
	$version     = '';

	for ( $x = 0; $x < 3; $x ++ ) {
		// 3 points max - MAJOR.MINOR.PATCH
		if ( ! isset( $version_tmp[ $x ] ) || strlen( $version_tmp[ $x ] ) < 1 ) {
			$version_tmp[ $x ] = '000';
		}

		$version_temp = str_split( $version_tmp[ $x ] );

		if ( 3 == count( $version_temp ) ) {
			$version .= $version_tmp[ $x ];
		} elseif ( 2 == count( $version_temp ) ) {
			$version .= '0' . $version_tmp[ $x ];
		} elseif ( 1 == count( $version_temp ) ) {
			$version .= '00' . $version_tmp[ $x ];
		}
	}

	$version = (int) $version;

	return $version;
}

/**
 * Get a Point value from a Pods Version number
 *
 * @since     1.10
 * @deprcated 2.3
 *
 * @param string $version Version number string.
 *
 * @return array|string
 */
function pods_version_to_point( $version ) {
	$point_tmp = $version;

	if ( strlen( $point_tmp ) < 9 ) {
		if ( 8 == strlen( $point_tmp ) ) {
			$point_tmp = '0' . $point_tmp;
		}

		if ( 7 == strlen( $point_tmp ) ) {
			$point_tmp = '00' . $point_tmp;
		}

		if ( 3 == strlen( $version ) ) {
			// older versions prior to 1.9.9
			return implode( '.', str_split( $version ) );
		}
	}

	$point_tmp = str_split( $point_tmp, 3 );
	$point     = array();

	foreach ( $point_tmp as $the_point ) {
		$point[] = (int) $the_point;
	}

	$point = implode( '.', $point );

	return $point;
}
