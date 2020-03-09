<?php

class Tribe__Editor__Provider extends tad_DI52_ServiceProvider {

	/**
	 * Binds and sets up implementations.
	 *
	 * @since 4.8
	 *
	 */
	public function register() {
		// Setup to check if gutenberg is active
		$this->container->singleton( 'editor', 'Tribe__Editor' );
		$this->container->singleton( 'editor.utils', 'Tribe__Editor__Utils' );
		$this->container->singleton( 'common.editor.configuration', 'Tribe__Editor__Configuration' );

		if ( ! tribe( 'editor' )->should_load_blocks() ) {
			return;
		}

		$this->container->singleton( 'editor.assets', 'Tribe__Editor__Assets', array( 'hook' ) );

		$this->hook();

		// Initialize the correct Singletons
		tribe( 'editor.assets' );
	}

	/**
	 * Any hooking any class needs happen here.
	 *
	 * In place of delegating the hooking responsibility to the single classes they are all hooked here.
	 *
	 * @since 4.8
	 *
	 */
	protected function hook() {
		// Setup the registration of Blocks
		add_action( 'init', array( $this, 'register_blocks' ), 20 );
	}

	/**
	 * Prevents us from using `init` to register our own blocks, allows us to move
	 * it when the proper place shows up
	 *
	 * @since 4.8.2
	 *
	 * @return void
	 */
	public function register_blocks() {
		/**
		 * Internal Action used to register blocks for Events
		 *
		 * @since 4.8.2
		 */
		do_action( 'tribe_editor_register_blocks' );
	}

	/**
	 * Binds and sets up implementations at boot time.
	 *
	 * @since 4.8
	 */
	public function boot() {
		// no ops
	}
}
