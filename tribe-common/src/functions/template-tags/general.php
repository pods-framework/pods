<?php
/**
 * Display functions (template-tags) for use in WordPress templates.
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( ! class_exists( 'Tribe__Main' ) ) {
	return;
}

if ( ! function_exists( 'tribe_get_option' ) ) {
	/**
	 * Get Options
	 *
	 * Retrieve specific key from options array, optionally provide a default return value
	 *
	 * @category Events
	 * @param string $optionName Name of the option to retrieve.
	 * @param string $default    Value to return if no such option is found.
	 *
	 * @return mixed Value of the option if found.
	 * @todo Abstract this function out of template tags or otherwise secure it from other namespace conflicts.
	 */
	function tribe_get_option( $optionName, $default = '' ) {
		$value = Tribe__Settings_Manager::get_option( $optionName, $default );

		/**
		 * Allow filtering of all options retrieved via tribe_get_option().
		 *
		 * @since 4.0.1
		 *
		 * @param mixed $value Value of the option if found.
		 * @param string $optionName Name of the option to retrieve.
		 * @param string $default    Value to return if no such option is found.
		 */
		$value = apply_filters( 'tribe_get_option', $value, $optionName, $default );

		/**
		 * Allow filtering of a specific option retrieved via tribe_get_option().
		 *
		 * @since 4.0.1
		 *
		 * @param mixed $value Value of the option if found.
		 * @param string $optionName Name of the option to retrieve.
		 * @param string $default    Value to return if no such option is found.
		 */
		return apply_filters( "tribe_get_option_{$optionName}", $value, $optionName, $default );
	}
}//end if

if ( ! function_exists( 'tribe_update_option' ) ) {
	/**
	 * Update Option
	 *
	 * Set specific key from options array, optionally provide a default return value
	 *
	 * @category Events
	 * @param string $optionName Name of the option to retrieve.
	 * @param string $value      Value to save
	 *
	 * @return bool
	 */
	function tribe_update_option( $optionName, $value ) {
		return Tribe__Settings_Manager::set_option( $optionName, $value );
	}
}//end if

if ( ! function_exists( 'tribe_remove_option' ) ) {
	/**
	 * Update Option
	 *
	 * Remove specific key from options array
	 *
	 * @category Events
	 * @param string $optionName Name of the option to retrieve.
	 * @param string $value      Value to save
	 *
	 * @return bool
	 */
	function tribe_remove_option( $optionName ) {
		return Tribe__Settings_Manager::remove_option( $optionName );
	}
}//end if

if ( ! function_exists( 'tribe_get_network_option' ) ) {
	/**
	 * Get Network Options
	 *
	 * Retrieve specific key from options array, optionally provide a default return value
	 *
	 * @category Events
	 * @param string $optionName Name of the option to retrieve.
	 * @param string $default    Value to return if no such option is found.
	 *
	 * @return mixed Value of the option if found.
	 * @todo Abstract this function out of template tags or otherwise secure it from other namespace conflicts.
	 */
	function tribe_get_network_option( $optionName, $default = '' ) {
		return Tribe__Settings_Manager::get_network_option( $optionName, $default );
	}
}

if ( ! function_exists( 'tribe_resource_url' ) ) {
	/**
	 * Returns or echoes a url to a file in the Events Calendar plugin resources directory
	 *
	 * @category Events
	 *
	 * @param string $resource the filename of the resource
	 * @param bool   $echo     whether or not to echo the url
	 * @param string $root_dir directory to hunt for resource files (null or the actual path)
	 * @param object $origin   Which plugin we are dealing with
	 *
	 * @return string
	 **/
	function tribe_resource_url( $resource, $echo = false, $root_dir = null, $origin = null ) {
		static $_plugin_url = [];

		if ( is_object( $origin ) ) {
			$plugin_path = ! empty( $origin->plugin_path ) ? $origin->plugin_path : $origin->pluginPath;
		} else {
			$plugin_path = dirname( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) );
		}

		if ( ! isset( $_plugin_url[ $plugin_path ] ) ) {
			$_plugin_url[ $plugin_path ] = trailingslashit( plugins_url( basename( $plugin_path ), $plugin_path ) );
		}
		$plugin_base_url = $_plugin_url[ $plugin_path ];

		$extension = pathinfo( $resource, PATHINFO_EXTENSION );
		$resource_path = $root_dir;

		if ( is_null( $resource_path ) ) {
			$resources_path = 'src/resources/';
			switch ( $extension ) {
				case 'css':
					$resource_path = $resources_path . 'css/';
					break;
				case 'js':
					$resource_path = $resources_path . 'js/';
					break;
				case 'scss':
					$resource_path = $resources_path . 'scss/';
					break;
				default:
					$resource_path = $resources_path;
					break;
			}
		}

		$url = $plugin_base_url . $resource_path . $resource;

		/**
		 * Filters the resource URL
		 *
		 * @param $url
		 * @param $resource
		 */
		$url = apply_filters( 'tribe_resource_url', $url, $resource );

		/**
		 * Deprecated the tribe_events_resource_url filter in 4.0 in favor of tribe_resource_url. Remove in 5.0
		 */
		$url = apply_filters( 'tribe_events_resource_url', $url, $resource );

		if ( $echo ) {
			echo $url;
		}

		return $url;
	}
}//end if

if ( ! function_exists( 'tribe_multi_line_remove_empty_lines' ) ) {
	/**
	 * helper function to remove empty lines from multi-line strings
	 *
	 * @category Events
	 * @link http://stackoverflow.com/questions/709669/how-do-i-remove-blank-lines-from-text-in-php
	 *
	 * @param string $multi_line_string a multiline string
	 *
	 * @return string the same string without empty lines
	 */
	function tribe_multi_line_remove_empty_lines( $multi_line_string ) {
		return preg_replace( "/^\n+|^[\t\s]*\n+/m", '', $multi_line_string );
	}
}//end if

if ( ! function_exists( 'tribe_get_date_format' ) ) {
	/**
	 * Get the date format specified in the tribe options
	 *
	 * @category Events
	 * @param bool $with_year
	 *
	 * @return mixed
	 */
	function tribe_get_date_format( $with_year = false ) {
		if ( $with_year ) {
			$format = tribe_get_date_option( 'dateWithYearFormat', get_option( 'date_format' ) );
		} else {
			$format = tribe_get_date_option( 'dateWithoutYearFormat', 'F j' );
		}

		return apply_filters( 'tribe_date_format', $format );
	}
}//end if

if ( ! function_exists( 'tribe_get_datetime_format' ) ) {
	/**
	 * Get the Datetime Format
	 *
	 * @category Events
	 *
	 * @param bool $with_year
	 *
	 * @return mixed|void
	 */
	function tribe_get_datetime_format( $with_year = false ) {

		$raw_separator = tribe_get_option( 'dateTimeSeparator', ' @ ' );
		$separator     = (array) str_split( $raw_separator );

		if ( empty( $raw_separator ) ) {
		    /**
			 * Filterable fallback for when the dateTimeSeparator is an empty string. Defaults to a space.
			 *
			 * @since 4.5.6
			 *
			 * @param string $fallback The string to use as the fallback.
			 * @param string $raw_separator The raw value of the dateTimeSeparator option.
			 * @return string
			 */
			$separator[0] = apply_filters( 'tribe_empty_datetime_separator_fallback', ' ', $raw_separator );
		}

		$format = tribe_get_date_format( $with_year );
		$format .= ( ! empty( $separator ) ? '\\' : '' ) . implode( '\\', $separator );
		$format .= get_option( 'time_format' );

		return apply_filters( 'tribe_datetime_format', $format );
	}
}//end if

if ( ! function_exists( 'tribe_get_time_format' ) ) {
	/**
	 * Get the time format
	 *
	 * @category Events
	 *
	 * @return mixed|void
	 */
	function tribe_get_time_format( ) {
		static $cache_var_name = __FUNCTION__;

		$format = tribe_get_var( $cache_var_name, null );

		if ( ! $format ) {
			$format = get_option( 'time_format' );
			tribe_set_var( $cache_var_name, $format );
		}

		return apply_filters( 'tribe_time_format', $format );
	}
}//end if

if ( ! function_exists( 'tribe_get_days_between' ) ) {
	/**
	 * Accepts two dates and returns the number of days between them
	 *
	 * @category Events
	 *
	 * @param string      $start_date
	 * @param string      $end_date
	 * @param string|bool $day_cutoff
	 *
	 * @return int
	 * @see Tribe__Date_Utils::date_diff()
	 **/
	function tribe_get_days_between( $start_date, $end_date, $day_cutoff = '00:00' ) {
		if ( $day_cutoff === false ) {
			$day_cutoff = '00:00';
		} elseif ( $day_cutoff === true ) {
			$day_cutoff = tribe_get_option( 'multiDayCutoff', '00:00' );
		}

		$start_date = new DateTime( $start_date );
		if ( $start_date < new DateTime( $start_date->format( 'Y-m-d ' . $day_cutoff ) ) ) {
			$start_date->modify( '-1 day' );
		}
		$end_date = new DateTime( $end_date );
		if ( $end_date <= new DateTime( $end_date->format( 'Y-m-d ' . $day_cutoff ) ) ) {
			$end_date->modify( '-1 day' );
		}

		return Tribe__Date_Utils::date_diff( $start_date->format( 'Y-m-d ' . $day_cutoff ), $end_date->format( 'Y-m-d ' . $day_cutoff ) );
	}
}//end if

if ( ! function_exists( 'tribe_prepare_for_json' ) ) {
	/**
	 * Function to prepare content for use as a value in a json encoded string destined for storage on a html data attribute.
	 * Hence the double quote fun, especially in case they pass html encoded &quot; along. Any of those getting through to the data att will break jQuery's parseJSON method.
	 * Themers can use this function to prepare data they may want to send to tribe_events_template_data() in the templates, and we use it in that function ourselves.
	 *
	 * @category Events
	 *
	 * @param $string
	 *
	 * @return string
	 */
	function tribe_prepare_for_json( $string ) {
		$value = trim( htmlspecialchars( $string, ENT_QUOTES, 'UTF-8' ) );
		$value = str_replace( '&quot;', '"', $value );
		// &amp;#013; is same as \r and JSON strings should be a single line not multiple lines.
		$removable_values = [ '\r', '\n', '\t', '&amp;#013;' ];
		$value = str_replace( $removable_values, '', $value );

		return $value;
	}
}//end if

if ( ! function_exists( 'tribe_prepare_for_json_deep' ) ) {
	/**
	 * Recursively iterate through an nested structure, calling
	 * tribe_prepare_for_json() on all scalar values
	 *
	 * @category Events
	 *
	 * @param mixed $value The data to be cleaned
	 *
	 * @return mixed The clean data
	 */
	function tribe_prepare_for_json_deep( $value ) {
		if ( is_array( $value ) ) {
			$value = array_map( 'tribe_prepare_for_json_deep', $value );
		} elseif ( is_object( $value ) ) {
			$vars = get_object_vars( $value );
			foreach ( $vars as $key => $data ) {
				$value->{$key} = tribe_prepare_for_json_deep( $data );
			}
		} elseif ( is_string( $value ) ) {
			$value = tribe_prepare_for_json( $value );
		}
		return $value;
	}
}//end if

if ( ! function_exists( 'tribe_the_notices' ) ) {
	/**
	 * Generates html for any notices that have been queued on the current view
	 *
	 * @category Events
	 *
	 * @param bool $echo Whether or not to echo the notices html
	 *
	 * @return void | string
	 * @see Tribe__Notices::get()
	 **/
	function tribe_the_notices( $echo = true ) {
		$notices = Tribe__Notices::get();

		$html        = ! empty( $notices ) ? '<div class="tribe-events-notices"><ul><li>' . implode( '</li><li>', $notices ) . '</li></ul></div>' : '';

		/**
		 * Deprecated the tribe_events_the_notices filter in 4.0 in favor of tribe_the_notices. Remove in 5.0
		 */
		$the_notices = apply_filters( 'tribe_events_the_notices', $html, $notices );

		/**
		 * filters the notices HTML
		 */
		$the_notices = apply_filters( 'tribe_the_notices', $html, $notices );
		if ( $echo ) {
			echo $the_notices;
		} else {
			return $the_notices;
		}
	}
}//end if

if ( ! function_exists( 'tribe_is_bot' ) ) {
	/**
	 * tribe_is_bot checks if the visitor is a bot and returns status
	 *
	 * @category Events
	 *
	 * @return bool
	 */
	function tribe_is_bot() {
		// get the current user agent
		$user_agent = strtolower( $_SERVER['HTTP_USER_AGENT'] );

		// check if the user agent is empty since most browsers identify themselves, so possibly a bot
		if ( empty( $user_agent ) ) {
			return apply_filters( 'tribe_is_bot_status', true, $user_agent, null );
		}

		// declare known bot user agents (lowercase)
		$user_agent_bots = (array) apply_filters(
			'tribe_is_bot_list', [
				'bot',
				'slurp',
				'spider',
				'crawler',
				'yandex',
			]
		);

		foreach ( $user_agent_bots as $bot ) {
			if ( stripos( $user_agent, $bot ) !== false ) {
				return apply_filters( 'tribe_is_bot_status', true, $user_agent, $bot );
			}
		}

		// we think this is probably a real human
		return apply_filters( 'tribe_is_bot_status', false, $user_agent, null );
	}
}//end if

if ( ! function_exists( 'tribe_count_hierarchical_keys' ) ) {
	/**
	 * Count keys in a hierarchical array
	 *
	 * @param $value
	 * @param $key
	 * @todo - remove, only used in the meta walker
	 */
	function tribe_count_hierarchical_keys( $value, $key ) {
		global $tribe_count_hierarchical_increment;
		$tribe_count_hierarchical_increment++;
	}
}//end if

if ( ! function_exists( 'tribe_count_hierarchical' ) ) {
	/**
	 * Count items in a hierarchical array
	 *
	 * @param array $walk
	 *
	 * @return int
	 * @todo - remove, only used in the meta walker
	 */
	function tribe_count_hierarchical( array $walk ) {
		global $tribe_count_hierarchical_increment;
		$tribe_count_hierarchical_increment = 0;
		array_walk_recursive( $walk, 'tribe_count_hierarchical_keys' );

		return $tribe_count_hierarchical_increment;
	}
}//end if

if ( ! function_exists( 'tribe_get_mobile_breakpoint' ) ) {
	/**
	 * Mobile breakpoint
	 *
	 * Get the breakpoint for switching to mobile styles. Defaults to 768.
	 *
	 * @category Events
	 *
	 * @param int $default The default width (in pixels) at which to break into mobile styles
	 *
	 * @return int
	 */
	function tribe_get_mobile_breakpoint( $default = 768 ) {
		return apply_filters( 'tribe_events_mobile_breakpoint', $default );
	}
}//end if

if ( ! function_exists( 'tribe_format_currency' ) ) {
	/**
	 * Receives a float and formats it with a currency symbol
	 *
	 * @category Cost
	 * @param string $cost pricing to format
	 * @param null|int $post_id
	 * @param null|string $currency_symbol
	 * @param null|bool $reverse_position
	 *
	 * @return string
	 */
	function tribe_format_currency( $cost, $post_id = null, $currency_symbol = null, $reverse_position = null ) {
		$post_id = Tribe__Main::post_id_helper( $post_id );

		if ( empty( $currency_symbol ) ) {
			$currency_symbol = tribe_get_option( 'defaultCurrencySymbol', '$' );

			/**
			 * Filters the currency symbol that will be used to format the price, defaults
			 * to the one set in the options.
			 *
			 * This will only apply if the currency symbol was not passed as a parameter.
			 *
			 * @since 4.7.7
			 *
			 * @param string $currency_symbol
			 * @param int $post_id
			 */
			$currency_symbol = apply_filters( 'tribe_currency_symbol', $currency_symbol, $post_id );
		}

		if ( null === $reverse_position ) {
			/**
			 * Filters whether the currency symbol that will be used to format the price should be
			 * prefixed (`false`) or appended (`true`) to the price value.
			 *
			 * This will only apply if the currency symbol reverse position not passed as a parameter.
			 *
			 * @since 4.7.7
			 *
			 * @param bool $reverse_position
			 * @param int  $post_id
			 */
			$reverse_position = apply_filters( 'tribe_reverse_currency_position', (bool) $reverse_position, $post_id );
		}

		// if no currency position was passed and we're not looking at a particular event,
		// let's get the default currency position
		if ( null === $reverse_position && ! $post_id ) {
			$reverse_position = tribe_get_option( 'reverseCurrencyPosition', false );
		}

		/**
		 * Add option to filter the cost value before is returned, allowing other providers to hook into it.
		 *
		 * @since 4.7.10
		 *
		 * @param string $cost
		 * @param int $post_id
		 */
		$cost = apply_filters( 'tribe_currency_cost', $cost, $post_id );

		$cost = $reverse_position
			? $cost . $currency_symbol
			: $currency_symbol . $cost;

		/**
		 * Filter the entire formatted string returned.
		 *
		 * @since 4.14.9
		 *
		 * @param string $cost
		 * @param int $post_id
		 */
		return apply_filters( 'tribe_currency_formatted', $cost, $post_id );
	}
}//end if

if ( ! function_exists( 'tribe_get_date_option' ) ) {
	/**
	 * Get a date option.
	 *
	 * Retrieve an option value taking care to escape it to preserve date format slashes.
	 *
	 * @category Events
	 * @param  string $optionName Name of the option to retrieve.
	 * @param string  $default    Value to return if no such option is found.
	 *
	 * @return mixed Value of the option if found
	 */
	function tribe_get_date_option( $optionName, $default = '' ) {
		$value = tribe_get_option( $optionName, $default );

		return Tribe__Date_Utils::unescape_date_format($value);
	}
}

/**
 * Shortcut for Tribe__Admin__Notices::register(), create a Admin Notice easily
 *
 * @param  string          $slug      Slug to save the notice
 * @param  callable|string $callback  A callable Method/Function to actually display the notice
 * @param  array           $arguments Arguments to Setup a notice
 * @param callable|null    $active_callback An optional callback that should return bool values
 *                                          to indicate whether the notice should display or not.
 *
 * @return stdClass Which notice was registered
 */
function tribe_notice( $slug, $callback, $arguments = [], $active_callback = null ) {
	return Tribe__Admin__Notices::instance()->register( $slug, $callback, $arguments, $active_callback );
}

/**
 * Shortcut for Tribe__Admin__Notices::register_transient(), create a transient Admin Notice easily.
 *
 * A transient admin notice is a "fire-and-forget" admin notice that will display once registered and
 * until dismissed (if dismissible) without need, on the side of the source code, to register it on each request.
 *
 * @param  string $slug      Slug to save the notice
 * @param  string $html      The notice output HTML code
 * @param  array  $arguments Arguments to Setup a notice
 * @param int     $expire    After how much time (in seconds) the notice will stop showing.
 *
 * @return stdClass Which notice was registered
 */
function tribe_transient_notice( $slug, $html, $arguments = [], $expire = null ) {
	$expire = null !== $expire ? (int) $expire : WEEK_IN_SECONDS;

	return Tribe__Admin__Notices::instance()->register_transient( $slug, $html, $arguments, $expire );
}

/**
 * Removes a transient notice based on its slug.
 *
 * @since 4.7.7
 *
 * @param string $slug
 */
function tribe_transient_notice_remove( $slug ) {
	Tribe__Admin__Notices::instance()->remove_transient( $slug );
}

/**
 * A quick internal way of sending errors using WP_Error
 *
 * @param  string|array $indexes Which Error we are looking for
 * @param  array        $context Gives the Error context
 * @param  array        $sprintf Allows variables on the message
 *
 * @return WP_Error
 */
function tribe_error( $indexes, $context = [], $sprintf = [] ) {
	return Tribe__Error::instance()->send( $indexes, $context, $sprintf );
}

/**
 * Register a new error based on a Namespace
 *
 * @param  string|array  $indexes  A list of the namespaces and last item should be the error name
 * @param  string        $message  What is going to be the message associate with this indexes
 *
 * @return boolean
 */
function tribe_register_error( $indexes, $message ) {
	return Tribe__Error::instance()->register( $indexes, $message );
}

/**
 * Shortcut for Tribe__Assets::register(), include a single asset
 *
 * @since 4.3
 *
 * @param object            $origin    The main object for the plugin you are enqueueing the asset for.
 * @param string            $slug      Slug to save the asset - passes through `sanitize_title_with_dashes()`.
 * @param string            $file      The asset file to load (CSS or JS), including non-minified file extension.
 * @param array             $deps      The list of dependencies or callable function that will return a list of dependencies.
 * @param string|array|null $action    The WordPress action(s) to enqueue on, such as `wp_enqueue_scripts`,
 *                                     `admin_enqueue_scripts`, or `login_enqueue_scripts`.
 * @param array             $arguments See `Tribe__Assets::register()` for more info.
 *
 * @return object|false     The asset that got registered or false on error.
 */
function tribe_asset( $origin, $slug, $file, $deps = [], $action = null, $arguments = [] ) {
	/** @var Tribe__Assets $assets */
	$assets = tribe( 'assets' );

	return $assets->register( $origin, $slug, $file, $deps, $action, $arguments );
}

/**
 * Shortcut for Tribe__Assets::enqueue() to include assets.
 *
 * @since 4.7
 *
 * @param string|array $slug Slug to enqueue
 */
function tribe_asset_enqueue( $slug ) {
	/** @var Tribe__Assets $assets */
	$assets = tribe( 'assets' );

	$assets->enqueue( $slug );
}

/**
 * Shortcut for Tribe__Assets::enqueue_group() include assets by groups.
 *
 * @since 4.7
 *
 * @param string|array  $group  Which group(s) should be enqueued.
 */
function tribe_asset_enqueue_group( $group ) {
	/** @var Tribe__Assets $assets */
	$assets = tribe( 'assets' );

	$assets->enqueue_group( $group );
}

/**
 * Function to include more the one asset, based on `tribe_asset`
 *
 * @since 4.3
 * @since 4.12.10 Added support for overriding arguments for individual assets.
 *
 * @param  object   $origin     The main Object for the plugin you are enqueueing the script/style for
 * @param  array    $assets     {
 *    Indexed array, don't use any associative key.
 *    E.g.: [ 'slug-my-script', 'my/own/path.js', [ 'jquery' ] ]
 *
 *    @type  string   $slug       Slug to save the asset
 *    @type  string   $file       Which file will be loaded, either CSS or JS
 *    @type  array    $deps       (optional) Dependencies
 * }
 * @param  string   $action     A WordPress hook that will automatically enqueue this asset once fired
 * @param  array    $arguments  Look at `Tribe__Assets::register()` for more info
 *
 * @return array             Which Assets were registered
 */
function tribe_assets( $origin, $assets, $action = null, $arguments = [] ) {
	$registered = [];

	foreach ( $assets as $asset ) {
		if ( ! is_array( $asset ) ) {
			continue;
		}

		$slug = reset( $asset );
		if ( empty( $asset[1] ) ) {
			continue;
		}

		$file = $asset[1];
		$deps = ! empty( $asset[2] ) ? $asset[2] : [];

		// Support the asset having a custom action.
		$asset_action = ! empty( $asset[3] ) ? $asset[3] : $action;

		// Support the asset having custom arguments and merge them with the original ones.
		$asset_arguments = ! empty( $asset[4] ) ? array_merge( $arguments, $asset[4] ) : $arguments;

		$registered[] = tribe_asset( $origin, $slug, $file, $deps, $asset_action, $asset_arguments );

	}

	return $registered;
}

if ( ! function_exists( 'tribe_doing_frontend' ) ) {
	/**
	 * Registers truthy or falsy callbacks on the filters used to detect if
	 * any frontend operation is being done for logged in users or not.
	 *
	 * @since 4.7.4
	 *
	 * @param bool $doing_frontend Whether what is being done happens in the
	 *                             context of the frontend or not.
	 */
	function tribe_doing_frontend( $doing_frontend ) {
		$callback = $doing_frontend ? '__return_true' : '__return_false';

		add_filter( 'tribe_doing_frontend', $callback );
	}
}

if ( ! function_exists( 'tribe_is_frontend' ) ) {
	/**
	 * Whether we are currently performing a frontend operation or not.
	 *
	 * @since 4.6.2
	 *
	 * @return bool
	 */
	function tribe_is_frontend() {
		/**
		 * Whether we are currently performing a frontend operation or not.
		 *
		 * @since 4.6.2
		 *
		 * @param bool $is_frontend
		 */
		return (bool) apply_filters( 'tribe_doing_frontend', false );
	}
}

if ( ! function_exists( 'tribe_set_time_limit' ) ) {
	/**
	 * Wrapper for set_time_limit to suppress errors
	 *
	 * @since 4.7.12
	 *
	 * @param int $limit Time limit.
	 */
	function tribe_set_time_limit( $limit = 0 ) {
		if (
			! function_exists( 'set_time_limit' )
			&& false !== strpos( ini_get( 'disable_functions' ), 'set_time_limit' )
			&& ini_get( 'safe_mode' )
		) {
			return false;
		}

		return @set_time_limit( $limit );
	}
}

if ( ! function_exists( 'tribe_context' ) ) {
	/**
	 * A wrapper function to get the singleton, immutable, global context object.
	 *
	 * Due to its immutable nature any method that would modify the context will return
	 * a clone of the context, not the original one.
	 *
	 * @since 4.9.5
	 *
	 * @return Tribe__Context The singleton, immutable, global object instance.
	 */
	function tribe_context() {
		$context = tribe( 'context' );

		/**
		 * Filters the global context object.
		 *
		 * @since 4.9.5
		 *
		 * @param Tribe__Context $context The singleton, immutable, global object instance.
		 */
		$context = apply_filters( 'tribe_global_context', $context );

		return $context;
	}
}

if ( ! function_exists( 'tribe_cache' ) ) {
	/**
	 * Returns the current Tribe Cache instance.
	 *
	 * @since 4.11.2
	 *
	 * @return Tribe__Cache The current cache instance.
	 */
	function tribe_cache() {
		return tribe( 'cache' );
	}
}

if ( ! function_exists( 'tribe_asset_print_group' ) ) {
	/**
	 * Prints the `script` (JS) and `link` (CSS) HTML tags associated with one or more assets groups.
	 *
	 * @since 4.12.6
	 *
	 * @param string|array $group Which group(s) should be enqueued.
	 * @param bool         $echo  Whether to print the group(s) tag(s) to the page or not; default to `true` to
	 *                            print the HTML `script` (JS) and `link` (CSS) tags to the page.
	 *
	 * @return string The `script` and `link` HTML tags produced for the group(s).
	 */
	function tribe_asset_print_group( $group, $echo = true ) {
		/** @var \Tribe__Assets $assets */
		$assets     = tribe( 'assets' );

		return $assets->print_group($group, $echo);
	}
}

if ( ! function_exists( 'tribe_doing_shortcode' ) ) {
	/**
	 * Check whether a specific shortcode is being run.
	 *
	 * This is limited to only shortcodes registered with Tribe\Shortcode\Manager.
	 *
	 * @since 4.12.10
	 *
	 * @param null|string $tag The shortcode tag name, or null to check if doing any shortcode.
	 *
	 * @return bool Whether the shortcode is currently being run.
	 */
	function tribe_doing_shortcode( $tag = null ) {
		return tribe( 'shortcode.manager' )->is_doing_shortcode( $tag );
	}
}
