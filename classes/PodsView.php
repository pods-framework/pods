<?php
/**
 * @package Pods
 */
class PodsView {

    /**
     * @var array $cache_modes Array of available cache modes
     */
    static $cache_modes = array( 'none', 'transient', 'site-transient', 'cache', 'option-cache' );

    /**
     * @return \PodsView
     */
    private function __construct () {
    }

    /**
     * @static
     *
     * @param string $view Path of the view file
     * @param array|null $data (optional) Data to pass on to the template
     * @param bool|int|array $expires (optional) Time in seconds for the cache to expire, if 0 no expiration.
     * @param string $cache_mode (optional) Decides the caching method to use for the view.
     *
     * @return bool|mixed|null|string|void
     *
     * @since 2.0
     */
    public static function view ( $view, $data = null, $expires = false, $cache_mode = 'cache' ) {
        // Different $expires if user is anonymous or logged in or specific capability
        if ( is_array( $expires ) ) {
            $anon = pods_var_raw( 0, $expires, false );
            $user = pods_var_raw( 1, $expires, false );
            $capability = pods_var_raw( 2, $expires, null, null, true );

            $expires = pods_var_user( $anon, $user, $capability );
        }

        if ( 'none' == $cache_mode )
            $expires = false;

        if ( false !== $expires && empty( $expires ) )
            $expires = 0;

        if ( !in_array( $cache_mode, self::$cache_modes ) )
            $cache_mode = 'cache';

        $view = apply_filters( 'pods_view_inc', $view, $data, $expires, $cache_mode );

        $view_key = $view;

        if ( is_array( $view_key ) )
            $view_key = implode( '-', $view_key ) . '.php';

        $view_key = realpath( $view_key );

        $pods_ui_dir = realpath( PODS_DIR . 'ui/' );
        $pods_components_dir = realpath( PODS_DIR . 'components/' );
        $content_dir = realpath( WP_CONTENT_DIR );
        $plugins_dir = realpath( WP_PLUGIN_DIR );
        $muplugins_dir = realpath( WPMU_PLUGIN_DIR );
        $abspath_dir = realpath( ABSPATH );

        $cache_key = sanitize_title(
            pods_str_replace(
                array(
                    $pods_ui_dir,
                    $pods_components_dir,
                    $content_dir,
                    $plugins_dir,
                    $abspath_dir,
                    '.php',
                    '/'
                ),
                array(
                    'ui-',
                    'ui-',
                    'custom-',
                    'custom-',
                    'custom-',
                    '',
                    '_'
                ),
                $view_key,
                1
            )
        );

        $output = false;

        if (
            false !== $expires
            && false === strpos( $view_key, $pods_ui_dir )
            && false === strpos( $view_key, $pods_components_dir )
            && false === strpos( $view_key, $content_dir )
            && false === strpos( $view_key, $plugins_dir )
            && false === strpos( $view_key, $muplugins_dir )
            && false === strpos( $view_key, $abspath_dir )
        ) {
            $output = self::get( 'pods-view-' . $cache_key, $cache_mode, 'pods_view' );
        }

        if ( false === $output || null === $output )
            $output = self::get_template_part( $view, $data );

        if ( false !== $output && false !== $expires )
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
	 * @param string $callback (optional) Callback function to run to set the value if not cached.
     *
     * @return bool|mixed|null|void
     *
     * @since 2.0
     */
	public static function get( $key, $cache_mode = 'cache', $group = '', $callback = null ) {

		$object_cache = false;

		if ( isset( $GLOBALS[ 'wp_object_cache' ] ) && is_object( $GLOBALS[ 'wp_object_cache' ] ) ) {
			$object_cache = true;
		}

		if ( !in_array( $cache_mode, self::$cache_modes ) ) {
			$cache_mode = 'cache';
		}

		$group_key = '';

		if ( !empty( $group ) ) {
			$group_key = $group . '_';
		}

		$original_key = $key;

		// Patch for limitations in DB
		if ( 44 < strlen( $group_key . $key ) ) {
			$key = md5( $key );

			if ( empty( $group_key ) ) {
				$group_key = 'pods_';
			}
		}

		$value = null;

		$called = false;

		$pods_nocache = pods_var_raw( 'pods_nocache' );
		$nocache = array();

		if ( pods_is_admin() && null !== $pods_nocache ) {
			if ( 1 < strlen( $pods_nocache ) ) {
				$nocache = explode( ',', $pods_nocache );
			}
			else {
				$nocache = self::$cache_modes;
			}
		}

		if ( 'transient' == $cache_mode && !in_array( $cache_mode, $nocache ) ) {
			$value = get_transient( $group_key . $key );
		}
		elseif ( 'site-transient' == $cache_mode && !in_array( $cache_mode, $nocache ) ) {
			$value = get_site_transient( $group_key . $key );
		}
		elseif ( 'cache' == $cache_mode && $object_cache && !in_array( $cache_mode, $nocache ) ) {
			$value = wp_cache_get( $key, ( empty( $group ) ? 'pods_view' : $group ) );
		}
		elseif ( 'option-cache' == $cache_mode && !in_array( $cache_mode, $nocache ) ) {
			global $_wp_using_ext_object_cache;

			$pre = apply_filters( 'pre_transient_' . $key, false );

			if ( false !== $pre ) {
				$value = $pre;
			}
			elseif ( $_wp_using_ext_object_cache ) {
				$value = wp_cache_get( $key, ( empty( $group ) ? 'pods_option_cache' : $group ) );
				$timeout = wp_cache_get( '_timeout_' . $key, ( empty( $group ) ? 'pods_option_cache' : $group ) );

				if ( !empty( $timeout ) && $timeout < time() ) {
					if ( is_callable( $callback ) ) {
						// Callback function should do it's own set/update for cache
						$callback_value = call_user_func( $callback, $original_key, $group, $cache_mode );

						if ( null !== $callback_value && false !== $callback_value ) {
							$value = $callback_value;
						}

						$called = true;
					}
					else {
						$value = false;

						wp_cache_delete( $key, ( empty( $group ) ? 'pods_option_cache' : $group ) );
						wp_cache_delete( '_timeout_' . $key, ( empty( $group ) ? 'pods_option_cache' : $group ) );
					}
				}
			}
			else {
				$transient_option = '_pods_option_' . $key;
				$transient_timeout = '_pods_option_timeout_' . $key;

				$value = get_option( $transient_option );
				$timeout = get_option( $transient_timeout );

				if ( !empty( $timeout ) && $timeout < time() ) {
					if ( is_callable( $callback ) ) {
						// Callback function should do it's own set/update for cache
						$callback_value = call_user_func( $callback, $original_key, $group, $cache_mode );

						if ( null !== $callback_value && false !== $callback_value ) {
							$value = $callback_value;
						}

						$called = true;
					}
					else {
						$value = false;

						delete_option( $transient_option );
						delete_option( $transient_timeout );
					}
				}
			}

			if ( false !== $value ) {
				$value = apply_filters( 'transient_' . $key, $value );
			}
		}
		else {
			$value = false;
		}

		if ( false === $value && is_callable( $callback ) && !$called ) {
			// Callback function should do it's own set/update for cache
			$callback_value = call_user_func( $callback, $original_key, $group, $cache_mode );

			if ( null !== $callback_value && false !== $callback_value ) {
				$value = $callback_value;
			}
		}

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
     * @param int $expires (optional) Time in seconds for the cache to expire, if 0 no expiration.
     * @param string $cache_mode (optional) Decides the caching method to use for the view.
     * @param string $group (optional) Set the group of the value.
     *
     * @return bool|mixed|null|string|void
     *
     * @since 2.0
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
		elseif ( 'option-cache' == $cache_mode ) {
			global $_wp_using_ext_object_cache;

			$value = apply_filters( 'pre_set_transient_' . $key, $value );

			if ( $_wp_using_ext_object_cache ) {
				$result = wp_cache_set( $key, $value, ( empty( $group ) ? 'pods_option_cache' : $group ) );

				if ( $expires ) {
					$result = wp_cache_set( '_timeout_' . $key, $expires, ( empty( $group ) ? 'pods_option_cache' : $group ) );
				}
			}
			else {
				$transient_timeout = '_pods_option_timeout_' . $key;
				$key = '_pods_option_' . $key;

				if ( false === get_option( $key ) ) {
					if ( $expires ) {
						add_option( $transient_timeout, time() + $expires, '', 'no' );
					}

					$result = add_option( $key, $value, '', 'no' );
				}
				else {
					if ( $expires ) {
						update_option( $transient_timeout, time() + $expires );
					}

					$result = update_option( $key, $value );
				}
			}

			if ( $result ) {
				do_action( 'set_transient_' . $key );
				do_action( 'setted_transient', $key );
			}
		}

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
     * @since 2.0
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
                $group_key = pods_sanitize_like( $group_key );

                $wpdb->query( "DELETE FROM `{$wpdb->options}` WHERE option_name LIKE '_transient_{$group_key}%'" );

                if ( $object_cache )
                    wp_cache_flush();
            }
            else
                delete_transient( $group_key . $key );
        }
        elseif ( 'site-transient' == $cache_mode ) {
            if ( true === $key ) {
                $group_key = pods_sanitize_like( $group_key );

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
		elseif ( 'option-cache' == $cache_mode ) {
			global $_wp_using_ext_object_cache;

			do_action( 'delete_transient_' . $key, $key );

			if ( $_wp_using_ext_object_cache ) {
				$result = wp_cache_delete( $key, ( empty( $group ) ? 'pods_option_cache' : $group ) );

				wp_cache_delete( '_timeout_' . $key, ( empty( $group ) ? 'pods_option_cache' : $group ) );
			}
			else {
				$option_timeout = '_pods_option_timeout_' . $key;
				$option = '_pods_option_' . $key;

				$result = delete_option( $option );

				if ( $result ) {
					delete_option( $option_timeout );
				}
			}

			if ( $result ) {
				do_action( 'deleted_transient', $key );
			}
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
    public static function get_template_part ( $_view, $_data = null ) {
        /* to be reviewed later, should have more checks and restrictions like a whitelist etc
        if ( 0 === strpos( $_view, 'http://' ) || 0 === strpos( $_view, 'https://' ) ) {
            $_view = apply_filters( 'pods_view_url_include', $_view );

            if ( empty( $_view ) || ( defined( 'PODS_REMOTE_VIEWS' ) && PODS_REMOTE_VIEWS ) )
                return '';

            $response = wp_remote_get( $_view );

            return wp_remote_retrieve_body( $response );
        }*/

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
        if ( is_array( $_view ) ) {
            if ( !empty( $_view ) ) {
                if ( isset( $_view[ 0 ] ) && false === strpos( $_view[ 0 ], '.php' ) ) {
                    $_views = array();

                    $_view_count = count( $_view );

                    for ( $_view_x = $_view_count; 0 < $_view_x; $_view_x-- ) {
                        $_view_v = array_slice( $_view, 0, $_view_x );

                        $_views[] = implode( '-', $_view_v ) . '.php';
                    }
                }
                else
                    $_views = $_view;
            }

            $_view = false;

            foreach ( $_views as $_view_check ) {
                $_view = self::locate_template( $_view_check );

                if ( !empty( $_view ) )
                    break;
            }

            return $_view;
        }

        // Keep it safe
        $_view = trim( str_replace( array( '../', '\\' ), array( '', '/' ), (string) $_view ) );
        $_view = preg_replace( '/\/+/', '/', $_view );

        if ( empty( $_view ) )
            return false;

        $_real_view = realpath( $_view );

        if ( empty( $_real_view ) )
            $_real_view = $_view;

        $located = false;

        if ( false === strpos( $_real_view, realpath( WP_PLUGIN_DIR ) ) && false === strpos( $_real_view, realpath( WPMU_PLUGIN_DIR ) ) ) {
            $_real_view = trim( $_real_view, '/' );

            if ( empty( $_real_view ) )
                return false;

            if ( file_exists( realpath( get_stylesheet_directory() . '/' . $_real_view ) ) )
                $located = realpath( get_stylesheet_directory() . '/' . $_real_view );
            elseif ( file_exists( realpath( get_template_directory() . '/' . $_real_view ) ) )
                $located = realpath( get_template_directory() . '/' . $_real_view );
        }
        // Allow includes within plugins directory too for plugins utilizing this
        elseif ( file_exists( $_view ) )
            $located = $_view;
        else
            $located = apply_filters( 'pods_view_locate_template', $located, $_view );

        return $located;
    }

    private static function filter_callback ( $value ) {
        if ( in_array( $value, array( '', null, false ) ) )
            return false;

        return true;
    }
}