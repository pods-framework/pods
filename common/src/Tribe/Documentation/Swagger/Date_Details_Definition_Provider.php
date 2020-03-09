<?php

class Tribe__Documentation__Swagger__Date_Details_Definition_Provider
	implements Tribe__Documentation__Swagger__Provider_Interface {

	/**
	 * Returns an array in the format used by Swagger 2.0.
	 *
	 * While the structure must conform to that used by v2.0 of Swagger the structure can be that of a full document
	 * or that of a document part.
	 * The intelligence lies in the "gatherer" of informations rather than in the single "providers" implementing this
	 * interface.
	 *
	 * @link http://swagger.io/
	 *
	 * @return array An array description of a Swagger supported component.
	 */
	public function get_documentation() {
		$documentation = array(
			'type'       => 'object',
			'properties' => array(
				'year'    => array(
					'type' => 'integer',
					'description' => __( 'The date year', 'tribe-common' ),
				),
				'month' => array(
					'type' => 'integer',
					'description' => __( 'The date month', 'tribe-common' ),
				),
				'day' => array(
					'type' => 'integer',
					'description' => __( 'The date day', 'tribe-common' ),
				),
				'hour' => array(
					'type' => 'integer',
					'description' => __( 'The date hour', 'tribe-common' ),
				),
				'minutes' => array(
					'type' => 'integer',
					'description' => __( 'The date minutes', 'tribe-common' ),
				),
				'seconds' => array(
					'type' => 'integer',
					'description' => __( 'The date seconds', 'tribe-common' ),
				),
			),
		);

		/**
		 * Filters the Swagger documentation generated for an date details in the TEC REST API.
		 *
		 * @param array $documentation An associative PHP array in the format supported by Swagger.
		 *
		 * @link http://swagger.io/
		 */
		$documentation = apply_filters( 'tribe_rest_swagger_date_details_documentation', $documentation );

		return $documentation;
	}
}
