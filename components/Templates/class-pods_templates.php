<?php
/**
 * Pods_Templates_Frontier
 *
 * @package   Pods_Templates_Frontier
 * @author    David Cramer <david@digilab.co.za>
 * @license   GPL-2.0+
 * @link
 * @copyright 2014 David Cramer
 */

/**
 * Plugin class.
 *
 * @package Pods_Templates_Frontier
 * @author  David Cramer <david@digilab.co.za>
 */
if ( class_exists( 'Pods_Frontier_Template_Editor' ) || class_exists( 'Pods_Templates_Frontier' ) ) {
	return;
}

/**
 * Class Pods_Templates_Frontier
 */
class Pods_Templates_Frontier {

	/**
	 * @var     string
	 */
	const VERSION = '1.00';

	/**
	 * @var      string
	 */
	protected $plugin_slug = 'pods_templates';

	/**
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * @var      array
	 */
	protected $elements = array();

	/**
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 */
	private function __construct() {

		add_filter( 'pods_templates_pre_template', 'frontier_prefilter_template', 25, 4 );
		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_stylescripts' ), 20 );
		add_action( 'init', array( $this, 'activate_metaboxes' ) );
	}

	/**
	 * Return an instance of this class.
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Register and enqueue admin-specific style sheet.
	 *
	 * @return    null
	 */
	public function enqueue_admin_stylescripts() {

		$screen = get_current_screen();

		if ( $screen && $screen->id === $this->plugin_screen_hook_suffix ) {
			wp_enqueue_style( $this->plugin_slug . '-admin-styles', self::get_url( 'assets/css/panel.css', __FILE__ ), array(), self::VERSION );
			wp_enqueue_style( 'pods-codemirror' );
			wp_enqueue_script( $this->plugin_slug . '-admin-scripts', self::get_url( 'assets/js/panel.js', __FILE__ ), array(), self::VERSION );
			wp_enqueue_script( 'pods_codemirror' );
			wp_enqueue_script( 'pods-codemirror-overlay' );
			wp_enqueue_script( 'pods-codemirror-hints' );
			wp_enqueue_script( $this->plugin_slug . '-cm-editor', self::get_url( 'assets/js/editor1.js', __FILE__ ), array( 'jquery' ), self::VERSION, true );
			wp_enqueue_script( 'pods-codemirror-mode-xml' );
			wp_enqueue_script( 'pods-codemirror-mode-html' );
			wp_enqueue_script( 'pods-codemirror-mode-css' );
		}//end if

	}

	/**
	 * Register metaboxes.
	 *
	 * @return    null
	 */
	public function activate_metaboxes() {

		add_action( 'add_meta_boxes', array( $this, 'add_metaboxes' ), 5, 4 );
		add_action( 'save_post', array( $this, 'save_post_metaboxes' ), 1, 2 );

	}

	/**
	 * setup meta boxes.
	 *
	 * @param      $slug
	 * @param bool $post
	 *
	 * @return null
	 */
	public function add_metaboxes( $slug, $post = false ) {

		if ( ! empty( $post ) ) {
			if ( ! '_pods_template' === $post->post_type ) {
				return;
			}
		} else {
			$screen = get_current_screen();

			if ( $screen && '_pods_template' !== $screen->base ) {
				return;
			}
		}

		$this->plugin_screen_hook_suffix = $slug;

		// Required Styles for metabox
		wp_enqueue_style( $this->plugin_slug . '-view_template-styles', self::get_url( 'assets/css/styles-view_template.css', __FILE__ ), array(), self::VERSION );

		// Required scripts for metabox
		wp_enqueue_script( 'jquery-ui-resizable' );
		wp_enqueue_script( $this->plugin_slug . '-handlebarsjs', self::get_url( 'assets/js/handlebars2.js', __FILE__ ), array( 'jquery' ), self::VERSION, true );
		wp_enqueue_script( $this->plugin_slug . '-baldrickjs', self::get_url( 'assets/js/jquery.baldrick3.js', __FILE__ ), array( 'jquery' ), self::VERSION, true );
		wp_enqueue_script( $this->plugin_slug . '-handlebars-baldrick', self::get_url( 'assets/js/handlebars.baldrick2.js', __FILE__ ), array( 'jquery' ), self::VERSION, true );
		wp_enqueue_style( $this->plugin_slug . '-pod_reference-styles', self::get_url( 'assets/css/styles-pod_reference.css', __FILE__ ), array(), self::VERSION );

		// add metabox
		add_meta_box(
			'view_template', __( 'Template', 'pods' ), array(
				$this,
				'render_metaboxes_custom',
			), '_pods_template', 'normal', 'high', array(
				'slug'   => 'view_template',
				'groups' => array(),
			)
		);
		add_meta_box(
			'pod_reference', __( 'Pod Reference', 'pods' ), array(
				$this,
				'render_metaboxes_custom',
			), '_pods_template', 'side', 'default', array(
				'slug'   => 'pod_reference',
				'groups' => array(),
			)
		);

	}

	/**
	 * render template based meta boxes.
	 *
	 * @param $post
	 * @param $args
	 *
	 * @return null
	 */
	public function render_metaboxes_custom( $post, $args ) {

		// include the metabox view
		echo '<input type="hidden" name="pods_templates_metabox" id="pods_templates_metabox" value="' . esc_attr( wp_create_nonce( plugin_basename( __FILE__ ) ) ) . '" />';
		echo '<input type="hidden" name="pods_templates_metabox_prefix[]" value="' . esc_attr( $args['args']['slug'] ) . '" />';

		// get post meta to $atts $ post content - ir the widget option
		if ( ! empty( $post ) ) {
			$atts    = get_post_meta( $post->ID, $args['args']['slug'], true );
			$content = $post->post_content;
		} else {
			$atts    = get_option( $args['args']['slug'] );
			$content = '';
		}

		if ( file_exists( plugin_dir_path( __FILE__ ) . 'includes/element-' . $args['args']['slug'] . '.php' ) ) {
			include plugin_dir_path( __FILE__ ) . 'includes/element-' . $args['args']['slug'] . '.php';
		} elseif ( file_exists( plugin_dir_path( __FILE__ ) . 'includes/element-' . $args['args']['slug'] . '.html' ) ) {
			include plugin_dir_path( __FILE__ ) . 'includes/element-' . $args['args']['slug'] . '.html';
		}
		// add script
		if ( file_exists( plugin_dir_path( __FILE__ ) . 'assets/js/scripts-' . $args['args']['slug'] . '.php' ) ) {
			echo "<script type=\"text/javascript\">\r\n";
			include plugin_dir_path( __FILE__ ) . 'assets/js/scripts-' . $args['args']['slug'] . '.php';
			echo "</script>\r\n";
		} elseif ( file_exists( plugin_dir_path( __FILE__ ) . 'assets/js/scripts-' . $args['args']['slug'] . '.js' ) ) {
			wp_enqueue_script( $this->plugin_slug . '-' . $args['args']['slug'] . '-script', self::get_url( 'assets/js/scripts-' . $args['args']['slug'] . '.js', __FILE__ ), array( 'jquery' ), self::VERSION, true );
		}

	}

	/**
	 * save metabox data
	 *
	 * @param $pid
	 * @param $post
	 */
	public function save_post_metaboxes( $pid, $post ) {

		if ( ! isset( $_POST['pods_templates_metabox'] ) || ! isset( $_POST['pods_templates_metabox_prefix'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_POST['pods_templates_metabox'], plugin_basename( __FILE__ ) ) ) {
			return $post->ID;
		}
		if ( ! current_user_can( 'edit_post', $post->ID ) ) {
			return $post->ID;
		}
		if ( 'revision' === $post->post_type ) {
			return;
		}

		foreach ( $_POST['pods_templates_metabox_prefix'] as $prefix ) {
			if ( ! isset( $_POST[ $prefix ] ) ) {
				continue;
			}

			delete_post_meta( $post->ID, $prefix );
			add_post_meta( $post->ID, $prefix, $_POST[ $prefix ] );
		}
	}

	/**
	 *
	 * Get the current URL
	 *
	 * @param null $src
	 * @param null $path
	 *
	 * @return string
	 */
	public static function get_url( $src = null, $path = null ) {

		if ( ! empty( $path ) ) {
			return plugins_url( $src, $path );
		}

		return trailingslashit( plugins_url( $path, __FILE__ ) );
	}

}
