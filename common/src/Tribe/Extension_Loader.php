<?php
defined( 'WPINC' ) || die; // Do not load directly.

/**
 * Class Tribe__Extension_Loader
 */
class Tribe__Extension_Loader {

	/**
	 * Plugin header data
	 *
	 * @var array {
	 *      Plugin header data
	 *
	 *      @param array $plugin_basename Plugin header key/value pairs.
	 * }
	 */
	private $plugin_data = array();

	/**
	 * Class instance.
	 *
	 * @var Tribe__Extension_Loader The singleton instance.
	 */
	private static $instance;

	/**
	 * Returns the singleton instance of this class.
	 *
	 * @return Tribe__Extension_Loader instance.
	 */
	public static function instance() {
		return null === self::$instance ? new self() : self::$instance;
	}

	/**
	 * Intializes each extension.
	 */
	private function __construct() {
		$prefixes = self::get_extension_file_prefixes();
		$extension_filepaths = Tribe__Utils__Plugins::get_plugins_with_prefix( $prefixes );

		foreach ( $extension_filepaths as $plugin_file ) {
			$this->instantiate_extension( $plugin_file );
		}
	}

	/**
	 * Gets tribe extension plugin foldername prefixes
	 *
	 * @return array Prefixes
	 */
	public static function get_extension_file_prefixes() {
		$prefixes = array( 'tribe-ext-' );

		/**
		 * Filter which plugin folder prefixes are considered tribe extensions.
		 *
		 * @param array $prefixes Extension plugin folder name prefixes.
		 */
		return apply_filters( 'tribe_extension_prefixes', $prefixes );
	}

	/**
	 * Instantiates an extension based on info in its plugin file header.
	 *
	 * @param string $plugin_file Full path to extension's plugin file header.
	 *
	 * @return bool Indicates if extension was instantiated successfully.
	 */
	public function instantiate_extension( $plugin_file ) {
		$p_data = $this->get_cached_plugin_data( $plugin_file );
		$p_folder = trailingslashit( dirname( $plugin_file ) );
		$success = false;

		// Nothing to instantiate if class is not set.
		if ( empty( $p_data['ExtensionClass'] ) ) {
			return $success;
		}

		// Default to plugin file when empty.
		$class_file = ! empty( $p_data['ExtensionFile'] ) ? $p_folder . $p_data['ExtensionFile'] : $plugin_file;

		// Include file.
		if ( file_exists( $class_file ) ) {
			// Prevent loading class twice in edge cases where require_once wouldn't work.
			if ( ! class_exists( $p_data['ExtensionClass'] ) ) {
				require( $class_file );
			}
		} else {
			_doing_it_wrong(
				esc_html( $class_file ),
				'Extension file does not exist, please specify valid extension file.',
				'4.3'
			);
		}

		// Class instantiation.
		if ( class_exists( $p_data['ExtensionClass'] ) ) {
			$extension_args = array(
				'file' => $plugin_file,
				'plugin_data' => $p_data,
			);

			// Instantiates extension instance.
			$extension = call_user_func( array( $p_data['ExtensionClass'], 'instance' ), $p_data['ExtensionClass'], $extension_args );

			if ( null !== $extension ) {
				$success = true;
			}
		} else {
			_doing_it_wrong(
				esc_html( $p_data['ExtensionClass'] ),
				'Specified extension class does not exist. Please double check that this class is declared in the extension file.',
				'4.3'
			);
		}

		return $success;
	}

	/**
	 * Retrieves plugin data from cache if it exists.
	 *
	 * @param string $plugin_path Path to plugin header file.
	 *
	 * @return array|null Plugin data or null.
	 */
	public function get_cached_plugin_data( $plugin_path ) {
		$plugin_basename = plugin_basename( $plugin_path );

		if ( ! array_key_exists( $plugin_basename, $this->plugin_data ) ) {
			$this->plugin_data[ $plugin_basename ] = Tribe__Utils__Plugins::get_plugin_data( $plugin_path );
		}

		return $this->plugin_data[ $plugin_basename ];
	}

	/**
	 * Prevent cloning the singleton with 'clone' operator
	 *
	 * @return void
	 */
	private function __clone() {
		_doing_it_wrong(
			__FUNCTION__,
			'Can not use this method on singletons.',
			'4.3'
		);
	}

	/**
	 * Prevent unserializing the singleton instance
	 *
	 * @return void
	 */
	private function __wakeup() {
		_doing_it_wrong(
			__FUNCTION__,
			'Can not use this method on singletons.',
			'4.3'
		);
	}
}
