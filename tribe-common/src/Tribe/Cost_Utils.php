<?php


/**
 * Class Tribe__Cost_Utils
 *
 * Utility methods to deal with costs.
 *
 * @since 4.3
 */
class Tribe__Cost_Utils {

	/**
	 * @var string
	 */
	protected $_current_original_cost_separator;

	/**
	 * @var string
	 */
	protected $_supported_decimal_separators = '.,';

	/**
	 * Static Singleton Factory Method
	 *
	 * @return Tribe__Events__Cost_Utils
	 */
	public static function instance() {
		static $instance;

		if ( ! $instance ) {
			$instance = new self;
		}

		return $instance;
	}

	/**
	 * Check if a string is a valid cost.
	 *
	 * @param  string $cost String to be checked.
	 *                      Can include decimal and thousands separator.
	 *
	 * @return boolean
	 */
	public function is_valid_cost( $cost, $allow_negative = true ) {
		return preg_match( $this->get_cost_regex(), trim( $cost ) );
	}

	/**
	 * Returns the regular expression that shold be used to  identify a valid
	 * cost string.
	 *
	 * @return string
	 */
	public function get_cost_regex() {
		$separators = '[\\' . implode( '\\', $this->get_separators() ) . ']?';
		$cost_regex = '(' . $separators . '([\d]+)' . $separators . '([\d]*))';

		/**
		 * Filters the regular expression that will be used to identify a valid cost
		 * string.
		 *
		 * @param string $cost_regex
		 *
		 * @deprecated 4.3 Use `tribe_cost_regex` instead
		 */
		$cost_regex = apply_filters(
			'tribe_events_cost_regex', $cost_regex
		);

		/**
		 * Filters the regular expression that will be used to identify a valid cost
		 * string.
		 *
		 * @param string $cost_regex
		 */
		$cost_regex = apply_filters( 'tribe_cost_regex', $cost_regex );

		return $cost_regex;
	}

	/**
	 * Fetch the possible separators
	 *
	 * @return array
	 */
	public function get_separators() {
		$separators = [ ',', '.' ];

		/**
		 * Filters the cost string possible separators, those must be only 1 char.
		 *
		 * @param array $separators Defaults to comma (",") and period (".")
		 */
		return apply_filters( 'tribe_events_cost_separators', $separators );
	}

	/**
	 * If the cost is "0", call it "Free"
	 *
	 * @param int|float|string $cost Cost to analyze
	 *
	 * @return int|float|string
	 */
	public function maybe_replace_cost_with_free( $cost ) {

		$cost_with_period = $this->convert_decimal_separator( $cost );

		if (
			is_numeric( $cost_with_period )
			&& '0.00' === number_format( $cost_with_period, 2, '.', ',' )
		) {
			return esc_html__( 'Free', 'tribe-common' );
		}

		return $cost;
	}

	/**
	 * Formats a cost with a currency symbol
	 *
	 * @param int|float|string $cost              Cost to format
	 *
	 * return string
	 * @param int|WP_Post      $event             An event post ID or post object.
	 * @param string           $currency_symbol
	 * @param string           $currency_position Either "prefix" or "posfix"
	 *
	 * @return float|int|string
	 */
	public function maybe_format_with_currency( $cost, $event = null, $currency_symbol = null, $currency_position = null ) {
		// check if the currency symbol is desired, and it's just a number in the field
		// be sure to account for european formats in decimals, and thousands separators
		if ( is_numeric( str_replace( $this->get_separators(), '', $cost ) ) ) {
			$reverse_position = null;
			// currency_position often gets passed as null or an empty string.
			if ( ! empty( $currency_position ) ) {
				$reverse_position = 'prefix' === $currency_position ? false : true;
			}

			$cost = tribe_format_currency( $cost, $event, $currency_symbol, $reverse_position );
		}

		return $cost;
	}

	/**
	 * @param       string       $original_string_cost A string cost with or without currency symbol,
	 *                                                 e.g. `10 - 20`, `Free` or `2$ - 4$`.
	 * @param       array|string $merging_cost         A single string cost representation to merge or an array of
	 *                                                 string cost representations to merge, e.g. ['Free', 10, 20,
	 *                                                 'Donation'] or `Donation`.
	 * @param       bool         $with_currency_symbol Whether the output should prepend the currency symbol to the
	 *                                                 numeric costs or not.
	 * @param array              $sorted_mins          An array of non numeric price minimums sorted smaller to larger,
	 *                                                 e.g. `['Really free', 'Somewhat free', 'Free with 3 friends']`.
	 * @param array              $sorted_maxs          An array of non numeric price maximums sorted smaller to larger,
	 *                                                 e.g. `['Donation min $10', 'Donation min $20', 'Donation min
	 *                                                 $100']`.
	 *
	 * @return string|array The merged cost range.
	 */
	public function merge_cost_ranges( $original_string_cost, $merging_cost, $with_currency_symbol, $sorted_mins = [], $sorted_maxs = [] ) {
		if ( empty( $merging_cost ) || $original_string_cost === $merging_cost ) {
			return $original_string_cost;
		}

		$_merging_cost              = array_map(
			[ $this, 'convert_decimal_separator' ], (array) $merging_cost
		);
		$_merging_cost              = array_map( [ $this, 'numerize_numbers' ], $_merging_cost );
		$numeric_merging_cost_costs = array_filter( $_merging_cost, 'is_numeric' );

		$matches = [];
		preg_match_all(
			'!\d+(?:([' . preg_quote( $this->_supported_decimal_separators ) . '])\d+)?!', $original_string_cost,
			$matches
		);
		$this->_current_original_cost_separator = empty( $matches[1][0] ) ? '.' : $matches[1][0];
		$matches[0]                             = empty( $matches[0] )
			? $matches[0]
			: array_map(
				[
					$this,
					'convert_decimal_separator',
				],
				$matches[0]
			);

		$numeric_orignal_costs                  = empty( $matches[0] ) ? $matches[0] : array_map(
			'floatval', $matches[0]
		);

		$all_numeric_costs = array_filter( array_merge( $numeric_merging_cost_costs, $numeric_orignal_costs ) );
		$cost_min          = $cost_max = false;

		$merging_mins     = array_intersect( $sorted_mins, (array) $merging_cost );
		$merging_has_min  = array_search( reset( $merging_mins ), $sorted_mins );
		$original_has_min = array_search( $original_string_cost, $sorted_mins );
		$merging_has_min  = false === $merging_has_min ? 999 : $merging_has_min;
		$original_has_min = false === $original_has_min ? 999 : $original_has_min;
		$string_min_key   = min( $merging_has_min, $original_has_min );
		if ( array_key_exists( $string_min_key, $sorted_mins ) ) {
			$cost_min = $sorted_mins[ $string_min_key ];
		} else {
			$cost_min = empty( $all_numeric_costs ) ? '' : min( $all_numeric_costs );
		}

		$merging_maxs     = array_intersect( $sorted_maxs, (array) $merging_cost );
		$merging_has_max  = array_search( end( $merging_maxs ), $sorted_maxs );
		$original_has_max = array_search( $original_string_cost, $sorted_maxs );
		$merging_has_max  = false === $merging_has_max ? - 1 : $merging_has_max;
		$original_has_max = false === $original_has_max ? - 1 : $original_has_max;
		$string_max_key   = max( $merging_has_max, $original_has_max );
		if ( array_key_exists( $string_max_key, $sorted_maxs ) ) {
			$cost_max = $sorted_maxs[ $string_max_key ];
		} else {
			$cost_max = empty( $all_numeric_costs ) ? '' : max( $all_numeric_costs );
		}

		$cost = array_filter( [ $cost_min, $cost_max ] );

		if ( $with_currency_symbol ) {
			$formatted_cost = [];
			foreach ( $cost as $c ) {
				$formatted_cost[] = is_numeric( $c ) ? tribe_format_currency( $c ) : $c;
			}
			$cost = $formatted_cost;
		}

		return empty( $cost ) ? $original_string_cost : array_map(
			[ $this, 'restore_original_decimal_separator' ],
			$cost
		);
	}

	/**
	 * Returns a maximum cost in a list of costs. If an array of costs is not passed in, the array of costs is fetched
	 * via query.
	 *
	 * @param $costs mixed Cost(s) to review for max value
	 *
	 * @return float
	 */
	public function get_maximum_cost( $costs = null ) {
		return $this->get_cost_by_func( $costs, 'max' );
	}

	/**
	 * Returns a particular cost within an array of costs
	 *
	 * @param $costs    mixed Cost(s) to review for max value
	 * @param $function string Function to use to determine which cost to return from range. Valid values: max, min
	 *
	 * @return float
	 */
	protected function get_cost_by_func( $costs = null, $function = 'max' ) {
		if ( null === $costs ) {
			$costs = $this->get_all_costs();
		} else {
			$costs = (array) $costs;
		}

		$costs = $this->parse_cost_range( $costs );

		// if there's only one item, we're looking at a single event. If the cost is non-numeric, let's
		// return the non-numeric cost so that value is preserved
		if ( 1 === count( $costs ) && ! is_numeric( current( $costs ) ) ) {
			return current( $costs );
		}

		// make sure we are only trying to get numeric min/max values
		$costs = array_filter( $costs, 'is_numeric' );

		if ( empty( $costs ) ) {
			return 0;
		}

		switch ( $function ) {
			case 'min':
				$cost = $costs[ min( array_keys( $costs ) ) ];
				break;
			case 'max':
			default:
				$cost = $costs[ max( array_keys( $costs ) ) ];
				break;
		}

		// If there isn't anything on the cost just return 0
		if ( empty( $cost ) ) {
			return 0;
		}

		return $cost;
	}

	/**
	 * Parses a cost into an array of ranges.
	 *
	 * If a range isn't provided, the resulting array will hold a single
	 * value.
	 *
	 * @param string|array $costs        A cost string or an array of cost strings.
	 * @param null         $max_decimals The maximum number of decimal values that should be returned in the range.
	 * @param bool         $sort         Whether the returned values should be sorted.
	 *
	 * @return array An associative array of parsed costs in [ <string cost> => <cost number> ] format.
	 */
	public function parse_cost_range( $costs, $max_decimals = null, $sort = true ) {
		if ( ! is_array( $costs ) && ! is_string( $costs ) ) {
			return [];
		}

		// make sure costs is an array
		$costs = (array) $costs;

		// If there aren't any costs, return a blank array
		if ( 0 === count( $costs ) ) {
			return [];
		}

		// Build the regular expression
		$price_regex = $this->get_cost_regex();
		$max         = 0;

		foreach ( $costs as &$cost ) {
			// Get the required parts
			if ( preg_match_all( '/' . $price_regex . '/', $cost, $matches ) ) {
				$cost = reset( $matches );
			} else {
				$cost = [ $cost ];
				continue;
			}

			// Get the max number of decimals for the range
			if ( count( $matches ) === 4 ) {
				$decimals = max( array_map( 'strlen', end( $matches ) ) );
				$max      = max( $max, $decimals );
			}
		}

		// If we passed max decimals
		if ( ! is_null( $max_decimals ) ) {
			$max = max( $max_decimals, $max );
		}

		$output_costs = [];
		$costs        = call_user_func_array( 'array_merge', array_values( $costs ) );

		foreach ( $costs as $cost ) {
			$numeric_cost = str_replace( $this->get_separators(), '.', $cost );

			if ( is_numeric( $numeric_cost ) ) {
				// Creates a Well Balanced Index that will perform good on a Key Sorting method
				$index = str_replace( [ '.', ',' ], '', number_format( $numeric_cost, $max ) );
			} else {
				// Makes sure that we have "index-safe" string
				$index = sanitize_title( $numeric_cost );
			}

			// Keep the Costs in a organizable array by keys with the "numeric" value
			$output_costs[ $index ] = $cost;
		}

		// Filter keeping the Keys
		if ( $sort ) {
			ksort( $output_costs );
		}

		return (array) $output_costs;
	}

	/**
	 * Returns a minimum cost in a list of costs. If an array of costs is not passed in, the array of costs is fetched
	 * via query.
	 *
	 * @param $costs mixed Cost(s) to review for min value
	 *
	 * @return float
	 */
	public function get_minimum_cost( $costs = null ) {
		return $this->get_cost_by_func( $costs, 'min' );
	}

	/**
	 * Converts the original decimal separator to ".".
	 *
	 * @param string|int $value
	 *
	 * @return string
	 */
	protected function convert_decimal_separator( $value ) {
		return preg_replace( '/[' . preg_quote( $this->_supported_decimal_separators ) . ']/', '.', $value );
	}

	/**
	 * Restores the decimal separator to its original symbol.
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	protected function restore_original_decimal_separator( $value ) {
		return str_replace( '.', $this->_current_original_cost_separator, $value );
	}

	/**
	 * Extracts int and floats from a numeric "dirty" string like strings that might contain other symbols.
	 *
	 * E.g. "$10" will yield "10"; "23.55$" will yield "23.55".
	 *
	 * @param string|int $value
	 *
	 * @return int|float
	 */
	protected function numerize_numbers( $value ) {
		$matches = [];

		$pattern = '/(\\d{1,}([' . $this->_supported_decimal_separators . ']\\d{1,}))/';

		return preg_match( $pattern, $value, $matches ) ? $matches[1] : $value;
	}

	/**
	 * Parses the currency symbol part of a cost string.
	 *
	 * @param string|array $cost A string cost, a comma separated array of string costs or an array of costs.
	 *
	 * @return false|string Either the inferred currency symbol or `false` if the currency symbol is missing or not consistent.
	 */
	public function parse_currency_symbol( $cost ) {
		if ( empty( $cost ) ) {
			return false;
		}

		$original_costs = is_array( $cost ) ? $cost : preg_split( '/\\s*,\\s*/', $cost );
		$costs = $this->parse_cost_range( $original_costs, null, false );

		if ( empty( $costs ) ) {
			return false;
		}

		$currency_symbols = [];
		$i = 0;
		foreach ( $costs as $string => $value ) {
			if ( is_numeric( $string ) ) {
				$currency_symbols[] = trim( str_replace( $value, '', $original_costs[ $i ] ) );
				if ( end( $currency_symbols ) !== reset( $currency_symbols ) ) {
					return false;
				}
			}

			$i ++;
		}

		return ! empty( $currency_symbols ) ? reset( $currency_symbols ) : false;
	}

	/**
	 * Parses the currency symbol position  part of a cost string.
	 *
	 * @param string|array $cost A string cost, a comma separated array of string costs or an array of costs.
	 *
	 * @return false|string Either the inferred currency symbol position or `false` if not present or not consistent.
	 */
	public function parse_currency_position( $cost ) {
		if ( empty( $cost ) ) {
			return false;
		}

		$original_costs = is_array( $cost ) ? $cost : preg_split( '/\\s*,\\s*/', $cost );
		$currency_symbol = $this->parse_currency_symbol( $original_costs );

		if ( empty( $currency_symbol ) ) {
			return false;
		}

		$currency_positions = [];
		foreach ( $original_costs as $original_cost ) {
			$currency_symbol_position = strpos( trim( $original_cost ), $currency_symbol );
			if ( false === $currency_symbol_position ) {
				continue;
			}

			$currency_positions[] = 0 === $currency_symbol_position ? 'prefix' : 'postfix';
			if ( end( $currency_positions ) !== reset( $currency_positions ) ) {
				return false;
			}
		}

		return ! empty( $currency_positions ) ? reset( $currency_positions ) : false;
	}

	/**
	 * Parses the cost value and current locale to infer decimal and thousands separators.
	 *
	 * The cost values stored in the meta table might not use the same decimal and thousands separator as the current
	 * locale.
	 * To work around this we parse the value assuming the decimal separator will be the last non-numeric symbol,
	 * if any.
	 *
	 * @since 4.9.12
	 *
	 * @param string|int|float $value The cost value to parse.
	 *
	 * @return array An array containing the parsed decimal and thousands separator symbols.
	 */
	public function parse_separators( $value ) {
		global $wp_locale;
		$locale_decimal_point = $wp_locale->number_format['decimal_point'];
		$locale_thousands_sep = $wp_locale->number_format['thousands_sep'];
		$decimal_sep          = $locale_decimal_point;
		$thousands_sep        = $locale_thousands_sep;

		preg_match_all( '/[\\.,]+/', $value, $matches );

		if ( ! empty( $matches[0] ) ) {
			$matched_separators = $matches[0];
			if ( count( array_unique( $matched_separators ) ) > 1 ) {
				// We have both, the decimal separator will be the last non-numeric symbol.
				$decimal_sep   = end( $matched_separators );
				$thousands_sep = reset( $matched_separators );
			} else {
				/*
				 * We only have one, we can assume it's the decimal separator if it comes before a number of numeric
				 * symbols that is not exactly 3. If there are exactly 3 number after the symbols we fall back on the
				 * locale; we did our best and cannot guess any further.
				 */
				$frags = explode( end( $matched_separators ), $value );
				if ( strlen( end( $frags ) ) !== 3 ) {
					$decimal_sep   = end( $matched_separators );
					$thousands_sep = $decimal_sep === $locale_decimal_point ?
						$locale_thousands_sep
						: $locale_decimal_point;
				}
			}
		}

		return [ $decimal_sep, $thousands_sep ];
	}
}
