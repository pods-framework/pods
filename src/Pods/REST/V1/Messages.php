<?php

namespace Pods\REST\V1;

use Tribe__REST__Messages_Interface as REST__Messages_Interface;

/**
 * Class Messages
 *
 * @since 2.8.0
 */
class Messages implements REST__Messages_Interface {

	/**
	 * @var string
	 */
	protected $message_prefix = 'rest-v1:';

	/**
	 * Messages constructor.
	 *
	 * @since 2.8.0
	 */
	public function __construct() {
		$this->messages = [
			'missing-attendee-id'         => __( 'The attendee ID is missing from the request', 'pods' ),
			'attendee-not-found'          => __( 'The requested post ID does not exist or is not an attendee', 'pods' ),
			'attendee-not-accessible'     => __( 'The requested attendee is not accessible', 'pods' ),
			'attendee-check-in-not-found' => __( 'The requested attendee check in is not available', 'pods' ),
			'ticket-not-found'            => __( 'The requested ticket post could not be found', 'pods' ),
			'ticket-provider-not-found'   => __( 'The ticket provider for the requested ticket is not available', 'pods' ),
			'ticket-post-not-found'       => __( 'The post associated with the requested ticket was not found', 'pods' ),
			'ticket-object-not-found'     => __( 'The requested ticket object could not be built or found', 'pods' ),
			'ticket-not-accessible'       => __( 'The requested ticket is not accessible', 'pods' ),
			'error-global-id-generation'  => __( 'The ticket global id could not be generated', 'pods' ),
			// this is an internal error, not same as the `ticket-not-found` one
			'error-ticket-post'           => __( 'There was a problem while fetching the requested ticket post', 'pods' ),
			'error-attendee-post'         => __( 'There was a problem while fetching the requested attendee post', 'pods' ),
			// same as WordPress REST API
			'invalid-page-number'         => __( 'The page number requested is larger than the number of pages available.', 'default' ),
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
