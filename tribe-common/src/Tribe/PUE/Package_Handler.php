<?php


/**
 * Class Tribe__PUE__Package_Handler
 *
 * Middleman for plugin updates.
 */
class Tribe__PUE__Package_Handler {

	/**
	 * @var static
	 */
	protected static $instance;
	/**
	 * @var WP_Upgrader
	 */
	protected $upgrader;
	/**
	 * @var WP_Filesystem_Base
	 */
	private $filesystem;

	/**
	 * Tribe__PUE__Package_Handler constructor.
	 *
	 * @param WP_Filesystem_Base|null $wp_filesystem
	 */
	public function __construct( WP_Filesystem_Base $wp_filesystem = null ) {
		if ( null === $wp_filesystem ) {
			global $wp_filesystem;
		}
		$this->filesystem = $wp_filesystem;
	}

	/**
	 * @return Tribe__PUE__Package_Handler
	 */
	public static function instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Filters the package download step to store the downloaded file with a shorter file name.
	 *
	 * @param bool        $reply    Whether to bail without returning the package.
	 *                              Default false.
	 * @param string      $package  The package file name or URL.
	 * @param WP_Upgrader $upgrader The WP_Upgrader instance.
	 */
	public function filter_upgrader_pre_download( $reply, $package, WP_Upgrader $upgrader ) {
		if ( $this->is_mt_package( $package ) ) {
			$this->upgrader = $upgrader;

			return $this->download( $package );
		}

		return $reply;
	}

	/**
	 * Whether the current package is an MT plugin package or not.
	 *
	 * @param string $package The package file name or URL.
	 *
	 * @return bool
	 */
	protected function is_mt_package( $package ) {
		if (
			empty( $package )
			|| ! preg_match( '!^(http|https|ftp)://!i', $package )
		) {
			return false;
		}

		$query_vars = parse_url( $package, PHP_URL_QUERY );

		if ( empty( $query_vars ) ) {
			return false;
		}

		wp_parse_str( $query_vars, $parsed );

		return isset( $parsed['pu_get_download'] ) && $parsed['pu_get_download'] == 1;
	}

	/**
	 * A mimic of the `WP_Upgrader::download_package` method that adds a step to store the temp file with a shorter
	 * file name.
	 *
	 * @see WP_Upgrader::download_package()
	 *
	 * @param string $package The URI of the package. If this is the full path to an
	 *                        existing local file, it will be returned untouched.
	 *
	 * @return string|WP_Error The full path to the downloaded package file, or a WP_Error object.
	 */
	protected function download( $package ) {
		if ( empty( $this->filesystem ) ) {
			// try to connect
			$this->upgrader->fs_connect( [ WP_CONTENT_DIR, WP_PLUGIN_DIR ] );

			global $wp_filesystem;

			// still empty?
			if ( empty( $wp_filesystem ) ) {
				// bail
				return false;
			}

			$this->filesystem = $wp_filesystem;
		}

		$this->upgrader->skin->feedback( 'downloading_package', $package );

		$download_file = download_url( $package );

		if ( is_wp_error( $download_file ) ) {
			return new WP_Error( 'download_failed', $this->upgrader->strings['download_failed'],
				$download_file->get_error_message() );
		}

		$file = $this->get_short_filename( $download_file );

		$moved = $this->filesystem->move( $download_file, $file );

		if ( empty( $moved ) ) {
			// we tried, we failed, we bail and let WP do its job
			return false;
		}

		return $file;
	}

	/**
	 * Returns the absolute path to a shorter filename version of the original download temp file.
	 *
	 * The path will point to the same temp dir (WP handled) but shortening the filename to a
	 * 6 chars hash to cope with OSes limiting the max number of chars in a file path.
	 * The original filename would be a sanitized version of the URL including query args.
	 *
	 * @param string $download_file The absolute path to the original download file.
	 *
	 * @return string The absolute path to a shorter name version of the downloaded file.
	 */
	protected function get_short_filename( $download_file ) {
		$extension = pathinfo( $download_file, PATHINFO_EXTENSION );
		$filename  = substr( md5( $download_file ), 0, 5 );
		$file      = dirname( $download_file ) . '/' . $filename . '.' . $extension;

		return $file;
	}
}
