<?php

namespace Pods\Integrations;

use Pods\Integration;

/**
 * Class Enfold
 *
 * @since 2.8.1
 */
class Enfold extends Integration {

	/**
	 * @inheritdoc
	 */
	protected $hooks = [
		'action' => [],
		'filter' => [
			'avf_enqueue_wp_mediaelement' => [ '__return_true' ],
		],
	];

	/**
	 * @inheritDoc
	 */
	public static function is_active() {
		global $avia_config;

		return ! empty( $avia_config );
	}

}
