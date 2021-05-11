<?php

interface Tribe__REST__Endpoints__DELETE_Endpoint_Interface {
	/**
	 * Handles DELETE requests on the endpoint.
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
	 * @return array
	 */
	public function DELETE_args();

	/**
	 * Whether the current user can delete content of this type or not.
	 *
	 * @return bool Whether the current user can delete or not.
	 */
	public function can_delete();
}
