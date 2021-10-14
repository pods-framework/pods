<?php

namespace Pods\REST\V1\Documentation;

use Tribe__Documentation__Swagger__Provider_Interface as Provider_Interface;

/**
 * Class Ticket_Definition_Provider
 *
 * @since 2.8.0
 */
class Ticket_Definition_Provider implements Provider_Interface {

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8.0
	 */
	public function get_documentation() {
		$documentation = [
			'type'       => 'object',
			'properties' => [
				'id'                            => [
					'type'        => 'integer',
					'description' => __( 'The ticket WordPress post ID', 'pods' ),
				],
				'post_id'                       => [
					'type'        => 'integer',
					'description' => __( 'The ID of the post the ticket is associated to', 'pods' ),
				],
				'global_id'                     => [
					'type'        => 'string',
					'description' => __( 'The ticket global ID', 'pods' ),
				],
				'global_id_lineage'             => [
					'type'        => 'array',
					'items'       => [
						'type' => 'string',
					],
					'description' => __( 'The ticket global ID lineage', 'pods' ),
				],
				'author'                        => [
					'type'        => 'integer',
					'description' => __( 'The ticket post author ID', 'pods' ),
				],
				'status'                        => [
					'type'        => 'string',
					'description' => __( 'The ticket post status', 'pods' ),
				],
				'date'                          => [
					'type'        => 'string',
					'format'      => 'date',
					'description' => __( 'The ticket creation date', 'pods' ),
				],
				'date_utc'                      => [
					'type'        => 'string',
					'format'      => 'date',
					'description' => __( 'The ticket creation UTC date', 'pods' ),
				],
				'modified'                      => [
					'type'        => 'string',
					'format'      => 'date',
					'description' => __( 'The ticket modification date', 'pods' ),
				],
				'modified_utc'                  => [
					'type'        => 'string',
					'format'      => 'date',
					'description' => __( 'The ticket modification UTC date', 'pods' ),
				],
				'rest_url'                      => [
					'type'        => 'string',
					'format'      => 'uri',
					'description' => __( 'The ticket ET REST API URL', 'pods' ),
				],
				'provider'                      => [
					'type'        => 'string',
					'description' => __( 'The ticket commerce provider', 'pods' ),
					'enum'        => [ 'rsvp', 'tribe-commerce', 'woo', 'edd' ],
				],
				'title'                         => [
					'type'        => 'string',
					'description' => __( 'The ticket title', 'pods' ),
				],
				'description'                   => [
					'type'        => 'string',
					'description' => __( 'The ticket description', 'pods' ),
				],
				'image'                         => [
					'$ref' => '#/components/schemas/Image',
				],
				'available_from'                => [
					'type'        => 'string',
					'format'      => 'date',
					'description' => __( 'The date the ticket will be available', 'pods' ),
				],
				'available_from_details'        => [
					'$ref' => '#/components/schemas/DateDetails',
				],
				'available_until'               => [
					'type'        => 'string',
					'format'      => 'date',
					'description' => __( 'The date the ticket will be available', 'pods' ),
				],
				'available_until_details'       => [
					'$ref' => '#/components/schemas/DateDetails',
				],
				'capacity'                      => [
					'type'        => 'integer',
					'description' => __( 'The formatted ticket current capacity', 'pods' ),
				],
				'capacity_details'              => [
					'$ref' => '#/components/schemas/CapacityDetails',
				],
				'is_available'                  => [
					'type'        => 'boolean',
					'description' => __( 'Whether the ticket is currently available or not due to capacity or date constraints', 'pods' ),
				],
				'cost'                          => [
					'type'        => 'integer',
					'description' => __( 'The formatted cost string', 'pods' ),
				],
				'cost_details'                  => [
					'$ref' => '#/components/schemas/CostDetails',
				],
				'attendees'                     => [
					'type'        => 'array',
					'items'       => [
						'$ref' => '#/components/schemas/Attendee',
					],
					'description' => __( 'A list of attendees for the ticket, ', 'pods' ),
				],
				'supports_attendee_information' => [
					'type'        => 'boolean',
					'description' => __( 'Whether the ticket supports at least one attendee information field, ET+ required', 'pods' ),
				],
				'requires_attendee_information' => [
					'type'        => 'boolean',
					'description' => __( 'Whether the ticket requires at least one attendee information field, ET+ required', 'pods' ),
				],
				'attendee_information_fields'   => [
					'type'        => 'object',
					'description' => __( 'A list of attendee information fields supported/required by the ticket in the format [ <field-slug>: label, required, type, extra ]', 'pods' ),
				],
				'rsvp'                          => [
					'$ref' => '#/components/schemas/RSVPReport',
				],
				'checkin'                       => [
					'$ref' => '#/components/schemas/CheckinReport',
				],
			],
		];

		/**
		 * Filters the Swagger documentation generated in the REST API.
		 *
		 * @since 2.8.0
		 *
		 * @param array $documentation An associative PHP array in the format supported by Swagger.
		 *
		 * @link  http://swagger.io/
		 */
		$documentation = apply_filters( 'pods_rest_swagger_ticket_documentation', $documentation );

		return $documentation;
	}
}
