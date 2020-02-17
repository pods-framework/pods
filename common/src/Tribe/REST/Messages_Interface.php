<?php


interface Tribe__REST__Messages_Interface {

	/**
	 * Returns the localized message associated with the slug.
	 *
	 * @param string $message_slug
	 *
	 * @return string
	 */
	public function get_message( $message_slug  );

	/**
	 * Returns the associative array of all the messages handled by the class.
	 *
	 * @return array An associative array in the `[ <slug> => <localized message> ]` format.
	 */
	public function get_messages();

	/**
	 * Prefixes a message slug with a common root.
	 *
	 * @param string $message_slug
	 *
	 * @return string The prefixed message slug.
	 */
	public function prefix_message_slug( $message_slug );
}
