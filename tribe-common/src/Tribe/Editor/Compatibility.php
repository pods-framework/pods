<?php

namespace Tribe\Editor;

use Tribe\Editor\Compatibility\Classic_Editor;
use Tribe\Editor\Compatibility\Divi;

/**
 * Editor Compatibility with other plugins and themes.
 *
 * @since 4.14.13
 */
class Compatibility extends \tad_DI52_ServiceProvider {
	public function register() {
		$this->container->singleton( self::class, $this );
		$this->container->singleton( 'editor.compatibility', $this );

		// Conditionally load compatibility for the Classic Editor plugin.
		if ( Classic_Editor::is_classic_plugin_active() ) {
			$this->container->singleton( 'editor.compatibility.classic-editor', Classic_Editor::class );
			tribe( 'editor.compatibility.classic-editor' )->init();
		}

		// Conditionally load compatibility for Divi themes.
		if ( Divi::is_divi_active() ) {
			$this->container->singleton( 'editor.compatibility.divi', Divi::class );
			tribe( 'editor.compatibility.divi' )->init();
		}

	}
}
