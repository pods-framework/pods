<?php

/**
 * Class Tribe__Duplicate__Strategy_Factory
 *
 * Provides built and ready to use strategies to find duplicates.
 *
 * @since 4.6
 */
class Tribe__Duplicate__Strategy_Factory {
	protected $strategy_map = array();

	public function __construct() {
		$strategy_map = array(
			'default' => 'Tribe__Duplicate__Strategy__Same',
			'same'    => 'Tribe__Duplicate__Strategy__Same',
			'like'    => 'Tribe__Duplicate__Strategy__Like',
		);

		/**
		 * Filters the strategies managed by the strategy factory.
		 *
		 * If a 'default' slug is not provided the first strategy class in the map will be used as default.
		 *
		 * @param array                              $strategy_map An array that maps strategy slugs to strategy classes.
		 * @param Tribe__Duplicate__Strategy_Factory $this         This factory object.
		 *
		 * @since 4.6
		 */
		$this->strategy_map = apply_filters( 'tribe_duplicate_post_strategies', $strategy_map, $this );
	}

	/**
	 * Builds a strategy provided a strategy slug.
	 *
	 * @param string $strategy The slug for the strategy that should be built.
	 *
	 * @return Tribe__Duplicate__Strategy__Interface|bool A built strategy or `false` if the strategy could not be built.
	 *
	 * @since 4.6
	 */
	public function make( $strategy ) {
		/**
		 * Filters the strategy built by the factory.
		 *
		 * Returning a non `null` value here will override the factory operations.
		 *
		 * @param Tribe__Duplicate__Strategy__Interface $built_strategy The strategy that should be built
		 *                                                              for the slug.
		 * @param string                                $strategy       The requested strategy slug.
		 * @param Tribe__Duplicate__Strategy_Factory    $this           This factory object.
		 *
		 * @since 4.6
		 */
		$built_strategy = apply_filters( 'tribe_duplicate_post_strategy', null, $strategy, $this );

		/**
		 * Filters the strategy built by the factory for a specific strategy.
		 *
		 * Returning a non `null` value here will override the factory operations.
		 *
		 * @param Tribe__Duplicate__Strategy__Interface $built_strategy The strategy that should be built
		 *                                                              for the slug.
		 * @param Tribe__Duplicate__Strategy_Factory    $this           This factory object.
		 *
		 * @since 4.6
		 */
		$built_strategy = apply_filters( "tribe_duplicate_post_{$strategy}_strategy", $built_strategy, $this );

		if ( null !== $built_strategy ) {
			return $built_strategy;
		}

		if ( isset( $this->strategy_map[ $strategy ] ) ) {
			$strategy_class = $this->strategy_map[ $strategy ];
		} else {
			$strategy_class = ! empty( $this->strategy_map['default'] )
				? $this->strategy_map['default']
				: reset( $this->strategy_map );
		}

		return class_exists( $strategy_class )
			? new $strategy_class
			: false;
	}

	/**
	 * Gets the unfiltered slug to strategy class map used by the factory.
	 *
	 * @return array
	 *
	 * @since 4.6
	 */
	public function get_strategy_map() {
		return $this->strategy_map;
	}

	/**
	 * Sets the unfiltered slug to strategy class map used by the factory.
	 *
	 * @param array $strategy_map
	 *
	 * @since 4.6
	 */
	public function set_strategy_map( array $strategy_map ) {
		$this->strategy_map = $strategy_map;
	}
}
