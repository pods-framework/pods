<?php

/**
 * Class Tribe__Process__Post_Thumbnail_Setter
 *
 * Handles upload and setting of a post thumbnail in an async process.
 * Example usage:
 *
 *      $post_thumbnail_setter = new Tribe__Process__Post_Thumbnail_Setter();
 *      $post_thumbnail_setter->set_post_id( $post_id );
 *      $post_thumbnail_setter->set_post_thumbnail( 'http://foo.com/random-image.jpg' );
 *      $post_thumbnail_setter->dispatch();
 *
 * @since 4.7.12
 */
class Tribe__Process__Post_Thumbnail_Setter extends Tribe__Process__Handler {
	/**
	 * @var int The ID of the post the post thumbnail should be assigned to.
	 */
	protected $post_id;

	/**
	 * @var int|string Either the ID of an attachment that should be set as the post thumbnail
	 *                 or the full URL, or file path, to it.
	 */
	protected $post_thumbnail;

	/**
	 * {@inheritdoc}
	 */
	public static function action() {
		return 'post_thumbnail_setter';
	}

	/**
	 * {@inheritdoc}
	 */
	public function dispatch() {
		if ( ! isset( $this->post_id, $this->post_thumbnail ) ) {
			// since this is a developer error we are not localizing this error string
			throw new InvalidArgumentException( 'Post ID and featured image should be set before trying to dispatch.' );
		}

		$data = [
			'post_id'        => $this->post_id,
			'post_thumbnail' => trim( $this->post_thumbnail ),
		];

		$this->data( $data );

		do_action( 'tribe_log', 'debug', $this->identifier, $data );

		return parent::dispatch();
	}

	/**
	 * Sets the ID of the post the post thumbnail (aka "featured image") should be attached
	 * and set for.
	 *
	 * @since 4.7.12
	 *
	 * @param int $post_id The target post ID.
	 */
	public function set_post_id( $post_id ) {
		$this->post_id = $post_id;
	}

	/**
	 * Sets the post thumbnail ID or source the process should set.
	 *
	 * @since 4.7.12
	 *
	 * @param int|string $post_thumbnail Either an attachment ID or the full URL, or path, to
	 *                                   the post thumbnail image.
	 */
	public function set_post_thumbnail( $post_thumbnail ) {
		$this->post_thumbnail = $post_thumbnail;
	}

	/**
	 * Handles the post thumbnail setting async process.
	 *
	 * The post thumbnail will be uploaded, if not uploaded already, using the `tribe_upload_image` function.
	 * This method is an alias of the publicly accessible `sync_handle` one.
	 *
	 * @since 4.7.12
	 *
	 * @param array|null $data_source An optional source of data.
	 *
	 * @see   Tribe__Process__Post_Thumbnail_Setter::sync_handle()
	 *
	 * @see   tribe_upload_image()
	 */
	protected function handle( array $data_source = null ) {
		$this->sync_handle( $data_source );
	}

	/**
	 * {@inheritdoc}
	 */
	public function sync_handle( array $data_source = null ) {
		do_action( 'tribe_log', 'debug', $this->identifier, [ 'status' => 'handling request' ] );

		$data_source = isset( $data_source ) ? $data_source : $_POST;

		if ( ! isset( $data_source['post_id'], $data_source['post_thumbnail'] ) ) {
			do_action( 'tribe_log', 'error', $this->identifier, [ 'data' => $data_source, ] );

			return 0;
		}

		$id             = filter_var( $data_source['post_id'], FILTER_SANITIZE_NUMBER_INT );
		$post_thumbnail = filter_var( $data_source['post_thumbnail'], FILTER_SANITIZE_STRING );

		do_action( 'tribe_log', 'debug', $this->identifier, [
			'status'         => 'fetching thumbnail',
			'post_thumbnail' => $post_thumbnail,
			'post_id'        => $id,
		] );

		$thumbnail_id = tribe_upload_image( $post_thumbnail );

		if ( false === $thumbnail_id ) {
			do_action(
				'tribe_log',
				'error',
				$this->identifier,
				[
					'action'         => 'fetch',
					'post_thumbnail' => $post_thumbnail,
					'post_id'        => $id,
					'status'         => 'could not fetch',
				]
			);

			return 0;
		}

		$set = true;
		if ( (int) get_post_thumbnail_id( $id ) !== (int) $thumbnail_id ) {
			$set = set_post_thumbnail( $id, $thumbnail_id );
		}

		if ( false === $set ) {
			do_action(
				'tribe_log',
				'error',
				$this->identifier,
				[
					'action'         => 'set',
					'post_thumbnail' => $post_thumbnail,
					'attachment_id'  => $thumbnail_id,
					'post_id'        => $id,
					'status'         => 'unable to set thumbnail',
				]
			);

			return $thumbnail_id;
		}

		do_action(
			'tribe_log',
			'debug',
			$this->identifier,
			[
				'action'         => 'set',
				'post_thumbnail' => $post_thumbnail,
				'attachment_id'  => $thumbnail_id,
				'post_id'        => $id,
				'status'         => 'completed - attachment created and linked to the post',
			]
		);

		return $thumbnail_id;
	}
}
