<?php

namespace Pods;

use Spyc;

/**
 * Class to handle registration of Pods configs and loading/saving of config files.
 *
 * @package Pods
 */
class Config_Handler {

	/**
	 * List of registered config types.
	 *
	 * @since 2.9.0
	 *
	 * @var array
	 */
	protected $registered_config_types = [
		'json' => 'json',
		'yml'  => 'yml',
	];

	/**
	 * List of registered config item types.
	 *
	 * @since 2.9.0
	 *
	 * @var array
	 */
	protected $registered_config_item_types = [
		'pods'      => 'pods',
		'fields'    => 'fields',
		'templates' => 'templates',
		'pages'     => 'pages',
		'helpers'   => 'helpers',
	];

	/**
	 * List of registered paths.
	 *
	 * @since 2.9.0
	 *
	 * @var array
	 */
	protected $registered_paths = [];

	/**
	 * List of registered files.
	 *
	 * @since 2.9.0
	 *
	 * @var array
	 */
	protected $registered_files = [];

	/**
	 * List of registered Pods configs.
	 *
	 * @since 2.9.0
	 *
	 * @var array
	 */
	protected $pods = [];

	/**
	 * List of registered Pods Template configs.
	 *
	 * @since 2.9.0
	 *
	 * @var array
	 */
	protected $templates = [];

	/**
	 * List of registered Pods Page configs.
	 *
	 * @since 2.9.0
	 *
	 * @var array
	 */
	protected $pages = [];

	/**
	 * List of registered Pods Helper configs.
	 *
	 * @since 2.9.0
	 *
	 * @var array
	 */
	protected $helpers = [];

	/**
	 * Associative array list of other registered configs.
	 *
	 * @since 2.9.0
	 *
	 * @var array
	 */
	protected $custom_configs = [];

	/**
	 * List of config names for each file path.
	 *
	 * @since 2.9.0
	 *
	 * @var array
	 */
	protected $file_path_configs = [];

	/**
	 * Config constructor.
	 *
	 * @since 2.9.0
	 */
	public function __construct() {
		// Nothing to see here.
	}

	/**
	 * Setup initial registered paths and load configs.
	 *
	 * @since 2.9.0
	 */
	public function setup() {
		// Register theme.
		$this->register_path( get_template_directory() );

		if ( get_template_directory() !== get_stylesheet_directory() ) {
			// Register child theme.
			$this->register_path( get_stylesheet_directory() );
		}

		$this->load_configs();
		$this->store_configs();
	}

	/**
	 * Register a config type.
	 *
	 * @since 2.9.0
	 *
	 * @param string $config_type Config type.
	 */
	public function register_config_type( $config_type ) {
		$config_type = sanitize_title( $config_type );
		$config_type = str_replace( [ '/', DIRECTORY_SEPARATOR ], '-', $config_type );

		$this->registered_config_types[ $config_type ] = $config_type;
	}

	/**
	 * Unregister a config type.
	 *
	 * @since 2.9.0
	 *
	 * @param string $config_type Config type.
	 */
	public function unregister_config_type( $config_type ) {
		$config_type = sanitize_title( $config_type );
		$config_type = str_replace( [ '/', DIRECTORY_SEPARATOR ], '-', $config_type );

		if ( isset( $this->registered_config_types[ $config_type ] ) ) {
			unset( $this->registered_config_types[ $config_type ] );
		}
	}

	/**
	 * Register a config item type.
	 *
	 * @since 2.9.0
	 *
	 * @param string $item_type Config item type.
	 */
	public function register_config_item_type( $item_type ) {
		$item_type = sanitize_title( $item_type );
		$item_type = str_replace( [ '/', DIRECTORY_SEPARATOR ], '-', $item_type );

		$this->registered_config_item_types[ $item_type ] = $item_type;
	}

	/**
	 * Unregister a config item type.
	 *
	 * @since 2.9.0
	 *
	 * @param string $item_type Config item type.
	 */
	public function unregister_config_item_type( $item_type ) {
		$item_type = sanitize_title( $item_type );
		$item_type = str_replace( [ '/', DIRECTORY_SEPARATOR ], '-', $item_type );

		if ( isset( $this->registered_config_item_types[ $item_type ] ) ) {
			unset( $this->registered_config_item_types[ $item_type ] );
		}
	}

	/**
	 * Register a config file path.
	 *
	 * @since 2.9.0
	 *
	 * @param string $path The config file path to use.
	 */
	public function register_path( $path ) {
		$path = trailingslashit( $path );

		if ( 0 !== strpos( $path, ABSPATH ) ) {
			$path = ABSPATH . $path;
		}

		$this->registered_paths[ $path ] = $path;
	}

	/**
	 * Unregister a config file path.
	 *
	 * @since 2.9.0
	 *
	 * @param string $path The config file path to use.
	 */
	public function unregister_path( $path ) {
		$path = trailingslashit( $path );

		if ( 0 !== strpos( $path, ABSPATH ) ) {
			$path = ABSPATH . $path;
		}

		if ( isset( $this->registered_paths[ $path ] ) ) {
			unset( $this->registered_paths[ $path ] );
		}
	}

	/**
	 * Register a config file.
	 *
	 * @since 2.9.0
	 *
	 * @param string $file        Config file to use.
	 * @param string $config_type Config type to use.
	 */
	public function register_file( $file, $config_type ) {
		if ( ! isset( $this->registered_files[ $config_type ] ) ) {
			$this->registered_files[ $config_type ] = [];
		}

		$this->registered_files[ $config_type ][ $file ] = $file;
	}

	/**
	 * Unregister a config file file.
	 *
	 * @since 2.9.0
	 *
	 * @param string $file        Config file to use.
	 * @param string $config_type Config type to use.
	 */
	public function unregister_file( $file, $config_type ) {
		if ( isset( $this->registered_files[ $config_type ][ $file ] ) ) {
			unset( $this->registered_files[ $config_type ][ $file ] );
		}
	}

	/**
	 * Get file configs based on registered config types and config item types.
	 *
	 * @since 2.9.0
	 *
	 * @return array File configs.
	 */
	protected function get_file_configs() {
		$file_configs = [];

		// Flesh out the config types.
		foreach ( $this->registered_config_types as $config_type ) {
			foreach ( $this->registered_config_item_types as $config_item_type ) {
				$theme_support = false;

				// Themes get pods.json / pods.yml support at root.
				if ( 'pods' === $config_item_type ) {
					$theme_support = true;
				}

				$path = sprintf( '%s.%s', $config_item_type, $config_type );

				$file_configs[] = [
					'type'          => $config_type,
					'file'          => $path,
					'item_type'     => $config_item_type,
					'theme_support' => $theme_support,
				];

				// Prepend pods/ to path for theme paths.
				$path = sprintf( 'pods%s%s', DIRECTORY_SEPARATOR, $path );

				$file_configs[] = [
					'type'          => $config_type,
					'file'          => $path,
					'item_type'     => $config_item_type,
					'theme_support' => true,
				];
			}
		}

		return $file_configs;
	}

	/**
	 * Load configs from registered file paths.
	 *
	 * @since 2.9.0
	 */
	protected function load_configs() {
		/**
		 * Allow plugins/themes to hook into config loading.
		 *
		 * @since 2.9.0
		 *
		 * @param Config $pods_config Pods config object.
		 *
		 */
		do_action( 'pods_config_pre_load_configs', $this );

		$file_configs = $this->get_file_configs();

		$theme_dirs = [
			trailingslashit( get_template_directory() )   => true,
			trailingslashit( get_stylesheet_directory() ) => true,
		];

		$cached_found_configs = pods_transient_get( 'pods_config_handler_found_configs' );

		if ( empty( $cached_found_configs ) ) {
			$cached_found_configs = false;
		}

		$found_configs = [];

		$refresh_cache = false;

		foreach ( $this->registered_paths as $config_path ) {
			foreach ( $file_configs as $file_config ) {
				if ( empty( $file_config['theme_support'] ) && isset( $theme_dirs[ $config_path ] ) ) {
					continue;
				}

				$file_path = $config_path . $file_config['file'];

				if ( $cached_found_configs && ! isset( $cached_found_configs[ $file_path ] ) ) {
					continue;
				}

				$found_config = $this->load_config_file( $file_path, $file_config['type'], $file_config );

				if ( $found_config ) {
					$found_configs[ $file_path ] = true;
				} elseif ( $cached_found_configs ) {
					$refresh_cache = true;
				}
			}
		}

		if (
			$refresh_cache
			|| (
				! empty( $found_configs )
				&& $found_configs !== $cached_found_configs
			)
		) {
			pods_transient_set( 'pods_config_handler_found_configs', $found_configs, WEEK_IN_SECONDS );
		}

		foreach ( $this->registered_files as $config_type => $files ) {
			foreach ( $files as $file ) {
				$this->load_config_file( $file, $config_type );
			}
		}
	}

	/**
	 * Load the config file for a config type from a specific file path.
	 *
	 * @param string $file_path   The config file path to use.
	 * @param string $config_type The config type to use.
	 * @param array  $file_config File config.
	 *
	 * @return bool Whether the config file was found and loaded.
	 */
	protected function load_config_file( $file_path, $config_type, array $file_config = [] ) {
		if ( ! file_exists( $file_path ) || ! is_readable( $file_path ) ) {
			return false;
		}

		$raw_config = file_get_contents( $file_path );

		if ( empty( $raw_config ) ) {
			return false;
		}

		$file_config['type'] = $config_type;

		return $this->load_config( $config_type, $raw_config, $file_path, $file_config );
	}

	/**
	 * Load config from registered file path.
	 *
	 * @since 2.9.0
	 *
	 * @param string $config_type Config type.
	 * @param string $raw_config  Raw config content.
	 * @param string $file_path   The config file path to use.
	 * @param array  $file_config File config.
	 *
	 * @return bool Whether the config was loaded.
	 */
	protected function load_config( $config_type, $raw_config, $file_path, array $file_config ) {
		$config = null;

		if ( 'yml' === $config_type ) {
			require_once PODS_DIR . 'vendor/mustangostang/spyc/Spyc.php';

			$config = Spyc::YAMLLoadString( $raw_config );
		} elseif ( 'json' === $config_type ) {
			$config = json_decode( $raw_config, true );
		} else {
			/**
			 * Parse Pods config from a custom config type.
			 *
			 * @since 2.9.0
			 *
			 * @param array  $config      Config data.
			 * @param string $raw_config  Raw config content.
			 */
			$config = apply_filters( "pods_config_parse_{$config_type}", [], $raw_config );
		}

		/**
		 * Allow filtering the config for additional parsing customization.
		 *
		 * @since 2.9.0
		 *
		 * @param array  $config      Config data.
		 * @param string $config_type Config type.
		 * @param string $raw_config  Raw config content.
		 */
		$config = apply_filters( 'pods_config_parse', $config, $config_type, $raw_config );

		if ( $config && is_array( $config ) ) {
			$this->register_config( $config, $file_path, $file_config );

			return true;
		}

		return false;
	}

	/**
	 * Register config for different item types.
	 *
	 * @since 2.9.0
	 *
	 * @param array  $config      Config data.
	 * @param string $file_path   The config file path to use.
	 * @param array  $file_config File config.
	 */
	protected function register_config( array $config, $file_path, array $file_config = [] ) {
		if ( ! isset( $this->file_path_configs[ $file_path ] ) ) {
			$this->file_path_configs[ $file_path ] = [];
		}

		foreach ( $config as $item_type => $items ) {
			if ( empty( $items ) || ! is_array( $items ) ) {
				continue;
			}

			$supported_item_types = [
				$item_type,
				// We support all item types for pods configs.
				'pods',
			];

			// Skip if the item type is not supported for this config file.
			if ( ! empty( $file_config['item_type'] ) && ! in_array( $file_config['item_type'], $supported_item_types, true ) ) {
				continue;
			}

			if ( ! isset( $this->file_path_configs[ $file_path ][ $item_type ] ) ) {
				$this->file_path_configs[ $file_path ][ $item_type ] = [];
			}

			if ( 'pods' === $item_type ) {
				$this->register_config_pods( $items, $file_path );
			} elseif ( 'fields' === $item_type ) {
				$this->register_config_fields( $items, $file_path );
			} elseif ( 'templates' === $item_type ) {
				$this->register_config_templates( $items, $file_path );
			} elseif ( 'pages' === $item_type ) {
				$this->register_config_pages( $items, $file_path );
			} elseif ( 'helpers' === $item_type ) {
				$this->register_config_helpers( $items, $file_path );
			} else {
				$this->register_config_custom_item_type( $item_type, $items, $file_path );
			}
		}

	}

	/**
	 * Register pod configs.
	 *
	 * @since 2.9.0
	 *
	 * @param array  $items     Config items.
	 * @param string $file_path The config file path to use.
	 */
	protected function register_config_pods( array $items, $file_path = '' ) {
		foreach ( $items as $item ) {
			// Check if the item type and name exists.
			if ( empty( $item['type'] ) || empty( $item['name'] ) ) {
				continue;
			}

			if ( ! isset( $this->pods[ $item['type'] ] ) ) {
				$this->pods[ $item['type'] ] = [];
			}

			if ( isset( $item['id'] ) ) {
				unset( $item['id'] );
			}

			if ( empty( $item['fields'] ) ) {
				$item['fields'] = [];
			}

			$item['_pods_file_source'] = $file_path;

			$this->pods[ $item['type'] ][ $item['name'] ] = $item;

			$this->file_path_configs[ $file_path ]['pods'] = $item['type'] . ':' . $item['name'];
		}

	}

	/**
	 * Register pod field configs.
	 *
	 * @since 2.9.0
	 *
	 * @param array  $items     Config items.
	 * @param string $file_path The config file path to use.
	 */
	protected function register_config_fields( array $items, $file_path = '' ) {
		foreach ( $items as $item ) {
			// Check if the pod name, pod type, item type, and item name exists.
			if ( empty( $item['type'] ) || empty( $item['name'] ) || empty( $item['pod']['name'] ) || empty( $item['pod']['type'] ) ) {
				continue;
			}

			if ( ! isset( $this->pods[ $item['pod']['type'] ] ) ) {
				$this->pods[ $item['pod']['type'] ] = [];
			}

			if ( isset( $item['pod']['id'] ) ) {
				unset( $item['pod']['id'] );
			}

			// Check if pod has been registered yet.
			if ( ! isset( $this->pods[ $item['pod']['type'][ $item['pod']['name'] ] ] ) ) {
				$this->pods[ $item['pod']['type'] ][ $item['pod']['name'] ] = $item['pod'];
			}

			// Check if pod has fields that have been registered yet.
			if ( ! isset( $this->pods[ $item['pod']['type'][ $item['pod']['name'] ] ]['fields'] ) ) {
				$this->pods[ $item['pod']['type'] ][ $item['pod']['name'] ]['fields'] = [];
			}

			if ( isset( $item['id'] ) ) {
				unset( $item['id'] );
			}

			$item['_pods_file_source'] = $file_path;

			$this->pods[ $item['pod']['type'] ][ $item['pod']['name'] ]['fields'][ $item['name'] ] = $item;

			$this->file_path_configs[ $file_path ]['pods'] = $item['pod']['type'] . ':' . $item['pod']['name'] . ':' . $item['name'];
		}

	}

	/**
	 * Register template configs.
	 *
	 * @since 2.9.0
	 *
	 * @param array  $items     Config items.
	 * @param string $file_path The config file path to use.
	 */
	protected function register_config_templates( array $items, $file_path = '' ) {
		foreach ( $items as $item ) {
			// Check if the item name exists.
			if ( empty( $item['name'] ) ) {
				continue;
			}

			if ( isset( $item['id'] ) ) {
				unset( $item['id'] );
			}

			// Legacy support for old Template/Page/Helper objects.
			$item['label']       = $item['name'];
			$item['description'] = $item['code'];
			$item['name'] = sanitize_title( $item['label'] );

			unset( $item['code'] );

			$item['_pods_file_source'] = $file_path;

			$this->templates[ $item['label'] ] = $item;

			$this->file_path_configs[ $file_path ]['templates'] = $item['label'];
		}

	}

	/**
	 * Register page configs.
	 *
	 * @since 2.9.0
	 *
	 * @param array  $items     Config items.
	 * @param string $file_path The config file path to use.
	 */
	protected function register_config_pages( array $items, $file_path = '' ) {
		foreach ( $items as $item ) {
			// Check if the item name exists.
			if ( empty( $item['name'] ) ) {
				continue;
			}

			if ( isset( $item['id'] ) ) {
				unset( $item['id'] );
			}

			// Legacy support for old Template/Page/Helper objects.
			$item['label']       = $item['name'];
			$item['description'] = $item['code'];
			$item['name'] = sanitize_title( $item['label'] );

			unset( $item['code'] );

			$item['_pods_file_source'] = $file_path;

			$this->pages[ $item['name'] ] = $item;

			$this->file_path_configs[ $file_path ]['pages'] = $item['name'];
		}

	}

	/**
	 * Register helper configs.
	 *
	 * @since 2.9.0
	 *
	 * @param array  $items     Config items.
	 * @param string $file_path The config file path to use.
	 */
	protected function register_config_helpers( array $items, $file_path = '' ) {
		foreach ( $items as $item ) {
			// Check if the item name exists.
			if ( empty( $item['name'] ) ) {
				continue;
			}

			if ( isset( $item['id'] ) ) {
				unset( $item['id'] );
			}

			// Legacy support for old Template/Page/Helper objects.
			$item['label']       = $item['name'];
			$item['description'] = $item['code'];
			$item['name']        = sanitize_title( $item['label'] );

			unset( $item['code'] );

			$item['_pods_file_source'] = $file_path;

			$this->helpers[ $item['name'] ] = $item;

			$this->file_path_configs[ $file_path ]['helpers'] = $item['name'];
		}

	}

	/**
	 * Register config items for custom config item type.
	 *
	 * @since 2.9.0
	 *
	 * @param string $item_type Config Item type.
	 * @param array  $items     Config items.
	 * @param string $file_path The config file path to use.
	 */
	protected function register_config_custom_item_type( $item_type, array $items, $file_path = '' ) {
		if ( ! isset( $this->custom_configs[ $item_type ] ) ) {
			$this->custom_configs[ $item_type ] = [];
		}

		foreach ( $items as $item ) {
			/**
			 * Pre-process the item to be saved for a custom item type.
			 *
			 * @since 2.9.0
			 *
			 * @param string $item_type Item type.
			 * @param string $file_path The config file path to use.
			 *
			 * @param array  $item      Item to pre-process.
			 */
			$item = apply_filters( 'pods_config_register_custom_item', $item, $item_type, $file_path );

			// Check if the item name exists.
			if ( empty( $item['name'] ) ) {
				continue;
			}

			if ( isset( $item['id'] ) ) {
				unset( $item['id'] );
			}

			$item['_pods_file_source'] = $file_path;

			$this->custom_configs[ $item_type ][ $item['name'] ] = $item;

			$this->file_path_configs[ $file_path ][ $item_type ] = $item['name'];
		}

	}

	/**
	 * Store the registered configurations.
	 */
	protected function store_configs() {
		$mapped_object_types = [
			'pods'      => 'pod',
			'templates' => 'template',
			'pages'     => 'page',
			'helpers'   => 'helper',
		];

		foreach ( $this->registered_config_item_types as $config_item_type ) {
			if ( 'pods' === $config_item_type ) {
				$configs = $this->pods;
			} elseif ( 'templates' === $config_item_type ) {
				$configs = $this->templates;
			} elseif ( 'pages' === $config_item_type ) {
				$configs = $this->pages;
			} elseif ( 'helpers' === $config_item_type ) {
				$configs = $this->helpers;
			} elseif ( isset( $this->custom_configs[ $config_item_type ] ) ) {
				$configs = $this->custom_configs[ $config_item_type ];
			} else {
				continue;
			}

			$real_type = isset( $mapped_object_types[ $config_item_type ] )
				? $mapped_object_types[ $config_item_type ]
				: $config_item_type;

			foreach ( $configs as $key => $config ) {
				if ( 'pod' === $real_type ) {
					foreach ( $config as $pod ) {
						$pod['object_type']         = $real_type;
						$pod['object_storage_type'] = 'file';

						pods_register_type( $key, $pod['name'], $pod );
					}
				} else {
					$config['object_storage_type'] = 'file';

					pods_register_object( $config, $real_type );
				}
			}
		}
	}

	/**
	 * @todo Get list of configs that do not match DB.
	 * @todo Handle syncing changed configs to DB.
	 * @todo Handle syncing configs from DB to file.
	 */

}
