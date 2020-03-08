<?php

use Tribe\Traits\Cache_User;
use Tribe__Cache_Listener as Listener;
use Tribe__Utils__Array as Arr;

/**
 * Class Tribe__Rewrite
 *
 * Utilities to generate and manipulate rewrite rules.
 *
 * @since 4.3
 */
class Tribe__Rewrite {
	use Cache_User;

	/**
	 * If we wish to setup a rewrite rule that uses percent symbols, we'll need
	 * to make use of this placeholder.
	 */
	const PERCENT_PLACEHOLDER = '~~TRIBE~PC~~';

	/**
	 * Static singleton variable.
	 *
	 * @var static
	 */
	public static $instance;

	/**
	 * WP_Rewrite Instance
	 *
	 * @var WP_Rewrite
	 */
	public $rewrite;

	/**
	 * Rewrite rules Holder
	 *
	 * @var array
	 */
	public $rules = array();

	/**
	 * Base slugs for rewrite urls
	 *
	 * @var array
	 */
	public $bases = array();
	/**
	 * After creating the Hooks on WordPress we lock the usage of the function.
	 *
	 * @var boolean
	 */
	protected $hook_lock = false;

	/**
	 * An array cache of resolved canonical URLs in the shape `[ <url> => <canonical_url> ]`.
	 *
	 * @since 4.9.11
	 *
	 * @var array
	 */
	protected $canonical_url_cache = null;

	/**
	 * An array cache of parsed URLs in the shape `[ <url> => <parsed_vars> ]`.
	 *
	 * @since 4.9.11
	 *
	 * @var array
	 */
	protected $parse_request_cache = null;

	/**
	 * And array cache of cleaned URLs.
	 *
	 * @since 4.9.11
	 *
	 * @var array
	 */
	protected $clean_url_cache = null;

	/**
	 * Static Singleton Factory Method
	 *
	 * @return self
	 */
	public static function instance() {
		if ( ! static::$instance ) {
			static::$instance = new static;
		}

		return static::$instance;
	}

	/**
	 * Tribe__Rewrite constructor.
	 *
	 * @param WP_Rewrite|null $wp_rewrite
	 */
	public function __construct( WP_Rewrite $wp_rewrite = null ) {
		$this->rewrite = $wp_rewrite;
	}

	/**
	 * When you are going to use any of the functions to create new rewrite rules you need to setup first
	 *
	 * @param  WP_Rewrite|null $wp_rewrite Pass the WP_Rewrite if you have it
	 *
	 * @return Tribe__Rewrite       The modified version of the class with the required variables in place
	 */
	public function setup( $wp_rewrite = null ) {
		if ( ! $wp_rewrite instanceof WP_Rewrite ) {
			global $wp_rewrite;
		}

		$this->rewrite = $wp_rewrite;
		$this->bases   = $this->get_bases( 'regex' );

		return $this;
	}

	/**
	 * Generate the Rewrite Rules
	 *
	 * @param  WP_Rewrite $wp_rewrite WordPress Rewrite that will be modified, pass it by reference (&$wp_rewrite)
	 */
	public function filter_generate( WP_Rewrite $wp_rewrite ) {
		// Gets the rewrite bases and completes any other required setup work
		$this->setup( $wp_rewrite );

		/**
		 * Use this to change the Tribe__Rewrite instance before new rules
		 * are committed.
		 *
		 * Should be used when you want to add more rewrite rules without having to
		 * deal with the array merge, noting that rules for The Events Calendar are
		 * themselves added via this hook (default priority).
		 *
		 * @var Tribe__Rewrite $rewrite
		 */
		do_action( 'tribe_pre_rewrite', $this );
	}


	/**
	 * Do not allow people to Hook methods twice by mistake
	 */
	public function hooks( $remove = false ) {
		if ( false === $this->hook_lock ) {
			// Don't allow people do Double the hooks
			$this->hook_lock = true;

			$this->add_hooks();
		} elseif ( true === $remove ) {
			$this->remove_hooks();
		}
	}

	/**
	 * Converts any percentage placeholders in the array keys back to % symbols.
	 *
	 * @param  array $rules
	 *
	 * @return array
	 */
	public function remove_percent_placeholders( array $rules ) {
		foreach ( $rules as $key => $value ) {
			$this->replace_array_key( $rules, $key, str_replace( self::PERCENT_PLACEHOLDER, '%', $key ) );
		}

		return $rules;
	}

	protected function add_hooks() {
		add_filter( 'generate_rewrite_rules', array( $this, 'filter_generate' ) );

		// Remove percent Placeholders on all items
		add_filter( 'rewrite_rules_array', array( $this, 'remove_percent_placeholders' ), 25 );

		add_action( 'shutdown', [ $this, 'dump_cache' ] );
	}

	protected function remove_hooks() {
		remove_filter( 'generate_rewrite_rules', array( $this, 'filter_generate' ) );
		remove_filter( 'rewrite_rules_array', array( $this, 'remove_percent_placeholders' ), 25 );

		remove_action( 'shutdown', [ $this, 'dump_cache' ] );
	}

	/**
	 * Get the base slugs for the rewrite rules.
	 *
	 * WARNING: Don't mess with the filters below if you don't know what you are doing
	 *
	 * @param  string $method Use "regex" to return a Regular Expression with the possible Base Slugs using l10n
	 *
	 * @return object         Return Base Slugs with l10n variations
	 */
	public function get_bases( $method = 'regex' ) {
		return new stdClass();
	}

	/**
	 * The base method for creating a new Rewrite rule
	 *
	 * @param array|string $regex The regular expression to catch the URL
	 * @param array        $args  The arguments in which the regular expression "alias" to
	 *
	 * @return Tribe__Events__Rewrite
	 */
	public function add( $regex, $args = array() ) {
		$regex = (array) $regex;

		$default = array();
		$args    = array_filter( wp_parse_args( $args, $default ) );

		$url = add_query_arg( $args, 'index.php' );

		// Optional Trailing Slash
		$regex[] = '?$';

		// Glue the pieces with slashes
		$regex = implode( '/', array_filter( $regex ) );

		// Add the Bases to the regex
		foreach ( $this->bases as $key => $value ) {
			$regex = str_replace( array( '{{ ' . $key . ' }}', '{{' . $key . '}}' ), $value, $regex );
		}

		// Apply the Preg Indexes to the URL
		preg_match_all( '/%([0-9])/', $url, $matches );
		foreach ( end( $matches ) as $index ) {
			$url = str_replace( '%' . $index, $this->rewrite->preg_index( $index ), $url );
		}

		// Add the rule
		$this->rules[ $regex ] = $url;

		return $this;
	}

	/**
	 * Returns a sanitized version of $slug that can be used in rewrite rules.
	 *
	 * This is ideal for those times where we wish to support internationalized
	 * URLs (ie, where "venue" in "venue/some-slug" may be rendered in non-ascii
	 * characters).
	 *
	 * In the case of registering new post types, $permastruct_name should
	 * generally match the CPT name itself.
	 *
	 * @param  string $slug
	 * @param  string $permastruct_name
	 * @param  string $is_regular_exp
	 *
	 * @return string
	 */
	public function prepare_slug( $slug, $permastruct_name, $is_regular_exp = true ) {
		$needs_handling = false;
		$sanitized_slug = sanitize_title( $slug );

		// Was UTF8 encoding required for the slug? %a0 type entities are a tell-tale of this
		if ( preg_match( '/(%[0-9a-f]{2})+/', $sanitized_slug ) ) {
			/**
			 * Controls whether special UTF8 URL handling is setup for the set of
			 * rules described by $permastruct_name.
			 *
			 * This only fires if Tribe__Events__Rewrite::prepare_slug() believes
			 * handling is required.
			 *
			 * @var string $permastruct_name
			 * @var string $slug
			 */
			$needs_handling = apply_filters(
				'tribe_events_rewrite_utf8_handling', true, $permastruct_name, $slug
			);
		}

		if ( $needs_handling ) {
			// User agents encode things the same way but in uppercase
			$sanitized_slug = strtoupper( $sanitized_slug );

			// UTF8 encoding results in lots of "%" chars in our string which play havoc
			// with WP_Rewrite::generate_rewrite_rules(), so we swap them out temporarily
			$sanitized_slug = str_replace( '%', Tribe__Rewrite::PERCENT_PLACEHOLDER, $sanitized_slug );
		}

		$prepared_slug = $is_regular_exp ? preg_quote( $sanitized_slug ) : $sanitized_slug;

		/**
		 * Provides an opportunity to modify the sanitized slug which will be used
		 * in rewrite rules relating to $permastruct_name.
		 *
		 * @var string $prepared_slug
		 * @var string $permastruct_name
		 * @var string $original_slug
		 */
		return apply_filters( 'tribe_rewrite_prepared_slug', $prepared_slug, $permastruct_name, $slug );
	}

	/**
	 * A way to replace an Array key without destroying the array ordering
	 *
	 * @since  4.0.6
	 *
	 * @param  array  &$array  The Rules Array should be used here
	 * @param  string $search  Search for this Key
	 * @param  string $replace Replace with this key]
	 *
	 * @return bool            Did we replace anything?
	 */
	protected function replace_array_key( &$array, $search, $replace ) {
		$keys  = array_keys( $array );
		$index = array_search( $search, $keys );

		if ( false !== $index ) {
			$keys[ $index ] = $replace;
			$array          = array_combine( $keys, $array );

			return true;
		}

		return false;
	}

	/**
	 * Returns the canonical URLs associated with a ugly link.
	 *
	 * This method will handle "our" URLs to go from their ugly form, filled with query vars, to the "pretty" one, if
	 * possible.
	 *
	 * @since 4.9.11
	 *
	 * @param string $url The URL to try and translate into its canonical form.
	 * @param bool   $force Whether to try and use the cache or force a new canonical URL conversion.
	 *
	 * @return string The canonical URL, or the input URL if it could not be resolved to a canonical one.
	 */
	public function get_canonical_url( $url, $force = false ) {
		if ( get_class( $this ) === Tribe__Rewrite::class ) {
			throw new BadMethodCallException(
				'Method get_canonical_url should only be called on extending classes.'
			);
		}

		if ( null === $this->rewrite ) {
			// We re-do this check here as the object might have been initialized before the global rewrite was set.
			$this->setup();
		}

		/**
		 * Filters the canonical URL for an input URL before any kind of logic runs.
		 *
		 * @since 4.9.11
		 *
		 * @param string|null    $canonical_url The canonical URL, defaults to `null`; returning a non `null` value will
		 *                                      make the logic bail and return the value.
		 * @param string         $url           The input URL to resolve to a canonical one.
		 * @param Tribe__Rewrite $this          This rewrite object.
		 */
		$canonical_url = apply_filters( 'tribe_rewrite_pre_canonical_url', null, $url );
		if ( null !== $canonical_url ) {
			return $canonical_url;
		}

		$home_url = home_url();

		// It's not a path we, or WP, could possibly handle.
		$has_http_scheme = (bool) parse_url( $url, PHP_URL_SCHEME );
		if (
			$home_url === $url
			|| ( $has_http_scheme && false === strpos( $url, $home_url ) )
		) {
			return $url;
		}

		$canonical_url = $url;
		// To avoid issues with missing `path` component let's always add a trailing '/'.
		if ( false !== strpos( $url, '?' ) ) {
			$canonical_url = preg_replace( '~(\\/)*\\?~', '/?', $canonical_url );
		} elseif ( false !== strpos( $url, '#' ) ) {
			$canonical_url = preg_replace( '~(\\/)*#~', '/#', $canonical_url );
		}

		// Canonical URLs are supposed to contain the home URL.
		if ( false === strpos( $canonical_url, $home_url ) ) {
			$canonical_url = home_url( $canonical_url );
		}

		if ( empty( $canonical_url ) ) {
			return $home_url;
		}

		if ( ! $force ) {
			$this->warmup_cache(
				'canonical_url',
				WEEK_IN_SECONDS,
				Listener::TRIGGER_GENERATE_REWRITE_RULES
			);
			if ( isset( $this->canonical_url_cache[ $url ] ) ) {
				return $this->canonical_url_cache[ $url ];
			}
		}

		$query         = (string) parse_url( $url, PHP_URL_QUERY );
		wp_parse_str( $query, $query_vars );

		// Remove the `paged` query var if it's 1.
		if ( isset( $query_vars['paged'] ) && 1 === (int) $query_vars['paged'] ) {
			unset( $query_vars['paged'] );
		}

		ksort( $query_vars );

		$our_rules          = $this->get_handled_rewrite_rules();
		$handled_query_vars = $this->get_rules_query_vars( $our_rules );

		if (
			empty( $our_rules )
			|| ! in_array( Arr::get( $query_vars, 'post_type', 'post' ), $this->get_post_types(), true )
		) {
			$wp_canonical = redirect_canonical( $canonical_url, false );
			if ( empty( $wp_canonical ) ) {
				$wp_canonical = $canonical_url;
			}

			$this->canonical_url_cache[ $url ] = $wp_canonical;

			return $wp_canonical;
		}

		$bases = (array) $this->get_bases();
		ksort( $bases );

		$localized_matchers = $this->get_localized_matchers();
		$dynamic_matchers   = $this->get_dynamic_matchers( $query_vars );

		// Try to match only on the query vars we're actually handling.
		$matched_vars   = array_intersect_key( $query_vars, array_combine( $handled_query_vars, $handled_query_vars ) );
		$unmatched_vars = array_diff_key( $query_vars, array_combine( $handled_query_vars, $handled_query_vars ) );

		if ( empty( $matched_vars ) ) {
			// The URL does contain query vars, but none we handle.
			$wp_canonical = trailingslashit( redirect_canonical( $url, false ) );
			$this->canonical_url_cache[ $url ] = $wp_canonical;

			return $wp_canonical;
		}

		$found = false;

		foreach ( $our_rules as $link_template => $index_path ) {
			wp_parse_str( (string) parse_url( $index_path, PHP_URL_QUERY ), $link_vars );
			ksort( $link_vars );

			if ( array_keys( $link_vars ) !== array_keys( $matched_vars ) ) {
				continue;
			}

			if ( ! (
				Arr::get( $matched_vars, 'post_type', '' ) === Arr::get( $link_vars, 'post_type', '' )
				&& Arr::get( $matched_vars, 'eventDisplay', '' ) === Arr::get( $link_vars, 'eventDisplay', '' )
			) ) {
				continue;
			}

			$replace = array_map( function ( $localized_matcher ) use ( $matched_vars ) {
				if ( ! is_array( $localized_matcher ) ) {
					// For the dates.
					return isset( $matched_vars[ $localized_matcher ] )
						? $matched_vars[ $localized_matcher ]
						: '';
				}

				$query_var  = $localized_matcher['query_var'];
				$query_vars = [ $query_var ];

				if ( $query_var === 'name' ) {
					$query_vars = array_merge( $query_vars, $this->get_post_types() );
				}

				if ( ! array_intersect( array_keys( $matched_vars ), $query_vars ) ) {
					return '';
				}

				/*
				 * We use `end` as, by default, the localized version of the slug in the current language will be at the
				 * end of the array.
				 * @todo here we should keep a map, that has to generated at permalink flush time, to map locales/slugs.
				 */
				return end( $localized_matcher['localized_slugs'] );
			}, $localized_matchers );

			// Include dynamic matchers now.
			$replace = array_merge( $dynamic_matchers, $replace );
			$replaced = str_replace( array_keys( $replace ), $replace, $link_template );

			// Remove trailing chars.
			$path     = rtrim( $replaced, '?$' );
			$resolved = trailingslashit( home_url( $path ) );
			$found = true;

			break;
		}

		if ( empty( $resolved ) ) {
			$wp_canonical = redirect_canonical( $canonical_url, false );
			$resolved     = empty( $wp_canonical ) ? $canonical_url : $wp_canonical;
		}

		if ( $canonical_url !== $resolved ) {
			// Be sure to add a trailing slash to the URL; before `?` or `#`.
			$resolved = preg_replace( '/(?<!\\/)(#|\\?)/u', '/$1', $resolved );
		}

		if ( count( $unmatched_vars ) ) {
			$resolved = add_query_arg( $unmatched_vars, $resolved );
		}

		/**
		 * Filters the resolved canonical URL to allow third party code to modify it.
		 *
		 * Mind that the value will be cached and hence this filter will fire once per URL per request and, second, this
		 * filter will fire after all the logic to resolve the URL ran. If you want to filter the canonical URL before
		 * the logic runs then use the `tribe_rewrite_pre_canonical_url` filter.
		 *
		 * @since 4.9.11
		 *
		 * @param string         $resolved The resolved, canonical URL.
		 * @param string         $url      The original URL to resolve.
		 * @param Tribe__Rewrite $this     This object.
		 */
		$resolved = apply_filters( 'tribe_rewrite_canonical_url', $resolved, $url, $this );

		if ( $found ) {
			// Since we're caching let's not cache unmatched rules to allow for their later, valid resolution.
			$this->canonical_url_cache[ $url ] = $resolved;
		}

		return $resolved;
	}

	/**
	 * Returns an array of rewrite rules handled by the implementation.
	 *
	 * @since 4.9.11
	 *
	 * @return array An array of rewrite rules handled by the implementation in the shape `[ <regex> => <path> ]`.
	 */
	protected function get_handled_rewrite_rules() {
		// We need to make sure we are have WP_Rewrite setup
		if ( ! $this->rewrite ) {
			$this->setup();
		}

		// While this is specific to The Events Calendar we're handling a small enough post type base to keep it here.
		$pattern = '/post_type=tribe_(events|venue|organizer)/';
		// Reverse the rules to try and match the most complex first.
		$rules     = isset( $this->rewrite->rules ) ? (array) $this->rewrite->rules : [];
		$our_rules = array_filter( $rules,
			static function ( $rule_query_string ) use ( $pattern ) {
				return preg_match( $pattern, $rule_query_string );
			}
		);

		/**
		 * Filters the list of rewrite rules handled by our code to add or remove some as required.
		 *
		 * @since  4.9.18
		 *
		 * @param array $our_rules An array of rewrite rules handled by our code, in the shape
		 *                         `[ <rewrite_rule_regex_pattern> => <query_string> ]`.
		 *                         E.g. `[ '(?:events)/(?:list)/?$' => 'index.php?post_type=tribe_events&eventDisplay=list' ]`.
		 */
		$our_rules = apply_filters( 'tribe_rewrite_handled_rewrite_rules', $our_rules );

		return $our_rules;
	}

	/**
	 * Returns a map relating localized regex matchers to query vars.
	 *
	 * @since 4.9.11
	 *
	 * @return array A map of localized regex matchers in the shape `[ <localized_regex> => <query_var> ]`.
	 */
	protected function get_localized_matchers() {
		$bases         = (array) $this->get_bases();
		$query_var_map = $this->get_matcher_to_query_var_map();

		$localized_matchers = [];
		foreach ( $bases as $base => $localized_matcher ) {
			if ( isset( $query_var_map[ $base ] ) ) {
				$localized_matchers[ $localized_matcher ] = [
					'query_var'       => $query_var_map[ $base ],
					'en_slug'         => $base,
					'localized_slugs' => [ $base ],
				];
				// If we have the localized slug version then let's parse it.
				preg_match( '/^\\(\\?:(?<slugs>[^\\)]+)\\)$/u', $localized_matcher, $buffer );
				if ( ! empty( $buffer['slugs'] ) ) {
					$slugs = explode( '|', $buffer['slugs'] );

					$localized_matchers[ $localized_matcher ]['localized_slugs'] = array_map(
						static function ( $localized_slug ) {
							return str_replace( '\-', '-', $localized_slug );
						},
						$slugs
					);

					// The English version is the first.
					$localized_matchers[ $localized_matcher ]['en_slug'] = reset( $slugs );
				}
			}
		}

		return $localized_matchers;
	}

	/**
	 * Returns a map relating localize matcher slugs to the corresponding query var.
	 *
	 * @since 4.9.11
	 *
	 * @return array A map relating localized matcher slugs to the corresponding query var.
	 */
	protected function get_matcher_to_query_var_map() {
		throw new BadMethodCallException(
			'This method should not be called on the base class (' . __CLASS__ . '); only on extending classes.'
		);
	}

	/**
	 * Return a list of the query vars handled in the input rewrite rules.
	 *
	 * @since 4.9.11
	 *
	 * @param array $rules A set of rewrite rules in the shape `[ <regex> => <path> ]`.
	 *
	 * @return array A list of all the query vars handled in the rules.
	 */
	protected function get_rules_query_vars( array $rules ) {
		return array_unique( array_filter( array_merge( [], ...
				array_values( array_map( static function ( $rule_string ) {
					wp_parse_str( parse_url( $rule_string, PHP_URL_QUERY ), $vars );

					return array_keys( $vars );
				}, $rules ) ) ) )
		);
	}

	/**
	 * Sets up the dynamic matchers based on the link query vars.
	 *
	 * @since 4.9.11
	 *
	 * @param array $query_vars An map of query vars and their values.
	 *
	 * @return array A map of dynamic matchers in the shape `[ <regex> => <value> ]`.
	 */
	protected function get_dynamic_matchers( array $query_vars ) {
		$bases            = (array) $this->get_bases();
		$dynamic_matchers = [];

		/*
		 * In some instance we use the `page` (w/o `d`) to paginate a dynamic archive.
		 * Let's support that too.
		 * It's important to add `page` after `paged` to try and match the longest (`paged`) first.
		 */
		foreach ( [ 'paged', 'page' ] as $page_var ) {
			if ( isset( $query_vars[ $page_var ] ) ) {
				$page_regex = $bases['page'];
				preg_match( '/^\(\?:(?<slugs>[^\\)]+)\)/', $page_regex, $matches );
				if ( isset( $matches['slugs'] ) ) {
					$slugs = explode( '|', $matches['slugs'] );
					// The localized version is the last.
					$localized_slug = end( $slugs );
					// We use two different regular expressions to read pages, let's add both.
					$dynamic_matchers["{$page_regex}/(\d+)"]       = "{$localized_slug}/{$query_vars[$page_var]}";
					$dynamic_matchers["{$page_regex}/([0-9]{1,})"] = "{$localized_slug}/{$query_vars[$page_var]}";
				}
			}
		}

		if ( isset( $query_vars['tag'] ) ) {
			$tag      = $query_vars['tag'];
			$tag_term = get_term_by( 'slug', $tag, 'post_tag' );

			if ( $tag_term instanceof WP_Term ) {
				// Let's actually add the matcher only if the tag exists.
				$tag_regex = $bases['tag'];
				preg_match( '/^\(\?:(?<slugs>[^\\)]+)\)/', $tag_regex, $matches );
				if ( isset( $matches['slugs'] ) ) {
					$slugs = explode( '|', $matches['slugs'] );
					// The localized version is the last.
					$localized_slug                           = end( $slugs );
					$dynamic_matchers["{$tag_regex}/([^/]+)"] = "{$localized_slug}/{$tag}";
				}
			}
		}

		if ( isset( $query_vars['feed'] ) ) {
			$feed_regex                      = 'feed/(feed|rdf|rss|rss2|atom)';
			$dynamic_matchers[ $feed_regex ] = "feed/{$query_vars['feed']}";
		}

		return $dynamic_matchers;
	}

	/**
	 * Returns a list of post types supported by the implementation.
	 *
	 * @since 4.9.11
	 */
	protected function get_post_types() {
		throw new BadMethodCallException( 'Method get_post_types should be implemented by extending classes.' );
	}

	/**
	 * Parses a URL to produce an array of query variables.
	 *
	 * Most of this functionality was copied from `WP::parse_request()` method
	 * with some changes to avoid conflicts and removing non-required behaviors.
	 *
	 * @since  4.9.11
	 *
	 * @param string $url              The URLto parse.
	 * @param array  $extra_query_vars An associative array of extra query vars to use for the parsing. These vars will
	 *                                 be read before the WordPress defined ones overriding them.
	 * @param bool   $force Whether to try and use the cache or force a new canonical URL conversion.
	 *
	 * @return array An array of query vars, as parsed from the input URL.
	 */
	public function parse_request( $url, array $extra_query_vars = [], $force = false ) {
		if ( null === $this->rewrite ) {
			// We re-do this check here as the object might have been initialized before the global rewrite was set.
			$this->setup();
		}

		/**
		 * Allows short-circuiting the URL parsing.
		 *
		 * This filter will run before any logic runs, its result will not be cached and this filter will be called on
		 * each call to this method.
		 * Returning a non `null` value here will short-circuit this logic.
		 *
		 * @since 4.9.11
		 *
		 * @param array  $query_vars       The parsed query vars array.
		 * @param array  $extra_query_vars An associative array of extra query vars that will be processed before the
		 *                                 WordPress defined ones.
		 * @param string $url              The URL to parse.
		 */
		$parsed = apply_filters( 'tribe_rewrite_pre_parse_query_vars', null, $extra_query_vars, $url );
		if ( null !== $parsed ) {
			return $parsed;
		}

		if ( ! $force ) {
			$this->warmup_cache(
				'parse_request',
				WEEK_IN_SECONDS,
				Listener::TRIGGER_GENERATE_REWRITE_RULES
			);
			if ( isset( $this->parse_request_cache[ $url ] ) ) {
				return $this->parse_request_cache[ $url ];
			}
		}

		$query_vars           = [];
		$post_type_query_vars = [];
		$perma_query_vars     = [];
		$url_components = parse_url($url);
		$url_path = Arr::get( $url_components, 'path', '/' );
		$url_query = Arr::get( $url_components, 'query', '' );
		parse_str( $url_query, $url_query_vars );
		// Look for matches, removing leading `/` char.
		$request_match = ltrim( $url_path, '/' );

		// Fetch the rewrite rules.
		$rewrite_rules = $this->rewrite->wp_rewrite_rules();
		$matched_rule = false;

		if ( ! empty( $rewrite_rules ) ) {
			foreach ( (array) $rewrite_rules as $match => $query ) {
				$matches_regex = preg_match( "#^$match#", $request_match, $matches )
				                 || preg_match( "#^$match#", urldecode( $request_match ), $matches );

				if ( ! $matches_regex ) {
					continue;
				}

				if (
					$this->rewrite->use_verbose_page_rules
					&& preg_match( '/pagename=\$matches\[([0-9]+)\]/', $query, $varmatch )
				) {
					// This is a verbose page match, let's check to be sure about it.
					$page = get_page_by_path( $matches[ $varmatch[1] ] );
					if ( ! $page ) {
						continue;
					}
					$post_status_obj = get_post_status_object( $page->post_status );
					if (
						! $post_status_obj->public
						&& ! $post_status_obj->protected
						&& ! $post_status_obj->private
						&& $post_status_obj->exclude_from_search
					) {
						continue;
					}
				}

				// Got a match.
				$matched_rule = $match;
				break;
			}

			if ( false !== $matched_rule ) {
				// Trim the query of everything up to the '?'.
				$query = preg_replace( '!^.+\?!', '', $query );
				// Substitute the substring matches into the query.
				$query = addslashes( WP_MatchesMapRegex::apply( $query, $matches ) );
				// Parse the query.
				parse_str( $query, $perma_query_vars );
			}
		}

		foreach ( get_post_types( [], 'objects' ) as $post_type => $t ) {
			if (
				is_post_type_viewable( $t )
				&& $t->query_var
			) {
				$post_type_query_vars[ $t->query_var ] = $post_type;
			}
		}

		global $wp;

		/*
		 * WordPress would apply this filter in the `parse_request` method to allow the registration of additional query
		 * vars. They might not have been registered at this point so we do this again making sure to avoid duplicates.
		 */
		$public_query_vars = array_unique( apply_filters( 'query_vars', $wp->public_query_vars ) );

		foreach ( $public_query_vars as $wpvar ) {
			if ( isset( $extra_query_vars[ $wpvar ] ) ) {
				$query_vars[ $wpvar ] = $extra_query_vars[ $wpvar ];
			} elseif ( isset( $perma_query_vars[ $wpvar ] ) ) {
				$query_vars[ $wpvar ] = $perma_query_vars[ $wpvar ];
			}
			if ( ! empty( $query_vars[ $wpvar ] ) ) {
				if ( ! is_array( $query_vars[ $wpvar ] ) ) {
					$query_vars[ $wpvar ] = (string) $query_vars[ $wpvar ];
				} else {
					foreach ( $query_vars[ $wpvar ] as $vkey => $v ) {
						if ( is_scalar( $v ) ) {
							$query_vars[ $wpvar ][ $vkey ] = (string) $v;
						}
					}
				}
				if ( isset( $post_type_query_vars[ $wpvar ] ) ) {
					$query_vars['post_type'] = $post_type_query_vars[ $wpvar ];
					$query_vars['name']      = $query_vars[ $wpvar ];
				}
			}
		}

		// Convert urldecoded spaces back into `+`.
		foreach ( get_taxonomies( [], 'objects' ) as $taxonomy => $t ) {
			if ( $t->query_var && isset( $query_vars[ $t->query_var ] ) ) {
				$query_vars[ $t->query_var ] = str_replace( ' ', '+', $query_vars[ $t->query_var ] );
			}
		}

		// Don't allow non-publicly queryable taxonomies to be queried from the front end.
		if ( ! is_admin() ) {
			foreach ( get_taxonomies( [ 'publicly_queryable' => false ], 'objects' ) as $taxonomy => $t ) {
				/*
				 * Disallow when set to the 'taxonomy' query var.
				 * Non-publicly queryable taxonomies cannot register custom query vars. See register_taxonomy().
				 */
				if ( isset( $query_vars['taxonomy'] ) && $taxonomy === $query_vars['taxonomy'] ) {
					unset( $query_vars['taxonomy'], $query_vars['term'] );
				}
			}
		}

		// Limit publicly queried post_types to those that are publicly_queryable
		if ( isset( $query_vars['post_type'] ) ) {
			$queryable_post_types = get_post_types( [ 'publicly_queryable' => true ] );
			if ( ! is_array( $query_vars['post_type'] ) ) {
				if ( ! in_array( $query_vars['post_type'], $queryable_post_types ) ) {
					unset( $query_vars['post_type'] );
				}
			} else {
				$query_vars['post_type'] = array_intersect( $query_vars['post_type'], $queryable_post_types );
			}
		}

		// Resolve conflicts between posts with numeric slugs and date archive queries.
		$query_vars = wp_resolve_numeric_slug_conflicts( $query_vars );

		foreach ( (array) $wp->private_query_vars as $var ) {
			if ( isset( $extra_query_vars[ $var ] ) ) {
				$query_vars[ $var ] = $extra_query_vars[ $var ];
			}
		}

		/*
		 * If we have both the `name` query var and the post type one, then let's remove the `name` one.
		 */
		if ( array_intersect( array_keys( $query_vars ), $this->get_post_types() ) ) {
			unset( $query_vars['name'] );
		}

		if ( ! empty( $url_query_vars ) ) {
			// If the URL did have query vars keep them if not overridden by our resolution.
			$query_vars = array_merge( $url_query_vars, $query_vars );
		}

		/**
		 * Filters the array of parsed query variables after the class logic has been applied to it.
		 *
		 * Due to the costly nature of this operation the results will be cached. The logic, and this filter, will
		 * not run a second time for the same URL in the context of the same request.
		 *
		 * @since 4.9.11
		 *
		 * @param array  $query_vars       The parsed query vars array.
		 * @param array  $extra_query_vars An associative array of extra query vars that will be processed before the
		 *                                 WordPress defined ones.
		 * @param string $url              The URL to parse.
		 */
		$query_vars = apply_filters( 'tribe_rewrite_parse_query_vars', $query_vars, $extra_query_vars, $url );

		if ( $matched_rule ) {
			// Since we're caching let's not cache unmatchec URLs to allow for their later, valid matching.
			$this->parse_request_cache[ $url ] = $query_vars;
		}

		return $query_vars;
	}

	/**
	 * Returns the "clean" version of a URL.
	 *
	 * The URL is first parsed then resolved to a canonical URL.
	 * As an example the URL `/events/list/?post_type=tribe_events` is "dirty" in that the `post_type` query variable
	 * is redundant. The clean version of the URL is `/events/list/`, where the query variable is removed.
	 *
	 * @since 4.9.11
	 *
	 * @param string $url The URL to clean.
	 * @param bool   $force Whether to try and use the cache or force a new URL cleaning run.
	 *
	 * @return string The cleaned URL, or the input URL if it could not be resolved to a clean one.
	 */
	public function get_clean_url( $url, $force = false ) {
		if ( ! $force ) {
			$this->warmup_cache(
				'clean_url',
				WEEK_IN_SECONDS,
				Listener::TRIGGER_GENERATE_REWRITE_RULES
			);
			if ( isset( $this->clean_url_cache[ $url ] ) ) {
				return $this->clean_url_cache[ $url ];
			}
		}

		$parsed_vars = $this->parse_request( $url );

		if ( empty( $parsed_vars ) ) {
			return home_url();
		}

		$clean = $this->get_canonical_url( add_query_arg( $parsed_vars, home_url() ) );

		$this->clean_url_cache[ $url ] = $clean;

		return $clean;
	}
}
