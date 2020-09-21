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

		// add the same tab for taxonomies
		add_filter( 'pods_admin_setup_edit_tabs_taxonomy', array( $this, 'tab' ), 11, 3 );

		// add the same tab for comments
		add_filter( 'pods_admin_setup_edit_tabs_comment', array( $this, 'tab' ), 11, 3 );

		// Add options to the new tab
		add_filter( 'pods_admin_setup_edit_options', array( $this, 'options' ), 12, 2 );

		// Include and init front-end class
		add_action( 'init', array( $this, 'front_end' ), 25 );

		// Delete transients when Pods settings are updated.
		add_action( 'update_option', array( $this, 'reset' ), 21, 3 );

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

		switch ( $pod['type'] ) {
			case 'post_type':
				$options['pods-pfat'] = array(
					'pfat_enable'           => array(
						'label'             => __( 'Enable Automatic Pods Templates for this Pod?', 'pods' ),
						'help'              => __( 'When enabled you can specify the names of Pods Templates to be used to display items in this Pod in the front-end.', 'pods' ),
						'type'              => 'boolean',
						'default'           => false,
						'dependency'        => true,
						'boolean_yes_label' => '',
					),
					'pfat_run_outside_loop' => array(
						'label'             => __( 'Execute Auto Template outside of the WordPress loop? (advanced)', 'pods' ),
						'help'              => __( 'When enabled, the template will be executed whenever the specified filter is called.', 'pods' ),
						'type'              => 'boolean',
						'default'           => false,
						'depends-on'        => array( 'pfat_enable' => true ),
						'boolean_yes_label' => '',
					),
					'pfat_single'           => array(
						'label'      => __( 'Single item view template', 'pods' ),
						'help'       => __( 'Name of Pods template to use for single item view.', 'pods' ),
						'type'       => 'text',
						'default'    => false,
						'depends-on' => array( 'pfat_enable' => true ),
					),
					'pfat_append_single'    => array(
						'label'      => __( 'Single Template Location', 'pods' ),
						'help'       => __( 'Whether the template will go before, after or in place of the post content.', 'pods' ),
						'depends-on' => array( 'pfat_enable' => true ),
					),
					'pfat_filter_single'    => array(
						'label'      => __( 'Single Template Filter', 'pods' ),
						'help'       => __( 'Which filter to use for single views.', 'pods' ),
						'default'    => 'the_content',
						'type'       => 'text',
						'depends-on' => array( 'pfat_enable' => true ),
					),
					'pfat_archive'          => array(
						'label'      => __( 'Archive view template', 'pods' ),
						'help'       => __( 'Name of Pods template to use for use in this Pods archive pages.', 'pods' ),
						'type'       => 'text',
						'default'    => false,
						'depends-on' => array( 'pfat_enable' => true ),
					),
					'pfat_append_archive'   => array(
						'label'      => __( 'Archive Template Location', 'pods' ),
						'help'       => __( 'Whether the template will go before, after or in place of the post content.', 'pods' ),
						'depends-on' => array( 'pfat_enable' => true ),
					),
					'pfat_filter_archive'   => array(
						'label'      => __( 'Archive Template Filter', 'pods' ),
						'help'       => __( 'Which filter to use for archives.', 'pods' ),
						'default'    => 'the_content',
						'type'       => 'text',
						'depends-on' => array( 'pfat_enable' => true ),
					),
				);
				break;

			case 'taxonomy':
				$options['pods-pfat'] = array(
					'pfat_enable'        => array(
						'label'             => __( 'Enable Automatic Pods Templates for this Taxonomy Pod?', 'pods' ),
						'help'              => __( 'When enabled you can specify the names of a Pods Template to be used to display information about this taxonomy and/or posts in this taxonomy in the front-end.', 'pods' ),
						'type'              => 'boolean',
						'default'           => false,
						'dependency'        => true,
						'boolean_yes_label' => '',
					),
					'pfat_single'        => array(
						'label'      => __( 'Taxonomy Template', 'pods' ),
						'help'       => __( 'Name of Pods template to use to present this taxonomy object itself.', 'pods' ),
						'type'       => 'text',
						'default'    => false,
						'depends-on' => array( 'pfat_enable' => true ),
					),
					'pfat_append_single' => array(
						'label'      => __( 'Template Location', 'pods' ),
						'help'       => __( 'Whether the template will go before, after or in place of the post content.', 'pods' ),
						'depends-on' => array( 'pfat_enable' => true ),
					),
					'pfat_filter_single' => array(
						'label'      => __( 'Taxonomy Template Filter', 'pods' ),
						'help'       => __( 'Which filter to use for taxonomy view.', 'pods' ),
						'default'    => 'get_the_archive_description',
						'type'       => 'text',
						'depends-on' => array( 'pfat_enable' => true ),
					),
				);
				break;

			case 'comment':
				$options['pods-pfat'] = array(
					'pfat_enable'           => array(
						'label'             => __( 'Enable Automatic Pods Templates for this Pod?', 'pods' ),
						'help'              => __( 'When enabled you can specify the names of a Pods Template to be used to display items in this Pod in the front-end.', 'pods' ),
						'type'              => 'boolean',
						'default'           => false,
						'dependency'        => true,
						'boolean_yes_label' => '',
					),
					'pfat_single'           => array(
						'label'      => __( 'Item view template', 'pods' ),
						'help'       => __( 'Name of Pods template to use for single comment view.', 'pods' ),
						'type'       => 'text',
						'default'    => false,
						'depends-on' => array( 'pfat_enable' => true ),
					),
					'pfat_append_single'    => array(
						'label'      => __( 'Template Location', 'pods' ),
						'help'       => __( 'Whether the template will go before, after or in place of the comment text.', 'pods' ),
						'depends-on' => array( 'pfat_enable' => true ),
					),
					// @todo support other hooks.
					/*'pfat_filter_single'    => array(
						'label'       => __( 'Template Filter', 'pods' ),
						'help'        => __( 'Which filter to use for views.', 'pods' ),
						'description' => __( 'When choosing a different filter make sure this filter passes the comment as a parameter.', 'pods' ),
						'default'     => 'comment_text',
						'type'        => 'text',
						'depends-on'  => array( 'pfat_enable' => true ),
					),*/
				);
				break;
		}

		if ( isset( $options['pods-pfat'] ) ) {

			// field options pick values
			$pick = array(
				'type'               => 'pick',
				'pick_format_type'   => 'single',
				'pick_format_single' => 'dropdown',
				'default'            => 'true',
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
			$pick['data']  = $location_data;

			// add location options to fields without type set.
			foreach ( $options['pods-pfat'] as $k => $option ) {
				if ( ! isset( $option['type'] ) ) {
					$options['pods-pfat'][ $k ] = array_merge( $option, $pick );
				}
			}
		}//end if

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
	 * Reset the transients for front-end class when Pods are saved.
	 *
	 * @uses  update_option hook
	 *
	 * @param string $option
	 * @param mixed  $old_value
	 * @param mixed  $value
	 *
	 * @since 2.5.5
	 */
	public function reset( $option, $old_value, $value ) {

		if ( $option === '_transient_pods_flush_rewrites' ) {
			$this->reseter();
		}

	}

	/**
	 * Delete transients that stores the settings.
	 *
	 * @since 2.5.5
	 */
	public function reseter() {

		$keys = array( 'pods_pfat_the_pods', 'pods_pfat_auto_pods', 'pods_pfat_archive_test' );
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
				if ( ! $pod['has_archive'] && $pod['archive'] && $pod['type'] !== 'taxonomy' && ! in_array(
					$name, array(
						'post',
						'page',
						'attachment',
					), true
				) ) {
					$archive_test[ $pod['label'] ] = 'fail';
				}
			}

			pods_transient_set( $key, $archive_test );

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
						echo sprintf( '<div id="message" class="error"><p>%s</p></div>', sprintf( __( 'The Pods post type %1$s has an archive template set to be displayed using Pods auto template, but the Pod does not have an archive. You can enable post type archives in the "Advanced Options" tab.', 'pods' ), $label ) );
					}
				}
			}
		}

	}

	/**
	 * Get titles of all Pods Templates
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
