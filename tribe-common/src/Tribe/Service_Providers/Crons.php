<?php
/**
 * Handles common cron functions.
 *
 * @since   4.12.6
 *
 * @package Tribe\Service_Providers
 */

namespace Tribe\Service_Providers;

use Tribe\DB_Lock;

/**
 * Class Crons
 *
 * @since   4.12.6
 *
 * @package Tribe\Service_Providers
 */
class Crons extends \tad_DI52_ServiceProvider {

	/**
	 * Registers the filters required by the provider to manage cron processes.
	 *
	 * @since 4.12.6
	 */
	public function register() {
		// Schedule a cron event happening once a day.
		if ( ! wp_get_schedule( 'tribe_daily_cron' ) ) {
			wp_schedule_event( time(), 'daily', 'tribe_daily_cron' );
		}

		// Register actions that should happen on that hook.
		add_action( 'tribe_daily_cron', [ DB_Lock::class, 'prune_stale_db_locks' ] );
	}
}
