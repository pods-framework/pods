<?php
	/**
	 * Class PodsClassLoader, PSR-0 compatible autoloader.
	 *
	 * Example usage:
	 *
	 * <code>
	 *     $classLoader = new PodsClassLoader( '/path/to/load','Namespace\To\Load' );
	 *     $classLoader->register();
	 * </code>
	 *
	 */
	class Pods_ClassLoader {

		private $prefixedDirectories = array();

		private $directories = array();

		private $aliases = array();

		/**
		 * Returns aliases
		 *
		 * @return array
		 */
		public function getAliases () {
			return $this->aliases;
		}

		/**
		 * Returns prefixes.
		 *
		 * @return array
		 */
		public function getPrefixedDirectories () {
			return $this->prefixedDirectories;
		}

		/**
		 * Returns fallback directories.
		 *
		 * @return array
		 */
		public function getDirectories () {
			return $this->directories;
		}

		public function addAlias ( $oldClass, $newClass ) {
			$this->aliases[ $oldClass ] = $newClass;
		}

		public function addAliases ( array $aliases ) {
			foreach ( $aliases as $oldClass => $newClass ) {
				$this->addAlias( $oldClass, $newClass );
			}
		}

		/**
		 * Adds prefixes.
		 *
		 * @param array $prefixes Prefixes to add
		 */
		public function addDirectoriesPrefixed ( array $prefixes ) {
			foreach ( $prefixes as $prefix => $path ) {
				$this->addDirectory( $path, $prefix );
			}
		}

		/**
		 * Registers a set of classes
		 *
		 * @param array|string $paths The location(s) of the classes
		 * @param string $prefix The classes prefix
		 */
		public function addDirectory ( $paths, $prefix = null ) {
			if ( !$prefix ) {
				foreach ( (array) $paths as $path ) {
					$this->directories[ ] = $path;
				}

				return;
			}
			if ( isset( $this->prefixedDirectories[ $prefix ] ) ) {
				$this->prefixedDirectories[ $prefix ] = array_merge(
					$this->prefixedDirectories[ $prefix ],
					(array) $paths
				);
			}
			else {
				$this->prefixedDirectories[ $prefix ] = (array) $paths;
			}
		}

		/**
		 * Registers this instance as an autoloader.
		 *
		 * @param Boolean $prepend Whether to prepend the autoloader or not
		 */
		public function register ( $prepend = false ) {
			spl_autoload_register( array( $this, 'loadClass' ), true, $prepend );
		}

		/**
		 * Unregisters this instance as an autoloader.
		 */
		public function unregister () {
			spl_autoload_unregister( array( $this, 'loadClass' ) );
		}

		/**
		 * Loads the given class.
		 *
		 * @param string $className The name of the class to load.
		 *
		 * @return boolean|null
		 */
		public function loadClass ( $className ) {

			if ( isset( $this->aliases[ $className ] ) )
				$this->forwardClass( $className, $this->aliases[ $className ] );

			if ( $file = $this->findFile( $className ) ) {
				require $file;

				return true;
			}

			return null;

		}

		public function findFile ( $class ) {
			if ( false !== $pos = strrpos( $class, '\\' ) ) {
				// namespaced class name
				$classPath = str_replace( '\\', DIRECTORY_SEPARATOR, substr( $class, 0, $pos ) ) . DIRECTORY_SEPARATOR;
				$className = substr( $class, $pos + 1 );
			}
			else {
				// PEAR-like class name
				$classPath = null;
				$className = $class;
			}
			$classPath .= str_replace( '_', DIRECTORY_SEPARATOR, $className ) . '.php';

			foreach ( $this->directories as $dir ) {
				if ( file_exists( $dir . DIRECTORY_SEPARATOR . $classPath ) ) {
					return $dir . DIRECTORY_SEPARATOR . $classPath;
				}
			}

			foreach ( $this->prefixedDirectories as $prefix => $dirs ) {
				if ( $class === strstr( $class, $prefix ) ) {
					foreach ( $dirs as $dir ) {
						if ( file_exists( $dir . DIRECTORY_SEPARATOR . $classPath ) ) {
							return $dir . DIRECTORY_SEPARATOR . $classPath;
						}
					}
				}
			}

			return false;
		}

		/**
		 * Creates a fallback class that maps to the correct class (PodsInit >> Pods_Init).
		 *
		 * @param string $fromClass The name of the original class to map from.
		 * @param string $toClass The name of the class to map to.
		 *
		 * @return void
		 */
		public function forwardClass ( $fromClass, $toClass ) {

			eval( "
			class {$fromClass} extends {$toClass} {

				public function __construct() {

					parent::__construct();

				}

			}
		" );

		}
	}
