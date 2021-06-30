<?php

/**
 * Interface Tribe__Repository__Update_Interface
 *
 * @since 4.7.19
 */
interface Tribe__Repository__Update_Interface extends Tribe__Repository__Setter_Interface {

	/**
	 * Commits the updates to the selected post IDs to the database.
	 *
	 * @since 4.7.19
	 *
	 * @param bool $return_promise Whether to return a promise object or just the ids
	 *                             of the updated posts; if `true` then a promise will
	 *                             be returned whether the update is happening in background
	 *                             or not.
	 *
	 * @return array|Tribe__Promise A list of the post IDs that have been (synchronous) or will
	 *                              be (asynchronous) updated if `$return_promise` is set to `false`;
	 *                              the Promise object if `$return_promise` is set to `true`.
	 */
	public function save( $return_promise = false );

	/**
	 * Adds an alias for an update/save field.
	 *
	 * @since 4.9.5
	 *
	 * @param string $alias The alias to add.
	 * @param string $field_name The field name this alias should resolve to, this
	 *                           can be posts table field, a taxonomy name or a custom
	 *                           field.
	 */
	public function add_update_field_alias( $alias, $field_name );

	/**
	 * Returns the update fields aliases for the repository.
	 *
	 * @since 4.9.5
	 *
	 * @return array This repository update fields aliases map.
	 */
	public function get_update_fields_aliases();

	/**
	 * Replaces the update fields aliases for this repository.
	 *
	 * @since 4.9.5
	 *
	 * @param array $update_fields_aliases The new update fields aliases
	 *                                     map for this repository.
	 */
	public function set_update_fields_aliases( array $update_fields_aliases );

	/**
	 * Filters the post array before updates.
	 *
	 * Extending classes that need to perform some logic checks during updates
	 * should extend this method.
	 *
	 * @since 4.9.5
	 *
	 * @param array    $postarr The post array that will be sent to the update callback.
	 * @param int|null $post_id The ID  of the post that will be updated.
	 *
	 * @return array|false The filtered post array or `false` to indicate the
	 *                     update should not happen.
	 */
	public function filter_postarr_for_update( array $postarr, $post_id );

	/**
	 * Creates a post of the type managed by the repository with the fields
	 * provided using the `set` or `set_args` methods.
	 *
	 * @since 4.9.5
	 *
	 * @return WP_Post|false The created post object or `false` if the creation
	 *                       fails for logic or runtime issues.
	 */
	public function create();

	/**
	 * Builds the post array that should be used to update or create a post of
	 * the type managed by the repository.
	 *
	 * @since 4.9.5
	 *
	 * @param int|null $id The post ID that's being updated or `null` to get the
	 *                     post array for a new post.
	 *
	 * @return array The post array ready to be passed to the `wp_update_post` or
	 *               `wp_insert_post` functions.
	 *
	 * @throws Tribe__Repository__Usage_Error If running an update and trying to update
	 *                                        a blocked field.
	 */
	public function build_postarr( $id = null );

	/**
	 * Filters the post array before creation.
	 *
	 * Extending classes that need to perform some logic checks during creations
	 * should extend this method.
	 *
	 * @since 4.9.5
	 *
	 * @param array $postarr The post array that will be sent to the creation callback.
	 *
	 * @return array|false The filtered post array or false to indicate creation should not
	 *                     proceed.
	 */
	public function filter_postarr_for_create( array $postarr );

	/**
	 * Sets the create args the repository will use to create posts.
	 *
	 * @since 4.9.5
	 *
	 * @param array $create_args The create args the repository will use to create posts.
	 */
	public function set_create_args( array $create_args );

	/**
	 * Returns the create args the repository will use to create posts.
	 *
	 * @since 4.9.5
	 *
	 * @return array The create args the repository will use to create posts.
	 */
	public function get_create_args();
}
