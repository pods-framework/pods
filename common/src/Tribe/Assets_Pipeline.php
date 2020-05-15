<?php
/**
 * Class used to hook into the assets being loaded
 *
 * @since 4.7.7
 */
class Tribe__Assets_Pipeline {

	/**
	 * Filter to listen when a tag is attached to the HTML.
	 *
	 * @since 4.7.7
	 */
	public function hook() {
		add_filter( 'script_loader_tag', array( $this, 'prevent_underscore_conflict' ), 10, 2 );
	}

	/**
	 * Before underscore is loaded to the FE we add two scripts on before and one after to prevent underscore from
	 * taking place on the global namespace if lodash is present.
	 *
	 * @since 4.7.7
	 *
	 * @param string $tag The <script> tag for the enqueued script.
	 * @param string $handle The script's registered handle.
	 * @return string The <script> tag.
	 */
	public function prevent_underscore_conflict( $tag, $handle ) {
		if ( is_admin() ) {
			return $tag;
		}

		if ( 'underscore' === $handle ) {
			$dir = Tribe__Main::instance()->plugin_url . 'src/resources/js';
			$tag = "<script src='{$dir}/underscore-before.js'></script>\n"
				. $tag
				. "<script src='{$dir}/underscore-after.js'></script>\n";
		}
		return $tag;
	}
}
