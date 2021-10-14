<?php

namespace Pods\Integrations;

/**
 * Class Genesis
 *
 * @since 2.8.0
 */
class Genesis {

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
		if ( ! function_exists( 'genesis' ) ) {
			return $supports;
		}

		$supports['supports_genesis_seo'] = [
			'name'  => 'supports_genesis_seo',
			'label' => __( 'Genesis: SEO', 'pods' ),
			'type'  => 'boolean',
		];

		$supports['supports_genesis_layouts'] = [
			'name'  => 'supports_genesis_layouts',
			'label' => __( 'Genesis: Layouts', 'pods' ),
			'type'  => 'boolean',
		];

		$supports['supports_genesis_simple_sidebars'] = [
			'name'  => 'supports_genesis_simple_sidebars',
			'label' => __( 'Genesis: Simple Sidebars', 'pods' ),
			'type'  => 'boolean',
		];

		return $supports;
	}
}
