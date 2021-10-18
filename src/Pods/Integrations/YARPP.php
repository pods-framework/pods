<?php

namespace Pods\Integrations;

/**
 * Class YARPP
 *
 * @since 2.8.0
 */
class YARPP {

	/**
	 * Add additional supports options for post types.
	 *
	 * @since 2.8.0
	 *
	 * @param array $supports List of supports options for post types.
	 *
	 * @return array List of supports options for post types.
	 */
	public function add_post_type_supports( array $supports ) {
		if ( ! defined( 'YARPP_VERSION' ) ) {
			return $supports;
		}

		$supports['supports_yarpp_support'] = [
			'name'  => 'supports_yarpp_support',
			'label' => __( 'YARPP Support', 'pods' ),
			'type'  => 'boolean',
		];

		return $supports;
	}
}
