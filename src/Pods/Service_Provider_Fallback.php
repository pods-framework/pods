<?php

namespace Pods;

/**
 * The service provider logic.
 *
 * @since 2.9.18
 */
abstract class Service_Provider_Fallback {
	/**
	 * Determine whether the service provider will be a deferred one or not.
	 *
	 * @return bool Whether the service provider will be a deferred one or not.
	 * @since 2.9.18
	 *
	 */
	public function isDeferred() {
		return false;
	}

	/**
	 * Get the classes or interfaces bound and provided by the service provider.
	 *
	 * @return array The classes or interfaces bound and provided by the service provider.
	 * @since 2.9.18
	 *
	 */
	public function provides() {
		return [];
	}

	/**
	 * Bind and set up implementations at boot time.
	 *
	 * @since 2.9.18
	 */
	public function boot() {
		// Nothing to do here.
	}
}
