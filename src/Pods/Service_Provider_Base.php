<?php

namespace Pods;

use tad_DI52_ServiceProvider as DI52_Service_Provider;
use TEC\Common\Contracts\Service_Provider as TEC_Service_Provider;

if ( class_exists( TEC_Service_Provider::class ) ) {
	/**
	 * The service provider logic.
	 *
	 * @since 2.9.18
	 */
	abstract class Service_Provider_Base extends TEC_Service_Provider {
		// This is just a class that wraps the TEC service provider class.
	}
} elseif ( class_exists( DI52_Service_Provider::class ) ) {
	/**
	 * The service provider logic.
	 *
	 * @since 2.9.18
	 */
	abstract class Service_Provider_Base extends DI52_Service_Provider {
		// This is just a class that wraps the DI52 service provider class.
	}
} else {
	pods_debug_log( 'Unexpected service provider conflict.' );

	/**
	 * The service provider logic.
	 *
	 * This class will still fail because it's not fully compatible with $provider->container->singleton() and other calls.
	 *
	 * @since 2.9.18
	 */
	abstract class Service_Provider_Base extends Service_Provider_Fallback {
		// This is just a class that wraps the fallback service provider class.
	}
}
