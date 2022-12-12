<?php

namespace Pods\CLI;

use Pods\CLI\Commands\Field;
use Pods\CLI\Commands\Group;
use Pods\CLI\Commands\Playbook;
use Pods\CLI\Commands\Pod;
use Pods\CLI\Commands\Tools;
use WP_CLI;
use tad_DI52_ServiceProvider;

/**
 * Class Service_Provider
 *
 * Add CLI commands and objects.
 *
 * @since 2.8.0
 */
class Service_Provider extends tad_DI52_ServiceProvider {

	/**
	 * Binds and sets up implementations.
	 */
	public $namespace;

	/**
	 * Registers the classes and functionality needed for CLI.
	 *
	 * @since 2.8.0
	 */
	public function register() {
		$this->container->singleton( 'pods.cli.commands.pods.pod', Pod::class, [ 'hook' ] );
		$this->container->singleton( 'pods.cli.commands.pods.group', Group::class, [ 'hook' ] );
		$this->container->singleton( 'pods.cli.commands.pods.field', Field::class, [ 'hook' ] );

		$this->hooks();
	}

	/**
	 * Hooks all the methods and actions the class needs.
	 *
	 * @since 2.8.0
	 */
	protected function hooks() {
		// Add dynamic commands.
		pods_container( 'pods.cli.commands.pods.pod' );
		pods_container( 'pods.cli.commands.pods.group' );
		pods_container( 'pods.cli.commands.pods.field' );

		// Add static commands.
		if ( defined( 'WP_CLI' ) ) {
			WP_CLI::add_command( 'pods playbook', Playbook::class );
			WP_CLI::add_command( 'pods tools', Tools::class );
		}
	}
}
