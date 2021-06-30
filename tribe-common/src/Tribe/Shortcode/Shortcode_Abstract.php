<?php

namespace Tribe\Shortcode;

use Tribe__Utils__Array as Arr;

/**
 * The abstract all shortcodes should implement.
 *
 * @package Tribe\Shortcode
 *
 * @since   4.12.0
 */
abstract class Shortcode_Abstract implements Shortcode_Interface {
	/**
	 * Slug of the current shortcode.
	 *
	 * @since 4.12.0
	 *
	 * @var string
	 */
	protected $slug;

	/**
	 * Default arguments to be merged into final arguments of the shortcode.
	 *
	 * @since 4.12.0
	 *
	 * @var array
	 */
	protected $default_arguments = [];

	/**
	 * Array map allowing aliased shortcode arguments.
	 *
	 * The array keys are aliases of the array values (i.e. the "real" shortcode attributes to parse).
	 * Example array: [ 'alias' => 'canonical', 'from' => 'to', 'that' => 'becomes_this' ]
	 * Example shortcode usage: [some_tag alias=17 to='Fred'] will be parsed as [some_tag canonical=17 to='Fred']
	 *
	 * @since 4.12.2
	 *
	 * @var array<string,string>
	 */
	protected $aliased_arguments = [];

	/**
	 * Array of callbacks for arguments validation.
	 *
	 * @since 4.12.0
	 *
	 * @var array
	 */
	protected $validate_arguments_map = [];

	/**
	 * Arguments of the current shortcode.
	 *
	 * @since 4.12.0
	 *
	 * @var array
	 */
	protected $arguments;

	/**
	 * Content of the current shortcode.
	 *
	 * @since 4.12.0
	 *
	 * @var string
	 */
	protected $content;

	/**
	 * {@inheritDoc}
	 */
	public function setup( $arguments, $content ) {
		$this->arguments = $this->parse_arguments( (array) $arguments );
		$this->content   = $content;
	}

	/**
	 * {@inheritDoc}
	 */
	public function set_aliased_arguments( array $alias_map ) {
		$this->aliased_arguments = Arr::filter_to_flat_scalar_associative_array( (array) $alias_map );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_aliased_arguments() {
		return $this->aliased_arguments;
	}

	/**
	 * {@inheritDoc}
	 */
	public function parse_arguments( array $arguments ) {
		$arguments = Arr::parse_associative_array_alias( (array) $arguments, (array) $this->get_aliased_arguments() );
		$arguments = shortcode_atts( $this->get_default_arguments(), $arguments, $this->slug );

		return $this->validate_arguments( $arguments );
	}

	/**
	 * {@inheritDoc}
	 */
	public function validate_arguments( array $arguments ) {
		$validate_arguments_map = $this->filter_validated_arguments_map( $this->get_validated_arguments_map() );
		foreach ( $validate_arguments_map as $key => $callback ) {
			$arguments[ $key ] = $callback( isset( $arguments[ $key ] ) ? $arguments[ $key ] : null );
		}

		return $arguments;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_registration_slug() {
		return $this->slug;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_validated_arguments_map() {
		return $this->validate_arguments_map;
	}

	/**
	 * {@inheritDoc}
	 */
	public function filter_validated_arguments_map( $validate_arguments_map ) {
		/**
		 * Applies a filter to instance arguments validation callbacks.
		 *
		 * @since 4.12.0
		 *
		 * @param array  $validate_arguments_map Current set of callbacks for arguments.
		 * @param static $instance               Which instance of shortcode we are dealing with.
		 */
		$validate_arguments_map = apply_filters( 'tribe_shortcode_validate_arguments_map', $validate_arguments_map, $this );

		$registration_slug = $this->get_registration_slug();

		/**
		 * Applies a filter to instance arguments validation callbacks based on the registration slug of the shortcode.
		 *
		 * @since 4.12.0
		 *
		 * @param array  $validate_arguments_map Current set of callbacks for arguments.
		 * @param static $instance               Which instance of shortcode we are dealing with.
		 */
		$validate_arguments_map = apply_filters( "tribe__shortcode_{$registration_slug}_validate_arguments_map", $validate_arguments_map, $this );

		return $validate_arguments_map;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_arguments() {
		/**
		 * Applies a filter to instance arguments.
		 *
		 * @since 4.12.0
		 *
		 * @param array  $arguments Current set of arguments.
		 * @param static $instance  Which instance of shortcode we are dealing with.
		 */
		$arguments = apply_filters( 'tribe_shortcode_arguments', $this->arguments, $this );

		$registration_slug = $this->get_registration_slug();

		/**
		 * Applies a filter to instance arguments based on the registration slug of the shortcode.
		 *
		 * @since 4.12.0
		 *
		 * @param array  $arguments Current set of arguments.
		 * @param static $instance  Which instance of shortcode we are dealing with.
		 */
		$arguments = apply_filters( "tribe_shortcode_{$registration_slug}_arguments", $arguments, $this );

		return $arguments;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_argument( $index, $default = null ) {
		$arguments = $this->get_arguments();
		$argument  = Arr::get( $arguments, $index, $default );

		/**
		 * Applies a filter to a specific shortcode argument, catch all for all shortcodes.
		 *
		 * @since 4.12.0
		 *
		 * @param mixed  $argument The argument.
		 * @param array  $index    Which index we indent to fetch from the arguments.
		 * @param array  $default  Default value if it doesn't exist.
		 * @param static $instance Which instance of shortcode we are dealing with.
		 */
		$argument = apply_filters( 'tribe_shortcode_argument', $argument, $index, $default, $this );

		$registration_slug = $this->get_registration_slug();

		/**
		 * Applies a filter to a specific shortcode argument, to a particular registration slug.
		 *
		 * @since 4.12.0
		 *
		 * @param mixed  $argument The argument value.
		 * @param array  $index    Which index we indent to fetch from the arguments.
		 * @param array  $default  Default value if it doesn't exist.
		 * @param static $instance Which instance of shortcode we are dealing with.
		 */
		$argument = apply_filters( "tribe_shortcode_{$registration_slug}_argument", $argument, $index, $default, $this );

		return $argument;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_default_arguments() {
		/**
		 * Applies a filter to instance default arguments.
		 *
		 * @since 4.12.0
		 *
		 * @param array  $default_arguments Current set of default arguments.
		 * @param static $instance          Which instance of shortcode we are dealing with.
		 */
		$default_arguments = apply_filters( 'tribe_shortcode_default_arguments', $this->default_arguments, $this );

		$registration_slug = $this->get_registration_slug();

		/**
		 * Applies a filter to instance default arguments based on the registration slug of the shortcode.
		 *
		 * @since 4.12.0
		 *
		 * @param array  $default_arguments Current set of default arguments.
		 * @param static $instance          Which instance of shortcode we are dealing with.
		 */
		$default_arguments = apply_filters( "tribe_shortcode_{$registration_slug}_default_arguments", $default_arguments, $this );

		return $default_arguments;
	}

}
