<?php

namespace Pods\Integrations;

// Don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * Class Yoast
 *
 * @since 3.3.10
 */
class Yoast {

	/**
	 * Add additional supports options for post types and taxonomies.
	 *
	 * @since 3.3.10
	 *
	 * @param array $supports List of supports options.
	 *
	 * @return array List of supports options.
	 */
	public function add_post_type_supports( array $supports ) {
		if ( ! defined( 'WPSEO_VERSION' ) ) {
			return $supports;
		}

		$supports['supports_yoast_seo'] = [
			'name'  => 'supports_yoast_seo',
			'label' => __( 'Yoast SEO', 'pods' ),
			'type'  => 'boolean',
		];

		return $supports;
	}
}
