<?php
/**
 * Base font icon handler
 *
 * @package Icon_Picker
 * @author Dzikri Aziz <kvcrvt@gmail.com>
 */

require_once dirname( __FILE__ ) . '/base.php';

/**
 * Generic handler for icon fonts
 *
 */
abstract class Icon_Picker_Type_Font extends Icon_Picker_Type {

	/**
	 * Stylesheet ID
	 *
	 * @since  0.1.0
	 * @access protected
	 * @var    string
	 */
	protected $stylesheet_id = '';

	/**
	 * JS Controller
	 *
	 * @since  0.1.0
	 * @access protected
	 * @var    string
	 */
	protected $controller = 'Font';

	/**
	 * Template ID
	 *
	 * @since  0.1.0
	 * @access protected
	 * @var    string
	 */
	protected $template_id = 'font';


	/**
	 * Get icon groups
	 *
	 * @since  0.1.0
	 * @return array
	 */
	public function get_groups() {}


	/**
	 * Get icon names
	 *
	 * @since  0.1.0
	 * @return array
	 */
	public function get_items() {}


	/**
	 * Get stylesheet URI
	 *
	 * @since  0.1.0
	 * @return string
	 */
	public function get_stylesheet_uri() {
		$stylesheet_uri = sprintf(
			'%1$s/css/types/%2$s%3$s.css',
			Icon_Picker::instance()->url,
			$this->stylesheet_id,
			( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min'
		);

		/**
		 * Filters icon type's stylesheet URI
		 *
		 * @since  0.4.0
		 *
		 * @param  string                $stylesheet_uri Icon type's stylesheet URI.
		 * @param  string                $icon_type_id   Icon type's ID.
		 * @param  Icon_Picker_Type_Font $icon_type      Icon type's instance.
		 *
		 * @return string
		 */
		$stylesheet_uri = apply_filters(
			'icon_picker_icon_type_stylesheet_uri',
			$stylesheet_uri,
			$this->id,
			$this
		);

		return $stylesheet_uri;
	}


	/**
	 * Register assets
	 *
	 * @since   0.1.0
	 * @wp_hook action icon_picker_loader_init
	 *
	 * @param  Icon_Picker_Loader  $loader Icon_Picker_Loader instance.
	 *
	 * @return void
	 */
	public function register_assets( Icon_Picker_Loader $loader ) {
		if ( empty( $this->stylesheet_uri ) ) {
			return;
		}

		$register = true;
		$deps     = false;
		$styles   = wp_styles();

		/**
		 * When the stylesheet ID of an icon type is already registered,
		 * we'll compare its version with ours. If our stylesheet has greater
		 * version number, we'll deregister the other stylesheet.
		 */
		if ( $styles->query( $this->stylesheet_id, 'registered' ) ) {
			$object = $styles->registered[ $this->stylesheet_id ];

			if ( version_compare( $object->ver, $this->version, '<' ) ) {
				$deps = $object->deps;
				wp_deregister_style( $this->stylesheet_id );
			} else {
				$register = false;
			}
		}

		if ( $register ) {
			wp_register_style( $this->stylesheet_id, $this->stylesheet_uri, $deps, $this->version );
		}

		$loader->add_style( $this->stylesheet_id );
	}


	/**
	 * Constructor
	 *
	 * @since 0.1.0
	 * @param array $args Optional arguments passed to parent class.
	 */
	public function __construct( array $args = array() ) {
		parent::__construct( $args );

		if ( empty( $this->stylesheet_id ) ) {
			$this->stylesheet_id = $this->id;
		}

		add_action( 'icon_picker_loader_init', array( $this, 'register_assets' ) );
	}


	/**
	 * Get extra properties data
	 *
	 * @since  0.1.0
	 * @access protected
	 * @return array
	 */
	protected function get_props_data() {
		return array(
			'groups' => $this->groups,
			'items'  => $this->items,
		);
	}


	/**
	 * Get media templates
	 *
	 * @since  0.1.0
	 * @return array
	 */
	public function get_templates() {
		$templates = array(
			'icon' => '<i class="_icon {{data.type}} {{ data.icon }}"></i>',
			'item' => sprintf(
				'<div class="attachment-preview js--select-attachment">
					<div class="thumbnail">
						<span class="_icon"><i class="{{data.type}} {{ data.id }}"></i></span>
						<div class="filename"><div>{{ data.name }}</div></div>
					</div>
				</div>
				<a class="check" href="#" title="%s"><div class="media-modal-icon"></div></a>',
				esc_attr__( 'Deselect', 'icon-picker' )
			),
		);

		/**
		 * Filter media templates
		 *
		 * @since 0.1.0
		 * @param array $templates Media templates.
		 */
		$templates = apply_filters( 'icon_picker_font_media_templates', $templates );

		return $templates;
	}
}
