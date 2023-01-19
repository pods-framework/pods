<?php
/**
 * Handles admin notice functions.
 *
 * @since   4.14.2
 *
 * @package Tribe\Admin\Notice;
 */

namespace Tribe\Admin\Notice;

/**
 * Class Notice
 *
 * @since 4.14.2
 *
 * @package Tribe\Admin\Notice
 */
class Service_Provider extends \tad_DI52_ServiceProvider {

	/**
	 * Registers the objects and filters required by the provider to manage admin notices.
	 *
	 * @since 4.14.2
	 */
	public function register() {
		tribe_singleton( 'pue.notices', 'Tribe__PUE__Notices' );
		tribe_singleton( WP_Version::class, WP_Version::class, [ 'hook' ] );
		tribe_singleton( 'admin.notice.php.version', 'Tribe__Admin__Notice__Php_Version', [ 'hook' ] );
		tribe_singleton( Marketing\Stellar_Sale::class, Marketing\Stellar_Sale::class, [ 'hook' ] );

		$this->hooks();
	}

	/**
	 * Set up hooks for classes.
	 *
	 * @since 4.14.2
	 */
	private function hooks() {
		add_action( 'tribe_plugins_loaded', [ $this, 'plugins_loaded'] );
	}

	/**
	 * Setup for things that require plugins loaded first.
	 *
	 * @since 4.14.2
	 */
	public function plugins_loaded() {
		tribe( 'pue.notices' );
		tribe( 'admin.notice.php.version' );
		tribe( WP_Version::class );

		if ( defined( 'TRIBE_HIDE_MARKETING_NOTICES' ) ) {
			return;
		}

		tribe( Marketing\Stellar_Sale::class );
		tribe( Marketing\Black_Friday::class );
        // EOY Sale disabled for 2022
		// tribe( Marketing\End_Of_Year_Sale::class );
	}
}
