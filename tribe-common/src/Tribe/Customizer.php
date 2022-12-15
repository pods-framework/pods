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
	 * Static Singleton Factory Method
	 *
	 * @return self
	 *
	 * @deprecated since 4.12.6, use `tribe( 'customizer' )` instead.
	 */
	public static function instance() {
		return tribe( 'customizer' );
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
	private $sections_class = [];

	/**
	 * Array of Sections Classes, for non-panel pages
	 *
	 * @since 4.2
	 * @access private
	 * @var array
	 */
	private $settings = [];

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
	public function __construct() {
		if ( ! $this->is_active() ) {
			return;
		}

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
		add_action( 'customize_register', [ $this, 'register' ], 15 );

		add_action( 'wp_print_footer_scripts', [ $this, 'print_css_template' ], 15 );
		add_action( 'customize_controls_print_footer_scripts', [ $this, 'customize_controls_print_footer_scripts' ], 15 );

		// front end styles from customizer
		add_action( 'tribe_events_pro_widget_render', [ $this, 'inline_style' ], 101 );
		add_action( 'wp_print_footer_scripts', [ $this, 'shortcode_inline_style' ], 10 );
		add_action( 'wp_print_footer_scripts', [ $this, 'widget_inline_style' ], 10 );

		/**
		 * Allows filtering the action that will be used to trigger the printing of inline scripts.
		 *
		 * By default inline scripts will be printed on the `wp_enqueue_scripts` action, but other
		 * plugins or later iterations might require inline styles to be printed on other actions.
		 *
		 * @since 4.12.15
		 *
		 * @param string $inline_script_action_handle The handle of the action that will be used to try
		 *                                            and attempt to print inline scripts.
		 */
		$print_styles_action = apply_filters( 'tribe_customizer_print_styles_action', 'wp_enqueue_scripts' );

		add_action( $print_styles_action, [ $this, 'inline_style' ], 15 );

		add_filter( "default_option_{$this->ID}", [ $this, 'maybe_fallback_get_option' ] );
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

		return get_option( 'tribe_events_pro_customizer', [] );
	}

	/**
	 * Loads a Section to the Customizer on The Events Calendar's Panel
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
		 * @param array $sections_class
		 * @param self  $customizer
		 */
		$this->sections_class = apply_filters( 'tribe_events_pro_customizer_sections_class', $this->sections_class, $this );

		/**
		 * Allow developers to filter Classes from Customizer Sections
		 *
		 * @since 4.4
		 *
		 * @param array $sections_class
		 * @param self  $customizer
		 */
		$this->sections_class = apply_filters( 'tribe_customizer_sections_class', $this->sections_class, $this );

		return $this->sections_class;
	}

	/**
	 * Returns the section requested by ID.
	 *
	 * @since 4.13.3
	 *
	 * @param string $id The ID of the desired section.
	 *
	 * @return boolean|Tribe__Customizer__Section The requested section or boolean false if not found.
	 */
	public function get_section( $id ) {
		$sections = $this->get_loaded_sections();

		if ( empty( $sections[ $id ] ) ) {
			return false;
		}

		return $sections[ $id ];
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
	public static function search_var( $variable = null, $indexes = [], $default = null ) {
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
	 * Get an option from the database, using index search you can retrieve the full panel, a section or even a setting.
	 *
	 * @since 4.4
	 *
	 * @param  array $search   Index search, array( 'section_name', 'setting_name' ).
	 * @param  mixed $default  The default, if the requested variable doesn't exits.
	 *
	 * @return mixed           The requested option or the default.
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
			$defaults[ $section->ID ] = apply_filters( "tribe_events_pro_customizer_section_{$section->ID}_defaults", [] );

			/**
			 * Allow filtering the defaults for each settings to be filtered before the Ghost options to be set
			 *
			 * @since 4.4
			 *
			 * @param array $defaults
			 */
			$settings                 = isset( $sections[ $section->ID ] ) ? $sections[ $section->ID ] : [];
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
	 * @return boolean Whether the option exists in the database
	 */
	public function has_option() {
		$search = func_get_args();
		$option = self::get_option();
		$real_option = get_option( $this->ID, [] );

		// Get section and Settings based on keys
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
	 * Add an action for some backwards compatibility.
	 *
	 * @since 4.14.2
	 *
	 * @return void
	 */
	public function customize_controls_print_footer_scripts() {
		/**
		 * Allows plugins to hook in and add any scripts they need at the right time.
		 *
		 * @param Tribe__Customizer $customizer The current instance of Tribe__Customizer.
		 */
		do_action( 'tribe_enqueue_customizer_scripts', $this );
	}

	/**
	 * Print the CSS for the customizer on `wp_print_footer_scripts`
	 *
	 * @since 4.12.6 Moved the template building code to the `get_styles_scripts` method.
	 *
	 * @return void
	 */
	public function print_css_template() {

		//Only load in Customizer
		if ( ! is_customize_preview() ) {
			return false;
		}

		echo $this->get_styles_scripts();
	}

	/**
	 * Print the CSS for the customizer for shortcodes.
	 *
	 * @since 4.12.6
	 */
	public function shortcode_inline_style() {
		/**
		 * Whether customizer styles should print for shortcodes or not.
		 *
		 * @since 4.12.6
		 *
		 * @param boolean $should_print Whether the inline styles should be printed on screen.
		 */
		$should_print = apply_filters( 'tribe_customizer_should_print_shortcode_customizer_styles', false );

		if ( empty( $should_print ) ) {
			return;
		}

		$this->inline_style();
	}

	/**
	 * Print the CSS for the customizer for widgets.
	 *
	 * @since 4.12.14
	 */
	public function widget_inline_style() {
		/**
		 * Whether customizer styles should print for widgets or not.
		 *
		 * @since 4.12.14
		 *
		 * @param boolean $should_print Whether the inline styles should be printed on screen.
		 */
		$should_print = apply_filters( 'tribe_customizer_should_print_widget_customizer_styles', false );

		if ( empty( $should_print ) ) {
			return;
		}

		$this->inline_style();
	}

	/**
	 * Print the CSS for the customizer using wp_add_inline_style
	 *
	 * @since 4.12.15 Added the `$force` parameter to force the print of the style inline.
	 *
	 * @param bool $force Whether to ignore the context to try and print the style inline, or not.
	 */
	public function inline_style( $force = false ) {
		// Only load once on front-end.
		if ( ! $force && ( is_customize_preview() || is_admin() || $this->inline_style ) ) {
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

		$sheets = [
			'tribe-common-full-style',
		];

		/**
		 * Allow plugins to add themselves to this list.
		 *
		 * @since 4.12.1
		 *
		 * @param array<string> $sheets An array of sheets to search for.
		 * @param string $css_template String containing the inline css to add.
		 */
		$sheets = apply_filters( 'tribe_customizer_inline_stylesheets', $sheets, $css_template );

		if ( empty( $sheets ) ) {
			return false;
		}

		// Add customizer styles inline with the latest stylesheet that is enqueued.
		foreach ( array_reverse( $sheets ) as $sheet ) {
			if ( wp_style_is( $sheet ) ) {
				$inline_style = wp_strip_all_tags( $this->parse_css_template( $css_template ) );

				/**
				 * Fires before a style is, possibly, printed inline depending on the stylesheet.
				 *
				 * @since 4.12.15
				 *
				 * @param string $sheet The handle of the stylesheet the style will be printed inline for.
				 * @param string $inline_style The inline style contents, as they will be printed on the page.
				 */
				do_action( 'tribe_customizer_before_inline_style', $sheet, $inline_style );

				// Just print styles if doing 'wp_print_footer_scripts' action.
				$just_print = (bool) doing_action( 'wp_print_footer_scripts' );

				if ( $just_print ) {
					printf(
						"<style id='%s-inline-css' class='tec-customizer-inline-style' type='text/css'>\n%s\n</style>\n",
						esc_attr( $sheet ),
						$inline_style
					);
				} else {
					wp_add_inline_style( $sheet, $inline_style );
				}

				$this->inline_style = true;

				break;
			}
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

		$search  = [];
		$replace = [];

		foreach ( $sections as $section => $settings ) {
			if ( ! is_array( $settings ) ) {
				continue;
			}
			foreach ( $settings as $setting => $value ) {
				$index = [ $section, $setting ];

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
		// Set the Customizer on a class variable
		$this->manager = $customizer;

		/**
		 * Allow users to filter the Panel
		 *
		 * @since 4.4
		 *
		 * @param WP_Customize_Panel $panel
		 * @param Tribe__Customizer  $customizer
		 */
		$this->panel = apply_filters( 'tribe_customizer_panel', $this->register_panel(), $this );

		/**
		 * Filter the Sections within our Panel before they are added to the Customize Manager
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
			 * @since 4.4
			 * @since 4.12.15 Add Customizer instance as a parameter.
			 *
			 * @param array                $section
			 * @param WP_Customize_Manager $manager
			 * @param Tribe__Customizer    $customizer The current customizer instance.
			 */
			do_action( "tribe_customizer_register_{$id}_settings", $this->sections[ $id ], $this->manager, $this );
		}

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

		$panel_args = [
			'title'       => esc_html__( 'The Events Calendar', 'tribe-common' ),
			'description' => esc_html__( 'Use the following panel of your customizer to change the styling of your Calendar and Event pages.', 'tribe-common' ),

			// After `static_front_page`
			'priority'    => 125,
		];

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
	 * Returns a URL to the TEC Customizer panel.
	 *
	 * @since 4.14.0
	 *
	 * @return string The URL to the TEC Customizer panel.
	 */
	public function get_panel_url() {
		$query['autofocus[panel]'] = 'tribe_customizer';
		return add_query_arg( $query, admin_url( 'customize.php' ) );
	}

	/**
	 * Returns an HTML link directly to the (opened) TEC Customizer panel
	 *
	 * @since 4.14.0
	 *
	 * @param string $link_text The (pre)translated text for the link.
	 *
	 * @return string The HTML anchor element, linking to the TEC Customizer panel.
	 *                An empty string is returned if missing a parameter.
	 */
	public function get_panel_link( $link_text ) {
		if ( empty( $link_text ) || ! is_string( $link_text ) ) {
			return '';
		}

		$panel_url = $this->get_panel_url();

		return sprintf(
			'<a href="%1$s">%2$s</a>',
			esc_url( $panel_url ),
			esc_html( $link_text )
		);
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
		 * @since 4.4
		 *
		 * @param string            $section_id
		 * @param Tribe__Customizer $customizer
		 */
		$section_id = apply_filters( 'tribe_customizer_section_id', $id, $this );

		// Tries to fetch the section
		$section = $this->manager->get_section( $section_id );

		// If the Panel already exists we leave returning it's instance
		if ( ! empty( $section ) ) {
			return $section;
		}

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
	 * Returns a URL to the a specific TEC Customizer section.
	 *
	 * @since 4.14.0
	 *
	 * @param string $section The slug for the desired section.
	 *
	 * @return string The URL to the TEC Customizer section.
	 */
	public function get_section_url( $section ) {
		if ( empty( $section ) ) {
			return '';
		}

		$query['autofocus[section]'] = $section;
		return add_query_arg( $query, admin_url( 'customize.php' ) );
	}

	/**
	 * Gets the HTML link to a section in the TEC Customizer.
	 *
	 * @since 4.14.0
	 *
	 * @param string $section   The section "slug" to link to.
	 * @param string $link_text The text for the link.
	 *
	 * @return string The HTML anchor element, linking to the TEC Customizer section.
	 *                An empty string is returned if missing a parameter.
	 */
	public function get_section_link( $section, $link_text = '' ) {
		if ( empty( $section ) || empty( $link_text ) || ! is_string($link_text ) ) {
			return '';
		}


		$panel_url = $this->get_section_url( $section );
		if ( empty( $panel_url ) ) {
			return '';
		}

		return sprintf(
			'<a href="%1$s">%2$s</a>',
			esc_url( $panel_url ),
			esc_html( $link_text )
		);
	}

	/**
	 * Build the Setting name using the HTML format for Arrays
	 *
	 * @since  4.0
	 *
	 * @param  string $slug                         The actual Setting name
	 * @param  string|WP_Customize_Section $section The section the setting lives in.
	 *
	 * @return string          HTML name Attribute name of the setting.
	 */
	public function get_setting_name( $slug, $section = null ) {
		$name = ! empty( $this->panel->id ) ? $this->panel->id : '';

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
	 * @param  string $name The actual Setting name
	 *
	 * @return array The list of existing Settings, the new one included
	 */
	public function add_setting_name( $name ) {
		$this->settings[] = $name;
		return $this->settings;
	}

	/**
	 * Gets the URL to a specific control/setting in the TEC Customizer.
	 *
	 * @since 4.14.0
	 *
	 * @param string $section The section "slug" to link into.
	 * @param string $setting The setting "slug" to link to.
	 *
	 * @return string The URL to the setting.
	 *                An empty string is returned if a parameter is missing or the setting control cannot be found.
	 */
	public function get_setting_url( $section, $setting ) {
		// Bail if something is missing.
		if ( empty( $setting ) || empty( $section ) ) {
			return '';
		}

		$control = $this->get_setting_name( $setting, $section );

		if ( empty( $control ) ) {
			return '';
		}

		$query['autofocus[control]'] = $control;

		return add_query_arg( $query, admin_url( 'customize.php' ) );
	}

	/**
	 * Gets the link to the a specific control/setting in the TEC Customizer.
	 *
	 * @since 4.14.0
	 *
	 * @param string $section   The section "slug" to link into.
	 * @param string $setting   The setting "slug" to link to.
	 * @param string $link_text The translated text for the link.
	 *
	 * @return string The HTML anchor element, linking to the TEC Customizer setting.
	 *                An empty string is returned if missing a parameter or the setting control cannot be found.
	 */
	public function get_setting_link( $section, $setting, $link_text ) {
		// Bail if something is missing.
		if ( empty( $setting ) || empty( $section ) || empty( $link_text ) ) {
			return '';
		}

		$control_url = $this->get_setting_url( $section, $setting );

		if ( empty( $control_url ) ) {
			return '';
		}

		return sprintf(
			'<a href="%1$s">%2$s</a>',
			esc_url( $control_url ),
			esc_html( $link_text )
		);
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
				[
					'selector'        => '#' . esc_attr( $this->ID . '_css' ),
					'render_callback' => [ $this, 'print_css_template' ],
				]
			);
		}
	}

	/**
	 * Builds and returns the Customizer CSS template contents.
	 *
	 * The method DOES NOT check if the current context is the one where the Customizer template should
	 * be printed or not; that care is left to the code calling this method.
	 *
	 * @since 4.12.6 Extracted this method from the `print_css_template` one.
	 *
	 * @return string The CSS template contents.
	 */
	public function get_styles_scripts() {
		/**
		 * Use this filter to add more CSS, using Underscore Template style.
		 *
		 * @since 4.4
		 *
		 * @param string $template The Customizer template.
		 *
		 * @link  http://underscorejs.org/#template
		 */
		$css_template = trim( apply_filters( 'tribe_customizer_css_template', '' ) );

		// If we don't have anything on the Customizer, then don't print empty styles.
		if ( empty( $css_template ) ) {
			return '';
		}

		// Prepare the customizer scripts.
		$result = '<script type="text/css" id="' . esc_attr( 'tmpl-' . $this->ID . '_css' ) . '">';
		$result .= $css_template;
		$result .= '</script>';

		// Prepare the customizer styles.
		$result .= '<style type="text/css" id="' . esc_attr( $this->ID . '_css' ) . '">';
		$result .= $this->parse_css_template( $css_template );
		$result .= '</style>';

		return $result;
	}

	/**
	 * Inserts link to TEC Customizer section for FSE themes in admin (left) menu.
	 *
	 * @since 4.14.8
	 */
	public function add_fse_customizer_link() {
		_deprecated_function( __METHOD__, '4.14.18', 'No replacement. Customizer menu item is preserved as long as we activate it.');
		// Exit early if the current theme is not a FSE theme.
		if (  ! tec_is_full_site_editor() ) {
			return;
		}

		// Add a link to the TEC panel in the Customizer.
		add_submenu_page(
			'themes.php',
			_x( 'Customize The Events Calendar', 'Page title for the TEC Customizer section.', 'tribe-common' ),
			_x( 'Customize The Events Calendar', 'Menu item text for the TEC Customizer section link.', 'tribe-common' ),
			'edit_theme_options',
			esc_url( add_query_arg( 'autofocus[panel]', 'tribe_customizer' , admin_url( 'customize.php' ) ) )
		);
	}

	/**
	 * Inserts link to TEC Customizer section for FSE themes in Events > Settings > Display.
	 *
	 * @since 4.14.8
	 *
	 * @param array<string|mixed> $settings The existing settings array.
	 *
	 * @return array<string|mixed> $settings The modified settings array.
	 */
	public function add_fse_customizer_link_to_display_tab( $settings ) {
		_deprecated_function( __METHOD__, '4.14.18', 'No replacement. Customizer link is preserved as long as we activate it.');
		// Exit early if the current theme is not a FSE theme.
		if (  ! tec_is_full_site_editor() ) {
			return $settings;
		}

		$new_settings = [
			'tribe-customizer-section-title' => [
				'type' => 'html',
				'html' => '<h3>' . __( 'Customizer', 'tribe-common' ) . '</h3>',
			],
			'tribe-customizer-link-description' => [
				'type' => 'html',
				'html' => '<p class="contained">' . __( 'Adjust colors, fonts, and more with the WordPress Customizer.', 'tribe-common' ) . '</p>',
			],
			'tribe-customizer-link' => [
				'type' => 'html',
				'html' => sprintf(
					/* translators: %1$s: opening anchor tag; %2$s: closing anchor tag */
					esc_html_x( '%1$sCustomize The Events Calendar%2$s', 'Link text added to the TEC->Settings->Display tab.', 'tribe-common' ),
					'<p class="contained"><a href="' . esc_url( admin_url( 'customize.php?autofocus[panel]=tribe_customizer' ) ) . '">',
					'</a></p>'
				),
			],
		];

		$settings = Tribe__Main::array_insert_after_key( 'tribe-form-content-start', $settings, $new_settings );

		return $settings;
	}
}
