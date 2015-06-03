<?php
/**
 * @package Pods
 * @category Utilities
 *
 * Class PodsClassLoader, PSR-0 compatible autoloader.
 *
 * Example usage:
 *
 * <code>
 *     $classLoader = new Pods_ClassLoader( );
 *     $classLoader->addDirectory( 'path/to/load' );
 *     $classLoader->addDirectory( 'path/to/load', 'namespace/prefix' );
 *     $classLoader->addAlias( 'Class_From', 'Class_To' );
 *     $classLoader->register( );
 * </code>
 *
 */
class Pods_ClassLoader {

	private $directoriesPrefixed = array();

	private $directories = array();

	private $aliases = array();

	/**
	 * Returns aliases
	 *
	 * @return array
	 */
	public function getAliases() {
		return $this->aliases;
	}

	/**
	 * Returns prefixes.
	 *
	 * @return array
	 */
	public function getDirectoriesPrefixed() {
		return $this->directoriesPrefixed;
	}

	/**
	 * Returns fallback directories.
	 *
	 * @return array
	 */
	public function getDirectories() {
		return $this->directories;
	}

	/**
	 * Adds a new class alias, forwarding the class to the new class.
	 *
	 * @param string $fromClass The class name we want to forward
	 * @param string $toClass   The class name we are forwarding to
	 */
	public function addAlias( $fromClass, $toClass ) {
		$this->aliases[ $fromClass ] = $toClass;
	}

	/**
	 * Adds one or more aliases from an associative array.
	 *
	 * @param array $aliases associative array of aliases.
	 */
	public function addAliases( array $aliases ) {
		foreach ( $aliases as $fromClass => $toClass ) {
			$this->addAlias( $fromClass, $toClass );
		}
	}

	/**
	 * Adds prefixes.
	 *
	 * @param array $prefixes Prefixes to add
	 */
	public function addDirectoriesPrefixed( array $prefixes ) {
		foreach ( $prefixes as $prefix => $path ) {
			$this->addDirectory( $path, $prefix );
		}
	}

	/**
	 * Registers a set of classes
	 *
	 * @param array|string $paths  The location(s) of the classes
	 * @param string       $prefix The classes prefix
	 */
	public function addDirectory( $paths, $prefix = null ) {
		if ( ! $prefix ) {
			foreach ( (array) $paths as $path ) {
				$this->directories[] = $path;
			}

			return;
		}
		if ( isset( $this->directoriesPrefixed[ $prefix ] ) ) {
			$this->directoriesPrefixed[ $prefix ] = array_merge( $this->directoriesPrefixed[ $prefix ],
				(array) $paths );
		} else {
			$this->directoriesPrefixed[ $prefix ] = (array) $paths;
		}
	}

	/**
	 * Registers this instance as an autoloader.
	 *
	 * @param Boolean $prepend Whether to prepend the autoloader or not
	 */
	public function register( $prepend = false ) {
		spl_autoload_register( array( $this, 'loadClass' ), true, $prepend );
	}

	/**
	 * Unregisters this instance as an autoloader.
	 */
	public function unregister() {
		spl_autoload_unregister( array( $this, 'loadClass' ) );
	}

	/**
	 * Loads the given class.
	 *
	 * @param string $className The name of the class to load.
	 *
	 * @return boolean|null
	 */
	public function loadClass( $className ) {

		if ( isset( $this->aliases[ $className ] ) ) {
			$this->forwardClass( $className, $this->aliases[ $className ] );
		}

		if ( $file = $this->findFile( $className ) ) {
			require_once $file;

			return true;
		}
		// Fallback for Pods 1.x and 2.x classes
		elseif ( 0 === strpos( $className, 'Pod' ) ) {
			$two_dot_oh = str_replace( 'Pods', 'Pods_', $className );
			$one_dot_oh = str_replace( 'Pod', 'Pods_', $className );

			if ( $file = $this->findFile( $two_dot_oh ) ) {
				$this->forwardClass( $two_dot_oh, $className );

				require_once $file;

				return true;
			} elseif ( $file = $this->findFile( $one_dot_oh ) ) {
				$this->forwardClass( $one_dot_oh, $className );

				require_once $file;

				return true;
			}
		}

		return null;

	}

	/**
	 * Finds the path to the file where the class is defined.
	 *
	 * @param string $class The classname to find
	 *
	 * @return bool|string
	 */
	public function findFile( $class ) {
		if ( false !== $pos = strrpos( $class, '\\' ) ) {
			// namespaced class name
			$classPath = str_replace( '\\', DIRECTORY_SEPARATOR, substr( $class, 0, $pos ) ) . DIRECTORY_SEPARATOR;
			$className = substr( $class, $pos + 1 );
		} else {
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

		foreach ( $this->directoriesPrefixed as $prefix => $dirs ) {
			if ( $class === strstr( $class, $prefix ) ) {
				foreach ( $dirs as $dir ) {
					if ( file_exists( $dir . DIRECTORY_SEPARATOR . $classPath ) ) {
						return $dir . DIRECTORY_SEPARATOR . $classPath;
					}
				}
			}
		}

		return null;
	}

	/**
	 * Creates a fallback class that maps to the correct class (PodsInit >> Pods_Init).
	 *
	 * @param string $fromClass The name of the original class to map from.
	 * @param string $toClass   The name of the class to map to.
	 *
	 * @return void
	 */
	public function forwardClass( $fromClass, $toClass ) {
		if ( ! class_exists( $fromClass ) ) {
			eval( "
				class {$fromClass} extends {$toClass} {

					public static \$classLoader_forward_class = '{$fromClass}';

					public function __construct() {

					parent::__construct();

					}

			}
			" );
		}
	}
}
