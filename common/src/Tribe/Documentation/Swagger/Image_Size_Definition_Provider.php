<?php

class Tribe__Documentation__Swagger__Image_Size_Definition_Provider
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
		$documentation = [
			'type'       => 'object',
			'properties' => [
				'width'     => [
					'type'        => 'integer',
					'description' => __( 'The image width in pixels in the specified size', 'tribe-common' ),
				],
				'height'    => [
					'type'        => 'integer',
					'description' => __( 'The image height in pixels in the specified size', 'tribe-common' ),
				],
				'mime-type' => [
					'type'        => 'string',
					'description' => __( 'The image mime-type', 'tribe-common' ),
				],
				'url'       => [
					'type'        => 'string',
					'format'      => 'uri',
					'description' => __( 'The link to the image in the specified size on the site', 'tribe-common' ),
				],
			],
		];

		/**
		 * Filters the Swagger documentation generated for an image size in the TEC REST API.
		 *
		 * @param array $documentation An associative PHP array in the format supported by Swagger.
		 *
		 * @link http://swagger.io/
		 */
		$documentation = apply_filters( 'tribe_rest_swagger_image_size_documentation', $documentation );

		return $documentation;
	}
}
