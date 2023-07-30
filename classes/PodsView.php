<?php

use Pods\Static_Cache;

/**
 * @package Pods
 */
class PodsView {

	/**
	 * List of keys that have been cached grouped by cache mode.
	 *
	 * @since 3.0
	 *
	 * @var array
	 */
	private static $cached_keys = [
		'transient'      => [],
		'site-transient' => [],
		'cache'          => [],
		'static-cache'   => [],
		'option-cache'   => [],
	];

	/**
	 * @var array $cache_modes Array of available cache modes
	 */
	public static $cache_modes = [
		'none'           => true,
		'transient'      => true,
		'site-transient' => true,
		'cache'          => true,
		'static-cache'   => true,
		'option-cache'   => true,
	];

	/**
	 * @return \PodsView
	 */
	private function __construct() {
		// !nope
	}

	/**
	 * Add a cache key to keep track of for a cache mode.
	 *
	 * @since 3.0
	 *
	 * @param string      $cache_mode   The cache mode.
	 * @param string      $cache_key    The cache key.
	 * @param null|string $group        The cache group, if needed.
	 * @param null|string $original_key The original cache key, if different from the cache key.
	 */
	public static function add_cached_key( $cache_mode, $cache_key, $group = null, $original_key = null ) {
		if ( ! isset( self::$cached_keys[ $cache_mode ] ) ) {
			self::$cached_keys[ $cache_mode ] = [];
		}

		if ( null === $original_key ) {
			$original_key = $cache_key;
		}

		if ( $group ) {
			if ( ! isset( self::$cached_keys[ $cache_mode ][ $group ] ) ) {
				self::$cached_keys[ $cache_mode ][ $group ] = [];
			}

			self::$cached_keys[ $cache_mode ][ $group ][ $cache_key ] = $original_key;

			return;
		}

		self::$cached_keys[ $cache_mode ][ $cache_key ] = $original_key;
	}

	/**
	 * Remove a cache key from tracking for a cache mode.
	 *
	 * @since 3.0
	 *
	 * @param string      $cache_mode   The cache mode.
	 * @param string      $cache_key    The cache key.
	 * @param null|string $group        The cache group, if needed.
	 */
	public static function remove_cached_key( $cache_mode, $cache_key, $group = null ) {
		if ( ! isset( self::$cached_keys[ $cache_mode ] ) ) {
			return;
		}

		if ( $group ) {
			if ( ! isset( self::$cached_keys[ $cache_mode ][ $group ] ) ) {
				return;
			}

			unset( self::$cached_keys[ $cache_mode ][ $group ][ $cache_key ] );

			return;
		}

		if ( ! isset( self::$cached_keys[ $cache_mode ][ $cache_key ] ) ) {
			return;
		}

		unset( self::$cached_keys[ $cache_mode ][ $cache_key ] );
	}

	/**
	 * Get the list of cache keys based on cache mode.
	 *
	 * @since 3.0
	 *
	 * @param string      $cache_mode The cache mode.
	 * @param null|string $group      The cache group, if needed.
	 */
	public static function get_cached_keys( $cache_mode, $group = null ) : array {
		if ( $group ) {
			return self::$cached_keys[ $cache_mode ][ $group ] ?? [];
		}

		return self::$cached_keys[ $cache_mode ] ?? [];
	}

	/**
	 * Reset cache keys based on cache mode.
	 *
	 * @since 3.0
	 *
	 * @param null|string $cache_mode The cache mode, null if resetting all.
	 * @param null|string $group      The cache group, if needed.
	 */
	public static function reset_cached_keys( $cache_mode = null, $group = null ) {
		if ( null === $cache_mode ) {
			foreach ( self::$cache_modes as $cache_mode_to_reset => $unused ) {
				if ( isset( self::$cached_keys[ $cache_mode_to_reset ] ) ) {
					self::$cached_keys[ $cache_mode_to_reset ] = [];
				}
			}

			return;
		}

		if ( $group ) {
			if ( isset( self::$cached_keys[ $cache_mode ][ $group ] ) ) {
				self::$cached_keys[ $cache_mode ][ $group ] = [];
			}

			return;
		}

		if ( isset( self::$cached_keys[ $cache_mode ] ) ) {
			self::$cached_keys[ $cache_mode ] = [];
		}
	}

	/**
	 * @static
	 *
	 * @param string         $view       Path of the view file
	 * @param array|null     $data       (optional) Data to pass on to the template
	 * @param bool|int|array $expires    (optional) Time in seconds for the cache to expire, if 0 no expiration.
	 * @param string         $cache_mode (optional) Decides the caching method to use for the view.
	 *
	 * @return bool|mixed|null|string|void
	 *
	 * @since 2.0.0
	 */
	public static function view( $view, $data = null, $expires = false, $cache_mode = 'cache' ) {

		/**
		 * Override the value of $view. For example, using Pods AJAX View.
		 *
		 * To use, set first param to true. If that param in not null, this method returns its value.
		 *
		 * @param null|bool      If          not set to null, this filter overrides the rest of the method.
		 * @param string         $view       Path of the view file
		 * @param array|null     $data       (optional) Data to pass on to the template
		 * @param bool|int|array $expires    (optional) Time in seconds for the cache to expire, if 0 no expiration.
		 * @param string         $cache_mode (optional) Decides the caching method to use for the view.
		 *
		 * @since 2.4.1
		 */
		$filter_check = apply_filters( 'pods_view_alt_view', null, $view, $data, $expires, $cache_mode );

		if ( null !== $filter_check ) {
			return $filter_check;
		}

		// Advanced $expires handling
		$expires = self::expires( $expires, $cache_mode );

		if ( ! self::is_cache_mode_valid( $cache_mode ) ) {
			$cache_mode = 'cache';
		}

		// Support my-view.php?custom-key=X#hash keying for cache
		$view_id = '';

		if ( ! is_array( $view ) ) {
			$view_q = explode( '?', $view );

			if ( 1 < count( $view_q ) ) {
				$view_id = '?' . $view_q[1];

				$view = $view_q[0];
			}

			$view_h = explode( '#', $view );

			if ( 1 < count( $view_h ) ) {
				$view_id .= '#' . $view_h[1];

				$view = $view_h[0];
			}

			// Support dynamic tags!
			$view_id = pods_evaluate_tags( $view_id );
		}

		$view = apply_filters( 'pods_view_inc', $view, $data, $expires, $cache_mode );

		$view_key = $view;

		if ( is_array( $view_key ) ) {
			$view_key = implode( '-', $view_key ) . '.php';
		}

		if ( false !== realpath( $view_key ) ) {
			$view_key = realpath( $view_key );
		}

		$pods_ui_dir         = realpath( PODS_DIR . 'ui/' );
		$pods_components_dir = realpath( PODS_DIR . 'components/' );
		$abspath_dir         = realpath( ABSPATH );

		$cache_key = pods_str_replace( $abspath_dir, '/', $view_key, 1 );

		$output = false;

		$caching = false;

		if ( false !== $expires && false === strpos( $view_key, $pods_ui_dir ) && false === strpos( $view_key, $pods_components_dir ) ) {
			$caching = true;
		}

		if ( $caching ) {
			$output = self::get( 'pods-view-' . $cache_key . $view_id, $cache_mode, 'pods_view' );
		}

		if ( false === $output || null === $output ) {
			$output = self::get_template_part( $view, $data );
		}

		if ( false !== $output && $caching ) {
			self::set( 'pods-view-' . $cache_key . $view_id, $output, $expires, $cache_mode, 'pods_view' );
		}

		$output = apply_filters( "pods_view_output_{$cache_key}", $output, $view, $data, $expires, $cache_mode );
		$output = apply_filters( 'pods_view_output', $output, $view, $data, $expires, $cache_mode );

		return $output;
	}

	/**
	 * Get the cache key, salted with current Pods version, peppered with md5 if too long
	 *
	 * @param string $key
	 * @param string $group_key
	 *
	 * @return string
	 *
	 * @since 2.6.2
	 */
	public static function get_key( $key, $group_key = '' ) {

		// Add some salt
		$key .= '-' . sanitize_key( PODS_VERSION );

		// Patch for limitations in DB
		if ( is_string( $group_key ) && 44 < strlen( $group_key . $key ) ) {
			$key = md5( $key );
		}

		return $key;

	}

	/**
	 * @static
	 *
	 * @param string $key        Key for the cache
	 * @param string $cache_mode (optional) Decides the caching method to use for the view.
	 * @param string $group      (optional) Set the group of the value.
	 * @param string $callback   (optional) Callback function to run to set the value if not cached.
	 *
	 * @return bool|mixed|null|void
	 *
	 * @since 2.0.0
	 */
	public static function get( $key, $cache_mode = 'cache', $group = '', $callback = null ) {
		$external_object_cache = wp_using_ext_object_cache();

		$object_cache_enabled = (
			(
				isset( $GLOBALS['wp_object_cache'] )
				&& is_object( $GLOBALS['wp_object_cache'] )
			)
			|| $external_object_cache
		);

		if ( ! self::is_cache_mode_valid( $cache_mode ) ) {
			$cache_mode = 'cache';
		}

		$group_key = 'pods_';

		if ( ! empty( $group ) ) {
			$group_key = $group . '_';
		}

		$original_key = $key;

		// Get proper cache key
		$key = self::get_key( $key, $group_key );

		$value = null;

		$called = false;

		$pods_nocache = pods_v( 'pods_nocache' );
		$nocache      = array();

		if ( null !== $pods_nocache && pods_is_admin() ) {
			if ( is_string( $pods_nocache ) && 1 < strlen( $pods_nocache ) ) {
				$nocache = explode( ',', $pods_nocache );
				$nocache = array_flip( $nocache );
			} else {
				$nocache = self::$cache_modes;
			}
		}

		$cache_enabled = ! isset( $nocache[ $cache_mode ] );

		if ( apply_filters( 'pods_view_cache_alt_get', false, $cache_mode, $group_key . $key, $original_key, $group ) ) {
			$value = apply_filters( 'pods_view_cache_alt_get_value', $value, $cache_mode, $group_key . $key, $original_key, $group );
		} elseif ( $cache_enabled ) {
			if ( 'transient' === $cache_mode ) {
				$value = get_transient( $group_key . $key );
			} elseif ( 'site-transient' === $cache_mode ) {
				$value = get_site_transient( $group_key . $key );
			} elseif ( 'cache' === $cache_mode && $object_cache_enabled ) {
				$value = wp_cache_get( $key, ( empty( $group ) ? 'pods_view' : $group ) );
			} elseif ( 'option-cache' === $cache_mode ) {
				$pre = apply_filters( "pre_transient_{$key}", false );

				if ( false !== $pre ) {
					$value = $pre;
				} elseif ( $external_object_cache ) {
					$cache_found = false;

					$value = wp_cache_get( $key, ( empty( $group ) ? 'pods_option_cache' : $group ), false, $cache_found );

					if ( false === $value || ! $cache_found ) {
						if ( is_callable( $callback ) ) {
							// Callback function should do it's own set/update for cache
							$callback_value = call_user_func( $callback, $original_key, $group, $cache_mode );

							if ( null !== $callback_value && false !== $callback_value ) {
								$value = $callback_value;
							}

							$called = true;
						}
					}
				} else {
					$transient_option  = '_pods_option_' . $key;
					$transient_timeout = '_pods_option_timeout_' . $key;

					$value   = get_option( $transient_option );
					$timeout = get_option( $transient_timeout );

					if ( ! empty( $timeout ) && $timeout < time() ) {
						if ( is_callable( $callback ) ) {
							// Callback function should do it's own set/update for cache
							$callback_value = call_user_func( $callback, $original_key, $group, $cache_mode );

							if ( null !== $callback_value && false !== $callback_value ) {
								$value = $callback_value;
							}

							$called = true;
						} else {
							$value = false;

							delete_option( $transient_option );
							delete_option( $transient_timeout );
						}
					}
				}//end if

				if ( false !== $value ) {
					$value = apply_filters( "transient_{$key}", $value );
				}
			} elseif ( 'static-cache' === $cache_mode ) {
				$static_cache = pods_container( Static_Cache::class );

				if ( $static_cache ) {
					$value = $static_cache->get( $key, ( empty( $group ) ? 'pods_view' : $group ) );
				} else {
					$value = false;
				}
			} else {
				$value = false;
			}//end if
		} else {
			$value = false;
		}//end if

		if ( false === $value && is_callable( $callback ) && ! $called ) {
			// Callback function should do it's own set/update for cache
			$callback_value = call_user_func( $callback, $original_key, $group, $cache_mode );

			if ( null !== $callback_value && false !== $callback_value ) {
				$value = $callback_value;
			}
		}

		$value = apply_filters( "pods_view_get_{$cache_mode}", $value, $original_key, $group );

		return $value;
	}

	/**
	 * @static
	 *
	 * Set a cached value
	 *
	 * @param string $key        Key for the cache
	 * @param mixed  $value      Value to add to the cache
	 * @param int    $expires    (optional) Time in seconds for the cache to expire, if 0 no expiration.
	 * @param string $cache_mode (optional) Decides the caching method to use for the view.
	 * @param string $group      (optional) Set the group of the value.
	 *
	 * @return bool|mixed|null|string|void
	 *
	 * @since 2.0.0
	 */
	public static function set( $key, $value, $expires = 0, $cache_mode = null, $group = '' ) {
		$external_object_cache = wp_using_ext_object_cache();

		$object_cache_enabled = (
			(
				isset( $GLOBALS['wp_object_cache'] )
				&& is_object( $GLOBALS['wp_object_cache'] )
			)
			|| $external_object_cache
		);

		// Advanced $expires handling
		$expires = self::expires( $expires, $cache_mode );

		if ( ! self::is_cache_mode_valid( $cache_mode ) ) {
			$cache_mode = 'cache';
		}

		$group_key = 'pods_';

		if ( ! empty( $group ) ) {
			$group_key = $group . '_';
		}

		$original_key = $key;

		// Get proper cache key
		$key = self::get_key( $key, $group_key );

		if ( apply_filters( 'pods_view_cache_alt_set', false, $cache_mode, $group_key . $key, $original_key, $value, $expires, $group ) ) {
			self::add_cached_key( $cache_mode, $group_key . $key, null, $original_key );

			return $value;
		} elseif ( 'transient' === $cache_mode ) {
			self::add_cached_key( $cache_mode, $group_key . $key, null, $original_key );

			set_transient( $group_key . $key, $value, $expires );
		} elseif ( 'site-transient' === $cache_mode ) {
			self::add_cached_key( $cache_mode, $group_key . $key, null, $original_key );

			set_site_transient( $group_key . $key, $value, $expires );
		} elseif ( 'cache' === $cache_mode && $object_cache_enabled ) {
			$group = ( empty( $group ) ? 'pods_view' : $group );
			$key   = ( empty( $key ) ? 'pods_view' : $key );

			self::add_cached_key( $cache_mode, $key, $group, $original_key );

			wp_cache_set( $key, $value, $group, $expires );
		} elseif ( 'option-cache' === $cache_mode ) {
			$group = ( empty( $group ) ? 'pods_option_cache' : $group );

			$value = apply_filters( "pre_set_transient_{$key}", $value );

			if ( $external_object_cache ) {
				$result = wp_cache_set( $key, $value, $group, $expires );
			} else {
				$transient_timeout = '_pods_option_timeout_' . $key;
				$key               = '_pods_option_' . $key;

				if ( false === get_option( $key ) ) {
					if ( $expires ) {
						add_option( $transient_timeout, time() + $expires, '', 'no' );
					}

					$result = add_option( $key, $value, '', 'no' );
				} else {
					if ( $expires ) {
						update_option( $transient_timeout, time() + $expires );
					}

					$result = update_option( $key, $value );
				}
			}//end if

			self::add_cached_key( $cache_mode, $key, $group, $original_key );

			if ( $result ) {
				do_action( "set_transient_{$key}" );
				do_action( 'setted_transient', $key );
			}
		} elseif ( 'static-cache' === $cache_mode ) {
			$static_cache = pods_container( Static_Cache::class );

			if ( $static_cache ) {
				$group = ( empty( $group ) ? __CLASS__ : $group );

				self::add_cached_key( $cache_mode, $key, $group, $original_key );

				$static_cache->set( $key, $value, $group );
			}
		}//end if

		do_action( "pods_view_set_{$cache_mode}", $original_key, $value, $expires, $group );

		return $value;
	}

	/**
	 * @static
	 *
	 * Clear a cached value
	 *
	 * @param string|bool $key        Key for the cache
	 * @param string      $cache_mode (optional) Decides the caching method to use for the view.
	 * @param string      $group      (optional) Set the group.
	 *
	 * @return bool
	 *
	 * @since 2.0.0
	 */
	public static function clear( $key = true, $cache_mode = null, $group = '' ) {
		$external_object_cache = wp_using_ext_object_cache();

		$object_cache_enabled = (
			(
				isset( $GLOBALS['wp_object_cache'] )
				&& is_object( $GLOBALS['wp_object_cache'] )
			)
			|| $external_object_cache
		);

		global $wpdb;

		if ( ! self::is_cache_mode_valid( $cache_mode ) ) {
			$cache_mode = 'cache';
		}

		$group_key = 'pods_';

		if ( ! empty( $group ) ) {
			$group_key = $group . '_';
		}

		$full_key     = $key;
		$original_key = $key;

		if ( true !== $key ) {
			// Get proper cache key
			$key = self::get_key( $key, $group_key );

			$full_key = $group_key . $key;
		}

		if ( apply_filters( 'pods_view_cache_alt_set', false, $cache_mode, $full_key, $original_key, '', 0, $group ) ) {
			self::remove_cached_key( $cache_mode, $full_key );

			return true;
		} elseif ( 'transient' === $cache_mode ) {
			if ( true === $key ) {
				$group_key = pods_sanitize_like( $group_key );

				$wpdb->query( "DELETE FROM `{$wpdb->options}` WHERE option_name LIKE '_transient_{$group_key}%'" );

				if ( $object_cache_enabled ) {
					if ( $group && function_exists( 'wp_cache_flush_group' ) ) {
						wp_cache_flush_group( $group );
					} else {
						wp_cache_flush();
					}
				}

				self::reset_cached_keys( $cache_mode );
			} else {
				delete_transient( $group_key . $key );

				self::remove_cached_key( $cache_mode, $group_key . $key );
			}
		} elseif ( 'site-transient' === $cache_mode ) {
			if ( true === $key ) {
				$group_key = pods_sanitize_like( $group_key );

				$wpdb->query( "DELETE FROM `{$wpdb->options}` WHERE option_name LIKE '_site_transient_{$group_key}%'" );

				if ( $object_cache_enabled ) {
					if ( $group && function_exists( 'wp_cache_flush_group' ) ) {
						wp_cache_flush_group( $group );
					} else {
						wp_cache_flush();
					}
				}

				self::reset_cached_keys( $cache_mode );
			} else {
				delete_site_transient( $group_key . $key );

				self::remove_cached_key( $cache_mode, $group_key . $key );
			}
		} elseif ( 'cache' === $cache_mode && $object_cache_enabled ) {
			if ( true === $key ) {
				if ( $group && function_exists( 'wp_cache_flush_group' ) ) {
					wp_cache_flush_group( $group );

					self::reset_cached_keys( $cache_mode, $group );
				} else {
					wp_cache_flush();

					self::reset_cached_keys( $cache_mode );
				}
			} else {
				$group = ( empty( $group ) ? 'pods_view' : $group );
				$key   = ( empty( $key ) ? 'pods_view' : $key );

				wp_cache_delete( $key, $group );

				self::remove_cached_key( $cache_mode, $key, $group );
			}
		} elseif ( 'option-cache' === $cache_mode ) {
			do_action( "delete_transient_{$key}", $key );

			$group = ( empty( $group ) ? 'pods_option_cache' : $group );

			if ( $external_object_cache ) {
				$result = wp_cache_delete( $key, $group );

				wp_cache_delete( '_timeout_' . $key, $group );
			} else {
				$option_timeout = '_pods_option_timeout_' . $key;
				$option         = '_pods_option_' . $key;

				$result = delete_option( $option );

				if ( $result ) {
					delete_option( $option_timeout );
				}
			}

			self::remove_cached_key( $cache_mode, $key, $group );

			if ( $result ) {
				do_action( 'deleted_transient', $key );
			}
		} elseif ( 'static-cache' === $cache_mode ) {
			$static_cache = pods_container( Static_Cache::class );

			if ( $static_cache ) {
				$group = ( empty( $group ) ? __CLASS__ : $group );

				if ( true === $key ) {
					$static_cache->flush( $group );

					self::reset_cached_keys( $cache_mode, $group );
				} else {
					$static_cache->delete( $key, $group );

					self::remove_cached_key( $cache_mode, $key, $group );
				}
			}
		}//end if

		do_action( "pods_view_clear_{$cache_mode}", $original_key, $group );

		return true;
	}

	/**
	 * @static
	 *
	 * @param            $_view
	 * @param null|array $_data
	 *
	 * @return bool|mixed|string|void
	 */
	public static function get_template_part( $_view, $_data = null ) {

		/*
		To be reviewed later, should have more checks and restrictions like a whitelist etc.

		if ( 0 === strpos( $_view, 'http://' ) || 0 === strpos( $_view, 'https://' ) ) {
			$_view = apply_filters( 'pods_view_url_include', $_view );

			if ( empty( $_view ) || ( defined( 'PODS_REMOTE_VIEWS' ) && PODS_REMOTE_VIEWS ) )
				return '';

			$response = wp_remote_get( $_view );

			return wp_remote_retrieve_body( $response );
		}
		*/

		$_view = self::locate_template( $_view );

		if ( empty( $_view ) ) {
			return $_view;
		}

		if ( ! empty( $_data ) && is_array( $_data ) ) {
			extract( $_data, EXTR_SKIP );
		}

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
	private static function locate_template( $_view ) {
		if ( is_array( $_view ) ) {
			$_views = [];

			if ( isset( $_view[0] ) && false === strpos( $_view[0], '.php' ) ) {
				$_view_count = count( $_view );

				for ( $_view_x = $_view_count; 0 < $_view_x; $_view_x -- ) {
					$_view_v = array_slice( $_view, 0, $_view_x );

					$_views[] = implode( '-', $_view_v ) . '.php';
				}
			} else {
				$_views = $_view;
			}

			$_view = false;

			foreach ( $_views as $_view_check ) {
				$_view = self::locate_template( $_view_check );

				if ( ! empty( $_view ) ) {
					break;
				}
			}

			return $_view;
		}//end if

		// Is the view's file somewhere within the plugin directory tree?
		// Note: we include PODS_DIR for the case of symlinks (see issue #2945).
		$located = pods_validate_safe_path( $_view, [ 'plugins', 'pods', 'theme' ] );

		/**
		 * Allow filtering the validated view file path to use.
		 *
		 * @since unknown
		 *
		 * @param string|false $located The validated view file path to use, or false if it was not valid.
		 * @param string       $_view   The original view file path to use.
		 */
		$located = apply_filters( 'pods_view_locate_template', $located, $_view );

		if ( ! $located ) {
			return false;
		}

		return $located;

	}

	/**
	 * Advanced $expires handling
	 *
	 * @param array|bool|int $expires
	 * @param string         $cache_mode
	 *
	 * @return bool|int
	 *
	 * @since 2.7.0
	 * @static
	 */
	public static function expires( $expires, $cache_mode = 'cache' ) {

		// Different $expires if user is anonymous or logged in or specific capability
		if ( is_array( $expires ) ) {
			if ( ( isset( $expires['anonymous'] ) || isset( $expires['user_with_access'] ) ) && isset( $expires['user'] ) ) {
				if ( isset( $expires['user_with_access'] ) ) {
					$expires = array(
						pods_v( 'anonymous', $expires, false ),
						pods_v( 'user', $expires, false ),
						pods_v( 'user_with_access', $expires, false ),
						pods_v( 'capability', $expires, null, null, true ),
					);
				} elseif ( isset( $expires['anonymous'] ) ) {
					$expires = array(
						pods_v( 'anonymous', $expires, false ),
						pods_v( 'user', $expires, false ),
						pods_v( 'capability', $expires, null, null, true ),
					);
				}
			} else {
				$expires = array_values( $expires );
			}

			if ( 4 === count( $expires ) ) {
				if ( ! is_user_logged_in() ) {
					$expires = pods_v( 0, $expires, false );
				} else {
					$user_no_access   = pods_v( 1, $expires, false );
					$user_with_access = pods_v( 2, $expires, false );
					$capability       = pods_v( 3, $expires, null, true );

					$expires = pods_var_user( $user_no_access, $user_with_access, $capability );
				}
			} else {
				$anon       = pods_v( 0, $expires, false );
				$user       = pods_v( 1, $expires, false );
				$capability = pods_v( 2, $expires, null, true );

				$expires = pods_var_user( $anon, $user, $capability );
			}
		}//end if

		if ( 'none' === $cache_mode ) {
			$expires = false;
		} elseif ( false !== $expires ) {
			$expires = (int) $expires;

			if ( $expires < 1 ) {
				$expires = 0;
			}
		}

		return $expires;

	}

	/**
	 * Determine whether the cache mode is valid.
	 *
	 * @since 2.9.14
	 *
	 * @param string|mixed $cache_mode The cache mode.
	 *
	 * @return bool Whether the cache mode is valid.
	 */
	public static function is_cache_mode_valid( $cache_mode ) {
		return (
			$cache_mode
			&& is_string( $cache_mode )
			&& isset( self::$cache_modes[ $cache_mode ] )
       );
	}

}
