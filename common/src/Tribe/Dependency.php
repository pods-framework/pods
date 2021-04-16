<?php
// Don't load directly
defined( 'WPINC' ) or die;

if ( ! class_exists( 'Tribe__Dependency' ) ) {
	/**
	 * Tracks which Tribe (or related) plugins are registered, activated, or requirements satisfied.
	 */
	class Tribe__Dependency {

		/**
		 * A multidimensional array of active tribe plugins in the following format
		 *
		 * array(
		 *  'class'   => 'main class name',
		 *  'version' => 'version num', (optional)
		 *  'path'    => 'Path to the main plugin/bootstrap file' (optional)
		 * )
		 */
		protected $active_plugins = [];

		/**
		 * A multidimensional array of active tribe plugins in the following format
		 *
		 * array(
		 *  'class'             => 'main class name',
		 *  'path'              => 'Path to the main plugin/bootstrap file'
		 *  'version'           => 'version num', (optional)
		 *  'dependencies'      => 'A multidimensional of dependencies' (optional)
		 * )
		 */
		protected $registered_plugins = [];

		/**
		 * An array of class Tribe__Admin__Notice__Plugin_Download per plugin
		 *
		 * @since 4.9
		 *
		 */
		protected $admin_messages = [];

		/**
		 * Adds a plugin to the active list
		 *
		 * @since 4.9
		 *
		 * @param string      $main_class   Main/base class for this plugin
		 * @param null|string $version      Version number of plugin
		 * @param null|string $path         Path to the main plugin/bootstrap file
		 * @param array       $dependencies An array of dependencies for a plugin
		 */
		public function add_registered_plugin( $main_class, $version = null, $path = null, $dependencies = array() ) {
			$plugin = array(
				'class'        => $main_class,
				'version'      => $version,
				'path'         => $path,
				'dependencies' => $dependencies,
			);

			$this->registered_plugins[ $main_class ] = $plugin;

			if ( $path ) {
				$this->admin_messages[ $main_class ] = new Tribe__Admin__Notice__Plugin_Download( $path );
			}
		}

		/**
		 * Retrieves registered plugin array
		 *
		 * @since 4.9
		 *
		 * @return array
		 */
		public function get_registered_plugins() {
			return $this->registered_plugins;
		}

		/**
		 * Adds a plugin to the active list
		 *
		 * @param string $main_class Main/base class for this plugin
		 * @param string $version    Version number of plugin
		 * @param string $path       Path to the main plugin/bootstrap file
		 */
		public function add_active_plugin( $main_class, $version = null, $path = null ) {
			$plugin = array(
				'class'        => $main_class,
				'version'      => $version,
				'path'         => $path,
			);

			$this->active_plugins[ $main_class ] = $plugin;
		}

		/**
		 * Retrieves active plugin array
		 *
		 * @return array
		 */
		public function get_active_plugins() {
			return $this->active_plugins;
		}

		/**
		 * Searches the plugin list for key/value pair and return the full details for that plugin
		 *
		 * @param string $search_key The array key this value will appear in
		 * @param string $search_val The value itself
		 *
		 * @return array|null
		 */
		public function get_plugin_by_key( $search_key, $search_val ) {
			foreach ( $this->get_active_plugins() as $plugin ) {
				if ( isset( $plugin[ $search_key ] ) && $plugin[ $search_key ] === $search_val ) {
					return $plugin;
				}
			}

			return null;
		}

		/**
		 * Retrieves the plugins details by class name
		 *
		 * @param string $main_class Main/base class for this plugin
		 *
		 * @return array|null
		 */
		public function get_plugin_by_class( $main_class ) {
			return $this->get_plugin_by_key( 'class', $main_class );
		}

		/**
		 * Retrieves the version of the plugin
		 *
		 * @param string $main_class Main/base class for this plugin
		 *
		 * @return string|null Version
		 */
		public function get_plugin_version( $main_class ) {
			$plugin = $this->get_plugin_by_class( $main_class );

			return ( isset( $plugin['version'] ) ? $plugin['version'] : null );
		}

		/**
		 * Checks if the plugin is active
		 *
		 * @param string $main_class Main/base class for this plugin
		 *
		 * @return bool
		 */
		public function is_plugin_active( $main_class ) {
			return ( $this->get_plugin_by_class( $main_class ) !== null );
		}

		/**
		 * Searches the registered plugin list for key/value pair and return the full details for that plugin
		 *
		 * @since 4.9
		 *
		 * @param string $search_key The array key this value will appear in
		 * @param string $search_val The value itself
		 *
		 * @return array|null
		 */
		public function get_registered_plugin_by_key( $search_key, $search_val ) {
			foreach ( $this->get_registered_plugins() as $plugin ) {
				if ( isset( $plugin[ $search_key ] ) && $plugin[ $search_key ] === $search_val ) {
					return $plugin;
				}
			}

			return null;
		}

		/**
		 * Retrieves the registered plugins details by class name
		 *
		 * @since 4.9
		 *
		 * @param string $main_class Main/base class for this plugin
		 *
		 * @return array|null
		 */
		public function get_registered_plugin_by_class( $main_class ) {
			return $this->get_registered_plugin_by_key( 'class', $main_class );
		}

		/**
		 * Retrieves the version of the registered plugin
		 *
		 * @since 4.9
		 *
		 * @param string $main_class Main/base class for this plugin
		 *
		 * @return string|null Version
		 */
		public function get_registered_plugin_version( $main_class ) {
			$plugin = $this->get_registered_plugin_by_class( $main_class );

			return ( isset( $plugin['version'] ) ? $plugin['version'] : null );
		}

		/**
		 * Checks if the plugin is active
		 *
		 * @since 4.9
		 *
		 * @param string $main_class Main/base class for this plugin
		 *
		 * @return bool
		 */
		public function is_plugin_registered( $main_class ) {
			return ( $this->get_registered_plugin_by_class( $main_class ) !== null );
		}


		/**
		 * Checks if a plugin is active and has the specified version
		 *
		 * @since 4.9
		 *
		 * @param string $main_class Main/base class for this plugin
		 * @param string $version Version to do a compare against
		 * @param string $compare Version compare string, defaults to >=
		 *
		 * @return bool
		 */
		public function is_plugin_version( $main_class, $version, $compare = '>=' ) {
			//active plugin check to see if the correct version is active
			if ( ! $this->is_plugin_active( $main_class ) ) {
				return false;
			} elseif ( version_compare( $this->get_plugin_version( $main_class ), $version, $compare ) ) {
				return true;
			}

			return false;
		}

		/**
		 * Is the plugin registered with at least the minimum version
		 *
		 * @since 4.9
		 *
		 * @param string $main_class Main/base class for this plugin
		 * @param string $version Version to do a compare against
		 * @param string $compare Version compare string, defaults to >=
		 *
		 * @return bool
		 */
		public function is_plugin_version_registered( $main_class, $version, $compare = '>=' ) {
			//registered plugin check if addon as it tests if it might load
			if ( ! $this->is_plugin_registered( $main_class ) ) {
				return false;
			} elseif ( version_compare( $this->get_registered_plugin_version( $main_class ), $version, $compare ) ) {
				return true;
			}

			return false;
		}

		/**
		 * Checks if each plugin is active and exceeds the specified version number
		 *
		 * @param array $plugins_required Each item is a 'class_name' => 'min version' pair. Min ver can be null.
		 *
		 * @return bool
		 */
		public function has_requisite_plugins( $plugins_required = array() ) {
			foreach ( $plugins_required as $class => $version ) {
				// Return false if the plugin is not set or is a lesser version
				if ( ! $this->is_plugin_active( $class ) ) {
					return false;
				}

				if ( null !== $version && ! $this->is_plugin_version( $class, $version ) ) {
					return false;
				}
			}

			return true;
		}

		/**
		 * Retrieves Registered Plugin by Class Name from Array
		 *
		 * @since 4.9
		 *
		 * @return array|boolean
		 */
		public function get_registered_plugin( $class ) {
			$plugins = $this->registered_plugins;

			return isset( $plugins[ $class ] ) ? $plugins[ $class ] : false;
		}

		/**
		 * Gets all dependencies or single class requirements if parent, co, add does not exist use array as is if they
		 * do exist check each one in turn.
		 *
		 * @since 4.9
		 *
		 * @param array  $plugin        An array of data for given registered plugin
		 * @param array  $dependencies  An array of dependencies for a plugin
		 * @param bool   $addon         Indicates if the plugin is an add-on for The Events Calendar or Event Tickets
		 *
		 * @return bool  returns false if any dependency is invalid
		 */
		public function has_valid_dependencies( $plugin, $dependencies = array(), $addon = false ) {
			if ( empty( $dependencies ) ) {
				return true;
			}

			$failed_dependency = 0;

			$tribe_plugins = new Tribe__Plugins();

			foreach ( $dependencies as $class => $version ) {

				// if no class for add-on
				$checked_plugin = $this->get_registered_plugin( $class );
				if ( $addon && empty( $checked_plugin ) ) {
					continue;
				}

				$is_registered = $this->is_plugin_version_registered( $class, $version );
				if ( ! empty( $is_registered ) ) {
					continue;
				}

				$dependent_plugin = $tribe_plugins->get_plugin_by_class( $class );

				$pue = $this->get_pue_from_class( $dependent_plugin['class'] );
				$has_pue_notice = $pue ? tribe( 'pue.notices' )->has_notice( $pue->pue_install_key ) : false;

				$this->admin_messages[ $plugin['class'] ]->add_required_plugin(
					$dependent_plugin['short_name'],
					$dependent_plugin['thickbox_url'],
					$is_registered,
					$version,
					$addon,
					$has_pue_notice
				);
				$failed_dependency++;
			}

			return $failed_dependency;
		}

		/**
		 * Gets the Tribe__PUE__Checker instance of a given plugin based on the class.
		 *
		 * @since  4.9.12
		 *
		 * @param  string $class Which plugin main class we are looking for.
		 *
		 * @return Tribe__PUE__Checker
		 */
		public function get_pue_from_class( $class ) {
			if ( ! is_string( $class ) ) {
				return false;
			}

			// If class doesnt exist the plugin doesnt exist.
			if ( ! class_exists( $class ) ) {
				return false;
			}

			/**
			 * These callbacks are only required to prevent fatals.
			 * Only happen for plugin that use PUE.
			 */
			$callback_map = [
				'Tribe__Events__Pro__Main' => function() {
					$pue_reflection = new ReflectionClass( Tribe__Events__Pro__PUE::class );
					$values = $pue_reflection->getStaticProperties();
					$values['plugin_file'] = EVENTS_CALENDAR_PRO_FILE;
					return $values;
				},
				'Tribe__Events__Filterbar__View' => function() {
					$pue_reflection = new ReflectionClass( Tribe__Events__Filterbar__PUE::class );
					$values = $pue_reflection->getStaticProperties();
					$values['plugin_file'] = TRIBE_EVENTS_FILTERBAR_FILE;
					return $values;
				},
				'Tribe__Events__Tickets__Eventbrite__Main' => function() {
					$pue_reflection = new ReflectionClass( Tribe__Events__Tickets__Eventbrite__PUE::class );
					$values = $pue_reflection->getStaticProperties();
					$values['plugin_file'] = EVENTBRITE_PLUGIN_FILE;
					return $values;
				},
			];

			// Bail when class is not mapped.
			if ( ! isset( $callback_map[ $class ] ) ) {
				return false;
			}

			// Use the callback to get the returns without fatals
			$values = $callback_map[ $class ]();
			$pue_instance = new Tribe__PUE__Checker( $values['update_url'], $values['pue_slug'], [], plugin_basename( $values['plugin_file'] ) );

			return $pue_instance;
		}

		/**
		 * Register a Plugin
		 *
		 * @since 4.9
		 *
		 * @param       $file_path
		 * @param       $main_class
		 * @param       $version
		 * @param array $classes_req
		 * @param array $dependencies
		 */
		public function register_plugin( $file_path, $main_class, $version, $classes_req = array(), $dependencies = array() ) {
			/**
			 * Filters the version string for a plugin.
			 *
			 * @since 4.9
			 *
			 * @param string $version The plugin version number, e.g. "4.0.4".
			 * @param array $dependencies An array of dependencies for the plugins. These can include parent, add-on and other dependencies.
			 * @param string $file_path The absolute path to the plugin main file.
			 * @param array $classes_req Any Main class files/tribe plugins required for this to run.
			 */
			$version = apply_filters( "tribe_register_{$main_class}_plugin_version", $version, $dependencies, $file_path, $classes_req );
			/**
			 * Filters the dependencies array for a plugin.
			 *
			 * @since 4.9
			 *
			 * @param array $dependencies An array of dependencies for the plugins. These can include parent, add-on and other dependencies.
			 * @param string $version The plugin version number, e.g. "4.0.4".
			 * @param string $file_path The absolute path to the plugin main file.
			 * @param array $classes_req Any Main class files/tribe plugins required for this to run.
			 */
			$dependencies = apply_filters( "tribe_register_{$main_class}_plugin_dependencies", $dependencies, $version, $file_path, $classes_req );

			//add all plugins to registered_plugins
			$this->add_registered_plugin( $main_class, $version, $file_path, $dependencies );

			// Checks to see if the plugins are active for extensions
			if ( ! empty( $classes_req ) && ! $this->has_requisite_plugins( $classes_req ) ) {
				$tribe_plugins = new Tribe__Plugins();
				foreach ( $classes_req as $class => $plugin_version ) {
					$plugin         = $tribe_plugins->get_plugin_by_class( $class );

					$is_active      = $this->is_plugin_version( $class, $plugin_version );
					$pue            = $this->get_pue_from_class( $plugin['class'] );
					$has_pue_notice = $pue ? tribe( 'pue.notices' )->has_notice( $pue->pue_install_key ) : false;

					$this->admin_messages[ $main_class ]->add_required_plugin(
						$plugin['short_name'],
						$plugin['thickbox_url'],
						$is_active,
						$plugin_version,
						false,
						$has_pue_notice
					);
				}
			}

			// only set The Events Calendar and Event Tickets to Active when registering
			if ( 'Tribe__Events__Main' === $main_class || 'Tribe__Tickets__Main' === $main_class ) {
				$this->add_active_plugin( $main_class, $version, $file_path );
			}

		}

		/**
		 * Checks if this plugin has permission to run, if not it notifies the admin
		 *
		 * @since 4.9
		 *
		 * @param string $file_path    Full file path to the base plugin file
		 * @param string $main_class   The Main/base class for this plugin
		 * @param string $version      The version
		 * @param array  $classes_req  Any Main class files/tribe plugins required for this to run
		 * @param array  $dependencies an array of dependencies to check
		 *
		 * @return bool Indicates if plugin should continue initialization
		 */
		public function check_plugin( $main_class ) {

			$parent_dependencies = $co_dependencies = $addon_dependencies = 0;

			//check if plugin is registered, if not return false
			$plugin = $this->get_registered_plugin( $main_class );
			if ( empty( $plugin ) ) {
				return false;
			}

			// check parent dependencies in add-on
			if ( ! empty( $plugin['dependencies']['parent-dependencies'] ) ) {
				$parent_dependencies = $this->has_valid_dependencies( $plugin, $plugin['dependencies']['parent-dependencies'] );
			}
			//check co-dependencies in add-on
			if ( ! empty( $plugin['dependencies']['co-dependencies'] ) ) {
				$co_dependencies = $this->has_valid_dependencies( $plugin, $plugin['dependencies']['co-dependencies'] );
			}

			//check add-on dependencies from parent
			$addon_dependencies = $this->check_addon_dependencies( $main_class );

			//if good then we set as active plugin and continue to load
			if ( ! $parent_dependencies && ! $co_dependencies && ! $addon_dependencies ) {
				$this->add_active_plugin( $main_class, $plugin['version'], $plugin['path'] );

				return true;
			}

			return false;
		}

		/**
		 * Check an add-on dependencies for its parent
		 *
		 * @since 4.9
		 *
		 * @param string  $main_class   a string of the main class for the plugin being checked
		 *
		 * @return bool  returns false if any dependency is invalid
		 */
		protected function check_addon_dependencies( $main_class ) {

			$addon_dependencies = 0;

			foreach ( $this->registered_plugins as $registered ) {
				if ( empty( $registered['dependencies']['addon-dependencies'][ $main_class ] ) ) {
					continue;
				}

				$addon_dependencies = $this->has_valid_dependencies( $registered, $registered['dependencies']['addon-dependencies'], true );
			}

			return $addon_dependencies;
		}

		/**
		 * Static Singleton Factory Method
		 *
		 * @deprecated  4.9.12  We shouldn't be handling singletons internally.
		 *
		 * @return self
		 */
		public static function instance() {
			return tribe( self::class );
		}
	}

}