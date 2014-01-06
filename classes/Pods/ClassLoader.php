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

	private $_fileExtension;

	private $_namespace;

	private $_includePath;

	private $_deprecatedIncludePath;

	private $_namespaceSeparator = '\\';

	private $_fallback = false;

	/**
	 * Creates a new instance that loads classes of the specified path, also works for namespaces.
	 *
	 * @param string $includePath Absolute path to include.
	 * @param string $ns The namespace to use.
	 * @param string $fileExtension File extensions to look for.
	 */
	public function __construct( $includePath = null, $ns = null, $fileExtension = '.php', $deprecatedIncludePath = null ) {

		$this->_namespace = $ns;
		$this->_includePath = $includePath;
		$this->_fileExtension = $fileExtension;
		$this->_deprecatedIncludePath = $deprecatedIncludePath;

	}

	/**
	 * Installs this class loader on the SPL autoload stack.
	 */
	public function register() {

		spl_autoload_register( array( $this, 'loadClass' ) );

	}

	/**
	 * Uninstalls this class loader from the SPL autoloader stack.
	 */
	public function unregister() {

		spl_autoload_unregister( array( $this, 'loadClass' ) );

	}

	/**
	 * Loads the given class.
	 *
	 * @param string $className The name of the class to load.
	 *
	 * @return void|boolean|null
	 */
	public function loadClass( $_className ) {

		$className = $_className;

		$foundClass = false;

		if ( null === $this->_namespace || $this->_namespace . $this->_namespaceSeparator === substr( $className, 0, strlen( $this->_namespace . $this->_namespaceSeparator ) ) ) {
			$fileName = '';

			if ( false !== ( $lastNsPos = strripos( $className, $this->_namespaceSeparator ) ) ) {
				$namespace = substr( $className, 0, $lastNsPos );
				$className = substr( $className, $lastNsPos + 1 );
				$fileName = str_replace( $this->_namespaceSeparator, DIRECTORY_SEPARATOR, $namespace ) . DIRECTORY_SEPARATOR;
			}

			$fileName .= str_replace( '_', DIRECTORY_SEPARATOR, $className ) . $this->_fileExtension;

			// Make sure we have a file before trying to include it, otherwise it will break WordPress
			if ( file_exists( ( $this->_includePath !== null ? $this->_includePath . DIRECTORY_SEPARATOR : '' ) . $fileName ) ) {
				require_once( ( $this->_includePath !== null ? $this->_includePath . DIRECTORY_SEPARATOR : '' ) . $fileName );

				$foundClass = true;
			}
			// Deprecated class loading
			elseif ( null !== $this->_deprecatedIncludePath ) {
				// Not many deprecated classes yet, only two
				if ( file_exists( $this->_deprecatedIncludePath . DIRECTORY_SEPARATOR . $className . $this->_fileExtension ) ) {
					require_once( $this->_includePath . DIRECTORY_SEPARATOR . $className . $this->_fileExtension );

					$foundClass = true;
				}
				// PSR-0 version of the above
				elseif ( file_exists( $this->_deprecatedIncludePath . DIRECTORY_SEPARATOR . $fileName ) ) {
					require_once( $this->_includePath . DIRECTORY_SEPARATOR . $fileName );

					$foundClass = true;
				}
			}

			// Fallback handling for old class style (PodsInit >> Pods_Init)
			if ( !$foundClass && !$this->_fallback && null !== $this->_namespace ) {
				$fallbackClass = trim( preg_replace( '/([A-Z])/', '_$1', $_className ), '_' );

				// Load main class if it doesn't exist
				if ( !class_exists( $fallbackClass ) ) {
					$this->_fallback = true;

					$foundFallback = $this->loadClass( $fallbackClass );

					// Setup fallback class
					if ( $foundFallback ) {
						$this->forwardClass( $_className, $fallbackClass );
					}

					$this->_fallback = false;
				}
			}
			elseif ( $this->_fallback ) {
				return $foundClass;
			}
		}

		return null;

	}

	/**
	 * Creates a fallback class that maps to the correct class (PodsInit >> Pods_Init).
	 *
	 * @param string $fromClass The name of the original class to map from.
	 * @param string $toClass The name of the class to map to.
	 *
	 * @return void
	 */
	public function forwardClass( $fromClass, $toClass ) {

		eval( "
			class {$fromClass} {

				private \$__obj;

				public function __construct() {

					\$reflection = new ReflectionClass( '{$toClass}' );

					\$this->__obj = \$reflection->newInstanceArgs( func_get_args() );

				}

				public function __call( \$name, \$arguments ) {

					return call_user_func_array( array( \$this->__obj, \$name ), \$arguments );

				}

				public static function __callStatic( \$name, \$arguments ) {

					return forward_static_call_array( array( '{$toClass}', \$name ), \$arguments );

				}

				public function __set( \$offset, \$value ) {

					return \$this->__obj->{\$offset} = \$value;

				}

				public function __get( \$offset ) {

					return \$this->__obj->{\$offset};

				}

				public function __isset( \$offset ) {

					return isset( \$this->__obj->{\$offset} );

				}

				public function __unset( \$offset ) {

					unset( \$this->__obj->{\$offset} );

				}
			}
		" );

	}
}
