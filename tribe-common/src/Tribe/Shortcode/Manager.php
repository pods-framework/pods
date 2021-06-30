<?php
/**
 * Shortcodes manager for Tribe plugins.
 *
 * @package Tribe\Shortcode
 * @since   4.12.0
 */

namespace Tribe\Shortcode;

/**
 * Class Shortcode Manager.
 *
 * @since  4.12.0
 *
 * @package Tribe\Shortcode
 */
class Manager {

	/**
	 * Current shortcodes.
	 *
	 * @since 4.12.9
	 *
	 * @var array $current_shortcode An array containing the current shortcodes being executed.
	 */
	public $current_shortcode = [];

	/**
	 * Get the list of shortcodes available for handling.
	 *
	 * @since  4.12.0
	 *
	 * @return array An associative array of shortcodes in the shape `[ <slug> => <class> ]`
	 */
	public function get_registered_shortcodes() {
		$shortcodes = [];

		/**
		 * Allow the registering of shortcodes into the our Tribe plugins.
		 *
		 * @since  4.12.0
		 *
		 * @var array An associative array of shortcodes in the shape `[ <slug> => <class> ]`.
		 */
		$shortcodes = apply_filters( 'tribe_shortcodes', $shortcodes );

		return $shortcodes;
	}

	/**
	 * Verifies if a given shortcode slug is registered for handling.
	 *
	 * @since  4.12.0
	 *
	 * @param  string $slug Which slug we are checking if is registered.
	 *
	 * @return bool Whether a shortcode is registered or not.
	 */
	public function is_shortcode_registered( $slug ) {
		$registered_shortcodes = $this->get_registered_shortcodes();
		return isset( $registered_shortcodes[ $slug ] );
	}

	/**
	 * Verifies if a given shortcode class name is registered for handling.
	 *
	 * @since  4.12.0
	 *
	 * @param  string $class_name Which class name we are checking if is registered.
	 *
	 * @return bool Whether a shortcode is registered, by class.
	 */
	public function is_shortcode_registered_by_class( $class_name ) {
		$registered_shortcodes = $this->get_registered_shortcodes();
		return in_array( $class_name, $registered_shortcodes );
	}

	/**
	 * Add new shortcodes handler to catch the correct strings.
	 *
	 * @since  4.12.0
	 */
	public function add_shortcodes() {
		$registered_shortcodes = $this->get_registered_shortcodes();

		// Add to WordPress all of the registered Shortcodes
		foreach ( $registered_shortcodes as $shortcode => $class_name ) {
			add_shortcode( $shortcode, [ $this, 'render_shortcode' ] );
		}
	}

	/**
	 * Makes sure we are correctly handling the Shortcodes we manage.
	 *
	 * @since  4.12.0
	 *
	 * @param array  $arguments Set of arguments passed to the Shortcode at hand.
	 * @param string $content   Contents passed to the shortcode, inside of the open and close brackets.
	 * @param string $shortcode Which shortcode tag are we handling here.
	 *
	 * @return string The rendered shortcode HTML.
	 */
	public function render_shortcode( $arguments, $content, $shortcode ) {
		$registered_shortcodes = $this->get_registered_shortcodes();

		// Bail when we try to handle an unregistered shortcode (shouldn't happen)
		if ( ! $this->is_shortcode_registered( $shortcode ) ) {
			return false;
		}

		/** @var Shortcode_Interface $instance */
		$instance = new $registered_shortcodes[ $shortcode ];
		$instance->setup( $arguments, $content );

		return $instance->get_html();
	}

	/**
	 * Filter `pre_do_shortcode_tag` to add the current shortcode.
	 *
	 * @since 4.12.9
	 *
	 * @param bool|string $return      Short-circuit return value. Either false or the value to replace the shortcode with.
	 * @param string      $tag         Shortcode name.
	 * @param array       $attr        Shortcode attributes array,
	 * @param array       $m           Regular expression match array.
	 *
	 * @return bool|string Short-circuit return value.
	 */
	public function filter_pre_do_shortcode_tag( $return, $tag, $attr, $m ) {
		if ( ! $this->is_shortcode_registered( $tag ) ) {
			return $return;
		}

		// Add to the doing shortcode.
		$this->current_shortcode[] = $tag;

		return $return;
	}

	/**
	 * Filter `do_shortcode_tag` to remove the shortcode from the `$tribe_current_shortcode` list.
	 *
	 * @since 4.12.9
	 *
	 * @param string       $output Shortcode output.
	 * @param string       $tag    Shortcode name.
	 * @param array|string $attr   Shortcode attributes array or empty string.
	 * @param array        $m      Regular expression match array.
	 *
	 * @return string Shortcode output.
	 */
	public function filter_do_shortcode_tag( $output, $tag, $attr, $m ) {
		if ( ! $this->is_shortcode_registered( $tag ) ) {
			return $output;
		}

		if ( isset( $this->current_shortcode[ $tag ] ) ) {
			unset( $this->current_shortcode[ $tag ] );
		}

		return $output;
	}

	/**
	 * Check if a shortcode is being done.
	 *
	 * @since 4.12.9
	 *
	 * @param null|string $tag The shortcode tag name, or null to check if doing any shortcode.
	 *
	 * @return bool If the shortcode is being done or not.
	 */
	public function is_doing_shortcode( $tag = null ) {
		if ( null === $tag ) {
			return ! empty( $this->current_shortcode );
		}

		return in_array( $tag, $this->current_shortcode, true );
	}
}
