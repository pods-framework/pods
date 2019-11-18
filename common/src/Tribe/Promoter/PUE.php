<?php

/**
 * Class Tribe__Promoter__PUE
 *
 * @since 4.9
 */
class Tribe__Promoter__PUE {

	/**
	 * @var string
	 */
	private $slug = 'promoter';

	/**
	 * @var Tribe__PUE__Checker
	 */
	private $pue_checker;

	/**
	 * Setup the PUE Checker.
	 *
	 * @since 4.9
	 */
	public function load() {
		$this->pue_checker = new Tribe__PUE__Checker( 'http://tri.be/', $this->slug, array(
			'context'     => 'service',
			'plugin_name' => __( 'Promoter', 'tribe-common' ),
		) );
	}

	/**
	 * Get whether service has a license and if the license is activated on network.
	 *
	 * @return array|false License information or false if not set.
	 *
	 * @since 4.9
	 */
	public function get_license_info() {
		$option_name = 'pue_install_key_' . $this->slug;

		$key = get_option( $option_name );

		$is_network_key = false;

		if ( is_multisite() ) {
			$network_key = get_network_option( null, $option_name );

			if ( empty( $key ) ) {
				$key = $network_key;

				$is_network_key = true;
			}
		}

		if ( empty( $key ) ) {
			return false;
		}

		return array(
			'key'            => $key,
			'is_network_key' => $is_network_key,
		);
	}

	/**
	 * Check whether service has a license key set or not.
	 *
	 * @return bool Whether service has a license key set.
	 *
	 * @since 4.9
	 */
	public function has_license_key() {
		$license_info = $this->get_license_info();

		return ! empty( $license_info );
	}

	/**
	 * Check whether service has a valid license key or not.
	 *
	 * @return bool Whether service has a valid license key.
	 *
	 * @since 4.9
	 */
	public function has_valid_license() {
		$license_info = $this->get_license_info();

		if ( ! $license_info ) {
			return false;
		}

		$response = $this->pue_checker->validate_key( $license_info['key'], $license_info['is_network_key'] );

		return isset( $response['status'] ) && 1 === (int) $response['status'];
	}

}
