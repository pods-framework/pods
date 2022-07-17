<?php
if ( class_exists( 'Pods_PFAT' ) ) {
	return;
}

/**
 * Class Pods_Templates_Auto_Template_Settings
 *
 * This class replaced Pods_PFAT class
 *
 * @since 2.5.5
 */
class Pods_Templates_Auto_Template_Settings {

	/**
	 * Front end class object
	 *
	 * @since 2.5.5
	 *
	 * @var Pods_Templates_Auto_Template_Front_End
	 */
	private $front_end_class;

	/**
	 * Holds instance of this class
	 *
	 * @var Pods_Templates_Auto_Template_Settings
	 */
	private $instance;

	/**
	 * Constructor for the Pods_PFAT class
	 *
	 * Sets up all the appropriate hooks and actions
	 * within the plugin.
	 *
	 * @since 2.5.5
	 */
	public function __construct() {

		// Add option tab for post types
		add_filter( 'pods_admin_setup_edit_tabs_post_type', array( $this, 'tab' ), 11, 3 );

		// Add the same tab for taxonomies.
		add_filter( 'pods_admin_setup_edit_tabs_taxonomy', array( $this, 'tab' ), 11, 3 );

		// Add the same tab for comments.
		add_filter( 'pods_admin_setup_edit_tabs_comment', array( $this, 'tab' ), 11, 3 );

		// Add the same tab for users / authors.
		add_filter( 'pods_admin_setup_edit_tabs_user', array( $this, 'tab' ), 11, 3 );

		// Add options to the new tab
		add_filter( 'pods_admin_setup_edit_options', array( $this, 'options' ), 12, 2 );

		// Include and init front-end class
		add_action( 'init', array( $this, 'front_end' ), 25 );

		// Delete transients when Pods cache is flushed.
		add_action( 'pods_cache_flushed', array( $this, 'flush_cache' ) );

		// admin notice for archives without archives
		add_action( 'admin_notices', array( $this, 'archive_warning' ) );

	}

	/**
	 * Initializes the class
	 *
	 * @since 2.5.5
	 */
	public function init() {

		if ( ! is_null( $this->instance ) ) {
			$this->instance = new self();
		}

		return $this->instance;

	}

	/**
	 * The Frontier Auto Display option tab.
	 *
	 * @param array $tabs
	 * @param array $pod
	 * @param array $addtl_args
	 *
	 * @return array
	 *
	 * @since 2.5.5
	 */
	public function tab( $tabs, $pod, $addtl_args ) {
		$type = $pod['type'];

		if ( ! in_array( $type, array(
			'post_type',
			'taxonomy',
			'user',
			'comment',
		) ) ) {
			return $tabs;
		}

		$tabs['pods-pfat'] = __( 'Auto Template Options', 'pods' );

		return $tabs;

	}

	/**
	 * Adds options for this plugin under the Frontier Auto Template tab.
	 *
	 * @param array $options Tab options.
	 * @param array $pod     Pod options.
	 *
	 * @return array
	 *
	 * @since 2.5.5
	 */
	public function options( $options, $pod ) {
		$type = $pod['type'];

		if ( ! in_array( $type, array(
			'post_type',
			'taxonomy',
			'user',
			'comment',
		) ) ) {
			return $options;
		}

		$default_single_hooks = array(
			'post_type' => 'the_content',
			'taxonomy'  => 'get_the_archive_description',
			'user'      => 'get_the_author_description',
			'comment'   => 'comment_text',
		);

		$default_archive_hooks = $default_single_hooks;

		// Posts use excerpt for archive.
		$default_archive_hooks['post_type'] = 'the_excerpt';

		$changed_single  = false;
		$changed_archive = false;

		$single_hook                = pods_v( 'pfat_filter_single', $pod, '' );
		$default_single_hook        = $default_single_hooks[ $type ];
		$default_single_hook_custom = '';

		if ( ! in_array( $single_hook, array(
			'',
			$default_single_hook,
		), true ) ) {
			$changed_single             = true;
			$default_single_hook        = 'custom';
			$default_single_hook_custom = $single_hook;
		}

		$archive_hook                = pods_v( 'pfat_filter_archive', $pod, '' );
		$default_archive_hook        = $default_archive_hooks[ $type ];
		$default_archive_hook_custom = '';

		if ( ! in_array( $archive_hook, array(
			'',
			$default_archive_hook,
		), true ) ) {
			$changed_archive             = true;
			$default_archive_hook        = 'custom';
			$default_archive_hook_custom = $archive_hook;
		}

		$options['pods-pfat'] = array(
			'pfat_enable'        => array(
				'label'             => __( 'Enable Auto Templates', 'pods' ),
				'help'              => __( 'When enabled you can specify the names of Pod Templates to be used to display items in this Pod in the front-end.', 'pods' ),
				'type'              => 'boolean',
				'default'           => false,
				'dependency'        => true,
				'boolean_yes_label' => __( 'Enable Automatic Pod Templates for this Pod', 'pods' ),
			),
			'pfat_single'        => array(
				'label'      => __( 'Singular Template', 'pods' ),
				'help'       => __( 'Name of Pod Template to use. For post types, this is the singular post. For taxonomies, this is the taxonomy archive. For users, this is the author archive.', 'pods' ),
				'type'       => 'text',
				'default'    => false,
				'depends-on' => array( 'pfat_enable' => true ),
				'dependency' => true,
			),
			'pfat_append_single' => array(
				'label'      => __( 'Singular Template Location', 'pods' ),
				'help'       => __( 'Whether the template will go before, after or in place of the existing content.', 'pods' ),
				'depends-on' => array( 'pfat_enable' => true ),
				'excludes-on' => array( 'pfat_single' => '' ),
			),
			'pfat_filter_single' => array(
				'label'      => __( 'Singular Template Filter', 'pods' ),
				'help'       => __( 'Which filter to use for singular views.', 'pods' ),
				'default'    => $default_single_hook,
				'type'       => 'pick',
				'data'       => array(
					$default_single_hooks[ $type ] => sprintf( __( 'Filter: %s', 'pods' ), $default_single_hooks[ $type ] ),
					'custom'                       => __( 'Use a custom hook', 'pods' ),
				),
				'depends-on' => array( 'pfat_enable' => true ),
				'excludes-on' => array( 'pfat_single' => '' ),
				'dependency' => true,
			),
			'pfat_filter_single_custom' => array(
				'label'      => __( 'Custom Singular Template Filter', 'pods' ),
				'help'       => __( 'Which custom filter to use for singular views.', 'pods' ),
				'default'    => $default_single_hook_custom,
				'type'       => 'text',
				'depends-on' => array( 'pfat_enable' => true, 'pfat_filter_single' => 'custom' ),
				'excludes-on' => array( 'pfat_single' => '' ),
			),
			'pfat_archive'          => array(
				'label'      => __( 'List Template', 'pods' ),
				'help'       => __( 'Name of Pod Template to use. This will be used when outside the Singular context. For comments, there are no Singular comment views so they will always use the List Template.', 'pods' ),
				'type'       => 'text',
				'default'    => false,
				'depends-on' => array( 'pfat_enable' => true ),
				'dependency' => true,
			),
			'pfat_append_archive'   => array(
				'label'      => __( 'List Template Location', 'pods' ),
				'help'       => __( 'Whether the template will go before, after or in place of the existing content.', 'pods' ),
				'depends-on' => array( 'pfat_enable' => true ),
				'excludes-on' => array( 'pfat_archive' => '' ),
			),
			'pfat_filter_archive'   => array(
				'label'      => __( 'List Template Filter', 'pods' ),
				'help'       => __( 'Which filter to use for archive/list views.', 'pods' ),
				'type'       => 'pick',
				'default'    => $default_archive_hook,
				'data'       => array(
					$default_archive_hooks[ $type ] => sprintf( __( 'Filter: %s', 'pods' ), $default_archive_hooks[ $type ] ),
					'custom'                        => __( 'Use a custom hook', 'pods' ),
				),
				'depends-on' => array( 'pfat_enable' => true ),
				'excludes-on' => array( 'pfat_archive' => '' ),
				'dependency' => true,
			),
			'pfat_filter_archive_custom' => array(
				'label'      => __( 'Custom List Template Filter', 'pods' ),
				'help'       => __( 'Which custom filter to use for archive/list views.', 'pods' ),
				'default'    => $default_archive_hook_custom,
				'type'       => 'text',
				'depends-on' => array( 'pfat_enable' => true, 'pfat_filter_archive' => 'custom' ),
				'excludes-on' => array( 'pfat_archive' => '' ),
			),
			'pfat_run_outside_loop' => array(
				'label'             => __( 'Run outside loop', 'pods' ),
				'help'              => __( 'When enabled, the template will be executed whenever the specified filter is called. Only use this if you know you need to. There could be unforeseen consequences such as content in widgets and other areas of the site getting the templates added.', 'pods' ),
				'type'              => 'boolean',
				'default'           => false,
				'depends-on'        => array( 'pfat_enable' => true ),
				'boolean_yes_label' => __( 'Execute Auto Template outside of the WordPress loop (advanced)', 'pods' ),
			),
		);

		// Backcompat: Override the value used in the UI to force new option.
		if ( $changed_single ) {
			$options['pods-pfat']['pfat_filter_single']['value'] = 'custom';
		}

		// Backcompat: Override the value used in the UI to force new option.
		if ( $changed_archive ) {
			$options['pods-pfat']['pfat_filter_archive']['value'] = 'custom';
		}


		// Handle type exceptions.
		if ( 'taxonomy' === $type ) {
			// Taxonomies do not have archives, they only have a singular view.
			unset( $options['pods-pfat']['pfat_archive'] );
			unset( $options['pods-pfat']['pfat_append_archive'] );
			unset( $options['pods-pfat']['pfat_filter_archive'] );
			unset( $options['pods-pfat']['pfat_filter_archive_custom'] );
		} elseif ( 'comment' === $type ) {
			// Comments do not have singular views, they only have archive/loop views.
			unset( $options['pods-pfat']['pfat_single'] );
			unset( $options['pods-pfat']['pfat_append_single'] );
			unset( $options['pods-pfat']['pfat_filter_single'] );
			unset( $options['pods-pfat']['pfat_filter_single_custom'] );
		}

		// Non-post types do not check for loop.
		if ( 'post_type' !== $type ) {
			unset( $options['pods-pfat']['pfat_run_outside_loop'] );
		}

		// field options pick values
		$pick = array(
			'type'               => 'pick',
			'pick_format_type'   => 'single',
			'pick_format_single' => 'dropdown',
			'default'            => '',
		);

		// get template titles
		$titles = $this->get_template_titles();

		if ( ! empty( $titles ) ) {
			foreach ( $pick as $k => $v ) {
				if ( isset( $options['pods-pfat']['pfat_single'] ) ) {
					$options['pods-pfat']['pfat_single'][ $k ] = $v;
				}
				if ( isset( $options['pods-pfat']['pfat_archive'] ) ) {
					$options['pods-pfat']['pfat_archive'][ $k ] = $v;
				}
			}

			$titles_data = array( null => __( '-- Select One --', 'pods' ) ) + array_combine( $titles, $titles );

			if ( isset( $options['pods-pfat']['pfat_single'] ) ) {
				$options['pods-pfat']['pfat_single']['data'] = $titles_data;
			}
			if ( isset( $options['pods-pfat']['pfat_archive'] ) ) {
				$options['pods-pfat']['pfat_archive']['data'] = $titles_data;
			}
		}

		// Add data to $pick for template location
		unset( $pick['data'] );
		$location_data = array(
			'append'  => __( 'After', 'pods' ),
			'prepend' => __( 'Before', 'pods' ),
			'replace' => __( 'Replace', 'pods' ),
		);
		$pick['data'] = $location_data;

		// add location options to fields without type set.
		foreach ( $options['pods-pfat'] as $k => $option ) {
			if ( ! isset( $option['type'] ) ) {
				$options['pods-pfat'][ $k ] = array_merge( $option, $pick );
			}
		}

		return $options;

	}

	/**
	 * Include/ init the front end class on the front end only
	 *
	 * @param bool $load_in_admin Optional. Whether to load in admin. Default is false.
	 *
	 * @return Pods_PFAT_Frontend
	 *
	 * @since 2.5.5
	 */
	public function front_end( $load_in_admin = false ) {

		if ( ! is_admin() || $load_in_admin ) {
			include_once dirname( __FILE__ ) . '/Pods_Templates_Auto_Template_Front_End.php';

			// Only instantiate if we haven't already
			if ( is_null( $this->front_end_class ) ) {
				$this->front_end_class = new Pods_Templates_Auto_Template_Front_End();
			}

			return $this->front_end_class;
		}

	}

	/**
	 * Delete transients that stores the settings.
	 *
	 * @since 2.8.4
	 */
	public function flush_cache() {
		$keys = [
			'_pods_pfat_the_pods',
			'pods_pfat_the_pods',
			'pods_pfat_auto_pods',
			'pods_pfat_archive_test',
		];

		foreach ( $keys as $key ) {
			pods_transient_clear( $key );
		}
	}

	/**
	 * Test if archive is set for post types that don't have archives.
	 *
	 * @return bool|mixed|null|void
	 *
	 * @since 2.4.5
	 */
	public function archive_test() {

		// try to get cached results of this method
		$key          = 'pods_pfat_archive_test';
		$archive_test = pods_transient_get( $key );

		if ( $archive_test === false ) {
			$front     = $this->front_end( true );
			$auto_pods = $front->auto_pods();

			foreach ( $auto_pods as $name => $pod ) {
				if ( ! $pod['has_archive'] && $pod['archive'] && 'post_type' === $pod['type'] && ! in_array(
					$name, array(
						'post',
						'page',
						'attachment',
					), true
				) ) {
					$archive_test[ $pod['label'] ] = 'fail';
				}
			}

			pods_transient_set( $key, $archive_test, WEEK_IN_SECONDS );

		}

		return $archive_test;

	}

	/**
	 * Throw admin warnings for post types that have archive templates set, but don't support archives
	 *
	 * @since 2.4.5
	 */
	public function archive_warning() {

		// create $page variable to check if we are on pods admin page
		$page = pods_v( 'page', 'get', false, true );

		// check if we are on Pods Admin page
		if ( $page === 'pods' ) {
			$archive_test = $this->archive_test();
			if ( is_array( $archive_test ) ) {
				foreach ( $archive_test as $label => $test ) {
					if ( $test === 'fail' ) {
						echo sprintf( '<div id="message" class="error"><p>%s</p></div>', sprintf( __( 'Your Custom Post Type "%1$s" has an archive template set to be displayed using in Auto Template Options, but the Post Type is not set to show archives. You can enable post type archives in the "Advanced Options" tab.', 'pods' ), $label ) );
					}
				}
			}
		}

	}

	/**
	 * Get titles of all Pod Templates
	 *
	 * @return string[] Array of template names
	 *
	 * @since 2.4.5
	 */
	public function get_template_titles() {

		static $template_titles;

		if ( empty( $template_titles ) ) {
			$all_templates = (array) pods_api()->load_templates( array() );

			$template_titles = array();

			foreach ( $all_templates as $template ) {
				$template_titles[] = $template['name'];
			}
		}

		return $template_titles;

	}

}
