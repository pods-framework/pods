<?php
/**
 * Icon type handler
 *
 * @package Icon_Picker
 * @author  Dzikri Aziz <kvcrvt@gmail.com>
 */


/**
 * Base icon type class
 */
class Icon_Picker_Type {

	/**
	 * Icon type ID
	 *
	 * @since  0.1.0
	 * @access protected
	 * @var    string
	 */
	protected $id = '';

	/**
	 * Icon type name
	 *
	 * @since  0.1.0
	 * @access protected
	 * @var    string
	 */
	protected $name = '';

	/**
	 * Icon type version
	 *
	 * @since  0.1.0
	 * @access protected
	 * @var    string
	 */
	protected $version = '';

	/**
	 * JS Controller
	 *
	 * @since 0.1.0
	 * @access protected
	 * @var    string
	 */
	protected $controller = '';


	/**
	 * Constructor
	 *
	 * Supplied $args override class property defaults.
	 *
	 * @since 0.1.0
	 * @param array  $args Optional. Arguments to override class property defaults.
	 */
	public function __construct( $args = array() ) {
		$keys = array_keys( get_object_vars( $this ) );

		foreach ( $keys as $key ) {
			if ( isset( $args[ $key ] ) ) {
				$this->$key = $args[ $key ];
			}
		}
	}


	/**
	 * Getter magic
	 *
	 * @since  0.1.0
	 * @param  string $name Property name
	 * @return mixed  NULL if attribute doesn't exist.
	 */
	public function __get( $name ) {
		$vars = get_object_vars( $this );
		if ( isset( $vars[ $name ] ) ) {
			return $vars[ $name ];
		}

		$method = "get_{$name}";
		if ( method_exists( $this, $method ) ) {
			return call_user_func( array( $this, $method ) );
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
		return ( isset( $this->$name ) || method_exists( $this, "get_{$name}" ) );
	}


	/**
	 * Get extra properties data
	 *
	 * @since  0.1.0
	 * @access protected
	 * @return array
	 */
	protected function get_props_data() {
		return array();
	}


	/**
	 * Get properties
	 *
	 * @since  0.1.0
	 * @return array
	 */
	public function get_props() {
		$props = array(
			'id'         => $this->id,
			'name'       => $this->name,
			'controller' => $this->controller,
			'templateId' => $this->template_id,
			'data'       => $this->get_props_data(),
		);

		/**
		 * Filter icon type properties
		 *
		 * @since 0.1.0
		 * @param array            $props Icon type properties.
		 * @param string           $id    Icon type ID.
		 * @param Icon_Picker_Type $type  Icon_Picker_Type object.
		 */
		$props = apply_filters( 'icon_picker_type_props', $props, $this->id, $this );

		/**
		 * Filter icon type properties
		 *
		 * @since 0.1.0
		 * @param array            $props Icon type properties.
		 * @param Icon_Picker_Type $type  Icon_Picker_Type object.
		 */
		$props = apply_filters( "icon_picker_type_props_{$this->id}", $props, $this );

		return $props;
	}
}
