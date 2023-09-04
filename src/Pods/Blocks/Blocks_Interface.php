<?php

namespace Pods\Blocks;

/**
 * Blocks interface.
 *
 * @credit The Events Calendar team - https://github.com/the-events-calendar/tribe-common
 *
 * @since 3.0
 */
interface Blocks_Interface {
	/**
	 * Which is the name/slug of this block
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public function slug();

	/**
	 * Which is the name/slug of this block
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public function name();

	/**
	 * What are the default attributes for this block
	 *
	 * @since 3.0
	 *
	 * @return array
	 */
	public function default_attributes();

	/**
	 * Since we are dealing with a Dynamic type of Block we need a PHP method to render it
	 *
	 * @since 3.0
	 *
	 * @param  array $attributes
	 *
	 * @return string
	 */
	public function render( $attributes = [] );

	/**
	 * Does the registration for PHP rendering for the Block, important due to been
	 * an dynamic Block
	 *
	 * @since 3.0
	 *
	 * @return void
	 */
	public function register();

	/**
	 * Used to include any Assets for the Block we are registering
	 *
	 * @since 3.0
	 *
	 * @return void
	 */
	public function assets();

	/**
	 * Attach any specific hook to the current block.
	 *
	 * @since 3.0
	 *
	 * @return mixed
	 */
	public function hook();
}
