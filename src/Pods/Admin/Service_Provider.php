<?php

namespace Pods\Admin;

use Pods\Admin\Config\Pod;
use Pods\Admin\Config\Group;
use Pods\Admin\Config\Field;

/**
 * Class Service_Provider.
 *
 * @since 2.8.0
 */
class Service_Provider extends \Pods\Service_Provider_Base {

	/**
	 * Registers the classes and functionality needed for Admin configs.
	 *
	 * @since 2.8.0
	 */
	public function register() {
		$this->container->singleton( Pod::class, Pod::class );
		$this->container->singleton( Group::class, Group::class );
		$this->container->singleton( Field::class, Field::class );
		$this->container->singleton( Settings::class, Settings::class );

		$this->hooks();
	}

	/**
	 * Hooks all the methods and actions the class needs.
	 *
	 * @since 2.8.0
	 */
	protected function hooks() {
		add_action( 'pods_admin_settings_init', $this->container->callback( Settings::class, 'hook' ) );
	}
}
