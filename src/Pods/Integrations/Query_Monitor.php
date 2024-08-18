<?php

namespace Pods\Integrations;

use Pods\Integration;
use Pods\Integrations\Query_Monitor\Collectors;
use Pods\Integrations\Query_Monitor\Outputters;
use QM_Collectors;

/**
 * Class Query_Monitor
 *
 * @since TBD
 */
class Query_Monitor extends Integration {

	/**
	 * @inheritdoc
	 */
	protected $hooks = [
		'action' => [
			'pods_debug_data' => [
				[ Collectors\Debug::class, 'track_debug_data' ],
				10,
				4,
			],
		],
		'filter' => [
			'pods_is_debug_logging_enabled' => [
				'__return_true',
			],
			'qm/outputter/html' => [
				[ __CLASS__, 'register_outputters' ],
			],
			'query_monitor_conditionals' => [
				[ __CLASS__, 'filter_query_monitor_conditionals' ],
			],
		],
	];

	/**
	 * @inheritDoc
	 */
	public function post_hook() {
		QM_Collectors::add( new Collectors\Constants() );
		QM_Collectors::add( new Collectors\Debug() );
	}

	/**
	 * @inheritDoc
	 */
	public static function is_active(): bool {
		return (
			class_exists( 'QM_Activation' )
			&& ( ! defined( 'QM_DISABLED' ) || ! QM_DISABLED )
			&& ( ! defined( 'QMX_DISABLED' ) || ! QMX_DISABLED )
		);
	}

	/**
	 * Register the custom Pods outputters.
	 *
	 * @since TBD
	 *
	 * @param array $outputters The array of outputter instances.
	 *
	 * @return array The updated array of outputters.
	 */
	public static function register_outputters( array $outputters ): array {
		$outputters['pods-constants'] = new Outputters\Constants( QM_Collectors::get( 'pods-constants' ) );
		$outputters['pods-debug']     = new Outputters\Debug( QM_Collectors::get( 'pods-debug' ) );

		return $outputters;
	}

	/**
	 * Add Pods conditional functions to Query Monitor.
	 *
	 * @since TBD
	 *
	 * @param array $conditionals The conditional functions for Query Monitor.
	 *
	 * @return array The updated conditional functions.
	 */
	public static function filter_query_monitor_conditionals( array $conditionals ): array {
		$conditionals[] = 'pods_developer';
		$conditionals[] = 'pods_tableless';
		$conditionals[] = 'pods_light';
		$conditionals[] = 'pods_strict';
		$conditionals[] = 'pods_allow_deprecated';
		$conditionals[] = 'pods_api_cache';
		$conditionals[] = 'pods_shortcode_allow_evaluate_tags';
		$conditionals[] = 'pods_session_auto_start';

		return $conditionals;
	}

}
