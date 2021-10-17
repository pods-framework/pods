<?php

/**
 * Fontpack
 *
 * @package Icon_Picker
 * @version 0.1.0
 * @author  Dzikri Aziz <kvcrvt@gmail.com>
 */

final class Icon_Picker_Fontpack {

	/**
	 * Icon_Picker_Fontpack singleton
	 *
	 * @static
	 * @since  0.1.0
	 * @access protected
	 * @var    Icon_Picker_Fontpack
	 */
	protected static $instance;

	/**
	 * Fontpack directory path
	 *
	 * @since  0.1.0
	 * @access protected
	 * @var    string
	 */
	protected $dir;

	/**
	 * Fontpack directory url path
	 *
	 * @since  0.1.0
	 * @access protected
	 * @var    string
	 */
	protected $url;

	/**
	 * Error messages
	 *
	 * @since  0.1.0
	 * @access protected
	 * @var    array
	 */
	protected $messages = array();

	/**
	 * Icon packs
	 *
	 * @since  0.1.0
	 * @access protected
	 * @var    array
	 */
	protected $packs = array();


	/**
	 * Get instance
	 *
	 * @static
	 * @since  0.1.0
	 * @return Icon_Picker_Fontpack
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	/**
	 * Getter magic
	 *
	 * @since  0.1.0
	 * @param  string $name Property name.
	 * @return mixed  NULL if attribute doesn't exist.
	 */
	public function __get( $name ) {
		if ( isset( $this->$name ) ) {
			return $this->$name;
		}

		return null;
	}


	/**
	 * Setter magic
	 *
	 * @since  0.1.0
	 * @return bool
	 */
	public function __isset( $name ) {
		return isset( $this->$name );
	}


	/**
	 * Constructor
	 *
	 * @since  0.1.0
	 * @access protected
	 * @return Icon_Picker_Fontpack
	 */
	protected function __construct() {
		/**
		 * Allow different system path for fontpacks
		 *
		 * @since 0.1.0
		 * @param string $dir Directory path, defaults to /wp-content/fontpacks.
		 */
		$this->dir = apply_filters( 'icon_picker_fontpacks_dir_path', WP_CONTENT_DIR . '/fontpacks' );

		if ( ! is_readable( $this->dir ) ) {
			return;
		}

		/**
		 * Allow different URL path for fontpacks
		 *
		 * @since 0.4.0
		 * @param string $url URL path, defaults to /wp-content/fontpacks
		 */
		$this->url = apply_filters( 'icon_picker_fontpacks_dir_url', WP_CONTENT_URL . '/fontpacks' );

		$this->messages = array(
			'no_config'    => __( 'Icon Picker: %1$s was not found in %2$s.', 'icon-picker' ),
			'config_error' => __( 'Icon Picker: %s contains an error or more.', 'icon-picker' ),
			'invalid'      => __( 'Icon Picker: %1$s is not set or invalid in %2$s.', 'icon-picker' ),
			'duplicate'    => __( 'Icon Picker: %1$s is already registered. Please check your font pack config file: %2$s.', 'icon-picker' ),
		);

		$this->collect_packs();
		$this->register_packs();
	}


	/**
	 * Collect icon packs
	 *
	 * @since  0.1.0
	 * @access protected
	 * @return void
	 */
	protected function collect_packs() {
		$iterator = new DirectoryIterator( $this->dir );

		foreach ( $iterator as $pack_dir ) {
			if ( $pack_dir->isDot() || ! $pack_dir->isDir() || ! $pack_dir->isReadable() ) {
				continue;
			}

			$pack_dirname = $pack_dir->getFilename();
			$pack_data    = $this->get_pack_data( $pack_dir );

			if ( ! empty( $pack_data ) ) {
				$this->packs[ $pack_dirname ] = $pack_data;
			}
		}
	}


	/**
	 * Register icon packs
	 *
	 * @since  0.1.0
	 * @access protected
	 * @return void
	 */
	protected function register_packs() {
		if ( empty( $this->packs ) ) {
			return;
		}

		$icon_picker = Icon_Picker::instance();
		require_once "{$icon_picker->dir}/includes/types/fontello.php";

		foreach ( $this->packs as $pack_data ) {
			$icon_picker->registry->add( new Icon_Picker_Type_Fontello( $pack_data ) );
		}
	}


	/**
	 * Get icon pack data
	 *
	 * @since  0.1.0
	 * @access protected
	 * @param  DirectoryIterator $pack_dir Icon pack directory object.
	 * @return array Icon pack data array or FALSE.
	 */
	protected function get_pack_data( DirectoryIterator $pack_dir ) {
		$pack_dirname  = $pack_dir->getFilename();
		$pack_path     = $pack_dir->getPathname();
		$cache_id      = "icon_picker_fontpack_{$pack_dirname}";
		$cache_data    = get_transient( $cache_id );
		$config_file   = "{$pack_path}/config.json";

		if ( false !== $cache_data && $cache_data['version'] === $pack_dir->getMTime() ) {
			return $cache_data;
		}

		// Make sure the config file exists and is readable.
		if ( ! is_readable( $config_file ) ) {
			trigger_error(
				sprintf(
					esc_html( $this->messages['no_config'] ),
					'<code>config.json</code>',
					sprintf( '<code>%s</code>', esc_html( $pack_path ) )
				)
			);

			return false;
		}

		$config = json_decode( file_get_contents( $config_file ), true );
		$errors = json_last_error();

		if ( ! empty( $errors ) ) {
			trigger_error(
				sprintf(
					esc_html( $this->messages['config_error'] ),
					sprintf( '<code>%s/config.json</code>', esc_html( $pack_path ) )
				)
			);

			return false;
		}

		$keys   = array( 'name', 'glyphs', 'css_prefix_text' );
		$items  = array();

		// Check each required config.
		foreach ( $keys as $key ) {
			if ( empty( $config[ $key ] ) ) {
				trigger_error(
					sprintf(
						esc_html( $this->messages['invalid'] ),
						sprintf( '<code><em>%s</em></code>', esc_html( $key ) ),
						esc_html( $config_file )
					)
				);

				return false;
			}
		}

		// Bail if no glyphs found.
		if ( ! is_array( $config['glyphs'] ) || empty( $config['glyphs'] ) ) {
			return false;
		}

		foreach ( $config['glyphs'] as $glyph ) {
			if ( ! empty( $glyph['css'] ) ) {
				$items[] = array(
					'id'   => $config['css_prefix_text'] . $glyph['css'],
					'name' => $glyph['css'],
				);
			}
		}

		if ( empty( $items ) ) {
			return false;
		}

		$pack_data = array(
			'id'             => "pack-{$config['name']}",
			'name'           => sprintf( __( 'Pack: %s', 'icon-picker' ), $config['name'] ),
			'version'        => $pack_dir->getMTime(),
			'items'          => $items,
			'stylesheet_uri' => "{$this->url}/{$pack_dirname}/css/{$config['name']}.css",
			'dir'            => "{$this->dir}/{$pack_dirname}",
			'url'            => "{$this->url}/{$pack_dirname}",
		);

		set_transient( $cache_id, $pack_data, DAY_IN_SECONDS );

		return $pack_data;
	}
}
