<?php

namespace Pods_Performance_Tests\Pods;

use Performance\Config;
use PodsAPI;

class Factory {

	/**
	 * List of Pods IDs created.
	 *
	 * @var array
	 */
	protected $pod_ids = [];

	/**
	 * Counter for the pod/field name.
	 *
	 * @var int
	 */
	protected $counter = 0;

	/**
	 * List of user IDs.
	 *
	 * @var array
	 */
	protected $user_ids = [];

	/**
	 * List of attachment IDs.
	 *
	 * @var array
	 */
	protected $attachment_ids = [];

	/**
	 * List of pod types.
	 *
	 * @var string[]
	 */
	protected $pod_types = [
		'post_type',
		'taxonomy',
		'pod',
	];

	/**
	 * List of field types.
	 *
	 * @var string[]
	 */
	protected $field_types = [
		'text',
		'number',
		'paragraph',
		'pick',
		'file',
	];

	/**
	 * List of pod configs.
	 *
	 * @var array
	 */
	protected $pod_configs = [];

	/**
	 * Pods API object.
	 *
	 * @var PodsAPI
	 */
	protected $api;

	/**
	 * Set up performance suite config.
	 */
	public function setup_performance_suite() {
		Config::setPresenter( 'console' );
		Config::setRunInformation( true );
		Config::setClearScreen( false );

		$this->api = pods_api();
	}

	/**
	 * Set up pod/field configurations.
	 *
	 * @param int $count Total count of pods to create.
	 */
	public function setup_configuration( $count = 1 ) {
		array_map( [ $this, 'create_pod' ], array_fill( 0, $count, 25 ) );
	}

	/**
	 * Set up content.
	 *
	 * @param int $count Total count of items to create for each type.
	 */
	public function setup_content( $count = 25 ) {
		array_map( [ $this, 'create_user' ], array_fill( 0, $count, 'a' ) );
		array_map( [ $this, 'create_attachment' ], array_fill( 0, $count, 'b' ) );
	}

	/**
	 * Create test user.
	 */
	public function create_user() {
		$this->counter++;

		$this->user_ids[] = wp_insert_user( [
			'user_login' => 'perfuser' . $this->counter,
			'user_email' => 'perfuser' . $this->counter . '@test.pods.local',
			'user_pass'  => 'test',
		] );
	}

	/**
	 * Create test attachment.
	 */
	public function create_attachment() {
		$this->counter++;

		$this->attachment_ids[] = wp_insert_attachment( [
			'post_title' => 'perffile' . $this->counter,
		], 'perffile' . $this->counter );
	}

	/**
	 * Create test field for Pod ID.
	 *
	 * @param int $field_count Total fields to create.
	 * @param int $item_count  Total items to create.
	 */
	public function create_pod( $field_count = 25, $item_count = 25 ) {
		$this->counter++;

		$type = $this->pod_types[ array_rand( $this->pod_types ) ];
		$name = 'perfpod' . $this->counter;

		$pod_id = $this->api->save_pod( [
			'type'          => $type,
			'name'          => $name,
			'label'         => 'Performance Pod ' . $this->counter,
			'storage'       => 'meta',
			'create_extend' => 'create',
		] );

		$this->pod_configs[ $pod_id ] = [
			'type'   => $type,
			'name'   => $name,
			'fields' => [],
		];

		if ( 'post_type' === $type ) {
			$this->pod_configs[ $pod_id ]['fields'][] = [
				'type' => 'text',
				'name' => 'post_title',
			];
		} elseif ( 'taxonomy' === $type ) {
			$this->pod_configs[ $pod_id ]['fields'][] = [
				'type' => 'text',
				'name' => 'name',
			];
		} elseif ( 'pod' === $type ) {
			$this->pod_configs[ $pod_id ]['fields'][] = [
				'type' => 'text',
				'name' => 'name',
			];
		}

		$this->pod_ids[] = $pod_id;

		/*array_map( [ $this, 'create_field' ], array_fill( 0, $field_count, $pod_id ) );
		array_map( [ $this, 'create_item' ], array_fill( 0, $item_count, $pod_id ) );*/
	}

	/**
	 * Create test field for Pod ID.
	 *
	 * @param int $pod_id Pod ID.
	 */
	public function create_field( $pod_id ) {
		$this->counter++;

		$type = $this->field_types[ array_rand( $this->field_types ) ];
		$name = 'perffield' . $this->counter;

		$this->api->save_field( [
			'pod_id' => $pod_id,
			'type'   => $type,
			'name'   => $name,
			'label'  => 'Performance Field ' . $this->counter,
		] );

		$this->pod_configs[ $pod_id ]['fields'][] = [
			'type' => $type,
			'name' => $name,
		];
	}

	/**
	 * Create item for pod.
	 *
	 * @param int $pod_id Pod ID.
	 */
	public function create_item( $pod_id ) {
		$config = $this->pod_configs[ $pod_id ];

		$name = $config['name'];

		$pod = pods( $name );

		$data = [];

		foreach ( $config['fields'] as $field ) {
			$data[ $field['name'] ] = $this->generate_content_for_field( $field['type'] );
		}

		$pod->add( $data );
	}

	/**
	 * Generate content for field.
	 *
	 * @param string $type Field type.
	 *
	 * @return float|int|string Generated content for field.
	 */
	public function generate_content_for_field( $type ) {
		$content = wp_generate_password( 12, false );

		if ( 'pick' === $type ) {
			$content = $this->user_ids[ array_rand( $this->user_ids ) ];
		} elseif ( 'file' === $type ) {
			$content = $this->attachment_ids[ array_rand( $this->attachment_ids ) ];
		} elseif ( 'number' === $type ) {
			$content = wp_rand( 1, 50 );
		}

		return $content;
	}

	/**
	 * Destroy configurations and content.
	 */
	public function destroy_configurations() {
		include_once dirname( dirname( dirname( dirname( dirname( dirname( __DIR__ ) ) ) ) ) ) . '/wp-admin/includes/user.php';

		array_map( static function( $user_id ) {
			\wp_delete_user( $user_id );
		}, $this->user_ids );

		array_map( static function( $attachment_id ) {
			\wp_delete_post( $attachment_id, true );
		}, $this->attachment_ids );

		$api = $this->api;

		$pods = $this->api->load_pods();

		foreach ( $pods as $pod ) {
			$api->reset_pod( [ 'id' => $pod['id'] ] );
			$api->delete_pod( [ 'id' => $pod['id'] ] );
		}

		$this->counter        = 0;
		$this->user_ids       = [];
		$this->attachment_ids = [];
		$this->pod_ids        = [];
		$this->pod_configs    = [];
	}
}