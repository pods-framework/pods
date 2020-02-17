<?php
defined( 'WPINC' ) || die; // Do not load directly.

/**
 * Base Extension class
 *
 * Avoid using static states within this class unless it's global for all extensions.
 * Some extension authors may lack a thorough understanding of OOP and inheritance.
 * This is built with such ones in mind.
 *
 * @package Tribe
 * @subpackage Extension
 * @since 4.3.1
 */
abstract class Tribe__Extension {

	/**
	 * Extension arguments
	 *
	 * @var array {
	 *      Each argument for this extension instance
	 *
	 *      @type string $version Extension's semantic version number.
	 *                            Can be manually set in child to boost performance.
	 *
	 *      @type string $url     Extension's tec.com page.
	 *
	 *      @type string $hook    Action/hook to fire init() on.
	 *
	 *      @type string $file    File path containing plugin header.
	 *                            Can be manually set in child to boost performance.
	 *
	 *      @type string $class   Extension's main class name.
	 *
	 *      @type array  $requires {
	 *          Each plugin this extension requires
	 *
	 *          @type string $main_class Minimum version number
	 *      }
	 *
	 *      @type array $plugin_data If the plugin file header is parsed, the
	 *                               resulting data is stored in this.
	 * }
	 */
	protected $args = array();

	/**
	 * The various extension instances
	 *
	 * @var array {
	 *      Each instance of an extension that extends this class
	 *
	 *      @type object $child_class_name instance
	 * }
	 */
	private static $instances = array();

	/**
	 * Get singleton instance of child class
	 *
	 * @param string $child_class (optional) Name of child class.
	 * @param array $args         (optional) Any args that should be set on construct.
	 *                            Only used the first time the extension is instantiated.
	 *
	 * @return object|null The extension's instance, or null if it can't be instantiated
	 */
	public static function instance( $child_class = null, $args = null ) {
		// Defaults to the name of the class that called this instance.
		$child_class = empty( $child_class ) ? self::get_called_class() : $child_class;

		// @see self::get_called_class() for the reason why this could be empty
		if ( empty( $child_class ) ) {
			return null;
		}

		if ( ! isset( self::$instances[ $child_class ] ) ) {
			$args = (array) $args;
			$args['class'] = $child_class;

			self::$instances[ $child_class ] = new $child_class( $args );
		}

		return self::$instances[ $child_class ];
	}

	/**
	 * Initializes the extension.
	 *
	 * Waits until after the init hook has fired.
	 *
	 * @param array $args The full path to the plugin file.
	 */
	final private function __construct( array $args ) {
		$this->args = $args;
		$this->construct();

		// The init() action/hook.
		$init_hook = $this->get_init_hook();

		// Continue plugin run after $init_hook has fired.
		if ( did_action( $init_hook ) > 0 ) {
			$this->register();
		} else {
			add_action( $init_hook, array( $this, 'register' ) );
		}
	}

	/**
	 * Empty function typically overridden by child class
	 */
	protected function construct() {}

	/**
	 * This is where the magic begins
	 *
	 * Declare this inside the child and put any custom code inside of it.
	 */
	abstract public function init();

	/**
	 * Adds a Tribe Plugin to the list of plugins this extension depends upon.
	 *
	 * If this plugin is not present or does not exceed the specified version
	 * init() will not run.
	 *
	 * @param string      $main_class      The Main class for this Tribe plugin.
	 * @param string|null $minimum_version Minimum acceptable version of plugin.
	 */
	final protected function add_required_plugin( $main_class, $minimum_version = null ) {
		$this->set( array( 'requires', $main_class ), $minimum_version );
	}

	/**
	 * Set the extension's tec.com URL
	 *
	 * @param string $url URL to the extension's page.
	 */
	final protected function set_url( $url ) {
		$this->set( 'url', $url );

		// Adds this as a tutorial <a> link to Wp Admin > Plugins page.
		Tribe__Plugin_Meta_Links::instance()->add_link(
			$this->get_plugin_file(),
			__( 'Tutorial', 'tribe-common' ),
			$url,
			array( 'class' => 'tribe-meta-link-extension' )
		);
	}

	/**
	 * Set the extension's version number
	 *
	 * @param string $version Extensions semantic version number.
	 */
	final protected function set_version( $version ) {
		$this->set( 'version', $version );
	}

	/**
	 * Checks if the extension has permission to run, if so runs init() in child class
	 */
	final public function register() {
		tribe_register_plugin(
			$this->get_plugin_file(),
			$this->get( 'class' ),
			$this->get_version(),
			$this->get( 'requires', array() )
		);

		$dependency = Tribe__Dependency::instance();

		// check requisite plugins are active for this extension
		$is_plugin_authorized = $dependency->has_requisite_plugins( $this->get( 'requires', array() ) );

		if ( $is_plugin_authorized ) {
			$this->init();

			//add extension as active to dependency checker
			$dependency->add_active_plugin( $this->get( 'class' ), $this->get_version(), $this->get_plugin_file() );
		}

	}

	/**
	 * Gets the full path to the extension's plugin file
	 *
	 * Sets default if the arg is blank.
	 *
	 * @return string File path
	 */
	final public function get_plugin_file() {
		$file = $this->get( 'file' );

		// If this is not set assume the extension's plugin class is the plugin file.
		if ( empty( $file ) ) {
			$reflection = new ReflectionClass( $this->get( 'class' ) );
			$file = $reflection->getFileName();
			$this->set( 'file', $file );
		}

		return $file;
	}

	/**
	 * Get the extension's version number
	 *
	 * @return string Semantic version number
	 */
	final public function get_version() {
		return $this->get_arg_or_plugin_data( 'version', 'Version' );
	}

	/**
	 * Get the extension's plugin name
	 *
	 * @return string Plugin name
	 */
	final public function get_name() {
		return $this->get_arg_or_plugin_data( 'name', 'Name' );
	}

	/**
	 * Get the extension's description
	 *
	 * @return string Plugin description
	 */
	final public function get_description() {
		return $this->get_arg_or_plugin_data( 'description', 'Description' );
	}

	/**
	 * Get's the action/hook for the extensions init()
	 *
	 * @return string Action/hook
	 */
	final public function get_init_hook() {
		return $this->get( 'hook', 'tribe_plugins_loaded' );
	}

	/**
	 * Gets the plugin data from the plugin file header
	 *
	 * This is somewhat resource intensive, so data is stored in $args
	 * in case of subsequent calls.
	 *
	 * @see get_plugin_data() for WP Admin only function this is similar to.
	 *
	 * @return array Plugin data; keys match capitalized file header declarations.
	 */
	final public function get_plugin_data() {
		$plugin_data = $this->get( 'plugin_data' );

		// Set the plugin data arg/cache to match.
		if ( empty( $plugin_data ) ) {
			$plugin_data = $this->set( 'plugin_data', Tribe__Utils__Plugins::get_plugin_data( $this->get_plugin_file() ) );
		}

		return $plugin_data;
	}

	/**
	 * Retrieves any args whose default value is stored in the plugin file header
	 *
	 * @param string $arg             The key for arg.
	 * @param string $plugin_data_key The key for the arg in the file header.
	 *
	 * @return string|null String if set, otherwise null.
	 */
	final public function get_arg_or_plugin_data( $arg, $plugin_data_key ) {
		$arg_value = $this->get( $arg, null );

		// See if the arg is already set, if not get default from plugin data and set it.
		if ( null === $arg_value ) {
			$pdata = $this->get_plugin_data();
			$arg_value = isset( $pdata[ $plugin_data_key ] ) ? $pdata[ $plugin_data_key ] : null;
		}

		return $arg_value;
	}

	/**
	 * Sets an arg, including one nested a few levels deep
	 *
	 * @param string|array $key    To set an arg nested multiple levels deep pass an array
	 *                             specifying each key in order as a value.
	 *                             Example: array( 'lvl1', 'lvl2', 'lvl3' );
	 * @param mixed         $value The value.
	 */
	final protected function set( $key, $value ) {
		$this->args = Tribe__Utils__Array::set( $this->args, $key, $value );
	}

	/**
	 * Retrieves arg, including one nested a few levels deep
	 *
	 * @param string|array $key     To select an arg nested multiple levels deep pass an
	 *                              array specifying each key in order as a value.
	 *                              Example: array( 'lvl1', 'lvl2', 'lvl3' );
	 * @param null         $default Value to return if nothing is set.
	 *
	 * @return mixed Returns the args value or the default if arg is not found.
	 */
	final public function get( $key, $default = null ) {
		return Tribe__Utils__Array::get( $this->args, $key, $default );
	}

	/**
	 * Gets the name of the class the method is called in; typically will be a child class
	 *
	 * This uses some hackery if the server is on PHP 5.2, and it can fail in rare
	 * circumstances causing a null value to be returned.
	 *
	 * @return string|null Class name
	 */
	final protected static function get_called_class() {
		$class_name = null;

		if ( function_exists( 'get_called_class' ) ) {
			// For PHP 5.3+ we can use the late static binding class name.
			$class_name = get_called_class();
		} else {
			// For PHP 5.2 and under we hack around the lack of late static bindings.
			try {
				$backtraces = debug_backtrace();

				// Grab each class from the backtrace.
				foreach ( $backtraces as $i ) {
					$class = null;

					if ( array_key_exists( 'class', $i ) ) {
						// Direct call to a class.
						$class = $i['class'];
					} elseif (
						array_key_exists( 'function', $i ) &&
						strpos( $i['function'], 'call_user_func' ) === 0 &&
						array_key_exists( 'args', $i ) &&
						is_array( $i['args'] ) &&
						is_array( $i['args'][0] ) &&
						isset( $i['args'][0][0] )
					) {
						// Found a call from call_user_func... and $i['args'][0][0] is present
						// indicating a static call to a method.
						$class = $i['args'][0][0];
					} else {
						// Slight performance boost from skipping ahead.
						continue;
					}

					// Check to see if the parent is the current class.
					// The first backtrace with a matching parent is our class.
					if ( get_parent_class( $class ) === __CLASS__ ) {
						$class_name = $class;
						break;
					}
				}
			} catch ( Exception $e ) {
				// Host has disabled or misconfigured debug_backtrace().
				$exception = new Tribe__Exception( $e );
				$exception->handle();
			}
		}

		// Class name was not set by debug_backtrace() hackery.
		if ( null === $class_name ) {
			tribe_notice( 'tribe_debug_backtrace_disabled', array( __CLASS__, 'notice_debug_backtrace' ) );
		}

		return $class_name;
	}

	/**
	 * Echoes error message indicating user is on PHP 5.2 and debug_backtrace is disabled
	 */
	final public static function notice_debug_backtrace() {
		printf(
			'<p>%s</p>',
			esc_html__( 'Unable to run Tribe Extensions. Your website host is running PHP 5.2 or older, and has likely disabled or misconfigured debug_backtrace(). You, or your website host, will need to upgrade PHP or properly configure debug_backtrace() for Tribe Extensions to work.', 'tribe-common' )
		);
	}

	/**
	 * Prevent cloning the singleton with 'clone' operator
	 *
	 * @return void
	 */
	final private function __clone() {
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
	final private function __wakeup() {
		_doing_it_wrong(
			__FUNCTION__,
			'Can not use this method on singletons.',
			'4.3'
		);
	}
}
