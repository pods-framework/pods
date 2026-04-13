<?php

namespace Pods_Performance_Tests;

use Performance\Performance;
use Pods_Performance_Tests\Pods\Factory;

class Pods {

	protected $factory;

	public function __construct() {
		add_filter( 'pods_error_mode', static function () {
			return 'exception';
		} );
		add_filter( 'pods_api_cache', '__return_false' );

		$this->factory = new Factory();
		$this->factory->setup_performance_suite();
		$this->factory->destroy_configurations();
		$this->factory->setup_content();

		$methods = get_class_methods( get_called_class() );

		foreach ( $methods as $method ) {
			if ( '__construct' === $method ) {
				continue;
			}

			call_user_func( [ $this, $method ] );

			Performance::finish();
		}

		/** @var ExportHandler $results */
		Performance::results();

		$this->factory->destroy_configurations();
	}

	public function setup_configuration() {
		Performance::point( 'Set up configurations' );

		$this->factory->setup_configuration( 100 );
	}

	public function test_pods_construct() {
		Performance::point( 'Test Pods::__construct()' );

		// Run code
		$pod = pods( 'user' );
	}

	public function test_pods_find() {
		Performance::point( 'Test Pods::find()' );

		// Run code
		$pod = pods( 'user' );
		$pod->find( [ 'where' => 'ID = 1' ] );
	}

	public function test_pods_field() {
		Performance::point( 'Test Pods::field()' );

		// Run code
		$pod = pods( 'user', 1 );
		$pod->field( 'display_name' );
	}

	public function test_pods_api_load_pods() {
		Performance::point( 'Test PodsAPI::load_pods()' );

		// Run code
		$api = pods_api();
		$api->load_pods();
	}
}

require_once dirname( dirname( dirname( dirname( dirname( __DIR__ ) ) ) ) ) . '/wp-load.php';
require_once dirname( dirname( __DIR__ ) ) . '/vendor/autoload.php';
require_once __DIR__ . '/_support/Factory.php';

try {
	new Pods;
} catch ( \Exception $exception ) {
	trigger_error( $exception->getMessage() );
}
