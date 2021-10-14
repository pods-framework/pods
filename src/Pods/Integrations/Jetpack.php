<?php

namespace Pods\Integrations;

/**
 * Class Jetpack
 *
 * @since 2.8.0
 */
class Jetpack {

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
		if ( ! class_exists( 'Jetpack' ) ) {
			return $supports;
		}

		$supports['supports_jetpack_publicize'] = [
			'name'  => 'supports_jetpack_publicize',
			'label' => __( 'Jetpack Publicize Support', 'pods' ),
			'type'  => 'boolean',
		];

		$supports['supports_jetpack_markdown'] = [
			'name'  => 'supports_jetpack_markdown',
			'label' => __( 'Jetpack Markdown Support', 'pods' ),
			'type'  => 'boolean',
		];

		return $supports;
	}
}
