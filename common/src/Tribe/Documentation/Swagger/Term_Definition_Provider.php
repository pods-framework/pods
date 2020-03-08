<?php

class Tribe__Documentation__Swagger__Term_Definition_Provider
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
				'id' => array(
					'type' => 'integer',
					'description' => __( 'The WordPress term ID', 'tribe-common' ),
				),
				'name' => array(
					'type' => 'string',
					'description' => __( 'The term name', 'tribe-common' ),
				),
				'slug' => array(
					'type' => 'string',
					'description' => __( 'The term slug', 'tribe-common' ),
				),
				'taxonomy' => array(
					'type' => 'string',
					'description' => __( 'The taxonomy the term belongs to', 'tribe-common' ),
				),
				'description' => array(
					'type' => 'string',
					'description' => __( 'The term description', 'tribe-common' ),
				),
				'parent' => array(
					'type' => 'integer',
					'description' => __( 'The term parent term if any', 'tribe-common' ),
				),
				'count' => array(
					'type' => 'integer',
					'description' => __( 'The number of posts associated with the term', 'tribe-common' ),
				),
				'url' => array(
					'type' => 'string',
					'description' => __( 'The URL to the term archive page', 'tribe-common' ),
				),
				'urls' => array(
					'type' => 'array',
					'items' => array( 'type' => 'string' ),
					'description' => __( 'A list of links to the term own, archive and parent REST URL', 'tribe-common' ),
				),
			),
		);

		/**
		 * Filters the Swagger documentation generated for an term in the TEC REST API.
		 *
		 * @param array $documentation An associative PHP array in the format supported by Swagger.
		 *
		 * @link http://swagger.io/
		 */
		$documentation = apply_filters( 'tribe_rest_swagger_term_documentation', $documentation );

		return $documentation;
	}
}
