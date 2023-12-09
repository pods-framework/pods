<?php

namespace Pods\REST\Interfaces\Endpoints;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

/**
 * DELETE endpoint interface.
 *
 * @credit The Events Calendar team - https://github.com/the-events-calendar/tribe-common
 *
 * @since 3.0
 */
interface DELETE_Interface {
	/**
	 * Handles DELETE requests on the endpoint.
	 *
	 * @since 3.0
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response An array containing the data of the trashed post on
	 *                                   success or a WP_Error instance on failure.
	 */
	public function delete( WP_REST_Request $request );

	/**
	 * Returns the content of the `args` array that should be used to register the endpoint
	 * with the `register_rest_route` function.
	 *
	 * @since 3.0
	 *
	 * @return array
	 */
	public function DELETE_args();

	/**
	 * Whether the current user can delete content of this type or not.
	 *
	 * @since 3.0
	 *
	 * @return bool Whether the current user can delete or not.
	 */
	public function can_delete();
}
