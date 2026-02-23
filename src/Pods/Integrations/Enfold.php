<?php

namespace Pods\Integrations;

// Don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

use Pods\Integration;

/**
 * Class Enfold
 *
 * @since 2.8.1
 */
class Enfold extends Integration {

	protected $hooks = [
		'action' => [],
		'filter' => [
			'avf_enqueue_wp_mediaelement' => [ '__return_true' ],
		],
	];

	public static function is_active() {
		global $avia_config;

		return ! empty( $avia_config );
	}

}
