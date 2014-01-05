<?php
	/**
	 * Class PodsClassLoader, PSR-0 compatible autoloader.
	 *
	 * Example usage:
	 *
	 * <code>
	 *     $classLoader = new PodsClassLoader('/path/to/load','Namespace\To\Load');
	 *     $classLoader->register();
	 * </code>
	 *
	 */
	class Pods_ClassLoader {

		private $_fileExtension;

		private $_namespace;

		private $_includePath;

		private $_namespaceSeparator = '\\';

		/**
		 * Creates a new instance that loads classes of the specified path, also works for namespaces.
		 *
		 * @param string $includePath Absolute path to include.
		 * @param string $ns The namespace to use.
		 * @param string $fileExtension File extensions to look for.
		 */
		public function __construct( $includePath = null, $ns = null, $fileExtension = '.php' ) {
			$this->_namespace = $ns;
			$this->_includePath = $includePath;
			$this->_fileExtension = $fileExtension;
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
		 * @return void
		 */
		public function loadClass( $className ) {
			if ( null === $this->_namespace || $this->_namespace . $this->_namespaceSeparator === substr( $className, 0, strlen( $this->_namespace . $this->_namespaceSeparator ) ) ) {
				$fileName = '';
				$namespace = '';
				if ( false !== ( $lastNsPos = strripos( $className, $this->_namespaceSeparator ) ) ) {
					$namespace = substr( $className, 0, $lastNsPos );
					$className = substr( $className, $lastNsPos + 1 );
					$fileName = str_replace( $this->_namespaceSeparator, DIRECTORY_SEPARATOR, $namespace ) . DIRECTORY_SEPARATOR;
				}

				$fileName .= str_replace( '_', DIRECTORY_SEPARATOR, $className ) . $this->_fileExtension;
				// Make sure we have a file before trying to include it, otherwise it will break Wordpress
				if ( file_exists( ( $this->_includePath !== null ? $this->_includePath . DIRECTORY_SEPARATOR : '' ) . $fileName ) )
					require ( $this->_includePath !== null ? $this->_includePath . DIRECTORY_SEPARATOR : '' ) . $fileName;
			}
		}
	}
