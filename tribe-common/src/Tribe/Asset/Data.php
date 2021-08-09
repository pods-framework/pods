<?php
/**
 * Handles adding script data to the page in cases where localizing a
 * specific script is not suitable.
 *
 * Should generally be accessed via tribe( 'tribe.asset.script-data' )
 * rather than via direct instantiation.
 */
class Tribe__Asset__Data {
	/**
	 * Container for any JS data objects that should be added to the page.
	 *
	 * @var array
	 */
	protected $objects = [];

	/**
	 * Hooks up the method used to actually render the JSON data.
	 */
	public function hook() {
		if ( is_admin() ) {
			add_action( 'admin_footer', [ $this, 'render_json' ] );
			add_action( 'customize_controls_print_footer_scripts', [ $this, 'render_json' ] );
		} else {
			add_action( 'wp_footer', [ $this, 'render_json' ] );
		}
	}

	/**
	 * Adds the provided data to the list of objects that should be available
	 * to other scripts.
	 *
	 * @param string $object_name Object name.
	 * @param array  $data        Object data.
	 */
	public function add( $object_name, $data ) {
		/**
		 * Allow plugins to filter data for a specific object.
		 *
		 * @since 4.8.4
		 *
		 * @param array  $data        Object data.
		 * @param string $object_name Object name.
		 */
		$data = apply_filters( "tribe_asset_data_add_object_{$object_name}", $data, $object_name );

		$this->objects[ $object_name ] = $data;
	}

	/**
	 * Outputs the
	 * @internal
	 */
	public function render_json() {
		if ( empty( $this->objects ) ) {
			return;
		}

		echo '<script> /* <![CDATA[ */';

		foreach ( $this->objects as $object_name => $data ) {
			echo 'var ' . esc_html( $object_name ) . ' = ' . wp_json_encode( $data ) . ';';
		}

		echo '/* ]]> */ </script>';
	}
}
