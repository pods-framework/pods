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
	protected $element_instances = array();

	/**
	 * @var      array
	 */
	protected $element_css_once = array();

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
		add_action( 'wp_footer', array( $this, 'footer_scripts' ) );
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

		if ( ! $screen || ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		if ( in_array( $screen->id, $this->plugin_screen_hook_suffix, true ) ) {
			$slug = array_search( $screen->id, $this->plugin_screen_hook_suffix );
			// $configfiles = glob( $this->get_path( __FILE__ ) .'configs/'.$slug.'-*.php' );
			if ( file_exists( $this->get_path( __FILE__ ) . 'configs/fieldgroups-' . $slug . '.php' ) ) {
				include $this->get_path( __FILE__ ) . 'configs/fieldgroups-' . $slug . '.php';
			}

			if ( ! empty( $configfiles ) ) {

				foreach ( $configfiles as $key => $fieldfile ) {
					include $fieldfile;
					if ( ! empty( $group['scripts'] ) ) {
						foreach ( $group['scripts'] as $script ) {
							wp_enqueue_script( $this->plugin_slug . '-' . strtok( $script, '.' ), $this->get_url( 'assets/js/' . $script, __FILE__ ), array( 'jquery' ) );
						}
					}
					if ( ! empty( $group['styles'] ) ) {
						foreach ( $group['styles'] as $style ) {
							wp_enqueue_style( $this->plugin_slug . '-' . strtok( $style, '.' ), $this->get_url( 'assets/css/' . $style, __FILE__ ) );
						}
					}
				}
			}
			wp_enqueue_style( $this->plugin_slug . '-admin-styles', $this->get_url( 'assets/css/panel.css', __FILE__ ), array(), self::VERSION );
			wp_enqueue_style( 'pods-codemirror' );
			wp_enqueue_script( $this->plugin_slug . '-admin-scripts', $this->get_url( 'assets/js/panel.js', __FILE__ ), array(), self::VERSION );
			wp_enqueue_script( 'pods_codemirror' );
			wp_enqueue_script( 'pods-codemirror-overlay' );
			wp_enqueue_script( 'pods-codemirror-hints' );
			wp_enqueue_script( $this->plugin_slug . '-cm-editor', $this->get_url( 'assets/js/editor1.js', __FILE__ ), array( 'jquery' ), self::VERSION, true );
			wp_enqueue_script( 'pods-codemirror-mode-xml' );
			wp_enqueue_script( 'pods-codemirror-mode-html' );
			wp_enqueue_script( 'pods-codemirror-mode-css' );
		}//end if

	}

	/**
	 * Process a field value
	 *
	 * @param $type
	 * @param $value
	 *
	 * @return mixed
	 */
	public function process_value( $type, $value ) {

		switch ( $type ) {
			default:
				return $value;
				break;

		}

		return $value;

	}

	/**
	 * Register metaboxes.
	 *
	 * @return    null
	 */
	public function activate_metaboxes() {

		add_action( 'add_meta_boxes__pods_template', array( $this, 'add_metaboxes' ), 5, 2 );
		add_action( 'save_post', array( $this, 'save_post_metaboxes' ), 1, 2 );

	}

	/**
	 * setup meta boxes.
	 *
	 * @param bool $post
	 *
	 * @return null
	 */
	public function add_metaboxes( $post = false ) {

		if ( ! empty( $post ) ) {
			if ( ! in_array( $post->post_type, array( '_pods_template' ), true ) ) {
				return;
			}

			$slug = $post->post_type;
		} else {
			$screen = get_current_screen();
			if ( ! $screen || ! in_array( $screen->base, array( '_pods_template' ), true ) ) {
				return;
			}

			$slug = $screen->base;
		}

		$this->plugin_screen_hook_suffix[ $slug ] = $post->post_type;

		// Required Styles for metabox
		wp_enqueue_style( $this->plugin_slug . '-view_template-styles', $this->get_url( 'assets/css/styles-view_template.css', __FILE__ ), array(), self::VERSION );

		// Required scripts for metabox
		wp_enqueue_script( 'jquery-ui-resizable' );
		wp_enqueue_script( $this->plugin_slug . '-handlebarsjs', $this->get_url( 'assets/js/handlebars2.js', __FILE__ ), array( 'jquery' ), self::VERSION, true );
		wp_enqueue_script( $this->plugin_slug . '-baldrickjs', $this->get_url( 'assets/js/jquery.baldrick3.js', __FILE__ ), array( 'jquery' ), self::VERSION, true );
		wp_enqueue_script( $this->plugin_slug . '-handlebars-baldrick', $this->get_url( 'assets/js/handlebars.baldrick2.js', __FILE__ ), array( 'jquery' ), self::VERSION, true );
		wp_enqueue_style( $this->plugin_slug . '-pod_reference-styles', $this->get_url( 'assets/css/styles-pod_reference.css', __FILE__ ), array(), self::VERSION );

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

		if ( file_exists( $this->get_path( __FILE__ ) . 'includes/element-' . $args['args']['slug'] . '.php' ) ) {
			include $this->get_path( __FILE__ ) . 'includes/element-' . $args['args']['slug'] . '.php';
		} elseif ( file_exists( $this->get_path( __FILE__ ) . 'includes/element-' . $args['args']['slug'] . '.html' ) ) {
			include $this->get_path( __FILE__ ) . 'includes/element-' . $args['args']['slug'] . '.html';
		}
		// add script
		if ( file_exists( $this->get_path( __FILE__ ) . 'assets/js/scripts-' . $args['args']['slug'] . '.php' ) ) {
			echo "<script type=\"text/javascript\">\r\n";
			include $this->get_path( __FILE__ ) . 'assets/js/scripts-' . $args['args']['slug'] . '.php';
			echo "</script>\r\n";
		} elseif ( file_exists( $this->get_path( __FILE__ ) . 'assets/js/scripts-' . $args['args']['slug'] . '.js' ) ) {
			wp_enqueue_script( $this->plugin_slug . '-' . $args['args']['slug'] . '-script', $this->get_url( 'assets/js/scripts-' . $args['args']['slug'] . '.js', __FILE__ ), array( 'jquery' ), self::VERSION, true );
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
		if ( $post->post_type == 'revision' ) {
			return;
		}

		foreach ( $_POST['pods_templates_metabox_prefix'] as $prefix ) {
			if ( ! isset( $_POST[ $prefix ] ) ) {
				continue;
			}

			delete_post_meta( $post->ID, $prefix );
			add_post_meta( $post->ID, $prefix, $_POST[ $prefix ] );
		}

		// Clean the Pods Blocks cache so that any new/updated templates show up.
		pods_transient_clear( 'pods_blocks' );
		pods_transient_clear( 'pods_blocks_js' );
	}

	/**
	 * create and register an instance ID
	 *
	 * @param $id
	 * @param $process
	 *
	 * @return string
	 */
	public function element_instance_id( $id, $process ) {

		$this->element_instances[ $id ][ $process ][] = true;
		$count                                        = count( $this->element_instances[ $id ][ $process ] );
		if ( $count > 1 ) {
			return $id . ( $count - 1 );
		}

		return $id;
	}

	/**
	 * Render the element
	 *
	 * @param      $atts
	 * @param      $content
	 * @param      $slug
	 * @param bool    $head
	 *
	 * @return string|void
	 */
	public function render_element( $atts, $content, $slug, $head = false ) {

		$raw_atts = $atts;

		if ( ! empty( $head ) ) {
			$instanceID = $this->element_instance_id( 'pods_templates' . $slug, 'header' );
		} else {
			$instanceID = $this->element_instance_id( 'pods_templates' . $slug, 'footer' );
		}

		// $configfiles = glob($this->get_path( __FILE__ ) .'configs/'.$slug.'-*.php');
		if ( file_exists( $this->get_path( __FILE__ ) . 'configs/fieldgroups-' . $slug . '.php' ) ) {
			include $this->get_path( __FILE__ ) . 'configs/fieldgroups-' . $slug . '.php';

			$defaults = array();
			foreach ( $configfiles as $file ) {

				include $file;
				foreach ( $group['fields'] as $variable => $conf ) {
					if ( ! empty( $group['multiple'] ) ) {
						$value = array( $this->process_value( $conf['type'], $conf['default'] ) );
					} else {
						$value = $this->process_value( $conf['type'], $conf['default'] );
					}
					if ( ! empty( $group['multiple'] ) ) {
						if ( isset( $atts[ $variable . '_1' ] ) ) {
							$index = 1;
							$value = array();
							while ( isset( $atts[ $variable . '_' . $index ] ) ) {
								$value[] = $this->process_value( $conf['type'], $atts[ $variable . '_' . $index ] );
								$index ++;
							}
						} elseif ( isset( $atts[ $variable ] ) ) {
							if ( is_array( $atts[ $variable ] ) ) {
								foreach ( $atts[ $variable ] as &$varval ) {
									$varval = $this->process_value( $conf['type'], $varval );
								}
								$value = $atts[ $variable ];
							} else {
								$value[] = $this->process_value( $conf['type'], $atts[ $variable ] );
							}
						}
					} else {
						if ( isset( $atts[ $variable ] ) ) {
							$value = $this->process_value( $conf['type'], $atts[ $variable ] );
						}
					}//end if

					if ( ! empty( $group['multiple'] ) && ! empty( $value ) ) {
						foreach ( $value as $key => $val ) {
							$groups[ $group['master'] ][ $key ][ $variable ] = $val;
						}
					}
					$defaults[ $variable ] = $value;
				}//end foreach
			}//end foreach
			$atts = $defaults;
		}//end if

		// pull in the assets
		$assets = array();
		if ( file_exists( $this->get_path( __FILE__ ) . 'assets/assets-' . $slug . '.php' ) ) {
			include $this->get_path( __FILE__ ) . 'assets/assets-' . $slug . '.php';
		}

		ob_start();
		if ( file_exists( $this->get_path( __FILE__ ) . 'includes/element-' . $slug . '.php' ) ) {
			include $this->get_path( __FILE__ ) . 'includes/element-' . $slug . '.php';
		} else {
			if ( file_exists( $this->get_path( __FILE__ ) . 'includes/element-' . $slug . '.html' ) ) {
				include $this->get_path( __FILE__ ) . 'includes/element-' . $slug . '.html';
			}
		}
		$out = ob_get_clean();

		if ( ! empty( $head ) ) {

			// process headers - CSS
			if ( file_exists( $this->get_path( __FILE__ ) . 'assets/css/styles-' . $slug . '.php' ) ) {
				ob_start();
				include $this->get_path( __FILE__ ) . 'assets/css/styles-' . $slug . '.php';
				$this->element_header_styles[] = ob_get_clean();
				add_action( 'wp_head', array( $this, 'header_styles' ) );
			} else {
				if ( file_exists( $this->get_path( __FILE__ ) . 'assets/css/styles-' . $slug . '.css' ) ) {
					wp_enqueue_style( $this->plugin_slug . '-' . $slug . '-styles', $this->get_url( 'assets/css/styles-' . $slug . '.css', __FILE__ ), array(), self::VERSION );
				}
			}
			// process headers - JS
			if ( file_exists( $this->get_path( __FILE__ ) . 'assets/js/scripts-' . $slug . '.php' ) ) {
				ob_start();
				include $this->get_path( __FILE__ ) . 'assets/js/scripts-' . $slug . '.php';
				$this->element_footer_scripts[] = ob_get_clean();
			} else {
				if ( file_exists( $this->get_path( __FILE__ ) . 'assets/js/scripts-' . $slug . '.js' ) ) {
					wp_enqueue_script( $this->plugin_slug . '-' . $slug . '-script', $this->get_url( 'assets/js/scripts-' . $slug . '.js', __FILE__ ), array( 'jquery' ), self::VERSION, true );
				}
			}
			// get clean do shortcode for header checking
			ob_start();
			pods_do_shortcode(
				$out, array(
					'each',
					'pod_sub_template',
					'once',
					'pod_once_template',
					'before',
					'pod_before_template',
					'after',
					'pod_after_template',
					'if',
					'pod_if_field',
				)
			);
			ob_get_clean();

			return;
		}//end if

		return pods_do_shortcode(
			$out, array(
				'each',
				'pod_sub_template',
				'once',
				'pod_once_template',
				'before',
				'pod_before_template',
				'after',
				'pod_after_template',
				'if',
				'pod_if_field',
			)
		);
	}

	/**
	 * Render any header styles
	 */
	public function header_styles() {

		if ( ! empty( $this->element_header_styles ) ) {
			echo "<style type=\"text/css\">\r\n";
			foreach ( $this->element_header_styles as $styles ) {
				echo $styles . "\r\n";
			}
			echo "</style>\r\n";
		}
	}

	/**
	 * Render any footer scripts
	 */
	public function footer_scripts() {

		if ( ! empty( $this->element_footer_scripts ) ) {
			echo "<script type=\"text/javascript\">\r\n";
			foreach ( $this->element_footer_scripts as $script ) {
				echo $script . "\r\n";
			}
			echo "</script>\r\n";
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

	/**
	 *
	 * Get the current URL
	 *
	 * @param null $src
	 *
	 * @return string
	 */
	public static function get_path( $src = null ) {

		return plugin_dir_path( $src );

	}

}
