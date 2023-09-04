<?php

namespace Pods\REST\Interfaces\Endpoints;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

/**
 * CREATE endpoint interface.
 *
 * @credit The Events Calendar team - https://github.com/the-events-calendar/tribe-common
 *
 * @since 3.0
 */
interface CREATE_Interface {
	/**
	 * Handles POST requests on the endpoint.
	 *
	 * @since 3.0
	 *
	 * @param WP_REST_Request $request
	 * @param bool            $return_id Whether the created post ID should be returned or the full response object.
	 *
	 * @return WP_Error|WP_REST_Response|int An array containing the data on success or a WP_Error instance on failure.
	 */
	public function create( WP_REST_Request $request, $return_id = false );

	/**
	 * Returns the content of the `args` array that should be used to register the endpoint
	 * with the `register_rest_route` function.
	 *
	 * @since 3.0
	 *
	 * @return array
	 */
	public function CREATE_args();

	/**
	 * Whether the current user can create content of the specified type or not.
	 *
	 * @since 3.0
	 *
	 * @return bool Whether the current user can post or not.
	 */
	public function can_create();
}
