<?php

/**
 * @package Pods\Upgrade
 */
class PodsUpgrade_2_1_0 extends PodsUpgrade {

	/**
	 * @var string
	 */
	protected $version = '2.1.0';

	/**
	 *
	 */
	public function __construct() {
	}

	/**
	 * @return array|bool|int|mixed|null|void
	 */
	public function prepare_relationships() {
		$relationship_fields = $this->api->load_fields( array( 'type' => 'pick' ) );

		$count = 0;

		if ( ! empty( $relationship_fields ) ) {
			$count = count( $relationship_fields );
		}

		return $count;
	}

	/**
	 * @return string
	 */
	public function migrate_relationships() {
		if ( true === $this->check_progress( __FUNCTION__ ) ) {
			return '1';
		}

		$migration_limit = (int) apply_filters( 'pods_upgrade_item_limit', 1500 );
		$migration_limit = max( $migration_limit, 100 );

		$last_id = (int) $this->check_progress( __FUNCTION__ );

		$relationship_fields = $this->api->load_fields( array( 'type' => array( 'pick', 'file' ) ) );

		foreach ( $relationship_fields as $field ) {
			$pod = $this->api->load_pod( array( 'pod' => $field['pod'] ) );

			// Only target pods that are meta-enabled
			if ( ! in_array( $pod['type'], array( 'post_type', 'media', 'user', 'comment' ), true ) ) {
				continue;
			}

			// Only target multi-select relationships
			$single_multi = pods_v( $field['type'] . '_format_type', $field['options'], 'single' );

			if ( 'multi' === $single_multi ) {
				$limit = (int) pods_v( $field['type'] . '_limit', $field['options'], 0 );
			} else {
				$limit = 1;
			}

			if ( $limit <= 1 ) {
				continue;
			}

			// Get and loop through relationship meta
			$sql = '

            ';
			// if serialized (or array), save as individual meta items and save new order meta key
		}//end foreach

		$last_id = true;

		$rel = array();

		$this->update_progress( __FUNCTION__, $last_id );

		if ( count( $rel ) === $migration_limit ) {
			return '-2';
		} else {
			return '1';
		}
	}

	/**
	 * @return string
	 */
	public function migrate_cleanup() {
		$this->upgraded();

		$this->api->cache_flush_pods();

		return '1';
	}

	/**
	 *
	 */
	public function restart() {
		$upgraded = get_option( 'pods_framework_upgraded' );

		if ( empty( $upgraded ) || ! is_array( $upgraded ) ) {
			$upgraded = array();
		}

		delete_option( 'pods_framework_upgrade_' . str_replace( '.', '_', $this->version ) );

		if ( in_array( $this->version, $upgraded, true ) ) {
			// @codingStandardsIgnoreLine
			unset( $upgraded[ array_search( $this->version, $upgraded ) ] );
		}

		update_option( 'pods_framework_upgraded', $upgraded );
	}

	/**
	 *
	 */
	public function cleanup() {
		$this->restart();
	}
}
