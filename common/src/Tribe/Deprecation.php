<?php


/**
 * Class Tribe__Deprecation
 *
 * Utilities to deprecate code.
 *
 * @since 4.3
 */
class Tribe__Deprecation {

	/**
	 * @var self
	 */
	protected static $instance;

	/**
	 * An array specifying the tag, version and optional replacements
	 * for deprecated filters.
	 *
	 * Use the format `<new_filter_tag> => array(<version>, <deprecated_filter_tag>)`.
	 * e.g. `'tribe_current' => array ('4.3', 'tribe_deprecated')`
	 *
	 * For performance reasons this array is manually set and **not**
	 * dynamically populated.
	 *
	 * @var array
	 */
	protected $deprecated_filters = array(
		'tribe_cost_regex'            => array( '4.3', 'tribe_events_cost_regex' ),
		'tribe_rewrite_prepared_slug' => array( '4.3', 'tribe_events_rewrite_prepared_slug' ),
	);

	/**
	 * An array specifying the tag, version and optional replacements
	 * for deprecated actions.
	 *
	 * Use the format `<new_action_tag> => array(<version>, <deprecated_action_tag>)`.
	 * e.g. `'tribe_current' => array ('4.3', 'tribe_deprecated')`
	 *
	 * For performance reasons this array is manually set and **not**
	 * dynamically populated.
	 *
	 * @var array
	 */
	protected $deprecated_actions = array(
		'tribe_pre_rewrite' => array( '4.3', 'tribe_events_pre_rewrite' ),
	);

	/**
	 * @return Tribe__Deprecation
	 */
	public static function instance() {
		if ( empty( self::$instance ) ) {
			$instance = new self();

			$instance->deprecate_actions();
			$instance->deprecate_filters();

			self::$instance = $instance;
		}

		return self::$instance;
	}

	/**
	 * Hooks the deprecation notices for actions.
	 *
	 * @internal
	 */
	public function deprecate_actions() {
		foreach ( array_keys( $this->deprecated_actions ) as $new_action_tag ) {
			add_action( $new_action_tag, array( $this, 'deprecated_action_message' ) );
			add_filter(
				$this->deprecated_actions[ $new_action_tag ][1], array( $this, 'deprecated_action_message' )
			);
		}
	}

	/**
	 * Hooks the deprecation notices for filters.
	 *
	 * @internal
	 */
	public function deprecate_filters() {
		foreach ( array_keys( $this->deprecated_filters ) as $new_filter_tag ) {
			add_filter( $new_filter_tag, array( $this, 'deprecated_filter_message' ) );
			add_filter(
				$this->deprecated_filters[ $new_filter_tag ][1], array( $this, 'deprecated_filter_message' )
			);
		}
	}

	/**
	 * Triggers a deprecation notice if there is any callback hooked on a deprecated action.
	 */
	public function deprecated_action_message() {
		$action = current_action();
		if ( isset( $this->deprecated_actions[ $action ] ) ) {
			$deprecated_tag = $this->deprecated_actions[ $action ][1];
		} else {
			$deprecated_tag = $action;
			$action         = $this->get_action_for_deprecated_tag( $action );
		}

		remove_action( $deprecated_tag, array( $this, 'deprecated_action_message' ) );

		if ( doing_action( $deprecated_tag ) || has_filter( $deprecated_tag ) ) {
			_deprecated_function(
				'The ' . $deprecated_tag . ' action', $this->deprecated_actions[ $action ][0], $action
			);
		}

		add_action( $deprecated_tag, array( $this, 'deprecated_action_message' ) );
	}

	/**
	 * Triggers a deprecation notice if there is any callback hooked on a deprecated filter.
	 *
	 * @since 4.5.13 the filtered value is passed through unchanged
	 *
	 * @param mixed $value
	 *
	 * @return mixed
	 */
	public function deprecated_filter_message( $value = null ) {
		$filter = current_filter();
		if ( isset( $this->deprecated_filters[ $filter ] ) ) {
			$deprecated_tag = $this->deprecated_filters[ $filter ][1];
		} else {
			$deprecated_tag = $filter;
			$filter         = $this->get_filter_for_deprecated_tag( $filter );
		}

		remove_filter( $deprecated_tag, array( $this, 'deprecated_filter_message' ) );

		if ( has_filter( $deprecated_tag ) || doing_filter( $deprecated_tag ) ) {
			$version = Tribe__Utils__Array::get( $this->deprecated_filters, array( $filter, 0 ), null );

			_deprecated_function(
				'The ' . $deprecated_tag . ' filter', $version, $filter
			);
		}

		add_filter( $deprecated_tag, array( $this, 'deprecated_filter_message' ) );

		return $value;
	}

	/**
	 * @param array $deprecated_filters
	 *
	 * @internal
	 */
	public function set_deprecated_filters( $deprecated_filters ) {
		$this->deprecated_filters = $deprecated_filters;
	}

	/**
	 * @param array $deprecated_actions
	 *
	 * @internal
	 */
	public function set_deprecated_actions( $deprecated_actions ) {
		$this->deprecated_actions = $deprecated_actions;
	}

	/**
	 * @param string $deprecated_tag
	 *
	 * @return int|string
	 */
	protected function get_action_for_deprecated_tag( $deprecated_tag ) {
		foreach ( $this->deprecated_actions as $new_tag => $args ) {
			if ( $args[1] === $deprecated_tag ) {
				return $new_tag;
			}
		}
	}

	/**
	 * @param string $deprecated_tag
	 *
	 * @return int|string
	 */
	protected function get_filter_for_deprecated_tag( $deprecated_tag ) {
		foreach ( $this->deprecated_filters as $new_tag => $args ) {
			if ( $args[1] === $deprecated_tag ) {
				return $new_tag;
			}
		}
	}
}
