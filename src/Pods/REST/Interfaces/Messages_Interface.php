<?php

namespace Pods\REST\Interfaces;

/**
 * Messages interface.
 *
 * @credit The Events Calendar team - https://github.com/the-events-calendar/tribe-common
 *
 * @since 3.0
 */
interface Messages_Interface {
	/**
	 * Returns the localized message associated with the slug.
	 *
	 * @since 3.0
	 *
	 * @param string $message_slug
	 *
	 * @return string
	 */
	public function get_message( $message_slug );

	/**
	 * Returns the associative array of all the messages handled by the class.
	 *
	 * @since 3.0
	 *
	 * @return array An associative array in the `[ <slug> => <localized message> ]` format.
	 */
	public function get_messages();

	/**
	 * Prefixes a message slug with a common root.
	 *
	 * @since 3.0
	 *
	 * @param string $message_slug
	 *
	 * @return string The prefixed message slug.
	 */
	public function prefix_message_slug( $message_slug );
}
