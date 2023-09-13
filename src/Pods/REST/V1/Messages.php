<?php

namespace Pods\REST\V1;

use Pods\REST\Interfaces\Messages_Interface;

/**
 * Class Messages
 *
 * @since 2.8.0
 */
class Messages implements Messages_Interface {

	/**
	 * @var string
	 */
	protected $message_prefix = 'rest-v1:';

	/**
	 * @var array
	 */
	protected $messages = [];

	/**
	 * Messages constructor.
	 *
	 * @since 2.8.0
	 */
	public function __construct() {
		$this->messages = [
			// @todo Fill this out.
		];
	}

	/**
	 * Returns the localized message associated with the slug.
	 *
	 * @since 2.8.0
	 *
	 * @param string $message_slug
	 *
	 * @return string
	 */
	public function get_message( $message_slug ) {
		if ( isset( $this->messages[ $message_slug ] ) ) {
			return $this->messages[ $message_slug ];
		}

		return '';
	}

	/**
	 * Returns the associative array of all the messages handled by the class.
	 *
	 * @since 2.8.0
	 *
	 * @return array An associative array in the `[ <slug> => <localized message> ]` format.
	 */
	public function get_messages() {
		return $this->messages;
	}

	/**
	 * Prefixes a message slug with a common root.
	 *
	 * @since 2.8.0
	 *
	 * @param string $message_slug
	 *
	 * @return string The prefixed message slug.
	 */
	public function prefix_message_slug( $message_slug ) {
		return $this->message_prefix . $message_slug;
	}

}
