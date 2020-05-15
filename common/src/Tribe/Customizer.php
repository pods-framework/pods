<?php
// Don't load directly
defined( 'WPINC' ) or die;

/**
 * Tribe Customizer class
 *
 * @package Tribe Common
 * @subpackage Customizer
 * @since 4.0
 */
final class Tribe__Customizer {
	/**
 	 * Static Singleton Holder
	 *
	 * @var self
	 */
	protected static $instance;

	/**
	 * Static Singleton Factory Method
	 *
	 * @return self
	 */
	public static function instance() {
		return self::$instance ? self::$instance : self::$instance = new self;
	}

	/**
	 * WP_Customize_Manager instance.
	 *
	 * @since 4.0
	 * @access public
	 * @var WP_Customize_Manager
	 */
	public $manager;

	/**
	 * Instance of Customize Panel
	 *
	 * @since 4.0
	 * @access public
	 * @var WP_Customize_Panel
	 */
	public $panel;

	/**
	 * The Panel ID
	 *
	 * @since 4.0
	 * @access public
	 * @var string
	 */
	public $ID;

	/**
	 * Array of Sections of our Panel
	 *
	 * @since 4.0
	 * @access private
	 * @var array
	 */
	private $sections;

	/**
	 * Array of Sections Classes, for non-panel pages
	 *
	 * @since 4.0
	 * @access private
	 * @var array
	 */
	private $sections_class = array();

	/**
	 * Array of Sections Classes, for non-panel pages
	 *
	 * @since 4.2
	 * @access private
	 * @var array
	 */
	private $settings = array();

	/**
	 * Inline Style has been added
	 *
	 * @since 4.7.21
	 * @access private
	 * @var boolean
	 */
	protected $inline_style = false;

	/**
	 * Loads the Basic Settings for the Class to work
	 *
	 * @since  4.0
	 *
	 * @see  self::instance()
	 * @access private
	 *
	 * @return void
	 */
	private function __construct() {
		if ( ! $this->is_active() ) {
			return;
		}

		/**
		 * Filters the Panel ID, which is also the `wp_option` name for the Customizer settings
		 *
		 * @deprecated
		 * @since 4.0
		 *
		 * @param string $ID
		 * @param self   $customizer
		 */
		$this->ID = apply_filters( 'tribe_events_pro_customizer_panel_id', 'tribe_customizer', $this );

		/**
		 * Filters the Panel ID, which is also the `wp_option` name for the Customizer settings
		 *
		 * @since 4.4
		 *
		 * @param string $ID
		 * @param self   $customizer
		 */
		$this->ID = apply_filters( 'tribe_customizer_panel_id', 'tribe_customizer', $this );

		// Hook the Registering methods
		add_action( 'customize_register', array( $this, 'register' ), 15 );

		add_action( 'wp_print_footer_scripts', array( $this, 'print_css_template' ), 15 );

		// front end styles from customizer
		add_action( 'wp_enqueue_scripts', array( $this, 'inline_style' ), 15 );
		add_action( 'tribe_events_pro_widget_render', array( $this, 'inline_style' ), 101 );

		add_filter( "default_option_{$this->ID}", array( $this, 'maybe_fallback_get_option' ) );
	}

	/**
	 * Backwards compatibility for the old Customizer Option Save
	 *
	 * @since  4.4
	 *
	 * @param  mixed $sections
	 *
	 * @return mixed
	 */
	public function maybe_fallback_get_option( $sections ) {
		// Return if there is something there
		if ( ! empty( $sections ) ) {
			return $sections;
		}

		return get_option( 'tribe_events_pro_customizer', array() );
	}

	/**
	 * Loads a Section to the Customizer on the The Events Calendar Panel
	 *
	 * @since  4.4
	 *
	 * @param  object $section An Object that extends the Abstract `Tribe__Customizer__Section`
	 *
	 * @return bool
	 */
	public function load_section( $section ) {
		// You can only add a section if it extends the abstract Section
		if ( ! is_object( $section ) || ! in_array( 'Tribe__Customizer__Section', class_parents( $section ) ) ) {
			return false;
		}

		// Add the Section
		// Enforces the usage of `$instance->ID`
		$this->sections_class[ $section->ID ] = $section;

		return true;
	}

	/**
	 * Fetches all Section Classes
	 *
	 * @since  4.4
	 *
	 * @return array
	 */
	public function get_loaded_sections() {
		/**
		 * Allow developers to filter Classes from Customizer Sections
		 *
		 * @deprecated
		 * @since 4.0
		 *
		 * @param array $selection_class
		 * @param self  $customizer
		 */
		$this->sections_class = apply_filters( 'tribe_events_pro_customizer_sections_class', $this->sections_class, $this );

		/**
		 * Allow developers to filter Classes from Customizer Sections
		 *
		 * @since 4.4
		 *
		 * @param array $selection_class
		 * @param self  $customizer
		 */
		$this->sections_class = apply_filters( 'tribe_customizer_sections_class', $this->sections_class, $this );

		return $this->sections_class;
	}

	/**
	 * A easy way to check if customize is active
	 *
	 * @since  4.2.2
	 *
	 * @return boolean
	 */
	public function is_active() {
		/**
		 * Allows Developers to completely deactivate Events Calendar Customizer
		 *
		 * @deprecated
		 *
		 * @param boolean $is_active
		 */
		$is_active = apply_filters( 'tribe_events_pro_customizer_is_active', true );

		/**
		 * Allows Developers to completely deactivate Events Calendar Customizer
		 *
		 * @param boolean $is_active
		 */
		return apply_filters( 'tribe_customizer_is_active', true );
	}

	/**
	 * A method to easily search on an array
	 *
	 * @since 4.0
	 *
	 * @param  array $variable  Variable to be searched
	 * @param  array $indexes   The index that the method will try to retrieve
	 * @param  mixed $default   If the variable doesn't exist, what is the default
	 *
	 * @return mixed            Return the variable based on the index
	 */
	public static function search_var( $variable = null, $indexes = array(), $default = null ) {
		if ( is_object( $variable ) ) {
			$variable = (array) $variable;
		}

		if ( ! is_array( $variable ) ) {
			return $variable;
		}

		foreach ( (array) $indexes as $index ) {
			if ( ! is_array( $variable ) || ! isset( $variable[ $index ] ) ) {
				$variable = $default;
				break;
			}

			$variable = $variable[ $index ];
		}

		return $variable;
	}

	/**
	 * Get an option from the database, using index search you can retrieve the full panel, a section or even a setting
	 *
	 * @param  array $search   Index search, array( 'section_name', 'setting_name' )
	 * @param  mixed $default  The default, if the requested variable doesn't exits
	 * @return mixed           The requested option or the default
	 */
	public function get_option( $search = null, $default = null ) {
		$sections = get_option( $this->ID, $default );

		foreach ( $this->get_loaded_sections() as $section ) {
			/**
			 * Allow filtering the defaults for each settings to be filtered before the Ghost options to be set
			 *
			 * @deprecated
			 * @since 4.0
			 *
			 * @param array $defaults
			 */
			$defaults[ $section->ID ] = apply_filters( "tribe_events_pro_customizer_section_{$section->ID}_defaults", array() );

			/**
			 * Allow filtering the defaults for each settings to be filtered before the Ghost options to be set
			 *
			 * @since 4.4
			 *
			 * @param array $defaults
			 */
			$settings = isset( $sections[ $section->ID ] ) ? $sections[ $section->ID ] : array();
			$defaults[ $section->ID ] = apply_filters( "tribe_customizer_section_{$section->ID}_defaults", $settings );
			$sections[ $section->ID ] = wp_parse_args( $settings, $defaults[ $section->ID ] );
		}

		/**
		 * Allows Ghost Options to be inserted
		 *
		 * @deprecated
		 * @since 4.0
		 *
		 * @param array $sections
		 * @param array $search
		 */
		$sections = apply_filters( 'tribe_events_pro_customizer_pre_get_option', $sections, $search );

		/**
		 * Allows Ghost Options to be inserted
		 *
		 * @since 4.4
		 *
		 * @param array $sections
		 * @param array $search
		 */
		$sections = apply_filters( 'tribe_customizer_pre_get_option', $sections, $search );

		// Search on the Array
		if ( ! is_null( $search ) ) {
			$option = self::search_var( $sections, $search, $default );
		} else {
			$option = $sections;
		}

		/**
		 * Apply Filters After finding the variable
		 *
		 * @deprecated
		 * @since 4.0
		 *
		 * @param mixed $option
		 * @param array $search
		 * @param array $sections
		 */
		$option = apply_filters( 'tribe_events_pro_customizer_get_option', $option, $search, $sections );

		/**
		 * Apply Filters After finding the variable
		 *
		 * @since 4.4
		 *
		 * @param mixed $option
		 * @param array $search
		 * @param array $sections
		 */
		$option = apply_filters( 'tribe_customizer_get_option', $option, $search, $sections );

		return $option;
	}

	/**
	 * Check if the option exists, this method is used allow only sections that were saved to be applied.
	 *
	 * @param strings Using the following structure: self::has_option( 'section_name', 'setting_name' );
	 *
	 * @return boolean Wheter the option exists in the database
	 */
	public function has_option() {
		$search = func_get_args();
		$option = self::get_option();
		$real_option = get_option( $this->ID, array() );

		// Get section and Settign based on keys
		$section = reset( $search );
		$setting = end( $search );

		if ( empty( $real_option ) || empty( $real_option[ $section ] ) ) {
			return false;
		}

		// Search on the Array
		if ( ! is_null( $search ) ) {
			$option = self::search_var( $option, $search, null );
		}

		return ! empty( $option );
	}

	/**
	 * Print the CSS for the customizer on `wp_print_footer_scripts`
	 *
	 * @return void
	 */
	public function print_css_template() {

		//Only load in Customizer
		if ( ! is_customize_preview() ) {
			return false;
		}

		/**
		 * Use this filter to add more CSS, using Underscore Template style
		 *
		 * @deprecated
		 * @since 4.0
		 *
		 * @link  http://underscorejs.org/#template
		 *
		 * @param string $template
		 */
		$css_template = trim( apply_filters( 'tribe_events_pro_customizer_css_template', '' ) );

		/**
		 * Use this filter to add more CSS, using Underscore Template style
		 *
		 * @since 4.4
		 *
		 * @link  http://underscorejs.org/#template
		 *
		 * @param string $template
		 */
		$css_template = trim( apply_filters( 'tribe_customizer_css_template', $css_template ) );

		// If we don't have anything on the customizer don't print empty styles
		// On Customize Page, we don't care we need this
		if ( ! is_customize_preview() && empty( $css_template ) ) {
			return false;
		}

		// All sections should use this action to print their template
		echo '<script type="text/css" id="' . esc_attr( 'tmpl-' . $this->ID . '_css' ) . '">';
		echo $css_template;
		echo '</script>';

		// Place where the template will be rendered to
		echo '<style type="text/css" id="' . esc_attr( $this->ID . '_css' ) . '">';
		echo $this->parse_css_template( $css_template );
		echo '</style>';
	}

	/**
	 * Print the CSS for the customizer using wp_add_inline_style
	 *
	 * @return void
	 */
	public function inline_style() {

		//Only load on front end
		if ( is_customize_preview() || is_admin() || $this->inline_style ) {
			return false;
		}

		/**
		 * Use this filter to add more CSS, using Underscore Template style
		 *
		 * @since 4.4
		 *
		 * @link  http://underscorejs.org/#template
		 *
		 * @param string $template
		 */
		$css_template = trim( apply_filters( 'tribe_customizer_css_template', '' ) );

		// If we don't have anything on the customizer don't print empty styles
		if ( empty( $css_template ) ) {
			return false;
		}

		// add customizer styles inline with either main stylesheet is enqueued or widgets
		if ( wp_style_is( 'tribe-events-calendar-style' ) ) {

			wp_add_inline_style( 'tribe-events-calendar-style', wp_strip_all_tags( $this->parse_css_template( $css_template ) ) );
			$this->inline_style = true;

			return;
		}

		if ( wp_style_is( 'tribe-events-calendar-pro-style' ) ) {

			wp_add_inline_style( 'tribe-events-calendar-pro-style', wp_strip_all_tags( $this->parse_css_template( $css_template ) ) );
			$this->inline_style = true;

			return;
		}

		if ( wp_style_is( 'widget-calendar-pro-style' ) ) {

			wp_add_inline_style( 'widget-calendar-pro-style', wp_strip_all_tags( $this->parse_css_template( $css_template ) ) );
			$this->inline_style = true;

			return;
		}
	}

	/**
	 * Replaces the Settings using the Underscore templating strings
	 *
	 * @param  string $template The template variable, that we will look to replace the variables
	 * @return string           A Valid css after replacing the variables
	 */
	private function parse_css_template( $template ) {
		$css      = $template;
		$sections = $this->get_option();

		$search  = array();
		$replace = array();

		foreach ( $sections as $section => $settings ) {
			if ( ! is_array( $settings ) ) {
				continue;
			}
			foreach ( $settings as $setting => $value ) {
				$index = array( $section, $setting );

				// Add search based on Underscore template
				$search[] = '<%= ' . implode( '.', $index ) . ' %>';

				// Get the Replace value
				$replace[] = $value;
			}
		}

		// Finally Str replace
		return str_replace( $search, $replace, $css );
	}

	/**
	 * Method to start setting up the Customizer Section and Fields
	 *
	 * @since  4.0
	 *
	 * @param  WP_Customize_Manager $customizer WordPress Customizer variable
	 * @return void
	 */
	public function register( WP_Customize_Manager $customizer ) {
		// Set the Cutomizer on a class variable
		$this->manager = $customizer;

		/**
		 * Allow users to filter the Panel
		 *
		 * @deprecated
		 * @since 4.0
		 *
		 * @param WP_Customize_Panel $panel
		 * @param Tribe__Customizer  $customizer
		 */
		$this->panel = apply_filters( 'tribe_events_pro_customizer_panel', $this->register_panel(), $this );

		/**
		 * Allow users to filter the Panel
		 *
		 * @since 4.4
		 *
		 * @param WP_Customize_Panel $panel
		 * @param Tribe__Customizer  $customizer
		 */
		$this->panel = apply_filters( 'tribe_customizer_panel', $this->panel, $this );

		/**
		 * Filter the Sections within our Panel before they are added to the Cutomize Manager
		 *
		 * @deprecated
		 * @since 4.0
		 *
		 * @param array             $sections
		 * @param Tribe__Customizer $customizer
		 */
		$this->sections = apply_filters( 'tribe_events_pro_customizer_pre_sections', $this->sections, $this );

		/**
		 * Filter the Sections within our Panel before they are added to the Cutomize Manager
		 *
		 * @since 4.4
		 *
		 * @param array             $sections
		 * @param Tribe__Customizer $customizer
		 */
		$this->sections = apply_filters( 'tribe_customizer_pre_sections', $this->sections, $this );

		foreach ( $this->sections as $id => $section ) {
			$this->sections[ $id ] = $this->register_section( $id, $section );

			/**
			 * Allows people to Register and de-register the method to register more Fields
			 *
			 * @deprecated
			 * @since 4.0
			 *
			 * @param array                $section
			 * @param WP_Customize_Manager $manager
			 */
			do_action( "tribe_events_pro_customizer_register_{$id}_settings", $this->sections[ $id ], $this->manager );

			/**
			 * Allows people to Register and de-register the method to register more Fields
			 *
			 * @since 4.4
			 *
			 * @param array                $section
			 * @param WP_Customize_Manager $manager
			 */
			do_action( "tribe_customizer_register_{$id}_settings", $this->sections[ $id ], $this->manager );
		}

		/**
		 * Filter the Sections within our Panel, now using the actual WP_Customize_Section
		 *
		 * @deprecated
		 * @since 4.0
		 *
		 * @param array             $sections
		 * @param Tribe__Customizer $customizer
		 */
		$this->sections = apply_filters( 'tribe_events_pro_customizer_sections', $this->sections, $this );

		/**
		 * Filter the Sections within our Panel, now using the actual WP_Customize_Section
		 *
		 * @since 4.4
		 *
		 * @param array             $sections
		 * @param Tribe__Customizer $customizer
		 */
		$this->sections = apply_filters( 'tribe_customizer_sections', $this->sections, $this );

		// After everything is done, try to add Selective refresh
		$this->maybe_selective_refresh();
	}

	/**
	 * Register the base Panel for Events Calendar Sections to be attached to
	 *
	 * @since 4.0
	 *
	 * @return WP_Customize_Panel
	 */
	private function register_panel() {
		$panel = $this->manager->get_panel( $this->ID );

		// If the Panel already exists we leave returning it's instance
		if ( ! empty( $panel ) ) {
			return $panel;
		}

		$panel_args = array(
			'title' => esc_html__( 'The Events Calendar', 'tribe-common' ),
			'description' => esc_html__( 'Use the following panel of your customizer to change the styling of your Calendar and Event pages.', 'tribe-common' ),

			// After `static_front_page`
			'priority' => 125,
		);

		/**
		 * Filter the Panel Arguments for WP Customize
		 *
		 * @deprecated
		 * @since 4.0
		 *
		 * @param array             $args
		 * @param string            $ID
		 * @param Tribe__Customizer $customizer
		 */
		$panel_args = apply_filters( 'tribe_events_pro_customizer_panel_args', $panel_args, $this->ID, $this );

		/**
		 * Filter the Panel Arguments for WP Customize
		 *
		 * @since 4.4
		 *
		 * @param array             $args
		 * @param string            $ID
		 * @param Tribe__Customizer $customizer
		 */
		$panel_args = apply_filters( 'tribe_customizer_panel_args', $panel_args, $this->ID, $this );

		// Actually Register the Panel
		$this->manager->add_panel( $this->ID, $panel_args );

		// Return the Panel instance
		return $this->manager->get_panel( $this->ID );
	}

	/**
	 * Use a "alias" method to register sections to allow users to filter args and the ID
	 *
	 * @since 4.0
	 *
	 * @param  string $id   The Unique section ID
	 * @param  array $args  Arguments to register the section
	 *
	 * @link https://codex.wordpress.org/Class_Reference/WP_Customize_Manager/add_section
	 *
	 * @return WP_Customize_Section
	 */
	public function register_section( $id, $args ) {
		/**
		 * Filter the Section ID
		 *
		 * @deprecated
		 * @since 4.0
		 *
		 * @param string            $section_id
		 * @param Tribe__Customizer $customizer
		 */
		$section_id = apply_filters( 'tribe_events_pro_customizer_section_id', $id, $this );

		/**
		 * Filter the Section ID
		 *
		 * @since 4.4
		 *
		 * @param string            $section_id
		 * @param Tribe__Customizer $customizer
		 */
		$section_id = apply_filters( 'tribe_customizer_section_id', $section_id, $this );

		// Tries to fetch the section
		$section = $this->manager->get_section( $section_id );

		// If the Panel already exists we leave returning it's instance
		if ( ! empty( $section ) ) {
			return $section;
		}

		/**
		 * Filter the Section arguments, so that developers can filter arguments based on $section_id
		 *
		 * @deprecated
		 * @since 4.0
		 *
		 * @param array             $args
		 * @param string            $section_id
		 * @param Tribe__Customizer $customizer
		 */
		$section_args = apply_filters( 'tribe_events_pro_customizer_section_args', $args, $section_id, $this );

		/**
		 * Filter the Section arguments, so that developers can filter arguments based on $section_id
		 *
		 * @since 4.4
		 *
		 * @param array             $args
		 * @param string            $section_id
		 * @param Tribe__Customizer $customizer
		 */
		$section_args = apply_filters( 'tribe_customizer_section_args', $args, $section_id, $this );

		// Don't allow sections outside of our panel
		$section_args['panel'] = $this->panel->id;

		// Actually Register the Section
		$this->manager->add_section( $section_id, $section_args );

		// Return the Section instance
		return $this->manager->get_section( $section_id );
	}

	/**
	 * Build the Setting name using the HTML format for Arrays
	 *
	 * @since  4.0
	 *
	 * @param  string $slug    The actual Setting name
	 * @param  string|WP_Customize_Section $section [description]
	 *
	 * @return string          HTML name Attribute name o the setting
	 */
	public function get_setting_name( $slug, $section = null ) {
		$name = $this->panel->id;

		// If there is a section set append it
		if ( $section instanceof WP_Customize_Section ) {
			$name .= '[' . $section->id . ']';
		} elseif ( is_string( $section ) ) {
			$name .= '[' . $section . ']';
		}

		// Set the actual setting slug
		$name .= '[' . esc_attr( $slug ) . ']';

		return $name;
	}


	/**
	 * Adds a setting field name to the Array of Possible Selective refresh fields
	 *
	 * @since  4.2
	 *
	 * @param  string $name    The actual Setting name
	 *
	 * @return array           The list of existing Settings, the new one included
	 */
	public function add_setting_name( $name ) {
		$this->settings[] = $name;
		return $this->settings;
	}


	/**
	 * Using the Previously created CSS element, we not just re-create it every setting change
	 *
	 * @since  4.2
	 *
	 * @return void
	 */
	public function maybe_selective_refresh() {
		// Only try to apply selective refresh if it's active
		if ( ! isset( $this->manager->selective_refresh ) ) {
			return;
		}

		foreach ( $this->settings as $name ) {
			$setting = $this->manager->get_setting( $name );

			// Skip if we don't have that setting then skip it
			if ( is_null( $setting ) ) {
				continue;
			}

			// Skip if we already have that
			if ( ! is_null( $this->manager->selective_refresh->get_partial( $name ) ) ) {
				continue;
			}

			// Remove the Setting
			// We need this because settings are protected on the WP_Customize_Manager
			$this->manager->remove_setting( $name );

			// Change the Transport
			$setting->transport = 'postMessage';

			// Re-add the setting
			// We need this because settings are protected on the WP_Customize_Manager
			$this->manager->add_setting( $setting );

			// Add the Partial
			$this->manager->selective_refresh->add_partial(
				$name,
				array(
					'selector'        => '#' . esc_attr( $this->ID . '_css' ),
					'render_callback' => array( $this, 'print_css_template' ),
				)
			);
		}
	}
}
