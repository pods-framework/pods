<?php
namespace Tribe\Service_Providers;

use \Tribe\Onboarding\Main as Onboarding_Main;

/**
 * Class Onboarding
 *
 * @since 4.14.9
 *
 * Handles the registration and creation of our async process handlers.
 */
class Onboarding extends \tad_DI52_ServiceProvider {

	/**
	 * The Onboarding assets group identifier.
	 *
	 * @var string
	 */
	public static $group_key = 'tribe-onboarding';

	/**
	 * Binds and sets up implementations.
	 *
	 * @since 4.14.9
	 */
	public function register() {
		if ( ! $this->is_enabled() ) {
			return;
		}

		$this->container->singleton( Onboarding_Main::class, Onboarding_Main::class );
		$this->container->singleton( static::class, static::class );

		$this->hooks();
	}

	/**
	 * Set up hooks for classes.
	 *
	 * @since 4.14.9
	 */
	protected function hooks() {
		add_action( 'tribe_common_loaded', [ $this, 'register_assets' ] );

		add_action( 'admin_enqueue_scripts', tribe_callback( Onboarding_Main::class, 'localize_tour' ) );
		add_action( 'admin_enqueue_scripts', tribe_callback( Onboarding_Main::class, 'localize_hints' ) );
	}

	/**
	 * Register assets associated with onboarding.
	 *
	 * @since 4.14.9
	 */
	public function register_assets() {
		$main = \Tribe__Main::instance();

		tribe_asset(
			$main,
			'tec-intro-js',
			'node_modules/intro.js/intro.js',
			[],
			[ 'admin_enqueue_scripts' ],
			[
				'groups'       => self::$group_key,
				'conditionals' => [ $this, 'should_enqueue_assets' ],
			]
		);

		tribe_asset(
			$main,
			'tec-intro-styles',
			'node_modules/intro.js/introjs.css',
			[],
			[ 'admin_enqueue_scripts' ],
			[
				'groups'       => self::$group_key,
				'conditionals' => [ $this, 'should_enqueue_assets' ],
			]
		);

		tribe_asset(
			$main,
			'tec-onboarding-styles',
			'onboarding.css',
			[ 'tec-intro-styles', 'tec-variables-skeleton', 'tec-variables-full' ],
			[ 'admin_enqueue_scripts' ],
			[
				'groups'       => self::$group_key,
				'conditionals' => [ $this, 'should_enqueue_assets' ],
			]
		);

		tribe_asset(
			$main,
			'tec-onboarding-js',
			'onboarding.js',
			[
				'tribe-common',
				'tec-intro-js',
			],
			[ 'admin_enqueue_scripts' ],
			[
				'groups'       => self::$group_key,
				'in_footer'    => false,
				'localize'     => [
					'name' => 'TribeOnboarding',
					'data' => [
						'hintButtonLabel' => __( 'Got it', 'tribe-common' ),
					],
				],
				'conditionals' => [ $this, 'should_enqueue_assets' ],
			]
		);
	}

	/**
	 * Define if the assets for `Onboarding` should be enqueued or not.
	 *
	 * @since 4.14.9
	 *
	 * @return bool If the Onboarding assets should be enqueued or not.
	 */
	public function should_enqueue_assets() {
		return $this->is_enabled();
	}

	/**
	 * Check if the onboarding is enabled or not.
	 *
	 * @since 4.14.9
	 *
	 * @return bool
	 */
	public function is_enabled() {
		/**
		 * Filter to disable tribe onboarding
		 *
		 * @since 4.14.9
		 *
		 * @param bool $disabled If we want to disable the on boarding.
		 */
		$is_enabled = (bool) apply_filters( 'tec_onboarding_enabled', false );

		return $is_enabled && is_admin();
	}
}
