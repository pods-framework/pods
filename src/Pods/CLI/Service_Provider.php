<?php

namespace Pods\CLI;

use Pods\CLI\Commands\Field;
use Pods\CLI\Commands\Group;
use Pods\CLI\Commands\Pod;

/**
 * Class Service_Provider
 *
 * Add CLI commands and objects.
 *
 * @since 2.8.0
 */
class Service_Provider extends \tad_DI52_ServiceProvider {

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
		tribe( 'pods.cli.commands.pods.pod' );
		tribe( 'pods.cli.commands.pods.group' );
		tribe( 'pods.cli.commands.pods.field' );
	}
}
