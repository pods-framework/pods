<?php

class Tribe__Tickets__REST__V1__Endpoints__Single_Ticket
	extends Tribe__Tickets__REST__V1__Endpoints__Base
	implements Tribe__REST__Endpoints__READ_Endpoint_Interface,
	Tribe__Documentation__Swagger__Provider_Interface {

	/**
	 * {@inheritdoc}
	 */
	public function get_documentation() {
		$GET_defaults = array( 'in' => 'query', 'default' => '', 'type' => 'string' );

		return array(
			'get' => array(
				'summary'    => __( 'Returns a single ticket data', 'pods' ),
				'parameters' => $this->swaggerize_args( $this->READ_args(), $GET_defaults ),
				'responses'  => array(
					'200' => array(
						'description' => __( 'Returns the data of the ticket with the specified post ID', 'ticket-tickets' ),
						'content'     => array(
							'application/json' => array(
								'schema' => array(
									'$ref' => '#/components/schemas/Ticket',
								),
							),
						),
					),
					'400' => array(
						'description' => __( 'The ticket post ID is invalid.', 'ticket-tickets' ),
						'content'     => array(
							'application/json' => array(
								'schema' => array(
									'type' => 'object',
								),
							),
						),
					),
					'401' => array(
						'description' => __( 'The ticket with the specified ID is not accessible.', 'ticket-tickets' ),
						'content'     => array(
							'application/json' => array(
								'schema' => array(
									'type' => 'object',
								),
							),
						),
					),
					'404' => array(
						'description' => __( 'A ticket with the specified ID does not exist.', 'ticket-tickets' ),
						'content'     => array(
							'application/json' => array(
								'schema' => array(
									'type' => 'object',
								),
							),
						),
					),
				),
			),
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function READ_args() {
		return array(
			'id' => array(
				'type'              => 'integer',
				'in'                => 'path',
				'description'       => __( 'The ticket post ID', 'pods' ),
				'required'          => true,
				/**
				 * Here we check for a positive int, not a ticket ID to properly
				 * return 404 for missing post in place of 400.
				 */
				'validate_callback' => array( $this->validator, 'is_positive_int' ),
			),
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get( WP_REST_Request $request ) {
		$ticket_id = $request['id'];

		$ticket_data = $this->get_readable_ticket_data( $ticket_id );

		if ( $ticket_data instanceof WP_Error ) {
			return $ticket_data;
		}

		/**
		 * Filters the data that will be returned for a single ticket request.
		 *
		 * @since 2.8
		 *
		 * @param array           $data    The ticket data.
		 * @param WP_REST_Request $request The original request.
		 */
		$data = apply_filters( 'tribe_rest_single_ticket_data', $ticket_data, $request );

		return $data;
	}
}
