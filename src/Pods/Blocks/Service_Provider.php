<?php

namespace Pods\Blocks;

use Pods\Blocks\Types\Field;
use Pods\Blocks\Types\Form;
use Pods\Blocks\Types\Item_List;
use Pods\Blocks\Types\Item_Single;
use Pods\Blocks\Types\View;
use tad_DI52_ServiceProvider;

/**
 * Class Service_Provider
 *
 * Add Blocks integration.
 *
 * @since 2.8
 */
class Service_Provider extends tad_DI52_ServiceProvider {

	/**
	 * Binds and sets up implementations.
	 */
	public $namespace;

	/**
	 * Registers the classes and functionality needed fro REST API
	 *
	 * @since 2.8
	 */
	public function register() {
		tribe_singleton( 'pods.blocks', API::class );
		tribe_singleton( 'pods.blocks.field', Field::class, [ 'register_with_pods' ] );
		tribe_singleton( 'pods.blocks.form', Form::class, [ 'register_with_pods' ] );
		tribe_singleton( 'pods.blocks.list', Item_List::class, [ 'register_with_pods' ] );
		tribe_singleton( 'pods.blocks.single', Item_Single::class, [ 'register_with_pods' ] );
		tribe_singleton( 'pods.blocks.view', View::class, [ 'register_with_pods' ] );

		$this->hooks();
	}

	/**
	 * Hooks all the methods and actions the class needs.
	 *
	 * @since 2.8
	 */
	protected function hooks() {
		add_action( 'pods_setup_content_types', tribe_callback( 'pods.blocks', 'register_blocks' ) );
	}
}
