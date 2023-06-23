<?php

namespace Pods;

if ( class_exists( '\TEC\Common\Contracts\Service_Provider' ) ) {
	class _Service_Provider extends \TEC\Common\Contracts\Service_Provider
	{
		public function register() {
			// TODO: Implement register() method.
		}
	}

} elseif ( class_exists( 'tad_DI52_ServiceProvider' ) ) {

	class _Service_Provider extends tad_DI52_ServiceProvider
	{
		public function register() {
			// TODO: Implement register() method.
		}
	}

} else {

	// @todo error.
}
