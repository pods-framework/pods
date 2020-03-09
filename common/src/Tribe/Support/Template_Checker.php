<?php
/**
 * Examines a plugin's views directory and builds a list of view filenames
 * and their respective version numbers.
 */
class Tribe__Support__Template_Checker {
	protected $plugin_name = '';
	protected $plugin_version = '';
	protected $plugin_views_dir = '';
	protected $theme_views_dir = '';

	protected $originals = array();
	protected $overrides = array();


	/**
	 * Examine the plugin views (and optionally any theme overrides) and analyse
	 * the version numbers where possible.
	 *
	 * @param string $plugin_version
	 * @param string $plugin_views_dir
	 * @param string $theme_views_dir
	 */
	public function __construct( $plugin_version, $plugin_views_dir, $theme_views_dir = '' ) {
		$this->plugin_version = $this->base_version_number( $plugin_version );
		$this->plugin_views_dir = $plugin_views_dir;
		$this->theme_views_dir = $theme_views_dir;

		$this->scan_view_directory();
		$this->scan_for_overrides();
	}

	/**
	 * Given a version number with an alpha/beta type suffix, strips that suffix and
	 * returns the "base" version number.
	 *
	 * For example, given "9.8.2beta1" this method will return "9.8.2".
	 *
	 * The utility of this is that if the author of a template change sets the
	 * version tag in the template header to 9.8.2 (to continue the same example) we
	 * don't need to worry about updating that for each alpha, beta or RC we put out.
	 *
	 * @param string $version_number
	 *
	 * @return string
	 */
	protected function base_version_number( $version_number ) {
		return preg_replace( '/[a-z]+[a-z0-9]*$/i', '', $version_number );
	}

	/**
	 * Recursively scans the plugin's view directory and examines the template headers
	 * of each file it finds within.
	 */
	protected function scan_view_directory() {
		// If the provided directory is invalid flag the problem and go no further
		if ( $this->bad_directory( $this->plugin_views_dir ) ) {
			return;
		}

		$view_directory = new RecursiveDirectoryIterator( $this->plugin_views_dir );
		$directory_list = new RecursiveIteratorIterator( $view_directory );

		foreach ( $directory_list as $file ) {
			$this->scan_view( $file );
		}
	}

	/**
	 * Scans an individual view file, adding it's version number (if found) to the
	 * $this->views array.
	 *
	 * @param SplFileInfo $file
	 */
	protected function scan_view( SplFileInfo $file ) {
		if ( ! $file->isFile() || ! $file->isReadable() ) {
			return;
		}

		$version = $this->get_template_version( $file->getPathname() );
		$this->originals[ $this->short_name( $file->getPathname() ) ] = $version;
	}

	protected function scan_for_overrides() {
		// If the provided directory is invalid flag the problem and go no further
		if ( $this->bad_directory( $this->theme_views_dir ) ) {
			return;
		}

		foreach ( $this->originals as $view_file => $current_version ) {
			$override_path = trailingslashit( $this->theme_views_dir ) . $view_file;

			if ( ! is_file( $override_path ) || ! is_readable( $override_path ) ) {
				continue;
			}

			$this->overrides[ $view_file ] = $this->get_template_version( $override_path );
		}
	}

	/**
	 * Tests to ensure the provided view directory path is invalid or unreadable.
	 *
	 * @param  string $directory
	 * @return bool
	 */
	protected function bad_directory( $directory ) {
		if ( is_dir( $directory ) && is_readable( $directory ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Inspects the template header block within the specified file and extracts the
	 * version number, if one can be found.
	 *
	 * @param  string $template_filepath
	 * @return string
	 */
	protected function get_template_version( $template_filepath ) {
		if ( ! is_file( $template_filepath ) || ! is_readable( $template_filepath ) ) {
			return '';
		}

		$view_content = file_get_contents( $template_filepath );

		if ( ! preg_match( '/^\s*\*\s*@version\s*([0-9\.]+)/mi', $view_content, $matches ) ) {
			return '';
		}

		return $matches[1];
	}

	/**
	 * Given a full filepath (ie, to a view file), chops off the base path found
	 * in $this->plugin_views_dir.
	 *
	 * For example, given:
	 *
	 *     $this->plugin_views_dir = '/srv/project/wp-content/plugins/my-plugin/views'
	 *     $full_filepath          = '/srv/project/wp-content/plugins/my-plugin/views/modules/icon.php'
	 *
	 * Returns:
	 *
	 *     'modules/icon.php'
	 *
	 * @param  string $full_filepath
	 * @return string
	 */
	protected function short_name( $full_filepath ) {
		if ( 0 === strpos( $full_filepath, $this->plugin_views_dir ) ) {
			return trim( substr( $full_filepath, strlen( $this->plugin_views_dir ) ), DIRECTORY_SEPARATOR );
		}

		return $full_filepath;
	}

	/**
	 * Returns an array of the plugin's shipped view files, where each key is the
	 * view filename and the value is the version it was last updated.
	 *
	 * @return array
	 */
	public function get_views() {
		return $this->originals;
	}

	/**
	 * Returns an array of any or all of the plugin's shipped view files that contain
	 * a version field in their header blocks.
	 *
	 * @see $this->get_views() for format of returned array
	 *
	 * @return array
	 */
	public function get_versioned_views() {
		$versioned_views = array();

		foreach ( $this->originals as $key => $version ) {
			if ( ! empty( $version ) ) {
				$versioned_views[ $key ] = $version;
			}
		}

		return $versioned_views;
	}

	/**
	 * Returns an array of any shipped plugin views that were updated or introduced
	 * with the current release (as specified by $this->plugin_version).
	 *
	 * @see $this->get_views() for format of returned array
	 *
	 * @return array
	 */
	public function get_views_tagged_this_release() {
		$currently_tagged_views = array();

		foreach ( $this->get_versioned_views() as $key => $version ) {
			if ( $version === $this->plugin_version ) {
				$currently_tagged_views[ $key ] = $version;
			}
		}

		return $currently_tagged_views;
	}

	/**
	 * Returns an array of theme overrides, where each key is the view filename and the
	 * value is the version it was last updated (may be empty).
	 *
	 * @return array
	 */
	public function get_overrides() {
		return $this->overrides;
	}

	/**
	 * Returns an array of any or all theme overrides that contain a version field in their
	 * header blocks.
	 *
	 * @see $this->get_overrides() for format of returned array
	 *
	 * @return array
	 */
	public function get_versioned_overrides() {
		$versioned_views = array();

		foreach ( $this->overrides as $key => $version ) {
			if ( ! empty( $version ) ) {
				$versioned_views[ $key ] = $version;
			}
		}

		return $versioned_views;
	}

	/**
	 * Returns an array of any or all theme overrides that seem to be based on an earlier
	 * version than that which currently ships with the plugin.
	 *
	 * If optional param $include_unknown is set to true, the list will include theme
	 * overrides where the version could not be determined (for instance, this might result
	 * in theme overrides where the template header - or version tag - was removed being
	 * included).
	 *
	 * @see $this->get_overrides() for format of returned array
	 *
	 * @param bool $include_unknown = false
	 * @return array
	 */
	public function get_outdated_overrides( $include_unknown = false ) {
		$outdated  = array();
		$originals = $this->get_versioned_views();

		$overrides = $include_unknown
			? $this->get_overrides()
			: $this->get_versioned_overrides();

		foreach ( $overrides as $view => $override_version ) {
			if ( empty( $originals[ $view ] ) ) {
				continue;
			}

			$shipped_version = $originals[ $view ];

			if ( version_compare( $shipped_version, $override_version, '>' ) ) {
				$outdated[ $view ] = $override_version;
			}
		}

		return $outdated;
	}
}
